<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 'On');

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-util.php';
include_once '../lib/l-elastic.php';
require_once '../lib/l-vizualization.php';
require_once '../include/common_functions.php';


//Replace $routes['get'] with if else
if (url::postToText('function') === 'Get_userDetails') {
    Get_userDetails();
} else if (url::postToText('function') === 'load_HomePage') {
    load_HomePage();
} else if (url::postToText('function') === 'verifyLicenseKey') {
    verifyLicenseKey();
}

//Replace $routes['post'] with if else
if (url::postToText('function') === 'contactAdminForPortalAccessFunc') {
    contactAdminForPortalAccessFunc();
}

function verifyLicenseKey()
{
    $encrypted_txt = url::issetInPost('checkKey') ? url::postToAny('checkKey') : "";
    $decrypted_txt = __decrypt($encrypted_txt);
    print_r($decrypted_txt);
}

function __decrypt($string)
{

    $privKey = '-----BEGIN PRIVATE KEY-----
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
i+uDHY3gWW2rbxOQFpITF1jCXnx4RJy7zQ==
-----END PRIVATE KEY-----';

    openssl_private_decrypt(base64_decode($string), $decrypted, $privKey);

    return $decrypted;
}



function load_HomePage()
{
    $pdo = pdo_connect();
    global $kibana_url;
    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $userId      = $_SESSION['user']['userid'];
    $kid = url::requestToText('kid');
    $date = url::requestToText('st');
    if ($kid == '') {
        $sqlRes = getUserDefaultDashboardData($userId);
        $kid = $sqlRes['dashboardId'];
        $dashName = $sqlRes['dashboardName'];
        $vistype = $sqlRes['type'];
    } else {
        $stmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "agent.dashboard where dashboardId = ?");
        $stmt->execute([$kid]);
        $nmdata = $stmt->fetch(PDO::FETCH_ASSOC);
        $dashName = $nmdata['dashboardName'];
        $vistype = 1;
    }
    $namespace = '';
    $stmt = $pdo->prepare("select value from " . $GLOBALS['PREFIX'] . "core.Options where name=?");
    $stmt->execute(['kibana_namespace']);
    $nmdata = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($nmdata['value'] != '') {
        $namespace = '/s/' . $nmdata['value'];
    }

    if ($namespace == '') {
        $kibanaURL = $kibana_url . "/app/kibana#";
    } else {
        $kibanaURL = $kibana_url . $namespace . "/app/kibana#";
    }
    $kibanaVisualize = "/visualize/edit/";
    $kibanaDashboard = "/dashboard/";
    $kibanaEmbed = "?embed=true";
    $kibanaViewID = "";
    $kibanaAutoRefresh = "";
    $kibanaBasicFilter = "";
    $kibanaAutoRefresh = "";
    $kibanaFromToDate = "";
    $kibanaIframeURL = "";
    $filter = "()";
    $MachineFilter = "(('$" . "state':(store:appState),meta:(alias:!n,disabled:!f,index:'" . $indexId . "',key:LEVELFILTER,negate:!f,params:(query:MACHINENAMEFILTER,type:phrase),type:phrase,value:MACHINENAMEFILTER),query:(match:(LEVELFILTER:(query:MACHINENAMEFILTER,type:phrase)))))";
    $types = "dashboard";
    $groupFilter = "(('$" . "state':(store:appState),meta:(alias:!n,disabled:!f,index:'" . $indexId . "',key:LEVELFILTER,negate:!f,type:custom,value:'FILTER123'),query:(bool:(should:!(QUERYFILTER)))))";

    if (!$kid) {
        $msg = array("msg" => "no");
        $msg = json_encode($msg);
        echo $msg;
        return;
    } else {
        $kibanaViewID = $kid;
    }

    if (!url::requestToAny('ar')) {
        $kibanaAutoRefresh = "refreshInterval:(pause:!t,value:0)";
    } else {
        $kibanaAutoRefresh = url::requestToText('ar');
        $kibanaAutoRefresh = "refreshInterval:(pause:!f,value:" . $kibanaAutoRefresh . ")";
    }

    if (!url::requestToAny('st')) {

        $kibanaFromToDate = 'time:(from:now-7d,to:now)';
        $value = "This week";
    } else if ($date) {
        switch ($date) {
            case '15min':
                $kibanaFromToDate  = 'time:(from:now-15m,to:now)';
                $value = "Last 15 min";
                break;
            case '60min':
                $kibanaFromToDate  = 'time:(from:now-1h,mode:quick,to:now)';
                $value = "Last 1 hour";
                break;
            case '1day':
                $kibanaFromToDate  = 'time:(from:now-1d,mode:quick,to:now)';
                $value = "Today";
                break;
            case '7day':
                $kibanaFromToDate  = 'time:(from:now-1w,mode:quick,to:now)';
                $value = "This Week";
                break;
            case '30day':
                $kibanaFromToDate  = 'time:(from:now-1M,mode:quick,to:now)';
                $value = "Last 30 days";
                break;
            case '3mnth':
                $kibanaFromToDate  = 'time:(from:now-3M,mode:quick,to:now)';
                $value = "Last 90 days";
                break;
            case '1yr':
                $kibanaFromToDate  = 'time:(from:now-3y,mode:quick,to:now)';
                $value = "Last 1 year";
                break;
            case '3yr':
                $kibanaFromToDate  = 'time:(from:now-3y,mode:quick,to:now)';
                $value = "Last 3 year";
                break;
            case '5yr':
                $kibanaFromToDate  = 'time:(from:now-5y,mode:quick,to:now)';
                $value = "Last 5 year";
                break;
            default:
                break;
        }
    } else {
        if (!$_REQUEST('en')) {
            echo "Invalid date range";
            return;
        } else {
            $kibanaFromToDate = "time:(from:'" . $_REQUEST('fr') . "',mode:absolute,to:'" . $_REQUEST('to') . "')";
        }
    }

    $advanceFilter = "";

    $groupHasNoMachines = false;

    if ($searchType == 'Sites') {
        $type = "site.keyword";
        $temp = str_replace("LEVELFILTER", $type, $MachineFilter);
        $tempfilter = str_replace("MACHINENAMEFILTER", $searchValue, $temp);
    } else if ($searchType == 'ServiceTag') {
        $type = "machine.keyword";
        $temp = str_replace("LEVELFILTER", $type, $MachineFilter);
        $tempfilter = str_replace("MACHINENAMEFILTER", $searchValue, $temp);
    } else if ($searchType == 'Groups') {
        $type = "machine.keyword";
        $dataScope = UTIL_GetSiteScope_PDO($pdo, $searchValue, $searchType);
        $machines = DASH_GetGroupsMachines_PDO('', $pdo, $dataScope);

        if (safe_sizeof($machines) > 0) {
            $maclist = $maclist1 = $maclist2 = $maclist12 = '';

            foreach ($machines as $val) {
                $maclist .= $val . ",";
                $maclist1 .=  $val . ",+";
                $maclist2 .= "(match_phrase:(machine.keyword:" . $val . ")),";
                $maclist12 .= '{"match_phrase":{"machine":"' . $val . '"}},';
            }
            $maclist = rtrim($maclist, ',');
            $maclist1 = rtrim($maclist1, ',+');
            $maclist2 = rtrim($maclist2, ',');
            $maclist12 = rtrim($maclist12, ',');
            $temps = '{"bool":{"should":[' . $maclist12 . ']}}';
            $temps = urlencode($temps);


            $temp = str_replace("LEVELFILTER", $type, $groupFilter);
            $temp1 = str_replace("MACHINENAMEFILTER", $maclist, $temp);
            $temp2 = str_replace("FILTER123", $temps, $temp1);
            $tempfilter = str_replace("QUERYFILTER", $maclist2, $temp2);
        } else {
            $groupHasNoMachines = true;
        }
    }

    $advanceFilter .= $tempfilter . ')';
    if ($advanceFilter != '') {
        $filter = $advanceFilter;
    }
    $addFilter = ")&_a=(description:'',filters:!" . $filter;
    if (!empty($searchType)) {
        $kibanaBasicFilter = "&_g=(" . $kibanaAutoRefresh . "," . $kibanaFromToDate . $addFilter;
    } else {
        $kibanaBasicFilter = "&_g=(" . $kibanaAutoRefresh . "," . $kibanaFromToDate;
    }

    if ($groupHasNoMachines) {
        $temp = array("hasData" => false, "name" => $dashName, "url" => $kibanaIframeURL, "dashId" => $kibanaViewID, "SelectedVal" => $value);
    } else {
        if ($vistype == 0) {
            $kibanaIframeURL = $kibanaURL . $kibanaVisualize . $kibanaViewID . $kibanaEmbed . $kibanaBasicFilter;
        } else {
            $kibanaIframeURL = $kibanaURL . $kibanaDashboard . $kibanaViewID . $kibanaEmbed . $kibanaBasicFilter;
        }
        $temp = array("hasData" => true, "name" => $dashName, "url" => $kibanaIframeURL, "dashId" => $kibanaViewID, "SelectedVal" => $value);
    }
    $auditRes = create_auditLog('Home', 'View', 'Success');
    echo json_encode($temp);
}

function getDataFromEL_new($kid, $type)
{

    $id = "$type:$kid";
    $params = '{
                "query": {
                  "bool": {
                    "must": [
                      {
                        "term": {
                          "type": "' . $type . '"
                        }
                      },
                      {
                        "term": {
                          "_id": "' . $id . '"
                        }
                      }
                    ]
                  }
                }
              }';
    $res = EL_GetCurl(".kibana*", $params);
    $curlArray = safe_json_decode($res, true);

    $result = $curlArray['hits']['hits'][0]['_source']['dashboard'];

    foreach ($result as $key => $val) {
        $embed = str_replace("}", ")", str_replace("{", "(", $result['panelsJSON']));
        $theme = $result['optionsJSON'];
        $version = $result['version'];
        $title = str_replace("/s", "+", $result['title']);
        $searcsource = $result['kibanaSavedObjectMeta']['searchSourceJSON'];
    }
    echo $embed;
    exit;
}

function getIndexId($kid)
{


    $query = '{
            "query": {
              "bool": {
                "must": [
                  {
                    "term": {
                      "_id": "dashboard:' . $kid . '"
                    }
                  },
                  {
                    "term": {
                      "type": "dashboard"
                    }
                  }
                ]
              }
            }
          }';
    $res = EL_GetCurl(".kibana*", $query);

    $curlArray = safe_json_decode($res, true);

    $result = $curlArray['hits']['hits'][0]['_source']['dashboard']['panelsJSON'];
    $res = safe_json_decode($result, TRUE);

    foreach ($res as $key => $val) {
        $visId = $val['id'];

        $indexID = getpanelJson($visId);
        if ($indexID != 'continue') {
            return $indexID;
        }
    }
}

function getpanelJson($visId)
{
    $query = '{
            "query": {
              "bool": {
                "must": [
                  {
                    "term": {
                      "_id": "visualization:' . $visId . '"
                    }
                  },
                  {
                    "term": {
                      "type": "visualization"
                    }
                  }
                ]
              }
            }
          }';
    $res = EL_GetCurl(".kibana*", $query);

    $curlArray = safe_json_decode($res, true);

    $result = $curlArray['hits']['hits'][0]['_source']['visualization']['kibanaSavedObjectMeta']['searchSourceJSON'];
    $res = safe_json_decode($result, TRUE);

    if (!empty($res['index'])) {
        return $res['index'];
    } else {
        return 'continue';
    }
}

function Get_userDetails()
{
    $pdo = pdo_connect();
    $user = $_SESSION['user']['adminEmail'];
    $proSql = $pdo->prepare("select userid,firstName,timezone from " . $GLOBALS['PREFIX'] . "core.Users where user_email =?");
    $proSql->execute([$user]);
    $proSqlRes = $proSql->fetch(PDO::FETCH_ASSOC);

    $userData = array();
    $SESSION['loggedUName'] = $proSqlRes['firstName'];
    $userData['userid'] = $proSqlRes['userid'];
    $userData['firstName'] = $proSqlRes['firstName'];
    $userData['adminEmail'] = $_SESSION['user']['adminEmail'];
    $userData['customerType'] = $_SESSION['user']['customerType'];
    $userData['timezone'] = $proSqlRes['timezone'];
    $_SESSION['timezone'] = $proSqlRes['timezone'];
    $_SESSION['userTimeZone'] = $proSqlRes['timezone'];
    $userData['imgPath'] = '';
    $userData['imgprofilelogo'] = '';
    // $userData['imgPath'] = '<img src="' . $proSqlRes['imgPath'] . '" alt="ProfilePicture">';
    // $userData['imgprofilelogo'] = '<img width="30" height="30" src="' . $proSqlRes['imgPath'] . '" alt="ProfileLogo">';
    // $path = $proSqlRes['imgPath'];
    // $type = pathinfo($path, PATHINFO_EXTENSION);
    // $data = file_get_contents($path);
    // $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    // $userData['imgprofileencry'] = '<img src="' . $base64 . '" alt="ProfileLogo">';
    $userData['imgprofileencry'] = '';
    echo json_encode($userData);
}

function contactAdminForPortalAccessFunc()
{
    global $base_url;
    $approvalLink = $base_url . 'admin/user_action.php';
    $body = 'Dear Nanoheal Admin,<br/><br/>A user has been requested '
        . 'for Nanoheal Portal access. Use below link to approve the request<br/><br/>'
        . 'User Approval Link : ' . $approvalLink . '<br/><br/>'
        . 'Thanks & Regards<br/>Nanoheal Admin';
    $subject = 'User Approval Status';
    $fromEmail = "noreply@nanoheal.com";
    $fromName = 'Nanoheal(noreply)';

    $pdo = pdo_connect();

    $loggedUserEmail = $_SESSION['user']['adminEmail'];
    $domain_name = substr(strrchr($loggedUserEmail, "@"), 1);

    $stmt = $pdo->prepare("select username, user_email from " . $GLOBALS['PREFIX'] . "core.Users where role_id = (select id from " . $GLOBALS['PREFIX'] . "core.Options where name = 'SuperAdminRole') and user_email like '%" . $domain_name . "%'");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($data) {
        foreach ($data as $value) {
            $username = $value['firstName'];
            $useremail = $value['user_email'];
            $arrayPost = array(
                'from' => getenv('SMTP_USER_LOGIN'),
                'to' => $useremail,
                'subject' => $subject,
                'text' => '',
                'html' => $body,
                'token' => getenv('APP_SECRET_KEY'),
            );

            $url = getenv('VISUALISATION_SERVICE_API_URL') . "/mailer/sendmassage";
            $mailResponse = CURL::sendDataCurl($url, $arrayPost);
        }
    } else {
        $username = 'Admin';
        $useremail = 'admin@nanoheal.com';
        $arrayPost = array(
            'from' => getenv('SMTP_USER_LOGIN'),
            'to' => $useremail,
            'subject' => $subject,
            'text' => '',
            'html' => $body,
            'token' => getenv('APP_SECRET_KEY'),
        );

        $url = getenv('VISUALISATION_SERVICE_API_URL') . "/mailer/sendmassage";
        $mailResponse = CURL::sendDataCurl($url, $arrayPost);
    }

    if ($mailResponse) {
        echo 'ok';
    } else {
        echo 'failed';
    }
}
