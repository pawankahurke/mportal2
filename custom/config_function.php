<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-quer.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-dashboard.php';
require_once '../include/common_functions.php';



if (!isset($_SESSION)) {
}
nhRole::dieIfnoRoles(['user']);

//Replace $routes['post'] with if else
if (url::postToText('function') === 'submit_config_browsercheck') { //roles: user
    submit_config_browsercheck();
} else if (url::postToText('function') === 'submit_config_kioskcheck') { //roles: user
    submit_config_kioskcheck();
} else if (url::postToText('function') === 'fetch_editdetails') { //roles: user
    fetch_editdetails();
} else if (url::postToText('function') === 'update_Config') { //roles: user
    update_Config();
}

//Replace $routes['get'] with if else
if (url::postToText('function') === 'get_AllBrowserDetails') { //roles: user
    get_AllBrowserDetails();
} else if (url::postToText('function') === 'get_SitesDetails') { //roles: user
    get_SitesDetails();
}




function submit_config_browsercheck()
{
    $siteEmailData = submitconfig_browser();
    echo json_encode($siteEmailData);
}

function submit_config_kioskcheck()
{
    $siteEmailData = submitconfig_kiosk();
    echo json_encode($siteEmailData);
}

function TotalSites()
{
    $db = pdo_connect();
    $username = $_SESSION["user"]["username"];
    $userid = $_SESSION["user"]["userid"];
    $siteList = $_SESSION["user"]["site_list"];
    $newArray = array();
    foreach ($siteList as $value) {
        $siteData = $value;
        array_push($newArray, $siteData);
    }

    return $newArray;
}

function get_SitesDetails()
{
    $userRes = TotalSites();
    $userListData = '';
    if (safe_count($userRes) > 0) {
        foreach ($userRes as $key => $value) {
            if ($value != '') {
                $userListData .= '<option value="' . $value . '">' . $value . '</option>';
            }
        }
    } else {
        $userListData = '<option value="">No Sites Available</option>';
    }
    ob_clean();
    echo $userListData;
}

function get_AllBrowserDetails()
{
    global $base_url;
    $db = pdo_connect();
    $sql1 = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "profile.ConfigBrowser");
    $sql1->execute();
    $res1 = $sql1->fetchAll();
    $finalArray = array();
    $newArray1 = array();
    $newArray2 = array();
    $i = 1;
	if ($res1) {
	$auditRes = create_auditLog('Configuration Browser', 'Browser View', 'Success');
    }
	
    foreach ($res1 as $values) {
        $newArray1['id'] = $i;
        $newArray1['sitename'] = $values['sitename'];
        $newArray1['Type'] = "Configuration Browser";
        $sitename = $newArray1['sitename'];
        $addUrl = "custom/json_browser.php?site=$sitename";
        $newArray1['Url'] = $base_url . $addUrl;
        $newArray1['orignalid'] = $values['id'];
        $i++;
        array_push($finalArray, $newArray1);
    }
		
    $sql2 = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "profile.ConfigKiosk");
    $sql2->execute();
    $res2 = $sql2->fetchAll();
	if ($res2) {
	$auditRes = create_auditLog('Configuration Browser', 'Kiosk View', 'Success');
    }
	
    foreach ($res2 as $values) {
        $newArray2['id'] = $i;
        $newArray2['sitename'] = $values['sitename'];
        $newArray2['Type'] = "Configuration Kiosk";
        $sitename = $newArray2['sitename'];
        $addUrl = "custom/json_kiosk.php?site=$sitename";
        $newArray2['Url'] = $base_url . $addUrl;
        $newArray2['orignalid'] = $values['id'];
        $i++;
        array_push($finalArray, $newArray2);
    }
		   
    if (safe_sizeof($finalArray) > 0) {
        foreach ($finalArray as $key => $val) {
            $id   = $val['id'];
            $host = $val['sitename'];
            $type = $val['Type'];
            $url = $val['Url'];
            $orgid = $val['orignalid'];
            $finalList[] = array($id, $host, $type, $url, $orgid);
        }
    } else {
        $finalList = [];
    }
		
    echo json_encode($finalList);
}

function submitconfig_browser()
{
    $db = pdo_connect();
    $browsersitename = url::postToText('browsersitename');
    $scripStatus = url::postToText('scripStatus');
    $appdownlURL = url::postToText('appdownlURL');
    $defaultURL = url::postToText('defaultURL');
    $accessRule = url::postToText('accessRule');
    $keywordfilter = url::postToText('keywordfilter');
    $bookmarks = url::postToText('bookmarks');
    $monitorTimest = url::postToText('monitorTimest');
    $restrictFileDownl = url::postToText('restrictFileDownl');
    $disableCookies = url::postToText('disableCookies');
    $clearCache = url::postToText('clearCache');
    $clearHistory = url::postToText('clearHistory');
    $disableBookmark = url::postToText('disableBookmark');
    $clearBookmarks = url::postToText('clearBookmarks');
    $disableCopyPaste = url::postToText('disableCopyPaste');
    $blockedPopUp = url::postToText('blockedPopUp');
    $disableFraudWarning = url::postToText('disableFraudWarning');
    $printPage = url::postToText('printPage');
    $schedTime = url::postToText('schedTime');
    $contentBlocking = url::postToText('contentBlocking');

    $arrayValues = [$browsersitename, $scripStatus, $appdownlURL, $defaultURL, $accessRule, $keywordfilter, $bookmarks, $monitorTimest, $restrictFileDownl, $disableCookies, $clearCache, $clearHistory, $disableBookmark, $clearBookmarks, $disableCopyPaste, $blockedPopUp, $disableFraudWarning, $printPage, $schedTime, $contentBlocking];
    $sql = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "profile.ConfigBrowser (sitename, scripStatus, appdownlURL, defaultURL, accessRule, keywordfilter, bookmarks, monitorTimest, restrictFileDownl, disableCookies, clearHistory, clearCache, disableBookmark, clearBookmarks, disableCopyPaste, blockedPopUp, disableFraudWarning, printPage, schedTime, contentBlocking) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $sql->execute($arrayValues);
    $res = $db->lastInsertId();
	
    if (!$res) {
        create_auditLog('Configuration Browser', 'Browser Insert', 'Failed', $arrayValues, 'submitconfig_browser()');
        return 0;
    } else {
        create_auditLog('Configuration Browser', 'Browser Insert', 'Success', $arrayValues, 'submitconfig_browser()');
        return 1;
    }
}

function submitconfig_kiosk()
{
    $db = pdo_connect();
    $kioskidsitename = url::postToText('kioskidsitename');
    $scripStatuskio = url::postToText('scripStatuskio');
    $userlist = url::postToText('userlist');
    $kioskProfiles = url::postToText('kioskProfiles');
    $userConfig = url::postToText('userConfig');
    $kioskconfigProfiles = url::postToText('kioskconfigProfiles');
    $lockScreen = url::postToText('lockScreen');
    $enableEmergency = url::postToText('enableEmergency');
    $emergencyContacts = url::postToText('emergencyContacts');
    $arrayValues = [$kioskidsitename, $scripStatuskio, $userlist, $kioskProfiles, $userConfig, $kioskconfigProfiles, $lockScreen, $enableEmergency, $emergencyContacts];
    $Sql = $db->prepare("Insert into " . $GLOBALS['PREFIX'] . "profile.ConfigKiosk (sitename,scripStatus,userlist,kioskProfiles,userConfig,kioskconfigProfiles,lockScreen,enableEmergency,emergencyContacts) VALUES (?,?,?,?,?,?,?,?,?)");;
    $Sql->execute($arrayValues);
    $res = $db->lastInsertId();
	
    if (!$res) {
        create_auditLog('Configuration Browser', 'kiosk Insert', 'Failed', $arrayValues, 'submitconfig_kiosk()');
        return 0;
    } else {
        create_auditLog('Configuration Browser', 'kiosk Insert', 'Success', $arrayValues, 'submitconfig_kiosk()');
        return 1;
    }
}

function fetch_editdetails()
{
    $type = url::postToText('type');
    $id = url::postToText('id');
    $userListData = '';
    $db = pdo_connect();
    if ($type === 'Configuration Browser') {
        $sql = $db->prepare("Select * from " . $GLOBALS['PREFIX'] . "profile.ConfigBrowser where id = ?");
        $sql->execute([$id]);
    } else {
        $sql = $db->prepare("Select * from " . $GLOBALS['PREFIX'] . "profile.ConfigKiosk where id = ?");
        $sql->execute([$id]);
    }
    $res = $sql->fetch();
    $sitename = $res['sitename'];
    $totalsites = TotalSites();
    $userListData = '';
    if (safe_count($totalsites) > 0) {
        foreach ($totalsites as $key => $value) {
            $selected = '';
            if ($value === $sitename) {
                $selected = 'selected';
            }
            if ($value != '') {
                $userListData .= '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
            }
        }
    } else {
        $userListData = '<option value="">No Sites Available</option>';
    }
    ob_clean();
    $res['sitename'] = $userListData;
    echo json_encode($res);
}

function update_Config()
{
    $type = url::postToText('type');
    $db = pdo_connect();
    if ($type === 'browser') {
        $browserid = url::postToText('browserid');
        $browsersitename = url::postToText('browsersitename');
        $scripStatus = url::postToText('scripStatus');
        $appdownlURL = url::postToText('appdownlURL');
        $defaultURL = url::postToText('defaultURL');
        $accessRule = url::postToText('accessRule');
        $keywordfilter = url::postToText('keywordfilter');
        $bookmarks = url::postToText('bookmarks');
        $monitorTimest = url::postToText('monitorTimest');
        $restrictFileDownl = url::postToText('restrictFileDownl');
        $disableCookies = url::postToText('disableCookies');
        $clearCache = url::postToText('clearCache');
        $clearHistory = url::postToText('clearHistory');
        $disableBookmark = url::postToText('disableBookmark');
        $clearBookmarks = url::postToText('clearBookmarks');
        $disableCopyPaste = url::postToText('disableCopyPaste');
        $blockedPopUp = url::postToText('blockedPopUp');
        $disableFraudWarning = url::postToText('disableFraudWarning');
        $printPage = url::postToText('printPage');
        $schedTime = url::postToText('schedTime');
        $contentBlocking = url::postToText('contentBlocking');
        $arrayValues = [$browsersitename, $scripStatus, $appdownlURL, $defaultURL, $accessRule, $keywordfilter, $bookmarks, $monitorTimest, $restrictFileDownl, $disableCookies, $clearHistory, $clearCache, $disableBookmark, $clearBookmarks, $disableCopyPaste, $blockedPopUp, $disableFraudWarning, $printPage, $schedTime, $contentBlocking, $browserid];
        $sqlupdate = $db->prepare("update " . $GLOBALS['PREFIX'] . "profile.ConfigBrowser set sitename=?, scripStatus=?,appdownlURL=?, defaultURL=?,
                    accessRule=?,keywordfilter=?,bookmarks=?,monitorTimest=?,restrictFileDownl=?,disableCookies=?,
                    clearHistory=?,clearCache=?,disableBookmark=?,clearBookmarks=?,disableCopyPaste=?,blockedPopUp=?,disableFraudWarning=?,
                    printPage=?,schedTime=?,contentBlocking=? where id = ?");
        $result = $sqlupdate->execute($arrayValues);
    } else {
        $kioskid = strip_tags($_POST['kioskid']);
        $kioskidsitename = strip_tags($_POST['kioskidsitename']);
        $scripStatuskio = strip_tags($_POST['scripStatuskio']);
        $userlist = strip_tags($_POST['userlist']);
        $kioskProfiles = strip_tags($_POST['kioskProfiles']);
        $userConfig = strip_tags($_POST['userConfig']);
        $kioskconfigProfiles = strip_tags($_POST['kioskconfigProfiles']);
        $lockScreen = strip_tags($_POST['lockScreen']);
        $enableEmergency = strip_tags($_POST['enableEmergency']);
        $emergencyContacts = strip_tags($_POST['emergencyContacts']);
        $arrayValues = [$kioskidsitename, $scripStatuskio, $userlist, $kioskProfiles, $userConfig, $kioskconfigProfiles, $lockScreen, $enableEmergency, $emergencyContacts, $kioskid];
        $sqlupdate = $db->prepare("update " . $GLOBALS['PREFIX'] . "profile.ConfigKiosk set sitename=?,scripStatus=?,userlist=?,kioskProfiles=?,
                    userConfig=?,kioskconfigProfiles=?,lockScreen=?,enableEmergency=?,emergencyContacts=? where id = ?");
        $result = $sqlupdate->execute($arrayValues);
    }
    $result = $db->lastInsertId();
    if ($result) {
        create_auditLog('Configuration Browser', 'Kiosk Insert', 'Success', $arrayValues, 'update_Config()');
        echo "Success";
    } else {
        create_auditLog('Configuration Browser', 'Kiosk Insert', 'Failed', $arrayValues, 'update_Config()');
        echo "Failed";
    }
}
