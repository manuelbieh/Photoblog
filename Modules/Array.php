<?php

class Modules_Array {

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