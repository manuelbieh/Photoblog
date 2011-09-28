<?php

abstract class Application_Log_Writer {

	public $logMessages;

	public abstract function log($msg, $level);

}

?>