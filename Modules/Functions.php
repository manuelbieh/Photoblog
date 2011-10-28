<?php

class Modules_Functions {

	public static function camelCaseClass($string) {

		$string = ucwords(strtolower($string));

		foreach (array('_') as $delimiter) {

			if (strpos($string, $delimiter) !== false) {

				$string =implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));

			}

		}

		return $string;

	}




	public static function patternReplace($pattern, $data, $delim=array('%','%'), $removeUnused=false) {

		#$delim = array('%','%');

		if(is_array($data)) {
			foreach($data AS $key => $array) {
				if(is_array($array)) {	// Multidimensional array
					foreach($array AS $marker => $content) {
						$search[$key][] = $delim[0] . $marker . $delim[1];
						$replace[$key][] = $content;
					}
					$output .= str_replace($search[$key], $replace[$key], $pattern);
				} else {  // Onedimensional array
					$search[] = $delim[0] . $key . $delim[1];
					$replace[] = $array;
					$output = str_replace($search, $replace, $pattern);
				}
			}
		} else {
			$output = $pattern;
		}

		if($removeUnused == true) {
			$output = preg_replace('|' . $delim[0] . '(.*)' . $delim[1] . '|Usm', '', $output);
		}
		return $output;

	}


	public static function MergeArrays($array1, $array2) {

		foreach($array2 as $key => $value) {

			if(array_key_exists($key, $array1) && is_array($value)) {
				$array1[$key] = self::MergeArrays($array1[$key], $array2[$key]);
			} else {
				$array1[$key] = $value;
			}

		}

		return $array1;

	}


	public function getSuffixByMime($mime) {

		switch(strtolower($mime)) {

			case 'image/jpg':
			case 'image/jpeg':
			case 'image/pjpg':
				return 'jpg';
			case 'image/gif':
				return 'gif';
			case 'image/png':
				return 'png';
			case 'text/plain':
				return 'txt';
			case 'text/html':
				return 'html';

		}

	}


	function datatable($data, $class='') {

		if(is_array($data)) {

			$out = '<table class="'.$class.'">';

			foreach($data AS $exifKey => $exifData) {

				$out .= '<tr><td>';
				$out .= $exifKey;
				$out .= '</td><td>';

				if(is_array($exifData)) {
					$out .= self::datatable($exifData, $class);
				} else {
					$out .= $exifData;
				}

				$out .= '</td></tr>';

			}

			$out .= '</table>';

		}

		return $out;

	}

	/*
	 * Returns a "unique" name, compared to a reference array
	 * e.g: $name = 'foobar', data = array('foobar', 'foobar_1', 'foobar_2')
	 * The return value will then be foobar_3
	 *
	 * @param name String to be checked
	 * @param array Collection of values to be checked against
	 * @param string optional devider
	 * @return the new suffixed string
	 */
	function getUniqueName($name, $data=array(), $delimiter='_') {

		if(in_array($name, $data)) {

			for($i = 1; $i < 8192; $i++) {
				if(!in_array($name . $delimiter . $i, $data)) {
					return $name . $delimiter . $i;
				}
			}

		} else {

			return $name;

		}

	}


	function cleanURL($string) {

		$s = array('á','à','â','ä','Á','À','Â','Ä',
				   'é','è','ê','É','È','Ê',
				   'í','ì','î','Í','Ì','Î',
				   'ó','ò','ô','ö','Ó','Ò','Ô','Ö',
				   'ú','ù','û','ü','Ú','Ù','Û','Ü',
				   ' ','–','—','…','’','%','&',' ','ß'
				   );
		$r = array('a','a','a','ae','A','A','A','Ae',
				   'e','e','e','E','E','E',
				   'i','i','i','I','I','I',
				   'o','o','o','oe','O','O','O','Oe',
				   'u','u','u','ue','U','U','U','Ue',
				   '-','-','-','...','','','+','-','ss'
				   );
		$string = strtolower(str_replace($s, $r, $string));

		$string = preg_replace('/\s{1,}/sm','-',$string);
		$string = preg_replace('/[^a-zA-Z0-9_.+ \]\[-]+/', '', $string);

		return $string;

	}


}
