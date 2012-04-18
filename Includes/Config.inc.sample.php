<?php

try {
	$pdodb = new PDO("mysql:dbname=myDBName;host=localhost", "myDBUser", "MyDBPass");
	$pdodb->query("SET NAMES 'utf8'");
	$pdodb->query("SET CHARACTER SET 'utf8'");
} catch(Exception $e) {
	die(__('Database connection could not be established'));
}

$translationAdapter = new i18n_Adapter_Array($app);
$translationAdapter->setLocale(Application_Settings::get('system/config/language', 1));
$translate  = new i18n_Translate($translationAdapter);

Application_Registry::load()->set('pdodb', $pdodb);

define('__SALT__', 'ch4ng3m3!');


//define('__LANG__', 'de_DE');
//$translate = new i18n_Translate(new i18n_Adapter_Array());
//$translate->setLocale(__LANG__);
//Application_Registry::load()->set('translate', $translate);

