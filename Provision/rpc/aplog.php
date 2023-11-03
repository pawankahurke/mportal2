<?php


/*
  |  This is sample code to demonstrate how
  |  to log assets to a parallel database.
  |
  |  The assets are stored in an associative
  |  array named $vars.
  |
  |  Enable this by running server.php
  |  set the server option 'asset_code' 
  |  as follows:
  |
  |       "include ( 'aplog.php' );"
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
    logs::log(__FILE__, __LINE__, $fname, 0);

    $date = date('Y-m-d H:i:s', $now);
    fwrite($handle, "site:$site, machine:$machine, date:$date\n");

    reset($vals);
    foreach ($vals as $name => $ords) {
        $group = $grps[$name];
        reset($ords);
        foreach ($ords as $ord => $value) {
            fwrite($handle, "$name:$group:$ord:$value\n");
        }
    }
    fwrite($handle, "\n\n");
    fclose($handle);
} else {
    logs::log(__FILE__, __LINE__, "could not create $fname", 0);
}
