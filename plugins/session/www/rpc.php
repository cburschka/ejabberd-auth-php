<?php

define('SESS_ROOT', __DIR__ . '/../');

require_once SESS_ROOT . 'main.php';

$entry = (!empty($_POST['salt']) && strlen($_POST['salt']) >= 16) ?
         create_key($_POST['salt']) : FALSE;

if ($entry) {
  header('Content-type: text/plain; charset=UTF-8');
  print json_encode($entry);
}
else {
  header('HTTP/1.1 403 Forbidden');
}
