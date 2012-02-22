<?php

class Model_Photo extends Application_Model {

	public function setPhotographer(Model_User $photographer) {
		$this->photographer = $photographer;
	}

	public function getPhotographer() {
		return $this->photographer;
	}

	public function setComments($comments) {
		$this->comments = $comments;
	}

	public function getComments($comments) {
		return $this->comments;
	}

}

