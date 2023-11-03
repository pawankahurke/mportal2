<?php


/*
  |  This is sample code to demonstrate how
  |  to log events to a parallel database.
  |
  |  The events are stored in an associative
  |  array, named with the same names in the
  |  mysql events table.
  |
  |     $args['idx'] -- index in events table
  |     $args['entered'] -- client time
  |     $args['scrip'] -- scrip number
  |     $args['servertime'] -- servertime
  |
  |   Enable this by running server.php
  |   set the server option 'event_code'
  |   as follows:
  |
  |       "include ( 'parallel.php' );"
  |
  |   Remember to include the trailing
  |   semicolon as well, but not the
  |   double quotes.
  */


$pid = getmypid();
$now = time();
$fname  = "/tmp/$now.$pid";
$handle = fopen($fname, 'w');
if ($handle) {
    //     logs::log(__FILE__, __LINE__, $fname,0);
    reset($args);
    foreach ($args as $key => $data) {
        if ($data != '') {
            fwrite($handle, "$key:$data\n");
        }
    }
    fwrite($handle, "\n\n\n");
    fclose($handle);
} else {
    logs::log(__FILE__, __LINE__, "could not create $fname", 0);
}
