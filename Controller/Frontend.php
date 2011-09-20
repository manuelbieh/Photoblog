<?php

class Controller_Frontend {

	public function __construct() {
		$this->view = new Application_View_Theme();
		$this->view->loadHTML('index.html');
	#	$this->view = new Application_View();
	#	$this->view->loadHTML('templates/ajax.html');
	}

	public function __destruct() {
		$this->view->render(true);
	}


}