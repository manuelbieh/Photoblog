<?php

class Extensions_Manuel_Admin_Controller_User extends Application_Extension {

	protected $configFile = "SystemTweaks.xml";

	public function addSuccess(Admin_Controller_User $userController) {

		$user = $userController->form->valueOf('data');

		if(!empty($user) && $this->get("//settings/adminUser/sendAddNotification", $this->configFile) == 1) {

			$sitename	= Application_Settings::get('//general/site/sitename', 1);
			$signupInfo	= Application_Settings::get('//system/email/signup', 1);

			$mail = new Modules_Mail_Mail();

			$mail->setRecipient($user['email'], $user['username']);
			$mail->setSubject(sprintf(__('New account for photoblog "%s"'), $sitename));
			$mail->setFrom($signupInfo['address'], str_replace('[[Sitename]]', $sitename, $signupInfo['name']));

			$mail->setMessage(
				sprintf(
					__(file_get_contents(dirname(__FILE__) . '/mail.txt') ), 
						$sitename, 
						$user['username'], 
						$user['password'], 
						$this->app->getBaseURL() 
				) 
			);

			$mail->send();

		}

	}

}
