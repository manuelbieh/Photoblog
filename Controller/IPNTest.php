<?php
/**
 * Logs IPN messages to a file.
 */
class Controller_IPNTest extends Modules_Paypal_IPNHandler
{
        public function process(array $post_data) {

                $dir = Application_Base::getProjectDir() . 'Sys/cache/';

                file_put_contents($dir . time() . '.postdata.txt' , print_r($post_data, true));
                $data = parent::process($post_data);
                file_put_contents($dir . time() . '.processeddata.txt' , print_r($data, true));

                if($data === FALSE) {
                        header('HTTP/1.0 400 Bad Request', true, 400);
                        exit;
                }
                
                $output = implode("\t", array(time(), json_encode($data)));
                //file_put_contents('log.txt', $output.PHP_EOL, FILE_APPEND);
                $dir = Application_Base::getProjectDir() . 'Sys/cache/';
                Modules_Filesys::write($dir . time() . '.txt' , $output);
              //  die(var_dump($output));

        }
}

date_default_timezone_set('Europe/Berlin');

$handler = new Controller_IPNTest();
$handler->process($_POST);
