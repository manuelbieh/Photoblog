<?php

class Modules_Encryption_Sha1 implements Modules_Encryption_Interface {

	public function encrypt($string) {
		return sha1($string);
	}

	public function encryptWithSalt($string, $salt) {
		return $this->encrypt($string . $salt);
	}

}