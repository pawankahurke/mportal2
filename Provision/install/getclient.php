<?php

/*
Revision history:

Date        Who     What
----        ---     ----
21-May-03   MMK     Original creation
05-Jun-03   NL      Text changes;
 5-May-03   NL      Removed constCookieEmailCode & constCookieEmailCode (already in l-cnst);
 5-May-03   NL      Use install db for authentication.
 5-May-03   NL      Change "emailcode" to "siteemailid".
16-Jun-03   MMK     Fixed Nina's change to work for cookie (it was referring to
                        an undefined constant).
16-Jun-03   NL      Change include line: '../lib/l-head.php' --> 'header.php'.
23-Jul-03   NL      Change footer to standard_html_footer().
31-Jul-03   MMK     Fixed a syntax error in adding a leading dot for cookie domains.
31-Jul-03   EWB     Uses install_login($db);
14-Aug-03   NL      Remove authentication.
 2-Sep-03   NL      Change header("Refresh:") to header("Location:").
08-Sep-03   MMK     Do not set installed flag in Siteemail table when downloading file,
                    log cookie data  because we have been having some transient problems
                    where the ALIST was not getting generated correctly.
24-Sep-03   NL      Add "install: " to error_log entries.
30-Sep-03   NL      Updates "numresponses" in Siteemails table.
10-Oct-06   WOH     Made changes for bugzilla #3657
19-Jun-07   AAM     Moved include of l-cnst.php.  Not sure why I did this.
06-Jun-08   WOH     Modified TrySendCookie() to use "_COOKIE" because previous global
                    variable was deprecated.
03-Oct-08   BTE     Bug 4828: Change customization feature of server.
25-Oct-08   AAM     Bug 4823: Fixed line termination; had gotten set to DOS.

*/

define('constUserID',              'InstUserID');
define('constURLDownload',         'DownloadURL');
define('constProxy',               'Proxy');
define('constExists',              'SiteExists');
define('constTestCookie',          'CookiesSupport');
define('constInstallCookie',       'Install');
define('constCookieRegCode',       'CustID');
/* cookie expiration is one week, in seconds */
define('constDefCookieExpires',    604800);

ob_start();

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include('../lib/l-cnst.php');
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-head.php');
include('../lib/l-errs.php');
include('header.php');
include('../lib/l-rpcs.php');

logs::log("RPC_START", ["request" => $_REQUEST, "post" => $_POST,  "get" => $_GET]);

/*  GetSiteData
        Retrieves data from the site table row indexed by
        the registration code "regcode". The return value
        is an array that contains several elements:
        whether the site exists, install user id, download
        URL, and proxy URL.
*/
function GetSiteData($regcode, $db)
{
    $sql = "select * from " . $GLOBALS['PREFIX'] . "install.Sites where regcode = '$regcode'";
    $siteRaw = command($sql, $db);
    $result = array();
    $result[constExists] = 0;

    if (mysqli_num_rows($siteRaw) > 0) {
        $result[constExists] = 1;
        $row = mysqli_fetch_array($siteRaw);

        /* get the install user ID from the regcode */
        $userID = $row['installuserid'];
        $result[constUserID] = $userID;
        $serverProtocol  = 'https://';
        $servername = $_SERVER['HTTP_HOST'];
        $downloadURL = $serverProtocol . $servername . '/Dashboard/Provision/download/download_helper.php';
        /* get the download url from the Sites table. If
                it's blank, use the Users table */
        $downloadURL = $row['urldownload'];
        if (strcmp($downloadURL, '') == 0) {
            $sql = "select urldownload from Users " .
                "where installuserid = '$userID'";
            $userURLRaw = command($sql, $db);
            $url = mysqli_fetch_array($userURLRaw);
            $downloadURL = $url['urldownload'];
        }
        $downloadURL = $serverProtocol . $servername . '/Dashboard/Provision/download/download_helper.php';
        $result[constURLDownload] = $downloadURL;
        /* get the proxy server */
        $result[constProxy] = $row['proxy'];
    }
    return $result;
}



/*  UpdateSiteEmail
        Sets the "response" and "install" columns of the
        row indexed by the email code "siteemailid" and
        install user ID "instUserID" to the current time.
*/
function UpdateSiteEmail($siteemailid, $instUserID, $db)
{
    $now = time();
    $sql = "update Siteemail set response = '$now', " .
        "numresponses = numresponses + 1 where " .
        "installuserid = '$instUserID' and " .
        "siteemailid = '$siteemailid'";
    command($sql, $db);
}




/*  TrySendCookie
        If the client browser supports cookies, then set a cookie
        that contains the Site ID, the email code, and the proxy
        URL, all encoded together into an ALIST. Otherwise, display
        the Site ID to the user.
*/
function TrySendCookie($regcode, $siteemailid, $proxy, $expTime)
{
    $cookiesWork = isset($GLOBALS['_COOKIE'][constTestCookie]);
    if ($cookiesWork) {
        $cookie = array();
        $cookie[constCookieCustID] =        $regcode;
        $cookie[constCookieSiteEmailID] =   $siteemailid;
        $cookie[constCookieProxy] =         $proxy;
        $cookieStr = fully_make_alist($cookie);
        $path = '/';
        $host = server_var('SERVER_NAME');
        /* to be compatible with some older browsers and the Netscape spec,
               if the server URL contains one dot, we add a leading dot */
        $numDots = substr_count($host, '.');
        if ($numDots < 2) {
            $servDot = array('.', $host);
            $servName = join('', $servDot);
        } else {
            $servName = $host;
        }
        $expire = time() + $expTime;
        setcookie(
            constInstallCookie,
            $cookieStr,
            $expire,
            $path,
            $servName
        );

        /* this is so that we get the constant inside the error log. eech. */
        $kludge = constInstallCookie;
        logs::log(__FILE__, __LINE__, "install: Set cookie for code '$regcode', email $siteemailid, " .
            "name: '$kludge', value: '$cookieStr' expire: '$expire', " .
            "path: '$path', server: '$servName'.");
    } else {
        /* Dump out error in php.log */
        logs::log(__FILE__, __LINE__, "Your browser doesn't support cookies.  registration code: '$regcode'");
        /* put more fancy code in here. Now we're just testing */
        echo "<br><br>";
        echo "<span class = 'blue'><b>registration code:</b></span> $regcode</b>";
        echo "<br><br>Your browser doesn't support cookies. You will need to manually ";
        echo "enter the registration code into the dialog box after installation.";
    }
}

/*
 *  CheckMaxInstall : Checks max install to verify installation process.
 *  @params siteemailid
 */

function CheckMaxInstall($siteemailid, $db)
{
    $doInstall = true;
    $sql = "select * from Siteemail where siteemailid = $siteemailid limit 1";
    $res = command($sql, $db);

    if (mysqli_num_rows($res) > 0) {
        $SEData = mysqli_fetch_array($res);
        $maxinstall = $SEData['maxinstall'];
        $numinstall = $SEData['numinstalls'];

        if ($maxinstall != 0) {
            if ($numinstall < $maxinstall) {
                $doInstall = true;
            } else {
                $doInstall = false;
            }
        }
    }
    return $doInstall;
}

/*  Redirect
        Forwards to download the file from  "downloadURL".
    Puts up a hyperlink to the URL for
        browsers that don't support the "Location" HTTP
        header.
*/
function Redirect($downloadURL, $regcode, $siteemailid, $db)
{
    // $downloadURL .= "index.php?rcode=".$regcode."&seid=" . $siteemailid;
    $downloadURL .= "?rcode=" . $regcode . "&seid=" . $siteemailid;
    /* always put the header first */
    header("Location: $downloadURL");
    echo ("<p>The file should start downloading. If it doesn't click here:<br>");
    $hyperlink = '<a href="' . $downloadURL . '">' . $downloadURL . '</a><br>';
    echo $hyperlink;
}


/*
    |  Main program
    */

$title = 'Downloading';

$siteemailid = url::toStringAz09(get_argument('siteemailid', 0, '0'));
$regcode = url::toStringAz09(get_argument('regcode', 0, '0'));

/* connect to core */
$db = db_connect();

/* get the cookie expiration time from core */
$exp = server_def('INST_COOKIE_EXP', constDefCookieExpires, $db);

/* switch to the install database */
db_change($GLOBALS['PREFIX'] . 'install', $db);

$comp = component_installed();

/* get privileges */
$priv_admin     = @($user['priv_admin'])  ? 1 : 0;
$priv_servers   = @($user['priv_servers']) ? 1 : 0;

/* output the headers */
echo install_html_header($title, $comp, '', $priv_admin, $priv_servers, $db);

/* see if the site given by the regcode exists */
$siteDLParams = GetSiteData($regcode, $db);
if ($siteDLParams[constExists] == 1) {
    /* site exists. Update the email table */
    UpdateSiteEmail($siteemailid, $siteDLParams[constUserID], $db);

    /* Check to proceed installation */
    $dwnldOrNot = CheckMaxInstall($siteemailid, $db);

    if ($dwnldOrNot) {
        /* set the cookie or tell the user the data. */
        TrySendCookie(
            $regcode,
            $siteemailid,
            $siteDLParams[constProxy],
            $exp
        );

        /* redirect the user to the download page */
        Redirect($siteDLParams[constURLDownload], $regcode, $siteemailid, $db);
    } else {
        echo '<p>No. of installation exceeded for the Reg Code# ' . $regcode . '</p>';
    }
} else {
    /* site doesn't exist */
    echo "<br><br>The requested site does not exist.";
}

/* Hardwired to pass in hfn for the user. */
$user = 'hfn';
echo head_standard_html_footer($user, $db);

/* output the data we've accumulated */
ob_end_flush();
