<?php

class Extensions_Manuel_Mobile_Styles extends Application_Extension {

	protected $configFile = "MobileStyles.xml";

	public function AdminTemplateHeadEnd() {

		echo '<link href="' . $this->app->getBaseURL() . '../Extensions/Manuel/Mobile/assets/css/mobile.css" rel="stylesheet" type="text/css" media="all and (max-width: 480px)" />';
		echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">';

	}

}