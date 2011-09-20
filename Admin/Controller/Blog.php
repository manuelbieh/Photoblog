<?php

class Controller_Blog {

	public function add() {

		// $form = new Modules_Form('templates/blog/add.form.html');

		// $categories = $form->valueOf('data[categories]');

		//$_FILES['image']['tmp_name'];

		$image['width']			= '';
		$image['height']		= '';
		$image['tags']			= '';
		$image['desciption']	= '';
		$image['blog_id']		= '';
		$image['image_id']		= '';
		$image['thumbnail_url']	= '';
		$image['preview_url']	= '';
		$image['hash']			= '';

		$Imagedata = json_encode($image);

		$Image = Api::get('Image');
		if($Image->Register($Imagedata)) {
		
		} else {
		
		}



	}

}