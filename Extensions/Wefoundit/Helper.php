<?php

class Extensions_Wefoundit_Helper {

	public function getSourceFolder() {

		$core	= Application_Base::getCoreDir();
		$source	= glob(rtrim($core, '/') . "/uploads/source/*", GLOB_ONLYDIR);
		return $source[0];

	}


}