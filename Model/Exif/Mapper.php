<?php

class Model_Exif_Mapper extends Model_Mapper_Abstract {

	protected $blacklist = array();

	public function find($id, Model_Exif $model) {

		$data = $this->_db->getExifDataByPhotoId($id);

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
			$photo = new Model_Exif;
			#$photo->photo_id = $entry['photo_id'];
			foreach($data AS $key => $value) {
				$photo->$key = $value;
			}
			$photos[] = $photo;
		}

		return $photos;

	}


	public function save(Model_Exif $model) {

		foreach($model->_data AS $key => $value) {
			$data[$key] = $value;
		}

		return $this->_db->setProperties($model->photo_id, $data);

	}

	public function delete($photo_id) {

		return $this->_db->delete($photo_id);

	}

}