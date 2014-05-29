<?php

/**
 * Copy this file to config.php.
 *
 * plugin_conf will always require the `root_path` to the CMS you want to
 * authenticate with. Some systems require additional information; Drupal
 * needs the site directory (usually `default`).
 */

$config = [
 'log_path' => 'logs/',
 'plugin' => 'PLUGIN',
 'plugin_conf' => [
   'root_path' => '</path/to/site>',
   // if using redis authentication
   'redis' => [
	   'host' => 'localhost',
	   'port' => '6379'
   ]
 ],

 // Remove this section if you are not using session authentication.
 'session' => [
   'mysql' => [
     'dsn' => 'mysql:host=localhost;dbname=DATABASE;charset=utf8',
     'username' => 'USER',
     'password' => 'PASSWORD',
     'tablename' => 'TABLE',
   ],
   'timeout' => 60,
 ],
];
