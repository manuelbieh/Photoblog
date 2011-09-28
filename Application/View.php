<?php

class Application_View {

	public $data;
	protected $charset='UTF-8';
	protected $mime='text/html';
	protected $HTML;
	protected $HTMLFile;
	protected $marker;
	protected $subview;
	protected $XML;
	protected $XSL;
	protected $outputType;

	public function __construct() {

		$this->XML = new DOMDocument;
		$this->XSL = new XSLTProcessor();
	
	}

	public function getThemeURL() {
		return Application_Base::getBaseURL() . 'templates/themes/' . rtrim(Application_Settings::get("//theme/main"), '/') . '/';
	}

	public function getThemeDir($public=0) {
		return 'templates/themes/' . rtrim(Application_Settings::get("//theme/main"), '/') . '/';
	}

	public function setCharset($charset='UTF-8') {
		$this->charset = $charset;
	}

	public function setMime($mime='text/html') {
		$this->mime = $mime;
	}

	public function getPath($filepath) {

		//$docroot = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		//$coredir = rtrim(realpath((dirname(__FILE__) . '/../Core')), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$docroot = Application_Base::getProjectDir();
		$coredir = Application_Base::getCoreDir();
		$tpls = "templates" . DIRECTORY_SEPARATOR;

		$filepath = str_replace($docroot, '', $filepath);
		$filepath = str_replace($coredir, '', $filepath);
		$filepath = str_replace($tpls, '', $filepath);


		if($docroot == $coredir && Modules_Filesys::isFile($docroot . $tpls . $filepath)) {

			// No project dir, use Celery default
			return $docroot . $tpls . $filepath;

		} else {

			if(Modules_Filesys::isFile($docroot . $tpls . $filepath)) {
				return $docroot . $tpls . $filepath;
			} else if(Modules_Filesys::isFile($coredir . $tpls . $filepath)) {
				return $coredir . $tpls . $filepath;
			} else {
				return $filepath;
				return false;
			}

		}

	}


	public function loadXSL($filename) {

		$DOM = new DOMDocument;

		#if(Modules_Filesys::isFile($filename)) {

			//$filename = $this->getPath($filename);
			$filename = Application_Base::getPath($filename);

			$DOM->load($filename);
			$this->XSL->importStyleSheet($DOM);
			$this->outputType = 'XSL';

		#}

		return $this;

	}

	public function setXML($xml) {

		//$xmlfile = $this->getPath($xml);
		$xmlfile = Application_Base::getPath($xml);
		if($xmlfile !== false) {
		#if(Modules_Filesys::isFile($xml)) {
			$this->XML->load($xmlfile);
		} else if (is_string($xml)) {
			$this->XML->loadXML($xml);
		}

		return $this;

	}

	public function setHTML($string) {

		$this->outputType = HTML;
		$this->HTML = $string;

	}

	public function setHTMLFile($filename) {
		$this->HTMLFile = $filename;
	}

	public function getHTMLFile() {
		return $this->HTMLFile;
	}

	public function setXSLFile($filename) {
		$this->XSLFile = $filename;
	}

	public function getXSLFile() {
		return $this->XSLFile;
	}

	public function loadHTML($filename, $instant=false) {

		#if(Modules_Filesys::isFile($filename)) {

			#$filename = $this->getPath($filename);

			$filename = Application_Base::getPath($filename);

			$this->outputType = 'HTML';
			$this->HTMLFile = $filename;

			if($instant == true) {
				ob_start();
				#$this->HTML = Modules_Filesys::read($filename);
				include $filename;
				$this->HTML = ob_get_contents();
				ob_end_clean();
			}

		#} else {

		#	throw new Application_View_Exception('Template not found: ' . $filename);

		#}

		return $this;

	}

	public function replace($marker, $content, $subview=false) {

		#var_dump($this);
		#echo '<hr />';
		#var_dump($this->HTML);
		if(isset($this->HTML) && $this->HTML != NULL) {
			$subview = $subview === true ? 'sub:':'';
			$this->HTML = str_replace('['.$subview.'[' . $marker . ']]', $content, $this->HTML);
		} else {
			$this->assign($marker, $content);
		}
		#echo '<hr />';
		#var_dump($this->HTML);
		#echo '<hr />';

		return $this;

	}

	public function assign($marker, $content=NULL, $subview=false) {

		if(is_array($marker)) {

			foreach($marker AS $key => $value) {
				$this->assign($key, $value, $subview);
			}

		} else {

			if($subview === false) {
				$this->marker[$marker] = $content;
			} else {
				$this->submark[$marker] = $content;
			}

		}

		return $this;

	}

	public function addSubview($subviewName, $viewObject) {
		$this->subview[$subviewName] = $viewObject;
		return $this;
	}

	public function getSubview($subviewName) {
		if(isset($this->subview[$subviewName])) {
			return $this->subview[$subviewName];
		}
	}

	public function getSubviews() {
		return $this->subview;
	}

	protected function resolveSubviews() {

		$xml 		= new Modules_XML();
		$subviews	= $xml->createElement('subviews');
		$xml->appendChild($subviews);

		$addedSubviews = $this->getSubviews();
		if(is_array($addedSubviews)) {

			foreach($this->subview AS $subviewName => $subviewObject) {

				$subviewContent = $subviewObject->render();

				if(isset($this->HTML) && $this->HTML != NULL) {
					//$this->HTML = str_replace('[sub:[' . $subviewName . ']]', $subviewObject->render(), $this->HTML);
					$this->replace($subviewName, $subviewContent, true);
				} else {
					$this->assign($subviewName, $subviewContent, true);
				}

				$cdata = $xml->createCDATASection($subviewContent);
				$el = $xml->createElement($subviewName);
				$el->appendChild($cdata);
				//$subviewObject->render();
				$subviews->appendChild($el);

			}

		}

		$node = $xml->getElementsByTagName('subviews')->item(0);

		if($this->XML instanceof DOMDocument) {
		#	var_dump($this->XML->saveXML());
			$node = $this->XML->importNode($node, true);
			if($this->XML->documentElement instanceof DOMNode) {
				$this->XML->documentElement->appendChild($node);
			}
		}

	}

	public function render($echo=false) {

		$addedSubviews = $this->getSubviews();
		if(!empty($addedSubviews)) {
			$this->resolveSubviews();
#			$this->XML->formatOutput = true;
		}

		switch (strtolower($this->outputType)) {

			case 'xsl':
				#var_dump($this->XML->saveXML());
				$output = $this->XSL->transformToXML($this->XML);
				break;

			case 'html':

				if($this->HTML == NULL) {

					$this->loadHTML($this->getHTMLFile(), true);

					if(is_array($this->marker)) {
						foreach($this->marker AS $marker => $content) {
							$this->replace($marker, $content);
						}
					}

					if(is_array($this->submark)) {
						foreach($this->submark AS $subview => $content) {
							$this->replace($subview, $content, true);
						}
					}

				}

				$output = $this->HTML;
				break;

		}

		header("Content-Type: " . $this->mime . "; charset=" . $this->charset);

		if($echo == true) {
			echo $output;
		}

		return $output;

	}


}