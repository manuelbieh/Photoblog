<?php

class Admin_Controller_Page {

	public function __construct($app) {

		$this->app = $app;

		$this->view		= $this->app->objectManager->get('Application_View');

		if(!isset($_POST['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}

	}

	public function __destruct() {
		$this->view->render(true);
	}

	public function view() {

		$pageMapper		= new Model_Page_Mapper(new Model_Page_Gateway_PDO(Application_Registry::get('pdodb')));
		$allPages		= $pageMapper->fetchAll();

		$subview = $this->app->createView();
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