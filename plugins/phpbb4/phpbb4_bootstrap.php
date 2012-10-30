<?php
define('IN_PHPBB', TRUE);
$phpEx = 'php';

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require($phpbb_root_path . 'includes/startup.' . $phpEx);
require_once __DIR__ . '/noweb_user.php';

if (file_exists($phpbb_root_path . 'config.' . $phpEx))
{
	require($phpbb_root_path . 'config.' . $phpEx);
}

// Include files
require($phpbb_root_path . 'includes/class_loader.' . $phpEx);
require($phpbb_root_path . 'includes/di/processor/interface.' . $phpEx);
require($phpbb_root_path . 'includes/di/processor/config.' . $phpEx);

require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/functions_content.' . $phpEx);

require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/db/' . ltrim($dbms, 'dbal_') . '.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

// Set PHP error handler to ours
set_error_handler(defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler');

$phpbb_container = new ContainerBuilder();
$loader = new YamlFileLoader($phpbb_container, new FileLocator($phpbb_root_path.'/config'));
$loader->load('services.yml');

$processor = new phpbb_di_processor_config($phpbb_root_path . 'config.' . $phpEx, $phpbb_root_path, $phpEx);
$processor->process($phpbb_container);

// Setup class loader first
$phpbb_class_loader = $phpbb_container->get('class_loader');
$phpbb_class_loader_ext = $phpbb_container->get('class_loader.ext');

// Instantiate some basic classes
$user = new noweb_user;
$auth		= $phpbb_container->get('auth');
$db			= $phpbb_container->get('dbal.conn');

// Grab global variables, re-cache if necessary
$config = $phpbb_container->get('config');
set_config(null, null, null, $config);
set_config_count(null, null, null, $config);
