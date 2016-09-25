#!/usr/bin/php
<?php

define('ROOT', __DIR__ . '/');
require_once ROOT . 'core/EjabberdAuth.php';
require_once ROOT . 'core/EjabberdAuthBridge.php';

main();

function main() {
  require_once ROOT . 'config.php';
  $plugin_file = 'plugins/' . $config['plugin'] . '/' . $config['plugin'] . '.module';
  if (!file_exists(ROOT . $plugin_file)) {
    return fwrite(STDERR, "Plugin <{$plugin_file}> not found.\n");
  }

  require_once ROOT . $plugin_file;
  $function = $config['plugin'] . '_init';
  $bridge = $function($config['plugin_conf']);

  if (!empty($config['session'])) {
    require_once 'plugins/session/session.module';
    $session = session_init($config['session']);
  }
  else $session = NULL;

  $main = new EjabberdAuth($config, $bridge, $session);
  if ($_SERVER['argc'] > 1) {
    $result = $main->execute(array_slice($_SERVER['argv'], 1));
    exit($result ? 0 : 1);
  }
  $main->run();
}
