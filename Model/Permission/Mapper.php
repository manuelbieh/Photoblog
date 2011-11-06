<?php

class Model_Permission_Mapper extends Model_Mapper_Abstract {

	protected $blacklist = array();

	public function find($permission_id, Model_Permission $model) {

		$data = $this->_db->getPermissionDataById($permission_id);
		if(is_array($data)) {
			foreach($data AS $prop => $value) {
				$model->$prop = $value;
			}
		}

		return $model;

	}

	public function fetchAll() {

		foreach($this->_db->fetchAll() AS $entry => $data) {
			$permissions[] = $this->find($data['permission_id'], new Model_Permission);
		}

		return $permissions;

	}

	public function findPermissionId($class, $method, $param='') {

		return $this->_db->getPermissionId($class, $method, $param);

	}

	public function findPermissionsByLink($link='') {

		return $this->_db->getPermissionsByLink($link);

	}

	public function findPermissionsByUserId($user_id) {

		return $this->_db->getPermissionsByUserId($user_id);

	}

	public function findUsersByPermissionId($permission_id) {

		return $this->_db->getUsersByPermissionId($permission_id);

	}

	public function save(Model_Permission $model) {

		$data = array(
			'class'=>$model->{'class'},
			'method'=>$model->method,
			'param'=>$model->param,
			'title'=>$model->title
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