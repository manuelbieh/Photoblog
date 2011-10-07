<?php

class Model_User_Gateway_PDO {

	public function __construct($dbh) {

		if($dbh) {
			$this->db = $dbh;
		} else {
			throw new Model_Database_Exception('Type of $db is not "Database".');
		}

	}

	// ByCredentials (klingt richtiger)
	public function getUserDataByLogin($username, $password) {

		#$cryptPassword = crypt($password);
		$s = $this->db->prepare("SELECT user_id, password FROM cel_users WHERE username = :username AND password = :password LIMIT 1");
		$s->execute(array('username'=>$username, 'password'=>$password));
		$user = $s->fetchAll(PDO::FETCH_ASSOC);

		return $this->getUserDataById($user[0]['user_id']);

	}

	public function getUserDataByCookieData($loginhash) {

		$loginhash = explode('|', $loginhash);

		$s = $this->db->prepare("SELECT user_id FROM cel_users WHERE username = :username AND loginhash = :loginhash LIMIT 1");
		$s->execute(array('username'=>$loginhash[0], 'loginhash'=>$loginhash[1]));
		$user = $s->fetchAll(PDO::FETCH_ASSOC);
		return $this->getUserDataById($user[0]['user_id']);

	}

	public function getUserDataById($user_id) {

		$s = $this->db->prepare("SELECT * FROM cel_users WHERE user_id = :user_id LIMIT 1");
		$s->execute(array('user_id'=>$user_id));

		$userProps = array();
		if($s->rowCount() == 1) {
			$userData = $s->fetch(PDO::FETCH_ASSOC);
			$s = $this->db->prepare("SELECT prop_name, value FROM cel_userprops AS a JOIN cel_userprops_x_users AS b on a.prop_id = b.prop_id AND b.user_id = :user_id");
			$s->execute(array('user_id'=>$user_id));

			foreach($s->fetchAll(PDO::FETCH_ASSOC) AS $data) {
				$userProps[$data['prop_name']] = $data['value'];
			}

			return array_merge($userData, $userProps);

		}

		return false;

	}

	public function getUserDataByField($fieldName, $value) {

		$fieldName = stripslashes($fieldName);
		$fieldName = preg_replace("([^a-zA-Z0-9_])", '', $fieldName);

		$s = $this->db->prepare("SELECT user_id FROM cel_users WHERE $fieldName = :value LIMIT 1");
		$s->execute(array("value"=>$value));
		$user = $s->fetch(PDO::FETCH_ASSOC);
		return $this->getUserDataById($user['user_id']);

/*
		if($this->db->dbrows == 1) {
			$user = $this->db->fetchObject();
			return $this->getUserDataById($user->user_id);
		}
*/
	}


	public function getUsersByMultipleFields($fieldArray) {

		$query = "SELECT a.user_id FROM cel_users AS a ";

		$colCount = count($fieldArray);

		foreach($this->db->query("SHOW COLUMNS FROM cel_users") AS $row) {
			if(array_key_exists($row['Field'], $fieldArray)) {
				$where[] = $row['Field'] . "=" . $this->db->quote($fieldArray[$row['Field']]);
			}
			unset($fieldArray[$row['Field']]);
		}

		$i = 0;
		if(!empty($fieldArray)) {

			foreach($fieldArray AS $col => $value) {
				$join[] = " LEFT JOIN cel_userprops_x_users AS b$i ON a.user_id = b$i.user_id ";
				$join[] = " LEFT JOIN cel_userprops AS c$i ON b$i.prop_id = c$i.prop_id ";
				$where[] = " c$i.prop_id = b$i.prop_id ";
				$where[] = " c$i.prop_name = " . $this->db->quote($col);
				$where[] = " b$i.value = " . $this->db->quote($value);
				$i++;
			}

			$query .= join("\n", $join);

		}

		$query .= ' WHERE ';

		if(is_array($where)) {
			$query .= join("\n AND ", $where);
		}

		foreach($this->db->query($query) AS $row) {
			$users[] = $row['user_id'];
		}
		return $users;

	}


	public function getEmailByUserData($userData) {

		if($userData['email']) {
			$where[] = "email = :email";
			$bind[':email'] =  $userData['email'];
		}
		if($userData['username']) {
			$where[] = "username = :username";
			$bind[':username'] = $userData['username'];
		}
		if($userData['user_id']) {
			$where[] = "user_id = :user_id";
			$bind[':user_id'] = $userData['user_id'];
		}

		if(is_array($where)) {

			$where = join(" AND ", $where);

			$s = $this->db->prepare("SELECT user_id, username, email FROM cel_users WHERE " . $where);
			$s->execute($bind);

			return $s->fetch(PDO::FETCH_ASSOC);

		} else {

			return false;

		}

	}


	public function setLoginhash($user_id, $loginhash) {
		$s = $this->db->prepare("UPDATE cel_users SET loginhash = :loginhash WHERE user_id = :user_id");
		$s->execute(array('loginhash'=>$loginhash, 'user_id'=>$user_id));
	}

	public function setProperty($user_id, $prop, $value) {

		#$this->db->query("SHOW COLUMNS FROM cel_users");
		$usertblProp = NULL;
		foreach($this->db->query("SHOW COLUMNS FROM cel_users") AS $row) {
			if($row['Field'] == $prop) {
				$usertblProp = $prop;
			}
		}

		if($usertblProp !== NULL) {
			$s = $this->db->prepare("UPDATE cel_users SET " . preg_replace("([^a-zA-Z0-9_])", '', $usertblProp) . " = :value WHERE user_id = :user_id");
			$s->execute(array('value'=>$value, 'user_id'=>$user_id));
		} else {
			$s = $this->db->prepare("UPDATE cel_userprops_x_users, cel_userprops, cel_users SET value = :value WHERE cel_userprops_x_users.prop_id = cel_userprops.prop_id AND cel_userprops.prop_name = :prop AND cel_users.user_id = :user_id");
			$s->execute(array('value'=>$value, 'user_id'=>$user_id, 'prop'=>$prop));
		}

	}


	public function setProperties($user_id, $proplist) {

		$user_id = (int) $user_id;
		$usertblProps = NULL;

		foreach($this->db->query("SHOW COLUMNS FROM cel_users") AS $row) {
			if(array_key_exists($row['Field'], $proplist)) {
				$usertblProps[$row['Field']] = $proplist[$row['Field']];
				unset($proplist[$row['Field']]);
			}
		}

		if($usertblProps !== NULL) {

			$updates = array();
			$binds['user_id'] = $user_id;
			foreach($usertblProps AS $field => $newValue) {
				$field = preg_replace("([^a-zA-Z0-9_])", '', $field);
				$updates[] = $field . " = :" . $field;
				$binds[$field] = $newValue;
			}
			$updates = join(', ', $updates);

			$s = $this->db->prepare("UPDATE cel_users SET " . $updates . " WHERE user_id = :user_id");
			$success[] = $s->execute($binds) ? 'true':'false';

		}

		if(!empty($proplist)) {

			foreach($proplist AS $key => $value) {
				$proplist[$key] = $value;
				$identifier[] = $this->db->quote($key);
			}

			$props = join(",", $identifier);
			foreach($this->db->query("SELECT prop_name, prop_id FROM cel_userprops WHERE prop_name IN ($props)") AS $row) {
				$prop[$row['prop_name']] = $row['prop_id'];
			}

			#$this->db->query("DELETE FROM cel_userprops_x_users WHERE prop_id IN (" . join(',', $prop) . ")");

			foreach($proplist AS $prop_name => $newValue) {
				$prop_id = $prop[$prop_name];
				$newValue = $newValue === NULL ? '' : $newValue;
				$s = $this->db->prepare("INSERT INTO cel_userprops_x_users (prop_id, value, user_id) VALUES (:prop_id, :value, :user_id) ON DUPLICATE KEY UPDATE value = :value");
				$success[$prop_name] = $s->execute(array('prop_id'=>$prop_id, 'value'=>$newValue, 'user_id'=>$user_id)) ? 'true':'false';
				#print_r(array('prop_id'=>$prop_id, 'value'=>$newValue, 'user_id'=>$user_id));
				#var_dump($success[$prop_name]);
			}


		}

		return $success;

	}


	public function getProperties($user_id, $proplist) {

		$data = $this->getUserDataById($user_id);
		$props = array();
		$proplist = is_string($proplist) ? explode(',', $proplist) : $proplist;

		if($data != NULL) {

			foreach($data AS $key => $value) {

				if(in_array($key, $proplist)) {
					$props[$key] = $value;
				}

			}

			return $props;

		} else {
			return false;
		}

	}


	public function getPropertyByField($fieldToGet, $fieldToCheck, $value) {

		$user = $this->getUserDataByField($fieldToCheck, $value);
		return $this->getProperties($user['user_id'], $fieldToGet);

	}

	public function getUsersUsergroups($user_id) {

		$s = $this->db->prepare("SELECT usergroup_id FROM cel_users_x_usergroups WHERE user_id = :user_id");
		$s->execute(array('user_id'=>$user_id));
		$groups = array();
		foreach($s->fetchAll(PDO::FETCH_ASSOC) AS $row) {
			$groups[] = $row['usergroup_id'];
		}

		return $groups;

	}

	public function createUser($model, $data) {

		$this->db->beginTransaction();

		$model->passconf	= $model->passconf == NULL ? '' : $model->passconf;
		$model->active		= (int) $model->active;

		$s = $this->db->prepare("INSERT INTO cel_users (user_id, username, email, passconf, date_signup, active) VALUES ('', :username, :email, :passconf, NOW(), :active)");
		$s->execute(array('username'=>$model->username, 'email'=>$model->email, 'passconf'=>$model->passconf, 'active'=>$model->active));

		$user_id = $this->db->lastInsertId();
		if($user_id != false) {

			$props = $this->setProperties($user_id, $data);

			if(in_array('false', $props)) {

				$this->db->rollBack();
				return false;

			} else {

				$this->db->commit();
				return $user_id;

			}

		} else {

			$this->db->rollBack();
			return false;

		}

	}

	public function fetchAll($columns=NULL, $where=NULL) {

		$s = $this->db->prepare("SELECT user_id FROM cel_users ORDER BY user_id ASC");
		$s->execute();
		return $s->fetchAll(PDO::FETCH_ASSOC);

	}

}
