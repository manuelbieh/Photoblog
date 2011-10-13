<?php
interface Modules_Encryption_Interface {

	public function encrypt($string);
	public function encryptWithSalt($string, $salt);

}