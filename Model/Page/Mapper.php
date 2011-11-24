<?php

class Model_Page_Mapper extends Model_Mapper_Abstract {

	protected $blacklist = array();

	public function find($page_id, Model_Page $model) {

		$data = $this->_db->getPageById($page_id);

		if(isset($data) && is_array($data)) {

			$model->page_id = $page_id;

			foreach($data AS $prop => $value) {
				$model->$prop = $value;
			}

		} else if($data == false) {

			return false;

		}

		return $model;

	}

	public function findByParentId($parent_page_id) {

		$pages = array();

		foreach($this->_db->getPagesByParentId($parent_page_id) AS $entry => $data) {

			$page = new Model_Page;

			if(isset($data) && is_array($data)) {

				foreach($data AS $prop => $value) {
					$page->$prop = $value;
				}

			} else if($data == false) {

				return false;

			}

			$pages[] = $page;

		}

		return $pages;

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

		unset($data['childcount']);

		return $this->_db->setProperties($model->page_id, $data);

	}

	public function delete($page_id) {

		return $this->_db->delete($page_id);

	}

}