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

		$this->_data[$key] = $value;

	}

	public function __get($key) {

		if(isset($this->_data[$key])) {
			return $this->_data[$key];
		}

	}

}