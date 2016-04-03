<?php

const CONF='resources.doctrine2.connection.options.';

/**
 * Implements EjabberdAuthBridge.
 */
class BridgeVimbadmin extends EjabberdAuthBridge {
  function __construct($config) {
    $this->config = $config;
    $dsn = 'mysql:dbname=' . $config[CONF.'dbname'] . ';host=' . $config[CONF.'host'];
    $this->connection = new PDO($dsn, $config[CONF.'user'], $config[CONF.'password']);
    $this->st = $this->connection->prepare('SELECT password FROM mailbox WHERE username = :username');
  }

  function _getpass($username, $server) {
    $this->st->execute([':username' => $username . '@' . $server]);
    return $this->st->fetch();
  }

  function isuser($username, $server) {
    return $this->_getpass($username, $server) != FALSE;
  }

  function auth($username, $server, $password) {
    $row = $this->_getpass($username, $server);
    if ($row) {
      return md5($password) == $row['password'];
    }
  }
}
