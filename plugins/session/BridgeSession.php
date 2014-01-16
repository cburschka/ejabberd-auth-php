<?php

/**
 * Implements EjabberdAuthBridge.
 */
class BridgeSession extends EjabberdAuthBridge {
  function __construct($pdo, $config) {
    $this->db = $pdo;
    $this->timeout = $config['timeout'];
    $this->table = $config['mysql']['tablename'];
    $this->_isuser = $this->db->prepare(sprintf('SELECT COUNT(*) FROM `%s` WHERE `username` = :user AND `created` >= :limit;', $this->table));
    $this->_auth = $this->db->prepare(sprintf('DELETE FROM `%s` WHERE `username` = :user AND `secret` = :secret AND `created` >= :limit;', $this->table));
    $this->_prune = $this->db->prepare(sprintf('DELETE COUNT(*) FROM `%s` WHERE `created` < :limit;', $this->table));
    $this->_create = $this->db->prepare(sprintf('INSERT INTO `%s` (`username`, `secret`, `created`) VALUES (:user, :secret, :time);', $this->table));
  }

  function create($entry) {
    $this->_create->execute([':user' => $entry['user'], ':secret' => $entry['secret'], ':time' => $entry['time']]);
  }

  function prune() {
    $this->_prune->execute([':limit' => time() - $this->timeout]);
  }

  function isuser($username, $server) {
    $this->prune();
    $this->_isuser->execute([':user' => $username, ':limit' => time() - $this->timeout]);
    return $this->_isuser->fetch()[0] > 0;
  }

  function auth($username, $server, $password) {
    $this->prune();
    $this->_auth->execute([':user' => $username, ':secret' => $password, ':limit' => time() - $this->timeout]);
    return $this->_auth->rowCount() > 0;
  }

  function setpass($username, $server, $password) {
    return FALSE;
  }

  function tryregister($username, $server, $password) {
    return FALSE;
  }

  function removeuser($username, $server) {
    return FALSE;
  }
}
