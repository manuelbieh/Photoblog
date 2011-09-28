<?php
if(!function_exists('getURL')) {
	function getURL() {
		return Application_Base::getBaseURL();
	}
}

if(!function_exists('__')) {
	function __($string) {

		$translationsFile = Application_Base::getCoreDir() . 'i18n/Strings.txt';
		if(file_exists($translationsFile)) {

			ob_start();
			include $translationsFile;
			$c = ob_get_contents();
			ob_end_clean();

			eval('$t = ' . $c . ';');
			array_push($t, $string);
			$t = array_unique($t);
			$v = var_export($t, true);
			file_put_contents($translationsFile, $v);

		}

		return $string;
	}
}