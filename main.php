#!/usr/bin/php
<?php
// by Aran, October 2012

// Bootstrap the phpBB system.
define('ROOT', __DIR__);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/phpbb-bridge/phpbb_bootstrap.php';

// Load the classes.
require_once __DIR__ . '/classes/JabberAuth.php';
require_once __DIR__ . '/classes/JabberAuthPhpBB.php';

// Launch the script.
$main = new JabberAuthPhpBB($auth, $db, $log_path);
$main->run();
