<?php

class Controller_Photo extends Controller_Frontend {

	public function __construct($app) {

		$this->app = $app;

		$this->view = new Application_View_Theme();
		$this->view->loadHTML('index.html');

		$this->photoGateway		= new Model_Photo_Gateway_PDO($app->objectManager->get('Datastore'));
		$this->photoMapper		= new Model_Photo_Mapper($this->photoGateway);

		$this->userGateway		= new Model_User_Gateway_PDO($app->objectManager->get('Datastore'));
		$this->userMapper		= new Model_User_Mapper($this->userGateway);

		$this->commentGateway	= new Model_Comment_Gateway_PDO($app->objectManager->get('Datastore'));
		$this->commentMapper	= new Model_Comment_Mapper($this->commentGateway);

	}

	public function view($cleanTitle) {

		$photo				= $this->photoMapper->fetchWhere(array('clean_title'=>$cleanTitle));
		$photo				= $photo[0];

		$commentController	= new Controller_Comment($this->app);

		$subview = new Application_View_Theme();

		if($photo !== NULL && Modules_Filesys::isFile($this->app->getProjectDir() . 'uploads/web/' . $photo->web_name)) {

			$image = new Modules_Image($this->app->getProjectDir() . 'uploads/web/' . $photo->web_name);

			$photo->width	= $image->getImageWidth();
			$photo->height	= $image->getImageHeight();

			$photo->comments		= $this->commentMapper->findByPhoto($photo->photo_id);
			$photo->photographer	= $this->userMapper->find($photo->user_id, new Model_User);

			$this->view->data['maxWidth'] = $photo->width;

			$subview->loadHTML('photo/view.html');
			if((int) $photo->allow_comments === 1) {
				$subview->assign('commentform', $commentController->commentForm($photo->photo_id));
			} else {
				$subview->assign('commentform', __('Comments are closed.'));
			}
			$subview->data['photo'] = $photo;
			$this->view->addSubview('main', $subview);

		} else {

			$subview->loadHTML('photo/view.notfound.error.html');
			$this->view->addSubview('main', $subview);

		}

	}

}