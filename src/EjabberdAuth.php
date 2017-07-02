<?php

namespace Ermarian\EAP;

/**
 * Runs constantly and takes requests from an input stream
 * as specified in the ejabberd auth protocol.
 */
class EjabberdAuth {

  /**
   * @var bool
   */
  protected $running;

  /**
   * @var \Ermarian\EAP\BridgeInterface[]
   */
  protected $bridges;

  /**
   * @var array[]
   */
  protected $routes;

  /**
   * @var \Ermarian\EAP\BridgeInterface[]
   */
  protected $bridgeCache = [];

  /**
   * EjabberdAuth constructor.
   *
   * @param array $bridges
   * @param array $routes
   * @param string $log_path
   */
  public function __construct(array $bridges, array $routes, $log_path = NULL) {
    $this->bridges = $bridges;
    $this->routes = $routes;
    if ($log_path && is_dir($log_path) && is_writable($log_path)) {
      $date = date('Y-m-d');
      $filename = "{$log_path}/activity-{$date}.log";
      $this->logfile = fopen($filename, 'ab');
    }
    else {
      $this->logfile = STDERR;
    }
    $this->log('Initialized.');
  }

  public function create(array $config) {
    $bridges = [];
    $routes = [];
    foreach ((array) $config['bridges'] as $key => $config) {
      $callable = [$config['class'], 'create'];
      $parameters = $config['parameters'];
      $bridges[$key] = $callable($parameters);
      foreach ((array) $config['hosts'] as $pattern) {
        $score   = ($pattern[0] !== '.')
                   + substr_count($pattern, '.')
                   - substr_count($pattern, '*');
        $pattern = str_replace(['.', '*'], ['\.', '[a-z0-9-]*'], $pattern);
        if ($pattern[0] === '.') {
          $pattern = '/^.*?' . substr($pattern, 1) . '$/';
        }
        else {
          $pattern = "/^$pattern$/";
        }
        $routes[] = [$score, $pattern, $key];
      }
    }
    usort($routes, function ($a, $b) {
      return $b['score'] - $a['score'];
    });
    return new static(
      $bridges,
      $routes,
      $config['log_path']
    );
  }

  /**
   * Stop the process.
   */
  public function stop() {
    $this->log('Stopping...');
    $this->running = FALSE;
  }

  /**
   * Run this process.
   *
   * Blocks until ::stop() is called or STDIN closes.
   */
  public function run() {
    $this->log('Starting...');
    $this->running = TRUE;
    while ($this->running && $data = $this->read()) {
      if ($data) {
        $result = $this->execute($data);
        $this->write((int)$result);
      }
    }
    $this->log('Stopped');
  }

  /**
   * Read a command from the input stream.
   *
   * Blocks until STDIN provides data or closes.
   *
   * @return string|null
   */
  public function read() {
    $input = fread(STDIN, 2);
    if ($input) {
      $input = unpack('n', $input);
      $length = $input[1];
      if($length > 0) {
        $this->log("Reading $length bytes...");
        return fread(STDIN, $length);
      }
    }
  }

  /**
   * Write a command to the output stream.
   *
   * @param $data
   */
  public function write($data) {
    $this->log("OUT: $data");
    fwrite(STDOUT, pack('nn', 2, $data));
  }

  /**
   * Log an event.
   *
   * @param string $message
   */
  public function log($message) {
    $entry = sprintf("%s [%d] - %s\n", date('Y-m-d H:i:s'), getmypid(), $message);
    fwrite($this->logfile, $entry);
  }

  /**
   * Execute a command from the server.
   *
   * @param $data
   *
   * @return bool
   */
  public function execute($data) {
    $args = is_array($data) ? array_merge($data, [NULL,NULL,NULL]) : explode(':', $data . ':::');
    list($command, $username, $server, $password) = $args;
    $username = static::xmpp_unescape_node($username);

    // Don't log the password, obviously.
    $this->log("Executing $command on {$username}@{$server}");

    switch ($command) {
      case 'isuser':
        return ($this->session && $this->session->isuser($username, $server)) ||
               $this->getBridge($server)->isuser($username, $server);
      case 'auth':
        return ($this->session && $this->session->auth($username, $server, $password)) ||
               $this->getBridge($server)->auth($username, $server, $password);
      case 'setpass':
      case 'tryregister':
      case 'removeuser':
      case 'removeuser3':
        return FALSE;
      default:
        $this->stop();
    }
  }

  /**
   * Match a hostname to the configured routes.
   *
   * @param $server
   *
   * @return \Ermarian\EAP\BridgeInterface
   *
   * @throws \InvalidArgumentException
   */
  protected function getBridge($server) {
    if (!isset($this->bridgeCache[$server])) {
      $result = FALSE;
      foreach ($this->routes as $route) {
        if (preg_match($route['pattern'], $server)) {
          $result = $this->bridges[$route['key']];
          break;
        }
      }
      $this->bridgeCache[$server] = $result;
      if (!$result) {
        $this->log("Unknown host $server");
        throw new \InvalidArgumentException('Unknown host');
      }
    }

    return $this->bridgeCache[$server];
  }

  public static function xmpp_unescape_node($string) {
    return str_replace(
      ['\\20', '\\22', '\\26', '\\27', '\\2f', '\\3a', '\\3c', '\\3e', '\\40', '\\5c'],
      [' ',     '"',    '&',    '\'',   '/',    ':',    '<',    '>',    '@',    '\\'],
      $string);
  }
}

