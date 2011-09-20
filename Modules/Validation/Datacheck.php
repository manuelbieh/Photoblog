<?php

class Modules_Validation_Datacheck extends Modules_Validation_Abstract {

	public $object = __CLASS__;
	public $errors = array();

	public function __construct() {

		

	}

	public function checkForExistance($tbl, $col, $val) {
		// SELECT $col FROM $tbl WHERE $col = $val
	}

}


