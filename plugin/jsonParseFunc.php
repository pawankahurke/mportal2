<?php




include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-dbConnect.php';

$pdo = pdo_connect();

$jsontype = url::requestToAny('jsontype');
foreach ($_FILES['eventjsondata']['name'] as $key => $value) {
    $filename = $_FILES['eventjsondata']['name'][$key];
    $filetype = $_FILES['eventjsondata']['type'][$key];
    $filetemp = $_FILES['eventjsondata']['tmp_name'][$key];

    $typecheck = explode('.', $filename)[1];
    if ($typecheck != 'json') {
        return 'error';
    } else {
        $batchsql = '';
        $myfile = fopen($filetemp, "r") or die("Unable to open file!");
        $jsondata = fread($myfile, filesize($filetemp));

        if ($jsontype == 'event') {
            $eventdata = safe_json_decode($jsondata, true);
            $sql = "INSERT IGNORE INTO  ".$GLOBALS['PREFIX']."event.Events (scrip, entered, customer, machine, username, "
                    . "clientversion, clientsize, priority, description, type, path, executable, "
                    . "version, size, id, windowtitle, string1, string2, text1, text2, text3, text4, "
                    . "ctime, servertime, uuid) VALUES ";

            foreach ($eventdata as $key => $value) {

                $scrip = $value['scrip'];
                $entered = time();
                $customer = $value['site'];
                $machine = $value['machine'];
                $username = $value['uname'];
                $clientversion = $value['cver'];
                $clientsize = $value['csize'];
                $priority = 'NULL';
                $description = $value['desc'];
                $type = $value['logtype'];
                $path = 'NULL';
                $executable = 'NULL';
                $version = 'NULL';
                $size = $value['size'];
                $id = $value['id'];
                $windowtitle = '';
                $string1 = isset($value['string1']) ? json_encode($value['string1']) : '{}';
                $string2 = isset($value['string2']) ? json_encode($value['string2']) : '{}';
                $text1 = isset($value['text1']) ? json_encode($value['text1']) : '{}';
                $text2 = isset($value['text2']) ? json_encode($value['text2']) : '{}';
                $text3 = isset($value['text3']) ? json_encode($value['text3']) : '{}';
                $text4 = isset($value['text4']) ? json_encode($value['text4']) : '{}';
                $ctime = floor($value['ctime'] / 1000);
                $servertime = floor($value['logtimeh'] / 1000);
                $uuid = '';

                $batchsql .= "($scrip, $entered, '$customer', '$machine', '$username', '$clientversion', $clientsize, "
                        . "$priority, '$description', '$type', '$path', '$executable', '$version', $size, $id, "
                        . "'$windowtitle', '$string1', '$string2', '$text1', '$text2', '$text3', '$text4', $ctime, "
                        . "$servertime, '$uuid'),";
            }
            $batchsql = rtrim($batchsql, ',') . ';';

            $runsql = $sql . '' . $batchsql;

            $stmt = $pdo->prepare($runsql);
            $data = $stmt->execute();
        } else if ($jsontype == 'asset') {
            $assetdata = safe_json_decode($jsondata, true);
            $sql = "INSERT IGNORE INTO ".$GLOBALS['PREFIX']."asset.AssetData (machineid, dataid, value, ordinal, cearliest, "
                    . "cobserved, clatest, searliest, sobserved, slatest, uuid) VALUES ";
            foreach ($assetdata as $key => $value) {
                $machineid = $value['machineid'];
                $dataid = $value['dataid'];
                $assetvalue = isset($value['value']) ? json_encode($value['value']) : '{}';
                $ordinal = $value['ordinal'];
                $cearliest = $value['cearliest'];
                $cobserved = $value['cobserved'];
                $clatest = $value['clatest'];
                $searliest = $value['searliest'];
                $sobserved = $value['sobserved'];
                $slatest = $value['slatest'];
                $uuid = 'NULL';

                $batchsql .= "($machineid, $dataid, '$assetvalue', $ordinal, $cearliest, $cobserved, "
                        . "$clatest, $searliest, $sobserved, $slatest, '$uuid'),";
            }
            $batchsql = rtrim($batchsql, ',');

            $runsql = $sql . '' . $batchsql;

            $stmt = $pdo->prepare($runsql);
            $data = $stmt->execute();
        }
    }
}

if ($data) {
    header('Location: index.php?st=success');
} else {
    header('Location: index.php?st=failed');
}


