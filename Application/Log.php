<?php

class Application_Log {

	const INFO = 1;			// Info messages
	const DEBUG = 2;		// Debug messages
	const NOTICE = 4;		// Notices
	const WARNING = 8;		// Warnings
	const ERROR = 16;		// Errors
	const CRITICAL = 32;	// Critical errors
	const FATAL = 64;		// Fatal errors
	const ALERT = 128;		// High severity errors

	public function __construct($logWriter) {

		if($logWriter instanceof Application_Log_Writer) {
			$this->logWriter = $logWriter;
		} else {
			throw new Exception ($logWriter . ' is not a Logwriter');
		}

	}

	public function log($msg, $level=self::INFO) {
		$this->logWriter->log($msg, $level);
	}

}


