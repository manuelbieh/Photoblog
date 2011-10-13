<?php

class Model_User_Mapper extends Model_Mapper_Abstract {

	protected $blacklist = array('user_id', 'passconf', 'loginhash', 'active', 'date_signup', 'last_login', 'loggedin');

	public function __construct($db) {
		parent::__construct($db);
		$this->enc = new Modules_Encryption_Md5();
	}

	public function find($id, Model_User $model) {

		$model->user_id = $id;

		$data = $this->_db->getUserDataById($id);
		if(is_array($data)) {
			foreach($data AS $prop => $value) {
				$model->$prop = $value;
			}
		} else {
			$model = false;
		}

		return $model;

	}

	public function findByField($fieldname, $value) {
		return $this->find($this->_db->getUserDataByField($fieldname, $value), new Model_User);
	}

	public function fetchAll() {

		foreach($this->_db->fetchAll() AS $entry => $data) {
			$users[] = $this->find($data['user_id'], new Model_User);
		}

		return $users;

	}

	public function savePermissions($user_id, $permissionsArray) {

		return $this->_db->setPermissions($user_id, $permissionsArray);

	}

	public function save(Model_User $model) {

		$data = array(
			'username'=>$model->username,
			'email'=>$model->email,
			'firstname'=>$model->firstname,
			'lastname'=>$model->lastname,
			'middlename'=>$model->middlename,
			'passconf'=>$model->passconf,
		);

		if($model->password !== NULL) {
			$data['password'] = $this->enc->encryptWithSalt($model->password, __SALT__);
		}

		if($model->active !== NULL) {
			$data['active'] = $model->active;
		}

		if((int) $model->user_id === 0) {

			$model->user_id = $this->_db->createUser($model, $data);

			if($model->user_id != false) {
				return $model->user_id;
			} else {
				return false;
			}

		} else {
			$this->_db->setProperties($model->user_id, $data);
		}

	}

}