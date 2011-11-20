<?php

class Admin_Controller_Login extends Controller_Frontend {

	public function __construct($app) {

		$app->extensions()->registerObservers($this);

		$this->app = $app;

		$this->view			= $this->app->objectManager->get('Application_View');
		$this->userGateway	= new Model_User_Gateway_PDO($app->objectManager->get('Datastore'));
		$this->userMapper	= new Model_User_Mapper($this->userGateway);
		$this->login		= new Modules_Login($this->userGateway);
		$this->enc			= new Modules_Encryption_Md5();

		$this->app->extensions()->notify($this, 'configEnd');

		$this->view->loadHTML('templates/index.html');

		$this->app->extensions()->notify($this, 'templateLoaded');

		if(isset($_GET['logout']) || $_POST['logout']) {
			$this->login->logout();
		}

	}


	public function index() {

		$this->view->replace('headline', __('Login'));
		$form = new Modules_Form('templates/login/login.form.html');

		$options['enableCookie'] = $form->valueOf('data[enableCookie]') == 1 ? true : false;

		$password = $this->enc->encryptWithSalt($form->valueOf('data[password]'), __SALT__);
		$this->login->loginUser($form->valueOf('data[username]'), $password, $options);

		if($this->login->isLoggedIn()) {

			$this->app->go('Dashboard');

		} else {

			if($form->isSent() && !$this->login->isLoggedIn()) {
				$val = new Modules_Validation_Basechecks();
				$form->addValidation($val);
				$form->addError(__('Login was not correct.'));
			}
			$this->view->addSubview('main', $form);

		}

	}


	public function forgot() {

		$form = new Modules_Form('templates/login/forgot.form.html');
		$form->setValidation(new Modules_Validation_Basechecks);

		if($form->isSent()) {
			if($form->valueOf('data[email]') == '' && $form->valueOf('data[username]') == '') {
				$form->addError(__('You must specify at least a username or your email.'));
			}
		}

		if($form->isSent(true)) {

			if($form->valueOf('data[email]')) {
				$fields['email'] = $form->valueOf('data[email]');
			}
			if($form->valueOf('data[username]')) {
				$fields['username'] = $form->valueOf('data[username]');
			}
			$fields['active'] = 1;

			$user = $this->userMapper->findByFields($fields);

			if(count($user) === 1) {

				$user = $user[0];

				$hash = md5($user->username . mt_rand(0, 9999999) . microtime(true) . $_SERVER['HTTP_USER_AGENT']);

				$user->passconf = $hash;
				$this->userMapper->save($user);

				$mailtpl = $this->app->createView();
				$mailtpl->loadHTML('templates/login/forgot.mail.txt');
				$mailtpl->assign('username', $user->username);
				$mailtpl->assign('resetlink', $this->app->getBaseURL() . 'Login/reset/' . $user->user_id . '/' . $hash);

				$mail = new Modules_Mail_Mail();
				$from = Application_Settings::get("//system/email/signup");
				$mail->setSubject('You requested to reset your password');
				$mail->setFrom($from['address'], $from['name']);
				$mail->setMessage($mailtpl->render());
				$mail->setRecipient($user->email);
				$mail->send();

				$subview = $this->app->createView();
				$subview->loadHTML('templates/login/forgot.success.html');
				$this->view->addSubview('main', $subview);
			
			} else {

				$form->addError(__('Username or email could not be found.'));
				$this->view->addSubview('main', $form);

			}

		} else {

			$this->view->addSubview('main', $form);

		}

	}

	public function reset($user_id=NULL, $hash=NULL) {

		$fields['user_id'] = (int) $user_id;
		$fields['passconf'] = $hash;
		$fields['active'] = 1;

		$user = $this->userMapper->findByFields($fields);

		if(count($user) === 1) {

			$form = new Modules_Form('templates/login/reset.form.html');
			$form->assign('hash', $hash);

			$v = new Modules_JSONValidation();
			if($form->isSent()) {
				$v->setConfigByJSONFile('templates/user/edit.password.json');
			}
			$form->setValidation($v);

			if($form->isSent(true)) {

				$user = $user[0];
				$user->password = $this->enc->encryptWithSalt($form->valueOf('data[password]'), __SALT__);
				$user->passconf = '';
				$this->userMapper->save($user);

				$subview = $this->app->createView();
				$subview->loadHTML('templates/login/reset.success.html');
				$this->view->addSubview('main', $subview);

			} else {

				$this->view->addSubview('main', $form);

			}

		} else {

			$subview = $this->app->createView();
			$subview->loadHTML('templates/login/reset.error.notfound.html');
			$this->view->addSubview('main', $subview);

		}

	}

}