<?php

class BridgeDrupal8 extends EjabberdAuthBridge {
  function isuser($username, $server) {
    return user_load_by_name($username) !== FALSE;
  }

  function auth($username, $server, $password) {
    return user_authenticate($username, $password) !== FALSE;
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
