<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
set_time_limit(0);
$cronConfig = new CronFunction();
if($cronEnabled = $cronConfig->startPermission('asset_data_history_tmp', 'ASSET DATA HISTORY TMP') != 'true'){
  echo 'no launch rights';
  exit();
}

$cronConfig->updateMonitoring('ASSET DATA HISTORY TMP');
$microtimeStart = microtime(true);


$dataIds = [10, 16];
foreach ($dataIds as $key => $ACubeIndex) {
//  if (RedisLink::tryToSetLock("AssetDataHistoryTmp_A$ACubeIndex",   3600 * 1)) {
  $sql = "insert into " . $GLOBALS['PREFIX'] . "asset.AssetDataHistoryTmp_A$ACubeIndex
    select
        UNIX_TIMESTAMP(LEAST(GREATEST (COALESCE((select from_unixtime(min(unixDate), '%Y-%m-%d') + interval 1 day from " . $GLOBALS['PREFIX'] . "asset.AssetDataHistoryTmp_A$ACubeIndex ),0 ),COALESCE((
            SELECT
               from_unixtime(MIN(clatest), '%Y-%m-%d')
            FROM
              " . $GLOBALS['PREFIX'] . "asset.AssetData
          ),0)), NOW() - interval 1 day)) as unixDate,
        m.machineid as mId,
        m.cust as msite,
        m.host as mhost,
         null as maxSlatest
          from  " . $GLOBALS['PREFIX'] . "asset.Machine m
          ON DUPLICATE KEY UPDATE mId=mId;";
  NanoDB::query($sql);
//  }

  $AssetDataHistoryTmp_Axx_limit = 1000;
  if (getenv('AssetDataHistoryTmp_Axx_limit')) {
    $AssetDataHistoryTmp_Axx_limit = (int)getenv('AssetDataHistoryTmp_Axx_limit');
  }
  for ($i = 0; $i < 10; $i++) {
    NanoDB::query("update " . $GLOBALS['PREFIX'] . "asset.AssetDataHistoryTmp_A$ACubeIndex set maxSlatest = (
    select
      COALESCE(max(slatest), 0)
    from
      " . $GLOBALS['PREFIX'] . "asset.AssetData as adt
    where
      unixDate > adt.slatest
          and mId = adt.machineid
          and adt.dataid = $ACubeIndex
  )
  where
    maxSlatest is null
    limit $AssetDataHistoryTmp_Axx_limit");
  }
}
$microtimeEnd = microtime(true);
$timeWork = $microtimeEnd - $microtimeStart;
$sqlEndTimeStamp = 'update '. $GLOBALS['PREFIX'].'core.CronMonitoring set time_end_cron=?,time_work_cron=?, success=? where cron_name=?';
NanoDB::query($sqlEndTimeStamp, [time(),$timeWork,1,'ASSET DATA HISTORY TMP']);
?>
