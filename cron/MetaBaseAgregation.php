<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
set_time_limit(0);
/**
 * Status page https://mbag-emea.nanoheal.app/Dashboard/cron/MetaBaseAgregation.php?status=1
 * Update https://mbag-emea.nanoheal.app/Dashboard/cron/MetaBaseAgregation.php?now=1
 * Force Update https://mbag-emea.nanoheal.app/Dashboard/cron/MetaBaseAgregation.php?now=force
 *
 */
?>
<style>
    td {
        border: 1px solid #000;
    }
</style>
<?php

echo "<br>\nstart MetaBaseAgregation<br>\n";

if (!getenv('CHARTS_ID') || !getenv('METABASE_SITE_URL') || !getenv('METABASE_SECRET_KEY') || !getenv('METABASE_SITE_URL')) {
    logs::log('metabase data could not be read');
    return;
}

$metaBase = new MetaBaseAgregation();

$testConnect = $metaBase->getToken();
if ($testConnect === 'null') {
  logs::log('MetaBaseAgregation getToken. not auth');
  Logs::sendNotification("deployment ".getenv('DEPLOYMENT_ID').".Error auth to metabase");
  exit();
}
$metaBase->getChartsStatus();

if (isset($_GET['now']) && $_GET['now'] === "force") {
    $metaBase->getChartsInfo();
}

$cronConfig = new CronFunction();
if ($cronConfig->startPermission('meta_base_agregation', 'METABASE AGGREGATION') != 'true') {
    echo 'no launch rights';
    exit();
}

/**
 * cron for Daily sql tasks
 */
$metaBase->getChartsInfo();
echo "<br>\nend MetaBaseAgregation<br>\n";

$cronConfig->updateMonitoring('METABASE AGGREGATION');
