<?php

class Controller_Photo extends Controller_Frontend {

	public function view($cleanTitle) {

		$photoMapper		= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO(Application_Registry::get('pdodb')));
		$photo				= $photoMapper->fetchWhere(array('clean_title'=>$cleanTitle));
		$photo				= $photo[0];

		$commentController	= new Controller_Comment();
		$commentMapper		= new Model_Comment_Mapper(new Model_Comment_Gateway_PDO(Application_Registry::get('pdodb')));

		$subview = new Application_View_Theme();

		if($photo !== NULL && Modules_Filesys::isFile(Application_Base::getProjectDir() . 'uploads/web/' . $photo->web_name)) {

			$image = new Modules_Image(Application_Base::getProjectDir() . 'uploads/web/' . $photo->web_name);

			$photo->width	= $image->getImageWidth();
			$photo->height	= $image->getImageHeight();

			$this->view->data['maxWidth'] = $photo->width;

			$subview->loadHTML('photo/view.html');

			$subview->assign('commentform', $commentController->commentForm($photo->photo_id));

			$subview->data['photo'] = $photo;
			$subview->data['comments'] = $commentMapper->findByPhoto($photo->photo_id);

			$this->view->addSubview('main', $subview);

		} else {

			$subview->loadHTML('photo/view.notfound.error.html');
			$this->view->addSubview('main', $subview);

		}

	}

}