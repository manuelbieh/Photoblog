<?php

class Controller_Comments {

	public function __construct() {

	}

	public function commentForm($photo_id) {

		$commentMapper		= new Model_Comment_Mapper(new Model_Comment_Gateway_PDO(Application_Registry::get('pdodb')));

		$this->commentForm = new Modules_Form();

	}

}