<?php

class Model_Permission_Gateway_PDO {

	public function __construct($dbh) {

		if($dbh) {
			$this->db = $dbh;
		} else {
			throw new Model_Database_Exception('Type of $db is not "Database".');
		}

	}


	public function getUsersByPermissionId($permission_id) {

		$s = $this->db->prepare("SELECT user_id FROM cel_permissions_x_users WHERE permission_id = :permission_id");
		$s->execute(array('permission_id'=>$permission_id));

		$users = array();
		foreach($s->fetchAll(PDO::FETCH_ASSOC) AS $user) {
			$users[] = $user->user_id;
		}

		return $users;

	}


	public function getPermissionId($class, $method, $param) {

		$s = $this->db->prepare("SELECT permission_id FROM cel_permissions_x_users WHERE class = :class AND method = :method AND param = :param LIMIT 1");
		$s->execute(array('class'=>$class, 'method'=>$method, 'param'=>$param));

		$permission_id = $s->fetch(PDO::FETCH_ASSOC);
		var_dump($permission_id);
		return $permission_id->permission_id;
	
	}


}
