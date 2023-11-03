<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

include_once 'CurlWrapper.php';

$urlreq = url::issetInRequest('url') ? url::requestToAny('url') : "";
$methodreq = url::issetInRequest('method') ? url::requestToAny('method') : "";
$selSku = url::issetInRequest('selSku') ? url::requestToAny('selSku') : "";
$compId = url::issetInRequest('compId') ? url::requestToAny('compId') : "";
$funcn = url::issetInRequest('funcn') ? url::requestToAny('funcn') : "";

if ($urlreq != "") {

    $postdata_req = safe_json_decode(file_get_contents("php://input"));

    if ($funcn == 'savecard') {

        if ($selSku != "") {

            $result = saveCCDetails($urlreq, $postdata_req);
            $cardRes = json_encode($result);

            if ($result->status == 'success') {
                $key = generateMSPKey($compId, $selSku);
                $keyresult = array("status" => "success", "key" => $key);
                $_SESSION["user"]["payinfo"] = "1";
                echo json_encode($keyresult);
            } else {
                $keyresult = array("status" => "error", "key" => "");
                echo json_encode($keyresult);
            }
        } else {

            $keyresult = array("status" => "error", "key" => "");
            echo json_encode($result);
        }
    } else {

        $result = DashboardAPI($methodreq, $urlreq, $postdata_req);
        echo json_encode($result);
    }
}

function DashboardAPI($method, $url, $postdata = false)
{
    global $apiurl;
    $res =  safe_json_decode(CURL::getContentByURL($apiurl, $postdata));

    // logs::log(__FILE__, __LINE__, "DashboardAPI: result", $res);
    return $res;
}
