<?php

class Admin_Controller_Page extends Controller_Frontend {

	public $sortFactor = 1024;

	public function __construct($app) {

		$this->app = $app;

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}

		$app->extensions()->registerObservers($this);

		$this->view				= $this->app->objectManager->get('Application_View');
		$this->access			= $this->app->objectManager->get('Admin_Application_Access');

		$this->pageGateway		= new Model_Page_Gateway_PDO($app->objectManager->get('Datastore'));
		$this->pageMapper		= new Model_Page_Mapper($this->pageGateway);

		if(!isset($_GET['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

	}

	public function view() {

		$pageData = $this->getPageStructure();

		$subview	= $this->app->createView();
		$subview->loadHTML('templates/page/view.html');

		$subview->data['pages'] = $pageData['flatArray'];
		$subview->data['nested_pages'] = $pageData['parentChild'];

		$this->view->addSubview('main', $subview);

	}

	public function create($reference_page_id=0, $where='inside') {

		$subview = $this->app->createView();

		if($this->access->check(__METHOD__)) {

			$this->form = new Modules_Form('templates/page/create.form.html');

			$reference_page = $this->pageMapper->find($reference_page_id, new Model_Page);

			if($reference_page == false) {

				// ERROR: Parent doesnt exist

			} else {

				if($this->form->isSent()) {

					$validation = new Modules_Validation_Basechecks();
					$validation->length($this->form->valueOf('data[title]'), 1, 0, __('Please enter a pagetitle'));

					$this->form->addValidation($validation);

				}

				if($this->form->isSent(true)) {

					$page = new Model_Page();

					if($where == 'inside') {

						$childPages = $this->pageMapper->findByParentId($reference_page_id);

						if(is_array($childPages) && !empty($childPages)) {
							foreach($childPages AS $count => $childPage) {
								$childPage->sort = ($count + 1) * $this->sortFactor;
							}
							$this->pageMapper->save($childPage);
						}

						$page->parent_page_id = $reference_page_id;
						$page->sort = 0;

					} else if($where == 'after' || $where == 'below') {

						$siblingPage = $this->pageMapper->find($reference_page_id, new Model_Page);

						$page->parent_page_id = $siblingPage->parent_page_id;
						// insert between refpage and following sibling
						$page->sort = $siblingPage->sort += $this->sortFactor / 2; 

					} else {
						// ERROR: Unknown position
					}

					foreach($this->form->valueOf('data') AS $col => $value) {
						$page->$col = $value;
					}

					// Remove HTML Tags, apply Extension modifications, etc
					$page->content 		= $this->parseContent($page->content); 

					$page->date_created = date('Y-m-d H:i:s');
					$page->page_id 		= NULL; // just to make sure
					$page->template 	= 'index.html'; // for now, that's it.
					$page->clean_title	= $this->getCleanTitle($page->title);


					if($this->pageMapper->save($page) != false) {

						// reorder pages
						$childPages = $this->pageMapper->findByParentId($page->parent_page_id);
						if(is_array($childPages)) {
							foreach($childPages AS $count => $childPage) {
								$childPage->sort = $count * $this->sortFactor;
								$this->pageMapper->save($childPage);
							}
						}

						$subview->loadHTML('templates/page/create.success.html');

					} else {
						$subview->loadHTML('templates/page/create.error.html');
					}

					$this->view->addSubview('main', $subview);

				} else {

					$this->view->addSubview('main', $this->form);

				}

			}

		} else { // Access denied

			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());

		}

	}

	public function edit($page_id, $inline=false) {

		$subview = $this->app->createView();

		if($this->access->check(__METHOD__)) {

			$page = $this->pageMapper->find($page_id, new Model_Page);

			$currentCleanTitle = $page->clean_title;

			if($this->app->isAjaxRequest() && $inline == true) {

				if(isset($_POST['data']) && $page != false) {

					foreach($_POST['data'] AS $key => $value) {

						$page->$key = $value;

						if($key == 'title') {
							$page->clean_title = $this->getCleanTitle($value, $currentCleanTitle);
						}

					}

					if($this->pageMapper->save($page) != false) {
						$response = array('message'=>__('Page was updated successfully.'));
					} else {
						$response = array('error'=>__('Could not save changes to the page.'));
					}

				} else {
					$response = array('error'=>__('Page not found or invalid data.'));
				}

				$this->view->addSubview('main', new Application_View_String(json_encode($response)));

			} else {

				if($page == false) {

					$subview->loadHTML('templates/page/edit.error.notfound.html');

				} else {

					$this->form = new Modules_Form();
					$this->form->data['page'] = $page;
					$this->form->loadTemplate('templates/page/edit.form.html');

					if($this->form->isSent()) {

						$validation = new Modules_Validation_Basechecks();
						$validation->length($this->form->valueOf('data[title]'), 1, 0, __('Please enter a pagetitle'));

						$this->form->addValidation($validation);

					}

					if($this->form->isSent(true)) {

						foreach($this->form->valueOf('data') AS $col => $value) {
							$page->$col = $value;
						}

						$page->active		= $this->form->valueOf('data[active]') == 1 ? '1' : '0';
						$page->hidden		= $this->form->valueOf('data[hidden]') == 1 ? '1' : '0';

						// Remove HTML Tags, apply Extension modifications, etc
						$page->content		= $this->parseContent($page->content); 
						$page->clean_title	= $this->getCleanTitle($page->title, $currentCleanTitle);

						if($this->pageMapper->save($page) != false) {
							$subview->loadHTML('templates/page/edit.success.html');
						} else {
							$subview->loadHTML('templates/page/edit.error.html');
						}

						$this->view->addSubview('main', $subview);

					} else {

						$this->view->addSubview('main', $this->form);

					}

				}

			}

		} else { // Access denied

			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());

		}

	}

	public function delete($page_id) {

		if($this->app->isAjaxRequest()) {

			$page = $this->pageMapper->find($page_id, new Model_Page);

			if($page == false) {

				$response = array('error'=>__('The page you’re trying to delete does not exist.'));

			} else {

				if(isset($_POST['__confirm'])) {

					if($this->isLastRootpage($page_id) == false) {

						$delete = $this->pageMapper->delete($page_id);

						if($delete == true) {

							$response = array('message'=>__('Page was deleted successfully.'));
														
						} else {

							$response = array('error'=>__('Page could not be deleted.'));

						}

					} else {

						$response = array('error'=>__('Cannot delete last rootpage from tree.'));

					}

				} else {

					$response = array('error'=>__('Action cancelled by the user.'));

				}

			}

			$this->view->addSubview('main', new Application_View_String(json_encode($response)));

		} else {

			$page = $this->pageMapper->find($page_id, new Model_Page);
			$subview = $this->app->createView();

			if($page == false) {

				$subview->loadHTML('templates/page/delete.error.notfound.html');

			} else {

				$form = new Modules_Form('templates/page/delete.form.html');

				if($form->isSent(true) && $form->valueOf('__confirm') != '') {

					if($this->isLastRootpage($page_id) == false) {

						$delete = $this->pageMapper->delete($page_id);

						if($delete == true) {

							$subview->loadHTML('templates/page/delete.success.html');

						} else {

							$subview->loadHTML('templates/page/delete.error.html');

						}

					} else {

						$response = array('error'=>__('Cannot delete last rootpage from tree.'));

					}

					$this->view->addSubview('main', $subview);

				} else {

					$this->view->addSubview('main', $form);

				}

			}

		}

		// DELETE ORPHANED SUBPAGES HERE (Pages with empty parent_page_id)
		// Should be run recursively to also delete subpages of deleted subpages of deleted subpages ... 
		// Nevermind.
		$this->pageMapper->deleteOrphanedPages();

	}


	public function reorder() {

		$pageData = $this->getPageStructure();
		$factor = $this->sortFactor;

		foreach($pageData['flatArray'] AS $num => $data) {
			$oldStructure[$data->page_id]['parent_page_id'] = $data->parent_page_id;
			$oldStructure[$data->page_id]['sort'] = $data->sort;
		}

		foreach($_POST['page'] AS $page_id => $parent_page_id) {

			$parent_page_id = (int) $parent_page_id;

			if(!isset($counter[$parent_page_id])) {
				$counter[$parent_page_id] = 0;
			} else {
				$counter[$parent_page_id] += 1;
			}


			if((int) $oldStructure[$page_id]['sort'] !== (int) $counter[$parent_page_id] * $factor) {
				$newStructure[$page_id]['parent_page_id'] = (int) $parent_page_id;
				$newStructure[$page_id]['sort'] = $counter[$parent_page_id] * $factor;
			}

			if((int) $oldStructure[$page_id]['parent_page_id'] !== (int) $parent_page_id) {
				$newStructure[$page_id]['parent_page_id'] = (int) $parent_page_id;
				$newStructure[$page_id]['sort'] = $counter[$parent_page_id] * $factor;
			}


		}

		if(is_array($newStructure)) {

			foreach($newStructure AS $page_id => $properties) {

				$page = new Model_Page();
				$page->page_id = $page_id;
				if(isset($properties['parent_page_id'])) {
					$page->parent_page_id = $properties['parent_page_id'];
				}
				$page->sort = $properties['sort'];
				$this->pageMapper->save($page);

			}

		}
		
	}

	protected function getCleanTitle($pagetitle, $currentTitle=NULL) {

		$allPages = $this->pageMapper->fetchAll();
		if(is_array($allPages)) {
			foreach($allPages AS $pageObject) {
				if($pageObject->clean_title != $currentTitle) {
					$cleanTitles[] = $pageObject->clean_title;
				}
			}
		}

		$clean_title		= $pagetitle != '' ? Modules_Functions::cleanURL($pagetitle) : 'untitled';
		$clean_title		= Modules_Functions::getUniqueName($clean_title, $cleanTitles);

		return $clean_title;

	}

	protected function parseContent($content) {

		$this->content = $content;

		$this->app->extensions()->notify($this, 'prePageParseContent');

		if(Application_Settings::get('//theme/pages/allowHTML', 1) == 1) {

			$allowed = Application_Settings::get('//theme/pages/allowedHTMLElements', 1);
			$allowed = explode(',', $allowed);
			foreach($allowed AS $key => $tag) {
				$allowed[$key] = '<' . trim($tag) . '>';
			}

			$this->content = strip_tags($this->content, join('', $allowed));

		} else {

			$this->content = strip_tags($this->content);

		}

		$this->app->extensions()->notify($this, 'postPageParseContent');

		$content = $this->content;
		unset($this->content);

		return $content;

	}

	protected function getPageStructure() {

		$allPages	= $this->pageMapper->fetchAll();

		if(is_array($allPages)) {

			$pages = array();

			foreach($allPages AS $page => $data) {

				$data->cssclass = '';
				$data->cssclass .= $data->hidden == 1 ? ' hidden ' : '';
				$data->cssclass .= $data->active == 1 ? ' active ' : ' inactive ';
				$data->cssclass .= $data->childcount > 0 ? ' haschildren ':'';
				$pages[$data->parent_page_id][$data->page_id] = $data;

			}

		}

		$ret = array();
		$ret['flatArray'] = $allPages;
		$ret['parentChild'] = $pages;

		return $ret;

	}

	public function isLastRootpage($page_id) {

		$pagesOnRootlevel = $this->pageMapper->getPagesOnRootlevel();
		if(count($pagesOnRootlevel) == 1) {

			if($pagesOnRootlevel[0]->page_id == $page_id) {
				return true;
			} else {
				return false;
			}

		} else {

			return false;

		}

	}

}