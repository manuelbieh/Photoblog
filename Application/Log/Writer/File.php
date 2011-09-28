<?php

class Application_Log_Writer_File extends Application_Log_Writer {

	public function __construct($path) {

		$this->path = $path;

	}

	public function log($msg, $level) {

		$data = sprintf("[%s] [%s] %s\n", date('Y-m-d H:i:s'), $level, $msg);

		if(is_writable($this->path)) {
			file_put_contents($this->path, $data, FILE_APPEND);
		} else {
			throw new Exception('Logfile not writable');
		}

	}

}

?>