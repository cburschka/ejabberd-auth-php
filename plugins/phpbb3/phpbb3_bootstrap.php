<?php
define('IN_PHPBB', TRUE);
global $phpEx, $db, $cache, $user, $config;
$phpEx = 'php';

if (!file_exists($phpbb_root_path . 'includes/startup.' . $phpEx)) {
  file_put_contents('php://stderr', "phpBB not found at <{$phpbb_root_path}>.\n");
  exit;
}
require($phpbb_root_path . 'includes/startup.' . $phpEx);

if (file_exists($phpbb_root_path . 'config.' . $phpEx))
{
	require($phpbb_root_path . 'config.' . $phpEx);
}

if (!defined('PHPBB_INSTALLED'))
{
  file_put_contents('php://stderr', "phpBB needs to be installed first.\n");
	exit;
}

// Include files
require(__DIR__ . '/noweb_user.php');
require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.' . $phpEx);
require($phpbb_root_path . 'includes/cache.' . $phpEx);
require($phpbb_root_path . 'includes/auth.' . $phpEx);
require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/functions_content.' . $phpEx);

require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

// Instantiate some basic classes
$user		= new noweb_user();
$auth		= new auth();
$db		= new $sql_db();
$cache		= new cache();

// Connect to DB
$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, defined('PHPBB_DB_NEW_LINK') ? PHPBB_DB_NEW_LINK : false);
// We do not need this any longer, unset for safety purposes
unset($dbpasswd);
$config = $cache->obtain_config();
