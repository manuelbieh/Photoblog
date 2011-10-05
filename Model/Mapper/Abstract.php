<?php

class Model_Mapper_Abstract {

	protected $_db;

	public function __construct($_db=NULL) {

		$this->setDatabase($_db);

	}

	public function setDatabase($_db) {

		if (is_string($_db)) {
			$_db = new $_db();
		}

		$this->_db = $_db;
		return $this;

	}
 
 	public function getDatabase() {

		return $this->_db;

	}
 
	// Deprecated. Use getDatabase() instead.
	public function getDbTable() {

		return $this->getDatabase();

	}

}