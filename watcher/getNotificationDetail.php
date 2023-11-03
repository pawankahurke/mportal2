<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
require_once '../include/common_functions.php';

$permission = checkModulePrivilege('alertnotification', 2);
if (!$permission) {
    exit(json_encode(array('status' => false, 'message' => 'Permission denied')));
}

$db = pdo_connect();
$watherName = url::issetInRequest('alertId') ? url::requestToAny('alertId') : '';

if ($watherName === '') {
    $return = array('status' => false, 'message' => 'Something went wrong');
    echo json_encode($return);
    return;
}

try {

    $sql = "SELECT d.id,d.name,d.username,d.ntype,d.priority,d.criteria,d.compCriteria,d.scrip,d.enabled,d.dartConfigId,d.plain_txt, d.alertConfig from  " . $GLOBALS['PREFIX'] . "event.Notifications d WHERE d.id= ? ";
    $pdo = $db->prepare($sql);
    $pdo->execute([$watherName]);
    $res = $pdo->fetch(PDO::FETCH_ASSOC);
    if ($pdo->rowCount() > 0) {
        if (strpos($res['plain_txt'], '&&') !== false) {
            $plainandSplit = explode(' && ', $res['plain_txt']);
            $plainSplit1 = explode(' ', $plainandSplit[0]);
            $plainSplit2 = explode(' ', $plainandSplit[1]);
            if (safe_count($plainSplit1) > 0 && safe_count($plainSplit2) > 0) {
                $res['logicalopt'] = 'range';
                $res['dartvalue1'] = $plainSplit1[2];
                $res['dartvalue2'] = $plainSplit2[2];
            }
        } else {
            $plainSplit = explode(' ', $res['plain_txt']);
            if (safe_count($plainSplit) > 0) {
                $res['logicalopt'] = $plainSplit[1];
                $res['dartvalue1'] = str_replace('_', ' ', $plainSplit[2]);
                $res['dartvalue2'] = '';
            }
        }

        $criteria = safe_json_decode($res['criteria'], true);
        if (!is_array($criteria)) {
            $criteria = [];
        }
        $comCriteria = !empty($res['compCriteria']) ? safe_json_decode($res['compCriteria'], true) : array();

        $resCriteria = array_merge($criteria, $comCriteria);
        $res['criteria'] = json_encode($resCriteria);


        try {
            $alertConfig = $res['alertConfig'];
            $res['alertConfig'] = safe_json_decode($res['alertConfig'], true);
        } catch (Exception $e) {
            $res['alertConfig'] = [];
        }


        $return = array('status' => true, 'res' => $res);
    } else {
        $return = array('status' => false, 'message' => 'Something went wrong');
    }

    echo json_encode($return);
} catch (Exception $e) {
    logs::log(__FILE__, __LINE__, $e, 0);
    $return = array('status' => false, 'message' => 'Something went wrong');
    echo json_encode($return);
    return;
}
