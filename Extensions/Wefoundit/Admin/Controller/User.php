<?php

class Extensions_Wefoundit_Admin_Controller_User {

	//public function __construct($observable) {
	public function __construct() {

		//$this->observable = $observable;

	}

	//public function addSuccess($config=array()) {
	public function addSuccess(Admin_Controller_User $userController) {

		$user = $userController->form->valueOf('data');

		$mail = new Modules_Mail_Mail();
		$mail->setRecipient($user['email'], $user['username']);
		$mail->setSubject(__('New account for Photoblog'));

		$mail->setMessage(__('A new account has been created. Username is: ' . $user['username']));
		$mail->send();

		

	}


}
