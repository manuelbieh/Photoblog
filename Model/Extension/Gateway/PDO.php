<?php

class Model_Extension_Gateway_PDO {

	public function __construct($dbh) {

		if($dbh) {
			$this->db = $dbh;
		} else {
			throw new Model_Database_Exception('Type of $db is not "Database".');
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

	public function delete($photo_id) {

		$s = $this->db->prepare("DELETE FROM cel_photo_exif WHERE photo_id = :photo_id");
		return $s->execute(array('photo_id'=>$photo_id));

	}

}
