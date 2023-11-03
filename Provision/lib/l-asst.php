<?php

/*
Revision history:

Date        Who     What
----        ---     ----
11-Sep-02   EWB     Merge with new asset code
11-Sep-02   EWB     get_checked_fields works if no fields checked.
12-Sep-02   EWB     fixed a bug in check_can_delete_search.
17-Sep-02   EWB     check_can_delete_search uses strstr instead of strpos...
07-Oct-02   NL      add <=, >= to comparison_options & comparison_sql
09-Oct-02   NL      remove get_AssetData_sql since it is defunct
09-Oct-02   NL      create get_expires_value function
09-Oct-02   NL      add outputJavascriptDaysAgo() function
09-Oct-02   NL      create check_can_make_assetsearch_local
21-Oct-02   NL      get_expires_value
22-Oct-02   NL      change to get_future_expires; add get_currect_expires
31-Oct-02   NL      change outputJavascriptShowDaysAgo to outputJavascriptShowElement
 4-Dec-02   EWB     include files should not include files
 5-Dec-02   EWB     Do not require php short_open_tag
 5-Dec-02   EWB     Replaced mysql_query with command
20-Dec-02   AAM     Fixed PHP3 incompatibility.
 5-Apr-03   EWB     removed get_machine_ids(), not used.
 5-May-03   NL      Move outputJavascriptShowElement() to l-js.php.
21-May-03   EWB     Comparison for does not contain.
21-May-03   EWB     Fixed leapday bug.
14-Jun-05   BJS     Fixed year bug: no longer hardwired, now computes from
                    current year.
20-Jun-05   EWB     Find current year without launching a new process.
*/


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

    /* get the current year (`date +%y`)
       subtract 5 ($year_past)
       add 10 ($year_future)
       build dropbox
     */
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


#  Generate expandable checkbox list of assets
#  If in edit mode (editmode=1), provide displayfields array, otherwise displayfields="".
function outputJavascriptAssetTree($checkboxes, $hyperlinks, $textboxes, $editmode, $displayfields)
{
    //<!-- These 3 scripts define the expandable checkbox list, do not remove-->

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


#  recursive used by gen_asset_fields function below
function write_HTML($db, $parent, $parentname, $all_cats_string, $indent, $displayfields, $checkboxes, $get_data, $data_array)
{
    # get the category tree
    $sql_tree = "SELECT dataid, name FROM DataName " .
        "WHERE parent = " . $parent . " " .
        "ORDER BY ordinal";
    $result_tree = command($sql_tree, $db);

    # build the output string
    $output =  $indent;

    if ($checkboxes) {
        $test_displayfields = is_int(strpos($displayfields, ":$parent:"));
        $checked = $test_displayfields ? "checked" : "";
        $output .= "<input type='checkbox' name='display_" . $parent;
        $output .= "' value='1' " . $checked . ">";
    }

    # is this a cateogry or a data field?
    $test_string = ":" . $parent . ":";

    if (!strstr($all_cats_string, $test_string)) {
        $cat_or_data = "data";
    } else {
        $cat_or_data = "cat";
    }

    # if this is a category
    if ($cat_or_data == "cat") {
        if ($parent == 999) {
            $output .= $parentname;
        } else {
            $output .= "<span class=faded>" . $parentname . "</span>";
        }
        # if this is a data field
    } elseif ($cat_or_data == "data") {
        $output .= "<span class=blue>" . $parentname . "</span>";
        # if user wants data displayed
        if ($get_data) {
            $output .= "<span class=blue>: </span>";
            # if there is data
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


#  Generate asset category, field & value expanded list
function gen_asset_fields($db, $dataid, $dataname, $machineid, $indent, $displayfields, $checkboxes, $get_data)
{

    # get the data
    $sql_data = "SELECT AD.dataid, AD.value " .
        "FROM AssetData AS AD, Machine AS M " .
        "WHERE M.machineid = " . $machineid . " " .
        "AND M.machineid = AD.machineid " .
        "AND AD.slatest = M.slatest ";

    # put the data in an array, keyed by dataid;
    $result_data = command($sql_data, $db);
    $data_array = array();
    while ($row = mysqli_fetch_assoc($result_data)) {
        $data_array[$row['dataid']] = $row['value'];
    }

    # Need to know which fields are data, not categories
    if ($get_data) {
        # have to get category fields:
        $sql_all_cats = "SELECT distinct DN.dataid, DN.name " .
            "FROM DataName AS DN, DataName AS DN2 " .
            "WHERE DN.dataid = DN2.parent " .
            "ORDER BY DN.parent, DN.ordinal";

        # put cats in an array, keyed by dataid;
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

    # build the output string
    $output = write_HTML($db, $dataid, $dataname, $all_cats_string, $indent, $displayfields, $checkboxes, $get_data, $data_array);

    return $output;
}


# Here, we just got root level (parent = 0) of asset fields
# not used
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

# HTTP_VARS: pass in either HTTP_GET_VARS or HTTP_POST_VARS
function get_checked_fields($HTTP_VARS, $db)
{
    # collect the fields the user checked on query page
    $checkedfields = array();
    reset($HTTP_VARS);
    while (list($k, $v) = each($HTTP_VARS)) {
        $posn = strpos($k, 'display_');
        if ((is_int($posn)) && ($posn == 0)) {
            $field = str_replace('display_', '', $k);
            # use an assoc array to collect fields as keys, to avoid duplicate entries
            $checkedfields[$field] = '';
        }
    }

    # get fieldnames
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

/* ------------------------------------------------------- \
|  get_display_fields gets the Asset fields (key=dataid,   |
|  value=name) the user wants to display in the result set |
|  of an asset query or search or in an asset report.  If  |
|  query, the displayfields are gotten from the passed     |
|  form data, else from the SavedAssetSearches table.      |
|                                                          |
|  params:                                                 |
|  $form_or_database: "form"|"database"                    |
|  HTTP_VARS:         for form, pass in the HTTP_GET_VARS  |
|                     or HTTP_POST_VARS, else ""           |
|  assetsearchid:     for database, pass in the id for     |
|                     that AssetSearch, else ""            |
\*--------------------------------------------------------*/

function get_display_fields($form_or_database, $HTTP_VARS, $assetsearchid, $db)
{
    $displayfields = array();
    if ($form_or_database == "form") {
        $displayfields = get_checked_fields($HTTP_VARS, $db);
    } elseif ($form_or_database == "database") {
        # get the field names from AssetSearches.displayfields
        $sql = "SELECT * FROM AssetSearches WHERE id=$assetsearchid";
        $result = command($sql, $db);
        $row = mysqli_fetch_assoc($result);
        $displayfieldnames = $row['displayfields'];
        # change ":" to ",", single-quote items, get rid of the leading and trailing "',"s
        $displayfieldnames = preg_replace("/:/", "', '", $displayfieldnames);
        $displayfieldnames = preg_replace("/^', /", "", $displayfieldnames);
        $displayfieldnames = preg_replace("/, '$/", "", $displayfieldnames);
        # get the field names for those ids
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



/* ------------------------------------------------------- \
|  get_match_fields gets the Asset fields the user wants   |
|  to apply matching criteria to in the result set of a    |
|  saved query (or report).  The fields are gotten from    |
|  the AssetSearchCriteria table.                          |
|                                                          |
|  $matchfields key=fieldname,                             |
|  value = $criteria {block, comparison, value, groupname} |
|                                                          |
\*--------------------------------------------------------*/

function get_match_fields($assetsearchid, $db)
{
    $matchfields = array();

    # get the data from AssetSearchCriteria
    $sql = "SELECT DN.dataid, DN.name, SC.* " .
        "FROM AssetSearchCriteria AS SC, DataName AS DN " .
        "WHERE SC.assetsearchid=$assetsearchid " .
        "AND DN.name=SC.fieldname " .
        "ORDER BY SC.block, SC.id";

    $result = command($sql, $db);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            # because a field may be included in more than one criterion
            #   an array, criteria, will contain the (multiple) criterions

            # build the criterion array
            $criterion['block'] = $row['block'];
            $criterion['comparison'] = $row['comparison'];
            $criterion['value'] = $row['value'];
            $criterion['groupname'] = $row['groupname'];

            # assign this criterion array as an element in an array named for the dataid.
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
        # now
        $now = time();

        # adjust
        switch ($date_code) {
            case  1:  // latest
                $date_value = $now;
                break;
            case  2:  // 1 day ago
                $date_value = yesterday($now);
                break;
            case  3:  // 1 week ago
                $date_value = yesterweek($now);
                break;
            case  4:  // 1 month ago
                $date_value = yestermonth($now);
                break;
            case  5:  // 3 months ago
                $date_value = yestermonth($now);
                $date_value = yestermonth($date_value);
                $date_value = yestermonth($date_value);
                break;
            case  6:  // 6 months ago
                $date_value = yestermonth($now);
                $date_value = yestermonth($date_value);
                $date_value = yestermonth($date_value);
                $date_value = yestermonth($date_value);
                $date_value = yestermonth($date_value);
                $date_value = yestermonth($date_value);
                break;
            case  7:  // 1 year ago
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


/* ------------------------------------------------------- \
|  SAMPLE OUTPUT:                                          |
|  (Version = '5.0.2195'' AND Time Zone LIKE 'Eastern%')   |
|  OR                                                      |
|  (Processor LIKE '%Intel%')                              |
|                                                          |
\*--------------------------------------------------------*/

function get_criteria_string($matchfields, $DateType, $date_code, $date_value, $db)
{
    global $comparison_options;
    $crit_strings  = array();
    $output = "";

    if (safe_count($matchfields)) {
        reset($matchfields);
        while (list($fieldname, $criteria) = each($matchfields)) {
            # criteria is an array of "criterions", cuz there can be more than 1 per data field.
            reset($criteria);
            while (list($index, $criterion) = each($criteria)) {
                $block = $criterion['block'];
                $comparison_code = $criterion['comparison'];
                $value = $criterion['value'];
                $comparison = $comparison_options[$comparison_code];

                # build criteria lines
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


# Check whether this Search can be changed from global to local
# Only allowed if the following DON'T exist:
#   - global notifications or reports that rely on the search
#   - (local) notifications or reports owned by another user that rely on the search
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
