<?php




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
    1  => '=',
    2  => '!=',
    3  => 'like',
    4  => 'like',
    5  => 'like',
    6  => '<',
    7  => '>',
    8  => '<=',
    9  => '>=',
    10  => 'not like'
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
    $current_year     = date('y', time());
    $year_past        = ($current_year - 5);
    $year_future      = ($current_year + 10);

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
    $output .= "<tr>";
    $output .= "<td><span class=footnote>month</span></td><td></td>\n";
    $output .= "<td><span class=footnote>&nbsp;day</span></td><td></td>\n";
    $output .= "<td><span class=footnote>&nbsp;year</span></td>\n";
    $output .= "</tr></table>\n";

    return $output;
}


function outputJavascriptAssetTree($checkboxes, $hyperlinks, $textboxes, $editmode, $displayfields)
{

    include('js/ua.js');
    include('js/ftiens4.js');
    include('js/dy-tree.php');
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
    $result_tree = command($sql_tree, $db);

    $output =  $indent;

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
        $output .= write_HTML(
            $db,
            $row['dataid'],
            $row['name'],
            $all_cats_string,
            "$indent&nbsp;&nbsp;&nbsp;&nbsp",
            $displayfields,
            $checkboxes,
            $get_data,
            $data_array
        );
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
            $all_cats[] =  $row['dataid'];
        }

        if (strlen($all_cats)) {
            $all_cats_string = implode(":", $all_cats);
            $all_cats_string = ":0:" . $all_cats_string  . ":";
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
    while (list($k, $v) = each($HTTP_VARS)) {
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
        while (list($k, $v) = each($checkedfields)) {
            if ($i != 1) {
                $checkedfieldids .= ", ";
            }
            $checkedfieldids .= $k;
            $i++;
        }
        reset($checkedfields);
        $sql_checkedfieldnames =    "SELECT DISTINCT dataid, name FROM DataName" .
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

    $sql  = "SELECT expires FROM AssetSearches";
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
            case  1:
                $date_value = $now;
                break;
            case  2:
                $date_value = yesterday($now);
                break;
            case  3:
                $date_value = yesterweek($now);
                break;
            case  4:
                $date_value = yestermonth($now);
                break;
            case  5:
                $date_value = yestermonth($now);
                $date_value = yestermonth($date_value);
                $date_value = yestermonth($date_value);
                break;
            case  6:
                $date_value = yestermonth($now);
                $date_value = yestermonth($date_value);
                $date_value = yestermonth($date_value);
                $date_value = yestermonth($date_value);
                $date_value = yestermonth($date_value);
                $date_value = yestermonth($date_value);
                break;
            case  7:
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
    $crit_strings  = array();
    $output = "";

    if (safe_count($matchfields)) {
        reset($matchfields);
        while (list($fieldname, $criteria) = each($matchfields)) {
            reset($criteria);
            while (list($index, $criterion) = each($criteria)) {
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

    $sql  = "SELECT id, name, username FROM AssetReports";
    $sql .= " WHERE searchid = " . $search_id;
    $sql .= " AND ( (username != '$currentuser') OR (global = 1) )";
    $result = command($sql, $db);
    if ($result) {
        $answer = (mysqli_num_rows($result)) ? 0 : 1;
    }

    return $answer;
}
