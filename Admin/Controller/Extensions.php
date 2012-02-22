<?php

class Admin_Controller_Extensions extends Controller_Frontend {

	public function __construct($app=NULL) {

		$this->app = $app;

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}

		$this->view		= $this->app->objectManager->get('Application_View');
		$this->access	= $this->app->objectManager->get('Admin_Application_Access');

		if(!$this->app->isAjaxRequest()) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

		$this->app->extensions()->buildIndex();

		$this->extGateway	= new Model_Extension_Gateway_PDO($this->app->objectManager->get('Datastore'));
		$this->extMapper	= new Model_Extension_Mapper($this->extGateway);

	}

	public function manage() {

		if($this->access->check(__METHOD__)) {

			$files = $this->getExtFiles();

			$xml = new Modules_XML();

			foreach($files AS $extMeta) {

				$extKey = basename($extMeta, '.xml');
				$ext = $this->extMapper->find($extKey, new Model_Extension);

				$xml->load($extMeta);

				$name = $xml->XPath()->query("//extension/@name");
				$desc = $xml->XPath()->query("//extension/meta/description");
				$deps = $xml->XPath()->query("//extension/@deps");
				$core = $xml->XPath()->query("//extension/@core");
				$icon = $xml->XPath()->query("//extension/@icon");

				$settings = $xml->XPath()->query("//extension/config/settings/*");
				if($settings->length > 0) {
					$hasSettings = true;
				} else {
					$hasSettings = false;
				}

				$extData = array(
					'extKey'=>$extKey, 
					'name'=>$name->item(0)->textContent, 
					'desc'=>$desc->item(0)->textContent, 
					'deps'=>$deps->item(0)->textContent, 
					'core'=>$core->item(0)->textContent, 
					'icon'=>$icon->item(0)->textContent,
					'hasSettings'=>$hasSettings
				);

				if($ext == false) {

					$newExt = new Model_Extension();
					$newExt->core = (int) $extData['core'];
					$newExt->deps = $extData['deps'];
					$newExt->active = 0;
					$newExt->extension_key = $extData['extKey'];

					$this->extMapper->save($newExt);

				}

				if($ext != NULL && $ext->active == 1) {
					$active[] = $extData;
				} else {
					$inactive[] = $extData;
				}

			}

			$subview = $this->app->createView();
			$subview->loadHTML('templates/extensions/manage.html');

			$subview->data['active'] = $active;
			$subview->data['inactive'] = $inactive;

			$this->view->addSubview('main', $subview);

		}

	}


	public function activate($extKey) {

		$subview = $this->app->createView();

		$files = $this->getExtFiles();
		if(is_array($files)) {
			foreach($files AS $num => $extFile) {
				$files[$num] = basename($extFile, '.xml');
			}
		}

		if(in_array($extKey, $files)) {

			$ext = $this->extMapper->find($extKey, new Model_Extension);
			$ext->active = 1;
			$this->extMapper->save($ext);
			$this->app->extensions()->buildIndex();

			$extFile = $this->getExtFile($extKey);

			$xml = new Modules_XML();
			$xml->load($extFile);

			$extSettings = $xml->XPath()->query("//extension/config/settings");
			$name = $xml->XPath()->query("//extension/@name");

			$subview->data['ext']->extKey = $extKey;
			$subview->data['ext']->name = $name->item(0)->textContent;
			$subview->data['ext']->hasSettings = ((int) $extSettings->length === 0) ? false : true;

			$subview->loadHTML('templates/extensions/activate.success.html');

		} else {

			$subview->loadHTML('templates/extensions/activate.error.notfound.html');

		}

		$this->view->addSubview('main', $subview);
	}


	public function deactivate($extKey) {

		$subview = $this->app->createView();

		$files = $this->getExtFiles();
		if(is_array($files)) {
			foreach($files AS $num => $extFile) {
				$files[$num] = basename($extFile, '.xml');
			}
		}

		if(in_array($extKey, $files)) {

			$ext = $this->extMapper->find($extKey, new Model_Extension);
			$ext->active = 0;
			$this->extMapper->save($ext);
			$this->app->extensions()->buildIndex();

			$subview->loadHTML('templates/extensions/deactivate.success.html');

		} else {

			$subview->loadHTML('templates/extensions/deactivate.error.notfound.html');

		}

		$this->view->addSubview('main', $subview);

	}


	public function browse($param1=NULL, $param2=NULL, $param3=NULL, $param4=NULL, $param5=NULL) {

		$subview	= $this->app->createView();

		$repoUrl = Application_Settings::get("//system/backend/extRepoUrl", 1);

		$curlObj = new Modules_Curl();
		$curlObj->setOption(CURLOPT_CONNECTTIMEOUT, 60)
				->setOption(CURLOPT_USERAGENT, 'EXHIBIT - Extension Repo/1.0 (http://extensions.exhibit-blog.net)');

		$curlObj->connect($repoUrl);

		$httpStatus = $curlObj->info(CURLINFO_HTTP_CODE);
		if($httpStatus < 400) {

			switch($param1) {

				case 'index':
				case 'start':
				default:
					$curlObj->connect($repoUrl . 'browse/index');
					break;

				case 'tags':
					$curlObj->connect($repoUrl . 'browse/tags/' . $param2);
					break;

				case 'popular':
					$curlObj->connect($repoUrl . 'browse/popular');
					break;
			
				case '':
					break;

			}

			$exts = json_decode($curlObj->exec(), true);

			if($exts != NULL && count($exts) > 0) {

				$subview->data = $exts;
				$subview->loadHTML('templates/extensions/browse.html');

			}

		}

		$subview = new Application_View_String(__('Extension browser is coming soon!'));

		$this->view->addSubview('main', $subview);

	}


	public function settings($extension_key) {

		$subview	= $this->app->createView();
		$ext		= new Application_Extension($this->app);
		$form		= new Modules_Form("templates/extensions/settings.form.html");

		$extensionFile = $this->app->getPath('/Extensions/' . $extension_key . '.xml');

		if($extensionFile != false) {

			$extension = new Modules_XML();
			$extension->load($extensionFile);
			$extensions = $extension->XPath()->query("//extension/config/settings/*");

			if($extensions->length > 0) {

				if($form->isSent()) {

					$checkboxes = $extension->XPath()->query("//settings//*[@type='checkbox']");
					if($checkboxes->length > 0) {
						foreach($checkboxes AS $checkbox) {
							$checkbox->nodeValue = 0;
						}
					}


					if(is_array($form->valueOf('data'))) {

						foreach($form->valueOf('data') AS $xpath => $value) {

							$node = $extension->XPath()->query("//settings/" . $xpath);
							$childnode = $node->item(0);
							$childnode->nodeValue = $value;

						}

					}

					if($extension->save($extensionFile) !== false) {

						$subview->loadHTML('templates/extensions/settings.edit.success.html');
						$this->view->addSubview('main', $subview);

					} else {

						$subview->loadHTML('templates/extensions/settings.edit.error.html');
						$this->view->addSubview('main', $subview);

					}


				} else {

					$nodes = $extension->XPath()->query("//settings/*");

					if($nodes->length > 0) {

						$xmlForm = new Modules_XMLSimpleForm();
						$extensionForm = '';

						foreach($nodes AS $node) {

							$extensionForm .= $xmlForm->renderNode($node, $extension);

						}

					}

					$form->assign('content', $extensionForm);

					$this->view->addSubview('main', $form);

				}

			} else {

				$subview->loadHTML('templates/extensions/settings.error.noneavailable.html');
				$this->view->addSubview('main', $subview);

			}

		} else {

			$subview->loadHTML('templates/extensions/settings.error.notfound.html');
			$this->view->addSubview('main', $subview);


		}

	}

	public function details($extKey) {

		

	}

	public function install($extKey, $version='') {

		$repoUrl = Application_Settings::get("//system/backend/extRepoUrl", 1);

		$curlObj = new Modules_Curl();
		$curlObj->setOption(CURLOPT_CONNECTTIMEOUT, 60)
				->setOption(CURLOPT_USERAGENT, 'EXHIBIT - Extension Repo/1.0 (http://extensions.exhibit-blog.net)');

		$curlObj->connect($repoUrl . '/download/' . $extKey . '/' . $version);

		$httpStatus = $curlObj->info(CURLINFO_HTTP_CODE);
		if($httpStatus < 400) {
		}


	}

	public function uninstall($extkey) {

		

	}


	protected function getExtFiles() {

		$core				= $this->app->getCoreDir();
		$project			= $this->app->getProjectDir();

		$dirs				= explode('/', rtrim($project, '/'));
		$project			= end($dirs) == 'Admin' ? realpath(rtrim($project, '/') . '/..') : $project;

		$coreFiles			= glob(realpath($core) . '/Extensions/*.xml');
		$projectFiles		= glob($project . '/Extensions/*.xml');

		$files				= array_unique(array_merge($coreFiles, $projectFiles));

		return $files;

	}

	protected function getExtFile($extKey) {

		$files = $this->getExtFiles();

		if(is_array($files)) {

			foreach($files AS $extFile) {

				if(basename($extFile, '.xml') == $extKey) {
					return $extFile;
				}

			}

		}

	}

}