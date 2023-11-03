<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../lib/l-db.php';
include_once '../lib/l-setTimeZone.php';

global $elast_path;

if (!isset($_SESSION)) {
}

if (!isset($_SESSION['user']['username'])) {
    $return = array('status' => true, 'message' => 'loggedout');
    echo json_encode($return);
    return;
}

$dir = $elast_path;

if (isset($_GET) && url::issetInGet('action') && url::getToText('action') === 'yaml/details' && url::issetInGet('file') && !url::isEmptyInGet('file')) {
    if (file_exists($dir . '/' . url::getToAny('file'))) {

        $pdo = pdo_connect();
        $name = htmlentities($_GET['file']);
        $sql = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "dashboard.yaml_configurations WHERE file_name=?");
        $sql->execute([$name]);
        $data = $sql->fetch();

        if (safe_sizeof($data) > 0 || array_key_exists("file_name", $data)) {
            $configurations = $data['configuration'];
            if (!empty($configurations)) {
                $configurations = safe_json_decode($configurations, true);
            }

            $return = array('status' => true, 'data' => $configurations);
            echo json_encode($return);
            return;
        } else {
            $return = array('status' => false, 'message' => 'file not found');
            echo json_encode($return);
            return;
        }
    } else {
        $return = array('status' => false, 'message' => 'file not found');
        echo json_encode($return);
        return;
    }
}

$pdo = pdo_connect();
$userId = $_SESSION['user']['userid'];
$files = '';
$sql = "SELECT d.*,u.username,u1.username as modified_user_username FROM
            (
                    SELECT * FROM " . $GLOBALS['PREFIX'] . "dashboard.yaml_configurations WHERE user_id=? UNION SELECT * FROM " . $GLOBALS['PREFIX'] . "dashboard.yaml_configurations WHERE scope='1' ORDER BY id DESC
            ) as d
            LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Users u on (d.user_id=u.userid)
            LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Users u1 on (d.modified_by=u1.userid)";
$sql = $pdo->prepare($sql);
$sql->execute([$userId]);
$data = $sql->fetchAll(PDO::FETCH_ASSOC);
$files = [];
if (safe_sizeof($data) > 0) {
    $ii = 1;
    foreach ($data as $eachConfigs) {
        $configuration = safe_json_decode($eachConfigs['configuration'], true);
        $type = isset($configuration['nhtype']) ? $configuration['nhtype'] : '';
        $restrictionField = ($type == 'Compliance') ? 'compliance-type' : 'notification-type';
        $isOwner = (is_numeric($eachConfigs['user_id']) && intval($eachConfigs['user_id']) == intval($userId)) ? true :  false;
        $disabled = $isOwner ? '' : ' disabled="disabled"';
        $input = '<input type="checkbox" value="' . $eachConfigs['id'] . '" class="configuration_list_items"' . $disabled . ' />';
        $isOwner = $isOwner ? 'true' : false;
        $createdBy = isset($eachConfigs['username']) && !is_null($eachConfigs['username']) ?  $eachConfigs['username'] : 'Not available';
        $modifiedBy = isset($eachConfigs['modified_user_username']) && !is_null($eachConfigs['modified_user_username']) ?  $eachConfigs['modified_user_username'] : 'None';
        if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
            $createdtime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], strtotime($eachConfigs['created_at']), "m/d/Y h:i A");
            $modifiedtime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], strtotime($eachConfigs['updated_at']), "m/d/Y h:i A");
        } else {
            $createdtime = date("m/d/Y h:i A", strtotime($eachConfigs['created_at']));
            $modifiedtime = date("m/d/Y h:i A", strtotime($eachConfigs['updated_at']));
        }
        $files[] = ['id' => $input . '<span class="dt-its" data-isowner="' . $isOwner . '">' . $ii . '</span>', 'name' => $eachConfigs['file_name'], 'type' => $type, 'created_by' => $createdBy, 'modified_by' => $modifiedBy, 'created' => $createdtime, 'modified' => $modifiedtime];
        $ii++;
    }
}

$return = array('data' => $files);
echo json_encode($return);
