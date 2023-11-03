<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once '../lib/l-dbConnect.php';

function getCRMdetails()
{
    return  NanoDB::find_one('select * from  ' . $GLOBALS['PREFIX'] . 'event.crmSnowConfigure where crmType = ? limit 1', null, ['CMDB']);
}

function getDataNameID($pdo)
{

    $dataNames = [
        'chassisinformation', 'processorinformation', 'logicaldiskinformation', 'physicaldiskinformation',
        'dnsservers', 'networkadapters', 'operatingsysteminformation', 'memoryarraymappedaddress',
        'identification', 'general', 'useraccountinformation'
    ];

    $dataname_in = str_repeat('?,', safe_count($dataNames) - 1) . '?';

    $dnameStmt = $pdo->prepare("select dataid, name from " . $GLOBALS['PREFIX'] . "asset.DataName where name IN ($dataname_in)");
    $dnameStmt->execute($dataNames);
    $dnameData = $dnameStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($dnameData as $key => $value) {
        $dataList[$value['name']] = $value['dataid'];
    }

    return $dataList;
}

function parseAssetData($sitename, $assetData, $lastReported, $lastAssetSync, $driveArray)
{
    $driveVal = json_encode($driveArray);
    $cmdbData = [];

    foreach ($assetData as $value) {
        $cmdbData = array_merge($cmdbData, safe_json_decode($value['value'], TRUE));
    }

    $cmdbAssetData = '{"u_site_name" : "' . $sitename . '", "u_chassis_type" : "' . $cmdbData['chassistype'] . '", '
        . '"u_cpu_core_count" : "' . $cmdbData['processorcorecount'] . '", '
        . '"u_cpu_core_thread" : "' . $cmdbData['processorthreadcount'] . '", '
        . '"u_cpu_count" : "' . $cmdbData['processorcoreenabled'] . '", '
        . '"u_cpu_manufacturer" : "' . $cmdbData['processormanufacturer'] . '", '
        . '"u_cpu_speed_mhz" : "' . $cmdbData['processormaxspeedinmhz'] . '", '
        . '"u_cpu_type" : "' . $cmdbData['processortype'] . '", "u_discovery_source" : "ElfTouch", '
        // . '"u_disk_space_gb" : "' . ($cmdbData['logicaldiskkbytestotal'] / 1000000) . '", '
        . '"u_disk_space_gb" : "' . byte_format($cmdbData['physicaldisksizeinbytes']) . '", '
        . '"u_dns_domain" : "' . $cmdbData['domain'] . '", "u_hostname" : "' . $cmdbData['host'] . '", '
        . '"u_ip_address" : "' . $cmdbData['ipaddress'] . '", "u_mac_address" : "' . $cmdbData['macaddress'] . '", '
        . '"u_manufacturer" : "' . $cmdbData['systemmanufacturer'] . '", '
        . '"u_model_number" : "' . $cmdbData['systemproduct'] . '", '
        . '"u_operating_system" : "' . $cmdbData['operatingsystem'] . '", '
        . '"u_os_service_pack" : "' . $cmdbData['ntinstalledservicepack'] . '", '
        . '"u_os_version" : "' . $cmdbData['osversionnumber'] . '", '
        . '"u_ram_mb" : "' . $cmdbData['arrayrangesize'] . '", "u_serial_number" : "' . $cmdbData['systemserialnumber'] . '", '
        . '"u_last_logged_on_domain_user" : "' . $cmdbData['useraccountname'] . '", '
        . '"u_devices_last_reporting_time" : "' . $lastReported . '", '
        . '"u_last_asset_info_synced_time": "' . $lastAssetSync . '",'
        . '"u_disk_info": ' . $driveVal . '
            
        }';

    return $cmdbAssetData;
}



function pushCMDBDataToSNOW($cmdbAssetData, $SNOW_CMDB_API_URL, $SNOW_CMDB_USERNAME, $SNOW_CMDB_PASSWORD)
{

    try {
        $UNAME_PASWD = "$SNOW_CMDB_USERNAME:$SNOW_CMDB_PASSWORD";
        echo $cmdbAssetData . '<br/>';
        if (!empty($cmdbAssetData)) {
            $headers = array();
            $headers[] = "Content-Type: application/json";
            $headers[] = "Accept: application/json";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_URL, $SNOW_CMDB_API_URL);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $cmdbAssetData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $UNAME_PASWD);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            if (curl_error($ch)) {
                echo '<br/><br/>Error : ' . curl_error($ch);
            }

            if ($result) {
                echo '<br/><br/>Result : ' . $result;
            }

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $httpcode;
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        echo "Exception : " . $ex;
    }
}

function byte_format($size)
{
    $bytes = array(' KB');
    foreach ($bytes as $val) {
        if (1024 <= $size) {
            $size = $size / 1024;
            continue;
        }
        break;
    }
    return round($size, 1) . $val;
}

function initiateCMDBProcess($pdo, $hwid, $sitename, $machine)
{
    $crmData = getCRMdetails($pdo);

    $SNOW_CMDB_API_URL = $crmData['crmUrl'];
    $SNOW_CMDB_USERNAME = $crmData['crmUsername'];
    $SNOW_CMDB_PASSWORD = $crmData['crmPassword'];

    $dataIds = getDataNameID($pdo);


    $siteStmt = $pdo->prepare('select id, site, host, born, last from ' . $GLOBALS['PREFIX'] . 'core.Census where site = ? and host = ? limit 1');
    $siteStmt->execute([$sitename, $machine]);
    $siteData = $siteStmt->fetch(PDO::FETCH_ASSOC);

    if ($siteData) {

        $machine = $siteData['host'];
        $lastReported = ($siteData['last'] != '') ? $siteData['last'] : $siteData['born'];

        $machStmt = $pdo->prepare('select machineid, host, searliest, slatest '
            . 'from ' . $GLOBALS['PREFIX'] . 'asset.Machine where cust = ? and host = ? limit 1');
        $machStmt->execute([$sitename, $machine]);
        $machData = $machStmt->fetchAll(PDO::FETCH_ASSOC);


        if ($machData) {
            foreach ($machData as $key => $value) {
                $driveArray = array();
                $machineid = $value['machineid'];
                $lastAssetSync = ($value['slatest']) ? $value['slatest'] : $value['searliest'];

                $dataid_in = str_repeat('?,', safe_count($dataIds) - 1) . '?';
                $sql = $pdo->prepare("select max(slatest) as 'maxlatest' from " . $GLOBALS['PREFIX'] . "asset.AssetData where machineid = ? and dataid = 37");
                $sql->execute([$machineid]);
                $maxslatest = $sql->fetch(PDO::FETCH_ASSOC);
                $slatest = $maxslatest['maxlatest'];

                $assetStmt1 = $pdo->prepare("select distinct(value->>'$.logicaldiskname') as 'u_drive_letter',
                value->>'$.partitioninfoname' as 'u_disk_name',
                value->>'$.logicaldiskkbytestotal' as 'u_disk_size',
                value->>'$.logicaldiskkbytesfree' as 'u_free_space',
                value->>'$.logicaldiskkbytesused' as 'u_used_space',
                id, 
                dataid, value from " . $GLOBALS['PREFIX'] . "asset.AssetData where machineid = ? and dataid = 37 
                and slatest = ? order by id desc");
                $assetStmt1->execute([$machineid, $slatest]);
                $assetData1 = $assetStmt1->fetchAll(PDO::FETCH_ASSOC);

                foreach ($assetData1 as $k => $value) {
                    $newArr = array();
                    $newArr['u_drive_letter'] = str_replace(':', '', $value['u_drive_letter']);
                    $newArr['u_disk_name'] = $value['u_disk_name'] ? $value['u_disk_name'] : '';
                    $newArr['u_disk_size']  = $value['u_disk_size'] ? $value['u_disk_size'] . " KB" : '';
                    $newArr['u_free_space']  = $value['u_free_space'] ? $value['u_free_space'] . " KB" : '';
                    $newArr['u_used_space']  = $value['u_used_space'] ? $value['u_used_space'] . " KB" : '';
                    array_push($driveArray, $newArr);
                }

                $assetStmt = $pdo->prepare("select id, dataid, value from " . $GLOBALS['PREFIX'] . "asset.AssetData "
                    . "where machineid = ? and dataid IN ($dataid_in) order by id desc");
                $params = array_merge([$machineid], array_values($dataIds));
                $assetStmt->execute($params);
                $assetData = $assetStmt->fetchAll(PDO::FETCH_ASSOC);
                if ($assetData) {
                    //Update the status before getting the response code
                    $updtStmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "asset.cmdbSoftwareData set status = ?, requestdata = ?, "
                        . "responsecode = ?, servertime = ? where site = ? and host = ? and sid = ?");
                    $updtStmt->execute([2, "Waiting for response", 202, time(), $sitename, $machine, $swid]);
                    echo '<br/><br/>Machine# ' . $machine . ' ==== Status Code : 202 Created';

                    $cmdbAssetData = parseAssetData($sitename, $assetData, $lastReported, $lastAssetSync, $driveArray);
                    echo 'Hardware Data for Machine# ' . $machine . ' -> ' . $cmdbAssetData . '<br/><br/>';

                    $pushStatusCode = pushCMDBDataToSNOW($cmdbAssetData, $SNOW_CMDB_API_URL, $SNOW_CMDB_USERNAME, $SNOW_CMDB_PASSWORD);
                    if ($pushStatusCode == 201 || $pushStatusCode == '201' || $pushStatusCode == '200' || $pushStatusCode == 200) {
                        $updtStmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "asset.cmdbHardwareData set status = ?, requestdata = ?,"
                            . "responsecode = ?, servertime = ? where site = ? and host = ? and hid = ?");
                        $updtStmt->execute([1, $cmdbAssetData, $pushStatusCode, time(), $sitename, $machine, $hwid]);

                        echo '<br/><br/>Status Code : ' . $pushStatusCode . '<br/><br/>Status Message : Created';
                    } else {
                        echo '<br/><br/>Status Code' . $pushStatusCode;
                    }
                } else {
                    $updtStmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "asset.cmdbHardwareData set status = ?, "
                        . "responsecode = ?, servertime = ? where site = ? and host = ? and hid = ?");
                    $updtStmt->execute([6, 404, time(), $sitename, $machine, $hwid]);
                    echo "Asset Data not found for Machine# " . $machine . " from Site# . " . $sitename . "<br/>";
                }
            }
        } else {
        }
    } else {
        // die('No Sites Found');
        echo "No Sites Found";
    }
}

$pdo = pdo_connect();

// $stmt = $pdo->prepare("select * from ".$GLOBALS['PREFIX']."asset.cmdbHardwareData where status = ? and site = 'elftouch_SCCC_VNM' 
// and host in ('VNM-CATL-L0105','VNM-CATL-L0107','VNM-CATL-L0108','VNM-CATL-L0602','VNM-CATL-L1202')");
$stmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "asset.cmdbHardwareData where status = ? limit 4");
$stmt->execute([0]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($data as $key => $value) {
    $hwid = $value['hid'];
    $sitename = $value['site'];
    $machine = $value['host'];

    initiateCMDBProcess($pdo, $hwid, $sitename, $machine);
}
