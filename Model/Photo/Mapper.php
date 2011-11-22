<?php

class Model_Photo_Mapper extends Model_Mapper_Abstract {

	protected $blacklist = array();

	public function find($id, Model_Photo $model) {

		$data = $this->_db->getPhotoById($id);

		if(isset($data) && is_array($data)) {

			$model->photo_id = $id;

			foreach($data AS $prop => $value) {
				$model->$prop = $value;
			}

		} else if($data == false) {

			return false;

		}

		return $model;

	}


	public function fetchAll() {

		foreach($this->_db->fetchAll() AS $entry => $data) {

			$photo = new Model_Photo;

			foreach($data AS $key => $value) {
				$photo->$key = $value;
			}

			$photos[] = $photo;

		}

		return $photos;

	}


	public function fetchWhere($where, $op='AND') {

		foreach($this->_db->fetchWhere($where, $op) AS $entry => $data) {

			$photo = new Model_Photo;

			foreach($data AS $key => $value) {
				$photo->$key = $value;
			}

			$photos[] = $photo;

		}

		return $photos;

	}

	public function findByTag($tag) {

		foreach($this->_db->findByTag($tag) AS $entry => $data) {

			$photo = new Model_Photo;

			foreach($data AS $key => $value) {
				$photo->$key = $value;
			}
			$photos[] = $photo;

		}

		return $photos;

	}

	public function save(Model_Photo $model) {

		foreach($model->_data AS $key => $value) {
			$data[$key] = $value;
		}

		$booleans = array('exif', 'allow_comments', 'active', 'deleted', 'released');

		$data['tags'] = str_replace(array(', ', ' ,'), ',', $data['tags']);

		return $this->_db->setProperties($model->photo_id, $data);

	}

	public function delete($photo_id) {

		return $this->_db->deletePhoto($photo_id);

	}

}