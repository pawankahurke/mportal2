<?php

/*
Revision history:

Date        Who     What
----        ---     ----
07-Oct-02   NL      Original creation
22-Oct-02   NL      Rewrite URL to include username & password
25-Oct-02   NL      Changed passed-in NULLs to falses;
25-Oct-02   NL      If machine does not exist, id=mysql_insert_id, force=0;
25-Oct-02   NL      Added debug flag & messages;
25-Oct-02   NL      Change (mysqli_num_rows($res) == 0) to (mysqli_num_rows($res) > 0)
25-Oct-02   NL      Separated "if ($res) && (mysqli_num_rows($res) > 0)" so
                        new site/machine records dont get created when $res = false;
2-Dec-02    NL      add_UpdateSites cant have empty sitename
2-Dec-02    NL      add_UpdateMachines cant have empyt sitename or machine
21-Dec-02   AAM     Fixed "debugging" part to be for production server.
20-May-03   EWB     Sigh ... yet more quoting issues.
20-May-03   EWB     Log machine and site introductions.
 3-Nov-03   EWB     Reduce memory usage, more extensive logging.
23-Mar-04   EWB     Directly calculate affected rows.
29-Mar-04   EWB     Don't upgrade if client is already later than target version.
31-Mar-04   EWB     UpdateSites.version now selects Downloads.name.
 1-Apr-04   EWB     Better logging to make update testing easier.
13-Apr-04   EWB     Don't complain unspecified target is missing.
25-May-04   EWB     Everybody updates the census.
17-Sep-04   EWB     'force' is a reserved word in mysql 4.
12-Oct-04   EWB     case-insensitive version comparisions.
23-Mar-05   EWB     New census logging, record uuid
31-Oct-05   BTE     Removed EXEC_LogUpdate.
10-Nov-05   BTE     Updated to use the new census_manage function.
18-Nov-05   BTE     Fixed an undefined variable.
15-May-06   BTE     Bug 2967: Handle duplicate UUIDs and remove migration for
                    pre-2.2 clients.
07-Apr-15   BTE     Added EXEC_QueryProfileUpdate.

*/


/*
 | SQL FUNCTIONS
 */

function add_UpdateSites($site, $version, $db)
{
    $success = 0;
    if ($site) {
        $qs  = safe_addslashes($site);
        $sql = "insert into UpdateSites set\n"
            . " sitename = '$qs',\n"
            . " version = '$version'";
        $res = command($sql, $db);
        $success = affected($res, $db);
    }
    return $success;
}


function find_updatesite($site, $db)
{
    $row = array();
    if ($site) {
        $qs  = safe_addslashes($site);
        $sql = "select * from UpdateSites\n"
            . " where sitename = '$qs'";
        $row = find_one($sql, $db);
        if ($row) {
            debug_note("site $site EXISTS");
        }
    }
    return $row;
}


function find_updatehost($site, $host, $db)
{
    $row = array();
    if (($site) && ($host)) {
        $qs  = safe_addslashes($site);
        $qh  = safe_addslashes($host);
        $sql = "select * from UpdateMachines\n"
            . " where machine = '$qh'\n"
            . " and sitename = '$qs'";
        $row = find_one($sql, $db);
        if ($row) {
            debug_note("machine $host at $site exists");
        }
    }
    return $row;
}


/*
 |  3/31/04: changed to make downloads not site specific.  We now select
 |  a download record based on its name alone, which is required to be
 |  unique.  At update time we revise all existing Download.name
 |  and UpdateSites.version to:
 |
 |     $version/$site
 */

function find_download($name, $db)
{
    $row = array();
    if ($name) {
        $qn  = safe_addslashes($name);
        $sql = "select * from Downloads\n"
            . " where name = '$qn'";
        $row = find_one($sql, $db);
        if ($row) {
            debug_note("download $name exists");
        }
    }
    return $row;
}



/*
 |  3/23/05: We are now recording the UUID of the machine
 |  whenever it is available, however nothing is really
 |  using it yet.
 */

function add_UpdateMachines($site, $host, $uuid, $now, $vers, $db)
{
    $num = 0;
    if (($site) && ($host)) {
        $qs  = safe_addslashes($site);
        $qh  = safe_addslashes($host);
        $qu  = safe_addslashes($uuid);
        $qv  = safe_addslashes($vers);
        $sql = "INSERT INTO UpdateMachines SET\n"
            . " sitename = '$qs',\n"
            . " machine = '$qh',\n"
            . " uuid = '$qu',\n"
            . " timecontact = $now,\n"
            . " lastversion = '$qv'";
        $res = command($sql, $db);
        if (affected($res, $db)) {
            $num = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
            debug_note("created record $num");
        }
    }
    return $num;
}


function touch_UpdateMachines($timecontact, $lastversion, $id, $db)
{
    $num = 0;
    if ($id > 0) {
        $sql = "UPDATE UpdateMachines SET\n"
            . " timecontact = $timecontact,\n"
            . " lastversion = '$lastversion'\n"
            . " WHERE id = $id";
        $res = command($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}


function update_UpdateMachines(
    $timeupdate,
    $oldversion,
    $newversion,
    $wasforced,
    $force,
    $id,
    $db
) {
    $num = 0;
    if ($id > 0) {
        $sql = "UPDATE UpdateMachines SET\n"
            . " timeupdate = $timeupdate,\n"
            . " oldversion = '$oldversion',\n"
            . " newversion = '$newversion',\n"
            . " wasforced = $wasforced,\n"
            . " doforce = $force\n"
            . " WHERE id = $id";
        $res = command($sql, $db);
        $num = affected($res, $db);
    }

    return $num;
}


/*
 | RESPONSE (ACTION CODE) FUNCTIONS
 | (broken out just to have useful function names for easier code reading)
 */

function no_update(&$args)
{
    $args['valu'][1] = false;    // cmdline
    $args['valu'][2] = false;    // url
    $args['valu'][3] = false;    // fName
    $args['valu'][4] = false;    // target
    $args['valu'][5] = 0;        // response
}

function normal_update(&$args, $cmdline, $url, $fName, $target)
{
    $args['valu'][1] = $cmdline; // cmdline
    $args['valu'][2] = $url;    // url
    $args['valu'][3] = $fName;  // fName
    $args['valu'][4] = $target; // target
    $args['valu'][5] = 1;       // response
}

function force_update(&$args, $cmdline, $url, $fName, $target)
{
    $args['valu'][1] = $cmdline; // cmdline
    $args['valu'][2] = $url;    // url
    $args['valu'][3] = $fName;  // fName
    $args['valu'][4] = $target; // target
    $args['valu'][5] = 4;       // response
}



/*
 |
 | EXEC_QueryUpdate: main function called by RPC code (server.php/dispatch()).
 |
 | Checks whether machine is due for an update, returns response in $valu[5]:
 |   0: no update
 |   1: normal update
 |   4: force update
 |
 | If site is not listed in Site Updates, adds site.
 |
 | Updates UpdateMachines table with times, versions, force info, etc.
 |
 | Returns values from the Downloads table, in the array $valu (stored in $args['valu']).
 |
 | valu array:
 |  RETURNS:
 |   valu[1]: cmdline
 |   valu[2]: url
 |   valu[3]: fName
 |   valu[4]: target
 |   valu[5]: response
 |  INPUTS:
 |   valu[6]: version
 |   valu[7]: host
 |   valu[8]: site
 |   valu[9]: uuid
 |   valu[10]: code
 |
 */

function update_common(&$args)
{
    $now  =  time();
    $vers = &$args['valu'][6];
    $host = &$args['valu'][7];
    $site = &$args['valu'][8];
    $uuid = &$args['valu'][9];
    $code = &$args['valu'][10];
    $usec = &$args['usec'];

    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = constAppNoErr;

    $db = db_code('db_upd');

    if (!$db) {
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $text = "update: $host -- mysql failure in $secs at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
        no_update($args);
        $args['rval'] = constErrDatabaseNotAvailable;
        return;
    }

    if ((!$host) || (!$site)) {
        // client sent empty machine, cust values
        no_update($args);
        return;
    }

    $rval = constAppNoErr;
    if ($args['updatecensus'] == 1) {
        $rval = census_manage($site, $host, $uuid, 1, $db);
    }

    /*
    |  If the census code detected a duplicate
    |  uuid we should immediately return the 
    |  error code without any further processing.
    */

    if ($rval != constAppNoErr) {
        no_update($args);
        $args['rval'] = $rval;
        return;
    }

    /*
    |  Check to see if the site exists yet.  If not
    |  we'll insert the new site and machine, then
    |  go away without doing anything else.
    */

    $row = find_updatesite($site, $db);
    if (!$row) {
        debug_note("site $site does not exist");
        add_UpdateSites($site, '', $db);
        $text = "update: $host introduces new site $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);

        add_UpdateMachines($site, $host, $uuid, $now, $vers, $db);
        no_update($args);

        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $text = "update: introduce $host ($vers) in $secs at new site $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
        return;
    }

    debug_note("site $site exists");
    $name  = $row['version'];
    $force = 0;

    /*
    |  Check to see if the machine exists yet.  If so,
    |  we'll update our last contact time.  Otherwise
    |  we'll insert the new record.
    */

    $row = find_updatehost($site, $host, $db);
    if ($row) {
        debug_note("machine $host at $site exists");

        $id    = $row['id'];
        $force = @intval($row['doforce']);

        touch_UpdateMachines($now, $vers, $id, $db);
    } else {
        $id   = add_UpdateMachines($site, $host, $uuid, $now, $vers, $db);
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        if ($id) {
            $text = "update: $host introduced ($vers) in $secs at $site.";
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        } else {
            $text = "update: $host failed introduction ($vers) in $secs at new site $site.";
            no_update($args);
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
            return;
        }
    }

    $dn = find_download($name, $db);

    if (!$dn) {
        if ($name != '') {
            $text = "update: $host download $name does not exist for $site.";
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        }
        no_update($args);
        return;
    }

    debug_note("download $name exists");
    $cmdline    = @$dn['cmdline'];
    $url        = @$dn['url'];
    $version    = @$dn['version'];
    $username   = @trim($dn['username']);
    $password   = @trim($dn['password']);

    // Rewrite URL
    if (($username != '') && ($url != '')) {
        if (strstr($url, '@')) {
            $url = preg_replace("/\/\/.*@/", "//$username:$password@", $url);
        } else {
            $url = preg_replace("/\/\//", "//$username:$password@", $url);
        }
    }


    // Is force set?
    debug_note("force: $force");
    if ($force) {
        // force is SET
        debug_note("force is SET");
        update_UpdateMachines($now, $vers, $version, 1, 0, $id, $db);
        force_update($args, $cmdline, $url, false, false);

        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $text = "update: $host force update from $vers to $version in $secs at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
        return;
    }

    // force is NOT SET
    debug_note("force is NOT SET");

    /*
    |     vers: from client.
    |  version: from server.
    |
    |  We want to do an update only if the client
    |  version is less than the target version.
    |
    |  12-Oct-04: EWB Case insensitive version comparisons.
    */

    $cmp = strnatcasecmp($version, $vers);
    if ($cmp <= 0) {
        // $version <= $vers, client is already current
        $state = ($cmp == 0) ? 'at' : 'later than';
        debug_note("client is already $state target version");
        no_update($args);
        if ($cmp < 0) {
            $msec = microtime_diff($usec, microtime());
            $secs = microtime_show($msec);
            $text = "update: $host skipping update ($version < $vers) in $secs at $site.";
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        }
    } else {
        // $version > $vers, client must be updated.
        debug_note("client needs to be updated");
        update_UpdateMachines($now, $vers, $version, 0, 0, $id, $db);

        normal_update($args, $cmdline, $url, false, false);
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $text = "update: $host normal update from $vers to $version in $secs at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    }

    // args returned to caller by reference
}

function EXEC_QueryUpdate(&$args)
{
    $args['valu'][9] = '';
    $args['valu'][10] = 0;
    update_common($args);
}


/*
 |
 | EXEC_QueryProfileUpdate
 |
 | Checks whether the provided machine should run a group sync and rather there's
 | a profile url for the given machine.  We may change this to just return the
 | URL prefix and the specific mgroupuniq the machine should take the profile
 | from but for now I think this will work.
 | valu array:
 |  RETURNS:
 |   valu[1]: runGroupSync (boolean)
 |   valu[2]: profile url (string)
 |  INPUTS:
 |   valu[3]: host
 |   valu[4]: site
 |
 */

function EXEC_QueryProfileUpdate(&$args)
{
    $now  =  time();
    $host = &$args['valu'][3];
    $site = &$args['valu'][4];
    $maxUpdated = &$args['valu'][5];
    $uuid = &$args['valu'][6];
    $code = &$args['valu'][7];
    $usec = &$args['usec'];

    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = constAppNoErr;

    $db = db_code('db_cor');

    if (!$db) {
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $text = "profile update: $host -- mysql failure in $secs at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
        $args['valu'][1] = false;
        $args['valu'][2] = false;
        $args['rval'] = constErrDatabaseNotAvailable;
        return;
    }

    if ((!$host) || (!$site)) {
        // client sent empty machine, cust values
        $args['valu'][1] = false;
        $args['valu'][2] = false;
        return;
    }

    $rval = constAppNoErr;
    if ($args['updatecensus'] == 1) {
        $rval = census_manage($site, $host, $uuid, 1, $db);
    }

    /*
    |  If the census code detected a duplicate
    |  uuid we should immediately return the 
    |  error code without any further processing.
    */

    if ($rval != constAppNoErr) {
        $args['valu'][1] = false;
        $args['valu'][2] = false;
        $args['rval'] = $rval;
        return;
    }

    $census = find_census_name($site, $host, $db);
    if (!$census) {
        $args['valu'][1] = false;
        $args['valu'][2] = false;
        $args['rval'] = constErrDatabaseNotAvailable;
        return;
    }

    //First we need to see if the groups have changed for this machine
    //since the last sync - we get the max machine group map timestamp
    //for this purpose and we need to compare it with our table
    $args['valu'][1] = 0;
    $sql = "SELECT MAX(updated) AS updated FROM MachineGroupMap WHERE censusuniq='"
        . $census['censusuniq'] . "'";
    $row = find_one($sql, $db);
    if (($row) && ($maxUpdated < $row['updated'])) {
        $args['valu'][1] = 1;
    }

    $args['valu'][2] = false;
    if ($census['profgrp']) {
        $returl = server_opt('cdn_profile_prefix', $db);
        $sql = 'SELECT category,name FROM MachineGroups LEFT JOIN MachineCategories ON ('
            . 'MachineGroups.mcatuniq=MachineCategories.mcatuniq) WHERE mgroupuniq=\''
            . $census['profgrp'] . '\'';
        $row = find_one($sql, $db);
        if ($row) {
            $args['valu'][2] = $returl . urlencode($row['category']) . '/'
                . urlencode($row['name']) . '/profile.db';
        }
    }

    $upgrp = $args['valu'][1] ? 'update groups' : 'not update groups';
    $text = "profile update: $host at $site found url " . $args['valu'][2] . " and was told to "
        . $upgrp;
    logs::log(__FILE__, __LINE__, $text, 0);

    // args returned to caller by reference
}

/* Don't delete the newline from the end of this file */
