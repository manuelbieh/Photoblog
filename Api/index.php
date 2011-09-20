<?php

include_once "Api.php";
include_once "../Includes/Bootstrap.inc.php";

include "../View/intro.php";
$router = new Application_Router(dirname(__FILE__).'/../Includes/routes.xml');
$router->execute();

include "../View/outro.php";