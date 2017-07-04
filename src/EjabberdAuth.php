<?php

namespace Ermarian\EAP;

use Symfony\Component\Yaml\Yaml;

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

  public static function create(array $config) {
    $bridges = [];
    $routes = [];
    foreach ((array) $config['bridges'] as $key => $bridge) {
      $callable = [$bridge['class'], 'create'];
      $parameters = $bridge['parameters'];
      $bridges[$key] = $callable($parameters);
      foreach ((array) $bridge['hosts'] as $pattern) {
        $score   = ($pattern[0] !== '.')
                   + substr_count($pattern, '.')
                   - substr_count($pattern, '*');
        $regex = str_replace(['.', '*'], ['\.', '[a-z0-9-]*'], $pattern);
        if ($pattern[0] === '.') {
          $regex = '/^.*?' . substr($regex, 2) . '$/';
        }
        else {
          $regex = "/^$regex$/";
        }
        $routes[] = [
          'score' => $score,
          'pattern' => $pattern,
          'regex' => $regex,
          'key' => $key
        ];
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
   * @param string $filename
   *
   * @return \Ermarian\EAP\EjabberdAuth
   *
   * @throws \InvalidArgumentException
   * @throws \Symfony\Component\Yaml\Exception\ParseException
   */
  public static function createFromFile($filename) {
    if (!is_file($filename)) {
      throw new \InvalidArgumentException("Configuration file {$filename} does not exist.");
    }
    $config = Yaml::parse(file_get_contents($filename));
    return static::create($config);
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
        try {
          $result = $this->execute($data);
          $this->write((int)$result);
        }
        catch (\InvalidArgumentException $exception) {
          $this->log($exception->getMessage());
          $this->write(0);
        }
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
   * @throws \InvalidArgumentException
   */
  public function execute($data) {
    $args = is_array($data) ? array_merge($data, [NULL,NULL,NULL]) : explode(':', $data . ':::');
    list($command, $username, $server, $password) = $args;
    $username = static::xmpp_unescape_node($username);

    // Don't log the password, obviously.
    $this->log("Executing $command on {$username}@{$server}");

    switch ($command) {
      case 'isuser':
        return $this->getBridge($server)->isuser($username, $server);
      case 'auth':
        return $this->getBridge($server)->auth($username, $server, $password);
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
        if (preg_match($route['regex'], $server)) {
          $this->log("Matched {$server} to {$route['pattern']} for {$route['key']}.");
          $result = $this->bridges[$route['key']];
          break;
        }
      }
      $this->bridgeCache[$server] = $result;
      if (!$result) {
        throw new \InvalidArgumentException("Unknown host '{$server}'");
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

