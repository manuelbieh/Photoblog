<?php

class Extensions_Wefoundit_Application_InputSanitizer {

	public function removeTags($string) {

		return strip_tags($string);

	}

	public function userInput($string) {

		return htmlentities(strip_tags($string), ENT_NOQUOTES, 'UTF-8');

	}

}