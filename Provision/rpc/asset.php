<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 8-Aug-02   EWB     Added new timestamp fields to Machine table
 9-Aug-02   EWB     Moved ALIST parser into server.php3
 9-Aug-02   EWB     Better debug features
 9-Aug-02   EWB     Added setbyclient, groups to DataName table.
 9-Aug-02   EWB     Conditionaly record groups in DataName table.
12-Aug-02   EWB     mysql failures logged to php log file.
12-Aug-02   EWB     Client Date is integer.
12-Aug-02   EWB     Added AssetData "gaps" feature.
12-Aug-02   EWB     Update DataName groups to reflect client.
12-Aug-02   EWB     Don't look for existing records for new host.
22-Aug-02   EWB     Turned off the output logging.
27-Aug-02   EWB     Record earliest possible time for new item.
 9-Sep-02   EWB     Repent from the evil of magic_quotes
18-Sep-02   EWB     Record earliest observed time for new item.
 7-Oct-02   EWB     Make a record for Site Name
 7-Nov-02   EWB     Added a few statistical measurements.
12-Nov-02   EWB     double_decode for alist values
13-Nov-02   EWB     Return correct code when database unavailable.
19-Dec-02   EWB     set_time_limit(0);
30-Jan-03   EWB     Report both upload and written counts.
 3-Feb-03   EWB     Enabled 'asset_code' parallel asset logging.
13-May-03   EWB     Much better error handling.
 2-Jun-03   EWB     Fixed a bug in asset group logging.
20-Jun-03   EWB     Provisional Update.
23-Jun-03   EWB     Cancel update.
24-Jun-03   EWB     Track all failures.
24-Jun-03   EWB     Removed cancel_update ... fixing the damage of
                    a failed update is not our responsibility.
17-Jul-03   EWB     Sets DataName creation date.
15-Oct-03   EWB     Fixed a particulary subtle race condition.
30-Oct-03   EWB     Establish a limit for number of asset uploads.
30-Oct-03   EWB     Pass large arrays by reference.
 3-Nov-03   EWB     Reduce memory usage.
 5-Nov-03   EWB     Asset Abort Code.
13-Nov-03   AAM     Re-did asset logging to use temporary tables and
                    just a few SQL queries.
30-Nov-03   AAM     Added more configurable asset log disable.
10-Dec-03   AAM     Fixed problem where bad ALIST from client was causing
                    misleading error message in log.  Also made bad ALIST not
                    leave the "provisional" update set.  Also changed log to
                    actually include the bad ALIST so that we can see it.
                    Changed ELOG_AssetDataALIST to use new "by-reference"
                    access (get_alist_param) to get its fifth parameter, which
                    can be very large.
10-Dec-03   EWB     error_log takes two args.
10-Dec-03   EWB     unclaim machine correctly.
26-Dec-03   AAM     Change select into temp table "updatedata" to happen in
                    two steps, with "mdata" as an intermediate table.  This
                    sped things up enormously by reducing the size of the JOIN.
 4-Mar-04   EWB     raise memory limit 128M
 8-Apr-04   EWB     record number of concurrent updates in log file.
17-Aug-04   EWB     reports pid of logging process in log file.
18-Mar-05   EWB     new asset logging rpc.
16-May-05   BJS     changed DataName.name -> DataName.clientname.
08-Jun-05   BJS     Fixed temp table creation to use clientname in primary keys.
03-Oct-05   AAM     Added asset debug capability to resolve bug 2883.
31-Oct-05   BTE     Removed ELOG_AssetLog.
10-Nov-05   BTE     Updated to use the new census_manage function.
24-Jan-06   AAM     Bug 3072: change memory_limit setting to use max_php_mem_mb.
                    (Note that this change was actually moved from 4.2 to 4.3
                    on 06-Mar-06.)
15-May-06   BTE     Bug 2967: Handle duplicate UUIDs and remove migration for
                    pre-2.2 clients.
04-Oct-06   AAM     Bug 3677: Removed logging of data in bad ALISTs, and faked
                    as if those were good asset logs.
06-Jun-07   WOH     Modified to handle partial asset logs.
11-Jun-07   WOH     Did a little clean up.
13-Jun-07   WOH     Added code to handle server is busy for old client.
18-Jun-07   WOH     Handle asset logs with no changes.

*/

/* Passed in from ELOG_AssetPartialDataALIST() and ELOG_AssetDataALIST()
        and used in asset_data() */
define('constFullLog', 1);
define('constPartialLog', 2);

function asset_debug($text)
{
    logs::log(__FILE__, __LINE__, $text, 0);
}


/* Create the temporary tables that we will use in order to do asset
        logging.  Return true if we succeeded in creating all the tables,
        otherwise return false.
    */
function create_temp_tables($tmp_prefix, $tbl_prefix, $db)
{
    $sql = "CREATE $tmp_prefix TABLE ${tbl_prefix}newdata (\n"
        . " clientname varchar(50) DEFAULT '' NOT NULL,\n"
        . " value varchar(255) DEFAULT '' NOT NULL,\n"
        . " ordinal int(11) DEFAULT '0' NOT NULL,\n"
        . " groupname varchar(50) DEFAULT '' NOT NULL,\n"
        . " PRIMARY KEY (clientname,ordinal)\n"
        . ")";
    $res = command($sql, $db);
    if (!$res) {
        return false;
    }

    $sql = "CREATE $tmp_prefix TABLE ${tbl_prefix}newgroups (\n"
        . " clientname varchar(50) DEFAULT '' NOT NULL,\n"
        . " PRIMARY KEY (clientname)\n"
        . " )";
    $res = command($sql, $db);
    if (!$res) {
        return false;
    }

    /* groupid can be NULL if the group name doesn't appear in the DataName
            table.  This is the normal case for an empty group ID.  What we
            do is replace the NULLs with 0s in a separate step. */
    $sql = "CREATE $tmp_prefix TABLE ${tbl_prefix}newdataids (\n"
        . " clientname varchar(50) DEFAULT '' NOT NULL,\n"
        . " groupid int(11) DEFAULT '0',\n"
        . " PRIMARY KEY (clientname)\n"
        . " )";
    $res = command($sql, $db);
    if (!$res) {
        return false;
    }

    /* This table must have the same columns as DataName because it is used
            in a REPLACE operation. */
    $sql = "CREATE $tmp_prefix TABLE ${tbl_prefix}changegroups (\n"
        . " dataid int(11) DEFAULT '0' NOT NULL,\n"
        . " setbyclient tinyint(1) DEFAULT '0' NOT NULL,\n"
        . " name varchar(50) DEFAULT '' NOT NULL,\n"
        . " parent int(11) DEFAULT '0' NOT NULL,\n"
        . " ordinal int(11) DEFAULT '0' NOT NULL,\n"
        . " groups int(11) DEFAULT '0' NOT NULL,\n"
        . " created int(11) DEFAULT '0' NOT NULL,\n"
        . " leader tinyint(1) DEFAULT '0' NOT NULL,\n"
        . " include tinyint(1) DEFAULT '0' NOT NULL,\n"
        . " clientname varchar(50) DEFAULT '' NOT NULL,\n"
        . " PRIMARY KEY (dataid)\n"
        . " )";
    $res = command($sql, $db);
    if (!$res) {
        return false;
    }

    $sql = "CREATE $tmp_prefix TABLE ${tbl_prefix}newdataid (\n"
        . " dataid int(11) DEFAULT '0' NOT NULL,\n"
        . " value varchar(255) DEFAULT '' NOT NULL,\n"
        . " ordinal int(11) DEFAULT '0' NOT NULL,\n"
        . " PRIMARY KEY (dataid,ordinal),\n"
        . " INDEX dataid (dataid),\n"
        . " INDEX ordinal (ordinal),\n"
        . " INDEX value (value)\n"
        . " )";
    $res = command($sql, $db);
    if (!$res) {
        return false;
    }

    $sql = "CREATE $tmp_prefix TABLE ${tbl_prefix}insertdata (\n"
        . " dataid int(11) DEFAULT '0' NOT NULL,\n"
        . " ordinal int(11) DEFAULT '0' NOT NULL,\n"
        . " value varchar(255) DEFAULT '0' NOT NULL\n"
        . " )";
    $res = command($sql, $db);
    if (!$res) {
        return false;
    }

    /* This table must have the same columns as AssetData because it is used
            in a REPLACE operation. */
    $sql = "CREATE $tmp_prefix TABLE ${tbl_prefix}updatedata (\n"
        . " id int(11) DEFAULT '0' NOT NULL,\n"
        . " machineid int(11) DEFAULT '0' NOT NULL,\n"
        . " dataid int(11) DEFAULT '0' NOT NULL,\n"
        . " value varchar(255) DEFAULT '' NOT NULL,\n"
        . " ordinal int(11) DEFAULT '0' NOT NULL,\n"
        . " cearliest int(11) DEFAULT '0' NOT NULL,\n"
        . " cobserved int(11) DEFAULT '0' NOT NULL,\n"
        . " clatest int(11) DEFAULT '0' NOT NULL,\n"
        . " searliest int(11) DEFAULT '0' NOT NULL,\n"
        . " sobserved int(11) DEFAULT '0' NOT NULL,\n"
        . " slatest int(11) DEFAULT '0' NOT NULL,\n"
        . " uuid varchar(50) DEFAULT '' NOT NULL,\n"
        . " PRIMARY KEY (id),\n"
        . " INDEX dataid (dataid),\n"
        . " INDEX ordinal (ordinal)\n"
        . " )";
    $res = command($sql, $db);
    if (!$res) {
        return false;
    }

    /* This table have a similar format as AssetData, but it only contains
            data for one machine from the latest server time, so it doesn't
            need those columns.  Also, it doesn't need the current client
            time since that is constant.  Finally, note that we can afford
            to index the value column since this table should only have a few
            thousand rows. */
    $sql = "CREATE $tmp_prefix TABLE ${tbl_prefix}mdata (\n"
        . " id int(11) DEFAULT '0' NOT NULL,\n"
        . " dataid int(11) DEFAULT '0' NOT NULL,\n"
        . " value varchar(255) DEFAULT '' NOT NULL,\n"
        . " ordinal int(11) DEFAULT '0' NOT NULL,\n"
        . " cearliest int(11) DEFAULT '0' NOT NULL,\n"
        . " cobserved int(11) DEFAULT '0' NOT NULL,\n"
        . " searliest int(11) DEFAULT '0' NOT NULL,\n"
        . " sobserved int(11) DEFAULT '0' NOT NULL,\n"
        . " uuid varchar(50) DEFAULT '' NOT NULL,\n"
        . " PRIMARY KEY (id),\n"
        . " INDEX dataid (dataid),\n"
        . " INDEX ordinal (ordinal),\n"
        . " INDEX value (value)\n"
        . " )";
    $res = command($sql, $db);
    if (!$res) {
        return false;
    }

    return true;
}



/* Delete the temp tables when we are finished with them. */
function delete_temp_tables($tbl_prefix, $db)
{
    $sql = "DROP TABLE IF EXISTS ${tbl_prefix}newdata";
    command($sql, $db);
    $sql = "DROP TABLE IF EXISTS ${tbl_prefix}newgroups";
    command($sql, $db);
    $sql = "DROP TABLE IF EXISTS ${tbl_prefix}newdataids";
    command($sql, $db);
    $sql = "DROP TABLE IF EXISTS ${tbl_prefix}changegroups";
    command($sql, $db);
    $sql = "DROP TABLE IF EXISTS ${tbl_prefix}newdataid";
    command($sql, $db);
    $sql = "DROP TABLE IF EXISTS ${tbl_prefix}insertdata";
    command($sql, $db);
    $sql = "DROP TABLE IF EXISTS ${tbl_prefix}updatedata";
    command($sql, $db);
    $sql = "DROP TABLE IF EXISTS ${tbl_prefix}mdata";
    command($sql, $db);
}




/*
    |  Returns the machine record for success, and
    |  an empty array for any kind of failure.
    */

function find_machine($host, $cust, $db)
{
    $machine = array();
    $qhost = safe_addslashes($host);
    $qcust = safe_addslashes($cust);

    $sql = "select * from Machine where\n"
        . " host = '$qhost' and\n"
        . " cust = '$qcust'";
    $row = find_one($sql, $db);

    if ($row) {
        $mid = $row['machineid'];
        $machine['mid']  = $mid;
        $machine['site'] = $row['cust'];
        $machine['host'] = $row['host'];
        $machine['uuid'] = $row['uuid'];
        $machine['cmax'] = $row['clatest'];
        $machine['cmin'] = $row['cearliest'];
        $machine['smax'] = $row['slatest'];
        $machine['smin'] = $row['searliest'];
        $machine['prov'] = $row['provisional'];
        debug_note("host:$host, cust:$cust, mid:$mid");
    }
    return $machine;
}


/*
    |  Figures out how many updates are currently
    |  marked as in process.
    */

function count_updates($db)
{
    $num = 0;
    $sql = "select count(*) from\n"
        . " Machine where\n"
        . " provisional > 0";
    $res = command($sql, $db);
    if ($res) {
        $num = mysqli_result($res, 0);
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $num;
}



/*
    |  We are now recording the uuid whenever it is
    |  available, however we aren't really using it
    |  for anything yet.
    */

function create_mid($host, $cust, $uuid, $ctime, $stime, $db)
{
    $qh = safe_addslashes($host);
    $qs = safe_addslashes($cust);
    $qu = safe_addslashes($uuid);

    $sql = "insert into Machine set\n"
        . " host='$qh',\n"
        . " cust='$qs',\n"
        . " uuid='$qu',\n"
        . " cearliest=$ctime,\n"
        . " searliest=$stime,\n"
        . " clatest=0,\n"
        . " slatest=0,\n"
        . " provisional=$stime";
    $res = command($sql, $db);
    logs::log(__FILE__, __LINE__, "assets: $host introduction at site $cust.", 0);
    return ($res) ? true : false;
}


/*
    |  Update the machine table to reflect that
    |  an update is in progress.
    */

function claim_machine($mid, $stime, $db)
{
    $res = false;
    if ($mid > 0) {
        $sql = "update Machine set\n"
            . " provisional=$stime\n"
            . " where machineid = $mid";
        $res = command($sql, $db);
    }
    return $res;
}


/*
    |  Update the machine table to reflect that
    |  the update is now complete.
    */

function update_mid($mid, $ctime, $stime, $db)
{
    $res = false;
    if ($mid > 0) {
        $sql = "update Machine set\n"
            . " slatest=$stime,\n"
            . " clatest=$ctime,\n"
            . " provisional=0\n"
            . " where machineid = $mid";
        $res = command($sql, $db);
    }
    return $res;
}


/*
    |  Find the specified machine, and set the provisional update time,
    |  which signifies that an update is in progress.  If the database
    |  fails during the update, then we will know that the machine is
    |  invalid.
    |
    |  Returns the machine record for success, or an empty array for failure.
    |
    |  1. The provisional update time must be zero when we begin.
    |     If not, then a previous update has failed and we're just
    |     going to wait for the cron job to fix things up.
    |
    |  2. The new server time must be strictly greater than the previous
    |     server time.
    |
    |  The other possibility is that the client has sent several asset updates
    |  at close to same time, and is trying to start a new update while another
    |  one is still in progress.
    */

function enter_machine($host, $cust, $uuid, $ctime, $stime, $db)
{
    $machine = array();
    $mach = find_machine($host, $cust, $db);
    $prov = 0;
    if ($mach) {
        $mid  = $mach['mid'];
        $prov = $mach['prov'];
        $smax = $mach['smax'];
        if (($prov == 0) && ($stime > $smax)) {
            if (claim_machine($mid, $stime, $db)) {
                $machine = $mach;
            }
        }
    } else {
        if (create_mid($host, $cust, $uuid, $ctime, $stime, $db)) {
            $machine = find_machine($host, $cust, $db);
        }
    }

    if (safe_count($machine) == 0) {
        if ($prov) {
            $date = date('m/d H:i:s', $prov);
            $text = "assets: pending update ($date) for $host at $cust";
        } else {
            $text = "assets: update failure for $host at $cust";
        }
        logs::log(__FILE__, __LINE__, $text, 0);
    }

    return $machine;
}




/* Check whether we have exceeded the time limit for doing an insert
        into the AssetData table.  If so, return false.  This will cause
        the logging operation to abort and get cleaned up later on.
        $stime is the server time when the logging operation started, $host
        is the machine name doing the logging, $site is the site name doing
        the logging, and $usec is the micro-time representation of when the
        logging started.

        This function defines the maximum logging time as 100 minutes.  The
        asset cron will attempt to repair any incomplete log more than two
        hours old.  This function will prevent a log from happening during
        that process.
    */
function check_timeout($stime, $host, $site, $usec)
{
    $age = time() - $stime;

    if ($age > 6000) {
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $text = "assets: $host -- timeout in $secs at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        return false;
    } else {
        return true;
    }
}



/*
    |  We almost always create a new record for every asset named.
    |
    |  The only time an existing record is updated is when
    |  we find an existing record that is just the same,
    |  and the last update time matches the last server
    |  update time.
    |
    |  If we find an item we haven't seen before,
    |  we set its earliest date to be the earliest
    |  possible time it could have been installed,
    |  which is one second after last log time.
    |
    |  If the client logs at times A, B, C, D,
    |  then the records created show intervals:
    |
    |       A..A, A+1..B, B+1..C, C+1..D.
    |
    |   If neither the value nor the ordinal
    |   changes, then there will only be one
    |   record, spanning the interval:
    |
    |       A..D
    |
    |   If a new item shows up at time B and later
    |   and then examined at time E (E > D) will show
    |   the following:
    |
    |    earliest: A+1
    |    observed: B
    |      latest: D
    |
    |   This means the item could have been installed at any time
    |   between A+1..B-1, but it was definitely there during the
    |   interval B..D.
    |
    */

function asset_data(&$args, $uuid, $ctime, $stime, &$list, $alen, $db)
{
    $busy    = false;  // but not very busy.
    $success = false;  // assume utter failure.

    $usec = &$args['usec'];
    $site = &$args['valu'][3];
    $host = &$args['valu'][4];

    $mach = array();

    /* $usize is the number of items the client sent. */
    /* $wsize is the number of new items added to the AssetData table. */
    $usize = $list[0];
    $wsize = 0;
    $num   = 1;
    $mid   = 0;
    $alistOK = true;

    $section = 0;
    $mach = enter_machine($host, $site, $uuid, $ctime, $stime, $db);
    if ($mach) {
        $mid = $mach['mid'];
        $num = count_updates($db);
        $max = server_def('max_asset_logs', 2, $db);
        if ($num > $max)
            $busy = true;
        else
            $success = true;
    }

    /* Handle the asset logging debugging options.  If you set the
            server options "asset_debug_site" and "asset_debug_machine"
            to a site name and machine name, the asset logging will save
            the temporary tables as permanent tables with the name
            debug_XXX so you can look at them.  When you are done,
            you should clear those server settings and delete the tables. */
    if ($success) {
        $section = 1;
        $dbg_site = server_opt('asset_debug_site', $db);
        $dbg_machine = server_opt('asset_debug_machine', $db);
        if (($site == $dbg_site) && ($host == $dbg_machine)) {
            $is_debug = true;
            $tmp_prefix = '';
            $tbl_prefix = 'debug_';
            /* Get rid of the tables if they are already there. */
            delete_temp_tables($tbl_prefix, $db);
        } else {
            $is_debug = false;
            $tmp_prefix = 'TEMPORARY';
            $tbl_prefix = '';
        }
    }
    if ($success) {
        $section = 2;
        /* $sql will accumulate a big INSERT to put all the data into
                the newdata table.  The IGNORE is in case we have duplicate
                entries; all but the first one will be ignored. */
        switch ($args['valu'][8]) {
            case constFullLog:
                $sql = "INSERT IGNORE INTO ${tbl_prefix}newdata\n"
                    . " (clientname,value,ordinal,groupname) VALUES ";
                break;
            case constPartialLog:
                $sql = "REPLACE INTO ${tbl_prefix}newdata\n"
                    . " (clientname,value,ordinal,groupname) VALUES ";
                break;
            default:
                break;
        }

        $index = 1;
        /* Since indices are zero-based, the last index is actually one
                less than the number of items in the list */
        $maxindex = safe_count($list) - 1;
        /* For each asset data item ... */
        for ($i = 0; $alistOK && ($i < $usize); $i++) {
            /* Skip the generated name and the PALIST type */
            $index += 2;
            if ($index > $maxindex) {
                $alistOK = false;
                break;
            }

            /* Get the size of the ALIST for this asset data item */
            $itemsize = $list[$index++];
            if ($index > $maxindex) {
                $alistOK = false;
                break;
            }

            $grup = '';
            /* For each item in the small ALIST, do the right thing */
            for ($j = 0; $j < $itemsize; $j++) {
                $type = $list[$index++];
                /* Skip the PSTRING data type */
                $index++;
                if ($index > $maxindex) {
                    $alistOK = false;
                    break;
                }
                switch ($type) {
                    case 'l':
                        $name = double_decode($list[$index]);
                        break;
                    case 'o':
                        $ord = $list[$index];
                        break;
                    case 'd':
                        $valu = double_decode($list[$index]);
                        break;
                    case 'g':
                        $grup = double_decode($list[$index]);
                        break;
                    default:
                        /* If it's one we don't recognize, just skip it */
                        break;
                }
                /* Move to the next small item */
                $index++;
                if ($index > $maxindex) {
                    $alistOK = false;
                    break;
                }
            }
            /* Move on to the next asset data item */
            $index++;
            if ($index > $maxindex) {
                $alistOK = false;
                break;
            }

            /* Add the item to the big INSERT */
            if ($i > 0) {
                $sql .= ",";
            }
            $qname = safe_addslashes($name);
            $qvalu = safe_addslashes($valu);
            $qgrup = safe_addslashes($grup);
            $sql .= "('$qname','$qvalu',$ord,'$qgrup')";
            debug_note("name:$name, valu:$valu, ord:$ord, grup:$grup");
        } /* for each asset data item */

        /* Add the "fake" entry for the site name. */
        if ($usize > 0) {
            $sql .= ",\n";
        }
        $qsite = safe_addslashes($site);
        $sql .= "('Site Name','$qsite',1,'')";

        /* Make sure that we didn't leave extra stuff at the end. */
        if ($index != $maxindex) {
            $alistOK = false;
        }
    }


    if (!$alistOK) {
        $msg  = "error in ALIST data from client, no logging";
        $text = "assets: $host at $site -- $msg";
        logs::log(__FILE__, __LINE__, $text, 0);
        /* We used to log the actual erroneous data but this was
                unnecessarily bloating php.log. */
        $success = false;
    }

    /* Create the temp tables that we use to do the log. */
    if ($success) {
        $section = 3;
        $success = create_temp_tables($tmp_prefix, $tbl_prefix, $db);
        if (!$success) {
            $msg  = "assets: first create_temp_tables failed";
            $text = "assets: $host at $site -- $msg";
            logs::log(__FILE__, __LINE__, $text, 0);
            delete_temp_tables($tbl_prefix, $db);
            $success = create_temp_tables($tmp_prefix, $tbl_prefix, $db);
            if (!$success) {
                $msg  = "create_temp_tables failed, no logging";
                $text = "assets: $host at $site -- $msg";
                logs::log(__FILE__, __LINE__, $text, 0);
            }
        }
    }

    /* Now populate the new temp table. */
    if ($success) {
        $section = 4;
        switch ($args['valu'][8]) {
            case constFullLog:
                /* Insert all the data into the newdata table. */
                $success = command($sql, $db);
                break;
            case constPartialLog:
                /* Get all the data from the last asset log
                    and put that into a temp table */
                $smax = $mach['smax'];
                $insert = "INSERT IGNORE INTO ${tbl_prefix}newdata (clientname,value,ordinal,groupname)"
                    . " SELECT d.clientname, a.value, a.ordinal, g.clientname FROM"
                    . " AssetData AS a LEFT JOIN DataName AS d ON (a.dataid = d.dataid)"
                    . " LEFT JOIN DataName AS g ON (d.groups = g.dataid)"
                    . " WHERE (a.machineid = $mid)"
                    . " AND (a.searliest <= $smax) AND ($smax <= a.slatest)";
                $success = command($insert, $db);

                /* Handle case where nothing changed on the client */
                if (($success) && ($usize > 0)) {
                    $success = command($sql, $db);
                }

                if (!$success) {
                    $msg  = "create_temp_tables failed, no logging";
                    $text = "assets: $host at $site -- $msg";
                    logs::log(__FILE__, __LINE__, $text, 0);
                    $success = false;
                }
                break;
            default:
                $success = false;
                break;
        }
    }

    /* Do the logging. */
    if ($success) {
        $section = 5;
        /* Set up the variables we need for constants in the
                database operations. */
        $smin = $mach['smin'];
        $smax = $mach['smax'];
        $cmax = $mach['cmax'];

        /* Figure out what group names are new, and add them to the
                DataName table.  Note that we just let the values of parent,
                ordinal, and leader take default values. */
        if ($success) {
            $sql = "INSERT INTO ${tbl_prefix}newgroups (clientname)\n"
                . " SELECT DISTINCT ${tbl_prefix}newdata.groupname FROM\n"
                . "  ${tbl_prefix}newdata LEFT JOIN DataName ON\n"
                . "   (${tbl_prefix}newdata.groupname = DataName.clientname)\n"
                . "  WHERE (DataName.clientname IS NULL) AND\n"
                . "   (${tbl_prefix}newdata.groupname != '')";
            $success = command($sql, $db);
            if ($is_debug) {
                logs::log(__FILE__, __LINE__, "assets: $host at $site"
                    . " created ${tbl_prefix}newgroups", 0);
            }
        }

        /* We use IGNORE here in case there is another thread that is
                also adding group names.  This way we will insert any that
                we have that it doesn't, rather than aborting. */
        if ($success) {
            $sql = "INSERT IGNORE INTO DataName\n"
                . " (setbyclient, name, clientname, groups, created)\n"
                . "   SELECT 1, clientname, clientname, 0, $stime FROM"
                . "    ${tbl_prefix}newgroups";
            $success = command($sql, $db);
            if ($is_debug) {
                logs::log(__FILE__, __LINE__, "assets: $host at $site"
                    . " updated DataName with ${tbl_prefix}newgroups", 0);
            }
        }


        /* Figure out what data names are new, and add them to the
                DataName table with the correct group ID.  Note that
                groupid can end up as NULL if the group name doesn't exist
                in DataName (this is the normal case for an empty group
                name).  So, that's why we set NULLs to zeros. */
        if ($success) {
            $sql = "INSERT INTO ${tbl_prefix}newdataids (clientname, groupid)\n"
                . " SELECT DISTINCT ${tbl_prefix}newdata.clientname, g.dataid FROM\n"
                . "  ${tbl_prefix}newdata LEFT JOIN DataName AS d\n"
                . "   ON (${tbl_prefix}newdata.clientname = d.clientname)\n"
                . "   LEFT JOIN DataName as g\n"
                . "   ON (${tbl_prefix}newdata.groupname = g.clientname)\n"
                . "  WHERE (d.clientname IS NULL)";
            $success = command($sql, $db);
            if ($is_debug) {
                logs::log(__FILE__, __LINE__, "assets: $host at $site"
                    . " created ${tbl_prefix}newdataids", 0);
            }
        }

        if ($success) {
            $sql = "UPDATE ${tbl_prefix}newdataids SET groupid = 0\n"
                . " WHERE (groupid IS NULL)";
            $success = command($sql, $db);
            if ($is_debug) {
                logs::log(__FILE__, __LINE__, "assets: $host at $site"
                    . " fixed up ${tbl_prefix}newdataids", 0);
            }
        }

        /* We use IGNORE here in case there is another thread that is
                also adding data names.  This way we will insert any that
                we have that it doesn't, rather than aborting. */
        if ($success) {
            $sql = "INSERT IGNORE INTO DataName\n"
                . " (setbyclient, name, clientname, groups, created)\n"
                . "  SELECT 1, clientname, clientname, groupid, $stime"
                . "   FROM ${tbl_prefix}newdataids";
            $success = command($sql, $db);
            if ($is_debug) {
                logs::log(__FILE__, __LINE__, "assets: $host at $site"
                    . " updated DataName with ${tbl_prefix}newdataids", 0);
            }
        }


        /* Handle any items where the group name has changed from the
                one on the server.  We trust the one from the client and
                use it to update the server.  This probably shouldn't
                happen but we handle it just in case. */
        /* Well, it finally did happen, due to a data error in a name
                coming from the client, and it didn't work.  The IGNORE
                has to be there because there can be lots of the same item
                being changed, and if that causes an error, we bail out
                without any error message. */
        if ($success) {
            $sql = "INSERT IGNORE INTO ${tbl_prefix}changegroups\n"
                . " (dataid, setbyclient, name, parent,\n"
                . "  ordinal, groups, created, leader, include, clientname)\n"
                . " SELECT d.dataid, d.setbyclient,\n"
                . "  d.name, d.parent, d.ordinal,\n"
                . "  g.dataid, d.created, d.leader, d.include, d.clientname FROM\n"
                . "  ${tbl_prefix}newdata, DataName as d, DataName as g\n"
                . " WHERE (${tbl_prefix}newdata.clientname = d.clientname) AND\n"
                . "   (${tbl_prefix}newdata.groupname = g.clientname) AND\n"
                . "   (g.dataid != d.groups)";
            $success = command($sql, $db);
            if ($is_debug) {
                logs::log(__FILE__, __LINE__, "assets: $host at $site"
                    . " created ${tbl_prefix}changegroups", 0);
            }
        }

        if ($success) {
            $sql = "REPLACE INTO DataName\n"
                . "  SELECT * FROM ${tbl_prefix}changegroups";
            $success = command($sql, $db);
            if ($is_debug) {
                logs::log(__FILE__, __LINE__, "assets: $host at $site"
                    . " updated DataName with ${tbl_prefix}changegroups", 0);
            }
        }


        /* Get the data ids for all the items that the client logged. */
        if ($success) {
            $sql = "INSERT INTO ${tbl_prefix}newdataid (dataid, value, ordinal)\n"
                . "  SELECT DataName.dataid, ${tbl_prefix}newdata.value,\n"
                . "    ${tbl_prefix}newdata.ordinal FROM\n"
                . "  ${tbl_prefix}newdata, DataName\n"
                . "    WHERE (${tbl_prefix}newdata.clientname = DataName.clientname)";
            $success = command($sql, $db);
            if ($is_debug) {
                logs::log(__FILE__, __LINE__, "assets: $host at $site"
                    . " created ${tbl_prefix}newdataid", 0);
            }
        }


        /* Now do the actual asset logging. */
        if ($smin == $stime) {
            /* Check for timeout */
            $success = check_timeout($stime, $host, $site, $usec);

            /* Completely new machine, just insert the data. */
            if ($success) {
                $sql = "INSERT INTO AssetData\n"
                    . " (machineid, dataid, value, ordinal,\n"
                    . "  cearliest, cobserved, clatest,\n"
                    . "  searliest, sobserved, slatest,\n"
                    . "  uuid)\n"
                    . " SELECT $mid, dataid, value, ordinal,\n"
                    . "  $ctime, $ctime, $ctime,\n"
                    . "  $stime, $stime, $stime,\n"
                    . "  ''\n"
                    . " FROM ${tbl_prefix}newdataid";
                $success = command($sql, $db);
                if ($is_debug) {
                    logs::log(__FILE__, __LINE__, "assets: $host at $site"
                        . " inserted from ${tbl_prefix}newdataid"
                        . " for completely new machine", 0);
                }
            }

            /* Figure out how many new items we added. */
            if ($success) {
                $sql = "SELECT count(*) from ${tbl_prefix}newdataid";
                $res = command($sql, $db);
                if ($res) {
                    $wsize = mysqli_result($res,  0);
                    ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
                }
            }
        } else {
            /* Separate out the data into the part that will update
                    existing entries in AssetData and the part that will
                    be added completely new to AssetData. */
            /* Note that this is likely to be the most time-consuming
                    SQL operation in this whole series. */

            /* First just get the latest data for this machine.  In
                    theory, mysql should optimize a single select into
                    updatedata but it doesn't seem to work that way. */
            if ($success) {
                $sql = "INSERT into ${tbl_prefix}mdata\n"
                    . " (id, dataid, value, ordinal,\n"
                    . "  cearliest, cobserved,\n"
                    . "  searliest, sobserved,\n"
                    . "  uuid)\n"
                    . " SELECT\n"
                    . "  AssetData.id, AssetData.dataid,\n"
                    . "  AssetData.value, AssetData.ordinal,\n"
                    . "  AssetData.cearliest,\n"
                    . "    AssetData.cobserved,\n"
                    . "  AssetData.searliest,\n"
                    . "    AssetData.sobserved,\n"
                    . "  AssetData.uuid\n"
                    . "  FROM AssetData WHERE\n"
                    . "    (AssetData.machineid = $mid) AND\n"
                    . "    (AssetData.slatest = $smax)";
                $success = command($sql, $db);
                if ($is_debug) {
                    logs::log(__FILE__, __LINE__, "assets: $host at $site"
                        . " created ${tbl_prefix}mdata", 0);
                }
            }

            /* Now select out the items that will only be an update to
                    the latest time. */
            if ($success) {
                $sql = "INSERT INTO ${tbl_prefix}updatedata\n"
                    . " (id, machineid, dataid, value, ordinal,\n"
                    . "  cearliest, cobserved, clatest,\n"
                    . "  searliest, sobserved, slatest,\n"
                    . "  uuid)\n"
                    . " SELECT\n"
                    . "  ${tbl_prefix}mdata.id, $mid, ${tbl_prefix}mdata.dataid,\n"
                    . "  ${tbl_prefix}mdata.value, ${tbl_prefix}mdata.ordinal,\n"
                    . "  ${tbl_prefix}mdata.cearliest,\n"
                    . "    ${tbl_prefix}mdata.cobserved, $ctime,\n"
                    . "  ${tbl_prefix}mdata.searliest,\n"
                    . "    ${tbl_prefix}mdata.sobserved, $stime,\n"
                    . "  ${tbl_prefix}mdata.uuid\n"
                    . "  FROM ${tbl_prefix}newdataid LEFT JOIN ${tbl_prefix}mdata ON\n"
                    . "   (${tbl_prefix}mdata.dataid = ${tbl_prefix}newdataid.dataid) AND\n"
                    . "   (${tbl_prefix}mdata.ordinal = ${tbl_prefix}newdataid.ordinal) AND\n"
                    . "   (${tbl_prefix}mdata.value = ${tbl_prefix}newdataid.value)\n"
                    . "  WHERE (${tbl_prefix}mdata.id IS NOT NULL)";
                $success = command($sql, $db);
                if ($is_debug) {
                    logs::log(__FILE__, __LINE__, "assets: $host at $site"
                        . " created ${tbl_prefix}updatedata", 0);
                }
            }

            /* Note that for some reason, this operation also seems to
                    take a while.  This is a little unexpected, so we might
                    be able to fix it.  Adding some indices helped a lot. */
            if ($success) {
                $sql = "INSERT INTO ${tbl_prefix}insertdata (dataid, ordinal, value)\n"
                    . " SELECT ${tbl_prefix}newdataid.dataid, ${tbl_prefix}newdataid.ordinal,\n"
                    . "   ${tbl_prefix}newdataid.value FROM\n"
                    . " ${tbl_prefix}newdataid LEFT JOIN ${tbl_prefix}updatedata ON\n"
                    . "  (${tbl_prefix}newdataid.dataid = ${tbl_prefix}updatedata.dataid) AND\n"
                    . "  (${tbl_prefix}newdataid.ordinal = ${tbl_prefix}updatedata.ordinal)\n"
                    . " WHERE (${tbl_prefix}updatedata.dataid IS NULL)";
                $success = command($sql, $db);
                if ($is_debug) {
                    logs::log(__FILE__, __LINE__, "assets: $host at $site"
                        . " created ${tbl_prefix}insertdata", 0);
                }
            }

            /* Check for timeout */
            $success = check_timeout($stime, $host, $site, $usec);

            /* Now actually do the updates and the new inserts. */
            if ($success) {
                $sql = "REPLACE INTO AssetData\n"
                    . " SELECT * FROM ${tbl_prefix}updatedata";
                $success = command($sql, $db);
                if ($is_debug) {
                    logs::log(__FILE__, __LINE__, "assets: $host at $site"
                        . " updated AssetData from ${tbl_prefix}updatedata", 0);
                }
            }

            /* Note that in the insert we use the defaults for id and
                    uuid. */
            if ($success) {
                $sql = "INSERT INTO AssetData\n"
                    . " (machineid, dataid, value, ordinal,\n"
                    . "  cearliest, cobserved, clatest,\n"
                    . "  searliest, sobserved, slatest)\n"
                    . " SELECT $mid, dataid, value, ordinal,\n"
                    . "  $cmax + 1, $ctime, $ctime,\n"
                    . "  $smax + 1, $stime, $stime\n"
                    . " FROM ${tbl_prefix}insertdata";
                $success = command($sql, $db);
                if ($is_debug) {
                    logs::log(__FILE__, __LINE__, "assets: $host at $site"
                        . " updated AssetData from ${tbl_prefix}insertdata", 0);
                }
            }

            /* Figure out how many new items we added. */
            if ($success) {
                $sql = "SELECT count(*) from ${tbl_prefix}insertdata";
                $res = command($sql, $db);
                if ($res) {
                    $wsize = mysqli_result($res,  0);
                    ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
                }
            }
        }

        /* We're all done with the temp tables, so delete them unless
                we are debugging with them. */
        if (!$is_debug) {
            delete_temp_tables($tbl_prefix, $db);
        }
    }

    $rval = constErrDatabaseNotAvailable;
    $pid  = getmypid();

    if ($success) {
        if (update_mid($mid, $ctime, $stime, $db)) {
            $rval = constAppNoErr;
            $stat = "u:$usize,w:$wsize,n:$num,p:$pid,l:$alen";
            $msec = microtime_diff($usec, microtime());
            $secs = microtime_show($msec);
            $text = "assets: $host logging ($stat) in $secs at $site.";
        } else {
            $success = false;
        }
    }

    /* If the ALIST was bad, go ahead and un-mark this as a provisional
            update, because we didn't do anything.  Also, make the client think
            that the log succeeded, so that it doesn't continue to re-send the
            data incessantly.  But, log that we are doing this. */
    if (!$alistOK) {
        claim_machine($mid, 0, $db);
        $rval = constAppNoErr;
        $stat = "u:$usize,n:$num,p:$pid,l:$alen";
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $text = "assets: $host faking success ($stat) in $secs at $site.";
        $success = true;
    }

    if (!$success) {
        $rval = constErrDatabaseNotAvailable;
        $stat = "u:$usize,n:$num,p:$pid,l:$alen";
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $text = "assets: $host update failure ($stat) in $secs at $site on section $section.";
    }

    if ($busy) {
        claim_machine($mid, 0, $db);
        $rval = constErrServerTooBusy;
        $stat = "n:$num,m:$max,u:$usize,p:$pid,l:$alen";
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $text = "assets: $host -- server too busy ($stat) in $secs at $site.";
    }

    /* Output what we did. */
    logs::log(__FILE__, __LINE__, $text, 0);

    if ($success) {
        if (mysqli_select_db($db, core)) {
            $code = server_opt('asset_code', $db);
            if ($code) {
                eval($code);
            }
        }
    }
    return $rval;
}


/*
    |   Note that the "assetDataList" parameter is NOT passed through the
    |   "$args" array because it can be very large.  See the note in
    |   build_args in server.php for more detail.
    */

function asset_common(&$args)
{
    $stime = time();
    $scrp = &$args['valu'][1];
    $date = &$args['valu'][2];
    $site = &$args['valu'][3];
    $host = &$args['valu'][4];
    $list = &get_alist_param(5);
    $uuid = &$args['valu'][6];
    $code = &$args['valu'][7];
    $usec = &$args['usec'];
    $alen = strlen($list);
    $rval = constErrDatabaseNotAvailable;
    $more = true;
    $pid  = getmypid();

    // old clients used to send client time in mysql format
    // new ones use posix time.  remove this someday.

    $ctime = intval($date);
    if ($ctime <= 9999) $ctime = $stime;

    if (pfDisableAsset) {
        $more = false;
        $rval = constErrServerNoSupport;
        $stat = "p:$pid,l:$alen";
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $text = "assets: $host -- ignoring upload ($stat) in $secs at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    }

    if ($more) {
        $db = db_code('db_ast');
    }

    if (($more) && (!$db)) {
        $more = false;
        $rval = constErrDatabaseNotAvailable;
        $stat = "l:alen,p:$pid";
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $text = "assets: $host -- mysql unavailable ($stat) in $secs at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    }

    if ($more) {
        $temp = constAppNoErr;
        if ($args['updatecensus'] == 1) {
            $temp = census_manage($site, $host, $uuid, 1, $db);
            if ($temp != constAppNoErr) {
                $more = false;
                $rval = $temp;
            }
        }
    }

    if ($more) {
        set_time_limit(0);
        $mem = server_def('max_php_mem_mb', '256', $db);
        ini_set('memory_limit', $mem . 'M');
    }

    if ($more) {
        $data = explode('#', $list);
        $rval = asset_data(
            $args,
            $uuid,
            $ctime,
            $stime,
            $data,
            $alen,
            $db
        );
    }

    $args['dbug'] = "host:$host, site:$site, scrp:$scrp, date:$date";
    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = $rval;

    // $args returned to caller by reference.
}


/*
    |  Server side of ELOG_AssetDataALIST
    |
    |  ERRSTAT ELOG_AssetDataALIST(MACHINE mID,
    |                              UINT32 scripNum,
    |                              PSTRING dateTime,
    |                              PSTRING customerName,
    |                              PSTRING machineName,
    |                              PALIST assetDataList);
    |
    |   https://hfndev.com:9443/engr/specs/assetdb.htm
    */

function ELOG_AssetDataALIST(&$args)
{
    $args['valu'][6] = '';
    $args['valu'][7] = 0;
    $args['valu'][8] = constFullLog;
    asset_common($args);
}


/*
    |  Server side of ELOG_CheckAsset
    |
    |
    */

function ELOG_CheckAsset(&$args)
{
    // First see if the server is busy.
    $db = db_code('db_ast');
    $pid  = getmypid();
    $usec = &$args['usec'];
    $host = &$args['valu'][2];
    $site = &$args['valu'][3];
    $max = server_def('max_asset_logs', 2, $db);
    $num = count_updates($db);
    if ($max < $num + 1) {
        // note that the special case of max being
        // zero is explicitly allowed and expected.
        $rval = constErrServerTooBusy;
        $stat = "n:$num,m:$max,p:$pid";
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $text = "assets: $host -- server too busy ($stat) in $secs at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    } else {
        // If its not busy then go ahead and get the lastime
        //  the client sent an asset log.
        $host = $args['valu'][2];
        $cust = $args['valu'][3];
        $mach = find_machine($host, $cust, $db);
        if ($mach) {
            $args['valu'][1] = $mach['cmax'];
            $rval = constAppNoErr;
        } else {
            $args['valu'][1] = 0;
            $rval = constAppNoErr;
        }
    }
    $args['rval'] = $rval;
}

/*
    |  Server side of ELOG_AssetPartialDataALIST
    |
    |  ERRSTAT ELOG_AssetPartialDataALIST(MACHINE mID,
    |                              UINT32 scripNum,
    |                              PSTRING dateTime,
    |                              PSTRING customerName,
    |                              PSTRING machineName,
    |                              PALIST assetDataList);
    |
    */

function ELOG_AssetPartialDataALIST(&$args)
{
    $args['valu'][6] = '';
    $args['valu'][7] = 0;
    $args['valu'][8] = constPartialLog;
    asset_common($args);
}
