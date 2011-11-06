<?php

class Application_ExtensionManager {

	protected $app;
	public $observers = array();

	public function __construct($app) {

		$this->app = $app;

	}

	public function buildIndex($dest=1) {

		$extensionGateway	= new Model_Extension_Gateway_PDO($this->app->objectManager->get('Datastore'));
		$extensionMapper	= new Model_Extension_Mapper($extensionGateway);

		$core				= Application_Base::getCoreDir();
		$project			= Application_Base::getProjectDir();

		$dirs				= explode('/', rtrim($project, '/'));
		$project			= end($dirs) == 'Admin' ? realpath(rtrim($project, '/') . '/..') : $project;
		$xml 				= new Modules_XML();

		$coreFiles			= glob(realpath($core) . '/Extensions/*.xml');
		$projectFiles		= glob(realpath($project) . '/Extensions/*.xml');
		$files				= array_unique(array_merge($coreFiles, $projectFiles));

		foreach($files AS $extMeta) {

			$ext = $extensionMapper->find(basename($extMeta, '.xml'), new Model_Extension);
			if($ext != NULL && $ext->active == 1) {

				$xml->load($extMeta);
				$classNodes = $xml->XPath()->query("//class/@name");

				foreach($classNodes AS $extClass) {

					$hooks = array();
					$hookNodes = $xml->XPath()->query("../hooks/item", $extClass);

					foreach($hookNodes AS $extHook) {
						$base[$extHook->textContent][] = $extClass->textContent;
					}

				}

			}

		}

		$hooksFile	= new Modules_XML();
		$rootEl		= $hooksFile->createElement('hooks');
		$hooksFile->appendChild($rootEl);

		foreach($base AS $classToHook => $extensions) {

			$classEl = $hooksFile->createElement('class');
			$classEl->setAttribute('name', $classToHook);
			$rootEl->appendChild($classEl);

			foreach($extensions AS $extClass) {
				$itemEl = $hooksFile->createElement('item', $extClass);
				$classEl->appendChild($itemEl);
			}

		}

		switch($dest) {

			case 0:
			default:
				$dest = $core;
				break;

			case 1:
				$dest = $project;
				break;

			case 2:
				$dest = realpath($project . '/..');
				break;

		}

		$hooksFile->save($dest . '/Sys/Hooks.xml');

	}

	public function getExtensions($class, $dest=0) {

		$core		= $this->app->getCoreDir();
		$project	= $this->app->getProjectDir();

		$dirs		= explode('/', rtrim($project, '/'));
		$project	= end($dirs) == 'Admin' ? realpath(rtrim($project, '/') . '/..') : $project;

		switch($dest) {

			case 0:
			default:
				$dest = $core;
				break;

			case 1:
				$dest = $project;
				break;

			case 2:
				$dest = realpath($project . '/..');
				break;

		}

		$hooksFile = new Modules_XML();
		$hooksFile->load($dest . '/Sys/Hooks.xml');

		$classes = $hooksFile->XPath()->query("//hooks/class[@name='" . $class . "']/item");

		$ext = array();
		foreach($classes AS $item) {
			if(class_exists($item->textContent)) {
				$ext[$item->textContent] = $item->textContent;
			}
		}

		return $ext;

	}

	public function registerObservers(&$obj, $dest=0) {

		$ext = $this->getExtensions(get_class($obj));

		foreach($ext AS $obs) {

			$this->addObserver(get_class($obj), new $obs($this->app));

		}

	}

	public function addObserver($className, $obs, $dest=0) {

		$this->observers[$className][get_class($obs)] = $obs;

	}

	public function notify(&$obj, $state, $additionalParams=NULL) {

		foreach((array) $this->observers[get_class($obj)] AS $obs) {

			if(method_exists($obs, $state)) {

				$obs->$state($obj, $additionalParams);

			}

		}

	}

}