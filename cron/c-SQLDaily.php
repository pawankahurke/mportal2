<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
set_time_limit(0);
$cronConfig = new CronFunction();
if ($cronEnabled = $cronConfig->startPermission('sql_daily', 'SQL DAILY') != 'true') {
  echo 'no launch rights';
  exit();
}
$cronConfig->updateMonitoring('SQL DAILY');
$microtimeStart = microtime(true);



//if($_GET['deleteLocks'] === 'true') {
//  RedisLink::deleteLock('dateCronSqlDaily');
//  RedisLink::deleteLock('cron_NotA36Daily_tmp');
//  RedisLink::deleteLock('cron_notifications_ttl');
//  RedisLink::deleteLock('AssetDataHistoryTmp_A10');
//  RedisLink::deleteLock('AssetDataHistoryTmp_A16');
//  RedisLink::deleteLock('LatestCombinedAsset');
//}

/**
 * cron for Daily sql tasks
 */
//if (RedisLink::tryToSetLock("dateCronSqlDaily",   60 * 30)) {
/**
 * cron for re -creation table AssetDataDaily
 */
$now = date("z") / 1;
$now_last = date("z") / 1 - 1;

$res = ['res' => 0];
try {
  $res = NanoDB::find_one("select    UNIX_TIMESTAMP() - COALESCE((max(slatest) + 3600*24), 1)  as res  from " . $GLOBALS['PREFIX'] . "asset.AssetDataDaily add2 ");
} catch (Exception $e) {
  $res = ['res' => 1];
}

if ($res['res'] > 0) {

  $create_query = "CREATE TABLE if not exists  " . $GLOBALS['PREFIX'] . "asset.AssetDataDaily_$now (
    `id` int DEFAULT '0',
    `machineid` int DEFAULT '0',
    `dataid` int DEFAULT '0',
    `value` json DEFAULT NULL,
    `ordinal` int DEFAULT '0',
    `cearliest` int DEFAULT '0',
    `cobserved` int DEFAULT '0',
    `clatest` int DEFAULT '0',
    `searliest` int DEFAULT '0',
    `sobserved` int DEFAULT '0',
    `slatest` int DEFAULT '0',
    `uuid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
    `host` varchar(64) CHARACTER SET latin1 DEFAULT '',
    `site` varchar(50) CHARACTER SET latin1 DEFAULT '',
    UNIQUE KEY `AssetDataDaily_id_IDX` (`id`) USING BTREE,
    KEY `AssetDataDaily_machineid_IDX` (`machineid`) USING BTREE,
    KEY `AssetDataDaily_slatest_IDX` (`slatest`) USING BTREE,
    KEY `AssetDataDaily_dataid_IDX` (`dataid`) USING BTREE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

  NanoDB::query($create_query);

  $res = NanoDB::find_one("select * from ".$GLOBALS['PREFIX']."asset.AssetDataDaily_$now limit 1");
  if (!$res) {
    $create_data = "insert into ".$GLOBALS['PREFIX']."asset.AssetDataDaily_$now select ad.*, m.host, m.cust as site from
  (select  machineid, dataid, max(slatest) as maxTime from ".$GLOBALS['PREFIX']."asset.AssetData ad group by 1,2) as ag
  left join ".$GLOBALS['PREFIX']."asset.AssetData as ad  on ad.machineid = ag.machineid
  left join ".$GLOBALS['PREFIX']."asset.Machine as m on m.machineid = ag.machineid
  where ad.dataid = ag.dataid and   ag.maxTime = ad.slatest ";
    try {
      NanoDB::query($create_data);
    } catch (Exception $e) {
      logs::log("Error", $e);
    }
  }

  try {
    NanoDB::query("drop TABLE if exists ".$GLOBALS['PREFIX']."asset.AssetDataDaily_" . $now_last . ";");
    NanoDB::query("RENAME TABLE ".$GLOBALS['PREFIX']."asset.AssetDataDaily TO ".$GLOBALS['PREFIX']."asset.AssetDataDaily_" . $now_last . ";");
  } catch (Exception $e) {
    logs::log("Error", $e);
  }
  try {
    $rename = "RENAME TABLE ".$GLOBALS['PREFIX']."asset.AssetDataDaily_" . $now . " TO ".$GLOBALS['PREFIX']."asset.AssetDataDaily";
    NanoDB::query($rename);
  } catch (Exception $e) {
    logs::log("Error", $e);
  }

  // perhaps later we can delete it
  $old_tables = NanoDB::find_many("SELECT table_name AS name FROM information_schema.tables WHERE table_schema = '".$GLOBALS['PREFIX']."asset' and table_name like 'AssetDataDaily_%'");
  foreach ($old_tables as $old_table) {
    NanoDB::query("drop TABLE if exists " . $GLOBALS['PREFIX'] . "asset." . $old_table["name"] . ";");
  }
}

$microtimeEnd = microtime(true);
$timeWork = $microtimeEnd - $microtimeStart;
$sqlEndTimeStamp = 'update ' . $GLOBALS['PREFIX'] . 'core.CronMonitoring set time_end_cron=?,time_work_cron=?, success=? where cron_name=?';
NanoDB::query($sqlEndTimeStamp, [time(), $timeWork, 1, 'SQL DAILY']);
//}



//if (RedisLink::tryToSetLock("cron_NotA36Daily_tmp",   3600 * 24)) {
//  /**
//   * Additional table for optimizations
//   * NCP-826 (Create NotA36 v2)
//   */
//  NanoDB::query('drop TABLE if exists " . $GLOBALS['PREFIX'] . "asset.NotA36Daily_tmp');
//  NanoDB::query("CREATE TABLE  " . $GLOBALS['PREFIX'] . "asset.NotA36Daily_tmp as  select  distinct
//  machineid,
//  CONCAT(ad.value ->> '$.installedsoftwarenames', '-' , ad.value ->> '$.version') as adNV
//  from " . $GLOBALS['PREFIX'] . "asset.AssetDataDaily as ad WHERE ad.dataid  = 36");
//  NanoDB::query('CREATE INDEX NotA36Daily_tmp_machineid_IDX USING BTREE ON " . $GLOBALS['PREFIX'] . "asset.NotA36Daily_tmp (machineid);');
//  NanoDB::query('CREATE INDEX NotA36Daily_tmp_adNV_IDX USING BTREE ON " . $GLOBALS['PREFIX'] . "asset.NotA36Daily_tmp (adNV (100));');
//}

//if (RedisLink::tryToSetLock("cron_notifications_ttl",   3600 * 24)) {
//  /**
//   * Data retention for notifications
//   * NCP-795
//   *
//   * Remove old data from event.Console
//   */
//  $notifications_ttl = (int)(getenv('notifications_ttl') ?: 30 * 86400);
//  NanoDB::query('delete  from event.Console c where  (( servertime + ' . $notifications_ttl . ') - UNIX_TIMESTAMP())/86400 < 0');
//}

/**
 * Prebuild tmp table for optimizations in cubeJS schemas
 * NCP-912
 */
//$dataIds = [10, 16];
//foreach ($dataIds as $key => $ACubeIndex) {
//  if (RedisLink::tryToSetLock("AssetDataHistoryTmp_A$ACubeIndex",   3600 * 1)) {
//    $sql = "insert into " . $GLOBALS['PREFIX'] . "asset.AssetDataHistoryTmp_A$ACubeIndex
//    select
//        UNIX_TIMESTAMP(LEAST(GREATEST (COALESCE((select from_unixtime(min(unixDate), '%Y-%m-%d') + interval 1 day from " . $GLOBALS['PREFIX'] . "asset.AssetDataHistoryTmp_A$ACubeIndex ),0 ),COALESCE((
//            SELECT
//               from_unixtime(MIN(clatest), '%Y-%m-%d')
//            FROM
//              " . $GLOBALS['PREFIX'] . "asset.AssetData
//          ),0)), NOW() - interval 1 day)) as unixDate,
//        m.machineid as mId,
//        m.cust as msite,
//        m.host as mhost,
//         null as maxSlatest
//          from  " . $GLOBALS['PREFIX'] . "asset.Machine m
//          ON DUPLICATE KEY UPDATE mId=mId;";
//    NanoDB::query($sql);
//  }
//
//  $AssetDataHistoryTmp_Axx_limit = 1000;
//  if (getenv('AssetDataHistoryTmp_Axx_limit')) {
//    $AssetDataHistoryTmp_Axx_limit = (int)getenv('AssetDataHistoryTmp_Axx_limit');
//  }
//  for ($i = 0; $i < 10; $i++) {
//    NanoDB::query("update " . $GLOBALS['PREFIX'] . "asset.AssetDataHistoryTmp_A$ACubeIndex set maxSlatest = (
//    select
//      COALESCE(max(slatest), 0)
//    from
//      " . $GLOBALS['PREFIX'] . "asset.AssetData as adt
//    where
//      unixDate > adt.slatest
//          and mId = adt.machineid
//          and adt.dataid = $ACubeIndex
//  )
//  where
//    maxSlatest is null
//    limit $AssetDataHistoryTmp_Axx_limit");
//  }
//}


//if (RedisLink::tryToSetLock("LatestCombinedAsset",   3600 * 24)) {
/**
 * Prebuild tmp table LatestCombinedAsset
 * NCP-785
 */
//  NanoDB::query("DROP TABLE IF EXISTS " . $GLOBALS['PREFIX'] . "asset.LatestCombinedAsset;");
//  NanoDB::query("
//    CREATE TABLE IF NOT EXISTS `" . $GLOBALS['PREFIX'] . "asset`.`LatestCombinedAsset` (
//        `id` INTEGER auto_increment,
//        `machineid` varchar(600) DEFAULT NULL,
//        `host` varchar(600) DEFAULT NULL,
//        `site` varchar(600) DEFAULT NULL,
//        `a5manufacturer` varchar(600) DEFAULT NULL,
//        `a5chassistype` varchar(600) DEFAULT NULL,
//        `a20registeredprocessor` varchar(600) DEFAULT NULL,
//        `a20processorfamily` varchar(600) DEFAULT NULL,
//        `a20processormanufacturer` varchar(600) DEFAULT NULL,
//        `a16operatingsystem` varchar(600) DEFAULT NULL,
//        `a39memorysize` varchar(600) DEFAULT NULL,
//        `slatest` varchar(100) DEFAULT NULL,
//        PRIMARY KEY (`id`)
//    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;");
//  NanoDB::query("insert into `" . $GLOBALS['PREFIX'] . "asset`.`LatestCombinedAsset` (
//        machineid,
//        host,
//        site,
//        a5manufacturer,
//        a5chassistype,
//        a20registeredprocessor,
//        a20processorfamily,
//        a20processormanufacturer,
//        a39memorysize,
//        a16operatingsystem,
//        slatest
//    )
//    select
//    `machineid` as `machineid`,
//    `host` as `host`,
//    `site` as `site`,
//    MAX(value->>'$.chassismanufacturer') as `a5manufacturer`,
//    MAX(value->>'$.chassistype') as `a5chassistype`,
//    MAX(value->>'$.registeredprocessor') as `a20registeredprocessor`,
//    MAX(value->>'$.processorfamily') as `a20processorfamily`,
//    MAX(value->>'$.processormanufacturer') as `a20processormanufacturer`,
//    MAX(value->>'$.memorysize') as `a39memorysize`,
//    MAX(value->>'$.operatingsystem') as `a16operatingsystem`,
//    slatest
//    from " . $GLOBALS['PREFIX'] . "asset.AssetDataDaily
//    where dataid = 16 or dataid = 5 or dataid = 20 or dataid = 39
//    GROUP by machineid ;");
//}