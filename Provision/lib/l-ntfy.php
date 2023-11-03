<?php

/*
Revision history:

Date        Who     What
----        ---     ----
13-Nov-02   EWB     Log mysql failures.
10-Feb-03   EWB     Fixed an override bug in search_select
23-Feb-03   EWB     Quote Crusade.
 3-Jun-03   EWB     Just use the standard select.
 7-May-04   EWB     More frequency options
10-Oct-04   EWB     Uses seconds instead.
19-Oct-04   EWB     Schedule / Cron constants.
20-Oct-04   EWB     Cron Schedule constants moved to l-next.php
29-Oct-04   EWB     added 2-6 days
*/


function frequency_options()
{
    $m = 60;
    $h = $m * 60;
    $d = $h * 24;
    return array
    (
        $m*3  =>  '3 minutes',
        $m*5  =>  '5 minutes',
        $m*10 => '10 minutes',
        $m*20 => '20 minutes',
        $m*40 => '40 minutes',
        $h*1  => '1 hour',
        $h*2  => '2 hours',
        $h*3  => '3 hours',
        $h*4  => '4 hours',
        $h*5  => '5 hours',
        $h*6  => '6 hours',
        $h*7  => '7 hours',
        $h*8  => '8 hours',
        $h*9  => '9 hours',
        $h*10 => '10 hours',
        $h*11 => '11 hours',
        $h*12 => '12 hours',
        $h*13 => '13 hours',
        $h*14 => '14 hours',
        $h*15 => '15 hours',
        $h*16 => '16 hours',
        $h*17 => '17 hours',
        $h*18 => '18 hours',
        $h*19 => '19 hours',
        $h*20 => '20 hours',
        $h*21 => '21 hours',
        $h*22 => '22 hours',
        $h*23 => '23 hours',
        $d    => '1 day',
        $d*2  => '2 days',
        $d*3  => '3 days',
        $d*4  => '4 days',
        $d*5  => '5 days',
        $d*6  => '6 days',
        $d*7  => '1 week'
    );
}


   // http://www.htmlgoodies.com/tutors/colors.html

   // wheat         '#F5DEB3'
   // lightsalmon   '#FFA07A'
   // gold          '#FFD700'
   // lightpink     '#FFB6CA'
   // lemonchiffon  '#FFFACD'
   // lightgreen    '#90EE90'
   // lightskyblue  '#87CEFA'
   // aquamarine    '#7FFFD4'

   $priorities = array('wheat','lightpink','lemonchiffon','lightgreen','aquamarine','lightskyblue');

?>

