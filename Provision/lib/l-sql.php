<?php

/*
Revision history:

Date        Who     What
----        ---     ----
19-Sep-02   EWB     Created.
13-Feb-03   EWB     More debug information.
19-Jun-03   EWB     moved microtime_diff, microtime_show here.
31-Oct-03   EWB     moved microtime_diff, microtime_show to l-db.php
 2-Dec-04   EWB     tag mysql messages for grepping in php.log
*/

   /*
    |  Converts from a unix time_t into mysql DATETIME
    |  ("YYYY-MM-DD HH:MM:SS") string type.
    */

    function mysqltime($utime)
    {
        return date("Y-m-d H:i:s",$utime);
    }


    function sqlerror($sql,$db)
    {
        $txt = mysqli_error($db);
        $num = mysqli_errno($db);
        $msg = "mysql: ($num) $txt";
        $cmd = "mysql: $sql";
        logs::log(__FILE__, __LINE__, $cmd,0);
        logs::log(__FILE__, __LINE__, $msg,0);
        echo "<p>Query: $sql<br>Errno: $num<br>Error: $txt</p>";
    }



   /*
    |  Acts just the same as mysql_query, plus it
    |  logs errors to the php log file.
    */

    function command_time($sql,&$time,$db)
    {
        $before = microtime();
        $result = mysqli_query($db, $sql);
        $after  = microtime();
        $time   = microtime_diff($before,$after);
        if (!$result)
        {
            $xxx = microtime_show($time);
            $txt = mysqli_error($db);
            $num = mysqli_errno($db);
            $msg = "mysql: ($num) $txt; $xxx";
            $cmd = "mysql: $sql";
            if (function_exists('server_var'))
            {
                $self = server_var('PHP_SELF');
                $args = server_var('QUERY_STRING');
                $what = ($args)? "$self?$args" : $self;
                $text = "mysql: $what";
                logs::log(__FILE__, __LINE__, $text,0);
            }
            logs::log(__FILE__, __LINE__, $msg,0);
            logs::log(__FILE__, __LINE__, $cmd,0);
        }
        return $result;
    }


   /*
    |  Acts just the same as mysql_query, plus it
    |  logs errors to the php log file.
    */

    function command($sql,$db)
    {
        $time = 0;
        return command_time($sql,$time,$db);
    }
