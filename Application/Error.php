<?php


class Application_Error {

	public function __construct() {
		
	}

	public static function error404() {

		$view = new Application_View();
		$view->loadHTML('templates/errordocs/404.html')->render(true);

	}


}