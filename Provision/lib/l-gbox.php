<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
19-Sep-02   EWB     Created.
26-Feb-03   EWB     Squashed a few warnings.
26-Feb-03   EWB     find_field_names works even when table is empty.
26-Feb-03   EWB     Moved genconfig here.
29-May-03   EWB     Let the style sheet control the font.
 1-Oct-03   EWB     find_field_names() --> l-msql.php
 1-Oct-03   EWB     created genconfig_list()
29-Mar-04   EWB     Created event_fields().
13-Oct-04   EWB     genboxes returns (instead of echo) its results.
*/


/*
 |  event detail checkbox code.
 |
 |   common to reports and notifications
 */

function gbox($i, $n, $name, $enabled)
{
    $checked = '';
    $out = '';
    if ($i < $n) {
        if ($enabled) $checked = 'checked';
        $out = <<< HERE

  <td align="right">
    $name:
  </td>
  <td>
    <input type="checkbox" name="d_$name" value="1" $checked>
  </td>

HERE;
    }
    return $out;
}

/*
 |  The deleted field will always be zero for anything
 |  we will be selecting on, so it doesn't make sense
 |  to include it in the genbox table.
 */


function event_fields($db)
{
    $names = find_field_names('event', 'Events', $db);
    $keys  = array_flip($names);
    unset($keys['deleted']);
    return array_flip($keys);
}


function genboxes($fnames, $defaults)
{
    $n = safe_count($fnames);

    $names = array();
    $names[] = 'ALL';

    reset($fnames);
    foreach ($fnames as $key => $name) {
        $names[] = $name;
    }

    for ($i = 0; $i < 6; $i++) {
        $names[] = '';
    }

    reset($names);
    foreach ($names as $key => $name) {
        if (!isset($defaults[$name])) {
            $defaults[$name] = '';
        }
    }

    $n = $n + 1;
    $x = intval(($n + 4) / 5);
    $i = 0;
    $m = '';
    for ($r = 0; $r < $x; $r++) {
        $m .= "<tr>\n";
        $m .= gbox($i, $n, $names[$i], $defaults[$names[$i]]);
        $i++;
        $m .= gbox($i, $n, $names[$i], $defaults[$names[$i]]);
        $i++;
        $m .= gbox($i, $n, $names[$i], $defaults[$names[$i]]);
        $i++;
        $m .= gbox($i, $n, $names[$i], $defaults[$names[$i]]);
        $i++;
        $m .= gbox($i, $n, $names[$i], $defaults[$names[$i]]);
        $i++;
        $m .= "</tr>\n";
    }
    return $m;
}


function genconfig_list($db, $details, $post, $list)
{
    $config = '';
    if ($details) {
        $config = ':';
        $all    = @$post['d_ALL'];

        reset($list);
        foreach ($list as $xxx => $name) {
            $d_name = "d_$name";
            $found  = @$post[$d_name];

            if (($all) || ($found)) {
                $config .= "$name:";
            }
        }
    }
    return $config;
}


/*
    |  Don't allow config to be empty if details are
    |  enabled.  We store the values in such a way
    |  so we can use substring matches to query,
    |  and not be confused by 'id' vs. 'idx', etc.
    */

function genconfig($db, $details, $post)
{
    $list = find_field_names('event', 'Events', $db);
    return genconfig_list($db, $details, $post, $list);
}
