<?php

class Admin_Controller_Page extends Controller_Frontend {

	public function __construct($app) {

		$this->app = $app;

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}

		$app->extensions()->registerObservers($this);

		$this->view				= $this->app->objectManager->get('Application_View');

		$this->pageGateway		= new Model_Page_Gateway_PDO($app->objectManager->get('Datastore'));
		$this->pageMapper		= new Model_Page_Mapper($this->pageGateway);

		if(!isset($_GET['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

	}

	public function view() {

		$allPages	= $this->pageMapper->fetchAll();

		$subview	= $this->app->createView();
		$subview->loadHTML('templates/page/view.html');

		$subview->data['pages'] = $allPages;

		foreach($allPages AS $page => $data) {

			$data->cssclass = '';
			$data->cssclass .= $data->active == 1 ? ' active ':' inactive ';
			$data->cssclass .= $data->childcount > 0 ? ' haschildren ':'';
			$pages[$data->parent_page_id][$data->page_id] = $data;

		}

		$subview->data['nested_pages'] = $pages;

		$this->view->addSubview('main', $subview);

	}

	public function create($parent_page_id=0) {
		
	}

	public function edit($type, $id) {
		
	}

	public function delete($type, $id) {
		
	}

	public function hide($type, $id) {
		
	}

	public function sort($type, $id, $order) {
		
	}

}