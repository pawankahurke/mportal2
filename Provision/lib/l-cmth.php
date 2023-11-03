<?php

/*
Revision history:

Date        Who     What
----        ---     ----
25-Sep-02   EWB     Created.
 8-Oct-02   EWB     days_ago, months_ago, correct_dst
19-May-03   EWB     Calculates period of cyclic reports.
21-Oct-03   EWB     Created next_cycle
22-Oct-03   EWB     Created last_cycle, correct_hour
 5-Apr-04   EWB     The missing hour bug.
*/


   /*
    |  Corrects for daylight savings time.
    |
    |  In this case we know that the hour of the target time.
    |  so we can just add or subtract an hour as appropriate.
    |
    |  5-Apr-04: The missing hour bug.  Nanoheal was having
    |  a problem early yesterday morning at the beginning of
    |  daylight savings time.  What happened is that we had
    |  a pair of reports which were supposed to run at 2AM 
    |  Sunday morning.   But when we tried to calculate a
    |  next_run time for 2AM EST we got 3AM EDT and 
    |  subtracting an hour put us back to 1AM EST.
    |
    |  So the reports started an hour earlier then expected,
    |  but set a next_run for the next cycle boundary which
    |  was 2AM EST ... but that turned into 3AM EDT and it
    |  just kept looping for an hour.
    |
    |  So ... after calculating a correction, check to make
    |  sure it works.  If it doesn't then we're standing
    |  right on the edge should just leave it alone.
    */

    function correct_hour($when,$hour)
    {
        $tm = getdate($when);
        $hh = $tm['hours'];
        if ($hh != $hour)
        {
            $temp = $when;
            if ((($hh + 1) % 24) == $hour)
            {
                $temp = $when + 3600;
            }
            if ((($hh + 23) % 24) == $hour)
            {
                $temp = $when - 3600;
            }
            if ($temp != $when)
            {
                $tm = getdate($temp);
                $hh = $tm['hours'];
                if ($hh == $hour)
                {
                    $when = $temp;
                }
            }
        }
        return $when;
    }


   /*
    |  Calculates a time some number of days ago.
    |  If we enter or leave daylight savings time,
    |  correct for that.
    */

    function days_ago($time,$days)
    {
        $date = getdate($time);
        $hour = $date['hours'];
        $when = $time - ($days * 86400);
        return correct_hour($when,$hour);
    }


   /*
    |  Calculates a unix time for a month before
    |  the specified time.  Since months are not
    |  all the same length, you can get strange
    |  results at the end of some months.
    |
    |      Mar 28-31 2001 --> Feb 28 2001
    |      May 30-31 2001 --> Apr 30 2001
    |      Jul 30-31 2001 --> Jun 30 2001
    |      Oct 30-31 2001 --> Sep 30 2001
    |      Dec 30-31 2001 --> Nov 30 2001
    |
    |  Be careful with mktime(), it can have trouble during
    |  the missing hour when daylight savings time goes into
    |  effect.  This happens in the early morning hours of the
    |  first Sunday in April when 1:59 EST is followed by 3:00 EDT.
    |
    |  In php4 it seems to work ok.
    */

    function months_ago($time,$months)
    {
        $len  = array(1 => 31,
                      2 => 28,
                      3 => 31,
                      4 => 30,
                      5 => 31,
                      6 => 30,
                      7 => 31,
                      8 => 31,
                      9 => 30,
                     10 => 31,
                     11 => 30,
                     12 => 31);
        $tday = getdate($time);
        $dd = $tday['mday'];
        $mm = $tday['mon'];
        $yy = $tday['year'];
        $h  = $tday['hours'];
        $m  = $tday['minutes'];
        $s  = $tday['seconds'];
        while ($months > 0)
        {
            if ($mm > 1)
                $mm--;
            else
            {
                $yy--;
                $mm = 12;
            }
            $months--;
        }
        if ($dd > $len[$mm])
        {
            if ((($yy % 4) == 0) && (($yy % 400) != 0))
            {
                $len[2] = 29;
            }
            while ($dd > $len[$mm])
            {
                $dd--;
            }
        }

       /*
        |  note ... beware of the missing hour that
        |  happens once a year when dst goes into
        |  effect, and the duplicate hour that happens
        |  once a year when it ends.  php4 seems
        |  to be well-behaved, but you never know.
        */

        $when = mktime($h,$m,$s,$mm,$dd,$yy);
        if ( (!isset($when)) || ($when <= 0) )
        {
            if ($h < 23) $h++;
            $when = mktime($h,$m,$s,$mm,$dd,$yy);
        }
        return $when;
    }


   /*
    |  Most days are 24 hours.  However April has a single
    |  day of 23 hours, and October has one of 25 hours.
    |
    |  Sun Apr  1 2001 (23 hours) 1:59 EST -> 3:00 AM EDT
    |  Sun Oct 28 2001 (25 hours) 1:59 EDT -> 1:00 AM EST
    |
    |  No such time as 2:30 AM, Sunday April 1st 2001.
    |  Two different times are called 1:30 AM Oct 27 2002.
    */

    function midnight($tdate)
    {
        $hour  = (60*60);
        $tday  = getdate($tdate);
        $delta = $tday['seconds'] + (60 * ($tday['minutes'] + (60 * $tday['hours'])));
        $ydate = ($delta <= 0)? $tdate : $tdate -$delta;
        return correct_hour($ydate,0);
    }


    function yesterday($tdate)
    {
        return days_ago($tdate,1);
    }


   /*
    |  Most weeks are 168 hours.  However April contains a single
    |  week of 167 hours, and October has one of 169 hours.
    */

    function yesterweek($tdate)
    {
        return days_ago($tdate,7);
    }


   /*
    |  Calculates a unix time for a month before
    |  the specified time.  Since months are not
    |  all the same length, you can get strange
    |  results at the end of some months.
    |
    |      Mar 28-31 2001 --> Feb 28 2001
    |      May 30-31 2001 --> Apr 30 2001
    |      Jul 30-31 2001 --> Jun 30 2001
    |      Oct 30-31 2001 --> Sep 30 2001
    |      Dec 30-31 2001 --> Nov 30 2001
    */

    function yestermonth($tdate)
    {
        return months_ago($tdate,1);
    }


   /*
    |   0: Daily report   (23,24,25 hours)
    |   1: Weekly report  (167,168,169 hours)
    |   2: Monthly report (28,29,30,31 days)
    |   3: Weekday report (1,2,3 days)
    |   4: Immediate      .... ?
    |
    |   Weekday reports work the same as daily ones, except
    |   they only run Monday through Friday, with the Monday
    |   report covering the weekend.
    |
    |   If they run a weekday report on Sunday, show a two
    |   day report.
    */

    function cyclic($cycle,$umax)
    {
        $umin = 0;
        switch ($cycle)
        {
            case  0: $umin = yesterday($umax);   break;
            case  1: $umin = yesterweek($umax);  break;
            case  2: $umin = yestermonth($umax); break;
            case  3:
                $tday = getdate($umax);
                $wday = $tday['wday'];
                $days = ($wday <= 1)? $wday+2 : 1;
                $umin = days_ago($umax,$days);
                break;
            default: break;
        }
        return $umin;
    }


   /*
    |  Try to figure out the next cycle boundary based upon the 
    |  type of report.  For example, if a report is supposed to 
    |  run at noon every day, then the next boundary should either 
    |  be noon today, or noon tomorrow, depending on what the time 
    |  is now.
    |
    |  This explictly includes the boundary condition ... so that
    |  in the example above, if the boundary time is noon, and
    |  you call it at noon, it should return noon today, not
    |  noon tomorrow.
    */

    function next_cycle(&$row,$now)
    {
        $tmp    = getdate($now);
        $cycle  = $row['cycle'];
        $rhh    = $row['hour'];
        $rmm    = $row['minute'];
        $nhh    = $tmp['hours'];
        $nmm    = $tmp['minutes'];
        $yyy    = $tmp['year'];
        $mon    = $tmp['mon'];
        $day    = $tmp['mday'];
        $when   = mktime($rhh,$rmm,0,$mon,$day,$yyy);
        if ($cycle == 0)
        {
            $next = ($now <= $when)? $when : $when + 86400;
        }
        if ($cycle == 1)
        {
            // weekly report, sundays is zero
            $nwday = $tmp['wday'];
            $rwday = $row['wday'];
            $when  = $when - ($nwday * 86400);
            $when  = $when + ($rwday * 86400);
            $next  = ($now <= $when)? $when : $when + (7*86400);
        }
        if ($cycle == 2)
        {
            // monthly report
            $mday = $row['mday'];
            if ($mday > 28) $mday = 28;
            $when = mktime($rhh,$rmm,0,$mon,$mday,$yyy);
            if ($when < $now)
            {
                if ($mon < 12)
                {
                    $mon++;
                }
                else
                {
                    $mon = 1;
                    $yyy++;
                }
                $when = mktime($rhh,$rmm,0,$mon,$mday,$yyy);
            }
            $next = $when;
        }
        if ($cycle == 3)
        {
            // weekday report, sundays is zero, 1..5
            $nwday = $tmp['wday'];
            if ($nwday == 6) $when += (2*86400);
            if ($nwday == 0) $when += (1*86400);
            $next = ($now <= $when)? $when : $when + 86400;
        }
        if ($cycle == 4)
        {
            $when = $row['umax'];
            $next = ($now <= $when)? $when : $now;
        }
        else
        {
            // if daylight savings time is about to begin
            // or end we could end up an hour off.  If so,
            // just add or subtract an hour as needed.

            $next = correct_hour($next,$rhh);
        }
        return $next;
    }


   /*
    |  This does the same thing as next_cycle, except that
    |  it must always return a timestamp in the past.
    */

    function last_cycle(&$row,$now)
    {
        $tmp    = getdate($now);
        $cycle  = $row['cycle'];
        $rhh    = $row['hour'];
        $rmm    = $row['minute'];
        $nhh    = $tmp['hours'];
        $nmm    = $tmp['minutes'];
        $yyy    = $tmp['year'];
        $mon    = $tmp['mon'];
        $day    = $tmp['mday'];
        $when   = mktime($rhh,$rmm,0,$mon,$day,$yyy);
        if ($cycle == 0)
        {
            $last = ($when < $now)? $when : yesterday($when);
        }
        if ($cycle == 1)
        {
            // weekly report, sundays is zero
            $nwday = $tmp['wday'];
            $rwday = $row['wday'];
            $when  = $when - ($nwday * 86400);
            $when  = $when + ($rwday * 86400);
            $last  = ($when < $now)? $when : yesterweek($when);
        }
        if ($cycle == 2)
        {
            // monthly report
            $mday = $row['mday'];
            if ($mday > 28) $mday = 28;
            $when = mktime($rhh,$rmm,0,$mon,$mday,$yyy);
            $last = ($when < $now)? $when : yestermonth($when);
        }
        if ($cycle == 3)
        {
            // weekday report, sundays is zero, 1..5
            $nwday = $tmp['wday'];
            if ($nwday == 0) $when = days_ago($when,2);
            if ($nwday == 6) $when = days_ago($when,1);
            $last = ($when < $now)? $when : yesterday($when);
        }
        if ($cycle == 4)
        {
            $last = $row['umin'];
        }
        else
        {
            // if daylight savings time is about to begin
            // or end we could end up an hour off.  If so,
            // just add or subtract an hour as needed.

            $last = correct_hour($last,$rhh);
        }
        return $last;
    }


?>

