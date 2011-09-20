<?php

class Modules_Validation_Basechecks extends Modules_Validation_Abstract {

	public $object = __CLASS__;
	public $errors = array();

	/**
	 * Prüft ob ein Feld mindestens eine bestimmte Anzahl an Zeichen enthält
	 * @param    string    zu prüfendes Feld
	 * @param    integer   Mindestlänge des zu prüfenden Feldes
	 * @param    string    Fehlermeldung 
	 */
	public function length($value, $min, $max, $error=true) {

		if( (strlen($value) < $min) || (strlen($value) > $max && $max != 0) ) {
			$this->addError($error);
			return false;
		}

	}


	/**
	 * Prüft ob eine E-Mail-Adresse gültig ist
	 * @param    string    zu prüfendes Feld
	 * @param    string    Fehlermeldung 
	 */
	public function email($value, $error=true) {

		if(preg_match("|^((?:(?:(?:\w[\.\-\+]?)*)\w)+)\@((?:(?:(?:\w[\.\-\+]?){0,62})\w)+)\.(\w{2,6})$|", $value) == 0) {
			$this->addError($error);
			return false;
		}

	}


	/**
	 * Prüft ob ein Feld einen entsprechenden Wert hat
	 * @param    string    zu prüfender Wert
	 * @param    string    String mit dem verglichen werden soll
	 * @param    string    Fehlermeldung 
	 * @param    bool      Prüft zusätzlich auf Typengleichheit (=== Operator) 
	 */
	public function compare($value, $value2, $error=true, $strict=false) {

		if($strict == false) {

			if($value != $value2) {
				$this->addError($error);
				return false;
			}

		} else {

			if($value !== $value2) {
				$this->addError($error);
				return false;
			}

		}

	}


	/**
	 * Prüft ob ein Feld gegen einen regulären Ausdruck validiert
	 * @param    string    zu prüfendes Feld
	 * @param    string    Regulärer Ausdruck der erfüllt sein muss
	 * @param    string    Fehlermeldung 
	 */
	public function regex($value, $expression, $error=true) {

		if(preg_match($expression, $value) == 0) {
			$this->addError($error);
			return false;
		}

	}


	/**
	 * Prüft ob der Value in einem Array enthalten ist
	 * @param    string    zu prüfendes Feld
	 * @param    array     Array mit Werten
	 * @param    string    Fehlermeldung 
	 */
	public function inArray($value, $array, $error=true) {

		if(is_array($array)) {

			if(!in_array($value, $array)) {
				$this->addError($error);
				return false;
			}

		} else {
			// Um hier doppelten Code zu sparen sollte man checken ob 
			// json_decode() true ist und sich selbst aufrufen.
			$array = json_decode($array, true);
			if(is_array($array)) {
				if(!in_array($value, $array)) {
					$this->addError($error);
					return false;
				}

			}

		}

	}


	/**
	 * Prüft ob ein Feld mindestens eine bestimmte Anzahl an Zeichen enthält
	 * @param    integer   zu prüfendes Feld
	 * @param    integer   Mindestwert des zu prüfenden Feldes
	 * @param    integer   Maximalwert des zu prüfenden Feldes
	 * @param    string    Fehlermeldung 
	 */
	public function range($value, $min, $max, $error=true) {

		#if( (strlen($value) < $min) || (strlen($value) > $max && $max != 0) ) {
		if( ($value < $min) || ($value > $max) ) {
			$this->addError($error);
			return false;
		}

	}

}


