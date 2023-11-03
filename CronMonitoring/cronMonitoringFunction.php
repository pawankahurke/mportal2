<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

if (url::postToText('function') === 'getCronMonitoring') {
  getCronMonitoring();
}

function getCronMonitoring()
{
  $cronMonitoringInDB = NanoDB::find_many('select * from ' . $GLOBALS['PREFIX'] . 'core.CronMonitoring');
  $resultHtml = '';


  $configFileDecode = "{}";
  if (file_exists('/cron-config/cron-onprem.json')) {
    $configFileDecode = file_get_contents('/cron-config/cron-onprem.json');
  } else {
    $configFileEncode = file_get_contents('/cron-config/cron.json');
    $configFileDecode = htmlspecialchars_decode($configFileEncode);
  }
  $config = json_decode($configFileDecode);

  foreach ($cronMonitoringInDB as $item) {
    $varPeriodicity = $item['cron_name_alias'] . '_periodicity';
    $varTimeExecution = $item['cron_name_alias'] . '_time_execution';
    $resultHtml = $resultHtml . '
        <tr>
            <td>' . $item['cron_name'] . '</td>
            <td>' . date('m-d-Y H:i', $item['last_launch']) . '</td>
            <td>' . $config->$varPeriodicity . '</td>
            <td>' . $config->$varTimeExecution . '</td>
            <td>' . $item['count_launches'] . '</td>
        </tr>
       ';
  }
  echo $resultHtml;
}
