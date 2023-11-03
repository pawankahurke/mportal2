<?php






function DEPL_GetLeftGridData($key, $db, $searchType, $site, $host)
{
    $res = [];
    $key = DASH_ValidateKey($key);
    if ($key) {

        if ($searchType == "ServiceTag") {
            $where = $site;
        } else if ($searchType == "Sites") {
            $where = $site;
        }


        $sql = $db->prepare("SELECT id,subnetmask,max(lastscan) as lastscan FROM " . $GLOBALS['PREFIX'] . "swupdate.Deployment where site=? group by subnetmask");
        $sql->execute([$where]);
        $res = $sql->fetchAll();

        if (safe_count($res) > 0) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}



function DEPL_AddSubnetId($key, $db, $subnetip, $site, $t2)
{
    $key = DASH_ValidateKey($key);
    if ($key) {

        $ipSplit = explode(".", $subnetip);
        $subIp = $ipSplit[0] . '.' . $ipSplit[1] . '.' . $ipSplit[2] . '.XXX';

        $sql = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "swupdate.Deployment (ipaddress,lastscan,subnetmask,macaddress,host,isclientavl,clientversion,site,deploystat) VALUES (?,?,?,?,?,?,?,?,?)");
        $sql->execute(['', 'never', $subIp, '', '', '', '', $site, 0]);
        $res = $db->lastInsertId();
    } else {
        echo "Your key has been expired";
    }
    return $val;
}



function DEPL_GetRightGridData($key, $db, $subnetmask, $site, $host)
{
    $res = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $status = DEPL_CheckScanJob($key, $db, $site, $host, $subnetmask);
        if ($status == 'scan triggered') {
            return 'scan triggered';
        } else if ($status == 'scan initiated') {
            return 'scan initiated';
        } else if ($status == 'scan error') {
            return 'scan error';
        } else if ($status == 'no scan') {


            $sql = $db->prepare("SELECT max(lastscan) as lastscan FROM " . $GLOBALS['PREFIX'] . "swupdate.Deployment where site = ? and subnetmask = ? group by subnetmask");
            $sql->execute([$site, $subnetmask]);
            $res = $sql->fetch();


            $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "swupdate.Deployment where subnetmask = ? and macaddress != '' and site = ? and lastscan = ?");
            $sql->execute([$subnetmask, $site, $res['lastscan']]);
            $res = $sql->fetchAll();

            if (safe_count($res) > 0) {
                return $res;
            } else {
                return array();
            }
        }
    } else {
        echo "Your key has been expired";
    }
}



function DEPL_ValidateSubnetIp($key, $db, $subnetip, $site)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $ipSplit = explode(".", $subnetip);
        $ipComp = $ipSplit[0] . '.' . $ipSplit[1] . '.' . $ipSplit[2];


        $sql = $db->prepare("SELECT subnetmask FROM " . $GLOBALS['PREFIX'] . "swupdate.Deployment where subnetmask like ? and site = ?");
        $sql->execute(["%$ipComp%", $site]);
        $res = $sql->fetchAll();

        $count = safe_count($res);
        return $count;
    } else {
        echo "Your key has been expired";
    }
}

function DEPL_ValidateImpDetails($key, $username, $password, $domain, $site, $host, $db)
{
    $key = DASH_ValidateKey($key);
    if ($key) {

        $ipSplit = explode(".", $subnetip);
        $ipComp = $ipSplit[0] . '.' . $ipSplit[1] . '.' . $ipSplit[2];

        $sql = "SELECT subnetmask FROM " . $GLOBALS['PREFIX'] . "swupdate.Deployment where subnetmask like '%$ipComp%' and site = '$site'";

        $res = find_many($sql, $db);
        $count = safe_count($res);
        return $count;
    } else {
        echo "Your key has been expired";
    }
}



function DEPL_InsJsonData()
{

    $db = db_connect();

    $jsonData = 'JSON_STRING_HERE';
    $jsonDecode = safe_json_decode($jsonData);

    $jsonDecodeKeys = $jsonDecode->ScanResult;

    foreach ($jsonDecodeKeys as $jsonKey => $jsonValue) {
        $Host = $jsonKey;
        $IPAddress = $jsonValue->IPAddress;
        $IsNHClientAvailabl = $jsonValue->IsNHClientAvailabl;
        $ClientVersion = $jsonValue->ClientVersion;
        $MACAddress = $jsonValue->MACAddress;
        $SubnetMask = '';
        $LastScan = 'never';

        $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "swupdate.Deployment (subnetmask,ipaddress,host,isclientavl,clientversion,macaddress,lastscan) "
            . "VALUES ('$SubnetMask','$IPAddress','$Host','$IsNHClientAvailabl','$ClientVersion','$MACAddress','$LastScan')";

        $res = redcommand($sql, $db);

        return $res;
    }
}



function DEPL_GetExportDetails($key, $scopetype, $searchvalue, $submask, $db)
{
    $key = DASH_ValidateKey($key);
    if ($scopetype == 'Sites') {
        $scopetype = 'site';

        $sql1 = $db->prepare("SELECT max(lastscan) as lastscan FROM " . $GLOBALS['PREFIX'] . "swupdate.Deployment where site = ? and subnetmask = ? group by subnetmask");
        $sql1->execute([$searchvalue, $submask]);
        $res = $sql1->fetch();

        $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "swupdate.Deployment where subnetmask = ? and macaddress != '' and site = ? and lastscan = ?");
        $sql->execute([$submask, $searchvalue, $res['lastscan']]);
    } else {
        $parent = $_SESSION['rparentName'];

        $sql1 = $db->prepare("SELECT max(lastscan) as lastscan FROM " . $GLOBALS['PREFIX'] . "swupdate.Deployment where site = ? and subnetmask = ? group by subnetmask");
        $sql1->execute([$parent, $submask]);
        $res = $sql1->fetch();

        $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "swupdate.Deployment where subnetmask = ? and macaddress != '' and site = ? and lastscan = ?");
        $sql->execute([$submask, $parent, $res['lastscan']]);
    }
    if ($key) {
        $data = $sql->fetchAll();
        Export_DeployData($data);
    } else {
        echo "Your key has been expired";
    }
}

function DEPL_CheckScanJob($key, $db, $site, $host, $submask)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $arr = [0, 1, 4];
        $in = str_repeat('?,', safe_count($arr) - 1) . '?';

        if ($host === "") {
            $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "swupdate.DeploymentQueue WHERE site = ? AND subnetmask LIKE ? and status in ($in)";
            $stm = $db->prepare($sql);
            $params = array_merge([$site, "%$submask%"], $arr);
            $stm->execute($params);
            $res = $stm->fetchAll();
        } else {
            $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "swupdate.DeploymentQueue WHERE host = ? AND site = ? AND subnetmask LIKE ? and status in ($in)";
            $stm = $db->prepare($sql);
            $params = array_merge([$host, $site, "%$submask%"], $arr);
            $stm->execute($params);
            $res = $stm->fetchAll();
        }
        if (safe_count($res) > 0) {
            if ($res['status'] == "0" || $res['status'] == 0) {
                return "scan triggered";
            } else if ($res['status'] == "1" || $res['status'] == 1) {
                return "scan initiated";
            } else if ($res['status'] == "4" || $res['status'] == 4) {
                return "scan error";
            }
        } else {
            return "no scan";
        }
    } else {
        echo "Your key has been expired";
    }
}

function DEPL_InsertScanJob($key, $db, $site, $host, $submask)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $now = time();

        $sql = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "swupdate.DeploymentQueue (subnetmask,host,site,status,message,scantime)
                VALUES (?,?,?,?,?,?)");
        $sql->execute([$submask, $host, $site, 0, '', $now]);
        $res = $db->lastInsertId();

        if ($res) {
            $resp = "scan triggered";
        } else {
            $resp = "scan triggered failed";
        }
        return $resp;
    } else {
        echo "Your key has been expired";
    }
}

function DEPL_UpdateScanJob($key, $db, $site, $host, $submask)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = $db->prepare("update " . $GLOBALS['PREFIX'] . "swupdate.DeploymentQueue set status = 4 where host = ? and site = ? and status = 1");
        $sql->execute([$mach, $site]);
        $res = $db->lastInsertId();
    } else {
        echo "Your key has been expired";
    }
}

function DEPL_UpdateDeployJob($key, $db, $site, $ip, $submask)
{
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = $db->prepare("select max(lastscan) as last from " . $GLOBALS['PREFIX'] . "swupdate.Deployment where subnetmask = ? and site = ? group by subnetmask");
        $sql->execute([$submask, $site]);
        $res = $sql->fetch();


        $arr = explode(",", $ip);
        $in = str_repeat('?,', safe_count($arr) - 1) . '?';
        $sql = "update " . $GLOBALS['PREFIX'] . "swupdate.Deployment set deploystat = 1 where ipaddress in ($in) and site = ? and subnetmask = ? and lastscan = ?";
        $stm = $db->prepare($sql);
        $params = array_merge($arr, [$site, $submask, $res['last']]);
        $stm->execute($params);
        $res = $db->lastInsertId();
    } else {
        echo "Your key has been expired";
    }
}

function DEPL_CheckImpersonation($key, $site, $host, $db)
{
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = $db->prepare("select username,password from " . $GLOBALS['PREFIX'] . "swupdate.DeployCreds where site = ? and machine = ? limit 1");
        $sql->execute([$site, $host]);
        $res = $sql->fetch();

        if (safe_count($res) > 0) {
            return 'impersonation exist';
        } else {
            return 'no impersonation';
        }
    } else {
        echo "Your key has been expired";
    }
}

function DEPL_AddImpersonation($key, $username, $password, $domain, $site, $host, $db)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $encodedpass = base64_encode($password);
        if (!empty($username) && (!empty($password)) && (!empty($domain))) {

            $sql = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "swupdate.DeployCreds (site,machine,username,password,domain)values (?,?,?,?,?)");
            $sql->execute([$site, $host, $username, $encodedpass, $domain]);
            $res = $db->lastInsertId();
        }
        if ($res) {
            return "success";
        } else {
            return "failed";
        }
    } else {
        echo "Your key has been expired";
    }
}

function DEPL_UpdateImpersonationCreds($key, $site, $host, $db, $username, $password, $domain)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $encodedpass = base64_encode($password);
        $impdetails = DEPL_GetImpersonationCreds($key, $site, $host, $db);
        if (($impdetails['username'] == '') || ($impdetails['password'] == '') || ($impdetails['domain'] == '')) {

            $sql = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "swupdate.DeployCreds (site,machine,username,password,domain)"
                . " values (?,?,?,?,?)");
            $sql->execute([$site, $host, $username, $encodedpass, $domain]);
        } else if (($impdetails['username'] != '') && ($impdetails['password'] != '') && ($impdetails['domain'] != '')) {
            $sql = $db->prepare("update " . $GLOBALS['PREFIX'] . "swupdate.DeployCreds set username=?,password=?,domain=? where site=? and machine=?");
            $sql->execute([$username, $encodedpass, $domain, $site, $host]);
        }
        $res = $sql->rowCount();
        if ($res) {
            return "success";
        } else {
            return "failed";
        }
    } else {
        echo "Your key has been expired";
    }
}

function DEPL_GetImpersonationCreds($key, $site, $host, $db)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = $db->prepare("select username,password,domain from " . $GLOBALS['PREFIX'] . "swupdate.DeployCreds where site = ? and machine = ? limit 1");
        $sql->execute([$site, $host]);
        $res = $sql->fetch();


        if (safe_count($res) > 0) {
            $return['username'] = $res['username'];
            $return['password'] = base64_decode($res['password']);
            $return['domain'] = $res['domain'];
        } else {
            $return['username'] = '';
            $return['password'] = '';
            $return['domain'] = '';
        }
        return $return;
    } else {
        echo "Your key has been expired";
    }
}

function DEPL_CheckResetStatus($key, $db, $subnetMask, $site)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = $db->prepare("SELECT scantime FROM " . $GLOBALS['PREFIX'] . "swupdate.DeploymentQueue WHERE site = ? AND subnetmask = ? LIMIT 1");
        $sql->execute([$site, $subnetMask]);
        $res = $sql->fetch();

        if (safe_count($res) > 0) {
            $now = time();
            $scanTime = $res['scantime'];
            $mins = ((int) $now - (int) $scanTime) / 60;
            if ((int) $mins <= 15) {
                return floor(15 - (int) $mins);
            } else {
                return 0;
            }
        }
    } else {
        echo "Your key has been expired";
    }
}

function DEPL_ResetDeployScan($key, $db, $subnetMask, $site)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "swupdate.DeploymentQueue WHERE site = ? AND subnetmask = ?");
        $sql->execute([$site, $subnetMask]);
        $res = $db->lastInsertId();

        if ($res) {
            return "success";
        } else {
            return "failed";
        }
    } else {
        echo "Your key has been expired";
    }
}

function DEPL_CheckRightGridData($key, $db, $subnetmask, $site, $host)
{
    $res = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $status = DEPL_CheckScanJob($key, $db, $site, $host, $subnetmask);


        if ($status == 'scan triggered') {
            return 'scan triggered';
        } else if ($status == 'scan initiated') {
            return 'scan initiated';
        } else if ($status == 'scan error') {
            return 'scan error';
        } else if ($status == 'no scan') {
            $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "swupdate.DeploymentQueue where subnetmask = ? and site = ?");
            $sql->execute([$subnetmask, $site]);
            $res = $sql->fetchAll();

            if (safe_count($res) > 0) {
                return $res;
            } else {
                return array();
            }
        }
    } else {
        echo "Your key has been expired";
    }
}

function DEPL_GetSiteNameUsingCensusId($db, $censusId)
{

    $sql = $db->prepare("SELECT id, site, host FROM " . $GLOBALS['PREFIX'] . "core.Census WHERE id = ? LIMIT 1");
    $sql->execute([$censusId]);
    $res = $db->lastInsertId();

    if (safe_count($res) > 0) {
        return $res['site'];
    } else {
        return '';
    }
}

function DEPL_DeployAudit($db, $key, $searchValue)
{
    $res = [];
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = $db->prepare("select idx,id,customer as site,machine,FROM_UNIXTIME(servertime,'%a %b %d %Y  %H:%i:%s') as time,text1 from " . $GLOBALS['PREFIX'] . "swupdate.DeploymentAudit where customer = ?");
        $sql->execute([$searchValue]);
        $res = $sql->fetchAll();

        if (safe_count($res) > 0) {
            return $res;
        } else {
            return "No data available";
        }
    } else {
        echo "Your key has been expired";
    }
}

function DEPL_DeployAuditDetails($db, $key, $idx)
{
    $res = [];
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = $db->prepare("select id, text1, text2, text3, text4 from " . $GLOBALS['PREFIX'] . "swupdate.DeploymentAudit where idx = ? limit 1");
        $sql->execute([$idx]);
        $res = $sql->fetch();

        return $res;
    } else {
        echo "your key has been expired";
    }
}

function DEPL_DeployDetails($db, $key, $searchValue)
{
    $res = [];
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = $db->prepare("select customer as site, machine, FROM_UNIXTIME(servertime,'%a %b %d %Y  %H:%i:%s') as time, text1, text2, text3, text4 from " . $GLOBALS['PREFIX'] . "swupdate.DeploymentAudit where customer = ?");
        $sql->execute([$searchValue]);
        $res = $sql->fetchAll();


        if (safe_count($res) > 0) {
            return $res;
        } else {
            return "Data not avaiable in table";
        }
    } else {
        echo "Your key has been expired";
    }
}

function DEPL_DeployDeleteSubnet($db, $key,  $selectedMask, $selectedSite)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        $sql = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "swupdate.Deployment  WHERE subnetmask = ? AND site = ? ");
        $sql->execute([$selectedMask, $selectedSite]);
        $res = $db->lastInsertId();

        if (safe_count($res) > 0) {
            $msg = "Success";
        } else {
            $msg = "Failed";
        }
        return $msg;
    } else {
        echo "your key has been expired";
    }
}
