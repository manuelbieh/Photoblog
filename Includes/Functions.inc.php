<?php
if(!function_exists('getURL')) {
	function getURL() {
		return Application_Base::getBaseURL();
	}
}

if(!function_exists('__')) {

	function __($string, $locale=NULL) {

		global $app; // URRRRRGHS!!!

		$translationsFile = Application_Base::getCoreDir() . 'i18n/Strings.txt';
		$translationsFile2 = Application_Base::getCoreDir() . 'i18n/Strings2.txt';

		if(file_exists($translationsFile)) {

			$content = trim(file_get_contents($translationsFile));
			$content = explode("/*END*/\n", $content);
			$content[] = trim($string);
			$content = array_unique($content);
			sort($content);
			$content = join("/*END*/\n", $content);
			file_put_contents($translationsFile, $content);


#			ob_start();
#			include $translationsFile;
#			$c = ob_get_contents();
#			ob_end_clean();

			//$strings = explode('/* STRING END */' . "\n", $c);
#			$strings = explode(";\n", $c);

#			$newTranslationString = '$_lang[\'' . str_replace("'", "\'", $string) . '\'] = \'\'';

			#$string = str_replace(array('\"'), array('"'), addslashes($string));
			#$newString = "\$_lang['" . $string ."'] = '';\n";

			#file_put_contents($translationsFile, $newString, FILE_APPEND);
			#$content = file_get_contents($translationsFile);
			#$strings = explode(";\n", $content);
			#$strings = array_unique($strings);
			#sort($strings);
			#$strings = join(";\n", $strings);
			#file_put_contents($translationsFile2, print_r($strings, true));
			#file_put_contents($translationsFile, $strings);
			#$content = trim(file_get_contents($translationsFile));
			##$strings = explode(";\n", $content);
			#$strings = array_unique($strings);
			#asort($strings);
			#array_pop($strings);
			#var_dump($strings);
			#file_put_contents($translationsFile, join(";\n", $strings) . "\n");


		//	if(!in_array($newTranslationString, $strings)) {
		//		$strings[] = $newTranslationString;
		//	}
		//	$newContent = join(";\n", $strings);
		//	file_put_contents($translationsFile, ($newContent) . ';');
/*
			eval('$t = ' . $c . ';');
			array_push($t, $string);
			$t = array_unique($t);
			$v = var_export($t, true);
			file_put_contents($translationsFile, $v);
*/

		}

		//$translate = Application_Registry::get('translate');
		$translate = $app->objectManager->get('Translate');
		if($translate != NULL) {
			return $translate->__($string, $locale);
		} else {
			return $string;
		}

	}

}