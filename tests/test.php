#!/usr/bin/env php
<?php

if ($_SERVER['argc'] != 4) {
  die(
'Usage:
  test.php <valid-user> <valid-domain> <valid-pass>
');
}

$valid_account = [
  'user' => $_SERVER['argv'][1],
  'domain' => $_SERVER['argv'][2],
  'password' => $_SERVER['argv'][3],
];

require_once __DIR__ . '/unit_test.php';

(new UnitTest($valid_account))->run();
