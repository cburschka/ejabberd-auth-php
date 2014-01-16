<?php

define('ROOT', __DIR__ . '/../../');

function create_key($salt) {
  require_once ROOT . 'config.php';
  require_once ROOT . 'plugins/session/session.module';
  $bridge = session_init($config['session']);
  $plugin = $config['session']['plugin'];
  $plugin_conf = $config[$plugin_conf];
  $plugin_id = $plugin_conf['file'];
  require_once ROOT . 'plugins/' . $plugin_id . '/' . $plugin_id . '.module';
  $function = $plugin_id . '_session';
  $username = function_exists($function) ? $function($plugin_conf) : NULL;
  if ($username) {
    $entry = ['user' => $username, 'secret' => sha1($salt . time() . mt_rand()), 'time' => time()];
    $bridge->create($entry);
    return $entry;
  }
  return FALSE;
}
