<?php

class Admin_Controller_User extends Controller_Frontend implements Application_Observable {

	public $app;
	protected $observers = array();

	public function __construct($app=NULL) {

		$app->extensions()->registerObservers($this);

		$this->app 		= $app;
		$this->userDB	= new Model_User_Gateway_PDO($app->objectManager->get('Datastore'));
		$this->view		= new Application_View();
		$this->enc		= new Modules_Encryption_Md5();

		$this->access	= $this->app->objectManager->get('Admin_Application_Access');

		$this->app->objectManager->register('userMapper', new Model_User_Mapper($this->userDB));

		$this->notify('configEnd');

		if(!isset($_POST['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

		$this->notify('templateLoaded');

		$navi = new Application_View();
		$navi->loadHTML("templates/main/navi.html");
		$navi->app = $app;
		$this->view->addSubview('navi', $navi);

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			header("Location: " . Application_Base::getBaseURL() . 'Login');
			exit;
		}

		$this->notify('constructorEnd');

	}

/*
	public function setUserMapper($userMapper) {
		$this->userMapper = $userMapper;
		
	}

	protected function getUserMapper() {
		return $this->userMapper;
	}
*/

	public function view($offset=0) {

		if($this->access->check(__METHOD__)) {

			//$userMapper		= new Model_User_Mapper($this->userDB);
			$userMapper		= $this->app->objectManager->get('userMapper');
			$allUsers		= $userMapper->fetchAll();

			$itemsPerPage	= 3;
			$totalItems		= count($allUsers);
			$offset			= (int) $offset;

			$subview = new Application_View();
			$subview->data['access'] = $this->app->objectManager->get('Admin_Application_Access');
			$subview->loadHTML('templates/user/view.html');

			$subview->data['offset'] = (int) $offset;
			for($i = $offset; $i < $offset+$itemsPerPage; $i++) {
				if(isset($allUsers[$i])) {
					$subview->data['users'][$i] = $allUsers[$i];
				}
			}

			$pagina = new Modules_Pagination;
			$pagina->setLink(Application_Base::getBaseURL() . "User/view/")->setItemsPerPage($itemsPerPage)->setItemsTotal($totalItems)->currentPageNum($offset);
			$subview->data['pagination'] = $pagina->render();

			$this->view->addSubview('main', $subview);

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
		}

	}

	public function profile($username=NULL) {

		$subview = new Application_View();

		if($username == NULL) {

			$subview->loadHTML('templates/user/profile.nouser.error.html');
			$this->view->addSubview('main', $subview);

		} else {

			$subview->data = $this->userDB->getUserDataByField('username', $username);

			$login_user_id = Modules_Session::getInstance()->getVar('userdata')->user_id;

			if($user_id === NULL) {
				$user_id = Modules_Session::getInstance()->getVar('userdata')->user_id;
			}

			if($login_user_id) { // user is logged in?
				// no user was specified or $user_id given equals login_user
				if($subview->data['user_id'] === NULL || ($subview->data['user_id'] !== NULL && $login_user_id === $user_id) ) {
					#$allowed = $this->app->getGlobal('access')->check(__METHOD__, 'own');
					$allowed = $this->access->check(__METHOD__, 'own');
				// login_user_id is not user_id, check if login_user may edit others
				} else if($subview->data['user_id'] !== NULL && ($subview->data['user_id'] !== $login_user_id)) {
					$allowed = $this->access->check(__METHOD__, 'other');
				}
			} else {
				$allowed = false;
			}

			if($allowed === true) {

				if($subview->data == false) {

					$subview->loadHTML('templates/user/profile.nouser.error.html');

				} else {

					$subview->loadHTML('templates/user/profile.html');
					$this->view->addSubview('main', $subview);

				}

			} else {
				$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
			}

		}

	}

	public function edit($user_id=NULL) {

		$login_user_id = Modules_Session::getInstance()->getVar('userdata')->user_id;

		if($user_id === NULL) {
			$user_id = Modules_Session::getInstance()->getVar('userdata')->user_id;
		}

		if($login_user_id) { // user is logged in?
			// no user was specified or $user_id given equals login_user
			if($user_id === NULL || ($user_id !== NULL && $login_user_id === $user_id) ) {
				#$allowed = $this->app->getGlobal('access')->check(__METHOD__, 'own');
				$allowed = $this->access->check(__METHOD__, 'own');
			// login_user_id is not user_id, check if login_user may edit others
			} else if($user_id !== NULL && ($user_id !== $login_user_id)) {
				$allowed = $this->access->check(__METHOD__, 'other');
			}
		} else {
			$allowed = false;
		}

		if($allowed === true) {

			$user = new Model_User();
			#$userMapper = new Model_User_Mapper($this->userDB);
			$userMapper = $this->app->objectManager->get('userMapper');
			$userMapper->find($user_id, $user);

			$form = new Modules_Form();
			$form->data = $user;
			$form->loadTemplate('templates/user/edit.form.html');

			$v = new Modules_JSONValidation();
			$v->setConfigByJSONFile('templates/user/edit.main.json');

			if($form->isSent() && $form->valueOf('data[password]') != '') {
				$v->setConfigByJSONFile('templates/user/edit.password.json');
			}

			$form->setValidation($v);

			$blacklist = array('user_id', 'username', 'passconf', 'loginhash', 'active', 'date_signup', 'last_login', 'loggedin');

			$avatar = $this->handleAvatarUpload($form);
			if($avatar !== false && $form->valueOf('avatar[delete]') != 1) {
				$user->avatar = $avatar;
			} else if($form->valueOf('avatar[delete]') == 1) {
				$user->avatar = '';
			}

			if($form->isSent(true)) {

				foreach($form->valueOf('data') AS $prop => $value) {

					if(!in_array($prop, $blacklist)) {

						if($prop != 'password') {
							$user->$prop = $value;
						} else if ($prop === 'password' && strlen($value) > 0) {
							$user->$prop = $this->enc->encryptWithSalt($value, __SALT__);
						}

					}

				}

				$userMapper = $this->app->objectManager->get('userMapper');
				$userMapper->save($user);

				Modules_Session::getInstance()->setVar('userdata', $user);

				$subview = new Application_View();
				$subview->loadHTML('templates/user/edit.success.html');
				$this->view->addSubview('main', $subview);

			} else {

				$this->view->addSubview('main', $form);

			}

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
		}

	}

	public function delete($user_id) {

		$login_user_id = (int) Modules_Session::getInstance()->getVar('userdata')->user_id;

		if($user_id === NULL) {
			$user_id = Modules_Session::getInstance()->getVar('userdata')->user_id;
		}

		if(
			(
				$login_user_id === $user_id && $this->access->check(__METHOD__, 'own') ||
				($login_user_id !== $user_id && $this->access->check(__METHOD__, 'other'))
			)
		) {

			$userMapper = $this->app->objectManager->get('userMapper');#
			$userCount = count($userMapper->fetchAll());
			if($userCount > 1) {
				if($userMapper->delete($user_id)) {
					
				} else {
				
				}
			} else {
				// cannot delete last user
			}

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
		}

	}

	public function add() {

		$login_user_id = Modules_Session::getInstance()->getVar('userdata')->user_id;

		if($login_user_id) { // user is logged in?
			$allowed = $this->access->check(__METHOD__);
		} else {
			$allowed = false;
		}

		if($allowed === true) {
			
			$this->form = new Modules_Form('templates/user/add.form.html');

			$validation = new Admin_Controller_User_Validation();
			$validation->checkUsername($this->form->valueOf('data[username]'));

			$this->form->addValidation($validation);

			$avatar = $this->handleAvatarUpload($this->form);

			if($this->form->isSent(true)) {

				$user = new Model_User();

				if($avatar !== false) {
					$user->avatar = $avatar;
				}

				foreach($this->form->valueOf('data') AS $prop => $value) {

					if($prop !== 'password') {
						$user->$prop = $value;
					} else {
						$user->$prop = $this->enc->encryptWithSalt($value, __SALT__);
					}

				}

				$userMapper = $this->app->objectManager->get('userMapper');
				$newUser = $userMapper->save($user);

				$this->subview = new Application_View();

				if($newUser != false) {
					$this->subview->data['user']->user_id = $newUser;
					$this->subview->loadHTML('templates/user/add.success.html');
					$this->notify('addSuccess');
					$this->view->addSubview('main', $this->subview);
				} else {
					$this->form->addError(__('An unknown error occured. Please try again.'));
					$this->notify('addError');
					$this->view->addSubview('main', $this->form);
				}

			} else {

				$this->view->addSubview('main', $this->form);

			}

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
		}

	}

	public function permissions($user_id=NULL) {

		// doesn't yet check if a user actually exists
		if($user_id == NULL) {

			$subview = new Application_View();
			$subview->loadHTML('templates/user/permissions.error.nouser.html');
			$this->view->addSubview('main', $subview);
			return;

		}

		$login_user_id = Modules_Session::getInstance()->getVar('userdata')->user_id;

		if($user_id === NULL) {
			$user_id = Modules_Session::getInstance()->getVar('userdata')->user_id;
		}

		if($login_user_id) { // user is logged in?
			// no user was specified or $user_id given equals login_user
			if($user_id === NULL || ($user_id !== NULL && $login_user_id === $user_id) ) {
				$allowed = $this->access->check(__METHOD__, 'own');
			// login_user_id is not user_id, check if login_user may edit others
			} else if($user_id !== NULL && ($user_id !== $login_user_id)) {
				$allowed = $this->access->check(__METHOD__, 'other');
			}
		} else {
			$allowed = false;
		}

		if($allowed === true) {

			$form = new Modules_Form();

			$form->data['permissions']['selected'] = array();
			$form->data['permissions']['options'] = array();

			$permissionMapper = new Model_Permission_Mapper(new Model_Permission_Gateway_PDO($this->app->getGlobal('pdodb')));
			$permissions = $permissionMapper->fetchAll();

			if(is_array($permissions)) {
				foreach($permissions AS $permissionModel) {
					$form->data['permissions']['options'][$permissionModel->permission_id] = $permissionModel->title;
				}
				asort($form->data['permissions']['options']);
				$form->data['permissions']['selected'] = $permissionMapper->findPermissionsByUserId($user_id);
			}

			$form->loadTemplate('templates/user/permissions.form.html');

			if($form->isSent(true)) {

				$subview = new Application_View();

				#$userMapper = new Model_User_Mapper(new Model_User_Gateway_PDO($this->app->getGlobal('pdodb')));
				#$userMapper = $this->getUserMapper();
				$userMapper = $this->app->objectManager->get('userMapper');

				// save permissions
				if($userMapper->savePermissions($user_id, $form->valueOf('data[permissions]'))) {
					$subview->loadHTML('templates/user/permissions.success.html');
				} else {
					$subview->loadHTML('templates/user/permissions.error.html');
				}

				$this->view->addSubview('main', $subview);

			} else {

				$this->view->addSubview('main', $form);

			}

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
		}
	
	}

	protected function handleAvatarUpload(&$formObject) {

		if($formObject->isSent() && $_FILES 
			&& $_FILES['avatar']['error'][0] !== 0 && $_FILES['avatar']['error'][0] !== 4) {

			$formObject->addError(__('Unable to upload avatar file.'));

		} else if($formObject->isSent() && $_FILES['avatar'] 
			&& $_FILES['avatar']['error'][0] === 0) {

			try {

				$avatarImage		= new Modules_Image($_FILES['avatar']['tmp_name'][0]);
				$avatarImageType	= $avatarImage->getImageMimeType();
				$avatarImageSuffix	= Modules_Functions::getSuffixByMime($avatarImageType);
				$avatarImageWidth	= $avatarImage->getImageWidth();
				$avatarImageHeight	= $avatarImage->getImageHeight();

				if(!in_array($avatarImageType, array('image/jpg', 'image/pjpeg', 'image/jpeg', 'image/gif', 'image/png'))) {

					$formObject->addError(__('Unknown filetype. Please upload JPG, GIF or PNG only.'));

				} else {

					$avatarDir = rtrim($this->app->getProjectDir(), '/') . '/../uploads/avatars/';
					$avatarFileName = md5(Modules_Session::getInstance()->getVar('userdata')->user_id) . '_' . time();

					$avatarImage->setImageFormat('png');

					$avatarImage->thumbnailImage(200, 200, true);
					$avatarImage->writeImage($avatarDir . $avatarFileName . '_200.png');

					$avatarImage->thumbnailImage(80, 80, true);
					$avatarImage->writeImage($avatarDir . $avatarFileName . '_80.png');

					$avatarImage->thumbnailImage(45, 45, true);
					$avatarImage->writeImage($avatarDir . $avatarFileName . '_45.png');

					return $avatarFileName;

				}

			} catch(Exception $e) {

				$formObject->addError(__('Unable to upload avatar file.'));

			}

		}

		return false;

	}

	public function test() {

		$permissionMapper = new Model_Permission_Mapper(new Model_Permission_Gateway_PDO($this->app->getGlobal('pdodb')));
		$permission = new Model_Permission();
		$permission->{'class'} = 'MyTest';
		$permissionMapper->save($permission);

	}

	/*
	public function usergroups() {

		$ug	= new Model_Usergroup_Gateway_PDO(Application_Registry::get('pdodb'));
		$groups = $ug->getRecursiveUsergroupById(3);
		$output = print_r($groups, true);

		$this->view->addSubview('main', new Application_View_String($output));
		var_dump($this->app->getGlobal('access')->check(__METHOD__, 'own'));

	}
	*/

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