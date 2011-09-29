<?php

class Admin_Controller_Comments {

	public function __construct() {

		$this->view = new Application_View();

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

	public function __destruct() {
		$this->view->render(true);
	}

	public function view($photo_id=NULL, $offset=0) {

		$commentMapper	= new Model_Comment_Mapper(new Model_Comment_Gateway_PDO(Application_Registry::get('pdodb')));
		$photoMapper	= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO(Application_Registry::get('pdodb')));

		if($photo_id == NULL || (int) $photo_id === 0) {
			$allComments	= $commentMapper->fetchAll();
		} else {
			$allComments	= $commentMapper->findByPhoto($photo_id);
		}
		$itemsPerPage	= 10;
		$totalItems		= count($allComments);
		$offset			= (int) $offset;

		$subview = new Application_View();
		$subview->loadHTML('templates/comments/view.html');

		$subview->data['photomapper'] = $photoMapper;
		$subview->data['offset'] = (int) $offset;
		for($i = $offset; $i < $offset+$itemsPerPage; $i++) {
			if(isset($allComments[$i])) {
				$subview->data['comments'][$i] = $allComments[$i];
			}
		}

		$pagina = new Modules_Pagination;
		$pagina->setLink(Application_Base::getBaseURL() . "Comments/view/")->setItemsPerPage($itemsPerPage)->setItemsTotal($totalItems)->currentPageNum($offset);
		$subview->data['pagination'] = $pagina->render();

		$this->view->addSubview('main', $subview);

	}

	public function edit($comment_id) {

		$commentMapper	= new Model_Comment_Mapper(new Model_Comment_Gateway_PDO(Application_Registry::get('pdodb')));
		$photoMapper	= new Model_Photo_Mapper(new Model_Photo_Gateway_PDO(Application_Registry::get('pdodb')));
		$comment		= $commentMapper->find($comment_id, new Model_Comment());

		if($comment !== false) {

			$form = new Modules_Form();

			if($form->isSent(true)) {

				foreach($form->valueOf('data') AS $property => $value) {
					$comment->$property = $value;
				}

				$commentMapper->save($comment);
				$subview = new Application_View();
				$subview->loadHTML('templates/comments/edit.form.success.html');
				$this->view->addSubview('main', $subview);

			} else {

				$form->data['comment'] = $comment;
				$form->loadTemplate('templates/comments/edit.form.html');
				$this->view->addSubview('main', $form);

			}

		} else {

			$subview = new Application_View();
			$subview->loadHTML('templates/comments/edit.error.notfound.html');
			$this->view->addSubview('main', $subview);

		}


	}

	public function delete($comment_id) {

		$commentMapper	= new Model_Comment_Mapper(new Model_Comment_Gateway_PDO(Application_Registry::get('pdodb')));
		$comment		= $commentMapper->find($comment_id, new Model_Comment());

		$subview 		= new Application_View();

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
					$subview->setHTML(__('{"error":"Comment could, for some reason, not be deleted."}'));
				} else {
					$subview->loadHTML('templates/comments/delete.success.html');
				}

			}

			$this->view->addSubview('main', $subview);

		} else if($comment === false) {

			$subview = new Application_View();
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

		// getImageId() -> getOriginalName, getWebName
		// delete uploads/source/image
		// delete uploads/web/image
		// delete uploads/thumbs/image
		// delete uploads/mini/image
		// delete db entry
		// call stock api -> delete (blog_id, image_id)


	}

}