<?php

/*
  Revision history:

  Date        Who      What
  ----        ---      ----
  14-May-03   MMK      Original creation.
  19-Jun-03   MMK      Removed client realm. Correctly handle the case of
  "Uninstall" being in the Startupnames table.
  19-Jun-03   EWB      Untabify.
  08-Sep-03   MMK      Fixed constant reference to email code inside the
  machine ID list passed from the client to make the
  installed flag in the Siteemail table work corectly.
  01-Oct-03   MMK      Increment the numinstalls field in the Siteemail
  table when client contacts the server with a valid
  site email ID.
  02-Oct-03   EWB      Changed "Install:" to "install:" in an error log.
  30-Oct-03   AAM      Fixed to use default ASI server when none is specified.
  31-Oct-03   EWB      Pass large objects by reference whenever feasible.
  10-Dec-03   EWB      Quote sitename when inserting new record.
  15-Jun-05   AAM      Put site name into both old (local/global) and new
  (local-only) variable on server.
  31-Oct-05   BTE      Added CONF_GetSiteInfo.
  08-Mar-06   BTE      Fixed CONF_GetSiteInfo to return the site name for clients
  checking in with a client-generated code.  This is part of
  bug 2966.
  09-Jun-06   AAM      Added IP address output, and fixed bug 3364.
  19-Jun-07   AAM      Changed to support new design for startup and followon
  options.
  04-Mar-19   JHN     Service Bot integration changes has been added.
  16-Oct-19   SVG     CustomerNo and TenantId value passing to client.
  18-Mar-21   ALX     SeviceBots Discarded
  18-Mar-21   ALX     SVBT_checkMaxInstallCountNew redone
 */

$ssl = (isset($_SERVER['HTTPS'])) ? 1 : 0;
$host = $_SERVER['HTTP_HOST'];
$http = ($ssl) ? 'https' : 'http';

/* Module constants.These should match what's on the client */
define('constInstallScrip', 223);
define('constClientToolsScrip', 43);
define('constCustomerInfoScrip', 253);
define('constNodeServerCommScrip', 266);
define('constNoScopeScrip', 0);
define('constFcmUrlScrip', 426);
define('constEmptyString', '');
define('constSchemeHTTP', 'http');
define('constSchemeHTTPS', 'https');
define('constPortHTTP', '80');
define('constPortHTTPS', '443');
define('constDefaultServer', "$http://$host:443/Provision/rpc/rpc.php");
define('constDefaultPath', '/Provision/rpc/rpc.php');
define('constFollowOnDelay', 'S00223FollowOnDelay');
define('constUninstall', 'S00223UninstallAction');
define('constInitialActiveScrips', 'S00223ActiveScrips');
define('constFollowOnScrips', 'S00223FollowOnScrips');
define('constServerUrl', 'S00266ServerURL');
define('constConfClientUser', 'ClientUser');
define('constConfConfigPassword', 'ConfigurationPassword');
define('constConfCustomerNameOld', 'CustName');
define('constConfCustomerName', 'CustNameA');
define('constConfCustDomain', 'CustDomain');
define('constConfProxyURL', 'ProxyServer');
define('constConfLoggingURL', 'LogServer');
define('constConfSupportEMail', 'Email_support');
define('constConfAdminEMail', 'Email_admin');
define('constConfEngineeringEMail', 'Email_engineering');
define('constConfSendEmail', 'SendEmail');
define('constConfEmailErrs', 'EmailErrAddr');
define('constConfHPCPassword', 'HPCPassword');
define('constAllScripsOption', 'All');
define('constNoneScripsOption', 'None');
define('constUninstScripsOption', 'Uninstall');
define('constStreamingURL', 'StrmngURL');
define('constDeployPath32', 'DeployPath32');
define('constDeployPath64', 'DeployPath64');
define('constS426FcmUrl', 'S426FcmUrl');


/*  PackConfig
  Extracts the value named "name" from the table "table"
  and packages it in a variable array. The returned
  value is the packaged array. "rev" is the revision
  level adjustment for this connection. See the comment
  in ComputeRevisionAdjustment for an explanation.
 */

function PackConfig($name, $varType, $table, $rev)
{
    $value = $table[$name];
    $ret = PackVar($varType, $value, $rev);
    return $ret;
}
/*
 * function get user ip
 */
function getUserIp()
{
    $keys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR'
    ];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(end(explode(',', $_SERVER[$key])));
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
}

/*  GetScrips
  Gets all the scrips from the Startupscrips table that
  have the installuser matching "user" and option matching
  "optionID" from the database "db", and put them into
  comma-separated list string. "rev" is the revision
  level adjustment for this connection. See the comment
  in ComputeRevisionAdjustment for an explanation.
 */

function GetScrips($user, $optionID, $db, $rev, $packVar)
{
    /* get the option name from the ID */
    $sql = "select scrip from Startupscrips where " .
        "startupnameid = '$optionID'";
    $scrips = command($sql, $db);
    $scripStr = '';
    if ($scrips) {
        while ($row = mysqli_fetch_row($scrips)) {
            if ($scripStr != '') {
                $scripStr .= ',';
            }
            $scripStr .= $row[0];
        }
        mysqli_free_result($scrips);
    }
    return $scripStr;
}

/* GetStartupScrips
  Get the option for Scrip 43 for startup Scrips with startup type
  $startupType and startup ID $startupID, for the user $user.  Use the
  database connection $db.  $packVar indicates whether or not to
  call PackVar, and $rev is needed by PackVar.
 */

function GetStartupScrips($user, $startupType, $startupID, $db, $rev, $packVar)
{
    switch ($startupType) {
        case constStartupTypeAll:
            $scripStr = '*';
            break;
        case constStartupTypeNone:
            $scripStr = constEmptyString;
            break;
        case constStartupTypeList:
            $scripStr = GetScrips($user, $startupID, $db, $rev, $packVar);
            break;
        default:
            $scripStr = constEmptyString;
            logs::log(__FILE__, __LINE__, "install: bad startuptype $startupType for $user", 0);
            break;
    }

    if ($packVar == 0) {
        $ret = $scripStr;
    } else {
        $ret = PackVar(constVblTypeString, $scripStr, $rev);
    }
    return $ret;
}

/* GetFollowonScrips
  Get the option for Scrip 43 for followon Scrips with followon type
  $followonType and followon ID $followonID, for the user $user.  Use the
  database connection $db.  $packVar indicates whether or not to
  call PackVar, and $rev is needed by PackVar.
 */

function GetFollowonScrips($user, $followonType, $followonID, $db, $rev, $packVar)
{
    switch ($followonType) {
        case constFollowonTypeAll:
            $scripStr = '*';
            break;
        case constFollowonTypeNone:
        case constFollowonTypeUninstall:
            $scripStr = constEmptyString;
            break;
        case constFollowonTypeList:
            $scripStr = GetScrips($user, $followonID, $db, $rev, $packVar);
            break;
        default:
            $scripStr = constEmptyString;
            logs::log(__FILE__, __LINE__, "install: bad startuptype $startupType for $user", 0);
            break;
    }

    if ($packVar == 0) {
        $ret = $scripStr;
    } else {
        $ret = PackVar(constVblTypeString, $scripStr, $rev);
    }
    return $ret;
}

/*  PackVar
  Packages the global value and revision level into
  an array for a variable having a type "varType",
  and a value "value". "rev" is the revision level
  adjustment. See the comment in ComputeRevisionAdjustment
  for an explanation.
  Note that this is partially taken from publish.php
  so although it's a very small amount of code (now),
  we should think about how to factor this out.
 */

function PackVar($varType, $value, $rev)
{
    $desc = array();
    $desc[constVarPackageType] = $varType;
    settype($desc[constVarPackageType], 'integer');
    /* set the revision level high enough so that we override any
      settings the user may have been changing accidentally at the
      site. Also add the revision adjustment so that these settings
      override any others at the site. */
    $desc[constVarPackageGlobalRev] = 100 + $rev;
    $desc[constVarPackageGlobal] = $value;
    settype($desc[constVarPackageGlobalRev], 'integer');
    if ($varType == constVblTypeString) {
        settype($desc[constVarPackageGlobal], 'string');
    } else {
        settype($desc[constVarPackageGlobal], 'integer');
    }
    return $desc;
}

/*  PackVarLocal
  Just like PackVar except it packages the value as a LOCAL setting
  instead of a global one.
 */

function PackVarLocal($varType, $value, $rev)
{
    $desc = array();
    $desc[constVarPackageType] = $varType;
    settype($desc[constVarPackageType], 'integer');
    /* set the revision level high enough so that we override any
      settings the user may have been changing accidentally at the
      site. Also add the revision adjustment so that these settings
      override any others at the site. */
    $desc[constVarPackageLocalRev] = 100 + $rev;
    $desc[constVarPackageLocal] = $value;
    settype($desc[constVarPackageLocalRev], 'integer');
    if ($varType == constVblTypeString) {
        settype($desc[constVarPackageLocal], 'string');
    } else {
        settype($desc[constVarPackageLocal], 'integer');
    }
    return $desc;
}

/* PING UpdateCounters
  Sets the counters for this connection in the Sites table.
  If the firstcontact field is zero, it is set to "curTime".
  The lastcontact field is always set to "curTime". The
  numconnects field is incremented by 1.
 */

function UpdateCounters($siteTable, $db, $curTime, $instUser)
{
    $firstContact = $siteTable['firstcontact'];
    if ($firstContact == 0) {
        $firstContact = $curTime;
        // insert into Sites table [1]
        $sql = "update Sites set firstcontact = '$firstContact' " .
            "where installuserid = '$instUser'";
        command($sql, $db);
    }
    $numConnects = $siteTable['numconnects'] + 1;
    // insert into Sites table [1]
    $sql = "update Sites set lastcontact = '$curTime'," .
        "numConnects = '$numConnects' " .
        "where installuserid = '$instUser'";
    command($sql, $db);
    TokenChecker::calcSites($instUser, 'installuserid');
}

/*  ComputeRevisionAdjustment
  Calculate the revision level adjustment by summing the numconnects
  over all sites. This solves two problems. The first is that if a
  user changes the site ID dynamically, he can't get the data
  for the new xsite because the revision on the installation server
  will be smaller than or equal to the ones at the site.
  The second problem is that if the user changes something on the
  installation server, the changes won't propagate to the site because
  all the clients that have already talked to the server won't do so
  again, but those that haven't will get the new setting, and a big
  mess will result with some clients having settings A, and others
  having B.
  Now, every client that contacts the installation server will get
  a set of variables with a higher revision level than any client
  before it. As a result, there will be a lot of variable updates
  on the client site, but this is only until all the machines have
  the installation settings.
 */

function ComputeRevisionAdjustment($db)
{
    $sql = 'select sum(numconnects) from ' . $GLOBALS['PREFIX'] . 'install.Sites';
    $revAdj = command($sql, $db);
    if (mysqli_num_rows($revAdj) > 0) {
        $row = mysqli_fetch_row($revAdj);
        $rev = intval($row[0]);
    } else {
        logs::log(__FILE__, __LINE__, "Unable to compute revision adjustment!", 0);
    }
    return $rev;
}

/*  PackageSettings
  Creates a package of variables that are needed for a client to
  run correctly. "row" is a row in the sites table, "rev" is the
  revision adjustment factor (see the comment for
  ComputeRevisionAdjustment).
 */

function PackageSettings($row, $rev, $db)
{
    $retVars = array();
    $instUser = $row['installuserid'];

    /* get the variables for the installation scrip */
    $retVars[constInstallScrip][constFollowOnDelay] = PackConfig('delay', constVblTypeInteger, $row, $rev);
    $retVars[constInstallScrip][constUninstall] = PackConfig('uninstall', constVblTypeBoolean, $row, $rev);

    /* get a list of scrips and put them into a comma-separated list */
    $retVars[constInstallScrip][constInitialActiveScrips] = GetStartupScrips($instUser, $row['startuptype'], $row['startupid'], $db, $rev, 1);
    $retVars[constInstallScrip][constFollowOnScrips] = GetFollowonScrips($instUser, $row['followontype'], $row['followonid'], $db, $rev, 1);

    /* next, get all the variables for scrip 43 */
    $retVars[constClientToolsScrip][constConfClientUser] = PackConfig('username', constVblTypeString, $row, $rev);
    $retVars[constClientToolsScrip][constConfConfigPassword] = PackConfig('password', constVblTypeString, $row, $rev);
    /* Set the site name into both the old variable and the new variable,
      so that all clients work properly. */
    $siteName = $row['sitename'];
    $retVars[constClientToolsScrip][constConfCustomerNameOld] = PackVar(constVblTypeString, $siteName, $rev);
    $retVars[constClientToolsScrip][constConfCustomerName] = PackVarLocal(constVblTypeString, $siteName, $rev);
    /* HPC password is the hash of the site name */
    $retVars[constClientToolsScrip][constConfHPCPassword] = PackVar(constVblTypeString, md5($siteName), $rev);
    $retVars[constClientToolsScrip][constConfCustDomain] = PackConfig('domain', constVblTypeString, $row, $rev);
    $retVars[constClientToolsScrip][constConfProxyURL] = PackConfig('proxy', constVblTypeString, $row, $rev);

    /* get the email address. If it's blank then don't use email at all */
    $email = $row['email'];
    $useMail = strcmp($email, constEmptyString) != 0;
    if ($useMail) {
        $retVars[constClientToolsScrip][constConfSupportEMail] = PackVar(constVblTypeString, $email, $rev);
        $retVars[constClientToolsScrip][constConfEngineeringEMail] = PackVar(constVblTypeString, $email, $rev);
        $retVars[constClientToolsScrip][constConfAdminEMail] = PackVar(constVblTypeString, $email, $rev);
    }
    $retVars[constClientToolsScrip][constConfSendEmail] = PackVar(constVblTypeBoolean, $useMail, $rev);

    /* get the bounced-email address from the sites table. If it's blank, then use
      the one in the users table as per the specification */
    $bounceMailAddr = $row['emailbounce'];
    if (strcmp($bounceMailAddr, constEmptyString) == 0) {
        $sql = "select emailbounce from Users where installuserid ='$instUser'";
        $bounceQueryRes = command($sql, $db);
        if (mysqli_num_rows($bounceQueryRes) > 0) {
            $bounceArray = mysqli_fetch_array($bounceQueryRes);
            $bounceMailAddr = $bounceArray['emailbounce'];
        }
        mysqli_free_result($bounceQueryRes);
    }
    $retVars[constClientToolsScrip][constConfEmailErrs] = PackVar(constVblTypeString, $bounceMailAddr, $rev);

    $serverID = $row['serverid'];
    /* Get the logging server.  If the server ID is zero, it means that the
      customer isn't enabled for ASI servers, so we just use the default
      ASI server (ours). */
    $sql = "select * from Servers where serverid = '$serverID'";
    $serverEntry = command($sql, $db);
    if (mysqli_num_rows($serverEntry) > 0) {
        /* The client will want a full URL, so try our best to put one together */
        $server = mysqli_fetch_array($serverEntry);
        $fullServerURL = GetFullURL($server['url']);
    } else {
        $fullServerURL = constDefaultServer;
    }

    $fullServerURL =   "https://" . preg_replace("#\.#im", "report.", getenv('DASHBOARD_SERVICE_HOST'), 1) . ":443/main/rpc/rpc.php";

    $retVars[constClientToolsScrip][constConfLoggingURL] = PackVar(constVblTypeString, $fullServerURL, $rev);
    mysqli_free_result($serverEntry);

    return $retVars;
}

/*  GetFullURL
  Creates a full URL from a partial URL. The only thing required in the
  partial input is a host name (e.g. "nanoheal.org"). Everything else
  defaults to standard HTTP conventions: http scheme, port 80, default
  path used for RPC, no user or password.
 */

function GetFullURL($partialURL)
{
    /* break up the input into its components */
    $serverURLComp = parse_url($partialURL);
    if (empty($serverURLComp['scheme'])) {
        $scheme = constSchemeHTTP;
        /* if the scheme isn't present, parse_url starts to behave
          in strange ways, like interpreting the host as the path,
          etc.. so add the scheme string and re-parse it. It's the
          least kludgey solution to this problem. The real fix is
          to rewrite parse_url but I don't have time to test it. */
        $url2 = $scheme . "://" . $partialURL;
        $serverURLComp = parse_url($url2);
    } else {
        $scheme = $serverURLComp['scheme'];
    }
    if (empty($serverURLComp['port'])) {
        /* if the scheme is HTTPS expand the port to 443 */
        if (strcasecmp($scheme, constSchemeHTTPS) == 0) {
            $port = constPortHTTPS;
        } else {
            $port = constPortHTTP;
        }
    } else {
        $port = $serverURLComp['port'];
    }
    $host = $serverURLComp['host'];
    if (empty($serverURLComp['path'])) {
        $path = constDefaultPath;
    } else {
        $path = $serverURLComp['path'];
    }
    if (empty($serverURLComp['user'])) {
        $userPassStr = constEmptyString;
    } else {
        $userPassStr = $serverURLComp['user'] . ':' .
            $serverURLComp['pass'] . '@';
    }
    $fullServerURL = $scheme . '://' . $userPassStr .
        $host . ':' . $port . $path;
    return $fullServerURL;
}

/* UpdateSiteContact
  Updates the Siteemail table row indexed by the install user id
  "instUser" and email code "emailCode" to have an "installed"
  field equal to the time in "curTime".
 */

function UpdateSiteContact($curTime, $db, $instUser, $emailCode)
{
    $sql = "update Siteemail set installed = '$curTime', " .
        "numinstalls = numinstalls + 1 where " .
        "installuserid = '$instUser' and " .
        "siteemailid = '$emailCode'";
    command($sql, $db);
}

function IsRegCodeClientGen($siteID)
{
    /* take the first 9 digits and convert them to a number */
    $siteIDNumberStr = substr($siteID, 0, 9);
    $siteIDNumber = intval($siteIDNumberStr);

    /* now just look at the last bit */
    $lastBit = $siteIDNumber & 1;

    return $lastBit;
}

function CreateSiteRecord($siteID, $siteName, $db)
{
    /* check if the site exists, if not then create it */
    $sql = "select * from " . $GLOBALS['PREFIX'] . "install.Sites where regcode = '$siteID'";
    $res = command($sql, $db);
    if (mysqli_num_rows($res) == 0) {
        /* insert a new entry into the Users table, then get
          the new installuserid to use in the Sites entry */
        $sql = "insert into Users set installuser = '$siteID'";
        command($sql, $db);
        $sql = "select * from Users where installuser = '$siteID'";
        $userRes = command($sql, $db);
        $userRow = mysqli_fetch_array($userRes);
        $instUserID = $userRow['installuserid'];

        /* insert a new entry into the Sites table */
        $qs = safe_addslashes($siteName);
        // insert into Sites table [1]
        $sql = "insert into Sites set sitename = '$qs'," .
            "installuserid = '$instUserID'," .
            "regcode = '$siteID'";
        command($sql, $db);
        TokenChecker::calcSites($qs, 'sitename');
    }
}

/*  CONF_GetClientSettings
  Downloads the configuration settings for the client.
  There is one incoming argument, an ALIST that (right now)
  contains two things: a site ID and an email code.
 */

function CONF_GetClientSettings(&$args)
{
    $type = $args['type'];
    $idnt = fully_parse_alist(urldecode($args['valu'][2]));

    $siteID = $idnt[constConfListCustID];
    $siteName = $idnt[constConfListSiteName];
    //    $ipAddr = server_var('REMOTE_ADDR');
    $ipAddr = getUserIp();

    $rval = constErrDatabaseNotAvailable;
    $curTime = time();

    $db = db_code('db_ins');
    if ($db) {
        $rval = constErrSiteNotFound;

        $isClientCode = IsRegCodeClientGen($siteID);
        if ($isClientCode == 1) {
            /* registration code was generated by the  client, so create a blank
              site for record purposes, and return an empty package. */
            $retList = array();

            logs::log(__FILE__, __LINE__, "install: machine checking in with client-generated code " .
                "'$siteID', and site name '$siteName' " .
                "from '$ipAddr'", 0);

            CreateSiteRecord($siteID, $siteName, $db);
            $rval = constAppNoErr;
        } else {
            /* first get the site entry from the site ID */
            $sql = "select * from " . $GLOBALS['PREFIX'] . "install.Sites where regcode = '$siteID'";

            $thisSite = command($sql, $db);
            if (mysqli_num_rows($thisSite) > 0) {
                logs::log(__FILE__, __LINE__, "install: machine checking in with server code " .
                    "'$siteID', and site name '$siteName' " .
                    "from '$ipAddr'", 0);

                $rval = constAppNoErr;
                $row = mysqli_fetch_array($thisSite);

                /* Calculate the revision. See the comment for an explanation */
                $rev = ComputeRevisionAdjustment($db);

                /* Package all the variables */
                $retVars = PackageSettings($row, $rev, $db);

                $instUser = $row['installuserid'];

                /* Update the counters: lastContact, firstContact, numConnects */
                UpdateCounters($row, $db, $curTime, $instUser);

                if (isset($idnt[constConfListEmailCode])) {
                    $emailCode = $idnt[constConfListEmailCode];
                    /* Update the Siteemail table */
                    UpdateSiteContact($curTime, $db, $instUser, $emailCode);
                }

                /* Set the ALIST array */
                $retList = array();
                $retList[constVarPackageVars] = $retVars;
            } else {
                logs::log(__FILE__, __LINE__, "install: machine checking in with NON-EXISTENT server code " .
                    "'$siteID', and site name '$siteName' " .
                    "from '$ipAddr'", 0);
            }
            mysqli_free_result($thisSite);
        }
    }

    if ($rval == constAppNoErr) {
        $args['valu'][1] = fully_make_alist($retList);
    }

    /* Don't bother returning the incoming PALISTs (slight traffic optimization). */
    $args['valu'][2] = '';

    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = $rval;

    // $args returned to caller by reference
}

/*  CONF_GetSiteInfo(MACHINE mID, PPALIST siteInfo, PALIST machineInfo)

  Accepts a site ID and an email code in "machineInfo" and returns the
  site name in "siteInfo".  "machineInfo" is a named ALIST with the following
  named items (constants defined on the server):
  constConfListCustID         PSTRING     site ID code
  constConfListEmailCode      PSTRING     email code (may not be present)
  constConfListSiteName       PSTRING     client's site name if the
  client generated the site code
  (constants defined on the client):
  constInstallCustID          PSTRING     site ID code
  constInstallMachineID       PSTRING     email code (may not be present)
  constInstallSiteName        PSTRING     client's site name if the
  client generated the site code

  Note: there are only three named items, at most, in "machineInfo".  The
  reason there are 6 listed is to define the constants used between the
  client and the server, whose names are different but whose values are not.

  If the site code is valid, returns "siteInfo" with the following named
  items:
  223 (named ALIST)
  constFollowOnDelay          UINT32      follow on delay
  constUninstall              UINT32      uninstall, treat as BOOLEAN
  constInitialActiveScrips    PSTRING     initial active Scrips
  constFollowOnScrips         PSTRING     follow-on Scrips

  43 (named ALIST)
  constConfClientUser         PSTRING     user for config interface
  constConfConfigPassword     PSTRING     password for interface
  constConfCustomerNameOld    PSTRING     old site name
  constConfCustomerName       PSTRING     new site name
  constConfCustDomain         PSTRING     customer domain
  constConfProxyURL           PSTRING     proxy URL
  constConfSupportEMail       PSTRING     support e-mail account*
  constConfEngineeringEMail   PSTRING     engineer e-mail*
  constConfAdminEMail         PSTRING     admin e-mail*
  constConfSendEmail          UINT32      treat as BOOLEAN
  constConfEmailErrs          PSTRING     email errors
  constConfLoggingURL         PSTRING     ASI LOG server

 * Items marked by a * will only be present if constConfListEmailCode/
  constInstallMachineID exists and is not an empty string.
 */

function CONF_GetSiteInfo(&$args)
{
    $retVars = array();
    // $type = $args['type'];
    $idnt = fully_parse_alist(urldecode($args['valu'][2]));

    // $date = date('d_m_Y', time());
    // $fp = ''; //fopen('logs/infolog_' . $date . '.txt', 'a+');
    logs::log('CONF_GetSiteInfo:: ARG List : ' . json_encode($args) . PHP_EOL);
    logs::log('CONF_GetSiteInfo:: fully_parse_alist : ' . json_encode($idnt) . PHP_EOL);

    $siteID = $idnt[constConfListCustID];

    $siteEmailId = $idnt[constConfListEmailCode];
    // If $idnt[constConfListSiteName] is empty, then the server is hosted by NH. Else it is self hosted
    $cSiteName = $idnt[constConfListSiteName];

    $machineOS = $idnt[constConfListOsName];
    $serviceTag = $idnt[constConfListHostName];   // Host Name
    $macAddress = $idnt[constConfListUniqueValue];  // Unique Value

    $ipAddr = getUserIp();

    $rval = constErrDatabaseNotAvailable;
    $curTime = time();

    $db = db_code('db_ins');
    if ($db) {
        $customerid = getCustomerID($cSiteName, $db);
        // = $idnt[customerid];
        $rval = constErrSiteNotFound;
        // if $siteID first nine digits is odd then registration code was generated by the  client.
        //NOT TESTED For ODD
        //    $isClientCode = IsRegCodeClientGen($siteID);
        //    if ($isClientCode == 1) {
        logs::log('isClientCode : TRUE' . PHP_EOL);
        /* registration code was generated by the  client, so create a blank
              site for record purposes, and return an empty package. */

        $siteName = @$idnt[constConfListSiteName];

        if (strcmp($siteName, "") == 0) {
            /* The client doesn't know the site name, so return it if
                  we have it. */
            $sql = "select sitename from " . $GLOBALS['PREFIX'] . "install.Sites where regcode = " . "'$siteID'";
            $thisSite = command($sql, $db);
            if (mysqli_num_rows($thisSite) > 0) {
                $row = mysqli_fetch_array($thisSite);
                $siteName = $row['sitename'];
                $retVars[constClientToolsScrip][constConfCustomerNameOld] = $siteName;
                $retVars[constClientToolsScrip][constConfCustomerName] = $siteName;
            }
            mysqli_free_result($thisSite);
        }

        logs::log(__FILE__, __LINE__, "install: machine checking in with "
            . "client-generated code '$siteID', and site name "
            . "'$siteName' "
            . "from '$ipAddr'", 0);

        CreateSiteRecord($siteID, $siteName, $db);
        $rval = constAppNoErr;
        //    } else {
        /// CHECK LICENCE COUNT AND VLIDATE AGAINST MAXINSTALL.
        ///RETURNS OK OR EXCEEDED
        $installValidCheck = SVBT_checkMaxInstallCountNew(
            $siteID,
            $siteEmailId,
            $cSiteName,
            $machineOS,
            $serviceTag,
            $macAddress,
            $db
        );


        if ($installValidCheck == 'OK') {
            // CHECK FIRST INSTALL AND INCREMENT. IF NOT NO INCREMENT
            // RETURNS SUBSEQUENT OR FIRST
            // INCREMENT OR NO INCREMENT DONE BY THIS FUNCTION
            // I OR R ALSO DONE BY THIS FUNCTION
            $checkFirstInstall = SVBT_checkFirstInstall(
                $siteID,
                $customerid,
                $siteEmailId,
                $cSiteName,
                $machineOS,
                $serviceTag,
                $macAddress,
                $db
            );
        } elseif ($installValidCheck == 'EXCEEDED') {
            $rval = constLicenseExceeded;
        }
        logs::log('isClientCode : FALSE' . PHP_EOL);
        $siteName = @$idnt[constConfListSiteName];
        /* first get the site entry from the site ID */
        $sql = "select * from " . $GLOBALS['PREFIX'] . "install.Sites where regcode = '$siteID'";
        logs::log('Site Query:: ' . $sql . PHP_EOL);

        $thisSite = command($sql, $db);
        if (mysqli_num_rows($thisSite) > 0) {
            logs::log(__FILE__, __LINE__, "install: machine checking in with server code '$siteID', and site name '$siteName' from '$ipAddr'", 0);

            $rval = constAppNoErr;
            $row = mysqli_fetch_array($thisSite, MYSQLI_ASSOC);
            $retVars = array();
            $instUser = $row['installuserid'];
            // $tenatid = $instUser;
            $nodeurl = $row['wsurl'];
            /* get the variables for the installation scrip */
            $delayMins = (int) $row['delay'];
            $delayOn = (int) $row['delayon'];
            if ($delayOn == 1) {
                $sqlse = "select * from Siteemail where installuserid =  '$instUser' and siteemailid = '$siteEmailId'";
                logs::log('Siteemail Query:: ' . $sqlse . PHP_EOL);
                $resse = command($sqlse, $db);
                $rowse = mysqli_fetch_array($resse);

                $createdTime = $rowse['createdtime'];
                $elapsedTime = floor((time() - $createdTime) / 60);
                $provExpTime = $delayMins - $elapsedTime;
                $delayMins = floor($provExpTime);
            }
            logs::log('Delay Mins:: ' . $delayMins . '----' . (int) $row['delay'] . PHP_EOL);
            $retVars[constInstallScrip][constFollowOnDelay] = (int) $delayMins;
            //(integer)$row['delay'];
            $retVars[constInstallScrip][constUninstall] = ($row['followontype'] == constFollowonTypeUninstall) ? 1 : 0;

            /* get a list of scrips and put them into a comma-separated
                  list */
            $retVars[constInstallScrip][constInitialActiveScrips] = '*';
            /* GetStartupScrips($instUser, $row['startuptype'],
                  $row['startupid'], $db, 0, 0); */
            $retVars[constInstallScrip][constFollowOnScrips] = '*';
            /* GetFollowonScrips($instUser, $row['followontype'],
                  $row['followonid'], $db, 0, 0); */

            /* next, get all the variables for scrip 43 */
            $retVars[constClientToolsScrip][constConfClientUser] = $row['username'];
            $retVars[constClientToolsScrip][constConfConfigPassword] = $row['password'];

            /* Set the site name into both the old variable and the new
                  variable, so that all clients work properly. */
            $siteName = $row['sitename'];
            $retVars[constClientToolsScrip][constConfCustomerNameOld] = $siteName;
            $retVars[constClientToolsScrip][constConfCustomerName] = $siteName;
            $retVars[constClientToolsScrip][constConfCustDomain] = $row['domain'];
            $retVars[constClientToolsScrip][constConfProxyURL] = $row['proxy'];

            /* get the email address. If it's blank then don't use
                  email at all */
            $email = $row['email'];
            $useMail = strcmp($email, constEmptyString) != 0;
            if ($useMail) {
                $retVars[constClientToolsScrip][constConfSupportEMail] = $email;
                $retVars[constClientToolsScrip][constConfEngineeringEMail] = $email;
                $retVars[constClientToolsScrip][constConfAdminEMail] = $email;
            }
            $retVars[constClientToolsScrip][constConfSendEmail] = (int) $useMail;

            /* get the bounced-email address from the sites table. If
                  it's blank, then use the one in the users table as per
                  the specification */
            $bounceMailAddr = $row['emailbounce'];
            if (strcmp($bounceMailAddr, constEmptyString) == 0) {
                $sql = "select emailbounce,installuserid from Users where "
                    . "installuserid ='$instUser'";
                $bounceQueryRes = command($sql, $db);
                if (mysqli_num_rows($bounceQueryRes) > 0) {
                    $bounceArray = mysqli_fetch_array($bounceQueryRes);
                    $bounceMailAddr = $bounceArray['emailbounce'];
                    // $tenatid = $bounceArray['installuserid'];
                }
                mysqli_free_result($bounceQueryRes);
            }
            $retVars[constClientToolsScrip][constConfEmailErrs] = $bounceMailAddr;

            $serverID = $row['serverid'];
            /* Get the logging server.  If the server ID is zero, it
                  means that the customer isn't enabled for ASI servers,
                  so we just use the default ASI server (ours). */
            $sql = "select * from Servers where serverid =  '$serverID'";
            $serverEntry = command($sql, $db);
            if (mysqli_num_rows($serverEntry) > 0) {
                /* The client will want a full URL, so try our best to
                      put one together */
                $server = mysqli_fetch_array($serverEntry);
                $fullServerURL = GetFullURL($server['url']);
                $streamingURL = $server['streamingurl'];
            } else {
                $fullServerURL = constDefaultServer;
                $streamingURL = constEmptyString;
            }


            $fullServerURL =   "https://" . preg_replace("#\.#im", "report.", getenv('DASHBOARD_SERVICE_HOST'), 1) . ":443/main/rpc/rpc.php";

            $retVars[constClientToolsScrip][constConfLoggingURL] = $fullServerURL;



            $streamingURL = "https://" . getenv('DASHBOARD_SERVICE_HOST') . "/Dashboard";

            $retVars[constNoScopeScrip][constStreamingURL] = $streamingURL;
            mysqli_free_result($serverEntry);

            //$instUser = $row['installuserid'];

            /* Update the counters: lastContact, firstContact, numConnects */
            UpdateCounters($row, $db, $curTime, $instUser);

            /*Get CustomerName starts*/
            $customerid = '';
            $sql_custid = "select customerid from " . $GLOBALS['PREFIX'] . "install.Siteemail where installuserid = '$instUser' and siteemailid = '$siteEmailId' limit 1";
            $res_custID = command($sql_custid, $db);
            if (mysqli_num_rows($res_custID) > 0) {
                $cust_row = mysqli_fetch_array($res_custID);
                $customerid = $cust_row['customerid'];
            }
            /*Get CustomerName ends*/
            $retVars[constCustomerInfoScrip]['CustomerNo'] = (string) $customerid;
            $retVars[constCustomerInfoScrip]['TenantId'] = (string) $userid;
            $retVars[constCustomerInfoScrip]['OrderNo'] = (string) $subscrp_id;

            if ($cSiteName != '') {
                $siteEmailData = SVBT_getSiteEmailInformation($siteID, $siteName, $db);
                logs::log("Returns the subscription info" . PHP_EOL);
                $retVars[constCustomerInfoScrip]['CustomerNo'] = (string) $siteEmailData['customerid'];
                $retVars[constCustomerInfoScrip]['TenantId'] = (string) $siteEmailData['userid'];
                $retVars[constCustomerInfoScrip]['OrderNo'] = (string) $siteEmailData['subscriptionid'];
            }

            // Reporting server url
            // $sql = "select wsurl from Users where installuserid = '$instUser' limit 1";
            // $res = command($sql, $db);
            // if (mysqli_num_rows($res) > 0) {
            //     $user = mysqli_fetch_array($res);
            //     $nodeurl = $user['wsurl'];
            // }
            $retVars[constNodeServerCommScrip][constServerUrl] = "wss://" . getenv('DASHBOARD_wsurl') ?: $nodeurl;
            $retVars[constNoScopeScrip][constDeployPath32] = $row['deploypath32'];
            $retVars[constNoScopeScrip][constDeployPath64] = $row['deploypath64'];
            $retVars[constFcmUrlScrip][constS426FcmUrl] = ($row['fcmUrl'] != '') ? $row['fcmUrl'] : '';

            //fwrite($fp, '<-------------------------------------------->' . PHP_EOL);
            //   fclose($fp);
            /* New Code Added : End */
        } else {
            logs::log(__FILE__, __LINE__, "install: machine checking in with NON-EXISTENT server code " .
                "'$siteID', and site name '$siteName' " .
                "from '$ipAddr'", 0);
        }
        mysqli_free_result($thisSite);
        //    }
    }

    logs::log("CONF_GetSiteInfo: rval=$rval ", $retVars);

    //    print_r($retVars);
    if ($rval == constAppNoErr) {
        $args['valu'][1] = fully_make_alist($retVars);
    }

    /* Don't bother returning the incoming PALISTs (slight traffic optimization). */
    $args['valu'][2] = '';
    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = $rval;
    //   print_r($args);
    // $args returned to caller by reference
}
function getCustomerID($siteID, $db)
{
    $sql = "SELECT cid FROM Sites  where sitename = '$siteID'";
    $result = find_one($sql, $db);

    return $result['cid'];
}
/* CHeck if it's a re-install on the same device */
function checkMacAddress($serviceTag, $siteID, $macAddress, $db, $fp)
{
    //fwrite($fp, 'Service Request :: Inside checkMacAddress function' . PHP_EOL);
    $sql_sertag = "select count(id) from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest where siteRegCode = '$siteID' and serviceTag='" . $serviceTag . "' and macAddress = '$macAddress' order by id desc limit 1";
    //fwrite($fp, 'Service Request :: checkMacAddress Query 1 -> ' . $sql_sertag . PHP_EOL);
    $res_sertag = redcommand($sql_sertag, $db);
    if (mysqli_num_rows($res_sertag) > 0) {
        return 1;
    } else {
        return 0;
    }
}

// function verifyMacAddress($serviceTag, $macAddressVal, $siteID, $db, $fp)
// {
//     try {

//         $mystring = $macAddressVal;
//         $findme = ':';
//         $findme1 = '-';

//         $pos = strpos($mystring, $findme);
//         $pos1 = strpos($mystring, $findme1);
//         $wh = '';

//         if ($pos !== FALSE) {
//             $wh = "and macAddress like '%:%'";
//         }

//         if ($pos1 !== FALSE) {
//             $wh = "and macAddress like '%-%'";
//         }

//         $sql_sertag = "select macAddress, siteRegCode from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest where siteRegCode = '" . $siteID . "' and serviceTag='" . $serviceTag . "' $wh order by id desc limit 1";

//         $res_sertag = redcommand($sql_sertag, $db);
//         if (mysqli_num_rows($res_sertag) > 0) {
//             return $macAddressVal;
//         } else {
//             if ($pos !== FALSE) {
//                 return str_replace(":", "-", $macAddressVal);
//             }
//             if ($pos1 !== FALSE) {
//                 return str_replace("-", ":", $macAddressVal);
//             }
//         }
//     } catch (Exception $ex) {
//         logs::log(__FILE__, __LINE__, $ex, 0);
//     }
// }
