<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once '../lib/l-dbConnect.php';

function getCRMdetails($pdo)
{
    try {
        $crmsql = $pdo->prepare('select * from  ' . $GLOBALS['PREFIX'] . 'event.crmSnowConfigure where crmType = ? limit 1');
        $crmsql->execute(['SOFTWARE_SE']);
        $crmres = $crmsql->fetch(PDO::FETCH_ASSOC);

        return $crmres;
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        echo "Exception : " . $ex;
    }
}

function getDataNameID($pdo)
{

    $dataNames = ['installedprogramswithversions'];

    $dataname_in = str_repeat('?,', safe_count($dataNames) - 1) . '?';

    $dnameStmt = $pdo->prepare("select dataid, name from " . $GLOBALS['PREFIX'] . "asset.DataName where name IN ($dataname_in)");
    $dnameStmt->execute($dataNames);
    $dnameData = $dnameStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($dnameData as $key => $value) {
        $dataList[$value['name']] = $value['dataid'];
    }

    return $dataList;
}



function parseAssetData($assetData)
{

    $machineData = [];

    foreach ($assetData as $key => $value) {
        $machineData = array_merge($machineData, safe_json_decode($value['value'], TRUE));
    }

    return $machineData;
}



function pushCMDBDataToSNOW($snowSoftwareData, $SNOW_CMDB_API_URL, $SNOW_CMDB_USERNAME, $SNOW_CMDB_PASSWORD)
{


    try {
        $UNAME_PASWD = "$SNOW_CMDB_USERNAME:$SNOW_CMDB_PASSWORD";
        if (!empty($snowSoftwareData)) {
            $headers = array();
            $headers[] = "Content-Type: application/json";
            $headers[] = "Accept: application/json";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_URL, $SNOW_CMDB_API_URL);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($snowSoftwareData));
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

function initiateSoftwareUpdateProcess($pdo, $swid, $sitename, $machine)
{
    $pushStatusCode = 0;
    $crmData = getCRMdetails($pdo);

    $SNOW_CMDB_API_URL = $crmData['crmUrl'];
    $SNOW_CMDB_USERNAME = $crmData['crmUsername'];
    $SNOW_CMDB_PASSWORD = $crmData['crmPassword'];

    $dataIds = getDataNameID($pdo);


    $machStmt = $pdo->prepare('select machineid, host, searliest, slatest, clatest, cearliest from '
        . $GLOBALS['PREFIX'] . 'asset.Machine where cust = ? and host = ? limit 1');
    $machStmt->execute([$sitename, $machine]);
    $machData = $machStmt->fetch(PDO::FETCH_ASSOC);


    if ($machData) {
        $machineid = $machData['machineid'];
        $clatest = $machData['clatest'];
        $cearliest = $machData['cearliest'];
        $lastAssetSync = ($machData['slatest']) ? $machData['slatest'] : $machData['searliest'];

        $dataid_in = str_repeat('?,', safe_count($dataIds) - 1) . '?';

        $assetStmt = $pdo->prepare("select id, dataid, value from " . $GLOBALS['PREFIX'] . "asset.AssetData "
            . "where machineid = ? and dataid IN ($dataid_in) and (clatest >= ? or clatest >= ?) order by id desc");
        $params = array_merge([$machineid], array_values($dataIds), [$clatest, $cearliest]);
        $assetStmt->execute($params);
        $assetData = $assetStmt->fetchAll(PDO::FETCH_ASSOC);

        $machStmt = $pdo->prepare("select value from " . $GLOBALS['PREFIX'] . "asset.AssetData where machineid = ? "
            . "and dataid IN (select dataid from " . $GLOBALS['PREFIX'] . "asset.DataName where name IN (?,?))");
        $machStmt->execute([$machineid, 'general', 'identification']);
        $machData = $machStmt->fetchAll(PDO::FETCH_ASSOC);
        $machineDetails = parseAssetData($machData);

        if ($machineDetails) {
            $softwareData['u_site_name'] = $sitename;
            $softwareData['u_serial_no'] = $machineDetails['systemserialnumber'];
            $softwareData['u_host_name'] = $machineDetails['host'];
            $softwareData['u_last_asset_info_synced_time'] = $lastAssetSync;

            if ($assetData) {
                foreach ($assetData as $key => $value) {

                    $instSoftdata = safe_json_decode($value['value'], TRUE);

                    $softwareData['softwares'][$key]['u_name'] = isset($instSoftdata['installedsoftwarenames']) ? $instSoftdata['installedsoftwarenames'] : 'NA';
                    $softwareData['softwares'][$key]['u_version'] = isset($instSoftdata['version']) ? $instSoftdata['version'] : 'NA';
                    $softwareData['softwares'][$key]['u_installed_date'] = isset($instSoftdata['installationdate']) ? strtotime($instSoftdata['installationdate']) : 'NA';
                }
            }


            if ($assetData) {
                //Update the status before getting the response code
                $updtStmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "asset.cmdbSoftwareData set status = ?, requestdata = ?, "
                    . "responsecode = ?, servertime = ? where site = ? and host = ? and sid = ?");
                $updtStmt->execute([2, "Waiting for response", 202, time(), $sitename, $machine, $swid]);
                echo '<br/><br/>Machine# ' . $machine . ' ==== Status Code : 202 Created';

                $pushStatusCode = pushCMDBDataToSNOW($softwareData, $SNOW_CMDB_API_URL, $SNOW_CMDB_USERNAME, $SNOW_CMDB_PASSWORD);
            }
            if ($pushStatusCode == 201 || $pushStatusCode == 200) {
                $updtStmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "asset.cmdbSoftwareData set status = ?, requestdata = ?, "
                    . "responsecode = ?, servertime = ? where site = ? and host = ? and sid = ?");
                $updtStmt->execute([1, json_encode($softwareData), $pushStatusCode, time(), $sitename, $machine, $swid]);

                echo '<br/><br/>Machine# ' . $machine . ' ==== Status Code : ' . $pushStatusCode . ' Created';
            } else {
                $updtStmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "asset.cmdbSoftwareData set status = ?, "
                    . "responsecode = ?, servertime = ? where site = ? and host = ? and sid = ?");
                $updtStmt->execute([6, 404, time(), $sitename, $machine, $swid]);
                echo '<br/><br/>Machine# ' . $machine . ' ==== Status Code' . $pushStatusCode;
            }
        } else {
            $updtStmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "asset.cmdbSoftwareData set status = ?, "
                . "responsecode = ?, servertime = ? where site = ? and host = ? and sid = ?");
            $updtStmt->execute([6, 404, time(), $sitename, $machine, $swid]);

            echo "Asset Data not found for Machine# " . $machine . " from Site# . " . $sitename . "<br/>";
        }
    } else {
        $updtStmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "asset.cmdbSoftwareData set status = ?, "
            . "responsecode = ?, servertime = ? where site = ? and host = ? and sid = ?");
        $updtStmt->execute([6, 404, time(), $sitename, $machine, $swid]);
    }
}



$pdo = pdo_connect();
echo '<pre>';

$stmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "asset.cmdbSoftwareData where status = ? limit 4");
$stmt->execute([0]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($data as $key => $value) {
    $swid = $value['sid'];
    $sitename = $value['site'];
    $machine = $value['host'];


    initiateSoftwareUpdateProcess($pdo, $swid, $sitename, $machine);
}
