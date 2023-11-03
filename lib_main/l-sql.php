<?php

function mysqltime($utime)
{
    return date("Y-m-d H:i:s", $utime);
}

function sqlerror($sql, $db)
{
    $txt = mysqli_error($db);
    $num = mysqli_errno($db);
    $msg = "mysql: ($num) $txt";
    $cmd = "mysql: $sql";
    echo "<p>Query: $sql<br>Errno: $num<br>Error: $txt</p>";
}

function command_time($sql, &$time, $db)
{
    $before = microtime();
    $result = mysqli_query($db, $sql);
    $after = microtime();
    $time = microtime_diff($before, $after);
    if (!$result) {
        $xxx = microtime_show($time);
        $txt = mysqli_error($db);
        $num = mysqli_errno($db);
        $msg = "mysql: ($num) $txt; $xxx";
        $cmd = "mysql: $sql";
        if (function_exists('server_var')) {
            $self = server_var('PHP_SELF');
            $args = server_var('QUERY_STRING');
            $what = ($args) ? "$self?$args" : $self;
            $text = "mysql: $what";
        }
    }
    return $result;
}

function command($sql, $db)
{
    $time = 0;
    return command_time($sql, $time, $db);
}
