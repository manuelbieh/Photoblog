<?php

class Modules_Image {

	public $imageObject;

	public function __construct($file) {

		if(class_exists('IMagick')) { 
			$this->imageObject = new IMagick($file);
		} else {
			$this->imageObject = new Modules_Image_GDLib($file);
		}

	}

	public function __get($member) {
		return $this->imageObject->$member;
	}

	public function __set($property, $value) {
		$this->imageObject->$property = $value;
	}

	public function __call($method, $params=array()) {

		return call_user_func_array(array($this->imageObject, $method), $params);

	}

}

?>
