<?php

class Extensions_Manuel_Admin_Controller_DashboardTest extends Application_Extension {

	protected $configFile = "SystemTweaks.xml";

	public function beforeRenderOutput($obj) {

		$obj->additionalContent = '<h3>Testextension is installed</h3>';
	
	}

}