<?php

class Admin_Controller_User extends Controller_Frontend implements Application_Observable {

	protected $observers = array();

	public function __construct() {

		Application_Extensions::registerObservers($this);

		$this->userDB	= new Model_User_Gateway_PDO(Application_Registry::get('pdodb'));
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

		$itemsPerPage	= 10;
		$totalItems		= count($allUsers);
		$offset			= (int) $offset;

		$subview = new Application_View();
		$subview->loadHTML('templates/user/view.html');

		$subview->data['offset'] = (int) $offset;
		for($i = $offset; $i < $offset+$itemsPerPage; $i++) {
			if(isset($allUsers[$i])) {
				$subview->data['users'][$i] = $allUsers[$i];
			}
		}

		$pagina = new Modules_Pagination;
		$pagina->setLink(Application_Base::getBaseURL() . "Photo/view/")->setItemsPerPage($itemsPerPage)->setItemsTotal($totalItems)->currentPageNum($offset);
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

	public function edit() {


		if(Modules_Session::getInstance()->getVar('userdata')->user_id) {

			$user = Modules_Session::getInstance()->getVar('userdata');

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

		}

	}

	public function delete($user_id) {}

	public function add() {

		$this->form = new Modules_Form('templates/user/add.form.html');

		$validation = new Admin_Controller_User_Validation();
		$validation->checkUsername($this->form->valueOf('data[username]'));
	//	$validation->checkEmail($form->valueOf('data[email]'));

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

	}

	public function usergroups() {

		$ug	= new Model_Usergroup_Gateway_PDO(Application_Registry::get('pdodb'));
		$groups = $ug->getRecursiveUsergroupById(3);
		$this->view->addSubview('main', new Application_View_String(print_r($groups, true)));

		$perm	= new Model_Permission_Gateway_PDO(Application_Registry::get('pdodb'));
		$perm	= new Model_Permission_Mapper($perm);
		$pid = $perm->findPermissionId('photo', 'edit', 'other');
		var_dump($pid);

	}

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