<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

class CronFunction
{
  /**
   * update monitoring table. Make mark about succsessful run
   */
  public function updateMonitoring($cronName)
  {
    $sqlUpdate = 'update ' . $GLOBALS['PREFIX'] . 'core.CronMonitoring set last_launch=?, count_launches=count_launches+1, success=? where cron_name=?';
    NanoDB::query($sqlUpdate, [time(),  0, $cronName]);
  }

  public function startPermission($cronENV, $cronName)
  {
    //Checking for enableb
    $enable = $cronENV . '_enable';
    $periodicity = $cronENV . '_periodicity';

    $configFileDecode = "{}";
    if (file_exists('/cron-config/cron-onprem.json')) {
      $configFileDecode = file_get_contents('/cron-config/cron-onprem.json');
    } else {
      $configFileEncode = file_get_contents('/cron-config/cron.json');
      $configFileDecode = htmlspecialchars_decode($configFileEncode);
    }
    $config = json_decode($configFileDecode);
    $enable = $config->$enable;


    if ($enable != true) {
      if ($config->$periodicity != '') {
        echo "[$cronName] not enable for $cronName\n";
        return 'false';
      }
    }

    //Checking for permissionLaunch
    $cronStartForLast = $cronENV . '_periodicity';
    $permissionLastLaunch = $config->$cronStartForLast;

    $sqlCron = 'select * from ' . $GLOBALS['PREFIX'] . 'core.CronMonitoring where cron_name=?';
    $cronDB = NanoDB::find_one($sqlCron, null, [$cronName]);
    if (!$cronDB['cron_name']) {
      $sqlInsert = 'insert into ' . $GLOBALS['PREFIX'] . 'core.CronMonitoring (cron_name,cron_name_alias,last_launch,count_launches,time_end_cron,time_work_cron,success) values ("' . $cronName . '","' . $cronENV . '",0,0,0,0,0)';
      NanoDB::query($sqlInsert);
      $sqlCron = 'select * from ' . $GLOBALS['PREFIX'] . 'core.CronMonitoring where cron_name=?';
      $cronDB = NanoDB::find_one($sqlCron, null, [$cronName]);
    }

    $timeNow = time();

    $permissionLaunchPeriodicity = $cronDB['last_launch'] + $permissionLastLaunch * 60;

    if ($permissionLaunchPeriodicity >= $timeNow) {
      echo "[$cronName] Next run after " . $permissionLaunchPeriodicity - $timeNow . " seconds\n";
      return 'false';
    }

    //Checking for allowed launch time

    $execution = $cronENV . '_time_execution';
    $timeExecution = $config->$execution;

    $period = explode('-', $timeExecution);
    $timeStart = (int)$period[0];
    $timeEnd = (int)$period[1];

    $timeNow = new DateTime("now", new DateTimeZone('America/New_York'));
    $timeNow = $timeNow->format('H');
    $timeNow = (int)$timeNow;

    if ($timeStart < $timeEnd) {
      if ($timeNow < $timeStart || $timeNow > $timeEnd) {
        echo "[$cronName] not good time for run (1)\n";
        return 'false';
      }
    }

    if (($timeStart == $timeEnd) && $timeNow <= $timeStart) {
      echo "[$cronName] not good time for run (2)\n";
      return 'false';
    }

    if ($timeStart > $timeEnd) {
      if ($timeNow < $timeStart && $timeNow > $timeEnd) {
        echo "[$cronName] not good time for run (3)\n";
        return  'false';
      }
    }

    return 'true';
  }

  public function getConfigJson($name)
  {
    $configFileDecode = "{}";
    if (file_exists('/cron-config/cron-onprem.json')) {
      $configFileDecode = file_get_contents('/cron-config/cron-onprem.json');
    } else {
      $configFileEncode = file_get_contents('/cron-config/cron.json');
      $configFileDecode = htmlspecialchars_decode($configFileEncode);
    }
    $config = json_decode($configFileDecode);
    return $config->$name;
  }
}
