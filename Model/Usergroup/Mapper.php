<?php

class Model_Usergroup_Mapper extends Model_Mapper_Abstract {

	protected $blacklist = array();

	public function find($id, Model_Usergroup $model) {

		$model->user_id = $id;

		$data = $this->_db->getUsergroupDataById($id);
		foreach($data AS $prop => $value) {
			$model->$prop = $value;
		}

		return $model;

	}


	public function fetchAll() {

		foreach($this->_db->fetchAll() AS $entry => $data) {
			$users[] = $this->find($data['user_id'], new Model_Usergroup);
		}

		return $users;

	}


	public function save(Model_Usergroup $model) {

		$data = array(
			'title'=>$model->title,
		);

		if($model->active !== NULL) {
			$data['active'] = $model->active;
		}

		if((int) $model->usergroup_id === 0) {

			$model->usergroup_id = $this->_db->createUsergroup($model, $data);

			if($model->usergroup_id != false) {
				return true;
			} else {
				return false;
			}

		} else {
			$this->_db->setProperties($model->usergroup_id, $data);
		}

	}

}