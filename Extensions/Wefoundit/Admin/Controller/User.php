<?php

class Extensions_Wefoundit_Admin_Controller_User {

	public function __construct() {}

	public function addSuccess(Admin_Controller_User $userController) {

		$user = $userController->form->valueOf('data');

		if(!empty($user)) {

			$sitename = Application_Settings::get('//general/site/sitename', 1);
			$signupInfo = Application_Settings::get('//system/email/signup', 1);

			$mail = new Modules_Mail_Mail();

			$mail->setRecipient($user['email'], $user['username']);
			$mail->setSubject(__('New account for photoblog "' . $sitename . '"'));
			$mail->setFrom($signupInfo['address'], str_replace('[[Sitename]]', $sitename, $signupInfo['name']));

			$mail->setMessage(
				sprintf(
					__(file_get_contents(dirname(__FILE__) . '/mail.txt') ), 
						$sitename, 
						$user['username'], 
						$user['password'], 
						Application_Base::getBaseURL() 
				) 
			);

			$mail->send();

		}

	}

}
