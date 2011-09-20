<?php

class Modules_Structure_Presets extends Modules_Structure {

	public function __construct(Array $data) {
		parent::__construct($data);
		#define('NL', "\n");
	}

	public function HTMLunorderedList() {

		$this->listWrap    = '<ul>%content%</ul>';

		$this->beforeList  = '<ul class="level-%level%">';
		$this->beforeItem  = '<li>';
		$this->beforeChild = '';

		$this->afterList   = '</ul>';
		$this->afterItem   = '</li>';
		$this->afterItemSeparator = '';
		$this->afterChild  = '';

		$this->itemContent = '%item%';

	}

	public function HTMLorderedList() {

		$this->listWrap    = '<ol>%content%</ol>';

		$this->beforeList  = '<ol class="level-%level%">';
		$this->beforeItem  = '<li>';
		$this->beforeChild = '';

		$this->afterList   = '</ol>';
		$this->afterItem   = '</li>';
		$this->afterItemSeparator = '';
		$this->afterChild  = '';

		$this->itemContent = '%item%';

	}

	public function JSONobject() {

		$this->listWrap    = '[%content%]';

		$this->beforeList  = '[';
		$this->beforeItem  = '{';
		$this->beforeChild = '';

		$this->afterList   = ']';
		$this->afterItem   = '}';
		$this->afterItemSeparator = ',';
		$this->afterChild  = '';

		$this->itemContent = '%item%';

	}

}


?>