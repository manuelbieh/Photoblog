<?php

class Model_Comment_Mapper extends Model_Mapper_Abstract {

	protected $blacklist = array('date', 'time');

	public function find($id, Model_Comment $model) {

		$model->comment_id = $id;

		$data = $this->_db->getCommentById($id);
		if($data == false) {

			return false;

		} else {

			foreach($data AS $prop => $value) {
				$model->$prop = $value;
			}

			return $model;

		}

	}

	public function findByPhoto($photo_id) {

		foreach($this->_db->findCommentsByPhotoId($photo_id) AS $entry => $data) {

			$comment = new Model_Comment;

			foreach($data AS $key => $value) {
				$comment->$key = $value;
			}
			$comments[] = $comment;
		}

		return $comments;

	}

	public function fetchAll() {

		foreach($this->_db->fetchAll() AS $entry => $data) {

			$comment = new Model_Comment;

			foreach($data AS $key => $value) {
				$comment->$key = $value;
			}
			$comments[] = $comment;
		}

		return $comments;

	}


	public function save(Model_Comment $model) {

		foreach($model->_data AS $key => $value) {
			if(!in_array($key, $this->blacklist)) {
				$data[$key] = $value;
			}
		}

		$this->_db->setProperties($model->comment_id, $data);

	}


	public function delete($comment_id) {

		return $this->_db->deleteComment($comment_id);

	}

}