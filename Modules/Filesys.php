<?php

class Modules_Filesys {

	public function __construct() {}

	public static function read($filename) {

		if(is_file($filename) && is_readable($filename)) {
			return file_get_contents($filename);
		}

	}


	public static function dynload($filename) {

	#	if(self::isFile($filename)) {
			ob_start();
			include $filename;
			$content = ob_get_contents();
			ob_end_clean();
	#	}

		return $content;

	}

	public static function write($filename, $content, $flags=NULL) {

		return file_put_contents($filename, $content, $flags);

	}

	public static function delete($filename) {

		if(is_file($filename) && is_writable($filename)) {
			return unlink($filename);
		} else if(is_dir($filename) && is_writable($filename)) {
			return rmdir($filename);
		}

	}

	public static function isFile($filename) {
		return is_file($filename);
	}

	public static function isDir($filename) {
		return is_dir($filename);
	}

	public static function isReadable($filename) {
		return is_readable($filename);
	}

	public static function isWritable($filename) {
		return is_writable($filename);
	}

}

?>