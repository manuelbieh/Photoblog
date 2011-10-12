<?php

class Application_Base {

	public static $autoloadDirs = array();
	public static $action;
	public static $controller;
	public static $i18n;
	private $globals = array(); 

	public function __construct() {

		spl_autoload_register(array($this, '__autoload'));

		if(!defined('__COREDIR__')) {
			define('__COREDIR__', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'));
		}

	}

	public function addGlobals($global) {
		if(is_array($global)) {
			foreach($global AS $key => $value) {
				$this->addGlobal($key, $value);
			}
		}
	}

	public function addGlobal($key, $value) {
		$this->globals[$key] = $value;
	}

	public function getGlobal($key) {
		return isset($this->globals[$key]) ? $this->globals[$key] : NULL;
	}

	public static function addAutoloadDir($dir) {
		self::$autoloadDirs[] = $dir;
	}

	public static function __autoload($className) {

		if(is_array(self::$autoloadDirs) && !empty(self::$autoloadDirs)) {

			foreach(self::$autoloadDirs AS $dir) {

				$classFile = realpath($dir . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className . '.php'));

				if(strpos($classFile, realpath($dir)) !== false) {

					if(is_file($classFile)) {
						require_once $classFile;
						return;
					}

				}

			}

		}

		$classFile = realpath(rtrim(self::getProjectDir(), '/') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className . '.php'));

		if($classFile !== false) {
			include_once $classFile;
			return;
		} else {
			$classFile = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className . '.php'));
		}

		if(strpos($classFile, realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') ) !== false) {

			if(is_file($classFile)) {
				require_once $classFile;
				return;
			}

		}


	}

	public function setLanguage($languageKey='de_DE') {
		
	}

	public function setController($controller) {
		self::$controller = $controller;
	}

	public function setAction($action) {
		self::$action = $action;
	
	}

	public function setProjectDir($dir) {
		if(is_dir($dir) && !defined('__PROJECTDIR__')) {
			define('__PROJECTDIR__', rtrim(realpath($dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
		}
	}

	public function getController() {
		return (isset(self::$controller)) ? self::$controller : NULL;
	}

	public function getAction() {
		return (isset(self::$action)) ? self::$action : NULL;
	}

	public function getProjectDir() {

		if(defined('__PROJECTDIR__')) {
			return __PROJECTDIR__;
		}
		$project	= rtrim(realpath(dirname($_SERVER['SCRIPT_FILENAME'])), DIRECTORY_SEPARATOR);
		$dirs		= explode('/', rtrim($project, '/'));
		$project	= end($dirs) == 'Admin' ? realpath($project . '/..') : $project;

		return rtrim(realpath(dirname($_SERVER['SCRIPT_FILENAME'])), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

	}

	public function getCoreDir() {
		return rtrim(realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "."), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	}

	public function getRelativePath() {
		$relPath = trim(str_replace($_SERVER['DOCUMENT_ROOT'], '', self::getProjectDir()), DIRECTORY_SEPARATOR . '/');
		return $relPath == '' ? '' : $relPath . '/';
	}

	public function getBaseURL() {
		$protocol = !isset($_SERVER['HTTPS']) ? 'http':'https';
		return $protocol .'://' . $_SERVER['SERVER_NAME'] . '/' . self::getRelativePath();
	}

	public function getPath($filepath) {

		// Experimental
		$docroot = self::getProjectDir();
		$coredir = self::getCoreDir();

		$filepath = str_replace(array($docroot, $coredir), '', $filepath);

		if($docroot == $coredir && Modules_Filesys::isFile($docroot . $filepath)) {

			// No project dir, use Celery default
			return $docroot . $filepath;

		} else {

			if(Modules_Filesys::isFile($docroot . $filepath)) {
				return $docroot . $filepath;
			} else if(Modules_Filesys::isFile($coredir . $filepath)) {
				return $coredir . $filepath;
			} else {
				return false;
			}

		}

	}


	public static function newObject($obj, $paramArray=array()) {

		$paramCount = count($paramArray);
		if(class_exists($obj) && is_callable($obj, true)) {

			switch($paramCount) {

				case 10:
					$ret = new $obj($paramArray[0], $paramArray[1], $paramArray[2], $paramArray[3], $paramArray[4], $paramArray[5], $paramArray[6], $paramArray[7], $paramArray[8], $paramArray[9]);
					break;

				case 9:
					$ret = new $obj($paramArray[0], $paramArray[1], $paramArray[2], $paramArray[3], $paramArray[4], $paramArray[5], $paramArray[6], $paramArray[7], $paramArray[8]);
					break;

				case 8:
					$ret = new $obj($paramArray[0], $paramArray[1], $paramArray[2], $paramArray[3], $paramArray[4], $paramArray[5], $paramArray[6], $paramArray[7]);
					break;

				case 7:
					$ret = new $obj($paramArray[0], $paramArray[1], $paramArray[2], $paramArray[3], $paramArray[4], $paramArray[5], $paramArray[6]);
					break;

				case 6:
					$ret = new $obj($paramArray[0], $paramArray[1], $paramArray[2], $paramArray[3], $paramArray[4], $paramArray[5]);
					break;

				case 5:
					$ret = new $obj($paramArray[0], $paramArray[1], $paramArray[2], $paramArray[3], $paramArray[4]);
					break;

				case 4:
					$ret = new $obj($paramArray[0], $paramArray[1], $paramArray[2], $paramArray[3]);
					break;

				case 3:
					$ret = new $obj($paramArray[0], $paramArray[1], $paramArray[2]);
					break;

				case 2:
					$ret = new $obj($paramArray[0], $paramArray[1]);
					break;

				case 1:
					$ret = new $obj($paramArray[0]);
					break;

				case 0:
					$ret = new $obj();
					break;

			}

		}

		return $ret;

	}

	public static function executeObjectCallback($obj, $method, $array=array()) {

		if(isset($obj)) {

			if(is_array($obj)) {
				$callbackObject = !is_object($obj[0]) ? self::newObject($obj[0], $obj[1]) : $obj[0];
			} else {
				$callbackObject = !is_object($obj) ? self::newObject($obj) : $obj;
			}

		}

		// instancecheck einführen. Callback MUSS Controller_Front implementieren!
		if(method_exists($callbackObject, $method) && is_callable(array($callbackObject, $method)) ) {
			return call_user_func_array(array($callbackObject, $method), (array) $array);
		}

	}


	public static function go($route) {

		$route = (strpos($route, 'http') === false) ? self::getBaseURL() . ltrim($route, '/') : $route;

		header("Location: " . $route);
		exit;

	}


}