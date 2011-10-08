<?php

class Admin_Application_Access {

	public static function hasAccess($user_id, $class, $method, $param=NULL) {

	}

	public function extract($string=NULL) {

		if($string === NULL) {

			throw new Exception(__('Method was not specified'));

		} else if (strpos($string, '::') !== false) {

			$string	= explode('::', $string);
			//$class		= $string[0];
			//$method		= $string[1];

			return array($string[0], $string[1]);

		} else if(strpos($string, '->') !== false) {

			$string	= explode('->', $string);

			return array($string[0], $string[1]);

		}

	}

	public function access($classMethod, $param) {

		$classMethod = self::extract($classMethod);

		self::hasAccess($classMethod[0], $classMethod[1], $param);

	}


}