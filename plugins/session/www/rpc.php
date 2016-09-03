<?php

define('SESS_ROOT', __DIR__ . '/../');

require_once SESS_ROOT . 'main.php';

if (!empty($_POST['salt']) && strlen($_POST['salt']) >= 16) {
  $entry = create_key($_POST['salt']);
  if ($entry) {
    header('Content-type: application/json');
    print json_encode($entry);
  }
  else {
    header('HTTP/1.1 403 Forbidden');
    print json_encode(['error' => 'no-session']);
  }
}
else {
  header('HTTP/1.1 403 Forbidden');
  print json_encode(['error' => 'no-request']);
}
