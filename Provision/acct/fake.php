<?php

/*
Revision history:

Date        Who     What
----        ---     ----
25-Feb-04   EWB     Created.
22-Apr-04   EWB     Fake Event Works.
11-Jun-04   BTE     Added fake INST_SendPatches RPC call.
23-Jun-04   BTE     Added fake INST_GetWUConfig, INST_CheckWUTimes RPC calls.
24-Jun-04   BTE     Added fake INST_SetStatus and INST_GetPatches RPC calls.
16-Jul-04   BTE     Updated to use the new database.
26-Jul-04   EWB     Fake config log creates install user entries.
16-Aug-04   BTE     Added fake INST_GetDefaultConfig.
13-Oct-04   EWB     Call 'INST_GetPatches' for any machine.
17-Mar-05   EWB     ELOG_LogEvent
18-Mar-05   EWB     ELOG_AssetLog / ELOG_AssetDataALIST
22-Mar-05   EWB     PROV_ReportMeter / PROV_LogReportMeter
24-Mar-05   EWB     INST_UploadPatch
25-Mar-05   EWB     new config logging changes
27-May-05   EWB     uses gconfig database ...
12-Oct-05   BTE     Changed reference from gconfig to core in find_revl.
31-Oct-05   BTE     Removed unused code.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.

*/

ob_start(); // avoid inadvertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-cnst.php');
include('../lib/l-rpcs.php');
include('../lib/l-serv.php');
include('../lib/l-jump.php');
include('../lib/l-user.php');
include('../lib/l-head.php');
include('../lib/l-gsql.php');
include('../lib/l-rlib.php');
include('../rpc/inst.php');

define('fake_site', 'The Fake Site');
define('fake_host', 'effluvia');
define('fake_uuid', '00000000-0000-0000-0000-000000000000');
define('fake_user', 'Phillip J. Fry');
define('fake_vers', '1.008.1599.BM');
define('fake_code', 9611778);



// stolen from main/rpc/provis.php

define('constInfoUserName',   'username');
define('constInfoClientTime', 'clienttime');
define('constInfoAction',     'action');
define('constInfoProdName',   'prodname');
define('constInfoExeName',    'exename');
define('constInfoEventType',  'eventtype');
define('constInfoProcessID',  'processid');
define('constInfoProdOwner',  'prodowner');

// stolen from main/rpc/provis.php

define('constProcessCompletion', 0);
define('constProcessCreation',   1);
define('constProcessLife',       2);


function title($act)
{
    switch ($act) {
        case 'evnt':
            return 'ELOG_LogEntry';
        case 'sync':
            return 'VARS_CheckSync (old)';
        case 'snew':
            return 'VARS_CheckSync (new)';
        case 'cnfg':
            return 'VARS_ApplyPackage (old)';
        case 'cnew':
            return 'VARS_ApplyPackage (new)';
        case 'inst':
            return 'INST_SendPatches';
        case 'getw':
            return 'INST_GetWUTimes';
        case 'chck':
            return 'INST_CheckWUTimes';
        case 'sets':
            return 'INST_SetStatus';
        case 'getp':
            return 'INST_GetPatches';
        case 'updt':
            return 'EXEC_QueryUpdate';
        case 'asst':
            return 'ELOG_AssetDataALIST';
        case 'metr':
            return 'PROV_ReportMeter';
        case 'none':
            return 'Nothing to See Here';
        case 'menu':
            return 'Fake Menu';
        default:
            return "Unknown Action ($act)";
    }
}


function redcommand($sql, $db)
{
    $res  = false;
    $test = global_def('test_sql', 0);
    $show = global_def('show_sql', 0);
    $dbug = global_def('debug', 0);

    if ($test) {
        $color = 'blue';
        $res   = false;
    } else {
        $color = 'red';
        $res   = command_time($sql, $time, $db);
    }
    if (($show) || ($dbug)) {
        $msg = str_replace("\n", "<br>\n&nbsp;&nbsp;&nbsp;", $sql);
        if ($time > 0) {
            $secs = microtime_show($time);
            $msg .= ";&nbsp;&nbsp;&nbsp;($secs)";
        }
        if ((!$res) && (!$test)) {
            $error = mysqli_error($db);
            $errno = mysqli_errno($db);
            $msg .= "<br>\nerrno:$errno<br>\n$error";
        }
        echo "<font color=\"$color\"><p>$msg</p></font><br>\n";
    }
    return $res;
}

function value_range($min, $max, $val)
{
    if ($val <= $min) $val = $min;
    if ($max <= $val) $val = $max;
    return $val;
}

function green($msg)
{
    return "<font color=\"green\">$msg</font>";
}

function button($valu)
{
    $type = 'type="submit"';
    $name = 'name="submit"';
    $valu = "value=\"$valu\"";
    return "<input $type $name $valu>";
}

function find_census_name($site, $host, $db)
{
    $qs  = safe_addslashes($site);
    $qh  = safe_addslashes($host);
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census\n"
        . " where host = '$qh'\n"
        . " and site = '$qs'";
    return find_one($sql, $db);
}


function find_revl($site, $host, $db)
{
    $qs  = safe_addslashes($site);
    $qh  = safe_addslashes($host);
    $sql = "select C.site, C.host,\n"
        . " C.uuid, R.* from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Revisions as R\n"
        . " where R.censusid = C.id\n"
        . " and C.host = '$qh'\n"
        . " and C.site = '$qs'";
    return find_one($sql, $db);
}

function hidden($name, $value)
{
    return "<input type=\"hidden\" name=\"$name\" value=\"$value\">\n";
}

function again($env)
{
    $priv = $env['priv'];
    $self = $env['self'];
    $args = $env['args'];
    $href = ($args) ? "$self?$args" : $self;
    $act  = "$self?act";
    $home = '../acct/index.php';

    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link("$act=evnt", 'event');
    $a[] = html_link("$act=sync", 'synch');
    $a[] = html_link("$act=cnfg", 'config');
    //  $a[] = html_link("$act=asst",'asset');
    $a[] = html_link("$act=menu", 'menu');
    $a[] = html_link($home, 'home');
    $a[] = html_link($href, 'again');
    return jumplist($a);
}

function nothing($env, $db)
{
    $msg = "This page requires administrative access.";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br>\n";
}

function unknown($env, $db)
{
    $msg = "unknown action.";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br>\n";
}

function post_rpc()
{
    $rpc = '../rpc/rpc.php';
    return "<form method=\"post\" action=\"$rpc\">\n\n";
}


function form_footer()
{
    echo "\n\n</form>\n\n";
}

function arg($n, $type, $valu)
{
    $tn = sprintf('Type%02d', $n);
    $vn = sprintf('Val%02d', $n);
    $ht = hidden($tn, $type);
    $hv = hidden($vn, $valu);
    return $hv . $ht;
}


function fake_meter($env, $new, $db)
{
    echo again($env);

    $int  = 'PUINT32';
    $str  = 'PSTRING';
    $lst  = 'PALIST';
    if ($new) {
        $proc = 'PROV_LogMeter';
        $nump = 5;
    } else {
        $proc = 'PROV_ReportMeter';
        $nump = 4;
    }
    $site = fake_site;
    $host = fake_host;
    $row  = find_census_name($site, $host, $db);
    if ($row) {
        $uuid = $row['uuid'];
        $code = $row['code'];
    } else {
        $code = fake_code;
        $uuid = fake_uuid;
    }

    $time = time();
    $when = $time - mt_rand(5, 60);
    $pid  = mt_rand(10, 65534);

    $set = array();
    $set[0][constInfoClientTime] = $when;
    $set[0][constInfoEventType] = constProcessCompletion;
    $set[0][constInfoSite] = $site;
    $set[0][constInfoMachine] = $host;
    $set[0][constInfoUUID] = $uuid;
    $set[0][constInfoExeName] = 'Random Executable';
    $set[0][constInfoProcessID] = $pid;
    $set[0][constInfoUserName] = fake_user;
    $set[0][constInfoAction] = 'Fake Action';
    $set[0][constInfoProdOwner] = 'Random Product Owner';
    $set[0][constInfoProdName] = 'Random Product Name';

    $alst = fully_make_alist($set);

    echo post_rpc();
    echo hidden('debug_rpc', 'yes');
    echo hidden('debug_sql', 'yes');

    echo hidden('ProtocolVer', '100');
    echo hidden('ProcName', $proc);
    echo hidden('ProcVer', '100');
    echo hidden('NumParams', $nump);
    echo arg(1, $str, $host);
    echo arg(2, $str, $site);
    echo arg(3, $str, $uuid);
    echo arg(4, $lst, $alst);
    if ($new) {
        echo arg(5, $int, $code);
    }
    echo button($proc);
    form_footer();
    echo again($env);
}

function fake_update($env, $new, $db)
{
    echo again($env);

    $int  = 'PUINT32';
    $str  = 'PSTRING';
    if ($new) {
        $proc = 'EXEC_LogUpdate';
        $nump = 10;
    } else {
        $proc = 'EXEC_QueryUpdate';
        $nump = 8;
    }
    $site = fake_site;
    $host = fake_host;
    $vers = fake_vers;
    $row  = find_census_name($site, $host, $db);
    if ($row) {
        $uuid = $row['uuid'];
        $code = $row['code'];
    } else {
        $code = fake_code;
        $uuid = fake_uuid;
    }

    echo post_rpc();
    echo hidden('debug_rpc', 'yes');
    echo hidden('debug_sql', 'yes');

    echo hidden('ProtocolVer', '100');
    echo hidden('ProcName', $proc);
    echo hidden('ProcVer', '100');
    echo hidden('NumParams', $nump);
    echo arg(1, $str, '');
    echo arg(2, $str, '');
    echo arg(3, $str, '');
    echo arg(4, $str, '');
    echo arg(5, $str, '');
    echo arg(6, $str, $vers);
    echo arg(7, $str, $host);
    echo arg(8, $str, $site);
    if ($new) {
        echo arg(9, $str, $uuid);
        echo arg(10, $int, $code);
    }
    echo button($proc);
    form_footer();
    echo again($env);
}


function fake_asset($env, $new, $db)
{
    echo again($env);

    $int  = 'UINT32';
    $pnt  = 'PUINT32';
    $str  = 'PSTRING';
    $lst  = 'PALIST';
    if ($new) {
        $proc = 'ELOG_AssetLog';
        $nump = 7;
    } else {
        $proc = 'ELOG_AssetDataALIST';
        $nump = 5;
    }
    $site = fake_site;
    $host = fake_host;
    $row  = find_census_name($site, $host, $db);
    $uuid = ($row) ? $row['uuid'] : fake_uuid;
    $code = ($row) ? $row['code'] : fake_code;

    $data[1]['g'] = '';
    $data[1]['d'] = $uuid;
    $data[1]['o'] = 0;
    $data[1]['l'] = 'System UUID';

    $data[2]['g'] = '';
    $data[2]['d'] = fake_host;
    $data[2]['o'] = 0;
    $data[2]['l'] = 'Machine Name';

    $data[3]['g'] = '';
    $data[3]['d'] = fake_user;
    $data[3]['o'] = 0;
    $data[3]['l'] = 'User Name';

    $args = fully_make_alist($data);

    echo post_rpc();
    echo hidden('debug_rpc', 'yes');
    echo hidden('debug_sql', 'yes');

    echo hidden('ProtocolVer', '100');
    echo hidden('ProcName', $proc);
    echo hidden('ProcVer', '100');
    echo hidden('NumParams', $nump);
    echo arg(1, $int, 61);
    echo arg(2, $str, time());
    echo arg(3, $str, $site);
    echo arg(4, $str, $host);
    echo arg(5, $lst, $args);
    if ($new) {
        echo arg(6, $str, $uuid);
        echo arg(7, $pnt, $code);
    }
    echo button($proc);
    form_footer();
    echo again($env);
}


function fake_event($env, $new, $db)
{
    echo again($env);
    $now  = $env['now'];

    $date = date('Y-m-d H:i:s', $now);

    $int  = 'UINT32';
    $pnt  = 'PUINT32';
    $str  = 'PSTRING';
    $site = fake_site;
    $host = fake_host;
    if ($new) {
        $proc = 'ELOG_LogEvent';
        $nump = 24;
        $row  = find_census_name($site, $host, $db);
        if ($row) {
            $uuid = $row['uuid'];
            $code = $row['code'];
        } else {
            $code = fake_code;
            $uuid = fake_uuid;
        }
    } else {
        $proc = 'ELOG_LogEntry';
        $nump = 22;
    }
    echo post_rpc();
    echo hidden('debug_rpc', 'yes');
    echo hidden('debug_sql', 'yes');

    echo hidden('ProtocolVer', '100');
    echo hidden('ProcName', $proc);
    echo hidden('ProcVer', '100');
    echo hidden('NumParams', $nump);
    echo arg(1, $int, 50);
    echo arg(2, $str, $date);
    echo arg(3, $str, $site);
    echo arg(4, $str, $host);
    echo arg(5, $str, fake_user);
    echo arg(6, $str, fake_vers);
    echo arg(7, $int, '3735599');
    echo arg(8, $int, '1');
    echo arg(9, $str, 'Process Creation Detected');
    echo arg(10, $str, '50');
    echo arg(11, $str, 'D:\Program Files\Eps10\bin\epsilon.exe');
    echo arg(12, $str, 'epsilon.exe');
    echo arg(13, $str, '10.0.0.0');
    echo arg(14, $str, '382465');
    echo arg(15, $str, '0');
    echo arg(16, $str, '');
    echo arg(17, $str, 'Monday, January 24, 2000 22:03:34');
    echo arg(18, $str, 'D:\hfn\dev\cust\Debug\Cust.exe');
    echo arg(19, $str, '-wl');
    echo arg(20, $str, '00000264');
    echo arg(21, $str, '');
    echo arg(22, $str, '');
    if ($new) {
        echo arg(23, $pnt, $code);
        echo arg(24, $str, $uuid);
    }
    echo button($proc);
    form_footer();
    echo again($env);
}



function fake_synch($env, $new, $db)
{
    echo again($env);

    $proc = 'VARS_CheckSync';
    $row  = array();
    $hid  = $env['hid'];

    $site = fake_site;
    $host = fake_host;
    $vers = fake_vers;
    $gchk = md5(fake_site);
    $lchk = md5(fake_host);
    $schk = md5(fake_uuid);
    if ($hid) {
        $row = find_machine($hid, $db);
    }
    if ($row) {
        $site = $row['site'];
        $host = $row['host'];
    } else {
        $row  = find_census_name($site, $host, $db);
    }
    $revl = find_revl($site, $host, $db);
    if ($revl) {
        $vers = $revl['vers'];
    }

    if ($row) {
        $uuid = $row['uuid'];
        $code = $row['code'];
    } else {
        $code = fake_code;
        $uuid = fake_uuid;
    }
    $none = array();
    $mach = array();

    $mach[constInfoSite]           = $site;
    $mach[constInfoMachine]        = $host;
    $mach[constInfoUUID]           = $uuid;
    $mach[constInfoVersion]        = $vers;
    $mach[constInfoGlobalChecksum] = $gchk;
    $mach[constInfoLocalChecksum]  = $lchk;
    $mach[constInfoConfigChecksum] = $schk;
    if ($new) {
        $mach[constInfoToken] = $code;
    }

    $rv = fully_make_alist($none);
    $id = fully_make_alist($mach);

    if (($hid) && ($row)) {
        echo "<p><b>$host</b> at <b>$site</b></p>\n";
    }


    echo post_rpc();
    echo hidden('debug_rpc', 'yes');
    echo hidden('debug_sql', 'yes');

    echo hidden('ProtocolVer', '100');
    echo hidden('ProcName', $proc);
    echo hidden('ProcVer', '100');
    echo hidden('NumParams', '2');
    echo arg(1, 'PALIST', $rv);
    echo arg(2, 'PALIST', $id);
    echo button($proc);
    form_footer();
    echo again($env);
}


function fake_config($env, $new, $db)
{
    echo again($env);

    $none = array();
    $mach = array();
    $pack = array();
    $site = fake_site;
    $host = fake_host;
    $vers = fake_vers;
    $row  = find_census_name($site, $host, $db);
    if ($row) {
        $uuid = $row['uuid'];
        $code = $row['code'];
    } else {
        $code = fake_code;
        $uuid = fake_uuid;
    }

    $mach[constInfoSite]    = $site;
    $mach[constInfoUUID]    = $uuid;
    $mach[constInfoMachine] = $host;
    $mach[constInfoVersion] = $vers;
    if ($new) {
        $mach[constInfoToken] = $code;
    }


    /*
        | type
        |   constVblTypeInteger
        |   constVblTypeDateTime
        |   constVblTypeString
        |   constVblTypeBoolean
        |   constVblTypeMailSendList
        |   constVblTypeLogInfoList
        |   constVblTypeAList
        |   constVblTypeSemaphore
        |   constVblTypeQueue
        | stat
        |   constVarConfStateGlobal
        |   constVarConfStateLocal
        |   constVarConfStateLocalOnly
        */

    $proc = 'VARS_ApplyPackage';
    $name = 'S00001UpdateMode';
    $type = constVblTypeBoolean;
    $scop = '1';
    $valu = '1';
    $stat = constVarConfStateGlobal;
    $cvpv = constVarPackageVars;

    $vars[constVarPackageGlobalRev] = 2;
    $vars[constVarPackageLocalRev]  = 2;
    $vars[constVarPackageStateRev]  = 1;
    $vars[constVarPackageGlobal]    = $valu;
    $vars[constVarPackageState]     = $stat;
    $vars[constVarPackageLocal]     = $valu;
    $vars[constVarPackageType]      = $type;
    $pack[$cvpv][$scop][$name]      = $vars;

    $scop = '241';
    $name = 'FirstName';
    $type = constVblTypeString;
    $stat = constVarConfStateLocalOnly;
    $valu = 'Phillip';

    unset($vars[constVarPackageGlobal]);

    $vars[constVarPackageState]  = $stat;
    $vars[constVarPackageLocal]  = $valu;
    $vars[constVarPackageType]   = $type;
    $pack[$cvpv][$scop][$name]   = $vars;

    $name = 'LastName';
    $valu = 'Fry';

    $vars[constVarPackageLocal]  = $valu;
    $pack[$cvpv][$scop][$name]   = $vars;

    $rv = fully_make_alist($none);
    $dm = fully_make_alist($none);
    $id = fully_make_alist($mach);
    $pv = fully_make_alist($pack);

    echo post_rpc();
    echo hidden('debug_rpc', 'yes');
    echo hidden('debug_sql', 'yes');

    echo hidden('ProtocolVer', '100');
    echo hidden('ProcName', $proc);
    echo hidden('ProcVer', '100');
    echo hidden('NumParams', '4');
    echo arg(1, 'PPALIST', $rv);
    echo arg(2, 'PPALIST', $dm);
    echo arg(3, 'PALIST', $id);
    echo arg(4, 'PALIST', $pv);
    echo button($proc);
    form_footer();
    echo again($env);
}


function aa(&$arr, $key, $val)
{
    $arr[$key] = strval($val);
}



function fake_upload_patch($env, $new, $db)
{
    echo again($env);
    $now  = $env['now'];

    if ($new) {
        $proc = 'INST_UploadPatch';
        $nump = 5;
    } else {
        $proc = 'INST_SendPatches';
        $nump = 4;
    }

    $site = fake_site;
    $host = fake_host;
    $row  = find_census_name($site, $host, $db);
    if ($row) {
        $uuid = $row['uuid'];
        $code = $row['code'];
    } else {
        $uuid = fake_uuid;
        $code = fake_code;
    }



    $date = date('Y-m-d H:i:s', $now);

    $int  = 'UINT32';
    $str  = 'PSTRING';
    $lst  = 'PALIST';
    $pnt  = 'PUINT32';


    /* the next and final argument is the alist of unamed alists,
            each unamed alist is a patch with several named items */

    $a = array();

    $when = $now - mt_rand(1, 3600);
    $name = 'Security Update for MS Jet 4.0 (x86 en 5.1.2195 SP3.0)';

    aa($a, constWUNewPatchItemID, 'win2k_patch_test_NEW89450');
    aa($a, constWUNewPatchName, $name);
    aa($a, constWUNewPatchDate, '0');
    aa($a, constWUNewPatchSize, '384293');
    aa($a, constWUNewPatchDesc, 'Test patch description.');
    aa($a, constWUNewPatchParams, '-q -Za');
    aa($a, constWUNewPatchClient, 'c:\Program Files\WU\foobar.exe');
    aa($a, constWUNewPatchServer, 'http://mayo/runme.exe');
    aa($a, constWUNewPatchCRC, 'A398C098329');
    aa($a, constWUNewPatchComponent, 'Windows 2000');
    aa($a, constWUNewPatchPlatform, 'ver_platform_test');
    aa($a, constWUNewPatchProcessor, 'x86');
    aa($a, constWUNewPatchOSMajor, '9879');
    aa($a, constWUNewPatchOSMinor, '3487');
    aa($a, constWUNewPatchOSBuild, '78556');
    aa($a, constWUNewPatchSPMajor, '56');
    aa($a, constWUNewPatchSPMinor, '7');
    aa($a, constWUNewPatchPriority, '9');
    aa($a, constWUNewPatchPrioH, '99');
    aa($a, constWUNewPatchEULA, 'http://wu.microsoft.com/eula.htm');
    aa($a, constWUNewPatchClientTime, $when);
    aa($a, constWUNewPatchStatus, constPatchStatusDetected);
    aa($a, constWUNewPatchMSName, 'KB834678_SP4_W2K');
    aa($a, constWUNewPatchTitle, 'Security Update for MS Jet 4.0');
    aa($a, constWUNewPatchLocale, 'en');

    $patches[] = $a;

    $when = $now - mt_rand(1, 3600);
    $name = 'Security Update for Microsoft'
        . ' Explorer (x86 en 4.1.3424 SP0.0)';

    aa($a, constWUNewPatchItemID, 'win2k_patch_test_45343');
    aa($a, constWUNewPatchName, $name);
    aa($a, constWUNewPatchDate, '0');
    aa($a, constWUNewPatchSize, '55632');
    aa($a, constWUNewPatchDesc, 'Test patch description.');
    aa($a, constWUNewPatchParams, '-q -Za');
    aa($a, constWUNewPatchClient, 'c:\Program Files\WU\45343.exe');
    aa($a, constWUNewPatchServer, 'http://wu.microsoft.com/foo.exe');
    aa($a, constWUNewPatchCRC, 'A398C098329');
    aa($a, constWUNewPatchComponent, 'Windows 2000');
    aa($a, constWUNewPatchPlatform, 'ver_platform_test');
    aa($a, constWUNewPatchProcessor, 'x86');
    aa($a, constWUNewPatchOSMajor, '9879');
    aa($a, constWUNewPatchOSMinor, '3487');
    aa($a, constWUNewPatchOSBuild, '78556');
    aa($a, constWUNewPatchSPMajor, '56');
    aa($a, constWUNewPatchSPMinor, '7');
    aa($a, constWUNewPatchPriority, '9');
    aa($a, constWUNewPatchPrioH, '99');
    aa($a, constWUNewPatchEULA, 'http://wu.microsoft.com/eula.htm');
    aa($a, constWUNewPatchClientTime, '3947');
    aa($a, constWUNewPatchStatus, constPatchStatusDetected);
    aa($a, constWUNewPatchMSName, 'KB834789_SP4_W2K');
    aa($a, constWUNewPatchTitle, 'Security Update for Microsoft Explorer');
    aa($a, constWUNewPatchLocale, 'en');

    $patches[] = $a;

    $when = $now - mt_rand(1, 3600);
    $name = 'Security Update for Microsoft IE'
        . ' (x86 en 5.1.2600 SP4.0)';

    aa($a, constWUNewPatchItemID, 'win2k_patch_test_99999');
    aa($a, constWUNewPatchName, $name);
    aa($a, constWUNewPatchDate, '0');
    aa($a, constWUNewPatchSize, '384243');
    aa($a, constWUNewPatchDesc, 'Test patch description.');
    aa($a, constWUNewPatchParams, '-q -Za');
    aa($a, constWUNewPatchClient, 'c:\Program Files\WU\999.exe');
    aa($a, constWUNewPatchServer, 'http://wu.microsoft.com/bar.exe');
    aa($a, constWUNewPatchCRC, 'A398C098329');
    aa($a, constWUNewPatchComponent, 'Windows 2000');
    aa($a, constWUNewPatchPlatform, 'ver_platform_test');
    aa($a, constWUNewPatchProcessor, 'x86');
    aa($a, constWUNewPatchOSMajor, '9879');
    aa($a, constWUNewPatchOSMinor, '3487');
    aa($a, constWUNewPatchOSBuild, '78556');
    aa($a, constWUNewPatchSPMajor, '56');
    aa($a, constWUNewPatchSPMinor, '7');
    aa($a, constWUNewPatchPriority, '9');
    aa($a, constWUNewPatchPrioH, '99');
    aa($a, constWUNewPatchEULA, 'http://wu.microsoft.com/eula.htm');
    aa($a, constWUNewPatchClientTime, $when);
    aa($a, constWUNewPatchStatus, constPatchStatusDetected);
    aa($a, constWUNewPatchMSName, 'KB834582_SP4_W2K');
    aa($a, constWUNewPatchTitle, 'Security Update for Microsoft IE');
    aa($a, constWUNewPatchLocale, 'en');

    $patches[] = $a;

    $alst = fully_make_alist($patches);
    echo post_rpc();
    echo hidden('debug_rpc', 'yes');
    echo hidden('debug_sql', 'yes');

    echo hidden('ProtocolVer', '100');
    echo hidden('ProcName', $proc);
    echo hidden('ProcVer', '100');
    echo hidden('NumParams', $nump);

    echo arg(1, $str, $host);
    echo arg(2, $str, $site);
    echo arg(3, $str, $uuid);
    echo arg(4, $lst, $alst);
    if ($new) {
        echo arg(5, $pnt, $code);
    }
    echo button($proc);

    form_footer();
    echo again($env);
}

function fake_inst_getwuconfig($env, $db)
{
    echo again($env);
    $now  = $env['now'];

    $proc = 'INST_GetWUConfig';

    $date = date('Y-m-d H:i:s', $now);

    $int  = 'UINT32';
    $str  = 'PSTRING';
    $alst = 'PPALIST';
    $fake_array = array();
    $fake_alst = fully_make_alist($fake_array);

    echo post_rpc();
    echo hidden('debug_rpc', 'yes');
    echo hidden('debug_sql', 'yes');

    echo hidden('ProtocolVer', '100');
    echo hidden('ProcName', $proc);
    echo hidden('ProcVer', '100');
    echo hidden('NumParams', '4');

    echo arg(1, $alst, $fake_alst);
    echo arg(2, $str, fake_host);
    echo arg(3, $str, fake_site);
    echo arg(4, $str, fake_uuid);

    echo button($proc);

    form_footer();
    echo again($env);
}

function fake_inst_checkwutimes($env, $db)
{
    echo again($env);
    $now  = $env['now'];

    $proc = 'INST_CheckWUTimes';

    $date = date('Y-m-d H:i:s', $now);

    $str  = 'PSTRING';
    $alst = 'PPALIST';
    $fake_array = array();
    $fake_alst = fully_make_alist($fake_array);

    echo post_rpc();
    echo hidden('debug_rpc', 'yes');
    echo hidden('debug_sql', 'yes');

    echo hidden('ProtocolVer', '100');
    echo hidden('ProcName', $proc);
    echo hidden('ProcVer', '100');
    echo hidden('NumParams', '4');

    echo arg(1, $alst, $fake_alst);
    echo arg(2, $str, fake_host);
    echo arg(3, $str, fake_site);
    echo arg(4, $str, fake_uuid);

    echo button($proc);

    form_footer();
    echo again($env);
}

function fake_inst_set_status($env, $db)
{
    echo again($env);
    $now  = $env['now'];

    $proc = 'INST_SetStatus';

    $date = date('Y-m-d H:i:s', $now);

    $int  = 'UINT32';
    $str  = 'PSTRING';

    echo post_rpc();
    echo hidden('debug_rpc', 'yes');
    echo hidden('debug_sql', 'yes');

    echo hidden('ProtocolVer', '100');
    echo hidden('ProcName', $proc);
    echo hidden('ProcVer', '100');
    echo hidden('NumParams', '4');

    echo arg(1, $str, fake_host);
    echo arg(2, $str, fake_site);
    echo arg(3, $str, fake_uuid);

    /* the next and final argument is the alist of unamed alists,
            each unamed alist is a patch with several named items */

    $thisPatch = array();

    $thisPatch[constWUStatPatchItemID] = 'win2k_patch_test_8945089';
    $thisPatch[constWUStatPatchStatus] = 12;
    $thisPatch[constWUStatPatchDate] = '1248034';
    $thisPatch[constWUStatPatchLastError] = 'Test patch status error.';
    $thisPatch[constWUStatPatchLastErrorDate] = '6784948';

    $patches[] = $thisPatch;
    $fake_patches = fully_make_alist($patches);
    echo arg(4, $str, $fake_patches);

    echo button($proc);

    form_footer();
    echo again($env);
}


function find_machine($hid, $db)
{
    $row = array();
    if ($hid > 0) {
        $sql = "select * from Census\n"
            . " where id = $hid";
        $row = find_one($sql, $db);
    }
    return $row;
}



function fake_inst_getpatches(&$env, $db)
{
    echo again($env);
    $now  = $env['now'];
    $hid  = $env['hid'];
    $proc = 'INST_GetPatches';
    $text = $proc;
    $host = fake_host;
    $site = fake_site;
    $uuid = fake_uuid;
    $row  = find_machine($hid, $db);
    if ($row) {
        $host = $row['host'];
        $site = $row['site'];
        $uuid = $row['uuid'];
        $text = "$proc ($host)";
    }


    $date = date('Y-m-d H:i:s', $now);

    $int  = 'UINT32';
    $str  = 'PSTRING';
    $alst = 'PPALIST';
    $fake_array = array();
    $fake_alst = fully_make_alist($fake_array);

    echo post_rpc();
    echo hidden('debug_rpc', 'yes');
    echo hidden('debug_sql', 'yes');

    echo hidden('ProtocolVer', '100');
    echo hidden('ProcName', $proc);
    echo hidden('ProcVer', '100');
    echo hidden('NumParams', '6');

    $fake_olddt = 0;
    $fake_newdt = 0;

    echo arg(1, $alst, $fake_alst);
    echo arg(2, $int, $fake_newdt);
    echo arg(3, $str, $host);
    echo arg(4, $str, $site);
    echo arg(5, $str, $uuid);
    echo arg(6, $int, $fake_olddt);

    echo button($text);

    form_footer();
    echo again($env);
}

function command_list(&$act, &$txt)
{
    echo "<p>What do you want to do?</p>\n\n\n<ol>\n";

    reset($txt);
    foreach ($txt as $key => $doc) {
        $cmd = html_link($act[$key], $doc);
        echo "<li>$cmd</li>\n";
    }
    echo "</ol>\n";
}

function gpatch_machine(&$env, $db)
{
    echo again($env);
    $cmd = $env['self'] . '?act';
    $sql = "select * from Census\n"
        . " where uuid != ''\n"
        . " order by host";
    $set = find_many($sql, $db);
    $act = array();
    $txt = array();
    $txt[] = 'return';
    $act[] = "$cmd=menu";
    $txt[] = '(A Fake Machine)';
    $act[] = "$cmd=getp&hid=-1";
    reset($set);
    foreach ($set as $key => $row) {
        $id   = $row['id'];
        $site = $row['site'];
        $host = $row['host'];
        $text = "$host at $site";
        $args = "$cmd=getp&hid=$id";
        $txt[] = $text;
        $act[] = $args;
    }
    command_list($act, $txt);
    echo again($env);
}

function getpatches(&$env, $db)
{
    $hid = $env['hid'];
    if ($hid)
        fake_inst_getpatches($env, $db);
    else
        gpatch_machine($env, $db);
}

function fake_menu($env, $db)
{
    echo again($env);
    $self = $env['self'];
    $cmd  = "$self?act";

    $act = array();
    $txt = array();

    $act[] = $self;
    $txt[] = 'Home Page.';

    $act[] = "$cmd=menu";
    $txt[] = 'This Page.';

    $act[] = "$cmd=evnt";
    $txt[] = 'ELOG_LogEntry';

    $act[] = "$cmd=sync";
    $txt[] = 'VARS_CheckSync (old)';

    $act[] = "$cmd=snew";
    $txt[] = 'VARS_CheckSync (new)';

    $act[] = "$cmd=cnfg";
    $txt[] = 'VARS_ApplyPackage (old)';

    $act[] = "$cmd=cnew";
    $txt[] = 'VARS_ApplyPackage (new)';

    $act[] = "$cmd=asst";
    $txt[] = 'ELOG_AssetDataALIST';

    $act[] = "$cmd=metr";
    $txt[] = 'PROV_ReportMeter';

    $act[] = "$cmd=inst";
    $txt[] = 'INST_SendPatches';

    $act[] = "$cmd=getw";
    $txt[] = 'INST_GetWUTimes';

    $act[] = "$cmd=updt";
    $txt[] = 'EXEC_QueryUpdate';

    $act[] = "$cmd=sets";
    $txt[] = 'INST_SetStatus';

    $act[] = "$cmd=chck";
    $txt[] = 'INST_CheckWUTimes';

    $act[] = "$cmd=getp";
    $txt[] = 'INST_GetPatches';

    command_list($act, $txt);
    echo again($env);
}


/*
    |  Main program
    */

$db = db_connect();
$auth = process_login($db);
$comp = component_installed();

$now   = time();
$date  = datestring($now);
$user  = user_data($auth, $db);

$apriv = ($user['priv_admin']) ? 1 : 0;
$dpriv = ($user['priv_debug']) ? 1 : 0;
$dbg   = get_integer('debug', 0);
$adm   = get_integer('admin', 1);
$act   = get_string('act', 'evnt');
$admin = ($apriv) ? $adm : 0;
$debug = ($dpriv) ? $dbg : 0;
$title = title($act);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $auth, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users
if ($debug) echo "<h2>$date</h2>";

if ((!$apriv) || (!$dpriv)) {
    $act = 'none';
}

$env  = array();
$env['pid']   = get_integer('pid', 0);
$env['now']   = $now;
$env['act']   = $act;
$env['hid']   = get_integer('hid', 0);
$env['self']  = server_var('PHP_SELF');
$env['args']  = server_var('QUERY_STRING');
$env['auth']  = $auth;
$env['priv']  = $dpriv;
$env['debug'] = $debug;

switch ($act) {
    case 'evnt':
        fake_event($env, 0, $db);
        break;
    case 'sync':
        fake_synch($env, 0, $db);
        break;
    case 'snew':
        fake_synch($env, 1, $db);
        break;
    case 'cnfg':
        fake_config($env, 0, $db);
        break;
    case 'cnew':
        fake_config($env, 1, $db);
        break;
    case 'asst':
        fake_asset($env, 0, $db);
        break;
    case 'updt':
        fake_update($env, 0, $db);
        break;
    case 'none':
        nothing($env, $db);
        break;
    case 'inst':
        fake_upload_patch($env, 0, $db);
        break;
    case 'getw':
        fake_inst_getwuconfig($env, $db);
        break;
    case 'chck':
        fake_inst_checkwutimes($env, $db);
        break;
    case 'sets':
        fake_inst_set_status($env, $db);
        break;
    case 'metr':
        fake_meter($env, 0, $db);
        break;
    case 'getp':
        getpatches($env, $db);
        break;
    case 'menu':
        fake_menu($env, $db);
        break;
    default:
        unknown($env, $db);
        break;
}

echo head_standard_html_footer($auth, $db);
