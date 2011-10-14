<?php

interface Application_Observable {

	public function addObserver($observer);

	public function notify($state, $additionalParams=NULL);

}