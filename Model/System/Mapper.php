<?php

class Model_System_Mapper extends Model_Mapper_Abstract {

	public function query($query) {
		return $this->_db->query($query);
	}

	public function getAllTables() {
		return $this->_db->getAllTables();
	}

	public function exportTables() {
		return $this->_db->exportTables();
	}

	public function exportTableData() {
		return $this->_db->exportTableData();
	}

	public function importDump($dump) {
		return $this->_db->importDump($dump);
	}

}