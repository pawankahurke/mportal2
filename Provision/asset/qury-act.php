<?php

/*
Revision history:

Date        Who     What
----        ---     ----
23-Sep-02   NL      start file from 19-Sep-02, EWB, Giant refactoring.
23-Sep-02   NL      remove event code --> asset only
09-Oct-02   NL      add rel date "X days ago" functionality
09-Oct-02   NL      use check_can_make_assetsearch_local
09-Oct-02   NL      add error checking for Crit Bldr
11-Oct-02   NL      fix bug with rel dates & date_value
11-Oct-02   NL      add JS code to X_success fctns to reset clickedFolder cookie
11-Oct-02   NL      change clickedFolder cookie to use remote IP address for new queries
21-Oct-02   NL      add "temporary query" functionality ($disposition)
22-Oct-02   NL      confirm_save() and really_save()
22-Oct-02   NL      delete cookies by expiring them rather than setting value =""
23-Oct-02   NL      change create to add
23-Oct-02   NL      fixed bugs in reallymodify() if action==reallydup
31-Oct-02   NL      change outputJavascriptShowDaysAgo to outputJavascriptShowElement
04-Nov-02   NL      global_auth checks for "priv_aquery" instead of "priv_search"
04-Nov-02   NL      only display "and Global Property" if $global_auth
04-Nov-02   NL      allow for multiple error display; rename error functions
04-Nov-02   NL      move error checking (for empty or duplicate name)
                    out of really_modify and really_add functions;
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navigation
31-Dec-02   EWB     Single quotes for non-evaluated strings.
28-Jan-03   EWB     Access to $_SERVER variables.
29-Jan-03   EWB     Work for php3, no complex string expressions.
 3-Feb-03   EWB     Work without register_globals.
 7-Feb-03   EWB     Moved to asset world.
10-Feb-03   EWB     Uses sandbox libraries.
11-Feb-03   EWB     db_change().
24-Feb-03   EWB     Always set created/modified
24-Feb-03   EWB     Automatic Override: When creating a local override for
                    a global query also create local overrides for global
                    asset reports that used it.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
19-Mar-03   NL      Move debug_note line below $debug.
15-Apr-03   EWB     Don't need $access, $machine_ids_str.
23-Apr-03   EWB     Fixed sql syntax error in really_add.
 5-May-03   NL      Include l-js.php cuz outputJavascriptShowElement() moved there.
15-May-03   EWB     Another sql syntax error.
26-May-03   EWB     Fixed Javascript relative-date problem
24-Jun-03   NL      green(): close font tag
05-Dec-05   BJS     update_filter() no longer sets filtersites.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
17-Aug-07   BTE     Changes for summary sections phase 1.
04-Sep-07   BTE     Added asrchuniq handling.
04-Oct-07   BTE     Increased the size of the click here text.
23-Oct-07   BTE     Added some comments, moved some functions.

*/


ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-head2.php');
include('local.php');
include('../lib/l-cmth.php');
include('../lib/l-asst.php');
include('../lib/l-slct.php');
include('../lib/l-srch.php');
include('../lib/l-user.php');
include('../lib/l-crit.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-upar.php');
include('../lib/l-js.php');
include('../lib/l-sitflt.php');
include('../lib/l-arpt.php');
include('../lib/l-cnst.php');
include('../lib/l-alib.php');


$action  = trim(get_argument('action', 0, 'none'));

switch ($action) {
    case 'edit':
        $title = 'Edit An Asset Query';
        break;
    case 'reallyedit':
        $title = 'Asset Query Updated';
        break;
    case 'reallyduplicate':
        $title = 'Asset Query Duplicated';
        break;
    case 'delete':
        $title = 'Delete An Asset Query';
        break;
    case 'reallydelete':
        $title = 'Asset Query Deleted';
        break;
    case 'duplicate':
        $title = 'Duplicate Asset Query';
        break;
    case 'add':
        $title = 'Asset Query Added';
        break;
    case 'save':
        $title = 'Asset Query Added';
        break;
    case 'reallysave':
        $title = 'Asset Query Added';
        break;
    case 'cancel':
        $title = 'Asset Query Cancelled';
        break;
    default:
        $title = 'Asset Query';
        break;
}


function green($text)
{
    return "<font color=\"green\">$text</font>";
}

function debug_array($debug, $p)
{
    if ($debug) {
        reset($p);
        foreach ($p as $key => $data) {
            $msg = green("$key: $data");
            echo "$msg<br>\n";
        }
    }
}


function go_back($db)
{
?>
    Return to the <a href="query.php">Asset Query List</a>.
<?php
}

function error_generic($db)
{
?>
    <br><span class="red">
        There was a problem with your submission. The most likely
        cause of this error is either an attempt to create a search
        string with a name that already exists, or a submission
        beyond the maximum length (50 for name). Please try again.
    </span>
<?php
}


function error_crit_bldr()
{
    $error = "<br>Some criteria are not completely filled in.";
    return $error;
}

function error_name_exists($name)
{
    $error = "<br>Another query with the name <i>$name</i> already exists.";
    return $error;
}

function error_name_missing()
{
    $error = "<br>The name cannot be empty.";
    return $error;
}

function add_success($name, $searchstring)
{
    $remote = server_var('REMOTE_ADDR');
    $stext = nl2br($searchstring);
    $punctuation = ($stext) ? '' : '.';

    # delete the cookie for new queries
?>
    <script type="text/javascript">
        var now = new Date();
        document.cookie = "query_<?php echo $remote ?>_clickedFolder=; expires=" + now.toGMTString();
    </script>

    <br>
    You have added an Asset Query called <b><?php echo $name ?></b><?php echo $punctuation ?>
    <?php
    if ($stext) {
    ?>
        which includes the criteria:<br>
        <blockquote><i>
                <?php echo $stext ?>
            </i></blockquote>
        <br>
    <?php
    } else {
        echo "<br><br>\n";
    }
}


function find_asset_search($id, $db)
{
    $row = array();
    $sql = "select * from AssetSearches where id = $id";
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_array($res);

            if (!isset($row['date_code']))
                $row['date_code']  = 0;
            if (!isset($row['date_value']))
                $row['date_value'] = 0;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $row;
}


function find_global_search($name, $db)
{
    $qn   = safe_addslashes($name);
    $gid  = 0;
    $sql  = "select * from AssetSearches where\n";
    $sql .= " name = '$qn' and\n";
    $sql .= " global = 1";
    $res  = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_array($result);
            $gid = $row['id'];
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $gid;
}





function redirect($action, $id)
{
    $remote = server_var('REMOTE_ADDR');
    if ($action = 'add') {
        $label = $remote;
    } else {
        $label = $id;
    }
    # delete the cookie for new queries
    ?>
    <script type="text/javascript">
        var now = new Date();
        document.cookie = "query_<?php echo $label ?>_clickedFolder=; expires=" + now.toGMTString();
        window.location = "exec.php?qid=<?php echo $id ?>";
    </script>
<?php

    # this wont work because headers have already been sent:
    # header("Location: hfnreport.php3?dataset=asset&adhoc_or_saved=saved&id=19");
    exit;
}


function edit_success($word, $name, $searchstring, $id)
{
    $stext = nl2br($searchstring);
    $punctuation = ($stext) ? '' : '.';

    # delete the cookie for this queryid
?>
    <script type="text/javascript">
        var now = new Date();
        document.cookie = "query_<?php echo $id ?>_clickedFolder=; expires=" + now.toGMTString();
    </script>
    <br>
    You have <?php echo $word ?> an Asset Query called <b><?php echo $name ?></b><?php echo $punctuation ?>
    <?php
    if ($stext) {
    ?>
        which includes the criteria:<br>
        <blockquote><i>
                <?php echo $stext ?>
            </i></blockquote>
        <br>
    <?php
    } else {
        echo "<br><br>\n";
    }
}


function delete_success($action, $id, $name)
{
    $word = ($action == 'cancel') ? 'cancelled' : 'deleted';
    // delete the cookie for this queryid
    ?>
    <script type="text/javascript">
        var now = new Date();
        document.cookie = "query_<?php echo $id ?>_clickedFolder=; expires=" + now.toGMTString();
    </script>
    <br>
    You have <?php echo $word ?> an Asset Query called <b><?php echo $name ?></b>.
    <br><br>
    <?php
}



function prompt_delete($id, $name, $searchstring)
{
    $self  = server_var('PHP_SELF');
    $stext = nl2br($searchstring);
    $punctuation = ($stext) ? '' : '?';
    $msg = "<br>Do you really want to delete <b>$name</b>$punctuation";
    echo $msg;

    if ($stext) {
    ?>
        which includes the criteria below:<br>

        <blockquote><i>
                <?php echo $stext ?>
            </i></blockquote>
        <br>
    <?php
    } else {
    ?>
        <br><br>
    <?php
    }

    $href = "$self?action=reallydelete&id=$id";
    echo "<a href='$href'>[Yes]</a>";
    ?>
    &nbsp;&nbsp;
    <a href='query.php'>[No]</a>
<?php
}


function delete_problem($name, $can_delete)
{
    if ($can_delete == 'dependencies')
        $why = "there are reports that rely on it";
    else
        $why = "you did not create it";
?>
    <br>
    <br>
    <font color="red"><b><?php echo $name ?></b> cannot be deleted
        because <?php echo $why ?>.</font>
    <br>
    <br>
<?php
}


function confirm_delete($authuser, $id, $name, $searchstring, $db)
{
    # check if search can be deleted w/o disrupting notifications and reports
    $can_delete = check_can_delete_item('AssetSearches', $authuser, $id, $db);

    if ($can_delete == 'ok')
        prompt_delete($id, $name, $searchstring);
    else {
        delete_problem($name, $can_delete);
        go_back(0);
    }
}


function really_delete($action, $id, $name, $authuser, $db)
{
    $good = 0;
    $can_delete = check_can_delete_item('AssetSearches', $authuser, $id, $db);
    if ($can_delete == 'ok') {
        $qu  = safe_addslashes($authuser);
        $sql = "delete from AssetSearches where id = $id and username = '$qu'";
        if (redcommand($sql, $db)) {
            if (mysqli_affected_rows($db)) {
                $sql2 = "delete from AssetSearchCriteria where assetsearchid = $id";
                if (redcommand($sql2, $db)) {
                    $good = 1;
                }
            }
        }
    }

    if ($good) {
        delete_success($action, $id, $name);
        go_back(0);
    }
}


/*
    |  A global asset query has to remain global when
    |  there are asset reports that rely on it.
    */

function must_remain_global($db)
{
?>
    <br>
    <font color="red">This Asset Query must
        remain global because there are Reports
        that rely on it.</font><br>
<?php
}


function really_add(
    $disposition,
    $name,
    $authuser,
    $global,
    $displayfields_string,
    $DateType,
    $date_code,
    $date_value,
    $rowsize,
    $refresh,
    $now,
    $db
) {

    # use only for CritBldr fields (Block$iRow$j); date fields have been changed
    $expires = get_future_expires($disposition);
    $searchstring = '';
    $good = 0;

    $qn   = safe_addslashes($name);
    # note: we are not inserting searchstring yet
    $sql  = "insert into AssetSearches set\n";
    $sql .= " name='$qn',\n";
    $sql .= " username='$authuser',\n";
    $sql .= " global=$global,\n";
    $sql .= " displayfields='$displayfields_string',\n";
    $sql .= " rowsize='$rowsize',\n";
    $sql .= " refresh='$refresh',\n";
    $sql .= " date_code = $date_code,\n";
    $sql .= " date_value = $date_value,\n";
    $sql .= " expires=$expires,\n";
    $sql .= " created=$now,\n";
    $sql .= " modified=$now";
    $asrchuniq = USER_GenerateManagedUniq($name, $authuser, $db);
    $sql .= ",\n asrchuniq = '$asrchuniq'";

    if (redcommand($sql, $db)) {
        if (mysqli_affected_rows($db)) {
            $good = 1;
        }
    }

    # insert the criteria data into the database
    if ($good) {
        $good = 0;
        $id  = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        $new_block = 0;
        $previ = 0;

        reset($_POST);
        foreach ($_POST as $k => $v) {
            # find all the Crit Bdr data fields (e.g. Block1Row1field)
            if (strlen($v) && preg_match('/^Block[1-9]Row[1-9]field/', $k)) {

                # What block ($i) and row ($j) are we in?
                $i = substr($k, 5, 1);  // Block
                $j = substr($k, 9, 1);  // Row

                # re-count blocks incase user deleted a block above other exisitg blocks
                if ($previ != $i) {
                    $new_block++;
                }

                # get data field name, eg Block1Row1field
                $fieldname = $v;

                # get comparison field, eg Block1Row1comparison
                $string = "Block" . $i . "Row" . $j . "comparison";
                $comparison = get_argument($string, 0, 0);

                # get value field, eg Block1Row1value
                $string = "Block" . $i . "Row" . $j . "value";
                $value = get_argument($string, 0, '');

                # get groupname field, eg Block1Row1group
                $string = "Block" . $i . "Row" . $j . "group";
                $groupname = get_argument($string, 0, '');

                $qv   = safe_addslashes($value);
                $sql2 = "insert into AssetSearchCriteria set\n" .
                    " assetsearchid = $id,\n" .
                    " block = $new_block,\n" .
                    " fieldname = '$fieldname',\n" .
                    " comparison = $comparison,\n" .
                    " value = '$qv',\n" .
                    " groupname = '$groupname',\n" .
                    " expires='$expires'";

                $result = redcommand($sql2, $db);
                if (!$result) $good = 0;

                $previ = $i;
            }
        } # end while

        # now create the searchstring
        $matchfields = get_match_fields($id, $db);
        $searchstring = get_criteria_string($matchfields, $DateType, $date_code, $date_value, $db);

        # and update the AssetSearches table
        $qs   = safe_addslashes($searchstring);
        $sql  = "update AssetSearches set\n";
        $sql .= " searchstring='$qs',\n";
        $sql .= " modified=$now\n";
        $sql .= " where id = $id";

        if (redcommand($sql, $db)) {
            if (mysqli_affected_rows($db)) {
                $good = 1;
            }
        }


        if ($good) {
            if (strstr($disposition, 'Run')) {
                redirect('add', $id);
            } else {
                add_success($name, $searchstring);
                go_back($db);
            }
        } else {
            error_generic($db);
        }
    } # end if good

    if ($good) {
        $isparent = url::postToAny('isparent');
        if ($isparent) {
            $row = array(
                'id' => $id,
                'asrchuniq' => $asrchuniq
            );
            $super = ARPT_WriteSingleSearchArray(
                $row,
                'window.opener.',
                $db
            );
            echo constFontSizeClickHere
                . 'Click <a href="#" onclick="' . $super
                . 'addDynamicItemButton('
                . $isparent . ',\'' . $asrchuniq . '\',\'' . $qn
                . '\');window.close();">here</a> to add this new asset '
                . 'search to the section you are defining.</font>';
        }
    }
}


/*
    | Only updates the report with the values in
    | $row, no longer sets the sitefilter.
   */
function update_filter($row, $db)
{
    $rid = update_asset($row, $db);
}


/*
    |  This happens when there is a previously existing global
    |  search, and the user has just created a local saved
    |  search with the same name.
    |
    |  If this user owns any local reports
    |  that use the global saved search, they must be updated
    |  to use the local saved search instead.
    */

function override($gid, $lid, $now, $name, $authuser, $db)
{
    $qu   = safe_addslashes($authuser);
    $qn   = safe_addslashes($name);
    $sql  = "select * from AssetReports\n";
    $sql .= " where global = 1 and\n";
    $sql .= " username != '$qu' and\n";
    $sql .= " searchid = $gid";
    $list = find_many($sql, $db);
    if ($list) {
        reset($list);
        foreach ($list as $key => $data) {
            $an   = safe_addslashes($data['name']);
            $sql  = "select * from AssetReports\n";
            $sql .= " where global = 0 and\n";
            $sql .= " name = '$an' and\n";
            $sql .= " username = '$qu'";
            $row  = find_many($sql, $db);
            if ($row)
                debug_note("local override exists: $an");
            else {
                debug_note("create local override: $an");
                $new = $data;
                $new['id'] = 0;
                $new['global'] = 0;
                $new['username'] = $authuser;
                $new['searchid'] = $lid;
                $new['modified'] = $now;
                update_filter($new, $db);
            }
        }
    }


    $sql  = "update AssetReports set\n";
    $sql .= " searchid = $lid,\n";
    $sql .= " modified = $now where\n";
    $sql .= " searchid = $gid and\n";
    $sql .= " global = 0 and\n";
    $sql .= " username = '$qu'";
    redcommand($sql, $db);
}


/*
    |  Most of the time, we will do nothing.
    |  We check to see if we need to do a local
    |  override for this name.
    */

function override_name($name, $authuser, $now, $db)
{
    $gid  = 0;
    $lid  = 0;
    $qu   = safe_addslashes($authuser);
    $qn   = safe_addslashes($name);
    $sql  = "select * from AssetSearches where\n";
    $sql .= " global = 1 and\n";
    $sql .= " username != '$qu' and\n";
    $sql .= " name = '$qn'";
    $res  = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_array($res);
            $gid = $row['id'];
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    if ($gid) {
        $sql  = "select * from AssetSearches\n";
        $sql .= " where global = 0 and\n";
        $sql .= " username = '$qu' and\n";
        $sql .= " name = '$qn'";
        $res  = redcommand($sql, $db);
        if ($res) {
            if (mysqli_num_rows($res) == 1) {
                $row = mysqli_fetch_array($res);
                $lid = $row['id'];
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    }
    if (($gid) && ($lid)) {
        override($gid, $lid, $now, $name, $authuser, $db);
    }
}



function really_modify(
    $global_auth,
    $disposition,
    $action,
    $id,
    $name,
    $dupname,
    $authuser,
    $searchstring,
    $global,
    $displayfields,
    $displayfields_string,
    $DateType,
    $date_code,
    $date_value,
    $rowsize,
    $refresh,
    $now,
    $db
) {
    # use only for CritBldr fields (Block$iRow$j); date fields have been changed

    $good = 0;
    $disabled = '';
    $expires_current = get_current_expires($id, $db);
    $expires = get_future_expires($disposition);

    # Check if this is a case of a non-owner editing a global item
    $nonowner_edit  = check_nonowner_edit($authuser, 'AssetSearches', $id, $db);
    $can_make_local = 1;

    if ($action == 'reallyduplicate') {
        # note: we are not inserting searchstring yet
        $qn   = safe_addslashes($dupname);
        $qu   = safe_addslashes($authuser);
        $sql  = "insert into AssetSearches set\n";
        $sql .= " name='$qn',\n";
        $sql .= " username='$qu',\n";
        $sql .= " global=$global,\n";
        $sql .= " displayfields='$displayfields_string',\n";
        $sql .= " date_code = $date_code,\n";
        $sql .= " date_value = $date_value,\n";
        $sql .= " rowsize='$rowsize',\n";
        $sql .= " refresh='$refresh',\n";
        $sql .= " expires=$expires,\n";
        $sql .= " created=$now,\n";
        $sql .= " modified=$now";
        $asrchuniq = USER_GenerateManagedUniq($dupname, $authuser, $db);
        $sql .= ",\n asrchuniq = '$asrchuniq'";

        $result = redcommand($sql, $db);
        if ($result) {
            if (mysqli_affected_rows($db)) {
                $good = 1;
            }
        }
        $id = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        debug_note("name:$dupname, owner:$authuser, id:$id");
    }

    if ($action == 'reallyedit') {

        # If non-owner is editing (global item), make local copy (insert)
        if ($nonowner_edit) {
            # note: we are not inserting searchstring yet
            $qn   = safe_addslashes($name);
            $qu   = safe_addslashes($authuser);
            $sql  = "insert into AssetSearches set\n";
            $sql .= " name='$qn',\n";
            $sql .= " username='$qu',\n";
            $sql .= " global=0,\n";
            $sql .= " displayfields='$displayfields_string',\n";
            $sql .= " date_code = $date_code,\n";
            $sql .= " date_value = $date_value,\n";
            $sql .= " rowsize='$rowsize',\n";
            $sql .= " refresh='$refresh',\n";
            $sql .= " expires='$expires',\n";
            $sql .= " created=$now,\n";
            $sql .= " modified=$now";
            $asrchuniq = USER_GenerateManagedUniq($name, $authuser, $db);
            $sql .= ",\n asrchuniq = '$asrchuniq'";

            $result = redcommand($sql, $db);
            $id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
            debug_note("name:$name, owner:$authuser, id:$id");

            $sql = "select * from AssetSearches where id = $id";
            $result = redcommand($sql, $db);

            if ($result) {
                if (mysqli_affected_rows($db)) {
                    $good = 1;
                }

                if (mysqli_num_rows($result)) {
                    $row    = mysqli_fetch_array($result);
                    $id     = $row['id'];
                    $name   = $row['name'];
                    $owner  = $row['username'];
                    $global = $row['global'];
                }
            }
        } else {
            # if global checkbox was turned off and search can become local
            # w/o disrupting Notifications and Reports, and the global checkbox
            # was displayed, update global field


            $old = find_asset_search($id, $db);

            $can_make_local = check_can_make_assetsearch_local($authuser, $id, $db);

            $gbl = $old['global'];

            if (($gbl) && (!$global) && ($can_make_local)) {
                debug_note("global becomes local");
                // change global to local
                $gbl = 0;
            }

            if (($global) && ($global_auth) && (!$gbl)) {
                // change local to global

                $gid = find_global_search($name, $db);
                if ($gid == 0) {
                    debug_note("global becomes local");
                    $gbl = 1;
                }
            }


            # note: we are not inserting searchstring yet
            $qn   = safe_addslashes($name);
            $sql  = "update AssetSearches set\n";
            $sql .= " name = '$qn',\n";
            $sql .= " global = $gbl,\n";
            $sql .= " displayfields='$displayfields_string',\n";
            $sql .= " date_code = $date_code,\n";
            $sql .= " date_value = $date_value,\n";
            $sql .= " rowsize='$rowsize',\n";
            $sql .= " refresh='$refresh',\n";
            $sql .= " expires='$expires',\n";
            $sql .= " modified=$now\n";
            $sql .= " where id = $id";

            $result = redcommand($sql, $db);
            if ($result) {
                $good = 1;
            }
        }
    }


    # insert the criteria data into the database
    if ($good) {
        $good = 0;

        # delete old criteria
        $sql_delete = "delete from AssetSearchCriteria\n" .
            " where assetsearchid = $id";
        $result_delete = redcommand($sql_delete, $db);
        if ($result) {
            $good = 1;
        }

        # add current ones
        $new_block = 0;
        $previ = 0;

        reset($_POST);
        foreach ($_POST as $k => $v) {
            # find all the CritBldr data fields (e.g. Block1Row1field)
            if (strlen($v) && preg_match('/^Block[1-9]Row[1-9]field/', $k)) {

                $good = 0;

                # What block ($i) and row ($j) are we in?
                $i = substr($k, 5, 1);  // Block
                $j = substr($k, 9, 1);  // Row

                # re-count blocks in case user deleted a block above other existing blocks
                if ($previ != $i) {
                    $new_block++;
                }

                # get data field name, eg Block1Row1field
                $fieldname = $v;

                # get comparison field, eg Block1Row1comparison
                $string = "Block" . $i . "Row" . $j . "comparison";
                $comparison = get_argument($string, 0, 0);

                # get value field, eg Block1Row1value
                $string = "Block" . $i . "Row" . $j . "value";
                $value = get_argument($string, 0, '');

                # get groupname field, eg Block1Row1group
                $string = "Block" . $i . "Row" . $j . "group";
                $groupname = get_argument($string, 0, '');

                $qval = safe_addslashes($value);
                $sql_add = "insert into AssetSearchCriteria set\n" .
                    " assetsearchid = $id,\n" .
                    " block = $new_block,\n " .
                    " fieldname = '$fieldname',\n" .
                    " comparison = $comparison,\n " .
                    " value = '$qval',\n " .
                    " groupname = '$groupname',\n " .
                    " expires='$expires'";
                $result_add = redcommand($sql_add, $db);

                if ($result_add) {
                    if (mysqli_affected_rows($db)) {
                        $good = 1;
                    }
                }

                $previ = $i;
            }
        }

        # now create the searchstring
        $matchfields = get_match_fields($id, $db);
        $searchstring = get_criteria_string($matchfields, $DateType, $date_code, $date_value, $db);

        # and update the AssetSearches table
        $good = 0;
        $qs   = safe_addslashes($searchstring);
        $sql  = "update AssetSearches set\n";
        $sql .= " searchstring='$qs',\n";
        $sql .= " modified=$now\n";
        $sql .= " where id = $id";

        if (redcommand($sql, $db)) {
            $good = 1;
        }
    }

    if ($good) {
        if (($expires_current != 0) && ($expires != 0)) {
            # temp query; just redirect
            redirect($action, $id);
        } else {
            $name = get_name('AssetSearches', $id, $db);
            $name = ($dupname) ? $dupname : $name;
            override_name($name, $authuser, $now, $db);
            if (strstr($disposition, 'Run') || url::issetInPost('redirect')) {
                redirect($action, $id);
            } else {
                $word = ($disposition == 'Save') ? 'saved' : 'updated';
                edit_success($word, $name, $searchstring, $id);
                if ((!$can_make_local) && (!$global) && (!$disabled)) {
                    must_remain_global($db);
                }
                go_back($db);
            }
        }
    } else {
        error_generic($db);
    }
}


function confirm_save($id, $name, $db)
{
    $self = server_var('PHP_SELF');
    if ($name == '') {
        $message = "You must provide a name before
                        you can save this query.";
    } else {
        $message = "You must provide a unique name before
                        you can save this query. Another query with the name
                        <b>$name</b> already exists.
                        Please chose another name.";
    }

    echo <<< HERE


<form method="post" action="$self" name="form">
    <input type="hidden" name="action" value="reallysave">
    <input type="hidden" name="id" value="$id">

<table border="0" cellpadding="3">
    <tr>
        <td>
            <span class="red">
                $message
            </span>
            <br><br>
        </td>
    </tr>
    <tr>
        <td>
            Name: <input type="text" maxlength="50" size="40" width="40" name="name">
        </td>
    </tr>
</table>

<br>

<table border="0" cellpadding="3">
    <tr>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type=submit value="Submit">
            &nbsp;&nbsp;&nbsp;
            <input type="reset" value="Reset">
            <br><br>
        </td>
    </tr>
</table>

</form>

HERE;
}


# updates the record of the temp query with a name and sets expires = 0
function really_save($id, $name, $authuser, $now, $db)
{
    $error = '';
    $good = 0;

    // Check name: not empty; not duplicate
    if (trim($name) == '') {
        $error = error_name_missing();
    } else {   //  check duplicates
        $exists = check_existing_name_asset($name, $authuser, $id, $db);
        if ($exists) {
            $error = error_name_exists($name);
        }
    }

    if ($error) {
        echo "<span class=\"red\">$error</span>";
    } else {

        # Update Asset Searches with name (may have changed fromn blank) & expires
        $qn   = safe_addslashes($name);
        $sql  = "update AssetSearches set\n";
        $sql .= " name = '$qn',\n";
        $sql .= " modified = $now,\n";
        $sql .= " expires='0'\n";
        $sql .= " where id = $id";

        $result = redcommand($sql, $db);
        if ($result) {
            $good = 1;
        }

        # Update the criteria data with expires
        if ($good) {
            $good = 0;

            $sql  = "update AssetSearchCriteria set\n";
            $sql .= " expires ='0',\n";
            $sql .= " modified = $now\n";
            $sql .= " where assetsearchid = $id";

            if (redcommand($sql, $db)) {
                $good = 1;
            }
        }


        if ($good) {
            edit_success('saved', $name, '', $id);
            go_back($db);
        } else {
            error_generic($db);
        }
    }
}



function confirm_modify(
    $global_auth,
    $action,
    $id,
    $name,
    $global,
    $authuser,
    $searchstring,
    $displayfields,
    $date_code,
    $date_value,
    $rowsize,
    $refresh,
    $expires,
    $db
) {

    global $openblockrows;

    #rel or exact date?
    $self = server_var('PHP_SELF');
    if ($date_code == 0) {
        $rel_checked   = '';
        $exact_checked = 'checked';
    } else {
        $rel_checked   = 'checked';
        $exact_checked = '';
    }

    // parse $date_value into DD, MM, YY

    if ($date_code == 0) {
        $date_value_MM = date('m', $date_value);
        $date_value_DD = date('d', $date_value);
        $date_value_YY = date('y', $date_value);
    } else {
        $date_value_MM = '';
        $date_value_DD = '';
        $date_value_YY = '';;
    }

    $gprop = ($global_auth) ? 'and a global property' : '';
    echo <<< HERE

<form method="post" action="$self" name="form">
    <input type="hidden" name="action" value="really$action">
    <input type="hidden" name="id" value="$id">
    <!--
        Since checkbox values aren't passed if unchecked,
        provide a hidden variable as a default
    -->
    <input type="hidden" name="global" value="0">



<table border="0" cellpadding="3">
    <tr>
        <td colspan="2">
            <br>
            <b>Provide a Name $gprop:</b>
        </td>
    </tr>
    <tr>
        <td>Name:</td>
        <td>
HERE;

    $dup    = ($action == 'duplicate') ? 1 : 0;
    $field  = ($dup) ? 'dupname' : 'name';
    $text   = ($dup) ? "Copy of $name" : $name;
    $value  = str_replace('"', '&#034;', $text);

    echo  <<< HERE
            <input type="text" size="40" name="$field" value="$value" width="40">
        </td>
    </tr>
HERE;

    if ($global_auth) {
        $gbl_checked = ($global) ? 'checked' : '';
        echo <<< HERE

    <tr>
        <td>Global:</td>
        <td>
            <input type="checkbox" name="global" $gbl_checked value="1">
        </td>
    </tr>

HERE;
    }
?>

    </table>
    <br>

    <table border=0 cellpadding=3>
        <tr>
            <td bgcolor="#EEEEEE" valign="top" nowrap>
                <b>Select Fields to Display:</b><br>
                <span class="footnote"><i>
                        <br>
                        Click on <img src="../pub/plus.gif">'s and
                        <img src="../pub/minus.gif">'s
                        to navigate <span class="faded">categories</span>.<br>
                        Click on <img src="../pub/check_box.gif">'s to choose
                        <span class="blue">fields</span> to be displayed.<br>
                        Clicking on the <span class="blue">field</span> itself
                        will enter it in<br>
                        the Search Criteria table to the right.<br>
                    </i></span>
                <br>
                <?php

                outputJavascriptAssetTree(1, 1, 0, 1, $displayfields);
                ?>
                <br>
            </td>

            <td width="20" rowspan="4">&nbsp;</td>

            <td bgcolor="#EEEEEE" valign="top">
                <b>Create Search Criteria:</b><br>
                <span class="footnote"><i>
                        <br>
                        <!-- Click on a <span class="blue"><u>field</u></span> on the
                left to enter <span style='background:#CCCCCC;'>search
                criteria</span> for it on the right.<br> -->
                        1. Click on a <span class="blue">field</span> in the list
                        to the left to include it in the search
                        criteria below.<br>
                        <img src="../pub/closed.gif" width="7" height="7" border="0">
                        &nbsp;It will appear next to the black arrow.
                        Click in any field to move the arrow to another row.<br>
                        2. Select a comparison option. <br>
                        3. Type in a value to be matched.<br>
                        <!-- To group fields so that matching fields are associated,
                enter a <span class=faded>category</span> from the left
                in the group by field.<br> -->

                        <br>
                    </i></span>

                <table cellpadding="3" cellspacing="3" border="1" bordercolor="#999999">
                    <tr>
                        <td></td>
                        <td>1. field name</td>
                        <td>2. comparison option</td>
                        <td>3. value to match</td>
                        <!-- <td>group by</td> -->
                    </tr>

                    <?php

                    outputJavascriptCritBldr(9, 9, $openblockrows);
                    echo <<< HERE


            </table>
       </td>
    </tr>
</table>

<br>

<table border="0" cellpadding="3">
    <tr>
        <td><b>Select Date:</b></td>
        <td>&nbsp;</td>
    </tr>

    <tr>
        <td>
            <input type="radio" name="DateType" value="RelDate" $rel_checked>Relative Date:
        </td>
        <td>

HERE;
                    /* This code is copied in three places:
                asset/adhoc.php
                asset/qury-add.php */
                    global $date_code;

                    $date_codes[0] = ' - - - - - - - - - - - - - - -';
                    $date_codes[1] = 'latest';
                    $date_codes[2] = '1 day ago';
                    $date_codes[3] = 'some days ago...'; # if index changes, change in outputJavascriptDaysAgo()
                    $date_codes[4] = '1 week ago';
                    $date_codes[5] = '1 month ago';
                    $date_codes[6] = '3 months ago';
                    $date_codes[7] = '6 months ago';
                    $date_codes[8] = '1 year ago';

                    $select  = html_select('date_code', $date_codes, $date_code, 1);
                    $show    = "showElement('rel_days_ago,rel_days_ago_text', document.form.date_code.selectedIndex,3,'')";
                    $change  = "onChange=\"$show\"";
                    $pattern = 'size="1"';
                    $replace = "\n  $change $pattern";
                    echo str_replace($pattern, $replace, $select);
                    ?>
            </td>
            <?php
            $rel_days_ago = ($date_code == 3) ? $date_value : '';
            ?>
            <td nowrap>
                <input type="text" size="2" name="rel_days_ago" id="rel_days_ago" value='<?php echo $rel_days_ago ?>'>
                <span id="rel_days_ago_text">days ago</span>
                <?php

                outputJavascriptShowElement(
                    "rel_days_ago,rel_days_ago_text",
                    "document.form.date_code.selectedIndex",
                    "3",
                    ""
                );
                ?>
            </td>
        </tr>
        <tr>
            <td><input type="Radio" Name="DateType" Value="ExactDate" <?php echo $exact_checked ?>>Exact Date: </td>
            <td colspan=2>
                <?php
                echo date_selector($date_value_MM, $date_value_DD, $date_value_YY)
                ?>
            </td>
        </tr>

        <tr>
            <td colspan=2><br><b>Select Display Options:</b></td>
            <td>&nbsp;</td>
        </tr>

        <tr>
            <td colspan=2>Number of Results per Page:</td>
            <td>
                <?php

                $rowsizes = array('25', '50', '100');
                echo html_select('rowsize', $rowsizes, $rowsize, 0);

                ?>
            </td>
        </tr>

        <tr>
            <td colspan=2>Refresh Page Every (in minutes):</td>
            <td>
                <?php

                $refreshes = array('never', '5', '10', '15');
                echo html_select('refresh', $refreshes, $refresh, 0);

                ?>
            </td>
        </tr>
    </table>
    <br>
    <table border="0" cellpadding="3">
        <tr>
            <td>&nbsp;</td>
            <td>
                <?php if ($expires > 0) {  ?>
                    <input type="submit" name="disposition" value="Save">
                    &nbsp;&nbsp;&nbsp;
                    <?php /* Run: still gets saved as a temp query */ ?>
                    <input type="submit" name="disposition" value="Run">
                    &nbsp;&nbsp;&nbsp;
                    <input type="submit" name="disposition" value="Save and Run">
                <?php } else {  ?>
                    <input type="submit" name="" value="Update">
                    &nbsp;&nbsp;&nbsp;
                    <input type="submit" name="redirect" value="Update and Run">
                <?php } ?>
                &nbsp;&nbsp;&nbsp;
                <input type="reset" value="Reset">
                <br><br>
            </td>
        </tr>
    </table>

    </form>

<?php
}


/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo custom_html_header(
    $title,
    $comp,
    $authuser,
    '',
    0,
    0,
    0,
    '<script type="text/javascript" language="JavaScript" src="'
        . '../report/control.js"></script>',
    $db
);

$now = time();
$debug = user_info($db, $authuser, 'priv_debug', 0);

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$global_auth = user_info($db, $authuser, 'priv_aquery', 0);
debug_array($debug, $_POST);
debug_array($debug, $_GET);
$error = '';
$openblockrows = '';

$name          = trim(get_argument('name', 0, ''));
$dupname       = trim(get_argument('dupname', 0, ''));
$disposition   = trim(get_argument('disposition', 0, ''));
$searchstring  = trim(get_argument('searchstring', 0, ''));
$refresh       = trim(get_argument('refresh', 0, 'never'));
$DateType      = trim(get_argument('DateType', 0, 'RelDate'));
$date_value_MM = trim(get_argument('date_value_MM', 0, ''));
$date_value_DD = trim(get_argument('date_value_DD', 0, ''));
$date_value_YY = trim(get_argument('date_value_YY', 0, ''));

$id           = intval(get_argument('id', 0, 0));
$rowsize      = intval(get_argument('rowsize', 0, 0));
$date_code    = intval(get_argument('date_code', 0, 0));
$rel_days_ago = intval(get_argument('rel_days_ago', 0, 0));
$global       = (get_argument('global', 0, 0)) ? 1 : 0;
$asrchuniq    = get_string('asrchuniq', '');

if (($id == 0) && ($asrchuniq != '')) {
    $sql = 'SELECT id FROM ' . $GLOBALS['PREFIX'] . 'asset.AssetSearches WHERE asrchuniq=\''
        . $asrchuniq . '\'';
    $row = find_one($sql, $db);
    if ($row) {
        $id = $row['id'];
    }
}



?>
<table>
    <tr>
        <td>
            <?php


            /*
    |   Get asset search form values & perform error checking
    */


            db_change($GLOBALS['PREFIX'] . 'asset', $db);

            if ($action == 'none') {
                $error .= "<br>Error -- no action specified.";
            }

            if (($id <= 0) && ($action != 'add')) {
                $error .= "<br>Error -- no id specified.";
            }

            if (($action == 'add') || ($action == 'reallyedit') || ($action == 'reallyduplicate')) {
                # Check name (unless temp): not empty; not duplicate
                $expires = get_future_expires($disposition);
                debug_note("name:($name) dupname:($dupname) action:$action expires: $expires");
                if ($expires == 0) {   //  not a temp query
                    $dup = ($action == 'reallyduplicate') ? 1 : 0;
                    $queryname = ($dup) ? $dupname : $name;
                    if ($queryname == '') {
                        $error .= error_name_missing();
                    } else {
                        //  ...check whether another query by this name exists.
                        $queryid = ($action == 'add') ? '' : $id;
                        $exists = check_existing_name_asset($queryname, $authuser, $queryid, $db);
                        if ($exists) {
                            $error .= error_name_exists($queryname);
                        }
                    }
                }


                # display fields
                $displayfields = get_display_fields('form', $_POST, '', $db);

                if (safe_count($displayfields) == 0) {
                    $error .= error_display_field($db);
                }
                $displayfields_string = ':' . implode(':', $displayfields) . ':';
                /* This date code is copied in l-alib.php as well */
                // check date fields
                if ($DateType == 'ExactDate') {
                    $date_code = 0;
                    if (($date_value_YY > 0) && ($date_value_YY < 100)) {
                        $date_value_YY += 2000;
                    }
                    if (($date_value_MM > 0) && ($date_value_DD > 0) && ($date_value_YY > 0)) {
                        $valid_date = checkdate($date_value_MM, $date_value_DD, $date_value_YY);
                        if (!$valid_date) {
                            $date_string = "$date_value_MM/$date_value_DD/$date_value_YY";
                            $error .= error_date_invalid($date_string);
                        }
                        $date_unix = mktime(0, 0, 0, $date_value_MM, $date_value_DD, $date_value_YY);
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

                # check criteria builder
                reset($_POST);
                foreach ($_POST as $k => $v) {
                    # find all the CritBldr data fields (e.g. Block1Row1field or Block1Row1value)
                    if (strlen($v) && preg_match('/^(Block[1-9]Row[1-9])(field|value)/', $k, $matches)) {
                        $field_field = $_POST[$matches[1] . 'field'];
                        $comp_field  = $_POST[$matches[1] . 'comparison'];
                        $value_field = $_POST[$matches[1] . 'value'];
                        if (($field_field == '') ||
                            ($value_field == '') ||
                            ($comp_field  ==  0)
                        ) {
                            $error .= error_crit_bldr();
                        }
                    }
                }
            }



            if ($error) {

                echo "<span class=red>$error</span>";
            } else {

                if (($action == 'edit') || ($action == 'duplicate') || ($action == 'delete')) {
                    /*
            |   Get existing values from the database (not from the form)
            */

                    $good = 0;
                    $field_values = array();

                    $row = find_asset_search($id, $db);
                    if ($row) {
                        $id            = $row['id'];
                        $name          = $row['name'];
                        $owner         = $row['username'];
                        $global        = $row['global'];
                        $searchstring  = $row['searchstring'];
                        $displayfields = $row['displayfields'];
                        $date_code     = $row['date_code'];
                        $date_value    = $row['date_value'];
                        $rowsize       = $row['rowsize'];
                        $refresh       = $row['refresh'];
                        $expires       = $row['expires'];
                        $good          = 1;
                    }

                    $sql2 = "select * from AssetSearchCriteria\n" .
                        " where assetsearchid = $id\n" .
                        " order by block, id";
                    $result2 = redcommand($sql2, $db);

                    if ($result2) {
                        $prevblock = 0;
                        while ($row = mysqli_fetch_array($result2)) {
                            $block        = $row['block'];
                            # var "row" already taken
                            $blockrow     = ($block == $prevblock) ? ++$blockrow : 1;

                            # used by criteria builder to display blocks containing data
                            $openblockrows .= $block . ':' . $blockrow . '-';

                            $field_value  = 'Block' . $block . 'Row' . $blockrow . 'field';
                            $$field_value = $row['fieldname'];
                            $comp_value   = 'Block' . $block . 'Row' . $blockrow . 'comparison';
                            $$comp_value  = $row['comparison'];
                            $value_value  = 'Block' . $block . 'Row' . $blockrow . 'value';
                            $$value_value = $row['value'];

                            $prevblock = $block;
                        }
                        ((mysqli_free_result($result2) || (is_object($result2) && (get_class($result2) == "mysqli_result"))) ? true : false);
                        # Dont pass these to confirm_modify then to critbldr; use global vars
                    }
                }

                if (($action == 'edit') || ($action == 'duplicate')) {
                    if ($good) {
                        confirm_modify(
                            $global_auth,
                            $action,
                            $id,
                            $name,
                            $global,
                            $authuser,
                            $searchstring,
                            $displayfields,
                            $date_code,
                            $date_value,
                            $rowsize,
                            $refresh,
                            $expires,
                            $db
                        );
                    }
                }

                if ($action == 'delete') {
                    if ($good) {
                        confirm_delete($authuser, $id, $name, $searchstring, $db);
                    }
                }

                if (($action == 'reallyedit') || ($action == 'reallyduplicate')) {
                    really_modify(
                        $global_auth,
                        $disposition,
                        $action,
                        $id,
                        $name,
                        $dupname,
                        $authuser,
                        '',
                        $global,
                        $displayfields,
                        $displayfields_string,
                        $DateType,
                        $date_code,
                        $date_value,
                        $rowsize,
                        $refresh,
                        $now,
                        $db
                    );
                }

                if (($action == 'reallydelete') || ($action == 'cancel')) {
                    $name = get_name('AssetSearches', $id, $db);
                    really_delete($action, $id, $name, $authuser, $db);
                }

                if (($action == 'add') && (!$error)) {
                    really_add(
                        $disposition,
                        $name,
                        $authuser,
                        $global,
                        $displayfields_string,
                        $DateType,
                        $date_code,
                        $date_value,
                        $rowsize,
                        $refresh,
                        $now,
                        $db
                    );
                }

                # If user saves temp query from the results page, check for blank or non-unique name
                if ($action == 'save') {
                    # user came from results page

                    $name = get_name('AssetSearches', $id, $db);

                    if (!strlen($name) || $exists = check_existing_name_asset($name, $authuser, $id, $db)) {
                        confirm_save($id, $name, $db);
                    } else {
                        really_save($id, $name, $authuser, $now, $db);
                    }
                }

                if ($action == 'reallysave') {  # user came from confirm_save page
                    really_save($id, $name, $authuser, $db);
                }
            }

            ?>
        </td>
    </tr>
</table>
<?php
echo head_standard_html_footer($authuser, $db);
?>