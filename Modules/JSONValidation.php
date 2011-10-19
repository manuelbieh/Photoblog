<?php

class Modules_JSONValidation extends Modules_Validation_Abstract {

	public $errors = array();
	protected $checks;
	protected $checked = false;
	protected $method = 'POST';
	protected $performAllChecks = true;
	protected $sendId;
	protected $validations;

	public function __construct($config=NULL, $valObject=NULL) {

		$valObject = $this->getValidationObject($valObject);

		if($config !== NULL) {
			$this->addCheck($config, $valObject);
		}

	}

	public function addCheck($config, $valObject='Modules_Validation_Basechecks') {

		$valObject = $this->getValidationObject($valObject);

		$this->checks[] = array('config'=>$config, 'validation'=>$valObject);
		$this->checked = false;
		return $this;
	}

	public function setConfigByJSON($json) {

		if(is_array($json)) {
			foreach($json AS $index => $data) {
				$this->addCheck($data['config'], $data['validation']);
			}
		}
		return $this;

	}

	public function setConfigByJSONFile($json) {

		$json = Application_Base::getPath($json);

		ob_start();
		include $json;
		$json = ob_get_contents();
		ob_end_clean();
		$config = json_decode($json, true);
		$this->setConfigByJSON($config);
		return $this;

	}

	public function setMethod($method='POST') {
		$this->method = in_array(strtoupper($method), array('POST', 'GET')) ? $method : 'POST';
		return $this;
	}

	public function setSendId($submitName, $value='') {
		$this->sendId = array(0=>$submitName, 1=>$value);
		return $this;
	}

	public function runChecks($ignoreSend=false) {

		$this->checked = true;

		if(
			$ignoreSend == true ||
			/*($ignoreSend == false && (($GLOBALS['_' . $this->method][$this->sendId[0]] === '' && isset($GLOBALS['_' . $this->method][$this->sendId[0]])) 
			|| ($GLOBALS['_' . $this->method][$this->sendId[0]] === $this->sendId[1])
			|| $GLOBALS['_' . $this->method])
			)
			*/
			($ignoreSend == false && $GLOBALS['_' . $this->method])
		) {

			if(is_array($this->checks)) {

				foreach($this->checks AS $count => $data) {

					foreach($data['config'] AS $field => $validation) {

						foreach($validation AS $check => $params) {

							if(isset($params['error'])) {
								$params['error'] = __($params['error']);
							}
							array_unshift($params, $this->getValue($field));
							if(is_callable(array($data['validation'], $check))) {

								$valObject = new $data['validation'];
								$result = call_user_func_array(array($valObject, $check), $params);
								$this->validations[] = $valObject;

								if($this->performAllChecks == false && $result === false) {
									//var_dump('RETURNING ERRORS');
									return $valObject->getErrors();
									// Should be an array with errors
								}

							}

							$this->errors = array_unique(array_merge($this->errors, $valObject->getErrors()));

						}


					}

				}

			}

		}

		return $this;

	}

	public function getErrors() {
		return $this->errors();
	}

	public function errors() {

		$errors = empty($this->errors) ? false : $this->errors;
		if($this->checked == true) {
			return $errors;
		} else {
			$this->runChecks();
			return $this->errors();
		}

	}


	public function getJSONConfig() {

		return json_encode($this->checks);

	}

	public function getFieldJSON($field) {

		$ret = array();
		if(is_array($this->checks)) {

			foreach($this->checks AS $count => $data) {

				foreach($data['config'] AS $fieldName => $fieldData) {

					if($field == $fieldName) {
						$ret = array_merge($ret, $fieldData);
					}

				}

			}

		}

		$json[$field] = $ret;
		return $json;

	}

	protected function getValidationObject($valObject=NULL) {

		if(is_object($valObject)) {

			return $valObject;

		} else if($valObject == NULL) {

			$valObject = new Modules_Validation_Basechecks;

		} else if(is_string($valObject['object'])) {

			if(class_exists($valObject['object'])) {
				$valObject = new $valObject['object']();
			}

		}

		return $valObject;

	}

	protected function getValue($field) {

		$fieldArray = preg_split("!\[([^\]\[]+)\]!", $field, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE); 

		foreach ($fieldArray as $num => $index) {
			$data[$index] = !isset($data) ? $GLOBALS['_' . strtoupper($this->method)][$index] : $data[$index];
			if (isset($data[$index])) {
				$data = $data[$index];
			} else {
				return false;
			}
		}

		return $data;

	}

}

