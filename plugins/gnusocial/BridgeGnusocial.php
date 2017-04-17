<?php

/**
 * Implements EjabberdAuthBridge.
 */
class BridgeGnusocial extends EjabberdAuthBridge {
  function isuser($username, $server) {
    return Nickname::isTaken($username) != null;
  }

  function auth($username, $server, $password) {
    return common_check_user($username, $password) instanceof User;
  }
}
