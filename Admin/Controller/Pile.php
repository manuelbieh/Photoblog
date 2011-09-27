<?php

class Admin_Controller_Pile {

	public function __construct() {

		$this->view = new Application_View();

		if(!isset($_POST['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

		$navi = new Application_View();

		$navi->loadHTML("templates/main/navi.html");
		$this->view->addSubview('navi', $navi);

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			Application_Base::go('Login');
			exit;
		}

	}

	public function __destruct() {
		$this->view->render(true);
	}

	public function folder($action=NULL, $folder=NULL) {

		$fileReader = new Modules_Filereader();
		$source		= basename(Extensions_Wefoundit_Helper::getSourceFolder());
		$pileDir	= rtrim(Application_Base::getCoreDir(), '/') . '/uploads/pile/' . $source;

		#$subview = new Application_View();
		#$subview->setHTML(print_r($folders, true));
		#$this->view->addSubview('main', $subview);
		$folders = $fileReader->getFoldersRecursive("/www/htdocs/w006633a/dev/photo/blog1/uploads");
		var_dump($folders);

		switch($action) {


			case 'create':
				break;


			case 'rename':
				break;


			case 'delete':
				break;


			case 'move':
				break;


			case 'view':
				
				break;

		}

	}


	public function upload() {

		$val = new Modules_JSONValidation();
		$val->setConfigByJSONFile('templates/pile/upload.form.validation.json');

		$form = new Modules_Form('templates/pile/upload.form.html');
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
						$uploadImage->writeImage(rtrim(Application_Base::getCoreDir(), '/') . '/uploads/web/' . $webFile);

						$uploadImage->thumbnailImage($thumbSize['maxwidth'], $thumbSize['maxheight'], true);
						$uploadImage->writeImage(rtrim(Application_Base::getCoreDir(), '/') . '/uploads/thumbs/' . $webFile);

						$uploadImage->thumbnailImage($miniSize['maxwidth'], $miniSize['maxheight'], true);
						$uploadImage->writeImage(rtrim(Application_Base::getCoreDir(), '/') . '/uploads/mini/' . $webFile);

						$photo = new Model_Photo();
						$photoMapper = new Model_Photo_Mapper(new Model_Photo_Gateway_PDO(Application_Registry::get('pdodb')));

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

						// Save the file!
						$photoMapper->save($photo);

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

	}

	public function view($folder=NULL, $offset=0) {

		$itemsPerPage	= 30;
		$totalItems		= count($allImages);
		$offset			= (int) $offset;

		$subview = new Application_View();
		$subview->loadHTML('templates/photo/view.html');

		$subview->data['offset'] = (int) $offset;
		for($i = $offset; $i < $offset+$itemsPerPage; $i++) {
			if(isset($allImages[$i])) {
				$subview->data['images'][$i] = $allImages[$i];
			}
		}

		$pagina = new Modules_Pagination;
		$pagina->setLink(Application_Base::getBaseURL() . "Photo/view/")->setItemsPerPage($itemsPerPage)->setItemsTotal($totalItems)->currentPageNum($offset);
		$subview->data['pagination'] = $pagina->render();

		$this->view->addSubview('main', $subview);

	}

	public function publish($image) {

	}

	public function delete($photo_id) {

	}

	public function unrelease($image_id) {

	}

	public function hide($image_id) {
		
	}

	public function show($image_id) {
		
	}

}