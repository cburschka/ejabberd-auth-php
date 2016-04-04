<?php

/**
 * Implements EjabberdAuthBridge.
 */
class BridgeWordpress extends EjabberdAuthBridge {
  function isuser($username, $server) {
    return get_user_by('login', $username) != FALSE;
  }

  function auth($username, $server, $password) {
    return wp_authenticate_username_password(NULL, $username, $password) instanceof WP_User;
  }
}
