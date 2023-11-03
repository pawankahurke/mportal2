<?php

/*
Revision history:

Date        Who     What
----        ---     ----
10-Oct-02   EWB     Created.
22-Oct-02   EWB     Added debug_log
16-Jan-03   EWB     Make HTML output easier to read.
17-Feb-03   AAM     Fixed an annoying PHP3 warning that was happening a lot.
18-Jun-03   EWB     Report elapsed time.
19-Jun-03   EWB     created redcommand_time.
27-Oct-05   BJS     Added debug_error_log.
*/

   /*
    |  needs:
    |    l-sql.php -- command, command_time, microtime_show
    */

    function redcommand_time($sql,&$time,$db)
    {
        $res  = false;
        $test = global_def('test_sql',0);
        $show = global_def('show_sql',0);
        $dbug = global_def('debug',0);
        $time = 0;

        if ($test)
        {
            $color = 'blue';
            $res   = false;
        }
        else
        {
            $color = 'red';
            $res   = command_time($sql,$time,$db);
        }
        if (($show) ||($dbug))
        {
            $msg = str_replace("\n","<br>\n&nbsp;&nbsp;&nbsp;",$sql);
            if ($time > 0)
            {
                $secs = microtime_show($time);
                $msg .= ";&nbsp;&nbsp;&nbsp;($secs)";
            }
            if ((!$res) && (!$test))
            {
                $error = mysqli_error($db);
                $errno = mysqli_errno($db);
                $msg .= "<br>\nerrno:$errno<br>\n$error";
            }
            echo "<font color=\"$color\"><p>$msg</p></font><br>\n";
        }
        return $res;
    }


   /*
    |  If you don't need to use the timestamp.
    */

    function redcommand($sql,$db)
    {
        $tim = 0;
        return redcommand_time($sql,$tim,$db);
    }


    function debug_note($msg)
    {
        $show = @ $GLOBALS['debug'];
        if (isset($show) && ($show))
        {
            echo "<font color=\"green\">$msg</font><br>\n";
        }
    }


    function debug_error_log($msg, $type)
    {
        $show = @ $GLOBALS['debug'];
        if (isset($show) && ($show))
        {
            logs::log(__FILE__, __LINE__, $msg, $type);
        }
    }


?>

