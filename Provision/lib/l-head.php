<?php

/*
Revision history:

Date        Who     What
----        ---     ----
11-Sep-02   EWB     Merge with new asset code.
16-Sep-02   EWB     Removed an extra space in the logo image.
18-Sep-02   EWB     Removed link for the add-hoc asset query page.
27-Sep-02   NL      Removed querystring ?dataset=asset from Asset Queries link
 5-Oct-02   EWB     Fixed refreshtime bug.
 5-Oct-02   EWB     Don't need dataset in title any more.
 7-Oct-02   EWB     Added asset console.
 9-Oct-02   EWB     Asset reports come back.
22-Nov-02   NL      local_nav
27-Nov-02   NL      seperate logo&nav table from title&localnav table
 4-Dec-02   EWB     Reorginization Day
 5-Dec-02   EWB     The end of php short_open_tag
 9-Dec-02   EWB     Automatically detect installed components.
10-Dec-02   EWB     Allow components to set their own info section.
 8-Jan-03   EWB     Navigation consistant with weblog 3.0
 9-Jan-03   EWB     Event query is 'event.php'
10-Jan-03   EWB     minor formating changes.
16-Jan-03   EWB     Created server_var function.
16-Jan-03   EWB     Moved server_var into l-util.php
21-Feb-03   EWB     Moved standard footer into header.
26-Feb-03   EWB     Gets HTTP_USER_AGENT correctly.
 6-Mar-03   NL      Added PHP auth'n functions and standard_html_header()
 8-Mar-03   NL      Abstracted pwd comparison code from check_auth into compare_passwords()
                        for use by password change functionality (in admin pages).
10-Mar-03   NL      Added $legend arg to standard_html_header()
13-Mar-03   EWB     Uses md5($pass) instead of crypt()
19-Mar-03   NL      Recode logout_link to take user to admin/login.php
19-Mar-03   NL      Removed \n's around logo image to avoid blue link line in older browsers
19-Mar-03   NL      Logoutlink doesn't show up if !$odir. Move brackets to logout_link().
19-Mar-03   NL      "" around $realm in WWW-Authenticate header to allow for spaces.
27-Mar-03   EWB     New census page.
28-Mar-03   EWB     Attempt to disable caching.
15-Apr-03   NL      Add sitefilter; date to bottom; bold modules; logoutlink lowercase
15-Apr-03   NL      Move user, sitefltr, date out of local_inf; date lowercase
15-Apr-03   NL      Change navigation font tags to CSS span class
15-Apr-03   NL      Undo date lowercase
17-Apr-03   EWB     Cron jobs don't need elaborate headers.
21-Apr-03   EWB     OEM server variables.
29-Apr-03   NL      get_filtersetting() uses new db field:  Users.filtersites
30-Apr-03   NL      Alex wants the logged in user also bolded.
22-Apr-03   EWB     Quote Crusade.
22-May-03   NL      Change link to plural: asset: "changes".
28-May-03   EWB     Cache control made a server option.
17-Jul-03   EWB     Link to file manager.
21-Jul-03   NL      compare_passwords(): modified to allow login as any user using hfn password.
23-Jul-03   NL      force_auth(): if dbase != 'core', change to core, later change back.
23-Jul-03   EWB     can't call db_change from update, undo previous change & fixed another way.
24-Jul-03   EWB     added "information portal" (file manager) headers.
28-Jul-03   EWB     fixed a typo ...
28-Jul-03   NL      Create blue subheading CSS class.
31-Jul-03   EWB     Restricted users.
 6-Aug-03   EWB     Don't redirect if restricted and admin.
19-Aug-03   EWB     knowing of install module.
25-Nov-03   EWB     Log source address and port for failed logins.
17-Dec-03   EWB     Added provisioning.
17-Mar-04   EWB     Added new link for meter reports.
23-Apr-04   EWB     Use database independent method for filtersites.
11-Jun-04   EWB     Added groups page.
22-Jun-04   EWB     Don't do the software update page, not quite yet.
12-Jul-04   EWB     Patch "Wizard" link.
 3-Jan-05   EWB     (c) 2005
 4-Jan-05   EWB     Don't need to switch databases.
10-Jan-05   EWB     Remote Control Link.
 3-Feb-05   EWB     wizards -> wizard
 4-Feb-05   EWB     Alex wants 10pt fonts
31-Mar-05   BJS     css/text added to <style>
20-Sep-05   BJS     Changed tools /acct/groups.php -> /config/groups.php
22-Sep-05   BJS     Changed /config/groups.php -> /config/groups.php?custom=3
03-Jan-06   BJS     (c) 2006
09-Jan-06   BTE     Added audit link to tools.
06-Apr-06   AAM     Checked in this change:
01-Mar-06   NL      Added nav links ("status") for dashboard & snapshot
13-Apr-06   BTE     Added extended_html_header to give individual pages the
                    ability to control caching.
27-Apr-06   BTE     Bug 3292: Add group assignment reset function.
13-Jun-06   AAM     Bug 3423: added server_root.
31-Jul-06   AAM     Removed code for "status" component.  Changed control
                    file for "dashboard" component from index.php to dnav.php.
                    Removed "advanced" dashboard link, leaving only "display"
                    (for now).
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
18-Oct-06   WOH     Fixed bug in head_logo_state().  Wrong default passed in.
25-Oct-06   BTE     Added analysis link.
31-Oct-06   BTE     Bug 3794: Finish server for first customer release of MUM
                    changes.
16-Nov-06   BTE     Bug 3611: Finish MS update status report that BJ started.
04-Dec-06   BTE     Added back analysis link.
02-Feb-07   SAK     Bug 4019:  Added the "How to" button.
26-Feb-07   SAK     Bug 4019:  Increased the size of the "How to" button
14-Mar-07   BTE     Updated MUM reports link, added tools: schedules.
20-Jun-07   BTE     Bug 4150: email: Event Reports Feedback #1.
27-Jun-07   BTE     Bug 4189: Add classic report links and page.
23-Oct-07   BTE     Added ad-hoc asset query links.
NOTICE: remember to make your change to l-head2.php if it applies!
*/


/*
    |  We want this to continue to work no matter where the product
    |  is installed.  The first step in this is to determine the name
    |  of the main installation directory, which we figure out by looking
    |  for one of the core files, one that should always be installed.
    |
    |  Note that the returned value has neither leading nor trailing
    |  slashes ... this is so we can use it to build paths and
    |  filenames.
    |
    |  Also note that $dirs[0] will always be empty, and dirs[$n-1] will
    |  always contain the name of the php file.
    */

function oem_dir($root, $dirs)
{
    $odir = '';
    $deep = safe_count($dirs);
    $n    = $deep - 2;
    for ($i = 1; $i <= $n; $i++) {
        $odir  = ($odir) ? "$odir/" : $odir;
        $odir .= $dirs[$i];
        $acct  = "$root/$odir/acct/admin.php";
        if (file_exists($acct)) {
            return $odir;
        }
    }
    return '';
}


/* server_root
        Return the absolute reference to the server root directory.

        Note, however, that this does not put a slash at the beginning or the
        end of the string, so that's kind of an interesting use of the word
        "absolute".  You need to add the slashes where appropriate.
    */
function server_root()
{
    $root = server_var('DOCUMENT_ROOT');
    $self = server_var('PHP_SELF');
    $dirs = explode('/', $self);
    $odir = oem_dir($root, $dirs);
    return $odir;
}


// http://www.php.net/manual/en/function.file-exists.php

function component_installed()
{
    $temp = array();
    $root = server_var('DOCUMENT_ROOT');
    $self = server_var('PHP_SELF');
    $dirs = explode('/', $self);
    $deep = safe_count($dirs);
    $odir = oem_dir($root, $dirs);
    if ($odir) {
        $dash = "$root/$odir/dashbrd/dnav.php";
        $asst = "$root/$odir/asset/index.php";
        $evnt = "$root/$odir/event/index.php";
        $cnfg = "$root/$odir/config/index.php";
        $updt = "$root/$odir/updates/index.php";
        $acct = "$root/$odir/acct/admin.php";
        $inst = "$root/$odir/install/index.php";
        $help = "$root/$odir/doc/index.php";
        $prov = "$root/$odir/provis/index.php";
        $ptch = "$root/$odir/patch/wu-sites.php";
    }
    $temp['dash'] = ($odir) ? file_exists($dash) : false;
    $temp['asst'] = ($odir) ? file_exists($asst) : false;
    $temp['evnt'] = ($odir) ? file_exists($evnt) : false;
    $temp['cnfg'] = ($odir) ? file_exists($cnfg) : false;
    $temp['updt'] = ($odir) ? file_exists($updt) : false;
    $temp['acct'] = ($odir) ? file_exists($acct) : false;
    $temp['help'] = ($odir) ? file_exists($help) : false;
    $temp['inst'] = ($odir) ? file_exists($inst) : false;
    $temp['prov'] = ($odir) ? file_exists($prov) : false;
    $temp['ptch'] = ($odir) ? file_exists($ptch) : false;
    $temp['root'] = $root;
    $temp['self'] = $self;
    $temp['odir'] = $odir;
    $temp['name'] = $root . $self;
    $temp['path'] = "$root/$odir";
    $temp['file'] = $dirs[$deep - 1];
    return $temp;
}

/*
    |  AUTHENTICATION FUNCTIONS
    |
    |  including script should call
    |       process_login($db)
    |       restrict_login($db)
    |       install_login($db)
    */

/* force_auth
        Force the browser to display the login popup.  $db is the database for
        getting login information.
    */

function force_auth($realm)
{
    $logout = intval(get_argument('logout', 0, 0));
    header("WWW-Authenticate: Basic realm=\"$realm\"");
    header('HTTP/1.0 401 Unauthorized');
    echo "Password required.\n"; // if user cancels
    if ($logout) {
        $self = server_var('PHP_SELF');
        $args = server_var('QUERY_STRING');
        $args = preg_replace("/&?logout=[^&]*(&user=[^&]*)?/", "", $args);
        $href = ($args) ? "$self?$args" : $self;
        $link = html_link($href, 'Cancel log in as new user (stay logged in as old user)');
        echo "<br><br>\n$link\n";
    }
    exit;
}


function load_one_user($name, $db)
{
    $row  = array();
    $sql  = "select * from Users where\n";
    $sql .= " username='$name'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    }
    return $row;
}


/*
    |  If a restricted user attempts to log in, redirect him
    |  to the file manager.  This is normally installed as
    |  /main/acct/files.php.
    */

function redirect_restricted_login($name)
{
    $comp = component_installed();
    $ssl  = true; //server_var('HTTPS');
    $host = server_var('HTTP_HOST');
    $http = ($ssl) ? 'https' : 'http';
    $odir = $comp['odir'];
    $page = ($odir) ? "$odir/acct/files.php" : 'main/acct/files.php';
    $href = "https://$host/$page";
    //  logs::log(__FILE__, __LINE__, "login: $name $href",0);
    ob_end_clean();
    header("Location: $href");
    exit();
}




function special_login($db, &$good, &$rest, &$admin, &$realm)
{
    $rest  = 0;
    $admin = 0;
    $good  = false;
    $name  = server_var('PHP_AUTH_USER');
    $pass  = server_var('PHP_AUTH_PW');
    $user  = trim(get_argument('user', 0, ''));

    $realm  = server_opt('company_name', $db);
    $logout = intval(get_argument('logout', 0, 0));
    if ($logout) {
        if (($user == '') || ($user == $name)) {
            $name = '';
            $pass = '';
        }
    }

    $mu = array();
    if (($name != '') && ($pass != '')) {
        $row = load_one_user($name, $db);
        if ($row) {
            $password = $row['password'];
            $name     = $row['username'];
            $rest     = $row['priv_restrict'];
            $admin    = $row['priv_admin'];
            $digest   = md5($pass);
            if ($digest != $password) {
                $hfn = server_def('master_user', 'hfn', $db);
                $mu  = load_one_user($hfn, $db);
                if ($mu) {
                    $password = $mu['password'];
                }
            }
            $good = ($digest == $password) ? true : false;
        }
    }
    if ((!$good) && ($name != '')) {
        $host = server_var('REMOTE_ADDR');
        $port = server_var('REMOTE_PORT');
        $stat = "h:$host,p:$port";
        $text = "login: $name: failed login ($stat)";
        logs::log(__FILE__, __LINE__, $text, 0);
    }
    return $name;
}


/*
    |   process_login
    |
    |   Process the login for nearly every page.
    |   Redirects restriced users to the file manager.
    */

function process_login($db)
{
    $good  = 0;
    $rest  = 0;
    $admin = 0;
    $realm = '';
    $name  = special_login($db, $good, $rest, $admin, $realm);
    if ($good) {
        if (($rest) && (!$admin)) {
            redirect_restricted_login($name);
        }
    } else {
        force_auth($realm);
    }
    return $name;
}


/*
    |  This is used by the file manager pages ... it allows
    |  restricted users to login normally.
    */

function restrict_login($db)
{
    $good  = 0;
    $rest  = 0;
    $admin = 0;
    $realm = '';
    $name = special_login($db, $good, $rest, $admin, $realm);
    if (!$good) {
        force_auth($realm);
    }
    return $name;
}

function logout_link($comp, $authuser)
{
    $odir = $comp['odir'];
    $msg  = '';
    if ($odir) {
        $page = 'acct/login.php';
        $args = "logout=1&user=$authuser";
        $href = "/$odir/$page?$args";
        $link = html_link($href, 'log in as new user');
        $msg  = "[$link]";
    }
    return $msg;
}

function check_multisite_user($authuser, $db)
{
    $multisite = 0;
    if ($authuser) {
        $multisite = 1;
        $qu  = safe_addslashes($authuser);
        $sql = "select id from " . $GLOBALS['PREFIX'] . "core.Customers\n"
            . " where username = '$qu'";
        $res = command($sql, $db);
        if ($res) {
            if (mysqli_num_rows($res) < 2)
                $multisite = 0;
        }
    }
    return $multisite;
}

function get_filtersetting($authuser, $db)
{
    $filtersites = 0;
    if ($authuser) {
        $qu  = safe_addslashes($authuser);
        $sql = "select filtersites from " . $GLOBALS['PREFIX'] . "core.Users\n"
            . " where username = '$qu'";
        $res = command($sql, $db);
        if ($res) {
            if (mysqli_num_rows($res) == 1) {
                $row = mysqli_fetch_assoc($res);
                $filtersites = $row['filtersites'];
            }
        }
    }
    return $filtersites;
}

function sitefilter_link($authuser, $db)
{
    $msg  = '';
    $comp = component_installed();
    $odir = $comp['odir'];

    if ($odir) {
        $filtersetting = (get_filtersetting($authuser, $db)) ? 'on' : 'off';
        $href = "/$odir/acct/sitefltr.php";
        $link = html_link($href, 'set site filter');
        $msg  = "<b>site filter:</b> $filtersetting [$link]";
    }

    return $msg;
}

/*  head_logo_state()
        This determines what logo is displayed based on the settings.
        1.) First we use the hardwired settings.
        2.) Then we look for global settings that get set in server.php  (server_def)
        3.) Lastly we check for user specific settings.
    */

function head_logo_state($user, $comp, $db)
{
    $logo = array();
    $odir = @$comp['odir'];

    /* Set the defaults */
    $xxx  = '200';
    $yyy  = '59';
    $src  = ($odir) ? "/$odir/pub/logo.gif" : '/main/pub/logo.gif';

    /* Override with global settings (server.php) */
    $src = server_def('logo_file', $src, $db);
    $xxx = server_def('logo_x', $xxx, $db);
    $yyy = server_def('logo_y', $yyy, $db);

    /* Override with user specific settings (core.users) */
    $tmp = get_user_options('logo_file', $user, $src, $db);
    if (($tmp) != ($src)) {
        /* We always tack on the directory to a user-specific setting. */
        $src = "/main/pub/patches/$tmp";
    }
    $xxx = get_user_options('logo_x', $user, $xxx, $db);
    $yyy = get_user_options('logo_y', $user, $yyy, $db);

    /* Return the final settings with overrides. */
    $logo['src'] = $src;
    $logo['xxx'] = $xxx;
    $logo['yyy'] = $yyy;

    /* For debugging */
    //logs::log(__FILE__, __LINE__, "temp = ($tmp)", 0);
    //logs::log(__FILE__, __LINE__, "User = ($user)", 0);
    //$userlogo = $logo['src'];
    //logs::log(__FILE__, __LINE__, "File = ($userlogo)", 0);
    //logs::log(__FILE__, __LINE__, "logo_x = ($xxx)", 0);
    //logs::log(__FILE__, __LINE__, "logo_y = ($yyy)", 0);

    return $logo;
}


function standard_style()
{
    $agent    = server_var('HTTP_USER_AGENT');
    $netscape = !strstr($agent, 'compatible');

    $font_heading   = ($netscape) ? 'larger'  : 'medium';
    $font_footnote  = ($netscape) ? 'x-small' : 'xx-small';
    $style = <<< HERE

    <style type="text/css">
        BODY, TD, TH, P  {font-family: Verdana, Helvetica, sans-serif;
                          font-size: 10pt;}
        .blue         {font-family: Verdana, Helvetica, sans-serif;
                       color:333399;}
        .red          {font-family: Verdana, Helvetica, sans-serif;
                       color:FF0000;}
        .heading      {font-family: Verdana, Helvetica, sans-serif;
                       font-size: $font_heading;
                       color:333399;}
        .subheading   {font-family: Verdana, Helvetica, sans-serif;
                       color:333399; font-weight:bold;}
        .footnote     {font-family: Verdana, Helvetica, sans-serif;
                       font-size: $font_footnote;}
        .faded        {font-family: Verdana, Helvetica, sans-serif;
                       color: #666666}
        .hide         {position:absolute; top:200; left:250; visibility:hidden}
        .bold         {font-weight:bold}
        a.hidelink:link     {color: #000000; text-decoration: none;}
        a.hidelink:visited  {color: #000000; text-decoration: none;}
        a.hidelink:hover    {color: #0000ff; text-decoration: underline;}
        a.hidelink:active   {color: #000000; text-decoration: none;}

    </style>

HERE;
    return $style;
}


function check_cache($nocache, $db)
{
    $disable = intval(server_def('disable_cache', 0, $db));
    if (($disable != 0) || ($nocache == 1)) {
        $gmtime = gmdate('D, d M Y H:i:s', time());           // modified now ...
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
        header("Last-Modified: $gmtime GMT");                // always modified
        header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");                          // HTTP/1.0
    }
}


function header_tag($name, $a)
{
    $menu = join(" |\n", $a);
    return "<b>$name:</b> $menu<br>\n";
}



function global_def($name, $def)
{
    return (isset($GLOBALS[$name])) ? $GLOBALS[$name] : $def;
}


function standard_html_header($title, $comp, $authuser, $local_nav, $local_inf, $legend, $db)
{
    return extended_html_header(
        $title,
        $comp,
        $authuser,
        $local_nav,
        $local_inf,
        $legend,
        0,
        $db
    );
}


function extended_html_header(
    $title,
    $comp,
    $authuser,
    $local_nav,
    $local_inf,
    $legend,
    $nocache,
    $db
) {
    check_cache($nocache, $db);

    $refreshtime = global_def('refreshtime', '');
    $standard_style = standard_style();

    $msg  = '<html>';
    $msg .= <<< HERE

<head>
    $refreshtime
    <title>$title</title>
    $standard_style
</head>

<body link="#333399" vlink="#660066" alink="#00FF00" bgcolor="#FFFFFF">

<a name="top"></a>

<table width="100%" border="0">
    <tr>
        <td align="left" valign="top">

HERE;
    echo $msg;
    $msg  = '<br>';
    $odir = $comp['odir'];
    if ($odir) {
        $logo = head_logo_state($authuser, $comp, $db);
        $src  = $logo['src'];
        $xxx  = $logo['xxx'];
        $yyy  = $logo['yyy'];
        $href = "/$odir/index.php";
        $img  = "<img border=\"0\" src=\"$src\" width=\"$xxx\" height=\"$yyy\">";
        $msg  = "<a href=\"$href\">\n$img</a>\n";
    }
    echo $msg;
?>
    </td>
    <td align="right">
        <?php
        $msg = '<br>';
        if ($odir) {
            $m = '';
            if ($comp['dash']) {
                $a   = array();
                $p   = "/$odir/dashbrd";
                $a[] = html_link("$p/dnav.php", 'display');
                $m  .= header_tag('dashboard', $a);
            }
            if ($comp['evnt']) {
                $a   = array();
                $p   = "/$odir/event";
                $a[] = html_link("$p/event.php", 'ad-hoc query');
                $a[] = html_link("$p/search.php", 'filters');
                $a[] = html_link("$p/notify.php", 'notifications');
                $a[] = html_link("$p/console.php", 'console');
                $m  .= header_tag('events', $a);
            }
            if ($comp['asst']) {
                $a   = array();
                $p   = "/$odir/asset";
                $a[] = html_link("$p/adhoc.php", 'ad-hoc queries');
                $a[] = html_link("$p/query.php", 'saved queries');
                $a[] = html_link("$p/console.php", 'console');
                $a[] = html_link("$p/change.php", 'changes');
                $m  .= header_tag('assets', $a);
            }
            if (($comp['cnfg']) && ($comp['updt'])) {
                $a   = array();
                $p   = "/$odir/config";
                $q   = "/$odir/updates";
                $cmd = "$p/index.php?act";
                $a[] = html_link("$cmd=wiz", 'wizard');
                $a[] = html_link("/$odir/config/status.php", 'status');
                $a[] = html_link("$cmd=site", 'configuration');
                $a[] = html_link("$q/index.php", 'updates');
                $m  .= header_tag('sites', $a);
            }
            if ($comp['ptch']) {
                $p   = "/$odir/patch";
                $a   = array();
                $a[] = html_link("$p/wu-confg.php", 'wizard');
                $a[] = html_link("$p/wu-stats.php", 'status');
                $a[] = html_link("$p/insw.php", 'analysis');
                $a[] = html_link("$p/wu-sites.php", 'advanced');
                $m  .= header_tag('microsoft update', $a);
                $a   = array();
                $p   = "/$odir/patch";
                $a[] = html_link("$p/su-stats.php", 'status');
                $a[] = html_link("$p/su-sites.php", 'sites');
                $a[] = html_link("$p/su-prods.php", 'products');
                $a[] = html_link("$p/su-confg.php", 'configuration');
                //            $m  .= header_tag('software update',$a);
            }
            if (($comp['cnfg']) && ($comp['prov'])) {
                $a   = array();
                $p   = "/$odir/provis";
                $a[] = html_link("$p/product.php", 'products');
                $a[] = html_link("$p/sites.php", 'sites');
                $a[] = html_link("$p/meter.php", 'metering');
                $a[] = html_link("$p/audit.php", 'audit');;
                $m  .= header_tag('provisioning', $a);
            }
            if ($comp['acct']) {
                $a   = array();
                $p   = "/$odir/acct/files.php?c";
                $a[] = html_link("$p=1", 'event');
                $a[] = html_link("$p=2", 'asset');
                $a[] = html_link("$p=3", 'change');
                $a[] = html_link("$p=4", 'meter');
                $m  .= header_tag('information portal', $a);
            }
            $a = array();
            $p = "/$odir/report/report.php?act";
            /* constActListReports=0 */
            $a[] = html_link("$p=0", 'reports');
            /* constActListSections=4 */
            $a[] = html_link("$p=4", 'sections');
            /* constActListSchedules=0 */
            $a[] = html_link("/$odir/report/sched.php?act=0", 'schedules');
            /* constActLegacy=23 */
            $a[] = html_link("$p=23", 'legacy reports');
            $m .= header_tag('reports', $a);
            if (($comp['acct']) && ($comp['help'])) {
                $a   = array();
                $p   = "/$odir/acct";
                $q   = "/$odir/doc";
                $c   = "/$odir/config";
                $a[] = html_link("$p/admin.php", 'admin');
                $a[] = html_link("$p/csrv.php?auditlog", 'audit');
                $a[] = html_link("$p/census.php", 'census');
                $a[] = html_link("$c/groups.php?custom=3", 'groups');
                $a[] = html_link("$c/remote.php?scop=4&act=scop&pcn=cwiz"
                    . "&rcon=1", 'remote control');
                $a[] = html_link("$q/index.php", 'help');
                $m  .= header_tag('tools', $a);
            }
            $msg = "$m<br><br>\n";
        }
        echo $msg;
        echo <<< HERE

        </td>
    </tr>
</table>

<table width="100%" border="0">
    <tr>
        <td align="left" valign="top">
            <span class="heading">
                $title
            </span>
        </td>
        <td align="right" valign="top">

HERE;
        /* The "how to" button */
        $srvdir = server_root();
        echo "<input type=\"submit\" value=\"How to\" style=\"height:"
            . "27px; font-size: 9pt;\"onclick=\"window.open("
            . "'/$srvdir/howto/HFN%20ASI%20main%20how-to%2001.html',"
            . "'_blank');\"><br>";

        if ($local_nav) {
            echo $local_nav;
        }
        if ($local_inf) {
            echo $local_inf;
        }

        $date = date('F d, Y');
        $log  = logout_link($comp, $authuser);
        $msg  = "<b>user: $authuser</b> $log<br>\n";
        if (check_multisite_user($authuser, $db)) {
            $lnk  = sitefilter_link($authuser, $db);
            $msg .= "$lnk<br>\n";
        }
        $msg .= "$date<br>\n";
        $msq  = "<span class=\"footnote\">\n$msg</span>\n";
        echo $msq;
        ?>
    </td>
    </tr>

<?php
    if ($legend) {
        echo <<< HERE

            <tr>
                <td>
                    <br>
                </td>
                <td align="right">
                    <img border="0" src="$legend">
                </td>
            </tr>
HERE;
    }
    echo "\n</table>\n\n\n";
}


/*  standard_html_footer()
        This determines what text footer left and footer right is displayed based
        on the settings located in three places.
        1.) First if we don't find any of these we use the hardwired settings.
        2.) Then we look for global settings that get set in server.php  (server_def)
        3.) Lastly we check for user specific settings are set we return them.
    */
function head_standard_html_footer($user, $db)
{
    $version = '';
    if (function_exists('asi_info')) {
        $info = asi_info();
        $vers = $info['svvers'];
        $date = $info['svdate'];
        $version = "<font color=\"gray\">Version $vers: $date\n</font>";
    }

    /* Set the hardwired defaults */
    $footer_left  = '&copy; 2000-2006 HandsFree Networks<br>';
    $footer_left .= '90 Washington Street<br>';
    $footer_left .= 'Newton, MA 02458-2220';

    $mt  = 'info@handsfreenetworks.com';
    $footer_right  = "Tel: 617-641-9381<br>\n";
    $footer_right .= "Fax: 617-969-1124<br>\n";
    $footer_right .= "<a href=\"mailto:$mt\">$mt</a>";
    $footer_right .= "&nbsp;";

    /* Override with global settings from server.php */
    $footer_left = server_def('footer_left', $footer_left, $db);
    $footer_right = server_def('footer_right', $footer_right, $db);

    /* Override with user specific settings */
    $footer_left = get_user_options('footer_left', $user, $footer_left, $db);
    $footer_right = get_user_options('footer_right', $user, $footer_right, $db);

    $footer_left = str_replace("\r\n", "\n", $footer_left);
    $footer_right = str_replace("\r\n", "\n", $footer_right);

    $right = fontspeak($footer_right);
    $left  = fontspeak("$footer_left<br>\n$version");
    $msg   = <<< HERE

<hr color="#333399" noshade size="1">

<div align="right">
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
      <tr>
        <td width="50%" valign="top">
          <p>
            $left
          </p>
        </td>
        <td width="50%" valign="top" align="right">
            $right
        </td>
      </tr>
  </table>
</div>

<br>
<a name="bottom"></a>
</body>

HERE;
    $msg .= "</html>\n";
    return $msg;
}



/*
    |  We now need to retrieve the logo information for the user
    |  Return these settings in the master "env" variable.
    */
function head_get_user_logo($user, &$env, $mdb)
{
    /* Gather inforamtion about the logo for the report */
    $comp = component_installed();
    $logo = head_logo_state($user, $comp, $mdb);
    $img  = logo_image($logo, $env['server']);
    $host = server_href($mdb);
    $iref = ($img) ? image_href($img) : logo_iref($logo, $host);

    /* Now set the "env" variables for the logo */
    $env['img'] = $img;
    $env['iref'] = $iref;
    $env['xxx'] = $logo['xxx'];
    $env['yyy'] = $logo['yyy'];
    $env['src'] = $host . $logo['src'];
    return $env;
}


?>