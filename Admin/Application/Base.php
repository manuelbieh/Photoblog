<?php

class Admin_Application_Base extends Application_Base {

	public function __construct() {

		if(!defined('__ADMINDIR__')) {
			define('__ADMINDIR__', $this->getAdminDir());
		}

	}

	public function getAdminDir() {
		return realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..');
	}

	public function getAdminPath() {
		$relPath = trim(str_replace($_SERVER['DOCUMENT_ROOT'], '', self::getAdminDir()), DIRECTORY_SEPARATOR . '/');
		return $relPath == '' ? '' : $relPath . '/';
	}

	public function getAdminURL() {
		$protocol = !isset($_SERVER['HTTPS']) ? 'http':'https';
		$port = $_SERVER['SERVER_PORT'];
		return $protocol .'://' . $_SERVER['SERVER_NAME'] . '/' . self::getAdminPath();
	}

}