<?php

class UnitTest {
  function __construct($valid_account) {
    $spec = [
      0 => ['pipe', 'r'],  // stdin is a pipe that the child will read from
      1 => ['pipe', 'w'],  // stdout is a pipe that the child will write to
      2 => STDERR,         // forward stderr directly
    ];
    $cmd = __DIR__ . '/../main.php';
    $this->_process = proc_open($cmd, $spec, $this->pipes);
    $this->_input = $this->pipes[0];
    $this->_output = $this->pipes[1];
    $this->valid = $valid_account;
    $this->successes = 0;
    $this->failures = 0;
    $this->cases = 0;
  }

  function run() {
    foreach (get_class_methods($this) as $method) {
      if (strpos($method, 'Test') === 0) {
        $this->$method();
      }
    }
    printf("%d tests, %d passed, %d failed\n", $this->cases, $this->successes, $this->failures);
  }

  function _send_request($request) {
    $request = implode(':', $request);
    fwrite($this->_input, pack('n', strlen($request)));
    fwrite($this->_input, $request);
    $result = unpack('n2x', fread($this->_output, 4));
    return [$result['x1'], $result['x2']];
  }

  function assert($request, $success, $comment) {
    $result = $this->_send_request($request);
    if ($result[0] == 2 and $result[1] == $success) {
      $this->successes++;
      printf("\033[0;32mPASS #%d: %s\033[0m\n", 1+$this->cases, $comment);
    }
    else {
      $this->failures++;
      printf("\033[0;31mFAIL #%d: %s\033[0m\n", 1+$this->cases, $comment);
    }
    $this->cases++;
  }

  function TestUserGood() {
    $this->assert(['isuser', $this->valid['user'], 'localhost'], TRUE, 'isuser with valid username');
  }

  function TestUserBad() {
    $this->assert(['isuser', '123456789', 'localhost'], FALSE, 'isuser with bad username');
  }

  function TestAuthGood() {
    $this->assert(['auth', $this->valid['user'], 'localhost', $this->valid['password']], TRUE, 'auth with valid password');
  }

  function TestAuthBadUser() {
    $this->assert(['auth', '123456789', 'localhost', '123456789'], FALSE, 'auth with bad username');
  }

  function TestAuthBadPass() {
    $this->assert(['auth', $this->valid['user'], 'localhost', '123456789'], FALSE, 'auth with bad password');
  }
  
  function TestSetPass() {
    $this->assert(['setpass', '123456789', 'localhost', '123456789'], FALSE, 'attempt to set password (fail)');
  }

  function TestRegister() {
    $this->assert(['tryregister', '123456789', 'localhost', '123456789'], FALSE, 'attempt to create account (fail)');
  }

  function TestRemove() {
    $this->assert(['removeuser', '123456789', 'localhost', '123456789'], FALSE, 'attempt to delete account (fail)');
  }

  function TestRemove3() {
    $this->assert(['removeuser3', '123456789', 'localhost', '123456789'], FALSE, 'attempt to login and delete account (fail)');
  }
}
