<?php

include_once __DIR__ . "/../config.php";

function asi_info()
{
    return array(
        'dbhost' => getenv('DB_HOST') ?: '127.0.0.1',
        'dbport' => getenv('DB_PORT') ?: 3306,
        'dbuser' => getenv('DB_DATABASE') ?: 'weblog',
        'dbpass' => getenv('DB_PASSWORD') ?: 'b6Q4qT17xyfYJS9CJP2019#',

        'svdate' => '#VERSIONDATETIME#',
        'svvers' => '#VERSION1#',
        'db_cor' => 'core',
        'db_log' => 'event',
        'db_ast' => 'asset',
        'db_cfg' => 'siteman',
        'db_upd' => 'swupdate',
        'db_ins' => 'install',
        'db_prv' => 'provision',
        'db_pat' => 'softinst',
        'db_dsp' => 'display',
    );
}

function db_getrealserver($server)
{
    global $db_host;
    global $db_port;
    $realserver = $server;
    if ($server == '') {
        $host = $db_host;
        $port = $db_port;
        if ($host == '') {
            $realserver = '';
        } else {
            if ($port == '') {
                $port = '3306';
            }
            $realserver = $host . ':' . $port;
        }
    }
    return $realserver;
}

/*
|  Log into the database, allow the caller to do its own
|  error handling.  Do not generate a php warning if the
|  connect fails.
 */

function db_server($server)
{
    $inf = asi_info();
    if ($server == '') {
        return @($GLOBALS["___mysqli_ston"] = mysqli_connect($inf['dbhost'] . ':' . $inf['dbport'], $inf['dbuser'], $inf['dbpass']));
    } else {
        return @($GLOBALS["___mysqli_ston"] = mysqli_connect($server, $inf['dbuser'], $inf['dbpass']));
    }
}

function db_pconnect()
{
    return db_server('');
}

function db_debug($msg)
{
}

function db_exit($msg)
{
    db_debug("db_exit($msg)");
    logs::log(__FILE__, __LINE__, $msg, 0);
    exit("<p><b>$msg</b></p>");
}

/*
|   Switch to the specified database, or die trying.
 */

function db_change($dbname, $db)
{
    //  db_debug("db_change($dbname,$db)");
    if (!mysqli_select_db($db, $dbname)) {
        db_exit("Database $dbname is not available.");
    }
}

/*
|  Exits upon failure.
 */

function db_select($dbname)
{
    db_debug("db_select($dbname)");
    $db = db_pconnect();
    if ($db) {
        db_change($dbname, $db);
    } else {
        db_exit('Cannot access mysql database.');
    }

    return $db;
}

function db_connect()
{
    db_debug("db_connect()");
    $inf = asi_info();
    return db_select($GLOBALS['PREFIX'] . $inf['db_cor']);
}

function get_db_name($db)
{
    $db_name = '';

    $sql = "SELECT DATABASE() AS db_name";
    $res = mysqli_query($db, $sql);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        $db_name = $row['db_name'];
    } else {
        sqlerror($sql, $db);
    }
	$dbPrefix_val = getenv('DB_PREFIX');
	if( $dbPrefix_name )
	{
		if(!strstr($db_name, $dbPrefix_val))
		{
			$db_name = (getenv('DB_PREFIX') ?: '') . $db_name;
		}
}
	
    return $db_name;
}

function db_access($carr)
{
    $sites = '';
    $names = array();
    foreach ($carr as $cid => $site) {
        $name = safe_addslashes($site);
        $names[] = "'$name'";
    }
    if ($names) {
        $sites = implode(',', $names);
    }
    return $sites;
}

function site_array($auth, $filter, $db)
{
    $arr = array();
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers where\n"
        . " username = '$auth'";
    if ($filter) {
        $sql .= "\n and sitefilter = 1";
    }
    $sql .= "\n order by CONVERT(customer USING latin1)";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_assoc($res)) {
                $cid = $row['id'];
                $name = $row['customer'];
                $arr[$cid] = $name;
            }
        }
    }
    return $arr;
}

function site_array_exec($auth, $filter, $db, $site)
{
    $arr = array();
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers where\n"
        . " username = '$auth'"
        . "\n and customer in ('" . $site . "')";
    $sql .= "\n order by CONVERT(customer USING latin1)";

    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_assoc($res)) {
                $cid = $row['id'];
                $name = $row['customer'];
                $arr[$cid] = $name;
            }
        }
    }

    return $arr;
}

function site_array_exec_site($auth, $filter, $db, $sitenames)
{
    $arr = array();
    $sitenames = str_replace(",", "','", $sitenames);
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers where\n"
        . " username = '$auth'"
        . "\n and customer in ('" . $sitenames . "')";
    $sql .= "\n order by CONVERT(customer USING latin1)";

    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_assoc($res)) {
                $cid = $row['id'];
                $name = $row['customer'];
                $arr[$cid] = $name;
            }
        }
    }

    return $arr;
}

function affected($res, $db)
{
    return ($res) ? (mysqli_affected_rows($db)) : 0;
}

function inserted_id($db)
{
    if ($db instanceof PDO) {
        return $db->lastInsertId();
    }
    return mysqli_insert_id($db);
}

/*
|  mysql_pconnect returns false upon failure, leaving
|  an unhelpful empty warning in the php log file:
|
|  PHP Warning:   in /www/main/lib/l-db.php on line 699
|
 */

function db_code($code)
{
    $info = asi_info();
    $host = $info['dbhost'];
    $port = $info['dbport'];
    $user = $info['dbuser'];
    $pass = $info['dbpass'];
    $dbase = $info[$code];
    $good = 0;
    $aaaa = microtime();
    $db = @($GLOBALS["___mysqli_ston"] = mysqli_connect($host . ':' . $port, $user, $pass));
    $bbbb = microtime();
    $time = microtime_diff($aaaa, $bbbb);
    if ($time > 10) {
        $pid = getmypid();
        $secs = microtime_show($time);
        $text = "mysql: slow mysql_pconnect: p:$pid $secs";
        logs::log(__FILE__, __LINE__, $text, 0);
    }
    if ($db) {
        $aaaa = microtime();
        $good = mysqli_select_db($db, $dbase);
        $bbbb = microtime();
        $time = microtime_diff($aaaa, $bbbb);
        if ($time > 10) {
            $pid = getmypid();
            $secs = microtime_show($time);
            $text = "mysql: slow mysql_select($dbase): p:$pid $secs";
            logs::log(__FILE__, __LINE__, $text, 0);
        }
    }
    if (!$good) {
        $secs = microtime_show($time);
        $self = server_var('PHP_SELF');
        $args = server_var('QUERY_STRING');
        $name = ($args) ? "$self?$args" : $self;
        $text = "mysql: $dbase ($secs) $name mysql unavailable.";
        logs::log(__FILE__, __LINE__, $text, 0);
        $db = false;
    }
    return $db;
}

/*
|  Returns a list of customers that this user is allowed to access,
|  formatted so that you can use the result as part of your sql query.
|
|    $access = find_sites($username,0,$db);
|    $sql   .= " where customer in ($access)";
 */

function find_sites($username, $filter, $db)
{
    db_debug("find_sites($username,$filter,$db)");
    $carr = site_array($username, $filter, $db);
    return db_access($carr);
}

// This is a short-term solution.  Future: remove
function filtered_find_customers($username, $db)
{
    db_debug("filtered_find_customers($username,$db)");
    return find_sites($username, 1, $db);
}

// This is a short-term solution.  Future: remove
function find_customers($username, $db)
{
    db_debug("find_customers($username,$db)");
    return find_sites($username, 0, $db);
}

/*
|  from php.net.
|
|  gordon at kanazawa-gu dot ac dot jp  28-Dec-2002 03:29
 */

function microtime_diff($a, $b)
{
    list($a_dec, $a_sec) = explode(' ', $a);
    list($b_dec, $b_sec) = explode(' ', $b);
    return ($b_sec - $a_sec) + ($b_dec - $a_dec);
}

/*
|  show an elapsed time in some reasonable format.
|
|  Moved here Oct 31, 2003 ... wanted to use it
|  inside the rpc code.
 */

function microtime_show($time)
{
    $text = '0';
    if (100 <= $time) {
        $nn = round($time);
        $ss = intval($nn % 60);
        $nn = intval($nn / 60);
        $mm = intval($nn % 60);
        $hh = intval($nn / 60);
        if ($hh) {
            $text = sprintf('%d:%02d:%02d', $hh, $mm, $ss);
        } else {
            $text = sprintf('%d:%02d', $mm, $ss);
        }
    }
    if ((5 <= $time) && ($time < 100)) {
        $nn = round($time * 1000);
        $ss = intval($nn / 1000);
        $dd = intval($nn % 1000);
        $text = sprintf('%d.%03d seconds', $ss, $dd);
    }
    if ((0 < $time) && ($time < 5)) {
        $usec = round($time * 1000000);
        $msec = intval($usec / 1000);
        if ($usec <= 9999) {
            $text = "$usec &micro;sec";
        } else {
            $text = "$msec msec";
        }
    }
    if ($time < 0) {
        $text = microtime_show(-$time);
        $text = "-$text";
    }
    return $text;
}

function find_tables($dbname, $db)
{
    $tables = array();
    $res = mysqli_query($GLOBALS["___mysqli_ston"], "SHOW TABLES FROM $dbname");
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $tables[$row[0]] = 1;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $tables;
}

if (!function_exists('pdo_connect')) {
    function pdo_connect($dataBase = null)
    {
        return NanoDB::connect($dataBase);
    }
}
