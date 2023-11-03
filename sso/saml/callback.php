<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * SAML CALLBACK FILE
 */

include_once '../../config.php';

global $ssosamlapiurl;

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

logs::log("oauth_callback_log",  $_REQUEST);


//header("location: $ssosamlapiurl/saml/callback/" . json_encode($_REQUEST));
header("location: $ssosamlapiurl/api/saml/callback", true, 307);


/*include_once '../config.php';
include_once '../lib/l-dbConnect.php';

function setCallbackRequest($reqData) {

    global $ssosamlapiurl;

    $data_string = json_encode($reqData);

    $header = array(
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string));
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ssosamlapiurl . '/saml/callback');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
    } catch (Exception $ex) {  logs::log(__FILE__, __LINE__, $ex, 0);
        return "Exception : " . $ex;
    }
    return ['code' => $httpcode, 'data' => $result];
}

$fp = fopen('callback_log.txt', 'a');

fwrite($fp, 'REQ : ' . json_encode($_REQUEST) . PHP_EOL . PHP_EOL);

$cbRespData = setCallbackRequest($_REQUEST);

fwrite($fp, json_encode($cbRespData) . PHP_EOL . PHP_EOL);
fclose($fp);*/