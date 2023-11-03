<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
 9-Apr-03   EWB     Created.
14-Apr-03   EWB     Factored $mid out of $env 
24-Apr-03   EWB     Convert echo to string procesing.
 2-May-03   EWB     spelling: "scaler" --> "scalar"
 5-May-03   EWB     Uses group leader from database.
 6-May-03   EWB     merging and sorting changes
 7-May-03   EWB     key calculations.
 7-May-03   EWB     New format table.
 8-May-03   EWB     Group leader code works.
 9-May-03   EWB     implement find_key();
 9-May-03   EWB     Complain when machine list is empty.
15-May-03   EWB     Use base for calculated links.
16-May-03   EWB     machine_list takes umin,umax,log as arguments.
16-May-03   EWB     Appropriate message for unchanged machines.
19-May-03   EWB     jumptable for every subsection.
19-May-03   EWB     improved merging of start-end scalers.
22-May-03   EWB     Quote Crusade
23-May-03   EWB     Memory optimisations.
24-May-03   EWB     created surveytime().
28-May-03   EWB     show machine count for multi-machine options.
28-May-03   EWB     always show real timestamps in host_header
30-May-03   EWB     new diff_merge method.
24-Jul-03   EWB     uses server_link for generated links.
16-May-05   EWB     Can't name normal variable '$this'
*/


function clear_rows()
{
    $txt = '<br clear="all">';
    return "\n\n$txt\n$txt\n\n\n";
}

function two_col($a, $b)
{
    return asset_data(array($a, $b));
}

function sort_unique($a)
{
    if ($a) {
        $keys = array();
        reset($a);
        foreach ($a as $key => $data) {
            $keys[$data] = true;
        }
        ksort($keys);
        reset($keys);
        $a = array();
        foreach ($keys as $key => $data) {
            $a[] = $key;
        }
    }
    return $a;
}


function unchanged($host, $umin, $umax, $smin, $smax, $rmin, $rmax)
{

    //  debug_note("unchanged($host,u:$umin,$umax s:$smin,$smax, r:$rmin,$rmax)");

    $tmin = ($umin) ? date('m/d H:i', $umin) : 'First Log';
    $tmax = ($umax) ? date('m/d H:i', $umax) : 'Last Log';
    $name  = ucfirst($host);
    $m  = table_start();
    $m .= table_head("$name is unchanged.", 2);
    $m .= two_col('Min Log:', date('m/d H:i', $smin));
    $m .= two_col('Max Log:', date('m/d H:i', $smax));
    if (($smin < $rmin) && ($rmin < $umin))
        $m .= two_col('Prior Log:', date('m/d H:i', $rmin));
    $m .= two_col('Min Time:', $tmin);
    $m .= two_col('Max Time:', $tmax);
    if (($umax < $rmax) && ($rmax < $smax))
        $m .= two_col('Next Log:', date('m/d H:i', $rmax));
    $m .= table_end();
    return $m;
}


function table_filter($s)
{
    $s = trim($s);
    $r = ($s == '') ? '<br>' : htmlspecialchars($s);
    return $r;
}


function table_head($value, $span)
{
    return "<tr><th colspan=\"$span\">$value</th></tr>\n";
}


function compare_diff($a, $b)
{
    $agid = $a['gid'];
    $bgid = $b['gid'];

    $aord = $a['ord'];
    $bord = $b['ord'];

    $akey = $a['key'];
    $bkey = $b['key'];

    $adid = $a['did'];
    $bdid = $b['did'];

    $atim = $a['time'];
    $btim = $b['time'];
    if ($agid != $bgid)
        $comp = ($agid > $bgid) ? 1 : -1;
    else if ($akey != $bkey)
        $comp = ($akey > $bkey) ? 1 : -1;
    else if ($aord != $bord)
        $comp = ($aord > $bord) ? 1 : -1;
    else if ($adid != $bdid)
        $comp = ($adid > $bdid) ? 1 : -1;
    else if ($atim != $btim)
        $comp = ($atim > $btim) ? 1 : -1;
    else
        $comp = 0;
    return $comp;
}



/*
    |  This does the actual merge, and it is what allows us to
    |  show old/new changes for those items with keys.
    |
    |  An item gets a key by being a member of a group which
    |  contains a group leader, or by being a scalar.
    |
    |  Items without keys are just passed through unchanged.
    */

function diff_merge($diff)
{
    $nval = array();
    $oval = array();
    $time = array();
    $ords = array();
    $keys = array();
    $flat = array();

    reset($diff);
    foreach ($diff as $xxx => $data) {
        $key = $data['key'];
        $old = $data['old'];
        $new = $data['new'];
        if ($key == '') {
            if ($old != $new) {
                $flat[] = $data;
            }
        } else {
            $gid = $data['gid'];
            $did = $data['did'];
            $ord = $data['ord'];
            $tim = $data['time'];

            if ($old != '') $oval[$gid][$did][$key] = $old;
            if ($new != '') $nval[$gid][$did][$key] = $new;

            $tmp = @$time[$gid][$did][$idx];

            //         debug_note("ords[$gid][$did][$key] = $ord");

            $ords[$gid][$did][$key] = $ord;
            $keys[$gid][$did][$key] = $key;
            $time[$gid][$did][$key] = ($tim > $tmp) ? $tim : $tmp;
        }
    }

    $diff = array();
    $diff = $flat;
    $flat = array();

    reset($time);
    foreach ($time as $gid => $dids) {
        reset($dids);
        foreach ($dids as $did => $indx) {
            reset($indx);
            foreach ($indx as $key => $tim) {
                $old = @$oval[$gid][$did][$key];
                $new = @$nval[$gid][$did][$key];
                if ($old != $new) {
                    $temp['gid'] = $gid;
                    $temp['did'] = $did;
                    $temp['key'] = @$keys[$gid][$did][$key];
                    $temp['ord'] = @$ords[$gid][$did][$key];
                    $temp['old'] = $old;
                    $temp['new'] = $new;
                    $temp['time'] = $tim;
                    $diff[] = $temp;
                }
            }
        }
    }

    $time = array();
    $oval = array();
    $nval = array();
    $keys = array();
    $ords = array();
    usort($diff, 'compare_diff');
    return $diff;
}



/*
    |  human-readable elapsed time.
    */

function elapsed_time($x)
{
    $x = intval(abs($x));
    $d = intval($x / 86400);
    $x = intval($x % 86400);
    $h = intval($x / 3600);
    $x = intval($x % 3600);
    $m = intval($x / 60);
    $s = intval($x % 60);
    if ($d > 1)
        $time = sprintf("%d days, %d:%02d:%02d", $d, $h, $m, $s);
    else if ($d == 1)
        $time = sprintf("1 day, %d:%02d:%02d", $h, $m, $s);
    else if ($h)
        $time = sprintf("%d:%02d:%02d", $h, $m, $s);
    else
        $time = sprintf("%d:%02d", $m, $s);
    return $time;
}


function host_header($host, $min, $max)
{
    $smin = date('m/d H:i', $min);
    $smax = date('m/d H:i', $max);
    $elapsed = elapsed_time($max - $min);
    $msg = "Changes on $host from $smin to $smax, an elapsed time of $elapsed.";
    $jmp = jumptable('top,bottom,index');
    return "$jmp\n<p>$msg</p><br>\n\n\n";
}


/*
    |  searches $state for an exact match to group $gid ordininal $ord
    */

function find_group(&$env, &$state, $gid, $ord, $dids)
{
    //      debug_note("find_group gid:$gid ord:$ord");

    $vals  = &$env['vals'];
    $qqq   = 0;
    $nnn   = 0;
    reset($dids);
    foreach ($dids as $did => $d) {
        $nnn++;
        if (!isset($state[$gid][$did])) {
            $name = $env['names'][$did]['name'];
            debug_note("missing state did:$did ($name)");
            return 0;
        }
    }

    /*
        |  Create a list of potential candidates.
        |  We only examine the ones where the
        |  first column matches.
        */

    reset($dids);
    $did  = key($dids);
    $d    = current($dids);
    $val  = $vals[$did][$ord][$d];
    $col  = $state[$gid][$did];
    $good = array();
    reset($col);
    foreach ($col as $i => $old) {
        if ($qqq == 0) {
            if ($val === $old) {
                $good[] = $i;
            }
        }
    }
    $match = 0;

    //    debug_note("gid:$gid, did:$did, ord:$ord nnn:$nnn val:$val");

    reset($good);
    foreach ($good as $x => $tmp) {
        if ($match == 0) {
            $qqq = $tmp;
            reset($dids);
            foreach ($dids as $did => $d) {
                //               debug_note("test $did, gid:$gid ord:ord did:$did d:$d");
                if ($qqq) {
                    $name = $env['names'][$did]['name'];
                    $new = $vals[$did][$ord][$d];
                    debug_note("did:$did ($name) gid:$gid ord:$ord qqq:$qqq d:$d");
                    $old = @$state[$gid][$did][$qqq];
                    if ($old != $new) {
                        $qqq = 0;
                    }
                }
            }
            if ($qqq) $match = $qqq;
        }
    }

    return $match;
}




function diff_group(&$env, $gid, $ord, $dids, &$old)
{
    if (!isset($old[$gid]))
        return 0;

    reset($dids);
    $did  = key($dids);
    $val  = current($dids);
    $ords = $old[$gid];
    $good = array();
    reset($ords);
    foreach ($ords as $qqq => $xdids) {
        $p = @$xdids[$did];
        if ($val === $p) {
            $good[] = $qqq;
        }
    }
    $match = 0;

    reset($good);
    foreach ($good as $x => $tmp) {
        if ($match == 0) {
            $qqq = $tmp;
            reset($dids);
            foreach ($dids as $did => $val) {
                if ($qqq) {
                    $name = $env['names'][$did]['name'];
                    //       debug_note("did:$did ($name) gid:$gid ord:$ord qqq:$qqq val:$val");
                    $p = @$old[$gid][$qqq][$did];
                    if ($p != $val) {
                        $qqq = 0;
                    }
                }
            }
            if ($qqq) $match = $qqq;
        }
    }

    return $match;
}


/*
    |  This finds the last known value prior to the
    |  target time for the group element in question.
    |
    |  It does not check for removal, however this should
    |  not be an issue in the special case where we are
    |  looking for the group leader.
    */

function find_key(&$env, $time, $gid, $did, $ord)
{
    $key = '';
    if (($gid > 0) && ($did > 0) && ($ord > 0)) {
        $last = 0;
        $d    = 0;
        $grps = &$env['grps'];
        reset($grps);
        foreach ($grps as $when => $gp) {
            if ($when <= $time) {
                if (isset($gp[$gid][$ord][$did])) {
                    $d = $gp[$gid][$ord][$did];
                    $last = $when;
                }
            }
        }
        if ($last > 0) {
            $vals  = &$env['vals'];
            $key   = @strval($vals[$did][$ord][$d]);
            //          $dlast = date('m/d H:i:s',$last);
            //          $dtime = date('m/d H:i:s',$time);
            //          debug_note("key from ($dlast) time ($dtime) key:$key");
            //          debug_note("gid:$gid, did:$did, ord:$ord, d:$d");
        }
    }
    return $key;
}



/*
    |  Attempt to figure out if this new group
    |  is one that really we have seen before, but
    |  somewhere else.
    */


function new_group(&$env, &$state, $index, $gid, $ord)
{
    $times = &$env['times'];
    $grps  = &$env['grps'];
    $now   = $times[$index];
    $dids  = $grps[$now][$gid][$ord];
    return find_group($env, $state, $gid, $ord, $dids);
}


/*
    |  This traverses the $grps array and submits
    |  difference records for every group that has
    |  changed the value of at least one member.
    */

function delta_group(&$env, &$state, $index, $gid, $data, $diff)
{
    if ($data) {
        $times = &$env['times'];
        $vals  = &$env['vals'];
        $time  = $times[$index];
        $lid   = @intval($env['lead'][$gid]);
        $gname = $env['names'][$gid]['name'];
        debug_note("delta group $index ($time) gid:$gid $gname");
        reset($data);
        foreach ($data as $ord => $dids) {
            $qqq = new_group($env, $state, $index, $gid, $ord);
            if ($qqq) {
                //                    debug_note("gid:$gid ($gname) $ord --> $qqq");
            } else {
                $key = find_key($env, $time, $gid, $lid, $ord);
                reset($dids);
                foreach ($dids as $did => $d) {
                    $new = $vals[$did][$ord][$d];
                    $tmp['gid']  = $gid;
                    $tmp['did']  = $did;
                    $tmp['ord']  = $ord;
                    $tmp['key']  = $key;
                    $tmp['old']  = '';
                    $tmp['new']  = $new;
                    $tmp['time'] = $time;
                    $diff[] = $tmp;
                    //               debug_note("gid:$gid did:$did ord:$ord key:$key new:$new");
                }
            }
        }
    }
    return $diff;
}


/*
    |  data comes from the xmax array ... a list of things
    |  which expire at time $time.
    */

function vanish_group(&$env, &$state, $last, $gid, $data, $diff)
{
    if ($data) {
        $times = &$env['times'];
        $names = &$env['names'];
        $vals  = &$env['vals'];
        $qqq   = 0;
        $lid   = @intval($env['lead'][$gid]);
        $time  = $times[$last];
        $dtime = date('m/d H:i:s', $time);
        debug_note("vanish group $last $time ($dtime)");
        reset($data);
        foreach ($data as $ord => $dids) {
            $gname = $names[$gid]['name'];
            $qqq = find_group($env, $state, $gid, $ord, $dids);
            if ($qqq) {
                debug_note("vanish $last gid:$gid ($gname) $ord --> $qqq");
            } else {
                $key = find_key($env, $time, $gid, $lid, $ord);
                debug_note("vanish $last gid:$gid ($gname) ord:$ord vanishes");
                reset($dids);
                foreach ($dids as $did => $d) {
                    $old = $vals[$did][$ord][$d];
                    $tmp['gid']  = $gid;
                    $tmp['did']  = $did;
                    $tmp['ord']  = $ord;
                    $tmp['key']  = $key;
                    $tmp['new']  = '';
                    $tmp['old']  = $old;
                    $tmp['time'] = $time;
                    $diff[] = $tmp;
                    //              debug_note("gid:$gid did:$did ord:$ord key:$key old:$old");
                }
            }
        }
    }
    return $diff;
}



function vanish_scalar(&$env, &$state, $index, $data, $diff)
{
    if ($data) {
        $names = &$env['names'];
        $times = &$env['times'];
        $vals  = &$env['vals'];
        $grps  = &$env['grps'];

        $now   = $times[$index];
        $dnow  = date('m/d H:i:s', $now);

        debug_note("vanish scalar $index $now ($dnow)");
        reset($data);
        foreach ($data as $did => $d) {
            $name = $env['names'][$did]['name'];
            $new  = @$state[0][$did];
            if ($new == '') {
                debug_note("vanish $index scalar did:$did ($name)");
                $tmp['gid'] = 0;
                $tmp['did'] = $did;
                $tmp['ord'] = 1;
                $tmp['key'] = "did_$did";
                $tmp['new'] = '';
                $tmp['old'] = $vals[$did][$d];
                $tmp['time'] = $now;
                $diff[] = $tmp;
            } else {
                debug_note("change $index scalar did:$did ($name)");
            }
        }
    }
    return $diff;
}




function delta_scalar(&$env, $time, $data, $diff)
{
    if ($data) {
        $vals = &$env['vals'];

        $tmp['gid'] = 0;
        $tmp['key'] = '';
        $tmp['ord'] = 1;
        $tmp['time'] = $time;

        reset($data);
        foreach ($data as $did => $d) {
            $tmp['did'] = $did;
            $name = $env['names'][$did]['name'];
            $old  = ($d) ? $vals[$did][$d - 1] : '';
            $val  = $vals[$did][$d];

            if ($old != $val) {
                $tmp['key'] = "did_$did";
                $tmp['old'] = $old;
                $tmp['new'] = $val;
                $diff[] = $tmp;
            }
        }
    }
    return $diff;
}


/*
    |  Remove all the items from state which are about to expire.
    |  This means that they are recorded in the xmax array as ending
    |  at time $times[$index]
    */

function purge_state(&$env, &$state, $index)
{
    $times = $env['times'];
    $xmax  = $env['xmax'];
    $now   = $times[$index];

    $that  = array();

    if (isset($xmax[$now])) {
        $that = $xmax[$now];
    }

    if ($that) {
        $dnow = date('m/d H:i:s', $now);
        debug_note("purge state time $index $now ($dnow)");
        reset($that);
        foreach ($that as $gid => $data) {
            if ($gid) {
                reset($data);
                foreach ($data as $ord => $dids) {
                    reset($dids);
                    foreach ($dids as $did => $d) {
                        //          debug_note("expire index:$index gid:$gid did:$did ord:$ord d:$d");
                        unset($state[$gid][$did][$ord]);
                    }
                }
            } else {
                reset($data);
                foreach ($data as $did => $d) {
                    //      debug_note("expire index:$index did:$did d:$d");
                    unset($state[0][$did]);
                }
            }
        }
    }
}



/*
    |  Add in the new records that first show up at time ($times[$index])
    */

function update_state(&$env, &$state, $index)
{
    $times = &$env['times'];
    $grps  = &$env['grps'];
    $vals  = &$env['vals'];
    $now   = $times[$index];
    $dnow  = date('m/d H:i:s', $now);

    $what  = array();
    $that  = array();

    debug_note("update state time $index $now ($dnow)");
    if (isset($grps[$now])) {
        $what = $grps[$now];
    }

    if ($what) {
        reset($what);
        foreach ($what as $gid => $data) {
            if ($gid > 0) {
                reset($data);
                foreach ($data as $ord => $dids) {
                    reset($dids);
                    foreach ($dids as $did => $d) {
                        //             debug_note("init state[$did][$ord]");
                        $state[$gid][$did][$ord] = $vals[$did][$ord][$d];
                    }
                }
            } else {
                reset($data);
                foreach ($data as $did => $d) {
                    //         debug_note("init state[$did]");
                    $state[$gid][$did] = $vals[$did][$d];
                }
            }
        }
    }
}


function newform($link, $old, $new)
{
    $msg  = "<tr><th rowspan='2'>$link</th>\n";
    $msg .= "<td>Old</td><td>$old</td></tr>\n";
    $msg .= "<tr><td>New</td><td>$new</td>\n";
    $msg .= "</tr>\n";
    return $msg;
}



function diff_table(&$env, $head, &$diff, $mid)
{
    $base  = $env['base'];
    $link  = $env['link'];
    if ($env['cron']) {
        $base .= "/asset/";
    }
    $msg   = table_start();
    $msg  .= table_head($head, 3);

    $gids = array();
    $keys = array();

    reset($diff);
    foreach ($diff as $k => $data) {
        $did  = $data['did'];
        $gid  = $data['gid'];
        $ord  = $data['ord'];
        $key  = $data['key'];
        $old  = $data['old'];
        $new  = $data['new'];
        $time = $data['time'];

        if ($gid) {
            $gids[$gid][$ord][$did]['old'] = $old;
            $gids[$gid][$ord][$did]['new'] = $new;
            $keys[$gid][$ord] = $key;
            //      debug_note("gids[$gid][$ord][$did][old] = $old");
            //      debug_note("gids[$gid][$ord][$did][new] = $new");
        } else {
            $name = $env['names'][$did]['name'];
            $old  = table_filter($old);
            $new  = table_filter($new);
            $href = "detail.php?mid=$mid&did=$did&when=$time";
            $text = server_link($env, $name, $href);
            //              $list = array($text,$old,$new);
            //              $msg .= asset_data($list);
            $msg .= newform($text, $old, $new);
        }
    }

    reset($gids);
    foreach ($gids as $gid => $ords) {
        reset($ords);
        foreach ($ords as $ord => $dids) {
            $key = $keys[$gid][$ord];
            if ($key != '') {
                $name = $env['names'][$gid]['name'];
                $href = "detail.php?mid=$mid&gid=$gid&ord=$ord&when=$time";
                $text = server_link($env, $name, $href);
                $key  = table_filter($key);
                $msg .= "<tr><th>$text</th>\n<td colspan='2'>$key</td></tr>\n";
            }
            reset($dids);
            foreach ($dids as $did => $data) {
                //                debug_note("group table gid:$gid, did:$did, ord:$ord when:$time key:$key");
                $old  = table_filter($data['old']);
                $new  = table_filter($data['new']);
                $name = $env['names'][$did]['name'];
                $href = "detail.php?mid=$mid&gid=$gid&ord=$ord&when=$time";
                $text = server_link($env, $name, $href);
                $list = array($text, $old, $new);
                //                  $msg .= asset_data($list);
                $msg .= newform($text, $old, $new);
            }
        }
    }
    $msg .= table_end();
    return $msg;
}



function delta(&$env, &$state, $index, $time, $mid, $umin, $umax)
{
    $grps  = &$env['grps'];
    $times = &$env['times'];
    $hosts = &$env['hosts'];
    $vals  = &$env['vals'];

    $base  = $env['base'];
    $xmax  = $env['xmax'];
    $smax  = $env['smax'];
    $smin  = $env['smin'];
    $link  = $env['link'];

    if ($env['cron']) {
        $base .= '/asset/';
    }


    $diff  = array();
    $txt   = '';

    $vmin = $smin;
    $vmax = $smax;

    if (($umin > 0) && ($smin <= $umin)) $vmin = $umin;
    if (($umax > 0) && ($umax <= $vmax)) $vmax = $umax;

    if ($index > 0) {
        if (isset($grps[$time])) {
            $what = $grps[$time];
            reset($what);
            foreach ($what as $gid => $data) {
                if ($gid)
                    $diff = delta_group($env, $state, $index, $gid, $data, $diff);
                else
                    $diff = delta_scalar($env, $time, $data, $diff);
            }
        }
        purge_state($env, $state, $index - 1);
    }

    update_state($env, $state, $index);

    if ($index > 0) {
        $last = $times[$index - 1];
        if (isset($xmax[$last])) {
            $that = $xmax[$last];
            reset($that);
            foreach ($that as $gid => $data) {
                if ($gid)
                    $diff = vanish_group($env, $state, $index - 1, $gid, $data, $diff);
                else
                    $diff = vanish_scalar($env, $state, $index - 1, $data, $diff);
            }
        }
    }


    /* 
        |   Note, time must be strictly greather than the
        |   start time, since we don't want to see a change
        |   at time vmin.
        */

    if (($index > 0) && ($vmin < $time) && ($time <= $vmax)) {
        $host = $hosts[$mid]['host'];
        $last = $times[$index - 1];
        $txt .= host_header($host, $last, $time);

        if ($diff) {
            $diff = diff_merge($diff);

            $date = datestring($time);
            $href = "detail.php?mid=$mid&when=$time";
            $lnk  = server_link($env, $date, $href);
            $txt .= diff_table($env, $lnk, $diff, $mid);
        } else {
            $txt .= "<p>No changes found.</p><br>\n";
        }
        $txt .= clear_rows();
    }
    return $txt;
}


/*
    |  This is only used for debugging,
    |  Display this list of log times.
    */

function machine_times(&$env, $mid)
{
    $times = &$env['times'];

    $debug = $env['debug'];
    $host  = $env['hosts'][$mid]['host'];
    $base  = $env['base'];
    if ($env['cron']) {
        $base .= '/asset/';
    }
    if (($times) && ($debug)) {
        $href = $base . "detail.php?mid=$mid";
        $link = html_link($href, $host);
        $list = array('<br>', $link);
        echo table_start();
        echo table_head($link, 3);

        reset($times);
        foreach ($times as $i => $t) {
            $text = datestring($t);
            $href = $base . "detail.php?mid=$mid&when=$t";
            $link = html_link($href, $text);
            $list = array($i, $t, $link);
            echo asset_data($list);
        }
        echo table_end();
        echo clear_rows();
    }
}




/*
    |   umin: user specified min time, zero signifies first log time.
    |   umax: user specified max time, zero signifies last log time.
    |   smin: server time of host introduction.
    |   smax: server time of last log time.
    |   vmin: first valid time in interval.
    |   vmax: last valid time within interval.
    |   rmin: most recent log time before specified interval
    |   rmax: first log time after specified interval
    |   logs: count of logs strictly within valid period.
    |
    |       0 < smin <= rmin <= vmin <= vmax <= rmax <= smax
    |       0 <= umin <= umax
    |       
    */

function surveytime($mid, $umin, $umax, $smin, $smax, &$vmin, &$vmax, &$rmin, &$rmax, &$logs, $db)
{
    $logs = 0;
    $vmin = $smin;
    $vmax = $smax;
    $rmin = $smin;
    $rmax = $smax;

    if ($smin < $smax) {
        if (($smin <= $umin) && ($umin <= $smax))
            $vmin = $umin;
        if (($smin <= $umax) && ($umax <= $smax))
            $vmax = $umax;
        if ((0 < $umax) && ($umax < $smin))     // before birth
            $vmax = $smin;
        if ((0 < $umin) && ($smax < $umin))     // after death
            $vmin = $smax;
    }
    if ($vmin < $vmax) {
        $times = asset_times($mid, $smin, $smax, $db);
        reset($times);
        foreach ($times as $key => $time) {
            if (($rmin < $time) && ($time < $vmin)) {
                $rmin = $time;  // just before vmin
            }
            if (($vmax < $time) && ($time < $rmax)) {
                $rmax = $time;  // just after vmax
            }
            if (($vmin <= $time) && ($time <= $vmax)) {
                $logs++;
            }
        }
    }
}



/*
    |  This can use a lot of memory. 
    */

function machine_changes(&$env, $mid, $umin, $umax)
{
    debug_note("machine_changes(env,$mid,$umin,$umax)");

    $db    = $env['db'];
    $smin  = $env['hosts'][$mid]['searliest'];
    $smax  = $env['hosts'][$mid]['slatest'];
    $host  = $env['hosts'][$mid]['host'];
    $slow  = $env['slow'];
    $dbid  = $env['dbid'];

    $env['smin'] = $smin;
    $env['smax'] = $smax;

    /*
        |  The specified time interval must at last 
        |  partially overlap the lifespan of the host.
        */

    $logs = 0;
    $vmin = $smin;
    $vmax = $smax;
    $rmin = $smin;
    $rmax = $smax;

    surveytime($mid, $umin, $umax, $smin, $smax, $vmin, $vmax, $rmin, $rmax, $logs, $db);

    if ($logs <= 0) {
        debug_note("$host: no logs in interval");
        return unchanged($host, $umin, $umax, $smin, $smax, $rmin, $rmax);
    }

    $xmax  = array();
    $vals  = array();
    $grps  = array();
    $lead  = array();
    $ktime = array();
    $times = array();

    /*
        |  Specified interval is time from vmin to vmax.
        |
        |  We can exclude exactly two groups:
        |
        |  1. slatest < rmin
        |      departed before specified interval.  
        |  2. sobserved > vmax
        |      arrived after specified interval.
        */

    $sql   = "select * from AssetData\n";
    $sql  .= " where machineid = $mid and\n";
    $sql  .= " $rmin <= slatest and\n";
    $sql  .= " sobserved <= $vmax\n";
    $sql  .= " order by sobserved";
    $res   = asset_time_query($sql, $slow, $dbid, $db);

    $index = 0;
    $txt   = '';
    if ($res) {
        $index = mysqli_num_rows($res);
        while ($row = mysqli_fetch_assoc($res)) {
            $did = $row['dataid'];
            $ord = $row['ordinal'];
            $val = $row['value'];
            $obs = $row['sobserved'];
            $max = $row['slatest'];
            $gid = $env['names'][$did]['groups'];
            $ldr = $env['names'][$did]['leader'];
            if ($gid > 0) {
                $vals[$did][$ord][] = $val;
                $when[$did][$ord][] = $obs;
                $n = safe_count($vals[$did][$ord]) - 1;
                $xmax[$max][$gid][$ord][$did] = $n;
                $grps[$obs][$gid][$ord][$did] = $n;
                if ($ldr) $lead[$gid] = $did;
            } else {
                $vals[$did][] = $val;
                $n = safe_count($vals[$did]) - 1;
                $xmax[$max][$gid][$did] = $n;
                $grps[$obs][$gid][$did] = $n;
            }
            $ktime[$obs] = true;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    debug_note("$index records loaded");

    if ($ktime) {
        reset($ktime);
        foreach ($ktime as $key => $data) {
            $times[] = $key;
        }
        $times = sort_unique($times);
        $ktime = array();
        foreach ($times as $key => $data) {
            $ktime[$data] = $key;
        }
    }


    $n = safe_count($times);
    if ($n > 1) {
        $state = array();

        $env['times'] = &$times;
        $env['ktime'] = &$ktime;

        $env['vals'] = &$vals;
        $env['xmax'] = &$xmax;
        $env['grps'] = &$grps;
        $env['lead'] = &$lead;

        machine_times($env, $mid);

        reset($times);
        foreach ($times as $key => $data) {
            $txt .= delta($env, $state, $key, $data, $mid, $umin, $umax);
        }

        if ($txt == '') {
            $txt = unchanged($host, $umin, $umax, $smin, $smax, $rmin, $rmax);
        }

        unset($state);
        unset($env['times']);
        unset($env['ktime']);

        unset($env['vals']);  // forget this one
        unset($env['grps']);  // we need the memory.
        unset($env['xmax']);
        unset($env['lead']);
    } else {
        $txt .= "<p>Machine <b>$host</b> has not logged enough times.</p>\n";
    }
    return $txt;
}



function machine_state(&$env, $sql, $time)
{
    $db    = $env['db'];
    $slow  = $env['slow'];
    $dbid  = $env['dbid'];

    $temp = array();
    $vals = array();
    $xmax = array();
    $xobs = array();
    $lead = array();
    $numr = 0;
    $res  = asset_time_query($sql, $slow, $dbid, $db);

    if ($res) {
        $numr = mysqli_num_rows($res);
        while ($row = mysqli_fetch_assoc($res)) {
            $did = $row['dataid'];
            $ord = $row['ordinal'];
            $val = $row['value'];
            $obs = $row['sobserved'];
            $max = $row['slatest'];
            $gid = $env['names'][$did]['groups'];
            $ldr = $env['names'][$did]['leader'];
            if ($gid > 0) {
                $vals[$gid][$ord][$did] = $val;
                $xmax[$gid][$ord][$did] = $max;
                $xobs[$gid][$ord][$did] = $obs;
                if ($ldr) $lead[$gid] = $did;
            } else {
                $vals[$gid][$did] = $val;
                $xmax[$gid][$did] = $max;
                $xobs[$gid][$did] = $obs;
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    $temp['vals'] = $vals;
    $temp['lead'] = $lead;
    $temp['time'] = $time;
    $temp['numr'] = $numr;
    $temp['xmax'] = $xmax;
    $temp['xobs'] = $xobs;
    return $temp;
}


function compare_state(&$env, &$ostate, &$nstate)
{
    $otime = $ostate['time'];
    $ntime = $nstate['time'];
    $omax  = &$ostate['xmax'];
    $nmax  = &$nstate['xmax'];
    $nobs  = &$nstate['xobs'];
    $oval  = &$ostate['vals'];
    $nval  = &$nstate['vals'];
    $lead  =  $ostate['lead'];
    $nldr  = &$nstate['lead'];

    reset($nldr);
    foreach ($nldr as $gid => $did) {
        debug_note("lead[$gid] = $did");
        $lead[$gid] = $did;
    }


    debug_note("compare_state: otime:$otime ntime:$ntime");

    $diff = array();
    $tmp  = array();

    $tmp['old']  = '';
    $tmp['time'] = $ntime;

    reset($nval);
    foreach ($nval as $gid => $ords) {
        $tmp['gid'] = $gid;

        if ($gid) {
            $lid = @intval($lead[$gid]);

            reset($ords);
            foreach ($ords as $ord => $dids) {
                $qqq = diff_group($env, $gid, $ord, $dids, $oval);
                if ($qqq) {
                    $name = $env['names'][$gid]['name'];
                    debug_note("group $gid ($name) $ord used to be $qqq");
                } else {
                    if ($lid > 0) {
                        $key = @$nval[$gid][$ord][$lid];
                    } else
                        $key = '';

                    reset($dids);
                    foreach ($dids as $did => $new) {
                        $tmp['did'] = $did;
                        $obs = $nobs[$gid][$ord][$did];
                        if ($obs > $otime) {
                            debug_note("gid:$gid did:$did ord:$ord key:$key new:$new");
                            $tmp['ord'] = $ord;
                            $tmp['new'] = $new;
                            $tmp['key'] = $key;
                            $diff[] = $tmp;
                        }
                    }
                }
            }
        } else {
            $tmp['ord'] = 1;
            $tmp['key'] = '';
            reset($ords);
            foreach ($ords as $did => $new) {
                $obs = $nobs[$gid][$did];
                if ($obs > $otime) {
                    $tmp['did'] = $did;
                    $tmp['key'] = "did_$did";
                    $tmp['new'] = $new;
                    $diff[] = $tmp;
                    //          $name = $env['names'][$did]['name'];
                    //          debug_note("did:$did ($name) new:$new old:$old");
                }
            }
        }
    }

    $tmp['new'] = '';
    $tmp['time'] = $otime;

    reset($oval);
    foreach ($oval as $gid => $ords) {
        $tmp['gid'] = $gid;
        reset($ords);
        if ($gid) {
            $lid = @intval($lead[$gid]);

            $name = $env['names'][$gid]['name'];
            foreach ($ords as $ord => $dids) {
                $qqq = diff_group($env, $gid, $ord, $dids, $nval);
                if ($qqq) {
                    debug_note("group $gid ($name) $ord becomes $qqq");
                } else {
                    //      debug_note("group $gid $name ord:$ord seems to be new");
                    if ($lid > 0) {
                        $key = @$oval[$gid][$ord][$lid];
                    } else
                        $key = '';

                    reset($dids);
                    foreach ($dids as $did => $old) {
                        $max = @$omax[$gid][$ord][$did];
                        if ((0 < $max) && ($max < $ntime)) {
                            $tmp['did'] = $did;
                            $tmp['ord'] = $ord;
                            $tmp['old'] = $old;
                            $tmp['key'] = $key;
                            $diff[] = $tmp;
                            debug_note("gid:$gid did:$did ord:$ord key:$key old:$old");
                        }
                    }
                }
            }
        } else {
            $tmp['ord'] = 1;
            $tmp['key'] = '';
            foreach ($ords as $did => $old) {
                $max = @$omax[$gid][$did];
                if ((0 < $max) && ($max < $ntime)) {
                    $tmp['did'] = $did;
                    $tmp['old'] = $old;
                    $tmp['key'] = "did_$did";
                    $diff[] = $tmp;
                    //                 $name = $env['names'][$did]['name'];
                    //                 debug_note("did:$did ($name) new:$new old:$old");
                }
            }
        }
    }

    return $diff;
}

function count_records($sql, $db)
{
    $num = 0;
    $res = redcommand($sql, $db);
    if ($res) {
        $num = mysqli_result($res, 0);
    }
    return $num;
}


function asset_times($mid, $smin, $smax, $db)
{
    $times = array();
    $sql   = "select distinct sobserved,slatest\n";
    $sql  .= " from AssetData\n";
    $sql  .= " where machineid = $mid";
    $res   = redcommand($sql, $db);
    if ($res) {
        $time = array($smin, $smax);
        while ($row = mysqli_fetch_assoc($res)) {
            $time[] = $row['sobserved'];
            $time[] = $row['slatest'];
        }
        $times = sort_unique($time);
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $times;
}



/*
    |  Note ... start time must be strictly less then end time.
    |
    |  The indicated time range must at least partially overlap with 
    |  the lifespan of of the data.
    */

function machine_diff(&$env, $mid, $umin, $umax)
{
    $db    = $env['db'];
    $smin  = $env['hosts'][$mid]['searliest'];
    $smax  = $env['hosts'][$mid]['slatest'];
    $host  = $env['hosts'][$mid]['host'];

    $env['smin'] = $smin;
    $env['smax'] = $smax;

    $diff  = array();
    $txt   = '';

    /*
        |  The specified time interval must at last 
        |  partially overlap the lifespan of the host.
        */

    $logs = 0;
    $vmin = $smin;
    $vmax = $smax;
    $rmin = $smin;
    $rmax = $smax;

    surveytime($mid, $umin, $umax, $smin, $smax, $vmin, $vmax, $rmin, $rmax, $logs, $db);

    if ($logs <= 0) {
        debug_note("$host: no logs in interval");
        $hmin = ($umin) ? $umin : $vmin;
        $hmax = ($umax) ? $umax : $vmax;
        $txt .= host_header($host, $hmin, $hmax);
        $txt .= unchanged($host, $umin, $umax, $smin, $smax, $rmin, $rmax);
        return $txt;
    }


    $sql = "select * from AssetData\n where machineid = $mid";

    if ($vmin > $smin)
        $osql = "$sql\n and searliest <= $vmin\n and $vmin <= slatest";
    else
        $osql = "$sql\n and sobserved = $smin";

    if ($vmax < $smax)
        $nsql = "$sql\n and searliest <= $vmax\n and $vmax <= slatest";
    else
        $nsql = "$sql\n and slatest = $smax";

    if ($vmin < $vmax) {
        $ostate = machine_state($env, $osql, $vmin);
        $nstate = machine_state($env, $nsql, $vmax);

        $odate = datestring($vmin);
        $ndate = datestring($vmax);
        $onum  = $ostate['numr'];
        $nnum  = $nstate['numr'];

        debug_note("$host: $odate: $onum records");
        debug_note("$host: $ndate: $nnum records");

        $diff = compare_state($env, $ostate, $nstate);
        unset($ostate);
        unset($nstate);
    }

    if ($diff) {
        $diff = diff_merge($diff);

        $txt .= host_header($host, $vmin, $vmax);

        $txt .= diff_table($env, $host, $diff, $mid);
    } else {
        $hmin = ($umin) ? $umin : $vmin;
        $hmax = ($umax) ? $umax : $vmax;
        $txt .= host_header($host, $hmin, $hmax);
        $txt .= unchanged($host, $umin, $umax, $smin, $smax, $rmin, $rmax);
    }
    return $txt;
}


function machine_header(&$row)
{
    $mid  = $row['machineid'];
    $host = $row['host'];
    $site = $row['cust'];
    $name = ucfirst($host);

    debug_note("mid:$mid, host:$host, cust:$site");
    $txt  = mark("machine_$mid");
    $txt .= "\n\n<h2>$name at $site</h2>\n";
    return $txt;
}


function machine_index(&$mach)
{
    $txt = '';
    if ($mach) {
        $num  = safe_count($mach);
        $txt .= mark('index');
        $txt .= "<h2>Index</h2>\n";
        $txt .= show_description("$num machines found.");
        $txt .= table_start();
        reset($mach);
        foreach ($mach as $key => $row) {
            $mid  = $row['machineid'];
            $host = $row['host'];
            $site = $row['cust'];
            $smin = $row['searliest'];
            $smax = $row['slatest'];
            $dmin = date('m/d H:i', $smin);
            $dmax = date('m/d H:i', $smax);
            $tag  = "#machine_$mid";
            $link = marklink($tag, $host);
            $args = array($link, $site, $dmin, $dmax);
            $txt .= asset_data($args);
        }
        $txt .= table_end();
    }
    return $txt;
}


function machine_list(&$env, &$mach, $umin, $umax, $log)
{
    $txt = '';
    if ($mach) {
        $txt .= machine_index($mach);
        reset($mach);
        foreach ($mach as $key => $row) {
            $mid  = $row['machineid'];
            $txt .= machine_header($row);
            if ($log)
                $txt .= machine_changes($env, $mid, $umin, $umax);
            else
                $txt .= machine_diff($env, $mid, $umin, $umax);
        }
        $txt .= jumptable('top,bottom,index');
    } else {
        $txt = "Machine list is empty.";
        $txt = fontspeak($txt);
        $txt = "\n<p>$txt</p>\n";
    }
    return $txt;
}
