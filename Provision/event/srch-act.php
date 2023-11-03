<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
15-Aug-02   EWB     Always log mysql failures
11-Sep-02   EWB     Merge with new asset code.
12-Sep-02   EWB     really_modify does not use $disabled, removed it.
12-Sep-02   EWB     Changed saved searches to query filters.
12-Sep-02   EWB     Asset search_add ignores searchstring.
12-Sep-02   EWB     Additional debug information.
19-Sep-02   EWB     Squashed another warning.
19-Sep-02   EWB     Giant refactoring.
20-Sep-02   EWB     Back to events only.
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navagation
13-Dec-02   EWB     Fixed short tags
 7-Jan-03   EWB     Does not require register_globals
 9-Jan-03   EWB     Handle names containing single quotes.
16-Jan-03   EWB     Access to $_SERVER variables.
 7-Feb-03   EWB     Moved to events world.
 7-Feb-03   EWB     Maintain created/edited values.
11-Feb-03   EWB     db_change()
19-Feb-03   EWB     Automatic Override.  When creating a local override 
                    for a global saved search also create local overrides 
                    for its client reports and notifications.
20-Feb-03   EWB     Port to 3.1
21-Feb-03   EWB     Everything uses same (NOW)
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header().
14-Apr-03   NL      Move debug_note line below $debug.
27-Jun-03   EWB     Don't let the user create an invalid search.
21-Jul-03   EWB     Fixed quoting issue.
21-Jul-03   EWB     Let them save an invalid search anyway.
10-Nov-05   BJS     Fixed saved search link.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
04-Jun-07   BTE     Added calls to PHP_REPF_UpdateDynamicList, other changes.
20-Jun-07   BTE     Bug 4152: Event sections: make sure all buttons work.
27-Jun-07   BTE     Fixed string quoting problem.
09-Sep-07   BTE     Fixed bug with here link.
04-Oct-07   BTE     Increased the size of the click here text.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-head2.php');
include('local.php');
include('../lib/l-srch.php');
include('../lib/l-user.php');
include('../lib/l-gsql.php');
include('../lib/l-upss.php');
include('../lib/l-upnt.php');
include('../lib/l-uprp.php');
include('../lib/l-cnst.php');


$name          = strval(get_argument('name', 0, ''));
$action        = strval(get_argument('action', 0, 'none'));
$dupname       = strval(get_argument('dupname', 0, ''));
$searchstring  = strval(get_argument('searchstring', 0, ''));

$id            = intval(get_argument('id', 0, 0));
$global        = intval(get_argument('global', 0, 0));
$disabled      = intval(get_argument('disabled', 0, 0));
$uniq = get_string('uniq', '');

$qf = 'Query Filter';
switch ($action) {
    case 'edit':
        $title = "Edit A $qf";
        break;
    case 'reallyedit':
        $title = "$qf Updated";
        break;
    case 'reallyduplicate':
        $title = "$qf Duplicated";
        break;
    case 'delete':
        $title = "Delete A $qf";
        break;
    case 'reallydelete':
        $title = "$qf Deleted";
        break;
    case 'duplicate':
        $title = "Duplicate $qf";
        break;
    case 'add':
        $title = "$qf Added";
        break;
    default:
        $title = "Saved A $qf";
        break;
}


function debug_array($debug, $p)
{
    if ($debug) {
        reset($p);
        foreach ($p as $key => $data) {
            $msg = "$key: $data";
            $msg = "<font color='green'>$msg</font><br>\n";
            echo $msg;
        }
    }
}


function go_back($db)
{
?>
    <p>
        <font face="verdana,helvetica" size="2">
            Return to the <a href="search.php">Search List</a>
            or Run a <a href="event.php">Query</a>.
        </font>
    </p>
<?php
}

function generic_error($db)
{
?>
    <font face="verdana,helvetica" size="2">

        There was a problem with your submission. The most likely
        cause of this error is either an attempt to create a search
        string with a name that already exists, or a submission
        beyond the maximum length (50 for name). Please try again.

    </font>
<?php
}



function name_exists($name)
{
?>
    <br>
    <font face="verdana,helvetica" size="2" color="red">
        Another Saved Search with the name
        <b><?php echo $name ?></b> already exists.
        Please chose another name.
        <br>
    </font>
<?php
}

function name_missing()
{
?>
    <br>
    <font face="verdana,helvetica" size="2" color="red">
        Neither the name nor the search string can be empty.
        Please try again.
        <br>
    </font>
<?php
}

function add_success($name, $searchstring)
{
?>
    <font face="verdana,helvetica" size="2">
        You have submitted a Saved Search
        called <b><?php echo $name ?></b>
        which contains the text: <br>
        <blockquote>
            <i>
                <?php
                echo $searchstring;
                ?>
            </i>
        </blockquote>
    </font>
<?php
}

function edit_success($name, $searchstring)
{
?>
    <font face="verdana,helvetica" size="2">
        You have edited a Saved Search called
        <b><?php echo $name ?></b>
        which contains the text:<br>
        <blockquote>
            <i>
                <?php
                echo $searchstring
                ?>
            </i>
        </blockquote>
        <br>
    </font>
<?php
}


function global_exists($name, $db)
{
    $qn   = safe_addslashes($name);
    $sql  = "select * from SavedSearches\n";
    $sql .= " where name = '$qn' and\n";
    $sql .= " global = 1";
    $row  = find_many($sql, $db);
    return ($row) ? true : false;
}


function delete_success($db)
{
?>
    <font face="verdana,helvetica" size="2">
        <p>
            Saved Search deleted.
        </p>
    </font>
<?php
}



function prompt_delete($id, $name, $searchstring)
{
    $self = server_var('PHP_SELF');
?>
    <br>
    <font face="verdana,helvetica" size="2">
        Do you really want to delete <b><?php echo $name ?></b>
        which contains the text below?<br>
        <blockquote><i><?php echo $searchstring ?></i></blockquote>
    </font>
    <br>
    <font face="verdana,helvetica" size="2">
        <?php
        $href = "$self?action=reallydelete&id=$id";
        echo "<a href='$href'>[Yes]</a>";
        ?>
        &nbsp;&nbsp;
        <a href="search.php">[No]</a>
    </font>
<?php
}



function delete_problem($name, $can_delete)
{
?>
    <br>
    <br>
    <font face="verdana,helvetica" size="2" color="red">
        <?php

        if ($can_delete == 'dependencies')
            $why = 'there are reports and notifications that rely on it';
        else
            $why = 'you did not create it';
        echo "<b>$name</b> cannot be deleted because $why.";
        ?>
    </font>
    <br>
    <br>
<?php
}


function confirm_delete($authuser, $id, $name, $searchstring, $db)
{
    # check if search can be deleted w/o disrupting notifications and reports
    $can_delete = check_can_delete_item('SavedSearches', $authuser, $id, $db);

    if ($can_delete == 'ok')
        prompt_delete($id, $name, $searchstring);
    else {
        delete_problem($name, $can_delete);
        go_back(0);
    }
}


function really_delete($id, $authuser, $db)
{
    $good = 0;
    $can_delete = check_can_delete_item('SavedSearches', $authuser, $id, $db);
    if ($can_delete == 'ok') {
        $sql = "delete from SavedSearches WHERE id = $id and username = '$authuser'";
        if (redcommand($sql, $db)) {
            if (mysqli_affected_rows($db)) {
                PHP_REPF_UpdateDynamicList(CUR, constJavaListEventFilters);
                $good = 1;
            }
        }
    }
    if ($good) {
        delete_success(0);
        go_back(0);
    }
}


/*
    |  A global saved search has to remain global when 
    |  there are notifications or reports that rely on it.
    */

function must_remain_global()
{
?>
    <font face="verdana,helvetica" size="2" color="red">
        This Saved Search must remain
        global because there are
        Notifications or Reports
        that rely on it.<br>
    </font>
<?php
}

/*
    |  A local saved search has to remain local when 
    |  a global saved search with the same name already
    |  exists.
    */

function must_remain_local()
{
?>
    <font face="verdana,helvetica" size="2" color="red">
        This Saved Search must remain
        local because a global saved
        search with that name already
        exists.<br>
    </font>
<?php
}


function bad_search($ss, $err)
{
    echo <<< HERE

        <font face="verdana,helvetica" size="2" color="red">
            This Saved Search contains a syntax error.  Please run an ad-hoc query using <br>
            this filter to get more detailed information about the syntax error.<br>
            <br><i>$ss</i><br>
            <br>$err<br>
        </font>    

HERE;
}



/*
    |  We don't actually care what the search result is ... we just 
    |  care if the searchstring is syntacticly correct or not.
    */

function search_valid($searchstring, &$err, $db)
{
    $umax = time();
    $umin = $umax - 7200;
    $err  = '(none)';
    $sql  = "select * from Events where\n";
    $sql .= " servertime between $umin and $umax\n";
    $sql .= " and ($searchstring)\n";
    $sql .= " limit 10";
    $res  = redcommand($sql, $db);
    if ($res) {
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    } else {
        $error = mysqli_error($db);
        $errno = mysqli_errno($db);
        $err   = "$errno:$error";
    }
    return ($res) ? true : false;
}



function search_add($name, $searchstring, $authuser, $global, $now, $db)
{
    $good   = 0;
    if (($name) && ($searchstring)) {
        $err    = '(none)';
        $exists = check_existing_name('SavedSearches', $name, $authuser, $db);
        $valid  = search_valid($searchstring, $err, $db);

        if ($exists)
            name_exists($name);
        else {
            if (!$valid) bad_search($searchstring, $err);
            debug_note("new search for $authuser");
            $row = array();
            $row['id'] = 0;
            $row['name'] = $name;
            $row['username'] = $authuser;
            $row['global'] = $global;
            $row['searchstring'] = $searchstring;
            $row['created'] = $now;
            $row['modified'] = $now;
            $res = update_search($row, $db);
            if ($res) {
                if (mysqli_affected_rows($db)) {
                    $good = 1;
                }
            }
            if ($good) {
                add_success($name, $searchstring);
                go_back($db);
            } else
                generic_error($db);
        }
    } else {
        name_missing();
    }
}


/*
    |  This happens when there is a previously existing global 
    |  saved search, and the user has just created a local saved 
    |  search with the same name.
    |  
    |  If this user owns any local notifications or reports 
    |  that use the global saved search, they must be updated 
    |  to use the local saved search instead.
    */

function override($gid, $lid, $name, $authuser, $now, $db)
{
    $qn  = safe_addslashes($name);
    $qu  = safe_addslashes($authuser);

    debug_note("override: gid:$gid lid:$lid now:$now, user:$authuser");

    $sql  = "update Notifications set\n";
    $sql .= " search_id = $lid,\n";
    $sql .= " modified = $now where\n";
    $sql .= " search_id = $gid and\n";
    $sql .= " global = 0 and\n";
    $sql .= " username = '$qu'";
    redcommand($sql, $db);

    /*
        |  Reports are more work because they
        |  can use multiple saved searches.
        |  We need to update the one in question
        |  without disturbing the others.
        */

    $sql  = "select * from Reports where\n";
    $sql .= " global = 0 and\n";
    $sql .= " username = '$qu' and\n";
    $sql .= " search_list like '%,$gid,%'";

    $list = find_many($sql, $db);
    if ($list) {
        reset($list);
        foreach ($list as $key => $data) {
            $id   = $data['id'];
            $sl   = $data['search_list'];
            $sl   = str_replace(",$gid,", ",$lid,", $sl);
            $sql  = "update Reports set\n";
            $sql .= " search_list = '$sl',\n";
            $sql .= " modified = '$now'\n";
            $sql .= " where id = $id";
            redcommand($sql, $db);
        }
    }

    // global notifications which use $gid
    $sql  = "select * from Notifications\n";
    $sql .= " where global = 1 and\n";
    $sql .= " search_id = $gid and\n";
    $sql .= " username != '$qu'";
    $global_notify = find_many($sql, $db);

    // global reports that use $gid
    $sql  = "select * from Reports\n";
    $sql .= " where global = 1 and\n";
    $sql .= " username != '$qu' and\n";
    $sql .= " search_list like '%,$gid,%'";
    $global_report = find_many($sql, $db);

    /*
        |  create new local overrides for existing
        |  global reports that use searchstring $gid
        |  but do not already have local overrides
        |  for $authuser.
        */

    reset($global_report);
    foreach ($global_report as $key => $data) {
        $rn   = safe_addslashes($data['name']);
        $sql  = "select * from Reports\n";
        $sql .= " where global = 0 and\n";
        $sql .= " username = '$qu' and\n";
        $sql .= " name = '$rn'";
        $row  = find_many($sql, $db);
        if (!$row) {
            $new = $data;
            $sl  = $new['search_list'];
            $sl  = str_replace(",$gid,", ",$lid,", $sl);
            $new['id'] = 0;
            $new['global'] = 0;
            $new['username'] = $authuser;
            $new['search_list'] = $sl;
            $new['last_run'] = 0;
            $new['created'] = $now;
            $new['modified'] = $now;
            update_report($new, $db);
        }
    }

    /*
        |  create new local overrides for existing
        |  global notifications that use searchstring $gid
        |  but do not already have local overrides
        |  for $authuser.
        */

    reset($global_notify);
    foreach ($global_notify as $key => $data) {
        $nn   = safe_addslashes($data['name']);
        $sql  = "select * from Notifications\n";
        $sql .= " where global = 0 and\n";
        $sql .= " username = '$qu' and\n";
        $sql .= " name = '$nn'";
        $row  = find_many($sql, $db);
        if (!$row) {
            $new = $data;
            $new['id'] = 0;
            $new['global'] = 0;
            $new['username'] = $authuser;
            $new['search_id'] = $lid;
            $new['last_run'] = $now;
            $new['created'] = $now;
            $new['modified'] = $now;
            update_notify($new, $db);
        }
    }
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
    $qn   = safe_addslashes($name);
    $qu   = safe_addslashes($authuser);
    $sql  = "select * from SavedSearches\n";
    $sql .= " where global = 1 and\n";
    $sql .= " username != '$qu' and\n";
    $sql .= " name = '$qn'";
    $row  = find_one($sql, $db);
    if ($row) {
        $gid = $row['id'];
    }
    if ($gid) {
        $sql  = "select * from SavedSearches\n";
        $sql .= " where global = 0 and\n";
        $sql .= " username = '$qu' and\n";
        $sql .= " name = '$qn'";
        $row  = find_one($sql, $db);
        if ($row) {
            $lid = $row['id'];
        }
    }
    if (($gid) && ($lid)) {
        override($gid, $lid, $name, $authuser, $now, $db);
    }
}


function really_modify($action, $id, $name, $dupname, $authuser, $searchstring, $global, $disabled, $now, $db)
{
    $row = find_record_id('SavedSearches', $id, $db);

    if (!$row) {
        $msg = "<p>Event query filter $id no longer exists.</p>";
        $msg = fontspeak($msg);
        echo $msg;
        return;
    }

    # Check if this is a case of a non-owner editing a global item
    $nonowner_edit  = check_nonowner_edit($authuser, 'SavedSearches', $id, $db);
    $can_make_local = 1;
    $exists = 0;

    if ($action == 'reallyedit') {

        # Get name of this SavedSearch before record is updated
        $orig_name = $row['name'];

        # If user is changing name, or non-owner is editing...
        if (($orig_name != $name) || ($nonowner_edit)) {
            # ...check whether another Search by this name exists.
            $exists = check_existing_name('SavedSearches', $name, $authuser, $db);
        }
    } else {
        $exists = check_existing_name('SavedSearches', $dupname, $authuser, $db);
    }

    if ($exists) {
        $name = ($action == 'reallyduplicate') ? $dupname : $name;
        name_exists($name);
        return;
    }

    $err = '(none)';
    $valid = search_valid($searchstring, $err, $db);
    if (!$valid) {
        bad_search($searchstring, $err);
    }

    $res = false;

    if ($action == 'reallyduplicate') {
        $setname = $dupname;
        $dup = $row;
        $dup['id'] = 0;
        $dup['name'] = $setname;
        $dup['global'] = $global;
        $dup['created'] = $now;
        $dup['modified'] = $now;
        $dup['username'] = $authuser;
        $dup['searchstring'] = $searchstring;
        $res = update_search($dup, $db);
    }

    if ($action == 'reallyedit') {
        # If non-owner is editing (global item), make local copy (insert)       
        $setname = $name;
        if ($nonowner_edit) {
            debug_note("create override for $authuser");
            $over = $row;
            $over['id'] = 0;
            $over['name'] = $setname;
            $over['global'] = 0;
            $over['created'] = $now;
            $over['modified'] = $now;
            $over['username'] = $authuser;
            $over['searchstring'] = $searchstring;
            $res = update_search($over, $db);
        } else {
            # if global checkbox was turned off and search can become local 
            # w/o disrupting Notifications and Reports, and the global checkbox 
            # was displayed, update global field

            $glob  = $row['global'];

            if (($glob) && (!$global)) {
                $can_make_local = check_can_make_search_local($authuser, $id, $db);
                if ($can_make_local) {
                    debug_note("global becomes local");
                    $glob = 0;
                } else {
                    must_remain_global();
                }
            }
            if ((!$glob) && ($global)) {
                if (global_exists($setname, $db)) {
                    must_remain_local();
                } else {
                    debug_note("local becomes global");
                    $glob = 1;
                }
            }

            // id, username, created:  unchanged.

            $row['name'] = $setname;
            $row['global'] = $glob;
            $row['modified'] = $now;
            $row['searchstring'] = $searchstring;
            $res = update_search($row, $db);
        }
    }

    if ($res) {
        edit_success($setname, $searchstring);
        override_name($setname, $authuser, $now, $db);
        go_back($db);
    } else {
        generic_error($db);
    }
}


function confirm_modify($action, $id, $name, $authuser, $searchstring, $global, $global_auth, $db)
{
    $self = server_var('PHP_SELF');
    if ($action == 'edit') {
        $msg  = "To edit your pre-defined search, ";
        $msg .= "modify the information below: ";
    } else {
        $msg  = "Make any desired changes ";
        $msg .= "below, then click the submit button:";
    }
?>
    <br>
    <font face="verdana,helvetica" size="2">
        <?php
        echo $msg;
        ?>
    </font>
    <form method=post action="<?php echo $self ?>">
        <input type=hidden name=action value=really<?php echo $action ?>>
        <input type=hidden name=id value=<?php echo $id ?>>
        <table border=0 padding=3>
            <tr>
                <td>
                    <font face="verdana,helvetica" size="2">
                        Name:
                    </font>
                </td>
                <td>
                    <font face="verdana,helvetica" size="2">
                        <?php

                        $dup    = ($action == 'duplicate') ? 1 : 0;
                        $field  = ($dup) ? 'dupname' : 'name';
                        $text   = ($dup) ? "Copy of $name" : $name;
                        $value  = str_replace("'", '&#039;', $text);

                        echo "<input type='text' size='40' name='$field'";
                        echo " value='$value' width='40'>\n";

                        ?>
                    </font>
                </td>
            </tr>
            <tr>
                <td>
                    <font face="verdana,helvetica" size="2">

                        Search String:

                    </font>
                </td>
                <td>
                    <font face="verdana,helvetica" size="2">
                        <textarea wrap="virtual" rows="4" cols="80" name="searchstring"><?php

                                                                                        echo $searchstring;

                                                                                        ?></textarea>
                    </font>
                </td>
            </tr>

            <?php
            $disabled = '';
            if ($global_auth) {
                $checked  = ($global) ? 'checked' : '';
                if ($action == 'edit') {
                    $can_make_local = check_can_make_search_local($authuser, $id, $db);
                    if (!$can_make_local and $checked) {
                        $disabled = 'disabled';
                    }
                }

            ?>
                <!-- Since checkbox values aren't passed if unchecked or de-checked, 
                 provide a hidden variable as a default  -->
                <input type=hidden name=global value=0>
                <?php
                if ($disabled) {
                ?>
                    <input type="hidden" name="disabled" value="1">
                <?php
                }
                ?>
                <tr>
                    <td>
                        <font face="verdana,helvetica" size="2">
                            Global:
                        </font>
                    </td>
                    <td>
                        <input type="checkbox" name="global" <?php echo "$checked $disabled" ?> value="1">
                        <?php
                        if ($disabled) {
                        ?>
                            <font color="gray">

                                (This Saved Search must remain global because
                                there are Notifications and Reports that rely on it.)

                            </font>
                        <?php
                        }
                        ?>
                    </td>
                </tr>
            <?php
            }
            ?>

            <tr>
                <td>
                    <br>
                </td>
                <td>
                    <font face="verdana,helvetica" size="2">
                        <input type=submit value="Submit">
                        &nbsp;&nbsp;&nbsp;
                        <input type="reset" value="reset">
                        <br><br>
                    </font>
                </td>
            </tr>
            <tr>
                <td valign=top>
                    <font face="verdana,helvetica" size="2">
                        Tip:
                    </font>
                </td>
                <td>
                    <pre>
                <?php

                include('srch-doc.txt') ?> 

Here are <a href="../doc/searches.doc">some examples</a> of saved searches (.doc)

                </pre>
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

$now    = time();
$user   = user_data($authuser, $db);

$debug  = @($user['priv_debug']) ?  1 : 0;
$priv   = @($user['priv_search']) ? 1 : 0;
$global_auth = $priv;

debug_array($debug, $_POST);
debug_array($debug, $_GET);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer) 

$isparent = get_integer('isparent', 0);

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

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users   

db_change($GLOBALS['PREFIX'] . 'event', $db);

if (($id == 0) && ($uniq != '')) {
    $sql = "SELECT id FROM SavedSearches WHERE searchuniq='$uniq'";
    $row = find_one($sql, $db);
    if ($row) {
        $id = $row['id'];
    }
}

if (($id == 0) && ($action != 'add')) {
?>
    <font face="verdana,helvetica" size="2">
        Error -- no id specified.
    </font>
<?php
}

if ($action == 'none') {
?>
    <font face="verdana,helvetica" size="2">
        Error -- no action specified.
    </font>
<?php
}

/*
    |   Get existing values from the database (not from the form)
    */

$good = 0;
if ($action == 'edit' || $action == 'duplicate' || $action == 'delete') {
    $row = find_record_id('SavedSearches', $id, $db);
    if ($row) {
        $id     = $row['id'];
        $name   = $row['name'];
        $owner  = $row['username'];
        $global = $row['global'];
        $searchstring = $row['searchstring'];
        $good = 1;
    }
}

if ($action == 'edit' || $action == 'duplicate') {
    if ($good) {
        confirm_modify($action, $id, $name, $authuser, $searchstring, $global, $global_auth, $db);
    }
}

if ($action == 'delete') {
    if ($good) {
        confirm_delete($authuser, $id, $name, $searchstring, $db);
    }
}

if ($action == 'reallyedit' || $action == 'reallyduplicate') {
    if (!$priv) $global = 0;
    really_modify($action, $id, $name, $dupname, $authuser, $searchstring, $global, $disabled, $now, $db);
}

if ($action == 'reallydelete') {
    really_delete($id, $authuser, $db);
}

if ($action == 'add') {
    search_add($name, $searchstring, $authuser, $global, $now, $db);
}

if ($isparent) {
    $qname = str_replace('\'', '\\\'', $name);
    $qqname = safe_addslashes($name);
    $quser = safe_addslashes($authuser);
    $sql = "SELECT searchuniq FROM SavedSearches WHERE name='$qqname'"
        . " AND global=$global AND username='$quser'";
    $row = find_one($sql, $db);
    if ($row) {
        echo '<script type="text/javascript"> var qname=\'' . $qname
            . '\';</script>';
        echo '<p>' . constFontSizeClickHere
            . 'Click <a href="#" onclick=" addDynamicItemButton('
            . constJavaListEventFilters . ',\'' . $row['searchuniq']
            . '\',qname);window.close();">here</a>'
            . ' to add this new filter to the event section you are '
            . 'defining.</font>';
    }
}

echo head_standard_html_footer($authuser, $db);
?>