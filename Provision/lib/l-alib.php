<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 1-Oct-02   EWB     Created.
12-Feb-03   EWB     Database factoring.
25-Jun-03   EWB     Ignore machines which are damaged or being updated.
 8-Dec-04   EWB     Works with any database.
23-Oct-07   BTE     Added some functions, moved some others.

*/

/* Constants shared with asset/adhoc.php */
define('constAdHocCritPrefix',  'crit');
define('constAdHocCritNone',    '-1');
define('constAssetCompareEqual', 1);
define('constAdHocGroupInc',     'grpinc');


/*
    |  Returns the asset name table.
    */

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
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $names;
}


/*
    |  Returns the list of all machines.
    */

function asset_machines($db)
{
    $mach = array();
    $sql  = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "asset.Machine\n"
        . " order by machineid";
    $res  = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $mid = $row['machineid'];
            $mach[$mid] = $row;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $mach;
}


/*
    |  Returns the list of all known asset groups.
    */

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
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $groups;
}


/*
    |  Returns a list of members of the specified group.
    |  The list will be empty if gid is not a group.
    */

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
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    }
    return $members;
}


/*
    |  Returns the list of children of the specified parent.
    |  The list will be empty if there are no children.
    |  The root of the tree is zero.
    */

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
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $children;
}


/*
    |  Returns a list of machine id's belonging
    |  to the specified site.
    */

function asset_site($site, $db)
{
    $machines = array();
    if ($site) {
        $qs  = safe_addslashes($site);
        $sql = "select machineid from " . $GLOBALS['PREFIX'] . "asset.Machine\n"
            . " where provisional = 0 and\n"
            . " cust = '$qs'\n"
            . " order by machineid";
        $res = command($sql, $db);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $machines[] = $row['machineid'];
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    }
    return $machines;
}


/*
    |  Returns a list of machine id's belonging to all the sites
    |  specified in the access list.   This replaces the old
    |  asset_owner function which required a database switch.
    */

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
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    }
    return $mlist;
}


/* ALIB_DrawYUITree

        Same basic structure as draw_tree in asset/console.php, but there
        are too many differences to commonize.  Draws a world tree for the
        Yahoo! User Interface (YUI) TreeView class.  Returns JavaScript.

        Pass in 0 for dataid and depth ($did and $depth) to start.
        This is recursive.  $nodename must be 'root' to start.  $names is
        the array returned by asset_names.

        Note that this procedure assumes a particular javascript context, that
        is:
            tree = new YAHOO.widget.TreeView("treeDiv1");
            var root = tree.getRoot();

        Where tree is a global variable to the page and root is the root of the
        newly created tree.  The name of the div where the tree is stored does
        not necessarily have to be named "treeDiv1".

        This procedure uses variables with a name of "nodeXXXX" where XXXX is
        a data identifier.
    */
function ALIB_DrawYUITree($did, $nodename, $names, $db)
{
    $js = '';
    $child  = asset_children($did, $db);
    $table  = asset_members($did, $db);

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
    /* Delete old temporary asset queries first */
    $now = time();
    $sql = "DELETE FROM AssetSearches WHERE created<($now-86400) AND "
        . 'querytype=' . constAssetQueryTypeAdHoc;
    redcommand($sql, $db);

    /* This date code is copied from asset/qury-act.php */
    $DateType      = trim(get_argument('DateType', 0, 'RelDate'));
    $date_value_MM = trim(get_argument('date_value_MM', 0, ''));
    $date_value_DD = trim(get_argument('date_value_DD', 0, ''));
    $date_value_YY = trim(get_argument('date_value_YY', 0, ''));
    $date_code    = intval(get_argument('date_code', 0, 0));
    $rel_days_ago = intval(get_argument('rel_days_ago', 0, 0));
    $error = '';
    // check date fields
    if ($DateType == 'ExactDate') {
        $date_code = 0;
        if (($date_value_YY > 0) && ($date_value_YY < 100)) {
            $date_value_YY += 2000;
        }
        if (($date_value_MM > 0) && ($date_value_DD > 0) &&
            ($date_value_YY > 0)
        ) {
            $valid_date = checkdate(
                $date_value_MM,
                $date_value_DD,
                $date_value_YY
            );
            if (!$valid_date) {
                $date_string = "$date_value_MM/$date_value_DD/"
                    . "$date_value_YY";
                $error .= error_date_invalid($date_string);
            }
            $date_unix = mktime(
                0,
                0,
                0,
                $date_value_MM,
                $date_value_DD,
                $date_value_YY
            );
            $date_value = $date_unix;
        } else {
            $error .= error_date_selection('exact');
        }
    } else { // Relative Date
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

    $sql  = "insert into AssetSearches set\n";
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
            $qid  = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        }
    }

    if ($qid == 0) {
        return 0;
    }

    $block = 0;
    reset($_POST);
    foreach ($_POST as $key => $value) {
        if (preg_match('/^' . constAdHocCritPrefix . '\d+$/', $key)) {
            /* Criteria specifier */
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
                $qv   = safe_addslashes($row['value']);
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
    $error = "<br>You did not choose any display fields.";
    return $error;
}

function error_date_selection($relative_or_exact)
{
    $error = "<br>You chose <i>$relative_or_exact date</i>" .
        " but did not select a corresponding value.";
    return $error;
}

function error_date_daysago()
{
    $error = "<br>You chose the relative date <i>some days ago...</i>" .
        " but did not enter a corresponding number of days.";
    return $error;
}

function error_date_invalid($date_string)
{
    $error = "<br>The date you selected, $date_string, is not a valid date.";
    return $error;
}
