<?php
header("Content-Type: text/html; charset=utf-8");
set_time_limit(0);
error_reporting(0);

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
						$stringCollector[] = $hit;
						if(!in_array($stringCollector, $hit)) {
							$trans[$file][] = $hit;
						}
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

$moreStrings = file_get_contents(dirname(__FILE__) . '/../i18n/Strings.txt');
$moreStrings = explode("/*END*/\n", $moreStrings);

$stringArray = array_merge($moreStrings, $stringArray);

$stringArray = array_unique($stringArray);
asort($stringArray);
foreach($stringArray AS $string) {
	$phpArray .= "\$_lang['" . str_replace('\"', '"', addslashes($string)) ."'] = '';\n";
	$csvPattern = strpos($string, ',') !== false ? '"%s"' : '%s';
	#$csvFile .= str_replace(array('\"', ';'), array('"', '\;'), addslashes($string)) . ";\n";
	$csvFile .= sprintf($csvPattern . "\n", str_replace('\"', '"', addslashes($string)));
}

file_put_contents('translationStrings.php', "<?php \n" . $phpArray);
file_put_contents('translation.csv', $csvFile);

#print_r($translations);