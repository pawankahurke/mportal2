<?php




include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
require_once '../include/common_functions.php';

function createAlertConfiguration()
{
    global $HTTP_HOST;
    $db = pdo_connect();

    $alertname = url::issetInRequest('alertname') ? url::requestToAny('alertname') : '';

    $alertsite_txt = '';

    if (url::issetInRequest('alertsite')) {
        foreach (url::requestToAny('alertsite') as $siteVal) {
            $alertsite_txt .= $siteVal . ",";
        }
    }

    $alertsite = rtrim($alertsite_txt, ",");

    $alertglobal = url::issetInRequest('alertglobal') ? url::requestToAny('alertglobal') : '';
    $global = 0;
    if ($alertglobal) {
        $global = 1;
    }

    $alerttype = url::issetInRequest('alerttype') ? url::requestToAny('alerttype') : '';

    $compname = url::issetInRequest('compliance-name') ? url::requestToAny('compliance-name') : '';
    $compcategory = url::issetInRequest('compliance-category') ? url::requestToAny('compliance-category') : '';
    $compitem = url::issetInRequest('compliance-item') ? url::requestToAny('compliance-item') : '';

    $priority = url::issetInRequest('notif-priority') ? url::requestToAny('notif-priority') : '';

    $querystring = url::issetInRequest('query-string') ? url::requestToAny('query-string') : '';
    $index_name = url::issetInRequest('index-name') ? url::requestToAny('index-name') : '';
    $numofevents = url::issetInRequest('number-of-events') ? url::requestToAny('number-of-events') : '';
    $timeframetype = url::issetInRequest('time-frame-type') ? url::requestToAny('time-frame-type') : '';
    $timeframevalue = url::issetInRequest('time-frame-value') ? url::requestToAny('time-frame-value') : '';
    $username = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : '';

    $core_pot = '*';

    $sql = "select O.value from " . $GLOBALS['PREFIX'] . "core.Options O where O.name = ?";
    $pdo = $db->prepare($sql);
    $pdo->execute(['indices_value']);
    $res = $pdo->fetch();
    if ($pdo->rowCount() > 0) {
        $core_pot = $res['value'];
    }
    $indexname = $index_name . $core_pot;

    $body = '';
    if ($alerttype == 'Compliance') {
        $watherName = 'comp_' . $alertname . '_' . mt_rand(100000, 999999);
        $body = '{"nhtype":"compliance","reset":"0","machine":"{{ctx.payload.aggregations.nmachine.buckets}}","ctime":"{{ctx.execution_time}}","text1":{' . $querystring . '},"nocName":"' . $alertname . '","text2":"","username":"' . $username . '","name":"' . $alertname . '","itemtype":"' . $compitem . '","category":"' . $compcategory . '","count":"' . $numofevents . '"}';
    } else if ($alerttype == 'Notification') {
        $watherName = 'Notif_' . $alertname . '_' . mt_rand(100000, 999999);
        $body = '{"nhtype":"notification","machine":"{{ ctx.payload.aggregations.nmachine.buckets }}","priority":"' . $priority . '","ctime":"{{ctx.execution_time }}","text1":{' . $querystring . '},"nocName":"' . $alertname . '","nocStatus":"2","dartExecutionStat":"default","solutionPush":"default","action":"default","notes":"default","text2":""}';
    } else {
        $body = '';
    }


    $alertjson = '';

    $alertjson .= '{
                    "trigger": {
                        "schedule": {
                            "interval": "' . $timeframevalue . $timeframetype . '"
                        }
                    },
                    "input": {
                        "search": {
                            "request": {
                                "search_type": "query_then_fetch",
                                "indices": [
                                    "' . $indexname . '"
                                ],
                                "rest_total_hits_as_int": true,
                                "body": {
                                    "size": 0,
                                    "query": {
                                        "bool": {
                                                  ' . $querystring . ',
                                            "filter": {
                                                "range": {
                                                    "@timestamp": {
                                                        "gte": "{{ctx.trigger.scheduled_time}}||-' . $timeframevalue . $timeframetype . '",
                                                        "lte": "{{ctx.trigger.scheduled_time}}",
                                                        "format": "strict_date_optional_time||epoch_millis"
                                                    }
                                                }
                                            }
                                        }
                                    },
                                    "aggs": {
                                        "nmachine": {
                                            "terms": {
                                                "field": "site.keyword"
                                            },
                                                "aggs":{
                                                "nsite":{
                                                    "terms":{
                                                        "field":"machine.keyword"
                                                        }
                                            }
                                    }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "condition": {
                        "compare": {
                            "ctx.payload.hits.total": {
                                "gte": 1
                            }
                        }
                    },
                    "actions": {
                        "ps_webhook": {
                            "throttle_period_in_millis": 180000,
                            "webhook": {
                                "scheme": "https",
                                "host": "' . $HTTP_HOST . '",
                                "port": 443,
                                "method": "post",
                                "path": "/Dashboard/elast/elastwatcher.php",
                                "params": {},
                                "headers": {},
                                "body":
                                        ' . json_encode($body) . '
                            }
                        }
                    }
                }';
    try {

        $currentTimestamp = time();
        $userId = $_SESSION['user']['userid'];

        $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "dashboard.alertConfiguration (alertname, global, site, alerttype ,compname, compcategory, compitem, configuration, indexname"
            . ", numevent, timeframetype, timeframevalu, userid, createdtime,watcherId) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $alertInfo = array($alertname, $global, $alertsite, $alerttype, $compname, $compcategory, $compitem, $querystring, $indexname, $numofevents, $timeframetype, $timeframevalue, $userId, $currentTimestamp, $watherName);
        $pdo_prepare = $db->prepare($sql);
        $pdo_prepare->execute($alertInfo);

        $auditRes = create_auditLog('Create Alert configuration', 'Create', 'Success');

        if ($pdo_prepare->rowCount() > 0) {
            $alertid = $db->lastInsertId();
            $empty = '';
            $notifstmt = $db->prepare("INSERT INTO  " . $GLOBALS['PREFIX'] . "event.Notifications (global, priority, name, username, email, threshold, enabled, created, "
                . "emaillist, group_include, group_exclude, group_suspend, config, machines, excluded, email_footer_txt, profile_name, search_id) "
                . "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $notifstmt->execute([
                $global, $priority, $alertname, $username, 0, $numofevents, 1, time(), $empty, $empty, $empty, $empty, $empty,
                $empty, $empty, $empty, $empty, $alertid
            ]);

            curl_put_watch($alertjson, $watherName);
            $return = array('status' => true);
        } else {
            $auditRes = create_auditLog('Create Alert configuration', 'Create', 'Failed');
            $return = array('status' => false, 'message' => 'Something went wrong');
        }

        echo json_encode($return);
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        $auditRes = create_auditLog('Create Alert configuration', 'Create', 'Failed');
        $return = array('status' => false, 'message' => 'Something went wrong');
        echo json_encode($return);
        return;
    }
}

function curl_put_watch($alertjson, $watherName)
{
    logs::trace(1, "Error:CodeRemoved");
}

function deleteAlertConfiguration()
{
    $db = pdo_connect();

    $watherName = url::issetInRequest('watcherId') ? url::requestToAny('watcherId') : '';

    if ($watherName === '') {
        $return = array('status' => false, 'message' => 'Something went wrong');
        echo json_encode($return);
        return;
    }

    try {
        $ckalertstmt = $db->prepare("SELECT id, alertname from " . $GLOBALS['PREFIX'] . "dashboard.alertConfiguration WHERE watcherId = ?");
        $ckalertstmt->execute([$watcherName]);
        $ckalertdata = $ckalertstmt->fetch(PDO::FETCH_ASSOC);

        $sql = "DELETE FROM " . $GLOBALS['PREFIX'] . "dashboard.alertConfiguration WHERE watcherId=?";
        $pdo = $db->prepare($sql);
        $pdo->execute([$watherName]);

        if ($pdo->rowCount() > 0) {
            $notifstmt = $db->prepare("delete from  " . $GLOBALS['PREFIX'] . "event.Notifications where search_id = ? and name = ?");
            $notifstmt->execute([$ckalertdata['id'], $ckalertdata['alertname']]);

            $auditRes = create_auditLog('Delete Alert configuration', 'Deletion', 'Success');
            curl_delete_watch($watherName);
            $return = array('status' => true, 'message' => 'Successfully deleted the record.');
        } else {
            $auditRes = create_auditLog('Delete Alert configuration', 'Deletion', 'Failed');
            $return = array('status' => false, 'message' => 'Something went wrong');
        }

        echo json_encode($return);
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        $auditRes = create_auditLog('Delete Alert configuration', 'Deletion', 'Failed');
        $return = array('status' => false, 'message' => 'Something went wrong');
        echo json_encode($return);
        return;
    }
}

function curl_delete_watch($watherName)
{

    logs::log(__FILE__, __LINE__, "Error:CodeRemoved");
}

function getAlertDetails()
{
    $db = pdo_connect();

    $watherName = url::issetInRequest('watcherId') ? url::requestToAny('watcherId') : '';

    if ($watherName === '') {
        $return = array('status' => false, 'message' => 'Something went wrong');
        echo json_encode($return);
        return;
    }

    try {

        $sql = "SELECT d.alertname,d.site,d.alerttype,d.compname,d.compcategory,d.compitem,d.configuration,d.indexname,d.numevent,d.timeframetype,d.timeframevalu from " . $GLOBALS['PREFIX'] . "dashboard.alertConfiguration d WHERE watcherId= ? ";
        $pdo = $db->prepare($sql);
        $pdo->execute([$watherName]);
        $res = $pdo->fetch();
        if ($pdo->rowCount() > 0) {
            $auditRes = create_auditLog('View Alert configuration', 'View', 'Success');
            $return = array('status' => true, 'res' => $res);
        } else {
            $auditRes = create_auditLog('View Alert configuration', 'View', 'Failed');
            $return = array('status' => false, 'message' => 'Something went wrong');
        }

        echo json_encode($return);
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        $auditRes = create_auditLog('View Alert configuration', 'View', 'Failed');
        $return = array('status' => false, 'message' => 'Something went wrong');
        echo json_encode($return);
        return;
    }
}

function modifyAlertConfiguration()
{
    global $HTTP_HOST;
    $db = pdo_connect();

    $alertname = url::issetInRequest('alertnameEditPost') ? url::requestToAny('alertnameEditPost') : '';

    $alertsite_txt = '';

    if (url::issetInRequest('alertsiteEdit')) {
        foreach (url::requestToAny('alertsiteEdit') as $siteVal) {
            $alertsite_txt .= $siteVal . ",";
        }
    }

    $alertsite = rtrim($alertsite_txt, ",");

    $alertglobal = url::issetInRequest('alertglobalEdit') ? url::requestToAny('alertglobalEdit') : '';
    $global = 0;
    if ($alertglobal) {
        $global = 1;
    }

    $alerttype = url::issetInRequest('alerttypeEdit') ? url::requestToAny('alerttypeEdit') : '';

    $compname = url::issetInRequest('compliance-nameEdit') ? url::requestToAny('compliance-nameEdit') : '';
    $compcategory = url::issetInRequest('compliance-categoryEdit') ? url::requestToAny('compliance-categoryEdit') : '';
    $compitem = url::issetInRequest('compliance-itemEdit') ? url::requestToAny('compliance-itemEdit') : '';
    $priority = url::issetInRequest('notif-priorityEdit') ? url::requestToAny('notif-priorityEdit') : '';

    $querystring = url::issetInRequest('query-stringEdit') ? url::requestToAny('query-stringEdit') : '';
    $index_name = url::issetInRequest('index-nameEdit') ? url::requestToAny('index-nameEdit') : '';
    $numofevents = url::issetInRequest('number-of-eventsEdit') ? url::requestToAny('number-of-eventsEdit') : '';
    $timeframetype = url::issetInRequest('time-frame-typeEdit') ? url::requestToAny('time-frame-typeEdit') : '';
    $timeframevalue = url::issetInRequest('time-frame-valueEdit') ? url::requestToAny('time-frame-valueEdit') : '';
    $username = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : '';

    $core_pot = '*';

    $sql = "select O.value from " . $GLOBALS['PREFIX'] . "core.Options O where O.name = ?";
    $pdo = $db->prepare($sql);
    $pdo->execute(['indices_value']);
    $res = $pdo->fetch();
    if ($pdo->rowCount() > 0) {
        $core_pot = $res['value'];
    }
    $indexname = $index_name . $core_pot;

    $watherName = url::issetInRequest('watcherIdEdit') ? url::requestToAny('watcherIdEdit') : '';
    $notifId = url::issetInRequest('alertNotifId') ? url::requestToAny('alertNotifId') : '';

    $body = '';
    if ($alerttype == 'Compliance') {
        $body = '{"nhtype":"compliance","reset":"0","machine":"{{ctx.payload.aggregations.nmachine.buckets}}","ctime":"{{ctx.execution_time}}","text1":{' . $querystring . '},"nocName":"' . $alertname . '","text2":"","username":"' . $username . '","name":"' . $alertname . '","itemtype":"' . $compitem . '","category":"' . $compcategory . '","count":"' . $numofevents . '"}';
    } else if ($alerttype == 'Notification') {
        $body = '{"nhtype":"notification","machine":"{{ ctx.payload.aggregations.nmachine.buckets }}","priority":"' . $priority . '","ctime":"{{ctx.execution_time }}","text1":{' . $querystring . '},"nocName":"' . $alertname . '","nocStatus":"2","dartExecutionStat":"default","solutionPush":"default","action":"default","notes":"default","text2":""}';
    } else {
        $body = '';
    }

    $alertjson = '';

    $alertjson .= '{
                    "trigger": {
                        "schedule": {
                            "interval": "' . $timeframevalue . $timeframetype . '"
                        }
                    },
                    "input": {
                        "search": {
                            "request": {
                                "search_type": "query_then_fetch",
                                "indices": [
                                    "' . $indexname . '"
                                ],
                                "rest_total_hits_as_int": true,
                                "body": {
                                    "size": 0,
                                    "query": {
                                        "bool": {
                                                    ' . $querystring . ',
                                            "filter": {
                                                "range": {
                                                    "@timestamp": {
                                                        "gte": "{{ctx.trigger.scheduled_time}}||-' . $timeframevalue . $timeframetype . '",
                                                        "lte": "{{ctx.trigger.scheduled_time}}",
                                                        "format": "strict_date_optional_time||epoch_millis"
                                                    }
                                                }
                                            }
                                        }
                                    },
                                    "aggs": {
                                        "nmachine": {
                                            "terms": {
                                                "field": "site.keyword"
                                            },
                                                "aggs":{
                                                "nsite":{
                                                    "terms":{
                                                        "field":"machine.keyword"
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "condition": {
                        "compare": {
                            "ctx.payload.hits.total": {
                                "gte": 1
                            }
                        }
                    },
                    "actions": {
                        "ps_webhook": {
                            "throttle_period_in_millis": 180000,
                            "webhook": {
                                "scheme": "https",
                                "host": "' . $HTTP_HOST . '",
                                "port": 443,
                                "method": "post",
                                "path": "/Dashboard/elast/elastwatcher.php",
                                "params": {},
                                "headers": {},
                                "body":
                                        ' . json_encode($body) . '
                            }
                        }
                    }
                }';
    try {

        $currentTimestamp = time();
        $userId = $_SESSION['user']['userid'];

        $sql = "UPDATE " . $GLOBALS['PREFIX'] . "dashboard.alertConfiguration SET  alertname = ?, global = ?, site = ?, alerttype = ?, compname = ?, compcategory = ?, compitem = ?, configuration = ?, indexname"
            . " = ?, numevent = ?, timeframetype = ?, timeframevalu = ?, modifiedUserid = ?, modifiedtime = ? where watcherId = ? ";
        $alertInfo = array($alertname, $global, $alertsite, $alerttype, $compname, $compcategory, $compitem, $querystring, $indexname, $numofevents, $timeframetype, $timeframevalue, $userId, $currentTimestamp, $watherName);
        $pdo_prepare = $db->prepare($sql);
        $sqlres = $pdo_prepare->execute($alertInfo);

        if ($sqlres == true) {
            if ($alerttype == 'Notification') {
                $cknotifstmt = $db->prepare("select count(id) as notifcnt from  " . $GLOBALS['PREFIX'] . "event.Notifications where search_id = ? and name = ?");
                $cknotifstmt->execute([$notifId, $alertname]);
                $cknotifdata = $cknotifstmt->fetch(PDO::FETCH_ASSOC);

                if ($cknotifdata['notifcnt'] > 0) {
                    $notifstmt = $db->prepare("update  " . $GLOBALS['PREFIX'] . "event.Notifications set priority = ?, global = ? where search_id = ? and name = ?");
                    $notifstmt->execute([$priority, $global, $notifId, $alertname]);
                } else {
                    $empty = '';
                    $notifstmt = $db->prepare("INSERT INTO  " . $GLOBALS['PREFIX'] . "event.Notifications (global, priority, name, username, email, threshold, enabled, created, "
                        . "emaillist, group_include, group_exclude, group_suspend, config, machines, excluded, email_footer_txt, profile_name, search_id) "
                        . "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $notifstmt->execute([
                        $global, $priority, $alertname, $username, 0, $numofevents, 1, time(), $empty, $empty, $empty, $empty, $empty,
                        $empty, $empty, $empty, $empty, $notifId
                    ]);
                }
            }
            $auditRes = create_auditLog('Modify Alert configuration', 'Modify', 'Success');
            curl_put_watch($alertjson, $watherName);
            $return = array('status' => true);
        } else if ($sqlres == false) {
            $auditRes = create_auditLog('Modify Alert configuration', 'Modify', 'Failed');
            $return = array('status' => false, 'message' => 'Something went wrong');
        }
        echo json_encode($return);
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        $auditRes = create_auditLog('Modify Alert configuration', 'Modify', 'Failed');
        $return = array('status' => false, 'message' => 'Something went wrong');
        echo json_encode($return);
        return;
    }
}

function enableAlertConfiguration()
{
    $pdo = pdo_connect();

    $watherName = url::issetInRequest('watcherId') ? url::requestToAny('watcherId') : '';

    if ($watherName === '') {
        $return = array('status' => false, 'message' => 'Something went wrong');
        echo json_encode($return);
        return;
    }

    try {

        $sql = "UPDATE " . $GLOBALS['PREFIX'] . "dashboard.alertConfiguration SET  status = ?  where watcherId = ? ";
        $alertInfo = array("1", $watherName);
        $pdo_prepare = $pdo->prepare($sql);
        $sqlres = $pdo_prepare->execute($alertInfo);

        if ($sqlres == true) {
            $auditRes = create_auditLog('Enable Alert configuration', 'Enable', 'Success');
            curl_ChangeStatus_watch("_activate?pretty", $watherName);
            $return = array('status' => true, 'message' => 'Successfully enabled the record.');
        } else if ($sqlres == false) {
            $auditRes = create_auditLog('Enable Alert configuration', 'Enable', 'Failed');
            $return = array('status' => false, 'message' => 'Something went wrong');
        }

        echo json_encode($return);
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        $auditRes = create_auditLog('Enable Alert configuration', 'Enable', 'Failed');
        $return = array('status' => false, 'message' => 'Something went wrong');
        echo json_encode($return);
        return;
    }
}

function disableAlertConfiguration()
{
    $pdo = pdo_connect();

    $watherName = url::issetInRequest('watcherId') ? url::requestToAny('watcherId') : '';

    if ($watherName === '') {
        $return = array('status' => false, 'message' => 'Something went wrong');
        echo json_encode($return);
        return;
    }

    try {

        $sql = "UPDATE " . $GLOBALS['PREFIX'] . "dashboard.alertConfiguration SET  status = ?  where watcherId = ? ";
        $alertInfo = array("0", $watherName);
        $pdo_prepare = $pdo->prepare($sql);
        $sqlres = $pdo_prepare->execute($alertInfo);

        if ($sqlres == true) {
            $auditRes = create_auditLog('Disable Alert configuration', 'Disable', 'Success');
            curl_ChangeStatus_watch("_deactivate?pretty", $watherName);
            $return = array('status' => true, 'message' => 'Successfully disabled the record.');
        } else if ($sqlres == false) {
            $auditRes = create_auditLog('Disable Alert configuration', 'Disable', 'Failed');
            $return = array('status' => false, 'message' => 'Something went wrong');
        }

        echo json_encode($return);
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        $auditRes = create_auditLog('Disable Alert configuration', 'Disable', 'Failed');
        $return = array('status' => false, 'message' => 'Something went wrong');
        echo json_encode($return);
        return;
    }
}

function curl_ChangeStatus_watch($status, $watherName)
{
    logs::log(__FILE__, __LINE__, "Error:CodeRemoved");
}


$action = url::requestToAny('action');

switch ($action) {
    case 'createalert':
        createAlertConfiguration();
        break;
    case 'deleteAlert':
        deleteAlertConfiguration();
        break;
    case 'getAlertDetails':
        getAlertDetails();
        break;
    case 'modifyalert':
        modifyAlertConfiguration();
        break;
    case 'enableAlert':
        enableAlertConfiguration();
        break;
    case 'disableAlert':
        disableAlertConfiguration();
        break;
    default:
        break;
}
