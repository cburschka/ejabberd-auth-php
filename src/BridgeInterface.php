<?php

namespace Ermarian\EjabberdAuth;

/**
 * Must be implemented by every plugin.
 */
interface BridgeInterface {

  /**
   * Factory method for initializing the plugin from config.
   *
   * @param array $config
   *
   * @return static
   */
  public static function create(array $config);

  /**
   * Check whether a user exists.
   *
   * @param string $username
   * @param string $server
   *
   * @return bool
   */
  public function isuser($username, $server);

  /**
   * Authenticate a user.
   *
   * @param string $username
   * @param string $server
   * @param string $password
   *
   * @return bool
   */
  public function auth($username, $server, $password);

}
