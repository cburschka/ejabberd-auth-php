<?php

class BridgePhpBB3 extends EjabberdAuthBridge {
  var $auth;
  var $db;
  
  function __construct($auth, $db) {
    $this->auth = $auth;
    $this->db = $db;
  }

  function isuser($username, $server) {
    $username_clean = utf8_clean_string($username);
    $row = $this->db->sql_fetchrow($this->db->sql_query('SELECT username FROM ' . USERS_TABLE . ' WHERE username_clean = ' . "'" . $this->db->sql_escape($username_clean) . "'" . ';'));
    return !empty($row);
  }

  function auth($username, $server, $password) {
    $result = $this->auth->login($username, $password);
    return $result['status'] == LOGIN_SUCCESS;
  }
  
  // The following functions are disabled. This script will not change the phpBB user database.
  
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
