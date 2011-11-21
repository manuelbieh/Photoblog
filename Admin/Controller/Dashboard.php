<?php

class Admin_Controller_Dashboard extends Controller_Frontend {

	public function __construct($app=NULL) {

		$app->extensions()->registerObservers($this);

		$this->app = $app;

		$this->view		= $this->app->objectManager->get('Application_View');
		$this->access	= $this->app->objectManager->get('Admin_Application_Access');

		if(!isset($_GET['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}


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

			$this->additionalContent = '';

			$this->app->extensions()->notify($this, 'beforeRenderOutput');

			$subview->data['photos'] = $photos;
			$subview->data['comments'] = $allComments;
			$subview->data['additionalContent'] = $this->additionalContent;

			if($this->access->check('Admin_Controller_System::update')) {

				$updateManager	= new Sys_Helper_Update($this->app);

				if($updateManager->checkForUpdates() === true) {
					$subview->data['info']['update'] = __('There are updates available on the update server. Do you wish to upgrade now?');
				}

			}

			$this->view->addSubview('main', $subview);

		} else {
			$this->app->go('Login');
		}

	}

}