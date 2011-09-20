<?php
include "../Modules/Curl.php";
class Api_Base {

	private $_API = 'http://photostock/Api';
	private $_APIKEY = 'manuel';

	public function __construct() {}

	public function getApi() {
		return $this->_API;
	}

	public function setApiKey($key) {
		$this->_APIKEY = $key;
	}

	public function getApiKey() {
		return $this->_APIKEY;
	}

	// http://photostock/Api/Auth/Verify/user/pass
	public function get($module, $params) {

		$params = array_merge($params, array('_APIKEY', $this->getApiKey()));
		$module	= ucfirst(ltrim($module, '/'));
		$url	= $this->getApi() . '/' . $module;
		var_dump($url);
		$curl	= new Modules_Curl($url);
		$curl->setOption(CURLOPT_POST, 1)->setOption(CURLOPT_POSTFIELDS, http_build_query($params));
		return $curl->exec();
		
	}

}
