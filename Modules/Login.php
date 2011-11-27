<?php

/**
 * Login module
 * 
 * @author Manuel Bieh
 * @depends Modules_Session
 *
 */

class Modules_Login {

	public $cookieExpires;
	public $loginCookieName 	= 'loginSession';
	public $safetyCookieName 	= 'loginSessionCheck';
	protected $gateway			= NULL;

	public function __construct($gateway) {

		$this->setGateway($gateway);
		$this->cookieExpires = time()+60*60*24*183;

	}

	public function setGateway($gateway) {
		$this->gateway = $gateway;
	}

	public function getGateway() {
		return $this->gateway;
	}

	public function loginUser($username=false, $password=false, $options=array()) {

		$sessionKey = isset($options['sessionKey']) ? $options['sessionKey'] : 'userdata';

		// Login via form
		if($username !== false && $password !== false) {

			$userMapper = new Model_User_Mapper($this->getGateway());
			$user = $userMapper->findByLogin($username, $password);

			if($user !== false) {

				Modules_Session::getInstance()->setVar($sessionKey, $user);

				$ua	= htmlentities($_SERVER['HTTP_USER_AGENT'], ENT_NOQUOTES);

				$loginhash = $user->loginhash ? $user->loginhash : md5(md5(__SALT__) . md5($password) . $ua . md5($user->username) . mt_rand(0, time()));

				if($options['enableCookie'] == true) {

					$this->setCookie($this->loginCookieName, $user->user_id . '|' . $loginhash);
					$this->setCookie($this->safetyCookieName, $this->createSafetyHash());

					$user->loginhash = $loginhash;

				}

				$user->last_login = date('Y-m-d H:i:s');
				$userMapper->save($user);

				return true;

			}

		// Login via valid session
		} else if($this->isLoggedIn($sessionKey)) {

			return true;

		// Login via valid cookie
		} else if($this->cookieIsSet() && $_COOKIE[$this->safetyCookieName] === $this->createSafetyHash()) {

			$cookieData = $this->getCookie($this->loginCookieName);
			$cookieData = explode('|', $cookieData);

			$user_id = $cookieData[0];
			$loginhash = $cookieData[1];

			if($loginhash != '') {

				$userMapper = new Model_User_Mapper($this->getGateway());
				$user = $userMapper->findByFields(array('user_id'=>(int) $user_id, 'loginhash'=>$loginhash));

				if($user[0] != false) {

					$user[0]->last_login = date('Y-m-d H:i:s');
					$userMapper->save($user[0]);

					Modules_Session::getInstance()->setVar($sessionKey, $user[0]);

					return true;

				}

			}

		}

		return false;

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

	public function isLoggedIn($sessionKey='userdata') {
		if((int) Modules_Session::getInstance()->getVar($sessionKey)->user_id !== 0) {
			return true;
		} else {
			return false;
		}
	}

	public function createSafetyHash() {
		return md5(md5(__SALT__) . $_SERVER['HTTP_USER_AGENT'] . md5(__SALT__ . __SALT__));
	}

	public function setCookie($cookieName, $data) {
		setcookie($cookieName, $data, $this->cookieExpires, '/');
	}

	public function getCookie($cookieName) {
		return $_COOKIE[$cookieName];
	}

	public function deleteCookie() {
		$_COOKIE[$this->loginCookieName] = false;
		$this->setCookie($this->loginCookieName, false);
	}

	public function cookieIsSet() {
		return (isset($_COOKIE[$this->loginCookieName])) ? true : false;
	}

	public function logout($sessionKey='userdata') {

		if($this->isLoggedIn($sessionKey)) {

			$user = Modules_Session::getInstance()->getVar($sessionKey);
			$user->loginhash = '';

			$userMapper = new Model_User_Mapper($this->getGateway());
			$userMapper->save($user);

		}

		Modules_Session::getInstance()->removeVar($sessionKey);
		$this->deleteCookie();

		$request = new Modules_Request_HTTP();
	#	$redirectTo = $request->removeQueryParams($request->getRequestURL(), array('logout'));
	#	header("Location: " . $redirectTo);
	#	exit;

	}

}