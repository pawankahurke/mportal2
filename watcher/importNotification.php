<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../lib/l-db.php';
require_once '../include/common_functions.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';
include_once '../lib/l-setTimeZone.php';
$pdo = pdo_connect();
$user_name = $_SESSION["user"]["username"];

if ($_FILES['notify_file']['size'] > 0) {
  $file = $_FILES['notify_file']['tmp_name'];
  $insertedRecords = 0;
  $failedRecords = 0;
  $duplicaterecord = 0;
  $key = '';
  $handle = fopen($file, "r");
  $total = count(file($file, FILE_SKIP_EMPTY_LINES)) - 1;
  $olddetails = [];
  $newdetails = [];
  $dupname = [];
  while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {

    if ($data[1] && $data[1] != 'Site' && $data[0] && $data[0] != 'Name') {
      $notifyname = $data[0];

      $group = $data[1];
      $ntype = getFilterType($data[2]);
      $seconds = $data[3];
      $criteria = $data[4];
      $search_txt = $data[5];
      $where_txt = $data[6];
      $show_txt = $data[7];
      $notify = checkNotificationDup($pdo, $notifyname);
      if (safe_count($notify) == 0) {
        $insertedRecords = $insertedRecords + 1;
      } else {
        $duplicaterecord = $duplicaterecord + 1;
        $dupname[] = $notifyname;
        $olddetails[] = array("criteria" => safe_json_decode($notify[0]['criteria']), "searchtxt" => $notify[0]['search_txt']);
        $newdetails[] = array("criteria" => safe_json_decode($criteria), "searchtxt" => $search_txt);
      }
    }
  }

  $dname = '';
  if (safe_count($dupname) > 0) {
    $dname = implode('</br>', $dupname);
  }
  echo json_encode(array("total" => $total, "insert" => $insertedRecords, "failed" => $failedRecords, "duplicate" => $duplicaterecord, "dupname" => $dname, "olddet" => json_encode($olddetails), "newdet" => json_encode($newdetails)));
}


function getFilterType($item)
{
  $typearray = 0;
  if ($item == 'Availability') {
    $typearray = 1;
  }
  if ($item == 'Security') {
    $typearray = 2;
  }
  if ($item == 'Resource') {
    $typearray = 3;
  }
  if ($item == 'Maintenance') {
    $typearray = 4;
  }
  if ($item == 'Events of Interest') {
    $typearray = 5;
  }
  return $typearray;
}

function checkNotificationDup($pdo, $name)
{

  $chkSql = $pdo->prepare('select * from  ' . $GLOBALS['PREFIX'] . 'event.Notifications where name = ?');
  $chkSql->execute([$name]);
  $chkcount = $chkSql->fetchAll(PDO::FETCH_ASSOC);

  return $chkcount;
}
