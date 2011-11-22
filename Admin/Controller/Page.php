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

		$pageData = $this->getPageStructure();

		$subview	= $this->app->createView();
		$subview->loadHTML('templates/page/view.html');

		$subview->data['pages'] = $pageData['flatArray'];
		$subview->data['nested_pages'] = $pageData['parentChild'];

		$this->view->addSubview('main', $subview);

	}

	public function reorder() {

		$pageData = $this->getPageStructure();
		$factor = 1024;

		foreach($pageData['flatArray'] AS $num => $data) {
			$oldStructure[$data->page_id]['parent_page_id'] = $data->parent_page_id;
			$oldStructure[$data->page_id]['sort'] = $data->sort;
		}

		foreach($_POST['page'] AS $page_id => $parent_page_id) {

			$parent_page_id = (int) $parent_page_id;

			if(!isset($counter[$parent_page_id])) {
				$counter[$parent_page_id] = 0;
			} else {
				$counter[$parent_page_id] += 1;
			}


			if((int) $oldStructure[$page_id]['sort'] !== (int) $counter[$parent_page_id] * $factor) {
				$newStructure[$page_id]['sort'] = $counter[$parent_page_id] * $factor;
			}

			if((int) $oldStructure[$page_id]['parent_page_id'] !== (int) $parent_page_id) {
				$newStructure[$page_id]['parent_page_id'] = (int) $parent_page_id;
				$newStructure[$page_id]['sort'] = $counter[$parent_page_id] * $factor;
			}


		}


		if(is_array($newStructure)) {

			foreach($newStructure AS $page_id => $properties) {

				$page = new Model_Page();
				$page->page_id = $page_id;
				if($properties['parent_page_id']) {
					$page->parent_page_id = $properties['parent_page_id'];
				}
				$page->sort = $properties['sort'];
				$this->pageMapper->save($page);

			}

		}
		
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

	protected function getPageStructure() {

		$allPages	= $this->pageMapper->fetchAll();

		if(is_array($allPages)) {

			$pages = array();

			foreach($allPages AS $page => $data) {

				$data->cssclass = '';
				$data->cssclass .= $data->active == 1 ? ' active ':' inactive ';
				$data->cssclass .= $data->childcount > 0 ? ' haschildren ':'';
				$pages[$data->parent_page_id][$data->page_id] = $data;

			}

		}

		$ret = array();
		$ret['flatArray'] = $allPages;
		$ret['parentChild'] = $pages;

		return $ret;

	}

}