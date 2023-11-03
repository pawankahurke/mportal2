<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();



global $crmList;
$crmList = array("SN" => "Service Now", "ME" => "Manage Engine", "OT" => "OTRS", "SG" => "Sugar CRM", "CW" => "Cherwell");


function CRM_GetCRMType($key, $db, $ch_id)
{

    $key = DASH_ValidateKey($key);

    if ($key) {
        try {
            $sql = "SELECT crmType, crmIP, crmKey, crmUsername, crmPassword FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE eid = '$ch_id' LIMIT 1";
            $result = find_one($sql, $db);
            if (safe_count($result) > 0) {
                return $result;
            } else {
                return array();
            }
        } catch (Exception $exc) {
            logs::log(__FILE__, __LINE__, $exc, 0);
            echo "Exception : " . $exc;
        }
    } else {
        echo "Your key has been expired";
    }
}

function CRM_GetCRMTypeView($key, $db, $ch_id, $customerType)
{

    $key = DASH_ValidateKey($key);
    if (($customerType == "5") || ($customerType == 5)) {
        $where = "chid='$ch_id' ";
        $limit = "LIMIT 1";
    } else if (($customerType == "2") || ($customerType == 2)) {
        $customerType = "5";
        $ch_idList = CRM_GetResellerCustomersIdComp($customerType, $db, $ch_id);
        $where = "chid in ($ch_idList)";
        $limit = "";
    }

    if ($key) {
        try {
            $sql = "SELECT chid from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure WHERE $where $limit";

            $result = find_one($sql, $db);
            if (safe_count($result) > 0) {
                return $result;
            } else {
                return array();
            }
        } catch (Exception $exc) {
            logs::log(__FILE__, __LINE__, $exc, 0);
            echo "Exception : " . $exc;
        }
    } else {
        echo "Your key has been expired";
    }
}

function CRM_GetResellerCustomersIdComp($CRMlogin_valueCust, $conn, $cid)
{


    $sql = "SELECT eid,companyName FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE ctype = '$CRMlogin_valueCust' and channelId='$cid'";

    $res = find_many($sql, $conn);
    if (safe_count($res) > 0) {
        $eid = "";

        foreach ($res as $value) {
            $eid .= $value['eid'] . ",";
        }
        $msg1 = "'" . str_replace(",", "','", $eid);
        $msg = rtrim($msg1, ",'") . "'";
    } else {
        $msg = "";
    }
    return $msg;
}

function CRM_GetResellerCustomersSiteComp($customerType, $db, $custId, $chis_lists)
{

    $msg1 = "'" . str_replace(",", "','", $chis_lists);

    $sql = "SELECT eid,companyName,sitelist FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE ctype = '$customerType' and channelId='$custId' and eid in($msg1')";


    $res = find_many($sql) or die(mysql_error($db) . "mysql error CRM_GetResellerCustomersId");

    if (safe_count($res) > 0) {
        $sites = "";

        foreach ($res as $value) {
            $sites .= $value['sitelist'] . ",";
        }

        $sitelist = rtrim($sites, ",'");
    } else {
        $sitelist = "continue";
    }

    return $sitelist;
}


function CRM_GetCRMNotifications($key, $db, $ch_id)
{

    $key = DASH_ValidateKey($key);

    if ($key) {
        try {
            echo $sql = "SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.crmConfigure WHERE ch_id = '$ch_id' LIMIT 1";
            $result = find_many($sql);
            if (safe_count($result) > 0) {
                return $result;
            } else {
                return array();
            }
        } catch (Exception $exc) {
            logs::log(__FILE__, __LINE__, $exc, 0);
            echo "Exception : " . $exc;
        }
    } else {
        echo "Your key has been expired";
    }
}


function CRM_UpdateCredentials($loggedEid, $db, $crmData)
{

    $crmType = $crmData['selectedCrm'];
    $CRMlogin_value = $crmData['CRMlogin_value'];
    $crmData['custName'] = url::issetInRequest('custName') ? url::requestToAny('custName') : "";
    $sitename = $crmData['custSiteName'];
    $crmUrl = $crmData['crm_url'];
    $username = $crmData['crm_username'];
    $password = $crmData['crm_password'];
    $apiKey = $crmData['crm_key'];
    $ch_id = $crmData['custId'];
    $crmName = "SN";
    $key = "";
    global $crmList;
    $key = DASH_ValidateKey($key);
    if ($key) {
        try {
            if (trim($ch_id) == "") {
                $response = array("response" => "notexist", "jsonData" => "notexist");
            } else {
                $isValid = CRM_IsValid_Credentials($crmUrl, $username, $password, $apiKey, $crmName);
                if ($isValid == TRUE) {
                    $createDbTable = CRM_Create_Alter_Tables($db);
                    if ($createDbTable == TRUE) {
                        $response = CRM_InsertCrmDetails($loggedEid, $db, $crmData);
                    }
                } else {
                    $response = array("response" => "invalid", "jsonData" => "invalid");
                }
                return $response;
            }
        } catch (Exception $exc) {
            logs::log(__FILE__, __LINE__, $exc, 0);
            echo "Exception : " . $exc;
        }
    } else {
        echo "Your key has been expired";
    }
}


function CRM_IsValid_Credentials($crmUrl, $username, $password, $apiKey, $crmName)
{

    $isValid = FALSE;

    switch ($crmName) {
        case "SN":
            $isValid = CRM_IsValid_ServiceNow($crmUrl, $username, $password);
            break;
        case "ME":
            $isValid = CRM_IsValid_ManageEngine($crmUrl, $username, $password, $apiKey);
            break;
        case "OT":
            $isValid = CRM_IsValid_OTRS($crmUrl, $username, $password, $apiKey);
            break;
        case "SC":
            $isValid = CRM_IsValid_SugarCrm($crmUrl, $username, $password, $apiKey);
            break;
        case "CW":
            $isValid = CRM_IsValid_Cherwell($crmUrl, $username, $password, $apiKey);
            break;
        default:
            $isValid = FALSE;
            break;
    }
    return $isValid;
}


function CRM_IsValid_ServiceNow($crmUrl, $username, $password)
{

    $header = array('Content-Type: application/json', 'Accept: application/json');
    $ch = curl_init();

    if (strpos($crmUrl, 'compucom') !== false) {
        $crmUrl = $crmUrl;
    } else {
        $crmUrl = $crmUrl . "/api/now/table/incident?sysparm_limit=1";
    }

    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_URL, $crmUrl . "/api/now/table/incident?sysparm_limit=1");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_POSTFIELDS, "");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        CRM_ErrorLog("CRM_IsValid_ServiceNow=", curl_error($ch));
    }

    if (strpos($result, 'failed') !== true) {
        $res = "invalid";
    } else if ($httpcode == 200 || $httpcode == '200') {
        $res = "valid";
    } else {
        $res = "invalid";
    }
    $res = "valid";

    $res = "valid";
    return $res;
}


function CRM_IsValid_Cherwell($crmUrl, $username, $password, $apiKey)
{

    $crmUrl = $crmUrl . '/token?api_key=' . $apiKey . '&api_key=' . $apiKey;
    $headers = array();
    $headers[] = "Content-Type: application/x-www-form-urlencoded";
    $headers[] = "Accept: application/json";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $crmUrl);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=password&client_id={$apiKey}");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    curl_close($ch);

    if (curl_errno($ch)) {
        CRM_ErrorLog("CRM_IsValid_Cherwell=", curl_error($ch));
    }

    $result = safe_json_decode($result);
    if ($result->error == 'invalid_client_id') {
        return FALSE;
    } else {
        return TRUE;
    }
}


function CRM_Create_Alter_Tables($db)
{

    $alterResult = CRM_AlterTable($db);
    $createResult = CRM_CreateTable($db);
    return TRUE;
}


function CRM_AlterTable($db)
{

    $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'agent' AND TABLE_NAME = 'channel' "
        . "AND COLUMN_NAME = 'crmIP'";
    $result = find_one($sql, $db);

    if (safe_count($result) > 0) {
        return TRUE;
    } else {
        $alertSql = "ALTER TABLE " . $GLOBALS['PREFIX'] . "agent.channel ADD COLUMN crmType VARCHAR(50) NULL DEFAULT '0',
                    ADD COLUMN	crmIP VARCHAR(250) NULL DEFAULT '0',
                    ADD COLUMN	crmKey VARCHAR(50) NULL DEFAULT '0',
                    ADD COLUMN	crmUsername VARCHAR(50) NULL DEFAULT '0',
                    ADD COLUMN	crmPassword VARCHAR(50) NULL DEFAULT '0'";
        $alterRes = redcommand($alertSql, $db);
        return TRUE;
    }
}


function CRM_CreateTable($db)
{

    $sql = "SELECT * FROM information_schema.tables WHERE table_schema = 'event' AND table_name = 'crmConfigure' LIMIT 1";
    $result = find_one($sql, $db);

    if (safe_count($result) > 0) {
        return TRUE;
    } else {
        $createSql = "CREATE TABLE  " . $GLOBALS['PREFIX'] . "event.`crmConfigure` (
	`id` INT(50) NOT NULL AUTO_INCREMENT,
	`ch_id` INT(50) NOT NULL,
	`companyName` VARCHAR(100) NOT NULL,
	`nid` VARCHAR(100) NOT NULL,
	`notifName` VARCHAR(250) NOT NULL,
	`crmType` VARCHAR(50) NOT NULL,
	`services` VARCHAR(100) NULL DEFAULT NULL,
	`category` VARCHAR(100) NULL DEFAULT NULL,
	`subcategory` VARCHAR(100) NULL DEFAULT NULL,
	`priority` INT(50) NOT NULL DEFAULT '1',
	`crmUser` VARCHAR(250) NULL DEFAULT NULL,
	`enabled` TINYINT(4) NOT NULL DEFAULT '0',
	`state` VARCHAR(50) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
        ) COLLATE='latin1_swedish_ci'
        ENGINE=InnoDB
        AUTO_INCREMENT=8
        ";
        $createRes = redcommand($createSql, $db);
        return TRUE;
    }
}


function CRM_InsertCrmDetails($loggedEid, $db, $crmData)
{

    $crmType = $crmData['selectedCrm'];
    $CRMlogin_value = $crmData['CRMlogin_value'];
    $crmData['custName'] = url::issetInRequest('custName') ? url::requestToAny('custName') : "";
    $chid = $crmData['custId'];
    $sitename = $crmData['custSiteName'];
    $crmUrl = $crmData['crm_url'];
    $username = $crmData['crm_username'];
    $password = $crmData['crm_password'];
    $apiKey = $crmData['crm_key'];

    if (($crmData['custName'] == "ALL") || ($crmData['custName'] == "all") || ($crmData['custName'] == "All")) {
        $sqlcust = "SELECT eid,companyName FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE ctype = '5' and channelId='$loggedEid'";
        $res = find_many($sqlcust, $db) or die(mysql_error() . "CRM_InsertCrmDetails error");
        $eids = "";
        foreach ($res as $value) {
            $eids .= "'" . $value['eid'] . "',";
        }
        $eidList = rtrim($eids, ",");
        $sqlsite = "select compId,siteName from " . $GLOBALS['PREFIX'] . "agent.customerOrder O where O.compId in($eidList)";

        $resSite = find_many($sqlsite, $db);

        foreach ($resSite as $Sitevalue) {
            $compId = $Sitevalue['compId'];
            $sitename = getSitelistCust($compId, $db);
            $result = "UPDATE " . $GLOBALS['PREFIX'] . "agent.channel ac SET ac.crmType = '$crmType', ac.crmIP = '$crmUrl', ac.crmKey = '$apiKey', "
                . "ac.crmUsername = '$username', ac.crmPassword = '$password',ac.sitelist='$sitename',ac.syncAssetData='compucom' WHERE eid = '$compId'";
            $result = redcommand($result, $db);
        }


        if ($result) {
            $res = "success";
        } else {
            $res = "failed";
        }
    } else {
        $sql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.channel ac SET ac.crmType = '$crmType', ac.crmIP = '$crmUrl', ac.crmKey = '$apiKey', "
            . "ac.crmUsername = '$username', ac.crmPassword = '$password',ac.sitelist='$sitename',ac.syncAssetData='compucom' WHERE eid = '$chid'";

        $result = redcommand($sql, $db);


        if ($result) {
            $res = "success";
            $sqljson = "select jsonData,jsonCloseData from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where chid='$chid' order by id desc limit 1";
            $sqlres = find_one($sqljson, $db);
            if (safe_count($sqlres) > 0) {
                $jsonData = $sqlres['jsonData'];
                $closejsonData = $sqlres['jsonCloseData'];
            } else {
                $jsonData = '{
"docType":"Nanoheal Enterprise",
"transactionId":"%%",
"timeStamp":"%%",
"sender":"Nanoheal Enterprise",
"client":"CompuCom Systems, Inc.",
"refCaseNumber":"NH_ID",
"openedDateStamp":"%%",
"transDateStamp":"%%",
"shortDescription":"%%",
"problemDescription":"%%",
"statusCode":1,
"priorityCode":"%%",
"impactCode":2,
"urgencyCode":2,
"supportGroup":"%%",
"category":"Incident",
"subCategory":"Reporting",
"notes":"%%",
"contactType": "%%",
"contact":{
      "company":"CompuCom Systems, Inc."
  },
 "equipment":{
      "model":"%%",
      "description":"%%"
 },

"internalNotes":"%1234%"
}';

                $closejsonData = '{
   "docType":"Nanoheal Enterprise",
   "client":"CompuCom Systems, Inc.",
   "transactionId":"%%",
   "timeStamp":"%%",
   "sender":"Nanoheal Enterprise",
   "caseNumber": "%%",
   "refCaseNumber": "%%",
   "transDateStamp":"%%",
   "resolution":{
      "text":"Issue resolved by nanoheal DART 7888",
      "code":"Resolved - Full Restoration",
      "timeStamp":"%%"
   },
   "statusCode":6,
   "problemDescription":"%%"
}';
            }
        } else {
            $res = "failed";
            $jsonData = "no json data";
            $closejsonData = "no json data";
        }
    }

    return $res = array("response" => $res, "jsonData" => $jsonData, "closejsonData" => $closejsonData);
}

function getSitelistCust($compId, $db)
{


    $sqlsite = "select compId,siteName from " . $GLOBALS['PREFIX'] . "agent.customerOrder O where O.compId='$compId'";

    $resSite = find_many($sqlsite, $db) or die(mysql_error() . "getSitelistCust error");
    $count = safe_count($resSite);

    if (($count > 1) || ($count > '1')) {
        $sitename = "";
        foreach ($resSite as $Sitevalue) {
            $sitename .= $Sitevalue['siteName'] . ",";
        }
    } elseif (($count == 1) || ($count == '1')) {
        foreach ($resSite as $Sitevalue) {
            $sitename = $Sitevalue['siteName'] . ",";
        }
    }
    $sitelist = rtrim($sitename, ",");

    return $sitelist;
}


function CRM_ErrorLog($fileName, $text)
{

    $fileObject = fopen($fileName . '.txt', 'w');
    fwrite($fileObject, date("d-m-Y"));
    fwrite($fileObject, PHP_EOL);
    fwrite($fileObject, $text);
    fclose($fileObject);
    return true;
}

function CRM_GetNotifications()
{
    try {
        $conn = db_connect();
        db_change($GLOBALS['PREFIX'] . "event", $conn);
        $username = $_SESSION["user"]["username"];
        $sql = "select id,name from  " . $GLOBALS['PREFIX'] . "event.Notifications where global='1'";
        $result = find_many($sql, $conn);

        if (safe_count($result) > 0) {
            $response = $result;
        } else {
            $response = "No records Found";
        }
        return $response;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
        echo "Exception : " . $exc;
    }
}

function View_NotificationForm()
{

    $form = Get_CRMForm();
    return $form;
}

function Get_CRMForm()
{

    $crmType = "SN";

    switch ($crmType) {
        case 'CW':
            CRM_CWForm($crmType);

            break;
        case 'SN':
            CRM_SNForm();

            break;
        case 'ME':
            echo 'ManageEngine';

            break;

        default:
            break;
    }
}

function CRM_CWForm($crmType)
{

    echo '                              <div class="form-group">
                                            <label for="search_id" class="col-sm-2 align-label">Services</label>
                                            <div class="col-sm-9">
                                            <input type="hidden" class="form-control" id="crmType" value="' . $crmType . '">
                                                <select id="cw_services_id" name="search_id" class="form-control" value="" style="-webkit-appearance: menulist !important;">
                                                    <option value="" selected>Please Select</option>
                                                    <option value="Account Management">Account Management</option>
                                                    <option value="Cloud Services">Cloud Services</option>
                                                    <option value="Printing">Printing</option>
                                                    <option value="WebServices">WebServices</option>
                                                </select>
                                            </div>
                                            <label style="font-size: 20px;cursor:pointer;" for="services" id="servicestxtbx" class="col-sm-1 align-label" onclick="sevicestxtbx()">+</label>
                                        </div>
                                        <div class="form-group" id="services-txtbx" style="display:none;">
                                            <label for="search_id" class="col-sm-2 align-label"></label>
                                            <div class="col-sm-9">
                                               <input type="text" class="form-control" id="cw_addservices_id" placeholder="Services">
                                            </div>
                                            <label style="font-size: 20px;cursor:pointer;" for="services" id="servicestxtbx" class="col-sm-1 align-label" onclick="sevicesdeltxtbx()">-</label>
                                        </div>





                                        <div class="form-group">
                                            <label for="search_id" class="col-sm-2 align-label">Category</label>
                                            <div class="col-sm-9">
                                                <select id="cw_category_id" name="search_id" class="form-control" style="-webkit-appearance: menulist !important;">
                                                    <option value="" selected>Please Select</option>
                                                    <option value="Cloud Services">Cloud Services</option>
                                                    <option value="Printing">Printing</option>
                                                    <option value="WebServices">WebServices</option>
                                                </select>
                                            </div>
                                            <label style="font-size: 20px;cursor:pointer;" for="category" class="col-sm-1 align-label" onclick="categorytxtbx()">+</label>
                                        </div>
                                         <div class="form-group" id="category-txtbx" style="display:none;">
                                            <label for="search_id" class="col-sm-2 align-label"></label>
                                            <div class="col-sm-9">
                                               <input type="text" class="form-control" id="cw_addcategory_id" placeholder="Category">
                                            </div>
                                            <label style="font-size: 20px;cursor:pointer;" for="services" id="servicestxtbx" class="col-sm-1 align-label" onclick="categorydeltxtbx()">-</label>
                                        </div>

                                        <div class="form-group">
                                            <label for="search_id" class="col-sm-2 align-label">SubCategory</label>
                                            <div class="col-sm-9">
                                                <select id="cw_subcategory_id" name="search_id" class="form-control" value="" style="-webkit-appearance: menulist !important;">

                                                    <option value="" selected>Please Select</option>
                                                    <option value="Cloud Services">Cloud Services</option>
                                                    <option value="Printing">Printing</option>
                                                    <option value="WebServices">WebServices</option>
                                                </select>
                                            </div>
                                            <label style="font-size: 20px;cursor:pointer;" for="subcategory" class="col-sm-1 align-label" onclick="subcategorytxtbx()">+</label>
                                        </div>
                                        <div class="form-group" id="subcategory-txtbx" style="display:none;">
                                            <label for="search_id" class="col-sm-2 align-label"></label>
                                            <div class="col-sm-9">
                                               <input type="text" class="form-control" id="cw_addsubcategory_id" placeholder="Category">
                                            </div>
                                            <label style="font-size: 20px;cursor:pointer;" for="subcategory" id="subcategorytxtbx" class="col-sm-1 align-label" onclick="subcategorydeltxtbx()">-</label>
                                        </div>



                                        <div class="form-group">
                                            <label for="search_id" class="col-sm-2 align-label">Priority</label>
                                            <div class="col-sm-9">
                                                <select id="cw_priority" name="search_id" class="form-control" value="" style="-webkit-appearance: menulist !important;">
                                                    <option value="" selected>Please Select</option>
                                                    <option value="High">High</option>
                                                    <option value="Medium">Medium</option>
                                                    <option value="Low">Low</option>
                                                </select>
                                            </div>
                                            <label for="search_id" class="col-sm-1 align-label">&nbsp;</label>
                                        </div>
                                         <div class="form-group">
                                            <label for="search_id" class="col-sm-2 align-label">CRM User</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="cw_crmUser" placeholder="CRM User">
                                            </div>
                                            <label style="font-size: 20px;"  for="search_id" class="col-sm-1 align-label">&nbsp;</label>
                                        </div>
                                         <div class="form-group">
                                            <label for="search_id" class="col-sm-2 align-label">EventType</label>
                                            <div class="col-sm-9">
                                                <select id="cw_eventType" name="search_id" class="form-control" value="" style="-webkit-appearance: menulist !important;">
                                                    <option value="1">AutoHeal</option>
                                                    <option value="2">SelfService</option>
                                                    <option value="3">Notification</option>
                                                </select>
                                            </div>
                                            <label style="font-size: 20px;cursor:pointer;" for="services" id="servicestxtbx" class="col-sm-1 align-label">&nbsp;</label>
                                        </div>
                                         <div class="form-group">
                                            <label for="search_id" class="col-sm-2 align-label">&nbsp;</label>
                                            <div class="col-sm-9">
                                                <input type="submit" class="add-user-add-btn" data-dismiss="modal" aria-label="Close" id="crmDetails" localized="" value="Submit">
                                            </div>
                                            <label style="font-size: 20px;cursor:pointer;" for="services" id="servicestxtbx" class="col-sm-1 align-label">&nbsp;</label>
                                        </div>';
}

function CRM_SNForm($crmType)
{

    echo '                              <div class="form-group">
                                            <label for="search_id" class="col-sm-2 align-label">Caller</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="crmUser" placeholder="Caller">
                                            </div>
                                            <label style="font-size: 20px;"  for="search_id" class="col-sm-1 align-label">&nbsp;</label>
                                        </div>


                                        <div class="form-group" id="services-txtbx" style="display:none;">
                                            <label for="search_id" class="col-sm-2 align-label"></label>
                                            <div class="col-sm-9">
                                               <input type="text" class="form-control" id="services" placeholder="Services">
                                            </div>
                                            <label style="font-size: 20px;cursor:pointer;" for="services" id="servicestxtbx" class="col-sm-1 align-label" onclick="sevicesdeltxtbx()">-</label>
                                        </div>





                                        <div class="form-group">
                                            <label for="category_id" class="col-sm-2 align-label">Category</label>
                                            <div class="col-sm-9">
                                                <select id="category_id" name="search_id" class="form-control" value="" style="-webkit-appearance: menulist !important;">
                                                    <option value="Account Management">Account</option>
                                                    <option value="Cloud Services">Cloud Services</option>
                                                    <option value="Printing">Printing</option>
                                                    <option value="WebServices">WebServices</option>
                                                </select>
                                            </div>
                                            <label style="font-size: 20px;cursor:pointer;" for="category" class="col-sm-1 align-label" onclick="categorytxtbx()">+</label>
                                        </div>
                                         <div class="form-group" id="category-txtbx" style="display:none;">
                                            <label for="search_id" class="col-sm-2 align-label"></label>
                                            <div class="col-sm-9">
                                               <input type="text" class="form-control" id="services" placeholder="Category">
                                            </div>
                                            <label style="font-size: 20px;cursor:pointer;" for="services" id="servicestxtbx" class="col-sm-1 align-label" onclick="categorydeltxtbx()">-</label>
                                        </div>

                                        <div class="form-group">
                                            <label for="search_id" class="col-sm-2 align-label">SubCategory</label>
                                            <div class="col-sm-9">
                                                <select id="subcategory_id" name="search_id" class="form-control" value="" style="-webkit-appearance: menulist !important;">

                                                    <option value="" selected>Please Select</option>
                                                    <option value="Cloud Services">Cloud Services</option>
                                                    <option value="Printing">Printing</option>
                                                    <option value="WebServices">WebServices</option>
                                                </select>
                                            </div>
                                            <label style="font-size: 20px;cursor:pointer;" for="subcategory" class="col-sm-1 align-label" onclick="subcategorytxtbx()">+</label>
                                        </div>
                                        <div class="form-group" id="subcategory-txtbx" style="display:none;">
                                            <label for="search_id" class="col-sm-2 align-label"></label>
                                            <div class="col-sm-9">
                                               <input type="text" class="form-control" id="services" placeholder="Category">
                                            </div>
                                            <label style="font-size: 20px;cursor:pointer;" for="subcategory" id="subcategorytxtbx" class="col-sm-1 align-label" onclick="subcategorydeltxtbx()">-</label>
                                        </div>



                                        <div class="form-group">
                                            <label for="search_id" class="col-sm-2 align-label">Priority</label>
                                            <div class="col-sm-9">
                                                <select id="search_id" name="search_id" class="form-control" value="" style="-webkit-appearance: menulist !important;">
                                                    <option value="High">High</option>
                                                    <option value="Medium">Medium</option>
                                                    <option value="Low">Low</option>
                                                </select>
                                            </div>
                                            <label for="search_id" class="col-sm-1 align-label">&nbsp;</label>
                                        </div>
                                         <div class="form-group">
                                            <label for="search_id" class="col-sm-2 align-label">CRM User</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="crmUser" placeholder="CRM User">
                                            </div>
                                            <label style="font-size: 20px;"  for="search_id" class="col-sm-1 align-label">&nbsp;</label>
                                        </div>
                                         <div class="form-group">
                                            <label for="search_id" class="col-sm-2 align-label">EventType</label>
                                            <div class="col-sm-9">
                                                <select id="search_id" name="search_id" class="form-control" value="" style="-webkit-appearance: menulist !important;">
                                                    <option value="1">AutoHeal</option>
                                                    <option value="2">SelfService</option>
                                                    <option value="3">Notification</option>
                                                </select>
                                            </div>
                                            <label style="font-size: 20px;cursor:pointer;" for="services" id="servicestxtbx" class="col-sm-1 align-label">&nbsp;</label>
                                        </div>
                                         <div class="form-group">
                                            <label for="search_id" class="col-sm-2 align-label">&nbsp;</label>
                                            <div class="col-sm-9">
                                                <button type="button" class="add-user-add-btn" data-dismiss="modal" aria-label="Close" localized="">Submit</button>
                                            </div>
                                            <label style="font-size: 20px;cursor:pointer;" for="services" id="servicestxtbx" class="col-sm-1 align-label">&nbsp;</label>
                                        </div>';
}

function validateCat($categoryname, $categoryValue, $db, $crmType, $ch_id)
{
    $sqlcat = "select name,value from  " . $GLOBALS['PREFIX'] . "event.categoryHistory where name='$categoryname' and value='$categoryValue' and crmType='$crmType' and ch_id='$ch_id'";
    $rescat = find_many($sqlcat, $db);
    $count = safe_count($rescat);
    return $count;
}

function validateSubCat($subcategoryName, $subcategoryValue, $db, $crmType, $ch_id)
{
    $sqlcat = "select name,value from  " . $GLOBALS['PREFIX'] . "event.categoryHistory where name='$subcategoryName' and value='$subcategoryValue' and crmType='$crmType' and ch_id='$ch_id'";
    $rescat = find_one($sqlcat, $db);
    $count = safe_count($rescat);
    return $count;
}

function Add_CRMCategory($CategoryData, $db)
{


    $crmType = "SN";
    $logged_username = $_SESSION["user"]["logged_username"];
    $companyName = $_SESSION["user"]["companyName"];
    $ch_id = $_SESSION["user"]["cId"];
    $categoryname = $CategoryData['categoryName'];
    $categoryValue = $CategoryData['categoryValue'];
    $CRMlogin_value = $CategoryData['CRMlogin_value'];


    $validateCat = validateCat($categoryname, $categoryValue, $db, $crmType, $ch_id);


    if ($validateCat > '0') {
        echo $res = "exists";
    } else {
        $catSQL = "insert into  " . $GLOBALS['PREFIX'] . "event.categoryHistory (ch_id,companyName,elementType,servicesId,categoryId,subcategoryId,name,value,crmType)values('$ch_id','$companyName',2,0,0,0,'$categoryname','$categoryValue','$crmType')";
        $catRes = redcommand($catSQL, $db);
        $sql = "select id from  " . $GLOBALS['PREFIX'] . "event.categoryHistory where ch_id='$ch_id' and companyName='$companyName' and elementType='2' and categoryId='0' and crmType='$crmType' and name='$categoryname' and value='$categoryValue'";
        $result = find_one($sql, $db);
        $categoryID = $result['id'];
        echo $res = "success";
    }

    return $res;
}

function Add_CRMSubCategory($SubCategoryData, $db)
{
    $crmType = "SN";
    $logged_username = $_SESSION["user"]["logged_username"];
    $companyName = $_SESSION["user"]["companyName"];
    $ch_id = $_SESSION["user"]["cId"];
    $subcategoryName = $SubCategoryData['subcategoryName'];
    $subcategoryValue = $SubCategoryData['subcategoryValue'];
    $validateSubCat = validateSubCat($subcategoryName, $subcategoryValue, $db, $crmType, $ch_id);

    $CRMlogin_value = $SubCategoryData['CRMlogin_value'];



    if ($validateSubCat > '0') {
        echo $res = 'exists';
    } else {
        $subcatSQL = "insert into  " . $GLOBALS['PREFIX'] . "event.categoryHistory (ch_id,companyName,elementType,servicesId,categoryId,subcategoryId,name,value,crmType)values('$ch_id','$companyName',3,0,'0',0,'$subcategoryName','$subcategoryValue','$crmType')";

        $subcatRes = redcommand($subcatSQL, $db);
        echo $res = "success";
    }
    return $res;
}

function GET_CRMCategory($db)
{

    $crmType = $_SESSION["user"]["crmType"];
    $crmType = "SN";
    $logged_username = $_SESSION["user"]["logged_username"];
    $companyName = $_SESSION["user"]["companyName"];
    $ch_id = $_SESSION["user"]["cId"];
    $sqlget = "select name,value from  " . $GLOBALS['PREFIX'] . "event.categoryHistory where ch_id='$ch_id' and companyName='$companyName' and elementType='2' and categoryId='0' and crmType='$crmType'";

    $result = find_many($sqlget, $db);
    if (safe_count($result) > 0) {
        return $result;
    } else {
        return array();
    }
}

function GET_CRMServices($db)
{
    $crmType = $_SESSION["user"]["crmType"];
    $crmType = "SN";
    $logged_username = $_SESSION["user"]["logged_username"];
    $companyName = $_SESSION["user"]["companyName"];
    $ch_id = $_SESSION["user"]["cId"];
    $sqlget = "select name,value from  " . $GLOBALS['PREFIX'] . "event.categoryHistory where ch_id='$ch_id' and companyName='$companyName' and elementType='1' and categoryId='0' and crmType='$crmType'";
    $result = find_many($sqlget, $db);
    if (safe_count($result) > 0) {
        return $result;
    } else {
        return array();
    }
}


function GET_CRMSubCategory($db)
{
    $crmType = $_SESSION["user"]["crmType"];
    $crmType = "SN";
    $logged_username = $_SESSION["user"]["logged_username"];
    $companyName = $_SESSION["user"]["companyName"];
    $ch_id = $_SESSION["user"]["cId"];
    $sqlget = "select name,value from  " . $GLOBALS['PREFIX'] . "event.categoryHistory where ch_id='$ch_id' and companyName='$companyName' and elementType='3' and crmType='$crmType'";
    $result = find_many($sqlget, $db);
    if (safe_count($result) > 0) {
        return $result;
    } else {
        return array();
    }
}

function GET_NotificationNames($selectedNIDlists, $db)
{
    $username = $_SESSION["user"]["username"];
    $sqlget = "select name from  " . $GLOBALS['PREFIX'] . "event.Notifications where id IN($selectedNIDlists)";
    $result = find_many($sqlget, $db);
    if (safe_count($result) > 0) {
        return $result;
    } else {
        return array();
    }
}

function GET_selectedNotificationsLists($selectedNIDlists, $db)
{
    $res = GET_NotificationNames($selectedNIDlists, $db);
    return $res;
}

function GET_NotificationsNames($selectedNIDs, $db)
{
    $username = $_SESSION["user"]["username"];
    $sqlget = "select name from  " . $GLOBALS['PREFIX'] . "event.Notifications where id IN($selectedNIDs)";
    $result = find_many($sqlget, $db);
    return $result;
}

function validateServices($serviceName, $serviceValue, $db, $crmType)
{
    $sqlcat = "select name,value from  " . $GLOBALS['PREFIX'] . "event.categoryHistory where name='$serviceName' and value='$serviceValue' and crmType='$crmType'";
    $rescat = find_many($sqlcat, $db);
    $count = safe_count($rescat);
    return $count;
}

function AddCRMservices($servicesData, $db)
{
    $crmType = $_SESSION["user"]["crmType"];
    $logged_username = $_SESSION["user"]["logged_username"];
    $companyName = $_SESSION["user"]["companyName"];
    $ch_id = $_SESSION["user"]["cId"];

    $serviceName = $servicesData['serviceName'];
    $serviceValue = $servicesData['serviceValue'];

    $validateSubCat = validateServices($serviceName, $serviceValue, $db, $crmType, $ch_id);
    if ($validateSubCat > '0') {
        echo $res = 'exists';
    } else {
        $subcatSQL = "insert into  " . $GLOBALS['PREFIX'] . "event.categoryHistory (ch_id,companyName,elementType,servicesId,categoryId,subcategoryId,name,value,crmType)values('$ch_id','$companyName',1,0,'0',0,'$serviceName','$serviceValue','$crmType')";
        $subcatRes = redcommand($subcatSQL, $db);
        echo $res = "success";
    }
    return $res;
}

function verify_Configuration($configurationData, $db)
{
    $crmType = "SN";

    $companyName = $_SESSION["user"]["companyName"];
    $ch_id = $configurationData['custId'];
    if ($crmType == "CW") {
        echo $sqlcat = "select ch_id,companyName,nid,notifName,crmType,services,category,subcategory,priority,crmUser,enabled,state,eventType from  " . $GLOBALS['PREFIX'] . "event.crmConfigure where ch_id='$ch_id' and machineName = '" . $configurationData['machineName'] . "' and nid='" . $configurationData['NIDs'] . "' and notifName='" . $configurationData['NotifName'] . "' and crmType='$crmType' and services='" . $configurationData['crmServices'] . "' and category='" . $configurationData['crmCategory'] . "' and subcategory='" . $configurationData['crmSubcategory'] . "' and priority='" . $configurationData['crmPriority'] . "' and crmUser='" . $configurationData['crmUser'] . "' and enabled='1' and state='NULL' and eventType='" . $configurationData['crmventType'] . "'";
    } else {
        $sqlcat = "select ch_id,companyName,nid,notifName,crmType,services,category,subcategory,priority,crmUser,enabled,state,eventType from  " . $GLOBALS['PREFIX'] . "event.crmConfigure where ch_id='$ch_id' and machineName = '" . $configurationData['machineName'] . "' and nid='" . $configurationData['NIDs'] . "' and notifName='" . $configurationData['NotifName'] . "' and crmType='$crmType' and services='-' and category='" . $configurationData['crmCategory'] . "' and subcategory='" . $configurationData['crmSubcategory'] . "' and priority='" . $configurationData['crmPriority'] . "' and crmUser='" . $configurationData['crmUser'] . "' and enabled='1' and state='NULL' and eventType='" . $configurationData['crmventType'] . "'";
    }

    $rescat = find_many($sqlcat, $db);
    $count = safe_count($rescat);
    return $count;
}

function configureCRM($configurationData, $db)
{

    $crmType = $_SESSION["user"]["crmType"];
    $logged_username = $_SESSION["user"]["logged_username"];
    $companyName = $_SESSION["user"]["companyName"];
    $ch_id = $_SESSION["user"]["cId"];
    $selectedNIDs = $configurationData['selectedNIDs'];
    $selectedNIDlists = explode(',', $selectedNIDs);
    $count = safe_count($selectedNIDlists);
    $nidNames = GET_NotificationsNames($selectedNIDs, $db);
    $category = $configurationData['crmCategory'];
    $crmSubcategory = $configurationData['crmSubcategory'];
    $crmPriority = $configurationData['crmPriority'];
    $crmUser = $configurationData['crmUser'];
    $crmventType = $configurationData['crmventType'];
    $custId = $configurationData['custId'];



    $CRMlogin_value = $configurationData['CRMlogin_value'];


    if ($crmType == "OTRS") {
        $crmTechnician = "-";
    } else if ($crmType == "ME") {
        $crmTechnician = $configurationData['crmTechnician'];
    } elseif ($crmType != 'CW') {
        $services = "-";
    } else {
        $services = $configurationData['crmServices'];
        $crmTechnician = "-";
    }

    if ($CRMlogin_value == '2') {
        $CRMlogin_valueCust = '5';
        $conn = $db;
        $sites = getSitelist($custId, $db);
        $companyName = getcompanyName($custId, $db);
        $machineNames1 = $sites['machineNames']['host'];
        $machineNamesLists = explode(",", $machineNames1);
        $machineids = $sites['machineIds']['machineid'];
        $machineidLists = explode(",", $machineids);




        foreach ($machineNamesLists as $key => $machineNames) {


            for ($i = 0; $i < $count; $i++) {
                $notifName = $nidNames[$i]['name'];
                $nid = trim($selectedNIDlists[$i]);
                $configurationData['NotifName'] = $notifName;
                $configurationData['NIDs'] = $nid;
                $configurationData['machineName'] = $machineNames;
                $verifyconfig = verify_Configuration($configurationData, $db);

                if ($verifyconfig > '0') {
                    $res = array("responseType" => "exists", "gridData" => $dataResponse);
                } else {

                    $subcatSQL = "insert into  " . $GLOBALS['PREFIX'] . "event.crmConfigure(ch_id,machineName,companyName,nid,notifName,crmType,services,category,subcategory,priority,crmUser,MEtechnician,enabled,state,eventType)values('$custId','$machineNames','$companyName','$nid','$notifName','$crmType','$services','$category','$crmSubcategory','$crmPriority','$crmUser','$crmTechnician','1','NULL',$crmventType)";

                    $subcatRes = redcommand($subcatSQL, $db);
                    if ($subcatRes) {
                        $res = array("responseType" => "success", "gridData" => $dataResponse);
                    } else {
                        $res = array("failed" => "failed", "gridData" => $dataResponse);
                    }
                }
            }
        }
    } elseif ($CRMlogin_value == '5') {
        $sites = getSitelist($custId, $db);
        $companyName = getcompanyName($custId, $db);
        $machineNames1 = $sites['machineNames']['host'];
        $machineNamesLists = explode(",", $machineNames1);
        $machineids = $sites['machineIds']['machineid'];
        $machineidLists = explode(",", $machineids);


        foreach ($machineNamesLists as $key => $machineNames) {

            $ch_id = $_SESSION['user']['cId'];
            for ($i = 0; $i < $count; $i++) {
                $notifName = $nidNames[$i]['name'];
                $nid = trim($selectedNIDlists[$i]);
                $configurationData['NotifName'] = $notifName;
                $configurationData['NIDs'] = $nid;
                $verifyconfig = verify_Configuration($configurationData, $db);
                if ($verifyconfig > '0') {
                    $res = array("responseType" => "exists", "gridData" => $dataResponse);
                } else {

                    $subcatSQL = "insert into  " . $GLOBALS['PREFIX'] . "event.crmConfigure(ch_id,machineName,companyName,nid,notifName,crmType,services,category,subcategory,priority,crmUser,MEtechnician,enabled,state,eventType)values('$custId','$machineNames','$companyName','$nid','$notifName','$crmType','$services','$category','$crmSubcategory','$crmPriority','$crmUser','$crmTechnician','1','NULL',$crmventType)";
                    $subcatRes = redcommand($subcatSQL, $db);
                    if ($subcatRes) {
                        $res = array("responseType" => "success", "gridData" => $dataResponse);
                    } else {
                        $res = array("failed" => "failed", "gridData" => $dataResponse);
                    }
                }
            }
        }
    }

    return $res;
}

function getcompanyName($custId, $db)
{
    $sql = "select A.companyName from " . $GLOBALS['PREFIX'] . "agent.channel A where A.eid='$custId'";
    $crm_resdtl = find_one($sql, $db) or die(mysql_error() . "error in selecting data");
    if (safe_count($crm_resdtl) > 0) {

        $companyName = $crm_resdtl['companyName'];
    } else {
        $companyName = "0";
    }

    return $companyName;
}

function getSitelist($custId, $db)
{

    $sql = "select A.sitelist from " . $GLOBALS['PREFIX'] . "agent.channel A where A.eid='$custId'";
    $crm_resdtl = find_one($sql, $db) or die(mysql_error() . "error in selecting data");
    if (safe_count($crm_resdtl) > 0) {

        $sitelist = $crm_resdtl['sitelist'];


        $machineData = fetch_MachineNames($sitelist, $db);
    } else {
        $machineData = "0";
    }

    return $machineData;
}

function getSitelistCRM($custId, $db)
{


    $sqlcust = "SELECT eid,companyName FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE ctype = '5' and channelId='$custId'";

    $res = find_many($sqlcust, $db) or die(mysql_error() . "CRM_InsertCrmDetails error");
    $eids = "";
    foreach ($res as $value) {
        $eids .= "'" . $value['eid'] . "',";
    }
    $eidList = rtrim($eids, ",");
    $sqlsite = "select compId,siteName from " . $GLOBALS['PREFIX'] . "agent.customerOrder O where O.compId in($eidList)";
    $resSite = find_many($sqlsite, $db) or die(mysql_error() . "CRM_InsertCrmDetails error");
    if (safe_count($resSite) > 0) {
        $siteNameList = "";
        foreach ($resSite as $value) {
            $siteNameList .= "'" . $value['siteName'] . "',";
        }

        $sitelist = rtrim($siteNameList, ",");
    } else {
        $sitelist = "0";
    }

    return $sitelist;
}

function getAllmachinNames($sitelists, $db)
{
    $sql = "select * from " . $GLOBALS['PREFIX'] . "asset.Machine where cust in($sitelists)";

    $machinesql = find_many($sql, $db);

    foreach ($machinesql as $key => $value) {
        $machineNames[] = $value['host'];
    }
    $lists = implode(',', $machineNames);
    $machineNameLists = "'" . str_replace(",", "','", $lists) . "'";

    return $machineNameLists;
}

function getCustIDforReseller($CRMlogin_valueCust, $conn, $cid)
{
    echo $sql = "SELECT eid,companyName FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE ctype = '$CRMlogin_valueCust' and channelId='$cid'";
    die();

    $res = find_many($sql, $conn);

    if (safe_count($res) > 0) {


        foreach ($res as $value) {
            $msg[] = $value['eid'];
        }
    } else {

        $msg[] = "continue";
    }

    return $msg;
}

function getAllConfiguredData($ch_id, $db)
{
    $sql = "select * from " . $GLOBALS['PREFIX'] . "asset.cmdbAssetData A where A.cid='$ch_id' and A.sn_dataname!='' and A.status='1'";
    $res = find_many($sql, $db);

    $recordList[] = "";
    if (safe_count($res) > 0) {
        foreach ($res as $key => $value) {
            $nid = $value['nid'];
            $notifName = $value['notifName'];
            $category = $value['category'];
            $subcategory = $value['subcategory'];
            $eventType = $value['eventType'];
            if ($eventType == '1') {
                $eventType = "AutoHeal";
            } elseif ($eventType == '3') {
                $eventType = "SelfHelp";
            } elseif ($eventType == '2') {
                $eventType = "Notification";
            } elseif ($eventType == '4') {
                $eventType = "Schedule";
            }
            $nid = '<p class="ellipsis" id="' . $value['nid'] . '" value="' . $value['nid'] . '" title="' . $value['nid'] . '">' . $value['nid'] . '</p>';
            $notifName = '<p class="ellipsis" id="' . $notifName . '" value="' . $notifName . '" title="' . $notifName . '">' . $notifName . '</p>';
            $category = '<p class="ellipsis" id="' . $category . '" value="' . $category . '" title="' . $category . '">' . $category . '</p>';
            $subcategory = '<p class="ellipsis" id="' . $subcategory . '" value="' . $subcategory . '" title="' . $subcategory . '">' . $subcategory . '</p>';
            $eventType = '<p class="ellipsis" id="' . $eventType . '" value="' . $eventType . '" title="' . $eventType . '">' . $eventType . '</p>';
            $recordList[] = array("SN_RowId" => $value['id'], $nid, $notifName, $category, $subcategory, $eventType);
        }
    } else {
        $noRecord = "No records";
        $recordList[] = array("SN_RowId" => $noRecord, $noRecord, $noRecord, $noRecord, $noRecord, $noRecord);
    }

    return $recordList;
}

function CRM_conguredata($db, $configData)
{

    if (($configData['CRMlogin_value'] == '2') || ($configData['CRMlogin_value'] == 2)) {
        $custId = $configData['custId'];
    } else if (($configData['CRMlogin_value'] == '5') || ($configData['CRMlogin_value'] == 5)) {
        $custId = $_SESSION['user']['cId'];
    }
    $siltelistOrg = $configData['custSiteName'];
    $siltelist = $configData['custSiteName'];
    $siltelist = str_replace(",", "','", $siltelist);

    $ticketType = safe_json_decode($configData['ticketType'], true);

    foreach ($ticketType as $key => $value) {
        if ($key == 'autohealcheck') {
            $autoheal = 1;
        } elseif ($key == 'notifcheck') {
            $notification = 1;
        } elseif ($key == 'selfhelpcheck') {
            $selfhelp = 1;
        } elseif ($key == 'schedulecheck') {
            $schedule = 1;
        }
    }


    $existsCount = checkExistanceSite($db, $siltelistOrg, $custId, $configData['CRMlogin_value']);
    $crmjsonData = $configData['crmjsonData'];
    $crmClosejsonData = $configData['crmClosejsonData'];
    if ($existsCount > 0) {
        if (($configData['CRMlogin_value'] == 5) || ($configData['CRMlogin_value'] == '5')) {
            $sitename = $configData['custSiteName'];
            $crmjsonData = $configData['crmjsonData'];
            $chId = $configData['custId'];
            if (strpos($sitename, ',') !== false) {

                $sitelist = explode(",", $sitename);
                foreach ($sitelist as $key => $value) {
                    $sql = "update  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure set siteNames='$value',jsonData='$crmjsonData',jsonCloseData='$crmClosejsonData',autoheal='$autoheal',notification='$notification',selfhelp='$selfhelp',schedule='$schedule' where chid='$chId' and siteNames='$value'";
                    $result = redcommand($sql, $db);
                }
            } else {
                $sql = "update  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure set siteNames='$sitename',jsonData='$crmjsonData',jsonCloseData='$crmClosejsonData',autoheal='$autoheal',notification='$notification',selfhelp='$selfhelp',schedule='$schedule' where chid='$chId'";
                $result = redcommand($sql, $db);
            }

            if ($result) {
                $result = "success";
            } else {
                $result = "failed";
            }
        } else if (($configData['CRMlogin_value'] == 2) || ($configData['CRMlogin_value'] == '2')) {
            $sqlSite = "select host from " . $GLOBALS['PREFIX'] . "core.Census where site in('$siltelistOrg')";


            $resultSite = find_many($sqlSite, $db);
            $count = safe_count($resultSite);

            if ((($count) == '0') || (($count) == 0)) {
                $result = "empty";
            } else {
                $hostNames = "";
                foreach ($resultSite as $key => $value) {
                    $hostNames .= $value['host'] . ",";
                }

                $hostNameList = rtrim($hostNames, ",");

                $chId = $configData['custId'];
                $sql = "update  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure set siteNames='$siltelistOrg',jsonData='$crmjsonData',jsonCloseData='$crmClosejsonData',autoheal='$autoheal',notification='$notification',selfhelp='$selfhelp',schedule='$schedule' where chid='$chId'";
                $result = redcommand($sql, $db);
                if ($result) {
                    $result = "success";
                } else {
                    $result = "failed";
                }
            }
        }
    } else {
        if ($configData['CRMlogin_value'] == '2') {
            if ($configData['custName'] == "All") {
                $custId = $_SESSION['user']['cId'];
                $resSites = getSitelistCRM($custId, $db);
                $sqlSite = "select site,host from " . $GLOBALS['PREFIX'] . "core.Census where site in($resSites)";

                $resultSite = find_many($sqlSite, $db);
                $count = safe_count($resultSite);
                if ((($count) == '0') || (($count) == 0)) {
                    $result = "empty";
                } else {
                    $hostNames = "";
                    foreach ($resultSite as $key => $value) {
                        $hostNames[$key] .= $value['host'] . ",";
                    }
                    $hostNameList = rtrim($hostNames, ",");
                    $sitename = $configData['custSiteName'];
                    $crmjsonData = $configData['crmjsonData'];
                    $customerType = "5";
                    $chids = CRM_GetResellerCustomersIdComp($customerType, $db, $custId);
                    $chis_lists = ltrim(rtrim(str_replace("','", ",", $chids), "'"), "'");
                    $sites = CRM_GetResellerCustomerssiteComp($customerType, $db, $custId, $chis_lists);

                    $insertSql = "insert into  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure(chid,siteNames,jsonData,jsonCloseData,autoheal,notification,selfhelp,schedule)values('$chis_lists','$sites','$crmjsonData','$crmClosejsonData','$autoheal','$notification','$selfhelp','$schedule')";

                    $subcatRes = redcommand($insertSql, $db);
                    if ($subcatRes) {
                        $result = "success";
                    } else {
                        $result = "failed";
                    }
                }
            } else {
                $sqlSite = "select host from " . $GLOBALS['PREFIX'] . "core.Census where site in('$siltelist')";

                $resultSite = find_many($sqlSite, $db);
                $count = safe_count($resultSite);

                if ((($count) == '0') || (($count) == 0)) {
                    $result = "empty";
                } else {

                    $sitename = $configData['custSiteName'];
                    $chId = $configData['custId'];
                    $crmjsonData = $configData['crmjsonData'];
                    $insertSql = "insert into  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure(chid,siteNames,jsonData,jsonCloseData,autoheal,notification,selfhelp,schedule)values('$chId','$sitename','$crmjsonData','$crmClosejsonData','$autoheal','$notification','$selfhelp','$schedule')";
                    $subcatRes = redcommand($insertSql, $db);
                    if ($subcatRes) {
                        $result = "success";
                    } else {
                        $result = "failed";
                    }
                }
            }
        } else if ($configData['CRMlogin_value'] == '5') {
            $sqlSite = "select host from " . $GLOBALS['PREFIX'] . "core.Census where site in('$siltelist')";

            $resultSite = find_many($sqlSite, $db);
            $count = safe_count($resultSite);
            if ((($count) == '0') || (($count) == 0)) {
                $result = "empty";
            } else {
                $sitename = $configData['custSiteName'];

                $chId = $configData['custId'];
                $crmjsonData = $configData['crmjsonData'];

                if (strpos($sitename, ',') !== false) {

                    $sitelist = explode(",", $sitename);
                    foreach ($sitelist as $key => $value) {
                        $insertSql = "insert into  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure(chid,siteNames,jsonData,jsonCloseData,autoheal,notification,selfhelp,schedule)values('$chId','$value','$crmjsonData','$crmClosejsonData','$autoheal','$notification','$selfhelp',$schedule)";
                        $subcatRes = redcommand($insertSql, $db);
                    }
                } else {

                    $insertSql = "insert into  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure(chid,siteNames,jsonData,jsonCloseData,autoheal,notification,selfhelp,schedule)values('$chId','$sitename','$crmjsonData','$crmClosejsonData','$autoheal','$notification','$selfhelp',$schedule)";
                    $subcatRes = redcommand($insertSql, $db);
                }

                if ($subcatRes) {
                    $result = "success";
                } else {
                    $result = "failed";
                }
            }
        }
    }


    return $result;
}

function checkExistanceSite($db, $siltelist, $custId, $loginType)
{

    if (($loginType == 2) || ($loginType == '2')) {
        $customerType = '5';
        $sql = "select * from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where siteNames in('$siltelist') and chid in($custId)";
    } else if (($loginType == 5) || ($loginType == '5')) {
        $siltelist1 = "'" . str_replace(",", "','", $siltelist) . "'";

        $sql = "select * from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where siteNames in($siltelist1) and chid='$custId'";
    }

    $sqlRun = find_many($sql, $db);
    $count = safe_count($sqlRun);
    return $count;
}

function CRM_exportTicketdtls($siteName, $startDate, $endDate, $custType, $cid, $db)
{
    $startDateUnix = strtotime($startDate);
    $endDateUnix = strtotime($endDate);
    $index = 2;
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'TicketID');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Notification Name');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'siteName');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'machineName');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'status');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Actioned');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Type');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', 'CreatedTime');
    $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Create SentPayload');
    $objPHPExcel->getActiveSheet()->setCellValue('J1', 'Create ReceivedResponse');
    $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Close SentPayload');
    $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Close ReceivedResponse');

    if (($custType == 5) || ($custType == '5')) {
        $sql = "select * from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where chid='$cid'";
    } else if (($custType == 2) || ($custType == '2')) {
        $customesCChannelid = '5';
        $chids = CRM_GetResellerCustomersIdComp($customesCChannelid, $db, $cid);
        $chis_lists = ltrim(rtrim(str_replace("','", ",", $chids), "'"), "'");
        $sql = "select * from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where chid in('$chis_lists')";
    }
    $sqlRes = find_many($sql, $db);
    foreach ($sqlRes as $key => $value) {
        $chid = $value['chid'];
        $siteNames = $value['siteNames'];
    }
    $siteNames = "'" . str_replace(",", "','", $siteNames) . "'";
    $sqlevent = "select * from  " . $GLOBALS['PREFIX'] . "event.ticketEvents where siteName in('$siteName') and eventDate BETWEEN $startDateUnix and $endDateUnix";


    $resswl = find_many($sqlevent, $db);

    if (safe_count($resswl) > 0) {
        foreach ($resswl as $key => $value) {

            $ticketId = $value['ticketId'];
            $ticketType = $value['ticketType'];
            $notifName = $value['ticketSub'];
            $status = $value['status'];
            $actioned = $value['eventDate'];
            $ccSentPayload = $value['ccSentPayload'];
            $ccResppayload = $value['ccResppayload'];
            $closeSentPayload = $value['closeSentPayload'];
            $closeRespPayload = $value['closeRespPayload'];
            if (empty($ccSentPayload) || ($ccSentPayload == '')) {
                $closeRespPayload = '';
            } else if (empty($ccResppayload) || ($ccResppayload == '')) {
                $ccResppayload = '';
            } else if (empty($closeSentPayload) || ($closeSentPayload == '')) {
                $closeSentPayload = '';
            } else if (empty($closeRespPayload) || ($closeRespPayload == '')) {
                $closeRespPayload = '';
            }

            if ((empty($actioned)) || ($actioned == NULL)) {
                $actioned = "";
            } else {
                $actioned = "";
            }

            if (empty($value['crontime'])) {
                $cronTime = "";
            } else {
                $cronTime = date("m/d/Y h:i:s", $value['eventDate']);
            }
            if (($ticketType == 1) || ($ticketType == '1')) {
                $type = 'Autoheal';
            } elseif (($ticketType == 2) || ($ticketType == '2')) {
                $type = 'Notification';
            } elseif (($ticketType == 3) || ($ticketType == '3')) {
                $type = 'Selfhelp';
            } elseif (($ticketType == 4) || ($ticketType == '4')) {
                $type = 'Schedule';
            } else {
                $type = 'Unknown';
            }
            $siteName = $value['siteName'];
            $str = $value['siteName'];
            if (strpos($str, '__') !== false) {

                $rs = explode("__", $str);
                $count = safe_count($rs);
                $resp = array();
                for ($i = 0; $i < $count; $i++) {
                    if (is_numeric($rs[$i])) {
                    } else {
                        $resp[] .= $rs[$i];
                    }
                }
                $str = $siteName;
                if (strpos($str, '_') !== false) {

                    $rs = explode("_", $str);
                    $count = safe_count($rs);
                    $resp = array();
                    for ($i = 0; $i < $count; $i++) {
                        if (is_numeric($rs[$i])) {
                        } else {
                            $resp[] .= $rs[$i];
                        }
                    }
                    $siteName = implode(" ", $resp);
                }
                $siteName = implode(" ", $resp);
            } elseif (strpos($str, '_') !== false) {

                $rs = explode("_", $str);
                $count = safe_count($rs);
                $resp = array();
                for ($i = 0; $i < $count; $i++) {
                    if (is_numeric($rs[$i])) {
                    } else {
                        $resp[] .= $rs[$i];
                    }
                }
                $siteName = implode(" ", $resp);
            }

            $machineName = $value['machineName'];

            if (($ticketType == '1') || ($ticketType == 1)) {
                $datalis = safe_json_decode($value['ccSentPayload'], true);
                $inter = $datalis['internalNotes'];
                $r = explode("Site:", $inter);
                $s = explode("Time:", $r[1]);
                $sitelist = trim($s[0]);


                $siteNaming = explode("__", $sitelist);
                $str = $siteNaming[0];

                $data = $r[0] . "Site: " . $str . " Time:" . $s[1];
                $datalis['internalNotes'] = $data;
                $datalis['problemDescription'] = $data;
                $datalis['notes'] = $data;
                $ccSentPayload = json_encode($datalis);


                $datalisclose = safe_json_decode($value['closeSentPayload'], true);
                $interclose = $datalisclose['problemDescription'];
                $rclose = explode("Site:", $interclose);
                $sclose = explode("Time:", $rclose[1]);
                $sitelistclose = trim($sclose[0]);

                $siteNamingclose = explode("__", $sitelistclose);
                $strclose = $siteNamingclose[0];

                $dataclose = $rclose[0] . "Site: " . $strclose . " Time:" . $sclose[1];
                $datalisclose['problemDescription'] = $dataclose;
                $closeSentPayload = json_encode($datalisclose);
            } else {
                $ccSentPayload = $value['ccSentPayload'];
            }


            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $ticketId);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $notifName);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $siteName);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $machineName);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $status);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $actioned);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, $type);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $index, $cronTime);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $index, $ccSentPayload);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $index, $ccResppayload);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $index, $closeSentPayload);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $index, $closeRespPayload);
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }
    $objPHPExcel->setActiveSheetIndex(0);
    $fn = "TicketLists.xls";

    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function CRM_GetSiteDataList($siteName, $db, $Cid)
{
    $tday = date('Y-m-d');
    $startDate = strtotime('-7 day', strtotime($tday));
    $endDate = strtotime('+1 day', strtotime($tday));
    $sqlevent = "select * from  " . $GLOBALS['PREFIX'] . "event.ticketEvents where siteName in('$siteName') and eventDate BETWEEN $startDate and $endDate  ORDER BY teid DESC";

    $eventRes = find_many($sqlevent, $db);

    $recordList = [];


    if (safe_count($eventRes) > 0) {


        $serialNum = 1;
        foreach ($eventRes as $key => $value) {
            $teid = $value['teid'];
            $ticketId = $value['ticketId'];
            $notifName = $value['ticketSub'];
            $status = $value['status'];
            $actioned = $value['ticketClose'];
            $syncStatus = $value['syncStatus'];
            $ticketType = $value['ticketType'];
            $eventDate = date("m/d/Y h:i:s", $value['eventDate']);
            $siteName = $value['siteName'];
            $machineName = $value['machineName'];
            if (($syncStatus == '0') || ($syncStatus == 0)) {
                $cronTime = "-";
                $ticketId = "-";
                $actioned = "Not created";
            } else {
                $ticketId = $value['ticketId'];
                $cronTime = date("m/d/Y h:i:s", $value['crontime']);
                $actioned = date("m/d/Y h:i:s", $value['crontime']);
            }
            if (($ticketType == 1) || ($ticketType == '1')) {
                $eventType = "Autoheal";
            } else if (($ticketType == 2) || ($ticketType == '2')) {
                $eventType = "Notification";
            }
            if (($ticketType == 3) || ($ticketType == '3')) {
                $eventType = "Selfhelp";
            }
            if (($ticketType == 4) || ($ticketType == '4')) {
                $eventType = "Schedule";
            }
            $machineName = "<p class='ellipsis' id='$machineName' value='$machineName' title='$machineName'>$machineName</p>";
            $serialNumData = "<p class='ellipsis' id='$serialNum' value='$serialNum' title='$serialNum'>$serialNum</p>";
            $eventDate = '<p class="ellipsis" id="' . $eventDate . '" value="' . $eventDate . '" title="' . $eventDate . '">' . $eventDate . '</p>';
            $siteName = '<p class="ellipsis" id="' . $siteName . '" value="' . $siteName . '" title="' . $siteName . '">' . $siteName . '</p>';
            $ticketId = '<p class="ellipsis" id="' . $ticketId . '" value="' . $ticketId . '" title="' . $ticketId . '"><a style="color:#48b2e4;" onclick="get_JSONPayload(' . $teid . ');" href="#">' . $ticketId . '</a></p>';
            $notifName = '<p class="ellipsis" id="' . $notifName . '" value="' . $notifName . '" title="' . $notifName . '">' . $notifName . '</p>';
            $status = '<p class="ellipsis" id="' . $status . '" value="' . $status . '" title="' . $status . '">' . $status . '</p>';
            $actioned = '<p class="ellipsis" id="' . $actioned . '" value="' . $actioned . '" title="' . $actioned . '">' . $actioned . '</p>';
            $cronTime = '<p class="ellipsis" id="' . $cronTime . '" value="' . $cronTime . '" title="' . $cronTime . '">' . $cronTime . '</p>';
            $Type = '<p class="ellipsis" id="' . $eventType . '" value="' . $eventType . '" title="' . $eventType . '">' . $eventType . '</p>';

            $recordListData[] = array("DT_RowId" => $teid, $serialNum, $notifName, $eventDate, $actioned, $machineName, $ticketId, $status, $Type);
            $serialNum++;
        }
    } else {
        $recordListData = array();
    }

    return $recordListData;
}

function CRM_GetTicketListData($custType, $cid, $siteName, $db)
{


    $db = db_connect();

    if (($custType == 5) || ($custType == '5')) {
        $sql = "select * from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where chid='$cid'";
    } else if (($custType == 2) || ($custType == '2')) {
        $customesCChannelid = '5';
        $chids = CRM_GetResellerCustomersIdComp($customesCChannelid, $db, $cid);
        $chis_lists = ltrim(rtrim(str_replace("','", ",", $chids), "'"), "'");
        $sql = "select * from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where chid in($chids)";
    }

    $sqlRes = find_many($sql, $db);
    $chid = "";
    $siteNames = "";
    foreach ($sqlRes as $key => $value) {
        $chid .= "'" . $value['chid'] . "',";
        $siteNames .= "'" . $value['siteNames'] . "',";
    }
    $siteNameLists = rtrim($siteNames, ",");


    $tday = date('Y-m-d');
    $startDate = strtotime('-7 day', strtotime($tday));
    $endDate = strtotime('+1 day', strtotime($tday));

    $sqlevent = "select * from  " . $GLOBALS['PREFIX'] . "event.ticketEvents where siteName in('$siteName') "
        . "and eventDate BETWEEN $startDate and $endDate "
        . "ORDER BY teid DESC";

    $eventRes = find_many($sqlevent, $db);
    if (safe_count($eventRes) > 0) {
        $serialNum = 1;
        foreach ($eventRes as $key => $value) {
            $teid = $value['teid'];
            $ticketId = $value['ticketId'];
            $notifName = $value['ticketSub'];
            $status = $value['status'];
            $actioned = $value['ticketClose'];
            $eventDate = date("m/d/Y h:i:s", $value['eventDate']);
            $syncStatus = $value['syncStatus'];
            $ticketType = $value['ticketType'];
            $siteName = $value['siteName'];
            $machineName = $value['machineName'];
            if (($syncStatus == '0') || ($syncStatus == 0)) {
                $cronTime = "-";
                $ticketId = "-";
                $actioned = "Not created";
            } else {
                $ticketId = $value['ticketId'];
                $actioned = date("m/d/Y h:i:s", $value['crontime']);
            }
            if (($ticketType == 1) || ($ticketType == '1')) {
                $eventType = "Autoheal";
            } else if (($ticketType == 2) || ($ticketType == '2')) {
                $eventType = "Notification";
            } else if (($ticketType == 3) || ($ticketType == '3')) {
                $eventType = "Selfhelp";
            } else if (($ticketType == 4) || ($ticketType == '4')) {
                $eventType = "Schedule";
            }
            $machineName = "<p class='ellipsis' id='$machineName' value='$machineName' title='$machineName'>$machineName</p>";
            $serialNumData = "<p class='ellipsis' id='$serialNum' value='$serialNum' title='$serialNum'>$serialNum</p>";

            $siteName = '<p class="ellipsis" id="' . $siteName . '" value="' . $siteName . '" title="' . $siteName . '">' . $siteName . '</p>';
            $eventDate = '<p class="ellipsis" id="' . $eventDate . '" value="' . $eventDate . '" title="' . $eventDate . '">' . $eventDate . '</p>';
            $ticketId = '<p class="ellipsis" id="' . $ticketId . '" value="' . $ticketId . '" title="' . $ticketId . '"><a style="color:#48b2e4;" onclick="get_JSONPayload(' . $teid . ');" href="#">' . $ticketId . '</a></p>';
            $notifName = '<p class="ellipsis" id="' . $notifName . '" value="' . $notifName . '" title="' . $notifName . '">' . $notifName . '</p>';
            $status = '<p class="ellipsis" id="' . $status . '" value="' . $status . '" title="' . $status . '">' . $status . '</p>';
            $actioned = '<p class="ellipsis" id="' . $actioned . '" value="' . $actioned . '" title="' . $actioned . '">' . $actioned . '</p>';
            $cronTime = '<p class="ellipsis" id="' . $cronTime . '" value="' . $cronTime . '" title="' . $cronTime . '">' . $cronTime . '</p>';
            $eventType1 = '<p class="ellipsis" id="' . $eventType . '" value="' . $eventType . '" title="' . $eventType . '">' . $eventType . '</p>';
            $id = '<p class="ellipsis" id="' . $eventType . '" value="' . $eventType . '" title="' . $eventType . '">' . $eventType . '</p>';

            $recordListData[] = array("DT_RowId" => $teid, $serialNum, $notifName, $eventDate, $actioned, $machineName, $ticketId, $status, $eventType1);
            $serialNum++;
        }
    } else {

        $recordListData = array();
    }

    return $recordListData;
}

function CRM_GetJsonData($id, $db)
{
    $db = db_connect();
    $json = getJsondataofID($id, $db);
    echo $json;
}

function CRM_GetSiteNameJsonData($id, $db)
{
    $db = db_connect();
    $sql = "select siteName from  " . $GLOBALS['PREFIX'] . "event.ticketEvents where teid='$id'";
    $eventRes = find_one($sql, $db) or die("error");
    $siteName = $eventRes['siteName'];
    return $siteName;
}

function getJsondataofID($siteName, $db)
{
    $db = db_connect();
    $sql = "select jsonData from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where siteNames='$siteName' limit 1";
    $eventRes = find_one($sql, $db);
    $jsonData = $eventRes['jsonData'];
    return $jsonData;
}
function CRM_GetCloseJsonData($id, $db)
{
    $db = db_connect();
    $json = getCloseJsondataofID($id, $db);
    echo $json;
}

function getCloseJsondataofID($siteName, $db)
{
    $db = db_connect();
    $sql = "select jsonCloseData from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where siteNames='$siteName' limit 1";
    $eventRes = find_one($sql, $db);
    $jsonData = $eventRes['jsonCloseData'];
    return $jsonData;
}

function CRM_UpdateSiteNameJsonData($siteName, $jsonData, $db)
{
    $db = db_connect();
    $sql = "update  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure set jsonData='$jsonData' where siteNames='$siteName'";
    $result = redcommand($sql, $db) or die("error CRM_UpdateSiteNameJsonData");
    if ($result) {
        $result = "success";
    } else {
        $result = "failed";
    }
    return $result;
}

function CRM_UpdateSiteNameCloseJsonData($siteName, $jsonData, $db)
{
    $db = db_connect();
    $sql = "update  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure set jsonCloseData='$jsonData' where siteNames='$siteName'";
    $result = redcommand($sql, $db) or die("error CRM_UpdateSiteNameCloseJsonData");
    if ($result) {
        $result = "success";
    } else {
        $result = "failed";
    }
    return $result;
}
