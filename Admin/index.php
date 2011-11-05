<?php
set_time_limit(10);

include_once "../Application/Benchmark.php";

$benchmark = new Application_Benchmark();
$benchmark->start();

try {

#	include_once 'View/intro.php';
	include_once "Includes/Bootstrap.inc.php";

	$router = new Application_Router(dirname(__FILE__).'/Includes/routes.xml', $app);
	$router->addParams(array('app'=>$app));
	$router->execute();

#	include_once 'View/outro.php';

} catch (Exception $e) {

	var_dump($e);
	echo $e->getMessage();

}

echo $benchmark->end();


