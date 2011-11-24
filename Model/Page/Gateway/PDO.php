<?php

class Model_Page_Gateway_PDO {

	public function __construct($dbh) {

		if($dbh) {
			$this->db = $dbh;
		} else {
			throw new Model_Database_Exception('Type of $db is not "Database".');
		}

	}

	public function getPageById($page_id) {

		$s = $this->db->prepare("SELECT *, (SELECT count(page_id) FROM cel_content_pages WHERE parent_page_id = main.page_id) AS childcount FROM cel_content_pages AS main WHERE page_id = :page_id");
		$s->execute(array('page_id'=>(int) $page_id));
		return $s->fetch(PDO::FETCH_ASSOC);

	}


	public function getPagesByParentId($parent_page_id) {

		$s = $this->db->prepare("SELECT *, (SELECT count(page_id) FROM cel_content_pages WHERE parent_page_id = main.page_id) AS childcount FROM cel_content_pages AS main WHERE parent_page_id = :parent_page_id ORDER BY sort ASC");
		$s->execute(array('parent_page_id'=>(int) $parent_page_id));
		return $s->fetchAll(PDO::FETCH_ASSOC);

	}


	public function setProperties($page_id, $proplist) {

		if(is_array($proplist) && !empty($proplist)) {

			$updates = array();
			if((int) $page_id !== 0) {
				$binds['page_id'] = $page_id;
			}
			foreach($proplist AS $field => $newValue) {
				$updates[]			= preg_replace("([^a-zA-Z0-9_])", '', $field) . " = :" . preg_replace("([^a-zA-Z0-9_])", '', $field);
				$insertColumns[]	= preg_replace("([^a-zA-Z0-9_])", '', $field);
				$insertValues[]		= $this->db->quote($newValue);
				$binds[':'.$field]		= $newValue;
			}
			$updates = join(', ', $updates);

			$s = $this->db->prepare("INSERT INTO cel_content_pages (" . join(',', $insertColumns) . ") VALUES (:" . join(', :', $insertColumns) . ") ON DUPLICATE KEY UPDATE $updates");
			$exec = $s->execute($binds);

			if($exec == true) {

				if((int) $page_id !== 0) {

					return $page_id;

				} else {

					return $this->db->lastInsertId();

				}

			}

		} 

		return false;

	}

	public function fetchAll($columns=NULL, $where=NULL) {

		$s = $this->db->prepare("SELECT *, (SELECT count(page_id) FROM cel_content_pages WHERE parent_page_id = main.page_id) AS childcount FROM `cel_content_pages` AS main ORDER BY sort ASC");
		$s->execute();
		return $s->fetchAll(PDO::FETCH_ASSOC);

	}


	public function fetchWhere($where, $op='AND') {

		$op = $op == 'OR' ? ' OR ' : ' AND ';

		if(is_array($where)) {
			$w = array();
			foreach($where AS $field => $value) {
				$w[] = preg_replace("([^a-zA-Z0-9_])", '', $field) . ' = :' . preg_replace("([^a-zA-Z0-9_])", '', $field);
				$prep[preg_replace("([^a-zA-Z0-9_])", '', $field)] = $value;
			}
			$w = " WHERE " . join($op, $w);
		}

		$s = $this->db->prepare("SELECT *, (SELECT count(page_id) FROM cel_content_pages WHERE parent_page_id = main.page_id) AS childcount FROM cel_content_pages AS main $w ORDER BY sort ASC");
		$s->execute($prep);

		return $s->fetchAll(PDO::FETCH_ASSOC);

	}



	public function delete($page_id) {

		$s = $this->db->prepare("DELETE FROM cel_content_pages WHERE page_id = :page_id");
		return $s->execute(array('page_id'=>$page_id));

	}

}
