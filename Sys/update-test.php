<?php
include_once "libs/pclzip/pclzip.lib.php";

$archive = new PclZip('./update/update-0.8.1beta.zip');
if($archive->extract(PCLZIP_OPT_PATH, '../', PCLZIP_OPT_REPLACE_NEWER) == 0) {
	echo 'Error :(';
	echo $archive->errorInfo(true);
} else {
	echo 'Alright!';
}