<?php

/**
 * Must be implemented by every plugin.
 */
abstract class EjabberdAuthBridge {
  abstract function isuser($username, $server);
  abstract function auth($username, $server, $password);
}
