<?php

class Model_Permission_Gateway_PDO {

	public function __construct($dbh) {

		if($dbh) {
			$this->db = $dbh;
		} else {
			throw new Model_Database_Exception('Type of $db is not "Database".');
		}

	}

	public function fetchAll() {

		$s = $this->db->prepare("SELECT permission_id FROM cel_permissions ORDER BY permission_id ASC");
		$s->execute();
		return $s->fetchAll(PDO::FETCH_ASSOC);

	}

	public function getUsersByPermissionId($permission_id) {

		$s = $this->db->prepare("SELECT user_id FROM cel_permissions_x_users WHERE permission_id = :permission_id");
		$s->execute(array('permission_id'=>$permission_id));

		$users = array();
		foreach($s->fetchAll(PDO::FETCH_ASSOC) AS $user) {
			$users[] = $user['user_id'];
		}

		return $users;

	}

	public function getPermissionDataById($permission_id) {

		$s = $this->db->prepare("SELECT * FROM cel_permissions WHERE permission_id = :permission_id LIMIT 1");
		$s->execute(array('permission_id'=>$permission_id));

		$permissionData = $s->fetch(PDO::FETCH_ASSOC);

		return $permissionData;

	}


	public function getPermissionId($class, $method, $param) {

		$s = $this->db->prepare("SELECT permission_id FROM cel_permissions WHERE class = :class AND method = :method AND param = :param LIMIT 1");
		$s->execute(array('class'=>$class, 'method'=>$method, 'param'=>$param));

		$permission_id = $s->fetch(PDO::FETCH_ASSOC);

		return (int) $permission_id['permission_id'];
	
	}


}
