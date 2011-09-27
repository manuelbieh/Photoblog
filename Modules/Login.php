<?php

class Modules_Login {

	public $cookieExpires;
	public $enableCookie 		= true;
	public $loginCookieName 	= 'loginSession';
	public $model				= NULL;
	public $safetyCookieName 	= 'loginSessionCheck';
	public $salt 				= 'xVf'; // use a random string here
	protected $gateway			= NULL;
	private $loginhash;

	public function __construct($gateway, Model_User $userModel=NULL) {

		if($userModel != NULL && $userModel instanceOf Model_User) {
			$this->setModel($userModel);
		} else {
			$this->setModel(new Model_User);
		}

		$this->setGateway($gateway);

		$this->cookieExpires = time()+60*60*24*183;
		return $this;

	}

	public function setGateway($gateway) {
		$this->gateway = $gateway;
	}

	public function setModel(Model_User $userModel) {
		$this->model = $userModel;
	}

	public function loginUser($username=NULL, $password=NULL, $options=array()) {

		if(isset($options['enableCookie']) && $options['enableCookie'] == true) {
			$this->enableCookie();
		} else {
			$this->disableCookie();
		}

		$sessionKey = isset($options['sessionKey']) ? $options['sessionKey'] : 'userdata';

		// Benutzer hat Login-Formular ausgefüllt
		if(($username !== NULL && $password !== NULL) && 
			($loginUser = $this->gateway->getUserDataByLogin($username, $password) ) != false) {
			$this->loginUserByCredentials($loginUser);
#echo '1';

		// Benutzer ist bereits eingeloggt (Session ist aktiv)
		} else if($this->isLoggedIn($sessionKey)) {

#echo '2';
			return true;

		// Benutzer ist nicht eingeloggt aber Cookie ist vorhanden
		} else if($username == NULL && $password == NULL &&
					/* $this->enableCookie == true && */
					$this->cookieIsSet() === true && $_COOKIE[$this->safetyCookieName] === $this->createSafetyHash()) {

#echo '3';
			$cred = $this->loginUserByCookie();
			$loginUser = $this->gateway->getUserDataByCookieData($cred);

		} else {

#echo '4';
			$this->logout();

		}

		if(isset($loginUser) && (is_array($loginUser) || is_object($loginUser) )) {
			foreach($loginUser AS $property => $value) {
				$this->model->$property = $value;
			}
		}
		/*
		if($this->model instanceof Model_User) {
			$this->model->user_id = $loginUser['user_id'];
			$this->model->username = $loginUser['username'];
			$this->model->email = $loginUser['email'];
		}
		*/

		if(is_array($loginUser)) {
			Modules_Session::getInstance()->setVar($sessionKey, $this->model);
			return true;
		} else {
			$this->logout($sessionKey);
			return false;
		}

	}

	public function requestPassword($userData) {

		$userData['active'] = 1;
		$userData = $this->gateway->getEmailByUserData($userData);

		if($userData != false) {

			$hash = md5($userData['username'] . mt_rand(0, 9999999) . microtime(true) . $_SERVER['HTTP_USER_AGENT']);
			$this->gateway->setProperty($userData['user_id'], 'passconf', $hash);

			$mailtpl = new Application_View();
			$mailtpl->loadHTML('templates/login/forgot.mail.txt');
			$mailtpl->assign('username', $userData['username']);
			$mailtpl->assign('resetlink', Application_Base::getBaseURL() . 'login/confirm/' . $userData['username'] . '/' . $hash);

			$mail = new Modules_Mail_Mail();
			#$mail->setFrom($_SERVER['SERVER_NAME'] . ': Passwort request');
			$from = Application_Settings::get("//email/signup");
			$mail->setFrom($from['address'], $from['name']);
			$mail->setMessage($mailtpl->render());
			$mail->setRecipient($userData['email']);
			$mail->setSubject('You requested to reset your password');
			$mail->send();
			// new Email(); -> loadTemplate() -> send()
			// return $userData;
			return true;

		} else {

			return false;

		}

	}


	public function resetPassword($identifier, $form) {

		$user_id = $this->gateway->getPropertyByField('user_id', 'passconf', $identifier);

		if((int) $user_id !== 0) {
			return true;
		} else {
			return false;
		}
		// Variante 1: Formular, neues Passwort setzen, sofort aktiv
		// Variante 2: "Danke"-Seite, neues Passwort per E-Mail

	}

	public function confirmAccount($user, $hash) {

		$user = $this->gateway->getUsersByMultipleFields(array('username'=>$user, 'passconf'=>$hash));
		if(count($user) == 1) {

			$user_id = $user[0];
			/*
			$this->model->user_id = $user_id;
			$this->model->passconf = '';
			$this->model->active = '';
			$this->model->save();
			*/
			$this->gateway->setProperties($user_id, array('passconf'=>'', 'active'=>'1'));
			return true;

		} else {

			return false;

		}

	}

	public function setNewPassword($input) {

		

	}

	public function sendNewPassword($user_id) {

		$userData = $this->gateway->getEmailByUserData(array('user_id'=>$user_id));
		// $newPassword = generatePassword();
		// $this->gateway->setProperty('password', $newPassword);
		// new Email(); -> loadTemplate() -> send()

	}



	public function loginUserByCookie() {
	#	echo $_COOKIE[$this->safetyCookieName];
	#	echo '|';
	#	echo $this->createSafetyHash();
		if($_COOKIE[$this->safetyCookieName] === $this->createSafetyHash()) {
			$this->loginhash = $_COOKIE[$this->loginCookieName];
			return $this->loginhash;
		} else {
			return false;
		}

	}


	public function loginUserByCredentials($userObject) {

			$cred['email'] = $userObject['email'];
			$cred['username'] = $userObject['username'];
			$cred['password'] = $userObject['password'];

			$cred['useragent'] = htmlentities($_SERVER['HTTP_USER_AGENT'], ENT_NOQUOTES);
			
			$cred['loginhash'] = $userObject['loginhash'] ? $userObject['loginhash'] : md5(md5($this->salt) . md5($userObject['password']) . $cred['useragent'] . md5($userObject['username']) . mt_rand(0, time()));

			$cred['safetyhash'] = $this->createSafetyHash();

			if($this->enableCookie === true) {

				$this->setCookie($this->loginCookieName, $cred['username'] . '|' . $cred['loginhash']);
				$this->setCookie($this->safetyCookieName, $cred['safetyhash']);

				$this->gateway->setLoginhash($userObject['user_id'], $cred['loginhash']);
				$loginUser->loginhash = $cred['loginhash'];

			}

	}


	public function isLoggedIn($sessionKey='userdata') {
		if(Modules_Session::getInstance()->getVar($sessionKey)->username == true) {
			return true;
		} else {
			return false;
		}
	}

	public function createSafetyHash() {
		return md5(md5($this->salt) . $_SERVER['HTTP_USER_AGENT'] . md5($this->salt . $this->salt));
	}

	public function enableCookie() {
		$this->enableCookie = true;
	}

	public function disableCookie() {
		$this->enableCookie = false;
	}

	public function setCookie($cookieName, $data) {

		setcookie($cookieName, $data, $this->cookieExpires, '/');

	}

	public function getCookie($cookieName) {
		return $_COOKIE[$cookieName];
	}


	public function deleteCookie() {
		$_COOKIE[$this->loginCookieName] = false;
		setcookie($this->loginCookieName, '', 0);
	}


	public function cookieIsSet() {

		return (isset($_COOKIE[$this->loginCookieName])) ? true : false;

	}

	public function logout($sessionKey='userdata') {

		$s = Modules_Session::getInstance();
		$this->gateway->setLoginhash($s->getVar($sessionKey)->user_id, '');
		$s->removeVar($sessionKey);
		$this->deleteCookie();
		$request = new Modules_Request_HTTP();
	#	$redirectTo = $request->removeQueryParams($request->getRequestURL(), array('logout'));
	#	header("Location: " . $redirectTo);
	#	exit;

	}

}