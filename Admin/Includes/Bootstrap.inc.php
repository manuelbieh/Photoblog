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

if(Modules_Session::getInstance()->getVar('userdata')->user_id) {

	$userSettings = new Model_User_Mapper(new Model_User_Gateway_PDO($pdodb));
	$user = $userSettings->find(Modules_Session::getInstance()->getVar('userdata')->user_id, new Model_User);
	if($user->language != false) {
		$translate->getAdapter()->setLocale($user->language);
	}

}

$app->objectManager->register('Admin_Application_Access', $access);
$app->objectManager->register('Datastore', $pdodb);
$app->objectManager->register('Translate', $translate);