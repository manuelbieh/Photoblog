<?php
$pdodb = new PDO("mysql:dbname=%db[name]%;host=%db[host]%", "%db[user]%", "%db[pass]%");
$pdodb->query("SET NAMES 'utf8'");
$pdodb->query("SET CHARACTER SET 'utf8'");

// NEVER EVER[!!!] change this value or you won't be able to login again.
define('__SALT__', '%settings[salt]%');

#$installPassword = '%settings[installpw]%';