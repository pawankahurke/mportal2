<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
16-Jun-03   EWB     Created.
18-Jun-03   EWB     Interactive and cron separately controlled.
*/

   /*
    |  Note ... we'll have to change this if mysql ever changes
    |  the format of it's show variables command.   However, I
    |  don't think thats too likely.
    */

    function get_mysql_variable($name,$db)
    {
        $valu = '';
        $sql  = "show variables like '$name'";
        $res  = command($sql,$db);
        $row  = array( );
        if ($res)
        {
            if (mysqli_num_rows($res) == 1)
            {
                $row = mysqli_fetch_assoc($res);
            }
        }
        if ($row)
        {
            $valu = $row['Value'];
        }
        return $valu;
    }


   /*
    |  When you ask for localhost, it seems that mysql_pconnect
    |  will ignore the port number and just use the socket.  This
    |  means that sometimes you can attempt to open a connection
    |  to the slave database and just end up with a second 
    |  connection to the master.
    |
    |  And this leads to the confusing situation where it seems
    |  as though replication is working, even though it really
    |  isn't.
    |
    |  So ... we check the server_id of both databases, and only
    |  accept the second connection when we find that it is
    |  really speaking to a different server.
    */

    function db_replicate($mdb,$key)
    {
        $db = false;
        if (server_int('slave_enable',0,$mdb))
        {
            if (server_int($key,0,$mdb))
            {
                $server = server_def('slave_server','',$mdb);
                $mid = '';
                $sid = '';
                $sdb = db_server($server);
                if (($sdb) && ($sdb != $mdb))
                {
                    $mid = get_mysql_variable('server_id',$mdb);
                    $sid = get_mysql_variable('server_id',$sdb);
                    debug_note("master server_id:$mid  slave server_id:$sid");
                }
                if (($mid != '') && ($sid != '') && ($sid != $mid))
                {
                    $db = $sdb;
                }
            }
        }
        return $db;
    }


   /*
    |  slave_enable -- master switch for replicated database
    |  slave_cron   -- enable for cron jobs.
    |  slave_user   -- enable for interactive pages.
    |
    |  The point is to have a big switch that can completely
    |  turn off the feature, and then underneath that an
    |  individual enable and disable for interactive and
    |  cron pages.
    */

    function db_slave($mdb)
    {
        return db_replicate($mdb,'slave_user');
    }

    function db_cron($mdb)
    {
        return db_replicate($mdb,'slave_cron');
    }

    function find_scalar($sql,$db)
    {
        $val = '';
        $res = command($sql,$db);
        if ($res)
        {
            $val = mysqli_result($res, 0);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
        return $val;
    }


    function event_delay($mdb,$sdb)
    {
        $delay = 0;
        $sql   = "select max(servertime) from Events limit 1";
        $slast = intval(find_scalar($sql,$sdb));
        $mlast = intval(find_scalar($sql,$mdb));
        if ($mlast > $slast) $delay = $mlast - $slast;
        return $delay;
    }


    function event_missing($mdb,$sdb)
    {
        $miss  = 0;
        $sql   = "select count(*) from Events";
        $scnt  = intval(find_scalar($sql,$sdb));
        $mcnt  = intval(find_scalar($sql,$mdb));
        if ($mcnt > $scnt) $miss = $mcnt - $scnt;
        return $miss;
    }


?>
