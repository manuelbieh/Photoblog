<?php

class i18n_Translate {

	public function __construct($adapter) {

		$this->adapter = $adapter;

	}

	public function setLocale($locale) {

		$this->adapter->setLocale($locale);

	}

	public function __($string, $locale=NULL) {

		return $this->adapter->__($string, $locale);

	}

}