<?php


require_once '../include/common_functions.php';
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';

$permission = checkModulePrivilege('alertnotification', 2);
if (!$permission) {
    exit(json_encode(array('status' => false, 'message' => 'Permission denied')));
}

//Replace $routes['post'] with if else
if (url::postToText('function') === 'deleteAlertFunc') {    // roles: alertnotification
    deleteAlertFunc();
} else if (url::postToText('function') === 'enableAlertFunc') { // roles: alertnotification
    enableAlertFunc();
} else if (url::postToText('function') === 'disableAlertFunc') { // roles: alertnotification
    disableAlertFunc();
} else if (url::postToText('function') === 'linkAlertFunc') { // roles: alertnotification
    linkAlertFunc();
}



function deleteAlertFunc()
{
    $pdo = pdo_connect();
    $alertName = url::postToAny('alertName');
    $alertId = url::postToAny('alertId');
    $action = url::postToAny('action');

    $notifySel = $pdo->prepare('select * from  ' . $GLOBALS['PREFIX'] . 'event.Notifications where id = ?');
    $notifySel->execute([$alertId]);
    $notifyRes = $notifySel->fetch(PDO::FETCH_ASSOC);
    if (isset($notifyRes['id'])) {
        $updateSel = $pdo->prepare('delete from  ' . $GLOBALS['PREFIX'] . 'event.Notifications where id = ?');
        $notifyuRes = $updateSel->execute([$alertId]);

        $updatecosnoleSel = $pdo->prepare('delete from  ' . $GLOBALS['PREFIX'] . 'event.Console where nid = ? and name = ?');
        $consoleuRes = $updatecosnoleSel->execute([$alertId, $alertName]);
        if ($notifyuRes) {
            $auditRes = create_auditLog('Delete Alert configuration', 'Delete', 'Success');
            echo json_encode(array('status' => 'true', 'message' => "Notification deleted successfully"));
        } else {
            $auditRes = create_auditLog('Delete Alert configuration', 'Delete', 'Failed');
            echo json_encode(array('status' => 'error', 'message' => "Notification delete action failed"));
        }
    }
}

function enableAlertFunc()
{
    $pdo = pdo_connect();
    $alertId = url::postToAny('alertId');
    $action = url::postToAny('action');

    $notifySel = $pdo->prepare('select * from  ' . $GLOBALS['PREFIX'] . 'event.Notifications where id = ?');
    $notifySel->execute([$alertId]);
    $notifyRes = $notifySel->fetch(PDO::FETCH_ASSOC);
    if (isset($notifyRes['id'])) {
        $updateSel = $pdo->prepare('update  ' . $GLOBALS['PREFIX'] . 'event.Notifications set enabled = 1,modified=? where id = ?');
        $notifyuRes = $updateSel->execute([time(), $alertId]);
        if ($notifyuRes) {
            $auditRes = create_auditLog('Enable Alert configuration', 'Enable', 'Success');
            echo json_encode(array('status' => 'true', 'message' => "Notification enabled successfully"));
        } else {
            $auditRes = create_auditLog('Enable Alert configuration', 'Enable', 'Failed');
            echo json_encode(array('status' => 'error', 'message' => "Notification enable action failed"));
        }
    }
}

function disableAlertFunc()
{
    $pdo = pdo_connect();
    $alertId = url::postToAny('alertId');

    $notifySel = $pdo->prepare('select * from  ' . $GLOBALS['PREFIX'] . 'event.Notifications where id = ?');
    $notifySel->execute([$alertId]);
    $notifyRes = $notifySel->fetch(PDO::FETCH_ASSOC);
    if (isset($notifyRes['id'])) {
        $updateSel = $pdo->prepare('update  ' . $GLOBALS['PREFIX'] . 'event.Notifications set enabled = 0,modified=? where id = ?');
        $notifyuRes = $updateSel->execute([time(), $alertId]);
        if ($notifyuRes) {
            $auditRes = create_auditLog('Disable Alert configuration', 'Disabled', 'Success');
            echo json_encode(array('status' => 'true', 'message' => "Notification disabled successfully"));
        } else {
            $auditRes = create_auditLog('Disable Alert configuration', 'Disabled', 'Failed');
            echo json_encode(array('status' => 'error', 'message' => "Notification disable action failed"));
        }
    }
}

function notEmptyString($var)
{
    return !empty($var);
}


function linkAlertFunc()
{
    // $pdo = pdo_connect();

    $siteName = url::postToText('site');
    $notifications = url::postToText('notifications');

    $notify  = explode(',', $notifications);

    $AllNotification = NanoDB::find_many('select * from  ' . $GLOBALS['PREFIX'] . 'event.Notifications');
    foreach ($AllNotification as $notification) {

        $group_include_array = explode(',', $notification['group_include']);

        $group_include_array_new = array_diff($group_include_array, [$siteName]);
        $group_include_array_new = array_filter($group_include_array_new, "notEmptyString");
        if (count($group_include_array) > count($group_include_array_new)) {
            NanoDB::query(
                'update  ' . $GLOBALS['PREFIX'] . 'event.Notifications set group_include = ?, modified=? where id = ?',
                [join(',', $group_include_array_new), time(), $notification['id']]
            );
        }
    }

    foreach ($notify as $notification) {
        $siteRes = NanoDB::find_one('select * from  ' . $GLOBALS['PREFIX'] . 'event.Notifications where id = ?', null, [$notification]);
        $group_include = $siteRes['group_include'];
        $group_include_array = explode(',', $siteRes['group_include']);

        if ($siteName == 'All' || $group_include == 'All') {
            $group_include_array = ['All'];
        } else  if (in_array($siteName, $group_include_array)) {
            continue;
        } else {
            $group_include_array[] = $siteName;
        }

        $group_include_array = array_filter($group_include_array, "notEmptyString");

        NanoDB::query(
            'update  ' . $GLOBALS['PREFIX'] . 'event.Notifications set group_include = ?, modified=? where id = ?',
            [join(',',  $group_include_array), time(), $notification]
        );
    }

    // redis update
    $rc = RedisLink::connect();
    $rc->delete('event.Notifications.lastModifiedCheck');

    create_auditLog('Alert configuration linking', 'Link', 'Success');
    echo json_encode(array('status' => 'success', 'message' => "Notification linked successfully"));
}
