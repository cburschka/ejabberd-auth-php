<?php

define('ROOT', __DIR__ . '/../../');

require_once ROOT . 'plugins/session/session.module';

function create_key($salt) {
  require_once ROOT . '/config.php';
  $db = session_db($config['session']['mysql']);
  $plugin = $config['plugin'];
  $plugin_conf = $config['plugin_conf'];
  require_once ROOT . 'plugins/' . $plugin . '/' . $plugin . '.module';
  $function = $plugin . '_session';

  $username = function_exists($function) ? $function($plugin_conf) : NULL;
  if ($username) {
    $entry = ['user' => $username, 'secret' => sha1($salt . time() . mt_rand()), 'time' => time()];
    $query = $db->prepare(sprintf('INSERT INTO `%s` (`username`, `secret`, `created`) VALUES (:user, :secret, :time);', $config['session']['mysql']['tablename']));
    $query->execute([':user' => $entry['user'], ':secret' => $entry['secret'], ':time' => $entry['time']]);
    return $entry;
  }
  return FALSE;
}
