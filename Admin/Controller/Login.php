<?php

class Admin_Controller_Login {

	public function __construct($app=NULL) {

		$this->app = $app;

		$this->view		= new Application_View();
		$this->login	= new Modules_Login( new Model_User_Gateway_PDO( Application_Registry::get('pdodb') ) );

		$this->view->loadHTML('templates/index.html');

		if(isset($_GET['logout'])) {

			$this->login->logout();

		}


	}


	public function index() {

		$this->view->replace('headline', 'Login');
		$form = new Modules_Form('templates/login/login.form.html');
		$this->login = new Modules_Login( new Model_User_Gateway_PDO( Application_Registry::get('pdodb') ) );

		$options['enableCookie'] = $form->valueOf('data[enableCookie]') == 1 ? true : false;

		$this->login->loginUser($form->valueOf('data[username]'), $form->valueOf('data[password]'), $options);

		if($this->login->isLoggedIn()) {

			Application_Base::go('Dashboard');
			// $subview = new Application_View();
			// $subview->loadHTML('templates/login/login.loggedin.html');
			// $this->view->addSubview('main', $subview);
			#$this->view->assign('main', $subview->render());

		} else {

			if($form->isSent() && !$this->login->isLoggedIn()) {
				$val = new Modules_Validation_Basechecks();
				$form->addValidation($val);
				$form->addError(__('Login was not correct.'));
			}
			$this->view->addSubview('main', $form);
			#$this->view->replace('main', $form->render());

		}

		#$user = new Model_User_Database(Application_Registry::get('db'));
		#$test = $user->getUserDataById(1);
		//var_dump($test);
		#echo '<hr />';
		#$userprop = $user->getProperty(array('firstname', 'lastname'), 1);
		#var_dump($userprop);
		#$this->view->render(true);
		#$user = Modules_Session::getInstance()->getVar('userdata');
		#var_dump($user);
	#	$user = new Model_User_Gateway_PDO( Application_Registry::get('pdodb') );
	#	$create = $user->createUser(array('firstname'=>'Franzl', 'username'=>'Hansfranz', 'email'=>'Foo@bar.de'));
	#	var_dump($create);


	}


	public function forgot() {

		$this->view->replace('headline', 'Passwort vergessen');
		$form = new Modules_Form('templates/login/forgot.form.html');
		$form->setValidation(new Modules_Validation_Basechecks);

		if($form->isSent()) {
			$request = new Modules_Login( new Model_User_Gateway_PDO( Application_Registry::get('pdodb') ) );
			if($request->requestPassword($form->valueOf('data')) !== false) {

				$subview = new Application_View();
				$subview->loadHTML('templates/login/forgot.success.html');
				$this->view->addSubview('main', $subview);

			} else {

				$form->addError('Benutzername und/oder E-Mail konnten nicht gefunden werden.');
				$this->view->addSubview('main', $form);

			}
		} else {
			$this->view->addSubview('main', $form);
		}

	#	$this->view->render(true);

	}


	public function reset($user=NULL, $hash=NULL) {

		$this->view->replace('headline', 'Neues Passwort wählen');
		$form = new Modules_Form('templates/login/reset.form.html');
		$form->assign('hash', $hash);
		$form->setValidation(new Modules_Validation_Basechecks);

		if($form->isSent()) {

			if($this->login->resetPassword($form->valueOf('hash'), $form) != true || $form->valueOf('hash') == NULL) {
				$form->addError('Benutzer nicht gefunden. Der Link ist möglicherweise bereits veraltet.');
			}

			if($form->valueOf('data[password]') != $form->valueOf('confirm')) {
				$form->addError('Passwort und Bestätigung stimmen nicht überein.');
			}

			if(strlen($form->valueOf('data[password]')) < 6) {
				$form->addError('Das Passwort muss mindestens 6 Zeichen haben.');
			}

		}

		if(!$form->isSent(true)) {

			$this->view->addSubview('main', $form);

		} else {

			$user = new Model_User_Gateway_PDO( Application_Registry::get('pdodb') );
			$user_id = $user->getUsersByMultipleFields(array('username'=>$user, 'passconf'=>$hash));

			if(count($user_id) == 1) {

				$user->setProperties($user_id[0], array('password'=>$form->valueOf('data[password]'), 'passconf'=>''));
				$subview = new Application_View();
				$subview->loadHTML('templates/login/reset.success.html');
				$this->view->addSubview('main', $subview);

			} else {

				$subview = new Application_View();
				$subview->loadHTML('templates/login/reset.error.html');
				$this->view->addSubview('main', $subview);

			}

		}

	#	$this->view->render(true);

	}


	public function __destruct() {

		$this->subview = new Application_View();
		$this->subview->loadHTML('templates/main/navi.html')->render();
		$this->view->addSubview('navi', $this->subview);

		$this->view->render(true);

	}

}