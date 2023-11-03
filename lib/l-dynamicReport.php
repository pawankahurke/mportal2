<?php



include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'l-db.php';
include_once 'l-sql.php';
include_once 'l-gsql.php';
include_once 'l-rcmd.php';

nhRole::dieIfnoRoles(['agentworkspace']); // roles: agentworkspace
if (url::issetInRequest('function')) { // roles: agentworkspace
    funcCallViaAjax();
}

function funcCallViaAjax()
{
    $function = url::requestToText('functionToCall'); // roles: agentworkspace
    $function(TRUE);
}

function getEventFilters($viaAjax)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'event', $db);

    $limit = '';
    if (url::issetInRequest('limit')) {
        $limitval = url::requestToAny('limit');
        $limit = "limit 0, $limitval";
    }

    $sql = "SELECT id,name,eventtag FROM " . $GLOBALS['PREFIX'] . "event.SavedSearches where eventtag !='' order by name";
    $result = find_many($sql, $db);
    $i = 0;

    foreach ($result as $key => $val) {
        $return[$i]['id'] = $val['id'];
        $return[$i]['name'] = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($val['name']));
        $return[$i]['etag'] = $val['eventtag'];
        $i++;
    }
    $return[0]['groupby'] = '{"val":"Machine:Site:User Name:Scrip:Executable:Window Title"}';
    if ($viaAjax) {
        echo json_encode($return);
    } else {
        return $return;
    }
}

function getAssetQueries($viaAjax)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'asset', $db);

    $username = $_SESSION['user']['logged_username'];
    $sql = "SELECT id,name,displayfields FROM " . $GLOBALS['PREFIX'] . "asset.AssetSearches where global=1 or username='$username' order by name";
    $result = find_many($sql, $db);

    $i = 0;
    $return[0]['groupby'] = "{";
    foreach ($result as $key => $val) {
        $return[$i]['id'] = $val['id'];
        $return[$i]['name'] = $val['name'];
        $return[0]['groupby'] .= '"' . $val['id'] . '":"' . preg_replace('/[^A-Za-z0-9\. -:]/', '', $val['displayfields']) . '",';
        $i++;
    }
    if ($viaAjax) {
        $return[0]['groupby'] = rtrim($return[0]['groupby'], ",") . "}";
        echo json_encode($return);
    } else {
        $return[0]['groupby'] = rtrim($return[0]['groupby'], ",") . "}";
        return $return;
    }
}

function getScripList($viaAjax)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'core', $db);

    $sql = "select distinct name, num from Scrips group by num order by num";
    $res = find_many($sql, $db);

    $scripList = "";
    foreach ($res as $key => $value) {
        $scripList .= "<option value='" . $value['num'] . "'>" . $value['num'] . " ( " . $value['name'] . " )</option>";
    }
    $scripList .= "<option value='31'>31 (System Start Up)</option>";
    if ($viaAjax) {
        echo $scripList;
    } else {
        echo 0;
    }
}
