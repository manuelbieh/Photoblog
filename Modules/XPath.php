<?php

class Modules_XPath {

	public function __construct(DOMDocument $dom) {

		$this->XPath = new DOMXPath($dom);
		return $this;

	}

	public function query() {

		return call_user_func_array(array($this->XPath, 'query'), func_get_args());

	}

}