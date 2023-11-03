<?php

global $_allow;
global $_content_type;
global $_request;
global $_method;
global $_code;

$_request = array();
$_method = "";
$_code = 200;
$_allow = array();
$_content_type = "application/json";

function get_referer() {
    return $_SERVER['HTTP_REFERER'];
}

function response($data, $status) {
    global $_allow; 
    global $_content_type;
    global $_request;
    global $_method;
    global $_code;
    $_code = ($status) ? $status : 200;
    set_headers();
    if(!is_string($data)){
        $data = json_encode($data);
    }
    echo $data;
    exit;
}

function jwt_response($set_header_data, $data, $status) {
    global $_allow;
    global $_content_type;
    global $_request;
    global $_method;
    global $_code;
    $_code = ($status) ? $status : 200;

    if ($status == 200) {
        set_jwt_headers($set_header_data);
    } else {
        set_headers();
    }

    echo $data;
    exit;
}

function get_status_message() {
    global $_allow;
    global $_content_type;
    global $_request;
    global $_method;
    global $_code;
    $status = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported');
    return ($status[$_code]) ? $status[$_code] : $status[500];
}

function get_request_method() {
    return $_SERVER['REQUEST_METHOD'];
}

function inputs() {
    global $_allow;
    global $_content_type;
    global $_request;
    global $_method;
    global $_code;
    switch (get_request_method()) {
        case "POST":
            $_request = $this->cleanInputs($_POST);
            break;
        case "GET":
        case "DELETE":
            $_request = $this->cleanInputs($_GET);
            break;
        case "PUT":
            parse_str(file_get_contents("php://input"), $this->_request);
            $_request = $this->cleanInputs($this->_request);
            break;
        default:
            response('', 406);
            break;
    }
}

function cleanInputs($data) {
    global $_allow;
    global $_content_type;
    global $_request;
    global $_method;
    global $_code;
    $clean_input = array();
    if (is_array($data)) {
        foreach ($data as $k => $v) {
            $clean_input[$k] = $this->cleanInputs($v);
        }
    } else {
        // if (get_magic_quotes_gpc()) {
        //     $data = trim(stripslashes($data));
        // }
        $data = strip_tags($data);
        $clean_input = trim($data);
    }
    return $clean_input;
}

function set_jwt_headers($jwtdata) {
    global $_allow;
    global $_content_type;
    global $_request;
    global $_method;
    global $_code;
    header("HTTP/1.1 " . $_code . " " . get_status_message());
    header("Content-Type:" . $_content_type);
    header("Authorization: Bearer " . $jwtdata);
}

function set_headers() {
    global $_allow;
    global $_content_type;
    global $_request;
    global $_method;
    global $_code;
    header("HTTP/1.1 " . $_code . " " . get_status_message());
    header("Content-Type:" . $_content_type);
}
