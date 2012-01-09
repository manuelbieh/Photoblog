<?php
ini_set('display_errors', 'on');
error_reporting(E_ALL);

header("Content-Type: text/html; charset=utf-8");
session_start();
$projectURL = $_SERVER['SERVER_NAME'] . '/' . trim(str_replace($_SERVER['DOCUMENT_ROOT'], '', rtrim(realpath(dirname($_SERVER['SCRIPT_FILENAME']) . '/..'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR . '/');
include "../Includes/Bootstrap.inc.php";
if(Modules_Filesys::isFile(dirname(__FILE__) . '/INSTALLED')) {
die(__('Exhibit is already installed. Delete the file /install/INSTALLED to install it again.'));
}
?><!DOCTYPE html>
<html>
<head>
<title>Exhibit Blog » Installation</title>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<link href="//<?php echo $projectURL; ?>/Admin/templates/assets/css/layout.css" rel="stylesheet" type="text/css" />
<script>
/*
$(function() {

	$('fieldset.optional').hide();

	$.each($('fieldset.optional'), function(key, el) {
		$$ = $(el);
		$$.before('<button class="showopt">' + $$.find('>legend').text() + '</button>');
	});

	$('.showopt').bind('click', function(e) {
		e.preventDefault();
		$$ = $(this);
		$$.next('fieldset').toggle();
		return false;
	});

});*/
</script>
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
					$form->addError(__('Required class <strong>DOMDocument</strong> was not found.'));
				}

				$extensionsNeeded = array(
					'libxml',
					'pdo',
					'curl',
					'zlib'
				);

				foreach($extensionsNeeded AS $ext) {
					if(!extension_loaded($ext)) { $form->addError(sprintf(__('Required PHP extension <strong>%s</strong> could not be found.'), $ext)); }
				}

				if(!extension_loaded('gd') && !extension_loaded('imagick')) {
					$form->addError(__('Either GDLib or PECL-Imagick extension is required. Could not found either of them.'));
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
						$dsn		= 'mysql:dbname='.$form->valueOf('db[name]').';host='.$form->valueOf('db[host]');
						$user		= $form->valueOf('db[user]');
						$password	= $form->valueOf('db[pass]');
						$dbcx = new PDO($dsn, $user, $password);
					} catch(Exception $e) {
						$validate->addError(__('Establishing connection to database failed (wrong credentials?)'));
					}

				}


				/* TRYING TO MODIFY SOME SETTINGS */
				$tempDir = dirname(__FILE__) . '/temp';

				/* CHECK FOR MOD_REWRITE */
				if(!is_dir($tempDir)) {
					mkdir($tempDir);
				}
				file_put_contents($tempDir . '/.htaccess', "RewriteEngine On\nRewriteRule ^test$ index.php?test=pass");
				file_put_contents($tempDir . '/index.php', '<?php echo isset($_GET["test"]) ? $_GET["test"] : "fail"; ?>');

				$curlObj = new Modules_Curl();
				$curlObj->setOption(CURLOPT_CONNECTTIMEOUT, 30);
				$curlObj->connect('http://' . $_SERVER['SERVER_NAME'] . '/' . trim(str_replace('index.php', '', $_SERVER['REQUEST_URI']), '/') . '/temp/test');
				$httpStatus = $curlObj->info(CURLINFO_HTTP_CODE);
				if($curlObj->output() != 'pass') {
					$warning[] = __('<strong>mod_rewrite</strong> is required and seems to be unavailable on this server. Installation will proceed but there’s no guarantee the application will run properly.');
				}

				/* END CHECK FOR MOD_REWRITE */

				/* CHECK IF MAGIC QUOTES CAN BE DISABLED VIA .htaccess */
				if(get_magic_quotes_gpc() == true) {

					file_put_contents($tempDir . '/.htaccess', 'php_flag magic_quotes_gpc Off');
					file_put_contents($tempDir . '/index.php', '<?php echo (int) get_magic_quotes_gpc() == 0 ? "pass" : "fail"; ?>');
					$curlObj = new Modules_Curl();
					$curlObj->connect('http://' . $_SERVER['SERVER_NAME'] . '/' . trim(str_replace('index.php', '', $_SERVER['REQUEST_URI']), '/') . '/temp/');
					$httpStatus = $curlObj->info(CURLINFO_HTTP_CODE);

					if($httpStatus < 400) {

						if($curlObj->output() == 'fail') {
							$warning[] = __("<strong>get_magic_quotes</strong> is currently 'on'. It's strongly recommended to turn it 'off'.");
						} else {
							file_put_contents(dirname(__FILE__) . '/../.htaccess', 'php_flag magic_quotes_gpc Off', FILE_APPEND);
						}

					} else {

						$warning[] = __("<strong>get_magic_quotes</strong> is currently 'on'. It's strongly recommended to turn it 'off'.");

					}

				}
				/* END CHECK IF MAGIC QUOTES CAN BE DISABLED VIA .htaccess */

				/* CLEAN UP */
				foreach(glob(dirname(__FILE__) . '/temp/*') AS $file) {
					unlink($file);
				}
				unlink(dirname(__FILE__) . '/temp/.htaccess');
				rmdir(dirname(__FILE__) . '/temp');
				/* END CLEAN UP */


				$form->assign('db[host]', $form->input(array('name'=>'db[host]', 'id'=>'db[host]')));
				$form->assign('db[name]', $form->input(array('name'=>'db[name]', 'id'=>'db[name]')));
				$form->assign('db[user]', $form->input(array('name'=>'db[user]', 'id'=>'db[user]')));
				$form->assign('db[pass]', $form->input(array('name'=>'db[pass]', 'id'=>'db[pass]', 'type'=>'password')));

				$form->assign('user[username]', $form->input(array('name'=>'user[username]', 'id'=>'user[username]')));
				$form->assign('user[email]', $form->input(array('name'=>'user[email]', 'id'=>'user[email]')));
				$form->assign('user[firstname]', $form->input(array('name'=>'user[firstname]', 'id'=>'user[firstname]')));
				$form->assign('user[lastname]', $form->input(array('name'=>'user[lastname]', 'id'=>'user[lastname]')));
				$form->assign('user[pass]', $form->input(array('name'=>'user[password]', 'id'=>'user[pass]', 'type'=>'password')));

				$form->assign('passconf', $form->input(array('name'=>'passconf', 'id'=>'passconf', 'type'=>'password')));

				$form->addValidation($validate);

				$perm644 = array(
					#'../Admin/templates', // needs write permission? sure?!
					'../uploads',
					'../Sys',
					'../Sys/Hooks.xml',
					'../Includes',
					'../Includes/Settings.xml',
					'../Includes/VERSION',
					'../Extensions'
				);

				foreach($perm644 AS $permCheck) {
					if(!is_writable($permCheck)) {
						$form->addError($permCheck . ' ' . __('is not writable by the webserver.'));
					}
				}

				if($form->isSent(true)) {

					// create required folders
					$letters = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'));
					$secret = Modules_Functions::getRandomString(36, $letters);
					$uploadDirs = array('avatars', 'mini', 'pile', "pile/$secret", 'source', "source/$secret", 'temp', 'thumbs', 'web');
					foreach($uploadDirs AS $dirName) {
						if(!file_exists(dirname(__FILE__) . '/../uploads/' . $dirName)) {
							mkdir(dirname(__FILE__) . '/../uploads/' . $dirName);
						}
					}
					mkdir(dirname(__FILE__) . '/../Sys/update');
					mkdir(dirname(__FILE__) . '/../Sys/backup');

					// config generation
					$config = Modules_Filesys::read('config.tpl');

					foreach($form->valueOf('db') AS $key => $value) {
						$config = Modules_Functions::patternReplace($config, array('db['.$key.']'=>$value));
					}

					$salt = Modules_Functions::getRandomString(24);
					$config = Modules_Functions::patternReplace($config, array('settings[salt]'=>$salt));

					$updatePW = Modules_Functions::getRandomString(mt_rand(8,12));

					file_put_contents(dirname(__FILE__) . '/../Includes/Config.inc.php', $config);
					include_once dirname(__FILE__) . '/../Includes/Config.inc.php';

					// mysql table import
					$sysGateway	= new Model_System_Gateway_PDO($pdodb);
					$sysMapper	= new Model_System_Mapper($sysGateway);

					$installSQL = Modules_Filesys::read('install.sql');
					$sqlImport = $sysMapper->importDump($installSQL);

					// admin user creation
					$user = new Model_User();
					foreach($form->valueOf('user') AS $key => $value) {
						$user->$key = $value;
					}

					$enc = new Modules_Encryption_Md5();

					$user->password = $enc->encryptWithSalt($user->password, $salt);
					$user->active = 1;

					$userMapper = new Model_User_Mapper(
						new Model_User_Gateway_PDO($pdodb)
					);
					$userMapper->save($user);

					$view = new Application_View();
					$view->loadHTML('templates/install/install.success.html');
					$view->render(true);
					Modules_Filesys::write(dirname(__FILE__) . '/INSTALLED', '');

				} else {

					if(!class_exists('IMagick') && extension_loaded('gd')) {
						$warning[] = __("Class <strong>IMagick</strong> doesn't exist. Using GDLib instead.");
					}

					if(is_array($warning)) {
						$warning = array_unique($warning);
					}

					if(is_array($warning)) {
						?>
						<ul class="warnings">
						<?php
						foreach($warning AS $w) {
						?>
							<li><?php echo $w; ?></li>
						<?php
						}
						?>
						</ul>
						<?php
						
					}

					echo $form->render();

				}

			?>

		</section>

	</div>

<footer></footer>
<!--
<?php
print_r($sqlImport);
?>
-->

</body>
</html>