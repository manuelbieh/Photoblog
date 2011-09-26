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
