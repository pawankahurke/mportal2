<?php

/*
Revision History

12-Feb-03   EWB     Created
25-Feb-03   EWB     Propogate users.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header() 
19-Mar-03   NL      Move $debug initialization and surrounding lines above ob_ lines
17-Apr-03   EWB     Fixed priv_downloads.
18-Apr-03   EWB     Don't modify notify email, console.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
04-Jun-07   BTE     Added calls to PHP_REPF_UpdateDynamicList.
06-Jun-07   BTE     Added code to support SavedSearches.searchuniq.
27-Jun-07   BTE     Bug 4198: Fix searchuniq for global SavedSearches.
31-Jul-07   BTE     asrchuniq support and universal unique function.

*/


$title = 'Database Global Upgrade';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)  
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-head.php');
include('../lib/l-rcmd.php');
include('../lib/l-user.php');
include('../lib/l-cnst.php');
include('../lib/l-gsql.php');


function find_databases($db)
{
    $dbs  = array();
    $res  = (($___mysqli_tmp = mysqli_query($db, "SHOW DATABASES")) ? $___mysqli_tmp : false);
    if ($res) {
        $n = mysqli_num_rows($res);
        for ($i = 0; $i < $n; $i++) {
            $name = ((mysqli_data_seek($res, $i) && (($___mysqli_tmp = mysqli_fetch_row($res)) !== NULL)) ? array_shift($___mysqli_tmp) : false);
            $dbs[$name] = true;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $dbs;
}


function find_single($sql, $db)
{
    $row  = array();
    $res  = command($sql, $db);
    if ($res) {
        $num = mysqli_num_rows($res);
        if ($num > 0) {
            $row = mysqli_fetch_array($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $row;
}

function load_search($db)
{
    $sql = "select * from SavedSearches";
    return find_several($sql, $db);
}


function load_users($db)
{
    $tmp = array();
    $sql = "select * from Users";
    $res = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_array($res)) {
            $id = $row['userid'];
            $tmp[$id] = $row;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $tmp;
}


function load_report($db)
{
    $sql = "select * from Reports";
    return find_several($sql, $db);
}

function load_notify($db)
{
    $sql = "select * from Notifications";
    return find_several($sql, $db);;
}

function load_asset($db)
{
    $sql = "select * from AssetReports";
    return find_several($sql, $db);
}

function load_query($db)
{
    $sql = "select * from AssetSearches";
    return find_several($sql, $db);
}

function load_criteria($db)
{
    $sql = "select * from AssetSearchCriteria";
    return find_several($sql, $db);
}

function count_records($sql, $db)
{
    $num = 0;
    $res = command($sql, $db);
    if ($res) {
        $num = myslq_num_rows($res);
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $num;
}


function find_name($name, $user, $table, $db)
{
    $row  = array();
    $qn   = safe_addslashes($name);
    $sql  = "select * from $table where";
    $sql .= " (name = '$qn') and";
    $sql .= " (username = '$user')";
    return find_single($sql, $db);
}

function find_user($user, $db)
{
    $row  = array();
    $sql  = "select * from Users where\n";
    $sql .= " (username = '$user')";
    return find_single($sql, $db);
}

function find_id($id, $table, $db)
{
    $sql = "select * from $table where id = $id";
    return find_single($sql, $db);
}

function find_report($name, $user, $db)
{
    return find_name($name, $user, 'Reports', $db);
}

function find_asset($name, $user, $db)
{
    return find_name($name, $user, 'AssetReports', $db);
}

function find_search($name, $user, $db)
{
    return find_name($name, $user, 'SavedSearches', $db);
}

function find_query($name, $user, $db)
{
    return find_name($name, $user, 'AssetSearches', $db);
}

function find_notify($name, $user, $db)
{
    return find_name($name, $user, 'Notifications', $db);
}


function notify_record($row, $db)
{
    $id        = $row['id'];
    $gbl       = $row['global'];
    $days      = $row['days'];
    $solo      = $row['solo'];
    $priority  = $row['priority'];
    $console   = $row['console'];
    $email     = $row['email'];
    $defmail   = $row['defmail'];
    $search_id = $row['search_id'];
    $seconds   = $row['seconds'];
    $threshold = $row['threshold'];
    $last_run  = $row['last_run'];
    $suspend   = $row['suspend'];
    $enabled   = $row['enabled'];

    $created   = @intval($row['created']);
    $modified  = @intval($row['modified']);

    $qn  = safe_addslashes($row['name']);
    $qc  = safe_addslashes($row['config']);
    $qu  = safe_addslashes($row['username']);
    $ql  = safe_addslashes($row['emaillist']);
    $qm  = @safe_addslashes($row['machines']);
    $qe  = @safe_addslashes($row['excluded']);

    $sql  = ($id) ? 'update' : 'insert into';
    $sql .= "\n Notifications set\n";
    $sql .= " global = $gbl,\n";
    $sql .= " priority = $priority,\n";
    $sql .= " name = '$qn',\n";
    $sql .= " username = '$qu',\n";
    $sql .= " days = $days,\n";
    $sql .= " solo = $solo,\n";
    $sql .= " console = $console,\n";
    $sql .= " email = $email,\n";
    $sql .= " emaillist = '$ql',\n";
    $sql .= " defmail = $defmail,\n";
    $sql .= " search_id = $search_id,\n";
    $sql .= " seconds = $seconds,\n";
    $sql .= " threshold = $threshold,\n";
    $sql .= " last_run = $last_run,\n";
    $sql .= " suspend = $suspend,\n";
    $sql .= " config = '$qc',\n";
    $sql .= " machines = '$qm',\n";
    $sql .= " excluded = '$qe',\n";
    $sql .= " created = $created,\n";
    $sql .= " modified = $modified,\n";
    $sql .= " enabled = $enabled";
    if ($id) $sql .= " where id = $id";
    redcommand($sql, $db);
}


function report_record($row, $db)
{
    $qn =   safe_addslashes($row['name']);
    $qu =   safe_addslashes($row['username']);
    $ql =   safe_addslashes($row['emaillist']);
    $qf =   safe_addslashes($row['format']);
    $q1 = @safe_addslashes($row['order1']);
    $q2 = @safe_addslashes($row['order2']);
    $q3 = @safe_addslashes($row['order3']);
    $q4 = @safe_addslashes($row['order4']);
    $qc = @safe_addslashes($row['config']);
    $qs =   safe_addslashes($row['search_list']);

    $id        = $row['id'];
    $gbl       = $row['global'];
    $cycle     = $row['cycle'];
    $defmail   = $row['defmail'];
    $hour      = @intval($row['hour']);
    $minute    = @intval($row['minute']);
    $wday      = @intval($row['wday']);
    $mday      = @intval($row['mday']);
    $last_run  = $row['last_run'];
    $enabled   = $row['enabled'];
    $details   = $row['details'];
    $umin      = @intval($row['umin']);
    $umax      = @intval($row['umax']);
    $created   = @intval($row['created']);
    $modified  = @intval($row['modified']);

    $sql  = ($id) ? 'update' : 'insert into';
    $sql .= "\n Reports set\n";
    $sql .= " global = $gbl,\n";
    $sql .= " name = '$qn',\n";
    $sql .= " username = '$qu',\n";
    $sql .= " emaillist = '$ql',\n";
    $sql .= " defmail = $defmail,\n";
    $sql .= " format = '$qf',\n";
    $sql .= " cycle = $cycle,\n";
    $sql .= " hour = $hour,\n";
    $sql .= " minute = $minute,\n";
    $sql .= " wday = $wday,\n";
    $sql .= " mday = $mday,\n";
    $sql .= " enabled = $enabled,\n";
    $sql .= " last_run = $last_run,\n";
    $sql .= " order1 = '$q1',\n";
    $sql .= " order2 = '$q2',\n";
    $sql .= " order3 = '$q3',\n";
    $sql .= " order4 = '$q4',\n";
    $sql .= " details = $details,\n";
    $sql .= " umin = $umin,\n";
    $sql .= " umax = $umax,\n";
    $sql .= " created = $created,\n";
    $sql .= " modified = $modified,\n";
    $sql .= " config = '$qc',\n";
    $sql .= " search_list = '$qs'";
    if ($id) $sql .= "\n where id = $id";
    redcommand($sql, $db);
}


function asset_record($row, $db)
{
    $qn =   safe_addslashes($row['name']);
    $qu =   safe_addslashes($row['username']);
    $ql =   safe_addslashes($row['emaillist']);
    $qf =   safe_addslashes($row['format']);
    $q1 = @safe_addslashes($row['order1']);
    $q2 = @safe_addslashes($row['order2']);
    $q3 = @safe_addslashes($row['order3']);
    $q4 = @safe_addslashes($row['order4']);

    $id        = $row['id'];
    $gbl       = $row['global'];
    $cycle     = $row['cycle'];
    $defmail   = $row['defmail'];
    $hour      = @intval($row['hour']);
    $minute    = @intval($row['minute']);
    $wday      = @intval($row['wday']);
    $mday      = @intval($row['mday']);
    $last_run  = $row['last_run'];
    $enabled   = $row['enabled'];
    $details   = $row['details'];

    $sid       = @intval($row['searchid']);
    $change    = @intval($row['change_rpt']);
    $umin      = @intval($row['umin']);
    $umax      = @intval($row['umax']);
    $created   = @intval($row['created']);
    $modified  = @intval($row['modified']);

    $sql  = ($id) ? 'update' : 'insert into';
    $sql .= "\n AssetReports set\n";
    $sql .= " global = $gbl,\n";
    $sql .= " name = '$qn',\n";
    $sql .= " username = '$qu',\n";
    $sql .= " emaillist = '$ql',\n";
    $sql .= " defmail = $defmail,\n";
    $sql .= " format = '$qf',\n";
    $sql .= " cycle = $cycle,\n";
    $sql .= " hour = $hour,\n";
    $sql .= " minute = $minute,\n";
    $sql .= " wday = $wday,\n";
    $sql .= " mday = $mday,\n";
    $sql .= " enabled = $enabled,\n";
    $sql .= " last_run = $last_run,\n";
    $sql .= " order1 = '$q1',\n";
    $sql .= " order2 = '$q2',\n";
    $sql .= " order3 = '$q3',\n";
    $sql .= " order4 = '$q4',\n";
    $sql .= " details = $details,\n";
    $sql .= " searchid = $sid,\n";
    $sql .= " change_rpt = $change,\n";
    $sql .= " created = $created,\n";
    $sql .= " modified = $modified,\n";
    $sql .= " umax = $umax,\n";
    $sql .= " umin = $umin";
    if ($id) $sql .= "\n where id = $id";
    redcommand($sql, $db);
}

function query_record($row, $db)
{
    $qn =   safe_addslashes($row['name']);
    $qu =   safe_addslashes($row['username']);
    $qs =   safe_addslashes($row['searchstring']);
    $qf = @safe_addslashes($row['displayfields']);
    $qr = @safe_addslashes($row['refresh']);

    $id  = $row['id'];
    $gbl = $row['global'];

    $dc  = @intval($row['date_code']);
    $dv  = @intval($row['date_value']);
    $rs  = @intval($row['row_size']);
    $xp  = @intval($row['expires']);

    $created   = @intval($row['created']);
    $modified  = @intval($row['modified']);

    $sql  = ($id) ? 'update' : 'insert into';
    $sql .= "\n AssetSearches set\n";
    $sql .= " global = $gbl,\n";
    $sql .= " name = '$qn',\n";
    $sql .= " searchstring = '$qs',\n";
    $sql .= " username = '$qu',\n";
    $sql .= " displayfields = '$qf',\n";
    $sql .= " date_code = $dc,\n";
    $sql .= " date_value = $dv,\n";
    $sql .= " created = $created,\n";
    $sql .= " modified = $modified,\n";
    $sql .= " rowsize = $rs,\n";
    $sql .= " refresh = '$qr',\n";
    $sql .= " expires = $xp";
    $asrchuniq = USER_GenerateManagedUniq(
        $row['name'],
        $row['username'],
        $db
    );
    $sql .= ",\n asrchuniq = '$asrchuniq'";
    if ($id) $sql .= "\n where id = $id";
    redcommand($sql, $db);
}

function criteria_record($row, $db)
{
    $qf = @safe_addslashes($row['fieldname']);
    $qg = @safe_addslashes($row['groupname']);
    $qv = @safe_addslashes($row['value']);

    $id  = $row['id'];
    $aid = $row['assetsearchid'];
    $xp  = $row['expires'];

    $blk = @intval($row['block']);
    $cmp = @intval($row['comparison']);

    $sql  = ($id) ? 'update' : 'insert into';
    $sql .= "\n AssetSearchCriteria set\n";
    $sql .= " assetsearchid = $aid,\n";
    $sql .= " block = $blk,\n";
    $sql .= " fieldname = '$qf',\n";
    $sql .= " comparison = $cmp,\n";
    $sql .= " value = '$qv',\n";
    $sql .= " groupname = '$qg',\n";
    $sql .= " expires = $xp";
    if ($id) $sql .= "\n where id = $id";
    redcommand($sql, $db);
}


function search_record($row, $db)
{
    $id  = $row['id'];
    $gbl = $row['global'];
    $created  = @intval($row['created']);
    $modified = @intval($row['modified']);

    $qs = safe_addslashes($row['searchstring']);
    $qn = safe_addslashes($row['name']);
    $qu = safe_addslashes($row['username']);

    $sql  = ($id) ? 'update' : 'insert into';
    $sql .= "\n SavedSearches set\n";
    $sql .= " global = $gbl,\n";
    $sql .= " username = '$qu',\n";
    $sql .= " name = '$qn',\n";
    $sql .= " searchstring = '$qs',\n";
    $sql .= " created = $created,\n";
    $sql .= " modified = $modified";
    $searchuniq = USER_GenerateManagedUniq(
        $row['name'],
        $row['username'],
        $db
    );
    $sql .= ",\n searchuniq = '$searchuniq'";
    if ($id) {
        $sql .= "\n where id = $id";
    }
    redcommand($sql, $db);
    PHP_REPF_UpdateDynamicList(CUR, constJavaListEventFilters);
}

function user_record($row, $db)
{
    $id  = $row['userid'];

    $qu = safe_addslashes($row['username']);
    $qp = @safe_addslashes($row['password']);
    $qn = safe_addslashes($row['notify_mail']);
    $qr = safe_addslashes($row['report_mail']);

    $pa = @intval($row['priv_admin']);
    $gn = @intval($row['priv_notify']);
    $gr = @intval($row['priv_report']);
    $ga = @intval($row['priv_areport']);
    $gs = @intval($row['priv_search']);
    $gq = @intval($row['priv_aquery']);
    $pd = @intval($row['priv_downloads']);
    $pu = @intval($row['priv_updates']);
    $pc = @intval($row['priv_config']);
    $px = @intval($row['priv_debug']);

    $cmd  = ($id) ? 'update' : 'insert into';
    $sql  = "$cmd Users set\n";
    $sql .= " username = '$qu',\n";
    $sql .= " password = '$qp',\n";
    $sql .= " report_mail = '$qr',\n";
    $sql .= " notify_mail = '$qn',\n";
    $sql .= " priv_admin = $pa,\n";
    $sql .= " priv_notify = $gn,\n";
    $sql .= " priv_report = $gr,\n";
    $sql .= " priv_areport = $ga,\n";
    $sql .= " priv_search = $gs,\n";
    $sql .= " priv_aquery = $gq,\n";
    $sql .= " priv_downloads = $pd,\n";
    $sql .= " priv_updates= $pu,\n";
    $sql .= " priv_config = $pc,\n";
    $sql .= " priv_debug = $px";
    if ($id) $sql .= "\n where userid = $id";
    return redcommand($sql, $db);
}


function update_search($row, $now, $db)
{
    $name = $row['name'];
    $user = $row['username'];

    $old = find_search($name, $user, $db);
    if ($old) {
        $msg = "search: $name already exists, not updating.";
    } else {
        $msg = "search: create $name.";
        $row['id'] = 0;
        $row['created'] = $now;
        $row['modified'] = $now;
        search_record($row, $db);
    }
    $msg = fontspeak("$msg<br>");
    echo "$msg\n";
}

function update_user($row, $now, $db)
{
    $user = $row['username'];

    $old = find_user($user, $db);
    if ($old) {
        $msg = "users: $user already exists, not updating.";
    } else {
        $msg = "user: create $user.";
        $row['userid'] = 0;
        user_record($row, $db);
    }
    $msg = fontspeak("$msg<br>");
    echo "$msg\n";
}


function update_query($row, $now, $db)
{
    $name = $row['name'];
    $user = $row['username'];

    $old = find_query($name, $user, $db);
    if ($old) {
        $msg = "query: $name already exists, not updating.";
    } else {
        $msg = "query: create $name.";
        $row['modified'] = $now;
        $row['created'] = $now;
        $row['id'] = 0;
        query_record($row, $db);
    }
    $msg = fontspeak("$msg<br>");
    echo "$msg\n";
}



function find_criteria($row, $db)
{
    $qf = @safe_addslashes($row['fieldname']);
    $qg = @safe_addslashes($row['groupname']);
    $qv = @safe_addslashes($row['value']);

    $aid = $row['assetsearchid'];
    $xp  = $row['expires'];

    $blk = @intval($row['block']);
    $cmp = @intval($row['comparison']);

    $sql  = "select * from AssetSearchCriteria where\n";
    $sql .= " (assetsearchid = $aid) and\n";
    $sql .= " (block = $blk) and\n";
    $sql .= " (fieldname = '$qf') and\n";
    $sql .= " (comparison = $cmp) and\n";
    $sql .= " (value = '$qv') and\n";
    $sql .= " (groupname = '$qg') and\n";
    $sql .= " (expires = $xp)";
    return find_several($sql, $db);
}


function update_criteria($row, $nq, $now, $db)
{
    $cid   = $row['id'];
    $qid   = $row['assetsearchid'];
    $query = @$nq[$qid];
    if ($query) {
        $name = $query['name'];
        $user = $query['username'];
        $new = find_query($name, $user, $db);
        if ($new) {
            $row['assetsearchid'] = $new['id'];
            $crit = find_criteria($row, $db);

            if ($crit) {
                $msg = "criteria $cid: unchanged $name.";
            } else {
                $row['id'] = 0;
                criteria_record($row, $db);
                $msg = "criteria $cid: update $name.";
            }
        } else {
            $msg = "criteria $cid: missing: $name.";
        }
        $msg = fontspeak("$msg<br>");
        echo $msg;
    } else {
        debug_note("criteria $cid: query ($qid) has vanished.");
    }
}


function update_notify($row, $ns, $now, $db)
{
    $name  = $row['name'];
    $owner = $row['username'];
    $sid   = $row['search_id'];

    $sname  = $ns[$sid]['name'];
    $suser  = $ns[$sid]['username'];
    $search = find_search($sname, $suser, $db);

    if ($search) {
        $row['search_id'] = $search['id'];
        $old = find_notify($name, $owner, $db);
        if ($old) {
            $msg = "notify: $name already exists, not updating.";
        } else {
            $row['id'] = 0;
            $row['last_run'] = $now;
            $row['created']  = $now;
            $row['modified'] = $now;
            notify_record($row, $db);
            $msg = "notify: create $name.";
        }
    } else {
        $msg = "error -- search ($sid) ($sname) for user ($suser) does not yet exist.";
    }
    $msg = fontspeak("$msg<br>");
    echo "$msg\n";
}



function make_report_list($list, $ns, $db)
{
    $text = '';
    $args = explode(',', $list);
    $temp = array();
    $errs = 0;
    $msg  = '';
    reset($args);
    foreach ($args as $k => $sid) {
        if ($sid > 0) {
            $name = $ns[$sid]['name'];
            $user = $ns[$sid]['username'];
            $srch = find_search($name, $user, $db);
            if ($srch) {
                $temp[] = $srch['id'];
            } else {
                $msg .= "error -- search ($sid) ($name) for user ($user) does not yet exist<br>\n";
            }
        }
    }
    if ($msg) {
        $msg = fontspeak($msg);
        echo "$msg\n";
    } else if ($temp) {
        $text = ',';
        reset($temp);
        foreach ($temp as $k => $id) {
            $text .= "$id,";
        }
    }
    return $text;
}



function update_asset($row, $nq, $now, $db)
{
    $name  = $row['name'];
    $user  = $row['username'];
    $qid   = $row['searchid'];

    $qname = $nq[$qid]['name'];
    $quser = $nq[$qid]['username'];
    $query = find_query($qname, $quser, $db);
    if ($query) {
        $row['searchid'] = $query['id'];
        $old = find_asset($name, $user, $db);
        if ($old) {
            $msg = "asset: $name already exists, not updating.";
        } else {
            $msg = "asset: create $name.";

            $row['id'] = 0;
            $row['last_run'] = 0;
            $row['created']  = $now;
            $row['modified'] = $now;
            asset_record($row, $db);
        }
    } else {
        $msg = "Error -- could not find query for asset report $name.";
    }
    $msg = fontspeak("$msg<br>");
    echo "$msg\n";
}


function update_report($row, $ns, $now, $db)
{
    $name  = $row['name'];
    $user  = $row['username'];
    $list  = $row['search_list'];
    $text  = make_report_list($list, $ns, $db);

    if ($text) {
        $row['search_list'] = $text;
        $old = find_report($name, $user, $db);
        if ($old) {
            $msg = "report: $name already exists, not updating.";
        } else {
            $msg = "report: create $name.";

            $row['id'] = 0;
            $row['created']  = $now;
            $row['modified'] = $now;
            $row['last_run'] = 0;
            report_record($row, $db);
        }
    } else {
        $msg = "Error -- could not find searches for report $name.";
    }
    $msg = fontspeak("$msg<br>");
    echo "$msg\n";
}


function update_global($env)
{
    $nu = $env['nu'];
    $ns = $env['ns'];
    $nr = $env['nr'];
    $nn = $env['nn'];
    $nq = $env['nq'];
    $na = $env['na'];
    $nc = $env['nc'];
    $db = $env['db'];
    $now = $env['now'];

    $cnu = safe_count($nu);
    $cns = safe_count($ns);
    $cnr = safe_count($nr);
    $cnn = safe_count($nn);
    $cnq = safe_count($nq);
    $cna = safe_count($na);
    $cnc = safe_count($nc);

    $msg  = "New Users: $cnu<br>";
    $msg .= "New Event Searches: $cns<br>";
    $msg .= "New Event Reports: $cnr<br>";
    $msg .= "New Event Notifications: $cnn<br>";
    $msg .= "New Asset Queries: $cnq<br>";
    $msg .= "New Asset Reports: $cna<br>";
    $msg .= "New Asset Criteria: $cnc<br>";
    $msg = "<p>$msg</p>";
    $msg = fontspeak($msg);
    echo "$msg\n";

    db_change($GLOBALS['PREFIX'] . 'core', $db);
    reset($nu);
    foreach ($nu as $key => $row) {
        update_user($row, $now, $db);
    }

    db_change($GLOBALS['PREFIX'] . 'event', $db);
    reset($ns);
    foreach ($ns as $id => $row) {
        update_search($row, $now, $db);
    }

    reset($nn);
    foreach ($nn as $id => $row) {
        update_notify($row, $ns, $now, $db);
    }

    reset($nr);
    foreach ($nr as $id => $row) {
        update_report($row, $ns, $now, $db);
    }

    db_change($GLOBALS['PREFIX'] . 'asset', $db);
    reset($nq);
    foreach ($nq as $id => $row) {
        update_query($row, $now, $db);
    }

    reset($na);
    foreach ($na as $id => $row) {
        update_asset($row, $nq, $now, $db);
    }

    reset($nc);
    foreach ($nc as $id => $row) {
        update_criteria($row, $nq, $now, $db);
    }
}


function global_owner($env)
{
    $db   = $env['db'];
    $user = $env['user'];
    $qu   = safe_addslashes($user);
    $sql  = "select * from Users where username = '$qu'";
    $row  = find_single($sql, $db);
    if ($row) {
        $u = "update";
        $w = "set username = '$qu' where global = 1";
        redcommand("$u SavedSearches $w", $db);
        PHP_REPF_UpdateDynamicList(CUR, constJavaListEventFilters);
        redcommand("$u Reports $w", $db);
        redcommand("$u Notifications $w", $db);
        redcommand("$u AssetReports $w", $db);
        redcommand("$u AssetSearches $w", $db);
        $sql  = "update Users set\n";
        $sql .= " priv_admin=1,\n";
        $sql .= " priv_search=1,\n";
        $sql .= " priv_report=1,\n";
        $sql .= " priv_notify=1,\n";
        $sql .= " priv_aquery=1,\n";
        $sql .= " priv_areport=1,\n";
        $sql .= " priv_config=1,\n";
        $sql .= " priv_updates=1,\n";
        $sql .= " priv_downloads=1\n";
        $sql .= " where username = '$qu'";
        redcommand($sql, $db);
    } else {
        $msg = "User $user does not exist.";
        $msg = fontspeak("<p><b>$msg</b></p>\n");
        echo $msg;
    }
}




function process($env)
{
    $db  = $env['db'];
    $now = $env['now'];
    $dlist = find_databases($db);
    $good = 0;
    $n = safe_count($dlist);
    if ($n > 0) {
        $msg = "mysql contains $n databases:<br>\n";
        reset($dlist);
        foreach ($dlist as $key => $data) {
            $msg .= "$key<br>\n";
        }
        echo fontspeak($msg);
    }
    $hfnlog  = @$dlist['hfnlog'];
    $msg = '';
    if ($hfnlog) {
        if (mysqli_select_db($db, hfnlog)) {
            $env['nu'] = load_users($db);
            $env['ns'] = load_search($db);
            $env['nr'] = load_report($db);
            $env['nn'] = load_notify($db);
            $env['na'] = load_asset($db);
            $env['nq'] = load_query($db);
            $env['nc'] = load_criteria($db);
            $good = 1;
        }
        if (mysqli_select_db($db, event)) {
            if ($good) {
                $reset = $env['reset'];
                $purge = $env['purge'];
                $user  = $env['user'];
                if ($reset) {
                    redcommand('delete from Reports', $db);
                    redcommand('delete from Notifications', $db);
                    redcommand('delete from SavedSearches', $db);
                    PHP_REPF_UpdateDynamicList(
                        CUR,
                        constJavaListEventFilters
                    );
                    db_change($GLOBALS['PREFIX'] . 'asset', $db);
                    redcommand('delete from AssetSearches', $db);
                    redcommand('delete from AssetSearchCriteria', $db);
                    redcommand('delete from AssetReports', $db);
                    db_change($GLOBALS['PREFIX'] . 'event', $db);
                }
                update_global($env);
            }
        } else {
            $msg = "unable to load records";
            $msg = "<p>$msg</p>";
            $msg = fontspeak($msg);
            echo "$msg\n";
        }
    } else {
        if (!$hfnlog)  $msg .= "database hfnlog does not exist<br>";
        $msg = "<p>$msg</p>";
        $msg = fontspeak($msg);
        echo "$msg\n";
    }
}


/*
    |  Main program
    */

$db   = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$now   = time();
$reset = intval(get_argument('reset', 0, 0));
$purge = intval(get_argument('purge', 0, 0));
$debug = intval(get_argument('debug', 0, 0));
$test  = intval(get_argument('test', 0, 0));
$user  = trim(get_argument('user', 0, ''));

$priv = ($db) ? user_info($db, $authuser, 'priv_debug', 0) : 0;
if ($priv) {
    $debug = ($debug) ? 1 : 0;
} else {
    $debug = 0;
}

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer) 
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users


$test_sql = $test;
$real_sql = (!$test_sql);

$env = array();
$env['db']    = $db;
$env['now']   = $now;
$env['user']  = $user;
$env['test']  = $test;
$env['purge'] = $purge;
$env['reset'] = $reset;
$env['debug'] = $debug;


if ($db) {
    process($env);
} else {
    $msg  = "The database is currently unavailable.  ";
    $msg .= "Please try again later.";
    $msg = fontspeak("<p><b>$msg</b></p>\n");
    echo $msg;
}

echo head_standard_html_footer($authuser, $db);
