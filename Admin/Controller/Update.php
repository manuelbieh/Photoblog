<?php

class Admin_Controller_Update extends Controller_Frontend {

	public $app;
	protected $observers = array();

	public function __construct($app) {

		require_once($app->getCoreDir() . 'Sys/libs/pclzip/pclzip.lib.php');

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

	public function index() {
		
	}

	protected function backup() {

		$core		= $this->app->getCoreDir();
		$version	= $this->app->getVersion();

		$filename = 'backup_'.date('Ymd-His').'.zip';
		$fullname = $core . '/Sys/backup/' . $filename;

		$archive = new PclZip($fullname);

		if($archive->create($core, PCLZIP_OPT_REMOVE_PATH, $core) == 0) {

			return false;

		} else {

			$blacklist = array('uploads/', 'uploads', 'Sys/backup/');
			$archive->delete(PCLZIP_OPT_BY_NAME, $blacklist);
			$archive->delete(PCLZIP_OPT_BY_EREG, '(\.svn)');

			return true;

		}

	}

	public function update() {

		$updateManager = new Sys_Helper_Update($this->app);
		if($updateManager->checkForUpdates() === -1) {
			$this->view->addSubview('main', new Application_View_String('New version is available'));
		}

	}

}