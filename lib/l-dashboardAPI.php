<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once 'CurlWrapper.php';



$urlreq     =   url::issetInRequest('url') ? url::requestToText('url') : "";
$methodreq  =   url::issetInRequest('method') ? url::requestToText('method') : "";
$selSku     =   url::issetInRequest('selSku') ? url::requestToText('selSku') : "";
$compId     =   url::issetInRequest('compId') ? url::requestToText('compId') : "";
$funcn      =   url::issetInRequest('funcn') ? url::requestToText('funcn') : "";

if($urlreq != ""){

    $postdata_req = safe_json_decode(file_get_contents("php://input"));

    if($funcn == 'savecard') {

        if($selSku != ""){


            $result  = saveCCDetails($urlreq, $postdata_req);
            $cardRes = json_encode($result);

            if($result->status == 'success'){
                $key = generateMSPKey($compId,$selSku);
                $keyresult =  array("status"=>"success","key" => $key);
                $_SESSION["user"]["payinfo"] = "1";
                echo json_encode($keyresult);
            } else {
                $keyresult =  array("status"=>"error","key" => "");
                echo json_encode($keyresult);
            }
         } else {

                $keyresult =  array("status"=>"error","key" => "");
                echo json_encode($result);
        }

    } else {

        $result=Dashboard_API($methodreq, $urlreq,$postdata_req);
        echo json_encode($result);
    }
}

function Dashboard_API($method, $url, $postdata = false){
    global $NH_API_URL;
    $errcnt=0;

    callBackRequest:{
        try {
                $curl = new CurlWrapper();
            } catch (CurlWrapperException $e) {
                echo $e->getMessage();
            }

            $access_token="";
            $baseURL=$NH_API_URL;
            $email='admin@nanoheal.com';
            $password='nanoheal@123';
                    if(!isset($_SESSION["accesstoken"])) {
            $curl1 = new CurlWrapper();
            $curl1->addHeader('Content-Type', 'application/json');
            $curl1->addOption(CURLINFO_HEADER_OUT, true);

            $postobj=array('email'=>'admin@nanoheal.com','password'=>'nanoheal@123');
            $response = $curl1->rawPost($baseURL."login",json_encode($postobj));

            $res_obj=safe_json_decode($response);

            if($res_obj->status=="error"){
                return  $response;
            }else{
                $access_token=$res_obj->result->access_token;
                $_SESSION["accesstoken"]=$access_token;
            }
        }else{
            $access_token=$_SESSION["accesstoken"];
        }

        if($errcnt==0){
                try {
                    $curl = new CurlWrapper();
                } catch (CurlWrapperException $e) {
                    echo $e->getMessage();
                }


                                $curl->addHeader('Content-Type', 'application/json');
                $curl->addOption(CURLINFO_HEADER_OUT, true);
                $curl->addHeader('Authorization', 'Bearer '.$access_token);



            switch ($method){
                case "POST":
                    $response = $curl->rawPost($baseURL.$url,json_encode($postdata));
                    break;
                case "PATCH":
                    $response = $curl->rawPatchPost($baseURL.$url,json_encode($postdata));
                    break;
                case "GET":
                    $response = $curl->get($baseURL.$url);
                    break;
                                                                default:
                        return array("error"=>"Method not found");
                    break;
            }
            $req=$curl->getTransferInfo();
                                    $resObj=safe_json_decode($response);
                                        if($resObj->status=="error"){
                    if($resObj->error->message=="Unauthorized"){
                        unset($_SESSION["accesstoken"]);
                        goto callBackRequest;
                    }else{
                        return $resObj;
                    }
                }else{
                    return $resObj;
                }

            }


        }
}








function generateMSPKey($eid,$skuid){

    $db = pdo_connect();

    $sql_chnl = $db->prepare("select C.eid,C.companyName from ".$GLOBALS['PREFIX']."agent.channel C where C.eid=?");
    $sql_chnl->execute([$eid]);
    $res = $sql_chnl->fetch();
    if(safe_count($res)>0){

        $update_sql = $db->prepare("update ".$GLOBALS['PREFIX']."agent.channel set skulist=?,payInfo='1' where eid = ?");
        $update_sql->execute([$skuid,$eid]);
        $result_update = $db->lastInsertId();
        if($result_update){
            $chksum = md5(mt_rand());
            $ordnum = rand(1000000, 9999999999);
            $dt = time();
            $sql_ord = $db->prepare("INSERT INTO ".$GLOBALS['PREFIX']."agent.orderDetails (chnl_id, orderNum, skuNum, skuDesc, licenseCnt, installCnt, purchaseDate, orderDate, contractEndDate, noofDays, payRefNum, transRefNum, trial, nh_lic, amount, aviraOtc, crmOrderId, uuid, licenseKey, orderType, sku_id, status, download_url) "
                    . "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $sql_ord->execute([$eid,$ordnum, '', '', '0', '0', $dt, $dt, $dt, '365', '', '', '0', '1', '0', NULL, NULL, NULL, '$chksum', NULL, '$skuid', '1', NULL]);
            $res_ord = $db->lastInsertId();
            if($res_ord){
                return $chksum;
            }


        }
    }
}


function saveCCDetails($url, $postdata){

    global $NH_API_URL;
    $errcnt=0;
    callBackRequest:{
        try {
                $curl = new CurlWrapper();
            } catch (CurlWrapperException $e) {
                echo $e->getMessage();
            }

            $access_token="";
            $baseURL=$NH_API_URL;
            $email='admin@nanoheal.com';
            $password='nanoheal@123';

        if(!isset($_SESSION["accesstoken"])) {
            $curl1 = new CurlWrapper();
            $curl1->addHeader('Content-Type', 'application/json');
            $curl1->addOption(CURLINFO_HEADER_OUT, true);

            $postobj=array('email'=>'admin@nanoheal.com','password'=>'nanoheal@123');
            $response = $curl1->rawPost($baseURL."login",json_encode($postobj));

            $res_obj=safe_json_decode($response);

            if($res_obj->status=="error"){
                return  $response;
            }else{
                $access_token=$res_obj->result->access_token;
                $_SESSION["accesstoken"]=$access_token;
            }
        } else {
            $access_token=$_SESSION["accesstoken"];
        }

        if($errcnt==0){
            try {
                $curl = new CurlWrapper();
            } catch (CurlWrapperException $e) {
                echo $e->getMessage();
            }

            $curl->addHeader('Content-Type', 'application/json');
            $curl->addOption(CURLINFO_HEADER_OUT, true);
            $curl->addHeader('Authorization', 'Bearer '.$access_token);
            $response = $curl->rawPost($baseURL.$url,json_encode($postdata));

            $req=$curl->getTransferInfo();
            $resObj=safe_json_decode($response);

            if($resObj->status=="error"){
                if($resObj->error->message=="Unauthorized"){
                    unset($_SESSION["accesstoken"]);
                    goto callBackRequest;
                }else{
                    return $resObj;
                }
            } else{
                return $resObj;
            }
        }
    }
}



?>
