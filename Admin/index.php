<?php
set_time_limit(30);

include_once "../Application/Benchmark.php";

$benchmark = new Application_Benchmark();
$benchmark->start();

try {

	include_once "Includes/Bootstrap.inc.php";

	$router = new Application_Router(dirname(__FILE__).'/Includes/routes.xml', $app);
	$router->addParams(array('app'=>$app));
	$router->execute();

} catch (Exception $e) {

	var_dump($e);
	echo $e->getMessage();

}

echo $benchmark->end();


