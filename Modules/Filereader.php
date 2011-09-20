<?php

class Modules_Filereader {

	public function loadFilesFromDir($dir) {

		if(is_dir($dir)) {

			$files = array();

			foreach(glob($dir . "/*") AS $file) {
				$files[] = $file;
			}

		}

		return $files;

	}


	public static function getFoldersRecursive($start, $tree=array()) {

		$folders = glob(rtrim($start,'/') . '/*', GLOB_ONLYDIR);

		if(is_array($folders)) {

			foreach($folders AS $num => $dir) {

				if(glob(rtrim($dir, '/'), GLOB_ONLYDIR) . '/*') {
					$tree[basename($start)][basename($dir)] = self::getFoldersRecursive($dir, $tree[basename($start)][basename($dir)]);
				} else {
					$tree[basename($start)][basename($dir)] = array();
				}

			}

		} else {
			$tree[basename($start)] = true;
		}

		return $tree[basename($start)];

	}


	public function getFileInfo($file) {

		$stat = stat($file);
		$stat['kbyte'] = $stat['size']/1024;
		$stat['mbyte'] = $stat['kbyte']/1024;
		$stat['gbyte'] = $stat['gbyte']/1024;
		return $stat;		

	}

}
