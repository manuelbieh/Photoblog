<?php

class Modules_JSON {

	/**
	 * Wandelt einen XML-String in das JSON-Format um
	 * @param string XML-String
	 * @return string Transformiertes XML-Dokument
	 */
	public function xml2json($xml) {

		if(isset($xml)) {

			$dom = new DOMDocument();
			$xsl = new XSLTProcessor();

			$dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . "json/xml2json.xsl");
			$xsl->importStyleSheet($dom);

			$dom->loadXML($xml);

			return $xsl->transformToXML($dom);

		}

	}

	/**
	 * Gibt einen JSON enkodierten String formatiert aus
	 * @param string unformatierter String in JSON
	 * @return string Formatierter JSON-String
	 */
	public function prettify($json) {

		$tab = "  ";
		$new_json = "";
		$indent_level = 0;
		$in_string = false;

		$json_obj = json_decode($json);

		if($json_obj === false) {
			return false;
		}

		$json = json_encode($json_obj);
		$len = strlen($json);

		for($c = 0; $c < $len; $c++) {

			$char = $json[$c];

			switch($char) {

				case '{':
				case '[':
					if(!$in_string) {
						$new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
						$indent_level++;
					} else {
						$new_json .= $char;
					}
					break;


				case '}':
				case ']':

					if(!$in_string)	{
						$indent_level--;
						$new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
					} else {
						$new_json .= $char;
					}
					break;


				case ',':
					if(!$in_string) {
						$new_json .= ",\n" . str_repeat($tab, $indent_level);
					} else {
						$new_json .= $char;
					}
					break;


				case ':':
					if(!$in_string) {
						$new_json .= ": ";
					} else {
						$new_json .= $char;
					}
					break;


				case '"':
					if($c > 0 && $json[$c-1] != '\\') {
						$in_string = !$in_string;
					}


				default:
					$new_json .= $char;
					break;                   

			}

		}

		return $new_json;

	}

}

?>