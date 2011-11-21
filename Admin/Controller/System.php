<?php

class Admin_Controller_System extends Controller_Frontend {

	public $app;
	protected $observers = array();

	public function __construct($app) {

		require_once($app->getCoreDir() . 'Sys/libs/pclzip/pclzip.lib.php');

		$app->extensions()->registerObservers($this);

		$this->app 		= $app;

		$this->view		= $this->app->objectManager->get('Application_View');
		$this->access	= $this->app->objectManager->get('Admin_Application_Access');

		if(!isset($_GET['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}

	}

	public function index($action=NULL) {

		if($this->access->check(__METHOD__)) {

			$updateManager	= new Sys_Helper_Update($this->app);
			$subview		= $this->app->createView();

			if($updateManager->checkForUpdates() === true) {
				$subview->data['update'] = true;
			}

			$subview->loadHTML('templates/system/index.html');

			$this->view->addSubview('main', $subview);

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error403());
		}

	}

	public function test() {
		$updateManager	= new Sys_Helper_Update($this->app);	
		$updateManager->test();
	}

	public function update() {

		if($this->access->check(__METHOD__)) {

			$updateManager	= new Sys_Helper_Update($this->app);
			$subview		= $this->app->createView();
			$update			= $updateManager->update();

			$updateManager->backup();

			if(isset($update['error'])) {

				$subview->data['error'] = $update['error'];
				$subview->loadHTML('templates/system/update.error.html');

			} else {

				$subview->data['info'] = $update['info'];
				$subview->data['warning'] = $update['warning'];
				$subview->loadHTML('templates/system/update.success.html');

			}

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error403());
		}

		$this->view->addSubview('main', $subview);

	}

	public function backup() {

		if($this->access->check(__METHOD__)) {

			$updateManager	= new Sys_Helper_Update($this->app);
			$subview = $this->app->createView();

			if($updateManager->backup() == true) {

				$subview->loadHTML('templates/system/backup.success.html');

			} else {

				$subview->loadHTML('templates/system/backup.error.html');

			}

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error403());
		}

		$this->view->addSubview('main', $subview);

	}

}