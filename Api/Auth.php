<?php

class Api_Auth extends Api_Base {

	public function __construct() {}

	public function Verify($username, $password) {

		$url	= $this->getApi() . '/Auth/Verify/' . $username . '/' . $password;

		$curl	= new Modules_Curl();
		$curl->connect($url);

		#$params = array('Username'=>$username, 'Password'=>$password);
		#$curl->setOption(CURLOPT_POST, 1)->setOption(CURLOPT_POSTFIELDS, http_build_query($params));

		$result = json_decode($curl->exec(), true);

		if($result == false || $result['access'] == false) {
			return false;
		} else {
			return true;
		}
		

	}

}