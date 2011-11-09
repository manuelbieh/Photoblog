<?php

require_once "../Includes/Bootstrap.inc.php";

	$router = new Application_Router(dirname(__FILE__).'/Includes/routes.xml', $app);
	$router->execute();
