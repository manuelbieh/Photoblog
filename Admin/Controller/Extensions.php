<?php

class Admin_Controller_Extensions extends Controller_Frontend {

	public function __construct() {

		#$this->userDB	= new Model_Extension_Gateway_PDO(Application_Registry::get('pdodb'));
		$this->view		= new Application_View();

		$this->view->loadHTML('templates/index.html');

		$navi = new Application_View();

		$navi->loadHTML("templates/main/navi.html");
		$this->view->addSubview('navi', $navi);

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			header("Location: " . Application_Base::getBaseURL() . 'Login');
			exit;
		}

	}

	public function view() {
		$foo = Application_Base::buildExtensionIndex();
		var_dump($foo);
		//$this->view->addSubview('main', new Application_View_String('foo'));
		
	}

	public function activate($pluginkey) {
	
	}

	public function deactivate($pluginkey) {
	
	}


}