<?php
if(!function_exists('getURL')) {
	function getURL() {
		return Application_Base::getBaseURL();
	}
}

if(!function_exists('__')) {
	function __($string) {
		return $string;
	}
}