<?php

class Application_Benchmark {

	function __construct() {}
	
	public function start($test=0) {
		$this->start[$test] = self::microtime_float();
	}

	public function end($test=0, $msg='') {
		$end = self::microtime_float();
		$dur = $end - $this->start[$test];
		if(isset($this->start[$test])) {
			return '<p>' . $msg . $dur .'</p>';
		}
	}

	private function microtime_float() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

}

?>