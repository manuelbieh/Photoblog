<?php

class Application_Request {

	protected $data = array();
	protected static $instance;

	protected function __construct($requestObject) {
		$this->setRequestObject($requestObject);
	}

	protected function __clone() {}

	public function getInstance($requestObject='Modules_Request_HTTP') {

         if(self::$instance === NULL) {
			$obj = __CLASS__;
             self::$instance = new $obj($requestObject);
         }

         return self::$instance;

	}

	public function setRequestObject($requestObject) {
		$this->request = is_object($requestObject) ? $requestObject : new $requestObject;
	}

	public function __call($method, $params) {

		if(is_callable(array($this->request, $method))) {
			return call_user_func_array(array($this->request, $method), $params);
		} else {
			return;
		}

	}

	public function __get($property) {
		return $this->data[$property];
	}


	public function __set($property, $value) {
		$this->data[$property] = $value;
	}


}
