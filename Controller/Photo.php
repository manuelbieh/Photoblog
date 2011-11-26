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


	public function index($link='page', $param1=NULL) {

		$allPhotos			= $this->photoMapper->fetchAll();
		$this->showPhotos($allPhotos, $link, $param1);

	}


	public function tag($link='tag', $tag, $param1=NULL) {

		$allPhotos = $this->photoMapper->findByTag($tag);
		$this->showPhotos($allPhotos, $link . '/' . $tag, $param1, array('photosPerPage'=>5));

	}


	public function view($cleanTitle) {

		$photo				= $this->photoMapper->fetchWhere(array('clean_title'=>$cleanTitle));
		$photo				= $photo[0];

		$subview = new Application_View_Theme();

		if($photo !== NULL && Modules_Filesys::isFile($this->app->getProjectDir() . 'uploads/web/' . $photo->web_name) && $photo->datenum <= date('YmdHis')) {

			$image = new Modules_Image($this->app->getProjectDir() . 'uploads/web/' . $photo->web_name);

			$photo->width	= $image->getImageWidth();
			$photo->height	= $image->getImageHeight();

			$photo->photographer	= $this->userMapper->find($photo->user_id, new Model_User);

			$this->view->data['maxWidth'] = $photo->width;

			$subview->loadHTML('photo/view.html');
			if((int) $photo->allow_comments === 1) {
				$commentController	= new Controller_Comment($this->app);
				$subview->assign('commentform', $commentController->commentForm($photo->photo_id));
			} else {
				$subview->assign('commentform', __('Comments are closed.'));
			}

			$photo->comments		= $this->commentMapper->findByPhoto($photo->photo_id);
			$subview->data['photo'] = $photo;

			if($photo->tags != '') {
				$tags = explode(',', $photo->tags);
				foreach($tags AS $i => $tag) {
					$tag = htmlentities($tag, ENT_NOQUOTES, 'UTF-8');
					$tags[$i] = '<a href="' . $this->app->getBaseURL() . 'tag/' . $tag .'">' . $tag . '</a>';
				}
				$subview->data['photo']->taglinks = $tags;
			} else {
				$subview->data['photo']->taglinks = array();
			}

			$this->view->addSubview('main', $subview);

		} else {

			$subview->loadHTML('photo/view.notfound.error.html');
			$this->view->addSubview('main', $subview);

		}

	}


	public function overview($page=0, $order='desc') {

		$allPhotos			= $this->photoMapper->fetchAll();
		$allPhotosReverse	= is_array($allPhotos) ? array_reverse($allPhotos) : array();

		$subview = new Application_View_Theme();
		$subview->loadHTML('photo/overview.html');

		$itemsPerPage	= 20;

		$totalItems		= count($allPhotos);

		$page				= (int) $page < 0 ? 0 : (int) $page;
		$offset				= $page * $itemsPerPage;

		$subview->data['offset'] = (int) $offset;

		for($i = $offset; $i < $offset+$itemsPerPage; $i++) {
			if($order == 'desc') {
				if(isset($allPhotosReverse[$i])) {
					$subview->data['photos'][$i] = $allPhotosReverse[$i];
					$subview->data['photos'][$i]->photographer = $this->userMapper->find($allPhotosReverse[$i]->user_id, new Model_User);
				}
			} else {
				if(isset($allPhotos[$i])) {
					$subview->data['photos'][$i] = $allPhotos[$i];
					$subview->data['photos'][$i]->photographer = $this->userMapper->find($allPhotos[$i]->user_id, new Model_User);
				}					
			}
		}

		$pagina = new Modules_Pagination;
		$pagina->usePages = true;
		$pagina->setLink($this->app->getBaseURL() . "overview/")->setItemsPerPage($itemsPerPage)->setItemsTotal($totalItems)->currentPageNum($page);
		if($order == 'asc') {
			$pagina->setParams('/' . $order);
		}
		$subview->data['pagination'] = $pagina->render();


		$this->view->addSubview('main', $subview);

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
		$subview->loadHTML('photo/index.html');

		$showComments = Application_Settings::get('//theme/layout/showCommentsOnIndex');

		$subview->data['showComments'] = $showComments;

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

					$currentPhoto->photographer	= $this->userMapper->find($currentPhoto->user_id, new Model_User);

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

					if($showComments == 1) {

						if((int) $currentPhoto->allow_comments === 1) {
							$commentController = new Controller_Comment($this->app);
							$subview->assign('commentform', $commentController->commentForm($currentPhoto->photo_id));
						} else {
							$subview->assign('commentform', __('Comments are closed.'));
						}

						$currentPhoto->comments	= $this->commentMapper->findByPhoto($currentPhoto->photo_id);

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