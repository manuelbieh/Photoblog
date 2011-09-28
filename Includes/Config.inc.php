<?php

#require_once dirname(__FILE__) . "/Bootstrap.inc.php";


Application_Registry::load()->set('pdodb', new PDO("mysql:dbname=d0047892;host=85.13.134.221", "d0047892", "qwertz"));

//define('__LANG__', 'de_DE');
//$translate = new i18n_Translate(new i18n_Adapter_Array());
//$translate->setLocale(__LANG__);
//Application_Registry::load()->set('translate', $translate);

