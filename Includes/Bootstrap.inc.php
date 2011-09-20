<?php
if(!defined('DIRECTORY_SEPARATOR')) {
	define('DIRECTORY_SEPARATOR', '/');
}
$rootDir = realpath(dirname(__FILE__) . '/../');

require_once $rootDir . '/Application/Base.php';
$app = new Application_Base();

include_once 'Config.inc.php';
include_once 'Functions.inc.php';

#spl_autoload_register('Application_Base::__autoload');
