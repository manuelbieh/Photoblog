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


	public function getPermissionId($class, $method='', $param='') {

		$where = "class = :class";
		$binds['class'] = $class;
		if($method !== '') {
			$where .= " AND method = :method";
			$binds['method'] = $method;
		}
		if($param !== '') {
			$where .= " AND param = :param";
			$binds['param'] = $param;
		}

		$s = $this->db->prepare("SELECT permission_id FROM cel_permissions WHERE $where LIMIT 1");
		$s->execute($binds);

		$permission_id = $s->fetch(PDO::FETCH_ASSOC);

		return (int) $permission_id['permission_id'];
	
	}


	public function getPermissionsByUserId($user_id) {

		$s = $this->db->prepare("SELECT permission_id FROM cel_permissions_x_users WHERE user_id = :user_id ORDER BY permission_id ASC");
		$s->execute(array('user_id'=>$user_id));
		
		foreach($s->fetchAll(PDO::FETCH_ASSOC) AS $permission) {
			$permissions[] = $permission['permission_id']; 
		}

		return $permissions;

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

		return $s->fetch(PDO::FETCH_ASSOC);

	}


	public function createPermission($model, $data) {

		$s = $this->db->prepare("INSERT INTO cel_permissions (permission_id) VALUES ('')");
		$s->execute();

		$permission_id = $this->db->lastInsertId();
		if($permission_id != false) {
			$this->setProperties($permission_id, $data);
			return $permission_id;
		} else {
			return false;
		}

	}

	public function setProperties($permission_id, $data) {

		if(is_array($data)) {

			$updates = array();
			$binds = array('permission_id'=>$permission_id);
			foreach($data AS $field => $newValue) {
				$field = preg_replace("([^a-zA-Z0-9_])", '', $field);
				$updates[] = $field . " = :" . $field;
				$binds[$field] = $newValue;
			}
			$updates = join(', ', $updates);
			$s = $this->db->prepare("UPDATE cel_permissions SET " . $updates . " WHERE permission_id = :permission_id");

			return $s->execute($binds);

		} else {

			return false;

		}

	}


}
