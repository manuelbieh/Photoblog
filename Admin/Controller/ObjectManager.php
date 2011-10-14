<?php

class Application_ObjectManager {

	public $observers = array();
	protected $objectStore = array();

	public function register($key, $object) {
		if(!isset($this->objectStore[$key])) {
			$this->set($key, $object);
		}
	}

	public function get($key) {
		return isset($this->objectStore[$key]) ? $this->objectStore[$key] : NULL;
	}

	public function set($key, $object) {
		$this->objectStore[$key] = $object;
		$this->notify('setObject', $key);
	}

	public function addObserver($observer) {
		array_push($this->observers, $observer);
	}

	public function notify($state, $additionalParams=NULL) {

		foreach((array) $this->observers AS $obs) {

			if(method_exists($obs, $state)) {

				$obs->$state(&$this, $additionalParams);

			}

		}

	}

}