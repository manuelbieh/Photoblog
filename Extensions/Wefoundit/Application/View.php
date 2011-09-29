<?php

class Extensions_Wefoundit_Application_View {

	public function __construct() {}

	public function init(Application_View $applicationView) {

		$applicationView->stringFunc = new Extensions_Wefoundit_Application_StringFunc();
		$applicationView->sanitize = new Extensions_Wefoundit_Application_InputSanitizer();

	}

}