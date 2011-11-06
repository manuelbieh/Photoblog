<?php

class Model_Extension_Gateway_PDO {

	public function __construct($dbh) {

		if($dbh) {
			$this->db = $dbh;
		} else {
			throw new Model_Database_Exception('Type of $dbh is not "Database" in ' . __CLASS__);
		}

	}

	public function getExtension($extension_key) {

		$s = $this->db->prepare("SELECT  * FROM cel_extensions WHERE extension_key = :extension_key");
		$s->execute(array('extension_key'=>$extension_key));
		return $s->fetch(PDO::FETCH_ASSOC);

	}

	public function fetchAll() {

		$s = $this->db->prepare("SELECT * FROM cel_extensions");
		$s->execute();
		return $s->fetchAll(PDO::FETCH_ASSOC);

	}

	public function fetchWhere() {}

	public function delete($extension_key) {

		$s = $this->db->prepare("DELETE FROM cel_extensions WHERE extension_key = :extension_key");
		return $s->execute(array('extension_key'=>$extension_key));

	}


	public function setProperties($extension_key, $proplist=array()) {

		if(is_array($proplist) && !empty($proplist)) {

			foreach($this->db->query("SHOW COLUMNS FROM cel_extensions", PDO::FETCH_ASSOC) AS $columnInfo) {
				$cols[] = $columnInfo['Field'];
			}

			$updates = array();
			if($extension_key != '') {
				$binds['extension_key'] = $extension_key;
			}

			foreach($proplist AS $field => $newValue) {

				if(in_array($field, $cols)) {
					$updates[]			= preg_replace("([^a-zA-Z0-9_])", '', $field) . " = :" . preg_replace("([^a-zA-Z0-9_])", '', $field);
					$insertColumns[]	= preg_replace("([^a-zA-Z0-9_])", '', $field);
					$insertValues[]		= $this->db->quote($newValue);
					$binds[':'.$field]		= $newValue;
				}

			}
			$updates = join(', ', $updates);

			$s = $this->db->prepare("INSERT INTO cel_extensions (" . join(',', $insertColumns) . ") VALUES (:" . join(', :', $insertColumns) . ") ON DUPLICATE KEY UPDATE $updates");
			$s->execute($binds);

			if((int) $s->errorCode() === 0) {

				return $extension_key;

			} else {

				return false;

			}

		}

	}


}
