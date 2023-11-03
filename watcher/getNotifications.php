<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../lib/l-db.php';
require_once '../include/common_functions.php';

if (!isset($_SESSION)) {
}

$site = url::issetInGet('site') ? url::getToAny('site') : '';

$sitelist = $_SESSION["user"]["site_list"];
$whereor = ' where ';
$tempor = '';
$i = 0;
if (safe_sizeof($sitelist) > 0) {

    $sitelist = array_values($sitelist);

    for ($i = 0; $i < safe_sizeof($sitelist); $i++) {
        if ($i == safe_sizeof($sitelist) - 1) {
            $tempor =  $tempor . " d.group_include like '%$sitelist[$i]%'" . " OR " . " d.group_include in( 'All', '') ";
        } else {

            $tempor =  $tempor . " d.group_include like '%$sitelist[$i]%'" . " OR ";
        }
    }
}
if ($tempor !== '') {

    $whereor = $whereor . $tempor;
} else {
    $whereor = $whereor . " d.group_include in( 'All', '') ";
}

$db = pdo_connect();
$username = $_SESSION['user']['username'];

$Sql = "SELECT d.id, d.name,d.ntype,d.created,d.modified,d.enabled,d.group_include FROM  " . $GLOBALS['PREFIX'] . "event.Notifications d " . $whereor;
$pdo = $db->prepare($Sql);
$pdo->execute();
$data = $pdo->fetchAll(PDO::FETCH_ASSOC);
$notifications = '';
if (safe_count($data) > 0) {
    foreach ($data as $index => $value) {
        $selected = '';
        if ($site == "") {
            if ($index == 0) {
                $selected = 'selected';
            }
        } else {

            if (strpos($value['group_include'], $site) !== false) {
                $selected = 'selected';
            }
        }


        $notifications .= '<option value="' . $value['id'] . '" ' . $selected . ' > ' . $value['name'] . '</option>';
    }
} else {
    $notifications = '<option value="">No Notifications Available</option>';
}

echo json_encode(array('status' => "success", "notifications" => $notifications));
