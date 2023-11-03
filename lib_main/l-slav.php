<?php





function get_mysql_variable($name, $db)
{
    $valu = '';
    $sql = "show variables like '$name'";
    $res = command($sql, $db);
    $row = array();
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
        }
    }
    if ($row) {
        $valu = $row['Value'];
    }
    return $valu;
}



function db_replicate($mdb, $key)
{
    $db = false;
    if (server_int('slave_enable', 0, $mdb)) {
        if (server_int($key, 0, $mdb)) {
            $server = server_def('slave_server', '', $mdb);
            $mid = '';
            $sid = '';
            $sdb = db_server($server);
            if (($sdb) && ($sdb != $mdb)) {
                $mid = get_mysql_variable('server_id', $mdb);
                $sid = get_mysql_variable('server_id', $sdb);
                debug_note("master server_id:$mid  slave server_id:$sid");
            }
            if (($mid != '') && ($sid != '') && ($sid != $mid)) {
                $db = $sdb;
            }
        }
    }
    return $db;
}



function db_slave($mdb)
{
    return db_replicate($mdb, 'slave_user');
}

function db_cron($mdb)
{
    return db_replicate($mdb, 'slave_cron');
}

function find_scalar($sql, $db)
{
    $val = '';
    $res = command($sql, $db);
    if ($res) {

        $resVal = mysqli_fetch_assoc($res);
        $val = $resVal['servertime'];
        mysqli_free_result($res);
    }
    return $val;
}

function event_delay($mdb, $sdb)
{
    $delay = 0;
    $sql = "select max(servertime) as servertime from Events limit 1";
    $slast = intval(find_scalar($sql, $sdb));
    $mlast = intval(find_scalar($sql, $mdb));
    if ($mlast > $slast)
        $delay = $mlast - $slast;
    return $delay;
}

function event_missing($mdb, $sdb)
{
    $miss = 0;
    $sql = "select count(*) from Events";
    $scnt = intval(find_scalar($sql, $sdb));
    $mcnt = intval(find_scalar($sql, $mdb));
    if ($mcnt > $scnt)
        $miss = $mcnt - $scnt;
    return $miss;
}
