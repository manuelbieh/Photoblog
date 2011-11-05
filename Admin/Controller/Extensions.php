<?php

class Admin_Controller_Extensions extends Controller_Frontend {

	public function __construct($app=NULL) {

		$this->app = $app;
		#$this->userDB	= new Model_Extension_Gateway_PDO(Application_Registry::get('pdodb'));
		$this->view		= $this->app->objectManager->get('Application_View');

		$this->view->loadHTML('templates/index.html');

		$navi = $this->app->createView();
		$navi->loadHTML("templates/main/navi.html");

		$this->view->addSubview('navi', $navi);

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}

	}

	public function view() {
		$foo = Application_Extensions::buildIndex();
		var_dump($foo);
		//$this->view->addSubview('main', new Application_View_String('foo'));
		
	}

	public function activate($pluginkey) {
	
	}

	public function deactivate($pluginkey) {
	
	}


}