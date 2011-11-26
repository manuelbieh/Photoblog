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

if(!isset($_GET['__ajax']) && !isset($_GET['ajax'])) {
//echo '<div style="position: fixed; background: white; top: 0; left: 0; margin: 0; padding: 0 8px;">' . $benchmark->end() . '</div>';
echo $benchmark->end();
}


