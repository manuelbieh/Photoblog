<?php

class Admin_Controller_Comments extends Controller_Frontend {

	public function __construct($app) {

		$this->app = $app;

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}

		$app->extensions()->registerObservers($this);

		$this->view		= $this->app->objectManager->get('Application_View');
		$this->access	= $this->app->objectManager->get('Admin_Application_Access');

		if(!$this->app->isAjaxRequest()) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

	}

	public function view($photo_id=NULL, $offset=0, $order='desc') {

		if($this->access->check(__METHOD__)) {

			$order			= strtolower($order);
			$order			= $order == 'asc' ? 'asc' : 'desc';
			$revOrder		= $order == 'asc' ? 'desc' : 'asc';

			$commentMapper	= new Model_Comment_Mapper(new Model_Comment_Gateway_PDO($this->app->objectManager->get('Datastore')));
			$photoMapper	= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO($this->app->objectManager->get('Datastore')));
			$subview		= $this->app->createView();

			if($photo_id == NULL || (int) $photo_id === 0) {
				$allComments	= $commentMapper->fetchAll();
			} else {
				$allComments	= $commentMapper->findByPhoto($photo_id);
			}

			if(is_array($allComments)) {

				$subview->loadHTML('templates/comments/view.html');

				$allCommentsReverse	= array_reverse($allComments);

				$itemsPerPage		= 10;
				$totalItems			= count($allComments);
				$offset				= (int) $offset;


				$subview->data['photomapper'] = $photoMapper;
				$subview->data['offset'] = (int) $offset;
				for($i = $offset; $i < $offset+$itemsPerPage; $i++) {
					if($order == 'desc') {
						if(isset($allCommentsReverse[$i])) {
							$subview->data['comments'][$i] = $allCommentsReverse[$i];
						}
					} else {
						if(isset($allComments[$i])) {
							$subview->data['comments'][$i] = $allComments[$i];
						}
					}
				}

				$pagina = new Modules_Pagination;
				$pagina->setLink(Application_Base::getBaseURL() . "Comments/view/" . (int) $photo_id . '/')->setItemsPerPage($itemsPerPage)->setItemsTotal($totalItems)->currentPageNum($offset);
				if($order == 'asc') {
					$pagina->setParams('/' . $order);
				}
				$subview->data['pagination'] = $pagina->render();

				$subview->data['sortLink'] = Modules_Functions::patternReplace(Application_Base::getBaseURL() . "Comments/view/%photo_id%/%page%/%sort%", array('photo_id'=>(int) $photo_id, 'page'=>$offset, 'sort'=>$revOrder));
				$subview->data['sortLabel'] = $order == 'asc' ? __('Show newest first') : __('Show oldest first');
				$subview->data['sort'] = htmlentities($order, ENT_NOQUOTES, 'UTF-8');

			} else {

				$subview->loadHTML('templates/comments/error.nocommentsfound.html');

			}

			$this->view->addSubview('main', $subview);

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
		}

	}

	public function edit($comment_id) {

		$commentMapper	= new Model_Comment_Mapper(new Model_Comment_Gateway_PDO($this->app->objectManager->get('Datastore')));
		$photoMapper	= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO($this->app->objectManager->get('Datastore')));
		$comment		= $commentMapper->find($comment_id, new Model_Comment());

		if($this->access->check(__METHOD__)) {

			if($comment !== false) {

				$form = new Modules_Form();

				if($form->isSent(true)) {

					foreach($form->valueOf('data') AS $property => $value) {
						$comment->$property = $value;
					}

					$commentMapper->save($comment);
					$subview = $this->app->createView();
					$subview->loadHTML('templates/comments/edit.form.success.html');
					$this->view->addSubview('main', $subview);

				} else {

					$form->data['comment'] = $comment;
					$form->loadTemplate('templates/comments/edit.form.html');
					$this->view->addSubview('main', $form);

				}

			} else {

				$subview = $this->app->createView();
				$subview->loadHTML('templates/comments/edit.error.notfound.html');
				$this->view->addSubview('main', $subview);

			}

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
		}

	}

	public function delete($comment_id) {

		$commentMapper	= new Model_Comment_Mapper(new Model_Comment_Gateway_PDO($this->app->objectManager->get('Datastore')));
		$comment		= $commentMapper->find($comment_id, new Model_Comment());

		$subview 		= $this->app->createView();

		if($this->access->check(__METHOD__)) {

			if($comment !== false && isset($_POST['confirm'])) {

				$deleted = $commentMapper->delete($comment_id);

				if($deleted == true) {

					if($_POST['ajax']) {
						$subview->setHTML(__('{"response":"Comment was deleted successfully."}'));
					} else {
						$subview->loadHTML('templates/comments/delete.success.html');
					}

				} else {

					if($_POST['ajax']) {
						$subview->setHTML(__('{"error":"For some reason the comment could not be deleted."}'));
					} else {
						$subview->loadHTML('templates/comments/delete.success.html');
					}

				}

				$this->view->addSubview('main', $subview);

			} else if($comment === false) {

				$subview = $this->app->createView();
				$subview->loadHTML('templates/comments/delete.error.notfound.html');
				$this->view->addSubview('main', $subview);
			
			} else if(isset($_POST['cancel'])) {

				Application_Base::go($_POST['r']);

			} else {

				if($_POST['ajax']) {
					
				} else {
					$form = new Modules_Form('templates/comments/delete.form.html');
					$form->assign('web_name', $comment->web_name);
					$this->view->addSubview('main', $form);
				}

				$deleted = false;

			}

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
		}

	}

}