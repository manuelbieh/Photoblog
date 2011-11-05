<?php

class Model_Extension_Mapper extends Model_Mapper_Abstract {

	protected $blacklist = array();

	public function find($extension_key, Model_Extension $model) {

		$data = $this->_db->getExtension($extension_key);

		if(isset($data) && is_array($data)) {

			$model->extension_key = $extension_key;

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
			$extension = new Model_Extension;
			foreach($data AS $key => $value) {
				$extension->$key = $value;
			}
			$extensions[] = $extension;
		}

		return $extensions;

	}


	public function save(Model_Extension $model) {

		foreach($model->_data AS $key => $value) {
			$data[$key] = $value;
		}

		return $this->_db->setProperties($model->extension_key, $data);

	}

	public function delete($extension_key) {

		return $this->_db->delete($extension_key);

	}

}