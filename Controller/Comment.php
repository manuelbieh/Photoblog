<?php

class Controller_Comment {

	protected $mapper;

	public function __construct($app=NULL) {

		$app->extensions()->registerObservers($this);

		$this->app = $app;

		$this->commentGateway	= new Model_Comment_Gateway_PDO($app->objectManager->get('Datastore'));
		$this->commentMapper	= new Model_Comment_Mapper($this->commentGateway);

		$this->view = new Application_View_Theme();
		$this->view->loadHTML('index.html');

	}

	public function commentForm($photo_id) {

		if((int) $photo_id !== 0) {

			$this->commentForm = new Modules_Form();
			$this->commentForm->loadTemplate($this->app->getPath(Application_View::getThemeDir() . '/comments/comment.form.html'));

			if($this->commentForm->isSent()) {
				$this->validation = new Controller_Comment_Validation();
				$this->validation->checkRequiredFields($this->commentForm);
				$this->commentForm->addValidation($this->validation);
				$this->app->extensions()->notify($this, 'commentFormSent');
			}


			if($this->commentForm->isSent(true)) {

				$comment = new Model_Comment();
				foreach($this->commentForm->valueOf('data') AS $prop => $value) {
					$comment->$prop = $value;
				}
				$comment->datetime	= date('Y-m-d H:i:s');
				$comment->active	= 1;
				$comment->photo_id	= (int) $photo_id;
				$comment->ip		= $_SERVER['REMOTE_ADDR'];

				$this->commentMapper->save($comment);
				return __('Your comment was saved successfully.');


			} else {

				return $this->commentForm->render();

			}

		}

	}

}