<?php


class Application_Error {

	public function __construct() {
	}

	public static function error401() {

		$view = new Application_View();
		$response = new Modules_Response_HTTP(); 
		$response->setStatus(401);
		$view->loadHTML('templates/errordocs/401.html');
		//$view->render(true);
		return $view;

	}

	public static function error404() {

		$view = new Application_View();
		$response = new Modules_Response_HTTP(); 
		$response->setStatus(404);
		$view->loadHTML('templates/errordocs/404.html');
		$view->render(true);
		return $view;

	}

	public static function error500() {

		$view = new Application_View();
		$response = new Modules_Response_HTTP(); 
		$response->setStatus(500);
		$view->loadHTML('templates/errordocs/500.html');
		$view->render(true);
		return $view;

	}



}