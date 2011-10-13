<?php

class Admin_Application_Access {

	public function setPermissionMapper($permission) {
		$this->permission = $permission;
	}

	public function hasAccess($user_id, $class, $method, $param=NULL) {

		$permission_id = $this->permission->findPermissionId($class, $method, $param);
		$users = $this->permission->findUsersByPermissionId($permission_id);

		if(in_array($user_id, $users)) {
			return true;
		} else {
			return false;
		}

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

	public function check($classMethod, $param='') {

		$classMethod = $this->extract($classMethod);

		return $this->hasAccess(Modules_Session::getInstance()->getVar('userdata')->user_id, $classMethod[0], $classMethod[1], $param);

	}


}