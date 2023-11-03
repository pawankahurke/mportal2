<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
set_time_limit(0);
$cronConfig = new CronFunction();
if($cronEnabled = $cronConfig->startPermission('notifications_ttl', 'NOTIFICATION TTL') != 'true'){
  echo 'no launch rights';
  exit();
}
$cronConfig->updateMonitoring('NOTIFICATION TTL');
$microtimeStart = microtime(true);

$notifications_ttl = (int)(getenv('notifications_ttl') ?: 30 * 86400);
NanoDB::query('delete  from '. $GLOBALS['PREFIX'] .'event.Console c where  (( servertime + ' . $notifications_ttl . ') - UNIX_TIMESTAMP())/86400 < 0');

$microtimeEnd = microtime(true);
$timeWork = $microtimeEnd - $microtimeStart;
$sqlEndTimeStamp = 'update '. $GLOBALS['PREFIX'].'core.CronMonitoring set time_end_cron=?,time_work_cron=?, success=? where cron_name=?';
NanoDB::query($sqlEndTimeStamp, [time(),$timeWork,1,'NOTIFICATION TTL']);
?>
