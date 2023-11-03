<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
19-Sep-02   EWB     Created.
*/


#
# $page[current][start]
# $page[current][end]
# $page[current][size]
# $page[next][exists]
# $page[next][start]
# $page[next][end]
# $page[previous][exists]
# $page[previous][start]
# $page[previous][end]
#

function paginate($start, $size, $resultsize) 
{
    $page['current']['start'] = $start;
    if ($page['current']['start'] < 1) 
    {
        $page['current']['start'] = 1;
    }
    if ($page['current']['start'] > $resultsize) 
    {
        $page['current']['start'] = $resultsize;
    }

    $page['current']['end'] = $page['current']['start'] + ($size - 1);
    if ($page['current']['end'] > $resultsize) 
    {
        $page['current']['end'] = $resultsize;
    }

    $page['current']['size'] = ($page['current']['end'] - $page['current']['start']) + 1;

    if ($page['current']['end'] < $resultsize) 
    {
        $page['next']['exists']  = 1;
        $page['next']['start']   = $page['current']['end'] + 1;
        $page['next']['end']     = $page['current']['end'] + $size;
    } 
    else 
    {
        $page['next']['exists']    = 0;
        $page['next']['start']     = $page['current']['start'];
        $page['next']['end']       = $page['current']['end'];
    }

    if ($page['current']['start'] > 1) 
    {
        $page['previous']['exists'] = 1;
        $page['previous']['start']  = $page['current']['start'] - $size;
        $page['previous']['end']    = $page['current']['start'] - 1;
    } 
    else 
    {
        $page['previous']['exists'] = 0;
        $page['previous']['start']  = $page['current']['start'];
        $page['previous']['end']    = $page['current']['end'];
    }
    return $page;
}


function debug_paginate ($page) 
{
    echo "current start "   . $page['current']['start'] . "<br>";
    echo "current end "     . $page['current']['end'] . "<br>";
    echo "current size "    . $page['current']['size'] . "<br>";
    echo "next exists "     . $page['next']['exists'] . "<br>";
    echo "next start "      . $page['next']['start'] . "<br>";
    echo "next end "        . $page['next']['end'] . "<br>";
    echo "previous exists " . $page['previous']['exists'] . "<br>";
    echo "previous start "  . $page['previous']['start'] . "<br>";
    echo "previous end "    . $page['previous']['end'] . "<br>";
}


      
?>

