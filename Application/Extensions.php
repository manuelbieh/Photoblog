<?php

class Application_Extensions {

	public function buildIndex($dest=0) {

		$core		= Application_Base::getCoreDir();
		$project	= Application_Base::getProjectDir();

		$dirs		= explode('/', rtrim($project, '/'));

		$project	= end($dirs) == 'Admin' ? realpath(rtrim($project, '/') . '/..') : $project;
		//$project	= strpos('Admin', $project
		//var_dump($project);

		$xml = new Modules_XML();

		$coreFiles		= glob($core . '/Extensions/*.xml');
		$projectFiles	= glob($project . '/Extensions/*.xml');

		$files			= array_merge($coreFiles, $projectFiles);

		foreach($files AS $extMeta) {

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

	public function getExtensions($class) {

		$coreExtensions		= Application_Base::getCoreDir();
		$projectExtensions	= Application_Base::getProjectDir();
		
		$xml = new Modules_XML();

	}

}