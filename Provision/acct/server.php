<?php

/*
Revision history:

Date        Who     What
----        ---     ----
31-Jan-03   EWB     Created.
 3-Feb-03   EWB     Added purge_days
11-Feb-03   EWB     Uses sandbox database.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
17-Mar-03   EWB     user_password default to '0'.
18-Mar-03   EWB     user change sets option modified flag.
19-Mar-03   NL      Move $debug initialization and surrounding lines above ob_ lines
 4-Apr-03   EWB     created census_days
22-Apr-03   EWB     Improved display of html values.
28-May-03   EWB     Added 'disable_cache'
10-Jun-03   EWB     Added 'cron_bias', 'slave_database'
11-Jun-03   EWB     Added 'slave_enable', 'slave_server
13-Jun-03   EWB     census_days, purge_days both default to 90.
18-Jun-03   EWB     slave_user, slave_cron
19-Jun-03   EWB     slow_query_asset, slow_query_report,
                    slow_query_notify, slow_query_event
18-Jul-03   EWB     file_expires_days
31-Jul-03   EWB     jumptable options
 5-Sep-03   EWB     Created config_days, asset_days, update_days.
 8-Sep-03   EWB     Changed default config & update expires to 40 days.
22-Sep-03   EWB     jpeg_quality is 95 by default (0..100)
30-Oct-03   EWB     max_asset_logs
10-Dec-03   AAM     Added max_config_logs setting, 5 by default.
 9-Jan-04   EWB     Server Name.
 9-Feb-04   EWB     meter_days, audit_days.
16-Feb-04   EWB     server_name, audit_page variable.
16-Feb-04   EWB     reduced the amount of vertical scrolling in the display.
18-Mar-04   EWB     meter_report_sender
30-Mar-04   EWB     update_cmdline
 8-Apr-04   EWB     optimize_events, max_report_retries, report_timeout
12-Apr-04   EWB     notify_timeout
16-Apr-04   EWB     home link always works.
24-May-04   EWB     added config_search, enabled by default.
 2-Jun-04   EWB     added override_sites, disabled by default.
11-Jun-04   EWB     honor the editable flag.
27-Oct-04   EWB     max_report_details.
18-Dec-04   EWB     max_report_retries -> report_max_retries
18-Dec-04   EWB     max_report_details -> report_max_details
 2-Mar-05   EWB     asset_timeout 
03-Oct-05   AAM     Added asset_debug_site and asset_debug_machine to address
                    bug 2883.
05-Oct-05   BJS     added ticketuser, ticketpassword, moved Action column.
13-Oct-05   BJS     Checks for $custom and acts on the server name text displayed
                    to the user.
09-Nov-05   AAM     Added config_debug_site and config_debug_machine to enable
                    selective debugging of checksum problems.
24-Jan-06   AAM     Bug 3072: change memory_limit setting to use max_php_mem_mb.
                    (Note that this change was actually moved from 4.2 to 4.3
                    on 06-Mar-06.)
18-Mar-06   AAM     Bug 3214: default override_sites=1, config_search=0.
19-Mar-06   AAM     Bug 3214, continued: put default config_search back to 1.
19-Mar-06   AAM     Bug 3214, still more: default config_search=0.
07-Jul-06   BTE     Bug 3525: Add search_config timeout option.
17-Aug-06   BTE     Bug 3616: Setup query interval for event reports.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.
17-Oct-06   RWM     Bug 3745: Added perf_event_days.
24-Nov-06   AAM     Bug 3896: added banned_vars.  The way this works is that it
                    is a colon-separated list of variable names that will not be
                    added to InvalidVars.  So, you set up the illegal variable
                    names here and also in the "banned variables" in Scrip 43.
                    Then, however, you still have to use mysql to delete the
                    variables from InvalidVars on the server the first time.
09-May-07   BTE     Added comment.
12-Oct-07   WOH     Added PHP_HTML_SetServerOption(CUR, $name, $valu) to update
                    config options in shared memory.
06-Nov-07   WOH     Added sync_max_delay.  Changed a few other names.
19-Feb-08   BTE     Bug 4413: Move check for second database while event
                    logging into shared memory.
07-Apr-15   BTE     Added cdn_profile_prefix.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-user.php');
include('../lib/l-jump.php');
include('../lib/l-base.php');
include('../lib/l-tabs.php');
include('../lib/l-form.php');
include('../lib/l-gsql.php');
include('../lib/l-head.php');
include('../lib/l-errs.php');
include('../lib/l-cnst.php');


$onames = array(
    'company_name',
    'logo_file',
    'logo_x',
    'logo_y',
    'port',
    'ssl',
    'event_report_sender',
    'meter_report_sender',
    'asset_report_sender',
    'event_notify_sender',
    'update_cmdline',
    'purge_days',
    'perf_event_days',
    'support_email',
    'footer_left',
    'footer_right',
    'event_code',
    'asset_code',
    'asset_debug_site',
    'asset_debug_machine',
    'ticket_user',
    'ticket_password',
    'config_debug_site',
    'config_debug_machine',
    'banned_vars'
);


function again($env)
{
    $self = $env['self'];
    $pdbg = $env['pdbg'];
    $add  = "$self?action=define";

    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link($add, 'add');
    $a[] = html_link('#options', 'options');
    if ($pdbg) {
        $args = $env['args'];
        $href = ($args) ? "$self?$args" : $self;
        $a[]  = html_link('index.php', 'home');
        $a[]  = html_link($href, 'again');
    }
    return jumplist($a);
}


function server_dset($name, $value, $db)
{
    $old = server_opt($name, $db);
    if ($old == '') {
        server_set($name, $value, $db);
    }
}



function create_empty($onames, $db)
{
    reset($onames);
    foreach ($onames as $key => $name) {
        $valu = server_opt($name, $db);
        if ($valu == '') {
            server_set($name, $valu, $db);
        }
    }
}


/*
    |  purge_days: event purge days
    |  perf_event_days: event purge days for performance Scrips
    |  census_days: census purge days
    |  asset_days: asset purge days
    |  update_days: update purge days
    |  config_days: config purge days
    |  meter_days: provision meter purge days.
    |  audit_days: provision audit purge days
    |  audit_page: provision audit page size.
    |  jpeg_quality: integer 0 worst to 100 best.
    |  max_asset_logs: max simultaneous asset logs.
    |  max_config_logs: max simultaneous config logs.
    |  report_max_retries: maximum number of retries to run report
    |  report_max_details: maximum number of details in report.
    |  optimize_events: optimize events table after purge.
    |  override_sites: controls default precedence of new group categories
    |  optimize_secs: postpone for optimize, if non-zero.
    |  config_search: search for machines needing update.
    */


function server_default($db)
{
    /*
        |  These are time limits, measured in days
        */

    server_dset('purge_days',        '90', $db);
    server_dset('perf_event_days',    '3', $db);
    server_dset('census_days',      '100', $db);
    server_dset('asset_days',       '120', $db);
    server_dset('config_days',       '40', $db);
    server_dset('update_days',       '40', $db);
    server_dset('meter_days',        '60', $db);
    server_dset('audit_days',        '60', $db);
    server_dset('audit_page',        '50', $db);
    server_dset('file_expire_days', '120', $db);

    /*
        |  These are time limits, measured in seconds
        |  The mysql timeouts are floating point.
        */

    server_dset('cron_bias',         '120',   $db);
    server_dset('report_timeout',    '7200',  $db);
    server_dset('asset_timeout',     '7200',  $db);
    server_dset('notify_timeout',    '1800',  $db);
    server_dset('optimize_secs',     '1200',  $db);
    server_dset('slow_query_asset',  '20.000', $db);
    server_dset('slow_query_event',  '20.000', $db);
    server_dset('slow_query_report', '20.000', $db);
    server_dset('slow_query_notify', '20.000', $db);
    server_dset('report_qinterval',  '86400', $db);

    /*
        |  These are expected to be boolean, i.e. 0 or 1.
        */

    server_dset('ssl',              '1', $db);
    server_dset('slave_user',       '0', $db);
    server_dset('slave_cron',       '0', $db);
    server_dset('slave_enable',     '0', $db);
    server_dset('config_search',    '0', $db);
    server_dset('config_schtimeout', '10800', $db);
    server_dset('user_password',    '0', $db);
    server_dset('disable_cache',    '0', $db);
    server_dset('override_sites',   '1', $db);
    server_dset('optimize_events',  '1', $db);

    server_dset('jpeg_quality',        '95', $db);
    server_dset('max_asset_logs',       '2', $db);
    server_dset('sync_max_config',      '20', $db);
    server_dset('sync_max_service',     '5', $db);
    server_dset('sync_max_delay',       '3600', $db);
    server_dset('sync_max_timeout',     '300', $db);
    server_dset('report_max_retries',   '3', $db);
    server_dset('report_max_details', '80000', $db);
    $opt = server_opt('ssl', $db);
    switch ($opt) {
        case '0':
            server_dset('port', '80', $db);
            break;
        case '1':
            server_dset('port', '443', $db);
            break;
        default:
            server_dset('ssl', '1', $db);
            server_dset('port', '443', $db);
    }
    $server = server_name($db);
    server_dset('server_name', $server, $db);
    server_dset('slave_server', "$server:43306", $db);
    server_dset('event_report_sender', "report@$server", $db);
    server_dset('meter_report_sender', "meter@$server", $db);
    server_dset('event_notify_sender', "notify@$server", $db);
    server_dset('asset_report_sender', "assets@$server", $db);
    server_dset('min_free_space', '15%', $db);

    /* Autotask Ticket Settings */
    server_dset('ticket_user',    '', $db);
    server_dset('ticket_password', '', $db);

    /* PHP memory configuration */
    server_dset('max_php_mem_mb', '256', $db);

    /* Report CSS hyperlink is located in update.php since it is critical
            for reports (rept_css) */

    /* Content Delivery Network default */
    server_dset('cdn_profile_prefix', 'http://' . $server . '/cdn/', $db);
}



function one_option($id, $db)
{
    $sql = "select * from Options\n"
        . " where id = $id\n"
        . " and editable = 1";
    return find_one($sql, $db);
}


function define_option($env, $db)
{
    debug_note("define_option(db)");
    $self = $env['self'];
    echo again($env);
    echo post_self('myform');
    echo hidden('action', 'create');
    echo table_header();
    echo '<tr><th colspan="2">Add a Server Option</th></tr>';
    $name = "<input type='text' name='name' maxlength='20' size='60'>\n";
    $data = array("<b>Name</b>", $name);
    echo table_data($data, 0);
    $valu = '<textarea wrap="virtual" rows="5" cols="60" name="value"></textarea>';
    $data = array("<b>Value</b>", $valu);
    echo table_data($data, 0);
    $submit = '<input type="submit" value="submit">';
    $reset  = '<input type="reset" value="reset">';
    $data   = array($submit, $reset);
    echo table_data($data, 0);
    echo table_footer();
    echo form_footer();
    echo again($env);
}


function display($valu)
{
    if ($valu == '') {
        $res = '<br>';
    } else {
        $res = htmlspecialchars($valu);
        $res = nl2br($res);
    }
    return $res;
}

function fulltime($time)
{
    return ($time) ? date('m/d/y H:i:s', $time) : '(never)';
}


function display_options($env, $db)
{
    $custom_list = build_custom_list('custom');

    $pdbg = $env['pdbg'];
    $self = $env['self'];
    $sql  = "select * from Options\n"
        . " where editable = 1\n"
        . " order by name";
    $set  = find_many($sql, $db);
    if ($set) {
        echo mark('options');
        echo again($env);

        $args = explode('|', 'Action|Name|Value|Date');
        $cols = safe_count($args);
        $rows = safe_count($set);
        $text = "Server Options &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $ax   = array();
            $id   = $row['id'];
            $name = customize_name($row['name'], $custom_list, '<b>', '</b>');
            $valu = $row['value'];
            $when = $row['modified'];
            $href = "$self?id=$id&action";
            $ax[] = html_link("$href=edit", '[edit]');
            $ax[] = html_link("$href=clear", '[clear]');
            $ax[] = html_link("$href=delete", '[delete]');
            $acts = join(' &nbsp ', $ax);
            $disp = display($valu);
            $date = fulltime($when);
            $args = array($acts, $name, $disp, $date);
            echo table_data($args, 0);
        }
        echo table_footer();
        echo again($env);
    } else {
        define_option($env, $db);
    }
}


function clear_option($env, $db)
{
    $id = $env['id'];
    debug_note("clear_option($id,db)");
    $row = one_option($id, $db);
    if ($row) {
        $now  = time();
        $name = $row['name'];
        if ($name == constOptionEventCodeStr) {
            $err = PHP_CORE_SetOptionCache(
                CUR,
                constOptionEventCode,
                ''
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, 'update_opt: failed to run '
                    . 'PHP_CORE_SetOptionCache', 0);
            }
        }
        $sql = "update Options set\n"
            . " value = '',\n"
            . " modified = $now\n"
            . " where id=$id\n"
            . " and editable = 1";
        $res = redcommand($sql, $db);
        if ($res)
            $msg = "Option <b>$name</b> has been cleared.";
        else
            $msg = "Option <b>$name</b> was not cleared.";
    } else {
        $msg = "Option $id does not exist.";
    }
    echo fontspeak("$msg<br>");
    display_options($env, $db);
}



function delete_option($env, $db)
{
    $id = $env['id'];
    debug_note("delete_option($id,db)");
    $row = one_option($id, $db);
    if ($row) {
        $name = $row['name'];
        $sql = "delete from Options\n where id = $id";
        $res = redcommand($sql, $db);
        if ($res)
            $msg = "Option <b>$name</b> has been removed.";
        else
            $msg = "Option <b>$name</b> was not removed.";
    } else {
        $msg = "Option $id does not exist.";
    }
    echo fontspeak("$msg<br>");
    display_options($env, $db);
}


function edit_option($env, $db)
{
    $id = $env['id'];
    debug_note("edit_option($id,db)");
    $row  = one_option($id, $db);
    if ($row) {
        echo again($env);
        $self = $env['self'];
        $name = $row['name'];
        $valu = $row['value'];
        $id   = $row['id'];
        echo post_self('myform');
        echo hidden('id', $id);
        echo hidden('action', 'update');
        echo table_header();
        $head = array('Name', 'Value');
        echo table_data($head, 1);
        $input = "<textarea wrap=\"virtual\" rows=\"5\" cols=\"60\" name=\"value\">$valu</textarea>\n";
        $data = array("<b>$name</b>", $input);
        echo table_data($data, 0);
        $submit = '<input type="submit" value="submit">';
        $reset  = '<input type="reset" value="reset">';
        $data   = array($submit, $reset);
        echo table_data($data, 0);
        echo table_footer();
        echo form_footer();
        echo again($env);
    }
}

function legal_name($name)
{
    if (!is_string($name)) {
        debug_note('not a string type');
        return false;
    }
    $len = strlen($name);
    if (($len < 1) || ($len > 20)) {
        debug_note("name length ($len) invalid.");
        return false;
    }
    $ch = $name[0];
    if (($ch < 'a') || ($ch > 'z')) {
        debug_note("$name does not begin correctly");
        return false;
    }
    for ($i = 0; $i < $len; $i++) {
        $ch = $name[$i];
        $good = ((('0' <= $ch) && ($ch <= '9')) ||
            (('a' <= $ch) && ($ch <= 'z')) ||
            (($ch == '_')));
        if (!$good) {
            debug_note("weird character $ch at position $i");
            return false;
        }
    }
    return true;
}



function create_option($env, $db)
{
    $name  = $env['name'];
    $value = $env['valu'];

    if (!legal_name($name)) {
        $msg = "Illegal name <b>$name</b>.";
    } else {
        $qname = safe_addslashes($name);
        $qvalu = safe_addslashes($value);
        $now = time();
        $sql = "insert into Options set\n"
            . " name='$qname',\n"
            . " value='$qvalu',\n"
            . " modified=$now";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
        if ($num)
            $msg = "Option <b>$name</b> created.";
        else
            $msg = "Option <b>$name</b> not created.";
    }
    echo fontspeak("$msg<br>");
    display_options($env, $db);
}


function nothing($env, $db)
{
    $msg  = 'You need administrative access in order to use this';
    $msg .= ' page.  Authorization denied.';
    $msg  = "<p>$msg</p>";
    echo fontspeak($msg);
}



function update_option($env, $db)
{
    $id   = $env['id'];
    $name = $env['name'];
    $valu = $env['valu'];
    debug_note("update_option($id,$valu,db)");
    $row = one_option($id, $db);
    $now = time();
    if ($row) {
        $name  = $row['name'];
        if ($name == constOptionEventCodeStr) {
            $err = PHP_CORE_SetOptionCache(
                CUR,
                constOptionEventCode,
                $valu
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, 'update_opt: failed to run '
                    . 'PHP_CORE_SetOptionCache', 0);
            }
        }
        $qv  = safe_addslashes($valu);
        $sql = "update Options set\n"
            . " value = '$qv',\n"
            . " modified = $now\n"
            . " where id = $id\n"
            . " and editable = 1";
        $res = redcommand($sql, $db);
        if ($res)
            $msg = "Option <b>$name</b> has been updated.";
        else
            $msg = "Option <b>$name</b> was not updated.";
    } else {
        $msg = "Option $id does not exist.";
    }
    echo fontspeak("$msg<br>");
    display_options($env, $db);
    /* Now update any options that may be in shared memory. */
    PHP_HTML_SetServerOption(CUR, $name, $valu);
    $log = "Server option $name changed to $valu";
    logs::log(__FILE__, __LINE__, $log, 0);
}


function unknown_option($env, $db)
{
    $acts = $env['acts'];
    debug_note("unknown_option($acts,db)");
    display_options($env, $db);
}


/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$user   = user_data($authuser, $db);
$padm   = @($user['priv_admin']) ? 1 : 0;
$pdbg   = @($user['priv_debug']) ? 1 : 0;

$id     = get_integer('id', 0);
$dbg    = get_integer('debug', 1);
$priv   = get_integer('priv', 1);
$action = get_string('action', 'display');
$name   = get_string('name', '');
$value  = get_string('value', '');

$admin  = ($padm) ? $priv : 0;
$debug  = ($pdbg) ? $dbg  : 0;
$title  = 'Server Options';

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

server_default($db);
create_empty($onames, $db);

$env = array();
$env['id'] = $id;
$env['valu'] = $value;
$env['name'] = $name;
$env['acts'] = $action;
$env['pdbg'] = $pdbg;
$env['padm'] = $pdbg;
$env['self'] = server_var('PHP_SELF');
$env['args'] = server_var('QUERY_STRING');

if (!$admin) {
    $action = 'none';
}
switch ($action) {
    case 'display':
        display_options($env, $db);
        break;
    case 'clear':
        clear_option($env, $db);
        break;
    case 'edit':
        edit_option($env, $db);
        break;
    case 'update':
        update_option($env, $db);
        break;
    case 'delete':
        delete_option($env, $db);
        break;
    case 'define':
        define_option($env, $db);
        break;
    case 'create':
        create_option($env, $db);
        break;
    case 'none':
        nothing($env, $db);
        break;
    default:
        unknown_option($env, $db);
        break;
}
echo head_standard_html_footer($authuser, $db);
