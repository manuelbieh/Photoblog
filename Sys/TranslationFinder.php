<?php
header("Content-Type: text/html; charset=utf-8");
set_time_limit(0);
error_reporting(E_NONE);

class TranslationHelper {

	public function checkFiles($dir, $blacklist=array()) {

		if(empty($blacklist)) {
			$blacklist = array('gif', 'png', 'jpg', 'eot', 'ttf', 'svg', 'woff');
		}

		foreach(glob($dir . '/*') AS $file) {

			$filename = explode('.', basename($file));

			if(is_dir($file)) {
				$this->parsed = $this->checkFiles($file, $blacklist);
			} else if (is_file($file)) {
				if(!in_array(end($filename), $blacklist)) {
					$this->parsed[] = $file;
				}
			}

		}

		return $this->parsed;

	}

	public function lookForTranslations() {

		if(is_array($this->parsed)) {

			foreach($this->parsed AS $file) {

				$fileContent = file_get_contents($file);
				preg_match_all("#\_\_\((\"|')(.*)(\"|')\)#Usm", $fileContent, $hits);

				if(is_array($hits)) {

					foreach($hits[2] AS $hit) {
						$trans[$file][] = $hit;
						#file_put_contents('translations.txt', var_export($hit), FILE_APPEND);
						
					}

				}

			}

		}

		//var_dump($trans);
		return $trans;
		#file_put_contents('translations.txt', var_export($trans));

	}

}

$t = new TranslationHelper();
$t->checkFiles('..');
$translations = $t->lookForTranslations();
foreach($translations AS $file => $strings) {
	foreach($strings AS $string) {
		$stringArray[] = $string;
	}
}

$stringArray = array_unique($stringArray);
foreach($stringArray AS $string) {
	$phpArray .= "\$_lang['" . str_replace('\"', '"', addslashes($string)) ."'] = '';\n";
	$csvFile .= '"'. str_replace('\"', '"', addslashes($string)) .'";'."\n";
}

file_put_contents('translationStrings.php', "<?php \n" . $phpArray);
file_put_contents('translation.csv', $csvFile);

#print_r($translations);