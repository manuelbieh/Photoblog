<?php

class Admin_Controller_Dashboard extends Controller_Frontend {

	public function __construct($app=NULL) {

		$app->extensions()->registerObservers($this);

		$this->app = $app;

		$this->view		= $this->app->objectManager->get('Application_View');
		$this->access	= $this->app->objectManager->get('Admin_Application_Access');

		if(!isset($_POST['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

		$navi = $this->app->createView();
		$navi->loadHTML("templates/main/navi.html");

		$this->view->addSubview('navi', $navi);

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}

	}

	public function index() {

		if(Modules_Session::getInstance()->getVar('userdata')->user_id) {

			$subview = $this->app->createView();
			$subview->loadHTML('templates/dashboard/index.html');

			$photoMapper	= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO($this->app->objectManager->get('Datastore')));
			$allPhotos		= $photoMapper->fetchAll();

			if(is_array($allPhotos)) {
				foreach($allPhotos AS $photoObject) {
					$photos[$photoObject->photo_id] = $photoObject;
				}
			}

			$CommentMapper	= new Model_Comment_Mapper(new Model_Comment_Gateway_PDO($this->app->objectManager->get('Datastore')));
			$allComments	= $CommentMapper->fetchAll();

			$subview->data['photos'] = $photos;
			$subview->data['comments'] = $allComments;
			$this->view->addSubview('main', $subview);

		} else {
			$this->app->go('Login');
		}

	}

}