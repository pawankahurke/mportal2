<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";



try {
    NanoDB::connect();
    echo "OK";
} catch (Exception $e) {
    logs::log("Error in DB healthz", $e);
    header('HTTP/1.1 500 Error');
    exit('HTTP/1.1 500 Error');
}
