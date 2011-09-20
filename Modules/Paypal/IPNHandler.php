<?php

/**
 * IPN Handler.
 *
 * @author	   Torleif Berger
 * @link	   http://www.geekality.net/?p=1420
 * @copyright  © 2011 www.geekality.net
 *
 * @link https://cms.paypal.com/cms_content/US/en_US/files/developer/IPNGuide.pdf
 * @link http://curl.haxx.se/docs/caextract.html
 */
abstract class Modules_Paypal_IPNHandler {

		const paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
		const paypal_sandbox_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

		const charset = 'utf-8';

		/**
		 * Validates and santizes IPN data from PayPal.
		 *
		 * @return	mixed		 returns the processed data or FALSE if validation failed.
		 */
		public function process(array $post_data) {
				// Validate
				$valid = self::validate($post_data);
				if($valid !== true) {
					return false;
				}

				// Sanitize
			#	return self::sanitize($post_data);

		}


		/**
		 * Validates IPN data.
		 *
		 * [!!] Verification will fail if the data has been alterend in *any* way.
		 *
		 * @param	array	 raw ipn post data from paypal
		 * @return	mixed		 returns the reply on error; otherwise `TRUE`
		 */
		protected static function validate(array $ipn_post_data) {

                file_put_contents(Application_Base::getProjectDir() . 'Sys/cache/' . time() . '.validate1.txt' , print_r($ipn_post_data, true));

				// Choose url
				if(array_key_exists('test_ipn', $ipn_post_data) && 1 === (int) $ipn_post_data['test_ipn']) {
					$url = self::paypal_sandbox_url;
				} else {
					$url = self::paypal_url;
				}

				// Set up request to PayPal
				$data = http_build_query(array('cmd' => '_notify-validate') + $ipn_post_data);
				$request = curl_init();
				curl_setopt_array($request, array(
					CURLOPT_URL => $url,
					CURLOPT_POST => TRUE,
					CURLOPT_POSTFIELDS => $data,
					CURLOPT_RETURNTRANSFER => TRUE,
					CURLOPT_HEADER => FALSE,
					CURLOPT_SSL_VERIFYPEER => TRUE,
					CURLOPT_CAINFO => 'cacert.pem',
				));

				// Execute request and get response and status code
				$response = curl_exec($request);
				$status	  = curl_getinfo($request, CURLINFO_HTTP_CODE);
                file_put_contents(Application_Base::getProjectDir() . 'Sys/cache/' . time() . '.validate2.txt' , print_r($response, true));
                file_put_contents(Application_Base::getProjectDir() . 'Sys/cache/' . time() . '.validate3.txt' , print_r($status, true));
                file_put_contents(Application_Base::getProjectDir() . 'Sys/cache/' . time() . '.validate4.txt' , print_r($url, true));
                file_put_contents(Application_Base::getProjectDir() . 'Sys/cache/' . time() . '.validate5.txt' , print_r($data, true));
                file_put_contents(Application_Base::getProjectDir() . 'Sys/cache/' . time() . '.validate5.txt' , print_r($request, true));

				// Close connection
				curl_close($request);

				if($status == 200 && $response == 'VERIFIED') {
					return TRUE;
				}

				return $response;
		}


		protected static function sanitize(array $ipn_data) {
				// Just return empty array if empty
				if( ! $ipn_data) {
					return array();
				}

				// Fix encoding
				self::fix_encoding($ipn_data);

				// Sort keys (easier to debug)
				ksort($ipn_data);

				return $ipn_data;
		}

		protected static function fix_encoding( & $ipn_data)
		{
				// If charset is specified
				if(array_key_exists('charset', $ipn_data) && ($charset = $ipn_data['charset'])) {
						// Ignore if same as our default
						if($charset == self::charset) {
								return;
						}

						// Otherwise convert all the values
						foreach($ipn_data as $key => &$value) {
								$value = mb_convert_encoding($value, self::charset, $charset);
						}

						// And store the charset values for future reference
						$ipn_data['charset'] = self::charset;
						$ipn_data['charset_original'] = $charset;
				}
		}
}
