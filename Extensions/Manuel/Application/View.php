<?php

class Extensions_Manuel_Application_View {

	protected $configFile = "ViewFunctions.xml";

	public function init(Application_View $applicationView) {

		$applicationView->stringFunc = new Extensions_Manuel_Application_StringFunc();
		$applicationView->sanitize = new Extensions_Manuel_Application_InputSanitizer();
		$applicationView->helper = new Extensions_Manuel_Application_View_Helper();

	}

}