<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
10-Oct-02   EWB     Created.
14-Nov-02   EWB     Fixed a bug in union()
 4-Sep-03   EWB     created subset().
*/


/*
    |  a and b are both simple lists of small integers.
    |  returns a new list which contains just the
    |  values which are in both arrays.
    */

function intersect($a, $b)
{
    $tmp = array();
    $res = array();
    reset($a);
    foreach ($a as $key => $data) {
        $tmp[$data] = false;
    }
    reset($b);
    foreach ($b as $key => $data) {
        $tmp[$data] = true;
    }
    reset($a);
    foreach ($a as $key => $data) {
        if ($tmp[$data])
            $res[] = $data;
    }
    return $res;
}



/*
    |  a and b are both simple lists of small integers.
    |  returns a new list which contains the
    |  values which are in either arrays.
    */

function union($a, $b)
{
    $tmp = array();
    $res = array();
    reset($a);
    foreach ($a as $key => $data) {
        $tmp[$data] = true;
    }
    reset($b);
    foreach ($b as $key => $data) {
        $tmp[$data] = true;
    }
    reset($tmp);
    foreach ($tmp as $key => $data) {
        $res[] = $key;
    }
    return $res;
}


/*
    |  a and b are both simple lists of things.
    |
    |  Returns true if a is a subset of b.
    |
    |  Or to put it another way, everything in 
    |  a must also be contained in b as well.
    */

function subset($a, $b)
{
    $vote = array();
    $good = true;
    reset($a);
    foreach ($a as $key => $data) {
        $vote[$data] = true;
    }
    reset($b);
    foreach ($b as $key => $data) {
        $vote[$data] = false;
    }
    reset($vote);
    foreach ($vote as $key => $data) {
        if ($data) {
            $good = false;
        }
    }
    return $good;
}
