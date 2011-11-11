<?php

require_once('libs/pclzip/pclzip.lib.php');
require_once('../Application/Base.php');

$app = new Application_Base();
$version = $app->getVersion();

$filename = 'update-'.$version.'.zip';
$filename = str_replace(' ', '_', $filename);
unlink($filename);

$archive = new PclZip($filename);
$v_dir = $app->getCoreDir();

$v_remove = $v_dir;
if($archive->create($v_dir, PCLZIP_OPT_REMOVE_PATH, $v_remove) == 0) {
	die("Error : ".$archive->errorInfo(true));
}

$blacklist = array('uploads/', 'uploads', 'Includes', '.git', '.svn');

$archive->delete(PCLZIP_OPT_BY_NAME, $blacklist);
$archive->delete(PCLZIP_OPT_BY_EREG, '(\.svn)');
