<?php

class Controller_Comment {

	protected $mapper;

	public function __construct() {
		$this->mapper		= new Model_Comment_Mapper(new Model_Comment_Gateway_PDO(Application_Registry::get('pdodb')));
	}

	public function commentForm($photo_id) {

		if((int) $photo_id !== 0) {

			$this->commentForm = new Modules_Form();
			$this->commentForm->loadTemplate(Application_Base::getPath(Application_View::getThemeDir() . '/comments/comment.form.html'));

			if($this->commentForm->isSent()) {
				$this->validation = new Controller_Comment_Validation();
				$this->validation->checkRequiredFields($this->commentForm);
				$this->commentForm->addValidation($this->validation);
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

				$this->mapper->save($comment);
				return __('Your comment was saved successfully.');


			} else {

				return $this->commentForm->render();

			}

		}

	}

}