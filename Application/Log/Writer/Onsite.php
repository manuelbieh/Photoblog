<?php

class Application_Log_Writer_Onsite extends Application_Log_Writer {

	public function __construct() {

	}

	public function log($msg, $level) {

		$data = sprintf("[%s] [%s] %s\n", date('Y-m-d H:i:s'), $level, $msg);

		$this->logMessages[$level] = $data;

	}

}

?>