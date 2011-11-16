<?php
session_start();
include "../Includes/Bootstrap.inc.php";
?><!DOCTYPE html>
<html>
<head>
<title>Exhibit Blog » Installation</title>
<link href="//<?php echo $_SERVER['SERVER_NAME'] . '/' . trim(str_replace($_SERVER['DOCUMENT_ROOT'], '', rtrim(realpath(dirname($_SERVER['SCRIPT_FILENAME']) . '/..'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR . '/'); ?>Admin/templates/assets/css/layout.css" rel="stylesheet" type="text/css" />
</head>
<body>

	<div id="wrapper">

		<section id="sidebar"><header><h1></h1></header></section>

		<section id="content">

			<h2>Exhibit Blog » Install</h2>

			<?php

				$form = new Modules_Form(dirname(__FILE__) . '/../templates/install/install.form.html');


				if(!class_exists('DOMXPath')) {
					$form->addError(__('Required class <strong>DOMXPath</strong> was not found.'));
				}

				if(!class_exists('DOMDocument')) {
					$form->addError(__('Class <strong>DOMDocument</strong> was not found.'));
				}

				$extensionsNeeded = array('libxml', 'zlib', 'foo');

				foreach($extensionsNeeded AS $ext) {
					if(!extension_loaded($ext)) { $form->addError(sprintf(__('Required PHP extension <strong>%s</strong> could not be found.'), $ext)); }
				}

				$minPHPVersion = '5.2.1';
				if (version_compare(PHP_VERSION, $minPHPVersion, '<')) {
					$form->addError(sprintf(__('You are running a PHP version <strong>(%s)</strong> which is lower than the required version <strong>(%s)</strong>.'), PHP_VERSION, $minPHPVersion));
				}

				$validate = new Modules_Validation_Basechecks();
				if($form->isSent()) {

					$validate->length($form->valueOf('db[host]'), 1, 0, __('Please specify a database host'));
					$validate->length($form->valueOf('db[name]'), 1, 0, __('Please specify a database name'));
					$validate->length($form->valueOf('db[user]'), 1, 0, __('Please specify a database user'));

					$validate->length($form->valueOf('user[username]'), 2, 0, __('Please choose a username.'));
					$validate->email($form->valueOf('user[email]'), __('Please enter a valid email address.'));
					$validate->length($form->valueOf('user[password]'), 2, 0, __('Please choose a password with at least 6 characters.'));
					if($form->valueOf('user[password]') != $form->valueOf('passconf')) {
						$form->addError(__('Password and password confirmation did not match.'));
					}

					try {
						$dbcx = new PDO("mysql:dbname=".$form->valueOf('db[name]').";host=".$form->valueOf('data[host]'), $form->valueOf('db[user]'), $form->valueOf('db[pass]'));
					} catch(Exception $e) {
						$validate->addError('Establishing connection to database failed (wrong credentials?)');
					}

				}

				$form->assign('db[host]', $form->input(array('name'=>'db[host]', 'id'=>'db[host]')));
				$form->assign('db[name]', $form->input(array('name'=>'db[name]', 'id'=>'db[name]')));
				$form->assign('db[user]', $form->input(array('name'=>'db[user]', 'id'=>'db[user]')));
				$form->assign('db[pass]', $form->input(array('name'=>'db[pass]', 'id'=>'db[pass]')));

				$form->assign('user[username]', $form->input(array('name'=>'user[username]', 'id'=>'user[username]')));
				$form->assign('user[email]', $form->input(array('name'=>'user[email]', 'id'=>'user[email]')));
				$form->assign('user[firstname]', $form->input(array('name'=>'user[firstname]', 'id'=>'user[firstname]')));
				$form->assign('user[lastname]', $form->input(array('name'=>'user[lastname]', 'id'=>'user[lastname]')));
				$form->assign('user[pass]', $form->input(array('name'=>'user[password]', 'id'=>'user[pass]')));

				$form->assign('passconf', $form->input(array('name'=>'passconf', 'id'=>'passconf')));

				$form->addValidation($validate);

				$perm644 = array(
					'../Admin/templates',
					'../uploads',
					'../uploads/avatars',
					'../uploads/mini',
					'../uploads/pile',
					'../uploads/source',
					'../uploads/thumbs',
					'../uploads/web',
					'../Sys',
					'../Includes/Config.inc.php',
					'../Includes/Settings.xml',
					'../Extensions'
				);

				foreach($perm644 AS $permCheck) {
					if(!is_writable($permCheck)) {
						$form->addError($permCheck . ' ' . __('is not writable by the webserver.'));
					}
				}

				if($form->isSent(true)) {

					$config = file_get_contents('config.tpl');

					foreach($form->valueOf('db') AS $key => $value) {
						$config = Modules_Functions::patternReplace($config, array('db['.$key.']'=>$value));
					}

					//file_put_contents('../Includes/Config.inc.php', $config);
					// mysql table import
					// admin user creation
					$uploadFolders = glob('../uploads/*', GLOB_ONLYDIR);
					foreach($uploadFolders AS $folder) {
						chmod($folder, 0666);
					}

				} else {
					echo $form->render();
				}
			?>

		</section>

	</div>

<footer></footer>

</body>
</html>