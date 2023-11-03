<?php

/*
Revision history:

Date        Who     What
----        ---     ----
14-Nov-03   EWB     Created
21-Nov-03   EWB     Initial testing.
24-Nov-03   EWB     New arguments
25-Nov-03   EWB     Special case uuid logging.
 4-Feb-04   EWB     process-aware meter logging.
 6-Feb-04   EWB     product/owner meter logging.
12-Feb-04   EWB     Added product owner to audit logging.
12-Feb-04   EWB     Changed args to PROV_SetKeys, PROV_GetKeys.
13-Feb-04   EWB     Comment out some debugging.
13-Feb-04   EWB     Fixed a bug in setkeys.
20-Feb-04   EWB     Track CryptKey usage.
16-Mar-04   EWB     Comment out logging for meter events.
23-Mar-04   EWB     Config does UUID fixup, not needed here any more.
27-Apr-04   EWB     find_many migrates to server.php.
25-May-04   EWB     Everybody updates the census.
21-Mar-05   EWB     UUID Checking Report Meter RPC.
23-Mar-05   EWB     Common Census Code.
31-Oct-05   BTE     Removed unused RPC calls.
10-Nov-05   BTE     Updated to use the new census_manage function.
24-Mar-06   BTE     Fixed undefined variable warning.
15-May-06   BTE     Bug 2967: Handle duplicate UUIDs and remove migration for
                    pre-2.2 clients.

*/

define('constInfoUserName',   'username');
define('constInfoClientTime', 'clienttime');
define('constInfoAction',     'action');
define('constInfoProdName',   'prodname');
define('constInfoExeName',    'exename');
define('constInfoEventType',  'eventtype');
define('constInfoProcessID',  'processid');
define('constInfoProdOwner',  'prodowner');

define('constProvEncrypt',   'encrypt');
define('constProvDecrypt',   'decrypt');
define('constProvMethod',    'method');

define('constProcessCompletion', 0);
define('constProcessCreation',   1);
define('constProcessLife',       2);


function quote($s)
{
    $tmp = safe_addslashes($s);
    return "'$tmp'";
}


function set_string(&$row, $name, &$valu)
{
    if (isset($row[$name])) {
        $valu = quote($row[$name]);
    }
}

function set_integer(&$row, $name, &$valu)
{
    if (isset($row[$name])) {
        $valu = intval($row[$name]);
    }
}

/*
    |  Finds the product id from the specified name and owner.
    |  We expect there to be exactly one of these.
    |  Returns zero for any kind of error.
    */

function find_product_id($name, $user, $db)
{
    $pid  = 0;
    if (($name) && ($user)) {
        $qn  = safe_addslashes($name);
        $qu  = safe_addslashes($user);
        $sql = "select productid from\n"
            . " Products where\n"
            . " username = '$qu' and\n"
            . " prodname = '$qn'";
        $row = find_one($sql, $db);
        $pid = ($row) ? $row['productid'] : 0;
    }

    //  $txt  = "find_product_id($name,$user): $pid";
    //  logs::log(__FILE__, __LINE__, $txt,0);

    return $pid;
}


function find_key_pid($pid, $uuid, $update, $db)
{
    $row = array();
    if (($pid > 0) && ($uuid)) {
        $qu  = safe_addslashes($uuid);
        $sql = "select * from CryptKeys where\n"
            . " uuid = '$qu' and\n"
            . " productid = $pid";
        $row = find_one($sql, $db);
    }
    if (($row) && ($update)) {
        $now = time();
        $cid = $row['cryptid'];
        $sql = "update CryptKeys set\n"
            . " lastuse = $now,\n"
            . " access=access+1\n"
            . " where cryptid = $cid";
        command($sql, $db);
    }

    //  $res  = ($row)? 'success' : 'failure';
    //  $txt  = "find_key_pid($pid,$uuid,$update) $res";
    //  logs::log(__FILE__, __LINE__, $txt,0);

    return $row;
}


function find_key($name, $user, $uuid, $db)
{
    $key = array();
    $pid = find_product_id($name, $user, $db);
    $row = find_key_pid($pid, $uuid, 1, $db);
    if ($row) {
        $key[constInfoProdOwner] = $user;
        $key[constInfoProdName]  = $name;
        $key[constProvMethod]    = $row['method'];
        $key[constProvEncrypt]   = $row['encryptkey'];
        $key[constProvDecrypt]   = $row['decryptkey'];
    }

    //  $res = ($key)? 'success' : 'failure';
    //  $txt = "find_key($name,$user,$uuid) $res";
    //  logs::log(__FILE__, __LINE__, $txt,0);

    return $key;
}


function census_common($site, $host, $uuid, &$rval, &$revl, $runUpdate, $db)
{
    debug_note("census_common($site,$host,$uuid,$rval)");
    $temp = constAppNoErr;
    if ($runUpdate == 1) {
        $temp = census_manage($site, $host, $uuid, 1, $db);
    }
    if ($temp == constAppNoErr) {
        $revl = find_revl_uuid($uuid, $db);
    } else {
        $rval = $temp;
    }
}


function setkey_common(&$args)
{
    $host = &$args['valu'][1];
    $site = &$args['valu'][2];
    $uuid = &$args['valu'][3];
    $alst = &$args['valu'][4];
    $code = &$args['valu'][5];

    $keys = fully_parse_alist($alst);
    $rval = constErrDatabaseNotAvailable;
    $revl = array();
    $db   = db_code('db_prv');

    if ($db) {
        census_common(
            $site,
            $host,
            $uuid,
            $rval,
            $revl,
            $args['update_census'],
            $db
        );
    }

    $asize = strlen($alst);
    $usize = safe_count($keys);
    $wsize = 0;
    if (($keys) && ($revl)) {
        $now    = time();
        $none   = quote('');
        $unique = quote($uuid);
        foreach ($keys as $k => $row) {
            $pid  = 0;
            $key  = array();
            $name = @strval($row[constInfoProdName]);
            $user = @strval($row[constInfoProdOwner]);
            if (($name) && ($user)) {
                $pid = find_product_id($name, $user, $db);
                $key = find_key_pid($pid, $uuid, 0, $db);
            }

            if (($pid) && (!$key)) {
                $encode = $none;
                $decode = $none;
                $method = $none;

                set_string($row, constProvEncrypt, $encode);
                set_string($row, constProvDecrypt, $decode);
                set_string($row, constProvMethod,  $method);

                $sql = "insert into CryptKeys set\n"
                    . " productid = $pid,\n"
                    . " created = $now,\n"
                    . " uuid = $unique,\n"
                    . " method = $method,\n"
                    . " encryptkey = $encode,\n"
                    . " decryptkey = $decode";
                $res = command($sql, $db);
                if (affected($res, $db)) {
                    $acts = 'key added';
                    $wsize++;
                } else {
                    $acts = 'database failure';
                }
            } else {
                if ($key)
                    $acts = 'key exists already';
                else
                    $acts = 'not found';
            }
            $text = "setkey: $host '$name' $acts";
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        }
    }

    $msec = microtime_diff($args['usec'], microtime());
    $secs = microtime_show($msec);
    if ($wsize == $usize) {
        $rval = constAppNoErr;
        $acts = 'logging';
    } else {
        $acts = 'failure';
    }

    $stat = "u:$usize,w:$wsize,a:$asize,r:$rval";
    $text = "setkey: $host $acts ($stat) in $secs at $site";
    logs::log(__FILE__, __LINE__, $text, 0);
    debug_note($text);

    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = $rval;

    // $args returned to caller by reference
}



function getkey_common(&$args)
{
    $list = &$args['valu'][1];
    $host = &$args['valu'][2];
    $site = &$args['valu'][3];
    $uuid = &$args['valu'][4];
    $alst = &$args['valu'][5];
    $code = &$args['valu'][6];

    $rval = constErrDatabaseNotAvailable;
    $keys = array();
    $revl = array();
    $db   = db_code('db_prv');

    if (($db) && ($site) && ($host) && ($uuid)) {
        census_common(
            $site,
            $host,
            $uuid,
            $rval,
            $revl,
            $args['updatecensus'],
            $db
        );
    }

    $alen = strlen($alst);
    $prds = fully_parse_alist($alst);

    if (($revl) && ($prds)) {
        reset($prds);
        foreach ($prds as $k => $row) {
            $key  = array();
            $name = @strval($row[constInfoProdName]);
            $user = @strval($row[constInfoProdOwner]);
            $key  = find_key($name, $user, $uuid, $db);
            if ($key) {
                $keys[] = $key;
                $status = 'key found';
            } else {
                if (($name) && ($user)) {
                    $status = 'key missing';
                } else {
                    $status = 'garbage';
                }
            }
            $text = "getkey: $host $status for $name at $site.";
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        }
    }

    $usize = safe_count($prds);  // upload
    $dsize = safe_count($keys);  // download

    $args['valu'][1] = fully_make_alist($keys);

    if (($db) && ($revl)) {
        if ($dsize == $usize) {
            $rval = constAppNoErr;
        } else {
            $rval = constErrNotEncrypted;
        }
    }

    $stat = "u:$usize,d:$dsize,l:$alen,r:$rval";
    $msec = microtime_diff($args['usec'], microtime());
    $secs = microtime_show($msec);
    $text = "getkey: $host ($stat) in $secs at $site.";
    logs::log(__FILE__, __LINE__, $text, 0);
    debug_note($text);

    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = $rval;

    // $args returned to caller by reference
}


function audit_common(&$args)
{
    $now  =  time();
    $host = &$args['valu'][1];
    $site = &$args['valu'][2];
    $uuid = &$args['valu'][3];
    $alst = &$args['valu'][4];
    $code = &$args['valu'][5];

    $db   = false;
    $alen = strlen($alst);
    $list = fully_parse_alist($alst);
    $rval = constErrDatabaseNotAvailable;
    $vals = array();
    $revl = array();

    $success = false;
    $usize   = 0;
    $wsize   = 0;

    if ($list) {
        $servertime = $now;
        $clienttime = $now;

        $none     = quote('');
        $owner    = $none;
        $product  = $none;
        $username = $none;
        $action   = $none;
        $machine  = quote($host);
        $sitename = quote($site);
        $unique   = quote($uuid);
        $usize    = safe_count($list);
        $who      = 0;

        reset($list);
        foreach ($list as $key => $row) {
            set_integer($row, constInfoClientTime, $clienttime);

            set_string($row, constInfoSite, $sitename);
            set_string($row, constInfoMachine, $machine);
            set_string($row, constInfoUUID, $unique);
            set_string($row, constInfoProdName, $product);
            set_string($row, constInfoProdOwner, $owner);
            set_string($row, constInfoUserName, $username);
            set_string($row, constInfoAction, $action);

            $a = array(
                0, $who, $servertime, $clienttime,
                $sitename, $machine, $unique, $product,
                $owner, $username, $action
            );

            $vals[] = '(' . join(',', $a) . ')';
        }
    }

    if ($vals) {
        $db = db_code('db_prv');

        if ($db) {
            census_common(
                $site,
                $host,
                $uuid,
                $rval,
                $revl,
                $args['updatecensus'],
                $db
            );
        }
    }

    /*
        |  This will process the entire list with just
        |  a single insert.
        */

    if (($db) && ($revl) && ($vals)) {
        $sql = "insert into Audit\n values\n " . join(",\n ", $vals);
        $res = command($sql, $db);
        $num = affected($res, $db);
        if ($num > 0) {
            $wsize   = $num;
            $success = true;
        }
    }

    $msec = microtime_diff($args['usec'], microtime());
    $secs = microtime_show($msec);
    if ($success) {
        $rval = constAppNoErr;
        $acts = 'logging';
    } else {
        $acts = 'failure';
    }

    $stat = "u:$usize,w:$wsize,l:$alen,r:$rval";
    $text = "audit: $host $acts ($stat) in $secs at $site.";
    logs::log(__FILE__, __LINE__, $text, 0);
    debug_note($text);

    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = $rval;

    // $args returned to caller by reference
}


/*
    |  On an active server, this probably produces too much
    |  output to be useful.  However, I'd like to keep it
    |  around so we can turn it on for debugging.
    */

function meter_log($msg)
{
    //     logs::log(__FILE__, __LINE__, $msg,0);
    debug_note($msg);
}


/*
    |  This function takes a process completion event
    |  and attempts to find a matching process creation
    |  event.
    |
    |  The matching creation event must of course be
    |  on the same machine as the completion event, and
    |  share the same exename, processid, and username.
    |
    |  Note that windows can re-use the same processid
    |  if a program gets restarted.  In that case we want
    |  to be sure to use the most recent process creation
    |  event not later than our completion event.
    */

function meter_seek(&$row, $db)
{
    $type = constProcessCreation;
    $life = constProcessLife;
    $smax = $row['servermax'];
    $cmax = $row['clientmax'];
    $uuid = safe_addslashes($row['uuid']);
    $name = safe_addslashes($row['exename']);
    $user = safe_addslashes($row['username']);
    $pid  = safe_addslashes($row['processid']);

    $sql = "select * from Meter where\n"
        . " eventtype = $type and\n"
        . " uuid = '$uuid' and\n"
        . " exename = '$name' and\n"
        . " processid = '$pid' and\n"
        . " username = '$user' and\n"
        . " clienttime <= $cmax\n"
        . " order by clienttime desc\n"
        . " limit 1";
    $old = $row['meterid'];
    $met = find_one($sql, $db);
    if ($met) {
        $new  = $met['meterid'];
        $cmin = $met['clienttime'];
        $sql  = "update Meter set\n"
            . " eventtype = $life,\n"
            . " clientmax = $cmax,\n"
            . " servermax = $smax\n"
            . " where meterid = $new";
        $res  = command($sql, $db);
        if (affected($res, $db) == 1) {
            // success ... we don't really need the
            // debug information, we should remove
            // this later.

            $host = $row['machine'];
            $name = $row['exename'];
            $site = $row['sitename'];
            $secs = $cmax - $cmin;
            $cmin = date('H:i:s', $cmin);
            $cmax = date('H:i:s', $cmax);
            $txt  = "meter: $host at $site, $name (m:$new) $secs ($cmin to $cmax)";
            meter_log($txt);

            $sql = "delete from Meter\n"
                . " where meterid = $old";
            command($sql, $db);
        }
    } else {
        // the client says a process has completed,
        // however we have no record of it starting.

        $host = $row['machine'];
        $name = $row['exename'];
        $site = $row['sitename'];
        $pid  = $row['processid'];
        $cmax = date('m/d H:i:s', $cmax);
        $txt  = "meter: $host at $site, $name (m:$old) $cmax never started";
        meter_log($txt);
    }
}


/*
    |  Currently this just looks at the most
    |  recently inserted records ... however
    |  it can do a full search if you comment
    |  out the second line.
    */

function meter_process($now, $db)
{
    $type = constProcessCompletion;
    $sql  = "select * from Meter\n"
        . " where eventtype = $type"
        . " and\n servertime = $now";
    $list = find_many($sql, $db);
    if ($list) {
        reset($list);
        foreach ($list as $key => $row) {
            meter_seek($row, $db);
        }
    }
}


function meter_common(&$args)
{
    $host = &$args['valu'][1];
    $site = &$args['valu'][2];
    $uuid = &$args['valu'][3];
    $alst = &$args['valu'][4];
    $code = &$args['valu'][5];

    $db   = false;
    $now  = time();
    $alen = strlen($alst);
    $list = fully_parse_alist($alst);
    $rval = constErrDatabaseNotAvailable;
    $vals = array();
    $revl = array();

    $complete = 0;
    $success  = false;
    $usize = 0;
    $wsize = 0;

    if ($list) {
        $usize = safe_count($list);

        $servertime = $now;
        $clienttime = $now;
        $eventtype  = 0;
        $none       = quote('');
        $username   = $none;
        $processid  = $none;
        $exename    = $none;
        $owner      = $none;
        $product    = $none;
        $action     = $none;
        $sitename   = quote($site);
        $machine    = quote($host);
        $unique     = quote($uuid);

        reset($list);
        foreach ($list as $key => $row) {
            $servermax = 0;
            $clientmax = 0;
            set_integer($row, constInfoClientTime, $clienttime);
            set_integer($row, constInfoEventType, $eventtype);

            set_string($row, constInfoSite, $sitename);
            set_string($row, constInfoMachine, $machine);
            set_string($row, constInfoUUID, $unique);
            set_string($row, constInfoExeName, $exename);
            set_string($row, constInfoProcessID, $processid);
            set_string($row, constInfoUserName, $username);
            set_string($row, constInfoAction, $action);
            set_string($row, constInfoProdOwner, $owner);
            set_string($row, constInfoProdName, $product);
            if ($eventtype == constProcessCompletion) {
                $complete++;
                $servermax = $servertime;
                $clientmax = $clienttime;
            }

            $a = array(
                0, $clienttime, $servertime,
                $clientmax, $servermax,
                $eventtype, $exename, $processid,
                $sitename, $machine, $unique,
                $username, $owner, $product
            );

            $vals[] = '(' . join(',', $a) . ')';
        }
    }

    if ($vals) {
        $db = db_code('db_prv');

        if ($db) {
            census_common(
                $site,
                $host,
                $uuid,
                $rval,
                $revl,
                $args['updatecensus'],
                $db
            );
        }
    }

    /*
        |  This will process the entire list with just
        |  a single insert.  If it turns out we have
        |  process completion events then we will go
        |  back and attempt to match them.
        */

    if (($db) && ($revl) && ($vals)) {
        $sql = "insert into Meter\n values\n " . join(",\n ", $vals);
        $res = command($sql, $db);
        $num = affected($res, $db);
        if ($num > 0) {
            $wsize   = $num;
            $success = true;
            if ($complete) {
                meter_process($now, $db);
            }
        }
    }

    $msec = microtime_diff($args['usec'], microtime());
    $secs = microtime_show($msec);
    if ($success) {
        $rval = constAppNoErr;
        $acts = 'logging';
    } else {
        $acts = 'failure';
    }

    $stat = "u:$usize,w:$wsize,l:$alen,r:$rval";
    $text = "meter: $host $acts ($stat) in $secs at $site.";
    meter_log($text);

    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = $rval;

    // $args returned to caller by reference
}

function PROV_ReportMeter(&$args)
{
    $args['valu'][5] = 0;
    meter_common($args);
}

function PROV_ReportAudit(&$args)
{
    $args['valu'][5] = 0;
    audit_common($args);
}

function PROV_SetKeys(&$args)
{
    $args['valu'][5] = 0;
    setkey_common($args);
}

function PROV_GetKeys(&$args)
{
    $args['valu'][6] = 0;
    getkey_common($args);
}
