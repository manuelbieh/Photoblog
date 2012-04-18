<?php

class Sys_Helper_Update {

	protected $latest;

	public function __construct($app) {

		require_once $app->getCoreDir() . "Sys/libs/pclzip/pclzip.lib.php";

		$this->app = $app;

		$this->systemMapper = new Model_System_Mapper(
			new Model_System_Gateway_PDO(
				$this->app->objectManager->get('Datastore')
			)
		);

	}

	public function test() {

		$core		= $this->app->getCoreDir();
		$version	= $this->app->getVersion();
		$path		= $core . 'Sys/backup/';
		#print_r($sqlMapper->exportTableData());
		$archive	= new PclZip($path . 'test.zip');
		$archive->create($core, 
							PCLZIP_OPT_REMOVE_PATH, $core,
							PCLZIP_CB_PRE_ADD, 'backupPreAddCallback'
						);
		var_dump($archive->errorInfo(true));

	}

	public function updateDatabase() {

		// there are updates available
		$files = $this->download($version);
		$status = array();

		if(is_array($files['sql'])) {

			foreach($files['sql'] AS $sqlFile) {

				if(Modules_Filesys::isFile($sqlFile)) {
					
					$installSQL = Modules_Filesys::read($sqlFile);
					$sqlImport = $this->systemMapper->importDump($installSQL);

					if(isset($sqlImport['error'])) {
						$status['error'] = __('Failed to perform database upgrade.');
					}

				}

			}

		}

		return $status;

	}

	public function update($version=NULL) {

		$core		= $this->app->getCoreDir();
		$files		= $this->download($version);
		$version	= $version == NULL ? $this->latest : $version;

		if($this->checkForUpdates() === true) {

			if(Modules_Filesys::isFile($core . '/Sys/update/beforeUpdate.' . $version . '.php')) {

				try {

					include_once $core . '/Sys/update/beforeUpdate.' . $version . '.php';

				} catch (Exception $e) {

					$status['error'] = __('An error occured. Error message: ' . $e->getMessage());
					return $status;

				}

			}

			$this->systemMapper->beginTransaction();

			$status = $this->updateDatabase();

			if(!isset($status['error']) && is_array($files['update'])) {

				foreach($files['update'] AS $updateFile) {

					$archive = new PclZip($updateFile);

					if($archive->extract(PCLZIP_OPT_PATH, $core, PCLZIP_OPT_REPLACE_NEWER) == 0) {
						// SQL ROLLBACK
						$this->systemMapper->rollBack();
						$status['error'] = __('Failed to unzip file. Make sure the directory permissions are set properly or update files manually!');
						break;

					}

				}

			}

			if(!isset($status['error'])) {


				if(Modules_Filesys::isFile($core . '/Sys/update/afterUpdate.' . $version . '.php')) {

					try {

						include_once $core . '/Sys/update/afterUpdate.' . $version . '.php';

					} catch (Exception $e) {

						$this->systemMapper->rollBack();
						$status['error'] = __('An error occured. Error message: ' . $e->getMessage());
						return $status;

					}

				}

				// SQL COMMIT
				$this->systemMapper->commit();
				$this->app->setVersion($this->latest);
				$status['info'] = __('Update was successful! New version is ') . $this->latest;

			}

		} else {

			$status['info'] = __('No updates available');

		}

		return $status;

	}


	public function backup() {

		$core		= $this->app->getCoreDir();
		$version	= $this->app->getVersion();

		$path		= $core . 'Sys/backup/';
		$files		= 'files_'.date('Ymd-His').'.zip';
		$sqlFile	= 'database_'.date('Ymd-His').'.sql';

		$archive	= new PclZip($path . $files);

		$tables		= $this->systemMapper->exportTables();
		$inserts	= $this->systemMapper->exportTableData();
		$qD			= $this->systemMapper->getQueryDelimiter();

		$sqlDump = join($qd, $tables);
		$sqlDump .= join($qd, $inserts);

		file_put_contents($path . $sqlFile, $sqlDump);
		#Modules_Filesys::write($path . $sqlFile, $sqlDump);

		if($archive->create($core, PCLZIP_OPT_REMOVE_PATH, $core, PCLZIP_CB_PRE_ADD, 'backupPreAddCallback') == 0) {

			return false;

		} else {

			$blacklist = array('uploads/', 'uploads', 'Sys/backup/');
		//	$archive->delete(PCLZIP_OPT_BY_NAME, $blacklist);
		//	$archive->delete(PCLZIP_OPT_BY_EREG, '(\.svn)');

			return true;

		}

	}


	public function download($version=NULL) {

		$updateServer = Application_Settings::get('//system/update/updateServerUrl', 1);
		$core = $this->app->getCoreDir();

		$curlObj = new Modules_Curl();
		$curlObj->setOption(CURLOPT_CONNECTTIMEOUT, 120)
				->connect($updateServer . '/update/' . $this->app->getVersion() . '/' . $version);

		$httpStatus = $curlObj->info(CURLINFO_HTTP_CODE);

		if($httpStatus >= 400) {
			// Error
			// throw new UpdateException();
			return false;

		} else {

			$response = json_decode($curlObj->exec(), true);
			if(is_array($response)) {

				foreach($response AS $version => $data) {

					$updateFilename = $core . 'Sys/update/' . basename($data['url']);
					if(isset($data['sql'])) {
						$sqlFilename = $core . 'Sys/update/' . basename($data['sql']);
					}

					if(isset($data['callback'])) {
						foreach($data['callback'] AS $type => $callbackFile) {
							$callbackFiles[$type] = $callbackFile;
						}
					}

					if(!Modules_Filesys::isFile($updateFilename)) {

						$curlObj = new Modules_Curl();
						$curlObj->connect($updateServer . $data['url']);
						$httpStatus = $curlObj->info(CURLINFO_HTTP_CODE);
						$updateData = $curlObj->exec();

						if($httpStatus < 400 && $updateData != 'null') {
							file_put_contents($updateFilename, $updateData);
						}

					}

					if(isset($sqlFilename) && !Modules_Filesys::isFile($sqlFilename)) {

						$curlObj = new Modules_Curl();
						$curlObj->connect($updateServer . $data['sql']);
						$httpStatus = $curlObj->info(CURLINFO_HTTP_CODE);
						$sqlData = $curlObj->exec();

						if($httpStatus < 400 && $sqlData != 'null') {
							file_put_contents($sqlFilename, $sqlData);
						}

					}

					if(isset($callbackFiles) && is_array($callbackFiles)) {

						foreach($callbackFiles AS $type => $callbackFile) {

							$curlObj = new Modules_Curl();
							$curlObj->connect($updateServer . $callbackFile);

							$httpStatus = $curlObj->info(CURLINFO_HTTP_CODE);
							$cbData = $curlObj->exec();

							if($httpStatus < 400 && $cbData != 'null') {
								file_put_contents($core . 'Sys/update/' . strtolower($type) . 'Update.' . $version . '.php', $cbData);
							}

						}

					}


					if(isset($updateFilename) && Modules_Filesys::isFile($updateFilename)) {
						$updateFiles['update'][]= $updateFilename;
					}
					if(isset($sqlFilename)) {
						$updateFiles['sql'][] = $sqlFilename;
					}

					$this->latest = $version;

				}

			}

			return $updateFiles;

		}

	}


	public function getLatest() {

		$curlObj = new Modules_Curl();

		$curlObj->setOption(CURLOPT_CONNECTTIMEOUT, 60)
				->connect($updateServer);

		$response = $curlObj->exec();
		$response = json_decode($response, true);
		$this->latest = $response['latest'];

		return $this->latest;

	}


	public function checkForUpdates() {

		$updateServer = Application_Settings::get('//system/update/updateServerUrl', 1);

		if($updateServer != '') {

			$curlObj = new Modules_Curl();

			$curlObj->setOption(CURLOPT_CONNECTTIMEOUT, 60)
					->connect($updateServer);

			$httpStatus = $curlObj->info(CURLINFO_HTTP_CODE);

			if($httpStatus >= 400) {

				//throw new UpdateException();
				return false;

			} else {

				$response = $curlObj->exec();
				$response = json_decode($response, true);

				$installed = $this->app->getVersion();
				$available = $response['latest'];

				// -1 = installed is older
				// 0 = installed is current
				// 1 = installed is newer
				if($this->compareVersion($installed, $available) === -1) {
					return true;
				} else {
					return false;
				}

			}

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


function backupPreAddCallback($p_event, &$p_header) {

	if(strpos($p_header['stored_filename'], 'Sys/backup') === 0) {
		return 0;
	//} else if(strpos($p_header['stored_filename'], 'uploads') === 0) {
	//	return 0;
	} else {
		return 1;
	}

}
