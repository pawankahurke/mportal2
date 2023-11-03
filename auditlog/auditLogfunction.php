<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
include_once '../lib/l-db.php';

nhRole::dieIfnoRoles(['user']);

if (url::postToText('function') === 'LOG_getLogDetails') { //roles: user
    LOG_getLogDetails();
}

function LOG_getLogDetails() {
    $db = pdo_connect();
    $limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
    $curPage = url::postToInt('nextPage') - 1;
    $orderVal = url::postToStringAz09('order');
    $sortVal = url::postToStringAz09('sort');

    $limitStart = $limitCount * $curPage;

    $limitEnd = $limitStart + $limitCount;
    if ($limitStart > 0) {
        $limitStr = " LIMIT ".$limitStart.",".$limitCount;
    } else {
        $limitStr = " LIMIT ".$limitStart.",".$limitEnd;
    }

    if ($orderVal != '') {
        $orderStr = 'order by ' . $orderVal . ' '. $sortVal;
    } else {
        $orderStr = 'order by created desc';
    }


    $draw = 1;
    $result = AUDITLOG_getData($db, $limitStr, $orderStr);
    $result = safe_json_decode($result,true);
    $totCount = $result['count'];
    $data = $result['result'];

    if (safe_sizeof($data) == 0) {
        $dataArr['largeDataPaginationHtml'] =  '';
        $dataArr['html'] =   '';
        echo json_encode($dataArr);
    } else {
        $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage);
        $dataArr['html'] = Format_notificationDataMysql($db, $data);
        echo json_encode($dataArr);
    }

}

function Format_notificationDataMysql($db, $result) {
    $i = 0;
    foreach ($result as $key => $row) {
        $uName = $row['username'];
        $mail = $row['useremail'];
        $module = $row['module'];
        $action = $row['action'];
        $ip = $row['ip'];
        $refName = $row['refName'];
        $publicip= $row['ip'];
        $browser= $row['agent'];
        $newbrowser =explode("-", $browser);
        $browser= $newbrowser[0];
        $time = $logintime = date('m/d/Y H:i:s', strtotime($row['created']));

        $userId = $_SESSION['user']['userid'];
        $sql = $db->prepare("SELECT * FROM ".$GLOBALS['PREFIX']."core.Users where userid=?");
        $sql->execute([$userId]);
        $SqlRes = $sql->fetch();
        $userTimeZone = !empty($SqlRes['timezone']) ? $SqlRes['timezone'] : 'UTC';

        date_default_timezone_set('UTC');
        $datetime = new DateTime(strval($row['created']));
        $time = $datetime->format('m/d/Y H:i:s');
        $la_time = new DateTimeZone($userTimeZone);
        $datetime->setTimezone($la_time);
        $logintime = $datetime->format('m/d/Y H:i:s');
        $rname = '';
        if (isset($refName)) {
            $rname = $refName;
        } else {
            $rname = 'NA';
        }

        $stat = $row['status'];
        $id = $row['audit_id'];

        $recordList[$i][] = '<p id = "' . $action . '" class="ellipsis" title="' . $action . '">' . $action . '</p>';
        $recordList[$i][] = '<p id = "' . $module . '" class="ellipsis" title="' . $module . '">' . $module . '</p>';
        $recordList[$i][] = '<p id = "' . $publicip . '" class="ellipsis" title="' . $publicip . '">' . $publicip . '</p>';
        $recordList[$i][] = '<p id = "' . $browser . '" class="ellipsis" title="' . $browser . '">' . $browser . '</p>';
        $recordList[$i][] = '<p id = "' . $uName . '" class="ellipsis" title="' . $uName . '">' . $uName . '</p>';
        $recordList[$i][] = '<p id = "' . $mail . '" class="ellipsis" title="' . $mail . '">' . $mail . '</p>';
        $recordList[$i][] = '<p id = "' . $logintime . '" class="ellipsis" title="' . $logintime . '">' . $logintime . '</p>';
        $recordList[$i][] = '<p id = "' . $time . '" class="ellipsis" title="' . $time . '">' . $time . '</p>';
        $recordList[$i][] = '<p id = "' . $rname . '" class="ellipsis" title="' . $rname . '">' . $rname . '</p>';
        $recordList[$i][] = '<p id = "' . $stat . '" class="ellipsis" title="' . $stat . '">' . $stat . '</p>';
        $recordList[$i][] = $id;
        $i++;
    }
    return $recordList;
}

function AUDITLOG_getData($db, $limitStr, $orderStr) {

    $notifSearch = url::postToText('notifSearch');
    if ($notifSearch != '') {
        $notifSearch = strtolower($notifSearch);
            $whereSearch = " and  (module LIKE '%" . $notifSearch . "%'
            OR action LIKE '%" . $notifSearch . "%'
            OR username LIKE '%" . $notifSearch . "%'
            OR useremail LIKE '%" . $notifSearch . "%'
            OR ip	 LIKE '%" . $notifSearch . "%'
            OR status LIKE '%" . $notifSearch . "%'
            OR agent LIKE '%" . $notifSearch . "%'
            OR refName LIKE '%" . $notifSearch . "%'
            ) ";
    }else {
        $whereSearch = '';
    }

    $retArr = array();
    $userId = $_SESSION['user']['userid'];
    $roleId = $_SESSION['user']['role_id'];
    $sql = $db->prepare("SELECT user_email FROM ".$GLOBALS['PREFIX']."core.Users where userid=?");
    $sql->execute([$userId]);
    $SqlRes = $sql->fetch(PDO::FETCH_ASSOC);
    $userEmail = $SqlRes['user_email'];
    $filterStr = '';
    $usrArr = getChildDetails($userId, 'user_email');
    $usrArrData = array_merge([$userEmail], $usrArr);

    $last30days = date('Y-m-d H:i:s', strtotime('-30 Days'));
    $in = str_repeat('?,', safe_count($usrArrData) - 1) . '?';
    // if ((int) $roleId != 96) {
        $filterStr = "useremail in($in) ";
    // }
    $sql2 = $db->prepare("SELECT * FROM ".$GLOBALS['PREFIX']."core.AuditLog WHERE " . $filterStr . " $whereSearch and created >= ? $orderStr $limitStr");

    if ($filterStr != '') {
        $params = array_merge($usrArrData, [$last30days]);
    } else {
        $params = array_merge([$last30days]);
    }
    $sql2->execute($params);
    $result = $sql2->fetchAll(PDO::FETCH_ASSOC);

    $sqlCount = $db->prepare("SELECT * FROM ".$GLOBALS['PREFIX']."core.AuditLog WHERE " . $filterStr . " $whereSearch and created >= ? $orderStr");
    $sqlCount->execute($params);
    $count = safe_count($sqlCount->fetchAll(PDO::FETCH_ASSOC));

    $retArr['result'] = $result ;
    $retArr['count'] = $count;
    return json_encode($retArr);
}
