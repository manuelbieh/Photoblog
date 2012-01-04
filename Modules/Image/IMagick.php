<?php

class Modules_Image_IMagick extends IMagick {

	public function __construct($image) {
		parent::__construct($image);
	}

	public function getImageMimeType() {

		if(!is_callable('parent::getImageMimeType')) {

			$type = $this->getImageType();
			switch($type) {
				case 6:
					return 'image/png';
				case 4:
					return 'image/gif';
			}

		} else {

			return parent::getImageMimeType();

		}

	}


}