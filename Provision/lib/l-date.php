<?php

/*
Revision history:

Date        Who     What
----        ---     ----
19-Sep-02   EWB     Created
14-Nov-02   EWB     we only get 68 years.
30-Dec-02   EWB     Single quote unevaluated string literals
25-Mar-04   EWB     Allow the user to specify a time without a date.
*/


/*
 |  Decide what to do when the user specifies a date with a month and
 |  a day, but not a year.  We start out by assuming he means the
 |  current year.
 |
 |  However, if it happens to be January or February, and he says
 |  November or December, we assume he means last year.
 |
 |  If it happens to be November or December, and he says January
 |  or February, we assume he means next year.
 */

function assumeyear($month)
{
    $date = getdate(time());
    $year = $date['year'];
    $mon  = $date['mon'];
    if ((11 <= $mon) && ($month <= 2)) $year++;
    if (($mon <= 2) && (11 <= $month)) $year--;
    return $year;
}


function debug_date($text)
{
    if (function_exists('debug_note'))
    {
        debug_note("date:$text");
    }
}


/*
 |  Parse a user supplied date and time.
 |  Assume reasonable defaults for unspecified
 |  values.
 |
 |  If the user supplies a date, but not a time, 
 |  assume midnight (00:00:00) of the specified day.
 |
 |  If the user supplies a time, but not a date, 
 |  we assume he means today.
 |
 |  If the user supplies a date but not a year, 
 |  pick the closest date.
 |
 |  Returns zero in the event of any error.
 */

function parsedate($text,$now)
{
    $posix = 0;
    $date  = '';
    $time  = '';
    $text  = trim($text);

    if ($text == '')
    {
        return $posix;
    }

   /*
    |  Note we have to be careful with strpos.
    |  However in this case we don't want any
    |  of these in position zero so it's ok
    |  to treat 0 and false the same.
    */

    $slash = intval(strpos($text,'/'));
    $colon = intval(strpos($text,':'));
    $space = intval(strpos($text,' '));

    if ((!$slash) && (!$colon))
    {
        debug_date('no colon, no slash');
        return $posix;
    }

    if ($space)
    {
        if (($slash) && ($colon))
        {
            if (preg_match('/\s*([^\s]+)\s+([^\s]+)\s*/',$text,$match))
            {
                $date = $match[1];
                $time = $match[2];
            }
        }
    }
    else
    {
        if (($slash) && (!$colon))
        {
            $date = $text;
            $time = '';
        }
        if (($colon) && (!$slash))
        {
            $date = '';
            $time = $text;
        }
    }

    $year  = 0;
    $month = 0;
    $day   = 0;
    $today = getdate($now);
    if ($date == '')
    {
        $year  = $today['year'];
        $month = $today['mon'];
        $day   = $today['mday'];
    }
    else
    {
        if (preg_match('^\s*(\d+)/(\d+)/(\d+)^',$date,$match))
        {
            $month = $match[1];
            $day   = $match[2];
            $year  = $match[3];
        }
        else if (preg_match('^\s*(\d+)/(\d+)^',$date,$match))
        {
            $month = $match[1];
            $day   = $match[2];
            $year  = assumeyear($month);
        }
        else
        {
            debug_date("not date: $date");
            return $posix;
        }

        if ((0 <= $year) && ($year < 100))
        {
            if ($year > 69) $year += 1900;
            if ($year < 38) $year += 2000;
        }
    }

    if ((31 < $day) || ($day < 1) || (12 < $month) || ($month < 1))
    {
        debug_date("day:$day, month:$month, year:$year");
        return $posix;
    }

    $hh = 0;
    $mm = 0;
    $ss = 0;

    if ($time != '')
    {
        if (preg_match('/(\d+)\:(\d+)\:(\d+)/',$time,$match))
        {
            $hh = $match[1];
            $mm = $match[2];
            $ss = $match[3];
        }
        else if (preg_match('/(\d+)\:(\d+)/',$time,$match))
        {
            $hh = $match[1];
            $mm = $match[2];
        }
        else
        {
            debug_date("not time: $time");
            return $posix;
        }
    }

    if (($year < 1970) || ($year > 2037))
    {
        debug_date("bad year: $month/$day/$year ($date)");
        return $posix;
    }

    if ((24 <= $hh) || (60 <= $mm) || (60 <= $ss))
    {
        debug_date("bad time: $hh:$mm:$ss ($time)");
        return $posix;
    }

   /*
    |   New mktime now handles the missing hour in the 23 hour day.
    |   4/1/2001 2:30 --> 4/1/2001 3:30
    */

    if ($year > 1969)
    {
        if (checkdate($month,$day,$year))
        {
            $posix = mktime($hh,$mm,$ss,$month,$day,$year);
            $value = sprintf('%02d/%02d/%04d %02d:%02d:%02d (%d)',
                        $month,$day,$year,$hh,$mm,$ss,$posix);
            debug_date($value);
        }
    }
    return $posix;
}


?>

