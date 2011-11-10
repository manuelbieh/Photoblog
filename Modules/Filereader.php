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


	public function getFoldersRecursive($start, $blacklist=array(), $tree=array()) {

		$folders = glob(rtrim($start,'/') . '/*', GLOB_ONLYDIR);

		if(is_array($folders)) {

			foreach($folders AS $num => $dir) {

				if(glob(rtrim($dir, '/'), GLOB_ONLYDIR) . '/*') {
					$tree[basename($start)][basename($dir)] = $this->getFoldersRecursive($dir, $blacklist, $tree[basename($start)][basename($dir)]);
				} else {
					$tree[basename($start)][basename($dir)] = array();
				}

			}

		} else {

			$tree[basename($start)] = true;

		}

		return $tree[basename($start)];

	}

	public function copyRecursive($source, $dest, $diffDir = ''){

		$sourceHandle = opendir($source);

		if(!$diffDir) {
			$diffDir = $source;
		}
	   
		mkdir($dest . '/' . $diffDir);
	   
		while($res = readdir($sourceHandle)){

			if($res == '.' || $res == '..') {
				continue;
			}
		   
			if(is_dir($source . '/' . $res)) {

				$this->copyRecursive($source . '/' . $res, $dest, $diffDir . '/' . $res);

			} else {

				copy($source . '/' . $res, $dest . '/' . $diffDir . '/' . $res);

			}

		}

	} 



	public function getFileInfo($file) {

		$stat = stat($file);
		$stat['kbyte'] = $stat['size']/1024;
		$stat['mbyte'] = $stat['kbyte']/1024;
		$stat['gbyte'] = $stat['gbyte']/1024;
		return $stat;		

	}

}
