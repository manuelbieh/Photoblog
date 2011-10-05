<?php

class Model_Usergroup_Gateway_PDO {

	public function __construct($dbh) {

		if($dbh) {
			$this->db = $dbh;
		} else {
			throw new Model_Database_Exception('Type of $db is not "Database".');
		}

	}

	public function getUsergroupsByUserId($user_id) {

		$s = $this->db->prepare("SELECT usergroup_id FROM cel_users_x_usergroups WHERE user_id = :user_id");
		$s->execute(array('user_id'=>$user_id));
		$usergroups = $s->fetchAll(PDO::FETCH_ASSOC);

		$groups = array();
		if(is_array($usergroups)) {
			foreach($usergroups AS $usergroup) {
				$groupData = $this->getUsergroupById($usergroup['usergroup_id']);
				$groups[$groupData['usergroup_id']] = $groupData;
			}
		}

		return $groups;

	}

	public function getUsergroupById($usergroup_id) {

		$s = $this->db->prepare("SELECT * FROM cel_usergroups WHERE usergroup_id = :usergroup_id LIMIT 1");
		$s->execute(array("usergroup_id"=>$usergroup_id));
		$usergroup = $s->fetch(PDO::FETCH_ASSOC);

		return $usergroup;

	}


	public function getRecursiveUsergroupById($usergroup_id, $collectedGroups=array()) {

		$s = $this->db->prepare("SELECT *, (SELECT count(usergroup_id) FROM cel_usergroups WHERE parent_usergroup_id = :usergroup_id) AS child_groups FROM cel_usergroups WHERE usergroup_id = :usergroup_id LIMIT 1");
		$s->execute(array("usergroup_id"=>$usergroup_id));
		$usergroup = $s->fetch(PDO::FETCH_ASSOC);
		$collectedGroups[] = $usergroup;

		if((int) $usergroup['child_groups'] > 0) {

			$childGroups = $this->getChildGroups($usergroup_id);

			foreach($childGroups AS $child) {
				$collectedGroups = $this->getRecursiveUsergroupById($child['usergroup_id'], $collectedGroups);
			}

		}

		return $collectedGroups;

	}

	public function getChildGroups($parent_usergroup_id) {

		$s = $this->db->prepare("SELECT * FROM cel_usergroups WHERE parent_usergroup_id = :usergroup_id");
	#	echo "SELECT * FROM cel_usergroups WHERE parent_usergroup_id = $parent_usergroup_id LIMIT 1";
		$s->execute(array("usergroup_id"=>$parent_usergroup_id));
		$usergroup = $s->fetchAll(PDO::FETCH_ASSOC);

		return $usergroup;

	}

	public function fetchAll($columns=NULL, $where=NULL) {

		$s = $this->db->prepare("SELECT usergroup_id FROM cel_usergroups ORDER BY usergroup_id ASC");
		$s->execute();
		return $s->fetchAll(PDO::FETCH_ASSOC);

	}

}
