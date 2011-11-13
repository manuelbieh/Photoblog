<?php

class Model_Update_Mapper extends Model_Mapper_Abstract {

	public function query($query) {

		return $this->_db->query($query);

	}

}