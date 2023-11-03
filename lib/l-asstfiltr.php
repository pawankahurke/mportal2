<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once 'l-util.php';

$local_nav = '';




function datestring($utime)
{
    return date('D M d H:i:s T Y', $utime);
}

function fontspeak($msg)
{
    return "<font face=\"verdana,helvetica\" size=\"2\">$msg</font>";
}

function html_link($href, $text)
{
    $id = strtolower($text);
    return "<a href=\"$href\" id=\"$id\" class=\"html_eventsummary_subheader\">$text</a>";
}

function html_link_report($href, $text)
{
    return $text;
}

function html_target($href, $target, $text)
{
    return "<a href=\"$href\" target=\"$target\">$text</a>";
}

function html_target_asstreport($href, $target, $text)
{
    return "<a href=\"$href\" target=\"$target\" name=\"$text\">$text</a>";
}

function html_page($href, $text)
{
    return html_target($href, '_blank', $text);
}

function html_page_asstreport($href, $text)
{
    return html_target_asstreport($href, '_blank', $text);
}



function magic_unquote($quoted, $valu)
{
    if (is_array($valu)) {
        reset($valu);
        foreach ($valu as $key => $data) {
            $valu[$key] = magic_unquote($quoted, $data);
        }
    } else {
        // if (get_magic_quotes_gpc()) {
        //     if (!$quoted) {
        //         $valu = stripslashes($valu);
        //     }
        // } else {
        if ($quoted) {
            $valu = safe_addslashes($valu);
        }
        // }
    }
    return $valu;
}



function get_argument($name, $quoted, $default)
{
    $valu = $default;

    if ((isset($_GET)) || (isset($_POST))) {
        if (isset($_GET[$name]))
            $valu = $_GET[$name];
        if (isset($_POST[$name]))
            $valu = $_POST[$name];
    } else {
        if (isset($GLOBALS['HTTP_GET_VARS'][$name]))
            $valu = $GLOBALS['HTTP_GET_VARS'][$name];
        if (isset($GLOBALS['HTTP_POST_VARS'][$name]))
            $valu = $GLOBALS['HTTP_POST_VARS'][$name];
    }
    return magic_unquote($quoted, $valu);
}







function get_integer($name, $def)
{
    $tmp = get_argument($name, 0, '');
    return ($tmp == '') ? intval($def) : intval($tmp);
}

function get_string($name, $def)
{
    return trim(get_argument($name, 0, $def));
}



function UTIL_GetStoredInteger($name, $def)
{
    $tmp = UTIL_GetStoredVariable($name, $def);
    return intval($tmp);
}



function UTIL_GetStoredString($name, $def)
{
    $tmp = UTIL_GetStoredVariable($name, $def);
    return trim($tmp);
}



function UTIL_GetStoredVariable($name, $def)
{
    $tmp = get_argument($name, 0, '');
    if ($tmp == '') {
        if (@$_SESSION && isset($_SESSION[$name])) {
            $tmp = $_SESSION[$name];
        } else {
            $tmp = $def;
        }
    }

    return $tmp;
}



function UTIL_StoreSessionVariable($name, $value)
{
    $_SESSION[$name] = $value;
}



function UTIL_StoreEnvironment($env)
{
    foreach ($env as $key => $value) {
        UTIL_StoreSessionVariable($key, $value);
    }
}



function UTIL_ClearSession()
{
    $_SESSION = null;
}





function serveroptions($db)
{
    $opt = array();
    $sql = 'select * from Options';
    $res = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $name = $row['name'];
            $opt[$name] = $row['value'];
        }
        mysqli_free_result($res);
    }
    return $opt;
}



function find_opt($name, $db)
{
    $row = array();
    $qn = safe_addslashes($name);
    $sql = "select * from " . $GLOBALS['PREFIX'] . "core.Options where name = '$qn'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
        }
        mysqli_free_result($res);
    }
    return $row;
}



function opt_insert($name, $value, $ed, $db)
{
    $qn = safe_addslashes($name);
    $qv = safe_addslashes($value);
    $ed = ($ed) ? 1 : 0;
    $now = time();
    $sql = "insert into " . $GLOBALS['PREFIX'] . "core.Options set\n"
        . " name = '$qn',\n"
        . " value = '$qv',\n"
        . " editable = $ed,\n"
        . " modified = $now";
    $res = redcommand($sql, $db);
    return affected($res, $db);
}

function opt_update($name, $value, $ed, $db)
{
    $qn = safe_addslashes($name);
    $qv = safe_addslashes($value);
    $ed = ($ed) ? 1 : 0;
    $now = time();
    $sql = "update " . $GLOBALS['PREFIX'] . "core.Options set\n"
        . " value = '$qv',\n"
        . " editable = $ed,\n"
        . " modified = $now\n"
        . " where name = '$qn'";
    $res = command($sql, $db);
    return affected($res, $db);
}



function server_opt($name, $db)
{
    $row = find_opt($name, $db);
    return ($row) ? $row['value'] : '';
}



function server_def($name, $def, $db)
{
    $out = $def;
    $row = find_opt($name, $db);
    if ($row) {

        $tmp = $row['value'];
        if (($tmp) == ('')) {
            $out = $def;
        } else {
            $out = $tmp;
        }
    }
    return $out;
}



function server_int($name, $def, $db)
{
    $row = find_opt($name, $db);
    $val = ($row) ? $row['value'] : $def;
    return intval($val);
}



function update_opt($name, $valu, $db)
{
    $qn = safe_addslashes($name);
    $qv = safe_addslashes($valu);
    $sql = "update " . $GLOBALS['PREFIX'] . "core.Options\n"
        . " set value = '$qv'\n"
        . " where name = '$qn'";
    $res = command($sql, $db);
    return affected($res, $db);
}



function server_set($name, $value, $db)
{
    $old = '';
    $row = find_opt($name, $db);
    if ($row) {
        $old = $row['value'];
        opt_update($name, $value, 1, $db);
    } else {
        opt_insert($name, $value, 1, $db);
    }
    return $old;
}



function server_name($db)
{
    $host = server_opt('server_name', $db);
    if ($host == '') {

        $host = server_var('SERVER_NAME');
    }
    if (($host == 'localhost') || ($host == '')) {

        $fqdn = `/bin/hostname -f`;
        $host = str_replace("\n", '', $fqdn);
    }

    return $host;
}



function find_site_email($site, $db)
{
    $qs = safe_addslashes($site);
    $sql = "select notify_sender from " . $GLOBALS['PREFIX'] . "core.Customers\n"
        . " where username = '' and\n"
        . " customer = '$qs'";
    $res = find_one($sql, $db);
    if (isset($res['notify_sender'])) {
        return $res['notify_sender'];
    }
    logs::log(__FILE__, __LINE__, "l-serv: notify_sender is not set for site($site)", 0);
    return '';
}



function set_site_email($qs, $site_email, $db)
{
    $good = false;
    $site_email = safe_addslashes($site_email);

    $sql = "update Customers set\n"
        . " notify_sender  = '$site_email'\n"
        . " where username = ''\n"
        . " and customer   = '$qs'";
    $res = redcommand($sql, $db);
    if (affected($res, $db)) {
        $good = true;
    }
    return $good;
}



function build_custom_list($custom)
{
    $custom_list = array();
    $custom_string = get_string($custom, '');
    $custom_list = explode(',', $custom_string);
    $custom_list = array_flip($custom_list);
    return $custom_list;
}



function customize_name($name, &$custom_list, $text_type_start, $text_type_end)
{
    if (isset($custom_list[$name])) {
        $anch = "<a name=\"#$name\"></a>";
        return "$anch $text_type_start $name $text_type_end";
    } else {
        return $name;
    }
}



function find_user_option($user, $db)
{
    $row = array();
    $name = safe_addslashes($user);
    $sql = "select * from " . $GLOBALS['PREFIX'] . "core.Users where username = '$name'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
        }
        mysqli_free_result($res);
    }
    return $row;
}



function get_user_options($option, $user, $def, $db)
{
    $out = $def;
    $user_option = safe_addslashes($option);
    $row = find_user_option($user, $db);
    if ($row) {

        $tmp = $row[$user_option];
        if (($tmp) == ('')) {

            $out = $def;
        } else {

            $out = $tmp;
        }
    }
    return $out;
}



function SERV_GetLeveledOption($option, $user, $def, $db)
{
    $useglobal = '';
    switch ($option) {
        case 'disable_cache':
            $useglobal = 2;
            break;
        case 'jpeg_quality':
            $useglobal = 101;
            break;
        default:
            $useglobal = '';
            break;
    }

    $useropt = get_user_options($option, $user, $useglobal, $db);
    if ($useropt == $useglobal) {

        return server_def($option, $def, $db);
    }
    return $useropt;
}






function correct_hour($when, $hour)
{
    $tm = getdate($when);
    $hh = $tm['hours'];
    if ($hh != $hour) {
        $temp = $when;
        if ((($hh + 1) % 24) == $hour) {
            $temp = $when + 3600;
        }
        if ((($hh + 23) % 24) == $hour) {
            $temp = $when - 3600;
        }
        if ($temp != $when) {
            $tm = getdate($temp);
            $hh = $tm['hours'];
            if ($hh == $hour) {
                $when = $temp;
            }
        }
    }
    return $when;
}



function days_ago($time, $days)
{
    $date = getdate($time);
    $hour = $date['hours'];
    $when = $time - ($days * 86400);
    return correct_hour($when, $hour);
}



function months_ago($time, $months)
{
    $len = array(
        1 => 31,
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
        12 => 31
    );
    $tday = getdate($time);
    $dd = $tday['mday'];
    $mm = $tday['mon'];
    $yy = $tday['year'];
    $h = $tday['hours'];
    $m = $tday['minutes'];
    $s = $tday['seconds'];
    while ($months > 0) {
        if ($mm > 1)
            $mm--;
        else {
            $yy--;
            $mm = 12;
        }
        $months--;
    }
    if ($dd > $len[$mm]) {
        if ((($yy % 4) == 0) && (($yy % 400) != 0)) {
            $len[2] = 29;
        }
        while ($dd > $len[$mm]) {
            $dd--;
        }
    }



    $when = mktime($h, $m, $s, $mm, $dd, $yy);
    if ((!isset($when)) || ($when <= 0)) {
        if ($h < 23)
            $h++;
        $when = mktime($h, $m, $s, $mm, $dd, $yy);
    }
    return $when;
}



function midnight($tdate)
{
    $hour = (60 * 60);
    $tday = getdate($tdate);
    $delta = $tday['seconds'] + (60 * ($tday['minutes'] + (60 * $tday['hours'])));
    $ydate = ($delta <= 0) ? $tdate : $tdate - $delta;
    return correct_hour($ydate, 0);
}

function yesterday($tdate)
{
    return days_ago($tdate, 1);
}



function yesterweek($tdate)
{
    return days_ago($tdate, 7);
}



function yestermonth($tdate)
{
    return months_ago($tdate, 1);
}



function cyclic($cycle, $umax)
{
    $umin = 0;
    switch ($cycle) {
        case 0:
            $umin = yesterday($umax);
            break;
        case 1:
            $umin = yesterweek($umax);
            break;
        case 2:
            $umin = yestermonth($umax);
            break;
        case 3:
            $tday = getdate($umax);
            $wday = $tday['wday'];
            $days = ($wday <= 1) ? $wday + 2 : 1;
            $umin = days_ago($umax, $days);
            break;
        default:
            break;
    }
    return $umin;
}



function next_cycle(&$row, $now)
{
    $tmp = getdate($now);
    $cycle = $row['cycle'];
    $rhh = $row['hour'];
    $rmm = $row['minute'];
    $nhh = $tmp['hours'];
    $nmm = $tmp['minutes'];
    $yyy = $tmp['year'];
    $mon = $tmp['mon'];
    $day = $tmp['mday'];
    $when = mktime($rhh, $rmm, 0, $mon, $day, $yyy);
    if ($cycle == 0) {
        $next = ($now <= $when) ? $when : $when + 86400;
    }
    if ($cycle == 1) {
        $nwday = $tmp['wday'];
        $rwday = $row['wday'];
        $when = $when - ($nwday * 86400);
        $when = $when + ($rwday * 86400);
        $next = ($now <= $when) ? $when : $when + (7 * 86400);
    }
    if ($cycle == 2) {
        $mday = $row['mday'];
        if ($mday > 28)
            $mday = 28;
        $when = mktime($rhh, $rmm, 0, $mon, $mday, $yyy);
        if ($when < $now) {
            if ($mon < 12) {
                $mon++;
            } else {
                $mon = 1;
                $yyy++;
            }
            $when = mktime($rhh, $rmm, 0, $mon, $mday, $yyy);
        }
        $next = $when;
    }
    if ($cycle == 3) {
        $nwday = $tmp['wday'];
        if ($nwday == 6)
            $when += (2 * 86400);
        if ($nwday == 0)
            $when += (1 * 86400);
        $next = ($now <= $when) ? $when : $when + 86400;
    }
    if ($cycle == 4) {
        $when = $row['umax'];
        $next = ($now <= $when) ? $when : $now;
    } else {

        $next = correct_hour($next, $rhh);
    }
    return $next;
}



function last_cycle(&$row, $now)
{
    $tmp = getdate($now);
    $cycle = $row['cycle'];
    $rhh = $row['hour'];
    $rmm = $row['minute'];
    $nhh = $tmp['hours'];
    $nmm = $tmp['minutes'];
    $yyy = $tmp['year'];
    $mon = $tmp['mon'];
    $day = $tmp['mday'];
    $when = mktime($rhh, $rmm, 0, $mon, $day, $yyy);
    if ($cycle == 0) {
        $last = ($when < $now) ? $when : yesterday($when);
    }
    if ($cycle == 1) {
        $nwday = $tmp['wday'];
        $rwday = $row['wday'];
        $when = $when - ($nwday * 86400);
        $when = $when + ($rwday * 86400);
        $last = ($when < $now) ? $when : yesterweek($when);
    }
    if ($cycle == 2) {
        $mday = $row['mday'];
        if ($mday > 28)
            $mday = 28;
        $when = mktime($rhh, $rmm, 0, $mon, $mday, $yyy);
        $last = ($when < $now) ? $when : yestermonth($when);
    }
    if ($cycle == 3) {
        $nwday = $tmp['wday'];
        if ($nwday == 0)
            $when = days_ago($when, 2);
        if ($nwday == 6)
            $when = days_ago($when, 1);
        $last = ($when < $now) ? $when : yesterday($when);
    }
    if ($cycle == 4) {
        $last = $row['umin'];
    } else {

        $last = correct_hour($last, $rhh);
    }
    return $last;
}




$comparison_options = array(
    0 => ' - - - - - - - - - - - - - - - - - - - -',
    1 => 'equal to',
    2 => 'not equal to',
    3 => 'contains',
    4 => 'begins with',
    5 => 'ends with',
    6 => 'less than',
    7 => 'greater than',
    8 => 'less than or equal to',
    9 => 'greater than or equal to',
    10 => 'does not contain'
);

$comparison_sql = array(
    1 => '=',
    2 => '!=',
    3 => 'like',
    4 => 'like',
    5 => 'like',
    6 => '<',
    7 => '>',
    8 => '<=',
    9 => '>=',
    10 => 'not like'
);

function date_selector($date_value_MM, $date_value_DD, $date_value_YY)
{
    $date_value_MMs[] = '- -';
    for ($i = 1; $i < 13; $i++) {
        $mm = sprintf('%02d', $i);
        $date_value_MMs[$mm] = $mm;
    }

    $date_value_DDs[] = '- -';
    for ($i = 1; $i < 32; $i++) {
        $dd = sprintf('%02d', $i);
        $date_value_DDs[$dd] = $dd;
    }


    $date_value_YYs[] = '- - - -';
    $current_year = date('y', time());
    $year_past = ($current_year - 5);
    $year_future = ($current_year + 10);

    for ($c = $year_past; $c < $year_future; $c++) {
        $yy = sprintf('%02d', $c);
        $date_value_YYs[$yy] = "20$yy";
    }

    $output = "<table cellpadding=0 cellspacing=0><tr><td align=top>\n";
    $output .= html_select("date_value_MM", $date_value_MMs, $date_value_MM, 1) . "\n";
    $output .= "</td>\n";
    $output .= "<td align=top>/</td>\n";
    $output .= "<td>\n";
    $output .= html_select("date_value_DD", $date_value_DDs, $date_value_DD, 1) . "\n";
    $output .= "</td>\n";
    $output .= "<td align=top>/</td>\n";
    $output .= "<td align=top>\n";
    $output .= html_select("date_value_YY", $date_value_YYs, $date_value_YY, 1) . "\n";
    $output .= "</td></tr>\n";
    $output .= "</table>\n";

    return $output;
}


function outputJavascriptAssetTree($checkboxes, $hyperlinks, $textboxes, $editmode, $displayfields)
{

    include('../js/admin/ua.js');
    include('../js/admin/ftien4.js');
    include('../admin/dy-tree.php');
?>
    <!-- By making any changes to this code you are violating your user agreement.
         Corporate users or any others that want to remove the link should check
         the online FAQ for instructions on how to obtain a version without the link -->
    <!-- Removing this link will make the script stop from working -->
    <font size=-10>
        <a style="font-size:7pt;text-decoration:none;color:silver" href=http://www.treeview.net/treemenu/userhelp.asp target=_top></a>
    </font>

    <script>
        initializeDocument()
    </script>
    <noscript>
        <font color="red">Please enable JavaScript<br>
            in your browser, so you can<br>
            view the asset data fields.</font>
    </noscript>
<?php
}


function write_HTML($db, $parent, $parentname, $all_cats_string, $indent, $displayfields, $checkboxes, $get_data, $data_array)
{


    $sql_tree = "SELECT dataid, name FROM DataName " .
        "WHERE parent = " . $parent . " " .
        "ORDER BY ordinal";
    echo $sql_tree;
    $result_tree = command($sql_tree, $db);

    $output = $indent;


    if ($checkboxes) {
        $test_displayfields = is_int(strpos($displayfields, ":$parent:"));
        $checked = $test_displayfields ? "checked" : "";
        $output .= "<input type='checkbox' name='display_" . $parent;
        $output .= "' value='1' " . $checked . ">";
    }

    $test_string = ":" . $parent . ":";

    if (!strstr($all_cats_string, $test_string)) {
        $cat_or_data = "data";
    } else {
        $cat_or_data = "cat";
    }

    if ($cat_or_data == "cat") {
        if ($parent == 999) {
            $output .= $parentname;
        } else {
            $output .= "<span class=faded>" . $parentname . "</span>";
        }
    } elseif ($cat_or_data == "data") {
        $output .= "<span class=blue>" . $parentname . "</span>";
        if ($get_data) {
            $output .= "<span class=blue>: </span>";
            if (isset($data_array[$parent])) {
                $output .= $data_array[$parent];
            }
        }
    }

    $output .= "<br>\n";

    while ($row = mysqli_fetch_assoc($result_tree)) {
        $output .= write_HTML($db, $row['dataid'], $row['name'], $all_cats_string, "$indent&nbsp;&nbsp;&nbsp;&nbsp", $displayfields, $checkboxes, $get_data, $data_array);
    }

    return $output;
}


function gen_asset_fields($db, $dataid, $dataname, $machineid, $indent, $displayfields, $checkboxes, $get_data)
{

    $sql_data = "SELECT AD.dataid, AD.value " .
        "FROM AssetData AS AD, Machine AS M " .
        "WHERE M.machineid = " . $machineid . " " .
        "AND M.machineid = AD.machineid " .
        "AND AD.slatest = M.slatest ";

    $result_data = command($sql_data, $db);
    $data_array = array();
    while ($row = mysqli_fetch_assoc($result_data)) {
        $data_array[$row['dataid']] = $row['value'];
    }

    if ($get_data) {
        $sql_all_cats = "SELECT distinct DN.dataid, DN.name " .
            "FROM DataName AS DN, DataName AS DN2 " .
            "WHERE DN.dataid = DN2.parent " .
            "ORDER BY DN.parent, DN.ordinal";

        $result_all_cats = command($sql_all_cats, $db);
        $all_cats = array();
        while ($row = mysqli_fetch_assoc($result_all_cats)) {
            $all_cats[] = $row['dataid'];
        }

        if (strlen($all_cats)) {
            $all_cats_string = implode(":", $all_cats);
            $all_cats_string = ":0:" . $all_cats_string . ":";
        }
    }

    $output = write_HTML($db, $dataid, $dataname, $all_cats_string, $indent, $displayfields, $checkboxes, $get_data, $data_array);

    return $output;
}


function get_rootassetnames($db)
{
    $sql = "select name from DataName where parent = 0 order by ordinal;";
    $result = command($sql, $db);

    if ($result <= 0) {
        sqlerror($sql, $db);
    } else {
        return $result;
    }
    return $output;
}


function get_checked_fields($HTTP_VARS, $db)
{
    $checkedfields = array();
    reset($HTTP_VARS);
    foreach ($HTTP_VARS as $k => $v) {
        $posn = strpos($k, 'display_');
        if ((is_int($posn)) && ($posn == 0)) {
            $field = str_replace('display_', '', $k);
            $checkedfields[$field] = '';
        }
    }

    $result = false;
    if (safe_count($checkedfields) > 0) {

        $i = 1;
        $checkedfieldids = '';
        reset($checkedfields);
        foreach ($checkedfields as $k => $v) {
            if ($i != 1) {
                $checkedfieldids .= ", ";
            }
            $checkedfieldids .= $k;
            $i++;
        }
        reset($checkedfields);
        $sql_checkedfieldnames = "SELECT DISTINCT dataid, name FROM DataName" .
            " WHERE dataid IN ($checkedfieldids)" .
            " ORDER BY parent, ordinal";

        $result = command($sql_checkedfieldnames, $db);
    }
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $checkedfields[$row['dataid']] = $row['name'];
            }
        }
    }
    return $checkedfields;
}



function get_display_fields($form_or_database, $HTTP_VARS, $assetsearchid, $db)
{
    $displayfields = array();

    if ($form_or_database == "form") {
        $displayfields = get_checked_fields($HTTP_VARS, $db);
    } elseif ($form_or_database == "database") {
        $sql = "SELECT * FROM AssetSearches WHERE id=$assetsearchid";
        $result = command($sql, $db);
        $row = mysqli_fetch_assoc($result);
        $displayfieldnames = $row['displayfields'];
        $displayfieldnames = preg_replace("/:/", "', '", $displayfieldnames);
        $displayfieldnames = preg_replace("/^', /", "", $displayfieldnames);
        $displayfieldnames = preg_replace("/, '$/", "", $displayfieldnames);
        $sql2 = "SELECT dataid, name FROM DataName WHERE name IN ($displayfieldnames) ORDER by parent, ordinal";
        $result2 = command($sql2, $db);
        if ($result2) {
            while ($row = mysqli_fetch_assoc($result2)) {
                $displayfields[$row['dataid']] = $row['name'];
            }
        }
    }
    return $displayfields;
}



function get_match_fields($assetsearchid, $db)
{
    $matchfields = array();

    $sql = "SELECT DN.dataid, DN.name, SC.* " .
        "FROM AssetSearchCriteria AS SC, DataName AS DN " .
        "WHERE SC.assetsearchid=$assetsearchid " .
        "AND DN.name=SC.fieldname " .
        "ORDER BY SC.block, SC.id";

    $result = command($sql, $db);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $criterion['block'] = $row['block'];
            $criterion['comparison'] = $row['comparison'];
            $criterion['value'] = $row['value'];
            $criterion['groupname'] = $row['groupname'];

            $id = $row['dataid'];
            $name = $row['name'];
            $crit_arr_name = "criteria_" . $id;
            ${$crit_arr_name}[] = $criterion;
            $matchfields[$name] = $$crit_arr_name;
        }
    }
    return $matchfields;
}

function get_current_expires($id, $db)
{
    $expires = "";

    $sql = "SELECT expires FROM AssetSearches";
    $sql .= " WHERE id = " . $id;

    $result = command($sql, $db);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $expires = $row['expires'];
    }

    return $expires;
}

function get_future_expires($disposition)
{
    $now = time();

    if (strstr($disposition, "Save")) {
        $expires = "0";
    } elseif (strstr($disposition, "Run") || strstr($disposition, "Renew")) {
        $expires = $now + (2 * 24 * 60 * 60);
    } elseif (strstr($disposition, "Cancel")) {
        $expires = $now;
    } else {
        $expires = "0";
    }

    return $expires;
}

function convert_to_date_value($date_code)
{
    if (($date_code > 0) && ($date_code < 8)) {
        $now = time();

        switch ($date_code) {
            case 1:
                $date_value = $now;
                break;
            case 2:
                $date_value = yesterday($now);
                break;
            case 3:
                $date_value = yesterweek($now);
                break;
            case 4:
                $date_value = yestermonth($now);
                break;
            case 5:
                $date_value = yestermonth($now);
                $date_value = yestermonth($date_value);
                $date_value = yestermonth($date_value);
                break;
            case 6:
                $date_value = yestermonth($now);
                $date_value = yestermonth($date_value);
                $date_value = yestermonth($date_value);
                $date_value = yestermonth($date_value);
                $date_value = yestermonth($date_value);
                $date_value = yestermonth($date_value);
                break;
            case 7:
                $h = date("H", $now);
                $min = date("i", $now);
                $s = date("s", $now);
                $d = date("d", $now);
                $m = date("m", $now);
                $y = date("y", $now);
                $y = $y - 1;
                if (($d == 29) && ($m == 2)) {
                    $d = 28;
                }
                $date_value = mktime($h, $min, $s, $m, $d, $y);
                break;
        }
        return $date_value;
    } else {
        echo "date code is out of range";
        exit;
    }
}



function get_criteria_string($matchfields, $DateType, $date_code, $date_value, $db)
{
    global $comparison_options;
    $crit_strings = array();
    $output = "";

    if (safe_count($matchfields)) {
        reset($matchfields);
        foreach ($matchfields as $fieldname => $criteria) {
            reset($criteria);
            foreach ($criteria as $index => $criterion) {
                $block = $criterion['block'];
                $comparison_code = $criterion['comparison'];
                $value = $criterion['value'];
                $comparison = $comparison_options[$comparison_code];

                $string = $fieldname . " " . $comparison . " '" . $value . "'";
                if (isset($crit_strings[$block])) {
                    $crit_strings[$block] .= " AND " . $string;
                } else {
                    $crit_strings[$block] = $string;
                }

                $prev_block = $block;
            }
            reset($criteria);
        }
        reset($matchfields);

        $ctr = 0;
        $output = "(";
        reset($crit_strings);
        foreach ($crit_strings as $block => $criteria_string) {
            if ($ctr > 0) {
                $output .= ")\n OR \n(";
            }
            $output .= $criteria_string;
            $ctr++;
        }
        reset($crit_strings);
        $output .= ")";
    }
    return $output;
}


function check_can_make_assetsearch_local($currentuser, $search_id, $db)
{
    $answer = 1;

    $sql = "SELECT id, name, username FROM AssetReports";
    $sql .= " WHERE searchid = " . $search_id;
    $sql .= " AND ( (username != '$currentuser') OR (global = 1) )";
    $result = command($sql, $db);
    if ($result) {
        $answer = (mysqli_num_rows($result)) ? 0 : 1;
    }

    return $answer;
}







function html_select_class_mum($name, $options, $selected, $keys, $class)
{
    if ($class == '') {
        $classtext = '';
    } else {
        $classtext = " class=\"$class\"";
    }
    reset($options);
    $m = "<select style='width:174px;height:25px;' $classtext name=\"$name\" id=\"$name\" size=\"1\">";

    if ($keys) {
        $i = 0;
        foreach ($options as $key => $data) {

            $key = ($key == '-1') ? '' : $key;

            if (($selected == $key) && $data != '') {
                $m .= "<option selected style='width:174px' value=\"$key\">$data</option>";
            } else if ($data != '')
                $m .= "<option style='width:174px' value=\"$key\">$data</option>";

            $i++;
        }
    } else {
        foreach ($options as $key => $data) {
            $data = ($data == 'Nothing') ? '' : $data;

            if ($selected == $data && $data != '') {

                $m .= "<option selected style='width:174px' value=\"$data\">$data</option>";
            } else
                $m .= "<option dataid  style='width:174px' value=\"$data\">$data</option>";
        }
    }
    $m .= "</select>";
    return $m;
}



function html_select_class($name, $options, $selected, $keys, $class)
{
    if ($class == '') {
        $classtext = '';
    } else {
        $classtext = " class=\"$class\"";
    }
    reset($options);
    $m = "<select style='width:174px;height:25px;' $classtext name=\"$name\" id=\"$name\" size=\"1\">";
    if ($keys) {
        foreach ($options as $key => $data) {
            $key = ($key == '-1') ? '' : $key;
            if ($selected == $key)
                $m .= "<option selected  value=\"$key\">$data</option>";
            else
                $m .= "<option  value=\"$key\">$data</option>";
        }
    } else {
        foreach ($options as $key => $data) {
            if ($selected == $data) {
                $data = trim($data);
                $data = str_replace('&nbsp;', '', $data);
                $m .= "<option  selected>$data</option>";
            } else
                $m .= "<option  >$data</option>";
        }
    }
    $m .= "</select>";
    return $m;
}

function html_select($name, $options, $selected, $keys)
{
    return html_select_class($name, $options, $selected, $keys, '');
}

function html_select_mum($name, $options, $selected, $keys, $class)
{
    return html_select_class_mum($name, $options, $selected, $keys, $class);
}

function yesno($bool)
{
    if ($bool)
        return "Yes";
    else
        return "No";
}





function find_record_id($table, $id, $db)
{
    $row = array();
    $sql = "select * from $table where id = $id";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
        }
        mysqli_free_result($res);
    }
    return $row;
}


function check_nonowner_edit($authuser, $table_name, $id, $db)
{
    $nonowner_edit = 0;
    $row = find_record_id($table_name, $id, $db);
    if ($row) {
        $global = $row['global'];
        $username = $row['username'];
        if (($global) && ($username != $authuser))
            $nonowner_edit = 1;
        else
            $nonowner_edit = 0;
    }
    return $nonowner_edit;
}


function check_existing_name($table_name, $name, $username, $db)
{
    $exists = 0;
    $qname = safe_addslashes($name);
    $sql = "SELECT name FROM $table_name";
    $sql .= " WHERE name = '$qname' AND";
    $sql .= " username = '$username'";
    $res = command($sql, $db);
    if ($res) {
        $exists = (mysqli_num_rows($res)) ? 1 : 0;
        mysqli_free_result($res);
    }
    return $exists;
}


function check_existing_name_asset($name, $username, $id, $db)
{
    $exists = 0;
    $sql = "SELECT name FROM AssetSearches";
    $sql .= " WHERE name = '$name' AND";
    $sql .= " username = '$username'";
    $sql .= " AND expires = 0";
    if (strlen($id)) {
        $sql .= " AND id != $id";
    }

    $res = command($sql, $db);
    if ($res) {
        $exists = (mysqli_num_rows($res)) ? 1 : 0;
        mysqli_free_result($res);
    }
    return $exists;
}


function check_can_make_search_local($currentuser, $search_id, $db)
{
    $answer = 1;
    $sql = "SELECT id, name, username FROM Notifications";
    $sql .= " WHERE search_id = $search_id";
    $sql .= " AND ( (username != '$currentuser') OR (global = 1) )";
    $result = command($sql, $db);
    if ($result) {
        $answer = (mysqli_num_rows($result)) ? 0 : 1;
    }
    if ($answer) {
        $sql = "SELECT id, name, username FROM Reports";
        $sql .= " WHERE search_list like '%,$search_id,%'";
        $sql .= " AND ( (username != '$currentuser') OR (global = 1) )";
        $result = command($sql, $db);
        if ($result) {
            $answer = (mysqli_num_rows($result)) ? 0 : 1;
        }
    }
    return $answer;
}


function check_can_delete_search($table_name, $search_id, $db)
{
    $delete = 1;
    $dataset = strstr($table_name, "Asset") ? "asset" : "event";


    if ($dataset != "asset") {
        $sql = "SELECT id, name, username FROM Notifications";
        $sql .= " WHERE search_id = $search_id";
        $result = command($sql, $db);
        if ($result) {
            $delete = (mysqli_num_rows($result)) ? 0 : 1;
        }
    }
    if ($delete) {
        if ($dataset == "asset") {
            $sql = "SELECT id, name, username FROM AssetReports";
            $sql .= " WHERE searchid = $search_id";
        } else {
            $sql = "SELECT id, name, username FROM Reports";
            $sql .= " WHERE search_list like '%,$search_id,%'";
        }
        $result = command($sql, $db);
        if ($result) {
            $delete = (mysqli_num_rows($result)) ? 0 : 1;
        }
    }
    return $delete;
}


function check_can_delete_item($table_name, $authuser, $item_id, $db)
{
    $delete = "ok";
    $row = find_record_id($table_name, $item_id, $db);
    $user = ($row) ? $row['username'] : '';

    if (strstr($table_name, "Searches")) {
        $can_delete_search = check_can_delete_search($table_name, $item_id, $db);
        if ($can_delete_search)
            $delete = "ok";
        else
            $delete = "dependencies";
    }
    return $delete;
}


function get_name($table_name, $id, $db)
{
    $row = find_record_id($table_name, $id, $db);
    return ($row) ? $row['name'] : '';
}







define('constDefUserUniq', 'hfn_default_item');

function user_data($name, $db, $userfield = 'username')
{
    $usr = array();
    $sql = "select * from Users where $userfield='$name'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $usr = mysqli_fetch_assoc($res);
        }
        mysqli_free_result($res);
    }
    return $usr;
}

function userid_data($adminid, $db, $userfield = 'userid')
{
    $usr = array();
    $sql = "select * from Users where $userfield='$adminid'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $usr = mysqli_fetch_assoc($res);
        }
        mysqli_free_result($res);
    }
    return $usr;
}

function user_info($db, $username, $column, $def)
{
    $val = $def;
    $usr = user_data($username, $db);
    if ($usr) {
        if (isset($usr[$column]))
            $val = $usr[$column];
    }
    return $val;
}



function load_search_global($db)
{
    $sql = "select * from SavedSearches where global = 1";
    return find_several($sql, $db);
}



function find_global_owner($db)
{
    $sql = "select * from " . $GLOBALS['PREFIX'] . "event.SavedSearches where global = 1";
    $search = find_several($sql, $db);
    $owners = array();
    $user = '';
    $max = 0;
    reset($search);
    foreach ($search as $key => $row) {
        $name = $row['username'];
        if (isset($owners[$name]))
            $owners[$name]++;
        else
            $owners[$name] = 1;
    }
    reset($owners);
    foreach ($owners as $name => $count) {
        if ($count > $max) {
            $user = $name;
            $max = $count;
        }
    }
    return $user;
}

function USER_GenerateManagedUniq($name, $user, $db)
{
    $searchuniq = '';
    $owner = find_global_owner($db);
    if ((!$owner) || ($user == $owner)) {
        $searchuniq = md5(constDefUserUniq . ',' . $name);
    } else {
        $searchuniq = md5($user . ',' . $name);
    }

    return $searchuniq;
}





function outputJavascriptCritBldr($num_ANDs, $num_ORs, $openblockrows)
{
    global $QUERY_STRING;
    global $comparison_options;

    $action = trim(get_argument('action', 0, 'none'));
    if ($action == "edit" || $action == "duplicate") {
        $editmode = 1;
    } else {
        $editmode = 0;
    }
?>

    <script type="text/javascript">
        function changeDiv(the_div, the_change) {
            var the_style = getStyleObject(the_div);
            // alert ("before:" + the_div + ":" + the_style.display)
            if (the_style != false) {
                the_style.display = the_change;
            }
            // alert ("after:" + the_div + ":" + the_style.display)
        }

        function hideAllArrows() {
            <?php
            for ($i = 1; $i <= $num_ORs; $i++) {
                for ($j = 1; $j <= $num_ANDs; $j++) {
            ?>
                    changeDiv("Block<?php echo $i ?>Row<?php echo $j ?>arrow", "none");
            <?php
                }
            }
            ?>
        }

        function getStyleObject(objectId) {
            if (document.getElementById && document.getElementById(objectId)) {
                return document.getElementById(objectId).style;
            } else if (document.all && document.all(objectId)) {
                return document.all(objectId).style;
            } else {
                return false;
            }
        }

        function onClickActions(AND_or_OR, i, j) {
            hideAllArrows();

            if (AND_or_OR == "AND") {
                changeDiv("Block" + i + "Row" + j + "ANDbutton", "none");
                changeDiv("Block" + i + "Row" + j + "ANDtext", "block");
                //if (j>1) {
                //changeDiv("Block" + i + "Row" + j + "GROUP", "none");
                //changeDiv("Block" + i + "Row" + j + "GROUPA", "none");
                //changeDiv("Block" + i + "Row" + j + "GROUPB", "none");
                //}
            } else {
                changeDiv("Block" + i + "ORbutton", "none");
                changeDiv("Block" + i + "ORtext", "block");
            }

            // do for both AND Rows and OR Blocks
            var z;
            if (AND_or_OR == "AND") {
                z = j + 1; // apply to the next row
            } else {
                z = 1; // apply to the first row of this new clock
                i++;
            }

            changeDiv("Block" + i + "Row" + z, "block");
            changeDiv("Block" + i + "Row" + z + "A", "block");
            changeDiv("Block" + i + "Row" + z + "arrow", "block");
            changeDiv("Block" + i + "Row" + z + "B", "block");
            changeDiv("Block" + i + "Row" + z + "C", "block");
            changeDiv("Block" + i + "Row" + z + "D", "block");
            //changeDiv("Block" + i + "Row" + z + "E","block");
            //changeDiv("Block" + i + "Row" + z + "group","block");
            //if (AND_or_OR == "AND") {
            //changeDiv("Block" + i + "Row" + z + "GROUP","block");
            //changeDiv("Block" + i + "Row" + z + "GROUPA","block");
            //changeDiv("Block" + i + "Row" + z + "GROUPB","block");
            //}
            changeDiv("Block" + i + "Row" + z + "AND", "block");
            changeDiv("Block" + i + "Row" + z + "ANDA", "block");
            changeDiv("Block" + i + "Row" + z + "ANDB", "block");
            changeDiv("Block" + i + "Row" + z + "ANDbutton", "block");

            if (AND_or_OR == "OR") {
                changeDiv("Block" + i + "OR", "block");
                changeDiv("Block" + i + "ORA", "block");
                changeDiv("Block" + i + "ORbutton", "block");
            }
        }

        function changeFocusArrow(arrowElement) {
            var currentFocusArrow = getFocusBlockRow() + "arrow";
            changeDiv(currentFocusArrow, "none");
            changeDiv(arrowElement, "block");
        }

        function getFocusBlockRow() {
            var num_ORs = "<?php echo $num_ORs ?>";
            var num_ANDs = "<?php echo $num_ANDs ?>";

            for (i = 1; i <= num_ORs; i++) {
                for (j = 1; j <= num_ANDs; j++) {
                    var arrow = "Block" + i + "Row" + j + "arrow";
                    var element = getStyleObject(arrow);
                    var displayStatus = element.display;
                    if (displayStatus == "block") {
                        // alert(arrow + " is " + displayStatus);
                        focusBlockRow = "Block" + i + "Row" + j;
                    }
                }
            }
            return focusBlockRow;
        }

        function autoEnterField(dataname) {
            //  var focusField = "Block1Row1field";
            var focusField = getFocusBlockRow() + "field";
            var element = document.getElementById(focusField)
            element.value = dataname;
            //alert(focusField + " is " + element.value);
        }
    </script>

    <?php
    $comp = component_installed();
    $arrowgif = "../vendors/images/closed.gif";

    for ($i = 1; $i <= $num_ORs; $i++) {
        if ($i == 1) {
            $display = "block";
        } else {
            $display = "none";
        }

        for ($j = 1; $j <= $num_ANDs; $j++) {
            if ($i == 1 && $j == 1) {
                $display = "block";
            } else {
                $display = "none";
            }

            $comp_value = "Block${i}Row${j}comparison";
            global $$comp_value;
            if (!isset($$comp_value))
                $$comp_value = "";

            $field_value = "Block${i}Row${j}field";
            global $$field_value;
            if (!isset($$field_value))
                $$field_value = "";

            $value_value = "Block${i}Row${j}value";
            global $$value_value;
            if (!isset($$value_value))
                $$value_value = "";
    ?>
            <tr id="Block<?php echo $i ?>Row<?php echo $j ?>" style="width:500px;position:absolute;display:<?php echo $display ?>;float:left;">
                <td id="Block<?php echo $i ?>Row<?php echo $j ?>A" style="display:<?php echo $display ?>;float:left;width:66px;height: 1px;">
                    <img src="<?php echo $arrowgif ?>" id="Block<?php echo $i ?>Row<?php echo $j ?>arrow" style="display:<?php echo $display ?>;margin-top:10px;" width="7" style="color: black; padding-right: 10px;">
                </td>


                <td id="Block<?php echo $i ?>Row<?php echo $j ?>B" style="width:130px;display:<?php echo $display ?>;float:left;color: black;">
                    <input type="text" size="17" id="Block<?php echo $i ?>Row<?php echo $j ?>field" name="Block<?php echo $i ?>Row<?php echo $j ?>field" value="<?php echo $$field_value ?>" onFocus="changeFocusArrow('Block<?php echo $i ?>Row<?php echo $j ?>arrow')">
                </td>


                <td id="Block<?php echo $i ?>Row<?php echo $j ?>C" style="width:173px;height:25px;color: black; display:<?php echo $display ?>;float:left;"><?php echo html_select("Block" . $i . "Row" . $j . "comparison", $comparison_options, $$comp_value, 1)
                                                                                                                                                            ?> </td>


                <td id="Block<?php echo $i ?>Row<?php echo $j ?>D" style="width:130px;display:<?php echo $display ?>;float:left;color: black;">
                    <input style="width:130px;" type="text" size="17" id="Block<?php echo $i ?>Row<?php echo $j ?>value" name="Block<?php echo $i ?>Row<?php echo $j ?>value" value="<?php echo stripslashes($$value_value) ?>" onFocus="changeFocusArrow('Block<?php echo $i ?>Row<?php echo $j ?>arrow')">
                </td>
            </tr>
            <?php
            if ($j != 1) {
            ?>
                <!--
                <tr id="Block<?php echo $i ?>Row<?php echo $j ?>GROUP"
                    style="position:block;display:none;">
                    <td id="Block<?php echo $i ?>Row<?php echo $j ?>GROUPA"
                        style="position:block;display:none;"></td>
                    <td colspan="4" id="Block<?php echo $i ?>Row<?php echo $j ?>GROUPB"
                        style="position:block;display:none;"><INPUT
                        type="checkbox"
                        name=""
                        value="1">The <?php echo $j ?> fields above should be grouped</td>
                </tr>
                -->
            <?php
            }

            if ($j != $num_ANDs) {
            ?>
                <tr id="Block<?php echo $i ?>Row<?php echo $j ?>AND" style="position:relative;display:<?php echo $display ?>;margin-top:43px;">

                    <!--<td id="Block<?php echo $i ?>Row<?php echo $j ?>ANDA"
                    style="position:block;display:<?php echo $display ?>;">
                </td>-->
                    <td colspan="4" id="Block<?php echo $i ?>Row<?php echo $j ?>ANDB" style="position:block;display:<?php echo $display ?>;">
                        <button type="button" class="add-user-add-btn" id="Block<?php echo $i ?>Row<?php echo $j ?>ANDbutton" name="AND" value="AND" style="float: left; display:<?php echo $display ?>" onClick="onClickActions('AND', <?php echo $i ?>, <?php echo $j ?>)">AND</button>

                        <span id="Block<?php echo $i ?>Row<?php echo $j ?>ANDtext" style="position:block;display:none;">AND</span>
                    </td>
                </tr>
            <?php
            }
        }

        if ($i == 1) {
            $display = "block";
        }

        if ($i != $num_ORs) {
            ?>
            <tr id="Block<?php echo $i ?>OR" style="position:relative;display:<?php echo $display ?>;margin-left: 38px;">
                <td colspan="5" id="Block<?php echo $i ?>ORA" style="position:block;display:<?php echo $display ?>">
                    <button type="button" class="add-user-add-btn" id="Block<?php echo $i ?>ORbutton" name="OR" value="OR" style="display:<?php echo $display ?>;" onClick="onClickActions('OR', <?php echo $i ?>, <?php echo $j ?>)">OR</button>

                    <span id="Block<?php echo $i ?>ORtext" style="position:block;display:none;">OR</span>
                </td>
            </tr>
        <?php
        }
    }

    if ($editmode) {
        ?>

        <script type="text/javascript">
            /* FUTURE: use cookies?
             //set a cookie called openblock:row and enter the list, eg 1:1-1:2
             SetCookie("openblock:row", "<?php echo $openblockrows ?>");
             cookie = GetCookie("openblock:row");
             openus = cookie.replace(/-$/,"");
             */

            string = "<?php echo $openblockrows ?>";
            openus = string.replace(/-$/, "");
            openus_array = openus.split("-");

            prevblock = 0;
            prevrow = 0;
            for (i = 0; i < openus_array.length; i++) {
                openme = openus_array[i];
                openme_array = openme.split(":");
                block = openme_array[0];
                row = openme_array[1];

                if (block == prevblock) {
                    changeDiv("Block" + prevblock + "Row" + prevrow + "ANDbutton", "none");
                    changeDiv("Block" + prevblock + "Row" + prevrow + "ANDtext", "block");
                } else {
                    if (prevblock != 0) {
                        changeDiv("Block" + prevblock + "Row" + prevrow + "AND", "block");
                        changeDiv("Block" + prevblock + "Row" + prevrow + "ANDA", "block");
                        changeDiv("Block" + prevblock + "Row" + prevrow + "ANDB", "block");
                        changeDiv("Block" + prevblock + "Row" + prevrow + "ANDbutton", "block");
                        changeDiv("Block" + prevblock + "Row" + prevrow + "ANDtext", "none");
                        changeDiv("Block" + prevblock + "OR", "block");
                        changeDiv("Block" + prevblock + "ORA", "block");
                        changeDiv("Block" + prevblock + "ORbutton", "none");
                        changeDiv("Block" + prevblock + "ORtext", "block");
                    }
                }

                changeDiv("Block" + block + "Row" + row, "block");
                changeDiv("Block" + block + "Row" + row + "A", "block");
                changeDiv("Block" + block + "Row" + row + "B", "block");
                changeDiv("Block" + block + "Row" + row + "field", "block");
                changeDiv("Block" + block + "Row" + row + "C", "block");
                changeDiv("Block" + block + "Row" + row + "comparison", "block");
                changeDiv("Block" + block + "Row" + row + "D", "block");
                changeDiv("Block" + block + "Row" + row + "value", "block");
                //changeDiv("Block" + block + "Row" + row + "E","block");
                //changeDiv("Block" + block + "Row" + row + "group","block");
                changeDiv("Block" + block + "Row" + row + "AND", "block");
                changeDiv("Block" + block + "Row" + row + "ANDA", "block");
                changeDiv("Block" + block + "Row" + row + "ANDB", "block");
                changeDiv("Block" + block + "Row" + row + "ANDbutton", "block");

                prevblock = block;
                prevrow = row;
            }

            changeDiv("Block" + block + "OR", "block");
            changeDiv("Block" + block + "ORA", "block");
            changeDiv("Block" + block + "ORbutton", "block");
        </script>

    <?php
    }
}





define('constAdHocCritPrefix', 'crit');
define('constAdHocCritNone', '-1');
define('constAssetCompareEqual', 1);
define('constAdHocGroupInc', 'grpinc');




function asset_names($db)
{
    $names = array();
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "asset.DataName\n"
        . " order by dataid";
    $res = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $did = $row['dataid'];
            $names[$did] = $row;
        }
        mysqli_free_result($res);
    }
    return $names;
}



function asset_machines($db)
{
    $mach = array();
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "asset.Machine\n"
        . " order by machineid";
    $res = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $mid = $row['machineid'];
            $mach[$mid] = $row;
        }
        mysqli_free_result($res);
    }
    return $mach;
}

function asset_machines_exec($machines, $db)
{

    $mach = array();
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "asset.Machine\n"
        . " where machineid in (" . $machines . ") \n"
        . " order by machineid";
    $res = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $mid = $row['machineid'];
            $mach[$mid] = $row;
        }
        mysqli_free_result($res);
    }
    return $mach;
}



function asset_sites($machines, $db)
{

    $mach = array();
    $sql = "select cust from\n"
        . " " . $GLOBALS['PREFIX'] . "asset.Machine\n"
        . " where machineid in (" . $machines . ") \n"
        . " order by machineid";

    $res = find_one($sql, $db);

    return $res['cust'];
}



function asset_groups($db)
{
    $groups = array();
    $sql = "select distinct groups\n"
        . " from " . $GLOBALS['PREFIX'] . "asset.DataName\n"
        . " where (groups > 0)\n"
        . " order by groups";
    $res = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $groups[] = $row['groups'];
        }
        mysqli_free_result($res);
    }
    return $groups;
}



function asset_members($gid, $db)
{
    $members = array();
    if ($gid > 0) {
        $sql = "select dataid from " . $GLOBALS['PREFIX'] . "asset.DataName\n"
            . " where groups = $gid\n"
            . " order by ordinal, name, dataid";
        $res = command($sql, $db);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $members[] = $row['dataid'];
            }
            mysqli_free_result($res);
        }
    }
    return $members;
}



function asset_children($pid, $db)
{
    $children = array();
    $sql = "select dataid from " . $GLOBALS['PREFIX'] . "asset.DataName\n"
        . " where parent = $pid\n"
        . " order by ordinal, name, dataid";
    $res = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $children[] = $row['dataid'];
        }
        mysqli_free_result($res);
    }
    return $children;
}



function asset_site($site, $db)
{
    $machines = array();
    if ($site) {
        $qs = safe_addslashes($site);
        $sql = "select machineid from " . $GLOBALS['PREFIX'] . "asset.Machine\n"
            . " where provisional = 0 and\n"
            . " cust = '$qs'\n"
            . " order by machineid";
        $res = command($sql, $db);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $machines[] = $row['machineid'];
            }
            mysqli_free_result($res);
        }
    }
    return $machines;
}



function asset_access($access, $db)
{
    $mlist = array();
    if ($access) {
        $sql = "select machineid from " . $GLOBALS['PREFIX'] . "asset.Machine\n"
            . " where provisional = 0 and\n"
            . " cust in ($access)\n"
            . " order by machineid";
        $res = command($sql, $db);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $mlist[] = $row['machineid'];
            }
            mysqli_free_result($res);
        }
    }
    return $mlist;
}



function ALIB_DrawYUITree($did, $nodename, $names, $db)
{
    $js = '';
    $child = asset_children($did, $db);
    $table = asset_members($did, $db);

    $name = $names[$did]['name'];
    $qname = str_replace('\'', '\\\'', $name);
    $js .= 'var node' . $did . ' = new YAHOO.widget.TaskNode('
        . '"' . $qname . '", ' . $nodename . ', false, false);'
        . 'node' . $did . '.data=' . $did . ';';
    if ($child) {
        if ($table) {
            foreach ($table as $key => $data) {
                $js .= ALIB_DrawYUITree($data, 'node' . $did, $names, $db);
            }
        } else {
            foreach ($child as $key => $data) {
                $js .= ALIB_DrawYUITree($data, 'node' . $did, $names, $db);
            }
        }
    }
    return $js;
}

function ALIB_BuildAdHocQuery($db)
{

    $now = time();
    $sql = "DELETE FROM AssetSearches WHERE created<($now-86400) AND "
        . 'querytype=' . constAssetQueryTypeAdHoc;
    redcommand($sql, $db);


    $DateType = trim(get_argument('DateType', 0, 'RelDate'));
    $date_value_MM = trim(get_argument('date_value_MM', 0, ''));
    $date_value_DD = trim(get_argument('date_value_DD', 0, ''));
    $date_value_YY = trim(get_argument('date_value_YY', 0, ''));
    $date_code = intval(get_argument('date_code', 0, 0));
    $rel_days_ago = intval(get_argument('rel_days_ago', 0, 0));
    $error = '';
    if ($DateType == 'ExactDate') {
        $date_code = 0;
        if (($date_value_YY > 0) && ($date_value_YY < 100)) {
            $date_value_YY += 2000;
        }
        if (($date_value_MM > 0) && ($date_value_DD > 0) &&
            ($date_value_YY > 0)
        ) {
            $valid_date = checkdate($date_value_MM, $date_value_DD, $date_value_YY);
            if (!$valid_date) {
                $date_string = "$date_value_MM/$date_value_DD/"
                    . "$date_value_YY";
                $error .= error_date_invalid($date_string);
            }
            $date_unix = mktime(0, 0, 0, $date_value_MM, $date_value_DD, $date_value_YY);
            $date_value = $date_unix;
        } else {
            $error .= error_date_selection('exact');
        }
    } else {
        $date_value = 0;
        if ($date_code == 0) {
            $error .= error_date_selection('relative');
        } elseif ($date_code == 3) {
            if (!$rel_days_ago || $rel_days_ago > 1000000000) {
                $error .= error_date_daysago();
            } else {
                $date_value = $rel_days_ago;
            }
        }
    }

    if ($error) {
        echo $error;
        return 0;
    }

    $err = PHP_TIMR_GenerateUniq(CUR, $name);
    if ($err != constAppNoErr) {
        return 0;
    }
    $rowsize = get_integer('rowsize', 50);
    $refresh = get_string('refresh', 'never');
    $displayfields = '';

    $sql = "insert into AssetSearches set\n";
    $sql .= " name='$name',\n";
    $sql .= " username='hfn',\n";
    $sql .= " global=0,\n";
    $sql .= " displayfields='',\n";
    $sql .= " rowsize='$rowsize',\n";
    $sql .= " refresh='$refresh',\n";
    $sql .= " date_code = $date_code,\n";
    $sql .= " date_value = $date_value,\n";
    $sql .= " expires=0,\n";
    $sql .= " querytype=" . constAssetQueryTypeAdHoc . ",\n";
    $sql .= " created=$now,\n";
    $sql .= " modified=$now";
    $asrchuniq = USER_GenerateManagedUniq($name, 'hfn', $db);
    $sql .= ",\n asrchuniq = '$asrchuniq'";
    if (redcommand($sql, $db)) {
        if (mysqli_affected_rows($db)) {
            $qid = mysqli_insert_id($db);
        }
    }

    if ($qid == 0) {
        return 0;
    }

    $block = 0;
    reset($_POST);
    foreach ($_POST as $key => $value) {
        if (preg_match('/^' . constAdHocCritPrefix . '\d+$/', $key)) {

            $did = substr($key, strlen(constAdHocCritPrefix));
            $sql = "SELECT name FROM DataName WHERE dataid=$did";
            $row = find_one($sql, $db);
            if (!$row) {
                logs::log(__FILE__, __LINE__, 'No data: ' . $sql, 0);
                return 0;
            }
            $dataname = $row['name'];
            $displayfields .= ':' . $dataname;
            if ($value != constAdHocCritNone) {
                $sql = "SELECT value FROM AssetData WHERE id=$value";
                $row = find_one($sql, $db);
                if (!$row) {
                    logs::log(__FILE__, __LINE__, 'No data: ' . $sql, 0);
                    return 0;
                }
                $qv = safe_addslashes($row['value']);
                $sql = "insert into AssetSearchCriteria set\n" .
                    " assetsearchid = $qid,\n" .
                    " block = $block,\n" .
                    " fieldname = '$dataname',\n" .
                    " comparison = " . constAssetCompareEqual . ",\n" .
                    " value = '$qv',\n" .
                    " groupname = '',\n" .
                    " expires='0'";
                $result = redcommand($sql, $db);
                if (!$result) {
                    logs::log(__FILE__, __LINE__, 'Failed to add criteria: ' . $sql, 0);
                    return 0;
                }
                $block++;
            }
        }
    }
    if ($displayfields) {
        $displayfields .= ':';
        $sql = "UPDATE AssetSearches SET displayfields='$displayfields' "
            . "WHERE id=$qid";
        $result = redcommand($sql, $db);
        if (!$result) {
            logs::log(__FILE__, __LINE__, 'Failed to update display: ' . $sql, 0);
            return 0;
        }
    } else {
        echo error_display_field($db);
        return 0;
    }

    return $qid;
}

function error_display_field($db)
{
    $error = "<br><script> $('#seterr').html('##Select any field to display.##');</script>";
    return $error;
}

function error_date_selection($relative_or_exact)
{

    $error = "<br><script> $('#seterr').html('##Please select a date.##');</script>";
    return $error;
}

function error_date_daysago()
{

    $error = "<br><script> $('#seterr').html('##Please select a date.##');</script>";
    return $error;
}

function error_date_invalid($date_string)
{
    $error = "<br><script> $('#seterr').html('##The date you selected, $date_string, is not a valid date.##');</script>";
    return $error;
}









function update_asset($row, $db)
{
    $qn = safe_addslashes($row['name']);
    $qu = safe_addslashes($row['username']);
    $ql = safe_addslashes($row['emaillist']);
    $qf = safe_addslashes($row['format']);
    $q1 = @safe_addslashes(strval($row['order1']));
    $q2 = @safe_addslashes(strval($row['order2']));
    $q3 = @safe_addslashes(strval($row['order3']));
    $q4 = @safe_addslashes(strval($row['order4']));
    $qt = safe_addslashes($row['subject_text']);
    $qurl = safe_addslashes($row['xmlurl']);
    $qpas = safe_addslashes($row['xmlpass']);
    $qusr = safe_addslashes($row['xmluser']);
    $qfil = safe_addslashes($row['xmlfile']);

    $id = $row['id'];
    $log = $row['log'];
    $gbl = $row['global'];
    $cycle = $row['cycle'];
    $defmail = $row['defmail'];
    $file = $row['file'];
    $links = $row['links'];
    $last_run = $row['last_run'];
    $next_run = $row['next_run'];
    $this_run = $row['this_run'];
    $enabled = $row['enabled'];
    $content = $row['content'];
    $retries = $row['retries'];
    $i_user = $row['include_user'];
    $i_text = $row['include_text'];
    $skip_owner = $row['skip_owner'];
    $tabular = $row['tabular'];
    $xmlpasv = $row['xmlpasv'];
    $g_include = $row['group_include'];
    $g_exclude = $row['group_exclude'];

    $hour = @intval($row['hour']);
    $mint = @intval($row['minute']);
    $wday = @intval($row['wday']);
    $mday = @intval($row['mday']);
    $qid = @intval($row['searchid']);
    $chng = @intval($row['change_rpt']);
    $umin = @intval($row['umin']);
    $umax = @intval($row['umax']);
    $ctim = @intval($row['created']);
    $mtim = @intval($row['modified']);

    $cmd = ($id) ? 'update' : 'insert into';
    $sql = "$cmd AssetReports set\n"
        . " global = $gbl,\n"
        . " name = '$qn',\n"
        . " username = '$qu',\n"
        . " emaillist = '$ql',\n"
        . " defmail = $defmail,\n"
        . " file = $file,\n"
        . " links = $links,\n"
        . " format = '$qf',\n"
        . " cycle = $cycle,\n"
        . " hour = $hour,\n"
        . " minute = $mint,\n"
        . " wday = $wday,\n"
        . " mday = $mday,\n"
        . " enabled = $enabled,\n"
        . " last_run = $last_run,\n"
        . " next_run = $next_run,\n"
        . " this_run = $this_run,\n"
        . " order1 = '$q1',\n"
        . " order2 = '$q2',\n"
        . " order3 = '$q3',\n"
        . " order4 = '$q4',\n"
        . " searchid = $qid,\n"
        . " change_rpt = $chng,\n"
        . " content = $content,\n"
        . " created = $ctim,\n"
        . " modified = $mtim,\n"
        . " retries = $retries,\n"
        . " log = $log,\n"
        . " umax = $umax,\n"
        . " umin = $umin,\n"
        . " include_user = $i_user,\n"
        . " include_text = $i_text,\n"
        . " subject_text = '$qt',\n"
        . " skip_owner = $skip_owner,\n"
        . " tabular = $tabular,\n"
        . " xmlurl  = '$qurl',\n"
        . " xmluser = '$qusr',\n"
        . " xmlpass = '$qpas',\n"
        . " xmlfile = '$qfil',\n"
        . " xmlpasv = $xmlpasv,\n"
        . " group_include = '$g_include',\n"
        . " group_exclude = '$g_exclude'\n";
    if ($id)
        $sql .= "\n where id = $id";
    $res = redcommand($sql, $db);
    $num = affected($res, $db);
    if (($num) && (!$id)) {
        $num = mysqli_insert_id($db);
    }
    return $num;
}



function return_asset_url()
{
    $a_id = get_integer('asset_id', '');
    $a_act = get_string('asset_act', '');
    return "rid=$a_id&act=$a_act";
}



function preserve_asset_state($aid, $act)
{
    return "asset_id=$aid&asset_act=$act";
}





function outputJavascriptShowElement($id_list, $testleft, $testright, $clear_list, $delay_ms = 500)
{
    JS_HideShow($id_list, $testleft, $testright, $clear_list, $delay_ms);
}

function JS_HideShow($id_list, $testleft, $testright, $clear_list, $delay_ms = 500)
{
    echo <<< HERE

        <script type="text/javascript">

        function getStyleObject(objectId)
        {
            if (document.getElementById && document.getElementById(objectId))
            {
                return document.getElementById(objectId).style;
            }
            else if (document.all && document.all(objectId))
            {
                return document.all(objectId).style;
            }
            else
            {
                return false;
            }
        }


        function getObject(objectId)
        {
            if (document.getElementById && document.getElementById(objectId))
            {
                return document.getElementById(objectId);
            }
            else if (document.all && document.all(objectId))
            {
                return document.all(objectId);
            }
            else
            {
                return false;
            }
        }


        function clearField(clear_list)
        {
            // unfortunately, there is no getElementByClass
            var ids = clear_list.split(",");
            for (i = 0; i < ids.length; i++)
            {
                var id = ids[i];
                var el = getObject(id);
                el.value = "";
            }
        }


        function showElement(id_list,testleft,testright,clear_list)
        {
			if (testleft == testright)
            {  // show
			     var display = "inline";
            }
            else
            {
			    clearField(clear_list);     // clear fields
                var display = "none";
                    //alert('sdf');// hide
            }

              // unfortunately, there is no getElementByClass
            var ids = id_list.split(",");
            for (i = 0; i < ids.length; i++)
            {
                var id = ids[i];
                var el_style = getStyleObject(id);
                el_style.display = display;
            }
       }


        /*
        Need to run on page load, esp. after backing into page (post-error),
        to display saved state.
        Unfortunately, cached values (esp. for checkboxes) do not "register"
        until after the whole page is loaded (window.onload doesn't even work,
        so have to delay slightly -- for some reason a delay of 0 milliseconds works!!
        */

        window.setTimeout("showElement('$id_list',$testleft,$testright,'$clear_list')",$delay_ms);

    </script>
HERE;
}





function JS_site_popup()
{
    ?>
    <script language="javascript">
        // <!--
        function OpenWindow(type, url) {
            window.name = 'main';

            if (type == "site_popup") {
                var winname = "site_popup";
                var winWidth = 300;
                var winHeight = 400;
            }

            var winLeft = screen.width - winWidth - 20;
            var winTop = 0;
            if (winLeft < 0) {
                winLeft = 0
            }
            if (winTop < 0) {
                winTop = 0
            }

            var win = window.open(url, winname,
                "Location=no,menubar=no,resizable=no,scrollbars=yes,height=" +
                winHeight + ",width=" + winWidth + ",screenX=" + winLeft +
                ",screenY=" + winTop + ",left=" + winLeft + ",top=" + winTop);
            win.focus();

        }
        // -->
    </script>

<?php
}

function get_user_filtersetting($authuser, $db)
{
    $filtersites = 0;
    if ($authuser) {
        $current_db_name = get_db_name($db);
        if ($current_db_name != 'core')
            db_change($GLOBALS['PREFIX'] . 'core', $db);

        $sql = "SELECT filtersites FROM Users WHERE username = '$authuser' ";
        $res = redcommand($sql, $db);

        if ($res) {
            if (mysqli_num_rows($res) == 1) {
                $row = mysqli_fetch_assoc($res);
                $filtersites = $row['filtersites'];
            }
        }

        if ($current_db_name != 'core')
            db_change($current_db_name, $db);
    }
    return $filtersites;
}

function JS_CheckUncheckAll()
{
?>
    <SCRIPT LANGUAGE="JavaScript">
        function CheckUncheckAll(all_setting) {
            var check;
            if (all_setting == "check")
                check = true;
            else if (all_setting == "uncheck")
                check = false;

            var checkboxes = window.document.myForm.sitesCHECKED;
            for (i = 0; i < checkboxes.length; i++)
                checkboxes[i].checked = check;
            return true;
        }
    </script>
<?php
}

function HTML_CheckUncheckAll($cols)
{
    $self = server_var('PHP_SELF');

    $querystring = server_var('QUERY_STRING');
    $querystring = preg_replace("/all_setting=(check|uncheck)&?/", "", $querystring);
    if (strlen($querystring))
        $querystring = '&' . $querystring;

    $span = $cols * 3;

    $ha = "$self?all_setting=check$querystring";
    $hn = "$self?all_setting=uncheck$querystring";
    $ja = "CheckUncheckAll('check');return false;";
    $jn = "CheckUncheckAll('uncheck');return false;";
    echo <<< HERE

<tr>
  <td colspan="$span">
    [<a href="$ha" onClick="$ja">check all</a> |
     <a href="$hn" onClick="$jn">uncheck all</a>]
  </td>
</tr>

HERE;
}

function get_user_sitefilter($authuser, $db)
{
    $current_db_name = get_db_name($db);
    if ($current_db_name != 'core')
        db_change($GLOBALS['PREFIX'] . 'core', $db);

    $user_sitefilter = array();
    $sql = "SELECT * FROM Customers\n WHERE username = '$authuser'\n ORDER BY customer";
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_assoc($res)) {
                $user_sitefilter[$row['customer']] = $row['sitefilter'];
            }
        }
        mysqli_free_result($res);
    }

    if ($current_db_name != 'core')
        db_change($current_db_name, $db);

    return $user_sitefilter;
}

function show_sitefilter_radio($filtersites, $db)
{
    $chk = ' checked';
    $on = ($filtersites) ? $chk : '';
    $off = ($filtersites) ? '' : $chk;

    echo "<input type=\"radio\" name=\"filtersites\" value=\"1\"$on>On ";
    echo "<input type=\"radio\" name=\"filtersites\" value=\"0\"$off>Off";
}

function show_sitefilterlist($objecttype, $sitefilter, $all_setting, $cols, $db)
{
    if (($cols < 1) || ($cols > 10))
        $cols = 1;
    JS_CheckUncheckAll();
    table_header();
    HTML_CheckUncheckAll($cols);
    $count = safe_count($sitefilter);
    if ($count > 0) {
        $data = array();
        $cols = ($count > 2 * $cols) ? $cols : 1;
        if ($cols > 1) {
            $div = intval($count / $cols);
            $rows = (($count % $cols) == 0) ? $div : $div + 1;
        } else {
            $rows = $count;
        }

        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < $cols; $col++) {
                $data[$row][$col]['site'] = '';
                $data[$row][$col]['filt'] = 0;
            }
        }

        $i = 0;
        reset($sitefilter);
        foreach ($sitefilter as $site => $filter) {
            $row = intval($i % $rows);
            $col = intval($i / $rows);
            $data[$row][$col]['site'] = $site;
            $data[$row][$col]['filt'] = $filter;
            $i++;
        }

        $chk = ' checked';
        for ($row = 0; $row < $rows; $row++) {
            $args = array();
            for ($col = 0; $col < $cols; $col++) {
                $site = $data[$row][$col]['site'];
                $filt = $data[$row][$col]['filt'];
                if ($site) {
                    if ($all_setting == 'check')
                        $checked = $chk;
                    elseif ($all_setting == 'uncheck')
                        $checked = '';
                    else
                        $checked = ($filt) ? $chk : '';

                    $hide = "\n<input type=\"hidden\" name=\"sites[]\" value=\"$site\">\n";
                    $cbox = "<input type=\"checkbox\" name=\"sitesCHECKED[]\" id=\"sitesCHECKED\" value=\"$site\"$checked>\n";
                    $args[] = $hide . $cbox;
                    $args[] = $site;
                    $args[] = '&nbsp;&nbsp;';
                } else {
                    $args[] = '<br>';
                    $args[] = '<br>';
                    $args[] = '<br>';
                }
            }
            table_data($args, 0);
        }
    }
    HTML_CheckUncheckAll($cols);
    table_footer();
}

function checkboxes2sitefilter($sites, $sitesCHECKED)
{
    $sitefilter = array();

    reset($sites);
    foreach ($sites as $key => $site) {
        $filter = (in_array($site, $sitesCHECKED)) ? 1 : 0;
        $sitefilter[$site] = $filter;
    }

    return $sitefilter;
}






function asset_null()
{
    return 'Nothing';
}



function asset_order_always($db)
{
    $tmp = array();
    $sql = "select name from DataName where include = 1";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_assoc($res)) {
                $tmp[] = $row['name'];
            }
        }
        mysqli_free_result($res);
    }
    return $tmp;
}



function find_single($sql, $db)
{
    $row = array();
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
            mysqli_free_result($res);
        }
    }
    return $row;
}



function asset_display_fields($id, $db)
{
    $list = array();
    if ($id > 0) {
        $sql = "select * from AssetSearches where id = $id";
        $row = find_single($sql, $db);
        if ($row) {
            $df = $row['displayfields'];
            $tmp = explode(':', $df);
            foreach ($tmp as $k => $d) {
                if ($d) {
                    $list[] = $d;
                }
            }
        }
    }
    return $list;
}

function asset_order_options($id, $db)
{
    $option = array();
    $names = array();
    $always = asset_order_always($db);
    $fields = asset_display_fields($id, $db);

    reset($always);
    foreach ($always as $xxx => $name) {
        $names[$name] = true;
    }
    reset($fields);
    foreach ($fields as $xxx => $name) {
        $names[$name] = true;
    }
    reset($names);
    foreach ($names as $name => $xxx) {
        $option[] = $name;
    }
    return $option;
}



function write_JS_order_options($searchids, $order1, $order2, $order3, $order4, $db)
{
    $super = "\n";
    $i = 1;
    reset($searchids);
    foreach ($searchids as $index => $id) {
        if ($i > 1) {
            $super .= ",\n";
        }
        $super .= "      new Array(\n";

        $j = 1;
        $names = asset_order_options($id, $db);
        reset($names);
        foreach ($names as $xxx => $name) {
            if ($j > 1) {
                $super .= ",\n";
            }
            $super .= "        new Array('$name','$name')";
            $j++;
        }
        $super .= "\n      )";
        $i++;
    }
    $nothing = asset_null();
    echo <<< HERE

<script language="JavaScript">

 function get_displayfields_for_search(myForm,selectedIndex)
 {
    super_displayfields = new Array(
        $super
    );

    var order1 = '$order1';
    var order2 = '$order2';
    var order3 = '$order3';
    var order4 = '$order4';
    var order1_index = 0;
    var order2_index = 0;
    var order3_index = 0;
    var order4_index = 0;

    for (i = super_displayfields[selectedIndex].length-1; i >= 0; i--)
    {
        if (super_displayfields[selectedIndex][i][1] != null)
        {
            if (super_displayfields[selectedIndex][i][1] == order1)
            {
                order1_index = i+1;
            }
            if (super_displayfields[selectedIndex][i][1] == order2)
            {
                order2_index = i+1;
            }
            if (super_displayfields[selectedIndex][i][1] == order3)
            {
                order3_index = i+1;
            }
            if (super_displayfields[selectedIndex][i][1] == order4)
            {
                 order4_index = i+1;
            }
        }
    }

    fillSelectFromArray( myForm.order1, super_displayfields[selectedIndex], order1_index );
    fillSelectFromArray( myForm.order2, super_displayfields[selectedIndex], order2_index );
    fillSelectFromArray( myForm.order3, super_displayfields[selectedIndex], order3_index );
    fillSelectFromArray( myForm.order4, super_displayfields[selectedIndex], order4_index );
  }

  // Original:  Jerome Caron (jerome.caron@globetrotter.net)
  // This script and many more are available free online at
  // The JavaScript Source!! http://javascript.internet.com
  function fillSelectFromArray(selectCtrl, itemArray, indexToSelect)
  {
    var i, j;
    var prompt;
    // empty existing items
    for (i = selectCtrl.options.length; i >= 0; i--)
    {
      selectCtrl.options[i] = null;
    }

    // add an entry for the first option
    selectCtrl.options[0] = new Option("$nothing");
    selectCtrl.options[0].value = "";

    j = 1;
    if (itemArray != null)
    {
      // add new items
      for (i = 0; i < itemArray.length; i++)
      {
        selectCtrl.options[j] = new Option(itemArray[i][0]);
        if (itemArray[i][1] != null)
        {
            selectCtrl.options[j].value = itemArray[i][1];
        }
        j++;
      }
      // select first item (prompt) for sub list
      if (selectCtrl.options[indexToSelect] != null)
      {
        selectCtrl.options[indexToSelect].selected = true;
      }
    }
  }


  // run when page loads if selection exists, eg. after backing into page (post-error)
  if (window.document.myform.searchid.selectedIndex != -1)
  {
    get_displayfields_for_search(myform,window.document.myform.searchid.selectedIndex);
  }

</script>

HERE;
}

function search_list($authuser, $db)
{
    $list = array();
    $qu = safe_addslashes($authuser);
    $sql = "select * from AssetSearches\n";
    $sql .= " where global = 1 or\n";
    $sql .= " username = '$qu'\n";
    $sql .= " order by name, global";
    $rows = find_many($sql, $db);
    if ($rows) {
        $prev = '';
        reset($rows);
        foreach ($rows as $key => $row) {
            $id = $row['id'];
            $name = $row['name'];
            if ($name != $prev) {
                $list[$id] = $name;
            }
            $prev = $name;
        }
    }
    return $list;
}

function searchid_list($searches)
{
    $list = array();
    if ($searches) {
        reset($searches);
        foreach ($searches as $id => $name) {
            $list[] = $id;
        }
    }
    return $list;
}



function ARPT_WriteSingleSearchArray($row, $context, $db)
{
    $id = $row['id'];
    $thissrchuniq = $row['asrchuniq'];
    $super = '';

    $super .= $context . "super_displayfields['$thissrchuniq'] = new "
        . "Array(\n";

    $j = 1;
    $names = asset_order_options($id, $db);
    reset($names);
    foreach ($names as $xxx => $name) {
        if ($j > 1) {
            $super .= ",\n";
        }
        $super .= "        new Array('$name','$name')";
        $j++;
    }
    $super .= "\n      );\n";

    return $super;
}




define('CUR', 1);


define('constCheckSyncDoNothing', 0);
define('constCheckSyncDoSync', 1);
define('constCheckSyncDoRegisterAndSync', 2);
define('constCheckSyncMachineList', 8);


define('constInfoSite', 'site');
define('constInfoUUID', 'uuid');
define('constInfoMachine', 'machine');
define('constInfoVersion', 'version');
define('constInfoToken', 'token');

define('constInfoGlobalChecksum', 'globalchk');
define('constInfoLocalChecksum', 'localchk');
define('constInfoConfigChecksum', 'configchk');


define('constVarPackageVars', 'vars');
define('constVarPackageMachines', 'machines');
define('constVarPackageType', 't');
define('constVarPackageState', 's');
define('constVarPackageStateRev', 'sr');
define('constVarPackageGlobal', 'g');
define('constVarPackageGlobalRev', 'gr');
define('constVarPackageLocal', 'l');
define('constVarPackageLocalRev', 'lr');



define('constVblTypeInteger', 0);
define('constVblTypeDateTime', 1);
define('constVblTypeString', 2);
define('constVblTypeBoolean', 3);
define('constVblTypeInvalid', 4);
define('constVblTypeMailSendList', 4);
define('constVblTypeLogInfoList', 5);
define('constVblTypeAList', 6);
define('constVblTypeSemaphore', 7);
define('constVblTypeQueue', 8);



define('constPasswordSecVarDefault', 0);
define('constPasswordSecCleartext', 1);
define('constPasswordSecHashed', 2);
define('constPasswordSecEncrypted', 3);
define('constPasswordSecInvalid', 4);



define('constConfigNormal', 0);
define('constConfigPassword', 1);
define('constConfigSkip', 2);
define('constConfigPrivate', 3);
define('constConfigIllegal', 4);



define('constVarConfStateGlobal', 0);
define('constVarConfStateLocal', 1);
define('constVarConfStateLocalOnly', 2);


define('constCookieCustID', 'CustomerID');
define('constCookieSiteEmailID', 'MachineID');
define('constCookieProxy', 'ProxyURL');


define('constConfListCustID', 'CustomerID');
define('constConfListEmailCode', 'MachineID');
define('constConfListSiteName', 'SiteName');


define('constProtocolVer100', 100);
define('constProtocolVer101', 101);
define('constProtocolVer102', 102);




define('constAuditNone', 0);
define('constAuditLowestDetail', 1);
define('constAuditMediumDetail', 5);
define('constAuditHighestDetail', 10);


define('constMUMChangeLevel', 4);
define('constAUTONotifyLevel', constAuditMediumDetail);
define('constNotifyDebugLevel', 7);


define('constModuleDSYN', 1);
define('constModuleCORE', 2);
define('constModuleConfig', 3);
define('constModuleINST', 4);
define('constModuleMUM', 5);
define('constModuleAUTO', 9);
define('constModuleNotify', 10);


define('constClassDebug', 1);
define('constClassUser', 2);


define('constProductClient', 1);
define('constProductServer', 2);
define('constProductCSRV', 3);


define('constAuditGroupMUMChange', 8);
define('constAuditGroupAUTONotification', 12);
define('constAuditGroupNotify', 14);




define('constTableTypeStatic', 1);
define('constTableTypeTemporary', 0);
define('constTableAssetData', 'AssetData');


define('constTableIDEvents', 200);
define('constTableIDAudit', 201);
define('constTableIDAddEventFilters', 202);
define('constTableIDAddMgrpInclude', 203);
define('constTableIDTest', 999);
define('constTableIDEventDisplay', 100);
define('constTableIDMonitorDisplay', 101);
define('constTableIDProfileDisplay', 102);
define('constTableIDResourceDisplay', 103);
define('constTableIDSecurityDisplay', 104);
define('constTableIDMaintenanceDisplay', 105);
define('constTableIDDisplayMachineDisplay', 106);
define('constTableIDDisplayMonitorDisplay', 107);
define('constTableIDMachineGroupDisplay', 108);
define('constTableIDMonItemGroupDisplay', 109);
define('constTableIDMachineDisplay', 110);
define('constTableIDValueMap', 400);
define('constTableIDValueMapAdv', 401);
define('constTableIDMUMUpdates', 500);
define('constTableIDSections', 501);
define('constTableIDMUMSections', 502);
define('constTableIDReports', 600);
define('constTableIDAddSections', 601);
define('constTableIDEventSections', 602);
define('constTableIDSchedules', 700);
define('constTableIDAddSchedules', 701);
define('constTableIDAddAssetQueries', 800);
define('constTableIDExecSumSections', 900);


define('constOptionPageSize', 1);
define('constOptionPageSizeStr', "1");
define('constOptionDisplayComplexity', 2);
define('constOptionDisplayComplexityStr', "2");
define('constOptionOptionsComplexity', 3);
define('constOptionOptionsComplexityStr', "3");


define('constSortOptionNone', 0);
define('constSortOptionBoth', 1);


define('constSortSettingNone', 0);
define('constSortSettingAsc', 1);
define('constSortSettingDesc', 2);


define('constDispDataNone', 0);
define('constDispDataTimestamp', 1);


define('constSelSearchBasic', 0);
define('constSelSearchExtended', 1);
define('constSelSearchDate', 2);
define('constSelSearchQuery', 3);
define('constSelSearchScrip', 4);


define('constNextAct', 1);
define('constPrevAct', -1);
define('constCancelAct', 0);


define('CFGFRMT_CLIENT', 0);
define('CFGFRMT_SERVER', 1);
define('CFGFRMT_SRVGROUP', 2);
define('CFGFRMT_SRVGROUPADV', 3);


define('constPageTypeScripConfig', 1);
define('constPageTypeConfirm1', 2);
define('constPageTypeConfirm2', 3);


define('constIDMin', 200);
define('constIDMax', 1999999);


define('constSourceScripConfig', "0");
define('constSourceScripGroupConfig', "1");
define('constSourceScripGroupAdvConfig', "2");
define('constSourceScripRemoteWizard', "3");
define('constSourceScripMalwareWizard', "4");
define('constSourceScripUpdateWizard', "5");
define('constSourceScripFreqWizard', "6");


define('constMUMIntMachineStart', 1);
define('constOutputUpdateInt', 3);


define('constScheduleBlank', 1);


define('constReportDisabled', 1);
define('constReportEnabled', 2);


define('constDataTypeReports', 1);


define('constObjectTypeReport', 1);


define('constSchedFormCreate', 0);
define('constSchedFormEdit', 1);
define('constSchedFormView', 2);


define('constServerOptionReptCSS', 'rept_css');
define('constServerOptionServerURL', 'server_url');


define('constJavaListEventFilters', 0);
define('constJavaListEventMgrpInclude', 1);
define('constJavaListEventMgrpExclude', 2);
define('constJavaListAssetQueries', 3);


define('constStartupTypeUninitialized', 0);
define('constStartupTypeList', 1);
define('constStartupTypeNone', 2);
define('constStartupTypeAll', 3);
define('constFollowonTypeUninitialized', 0);
define('constFollowonTypeList', 1);
define('constFollowonTypeNone', 2);
define('constFollowonTypeAll', 3);
define('constFollowonTypeUninstall', 4);
define('constInstPageUninitialized', 0);
define('constInstPageFull', 1);
define('constInstPageFrame', 2);
define('constIntroTextUninitialized', 0);
define('constIntroTextNone', 1);
define('constIntroTextText', 2);
define('constFormTypeUninitialized', 0);
define('constFormTypeText', 1);
define('constFormTypeTextArea', 2);
define('constFormTypeCheckbox', 3);
define('constFormTypeSalutation', 4);
define('constFormTypeState', 5);
define('constFormTypeCountry', 6);
define('constFormTypeMonth', 7);
define('constFormTypeDay', 8);
define('constFormTypeYear', 9);


define('constRepfFormPrefix', 'ctrform_');


define('constSectionNameEventConfigUniq', '6f8a39ea9f28bf718052e75e5f11e173');
define('constSectionNameMUMConfigUniq', 'a4790423ed34921c368c922a0a81b52e');
define('constSectionNameExecSumConfigUniq', '08fff781eae063e35db95473e27fed59');


define('constReportNameConfigUniq', '47ff2696057fc7c5164c845d0f905e6d');


define('constSchedNameConfigUniq', 'ctrsched_name');


define('constJavaListDispositionEdit', 0);
define('constJavaListDispositionView', 1);


define('constFontSizeClickHere', '<font size="3">');


define('constAssetQueryTypePerm', 0);
define('constAssetQueryTypeAdHoc', 1);


define('constOptionEventCode', 0);
define('constOptionEventCodeStr', 'event_code');





define('constErrAssertFail', 1);
define('constAppNoErr', 2);
define('constErrNoConfigVars', 347);
define('constErrServerNoSupport', 613);
define('constErrDatabaseNotAvailable', 626);
define('constErrSiteNotFound', 705);
define('constErrServerTooBusy', 763);
define('constErrNotEncrypted', 768);
define('constErrCensusUUID', 888);
define('constErrCensusName', 889);
define('constErrServChangeUUID', 915);
define('constErrServChangeName', 916);
define('constErrUniqueName', 996);



define('constRevisionLevel', 1413);

?>