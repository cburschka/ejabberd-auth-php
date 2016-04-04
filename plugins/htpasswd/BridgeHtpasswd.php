<?php

/**
 * Implements EjabberdAuthBridge.
 */
class BridgeHtpasswd extends EjabberdAuthBridge {
  function __construct($data, $config) {
    $this->data = $data;
    $this->config = $config;
  }

  function getData($server) {
    return array_key_exists($server, $this->data) ? $this->data[$server] : $this->data[NULL];
  }

  function isuser($username, $server) {
    return array_key_exists($username, $this->getData($server));
  }

  function auth($username, $server, $password) {
    return $this->isuser($username, $server) && htpasswd_check($password, $this->getData($server)[$username], $this->config);
  }
}
