<?php

class Application_View_String {

	protected $content;

	public function __construct($string) {
		$this->content = $string;
	}

	public function render($output=false) {

		if($output==true) {

			echo $this->content;

		} else {

			return $this->content;

		}

	}

}