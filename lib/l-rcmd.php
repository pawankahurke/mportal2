<?php

include_once $absDocRoot . "config.php";

include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'l-head.php';
function redcommand_time($sql, &$time, $db)
{
    $res = false;
    $test = global_def('test_sql', 0);
    $show = global_def('show_sql', 0);
    $dbug = global_def('debug', 0);
    $time = 0;

    if ($test) {
        $color = 'blue';
        $res = false;
    } else {
        $color = 'red';
        $res = command_time($sql, $time, $db);
    }
    if (($show) || ($dbug)) {
        $msg = str_replace("\n", "<br>\n&nbsp;&nbsp;&nbsp;", $sql);
        if ($time > 0) {
            $secs = microtime_show($time);
            $msg .= ";&nbsp;&nbsp;&nbsp;($secs)";
        }
        if ((!$res) && (!$test)) {
            $error = mysqli_error($db);
            $errno = mysqli_errno($db);
            $msg .= "<br>\nerrno:$errno<br>\n$error";
        }
    }
    return $res;
}

function redcommand($sql, $db)
{
    $tim = 0;
    return redcommand_time($sql, $tim, $db);
}

function debug_note($msg)
{
    $show = @$GLOBALS['debug'];
    if (isset($show) && ($show)) {
    }
}

function debug_error_log($msg, $type)
{
    $show = @$GLOBALS['debug'];
    if (isset($show) && ($show)) {
        logs::log(__FILE__, __LINE__, $msg, $type);
    }
}

