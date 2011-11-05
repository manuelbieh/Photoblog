<?php

class Model_Photo_Gateway_PDO {

	public function __construct($dbh) {

		if($dbh) {
			$this->db = $dbh;
		} else {
			throw new Model_Database_Exception('Type of $db is not "Database" in ' . (__FILE__));
		}

	}

	public function getPhotoById($id) {

		$s = $this->db->prepare("SELECT *, date_format(date_publish, '%Y-%m-%d %H:%i:%s') AS date_publish, (SELECT count(comment_id) FROM cel_photo_comments WHERE photo_id = :photo_id) AS comment_count FROM cel_photo_photos WHERE photo_id = :photo_id");
		$s->execute(array('photo_id'=>(int) $id));
		return $s->fetch(PDO::FETCH_ASSOC);

	}

	public function setProperties($photo_id, $proplist) {

		if(is_array($proplist) && !empty($proplist)) {

			foreach($this->db->query("SHOW COLUMNS FROM cel_photo_photos", PDO::FETCH_ASSOC) AS $columnInfo) {
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

			$s = $this->db->prepare("INSERT INTO cel_photo_photos (" . join(',', $insertColumns) . ") VALUES (:" . join(', :', $insertColumns) . ") ON DUPLICATE KEY UPDATE $updates");
			$s->execute($binds);

			if((int) $s->errorCode() === 0) {

				if((int) $photo_id !== 0) {

					return $photo_id;

				} else {

					return $this->db->lastInsertId();

				}

			} else {

				return false;

			}

		}

	}

	public function fetchAll($columns=NULL, $where=NULL) {

		$s = $this->db->prepare("SELECT *, date_format(date_publish, '%Y-%m-%d %H:%i:%s') AS date_publish, (SELECT count(comment_id) FROM cel_photo_comments WHERE photo_id = cel_photo_photos.photo_id) AS comment_count FROM cel_photo_photos ORDER BY photo_id ASC");
		$s->execute();
		return $s->fetchAll(PDO::FETCH_ASSOC);

	}

	public function fetchWhere($where, $limit=NULL) {

		if(is_array($where)) {
			$w = array();
			foreach($where AS $field => $value) {
				$w[] = preg_replace("([^a-zA-Z0-9_])", '', $field) . ' = :' . preg_replace("([^a-zA-Z0-9_])", '', $field);
				$prep[preg_replace("([^a-zA-Z0-9_])", '', $field)] = $value;
			}
			$w = " WHERE " . join(' AND ', $w);
		}
		$s = $this->db->prepare("SELECT *, date_format(date_publish, '%Y-%m-%d %H:%i:%s') AS date_publish, (SELECT count(comment_id) FROM cel_photo_comments WHERE photo_id = cel_photo_photos.photo_id) AS comment_count FROM cel_photo_photos $w ORDER BY photo_id ASC");
		$s->execute($prep);
		return $s->fetchAll(PDO::FETCH_ASSOC);

	}

	public function deletePhoto($photo_id) {

		$s = $this->db->prepare("DELETE FROM cel_photo_photos WHERE photo_id = :photo_id");
		return $s->execute(array('photo_id'=>$photo_id));

	}

}
