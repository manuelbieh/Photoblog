<?php

class Modules_Validation_Basechecks extends Modules_Validation_Abstract {

	public $object = __CLASS__;
	public $errors = array();

	/**
	 * Pr�ft ob ein Feld mindestens eine bestimmte Anzahl an Zeichen enth�lt
	 * @param    string    zu pr�fendes Feld
	 * @param    integer   Mindestl�nge des zu pr�fenden Feldes
	 * @param    string    Fehlermeldung 
	 */
	public function length($value, $min, $max, $error=true) {

		if( (strlen($value) < $min) || (strlen($value) > $max && $max != 0) ) {
			$this->addError($error);
			return false;
		}

	}


	/**
	 * Pr�ft ob eine E-Mail-Adresse g�ltig ist
	 * @param    string    zu pr�fendes Feld
	 * @param    string    Fehlermeldung 
	 */
	public function email($value, $error=true) {

		if(preg_match("|^((?:(?:(?:\w[\.\-\+]?)*)\w)+)\@((?:(?:(?:\w[\.\-\+]?){0,62})\w)+)\.(\w{2,6})$|", $value) == 0) {
			$this->addError($error);
			return false;
		}

	}


	/**
	 * Pr�ft ob ein Feld einen entsprechenden Wert hat
	 * @param    string    zu pr�fender Wert
	 * @param    string    String mit dem verglichen werden soll
	 * @param    string    Fehlermeldung 
	 * @param    bool      Pr�ft zus�tzlich auf Typengleichheit (=== Operator) 
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
	 * Pr�ft ob ein Feld gegen einen regul�ren Ausdruck validiert
	 * @param    string    zu pr�fendes Feld
	 * @param    string    Regul�rer Ausdruck der erf�llt sein muss
	 * @param    string    Fehlermeldung 
	 */
	public function regex($value, $expression, $error=true) {

		if(preg_match($expression, $value) == 0) {
			$this->addError($error);
			return false;
		}

	}


	/**
	 * Pr�ft ob der Value in einem Array enthalten ist
	 * @param    string    zu pr�fendes Feld
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
	 * Pr�ft ob ein Feld mindestens eine bestimmte Anzahl an Zeichen enth�lt
	 * @param    integer   zu pr�fendes Feld
	 * @param    integer   Mindestwert des zu pr�fenden Feldes
	 * @param    integer   Maximalwert des zu pr�fenden Feldes
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


