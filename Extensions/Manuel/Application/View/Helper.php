<?php

class Extensions_Manuel_Application_View_Helper {

	public function link($label, $link=NULL, $class=NULL, $target=NULL) {

		if($link === NULL || $link === '') {

			return $label;

		} else {

			$class	= $class === NULL || $class === '' ? '' : ' class="'.$class.'" ';
			$target	= $target === NULL || $target === '' ? '' : ' target="'.$target.'" ';

			if(strpos($link, 'http') !== 0) {
				$link = 'http://' . $link;
			}

			return '<a href="'.$link.'"' . $class . $target . '>'.$label.'</a>';

		}

	}

}