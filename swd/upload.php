<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

$bucketName = base64_decode(url::requestToAny('bucket'));
$IAM_KEY = base64_decode(url::requestToAny('access'));
$IAM_SECRET = base64_decode(url::requestToAny('secret'));
$Region = base64_decode(url::requestToAny('region'));
$filename = base64_decode(url::requestToAny('file'));
try {
	$s3 = S3Client::factory(
		array(
			'credentials' => array(
				'key' => $IAM_KEY,
				'secret' => $IAM_SECRET
			),
			'version' => 'latest'
		)
	);
} catch (Exception $e) {
	logs::log(__FILE__, __LINE__, $e, 0);
	die("Error: " . $e->getMessage());
}

$fileURL = $base_url . "swd/" . $filename;
$keyName = '' . basename($fileURL);
$pathInS3 = 'https://s3.amazonaws.com/' . $bucketName . '/' . $keyName;
try {
	if (!file_exists('/tmp/tmpfile')) {
		mkdir('/tmp/tmpfile');
	}

	$tempFilePath = '/tmp/tmpfile/' . basename($fileURL);
	$tempFile = fopen($tempFilePath, "w") or die("Error: Unable to open file.");
	$fileContents = file_get_contents($fileURL);
	$tempFile = file_put_contents($tempFilePath, $fileContents);
	$s3->putObject(
		array(
			'Bucket' => $bucketName,
			'ACL' => 'public-read',
			'Key' =>  $keyName,
			'SourceFile' => $tempFilePath,
			'Body' => $tempFilePath,
			'StorageClass' => 'REDUCED_REDUNDANCY'
		)
	);
} catch (S3Exception $e) {
	die('Error:' . $e->getMessage());
} catch (Exception $e) {
	logs::log(__FILE__, __LINE__, $e, 0);
	die('Error:' . $e->getMessage());
}
echo 'Done';
