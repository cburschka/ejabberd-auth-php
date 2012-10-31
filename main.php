#!/usr/bin/php
<?php

define('ROOT', __DIR__ . '/');
require_once ROOT . 'core/EjabberdAuth.php';
require_once ROOT . 'core/EjabberdAuthBridge.php';

main();

function main() {
  require_once ROOT . 'config.php';
  $err = fopen('php://stderr', 'w');
  if (!empty($config['plugin']) && !empty($config[$config['plugin']])) {
    $plugin_file = 'plugins/' . $config['plugin'] . '/' . $config['plugin'] . '.module';
    if (file_exists(ROOT . $plugin_file)) {
      require_once ROOT . $plugin_file;
      $function = $config['plugin'] . '_init';
      $auth = new EjabberdAuth($config, $function($config[$config['plugin']]));
      $auth->run();
    }
    else {
      fwrite($err, "Plugin <{$plugin_file}> not found.\n");
    }
  }
  else {
    fwrite($err, 'Incomplete configuration: $config[\'plugin\'] must be set to <name>, and $config[<name>] populated.' . "\n");
  }
}