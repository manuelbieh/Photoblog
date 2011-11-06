<?php

class Model_User_Mapper extends Model_Mapper_Abstract {

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
		$user_id = $this->_db->getUserIdByField($fieldname, $value);
		return $this->find($user_id, new Model_User);
	}

	public function findByFields($fieldArray) {

		$res = $this->_db->getUsersByMultipleFields($fieldArray);

		$users = array();
		if(is_array($res)) {
			foreach($res AS $user_id) {
				$users[] = $this->find($user_id, new Model_User);
			}
		}
		return $users;

	}

	public function findByLogin($username, $password) {

		$user = $this->_db->getUserDataByLogin($username, $password);
		if($user != false) {
			return $this->find($user['user_id'], new Model_User);
		}
		return false;

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

	public function delete($user_id) {

		return $this->_db->delete($user_id);

	}

	/*
	 * $additional = temporarily allow to set some more properties
	 */
	public function save(Model_User $model, $additional=array()) {

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
			'avatar'=>$model->avatar,
			'gender'=>$model->gender,
			'last_login'=>$model->last_login,
		);

		if(is_array($additional)) {
			foreach($additional AS $prop) {
				$this->data[$prop] = $model->$prop;
			}
		}

		if($model->password !== NULL) {
			$this->data['password'] = $model->password;
		}

		if($model->passconf !== NULL) {
			$this->data['passconf'] = $model->passconf;
		}

		if($model->loginhash !== NULL) {
			$this->data['loginhash'] = $model->loginhash;
		}

		if($model->active !== NULL) {
			$this->data['active'] = $model->active;
		}

		$app->extensions()->notify($this, 'builtDataArray');

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

}