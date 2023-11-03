<?php

/*
Revision history:

Date        Who     What
----        ---     ----
25-Nov-02   EWB     Created.
 3-Dev-02   EWB     A bit more error checking.
10-Feb-03   EWB     Don't worry about duplicate hosts.
14-Feb-03   EWB     Automatically create new sites.
18-Mar-03   EWB     Admin users get access to new sites.
 4-Dec-03   EWB     Set ownership of new sites.
25-May-04   EWB     All logging updates the census.
26-May-04   EWB     Duplicate entry keeps most recent.
 4-Jun-04   EWB     Adding a machine sets the census dirty bit.
15-Jul-04   EWB     Check for new site at machine migration time.
27-Jul-04   EWB     Migrate for asset and update.
10-Mar-05   EWB     Log site / host when killing uuid clone.
16-Mar-05   EWB     Unique index on Census UUID
 1-Jun-05   EWB     Config Site Migration
 2-Jun-05   EWB     Machine Migration sets census dirty bit.
12-Sep-05   BTE     Added checksum invalidation code.
12-Oct-05   BTE     Updated a comment.
31-Oct-05   BTE     Removed ELOG_GetToken and ELOG_UpdateUUID.
10-Nov-05   BTE     Updated to use the new census_manage function.
26-Jan-06   BTE     Removed unused code.
06-May-06   BTE     Bug 3209: 4.2 to 4.3 server upgrade does not work
                    correctly.
15-May-06   BTE     Bug 2967: Handle duplicate UUIDs and remove migration for
                    pre-2.2 clients.
19-Feb-08   BTE     Bug 4416: Move the "last event log" timestamp into shared
                    memory.

*/


define('constCensusArgs', -1);
define('constCensusDupe', -2);
define('constCensusName', -3);

/*
    |  This is to maintain a census of machines which are logging to
    |  this server.  The census should be immediately and automatically
    |  updated whenever a new machine begins logging.
    */


function find_census_uuid($uuid, $db)
{
    $row = array();
    if ($uuid) {
        $qu  = safe_addslashes($uuid);
        $sql = "select * from"
            . " " . $GLOBALS['PREFIX'] . "core.Census where\n"
            . " uuid = '$qu'";
        $row = find_one($sql, $db);
    }
    return $row;
}


/*
    |  Create a site record.
    |  Every site has several records, one for its
    |  existance (username == '') and another for each
    |  user who is allowed to access it.
    */

function create_site($site, $user, $db)
{
    debug_note("create_site($site,$user,db)");
    $qs  = safe_addslashes($site);
    $qu  = safe_addslashes($user);
    $sql = "insert into\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers set\n"
        . " customer='$qs',\n"
        . " username='$qu'";
    command($sql, $db);
}


/*
    |  Create this site if it does not exist already.
    |  Admin users automatically get access to sites
    |  created this way.
    |
    |  We also attempt to assign ownership of new sites
    |  created this way.
    |
    |  1. If the server option site_owner is set, then
    |     the site owner user automatically gets ownership.
    |
    |  2. If a single admin account exists, other than the
    |     master_user (hfn), then he assumes ownership.
    |
    |  3. If only one account has access to the site, then
    |     it assumes ownership.
    */

function enter_site($site, $db)
{
    debug_note("enter_site($site,db)");
    $qs  = safe_addslashes($site);
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers\n"
        . " where username = ''\n"
        . " and customer = '$qs'";
    $row = find_one($sql, $db);
    $new = ($row) ? false : true;
    $num = 0;
    $user = '';
    if ($new) {
        create_site($site, '', $db);
        logs::log(__FILE__, __LINE__, "census: new site: $site", 0);
        $sql = "select * from " . $GLOBALS['PREFIX'] . "core.Users where priv_admin = 1";
        $res = command($sql, $db);
        if ($res) {
            $num = mysqli_num_rows($res);
            if ($num) {
                while ($row = mysqli_fetch_array($res)) {
                    $user = $row['username'];
                    create_site($site, $user, $db);
                    logs::log(__FILE__, __LINE__, "census: $user can access $site", 0);
                }
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
        $so = server_opt('site_owner', $db);
        if ($so) {
            $own = safe_addslashes($so);
            $sql = "update " . $GLOBALS['PREFIX'] . "core.Customers\n"
                . " set owner = 1\n"
                . " where username = '$own'\n"
                . " and customer = '$qs'";
            command($sql, $db);
        } else {
            $mu  = server_def('master_user', 'hfn', $db);
            $qu  = safe_addslashes($mu);
            $sql = "select * from " . $GLOBALS['PREFIX'] . "core.Customers\n"
                . " where customer = '$qs'\n"
                . " and username != '$qu'\n"
                . " and username != ''";
            $row = find_one($sql, $db);
            if ($row) {
                $id  = $row['id'];
                $sql = "update " . $GLOBALS['PREFIX'] . "core.Customers\n"
                    . " set owner = 1\n"
                    . " where id = $id";
                command($sql, $db);
            } else {
                if (($num == 1) and ($user != '')) {
                    $qu  = safe_addslashes($user);
                    $sql = "update " . $GLOBALS['PREFIX'] . "core.Customers\n"
                        . " set owner = 1\n"
                        . " where username = '$qu'\n"
                        . " and customer = '$qs'";
                    command($sql, $db);
                }
            }
        }
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers\n"
            . " where owner = 1\n"
            . " and customer = '$qs'";
        $row = find_one($sql, $db);
        if ($row) {
            $user = $row['username'];
            logs::log(__FILE__, __LINE__, "census: site $site owned by $user.", 0);
        }
    }
}


function census_log($site, $host, $uuid, $event)
{
    $text = "census: $host $event ($uuid) at $site.";
    logs::log(__FILE__, __LINE__, $text, 0);
    debug_note($text);
}

function census_duplicate($row)
{
    $id   = $row['id'];
    $site = $row['site'];
    $host = $row['host'];
    $uuid = $row['uuid'];
    $text = "census: $host duplicate $uuid ($id) at $site.";
    logs::log(__FILE__, __LINE__, $text, 0);
    debug_note($text);
}


function census_clone($row, $uuu)
{
    census_duplicate($row);
    census_duplicate($uuu);
}


function census_stamp($id, $db)
{
    $now = time();
    $sql = "update " . $GLOBALS['PREFIX'] . "core.Census set\n"
        . " last = $now\n"
        . " where id = $id";
    command($sql, $db);
}


/* census_manage

        If $uuid is not empty, updates the census for the machine uniquely
        identified by $site, $host, and $uuid.  If $uuid is empty, simply
        update the census for $site and $host.  Return the appropriate
        error code, otherwise if $legacy is TRUE then any new error codes
        will be changed to constErrServerTooBusy.
    */
function census_manage($site, $host, $uuid, $legacy, $db)
{
    $now = time();
    $err = constAppNoErr;
    if (!($uuid)) {
        /* Older RPCs never used an UUID, and they use the old protocol
                version as well, so we handle these specially...this mimics
                the way update_census used to work with no UUID. */
        $err = PHP_CORE_UpdateTimestamp(CUR, $site, $host);
        if ($err != constAppNoErr) {
            logs::log(__FILE__, __LINE__, 'census_manage: failed to update timestamp', 0);
        }
        $err = constAppNoErr;
    } else {
        $err = PHP_ALST_MakeAList(CUR, $machineID);
        if ($err != constAppNoErr) {
            logs::log(__FILE__, __LINE__, "dispatch: Failed to make alist: $err", 0);
            return $err;
        }
        $err = PHP_ALST_SetNamedItemString(
            CUR,
            $machineID,
            constInfoSite,
            $site
        );
        if ($err != constAppNoErr) {
            logs::log(__FILE__, __LINE__, "dispatch: Failed to set string: $err", 0);
            return $err;
        }
        $err = PHP_ALST_SetNamedItemString(
            CUR,
            $machineID,
            constInfoMachine,
            $host
        );
        if ($err != constAppNoErr) {
            logs::log(__FILE__, __LINE__, "dispatch: Failed to set string: $err", 0);
            return $err;
        }
        $err = PHP_ALST_SetNamedItemString(
            CUR,
            $machineID,
            constInfoUUID,
            $uuid
        );
        if ($err != constAppNoErr) {
            logs::log(__FILE__, __LINE__, "dispatch: Failed to set string: $err", 0);
            return $err;
        }
        $initGroups = 0;
        $err = PHP_CORE_UpdateCensus(CUR, $initGroups, $machineID);
        if ($err != constAppNoErr) {
            logs::log(__FILE__, __LINE__, "PHP_CORE_UpdateCensus returned $err for "
                . "machine " . $host . " site "
                . $site . " with uuid " . $uuid, 0);
        }

        if ($initGroups) {
            /* New machine, need to run groups_init. */
            groups_init($db, constGroupsInitBuildOnly);
        }

        /* This is a legacy client, so change the error code to
                constErrServerTooBusy */
        if ($legacy) {
            switch ($err) {
                case constAppNoErr:
                    break;
                case constErrServChangeUUID:
                    $err = constErrServerTooBusy;
                    break;
                case constErrServChangeName:
                    $err = constErrServerTooBusy;
                    break;
                default:
                    break;
            }
        }

        if ($err != constAppNoErr) {
            /* Return the error code as generated by PHP_CORE_UpdateCensus
                    and the logic above, not the code generated by
                    PHP_ALST_FreeEntireAList. */
            PHP_ALST_FreeEntireAList(CUR, $machineID);
            return $err;
        }
        $err = PHP_ALST_FreeEntireAList(CUR, $machineID);
        if ($err != constAppNoErr) {
            logs::log(__FILE__, __LINE__, "dispatch: Failed to free alist: $err", 0);
        }
    }
    return $err;
}
