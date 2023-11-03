<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
require_once '../lib/l-db.php';

$permission = checkModulePrivilege('alertnotification', 2);
if (!$permission) exit(json_encode(array('status' => false, 'message' => 'Permission denied')));

global $elast_watch;
global $elast_path;

if (!isset($_SESSION)) {
}

$path = $elast_path;

if (isset($_GET) && url::getToText('action') == 'delete') {

    if (!isset($_SESSION['user']['username'])  || !isset($_SESSION['user']['userid'])) {
        $return = array('status' => true, 'message' => 'loggedout');
        echo json_encode($return);
        return;
    }

    if (!url::issetInPost('yaml') ||  safe_sizeof(url::postToAny('yaml')) <= 0) {
        $return = array('status' => false, 'message' => 'please mention the name of the file to delete');
        echo json_encode($return);
        return;
    }

    $userId = $_SESSION['user']['userid'];
    $yamlName = url::postToText('yaml');

    $bindings = str_repeat('?,', count(url::postToAny('yaml')) - 1) . '?';
    $bindData = url::postToAny('yaml');
    $bindData[] = $userId;

    $db = NanoDB::connect();
    $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "dashboard.yaml_configurations WHERE id IN (" . $bindings . ") AND user_id=?");
    $sql->execute($bindData);
    $isExist = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$isExist) {
        exit(json_encode(array('status' => false, 'message' => 'You dont have permission to delete this configuration')));
    }

    $target = $path . "/" . $yamlName;
    @unlink($target);

    try {

        $sql = "DELETE FROM `" . $GLOBALS['PREFIX'] . "dashboard`.`yaml_configurations` WHERE id IN (" . $bindings . ") AND user_id=?";
        $pdo = $db->prepare($sql);
        $pdo->execute($bindData);
    } catch (Exception $e) {
        $return = array('status' => false, 'message' => 'Something went wrong');
        echo json_encode($return);
        return;
    }

    $return = array('status' => true, "message" => "successfully deleted yaml configuration file");

    echo json_encode($return);
}
