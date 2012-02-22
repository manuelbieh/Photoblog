<?php

class Admin_Controller_Content extends Controller_Frontend {

	protected $app;
	protected $access;
	protected $view;

	public function __construct($app) {

		$app->extensions()->registerObservers($this);

		$this->app = $app;

		$this->view		= $this->app->objectManager->get('Application_View');
		$this->access	= $this->app->objectManager->get('Admin_Application_Access');

		if(!$this->app->isAjaxRequest()) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}

	}

	public function index($filename) {

		$subview = $this->app->createView();

		$filename	= str_replace(array("../", "./", "..", "\0", "\x", "?"), '', $filename);

		if(strlen($filename)-5 == strripos($filename, '.html')) {
			$filename = substr($filename, 0, strripos($filename, '.html'));
		}

		$fullpath	= 'templates/content/' . $filename . '.html';

		if(Modules_Filesys::isFile($this->app->getPath($fullpath))) {
			$subview->loadHTML($fullpath);
			$this->view->addSubview('main', $subview);
		} else {
			$this->view->addSubview('main', $this->app->errors()->error404());
		}

	}


}