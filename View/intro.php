<?php
if(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
	ob_start('ob_gzhandler');
} else {
	ob_start();
}