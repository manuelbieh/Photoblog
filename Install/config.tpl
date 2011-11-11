<?php
$pdodb = new PDO("mysql:dbname=%db[name]%;host=%db[host]%", "%db[user]%", "%db[pass]%");
$pdodb->query("SET NAMES 'utf8'");
$pdodb->query("SET CHARACTER SET 'utf8'");

define('__SALT__', '%settings[hash]%');

$installPassword = '%settings[installpw]%';
