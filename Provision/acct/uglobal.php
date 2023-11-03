<?php

/*
Revision History

16-Dec-02   EWB     Created
17-Dec-02   EWB     Update search
18-Dec-02   EWB     Work in 3.0.xx directory structure
19-Dec-02   EWB     Added global user command
19-Dec-02   EWB     Added Asset Report, Asset Query, AssetCriteria
 2-Jan-03   EWB     Don't update existing notifications
 2-Jan-03   EWB     Don't update existing reports
 2-Jan-03   EWB     New notifications: email:0, console:1.
 5-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
19-Mar-03   NL      Move $debug initialization and surrounding lines above ob_ lines
30-Jun-03   EWB     Update 3.1 server to 3.2
 8-Jul-03   EWB     Table output
10-Jul-03   EWB     Asset Search Criteria.
10-Jul-03   EWB     Update created/modified.
17-Jul-03   EWB     Added File and Links
21-Aug-03   EWB     update sends logfile.
22-Aug-03   EWB     Regime Change
25-Aug-03   EWB     Purge Backups
26-Aug-03   EWB     AssetSearches, not AssetSavedSearches
27-Aug-03   EWB     Added more information to report.
28-Aug-03   EWB     Page now requires admin access, reset clears console
28-Aug-03   EWB     Backup report filters along with their reports.
11-Sep-03   EWB     RptSiteFilters not the same between assets and events.
24-Nov-03   EWB     Reports.next_run, Reports.assetlinks, AssetReports.next_run
 9-Jan-04   EWB     Server Name.
 9-Apr-04   EWB     this_run, report retries.
20-Jul-04   EWB     remove 'Monthly System Maintenance Report Failures'
20-Jul-04   EWB     remove 'Weekly System Maintenance Report Failures'
 8-Oct-04   EWB     new notification schedule.
27-Oct-04   BJS     Added include_user, include_text and subject_text to Event/Asset Reports.
20-Dec-04   BJS     Added skip_owner to notify_record, event_record & asset_record.
21-Jan-05   BJS     added: asset.AssetReport.tabular
 1-Jun-05   BJS     added: asset.AssetReports.translated, AssetSearchCriteria.translated
                           AssetSearches.translated.
 7-Jun-05   BJS     added: event.Reports.omit.
19-Jul-05   BJS     added: event.Reports.detaillinks.
 1-Aug-05   EWB     remove: event.Notifications.machines / excluded
                    added: event.Notifications.ginclude / gexclude / gsuspend
                    added: event.Reports.ginclude / gexclude
                    added: asset.AssetReports.ginclude / gexclude
17-Aug-05   BJS     added: asset.AssetReports.xmlurl, xmluser, xmlpass.
18-Aug-05   BJS     added: asset.AssetReports.xmlfile.
25-Aug-05   BJS     added: asset.AssetReports.xmlpasv.
08-Nov-05   BJS     replaced: event.Reports.ginclude/gexclude w/group_include,
                    group_exclude, event.Notifications.ginclude/gexclude/gsuspend,
                    group_include, group_exclude, group_suspend.
09-Nov-05   BJS     Removed event.NotSiteFilters/RptSiteFilters.
05-Dec-05   BJS     Removed asset.RptSiteFilters, filter_record()
                    remove_report_filters() & report_filter().
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
21-Mar-07   AAM/RWM Fixed ginclude/gexclude in AssetReports.
03-May-07   BTE     Externalized find_global_owner and load_search (renamed to
                    load_search_global).
04-Jun-07   BTE     Added calls to PHP_REPF_UpdateDynamicList.
06-Jun-07   BTE     Added code to support SavedSearches.searchuniq.
27-Jun-07   BTE     Bug 4198: Fix searchuniq for global SavedSearches.
31-Jul-07   BTE     asrchuniq support and universal unique function.

*/


/*
From amiller@handsfreenetworks.com  Tue Jul 15 17:15:33 2003
Message-ID: <0d2101c34b2d$f52b4110$0f00a8c0@napa>
From: "Allan Miller" <amiller@handsfreenetworks.com>
To: "Eric W. Brown" <ebrown@handsfreenetworks.com>
References: <20030710192643.A4415@cthulhu.pacbell.net>
Subject: Re: The uglobal problem
Date: Tue, 15 Jul 2003 20:04:30 -0400

Eric, Alex:

OK, I've talked with both of you and I think I've figured out what
we need to do.  Here's as concise a summary as I can make.
See if you agree, and let's take it from there.

For all the new global items:

1. If no global item exists with that name, then the new item is added.
When a new notification is added, the "email" field is always set to
FALSE no matter what the new item has set.

2. Otherwise, a global item exists with that name.  If all of the "update"
fields in the item are the same as the corresponding fields in the new
item, then the item remains unchanged.

3. Otherwise, there is a difference in at least one "update" field.  If the
user has edited the item (see note 1), then the item remains unchanged.

4. Otherwise, we need to update the item.  The existing item is copied
to a new one with "Old:" prepended to the beginning of its name.  Then,
all of the "update" fields are copied from the new item to the existing item.


The "update" fields are as follows:

Searches -
    searchstring

Notifications -
(None.  Note that this means that test #2 is always true, so notifications
are never updated, only new ones are added by step #1.)

Reports -
    order1
    order2
    order3
    order4
    details
    search_list   [I think -- Eric, this is the list of filters to use, right?]
    config  [I think -- Eric, this is the list of fields to include, right?]

Asset Reports -
    order1
    order2
    order3
    order4
    search_list   [I think -- Eric, this is the list of filters to use, right?]
    config  [I think -- Eric, this is the list of fields to include, right?]

*/

$title = 'Database Global Update';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-user.php');
include('../lib/l-base.php');
include('../lib/l-head.php');
include('../lib/l-cnst.php');
include('../lib/l-gsql.php');


function table_start()
{
    return '<table align="left" border="2" cellspacing="2" cellpadding="2">';
}

function table_data($args)
{
    $msg = '';
    if ($args) {
        $msg .= "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            $msg .= " <td>$data</td>\n";
        }
        $msg .= "</tr>\n";
    }
    return $msg;
}

function table_head($args)
{
    $msg = '';
    if ($args) {
        $msg .= "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            $msg .= " <th>$data</th>\n";
        }
        $msg .= "</tr>\n";
    }
    return $msg;
}


function display_out($out)
{
    $txt = '';
    if ($out) {
        reset($out);
        $head = explode(',', 'table,name,user,action');
        echo table_start();
        echo table_head($head);
        foreach ($out as $key => $row) {
            $name = $row['name'];
            $user = $row['user'];
            $action = $row['action'];
            $table = $row['table'];
            $args = array($table, $name, $user, $action);
            echo table_data($args);
            $txt .= "$table ($action) $name\n";
        }
        echo "</table><br>\n";
        echo '<br clear="all">';
        echo "\n\n\n";
        $txt .= "\n\n\n";
    }
    return $txt;
}

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


function load_report($db)
{
    $sql = "select * from Reports where global = 1";
    return find_several($sql, $db);
}

function load_notify($db)
{
    $sql = "select * from Notifications where global = 1";
    return find_several($sql, $db);;
}

function load_asset($db)
{
    $sql = "select * from AssetReports where global = 1";
    return find_several($sql, $db);
}

function load_query($db)
{
    $sql = "select * from AssetSearches where global = 1";
    return find_several($sql, $db);
}

function load_criteria($db)
{
    $arr = array();
    $sql = "select * from AssetSearchCriteria";
    $tmp = find_several($sql, $db);
    if ($tmp) {
        reset($tmp);
        foreach ($tmp as $key => $row) {
            $qid = $row['assetsearchid'];
            $cid = $row['id'];
            $arr[$qid][$cid] = $row;
        }
    }
    return $arr;
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


function find_name($name, $table, $db)
{
    $row  = array();
    $qn   = safe_addslashes($name);
    $sql  = "select * from $table where";
    $sql .= " (name = '$qn') and";
    $sql .= " (global = 1)";
    return find_single($sql, $db);
}


function find_local($name, $table, $owner, $db)
{
    $qn   = safe_addslashes($name);
    $qu   = safe_addslashes($owner);
    $sql  = "select * from $table where\n";
    $sql .= " username = '$qu' and\n";
    $sql .= " name = '$qn' and\n";
    $sql .= " global = 0";
    return find_single($sql, $db);
}


function find_id($id, $table, $db)
{
    $sql = "select * from $table where id = $id";
    return find_single($sql, $db);
}

function find_report($name, $db)
{
    return find_name($name, 'Reports', $db);
}

function find_asset($name, $db)
{
    return find_name($name, 'AssetReports', $db);
}

function find_search($name, $db)
{
    return find_name($name, 'SavedSearches', $db);
}

function find_query($name, $db)
{
    return find_name($name, 'AssetSearches', $db);
}

function find_notify($name, $db)
{
    return find_name($name, 'Notifications', $db);
}

function find_local_search($name, $user, $db)
{
    return find_local($name, 'SavedSearches', $user, $db);
}

function find_local_notify($name, $user, $db)
{
    return find_local($name, 'Notifications', $user, $db);
}


function notify_record($row, $db)
{
    $id        = $row['id'];
    $gbl       = $row['global'];
    $ntype     = $row['ntype'];
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
    $next_run  = $row['next_run'];
    $this_run  = @intval($row['this_run']);
    $suspend   = $row['suspend'];
    $retries   = $row['retries'];
    $enabled   = $row['enabled'];
    $group_include  = $row['group_include'];
    $group_exclude  = $row['group_exclude'];
    $group_suspend  = $row['group_suspend'];
    $links     = $row['links'];
    $crt       = $row['created'];
    $mod       = $row['modified'];
    $skip      = $row['skip_owner'];

    $qn  = safe_addslashes($row['name']);
    $qc  = safe_addslashes($row['config']);
    $qu  = safe_addslashes($row['username']);
    $ql  = safe_addslashes($row['emaillist']);

    $cmd = ($id) ? 'update' : 'insert into';
    $sql = "$cmd Notifications set\n"
        . " global = $gbl,\n"
        . " ntype = $ntype,\n"
        . " priority = $priority,\n"
        . " name = '$qn',\n"
        . " username = '$qu',\n"
        . " days = $days,\n"
        . " solo = $solo,\n"
        . " console = $console,\n"
        . " email = $email,\n"
        . " emaillist = '$ql',\n"
        . " defmail = $defmail,\n"
        . " search_id = $search_id,\n"
        . " seconds = $seconds,\n"
        . " threshold = $threshold,\n"
        . " last_run = $last_run,\n"
        . " next_run = $next_run,\n"
        . " this_run = $this_run,\n"
        . " suspend = $suspend,\n"
        . " retries = $retries,\n"
        . " config = '$qc',\n"
        . " enabled = $enabled,\n"
        . " links = $links,\n"
        . " created = $crt,\n"
        . " modified = $mod,\n"
        . " group_include = '$group_include',\n"
        . " group_exclude = '$group_exclude',\n"
        . " group_suspend = '$group_suspend',\n"
        . " skip_owner = $skip";
    if ($id) $sql .= "\n where id = $id";
    return redcommand($sql, $db);
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
    $qt =   safe_addslashes($row['subject_text']);

    $id        = $row['id'];
    $gbl       = $row['global'];
    $cycle     = $row['cycle'];
    $defmail   = $row['defmail'];
    $file      = $row['file'];
    $last_run  = $row['last_run'];
    $next_run  = $row['next_run'];
    $this_run  = @intval($row['this_run']);
    $enabled   = $row['enabled'];
    $links     = $row['links'];
    $alinks    = $row['assetlinks'];
    $details   = $row['details'];
    $crt       = $row['created'];
    $mod       = $row['modified'];
    $group_include  = $row['group_include'];
    $group_exclude  = $row['group_exclude'];
    $try       = @intval($row['retries']);
    $skip      = $row['skip_owner'];
    $omit      = $row['omit'];
    $detaillinks  = $row['detaillinks'];
    $include_user = $row['include_user'];
    $include_text = $row['include_text'];

    $hour      = @intval($row['hour']);
    $minute    = @intval($row['minute']);
    $wday      = @intval($row['wday']);
    $mday      = @intval($row['mday']);
    $umin      = @intval($row['umin']);
    $umax      = @intval($row['umax']);

    $cmd = ($id) ? 'update' : 'insert into';
    $sql = "$cmd Reports set\n"
        . " global = $gbl,\n"
        . " name = '$qn',\n"
        . " username = '$qu',\n"
        . " emaillist = '$ql',\n"
        . " defmail = $defmail,\n"
        . " file = $file,\n"
        . " format = '$qf',\n"
        . " cycle = $cycle,\n"
        . " hour = $hour,\n"
        . " minute = $minute,\n"
        . " wday = $wday,\n"
        . " mday = $mday,\n"
        . " enabled = $enabled,\n"
        . " links = $links,\n"
        . " assetlinks = $alinks,\n"
        . " last_run = $last_run,\n"
        . " next_run = $next_run,\n"
        . " this_run = $this_run,\n"
        . " order1 = '$q1',\n"
        . " order2 = '$q2',\n"
        . " order3 = '$q3',\n"
        . " order4 = '$q4',\n"
        . " details = $details,\n"
        . " umin = $umin,\n"
        . " umax = $umax,\n"
        . " config = '$qc',\n"
        . " search_list = '$qs',\n"
        . " created = $crt,\n"
        . " modified = $mod,\n"
        . " group_include = '$group_include',\n"
        . " group_exclude = '$group_exclude',\n"
        . " retries = $try,\n"
        . " include_user = $include_user,\n"
        . " include_text = $include_text,\n"
        . " subject_text = '$qt',\n"
        . " skip_owner = $skip,\n"
        . " omit = $omit,\n"
        . " detaillinks = $detaillinks";
    if ($id) $sql .= "\n where id = $id";
    return redcommand($sql, $db);
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
    $qt =   safe_addslashes($row['subject_text']);
    $qurl = safe_addslashes($row['xmlurl']);
    $qusr = safe_addslashes($row['xmluser']);
    $qpas = safe_addslashes($row['xmlpass']);
    $qfil = safe_addslashes($row['xmlfile']);

    $id        = $row['id'];
    $gbl       = $row['global'];
    $cycle     = $row['cycle'];
    $defmail   = $row['defmail'];
    $file      = $row['file'];
    $last_run  = $row['last_run'];
    $next_run  = $row['next_run'];
    $this_run  = @intval($row['this_run']);
    $enabled   = $row['enabled'];
    $links     = $row['links'];
    $log       = $row['log'];
    $crt       = $row['created'];
    $mod       = $row['modified'];
    $group_include = $row['group_include'];
    $group_exclude = $row['group_exclude'];
    $try       = @intval($row['retries']);
    $hour      = @intval($row['hour']);
    $minute    = @intval($row['minute']);
    $wday      = @intval($row['wday']);
    $mday      = @intval($row['mday']);

    $sid       = @intval($row['searchid']);
    $content   = @intval($row['content']);
    $change    = @intval($row['change_rpt']);
    $umin      = @intval($row['umin']);
    $umax      = @intval($row['umax']);
    $include_user = $row['include_user'];
    $include_text = $row['include_text'];
    $skip         = $row['skip_owner'];
    $tabular      = $row['tabular'];
    $xmlpasv      = $row['xmlpasv'];

    /* Use the "All" group for group_include. */
    $sql = "select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups" .
        " where name='All' and human=0";
    $row = find_single($sql, $db);
    $group_include = $row[0];

    $cmd = ($id) ? 'update' : 'insert into';
    $sql = "$cmd AssetReports set\n"
        . " global = $gbl,\n"
        . " name = '$qn',\n"
        . " username = '$qu',\n"
        . " emaillist = '$ql',\n"
        . " defmail = $defmail,\n"
        . " file = $file,\n"
        . " format = '$qf',\n"
        . " cycle = $cycle,\n"
        . " hour = $hour,\n"
        . " minute = $minute,\n"
        . " wday = $wday,\n"
        . " mday = $mday,\n"
        . " enabled = $enabled,\n"
        . " links = $links,\n"
        . " last_run = $last_run,\n"
        . " next_run = $next_run,\n"
        . " this_run = $this_run,\n"
        . " order1 = '$q1',\n"
        . " order2 = '$q2',\n"
        . " order3 = '$q3',\n"
        . " order4 = '$q4',\n"
        . " searchid = $sid,\n"
        . " change_rpt = $change,\n"
        . " content = $content,\n"
        . " umax = $umax,\n"
        . " umin = $umin,\n"
        . " log = $log,\n"
        . " created = $crt,\n"
        . " modified = $mod,\n"
        . " group_include = '$group_include',\n"
        . " group_exclude = '$group_exclude',\n"
        . " retries = $try,\n"
        . " include_user = $include_user,\n"
        . " include_text = $include_text,\n"
        . " subject_text = '$qt',\n"
        . " skip_owner = $skip,\n"
        . " tabular = $tabular,\n"
        . " translated = 0,\n"
        . " xmlurl  = '$qurl',\n"
        . " xmluser = '$qusr',\n"
        . " xmlpass = '$qpas',\n"
        . " xmlfile = '$qfil',\n"
        . " xmlpasv = $xmlpasv";
    if ($id) $sql .= "\n where id = $id";
    return redcommand($sql, $db);
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
    $crt = $row['created'];
    $mod = $row['modified'];

    $dc  = @intval($row['date_code']);
    $dv  = @intval($row['date_value']);
    $rs  = @intval($row['row_size']);
    $xp  = @intval($row['expires']);

    $sql  = ($id) ? 'update' : 'insert into';
    $sql .= "\n AssetSearches set\n";
    $sql .= " global = $gbl,\n";
    $sql .= " name = '$qn',\n";
    $sql .= " searchstring = '$qs',\n";
    $sql .= " username = '$qu',\n";
    $sql .= " displayfields = '$qf',\n";
    $sql .= " date_code = $dc,\n";
    $sql .= " date_value = $dv,\n";
    $sql .= " rowsize = $rs,\n";
    $sql .= " refresh = '$qr',\n";
    $sql .= " expires = $xp,\n";
    $sql .= " created = $crt,\n";
    $sql .= " modified = $mod,\n";
    $sql .= " translated = 0";
    $asrchuniq = USER_GenerateManagedUniq(
        $row['name'],
        $row['username'],
        $db
    );
    $sql .= ",\n asrchuniq = '$asrchuniq'";
    if ($id) $sql .= "\n where id = $id";
    return redcommand($sql, $db);
}

function search_record($row, $db)
{
    $id  = $row['id'];
    $gbl = $row['global'];
    $crt = $row['created'];
    $mod = $row['modified'];

    $qs  = safe_addslashes($row['searchstring']);
    $qn  = safe_addslashes($row['name']);
    $qu  = safe_addslashes($row['username']);

    $sql  = ($id) ? 'update' : 'insert into';
    $sql .= "\n SavedSearches set\n";
    $sql .= " global = $gbl,\n";
    $sql .= " username = '$qu',\n";
    $sql .= " name = '$qn',\n";
    $sql .= " searchstring = '$qs',\n";
    $sql .= " created = $crt,\n";
    $sql .= " modified = $mod";
    /* Yes, we have to change searchuniq in case the name is changing -
            otherwise there will be a conflict. */
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

    /* Probably:
            PHP_REPF_UpdateDynamicList(CUR, constJavaListEventFilters); */
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
    $sql .= " expires = $xp,\n";
    $sql .= " translated = 0";
    if ($id) $sql .= "\n where id = $id";
    return redcommand($sql, $db);
}


/*
     |  0 -- no update, user modified
     |  1 -- already exists, no update
     |  2 -- identical, no update
     |  3 -- aparently unchanged
     |  4 -- update with backup
     |  5 -- created new
     |  6 -- error
     */

function compare_out($a, $b)
{
    $ac = $a['code'];
    $bc = $b['code'];
    $an = $a['name'];
    $bn = $b['name'];
    $at = $a['table'];
    $bt = $b['table'];
    $aa = $a['action'];
    $ba = $b['action'];
    if ($ac != $bc)
        $cmp = ($ac > $bc) ? 1 : -1;
    else if ($an != $bn)
        $cmp = ($an > $bn) ? 1 : -1;
    else if ($at != $bt)
        $cmp = ($at > $bt) ? 1 : -1;
    else if ($aa != $ba)
        $cmp = ($aa > $ba) ? 1 : -1;
    else
        $cmp = 0;
    return $cmp;
}


function oldname($name)
{
    return "Old:$name";
}


function update_search(&$env, $row, &$out, $db)
{
    $name = $row['name'];
    $user = $env['user'];
    $tab  = 'SavedSearches';

    $temp = array();
    $temp['code']   = 0;
    $temp['name']   = $name;
    $temp['user']   = $user;
    $temp['table']  = $tab;
    $temp['action'] = 'undefined';

    $old = find_search($name, $db);
    $lcl = find_local_search($name, $user, $db);
    if ($lcl) {
        $temp['action'] = 'local, no update';
        $temp['code']   = 0;
    } else if ($old) {
        $nss = $row['searchstring'];
        $oss = $old['searchstring'];
        $ocd = $old['created'];
        $omd = $old['modified'];
        if ($oss == $nss) {
            $temp['user']   = $old['username'];
            $temp['action'] = 'identical';
            $temp['code']   = 2;
        } else if ($ocd != $omd) {
            $temp['user']   = $old['username'];
            $temp['action'] = 'modified, no update';
            $temp['code']   = 0;
        } else {
            $oname = oldname($name);
            $bak = $old;
            $rpm = find_search($oname, $db);
            $bak['id']   = ($rpm) ? $rpm['id'] : 0;
            $bak['name'] = $oname;
            search_record($bak, $db);

            $old['searchstring'] = $row['searchstring'];
            $old['created']      = $row['created'];
            $old['modified']     = $row['modified'];
            search_record($old, $db);

            $temp['user']   = $old['username'];
            $temp['action'] = 'backup, update';
            $temp['code']   = 4;
        }
    } else {
        $row['id'] = 0;
        $row['username'] = $user;
        search_record($row, $db);
        $temp['action'] = 'created';
        $temp['user'] = $user;
        $temp['code'] = 5;
    }
    $out[] = $temp;
}


function create_new_criteria($name, $list, &$out, $db)
{
    $new = find_query($name, $db);
    if (($new) && ($list)) {
        $qid = $new['id'];
        $tmp = array();
        $tmp['user']   = $new['username'];
        $tmp['table']  = 'AssetSearchCriteria';
        $tmp['action'] = 'created';
        $tmp['code']   = 5;
        foreach ($list as $cid => $row) {
            $fld = $row['fieldname'];
            $val = $row['value'];
            $row['id'] = 0;
            $row['assetsearchid'] = $qid;
            $tmp['name'] = "$name <i>$fld</i> ($val)";
            criteria_record($row, $db);
            $out[] = $tmp;
        }
    }
}


function remove_query($qid, $db)
{
    if ($qid > 0) {
        $sql  = "delete from AssetSearches\n";
        $sql .= " where id = $qid";
        redcommand($sql, $db);
        $sql  = "delete from AssetSearchCriteria\n";
        $sql .= " where assetsearchid = $qid";
        redcommand($sql, $db);
    }
}


function remove_report($rid, $db)
{
    if ($rid > 0) {
        $sql  = "delete from Reports\n";
        $sql .= " where id = $rid";
        redcommand($sql, $db);
    }
}

function remove_asset($rid, $db)
{
    if ($rid > 0) {
        $sql  = "delete from AssetReports\n";
        $sql .= " where id = $rid";
        redcommand($sql, $db);
    }
}

function remove_notify($nid, $db)
{
    if ($nid > 0) {
        $sql  = "delete from Notifications\n";
        $sql .= " where id = $nid";
        redcommand($sql, $db);
    }
}


/*
    |  criteria live and die along with their associated queries.
    |  and are not seperately updated.
    */

function update_query(&$env, $row, $crit, &$out, $db)
{
    $user  = $env['user'];
    $name  = $row['name'];
    $qid   = $row['id'];
    $tab   = 'AssetSearches';

    $temp  = array();
    $temp['code']   = 0;
    $temp['name']   = $name;
    $temp['user']   = $user;
    $temp['table']  = $tab;
    $temp['action'] = 'undefined';

    $old = find_query($name, $db);
    $lcl = find_local($name, $tab, $user, $db);
    if ($lcl) {
        $temp['action'] = 'local, no update';
        $temp['code']   = 0;
    } else if ($old) {
        $nss = $row['searchstring'];
        $oss = $old['searchstring'];
        $ocd = $old['created'];
        $omd = $old['modified'];
        if ($oss == $nss) {
            $temp['user']   = $old['username'];
            $temp['action'] = 'identical';
            $temp['code']   = 2;
        } else if ($ocd != $omd) {
            $temp['user']   = $old['username'];
            $temp['action'] = 'modified, no update';
            $temp['code']   = 0;
        } else {
            $oid = $old['id'];
            $oname = oldname($name);
            $rpm = find_query($oname, $db);
            if ($rpm) {
                remove_query($rpm['id'], $db);
            }
            $old['id'] = 0;
            $old['name'] = $oname;
            query_record($old, $db);
            $rpm = find_query($oname, $db);
            if ($rpm) {
                $rid  = $rpm['id'];
                $sql  = "update AssetSearchCriteria\n";
                $sql .= " set assetsearchid = $rid\n";
                $sql .= " where assetsearchid = $oid";
                redcommand($sql, $db);
            }

            // we're just going to do this as an update,
            // since the old criteria now point to the
            // backup copy.

            $row['id'] = $oid;
            $row['username'] = $old['username'];
            query_record($row, $db);
            if (isset($crit[$qid])) {
                create_new_criteria($name, $crit[$qid], $out, $db);
            }
            $temp['user']   = $old['username'];
            $temp['code']   = 4;
            $temp['action'] = 'backup, update';
        }
    } else {
        $row['id'] = 0;
        $row['username'] = $user;
        query_record($row, $db);
        $temp['user']   = $user;
        $temp['code']   = 5;
        $temp['action'] = 'created';
        if (isset($crit[$qid])) {
            create_new_criteria($name, $crit[$qid], $out, $db);
        }
    }
    $out[] = $temp;
}



function record_compare($old, $new, $names)
{
    reset($names);
    foreach ($names as $k => $v) {
        $nv = @$new[$v];
        $ov = @$old[$v];
        if ($nv != $ov) {
            debug_note("compare $v old($ov) != new($nv)");
            return true;
        }
    }
    return false;
}


function report_compare($old, $new)
{
    $names = array();
    $names[] = 'name';
    $names[] = 'search_list';
    $names[] = 'config';
    $names[] = 'order1';
    $names[] = 'order2';
    $names[] = 'order3';
    $names[] = 'order4';
    return record_compare($old, $new, $names);
}

function asset_compare($old, $new)
{
    $names = array();
    $names[] = 'name';
    $names[] = 'order1';
    $names[] = 'order2';
    $names[] = 'order3';
    $names[] = 'order4';
    $names[] = 'searchid';
    return record_compare($old, $new, $names);
}



function update_notify(&$env, $row, &$out, $ns, $now, $db)
{
    $user = $env['user'];
    $name = $row['name'];
    $sid  = $row['search_id'];
    $tab  = 'Notifications';

    $temp = array();
    $temp['code']   = 0;
    $temp['name']   = $name;
    $temp['table']  = $tab;
    $temp['user']   = $user;
    $temp['action'] = 'unknown';

    $sname  = $ns[$sid]['name'];
    $suser  = $ns[$sid]['username'];
    $search = find_search($sname, $db);

    if ($search) {
        $old = find_notify($name, $db);
        $lcl = find_local($name, $tab, $user, $db);
        if ($lcl) {
            $temp['action'] = 'local, no update';
            $temp['code']   = 0;
        } else if ($old) {
            $temp['action'] = 'exists, no update';
            $temp['user']   = $old['username'];
            $temp['code']   = 1;
        } else {
            $row['id'] = 0;
            $row['username']  = $user;
            $row['search_id'] = $search['id'];

            $temp['action'] = 'created';
            $temp['code']   = 5;
            notify_record($row, $db);
        }
    } else {
        $temp['code'] = 0;
        $temp['user'] = 'unknown';
        $temp['action'] = "error: search $sname not found";
    }
    $out[] = $temp;
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
            $srch = find_search($name, $db);
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


function update_asset(&$env, $row, &$out, $nq, $now, $db)
{
    $user  = $env['user'];
    $name  = $row['name'];
    $qid   = $row['searchid'];
    $tab   = 'AssetReports';

    $temp = array();
    $temp['code']   = 0;
    $temp['name']   = $name;
    $temp['user']   = $user;
    $temp['table']  = $tab;
    $temp['action'] = 'unknown';

    $qname = $nq[$qid]['name'];
    $query = find_query($qname, $db);
    if ($query) {
        $row['searchid'] = $query['id'];
        $old = find_asset($name, $db);
        $lcl = find_local($name, $tab, $user, $db);
        if ($lcl) {
            $temp['action'] = 'local, no update';
            $temp['code']   = 0;
        } else if ($old) {
            $ocd = $old['created'];
            $omd = $old['modified'];
            if (asset_compare($old, $row)) {
                if ($ocd != $omd) {
                    $temp['code']   = 0;
                    $temp['user']   = $old['username'];
                    $temp['action'] = 'modified, no update';
                } else {
                    $oname = oldname($name);
                    $bak = $old;

                    $rpm = find_asset($oname, $db);
                    $bak['id']   = ($rpm) ? $rpm['id'] : 0;
                    $bak['name'] = $oname;
                    $bak['enabled'] = 0;
                    asset_record($bak, $db);

                    $old['searchid'] = $query['id'];
                    $old['order1']   = $row['order1'];
                    $old['order2']   = $row['order2'];
                    $old['order3']   = $row['order3'];
                    $old['order4']   = $row['order4'];
                    $old['created']  = $row['created'];
                    $old['modified'] = $row['modified'];
                    asset_record($old, $db);

                    $temp['action'] = 'backup, partial update';
                    $temp['user'] = $old['username'];
                    $temp['code'] = 4;
                }
            } else {
                $temp['code']   = 3;
                $temp['user']   = $old['username'];
                $temp['action'] = 'partial match, no update';
            }
        } else {
            $row['id'] = 0;
            $row['last_run'] = 0;
            $row['next_run'] = 0;
            $row['username'] = $user;
            asset_record($row, $db);
            $temp['action'] = 'created';
            $temp['code'] = 5;
        }
    } else {
        $temp['code']   = 0;
        $temp['user']   = 'unknown';
        $temp['action'] = "error: query $qname not found";
    }
    $out[] = $temp;
}


function update_report(&$env, $row, &$out, $ns, $now, $db)
{
    $user  = $env['user'];
    $name  = $row['name'];
    $list  = $row['search_list'];
    $text  = make_report_list($list, $ns, $db);
    $tab   = 'Reports';

    $temp  = array();
    $temp['code']   = 0;
    $temp['name']   = $name;
    $temp['user']   = $user;
    $temp['table']  = $tab;
    $temp['action'] = 'unknown';

    if ($text) {
        $row['search_list'] = $text;
        $old = find_report($name, $db);
        $lcl = find_local($name, $tab, $user, $db);
        if ($lcl) {
            $temp['action'] = 'local, no update';
            $temp['code']   = 0;
        } else if ($old) {
            $ocd = $old['created'];
            $omd = $old['modified'];
            if (report_compare($old, $row)) {
                if ($ocd != $omd) {
                    $temp['user']   = $old['username'];
                    $temp['action'] = 'modified, no update';
                    $temp['code']   = 0;
                } else {
                    $oname = oldname($name);
                    $bak = $old;
                    $rpm = find_report($oname, $db);

                    $bak['id']   = ($rpm) ? $rpm['id'] : 0;
                    $bak['name'] = $oname;
                    $bak['enabled'] = 0;
                    report_record($bak, $db);

                    $old['created']  = $row['created'];
                    $old['modified'] = $row['modified'];
                    $old['order1']   = $row['order1'];
                    $old['order2']   = $row['order2'];
                    $old['order3']   = $row['order3'];
                    $old['order4']   = $row['order4'];
                    $old['config']   = $row['config'];
                    $old['search_list'] = $text;
                    report_record($old, $db);

                    $temp['user']   = $old['username'];
                    $temp['code']   = 4;
                    $temp['action'] = 'backup, partial update';
                }
            } else {
                $temp['code'] = 3;
                $temp['user'] = $old['username'];
                $temp['action'] = 'partial match, no update';
            }
        } else {
            $user = $env['user'];
            $row['id'] = 0;
            $row['last_run'] = 0;
            $row['next_run'] = 0;
            $row['username'] = $user;
            report_record($row, $db);

            $temp['action'] = 'created';
            $temp['user'] = $user;
            $temp['code'] = 5;
        }
    } else {
        $temp['code'] = 0;
        $temp['user'] = 'unknown';
        $temp['action'] = 'error';
    }
    $out[] = $temp;
}


function update_event_db(&$env, &$out)
{
    $ns = $env['ns'];
    $nr = $env['nr'];
    $nn = $env['nn'];
    $db = $env['db'];
    $now = $env['now'];

    $cns = safe_count($ns);
    $cnr = safe_count($nr);
    $cnn = safe_count($nn);

    $msg  = "New Event Searches: $cns<br>";
    $msg .= "New Event Reports: $cnr<br>";
    $msg .= "New Event Notifications: $cnn<br>";
    $msg = "<p>$msg</p>";
    $msg = fontspeak($msg);
    echo "$msg\n";

    $out = array();

    reset($ns);
    foreach ($ns as $id => $row) {
        update_search($env, $row, $out, $db);
    }

    reset($nn);
    foreach ($nn as $id => $row) {
        update_notify($env, $row, $out, $ns, $now, $db);
    }

    $sql  = "update Notifications set\n";
    $sql .= " last_run = $now where\n";
    $sql .= " enabled = 1 and\n";
    $sql .= " last_run = 0";
    redcommand($sql, $db);

    reset($nr);
    foreach ($nr as $id => $row) {
        update_report($env, $row, $out, $ns, $now, $db);
    }
}


function engine_log($env)
{
    $nl = array();
    $sl = array();
    $db = $env['db'];
    if (mysqli_select_db($db, uglobal)) {
        $sql = "select * from SavedSearches where global = 0";
        $sl  = find_several($sql, $db);
        $sql = "select * from Notifications where global = 0";
        $nl  = find_several($sql, $db);
    }
    if (mysqli_select_db($db, event)) {
        $list = array();
        if ($sl) {
            reset($sl);
            foreach ($sl as $key => $data) {
                $tmp  = $data;
                $id   = $tmp['id'];
                $name = $tmp['name'];
                $user = $tmp['username'];  // check for user existance
                $list[$id] = $tmp;

                $old  = find_local_search($name, $user, $db);
                $tmp['id'] = ($old) ? $old['id'] : 0;
                search_record($tmp, $db);
            }
        }

        if ($nl) {
            reset($nl);
            foreach ($nl as $key => $row) {
                $tmp = $row;
                $sid = $tmp['search_id'];
                if (isset($list[$sid])) {
                    $sname = $list[$sid]['name'];
                    $suser = $list[$sid]['username'];
                    $old   = find_local_search($sname, $suser, $db);
                    if ($old) {
                        $tmp['search_id'] = $old['id'];
                        $name = $tmp['name'];
                        $user = $tmp['username'];
                        $old  = find_local_notify($name, $user, $db);
                        $tmp['id'] = ($old) ? $old['id'] : 0;
                        $tmp['last_run'] = time();
                        notify_record($tmp, $db);
                    }
                }
            }
        }
    }
}




function update_asset_db(&$env, &$out)
{
    $nq = $env['nq'];
    $na = $env['na'];
    $nc = $env['nc'];
    $db = $env['db'];
    $now = $env['now'];

    $cnq = safe_count($nq);
    $cna = safe_count($na);

    $msg  = '';
    $msg .= "New Asset Queries: $cnq<br>";
    $msg .= "New Asset Reports: $cna<br>";
    $msg = "<p>$msg</p>";
    $msg = fontspeak($msg);
    echo "$msg\n";

    reset($nq);
    foreach ($nq as $id => $row) {
        update_query($env, $row, $nc, $out, $db);
    }

    reset($na);
    foreach ($na as $id => $row) {
        update_asset($env, $row, $out, $nq, $now, $db);
    }
}


/*
    function global_event_owner($env)
    {
        $db   = $env['db'];
        $user = $env['user'];
        $qu   = safe_addslashes($user);
        $sql  = "select * from Users where username = '$qu'";
        $row  = find_single($sql,$db);
        if ($row)
        {
            $u = "update";
            $w = "set username = '$qu' where global = 1";
            redcommand("$u SavedSearches $w",$db);
            redcommand("$u Reports $w",$db);
            redcommand("$u Notifications $w",$db);
            redcommand("$u AssetReports $w",$db);
            redcommand("$u AssetSearches $w",$db);
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
            redcommand($sql,$db);
        }
        else
        {
            $msg = "User $user does not exist.";
            $msg = fontspeak("<p><b>$msg</b></p>\n");
            echo $msg;
        }
    }


    function update_owner($user,$txt,$db)
    {
        $list = explode(',',$txt);
        if (($list) && ($user))
        {
            reset($list);
            foreach ($list as $key => $table)
            {
                $sql  = "update $table set\n";
                $sql .= " username = '$user'\n";
                $sql .= " where global = 1";
                redcommand($sql,$db);
            }
        }
    }

*/
function message($msg)
{
    $msg = fontspeak($msg);
    echo "<p><b>$msg</b></p>\n";
}


/*
    function update_privs($user,$db)
    {
        if ($user)
        {
            $sql  = "update Users set\n";
            $sql .= " priv_admin=1,\n";
            $sql .= " priv_search=1,\n";
            $sql .= " priv_report=1,\n";
            $sql .= " priv_notify=1,\n";
            $sql .= " priv_aquery=1,\n";
            $sql .= " priv_areport=1,\n";
            $sql .= " priv_config=1,\n";
            $sql .= " priv_asset=1,\n";
            $sql .= " priv_updates=1,\n";
            $sql .= " priv_downloads=1\n";
            $sql .= " where username = '$user'";
            redcommand($sql,$db);
        }
    }
*/


/*
    function global_owner($env)
    {
        $db   = $env['db'];
        $user = $env['user'];
        $sql  = "select * from Users\n";
        $sql .= " where username = '$user'";
        $row  = find_single($sql,$db);
        if ($row)
        {
            if (mysql_select_db('asset',$db))
            {
                update_owner($user,'AssetReports,AssetSearches',$db);
            }
            if (mysql_select_db('event',$db))
            {
                update_owner($user,'Reports,Notifications,SavedSearches',$db);
            }
            if (mysql_select_db('core',$db))
            {
                update_privs($user,$db);
            }
        }
        else
        {
            message("User $user does not exist.");
        }
    }
*/

function save_buffer($txt)
{
    $fname = '/tmp/uglobal.txt';
    if (file_exists($fname)) {
        unlink($fname);
    }
    $file = @fopen($fname, 'w+');
    if ($file) {
        fwrite($file, $txt);
        fclose($file);
    } else {
        echo "can not save output file!<br>\n";
        echo "\n<pre>\n$txt\n</pre>\n";
    }
}


function delete_name_report($name, $db)
{
    $qn  = safe_addslashes($name);
    $sql = "delete from Reports\n"
        . " where global = 1\n"
        . " and name = '$qn'";
    $res = redcommand($sql, $db);
}


function update(&$env)
{
    $success = false;
    $good = 0;
    $db = $env['db'];
    if (mysqli_select_db($db, uglobal)) {
        $env['ns'] = load_search_global($db);
        $env['nr'] = load_report($db);
        $env['nn'] = load_notify($db);
        $env['na'] = load_asset($db);
        $env['nq'] = load_query($db);
        $env['nc'] = load_criteria($db);
        $good = 1;
    }
    if (!$good) {
        message("Unable to load records");
        return false;
    }
    $reset = $env['reset'];
    $debug = $env['debug'];
    $out   = array();
    if (mysqli_select_db($db, event)) {
        delete_name_report('Monthly System Maintenance Report Failures', $db);
        delete_name_report('Weekly System Maintenance Report Failures', $db);

        $owner = find_global_owner($db);
        if ($owner) {
            $user = $owner;
            $env['user'] = $owner;
        }
        echo "global owner is: $user<br>\n";
        if ($reset) {
            redcommand('delete from Reports', $db);
            redcommand('delete from Console', $db);
            redcommand('delete from Notifications', $db);
            redcommand('delete from SavedSearches', $db);
            PHP_REPF_UpdateDynamicList(CUR, constJavaListEventFilters);
        }
        update_event_db($env, $out);
        engine_log($env);
    } else {
        message("missing event database");
    }
    if (mysqli_select_db($db, asset)) {
        if ($reset) {
            redcommand('delete from AssetReports', $db);
            redcommand('delete from AssetSearches', $db);
            redcommand('delete from AssetSearchCriteria', $db);
        }
        update_asset_db($env, $out);
    } else {
        message("missing asset database");
    }

    if ($out) {
        $success = true;
        usort($out, 'compare_out');
        $txt = display_out($out);
        if ($txt) {
            $created = 0;
            $updated = 0;
            $process = 0;
            $sustain = 0;
            reset($out);
            foreach ($out as $key => $row) {
                $process++;
                $code = $row['code'];
                if ($code <= 3) $sustain++;
                if ($code == 4) $updated++;
                if ($code == 5) $created++;
            }
            unset($out);
            $serv = server_name($db);
            $info = asi_info();
            $vers = $info['svvers'];
            $date = $info['svdate'];
            $dest = 'serverupdate@handsfreenetworks.com';
            $subj = "update for $serv ($vers)";
            $head = "From: uglobal@$serv";
            $time = datestring($env['now']);

            $ns = safe_count($env['ns']);
            $nr = safe_count($env['nr']);
            $nn = safe_count($env['nn']);
            $na = safe_count($env['na']);
            $nq = safe_count($env['nq']);
            logs::log(__FILE__, __LINE__, "uglobal: search:$ns, report:$nr, notify:$nn, query:$nq, asset:$na", 0);

            $msg  = "\n\n\n";
            $msg .= "   Server Name: $serv\n";
            $msg .= "Server Version: $vers\n";
            $msg .= "   Server Date: $date\n";
            $msg .= "  Uglobal Time: $time\n";
            $msg .= "   Global User: $user\n\n";
            $msg .= "---\n\n";
            $msg .= " Event Search: $ns\n";
            $msg .= " Event Report: $nr\n";
            $msg .= " Event Notify: $nn\n";
            $msg .= " Asset Search: $nq\n";
            $msg .= " Asset Report: $na\n\n";
            $msg .= "---\n\n";
            $msg .= " Process: $process\n";
            $msg .= " Sustain: $sustain\n";
            $msg .= " Updated: $updated\n";
            $msg .= " Created: $created\n\n";
            $msg .= "\n\n";
            $msg .= $txt;
            mail($dest, $subj, $msg, $head);

            if ($debug) save_buffer($msg);
            logs::log(__FILE__, __LINE__, "uglobal: process:$process, created:$created, updated:$updated, sustain:$sustain", 0);
        }
    }
    return $success;
}


function check_confirm(&$env)
{
    $self = server_var('PHP_SELF');
    $yes  = html_link("$self?action=confirm", 'Yes');
    $no   = html_link('../index.php', 'No');
    $msg  = "";
    $msg .= "database uglobal does not exist<br>\n";
    $msg .= "<pre>\n";
    $msg .= "   # cd /root/weblog\n";
    $msg .= "   # mysql -u weblog &lt; uglobal.sql<br>\n";
    $msg .= "</pre><br>\n";
    $msg .= "<br>\n";
    $msg .= "Would you like to purge the backups?\n";
    $msg .= "<br><br><br>\n\n";
    $msg .= "$yes&nbsp;&nbsp;&nbsp;$no<br>\n";
    echo $msg;
}

function find_backup($table, $db)
{
    $sql  = "select * from $table where\n";
    $sql .= " name like 'Old:%' and\n";
    $sql .= " global = 1\n";
    $sql .= " order by name";
    return find_several($sql, $db);
}

function add_backup(&$out, $table, $db)
{
    $list = find_backup($table, $db);
    if ($list) {
        reset($list);
        foreach ($list as $key => $row) {
            $temp = array();
            $temp['id']        = $row['id'];
            $temp['name']      = $row['name'];
            $temp['table']     = $table;
            $temp['username']  = $row['username'];
            $out[] = $temp;
        }
    }
}


function confirm_backups(&$env)
{
    $db  = $env['db'];
    $out = array();
    if (mysqli_select_db($db, event)) {
        add_backup($out, 'SavedSearches', $db);
        add_backup($out, 'Notifications', $db);
        add_backup($out, 'Reports', $db);
    }
    if (mysqli_select_db($db, asset)) {
        add_backup($out, 'AssetSearches', $db);
        add_backup($out, 'AssetReports', $db);
    }

    if ($out) {
        echo table_start();
        reset($out);
        $head = array('Table', 'Name', 'Owner', 'Id');
        echo table_head($head);
        foreach ($out as $key => $row) {
            $id    = $row['id'];
            $name  = $row['name'];
            $table = $row['table'];
            $user  = $row['username'];
            $args  = array($table, $name, $user, $id);
            echo table_data($args);
        }
        echo "</table><br>\n";
        echo '<br clear="all">';
        echo "\n\n\n";
        $self = server_var('PHP_SELF');
        $yes  = html_link("$self?action=purge", 'Yes');
        $no   = html_link('../index.php', 'No');
        $msg  = "Are you sure you want to delete these backups?\n";
        $msg .= "<br><br><br>\n\n";
        $msg .= "$yes&nbsp;&nbsp;&nbsp;$no<br>\n";
    } else {
        $home = html_link('../index.php', 'Home');
        $msg  = "There are no backups on the server . . .\n";
        $msg .= "<br><br><br>\n\n";
        $msg .= "$home\n";
    }
    echo $msg;
}


function search_unused($id, $db)
{
    $sql  = "select * from Notifications\n";
    $sql .= " where search_id = $id";
    $list = find_several($sql, $db);
    if ($list) return false;
    $sql  = "select * from Reports\n";
    $sql .= " where search_list like '%,$id,%'";
    $list = find_several($sql, $db);
    if ($list) return false;
    return true;
}



function kill_search($table, $db)
{
    $out = array();
    add_backup($out, $table, $db);
    if ($out) {
        reset($out);
        foreach ($out as $key => $row) {
            $id  = $row['id'];
            if ($id > 0) {
                if (search_unused($id, $db)) {
                    $sql = "delete from $table where id = $id";
                    redcommand($sql, $db);
                }
            }
        }
    }
}



function kill_query($table, $db)
{
    $out = array();
    add_backup($out, $table, $db);
    if ($out) {
        reset($out);
        foreach ($out as $key => $row) {
            $qid = $row['id'];
            if ($qid > 0) {
                $sql  = "select * from AssetReports\n";
                $sql .= " where searchid = $qid";
                $tmp = find_several($sql, $db);
                if (!$tmp) {
                    remove_query($qid, $db);
                }
            }
        }
    }
}


function kill_report($table, $db)
{
    $out = array();
    add_backup($out, $table, $db);
    if ($out) {
        reset($out);
        foreach ($out as $key => $row) {
            $rid = $row['id'];
            remove_report($rid, $db);
        }
    }
}


function kill_asset($table, $db)
{
    $out = array();
    add_backup($out, $table, $db);
    if ($out) {
        reset($out);
        foreach ($out as $key => $row) {
            $rid = $row['id'];
            remove_asset($rid, $db);
        }
    }
}


function kill_notify($table, $db)
{
    $out = array();
    add_backup($out, $table, $db);
    if ($out) {
        reset($out);
        foreach ($out as $key => $row) {
            remove_notify($row['id'], $db);
        }
    }
}


function purge_backups(&$env)
{
    $db  = $env['db'];
    if (mysqli_select_db($db, event)) {
        kill_notify('Notifications', $db);
        kill_report('Reports', $db);
        kill_search('SavedSearches', $db);
        /* probably:
            PHP_REPF_UpdateDynamicList(CUR, constJavaListEventFilters); */
    }
    if (mysqli_select_db($db, asset)) {
        kill_asset('AssetReports', $db);
        kill_query('AssetSearches', $db);
    }
    $home = html_link('../index.php', 'Home');
    $msg  = "Purge complete ...\n";
    $msg .= "<br><br>\n\n";
    $msg .= "$home<br>\n";
    echo $msg;
}


function process(&$env)
{
    $db     = $env['db'];
    $now    = $env['now'];
    $action = $env['action'];
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
    $uglobal = @$dlist['uglobal'];
    $event   = @$dlist['event'];
    $asset   = @$dlist['asset'];
    $core    = @$dlist['core'];
    $err  = '';
    if (!$event)  $err .= "database event does not exist<br>";
    if (!$asset)  $err .= "database asset does not exist<br>";
    if (!$core)   $err .= "database core does not exist<br>";
    if ($err) {
        message($err);
        return false;
    }
    if ($uglobal) {
        if (update($env)) {
            $sql = "drop database if exists uglobal";
            redcommand($sql, $db);
        }
    } else {
        if ($action == 'update') {
            check_confirm($env);
        }
        if ($action == 'confirm') {
            confirm_backups($env);
        }
        if ($action == 'purge') {
            purge_backups($env);
        }
    }
}


/*
    |  Main program
    */

$now   = time();
$db    = db_pconnect();
$dpriv = 0;
$apriv = 0;
$authuser = 'none';

if ($db) {
    if (mysqli_select_db($db, core)) {
        $authuser = process_login($db);
        $user   = user_data($authuser, $db);
        $dpriv  = @($user['priv_debug']) ? 1 : 0;
        $apriv  = @($user['priv_admin']) ? 1 : 0;
    }
}

$comp   = component_installed();
$rst    = intval(get_argument('reset', 0, 0));
$dbg    = intval(get_argument('debug', 0, 0));
$test   = intval(get_argument('test', 0, 0));
$action = trim(get_argument('action', 0, 'update'));
$debug  = ($dpriv) ? $dbg : 0;
$reset  = ($dpriv) ? $rst : 0;

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users


$test_sql = $test;
$real_sql = (!$test_sql);

$env = array();
$env['db']      = $db;
$env['now']     = $now;
$env['user']    = $authuser;
$env['test']    = $test;
$env['reset']   = $reset;
$env['debug']   = $debug;
$env['action']  = $action;
$env['authuser'] = $authuser;

$msg = '';
if ($db) {
    if ($apriv) {
        process($env);
    } else {
        $msg = "This page requires administrative access.";
    }
} else {
    $msg  = "The database is currently unavailable. <br>";
    $msg .= "Please try again later.";
}

if ($msg) {
    $msg = fontspeak("<p><b>$msg</b></p>\n");
    echo $msg;
}

echo head_standard_html_footer($authuser, $db);
