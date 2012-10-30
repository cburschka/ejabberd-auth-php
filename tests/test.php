<?php
$stderr = fopen('php://stderr', 'w'); 
$in = fopen('php://stdin', 'r');
fwrite($stderr, "Enter a valid username: ");
$user = trim(fgets($in));
fwrite($stderr, "Enter the password: ");
$password = trim(fgets($in));

$str = array(
  array('isuser', $user),
  array('isuser', '123456789'),
  array('auth', $user, 'localhost', $password),
  array('auth', $user, 'localhost', '123456789'),
  
  // These should all fail cleanly.
  array('setpass', '123456789', 'localhost', '123456789'),
  array('tryregister', '123456789', 'localhost', '123456789'),
  array('removeuser', '123456789', 'localhost', '123456789'),
  array('removeuser3', '123456789', 'localhost', '123456789'),
);

foreach ($str as $command) {
  $command = implode(':', $command);
  print pack('n', strlen($command));
  print $command;
}
