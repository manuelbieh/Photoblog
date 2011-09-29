<?php


class Controller_Comment_Validation extends Modules_Validation_Abstract {

	public $object = __CLASS__;
	public $errors = array();

	public function checkRequiredFields($data) {

		if(strlen($data->valueOf('data[name]')) < 2) {
			$this->addError(__('Please enter your name.'));
			return false;
		}

		if(strlen($data->valueOf('data[text]')) < 2) {
			$this->addError(__('Comment text is missing?'));
			return false;
		}

	}

}