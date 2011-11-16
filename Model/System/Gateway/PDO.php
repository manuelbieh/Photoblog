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

	public function getAllTables() {

		$s = $this->db->prepare("SHOW TABLES");
		$s->execute();
		$tbls = array();
		foreach($s->fetchAll(PDO::FETCH_COLUMN) AS $tbl) {
			$tbls[] = $tbl;
		}

		return $tbls;

	}

	public function exportTables() {

		$tables = $this->getAllTables();
		foreach($tables AS $table) {

			$s = $this->db->prepare("SHOW CREATE TABLE $table");
			$s->execute();

			$creates = array();
			foreach($s->fetchAll(PDO::FETCH_NUM) AS $createTableStatement) {
				$creates[] = $createTableStatement[1];
			}

		}

		return $creates;

	}

	public function exportTableData() {

		$tables		= $this->getAllTables();
		$inserts	= array();

		foreach($tables AS $table) {

			$tblStmt = $this->db->prepare("SELECT * FROM " . $table);
			$tblStmt->execute();

			foreach($tblStmt->fetchAll(PDO::FETCH_ASSOC) AS $data) {
				// addslashes to values!!!
				$inserts[] = "INSERT INTO $table (`" . join('`,`', array_keys($data)) . "`) VALUES ('" . join("','", $data) ."');";
			}

		}

		return $inserts;

	}

}
