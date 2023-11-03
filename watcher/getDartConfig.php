<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';

$permission = checkModulePrivilege('alertnotification', 2);
if (!$permission) {
    exit(json_encode(array('status' => false, 'message' => 'Permission denied')));
}



if (url::issetInPost('function')) { // roles: alertnotification
    $function = url::postToText('function');  // roles: alertnotification

    if ($function == 'getDartConfiguration') {
        $dart = url::postToStringAz09('dart');
        $function($pdo, $dart);
    } else {

        $dart = url::postToStringAz09('dart');
        $dartSel =  url::postToStringAz09('dartSel');
        $function($pdo, $dart, $dartSel);
    }
}

function getDartConfiguration($pdo, $dart)
{
    $dartconfigres = NanoDB::find_many("select distinct(dartNo),dartName from  " . $GLOBALS['PREFIX'] . "event.dart_config");


    $dartListData = '';

    if (safe_count($dartconfigres) > 0) {
        $dartFinded = false;
        foreach ($dartconfigres as $index => $value) {
            $selected = '';
            if ($dart == $value['dartNo']) {
                $selected = 'selected';
                $dartFinded = true;
            }
            // if ($dart != '') {
            //     $dartcount = NanoDB::find_many("select * from  " . $GLOBALS['PREFIX'] . "event.dart_config where id = ? and dartNo = ?", null, [$dart, $value['dartNo']]);
            //     if (safe_count($dartcount) > 0 && $value['dartName'] == $dartcount[0]['dartName']) {
            //         $selected = 'selected';
            //     }
            // } else if ($dart == '') {
            //     if ($index == 0) {
            //         $selected = 'selected';
            //     }
            // }

            $dartListData .= '<option value="' . $value['dartNo'] . '" ' . $selected . '> Dart ' . $value['dartNo'] . ' - ' . $value['dartName'] . '</option>';
        }

        if (!$dartFinded) {
            $dartListData .= '<option value="" disabled="true" selected >Not selected</option>';
        }
    } else {
        $dartListData = '<option value="" disabled="true" >No Dart Available</option>';
    }


    echo json_encode(array("status" => "success", "dartlist" => $dartListData));
}

function getDartConfigurationDet($pdo, $dart, $dartSel)
{
    $dartconfigres = NanoDB::find_many("select * from  " . $GLOBALS['PREFIX'] . "event.dart_config where dartNo = ?", null, [$dart]);

    $dartListData = '';
    $dartconfig = array();
    if (safe_count($dartconfigres) > 0) {

        foreach ($dartconfigres as $index => $value) {
            if (!in_array($value['dartConfig'], $dartconfig)) {
                $selected = '';
                if ($dartSel == $value['id']) {
                    $selected = 'selected';
                } else if ($dart == '') {

                    if ($index == 0) {
                        $selected = 'selected';
                    }
                }
                $dartconfig[] = $value['dartConfig'];
                $dartListData .= '<option value="' . $value['id'] . '" ' . $selected . '>' . $value['dartConfig'] . '</option>';
            }
        }
    } else {
        $dartListData = '<option value="">No Criteria Available</option>';
    }


    echo json_encode(array("status" => "success", "dartlistdet" => $dartListData));
}
