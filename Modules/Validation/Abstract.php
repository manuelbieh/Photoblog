<?php

class Modules_Validation_Abstract {

	public $errors = array();

	public function addError($error) {
		$this->errors[] = $error;
	}

	public function getErrors() {
		return $this->errors;
	}

}

?>