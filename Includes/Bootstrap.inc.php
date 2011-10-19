<?php
if(!defined('DIRECTORY_SEPARATOR')) {
	define('DIRECTORY_SEPARATOR', '/');
}
$rootDir = realpath(dirname(__FILE__) . '/../');

require_once $rootDir . '/Application/Base.php';
$app = new Application_Base();
$app->setProjectDir(dirname(__FILE__ . "/.."));

include_once 'Config.inc.php';
include_once 'Functions.inc.php';

$app->objectManager->register('Datastore', $pdodb);

#spl_autoload_register('Application_Base::__autoload');
