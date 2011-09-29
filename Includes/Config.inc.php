<?php

#require_once dirname(__FILE__) . "/Bootstrap.inc.php";


$pdodb = new PDO("mysql:dbname=d0047892;host=85.13.134.221", "d0047892", "qwertz");
$pdodb->query("SET NAMES 'utf8'");
$pdodb->query("SET CHARACTER SET 'utf8'");

Application_Registry::load()->set('pdodb', $pdodb);

//define('__LANG__', 'de_DE');
//$translate = new i18n_Translate(new i18n_Adapter_Array());
//$translate->setLocale(__LANG__);
//Application_Registry::load()->set('translate', $translate);

