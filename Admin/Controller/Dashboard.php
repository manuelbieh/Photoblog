<?php

class Admin_Controller_Dashboard {

	public function __construct() {

		$this->view = new Application_View();

		if(!isset($_POST['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

		$navi = new Application_View();

		$navi->loadHTML("templates/main/navi.html");
		$this->view->addSubview('navi', $navi);

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			Application_Base::go('Login');
			exit;
		}

	}

	public function __destruct() {
		$this->view->render(true);
	}

	public function index() {

		if(Modules_Session::getInstance()->getVar('userdata')->user_id) {
			$subview = new Application_View();
			$subview->loadHTML('templates/dashboard/index.html');

			$photoMapper	= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO(Application_Registry::get('pdodb')));
			$allPhotos		= $photoMapper->fetchAll();

			foreach($allPhotos AS $photoObject) {
				$photos[$photoObject->photo_id] = $photoObject;
			}

			$CommentMapper	= new Model_Comment_Mapper(new Model_Comment_Gateway_PDO(Application_Registry::get('pdodb')));
			$allComments	= $CommentMapper->fetchAll();

			$subview->data['photos'] = $photos;
			$subview->data['comments'] = $allComments;
			$this->view->addSubview('main', $subview);
			//echo 'Hallo Admin';
			//Application_Base::go('Photo/add');
		} else {
			Application_Base::go('Login');
		}

	}

}