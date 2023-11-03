<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
require_once 'l-inputFilter.php';



function validateInput($input){
    
    $filter = new Filter();
    $allowed_protocols = array('http', 'ftp', 'mailto');
    $allowed_tags = array();

    $filter->addAllowedProtocols($allowed_protocols);
    $filter->addAllowedTags($allowed_tags);

    $filtered_string = $filter->xss($input);
    return $filtered_string;
}

function validateInteger($input){
    $input = trim($input);
    if($input != ''){
    if (preg_match('/^[0-9]+$/', $input)) {
        return true;
    } else {
       return false;
    }
    } else {
        return true;
}
}

function validateAlphaNumeric($input){
    $input = trim($input);
    if($input != ''){
    if (preg_match('/^[a-z0-9 .\-]+$/i', $input)) {
        return true;
    } else {
       return false;
    }
    } else {
        return true;
}
}

function validateAlpha($input){
    $input = trim($input);
    if($input != ''){
    if (preg_match('/^[a-z .\-]+$/i', $input)) {
        return true;
    } else {
       return false;
    }
    } else {
        return true;
}
}

function validateDownloadFile($input){
    $input = trim($input);
    if($input != ''){
    if (preg_match('/[^a-zA-Z0-9\.\/]/', $input)) {
        return true;
    } else {
       return false;
    }
    } else {
        return true;
}
}

?>