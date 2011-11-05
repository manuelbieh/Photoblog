<?php

class Admin_Controller_Extensions extends Controller_Frontend {

	public function __construct($app=NULL) {

		$this->app = $app;

		$this->view		= $this->app->objectManager->get('Application_View');
		$this->view->loadHTML('templates/index.html');

		$this->extGateway	= new Model_Extension_Gateway_PDO($this->app->objectManager->get('Datastore'));
		$this->extMapper	= new Model_Extension_Mapper($this->extGateway);

		$navi = $this->app->createView();
		$navi->loadHTML("templates/main/navi.html");

		$this->view->addSubview('navi', $navi);

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}

	}

	public function view() {
		#$foo = Application_Extensions::buildIndex();
		#var_dump($foo);
		//$this->view->addSubview('main', new Application_View_String('foo'));
		
	}

	public function manage() {

#		$this->app->extensions()->buildIndex();

		$core				= $this->app->getCoreDir();
		$project			= $this->app->getProjectDir();

		$dirs				= explode('/', rtrim($project, '/'));
		$project			= end($dirs) == 'Admin' ? realpath(rtrim($project, '/') . '/..') : $project;

		$coreFiles			= glob(realpath($core) . '/Extensions/*.xml');
		$projectFiles		= glob($project . '/Extensions/*.xml');
		$files				= array_unique(array_merge($coreFiles, $projectFiles));

		$xml = new Modules_XML();

		foreach($files AS $extMeta) {

			$extKey = basename($extMeta, '.xml');
			$ext = $this->extMapper->find($extKey, new Model_Extension);

			$xml->load($extMeta);

			$name = $xml->XPath()->query("//extension/@name");
			$desc = $xml->XPath()->query("//extension/@desc");
			$deps = $xml->XPath()->query("//extension/@deps");
			$core = $xml->XPath()->query("//extension/@core");
			$icon = $xml->XPath()->query("//extension/@icon");

			$extData = array(
				'extKey'=>$extKey, 
				'name'=>$name->item(0)->textContent, 
				'desc'=>$desc->item(0)->textContent, 
				'deps'=>$deps->item(0)->textContent, 
				'core'=>$core->item(0)->textContent, 
				'icon'=>$icon->item(0)->textContent
			);

			if($ext != NULL) {
				$active[] = $extData;
			} else {
				$inactive[] = $extData;
			}

		}

		$subview = $this->app->createView();
		$subview->loadHTML('templates/extensions/manage.html');

		$subview->data['active'] = $active;
		$subview->data['inactive'] = $inactive;

		$this->view->addSubview('main', $subview);


	}

	public function activate($extKey) {
		$ext = $this->extMapper->find($extKey, new Model_Extension);
	}

	public function deactivate($extKey) {
		$ext = $this->extMapper->find($extKey, new Model_Extension);
	}

	public function browse() {
	
	}

	public function settings($extension_key) {

		$settings = new Modules_XML();
		
	}

}