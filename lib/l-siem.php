<?php



include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-util.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';

if (url::issetInRequest('function')) { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    $function = url::requestToAny('function'); // roles: site
    $function();
}

function SIEM_getSiteList()
{

    $pdo = pdo_connect();
    $userName = $_SESSION['user']['username'];

    $options = '';
    $sites = $_SESSION['user']['site_list'];

    if (safe_count($sites) > 0) {
        $options = "<option value='All'>All</option>";
        foreach ($sites as $key => $val) {
            $site = UTIL_GetTrimCompanyName(trim($val));
            if (!empty($site)) {
                $options .= "<option value='$val'>$site</option>";
            }
        }
    } else {
        $options = "<option value='All'>All</option>";
    }
    echo $options;
}

function getPostValues()
{
    $data = safe_json_decode(file_get_contents('php://input'), true);
    return $data;
}

function sendDataToNode($data)
{

    global $nodeSetUrl;
    $url = $nodeSetUrl;

    $json_array = json_encode($data);
    $data_string = '{"jsondata":' . $json_array . '}';
    $header = array(
        'Content-Type: application/json',
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        'Content-Length: ' . strlen($data_string)
    );
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        return $result;
        curl_close($ch);
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        return "Exception : " . $ex;
    }
}

function SIEM_addConfig()
{

    $pdo = pdo_connect();
    $data = getPostValues();

    if (safe_sizeof($data) === 0) {
        $data = $_REQUEST;
    }
    $time = time();
    $username = $_SESSION['user']['username'];
    $chid = $_SESSION['user']['cId'];
    $name = $data['name'];
    $global = $data['global'];
    $sitename = $data['sitename'];
    $logurl = $data['logurl'];
    $darturl = $data['darturl'];
    $eventurl = $data['eventurl'];
    $asseturl = $data['asseturl'];
    $proactiveurl = $data['proactiveurl'];
    $patchurl = $data['patchurl'];
    $complianceurl = $data['complianceurl'];
    $notificationurl = $data['notificationurl'];

    $sql = "Insert into " . $GLOBALS['PREFIX'] . "agent.siemConfiguration (name,global,scope,logUrl,dartUrl,proUrl,eventUrl,assetUrl,compUrl,nocUrl,patchUrl,username,createtime,modifiedUsername,modifiedtime) values "
        . "(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE  modifiedUsername=?,modifiedtime=?,scope=?,logUrl=?,dartUrl=?,"
        . "proUrl=?,eventUrl=?,assetUrl=?,compUrl=?,nocUrl=?,patchUrl=?,global=?";
    $sqlres = $pdo->prepare($sql);
    $sqlres->execute([
        $name, $global, $sitename, $logurl, $darturl, $proactiveurl, $eventurl, $asseturl, $complianceurl, $notificationurl, $patchurl, $username,
        $time, '', 0, $username, $time, $sitename, $logurl, $darturl, $proactiveurl, $eventurl, $asseturl, $complianceurl, $notificationurl, $patchurl, $global
    ]);

    $logkey = $sitename . '#log';
    $dartkey = $sitename . '#dart';
    $prokey = $sitename . '#pro';
    $eventkey = $sitename . '#event';
    $assetkey = $sitename . '#asset';
    $compkey = $sitename . '#comp';
    $nockey = $sitename . '#noc';
    $patchkey = $sitename . '#patch';

    $tempArr[] = array(
        $logkey => $logurl, $dartkey => $darturl, $prokey => $proactiveurl, $eventkey => $eventurl, $assetkey => $asseturl,
        $compkey => $complianceurl, $nockey => $notificationurl, $patchkey => $patchurl
    );
    sendDataToNode($tempArr);
    $res = array("status" => "success");
    echo json_encode($res);
}

function SIEM_editConfig()
{

    $pdo = pdo_connect();

    $data = getPostValues();

    if (safe_sizeof($data) === 0) {
        $data = $_REQUEST;
    }
    $time = time();
    $username = $_SESSION['user']['username'];
    $id = $data['id'];
    $global = $data['global'];
    $sitename = $data['sitename'];
    $logurl = $data['logurl'];
    $darturl = $data['darturl'];
    $eventurl = $data['eventurl'];
    $asseturl = $data['asseturl'];
    $proactiveurl = $data['proactiveurl'];
    $patchurl = $data['patchurl'];
    $complianceurl = $data['complianceurl'];
    $notificationurl = $data['notificationurl'];

    $sql_change = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.siemConfiguration  set global=?,scope=?,logUrl=?,dartUrl=?,proUrl=?"
        . ",eventUrl=?,assetUrl=?,compUrl=?,nocUrl=?,patchUrl=? where id=?");
    $sql_change->execute([$global, $sitename, $logurl, $darturl, $proactiveurl, $eventurl, $asseturl, $complianceurl, $patchurl, $id]);
    $Affected = $pdo->lastInsertId();
    if ($Affected) {
        $sql_change_mod = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.siemConfiguration set modifiedUsername=?,modifiedtime=? where id=?");
        $sql_change_mod->execute([$username, $time, $id]);
        $res = array("status" => "success");
    } else {
        $res = array("status" => "failed");
    }



    echo json_encode($res);
}

function getSelectedSite($site)
{

    $sitelist = $_SESSION['user']['site_list'];
    $option = '';
    foreach ($sitelist as $key => $val) {
        $key = UTIL_GetTrimCompanyName($key);
        if ($val == $site) {
            $selected = $key;
            $option .= "<option value='$key' selected>$key</option>";
        } else {
            $option .= "<option value='$key'>$key</option>";
        }
    }
    return $option;
}

function getSiemData()
{

    $username = $_SESSION['user']['logged_username'];
    $pdo = pdo_connect();

    $sql = $pdo->prepare("Select * from " . $GLOBALS['PREFIX'] . "agent.siemConfiguration where username = ?");
    $sql->execute([$username]);
    $sqlres = $sql->fetchAll();

    $recordList = array();
    foreach ($sqlres as $key => $val) {
        $name = $val['name'];
        $id = $val['id'];
        $site = $val['scope'];
        $confName = '<p class="ellipsis" title="' . $name . '" id="' . $id . '">' . $name . '</p>';
        $siteName = '<p class="ellipsis" title="' . $site . '" id="' . $site . '">' . $site . '</p>';
        $id = $val['id'];
        $recordList[] = array($confName, $siteName, $id);
    }
    echo json_encode($recordList);
}

function SIEM_getSiemData()
{

    $id = url::requestToAny('id');
    $pdo = pdo_connect();

    $sql = $pdo->prepare("Select * from " . $GLOBALS['PREFIX'] . "agent.siemConfiguration where id =? limit 1");
    $sql->execute([$id]);
    $sqlRes = $sql->fetch();

    $name = '';
    $global = 0;
    $site = '';
    $logurl = '';
    $darturl = '';
    $prourl = '';
    $eventurl = '';
    $asseturl = '';
    $compurl = '';
    $nocurl = '';
    $patchurl = '';

    if ($sqlRes['id'] != '') {

        $name = $sqlRes['name'];
        $global = $sqlRes['global'];
        $scope = getSelectedSite($sqlRes['scope']);
        $logurl = $sqlRes['logUrl'];
        $darturl = $sqlRes['dartUrl'];
        $eventurl = $sqlRes['eventUrl'];
        $asseturl = $sqlRes['assetUrl'];
        $compurl = $sqlRes['compUrl'];
        $nocurl = $sqlRes['nocUrl'];
        $patchurl = $sqlRes['patchUrl'];
        $prourl = $sqlRes['proUrl'];

        $resArr = array(
            "name" => $name, "global" => $global, "site" => $scope, "log" => $logurl, "dart" => $darturl,
            "event" => $eventurl, "asset" => $asseturl, "comp" => $compurl, "noc" => $nocurl, "patch" => $patchurl, "pro" => $prourl
        );
    } else {
        $resArr = array(
            "name" => $name, "global" => $global, "site" => $site, "log" > $logurl, "dart" => $darturl,
            "event" => $eventurl, "asset" => $asseturl, "comp" => $compurl, "noc" => $nocurl, "patch" => $patchurl, "pro" => $prourl
        );
    }
    echo json_encode($resArr);
}

function get_samplefileDownload()
{

    $index = 2;
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'key');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'value');

    $objPHPExcel->getActiveSheet()->setCellValue('A2', 'Content-Type');
    $objPHPExcel->getActiveSheet()->setCellValue('B2', 'application/json');
    $objPHPExcel->getActiveSheet()->setCellValue('A3', 'Authorization');
    $objPHPExcel->getActiveSheet()->setCellValue('B3', 'Bearer eyJhbGciOiJIUzI1NiIsInR5');


    $fn = "SampleConfList.csv";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
    ob_end_clean();
    $objWriter->save('php://output');
}

function SIEM_deleteConfiguration()
{

    $pdo = pdo_connect();
    $id = url::requestToAny('sel');

    $sql = $pdo->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "agent.siemConfiguration WHERE id=?");
    $sql->execute([$id]);

    echo "success";
}
