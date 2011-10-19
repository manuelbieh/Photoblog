<?php

class Controller_Frontend {

	public function __destruct() {
		if(isset($this->view) && $this->view instanceof Application_View) {
			$this->view->render(true);
		}
	}


}