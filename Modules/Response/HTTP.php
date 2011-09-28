<?php

class Modules_Response_HTTP {

	public $status = array();

	public function __construct() {

		$this->status['100'] = 'Continue';
		$this->status['101'] = 'Switching Protocols';
		$this->status['102'] = 'Processing';

		$this->status['200'] = 'OK';
		$this->status['201'] = 'Created';
		$this->status['202'] = 'Accepted';
		$this->status['203'] = 'Non-Authoritative Information';
		$this->status['204'] = 'No Content';
		$this->status['205'] = 'Reset Content';
		$this->status['206'] = 'Partial Content';
		$this->status['207'] = 'Multi Status';

		$this->status['300'] = 'Multiple Choice';
		$this->status['301'] = 'Moved Permanently';
		$this->status['302'] = 'Found';
		$this->status['303'] = 'See Other';
		$this->status['304'] = 'Not Modified';
		$this->status['305'] = 'Use Proxy';
		#$this->status['306'] = '';
		$this->status['307'] = 'Temporary Redirect';

		$this->status['400'] = 'Bad Request';
		$this->status['401'] = 'Unauthorized';
		$this->status['402'] = 'Payment Required';
		$this->status['403'] = 'Forbidden';
		$this->status['404'] = 'Not Found';
		$this->status['405'] = 'Method Not Allowed';
		$this->status['406'] = 'Not Acceptable';
		$this->status['407'] = 'Proxy Authentication Required';
		$this->status['408'] = 'Request Time-out';
		$this->status['409'] = 'Conflict';
		$this->status['410'] = 'Gone';
		$this->status['411'] = 'Length Required';
		$this->status['412'] = 'Precondition Failed';
		$this->status['413'] = 'Request Entity Too Large';
		$this->status['414'] = 'Request-URI Too Long';
		$this->status['415'] = 'Unsupported Media Type';
		$this->status['416'] = 'Request range not satisfiable';
		$this->status['417'] = 'Expectation Failed';
		$this->status['418'] = "I'm a Teapot";
		$this->status['421'] = 'There are too many connections from your internet address';
		$this->status['422'] = 'Unprocessable Entity';
		$this->status['423'] = 'Locked';
		$this->status['424'] = 'Failed Dependency';
		$this->status['425'] = 'Unordered Collection';
		$this->status['426'] = 'Upgrade Required';

		$this->status['500'] = 'Internal Server Error';
		$this->status['501'] = 'Not Implemented';
		$this->status['502'] = 'Bad Gateway';
		$this->status['503'] = 'Service Unavailable';
		$this->status['504'] = 'Gateway Time-out';
		$this->status['505'] = 'HTTP Version not supported';
		$this->status['506'] = 'Variant Also Negotiates';
		$this->status['507'] = 'Insufficient Storage';
		$this->status['509'] = 'Bandwidth Limit Exceeded';
		$this->status['510'] = 'Not Extended';

	}

	public function setStatus($num) {

		if(isset($this->status[$num])) {
			header("HTTP/1.1 " . $num . " " . $this->status[$num]);
		}

	}

}