<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

nhRole::dieIfNotSuperAdminRole();

include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';

$action = url::postToText('action');


$pdo = pdo_connect();
if ($action  == 'Upload') {

  if ($_FILES['csvfile']['size'] > 0) {
    $file = $_FILES['csvfile']['tmp_name'];
    $insertedRecords = 0;
    $failedRecords = 0;
    $duplicaterecords = 0;
    $total = count(file($file, FILE_SKIP_EMPTY_LINES)) - 1;
    $handle = fopen($file, "r");
    $key = '';
    while (($data = fgetcsv($handle)) !== FALSE) {
      if ($data[0] != 'dartNo') {

        $dartConfig = $data[2];
        $search_txt = $data[3];
        $where_txt = $data[4];

        $chkSel = $pdo->query("select * from  " . $GLOBALS['PREFIX'] . "event.dart_config where dartNo = '" . (int)$data[0] . "' and dartConfig = '" . $dartConfig . "'");
        $chkSel->execute();
        $chkres = $chkSel->fetchAll();

        if (safe_count($chkres) == 0) {
          $chcksql = $pdo->prepare(
            "insert into " . $GLOBALS['PREFIX'] . "event.dart_config(dartNo,dartName,dartConfig,dartConfigsearch_txt,dartConfigwhere_txt,dartConfigshow_txt,created)
                   values(?,?,?,?,?,?,?)
                   ON DUPLICATE KEY UPDATE
                   dartNo = VALUES(dartNo)");
          $chcksql->execute([$data[0], $data[1], $dartConfig, $search_txt, $where_txt, $search_txt, time()]);
          $insertedRecords = $insertedRecords + 1;
        } else {
          $duplicaterecords = $duplicaterecords + 1;
        }
      }
    }
    fclose($handle);
    echo "<b data-qa='Import-successfully-done' >Import successfully done inserted   - " . $insertedRecords . " duplicate records -  " . $duplicaterecords . "</b>";
  }
} else if ($action == 'Delete') {


  $dartSql = $pdo->prepare("truncate table  " . $GLOBALS['PREFIX'] . "event.dart_config;");
  $dartSql->execute();

  echo "Deleted successfully";
}
