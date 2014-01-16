#!/usr/bin/php
<?php

define('ROOT', __DIR__ . '/');
require_once ROOT . 'core/EjabberdAuth.php';
require_once ROOT . 'core/EjabberdAuthBridge.php';

main();

function main() {
  require_once ROOT . 'config.php';
  $bridges = [];
  foreach ($config as $domain => $plugins) {
    $bridges[$domain] = [];
    foreach ($plugins as $settings) {
      $plugin_file = 'plugins/' . $settings['plugin'] . '/' . $settings['plugin'] . '.module';
      if (file_exists(ROOT . $plugin_file)) {
        require_once ROOT . $plugin_file;
        $function = $settings['plugin'] . '_init';
        $bridges[$domain][] = $function($settings['config']);
      }
      else {
        return fwrite(STDERR, "Plugin <{$plugin_file}> not found.\n");
      }
    }
  }
  (new EjabberdAuth($meta, $bridges))->run();
}
