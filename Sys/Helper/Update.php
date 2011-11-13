<?php

class Sys_Helper_Update {

	public function __construct($app) {

		require_once $app->getCoreDir() . "Sys/libs/pclzip/pclzip.lib.php";
		$this->app = $app;

	}


	public function update($version='latest') {

		$core = $this->app->getCoreDir();

		if($version=='latest') {

			$updateFiles = glob($core . '/Sys/update/update*.zip');

			if(is_array($updateFiles)) {
				usort($updateFiles, 'self::sortVersionFiles');
				$updateFiles = array_map($updateFiles, 'basename');
				$updateFile = end($updateFiles);
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


	public function backup() {

		$core		= $this->app->getCoreDir();
		$version	= $this->app->getVersion();

		$filename = 'backup_'.date('Ymd-His').'.zip';
		$fullname = $core . '/Sys/backup/' . $filename;

		$archive = new PclZip($fullname);

		if($archive->create($core, PCLZIP_OPT_REMOVE_PATH, $core) == 0) {

			return false;

		} else {

			$blacklist = array('uploads/', 'uploads', 'Sys/backup/');
			$archive->delete(PCLZIP_OPT_BY_NAME, $blacklist);
			$archive->delete(PCLZIP_OPT_BY_EREG, '(\.svn)');

			return true;

		}

	}


	public function download($version, $serverURL=NULL) {

		$updateServer = Application_Settings::get('//system/update/updateServerUrl', 1);

	}

	public function checkForUpdates() {

		$updateServer = Application_Settings::get('//system/update/updateServerUrl', 1);

		$curlObj = new Modules_Curl();

		$curlObj->setOption(CURLOPT_CONNECTTIMEOUT, 60)
				->connect($updateServer);

		$httpStatus = $curlObj->info(CURLINFO_HTTP_CODE);

		if($httpStatus > 400) {

			//throw new UpdateException();
			return false;

		} else {


			$response = $curlObj->exec();
			$response = json_decode($response, true);

			$installed = $this->app->getVersion();
			$available = $response['currentVersion'];

			// -1 = installed is older
			// 0 = installed is current
			// 1 = installed is newer
			return $this->compareVersion($installed, $available);

		}

	}

	public function compareVersion($a, $b) {

		$a = $this->padVersion($a);
		$b = $this->padVersion($b);

		if($a < $b) {
			return -1;
		} else if($a > $b) {
			return 1;
		} else {
			return 0;
		}

	}

	private function sortVersionFiles($a, $b) {

		$regex = '/(.*)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})(.*)/U';
		preg_match($regex, $a, $aVal);
		preg_match($regex, $b, $bVal);

		$this->compareVersion($aVal[2], $bVal[2]);

	}

	public function padVersion($version) {

		$version = explode('.', $version);

		$version[0] = str_pad((int) trim($version[0]), 5, 0, STR_PAD_LEFT);
		$version[1] = str_pad((int) trim($version[1]), 5, 0, STR_PAD_LEFT);
		$version[2] = str_pad((int) trim($version[2]), 5, 0, STR_PAD_LEFT);

		return $version[0] . $version[1] . $version[2];

	}



}