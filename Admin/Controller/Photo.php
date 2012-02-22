<?php

class Admin_Controller_Photo extends Controller_Frontend {

	protected $access;
	protected $app;
	protected $exif;
	protected $photo;
	protected $sourceFile;
	protected $sourceFolder;
	protected $uploadImage;
	protected $webFile;
	protected $view;

	public function __construct($app) {

		$this->app = $app;

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}

		$app->extensions()->registerObservers($this);

		$this->view			= $this->app->objectManager->get('Application_View');
		$this->access		= $this->app->objectManager->get('Admin_Application_Access');

		$this->photoGateway	= new Model_Photo_Gateway_PDO($app->objectManager->get('Datastore'));

		$this->app->objectManager->register('photoMapper', new Model_Photo_Mapper($this->photoGateway));

		if(!$this->app->isAjaxRequest()) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

		$this->app->extensions()->notify($this, 'constructorEnd');

	}

	public function add() {

		$subview = $this->app->createView();

		if($this->access->check(__METHOD__)) {

			$val = new Modules_JSONValidation();
			$val->setConfigByJSONFile('templates/photo/add.form.validation.json');

			$this->form = new Modules_Form('templates/photo/add.form.html');
			$this->form->setValidation($val);

			if($this->form->isSent()) {

				if($this->form->valueOf('source') == 'web') {

					$curlObj = new Modules_Curl();
					$curlObj->setOption(CURLOPT_FOLLOWLOCATION, true);
					$curlObj->setOption(CURLOPT_MAXREDIRS, 3);
					$curlObj->setOption(CURLOPT_CONNECTTIMEOUT, 60);

					$curlObj->connect($this->form->valueOf('download[url]'));
					$httpStatus = $curlObj->info(CURLINFO_HTTP_CODE);

					if($httpStatus > 400) {

						$this->view->addSubview('main', $this->form);
						$this->form->addError(__('Downloading the file from the URL you specified was not successful.'));
						return;

					} else {

						$tmpFolder = rtrim($this->app->getProjectDir(), '/') . '/../uploads/temp/';

						$imageSourceName = md5(base64_encode($this->form->valueOf('download[url]')));
						$imageSource = $tmpFolder . $imageSourceName;

						$imageFile = $curlObj->output();
						file_put_contents($tmpFolder . $imageSourceName, $imageFile);

					}

				} else {

					if(!$_FILES['upload'] || $_FILES['upload']['error'][0] !== 0) {

						$this->view->addSubview('main', $this->form);
						$this->form->addError(__('Unable to upload file.'));
						return;

					} else {

						$imageSourceName = $_FILES['upload']['name'][0];
						$imageSource = $_FILES['upload']['tmp_name'][0];

					}
				
				}

				try {

					$this->uploadImage	= new Modules_Image($imageSource);
					$uploadImageType	= $this->uploadImage->getImageMimeType();
					$uploadImageSuffix	= Modules_Functions::getSuffixByMime($uploadImageType);
					$uploadImageWidth	= $this->uploadImage->getImageWidth();
					$uploadImageHeight	= $this->uploadImage->getImageHeight();

					if(!in_array($uploadImageType, array('image/jpg', 'image/pjpeg', 'image/jpeg', 'image/gif', 'image/png'))) {
						$this->form->addError(__('Unknown filetype. Please upload JPG, GIF or PNG only.'));
					}

					if($this->form->isSent(true)) {

						$this->sourceFolder	= Extensions_Manuel_Helper::getSourceFolder();
						$this->sourceFile	= time() . base64_encode($imageSourceName) .'.'. $uploadImageSuffix;
						$this->webFile		= time() . base64_encode($imageSourceName) .'.'. $uploadImageSuffix;

						$this->app->extensions()->notify($this, 'addUploadSuccessful');

						$webSize	= Application_Settings::get("//settings/defaults/image/web");
						$thumbSize	= Application_Settings::get("//settings/defaults/image/thumb");
						$miniSize	= Application_Settings::get("//settings/defaults/image/mini");

						$webSize['maxwidth']  = $uploadImageWidth < $webSize['maxwidth'] ? $uploadImageWidth : $webSize['maxwidth'];
						$webSize['maxheight'] = $uploadImageHeight < $webSize['maxheight'] ? $uploadImageHeight : $webSize['maxheight'];
						$this->uploadImage->thumbnailImage($webSize['maxwidth'], $webSize['maxheight'], true);
						$this->uploadImage->writeImage(rtrim(Application_Base::getProjectDir(), '/') . '/../uploads/web/' . $this->webFile);

						$thumbSize['maxwidth']  = $uploadImageWidth < $thumbSize['maxwidth'] ? $uploadImageWidth : $thumbSize['maxwidth'];
						$thumbSize['maxheight'] = $uploadImageHeight < $thumbSize['maxheight'] ? $uploadImageHeight : $thumbSize['maxheight'];
						$this->uploadImage->thumbnailImage($thumbSize['maxwidth'], $thumbSize['maxheight'], true);
						$this->uploadImage->writeImage(rtrim(Application_Base::getProjectDir(), '/') . '/../uploads/thumbs/' . $this->webFile);

						$miniSize['maxwidth']  = $uploadImageWidth < $miniSize['maxwidth'] ? $uploadImageWidth : $miniSize['maxwidth'];
						$miniSize['maxheight'] = $uploadImageHeight < $miniSize['maxheight'] ? $uploadImageHeight : $miniSize['maxheight'];
						$this->uploadImage->thumbnailImage($miniSize['maxwidth'], $miniSize['maxheight'], true);
						$this->uploadImage->writeImage(rtrim(Application_Base::getProjectDir(), '/') . '/../uploads/mini/' . $this->webFile);


						if($this->form->valueOf('source') == 'web') {
							file_put_contents($this->sourceFolder . DIRECTORY_SEPARATOR . $this->sourceFile, $imageFile);
							unlink($imageSource);
						} else {
							move_uploaded_file($_FILES['upload']['tmp_name'][0], $this->sourceFolder . DIRECTORY_SEPARATOR . $this->sourceFile);
						}

						$photo = new Model_Photo();
						$photoMapper = new Model_Photo_Mapper(new Model_Photo_Gateway_PDO($this->app->objectManager->get('Datastore')));

						$cleanTitles = array();
						$allPhotos = $photoMapper->fetchAll();
						if(is_array($allPhotos)) {
							foreach($allPhotos AS $photoObject) {
								$cleanTitles[] = $photoObject->clean_title;
							}
						}

						foreach($this->form->valueOf('data') AS $key => $value) {
							$photo->$key = $value;
						}

						$photo->date_uploaded = date('Y-m-d H:i:s');

						if($this->form->valueOf('instant') == 1 || $photo->date_publish == '') {
							$photo->date_publish = $photo->date_uploaded;
						}

						$photo->original_name	= $this->sourceFile;
						$photo->original_width	= $uploadImageWidth;
						$photo->original_height	= $uploadImageHeight;
						$photo->web_name		= $this->webFile;
						$photo->user_id			= Modules_Session::getInstance()->getVar('userdata')->user_id;
						$photo->active			= 1;
						$photo->clean_title		= $photo->title != '' ? Modules_Functions::cleanURL($photo->title) : 'untitled';
						$photo->clean_title		= Modules_Functions::getUniqueName($photo->clean_title, $cleanTitles);

						$this->photo = $photo;
						$this->app->extensions()->notify($this, 'addBeforeSavePhoto');

						// Now save the file!
						$photo_id = $photoMapper->save($this->photo);

						if($photo_id == false) {

							
							$this->app->extensions()->notify($this, 'addPhotoError');
							$subview->loadHTML('templates/photo/add.error.html');

						} else {

							if($photo_id != false && in_array($uploadImageType, array('image/jpeg', 'image/pjpeg', 'image/jpg'))) {

								$exifMapper = new Model_Exif_Mapper(new Model_Exif_Gateway_PDO($this->app->objectManager->get('Datastore')));
								$exifData = json_encode(exif_read_data($this->sourceFolder . DIRECTORY_SEPARATOR . $this->sourceFile));

								$exif = new Model_Exif();
								$exif->photo_id = $photo_id;
								$exif->exif_data = $exifData;

								$this->exif = $exif;
								$this->app->extensions()->notify($this, 'saveExif');

								$exifMapper->save($this->exif);

							}

							$this->app->extensions()->notify($this, 'addPhotoSuccess');
							$subview->loadHTML('templates/photo/add.success.html');

						}

						$this->view->addSubview('main', $subview);

					} else {

						$this->view->addSubview('main', $this->form);

					}


				} catch(Exception $e) {

					$this->form->addError(__('Unable to upload file.'));
					$this->view->addSubview('main', $this->form);

				}



			} else {

				$this->view->addSubview('main', $this->form);

			}

		} else {

			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());

		}

	}

	public function view($offset=0, $order='desc') {

		if($this->access->check(__METHOD__)) {

			$order = strtolower($order);

			$order			= $order == 'asc' ? 'asc' : 'desc';
			$revOrder		= $order == 'asc' ? 'desc' : 'asc';

			$photoMapper	= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO($this->app->objectManager->get('Datastore')));
			$userMapper		= new Model_User_Mapper(new Model_User_Gateway_PDO($this->app->objectManager->get('Datastore')));

			$allPhotos			= $photoMapper->fetchAll();
			$allPhotosReverse	= is_array($allPhotos) ? array_reverse($allPhotos) : array();

			$itemsPerPage	= Application_Settings::get("//settings/system/backend/photosPerPage", 1);
			$itemsPerPage	= (int) $itemsPerPage === 0 ? 15 : $itemsPerPage;

			$totalItems		= count($allPhotos);
			$offset			= (int) $offset;

			$subview = $this->app->createView();
			$subview->loadHTML('templates/photo/view.html');

			$subview->data['offset'] = (int) $offset;
			for($i = $offset; $i < $offset+$itemsPerPage; $i++) {
				if($order == 'desc') {
					if(isset($allPhotosReverse[$i])) {
						$subview->data['images'][$i] = $allPhotosReverse[$i];
						$subview->data['images'][$i]->photographer = $userMapper->find($allPhotosReverse[$i]->user_id, new Model_User);
					}
				} else {
					if(isset($allPhotos[$i])) {
						$subview->data['images'][$i] = $allPhotos[$i];
						$subview->data['images'][$i]->photographer = $userMapper->find($allPhotos[$i]->user_id, new Model_User);
					}					
				}
			}

			$pagina = new Modules_Pagination;
			$pagina->setLink(Application_Base::getBaseURL() . "Photo/view/")->setItemsPerPage($itemsPerPage)->setItemsTotal($totalItems)->currentPageNum($offset);
			if($order == 'asc') {
				$pagina->setParams('/' . $order);
			}
			$subview->data['pagination'] = $pagina->render();

			$subview->data['sortLink'] = Modules_Functions::patternReplace(Application_Base::getBaseURL() . "Photo/view/%page%/%sort%", array('page'=>$offset, 'sort'=>$revOrder));
			$subview->data['sortLabel'] = $order == 'asc' ? __('Show newest first') : __('Show oldest first');
			$subview->data['sort'] = htmlentities($order, ENT_NOQUOTES, 'UTF-8');

			$this->view->addSubview('main', $subview);

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
		}

	}

	public function edit($photo_id) {

		$photoMapper	= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO($this->app->objectManager->get('Datastore')));
		$photo 			= $photoMapper->find($photo_id, new Model_Photo());

		$exifMapper		= new Model_Exif_Mapper(new Model_Exif_Gateway_PDO($this->app->objectManager->get('Datastore')));
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

					$this->photo = $photo;

					$this->app->extensions()->notify($this, 'editBeforeSavePhoto');
					$photo_id = $photoMapper->save($this->photo);

					// Check is needed if data was saved correctly! (+ notifiers)
					$subview = $this->app->createView();
					$subview->loadHTML('templates/photo/edit.form.success.html');
					$this->view->addSubview('main', $subview);

				} else {

					$form->data['photo'] = $photo;
					$form->data['exif'] = json_decode($exif->exif_data, true);
					$form->loadTemplate('templates/photo/edit.form.html');
					$this->view->addSubview('main', $form);

				}

			} else {

				$subview = $this->app->createView();
				$subview->loadHTML('templates/photo/edit.error.notfound.html');
				$this->view->addSubview('main', $subview);

			}

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
		}

	}

	public function delete($photo_id) {

		$photoMapper	= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO($this->app->objectManager->get('Datastore')));
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

				$files['original']	= Extensions_Manuel_Helper::getSourceFolder() . '/' . $photo->original_name;
				$files['web']		= rtrim($this->app->getProjectDir(), '/') . '/../uploads/web/' . $photo->web_name;
				$files['thumb']		= rtrim($this->app->getProjectDir(), '/') . '/../uploads/thumbs/' . $photo->web_name;
				$files['mini']		= rtrim($this->app->getProjectDir(), '/') . '/../uploads/mini/' . $photo->web_name;

				foreach($files AS $file) {

					if(is_file($file)) {
						unlink($file);
					}

				}

				$deleted = $photoMapper->delete($photo_id);

			} else if($photo === false) {

				$subview = $this->app->createView();
				$subview->loadHTML('templates/photo/delete.error.notfound.html');
				$this->view->addSubview('main', $subview);
			
			} else if(isset($_POST['cancel'])) {
				$this->app->go($_POST['r']);
			} else {

				if($this->app->isAjaxRequest()) {
					
				} else {
					$form = new Modules_Form('templates/photo/delete.form.html');
					$form->assign('web_name', $photo->web_name);
					$this->view->addSubview('main', $form);
				}

				$deleted = false;

			}

			if($this->app->isAjaxRequest()) {

				$subview = $this->app->createView();
				if($deleted == true) {
					$subview->setHTML(__('File was deleted successfully.'));
				} else {
					$subview->setHTML(__('File could not be deleted.'));
				}
				$this->view->addSubview('main', $subview);

			} else {

				$subview = $this->app->createView();
				$subview->loadHTML('templates/photo/delete.success.html');
				$this->view->addSubview('main', $subview);

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

}