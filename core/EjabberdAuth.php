<?php

/**
 * Runs constantly and takes requests from an input stream
 * as specified in the ejabberd auth protocol.
 */
class EjabberdAuth {
  var $running;

  function __construct($meta, $bridges) {
    $this->bridges = $bridges;
    foreach ($bridges as $domain) foreach ($domain as $bridge) {
      $bridge->parent = $this;
    }
    if (!empty($meta['log_path']) && is_dir($meta['log_path']) && is_writable($meta['log_path']))
      $this->logfile = fopen($meta['log_path'] . 'activity-' . date('Y-m-d') . '.log', 'a');
    else $this->logfile = STDERR;
    $this->log('Initialized.');
  }

  function stop() {
    $this->log("Stopping...");
    $this->running = FALSE;
  }

  function run() {
    $this->log('Starting...');
    $this->running = TRUE;
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
    $input = fread(STDIN, 2);
    if (!$input) {
      return $this->stop();
    }

    $input = unpack('n', $input);
    $length = $input[1];
    if($length > 0) {
      $this->log("Reading $length bytes...");
      $data = fread(STDIN, $length);
      return $data;
    }
  }

  function write($data) {
    $this->log("OUT: $data");
    fwrite(STDOUT, pack("nn", 2, $data));
  }

  function log($data) {
    fwrite($this->logfile, sprintf("%s [%d] - %s\n", date('Y-m-d H:i:s'), getmypid(), $data));
  }

  function execute($data) {
    $args = explode(':', $data . ':::');
    list($command, $username, $server, $password) = $args;

    // Don't log the password, obviously.
    $this->log("Executing $command on {$username}@{$server}");

    $domain = array_key_exists($server, $this->bridges) ? $server : '*';

    switch ($command) {
      case 'isuser':
        return $this->isuser($domain, $username, $server);
      case 'auth':
        return $this->auth($domain, $username, $server, $password);
      case 'setpass':
      case 'tryregister':
      case 'removeuser':
      case 'removeuser3':
        return FALSE;
      default:
        $this->stop();
    }
  }

  function isuser($domain, $username, $server) {
    foreach ($this->bridges[$domain] as $bridge)
      if ($bridge->isuser($username, $server)) return TRUE;
    return FALSE;
  }

  function auth($domain, $username, $server, $password) {
    foreach ($this->bridges[$domain] as $bridge)
      if ($bridge->auth($username, $server, $password)) return TRUE;
    return FALSE;
  }
}
