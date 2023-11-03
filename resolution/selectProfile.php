<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once "../include/common_functions.php";
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-util.php';

global $db;
$db = pdo_connect();

function getprofile($data)
{

    $res = checkModulePrivilege('resolution', 2);
    if (!$res) {
        echo 'Permission denied';
        exit();
    }

    global $redis_url;
    global $redis_port;
    global $redis_pwd;
    $redis = new Redis();
    $redis->connect($redis_url, $redis_port);
    $redis->auth($redis_pwd);

    $redis->select(0);

    $searchtype = $_SESSION["searchType"];
    $searchValue = $_SESSION["searchValue"];
    $key = '';
    $profileShow = [];

    if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {

        $Redisres = $redis->lrange("$searchValue", 0, -1);
        $rmselectprofile = 'Windows';
        if (stripos($Redisres[4], 'OS X') !== false) {
            $rmselectprofile = 'Mac';
            $_SESSION['notifyshowProfile'] = "Mac";
        } else if (stripos($Redisres[4], 'Windows') !== false) {
            $rmselectprofile = 'Windows';
            $_SESSION['notifyshowProfile'] = "Windows";
        } else if (stripos($Redisres[4], 'Android') !== false) {
            $rmselectprofile = 'Android';
            $_SESSION['notifyshowProfile'] = "Android";
        } else if (stripos($Redisres[4], 'Linux') !== false) {
            $rmselectprofile = 'Linux';
            $_SESSION['notifyshowProfile'] = "Linux";
        } else if (stripos($Redisres[4], 'iOS') !== false) {
            $rmselectprofile = 'iOS';
            $_SESSION['notifyshowProfile'] = "iOS";
        }
        if (url::issetInRequest('type') && url::requestToText('type') === '1') {
            echo $rmselectprofile;
        }
    } else if ($searchtype == 'Site' || $searchtype == 'Sites') {

        $siteScope    = UTIL_GetSiteScope($db, $searchValue, $searchtype);
        $sitemachines = DASH_GetMachinesSites($key, $db, $siteScope);

        foreach ($sitemachines as $key => $value) {
            $Redisres = $redis->lrange("$sitemachines[$key]", 0, -1);
            if (safe_count($Redisres) > 0) {
                if (stripos($Redisres[4], 'OS X') !== false) {
                    $profileShow[] = 'Mac';
                }
                if (stripos($Redisres[4], 'Windows') !== false) {
                    $profileShow[] = 'Windows';
                }
                if (stripos($Redisres[4], 'Android') !== false) {
                    $profileShow[] = 'Android';
                }
                if (stripos($Redisres[4], 'Linux') !== false) {
                    $profileShow[] = 'Linux';
                }
                if (stripos($Redisres[4], 'iOS') !== false) {
                    $profileShow[] = 'iOS';
                }
            }
        }

        if (in_array("Mac", $profileShow) && in_array("Windows", $profileShow) && in_array("Android", $profileShow) && in_array("Linux", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "All";
        } else if (in_array("Mac", $profileShow) && in_array("Windows", $profileShow) && in_array("Android", $profileShow) && in_array("Linux", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MWAL";
        } else if (in_array("Mac", $profileShow) && in_array("Android", $profileShow) && in_array("Linux", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MALI";
        } else if (in_array("Windows", $profileShow) && in_array("Android", $profileShow) && in_array("Linux", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WALI";
        } else if (in_array("Mac", $profileShow) && in_array("Windows", $profileShow) && in_array("Android", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MWAI";
        } else if (in_array("Mac", $profileShow) && in_array("Windows", $profileShow) && in_array("Linux", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MWLI";
        } else if (in_array("Mac", $profileShow) && in_array("Windows", $profileShow) && in_array("Android", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WMA";
        } else if (in_array("Windows", $profileShow) && in_array("Linux", $profileShow) && in_array("Android", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WLA";
        } else if (in_array("Windows", $profileShow) && in_array("iOS", $profileShow) && in_array("Android", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WIA";
        } else if (in_array("Windows", $profileShow) && in_array("Linux", $profileShow) && in_array("Mac", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WLM";
        } else if (in_array("Windows", $profileShow) && in_array("Linux", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WLI";
        } else if (in_array("Windows", $profileShow) && in_array("Mac", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WMI";
        } else if (in_array("Mac", $profileShow) && in_array("Android", $profileShow) && in_array("Linux", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MAL";
        } else if (in_array("Mac", $profileShow) && in_array("iOS", $profileShow) && in_array("Android", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MIA";
        } else if (in_array("Mac", $profileShow) && in_array("iOS", $profileShow) && in_array("Linux", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MIL";
        } else if (in_array("Mac", $profileShow) && in_array("Windows", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WM";
        } else if (in_array("Android", $profileShow) && in_array("Windows", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WA";
        } else if (in_array("Linux", $profileShow) && in_array("Windows", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WL";
        } else if (in_array("iOS", $profileShow) && in_array("Windows", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WI";
        } else if (in_array("Mac", $profileShow) && in_array("Android", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MA";
        } else if (in_array("Mac", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MI";
        } else if (in_array("Mac", $profileShow) && in_array("Linux", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "ML";
        } else if (in_array("Android", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "AI";
        } else if (in_array("Android", $profileShow) && in_array("Linux", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "AL";
        } else if (in_array("Mac", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "Mac";
        } else if (in_array("Windows", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "Windows";
        } else if (in_array("Android", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "Android";
        } else if (in_array("Linux", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "Linux";
        } else if (in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "iOS";
        }
    } else if ($searchtype == 'Group' || $searchtype == 'Groups') {

        $groupScope    = UTIL_GetSiteScope($db, $searchValue, $searchtype);
        $groupmachines = DASH_GetGroupsMachines($key, $db, $groupScope);

        foreach ($groupmachines as $key => $value) {
            $Redisres = $redis->lrange("$groupmachines[$key]", 0, -1);
            if (safe_count($Redisres) > 0) {
                if (stripos($Redisres[4], 'OS X') !== false) {
                    $profileShow[] = 'Mac';
                }
                if (stripos($Redisres[4], 'Windows') !== false) {
                    $profileShow[] = 'Windows';
                }
                if (stripos($Redisres[4], 'Android') !== false) {
                    $profileShow[] = 'Android';
                }
                if (stripos($Redisres[4], 'Linux') !== false) {
                    $profileShow[] = 'Linux';
                }
                if (stripos($Redisres[4], 'iOS') !== false) {
                    $profileShow[] = 'iOS';
                }
            }
        }

        if (in_array("Mac", $profileShow) && in_array("Windows", $profileShow) && in_array("Android", $profileShow) && in_array("Linux", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "All";
        } else if (in_array("Mac", $profileShow) && in_array("Windows", $profileShow) && in_array("Android", $profileShow) && in_array("Linux", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MWAL";
        } else if (in_array("Mac", $profileShow) && in_array("Android", $profileShow) && in_array("Linux", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MALI";
        } else if (in_array("Windows", $profileShow) && in_array("Android", $profileShow) && in_array("Linux", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WALI";
        } else if (in_array("Mac", $profileShow) && in_array("Windows", $profileShow) && in_array("Android", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MWAI";
        } else if (in_array("Mac", $profileShow) && in_array("Windows", $profileShow) && in_array("Linux", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MWLI";
        } else if (in_array("Mac", $profileShow) && in_array("Windows", $profileShow) && in_array("Android", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WMA";
        } else if (in_array("Windows", $profileShow) && in_array("Linux", $profileShow) && in_array("Android", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WLA";
        } else if (in_array("Windows", $profileShow) && in_array("iOS", $profileShow) && in_array("Android", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WIA";
        } else if (in_array("Windows", $profileShow) && in_array("Linux", $profileShow) && in_array("Mac", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WLM";
        } else if (in_array("Windows", $profileShow) && in_array("Linux", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WLI";
        } else if (in_array("Windows", $profileShow) && in_array("Mac", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WMI";
        } else if (in_array("Mac", $profileShow) && in_array("Android", $profileShow) && in_array("Linux", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MAL";
        } else if (in_array("Mac", $profileShow) && in_array("iOS", $profileShow) && in_array("Android", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MIA";
        } else if (in_array("Mac", $profileShow) && in_array("iOS", $profileShow) && in_array("Linux", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MIL";
        } else if (in_array("Mac", $profileShow) && in_array("Windows", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WM";
        } else if (in_array("Android", $profileShow) && in_array("Windows", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WA";
        } else if (in_array("Linux", $profileShow) && in_array("Windows", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WL";
        } else if (in_array("iOS", $profileShow) && in_array("Windows", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "WI";
        } else if (in_array("Mac", $profileShow) && in_array("Android", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MA";
        } else if (in_array("Mac", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "MI";
        } else if (in_array("Mac", $profileShow) && in_array("Linux", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "ML";
        } else if (in_array("Android", $profileShow) && in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "AI";
        } else if (in_array("Android", $profileShow) && in_array("Linux", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "AL";
        } else if (in_array("Mac", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "Mac";
        } else if (in_array("Windows", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "Windows";
        } else if (in_array("Android", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "Android";
        } else if (in_array("Linux", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "Linux";
        } else if (in_array("iOS", $profileShow)) {
            $_SESSION['notifyshowProfile'] = "iOS";
        }
    }
    $redis->close();
}
