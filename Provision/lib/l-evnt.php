<?php

/*
Revision History:

Date       Who    What
----       ---    ----
03-Nov-05  BJS    Created to store common event procs.
30-Nov-05  BJS    Removed group_detail().
*/   

    function format_suspend($when, $now)
    {
        $ignore = array();
        $date = '';
        if ($when > $now)
        {
            $date   = shortdate($when);
            $ignore = explode(',',$date);
        }
        return textmax('susp',19,19,$date);
    }


    function shortdate($utime)
    {
        $date = getdate($utime);
        $year = $date['year'];
        $mon  = $date['mon'];
        $day  = $date['mday'];
        if (assumeyear($mon) == $year)
        {
            $msg = sprintf('%d/%d',$mon,$day);
        }
        else
        {
            $msg = sprintf('%d/%d/%02d',$mon,$day,$year % 100);
        }
        if (($date['hours']) || ($date['minutes']) || ($date['seconds']))
        {
            $msg .= sprintf(" %02d:%02d",$date['hours'],$date['minutes']);
            if ($date['seconds'])
            {
                $msg .= sprintf(":%02d",$date['seconds']);
            }
        }
        return $msg;
    }


?>