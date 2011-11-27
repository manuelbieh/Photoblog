<?php

/**
 * Collection of array helper functions
 *
 * @author Manuel Bieh
*/

class Modules_Array {


	/**
	 * Flattens a multi dimensional array and transforms it to a given structure
	 * e.g. $arr['a']['b']['c'] = $result['a/b/c'] or $result['a.b.c']
	 *
	 * @param array Multidimensional array as input
	 * @param string Seperator for the array key (Default: /)
	 * @param string Prefix (Default: '')
	 * @param integer Limit of recursion (Default: 10)
	 * @return $this
	*/
	public function flattenArray($input, $sep='/', $prefix = '', $recursionLimit = 10) {

		$result = array();

		foreach ($input AS $name => $value) {

			if (empty($prefix)) {
				$key = $name;
			} else {
				$key = $prefix.$sep.$name;
			}

			if (is_array($value)) {
				$result = array_merge(
					$result, $this->flatArray($value, $sep, $key, $recursionLimit - 1)
				);

			} else {

				$result[$key] = (string)$value;

			}

		}

		return $result;

	}

}