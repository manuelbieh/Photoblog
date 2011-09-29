<?php
if(!function_exists('getURL')) {
	function getURL() {
		return Application_Base::getBaseURL();
	}
}

if(!function_exists('__')) {
	function __($string, $locale=NULL) {

		$translationsFile = Application_Base::getCoreDir() . 'i18n/Strings.txt';
		$translationsFile = Application_Base::getCoreDir() . 'i18n/Strings2.txt';

		if(file_exists($translationsFile)) {

			ob_start();
			include $translationsFile;
			$c = ob_get_contents();
			ob_end_clean();

			$strings = explode('/* STRING END */' . "\n", $c);

			$newTranslationString = '$_lang[\'' . str_replace("'", "\'", $string) . '\'] = \'\';';
			if(!in_array($newTranslationString, $strings)) {
				$strings[] = $newTranslationString;
			}
			$newContent = join("/* STRING END */\n", $strings);
			file_put_contents($translationsFile, $newContent);
/*
			eval('$t = ' . $c . ';');
			array_push($t, $string);
			$t = array_unique($t);
			$v = var_export($t, true);
			file_put_contents($translationsFile, $v);
*/

		}

		$translate = Application_Registry::get('translate');
		if($translate != NULL) {
			return $translate->__($string, $locale);
		} else {
			return $string;
		}

	}
}