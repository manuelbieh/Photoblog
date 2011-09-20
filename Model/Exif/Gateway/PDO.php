<?php

class Model_Exif_Gateway_PDO {

	public function __construct($dbh) {

		if($dbh) {
			$this->db = $dbh;
		} else {
			throw new Model_Database_Exception('Type of $db is not "Database".');
		}

	}

	public function getExifDataByPhotoId($id) {

		$s = $this->db->prepare("SELECT  * FROM cel_photo_exif WHERE photo_id = :photo_id");
		$s->execute(array('photo_id'=>(int) $id));
		return $s->fetch(PDO::FETCH_ASSOC);

	}

	public function setProperties($photo_id, $proplist) {

		if(is_array($proplist) && !empty($proplist)) {

			foreach($this->db->query("SHOW COLUMNS FROM cel_photo_exif", PDO::FETCH_ASSOC) AS $columnInfo) {
				$cols[] = $columnInfo['Field'];
			}

			$updates = array();
			if((int) $photo_id !== 0) {
				$binds['photo_id'] = $photo_id;
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

			$s = $this->db->prepare("INSERT INTO cel_photo_exif (" . join(',', $insertColumns) . ") VALUES (:" . join(', :', $insertColumns) . ") ON DUPLICATE KEY UPDATE $updates");
			$s->execute($binds);

			if($s->errorCode() == 0) {

				if($photo_id !== 0) {
					return $photo_id;
				} else {
					return $this->db->lastInsertId();
				}

			} else {

				return false;

			}

		}

	}

	public function fetchAll() {

		$s = $this->db->prepare("SELECT * FROM cel_photo_exif");
		$s->execute();
		return $s->fetchAll(PDO::FETCH_ASSOC);

	}

	public function delete($photo_id) {

		$s = $this->db->prepare("DELETE FROM cel_photo_exif WHERE photo_id = :photo_id");
		return $s->execute(array('photo_id'=>$photo_id));

	}

}
