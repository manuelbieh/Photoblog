<?php
$pdodb = new PDO("mysql:dbname=%db[name]%;host=%db[host]%", "%db[user]%", "%db[pass]%");
$pdodb->query("SET NAMES 'utf8'");
$pdodb->query("SET CHARACTER SET 'utf8'");

$translationAdapter = new i18n_Adapter_Array($app);
$translationAdapter->setLocale(Application_Settings::get('system/config/language', 1));
$translate  = new i18n_Translate($translationAdapter);

// NEVER EVER[!!!] change this value or you won't be able to login again.
define('__SALT__', '%settings[salt]%');
define('__UPDATEPASS__', '%settings[updatepass]%');


#$installPassword = '%settings[installpw]%';