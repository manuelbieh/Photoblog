<?php

class i18n_Adapter_Array {

	protected $app;
	protected $locale;

	public function __construct($app=NULL) {
		$this->app = $app;
	}

	public function setLocale($locale) {

		$this->locale = $locale;

		$localeFile = Application_Base::getCoreDir() . "/i18n/lang/Array/" . $locale . ".php";
		if($this->app instanceof Application_Base) {

			$localeFiles[] = Application_Base::getProjectDir() . "/i18n/lang/Array/" . $locale . ".php";
			$localeFiles[] = Application_Base::getCoreDir() . "/i18n/lang/Array/" . $locale . ".php";

			foreach($localFiles AS $locale) {
				if(Module_Filesys::isFile($locale)) {
					include_once $localeFile;
					$this->lang[$locale] = $_lang;
				}
			}
		}

		if(Modules_Filesys::isFile($localeFile)) {
			include_once $localeFile;
			$this->lang[$locale] = $_lang;
		} else {
			include_once Application_Base::getCoreDir() . "/i18n/lang/Array/en_EN.php";
			$this->lang[$locale] = $_lang;
		}

	}

	public function __($string, $locale=NULL) {

		$locale = $locale === NULL ? $this->locale : $locale;

		if(array_key_exists($string, $this->lang[$locale])) {

			return $this->lang[$locale][$string];

		} else {

			return $string;

		}

	}



}