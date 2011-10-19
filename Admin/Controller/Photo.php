<?php

class Admin_Controller_Photo extends Controller_Frontend implements Application_Observable {

	public function __construct($app=NULL) {

		$app->extensions()->registerObservers($this);

		$this->app = $app;

		$this->view		= new Application_View();
		$this->access	= $this->app->objectManager->get('Admin_Application_Access');

		$this->app->objectManager->register('photoMapper', new Model_Photo_Mapper(new Model_Photo_Gateway_PDO($this->app->getGlobal('pdodb'))));

		if(!isset($_POST['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

		$navi = new Application_View();
		$navi->app = $app;
		$navi->loadHTML("templates/main/navi.html");
		$this->view->addSubview('navi', $navi);

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			Application_Base::go('Login');
			exit;
		}

		$this->notify('constructorEnd');

	}

	public function add() {

		if($this->access->check(__METHOD__)) {

			$val = new Modules_JSONValidation();
			$val->setConfigByJSONFile('templates/photo/add.form.validation.json');

			$form = new Modules_Form('templates/photo/add.form.html');
			$form->setValidation($val);

			if($form->isSent()) {

				if(!$_FILES['upload'] || $_FILES['upload']['error'][0] !== 0) {

					$form->addError(__('Unable to upload file.'));

				} else {

					try {

						$uploadImage		= new Modules_Image($_FILES['upload']['tmp_name'][0]);
						$uploadImageType	= $uploadImage->getImageMimeType();
						$uploadImageSuffix	= Modules_Functions::getSuffixByMime($uploadImageType);
						$uploadImageWidth	= $uploadImage->getImageWidth();
						$uploadImageHeight	= $uploadImage->getImageHeight();

						if(!in_array($uploadImageType, array('image/jpg', 'image/pjpeg', 'image/jpeg', 'image/gif', 'image/png'))) {
							$form->addError(__('Unknown filetype. Please upload JPG, GIF or PNG only.'));
						}

						if($form->isSent(true)) {

							$source		= Extensions_Wefoundit_Helper::getSourceFolder();
							$sourceFile	= time() . base64_encode($_FILES['upload']['name'][0]) .'.'. $uploadImageSuffix;
							$webFile	= time() . base64_encode($_FILES['upload']['name'][0]) .'.'. $uploadImageSuffix;

							move_uploaded_file($_FILES['upload']['tmp_name'][0], $source . DIRECTORY_SEPARATOR . $sourceFile);

							$webSize	= Application_Settings::get("//settings/defaults/image/web");
							$thumbSize	= Application_Settings::get("//settings/defaults/image/thumb");
							$miniSize	= Application_Settings::get("//settings/defaults/image/mini");

							$uploadImage->thumbnailImage($webSize['maxwidth'], $webSize['maxheight'], true);
							$uploadImage->writeImage(rtrim(Application_Base::getProjectDir(), '/') . '/../uploads/web/' . $webFile);

							$uploadImage->thumbnailImage($thumbSize['maxwidth'], $thumbSize['maxheight'], true);
							$uploadImage->writeImage(rtrim(Application_Base::getProjectDir(), '/') . '/../uploads/thumbs/' . $webFile);

							$uploadImage->thumbnailImage($miniSize['maxwidth'], $miniSize['maxheight'], true);
							$uploadImage->writeImage(rtrim(Application_Base::getProjectDir(), '/') . '/../uploads/mini/' . $webFile);

							$photo = new Model_Photo();
							$photoMapper = new Model_Photo_Mapper(new Model_Photo_Gateway_PDO($this->app->getGlobal('pdodb')));

							$cleanTitles = array();
							$allPhotos = $photoMapper->fetchAll();
							if(is_array($allPhotos)) {
								foreach($allPhotos AS $photoObject) {
									$cleanTitles[] = $photoObject->clean_title;
								}
							}

							foreach($form->valueOf('data') AS $key => $value) {
								$photo->$key = $value;
							}

							$photo->date_uploaded = date('Y-m-d H:i:s');

							if($form->valueOf('instant') == 1 || $photo->date_publish == '') {
								$photo->date_publish = $photo->date_uploaded;
							}

							$photo->original_name	= $sourceFile;
							$photo->original_width	= $uploadImageWidth;
							$photo->original_height	= $uploadImageHeight;
							$photo->web_name		= $webFile;
							$photo->user_id			= Modules_Session::getInstance()->getVar('userdata')->user_id;
							$photo->active			= 1;
							$photo->clean_title		= Modules_Functions::cleanURL($photo->title);
							$photo->clean_title		= Modules_Functions::getUniqueName($photo->clean_title, $cleanTitles);

							// Save the file!
							$photo_id = $photoMapper->save($photo);

							if($photo_id != false && in_array($uploadImageType, array('image/jpeg', 'image/pjpeg', 'image/jpg'))) {

								$exifMapper = new Model_Exif_Mapper(new Model_Exif_Gateway_PDO($this->app->getGlobal('pdodb')));
								$exifData = json_encode(exif_read_data($source . DIRECTORY_SEPARATOR . $sourceFile));

								$exif = new Model_Exif();
								$exif->photo_id = $photo_id;
								$exif->exif_data = $exifData;

								$exifMapper->save($exif);

							}

						}

					} catch(Exception $e) {

						$form->addError(__('Unable to upload file.'));

					}

				}

			}

			if($form->isSent(true)) {

				$subview = new Application_View();
				$subview->loadHTML('templates/photo/add.success.html');
				$this->view->addSubview('main', $subview);

			} else {

				$this->view->addSubview('main', $form);

			}

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
		}

	}

	public function view($offset=0) {

		if($this->access->check(__METHOD__)) {

			$photoMapper	= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO($this->app->getGlobal('pdodb')));
			$allPhotos		= $photoMapper->fetchAll();
			$allPhotosReverse	= is_array($allPhotos) ? array_reverse($allPhotos) : array();

			$itemsPerPage	= Application_Settings::get("//settings/system/backend/photosPerPage", 1);
			$itemsPerPage	= (int) $itemsPerPage === 0 ? 15 : $itemsPerPage;

			$totalItems		= count($allPhotos);
			$offset			= (int) $offset;

			$subview = new Application_View();
			$subview->loadHTML('templates/photo/view.html');

			$subview->data['offset'] = (int) $offset;
			for($i = $offset; $i < $offset+$itemsPerPage; $i++) {
				if(isset($allPhotosReverse[$i])) {
					$subview->data['images'][$i] = $allPhotosReverse[$i];
				}
			}

			$pagina = new Modules_Pagination;
			$pagina->setLink(Application_Base::getBaseURL() . "Photo/view/")->setItemsPerPage($itemsPerPage)->setItemsTotal($totalItems)->currentPageNum($offset);
			$subview->data['pagination'] = $pagina->render();

			$this->view->addSubview('main', $subview);

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
		}

	}

	public function edit($photo_id) {

		$photoMapper	= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO($this->app->getGlobal('pdodb')));
		$photo 			= $photoMapper->find($photo_id, new Model_Photo());

		$exifMapper		= new Model_Exif_Mapper(new Model_Exif_Gateway_PDO($this->app->getGlobal('pdodb')));
		$exif 			= $exifMapper->find($photo_id, new Model_Exif());

		$login_user_id = Modules_Session::getInstance()->getVar('userdata')->user_id;

		if($login_user_id) { 
			if((int) $photo->user_id === (int) $login_user_id) {
				$allowed = $this->access->check(__METHOD__, 'own');
			} else if((int) $photo->user_id !== (int) $login_user_id) {
				$allowed = $this->access->check(__METHOD__, 'other');
			}
		} else {
			$allowed = false;
		}

		if($allowed) {

			if($photo !== false) {

				$form = new Modules_Form();

				if($form->isSent(true)) {

					foreach($form->valueOf('data') AS $property => $value) {
						$photo->$property = $value;
					}

					$photo->exif = $form->valueOf('data[exif]') == false ? 0 : $form->valueOf('data[exif]');
					$photo->allow_comments = $form->valueOf('data[allow_comments]') == false ? 0 : $form->valueOf('data[allow_comments]');

					$photo_id = $photoMapper->save($photo);
					$subview = new Application_View();
					$subview->loadHTML('templates/photo/edit.form.success.html');
					$this->view->addSubview('main', $subview);

				} else {

					$form->data['photo'] = $photo;
					$form->data['exif'] = json_decode($exif->exif_data, true);
					$form->loadTemplate('templates/photo/edit.form.html');
					$this->view->addSubview('main', $form);

				}

			} else {

				$subview = new Application_View();
				$subview->loadHTML('templates/photo/edit.error.notfound.html');
				$this->view->addSubview('main', $subview);

			}

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
		}

	}

	public function delete($photo_id) {

		$photoMapper	= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO($this->app->getGlobal('pdodb')));
		$photo 			= $photoMapper->find($photo_id, new Model_Photo());

		$login_user_id = Modules_Session::getInstance()->getVar('userdata')->user_id;

		if($login_user_id) {
			if((int) $photo->user_id === (int) $login_user_id) {
				$allowed = $this->access->check(__METHOD__, 'own');
			} else if((int) $photo->user_id !== (int) $login_user_id) {
				$allowed = $this->access->check(__METHOD__, 'other');
			}
		} else {
			$allowed = false;
		}

		if($allowed) {

			if($photo !== false && isset($_POST['confirm'])) {

				$files = array();

				$files['original']	= Extensions_Wefoundit_Helper::getSourceFolder() . '/' . $photo->original_name;
				$files['web']		= rtrim(Application_Base::getProjectDir(), '/') . '/../uploads/web/' . $photo->web_name;
				$files['thumb']		= rtrim(Application_Base::getProjectDir(), '/') . '/../uploads/thumbs/' . $photo->web_name;
				$files['mini']		= rtrim(Application_Base::getProjectDir(), '/') . '/../uploads/mini/' . $photo->web_name;

				foreach($files AS $file) {

					if(is_file($file)) {
						unlink($file);
					}

				}

				$deleted = $photoMapper->delete($photo_id);

			} else if($photo === false) {

				$subview = new Application_View();
				$subview->loadHTML('templates/photo/delete.error.notfound.html');
				$this->view->addSubview('main', $subview);
			
			} else if(isset($_POST['cancel'])) {
				Application_Base::go($_POST['r']);
			} else {

				if($_POST['ajax']) {
					
				} else {
					$form = new Modules_Form('templates/photo/delete.form.html');
					$form->assign('web_name', $photo->web_name);
					$this->view->addSubview('main', $form);
				}

				$deleted = false;

			}

			if($_POST['ajax']) {

				$subview = new Application_View();
				if($deleted == true) {
					$subview->setHTML(__('File was deleted successfully.'));
				} else {
					$subview->setHTML(__('File could not be deleted.'));
				}
				$this->view->addSubview('main', $subview);

			} else {

				

			}

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
		}

		// getImageId() -> getOriginalName, getWebName
		// delete uploads/source/image
		// delete uploads/web/image
		// delete uploads/thumbs/image
		// delete uploads/mini/image
		// delete db entry
		// call stock api -> delete (blog_id, image_id)

	}

	public function unrelease($image_id) {

	}

	public function hide($image_id) {
		
	}

	public function show($image_id) {
		
	}

	public function addObserver($observer) {

		array_push($this->observers, $observer);

	}

	public function notify($state, $additionalParams=NULL) {

		foreach((array) $this->observers AS $obs) {

			if(method_exists($obs, $state)) {

				$obs->$state(&$this, $additionalParams);

			}

		}

	}

}