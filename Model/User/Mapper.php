<?php

class Model_User_Mapper extends Model_Mapper_Abstract implements Application_Observable {

	protected $blacklist = array('user_id', 'passconf', 'loginhash', 'active', 'date_signup', 'last_login', 'loggedin');
	public $data;
	public $observers = array();

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
/*
		foreach($model->getKeys() AS $prop => $value) {
			$data[$prop] = $value;
		}
*/
		$app = new Application_Base();
		$app->extensions()->registerObservers($this);

		$this->data = array(
			'username'=>$model->username,
			'email'=>$model->email,
			'firstname'=>$model->firstname,
			'lastname'=>$model->lastname,
			'birthname'=>$model->birthname,
			'salutation'=>$model->salutation,
			'middlename'=>$model->middlename,
			'passconf'=>$model->passconf,
			'gender'=>$model->gender,
		);

		if($model->password !== NULL) {
			$this->data['password'] = $model->password;
		}

		if($model->active !== NULL) {
			$this->data['active'] = $model->active;
		}

		$this->notify('builtDataArray');

		if((int) $model->user_id === 0) {

			$model->date_signup = date('Y-m-d H:i:s');
			$model->user_id = $this->_db->createUser($model, $this->data);

			if($model->user_id != false) {
				return $model->user_id;
			} else {
				return false;
			}

		} else {
			$this->_db->setProperties($model->user_id, $this->data);
		}

	}

	public function addObserver($observer) {

		array_push($this->observers, $observer);

	}

	public function notify($state, $additionalParams=NULL) {

		foreach((array) $this->observers AS $obs) {

			if(method_exists($obs, $state)) {

				$obs->$state(&$this, $additionalParams);

			}

		}

	}

}