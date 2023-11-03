<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-user.php';
include_once '../lib/l-util.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-profileAPI.php';
include_once '../lib/l-elastic.php';


if (url::issetInRequest('function')) {
    $func = url::requestToAny('function');
    $func();
}

function getLoggedUserData(){
    $roleName = $_SESSION['user']['rolename'];
    echo $roleName;
}

function getDefaultData(){
    global $securityArray;
    global $globalretry;
    global $timer;
    global $elastic_url;
    global $elastic_username;
    global $elastic_password;
    global $kibana_url;
    global $kibana_ip_url;
    global $kibana_username;
    global $kibana_password;

    $finalArray = array();
    $securityArr = array();
    $db = pdo_connect();
    $qry = $db->prepare("select value,name from ".$GLOBALS['PREFIX']."core.Options where name in ('dashboard_config','kibana_namespace','elast_config')");
    $qry->execute();
    $res = $qry->fetchAll();
    foreach($res as $key=>$val){
        $values = $val['value'];
        $name = $val['name'];
        if($name == 'dashboard_config'){
            $confdata = safe_json_decode($values, true);
            $wsurl = $confdata['wsurl'];
            $licenseurl = $confdata['licenseurl'];
            $finalArray["licenseurl"] = $licenseurl;
            $finalArray["wsurl"] = $wsurl;
        }else if($name == 'kibana_namespace'){
            $finalArray[kibananamespace] = $values;
        }else if($name == 'elast_config'){
            $finalArray[elastconfig] = $values;
        }
            $finalArray["securityArray"] =$securityArray['emailotp'];
            $finalArray['globalretry'] =$globalretry;
            $finalArray['timer'] =$timer;
            $finalArray['elasticurl'] =$elastic_url;
            $finalArray['elasticusername'] =$elastic_username;
            $finalArray['elasticpassword'] =$elastic_password;
            $finalArray['kibanaurl'] =$kibana_url;
            $finalArray['kibanaipurl'] =$kibana_ip_url;
            $finalArray['kibanausername'] =$kibana_username;
            $finalArray['kibanapassword'] =$kibana_password;

    }

    echo json_encode($finalArray);

}

function updateValuesConfig(){
    $db = pdo_connect();
    global $entireArray;
    global $file_path;

    $newValuesArray = array();

    $kibananamespace = url::requestToAny('kibananamespace');
    $elastconfig = url::requestToAny('elastconfig');
    $licenseurl = url::requestToAny('licenseurl');
    $wsurl = url::requestToAny('wsurl');
    $securityArray = url::requestToAny('securityArray');
    $globalretry = url::requestToAny('globalretry');
    $timer = url::requestToAny('timer');
    $elasticurl = url::requestToAny('elasticurl');
    $elasticusername = url::requestToAny('elasticusername');
    $elasticpassword = url::requestToAny('elasticpassword');
    $kibanaurl = url::requestToAny('kibanaurl');
    $kibanaipurl = url::requestToAny('kibanaipurl');
    $kibanausername = url::requestToAny('kibanausername');
    $kibanapassword= url::requestToAny('kibanapassword');

            $dash_config = array();
        $dash_config["wsurl"] = $wsurl;
        $dash_config["licenseurl"] = $licenseurl;
        $dash_configVal = json_encode($dash_config);

        $sql1 = $db->prepare("UPDATE ".$GLOBALS['PREFIX']."core.Options value = ? where name = ?");
        $sql1->execute([$dash_configVal,'dashboard_config']);
        $res1 = $db->lastInsertId();

        $sql2 = $db->prepare("UPDATE ".$GLOBALS['PREFIX']."core.Options value = ? where name = ?");
        $sql2->execute([$kibananamespace,'kibana_namespace']);
        $res2 = $db->lastInsertId();

        $sql3 = $db->prepare("UPDATE ".$GLOBALS['PREFIX']."core.Options value = ? where name = ?");
        $sql3->execute([$elastconfig,'elast_config']);
        $res3 = $db->lastInsertId();

            $file = file_get_contents('../config.php', true);
        foreach($entireArray as $key=>$val){
            $newValuesArray[$key] = $val;
        }
        $oldKibanaurl = $newValuesArray["kibanaurl"];
        $oldKibanaIpUrl = $newValuesArray["kibanaipurl"];
        $oldkibanaUname = $newValuesArray["kibanausername"];
        $oldkibanaPass = $newValuesArray["kibanapass"];
        $oldelasticUrl = $newValuesArray["elasticurl"];
        $oldelasticUname = $newValuesArray["elasticusername"];
        $oldelasticPass = $newValuesArray["elasticpass"];
        $oldsecurityArr = $newValuesArray["securityarr"];
        $oldglobalRetry = $newValuesArray["globalretry"];
        $oldtimer = $newValuesArray["timer"];

        $file = str_replace($oldKibanaurl,$kibanaurl, $file);
        $file = str_replace($oldKibanaIpUrl,$kibanaipurl, $file);
        $file = str_replace($oldkibanaUname,$kibanausername, $file);
        $file = str_replace($oldkibanaPass,$kibanapassword, $file);
        $file = str_replace($oldelasticUrl,$elasticurl, $file);
        $file = str_replace($oldelasticUname,$elasticusername, $file);
        $file = str_replace($oldelasticPass,$elasticpassword, $file);
        $file = str_replace($oldsecurityArr['emailotp'],$securityArray, $file);
        $file = str_replace($oldglobalRetry,$globalretry, $file);
        $file = str_replace($oldtimer,$timer, $file);
        unlink('../config.php');
        $newfile = $file_path."config.php";
        $fh = fopen($newfile, "wb");
        fwrite($fh, $file);
        fclose($fh);
        chmod($fh,0777);

        echo "success";
}


?>
