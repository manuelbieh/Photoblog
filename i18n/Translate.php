<?php

class i18n_Translate {

	public $adapter;

	public function __construct($adapter=NULL) {

		if($adapter === NULL) {
			$adapter = new i18n_Adapter_Array();
		}

		$this->adapter = $adapter;

	}

	public function setAdapter($adapter) {
		$this->adapter = $adapter;
	}

	public function setLocale($locale) {

		$this->adapter->setLocale($locale);

	}

	public function __($string, $locale=NULL) {

		return $this->adapter->__($string, $locale);

	}

	public function getAdapter() {
		return $this->adapter;
	}

	public function getLanguages() {

		foreach(glob(dirname(__FILE__) . '/lang/*/*') AS $langfile) {
			$langfile = explode('.', basename($langfile));
			$langfiles[$langfile[0]] = $langfile[0];
		}

		return $langfiles;

	}

}