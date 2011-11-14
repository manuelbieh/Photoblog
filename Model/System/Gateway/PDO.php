<?php

class Model_System_Gateway_PDO {

	public function __construct($dbh) {

		if($dbh) {
			$this->db = $dbh;
		} else {
			throw new Model_Database_Exception('Type of $db is not "Database".');
		}

	}

	public function query($query) {

		if($this->db->query($query) === false) {
			$error = $this->db->errorInfo();
			return $error[2];
		}
		return true;

	}

}
