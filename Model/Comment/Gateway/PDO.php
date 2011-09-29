<?php

class Model_Comment_Gateway_PDO {

	public function __construct($dbh) {

		if($dbh) {
			$this->db = $dbh;
		} else {
			throw new Model_Database_Exception('Type of $db is not "Database".');
		}

	}


	public function getCommentById($id) {

		$s = $this->db->prepare("SELECT *, date_format(datetime, '%Y-%m-%d %H:%i:%s') AS datetime, date_format(datetime, '%Y-%m-%d') AS date, date_format(datetime, '%H:%i:%s') AS time FROM cel_photo_comments WHERE comment_id = :comment_id");
		$s->execute(array('comment_id'=>(int) $id));
		return $s->fetch(PDO::FETCH_ASSOC);

	}


	public function setProperties($comment_id, $proplist) {

		if(is_array($proplist) && !empty($proplist)) {

			$updates = array();
			if((int) $comment_id !== 0) {
				$binds['comment_id'] = $photo_id;
			}
			foreach($proplist AS $field => $newValue) {
				$updates[]			= preg_replace("([^a-zA-Z0-9_])", '', $field) . " = :" . preg_replace("([^a-zA-Z0-9_])", '', $field);
				$insertColumns[]	= preg_replace("([^a-zA-Z0-9_])", '', $field);
				$insertValues[]		= $this->db->quote($newValue);
				$binds[':'.$field]		= $newValue;
			}
			$updates = join(', ', $updates);

			$s = $this->db->prepare("INSERT INTO cel_photo_comments (" . join(',', $insertColumns) . ") VALUES (:" . join(', :', $insertColumns) . ") ON DUPLICATE KEY UPDATE $updates");
			$s->execute($binds);

		}

	}


	public function fetchAll($columns=NULL, $where=NULL) {

		$s = $this->db->prepare("SELECT *, date_format(datetime, '%Y-%m-%d %H:%i:%s') AS datetime FROM cel_photo_comments ORDER BY comment_id ASC");
		$s->execute();
		return $s->fetchAll(PDO::FETCH_ASSOC);

	}

	public function findCommentsByPhotoId($photo_id) {

		$s = $this->db->prepare("SELECT *, date_format(datetime, '%Y-%m-%d %H:%i:%s') AS datetime FROM cel_photo_comments WHERE photo_id = :photo_id ORDER BY comment_id ASC");
		$s->execute(array('photo_id'=>(int) $photo_id));
		return $s->fetchAll(PDO::FETCH_ASSOC);

	}

	public function deleteComment($comment_id) {

		$s = $this->db->prepare("DELETE FROM cel_photo_comments WHERE comment_id = :comment_id");
		return $s->execute(array('comment_id'=>$comment_id));

	}

}
