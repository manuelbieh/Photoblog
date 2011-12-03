<?php

class Extensions_Manuel_Admin_Controller_Pixelpost extends Controller_Frontend {

	public function __construct($app) {

		$this->app = $app;

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}

		$app->extensions()->registerObservers($this);

		$this->view		= $this->app->objectManager->get('Application_View');
		$this->access	= $this->app->objectManager->get('Admin_Application_Access');

		$this->commentMapper	= new Model_Comment_Mapper(new Model_Comment_Gateway_PDO($this->app->objectManager->get('Datastore')));
		$this->photoMapper		= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO($this->app->objectManager->get('Datastore')));
		$this->userMapper		= new Model_User_Mapper(new Model_User_Gateway_PDO($this->app->objectManager->get('Datastore')));

		if(!isset($_GET['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

	}

	public function test() {

		$subview = $this->app->createView();
		$subview->loadHTML('Extensions/Manuel/Admin/templates/canyouseeme.html');

		$this->view->addSubview('main', $subview);

	}

	public function import() {

		$pixelpostDB = new PDO('mysql:dbname=d0026071;host=manuelbieh.com', 'd0026071', 'qwertz');

		$tmpFolder 		= rtrim($this->app->getProjectDir(), '/') . '/../uploads/temp/';

		$cleanTitles 	= array();
		$allPhotos 		= $this->photoMapper->fetchAll();

		if(is_array($allPhotos)) {
			foreach($allPhotos AS $photoObject) {
				$cleanTitles[] = $photoObject->clean_title;
			}
		}


		$photos = $pixelpostDB->query("SELECT * FROM pixelpost_pixelpost");

		foreach($photos->fetchAll(PDO::FETCH_ASSOC) AS $photo) {

			$exPhoto = new Model_Photo();

			$exPhoto->title = $photo['headline'];
			$exPhoto->description = (string) $photo['bodytext'];
			$exPhoto->date_publish = $photo['datetime'];

			$curlObj = new Modules_Curl();
			$curlObj->setOption(CURLOPT_CONNECTTIMEOUT, 30)
					->setOption(CURLOPT_FOLLOWLOCATION, true)
					->setOption(CURLOPT_MAXREDIRS, 5);
			$curlObj->connect('http://photos.manuelbieh.com/images/' . $photo['image']);

			$httpStatus = $curlObj->info(CURLINFO_HTTP_CODE);

			if($httpStatus < 400) {

				$imageSourceName = md5(base64_encode($photo['image']));
				$imageSource = $tmpFolder . $imageSourceName;

				$imageFile = $curlObj->output();
				file_put_contents($tmpFolder . $imageSourceName, $imageFile);

				try {

					$this->uploadImage	= new Modules_Image($imageSource);
					$uploadImageType	= $this->uploadImage->getImageMimeType();
					$uploadImageSuffix	= Modules_Functions::getSuffixByMime($uploadImageType);
					$uploadImageWidth	= $this->uploadImage->getImageWidth();
					$uploadImageHeight	= $this->uploadImage->getImageHeight();

					if(!in_array($uploadImageType, array('image/jpg', 'image/pjpeg', 'image/jpeg', 'image/gif', 'image/png'))) {
						$this->form->addError(__('Unknown filetype. Please upload JPG, GIF or PNG only.'));
					}

					$this->sourceFolder	= Extensions_Manuel_Helper::getSourceFolder();
					$this->sourceFile	= time() . base64_encode($imageSourceName) .'.'. $uploadImageSuffix;
					$this->webFile		= time() . base64_encode($imageSourceName) .'.'. $uploadImageSuffix;

					$this->app->extensions()->notify($this, 'addUploadSuccessful');

					$webSize	= Application_Settings::get("//settings/defaults/image/web");
					$thumbSize	= Application_Settings::get("//settings/defaults/image/thumb");
					$miniSize	= Application_Settings::get("//settings/defaults/image/mini");

					$this->uploadImage->thumbnailImage($webSize['maxwidth'], $webSize['maxheight'], true);
					$this->uploadImage->writeImage(rtrim(Application_Base::getProjectDir(), '/') . '/../uploads/web/' . $this->webFile);

					$this->uploadImage->thumbnailImage($thumbSize['maxwidth'], $thumbSize['maxheight'], true);
					$this->uploadImage->writeImage(rtrim(Application_Base::getProjectDir(), '/') . '/../uploads/thumbs/' . $this->webFile);

					$this->uploadImage->thumbnailImage($miniSize['maxwidth'], $miniSize['maxheight'], true);
					$this->uploadImage->writeImage(rtrim(Application_Base::getProjectDir(), '/') . '/../uploads/mini/' . $this->webFile);

					file_put_contents($this->sourceFolder . DIRECTORY_SEPARATOR . $this->sourceFile, $imageFile);

					$exPhoto->original_name		= $this->sourceFile;
					$exPhoto->original_width	= $uploadImageWidth;
					$exPhoto->original_height	= $uploadImageHeight;
					$exPhoto->web_name			= $this->webFile;
					$exPhoto->user_id			= Modules_Session::getInstance()->getVar('userdata')->user_id;
					$exPhoto->active			= 1;
					$exPhoto->clean_title		= trim($exPhoto->title) != '' ? Modules_Functions::cleanURL($exPhoto->title) : 'untitled';
					$exPhoto->clean_title		= Modules_Functions::getUniqueName($exPhoto->clean_title, $cleanTitles);

					$this->photo = $exPhoto;

					$this->app->extensions()->notify($this, 'addBeforeSavePhoto');

					$exPhoto = $this->photo;

					// Now save the file!
					$photo_id = $this->photoMapper->save($this->photo);
					$cleanTitles[] = $exPhoto->clean_title;

				} catch (Exception $e) {

					

				}

			}

			$comments = $pixelpostDB->query("SELECT * FROM pixelpost_comments WHERE parent_id = " . (int) $photo['id']);
			foreach($comments->fetchAll(PDO::FETCH_ASSOC) AS $comment) {

				$exComment = new Model_Comment();
				
				$exComment->photo_id = $photo_id;
				$exComment->datetime = $comment['datetime'];
				$exComment->name = $comment['name'];
				$exComment->url = $comment['url'];
				$exComment->email = $comment['email'];
				$exComment->text = $comment['message'];
				$exComment->active = $comment['publish'] == 'yes' ? 1 : 0;
				$exComment->ip = $comment['ip'];

				$this->commentMapper->save($exComment);

			}

			$num++;
			if($num % 30 == 0) {
				sleep(2);
			}

		}

	}



}