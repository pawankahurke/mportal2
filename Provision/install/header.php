<?php
/*
  Revision history:

  Date        Who     What
  ----        ---     ----
  13-May-03   NL      Creation.
  29-May-03   EWB     Suppress annoying logfile messages.
  29-May-03   NL      Only show servers link if priv_serv.
  29-May-03   NL      db_change('core',$db) for server options (realm & logo)
  02-Jun-03   NL      HFN logo link --> install home.
  02-Jun-03   NL      Create install_html_footer($db) (has no version)
  02-Jun-03   NL      Oops, we do want a version, but distinct from ASI software.
  04-Jun-03   NL      Change link names.
  09-Jun-03   NL      Change sitelist.php to sites.php?action=list
  09-Jun-03   NL      install_html_header(): If a siteid is passed, show emails sub nav.
  11-Jun-03   NL      Add emails subnav; add in user name and date.
  11-Jun-03   NL      Change action=emails to action=emaildistr.
  16-Jun-03   NL      Moved this file from server/lib/insthd.php to install/header.php.
  16-Jun-03   NL      Instead of changing db back to install, change back to previous db.
  24-Jun-03   NL      Change subnav links.
  23-Jul-03   NL      Use same vers as ASI software, so delete install_html_footer().
  28-Jul-03   NL      Create blue subheading CSS class.
  31-Jul-03   EWB     Created install_login, install_user.
  14-Aug-03   NL      Remove "user: $authuser" line if blank authuser.
  15-Aug-03   NL      Add get_key_index().
  28-Aug-03   NL      Oops.  Put <BR> back in after "user: $authuser" line.
  29-Aug-03   NL      Move get_key_index() function to the new lib/l-dberr.php.
  29-Sep-03   NL      Remove "user" link --> even non-admin user goes to userlist page.
  3-Oct-03   NL      Clean up long $args; Add some function descriptions.
  23-Oct-03   NL      BACK OUT: Remove "user" link --> even non-admin user goes to userlist page.
  13-Jul-04   WOH     Added help link to links list at top of the page.
  13-Jul-04   WOH     Added format information.
  19-Jul-04   WOH     Change reference of install help document.
  09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
  19-Jun-07   AAM     Added "service offerings" link.
  25-Oct-07   AAM     Bug 4823: backed out "service offerings" link.
  16-Oct-19   SVG     Import Customer Key option added.

 */

function install_user($name, $db) {
    $row = array();
    $sql = "select * from Users where\n";
    $sql .= " installuser='$name'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_array($res);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    }
    return $row;
}

/*
  |   install_login
  |
  |   Attempts to authenticates the user. If the user is logging out or their
  |    userid & password aren't valid, directs to force_auth() for login.
  |
  |   parameters:
  |       $db:        The handle to the database connection.
 */

function install_login($db) {
    $good = false;
    $name = server_var('PHP_AUTH_USER');
    $pass = server_var('PHP_AUTH_PW');
    $user = trim(get_argument('user', 0, ''));

    $logout = intval(get_argument('logout', 0, 0));
    if ($logout) {
        if (($user == '') || ($user == $name)) {
            $name = '';
            $pass = '';
        }
    }

    if (($name != '') && ($pass != '')) {
        $row = install_user($name, $db);
        if ($row) {
            $password = $row['password'];
            $name = $row['installuser'];
            $digest = md5($pass);
            if ($digest != $password) {
                $hfn = server_def('master_user', 'hfn', $db);
                $hfn = install_user($hfn, $db);
                if ($hfn) {
                    $password = $hfn['password'];
                }
            }
            $good = ($digest == $password) ? true : false;
        }
    }

    if (!$good) {
        $realm = server_opt('install_realm', $db);
        force_auth($realm);
    }

    return $name;
}

function install_html_header($title, $comp, $authuser, $priv_admin, $priv_servers, $db, $siteid = '') {
    $refreshtime = global_def('refreshtime', '');

    $current_db_name = get_db_name($db);
    db_change($GLOBALS['PREFIX'].'core', $db);
    $realm = server_opt('company_name', $db); // gets company name from Options table.

    $browser_title = (isset($title)) ? $title : $realm;

    #CSS stylesheet fonts vary by browser
    $agent = server_var('HTTP_USER_AGENT');
    $netscape = !strstr($agent, 'compatible');

    $font_heading = ($netscape) ? 'larger' : 'medium';
    $font_footnote = ($netscape) ? 'x-small' : 'xx-small';

    $msg = '<html>';
    $msg .= <<< HERE

<head>
    <title>$browser_title</title>

    $refreshtime

    <style>
        BODY, TD, TH  {font-family: Verdana, Helvetica, sans-serif;
                       font-size: smaller;}
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
    </style>
            <script src="sites_config/js/jquery_lib.js"></script>
            <script src="sites_config/js/sites.js"></script>
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
        $href = "/$odir/install/index.php";
        $img = "<img border='0' src='$src' width='$xxx' height='$yyy'>";
        $msg = "<a href='$href'>\n$img</a>\n";
    }
    echo $msg;
    ?>
    </td>
    <td align="right" valign="top">
    <?php
    $m = '<br>';
    if ($odir) {
        $userpagename = ($priv_admin) ? 'users' : 'user';
        $userpageurl = ($priv_admin) ? 'userlist.php' : 'userdata.php';
        $customerpageurl = ($priv_admin) ? 'custlist.php' : 'custdata.php';
        $m = '';
        $p = "/$odir/install";
        $m .= "<b>installation:</b> ";
        $m .= "<a href='$p/$userpageurl'>Users</a> |\n";
        $m .= "<a href='$p/$customerpageurl'>Customer</a> |\n";
        $m .= "<a href='$p/sites.php?action=list'>sites</a> |\n";
        $m .= "<a href='$p/strtlist.php'>configurations</a>\n";
        if ($priv_servers) {
            $m .= " | <a href='$p/servlist.php'>servers</a>";
        }
        if ($priv_admin) {
            $m .= " | <a href='$p/maildata.php'>smtp config</a>";
            $m .= " | <a href='$p/skudata.php'>import key</a>";
            $m .= " | <a href='$p/custImportdata.php'>import Customer</a>";
        }
        $m .= " | <a href='/main/doc/installgd.pdf'>help</a>\n";
    }

    if ($siteid) {
        $m .= "<br><br>\n";
        $m .= "<span class='footnote'>\n";
        $m .= "<b>manage email distribution:</b>\n ";
        $m .= "<a href='emails.php?action=addresses&siteid=$siteid'>add addresses</a> |\n";
        $m .= "<a href='emails.php?action=emaildistr&siteid=$siteid'>send email</a> |\n";
        $m .= "<a href='emails.php?action=status&siteid=$siteid'>review email status</a>\n";
    }
    $m .= "</span>\n";
    $m .= "<br><br>\n";
    echo $m;
    ?>

        <?php
        $date = date('F d, Y');
        $msg = "<br>\n";
        if (strlen($authuser)) {
            //$log  = logout_link($authuser);
            //$msg  = "<b>user: $authuser</b> $log<br>\n";
            $msg = "<b>user: $authuser</b> | <a href='index.php?logout=1&user=$authuser'>Logout</a><br>\n";
        }
        $msg .= "$date<br>\n";
        $message = "<span class=\"footnote\">\n$msg</span>\n";
        echo $message;
        ?>
    </td>
    </tr>
    </table>

    <table width="100%" border="0">
        <tr>
            <td align="left" valign="top">
                <span class="heading"><?php echo $title ?></span>
            </td>
        </tr>
    </table>
        <?php
        db_change($current_db_name, $db);
}

function install_html_header_slave($title, $comp, $authuser, $priv_admin, $priv_servers, $db, $siteid = '') {
        $refreshtime = global_def('refreshtime', '');

        $current_db_name = get_db_name($db);
        db_change($GLOBALS['PREFIX'].'core', $db);
        $realm = server_opt('company_name', $db); // gets company name from Options table.

        $browser_title = (isset($title)) ? $title : $realm;

        #CSS stylesheet fonts vary by browser
        $agent = server_var('HTTP_USER_AGENT');
        $netscape = !strstr($agent, 'compatible');

        $font_heading = ($netscape) ? 'larger' : 'medium';
        $font_footnote = ($netscape) ? 'x-small' : 'xx-small';

        $msg = '<html>';
        $msg .= <<< HERE

<head>
    <title>$browser_title</title>

    $refreshtime

    <style>
        BODY, TD, TH  {font-family: Verdana, Helvetica, sans-serif;
                       font-size: smaller;}
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
    </style>
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
            $href = "/$odir/install/index.php";
            $img = "<img border='0' src='$src' width='$xxx' height='$yyy'>";
            $msg = "<a href='$href'>\n$img</a>\n";
        }
        echo $msg;
        ?>
    </td>
    <td align="right" valign="top">
    <?php
    $m = '<br>';
    if ($odir) {
        $userpagename = ($priv_admin) ? 'users' : 'user';
        $userpageurl = ($priv_admin) ? 'userlist.php' : 'userdata.php';
        $m = '';
        $p = "/$odir/install";
        $m .= "<b>installation:</b> ";
        $m .= "<a href='$p/$userpageurl'>Users</a> |\n";
        $m .= "<a href='$p/sites.php?action=list'>sites</a>\n";
            /* $m .= "<a href='$p/strtlist.php'>configurations</a>\n";
        if ($priv_servers) {
            $m .= " | <a href='$p/servlist.php'>servers</a>";
        }
        if ($priv_admin) {
            $m .= " | <a href='$p/maildata.php'>smtp config</a>";
            $m .= " | <a href='$p/skudata.php'>import key</a>";
              } */
        $m .= " | <a href='/main/doc/installgd.pdf'>help</a>\n";
    }

    if ($siteid) {
        $m .= "<br><br>\n";
        $m .= "<span class='footnote'>\n";
        $m .= "<b>manage email distribution:</b>\n ";
        $m .= "<a href='emails.php?action=addresses&siteid=$siteid'>add addresses</a> |\n";
        $m .= "<a href='emails.php?action=emaildistr&siteid=$siteid'>send email</a> |\n";
        $m .= "<a href='emails.php?action=status&siteid=$siteid'>review email status</a>\n";
    }
    $m .= "</span>\n";
    $m .= "<br><br>\n";
    //echo $m;
    ?>

        <?php
        $date = date('F d, Y');
        $msg = "<br>\n";
        if (strlen($authuser)) {
            //$log  = logout_link($authuser);
            //$msg  = "<b>user: $authuser</b> $log<br>\n";
            $msg = "<b>user: $authuser</b> |<a href='index.php?logout=1&user=$authuser'>Logout</a><br>\n";
        } else {
            if (!url::issetInRequest('action')) {
                $msg = "<a href='user-setup.php?action=generateimporttoken'>Generate Token</a> | ";
            }
            $msg .= "<a href='index.php'>Login</a><br>\n";
        }
        $msg .= "$date<br>\n";
        $message = "<span class=\"footnote\">\n$msg</span>\n";
        echo $message;
        ?>
    </td>
    </tr>
    </table>

    <table width="100%" border="0">
        <tr>
            <td align="left" valign="top">
                <span class="heading"><?php echo $title ?></span>
            </td>
        </tr>
    </table>
        <?php
        db_change($current_db_name, $db);
}
    ?>
