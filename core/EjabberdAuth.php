<?php

/**
 * Runs constantly and takes requests from an input stream
 * as specified in the ejabberd auth protocol.
 */
class EjabberdAuth {
  var $running;

  function __construct($config, EjabberdAuthBridge $bridge) {
    $this->stdin = fopen('php://stdin', 'r');
    $this->stdout = fopen('php://stdout', 'w');
    $this->bridge = $bridge;
    if (!empty($config['log_path']) && is_dir($config['log_path']) && is_writable($config['log_path'])) {
      $this->logfile = fopen($config['log_path'] . 'activity-' . date('Y-m-d') . '.log', 'a');
    }
    else {
      $this->logfile = fopen('php://stderr', 'w');
    }
    $this->log('Starting...');
    $this->running = TRUE;
  }

  function stop() {
    $this->log("Stopping...");
    $this->running = FALSE;
  }

  function run() {
    while ($this->running) {
      $data = $this->read();
      if ($data) {
        $result = $this->execute($data);
        $this->write((int)$result);
      }
    }
    $this->log("Stopped");
  }

  function read() {
    $input = fread($this->stdin, 2);
    if (!$input) {
      return $this->stop();
    }

    $input = unpack('n', $input);
    $length = $input[1];
    if($length > 0) {
      $this->log("Reading $length bytes...");
      $data = fread($this->stdin, $length);
      return $data;
    }
  }

  function write($data) {
    $this->log("OUT: $data");
    fwrite($this->stdout, pack("nn", 2, $data));
  }

  function log($data) {
    fwrite($this->logfile, sprintf("%s [%d] - %s\n", date('Y-m-d H:i:s'), getmypid(), $data));
  }

  function execute($data) {
    $args = explode(':', $data);
    $command = array_shift($args);
    // Only log the username for security.
    $this->log("Executing $command on {$args[0]}");

    switch ($command) {
      case 'isuser':
        list($username, $server) = $args;
        return $this->bridge->isuser($username, $server);
      case 'auth':
        list($username, $server, $password) = $args;
        return $this->bridge->auth($username, $server, $password);
      case 'setpass':
        list($username, $server, $password) = $args;
        return $this->bridge->setpass($username, $server, $password);
      case 'tryregister':
        list($username, $server, $password) = $args;
        return $this->bridge->tryregister($username, $server, $password);
      case 'removeuser':
        list($username, $server) = $args;
        return $this->bridge->removeuser($username, $server);
      case 'removeuser3':
        list($username, $server, $password) = $args;
        return $this->bridge->auth($username, $server, $password) && $this->bridge->removeuser($username, $password);
      default:
        $this->stop();
    }
  }
}
