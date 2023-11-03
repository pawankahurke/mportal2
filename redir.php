<?php
$header = array(
  'Content-Type: application/json',
  'PHPSESSID: ' . session_id(),
  "X-Nh-Token: " . nhRole::getNhTokenForHeader(),
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,    url::getToAny('url'));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_REFERER, $base_url);
curl_setopt($ch, CURLOPT_POST, true);

echo  $result = curl_exec($ch);
echo $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
/* This will give an error. Note the output
 * above, which is before the header() call */
//echo url::getToAny('url');exit;

/* header_remove("x-forwarded-for");
header('Location: '.url::getToAny('url'), true ,301);
exit; */
