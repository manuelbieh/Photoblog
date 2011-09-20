<?php

class Application_View_Theme extends Application_View {

	public $HTMLFile;

	public function __construct() {

		parent::__construct();
	
	}


	public function loadXSL($filename) {

		$DOM = new DOMDocument;

		if($this->getXSLFile() == NULL) {
			$filename = Application_Base::getPath($this->getThemeDir() . $filename);
			$this->setXSLFile($filename);
			$this->outputType = 'XSL';
		}

		$DOM->load($filename);
		$this->XSL->importStyleSheet($DOM);

		return $this;

	}


	public function loadHTML($filename, $instant=false) {

		if($this->getHTMLFile() == NULL) {
			$filename = Application_Base::getPath($this->getThemeDir() . $filename);
			$this->setHTMLFile($filename);
			$this->outputType = 'HTML';
		}

		if($instant == true) {
			ob_start();
			include $filename;
			$this->HTML = ob_get_contents();
			ob_end_clean();
		}

		return $this;

	}

	public function getThemelist() {

		$theme = array();
		$themes = glob(Application_Base::getCoreDir() . 'templates/themes/*', GLOB_ONLYDIR);
		foreach($themes AS $themeDir) {
			$theme[basename($themeDir)] = basename($themeDir);
		}
		return $theme;

	}


}