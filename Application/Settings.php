<?php

class Application_Settings {

	public static $instance = NULL;

	protected function __construct() {
		return $this;
	}

	protected function __clone() {}

	public static function getInstance() {
		$className = __CLASS__;
		return self::$instance === NULL ? new $className() : self::$instance;
	}

	public function getFile($type=0) {

		switch($type) {
			case 0:
			default:
				// Main project settings
				return Application_Base::getProjectDir() . 'Includes/Settings.xml';
			case 1:
				// Main project settings for site accessed from /Admin
				return Application_Base::getProjectDir() . '../Includes/Settings.xml';
			case 2:
				return Application_Base::getCoreDir() . 'Includes/Settings.xml';
		}

	}

	/* Gets the value of the defined key. Returns either string or array when the
	 * given key has childnodes.
	 * @param string Searchkey
	 * @param integer Where to look (0 = project dir, 1 = base dir)
	 * @return mixed Either array or string
	 */
	public function get($xPathKey, $type=0) {

		$settings = new Modules_XML();
		$settings->load(self::getFile($type));
		$nodes = $settings->XPath()->query($xPathKey);

		if($nodes->length > 0) {

			foreach($nodes AS $node) {

				$children = $settings->XPath()->query("./*", $node);

				if($children->length > 0) {

					$data = array();
					foreach($children AS $childNode) {
						$data[$childNode->localName] = $childNode->textContent;
					}
					return $data;

				} else {
					return $node->textContent;
				}

			}

		} else {
			return NULL;
		}

	}

	/* Sets the value of the defined key. Key must not have childnodes.
	 * @param string Searchkey
	 * @param string New value
	 * @param string Where to look (0 = project dir, 1 = base dir)
	 * @return mixed Either array or string
	 */
	public function set($xPathKey, $value, $type=0) {

		$settings = new Modules_XML();
		$settings->load(self::getFile($type));
		$nodes = $settings->XPath()->query($xPathKey);

		if($nodes->length == 1) {
			$nodes->item(0)->nodeValue = $value;
			$settings->save($settingsFile);
		}

	}



}

?>