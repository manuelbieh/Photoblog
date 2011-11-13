<?php

class Model_Update_Gateway_PDO {

	public function __construct($dbh) {

		if($dbh) {
			$this->db = $dbh;
		} else {
			throw new Model_Database_Exception('Type of $db is not "Database".');
		}

	}

	public function query($query) {

		return $this->db->query($query);

	}

}
