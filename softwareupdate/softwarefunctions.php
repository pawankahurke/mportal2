<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-dbConnect.php';
require_once("../include/common_functions.php");
include_once '../lib/l-dashboard.php';
include_once '../lib/l-coredbn.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';

global $db;
$pdo = pdo_connect();
$function = '';

nhRole::dieIfnoRoles(['softwaredetails']); // roles: softwaredetails
if (url::issetInRequest('function')) {
    $function = url::requestToText('function');
    $function();
}

function softwareData()
{
    nhRole::dieIfnoRoles(['softwaredetails']); // roles: softwaredetails
    // if (!isset($_SESSION['searchValue']) || empty($_SESSION['searchValue']) || !isset($_SESSION['searchType']) || empty($_SESSION['searchType'])) {
    //     echo json_encode(['data' => '{}']);
    //     return;
    // }

    $pdo = pdo_connect();
    $key = '';
    $draw = url::requestToAny('draw');
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];

    $searchVal = ''; //url::requestToAny('search')['value'];

    $orderval = url::requestToAny('order') ? url::requestToAny('order')[0]['column'] : '';

    if ($orderval != '') {
        $orderColoumn = url::requestToAny('columns')[$orderval]['data'];
        $ordertype = url::requestToAny('order')[0]['dir'];
        $orderValues = "group by $orderColoumn $ordertype";
    } else {
        $orderValues = "group by sitename";
    }

    if ($searchType == 'ServiceTag' || $searchType == 'Groups') {
        $recordList[] = array(
            "id" => '', "site" => '', "sitename" => '',
            "machine" => 'Software update can be viewed only at Site Level', "version" => '', "os" => '', "action" => ''
        );
    } else {
        $dataScope = UTIL_GetSiteScope(NanoDB::connect(), $searchValue, $searchType);
        $dataScope = [$dataScope];
        $softwaregriddata = DASH_GetSoftwareUpdateData($key, $searchValue, $searchType, $dataScope, $searchVal, $orderValues, '', $pdo);
        $census = DASH_UpdateCensus($key, $searchValue, $searchType, $dataScope, $pdo);
        $totalRecords = safe_count($softwaregriddata);
        if ($totalRecords > 0) {
            foreach ($softwaregriddata as $key => $row) {
                $site = $row['sitename'];
                $num = $census[trim($row['sitename'])];
                $site = ($site == '') ? '' : $site;

                if ($num == '') {
                    $machine = 0;
                } else if ($num == 1) {
                    $machine = $num . " Machine";
                } else if ($num > 1) {
                    $machine = $num . " Machines";
                } else {
                    $machine = "NA";
                }

                $siteres = DASH_get_desiredversionlist($key, $site, $pdo);
                $totalsiteres = safe_count($siteres);
                if ($totalsiteres > 0) {

                    foreach ($siteres as $value) {
                        if ($value['os'] != '' || $value['version'] != '') {
                            $id = $value['id'];
                            $osversion = ($value['os'] == '') ? 'NA' : $value['os'];
                            $version = $value['version'];
                            $del = '<a onclick="Versionosremove(' . $id . ');" style="cursor: pointer; color: #48b2e4;margin-left: -1px;">Remove</a>';
                        }

                        $recordList[] = array(
                            "id" => '' . $row["id"] . '',
                            "site" => $site,
                            "completeSiteName" => $row['sitename'],
                            "sitename" => '<p class="ellipsis" id="' . $site . '" title="' . UTIL_GetTrimmedGroupName($site) . '">' . UTIL_GetTrimmedGroupName($site) . '</p>',
                            "machine" => '<p class="ellipsis" title="' . $machine . '" >' . $machine . '</p>', "os" => '<p class="ellipsis" title="' . $osversion . '" >' . $osversion . '</p>',
                            "version" => '<p class="ellipsis" title="' . $version . '" >' . $version . '</p>',
                            "action" => '<p class="ellipsis">' . $del . '</p>'
                        );
                    }
                } else {
                    $id = '-';
                    $osversion = 'NA';
                    $version = '-';
                    $del = '-';

                    $recordList[] = array(
                        "id" => '' . $row["id"] . '',
                        "site" => $site,
                        "completeSiteName" => $row['sitename'],
                        "sitename" => '<p class="ellipsis" id="' . $site . '" title="' . UTIL_GetTrimmedGroupName($site) . '">' . UTIL_GetTrimmedGroupName($site) . '</p>',
                        "machine" => '<p class="ellipsis" title="' . $machine . '" >' . $machine . '</p>', "os" => '<p class="ellipsis" title="' . $osversion . '" >' . $osversion . '</p>',
                        "version" => '<p class="ellipsis" title="' . $version . '" >' . $version . '</p>',
                        "action" => '<p class="ellipsis">' . $del . '</p>'
                    );
                }
            }
        } else {
            $recordList[] = array("id" => '', "site" => '', "sitename" => '', "machine" => '<p style="margin-left:35%;">No data available in table<p>', "version" => '', "os" => '', "action" => '');
        }
    }
    create_auditLog('Software Update', 'View', 'Success');
    $jsonData = array("draw" => $draw, "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecords, "data" => $recordList);
    echo json_encode($jsonData);
}

function get_versionosremove()
{
    nhRole::dieIfnoRoles(['softwaredetails']); // roles: softwaredetails
    $pdo = pdo_connect();
    $key = '';
    $id = url::requestToText('id');

    $deleteos = DASH_GetDeleteOS($key, $id, $pdo);

    if ($deleteos == 1) {
        $jsonData = array('msg' => 'success');
    } else {
        $jsonData = array('msg' => 'invalid');
    }
    echo json_encode($jsonData);
}

function get_editversiondata()
{

    nhRole::dieIfnoRoles(['softwaredetails', 'editUpdate']); // roles: softwaredetails, editUpdate

    $pdo = pdo_connect();
    $key = '';
    $id = url::requestToText('idx');
    $site = url::requestToText('sitename');

    $sqldata = $pdo->prepare("select version from " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites where sitename = ?");
    $sqldata->execute([$site]);
    $resultsql = $sqldata->fetchAll();

    foreach ($resultsql as $val) {
        $osDetails[$val['os']] = $val['version'];
    }

    $sitename = UTIL_GetTrimmedGroupName($site);
    $window = $osDetails['Windows'];
    $android = $osDetails['Android'];
    $linux = $osDetails['Linux'];
    $mac = $osDetails['MAC'];

    $jsonData = array('sitename' => $sitename, 'windows' => $window, 'android' => $android, 'linux' => $linux, 'mac' => $mac);
    echo json_encode($jsonData);
}

function get_versionadd()
{

    nhRole::dieIfnoRoles(['softwaredetails', 'updatesoftwareversion']); // roles: softwaredetails, updatesoftwareversion


    $db = pdo_connect();
    $key = '';
    $window = url::requestToText('window');
    $android = url::requestToText('android');
    $Linux = url::requestToText('linux');
    $mac = url::requestToText('mac');
    $sites = url::requestToText('site');
    $ios = url::requestToText('ios');


    $versionupdate = DASH_GetVersionUpdate($key, $window, $android, $Linux, $mac, $ios, $sites, $db);

    echo json_encode($versionupdate);
}

function get_versionmachinelist()
{
    nhRole::dieIfnoRoles(['softwaredetails']); // roles: softwaredetails

    $db = pdo_connect();
    $key = '';
    $sitename = url::requestToText('site');
    $draw = url::requestToText('draw');
    $start = url::requestToText('start');
    $length = url::requestToText('length');
    $username = $_SESSION['user']['username'];

    $checkAccess = checkUserSiteAccess($username, $sitename);

    if (!$checkAccess) {
        echo "Permission Denied";
        exit;
    }

    $limit = " limit $start , $length";
    $orderval = url::requestToText('order') ? url::requestToText('order')[0]['column'] : '';

    $recordList = [];
    if ($orderval != '') {
        $orderColoumn = url::requestToAny('columns')[$orderval]['data'];
        $ordertype = url::requestToAny('order')[0]['dir'];
        $orderValues = "group by $orderColoumn $ordertype";
    }

    $versioncount = DASH_GetMachines($key, $sitename, $orderValues, '', $db);
    $versionmachine = DASH_GetMachines($key, $sitename, $orderValues, '', $db);
    $totalRecords = safe_count($versioncount);
    if ($totalRecords > 0) {
        foreach ($versionmachine as $key => $val) {
            $machine = $val['machine'];
            $timecontact = date('m/d/Y H:i:s', $val['timecontact']);
            $timeupdate = date('m/d/Y H:i:s', $val['timeupdate']);
            $lastversion = $val['lastversion'];
            $oldversion = $val['oldversion'];
            $newversion = $val['newversion'];

            $recordList[] = array(
                '<p class="ellipsis" title="' . $machine . '">' . $machine . '</p>',
                '<p class="ellipsis" title="' . $lastversion . '">' . $lastversion . '</p>',
                '<p class="ellipsis" title="' . $oldversion . '">' . $oldversion . '</p>'
            );
        }
    } else {
        $recordList[] = array("machine" => '', "lastversion" => '', "oldversion" => '');
    }
    echo json_encode($recordList);
}

function get_machinelistexport()
{
    nhRole::dieIfnoRoles(['softwaredetails']); // roles: softwaredetails
    $db = pdo_connect();
    $sitename = url::requestToText('site');
    $index = 2;

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Machine Name');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Last Contact');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Last Version');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Last Update');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Old Version');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'New Version');

    $versionmachine = DASH_GetMachines($key, $sitename, '', '', $db);

    if ($versionmachine) {
        foreach ($versionmachine as $key => $val) {
            $machine = $val['machine'];
            $timecontact = date('m/d/Y H:i:s', $val['timecontact']);
            $lastversion = $val['lastversion'];
            if ($val['timeupdate'] == 0) {
                $timeupdate = "";
            } else {
                $timeupdate = date('m/d/Y H:i:s', $val['timeupdate']);
            }
            $oldversion = $val['oldversion'];
            $newversion = $val['newversion'];

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $machine);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $timecontact);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $lastversion);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $timeupdate);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $oldversion);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $newversion);
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }
    $fn = "VersionList.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
    $auditRes = create_auditLog('Software Update', 'Export', 'Success', $_REQUEST, $gpname);

    $objWriter->save('php://output');
}

function get_versionlist()
{
    nhRole::dieIfnoRoles(['softwaredetails']); // roles: softwaredetails
    $db = pdo_connect();
    $key = '';
    $start = url::requestToAny('start');
    $length = url::requestToAny('length');
    $limit = " limit $start , $length";
    $orderval = url::requestToAny('order') ? url::requestToAny('order')[0]['column'] : '';
    $recordList = [];
    if ($orderval != '') {
        $orderColoumn = url::requestToAny('columns')[$orderval]['data'];
        $ordertype = url::requestToAny('order')[0]['dir'];
        $orderValues = "group by $orderColoumn $ordertype";
    }
    $ctype = $_SESSION['user']['customerType'];
    if ($ctype == 0 || $ctype == "0") {
        $username = $_SESSION['user']['username'];
    } else {
        $username = $_SESSION['user']['username'];
    }

    $versioncount = DASH_allversionlist($key, $db, $username, $orderValues, '');
    $versionlist = DASH_allversionlist($key, $db, $username, $orderValues, '');

    $totalRecords = safe_count($versioncount);
    if ($totalRecords > 0) {
        foreach ($versionlist as $key => $val) {
            $id = $val['id'];
            $name = $val['name'];
            $version = $val['version'];
            $url = $val['url'];
            $commandline = $val['cmdline'];
            $os = $val['os'];

            $recordList[] = array(
                'id' => $id,
                '<p class="ellipsis" title="' . $name . '" id="' . $name . '">' . $name . '</p>',
                '<p class="ellipsis" title="' . $version . '" id="' . $version . '">' . $version . '</p>',
                '<p class="ellipsis" title="' . $url . '" id="' . $url . '">' . $url . '</p>',
                '<p class="ellipsis" title="' . $commandline . '" id="' . $commandline . '">' . $commandline . '</p>',
                '<p class="ellipsis" title="' . $os . '" id="' . $os . '">' . $os . '</p>'
            );
        }
    } else {
        $recordList = array();
    }
    echo json_encode($recordList);
}

function get_versionsubmit()
{
    nhRole::dieIfnoRoles(['softwaredetails', 'addversion']); // roles: softwaredetails, addversion

    $db = NanoDB::connect();
    $name = url::requestToText('vname');
    $version = url::requestToText('vnumber');
    $os = url::requestToText('os');
    $check = url::requestToText('check');
    $username = url::issetInRequest('uname') ? url::requestToText('uname') : "";
    $password = url::issetInRequest('pass') ? url::requestToText('pass') : "";
    $cmdline = url::issetInRequest('command') ? strip_tags(urldecode(url::requestToAny('command'))) : "";
    $logedusername = strip_tags($_SESSION['user']['username']);
    $downloadurl = false;

    $checksql = $db->prepare("select name from " . $GLOBALS['PREFIX'] . "swupdate.Downloads where name=?");
    $checksql->execute([$name]);
    $checkRes = $checksql->fetch();
    $allowedMimes = ['application/x-msdownload', 'application/octet-stream', 'application/octet-stream', 'application/vnd.android.package-archive', 'application/vnd.oasis.opendocument.presentation', 'application/x-apple-diskimage', 'application/octet-stream', 'application/tar', 'application/tar+gzip', 'application/tar', 'text/x-shellscript'];

    if ($checkRes['name'] == '') {
        if (isset($_FILES['client'])) {
            $fileArray = $_FILES['client'];
            if (isset($fileArray['error']) && is_numeric($fileArray['error']) && intval($fileArray['error']) == 0) {
                if (!in_array($fileArray['type'], $allowedMimes)) {
                    echo json_encode(array("msg" => 'invalidmime'));
                    return false;
                }
                $uploadResult = CURL::uploadFileInStorage($_FILES['client']);
                if (!$uploadResult['message'] || $uploadResult['message'] !== 'Good') {
                    echo json_encode(array("msg" => 'Failed to upload file'));
                    return false;
                }
                $downloadurl = $uploadResult['link'];
            }
        } else if (url::issetInPost('Durl')) {
            $downloadurl = url::postToText('Durl');
        }

        $res = false;

        if ($downloadurl) {
            $sql = $db->prepare("insert into " . $GLOBALS['PREFIX'] . "swupdate.Downloads (name,owner,global,os,version,url,username,password,cmdline) VALUES (?,?,?,?,?,?,?,?,?)");
            return false;

            $sql->execute([$name, $logedusername, $check, $os, $version, $downloadurl, $username, $password, $cmdline]);
            $res = $db->lastInsertId();
        }

        if ($res) {
            $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
            $auditRes = create_auditLog('Software Update', 'Add Version', 'Success', $_REQUEST, $gpname);

            echo json_encode(array("msg" => 'success'));
            return true;
        } else {
            $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
            $auditRes = create_auditLog('Software Update', 'Add Version', 'Failed', $_REQUEST, $gpname);

            echo json_encode(array("msg" => 'failed', "_l" => __LINE__));
            return false;
        }
    } else {
        $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
        $auditRes = create_auditLog('Software Update', 'Add Version', 'Failed', $_REQUEST, $gpname);

        echo json_encode(array("msg" => 'failed', "_l" => __LINE__));
        return false;
    }
}

function get_editversiondetailedlist()
{
    nhRole::dieIfnoRoles(['softwaredetails', 'addversion']); // roles: softwaredetails, addversion

    $key = '';
    $db = pdo_connect();
    $vid = UTIL_GetInteger('id', 9);

    $versionDetail = DASH_GetVersionDetailList($key, $vid, $db);
    $Os = $versionDetail['os'];
    $version = $versionDetail['version'];
    $name = $versionDetail['name'];
    $url = $versionDetail['url'];
    $username = $versionDetail['username'];
    $password = $versionDetail['password'];
    $cmdline = $versionDetail['cmdline'];
    $global = $versionDetail['global'];

    $recordList[] = array(
        'os' => $Os, 'version' => $version, 'name' => $name, 'url' => $url, 'username' => $username,
        'password' => $password, 'cmdline' => $cmdline, 'check' => $global
    );

    echo json_encode($recordList);
}

function get_editversionsubmit()
{

    nhRole::dieIfnoRoles(['softwaredetails', 'addversion']); // roles: softwaredetails, addversion

    $db = NanoDB::connect();
    $id = url::requestToText('vid');
    $name = url::requestToText('vname');
    $version = url::requestToText('vnumber');
    $os = url::requestToText('os');
    $check = url::requestToText('check');
    $username = url::requestToText('uname');
    $password = url::requestToText('pass');
    $cmdline = url::requestToText('command');
    $logedusername = $_SESSION['user']['username'];
    $downloadurl = false;
    $return = array("msg" => 'failed', "_l" => __LINE__);

    $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "swupdate.Downloads WHERE id = ?");
    $sql->execute([$id]);
    $existingData = $sql->fetch(PDO::FETCH_ASSOC);

    if ($existingData) {
        if (isset($_FILES['client'])) {
            $fileArray = $_FILES['client'];
            if (isset($fileArray['error']) && is_numeric($fileArray['error']) && intval($fileArray['error']) == 0) {
                $uploadDirectory = 'swd';
                $moveFileTo = '../' . $uploadDirectory;
                $extension = pathinfo($_FILES['client']['name'], PATHINFO_EXTENSION);
                $newFileName = sha1(uniqid() . time() . rand(rand(1231, 88791), 8768767)) . '_' . time() . '.' . $extension;

                @move_uploaded_file($fileArray['tmp_name'], $moveFileTo . '/' . $newFileName);
                global $base_url;
                $downloadurl = $base_url . $uploadDirectory . '/' . $newFileName;
            }
        } else if (url::issetInPost('Durl')) {
            $downloadurl = url::postToText('Durl');
        }

        if ($downloadurl) {
            $sql = $db->prepare("update " . $GLOBALS['PREFIX'] . "swupdate.Downloads set name=?, owner=?, global=?, os=?, version=?, url=?, username=?, password=?, cmdline =? WHERE id = ? ");
            try {
                $sql->execute([$name, $logedusername, $check, $os, $version, $downloadurl, $username, $password, $cmdline, $id]);
                $return = json_encode(array("msg" => 'success', "_l" => __LINE__));
            } catch (PDOException $e) {
            }
        }
    }

    echo json_encode($return);
}

function delete_version()
{

    nhRole::dieIfnoRoles(['softwaredetails', 'deleteversion']); // roles: softwaredetails, deleteversion

    $db = pdo_connect();
    $id = url::requestToText('id');


    $sql = $db->prepare("delete from " . $GLOBALS['PREFIX'] . "swupdate.Downloads where id = ?");
    $sql->execute([$id]);
    $result = $sql->rowCount();
    if ($result) {
        $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
        $auditRes = create_auditLog('Software Update', 'Delete Version', 'Success', $id, $gpname);

        $recordlist = array('msg' => 'success');
    } else {
        $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
        $auditRes = create_auditLog('Software Update', 'Delete Version', 'Failed', $id, $gpname);

        $recordlist = array('msg' => 'failed', "_l" => __LINE__);
    }
    echo json_encode($recordlist);
}

function get_copyversionData()
{
    nhRole::dieIfnoRoles(['softwaredetails', 'copyversion']); // roles: softwaredetails, copyversion

    $db = pdo_connect();
    $id = url::requestToText('id');


    $sql = $db->prepare("select id,global,name,version,os,url,username,password,cmdline from " . $GLOBALS['PREFIX'] . "swupdate.Downloads where id = ?");
    $sql->execute([$id]);
    $sqlres = $sql->fetchAll();

    $totalRecords = safe_count($sqlres);
    if ($totalRecords > 0) {
        foreach ($sqlres as $key => $val) {
            $global = $val['global'];
            $name = $val['name'];
            $version = $val['version'];
            $os = $val['os'];
            $username = $val['username'];
            $password = $val['password'];
            $url = $val['url'];
            $commandline = $val['cmdline'];


            $recordList = array(
                'global' => $global, 'name' => $name, 'version' => $version, 'os' => $os, 'username' => $username,
                'password' => $password, 'url' => $url, 'commandline' => $commandline
            );
        }
    } else {
        $recordList = array();
    }
    create_auditLog('Software Update', 'Copy Version', 'Success', $id);

    echo json_encode($recordList);
}

function get_copydataInsert()
{
    nhRole::dieIfnoRoles(['softwaredetails', 'copyversion']); // roles: softwaredetails, copyversion

    $db = pdo_connect();
    $name = url::requestToText('vname');
    $version = url::requestToText('vnumber');
    $os = url::requestToText('os');
    $check = url::requestToText('check');
    $downloadurl = url::requestToText('Durl');
    $username = url::requestToText('uname');
    $password = url::requestToText('pass');
    $cmdline = url::requestToText('command');
    $logedusername = $_SESSION['user']['username'];


    $sqlname = $db->prepare("select id from " . $GLOBALS['PREFIX'] . "swupdate.Downloads where name = ? limit 1");
    $sqlname->execute([$name]);
    $sqlnameres = $sqlname->fetch();
    $sqlid = $sqlnameres['id'];

    if ($sqlid == '') {
        $sqlinsert = $db->prepare("insert into " . $GLOBALS['PREFIX'] . "swupdate.Downloads (name,owner,global,os,version,url,username,password,cmdline) VALUES (?,?,?,?,?,?,?,?,?)");
        $sqlinsert->execute([$name, $logedusername, $check, $os, $version, $downloadurl, $username, $password, $cmdline]);
        $res = $db->lastInsertId();
        if ($res) {
            $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
            $auditRes = create_auditLog('Software Update', 'Copy Version Added', 'Success', $_REQUEST, $gpname);

            $jsondata = array('msg' => 'success');
        } else {
            $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
            $auditRes = create_auditLog('Software Update', 'Copy Version Added', 'Failed', $_REQUEST, $gpname);

            $jsondata = array('msg' => 'failed', "_l" => __LINE__);
        }
    } else {
        $jsondata = array('msg' => 'error');
    }

    echo json_encode($jsondata);
}

function get_osversionList()
{
    nhRole::dieIfnoRoles(['softwaredetails']); // roles: softwaredetails 


    $pdo = pdo_connect();
    $logeduser = $_SESSION['user']['username'];

    $sites = url::issetInRequest('site') ? url::requestToText('site') : "";

    $sqlW = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "swupdate.Downloads where os = 'Windows' and (global = 1 or owner in ('',?)) order by name");
    $sqlW->execute([$logeduser]);
    $windowres = $sqlW->fetchAll();

    $sqlA = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "swupdate.Downloads where os = 'Android' and (global = 1 or owner in ('',?)) order by name");
    $sqlA->execute([$logeduser]);
    $androidres = $sqlA->fetchAll();

    $sqlL = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "swupdate.Downloads where os = 'Linux' and (global = 1 or owner in ('',?)) order by name");
    $sqlL->execute([$logeduser]);
    $linuxres = $sqlL->fetchAll();

    $sqlM = $pdo->prepare("select name from " . $GLOBALS['PREFIX'] . "swupdate.Downloads where os = 'MAC' and (global = 1 or owner in ('',?)) order by name");
    $sqlM->execute([$logeduser]);
    $macres = $sqlM->fetchAll();

    $sqli = $pdo->prepare("select name from " . $GLOBALS['PREFIX'] . "swupdate.Downloads where os = 'iOS' and (global = 1 or owner in ('',?)) order by name");
    $sqli->execute([$logeduser]);
    $iosres = $sqli->fetchAll();

    $winList = '';
    $andList = '';
    $linList = '';
    $macList = '';
    $iosList = '';

    $stmt1 = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites where sitename = ? and os = 'Windows'");
    $stmt1->execute([$sites]);
    $oscheckwindowres = $stmt1->fetch();
    $oscheckwindowVersion = $oscheckwindowres['version'];

    $stmt2 = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites where sitename = ? and os = 'Android'");
    $stmt2->execute([$sites]);
    $oscheckandroidres = $stmt2->fetch();
    $oscheckandroidVersion = $oscheckandroidres['version'];

    $stmt3 = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites where sitename = ? and os = 'Linux'");
    $stmt3->execute([$sites]);
    $oschecklinuxres = $stmt3->fetch();
    $oschecklinuxVersion = $oschecklinuxres['version'];

    $stmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites where sitename = ? and os = 'MAC'");
    $stmt->execute([$sites]);
    $oscheckmacres = $stmt->fetch();
    $oscheckmacVersion = $oscheckmacres['version'];

    $stmt4 = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites where sitename = ? and os = 'iOS'");
    $stmt4->execute([$sites]);
    $oscheckiosres = $stmt4->fetch();
    $oscheckiosVersion = $oscheckiosres['version'];

    if (safe_count($windowres) > 0) {
        foreach ($windowres as $key => $val) {
            $selectedVal = "";
            if (($oscheckwindowVersion === $val['name']) && ($oscheckwindowVersion != '')) {
                $selectedVal = "selected";
            }
            $winList .= "<option value='" . $val['name'] . "' " . $selectedVal . " >" . $val['name'] . "</option>";
        }
    } else {
        $winList .= "<option value=''>No data available</option>";
    }

    if (safe_count($androidres) > 0) {
        foreach ($androidres as $key => $val) {
            $selectedVal = "";
            if (($oscheckandroidVersion === $val['name']) && ($oscheckandroidVersion != '')) {
                $selectedVal = "selected";
            }
            $andList .= "<option value='" . $val['name'] . "' " . $selectedVal . ">" . $val['name'] . "</option>";
        }
    } else {
        $andList .= "<option value=''>No data available</option>";
    }

    if (safe_count($linuxres) > 0) {
        foreach ($linuxres as $key => $val) {
            $selectedVal = "";
            if (($oschecklinuxVersion === $val['name']) && ($oschecklinuxVersion != '')) {
                $selectedVal = "selected";
            }
            $linList .= "<option value='" . $val['name'] . "' " . $selectedVal . ">" . $val['name'] . "</option>";
        }
    } else {
        $linList .= "<option value=''>No data available</option>";
    }

    if (safe_count($macres) > 0) {
        foreach ($macres as $key => $val) {
            $selectedVal = "";
            if (($oscheckmacVersion === $val['name']) && ($oscheckmacVersion != '')) {
                $selectedVal = "selected";
            }
            $macList .= "<option value='" . $val['name'] . "' " . $selectedVal . ">" . $val['name'] . "</option>";
        }
    } else {
        $macList .= "<option value=''>No data available</option>";
    }

    if (safe_count($iosres) > 0) {
        foreach ($iosres as $key => $val) {
            $selectedVal = "";
            if (($oscheckiosVersion === $val['name']) && ($oscheckiosVersion != '')) {
                $selectedVal = "selected";
            }
            $iosList .= "<option value='" . $val['name'] . "' " . $selectedVal . ">" . $val['name'] . "</option>";
        }
    } else {
        $iosList .= "<option value=''>No data available</option>";
    }
    $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
    create_auditLog('Software update', 'OS Version Update', 'Success', $_REQUEST, $gpname);

    $returnArray = array('win' => $winList, 'mac' => $macList, 'and' => $andList, 'linux' => $linList, 'ios' => $iosList);
    echo json_encode($returnArray);
}

function uploadCoreDb()
{
    nhRole::dieIfnoRoles(['softwaredetails', 'addversion']); // roles: softwaredetails, addversion

    global $dbnAbsolutePath;

    $fileName = 'core-file';
    $return = ['msg' => 'failed', "_l" => __LINE__];
    $pathInfo = pathinfo($dbnAbsolutePath);

    $ext = $pathInfo['extension'];

    if ($ext != 'dbn') {
        $return = ['msg' => 'Please upload file with .dbn extension'];
    }

    $renameExistingTo = date('mdY') . '_' . time() . '_' . '.' . $pathInfo['extension'];
    $moveFileTo = $pathInfo['dirname'];

    try {
        if (isset($_FILES[$fileName])) {
            $fileArray = $_FILES[$fileName];
            if (isset($fileArray['error']) && is_numeric($fileArray['error']) && intval($fileArray['error']) == 0 && $fileArray['type'] == 'application/octet-stream') {
                $extension = pathinfo($fileArray['name'], PATHINFO_EXTENSION);
                if ($pathInfo['extension'] == $extension) {
                    @rename($dbnAbsolutePath, $moveFileTo . '/' . $renameExistingTo);
                    $moved = move_uploaded_file($fileArray['tmp_name'], $dbnAbsolutePath);
                    @chmod($dbnAbsolutePath, 777);
                    $_SESSION['dbn_import_time'] = time();
                    $_SESSION['dbn_import_key'] = md5(uniqid() . rand(2123, 49787651));
                    if ($moved)
                        $return = ['msg' => 'success', 'import_key' => $_SESSION['dbn_import_key']];
                }
            }
            $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
            create_auditLog('Software Update', 'CoreDb upload', 'Success', NULL, $gpname);
        }
    } catch (Exception $e) {
        logs::log("uploadCoreDb Error:", $e);
        $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
        create_auditLog('Software Update', 'CoreDb upload', 'Failed', NULL, $gpname);

        @rename($moveFileTo . '/' . $renameExistingTo, $dbnAbsolutePath);
    }


    echo json_encode($return);
}

function inspectDBNVers()
{
    nhRole::dieIfnoRoles(['softwaredetails', 'addversion']); // roles: softwaredetails, addversion
    global $dbnFileName;
    global $dbnAbsolutePath;

    $importKey = url::postToText('key');
    $response = '';

    $response .= "Inspecting " . $dbnFileName . " variables...<br/>";
    try {
        $response .= "Attempting to open " . $dbnAbsolutePath . "...<br/>";
        $dbsqlite = new SQLite3($dbnAbsolutePath, SQLITE3_OPEN_READONLY);
        $response .= "Database opened.<br/>";
        $query = $dbsqlite->query("SELECT vers, count(*) as num FROM VarVersions GROUP BY vers");
        if (!$query) {
            $response .= "Failed to select from VarVersions: " . $dbsqlite->lastErrorMsg();
            echo $response;
            return false;
        }
        while ($row = $query->fetchArray()) {
            $tables = array('VarVersions', 'VarValues', 'ValueMap', 'Scrips');

            foreach ($tables as $table) {
                $sql = "select count(*) as num from " . $GLOBALS['PREFIX'] . "core.DBN_$table where cksum='"
                    . safe_addslashes($row['vers']) . "'";
                $set = NanoDB::find_one($sql);
                if ($set) {
                    $response .= "Found " . $set['num'] . " variable(s) in server DBN_$table table for " . $row['vers'] . "<br/>";
                }
            }

            $sql = "select vers from " . $GLOBALS['PREFIX'] . "core.DBN_Versions WHERE vers='"
                . safe_addslashes($row['vers']) . "'";
            $set = NanoDB::find_one($sql);

            if ($set) {
                $response .= "The version " . $row['vers'] . " has already been imported.";
            } else {
                $response .= "<br/><a href=\"javascript:void(0)\" data-qa=\"import-dbn\" class=\"btn btn-rose btn-round btn-file btn-success  import-dbn-data-w\" style='color: #fff;' data-key=\"" . $importKey . "\" data-type=\"\" data-vers=\"" . rawurlencode($row['vers'])
                    . "\">Import " . $row['vers'] . "</a> (" . $row['num']
                    . " variables)<br/>";
                $response .= "<br/>"
                    . " <a href=\"javascript:void(0)\"  data-qa=\"import-dbn-one-way\" data-key=\"" . $importKey . "\"  style='color: #fff;'  class=\"btn btn-rose btn-round btn-file btn-success  import-dbn-data-w\" data-vers=\"" . rawurlencode($row['vers']) . "\" data-type=\"1\">Import (one-way sync) " . $row['vers'] . "</a> (" . $row['num'] . " variables)<br/>";
            }
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        $response = $e->getMessage();
    }

    echo $response;
}

function importVars()
{
    nhRole::dieIfnoRoles(['softwaredetails', 'addversion']); // roles: softwaredetails, addversion
    global $dbnFileName;
    global $dbnAbsolutePath;

    $pdo = pdo_connect();

    $vers = url::issetInPost('vers') ? url::postToStringAz09('vers') : '';
    if ($vers == '') {
        echo 'core dbn version not available!';
        return false;
    }

    try {
        echo "Attempting to open " . $dbnFileName . "...<br/>";
        $dbsqlite = new SQLite3($dbnAbsolutePath, SQLITE3_OPEN_READONLY);
        $importvers = strip_tags(htmlentities($vers));

        $sql = "select vers from " . $GLOBALS['PREFIX'] . "core.DBN_Versions WHERE vers=? limit 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$importvers]);
        $set = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($set) {
            echo "The version in core.dbn, " . $importvers . ", has already been imported.";
            return;
        }

        echo "Database opened, now importing $importvers<br/>";

        $verboseoutput = array();

        if (process_vars($pdo, $dbsqlite) > 0) {
            echo "<br/>Import failed, aborting.<br/>";
            do_cleanup($importvers, $verboseoutput, $pdo);
            return;
        }

        if (process_table("SELECT * FROM VarVersions WHERE vers='" . $importvers . "'", 0, $verboseoutput, 'VarVersions', $pdo, $dbsqlite, $importvers) > 0) {
            echo "<br/>Import failed, aborting.<br/>";
            do_cleanup($importvers, $verboseoutput, $pdo);
            return;
        }
        if (process_table("SELECT * FROM VarValues", 1, $verboseoutput, 'VarValues', $pdo, $dbsqlite, $importvers) > 0) {
            echo "<br/>Import failed, aborting.<br/>";
            do_cleanup($importvers, $verboseoutput, $pdo);
            return;
        }
        if (process_table("SELECT * FROM ValueMap", 2, $verboseoutput, 'ValueMap', $pdo, $dbsqlite, $importvers) > 0) {
            echo "<br/>Import failed, aborting.<br/>";
            do_cleanup($importvers, $verboseoutput, $pdo);
            return;
        }
        if (process_table("SELECT * FROM Scrips", 3, $verboseoutput, 'Scrips', $pdo, $dbsqlite, $importvers) > 0) {
            echo "<br/>Import failed, aborting.<br/>";
            do_cleanup($importvers, $verboseoutput, $pdo);
            return;
        }

        $type = (url::issetInPost('type') && !url::isEmptyInPost('type')) ? url::postToText('type') : 0;
        echo "<br/>Import succeeded, making version available to new clients...";

        $pdoOb = $pdo->prepare("INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.DBN_Versions (vers, type) VALUES (?, ?)");
        $pdoOb->execute([$importvers, $type]);
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        echo $e->getMessage();
    }
}
