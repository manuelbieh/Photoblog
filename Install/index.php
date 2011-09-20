<?php
include "../Includes/Bootstrap.inc.php";
?><!DOCTYPE html>
<html>
<head>
<title></title>
</head>
<body>

	<div id="wrapper">

		<h1>Install</h1>

		<?php
			$form = new Modules_Form('../../templates/install/install.form.html');

			$validate = new Modules_Validation_Basechecks();
			if($form->isSent()) {

				$validate->length($form->valueOf('db[host]'), 1, 0, 'Please specify a database host');
				$validate->length($form->valueOf('db[name]'), 1, 0, 'Please specify a database name');
				$validate->length($form->valueOf('db[user]'), 1, 0, 'Please set a database user');

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

			$form->addValidation($validate);

			if($form->isSent(true)) {

				$config = file_get_contents('config.tpl');

				foreach($form->valueOf('db') AS $key => $value) {
					$config = Modules_Functions::patternReplace($config, array('db['.$key.']'=>$value));
				}

				file_put_contents('../Includes/Config.inc.php', $config);
				// mysql table import
				// admin user creation

			} else {
				echo $form->render();
			}
		?>

	</div>

</body>
</html>