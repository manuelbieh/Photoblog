<?php

class Extensions_Wefoundit_Test {

	public function builtDataArray($obj) {

		$obj->data['testfoo'] = 'Extensiontest' . mt_rand(0, 999);

	}


}