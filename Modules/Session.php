<?php

class Modules_Session {

	private static $instance = NULL;
	protected static $savePath;
	protected $session;

	protected function __construct() {

		$_defaultSavePath = session_save_path();

		if(is_readable($_defaultSavePath) == false && is_readable($_SERVER['DOCUMENT_ROOT'] . $_defaultSavePath) == false ) {

			$dir = rtrim($_SERVER['DOCUMENT_ROOT'], ' /') . '/' . self::$savePath;

			if(!is_dir($dir)) {
				mkdir($dir, 0755, true);
				chmod($dir, 0755);
			}

			session_save_path($dir);

		} else if(is_readable(rtrim($_SERVER['DOCUMENT_ROOT'], '/ ') . $_defaultSavePath)) {

			session_save_path(rtrim($_SERVER['DOCUMENT_ROOT'], '/ ') . $_defaultSavePath);

		}

		#@session_start();
		session_start();
		$this->session = &$_SESSION;

	}

	public static function getInstance($savePath='sesstmp') {

		self::$savePath = $savePath;

		if(self::$instance === NULL) {
			self::$instance = new Modules_Session();
		}

		return self::$instance;

	}

	public function destroy() {
		session_destroy();
		return $this;
	}

	public function free($key=NULL) {
		if($key === NULL) {
			unset($_SESSION);
			session_unset();
		} else {
			unset($_SESSION[$key]);
		}
		return $this;
	}

	public function kill() {
		$this->destroy();
		$this->free();
		return $this;
	}


	public function setVar($key, $value='') {

		if(is_array($key)) {
			foreach($key AS $sKey => $value) {
				$_SESSION[$sKey] = $value;
				#$this->data[$skey] = $value;
				#$_SESSION[$sKey] = $this->data;
				
			}
		} else {
			$_SESSION[$key] = $value;
		}
		return $this;

	}

	public function getVar($key) {

		return $this->session[$key];

	}

	public function removeVar($key) {
		unset($_SESSION[$key]);
	}

	public function dump() {
		return $this;
	}

}