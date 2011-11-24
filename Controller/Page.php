<?php

class Controller_Page extends Controller_Frontend {

	public function __construct($app) {

		$this->app = $app;

		$this->view = new Application_View_Theme();
		$this->view->loadHTML('index.html');

		$this->pageGateway		= new Model_Page_Gateway_PDO($app->objectManager->get('Datastore'));
		$this->pageMapper		= new Model_Page_Mapper($this->pageGateway);

	}

	public function view($cleanTitle) {

		$page				= $this->pageMapper->fetchWhere(array('clean_title'=>$cleanTitle));
		$page				= $page[0];

		$subview = new Application_View_Theme();

		if($page !== NULL) {

			$subview->loadHTML('page/view.html');

			$subview->data['page'] = $page;

			$this->view->addSubview('main', $subview);

		} else {

			$subview->loadHTML('page/view.notfound.error.html');
			$this->view->addSubview('main', $subview);

		}

	}

}