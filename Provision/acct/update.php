<?php

/*
Revision History

02-Aug-02    AAM     Added config database, and asset management tables
                      (AssetData, Machine, and DataName) to hfnlog database.
16-Aug-02    EWB     Added new fields to Machine, DataName, Users tables.
16-Aug-02    EWB     Added new fields to Scrips, Variables, Revisions tables
16-Aug-02    EWB     mysql errors always logged
26-Aug-02    EWB     New format of config tables.
30-Aug-02    EWB     New fields for config variable scoping.
20-Sep-02    EWB     New fields in AssetData
20-Sep-02    EWB     Giant refactoring.
 3-Oct-02    EWB     Added AssetSearch, AssetReports, AssetSearchCriteria
 3-Oct-02    EWB     Added description to AssetSearches
 7-Oct-02    EWB     Removed config from AssetSearches
 7-Oct-02    EWB     Index AssetData by clatest
 9-Oct-02    EWB     Removed description from AssetSearches
21-Oct-02    NL      Added expires to AssetSearches & AssetSearchCriteria
21-Oct-02    NL      Added more text to "just testing" message.
31-Oct-02    NL      Added change_rpt, umin, umax to AssetReports
 5-Nov-02    EWB     priv_aquery, priv_areport
 6-Nov-02    NL      added updates db & tables: Downloads, UpdateSites, UpdateMachines
18-Nov-02    NL      added Downloads.sitename to updates db
18-Nov-02    NL      added Downloads.username & Downloads.password to updates db
18-Nov-02    NL      changed field sizes for all varchars in updates db to 255
18-Nov-02    NL      added get_fieldtypes
19-Nov-02    NL      added priv_downloads, priv_updates
25-Nov-02    EWB     Census table.
25-Nov-02    EWB     priv_config
 2-Dec-02    NL      change UpdateSites.sitename from "key" to "unique index"
 4-Dec-02    EWB     Reorginization Day
10-Dec-02    EWB     Factored common asset building code into l-abld.php
10-Dec-02    EWB     Factored common config building code into l-cbld.php
20-Dec-02    EWB/AAM Added isset to tests to avoid PHP error messages.
30-Dec-02    EWB     Add indexing to config database.
30-Dec-02    EWB     Unique index for scrips and variables.
20-Jan-03    AAM     Nice little cleanup:  fixed bug where redcommand was
                        not returning a value; fixed bug where find_indices
                        was not returning the index name; added links so that
                        the user doesn't have to type URLs to actually do the
                        update.  Database update:
                        Globals: removed type; added itype; added indices itype
                            and uniq.
                        Locals: removed type; added srev, itype; added indices
                            itype and uniq.
                        Made index named explicit for num in Scrips, name in
                            Variables.
                        Events: changed machine from varchar(20) to varchar(50).

20-Jan-03   AAM     Change ordering so that indices are only updated after the
                    fields that they index are brought up to date.
20-Jan-03   AAM     While simulating, pretend that every SQL command works.
23-Jan-03   EWB     Moved changes from 3.0 to 3.1
 7-Feb-03   EWB     vast changes for 3.1 database scheme.
10-Feb-03   EWB     Added defmail to reports.
12-Feb-03   EWB     Unique key for event reports.
24-Feb-03   EWB     Name, username must be unique for Reports, Searches, Notifications
 3-Mar-03   EWB     Added revision to downloads table.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
12-Mar-03   EWB     Rename database update to swupdate.
12-Mar-03   EWB     Does not use PHP authorization.
14-Mar-03   EWB     Shows counts for core database.
18-Mar-03   EWB     Added modified to server options.
19-Mar-03   NL      Comment out "if (trim($msg)) debug_note($msg)" cuz $debug non-existant
20-Mar-03   EWB     No more nanoheal email.
14-Apr-03   NL      Added sitefilter to Customers table.
14-Apr-03   EWB     Select core database at first.
14-Apr-03   EWB     More quoting.
17-Apr-03   EWB     Added site to event Console table.
17-Apr-03   EWB     No more Alex email.
26-Apr-03   NL      Create NotSiteFilters, RptSiteFilters,  RptSiteFilters(Asset)
28-Apr-03   NL      Create admin_sitefiltertree() and update_obj_sitefilter() to
                    populate NotSiteFilters, RptSiteFilters & RptSiteFilters(Asset)
29-Apr-03   EWB     Added priv_asset.
29-Apr-03   EWB     Unclassified notification console: 'multiple sites';
29-Apr-03   NL      Add filtersites to Users, NotSiteFilters, RptSiteFilters &
                    RptSiteFilters(Asset).
29-Apr-03   NL      Change Customers.sitefilter default to '0'.
30-Apr-03   NL      Rename admin_sitefiltertree --> user_sitefiltertree
30-Apr-03   NL      To avoid errors in debug mode, check that new SiteFilter
                        tables exist before populating them w/ update_obj_sitefilter()
30-Apr-03   NL      To avoid errors in for users with no site, check that user's
                        sitefilter exists before using to populate new sitefilter tables
 2-May-03   EWB     Asset group leaders added to dataname table.
 2-May-03   NL      Dont populate NotSiteFilters, RptSiteFilters & RptSiteFilters(Asset)
                    Delete user_sitefiltertree() and update_obj_sitefilter().
14-May-03   EWB     Changes for Asset Change Report.
20-Jun-03   EWB     Addded 'provisional' to Machine table.
24-Jun-03   NL      single_cust(): add missing comma
24-Jun-03   NL      change AssetSearches.displayfields from varchar(255) to text.
11-Jul-03   EWB     Added Files table.
17-Jul-03   EWB     Added links field to reports and notifications.
17-Jul-03   EWB     Files have expiration dates.
17-Jul-03   EWB     Added creation date for DataName table.
21-Jul-03   EWB     Added file notes.
23-Jul-03   EWB     Added priv_restrict to Users table.
29-Aug-03   EWB     Created FileSites table.
 4-Sep-03   EWB     Changed 'FileSites.site' to 'FileSites.sitename';
 5-Sep-03   EWB     Created Files.counted.
 5-Sep-03   MMK     Added password security and dangerous attribute fields to the
                    Variables table in siteman.
 6-Oct-03   EWB     Added "include" field to DataName table.
21-Oct-03   EWB     Added "next_run" field to Reports, AssetReports table.
14-Nov-03   NL      Added "assetlinks" field to Reports tables.
 3-Dec-03   EWB     Create core.Customers.owner
 5-Dec-03   NL      Insert Scrip 1000 (zero-event notn) into event.EventScrips
10-Dec-03   AAM     Added "provisional" column to "Revisions" table in "config"
                    database.
10-Dec-03   EWB     Don't add scrip 1000 to EventScrips if it's already there.
 2-Jan-04   EWB     Create provision database and tables.
 2-Feb-04   EWB     Create MeterFiles table.
 4-Feb-04   EWB     New Meter fields.
 6-Feb-04   EWB     Meter Product/Owner
 6-Feb-04   EWB     removed Products.meterfilename field.
 9-Feb-04   EWB     added provision site/machine assignments.
12-Feb-04   EWB     Added provision.Audit.owner
13-Feb-04   EWB     Added provision.CryptKeys.created
20-Feb-04   EWB     Added provision.CryptKeys.lastuse
20-Feb-04   EWB     Added provision.CryptKeys.access
 8-Mar-04   EWB     Added core.Users.priv_provis
11-Mar-04   EWB     Added event.Events.deleted.
25-Mar-04   EWB     Added event.Notifications.next_run.
30-Mar-04   EWB     Added swupdate.Downloads.(global/name/owner)
31-Mar-04   EWB     Adding Downloads.name updates UpdateSites.version.
 8-Apr-04   EWB     Reports this_run/retries, notify this_run
 5-May-04   EWB     Machine Groups tables.
 3-Jun-04   BTE     Added tables for the softinst database.
11-Jun-04   EWB     New fields for server option tables.
14-Jun-04   EWB     Changed a few patch defaults.
16-Jun-04   EWB     More changes to patch tables.
18-Jun-04   EWB     softinst.WUConfigCache
21-Jun-04   EWB     softinst.Patches.lastupdate, lastreference
22-Jun-04   EWB     softinst.PatchGroups.style, softinst.PatchGroups.search
22-Jun-04   EWB     softinst.PatchExpression
22-Jun-04   EWB     softinst.PatchGroups.boolstring
 8-Jul-04   BTE     softinst.PatchStatus.lasterrordate
14-Jul-04   EWB     softinst.PatchConfig.installdelay
14-Jul-04   EWB     removed: softinst.WUConfig.installdelay
14-Jul-04   EWB     softinst.Patches.msname, title, locale, canuninstall
14-Jul-04   EWB     softinst.PatcheStatus.detected
14-Jul-04   EWB     removed: softinst.PatchStatus.laststatus
14-Jul-04   EWB     rename softinst.WUConfigCache to softinst.Machine
19-Jul-04   EWB     remove: PatchConfig.scheduletime, PatchConfig.notifyscheduletime
19-Jul-04   EWB     added: PatchConfig.sched(minute,hour,day,month,week,random,type,fail)
19-Jul-04   EWB     added: PatchConifg.notify(minute,hour,day,month,week,random,type,fail)
16-Aug-04   BTE     Added softinst.Machine.lastdefconfigid and
                    softinst.Machine.lastdefchange.
18-Aug-04   BTE     removed: softinst.Machine.lastconfigid and
                    softinst.Machine.nextwuclient.
25-Aug-04   EWB     added: MachineGroups.created, PatchGroups.created
 2-Sep-04   EWB     added: MachineGroupMap.uniq
 2-Sep-04   EWB     added: WUConfig.updatecache, cacheseconds
13-Sep-04   EWB     removed: PatchConfig.schedfail
17-Sep-04   EWB     UpdateMachines.force changed to UpdateMachines.doforce
30-Sep-04   EWB     core.MachineGroups.human, softinst.PatchGroups.human
 8-Oct-04   EWB     New notification schedule.
13-Oct-04   BJS     added: to event.Reports the fields include_user, include_text & subject_text
10-Nov-04   EWB     added: softinst.PatchConfig.configtype
16-Nov-04   EWB     added: asset.AssetReports.content
 9-Nov-04   BJS     added: event.Reports.skip_owner / asset.EventReports.skip_owner
10-Nov-04   BJS     added: Notifications.skip_owner
28-Dec-04   BTE     Added softinst.WUConfig.restart,
                    softinst.PatchConfig.chain, and
                    softinst.PatchConfig.chainseconds.
29-Dec-04   BTE     Moved softinst.PatchConfig.chain and .chainseconds to
                    softinst.WUConfig.chain and .chainseconds.
26-Jan-05   BJS     added: asset.AssetReports.tabular
17-Feb-05   BJS     added: event.Report.aggregate / event.ReportGroups
18-Feb-05   EWB     add unique index to swupdate.UpdateMachines
16-Mar-05   EWB     add unique index for core.Census.uuid
 6-Apr-05   EWB     auto increment primary key for scrip cache
 8-Apr-05   EWB     mysql 4 does not like default on auto_increment field
27-May-05   EWB     mysql 4 does not support mysql_create_db
 1-Jun-05   BJS     added: asset.AssetReports.translated, AssetSearchCriteria.translated
                    AssetSearches.translated
 2-Jun-05   EWB     asset.xxx.translated defaults to 1.
 6-Jun-05   BJS     Set Asset.Reports, SearchCriteria & Searches to zero after table creation.
 6-Jun-05   BJS     added: event.Reports.omit.
 8-Jul-05   EWB     gconfig.VarValues.valu: mediumtext
19-Jul-05   BJS     added: event.Reports.detaillinks.
22-Jul-05   EWB     removed: gconfig.*.deleted
26-Jul-05   EWB     added: event.Notifications.gincluded and friends.
27-Jul-05   EWB     builds mcat/mgrp for notification exclude / suspend.
17-Aug-05   BJS     added: asset.AssetReports.xmlurl, xmluser, xmlpass.
18-Aug-05   BJS     added: asset.AssetReports.xmlfile.
25-Aug-05   BJS     added: asset.AssetReports.xmlpasv
29-Aug-05   BTE     Changed core.MachineGroups.name and 
                    core.MachineCategories.category from varchar(255) to 
                    varchar(100), added mechanism to recompute checksums and to
                    create checksum cache tables for the core and gconfig 
                    databases.
 6-Sep-05   BJS     added: core.Census.deleted, removed event.Events.deleted 
                    added: l-ebld.php
 7-Sep-05   BJS     Commented out gconfig checksum procedures for RH9.
 7-Sep-05   BTE     Put back gconfig checksum procedures.
12-Sep-05   BTE     Added checksum invalidation code.
13-Sep-05   BTE     Added core.MachineCategories.revl (revision level for the
                    precedence column), fixed undefined find_many function when
                    calling functions in l-dsyn.php.
14-Sep-05   BTE     Update the revision level when the precedence changes for
                    the table core.MachineCategories.
19-Sep-05   BJS     Added: l-core.php, create_coreCensus().
20-Sep-05   BTE     Added proper table specification to build_cron_cat.
26-Sep-05   BJS     Added: Notifications.email_footer, email_per_site & email_footer_txt.
27-Sep-05   BJS     Added: Notifications.email_sender.
12-Oct-05   BTE     Moved gconfig tables into core, moved core table creation
                    to csrv.so.
13-Oct-05   BTE     Additional includes to support siteman to gconfig
                    conversion.
21-Oct-05   BJS     Removed: Notifications.ginclude/gexclude/gsuspend. 
                    Addded:  Notifications.group_include/exclude/suspend (text).
25-Oct-05   BJS     Notifications.group_include/exclude/suspend default 0.
01-Nov-05   BJS     Added: update_groups().
02-Nov-05   BJS     Removed: Reports.ginclude/gexclude, added group_include/exclude/suspend.
05-Nov-05   BTE     Specify the operation when calling checksum invalidation code.
07-Nov-05   BJS     Removed: Reports.group_suspend, suspend.
09-Nov-05   BJS     Removed: Reports.filtersites, Notifications.filtersites.
10-Nov-05   BJS     Removed: event.NotSiteFilters/RptSiteFilters.
10-Nov-05   BTE     Some delete operations should not be permanent.
02-Dec-05   BJS     Added: AssetReports.group_include/exclude.
                    Removed: AssetReports.ginclude/gexclude. 
05-Dec-05   BJS     Removed: asset.RptSiteFilters.
09-Jan-06   BTE     Updated to handle audit/CSRV rights, bug 2955:
                    PatchExpression could not be created.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.  Bug 2969: Remove the siteman to gconfig
                    conversion.
06-May-06   BTE     Bug 3209: 4.2 to 4.3 server upgrade does not work
                    correctly.  Bug 3373: Fix InvalidVars.valu to use
                    mediumtext.
31-May-06   BTE     Bug 3410: Update.php: make sure no critical updates are
                    done.
06-Jul-06   BTE     Another parameter to build_events_table.
07-Jul-06   BTE     Bug 3508: CSRV right can disappear.
31-Jul-06   BTE     Bug 3566: Handle the new auditing database.
07-Aug-06   BTE     Function find_tables is now in l-db.php.
11-Aug-06   BTE     Permit lots of time to run update.php.
26-Aug-06   BTE     Bug 3601: Superseded status can be misleading in MUM.
20-Sep-06   BTE     Bug 2826: Make MUM approve/decline wizards a little easier
                    to use (not so large).
09-Oct-06   BTE     Bug 3710: Extend the patch status page.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.
13-Nov-06   JRN     Added recompute Cksum for registry managment.
09-Dec-06   BTE     Bug 3842: Make mandatory an update attribute.
15-Dec-06   BTE     Bug 3957: Fix inconsistencies in PatchStatus table.
03-Apr-07   BTE     Added code to add reports/schedules and to update them.
09-Apr-07   BTE     Added data versioning code to the update procedure.
20-Apr-07   BTE     Updated .sql file comments.
03-May-07   BTE     Default reports, sections, and schedules are owned by the
                    server owner.
04-May-07   BTE     Updated to use new data versioning functions.
05-May-07   BTE     Updated some comments.
09-May-07   BTE     Added the report css server option.
04-Jun-07   BTE     Added constServerOptionServerURL.
06-Jun-07   BTE     Added code to support SavedSearches.searchuniq.
27-Jun-07   BTE     Bug 4198: Fix searchuniq for global SavedSearches.
29-Jun-07   BTE     Updated data version for report sections (control sql has
                    changed).
08-Jul-07   BTE     Bug 4225: Reports and sections: settings changed after
                    upgrade.
09-Jul-07   BTE     Updated report defaults.
31-Jul-07   BTE     asrchuniq support and universal unique function.
09-Sep-07   BTE     Updated some comments.
04-Oct-07   BTE     Updated data version, some bugfixes.
23-Oct-07   BTE     Added AssetSearches.querytype.
27-Dec-07   BTE     Added Notifications.autotask.
11-Jan-08   WOH     Added link to recompute service databases.
07-Feb-08   BTE     Bug 4407: Add non-unique indices to speedup
                    provision.Meter.
19-Feb-08   BTE     Bug 4415: Cache the Scrip title table in shared memory.
21-Feb-08   WOH     Updated Aggregate set to recompute service databases.
17-Mar-08   BTE     Bug 4382: Add report output as body of email.  Bug 4434:
                    Change certain MUM section defaults.

*/

die("Code was removed");

// $title = 'Database Update';

// ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
// include('../lib/l-util.php');
// include('../lib/l-db.php');
// include('../lib/l-sql.php');
// include('../lib/l-serv.php');
// include('../lib/l-head.php');
// include('../lib/l-ebld.php');
// include('../lib/l-abld.php');
// include('../lib/l-core.php');
// include('../lib/l-rlib.php');
// include('../lib/l-jump.php');
// include('../lib/l-cbld.php');
// include('../lib/l-cnst.php');
// include('../lib/l-errs.php');
// include('../lib/l-gsql.php');
// include('../lib/l-dsyn.php');
// include('../lib/l-gcfg.php');
// include('../lib/l-grps.php');
// include('../lib/l-tabs.php');
// include('../lib/l-gdrt.php');
// include('../lib/l-pdrt.php');
// include('../lib/l-ptch.php');
// include('../lib/l-user.php');
// include('../lib/l-repf.php');

// /* Current version of defined reports
//         Revision History:
//             Num Author  Reason
//             1   BTE     Original version
//             2   BTE     (prior to specific history)
//             3   BTE     (prior to specific history)
//             4   BTE     Changed query filter coldbname for event sections
//             5   BTE     Changed tdoption for event section summary component.
//             6   BTE     Added MHTML output format option.
//             7   BTE     Changed some default sections ("update one").
//             8   BTE     Mhtmlmessage defaults for all reports.
//      */
// define('constReportDataVersion',        8);
// define('constReportDataVersionUpdateOne',   7);
// define('constReportDataVersionUpdateTwo',   8);


// function again()
// {
//     $self = server_var('PHP_SELF');
//     $args = server_var('QUERY_STRING');
//     $href = ($args) ? "$self?$args" : $self;
//     $a    = array();
//     $a[]  = html_link('#top', 'top');
//     $a[]  = html_link('#bottom', 'bottom');
//     $a[]  = html_link('index.php', 'home');
//     $a[]  = html_link($href, 'again');
//     return jumplist($a);
// }


// function real_command()
// {
//     global $command;
//     return ($command) ? true : false;
// }


// // note this is not the same as the library
// // redcommand.

// function redcommand($sql, $db)
// {
//     $real  = real_command();
//     $msg   = str_replace("\n", "<br>\n&nbsp;&nbsp;&nbsp;", $sql);
//     $color = ($real) ? 'red' : 'blue';
//     $msg   = "<font color='$color'>$msg</font><br>\n";
//     if ($real) {
//         $res = command($sql, $db);
//         if (!$res) {
//             $error = mysqli_error($db);
//             $errno = mysqli_errno($db);
//             $msg  .= "$errno:$error<br>\n";
//         }
//     } else {
//         /* While simulating, pretend every command works. */
//         $res = "fake success";
//     }
//     echo fontspeak("command: $msg<br>");
//     return $res;
// }

// function debug_note($msg)
// {
//     $show = @$GLOBALS['debug'];
//     if (isset($show) && ($show)) {
//         echo "<font color=\"green\">$msg</font><br>\n";
//     }
// }



// function find_databases($db)
// {
//     $dbs  = array();
//     $res  = (($___mysqli_tmp = mysqli_query($db, "SHOW DATABASES")) ? $___mysqli_tmp : false);
//     if ($res) {
//         $n = mysqli_num_rows($res);
//         for ($i = 0; $i < $n; $i++) {
//             $name = ((mysqli_data_seek($res, $i) && (($___mysqli_tmp = mysqli_fetch_row($res)) !== NULL)) ? array_shift($___mysqli_tmp) : false);
//             $dbs[$name] = 1;
//         }
//         ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
//     }
//     return $dbs;
// }

// function find_fields($dbname, $table, $db)
// {
//     $fields = array();
//     $res = (($___mysqli_tmp = mysqli_query($db, "SHOW COLUMNS FROM $dbname.$table")) ? $___mysqli_tmp : false);
//     if ($res) {
//         $n = (($___mysqli_tmp = mysqli_num_fields($res)) ? $___mysqli_tmp : false);
//         for ($i = 0; $i < $n; $i++) {
//             $field = ((($___mysqli_tmp = mysqli_fetch_field_direct($res, $i)->name) && (!is_null($___mysqli_tmp))) ? $___mysqli_tmp : false);
//             $fields[$field] = 1;
//         }
//         ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
//     }
//     return $fields;
// }

// function get_fieldtypes($database, $tablename, $db)
// {
//     $fieldtypes = array();
//     $sql = "SHOW FIELDS FROM $database.$tablename";
//     $res  = command($sql, $db);
//     if ($res) {
//         while ($row = mysqli_fetch_array($res)) {
//             $fieldtypes[$row['Field']] = $row['Type'];
//         }
//     }
//     return $fieldtypes;
// }

// function find_indices($database, $tablename, $db)      // added 12/2
// {
//     $indices = array();
//     $sql = "SHOW INDEX FROM $database.$tablename";
//     $res  = command($sql, $db);
//     if ($res) {
//         while ($row = mysqli_fetch_array($res)) {
//             // $indexdata array:
//             //      Table,Non_unique,Key_name,Seq_in_index,Column_name,
//             //      Collation, Cardinality,Sub_part,Packed,Comment
//             $indexdata  = $row;
//             $index      = $row['Key_name'];
//             $indices[$index] = $indexdata;
//         }
//         ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
//     }
//     return $indices;
// }

// function find_all_customers($db)
// {
//     $cust = array();
//     $sql  = "SELECT distinct customer FROM Customers order by customer";
//     $res  = command($sql, $db);
//     if ($res) {
//         if (mysqli_num_rows($res)) {
//             while ($row = mysqli_fetch_array($res)) {
//                 $cust[] = $row['customer'];
//             }
//             sort($cust);
//         }
//         ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
//     }
//     return $cust;
// }

// function index_field($index, $field, $table, $db)
// {
//     if (!isset($index[$field])) {
//         $sql = "alter table $table add index ($field)";
//         redcommand($sql, $db);
//     }
// }


// function ownertable($accesslist)
// {
//     $owners = array();
//     reset($accesslist);
//     foreach ($accesslist as $key => $data) {
//         $list = explode(',', $data);
//         foreach ($list as $k => $d) {
//             $list[$k] = str_replace("'", '', $d);
//         }
//         $owners[$key] = $list;
//     }
//     return $owners;
// }


// function show_table($table, $fields)
// {
//     if ($fields) {
//         $table = fontspeak($table);
//         echo "<table border='2' align='left' cellspacing='2' cellpadding='2'>\n";
//         echo "<tr><th colspan='2'>$table</th></tr>\n";
//         $i = 0;
//         foreach ($fields as $key => $data) {
//             $i++;
//             $idx = fontspeak($i);
//             $key = fontspeak($key);
//             echo "<tr><td>$idx</td><td>$key</td></tr>\n";
//         }
//         echo "</table>\n";
//     }
// }

// function dump($dbname, $table, $tables, $db)
// {
//     if (isset($tables[$table])) {
//         $fields = find_fields($dbname, $table, $db);
//         if ($fields) {
//             show_table($table, $fields);
//         }
//     }
// }


// function newline()
// {
//     echo "<br clear='all'><br><br>\n";
// }


// function show_owners($owners)
// {
//     if (safe_count($owners)) {
//         newline();
//         echo "<table border='2' align='left' cellspacing='2' cellpadding='2'>";
//         reset($owners);
//         foreach ($owners as $key => $data) {
//             $name = $key;
//             $cust = implode('<br>', $data);

//             $name = fontspeak($name);
//             $cust = fontspeak($cust);

//             echo "<tr><td>$name</td><td>$cust</td></tr>\n";
//         }
//     }
//     echo "</table>\n";
// }

// function update_hfn($db)
// {
//     $sql = "select * from Users where username = 'hfn' and priv_admin = 0";
//     $res = command($sql, $db);
//     if ($res) {
//         if (mysqli_num_rows($res) == 1) {
//             $sql  = "update Users set";
//             $sql .= " priv_admin=1,";
//             $sql .= " priv_search=1,";
//             $sql .= " priv_notify=1,";
//             $sql .= " priv_report=1,";
//             $sql .= " priv_aquery=1,";          // 11/5/2002
//             $sql .= " priv_areport=1,";         // 11/5/2002
//             $sql .= " priv_downloads=1,";       // 11/19/2002
//             $sql .= " priv_updates=1,";         // 11/19/2002
//             $sql .= " priv_config=1,";          // 11/25/2002
//             $sql .= " priv_provis=1,";          // 3/08/2004
//             $sql .= " priv_asset=1,";           // 4/29/2003
//             $sql .= " priv_debug=0,";            // 8/16/2002
//             $sql .= " priv_audit=1,";           // 12/31/05
//             $sql .= " priv_csrv=1";             // 12/31/05
//             $sql .= " where username = 'hfn'";
//             redcommand($sql, $db);
//         }
//         ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
//     }
// }


// function update_single($username, $priv, $email, $db)
// {
//     $sql = "select * from Users where username = '$username'";
//     $res = command($sql, $db);
//     if ($res) {
//         if (mysqli_num_rows($res) == 0) {
//             $sql  = "";
//             $sql .= "insert into Users set";
//             $sql .= " username='$username',";
//             $sql .= " notify_mail='$email',";
//             $sql .= " report_mail='$email',";
//             $sql .= " priv_admin=$priv,";
//             $sql .= " priv_notify=$priv,";
//             $sql .= " priv_report=$priv,";
//             $sql .= " priv_search=$priv,";
//             $sql .= " priv_areport=$priv,";     // 11/5/2002
//             $sql .= " priv_aquery=$priv,";      // 11/5/2002
//             $sql .= " priv_downloads=$priv,";   // 11/19/2002
//             $sql .= " priv_updates=$priv,";     // 11/19/2002
//             $sql .= " priv_config=$priv,";      // 11/25/2002
//             $sql .= " priv_provis=$priv,";      // 3/08/2004
//             $sql .= " priv_asset=$priv,";       // 4/29/2003
//             $sql .= " priv_debug=0,";            // 8/16/2002
//             $sql .= " priv_audit=$priv,";       // 12/31/2005
//             if ($username == 'hfn') {
//                 $sql .= " priv_csrv=1";
//             } else {
//                 $sql .= " priv_csrv=0";
//             }
//             redcommand($sql, $db);
//         }
//         ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
//     }
// }


// /*
//     |  This is a non-debug version of find_many
//     */

// function find_many_nondebug($sql, $db)
// {
//     $set = array();
//     $res = mysqli_query($db, $sql);
//     if ($res) {
//         while ($row = mysqli_fetch_assoc($res)) {
//             $set[] = $row;
//         }
//         ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
//     }
//     return $set;
// }

// /*
//     |  This is a non-debug version of find_one
//     */

// function find_single($sql, $db)
// {
//     $row = array();
//     $res = mysqli_query($db, $sql);
//     if ($res) {
//         if (mysqli_num_rows($res) == 1) {
//             $row = mysqli_fetch_assoc($res);
//         }
//         ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
//     }
//     return $row;
// }



// /*
//     |  Adding the unique index on sitename,machine will
//     |  automatically remove the duplicates ... however
//     |  I'd prefer to keep just the newest record and
//     |  and remove the others.
//     */

// function fix_update_machines($db)
// {
//     $sql = "select * from UpdateMachines\n"
//         . " order by sitename, machine, timecontact desc";
//     $set = find_many_nondebug($sql, $db);
//     if ($set) {
//         $dup = 0;
//         $hhh = '';
//         $sss = '';
//         $num = safe_count($set);
//         reset($set);
//         foreach ($set as $key => $row) {
//             $id   = $row['id'];
//             $host = $row['machine'];
//             $site = $row['sitename'];
//             if (($site == $sss) && ($host == $hhh)) {
//                 echo "$site, $host is not unique<br>\n";
//                 $sql = "delete from UpdateMachines\n"
//                     . " where id = $id";
//                 redcommand($sql, $db);
//                 $dup++;
//             }
//             $sss = $site;
//             $hhh = $host;
//         }
//         echo "Survey of $num machines, $dup duplicates<br>\n";
//     } else {
//         echo "UpdateMachines currently empty ...<br>\n";
//     }
// }


// /*
//     |  $db = database handle
//     |  Find all notifications that currently exist with the excluded
//     |  and suspend fields set. These fields will contain a comma 
//     |  seperated list of machine names.
//     |  We must lookup each machine name and get its mgroupid.
//     |  We finish by inserting the mgroupid list into the new
//     |  notification fields, group_suspend & group_exclude.
//     |  proc: GRPS_ located in l-grps.php.
//    */
// function update_groups($db)
// {
//     $machines_val      = '';
//     $machines_mgroupid = '';
//     $excluded_mgroupid = '';
//     $finished_list     = array();

//     $sql = " select id, machines, excluded, suspend\n"
//         . " from  " . $GLOBALS['PREFIX'] . "event.Notifications\n"
//         . " where (excluded != '')\n"
//         . " or (machines != '')";
//     $set = find_many($sql, $db);
//     if ($set) {
//         reset($set);
//         foreach ($set as $key => $row) {
//             $machines_val = $row['machines'];
//             $excluded_val = $row['excluded'];
//             $suspend_val  = $row['suspend'];
//             $n_id         = $row['id'];

//             if ($machines_val != '') {
//                 $machines_list = explode(",", $machines_val);
//                 if ($machines_list) {
//                     /* build a string of suspend mgroupids */
//                     $machines_mgroupid = GRPS_translate_groupname_to_mgroupid($machines_list, $db);
//                 }
//             }
//             if ($excluded_val != '') {
//                 $excluded_list = explode(",", $excluded_val);
//                 if ($excluded_list) {
//                     /* build a string of excluded mgroupids */
//                     $excluded_mgroupid = GRPS_translate_groupname_to_mgroupid($excluded_list, $db);
//                 }
//             }

//             $finished_list[$n_id][constGroupSuspend] = $machines_mgroupid;
//             $finished_list[$n_id][constGroupExclude] = $excluded_mgroupid;
//             $finished_list[$n_id]['suspend']         = $suspend_val;
//         }
//         /*
//              | $finished_list = an array indexed by notification id
//              | and contains the a string value for the 'group_suspend', 
//              | 'group_exlcude' and 0 or posix time stamp for 'suspend'.
//              |
//              | Loop through this array and exectute the SQL for each 
//              | notification.
//             */
//         $now = time();
//         reset($finished_list);
//         foreach ($finished_list as $notification_id => $group_entry) {
//             $group_suspend = $group_entry[constGroupSuspend];
//             $group_exclude = $group_entry[constGroupExclude];
//             $suspend       = $group_entry['suspend'];

//             $sql = "update  " . $GLOBALS['PREFIX'] . "event.Notifications set\n"
//                 . " group_suspend = '$group_suspend',\n"
//                 . " group_exclude = '$group_exclude',\n"
//                 . " modified = $now,\n"
//                 . " suspend  = $suspend\n"
//                 . " where id = $notification_id";
//             $res = redcommand($sql, $db);
//         }
//     }
// }


// function update_users($owners, $db)
// {
//     newline();
//     update_single('hfn', 1, '', $db);
//     reset($owners);
//     foreach ($owners as $key => $data) {
//         update_single($key, 0, '', $db);
//         foreach ($data as $k => $d) {
//             update_customer($key, $d, $db);
//         }
//     }
// }


// function update_customer($username, $customer, $db)
// {
//     $qu = safe_addslashes($username);
//     $qs = safe_addslashes($customer);
//     $sql  = "select * from Customers where";
//     $sql .= " username = '$qu' and";
//     $sql .= " customer = '$qs'";
//     $res = command($sql, $db);
//     if ($res) {
//         if (mysqli_num_rows($res) == 0) {
//             $sql  = "";
//             $sql .= "insert into Customers set";
//             $sql .= " username='$qu',";
//             $sql .= " customer='$qs'";
//             redcommand($sql, $db);
//         }
//     }
// }


// function update_customers($owners, $db)
// {
//     newline();
//     reset($owners);
//     foreach ($owners as $key => $data) {
//         foreach ($data as $k => $d) {
//             update_customer($key, $d, $db);
//         }
//     }
// }


// function show_database($dbname, $tables, $db)
// {
//     $n = safe_count($tables);
//     if ($n) {
//         $msg = "Database $dbname contains $n tables:<br>";
//         echo fontspeak($msg);
//         $i = 0;
//         foreach ($tables as $key => $data) {
//             $i++;
//             dump($dbname, $key, $tables, $db);
//             if ($i > 3) {
//                 newline();
//                 $i = 0;
//             }
//         }
//         newline();
//     }
// }


// function single_cust($customer, $db)
// {
//     $qs   = safe_addslashes($customer);
//     $sql  = "select * from Customers";
//     $sql .= " where customer = '$qs'";
//     $sql .= " and username = ''";
//     $res = command($sql, $db);
//     if ($res) {
//         if (mysqli_num_rows($res) == 0) {
//             $sql  = "insert into Customers set";
//             $sql .= " customer = '$qs',";
//             $sql .= " username = ''";
//             redcommand($sql, $db);
//         }
//         ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
//     }
// }


// function sum_table($name, $db)
// {
//     if ($name) {
//         $sql = "select count(*) from $name";
//         $res = command($sql, $db);
//         if ($res) {
//             $num = mysqli_result($res, 0);
//             $msg = "<b>$name</b> contains <b>$num</b> record(s).<br>";
//             echo fontspeak($msg);
//         }
//     }
// }

// function fix_customers($db)
// {
//     $cust = find_all_customers($db);
//     if (safe_count($cust)) {
//         reset($cust);
//         foreach ($cust as $key => $data) {
//             //        echo "debug cust $data<br>";
//             single_cust($data, $db);
//         }
//     }
// }

// function index_error()
// {
//     global $command;
//     if ($command) {
//         $href    = '../config/rebuild.php';
//         $rebuild = "<a href='$href'>rebuild</a>";
//         $msg  = "Could not create unique index for config database because";
//         $msg .= " duplicates already exist.<br>Either you can manually remove the";
//         $msg .= " duplicate entries, or just $rebuild the config database instead.<br><br>";
//         echo fontspeak($msg);
//     }
// }


// function para($text)
// {
//     return "<p>$text</p>\n";
// }

// function use_database($db, $dlist, $dbname)
// {
//     if (!isset($dlist[$dbname])) {
//         echo fontspeak("database <i>$dbname</i> does not exist; it will be created<br>\n");
//         $sql = "create database if not exists $dbname";
//         $res = mysqli_query($db, $sql);
//         if (!$res) {
//             $err = mysqli_error($db);
//             $num = mysqli_errno($db);
//             $txt = "$num:$err";
//             echo para($txt);
//             logs::log(__FILE__, __LINE__, $txt, 0);
//         }
//     }

//     mysqli_select_db($db, $dbname);
//     newline();
// }


// function drop_field($dbname, $table, $field, $db)
// {
//     $fields = find_fields($dbname, $table, $db);
//     if (isset($fields[$field])) {
//         $sql = "alter table $table drop $field";
//         redcommand($sql, $db);
//     }
// }


// function add_field($fields, $tab, $name, $after, $db)
// {
//     if (!isset($fields[$name])) {
//         $sql = "alter table $tab\n"
//             . " add $name\n"
//             . " int(11) not null default 0\n"
//             . " after $after";
//         redcommand($sql, $db);
//     }
// }



// /*
//     |  Main program
//     */

// $db  = db_pconnect();
// if ($db) {
//     if (!mysqli_select_db($db, core)) {
//         logs::log(__FILE__, __LINE__, 'core does not exist, continue anyway', 0);
//     }
// } else {
//     $msg  = "The database is currently unavailable.  ";
//     $msg .= "Please try again later.";
//     $msg = fontspeak("<p><b>$msg</b></p>");
//     die($msg);
// }

// // $authuser = process_login($db);  // not for this one
// $authuser = getenv('REMOTE_USER');

// $comp = component_installed();

// /* Allow plenty of time and ignore client disconnects */
// set_time_limit(0);
// ignore_user_abort(true);

// $msg = ob_get_contents();           // save the buffered output so we can...
// ob_end_clean();                     // (now dump the buffer)
// echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
// if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

// $command = (get_integer('command', 0)) ? 1 : 0;
// $delete  = (get_integer('delete', 0)) ?  1 : 0;
// $recompute = (get_integer('recompute', 0)) ? 1 : 0;
// $recompute_rmgt = (get_integer('recompute_rmgt', 0)) ? 1 : 0;
// $recompute_srvc = (get_integer('recompute_srvc', 0)) ? 1 : 0;
// $self    = server_var('PHP_SELF');
// $date    = datestring(time());

// echo "<h2>$date</h2>\n";
// echo again();

// if ($command) {
//     $msg  = "<font color='red' size='5'>";
//     $msg .= "For Real";
//     $msg .= "</font>";
// } else {
//     $href = "$self?command=1&delete=$delete";
//     $link = "<a href='$href'>this link</a>";

//     $msg  = 'Just testing.  If you want to execute this script on ';
//     $msg .= "the database, click on $link.";
// }

// echo "<br><br><p><b>$msg</b></p><br>";

// if ($recompute) {
//     $msg  = "<font color='red' size='5'>";
//     $msg .= "Recomputing checksums...";
//     echo "<br><br><p><b>$msg";
//     $err = PHP_DSYN_RecomputeChecksums(
//         CUR,
//         constAggregateSetGConfig,
//         TRUE
//     );
//     if ($err != constAppNoErr) {
//         logs::log(
//             __FILE__,
//             __LINE__,
//             "update: PHP_DSYN_RecomputeChecksums returned " . $err,
//             0
//         );
//         echo "An error occurred recomputing the";
//         echo "configuration checksums.";
//     } else {
//         echo "done.";
//     }
//     echo "</font>";
// } else {
//     $href = "$self?command=0&delete=0&recompute=1";
//     $link = "<a href='$href'>this link</a>";

//     $msg  = "To recompute configuration checksums, click on $link.";

//     echo "<br><br><p><b>$msg";
// }

// if ($recompute_srvc) {
//     $msg  = "<font color='red' size='5'>";
//     $msg .= "Recomputing checksums...";
//     echo "<br><br><p><b>$msg";
//     $err = PHP_DSYN_RecomputeChecksums(
//         CUR,
//         constAggregateDSYNSetSrvcMgmtTables,
//         TRUE
//     );
//     if ($err != constAppNoErr) {
//         logs::log(
//             __FILE__,
//             __LINE__,
//             "update: PHP_DSYN_RecomputeChecksums returned " . $err,
//             0
//         );
//         echo "An error occurred recomputing the";
//         echo "configuration checksums.";
//     } else {
//         echo "done.";
//     }
//     echo "</font>";
// } else {
//     $href = "$self?command=0&delete=0&recompute_srvc=1";
//     $link = "<a href='$href'>this link</a>";

//     $msg  = "To recompute service checksums, click on $link.";

//     echo "<br><br><p><b>$msg";
// }
// echo "</b></p><br>";

// /* this will recompute only the checksums for the registry
//      managment databases. For a list of all aggregate set search
//      for constAggregateSetRegistryManagment*/
// if ($recompute_rmgt) {
//     $msg  = "<font color='red' size='5'>";
//     $msg .= "Recomputing checksums...";
//     echo "<br><br><p><b>$msg";
//     $err = PHP_DSYN_RecomputeChecksums(
//         CUR,
//         constAggregateSetRegistryManagment,
//         TRUE
//     );
//     if ($err != constAppNoErr) {
//         logs::log(
//             __FILE__,
//             __LINE__,
//             "update: PHP_DSYN_RecomputeChecksums returned " . $err,
//             0
//         );
//         echo "An error occurred recomputing the";
//         echo " registry managment checksums.";
//     } else {
//         echo "done.";
//     }
//     echo "</font>";
// } else {
//     $href = "$self?command=0&delete=0&recompute_rmgt=1";
//     $link = "<a href='$href'>this link</a>";

//     $msg  = "To recompute the registry managment";
//     $msg .= "checksums, click on $link.";

//     echo "<br><br><p><b>$msg";
// }

// echo "</b></p><br>";

// if ($delete) {
//     $msg  = "<font color='red' size='5'>";
//     $msg .= "Will delete unused fields!";
//     $msg .= "</font>";
// } else {
//     $href = "$self?command=$command&delete=1";
//     $link = "<a href='$href'>this link</a>";

//     $msg  = 'Will not delete unused fields.  If you want to delete unused ';
//     $msg .= "fields, click on $link.";
// }

// echo "<br><br><p><b>$msg</b></p><br>";

// $dlist = find_databases($db);

// if ($dlist) {
//     $n = safe_count($dlist);
//     $msg = "mysql contains $n databases:<br>\n";
//     reset($dlist);
//     foreach ($dlist as $key => $data) {
//         $msg .= "$key<br>\n";
//     }
//     echo fontspeak($msg);
// }

// //
// // Set up the event logging database
// //

// $dbname = 'core';
// $dbname = (getenv('DB_PREFIX') ?: '') . 'core';

// use_database($db, $dlist, $dbname);

// $tables = find_tables($dbname, $db);
// show_database($dbname, $tables, $db);

// // mysqldump -u weblog --opt --no-data core > core.sql
// // mysql -u root -p hfnlog < hfnlog.sql

// $tab = 'Files';
// if (isset($tables[$tab])) {
//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields['note']))              // 7/21/2003
//     {
//         $sql  = "alter table $tab add\n";
//         $sql .= " note text\n";
//         $sql .= " not null\n";
//         $sql .= " default ''\n";
//         $sql .= " after link";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['counted']))      // 9/5/2003
//     {
//         $sql  = "alter table $tab add\n";
//         $sql .= " counted int(11)\n";
//         $sql .= " not null\n";
//         $sql .= " default 0\n";
//         $sql .= " after expires";
//         redcommand($sql, $db);
//     }
// } else {
//     $sql  = "";
//     $sql .= "CREATE TABLE Files (\n";
//     $sql .= "  id    int(11)     not null auto_increment,\n";
//     $sql .= "  name  varchar(50) default '' not null,\n";
//     $sql .= "  type  varchar(50) default '' not null,\n";
//     $sql .= "  username varchar(50) default '' not null,\n";
//     $sql .= "  created int(11) default 0 not null,\n";
//     $sql .= "  expires int(11) default 0 not null,\n";
//     $sql .= "  counted int(11) default 0 not null,\n";
//     $sql .= "  path text default '' not null,\n";
//     $sql .= "  link text default '' not null,\n";
//     $sql .= "  note text default '' not null,\n";  // 7/21/03
//     $sql .= "  primary key (id)\n";
//     $sql .= ")";
//     redcommand($sql, $db);
// }

// $tab = 'FileSites';
// if (!isset($tables[$tab])) {
//     $sql  = "";
//     $sql .= "CREATE TABLE FileSites (\n";
//     $sql .= "  id    int(11)     not null auto_increment,\n";
//     $sql .= "  fid   int(11)     default 0  not null,\n";
//     $sql .= "  sitename  varchar(50) default '' not null,\n";
//     $sql .= "  primary key (id)\n";
//     $sql .= ")";
//     redcommand($sql, $db);
// }

// $tables = find_tables($dbname, $db);

// $table  = 'Users';
// if (isset($tables[$table])) {
//     $fields = find_fields($dbname, $table, $db);
//     if ($fields) {
//         if (isset($fields['priv_admin'])) {
//             update_single('hfn', 1, '', $db);
//             update_hfn($db);
//         }
//     }
// }

// if (isset($tables['Customers'])) {
//     //     echo "debug tables cust<br>";
//     fix_customers($db);
// }

// reset($tables);
// foreach ($tables as $key => $data) {
//     sum_table($key, $db);
// }


// newline();

// $dbname = 'event';
// $dbname = (getenv('DB_PREFIX') ?: '') . $dbname;

// use_database($db, $dlist, $dbname);

// $tables = find_tables($dbname, $db);
// show_database($dbname, $tables, $db);

// $tab = 'Console';
// if (isset($tables[$tab])) {
//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields['site'])) // added 4/17/03
//     {
//         $sql  = "alter table Console\n";
//         $sql .= "  add site varchar(50) NOT NULL after username";
//         redcommand($sql, $db);
//     }
// } else {
//     $sql  = "";
//     $sql .= "CREATE TABLE Console (\n";
//     $sql .= "   id int(11) NOT NULL auto_increment,\n";
//     $sql .= "   priority tinyint(1),\n";
//     $sql .= "   name varchar(50) not null,\n";
//     $sql .= "   username varchar(50),\n";
//     $sql .= "   site varchar(50) not null,\n";
//     $sql .= "   nid int(11) DEFAULT 0 NOT NULL,\n";
//     $sql .= "   servertime int(11) DEFAULT 0 NOT NULL,\n";
//     $sql .= "   expire int(11),\n";
//     $sql .= "   count int(11) DEFAULT '1',\n";
//     $sql .= "   event_list text,\n";
//     $sql .= "   config text,\n";
//     $sql .= "   PRIMARY KEY (id)\n";
//     $sql .= ")";
//     redcommand($sql, $db);
// }

// // mysqldump -u weblog --opt event SavedSearches > search.sql
// // mysqldump -u weblog --opt event Notifications > notify.sql
// // mysqldump -u weblog --opt event Reports       > report.sql

// // mysql -u weblog event < search.sql
// // mysql -u weblog event < notify.sql
// // mysql -u weblog event < report.sql

// /*
//     |  ntype
//     |     0: every X seconds (classic)
//     !     1: illegal/invalid.
//     |     2: crontab schedule, ignore proximity
//     |     3: crontab schedule, honor proximity
//     !     4: one shot notification.
//     */

// $tab = 'Notifications';
// $byt = 'tinyint(1)';
// $def = 'not null default';

// if (!isset($tables[$tab])) {
//     $sql = "create table Notifications (\n"
//         . "  id        int(11) not null auto_increment,\n"
//         . "  global    $byt     $def  0,\n"
//         . "  ntype     $byt     $def  0,\n"     // 10/08/2004
//         . "  priority  $byt     $def  3,\n"
//         . "  name      varchar(50) $def '',\n"
//         . "  username  varchar(50) $def '',\n"
//         . "  days      $byt     $def  0,\n"
//         . "  solo      $byt     $def  0,\n"
//         . "  console   $byt     $def  1,\n"
//         . "  email     $byt     $def  1,\n"
//         . "  emaillist text     $def '',\n"
//         . "  defmail   $byt     $def  0,\n"
//         . "  search_id int(11)  $def  0,\n"
//         . "  seconds   int(11)  $def  0,\n"  // 10/08/2004
//         . "  threshold int(11)  $def  0,\n"
//         . "  last_run  int(11)  $def  0,\n"
//         . "  next_run  int(11)  $def  0,\n"  //  3/25/2004
//         . "  this_run  int(11)  $def  0,\n"  //  4/08/2004
//         . "  suspend   int(11)  $def  0,\n"
//         . "  retries   int(11)  $def  0,\n"  // 10/08/2004
//         . "  group_include text $def '',\n"  // 10/21/2005
//         . "  group_exclude text $def '',\n"  // 10/21/2005
//         . "  group_suspend text $def '',\n"  // 10/21/2005
//         . "  config    text     $def '',\n"
//         . "  enabled   int(11)  $def  0,\n"
//         . "  links     $byt     $def  1,\n"
//         . "  created   int(11)  $def  0,\n"
//         . "  modified  int(11)  $def  0,\n"
//         . "  skip_owner  $byt   $def  0,\n"  //12/10/2004
//         . "  email_footer   $byt $def 0,\n"    // 9/26/05
//         . "  email_per_site $byt $def 0,\n"    // 9/26/05
//         . "  email_footer_txt text $def '',\n" // 9/26/05
//         . "  email_sender   $byt $def 0,\n"   // 9/27/05
//         . "  autotask $byt $def 0,\n"    // 11/26/07
//         . "  primary key (id),\n"
//         . "  unique index uniq (username,name)\n"
//         . ")";
//     redcommand($sql, $db);
// } else {
//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields['links'])) {
//         $sql = "alter table $tab add\n"
//             . " links tinyint(1)\n"
//             . " $def 1\n"
//             . " after enabled";        // 7/18/2003
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['next_run'])) {
//         $sql = "alter table $tab\n"
//             . " add next_run\n"
//             . " int(11) $def 0\n"
//             . " after last_run";       // 3/25/2004
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['this_run'])) {
//         $sql = "alter table $tab\n"
//             . " add this_run\n"
//             . " int(11) $def 0\n"
//             . " after next_run";       // 4/08/2004
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['retries'])) {
//         $sql = "alter table $tab\n"
//             . " add retries\n"
//             . " int(11) $def 0\n"
//             . " after suspend";       // 10/08/2004
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['group_include'])) {   // Oct-21-05
//         $sql = "alter table $tab\n"
//             . " add group_include\n"
//             . " text $def ''\n"
//             . " after retries";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['group_exclude'])) {   // Oct-21-05
//         $sql = "alter table $tab\n"
//             . " add group_exclude\n"
//             . " text $def ''\n"
//             . " after group_include";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['group_suspend'])) {   // Oct-21-05
//         $sql = "alter table $tab add\n"
//             . " group_suspend\n"
//             . " text $def ''\n"
//             . " after group_exclude";
//         redcommand($sql, $db);
//         /*
//              | Updates the new Notification fields group_suspend/exclude 
//              | to contain the applicable mgroupids from the machine names 
//              | stored in the old notification fields 'machines' & 'excluded'.
//             */
//         if (real_command()) {
//             update_groups($db);
//         }
//     }
//     if (!isset($fields['seconds'])) {
//         $sql = "alter table $tab\n"
//             . " add seconds\n"
//             . " int(11) $def 0\n"
//             . " after search_id";     // 10/08/2004
//         redcommand($sql, $db);
//         $sql = "update $tab set\n"
//             . " seconds = frequency_minutes*60";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['ntype'])) {
//         $sql = "alter table $tab\n"
//             . " add ntype\n"
//             . " $byt $def 0\n"
//             . " after global";       // 10/08/2004
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['skip_owner'])) {
//         $sql = "alter table $tab\n"
//             . " add skip_owner\n"
//             . " $byt $def 0\n"
//             . " after ntype";   // 12/10/04
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['email_footer'])) {
//         $sql = "alter table $tab\n"
//             . " add email_footer\n"
//             . " $byt $def 0\n"
//             . " after skip_owner";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['email_per_site'])) {
//         $sql = "alter table $tab\n"
//             . " add email_per_site\n"
//             . " $byt $def 0\n"
//             . " after email_footer";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['email_footer_txt'])) {
//         $sql = "alter table $tab\n"
//             . " add email_footer_txt\n"
//             . " text not null default ''\n"
//             . " after email_per_site";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['email_sender'])) {
//         $sql = "alter table $tab\n"
//             . " add email_sender\n"
//             . " $byt $def 0\n"
//             . " after email_footer_txt";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['autotask'])) {
//         $sql = "alter table $tab\n"
//             . " add autotask\n"
//             . " $byt $def 0\n"
//             . " after email_sender";
//         redcommand($sql, $db);
//     }
//     if ($delete) {
//         // 10/21/05
//         drop_field($dbname, $tab, 'ginclude',         $db);
//         drop_field($dbname, $tab, 'gexclude',         $db);
//         drop_field($dbname, $tab, 'gsuspend',         $db);
//         drop_field($dbname, $tab, 'frequency_minutes', $db);
//         drop_field($dbname, $tab, 'filtersites',      $db);
//     }
// }

// /*
//     | coordinate with /www/main/lib/l-next.php
//     |
//     | type
//     |    0: invalid
//     |    1: minute  0-59
//     |    2: hour    0-23
//     |    3: mday    1-31
//     !    4: wday    0-7  (0:sunday)
//     |    5: month   1-12
//     |    6: week    1-5
//     |    7: yday    1-366
//     |
//     |   10: one shot, min time (posix)
//     |   11: one shot, max time (posix)
//     |
//     |  100: proximity    (default 600)
//     |  101: max failures
//     |
//     |  week                 intval((mday+6)/7)
//     |     1: mday  1-07
//     |     2: mday  8-14
//     |     3: mday 15-21
//     |     4: mday 22-28
//     |     5: mday 29-31
//     |
//     |  For example, this means 15:20 on Thanksgiving Day
//     |
//     |     (month:11,week:4,wday:4,hour:15,minute:20)
//     |
//     */

// $tab = 'NotifySchedule';
// if (!isset($tables[$tab])) {
//     $sql = "create table $tab (\n"
//         . "  id   int(11) not null auto_increment,\n"
//         . "  nid  int(11) $def 0,\n"
//         . "  type int(11) $def 0,\n"
//         . "  data int(11) $def 0,\n"
//         . "  primary key (id)\n"
//         . ")";
//     redcommand($sql, $db);
// }


// /* Remove the NotSiteFilters table from the Notifications database */
// $tab = 'NotSiteFilters';
// if (isset($tables[$tab])) {
//     if ($delete) {
//         $sql = "drop table $tab";
//         redcommand($sql, $db);
//         unset($tables[$tab]);
//     }
// }


// $tab  = 'Reports';
// if (!isset($tables[$tab])) {
//     $sql = "create table Reports (\n"
//         . "  id int(11) not null auto_increment,\n"
//         . "  global   tinyint(1)  $def 0,\n"
//         . "  name     varchar(50) $def '',\n"
//         . "  username varchar(50) $def '',\n"
//         . "  emaillist text $def '',\n"
//         . "  defmail tinyint(1) $def 0,\n"
//         . "  file    tinyint(1) $def 0,\n"             // 7/11/03
//         . "  format varchar(10) $def 'text',\n"
//         . "  cycle   int(11) $def 0,\n"
//         . "  hour    int(11) $def 0,\n"
//         . "  minute  int(11) $def 0,\n"
//         . "  wday    int(11) $def 0,\n"
//         . "  mday    int(11) $def 0,\n"
//         . "  enabled int(11) $def 0,\n"
//         . "  links tinyint(1) $def 1,\n"         // 7/17/03
//         . "  detaillinks tinyint(1) $def 0,\n"   // 7/19/05
//         . "  assetlinks  tinyint(1) $def 1,\n"   // 11/14/03
//         . "  last_run int(11) $def 0,\n"
//         . "  next_run int(11) $def 0,\n"         // 10/21/03
//         . "  this_run int(11) $def 0,\n"         //  4/08/04
//         . "  order1 varchar(50) $def '',\n"
//         . "  order2 varchar(50) $def '',\n"
//         . "  order3 varchar(50) $def '',\n"
//         . "  order4 varchar(50) $def '',\n"
//         . "  details  int(11) $def 0,\n"
//         . "  umin     int(11) $def 0,\n"
//         . "  umax     int(11) $def 0,\n"
//         . "  created  int(11) $def 0,\n"
//         . "  modified int(11) $def 0,\n"
//         . "  retries  int(11) $def 0,\n"  // 4/08/2004
//         . "  config text $def '',\n"
//         . "  search_list text $def '',\n"
//         . "  include_user tinyint(1) $def 0,\n"     // 10/13/04
//         . "  include_text tinyint(1) $def 0,\n "    // 10/13/04
//         . "  subject_text varchar(255) default '' not null,\n"  // 10/13/04
//         . "  skip_owner   tinyint(1) $def 0,\n"     // 12/08/04
//         . "  aggregate    tinyint(1) $def 0,\n"     // 02/15/05
//         . "  omit         tinyint(1) $def 0,\n"     // 6/6/05
//         . "  group_include text $def '',\n"
//         . "  group_exclude text $def '',\n"
//         . "  primary key (id),\n"
//         . "  unique index uniq (username,name)\n"
//         . ")";
//     redcommand($sql, $db);
// } else {
//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields['file'])) {
//         $sql = "alter table Reports add\n"
//             . " file tinyint(1)\n"
//             . " $def 0\n"
//             . " after defmail";           // 7/11/2003
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['next_run'])) {
//         $sql = "alter table $tab add\n"
//             . " next_run int(11)\n"
//             . " $def 0\n"
//             . " after last_run";  // 10/21/2003
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['this_run'])) {
//         $sql = "alter table $tab add\n"
//             . " this_run int(11)\n"
//             . " $def 0\n"
//             . " after next_run";  // 4/08/04
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['links'])) {
//         $sql = "alter table Reports add\n"
//             . " links tinyint(1)\n"
//             . " $def 1\n"
//             . " after enabled";           // 7/17/2003
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['detaillinks'])) {
//         $sql = "alter table Reports add\n"
//             . " detaillinks tinyint(1)\n"
//             . " $def 0\n"
//             . " after links";             // 7/20/2005
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['assetlinks'])) {
//         $sql = "alter table Reports add\n"
//             . " assetlinks tinyint(1)\n"
//             . " $def 1\n"
//             . " after links";           // 11/14/2003
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['retries'])) {
//         $sql = "alter table $tab add\n"
//             . " retries int(11)\n"
//             . " $def 0\n"
//             . " after modified";  // 4/08/04
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['include_user'])) {
//         $sql = "alter table Reports add\n"
//             . " include_user tinyint(1)\n"
//             . " $def 0\n"
//             . " after retries"; // 10/13/04
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['include_text'])) {
//         $sql = "alter table Reports add\n"
//             . " include_text tinyint(1)\n"
//             . " $def 0\n"
//             . " after include_user"; // 10/13/04
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['subject_text'])) {
//         $sql = "alter table Reports add\n"
//             . " subject_text varchar(255)\n"
//             . " default '' not null\n"
//             . " after include_text"; // 10/13/04
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['skip_owner'])) {
//         $sql = "alter table Reports add\n"
//             . " skip_owner tinyint(1)\n"
//             . " $def 0\n"
//             . " after subject_text"; // 12/08/04
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['aggregate'])) {
//         $sql = "alter table Reports add\n"
//             . " aggregate tinyint(1)\n"
//             . " $def 0\n"
//             . " after skip_owner";   // 02/15/05
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['omit'])) {
//         $sql = " alter table Reports add\n"
//             . " omit tinyint(1)\n"
//             . " $def 0\n"
//             . " after aggregate";   // 06/06/05
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['group_include'])) {   // 10/2/05 
//         $sql = " alter table Reports add\n"
//             . " group_include text\n"
//             . " $def ''\n"
//             . " after omit";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['group_exclude'])) {   // 10/2/05
//         $sql = " alter table Reports add\n"
//             . " group_exclude text\n"
//             . " $def ''\n"
//             . " after group_include";
//         redcommand($sql, $db);
//     }
//     $sql = "select id from Reports where created = 0";
//     $res = command($sql, $db);
//     if (($res) && (mysqli_num_rows($res))) {
//         $now = time();
//         $sql = "update Reports set created = $now where created  = 0";
//         redcommand($sql, $db);
//         $sql = "update Reports set modified = $now where modified = 0";
//         redcommand($sql, $db);
//     }
//     if ($delete) {
//         // 10/2/05
//         drop_field('event', 'Reports', 'ginclude',    $db);
//         drop_field('event', 'Reports', 'gexclude',    $db);
//         // 11/9/05
//         drop_field('event', 'Reports', 'filtersites', $db);
//     }
// }


// /* Remove the RptSiteFilters table from events database */
// $tab = 'RptSiteFilters';
// if (isset($tables[$tab])) {
//     if ($delete) {
//         $sql = "drop table $tab";
//         redcommand($sql, $db);
//         unset($tables[$tab]);
//     }
// }

// $tab = 'SavedSearches';
// if (!isset($tables[$tab])) {
//     $sql = "CREATE TABLE $tab (\n"
//         . "  id int(11) not null auto_increment,\n"
//         . "  global tinyint(1) $def 0,\n"
//         . "  name varchar(50) $def '',\n"
//         . "  searchstring text $def '',\n"
//         . "  username varchar(50) $def '',\n"
//         . "  created int(11) $def 0,\n"
//         . "  modified int(11) $def 0,\n"
//         . "  searchuniq varchar(32) $def '',\n"
//         . "  PRIMARY KEY (id),\n"
//         . "  unique index uniq (username,name),\n"
//         . "  unique index searchuniq (searchuniq)\n"
//         . ")";
//     redcommand($sql, $db);
// } else {
//     $flds = find_fields($dbname, $tab, $db);
//     if (!isset($flds['searchuniq'])) {
//         $sql = "alter table $tab\n"
//             . " add searchuniq varchar(32) not null default ''\n";
//         redcommand($sql, $db);
//         $owner = find_global_owner($db);
//         if ($owner) {
//             $sql = "UPDATE $tab SET searchuniq=md5(concat('"
//                 . constDefUserUniq . "',',',name)) WHERE username='"
//                 . "$owner'";
//             redcommand($sql, $db);
//         } else {
//             /* No established owner, make them all hfn_default_item */
//             $sql = "UPDATE $tab SET searchuniq=md5(concat('"
//                 . constDefUserUniq . "',',',name))";
//             redcommand($sql, $db);
//         }
//         $sql = "UPDATE $tab SET searchuniq=md5(concat(username,',',name)) "
//             . 'WHERE searchuniq=\'\'';
//         redcommand($sql, $db);

//         /* Add the index after the field is populated */
//         $sql = "alter table $tab\n"
//             . " add unique searchuniq (searchuniq)";
//         redcommand($sql, $db);
//     }
// }

// $tab = 'Events';
// if (!isset($tables[$tab])) {
//     /* Events table is now stored in /lib/l-ebld.php
//            If you want to add a new field to the Events
//            table, add it to /lib/l-ebld.php.
//         */
//     build_events_table('CREATE TABLE Events', '', $db);
// }
// if ($delete) {
//     drop_field('event', 'Events', 'deleted', $db);
// }

// $tab = 'EventScrips';
// if (isset($tables[$tab])) {
//     $flds = find_fields($dbname, $tab, $db);
//     if (!isset($flds['id'])) {
//         $sql = "alter table $tab\n"
//             . " drop primary key,\n"
//             . " add id int(11) not null\n"
//             . " auto_increment first,\n"
//             . " add primary key (id),\n"
//             . " add unique uniq (scrip,description)";
//         redcommand($sql, $db);
//     }
//     $sql  = "select * from EventScrips\n";
//     $sql .= " where scrip = 1000";
//     $res  = command($sql, $db);
//     if (($res) && (mysqli_num_rows($res) == 0)) {
//         ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
//         $err = PHP_RUNT_AddScripCache(
//             CUR,
//             1000,
//             '1000 - No Event Notification'
//         );
//         if ($err != constAppNoErr) {
//             logs::log(__FILE__, __LINE__, 'update.php: PHP_RUNT_AddScripCache failure', 0);
//         }
//     }
// } else {
//     $sql = "CREATE TABLE EventScrips (\n"
//         . "  id    int(11) not null auto_increment,\n"
//         . "  scrip int(11) $def 0,\n"
//         . "  description varchar(80) $def '',\n"
//         . "  modified int(11) $def 0,\n"
//         . "  unique index uniq (scrip,description),\n"
//         . "  PRIMARY KEY (id)\n"
//         . ")";
//     redcommand($sql, $db);
// }

// if (!isset($tables['ReportGroups'])) //added 2/17/05
// {
//     $sql  = '';
//     $sql .= "CREATE TABLE ReportGroups (\n";
//     $sql .= " id     int(11) not null auto_increment,\n";
//     $sql .= " owner  int(11) $def 0,\n";
//     $sql .= " member int(11) $def 0,\n";
//     $sql .= " PRIMARY KEY (id)\n";
//     $sql .= ')';
//     redcommand($sql, $db);
// }

// $tab = 'Audit';
// if (isset($tables[$tab])) {
//     /* The Audit table is now in its own database */
//     if ($delete) {
//         $sql = "DROP TABLE Audit";
//         redcommand($sql, $db);
//     }
// }


// newline();
// reset($tables);
// foreach ($tables as $key => $data) {
//     sum_table($key, $db);
// }

// newline();


// $dbname = 'asset';
// $dbname = (getenv('DB_PREFIX') ?: '') . $dbname;

// use_database($db, $dlist, $dbname);

// $tables = find_tables($dbname, $db);
// show_database($dbname, $tables, $db);

// /* The All group must exist before we update the asset management tables */
// $real = real_command();
// if ($real) {
//     groups_init($db, constGroupsInitBuildOnly);
// }

// // Asset management tables
// //
// // mysqldump -u weblog --opt --no-data asset Machine DataName AssetData

// // 10-Dec-02  moved to l-abld.php

// if (!isset($tables['AssetData']))
//     build_assetdata('real', 'AssetData', $db);

// $tab = 'DataName';
// if (isset($tables[$tab])) {
//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields['created']))              // 7/17/2003
//     {
//         $sql  = "alter table $tab add\n";
//         $sql .= " created int(11)\n";
//         $sql .= " not null\n";
//         $sql .= " default 0\n";
//         $sql .= " after groups";
//         redcommand($sql, $db);
//         $now = time();
//         $sql  = "update $tab set\n";
//         $sql .= " created = $now";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['leader']))              // 5/2/2003
//     {
//         $sql  = "alter table $tab add\n";
//         $sql .= " leader tinyint(1)\n";
//         $sql .= " not null\n";
//         $sql .= " default 0\n";
//         $sql .= " after created";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['include']))             // 10/6/2003
//     {
//         $sql  = "alter table $tab add\n";
//         $sql .= " include tinyint(1)\n";
//         $sql .= " not null\n";
//         $sql .= " default 0\n";
//         $sql .= " after leader";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['clientname'])) {
//         $sql  = "alter table $tab add\n";
//         $sql .= " clientname varchar(50)\n";
//         $sql .= " not null\n";
//         $sql .= " default ''\n";
//         $sql .= " after include";
//         redcommand($sql, $db);
//         $sql = "update $tab set clientname = name";
//         redcommand($sql, $db);
//         $sql = "alter table $tab add unique uniq2 (clientname)";
//         redcommand($sql, $db);
//     }
// } else {
//     build_dataname($db);
// }

// $tab = 'Machine';
// if (isset($tables[$tab])) {
//     $fld  = 'provisional';
//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields[$fld]))      // 6/20/2003
//     {
//         $sql  = "alter table $tab add\n";
//         $sql .= " $fld int(11)\n";
//         $sql .= " not null\n";
//         $sql .= " default 0";
//         redcommand($sql, $db);
//     }
// } else {
//     build_machine($db);
// }

// //  config removed 10/7/2002
// //  description removed 10/9/2002
// //  expires added 10/21/2002

// //  displayfields changed to text 6/24/2003
// if (!isset($tables['AssetSearches'])) {
//     $def  = 'not null default';
//     $sql  = "CREATE TABLE AssetSearches (\n";
//     $sql .= "  id int(11) NOT NULL auto_increment,\n";
//     $sql .= "  global tinyint(1) $def 0,\n";
//     $sql .= "  name varchar(50) $def '',\n";
//     $sql .= "  searchstring text,\n";
//     $sql .= "  username varchar(50) $def '',\n";
//     $sql .= "  displayfields text default '',\n";
//     $sql .= "  date_code int(2) default 0,\n";
//     $sql .= "  date_value int(11) default 0,\n";
//     $sql .= "  rowsize int(2) default 0,\n";
//     $sql .= "  refresh varchar(10) default '',\n";
//     $sql .= "  expires  int(11) $def 0,\n";
//     $sql .= "  created  int(11) $def 0,\n";
//     $sql .= "  modified int(11) $def 0,\n";
//     $sql .= "  translated tinyint(1) $def 1,\n";
//     $sql .= "  asrchuniq varchar(32) not null default '',\n";
//     $sql .= "  querytype int(11) $def 0,\n";
//     $sql .= "  PRIMARY KEY(id),\n";
//     $sql .= "  unique key uniq (username,name),\n";
//     $sql .= "  unique key asrchuniq (asrchuniq)\n";
//     $sql .= ")";
//     redcommand($sql, $db);
// } else {
//     // Need to check fieldtypes to change them
//     $fields = find_fields('asset', 'AssetSearches', $db);
//     $fieldtypes = get_fieldtypes('asset', 'AssetSearches', $db);

//     // 6/24/2003
//     if (isset($fields['displayfields']) && ($fieldtypes['displayfields'] != 'text')) {
//         $sql  = "ALTER TABLE AssetSearches MODIFY\n";
//         $sql .= "  displayfields text NOT NULL DEFAULT ''";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['translated'])) {
//         $sql  = "alter table AssetSearches add\n";
//         $sql .= " translated tinyint(1)\n";
//         $sql .= " not null default 1\n";
//         $sql .= " after modified";
//         redcommand($sql, $db);

//         $sql  = "update AssetSearches set\n";
//         $sql .= " translated = 0";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['asrchuniq'])) {
//         $sql = "alter table AssetSearches\n"
//             . " add asrchuniq varchar(32) not null default ''\n";
//         redcommand($sql, $db);
//         $owner = find_global_owner($db);
//         if ($owner) {
//             $sql = "UPDATE AssetSearches SET asrchuniq=md5(concat('"
//                 . constDefUserUniq . "',',',name)) WHERE username='"
//                 . "$owner'";
//             redcommand($sql, $db);
//         } else {
//             /* No established owner, make them all hfn_default_item */
//             $sql = "UPDATE AssetSearches SET asrchuniq=md5(concat('"
//                 . constDefUserUniq . "',',',name))";
//             redcommand($sql, $db);
//         }
//         $sql = "UPDATE AssetSearches SET asrchuniq=md5(concat(username,','"
//             . ',name)) WHERE asrchuniq=\'\'';
//         redcommand($sql, $db);

//         /* Add the index after the field is populated */
//         $sql = "alter table AssetSearches\n"
//             . " add unique asrchuniq (asrchuniq)";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['querytype'])) {
//         $sql = "alter table AssetSearches\n"
//             . " add querytype int(11) not null default 0\n";
//         redcommand($sql, $db);
//     }
// }

// //  change_rpt, umin, umax added 10/31/2002
// $tab = 'AssetReports';
// $def = 'not null default';
// $byt = 'tinyint(1)';
// if (!isset($tables[$tab])) {
//     $sql = "create table $tab (\n"
//         . "  id int(11) NOT NULL auto_increment,\n"
//         . "  global    $byt $def 0,\n"
//         . "  name      varchar(50) $def '',\n"
//         . "  username  varchar(50) $def '',\n"
//         . "  emaillist text $def '',\n"
//         . "  defmail   $byt $def 0,\n"
//         . "  file      $byt $def 0,\n"             // 7/11/03
//         . "  format varchar(10) $def 'text',\n"
//         . "  cycle     int(11) $def 0,\n"
//         . "  hour      int(11) $def 0,\n"
//         . "  minute    int(11) $def 0,\n"
//         . "  wday      int(11) $def 0,\n"
//         . "  mday      int(11) $def 0,\n"
//         . "  enabled   int(11) $def 0,\n"
//         . "  links     $byt    $def 1,\n"
//         . "  last_run  int(11) $def 0,\n"
//         . "  next_run  int(11) $def 0,\n"    // 10/21/03
//         . "  this_run  int(11) $def 0,\n"    //  4/08/04
//         . "  order1 varchar(50) $def '',\n"
//         . "  order2 varchar(50) $def '',\n"
//         . "  order3 varchar(50) $def '',\n"
//         . "  order4 varchar(50) $def '',\n"
//         . "  searchid   int(11) $def 0,\n"
//         . "  content    $byt    $def 1,\n"  // 11/16/04
//         . "  change_rpt $byt    $def 0,\n"  // 10/31/02
//         . "  umax       int(11) $def 0,\n"  // 10/31/02
//         . "  umin       int(11) $def 0,\n"  // 10/31/02
//         . "  created    int(11) $def 0,\n"
//         . "  modified   int(11) $def 0,\n"
//         . "  retries    int(11) $def 0,\n"  //  4/08/04
//         . "  log           $byt $def 0,\n"  //  5/13/03
//         . "  include_user  $byt $def 0,\n"  // 10/14/04
//         . "  include_text  $byt $def 0,\n"  // 10/14/04
//         . "  subject_text varchar(255) $def '',\n" // 10/14/04
//         . "  skip_owner    $byt $def 0,\n"  // 12/08/04
//         . "  tabular       $byt $def 0,\n"  // 1/21/05
//         . "  translated    $byt $def 1,\n"  // 5/31/05
//         . "  xmlurl   varchar(255) $def '',\n" // 8/17/05
//         . "  xmluser  varchar(255) $def '',\n" // 8/17/05
//         . "  xmlpass  varchar(255) $def '',\n" // 8/17/05
//         . "  xmlfile  varchar(255) $def '',\n" // 8/18/05
//         . "  xmlpasv       $byt $def 1,\n"     // 8/25/05
//         . "  group_include text $def '',\n"
//         . "  group_exclude text $def '',\n"
//         . "  primary key (id),\n"
//         . "  unique key uniq (username,name)\n"
//         . ")";
//     redcommand($sql, $db);

//     GRPS_update_AssetReports_group_include($db);
// } else {
//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields['links'])) {
//         $sql = "alter table $tab add\n"
//             . " links $byt\n"
//             . " $def 1 after enabled";     // 7/17/2003
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['content'])) {
//         $sql = "alter table $tab add\n"
//             . " content $byt\n"
//             . " $def 1 after searchid";  // 11/16/2004
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['next_run'])) {
//         $sql = "alter table $tab add\n"
//             . " next_run int(11)\n"
//             . " $def 0 after last_run";  // 10/21/2003
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['this_run'])) {
//         $sql = "alter table $tab add\n"
//             . " this_run int(11)\n"
//             . " $def 0 after next_run";  // 4/08/04
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['log'])) {
//         $sql = "alter table $tab add\n"
//             . "  log $byt $def 0\n"
//             . "  after filtersites";  // 5/14/2003
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['retries'])) {
//         $sql = "alter table $tab add\n"
//             . " retries int(11)\n"
//             . " $def 0 after modified";  // 4/08/04
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['file'])) {
//         $sql = "alter table $tab add\n"
//             . " file $byt\n"
//             . " $def 0 after defmail";    // 7/11/2003
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['include_user'])) {
//         $sql = "alter table $tab add\n"
//             . " include_user $byt\n"
//             . " $def 0 after log";         // 10/14/04
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['include_text'])) {
//         $sql = "alter table $tab add\n"
//             . " include_text $byt\n"
//             . " $def 0 after include_user";     // 10/14/04
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['subject_text'])) {
//         $sql = "alter table $tab add\n"
//             . " subject_text varchar(255)\n"
//             . " $def '' after include_text";     // 10/14/04
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['skip_owner'])) {
//         $sql = "alter table $tab add\n"
//             . " skip_owner $byt\n"
//             . " $def 0 after subject_text";      // 12/08/04
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['tabular'])) {
//         $sql = "alter table $tab add\n"
//             . " tabular $byt\n"
//             . " $def 0 after skip_owner";       // 1/21/05
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['translated'])) {
//         $sql = "alter table $tab add\n"
//             . " translated $byt\n"
//             . " $def 1 after tabular";          //5/31/05
//         redcommand($sql, $db);

//         $sql = "update AssetReports set\n"
//             . " translated = 0";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['xmlurl'])) {
//         $sql = "alter table $tab add\n"
//             . " xmlurl varchar(255)\n"
//             . " $def '' after translated";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['xmluser'])) {
//         $sql = "alter table $tab add\n"
//             . " xmluser varchar(255)\n"
//             . " $def '' after xmlurl";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['xmlpass'])) {
//         $sql = "alter table $tab add\n"
//             . " xmlpass varchar(255)\n"
//             . " $def '' after xmluser";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['xmlfile'])) {
//         $sql = "alter table $tab add\n"
//             . " xmlfile varchar(255)\n"
//             . " $def '' after xmlpass";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['xmlpasv'])) {
//         $sql = "alter table $tab add\n"
//             . " xmlpasv $byt\n"
//             . " $def 1 after xmlfile";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['group_include'])) {
//         $sql = "alter table $tab add\n"
//             . " group_include text\n"
//             . " $def '' after xmlpasv";
//         redcommand($sql, $db);

//         if (real_command()) {
//             GRPS_update_AssetReports_group_include($db);
//         }
//     }
//     if (!isset($fields['group_exclude'])) {
//         $sql = "alter table $tab add\n"
//             . " group_exclude text\n"
//             . " $def '' after group_include";
//         redcommand($sql, $db);
//     }
//     if ($delete) {
//         drop_field($dbname, $tab, 'details',    $db);  // 11/16/2004
//         drop_field($dbname, $tab, 'ginclude',   $db);  // 11/29/2005
//         drop_field($dbname, $tab, 'gexclude',   $db);
//         drop_field($dbname, $tab, 'filtersites', $db);
//     }
// }

// /*
//     | We now have group_include & group_exclude for asset reports
//     | so we no longer need the asset.RptSiteFilters table.
//    */
// $tab = 'RptSiteFilters';
// if (isset($tables[$tab])) {
//     if ($delete) {
//         $sql = "drop table $tab";
//         redcommand($sql, $db);
//         unset($tables[$tab]);
//     }
// }

// if (!isset($tables['AssetSearchCriteria'])) {
//     $sql  = "CREATE TABLE AssetSearchCriteria (\n";
//     $sql .= "  id int(11) NOT NULL auto_increment,\n";
//     $sql .= "  assetsearchid int(4) NOT NULL default 0,\n";
//     $sql .= "  block int(2) default 0,\n";
//     $sql .= "  fieldname varchar(255) default '',\n";
//     $sql .= "  comparison int(2) default 0,\n";
//     $sql .= "  value varchar(255) default '',\n";
//     $sql .= "  groupname varchar(255) default '',\n";
//     $sql .= "  expires int(11) NOT NULL default 0,\n";
//     $sql .= "  translated tinyint(1) NOT NULL default 1,\n";
//     $sql .= "  PRIMARY KEY  (id)\n";
//     $sql .= ")";
//     redcommand($sql, $db);
// } else {
//     $fields = find_fields('asset', 'AssetSearchCriteria', $db);
//     if (!isset($fields['translated'])) {
//         $sql  = "alter table AssetSearchCriteria\n";
//         $sql .= " add translated tinyint(1)\n";
//         $sql .= " not null default 1 after expires";
//         redcommand($sql, $db);

//         $sql  = "update AssetSearchCriteria set\n";
//         $sql .= " translated = 0";
//         redcommand($sql, $db);
//     }
// }


// reset($tables);
// foreach ($tables as $key => $data) {
//     sum_table($key, $db);
// }

// newline();



// //
// // Set up the updates database 11/7/02 NL
// //

// $dbname = 'swupdate';
// $dbname = (getenv('DB_PREFIX') ?: '') . $dbname;

// use_database($db, $dlist, $dbname);

// $tables = find_tables($dbname, $db);
// show_database($dbname, $tables, $db);

// if (!isset($tables['UpdateSites'])) {
//     $sql = "create table UpdateSites (\n"
//         . "  id int(11) not null auto_increment,\n"
//         . "  sitename varchar(255) not null default '',\n"
//         . "  version varchar(255) not null default '',\n"
//         . "  PRIMARY KEY (id),\n"
//         . "  UNIQUE INDEX sitename (sitename)\n"
//         . ")";
//     redcommand($sql, $db);
// }

// $tab = 'Downloads';
// if (isset($tables[$tab])) {
//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields['global'])) {
//         $sql = "alter table $tab add\n"
//             . " global tinyint(1)\n"
//             . " not null default 0\n"
//             . " after id";
//         redcommand($sql, $db);
//         $sql = "update $tab set\n"
//             . " global = 1";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['owner'])) {
//         $sql = "alter table $tab add\n"
//             . " owner varchar(50)\n"
//             . " not null default ''\n"
//             . " after global";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['name'])) {
//         $sql = "alter table $tab add\n"
//             . " name varchar(255)\n"
//             . " not null default ''\n"
//             . " after version";
//         redcommand($sql, $db);
//         $sql = "update $tab set\n"
//             . " name = concat(version,'/',sitename)";
//         redcommand($sql, $db);
//         $sql = "update UpdateSites set\n"
//             . " version = concat(version,'/',sitename)\n"
//             . " where version != ''";
//         redcommand($sql, $db);
//         $sql = "alter table $tab add\n"
//             . " unique index name (name)";
//         redcommand($sql, $db);
//     }
// } else {
//     $sql = "create table Downloads (\n"
//         . "  id int(11) not null auto_increment,\n"
//         . "  global tinyint(1) not null default 0\n"       // 3/30/04
//         . "  owner varchar(50) not null default ''\n"      // 3/30/04
//         . "  revision int(11) not null default 0,\n"     // 3/03/03
//         . "  version varchar(255) not null default '',\n"
//         . "  name varchar(255) not null default '',\n"     // 3/30/04
//         . "  sitename varchar(255) not null default '',\n" // 11/18/02
//         . "  url varchar(255) not null default '',\n"
//         . "  username varchar(255) default '',\n"          // 11/18/02
//         . "  password varchar(255) default '',\n"          // 11/18/02
//         . "  filename varchar(255),\n"
//         . "  target varchar(255),\n"
//         . "  cmdline varchar(255) default '-s -a /s',\n"
//         . "  PRIMARY KEY (id),\n"
//         . "  unique index name (name)\n"
//         . ")";
//     redcommand($sql, $db);
// }

// $tab = 'UpdateMachines';
// if (isset($tables[$tab])) {
//     // force is a reserved word in mysql 4.

//     $fields = find_fields($dbname, $tab, $db);
//     if (isset($fields['force'])) {
//         $sql = "alter table $tab\n"         // 9/17/04
//             . " change force\n"
//             . " doforce tinyint(1)";
//         redcommand($sql, $db);
//     }
//     $idx = find_indices($dbname, $tab, $db);
//     if (!isset($idx['uniq'])) {
//         // 18-Feb-2005 EWB
//         //
//         // This really should have been here from the
//         // beginning.
//         fix_update_machines($db);
//         $sql = "alter table $tab\n"
//             . " add unique\n"
//             . " uniq (sitename,machine)";
//         redcommand($sql, $db);
//     }
// } else {
//     $def = 'not null default';
//     $sql = "create table UpdateMachines (\n"
//         . "  id int(11) not null auto_increment,\n"
//         . "  sitename varchar(255) $def '',\n"
//         . "  machine varchar(64) $def '',\n"
//         . "  uuid varchar(50) $def '',\n"
//         . "  timecontact int(11) $def 0,\n"
//         . "  timeupdate int(11) $def 0,\n"
//         . "  lastversion varchar(255) $def '',\n"
//         . "  oldversion varchar(255)  $def '',\n"
//         . "  newversion varchar(255)  $def '',\n"
//         . "  wasforced tinyint(1),\n"
//         . "  doforce tinyint(1),\n"
//         . "  primary key (id),\n"
//         . "  unique index uniq (sitename, machine)\n"
//         . ")";
//     redcommand($sql, $db);
// }


// reset($tables);
// foreach ($tables as $key => $data) {
//     sum_table($key, $db);
// }

// newline();

// //
// // create provision database
// //

// $dbname = 'provision';
// $dbname = (getenv('DB_PREFIX') ?: '') . $dbname;

// use_database($db, $dlist, $dbname);

// $tables = find_tables($dbname, $db);
// show_database($dbname, $tables, $db);

// $def = 'not null default';
// $now = time();
// $tab = 'Products';
// if (isset($tables['Products'])) {
//     $add = false;
//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields['created'])) {
//         $add  = true;
//         $sql  = "alter table $tab add\n";
//         $sql .= " created int(11)";
//         $sql .= " not null";
//         $sql .= " default 0";
//         redcommand($sql, $db);
//     }

//     if (!isset($fields['modified'])) {
//         $add  = true;
//         $sql  = "alter table $tab add\n";
//         $sql .= " modified int(11)";
//         $sql .= " not null";
//         $sql .= " default 0";
//         redcommand($sql, $db);
//     }
//     if ($add) {
//         $sql  = "update $tab set\n";
//         $sql .= " created = $now,\n";
//         $sql .= " modified = $now\n";
//         $sql .= " where created = 0";
//         redcommand($sql, $db);
//     }
//     if ($delete) {
//         drop_field($dbname, $tab, 'meterfilename', $db);
//     }
// } else {
//     $sql  = "create table Products (\n";
//     $sql .= "  productid int(11) not null auto_increment,\n";
//     $sql .= "  global tinyint(1) $def 0,\n";
//     $sql .= "  username varchar(50) $def '',\n";
//     $sql .= "  prodname varchar(255) $def '',\n";
//     $sql .= "  defaultenable tinyint(1) $def 1,\n";
//     $sql .= "  defaultmonitor tinyint(1) $def 1,\n";
//     $sql .= "  created int(11) $def 0,\n";
//     $sql .= "  modified int(11) $def 0,\n";
//     $sql .= "  primary key (productid),\n";
//     $sql .= "  unique index uniq (username,prodname)\n";
//     $sql .= ")";
//     redcommand($sql, $db);
// }

// if (!isset($tables['SiteAssignments'])) {
//     $sql  = "create table SiteAssignments (\n";
//     $sql .= "  id int(11) not null auto_increment,\n";
//     $sql .= "  productid int(11) $def 0,\n";
//     $sql .= "  sitename varchar(50) $def '',\n";
//     $sql .= "  provisioned tinyint(1) $def 0,\n";
//     $sql .= "  enabled tinyint(1) $def 0,\n";
//     $sql .= "  metered tinyint(1) $def 0,\n";
//     $sql .= "  primary key (id)\n";
//     $sql .= ")";
//     redcommand($sql, $db);
// }

// if (!isset($tables['MachineAssignments'])) {
//     $sql  = "create table MachineAssignments (\n";
//     $sql .= "  id int(11) not null auto_increment,\n";
//     $sql .= "  productid int(11) $def 0,\n";
//     $sql .= "  sitename varchar(50) $def '',\n";
//     $sql .= "  machine varchar(64) $def '',\n";
//     $sql .= "  uuid varchar(50) $def '',\n";
//     $sql .= "  provisioned tinyint(1) $def 0,\n";
//     $sql .= "  enabled tinyint(1) $def 0,\n";
//     $sql .= "  metered tinyint(1) $def 0,\n";
//     $sql .= "  primary key (id)\n";
//     $sql .= ")";
//     redcommand($sql, $db);
// }

// if (!isset($tables['KeyFiles'])) {
//     $sql  = "create table KeyFiles (\n";
//     $sql .= "  keyid int(11) not null auto_increment,\n";
//     $sql .= "  productid int(11) $def 0,\n";
//     $sql .= "  filename varchar(255) $def '',\n";
//     $sql .= "  primary key (keyid)\n";
//     $sql .= ")";
//     redcommand($sql, $db);
// }

// if (!isset($tables['MeterFiles'])) {
//     $sql  = "create table MeterFiles (\n";
//     $sql .= "  meterid int(11) not null auto_increment,\n";
//     $sql .= "  productid int(11) $def 0,\n";
//     $sql .= "  filename varchar(255) $def '',\n";
//     $sql .= "  primary key (meterid)\n";
//     $sql .= ")";
//     redcommand($sql, $db);  // 2/2/2004
// }

// $tab = 'CryptKeys';
// if (isset($tables[$tab])) {
//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields['created'])) {
//         $sql  = "alter table $tab add\n";
//         $sql .= " created int(11)";
//         $sql .= " not null";
//         $sql .= " default 0";
//         $sql .= " after productid";
//         redcommand($sql, $db);

//         $now  = time();
//         $sql  = "update CryptKeys\n";
//         $sql .= " set created = $now\n";
//         $sql .= " where created = 0";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['lastuse'])) {
//         $sql  = "alter table $tab add\n";
//         $sql .= " lastuse int(11)";
//         $sql .= " not null";
//         $sql .= " default 0";
//         $sql .= " after created";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['access'])) {
//         $sql  = "alter table $tab add\n";
//         $sql .= " access int(11)";
//         $sql .= " not null";
//         $sql .= " default 0";
//         $sql .= " after lastuse";
//         redcommand($sql, $db);
//     }
// } else {
//     $def = 'not null default';
//     $sql = "create table CryptKeys (\n"
//         . "  cryptid int(11) not null auto_increment,\n"
//         . "  uuid varchar(50)  $def '',\n"
//         . "  productid int(11) $def 0,\n"
//         . "  created int(11)   $def 0,\n"
//         . "  lastuse int(11)   $def 0,\n"
//         . "  access int(11)    $def 0,\n"
//         . "  encryptkey varchar(128) $def '',\n"
//         . "  decryptkey varchar(128) $def '',\n"
//         . "  method varchar(50) $def '',\n"
//         . "  primary key (cryptid),\n"
//         . "  unique index uniq (uuid,productid)\n"
//         . ")";
//     redcommand($sql, $db);
// }

// $tab = 'Audit';
// if (isset($tables[$tab])) {
//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields['owner'])) {
//         $sql  = "alter table $tab add\n";
//         $sql .= " owner varchar(50)";
//         $sql .= " not null";
//         $sql .= " default ''";
//         $sql .= " after product";
//         redcommand($sql, $db);
//     }
// } else {
//     $def  = 'not null default';
//     $sql  = "create table Audit (\n";
//     $sql .= "  auditid int(11) not null auto_increment,\n";
//     $sql .= "  who tinyint(1)  $def 1,\n";
//     $sql .= "  servertime int(11)  $def 0,\n";
//     $sql .= "  clienttime int(11)  $def 0,\n";
//     $sql .= "  sitename varchar(50) $def '',\n";
//     $sql .= "  machine varchar(64) $def '',\n";
//     $sql .= "  uuid varchar(64) $def '',\n";
//     $sql .= "  product varchar(255) $def '',\n";
//     $sql .= "  owner varchar(50) $def '',\n";       // 2/12/04
//     $sql .= "  username varchar(50) $def '',\n";
//     $sql .= "  action varchar(10) $def '',\n";
//     $sql .= "  primary key (auditid)\n";
//     $sql .= ")";
//     redcommand($sql, $db);
// }

// $tab = 'Meter';
// if (isset($tables['Meter'])) {
//     $add = false;
//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields['servermax'])) {
//         $add  = true;
//         $sql  = "alter table $tab add\n";
//         $sql .= " servermax int(11)";
//         $sql .= " not null";
//         $sql .= " default 0";
//         $sql .= " after servertime";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['clientmax'])) {
//         $add  = true;
//         $sql  = "alter table $tab add\n";
//         $sql .= " clientmax int(11)";
//         $sql .= " not null";
//         $sql .= " default 0";
//         $sql .= " after servertime";
//         redcommand($sql, $db);
//     }
//     if ($add) {
//         $sql  = "update Meter set\n";
//         $sql .= " clientmax = clienttime,\n";
//         $sql .= " servermax = servertime\n";
//         $sql .= " where eventtype = 1";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['owner'])) {
//         $sql  = "alter table $tab add\n";
//         $sql .= " owner varchar(50)";
//         $sql .= " not null";
//         $sql .= " default ''";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['product'])) {
//         $sql  = "alter table $tab add\n";
//         $sql .= " product varchar(255)";
//         $sql .= " not null";
//         $sql .= " default ''";
//         redcommand($sql, $db);
//     }

//     $idx = find_indices($dbname, $tab, $db);
//     if (!isset($idx['ctime'])) {
//         $sql = "alter table $tab\n"
//             . " add index \n"
//             . " ctime (clienttime)";
//         redcommand($sql, $db);
//     }
//     if (!isset($idx['procid'])) {
//         $sql = "alter table $tab\n"
//             . " add index \n"
//             . " procid (processid)";
//         redcommand($sql, $db);
//     }
// } else {
//     $sql  = "create table Meter (\n";
//     $sql .= "  meterid int(11) not null auto_increment,\n";
//     $sql .= "  clienttime int(11) $def 0,\n";
//     $sql .= "  servertime int(11) $def 0,\n";
//     $sql .= "  clientmax  int(11) $def 0,\n";
//     $sql .= "  servermax  int(11) $def 0,\n";
//     $sql .= "  eventtype tinyint(1) $def 0,\n";
//     $sql .= "  exename varchar(255) $def '',\n";
//     $sql .= "  processid varchar(16) $def '',\n";
//     $sql .= "  sitename varchar(50) $def '',\n";
//     $sql .= "  machine varchar(64) $def '',\n";
//     $sql .= "  uuid varchar(50) $def '',\n";
//     $sql .= "  username varchar(50) $def '',\n";
//     $sql .= "  owner varchar(50) $def '',\n";       // 2/6/2004
//     $sql .= "  product varchar(255) $def '',\n";    // 2/6/2004
//     $sql .= "  primary key (meterid),\n";
//     $sql .= "  index ctime (clienttime),\n";
//     $sql .= "  index procid (processid)\n";
//     $sql .= ")";
//     redcommand($sql, $db);
// }


// reset($tables);
// foreach ($tables as $key => $data) {
//     sum_table($key, $db);
// }

// newline();

// /* create softinst database */

// $dbname = 'softinst';
// $dbname = (getenv('DB_PREFIX') ?: '') . $dbname;

// use_database($db, $dlist, $dbname);

// $tables = find_tables($dbname, $db);
// show_database($dbname, $tables, $db);

// $now = time();
// $tab = 'PatchCategories';
// if (!isset($tables['PatchCategories'])) {
//     $sql = "create table PatchCategories (\n"
//         . " pcategoryid int(11) not null auto_increment,\n"
//         . " category varchar(255) not null default '',\n"
//         . " precedence int(11) not null default 0,\n"
//         . " primary key (pcategoryid),\n"
//         . " unique index uniq (category)\n"
//         . ")";
//     redcommand($sql, $db);
// }

// $tab = 'PatchGroups';
// if (isset($tables[$tab])) {
//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields['created'])) {
//         $now = time();
//         $sql = "alter table $tab\n"
//             . " add created int(11)\n"
//             . " default 0 not null\n"
//             . " after style";
//         redcommand($sql, $db);
//         $sql = "update $tab set\n"
//             . " created = $now";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['human']))   // 9/30/04
//     {
//         $sql = "alter table $tab\n"
//             . " add human tinyint(1)\n"
//             . " default 0 not null\n"
//             . " after global";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['whereclause'])) {
//         $sql = "alter table $tab\n"
//             . " add whereclause text\n"
//             . " default '' not null\n"
//             . " after boolstring";
//         redcommand($sql, $db);
//     }
// } else {
//     $sql = "create table PatchGroups (\n"
//         . " pgroupid    int(11)      not null auto_increment,\n"
//         . " pcategoryid int(11)      not null default 0,\n"
//         . " name        varchar(255) not null default '',\n"
//         . " username    varchar(50)  not null default '',\n"
//         . " global      tinyint(1)   not null default 0,\n"
//         . " human       tinyint(1)   not null default 0,\n"    // 9/30/04
//         . " style       int(11)      not null default 0,\n"
//         . " created     int(11)      not null default 0,\n"
//         . " search      varchar(255) not null default '',\n"
//         . " boolstring  text         not null default '',\n"
//         . " whereclause text         not null default '',\n"
//         . " primary key (pgroupid),\n"
//         . " unique index uniq (pcategoryid,name)\n"
//         . ")";
//     redcommand($sql, $db);
// }

// $tab = 'PatchExpression';
// if (!isset($tables[$tab])) {
//     $sql = "create table $tab (\n"
//         . "  exprid      int(11)     not null auto_increment,\n"
//         . "  pgroupid    int(11)     not null default 0,\n"
//         . "  pcatid      int(11)     not null default 0,\n"
//         . "  orterm      int(11)     not null default 0,\n"
//         . "  negation    tinyint(1)  not null default 0,\n"
//         . "  item        int(11)     not null default 0,\n"
//         . "  primary key (exprid)\n"
//         . ")";
//     redcommand($sql, $db);
// }

// if (!isset($tables['PatchGroupMap'])) {
//     $sql = "create table PatchGroupMap (\n"
//         . " pgroupmapid int(11) not null auto_increment,\n"
//         . " pgroupid int(11) not null default 0,\n"
//         . " patchid int(11) not null default 0,\n"
//         . " primary key (pgroupmapid),\n"
//         . " unique index uniq (pgroupid,patchid)\n"
//         . ")";
//     redcommand($sql, $db);
// }

// $tab = 'WUConfig';
// if (isset($tables[$tab])) {
//     $fields = find_fields($dbname, $tab, $db);
//     if ($delete) {
//         drop_field($dbname, $tab, 'installdelay', $db);  // 7/14/2004
//     }

//     if (!isset($fields['updatecache'])) {
//         $sql = "alter table $tab add\n"
//             . " updatecache tinyint(1)\n"
//             . " default 0 not null\n"
//             . " after lastupdate";
//         redcommand($sql, $db);
//         $sql = "update $tab set updatecache = 2";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['cacheseconds'])) {
//         $sql = "alter table $tab add\n"
//             . " cacheseconds int(11)\n"
//             . " default 0 not null\n"
//             . " after updatecache";
//         redcommand($sql, $db);
//         $def = 86400 * 14; // two weeks
//         $sql = "update $tab set cacheseconds = $def";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['restart']))     // 12/28/2004
//     {
//         $sql = "alter table $tab add\n"
//             . " restart tinyint(1)\n"
//             . " default 0 not null\n"
//             . "after cacheseconds";
//         redcommand($sql, $db);
//         $sql = "update $tab set restart = 2";
//         redcommand($sql, $db);
//     }

//     if (!isset($fields['chain']))         // 12/29/2004
//     {
//         $sql = "alter table $tab\n"
//             . " add chain tinyint(1)\n"
//             . " default 0 not null\n"
//             . " after restart";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['chainseconds']))    // 12/29/2004
//     {
//         $sql = "alter table $tab\n"
//             . " add chainseconds int(11)\n"
//             . " default 0 not null\n"
//             . " after chain";
//         redcommand($sql, $db);
//     }
// } else {
//     $def = 'not null default';
//     $sql = "create table WUConfig (\n"
//         . " id int(11) not null auto_increment,\n"
//         . " mgroupid     int(11) $def 0,\n"
//         . " management   int(11) $def 0,\n"
//         . " installday   int(11) $def 0,\n"
//         . " installhour  int(11) $def 3,\n"
//         . " newpatches   int(11) $def 0,\n"
//         . " patchsource  int(11) $def 0,\n"
//         . " serverurl    varchar(255) $def '',\n"
//         . " propagate    tinyint(1) $def 0,\n"
//         . " lastupdate   int(11) $def 0,\n"
//         . " updatecache  tinyint(1) $def 0,\n"
//         . " cacheseconds int(11) $def 0,\n"
//         . " restart      tinyint(1) $def 0,\n"   // 12/28/2004
//         . " chain        tinyint(1) $def 0,\n"   // 12/29/2004
//         . " chainseconds int(11) $def 0,\n"      // 12/29/2004
//         . " primary key (id),\n"
//         . " unique index uniq (mgroupid)\n"
//         . ")";
//     redcommand($sql, $db);
// }

// $tab = 'WUConfigCache';
// if (isset($tables[$tab])) {
//     if ($delete) {
//         $sql = "drop table $tab";
//         redcommand($sql, $db);
//         unset($tables[$tab]);
//     }
// }

// $i11 = "int(11) $def 0";
// $tab = 'Machine';
// if (isset($tables['Machine'])) {
//     if ($delete) {
//         drop_field($dbname, $tab, 'lastconfigid', $db);  // 8/18/2004
//         drop_field($dbname, $tab, 'nextwuclient', $db);  // 8/18/2004
//     }

//     $fields = find_fields($dbname, $tab, $db);
//     add_field($fields, $tab, 'lastdefconfigid', 'nextwuclient', $db);
//     add_field($fields, $tab, 'lastdefchange', 'lastdefconfigid', $db);
// } else {
//     $sql = "create table Machine (\n"
//         . " machineid       int(11) not null auto_increment,\n"
//         . " id              $i11,\n"
//         . " wuconfigid      $i11,\n"
//         . " lastchange      $i11,\n"
//         . " lastcontact     $i11,\n"
//         . " lastdefconfigid $i11,\n"
//         . " lastdefchange   $i11,\n"
//         . " primary key (machineid),\n"
//         . " unique index uniq (id)\n"
//         . ")";
//     redcommand($sql, $db);
// }

// $tab = 'PatchConfig';
// if (isset($tables[$tab])) {
//     $fields = find_fields($dbname, $tab, $db);
//     add_field($fields, $tab, 'scheddelay', 'notifyadvancetime', $db);
//     add_field($fields, $tab, 'schedminute', 'scheddelay', $db);
//     add_field($fields, $tab, 'schedhour',  'schedminute', $db);
//     add_field($fields, $tab, 'schedday',   'schedhour', $db);
//     add_field($fields, $tab, 'schedmonth', 'schedday', $db);
//     add_field($fields, $tab, 'schedweek',  'schedmonth', $db);
//     add_field($fields, $tab, 'schedrandom', 'schedweek', $db);
//     add_field($fields, $tab, 'schedtype',  'schedrandom', $db);

//     add_field($fields, $tab, 'notifydelay', 'schedtype', $db);
//     add_field($fields, $tab, 'notifyminute', 'notifydelay', $db);
//     add_field($fields, $tab, 'notifyhour',  'notifyminute', $db);
//     add_field($fields, $tab, 'notifyday',   'notifyhour', $db);
//     add_field($fields, $tab, 'notifymonth', 'notifyday', $db);
//     add_field($fields, $tab, 'notifyweek',  'notifymonth', $db);
//     add_field($fields, $tab, 'notifyrandom', 'notifyweek', $db);
//     add_field($fields, $tab, 'notifytype',  'notifyrandom', $db);
//     add_field($fields, $tab, 'notifyfail',  'notifytype', $db);
//     add_field($fields, $tab, 'configtype',  'notifyfail', $db);

//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields['wpgroupid'])) {
//         $sql = "alter table $tab add\n"
//             . " wpgroupid int(11)\n"
//             . " not null default 0\n"
//             . " after lastupdate";
//         redcommand($sql, $db);

//         $sql = "update PatchConfig set wpgroupid=pgroupid";
//         redcommand($sql, $db);
//     }

//     if ($delete) {
//         drop_field($dbname, $tab, 'scheduletime', $db);
//         drop_field($dbname, $tab, 'notifyscheduletime', $db);
//         drop_field($dbname, $tab, 'installdelay', $db);
//         drop_field($dbname, $tab, 'scheddeley', $db);
//         drop_field($dbname, $tab, 'schedfail', $db);
//         drop_field($dbname, $tab, 'chain', $db);   // 12/29/2004
//         drop_field($dbname, $tab, 'chainseconds', $db);  // 12/29/2004
//     }
// } else {
//     $def = 'not null default';
//     $sql = "create table PatchConfig (\n"
//         . " pconfigid int(11) not null auto_increment,\n"
//         . " pgroupid int(11) $def 0,\n"
//         . " mgroupid int(11) $def 0,\n"
//         . " installation int(11) $def 0,\n"
//         . " notifyadvance tinyint(1) $def 0,\n"
//         . " notifyadvancetime int(11) $def 900,\n"
//         . " scheddelay     $i11,\n"
//         . " schedminute    $i11,\n"
//         . " schedhour      $i11,\n"
//         . " schedday       $i11,\n"
//         . " schedmonth     $i11,\n"
//         . " schedweek      $i11,\n"
//         . " schedrandom    $i11,\n"
//         . " schedtype      $i11,\n"
//         . " notifydelay    $i11,\n"
//         . " notifyminute   $i11,\n"
//         . " notifyhour     $i11,\n"
//         . " notifyday      $i11,\n"
//         . " notifymonth    $i11,\n"
//         . " notifyweek     $i11,\n"
//         . " notifyrandom   $i11,\n"
//         . " notifytype     $i11,\n"
//         . " notifyfail     $i11,\n"
//         . " configtype     $i11,\n"
//         . " reminduser tinyint(1) $def  0,\n"
//         . " preventshutdown tinyint(1) $def 0,\n"
//         . " notifyschedule tinyint(1) $def 0,\n"
//         . " notifytext text $def '',\n"
//         . " lastupdate int(11) $def 0,\n"
//         . " wpgroupid int(11) $def 0,\n"
//         . " primary key (pconfigid),\n"
//         . " unique index uniq (mgroupid,pgroupid)\n"
//         . ")";
//     redcommand($sql, $db);
// }

// $tab = 'Patches';
// if (isset($tables['Patches'])) {
//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields['canuninstall'])) {
//         $sql = "alter table $tab add\n"
//             . " canuninstall tinyint(1)\n"
//             . " not null default 0\n"
//             . " after lastreference";       // 7/14/2004
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['msname'])) {
//         $sql = "alter table $tab add\n"
//             . " msname varchar(255)\n"
//             . " not null default ''\n"
//             . " after name";       // 7/14/2004
//         redcommand($sql, $db);
//         $sql = "update $tab set msname = name";
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['title'])) {
//         $sql = "alter table $tab add\n"
//             . " title varchar(255)\n"
//             . " not null default ''\n"
//             . " after msname";       // 7/14/2004
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['locale'])) {
//         $sql = "alter table $tab add\n"
//             . " locale varchar(20)\n"
//             . " not null default ''\n"
//             . " after title";       // 7/14/2004
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['priority'])) {
//         $sql = "alter table $tab add\n"
//             . " priority int(11)\n"
//             . " not null default -1\n"
//             . " after canuninstall";       // 7/14/2004
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['type'])) {
//         $sql = "alter table $tab add\n"
//             . " type int(11)\n"
//             . " not null default 0\n"
//             . " after priority";       // 7/14/2004
//         redcommand($sql, $db);
//     }
//     if (!isset($fields['mandatory'])) {
//         $sql = "alter table $tab add\n"
//             . " mandatory int(11)\n"
//             . " not null default 0\n"
//             . " after type";
//         redcommand($sql, $db);
//     }
// } else {
//     $def = 'not null default';
//     $sql = "create table Patches (\n"
//         . " patchid int(11) not null auto_increment,\n"
//         . " itemid varchar(255) $def '',\n"
//         . " name varchar(255) $def '',\n"
//         . " msname varchar(255) $def '',\n"    // 7/14/04
//         . " title varchar(255) $def '',\n"     // 7/14/04
//         . " locale varchar(20) $def '',\n"     // 7/14/04
//         . " date int(11) $def 0,\n"
//         . " size int(11) $def 0,\n"
//         . " patchdesc text $def '',\n"
//         . " params varchar(30) $def '',\n"
//         . " clientfile varchar(255) $def '',\n"
//         . " serverfile varchar(255) $def '',\n"
//         . " crc varchar(50) $def '',\n"
//         . " component varchar(64) $def '',\n"
//         . " platform varchar(50) $def '',\n"
//         . " processor varchar(10) $def '',\n"
//         . " osmajor int(11) $def 0,\n"
//         . " osminor int(11) $def 0,\n"
//         . " osbuild int(11) $def 0,\n"
//         . " spmajor int(11) $def 0,\n"
//         . " spminor int(11) $def 0,\n"
//         . " prio int(11) $def 0,\n"
//         . " priohidden int(11) $def 0,\n"
//         . " eula varchar(255) $def '',\n"
//         . " lastupdate int(11) $def 0,\n"
//         . " lastreference int(11) $def 0,\n"
//         . " canuninstall tinyint(1) $def 0,\n" // 7/14/04
//         . " priority int(11) $def -1,\n"
//         . " type int(11) $def 0,\n"
//         . " mandatory int(11) $def 0,\n"
//         . " primary key (patchid),\n"
//         . " unique index uniq (itemid)\n"
//         . ")";
//     redcommand($sql, $db);
// }

// $tab = 'PatchStatus';
// if (isset($tables[$tab])) {
//     $fields = find_fields($dbname, $tab, $db);
//     if (!isset($fields['detected'])) {
//         $sql = "alter table $tab\n"
//             . " add detected int(11)\n"
//             . " not null default 0\n"
//             . " after lastchange";       // 7/14/2004
//         redcommand($sql, $db);
//     }
//     if ($delete) {
//         drop_field($dbname, $tab, 'laststatus', $db);
//     }

//     /* Now, remove the useless "Software update failed to install."
//             message.  For now, continue to do this until the newer clients
//             become more commonplace. */
//     $sql = "UPDATE PatchStatus SET lasterror='' WHERE lasterror='"
//         . "Software update failed to install.'";
//     $patchRes = redcommand($sql, $db);
//     $numPatch = affected($res, $db);
//     if ($numPatch > 0) {
//         logs::log(__FILE__, __LINE__, "update.php: removed $numPatch PatchStatus error "
//             . 'messages', 0);
//     }

//     /* Any updates that are marked superseded but also have an installation
//             date are actually installed. */
//     $sql = 'UPDATE PatchStatus SET status=' . constPatchStatusInstalled
//         . ' WHERE status=' . constPatchStatusSuperseded
//         . ' AND lastinstall!=0';
//     $patchRes = redcommand($sql, $db);
//     $numPatch = affected($res, $db);
//     if ($numPatch > 0) {
//         logs::log(__FILE__, __LINE__, "update.php: marked $numPatch PatchStatus installed", 0);
//     }
// } else {
//     $def = 'not null default';
//     $sql = "create table PatchStatus (\n"
//         . " patchstatusid  int(11) not null auto_increment,\n"
//         . " id             int(11) $def 0,\n"
//         . " patchid        int(11) $def 0,\n"
//         . " patchconfigid  int(11) $def 0,\n"
//         . " lastconfigid   int(11) $def 0,\n"
//         . " lastchange     int(11) $def 0,\n"
//         . " detected       int(11) $def 0,\n"  // 7/14/04
//         . " status         int(11) $def 0,\n"
//         . " lastinstall    int(11) $def 0,\n"
//         . " lastuninstall  int(11) $def 0,\n"
//         . " nextaction     int(11) $def 0,\n"
//         . " lastdownload   int(11) $def 0,\n"
//         . " downloadsource varchar(255) $def '',\n"
//         . " lasterror      text $def '',\n"
//         . " lasterrordate  int(11) $def 0,\n"
//         . " primary key(patchstatusid),\n"
//         . " unique index uniq (id,patchid)\n"
//         . ")";
//     redcommand($sql, $db);
// }

// $tab = 'BlockedPatches';
// if (!isset($tables[$tab])) {
//     $sql = "create table $tab (\n"
//         . "  pblockid    int(11)     not null auto_increment,\n"
//         . "  patchstatusid int(11)   not null default 0,\n"
//         . "  patchid     int(11)     not null default 0,\n"
//         . "  primary key (pblockid),\n"
//         . "  unique index uniq (patchstatusid,patchid)\n"
//         . ")";
//     redcommand($sql, $db);
// }

// $tab = 'ErrorCodes';
// if (!isset($tables[$tab])) {
//     $sql = "create table $tab (\n"
//         . " errorid int(11) not null auto_increment,\n"
//         . " hexcode char(20) not null default '',\n"
//         . " deccode bigint not null default 0,\n"
//         . " strcode varchar(255) not null default '',\n"
//         . " textcode text not null default '',\n"
//         . " primary key (errorid),\n"
//         . " unique index hexuniq (hexcode),\n"
//         . " unique index decuniq (deccode)\n"
//         . ")";
//     redcommand($sql, $db);
// }

// $sql = "LOAD DATA INFILE '/home/webcron/errcodes.csv' REPLACE INTO TABLE "
//     . "ErrorCodes FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' "
//     . "LINES TERMINATED BY '\\n' (hexcode,deccode,strcode,"
//     . "textcode)";
// redcommand($sql, $db);

// if ($real) {
//     patch_init($db);
// }

// reset($tables);
// foreach ($tables as $key => $data) {
//     sum_table($key, $db);
// }

// newline();

// /* end create softinst database */

// /* Get the current owner of the server */
// $owner = '';
// if ($command) {
//     $db = db_select($GLOBALS['PREFIX'] . 'event');
//     $owner = find_global_owner($db);
// }

// /* Create the Report CSS link server option */
// if ($command) {
//     $db = db_select($GLOBALS['PREFIX'] . 'core');
//     $row = find_opt(constServerOptionReptCSS, $db);
//     if (!$row) {
//         $cssfile = 'https://' . server_var('SERVER_NAME') . ':'
//             . server_var('SERVER_PORT') . '/' . server_root()
//             . '/report/default.css';
//         server_set(constServerOptionReptCSS, $cssfile, $db);
//     }
//     $row = find_opt(constServerOptionServerURL, $db);
//     if (!$row) {
//         $url = 'https://' . server_var('SERVER_NAME') . ':'
//             . server_var('SERVER_PORT') . '/' . server_root();
//         server_set(constServerOptionServerURL, $url, $db);
//     }
// }

// /* Build the schedule defaults - before creating a new .sql file read this:
//         Mysqldump cannot control which columns within a table are dumped.  So,
//         you must drop the auto-increment column just before dumping the
//         Schedules tables (the schedules table is the only table that needs to
//         be dumped).  Make sure you also remove all statements from the dump
//         except the INSERT, and change the INSERT to INSERT IGNORE.

//         DO NOT USE REPLACE FOR ScheduleMap, use INSERT IGNORE!!!

//         To drop the column:
//             alter table Schedules drop schedid;
//             alter table ScheduleMap drop schedmapid, drop lastrun,
//                 drop failcount, drop nextrun, drop lastcomputed, drop lastrand;

//         mysqldump -c -u weblog schedule > schedule.sql
//     */
// if ($command) {
//     $db = db_select($GLOBALS['PREFIX'] . 'schedule');
//     $sql = 'SELECT schedid FROM Schedules';
//     $set = find_many($sql, $db);

//     /* Cache each distinct schedule and objectuniq already in ScheduleMap,
//             we will delete rows added incorrectly later */
//     $sql = 'SELECT ' . constObjectTypeReport . ' AS objecttype,reportuniq '
//         . 'FROM ' . $GLOBALS['PREFIX'] . 'report.Report';
//     $list = find_many_nondebug($sql, $db);
//     foreach ($list as $key => $row) {
//         $sql = 'SELECT schedmapid FROM ScheduleMap WHERE objecttype='
//             . $row['objecttype'] . ' AND objectuniq=\''
//             . $row['reportuniq'] . '\'';
//         $res = command($sql, $db);
//         $list[$key]['schedmaps'] = array();
//         while ($row2 = mysqli_fetch_row($res)) {
//             $list[$key]['schedmaps'][] = $row2[0];
//         }
//         ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
//     }

//     exec('mysql -u weblog schedule < /home/webcron/schedule.sql');

//     $sql = "UPDATE Schedules SET username='$owner'";

//     if ($set) {
//         $sql .= ' WHERE schedid NOT IN(';
//         $first = true;
//         foreach ($set as $key => $row) {
//             if (!$first) {
//                 $sql .= ',';
//             }
//             $first = false;
//             $sql .= $row['schedid'];
//         }
//         $sql .= ')';
//     }
//     redcommand($sql, $db);

//     /* Now, delete those ScheduleMaps that were deleted before by the
//             customer. */
//     reset($list);
//     foreach ($list as $key => $row) {
//         $sql = 'DELETE FROM ScheduleMap WHERE objecttype='
//             . $row['objecttype'] . ' AND objectuniq=\''
//             . $row['reportuniq'] . '\'';
//         if ($row['schedmaps']) {
//             $sql .= ' AND schedmapid NOT IN ('
//                 . implode(',', $row['schedmaps']) . ')';
//         }
//         $res = command($sql, $db);
//         $num = affected($res, $db);
//         if ($num > 0) {
//             logs::log(__FILE__, __LINE__, "update.php: deleted $num SM rows for the "
//                 . 'object ' . $row['reportuniq'], 0);
//         }
//     }
// }

// /* Build the report section defaults - follow the same instructions for
//         schedule.sql.

//         BEFORE STARTING, YOU MUST MANUALLY EDIT AND CLICK UPDATE FOR EACH
//         REPORT.  THIS IS BECAUSE THE DYNAMIC UPDATE DOES NOT CURRENTLY HANDLE
//         DEFAULT REPORTS.

//         Columns to drop:
//             alter table Section drop sectionid;
//             alter table Report drop reportid;
//             alter table ReportMap drop reportmapid;
//             alter table SectionComponentDyn drop sectioncompid;
//             alter table SectionCompMapDyn drop sectioncompmapid;
//             alter table SectionCompMapSet drop sectioncompmapsetid;
//             alter table MultipleValueMap drop mvmid;
//             alter table SectionCompColDyn drop sectioncompcolid;
//             alter table NullHandleDyn drop nullhandleid;
//             alter table SectionCompColMapDyn drop sectioncompcolmapid;
//             alter table SectionCompDynValues drop valueid;
//             alter table SectionCompColMapSet drop sectioncompcolmapsetid;
//             alter table SubstitutionStrings drop substrid;

//         Tables that should be included (in mysql dump order):
//             MultipleValueMap
//             NullHandleDyn
//             Report
//             ReportMap
//             Section
//             SectionCompColDyn
//             SectionCompColMapDyn
//             SectionCompColMapSet
//             SectionCompDynValues
//             SectionCompMapDyn
//             SectionCompMapSet
//             SectionComponentDyn
//             SubstitutionStrings

//         mysqldump -c -u weblog report > report.sql
//     */
// if ($command) {
//     $db = db_select($GLOBALS['PREFIX'] . 'report');
//     $sql = 'SELECT reportid FROM Report';
//     $set = find_many($sql, $db);
//     $sql = 'SELECT sectionid FROM Section';
//     $set2 = find_many($sql, $db);

//     /* Prevent illegal additions to the MultipleValueMap table - only allow
//             inserts for existing reports/sections for new reportconfiguniqs */
//     $sql = '(SELECT reportconfiguniq, reportuniq, sectiontype FROM '
//         . 'Report, ReportConfig WHERE sectiontype='
//         . constSectionTypeReport . ') UNION (SELECT reportconfiguniq, '
//         . 'sectionuniq AS \'reportuniq\', Section.sectiontype AS \''
//         . 'sectiontype\' FROM Section, '
//         . 'ReportConfig WHERE '
//         . 'Section.sectiontype=ReportConfig.sectiontype)';
//     $list = find_many_nondebug($sql, $db);
//     foreach ($list as $key => $row) {
//         $sql = 'SELECT mvmid FROM MultipleValueMap WHERE '
//             . 'reportconfiguniq=\'' . $row['reportconfiguniq']
//             . '\' AND reportuniq=\'' . $row['reportuniq'] . '\'';
//         $res = command($sql, $db);
//         $list[$key]['mvmids'] = array();
//         while ($row2 = mysqli_fetch_row($res)) {
//             $list[$key]['mvmids'][] = $row2[0];
//         }
//         ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
//     }

//     /* Prevent illegal additions to the ReportMap table - only allow
//             inserts for new reports */
//     $sql = 'SELECT reportuniq FROM Report';
//     $reptList = find_many_nondebug($sql, $db);
//     foreach ($reptList as $key => $row) {
//         $sql = 'SELECT reportmapid FROM ReportMap WHERE '
//             . 'reportuniq=\'' . $row['reportuniq'] . '\'';
//         $res = command($sql, $db);
//         $reptList[$key]['reportmapids'] = array();
//         while ($row2 = mysqli_fetch_row($res)) {
//             $reptList[$key]['reportmapids'][] = $row2[0];
//         }
//         ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
//     }

//     exec('mysql -u weblog report < /home/webcron/report.sql');

//     $sql = "UPDATE Report SET username='$owner'";
//     if ($set) {
//         $sql .= ' WHERE reportid NOT IN(';
//         $first = true;
//         foreach ($set as $key => $row) {
//             if (!$first) {
//                 $sql .= ',';
//             }
//             $first = false;
//             $sql .= $row['reportid'];
//         }
//         $sql .= ')';
//     }
//     redcommand($sql, $db);

//     $sql = "UPDATE Section SET username='$owner'";
//     if ($set2) {
//         $sql .= ' WHERE sectionid NOT IN(';
//         $first = true;
//         foreach ($set2 as $key => $row) {
//             if (!$first) {
//                 $sql .= ',';
//             }
//             $first = false;
//             $sql .= $row['sectionid'];
//         }
//         $sql .= ')';
//     }
//     redcommand($sql, $db);

//     /* Now, delete those MultipleValueMaps that were deleted before by the
//             customer. */
//     reset($list);
//     foreach ($list as $key => $row) {
//         $sql = 'DELETE FROM MultipleValueMap WHERE reportconfiguniq=\''
//             . $row['reportconfiguniq'] . '\' AND reportuniq=\''
//             . $row['reportuniq'] . '\'';
//         if ($row['mvmids']) {
//             $sql .= ' AND mvmid NOT IN ('
//                 . implode(',', $row['mvmids']) . ')';
//         }
//         $res = command($sql, $db);
//         $num = affected($res, $db);
//         if ($num > 0) {
//             logs::log(__FILE__, __LINE__, "update.php: deleted $num MVM rows for the "
//                 . 'report ' . $row['reportuniq'] . ' control '
//                 . $row['reportconfiguniq'], 0);
//         }
//     }

//     /* Now, delete those ReportMaps that were deleted before by the
//             customer. */
//     reset($reptList);
//     foreach ($reptList as $key => $row) {
//         $sql = 'DELETE FROM ReportMap WHERE reportuniq=\''
//             . $row['reportuniq'] . '\'';
//         if ($row['reportmapids']) {
//             $sql .= ' AND reportmapid NOT IN ('
//                 . implode(',', $row['reportmapids']) . ')';
//         }
//         $res = command($sql, $db);
//         $num = affected($res, $db);
//         if ($num > 0) {
//             logs::log(__FILE__, __LINE__, "update.php: deleted $num RM rows for the "
//                 . 'report ' . $row['reportuniq'], 0);
//         }
//     }

//     /* Finally, run the update code for summary section types only.  This
//             must be done because columns generate a new timestamp each time,
//             so we have to remove the duplicates. */
//     $err = PHP_REPF_UpdateSectionType(CUR, constSectionTypeExecSummary);
//     if ($err != constAppNoErr) {
//         logs::log(
//             __FILE__,
//             __LINE__,
//             'update.php: failed to call PHP_REPF_UpdateSectionType',
//             0
//         );
//     }
// }

// if ($command) {
//     /* Update the defaults if necessary */
//     $dataversion = 0;
//     $err = PHP_DBAS_GetDataVersion(
//         CUR,
//         $dataversion,
//         $exists,
//         constDataTypeReports
//     );
//     if ($err == constAppNoErr) {
//         /* Add one-time default updates here.  These can be removed
//                 when nuglobal is implemented. */
//         if ($dataversion < constReportDataVersionUpdateOne) {
//             $err = PHP_REPD_UpdateDefaultsOne(CUR);
//             if ($err != constAppNoErr) {
//                 echo "Error $err updating defaults to update one.";
//             }
//         }
//         if ($dataversion < constReportDataVersionUpdateTwo) {
//             $err = PHP_REPD_UpdateDefaultsTwo(CUR);
//             if ($err != constAppNoErr) {
//                 echo "Error $err updating defaults to update two.";
//             }
//         }

//         /* Very simple update procedure:
//                 1. On a new server, the version for this data does not exist,
//                     therefore the inserts are good enough.
//                 2. If the version does exist, check if its the old version.
//             */
//         $err = constAppNoErr;
//         if ($dataversion < constReportDataVersion) {
//             $err = PHP_REPF_UpdateDynamicData(CUR);
//             if ($err != constAppNoErr) {
//                 echo "Error $err updating report defaults.";
//             }
//         }

//         /* Set our server to the current version */
//         if ($err == constAppNoErr) {
//             $err = PHP_DBAS_SetDataVersion(
//                 CUR,
//                 constDataTypeReports,
//                 constReportDataVersion
//             );
//             if ($err != constAppNoErr) {
//                 echo "Error $err updating report defaults.";
//             }
//         }
//     } else {
//         echo "Error $err updating report defaults.";
//     }
// }

// echo again();
// echo head_standard_html_footer($authuser, $db);
