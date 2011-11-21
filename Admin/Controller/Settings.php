<?php

class Admin_Controller_Settings extends Controller_Frontend {

	public function __construct($app=NULL) {

		$app->extensions()->registerObservers($this);

		$this->app = $app;

		$this->view		= $this->app->objectManager->get('Application_View');
		$this->access	= $this->app->objectManager->get('Admin_Application_Access');

		if(!isset($_GET['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}
	
	}

	public function edit($section) {

		if($this->access->check(__METHOD__, $section)) {

			$form = new Modules_Form("templates/settings/settings.form.html");

			if($form->isSent()) {

				$settings = new Modules_XML();
				$settings->load(Application_Settings::getFile(1));

				$checkboxes = $settings->XPath()->query("//settings/" . $section . "//*[@type='checkbox']");
				if($checkboxes->length > 0) {
					foreach($checkboxes AS $checkbox) {
						$checkbox->nodeValue = 0;
					}
				}

				foreach($form->valueOf('data') AS $xpath => $value) {

					$node = $settings->XPath()->query("//settings/" . $section . $xpath);
					$childnode = $node->item(0);
					$childnode->nodeValue = $value;

				}

				$subview = $this->app->createView();

				if($settings->save(Application_Settings::getFile(1)) !== false) {

					$subview->loadHTML('templates/settings/edit.success.html');
					$this->view->addSubview('main', $subview);

				} else {

					$subview->loadHTML('templates/settings/edit.error.html');
					$this->view->addSubview('main', $subview);

				}


			} else {

				$form->assign('content', $this->getSettingsXML($section));

				$subview = $this->app->createView();
				$subview->loadHTML('templates/settings/settings.html');

				$subview->assign('form', $form->render());
				$subview->assign('headline', 'Settings');

				$this->view->addSubview('main', $subview);

			}

		} else {

			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error403());

		}

	}

	private function getSettingsXML($section) {

		$settings = new Modules_XML();
		$settings->load(Application_Settings::getFile(1));
		$nodes = $settings->XPath()->query("//settings/" . $section . "/*");

		if($nodes->length > 0) {

			$xmlForm = new Modules_XMLSimpleForm();

			foreach($nodes AS $node) {

				$form .= $xmlForm->renderNode($node, $settings);

			}

		}

		return $form;

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

}