<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 2-Jun-03   EWB     Created.
 2-Jun-03   EWB     Only show database stats when we ask for verbose.
 3-Jun-03   EWB     Report dimensions of table (rows and columns)
 3-Jun-03   EWB     Option to flip from quiet to verbose, etc.
 7-Jun-03   EWB     Added "show variables";
11-Jun-03   EWB     Added replication master and slave diagnostics.
11-Jun-03   EWB     Can examine all tables.
12-Jun-03   EWB     Show master status, show slave status require root access.
13-Jun-03   EWB     Slave Test.
14-Jun-03   EWB     Check for mysql_pconnect problem.
16-Jun-03   EWB     Uses slave library.
17-Jun-03   EWB     Further diagnostics
17-Jun-03   EWB     Event delay hh:mm:ss
17-Jun-03   EWB     Measure command elapsed time.
18-Jun-03   EWB     Moved microtime_diff into l-rcmd.
 6-Aug-03   EWB     Uniform jumps
 7-Aug-03   EWB     Don't require debug priv for this.
 1-Oct-03   EWB     Show table columns.
 9-Jan-04   EWB     Server Name.
29-Jan-04   EWB     check table command.
 1-Mar-04   EWB     check for holes.
 3-Mar-04   EWB     hole check reports timestamps.
17-Mar-04   EWB     hole check ok with zero events total.
15-Apr-04   EWB     Don't always show the process list.
20-Apr-04   EWB     mysql Menu.
 6-May-04   EWB     Optimize slave events table.
11-May-04   EWB     show index command.
12-May-04   EWB     analyze events table.
13-May-04   EWB     slave start/stop command.
13-May-04   EWB     stop slave before analyze/optimize.
12-Oct-05   BTE     Changed reference from gconfig to core in optimize_config.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.                    

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-user.php');
include('../lib/l-slav.php');
include('../lib/l-jump.php');
include('../lib/l-msql.php');
include('../lib/l-head.php');

function ident($now)
{
    $date = datestring($now);
    $info = asi_info();
    $version = $info['svvers'];
    return "\n<h2>$date ($version)</h2>\n";
}


function local_nav($env)
{
    $self = $env['self'];
    $p   = "$self?action";
    $a   = array();
    $b   = array();
    $a[] = html_link("$p=mproc", 'process');
    $a[] = html_link("$p=muser", 'user');
    $a[] = html_link("$p=mstat", 'status');
    $a[] = html_link("$p=mtab", 'tables');
    $a[] = html_link("$p=mcol", 'columns');
    $b[] = html_link("$p=sproc", 'process');
    $b[] = html_link("$p=suser", 'user');
    $b[] = html_link("$p=sstat", 'status');
    $b[] = html_link("$p=stab", 'tables');
    $b[] = html_link("$p=scol", 'columns');
    $aa  = '<b>master:</b> ' . join(" | \n", $a);
    $bb  = '<b>slave:</b> '  . join(" | \n", $b);
    return "$aa<br>\n$bb<br>\n<br><br>\n\n";
}


function again($env)
{
    $self = $env['self'];
    $args = $env['args'];
    $priv = $env['priv'];
    $href = ($args) ? "$self?$args" : $self;
    $act = "$self?action";
    $a   = array();
    if ($priv) $a[] = html_link('index.php', 'home');
    jumptags($a, 'top,bottom');
    $a[] = html_link($href, 'again');
    $a[] = html_link("$act=mhole", 'holes');
    $a[] = html_link("$act=slave", 'slave');
    $a[] = html_link("$act=event", 'event');
    $a[] = html_link("$act=menu", 'menu');
    return jumplist($a);
}


function special_header($msg, $span)
{
    return <<< HERE

<tr>
  <th colspan="$span" bgcolor="#333399">
    <font color="white">
       $msg
    </font>
  </th>
</tr>

HERE;
}


function table_header()
{
    echo "\n\n\n";
    echo '<table border="2" align="left" cellspacing="2" cellpadding="2">';
    echo "\n";
}

function table_footer()
{
    echo "\n</table>\n\n\n";
    echo "<br clear=\"all\">\n\n";
}


function table_data($args)
{
    if (safe_count($args)) {
        echo "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            echo " <td>$data</td>\n";
        }
        echo "</tr>\n";
    }
}

function table_head($args)
{
    if (safe_count($args)) {
        echo "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            echo " <th>$data</th>\n";
        }
        echo "</tr>\n";
    }
}


function show_mysql_table($sql, $db)
{
    $time = 0;
    $res  = redcommand_time($sql, $time, $db);
    if ($res) {
        $rows = mysqli_num_rows($res);
        $cols = (($___mysqli_tmp = mysqli_num_fields($res)) ? $___mysqli_tmp : false);

        $secs = microtime_show($time);
        $text = "$sql ($secs)";

        if (($rows > 0) && ($cols > 0)) {
            $msg = "Table contains $rows rows and $cols columns.<br><br>\n\n\n";
            echo $msg;

            table_header();
            echo special_header($text, $cols);
            $args = array();
            for ($col = 0; $col < $cols; $col++) {
                $args[$col] = ((($___mysqli_tmp = mysqli_fetch_field_direct($res, $col)->name) && (!is_null($___mysqli_tmp))) ? $___mysqli_tmp : false);
            }
            table_head($args);
            while ($row = mysqli_fetch_array($res)) {
                $args = array();
                for ($col = 0; $col < $cols; $col++) {
                    $valu = $row[$col];
                    $valu = ($valu == '') ? '<br>' : $valu;
                    $args[$col] = $valu;
                }
                table_data($args);
            }
            table_footer();
        } else {
            echo "$sql<br>empty table<br>";
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    } else {
        echo "$sql<br>empty table<br>";
    }
}


function table_status($dbase, $db)
{
    $sql = "show table status from $dbase";
    show_mysql_table($sql, $db);
}



/*
    |  This is all useful stuff, but it doesn't change
    |  very often.  Once we've seen it, we've seen it.
    */

function dump_tables($db)
{
    $names = find_database_names($db);
    reset($names);
    foreach ($names as $xxx => $name) {
        table_status($name, $db);
    }
}


function dump_columns($db)
{
    $names = find_database_names($db);
    reset($names);
    foreach ($names as $xxx => $dbname) {
        if (mysqli_select_db($db, $dbname)) {
            $tables = find_table_names($dbname, $db);
            reset($tables);
            foreach ($tables as $yyy => $table) {
                $sql = "show columns from $table";
                show_mysql_table($sql, $db);
            }
        }
    }
}

function dump_index($db)
{
    $names = find_database_names($db);
    reset($names);
    foreach ($names as $xxx => $dbname) {
        if (mysqli_select_db($db, $dbname)) {
            $tables = find_table_names($dbname, $db);
            reset($tables);
            foreach ($tables as $yyy => $table) {
                $sql = "show index from $table";
                show_mysql_table($sql, $db);
            }
        }
    }
}


function dump_check($db)
{
    $names = find_database_names($db);
    reset($names);
    foreach ($names as $xxx => $dbname) {
        if (mysqli_select_db($db, $dbname)) {
            $tables = find_table_names($dbname, $db);
            reset($tables);
            foreach ($tables as $yyy => $table) {
                $sql = "check table $table";
                show_mysql_table($sql, $db);
            }
        }
    }
}



function double($a, $b)
{
    table_data(array($a, $b));
}

function event_status($name, $id, $server, $user, $count, $min, $max, $secs, $db)
{
    $dmin = date('m/d/Y H:i:s', $min);
    $dmax = date('m/d/Y H:i:s', $max);
    table_header();
    echo special_header($name, 2);
    $head = array('Name', 'Value');

    $time = microtime_show($secs);

    table_head($head);
    double('Id', $id);
    double('Server', $server);
    double('User', $user);
    double('Link', $db);
    double('First Event', $dmin);
    double('Last Event', $dmax);
    double('Event Count', $count);
    double('Elapsed', $time);
    table_footer();
    echo "<br clear=\"all\">\n\n";
}



function optimize($env, $name, $db)
{
    $time = 0;
    $sql  = "optimize table $name";
    $res  = redcommand_time($sql, $time, $db);
    if ($res) {
        $auth = $env['auth'];
        $secs = microtime_show($time);
        $msg  = "mysql: $auth: $sql ($secs).";
        logs::log(__FILE__, __LINE__, $msg, 0);
        echo "<p>$msg</p>\n";
    }
}


function optimize_slave($env, $db)
{
    if (mysqli_select_db($db, event)) {
        stop_slave($env, $db);
        echo "<p>optimize events table ...</p>\n";
        optimize($env, 'Events', $db);
        table_status('event', $db);
        start_slave($env, $db);
    }
}



function optimize_events($env, $db)
{
    if (mysqli_select_db($db, event)) {
        echo "<p>optimize events table ...</p>\n";
        optimize($env, 'Events', $db);
        optimize($env, 'Console', $db);
        if (mysqli_select_db($db, core)) {
            optimize($env, 'Census', $db);
            optimize($env, 'Customers', $db);
            optimize($env, 'Users', $db);
            optimize($env, 'Files', $db);
            optimize($env, 'Options', $db);
        }
        table_status('event', $db);
        table_status('core', $db);
    }
}

function analyze_events($env, $db)
{
    if (mysqli_select_db($db, event)) {
        echo "<p>analyze events table ...</p>\n";
        $sql = 'analyze table Events';
        redcommand($sql, $db);
    }
}


function slave_status($env, $db)
{
    $sql = 'show slave status';
    show_mysql_table($sql, $db);
}


function stop_slave($env, $db)
{
    $sql  = 'slave stop';  // "stop slave" for mysql 4.0.5
    redcommand($sql, $db);
    slave_status($env, $db);
}

function start_slave($env, $db)
{
    $sql = 'slave start';  // "start slave" for mysql 4.0.5
    redcommand($sql, $db);
    slave_status($env, $db);
}


function analyze_slave($env, $db)
{
    stop_slave($env, $db);
    analyze_events($env, $db);
    start_slave($env, $db);
    logs::log(__FILE__, __LINE__, 'mysql: analyze slave events table', 0);
}


function optimize_assets($env, $db)
{
    $auth = $env['auth'];
    if (mysqli_select_db($db, asset)) {
        echo "<p>optimize assets table ... </p>\n";
        optimize($env, 'AssetData', $db);
        optimize($env, 'DataName', $db);
        optimize($env, 'Machine', $db);
        table_status('asset', $db);
        mysqli_select_db($db, core);
    }
}

function optimize_config($env, $db)
{
    $auth = $env['auth'];
    if (mysqli_select_db($db, core)) {
        echo "<p>optimize site manager tables ... </p>\n";
        optimize($env, 'Variables', $db);
        optimize($env, 'VarValues', $db);
        optimize($env, 'ValueMap', $db);
        table_status('core', $db);
        mysqli_select_db($db, core);
    }
}


function show_process($env, $db)
{
    $sql = "show full processlist";
    show_mysql_table($sql, $db);
}

function show_variables($env, $db)
{
    $sql = "show variables";
    show_mysql_table($sql, $db);
}

function show_status($env, $db)
{
    $sql = 'show status';
    show_mysql_table($sql, $db);
}

function show_logs($env, $db)
{
    $sql = 'show master logs';
    show_mysql_table($sql, $db);
}

function master_status($env, $db)
{
    $sql = 'show master status';
    show_mysql_table($sql, $db);
}

function show_users($db)
{
    if (mysqli_select_db($db, mysql)) {
        $sql = "select * from user";
        show_mysql_table($sql, $db);
    }
}

function show_events($db)
{
    if (mysqli_select_db($db, event)) {
        $sql  = "select idx, machine, description,"
            . " from_unixtime(servertime) as time"
            . " from Events"
            . " order by servertime desc"
            . " limit 50";
        show_mysql_table($sql, $db);
        db_change($GLOBALS['PREFIX'] . 'core', $db);
    }
}



/*
    |  This will detect if someone has ever removed a
    |  record from the middle of the Events table.
    |
    |  This does not prove that there is a hole...
    |  but it does show if there might be.
    */

function check_holes($db)
{
    $min = "select min(idx) from Events limit 1";
    $max = "select max(idx) from Events limit 1";
    $cnt = "select count(*) from Events";

    if (mysqli_select_db($db, event)) {
        echo "<br clear=\"all\">\n\n";
        echo "<br clear=\"all\">\n\n";

        $emin = 0;
        $emax = 0;
        $dcnt = 0;
        $hcnt = 0;
        $now  = time();
        $dmin = $now;
        $dmax = $now;
        $ecnt = find_scalar($cnt, $db);
        if ($ecnt > 0) {
            $emin = find_scalar($min, $db);
            $emax = find_scalar($max, $db);
            $min  = "select servertime from Events where idx = $emin";
            $max  = "select servertime from Events where idx = $emax";
            $dmin = find_scalar($min, $db);
            $dmax = find_scalar($max, $db);
            $dcnt = 1 + ($emax - $emin);
            $hcnt = $dcnt - $ecnt;
            $now  = time();
        }
        $secs = $now - $dmin;
        $days = intval(round($secs / 84600));
        $secs = $now - $dmax;
        $tmin = "$emin: " . datestring($dmin);
        $tmax = "$emax: " . datestring($dmax);

        if ($hcnt < 0) $hcnt = 0;
        if ($secs < 0) $secs = 0;

        $head = explode('|', 'Name|Value');
        table_header();
        echo special_header('Potential Event Holes', 2);
        table_head($head);
        double('First Event', $tmin);
        double('Last Event', $tmax);
        double('Age', "$secs seconds");
        double('Days', "$days days");
        double('Event Count', $ecnt);
        double('Delta', $dcnt);
        double('Missing', $hcnt);
        table_footer();
    }
    mysqli_select_db($db, core);
}

function event_test($mdb, $mid, $msrv, $sdb, $sid, $ssrv)
{
    $min = "select min(servertime) from Events limit 1";
    $max = "select max(servertime) from Events limit 1";
    $cnt = "select count(*) from Events";
    $usr = "select user()";

    $musr = find_scalar($usr, $mdb);
    $susr = find_scalar($usr, $sdb);

    $scnt = 0;
    $smax = 0;
    $mmax = 0;
    $smax = 0;

    if (mysqli_select_db($mdb, event)) {
        echo "<br clear=\"all\">\n\n";
        echo "<br clear=\"all\">\n\n";

        $maaa = microtime();
        $mcnt = find_scalar($cnt, $mdb);
        $mmin = find_scalar($min, $mdb);
        $mmax = find_scalar($max, $mdb);
        $mbbb = microtime();
        $melp = microtime_diff($maaa, $mbbb);
        event_status('Master Database', $mid, $msrv, $musr, $mcnt, $mmin, $mmax, $melp, $mdb);
    } else {
        $error = mysqli_error($mdb);
        $errno = mysqli_errno($mdb);
        echo "$errno:$error<br>master database failure<br>";
    }

    if (mysqli_select_db($sdb, event)) {
        echo "<br clear=\"all\">\n\n";
        echo "<br clear=\"all\">\n\n";

        $saaa = microtime();
        $scnt = find_scalar($cnt, $sdb);
        $smin = find_scalar($min, $sdb);
        $smax = find_scalar($max, $sdb);
        $sbbb = microtime();
        $selp = microtime_diff($saaa, $sbbb);
        event_status('Slave Database', $sid, $ssrv, $susr, $scnt, $smin, $smax, $selp, $sdb);

        $miss  = event_missing($mdb, $sdb);
        $delay = event_delay($mdb, $sdb);
        if ($miss > 0) {
            echo "$miss events missing.<br>\n";
        }
        if ($delay > 0) {
            $time = microtime_show($delay);
            echo "event delay $time.<br>\n";
        }
    } else {
        $error = mysqli_error($sdb);
        $errno = mysqli_errno($sdb);
        echo "$errno:$error<br>slave database failure<br>";
    }
}



function nothing($env)
{
    echo "Access denied ...<br>\n";
}


function coma($env)
{
    echo "<br>\n"
        .  "<h2>Slave database is not running<h2>\n"
        .  "<br>\n";
}

function unknown($env)
{
    $acts = $env['acts'];
    echo "<br>\n"
        .  "<p>Unknown action $acts</p>\n"
        .  "<br>\n";
}


/*
    |  It seems that if you are running both the master and slave
    |  on localhost mysql_pconnect will give you the same database
    |  for localhost:43306 that it does for localhost:3306
    |
    |  So, we check for that.
    */

function dups($mid, $sid)
{
    echo "<br>\n"
        .  "<h2>Error</h2>\n"
        .  "<br>\n"
        .  "Both connections are talking to the same server.<br>"
        .  "Master Server Id:$mid<br>"
        .  "Slave  Server Id:$sid<br>"
        .  "<br>\n\n";
}


function slave($env, $mdb)
{
    $action = $env['acts'];
    $server = server_opt('slave_server', $mdb);
    $sdb = false;
    if ($server) {
        $sdb = db_server($server);
    }
    $mid = 0;
    $sid = 0;

    /*
        |  It seems that if you are running both the master and slave
        |  on localhost mysql_pconnect will sometimes give you the
        |  same database for localhost:43306 that it does for localhost:3306
        |
        |  So, we check for that.
        */

    if (($sdb) && ($sdb != $mdb)) {
        $mid = intval(get_mysql_variable('server_id', $mdb));
        $sid = intval(get_mysql_variable('server_id', $sdb));
        debug_note("master server_id:$mid  slave server_id:$sid");
    }


    if ($sid == 0) {
        $action = 'coma';
    }

    if (($sid != 0) && ($sid == $mid)) {
        $action = 'dups';
    } else {
        echo "<h2>Slave Database</h2>\n";
    }

    $msrv = 'localhost:3306';
    switch ($action) {
        case 'slave':
            event_test($mdb, $mid, $msrv, $sdb, $sid, $server);
            break;
        case 'sproc':
            show_process($env, $sdb);
            break;
        case 'svar':
            show_variables($env, $sdb);
            break;
        case 'sstat':
            show_status($env, $sdb);
            break;
        case 'scol':
            dump_columns($sdb);
            break;
        case 'sind':
            dump_index($sdb);
            break;
        case 'schk':
            dump_check($sdb);
            break;
        case 'stab':
            dump_tables($sdb);
            break;
        case 'suser':
            show_users($sdb);
            break;
        case 'soet':
            optimize_slave($env, $sdb);
            break;
        case 'saet':
            analyze_slave($env, $sdb);
            break;
        case 'strt':
            start_slave($env, $sdb);
            break;
        case 'stop':
            stop_slave($env, $sdb);
            break;
        case 'event':
            show_events($sdb);
            break;
        case 'ssstat':
            slave_status($env, $sdb);
            break;
        case 'coma':
            coma($env);
            break;
        case 'dups':
            dups($mid, $sid);
            break;
        default:
            unknown($env);
            break;
    }
    db_change($GLOBALS['PREFIX'] . 'core', $mdb);
}



function listcmd($env, $cmd, $text)
{
    $self = $env['self'];
    $href = "$self?action=$cmd";
    $link = html_link($href, $text);
    return "<li>$link</li>\n";
}


function mysql_menu($env, $db)
{
    $priv = $env['priv'];
    echo "<br><ul>\n";
    echo listcmd($env, 'mproc', 'master: show full processlist');
    echo listcmd($env, 'sproc', 'slave: show full processlist');
    echo listcmd($env, 'mstat', 'master: show status');
    echo listcmd($env, 'sstat', 'slave: show status');
    echo listcmd($env, 'mvar', 'master: show variables');
    echo listcmd($env, 'svar', 'slave: show variables');
    echo listcmd($env, 'mtab', 'master: show table status from ...');
    echo listcmd($env, 'stab', 'slave: show table status from ...');
    echo listcmd($env, 'mcol', 'master: show columns from ...');
    echo listcmd($env, 'scol', 'slave: show columns from ...');
    echo listcmd($env, 'mind', 'master: show index from ...');
    echo listcmd($env, 'sind', 'slave: show index from ...');
    echo listcmd($env, 'smstat', 'master: show master status');
    echo listcmd($env, 'ssstat', 'slave: show slave status');
    echo listcmd($env, 'mhole', 'check for holes');
    echo listcmd($env, 'slave', 'checkup slave status');
    echo listcmd($env, 'event', 'recent events');
    echo listcmd($env, 'mlog', 'master: show master logs');
    if ($priv) {
        echo listcmd($env, 'mchk', 'master: check table ...');
        echo listcmd($env, 'schk', 'slave: check table ...');
        echo listcmd($env, 'moet', 'master: optimize events table');
        echo listcmd($env, 'soet', 'slave: optimize events table');
        echo listcmd($env, 'moat', 'master: optimize assets table');
        echo listcmd($env, 'moct', 'master: optimize config table');
        echo listcmd($env, 'maet', 'master: analyze events table');
        echo listcmd($env, 'saet', 'slave: analyze events table');
        echo listcmd($env, 'stop', 'slave: slave stop');
        echo listcmd($env, 'strt', 'slave: slave start');
    }

    echo "</ul><br>\n";
}

/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$user   = user_data($authuser, $db);
$admin  = $user['priv_admin'];
$debug  = $user['priv_debug'];

$refresh = get_string('refresh', 'never');
$action  = get_string('action', 'mproc');
$self    = server_var('PHP_SELF');
$text    = '';
if ($refresh > 0) {
    $secs = 60 * intval($refresh);
    $text = "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n"
        . "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"$secs\">\n";
}

$refreshtime = $text;   // spooky global

$serv  = server_name($db);
$title = "$serv (mysql)";

$env = array();
$env['acts'] = $action;
$env['auth'] = $authuser;
$env['self'] = server_var('PHP_SELF');
$env['args'] = server_var('QUERY_STRING');
$env['priv'] = $debug;

$nav = local_nav($env);
$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, $nav, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users


$mnow = microtime();
list($usecs, $now) = explode(' ', $mnow);
if ($refresh > 0) {
    $len = strlen($refreshtime);
    $date = datestring($now);
    debug_note("refresh:$refresh, len:$len, now:$date");
}


if (!$admin) {
    $action = 'none';
}

echo again($env);

echo ident($now);

switch ($action) {
    case 'none':
        nothing($env);
        break;
    case 'mproc':
        show_process($env, $db);
        break;
    case 'mvar':
        show_variables($env, $db);
        break;
    case 'mstat':
        show_status($env, $db);
        break;
    case 'mlog':
        show_logs($env, $db);
        break;
    case 'mcol':
        dump_columns($db);
        break;
    case 'mind':
        dump_index($db);
        break;
    case 'mchk':
        dump_check($db);
        break;
    case 'moet':
        optimize_events($env, $db);
        break;
    case 'moat':
        optimize_assets($env, $db);
        break;
    case 'moct':
        optimize_config($env, $db);
        break;
    case 'maet':
        analyze_events($env, $db);
        break;
    case 'smstat':
        master_status($env, $db);
        break;
    case 'mtab':
        dump_tables($db);
        break;
    case 'mhole':
        check_holes($db);
        break;
    case 'muser':
        show_users($db);
        break;
    case 'event':
        show_events($db);
        break;
    case 'menu':
        mysql_menu($env, $db);
        break;
    case 'schk':;
    case 'suser':;
    case 'ssstat':;
    case 'scol':;
    case 'sstat':;
    case 'soet':;
    case 'saet':;
    case 'sind':;
    case 'stab':;
    case 'svar':;
    case 'stop':;
    case 'strt':;
    case 'sproc':;
    case 'slave':
        slave($env, $db);
        break;
    default:
        unknown($env);
        break;
}
echo ident($now);
echo again($env);

$mlater  = microtime();
$elapsed = microtime_diff($mnow, $mlater);
if ($elapsed > 0) {
    $text = microtime_show($elapsed);
    echo "<p>Elapsed time: $text</p>\n";
}
echo head_standard_html_footer($authuser, $db);
