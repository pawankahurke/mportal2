<?php




   

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


   

    function days_ago($time,$days)
    {
        $date = getdate($time);
        $hour = $date['hours'];
        $when = $time - ($days * 86400);
        return correct_hour($when,$hour);
    }


   

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

       

        $when = mktime($h,$m,$s,$mm,$dd,$yy);
        if ( (!isset($when)) || ($when <= 0) )
        {
            if ($h < 23) $h++;
            $when = mktime($h,$m,$s,$mm,$dd,$yy);
        }
        return $when;
    }


   

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


   

    function yesterweek($tdate)
    {
        return days_ago($tdate,7);
    }


   

    function yestermonth($tdate)
    {
        return months_ago($tdate,1);
    }


   

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
                        $nwday = $tmp['wday'];
            $rwday = $row['wday'];
            $when  = $when - ($nwday * 86400);
            $when  = $when + ($rwday * 86400);
            $next  = ($now <= $when)? $when : $when + (7*86400);
        }
        if ($cycle == 2)
        {
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
                                    
            $next = correct_hour($next,$rhh);
        }
        return $next;
    }


   

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
                        $nwday = $tmp['wday'];
            $rwday = $row['wday'];
            $when  = $when - ($nwday * 86400);
            $when  = $when + ($rwday * 86400);
            $last  = ($when < $now)? $when : yesterweek($when);
        }
        if ($cycle == 2)
        {
                        $mday = $row['mday'];
            if ($mday > 28) $mday = 28;
            $when = mktime($rhh,$rmm,0,$mon,$mday,$yyy);
            $last = ($when < $now)? $when : yestermonth($when);
        }
        if ($cycle == 3)
        {
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
                                    
            $last = correct_hour($last,$rhh);
        }
        return $last;
    }


?>

