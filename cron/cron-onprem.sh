#!/bin/bash

mkdir -p /cron-config
cp /home/nanoheal/cron-onprem.json /cron-config/cron-onprem.json

export IN_CRON=true

printf "HELLO FROM CRON-onprem.SH\n"

printf "=============\n/Dashboard/cron/c-SQLDaily.php\n=============\n"
curl --silent "http://localhost:85/Dashboard/cron/c-SQLDaily.php" 2>&1

printf "=============\n/Dashboard/cron/LatestCombinedAsset.php\n=============\n"
curl --silent "http://localhost:85/Dashboard/cron/LatestCombinedAsset.php" 2>&1

printf "=============\n/Dashboard/cron/AssetDataHistoryTmp.php\n=============\n"
curl --silent "http://localhost:85/Dashboard/cron/AssetDataHistoryTmp.php" 2>&1

printf "=============\n/Dashboard/cron/cron_notifications_ttl.php\n=============\n"
curl --silent "http://localhost:85/Dashboard/cron/cron_notifications_ttl.php" 2>&1

printf "=============\n/Dashboard/cron/cron_NotA36Daily_tmp.php\n=============\n"
curl --silent "http://localhost:85/Dashboard/cron/cron_NotA36Daily_tmp.php" 2>&1

printf "=============\n/Dashboard/src/MetaBaseAgregation/MetaBaseAgregation.php\n=============\n"
curl --silent "http://localhost:85/Dashboard/cron/MetaBaseAgregation.php" 2>&1

# curl -v "http://localhost:85/Dashboard/cron/c-adhocqueries.php" 2>&1
# curl -v "http://localhost:85/Dashboard/cron/c-cmdbHardwarePush.php" 2>&1
# curl -v "http://localhost:85/Dashboard/cron/c-cmdbSoftwarePush.php" 2>&1

printf "=============\n/Dashboard/cron/c-crmincident.php\n=============\n"
curl --silent "http://localhost:85/Dashboard/cron/c-crmincident.php" 2>&1

# curl -v "http://localhost:85/Dashboard/cron/c-insertqueries.php" 2>&1

# curl -v "http://localhost:85/Dashboard/cron/c-itsmccRetry.php" 2>&1

printf "=============\n/Dashboard/Provision/cron/c-purge.php\n=============\n"
curl --silent "http://localhost:85/Dashboard/Provision/cron/c-purge.php" 2>&1

printf "=============\n/Dashboard/cron/c-crmincident_closedEvents.php\n=============\n"
curl --silent "http://localhost:85/Dashboard/cron/c-crmincident_closedEvents.php" 2>&1

printf "=============\n/Dashboard/admin/expunge_machines_cron.php\n=============\n"
curl --silent "http://localhost:85/Dashboard/admin/expunge_machines_cron.php" 2>&1
