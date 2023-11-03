<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once('./common.php');

if ($version[0] != '') {

    $dashid = url::requestToAny('id');
    $stmt = $pdo->query("SELECT `schema` from `analytics`.`schema` WHERE id = " . $dashid . " ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    print_r($rows[0]['schema']);
    die();
}
?>