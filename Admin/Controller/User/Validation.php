<?php


class Admin_Controller_User_Validation extends Modules_Validation_Abstract {

	public $object = __CLASS__;
	public $errors = array();

	public function __construct() {

		$this->db = new Model_User_Gateway_PDO(Application_Registry::get('pdodb'));

	}

	public function checkUsername($username) {

		$user = $this->db->getUserDataByField('username', $username);

		if($user !== false) {
			$this->addError(__('Username already exists.'));
			return false;
		}

	}

	public function checkEmail($email) {

		$user = $this->db->getUserDataByField('email', $email);

		if($user !== false) {
			$this->addError(__('A User with this email is already registered.'));
			return false;
		}

	}




}