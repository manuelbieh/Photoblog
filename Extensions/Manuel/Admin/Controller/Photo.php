<?php

class Extensions_Manuel_Admin_Controller_Photo extends Application_Extension {

	public function adminPhotoAddTabLabel() {

		echo '<li><label><input type="radio" name="source" value="extension" /> Test extension</label></li>';
	
	}

	public function adminPhotoAddTabSection() {
?>
		<section data-value="extension">

			<p>
				<label for="extension[foo]"><?php echo __('Enter Something:'); ?></label>
				<input type="text" value="extensionfoo" />
			</p>

		</section>

<?php
	}

}