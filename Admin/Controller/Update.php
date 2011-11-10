<?php

class Admin_Controller_Update extends Controller_Frontend {

	public $app;
	protected $observers = array();

	public function __construct($app=NULL) {

		$app->extensions()->registerObservers($this);

		$this->app 		= $app;

		$this->view		= $this->app->objectManager->get('Application_View');
		$this->access	= $this->app->objectManager->get('Admin_Application_Access');

		if(!isset($_POST['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}

	}

	public function backup() {

		$fr = new Modules_Filereader();
		print_r($fr->getFoldersRecursive($this->app->getCoreDir()));
		mkdir($this->app->getCoreDir() . '/Sys/backup/bak_' . date('Y-m-d_H:i:s'));
		#$backupFolders = 
		#foreach($

	}


}