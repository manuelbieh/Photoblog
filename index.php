<?php
set_time_limit(0);
include_once "Application/Benchmark.php";

$benchmark = new Application_Benchmark();

$benchmark->start();

try {

	#include_once 'View/intro.php';
	include_once "Includes/Bootstrap.inc.php";

	$router = new Application_Router(dirname(__FILE__).'/Includes/routes.xml');
	$router->execute();

	#include_once 'View/outro.php';

	echo $benchmark->end();


} catch (Exception$e) {

	echo $e->getMessage();

}

