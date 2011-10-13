<?php

class Model_Permission_Mapper extends Model_Mapper_Abstract {

	protected $blacklist = array();

	public function find($permission_id, Model_Permission $model) {

		$data = $this->_db->getPermissionDataById($id);
		foreach($data AS $prop => $value) {
			$model->$prop = $value;
		}

		return $model;

	}

	public function findPermissionId($class, $method, $param='') {

		return $this->_db->getPermissionId($class, $method, $param);

	}

	public function findUsersByPermissionId($permission_id) {

		return $this->_db->getUsersByPermissionId($permission_id);

	}

	public function fetchAll() {

		foreach($this->_db->fetchAll() AS $entry => $data) {
			$users[] = $this->find($data['permission_id'], new Model_Permission);
		}

		return $users;

	}

	public function save(Model_User $model) {

		$data = array(
			'class'=>$model->{'class'},
			'method'=>$model->method,
			'param'=>$model->param
		);


		if((int) $model->permission_id === 0) {

			$model->permission_id = $this->_db->createPermission($model, $data);

			if($model->permission_id != false) {
				return true;
			} else {
				return false;
			}

		} else {
			$this->_db->setProperties($model->permission_id, $data);
		}

	}

}