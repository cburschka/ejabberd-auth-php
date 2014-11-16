<?php

/**
 * Implements EjabberdAuthBridge.
 */
class BridgeHtpasswd extends EjabberdAuthBridge {
  function __construct($data, $config) {
    $this->data = $data;
    $this->config = $config;
  }

  function isuser($username, $server) {
    return array_key_exists($username, $this->data);
  }

  function auth($username, $server, $password) {
    return $this->isuser($username, $server) && htpasswd_check($password, $this->data[$username], $this->config);
  }
}
