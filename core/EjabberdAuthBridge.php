<?php

/**
 * Must be implemented by every plugin.
 */
abstract class EjabberdAuthBridge {
  abstract function isuser($username, $server);
  abstract function auth($username, $server, $password);
  abstract function setpass($username, $server, $password);
  abstract function tryregister($username, $server, $password);
  abstract function removeuser($username, $server);
}
