<?php

class Api {

	public static function get($Api) {

		$class = 'Api_' . ucfirst($Api);
		if(class_exists($class)) {
			return new $class;
		}

	}




}