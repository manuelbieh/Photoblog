<?php

class Admin_Controller_User extends Controller_Frontend implements Application_Observable {

	protected $observers = array();

	public function __construct($app=NULL) {

		#Application_Extensions::registerObservers($this);
		$app->extensions()->registerObservers($this);

		$this->app = $app;
		$this->userDB	= new Model_User_Gateway_PDO($app->getGlobal('pdodb'));
		$this->view		= new Application_View();

		if(!isset($_POST['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}
		$this->notify('templateLoaded');

		$navi = new Application_View();
		$navi->loadHTML("templates/main/navi.html");
		$this->view->addSubview('navi', $navi);

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			header("Location: " . Application_Base::getBaseURL() . 'Login');
			exit;
		}

	}

	public function view($offset=0) {

		$userMapper		= new Model_User_Mapper($this->userDB);
		$allUsers		= $userMapper->fetchAll();

		$itemsPerPage	= 3;
		$totalItems		= count($allUsers);
		$offset			= (int) $offset;

		$subview = new Application_View();
		$subview->data['access'] = $this->app->getGlobal('access');
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

/*
		$subview = new Application_View();
		$subview->data = $userMapper->fetchAll();
		$subview->loadHTML('templates/user/view.html');
*/

	}

	public function profile($username=NULL) {

		$subview = new Application_View();

		if($username == NULL) {

			$subview->loadHTML('templates/user/profile.nouser.error.html');
			$this->view->addSubview('main', $subview);

		} else {

			$subview->data = $this->userDB->getUserDataByField('username', $username);

			if($subview->data == false) {

				$subview->loadHTML('templates/user/profile.nouser.error.html');

			} else {

				$subview->loadHTML('templates/user/profile.html');
				$this->view->addSubview('main', $subview);

			}

		}

	//	$this->view->render(true);

/*
		$user = new Model_User();
		$userMapper = new Model_User_Mapper($this->userDB);
		$userMapper->find(1, $user);
		print_r($user);

		$usergroups = $this->userDB->getUsersUsergroups(1);
		foreach($usergroups AS $usergroup) {
			$usergroups[$usergroup] = $this->group->getUsergroupsGathered($usergroup);
		}
		print_r($usergroups);
*/

#		$login = new Modules_Login($this->userDB);
#		$login->loginUser('Manuel', '123');

		#print_r($this->group->getUsergroupsGathered(0));
		
		#$this->userDB->getUserDataByField('username', 

		#$userMapper->find(1, $user);
		#print_r($user);
		
/*
		$user->user_id = 1;
		$user->firstname = 'Manuel';
		$user->lastname = 'Bieh';

		$userMapper->save($user);

		$result = $userMapper->find(1, $user);
		print_r($result);
*/
	}

	public function edit($user_id=NULL) {

		$login_user_id = Modules_Session::getInstance()->getVar('userdata')->user_id;

		if($user_id === NULL) {
			$user_id = Modules_Session::getInstance()->getVar('userdata')->user_id;
		}

		if($login_user_id) { // user is logged in?
			// no user was specified or $user_id given equals login_user
			if($user_id === NULL || ($user_id !== NULL && $login_user_id === $user_id) ) {
				$allowed = $this->app->getGlobal('access')->check(__METHOD__, 'own');
			// login_user_id is not user_id, check if login_user may edit others
			} else if($user_id !== NULL && ($user_id !== $login_user_id)) {
				$allowed = $this->app->getGlobal('access')->check(__METHOD__, 'other');
			}
		} else {
			$allowed = false;
		}

		if($allowed === true) {

			$user = new Model_User();
			$userMapper = new Model_User_Mapper($this->userDB);
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

			if($form->isSent(true)) {

				foreach($form->valueOf('data') AS $prop => $value) {
					// ISSUE: password is removed since check fails
					if(!in_array($prop, $blacklist)) {
						if($prop != 'password' || ($prop === 'password' && strlen($value) > 0)) {
							$user->$prop = $value;
						}
					}
				}

				$userMapper = new Model_User_Mapper($this->userDB);
				$userMapper->save($user);

				$subview = new Application_View();
				$subview->loadHTML('templates/user/edit.success.html');
				$this->view->addSubview('main', $subview);

			} else {

				$this->view->addSubview('main', $form);

			}

		} else {
			$this->view->addSubview('main', Application_Error::error401());
		}

	}

	public function delete($user_id) {

		$login_user_id = Modules_Session::getInstance()->getVar('userdata')->user_id;

		if($user_id === NULL) {
			$user_id = Modules_Session::getInstance()->getVar('userdata')->user_id;
		}

		if($login_user_id) { // user is logged in?
			// no user was specified or $user_id given equals login_user
			if($user_id === NULL || ($user_id !== NULL && $login_user_id === $user_id) ) {
				$allowed = $this->app->getGlobal('access')->check(__METHOD__, 'own');
			// login_user_id is not user_id, check if login_user may edit others
			} else if($user_id !== NULL && ($user_id !== $login_user_id)) {
				$allowed = $this->app->getGlobal('access')->check(__METHOD__, 'other');
			}
		} else {
			$allowed = false;
		}

		if($allowed === true) {
			// delete code here
		} else {
			$this->view->addSubview('main', Application_Error::error401());
		}

	}

	public function add() {

		$login_user_id = Modules_Session::getInstance()->getVar('userdata')->user_id;

		if($login_user_id) { // user is logged in?
			$allowed = $this->app->getGlobal('access')->check(__METHOD__);
		} else {
			$allowed = false;
		}

		if($allowed === true) {
			
			$this->form = new Modules_Form('templates/user/add.form.html');

			$validation = new Admin_Controller_User_Validation();
			$validation->checkUsername($this->form->valueOf('data[username]'));

			$this->form->addValidation($validation);

			if($this->form->isSent(true)) {

				$user = new Model_User();
				foreach($this->form->valueOf('data') AS $property => $value) {
					$user->$property = $value;
				}

				$userMapper = new Model_User_Mapper($this->userDB);
				$newUser = $userMapper->save($user);

				$subview = new Application_View();
				if($newUser != false) {
					$subview->loadHTML('templates/user/add.success.html');
					$this->notify('addSuccess');
					$this->view->addSubview('main', $subview);
				} else {
					$this->form->addError(__('An unknown error occured. Please try again.'));
					$this->view->addSubview('main', $this->form);
				}

				//$this->notify('user saved successfully');

			} else {

				$this->view->addSubview('main', $this->form);

			}

		} else {
			$this->view->addSubview('main', Application_Error::error401());
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
				$allowed = $this->app->getGlobal('access')->check(__METHOD__, 'own');
			// login_user_id is not user_id, check if login_user may edit others
			} else if($user_id !== NULL && ($user_id !== $login_user_id)) {
				$allowed = $this->app->getGlobal('access')->check(__METHOD__, 'other');
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
				$form->data['permissions']['selected'] = $permissionMapper->findPermissionsByUserId($user_id);
			}

			$form->loadTemplate('templates/user/permissions.form.html');

			if($form->isSent(true)) {

				$subview = new Application_View();

				$userMapper = new Model_User_Mapper(new Model_User_Gateway_PDO($this->app->getGlobal('pdodb')));

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
			$this->view->addSubview('main', Application_Error::error401());
		}
	
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

	public function notify($state) {

		foreach((array) $this->observers AS $obs) {

			if(method_exists($obs, $state)) {

				$obs->$state(&$this);

			}

		}

	}


}