<?php
error_reporting(E_ERROR);

$rootDir = realpath(dirname(__FILE__) . '/../../');

require_once $rootDir . '/Application/Base.php';
$app = new Application_Base();
$app->setProjectDir(dirname(__FILE__ . "/../.."));


require_once 'PHPUnit/Framework.php';

class Modules_FormTest extends PHPUnit_Framework_TestCase {

	public function setUp() {

		$this->form = new Modules_Form();	

	}

	public function testInput() {
		$result = $this->form->input(array('name'=>'test_name', 'id'=>'test_id', 'class'=>'test_class', 'data-attribute'=>'test_data-attribute', 'value'=>'value'));
        $expected = '<input type="text" name="test_name" id="test_id" value="value" class="test_class" data-attribute="test_data-attribute" />';
        $this->assertTrue($result == $expected);
	}

	#public function testSelect() {}
	#public function testOption() {}

}
