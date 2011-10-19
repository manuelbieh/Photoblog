<?php

class Controller_Index extends Controller_Frontend {

	public function __construct($app=NULL) {

		$this->app = $app;

		$this->view = new Application_View_Theme();
		$this->view->loadHTML('index.html');

		$this->photoGateway	= new Model_Photo_Gateway_PDO($app->objectManager->get('Datastore'));
		$this->photoMapper	= new Model_Photo_Mapper($this->photoGateway);

		$this->userGateway	= new Model_User_Gateway_PDO($app->objectManager->get('Datastore'));
		$this->userMapper	= new Model_User_Mapper($this->userGateway);

	}

	public function index($command=NULL, $param1=NULL) {

	//	$photoMapper		= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO(Application_Registry::get('pdodb')));
		$allPhotos			= $this->photoMapper->fetchAll();

		$photosPerPage		= (int) Application_Settings::get('//theme/photosPerPage');
		$photosPerPage		= $photosPerPage < 1 ? 1 : $photosPerPage;
		$totalPhotos		= count($allPhotos);

		$allPhotosReverse	= is_array($allPhotos) ? array_reverse($allPhotos) : array();
		$page				= (int) $param1 < 0 ? 0 : (int) $param1;
		$offset				= $page * $photosPerPage;

		$subview = new Application_View_Theme();
		$subview->loadHTML('index/index.html');

		for($i = $offset; $i < $offset + $photosPerPage; $i++) {

			$currentPhoto = $allPhotosReverse[$i];

			if(Modules_Filesys::isFile($this->app->getProjectDir() . 'uploads/web/' . $currentPhoto->web_name)) {

				$image = new Modules_Image($this->app->getProjectDir() . 'uploads/web/' . $currentPhoto->web_name);

				$currentPhoto->width = $image->getImageWidth();
				$currentPhoto->height = $image->getImageHeight();
				$currentPhoto->photographer = $this->userMapper->find($currentPhoto->user_id, new Model_User);

				$maxWidth	= $maxWidth == NULL || $currentPhoto->width > $maxWidth ? $currentPhoto->width : $maxWidth;
				$maxHeight	= $maxHeight == NULL || $currentPhoto->height > $maxHeight ? $currentPhoto->height : $maxHeight;

				$subview->data['photos'][] = $currentPhoto;


			}

		}

		if((int) $totalPhotos > 0) {

			$this->view->data['maxWidth'] = $maxWidth;
			$this->view->data['maxHeight'] = $maxHeight;

			if( (($page+1) * $photosPerPage) < $totalPhotos) {
				$subview->data['prevLink'] = $this->app->getBaseURL() . 'page/' . ((int) $page+1);
			} else {
				$subview->data['prevLink'] = '';
			}

			if(!($page < 1)) {
				$subview->data['nextLink'] = $this->app->getBaseURL() . 'page/' . ((int) $page-1);
			} else {
				$subview->data['nextLink'] = '';
			}

			$pagina = new Modules_Pagination;
			$pagina->setUsePages(true)
					->setAtLast(3)
					->setAtLeast(3)
					->setLink($this->app->getBaseURL() . "page/")
					->setItemsPerPage($photosPerPage)
					->setItemsTotal($totalPhotos)
					->currentPageNum($page);
			$subview->data['pagination'] = $pagina->render();

		}

		$this->view->addSubview('main', $subview);

	}


}