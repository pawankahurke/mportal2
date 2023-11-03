<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
 1-Mar-06   NL      Create file. 
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
 
*/


ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include('../lib/l-cnst.php');
include('../lib/l-errs.php');
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-head.php');
include('local.php');
include('../lib/l-user.php');
include('../lib/l-disp.php');


/*  
  FUNCTIONS USED FOR LEFT PANE 
*/


/*----------------------------------------------------------\
    |  get_selected() gets which left-pane item is selected     |
    |                                                           |
    |  params:                                                  |
    |  $userid: the logged in user                              |
    |  $db:     the db connection handle                        |
    |                                                           |
    \*---------------------------------------------------------*/
function get_selected($userid, $db)
{
    $selected = array();
    $selected['itemtype'] = 0;
    $selected['itemid'] = 0;
    $sql  = "SELECT selected, itemtype, itemid, parent\n"
        . "FROM UserSettings\n"
        . "WHERE userid = " . $userid . "\n"
        . "ORDER BY itemtype, itemid";
    $row = find_one($sql, $db);
    if ($row) {
        $selected['itemtype']  = $row['itemtype'];
        $selected['itemid']    = $row['itemid'];
        $selected['parent']    = $row['parent'];
    }
    return $selected;
}

/*----------------------------------------------------------\
    |  set_selected)( updates which left-pane item is selected  |
    |                                                           |
    |  params:                                                  |
    |  $userid: the logged in user                              |
    |  $db:     the db connection handle                        |
    |                                                           |
    \*---------------------------------------------------------*/
function set_selected($env, $sel_flag = 1)
{
    $db         = $env['db'];
    $userid     = $env['userid'];
    $itemtype   = $sel_flag ? $env['itemtype']  : 0;
    $itemid     = $sel_flag ? $env['itemid']    : 0;
    $parent     = $sel_flag ? $env['parent']    : 0;

    $selected = array();
    if (($itemtype && $itemid) ||  $sel_flag == 0) {
        $sql  = "UPDATE UserSettings\n"
            . "SET selected = " . $sel_flag . ",\n"
            . "itemtype = " . $itemtype . ",\n"
            . "itemid = " . $itemid . ",\n"
            . "parent = " . $parent . "\n"
            . "WHERE userid = " . $userid;
        $res = redcommand($sql, $db);
        if (affected($res, $db) == 1) {
            $rid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
            return $rid;
        }
    }
}

function deselect($env)
{
    set_selected($env, 0);
}


function expand($userid, $db)
{
    $itemtype   = get_argument('itemtype', 0, 0);
    $itemid     = get_argument('itemid', 0, 0);
    $expandid   = get_argument('expandid', 0, 0);
    $parent     = get_argument('parent', 0, 0);

    if ($expandid == 0) // expand it
    {
        $sql = "INSERT INTO Expansions SET\n"
            . " parent = " . $parent . ",\n"
            . " userid = " . $userid . ",\n"
            . " itemtype = " . $itemtype . ",\n"
            . " itemid = " . $itemid;
        $res = redcommand($sql, $db);

        if (affected($res, $db) == 1) {
            $rid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
            return $rid;
        }
    } else // unexpand it
    {
        $sql = "DELETE FROM Expansions\n"
            . "WHERE expandid = " . $expandid . "\n"
            . "OR parent = " . $expandid;
        $res = redcommand($sql, $db);
        return affected($res, $db);
    }
}

// HIERARCHY ITEM ARRAYS

/*
    NOTE: The left pane uses a series of arrays to collect the left pane
            hierarchy items.  This results in a lot of PHP code, which 
            could have been reduced by using SQL to generate the hierarchy.
            
            Ideally, the naming convention for the arrays of hierarchy items
            which follow  would be
                get_[item]_idx
                    returns an array of items
                    indexed by the itemid one level above
                    (exception: get_user_displays(), since it is at the top level)
                get_[item]_exp
                    returns an array of expanded items
                    indexed by the itemid one level above
                    
            But because machine info comes from 2 diff databases, 
            there are more functions for machines (and for monitored items, 
            due to legacy, but I could change that).

    However, there are currently no arrays for the P,S,R,E,M detail 
    categories since there is no data associated with them (in the left pane).

// DISPLAYS:
    /*----------------------------------------------------------\
    |  get_users_displays() gets all the displays (both id &    |
    |  name) that this user wants to display (should be all the |
    |  displays owns plus all global displays minus any         |
    |  displays this user has inactivated).                     |
    |                                                           |
    |  Output is an array with                                  |
    |   key = dispid                                            |
    |   val = name                                              |
    |                                                           |
    |  params:                                                  |
    |  $userid: the logged in user                              |
    |  $db:     the db connection handle                        |
    |                                                           |
    \*---------------------------------------------------------*/
function get_users_displays($userid, $db)
{
    $displays = array();
    $sql  = "SELECT Displays.dispid, Displays.name\n"
        . "FROM Displays, DisplayUsers\n"
        . "WHERE global = 1\n"
        . "  OR (Displays.dispid = DisplayUsers.dispid\n"
        . "  AND DisplayUsers.userid = " . $userid . ")\n"
        . "ORDER BY Displays.name";
    $set = find_many($sql, $db);
    reset($set);
    foreach ($set as $key => $row) {
        $dispid = $row['dispid'];
        $displays[$dispid] = $row['name'];
    }
    return $displays;
}

/*----------------------------------------------------------\
    |  get_displays_exp() gets all the displayids & expandids   |
    |  for displays that this user wants to display             |
    |  and that are currently expanded on the                   |
    |  dashboard.                                               |
    |                                                           |
    |  Output is an array with                                  |
    |   key = dispid                                            |
    |   val = expandid                                          |
    |                                                           |
    |  params:                                                  |
    |  $userid: the logged in user                              |
    |  $db:     the db connection handle                        |
    |                                                           |
    \*---------------------------------------------------------*/
function get_displays_exp($displays, $db)
{
    $dispids_str = join(",", safe_array_keys($displays));
    $displays_exp = array();
    $sql  = "SELECT Displays.dispid, Displays.name, Expansions.expandid\n"
        . "FROM Displays, Expansions\n"
        . "WHERE Displays.dispid IN (" . $dispids_str . ") \n"
        . "AND Expansions.itemtype = " . constDisplayItemDisplay . "\n"
        . "AND Expansions.itemid = Displays.dispid\n"
        . "ORDER BY Displays.name";
    $set = find_many($sql, $db);
    reset($set);
    foreach ($set as $key => $row) {
        $dispid = $row['dispid'];
        $expandid = $row['expandid'];
        $displays_exp[$dispid] = $expandid;
    }
    return $displays_exp;
}

// MACHINE GROUPS:

/*----------------------------------------------------------\
    |  get_mgroups_idx() gets all the machinegroup ids & names  |
    |  that are part of any display being displayed, indexed by |
    |  display id.   All user displays are included, whether or |
    |  not there are machine groups associated with it.         |
    |                                                           |
    |  Output is an associative array with                      |
    |  key = dispid                                             |
    |  val = associative array of mgroupids (or empty array)    |
    |   key2 = mgroupid                                         |
    |   val2 = mgroupname                                       |
    |  $mgroups_idx[$dispid][$mgroupid] = $mgroupname           |
    |                                                           |
    |  params:                                                  |
    |  $display: array of displays (k=dispid, v=dispname)       |
    |  $db:     the db connection handle                        |
    |                                                           |
    \*---------------------------------------------------------*/
function get_mgroups_idx($displays, $db)
{
    $mgroups_idx = array();
    if ($displays) {
        // initialize $mgroups_idx w/ k = $display keys (dispid); v = empty array.
        reset($displays);
        foreach ($displays as $dispid => $row) {
            $mgroups_idx[$dispid] = array();
        }

        $dispids_str = join(",", safe_array_keys($displays));
        $sql  = "SELECT DMG.dispid, DMG.mgroupid, cMG.name\n"
            . "FROM DisplayMachineGroups AS DMG, " . $GLOBALS['PREFIX'] . "core.MachineGroups AS cMG\n"
            . "WHERE DMG.dispid IN ($dispids_str)\n"
            . "AND DMG.mgroupid = cMG.mgroupid\n"
            . "ORDER BY DMG.dispid, DMG.mgroupid";
        $set = find_many($sql, $db);
        reset($set);
        foreach ($set as $key => $row) {
            $dispid     = $row['dispid'];
            $mgroupid   = $row['mgroupid'];
            $name       = $row['name'];
            $mgroups_idx[$dispid][$mgroupid] = $name;
        }
    }
    return $mgroups_idx;
}

/*----------------------------------------------------------\
    |  get_mgroups_exp() gets all the machinegroupids and       |
    |  expandids that are part of any display  being displayed  |
    |  and that are currently expanded on the dashboard,        |
    |  indexed by display id.   All expanded user displays are  |
    |  included, whether or not there are machine groups        |
    |  associated with it.                                      |
    |                                                           |
    |  Output is an associative array with                      |
    |  key = dispid                                             |
    |  val = assoc array of expanded mgroupids (or empty array) |
    |   key2 = mgroupid                                         |
    |   val2 = expandid                                         |
    |  $mgroups_exp[$dispid][$mgroupid] = $expandid             |
    |                                                           |
    |  params:                                                  |
    |  $array of expanded displays (k=dispid, v=expandid)       |
    |  $db:     the db connection handle                        |
    |                                                           |
    \*---------------------------------------------------------*/
function get_mgroups_exp($displays_exp, $db)
{
    $mgroups_exp = array();
    if ($displays_exp) {
        // initialize $mgroups_exp w/ k = $displays_exp keys (dispid); v = empty array.
        $mgroups_exp = $displays_exp;
        reset($mgroups_exp);
        foreach ($mgroups_exp as $dispid => $mgroups) {
            $mgroups_exp[$dispid] = array();
        }

        $dispids_exp_str = join(",", safe_array_keys($displays_exp));
        $disp_expids_str = join(",", array_values($displays_exp));

        //expandid's parent == displayids expandid          
        $sql  = "SELECT E.expandid, E.itemid AS mgroupid, E.parent, "
            . "Ex.expandid AS par_expid, Ex.itemtype, Ex.itemid AS dispid\n"
            . "FROM Expansions AS E, Expansions AS Ex\n"
            . "WHERE E.itemtype = " . constDisplayItemMachineGroup . "\n"
            . "AND E.itemid IN \n"
            . "( \n"
            .   "SELECT mgroupid FROM DisplayMachineGroups\n"
            .   "WHERE dispid IN (" . $dispids_exp_str . ")\n"
            . ")\n"
            . "AND E.parent = Ex.expandid \n";

        $set = find_many($sql, $db);
        reset($set);
        foreach ($set as $key => $row) {
            $dispid    = $row['dispid'];
            $mgroupid  = $row['mgroupid'];
            $expandid  = $row['expandid'];

            $mgroups_exp[$dispid][$mgroupid] = $expandid;
        }
    }
    return $mgroups_exp;
}

// MACHINES:

/*----------------------------------------------------------\
    |  get_machines_idx() gets all the machine ids and          |
    |  names (site:host) that are part of any display being     |
    |  displayed.                                               |
    |                                                           |
    |  Output is an associative array with                      |
    |   key = mgroupid                                          |
    |   val = associative array of machines (or empty array)    |
    |       key = mid                                           |
    |       val = mname                                         |
    |  $machines_idx[$mgroupid][$mid] = $mname                  |
    |                                                           |
    |  params:                                                  |
    |  $mgroupids_str: comma-delimited string of mgroupids      |
    |  $db: the db connection handle                            |
    |                                                           |
    \*---------------------------------------------------------*/
function get_machines_idx($mgroupids_str, $db)
{
    $mids = array();
    $machines_idx = array();
    // initialize $machines_idx w/ k = mgroupids; v = empty array.
    $mgroupids = explode(",", $mgroupids_str);
    reset($mgroupids);
    foreach ($mgroupids as $key => $mgroupid) {
        $machines_idx[$mgroupid] = array();
    }

    if ($mgroupids_str) {
        $sql  = "SELECT MG.mgroupid, MGM.mgroupuniq, MGM.censusuniq, C.id, C.site, C.host\n"
            . "FROM MachineGroups AS MG, MachineGroupMap AS MGM, Census AS C\n"
            . "WHERE mgroupid IN (" . $mgroupids_str . ")\n"
            . "AND MG.mgroupuniq = MGM.mgroupuniq\n"
            . "AND MGM.censusuniq = C.censusuniq\n"
            . "ORDER BY mgroupid, id";
        $set = find_many($sql, $db);
        reset($set);
        foreach ($set as $key => $row) {
            $mgroupid  = $row['mgroupid'];
            $mid  = $row['id'];
            $site = $row['site'];
            $host = $row['host'];

            $machines_idx[$mgroupid][$mid] = $site . ":" . $host;
            natcasesort($machines_idx[$mgroupid]); // sort by mname, case insensitive

            $mids[] = $mid;
        }
        $mids = array_unique($mids);
        sort($mids);
    }
    $out['machines_idx'] = $machines_idx;
    $out['mids'] = $mids;
    return $out;
}


/*----------------------------------------------------------\
    |  get_machines_exp() gets all the machine ids & expandids  |
    |  that are part of any machinegroup being displayed        |
    |  and that is currently expanded on the dashboard,         |
    |  indexed first by displayid then by machine group id.     |
    |  All expanded machine groups are included, whether or not |
    |  there are machines associated with it.                   |
    |                                                           |
    |  Output is an associative array with                      |
    |  key1 = dispid                                            |
    |  val1 = assoc array of expanded mgroupids (or empty array)|
    |   key2 = mgroupid                                         |
    |   val2 = assoc array of expanded mids (or empty array)    |
    |    key3 = mid                                             |
    |    val3 = expandid                                        |
    |  $machines_exp[$dispid][$mgid][$mid] = $expandid          |
    |                                                           |
    |  params:                                                  |
    |  $array of expanded mgroups (k=dispid, v=mgroups array    |
    |  $db:     the db connection handle                        |
    |                                                           |
    \*---------------------------------------------------------*/
function get_machines_exp($mgroups_exp, $userid, $db)
{
    $machines_exp = array();
    if ($mgroups_exp) {
        reset($mgroups_exp);
        foreach ($mgroups_exp as $dispid => $mgroups) {
            $machines_exp[$dispid] = array();
            reset($mgroups);
            foreach ($mgroups as $mgid => $value) {
                $machines_exp[$dispid][$mgid] = array();

                $sql  = "SELECT itemid AS machineid, expandid\n"
                    . "FROM Expansions\n"
                    . "WHERE userid = " . $userid . "\n"
                    . "AND itemtype = " . constDisplayItemMachine . "\n"
                    . "AND parent IN\n"
                    . "( \n"
                    .     "SELECT expandid\n"
                    .     "FROM Expansions\n"
                    .     "WHERE userid = " . $userid . "\n"
                    .     "AND itemtype = " . constDisplayItemMachineGroup . "\n"
                    .     "AND itemid = " . $mgid . "\n"
                    .     "AND parent IN\n"
                    .     "( \n"
                    .         "SELECT expandid\n"
                    .         "FROM Expansions\n"
                    .         "WHERE userid = " . $userid . "\n"
                    .         "AND itemtype = " . constDisplayItemDisplay . "\n"
                    .         "AND itemid = " . $dispid . "\n"
                    .     ")\n"
                    . ")\n"
                    . "ORDER BY machineid";
                $set = find_many($sql, $db);
                reset($set);
                foreach ($set as $key => $row) {
                    $mid        = $row['machineid'];
                    $expandid   = $row['expandid'];
                    $machines_exp[$dispid][$mgid][$mid] = $expandid;
                }
            }
        }
    }
    return $machines_exp;
}

// MONITERED ITEM GROUPS:

/*----------------------------------------------------------\
    |  get_mongroups_idx() gets all the monitem groupids & names|
    |  that are part of any display being displayed, indexed by |
    |  display id.   All user displays are included, whether or |
    |  not there are monitered item groups associated with it.  |
    |                                                           |
    |  Output is an associative array with                      |
    |  key = dispid                                             |
    |  val = associative array of mongroupids (or empty array)  |
    |   key2 = mongroupid                                       |
    |   val2 = mongroupname                                       |
    |  $mongroups_idx[$dispid][$mongroupid] = $mongroupname     |
    |                                                           |
    |  params:                                                  |
    |  $display: array of displays (k=dispid, v=dispname)       |
    |  $db:     the db connection handle                        |
    |                                                           |
    \*---------------------------------------------------------*/
function get_mongroups_idx($displays, $db)
{
    $mongroups_idx = array();
    if ($displays) {
        // initialize $mongroups_idx w/ k = $display keys; v = empty array.
        reset($displays);
        foreach ($displays as $dispid => $row) {
            $mongroups_idx[$dispid] = array();
        }

        $dispids_str = join(",", safe_array_keys($displays));
        $sql  = "SELECT DMG.dispid, DMG.mongroupid, MG.name\n"
            . "FROM DisplayMonitorGroups AS DMG, MonitorGroups AS MG\n"
            . "WHERE dispid IN (" . $dispids_str . ")\n"
            . "ORDER BY DMG.dispid, DMG.mongroupid";
        $set = find_many($sql, $db);
        reset($set);
        foreach ($set as $key => $row) {
            $dispid     = $row['dispid'];
            $mongroupid = $row['mongroupid'];
            $name       = $row['name'];
            $mongroups_idx[$dispid][$mongroupid] = $name;
        }
    }
    return $mongroups_idx;
}

/*----------------------------------------------------------\
    |  get_mongroups_exp() gets all the mongroupids & expandids |
    |  that are part of any display  being displayed            |
    |  and that are currently expanded on the dashboard,        |
    |  indexed by display id.   All expanded user displays are  |
    |  included, whether or not there are monitored item groups |
    |  associated with it.                                      |
    |                                                           |
    |  Output is an associative array with                      |
    |   key = dispid                                            |
    |   val = assoc array of expanded mongroupids (or empty ar) |
    |       key2 = mongroupid                                   |
    |       val2 = expandid                                     |
    |  $mongroups_exp[$dispid][$mongroupid] = $expandid         |
    |                                                           |
    |  params:                                                  |
    |  $array of expanded displays (k=dispid, v=dispname)       |
    |  $db:     the db connection handle                        |
    |                                                           |
    \*---------------------------------------------------------*/
function get_mongroups_exp($displays_exp, $db)
{
    $mongroups_exp = array();
    if ($displays_exp) {
        // initialize $mongroups_exp w/ k = $displays_exp keys (dispid); v = empty array.
        $mongroups_exp = $displays_exp;
        reset($mongroups_exp);
        foreach ($mongroups_exp as $dispid => $mongroups) {
            $mongroups_exp[$dispid] = array();
        }

        $dispids_exp_str = join(",", safe_array_keys($displays_exp));
        $sql  = "SELECT DisplayMonitorGroups.mongroupid, DisplayMonitorGroups.dispid, E.expandid\n"
            . "FROM DisplayMonitorGroups, Expansions AS E, MonitorGroups\n"
            . "WHERE DisplayMonitorGroups.dispid IN (" . $dispids_exp_str . ")\n"
            . "AND E.itemtype = " . constDisplayItemMonItemGroup . "\n"
            . "AND E.itemid = MonitorGroups.mongroupid\n"
            . "ORDER BY dispid,mongroupid";
        $set = find_many($sql, $db);
        reset($set);
        foreach ($set as $key => $row) {
            $dispid    = $row['dispid'];
            $mongroupid = $row['mongroupid'];
            $expandid  = $row['expandid'];

            $mongroups_exp[$dispid][$mongroupid] = $expandid;
        }
    }
    return $mongroups_exp;
}

// MONITORED ITEMS:

/*----------------------------------------------------------\
    |  get_monitems() gets all the monitored item ids and       |
    |  names that are part of any display being displayed.      |
    |                                                           |
    |  Output is an associative array with                      |
    |   key = mongroupid                                        |
    |   val = assoc array of monitems (or empty array)          |
    |       key = monitemid                                     |
    |       val = monitemname                                   |
    |  $monitems_idx[$mongroupid][$monitemid] = $monitemname    |
    |                                                           |
    |  params:                                                  |
    |  $mongroupids_str: comma-delimited string of mongroupids  |
    |  $db: the db connection handle                            |
    |                                                           |
    \*---------------------------------------------------------*/
function get_monitems_idx($mongroupids_str, $db)
{
    $monitems_idx = array();
    // initialize $monitems_idx w/ k = mongroupids; v = empty array.
    $mongroupids = explode(",", $mongroupids_str);
    reset($mongroupids);
    foreach ($mongroupids as $key => $mongroupid) {
        $monitems_idx[$mongroupid] = array();
    }

    if ($mongroupids_str) {
        $sql  = "SELECT MGM.mongroupid, MGM.monitemid, MI.name\n"
            . "FROM MonitorGroupMap AS MGM, MonitorItems AS MI\n"
            . "WHERE mongroupid IN (" . $mongroupids_str . ")\n"
            . "AND MGM.monitemid = MI.monitemid\n"
            . "ORDER BY mongroupid, name";
        $set = find_many($sql, $db);
        db_change($GLOBALS['PREFIX'] . 'dashboard', $db);
        reset($set);
        foreach ($set as $key => $row) {
            $mongroupid    = $row['mongroupid'];
            $monitemid     = $row['monitemid'];
            $monitemname   = $row['name'];

            $monitems_idx[$mongroupid][$monitemid] = $monitemname;
        }
    }
    return $monitems_idx;
}

// STATUS:

/*----------------------------------------------------------\
    |  get_status_machcats() creates a 2D array, indexed by     |
    |  mid (censusid), of the status for each machine detail    |
    |  category that are part of any display being displayed.   |
    |                                                           |
    |  Output is an associative array with                      |
    |   key = machineid ($mid, same as $censusid)               |
    |   val = assoc array of status                             |
    |       key = status category label                         |
    |       val = status                                        |
    |  $status_machcats[$mid]['status_P'] = $status             |
    |                                                           |
    |  params:                                                  |
    |  $mids_str: comma-delimited string of mids (censusids)    |
    |  $db: the db connection handle                            |
    |                                                           |
    \*---------------------------------------------------------*/
function get_status_machcats($mids_str, $db)
{
    $status_machcats = array();
    if ($mids_str) {
        // WARNING: is it certain there would be records in all 5 categories?
        $sql = "SELECT PD.censusid, PD.status AS status_P,\n"
            . "SD.status AS status_S,\n"
            . "RD.status AS status_R,\n"
            . "ED.status AS status_E,\n"
            . "MD.status AS status_M\n"
            . "FROM ProfileDisplay AS PD, SecurityDisplay AS SD,\n"
            . "ResourceDisplay AS RD, EventDisplay AS ED,\n"
            . "MaintenanceDisplay AS MD\n"
            . "WHERE PD.censusid = SD.censusid\n"
            . "AND RD.censusid = ED.censusid\n"
            . "AND ED.censusid = PD.censusid\n"
            . "AND PD.censusid IN (" . $mids_str . ")\n"
            . "AND MD.censusid = PD.censusid\n"
            . "ORDER by censusid";
        $set = find_many($sql, $db);
        reset($set);
        foreach ($set as $key => $row) {
            $mid       = $row['censusid'];
            $status_machcats[$mid]['status_P'] = $row['status_P'];
            $status_machcats[$mid]['status_S'] = $row['status_S'];
            $status_machcats[$mid]['status_R'] = $row['status_R'];
            $status_machcats[$mid]['status_E'] = $row['status_E'];
            $status_machcats[$mid]['status_M'] = $row['status_M'];
        }
    }
    return $status_machcats;
}

/*----------------------------------------------------------\
    |  get_status_monitem() creates an array of status for each |
    |  monitemid that is a part of any display being displayed. |
    |                                                           |
    |  Output is an associative array with                      |
    |   key = monitemid                                         |
    |   val = status                                            |
    |   $status_monitem[$monitemid] = $status;                  |
    |                                                           |
    |  params:                                                  |
    |  $mongroupids_str: comma-delimited string of mongroupids  |
    |  $db: the db connection handle                            |
    |                                                           |
    \*---------------------------------------------------------*/
function get_status_monitem($mongroupids_str, $db)
{
    $status_monitem = array();
    if ($mongroupids_str) {
        $sql = "SELECT monitemid, `status`\n"
            . "FROM MonitorDisplay\n"
            . "WHERE monitemid IN (\n"
            . "    SELECT MGM.monitemid\n"
            . "    FROM MonitorGroupMap AS MGM\n"
            . "    WHERE mongroupid IN (" . $mongroupids_str . ")\n"
            . ")\n"
            . "ORDER by monitemid";
        $set = find_many($sql, $db);
        reset($set);
        foreach ($set as $key => $row) {
            $monitemid = $row['monitemid'];
            $status    = $row['status'];
            $status_monitem[$monitemid] = $status;
        }
    }
    return $status_monitem;
}


/*----------------------------------------------------------\
    |  create_hier_item() creates the table row (<tr>)          |
    |  containingthe images, links and text for an item in the  |
    |  left-pane hierarchy.                                     |
    |                                                           |
    |  Output is an html string.                                |
    |                                                           |
    |  There is a long list of parameters.  Alternately, the    |
    |  arguments passed could be placed in an array (and just   |
    |  the array passed), but seeing the individual arguments   |
    |  passed in probably makes it easier to understand why     |
    |  certain args are used, e.g. that mid is used as itemid   |
    |  for detail cateopgry items (PSREM).                      |
    |                                                           |
    |  params:                                                  |
    |  $itemtype: the type of item                              |
    |  $itemid: the id of the item; var name depends on itemtype|
    |  $expandid: Expansions.expandid of this item              |
    |  $parent: Expansions.expandid of the parent of this item  |
    |  $colspan: corresponds to indent                          |
    |  $itemname: the name of the item                          |
    |  $selected: the array w/ the currently selected item      |
    |  $status: the rolled-up status for this item              |
    |  $statuscfg: the array with the status color & border     |
    |                                                           |
    \*---------------------------------------------------------*/
function create_hier_item($itemtype, $itemid, $expandid, $parent, $colspan, $itemname, $selected, $status, $statuscfg)
{
    /*
        UserSettings.itemtype (0-4 used in Expansions.itemtype):
        0   constDisplayItemUndefined       invalid (uninitialized)
        1   constDisplayItemDisplay 	    display
        2   constDisplayItemMachineGroup    machine group
        3   constDisplayItemMonItemGroup 	monitoring item group
        4   constDisplayItemMachine 	    machine
        5   constDisplayItemMonItem 	    monitoring item
        // IMPORTANT: at the detail category level (PSREM), use itemid & parent of corresponding machine:
        6   constDisplayItemProfile         profile data for a machine (itemid = machineid, $parent=machine's parentid)
        7   constDisplayItemSecurity        security data for a machine (itemid = machineid, $parent=machine's parentid)
        8   constDisplayItemResources 	    resource data for a machine (itemid = machineid, $parent=machine's parentid)
        9   constDisplayItemEvents 	        event info data a machine (itemid = machineid, $parent=machine's parentid)
        10  constDisplayItemMaintenance     maintenance data for a machine (itemid = machineid, $parent=machine's parentid)
        */

    switch ($itemtype) {
        case constDisplayItemDisplay:
            $image = 'display';
            $alt = 'Display';
            break;
        case constDisplayItemMachineGroup:
            $image = 'machine-group';
            $alt = 'Machine group';
            break;
        case constDisplayItemMonItemGroup:
            $image = 'mon-item-group';
            $alt = 'Monitored item group';
            break;
        case constDisplayItemMachine:
            $image = 'machine';
            $alt = 'Machine';
            break;
        case constDisplayItemMonItem:
            $image = 'mon-item';
            $alt = 'Monitored item';
            break;
        case constDisplayItemProfile:
            $image = 'profile';
            $alt = 'Profile';
            break;
        case constDisplayItemSecurity:
            $image = 'security';
            $alt = 'Security';
            break;
        case constDisplayItemResources:
            $image = 'resources';
            $alt = 'Resources';
            break;
        case constDisplayItemEvents:
            $image = 'events';
            $alt = 'Events';
            break;
        case constDisplayItemMaintenance:
            $image = 'maintenance';
            $alt = 'Maintenance';
            break;
    }

    $expandables = array(
        constDisplayItemDisplay,
        constDisplayItemMachineGroup,
        constDisplayItemMonItemGroup,
        constDisplayItemMachine
    );

    $bold       = bold_html($selected, $itemtype, $itemid);
    $border_fmt = border_format($status, $statuscfg);
    $width      = $border_fmt['width'];
    $color      = $border_fmt['color'];
    $style      = $border_fmt['style'];

    $html       = "";
    $expd       = ($expandid) ? "1" : "0";
    $symbol     = ($expd) ? "minus" : "plus";
    $symbol_alt = ($expd) ? "contract" : "expand";
    $icon       = "<img border=\"0\" src=\"images/" . $image . ".gif\" alt=\"" . $alt . "\">";
    $iref       = server_var('PHP_SELF') . "?act=expand&itemtype=" . $itemtype . "&itemid=" . $itemid
        . "&expandid=" . $expandid . "&parent=" . $parent;
    $plusminus  = "<img border=\"0\" src=\"images/" . $symbol . ".gif\" alt=\"" . $symbol_alt . "\">";
    $exp_link   = (in_array($itemtype, $expandables)) ? "<a href=\"$iref\">$plusminus</a>" : "";

    // parent in the QS is a kludge so we can determine the snapshot link
    $href       = server_var('PHP_SELF') . "?act=select&itemtype=" . $itemtype . "&itemid=" . $itemid . "&parent=" . $parent;
    $item_link  = "<a class=\"hidelink\" href=\"$href\">$itemname</a>";
    $td = <<< HERE
        
        <td></td>\n
HERE;
    $tds = str_repeat($td, 4 - $colspan);
    $html = <<< HERE
        
        <tr>
        $tds
        <td align="center">$exp_link</td>
        <td align="center">$icon</td>
        <td width='100%' colspan=$colspan>
        <table cellspacing="2" cellpadding="1">
            <tr>
            <td nowrap $bold style="border-width:$width; border-color:$color; border-style:$style; ">$item_link</td>
            <td width='100%'></td>
            </tr>
    	</table>
        </td>
        </tr>
        
HERE;

    return $html;
}

/*----------------------------------------------------------\
    |  gen_left_pane() creates all the arrays of display items, |
    |  then uses a loop to rollup status values, then uses a    |
    |  uses a loop to generate the html.                        |
    |                                                           |
    |  Output is an html string.                                |
    |                                                           |
    |  params:                                                  |
    |  $env: the array of environment vars (incl form & QS args)|
    |  $selected: the array w/ the currently selected item      |
    |  $statuscfg: the array with the status color & border     |
    |                                                           |
    \*---------------------------------------------------------*/
function gen_left_pane($env, $selected, $statuscfg)
{
    $db         = $env['db'];
    $userid     = $env['userid'];

    /*
        First Create all the arrays of items
        */

    //get the displays avail to this user
    $displays = get_users_displays($userid, $db);
    $displays_exp = get_displays_exp($displays, $db);

    // get the mgroups from the dashboard db
    $mgroups_idx = get_mgroups_idx($displays, $db);
    $mgroups_exp = get_mgroups_exp($displays_exp, $db);
    $mgroupids_str = join(',', flatten_array($mgroups_idx, $displays, $db));
    // but get the machines from the core db
    db_change($GLOBALS['PREFIX'] . 'core', $db);  // put this line inside function?
    $out = get_machines_idx($mgroupids_str, $db);
    $machines_idx = $out['machines_idx'];
    $mids = $out['mids'];
    $mids_str = join(",", $mids);
    db_change($GLOBALS['PREFIX'] . 'dashboard', $db);
    $machines_exp = get_machines_exp($mgroups_exp, $userid, $db);

    $mongroups_idx = get_mongroups_idx($displays, $db);
    $mongroups_exp = get_mongroups_exp($displays_exp, $db);
    $mongroupids_str = join(',', flatten_array($mongroups_idx, $displays, $db));
    $monitems_idx = get_monitems_idx($mongroupids_str, $db);

    /*
        Determine the status of machine detail data and mon items 
        and roll up status to machgroups, monitemgroups & displays.
        
        This would be a lot more elegantly done in SQL using Allan & Brian's SQL code.
        */
    $status_machcats = get_status_machcats($mids_str, $db);
    $status_monitem = get_status_monitem($mongroupids_str, $db);
    $status_mach    = array();
    $status_mgroup  = array();
    $status_mongroup = array();
    $status_disp    = array();

    reset($displays);
    foreach ($displays as $dispid => $dispname) {
        $status_mgroup[$dispid]     = array();
        $status_mongroup[$dispid]   = array();
        reset($mgroups_idx);
        reset($mgroups_idx[$dispid]);
        foreach ($mgroups_idx[$dispid] as $mgroupid => $mgroupname) {
            $status_max = 0;
            reset($machines_idx);
            reset($machines_idx[$mgroupid]);
            foreach ($machines_idx[$mgroupid] as $mid => $mname) {
                // Rolling up from status_machcats array.
                $status_mach[$mid] = array_key_exists($mid, $status_machcats) ? max($status_machcats[$mid]) : 0;
                /*
                        Unfortunately, we can't do it this way; array_interesect_key() needs PHP5.1
                        // Rolling up from status_mach array.
                        $these_machids = safe_array_keys($machines_idx[$mgroupid]);
                        // status for just the machines in this mongroup
                        $these_status = array_intersect_key($status_mach,$these_machids);
                        $status_mgroup[$dispid][$mgroupid] = max($these_status);
                    */
                // Rolling up from status_mach array. 
                if ($status_mach[$mid] > $status_max) {
                    $status_max = $status_mach[$mid];
                }
            }
            // Rolling up...
            $status_mgroup[$dispid][$mgroupid] = $status_max;
        }
        reset($mongroups_idx);
        reset($mongroups_idx[$dispid]);
        foreach ($mongroups_idx[$dispid] as $mongroupid => $mongroupname) {
            /*
                Unfortunately, we can't do it this way; array_interesect_key() needs PHP5.1
                // Rolling up from status_monitem array.
                $these_monitemids = safe_array_keys($monitems_idx[$mongroupid]);
                // status for just the monitems in this mongroup
                $these_status = array_intersect_key($status_monitem,$these_monitemids);
                $status_mongroup[$dispid][$mongroupid] = max($these_status);
            */
            $status_max = 0;
            reset($monitems_idx);
            reset($monitems_idx[$mongroupid]);
            foreach ($monitems_idx[$mongroupid] as $monitemid => $monitemname) {
                // Rolling up from monitem_status array. 
                if ($status_monitem[$monitemid] > $status_max) {
                    $status_max = $status_monitem[$monitemid];
                }
            }
            $status_mongroup[$dispid][$mongroupid] = $status_max;
        }
        // Rolling up...

        $status_groups[$dispid] = array_merge(array_values($status_mgroup[$dispid]), array_values($status_mongroup[$dispid]));
        $status_disp[$dispid] = max($status_groups[$dispid]);
    }


    /*
        Now Generate the HTML
        */
    $html = "";
    $one_selected = 0;
    reset($displays);
    foreach ($displays as $dispid => $dispname) {
        $parent = 0;
        $expd   = array_key_exists($dispid, $displays_exp);
        $expandid = $expd ? $displays_exp[$dispid] : 0;
        $status = $status_disp[$dispid];
        $html  .= create_hier_item(constDisplayItemDisplay, $dispid, $expandid, $parent, 4, $dispname, $selected, $status, $statuscfg);
        if (($selected['itemtype'] == constDisplayItemDisplay) && ($selected['itemid'] == $dispid)) $one_selected = 1;

        $dispexpandid = $expandid; // need to preserve this var for mongroup/monitem loops below

        if ($expd) {
            reset($mgroups_idx);
            reset($mgroups_idx[$dispid]);
            foreach ($mgroups_idx[$dispid] as $mgroupid => $mgroupname) {
                $parent = $dispexpandid;
                $expd   = array_key_exists($mgroupid, $mgroups_exp[$dispid]);
                $expandid = $expd ? $mgroups_exp[$dispid][$mgroupid] : 0;
                $status = $status_mgroup[$dispid][$mgroupid];
                $html  .= create_hier_item(constDisplayItemMachineGroup, $mgroupid, $expandid, $parent, 3, $mgroupname, $selected, $status, $statuscfg);
                if (($selected['itemtype'] == constDisplayItemMachineGroup) && ($selected['itemid'] == $mgroupid)) $one_selected = 1;

                if ($expd) {
                    $parent = $expandid;
                    reset($machines_idx);
                    reset($machines_idx[$mgroupid]);
                    foreach ($machines_idx[$mgroupid] as $mid => $mname) {
                        $expd   = array_key_exists($mid, $machines_exp[$dispid][$mgroupid]);
                        $expandid = $expd ? $machines_exp[$dispid][$mgroupid][$mid] : 0;
                        $status = $status_mach[$mid];
                        $html  .= create_hier_item(constDisplayItemMachine, $mid, $expandid, $parent, 2, $mname, $selected, $status, $statuscfg);
                        if (($selected['itemtype'] == constDisplayItemMachine) && ($selected['itemid'] == $mid)) $one_selected = 1;

                        if ($expd) {
                            // IMPORTANT: at the detail category level (PSREM), use itemid and parent of corresponding machine
                            $status = $status_machcats[$mid]['status_P'];
                            $html  .= create_hier_item(constDisplayItemProfile, $mid, 0, $parent, 1, "Profile", $selected, $status, $statuscfg);
                            $status = $status_machcats[$mid]['status_S'];
                            $html  .= create_hier_item(constDisplayItemSecurity, $mid, 0, $parent, 1, "Security", $selected, $status, $statuscfg);
                            $status = $status_machcats[$mid]['status_R'];
                            $html  .= create_hier_item(constDisplayItemResources, $mid, 0, $parent, 1, "Resources", $selected, $status, $statuscfg);
                            $status = $status_machcats[$mid]['status_E'];
                            $html  .= create_hier_item(constDisplayItemEvents, $mid, 0, $parent, 1, "Events", $selected, $status, $statuscfg);
                            $status = $status_machcats[$mid]['status_M'];
                            $html  .= create_hier_item(constDisplayItemMaintenance, $mid, 0, $parent, 1, "Maintenance", $selected, $status, $statuscfg);
                            $detcats = array(constDisplayItemProfile, constDisplayItemSecurity, constDisplayItemResources, constDisplayItemEvents, constDisplayItemMaintenance);
                            if (in_array($selected['itemtype'], $detcats)  && ($selected['itemid'] == $mid)) $one_selected = 1;
                        }
                    }
                }
            }

            reset($mongroups_idx);
            reset($mongroups_idx[$dispid]);
            foreach ($mongroups_idx[$dispid] as $mongroupid => $mongroupname) {
                $parent = $dispexpandid;
                $expd   = array_key_exists($mongroupid, $mongroups_exp[$dispid]);
                $expandid = $expd ? $mongroups_exp[$dispid][$mongroupid] : 0;
                $status = $status_mongroup[$dispid][$mongroupid];
                $html  .= create_hier_item(constDisplayItemMonItemGroup, $mongroupid, $expandid, $parent, 3, $mongroupname, $selected, $status, $statuscfg);
                if (($selected['itemtype'] == constDisplayItemMonItemGroup) && ($selected['itemid'] == $mongroupid)) $one_selected = 1;

                if ($expd) {
                    $parent = $expandid;
                    reset($monitems_idx);
                    reset($monitems_idx[$mongroupid]);
                    foreach ($monitems_idx[$mongroupid] as $monitemid => $monitemname) {
                        $status = $status_monitem[$monitemid];
                        $html  .= create_hier_item(constDisplayItemMonItem, $monitemid, $expandid, $parent, 2, $monitemname, $selected, $status, $statuscfg);
                        if (($selected['itemtype'] == constDisplayItemMonItem) && ($selected['itemid'] == $monitemid)) $one_selected = 1;
                    }
                }
            }
        }
    }
    if ($one_selected != 1) {
        deselect($env);
    }
    return $html;
}


/*
     RIGHT PANE
*/

/*----------------------------------------------------------\
    |  gen_right_pane() generates the html to diplay the right  |
    |  pane.  It calls functions to get the heading,            |
    |  snapshot & restart monitoring links, and the selection   |
    |  table.  (It doesn not yet take ninto account that for    |
    |  a profile item, a selection table is not used.           |
    |                                                           |
    |  Output is an html string.                                |
    |                                                           |
    |  params:                                                  |
    |  $env: the array of environment vars (incl form & QS args)|
    |  $selected: the array w/ the currently selected item      |
    |  $statuscfg: the array with the status color & border     |
    |  $user: the array with data about the logged in user      |
    |                                                           |
    \*---------------------------------------------------------*/
function gen_right_pane($env, $selected, $statuscfg, $user)
{
    $db         = $env['db'];
    $self       = $env['self'];
    $userid     = $env['userid'];
    $act        = $env['act'];
    $selitemtype = $selected['itemtype'];
    $selitemid  = $selected['itemid'];
    $selparent  = $selected['parent'];

    $heading        = gen_heading($selitemtype, $selitemid, $db);
    $snapshot_link  = create_snapshot_link($selitemtype, $selitemid, $selparent, $db);
    $restart_link   = create_restart_link($selitemtype, $selitemid, $db);
    $standalonelink = ($snapshot_link) ? $snapshot_link : $restart_link;
    $standalonelink = ($standalonelink) ? '[' .  $standalonelink . ']<br><br>' : '';
    $html_sel       = gen_seltables($env, $selected, $db);
    $html_sel       = "";

    $html = <<< HERE
        
        <tr>
        <td valign="top"><font size="+1">$heading</font>
            <br><br>
            $standalonelink
            $html_sel
            <br><br>
        </td>
        </tr>

HERE;
    return $html;
}

function get_refresh($userid, $db)
{
    $refresh = 0;
    $sql  = "SELECT refresh\n"
        . "FROM UserSettings\n"
        . "WHERE userid = " . $userid . "\n"
        . "ORDER BY itemtype, itemid";
    $row = find_one($sql, $db);
    if ($row) {
        $refresh = $row['refresh'];
    }
    return $refresh;
}


/*
    |  Main program
    */

/* Perform authentication */
$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();
$user  = user_data($authuser, $db);
$debug = @($user['priv_debug']) ? 1 : 0;
$admin = @($user['priv_admin']) ? 1 : 0;
$userid =  ($user['userid']);
$username = ($user['username']);

define('constDisplayItemDisplay',       1);
define('constDisplayItemMachineGroup',  2);
define('constDisplayItemMonItemGroup',  3);
define('constDisplayItemMachine',       4);
define('constDisplayItemMonItem',       5);
define('constDisplayItemProfile',       6);
define('constDisplayItemSecurity',      7);
define('constDisplayItemResources',     8);
define('constDisplayItemEvents',        9);
define('constDisplayItemMaintenance',  10);

$act        = get_string('act', 'select');
//IMPORTANT: at the detail category level (PSREM)
//itemid and parent are of the corresponding machine (itemid === censusid)
$itemtype   = get_integer('itemtype', 0);
$itemid     = get_integer('itemid',   0);
$expandid   = get_integer('expandid', 0);
$parent     = get_integer('parent',   0);

$env            = array();
$env['self']    = server_var('PHP_SELF');
$env['args']    = server_var('QUERY_STRING');
$env['db']      = $db;
$env['userid']  = $userid;
$env['username'] = $username;
$env['act']     = $act;
$env['itemtype'] = $itemtype;
$env['itemid']  = $itemid;
$env['expandid'] = $expandid;
$env['parent']  = $parent;

db_change($GLOBALS['PREFIX'] . 'dashboard', $db);

$refresh = get_refresh($userid, $db);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer) 

if ($refresh) header("Refresh: $refresh;");

$title = "ASI Site and System Status Dashboard";
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($act == 'expand') expand($userid, $db);
if ($act == 'select') set_selected($env, 1);


$selected   = get_selected($userid, $db);
$statuscfg  = get_statuscfg($userid, $db);

$html_left  = gen_left_pane($env, $selected, $statuscfg);
$html_right = gen_right_pane($env, $selected, $statuscfg, $user);


$html_top = <<< HERE
<table border="1" cellspacing="0" cellpadding="2">
    <tr><td valign="top">
    <table border="0" align="left" cellspacing="0" cellpadding="0">
    
HERE;

$html_mid = <<< HERE

    </table>
    &nbsp;<br>
    </td>
    <td valign="top">
    <table border="0" align="left" cellspacing="0" cellpadding="0">

HERE;

$html_bot = <<< HERE
    </table>
</table>    

HERE;

echo $html_top . $html_left . $html_mid . $html_right . $html_bot;
echo head_standard_html_footer($authuser, $db);
