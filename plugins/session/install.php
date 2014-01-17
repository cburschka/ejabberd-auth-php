<?php

define('ROOT', __DIR__ . '/../../');

require_once ROOT . 'plugins/session/session.module';
require_once ROOT . 'config.php';

$db = session_db($config['session']['mysql']);

$db->exec(sprintf(<<<SQL
CREATE TABLE `%s` (
	username VARCHAR(255),
	secret VARCHAR(40),
	created INT,
	PRIMARY KEY(username, secret),
	INDEX(created)
);
SQL
, $config['session']['mysql']['tablename']));
