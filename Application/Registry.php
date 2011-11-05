<?php

class Application_Registry {

	private static $objects = array();

	public static function &load() {

		static $me;
		if(!is_object($me)) {
			$className = __CLASS__;
			$me = new $className();
		}
		return $me;

	}

	public function set($name, &$object) {
		#$this->objects[$name] =& $object;
		self::$objects[$name] =& $object;
	}

	public function &get($name) {
		return self::$objects[$name];
	}

	public static function getInstance($class) {

		$registry =& call_user_func(__CLASS__.'::load()');
		#$registry =& Application_Registry::load();
		$args = func_get_args();
		if(count($args) > 1) {
			$ref = array();
			foreach($args as $arg) {
				$ref[] =& $registry->get($arg);
			}
		} else {
			$ref =& $registry->get($args[0]);
		}
		return $ref;

	}

}

#$registry =& registry::load();