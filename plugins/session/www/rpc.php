<?php

define('ROOT', __DIR__ . '/../../../');
define('SESS_ROOT' , __DIR__ . '/../');

main();

function main() {
  require_once ROOT . 'config.php';
  require_once SESS_ROOT . 'session.module';
  $bridge = session_init($config['session']);
  $plugin = $config['session']['plugin'];
  $plugin_conf = $config['session']['plugins'][$plugin_conf];
  $plugin_id = $plugin_conf['file'];
  require_once SESS_ROOT . 'plugins/' . $plugin_id . '/' . $plugin_id . '.module';
  $function = $plugin_id . '_authenticate';
  $username = $function($plugin_conf);
  if ($username) {
    $entry = ['user' => $username, 'secret' => sha1($_POST['salt'] . time() . mt_rand()), 'time' => time()];
    $bridge->create($entry);
    header('Content-type: text/plain; charset=UTF-8');
    print json_encode($entry);
  }
  else header('HTTP/1.1 403 Forbidden');
}
