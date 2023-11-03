<?php
include_once '../config.php';

$token = url::requestToAny('token');
$line = url::requestToAny('line');

if(getenv('APP_SECRET_KEY') !== $token){
  die();
}

$filename = 'GENERIC';
$datenow = date('d-m-Y', time());

$fp = fopen('expunge_' . $filename . '_' . $datenow . '_log.txt', 'a');
fwrite($fp, $line . PHP_EOL);
fclose($fp);
