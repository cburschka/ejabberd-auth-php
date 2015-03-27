<?php

/**
 * Runs constantly and takes requests from an input stream
 * as specified in the ejabberd auth protocol.
 */
class EjabberdAuth {
  var $running;

  function __construct($config, $bridge, $session) {
    $this->bridge = $bridge;
    $this->session = $session;
    $this->bridge->parent = $this;
    if ($this->session) $this->session->parent = $this;
    if (!empty($config['log_path']) && is_dir($config['log_path']) && is_writable($config['log_path']))
      $this->logfile = fopen($config['log_path'] . 'activity-' . date('Y-m-d') . '.log', 'a');
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
    $username = xmpp_unescape_node($username);

    // Don't log the password, obviously.
    $this->log("Executing $command on {$username}@{$server}");

    switch ($command) {
      case 'isuser':
        return ($this->session && $this->session->isuser($username, $server)) ||
               $this->bridge->isuser($username, $server);
      case 'auth':
        return ($this->session && $this->session->auth($username, $server, $password)) ||
               $this->bridge->auth($username, $server, $password);
      case 'setpass':
      case 'tryregister':
      case 'removeuser':
      case 'removeuser3':
        return FALSE;
      default:
        $this->stop();
    }
  }
}

function xmpp_unescape_node($string) {
  return str_replace(
    ['\\20', '\\22', '\\26', '\\27', '\\2f', '\\3a', '\\3c', '\\3e', '\\40', '\\5c'],
    [' ',     '"',    '&',    '\'',   '/',    ':',    '<',    '>',    '@',    '\\'],
    $string);
}
