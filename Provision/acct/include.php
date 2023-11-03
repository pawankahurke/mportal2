<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
11-Jul-03   EWB     Created.
16-Jul-03   EWB     Don't worry about setting ldir for now.
24-Jul-03   EWB     Link back to files.php, home if debug
29-Jul-03   EWB     Rename 'menu' to 'index'
30-Jul-03   EWB     Don't display notes with file.
31-Jul-03   EWB     Restricted Users.
 4-Sep-03   EWB     Access by subset checking.
 7-Oct-03   EWB     Commandline override for admin/debug/restrict.
24-Jan-06   AAM     Bug 3072: change memory_limit setting to use max_php_mem_mb.
                    (Note that this change was actually moved from 4.2 to 4.3
                    on 06-Mar-06.)
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.                    

*/

ob_start();
$ldir = '';
$args = explode(',', '../lib,../../lib,../../../lib,/main/lib');
$file = 'l-db.php';

reset($args);
foreach ($args as $key => $path) {
    if ($ldir == '') {
        if (file_exists("$path/$file")) {
            $ldir = $path;
        }
    }
}

// http://us4.php.net/manual/en/function.realpath.php

if ($ldir == '') {
    $ldir = '/main/lib';
}

$root = '/var/www/html';
$ldir = "$root/main/lib";
$ldir = "$root/eric/dev/server/lib";
$ldir = "../lib";

include("$ldir/l-util.php");
include("$ldir/l-db.php");
include("$ldir/l-sql.php");
include("$ldir/l-serv.php");
include("$ldir/l-rcmd.php");
include("$ldir/l-gsql.php");
include("$ldir/l-user.php");
include("$ldir/l-sets.php");
include("$ldir/l-jump.php");
include("$ldir/l-file.php");
include("$ldir/l-rest.php");
include("$ldir/l-head.php");

function jumparound($env)
{
    $debug = $env['debug'];
    $odir  = $env['comp']['odir'];
    $a     = array();
    if ($odir) {
        if ($debug) {
            $a[] = html_link("/$odir/acct/index.php", 'home');
        }
        $a[] = html_link("/$odir/acct/files.php", 'index');
    }
    jumptags($a, 'top,bottom');
    return jumplist($a);
}

function load_file($id, $db)
{
    $data = array();
    if ($id > 0) {
        $sql = "select * from Files where id = $id";
        $data = find_one($sql, $db);
    }
    return $data;
}


/*
    |  This is acting as the main program for all the files.
    |
    */

function file_header($code)
{
    $title = "File View";
    $db    = db_connect();
    $authuser = restrict_login($db);
    $comp  = component_installed();
    $self  = server_var('PHP_SELF');
    $user  = user_data($authuser, $db);
    $carr  = site_array($authuser, 0, $db);
    $padm  = @($user['priv_admin']) ? 1 : 0;
    $pdbg  = @($user['priv_debug']) ? 1 : 0;
    $prst  = @($user['priv_restrict']) ? 1 : 0;

    $adm   = get_integer('admin', 1);
    $dbg   = get_integer('debug', 1);
    $rst   = get_integer('rest', 0);

    $debug = ($pdbg) ? $dbg : 0;
    $rest  = ($prst) ?   1  : $rst;
    if ($rest)
        $admin = 0;
    else
        $admin = ($padm) ? $adm : 0;

    $GLOBALS['debug'] = $debug;

    $note = '';
    $data = load_file($code, $db);
    if ($data) {
        $note  = $data['note'];
        $title = $data['name'];
    }

    $msg  = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer) 
    if ($rest) {
        echo restricted_html_header($title, $comp, $authuser, $db);
    } else {
        echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
    }
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    if ($debug) {
        //       echo "<pre>\n";
        //       print_r($data);
        //       echo "\n</pre>\n";
    }
    $mem = server_def('max_php_mem_mb', '256', $db);
    ini_set('memory_limit', $mem . 'M');
    ob_start();
    $env = array();
    $env['db']    = $db;
    $env['user']  = $authuser;
    $env['data']  = $data;
    $env['carr']  = $carr;
    $env['code']  = $code;
    $env['note']  = $note;
    $env['self']  = $self;
    $env['comp']  = $comp;
    $env['rest']  = $rest;
    $env['admin'] = $admin;
    $env['debug'] = $debug;
    return $env;
}


/*
    |  We run the entire file with output buffering turned on.
    |
    |  When we get called at the end, we decide if the currently
    |  logged in user should be allowed to see the file.
    |  
    |  The user should be allowed to inspect any
    |  file which contains a subset of his sites.
    |
    |  Also, an admin user can look at any file, whether he
    |  owns it or not.
    */

function file_footer(&$env)
{
    $db    = $env['db'];
    $user  = $env['user'];
    $data  = $env['data'];
    $note  = $env['note'];
    $admin = $env['admin'];
    $debug = $env['debug'];
    $good  = false;
    $msg   = ob_get_contents();
    ob_end_clean();

    if ($data) {
        if ($admin) {
            debug_note("admin access prevails");
            $good = true;
        } else {
            $code = $env['code'];
            $carr = $env['carr'];
            $site = file_site_list($code, $db);
            $temp = safe_count($site);
            $good = subset($site, $carr);
            debug_note("report has $temp sites, good:$good");
        }
        if (!$good) {
            $msg = "You do not own this file.<br>\n";
        }
    } else {
        $txt = "There is no record of this file.<br>\n";
        if ($admin) {
            $msg = $txt . $msg;
        } else {
            $msg = $txt;
        }
    }
    echo jumparound($env);
    if (($good) && ($note)) {
        $note = htmlspecialchars($note);
        $note = nl2br($note);
        //        echo "<font color=\"grey\">\n\n$note\n</font>\n\n";
    }

    echo $msg;
    echo jumparound($env);
    echo head_standard_html_footer($user, $db);
}
