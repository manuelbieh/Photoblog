<?php

class Sys_Helper_Update {

	public function __construct($app) {

		require_once $app->getCoreDir() . "/libs/pclzip/pclzip.lib.php";
		$this->app = $app;

	}

	public function update($version='latest') {

		$core = $this->app->getCoreDir();

		if($version=='latest') {

			$updateFiles = glob($core . '/Sys/update/update-*.zip');

			if(is_array($updateFiles)) {
				// iterate and check for highest version number
			} else {
				// throw new UpdateException('No update files found.');
				return;
			}

		}

		$archive = new PclZip($updateFile);

		if($archive->extract(PCLZIP_OPT_PATH, $core, PCLZIP_OPT_REPLACE_NEWER) == 0) {

			throw new UpdateException($archive->errorInfo(true));

		} else {

			return true;

		}

	}

	



}