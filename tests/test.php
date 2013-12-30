#!/usr/bin/env php
<?php

if ($_SERVER['argc'] != 3) {
  die(
'Usage:
  test.php <valid-user> <valid-pass>
');
}

$valid_account = [
  'user' => $_SERVER['argv'][1],
  'password' => $_SERVER['argv'][2],
];

require_once __DIR__ . '/unit_test.php';

(new UnitTest($valid_account))->run();
