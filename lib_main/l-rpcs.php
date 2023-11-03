<?php



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



function rpc_encode($s)
{

    settype($s, 'string');
    $l = strlen($s);
    $r = ($l) ? $s : '\0';
    if (($l > 0) && ($s[0] == '\\')) {
        $r = '\\' . $s;
    }
    return $r;
}




function rpc_decode($s)
{
    $r = $s;
    $l = strlen($s);
    if (($l > 0) && ($s[0] == '\\')) {
        $r = '';
        if ((2 <= $l) && ($s[1] == '\\'))
            $r = substr($s, 1);
        else if (($l != 2) || ($s[1] != '0')) {
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



function parse_alist(&$arg, $min)
{
    $vars = array();
    $temp = array();
    $errs = '';

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
    }
    return $vars;
}



function fully_parse_alist(&$strList)
{
    $arr = explode('#', $strList);
    $pos = 0;
    $aList = extract_alist($arr, $errs, $pos, safe_count($arr));
    if ($errs) {
        debug_parse("errs: $errs");
        $aList = array();
    }
    return $aList;
}




function extract_alist(&$itemArr, &$errs, &$pos, $max)
{
    $aList = array();
    $errs = '';

    if ($pos < $max) {
        $count = $itemArr[$pos++];

        for ($item = 0; $item < $count; $item++) {
            if ($pos >= $max) {
                $errs = "missing name";
                break;
            }

            $name = double_decode($itemArr[$pos++]);
            if ($pos >= $max) {
                $errs = "missing type";
                break;
            }
            $type = $itemArr[$pos++];

            switch ($type) {
                case 'UINT32':
                    if ($pos >= $max) {
                        $errs = "missing UINT32 value";
                        break 2;
                    }
                    $val = intval($itemArr[$pos++]);
                    break;
                case 'PSTRING':
                    if ($pos >= $max) {
                        $errs = "missing PSTRING value";
                        break 2;
                    }
                    $val = double_decode($itemArr[$pos++]);
                    break;
                case 'PALIST':
                    $val = extract_alist($itemArr, $errs, $pos, $max);
                    if ($errs) {
                        break 2;
                    }

                    $pos++;
                    break;
                default:
                    $errs = "unknown type $type";
                    break 2;
            }


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
