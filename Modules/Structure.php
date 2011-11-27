<?php


/**
 * Module to create a structue (e.g. nested list, json object, ...) out of an array
 * 
 * @author Manuel Bieh
 *
 */
class Modules_Structure {

	private $data;
	public $listWrap;

	public $beforeList;
	public $beforeItem;

	public $afterList;
	public $afterItem;
	public $afterItemSeparator;

	public $itemContent;
	public $level;
	public $parent_id;
	public $root;

	public $NL = "\n";

	public function __construct($data) {

		$this->data = $data;
		$this->menustring = NULL;

		$this->listWrap   = '<ul>%content%</ul>';

		$this->beforeList = '<ul>';
		$this->beforeItem = '<li>';

		$this->afterList = '</ul>';
		$this->afterItem = '</li>';
		$this->afterItemSeparator = '';

		$this->itemContent = '%item%';

		$this->setDepth(0);
		$this->setRoot(0);
		$this->setParent(0);

	}

	public function setRoot($root=0) {
		$this->root = (int) $root;
		return $this;
	}

	public function setDepth($maxDepth=0) {
		$this->maxDepth = (int) $maxDepth;
		return $this;
	}

	public function setParent($parent_id=0) {
		$this->oldParent = $this->parent_id;
		$this->parent_id = (int) $parent_id;
		return $this;
	}

	public function setLevel($level=0) {
		$this->level = (int) $level;
		return $this;
	}

	public function setListWrap($value="<ul>%content%</ul>") {
		$this->listWrap = $value;
		return $this;
	}

	public function setBeforeList($value="<ul>") {
		$this->beforeList = $value;
		return $this;
	}

	public function setBeforeItem($value="<li>") {
		$this->beforeItem = $value;
		return $this;
	}

	public function setAfterList($value="</ul>") {
		$this->afterList = $value;
		return $this;
	}

	public function setAfterItem($value="</li>") {
		$this->afterItem = $value;
		return $this;
	}

	public function setAfterItemSeparator($value="") {
		$this->afterItemSeparator = $value;
		return $this;
	}

	public function setItemContent($value="%item%") {
		$this->itemContent = $value;
		return $this;
	}

	protected function level($parent_id=0, $level=0) {

		$parent_id = (empty($parent_id)) ? (int) $this->parent_id : $parent_id;
		$level = (empty($level)) ? (int) $this->level : $level;

		if( ($level <= $this->maxDepth) || ($this->maxDepth === 0) ) {

			if(isset($this->data[$parent_id]) && is_array($this->data[$parent_id])) {

				if($parent_id !== $this->parent_id) {
					$this->menustring .= Modules_Functions::patternReplace($this->beforeList, array('level'=>$level, 'parent'=>$parent_id));
				}

				foreach($this->data[$parent_id] AS $id => $content) {

					if(isset($content->_data)) {

						foreach($content->_data AS $key => $value) {
							$kv[$key] = $value;
						}

						$content = $kv;

					}

					$this->menustring .= Modules_Functions::patternReplace($this->beforeItem, $content);
					$this->menustring .= Modules_Functions::patternReplace($this->itemContent, $content);
					$level++;
					$this->level($id, $level);
					$this->menustring .= Modules_Functions::patternReplace($this->afterItem, $content);
					$this->menustring .= $this->afterItemSeparator;

				}

				if($parent_id !== $this->parent_id) {
					$this->menustring = rtrim($this->menustring, $this->afterItemSeparator . " \r\n\t\x0B\0");
					$this->menustring .= $this->afterList;
				}

			}

		}

		$this->menustring = rtrim($this->menustring, $this->afterItemSeparator . " \r\n\t\x0B\0");

	}

	public function draw() {
		$this->level($this->parent_id, $this->level);
		return Modules_Functions::patternReplace($this->listWrap, array('content'=>$this->menustring));
	}



}
