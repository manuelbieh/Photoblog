<?php

class Admin_Controller_Settings extends Controller_Frontend implements Application_Observable {

	public function __construct($app=NULL) {

		$app->extensions()->registerObservers($this);

		$this->app = $app;

		$this->view		= new Application_View();
		$this->access	= $this->app->objectManager->get('Admin_Application_Access');

		if(!isset($_POST['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

		$navi = new Application_View();

		$navi->loadHTML("templates/main/navi.html");
		$this->view->addSubview('navi', $navi);

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			Application_Base::go('Login');
			exit;
		}
	
	}

	public function edit($section) {

		if($this->access->check(__METHOD__, $section)) {

			$form = new Modules_Form("templates/settings/settings.form.html");

			if($form->isSent()) {

				$settings = new Modules_XML();
				$settings->load(Application_Settings::getFile(1));

				$checkboxes = $settings->XPath()->query("//settings/" . $section . "//*[@type='checkbox']");
				foreach($checkboxes AS $checkbox) {
					$checkbox->nodeValue = 0;
				}

				foreach($form->valueOf('data') AS $xpath => $value) {

					$node = $settings->XPath()->query("//settings/" . $section . $xpath);
					$childnode = $node->item(0);
					$childnode->nodeValue = $value;

				}

				$settings->save(Application_Settings::getFile(1));

			}

			$form->assign('content', $this->getSettingsXML($section));

			$subview = new Application_View();
			$subview->loadHTML('templates/settings/settings.html');
			$subview->assign('form', $form->render());
			$subview->assign('headline', 'Settings');

			$this->view->addSubview('main', $subview);

		}

	}

	private function getSettingsXML($section) {

		$settings = new Modules_XML();
		$settings->load(Application_Settings::getFile(1));
		$nodes = $settings->XPath()->query("//settings/" . $section . "/*");

		if($nodes->length > 0) {

			foreach($nodes AS $node) {

				$form .= $this->renderNode($node, $settings);

			}

		}

		return $form;

	}

	private function renderNode($node, $settings, $xpath='') {

		$data = '';
		$children = $settings->XPath()->query("./*", $node);

		if($node->getAttribute('type') == '' && $children->length > 0) {
			$node->setAttribute('type', 'group');
		}

		$label = $node->getAttribute('label') != '' ? $node->getAttribute('label') : ucfirst($node->localName);
		$label = __($label);

		$xpath .= '/' . $node->localName;

		switch($node->getAttribute('type')) {

			case 'group':

				$data .= '<fieldset><legend>';
				$data .= $label;
				$data .= '</legend>';
				if($children->length > 0) {
					foreach($children AS $childNode) {
						$data .= $this->renderNode($childNode, $settings, $xpath);
					}
				}
				$data .= '</fieldset>';
				break;


			case 'email':

				$data .= '<p>';
				$data .= '<label for="data[' . $xpath . ']">' . $label . '</label>';
				$data .= '<input type="email" id="data[' . $xpath . ']" name="data[' . $xpath . ']" value="' . $node->textContent . '" />';
				$data .= '</p>';
				break;


			case 'checkbox':

				$data .= '<p>';
				$data .= '<label for="data[' . $xpath . ']">';
				$checked = ($node->textContent == 1) ? ' checked="checked" ' : '';
				$data .= '<input type="checkbox" id="data[' . $xpath . ']" name="data[' . $xpath . ']" value="1" ' . $checked . ' /> ';
				$data .= $label . '</label>';
				$data .= $node->getAttribute('break') == 'true' ? '<br />' : '';
				$data .= '</p>';
				break;


			case 'range':

				$data .= '<p>';
				$data .= '<label for="data[' . $xpath . ']">' . $label . '</label>';
				$data .= '<input type="range" min="' . (int) $node->getAttribute('min') . '" max="' . (int) $node->getAttribute('max') . '" id="data[' . $xpath . ']" name="data[' . $xpath . ']" value="' . $node->textContent . '" />';
				$data .= '</p>';
				break;


			case 'select':

				$data .= '<p>';
				$data .= '<label for="' . $node->localName . '">' . $label . '</label>';
				$data .= '<select name="data['.$xpath.']">';

				if($node->getAttribute('list') != '') {
					$items = explode(';', $node->getAttribute('list'));
					foreach($items AS $item) {
						$values = explode('=', $item, 2);
						$value = $values[0];
						$text = $values[1];
						$selected = $value == $node->textContent ? ' selected="selected" ' : '';
						$data .= '<option ' . $selected . ' value="' . $value . '">' . $text . '</option>';
					}
				}

				if($node->getAttribute('callback') != '') {

					preg_match("/(.*)(::|->)(.*)\((.*)?\)/Usm", $node->getAttribute('callback'), $callback);
					if(!empty($callback)) {
						$object = $callback[1];
						$method = $callback[3];
						if(isset($callback[4])) {
							$params = explode(',', $callback[4]);
							foreach($params AS $i => $param) {
								$params[$i] = trim($param, " \t'\"");
							}
						}

						if(is_callable(array($object, $method))) {
							$params = is_array($params) ? $params : array($params);
							$values = call_user_func_array(array(new $object, $method), $params);
						}

						foreach($values AS $key => $value) {
							$selected = $key == $node->textContent ? ' selected="selected" ' : '';
							$data .= '<option ' . $selected . ' value="' . $key . '">' . $value . '</option>';
						}

					}

				}
				$data .= '</select>';
				break;


			case 'text':
			default:
				$data .= '<p>';
				$data .= '<label for="data[' . $xpath . ']">' . $node->getAttribute('label') . '</label>';
				$data .= '<input type="text" id="data[' . $xpath . ']" name="data[' . $xpath . ']" value="' . $node->textContent . '" />';
				$data .= '</p>';

		}

		return $data;

	}


	private function getSettings($section) {

		$settings = new Modules_XML();
		$settings->load(Application_Settings::getFile(1));
		$nodes = $settings->XPath()->query("//settings/" . $section . "/*");

		if($nodes->length > 0) {

			foreach($nodes AS $node) {

				$children = $settings->XPath()->query("./*", $node);

				if($children->length > 0) {

					foreach($children AS $childNode) {

						$grandChildren = $settings->XPath()->query("./*", $childNode);
						if($grandChildren->length > 0) {

							foreach($grandChildren AS $grandChild) {

								$data[$node->localName][$childNode->localName][$grandChild->localName] = trim($grandChild->textContent);

							}

						} else {

							$data[$node->localName][$childNode->localName] = trim($childNode->textContent);

						}

					}

				} else {

					$data[$node->localName] = trim($node->textContent);

				}

			}

		}

		return $data;

	}

	public function addObserver($observer) {

		array_push($this->observers, $observer);

	}

	public function notify($state, $additionalParams=NULL) {

		foreach((array) $this->observers AS $obs) {

			if(method_exists($obs, $state)) {

				$obs->$state(&$this, $additionalParams);

			}

		}

	}

}