<?php

class Admin_Controller_Comments extends Controller_Frontend {

	public function __construct($app) {

		$app->extensions()->registerObservers($this);

		$this->app = $app;

		$this->view		= $this->app->objectManager->get('Application_View');
		$this->access	= $this->app->objectManager->get('Admin_Application_Access');

		if(!isset($_POST['ajax'])) {
			$this->view->loadHTML('templates/index.html');
		} else {
			$this->view->loadHTML('templates/ajax.html');
		}

		$navi = $this->app->createView();
		$navi->loadHTML("templates/main/navi.html");

		$this->view->addSubview('navi', $navi);

		if((int) Modules_Session::getInstance()->getVar('userdata')->user_id === 0) {
			$this->app->go('Login');
		}

	}

	public function view($photo_id=NULL, $offset=0, $order='DESC') {

		if($this->access->check(__METHOD__)) {

			$order			= $order == 'ASC' ? 'ASC' : 'DESC';
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
					if($order == 'DESC') {
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
				if($order == 'ASC') {
					$pagina->setParams('/' . $order);
				}
				$subview->data['pagination'] = $pagina->render();

			} else {

				$subview->loadHTML('templates/comments/error.nocommentsfound.html');

			}

			$this->view->addSubview('main', $subview);

		} else {
			$this->view->addSubview('main', $this->app->objectManager->get('Application_Error')->error401());
		}

	}

	public function edit($comment_id) {

		$commentMapper	= new Model_Comment_Mapper(new Model_Comment_Gateway_PDO(Application_Registry::get('pdodb')));
		$photoMapper	= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO(Application_Registry::get('pdodb')));
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

		$commentMapper	= new Model_Comment_Mapper(new Model_Comment_Gateway_PDO(Application_Registry::get('pdodb')));
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