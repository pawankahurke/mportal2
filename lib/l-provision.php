<?php




function PROV_checkCustomerOrderNumExists($customerno, $orderno, $type) {

    $db = db_connect();
    db_change($GLOBALS['PREFIX'].'agent', $db);

    if ($type == 'NEW') {
        $csql = "select customerNum, orderNum from customerOrder where customerNum = '$customerno' limit 1;";
        $cres = find_one($csql, $db);
        if ($cres) {
            return 'CEXIST';
        }

        $osql = "select customerNum, orderNum from customerOrder where orderNum = '$orderno' limit 1;";
        $ores = find_one($osql, $db);
        if ($ores) {
            return 'OEXIST';
        }
    }

    if ($type == 'NEWORDER') {
        $osql = "select customerNum, orderNum from customerOrder where orderNum = '$orderno' limit 1;";
        $ores = find_one($osql, $db);
        if ($ores) {
            return 'EXIST';
        } else {
            return 1;
        }
    }


    }

function PROV_checkCustomerSiteExists($customerno,$siteName,$type) {

    $db = db_connect();
    db_change($GLOBALS['PREFIX'].'agent', $db);
    
    if($siteName == ''){
        
        return 'Empty';
    } else {
        if ($type == 'NEW') {
            $csql = "select customerNum, orderNum from customerOrder where siteName='$siteName' limit 1;";
            $cres = find_one($csql, $db);
            if ($cres) {
                return 'Exist';
            } else {
                return $siteName;
            }
        } else {
            $csql = "select customerNum, orderNum,siteName,coustomerFirstName,coustomerLastName,emailId from customerOrder where customerNum = '$customerno' limit 1;";
            $cres = find_one($csql, $db);
            if ($cres) {
                return $cres;
            }
        }
    }
}





function PROV_commonLoopFunction($sqlres) {

    $jsonarray = array();
    $datetoday = gmdate("m/d/Y", time());

    foreach ($sqlres as $key => $value) {

        $jsonarray[$key]['customerNum'] = $value['customerNum'];
        $jsonarray[$key]['orderNum'] = $value['orderNum'];
        $jsonarray[$key]['customerFirstName'] = $value['coustomerFirstName'];
        $jsonarray[$key]['emailId'] = $value['emailId'];
        $jsonarray[$key]['SKUNum'] = $value['SKUNum'];
        $jsonarray[$key]['SKUDesc'] = $value['SKUDesc'];
        $jsonarray[$key]['orderDate'] = $value['orderDate'];
        $jsonarray[$key]['contractEndDate'] = $value['contractEndDate'];

        if ($value['contractEndDate'] > $datetoday) {
            $status = 'Active';
        } else {
            $status = 'Expired';
        }
        $jsonarray[$key]['contractStatus'] = $status;
        $orderDetails = getInstallDetails($value['customerNum'], $value['orderNum']);
        $jsonarray[$key]['installDetails'] = $orderDetails;
    }

    $jsonres = json_encode($jsonarray);
    return $jsonres;
}

function PROV_customerProccessInfo($compname) {

    $db = db_connect();
    db_change($GLOBALS['PREFIX'].'agent', $db);

    $proccessinfo = array();

    $compsql = "select eid, companyName, emailId from channel where companyName='$compname' limit 1";
    $compres = find_one($compsql, $db);
    $cid = $compres['eid'];

    $_SESSION['user']['cId'] = $cid;

    $proccessinfo['cid'] = $cid;
    $proccessinfo['compname'] = $compres['companyName'];
    $proccessinfo['emailid'] = $compres['emailId'];

    return $proccessinfo;
}

function PROV_validateCompanyName($compname) {
    $db = db_connect();
    db_change($GLOBALS['PREFIX'].'agent', $db);
    
    $compsql = "select companyName, channelId from channel where companyName = '$compname' order by eid desc limit 1;";
    $compres = find_one($compsql, $db);
    
    if($compname == $compres['companyName']) {
        return '';
    } else {
        return 'Company name does not exist.';
    }
}

function PROV_convertDateToUnix($date) {
    $unixdate = strtotime($date);
    return $unixdate;
}

function PROV_updateJsonResult($res) {
    
    $res1 = json_encode($res);
    
    $res2 = safe_json_decode($res1);
    
    foreach ($res2 as $key => $value) {
        echo $key . ' -- ' . $value;
    }
    
    
}


function validateName($param) {
    if(!preg_match("/^[A-Za-z-`'.,&\\s]+$/", $param)) {
        return false;
    } else {
        return true;
    }
}


function validateEmail($param) {
    if(!filter_var($param, FILTER_VALIDATE_EMAIL)) {
        return false;
    } else {
        return true;
    }
}


function validationMsg($name) {
    return $name . " name field should not contain any special characters other than - ` ' . , & ";
}



function PROV_addSubscriberValidation($compname, $customerno, $orderno, $firstname, $lastname, $emailid, $SKU, $type) {

    $errmsg = '';
    if ($compname == '') {
        $errmsg = "Company name parameter not passed.";
    } else if ($customerno == '') {
        $errmsg = "Customer number parameter not passed.";
    } else if ($orderno == '') {
        $errmsg = "Order number parameter not passed.";
    } else if ($firstname == '') {
        $errmsg = "First name parameter not passed.";
    } else if ($lastname == '') {
        $errmsg = "Last name parameter not passed.";
    } else if ($emailid == '') {
        $errmsg = "Email id parameter not passed.";
    } else if ($SKU == '') {
        $errmsg = "Subscription SKU Value parameter not passed.";
    } else if((strlen($customerno) < 8 || strlen($customerno)) > 18 && $type == 'NEW') {
        $errmsg = "Customer number should be min. 8 and max. 18 digits only.";
    } else if(strlen($orderno) < 8 || strlen($orderno) > 18) {
        $errmsg = "Order number should be min. 8 and max. 18 digits only.";
    } else if(!validateName($firstname)) {
        $errmsg = validationMsg("First");
    } else if(!validateName($lastname)) {
        $errmsg = validationMsg("Last");
    } else if(!validateName($compname)) {
        $errmsg = validationMsg("Company");
    } else if(!validateEmail($emailid)) {
        $errmsg = "Email address is not valid.";
    } else {
        if($type == 'NEW') {
            $cores = PROV_checkCustomerOrderNumExists($customerno, $orderno, "NEW");
            if ($cores == 'CEXIST') {
                $errmsg = "Customer number already exists.";
            } else if ($cores == 'OEXIST') {
                $errmsg = "Order number already exists.";
            } else {
                $errmsg = '';
            }
        } else {
            $cores = PROV_checkCustomerOrderNumExists($customerno, $orderno, "NEWORDER");
            if ($cores == 'EXIST') {
                $errmsg = "Order number already exists.";
            } else {
                $errmsg = '';
            }
        }
        
    }

    return $errmsg;
}

function PROV_getSubscribersBySkuValidation($compname, $skuref, $rowlimit) {

    $errmsg = '';
    if ($compname == '') {
        $errmsg = "Company name parameter not passed.";
    } else if ($skuref == '') {
        $errmsg = "Subscription SKU value parameter not passed.";
    } else if ($rowlimit == '') {
        $errmsg = "Row limit value parameter not passed.";
    } else if(!validateName($compname)) {
        $errmsg = validationMsg("Company");
    } else {
        $errmsg = PROV_validateCompanyName($compname);
    }

    return $errmsg;
}

function PROV_getSubscriptionsBySubscriberValidation($custno, $compname) {

    $errmsg = '';
    if ($custno == '') {
        $errmsg = "Customer number parameter not passed.";
    } else if ($compname == '') {
        $errmsg = "Company name parameter not passed.";
    } else if(!validateName($compname)) {
        $errmsg = validationMsg("Company");
    } else {
        $errmsg = PROV_validateCompanyName($compname);
    }

    return $errmsg;
}

function PROV_getSubscriberDetailsValidation($compname, $rowlimit) {
    
    $errmsg = '';
    if($compname == '') {
        $errmsg = 'Company name parameter not passed.';
    } else if($rowlimit == '') {
        $errmsg = 'Row limit value parameter not passed.';
    } else if(!validateName($compname)) {
        $errmsg = validationMsg("Company");
    }
    return $errmsg;    
}

function PROV_getSubscriptionDetailsValidation($orderno, $compname) {

    $errmsg = '';
    if ($orderno == '') {
        $errmsg = "Order number parameter not passed.";
    } else if ($compname == '') {
        $errmsg = "Company name parameter not passed.";
    } else if(!validateName($compname)) {
        $errmsg = validationMsg("Company");
    } else {
        $errmsg = PROV_validateCompanyName($compname);
    }

    return $errmsg;
}

function PROV_renewupgradeSubscriptionValidation($customerno, $oldorderno, $neworderno, $firstname, $lastname, $emailid, $SKU) {

    $errmsg = '';
    if ($customerno == '') {
        $errmsg = 'Customer number parameter not passed.';
    } else if ($oldorderno == '') {
        $errmsg = 'Old order number parameter not passed.';
    } else if ($neworderno == '') {
        $errmsg = 'Order number parameter not passed.';
    } else if ($firstname == '') {
        $errmsg = 'First name paramter not passed.';
    } else if ($lastname == '') {
        $errmsg = 'Last name parameter not passed.';
    } else if ($emailid == '') {
        $errmsg = 'Email id paramter not passed.';
    } else if ($SKU == '') {
        $errmsg = 'Subscription SKU value parameter not passed.';
    } else if(strlen($neworderno) < 8 || strlen($neworderno) > 18) {
        $errmsg = 'Order number should be min. 8 and max. 18 digits only.';
    } else if(!validateName($firstname)) {
        $errmsg = validationMsg("First");
    } else if(!validateName($lastname)) {
        $errmsg = validationMsg("Last");
    } else if(!validateEmail($emailid)) {
        $errmsg = "Email address is not valid.";
    } else {
        $cores = PROV_checkCustomerOrderNumExists($customerno, $neworderno, "NEWORDER");
        if ($cores == 'EXIST') {
            $errmsg = "Order number already exists.";
        } else {
            $errmsg = '';
        }
    }
    return $errmsg;
}

function PROV_revokeregenerateSubscriptionValidation($customerno, $orderno, $emailid, $compname) {

    $errmsg = '';
    if ($customerno == '') {
        $errmsg = 'Customer number parameter not passed.';
    } else if ($orderno == '') {
        $errmsg = 'Order number parameter not passed.';
    } else if ($emailid == '') {
        $errmsg = 'Email id parameter not passed.';
    } else if ($compname == '') {
        $errmsg = 'Company name parameter not passed.';
    } else if(!validateName($compname)) {
        $errmsg = validationMsg("Company");
    } else if(!validateEmail($emailid)) {
        $errmsg = "Email address is not valid.";
    } else {
        $errmsg = PROV_validateCompanyName($compname);
    }

    return $errmsg;
}

function PROV_ProvCountValidation($compname, $startdate, $enddate) {
    $errmsg = '';
    if($compname == '') {
        $errmsg = 'Company name parameter not passed.';
    } else if($startdate == '') {
        $errmsg = 'From date parameter not passed.';
    } else if($enddate == '') {
        $errmsg = 'To date parameter not passed.';
    } else if(!validateName($compname)) {
        $errmsg = validationMsg("Company");
    } else {
        $errmsg = '';
    }
    return $errmsg;
}

function PROV_InstCountValidation($startdate, $enddate) {
    $errmsg = '';
    if($startdate == '') {
        $errmsg = 'From date parameter value not passed.';
    } else if($enddate == '') {
        $errmsg = 'To date parameter value not passed.';
    } else {
        $errmsg = '';
    }
    return $errmsg;
}
