<?php
set_time_limit(10);

try {

	include_once 'View/intro.php';
	include_once "Includes/Bootstrap.inc.php";

	$router = new Application_Router(dirname(__FILE__).'/Includes/routes.xml');
	$router->execute();

	include_once 'View/outro.php';

} catch (Exception $e) {

	echo $e->getMessage();

}