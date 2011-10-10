<?php
if(!defined('DIRECTORY_SEPARATOR')) {
	define('DIRECTORY_SEPARATOR', '/');
}
$rootDir = realpath(dirname(__FILE__) . '/../..');

require_once $rootDir . '/Application/Base.php';
$app = new Application_Base();
$app->setProjectDir(dirname(__FILE__) . "/..");

include_once $app->getProjectDir() . '../Includes/Config.inc.php';
include_once $app->getProjectDir() . '../Includes/Functions.inc.php';

$access = new Admin_Application_Access();
$access->setPermissionMapper(new Model_Permission_Mapper(new Model_Permission_Gateway_PDO($pdodb)));
