<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
set_time_limit(0);
$cronConfig = new CronFunction();
if($cronEnabled = $cronConfig->startPermission('not_A36_daily', 'NOT A36 DAILY') != 'true'){
  echo 'no launch rights';
  exit();
}
$cronConfig->updateMonitoring('NOT A36 DAILY');
$microtimeStart = microtime(true);
NanoDB::query('drop TABLE if exists ' . $GLOBALS['PREFIX'] . 'asset.NotA36Daily_tmp');
NanoDB::query("CREATE TABLE  " . $GLOBALS['PREFIX'] . "asset.NotA36Daily_tmp as  select  distinct
  machineid,
  CONCAT(ad.value ->> '$.installedsoftwarenames', '-' , ad.value ->> '$.version') as adNV
  from " . $GLOBALS['PREFIX'] . "asset.AssetDataDaily as ad WHERE ad.dataid  = 36");
NanoDB::query('CREATE INDEX NotA36Daily_tmp_machineid_IDX USING BTREE ON ' . $GLOBALS['PREFIX'] . 'asset.NotA36Daily_tmp (machineid);');
NanoDB::query('CREATE INDEX NotA36Daily_tmp_adNV_IDX USING BTREE ON ' . $GLOBALS['PREFIX'] . 'asset.NotA36Daily_tmp (adNV (100));');

$microtimeEnd = microtime(true);
$timeWork = $microtimeEnd - $microtimeStart;
$sqlEndTimeStamp = 'update '. $GLOBALS['PREFIX'].'core.CronMonitoring set time_end_cron=?,time_work_cron=?, success=? where cron_name=?';
NanoDB::query($sqlEndTimeStamp, [time(),$timeWork,1,'NOT A36 DAILY']);
?>
