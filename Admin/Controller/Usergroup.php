<?php

class Admin_Controller_Usergroup extends Controller_Frontend {

	protected $observers = array();

	public function __construct() {

		#Application_Extensions::registerObservers($this);

		$this->db		= new Model_Usergroup_Gateway_PDO(Application_Registry::get('pdodb'));
		$this->view		= $this->app->createView();

		if(!isset($_POST['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}
		#$this->notify('templateLoaded');

		$navi = new Application_View();

		$navi->loadHTML("templates/main/navi.html");
		$this->view->addSubview('navi', $navi);

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}

	}

	public function view($offset=0) {

		$usergroupMapper	= new Model_Usergroup_Mapper($this->db);
		$allUsergroups		= $usergroupMapper->fetchAll();

		$itemsPerPage		= 10;
		$totalItems			= count($allUsergroups);
		$offset				= (int) $offset;

		$subview = $this->app->createView();
		$subview->loadHTML('templates/usergroup/view.html');

		$subview->data['offset'] = (int) $offset;
		for($i = $offset; $i < $offset+$itemsPerPage; $i++) {
			if(isset($allUsergroups[$i])) {
				$subview->data['usergroups'][$i] = $allUsergroups[$i];
			}
		}

		$pagina = new Modules_Pagination;
		$pagina->setLink(Application_Base::getBaseURL() . "Usergroup/view/")->setItemsPerPage($itemsPerPage)->setItemsTotal($totalItems)->currentPageNum($offset);
		$subview->data['pagination'] = $pagina->render();

		$this->view->addSubview('main', $subview);

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

				$subview = $this->app->createView();
				$subview->loadHTML('templates/user/edit.success.html');
				$this->view->addSubview('main', $subview);

			} else {

				$this->view->addSubview('main', $form);

			}

		}

	}

	public function delete($usergroup_id) {}

	public function add() {

		$this->form = new Modules_Form('templates/usergroup/add.form.html');

		#$validation = new Admin_Controller_Usergroup_Validation();
		#$validation->checkUsername($this->form->valueOf('data[username]'));
		$this->form->addValidation($validation);

		if($this->form->isSent(true)) {

			$usergroup = new Model_Usergroup();
			foreach($this->form->valueOf('data') AS $property => $value) {
				$usergroup->$property = $value;
			}
			$usergroupMapper = new Model_Usergroup_Mapper($this->db);
			$newUsergroup = $usergroupMapper->save($usergroup);

			$subview = $this->app->createView();
			if($newUsergroup != false) {
				$subview->loadHTML('templates/usergroup/add.success.html');
				$this->view->addSubview('main', $subview);
				$this->notify('addSuccess');
			} else {
				$this->form->addError(__('An unknown error occured. Please try again.'));
				$this->view->addSubview('main', $this->form);
				$this->notify('addError');
			}

		} else {

			$this->view->addSubview('main', $this->form);

		}

	}


}