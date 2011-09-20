<?php

class Controller_Photo extends Controller_Frontend {

	public function view($cleanTitle) {

		$photoMapper		= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO(Application_Registry::get('pdodb')));
		$photo				= $photoMapper->fetchWhere(array('clean_title'=>$cleanTitle));
		$photo				= $photo[0];

		$commentMapper		= new Model_Comment_Mapper(new Model_Comment_Gateway_PDO(Application_Registry::get('pdodb')));
		$comments			= $commentMapper->findByPhoto($photo->photo_id);

		if(Modules_Filesys::isFile(Application_Base::getProjectDir() . 'uploads/web/' . $photo->web_name)) {

			$image = new Modules_Image(Application_Base::getProjectDir() . 'uploads/web/' . $photo->web_name);

			$photo->width	= $image->getImageWidth();
			$photo->height	= $image->getImageHeight();

			$this->view->data['maxWidth'] = $photo->width;

			$subview = new Application_View_Theme();
			$subview->loadHTML('photo/view.html');
			$subview->data['photo'] = $photo;
			$subview->data['comments'] = $comments;

			$this->view->addSubview('main', $subview);

		}


	}

}