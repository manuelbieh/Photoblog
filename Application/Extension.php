<?php

class Application_Extension {

	protected $app;

	public function __construct($app=NULL) {
		$this->app = $app;
	}


	/* Gets the value of the defined key. Returns either string or array when the
	 * given key has childnodes.
	 * @param string Searchkey
	 * @param integer Where to look (0 = project dir, 1 = base dir)
	 * @return mixed Either array or string
	 */
	public function get($xPathKey, $file) {

		$settings = new Modules_XML();

		$configFile = $this->app->getPath('/Extensions/' . $file);
		$settings->load($configFile);

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
	public function set($xPathKey, $value, $file) {

		$settings = new Modules_XML();

		$configFile = $this->app->getPath('/Extensions/' . $file);
		$settings->load($configFile);

		$nodes = $settings->XPath()->query($xPathKey);

		if($nodes->length == 1) {
			$nodes->item(0)->nodeValue = $value;
			$settings->save($settingsFile);
		}

	}

}