<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
set_time_limit(0);
$cronConfig = new CronFunction();
if($cronEnabled = $cronConfig->startPermission('latest_combined_asset', 'LATEST COMBINED ASSET') != 'true'){
  echo 'no launch rights';
  exit();
}
$cronConfig->updateMonitoring('LATEST COMBINED ASSET');
$microtimeStart = microtime(true);


NanoDB::query("DROP TABLE IF EXISTS " . $GLOBALS['PREFIX'] . "asset.LatestCombinedAsset;");
NanoDB::query("
    CREATE TABLE IF NOT EXISTS `" . $GLOBALS['PREFIX'] . "asset`.`LatestCombinedAsset` (
        `id` INTEGER auto_increment,
        `machineid` varchar(600) DEFAULT NULL,
        `host` varchar(600) DEFAULT NULL,
        `site` varchar(600) DEFAULT NULL,
        `a5manufacturer` varchar(600) DEFAULT NULL,
        `a5chassistype` varchar(600) DEFAULT NULL,
        `a20registeredprocessor` varchar(600) DEFAULT NULL,
        `a20processorfamily` varchar(600) DEFAULT NULL,
        `a20processormanufacturer` varchar(600) DEFAULT NULL,
        `a16operatingsystem` varchar(600) DEFAULT NULL,
        `a39memorysize` varchar(600) DEFAULT NULL,
        `slatest` varchar(100) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;");
NanoDB::query("insert into `" . $GLOBALS['PREFIX'] . "asset`.`LatestCombinedAsset` (
        machineid,
        host,
        site,
        a5manufacturer,
        a5chassistype,
        a20registeredprocessor,
        a20processorfamily,
        a20processormanufacturer,
        a39memorysize,
        a16operatingsystem,
        slatest
    )
    select
    `machineid` as `machineid`,
    `host` as `host`,
    `site` as `site`,
    MAX(value->>'$.chassismanufacturer') as `a5manufacturer`,
    MAX(value->>'$.chassistype') as `a5chassistype`,
    MAX(value->>'$.registeredprocessor') as `a20registeredprocessor`,
    MAX(value->>'$.processorfamily') as `a20processorfamily`,
    MAX(value->>'$.processormanufacturer') as `a20processormanufacturer`,
    MAX(value->>'$.memorysize') as `a39memorysize`,
    MAX(value->>'$.operatingsystem') as `a16operatingsystem`,
    slatest
    from " . $GLOBALS['PREFIX'] . "asset.AssetDataDaily
    where dataid = 16 or dataid = 5 or dataid = 20 or dataid = 39
    GROUP by machineid ;");

$microtimeEnd = microtime(true);
$timeWork = $microtimeEnd - $microtimeStart;
$sqlEndTimeStamp = 'update '. $GLOBALS['PREFIX'].'core.CronMonitoring set time_end_cron=?,time_work_cron=?, success=? where cron_name=?';
NanoDB::query($sqlEndTimeStamp, [time(),$timeWork,1,'LATEST COMBINED ASSET']);
?>
