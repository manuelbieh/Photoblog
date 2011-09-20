<?php

require_once dirname(__FILE__) . "/Bootstrap.inc.php";

Application_Registry::load()->set('pdodb', new PDO("mysql:dbname=%db[name]%;host=%db[host]%", "%db[user]%", "%db[pass]%"));