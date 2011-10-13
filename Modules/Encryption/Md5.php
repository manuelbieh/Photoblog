<?php

class Modules_Encryption_Md5 implements Modules_Encryption_Interface {

	public function encrypt($string) {
		return md5($string);
	}

	public function encryptWithSalt($string, $salt) {
		return $this->encrypt($string . $salt);
	}

}