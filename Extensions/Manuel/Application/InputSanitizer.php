<?php

class Extensions_Manuel_Application_InputSanitizer {

	public function removeTags($string, $allowed=NULL) {

		return strip_tags($string, $allowed);

	}

	public function userInput($string) {

		return htmlentities(strip_tags($string), ENT_NOQUOTES, 'UTF-8');

	}

}