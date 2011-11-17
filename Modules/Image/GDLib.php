<?php

class Modules_Image_GDLib {

	public $filename = NULL;
	protected $COMPRESSION_QUALITY = 90;
	protected $filehandle = NULL;
	protected $format;

	public function __construct($filename=NULL) {

		if($filename !== NULL) {
			$this->readImage($filename);
		}

	}

	public function readImage($filename) {

		if(is_file($filename) && is_readable($filename)) {
			$this->filename = $filename;
		} else {
			throw new Exception('File not found.');
		}

	}

	public function getImageWidth() {
		$size = $this->getImageSize();
		return $size[0];
	}

	public function getImageHeight() {
		$size = $this->getImageSize();
		return $size[1];
	}

	public function getImageMimeType() {
		return $this->getImageType();
	}

	public function getImageType() {
		$size = $this->getImageSize();
		return $size['mime'];
	}

	public function getImageSize() {
		$size = @getimagesize($this->filename);
		return $size;
	}

	public function setCompressionQuality($value) {
		if((int) $value >= 0 && (int) $value <= 100) {
			$this->COMPRESSION_QUALITY = (int) $value;
		}
	}

	public function setImageFormat($format) {

		switch($format) {

			case 'png':
				$this->format = 'image/png';
				break;

			case 'jpg':
			case 'jpeg':
				$this->format = 'image/jpg';
				break;

			case 'gif':
				$this->format = 'image/gif';
				break;

		}

	}

	public function thumbnailImage($width, $height, $bestfit = false) {

		$source_w = $this->getImageWidth();
		$source_h = $this->getImageHeight();

		$imageType = strtolower($this->getImageType());

		switch($imageType) {

			case 'image/jpeg':
			case 'image/jpg':
			case 'image/pjpeg':
				$oldImage = ImageCreateFromJPEG($this->filename);
				break;
			case 'image/png':
				$oldImage = ImageCreateFromPNG($this->filename);
				break;
			case 'image/gif':
				$oldImage = ImageCreateFromGIF($this->filename);
				break;

		}

		$this->filehandle = imagecreatetruecolor($width, $height);
		imageCopyResampled($this->filehandle, $oldImage, 0, 0, 0, 0, $width, $height, $source_w, $source_h);

/*
		if( ($w <= $width) && ($h <= $height) && ($bestfit == false) ) {
			
		} else {
			
		}
*/

	}

	public function writeImage($outputFilename) {

		if($this->filehandle !== NULL) {

			$output = explode('.', $outputFilename);
			$suffix = isset($this->format) ? $this->format : strtolower(end($output));

			switch($suffix) {

				case 'jpg':
				case 'jpeg':
				case 'pjpeg':
				default:
					ImageJPEG($this->filehandle, $outputFilename, $this->COMPRESSION_QUALITY);
					break;
				case 'gif':
					ImageGIF($this->filehandle, $outputFilename);
					break;
				case 'png':
					ImagePNG($this->filehandle, $outputFilename);
					break;
			}

		}

	}


}

?>