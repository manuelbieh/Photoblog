<?php

$_POST['Username'] = 'Manuel';
$_POST['Password'] = '123';

class Api_Image extends Api_Base {

	public function Get($image, $width=NULL) {

		$Auth = Api::get('Auth');

		// User authentication
		if($Auth->Verify($_POST['Username'], $_POST['Password'])) {

			// Check for cached version of the image
			if($width !== NULL && realpath('../Content/Cache/' . (int) $width . '/' . $image)) {

				header("Content-Type: image/jpeg");
				$output = file_get_contents(realpath('../Content/Cache/' . (int) $width . '/' . $image));

			// Check for if requested image exists
			} else if(file_exists(realpath('../Content/Source/' . $image))) {

				header("Content-Type: image/jpeg");
				$output = file_get_contents(realpath('../Content/Source/' . $image));

			// 404: No file found
			} else {

				header("HTTP/1.1 404 Not found");
				$output = '{"error":"File not found"}';

			}

		// Authentication failed
		} else {

			header("HTTP/1.1 401 Unauthorized");
			$output = '{"error":"Authentication failed"}';

		}

		echo $output;
		return $output;

	}

	public function Register($Imagedata) {

		$url	= $this->getApi() . '/Image/Register';

		$curl	= new Modules_Curl();
		$curl->connect($url);

		$params = array('Imagedata'=>$Imagedata, 'Token'=>'xyz');
		$curl->setOption(CURLOPT_POST, 1)->setOption(CURLOPT_POSTFIELDS, http_build_query($params));


	}

}

#$test = new Api_Image();
#echo $test->Get('green.jpg');