<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
25-Oct-02   EWB     Created
29-Oct-02   EWB     Load asset data
30-Oct-02   EWB     Detect things getting removed
31-Oct-02   EWB     Show groups with ordered items grouped together.
 1-Nov-02   EWB     Attempt to detect when groups move.
 4-Nov-02   EWB     Sort results by group, ordinal, dataid
 4-Nov-02   EWB     changed old/new to action/value
 4-Nov-02   EWB     Link to dataid detail page.
 5-Nov-02   EWB     Improved links.
 6-Nov-02   EWB     Show start and stop times for each table.
 7-Nov-02   EWB     Difference between times.
 7-Nov-02   EWB     Merged differences.
 8-Nov-02   EWB     More differences between times.
 8-Nov-02   EWB     Machine discription table.
11-Nov-02   EWB     Form for params.
11-Nov-02   EWB     htmlspecialchars
13-Nov-02   EWB     Page title includes host name.
14-Nov-02   EWB     Allow machine selection from form.
19-Nov-02   EWB     Moved times calculation into it's own procedure.
19-Nov-02   EWB     Prompt change for Alex.
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navigation
10-Jan-03   EWB     Don't copy large arrays.
16-Jan-03   EWB     Access to $_SERVER variables.
10-Feb-03   EWB     Uses sandbox libraries.
11-Feb-03   EWB     db_change()
12-Feb-03   EWB     database factoring.
26-Feb-03   EWB     use auth not authuser.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header() 
19-Mar-03   NL      Move $debug initialization and surrounding lines above ob_ lines
10-Apr-03   EWB     Many Machine Report
15-Apr-03   EWB     Changes for entire site.
23-Apr-03   EWB     Uses site filters.
24-Apr-03   EWB     Jumptable for filtered sites.
25-Apr-03   EWB     machine_list()
29-Apr-03   EWB     Clean up access lists.
29-Apr-03   EWB     Select by Asset Query
30-Apr-03   EWB     User site filter.
30-Apr-03   EWB     Propogate dmin/dmax to next level.
 9-May-03   EWB     Show results of asset query.
 9-May-03   EWB     Added help for multi-select scroll box.
 9-May-03   EWB     Return of Select Sites.
12-May-03   EWB     Handle user without enabled sites.
12-May-03   EWB     Selected sites when only owns one site.
12-May-03   EWB     Mark as NOT cron job.
14-May-03   EWB     Don't show query contents after all.
15-May-03   EWB     Some minor factoring for asset change reports.
16-May-03   EWB     machine_list takes umin,umax,log as arguments.
22-May-03   NL      Change title to plural: "changes".
22-May-03   EWB     Quote Crusade.
19-Jun-03   EWB     Log Slow Queries
20-Jun-03   EWB     No Slave Database.
23-Jul-03   EWB     Set env['link'] as appropriate.
 7-Jan-04   EWB     l-qury requires l-cron (site_intersect).
 2-Sep-05   BJS     pass $env['SelectedAssetDataTableName'] to
                    query_query().
 7-Sep-05   BJS     removed drop_selected_table().
12-Sep-05   BJS     Added l-abld.php.
09-Nov-05   BJS     query_query() missing 4th arg.
15-Nov-05   BJS     added l-cnst.php
24-Jan-06   AAM     Bug 3072: change memory_limit setting to use max_php_mem_mb.
                    (Note that this change was actually moved from 4.2 to 4.3
                    on 06-Mar-06.)
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()                    

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-user.php');
include('../lib/l-alib.php');
include('../lib/l-abld.php');
include('../lib/l-qtbl.php');
include('../lib/l-date.php');
include('../lib/l-sets.php');
include('../lib/l-slct.php');
include('../lib/l-chng.php');
include('../lib/l-jump.php');
include('../lib/l-dids.php');
include('../lib/l-cron.php');
include('../lib/l-qury.php');
//  include ( '../lib/l-slav.php'  );
include('../lib/l-head.php');
include('../lib/l-cnst.php');
include('local.php');

function green($text)
{
    return "<font color=\"green\">$text</font>";
}

function plural($n, $name)
{
    $word = ($n == 1) ? $name : $name . 's';
    return "$n $word";
}

function dmindmax(&$env)
{
    $dmin = $env['dmin'];
    $dmax = $env['dmax'];

    $date = "&nbsp;&nbsp;<i>(mm/dd/yyyy)</i>";
    $emin = "<input type=\"text\" name=\"dmin\" value=\"$dmin\" size=\"20\">$date";
    $emax = "<input type=\"text\" name=\"dmax\" value=\"$dmax\" size=\"20\">$date";

    echo two_col('Enter Min Time', $emin);
    echo two_col('Enter Max Time', $emax);
}

function logoption($log)
{
    $logopt = array(
        0 => 'Start-End Difference',
        1 => 'Log of changes'
    );
    $log_select = html_select('log', $logopt, $log, 1);
    echo two_col('Output', $log_select);
}

function debug_select($priv, $debug)
{
    if ($priv) {
        $opt         = array('No', 'Yes');
        $dbg_select  = html_select('debug', $opt, $debug, 1);
        echo two_col(green('$debug'), $dbg_select);
    }
}

function submit()
{
    $submit = "<input type=\"submit\" value=\"submit\">";
    $reset  = "<input type=\"reset\" value=\"reset\">";
    echo two_col($submit, $reset);
}


function describe_machine(&$env)
{
    $db    = $env['db'];
    $mid   = $env['mid'];
    $log   = $env['log'];
    $self  = $env['self'];
    $umin  = $env['umin'];
    $umax  = $env['umax'];
    $head  = $env['title'];
    $priv  = $env['priv'];
    $debug = $env['debug'];
    $owned = $env['owned'];

    $site  = '';
    $host  = '';

    $events = 0;
    $head = "Single Machine";

    if ($mid) {
        $site  = $env['hosts'][$mid]['cust'];
        $host  = $env['hosts'][$mid]['host'];
        $smin  = $env['hosts'][$mid]['searliest'];
        $smax  = $env['hosts'][$mid]['slatest'];

        $mindate = datestring($smin);
        $maxdate = datestring($smax);

        $sql     = "select count(*) from AssetData where machineid=$mid";
        $records = count_records($sql, $db);
        $minhref = "detail.php?mid=$mid&when=$smin";
        $maxhref = "detail.php?mid=$mid&when=$smax";
        $minlink = html_link($minhref, $mindate);
        $maxlink = html_link($maxhref, $maxdate);

        $href = "detail.php?mid=$mid";
        $head = html_link($href, $host);

        $times = asset_times($mid, $smin, $smax, $db);

        $events = safe_count($times);

        $time_opt = array('default');

        $time   = $times;
        $time[] = $umin;
        $time[] = $umax;
        $times = sort_unique($time);

        reset($times);
        foreach ($times as $key => $data) {
            if ($data)
                $time_opt[$data] = date("m/d/Y H:i", $data);
            else
                $time_opt[$data] = "default";
        }
        $xmin = $time_opt;
        $xmax = $time_opt;
        $xmin[0] = "first log";
        $xmax[0] = "last log";

        unset($xmin[$smax]);
        unset($xmax[$smin]);

        $min_select = html_select('umin', $xmin, $umin, 1);
        $max_select = html_select('umax', $xmax, $umax, 1);
    }

    $machines = array();
    reset($owned);
    foreach ($owned as $xxx => $omid) {
        $machines[$omid] = $env['hosts'][$omid]['host'];
    }
    asort($machines);
    $mid_select = html_select('mid', $machines, $mid, 1);

    echo clear_rows();
    echo "<form method=\"post\" action=\"$self\">\n";
    if (($mid > 0) && (safe_count($machines) == 1)) {
        echo "<input type=\"hidden\" name=\"mid\" value=\"$mid\">\n";
    }
    echo table_start();
    echo table_head($head, 2);
    if ($site)
        echo asset_data(array('Site', $site));
    if (safe_count($machines) > 1) {
        $host = '<input type="text" name="host" size="32" maxlength="64">';
        echo two_col('Select Machine', $mid_select);
        echo two_col('Enter Machine', $host);
    }
    if ($mid) {
        echo two_col('Earliest Log Time', $minlink);
        echo two_col('Latest Log Time', $maxlink);
        echo two_col('Records', $records);
        echo two_col('Events', $events);
        if (3 <= $events) {
            echo two_col('Select Min Time', $min_select);
            echo two_col('Select Max Time', $max_select);
        }
    }

    dmindmax($env);
    logoption($log);
    submit();
    debug_select($priv, $debug);
    echo table_end();
    echo "<form>\n";
    echo clear_rows();

    $errs = '';
    if ((0 < $umax) && ($umax <= $umin))
        $errs .= "The specified time range is invalid.<br>\n";
    if ($mid <= 0)
        $errs .= "No machine specified.<br>\n";
    if ($errs) {
        echo "<p>$errs</p>";
        $events = 0;
    }
    return $events;
}


/*
    |  Figure out the machine in question givin it's name.
    |  Returns a valid machineid if the name uniquely identifies
    |  the machine.
    |
    |    $mid == 0: not found
    |    $mid  > 0: valid machineid
    |    $mid <  0: ambiguous name.
    |
    */

function asset_host($host, $access, $db)
{
    $mid    = 0;
    if ($host) {
        $qhost  = safe_addslashes($host);
        $sql    = "select * from Machine\n";
        $sql   .= " where host = '$qhost' and\n";
        $sql   .= " cust in ($access)";
        $list   = find_many($sql, $db);
        $num    = safe_count($list);
        if ($num == 1) {
            $data = reset($list);
            $mid  = $data['machineid'];
        }
        if ($num <= 0) {
            debug_note("$host: not found.");
            $mid = 0;
        }
        if (2 <= $num) {
            debug_note("$host: ambiguous, $num found");
            $mid = -1;
        }
    }
    return $mid;
}


function asset_census($carr, $db)
{
    $used = array();
    $list = array();
    $nums = array();
    $cids = array();
    $mids = array();
    if ($carr) {
        reset($carr);
        foreach ($carr as $cid => $site) {
            $nums[$site] = 0;
            $cids[$site] = $cid;
        }
        ksort($cids);
    }
    if (($cids) && ($carr)) {
        $access = db_access($carr);
        $sql  = "select * from Machine\n";
        $sql .= " where provisional = 0 and\n";
        $sql .= " cust in ($access)";
        $list = find_many($sql, $db);
    }
    if (($list) && ($nums)) {
        foreach ($list as $key => $row) {
            $site = $row['cust'];
            $host = $row['host'];
            $mid  = $row['machineid'];
            $nums[$site]++;
            $used[$site] = $cids[$site];
            $mids[$mid] = $host;
        }
        ksort($used);
        asort($mids);
    }
    $temp['used'] = $used;
    $temp['nums'] = $nums;
    $temp['mids'] = $mids;
    return $temp;
}


function empty_access(&$env)
{
    $fsize = safe_count($env['full']);
    $csize = safe_count($env['carr']);
    $msg = "You need access to at least one machine in order to use this page.";
    if ($fsize < 1) {
        $msg .= "<br>You do not own any sites.";
    } else if ($csize < 1) {
        $msg .= "<br>Please enable a site or disable site filtering.";
    }
    $msg = fontspeak($msg);
    echo "<p>$msg</p>\n";
}



function change_control(&$env)
{
    $cid  = $env['cid'];
    $mid  = $env['mid'];
    $log  = $env['log'];
    $used = $env['used'];
    $mids = $env['mids'];
    $self = $env['self'];
    $priv = $env['priv'];
    $carr = $env['carr'];
    $debug  = $env['debug'];
    $action = $env['action'];
    if ($used) {
        $blank = '     ';
        $tmp = array($blank);
        $mac = array($blank);
        reset($used);
        foreach ($used as $xsite => $xcid) {
            $tmp[$xcid] = $xsite;
        }
        foreach ($mids as $xmid => $xhost) {
            $mac[$xmid] = $xhost;
        }
        $active = safe_count($used);
        $opt['machine']  = 'just one machine';
        $opt['select']   = 'selected machines';
        $opt['one']      = 'just one site';
        $opt['site']     = 'selected sites';
        $opt['query']    = 'select machines by query';

        $act_select = html_select('action', $opt, $action, 1);
        if ($active <= 1) {
            $cid  = reset($used);
            $site = key($used);
            $cid_select = "$site<input type=\"hidden\" name=\"cid\" value=\"$cid\">";
        } else {
            $cid_select = html_select('cid', $tmp, $cid, 1);
        }
        $mid_select = html_select('mid', $mac, $mid, 1);

        $host = '<input type="text" name="host" size="32" maxlength="64">';
        echo "<form method=\"get\" action=\"$self\">\n";
        echo table_start();
        echo two_col('Select Display', $act_select);
        echo two_col('Select Site', $cid_select);
        echo two_col('Select Machine', $mid_select);
        echo two_col('Enter Machine', $host);
        echo two_col('Access', plural(safe_count($carr), 'site'));
        echo two_col('Active', plural($active, 'site'));
        echo two_col('Machines', plural(safe_count($mids), 'machine'));
        dmindmax($env);
        logoption($log);
        submit();
        debug_select($priv, $debug);
        echo table_end();
        echo "</form>\n";
    } else {
        empty_access($env);
    }
}


function change_machine(&$env)
{
    $mid  = $env['mid'];
    $log  = $env['log'];
    $umin = $env['umin'];
    $umax = $env['umax'];
    if (($env['names']) && ($mid)) {
        echo clear_rows();

        echo mark('index');

        $n = describe_machine($env);

        if ($n > 1) {
            if ($log)
                echo machine_changes($env, $mid, $umin, $umax);
            else
                echo machine_diff($env, $mid, $umin, $umax);
        }
        echo jumptable('top,bottom,index');
    } else {
        describe_machine($env);
    }
}



function change_unknown(&$env)
{
    $action = $env['action'];
    debug_note("unknown action: $action");
}

function select_scroll($name, $size, $mult, $options, $selected)
{
    $keys = array();
    reset($selected);
    foreach ($selected as $key => $data) {
        $keys[$data] = 1;
    }
    $mult = ($mult) ? ' multiple' : ' ';
    $msg = "<select$mult name=\"$name\" size=\"$size\">\n";
    reset($options);
    foreach ($options as $key => $data) {
        if (isset($keys[$data]))
            $msg .= "<option selected value=\"$key\">$data</option>\n";
        else
            $msg .= "<option value=\"$key\">$data</option>\n";
    }
    $msg .= "</select>\n";
    return $msg;
}


function scrolldoc($list)
{
    $doc  = <<< HERE
<i>
  To select multiple items, hold down 'ctrl' and click on <br>
  each item you want to select. To de-select an item, hold<br>
  down 'Ctrl'and click on the item you want to de-select.<br>
  To select contiguous items click on the first one, press<br>
  the 'Shift' key, click on the last one in the contiguous group.
</i>

HERE;
    $tmp  = "<table>\n";
    $tmp .= two_col($list, $doc);
    $tmp .= "</table>\n";
    return $tmp;
}


function change_select(&$env)
{
    debug_note("change_select");
    $log   = $env['log'];
    $mids  = $env['mids'];
    $self  = $env['self'];
    $priv  = $env['priv'];
    $debug = $env['debug'];

    $empty = array();
    $size = safe_count($mids);
    if ($size > 12) $size = 12;
    $list = select_scroll('list[]', $size, 1, $mids, $empty);
    echo "<form method=\"post\" action=\"$self\">\n";
    echo '<input type="hidden" name="action" value="list">' . "\n";
    echo table_start();
    echo table_head('Choose Machines', 2);
    dmindmax($env);
    logoption($log);
    echo two_col('Select Machines', scrolldoc($list));
    submit();
    debug_select($priv, $debug);
    echo table_end();
    echo "</form>\n";
}

function memhog($new)
{
    set_time_limit(0);
    if (function_exists('ini_set')) {
        $mem = server_def('max_php_mem_mb', '256', $db);
        $old = ini_set('memory_limit', $mem . 'M');
        debug_note("memory_limit $old --> $mem" . 'M');
    }
}


function change_list(&$env)
{
    debug_note("change_list");
    $db     = $env['db'];
    $log    = $env['log'];
    $list   = $env['list'];
    $user   = $env['user'];
    $umin   = $env['umin'];
    $umax   = $env['umax'];
    $access = $env['access'][$user];
    $count  = safe_count($list);
    $mach   = array();
    debug_note("$count machines found");
    if (($count) && ($access)) {
        $tmp  = join(',', $list);
        $sql  = "select * from Machine\n";
        $sql .= " where machineid in ($tmp) and\n";
        $sql .= " cust in ($access) order by";
        $sql .= " cust, host";
        $mach = find_many($sql, $db);
        memhog('32M');
    }
    echo machine_list($env, $mach, $umin, $umax, $log);
}


/*
    |  since $full is already restricted to sites the user is 
    |  allowed to access, we don't need to check with $access.
    |
    |   We allow this to work even if the specified site would
    |   normally be filtered out, since the user can get here
    |   via the back door in select sites, in the special case
    |   when he only owns a single site.
    */

function change_one(&$env)
{
    $cid    = $env['cid'];
    if ($cid > 0) {
        $full   = $env['full'];
        $umin   = $env['umin'];
        $umax   = $env['umax'];
        $log    = $env['log'];
        $mach   = array();
        $site   = @strval($full[$cid]);
        debug_note("change_one: cid:$cid site:$site");
        if ($site) {
            $db   = $env['db'];
            $qs   = safe_addslashes($site);
            $sql  = "select * from Machine\n";
            $sql .= " where cust = '$qs'\n";
            $sql .= " order by host";
            $mach = find_many($sql, $db);
            memhog('32M');
        }
        $count = safe_count($mach);
        debug_note("$count machines found");
        echo machine_list($env, $mach, $umin, $umax, $log);
    } else {
        debug_note("change_one: no site specified");
        $msg = "No site specified.<br>\n";
        echo $msg;
    }
}



function change_site(&$env)
{
    debug_note("change_site");
    $db    = $env['db'];
    $log   = $env['log'];
    $carr  = $env['carr'];
    $full  = $env['full'];
    $self  = $env['self'];
    $user  = $env['user'];
    $priv  = $env['priv'];
    $debug = $env['debug'];
    $select = array();

    $size = safe_count($full);
    if ($size > 0) {
        if ($size > safe_count($carr)) {
            $select = $carr;
        }

        echo "<form method=\"post\" action=\"$self\">\n";
        echo table_start();
        echo table_head("Choose Sites", 2);

        if ($size > 12) $size = 12;
        if ($size > 1) {
            echo "<input type=\"hidden\" name=\"action\" value=\"cids\">\n";
            $list = select_scroll('cids[]', $size, 1, $full, $select);
            echo two_col('Select Sites', scrolldoc($list));
        } else {
            $site  = reset($full);
            $cid   = key($full);
            echo "<input type=\"hidden\" name=\"action\" value=\"one\">\n";
            echo "<input type=\"hidden\" name=\"cid\" value=\"$cid\">\n";
            echo two_col('Select Sites', $site);
        }

        dmindmax($env);
        logoption($log);
        submit();
        debug_select($priv, $debug);
        echo table_end();
        echo "</form>\n\n";
    } else {
        empty_access($env);
    }
}


function asset_search($user, $db)
{
    $list = array();
    $sql  = "select name, id from AssetSearches where\n";
    $sql .= " username = '$user' or\n";
    $sql .= " global = 1 order by name, global";
    $search = find_many($sql, $db);
    if ($search) {
        $list[0] = '    ';
        $prev = '';
        reset($search);
        foreach ($search as $key => $row) {
            $id   = $row['id'];
            $name = $row['name'];
            if ($name != $prev) {
                $list[$id] = $name;
                $prev = $name;
            }
        }
    }
    return $list;
}


function change_query(&$env)
{
    debug_note("change_query");
    $db    = $env['db'];
    $log   = $env['log'];
    $mids  = $env['mids'];
    $self  = $env['self'];
    $priv  = $env['priv'];
    $user  = $env['user'];
    $debug = $env['debug'];
    $empty = array();

    $list = asset_search($user, $db);
    $size = safe_count($list);
    if ($size > 0) {
        $qid_select = html_select('qid', $list, 0, 1);
        echo <<< HERE

            <form method="post" action="$self">
            <input type="hidden" name="action" value="exec">
HERE;
        echo table_start();
        echo table_head("Select by Query", 2);
        dmindmax($env);
        logoption($log);
        echo two_col('Select Query', $qid_select);
        submit();
        debug_select($priv, $debug);
        echo table_end();
        echo "</form>\n\n";
    }
}

/*
    |  since $carr is already restricted to
    |  sites the user is allowed to access, we don't
    |  need to check with $access.
    */

function change_cids(&$env)
{
    $db     = $env['db'];
    $log    = $env['log'];
    $cid    = $env['cid'];
    $cids   = $env['cids'];
    $umin   = $env['umin'];
    $umax   = $env['umax'];
    $full   = $env['full'];
    $mach   = array();
    $list   = array();
    debug_note("change_cids");

    if ($cids) {
        reset($cids);
        foreach ($cids as $key => $cid) {
            $site = @$full[$cid];
            if ($site) {
                debug_note("include site $site");
                $list[] = $site;
            }
        }
    }
    if ($list) {
        $access = db_access($list);
        $sql  = "select * from Machine\n";
        $sql .= " where cust in ($access)\n";
        $sql .= " order by cust, host";
        $mach = find_many($sql, $db);
        memhog('32M');
    }
    $count = safe_count($mach);
    debug_note("$count machines found");
    echo machine_list($env, $mach, $umin, $umax, $log);
}


function change_exec(&$env)
{
    $db   = $env['db'];
    $qid  = $env['qid'];
    $log  = $env['log'];
    $umin = $env['umin'];
    $umax = $env['umax'];
    if ($qid > 0) {
        $user   = $env['user'];
        $access = $env['access'][$user];

        // query_query() needs the table name
        $tbl = create_pidtable_name('SelectedAssetData', $db);
        $env['SelectedAssetDataTableName'] = $tbl;

        debug_note("change_exec qid:$qid user:$user");
        $q = query_query($env, $user, $qid, array());
        $mids = $q['mids'];
        $text = $q['text'];
        $name = $q['name'];
        $when = $q['when'];
        $msg = asset_header($name);

        // remove the table
        $sql = "drop table $tbl";
        redcommand($sql, $db);

        if ($text) {
            $msg .= show_description($text);
        }

        if ($when) {
            $msg .= show_when($when);
        }

        if ($mids) {
            $many  = plural(safe_count($mids), 'machine');
            $msg  .= "Query found $many.";
        }
        echo $msg;

        echo clear_rows();

        $mach = array();
        if (($mids) && ($access)) {
            $tmp  = join(',', $mids);
            $sql  = "select * from Machine\n";
            $sql .= " where machineid in ($tmp) and\n";
            $sql .= " cust in ($access) order by";
            $sql .= " cust, host";
            $mach = find_many($sql, $db);
            memhog('32M');
        }
        $count = safe_count($mach);
        debug_note("$count machines found");
        echo machine_list($env, $mach, $umin, $umax, $log);
    } else {
        $msg = "No query specified.<br>\n";
        echo $msg;
    }
}

/*
    |  Main program
    */

$now = time();
$db  = db_connect();

/************************
    $mdb = db_connect();
    $sdb = db_slave($mdb);
    if ($sdb)
    {
        db_change($GLOBALS['PREFIX'].'core',$sdb);
        $db = $sdb;
    }
    else
    {
        $db = $mdb;
    }
 ************************/

$authuser = process_login($db);
$comp = component_installed();
$user = user_data($authuser, $db);

//  $o1   = intval(get_argument('o1',0,0));
//  $o2   = intval(get_argument('o2',0,0));
//  $o3   = intval(get_argument('o3',0,0));
//  $o4   = intval(get_argument('o4',0,0));
$mid  = intval(get_argument('mid', 0, 0));
$cid  = intval(get_argument('cid', 0, 0));
$did  = intval(get_argument('did', 0, 0));
$qid  = intval(get_argument('qid', 0, 0));
$log  = intval(get_argument('log', 0, 1));
$umin = intval(get_argument('umin', 0, 0));
$umax = intval(get_argument('umax', 0, 0));
$link = intval(get_argument('link', 0, 1));
$dbg  = intval(get_argument('debug', 0, 0));
$act  = ($mid) ? 'machine' : 'control';

$empty  = array();
$action = trim(get_argument('action', 0, $act));
$dmin   = trim(get_argument('dmin', 0, ''));
$dmax   = trim(get_argument('dmax', 0, ''));
$host   = trim(get_argument('host', 0, ''));
$list   = get_argument('list', 0, '', $empty);
$cids   = get_argument('cids', 0, '', $empty);

$self = server_var('PHP_SELF');

$filter = @($user['filtersites']) ? 1 : 0;
$dbpriv = @($user['priv_debug']) ?  1 : 0;
$debug  = ($dbpriv) ? $dbg : 0;

/************************************************
    if ($sdb)
        debug_note("replicated database, mdb:$mdb, sdb:$sdb");
    else
        debug_note("normal database");
 ************************************************/

$title = '';
$full  = site_array($authuser, 0, $db);

if ($filter)
    $carr = site_array($authuser, $filter, $db);
else
    $carr = $full;
$access = db_access($carr);


db_change($GLOBALS['PREFIX'] . 'asset', $db);
if (($host) && ($access)) {
    $mid = asset_host($host, $access, $db);
    if ($mid <= 0) {
        $host = '';
    }
}

$temp = asset_census($carr, $db);
$used = $temp['used'];
$mids = $temp['mids'];
$nums = $temp['nums'];


$names = asset_names($db);
$hosts = asset_machines($db);
$owned = asset_access($access, $db);
db_change($GLOBALS['PREFIX'] . 'core', $db);

$slow = (float) server_def('slow_query_asset', 20, $db);

if ($mid) {
    $mids = array($mid);
    $found = intersect($mids, $owned);
    if ($found)
        $host = @$hosts[$mid]['host'];
    else
        $mid = 0;
}

if ($host)
    $title = ucwords("$host Asset Changes");
else
    $title = "Asset Changes";

if ($dmin != '') $umin = parsedate($dmin, $now);
if ($dmax != '') $umax = parsedate($dmax, $now);
if ($umin <=  0) $dmin = '';
if ($umax <=  0) $dmax = '';


//  $ords = array($o1,$o2,$o3,$o4);
//  reset($ords);

//  foreach ($ords as $k => $o)
//  {
//      $good = false;
//      if (0 <= $o)
//      {
//          if ($o)
//          {
//              $name = @ $names[$o]['name'];
//              if ($name)
//              {
//                  debug_note("sorting by $name");
//                  $good = true;
//              }
//          }
//          else
//          {
//              $good = true;
//          }
//      }
//      if ((!$good) && ($o > 0))
//      {
//          debug_note("invalid sort $o");
//          $ords[$k] = 0;
//      }
//  }

$temp = array();
$temp[$authuser] = $access;
$env = array();
$env['db'] = $db;
//  $env['o1'] = $ords[0];
//  $env['o2'] = $ords[1];
//  $env['o3'] = $ords[2];
//  $env['o4'] = $ords[3];
$env['mid'] = $mid;
$env['cid'] = $cid;
$env['qid'] = $qid;
$env['log'] = $log;
$env['now'] = $now;
$env['base'] = '';
$env['link'] = $link;
$env['dmin'] = $dmin;
$env['dmax'] = $dmax;
$env['umin'] = $umin;
$env['umax'] = $umax;
$env['self'] = $comp['self'];
$env['carr'] = $carr;
$env['full'] = $full;
$env['cids'] = $cids;
$env['mids'] = $mids;
$env['list'] = $list;
$env['cron'] = 0;
$env['file'] = 0;
$env['user'] = $authuser;
$env['site'] = '';
$env['host'] = $host;
$env['slow'] = $slow;
$env['dbid'] = 'master';
$env['used'] = $used;
$env['priv'] = $dbpriv;
$env['names'] = $names;
$env['hosts'] = $hosts;
$env['debug'] = $debug;
$env['owned'] = $owned;
$env['title'] = $title;
$env['table'] = $comp['self'];
$env['action'] = $action;
$env['access'] = $temp;

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer) 
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users  

db_change($GLOBALS['PREFIX'] . 'asset', $db);

debug_note("action:$action user:$authuser");

switch ($action) {
    case 'control':
        change_control($env);
        break;
    case 'machine':
        change_machine($env);
        break;
    case 'select':
        change_select($env);
        break;
    case 'query':
        change_query($env);
        break;
    case 'list':
        change_list($env);
        break;
    case 'site':
        change_site($env);
        break;
    case 'exec':
        change_exec($env);
        break;
    case 'cids':
        change_cids($env);
        break;
    case 'one':
        change_one($env);
        break;
    default:
        change_unknown($env);
        break;
}

db_change($GLOBALS['PREFIX'] . 'core', $db);

echo head_standard_html_footer($authuser, $db);
