<?php

class Model_Page_Mapper extends Model_Mapper_Abstract {

	protected $blacklist = array();

	public function find($id, Model_Page $model) {

		$data = $this->_db->getPageById($id);

		if(isset($data) && is_array($data)) {

			$model->page_id = $id;

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
			$page = new Model_Page;
			#$photo->photo_id = $entry['photo_id'];
			foreach($data AS $key => $value) {
				$page->$key = $value;
			}
			$pages[] = $page;
		}

		return $pages;

	}


	public function save(Model_Page $model) {

		foreach($model->_data AS $key => $value) {
			$data[$key] = $value;
		}

		$this->_db->setProperties($model->page_id, $data);

	}

	public function delete($page_id) {

		return $this->_db->delete($page_id);

	}

}