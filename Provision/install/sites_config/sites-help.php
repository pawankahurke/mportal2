<?php

/*
  Revision history:

  Date        Who     What
  ---------   ---     ----
  13-Dec-19   SVG     Created File.

 *  */

//error_reporting(-1);
//ini_set('display_errors', 'On');

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once '../../lib/l-db.php';
include_once '../../lib/l-sql.php';
include_once '../../lib/l-dberr.php';
include_once '../../lib/l-config.php';


//Following code will check $_REQUEST array and takes its function parameter value.
//Respective function will get called.
if (url::issetInRequest('function')) { // roles: user
    nhRole::dieIfnoRoles(['user']); // roles: user
    $functionName = url::requestToText('function');
    $functionName($_REQUEST);
}

function get_SKUForCust_ajx($data)
{
    nhRole::dieIfnoRoles(['user']); // roles: user
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'install', $db);

    $offerings = [];

    $custId = isset($data['custId']) ? $data['custId'] : '';
    if ($custId !== '') {
        $offerings = get_configuredSku_ajx($custId, $db);
    }
    echo json_encode($offerings);
}

function get_configuredSku_ajx($selectedCustId, $db)
{

    $skulist = [];
    $offerings = [];
    $sql = "SELECT sku_list FROM Customers WHERE cid = '$selectedCustId'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $skulist[] = $row['sku_list'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }


    $offerids = implode(',', $skulist);
    $offerSql = "SELECT * FROM skuOfferings where sid IN ($offerids)";
    $offerRes = command($offerSql, $db);
    if ($offerRes) {
        if (mysqli_num_rows($offerRes)) {
            while ($row = mysqli_fetch_array($offerRes)) {
                $offerings['sid'][] = $row['sid'];
                $offerings['name'][] = $row['name'];
            }
        }
        ((mysqli_free_result($offerRes) || (is_object($offerRes) && (get_class($offerRes) == "mysqli_result"))) ? true : false);
    }

    return $offerings;
}
