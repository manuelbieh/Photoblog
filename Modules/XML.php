<?php

/**
 * A small addition to the DOMDocument class. 
 * 
 * @author Manuel Bieh
 *
 */
class Modules_XML extends DOMDocument {

	protected $namespaces = array();
	public $XPath;
	public $XML;

	public function __construct() {
		parent::__construct();
		$this->XML = $this;
	}

	public function registerNamespace($prefix, $ns) {
		if(isset($this->XPath) && $this->XPath instanceof DOMXPath) {
			$this->XPath->registerNamespace($prefix, $ns);
		}
	}

	public function XPath(DOMDocument $dom = NULL) {

		if(isset($dom)) {
			$xpath = new Modules_XPath($dom);
		} else {
			$xpath = new Modules_XPath($this->XML);
		}
		$this->XPath = $xpath->XPath;

		return $this->XPath;

	}

	public function getXML($xml) {

		if(Modules_Filesys::isfile($xml)) {
			parent::load($xml);
		} else {
			parent::loadXML($xml);
		}

	}

	public function load($file) {

		if(Modules_Filesys::isFile($file)) {
			parent::load($file);
		}

		return $this;

	}

	public function loadXML($xmlString) {

		parent::loadXML($xmlString);
		
		return $this;

	}

	public function loadHTML($htmlString) {

		parent::loadHTML($htmlString);
		return $this;

	}

	public function loadHTMLFile($file) {

		if(Modules_Filesys::isFile($filele)) {
			parent::loadHTMLFile($file);
		}

		return $this;

	}

	public function sanitize($string) {

		return trim(trim($string), '"\'<>');

	}

	public static function attributeSanitize($string) {
		
		return htmlspecialchars(urlencode($string), ENT_NOQUOTES, 'UTF-8');
	}

}