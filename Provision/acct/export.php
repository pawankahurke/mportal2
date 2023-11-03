<?php

/*
Revision History:

Date        Who     What
----        ---     ----
28-Dec-04   BJS     Created.
31-Dec-04   BJS     Many changes.
03-Jan-05   BJS     Added load_instructions, space(), mv'd & revoked db_install access.
04-Jan-05   BJS     Added query_to_csv & "_"_sql functions.
05-Jan-05   BJS     post_self(), no SDB, no db_code, gang_table() and more.
06-Jan-05   BJS     tiny->html_select, checkall/checknone, .sql->.txt, filename error checking.
                    no longer use asi() to get db's, and now pass ints, not strings.
07-Jan-05   BJS     no longer use find_dirty, $env is used more/better, began site restrictions.
10-Jan-05   BJS     Finished site restrictions.
11-Jan-05   BJS     SQL gets generated properly for field values in the db.
                    Uses radio buttons now. Menu Improvements. User picks limit. Increased sanity checks.
12-Jan-05   BJS     Modified sql queries. Radio button mods. Added priv_disp.
13-Jan-05   BJS     Added debug option. Counts rows from table. Minor fixes.
14-Jan-05   BJS     query_to_sql/csv handle queries to conserve memory.
17-Jan-05   BJS     Added browser checking for mozilla, defaults to IE.
                    Added option to select multiple query filters.
18-Jan-05   BJS     Fixed servertime error.
19-Jan-05   BJS     return_table_query switches on dbcode, calls SQL_<dbnanme>. SQL query fixes.
07-Feb-05   BJS     wu-stats.php uses export.php to export softinst.PatchStatus data.
08-Feb-05   BJS     added csv & sql support for wu-stats, softinst.PatchStatus.
08-Feb-05   BJS     Many cosmetic fixes for Alex.
09-Feb-05   BJS     Removed username/customer column from sql/cvs export for PatchStatus. Fixed datatypes.
                    Added all sorts of instructions for Alex.
10-Feb-05   BJS     Minor changes.
10-Feb-05   EWB     Fixed a small security problem in PatchStatus export.
21-Mar-05   BJS     Cosmetic fix for Alex.
01-Apr-05   BJS     ability to format time. Removed excess while loops.
12-Apr-05   BJS     more time formats added.
30-Jun-05   BJS     fixed asset.Machine[cearliest] not getting translated.
                    changed text 'format of date fields'.
26-Sep-05   BJS     Text changes for Alex.
08-Nov-05   BJS     Removed NotSiteFilters & RptSiteFilters for Events.
10-Nov-05   BJS     Updated to new core tables, removed siteman references.
05-Dec-05   BJS     Removed asset.RptSiteFilters.
24-Apr-06   BTE     Bugs 2963 and 2974.
21-Jul-06   BTE     Bug 3541: Export wizard fails.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.

*/

/* -- to-do ----------------------------------------
> work on user sql section
> need to warn user of possible size
> save sql queries
----------------------------------------------------*/

ob_start();

include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-user.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-slct.php');
include('../lib/l-head.php');
include('../lib/l-tabs.php');
include('../lib/l-form.php');
include('../lib/l-tiny.php');
include('../lib/l-msql.php');
include('../lib/l-dsql.php');
include('../lib/l-slav.php');
include('../lib/l-cmth.php');

/* Definitions */
define('constButtonSubmit', 'Submit');
define('constButtonExcel',  'Text');
define('constButtonSQL',    'SQL');
define('constButtonAll',    'Check All');
define('constButtonNone',   'Uncheck All');
define('constButtonCancel', 'Cancel');

/* Database Definitions */
define('constSelectDB',   0);
define('constCoreDB',     1);
define('constEventDB',    2);
define('constSwupdateDB', 3);
define('constProvisionDB', 4);
define('constSoftinstDB', 5);
define('constAssetDB',    6);

/* Time definitions */
define('constTimePosix',  0);
define('constTimeMysql',  1);
define('constTimeRfc',    2);
define('constTimeMdy',    3);
define('constTimeDmy',    4);
define('constTimeDot',    5);
define('constTimeGmt',    6);
define('constTimeMysqlEU', 7);
define('constTimeAltL',   8);
define('constTimeAltS',   9);


function query_to_sql(&$env)
{
    $create_db  = $env['create_db'];
    $create_tbl = $env['create_tbl'];
    $header     = $env['header'];
    $table      = $env['s_tbl'];
    $out        = $env['out'];
    $db         = $env['db'];
    $ftime      = $env['ftime'];

    $dt_dbname  = db_decode($env['db_check']);
    $dt_table   = ucfirst($table);
    $res        = describe_table($dt_dbname, $dt_table, $db); /* from l-dsql */

    $table = strtolower($table);
    $file  = "$table" . '_export';

    $dbname = db_decode($env['db_check']);
    $dbname = $dbname . '_export';
    $dbname = (getenv('DB_PREFIX') ?: '') . 'core';

    /* get the array of fields that need to be converted */
    if ($ftime)
        $ttd  = table_time_desc($dt_dbname, $dt_table);

    /* SQL */
    $createtb   = '';
    $createin   = 'INSERT INTO '  . $file . ' VALUES (';
    $varchar    = ' varchar (255)';
    $newline    = "\r\n";
    $endparen   = ');';
    $noscomma   = ',';
    $commaspace = ', ';
    $space      = ' ';
    $txt        = '';
    $createdb   = '';
    $usedb      = '';
    $sql_main_tables = '';

    $eol    = ($env['priv_disp']) ? "<br>\n" : $newline;
    $indent = ($env['priv_disp']) ? '&nbsp;' : '';

    if ($create_db) {
        $createdb = "CREATE DATABASE $dbname;";
        $usedb    = "USE $dbname;";
    }

    /* CREATE TABLE ... */
    if ($create_tbl) {
        $createtb   = 'CREATE TABLE ' . $file . ' (';
        $comma = FALSE;
        foreach ($header as $k => $v) {
            if ($comma) {
                $createtb .= $commaspace;
            }
            reset($res);
            foreach ($res as $k1 => $v1)              /* ($res) from describe_table */ {
                reset($v1);
                foreach ($v1 as $k2 => $v2) {
                    if ($k1 == 'type')                     /* I want the data type only */ {
                        if ($k2 == $v) {
                            if ($env['wu-stats']) {
                                /* because the POSIX time will change to RFC, int(11) no longer cuts it, need varchar(35)*/
                                $vv = wu_type_change($v, $v2);
                                $createtb .= "$eol$indent $v ${vv}";
                            } else {
                                $createtb .= "$eol$indent $v ${v2}";
                            }
                        }
                    }
                }
            }
            $comma = TRUE;
        }
        if ($env['wu-stats']) {
            $createtb .= ',' . wu_create($eol, $indent);
        }
        $createtb .= $endparen . $eol;
    }

    /*
          INSERT VALUES ...
                           */

    if ($env['wu-stats']) {
        /* describe_table (above) doesn't return all tables needed for wu-stats/softinst.PatchStatus */

        $env['fields'] .= ($env['wu-stats']) ? wu_fields() : '';
        $header         = explode('|', $env['fields']);
    }

    $comma = FALSE;
    $res   = redcommand($out, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $sql_create = '';
            reset($header);
            foreach ($header as $k => $v) {
                $data = $row[$v];
                if (isset($data)) {
                    if ($comma) {
                        $sql_create .= $noscomma;
                    }
                    $comma = TRUE;
                    if (is_numeric($data)) {
                        if ($env['wu-stats']) /* because these fields need to be converted */ {
                            $utemp = wu_stats_check($v, $data);
                            if (!is_numeric($utemp)) /* if its now a date, quote it */ {
                                $sql_create .= squote($data);
                            } else                     /* or an int, no quotes are needed */ {
                                $sql_create .= $utemp;
                            }
                        } else {
                            /* if the user wants to format the time other than posix */
                            if ($ftime && isset($ttd[$v])) {
                                $data = time_decode($ftime, $data);
                            }
                            $sql_create .= squote($data);
                        }
                    } else {
                        $qv  = safe_addslashes($data);
                        $qv  = squote($qv);
                        $sql_create .= $qv;
                    }
                    $sql_create .= ' ';
                } //end while
            } //end while
            $sql_create      .= $endparen . $eol;
            $sql_create       = $createin . $sql_create;
            $sql_main_tables .= $sql_create;
            $comma = FALSE;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    /* Format strings */
    $txt .= $createdb        . $eol
        . $usedb           . $eol
        . $createtb        . $eol
        . $sql_main_tables . $eol;

    return $txt;
}


function query_to_csv(&$env)
{
    $out    = $env['out'];    /* sql query */
    $header = $env['header'];
    $db     = $env['db'];
    $s_tbl  = $env['s_tbl'];
    $ftime  = $env['ftime'];

    $txt     = array();
    $eol     = "\n";
    $count   = '0';
    $db_name = db_decode($env['db_check']);

    /* get the array of fields that need to be converted */
    if ($ftime)
        $ttd = table_time_desc($db_name, $s_tbl);

    /* csv header */
    foreach ($header as $k => $v) {
        $txt[] = quote($v);
        $count++;
    }
    $txt   = join(',', $txt);
    $txt  .= $eol;

    /* csv the data */
    $res = redcommand($out, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $set = array();
            reset($header);
            foreach ($header as $k => $v) {
                $data = $row[$v];
                if (isset($data)) {
                    if ($env['wu-stats']) /* because these fields need to be converted */ {
                        $udate = wu_stats_check($v, $data);
                        $set[] = quote($udate);
                    } else {
                        /* if the user wants to format the time other than posix*/
                        if ($ftime && isset($ttd[$v])) {
                            $data = time_decode($ftime, $data);
                        }
                        $set[] = quote($data);
                    }
                } else {
                    $set[] = '';
                }
            }
            $set = join(',', $set);
            $set .= $eol;
            $txt .= $set;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $txt;
}

function gang_table(&$env, &$set, $frm, $head)
{
    $display = '';
    $rows = safe_count($set);
    if ($rows <= 0) {
        return;
    }

    $cols = (12 <= $rows) ? 4 : 1;
    if ($frm) {
        $post = $env['post'];
        $aflg = ($post == constButtonAll);
        $nflg = ($post == constButtonNone);
    }

    $out = array();
    $tmp = array();

    reset($set);
    foreach ($set as $nnn => $data) {
        $nam = $data['name'];
        if ($frm) {
            $tag = $data['id'];
            $chk = get_integer($tag, 1); /* all checked by default */
            $chk = ($aflg) ? 1 : $chk;
            $chk = ($nflg) ? 0 : $chk;
            $box = checkbox($tag, $chk) . '&nbsp;';
            $txt = $box . $nam;
        } else {
            $txt = $nam;
        }
        $tmp[$nnn] = $txt;
    }
    if ($cols > 1) {
        $dec = $cols - 1;
        $max = intval(($rows + $dec) / $cols);
        for ($row = 0; $row < $max; $row++) {
            for ($col = 0; $col < $cols; $col++) {
                $out[$row][$col] = '<br>';
            }
        }
    } else {
        $max = $rows;
        $col = 0;
    }
    reset($tmp);
    foreach ($tmp as $nnn => $txt) {
        if ($cols > 1) {
            $row = intval($nnn % $max);
            $col = intval($nnn / $max);
        } else {
            $row = $nnn;
        }
        $out[$row][$col] = $txt;
    }

    $text = "$head &nbsp; ($rows found)";
    /* don't want to display these items yet.. */
    $display .= table_header();
    $display .= pretty_header($text, $cols);
    reset($out);
    foreach ($out as $key => $args) {
        $display .= table_data($args, 0);
    }
    $display .= table_footer();
    return $display;
}


function create_gang_array($set)
{
    $n = 'name';
    $i = 'id';
    $out = array();
    foreach ($set as $k => $v) {
        $out[$k][$n] = $v;
        $out[$k][$i] = $k;
    }
    return $out;
}


function checkallnone($n)
{
    $in = indent($n);
    $ck = button(constButtonAll);
    $un = button(constButtonNone);
    return para("${in}${ck}${in}${un}");
}


function s_error()
{
    echo "You have made an error in your selection.";
    echo "<br><br> Please return to the previous page to correct it.";
}


function r_error()
{
    $submit = button('< Return to Start');
    ob_end_clean();

    echo post_self('myform');

    echo '<b>SQL Error!</b> Your request cannot be processed at this time.';
    space(2);
    echo $submit;

    echo form_footer();
}


function report_error($sql, $db)
{
    $error = mysqli_error($db);
    $errno = mysqli_errno($db);
    $msg   = '       Query: ' . nl2br($sql) . "\n";
    $msg  .= "Error Number: $errno\n";
    $msg  .= "  Error Text: $error\n";
    return $msg;
}


function load_instructions(&$env)
{/* main instructions loaded per page */
    $txt     = '';
    $act     = $env['act'];
    $tn      = $env['s_tbl'];

    $db_name = db_decode($env['db_check']);
    if ($env['db_check'] == constAssetDB) {
        $txt = 'If you want to export asset information please click on the link below.'
            . ' It will take you to the Asset Queries page where you will be able to export'
            . ' the desired asset information using the appropriate asset query.<br><br>';
    }
    switch ($act) {
        case 'ptbl':
            $txt .= " Please select a table in the $db_name database:";
            break;
        case 'pcol':
            $txt .= "1) Please select the fields in the $tn table records which you would like to export:";
            break;
        case 'xprt':
            $txt .= '1) Please select the file format you would like:';
            break;
        case 'pdat':
            $txt .= 'Welcome to the database wizard. Depending on the database you wish to export data from,'
                . ' in as few as three steps you will be able to export the desired information either to'
                . ' an ASCII comma-delimited text file with headers (.csv extension), or to an SQL table.'
                . '<br><br>Please select a database:';
            break;
    }
    return $txt;
}


function db_desc($i)
{
    switch ($i) {
        case constCoreDB:
            return ' - System census, information portal, machine group, server account'
                . ' and server option information';
        case constEventDB:
            return ' - Event and action logs posted by the ASI client';
        case constSwupdateDB:
            return ' - ASI client update information';
        case constProvisionDB:
            return ' - Provisioning and metering information';
        case constSoftinstDB:
            return ' - Microsoft update status information';
        case constAssetDB:
            return ' - Asset information for all systems at all sites';
    }
}

function asset_desc($column)
{
    switch ($column) {
        case 'AssetReports':
            return ' - Asset report definitions and information about current status';
        case 'AssetSearchCriteria':
            return ' - Asset queries selection clauses';
        case 'AssetSearches':
            return ' - Asset queries definitions';
        case 'DataName':
            return ' - Names, hierarchy, and grouping information for asset data';
        case 'Machine':
            return ' - Information about systems with asset data in the ASI asset database';
    }
}

function event_desc($column)
{
    switch ($column) {
        case 'Audit':
            return ' - Track actions on the client or server';
        case 'Console':
            return ' - Information about notifications posted on notification console';
        case 'EventScrips':
            return ' - Information about available Scrips';
        case 'Events':
            return ' - Event log databases';
        case 'Notifications':
            return ' - Event notification definitions';
        case 'NotifySchedule':
            return ' - Event notification schedule information';
        case 'Reports':
            return ' - Event report definitions';
        case 'SavedSearches':
            return ' - Event query filters definitions';
    }
}


function siteman_desc($column)
{
    switch ($column) {
        case 'Descriptions':
            return ' - Contains the descriptions of the variables for the configuration interface';
        case 'GlobalCache':
            return ' - Caches checksums for global variables';
        case 'Globals':
            return ' - Contains information on global variables';
        case 'LocalCache':
            return ' - Caches per machine checksums';
        case 'Locals':
            return ' - Contains information on local variables';
        case 'Revisions':
            return ' - Stores machine specific information relevant to configuration variables';
        case 'Scrips':
            return ' - Describes the layout of the configuration pages for the Scrip configuration';
        case 'Variables':
            return ' - Contains version specific information about all configuration variables';
    }
}


function swupdate_desc($column)
{
    switch ($column) {
        case 'Downloads':
            return ' - Latest client update';
        case 'UpdateMachines':
            return ' - Latest client version per machine';
        case 'UpdateSites':
            return ' - All sitenames and current version';
    }
}


function provision_desc($column)
{
    switch ($column) {
        case 'Audit':
            return ' - Contains the audit trail on provisioning information';
        case 'CryptKeys':
            return ' - Contains the encryption and decryption keys used for'
                . ' enabling and disabling applications';
        case 'KeyFiles':
            return ' - Contains the list of keyfiles for each product';
        case 'MachineAssignments':
            return ' - Contains information about which machines have machine-specific'
                . ' information about provisioning and/or metering';
        case 'Meter':
            return ' - Contains the metering information sent by the client';
        case 'MeterFiles':
            return ' - Contains the list of metering files for each product';
        case 'Products':
            return ' - Contains information for products that can be provisioned';
        case 'SiteAssignments':
            return ' - Contains information about which sites have products provisioned or metered';
    }
}

function core_desc($column)
{
    switch ($column) {
        case 'Census':
            return ' - Lookup machines by site, host, uuid and id';
        case 'Customers':
            return ' - Control sites access for users';
        case 'FileSites':
            return ' - What file each site uses';
        case 'Files':
            return ' - Files saved from report generation';
        case 'GroupExpression':
            return ' - Groups defined by expressions';
        case 'GroupSettings':
            return ' - Indicates user-defined groups where variables can have a value';
        case 'InvalidVars':
            return ' - Contains varialbes that are no longer used but still maintained';
        case 'MachineCategories':
            return ' - Categories for groups';
        case 'MachineGroupMap':
            return ' - Associates machine to machine group';
        case 'MachineGroups':
            return ' - Keeps track of groups of machines';
        case 'Options':
            return ' - Server options';
        case 'Revisions':
            return ' - Stores machine-specific information relevant to configuration variables';
        case 'Scrips':
            return ' - Lists the Scrip names';
        case 'SemClears':
            return ' - Stores the clear side of all semaphores';
        case 'Users':
            return ' - List of users and privileges';
        case 'ValueMap':
            return ' - What value to use for each variable on every machine';
        case 'Variables':
            return ' - Version-independent information about all variables';
        case 'VarValues':
            return ' - Contains information on variable values';
        case 'VarVersions':
            return ' - Version-dependent information about all variables';
    }
}

function softinst_desc($column)
{
    switch ($column) {
        case 'Machine':
            return ' - Row for each machine contains machine specific data';
        case 'PatchCategories':
            return ' - List of patch group categories';
        case 'PatchConfig':
            return ' - Configuration of a patch';
        case 'PatchExpression':
            return ' - Patches defined by expressions';
        case 'PatchGroupMap':
            return ' - Mapping between patches and patch groups';
        case 'PatchGroups':
            return ' - List of patch groups';
        case 'PatchStatus':
            return ' - Current status of all patches';
        case 'Patches':
            return ' - List of all the patches';
        case 'WUConfig':
            return ' - Configuration of the windows update client';
    }
    return $txt;
}


function table_desc($tbl, $column)
{   /* gets the desc for all tables in a given db */
    switch ($tbl) {
        case 'core':
            return core_desc($column);
        case 'event':
            return event_desc($column);
        case 'swupdate':
            return swupdate_desc($column);
        case 'provision':
            return provision_desc($column);
        case 'softinst':
            return softinst_desc($column);
        case 'asset':
            return asset_desc($column);
    }
}

function table_time_desc($db_name, $s_tbl)
{   /* calls the time field procedures for a given database table */
    switch ($db_name) {
        case 'core':
            return core_time($s_tbl);
        case 'event':
            return event_time($s_tbl);
        case 'swupdate':
            return swupdate_time($s_tbl);
        case 'provision':
            return provision_time($s_tbl);
        case 'softinst':
            return softinst_time($s_tbl);
        case 'asset':
            return asset_time($s_tbl);
    }
}


function wu_array()
{   /* fields from PatchStatus to be converted from POSIX to rfc */
    return array(
        0 => 'lastchange',
        1 => 'detected',
        2 => 'lastinstall',
        3 => 'lastuninstall',
        4 => 'nextaction',
        5 => 'lastdownload',
        6 => 'lasterror',
        7 => 'lasterrordate'
    );
}


function return_db()
{
    return array(
        constCoreDB      => 'core',
        constEventDB     => 'event',
        constSwupdateDB  => 'swupdate',
        constProvisionDB => 'provision',
        constSoftinstDB  => 'softinst',
        constAssetDB     => 'asset'
    );
}


/* gets int, returns string */
function db_decode($db_code)
{
    switch ($db_code) {
        case constCoreDB:
            return 'core';
        case constEventDB:
            return 'event';
        case constSwupdateDB:
            return 'swupdate';
        case constProvisionDB:
            return 'provision';
        case constSoftinstDB:
            return 'softinst';
        case constAssetDB:
            return 'asset';
    }
}


/* available time formats */
function return_time_formats()
{
    return array(
        constTimePosix   => '1122334455 (Posix)',
        constTimeMysql   => '2001-03-30 13:22:12 (Server Local Time)',
        constTimeAltL    => '03/30/2001 13:22:12 (American Local Time)',
        constTimeMysqlEU => '30/03/2001 13:22:12 (European)',
        constTimeGmt     => '2001-03-30 13:22:12 (GMT)',
        constTimeAltS    => '03/30/01 13:22:12 (American Local Time)',
        constTimeMdy     => 'March 30, 2001',
        constTimeDmy     => '30 March, 2001',
        constTimeDot     => '03.30.01',
        constTimeRfc     => 'Fri, 30 Mar 2001 13:22:12 +0200',
    );
}


/* this funtion recvs the ($time) format the user wants and the time-stamp
       ($data) to be converted and returned.
    */
function time_decode($ftime, $data)
{
    switch ($ftime) {
        case constTimePosix:
            return $data;
        case constTimeMysql:
            return mysqltime($data);
        case constTimeMysqlEU:
            return date('d/m/Y H:i:s',   $data);
        case constTimeGmt:
            return gmdate('Y-m-d H:i:s', $data);
        case constTimeAltL:
            return date('m/d/Y H:i:s',   $data);
        case constTimeAltS:
            return date('m/d/y H:i:s',   $data);
        case constTimeMdy:
            return date('F j, Y',        $data);
        case constTimeDmy:
            return date('j F, Y',        $data);
        case constTimeDot:
            return date('m.d.y',         $data);
        case constTimeRfc:
            return date('r',             $data);
        default:
            return                       $data;
    }
}


function return_limit($admin)
{
    if ($admin) { /* for admin */
        return array(
            10  => 10,
            100  => 100,
            500  => 500,
            1000 => 1000,
            2000 => 2000,
            4000 => 4000,
            5000 => 5000,
            0 => 0,
        );
    } else { /* and for no admin privs */
        return array(
            10  => 10,
            100  => 100,
            500  => 500,
            1000 => 1000,
            2000 => 2000,
            4000 => 4000,
            5000 => 5000,
        );
    }
}

function return_days()
{
    return array(
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
    );
}

function rfc_date($date)
{
    return date('r', $date);
}

function date_code($when, $d)
{
    if ($d > 1) {
        $when = days_ago($when, $d - 1);
    }
    return $when;
}

function past_options($midn, $days)
{
    $opts = array(
        0 => 'No Limit',
        1 => 'Today'
    );
    reset($days);
    foreach ($days as $key => $day) {
        $time = date_code($midn, $day);
        $text = date('D m/d', $time) . " ($day days)";
        $opts[$day] = $text;
    }
    return $opts;
}

function wu_fields()
{   /* fields not in the PatchStatus table, but needed for the query */
    return '|host|site|name';
}

function wu_create($eol, $indent)
{
    return " $eol$indent host     varchar(64),"
        . " $eol$indent site     varchar(50),"
        . " $eol$indent name     varchar(255)";
}

function wu_type_change($v, $v2)
{   /* because POSIX time is: 1) not a 11char int 2) a string */
    /* varchar(35) will be the new RFC datatype */
    $wuarray = wu_array();
    $vv = $v2;
    foreach ($wuarray as $key => $value) {
        if ($v == $value) {
            $vv = 'varchar(35)';
        }
    }
    return $vv;
}

function wu_stats_check($v, $arry)
{
    $wuarray = wu_array();
    foreach ($wuarray as $key => $value) {     /* if the column name is the same as the ones that need conversion in wuarray() */
        if ($v == $value) {
            $arry = rfc_date($arry); /* then convert it */
        }
    }
    return $arry;
}


/*
       the database name_time functions return an array of true fields that hold POSIX time stamps
    */

function core_time($s_tbl)
{
    switch ($s_tbl) {
        case 'Census':
            return array(
                'last'     => 1,
                'born'     => 1
            );
            break;

        case 'Files':
            return array(
                'created'  => 1,
                'expires'  => 1
            );
            break;

        case 'MachineGroups':
            return array('created'  => 1);
            break;
        case 'Options':
            return array('modified' => 1);
            break;
        default:
            return array();
            break;
    }
}


function event_time($s_tbl)
{
    switch ($s_tbl) {
        case 'Console':
            return array(
                'servertime' => 1,
                'expire'     => 1
            );
            break;

        case 'EventScrips':
            return array('modified'   => 1);
            break;

        case 'Events':
            return array(
                'entered'    => 1,
                'servertime' => 1
            );
            break;

        case 'Notifications':
            return array(
                'last_run'   => 1,
                'next_run'   => 1,
                'created'    => 1,
                'modified'   => 1
            );
            break;

        case 'Reports':
            return array(
                'last_run'   => 1,
                'next_run'   => 1,
                'umin'       => 1,
                'umax'       => 1,
                'created'    => 1,
                'modified'   => 1
            );
            break;

        case 'SavedSearches':
            return array(
                'created'    => 1,
                'modified'   => 1
            );
            break;
        default:
            return array();
            break;
    }
}



function swupdate_time($s_tbl)
{
    switch ($s_tbl) {
        case 'UpdateMachines':
            return array('timecontact' => 1);
            break;
        default:
            return array();
            break;
    }
}


function provision_time($s_tbl)
{
    switch ($s_tbl) {
        case 'Audit':
            return array(
                'servertime' => 1,
                'clienttime' => 1
            );
            break;

        case 'CryptKeys':
            return array(
                'created'    => 1,
                'lastuse'    => 1
            );
            break;

        case 'Meter':
            return array(
                'clienttime' => 1,
                'servertime' => 1
            );
            break;

        case 'Products':
            return array(
                'created'    => 1,
                'modified'   => 1
            );
            break;
        default:
            return array();
            break;
    }
}


function softinst_time($s_tbl)
{
    switch ($s_tbl) {
        case 'Machine':
            return array(
                'lastchange'    => 1,
                'lastcontact'   => 1,
                'lastdefchange' => 1
            );
            break;

        case 'PatchConfig':
            return array('lastupdate'    => 1);
            break;
        case 'PatchGroups':
            return array('created'       => 1);
            break;

        case 'PatchStatus':
            return array(
                'lastchange'    => 1,
                'detected'      => 1,
                'lastinstall'   => 1,
                'lastdownload'  => 1,
                'lasterrordate' => 1
            );
            break;

        case 'Patches':
            return array('data'          => 1);
            break;
        case 'WUConfig':
            return array('lastupdate'    => 1);
            break;
        default:
            return array();
            break;
    }
}


function asset_time($s_tbl)
{
    switch ($s_tbl) {
        case 'AssetReports':
            return array(
                'last_run'  => 1,
                'created'   => 1,
                'modified'  => 1,
                'next_run'  => 1
            );
            break;

        case 'AssetSearches':
            return array(
                'created'   => 1,
                'modified'  => 1
            );
            break;

        case 'Machine':
            return array(
                'cearliest' => 1,
                'clatest'   => 1,
                'searliest' => 1,
                'slatest'   => 1
            );
            break;
        default:
            return array();
            break;
    }
}

/*
       </ time functions >
    */


function return_table_query(&$env)
{
    $sql   = '';
    $limit = ' LIMIT ' . $env['user_limit']; /* user sets the limit */
    if ($env['user_limit'] == 0) {
        $limit = '';                         /* no limit for priv users */
    }
    $env['limit'] = $limit;

    switch ($env['db_check']) /* locate the proper db */ {
        case constCoreDB:
            $sql = SQL_Core($env);
            break;
        case constEventDB:
            $sql = SQL_Event($env);
            break;
        case constSwupdateDB:
            $sql = SQL_Swupdate($env);
            break;
        case constProvisionDB:
            $sql = SQL_Provision($env);
            break;
        case constSoftinstDB:
            $sql = SQL_Softinst($env);
            break;
        case constAssetDB:
            $sql = SQL_Asset($env);
            break;
        default:;
            break;
    }
    return $sql;
}

function SQL_Core(&$env)
{
    $sql = '';
    switch ($env['s_tbl']) {
            /* core DB */
        case 'Census':
            $sql = SQL_Census($env);
            break;
        case 'Customers':
            $sql = SQL_Customers($env);
            break;
        case 'FileSites':
            $sql = SQL_FileSites($env);
            break;
        case 'Files':
            $sql = SQL_Files($env);
            break;
        case 'GroupExpression':
            $sql = SQL_GroupExpression($env);
            break;
        case 'MachineCategories':
            $sql = SQL_MachineCategories($env);
            break;
        case 'MachineGroupMap':
            $sql = SQL_MachineGroupMap($env);
            break;
        case 'MachineGroups':
            $sql = SQL_MachineGroups($env);
            break;
        case 'Options':
            $sql = SQL_Options($env);
            break;
        case 'Users':
            $sql = SQL_Users($env);
            break;
        case 'GroupSettings':
        case 'InvalidVars':
        case 'Revisions':
        case 'Scrips':
        case 'SemClears':
        case 'ValueMap':
        case 'Variables':
        case 'VarValues':
        case 'VarVersions':
            if ($env['optsql']) {
                $sql = 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'core.'
                    . $env['s_tbl'] . ' WHERE ' . $env['optsql']
                    . $env['limit'];
            } else {
                $sql = 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'core.'
                    . $env['s_tbl'] . $env['limit'];
            }
            break;
        default:;
            break;
    }
    return $sql;
}

function SQL_Event(&$env)
{
    $sql = '';
    switch ($env['s_tbl']) {
            /* event DB */
        case 'Console':
            $sql = SQL_Console($env);
            break;
        case 'EventScrips':
            $sql = SQL_EventScrips($env);
            break;
        case 'Events':
            $sql = SQL_Events($env);
            break;
        case 'Notifications':
            $sql = SQL_Notifications($env);
            break;
        case 'NotifySchedule':
            $sql = SQL_NotifySchedule($env);
            break;
        case 'Reports':
            $sql = SQL_Reports($env);
            break;
        case 'SavedSearches':
            $sql = SQL_SavedSearches($env);
            break;
        case 'Audit':
            $sql = SQL_EventAudit($env);
            break;
        default:;
            break;
    }
    return $sql;
}

function SQL_Provision(&$env)
{
    $sql = '';
    switch ($env['s_tbl']) {
            /* provision DB */
        case 'Audit':
            $sql = SQL_Audit($env);
            break;
        case 'CryptKeys':
            $sql = SQL_CryptKeys($env);
            break;
        case 'KeyFiles':
            $sql = SQL_KeyFiles($env);
            break;
        case 'MachineAssignments':
            $sql = SQL_MachineAssignments($env);
            break;
        case 'Meter':
            $sql = SQL_Meter($env);
            break;
        case 'MeterFiles':
            $sql = SQL_MeterFiles($env);
            break;
        case 'Products':
            $sql = SQL_Products($env);
            break;
        case 'SiteAssignments':
            $sql = SQL_SiteAssignments($env);
            break;
        default:;
            break;
    }
    return $sql;
}


function SQL_Softinst(&$env)
{
    $sql = '';
    switch ($env['s_tbl']) {
            /* softinst DB */
        case 'Machine':
            $sql = SQL_Machine($env);
            break;
        case 'PatchCategories':
            $sql = SQL_PatchCategories($env);
            break;
        case 'PatchConfig':
            $sql = SQL_PatchConfig($env);
            break;
        case 'PatchExpression':
            $sql = SQL_PatchExpression($env);
            break;
        case 'PatchGroupMap':
            $sql = SQL_PatchGroupMap($env);
            break;
        case 'PatchGroups':
            $sql = SQL_PatchGroups($env);
            break;
        case 'PatchStatus':
            $sql = SQL_PatchStatus($env);
            break;
        case 'Patches':
            $sql = SQL_Patches($env);
            break;
        case 'WUConfig':
            $sql = SQL_WUConfig($env);
            break;
        default:;
            break;
    }
    return $sql;
}

function SQL_Swupdate(&$env)
{
    $sql = '';
    switch ($env['s_tbl']) {
            /* swupdate DB */
        case 'Downloads':
            $sql = SQL_Downloads($env);
            break;
        case 'UpdateMachines':
            $sql = SQL_UpdateMachines($env);
            break;
        case 'UpdateSites':
            $sql = SQL_UpdateSites($env);
            break;
        default:;
            break;
    }
    return $sql;
}

function SQL_Asset(&$env)
{
    $sql = '';
    switch ($env['s_tbl']) {
            /* asset DB */
        case 'AssetReports':
            $sql = SQL_AssetReports($env);
            break;
        case 'AssetSearchCriteria':
            $sql = SQL_AssetSearchCriteria($env);
            break;
        case 'AssetSearches':
            $sql = SQL_AssetSearches($env);
            break;
        case 'DataName':
            $sql = SQL_DataName($env);
            break;
        case 'Machine':
            $sql = SQL_AssetMachine($env);
            break;
        default: /* returns $sql */;
            break;
    }
    return $sql;
}

/* -------- begin core ----------*/

function SQL_Census(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'core.Census'
        . ' WHERE site IN (' . $env['dbaccess'] . ')'
        . $addsql
        . ' ORDER BY site, host '
        . $env['limit'];
}

function SQL_Customers(&$env)
{
    $qa = safe_addslashes($env['auth']);
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'core.Customers'
        . ' WHERE (username = \'' . $qa . '\')'
        . $addsql
        . $env['limit'];
}

function SQL_FileSites(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'core.FileSites'
        . ' WHERE sitename IN (' . $env['dbaccess'] . ')'
        . $addsql
        . $env['limit'];
}

function SQL_Files(&$env)
{
    $qa = safe_addslashes($env['auth']);
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'core.Files'
        . ' WHERE (username = \'' . $qa . '\')'
        . $addsql
        . $env['limit'];
}

function SQL_GroupExpression(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' WHERE ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'core.GroupExpression'
        . $addsql
        . $env['limit'];
}

function SQL_MachineCategories(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' WHERE ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'core.MachineCategories'
        . $addsql
        . $env['limit'];
}

function SQL_MachineGroupMap(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' WHERE ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'core.MachineGroupMap'
        . $addsql
        . $env['limit'];
}

function SQL_MachineGroups(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' WHERE ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'core.MachineGroups'
        . $addsql
        . $env['limit'];
}

function SQL_Options(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' WHERE ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'core.Options'
        . $addsql
        . $env['limit'];
}

function SQL_Users(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' WHERE ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'core.Users'
        . $addsql
        . $env['limit'];
}
/* ----- </core> --- begin event --------*/

function SQL_Console(&$env)
{
    $qa = safe_addslashes($env['auth']);
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM  ' . $GLOBALS['PREFIX'] . 'event.Console'
        . ' WHERE (username = \'' . $qa . '\')'
        . $addsql
        . ' ORDER BY site, name, servertime desc '
        . $env['limit'];
}

function SQL_EventScrips(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' WHERE ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM  ' . $GLOBALS['PREFIX'] . 'event.EventScrips'
        . $addsql
        . $env['limit'];
}

function SQL_Events(&$env)
{
    $time = '';
    $msel_sql = '';
    if ($env['days']) {
        $days = $env['days'];
        $time = " AND ( servertime > $days )";
    }
    if ($env['msel_sql']) {
        $msel_sql = $env['msel_sql'];
    }
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM  ' . $GLOBALS['PREFIX'] . 'event.Events'
        . ' WHERE customer IN (' . $env['dbaccess'] . ')'
        . $time
        . $addsql
        . ' ' . $msel_sql
        . ' ORDER BY servertime desc'
        . $env['limit'];
}

function SQL_Notifications(&$env)
{
    $qa = safe_addslashes($env['auth']);
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM  ' . $GLOBALS['PREFIX'] . 'event.Notifications'
        . ' WHERE ( (username = \'' . $qa . '\') '
        . ' OR ( global = 1 ) )'
        . $addsql
        . ' ORDER BY name '
        . $env['limit'];
}

function SQL_NotifySchedule(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM  ' . $GLOBALS['PREFIX'] . 'event.NotifySchedule'
        . ' JOIN  ' . $GLOBALS['PREFIX'] . 'event.Notifications '
        . ' WHERE ( Notifications.id = NotifySchedule.nid )'
        . $addsql
        . $env['limit'];
}

function SQL_Reports(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    $qa = safe_addslashes($env['auth']);
    return 'SELECT ' . $env['columns'] . ' FROM  ' . $GLOBALS['PREFIX'] . 'event.Reports'
        . ' WHERE ( (username = \'' . $qa . '\')'
        . ' OR ( global = 1 ) )'
        . $addsql
        . ' ORDER BY name '
        . $env['limit'];
}

function SQL_SavedSearches(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    $qa = safe_addslashes($env['auth']);
    return 'SELECT ' . $env['columns'] . ' FROM event.SavedSearches'
        . ' WHERE ( (username = \'' . $qa . '\')'
        . ' OR (global = 1) ) '
        . $addsql
        . $env['limit'];
}

function SQL_EventAudit(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    $qa = safe_addslashes($env['auth']);
    return 'SELECT ' . $env['columns'] . ' FROM  ' . $GLOBALS['PREFIX'] . 'event.Audit'
        . ' WHERE (user = \'' . $qa . '\')'
        . $addsql
        . $env['limit'];
}

/* ------ </event> --- begin provision --- */

function SQL_Audit(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'provision.Audit'
        . ' WHERE sitename IN (' . $env['dbaccess'] . ')'
        . $addsql
        . $env['limit'];
}

function SQL_CryptKeys(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'provision.CryptKeys'
        . ' JOIN ' . $GLOBALS['PREFIX'] . 'core.Census '
        . ' WHERE ( CryptKeys.uuid = ' . $GLOBALS['PREFIX'] . 'core.Census.uuid ) '
        . ' AND ' . $GLOBALS['PREFIX'] . 'core.Census.site IN (' . $env['dbaccess'] . ')'
        . $addsql
        . $env['limit'];
}

function SQL_KeyFiles(&$env)
{
    $qa = safe_addslashes($env['auth']);
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT  ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'provision.KeyFiles'
        . ' JOIN Products '
        . ' WHERE ( KeyFiles.productid = Products.productid ) '
        . ' AND ( Products.username = \'' . $qa . '\' )'
        . $addsql
        . $env['limit'];
}

function SQL_MachineAssignments(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'provision.MachineAssignments'
        . ' WHERE sitename IN (' . $env['dbaccess'] . ')'
        . $addsql
        . $env['limit'];
}

function SQL_Meter(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'provision.Meter'
        . ' WHERE sitename IN (' . $env['dbaccess'] . ')'
        . $addsql
        . $env['limit'];
}

function SQL_MeterFiles(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    $qa = safe_addslashes($env['auth']);
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'provision.MeterFiles'
        . ' JOIN Products'
        . ' WHERE ( MeterFiles.productid = Products.productid )'
        . ' and ( Products.username = \'' . $qa . '\' )'
        . $addsql
        . $env['limit'];
}

function SQL_Products(&$env)
{
    $qa = safe_addslashes($env['auth']);
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'provision.Products'
        . ' WHERE ( username = \'' . $qa . '\' )'
        . $addsql
        . $env['limit'];
}

function SQL_SiteAssignments(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'provision.SiteAssignments'
        . ' WHERE sitename IN (' . $env['dbaccess'] . ')'
        . $addsql
        . $env['limit'];
}


/* ---- </provision> --- begin softinst ------*/

function SQL_Machine(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'softinst.Machine'
        . ' JOIN ' . $GLOBALS['PREFIX'] . 'core.Census'
        . ' WHERE ( Machine.id = ' . $GLOBALS['PREFIX'] . 'core.Census.id )'
        . ' AND ' . $GLOBALS['PREFIX'] . 'core.Census.site IN (' . $env['dbaccess'] . ')'
        . $addsql
        . $env['limit'];
}

function SQL_PatchCategories(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' WHERE ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'softinst.PatchCategories'
        . $addsql
        . $env['limit'];
}

function SQL_PatchConfig(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' WHERE ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'softinst.PatchConfig'
        . $addsql
        . $env['limit'];
}

function SQL_PatchExpression(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' WHERE ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'softinst.PatchExpression'
        . $addsql
        . $env['limit'];
}

function SQL_PatchGroupMap(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' WHERE ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'softinst.PatchGroupMap'
        . $addsql
        . $env['limit'];
}

function SQL_PatchGroups(&$env)
{
    $qa = safe_addslashes($env['auth']);
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'softinst.PatchGroups'
        . ' WHERE ( username = \'' . $qa . '\' )'
        . $addsql
        . $env['limit'];
}

function SQL_PatchStatus(&$env)
{

    $qa = safe_addslashes($env['auth']);
    if ($env['wu-stats']) /* if they came from wu-stats.php */ {
        return  "SELECT S.*, C.host, C.site, P.name\n"
            . " FROM " . $GLOBALS['PREFIX'] . "softinst.PatchStatus as S, \n"
            . " " . $GLOBALS['PREFIX'] . "softinst.Patches as P,          \n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,               \n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as U             \n"
            . " WHERE (U.username = '$qa')      \n"
            . " AND   (U.customer = C.site)     \n"
            . " AND   (P.patchid = S.patchid)   \n"
            . " AND   (S.id = C.id)             \n"
            . " GROUP BY S.patchstatusid        \n"
            . " order by site, host, name";
    } else {
        $addsql = '';
        if ($env['optsql']) {
            $addsql = ' AND ' . $env['optsql'];
        }
        return 'SELECT ' . $env['columns'] . " FROM " . $GLOBALS['PREFIX'] . "softinst.PatchStatus, \n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,                            \n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as U                          \n"
            . " WHERE U.username = '$qa'                     \n"
            . " AND  U.customer = C.site                     \n"
            . " AND C.id = PatchStatus.id                    \n"
            . $addsql
            . $env['limit'];
    }
}

function SQL_Patches(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' WHERE ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'softinst.Patches'
        . $addsql
        . ' ORDER BY name '
        . $env['limit'];
}

function SQL_WUConfig(&$env)
{
    $qa = safe_addslashes($env['auth']);
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . " FROM " . $GLOBALS['PREFIX'] . "softinst.WUConfig, \n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G                   \n"
        . " WHERE WUConfig.mgroupid = G.mgroupid      \n"
        . " AND ( ( G.global = 1 )                    \n"
        . " OR    ( G.username = '$qa' ) )            \n"
        . $addsql
        . $env['limit'];
}

/* --- </softinst> --- begin swupdate ---*/

function SQL_Downloads(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'swupdate.Downloads'
        . ' WHERE sitename IN (' . $env['dbaccess'] . ')'
        . $addsql
        . $env['limit'];
}

function SQL_UpdateMachines(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'swupdate.UpdateMachines'
        . ' WHERE sitename IN (' . $env['dbaccess'] . ')'
        . $addsql
        . $env['limit'];
}

function SQL_UpdateSites(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'swupdate.UpdateSites'
        . ' WHERE sitename IN (' . $env['dbaccess'] . ')'
        . $addsql
        . $env['limit'];
}

/* --- </swupdate> begin asset ----*/

function SQL_AssetReports(&$env)
{
    $qa = safe_addslashes($env['auth']);
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'asset.AssetReports'
        . ' WHERE ( (username = \'' . $qa . '\')'
        . ' OR (global = 1) ) '
        . $addsql
        . ' ORDER BY username '
        . $env['limit'];
}

function SQL_AssetSearchCriteria(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' WHERE ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'asset.AssetSearchCriteria'
        . $addsql
        . $env['limit'];
}

function SQL_AssetSearches(&$env)
{
    $qa = safe_addslashes($env['auth']);
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'asset.AssetSearches'
        . ' WHERE ( (username = \'' . $qa . '\')'
        . ' OR (global = 1) ) '
        . $addsql
        . $env['limit'];
}

function SQL_DataName(&$env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' WHERE ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'asset.DataName'
        . $addsql
        . ' ORDER BY name '
        . $env['limit'];
}

function SQL_AssetMachine($env)
{
    $addsql = '';
    if ($env['optsql']) {
        $addsql = ' AND ' . $env['optsql'];
    }
    return 'SELECT ' . $env['columns'] . ' FROM ' . $GLOBALS['PREFIX'] . 'asset.Machine'
        . ' WHERE cust IN (' . $env['dbaccess'] . ')'
        . $addsql
        . ' ORDER BY cust, host '
        . $env['limit'];
}


/* --- </asset db> --- */


function space($num)
{
    for ($i = 0; $i < $num; $i++) {
        echo "<br>";
    }
}


function nbsp($num)
{
    for ($i = 0; $i < $num; $i++) {
        echo "&nbsp;";
    }
}


function tag_int($name, $min, $max, $def)
{
    $valu = get_integer($name, $def);
    return value_range($min, $max, $valu);
}


function quote($txt)
{
    if ($txt != '') {
        $txt = str_replace('"', '""', $txt);
    }
    return '"' . $txt . '"';
}


function squote($txt)
{
    return "'" . $txt . "'";
}


function db_radio($set, $db_check)
{
    $out = array();
    for ($i = 1; $i <= 6; $i++) {
        $out[] = radio('db_check', $i, $db_check) . $set[$i] . db_desc($i);
    }
    return $out;
}


function tbl_radio($set, $tbl)
{
    reset($set);
    $out = array();
    foreach ($set as $k => $v) {
        reset($v);
        foreach ($v as $k1 => $v1) {
            /* we do not want to display any temp tables */
            if (substr($v1, 0, 5) != 'temp_') {
                switch ($v1) {
                        /* CORE tables we don't want displayed */
                    case 'AggregateCksum':
                        break;
                    case 'Census_cksum':
                        break;
                    case 'Census_deleted':
                        break;
                    case 'Descriptions_cksum':
                        break;
                    case 'GroupSettings_cksum':
                        break;
                    case 'GroupSettings_deleted':
                        break;
                    case 'MachineCategories_cksum':
                        break;
                    case 'MachineCategories_deleted':
                        break;
                    case 'MachineGroupMap_cksum':
                        break;
                    case 'MachineGroupMap_deleted':
                        break;
                    case 'MachineGroups_cksum':
                        break;
                    case 'MachineGroups_deleted':
                        break;
                    case 'Scrips_cksum':
                        break;
                    case 'Scrips_deleted':
                        break;
                    case 'SemClears_cksum':
                        break;
                    case 'SemClears_deleted':
                        break;
                    case 'ValueMap_cksum':
                        break;
                    case 'ValueMap_deleted':
                        break;
                    case 'VarValues_cksum':
                        break;
                    case 'VarValues_deleted':
                        break;
                    case 'VarVersions_cksum':
                        break;
                    case 'VarVersions_deleted':
                        break;
                    case 'Variables_cksum':
                        break;
                    case 'Variables_deleted':
                        break;
                    case 'LegacyCache':
                        break;
                    case 'CensusNeedBuild':
                        break;

                        /* EVENT temp table we don't want displayed */
                    case 'ReportGroups':
                        break;

                    case 'AssetData':
                        $out[] = "Asset Data: <a href=\"../asset/query.php\">"
                            . "Export asset information using an asset query</a><br>";
                        break;

                    default:
                        $out[] = radio('s_tbl', $v1, $tbl) . $v1 . table_desc($tbl, $v1);
                }
            }
        }
    }
    return $out;
}


function display_radio($radio_ary)
{
    foreach ($radio_ary as $k => $v) {
        echo "<br>$v";
    }
}


function count_table($table, $db_name, $db)
{
    $sql = "SELECT COUNT(*) FROM $db_name.$table";
    return find_scalar($sql, $db);
}


/* defaults to ie if browser can't be detect */
function return_browser()
{
    $browser = $_SERVER['HTTP_USER_AGENT'];
    if ((stristr($browser, 'Firefox')) && (stristr($browser, 'Mozilla'))) {
        $browser = 'Mozilla';
    } else {
        $browser = 'IE';
    }
    return $browser;
}


function set_headers($browser)
{
    $type = 'inline';
    if ($browser == 'Mozilla') {
        $type = 'attachment';
    }
    return $type;
}


function send_headers($type, $file)
{
    header("Content-Type: application");
    header("Content-Disposition: $type; filename=\"$file\"");
}

function filter_options($auth, $db)
{
    $qu  = safe_addslashes($auth);
    $sql = "select S.id, S.name from\n"
        . " event.SavedSearches as S\n"
        . " left join event.SavedSearches as X\n"
        . " on X.name = S.name\n"
        . " and X.global = 0\n"
        . " and X.username = '$qu'\n"
        . " where S.username = '$qu'\n"
        . " or (S.global = 1 and (X.id is NULL))\n"
        . " order by name, id";
    $set = find_many($sql, $db);
    $out = array();
    reset($set);
    foreach ($set as $key => $row) {
        $sid = $row['id'];
        $out[$sid] = $row['name'];
    }
    return $out;
}

/* create a scroll box of string values */
function EXPORT_saved_search($searches)
{
    if ($searches) {
        $msg = "<select name=\"sel_searchstring[]\" multiple size=\"5\">\n";
        foreach ($searches as $id => $name) {
            $msg .= "<option value=\"$id\">$name</option>\n";
        }
        $msg .= "</select>\n";
    } else {
        $msg = "<b>no event filters.</b>";
    }
    return $msg;
}

/* creates the query string based on users filters */
function querystring($db, $id)
{
    $data = '';
    $sql  = "select * from event.SavedSearches where id = $id";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_array($res);
            $data = $row['searchstring'];
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    } else {
        $msg = report_error($sql, $db);
        echo "<p><b>querystring:$msg</b><p>";
    }
    return $data;
}

/* build the multiple select saved search string */
function mselect_search($sel_searchstring, $mdb)
{
    $savedsearch = '';
    $sql = '';
    if ($sel_searchstring) {
        $n = safe_count($sel_searchstring);
        for ($i = 0; $i < $n; $i++) {
            $item = querystring($mdb, $sel_searchstring[$i]);
            if (strlen($item)) {
                if ($i)
                    $savedsearch .= " OR ($item)";
                else
                    $savedsearch .= "($item)";
            }
        }
        if (strlen($savedsearch)) {
            if ($n > 1) {
                $savedsearch = "($savedsearch)";
            }
            $savedsearch = stripslashes($savedsearch);
            $sql .= " and $savedsearch\n";
        }
        return $sql;
    }
}

/* -------------------------
            main functions
       ------------------------- */

function pickdb(&$env)
{
    $act       = $env['act'];
    $dbset     = $env['dbset']; /* [n] => dbname */
    $env['act'] = 'pdat';
    $instruct  = load_instructions($env);
    $radio_ary = db_radio($dbset, $env['db_check']);
    $submit    = button('Next >');
    $cancel    = button(constButtonCancel);

    echo post_self('myform');
    /* -- <form> -- */

    echo hidden('act', 'ptbl');

    echo $instruct;
    space(1);
    display_radio($radio_ary);
    space(2);
    echo $submit;
    nbsp(2);
    echo $cancel;
    /* -- </form> -- */
    echo form_footer();
}

/* ----- picktbl ------- */
function picktbl(&$env, $db)
{
    $cancel = button(constButtonCancel);
    if ($env['db_check']) {
        $db_name   = db_decode($env['db_check']);
        $sql       = "SHOW TABLES FROM $db_name";
        $instruct  = load_instructions($env);
        $tables    = find_many($sql, $db); /* 2d array */
        $table_ary = tbl_radio($tables, $db_name);
        $submit    = button('Next >');

        echo post_self('myform');
        /* -- <form> -- */

        echo hidden('act', 'pcol');
        echo hidden('db_check', $env['db_check']);

        echo $instruct;
        space(1);
        display_radio($table_ary);
        space(2);
        echo $submit;
        nbsp(2);
        echo $cancel;

        /* -- </form> -- */
        echo form_footer();
    } else  /* error */ {
        $submit = button('< Back');

        echo post_self('myform');
        /* -- <form> -- */

        echo hidden('act', 'pdat');

        s_error();
        space(2);
        echo $submit;
        nbsp(2);
        echo $cancel;

        /* -- </form> -- */
        echo form_footer();
    }
}

/* ----- pickcol -------- */
function pickcol(&$env, $db)
{
    $set      = array();
    $tn       = $env['s_tbl'];
    $cancel   = button(constButtonCancel);
    if (($tn != '') && ($env['db_check'])) {
        $db_name      = db_decode($env['db_check']);
        $set          = find_field_names($db_name, $tn, $db);  /* get the columns from the database.table */
        $out          = create_gang_array($set);             /* creates the array to feed to gang_table */
        $limit        = return_limit($env['priv_admin']);    /* max. export row choices, based on priv  */
        $limit_choice = html_select('user_limit', $limit, 1000, 0);
        $times        = return_time_formats();               /* array of okay time formats */
        $ftime_choice = html_select('ftime', $times, 0, 1);     /* drop box of time formats available */
        $count        = count_table($tn, $db_name, $db);

        $submit    = button('Next >');
        $checkall  = button(constButtonAll);
        $checknone = button(constButtonNone);

        $txtbox    = textbox('optsql', 50, '');
        $instruct  = load_instructions($env);

        $head          = $tn;
        $stn           = '<i>' . $tn . '</i>';
        $opts_choice   = '';
        $search_select = '';
        if ($tn == 'Events') /* ability to pick past time, only for events */ {
            $days = return_days();
            $opts = past_options($env['midn'], $days);
            $opts_choice  .= html_select('opts_choice', $opts, 1, 1);
            space(2);
            $searches      = filter_options($env['auth'], $db);
            $search_select = EXPORT_saved_search($searches);
        }
        $n = ($opts_choice) ? 5 : 3;

        echo post_self('myform');
        /* -- <form> --*/

        echo hidden('act', 'xprt');
        echo hidden('db_check', $env['db_check']);
        echo hidden('s_tbl', $tn);

        echo $instruct;
        space(2);
        $gt  = gang_table($env, $out, 1, $head);
        $can = checkallnone(5);

        echo <<< HERE
    <table>
      <tr>
        <td>$gt</td>
        <td>
          $can
          Please note that at present in the $stn table there are <font color="blue">$count</font> records.
          <br>
          <br>
          <br>
          Next, you will define the criteria for selecting the $stn log records you are interested in.
          <br>
          Note that only $stn logs meeting <b>ALL</b> selection criteria will be retrieved for export.
        </td>
      </tr>
    </table>
HERE;
        space(1);
        echo '2) Select a maximum number of records to export: ' . $limit_choice;
        if ($env['priv_admin']) {
            echo '<i>(0 is unlimited)</i>';
        };
        if ($opts_choice) {   /* hard coded 'event', because its only for event */
            space(2);
            echo '3) Select the period of time from which you want to retrieve event logs: ';
            echo $opts_choice;
            space(2);
            echo '4) Select an event query filter you want to use for retrieving event logs'
                . ' for export by clicking on its name.'
                . ' If you want to use multiple event query filters, press the <b>Ctrl</b> key'
                . ' while selecting event query filters.'
                . ' Please note that if you select multiple event query filters, we will retrieve'
                . ' event logs matching the selection'
                . ' criteria of <b>ANY</b> of the query filters (in other words the event query'
                . ' filters are chained together with'
                . ' <b>OR</b>s). For more information on event query filters please go to the'
                . " <a href =\"../event/search.php\">Event Query Filters</a> page.";
            space(2);
            echo $search_select;
        }
        space(1);
        echo "<br>$n) You can add a SQL selection clause below."
            . ' Please note that any clause you enter in the box below will be'
            . " used as additional selection criteria. This means that only $stn records meeting the above selection criteria"
            . ' <b>AND</b> the one you enter below will be retrieved for export.';
        echo '<br><br>Optional SQL:' . $txtbox;
        echo '<br><br>Example: <br><br> (scrip=238) AND ((string1 < (string2-604800)) AND (string1 >= (string2-1209600)))'
            . '<br><br>The above clause would select all Symantec virus definition dates 1-2 weeks out of date.';
        $n++; /* option number */
        echo double("<br><br>$n) Format of date fields:</b>", $ftime_choice);
        space(2);
        echo $submit;
        nbsp(2);
        echo $cancel;

        /* -- </form> --*/
        echo form_footer();
    } else /* error */ {
        $submit   = button('< Back');

        echo post_self('myform');
        /* -- <form> -- */

        echo hidden('act', 'ptbl');
        echo hidden('db_check', $env['db_check']);

        s_error();
        space(2);
        echo $submit;
        nbsp(2);
        echo $cancel;

        /* -- </form> -- */
        echo form_footer();
    }
}

/* ----- export -------- */
function export(&$env, $db)
{
    $set      = array();
    $out      = array();
    $comma    = FALSE;
    $checked  = FALSE;
    $fields   = '';
    $flds     = '';
    $columns  = '';
    $user_sql = '';
    $act      = $env['act'];
    $table    = $env['s_tbl'];
    $db_name  = db_decode($env['db_check']);
    $sql      = 'SELECT ';
    $fields   = find_field_names($db_name, $table, $db);
    $cancel   = ($env['wu-stats']) ? '' : button(constButtonCancel);
    reset($fields);
    foreach ($fields as $key => $value) {
        if ((get_integer($key, 0) == 1) || $env['wu-stats']) {
            if ($comma) {
                $sql     .= ', ';
                $columns .= ', ';
                $flds    .= '|';
            }
            $comma    = TRUE;
            $checked  = TRUE;
            $sql     .= $value;
            $flds    .= $value;
            $columns .= "$table.$value";
        }
    }
    if ($checked) {
        if ($env['optsql'] != '') /* if the user entered optional sql */ {
            $user_sql = $env['optsql'];
            $user_sql = " ( $user_sql )";
        }
        if ($env['opts_choice']) {
            $now = time();
            $env['days'] = $now - (86400 * $env['opts_choice']);
        }

        $xclfile_name = "$table.csv";
        $sqlfile_name = "$table.sql";
        $xclfile = textbox('xclfile', 60, $xclfile_name);
        $sqlfile = textbox('sqlfile', 60, $sqlfile_name);
        $subxcl  = button(constButtonExcel);
        $subsql  = button(constButtonSQL);

        $create_db  = checkbox('create_db', 0);
        $create_tbl = checkbox('create_tbl', 1);
        $priv_disp  = checkbox('priv_disp', 0);

        $instruct = load_instructions($env);

        $msel_sql = mselect_search($env['sel_searchstring'], $db);     /* converts id array to sql */

        if ($env['wu-stats']) {
            $limit        = return_limit($env['priv_admin']);
            $limit_choice = html_select('user_limit', $limit, 1000, 0);
            $count        = count_table('PatchStatus', $db_name, $db);
        }

        echo post_self('myform');
        /* -- <form> --*/

        echo hidden('act', 'dprt');
        echo hidden('user_limit', $env['user_limit']);
        echo hidden('optsql', $user_sql);
        echo hidden('s_tbl', $table);
        echo hidden('db_check', $env['db_check']);
        echo hidden('fields', $flds);
        echo hidden('columns', $columns);
        echo hidden('xclfile', $xclfile_name);
        echo hidden('sqlfile', $sqlfile_name);
        echo hidden('xclfile_name', $xclfile_name);
        echo hidden('sqlfile_name', $sqlfile_name);
        echo hidden('days', $env['days']);
        echo hidden('msel_sql', $msel_sql);
        echo hidden('wu-stats', $env['wu-stats']);
        echo hidden('ftime', $env['ftime']);

        space(2);
        echo $instruct;
        space(2);
        echo table_header();
        echo double('Filename:', $xclfile);
        echo double('Export to:', $subxcl);
        echo double('<br>', '<br>');
        echo double('Filename:', $sqlfile);
        echo double('Create Database:', $create_db);
        echo double('Create Table:', $create_tbl);
        echo double('Export to:', $subsql);
        if ($env['wu-stats']) {
            echo "Notice: $count records in this table.";
            space(1);
            echo "Select a maximum number of records to export: " . $limit_choice;
            space(2);
        }
        echo table_footer();
        echo $cancel;
        space(2);
        if ($env['debug']) {
            echo double("<font color=\"green\">Display to screen</font>", $priv_disp);
        }
        /* -- </form> --*/
        echo form_footer();
    } else  /* error */ {
        $out    = create_gang_array($fields);       /* creates the array to feed to gang_table */
        $submit = button('< Back');

        echo post_self('myform');
        /* -- <form> -- */

        echo hidden('act', 'pcol');
        echo hidden('db_check', $env['db_check']);
        echo hidden('s_tbl', $table);

        s_error();
        space(2);
        echo $submit;
        nbsp(2);
        echo $cancel;

        /* -- </form> -- */
        echo form_footer();
    }
}

/* ----- export_data ------- */
function export_data(&$env, $db)
{
    $post       = $env['post'];
    $fields     = $env['fields'];
    $user_sql   = $env['optsql'];
    $priv_disp  = $env['priv_disp'];
    $msel_sql   = $env['msel_sql'];

    $txt        = '';
    $sql = return_table_query($env);             /* provides the proper sql for any table */
    if ($sql == '')                              /* something bad happened */ {
        r_error();
    } else {
        $env['out'] = $sql; /* the sql query */
        $browser = return_browser();
        $type    = set_headers($browser);

        if ($post == constButtonExcel) {
            $fields .= ($env['wu-stats']) ? wu_fields() : '';
            $env['header'] = explode('|', $fields);

            $file = $env['xclfile'];
            $ext  = strrchr($file, '.');              /* gets the file extension */
            if (($file == '') || ($ext != '.csv')) {
                $file = $env['xclfile_name'];        /* renames to original if needed */
            }
            $txt = query_to_csv($env);
            if (!$priv_disp) {
                send_headers($type, $file);
            }
        }
        if ($post == constButtonSQL) {
            $env['header'] = explode('|', $fields);

            $file = $env['sqlfile'];
            $ext  = strrchr($file, '.');
            if (($file == '') || (($ext != '.sql') && ($ext != '.txt'))) {
                $file = $env['sqlfile_name'];
            }
            $txt = query_to_sql($env);
            if (!$priv_disp) {
                send_headers($type, $file);
            }
        }
        if (!$priv_disp) {
            ob_end_clean();
        }
        print($txt);
    }
}

/*-------------
    | Main Program
    */

$name = 'Database Export Wizard';
$env  = array();
$now  = time();
$db   = db_connect();
$auth = process_login($db);
$comp = component_installed();
$user = user_data($auth, $db);

$env['midn']         = midnight($now);
$dbg                 = get_integer('debug', 1);
$priv_debug          = @($user['priv_debug']) ?  1 : 0;
$env['priv_admin']   = @($user['priv_admin']) ? 1 : 0;
$debug               = ($priv_debug) ? $dbg : 0;

$filter              = @($user['filtersites']) ? 1 : 0;

$env['priv']         = $priv_debug;
$env['debug']        = $debug;
$env['db']           = $db;
$env['carr']         = site_array($auth, $filter, $db);          /* list of site(s) user can access */
$env['dbaccess']     = db_access($env['carr']);                /* sql site list to tack onto query */
$env['dbset']        = return_db();                            /* array of predefined db's */
$act                 = get_string('act', 'pdat');               /* current action */
$env['act']          = $act;
$env['auth']         = $auth;

$env['db_check']     = tag_int('db_check', 0, 7, 0);              /* the database choices */

$env['user_limit']   = get_integer('user_limit', 3000);         /* user chosen max_export_rows */
$env['create_db']    = get_integer('create_db', 0);             /* user creates db in sql file */
$env['create_tbl']   = get_integer('create_tbl', 0);            /* user creates tables in sql file */
$env['priv_disp']    = get_integer('priv_disp', 0);             /* only available to debug users */
$env['opts_choice']  = get_integer('opts_choice', 0);           /* time in the past for event.reports */
$env['days']         = get_integer('days', 0);
$env['wu-stats']     = get_integer('wu-stats', 0);              /* post not from export.php */

$env['s_tbl']        = get_string('s_tbl', '');                 /* user selected table */
$env['optsql']       = get_string('optsql', '');                /* user input optional sql */
$env['sql']          = get_string('sql', '');                   /* generated sql */
$env['file']         = get_string('file', '');                  /* file name to save to */
$env['post']         = get_string('button', '');                /* what button the user pressed */
$env['fields']       = get_string('fields', '');                /* user selected fields */
$env['columns']      = get_string('columns', '');               /* user selected columns */
$env['xclfile']      = get_string('xclfile', '');               /* excel filename */
$env['sqlfile']      = get_string('sqlfile', '');               /* sql filename */
$env['xclfile_name'] = get_string('xclfile_name', '');          /* original filename */
$env['sqlfile_name'] = get_string('sqlfile_name', '');          /* "        "        */
$env['msel_sql']     = get_string('msel_sql', '');              /* multiple query filter SQL */

$env['ftime']        = get_integer('ftime', 0);                 /* user selected time formats */

$env['sel_searchstring'] = get_argument('sel_searchstring', 0, array()); /* array of filter id's */

if ($env['post'] == constButtonCancel) {
    $act = 'pdat';
}
if ($env['post'] == constButtonNone) {
    $act = 'pcol';
}
if ($env['post'] == constButtonAll) {
    $act = 'pcol';
}

/* this post comes from wu-stats.php */
if ($env['act'] == 'ps') {
    $act  = 'xprt';
    $name = 'Software Update Export';
    $env['wu-stats'] = true;
    $env['db_check'] = 6;
    $env['s_tbl']    = 'PatchStatus';
}

/*--- now display ---*/

if ($act != 'dprt') {
    echo standard_html_header($name, $comp, $auth, 0, 0, 0, $db);
}

switch ($act) {
    case 'ptbl':
        picktbl($env, $db);
        break;
    case 'pcol':
        pickcol($env, $db);
        break;
    case 'xprt':
        export($env, $db);
        break;
    case 'dprt':
        export_data($env, $db);
        break;
    case 'pdat':
        pickdb($env);
        break;
}

if ($act != 'dprt') {
    echo head_standard_html_footer($auth, $db);
}
