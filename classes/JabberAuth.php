<?php
// Arancaytar, October 2012
// This is a general PHP implementation of the ejabberd auth protocol.

abstract class JabberAuth {
  var $running;

  abstract function isuser($username, $server);
  abstract function auth($username, $server, $password);
  abstract function setpass($username, $server, $password);
  abstract function tryregister($username, $server, $password);
  abstract function removeuser($username, $server);

  function init() {
    $this->stdin = fopen('php://stdin', 'r');
    $this->stdout = fopen('php://stdout', 'w');
    $this->logfile = fopen($this->logpath . 'activity-' . date('Y-m-d') . '.log', 'a');
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
        return $this->isuser($username, $server);
      case 'auth':
        list($username, $server, $password) = $args;
        return $this->auth($username, $server, $password);
      case 'setpass':
        list($username, $server, $password) = $args;
        return $this->setpass($username, $server, $password);
      case 'tryregister':
        list($username, $server, $password) = $args;
        return $this->tryregister($username, $server, $password);
      case 'removeuser':
        list($username, $server) = $args;
        return $this->removeuser($username, $server);
      case 'removeuser3':
        list($username, $server, $password) = $args;
        return $this->auth($username, $server, $password) && $this->removeuser($username, $password);
      default:
        $this->stop();
    }
  }
}
