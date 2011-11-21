<?php

/**
 * Formularklasse zur einfachen Erzeugung eines Formulars mit mini Templatesystem
 *
 * @created     2008-02-29
 * @modified    2011-02-22
 * @version     0.7    
 * @author      Manuel Bieh
 * 
 * @depends     Modules_Session
 */
class Modules_Form {

	public $append;
	public $errors;
	public $errorlist;
	public $method;
	public $form;
	public $formaction;
	public $formid;
	public $sendname;
	public $validation;
	protected $delimiter;
	protected $IDs = array();
	protected $marker;

	/**
	 * Konstruktor. Erwartet momentan noch ein Templatefile (.html)
	 *
	 * @param    string    Dateiname inkl. Pfadangabe zum Template-File
	 * @param    string    Optionale ID für das Formular
	 */
	public function __construct($tpl='', $formid='') {

		$this->formid = htmlspecialchars($formid);
		$this->method = 'POST';

		$this->delimiter[0] = '[[';
		$this->delimiter[1] = ']]';

		$this->sendButton(array('name'=>'send', 'value'=>_('Abschicken')));
		$this->addSend('save');

		$this->__token = md5(mt_rand(0, 9999999));

		// GLOBALS === BAD!!		
		$this->app = $GLOBALS['app'] instanceof Application_Base ? $GLOBALS['app'] : new Application_Base();
		$this->app->extensions()->registerObservers($this);

		$oldToken = Modules_Session::getInstance()->getVar('form__token[' . md5($this->getRequestURI()) . ']');
		$oldToken = $oldToken[0];

		Modules_Session::getInstance()->setVar('form__token[' . md5($this->getRequestURI()) . ']', 
			array(
				0=>$this->__token,
				1=>$oldToken
			)
		);

		$this->loadTemplate($tpl);

	}

	public function loadTemplate($tpl='') {

		if($tpl !== '') {

			$tplFile = $this->app->getPath($tpl);

			if($tplFile !== false) {
				ob_start();
				include $tplFile;
				$this->form = ob_get_contents();
				ob_end_clean();
			} else {
				$this->form = $tpl;
			}

		} else {

			$this->form = '';

		}

	}

	// deprecated: use addValidation in the future
	public function setValidation($validation) {
		$this->validation[] = $validation;
	}

	public function addValidation($validation) {
		$this->validation[] = $validation;
	}

/*
	public function __set($member, $value) {
		$this->$member = $value;
	}
*/

	/**
	 * Erzeugt ein einfaches Inputfeld mit den Attributen aus $attrib
	 *
	 * @param    array     Array in der Form 'attribut'=>'wert'. Also z.B. array('type'=>'text', 'name'=>'email')
	 * @param    bool      Flag ob das Feld nach dem Abschicken wieder vorausgefüllt sein soll. Nicht sinnvoll bspw. bei Inputs vom Typ 'password'
	 * @return   string    Erzeugtes Inputfeld mit den in $attrib übergebenen Daten
	 */
	public function input(array $attrib, $prefilled=true) {

		$attrib['type'] = (!empty($attrib['type'])) ? htmlspecialchars($attrib['type'], ENT_NOQUOTES) : 'text';

		if(empty($attrib['name'])) {
			throw new Exception(_('Unbenanntes Formularelement gefunden.'));
		} else {
			$attrib['name'] = htmlspecialchars($attrib['name']);
		}
		$attrib['id'] = (!empty($attrib['id'])) ? htmlspecialchars($attrib['id'], ENT_NOQUOTES) : NULL;

		$noVal = array('checkbox','radio');
		if( (!in_array($attrib['type'], $noVal)) && $prefilled==true ) {
			$attrib['value'] = (isset($attrib['value']) && ($attrib['value'] != '') && ($this->isSent() != true)) ? 
				htmlspecialchars($attrib['value'], ENT_NOQUOTES, 'UTF-8') : 
				htmlspecialchars($this->valueOf($attrib['name']), ENT_NOQUOTES, 'UTF-8');
			//var_dump($this->valueOf($attrib['name']));
		} else {
		// Bedarf hier ggf. einer Überarbeitung. Bei Submit-Buttons die nicht 'send' heißen klappts nicht.
		// Generell Send-Button bzw isSent() Methode überdenken
		// -- 2011-02-22 --
		// ist das immer noch so? 

			if($this->isSent()) {
				if($this->getCheckedStatus($attrib) === true || (isset($attrib['checked']) && $attrib['checked'] == 'checked') ) {
					$attrib['checked'] = 'checked';
				} else {
					unset($attrib['checked']);
				}
			}
		}

		if(!isset($attrib['value'])) {
			$attrib['value'] = NULL;
		}

		$exclude = array('type', 'name', 'id', 'value');
		$d = $this->getAttributeList($attrib, $exclude);

		return sprintf('<input type="%s" name="%s" id="%s" value="%s" %s/>', $attrib['type'], $attrib['name'], $attrib['id'], $attrib['value'], $d);

	}


	/**
	 * Erzeugt eine einfache Textarea mit den Attributen aus $attrib
	 *
	 * @param    array     Array in der Form 'attribut'=>'wert'. Also z.B. array('name'=>'nachricht')
	 * @param    string    Erzeugte Textarea mit den in $attrib übergebenen Daten
	 */
	public function textarea(array $attrib) {

		if(empty($attrib['name'])) {
			throw new Exception(_('Unbenanntes Formularelement gefunden.'));
		} else {
			$attrib['name'] = htmlspecialchars($attrib['name']);
		}
		$attrib['id'] = (!empty($attrib['id'])) ? htmlspecialchars($attrib['id']) : NULL;
		$attrib['rows'] = (!empty($attrib['rows'])) ? (int) $attrib['rows'] : 6;
		$attrib['cols'] = (!empty($attrib['cols'])) ? (int) $attrib['cols'] : 30;
		$attrib['value'] = (isset($attrib['value']) && ($this->isSent() != true)) ? htmlspecialchars($attrib['value']) : htmlspecialchars($this->valueOf($attrib['name']));

		$exclude = array('name', 'id', 'value', 'rows', 'cols');
		$d = $this->getAttributeList($attrib, $exclude);

		return sprintf('<textarea name="%s" id="%s" rows="%s" cols="%s" %s>%s</textarea>', $attrib['name'], $attrib['id'], $attrib['rows'], $attrib['cols'], $d, $attrib['value']);

	}


	/**
	 * Erzeugt eine Auswahlliste
	 *
	 * @param    array    Attributliste als Array in der Form ('attribut'=>'wert')
	 * @param    mixed    array / string: Optionsfelder. Können als Array ('value'=>'label') oder string (<option value="">label</option>) übergeben werden    
	 * @param    mixed    array / string: Die als aktiv markierten Optionsfelder
	 * @return   string   Erzeugte Selectliste
	 */
	public function select(array $attrib, $options, $active=array()) {

		if(!isset($options)) {
			$options = array();
		}

		if(empty($attrib['name'])) {
			throw new Exception(_('Unbenanntes Formularelement gefunden.'));
		} else {
			$attrib['name'] = htmlspecialchars($attrib['name']);
		}
		$attrib['id'] = (!empty($attrib['id'])) ? htmlspecialchars($attrib['id']) : NULL;

		if($this->isSent()) {
			$active = $this->valueOf($attrib['name']);
		}

		$exclude = array('name', 'id');
		$d = $this->getAttributeList($attrib, $exclude);

		$options = (is_array($options)) ? $this->optionsFromArray($options, $active) : $options;

		return sprintf('<select name="%s" id="%s" %s>%s</select>', $attrib['name'], $attrib['id'], $d, $options);

	}



	/**
	 * Creates a HTML5 Datalist
	 *
	 * @param    array    attribute list as array ('attribute'=>'value')
	 * @param    mixed    array/string: Options. Can be specified as array ('value'=>'label') or string (<option value="">label</option>)
	 * @return   string   Generated list
	 */
	public function datalist(array $attrib, $options) {

		if(!isset($options)) {
			$options = array();
		}

		if(empty($attrib['name'])) {
			throw new Exception(__('Found unknown form element.'));
		} else {
			$attrib['name'] = htmlspecialchars($attrib['name']);
		}
		$attrib['id'] = (!empty($attrib['id'])) ? htmlspecialchars($attrib['id']) : NULL;

		if($this->isSent()) {
			$active = $this->valueOf($attrib['name']);
		}

		$exclude = array('name', 'id');
		$d = $this->getAttributeList($attrib, $exclude);

		$options = (is_array($options)) ? $this->optionsFromArray($options) : $options;

		return sprintf('<datalist name="%s" id="%s" %s>%s</datalist>', $attrib['name'], $attrib['id'], $d, $options);

	}


	/**
	 * Erstellt eine Auswahlliste aus einem Array mit Daten. Muss in der Form value=>label vorliegen.
	 * Bei 2D-Array wird eine Optionsliste mit Optgroups erstellt.
	 *
	 * @param    array    Optionsfelder. Können als Array ('value'=>'label') oder string (<option value="">label</option>) übergeben werden    
	 * @param    mixed    Als aktiv markierte Optionsfelder
	 * @return   string   Optionsliste in der Form <option value....>...</option><option....
	 */
	protected function optionsFromArray(array $data, $active='') {
		if(is_array($data)) {
			$out = NULL;
			foreach($data AS $optgroup => $option) {
				if(is_array($option)) {
					$out .= sprintf('<optgroup label="%s">', $optgroup);
					foreach($option AS $value => $label) {
						if(is_array($active)) {
							$selected = (in_array($value, $active)) ? ' selected="selected"' : '';
						} else {
							$selected = ($value == $active) ? ' selected="selected"' : '';
						}
						$out .= sprintf('<option value="%s"%s%s>%s</option>', htmlspecialchars($value), $selected, ' title="'.htmlspecialchars($label).'"', htmlspecialchars($label));
					}
					$out .= '</optgroup>';
				} else {
					if(is_array($active)) {
						$selected = (in_array($optgroup, $active)) ? ' selected="selected"':'';
					} else {
						$selected = ($optgroup == $active) ? ' selected="selected"' : '';
					}
					$out .= sprintf('<option value="%s"%s%s>%s</option>', htmlspecialchars($optgroup), $selected, ' title="'.htmlspecialchars($option).'"', htmlspecialchars($option));
				}
			}
		}

		return $out;

	}


	/**
	 * Prüft ob eine Checkbox oder Radiobutton ausgewählt wurde beim abschicken.
	 *
	 * @param    array     Attributarray des zu prüfenden Feldes
	 * @return   bool      true wenn Feld ausgewählt wurde, false wenn nicht.
	 */
	protected function getCheckedStatus(array $attrib) {

		if($attrib['type'] === 'checkbox') {
			if(is_array($this->valueOf($attrib['name']))) {
				if(in_array($attrib['value'], $this->valueOf($attrib['name']))) {
					return true;
				} else {
					return false;
				}
			} else {
				if($this->valueOf($attrib['name']) != '') {
					return true;
				}
			}
		} else if($attrib['type'] === 'radio') {
			if($attrib['value'] === $this->valueOf($attrib['name'])) {
				return true;
			} else {
				return false;
			}
		}

	}


	/**
	 * Erzeugt eine Liste mit Attributen aus einem Array
	 *
	 * @param    array     Array mit Attributen in der Form 'attribut'=>'wert'
	 * @param    array     Attribute die nicht in der Liste berücksichtigt werden sollen
	 * @return   string    Attributlist in der Form attribut="wert" attribut2="wert" ...
	 */
	protected function getAttributeList(array $attrib, array $exclude=array()) {

		$d = NULL;

		if(is_array($attrib)) {
			foreach($attrib AS $key => $value) {
				if(!in_array($key, $exclude)) {
					$d .= sprintf('%s="%s" ', strtolower($key), $value);
				}
			}
		} else {
			throw new Exception(__('Attributes list contained invalid data.'));
		}

		return $d;

	}


	/**
	 * Checkt ob ein Formular abgeschickt wurde.
	 *
	 * @return    bool     Gibt im Erfolgsfall true zurück ansonsten false
	 */
	public function isSent($validate=false) {

		if(is_array($this->sendname)) {

			foreach($this->sendname AS $button) {
				if(
					isset($GLOBALS['_'.strtoupper($this->method)][$button]) && 
					$GLOBALS['_'.strtoupper($this->method)][$button] === $this->valueOf($button) &&
					( 
						($validate == true && ( (isset($this->validation) && $this->errors() == false) || !isset($this->validation)) ) ||
						$validate == false
					)
				) {
					if($this->alreadySent() == true) {
						return false;
					} else {
						return true;
					}
				}
			}

			return false;

		} else {

			if(
				isset($GLOBALS['_'.strtoupper($this->method)][$button]) && 
				$GLOBALS['_'.strtoupper($this->method)][$this->sendname] === $this->valueOf($button) &&
				( 
						($validate == true && ( (isset($this->validation) && $this->errors() == false) || !isset($this->validation)) ) ||
					$validate == false
				)
			) {

				if($this->alreadySent() == true) {
					return false;
				} else {
					return true;
				}

			} else {
				return false;
			}

		}

	}


	public function alreadySent() {

		$formToken = Modules_Session::getInstance()->getVar('form__token[' . md5($this->getRequestURI()) . ']');
		if($this->valueOf('__token') != $formToken[1]) {
			$this->addError(__('The submitted form is no longer valid.'));
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Checkt ob ein Formular abgeschickt wurde ohne das Fehlermeldungen aufgetreten sind.
	 *
	 * @return    bool     Gibt im Erfolgsfall true zurück ansonsten false
	 */
	public function reallySent() {

		if(isset($this->validation)) {

			if($this->isSent() && $this->validation->errors() === NULL) {
				return true;
			} else {
				return false;
			}

		}

	}

	/**
	 * Prüft eine hochgeladene Datei auf eine maximale Dateigröße
	 *
	 * @param    string    Name des zu prüfenden Feldes
	 * @param    integer   Minimale Größe
	 * @param    integer   Maximale Größe
	 * @param    string    Fehlermeldung 
	 */
	function checkFileSize($field, $minSize=0, $maxSize=0, $errmsg=true) {
		if($this->isSent()) {
			if($this->fileArray($field, 'size') < $minSize) {
				$this->addError($errmsg);
			} else if ($this->fileArray($field, 'size') > $maxSize && $maxSize != 0) {
				$this->addError($errmsg);
			}
		}
	}

	/**
	 * Prüft eine hochgeladene Datei auf den MIME-Type
	 *
	 * @param    string    Name des zu prüfenden Feldes
	 * @param    mixed     Entweder Array mit erlaubten Werten oder String falls nur ein Wert erlaubt
	 * @param    string    Fehlermeldung 
	 */
	function checkFileType($field, $allowed, $errmsg=true) {
		if($this->isSent()) {
			if(is_array($allowed)) {
				if(!in_array($this->fileArray(strtolower($field), 'type'), $allowed)) {
					$this->addError($errmsg);
				}
			} else if(is_string($allowed)) {
				if($this->fileArray(strtolower($field), 'type') != $allowed) {
					$this->addError($errmsg);
				}
			} else {
				throw new Exception(__('An error occured while checking a file type'));
			}
		}
	}

	/**
	 * Liefert den Wert eines Feldes der entsprechenden Methode zurück.
	 * Also bei $this->method = 'post' ists $_POST['feldname'], bei get eben $_GET['feldname']
	 * Mehrdimensionale Arrays können über [feldname][level2][level3]... angesprochen werden.
	 *
	 * @param    string    feldname
	 * @return   bool/array/string
	 */
/*
// 2011-02-22 NEUE VERSION, BUGFIXEN!!
	public function valueOf($field) {

		$field = str_replace('[]', '', $field);
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
*/

	public function value($field) {
		return $this->valueOf($field);
	}

	public function valueOf($field) {

	#echo '<hr />';
	#echo $field;
	#echo '<br />';
		$field = str_replace('[]', '', $field);
	#echo $field;
	#echo '<br />';
		$field_array = preg_split("!\[([^\]\[]+)\]!", $field, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE); 
	#print_r($field_array);
	#echo '<br />';
		// ineffizientes kopieren aller daten
		$data = $GLOBALS['_' . strtoupper($this->method)];
		/*
		if($this->method == 'get') {
			$data = $_GET;
		} else {
			$data = $_POST;
		}
		*/

		foreach ($field_array as $num => $index) {
			if (isset($data[$index])) {
				$data = $data[$index];
				#$data = trim(preg_replace("/\s+/", " ", $data[$index]));
			} else { 
				// daten nicht gefunden
				return false;
			}
		}
	#var_dump( $data );
	#echo '<hr />';
		return $data;

	}

	public function addValue($key, $value) {

		$GLOBALS['_' . strtoupper($this->method)][$key] = $value;

	}

	/**
	 * @param    string    feldname
	 * @return   mixed
	 */
	public function fileArray($field, $index=NULL) {

		$field = str_replace('[]', '', $field);

		if(isset($_FILES[$field]) && $_FILES[$field]['error'] > 0) {

			return $_FILES[$field]['error'];

		} else {

			$fieldArray = preg_split("!\[([^\]\[]+)\]!", $field, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE); 

			if($index !== NULL && in_array($index, array('name', 'type', 'size', 'tmp_name', 'error'))) {

				if($fieldArray[1]) {
					return $_FILES[$fieldArray[0]][$index][$fieldArray[1]];
				} else {
					return $_FILES[$fieldArray[0]][$index];
				}

			} else {

				return $_FILES[$fieldArray[0]];

			}

		}

	}


	public function fileExt($field) {
		$ext = explode('.', $this->fileArray($field, 'name'));
		if(is_array($ext)) {
			return $ext[count($ext)-1];
		} else {
			return false;
		}
	}


	/**
	 * Fügt eine eigene Fehlermeldung in den Fehlerarray hinzu
	 *
	 * @param    string    Fehlermeldung
	 */
	public function addError($errmsg=true) {

		if(isset($this->validation) && is_array($this->validation)) {
			$this->validation[0]->addError($errmsg);
		} else {
			$this->errors[] = $errmsg;
		}

		/*
		if(isset($this->validation) && $this->validation instanceof Modules_Validation_Abstract) {
			$this->validation->addError($errmsg);
		}
		*/
	}

	public function getErrors($wrap='<ul class="errors">%s</ul>', $pattern='<li>%s</li>') {

		if(is_array($this->validation)) {

			foreach($this->validation AS $valObject) {
				$errors = $valObject->getErrors();
				if(is_array($errors)) {
					foreach($errors AS $error) {
						$this->errors[] = $error;
					}
				}
			}
			if(is_array($this->errors)) {
				$this->errors = array_unique($this->errors);
			}
			return $this->errors;

		} else {
			return $this->errors;
		}

		return false;


	}

	/**
	 * Gibt eine Liste mit Fehlern aus $this->errors zurück
	 *
	 * @param    string    Wrap aller Fehlermeldungen. %s als Platzhalter (default: <ul class="errors>%s</ul>)
	 * @param    string    Muster für einen einzelnen Fehler. %s als Platzhalter (default: <li>%s</li>)
	 * @return   string    Wrap + Fehler
	 */
	public function errors($wrap='<ul class="errors">%s</ul>', $pattern='<li>%s</li>') {

		$e = $this->getErrors();
		if(is_array($e)) {
			$e = array_unique($e);
		}
	
		if($e != false) {

			$errors = '';

			foreach($e AS $error) {
				$errors .= sprintf($pattern, $error);
			}

			return $this->errorlist = sprintf($wrap, $errors);

		}

		return false;

/*
		if(!empty($this->validation->errors)) {

			$e = NULL;
			foreach($this->validation->errors AS $error) {
				if($error !== true) {
					$e .= sprintf($pattern, $error);
				}
			}

			return $this->errorlist = sprintf($wrap, $e);

		} else {

			return false;

		}
*/

	}


	/**
	 * Weist einem Marker (üblicherweise [[MARKER]]) spezielle Daten (bspw. Input-Felder) zu.
	 *
	 * @param    string    Wrap aller Fehlermeldungen. %s als Platzhalter (default: <ul class="errors>%s</ul>)
	 * @param    string    Muster für einen einzelnen Fehler. %s als Platzhalter (default: <li>%s</li>)
	 * @return   string    Wrap + Fehler
	 */
	public function assign($marker, $data) {
		$this->form = str_replace($this->delimiter[0] . $marker . $this->delimiter[1], $data, $this->form);
		$this->marker[$marker] = $data;
	}

	public function append($content) {
		$this->append .= $content;
	}

	public function addSend($name) {
		$this->sendname[] = $name;
	}


	/**
	 * Fügt automatisch einen Senden-Button an das Ende des Formulars
	 *
	 * @param    string    zu prüfendes Feld
	 * @param    string    String mit dem verglichen werden soll
	 * @param    string    Fehlermeldung 
	 * @param    bool      Prüft zusätzlich auf Typengleichheit (=== Operator) 
	 */
	public function sendButton(array $attrib, $wrap='<p>%s</p>') {

		$attrib['name'] = (!empty($attrib['name'])) ? htmlspecialchars($attrib['name']) : 'send';
		$attrib['value'] = (!empty($attrib['value'])) ? htmlspecialchars($attrib['value']) : _('Abschicken');
		$attrib['class'] = (!empty($attrib['class'])) ? htmlspecialchars($attrib['class']) : 'button';
		$attrib['type'] = (!empty($attrib['type'])) ? htmlspecialchars($attrib['type']) : 'submit';

		#$this->sendname = $attrib['name'];
		$this->addSend($attrib['name']);

		$exclude = array('name', 'value', 'class', 'type');
		$d = $this->getAttributeList($attrib, $exclude);

		$this->sendbutton = sprintf('<input type="%s" name="%s" value="%s" class="%s" %s/>', $attrib['type'], $attrib['name'], $attrib['value'], $attrib['class'], $d);
		$this->sendbutton = sprintf($wrap, $this->sendbutton);

	}


	public function getRequestURI() {
		$request = new Modules_Request_HTTP();
		$requestURI = $request->removeQueryParams(htmlspecialchars($_SERVER['REQUEST_URI'], ENT_NOQUOTES, 'UTF-8'), array('ajax', '_ajax', '__ajax'));
		return $requestURI;
	}

	public function render() {

		if($this->form === false) {
			$this->loadTemplate($this->form);
		#	throw new Exception(_('Formulartemplate konnte nicht gefunden werden.'));
		}

		if(is_array($this->marker)) {
			foreach($this->marker AS $marker => $value) {
				$this->form = str_replace($this->delimiter[0] . $marker . $this->delimiter[1], $value, $this->form);
			}
		}

		$this->assign('$SEND', $this->sendbutton);
		if( (isset($this->validation) && !empty($this->validation)) || !empty($this->errors) ) {
			//$this->validation->getErrors();
			$this->assign('$ERRORS', (isset($this->errorlist) && $this->errorlist != '') ? $this->errorlist : $this->errors());
		} else {
			$this->assign('$ERRORS', '');
		}

		/**/
		# hier fehlt noch eine funktion zum entfernen von nicht zugeordneten markern
		/**/

		if($this->method == 'get') {

				$action = parse_url($this->formaction);
				if(!empty($action['query'])) {

					parse_str($action['query'], $params);
					if(is_array($params)) {

						foreach($params AS $fieldname => $value) {
							$actionGetParams .= $this->input(array('type'=>'hidden', 'name'=>$fieldname, 'value'=>$value));
						}

					}

				}

		} else {

			$requestURI = $this->getRequestURI();

			$actionGetParams = false;
			if(!$this->formaction) {
				$this->formaction = $requestURI;
			}

		}

		$d = (!empty($this->formid)) ? ' id="' . $this->formid . '" ':'';
		$e = (!empty($this->formid)) ? ' name="' . $this->formid . '" ':'';

		return sprintf('
			<form method="%s" enctype="multipart/form-data" action="%s"%s%s>%s%s%s
			<input type="hidden" name="__token" value="' . $this->__token . '" />
			</form>', 
			strtolower($this->method), $this->formaction, $d, $e, $this->form, $actionGetParams, $this->append
		);

	}

}

?>