<?php

#require_once dirname(__FILE__) . "/Bootstrap.inc.php";

Application_Registry::load()->set('pdodb', new PDO("mysql:dbname=d0047892;host=85.13.134.221", "d0047892", "qwertz"));