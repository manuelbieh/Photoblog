<?php

class Model_System_Gateway_PDO {

	public $queryDelimiter = "\n-- QUERY END";

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

		$tables		= $this->getAllTables();
		$creates	= array();

		foreach($tables AS $table) {

			$s = $this->db->prepare("SHOW CREATE TABLE $table");
			$s->execute();

			foreach($s->fetchAll(PDO::FETCH_NUM) AS $createTableStatement) {
				$creates[] = $createTableStatement[1] .';' . $this->queryDelimiter;
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
				$data = array_map('addslashes', $data);
				$inserts[] = "INSERT INTO $table (`" . join('`,`', array_keys($data)) . "`) VALUES ('" . join("','", $data) ."');" . $this->queryDelimiter;
			}

		}

		return $inserts;

	}

	public function importDump($dump, $abortOnError=true) {

		$queries = explode($this->queryDelimiter, $dump);
		$status = true;
		var_dump($queries);
		foreach($queries AS $query) {

			$i++;

			if($i%200 == 0) {
				usleep(250000); // wait 250ms
			}

			$q = $this->query($query);
			$info[$i]['query'] = $query;
			if($q !== true) {
				$err = $this->db->errorInfo();
				if($abortOnError == true) {
					return $err[2];
				}
				$status[] = $err[2];
				$info[$i]['error'] = $err[2];
			}

		}

		$info['dump'] = $queries;
		return $info;

	}

}
