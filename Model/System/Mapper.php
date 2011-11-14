<?php

class Model_System_Mapper extends Model_Mapper_Abstract {

	public function query($query) {

		return $this->_db->query($query);

	}

}