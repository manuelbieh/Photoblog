<?php

class Extensions_Manuel_Application_View {

	public function __construct() {}

	public function init(Application_View $applicationView) {

		$applicationView->stringFunc = new Extensions_Wefoundit_Application_StringFunc();
		$applicationView->sanitize = new Extensions_Wefoundit_Application_InputSanitizer();

	}

}