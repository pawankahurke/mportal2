<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 1-Apr-05   AAM     Created.
 1-Apr-05   EWB     Small revisions.
14-Dec-05   AAM     Added CPU and wall clock timing to counters, moved copied
                    code into l-pcnt.php.  Added rate to counters.
14-Dec-05   AAM     Fixed a silly display problem.
22-May-06   AAM     Added display of byte counts to performance counters.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.
26-Jan-07   BTE     Bug 4015: Add TX performance counters.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-user.php');
include('../lib/l-jump.php');
include('../lib/l-tabs.php');
include('../lib/l-rlib.php');
include('../lib/l-head.php');
include('../lib/l-pcnt.php');


function again($env)
{
    $self = $env['self'];
    $pdbg = $env['pdbg'];
    $act  = "$self?act";
    $dsp  = $self;
    $clr  = "$act=clear";
    $enb  = "$act=enable";
    $dis  = "$act=disable";
    $del  = "$act=delete";
    $sim  = "$act=sim";

    $a   = array();
    $a[] = html_link($dsp, 'display');
    $a[] = html_link($clr, 'clear');
    $a[] = html_link($enb, 'enable');
    $a[] = html_link($dis, 'disable');
    $a[] = html_link($del, 'delete');
    $a[] = html_link($sim, 'simulate');
    if ($pdbg) {
        $args = $env['args'];
        $href = ($args) ? "$self?$args" : $self;
        $a[]  = html_link('index.php', 'home');
        $a[]  = html_link($href, 'again');
    }
    return jumplist($a);
}


function para($text)
{
    $msg = fontspeak($text);
    return "<p>$msg</p>\n";
}

function show_perf($env)
{
    return null;
}



function display_perf($env)
{
    echo again($env);
    show_perf($env);
    echo again($env);
}


function simulate_perf($env)
{
    echo again($env);

    $proc = 'SIM_ProcName';
    start_perf_count(
        $proc,
        $wSec,
        $wUsec,
        $uSec,
        $uUsec,
        $sSec,
        $sUsec,
        $enabled
    );
    finish_perf_count(
        $proc,
        $wSec,
        $wUsec,
        $uSec,
        $uUsec,
        $sSec,
        $sUsec,
        $enabled
    );
    echo para('Simulated performance counter incremented.');
    show_perf($env);
    echo again($env);
}



function clear_perf($env)
{
    return null;
    // echo again($env);

    // $shm = shm_attach(constShmKey);
    // $sem = sem_get(constSemKey);

    // if (sem_acquire($sem)) {
    //     shm_put_var($shm, constShmData, array());
    //     shm_put_var($shm, constShmTime, time());
    //     sem_release($sem);
    // }
    // shm_detach($shm);

    // echo para('Performance counters cleared.');

    // show_perf($env);
    // echo again($env);
}



function test_perf($env)
{
    return null;
    // echo again($env);

    // $shm = shm_attach(constShmKey);
    // $sem = sem_get(constSemKey);

    // if (sem_acquire($sem)) {
    //     $gork =null; //  @shm_get_var($shm, 72);
    //     sem_release($sem);
    // }
    // shm_detach($shm);

    // if (!isset($gork)) {
    //     para('gork not set');
    // }
    // if (!$gork) {
    //     para('gork is false');
    // }

    // echo again($env);
}



function enable_perf($env)
{
    return null;
    // echo again($env);

    // $shm = shm_attach(constShmKey);
    // $sem = sem_get(constSemKey);

    // if (sem_acquire($sem)) {
    //     shm_put_var($shm, constShmEnab, 1);
    //     shm_put_var($shm, constShmData, array());
    //     shm_put_var($shm, constShmTime, time());
    //     sem_release($sem);
    // }
    // shm_detach($shm);

    // echo para("Performance counters enabled.");

    // echo again($env);
}



function disable_perf($env)
{
    return null;
    // echo again($env);
    // $shm = shm_attach(constShmKey);
    // $sem = sem_get(constSemKey);

    // if (sem_acquire($sem)) {
    //     shm_put_var($shm, constShmEnab, 0);
    //     sem_release($sem);
    // }
    // shm_detach($shm);

    // echo para('Performance counters disabled.');

    // echo again($env);
}



function delete_perf($env)
{
    return null;
    // echo again($env);
    // $shm = shm_attach(constShmKey);
    // $sem = sem_get(constSemKey);

    // if (sem_acquire($sem)) {
    //     shm_remove($shm);
    //     sem_release($sem);
    // }

    // echo para('Performance counters completely removed.');

    // echo again($env);
}



function nothing($env, $db)
{
    echo again($env);
    $msg  = 'You need administrative access in order to use this';
    $msg .= ' page.  Authorization denied.';
    echo para($msg);

    echo again($env);
}



function unknown_option($env)
{
    echo again($env);
    $acts = $env['acts'];
    debug_note("unknown_option($acts)");
    echo again($env);
}


/*
    |  Main program
    */

$db = db_connect();
$auth = process_login($db);
$comp = component_installed();

$user   = user_data($auth, $db);
$padm   = @($user['priv_admin']) ? 1 : 0;
$pdbg   = @($user['priv_debug']) ? 1 : 0;

$dbg    = get_integer('debug', 1);
$priv   = get_integer('priv', 1);
$act    = get_string('act', 'display');

$admin  = ($padm) ? $priv : 0;
$debug  = ($pdbg) ? $dbg  : 0;
$title  = 'Server Performance';

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $auth, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$env = array();
$env['acts'] = $act;
$env['pdbg'] = $pdbg;
$env['padm'] = $pdbg;
$env['self'] = server_var('PHP_SELF');
$env['args'] = server_var('QUERY_STRING');

if (!$admin) {
    $act = 'none';
}
switch ($act) {
    case 'display':
        display_perf($env);
        break;
    case 'clear':
        clear_perf($env);
        break;
    case 'enable':
        enable_perf($env);
        break;
    case 'disable':
        disable_perf($env);
        break;
    case 'delete':
        delete_perf($env);
        break;
    case 'test':
        test_perf($env);
        break;
    case 'none':
        nothing($env);
        break;
    case 'sim':
        simulate_perf($env);
        break;
    default:
        unknown_option($env);
        break;
}
echo head_standard_html_footer($auth, $db);
