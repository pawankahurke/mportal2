<?php

/*
Revision history:

Date        Who     What
----        ---     ----
28-May-03   MMK     Original creation.
25-Jun-03   EWB     Reinstate logging code.
25-Jun-03   EWB     Better overflow detection.
30-Oct-03   EWB     Pass large arrays by reference
21-Nov-03   MMK     Double encode, decode names in fully_make/parse_alist
                    to match client.
 2-Dec-03   AAM     Handle unnamed alist items.
22-Apr-04   EWB     Improved redcommand display.
*/

function debug_msg($color, $msg)
{
    $txt = str_replace("\n", "<br>\n&nbsp;&nbsp;&nbsp;", $msg);
    echo "<b><font color=\"$color\">$txt</font></b><br>\n";
}

function debug_code($code, $color, $msg)
{
    $display = @$GLOBALS[$code];
    if ($display) {
        debug_msg($color, $msg);
    }
}

function debug_note($msg)
{
    debug_code('debug', 'green', $msg);
}

function debug_parse($msg)
{
    debug_code('debug_parse', 'cyan', $msg);
}

/*
    |  This is for strings coming from the server going to the
    |  client.  The rules are:
    |
    |  1. If the string is empty, return '\0';
    |  2. If the string starts with a backslash, double it.
    |  3. otherwise return the string unchanged.
    |
    |  Backslashes do *NOT* need to be quoted in a single quoted
    |  string *UNLESS* they are before a single quote or at the
    |  end of the string (or both).
    */

function rpc_encode($s)
{
    /* If it isn't a string, make it one first. */
    settype($s, 'string');
    $l = strlen($s);
    $r = ($l) ? $s : '\0';
    if (($l > 0) && ($s[0] == '\\')) {
        $r = '\\' . $s;
    }
    return $r;
}


/*
    |  This should be used for decoding strings coming in from the client.
    |  It represets empty strings as '\0' and null pointers as ''.
    |
    |  The problem with this is that php doesn't really have a simple
    |  way to represent a null pointer.  So, at the moment, we'll
    |  let the server represent both of these as an empty string.
    |
    |  The other thing is that if the client sends us a string with a
    |  leading backslash backslash, we should strip off the first
    |  backslash and store the rest of the string.
    |
    |  http://www.php.net/manual/en/function.substr.php
    |  http://www.php.net/manual/en/printwn/language.types.string.php
    */

function rpc_decode($s)
{
    $r = $s;
    $l = strlen($s);
    if (($l > 0) && ($s[0] == '\\')) {
        $r = '';
        if ((2 <= $l) && ($s[1] == '\\'))
            $r = substr($s, 1);
        else if (($l != 2) || ($s[1] != '0')) {
            logs::log(__FILE__, __LINE__, "rpc_decode: invalid string: $s", 0);
        }
    }
    return $r;
}

function double_decode($s)
{
    return rpc_decode(urldecode($s));
}

function double_encode($s)
{
    return urlencode(rpc_encode($s));
}

/*
    |   Parses an ALIST containing a list of ALISTS.
    |
    |   Each of the lower level lists may contain
    |   as many of any scaler data you like.
    |
    |   t -- type
    |   v -- value
    |   r -- revision
    |   s -- state  (0: global, 1: local, 2: local only)
    |
    |   $vars['S00088DeviceList']['tv'] = 'PSTRING';
    |   $vars['S00088DeviceList']['vv'] = ',nanoheal.org,ICMP,0,4,1000';
    |   $vars['S00088DeviceList']['tt'] = 'PSTRING';
    |   $vars['S00088DeviceList']['vt'] = 'string';
    |   $vars['S00088DeviceList']['tr'] = 'UINT32';
    |   $vars['S00088DeviceList']['vr'] = '0';
    |   $vars['S00088DeviceList']['ts'] = 'UINT32';
    |   $vars['S00088DeviceList']['vs'] = '0';
    */

function parse_alist(&$arg, $min)
{
    $vars = array();
    $temp = array();
    $errs = '';
    /* Using "explode" instead of "split" runs nearly 100 times faster. */
    $s = explode('#', $arg);
    $n = safe_count($s);
    $i = 0;
    if ($n < $min) {
        debug_parse("n:$n, smaller than expected");
        return $vars;
    }
    $vmax = intval($s[$i++]);
    if ($vmax < 1) {
        debug_parse("vmax:$vmax, smaller than expected");
        return $vars;
    }
    debug_parse("vmax:$vmax, n:$n");
    //  logs::log(__FILE__, __LINE__, "parse_alist: vmax:$vmax, n:$n");
    for ($v = 0; $v < $vmax; $v++) {
        $desc  = array();
        $imax  = $n + 1;
        if ($i + 3 < $n) {
            $vname = $s[$i++];
            $vtype = $s[$i++];
            $dmax  = $s[$i++];
            $dmax  = intval($dmax);
            $imax  = ($dmax * 3) + $i;
        }
        if ($n <= $imax) {
            $vars = array();
            $errs = "overflow i:$i, dmax:$dmax, n:$n, imax:$imax";
            debug_parse($errs);
            logs::log(__FILE__, __LINE__, "parse_alist:$errs", 0);
            return $vars;
        }

        for ($d = 0; $d < $dmax; $d++) {
            $nn = $s[$i++];
            $tt = $s[$i++];
            $vv = $s[$i++];
            $tnn = "t$nn";
            $vnn = "v$nn";
            $desc[$tnn] = $tt;
            $desc[$vnn] = $vv;
        }
        $delim = $s[$i++];
        $vars[$vname] = $desc;
        debug_parse("vname:$vname, vtype:$vtype, dmax:$dmax, nn:$nn, tt:$tt, vv:$vv, i:$i");
        if ($vtype != 'PALIST') {
            $errs = "wrong vtype:$vtype";
        }
        if ($delim) {
            $errs = "bad variable delimiter $delim";
        }
    }
    $delim = $s[$i++];
    if ($i != $n) {
        $errs = "param mismatch i:$i, n:$n";
    }
    if ($errs) {
        $vars = array();
        debug_parse("errs:$errs");
        logs::log(__FILE__, __LINE__, "parse_alist:$errs", 0);
    }
    return $vars;
}


/* fully_parse_alist
    Take an ALIST "strList" that came in from outside, and convert it into
    an array where each item is indexed by the name of the ALIST item, and
    whose value is the value of the ALIST item.
*/
function fully_parse_alist(&$strList)
{
    $arr = explode('#', $strList);
    $pos = 0;
    $aList = extract_alist($arr, $errs, $pos, safe_count($arr));
    if ($errs) {
        logs::log(__FILE__, __LINE__, "fully_parse_alist:$errs", 0);
        debug_parse("errs: $errs");
        $aList = array();
    }
    return $aList;
}



/* extract_alist
    Helper function for fully_parse_alist.  Take an exploded array "itemArr"
    and, starting at position "pos", interpret it as an ALIST from outside,
    returning an array form of the ALIST data.  "max" is the total number of
    items in "itemArr".  Return "errs" as non-empty, with an error message,
    if any errors are encountered.  Update "pos" to point to the next item
    after the ALIST is parsed.  Note that this function uses itself recursively
    to parse ALISTs that are items in the ALIST.

    Note that the first parameter really needs to be passed by reference.  We
    don't ever change it but it can be a really big array and if we start
    recursing into alists, this makes a big performance difference.
*/
function extract_alist(&$itemArr, &$errs, &$pos, $max)
{
    $aList = array();
    $errs = '';
    /* Get the count of items in the ALIST. */
    if ($pos < $max) {
        $count = $itemArr[$pos++];
        /* For each item, get the name, type, and value. */
        for ($item = 0; $item < $count; $item++) {
            if ($pos >= $max) {
                $errs = "missing name";
                break;
            }
            /* the name is also encoded */
            $name = double_decode($itemArr[$pos++]);
            if ($pos >= $max) {
                $errs = "missing type";
                break;
            }
            $type = $itemArr[$pos++];
            /* Getting the value depends on what type it is. */
            switch ($type) {
                case 'UINT32':
                    if ($pos >= $max) {
                        $errs = "missing UINT32 value";
                        break 2;    /* exit switch and for */
                    }
                    $val = intval($itemArr[$pos++]);
                    break;
                case 'PSTRING':
                    if ($pos >= $max) {
                        $errs = "missing PSTRING value";
                        break 2;    /* exit switch and for */
                    }
                    $val = double_decode($itemArr[$pos++]);
                    break;
                case 'PALIST':
                    $val = extract_alist($itemArr, $errs, $pos, $max);
                    if ($errs) {
                        break 2;    /* exit switch and for */
                    }
                    /* Skip the trailing terminator for the ALIST */
                    $pos++;
                    break;
                default:
                    $errs = "unknown type $type";
                    break 2;    /* exit switch and for */
            }

            /* Now put the item into the array.  Handle unnamed
                    items specially. */
            if ($name == '') {
                $aList[] = $val;
            } else {
                $aList[$name] = $val;
            }
        }
    } else {
        $errs = "missing count";
    }
    return $aList;
}


/* fully_make_alist
    This is the inverse of fully_parse_alist.  Take the array "aList" and
    return a string value representing it that can be passed to the outside
    world.  Note that this function calls itself recursively to put together
    ALISTs that have ALISTs as members.
    I'm not sure how PHP stores strings internally:  if it doesn't keep a
    pointer to the tail, then the repeated concatenations here will turn this
    into an n-squared operation.
*/
function fully_make_alist($aList)
{
    $retVal = safe_count($aList) . '#';
    reset($aList);
    foreach ($aList as $name => $val) {
        $retVal .= double_encode($name) . '#';
        if (is_int($val)) {
            $retVal .= 'UINT32#' . $val . '#';
        } else if (is_array($val)) {
            $retVal .= 'PALIST#' . fully_make_alist($val) . '#';
        } else {
            $retVal .= 'PSTRING#' . double_encode($val) . '#';
        }
    }
    return $retVal;
}
