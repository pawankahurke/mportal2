<?php
// used for create or update notifications

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
require_once '../include/common_functions.php';
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

include_once '../lib/l-db.php';


$permission = checkModulePrivilege('alertnotification', 2);
if (!$permission) {
  exit(json_encode(array('status' => false, 'message' => 'Permission denied')));
}
$pdo =  pdo_connect();
$user_name = $_SESSION['user']['username'];

$ntoifyname = url::postToText('alertname');
$notifyType = url::postToInt('alerttype');
$priority = url::postToInt('priority');
$scrip = url::postToInt('dart');


$criteria =  url::postToJson('criteria');
$topseconds = url::postToInt('topSeconds');
$alertId = url::postToText('alertId');
$action = url::postToText('action');

$dartConfigId = url::postToInt('dart');
$alertConfig = url::postToJson('dartconfigs');


$searchTxt = "";
$where_txt = "";
$showTxt = "";
$plain_txt = "";




foreach ($alertConfig as $key => $dConf) {
  # code...
  $dartval1 = $dConf['dartvalue1'];
  $dartval2 = $dConf['dartvalue2'];
  $logicalopt = $dConf['logicalopt'];
  $dartconfig = $dConf['dartconfig'];

  $group = '';
  if (isset($dConf['group'])) {
    $group =  $dConf['group'] == 'OR' ? ' || ' : ' && ';
  }


  $dartres = NanoDB::find_one('select * from  ' . $GLOBALS['PREFIX'] . 'event.dart_config where id = ? limit 1', null, [$dartconfig]);
  if (!$dartres) {
    continue;
  }

  // ----------------- //
  $searchTxt .= $group . " ( " . genearte_searchTxt($logicalopt, $dartres['dartConfigsearch_txt'], $dartval1, $dartval2) . " ) ";
  $where_txt .= $group . " ( " . genearte_whereTxt($logicalopt, $dartres['dartConfigwhere_txt'], $dartval1, $dartval2) . " ) ";
  $showTxt .= $group . " ( " . $dartres['dartConfigshow_txt'] . " ) ";
  if ($logicalopt != 'range') {
    $plain_txt .= $group . " ( " . str_replace(' ', '_', $dartres['dartConfig']) . ' ' . $logicalopt . ' ' . str_replace(' ', '_', $dartval1) . " ) ";
  } else {
    $plain_txt .= $group . " ( " . str_replace(' ', '_', $dartres['dartConfig']) . ' > ' . $dartval1 . ' && ' . $dartres['dartConfig'] . ' < ' . $dartval2 . " ) ";
  }
  // ----------------- //
}

// $dartConfigId = json_encode($dartConfigId);
// $logicalopt = json_encode($logicalopt);
// $dartval1 = json_encode($dartval1);


if (!$alertId) {
  $notify = checkNotificationDup($ntoifyname);
  if ($notify == 0) {
    $notifySt = NanoDB::query("insert into  " . $GLOBALS['PREFIX'] . "event.Notifications(
      name,
      username,
      console,
      ntype,
      priority,
      seconds,
      threshold,
      last_run,
      next_run,
      criteria,
      search_txt,
      show_txt,
      where_txt,
      scrip,
      enabled,
      created,
      dartConfigId,
      plain_txt,
      group_include,
      modified,
      alertConfig)
             values(?,?,1,?,?,?,0,0,0,?,?,?,?,?,1,?,?,?,'All', ?, ?);", [
      $ntoifyname,  // name
      $user_name,
      $notifyType,
      $priority,
      $topseconds,
      json_encode($criteria, JSON_NUMERIC_CHECK),
      $searchTxt,
      $showTxt,
      $where_txt,
      $scrip,
      time(),
      $dartConfigId,
      $plain_txt,
      time(),
      json_encode($alertConfig)
    ]);


    if ($notifySt) {
      $auditRes = create_auditLog('Create Alert configuration', 'Create', 'Success');
      echo json_encode(array('status' => 'success', 'message' => "Notification created successfully"));
    } else {
      $auditRes = create_auditLog('Create Alert configuration', 'Create', 'Failed');
      echo json_encode(array('status' => 'error', 'message' => "Notification creation failed"));
    }
  } else {
    $auditRes = create_auditLog('Create Alert configuration', 'Create', 'Failed');
    echo json_encode(array('status' => 'error', 'message' => "Notification with name " . $ntoifyname . " already found"));
  }
} else {


  $notifySt = NanoDB::query(
    "update  " . $GLOBALS['PREFIX'] . "event.Notifications set 
    ntype=?,priority = ?,seconds=?,criteria =?,compCriteria = NULL,search_txt=?,show_txt=?,
    where_txt=?,scrip=?,modified =?,dartConfigId =?,plain_txt =?, alertConfig=? where id = ?;",
    [
      $notifyType, // ntype
      $priority,
      $topseconds,
      json_encode($criteria, JSON_NUMERIC_CHECK),
      $searchTxt,
      $showTxt,
      $where_txt,
      $scrip,
      time(),
      $dartConfigId,
      $plain_txt,
      json_encode($alertConfig),
      $alertId
    ]
  );
  if ($notifySt) {
    $auditRes = create_auditLog('Update Alert configuration', 'Modification', 'Success');
    echo json_encode(array('status' => 'success', 'message' => "Notification updated successfully"));
  } else {
    $auditRes = create_auditLog('Update Alert configuration', 'Modification', 'Failed');
    echo json_encode(array('status' => 'error', 'message' => "Notification update failed"));
  }
}

// redis update
$rc = RedisLink::connect();
$rc->delete('event.Notifications.lastModifiedCheck');

function genearte_searchTxt($logic, $search, $dartvalue1, $dartvalue2)
{
  $searchtxt = '';
  if ($logic == '=') {
    if (trim($dartvalue1 . "") == "0") {
      // In php8 (null == 0) so we set this code
      // https://nanoheal.atlassian.net/browse/NCP-872
      $searchtxt = " ( $search == $dartvalue1 && $search  !== null ) ";
    } else if (is_numeric($dartvalue1)) {
      $searchtxt = " ( $search == $dartvalue1 && $search  !== null ) ";
    } else {
      $searchtxt = " ( $search == '$dartvalue1' && $search  !== null ) ";
    }
  }
  if ($logic == '>') {
    $searchtxt = " ($search !== null && $search  >  $dartvalue1) ";
  }
  if ($logic == '<') {
    $searchtxt = " ($search !== null && $search  <  $dartvalue1) ";
  }
  if ($logic == 'like') {
    // CASE not sensative
    $searchtxt = "strpos(mb_strtolower(''." . $search . "), mb_strtolower('" . $dartvalue1 . "')) !== false";
  }
  if ($logic == 'range') {
    $searchtxt = $search . " > " . $dartvalue1 . " &&  " . $search . " < " . $dartvalue2;
  }

  return $searchtxt;
}

function genearte_whereTxt($logic, $search, $dartvalue1, $dartvalue2)
{
  $wheretxt = '';
  if ($logic == '=') {
    if (is_numeric($dartvalue1)) {
      $wheretxt = $search . " = " . $dartvalue1;
    } else {
      $wheretxt = $search . " = '" . $dartvalue1 . "'";
    }
  }
  if ($logic == '>') {
    $wheretxt = $search . " > " . $dartvalue1;
  }
  if ($logic == '<') {
    $wheretxt = $search . " < " . $dartvalue1;
  }
  if ($logic == 'like') {
    $wheretxt = $search . " like '%" . $dartvalue1 . "%'";
  }
  if ($logic == 'range') {
    $wheretxt = $search . " > " . $dartvalue1 . " and  " . $search . " < " . $dartvalue2;
  }

  return $wheretxt;
}

function checkNotificationDup($name)
{
  $chkcount = NanoDB::find_many('select * from  ' . $GLOBALS['PREFIX'] . 'event.Notifications where name = ?', null, [$name]);
  return safe_count($chkcount);
}
