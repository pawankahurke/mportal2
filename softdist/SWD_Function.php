<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../communication/common_communication.php';
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-user.php';
include_once '../lib/l-util.php';
include_once '../lib/l-swd.php';
include_once '../communication/l-comm.php';
include_once '../lib/passdata.php';
require_once '../include/common_functions.php';
include_once '../lib/l-setTimeZone.php';

global $base_url;
//Replace $routes['get'] with if else

// nhRole::dieIfnoRoles(['softwaredistribution']); // roles: softwaredistribution

if (url::requestToText('function') === 'getConfig') { // roles: softwaredistribution
    getConfig();
} else if (url::requestToText('function') === 'getConfigDistribute') { // roles: softwaredistribution
    getConfigDistribute();
} else if (url::requestToText('function') === 'getDeployExecuteAvailability') { // roles: softwaredistribution
    getDeployExecuteAvailability();
} else if (url::requestToText('function') === 'getConfigexecute') { // roles: softwaredistribution
    getConfigexecute();
} else if (url::requestToText('function') === 'MACconfigPatchFn') { // roles: softwaredistribution
    MACconfigPatchFn();
} else if (url::requestToText('function') === 'packagesSelectFn') { // roles: softwaredistribution
    packagesSelectFn();
} else if (url::getToText('function') === 'exportauditFn') { // roles: softwaredistribution
    exportauditFn();
} else if (url::requestToText('function') === 'softwareAuditDetailsFn') { // roles: softwaredistribution
    softwareAuditDetailsFn();
} else if (url::requestToText('function') === 'addFn') { // roles: softwaredistribution
    addFn();
} else if (url::requestToText('function') === 'Get_allSites') { // roles: softwaredistribution
    Get_allSites();
} else if (url::requestToText('function') === 'getAllPackageDetails') { // roles: softwaredistribution
    getAllPackageDetails();
} else if (url::requestToText('function') === 'checkConfigureStatus') { // roles: softwaredistribution
    checkConfigureStatus();
} else if (url::requestToText('function') === 'showDivStyle') { // roles: softwaredistribution
    showDivStyle();
} else if (url::requestToText('function') === 'get32bitConfig') { // roles: softwaredistribution
    get32bitConfig();
} else if (url::requestToText('function') === 'get64bitConfig') { // roles: softwaredistribution
    get64bitConfig();
} else if (url::requestToText('function') === 'getResetConfig') { // roles: softwaredistribution
    getResetConfig();
} else if (url::requestToText('function') === 'packageGridFn') { // roles: softwaredistribution
    packageGridFn();
}




//Replace $routes['post'] with if else
if (url::requestToText('function') === 'editPackageFn') { // roles: softwaredistribution
    editPackageFn();
} else if (url::requestToText('function') === 'updateUploadStatFn') { // roles: softwaredistribution
    updateUploadStatFn();
} else if (url::requestToText('function') === 'deleteFn') { // roles: softwaredistribution
    deleteFn();
} else if (url::requestToText('function') === 'editFn') { // roles: softwaredistribution
    editFn();
} else if (url::requestToText('function') === 'swdDetailFn') { // roles: softwaredistribution
    swdDetailFn();
} else if (url::postToText('function') === 'getSite') { // roles: softwaredistribution
    getSite();
} else if (url::requestToText('function') === 'addPackageFn') { // roles: softwaredistribution
    addPackageFn();
} else if (url::requestToText('function') === 'checkAvailabilityFn') { // roles: softwaredistribution
    checkAvailabilityFn();
} else if (url::requestToText('function') === 'saveftpconfig') { // roles: softwaredistribution
    saveftpconfig();
} else if (url::requestToText('function') === 'getFtpCdnDataFn') { // roles: softwaredistribution
    getFtpCdnDataFn();
} else if (url::requestToText('function') === 'savecdnconfig') { // roles: softwaredistribution
    savecdnconfig();
} else if (url::requestToText('function') === 'configPatchFn') { // roles: softwaredistribution
    configPatchFn();
} else if (url::requestToText('function') === 'distributeConfigurationFn') { // roles: softwaredistribution
    distributeConfigurationFn();
} else if (url::requestToText('function') === 'MACconfigPatchSubmitFn') { // roles: softwaredistribution
    MACconfigPatchSubmitFn();
} else if (url::requestToText('function') === 'saveDistributeConfigFn') { // roles: softwaredistribution
    saveDistributeConfigFn();
} else if (url::requestToText('function') === 'saveDistributeConfigFn_old') { // roles: softwaredistribution
    saveDistributeConfigFn_old();
} else if (url::requestToText('function') === 'getConfigFn') { // roles: softwaredistribution
    getConfigFn();
} else if (url::requestToText('function') === 'showconfigDetailsFn') { // roles: softwaredistribution
    showconfigDetailsFn();
} else if (url::requestToText('function') === 'getexecuteStatus') { // roles: softwaredistribution
    getexecuteStatus();
} else if (url::requestToText('function') === 'saveConfigFn') { // roles: softwaredistribution
    saveConfigFn();
} else if (url::requestToText('function') === 'getSelectedConfigVal') { // roles: softwaredistribution
    getSelectedConfigVal();
}

function explode_user_def($delim, $str)
{
    if (!is_null($str)) {
        return explode($delim, $str);
    } else {
        return [];
    }
}

if (url::requestToText('function') === '') { // roles: softwaredistribution
    $data = url::requestToAny('data');

    $ivBytes = hex2bin($iv);
    $keyBytes = hex2bin($key);
    $ctBytes = base64_decode($data);

    ob_clean();
    $postdecryptdata = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $keyBytes, $ctBytes, MCRYPT_MODE_CBC, $ivBytes));

    $postdata = explode_user_def('&', $postdecryptdata);
    $fndata = [];

    foreach ($postdata as $key => $value) {
        $postsubdata = explode_user_def('=', $value);
        if ($postsubdata[0] == 'function') {
            $function = $postsubdata[1];
        } else {
            $fndata[$postsubdata[0]] = $postsubdata[1];
        }
    }
    $function($fndata);
}


if (!function_exists('checkModulePrivilege')) {
    function checkModulePrivilege($roleName, $requiredVal)
    {
        return nhRole::checkModulePrivilege($roleName, $requiredVal);
    }
}




function packageGridFn()
{
    $type = url::requestToStringAz09('type');

    nhRole::dieIfnoRoles(['softwaredistribution']); // roles: softwaredistribution


    $db = pdo_connect();
    $limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
    $curPage = url::postToInt('nextPage') - 1;

    $limitStart = $limitCount * $curPage;

    $limitEnd = $limitStart + $limitCount;
    if ($limitStart > 0) {
        $limitStr = " LIMIT " . $limitStart . "," . $limitCount;
    } else {
        $limitStr = " LIMIT " . $limitStart . "," . $limitEnd;
    }

    $orderVal = url::postToStringAz09('order');
    $sortVal = url::postToStringAz09('sort');

    if ($orderVal != '') {
        $orderStr = 'order by ' . $orderVal . ' ' . $sortVal;
    } else {
        $orderStr = 'order by id desc';
    }

    $append_search = '';
    $bindings = array();
    $notifSearch = url::postToText('notifSearch');

    if ($notifSearch != '') {
        $name = strtolower($notifSearch);
        $bindings[] = "%" . $name . "%";
        $bindings[] = "%" . $name . "%";
        $bindings[] = "%" . $name . "%";
        $append_search = "(packageName LIKE ? or version LIKE ? or packageDesc LIKE ?) and";
    } else {
        $append_search = "";
    }

    $swdGridSql = "SELECT Packages.id, platform, packageName, type, sourceType, status, path, fileName, version, packageDesc,owner, lastModified, "
        . "global, distrubute, isConfigured ,PackagesConfiguration.executePath as executePath, PackagesConfiguration.32bitConfig as 32bitConfig, PackagesConfiguration.64bitConfig as 64bitConfig FROM " . $GLOBALS['PREFIX'] . "softinst.Packages "
        . "LEFT JOIN " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration ON (PackagesConfiguration.packageId=Packages.id) "
        . "where  $append_search "
        . "(global = 'yes' and owner in (select user_email from " . $GLOBALS['PREFIX'] . "core.Users)) $orderStr $limitStr";
    $mastersql = $db->prepare($swdGridSql);
    $mastersql->execute($bindings);
    $res = $mastersql->fetchAll(PDO::FETCH_ASSOC);


    $CountGridSql = "SELECT Packages.id, platform, packageName, type, sourceType, status, path, fileName, version, packageDesc,owner, lastModified, "
        . "global, distrubute, isConfigured ,PackagesConfiguration.executePath as executePath, PackagesConfiguration.32bitConfig as 32bitConfig, PackagesConfiguration.64bitConfig as 64bitConfig FROM " . $GLOBALS['PREFIX'] . "softinst.Packages "
        . "LEFT JOIN " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration ON (PackagesConfiguration.packageId=Packages.id) "
        . "where $append_search  "
        . "(global = 'yes' and owner in (select user_email from " . $GLOBALS['PREFIX'] . "core.Users))  $orderStr";

    $countSql = $db->prepare($CountGridSql);
    $countSql->execute($bindings);
    $totCount = safe_count($countSql->fetchAll(PDO::FETCH_ASSOC));



    if (safe_sizeof($res) == 0) {
        $dataArr['largeDataPaginationHtml'] =  '';
        $dataArr['html'] =   '';
        echo json_encode($dataArr);
    } else {
        $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage);
        $dataArr['html'] =    Format_SWDDataMysql($res, $type);
        echo json_encode($dataArr);
    }

    // $jsonData = array("draw" => $draw, "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecords, "data" => $recordList);
    // print_json_data($jsonData);
}

function Format_SWDDataMysql($data, $type)
{
    $recordList = array();
    foreach ($data as $key => $value) {
        if ($value['platform'] == 'windows') {
            if ($value['isConfigured'] == '1') {
                if ($data[$key]['distrubute'] == 1) {
                    $isconfig = "<span style='color:red;cursor: pointer; cursor: hand;'>No</span>";
                } else {
                    $isconfig = "<span style='color:green;'>Yes</span>";
                }
            } else if ($value['isConfigured'] == '2') {
                if ($data[$key]['distrubute'] == 1) {
                    $isconfig = "<span style='color:green;cursor: pointer; cursor: hand;'>Yes</span>";
                } else {
                    $isconfig = "<span style='color:red;cursor: pointer; cursor: hand;'>No</span>";
                }
            } else {
                if ($data[$key]['distrubute'] == 1) {
                    $isconfig = "<span style='color:red;cursor: pointer; cursor: hand;'>No</span>";
                } else {
                    $isconfig = "<span style='color:red;cursor: pointer; cursor: hand;'>No</span>";
                }
            }
        } else if ($value['platform'] == 'mac' || $value['platform'] == 'linux') {
            if ($value['isConfigured'] == '2') {
                if ($data[$key]['distrubute'] == 1) {
                    $isconfig = "<span style='color:green;cursor: pointer; cursor: hand;'>Yes</span>";
                } else {
                    $isconfig = "<span style='color:green;cursor: pointer; cursor: hand;'>Yes</span>";
                }
            } else {
                if ($data[$key]['distrubute'] == 1) {
                    $isconfig = "<span style='color:red;cursor: pointer; cursor: hand;'>No</span>";
                } else {
                    $isconfig = "<span style='color:red;cursor: pointer; cursor: hand;'>No</span>";
                }
            }
        } else {
            $isconfig = "<span style='color:green;cursor: pointer; cursor: hand;'>Yes</span>";
        }

        if ($value['platform'] == 'android' && $value['sourceType'] == 5) {
            $platform_val = $value['platform'] . "<span style='color:#258cd1;font-size:14px;'>&nbsp;(E)</i>";
        } else if ($value['platform'] == 'ios' && $value['sourceType'] == 5 && $value['type'] == 'file') {
            $platform_val = $value['platform'] . "<span style='color:#258cd1;font-size:14px;'>&nbsp;(E)</i>";
        } else {
            $platform_val = $value['platform'];
        }

        if ($value['global'] == 'yes') {
            $global = "<span>Yes</span>";
        } else {
            $global = "<span>No</span>";
        }

        if (isset($value['distrubute']) && is_numeric($value['distrubute']) && intval($value['distrubute']) == 1) {
            $isDistribute = "Yes";
        } else {
            $isDistribute = "No";
        }

        $isExecute = "No";

        if (isset($value['sourceType']) && is_numeric($value['sourceType'])) {
            if (intval($value['sourceType']) == 3) {
                if (isset($value['32bitConfig']) && !is_null($value['32bitConfig']) && !empty($value['32bitConfig']) && isset($value['64bitConfig']) && !is_null($value['64bitConfig']) && !empty($value['64bitConfig'])) {
                    $isExecute = "Yes";
                }
            } else {
                $isExecute = "Yes";
            }
        }

        if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
            $modifiedtime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $value['lastModified'], "m/d/Y g:i:s A");
        } else {
            $modifiedtime = date("m/d/Y g:i:s A", $value['lastModified']);
        }

        $modifiedtime = isset($value['lastModified']) ? $modifiedtime : '-';
        if ($type == 'add') {
            $recordList[$key][] = '<p class="ellipsis" title="' . $data[$key]['packageName'] . '">' . $data[$key]['packageName'] . '</p>';
            $recordList[$key][] = '<p class="ellipsis" title="' . $data[$key]['packageDesc'] . '">' . $data[$key]['packageDesc'] . '</p>';
            $recordList[$key][] = '<p class="ellipsis" title="' . $data[$key]['version'] . '">' . $data[$key]['version'] . '</p>';
            $recordList[$key][] = $platform_val;
            $recordList[$key][] = $modifiedtime;
            $recordList[$key][] = $global;
            $recordList[$key][] = $data[$key]['id'];
            // 'isDistributed' => $isDistribute,
            //  'isExecute' => $isExecute,
        } else {
            $recordList[$key][] = $platform_val;
            $recordList[$key][] = $data[$key]['packageName'];
            $recordList[$key][] = $data[$key]['packageDesc'];
            $recordList[$key][] = $data[$key]['version'];
            $recordList[$key][] = $isDistribute;
            $recordList[$key][] = $data[$key]['id'];
        }
    }
    return $recordList;
}



function cleanWhereInStringToArray($string)
{

    $array = array();

    if (!empty($string)) {
        if (strpos($string, ",")) {
            $array = array();
            $arrayN = explode_user_def(",", $string);
            foreach ($arrayN as $eachString) {
                $array[] = str_replace("'", "", $eachString);
            }
        } else {
            $array = arary(str_replace("'", "", $string));
        }
    }

    return $array;
}



function packagesSelectFn()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();
    $agentUniqId = $_SESSION['user']['adminEmail'];
    $recordList = [];
    $username = $_SESSION['user']['logged_username'];
    $searchtype = $_SESSION["searchType"];
    $searchValue = $_SESSION["searchValue"];
    $userId = $_SESSION["user"]["userid"];
    $auditSearch = url::requestToText('auditSearch');
    $siteName = UTIL_GetUserSiteList_PDO($db, $userId);
    $custList = $siteName['custNo'];
    $ordList = $siteName['ordNo'];
    $dataScope = GetSiteScope_PDO($db, $searchValue, $searchtype);
    $bindings = array();
    $custListArray = cleanWhereInStringToArray($custList);
    $customerBinding = str_repeat('?,', safe_count($custListArray) - 1) . '?';
    $orderListArray = cleanWhereInStringToArray($ordList);
    $orderListBinding = str_repeat('?,', safe_count($orderListArray) - 1) . '?';

    if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
        $sqlS = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType = 'Software Distribution' and AgentUniqId=? group by ProfileName";

        $bindings[] = 'Machine : ' . $auditSearch;
        $bindings[] = $agentUniqId;
    } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
        if ($auditSearch == "All") {
            $machines = GetMachinesSites_PDO($db, $dataScope);
            $machinesBinding = str_repeat('?,', safe_count($machines) - 1) . '?';

            $sqlS = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where (MachineTag IN (" . $machinesBinding . ") or SelectionType like '%Site%') and JobType = 'Software Distribution' and AgentUniqId=? group by ProfileName";

            $bindings = $machines;
            $bindings[] = $agentUniqId;
        } else {
            $sqlS = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType = 'Software Distribution' and AgentUniqId=?  group by ProfileName";

            $bindings[] = 'Site : ' . $auditSearch;
            $bindings[] = $agentUniqId;
        }
    } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
        if ($auditSearch == "All") {
            $dataScope = GetSiteScope_PDO($db, $searchValue, $searchtype);
            $data = GetGroupsMachines_PDO($db, $dataScope);
            $machines = $data;
            $machinesBinding = str_repeat('?,', safe_count($machines) - 1) . '?';

            $sqlS = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where (MachineTag IN (" . $machinesBinding . ") or SelectionType like '%Group%') and JobType = 'Software Distribution' and AgentUniqId=?  group by ProfileName";
            $bindings = $machines;
            $bindings[] = $agentUniqId;
        } else {
            $sqlS = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType = 'Software Distribution' and AgentUniqId=? group by ProfileName";
            $bindings[] = 'Group : ' . $auditSearch;
            $bindings[] = $agentUniqId;
        }
    }

    $pdo = $db->prepare($sqlS);
    $pdo->execute($bindings);
    $res = $pdo->fetchAll(PDO::FETCH_ASSOC);

    if (safe_count($res) > 0) {
        foreach ($res as $key => $value) {
            $profilename = '<p class="ellipsis" title="' . $res[$key]['ProfileName'] . '">' . $res[$key]['ProfileName'] . '</p>';
            $time = $res[$key]['JobCreatedTime'];
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $jobcreatedtime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $time, "m/d/Y g:i:s A");
            } else {
                $jobcreatedtime = date("m/d/Y g:i:s A", $time);
            }

            $agentname = $res[$key]['AgentName'];
            $profname = $res[$key]['ProfileName'];
            $id = $res[$key]['BID'];

            $recordList[] = array($profilename, $jobcreatedtime, $agentname, $profname, $id);
        }
    } else {
        $recordList = array();
    }
    print_json_data($recordList);
}



function softwareAuditDetailsFn()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $dbo = pdo_connect();

    $searchtype = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $agentUniqId = $_SESSION['user']['adminEmail'];

    $where = '';
    $recordList = array();
    $start = url::requestToAny('start');
    $length = url::requestToAny('length');
    $limit = "";

    if (isset($start) && is_numeric($start) && isset($length) && is_numeric($length)) {
        $limit = " limit $start , $length";
    }

    $searchVal = strip_tags(url::requestToAny('search')['value']);
    $draw = url::requestToAny('draw');
    $auditSearch = url::requestToText('audit');
    $orderval = url::requestToAny('order')[0]['column'];
    $orderValues = '';

    if ($orderval != '') {
        $orderColoumn = url::requestToAny('columns')[$orderval]['data'];
        $ordertype = url::requestToAny('order')[0]['dir'];
        if (isset($orderColoumn) && !empty($orderColoumn) && preg_match('/^[^\'\"]*$/', $orderColoumn) && isset($ordertype) && !empty($ordertype) && in_array(rtrim(ltrim(strtoupper($ordertype))), array('ASC', 'DESC'))) {
            $orderValues = " order by $orderColoumn $ordertype";
        }
    }

    $bid = url::requestToText('bid');
    $pack = url::requestToText('pack');
    $packageName = url::requestToText('PackageName');

    $_SESSION['bid'] = $bid;
    $_SESSION['pack'] = $pack;
    $_SESSION['packageName'] = $packageName;

    if (url::getToAny('searchVal') != '') {
        $name = url::getToText('searchVal');
        $append_search = "and MachineTag  LIKE ?";
    }

    $currentTimestamp = time();
    $yestDTStamp = strtotime("-90 days", $currentTimestamp);
    $where = '';
    $dataScope = GetSiteScope_PDO($dbo, $searchValue, $searchtype);
    $AuditSqlbindings = array();
    $schedulebindings = array();

    if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
        $AuditSqlbindings[] = 'Machine : ' . $auditSearch;
        $AuditSqlbindings[] = $agentUniqId;
        $AuditSqlbindings[] = $pack;
        if (url::getToAny('searchVal') != '') $AuditSqlbindings[] = "'%" . strtolower($name) . "%'";

        $auditSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType= 'Software Distribution' and JobStatus = '2' and AgentUniqId=? and ProfileName = ? $append_search $orderValues";
        $scheduleSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType= 'Software Distribution' and JobStatus in ('0','3') and AgentUniqId=? and ProfileName=? $orderValues $limit";
        $schedulebindings[] = 'Machine : ' . $auditSearch;
        $schedulebindings[] = $agentUniqId;
        $schedulebindings[] = $pack;
    } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
        if ($auditSearch == "All") {
            $machinesArray = GetMachinesSites_PDO($dbo, $dataScope);
            $machinesBindDelim = str_repeat('?,', safe_count($machinesArray) - 1) . '?';

            $auditSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag IN (" . $machinesBindDelim . ") AND JobType= 'Software Distribution' and JobStatus = '2' and AgentUniqId=? and ProfileName=? $append_search $orderValues";
            $AuditSqlbindings = $machinesArray;
            $AuditSqlbindings[] = $agentUniqId;
            $AuditSqlbindings[] = $pack;
            if (url::getToAny('searchVal') != '') $AuditSqlbindings[] = "'%" . strtolower($name) . "%'";

            $scheduleSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag IN (" . $machinesBindDelim . ") AND JobType= 'Software Distribution' and JobStatus in ('0','3') and AgentUniqId=? and ProfileName=? $orderValues $limit";
            $schedulebindings = $machinesArray;
            $schedulebindings[] = $agentUniqId;
            $schedulebindings[] = $pack;
        } else {
            $AuditSqlbindings[] = 'Site : ' . $auditSearch;
            $AuditSqlbindings[] = $agentUniqId;
            $AuditSqlbindings[] = $pack;
            if (url::getToAny('searchVal') != '') $AuditSqlbindings[] = "'%" . strtolower($name) . "%'";

            $auditSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType= 'Software Distribution' and JobStatus = '2' and AgentUniqId=? and ProfileName=? $append_search $orderValues";
            $scheduleSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType= 'Software Distribution' and JobStatus in ('0','3') and AgentUniqId=? and ProfileName=? $orderValues $limit";
            $schedulebindings[] = 'Site : ' . $auditSearch;
            $schedulebindings[] = $agentUniqId;
            $schedulebindings[] = $pack;
        }
    } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
        if ($auditSearch == "All") {
            $dataScope = GetSiteScope_PDO($dbo, $searchValue, $searchtype);
            $machinesArray = GetGroupsMachines_PDO($dbo, $dataScope);
            $machinesBindDelim = str_repeat('?,', safe_count($machinesArray) - 1) . '?';

            $auditSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag IN (" . $machinesBindDelim . ") and JobType= 'Software Distribution' and JobStatus = '2' and AgentUniqId=? and ProfileName=? $append_search $orderValues";
            $AuditSqlbindings = $machinesArray;
            $AuditSqlbindings[] = $agentUniqId;
            $AuditSqlbindings[] = $pack;
            if (url::getToAny('searchVal') != '') $AuditSqlbindings[] = "'%" . strtolower($name) . "%'";

            $scheduleSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag IN (" . $machinesBindDelim . ") and JobType= 'Software Distribution' and JobStatus in ('0','3') and AgentUniqId=? and ProfileName=? $orderValues $limit";
            $schedulebindings = $machinesArray;
            $schedulebindings[] = $agentUniqId;
            $schedulebindings[] = $pack;
        } else {
            $orderValues = 'group by MachineTag';
            $auditSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType= 'Software Distribution' and JobStatus = '2' and AgentUniqId=? and ProfileName=? and BID = $bid $append_search $orderValues";
            $AuditSqlbindings[] = 'Group : ' . $auditSearch;
            $AuditSqlbindings[] = $agentUniqId;
            $AuditSqlbindings[] = $pack;
            if (url::getToAny('searchVal') != '') $AuditSqlbindings[] = "'%" . strtolower($name) . "%'";

            $scheduleSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType= 'Software Distribution' and JobStatus in ('0','3') and AgentUniqId=? and ProfileName=? and BID = $bid $orderValues $limit";
            $schedulebindings[] = 'Group : ' . $auditSearch;
            $schedulebindings[] = $agentUniqId;
            $schedulebindings[] = $pack;
        }
    }


    $auditCntSql = $auditSql;
    $auditSql .= $limit;
    $pdo = $dbo->prepare($auditSql);
    $pdo->execute($AuditSqlbindings);
    $auditRes = $pdo->fetchAll(PDO::FETCH_ASSOC);

    $pdo = $dbo->prepare($auditCntSql);
    $pdo->execute($AuditSqlbindings);
    $auditCnt = $pdo->fetchAll(PDO::FETCH_ASSOC);


    $scheduleCntSql = $scheduleSql;
    $pdo = $dbo->prepare($scheduleSql);
    $pdo->execute($schedulebindings);
    $scheduleRes = $pdo->fetchAll(PDO::FETCH_ASSOC);
    $pdo = $dbo->prepare($scheduleCntSql);
    $pdo->execute($schedulebindings);
    $scheduleCnt = $pdo->fetchAll(PDO::FETCH_ASSOC);

    $total = safe_count($auditCnt) + safe_count($scheduleCnt);

    if ($total == 0) {
        $total = 1;
    }

    $totalRecords = safe_count($auditCnt) + safe_count($scheduleCnt);

    $i = 0;

    foreach ($auditRes as $key => $row) {


        $mystring = $row['ProfileSequence'];

        $findme = '289-';
        $pos = strpos($mystring, $findme);
        if ($pos === false) {
            $findme = '_256_';
            $pos = strpos($mystring, $findme);
            if ($pos === false) {
                $find287 = '_287_';
                $pos287 = strpos($mystring, $find287);
                if ($pos287 === false) {
                    $solutionPushed = 'Silent Uninstall';
                } else {
                    $arr1 = explode_user_def('_', $mystring);
                    $val = '';
                    if (isset($arr1[4])) {
                        $arr2 = explode_user_def("\n", trim($arr1[4]));
                        foreach ($arr2 as $value) {
                            $arr3 = explode_user_def(',', $value);
                            if ($val == '') {
                                $val .= trim($arr3[1]);
                            } else {
                                $val .= ',' . trim($arr3[1]);
                            }
                        }
                        $solutionPushed = $val;
                    } else {
                        $solutionPushed = str_replace("\n", ' ', trim($mystring));
                    }
                }
            } else {
                $solutionPushed = 'Tweaks';
            }
        } else {
            $arr1 = explode_user_def('-', $mystring, 2);
            if (isset($arr1[1])) {
                $disSql = "select profile from event.profile where varValue=? limit 1";
                $pdo = $dbo->prepare($disSql);
                $pdo->execute([urldecode($arr1[1])]);
                $finType = $pdo->fetch(PDO::FETCH_ASSOC);

                if (safe_count($finType) > 0) {
                    $solutionPushed = str_replace("\n", ' ', $finType['profile']);
                } else {
                    $solutionPushed = str_replace("\n", ' ', $arr1[1]);
                }
            } else {
                $solutionPushed = str_replace("\n", ' ', $mystring);
            }
        }

        $auditId = $row['AID'];
        $eventListN = $row['DartExecutionProof'];
        $status = $row['JobStatus'];
        if ($status == '2' || $status == 2) {
            $strN = 'style="color:#5cb85c" onClick="AuditDetailStatusFn(1,' . $auditId . ',\'' . $eventListN . '\')"';
            $proof = "<a href='javascript:;' " . $strN . ">Completed</a>";
        } else if ($status == '0' || $status == 0) {
            $strN = 'style="color:#f0ad4e" onClick="AuditDetailStatusFn(0,' . $auditId . ',\'' . $eventListN . '\')"';
            $proof = "<a href='javascript:;' " . $strN . ">Pending</a>";
        } else if ($status == '3' || $status == 3) {
            $strN = 'style="color:#FF0000;" onClick="AuditDetailStatusFn(1,' . $auditId . ',\'' . $eventListN . '\')"';
            $proof = "<a href='javascript:;' " . $strN . ">Failed</a>";
        } else {
            $strN = 'style="color:#FF0000"';
            $proof = "<a href='javascript:;' " . $strN . ">Failed</a>";
        }

        if (strpos($row['SelectionType'], 'Site ') !== false) {
            $siteName = explode_user_def('__', $row['SelectionType']);
            $selectT = $siteName[0];
        } else {
            $selectT = $row['SelectionType'];
        }

        $recordList[] = array('SelectionType' => '<p class="ellipsis" title="' . $selectT . '">' . $selectT . '</p>', 'MachineTag' => '<p class="ellipsis" title="' . $row['MachineTag'] . '">' . $row['MachineTag'] . '</p>', 'Status' => $proof);
    }

    foreach ($scheduleRes as $key => $row) {


        $mystring = $row['varValues'];

        $findme = '289-';
        $pos = strpos($mystring, $findme);
        if ($pos === false) {
            $findme = '_256_';
            $pos = strpos($mystring, $findme);
            if ($pos === false) {
                $find287 = '_287_';
                $pos287 = strpos($mystring, $find287);
                if ($pos287 === false) {
                    $solutionPushed = 'Silent Uninstall';
                } else {
                    $arr1 = explode_user_def('_', $mystring);
                    $val = '';
                    if (isset($arr1[4])) {
                        $arr2 = explode_user_def("\n", trim($arr1[4]));
                        foreach ($arr2 as $value) {
                            $arr3 = explode_user_def(',', $value);
                            if ($val == '') {
                                $val .= trim($arr3[1]);
                            } else {
                                $val .= ',' . trim($arr3[1]);
                            }
                        }
                        $solutionPushed = $val;
                    } else {
                        $solutionPushed = str_replace("\n", ' ', trim($mystring));
                    }
                }
            } else {
                $solutionPushed = 'Tweaks';
            }
        } else {
            $arr1 = explode_user_def('-', $mystring, 2);
            if (isset($arr1[1])) {
                $disSql = "select profile from event.profile where varValue=? limit 1";
                $pdo = $dbo->prepare($disSql);
                $pdo->execute([urldecode($arr1[1])]);
                $finType = $pdo->fetch(PDO::FETCH_ASSOC);

                if (safe_count($finType) > 0) {
                    $solutionPushed = str_replace("\n", ' ', $finType['profile']);
                } else {
                    $solutionPushed = str_replace("\n", ' ', $arr1[1]);
                }
            } else {
                $solutionPushed = str_replace("\n", ' ', $mystring);
            }
        }

        $auditId = $row['AID'];
        $eventListN = $row['DartExecutionProof'];
        $status = $row['JobStatus'];

        if ($status == 0 || $status == '0') {
            $strN = 'style="color:#f0ad4e"';
            $proof = "<a href='javascript:;' " . $strN . ">Pending</a>";
        } else if ($status == 3 || $status == '3') {
            $strN = 'style="color:#FF0000;" onClick="AuditDetailStatusFn(1,' . $auditId . ',\'' . $eventListN . '\')"';
            $proof = "<a href='javascript:;' " . $strN . ">Failed</a>";
        }

        if (strpos($row['SelectionType'], 'Site ') !== false) {
            $siteName = explode_user_def('__', $row['SelectionType']);
            $selectT = $siteName[0];
        } else {
            $selectT = $row['SelectionType'];
        }

        $recordList[] = array('SelectionType' => '<p class="ellipsis" title="' . $selectT . '">' . $selectT . '</p>', 'MachineTag' => '<p class="ellipsis" title="' . $row['MachineTag'] . '">' . $row['MachineTag'] . '</p>', 'Status' => $proof);
    }
    $jsonData = array("draw" => $draw, "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecords, "data" => $recordList);
    print_json_data($jsonData);
}



function allexportPatchFn()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $dbo = db_connect();

    $logged_user = $_SESSION['user']['logged_username'];

    $searchtype = $_SESSION["searchType"];
    $searchValue = $_SESSION["searchValue"];

    header('Content-Type: application/ms-excel');
    header("Content-Disposition: attachment; filename=" . $logged_user . "_PackageAuditDetail.xls");
    header("Content-Type: application/vnd.ms-excel;");
    header("Pragma: no-cache");
    header("Expires: 0");
    $out = fopen("php://output", "w");

    $selectionType = $_SESSION['searchValue'];
    $agentUniqId = $_SESSION['user']['adminEmail'];
    $searchVal = strip_tags(url::requestToAny('search')['value']);
    $auditSearch = url::requestToText('auditSearch');

    if ($searchVal != '') {
        $name = safe_addslashes(strip_tags($searchVal));
        $append_search = "and ProfileName LIKE '%" . $name . "%'";
    } else {
        $append_search = "";
    }


    $final = "<table border=1 style='font:14px Calibri'>
    <tr>
        <th>Package Name</th>
        <th>Triggered Time</th>
        <th>Agent</th>
    </tr>";

    $dataScope = GetSiteScope_PDO($dbo, $searchValue, $searchtype);
    $AuditSqlbindings = array();

    if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
        $sqlS = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,count(ProfileName) as Count,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType = 'Software Distribution' and AgentUniqId=? group by ProfileName";
        $AuditSqlbindings[] = 'Machine : ' . $auditSearch;
        $AuditSqlbindings[] = $agentUniqId;
    } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
        if ($auditSearch == "All") {
            $machinesArray = GetMachinesSites_PDO($dbo, $dataScope);
            $machinesBindDelim = str_repeat('?,', safe_count($machinesArray) - 1) . '?';
            $AuditSqlbindings = $machinesArray;
            $AuditSqlbindings[] = $agentUniqId;
            $sqlS = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,count(ProfileName) as Count,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag IN (" . $machinesBindDelim . ") and SelectionType like '%Site%' and JobType = 'Software Distribution' and AgentUniqId=? group by ProfileName";
        } else {
            $sqlS = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,count(ProfileName) as Count,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType = 'Software Distribution' and AgentUniqId=? group by ProfileName";
            $AuditSqlbindings[] = 'Site : ' . $auditSearch;
            $AuditSqlbindings[] = $agentUniqId;
        }
    } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
        if ($auditSearch == "All") {
            $dataScope = GetSiteScope_PDO($dbo, $searchValue, $searchtype);
            $machinesArray = GetGroupsMachines_PDO($dbo, $dataScope);
            $machinesBindDelim = str_repeat('?,', safe_count($machinesArray) - 1) . '?';

            $sqlS = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,count(ProfileName) as Count,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag IN (" . $machinesBindDelim . ") and SelectionType like '%Group%' and JobType = 'Software Distribution' and AgentUniqId=? group by ProfileName";
            $AuditSqlbindings = $machinesArray;
            $AuditSqlbindings[] = $agentUniqId;
        } else {
            $sqlS = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,count(ProfileName) as Count,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType = 'Software Distribution' and AgentUniqId=? group by ProfileName";
            $AuditSqlbindings[] = 'Group : ' . $auditSearch;
            $AuditSqlbindings[] = $agentUniqId;
        }
    }

    $pdo = $dbo->prepare($sqlS);
    $pdo->execute($AuditSqlbindings);
    $patchRes = $pdo->fetchAll(PDO::FETCH_ASSOC);

    foreach ($patchRes as $key => $value) {

        $time = date("m/d/Y h:i A", $value['JobCreatedTime']);

        $final .= "<tr>";
        $final .= '<td>' . $value['ProfileName'] . '</td>';
        $final .= '<td>' . $time . '</td>';
        $final .= '<td>' . $value['AgentName'] . '</td>';
        $final .= "</tr>";
    }

    if ($value['BID'] != '' && $value['ProfileName'] != '' && $value['Count'] != '') {
        print_data($final);
    } else {
        $msg = "No record(s) available";
        print_data($msg);
    }

    fclose($out);
}


//$FinalArray = array();
function exportauditFn()
{

    // $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    // if (!$priviledge) {
    //     echo 'Permission denied';
    //     exit();
    // }
    $bid = url::requestToText('bid');
    $packname = url::requestToText('packageName');
    $auditSearch = url::requestToText('auditSearch');
    $dbo = pdo_connect();
    $searchtype = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $agentUniqId = $_SESSION['user']['adminEmail'];

    $dataScope = GetSiteScope_PDO($dbo, $searchValue, $searchtype);
    $AuditSqlbindings = array();
    $schedulebindings = array();
    if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
        $auditSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType= 'Software Distribution' and JobStatus = '2' and ProfileName=?";
        $AuditSqlbindings[] = 'Machine : ' . $auditSearch;
        $AuditSqlbindings[] = "SWD : " . $packname;

        $scheduleSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType= 'Software Distribution' and JobStatus in ('0','3') and ProfileName=?";
        $schedulebindings[] = 'Machine : ' . $auditSearch;
        $schedulebindings[] = "SWD : " . $packname;
    } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
        $auditSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType= 'Software Distribution' and JobStatus = '2'";
        $AuditSqlbindings[] = 'Site : ' . $auditSearch;
        //$AuditSqlbindings[] = "SWD : " . $packname;

        $scheduleSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType= 'Software Distribution' and JobStatus in ('0','3')";
        $schedulebindings[] = 'Site : ' . $auditSearch;
        //$schedulebindings[] = "SWD : " . $packname;
    } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
        $auditSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType= 'Software Distribution' and JobStatus = '2' and ProfileName=?";
        $AuditSqlbindings[] = 'Group : ' . $auditSearch;
        $AuditSqlbindings[] = "SWD : " . $packname;

        $scheduleSql = "select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType=? and JobType= 'Software Distribution' and JobStatus in ('0','3') and ProfileName=?";
        $schedulebindings[] = 'Group : ' . $auditSearch;
        $schedulebindings[] = "SWD : " . $packname;
    }
    /* header('Content-Type: application/ms-excel');
    header("Content-Disposition: attachment; filename=" . $packname . "_PackageDetails.xls");
    header("Content-Type: application/vnd.ms-excel;"); */
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="_PackageDetails.csv"');

    header("Pragma: no-cache");
    header("Expires: 0");
    $out = fopen("php://output", "w");
    $final = "<table border=1 style='font:14px Calibri'>
    <tr>
        <th>Scope</th>
        <th>Machine</th>
        <th>Status</th>
    </tr>";
    $finallist[0] = array('Scope','Machine','Status');

    $pdo = $dbo->prepare($auditSql);
    $pdo->execute($AuditSqlbindings);
    $auditRes = $pdo->fetchAll(PDO::FETCH_ASSOC);

    foreach ($auditRes as $key => $value) {
        $status = $value['JobStatus'];
        switch ($status) {
            case "2":
                //$proof = '<a style="color: #008000;">Completed</a>';
                $proof = 'Completed';
                break;
            case "3":
                //$proof = '<a style="color: #FF0000;">Failed</a>';
                $proof = 'Failed';
                break;
            case "0":
                //$proof = '<a style="color: #FFA500;">Pending</a>';
                $proof = 'Pending';
                break;
            default:
                //$proof = '<a style="color: #FFA500;">Failed</a>';
                $proof = 'Failed';
                break;
        }
        if (strpos($value['SelectionType'], 'Site ') !== false) {
            $selectT = UTIL_GetTrimmedGroupName($value['SelectionType']);
        } else {
            $selectT = UTIL_GetTrimmedGroupName($value['SelectionType']);
        }
        
        $final .= "<tr>";
        $final .= '<td>' . $selectT . '</td>';
        $final .= '<td>' . $value['MachineTag'] . '</td>';
        $final .= '<td>' . $proof . '</td>';
        $final .= "</tr>";

        //logs::log("final11111------------------",$final);
        $finallist[] = array($selectT, $value['MachineTag'], $proof);
        logs::log("finalArray1111------------------",$finallist);

    }
    $pdo = $dbo->prepare($scheduleSql);
    $pdo->execute($schedulebindings);
    $scheduleRes = $pdo->fetchAll(PDO::FETCH_ASSOC);
    foreach ($scheduleRes as $key => $value) {
        $status = $value['JobStatus'];
        switch ($status) {
            case "2":
                //$proof = '<a style="color: #008000;">Completed</a>';
                $proof = 'Completed';
                break;
            case "3":
                //$proof = '<a style="color: #FF0000;">Failed</a>';
                $proof = 'Failed';
                break;
            case "0":
                //$proof = '<a style="color: #FFA500;">Pending</a>';
                $proof = 'Pending';
                break;
            default:
                //$proof = '<a style="color: #FFA500;">Failed</a>';
                $proof = 'Failed';
                break;
        }
        $final .= "<tr>";
        $final .= '<td>' . UTIL_GetTrimmedGroupName($value['SelectionType']) . '</td>';
        $final .= '<td>' . $value['MachineTag'] . '</td>';
        $final .= '<td>' . $proof . '</td>';
        $final .= "</tr>";

        //logs::log("final2222------------------",$final);
        $finallist[] = array(UTIL_GetTrimmedGroupName($value['SelectionType']), $value['MachineTag'], $proof);
        logs::log("finalArray2222------------------",$finallist);

    }
    logs::log("final========================",$finallist);
    /* print_data($final);
    fclose($out); */

    foreach ($finallist as $line) {
        fputcsv($out, $line, ',');
    }
    fclose($out);

}

function AuditDetailStatusFn()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();

    $status = url::getToText('stat');
    $tidN = url::getToText('eid');
    $eventList = url::getToText('eventList');

    $sql = 'select * from ' . $GLOBALS['PREFIX'] . 'communication.Audit where AID=? order by AID desc limit 1';
    $pdo = $db->prepare($sql);
    $pdo->execute([$tidN]);
    $result = $pdo->fetch(PDO::FETCH_ASSOC);

    $bindDelims = str_repeat('?,', safe_count($eventList) - 1) . '?';
    $sqlevent = 'select * from  ' . $GLOBALS['PREFIX'] . 'event.`Events` where  ' . $GLOBALS['PREFIX'] . 'event.`Events`.idx IN (' . $bindDelims . ') limit 1';
    $pdo = $db->prepare($sqlevent);
    $pdo->execute($eventList);
    $resultevent = $pdo->fetch(PDO::FETCH_ASSOC);

    $servertime = date("m/d/Y h:i A", $result['JobCreatedTime']);
    $nodetrigger = date("m/d/Y h:i A", $result['JobCreatedTime']);
    $clienttime = date("m/d/Y h:i A", $result['ClientExecutedTime']);

    if (safe_count($resultevent) > 0) {
        $eventserver = date("m/d/Y h:i A", $resultevent['servertime']);
        $eventclient = date("m/d/Y h:i A", $resultevent['entered']);
    }

    $agentName = UTIL_GetTrimCompanyName($result['AgentName']);

    $recordList = array(
        "username" => $agentName,
        "servertime" => $servertime,
        "nodetrigger" => $nodetrigger,
        "clienttime" => $clienttime,
        "eventuser" => $resultevent['username'],
        "eventserver" => $eventserver,
        "eventcustomer" => UTIL_GetTrimmedGroupName($resultevent['customer']),
        "eventuuid" => $resultevent['uuid'],
        "eventversion" => $resultevent['clientversion'],
        "eventdescription" => $resultevent['description'],
        "eventsize" => $resultevent['size'],
        "eventid" => $resultevent['id'],
        "eventstring2" => $resultevent['string2'],
        "eventclient" => $eventclient,
        "eventscrip" => $resultevent['scrip'],
        "eventmachine" => $resultevent['machine'],
        "eventusername" => $resultevent['username'],
        "eventpriority" => $resultevent['priority'],
        "eventtype" => $resultevent['type'],
        "eventversion" => $resultevent['version'],
        "eventid2" => $resultevent['id'],
        "eventstring1" => $resultevent['string1'],
        "eventpath" => $resultevent['path'],
        "eventtext1" => strip_tags($resultevent["text1"]),
        "eventtext2" => strip_tags($resultevent["text2"]),
        "eventtext3" => strip_tags($resultevent["text3"]),
        "eventtext4" => strip_tags($resultevent["text4"]),
    );

    print_json_data($recordList);
}

function formatString($eventList)
{
    $arr = explode_user_def(',', $eventList);
    for ($i = 0; $i < safe_count($arr) - 1; $i++) {
        $arrNew[] = $arr[$i];
    }
    return implode(',', $arrNew);
}



function addPackageFn()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $now = date("U");
    $date = new DateTime();
    $uts = $date->getTimestamp();
    $_SESSION['uts'] = $uts;

    $db = pdo_connect();


    $user = url::requestToText('email');
    $getcdnConfig = "select ftp,ftpUrl,cdn,cdnUrl,cdnAccessKey,cdnSecretKey,cdnBucketName,cdnRegion from " . $GLOBALS['PREFIX'] . "softinst.PackagesFtp where user=? limit 1";
    $pdo = $db->prepare($getcdnConfig);
    $pdo->execute([$user]);
    $getcdnConfig = $pdo->fetch(PDO::FETCH_ASSOC);


    $cdn = $getcdnConfig['cdn'];
    $AWSACCESS = $getcdnConfig['cdnAccessKey'];
    $AWSSECRET = $getcdnConfig['cdnSecretKey'];
    $AWSBUCKET = $getcdnConfig['cdnBucketName'];
    $AWSREGION = $getcdnConfig['cdnRegion'];
    $AWSURL = $getcdnConfig['cdnUrl'];

    $ftp = $getcdnConfig['ftp'];
    $ftpUrl = $getcdnConfig['ftpUrl'];

    $data = array(
        "ftp" => "$ftp",
        "ftpUrl" => "$ftpUrl",
        "cdn" => "$cdn",
        "AWSURL" => "$AWSURL",
        "AWSACCESS" => "$AWSACCESS",
        "AWSSECRET" => "$AWSSECRET",
        "AWSBUCKET" => "$AWSBUCKET",
        "AWSREGION" => "$AWSREGION",
    );
    $jsonData = array("data" => $data);
    print_json_data($jsonData);
}



function editPackageFn()
{
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $now = date("U");
    $date = new DateTime();
    $uts = $date->getTimestamp();
    $_SESSION['uts'] = $uts;

    $db = pdo_connect();


    $user = $_SESSION['user']['adminEmail'];
    $getcdnConfig = "select ftp,ftpUrl,cdn,cdnUrl,cdnAccessKey,cdnSecretKey,cdnBucketName,cdnRegion from " . $GLOBALS['PREFIX'] . "softinst.PackagesFtp where user=? limit 1";
    $pdo = $db->prepare($getcdnConfig);
    $pdo->execute([$user]);
    $getcdnConfig = $pdo->fetch(PDO::FETCH_ASSOC);

    $cdn = $getcdnConfig['cdn'];
    $AWSACCESS = $getcdnConfig['cdnAccessKey'];
    $AWSSECRET = $getcdnConfig['cdnSecretKey'];
    $AWSBUCKET = $getcdnConfig['cdnBucketName'];
    $AWSREGION = $getcdnConfig['cdnRegion'];
    $AWSURL = $getcdnConfig['cdnUrl'];

    $ftp = $getcdnConfig['ftp'];
    $ftpUrl = $getcdnConfig['ftpUrl'];

    $edit = url::requestToAny('sel');
    $editSql = "SELECT id,protocol, platform, type, sourceType, packageName, path, config3264type, fileName,fileName2, packageDesc, androidIcon, androidSite, version, "
        . "androiddate,androidnotify,androidUninstall, status, access, userName, password, domain, global, distrubute, distributionPath, "
        . "distributionTime, distributionVpath, preinstall, oninstall, isConfigured FROM " . $GLOBALS['PREFIX'] . "softinst.Packages WHERE id=?";
    $pdo = $db->prepare($editSql);
    $pdo->execute([$edit]);
    $editSqlRes = $pdo->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT dcheckPreInstall, dValidationFilePath, dsoftwareName, dsoftwareVersion, dknowledgeBase, dservicePack, dRootKey, "
        . "dSubKey, peerdistribution, posKeywords, packageExpiry, policyEnforce, androidPreCheck, androidPostCheck, downloadType, "
        . "maxTime, preInstallMsg, postDownloadMsg, finalInstallMsg, frequencySettings, installType, andPreCheckCond, andSourcePath, "
        . "andDestinationPath, distributionType, messageText, andPostCheckCond, andDestPath, andPreCheckPath, pExecPreCheckVal, pRegName, pType, pValue "
        . "FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration where packageId=?";
    $pdo = $db->prepare($sql);
    $pdo->execute([$edit]);
    $sqlres = $pdo->fetch(PDO::FETCH_ASSOC);

    $same3264config = $editSqlRes['config3264type'];
    if ($same3264config == 'different') {
        $same3264config = 'no';
    } else {
        $same3264config = 'yes';
    }
    $protocol = $editSqlRes['protocol'];
    $filename2 = $editSqlRes['fileName2'];
    $id = $editSqlRes['id'];
    $appId = $editSqlRes['appId'];
    $platform = $editSqlRes['platform'];
    $type = $editSqlRes['type'];
    $sourceType = $editSqlRes['sourceType'];
    $packageName = $editSqlRes['packageName'];
    $path = $editSqlRes['path'];
    $fileName = $editSqlRes['fileName'];
    $packageDesc = $editSqlRes['packageDesc'];
    $androidIcon = $editSqlRes['androidIcon'];
    $androidSite = $editSqlRes['androidSite'];
    $version = $editSqlRes['version'];
    $androiddate = $editSqlRes['androiddate'];
    $androidnotify = $editSqlRes['androidnotify'];
    $androidUninstall = $editSqlRes['androidUninstall'];
    $status = $editSqlRes['status'];
    $access = $editSqlRes['access'];
    $userName = $editSqlRes['userName'];
    $password = $editSqlRes['password'];
    $domain = $editSqlRes['domain'];
    $global = $editSqlRes['global'];
    $distrubute = $editSqlRes['distrubute'];
    $distributionPath = $editSqlRes['distributionPath'];
    $distributionTime = $editSqlRes['distributionTime'];
    $distributionVpath = $editSqlRes['distributionVpath'];

    $dIsConfigured = $editSqlRes['isConfigured'];

    if ($editSqlRes['type'] == 'file') {
        if ($editSqlRes['sourceType'] == '5') {
            if ($editSqlRes['androidIcon'] != '') {
                $tempArr = explode_user_def("/", $editSqlRes['androidIcon']);
                $iconUrl = end($tempArr);
                if ($iconUrl == '') {
                    $iconUrl = 'Icon not Uploaded';
                }
            }
            $fileandicon = $editSqlRes['fileName'];
            $fileiconUrl = $iconUrl;
        } else {
            $fileonly = $editSqlRes['fileName'];
            $fileonly2 = isset($editSqlRes['fileName2']) ? $editSqlRes['fileName2'] : '';
        }
    } else {
        $fileonly = $editSqlRes['fileName'];
        $fileonly2 = isset($editSqlRes['fileName2']) ? $editSqlRes['fileName2'] : '';
    }

    foreach ($sqlres as $key => $value) {
        $dcheckPreInstall = $sqlres['dcheckPreInstall'];
        $strdcheckPreInstall = (string)$dcheckPreInstall;
        $trimdcheckPreInstall = trim($dcheckPreInstall);
        $dValidationFilePath = $sqlres['dValidationFilePath'];
        $dsoftwareName = $sqlres['dsoftwareName'];
        $dsoftwareVersion1 = $sqlres['dsoftwareVersion'];
        $dsoftwareVersion = str_replace("&&", ",", $dsoftwareVersion1);
        $dknowledgeBase = $sqlres['dknowledgeBase'];
        $dservicePack = $sqlres['dservicePack'];
        $dRootKey = $sqlres['dRootKey'];
        $dSubKey = $sqlres['dSubKey'];
        $posKey = $sqlres['posKeywords'];
        $packageExpiry = $sqlres['packageExpiry'];
        $policyEnforce = $sqlres['policyEnforce'];
        $androidPreCheck = $sqlres['androidPreCheck'];
        $androidPostCheck = $sqlres['androidPostCheck'];
        $downloadType = $sqlres['downloadType'];
        $maxTime = $sqlres['maxTime'];
        $installType = $sqlres['installType'];
        $preInstallMsg = $sqlres['preInstallMsg'];
        $postDownloadMsgVal = $sqlres['postDownloadMsg'];
        $finalInstallMsg = $sqlres['finalInstallMsg'];
        $frequencySettings = $sqlres['frequencySettings'];
        $distType = $sqlres['distributionType'];
        $sourcePath = $sqlres['andSourcePath'];
        $destinationPath = $sqlres['andDestinationPath'];
        $title = $sqlres['messageText'];
        $andPreCheckCond = $sqlres['andPreCheckCond'];
        $splitpackvers = explode_user_def("#", $andPreCheckCond);
        $andPackName = $splitpackvers[0];
        $andVersionCode = $splitpackvers[1];
        $apkPath = $splitpackvers[0];
        $apkSize = $splitpackvers[1];
        $andPostCheckCond = $sqlres['andPostCheckCond'];
        $splitpackversP = explode_user_def("#", $andPostCheckCond);
        $andPPackName = $splitpackversP[0];
        $andPVersionCode = $splitpackversP[1];
        $andDestPath = $sqlres['andDestPath'];

        $preInstallMsgExp = explode_user_def(",", $preInstallMsg);
        $preDownloadMsg = $preInstallMsgExp[1];
        $preDownloadPosMsg = $preInstallMsgExp[2];
        $preDownloadNegMsg = $preInstallMsgExp[3];

        $postDownloadMsgExp = explode_user_def(",", $postDownloadMsgVal);
        $postDownloadMsg = $postDownloadMsgExp[1];
        $postDownloadPosMsg = $postDownloadMsgExp[2];
        $postDownloadNegMsg = $postDownloadMsgExp[3];

        $finalInstallMsgExp = explode_user_def(",", $finalInstallMsg);
        $installMsg = $finalInstallMsgExp[1];
        $installAction = $finalInstallMsgExp[2];
        $installFieldVal = $finalInstallMsgExp[3];

        $frequencySettingsExp = explode_user_def(",", $frequencySettings);
        $frequencySet = $frequencySettingsExp[0];
        $intervalSet = $frequencySettingsExp[1];
        $policyEnforceAction = $frequencySettingsExp[2];
        $enfMessage = $frequencySettingsExp[3];

        $pRegName = $sqlres['pRegName'];
        $pType = $sqlres['pType'];
        $pValue = $sqlres['pValue'];
        $pExecPreCheckVal = $sqlres['pExecPreCheckVal'];
        $peerdist = $sqlres['peerdistribution'];
        $andPreCheckPath = $sqlres['andPreCheckPath'];
    }

    $temp = explode_user_def(',', $androidSite);
    if (safe_count($temp) > 1) {
        foreach ($temp as $val) {
            $site .= UTIL_GetTrimmedGroupName($val) . ',';
        }
        $androidSite = rtrim($site, ',');
    } else {
        $androidSite = UTIL_GetTrimmedGroupName($androidSite);
    }

    $recordList = array(
        'id' => $id,
        'appId' => $appId,
        'cdn' => $cdn,
        "AWSURL" => $AWSURL,
        'ftp' => $ftp,
        'ftpUrl' => $ftpUrl,
        'protocol' => $protocol,
        'platform' => $platform,
        'fileandicon' => $fileandicon,
        'fileiconUrl' => $fileiconUrl,
        'fileonly' => $fileonly,
        'type' => $type,
        'sourceType' => $sourceType,
        'packageName' => $packageName,
        'path' => $path,
        'fileName' => $fileName,
        'fileName2' => $fileonly2,
        'packageDesc' => $packageDesc,
        'androidIcon' => $androidIcon,
        'androidSite' => UTIL_GetTrimmedGroupName($androidSite),
        'version' => $version,
        'androiddate' => $androiddate,
        'androidnotify' => $androidnotify,
        'androidUninstall' => $androidUninstall,
        'status' => $status,
        'access' => $access,
        'userName' => $userName,
        'password' => $password,
        'domain' => $domain,
        'global' => $global,
        'distrubute' => $distrubute,
        'distributionPath' => $distributionPath,
        'distributionTime' => $distributionTime,
        'distributionVpath' => $distributionVpath,
        'dcheckPreInstall' => $dcheckPreInstall,
        'strdcheckPreInstall' => $strdcheckPreInstall,
        'trimdcheckPreInstall' => $trimdcheckPreInstall,
        'dValidationFilePath' => $dValidationFilePath,
        'dsoftwareName' => $dsoftwareName,
        'dsoftwareVersion' => $dsoftwareVersion,
        'dknowledgeBase' => $dknowledgeBase,
        'dservicePack' => $dservicePack,
        'dRootKey' => $dRootKey,
        'dSubKey' => $dSubKey,
        "AWSACCESS" => $AWSACCESS,
        "AWSSECRET" => $AWSSECRET,
        "AWSBUCKET" => $AWSBUCKET,
        'posKey' => $posKey,
        'distType' => $distType,
        'packExpiry' => $packageExpiry,
        'policyEnforce' => $policyEnforce,
        'downloadPath' => $andDestPath,
        'andPreCheck' => $androidPreCheck,
        'andPackName' => $andPackName,
        'andVersionCode' => $andVersionCode,
        'apkPath' => $apkPath,
        'apkSize' => $apkSize,
        'andPostCheck' => $androidPostCheck,
        'andPPackName' => $andPPackName,
        'andPVersionCode' => $andPVersionCode,
        'sourcePath' => $sourcePath,
        'destinationPath' => $destinationPath,
        'downloadType' => $downloadType,
        'maxTime' => $maxTime,
        'installType' => $installType,
        'title' => $title,
        'preDownloadMsg' => $preDownloadMsg,
        'preDownloadPosMsg' => $preDownloadPosMsg,
        'preDownloadNegMsg' => $preDownloadNegMsg,
        'postDownloadMsg' => $postDownloadMsg,
        'postDownloadPosMsg' => $postDownloadPosMsg,
        'postDownloadNegMsg' => $postDownloadNegMsg,
        'installMsg' => $installMsg,
        'installAction' => $installAction,
        'installFinishMsg' => $installFieldVal,
        'installPopupMsg' => $installFieldVal,
        'frequencySet' => $frequencySet,
        'intervalSet' => $intervalSet,
        'policyEnforceAction' => $policyEnforceAction,
        'enfMessage' => $enfMessage,
        'pRegName' => $pRegName,
        'pType' => $pType,
        'pValue' => $pValue,
        'pExecPreCheckVal' => $pExecPreCheckVal,
        'peerdistribution' => $peerdist,
        'andPreCheckPath' => $andPreCheckPath,
        'dIsConfigured' => $dIsConfigured,
        'same3264config' => $same3264config
    );

    $jsonData = array("data" => $recordList);
    print_json_data($jsonData);
}



function deleteFn()
{

    $priviledge = checkModulePrivilege('deletesoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $pdo = pdo_connect();
    $delId = (int)$_POST['id'];
    $deletePackageQry = $pdo->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.Packages WHERE id=?");
    $deletePackageQry->execute([$delId]);
    $auditRes = create_auditLog('Software Distribution', 'Delete', 'Success', $delId);

    print_json_data(array('message' => "The record was deleted successfully."));
}



function getSite()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();

    $username = url::requestToText('user');

    $sql = "SELECT customer FROM " . $GLOBALS['PREFIX'] . "core.Customers WHERE username=?";
    $pdo = $db->prepare($sql);
    $pdo->execute([$username]);
    $getSiteRes = $pdo->fetchAll(PDO::FETCH_ASSOC);


    $option = "";
    foreach ($getSiteRes as $value) {
        $option .= '<option value="' . $value['customer'] . '">' . UTIL_GetTrimmedGroupName($value['customer']) . '</option>';
    }

    $jsonData = array("option" => $option);
    print_json_data($jsonData);
}



function getCategory()
{
}



function updateUploadStatFn()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $UserRestrict = $_SESSION["user"]["restirct"];
    $UserIsAdmin = $_SESSION["user"]["isadmin"];
    $username = $_SESSION["user"]["username"];
    $_SESSION['windowtype'] = 'Manage';
    $_SESSION['currentwindow'] = 'Sofware Distribution';
    $user = $_SESSION['user']['adminEmail'];

    $now = date("U");
    $db = pdo_connect();
    $package = 'Packages';

    $editid = (int)url::requestToAny('id');
    $sql = $sql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.Packages SET status='Uploaded' WHERE id=?";
    $pdo = $db->prepare($sql);
    $pdo->execute([$editid]);
}



function addFn()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();
    $user = $_SESSION['user']['adminEmail'];
    $platform = url::requestToText('platform');
    $list = "";

    $sql = "select distinct fileName from " . $GLOBALS['PREFIX'] . "softinst.Packages where platform=? and (protocol =2 or (protocol = 1 and access =''))";
    $pdo = $db->prepare($sql);
    $pdo->execute([$platform]);
    $res = $pdo->fetchAll(PDO::FETCH_ASSOC);
    foreach ($res as $key => $value) {
        if ($res[$key]['fileName'] != "") {
            $list .= "<div class='radio repositoryList'><label><input type='radio' name='selectfromrepo' value='" . $res[$key]['fileName'] . "' class='selectSource repositoryList_checkbox' onclick='selectFilefromRepositoryAdd(this)' /><span class='circle'></span><span class='check'></span>" . $res[$key]['fileName'] . "</label></div>";
        }
    }

    print_data($list);
}



function editFn()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();

    $user = $_SESSION['user']['adminEmail'];
    $platform = url::requestToText('platform');
    $list = "";

    $sql = "select distinct fileName from " . $GLOBALS['PREFIX'] . "softinst.Packages where id NOT IN(1,2,3,4,5,6,7) and owner=? and platform=? and (protocol =2 or (protocol = 1 and access =''))";
    $pdo = $db->prepare($sql);
    $pdo->execute([$user, $platform]);
    $res = $pdo->fetchAll(PDO::FETCH_ASSOC);

    foreach ($res as $key => $value) {
        if ($res[$key]['fileName'] != "") {
            $list .= "<div class='radio repositoryList'><label><input type='radio' name='selectfromrepo1' value='" . $res[$key]['fileName'] . "' class='selectSource repositoryList_checkbox1' onclick='selectFilefromRepository(this)' /><span class='circle'></span><span class='check'></span>" . $res[$key]['fileName'] . "</label></div>";
        }
    }

    print_data($list);
}



function swdDetailFn()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();

    $id = url::requestToText('sel');
    $sql = "SELECT id, platform, type, sourceType, packageName,path,path2, androidIcon,config3264type, fileName,fileName2, packageDesc, lastModified, version, status, domain, global FROM " . $GLOBALS['PREFIX'] . "softinst.Packages WHERE id=?";
    $pdo = $db->prepare($sql);
    $pdo->execute([$id]);
    $detailSqlRes = $pdo->fetch(PDO::FETCH_ASSOC);
    $platformDetail = $detailSqlRes['platform'];
    $typeDetail = $detailSqlRes['type'];
    $packNameDetail = $detailSqlRes['packageName'];
    $versionDetail = $detailSqlRes['version'];
    $pathDetail = $detailSqlRes['path'];
    $path2Detail = $detailSqlRes['path2'];
    $androidIcon = $detailSqlRes['androidIcon'];
    $forfDetail = $detailSqlRes['fileName'];
    $forfDetail1 = $detailSqlRes['fileName2'];
    $config_type = $detailSqlRes['config3264type'];
    $packDescDetail = $detailSqlRes['packageDesc'];
    $globalDetail = $detailSqlRes['global'];
    if ($config_type == 'same') {
        $config_32 = $forfDetail;
        $config_64 = '';
    } else {
        $config_32 = $forfDetail;
        $config_64 = $forfDetail1;
    }
    if ($detailSqlRes['lastModified'] == '') {
        $modifyDetail = '';
    } else {
        if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
            $modifyDetail = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $detailSqlRes['lastModified'], "m/d/Y g:i:s A");
        } else {
            $modifyDetail = date("m/d/Y g:i:s A", $detailSqlRes['lastModified']);
        }
    }

    if ($detailSqlRes['status'] == 'Initiated') {
        $uploadDetail = 'Failed';
    } else {
        $uploadDetail = $detailSqlRes['status'];
    }

    if ($globalDetail == "yes") {
        $globalDetail = "Yes";
    } else {
        $globalDetail = "No";
    }

    $recordList = array(
        'platformDetail' => $platformDetail,
        'typeDetail' => $typeDetail,
        'packNameDetail' => $packNameDetail,
        'versionDetail' => $versionDetail,
        'pathDetail' => $pathDetail,
        'path2Detail' => $path2Detail,
        'androidIcon' => $androidIcon,
        'config_type' => $config_type,
        'config_32' => $config_32,
        'config_64' => $config_64,
        'forfDetail' => $forfDetail,
        'packDescDetail' => $packDescDetail,
        'uploadDetail' => $uploadDetail,
        'modifyDetail' => $modifyDetail,
        'globalDetail' => $globalDetail
    );

    $jsonData = $recordList;
    print_json_data($jsonData);
}



function saveConfigFn()
{

    // $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    // if (!$priviledge) {
    //     echo 'Permission denied';
    //     exit();
    // }

    $db = pdo_connect();

    $id = (int)url::requestToText('id');
    $edconfig = url::rawPost('edconfig');
    $configg = url::rawPost('configg');
    if ($edconfig != '') {
        $configure = trim($edconfig);
    } else {
        $configure = trim($configg);
    }

    $updateBindings = array();
    $updateBindings[] = $configure;
    $updateBindings[] = $id;

    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.Packages SET " . url::requestToText('column') . "=? WHERE id=?";
    $sqlres = $db->prepare($sql)->execute($updateBindings);

    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.Packages SET configDetail=? WHERE id=?";
    $sqlres = $db->prepare($sql)->execute($updateBindings);

    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.Packages SET distrubute=1 WHERE id=?";
    $sqlres = $db->prepare($sql)->execute([$id]);

    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.Packages SET isConfigured=2 WHERE id=?";
    $sqlres = $db->prepare($sql)->execute([$id]);

    $typeSql = "SELECT sourceType,platform,packageName FROM " . $GLOBALS['PREFIX'] . "softinst.Packages WHERE id=?";
    $pdo = $db->prepare($typeSql);
    $pdo->execute([$id]);
    $typeRes = $pdo->fetch(PDO::FETCH_ASSOC);

    if (($typeRes['platform'] == "ios") && ($typeRes['sourceType'] == '2' || $typeRes['sourceType'] == '5')) {
        mdmConfigUpdate($typeRes);
    }
    addUpdateswdConf($id, $configure);
}



function checkAvailabilityFn()
{
    $name = url::postToText('packName');
    $vers = url::postToText('version');
    $description = url::postToText('description');

    $db = pdo_connect();
    $sql = "SELECT packageName, version FROM " . $GLOBALS['PREFIX'] . "softinst.Packages WHERE packageName=? AND version=? and packageDesc=?";
    $pdo = $db->prepare($sql);
    $pdo->execute([$name, $vers, $description]);

    if ($pdo->fetch(PDO::FETCH_ASSOC)) {
        $result = 'true';
    } else {
        $result = 'false';
    }

    $jsonData = array("data" => $result);

    echo json_encode($jsonData);
}



function showconfigDetailsFn()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();

    $id = (int)url::requestToText('id');
    $column = url::requestToText('column');

    if ($column == 'a') {
        $sql = "select addConfigDetail from " . $GLOBALS['PREFIX'] . "softinst.Packages where id=?";
        $col = "addConfigDetail";
    } else if ($column == 'c') {
        $sql = "select configDetail from " . $GLOBALS['PREFIX'] . "softinst.Packages where id=?";
        $col = "configDetail";
    } else {
        $sql = "select distributionConfigDetail from " . $GLOBALS['PREFIX'] . "softinst.Packages where id=?";
        $col = "distributionConfigDetail";
    }

    $bindings = [$id];
    $pdo = $db->prepare($sql);
    $pdo->execute($bindings);
    $config = $pdo->fetch(PDO::FETCH_ASSOC);
    $configval = $config[$col];

    $recordList = array('config' => $configval, 'column' => $col);

    $jsonData = array("data" => $recordList);
    print_json_data($jsonData);
}



function saveDistributeConfigFn_old()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $packName = url::requestToText('packNameD');
    $spid = url::requestToText('selD');
    $configStat = url::requestToText('configStatD');
    $db = pdo_connect();

    $sqlp = "SELECT platform,sourceType,ftpcdnURL as urlpath,fileName,path FROM " . $GLOBALS['PREFIX'] . "softinst.Packages WHERE id=?";
    $pdo = $db->prepare($sqlp);
    $pdo->execute([$spid]);
    $sqlresp = $pdo->fetch(PDO::FETCH_ASSOC);
    $cmdLine = url::requestToText('cmdLine2');
    $pathExec = url::requestToText('pathExec2');
    $session = url::requestToText('session2');
    $runas = url::requestToText('runas2');
    $validity = url::requestToText('validity');
    $rootKey = url::requestToText('rootKey12');
    $subKey = url::requestToText('subKey12');
    $filePath = url::requestToText('filePath12');
    $enablemsg = url::requestToText('enablemsg2');
    $msgtext = url::requestToText('msgtext12');
    $validityCheck = url::requestToText('validityCheck2');
    $preinst = url::requestToText('preinstcheck2');

    if ($enablemsg != 1) {
        $enablemsg = 0;
    }

    if ($cmdLine == '') {
        $cmdLine = 'NA';
    }

    if ($msgtext == '') {
        $msgtext_app = $msgtext;
    } else {
        $msgtext_app = '[1]-' . $msgtext;
    }

    if ($subKey == '') {
        $subKey_append = '';
    } else {
        $subKey_append = '#' . $subKey;
    }

    if ($validityCheck == 1) {
        $vRegName = url::requestToText('vRegNameDE');
        $vType = url::requestToText('vTypeDE');
        $vValue = url::requestToText('vValueDE');
        if ($validity == 0) {
            $validityStr = "," . $validity . '#';
        } else if ($validity == 1) {
            $validityStr = "," . $validity . '#' . $rootKey . $subKey_append . "#$vRegName#$vType#$vValue";
        }
    } else {
        $validityStr = "";
    }

    if ($preinst != '') {

        $pfilePath = url::requestToText('pfilePath22');
        $pSoftName = url::requestToText('pSoftName22');
        $pSoftVer = url::requestToText('pSoftVer22');
        $pKb = url::requestToText('pKb22');
        $pServicePack = url::requestToText('pServicePack22');
        $prootKey = url::requestToText('prootKey22');
        $psubKey = url::requestToText('psubKey22');
        $pExecPreCheckVal = url::requestToText('pExecPreCheckValDE');
        $pRegName = url::requestToText('pRegNameDE');
        $pType = url::requestToText('pTypeDE');
        $pValue = url::requestToText('pValueDE');

        $pKb = str_replace(",", "&&", $pKb);

        if ($pExecPreCheckVal == 0) {
            $notVal = "!";
        } else {
            $notVal = "";
        }

        if ($pSoftVer == '') {
            $pSoftVer_m = 'NA';
        } else {
            $pSoftVer_m = $pSoftVer;
        }
        if ($pKb == '') {
            $pKb_m = 'NA';
        } else {
            $pKb_m = $pKb;
        }
        if ($pServicePack == '') {
            $pServicePack_m = 'NA';
        } else {
            $pServicePack_m = $pServicePack;
        }

        if ($preinst == 0) {
            $preinstallCheck = $notVal . "0,$pfilePath";
        } else if ($preinst == 1) {
            $preinstallCheck = $notVal . "1,";
            if ($pSoftVer_m == 'NA' && $pKb_m == 'NA' && $pServicePack_m == 'NA') {
                $preinstallCheck .= "$pSoftName";
            } else if ($pKb_m == 'NA' && $pServicePack_m == 'NA') {
                if ($pSoftName == '') {
                    $pSoftName = 'NA';
                }
                $preinstallCheck .= "$pSoftName#$pSoftVer_m";
            } else if ($pServicePack_m == 'NA') {
                if ($pSoftName == '') {
                    $pSoftName = 'NA';
                }
                $preinstallCheck .= "$pSoftName#$pSoftVer_m#$pKb_m";
            } else {
                if ($pSoftName == '') {
                    $pSoftName = 'NA';
                }
                $preinstallCheck .= "$pSoftName#$pSoftVer_m#$pKb_m#$pServicePack_m";
            }
        } else if ($preinst == 2) {
            $preinstallCheck = $notVal . "2,$rootKey#$subKey#$pRegName#$pType#$pValue";
            if ($rootKey == '') {
                $preinstallCheck = $notVal . "2,NA#$subKey#$pRegName#$pType#$pValue";
            }
            if ($subKey == '') {
                $preinstallCheck = $notVal . "2,$rootKey#$pRegName#$pType#$pValue";
            }
            if ($prootKey != '' && $psubKey != '') {
                $preinstallCheck = $notVal . "2,$prootKey#$psubKey#$pRegName#$pType#$pValue";
            }
        } else if ($preinst == 3) {
            $preinstallCheck = "1,$pSoftName";
        } else {
            $preinstallCheck = "NA,NA";
        }

        $confStr = "1,NT,$pathExec,$session,$runas,1,$cmdLine,$preinstallCheck$validityStr$filePath";
    } else {
        if ($validityStr == "") {
            $confStr = "1,NT,$pathExec,$session,$runas,1,$cmdLine";
        } else {
            $confStr = "1,NT,$pathExec,$session,$runas,1,$cmdLine,NA,NA$validityStr$filePath";
        }
    }

    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.Packages SET configDetail=?, isConfigured='2' where id=?";
    $pdo = $db->prepare($sql);
    $q1 = $pdo->execute([$confStr, $spid]);

    $sqlch = "SELECT count(packageId) as count from " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration WHERE packageId=?";
    $pdo = $db->prepare($sqlch);
    $pdo->execute([$spid]);
    $resch = $pdo->fetch(PDO::FETCH_ASSOC);

    $validation = (url::issetInRequest('validityCheck2') && url::isNumericInRequest('validityCheck2') && url::requestToInt('validityCheck2') == 1) ? $validity : '';
    $session = !isset($session) || empty($session) ? NULL : $session;

    if ($resch && isset($resch['count']) && is_numeric($resch['count']) && intval($resch['count']) > 0) {
        $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration SET session=?, runas=?, cmdLines=?, enableMessage=?, messageText=?, executePath=?, validation=?, rootKey=?, subKey=?, validationFilePath=?, checkPreInstall=?, pExecPreCheckVal=?, pValidationFilePath=?, softwareName=?, softwareVersion=?, knowledgeBase=?, servicePack=?, pRootKey=?, pSubKey=?, pRegName=?, pType=?, pValue=?, vRegName=?, vType=?, vValue=? WHERE packageId=?";
        $pdo = $db->prepare($updateSql);
        $q2 = $pdo->execute([$session, $runas, $cmdLine, $enablemsg, $msgtext, $pathExec, $validation, $rootKey, $subKey, $filePath, $preinst, $pExecPreCheckVal, $pfilePath, $pSoftName, $pSoftVer, $pKb, $pServicePack, $prootKey, $psubKey, $pRegName, $pType, $pValue, $vRegName, $vType, $vValue, $spid]);
    } else {
        $insertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration (packageId, session, runas, cmdLines, enableMessage, messageText, executePath, validation, rootKey, subKey, validationFilePath, checkPreInstall, pExecPreCheckVal, pValidationFilePath, softwareName, softwareVersion, knowledgeBase, servicePack, pRootKey, pSubKey, pRegName, pType, pValue, vRegName, vType, vValue) VALUES (?, ?, ?, ?, ?, ?,?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, ?,?)";
        $pdo = $db->prepare($insertSql);
        $q3 = $pdo->execute([$spid, $session, $runas, $cmdLine, $enablemsg, $msgtext, $pathExec, $validation, $rootKey, $subKey, $filePath, $preinst, $pExecPreCheckVal, $pfilePath, $pSoftName, $pSoftVer, $pKb, $pServicePack, $prootKey, $psubKey, $pRegName, $pType, $pValue, $vRegName, $vType, $vValue]);
    }
}

function createLineConfiguration(
    $append_32deploy,
    $conf_32,
    $line1_32_cmd,
    $typeoffile,
    $filepathexist,
    $line1_32_pexecprecheck,
    $line1_32_pfilepath,
    $line1_32_psoftname,
    $line1_32_pSoftVer,
    $line1_32_prootKey,
    $line1_32_pTypeDE,
    $line1_32_pValueDE,
    $line1_32_rootKey,
    $line1_32_validity,
    $line1_32_vTypeDE,
    $line1_32_vValueDE,
    $line1_32_filepath,
    $line1_32_32patchDep,
    $tablename = '',
    $line = '',
    $id = ''
) {

    $conf1_32 = explode_user_def(',', $conf_32);

    $typeNum = (int)trim($line, 'line');
    $enable = $conf1_32[0];
    $os = $conf1_32[1];
    $url = $conf1_32[2];
    $session = $conf1_32[3];
    $api = $conf1_32[4];

    $line1_32Arr = array(
        $line . "_append" => $append_32deploy,
        $line . "_enable" => $enable,
        $line . "_os" => $os,
        $line . "_url" => $url,
        $line . "_session" => $session,
        $line . "_api" => $api,
        $line . "_cmndline" => $line1_32_cmd,
        $line . "_typeoffile" => $typeoffile,
        $line . "_filepathexist" => $filepathexist,
        $line . "_pexecprecheck" => $line1_32_pexecprecheck,
        $line . "_pfilepath" => $line1_32_pfilepath,
        $line . "_psoftname" => $line1_32_psoftname,
        $line . "_pSoftVer" => $line1_32_pSoftVer,
        $line . "_prootKey" => $line1_32_prootKey,
        $line . "_pTypeDE" => $line1_32_pTypeDE,
        $line . "_pValueDE" => $line1_32_pValueDE,
        $line . "_rootKey" => $line1_32_rootKey,
        $line . "_validity" => $line1_32_validity,
        $line . "_vTypeDE" => $line1_32_vTypeDE,
        $line . "_vValueDE" => $line1_32_vValueDE,
        $line . "_filepath" => $line1_32_filepath,
        $line . "_patchDep" => $line1_32_32patchDep
    );

    $line1_32Arr = json_encode($line1_32Arr);

    insertConfiguration($line1_32Arr, $tablename, $id, $line);

    $precheckConfig = '';

    if ($typeoffile === "0") {
        if ($filepathexist == 0) {
            $precheckConfig .= '!0,';
        } else {
            $precheckConfig .= '0,';
        }

        $precheckConfig .= $line1_32_pfilepath;

        $preCheck = $precheckConfig;
    } else if ($typeoffile == 1) {
        if ($line1_32_psoftname && $line1_32_pSoftVer) {
            $precheckConfig = '1,' . $line1_32_psoftname . "#" . $line1_32_pSoftVer;
        } else {
            $precheckConfig = '!1,' . $line1_32_psoftname . "#" . $line1_32_pSoftVer;
        }
        $preCheck = $precheckConfig;
    } else if ($typeoffile == 2) {
        if ($filepathexist == 0) {
            $precheckConfig = '!2,' . $line1_32_prootKey . '#'  . $line1_32_pValueDE;
        } else {
            $precheckConfig = '2,' . $line1_32_prootKey . '#'  . $line1_32_pValueDE;
        }
        $preCheck = $precheckConfig;
    } else {
        // if($line1_32_32patchDep && $typeNum % 2 == 0) {
        //     $preCheck = '3,' . $line1_32_32patchDep;
        // } else {
        $preCheck = "";
        // }
    }
    // if ($line1_32_validity == 0) {
    //     $postCheck = '';
    // } else {
    //     $postCheck = '1';
    // }

    if ($line1_32_validity == 1 || $line1_32_validity === "0") {
        $vRegName = $line1_32_rootKey;
        // $vType = $line1_32_vTypeDE;
        $vValue = $line1_32_vValueDE;
        $vFilePath = $line1_32_filepath;
        if ($line1_32_validity == 0) {
            $validityStr = $line1_32_validity . '#' . $vFilePath;
        } else if ($line1_32_validity == 1) {
            $validityStr = $line1_32_validity  . "#$vRegName#$vValue";
        }
        if ($preCheck == "") {
            $preCheck = ",";
        }
        $line1_ConfStr = $conf_32 . "," . $append_32deploy . "," . $line1_32_cmd . "," . $preCheck . "," . $validityStr;
        // logs::log('==========', [$conf_32, $append_32deploy, $line1_32_cmd, $preCheck, $validityStr]);
    } else {
        $validityStr = "";
        $line1_ConfStr = $conf_32 . "," . $append_32deploy . "," . $line1_32_cmd . "," . $preCheck;
    }
    $line_conf = rtrim($line1_ConfStr, ',');

    // if($typeNum % 2 != 0) {
    //     $line_conf = $line_conf . ',' .$line1_32_32patchDep;
    // } else {
    //     if(str_contains($line1_32_32patchDep, ',')) {
    //         $line_conf = $line_conf . ',' . $line1_32_32patchDep;
    //     } else {
    //         $line_conf = $line_conf . ',' . ((int)$line1_32_32patchDep + 1);
    //     }
    // }
    return $line_conf;
}


function insertConfiguration($val, $tablename, $id, $line)
{
    $db = pdo_connect();
    $sql1 = $db->prepare("SELECT count(id)as count FROM " . $GLOBALS['PREFIX'] . "softinst.$tablename WHERE packageId=?");
    $sql1->execute([$id]);
    $sql1Res = $sql1->fetch(PDO::FETCH_ASSOC);

    if ($sql1Res['count'] > 0) {
        $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.$tablename SET $line = ?  WHERE packageId=?";
        $pdo = $db->prepare($updateSql);
        $q2 = $pdo->execute([$val, $id]);
    } else {
        $insertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.$tablename (packageId,$line) VALUES (?, ?)";
        $pdo = $db->prepare($insertSql);
        $q3 = $pdo->execute([$id, $val]);
    }
}

function saveDistributeConfigFn()
{
    $spid = url::requestToText('packageid');
    $packageName = url::rawRequest('packagename');
    $os = url::rawRequest('platform');
    $restartClient = url::rawRequest('resetClient');
    $restartPC = url::rawRequest('resetPC');

    if ($os == 'windows') {
        $os = 'NT';
    }

    // NanoDB::query("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_32 WHERE packageId=?", [$spid]);
    // NanoDB::query("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_64 WHERE packageId=?", [$spid]);

    $append_32deploy = '4';
    $enable_32 = url::rawRequest('32DeployMain_enable');
    $url_32 = url::rawRequest('32DeployMain_url');
    $session_32 = url::rawRequest('32DeployMain_session');
    $api_32 = url::rawRequest('32DeployMain_api');
    $conf1_32 = $enable_32 . "," . $os . "," . $url_32 . "," . $session_32 . "," . $api_32;

    $line1_32_cmd = url::rawRequest('32DeployMain_cmndline');
    $typeoffile = url::rawRequest('32DeployMain_typeoffile');
    $filepathexist = url::rawRequest('32DeployMain_pexecprecheck');
    $line1_32_pexecprecheck = url::rawRequest('32DeployMain_pexecprecheck');
    $line1_32_pfilepath = url::rawRequest('32DeployMain_pfilepath');
    $line1_32_psoftname = url::rawRequest('32DeployMain_psoftname');
    $line1_32_pSoftVer = url::rawRequest('32DeployMain_pSoftVer');
    $line1_32_prootKey = url::rawRequest('32DeployMain_prootKey');
    $line1_32_pTypeDE = url::rawRequest('32DeployMain_pTypeDE');
    $line1_32_pValueDE = url::rawRequest('32DeployMain_pValueDE');
    $line1_32_rootKey = url::rawRequest('32DeployMain_rootKey');
    $line1_32_validity = url::rawRequest('32DeployMain_validity');
    $line1_32_vTypeDE = url::rawRequest('32DeployMain_vTypeDE');
    $line1_32_vValueDE = url::rawRequest('32DeployMain_vValueDE');
    $line1_32_filepath = url::rawRequest('32DeployMain_filepath');
    $line1_32_patchDep = url::rawRequest('32DeployMain_patchDep');

    $line1_32 = createLineConfiguration(
        $append_32deploy,
        $conf1_32,
        $line1_32_cmd,
        $typeoffile,
        $filepathexist,
        $line1_32_pexecprecheck,
        $line1_32_pfilepath,
        $line1_32_psoftname,
        $line1_32_pSoftVer,
        $line1_32_prootKey,
        $line1_32_pTypeDE,
        $line1_32_pValueDE,
        $line1_32_rootKey,
        $line1_32_validity,
        $line1_32_vTypeDE,
        $line1_32_vValueDE,
        $line1_32_filepath,
        $line1_32_patchDep,
        'PackagesConfiguration_32',
        'line1',
        $spid
    );

    $append_32exec = '1';
    $enable_32exec = url::rawRequest('32ExecuteMain_enable');
    $url_32exec = url::rawRequest('32ExecuteMain_url');
    $session_32exec = url::rawRequest('32ExecuteMain_session');
    $api_32exec = url::rawRequest('32ExecuteMain_api');
    $conf2_32 = $enable_32exec . "," . $os . "," . $url_32exec . "," . $session_32exec . "," . $api_32exec;
    $line2_32_cmd = url::rawRequest('32ExecuteMain_cmndline');
    $typeoffile2 = url::rawRequest('32ExecuteMain_typeoffile');
    $filepathexist2 = url::rawRequest('32ExecuteMain_pexecprecheck');
    $line2_32_pexecprecheck = url::rawRequest('32ExecuteMain_pexecprecheck');
    $line2_32_pfilepath = url::rawRequest('32ExecuteMain_pfilepath');
    $line2_32_psoftname = url::rawRequest('32ExecuteMain_psoftname');
    $line2_32_pSoftVer = url::rawRequest('32ExecuteMain_pSoftVer');
    $line2_32_prootKey = url::rawRequest('32ExecuteMain_prootKey');
    $line2_32_pTypeDE = url::rawRequest('32ExecuteMain_pTypeDE');
    $line2_32_pValueDE = url::rawRequest('32ExecuteMain_pValueDE');
    $line2_32_rootKey = url::rawRequest('32ExecuteMain_rootKey');
    $line2_32_validity = url::rawRequest('32ExecuteMain_validity');
    $line2_32_vTypeDE = url::rawRequest('32ExecuteMain_vTypeDE');
    $line2_32_vValueDE = url::rawRequest('32ExecuteMain_vValueDE');
    $line2_32_filepath = url::rawRequest('32ExecuteMain_filepath');
    $line2_32_patchDep = url::rawRequest('32ExecuteMain_patchDep');

    $line2_32 = createLineConfiguration(
        $append_32exec,
        $conf2_32,
        $line2_32_cmd,
        $typeoffile2,
        $filepathexist2,
        $line2_32_pexecprecheck,
        $line2_32_pfilepath,
        $line2_32_psoftname,
        $line2_32_pSoftVer,
        $line2_32_prootKey,
        $line2_32_pTypeDE,
        $line2_32_pValueDE,
        $line2_32_rootKey,
        $line2_32_validity,
        $line2_32_vTypeDE,
        $line2_32_vValueDE,
        $line2_32_filepath,
        $line2_32_patchDep,
        'PackagesConfiguration_32',
        'line2',
        $spid
    );

    $append_32deploy = '4';
    $enable_32 = url::rawRequest('32DeployResetClient_enable');
    $url_32 = url::rawRequest('32DeployResetClient_url');
    $session_32 = url::rawRequest('32DeployResetClient_session');
    $api_32 = url::rawRequest('32DeployResetClient_api');
    $conf1_32 = $enable_32 . "," . $os . "," . $url_32 . "," . $session_32 . "," . $api_32;

    $line3_32_cmd = url::rawRequest('32DeployResetClient_cmndline');
    $typeoffile = url::rawRequest('32DeployResetClient_typeoffile');
    $filepathexist = url::rawRequest('32DeployResetClient_pexecprecheck');
    $line3_32_pexecprecheck = url::rawRequest('32DeployResetClient_pexecprecheck');
    $line3_32_pfilepath = url::rawRequest('32DeployResetClient_pfilepath');
    $line3_32_psoftname = url::rawRequest('32DeployResetClient_psoftname');
    $line3_32_pSoftVer = url::rawRequest('32DeployResetClient_pSoftVer');
    $line3_32_prootKey = url::rawRequest('32DeployResetClient_prootKey');
    $line3_32_pTypeDE = url::rawRequest('32DeployResetClient_pTypeDE');
    $line3_32_pValueDE = url::rawRequest('32DeployResetClient_pValueDE');
    $line3_32_rootKey = url::rawRequest('32DeployResetClient_rootKey');
    $line3_32_validity = url::rawRequest('32DeployResetClient_validity');
    $line3_32_vTypeDE = url::rawRequest('32DeployResetClient_vTypeDE');
    $line3_32_vValueDE = url::rawRequest('32DeployResetClient_vValueDE');
    $line3_32_filepath = url::rawRequest('32DeployResetClient_filepath');
    $line3_32_patchDep = url::rawRequest('32DeployResetClient_patchDep');

    $line3_32 = createLineConfiguration(
        $append_32deploy,
        $conf1_32,
        $line3_32_cmd,
        $typeoffile,
        $filepathexist,
        $line3_32_pexecprecheck,
        $line3_32_pfilepath,
        $line3_32_psoftname,
        $line3_32_pSoftVer,
        $line3_32_prootKey,
        $line3_32_pTypeDE,
        $line3_32_pValueDE,
        $line3_32_rootKey,
        $line3_32_validity,
        $line3_32_vTypeDE,
        $line3_32_vValueDE,
        $line3_32_filepath,
        $line3_32_patchDep,
        'PackagesConfiguration_32',
        'line3',
        $spid
    );

    $append_32exec = '1';
    $enable_32exec = url::rawRequest('32ExecuteResetClient_enable');
    $url_32exec = url::rawRequest('32ExecuteResetClient_url');
    $session_32exec = url::rawRequest('32ExecuteResetClient_session');
    $api_32exec = url::rawRequest('32ExecuteResetClient_api');
    $conf2_32 = $enable_32exec . "," . $os . "," . $url_32exec . "," . $session_32exec . "," . $api_32exec;
    $line4_32_cmd = url::rawRequest('32ExecuteResetClient_cmndline');
    $typeoffile2 = url::rawRequest('32ExecuteResetClient_typeoffile');
    $filepathexist2 = url::rawRequest('32ExecuteResetClient_pexecprecheck');
    $line4_32_pexecprecheck = url::rawRequest('32ExecuteResetClient_pexecprecheck');
    $line4_32_pfilepath = url::rawRequest('32ExecuteResetClient_pfilepath');
    $line4_32_psoftname = url::rawRequest('32ExecuteResetClient_psoftname');
    $line4_32_pSoftVer = url::rawRequest('32ExecuteResetClient_pSoftVer');
    $line4_32_prootKey = url::rawRequest('32ExecuteResetClient_prootKey');
    $line4_32_pTypeDE = url::rawRequest('32ExecuteResetClient_pTypeDE');
    $line4_32_pValueDE = url::rawRequest('32ExecuteResetClient_pValueDE');
    $line4_32_rootKey = url::rawRequest('32ExecuteResetClient_rootKey');
    $line4_32_validity = url::rawRequest('32ExecuteResetClient_validity');
    $line4_32_vTypeDE = url::rawRequest('32ExecuteResetClient_vTypeDE');
    $line4_32_vValueDE = url::rawRequest('32ExecuteResetClient_vValueDE');
    $line4_32_filepath = url::rawRequest('32ExecuteResetClient_filepath');
    $line4_32_patchDep = url::rawRequest('32ExecuteResetClient_patchDep');

    $line4_32 = createLineConfiguration(
        $append_32exec,
        $conf2_32,
        $line4_32_cmd,
        $typeoffile2,
        $filepathexist2,
        $line4_32_pexecprecheck,
        $line4_32_pfilepath,
        $line4_32_psoftname,
        $line4_32_pSoftVer,
        $line4_32_prootKey,
        $line4_32_pTypeDE,
        $line4_32_pValueDE,
        $line4_32_rootKey,
        $line4_32_validity,
        $line4_32_vTypeDE,
        $line4_32_vValueDE,
        $line4_32_filepath,
        $line4_32_patchDep,
        'PackagesConfiguration_32',
        'line4',
        $spid
    );

    $append_32deploy = '4';
    $enable_32 = url::rawRequest('32DeployResetPC_enable');
    $url_32 = url::rawRequest('32DeployResetPC_url');
    $session_32 = url::rawRequest('32DeployResetPC_session');
    $api_32 = url::rawRequest('32DeployResetPC_api');
    $conf1_32 = $enable_32 . "," . $os . "," . $url_32 . "," . $session_32 . "," . $api_32;

    $line5_32_cmd = url::rawRequest('32DeployResetPC_cmndline');
    $typeoffile = url::rawRequest('32DeployResetPC_typeoffile');
    $filepathexist = url::rawRequest('32DeployResetPC_pexecprecheck');
    $line5_32_pexecprecheck = url::rawRequest('32DeployResetPC_pexecprecheck');
    $line5_32_pfilepath = url::rawRequest('32DeployResetPC_pfilepath');
    $line5_32_psoftname = url::rawRequest('32DeployResetPC_psoftname');
    $line5_32_pSoftVer = url::rawRequest('32DeployResetPC_pSoftVer');
    $line5_32_prootKey = url::rawRequest('32DeployResetPC_prootKey');
    $line5_32_pTypeDE = url::rawRequest('32DeployResetPC_pTypeDE');
    $line5_32_pValueDE = url::rawRequest('32DeployResetPC_pValueDE');
    $line5_32_rootKey = url::rawRequest('32DeployResetPC_rootKey');
    $line5_32_validity = url::rawRequest('32DeployResetPC_validity');
    $line5_32_vTypeDE = url::rawRequest('32DeployResetPC_vTypeDE');
    $line5_32_vValueDE = url::rawRequest('32DeployResetPC_vValueDE');
    $line5_32_filepath = url::rawRequest('32DeployResetPC_filepath');
    $line5_32_patchDep = url::rawRequest('32DeployResetPC_patchDep');

    $line5_32 = createLineConfiguration(
        $append_32deploy,
        $conf1_32,
        $line5_32_cmd,
        $typeoffile,
        $filepathexist,
        $line5_32_pexecprecheck,
        $line5_32_pfilepath,
        $line5_32_psoftname,
        $line5_32_pSoftVer,
        $line5_32_prootKey,
        $line5_32_pTypeDE,
        $line5_32_pValueDE,
        $line5_32_rootKey,
        $line5_32_validity,
        $line5_32_vTypeDE,
        $line5_32_vValueDE,
        $line5_32_filepath,
        $line5_32_patchDep,
        'PackagesConfiguration_32',
        'line5',
        $spid
    );

    $append_32exec = '1';
    $enable_32exec = url::rawRequest('32ExecuteResetPC_enable');
    $url_32exec = url::rawRequest('32ExecuteResetPC_url');
    $session_32exec = url::rawRequest('32ExecuteResetPC_session');
    $api_32exec = url::rawRequest('32ExecuteResetPC_api');
    $conf2_32 = $enable_32exec . "," . $os . "," . $url_32exec . "," . $session_32exec . "," . $api_32exec;
    $line6_32_cmd = url::rawRequest('32ExecuteResetPC_cmndline');
    $typeoffile2 = url::rawRequest('32ExecuteResetPC_typeoffile');
    $filepathexist2 = url::rawRequest('32ExecuteResetPC_pexecprecheck');
    $line6_32_pexecprecheck = url::rawRequest('32ExecuteResetPC_pexecprecheck');
    $line6_32_pfilepath = url::rawRequest('32ExecuteResetPC_pfilepath');
    $line6_32_psoftname = url::rawRequest('32ExecuteResetPC_psoftname');
    $line6_32_pSoftVer = url::rawRequest('32ExecuteResetPC_pSoftVer');
    $line6_32_prootKey = url::rawRequest('32ExecuteResetPC_prootKey');
    $line6_32_pTypeDE = url::rawRequest('32ExecuteResetPC_pTypeDE');
    $line6_32_pValueDE = url::rawRequest('32ExecuteResetPC_pValueDE');
    $line6_32_rootKey = url::rawRequest('32ExecuteResetPC_rootKey');
    $line6_32_validity = url::rawRequest('32ExecuteResetPC_validity');
    $line6_32_vTypeDE = url::rawRequest('32ExecuteResetPC_vTypeDE');
    $line6_32_vValueDE = url::rawRequest('32ExecuteResetPC_vValueDE');
    $line6_32_filepath = url::rawRequest('32ExecuteResetPC_filepath');
    $line6_32_patchDep = url::rawRequest('32ExecuteResetPC_patchDep');

    $line6_32 = createLineConfiguration(
        $append_32exec,
        $conf2_32,
        $line6_32_cmd,
        $typeoffile2,
        $filepathexist2,
        $line6_32_pexecprecheck,
        $line6_32_pfilepath,
        $line6_32_psoftname,
        $line6_32_pSoftVer,
        $line6_32_prootKey,
        $line6_32_pTypeDE,
        $line6_32_pValueDE,
        $line6_32_rootKey,
        $line6_32_validity,
        $line6_32_vTypeDE,
        $line6_32_vValueDE,
        $line6_32_filepath,
        $line6_32_patchDep,
        'PackagesConfiguration_32',
        'line6',
        $spid
    );


    $append_64deploy = '4';
    $enable_64 = url::rawRequest('64DeployMain_enable');
    $url_64 = url::rawRequest('64DeployMain_url');
    $session_64 = url::rawRequest('64DeployMain_session');
    $api_64 = url::rawRequest('64DeployMain_api');
    $conf1_64 = $enable_64 . "," . $os . "," . $url_64 . "," . $session_64 . "," . $api_64;
    $line1_64_cmd = url::rawRequest('64DeployMain_cmndline');
    $typeoffile = url::rawRequest('64DeployMain_typeoffile');
    $filepathexist = url::rawRequest('64DeployMain_pexecprecheck');
    $line1_64_pexecprecheck = url::rawRequest('64DeployMain_pexecprecheck');
    $line1_64_pfilepath = url::rawRequest('64DeployMain_pfilepath');
    $line1_64_psoftname = url::rawRequest('64DeployMain_psoftname');
    $line1_64_pSoftVer = url::rawRequest('64DeployMain_pSoftVer');
    $line1_64_prootKey = url::rawRequest('64DeployMain_prootKey');
    $line1_64_pTypeDE = url::rawRequest('64DeployMain_pTypeDE');
    $line1_64_pValueDE = url::rawRequest('64DeployMain_pValueDE');
    $line1_64_rootKey = url::rawRequest('64DeployMain_rootKey');
    $line1_64_validity = url::rawRequest('64DeployMain_validity');
    $line1_64_vTypeDE = url::rawRequest('64DeployMain_vTypeDE');
    $line1_64_vValueDE = url::rawRequest('64DeployMain_vValueDE');
    $line1_64_filepath = url::rawRequest('64DeployMain_filepath');
    $line1_64_patchDep = url::rawRequest('64DeployMain_patchDep');

    $line1_64 = createLineConfiguration(
        $append_64deploy,
        $conf1_64,
        $line1_64_cmd,
        $typeoffile,
        $filepathexist,
        $line1_64_pexecprecheck,
        $line1_64_pfilepath,
        $line1_64_psoftname,
        $line1_64_pSoftVer,
        $line1_64_prootKey,
        $line1_64_pTypeDE,
        $line1_64_pValueDE,
        $line1_64_rootKey,
        $line1_64_validity,
        $line1_64_vTypeDE,
        $line1_64_vValueDE,
        $line1_64_filepath,
        $line1_64_patchDep,
        'PackagesConfiguration_64',
        'line1',
        $spid
    );

    $append_64exec = '1';
    $enable_64exec = url::rawRequest('64ExecuteMain_enable');
    $url_64exec = url::rawRequest('64ExecuteMain_url');
    $session_64exec = url::rawRequest('64ExecuteMain_session');
    $api_64exec = url::rawRequest('64ExecuteMain_api');
    $conf2_64 = $enable_64exec . "," . $os . "," . $url_64exec . "," . $session_64exec . "," . $api_64exec;
    $line2_64_cmd = url::rawRequest('64ExecuteMain_cmndline');
    $typeoffile2 = url::rawRequest('64ExecuteMain_typeoffile');
    $filepathexist2 = url::rawRequest('64ExecuteMain_pexecprecheck');
    $line2_64_pexecprecheck = url::rawRequest('64ExecuteMain_pexecprecheck');
    $line2_64_pfilepath = url::rawRequest('64ExecuteMain_pfilepath');
    $line2_64_psoftname = url::rawRequest('64ExecuteMain_psoftname');
    $line2_64_pSoftVer = url::rawRequest('64ExecuteMain_pSoftVer');
    $line2_64_prootKey = url::rawRequest('64ExecuteMain_prootKey');
    $line2_64_pTypeDE = url::rawRequest('64ExecuteMain_pTypeDE');
    $line2_64_pValueDE = url::rawRequest('64ExecuteMain_pValueDE');
    $line2_64_rootKey = url::rawRequest('64ExecuteMain_rootKey');
    $line2_64_validity = url::rawRequest('64ExecuteMain_validity');
    $line2_64_vTypeDE = url::rawRequest('64ExecuteMain_vTypeDE');
    $line2_64_vValueDE = url::rawRequest('64ExecuteMain_vValueDE');
    $line2_64_filepath = url::rawRequest('64ExecuteMain_filepath');
    $line2_64_patchDep = url::rawRequest('64ExecuteMain_patchDep');

    $line2_64 = createLineConfiguration(
        $append_64exec,
        $conf2_64,
        $line2_64_cmd,
        $typeoffile2,
        $filepathexist2,
        $line2_64_pexecprecheck,
        $line2_64_pfilepath,
        $line2_64_psoftname,
        $line2_64_pSoftVer,
        $line2_64_prootKey,
        $line2_64_pTypeDE,
        $line2_64_pValueDE,
        $line2_64_rootKey,
        $line2_64_validity,
        $line2_64_vTypeDE,
        $line2_64_vValueDE,
        $line2_64_filepath,
        $line2_64_patchDep,
        'PackagesConfiguration_64',
        'line2',
        $spid
    );

    $append_64deploy = '4';
    $enable_64 = url::rawRequest('64DeployResetClient_enable');
    $url_64 = url::rawRequest('64DeployResetClient_url');
    $session_64 = url::rawRequest('64DeployResetClient_session');
    $api_64 = url::rawRequest('64DeployResetClient_api');
    $conf1_64 = $enable_64 . "," . $os . "," . $url_64 . "," . $session_64 . "," . $api_64;

    $line3_64_cmd = url::rawRequest('64DeployResetClient_cmndline');
    $typeoffile = url::rawRequest('64DeployResetClient_typeoffile');
    $filepathexist = url::rawRequest('64DeployResetClient_pexecprecheck');
    $line3_64_pexecprecheck = url::rawRequest('64DeployResetClient_pexecprecheck');
    $line3_64_pfilepath = url::rawRequest('64DeployResetClient_pfilepath');
    $line3_64_psoftname = url::rawRequest('64DeployResetClient_psoftname');
    $line3_64_pSoftVer = url::rawRequest('64DeployResetClient_pSoftVer');
    $line3_64_prootKey = url::rawRequest('64DeployResetClient_prootKey');
    $line3_64_pTypeDE = url::rawRequest('64DeployResetClient_pTypeDE');
    $line3_64_pValueDE = url::rawRequest('64DeployResetClient_pValueDE');
    $line3_64_rootKey = url::rawRequest('64DeployResetClient_rootKey');
    $line3_64_validity = url::rawRequest('64DeployResetClient_validity');
    $line3_64_vTypeDE = url::rawRequest('64DeployResetClient_vTypeDE');
    $line3_64_vValueDE = url::rawRequest('64DeployResetClient_vValueDE');
    $line3_64_filepath = url::rawRequest('64DeployResetClient_filepath');
    $line3_64_patchDep = url::rawRequest('64DeployResetClient_patchDep');

    $line3_64 = createLineConfiguration(
        $append_64deploy,
        $conf1_64,
        $line3_64_cmd,
        $typeoffile,
        $filepathexist,
        $line3_64_pexecprecheck,
        $line3_64_pfilepath,
        $line3_64_psoftname,
        $line3_64_pSoftVer,
        $line3_64_prootKey,
        $line3_64_pTypeDE,
        $line3_64_pValueDE,
        $line3_64_rootKey,
        $line3_64_validity,
        $line3_64_vTypeDE,
        $line3_64_vValueDE,
        $line3_64_filepath,
        $line3_64_patchDep,
        'PackagesConfiguration_64',
        'line3',
        $spid
    );

    $append_64exec = '1';
    $enable_64exec = url::rawRequest('64ExecuteResetClient_enable');
    $url_64exec = url::rawRequest('64ExecuteResetClient_url');
    $session_64exec = url::rawRequest('64ExecuteResetClient_session');
    $api_64exec = url::rawRequest('64ExecuteResetClient_api');
    $conf2_64 = $enable_64exec . "," . $os . "," . $url_64exec . "," . $session_64exec . "," . $api_64exec;
    $line4_64_cmd = url::rawRequest('64ExecuteResetClient_cmndline');
    $typeoffile2 = url::rawRequest('64ExecuteResetClient_typeoffile');
    $filepathexist2 = url::rawRequest('64ExecuteResetClient_pexecprecheck');
    $line4_64_pexecprecheck = url::rawRequest('64ExecuteResetClient_pexecprecheck');
    $line4_64_pfilepath = url::rawRequest('64ExecuteResetClient_pfilepath');
    $line4_64_psoftname = url::rawRequest('64ExecuteResetClient_psoftname');
    $line4_64_pSoftVer = url::rawRequest('64ExecuteResetClient_pSoftVer');
    $line4_64_prootKey = url::rawRequest('64ExecuteResetClient_prootKey');
    $line4_64_pTypeDE = url::rawRequest('64ExecuteResetClient_pTypeDE');
    $line4_64_pValueDE = url::rawRequest('64ExecuteResetClient_pValueDE');
    $line4_64_rootKey = url::rawRequest('64ExecuteResetClient_rootKey');
    $line4_64_validity = url::rawRequest('64ExecuteResetClient_validity');
    $line4_64_vTypeDE = url::rawRequest('64ExecuteResetClient_vTypeDE');
    $line4_64_vValueDE = url::rawRequest('64ExecuteResetClient_vValueDE');
    $line4_64_filepath = url::rawRequest('64ExecuteResetClient_filepath');
    $line4_64_patchDep = url::rawRequest('64ExecuteResetClient_patchDep');

    $line4_64 = createLineConfiguration(
        $append_64exec,
        $conf2_64,
        $line4_64_cmd,
        $typeoffile2,
        $filepathexist2,
        $line4_64_pexecprecheck,
        $line4_64_pfilepath,
        $line4_64_psoftname,
        $line4_64_pSoftVer,
        $line4_64_prootKey,
        $line4_64_pTypeDE,
        $line4_64_pValueDE,
        $line4_64_rootKey,
        $line4_64_validity,
        $line4_64_vTypeDE,
        $line4_64_vValueDE,
        $line4_64_filepath,
        $line4_64_patchDep,
        'PackagesConfiguration_64',
        'line4',
        $spid
    );

    $append_64deploy = '4';
    $enable_64 = url::rawRequest('64DeployResetPC_enable');
    $url_64 = url::rawRequest('64DeployResetPC_url');
    $session_64 = url::rawRequest('64DeployResetPC_session');
    $api_64 = url::rawRequest('64DeployResetPC_api');
    $conf1_64 = $enable_64 . "," . $os . "," . $url_64 . "," . $session_64 . "," . $api_64;

    $line5_64_cmd = url::rawRequest('64DeployResetPC_cmndline');
    $typeoffile = url::rawRequest('64DeployResetPC_typeoffile');
    $filepathexist = url::rawRequest('64DeployResetPC_pexecprecheck');
    $line5_64_pexecprecheck = url::rawRequest('64DeployResetPC_pexecprecheck');
    $line5_64_pfilepath = url::rawRequest('64DeployResetPC_pfilepath');
    $line5_64_psoftname = url::rawRequest('64DeployResetPC_psoftname');
    $line5_64_pSoftVer = url::rawRequest('64DeployResetPC_pSoftVer');
    $line5_64_prootKey = url::rawRequest('64DeployResetPC_prootKey');
    $line5_64_pTypeDE = url::rawRequest('64DeployResetPC_pTypeDE');
    $line5_64_pValueDE = url::rawRequest('64DeployResetPC_pValueDE');
    $line5_64_rootKey = url::rawRequest('64DeployResetPC_rootKey');
    $line5_64_validity = url::rawRequest('64DeployResetPC_validity');
    $line5_64_vTypeDE = url::rawRequest('64DeployResetPC_vTypeDE');
    $line5_64_vValueDE = url::rawRequest('64DeployResetPC_vValueDE');
    $line5_64_filepath = url::rawRequest('64DeployResetPC_filepath');
    $line5_64_patchDep = url::rawRequest('64DeployResetPC_patchDep');

    $line5_64 = createLineConfiguration(
        $append_64deploy,
        $conf1_64,
        $line5_64_cmd,
        $typeoffile,
        $filepathexist,
        $line5_64_pexecprecheck,
        $line5_64_pfilepath,
        $line5_64_psoftname,
        $line5_64_pSoftVer,
        $line5_64_prootKey,
        $line5_64_pTypeDE,
        $line5_64_pValueDE,
        $line5_64_rootKey,
        $line5_64_validity,
        $line5_64_vTypeDE,
        $line5_64_vValueDE,
        $line5_64_filepath,
        $line5_64_patchDep,
        'PackagesConfiguration_64',
        'line5',
        $spid
    );

    $append_64exec = '1';
    $enable_64exec = url::rawRequest('64ExecuteResetPC_enable');
    $url_64exec = url::rawRequest('64ExecuteResetPC_url');
    $session_64exec = url::rawRequest('64ExecuteResetPC_session');
    $api_64exec = url::rawRequest('64ExecuteResetPC_api');
    $conf2_64 = $enable_64exec . "," . $os . "," . $url_64exec . "," . $session_64exec . "," . $api_64exec;
    $line6_64_cmd = url::rawRequest('64ExecuteResetPC_cmndline');
    $typeoffile2 = url::rawRequest('64ExecuteResetPC_typeoffile');
    $filepathexist2 = url::rawRequest('64ExecuteResetPC_pexecprecheck');
    $line6_64_pexecprecheck = url::rawRequest('64ExecuteResetPC_pexecprecheck');
    $line6_64_pfilepath = url::rawRequest('64ExecuteResetPC_pfilepath');
    $line6_64_psoftname = url::rawRequest('64ExecuteResetPC_psoftname');
    $line6_64_pSoftVer = url::rawRequest('64ExecuteResetPC_pSoftVer');
    $line6_64_prootKey = url::rawRequest('64ExecuteResetPC_prootKey');
    $line6_64_pTypeDE = url::rawRequest('64ExecuteResetPC_pTypeDE');
    $line6_64_pValueDE = url::rawRequest('64ExecuteResetPC_pValueDE');
    $line6_64_rootKey = url::rawRequest('64ExecuteResetPC_rootKey');
    $line6_64_validity = url::rawRequest('64ExecuteResetPC_validity');
    $line6_64_vTypeDE = url::rawRequest('64ExecuteResetPC_vTypeDE');
    $line6_64_vValueDE = url::rawRequest('64ExecuteResetPC_vValueDE');
    $line6_64_filepath = url::rawRequest('64ExecuteResetPC_filepath');
    $line6_64_patchDep = url::rawRequest('64ExecuteResetPC_patchDep');

    $line6_64 = createLineConfiguration(
        $append_64exec,
        $conf2_64,
        $line6_64_cmd,
        $typeoffile2,
        $filepathexist2,
        $line6_64_pexecprecheck,
        $line6_64_pfilepath,
        $line6_64_psoftname,
        $line6_64_pSoftVer,
        $line6_64_prootKey,
        $line6_64_pTypeDE,
        $line6_64_pValueDE,
        $line6_64_rootKey,
        $line6_64_validity,
        $line6_64_vTypeDE,
        $line6_64_vValueDE,
        $line6_64_filepath,
        $line6_64_patchDep,
        'PackagesConfiguration_64',
        'line6',
        $spid
    );

    $line2_32_ResetClient = '';
    $line2_64_ResetClient = '';
    $line3_32_ResetPC = '';
    $line3_64_ResetPC = '';

    $db = NanoDB::connect();

    if ($restartClient === 'yes') {
        if (url::rawRequest('32DeployResetClient_enable') === '1') {
            $line2_32_ResetClient .= $line3_32 . "\n";
        }
        if (url::rawRequest('32ExecuteResetClient_enable') === '1') {
            $line2_32_ResetClient .= $line4_32 . "\n";
        }
        if (url::rawRequest('64DeployResetClient_enable') === '1') {
            $line2_64_ResetClient .= $line3_64 . "\n";
        }

        if (url::rawRequest('64ExecuteResetClient_enable') === '1') {
            $line2_64_ResetClient .= $line4_64 . "\n";
        }
    }

    if ($restartPC === 'yes' && $restartClient === 'yes') {
        if (url::rawRequest('32DeployResetPC_enable') === '1') {
            $line3_32_ResetPC .= $line5_32 . "\n";
        }
        if (url::rawRequest('32ExecuteResetPC_enable') === '1') {
            $line3_32_ResetPC .= $line6_32 . "\n";
        }
        if (url::rawRequest('64DeployResetPC_enable') === '1') {
            $line3_64_ResetPC .= $line5_64 . "\n";
        }

        if (url::rawRequest('64ExecuteResetPC_enable') === '1') {
            $line3_64_ResetPC .= $line6_64 . "\n";
        }
    }

    if ($restartClient === 'no') {
        NanoDB::query("UPDATE " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_32 SET line3=?,line4=?,line5=?,line6=? WHERE packageId=?", ['', '', '', '', $spid]);
        NanoDB::query("UPDATE " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_64 SET line3=?,line4=?,line5=?,line6=? WHERE packageId=?", ['', '', '', '', $spid]);
        $line2_32_ResetClient = '';
        $line2_64_ResetClient = '';
        $line3_32_ResetPC = '';
        $line3_64_ResetPC = '';
    }

    $deploy_32 = url::rawRequest('32DeployMain_enable') === '1' && url::rawRequest('32ExecuteMain_enable') !== '1';
    $execute_32 = url::rawRequest('32DeployMain_enable') !== '1' && url::rawRequest('32ExecuteMain_enable') === '1';
    $depl_exec_32 = url::rawRequest('32DeployMain_enable') === '1' && url::rawRequest('32ExecuteMain_enable') === '1';
    $noone_32 = url::rawRequest('32DeployMain_enable') !== '1' && url::rawRequest('32ExecuteMain_enable') !== '1';

    $deploy_64 = url::rawRequest('64DeployMain_enable') === '1' && url::rawRequest('64ExecuteMain_enable') !== '1';
    $execute_64 = url::rawRequest('64DeployMain_enable') !== '1' && url::rawRequest('64ExecuteMain_enable') === '1';
    $depl_exec_64 = url::rawRequest('64DeployMain_enable') === '1' && url::rawRequest('64ExecuteMain_enable') === '1';
    $noone_64 = url::rawRequest('64DeployMain_enable') !== '1' && url::rawRequest('64ExecuteMain_enable') !== '1';


    if ($deploy_32) {
        $line1_32 = $line1_32 . "\n";
    } else if ($execute_32) {
        $line1_32 = $line2_32 . "\n";
    } else if ($depl_exec_32) {
        $line1_32 = $line1_32 . "\n";
        $line1_32 .= $line2_32 . "\n";
    } else if ($noone_32) {
        $line1_32 = '';
    }

    if ($deploy_64) {
        $line1_64 = $line1_64 . "\n";
    } else if ($execute_64) {
        $line1_64 = $line2_64 . "\n";
    } else if ($depl_exec_64) {
        $line1_64 = $line1_64 . "\n";
        $line1_64 .= $line2_64 . "\n";
    } else if ($noone_64) {
        $line1_64 = '';
    }

    $addConfStr_32 = $line1_32;
    $addConfStr_32 .= $line2_32_ResetClient;
    $addConfStr_32 .= $line3_32_ResetPC;

    $addConfStr_64 = $line1_64;
    $addConfStr_64 .= $line2_64_ResetClient;
    $addConfStr_64 .= $line3_64_ResetPC;

    $configClick = url::rawRequest('posKeywords');
    $configDobleClick = url::rawRequest('negKeywords');
    $logFileSave = url::rawRequest('logFilesToRead');
    $defaultRead = url::rawRequest('defaultRead');
    $deleteLogFile = (int)url::rawRequest('deleteLogFile');
    $msgDownload = url::rawRequest('preInstallMsg');
    $msgInstall = url::rawRequest('postDownloadMsg');
    $maxpatchtime = url::rawRequest('maxTime');
    $processtokill = url::rawRequest('processToKill');

    if ($msgDownload || $msgInstall) {
        $enableMessage = 1;
    } else {
        $enableMessage = 0;
    }
    $db = pdo_connect();
    $sqlch = "SELECT count(packageId) as count from " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_new WHERE packageId=?";
    $pdo = $db->prepare($sqlch);
    $pdo->execute([$spid]);
    $resch = $pdo->fetch(PDO::FETCH_ASSOC);

    //update data in the table "softinst.Packages"
    // $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.Packages SET packageName = ?, platform = ? WHERE id = ?";
    // $pdo = $db->prepare($updateSql);
    // $pdo->execute([$packageName,$os,$spid]);

    // updating urls 
    if ($url_32) {
        $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.Packages SET path = ? WHERE id = ?";
        $pdo = $db->prepare($updateSql);
        $pdo->execute([$url_32, $spid]);
    }
    if ($url_64) {
        $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.Packages SET path2 = ? WHERE id = ?";
        $pdo = $db->prepare($updateSql);
        $pdo->execute([$url_64, $spid]);
    }

    if ($resch && isset($resch['count']) && is_numeric($resch['count']) && intval($resch['count']) > 0) {
        $params = array_merge([$addConfStr_32, $addConfStr_64, $maxpatchtime, $enableMessage, $msgDownload, $msgInstall, $processtokill, $logFileSave, $defaultRead, (int)$deleteLogFile, $configClick, $configDobleClick, $spid]);
        $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_new SET 32bitConfig=?,64bitConfig=?,maxTime = ?,enableMessage = ?,preInstallMsg = ?,postDownloadMsg = ?,processToKill = ?,logFilesToRead = ?,defaultRead=?,deleteLogFile=?,posKeywords = ?,negKeywords = ? WHERE packageId=?";
        $pdo = $db->prepare($updateSql);
        $q2 = $pdo->execute($params);
    } else {
        $insertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_new (packageId,32bitConfig,64bitConfig,maxTime,enableMessage,preInstallMsg,postDownloadMsg,processToKill,logFilesToRead,defaultRead,deleteLogFile,posKeywords,negKeywords) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $pdo = $db->prepare($insertSql);
        $q3 = $pdo->execute([$spid, $addConfStr_32, $addConfStr_64, $maxpatchtime, $enableMessage, $msgDownload, $msgInstall, $processtokill, $logFileSave, $defaultRead, (int)$deleteLogFile, $configClick, $configDobleClick]);
    }
}

function getFtpCdnDataFn()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();
    $email = url::requestToText('email');
    $getConfigSql = "select ftp,ftpUrl,ftpauth,ftpUser,ftpPwd,cdn,cdnUrl,cdnAccessKey,cdnSecretKey,cdnBucketName,cdnRegion from " . $GLOBALS['PREFIX'] . "softinst.PackagesFtp where user=? order by id desc limit 1";
    $pdo = $db->prepare($getConfigSql);
    $pdo->execute([$email]);
    $getConfigRes = $pdo->fetch(PDO::FETCH_ASSOC);

    $data = array(
        "ftp" => $getConfigRes['ftp'],
        "ftpUrl" => $getConfigRes['ftpUrl'],
        "ftpauth" => $getConfigRes['ftpauth'],
        "ftpUser" => $getConfigRes['ftpUser'],
        "ftpPwd" => $getConfigRes['ftpPwd'],
        "cdn" => $getConfigRes['cdn'],
        "cdnUrl" => $getConfigRes['cdnUrl'],
        "cdnAccessKey" => $getConfigRes['cdnAccessKey'],
        "cdnSecretKey" => $getConfigRes['cdnSecretKey'],
        "cdnBucketName" => $getConfigRes['cdnBucketName'],
        "cdnRegion" => $getConfigRes['cdnRegion']
    );

    $jsonData = $data;
    print_json_data($jsonData);
}



function checkConfig()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();
    $user = $_SESSION['user']['adminEmail'];

    $sql = "select count(id) as total from " . $GLOBALS['PREFIX'] . "softinst.PackagesFtp where user=? limit 1";
    $pdo = $db->prepare($sql);
    $pdo->execute([$user]);
    $sqlres = $pdo->fetch(PDO::FETCH_ASSOC);
    $total = $sqlres['total'];
    return $total;
}

function checktoaddPatch()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();
    $user = $_SESSION['user']['adminEmail'];
    $sql = "select count(id) as total from " . $GLOBALS['PREFIX'] . "softinst.PackagesFtp where user=?";
    $pdo = $db->prepare($sql);
    $pdo->execute([$user]);
    $sqlres = $pdo->fetch(PDO::FETCH_ASSOC);
    $total = $sqlres['total'];
    print_data($total);
}

function saveftpconfig()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();
    $user = $_SESSION['user']['adminEmail'];
    $ftp = url::requestToText('ftp');
    $furl = url::requestToText('furl');
    $fauth = url::requestToText('fauth');
    $fuser = url::requestToText('fuser');
    $fpwd = url::requestToText('fpwd');

    $total = checkConfig();
    $inInsert = false;

    if ($total == 1) {
        $sql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.PackagesFtp SET ftp=?,ftpUrl=?,ftpauth=?,ftpUser=?,ftpPwd=? where user=?";
        $bindings = [$ftp, $furl, $fauth, $fuser, $fpwd, $user];
    } else {
        $inInsert = true;
        $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PackagesFtp(ftp, ftpUrl, ftpauth, ftpUser, ftpPwd, user) VALUES(?,?,?,?,?,?)";
        $bindings = [$ftp, $furl, $fauth, $fuser, $fpwd, $user];
    }

    $pdo = $db->prepare($sql);
    $sqlres = $pdo->execute($bindings);
    $message = '';

    if ($sqlres == true) {
        $auditRes = create_auditLog('Software Distribution', 'Save FTP Configuration', 'Success', $_REQUEST);

        $result = 'true';
        $message = $inInsert ? 'Successfully added ftp configuration' : 'Successfully updated ftp configuration';
    } else if ($sqlres == false) {
        $auditRes = create_auditLog('Software Distribution', 'Save FTP Configuration', 'Failed', $_REQUEST);

        $result = 'false';
    }
    $jsonData = array("data" => $result, 'message' => $message);
    print_json_data($jsonData);
}

function savecdnconfig()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();
    $user = $_SESSION['user']['adminEmail'];
    $cdn = url::requestToText('cdn');
    $cdnurl = url::requestToText('cdnurl');
    $cdnAk = url::requestToText('cdnAk');
    $cdnSk = url::requestToText('cdnSk');
    $bucket = url::requestToText('bucket');
    $region = url::requestToText('region');

    $total = checkConfig();
    $isInsert = false;

    if ($total == 1) {
        $sql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.PackagesFtp SET cdn=?,cdnUrl=?, cdnAccessKey=?, cdnSecretKey=?,cdnBucketName=?, cdnRegion=? where user=?";
        $bindings = [$cdn, $cdnurl, $cdnAk, $cdnSk, $bucket, $region, $user];
    } else {
        $isInsert = true;
        $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PackagesFtp(cdn, cdnUrl, cdnAccessKey, cdnSecretKey, cdnBucketName, cdnRegion, user) VALUES(?,?,?,?,?,?,?)";
        $bindings = [$cdn, $cdnurl, $cdnAk, $cdnSk, $bucket, $region, $user];
    }

    $pdo = $db->prepare($sql);
    $sqlres = $pdo->execute($bindings);
    $message = '';

    if ($sqlres == true) {
        $auditRes = create_auditLog('Software Distribution', 'Save CDN Configuration', 'Success', $_REQUEST);

        $result = 'true';
        $message = $isInsert ? 'Successfully added cdn configuration' : 'Successfully updated cdn configuration';
    } else if ($sqlres == false) {
        $auditRes = create_auditLog('Software Distribution', 'Save CDN Configuration', 'Failed', $_REQUEST);

        $result = 'false';
    }

    $jsonData = array("data" => $result, 'message' => $message);
    print_json_data($jsonData);
}



function distributeConfigurationFn()
{
    $priviledge = checkModulePrivilege('distributesoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }


    $selected = (int)url::requestToText('id');

    $db = pdo_connect();

    $Dsql = "SELECT id,packageName,fileName,sourceType,path,platform,isConfigured FROM " . $GLOBALS['PREFIX'] . "softinst.Packages WHERE id=?";
    $pdo = $db->prepare($Dsql);
    $pdo->execute([$selected]);
    $sqlDres = $pdo->fetch(PDO::FETCH_ASSOC);
    $packageIdD = $sqlDres['id'];
    $packageNameD = $sqlDres['packageName'];
    $platformD = $sqlDres['platform'];
    $fileNameD = $sqlDres['path'];
    $sourceTypeD = $sqlDres['sourceType'];
    $configStatD = $sqlDres['isConfigured'];

    $sqlDo = "SELECT cmdLines, enableMessage, messageText, executePath, validation, session, runas, rootKey, subKey, validationFilePath, checkPreInstall, pValidationFilePath, softwareName, softwareVersion, knowledgeBase, servicePack, pRootKey, pSubKey, peerdistribution, pExecPreCheckVal, pRegName, pType, pValue, vRegName, vType, vValue FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration where packageId=?";
    $pdo = $db->prepare($sqlDo);
    $pdo->execute([$selected]);
    $sqlDoRes = $pdo->fetch(PDO::FETCH_ASSOC);

    $enableMessageD = $sqlDoRes['enableMessage'];
    $commandLinesD = $sqlDoRes['cmdLines'];
    $executePathD = $sqlDoRes['executePath'];
    $filePathD = $sqlDoRes['pValidationFilePath'];
    $softwareNameD = $sqlDoRes['softwareName'];
    $softwareVersionD = $sqlDoRes['softwareVersion'];
    $knowledgeBaseD = $sqlDoRes['knowledgeBase'];
    $knowledgeBaseDD = str_replace("&&", ",", $knowledgeBaseD);
    $servicePackD = $sqlDoRes['servicePack'];
    $rootKeyD = $sqlDoRes['rootKey'];
    $subKeyD = $sqlDoRes['subKey'];
    $messageTextD = $sqlDoRes['messageText'];
    $validationD = $sqlDoRes['validation'];
    $validationFilePathD = $sqlDoRes['validationFilePath'];
    $preInstall = $sqlDoRes['checkPreInstall'];
    $preInstallD = (string)$preInstall;
    $preInstallDD = trim($preInstall);
    $prootKeyD = $sqlDoRes['pRootKey'];
    $psubKeyD = $sqlDoRes['pSubKey'];
    $vRegName = $sqlDoRes['vRegName'];
    $vType = $sqlDoRes['vType'];
    $vValue = $sqlDoRes['vValue'];
    $pRegName = $sqlDoRes['pRegName'];
    $pType = $sqlDoRes['pType'];
    $pValue = $sqlDoRes['pValue'];
    $pExecPreCheckVal = $sqlDoRes['pExecPreCheckVal'];
    $peerdistribution = $sqlDoRes['peerdistribution'];
    $runasD = $sqlDoRes['runas'];
    $sessionD = $sqlDoRes['session'];

    if ($softwareNameD == 'NA')
        $softwareNameD = '';
    if ($commandLinesD == 'NA')
        $commandLinesD = '';


    $data = array(
        "packageIdD" => "$packageIdD",
        "packageNameD" => "$packageNameD",
        "platformD" => "$platformD",
        "sourceTypeD" => "$sourceTypeD",
        "fileNameD" => "$fileNameD",
        "configStatD" => "$configStatD",
        "executePathD" => "$executePathD",
        "sessionD" => "$sessionD",
        "runasD" => "$runasD",
        "cmdSettingD" => "$cmdSettingD",
        "cmdLinesD" => "$commandLinesD",
        "validationD" => "$validationD",
        "enableMessageD" => "$enableMessageD",
        "messageTextD" => "$messageTextD",
        "preInstallD" => "$preInstallD",
        "preInstallDD" => "$preInstallDD",
        "filePathD" => "$filePathD",
        "softwareNameD" => "$softwareNameD",
        "softwareVersionD" => "$softwareVersionD",
        "knowledgeBaseDD" => "$knowledgeBaseDD",
        "validationFilePathD" => "$validationFilePathD",
        "servicePackD" => "$servicePackD",
        "rootKeyD" => "$rootKeyD",
        "subKeyD" => "$subKeyD",
        "prootKeyD" => "$prootKeyD",
        "psubKeyD" => "$psubKeyD",
        "vRegName" => "$vRegName",
        "vType" => "$vType",
        "vValue" => "$vValue",
        "pRegName" => "$pRegName",
        "pType" => "$pType",
        "pValue" => "$pValue",
        "pExecPreCheckVal" => "$pExecPreCheckVal",
        "peerdistribution" => "$peerdistribution"
    );

    $jsonData = array("data" => $data);
    print_json_data($jsonData);
}



function configPatchFn()
{

    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $selected = (int)url::requestToText('id');
    $db = pdo_connect();

    $Csql = "SELECT id,packageName,fileName,sourceType,path,platform,isConfigured FROM " . $GLOBALS['PREFIX'] . "softinst.Packages WHERE id=?";
    $pdo = $db->prepare($Csql);
    $pdo->execute([$selected]);
    $Csqlres = $pdo->fetch(PDO::FETCH_ASSOC);

    $packageIdC = $Csqlres['id'];
    $packageNameC = $Csqlres['packageName'];
    $platformC = $Csqlres['platform'];
    $sourceTypeC = $Csqlres['sourceType'];
    $fileNameC = $Csqlres['path'];
    $configStatC = $Csqlres['isConfigured'];

    $sqlCo = "SELECT 32bitConfig, 64bitConfig, session, runas, cmdLineSetting, cmdLines, posKeywords, negKeywords, logFilesToRead, defaultRead, deleteLogFile, enableMessage, messageText, maxTime, processToKill, checkPreInstall, pValidationFilePath, softwareName, softwareVersion, knowledgeBase, servicePack, pRootKey, pSubKey, peerdistribution, pExecPreCheckVal, pRegName, pType, pValue FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration where packageId=?";
    $pdo = $db->prepare($sqlCo);
    $pdo->execute([$selected]);
    $sqlCoRes = $pdo->fetch(PDO::FETCH_ASSOC);

    $bit32C = $sqlCoRes['32bitConfig'];
    $bit64C = $sqlCoRes['64bitConfig'];
    $sessionC = $sqlCoRes['session'];
    $runasC = $sqlCoRes['runas'];
    $cmdSetting = $sqlCoRes['cmdLineSetting'];
    $cmdSettingC = (string)$cmdSetting;
    $cmdLinesC = $sqlCoRes['cmdLines'];
    $posKeywordsC = $sqlCoRes['posKeywords'];
    $negKeywordsC = $sqlCoRes['negKeywords'];
    $logFilesToRead = $sqlCoRes['logFilesToRead'];

    if (isset($logFilesToRead)) {
        $logFilesToReadC = $logFilesToRead;
    }

    $defaultRead = $sqlCoRes['defaultRead'];

    if (isset($defaultRead)) {
        $defaultReadC = htmlspecialchars($defaultRead);
    } else {
        $defaultReadC = "default,*.txt";
    }
    $deleteLogFile = $sqlCoRes['deleteLogFile'];
    $deleteLogFileC = (string)$deleteLogFile;
    $enableMessage = $sqlCoRes['enableMessage'];
    $enableMessageC = (string)$enableMessage;
    $messageTextC = $sqlCoRes['messageText'];
    $maxTimeC = $sqlCoRes['maxTime'];
    $processToKillC = $sqlCoRes['processToKill'];

    $preInstall = $sqlCoRes['checkPreInstall'];
    $preInstallC = (string)$preInstall;
    $preInstallCC = trim($preInstall);
    $filePathC = $sqlCoRes['pValidationFilePath'];
    $softwareNameC = $sqlCoRes['softwareName'];
    $softwareVersionC = $sqlCoRes['softwareVersion'];
    $knowledgeBaseC = $sqlCoRes['knowledgeBase'];
    $knowledgeBaseCC = str_replace("&&", ",", $knowledgeBaseC);
    $servicePackC = $sqlCoRes['servicePack'];
    $rootKeyC = $sqlCoRes['pRootKey'];
    $subKeyC = $sqlCoRes['pSubKey'];
    $pRegName = $sqlCoRes['pRegName'];
    $pType = $sqlCoRes['pType'];
    $pValue = $sqlCoRes['pValue'];
    $pExecPreCheckVal = $sqlCoRes['pExecPreCheckVal'];
    $peerdist = $sqlCoRes['peerdistribution'];

    $data = array(
        "packageIdC" => "$packageIdC",
        "packageNameC" => "$packageNameC",
        "platformC" => "$platformC",
        "sourceTypeC" => "$sourceTypeC",
        "fileNameC" => "$fileNameC",
        "configStatC" => "$configStatC",
        "bit32C" => "$bit32C",
        "bit64C" => "$bit64C",
        "sessionC" => "$sessionC",
        "runasC" => "$runasC",
        "cmdSettingC" => "$cmdSettingC",
        "cmdLinesC" => "$cmdLinesC",
        "posKeywordsC" => "$posKeywordsC",
        "negKeywordsC" => "$negKeywordsC",
        "logFilesToReadC" => "$logFilesToReadC",
        "defaultReadC" => "$defaultReadC",
        "deleteLogFileC" => "$deleteLogFileC",
        "enableMessageC" => "$enableMessageC",
        "messageTextC" => "$messageTextC",
        "maxTimeC" => "$maxTimeC",
        "processToKillC" => "$processToKillC",
        "preInstallC" => "$preInstallC",
        "preInstallCC" => "$preInstallCC",
        "filePathC" => "$filePathC",
        "softwareNameC" => "$softwareNameC",
        "softwareVersionC" => "$softwareVersionC",
        "knowledgeBaseCC" => "$knowledgeBaseCC",
        "servicePackC" => "$servicePackC",
        "rootKeyC" => "$rootKeyC",
        "subKeyC" => "$subKeyC",
        "pRegName" => "$pRegName",
        "pType" => "$pType",
        "pValue" => "$pValue",
        "pExecPreCheckVal" => "$pExecPreCheckVal",
        "peerdistribution" => "$peerdist"
    );

    $jsonData = array("data" => $data);
    print_json_data($jsonData);
}

function MACconfigPatchFn()
{
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $selected = (int)url::requestToText('id');

    $db = pdo_connect();

    $Csql = "SELECT packageName,sourceType,platform,isConfigured FROM " . $GLOBALS['PREFIX'] . "softinst.Packages WHERE id=?";
    $pdo = $db->prepare($Csql);
    $pdo->execute([$selected]);
    $Csqlres = $pdo->fetch(PDO::FETCH_ASSOC);

    $packageNameC = $Csqlres['packageName'];
    $platformC = $Csqlres['platform'];
    $sourceTypeC = $Csqlres['sourceType'];
    $isConfigured = $Csqlres['isConfigured'];

    $sqlCo = "SELECT executePath, validation, validationFilePath, checkPreInstall, pValidationFilePath, softwareName, softwareVersion FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration where packageId=?";
    $pdo = $db->prepare($sqlCo);
    $pdo->execute([$selected]);
    $sqlres = $pdo->fetch(PDO::FETCH_ASSOC);

    $executePath = $sqlres['executePath'];
    $preInstall = $sqlres['checkPreInstall'];
    $filePath = $sqlres['pValidationFilePath'];
    $softwareName = $sqlres['softwareName'];
    $softwareVersion = $sqlres['softwareVersion'];
    $validation = $sqlres['validation'];
    $validationFilePath = $sqlres['validationFilePath'];

    if ($softwareName == 'NA')
        $softwareName = '';

    $data = array(
        "packageName" => "$packageNameC",
        "isConfigured" => "$isConfigured",
        "platform" => "$platformC",
        "sourceType" => "$sourceTypeC",
        "pathorurl" => "$executePath",
        "validation" => "$validation",
        "validationFilePath" => "$validationFilePath",
        "preInstall" => "$preInstall",
        "pValidationFilePath" => "$filePath",
        "softwareName" => "$softwareName",
        "softwareVersion" => "$softwareVersion"
    );

    $jsonData = array("data" => $data);
    print_json_data($jsonData);
}

function MACconfigPatchSubmitFn()
{
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $spid = (int)url::requestToText('id');

    $db = pdo_connect();
    $sqlp = "SELECT packageName,sourceType,platform,isConfigured,androidIcon as urlpath,fileName,path FROM " . $GLOBALS['PREFIX'] . "softinst.Packages WHERE id=?";
    $pdo = $db->prepare($sqlp);
    $pdo->execute([$spid]);
    $sqlresp = $pdo->fetch(PDO::FETCH_ASSOC);

    $sourceType = $sqlresp['sourceType'];
    $configStat = $sqlresp['isConfigured'];
    $packName = $sqlresp['packageName'];
    $packNameMac = "," . $sqlresp['packageName'];
    $macurlpath = $sqlresp['urlpath'];
    $macfileName = $sqlresp['fileName'];
    $macpath = $sqlresp['path'];
    $platform = $sqlresp['platform'];
    $plat = ($platform == 'mac') ? 'MAC' : 'LINUX';
    $ftype1 = "0";
    $ftype2 = "0";
    $dmg = "0";

    if ($sourceType == 2) {
        $isConf = 'NA';
        $validityCheck = url::requestToText('validityCheckMC');
        $validity = url::requestToText('validityMC');
        $pathUrl = url::requestToText('pathExecMC');

        if ($validity != "") {
            if ($validity == 0) {
                $filePath = url::requestToText('filePathMC');
                $vSoftName = '';
                $vSoftVer = '';
                if ($filePath != "") {
                    $validityStr = "," . $validity . '#' . $filePath;
                } else {
                    $validityStr = "";
                }
            } else if ($validity == 1) {
                $validityStr = ",1#";
                $filePath = url::requestToText('vSoftNameMC');
                $vSoftName = url::requestToText('vSoftNameMC');
                $vSoftVer_m = $vSoftVer;
                if ($vSoftName == '') {
                    $vSoftName = 'NA';
                }
                $validityStr .= "$vSoftName";
            }
        } else {
            $validity = '';
            $filePath = '';
            $validityStr = '';
        }

        $preInsCheck = url::requestToText('preinstcheckMC');

        if ($preInsCheck != '') {
            if ($preInsCheck == '0') {
                $pfilePath = url::requestToText('pfilePathMC');
                $pSoftName = '';
                $pSoftVer = '';
                if ($pfilePath != "") {
                    $preinstallCheck = ",0,$pfilePath";
                } else {
                    $preinstallCheck = "";
                }
            } else if ($preInsCheck == 1) {
                $preinstallCheck = ",1,";
                $pfilePath = '';
                $pSoftName = url::requestToText('pSoftNameMC');
                $pSoftVer = url::requestToText('pSoftVerMC');
                if ($pSoftVer == '') {
                    $pSoftVer_m = 'NA';
                    $preinstallCheck .= "$pSoftName";
                } else {
                    $pSoftVer_m = $pSoftVer;
                    if ($pSoftName == '') {
                        $pSoftName = 'NA';
                    }
                    $preinstallCheck .= "$pSoftName#$pSoftVer_m";
                }
            }
        } else {
            $pfilePath = '';
            $pSoftName = '';
            $pSoftVer = '';
            $preinstallCheck = ',NA';
        }

        if ($filePath == "" && $pSoftVer == "" && $pfilePath == "" && ($pSoftName == "" || $pSoftName == "NA") && ($vSoftName == "" || $vSoftName == "NA")) {
            $ftype2 = $packNameMac = $preinstallCheck = $validityStr = "";
        }

        $confStr = "1,$plat,$pathUrl,0,0,$ftype1,$isConf$preinstallCheck$validityStr";

        $sql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.Packages SET addConfigDetail=?, isConfigured='2' where id=?";
        $pdo = $db->prepare($sql);
        $sqlres = $pdo->execute([$confStr, $spid]);
    } else if ($sourceType == 3) {
        $validityCheck = $validity = $filePath = '';
        $pathUrl = url::requestToText('pathUrlMC');
        $preInsCheck = url::requestToText('preinstcheckMC');
        $validity = url::requestToText('validityMC');
        $macpath = 'NA';

        if ($validity != "") {
            if ($validity == 0) {
                $filePath = url::requestToText('filePathMC');
                $vSoftName = '';
                $vSoftVer = '';
                if ($filePath != "") {
                    $validityStr = "," . $validity . '#' . $filePath;
                } else {
                    $validityStr = "";
                }
            } else if ($validity == 1) {
                $validityStr = ",1#";
                $filePath = url::requestToText('vSoftNameMC');
                $vSoftName = url::requestToText('vSoftNameMC');
                $vSoftVer_m = $vSoftVer;
                if ($vSoftName == '') {
                    $vSoftName = 'NA';
                }
                $validityStr .= "$vSoftName";
            }
        } else {
            $validity = '';
            $filePath = '';
            $validityStr = '';
        }

        $preInsCheck = url::requestToText('preinstcheckMC');

        if ($preInsCheck != '') {
            if ($preInsCheck == '0') {
                $pfilePath = url::requestToText('pfilePathMC');
                $pSoftName = '';
                $pSoftVer = '';
                if ($pfilePath != "") {
                    $preinstallCheck = ",0,$pfilePath";
                } else {
                    $preinstallCheck = "";
                }
            } else if ($preInsCheck == 1) {
                $preinstallCheck = ",1,";
                $pfilePath = '';
                $pSoftName = url::requestToText('pSoftNameMC');
                $pSoftVer = url::requestToText('pSoftVerMC');
                if ($pSoftVer == '') {
                    $pSoftVer_m = 'NA';
                    $preinstallCheck .= "$pSoftName";
                } else {
                    $pSoftVer_m = $pSoftVer;
                    if ($pSoftName == '') {
                        $pSoftName = 'NA';
                    }
                    $preinstallCheck .= "$pSoftName#$pSoftVer_m";
                }
            }
        } else {
            $pfilePath = '';
            $pSoftName = '';
            $pSoftVer = '';
            $preinstallCheck = ',NA';
        }

        if ($sourceType == "3") {
            $ftype1 = "0";
            if ($pSoftVer == "" && $pfilePath == "" && $filePath == "" && ($pSoftName == "" || $pSoftName == "NA") && ($vSoftName == "" || $vSoftName == "NA")) {
                $ftype2 = $packNameMac = $preinstallCheck = $validityStr = "";
            }
            $confStr = "1,$plat,$pathUrl,0,0,$ftype1,$macpath$preinstallCheck$validityStr";
        } else if ($dmg == 1) {
            if ($pSoftVer == "" && $pfilePath == "" && ($pSoftName == "" || $pSoftName == "NA")) {
                $ftype2 = $packNameMac = $preinstallCheck = $validityStr = "";
            }
            $confStr = "1,$plat,$pathUrl,0,0,$ftype1,$macpath$ftype2$packNameMac$preinstallCheck";
        } else {
            $confStr = "1,$plat,$pathUrl,1,1,$ftype1,$macpath,$ftype2,$macpath$macfileName$preinstallCheck";
        }

        $sql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.Packages SET addConfigDetail=?, isConfigured='2' where id=?";
        $pdo = $db->prepare($sql);
        $sqlres = $pdo->execute([$confStr, $spid]);
    }

    $sqlcount = "select count(packageId) as count from " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration where packageId=?";
    $pdo = $db->prepare($sqlcount);
    $pdo->execute([$spid]);
    $rescount = $pdo->fetch(PDO::FETCH_ASSOC);
    $count = $rescount['count'];


    if ($count > 0) {
        $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration SET executePath=?, validation=?, validationFilePath=? , checkPreInstall=?, pValidationFilePath=?, softwareName=?, softwareVersion=? WHERE packageId=?";
        $pdo = $db->prepare($updateSql);
        $result = $pdo->execute([$pathUrl, $validity, $filePath, $preInsCheck, $pfilePath, $pSoftName, $pSoftVer, $spid]);
    } else {
        $insertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration (packageId,executePath, validation, validationFilePath, checkPreInstall, pValidationFilePath, softwareName, softwareVersion) VALUES (?,?,?,?,?,?,?,?)";
        $pdo = $db->prepare($insertSql);
        $result = $pdo->execute([$spid, $pathUrl, $validity, $filePath, $preInsCheck, $pfilePath, $pSoftName, $pSoftVer]);
    }
    if ($result) {
        echo "success";
    }
}



function getConfigFn()
{
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $packName = url::requestToText('packNamed');
    $spid = url::requestToText('sele');
    $configStat = url::requestToText('configStat');
    $bit32 = url::requestToText('bit32');
    $bit64 = url::requestToText('bit64');
    $session = url::requestToText('session');
    $runas = url::requestToText('runas');
    $cmdsetting = url::requestToText('cls');
    $cmdLine = url::requestToText('cmdLine');
    $posKey = url::requestToText('pposKey');
    $negKey = url::requestToText('pnegKey');
    $logfiles = url::requestToText('logfiles');
    $defread = url::requestToText('defaultRead');
    $deletelog = url::issetInRequest('deletelog') && in_array(url::requestToAny('deletelog'), ["0", "1"]) ? url::requestToAny('deletelog') : '0';
    $enablemsg = url::requestToText('enablemsg');
    $msgtext = url::requestToText('msgtext');
    $maxtime = url::requestToText('maxtime');
    $processkill = url::requestToText('pprocesskill');
    $peerdist = "";
    $pExecPreCheckVal = 1;
    $pRegName = null;
    $pType = null;
    $pValue = null;

    $peer = "";
    if ($peerdist == "1" || $peerdist == 1) {
        $peer = ",NA,NA,NA,NA,NA,1";
    }

    $preinst = url::requestToText('ppreinstcheck');

    if ($cmdsetting != 1) {
        $cmdsetting = 0;
    }

    if ($enablemsg != 1) {
        $enablemsg = 0;
    }

    if (url::requestToAny('logfiles') != '') {
        $logFileTxt = '';
        $logFilesArr = explode_user_def(",", url::requestToAny('logfiles'));
        foreach ($logFilesArr as $key => $value) {
            $logFileTxt .= "1,0," . strip_tags($value) . "\n";
        }

        $logFileTxt = rtrim($logFileTxt);
    } else {
        $logFileTxt = '';
    }

    if ($cmdLine == '') {
        $cmdLine_append = 'NA';
    } else {
        $cmdLine_append = $cmdLine;
    }

    if ($msgtext == '') {
        $msgtext_app = $msgtext;
    } else {
        $msgtext_app = '[1]-' . $msgtext;
    }

    if ($preinst != '') {

        $pfilePath = url::requestToText('ppfilePath');
        $pSoftName = url::requestToText('ppSoftName');
        $pSoftVer = url::requestToText('ppSoftVer');
        $pKb = url::requestToText('ppKb');
        $pServicePack = url::requestToText('ppServicePack');
        $rootKey = url::requestToText('prootKey');
        $subKey = url::requestToText('psubKey');
        $pExecPreCheckVal = url::requestToText('pExecPreCheckValCP');
        $pRegName = url::requestToText('pRegNameCP');
        $pType = url::requestToText('pTypeCP');
        $pValue = url::requestToText('pValueCP');

        if ($pExecPreCheckVal == 0) {
            if ($preinst == '1') {
                $notVal = "";
            } else {
                $notVal = "!";
            }
        } else {
            $notVal = "";
        }

        $pKb = str_replace(",", "&&", $pKb);

        if ($pSoftVer == '') {
            $pSoftVer_m = 'NA';
        } else {
            $pSoftVer_m = $pSoftVer;
        }
        if ($pKb == '') {
            $pKb_m = 'NA';
        } else {
            $pKb_m = $pKb;
        }
        if ($pServicePack == '') {
            $pServicePack_m = 'NA';
        } else {
            $pServicePack_m = $pServicePack;
        }

        if ($preinst == 0) {
            $preinstallCheck = "," . $notVal . "0,$pfilePath";
        } else if ($preinst == 1) {
            $preinstallCheck = "," . $notVal . "1,";
            if ($pSoftVer_m == 'NA' && $pKb_m == 'NA' && $pServicePack_m == 'NA') {
                $preinstallCheck .= "$pSoftName";
            } else if ($pKb_m == 'NA' && $pServicePack_m == 'NA') {
                if ($pSoftName == '') {
                    $pSoftName = 'NA';
                }
                $preinstallCheck .= "$pSoftName#$pSoftVer_m";
            } else if ($pServicePack_m == 'NA') {
                if ($pSoftName == '') {
                    $pSoftName = 'NA';
                }
                $preinstallCheck .= "$pSoftName#$pSoftVer_m#$pKb_m";
            } else {
                if ($pSoftName == '') {
                    $pSoftName = 'NA';
                }
                $preinstallCheck .= "$pSoftName#$pSoftVer_m#$pKb_m#$pServicePack_m";
            }
        } else if ($preinst == 2) {
            $preinstallCheck = "," . $notVal . "2,2,$rootKey#$subKey#$pRegName#$pType#$pValue";
            if ($rootKey == '') {
                $preinstallCheck = "," . $notVal . "2,2,NA#$subKey#$pRegName#$pType#$pValue";
            }
            if ($subKey == '') {
                $preinstallCheck = "," . $notVal . "2,2,$rootKey#$pRegName#$pType#$pValue";
            }
        } else if ($preinst == 3) {
            $preinstallCheck = "," . $notVal . "2,1,$pSoftName";
        } else {
            $preinstallCheck = "";
        }
    } else {

        $preinstallCheck = "";
    }

    $confStr = "1~[$packName]" . "\n";
    $confStr .= "32Link:\n1,NT,$bit32,$session,$runas,$cmdsetting,$cmdLine_append$preinstallCheck" . "\n";
    $confStr .= "64Link:\n1,NT,$bit64,$session,$runas,$cmdsetting,$cmdLine_append$preinstallCheck" . "\n";
    $confStr .= "Positive:$posKey" . "\n";
    $confStr .= "Negative:$negKey" . "\n";
    $confStr .= "Special:" . "\n";
    $confStr .= "LogFile:$logFileTxt" . "\n";
    $confStr .= "Default:$defread" . "\n";
    $confStr .= "DeleteLogFile:$deletelog" . "\n";
    $confStr .= "StatusMessageBox:$enablemsg" . "\n";
    $confStr .= "MessageBoxText:$msgtext_app" . "\n";
    $confStr .= "MaxTimePerPatch:$maxtime" . "\n";
    $confStr .= "ProcessToKill:$processkill";

    if ($peerdist != "") {
        $peerdist = $peerdist;
    } else {
        $peerdist = 0;
    }

    $db = pdo_connect();

    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.Packages SET configDetail=?, isConfigured='1' where id=?";
    $pdo = $db->prepare($sql);
    $q1 = $pdo->execute([$confStr, $spid]);

    if ($configStat == '1' || $configStat == '2' || $configStat == '3') {

        $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration SET 32bitConfig=?, 64bitConfig=?, session=?, runas=?, cmdLineSetting=?, cmdLines=?, posKeywords=?, negKeywords=?, logFilesToRead=?, defaultRead=?, deleteLogFile=?, enableMessage=?, messageText=?, maxTime=?, processToKill=?, checkPreInstall=?, pValidationFilePath=?, softwareName=?, softwareVersion=?, knowledgeBase=?, servicePack=?, pRootKey=?, pSubKey=?, peerdistribution=?, pExecPreCheckVal=?, pRegName=?, pType=?, pValue=? WHERE packageId=?";
        $pdo = $db->prepare($updateSql);
        $q2 = $pdo->execute([$bit32, $bit64, $session, $runas, $cmdsetting, $cmdLine, $posKey, $negKey, $logfiles, $defread, (int)$deletelog, $enablemsg, $msgtext, $maxtime, $processkill, $preinst, $pfilePath, $pSoftName, $pSoftVer, $pKb, $pServicePack, $rootKey, $subKey, $peerdist, $pExecPreCheckVal, $pRegName, $pType, $pValue, $spid]);
    } else {

        $insertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration (packageId, 32bitConfig, 64bitConfig, session, runas, cmdLineSetting, cmdLines, posKeywords,negKeywords, logFilesToRead, defaultRead, deleteLogFile, enableMessage, messageText, maxTime, processToKill, checkPreInstall,pValidationFilePath,softwareName, softwareVersion, knowledgeBase, servicePack, pRootKey, pSubKey, peerdistribution,pExecPreCheckVal,pRegName,pType,pValue) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?)";
        $pdo = $db->prepare($insertSql);
        $q3 = $pdo->execute([$spid, $bit32, $bit64, $session, $runas, $cmdsetting, $cmdLine, $posKey, $negKey, $logfiles, $defread, (int)$deletelog, $enablemsg, $msgtext, $maxtime, $processkill, $preinst, $pfilePath, $pSoftName, $pSoftVer, $pKb, $pServicePack, $rootKey, $subKey, $peerdist, $pExecPreCheckVal, $pRegName, $pType, $pValue]);
    }
}



function getStatus()
{
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();

    $id = (int)url::requestToAny('id');
    $sql = "select distrubute,platform,sourceType from " . $GLOBALS['PREFIX'] . "softinst.Packages where id=?";
    $pdo = $db->prepare($sql);
    $pdo->execute([$id]);
    $sqlres = $pdo->fetch(PDO::FETCH_ASSOC);

    $distrubute = $sqlres['distrubute'];
    $platform = $sqlres['platform'];
    $sourceType = $sqlres['sourceType'];

    if (($platform == 'mac' || $platform == 'linux') && $sourceType == '2') {
        $echo = "";
        $getStatus = array('getStatus' => $echo);
        $jsonData = array("data" => $getStatus);
        print_json_data($jsonData);
        exit;
    } else if ($platform == 'android' && $distribute == 1) {
        $echo = "";
        $getStatus = array('getStatus' => $echo);
        $jsonData = array("data" => $getStatus);
        print_json_data($jsonData);
        exit;
    } else if ($distrubute == 0) {
        $echo = "ND";
        $getStatus = array('getStatus' => $echo);
        $jsonData = array("data" => $getStatus);
        print_json_data($jsonData);
        exit;
    } else {
        $echo = "";
        $getStatus = array('getStatus' => $echo);
        $jsonData = array("data" => $getStatus);
        print_json_data($jsonData);
        exit;
    }
}

function getDeployExecuteAvailability()
{
    $id = (int)url::requestToInt('id');

    $db = pdo_connect();
    $sql = "select line1, line2, line3, line4, line5, line6 from " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_32 where packageId=?";
    $pdo = $db->prepare($sql);
    $pdo->execute([$id]);
    $sqlres32 = $pdo->fetch(PDO::FETCH_ASSOC);

    $sql = "select line1, line2, line3, line4, line5, line6 from " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_64 where packageId=?";
    $pdo = $db->prepare($sql);
    $pdo->execute([$id]);
    $sqlres64 = $pdo->fetch(PDO::FETCH_ASSOC);

    $resp = [
        'deploy' => false,
        'execute' => false,
    ];

    if (
        json_decode($sqlres32['line1'])->line1_enable == 1 ||
        json_decode($sqlres32['line3'])->line3_enable == 1 ||
        json_decode($sqlres32['line5'])->line5_enable == 1 ||
        json_decode($sqlres64['line1'])->line1_enable == 1 ||
        json_decode($sqlres64['line3'])->line3_enable == 1 ||
        json_decode($sqlres64['line5'])->line5_enable == 1
    ) {
        $resp['deploy'] = true;
    }

    if (
        json_decode($sqlres32['line2'])->line2_enable == 1 ||
        json_decode($sqlres32['line4'])->line4_enable == 1 ||
        json_decode($sqlres32['line6'])->line6_enable == 1 ||
        json_decode($sqlres64['line2'])->line2_enable == 1 ||
        json_decode($sqlres64['line4'])->line4_enable == 1 ||
        json_decode($sqlres64['line6'])->line6_enable == 1
    ) {
        $resp['execute'] = true;
    }

    echo json_encode($resp);
}
function getexecuteStatus()
{
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();

    $id = (int)url::requestToAny('id');
    $sql = "select sourceType,platform,distrubute,isConfigured from " . $GLOBALS['PREFIX'] . "softinst.Packages where id=?";
    $pdo = $db->prepare($sql);
    $pdo->execute([$id]);
    $sqlres = $pdo->fetch(PDO::FETCH_ASSOC);

    $distribute = $sqlres['distrubute'];
    $isConfig = $sqlres['isConfigured'];
    $platform = $sqlres['platform'];

    if ($platform == 'android' && $isConfig == 1) {
        $echo = "";
        $getexecuteStatus = array('getexecuteStatus' => $echo);
        $jsonData = array("data" => $getexecuteStatus);
        print_json_data($jsonData);
        exit;
    } else if (is_numeric($distribute) && intval($distribute) == 1 && is_numeric($isConfig) && intval($isConfig) == 2) {
        $echo = "";
        $getexecuteStatus = array('getexecuteStatus' => $echo);
        $jsonData = array("data" => $getexecuteStatus);
        print_json_data($jsonData);
        exit;
    } else if (is_numeric($distribute) && intval($distribute) == 0 && is_numeric($isConfig) && intval($isConfig) == 1) {
        $echo = "";
        $getexecuteStatus = array('getexecuteStatus' => $echo);
        $jsonData = array("data" => $getexecuteStatus);
        print_json_data($jsonData);
        exit;
    } else if (($platform == 'mac' || $platform == 'linux') && is_numeric($isConfig) && intval($isConfig) == 2) {
        $echo = "";
        $getexecuteStatus = array('getexecuteStatus' => $echo);
        $jsonData = array("data" => $getexecuteStatus);
        print_json_data($jsonData);
        exit;
    } else {
        $echo = "NE";
        $getexecuteStatus = array('getexecuteStatus' => $echo);
        $jsonData = array("data" => $getexecuteStatus);
        print_json_data($jsonData);
        exit;
    }
}

function getConfigDistribute_old()
{
    $db = pdo_connect();
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();

    $id = (int)url::requestToText('id');
    $sql1 = "SELECT configDetail from " . $GLOBALS['PREFIX'] . "softinst.Packages where id=?";
    $pdo = $db->prepare($sql1);
    $pdo->execute([$id]);
    $sqlres1 = $pdo->fetch(PDO::FETCH_ASSOC);
    $confStr = $sqlres1['configDetail'];
    if ($confStr == '') {
        $sql = "select platform,type,sourceType,protocol,packageDesc,path,fileName,packageName,distrubute,distributionPath,distributionTime,distributionVpath,isConfigured,configDetail,addConfigDetail,distributionConfigDetail from " . $GLOBALS['PREFIX'] . "softinst.Packages where id=?";
        $pdo = $db->prepare($sql);
        $pdo->execute([$id]);
        $sqlres = $pdo->fetch(PDO::FETCH_ASSOC);

        $sql1 = "SELECT cmdLines, enableMessage, messageText, executePath, validation, session, runas, rootKey, subKey, validationFilePath, checkPreInstall, pValidationFilePath, softwareName, softwareVersion, knowledgeBase, servicePack, pRootKey, pSubKey FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration where packageId=?";
        $pdo = $db->prepare($sql1);
        $pdo->execute([$id]);
        $sqlres1 = $pdo->fetch(PDO::FETCH_ASSOC);

        $platform = $sqlres['platform'];
        $packageName = $sqlres['packageName'];
        $addconfig = $sqlres['addConfigDetail'];
        $macconfig = $sqlres['configDetail'];
        $maxTime = $sqlres['distributionTime'];
        $distributionPath = $sqlres['distributionPath'];
        $validationPath = $sqlres['distributionVpath'];
        $enablemsg = $sqlres1['enableMessage'];
        $msgtext = $sqlres1['messageText'];
        $linuxConf = $sqlres['configDetail'];

        $confStr = "1~[$packageName]" . "\n";
        $confStr .= "32Link:\n";
        $confStr .= "$addconfig" . "\n";

        $distrubute = $sqlres['distrubute'];

        $filename = $sqlres['fileName'];
        $fileArr = pathinfo($filename);
        $fileExt = $fileArr['extension'];

        if ($fileExt == 'zip') {

            $sql = "select configDetail from " . $GLOBALS['PREFIX'] . "softinst.Packages where id IN(1,7,2,3)";
            $pdo = $db->prepare($sql);
            $pdo->execute();
            $getconfigforZip = $pdo->fetchAll(PDO::FETCH_ASSOC);

            $zipdownload32 = $getconfigforZip[0]['configDetail'];
            $zipdownload64 = $getconfigforZip[3]['configDetail'];
            $bit32Link = $getconfigforZip[1]['configDetail'];
            $bit64Link = $getconfigforZip[2]['configDetail'];

            $bit32Link = str_replace("ValidationPath", $validationPath, $bit32Link);
            $bit32Link = str_replace("DistributionPath", $distributionPath, $bit32Link);

            $bit64Link = str_replace("ValidationPath", $validationPath, $bit64Link);
            $bit64Link = str_replace("DistributionPath", $distributionPath, $bit64Link);


            $confStr .= "$zipdownload32 " . "\n";
            $confStr .= "$bit32Link" . "\n";
            $confStr .= "64Link:\n";
            $confStr .= "$addconfig" . "\n";
            $confStr .= "$zipdownload64" . "\n";
            $confStr .= "$bit64Link" . "\n";
        } else {
            $confStr .= "64Link:\n";
            $confStr .= "$addconfig" . "\n";
        }

        $confStr .= "Positive:" . "\n";
        $confStr .= "Negative:" . "\n";
        $confStr .= "Special:" . "\n";
        $confStr .= "LogFile:" . "\n";
        $confStr .= "Default:default,*.txt" . "\n";
        $confStr .= "DeleteLogFile:" . "\n";
        $confStr .= "StatusMessageBox:$enablemsg" . "\n";
        $confStr .= "MessageBoxText:$msgtext" . "\n";
        $confStr .= "MaxTimePerPatch:$maxTime" . "\n";
        $confStr .= "ProcessToKill:";

        $confStrLin = "1~[$packageName]" . "\n";
        $confStrLin .= "32Link:\n";
        $confStrLin .= "$linuxConf" . "\n";
        $confStrLin .= "64Link:\n";
        $confStrLin .= "$linuxConf" . "\n";

        if ($platform == 'mac') {
            echo $macconfig;
        } else if ($platform == 'linux') {
            echo $confStrLin;
        } else if ($platform == 'android') {
            $sql1 = "SELECT posKeywords,packageExpiry,policyEnforce,androidPreCheck,androidPostCheck,installType,"
                . "downloadType,maxTime,preInstallMsg,postDownloadMsg,finalInstallMsg,frequencySettings,"
                . "andPreCheckCond,andDestinationPath,distributionType,andSourcePath,messageText,andPostCheckCond,"
                . "andDestPath,andPreCheckPath "
                . "FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration where packageId=?";

            $pdo = $db->prepare($sql1);
            $pdo->execute([$id]);
            $sqlres1 = $pdo->fetch(PDO::FETCH_ASSOC);

            $posKeywords = $sqlres1['posKeywords'];
            $path = $sqlres['path'];
            $packageName = $sqlres['packageName'];

            if ($posKeywords == "415") {
                $titleName = $sqlres1['messageText'];
                $installType = $sqlres1['installType'];
                $preInstallMsg = '$$$Preinstall=' . $titleName . $sqlres1['preInstallMsg'];
                $postDownloadMsg = '$$$Postdownload=' . $titleName . $sqlres1['postDownloadMsg'];
                $finalInstallMsg = '$$$Installmsg=' . $titleName . $sqlres1['finalInstallMsg'];
                $frequencySettingsRaw = '$$$' . $sqlres1['frequencySettings'];
                $frequencySettings = str_replace(',', '$$$', $frequencySettingsRaw);

                switch ($installType) {
                    case 0:
                        $confStr = "0," . $path;
                        break;
                    case 3:
                        $confStr = '3$$$AppUrl=' . $path . $preInstallMsg . $postDownloadMsg . $finalInstallMsg;
                        break;
                    case 5:
                        $confStr = '5$$$AppUrl=' . $path . $preInstallMsg . $postDownloadMsg . $finalInstallMsg . $frequencySettings;
                        break;
                    default:
                        $confStr = "0," . $path;
                        break;
                }
            } else if ($posKeywords == "288") {
                $FileTypeExt = null;
                if ($sqlres1['distributionType'] == "1" || $sqlres1['distributionType'] == "3") {
                    $FileTypeExt = 4;
                } else {
                    $FileTypeExt = 1;
                }

                $packageExpiry = $sqlres1['packageExpiry'];
                $policyEnforce = $sqlres1['policyEnforce'];
                $androidPreCheck = $sqlres1['androidPreCheck'];
                $androidPostCheck = $sqlres1['androidPostCheck'];
                $andPreCheckCond = $sqlres1['andPreCheckCond'];
                $andPostCheckCond = $sqlres1['andPostCheckCond'];
                $downloadType = $sqlres1['downloadType'];
                $maxTime = $sqlres1['maxTime'];
                $andDestPath = $sqlres1['andDestPath'];
                $andPreCheckPath = $sqlres1['andPreCheckPath'];


                if ($androidPreCheck == "0") {
                    if ($andPreCheckPath == "NA") {
                        $androidPreCheck = "0," . $andPreCheckPath;
                    } else {
                        $androidPreCheck = "0," . $andPreCheckPath . $sqlres['fileName'];
                    }
                } else if ($androidPreCheck == "1") {
                    $androidPreCheck = "1," . $andPreCheckCond;
                } else if ($androidPreCheck == "2") {
                    $androidPreCheck = "2," . $andPreCheckCond;
                }

                if ($androidPostCheck == "1") {
                    $androidPostCheck = "#1#" . $andPostCheckCond . "," . $downloadType;
                } else if ($androidPostCheck == "0") {
                    $androidPostCheck = "#1#" . $androidPostCheck;
                } else {
                    $androidPostCheck = "," . $downloadType;
                }


                if ($sqlres1['distributionType'] == "3") {
                    $confpath = "1,AND," . $sqlres1['andSourcePath'] . ",0,$policyEnforce,$FileTypeExt," . $sqlres1['andDestinationPath'] . ",0,NA,1";
                } else if ($sqlres1['distributionType'] == "1") {
                    $confpath = "1,AND,$path,0,$policyEnforce,$FileTypeExt,$andDestPath,$androidPreCheck$androidPostCheck";
                } else {
                    $confpath = "1,AND,$path,0,$policyEnforce,$FileTypeExt,NA,$androidPreCheck$androidPostCheck";
                }


                $confStr = "1~[$packageName]" . "\n";
                $confStr .= "32Link:\n";
                $confStr .= "$confpath" . "\n";

                $filename = $sqlres['fileName'];
                $fileArr = pathinfo($filename);
                $fileExt = $fileArr['extension'];

                if ($fileExt == 'zip') {

                    $pdo = $db->prepare($getconfigforZip);
                    $pdo->execute();
                    $getconfigforZip = $pdo->fetchAll(PDO::FETCH_ASSOC);

                    $zipdownload32 = $getconfigforZip[0]['configDetail'];
                    $zipdownload64 = $getconfigforZip[3]['configDetail'];
                    $bit32Link = $getconfigforZip[1]['configDetail'];
                    $bit64Link = $getconfigforZip[2]['configDetail'];

                    $confStr .= "64Link:\n";
                    $confStr .= "$confpath" . "\n";
                } else {
                    $confStr .= "64Link:\n";
                    $confStr .= "$confpath" . "\n";
                }


                $confStr .= "Positive:" . "\n";
                $confStr .= "Negative:" . "\n";
                $confStr .= "Special:" . "\n";
                $confStr .= "LogFile:" . "\n";
                $confStr .= "Default:default,*.txt" . "\n";
                $confStr .= "DeleteLogFile:0" . "\n";
                $confStr .= "StatusMessageBox:0" . "\n";
                $confStr .= "MessageBoxText:0\n";
                $confStr .= "MaxTimePerPatch:" . $maxTime . "\n";
                $confStr .= "ProcessToKill:";
            }
            echo $confStr;
        }
    } else {
        $sql1 = "SELECT distributionConfigDetail from " . $GLOBALS['PREFIX'] . "softinst.Packages where id=?";
        $pdo = $db->prepare($sql1);
        $pdo->execute([$id]);
        $sqlres1 = $pdo->fetch(PDO::FETCH_ASSOC);
        $confStr = $sqlres1['distributionConfigDetail'];
        echo $confStr;
    }
}


function getConfigDistribute()
{
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();

    $id = (int)url::requestToText('id');
    $sql = "select platform,type,sourceType,protocol,packageDesc,path,fileName,packageName,distrubute,distributionPath,distributionTime,distributionVpath,isConfigured,configDetail,addConfigDetail,distributionConfigDetail from " . $GLOBALS['PREFIX'] . "softinst.Packages where id=?";
    $pdo = $db->prepare($sql);
    $pdo->execute([$id]);
    $sqlres = $pdo->fetch(PDO::FETCH_ASSOC);
    if ($sqlres['platform'] == 'windows') {
        $sql1 = "SELECT 32bitConfig, 64bitConfig, posKeywords, negKeywords, logFilesToRead, defaultRead, deleteLogFile, enableMessage, messageText, maxTime, processToKill, installType, preInstallMsg, postDownloadMsg, finalInstallMsg FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_new where packageId=?";
        $pdo = $db->prepare($sql1);
        $pdo->execute([$id]);
        $sqlres1 = $pdo->fetch(PDO::FETCH_ASSOC);
    } else {
        $sql1 = "SELECT cmdLines, enableMessage, messageText, executePath, validation, session, runas, rootKey, subKey, validationFilePath, checkPreInstall, pValidationFilePath, softwareName, softwareVersion, knowledgeBase, servicePack, pRootKey, pSubKey FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration where packageId=?";
        $pdo = $db->prepare($sql1);
        $pdo->execute([$id]);
        $sqlres1 = $pdo->fetch(PDO::FETCH_ASSOC);
    }
    $platform = $sqlres['platform'];
    $packageName = $sqlres['packageName'];
    $addconfig = $sqlres['addConfigDetail'];
    $macconfig = $sqlres['configDetail'];
    $maxTime = $sqlres1['maxTime'];
    $distributionPath = $sqlres['distributionPath'];
    $validationPath = $sqlres['distributionVpath'];
    $enablemsg = $sqlres1['enableMessage'];
    $msgtext = $sqlres1['messageText'];
    $linuxConf = $sqlres['configDetail'];
    $linkConfig_32 = $sqlres1['32bitConfig'];
    $linkConfig_64 = $sqlres1['64bitConfig'];
    $confStr = "1~[$packageName]" . "\n";
    $confStr .= "32Link:\n";
    $confStr .= "$linkConfig_32";

    $distrubute = $sqlres['distrubute'];

    $filename = $sqlres['fileName'];
    $fileArr = pathinfo($filename);
    $fileExt = $fileArr['extension'];

    if ($fileExt == 'zip') {

        $sql = "select configDetail from " . $GLOBALS['PREFIX'] . "softinst.Packages where id IN(1,7,2,3)";
        $pdo = $db->prepare($sql);
        $pdo->execute();
        $getconfigforZip = $pdo->fetchAll(PDO::FETCH_ASSOC);

        $zipdownload32 = $getconfigforZip[0]['configDetail'];
        $zipdownload64 = $getconfigforZip[3]['configDetail'];
        $bit32Link = $getconfigforZip[1]['configDetail'];
        $bit64Link = $getconfigforZip[2]['configDetail'];

        $bit32Link = str_replace("ValidationPath", $validationPath, $bit32Link);
        $bit32Link = str_replace("DistributionPath", $distributionPath, $bit32Link);

        $bit64Link = str_replace("ValidationPath", $validationPath, $bit64Link);
        $bit64Link = str_replace("DistributionPath", $distributionPath, $bit64Link);


        $confStr .= "$zipdownload32 " . "\n";
        $confStr .= "$bit32Link" . "\n";
        $confStr .= "64Link:\n";
        $confStr .= "$addconfig" . "\n";
        $confStr .= "$zipdownload64" . "\n";
        $confStr .= "$bit64Link" . "\n";
    } else {
        $confStr .= "64Link:\n";
        $confStr .= "$linkConfig_64";
    }

    $confStr .= "Positive:" . "\n";
    $confStr .= "Negative:" . "\n";
    $confStr .= "Special:" . "\n";
    $confStr .= "LogFile:" . "\n";
    $confStr .= "Default:default,*.txt" . "\n";
    $confStr .= "DeleteLogFile:" . "\n";
    $confStr .= "StatusMessageBox:$enablemsg" . "\n";
    $confStr .= "MessageBoxText:$msgtext" . "\n";
    $confStr .= "MaxTimePerPatch:$maxTime" . "\n";
    $confStr .= "ProcessToKill:";

    $confStrLin = "1~[$packageName]" . "\n";
    $confStrLin .= "32Link:\n";
    $confStrLin .= "$linuxConf" . "\n";
    $confStrLin .= "64Link:\n";
    $confStrLin .= "$linuxConf" . "\n";
    if ($platform == 'mac') {
        echo $macconfig;
    } else if ($platform == 'linux') {
        echo $confStrLin;
    } else if ($platform == 'android') {
        $sql1 = "SELECT posKeywords,packageExpiry,policyEnforce,androidPreCheck,androidPostCheck,installType,"
            . "downloadType,maxTime,preInstallMsg,postDownloadMsg,finalInstallMsg,frequencySettings,"
            . "andPreCheckCond,andDestinationPath,distributionType,andSourcePath,messageText,andPostCheckCond,"
            . "andDestPath,andPreCheckPath "
            . "FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration where packageId=?";

        $pdo = $db->prepare($sql1);
        $pdo->execute([$id]);
        $sqlres1 = $pdo->fetch(PDO::FETCH_ASSOC);

        $posKeywords = $sqlres1['posKeywords'];
        $path = $sqlres['path'];
        $packageName = $sqlres['packageName'];

        if ($posKeywords == "415") {
            $titleName = $sqlres1['messageText'];
            $installType = $sqlres1['installType'];
            $preInstallMsg = '$$$Preinstall=' . $titleName . $sqlres1['preInstallMsg'];
            $postDownloadMsg = '$$$Postdownload=' . $titleName . $sqlres1['postDownloadMsg'];
            $finalInstallMsg = '$$$Installmsg=' . $titleName . $sqlres1['finalInstallMsg'];
            $frequencySettingsRaw = '$$$' . $sqlres1['frequencySettings'];
            $frequencySettings = str_replace(',', '$$$', $frequencySettingsRaw);

            switch ($installType) {
                case 0:
                    $confStr = "0," . $path;
                    break;
                case 3:
                    $confStr = '3$$$AppUrl=' . $path . $preInstallMsg . $postDownloadMsg . $finalInstallMsg;
                    break;
                case 5:
                    $confStr = '5$$$AppUrl=' . $path . $preInstallMsg . $postDownloadMsg . $finalInstallMsg . $frequencySettings;
                    break;
                default:
                    $confStr = "0," . $path;
                    break;
            }
        } else if ($posKeywords == "288") {
            $FileTypeExt = null;
            if ($sqlres1['distributionType'] == "1" || $sqlres1['distributionType'] == "3") {
                $FileTypeExt = 4;
            } else {
                $FileTypeExt = 1;
            }

            $packageExpiry = $sqlres1['packageExpiry'];
            $policyEnforce = $sqlres1['policyEnforce'];
            $androidPreCheck = $sqlres1['androidPreCheck'];
            $androidPostCheck = $sqlres1['androidPostCheck'];
            $andPreCheckCond = $sqlres1['andPreCheckCond'];
            $andPostCheckCond = $sqlres1['andPostCheckCond'];
            $downloadType = $sqlres1['downloadType'];
            $maxTime = $sqlres1['maxTime'];
            $andDestPath = $sqlres1['andDestPath'];
            $andPreCheckPath = $sqlres1['andPreCheckPath'];


            if ($androidPreCheck == "0") {
                if ($andPreCheckPath == "NA") {
                    $androidPreCheck = "0," . $andPreCheckPath;
                } else {
                    $androidPreCheck = "0," . $andPreCheckPath . $sqlres['fileName'];
                }
            } else if ($androidPreCheck == "1") {
                $androidPreCheck = "1," . $andPreCheckCond;
            } else if ($androidPreCheck == "2") {
                $androidPreCheck = "2," . $andPreCheckCond;
            }

            if ($androidPostCheck == "1") {
                $androidPostCheck = "#1#" . $andPostCheckCond . "," . $downloadType;
            } else if ($androidPostCheck == "0") {
                $androidPostCheck = "#1#" . $androidPostCheck;
            } else {
                $androidPostCheck = "," . $downloadType;
            }


            if ($sqlres1['distributionType'] == "3") {
                $confpath = "1,AND," . $sqlres1['andSourcePath'] . ",0,$policyEnforce,$FileTypeExt," . $sqlres1['andDestinationPath'] . ",0,NA,1";
            } else if ($sqlres1['distributionType'] == "1") {
                $confpath = "1,AND,$path,0,$policyEnforce,$FileTypeExt,$andDestPath,$androidPreCheck$androidPostCheck";
            } else {
                $confpath = "1,AND,$path,0,$policyEnforce,$FileTypeExt,NA,$androidPreCheck$androidPostCheck";
            }


            $confStr = "1~[$packageName]" . "\n";
            $confStr .= "32Link:\n";
            $confStr .= "$confpath" . "\n";

            $filename = $sqlres['fileName'];
            $fileArr = pathinfo($filename);
            $fileExt = $fileArr['extension'];

            if ($fileExt == 'zip') {

                $pdo = $db->prepare($getconfigforZip);
                $pdo->execute();
                $getconfigforZip = $pdo->fetchAll(PDO::FETCH_ASSOC);

                $zipdownload32 = $getconfigforZip[0]['configDetail'];
                $zipdownload64 = $getconfigforZip[3]['configDetail'];
                $bit32Link = $getconfigforZip[1]['configDetail'];
                $bit64Link = $getconfigforZip[2]['configDetail'];

                $confStr .= "64Link:\n";
                $confStr .= "$confpath" . "\n";
            } else {
                $confStr .= "64Link:\n";
                $confStr .= "$confpath" . "\n";
            }


            $confStr .= "Positive:" . "\n";
            $confStr .= "Negative:" . "\n";
            $confStr .= "Special:" . "\n";
            $confStr .= "LogFile:" . "\n";
            $confStr .= "Default:default,*.txt" . "\n";
            $confStr .= "DeleteLogFile:0" . "\n";
            $confStr .= "StatusMessageBox:0" . "\n";
            $confStr .= "MessageBoxText:0\n";
            $confStr .= "MaxTimePerPatch:" . $maxTime . "\n";
            $confStr .= "ProcessToKill:";
        }
        echo $confStr;
    } else {
        echo $confStr;
        $sql = $db->prepare("update " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_new set edConfigdeploy=? where packageId=?");
        $res = $sql->execute([$confStr, $id]);
    }
}

function getConfigexecute() ////////////////////////
{
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }


    $db = pdo_connect();
    $id = (int)url::requestToText('id');

    $sql = "select platform,type,sourceType,protocol,packageDesc,path,version,fileName,packageName,distrubute,distributionPath,distributionTime,"
        . "distributionVpath,isConfigured,configDetail,addConfigDetail,distributionConfigDetail from " . $GLOBALS['PREFIX'] . "softinst.Packages where id=?";
    $pdo = $db->prepare($sql);
    $pdo->execute([$id]);
    $sqlres = $pdo->fetch(PDO::FETCH_ASSOC);

    if ($sqlres['platform'] == "mac") {
        $confStr = $sqlres['addConfigDetail'];
    } else if ($sqlres['platform'] == "linux") {
        $packageName = $sqlres['packageName'];
        $confStr .= "1~[$packageName]" . "\n";
        $confStr .= "32Link:\n";
        $confStr .= $sqlres['addConfigDetail'] . "\n";
        $confStr .= "64Link:\n";
        $confStr .= $sqlres['addConfigDetail'] . "\n";
    } else if (($sqlres['platform'] == "ios") && ($sqlres['sourceType'] == '2' || $sqlres['sourceType'] == '5')) {
        $confStr = "0," . $sqlres['distributionPath'];
    } else if (($sqlres['platform'] == "android") && ($sqlres['sourceType'] == '2')) {

        $sql1 = "SELECT posKeywords,packageExpiry,policyEnforce,androidPreCheck,androidPostCheck,installType,"
            . "downloadType,maxTime,preInstallMsg,postDownloadMsg,finalInstallMsg,frequencySettings,"
            . "andPreCheckCond,andDestinationPath,distributionType,andSourcePath,messageText,andPostCheckCond,"
            . "andDestPath,andPreCheckPath "
            . "FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration where packageId=?";

        $pdo = $db->prepare($sql1);
        $pdo->execute([$id]);
        $sqlres1 = $pdo->fetch(PDO::FETCH_ASSOC);

        $posKeywords = $sqlres1['posKeywords'];
        $path = $sqlres['path'];
        $packageName = $sqlres['packageName'];

        if ($posKeywords == "415") {
            $titleName = $sqlres1['messageText'];
            $installType = $sqlres1['installType'];
            $preInstallMsg = '$$$Preinstall=' . $titleName . $sqlres1['preInstallMsg'];
            $postDownloadMsg = '$$$Postdownload=' . $titleName . $sqlres1['postDownloadMsg'];
            $finalInstallMsg = '$$$Installmsg=' . $titleName . $sqlres1['finalInstallMsg'];
            $frequencySettingsRaw = '$$$' . $sqlres1['frequencySettings'];
            $frequencySettings = str_replace(',', '$$$', $frequencySettingsRaw);

            switch ($installType) {
                case 0:
                    $confStr = "0," . $path;
                    break;
                case 3:
                    $confStr = '3$$$AppUrl=' . $path . $preInstallMsg . $postDownloadMsg . $finalInstallMsg;
                    break;
                case 5:
                    $confStr = '5$$$AppUrl=' . $path . $preInstallMsg . $postDownloadMsg . $finalInstallMsg . $frequencySettings;
                    break;
                default:
                    $confStr = "0," . $path;
                    break;
            }
        } else if ($posKeywords == "288") {
            $FileTypeExt = null;
            if ($sqlres1['distributionType'] == "1" || $sqlres1['distributionType'] == "3") {
                $FileTypeExt = 4;
            } else {
                $FileTypeExt = 1;
            }

            $packageExpiry = $sqlres1['packageExpiry'];
            $policyEnforce = $sqlres1['policyEnforce'];
            $androidPreCheck = $sqlres1['androidPreCheck'];
            $androidPostCheck = $sqlres1['androidPostCheck'];
            $andPreCheckCond = $sqlres1['andPreCheckCond'];
            $andPostCheckCond = $sqlres1['andPostCheckCond'];
            $downloadType = $sqlres1['downloadType'];
            $maxTime = $sqlres1['maxTime'];
            $andDestPath = $sqlres1['andDestPath'];
            $andPreCheckPath = $sqlres1['andPreCheckPath'];


            if ($androidPreCheck == "0") {
                if ($andPreCheckPath == "NA") {
                    $androidPreCheck = "0," . $andPreCheckPath;
                } else {
                    $androidPreCheck = "0," . $andPreCheckPath . $sqlres['fileName'];
                }
            } else if ($androidPreCheck == "1") {
                $androidPreCheck = "1," . $andPreCheckCond;
            } else if ($androidPreCheck == "2") {
                $androidPreCheck = "2," . $andPreCheckCond;
            }

            if ($androidPostCheck == "1") {
                $androidPostCheck = "#1#" . $andPostCheckCond . "," . $downloadType;
            } else if ($androidPostCheck == "0") {
                $androidPostCheck = "#1#" . $androidPostCheck;
            } else {
                $androidPostCheck = "," . $downloadType;
            }


            if ($sqlres1['distributionType'] == "3") {
                $confpath = "1,AND," . $sqlres1['andSourcePath'] . ",0,$policyEnforce,$FileTypeExt," . $sqlres1['andDestinationPath'] . ",0,NA,1";
            } else if ($sqlres1['distributionType'] == "1") {
                $confpath = "1,AND,$path,0,$policyEnforce,$FileTypeExt,$andDestPath,$androidPreCheck$androidPostCheck";
            } else {
                $confpath = "1,AND,$path,0,$policyEnforce,$FileTypeExt,NA,$androidPreCheck$androidPostCheck";
            }


            $confStr = "1~[$packageName]" . "\n";
            $confStr .= "32Link:\n";
            $confStr .= "$confpath" . "\n";

            $filename = $sqlres['fileName'];
            $fileArr = pathinfo($filename);
            $fileExt = $fileArr['extension'];

            if ($fileExt == 'zip') {

                $getconfigforZip = "select configDetail from Packages where id IN(1,7,2,3)";
                $pdo = $db->prepare($getconfigforZip);
                $pdo->execute();
                $getconfigforZip = $pdo->fetchAll(PDO::FETCH_ASSOC);

                $zipdownload32 = $getconfigforZip[0]['configDetail'];
                $zipdownload64 = $getconfigforZip[3]['configDetail'];
                $bit32Link = $getconfigforZip[1]['configDetail'];
                $bit64Link = $getconfigforZip[2]['configDetail'];

                $confStr .= "64Link:\n";
                $confStr .= "$confpath" . "\n";
            } else {
                $confStr .= "64Link:\n";
                $confStr .= "$confpath" . "\n";
            }


            $confStr .= "Positive:" . "\n";
            $confStr .= "Negative:" . "\n";
            $confStr .= "Special:" . "\n";
            $confStr .= "LogFile:" . "\n";
            $confStr .= "Default:default,*.txt" . "\n";
            $confStr .= "DeleteLogFile:0" . "\n";
            $confStr .= "StatusMessageBox:0" . "\n";
            $confStr .= "MessageBoxText:0\n";
            $confStr .= "MaxTimePerPatch:" . $maxTime . "\n";
            $confStr .= "ProcessToKill:";
        }
    } else {
        $sql1 = "SELECT packageId, 32bitConfig, 64bitConfig, posKeywords, negKeywords, logFilesToRead, defaultRead, deleteLogFile, enableMessage,"
            . "messageText, maxTime, processToKill, installType, preInstallMsg, postDownloadMsg, finalInstallMsg"
            . " FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_new where packageId=?";
        $pdo = $db->prepare($sql1);
        $pdo->execute([$id]);
        $sqlres1 = $pdo->fetch(PDO::FETCH_ASSOC);
        $config_32bit = trim($sqlres1['32bitConfig']);
        $config_64bit = trim($sqlres1['64bitConfig']);
        $packageName = $sqlres['packageName'];
        $conconfig = $sqlres['configDetail'];
        $maxTime = $sqlres1['maxTime'];
        $distributionPath = $sqlres['distributionPath'];
        $validationPath = $sqlres['distributionVpath'];
        $enablemsg = $sqlres1['enableMessage'];
        $msgtext = $sqlres1['messageText'];
        $distribute = $sqlres['distrubute'];

        $confStr = "1~[$packageName]" . "\n";
        $confStr .= "32Link:\n";
        $confStr .= "$config_32bit" . "\n";

        $distrubute = $sqlres['distrubute'];

        $filename = $sqlres['fileName'];
        $fileArr = pathinfo($filename);
        $fileExt = $fileArr['extension'];

        if ($fileExt == 'zip') {

            $sql2 = "select configDetail from " . $GLOBALS['PREFIX'] . "softinst.Packages where id IN(1,7,2,3)";
            $pdo = $db->prepare($sql2);
            $pdo->execute();
            $getconfigforZip = $pdo->fetchAll(PDO::FETCH_ASSOC);

            $zipdownload32 = $getconfigforZip[0]['configDetail'];
            $zipdownload64 = $getconfigforZip[3]['configDetail'];
            $bit32Link = $getconfigforZip[1]['configDetail'];
            $bit64Link = $getconfigforZip[2]['configDetail'];

            $bit32Link = str_replace("ValidationPath", $validationPath, $bit32Link);
            $bit32Link = str_replace("DistributionPath", $distributionPath, $bit32Link);

            $bit64Link = str_replace("ValidationPath", $validationPath, $bit64Link);
            $bit64Link = str_replace("DistributionPath", $distributionPath, $bit64Link);

            $confStr .= "64Link:\n";
            $confStr .= "$conconfig" . "\n";
        } else {
            $confStr .= "64Link:\n";
            $confStr .= "$config_64bit" . "\n";
        }

        $confStr .= "Positive:" . "\n";
        $confStr .= "Negative:" . "\n";
        $confStr .= "Special:" . "\n";
        $confStr .= "LogFile:" . "\n";
        $confStr .= "Default:default,*.txt" . "\n";
        $confStr .= "DeleteLogFile:" . "\n";
        $confStr .= "StatusMessageBox:$enablemsg" . "\n";
        $confStr .= "MessageBoxText:$msgtext" . "\n";
        $confStr .= "MaxTimePerPatch:$maxTime" . "\n";
        $confStr .= "ProcessToKill:";
    }
    $sql = $db->prepare("update " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_new set edConfigexecute=? where packageId=?");
    $res = $sql->execute([$confStr, $id]);
    print_data($confStr);
}

function getConfig()
{
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();

    $id = (int)url::requestToText('id');

    $sql = "select platform,type,sourceType,protocol,packageDesc,path,fileName,packageName,distrubute,distributionPath,distributionTime,distributionVpath,isConfigured,configDetail,addConfigDetail,distributionConfigDetail from " . $GLOBALS['PREFIX'] . "softinst.Packages where id=?";
    $pdo = $db->prepare($sql);
    $pdo->execute([$id]);
    $sqlres = $pdo->fetch(PDO::FETCH_ASSOC);
    if ($sqlres['platform'] == 'windows') {
        $sql1 = "SELECT packageId, 32bitConfig, 64bitConfig, posKeywords, negKeywords, logFilesToRead, defaultRead, deleteLogFile, enableMessage, messageText, maxTime, processToKill, installType, preInstallMsg, postDownloadMsg, finalInstallMsg from " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_new where packageId=?";
        $pdo = $db->prepare($sql1);
        $pdo->execute([$id]);
        $sqlres1 = $pdo->fetch(PDO::FETCH_ASSOC);
    } else {
        $sql1 = "SELECT cmdLines, enableMessage, messageText, executePath, validation, session, runas, rootKey, subKey, validationFilePath, checkPreInstall, pValidationFilePath, softwareName, softwareVersion, knowledgeBase, servicePack, pRootKey, pSubKey FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration where packageId=?";
        $pdo = $db->prepare($sql1);
        $pdo->execute([$id]);
        $sqlres1 = $pdo->fetch(PDO::FETCH_ASSOC);
    }
    if ($sqlres['platform'] == "mac") {
        if ($sqlres['addConfigDetail'] != "") {
            $confStr = $sqlres['configDetail'] . "\n";
            $confStr .= $sqlres['addConfigDetail'];
        } else {
            $confStr = $sqlres['configDetail'];
        }
    } else if ($sqlres['platform'] == "windows") {
        $packageName = $sqlres['packageName'];
        $addconfig = $sqlres['addConfigDetail'];
        $conconfig = $sqlres['configDetail'];
        $config_32 = $sqlres1['32bitConfig'];
        $config_64 = $sqlres1['64bitConfig'];
        $maxTime = $sqlres1['maxTime'];
        $distributionPath = $sqlres['distributionPath'];
        $validationPath = $sqlres['distributionVpath'];
        $enablemsg = $sqlres1['enableMessage'];
        // $msgtext = $sqlres1['messageText'];
        $msgtext = '';
        $msgTextArr = [];

        if ($enablemsg == 1) {
            foreach ([$sqlres1['postDownloadMsg'], $sqlres1['preInstallMsg']] as $key => $value) {
                if ($value) {
                    $msgTextArr[] = '[' . ($key + 1) . ']-' . $value;
                }
            }
            $msgtext = implode("#", $msgTextArr);
        }

        $confStr = "1~[$packageName]" . "\n";
        $confStr .= "32Link:\n";
        $confStr .= "$config_32" . "\n";

        $distrubute = $sqlres['distrubute'];

        $filename = $sqlres['fileName'];
        $fileArr = pathinfo($filename);
        $fileExt = $fileArr['extension'];

        if ($fileExt == 'zip') {

            $getconfigforZip = "select configDetail from " . $GLOBALS['PREFIX'] . "softinst.Packages where id IN(1,7,2,3)";
            $pdo = $db->prepare($getconfigforZip);
            $pdo->execute();
            $getconfigforZip = $pdo->fetchAll(PDO::FETCH_ASSOC);

            $zipdownload32 = $getconfigforZip[0]['configDetail'];
            $zipdownload64 = $getconfigforZip[3]['configDetail'];
            $bit32Link = $getconfigforZip[1]['configDetail'];
            $bit64Link = $getconfigforZip[2]['configDetail'];

            $bit32Link = str_replace("ValidationPath", $validationPath, $bit32Link);
            $bit32Link = str_replace("DistributionPath", $distributionPath, $bit32Link);

            $bit64Link = str_replace("ValidationPath", $validationPath, $bit64Link);
            $bit64Link = str_replace("DistributionPath", $distributionPath, $bit64Link);


            $confStr .= "$zipdownload32" . "\n";
            $confStr .= "$bit32Link" . "\n";
            $confStr .= "$conconfig" . "\n";
            $confStr .= "64Link:\n";
            $confStr .= "$addconfig" . "\n";
            $confStr .= "$zipdownload64" . "\n";
            $confStr .= "$bit64Link" . "\n";
            $confStr .= "$conconfig" . "\n";
        } else {
            $confStr .= "\n" . "64Link:\n";
            $confStr .= "$config_64" . "\n";
        }

        $confStr .= "Positive:" . $sqlres1['posKeywords'] . "\n";
        $confStr .= "Negative:" . $sqlres1['negKeywords'] . "\n";
        $confStr .= "Special:" . "\n";
        $confStr .= "LogFile:" . ($sqlres1['logFilesToRead'] ? "\n" : '') . $sqlres1['logFilesToRead'] . "\n";
        $confStr .= "Default:" . $sqlres1['defaultRead'] . "\n";
        $confStr .= "DeleteLogFile:" . $sqlres1['deleteLogFile'] . "\n";
        $confStr .= "StatusMessageBox:$enablemsg" . "\n";
        $confStr .= "MessageBoxText:$msgtext" . "\n";
        $confStr .= "MaxTimePerPatch:$maxTime" . "\n";
        $confStr .= "ProcessToKill:" . $sqlres1['processToKill'];
    } else {
        $packageName = $sqlres['packageName'];

        if ($sqlres['platform'] == "linux") {
            $addconfig = $sqlres['configDetail'];
            $conconfig = $sqlres['addConfigDetail'];
        } else {
            $addconfig = $sqlres['addConfigDetail'];
            $conconfig = $sqlres['configDetail'];
        }

        $maxTime = $sqlres['distributionTime'];
        $distributionPath = $sqlres['distributionPath'];
        $validationPath = $sqlres['distributionVpath'];
        $enablemsg = $sqlres1['enableMessage'];
        $msgtext = $sqlres1['messageText'];

        $confStr = "1~[$packageName]" . "\n";
        $confStr .= "32Link:\n";
        $confStr .= "$addconfig" . "\n";

        $distrubute = $sqlres['distrubute'];

        $filename = $sqlres['fileName'];
        $fileArr = pathinfo($filename);
        $fileExt = $fileArr['extension'];

        if ($fileExt == 'zip') {

            $getconfigforZip = "select configDetail from " . $GLOBALS['PREFIX'] . "softinst.Packages where id IN(1,7,2,3)";
            $pdo = $db->prepare($getconfigforZip);
            $pdo->execute();
            $getconfigforZip = $pdo->fetchAll(PDO::FETCH_ASSOC);

            $zipdownload32 = $getconfigforZip[0]['configDetail'];
            $zipdownload64 = $getconfigforZip[3]['configDetail'];
            $bit32Link = $getconfigforZip[1]['configDetail'];
            $bit64Link = $getconfigforZip[2]['configDetail'];

            $bit32Link = str_replace("ValidationPath", $validationPath, $bit32Link);
            $bit32Link = str_replace("DistributionPath", $distributionPath, $bit32Link);

            $bit64Link = str_replace("ValidationPath", $validationPath, $bit64Link);
            $bit64Link = str_replace("DistributionPath", $distributionPath, $bit64Link);


            $confStr .= "$zipdownload32" . "\n";
            $confStr .= "$bit32Link" . "\n";
            $confStr .= "$conconfig" . "\n";
            $confStr .= "64Link:\n";
            $confStr .= "$addconfig" . "\n";
            $confStr .= "$zipdownload64" . "\n";
            $confStr .= "$bit64Link" . "\n";
            $confStr .= "$conconfig" . "\n";
        } else {
            $confStr .= "$conconfig" . "\n";
            $confStr .= "64Link:\n";
            $confStr .= "$addconfig" . "\n";
            $confStr .= "$conconfig" . "\n";
        }

        if ($sqlres['platform'] != 'linux') {
            $confStr .= "Positive:" . "\n";
            $confStr .= "Negative:" . "\n";
            $confStr .= "Special:" . "\n";
            $confStr .= "LogFile:" . "\n";
            $confStr .= "Default:default,*.txt" . "\n";
            $confStr .= "DeleteLogFile:" . "\n";
            $confStr .= "StatusMessageBox:$enablemsg" . "\n";
            $confStr .= "MessageBoxText:$msgtext" . "\n";
            $confStr .= "MaxTimePerPatch:$maxTime" . "\n";
            $confStr .= "ProcessToKill:";
        }
    }
    $confStr = preg_replace("/(\R){2,}/", "$1", $confStr);
    $sql = $db->prepare("update " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_new set edConfig=? where packageId=?");
    $res = $sql->execute([$confStr, $id]);

    print_data($confStr);
}

function addUpdateswdConf($id, $configure)
{
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    $redis = swdredisConn();
    $redis->HSET('SWDConf', $id, $configure);
}

function swdredisConn()
{
    logs::log(__FILE__, __LINE__, "TEsting error", 3, '/var/www/html/dev_dashv8/softdist/softdist_error.log');
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    try {
        return RedisLink::connect();
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        logs::log(__FILE__, __LINE__, $e->getMessage(), 3, '/var/www/html/dev_dashv8/softdist/softdist_error.log');
        return null;
    }
}

function mdmConfigUpdate($typeRes)
{
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$priviledge) {
        echo 'Permission denied';
        exit();
    }

    global $SWD_iOS_Url;
    $url = $SWD_iOS_Url;
    $db = pdo_connect();

    $searchVal = $_SESSION['searchValue'];
    $sqlInsValues = "";
    $siteName = $_SESSION['searchValue'];
    $time = strtotime(date('d-m-Y H:i:s'));

    if ($_SESSION['searchType'] == 'Sites') {
        $priority = 'p3';
        $machines = "SELECT host FROM " . $GLOBALS['PREFIX'] . "core.Census WHERE site=?";
        $pdo = $db->prepare($machines);
        $pdo->execute([$searchVal]);
        $machinesRes = $pdo->fetchAll(PDO::FETCH_ASSOC);

        foreach ($machinesRes as $id => $values) {
            $machineName = $values['host'];
            $udidsql = "SELECT UDID FROM " . $GLOBALS['PREFIX'] . "iosprofile.device WHERE machine=?";
            $pdo = $db->prepare($udidsql);
            $pdo->execute([$machineName]);
            $udidres = $pdo->fetch(PDO::FETCH_ASSOC);

            $dictCommand = 'SWD#' . $typeRes['packageName'] . '#<dict>'
                . '<key>Command</key>'
                . '<dict>'
                . '<key>RequestType</key>'
                . '<string>InstallApplication</string>'
                . '<key>ManagementFlags</key>'
                . '<integer>1</integer>'
                . '<key>ManifestURL</key>'
                . '<string>' . $url . '?dev_id=' . $udidres['UDID'] . '</string>'
                . '</dict>'
                . '<key>CommandUUID</key>'
                . '<string>swd</string>'
                . '</dict>';

            $filePath = split('url=', url::requestToText('edconfig'));
            $fileContent = file_get_contents($filePath[1]);
            $sqlInsValues .= " ('$searchVal','$machineName', '$priority', '9000','$dictCommand','$time',2,0), ('$searchVal', '$machineName', '$priority', '9000','$fileContent','$time',2,0), ('$searchVal', '$machineName', '$priority', '9000','$fileContent','$time',2,0),";
        }
    } elseif ($_SESSION['searchType'] == 'Groups') {
        $priority = 'p2';
        $group = "SELECT mgroupid FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name=?";
        $pdo = $db->prepare($group);
        $pdo->execute([$searchVal]);
        $groupRes = $pdo->fetch(PDO::FETCH_ASSOC);

        $groupId = $groupRes['mgroupid'];
        $groupMac = "SELECT site,host FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups as mg JOIN " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as mgm " .
            "on mg.mgroupuniq = mgm.mgroupuniq join " . $GLOBALS['PREFIX'] . "core.Census as c on mgm.censusuniq = c.censusuniq WHERE mg.mgroupid=?";
        $pdo = $db->prepare($groupMac);
        $pdo->execute([$groupId]);
        $groupMacRes = $pdo->fetchAll(PDO::FETCH_ASSOC);

        foreach ($groupMacRes as $keys => $val) {
            $machine = $val['host'];
            $udidsql = "SELECT UDID FROM " . $GLOBALS['PREFIX'] . "iosprofile.device WHERE machine=?";
            $pdo = $db->prepare($udidsql);
            $pdo->execute([$machine]);
            $udidres = $pdo->fetch(PDO::FETCH_ASSOC);

            $dictCommand = 'SWD#' . $typeRes['packageName'] . '#<dict>'
                . '<key>Command</key>'
                . '<dict>'
                . '<key>RequestType</key>'
                . '<string>InstallApplication</string>'
                . '<key>ManagementFlags</key>'
                . '<integer>1</integer>'
                . '<key>ManifestURL</key>'
                . '<string>' . $url . '?dev_id=' . $udidres['UDID'] . '</string>'
                . '</dict>'
                . '<key>CommandUUID</key>'
                . '<string>swd</string>'
                . '</dict>';

            $filePath = split('url=', url::requestToAny('edconfig'));
            $fileContent = file_get_contents($filePath[1]);

            $sqlInsValues .= " ('$searchVal', '$machine', '$priority', '9000','$dictCommand','$time',2,0), ('$searchVal', '$machine', '$priority', '9000','$fileContent','$time',2,0), ('$searchVal', '$machine', '$priority', '9000','$fileContent','$time',2,0),";
        }
    } elseif ($_SESSION['searchType'] == 'Service Tag' || $_SESSION['searchType'] == 'ServiceTag') {

        $scope = $_SESSION['rparentName'];
        $priority = 'p1';
        $udidsql = "SELECT UDID FROM " . $GLOBALS['PREFIX'] . "iosprofile.device WHERE machine=?";
        $pdo = $db->prepare($udidsql);
        $pdo->execute([$searchVal]);
        $udidres = $pdo->fetch(PDO::FETCH_ASSOC);

        $dictCommand = 'SWD#' . $typeRes['packageName'] . '#<dict>'
            . '<key>Command</key>'
            . '<dict>'
            . '<key>RequestType</key>'
            . '<string>InstallApplication</string>'
            . '<key>ManagementFlags</key>'
            . '<integer>1</integer>'
            . '<key>ManifestURL</key>'
            . '<string>' . $url . '?dev_id=' . $udidres['UDID'] . '</string>'
            . '</dict>'
            . '<key>CommandUUID</key>'
            . '<string>swd</string>'
            . '</dict>';

        $filePath = split('url=', url::requestToText('edconfig'));

        $fileContent = file_get_contents($filePath[1], false, $arrContextOptions);
        $sqlInsValues .= " ('$scope', '$searchVal', '$priority', '9000', '$dictCommand', '$time', 2, 0), "
            . "('$scope', '$searchVal', '$priority', '9000', '$fileContent', '$time', 2, 0), "
            . "('$scope', '$searchVal', '$priority', '9000', '$fileContent', '$time', 2, 0),"
            . "('$scope', '$searchVal', '$priority', '9000', '$fileContent', '$time', 2, 0),";

        $sqlInsertBindings[] = array($scope, $searchVal, $priority, 9000, $dictCommand, $time, 2, 0);
        $sqlInsertBindings[] = array($scope, $searchVal, $priority, 9000, $fileContent, $time, 2, 0);
        $sqlInsertBindings[] = array($scope, $searchVal, $priority, 9000, $fileContent, $time, 2, 0);
        $sqlInsertBindings[] = array($scope, $searchVal, $priority, 9000, $dictCommand, $time, 2, 0);
        $sqlInsertBindings[] = array($scope, $searchVal, $priority, 9000, $dictCommand, $time, 2, 0);
    }

    $xmlSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "iosprofile.mdmConfig (scope, machine, priority, dartNum, xml, executeTime, level, status) VALUES (?,?,?,?,?,?,?,?)";

    if (safe_sizeof($sqlInsertBindings) > 0) {
        foreach ($sqlInsertBindings as $eachBindingArray) {
            $pdo = $db->prepare($xmlSql);
            $insertId = $pdo->execute($eachBindingArray);
        }
    }
}

function Get_allSites()
{
    $db = pdo_connect();
    $sql1 = $db->prepare("select distinct site  from " . $GLOBALS['PREFIX'] . "core.Census");
    $sql1->execute();
    $sql1Res = $sql1->fetchAll();
    $siteList = $sql1Res['site'];
    print_r($siteList);
}

function Get_allMachines()
{
    $db = pdo_connect();
    $sql1 = $db->prepare("select distinct host  from " . $GLOBALS['PREFIX'] . "core.Census");
    $sql1->execute();
    $sql1Res = $sql1->fetchAll();
    $machineList = $sql1Res['host'];
    print_r($machineList);
}

function Get_allGroups()
{
    $db = pdo_connect();
    $groups = DASH_GetGroups($key, $pdo, $user);
    print_r($groups);
}

function getAllPackageDetails()
{
    $id = url::requestToAny('id');
    $db = pdo_connect();
    $sql1 = $db->prepare("select path,path2,config3264type,platform,packageName from " . $GLOBALS['PREFIX'] . "softinst.Packages where id =?");
    $sql1->execute([$id]);
    $sql1Res = $sql1->fetch(PDO::FETCH_ASSOC);
    $path = $sql1Res['path'];
    $path2 = $sql1Res['path2'];
    $platform = $sql1Res['platform'];
    $pack = $sql1Res['packageName'];
    $configtype = $sql1Res['config3264type'];

    $sql1 = $db->prepare("select maxTime,preInstallMsg,postDownloadMsg,processToKill,logFilesToRead,defaultRead,deleteLogFile,posKeywords,negKeywords from " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_new where packageId =?");
    $sql1->execute([$id]);
    $sql2Res = $sql1->fetch(PDO::FETCH_ASSOC);

    $maxTime = $sql2Res['maxTime'];
    $preInstallMsg = $sql2Res['preInstallMsg'];
    $postDownloadMsg = $sql2Res['postDownloadMsg'];
    $processToKill = $sql2Res['processToKill'];
    $logFilesToRead = $sql2Res['logFilesToRead'];
    $defaultRead = $sql2Res['defaultRead'];
    $deleteLogFile = $sql2Res['deleteLogFile'];
    $posKeywords = $sql2Res['posKeywords'];
    $negKeywords = $sql2Res['negKeywords'];

    $list = array(
        'path' => $path, 'path2' => $path2, 'platform' => $platform, 'packagename' => $pack, 'configType' => $configtype, 'maxTime' => $maxTime,
        'preInstallMsg' => $preInstallMsg, 'postDownloadMsg' => $postDownloadMsg, 'processToKill' => $processToKill, 'logFilesToRead' => $logFilesToRead,
        'defaultRead' => $defaultRead, 'deleteLogFile' => $deleteLogFile, 'posKeywords' => $posKeywords, 'negKeywords' => $negKeywords
    );

    echo json_encode($list);
}

function checkConfigureStatus()
{
    $id = url::postToText('id');
    $db = pdo_connect();

    $sql1 = $db->prepare("select 32bitConfig, 64bitConfig from " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_new where packageId =?");
    $sql1->execute([$id]);
    $sql1Res = $sql1->fetch(PDO::FETCH_ASSOC);
    $configStatus = $sql1Res['32bitConfig'] || $sql1Res['64bitConfig'];

    if ($configStatus) {
        echo "1";
    } else {
        echo  "0";
    }
}

function getSelectedConfigVal()
{
    $id = url::requestToAny('id');
    $type = url::requestToAny('type');

    $db = pdo_connect();
    $sql1 = $db->prepare("SELECT $type FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_new WHERE packageId=?");
    $sql1->execute([$id]);
    $sql1Res = $sql1->fetch(PDO::FETCH_ASSOC);
    $returnValue = $sql1Res[$type];

    echo $returnValue;
}

function get32bitConfig()
{
    $id = url::requestToAny('id');
    $db = pdo_connect();
    $sql1 = $db->prepare("SELECT  line1,line2,line3,line4,line5,line6 FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_32 WHERE packageId=?"); //sdfsfsdf
    $sql1->execute([$id]);
    $sql1Res = $sql1->fetch(PDO::FETCH_ASSOC);
    $line1 = $sql1Res['line1'];
    $line2 = $sql1Res['line2'];
    $line3 = $sql1Res['line3'];
    $line4 = $sql1Res['line4'];
    $line5 = $sql1Res['line5'];
    $line6 = $sql1Res['line6'];
    $recordList = array("line1" => $line1, "line2" => $line2, "line3" => $line3, "line4" => $line4, "line5" => $line5, "line6" => $line6);

    print_r(json_encode($recordList));
}

function get64bitConfig()
{
    $id = url::requestToAny('id');
    $db = pdo_connect();
    $sql1 = $db->prepare("SELECT  line1,line2,line3,line4,line5,line6 FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_64 WHERE packageId=?");
    $sql1->execute([$id]);
    $sql1Res = $sql1->fetch(PDO::FETCH_ASSOC);
    $line1 = $sql1Res['line1'];
    $line2 = $sql1Res['line2'];
    $line3 = $sql1Res['line3'];
    $line4 = $sql1Res['line4'];
    $line5 = $sql1Res['line5'];
    $line6 = $sql1Res['line6'];
    $recordList = array("line1" => $line1, "line2" => $line2, "line3" => $line3, "line4" => $line4, "line5" => $line5, "line6" => $line6);

    print_r(json_encode($recordList));
}

function getResetConfig()
{
    $id = url::requestToAny('id');

    $db = pdo_connect();
    $sql1 = $db->prepare("SELECT  line3,line4,line5,line6 FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_32 WHERE packageId=?");
    $sql1->execute([$id]);
    $sql1Res = $sql1->fetch(PDO::FETCH_ASSOC);
    $line3_32 = $sql1Res['line3'];
    $line4_32 = $sql1Res['line4'];
    $line5_32 = $sql1Res['line5'];
    $line6_32 = $sql1Res['line6'];

    $sql2 = $db->prepare("SELECT  line3,line4,line5,line6 FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration_64 WHERE packageId=?");
    $sql2->execute([$id]);
    $sql2Res = $sql2->fetch(PDO::FETCH_ASSOC);
    $line3_64 = $sql2Res['line3'];
    $line4_64 = $sql2Res['line4'];
    $line5_64 = $sql2Res['line5'];
    $line6_64 = $sql2Res['line6'];

    $recordList = array(
        "line3_32" => $line3_32, "line4_32" => $line4_32, "line5_32" => $line5_32, "line6_32" => $line6_32,
        "line3_64" => $line3_64, "line4_64" => $line4_64, "line5_64" => $line5_64, "line6_64" => $line6_64
    );
    print_r(json_encode($recordList));
}
