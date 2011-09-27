<?php

class i18n_Translate {

	public function __construct($adapter) {

		$this->adapter = $adapter;

	}

	public function setLocale($locale) {

		

	}

	public function _($key, $locale=NULL) {

		if($language != NULL) {
			//include dirname(__FILE__) . 'lang' . substr($locale, 0, 5) . '.php';
			include Application_Base::getProjectDir() . '/i18n/lang' . substr($locale, 0, 5) . '.php';
		}

		if(isset($_lang[$key])) {
			return $_lang[$key];
		}

	}

}