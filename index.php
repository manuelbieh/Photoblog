<?php
set_time_limit(0);
include_once "Application/Benchmark.php";

$benchmark = new Application_Benchmark();

$benchmark->start();

try {

	if(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
		ob_start('ob_gzhandler');
	} else {
		ob_start();
	}

	include_once "Includes/Bootstrap.inc.php";

	$router = new Application_Router(dirname(__FILE__).'/Includes/routes.xml', $app);
	$router->execute();

	echo $benchmark->end();

	ob_end_flush();


} catch (Exception$e) {

	echo $e->getMessage();

}

