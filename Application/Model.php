<?php

class Application_Model {

	public $_data = array();

	public function getKeys() {
		if(isset($this->_data)) {
			return array_keys($this->_data);
		} else {
			return false;
		}
	}

	public function __set($key, $value) {

		if(method_exists($this, 'set' . ucfirst($key))) {
			$method = 'set' . ucfirst($key);
			return $this->$method($value);
		}

		$this->_data[$key] = $value;

	}

	public function __get($key) {

		if(method_exists($this, 'get' . ucfirst($key))) {
			$method = 'get' . ucfirst($key);
			return $this->$method();
		}

		if(isset($this->_data[$key])) {
			return $this->_data[$key];
		}

	}

	public function __call($name, $arguments) {

		$property = strtolower(substr($name, 3, strlen($name)-3));

		if(strpos($name, 'get') === 0 && !method_exists($this, $name)) {
			return $this->$property;
		} else if (strpos($name, 'set') === 0 && !method_exists($this, $name)) {
			$this->$property = $arguments[0];
		} else {
			return call_user_func_array(array($this, $name), $arguments);
		}

/*
*/

	}


}