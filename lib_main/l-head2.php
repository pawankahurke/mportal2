<?php

function oem_dir($root, $dirs)
{

    $odir = '';

    $deep = safe_count($dirs);

    $n = $deep - 2;

    for ($i = 1; $i <= $n; $i++) {

        $odir = ($odir) ? "$odir/" : $odir;

        $odir .= $dirs[$i];

        $acct = "$root/$odir/acct/admin.php";

        if (file_exists($acct)) {

            return $odir;
        }
    }

    return '';
}

function server_root()
{

    $root = server_var('DOCUMENT_ROOT');

    $self = server_var('PHP_SELF');

    $dirs = explode('/', $self);

    $odir = oem_dir($root, $dirs);

    return $odir;
}

function component_installed()
{

    $temp = array();

    $root = '/var/www/html';
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

function force_auth($realm)
{

    $logout = intval(get_argument('logout', 0, 0));

    header("WWW-Authenticate: Basic realm=\"$realm\"");

    header('HTTP/1.0 401 Unauthorized');

    echo "Password required.\n";
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

    $row = array();

    $sql = "select * from Users where\n";

    $sql .= " username='$name'";

    $res = command($sql, $db);

    if ($res) {

        if (mysqli_num_rows($res) == 1) {

            $row = mysqli_fetch_assoc($res);

            mysqli_free_result($res);
        }
    }

    return $row;
}

function redirect_restricted_login($name)
{

    $comp = component_installed();

    $ssl = true; //server_var('HTTPS');

    $host = server_var('HTTP_HOST');

    $http = ($ssl) ? 'https' : 'http';

    $odir = $comp['odir'];

    $page = ($odir) ? "$odir/acct/files.php" : 'main/acct/files.php';

    $href = "https://$host/$page";

    ob_end_clean();

    header("Location: $href");

    exit();
}

function special_login($db, &$good, &$rest, &$admin, &$realm)
{

    $rest = 0;

    $admin = 0;

    $good = false;

    $name = server_var('PHP_AUTH_USER');

    $pass = server_var('PHP_AUTH_PW');

    $user = trim(get_argument('user', 0, ''));

    $realm = server_opt('company_name', $db);

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

            $name = $row['username'];

            $rest = $row['priv_restrict'];

            $admin = $row['priv_admin'];

            $digest = md5($pass);

            if ($digest != $password) {

                $hfn = server_def('master_user', 'hfn', $db);

                $mu = load_one_user($hfn, $db);

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
    }

    return $name;
}

function process_login($db)
{

    $good = 0;

    $rest = 0;

    $admin = 0;

    $realm = '';

    $name = special_login($db, $good, $rest, $admin, $realm);

    if ($good) {

        if (($rest) && (!$admin)) {

            redirect_restricted_login($name);
        }
    } else {

        force_auth($realm);
    }

    return $name;
}

function restrict_login($db)
{

    $good = 0;

    $rest = 0;

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

    $msg = '';

    if ($odir) {

        $page = 'acct/login.php';

        $args = "logout=1&user=$authuser";

        $href = "/$odir/$page?$args";

        $link = html_link($href, 'log in as new user');

        $msg = "[$link]";
    }

    return $msg;
}

function check_multisite_user($authuser, $db)
{

    $multisite = 0;

    if ($authuser) {

        $multisite = 1;

        $qu = safe_addslashes($authuser);

        $sql = "select id from " . $GLOBALS['PREFIX'] . "core.Customers\n"

            . " where username = '$qu'";

        $res = command($sql, $db);

        if ($res) {

            if (mysqli_num_rows($res) < 2) {
                $multisite = 0;
            }
        }
    }

    return $multisite;
}

function get_filtersetting($authuser, $db)
{

    $filtersites = 0;

    if ($authuser) {

        $qu = safe_addslashes($authuser);

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

    $msg = '';

    $comp = component_installed();

    $odir = $comp['odir'];

    if ($odir) {

        $filtersetting = (get_filtersetting($authuser, $db)) ? 'on' : 'off';

        $href = "/$odir/acct/sitefltr.php";

        $link = html_link($href, 'set site filter');

        $msg = "<b>site filter:</b> $filtersetting [$link]";
    }

    return $msg;
}

function head_logo_state($user, $comp, $db)
{

    $logo = array();

    $odir = @$comp['odir'];

    $xxx = '200';

    $yyy = '59';

    $src = ($odir) ? "/$odir/pub/logo.gif" : '/main/pub/logo.gif';

    $src = server_def('logo_file', $src, $db);

    $xxx = server_def('logo_x', $xxx, $db);

    $yyy = server_def('logo_y', $yyy, $db);

    $tmp = get_user_options('logo_file', $user, $src, $db);

    if (($tmp) != ($src)) {

        $src = "/main/pub/patches/$tmp";
    }

    $xxx = get_user_options('logo_x', $user, $xxx, $db);

    $yyy = get_user_options('logo_y', $user, $yyy, $db);

    $logo['src'] = $src;

    $logo['xxx'] = $xxx;

    $logo['yyy'] = $yyy;

    return $logo;
}

function standard_style()
{

    $cssfile = 'http://' . server_var('SERVER_NAME') . ':'

        . server_var('SERVER_PORT') . '/' . server_root()

        . '/report/default.css';

    return "<LINK href=\"$cssfile\" rel=\"stylesheet\" "

        . 'type="text/css">';
}

function check_cache($nocache, $db)
{

    $disable = intval(server_def('disable_cache', 0, $db));

    if (($disable != 0) || ($nocache == 1)) {

        $gmtime = gmdate('D, d M Y H:i:s', time());
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: $gmtime GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);

        header("Pragma: no-cache");
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
    echo 'yes';
    return custom_html_header(
        $title,
        $comp,
        $authuser,
        $local_nav,
        $local_inf,

        $legend,
        0,
        '',
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

    return custom_html_header(
        $title,
        $comp,
        $authuser,
        $local_nav,

        $local_inf,
        $legend,
        $nocache,
        '',
        $db
    );
}

function custom_html_header(
    $title,
    $comp,
    $authuser,
    $local_nav,
    $local_inf,

    $legend,
    $nocache,
    $headtxt,
    $db
) {

    check_cache($nocache, $db);

    $refreshtime = global_def('refreshtime', '');

    $standard_style = standard_style();

    $msg = '<html>';

    $msg .= <<< HERE



<head>

    $refreshtime

    <title>$title</title>

    $standard_style

    $headtxt

</head>



<body link="#333399" vlink="#660066" alink="#00FF00" bgcolor="#FFFFFF">



<a name="top"></a>



<table width="100%" border="0">

    <tr>

        <td align="left" valign="top">



HERE;

    echo $msg;

    $msg = '<br>';

    $odir = $comp['odir'];

    if ($odir) {

        $logo = head_logo_state($authuser, $comp, $db);

        $src = $logo['src'];

        $xxx = $logo['xxx'];

        $yyy = $logo['yyy'];

        $href = "/$odir/index.php";

        $img = "<img border=\"0\" src=\"$src\" width=\"$xxx\" height=\"$yyy\">";

        $msg = "<a href=\"$href\">\n$img</a>\n";
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

                $a = array();

                $p = "/$odir/dashbrd";

                $a[] = html_link("$p/dnav.php", 'display');

                $m .= header_tag('dashboard', $a);
            }

            if ($comp['evnt']) {

                $a = array();

                $p = "/$odir/event";

                $a[] = html_link("$p/event.php", 'search');

                $a[] = html_link("$p/search.php", 'filters');

                $a[] = html_link("$p/notify.php", 'notifications');

                $a[] = html_link("$p/console.php", 'console');

                $m .= header_tag('events', $a);
            }

            if ($comp['asst']) {

                $a = array();

                $p = "/$odir/asset";

                $a[] = html_link("$p/adhoc.php", 'search');

                $a[] = html_link("$p/query.php", 'saved queries');

                $a[] = html_link("$p/console.php", 'console');

                $a[] = html_link("$p/change.php", 'changes');

                $m .= header_tag('assets', $a);
            }

            if (($comp['cnfg']) && ($comp['updt'])) {

                $a = array();

                $p = "/$odir/config";

                $q = "/$odir/updates";

                $cmd = "$p/index.php?act";

                $a[] = html_link("$cmd=wiz", 'wizard');

                $a[] = html_link("/$odir/config/status.php", 'status');

                $a[] = html_link("$cmd=site", 'configuration');

                $a[] = html_link("$q/index.php", 'updates');

                $m .= header_tag('sites', $a);
            }

            if ($comp['ptch']) {

                $p = "/$odir/patch";

                $a = array();

                $a[] = html_link("$p/wu-confg.php", 'wizard');

                $a[] = html_link("$p/wu-stats.php", 'status');

                $a[] = html_link("$p/insw.php", 'analysis');

                $a[] = html_link("$p/wu-sites.php", 'advanced');

                $m .= header_tag('microsoft update', $a);

                $a = array();

                $p = "/$odir/patch";

                $a[] = html_link("$p/su-stats.php", 'status');

                $a[] = html_link("$p/su-sites.php", 'sites');

                $a[] = html_link("$p/su-prods.php", 'products');

                $a[] = html_link("$p/su-confg.php", 'configuration');
            }

            if (($comp['cnfg']) && ($comp['prov'])) {

                $a = array();

                $p = "/$odir/provis";

                $a[] = html_link("$p/product.php", 'products');

                $a[] = html_link("$p/sites.php", 'sites');

                $a[] = html_link("$p/meter.php", 'metering');

                $a[] = html_link("$p/audit.php", 'audit');

                $m .= header_tag('provisioning', $a);
            }

            if ($comp['acct']) {

                $a = array();

                $p = "/$odir/acct/files.php?c";

                $a[] = html_link("$p=1", 'event');

                $a[] = html_link("$p=2", 'asset');

                $a[] = html_link("$p=3", 'change');

                $a[] = html_link("$p=4", 'meter');

                $m .= header_tag('information portal', $a);
            }

            $a = array();

            $p = "/$odir/report/report.php?act";

            $a[] = html_link("$p=0", 'reports');

            $a[] = html_link("$p=4", 'sections');

            $a[] = html_link("/$odir/report/sched.php?act=0", 'schedules');

            $a[] = html_link("$p=23", 'legacy reports');

            $m .= header_tag('reports', $a);

            if (($comp['acct']) && ($comp['help'])) {

                $a = array();

                $p = "/$odir/acct";

                $q = "/$odir/doc";

                $c = "/$odir/config";

                $a[] = html_link("$p/admin.php", 'admin');

                $a[] = html_link("$p/csrv.php?auditlog", 'audit');

                $a[] = html_link("$p/census.php", 'census');

                $a[] = html_link("$c/groups.php?custom=3", 'groups');

                $a[] = html_link("$c/remote.php?scop=4&act=scop&pcn=cwiz"

                    . "&rcon=1", 'remote control');

                $a[] = html_link("$q/index.php", 'help');

                $m .= header_tag('tools', $a);
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

        $log = logout_link($comp, $authuser);

        $msg = "<b>user: $authuser</b> $log<br>\n";

        if (check_multisite_user($authuser, $db)) {

            $lnk = sitefilter_link($authuser, $db);

            $msg .= "$lnk<br>\n";
        }

        $msg .= "$date<br>\n";

        $msq = "<span class=\"footnote\">\n$msg</span>\n";

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

function head_standard_html_footer($user, $db)
{

    $version = '';

    if (function_exists('asi_info')) {

        $info = asi_info();

        $vers = $info['svvers'];

        $date = $info['svdate'];

        $version = "<font color=\"gray\">Version $vers: $date\n</font>";
    }

    $footer_left = '&copy; 2000-2006 HandsFree Networks<br>';

    $footer_left .= '90 Washington Street<br>';

    $footer_left .= 'Newton, MA 02458-2220';

    $mt = 'info@handsfreenetworks.com';

    $footer_right = "Tel: 617-641-9381<br>\n";

    $footer_right .= "Fax: 617-969-1124<br>\n";

    $footer_right .= "<a href=\"mailto:$mt\">$mt</a>";

    $footer_right .= "&nbsp;";

    $footer_left = server_def('footer_left', $footer_left, $db);

    $footer_right = server_def('footer_right', $footer_right, $db);

    $footer_left = get_user_options('footer_left', $user, $footer_left, $db);

    $footer_right = get_user_options('footer_right', $user, $footer_right, $db);

    $footer_left = str_replace("\r\n", "\n", $footer_left);

    $footer_right = str_replace("\r\n", "\n", $footer_right);

    $right = fontspeak($footer_right);

    $left = fontspeak("$footer_left<br>\n$version");

    $msg = <<< HERE



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

function head_get_user_logo($user, &$env, $mdb)
{

    $comp = component_installed();

    $logo = head_logo_state($user, $comp, $mdb);

    $img = logo_image($logo, $env['server']);

    $host = server_href($mdb);

    $iref = ($img) ? image_href($img) : logo_iref($logo, $host);

    $env['img'] = $img;

    $env['iref'] = $iref;

    $env['xxx'] = $logo['xxx'];

    $env['yyy'] = $logo['yyy'];

    $env['src'] = $host . $logo['src'];

    return $env;
}

?>