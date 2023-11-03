<?php

/*
Revision history:

Date        Who     What
----        ---     ----
28-May-03   MMK     Original creation
 5-May-03   NL      Detect browser, error message if not IE.
 5-May-03   NL      Change "emailcode" to "siteemailid".
16-Jun-03   NL      Change include line: '../lib/l-head.php' --> 'header.php'.
23-Jul-03   NL      Change footer to standard_html_footer().
31-Jul-03   EWB     install_login
14-Aug-03   NL      Remove authentication;  Validate siteemailid.
 2-Sep-03   NL      Change header("Refresh:") to header("Location:").
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
03-Oct-08   BTE     Bug 4828: Change customization feature of server.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-util.php');

function validate($siteemailid, $db)
{
    $good = 0;
    $sql = "SELECT * FROM Siteemail WHERE siteemailid = $siteemailid";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            $good = 1;
        }
    }
    return $good;
}


/*
    |  Main program
    */

$title = 'Downloading';

define('constTestCookie', 'CookiesSupport');

$agent    = server_var('HTTP_USER_AGENT');
$IE = true; //strstr($agent,'MSIE') ? '1' : '0';

$siteemailid = get_argument('siteemailid', 0, '0');
$regcode     = get_argument('regcode', 0, '0');

$good = $IE && $siteemailid;

if ($good) {
    /* connect to install */
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'install', $db);

    $good = validate($siteemailid, $db);

    if ($good) {

        /* Set up the cookie */
        $host = server_var('HTTP_HOST');
        $self = server_var('PHP_SELF');
        $secure = true; //server_var('HTTPS');

        $path = '/';
        /* expire in five hours */
        $exp = time() + 18000;

        /* Set up the redirect for the final page */
        $nextPageURL = "https://" .
            $host . dirname($self) . "/getclient.php?" .
            "siteemailid=" . $siteemailid . "&regcode=" . $regcode;

        $msg = ob_get_contents();           // save the buffered output so we can...
        ob_end_clean();                     // (now dump the buffer)

        setcookie(constTestCookie, '1', $exp, $path, $host);
        header("Location: $nextPageURL");
    }
}


if (!$good) {
    include('../lib/l-serv.php');
    include('../lib/l-head.php');
    include('../lib/l-errs.php');
    include('../lib/l-cnst.php');
    include('header.php');

    /* connect to install */
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'install', $db);

    $comp = component_installed();

    /* get privileges */
    $priv_admin     = @($user['priv_admin'])  ? 1 : 0;
    $priv_servers   = @($user['priv_servers']) ? 1 : 0;

    /* output the headers */
    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer)
    echo install_html_header($title, $comp, '', $priv_admin, $priv_servers, $db);
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    echo "<br>\n";
    echo "<table><tr><td>\n";

    if (!$IE) {
        echo "This operation requires the Microsoft Internet Explorer browser.\n";
        echo "<br><br>You may download it at ";
        echo "<a href='http://www.microsoft.com/windows/ie/'>http://www.microsoft.com/windows/ie/</a>.\n";
    } else {
        echo "This URL is not valid for the requested operation.\n";
        echo "</td></tr></table>\n";
    }
    echo "</td></tr></table>\n";

    /* Hardwired to pass in hfn for the user. */
    $user = 'hfn';
    echo head_standard_html_footer($user, $db);
}
