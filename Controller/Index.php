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

	public function index($link='page', $param1=NULL) {

		$allPhotos			= $this->photoMapper->fetchAll();
		$this->showPhotos($allPhotos, $link, $param1);

	}


	public function tag($link='tag', $tag, $param1=NULL) {

		$allPhotos = $this->photoMapper->findByTag($tag);
		$this->showPhotos($allPhotos, $link . '/' . $tag, $param1, array('photosPerPage'=>5));

	}


	private function showPhotos($allPhotos, $link=NULL, $param1=NULL, $options=array()) {

		$photosPerPage		= (int) Application_Settings::get('//theme//photosPerPage');
		if((int) $options['photosPerPage'] !== 0) {
			$photosPerPage = (int) $options['photosPerPage'];
		}
		$photosPerPage		= $photosPerPage < 1 ? 1 : $photosPerPage;

		$totalPhotos		= count($allPhotos);

		$allPhotosReverse	= is_array($allPhotos) ? array_reverse($allPhotos) : array();
		$page				= (int) $param1 < 0 ? 0 : (int) $param1;
		$offset				= $page * $photosPerPage;

		$subview = new Application_View_Theme();
		$subview->loadHTML('index/index.html');

		for($i = $offset; $i < $offset + $photosPerPage; $i++) {

			if($allPhotosReverse[$i]->datenum > date('YmdHis')) {

				continue;

			} else {

				$currentPhoto = $allPhotosReverse[$i];

				if(Modules_Filesys::isFile($this->app->getProjectDir() . 'uploads/web/' . $currentPhoto->web_name)) {

					$image = new Modules_Image($this->app->getProjectDir() . 'uploads/web/' . $currentPhoto->web_name);

					$currentPhoto->width = $image->getImageWidth();
					$currentPhoto->height = $image->getImageHeight();
					$currentPhoto->photographer = $this->userMapper->find($currentPhoto->user_id, new Model_User);

					$maxWidth	= $maxWidth == NULL || $currentPhoto->width > $maxWidth ? $currentPhoto->width : $maxWidth;
					$maxHeight	= $maxHeight == NULL || $currentPhoto->height > $maxHeight ? $currentPhoto->height : $maxHeight;

				
					if($currentPhoto->tags != '') {

						$tags = explode(',', $currentPhoto->tags);

						foreach($tags AS $j => $tag) {
							$tag = htmlentities($tag, ENT_NOQUOTES, 'UTF-8');
							$tags[$j] = '<a href="' . $this->app->getBaseURL() . 'tag/' . $tag .'">' . $tag . '</a>';						
						}

						$currentPhoto->taglinks = $tags;

					} else {

						$currentPhoto->taglinks = array();

					}

					$subview->data['photos'][] = $currentPhoto;

				}

			}

		}

		if((int) $totalPhotos > 0) {

			$this->view->data['maxWidth'] = $maxWidth;
			$this->view->data['maxHeight'] = $maxHeight;

			if( (($page+1) * $photosPerPage) < $totalPhotos) {
				$subview->data['prevLink'] = $this->app->getBaseURL() . $link . '/' . ((int) $page+1);
			} else {
				$subview->data['prevLink'] = '';
			}

			if(!($page < 1)) {
				$subview->data['nextLink'] = $this->app->getBaseURL() . $link . '/' . ((int) $page-1);
			} else {
				$subview->data['nextLink'] = '';
			}

			$pagina = new Modules_Pagination;
			$pagina->setUsePages(true)
					->setAtLast(3)
					->setAtLeast(3)
					->setLink($this->app->getBaseURL() . $link . "/")
					->setItemsPerPage($photosPerPage)
					->setItemsTotal($totalPhotos)
					->currentPageNum($page);
			$subview->data['pagination'] = $pagina->render();

		}

		$this->view->addSubview('main', $subview);


	}

}