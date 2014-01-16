<?php

class BridgeDrupal7 extends EjabberdAuthBridge {
  function isuser($username, $server) {
    return user_load_by_name($username) !== FALSE;
  }

  function auth($username, $server, $password) {
    return user_authenticate($username, $password) !== FALSE;
  }
}
