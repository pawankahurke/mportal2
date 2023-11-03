<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
19-Sep-02   EWB     Created.
29-May-03   EWB     Quote Crusade.
*/

function date_select($name, $day, $mon, $yr, $hr, $min) 
{
    if (!isset($name)) $name = "date"; 

    $months = array("1" => "January",
                    "2" => "February",
                    "3" => "March",
                    "4" => "April",
                    "5" => "May",
                    "6" => "June",
                    "7" => "July",
                    "8" => "August",
                    "9" => "September",
                   "10" => "October",
                   "11" => "November",
                   "12" => "December");

    $mname = $name . '_MON';
    echo "<select name=\"$mname\">\n";
    for (reset($months); $key = key($months); next($months)) 
    {
        if ($key == $mon) 
            $s = " selected";
        else 
            $s = "";
        printf("<option%s value=\"%02d\">%s</option>\n",$s, $key, $months[$key]);
    }
    echo "</select>\n";

    $dname = $name . '_DAY';
    echo "<select name=\"$dname\">\n";
    $db = 1;
    $de = 32;

    while ($db < $de) 
    {
        if ($day == $db) 
            $s = " selected";
        else
            $s = "";
        printf("<option%s value=\"%02d\">%d</option>\n", $s, $db, $db);
        $db = $db + 1;
    }
    echo "</select>\n";

    $yname = $name . '_YR';
    echo "<select name=\"$yname\">\n";

    $yb = date("Y", time()) - 1;
    $ye = $yb + 3;

    while ($yb < $ye) 
    {
        if ($yr == $yb) 
            $s = " selected";
        else
            $s = "";
        printf("<option%s>%d</option>\n",$s,$yb);

        $yb = $yb + 1;
    }

    echo "</select>\n";

    $hname = $name . '_HR';
    echo "<select name=\"$hname\">\n";
    for ($i = 0; $i < 24; $i++) 
    {
        if ($hr == $i) 
            $s = " selected";
        else 
            $s = "";
        printf("<option%s>%02d</option>\n", $s, $i);
    }       
    echo "</select>";

    $nname = $name . '_MIN';
    echo ":<select name=\"$nname\">\n";
    for ($i = 0; $i < 60; $i++) 
    {
        if ($min == $i) 
            $s = " selected";
        else 
            $s = "";
        printf("<option%s>%02d</option>\n", $s, $i);
    }
    echo "</select>\n";
}


?>

