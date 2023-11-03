<?php



/*

Revision history:



Date        Who     What

----        ---     ----

15-Aug-02   EWB     Added priv_debug

15-Aug-02   EWB     Always log mysql failures

19-Sep-02   EWB     Giant refactoring.

29-Oct-02   EWB     Clear AssetSearches, AssetReports when removing user.

29-Oct-02   EWB     Clear AssetData and Machine tables when removing site.

29-Oct-02   EWB     Clear AssetSearchCriteria when removing user.

 5-Nov-02   EWB     Added priv_aquery, priv_areport.

13-Nov-02   EWB     Log mysql errors.

19-Nov-02   EWB     Gloabl User command

25-Nov-02   EWB     Configure Machine Priv

 4-Dec-02   EWB     Configure priv available at creation time, fixed priv name.

 5-Dec-02   EWB     priv_updates, priv_downloads

 6-Dec-02   EWB     Don't change priv_debug, removed global user.

19-Dec-02   EWB     Allow rename of '/main/'

14-Jan-03   EWB     Minimal quotes.

14-Jan-03   EWB     Don't require register_globals

 7-Feb-03   EWB     Use 3.1 database.

13-Feb-03   EWB     Remove from tables only if switching worked.

 6-Mar-03   EWB     Initialize email in update_user()

 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()

 8-Mar-03   NL      Added Change Password functionality, incl. check_pwd_change()

10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.

10-Mar-03   NL      Passed 0 as $legend to standard_html_header()

10-Mar-03   NL      Modified delete_cust() to use 3.1 databases.

17-Mar-03   NL      Allow change password only if $admin or 'user_password' server option is on.

17-Mar-03   NL      Pass $admin to update_user().

19-Mar-03   NL      Move $debug initialization and surrounding lines above ob_ lines

28-Mar-03   EWB     Fixed a minor syntax error.

 8-Apr-03   EWB     Further quoting issues.

 9-Apr-03   EWB     Scroll box for create user

18-Apr-03   EWB     Allow uppercase letters in usernames.

27-Apr-03   NL      Delete aux SiteFilter records when deleting Reports, Notns, AssetReports

29-Apr-03   EWB     added priv_asset

19-May-03   EWB     Web interface to toggle debug priv

23-May-03   EWB     Quote Crusade.

17-Jul-03   EWB     Filtersites always 0 on creation.

23-Jul-03   EWB     Create/Modify Restricted User.

31-Jul-03   EWB     Enable creation of Restricted User.

 8-Aug-03   EWB     Pulldown for account type (normal,restricted,admin)

10-Sep-03   EWB     Set filtersites to 0 when editing an account.

19-Sep-03   EWB     Delete site command does the complete job.

 3-Nov-03   EWB     delete site clears site filter records.

 4-Nov-03   EWB     delete site clears swupdate.Downloads

 6-Nov-03   EWB     delete site clears event.Console

 3-Dec-03   EWB     New site is owned by the creator.

 3-Dec-03   EWB     Site ownership commands.

 4-Dec-03   EWB     Handle case of orphaned site.

18-Feb-03   EWB     reinstate compare_passwords.

18-Feb-03   EWB     spelling counts ... it's "privileges"

18-Feb-03   EWB     delete site includes provision records.

19-Feb-03   EWB     delete site includes cryptkey records.

 8-Mar-04   EWB     created priv_provis

14-Jul-04   EWB     purge from census clears patches and machine groups.

11-Nov-04   EWB     changing site access sets census_dirty.

20-Jun-05   EWB     fixed problem with deleting inactive sites.

27-Sep-05   BJS     set_site_owner() & assign_site handle() the

                    core.Customers.notify_sender field.

                    Added find_site_email().

                    Added l-form.php.

30-Sep-05   BJS     Changed 'assign' to 'edit'. Action is first column.

12-Oct-05   BTE     Changed reference from gconfig to core in delete_cust.

13-Oct-05   BJS     Added option to set site email when adding a new site.

08-Nov-05   BJS     Removed Event & Notification references to RptSiteFilters &

                    NotSiteFilters.

09-Jan-06   BTE     Added auditing and CSRV rights.

24-Feb-06   BTE     Removed census dirty bits.

15-Mar-06   BTE     Bug 3186: Event logging appears to be completely broken on

                    4.3 server.

15-Mar-06   BTE     Fixed an undefined constant.

06-May-06   BTE     Bug 3209: 4.2 to 4.3 server upgrade does not work

                    correctly.

21-Jun-06   BTE     Bug 3466: The primary census server page is no longer in

                    order.

15-Sep-06   WOH     Made changes for bugzilla #3657

12-Oct-06   WOH     Updated text on edit/new user page.

12-Oct-06   WOH     Updated text on edit/new user page - again.

18-Oct-06   WOH     Updated label of logo field.

13-Nov-06   JRN     Added PHP_DSYN_InvalidateRow to synchronize with clients.

04-Jun-07   BTE     Added calls to PHP_REPF_UpdateDynamicList.



*/



ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)

include('../lib/l-util.php');

include('../lib/l-db.php');

include('../lib/l-sql.php');

include('../lib/l-serv.php');

include('../lib/l-head.php');

include('../lib/l-user.php');

include('../lib/l-rcmd.php');

include('../lib/l-rlib.php');

include('../lib/l-alib.php');

include('../lib/l-gsql.php');

include('../lib/l-slct.php');

include('../lib/l-aprg.php');

include('../lib/l-gcfg.php');

include('../lib/l-cprg.php');

include('../lib/l-form.php');

include('../lib/l-gdrt.php');

include('../lib/l-grps.php');

include('../lib/l-dsyn.php');

include('../lib/l-cnst.php');

include('../lib/l-core.php');

include('../lib/l-errs.php');



/*  These constants are used to describe 5 fields on the new/edit user. */

define('constLogoMsg', 'Enter name of the logo file you uploaded to the

                            \main\pub\patches directory on this ASI server

                            for this user.');

define('constWidthMsg', 'Enter the width of the logo for this user

                            in pixels');

define('constHeightMsg', 'Enter the height of the logo for this user

                             in pixels');

define('constFooterLeftMsg', 'Enter left ASI server page footer for this

                                  user <br> Because this is HTML, each line in

                                  the footer should end with "&lt;br&gt;"

                                  (without quotes).');

define('constFooterRightMsg', 'Enter right ASI server page footer for this

                                  user <br> Because this is HTML, each line in

                                  the footer should end with "&lt;br&gt;"

                                  (without quotes).');



$action = get_string('action', 'none');



switch ($action) {

    case 'auu':
        $title = 'Admin Updating User';
        break;

    case 'cc':
        $title = 'Creating Site';
        break;

    case 'cu':
        $title = 'Creating User';
        break;

    case 'dc':
        $title = 'Deleting Site';
        break;

    case 'du':
        $title = 'Deleting User';
        break;

    case 'ec':
        $title = 'Edit Site';
        break;

    case 'eu':
        $title = 'Edit User';
        break;

    case 'gu':
        $title = 'Global User';
        break;

    case 'uu':
        $title = 'Updating User';
        break;

    case 'uc':
        $title = 'Update Site';
        break;

    case 'ccc':
        $title = 'Create Site';
        break;

    case 'ccu':
        $title = 'Create User';
        break;

    case 'cdc':
        $title = 'Delete Site';
        break;

    case 'cdu':
        $title = 'Delete User';
        break;

    case 'dbg':
        $title = 'Toggle Debug Priv';
        break;

    case 'sso':
        $title = 'Set Site Owner';
        break;

    case 'as':
        $title = 'Assign Site';
        break;

    default:
        $title = 'Admin Action Unknown';
        break;
}





function backtoadmin()

{

    $back = html_link('admin.php', 'back');

    $text = "<br>\nGo $back to admin page.<br>\n";

    return $text;
}







function special_header($msg, $span)

{

    return <<< HERE



<tr>

  <th colspan="$span" bgcolor="#333399">

    <font color="white">

       $msg

    </font>

  </th>

</tr>



HERE;
}



function message($s)

{

    $msg = fontspeak($s);

    echo "$msg<br>\n";
}





function span_data($n, $msg)

{

    return <<< HERE

<tr>

   <td colspan="$n">

       $msg

   </td>

</tr>



HERE;
}



function table_header()

{

    echo "\n\n\n";

    echo '<table border="2" align="left" cellspacing="2" cellpadding="2">';

    echo "\n";
}



function table_footer()

{

    echo "\n</table>\n\n\n";

    echo "<br clear=\"all\">\n\n";
}



/*

    |  Figure out what has been added.  In other words, calculate the

    |  list of items which are in the new list, but not in the current list.

    */



function calc_added($current, $new)

{

    $temp = array();

    $rezz = array();

    reset($new);

    foreach ($new as $key => $data) {

        $temp[$data] = 1;
    }

    reset($current);

    foreach ($current as $key => $data) {

        $temp[$data] = 0;
    }

    reset($new);

    foreach ($new as $key => $data) {

        if ($temp[$data])

            $rezz[] = $data;
    }

    return $rezz;
}





function show_list($prompt, $name, $list)

{

    echo "<br><br>$prompt<br>";

    reset($list);

    foreach ($list as $key => $data) {

        echo "$name [ $key ] => $data<br>";
    }
}







/*

    |  Figure out what has been removed.  In other words, calculate the

    |  list of items which are in the current list, but not in the new list.

    */



function calc_killed($current, $new)

{

    $temp = array();

    $rezz = array();



    //    show_list('calc killed 1 ','current',$current);

    //    show_list('calc killed 2 ','new',$new);



    reset($current);

    foreach ($current as $key => $data) {

        $temp[$data] = 1;
    }

    //    show_list('calc killed 3 ','temp',$temp);



    reset($new);

    foreach ($new as $key => $data) {

        $temp[$data] = 0;
    }

    //    show_list('calc killed 4 ','temp',$temp);

    reset($temp);

    foreach ($temp as $key => $data) {

        if ($temp[$key])

            $rezz[] = $key;
    }

    //    show_list('calc killed 5 ','rezz',$rezz);

    return $rezz;
}



function select_scroll($name, $size, $mult, $options, $selected)

{

    $keys = array();

    reset($selected);

    foreach ($selected as $key => $data) {

        $keys[$data] = 1;
    }

    $mult = ($mult) ? ' multiple' : '';

    $msg = "<select$mult name=\"$name\" size=\"$size\">\n";

    reset($options);

    foreach ($options as $key => $data) {

        if (isset($keys[$data]))

            $msg .= "<option selected value=\"$key\">$data</option>\n";

        else

            $msg .= "<option value=\"$key\">$data</option>\n";
    }

    $msg .= "</select>\n";

    return $msg;
}







function select_with_values($name, $options, $selected)

{

    $m = "<select name=\"$name\" size=\"1\">\n";

    reset($options);

    foreach ($options as $key => $data) {

        if ($selected == $key)

            $m .= "<option selected value=\"$key\">$data</option>\n";

        else

            $m .= "<option value=\"$key\">$data</option>\n";
    }

    $m .= "</select>\n";

    return $m;
}



function table_data($args, $head)

{

    $td = ($head) ? 'th' : 'td';

    if (safe_count($args)) {

        echo "<tr>\n";

        reset($args);

        foreach ($args as $key => $data) {

            echo "<$td>$data</$td>\n";
        }

        echo "</tr>\n";
    }
}



/* compare_passwords

        Compare the password stored the database with a user typed password.

        Must hash the typed password.

        $db is the database connection to use for login information.

        $username is the username associated with the typed password.

        $typed_password is the user-submitted password to check against the database.

    */



function compare_passwords($db, $username, $typed_pwd)

{

    $stored_pwd = '';



    /* get existing hashed password */

    $sql = "SELECT password FROM Users WHERE username = '$username'";

    $res = command($sql, $db);

    if ($res) {

        if (mysqli_num_rows($res) == 1) {

            $row = mysqli_fetch_assoc($res);

            $stored_pwd = $row['password'];
        }
    }



    /* hash the typed password */

    $digest = md5($typed_pwd);



    /* compare the typed password to stored password */

    return ($digest == $stored_pwd) ? 1 : 0;
}



/*

        check_pwd_change

        If $req_old_pwd is 1, checks that old password entered by user is correct.

        Then checks that new password and confirm password exist and match.

        Returns $response of "success" or an error message to display to user.

        ARGS:

        $username:    user of password to change.

        $req_old_pwd: boolean whether to check that $old_pwd matches database password

        $old_pwd:     old password. If $req_old_pwd==0, just use empty string.

        $new_pwd:     new password.

        $confirm_pwd: re-typed new password.

    */

function check_pwd_change($db, $username, $req_old_pwd, $old_pwd, $new_pwd, $confirm_pwd)

{

    $response = '';



    if ($req_old_pwd) {

        // check old password entered and correct

        if (!strlen($old_pwd)) {

            $response = "You must enter the old password for user <b>$username</b>.";

            return $response;
        } else {

            if (!compare_passwords($db, $username, $old_pwd)) {

                $response = "You have entered an incorrect password for user " .

                    "<b>$username</b>.  Please try again.";

                return $response;
            }
        }
    }



    if (!(strlen($new_pwd)) || !(strlen($confirm_pwd))) {

        if (!strlen($new_pwd))

            $response .= "You must enter a new password for user " .

                "<b>$username</b>.<br>";

        if (!strlen($confirm_pwd))

            $response .= "You must confirm the new password for " .

                "user <b>$username</b>.<br>";
    } else {

        if ($new_pwd != $confirm_pwd)

            $response = "The <b>New Password</b> and <b>Confirm New Password</b> " .

                "entries do not match. Please try again.<br>";

        else {

            // Good to go

            $response = "success";
        }
    }



    return $response;
}





function update_user($admin, $username, $db)

{

    $old_pwd     = get_string('old_pwd', '');

    $report_mail = get_string('report_mail', '');

    $notify_mail = get_string('notify_mail', '');



    $rm = safe_addslashes($report_mail);

    $nm = safe_addslashes($notify_mail);



    $sql_pwd  = '';

    $msg      = '';



    // check for password change

    if ($admin || intval(server_opt('user_password', $db))) {

        $new_pwd        = trim(get_argument('new_pwd', 0, ''));

        $confirm_pwd    = trim(get_argument('confirm_pwd', 0, ''));



        if (strlen($old_pwd) || strlen($new_pwd) || strlen($confirm_pwd)) {

            $response = check_pwd_change($db, $username, 1, $old_pwd, $new_pwd, $confirm_pwd);

            if ($response == "success")

                $sql_pwd = " password='" . md5($new_pwd) . "',\n";

            elseif (strlen(trim($response)))

                $msg = $response;

            else

                $msg = "There was a problem with this update. Please try again.";
        }
    }



    if (!($msg)) {

        // update Users table

        $sql  = "update Users set";

        if (strlen(trim($sql_pwd))) $sql .= $sql_pwd;

        $sql .= " report_mail='$rm',\n";

        $sql .= " notify_mail='$nm'\n";

        $sql .= " where username = '$username'";

        $res  = redcommand($sql, $db);

        if (affected($res, $db) == 1) {

            $msg = "User <b>$username</b> updated.<br>";

            if ($sql_pwd) {

                $msg .= "<br>You will be required to login with your new password.<br>";

                $txt  = "admin: $username changed password.";

                logs::log(__FILE__, __LINE__, $txt, 0);
            }
        }
    }



    $msg .= backtoadmin();



    message($msg);
}



function find_user($id, $db)

{

    $user = array();

    if ($id > 0) {

        $sql  = "select * from Users where userid = $id";

        $user = find_one($sql, $db);
    }

    return $user;
}



function find_cust($id, $db)

{

    $cust = array();

    if ($id > 0) {

        $sql  = "select * from Customers where id = $id";

        $cust = find_one($sql, $db);
    }

    return $cust;
}



function find_cust_list($list, $db)

{

    $cst = array();

    if (safe_count($list)) {

        $idl = implode(',', $list);

        $sql = "select * from Customers where id in ($idl)";

        $res = command($sql, $db);

        if ($res) {

            if (mysqli_num_rows($res)) {

                while ($row = mysqli_fetch_assoc($res)) {

                    $cst[] = $row['customer'];
                }
            }

            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    }

    return $cst;
}



function find_all_customers($db)

{

    $cust = array();

    $sql  = "select * from Customers order by CONVERT(customer USING "

        . "latin1), id";

    $res  = command($sql, $db);

    if ($res) {

        $prev = '';

        if (mysqli_num_rows($res)) {

            while ($row = mysqli_fetch_assoc($res)) {

                $id     = $row['id'];

                $name   = $row['customer'];

                if ($name != $prev) {

                    $cust[$id] = $name;

                    $prev = $name;
                }
            }
        }

        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    return $cust;
}





function find_site_users($site, $db)

{

    $user = array();

    $qs   = safe_addslashes($site);

    $sql  = "select * from Customers\n";

    $sql .= " where username != '' and\n";

    $sql .= " customer = '$qs'\n";

    $sql .= " order by CONVERT(username USING latin1)";

    $res  = command($sql, $db);

    if ($res) {

        if (mysqli_num_rows($res)) {

            $user[0]  = '      ';

            while ($row = mysqli_fetch_assoc($res)) {

                $id   = $row['id'];

                $name = $row['username'];

                $user[$id] = $name;
            }
        }

        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    return $user;
}





function cust_exists($customer, $db)

{

    $exists = 1;

    $qcust  = safe_addslashes($customer);

    $sql = "select * from Customers where customer = '$qcust'";

    $res = command($sql, $db);

    if ($res) {

        if (mysqli_num_rows($res) == 0) {

            $exists = 0;
        }

        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    return $exists;
}







function find_user_cust($db, $username)

{

    $cust = array();

    $sql  = "SELECT * FROM Customers where username = '$username'";

    $res  = command($sql, $db);

    if ($res) {

        if (mysqli_num_rows($res) > 0) {

            while ($row = mysqli_fetch_assoc($res)) {

                $id   = $row['id'];

                $name = $row['customer'];

                $cust[$id] = $name;
            }
        }

        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    return $cust;
}



function global_user($id, $admin, $authuser, $db)

{

    $user = array();

    if ($admin) {

        $user = find_user($id, $db);
    }

    if ($user) {

        $name = $user['username'];

        $sql  = "update Users set";

        $sql .= " priv_notify=1,\n";

        $sql .= " priv_search=1,\n";

        $sql .= " priv_report=1,\n";

        $sql .= " priv_areport=1,\n";

        $sql .= " priv_aquery=1\n";

        $sql .= " where username = '$name'";

        redcommand($sql, $db);

        if (mysqli_select_db($db, event)) {

            $sql = "update Reports set username='$name' where global = 1";

            redcommand($sql, $db);

            $sql = "update SavedSearches set username='$name' where global = 1";

            redcommand($sql, $db);

            PHP_REPF_UpdateDynamicList(CUR, constJavaListEventFilters);

            $sql = "update Notifications set username='$name' where global = 1";

            redcommand($sql, $db);
        }

        if (mysqli_select_db($db, asset)) {

            $sql = "update AssetSearches set username='$name' where global = 1";

            redcommand($sql, $db);

            $sql = "update AssetReports set username='$name' where global = 1";

            redcommand($sql, $db);
        }

        mysqli_select_db($db, core);

        logs::log(__FILE__, __LINE__, "global user $name created by $authuser", 0);
    }
}





/*

    |   0: Normal User

    |   1: Restricted User

    |   2: Admin User

    */



function priv_encode($user)

{

    $code = 0;

    if ($user) {

        if ($user['priv_restrict']) $code = 1;

        if ($user['priv_admin'])    $code = 2;
    }

    return $code;
}



function priv_decode($code, &$admin, &$rest)

{

    $admin = ($code == 2) ? 1 : 0;

    $rest  = ($code == 1) ? 1 : 0;
}





function edit_user($id, $admin, $db)

{

    $user = array();

    if ($admin) {

        $user = find_user($id, $db);
    }



    if ($user) {

        $self = server_var('PHP_SELF');

        echo <<< HERE



<form method="post" action="$self">

<input type="hidden" name="action" value="auu">

<input type="hidden" name="id" value="$id">



HERE;

        table_header();

        echo special_header("Edit User", 3);



        $name   = $user['username'];

        $notify = $user['notify_mail'];

        $report = $user['report_mail'];



        $doc    = "<i>user names can not be changed</i>";

        $args = array("Name: ", "<b>$name</b>", $doc);

        table_data($args, 0);



        $p0      = '<input type="password" name="new_pwd" size="15">';

        $p1      = '<input type="password" name="confirm_pwd" size="15">';

        $doc     = "<i>enter the same password twice</i>";

        $args[0] = 'Password: ';

        $args[1] = "New:&nbsp;$p0\nConfirm:&nbsp;$p1";

        $args[2] = $doc;

        table_data($args, 0);



        $doc     = "<i>enter comma-separated list of email addesses</i>";

        $args[0] = "Notify mail: ";

        $args[1] = "<input type=\"text\" name=\"notify_mail\" value=\"$notify\" size=\"70\">";

        $args[2] = $doc;

        table_data($args, 0);



        $args[0] = "Report mail: ";

        $args[1] = "<input type=\"text\" name=\"report_mail\" value=\"$report\" size=\"70\">";

        $args[2] = $doc;

        table_data($args, 0);



        $yesno  = array('No', 'Yes');

        $privs  = array('Normal', 'Restricted', 'Admin');

        $level  = priv_encode($user);

        $pa = select_with_values('priv_level',    $privs, $level);

        $pn = select_with_values('priv_notify',   $yesno, $user['priv_notify']);

        $ps = select_with_values('priv_search',   $yesno, $user['priv_search']);

        $pq = select_with_values('priv_aquery',   $yesno, $user['priv_aquery']);

        $pr = select_with_values('priv_report',   $yesno, $user['priv_report']);

        $px = select_with_values('priv_areport',  $yesno, $user['priv_areport']);

        $pc = select_with_values('priv_config',   $yesno, $user['priv_config']);

        $pp = select_with_values('priv_provis',   $yesno, $user['priv_provis']);

        $pt = select_with_values('priv_asset',    $yesno, $user['priv_asset']);

        $pl = select_with_values('priv_downloads', $yesno, $user['priv_downloads']);

        $pu = select_with_values('priv_updates',  $yesno, $user['priv_updates']);

        $pd = select_with_values('priv_debug',    $yesno, $user['priv_debug']);

        $privAudit = select_with_values(
            'priv_audit',
            $yesno,

            $user['priv_audit']
        );

        $privCSRV = select_with_values(
            'priv_csrv',
            $yesno,

            $user['priv_csrv']
        );



        $da = '<i>controls account type</i>';

        $dn = '<i>can create and edit global notifications</i>';

        $dr = '<i>can create and edit global event reports</i>';

        $ds = '<i>can create and edit global searches</i>';

        $dq = '<i>can create and edit global queries</i>';

        $dx = '<i>can create and edit global asset reports</i>';

        $dc = '<i>can configure client machines</i>';

        $dp = '<i>can provision client machines</i>';

        $dt = '<i>can remove machine assets</i>';

        $dl = '<i>can control downloads</i>';

        $du = '<i>can control updates</i>';

        $dd = '<i>can access debug pages</i>';

        $diagAudit = '<i>can view and control audits</i>';

        $diagCSRV = '<i>can access CSRV<i>';



        table_data(array('Privileges: ',          $pa, $da), 0);

        table_data(array('Global Notify: ',       $pn, $dn), 0);

        table_data(array('Global Event Report: ', $pr, $dr), 0);

        table_data(array('Global Event Search: ', $ps, $ds), 0);

        table_data(array('Global Asset Query: ',  $pq, $dq), 0);

        table_data(array('Global Asset Report: ', $px, $dx), 0);

        table_data(array('Configure Machines: ',  $pc, $dc), 0);

        table_data(array('Configure Provision: ', $pp, $dp), 0);

        table_data(array('Remove Assets: ',       $pt, $dt), 0);

        table_data(array('Control Downloads: ',   $pl, $dl), 0);

        table_data(array('Control Updates: ',     $pu, $du), 0);

        //      table_data(array('Debug: ',               $pd,$dd),0);

        table_data(array('Audit Rights: ', $privAudit, $diagAudit), 0);

        table_data(array('CSRV Access: ', $privCSRV, $diagCSRV), 0);



        $doc      = '<i>specify sites this user is allowed to access</i>';

        $allcust  = find_all_customers($db);

        $selected = find_user_cust($db, $name);

        $size = safe_count($allcust);

        if ($size > 0) {

            $size = ($size > 12) ? 12 : $size;

            $args[0] = 'Sites:';

            $args[1] = select_scroll('cust[]', $size, 1, $allcust, $selected);

            $args[2] = $doc;

            table_data($args, 0);
        }



        /* Get the log and footer information */

        $logo   = $user['logo_file'];

        $xxx    = $user['logo_x'];

        $yyy    = $user['logo_y'];

        $lfoot  = $user['footer_left'];

        $rfoot  = $user['footer_right'];



        /* File name for logo  */

        $msg = constLogoMsg;

        $doc  = "<i> $msg </i>";

        $args[0] = 'Logo file: ';

        $args[1] = "<input type=\"text\" name=\"logo_file\" value=\"$logo\" size=\"50\">";

        $args[2] = $doc;

        table_data($args, 0);



        /* X for logo */

        $msg = constWidthMsg;

        $doc  = "<i> $msg </i>";

        $args[0] = 'Width (Pixels): ';

        $args[1] = "<input type=\"text\" name=\"logo_x\" value=\"$xxx\" size=\"50\">";

        $args[2] = $doc;

        table_data($args, 0);



        /* Y for logo */

        $msg = constHeightMsg;

        $doc  = "<i> $msg </i>";

        $args[0] = 'Height (Pixels): ';

        $args[1] = "<input type=\"text\" name=\"logo_y\" value=\"$yyy\" size=\"50\">";

        $args[2] = $doc;

        table_data($args, 0);



        /* Footer - Left side of page */

        $msg = constFooterLeftMsg;

        $doc  = "<i> $msg </i>";

        $args[0] = 'Footer left: ';

        $args[1] = "<textarea wrap=\"virtual\" name=\"footer_left\" rows=\"5\" cols=\"50\">$lfoot</textarea>";

        $args[2] = $doc;

        table_data($args, 0);



        /* Footer - Right side of page */

        $msg = constFooterRightMsg;

        $doc  = "<i> $msg </i>";

        $args[0] = 'Footer right: ';

        $args[1] = "<textarea wrap=\"virtual\" name=\"footer_right\" rows=\"5\" cols=\"50\">$rfoot</textarea>";

        $args[2] = $doc;

        table_data($args, 0);



        $submit = "<input type=\"submit\" name=\"submit\" value=\"update\">";

        $reset  = "<input type=\"reset\" value=\"reset\">";

        $action = "$submit&nbsp;&nbsp;&nbsp;$reset";

        echo span_data(3, $action);

        table_footer();

        echo "</form>\n\n\n";
    }
}



function update_user_cust($username, $custlist, $db)

{

    $msg = '';

    $added  = array();

    $killed = array();

    if ($username) {

        $quser = safe_addslashes($username);

        $msg   = "User <b>$username</b> updated.<br>";

        $cust  = find_cust_list($custlist, $db);

        if ($cust) {

            $current = find_user_cust($db, $username);

            $added   = calc_added($current, $cust);

            $killed  = calc_killed($current, $cust);
        } else {

            $killed = find_user_cust($db, $username);
        }
    }

    if ($added) {

        reset($added);

        foreach ($added as $key => $site) {

            $qsite = safe_addslashes($site);

            $sql  = "insert into\n Customers set\n";

            $sql .= " username='$quser',\n";

            $sql .= " customer='$qsite'";

            $res  = redcommand($sql, $db);

            if ($res) {

                $log  = "user $username has access to $site.";

                $msg .= "User <b>$username</b> has access to <b>$site</b>.<br>\n";

                logs::log(__FILE__, __LINE__, $log, 0);
            }
        }
    }

    if ($killed) {

        reset($killed);

        foreach ($killed as $key => $site) {

            $qsite = safe_addslashes($site);

            $sql  = "delete from Customers where\n";

            $sql .= " username = '$quser' and\n";

            $sql .= " customer = '$qsite'";

            $res  = redcommand($sql, $db);

            if ($res) {

                $log  = "user $username has no access to $site.";

                $msg .= "User <b>$username</b> has no access to <b>$site</b>.<br>\n";

                logs::log(__FILE__, __LINE__, $log, 0);
            }
        }
    }

    if ($msg) {

        groups_init($db, constGroupsInitFull);
    }

    return $msg;
}





/*

    |  Always set filtersites to 0 when modifying

    |  an account, since we may also be adding

    |  or removing sites.

   */



function admin_update_user($admin, $authuser, $db)

{

    $sql_pwd  = '';

    $sql_notn = '';

    $sql_rprt = '';

    $msg      = '';



    $user = array();

    $id   = get_integer('id', 0);

    if (($admin) && ($id)) {

        $user = find_user($id, $db);
    }

    if ($user) {

        $username       = $user['username'];

        $new_pwd        = trim(get_argument('new_pwd', 0, ''));

        $confirm_pwd    = trim(get_argument('confirm_pwd', 0, ''));

        $report_mail    = trim(get_argument('report_mail', 1, ''));

        $notify_mail    = trim(get_argument('notify_mail', 1, ''));

        $priv_restrict  = 0;  // encoded in priv_level

        $priv_admin     = 0;  // encoded in priv_level

        $priv_level     = get_integer('priv_level', 0);

        $priv_notify    = get_integer('priv_notify', 0) ?    1 : 0;

        $priv_search    = get_integer('priv_search', 0) ?    1 : 0;

        $priv_report    = get_integer('priv_report', 0) ?    1 : 0;

        $priv_aquery    = get_integer('priv_aquery', 0) ?    1 : 0;

        $priv_areport   = get_integer('priv_areport', 0) ?   1 : 0;

        $priv_config    = get_integer('priv_config', 0) ?    1 : 0;

        $priv_provis    = get_integer('priv_provis', 0) ?    1 : 0;

        $priv_asset     = get_integer('priv_asset', 0) ?     1 : 0;

        $priv_downloads = get_integer('priv_downloads', 0) ? 1 : 0;

        $priv_updates   = get_integer('priv_updates', 0) ?   1 : 0;

        //      $priv_debug     = get_integer('priv_debug',0)?     1 : 0;

        $priv_audit     = get_integer('priv_audit', 0) ?     1 : 0;

        $priv_csrv      = get_integer('priv_csrv', 0) ?      1 : 0;



        /* Get the logo and footer information */

        $logo           = trim(get_argument('logo_file', 1, ''));

        $xxx            = trim(get_argument('logo_x', 1, ''));

        $yyy            = trim(get_argument('logo_y', 1, ''));

        $lfoot          = trim(get_argument('footer_left', 1, ''));

        $rfoot             = trim(get_argument('footer_right', 1, ''));

        $custlist       = get_argument('cust', 0, array());



        // check for password change

        if (strlen($new_pwd) || strlen($confirm_pwd)) {

            $response = check_pwd_change($db, $username, 0, '', $new_pwd, $confirm_pwd);

            if ($response == "success")

                $sql_pwd = " password='" . md5($new_pwd) . "',\n";

            elseif (strlen(trim($response)))

                $msg = $response;

            else

                $msg = "There was a problem with this update. Please try again.";
        }



        if ($msg == '') {

            // update Users table

            priv_decode($priv_level, $priv_admin, $priv_restrict);

            $sql  = "update Users set\n";

            if (strlen(trim($sql_pwd))) $sql .= $sql_pwd;

            $sql .= " report_mail='$report_mail',\n";

            $sql .= " notify_mail='$notify_mail',\n";

            $sql .= " priv_admin=$priv_admin,\n";

            //      $sql .= " priv_debug=$priv_debug,\n";

            $sql .= " priv_notify=$priv_notify,\n";

            $sql .= " priv_search=$priv_search,\n";

            $sql .= " priv_report=$priv_report,\n";

            $sql .= " priv_areport=$priv_areport,\n";

            $sql .= " priv_aquery=$priv_aquery,\n";

            $sql .= " priv_config=$priv_config,\n";

            $sql .= " priv_provis=$priv_provis,\n";

            $sql .= " priv_asset=$priv_asset,\n";

            $sql .= " priv_downloads=$priv_downloads,\n";

            $sql .= " priv_updates=$priv_updates,\n";

            $sql .= " priv_restrict=$priv_restrict,\n";

            $sql .= " priv_audit=$priv_audit,\n";

            $sql .= " priv_csrv=$priv_csrv,\n";

            $sql .= " filtersites=0,\n";

            $sql .= " logo_file='$logo',\n";

            $sql .= " logo_x='$xxx',\n";

            $sql .= " logo_y='$yyy',\n";

            $sql .= " footer_left='$lfoot',\n";

            $sql .= " footer_right='$rfoot'\n";

            $sql .= " where userid = $id";

            $res  = redcommand($sql, $db);



            if ($res) {

                $err = PHP_DSYN_InvalidateRow(
                    CUR,
                    (int)$id,
                    "userid",

                    constDataSetCoreUsers,
                    constOperationDelete
                );

                if ($err != constAppNoErr) {

                    logs::log(__FILE__, __LINE__, "GCFG_SetVariableValue: PHP_DSYN_InvalidateRow "

                        . "returned " . $err, 0);

                    return constSetVarValFailure;
                }



                if (mysqli_affected_rows($db) == 1) {

                    $log = "update $username by $authuser;"

                        . " ad:$priv_admin cf:$priv_config as:$priv_asset dl:$priv_downloads up:$priv_updates"

                        . " gn:$priv_notify gr:$priv_report gs:$priv_search gq:$priv_aquery ga:$priv_areport"

                        . " pr:$priv_provis rs:$priv_restrict"

                        . " privAudit:$priv_audit privCSRV:$priv_csrv";

                    logs::log(__FILE__, __LINE__, $log, 0);

                    debug_note($log);
                }

                $msg = update_user_cust($username, $custlist, $db);
            } else {

                $msg = "User <b>$username</b> unchanged.";
            }
        }

        $msg .= backtoadmin();

        message($msg);
    }
}





function confirm_delete_cust($id, $admin, $db)

{

    if ($admin) {

        $cust = find_cust($id, $db);

        if ($cust) {

            $self = server_var('PHP_SELF');

            $href = "$self?action=dc&id=$id";

            $yes  = html_link($href, 'Yes');

            $no   = html_link('admin.php', 'No');



            $site = $cust['customer'];

            $msg  = "Are you sure you want to delete site <b>$site</b>?<br>";

            $msg .= "<br>";

            $msg .= "$yes&nbsp;&nbsp;&nbsp;$no";
        } else {

            $msg = "Site $id does not exist.";
        }

        echo fontspeak($msg);
    }
}



function confirm_delete_user($id, $admin, $db)

{

    if ($admin) {

        $user = find_user($id, $db);

        if ($user) {

            $self = server_var('PHP_SELF');

            $href = "$self?action=du&id=$id";

            $yes  = html_link($href, 'Yes');

            $no   = html_link('admin.php', 'No');



            $username = $user['username'];

            $msg  = "Are you sure you want to delete user <b>$username</b>?<br>\n";

            $msg .= "<br>\n";

            $msg .= "$yes&nbsp;&nbsp;&nbsp;$no";

            message($msg);
        }
    }
}





function get_ids_before_delete($db, $name, $table)

{

    $ids = array();

    $id_string = '';



    $qname = safe_addslashes($name);

    $sql = "select id from $table where username = '$qname'";

    $res = redcommand($sql, $db);

    if ($res) {

        while ($row = mysqli_fetch_assoc($res)) {

            $ids[] = $row['id'];
        }

        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);

        $id_string = implode(',', $ids);
    }

    return $id_string;
}



function delete_user_auxtable($db, $objecttype, $ids_to_delete)

{

    if (strlen($ids_to_delete)) {

        switch ($objecttype) {

            case 'assetreport':

                $idfield    = 'assetreportid';

                $auxtable   = 'RptSiteFilters';

                break;

            default:

                $idfield    = '';

                $auxtable   = '';

                break;
        }



        $sql = "delete from $auxtable where $idfield IN ($ids_to_delete)";

        redcommand($sql, $db);
    }
}



function delete_user_table($db, $name, $table)

{

    $qname = safe_addslashes($name);

    $sql = "delete from $table where username = '$qname'";

    redcommand($sql, $db);
}





function delete_user($id, $admin, $authuser, $db)

{

    if ($admin) {

        $user = find_user($id, $db);

        if ($user) {

            $name = $user['username'];

            logs::log(__FILE__, __LINE__, "user $name removed by $authuser", 0);

            if (mysqli_select_db($db, event)) {

                $deleted_report_ids = get_ids_before_delete($db, $name, 'Reports');

                delete_user_table($db, $name, 'Reports');



                $deleted_notn_ids = get_ids_before_delete($db, $name, 'Notifications');

                delete_user_table($db, $name, 'Notifications');



                delete_user_table($db, $name, 'Console');



                delete_user_table($db, $name, 'SavedSearches');

                PHP_REPF_UpdateDynamicList(CUR, constJavaListEventFilters);
            }

            if (mysqli_select_db($db, asset)) {

                delete_user_table($db, $name, 'AssetSearches');



                $deleted_asstrpt_ids = get_ids_before_delete($db, $name, 'AssetReports');

                delete_user_table($db, $name, 'AssetReports');

                delete_user_auxtable($db, 'assetreport', $deleted_asstrpt_ids);



                purge_unused_criteria($db);
            }

            mysqli_select_db($db, core);

            delete_user_table($db, $name, 'Customers');



            $err = PHP_DSYN_InvalidateRow(
                CUR,
                (int)$id,
                "userid",

                constDataSetCoreUsers,

                constOperationPermanentDelete
            );



            $sql = "delete from Users where userid = $id";

            redcommand($sql, $db);

            $msg = "User <b>$name</b> has been removed.";

            groups_init($db, constGroupsInitFull);
        } else {

            $msg = "User $id does not exist.";
        }
    } else {

        $msg = "Authorization denied.";
    }

    $msg .= backtoadmin();

    message($msg);
}





function purge_what($num, $what)

{

    $msg = "admin: removed $num records from $what.";

    debug_note($msg);

    if ($num > 0) logs::log(__FILE__, __LINE__, $msg, 0);
}



function purge_clause($table, $clause, $db)

{

    $sql = "delete from $table\n where $clause";

    $res = redcommand($sql, $db);

    $num = affected($res, $db);

    purge_what($num, $table);
}





function delete_cust($id, $admin, $authuser, $db)

{

    if ($admin) {

        $cust = find_cust($id, $db);

        if ($cust) {

            $site  = $cust['customer'];

            logs::log(__FILE__, __LINE__, "admin: site $site removed by $authuser", 0);



            $list    = site_list($site, $db);

            $patch   = purge_patch_list($list, $db);

            $groups  = purge_groups_list($list, $db);

            $event   = purge_event_site($site, $db);

            $asset   = purge_asset_site($site, $db);

            $provis  = purge_provis_site($site, $db);

            $crypt   = purge_crypt_site($site, $db);

            $config  = purge_config_site($site, $db);

            $update  = purge_update_site($site, $db);

            $provis += $crypt;



            purge_what($asset,  'asset');

            purge_what($event,  'event');

            purge_what($provis, 'provision');

            purge_what($config, 'core');

            purge_what($update, 'swupdate');

            purge_what($patch,  'softinst');



            if (mysqli_select_db($db, core)) {

                $qs = safe_addslashes($site);

                purge_clause('Census', "site = '$qs'", $db);

                purge_clause('Customers', "customer = '$qs'", $db);

                groups_init($db, constGroupsInitFull);
            }

            $msg  = "Site <b>$site</b> has been removed.<br>\n";
        } else {

            $msg = "Site $id does not exist.";
        }
    } else {

        $msg = "Authorization denied.";
    }

    $msg .= backtoadmin();

    message($msg);
}





function confirm_create_cust($admin)

{

    if ($admin) {

        $self = server_var('PHP_SELF');

        echo "<form method=\"post\" action=\"$self\">\n";

        echo "<input type=\"hidden\" name=\"action\" value=\"cc\">\n";

        table_header();

        echo special_header('Create Site', 2);

        $args[0] = 'Name: ';

        $args[1] = '<input type="text" name="customer" size="50" maxlength="50">';

        table_data($args, 0);

        $site_email = textbox('site_email', 50, '');

        $args[0] = 'Site Email: ';

        $args[1] = $site_email;

        table_data($args, 0);

        $submit = '<input type="submit" name="submit" value="create">';

        $reset  = '<input type="reset" value="reset">';

        $action = "$submit&nbsp;&nbsp;&nbsp;\n$reset";

        echo span_data(2, $action);

        table_footer();

        echo "</form>";
    }
}



/* confirm_create_user()

        This page is used to create a new user

    */

function confirm_create_user($admin, $db)

{

    if ($admin) {

        $self = server_var('PHP_SELF');

        echo "<form method=\"post\" action=\"$self\">\n";

        echo "<input type=\"hidden\" name=\"action\" value=\"cu\">\n";

        table_header();

        echo special_header('Create User', 3);



        /* Username */

        $doc  = '<i>enter new user account name</i>';

        $args[0] = 'Name: ';

        $args[1] = '<input type="text" name="username" size="50">';

        $args[2] = $doc;

        table_data($args, 0);



        /* Password */

        $p0      = '<input type="password" name="new_pwd" size="15">';

        $p1      = '<input type="password" name="confirm_pwd" size="15">';



        $doc     = "<i>enter the same password twice</i>";

        $args[0] = 'Password: ';

        $args[1] = "$p0\nConfirm:&nbsp;$p1";

        $args[2] = $doc;

        table_data($args, 0);



        /* Email addresses  -Notify- */

        $doc     = '<i>enter comma-separated list of email addesses</i>';

        $args[0] = 'Notify mail: ';

        $args[1] = '<input type="text" name="notify_mail" size="70">';

        $args[2] = $doc;

        table_data($args, 0);



        /* Email addresses  -Report- */

        $args[0] = 'Report mail: ';

        $args[1] = '<input type="text" name="report_mail" size="70">';

        $args[2] = $doc;

        table_data($args, 0);



        /* Account privileges labels */

        $da = '<i>controls account type</i>';

        $dn = '<i>can create and edit global notifications</i>';

        $dr = '<i>can create and edit global reports</i>';

        $ds = '<i>can create and edit global searches</i>';

        $dq = '<i>can create and edit global queries</i>';

        $dx = '<i>can create and edit global asset reports</i>';

        $dc = '<i>can configure client machines</i>';

        $dp = '<i>can provision client machines</i>';

        $dt = '<i>can remove machine assets</i>';

        $dl = '<i>can control downloads</i>';

        $du = '<i>can control updates</i>';

        $de = '<i>restricted access</i>';

        $diagAudit = '<i>can view and control audits</i>';

        $diagCSRV = '<i>can access CSRV</i>';



        /* Build the selects */

        $yesno  = array('No', 'Yes');

        $privs  = array('Normal', 'Restricted', 'Admin');

        $pa = select_with_values('priv_level',    $privs, 0);

        $pn = select_with_values('priv_notify',   $yesno, 0);

        $ps = select_with_values('priv_search',   $yesno, 0);

        $pq = select_with_values('priv_aquery',   $yesno, 0);

        $pr = select_with_values('priv_report',   $yesno, 0);

        $px = select_with_values('priv_areport',  $yesno, 0);

        $pc = select_with_values('priv_config',   $yesno, 0);

        $pp = select_with_values('priv_provis',   $yesno, 0);

        $pt = select_with_values('priv_asset',    $yesno, 0);

        $pl = select_with_values('priv_downloads', $yesno, 0);

        $pu = select_with_values('priv_updates',  $yesno, 0);

        $privAudit = select_with_values('priv_audit', $yesno, 0);

        $privCSRV = select_with_values('priv_csrv', $yesno, 0);



        /* Assign labels */

        $na = 'Privileges: ';

        $nn = 'Global Event Notify: ';

        $nr = 'Global Event Report: ';

        $ns = 'Global Event Search: ';

        $nq = 'Global Asset Query: ';

        $nx = 'Global Asset Report: ';

        $nc = 'Configure Machines: ';

        $np = 'Provision Machines: ';

        $nt = 'Remove Assets: ';

        $nl = 'Control Downloads: ';

        $nu = 'Control Updates: ';

        $noteAudit = 'Audit Rights: ';

        $noteCSRV = 'CSRV Access: ';



        table_data(array($na, $pa, $da), 0);

        table_data(array($nn, $pn, $dn), 0);

        table_data(array($nr, $pr, $dr), 0);

        table_data(array($ns, $ps, $ds), 0);

        table_data(array($nq, $pq, $dq), 0);

        table_data(array($nx, $px, $dx), 0);

        table_data(array($nc, $pc, $dc), 0);

        table_data(array($np, $pp, $dp), 0);

        table_data(array($nt, $pt, $dt), 0);

        table_data(array($nl, $pl, $dl), 0);

        table_data(array($nu, $pu, $du), 0);

        table_data(array($noteAudit, $privAudit, $diagAudit), 0);

        table_data(array($noteCSRV, $privCSRV, $diagCSRV), 0);



        $doc      = '<i>specify sites this user is allowed to access</i>';

        $allcust  = find_all_customers($db);

        $size = safe_count($allcust);

        if ($size > 0) {

            $size = ($size > 12) ? 12 : $size;

            $selected = array();

            $args[0] = 'Sites:';

            $args[1] = select_scroll('cust[]', $size, 1, $allcust, $selected);

            $args[2] = $doc;

            table_data($args, 0);
        }



        /* File name for logo  */

        $msg = constLogoMsg;

        $doc  = "<i> $msg </i>";

        $args[0] = 'Logo file: ';

        $args[1] = '<input type="text" name="logo_file" size="50">';

        $args[2] = $doc;

        table_data($args, 0);



        /* X for logo */

        $msg = constWidthMsg;

        $doc  = "<i> $msg </i>";

        $args[0] = 'Width (Pixels): ';

        $args[1] = '<input type="text" name="logo_x" size="50">';

        $args[2] = $doc;

        table_data($args, 0);



        /* Y for logo */

        $msg = constHeightMsg;

        $doc  = "<i> $msg </i>";

        $args[0] = 'Height (Pixels): ';

        $args[1] = '<input type="text" name="logo_y" size="50">';

        $args[2] = $doc;

        table_data($args, 0);



        /* Footer - Left side of page */

        $msg = constFooterLeftMsg;

        $doc  = "<i> $msg </i>";

        $args[0] = 'Footer left: ';

        $args[1] = '<textarea wrap="virtual" name="footer_left" rows="5" cols="50"></textarea>';

        $args[2] = $doc;

        table_data($args, 0);



        /* Footer - Right side of page */

        $msg = constFooterRightMsg;

        $doc  = "<i> $msg </i>";

        $args[0] = 'Footer right: ';

        $args[1] = '<textarea wrap="virtual" name="footer_right" rows="5" cols="50"></textarea>';

        $args[2] = $doc;

        table_data($args, 0);



        $submit = "<input type='submit' name='submit' value='create'>";

        $reset  = "<input type='reset' value='reset'>";

        $action = "$submit&nbsp;&nbsp;&nbsp;$reset";

        echo span_data(3, $action);

        table_footer();

        echo "</form>\n";
    }
}





function legal_name($name)

{

    if (!is_string($name)) {

        debug_note('not a string type');

        return false;
    }

    $len = strlen($name);

    if (($len < 1) || ($len > 20)) {

        debug_note("name length ($len) invalid.");

        return false;
    }

    $ch = $name[0];

    $good = ((('a' <= $ch) && ($ch <= 'z')) ||

        (('A' <= $ch) && ($ch <= 'Z')));

    if (!$good) {

        debug_note("$name does not begin correctly");

        return false;
    }

    for ($i = 0; $i < $len; $i++) {

        $ch = $name[$i];

        $good = ((('0' <= $ch) && ($ch <= '9')) ||

            (('a' <= $ch) && ($ch <= 'z')) ||

            (('A' <= $ch) && ($ch <= 'Z')));

        if (!$good) {

            debug_note("weird character $ch at position $i");

            return false;
        }
    }

    return true;
}



/* create_user()



        Take the data from the confirm_create_user page and enter it into

        the database.



     */

function create_user($admin, $authuser, $db)

{

    $sql_pwd  = '';

    $sql_notn = '';

    $sql_rprt = '';

    $msg      = '';



    if ($admin) {

        $username       = trim(get_argument('username', 0, ''));

        $new_pwd        = trim(get_argument('new_pwd', 0, ''));

        $confirm_pwd    = trim(get_argument('confirm_pwd', 0, ''));

        $report_mail    = trim(get_argument('report_mail', 1, ''));

        $notify_mail    = trim(get_argument('notify_mail', 1, ''));



        $cust           = get_argument('cust', 0, array());

        $priv_admin     = 0;

        $priv_restrict  = 0;

        $priv_level     = get_integer('priv_level', 0);

        $priv_notify    = get_integer('priv_notify', 0) ?    1 : 0;

        $priv_search    = get_integer('priv_search', 0) ?    1 : 0;

        $priv_report    = get_integer('priv_report', 0) ?    1 : 0;

        $priv_aquery    = get_integer('priv_aquery', 0) ?    1 : 0;

        $priv_areport   = get_integer('priv_areport', 0) ?   1 : 0;

        $priv_config    = get_integer('priv_config', 0) ?    1 : 0;

        $priv_provis    = get_integer('priv_provis', 0) ?    1 : 0;

        $priv_asset     = get_integer('priv_asset', 0) ?     1 : 0;

        $priv_downloads = get_integer('priv_downloads', 0) ? 1 : 0;

        $priv_updates   = get_integer('priv_updates', 0) ?   1 : 0;

        $priv_debug     = 0;  // not on creation

        $priv_audit     = get_integer('priv_audit', 0) ?     1 : 0;

        $priv_csrv      = get_integer('priv_csrv', 0) ?      1 : 0;



        /* Get the logo and footer information */

        $logo           = trim(get_argument('logo_file', 1, ''));

        $xxx            = trim(get_argument('logo_x', 1, ''));

        $yyy            = trim(get_argument('logo_y', 1, ''));

        $lfoot          = trim(get_argument('footer_left', 1, ''));

        $rfoot          = trim(get_argument('footer_right', 1, ''));



        if (legal_name($username)) {

            // check unique username

            $num = 0;

            $quser = safe_addslashes($username);

            $sql  = "select * from Users where";

            $sql .= " username = '$quser'";

            $res  = command($sql, $db);

            if ($res) {

                $num = mysqli_num_rows($res);

                if ($num == 0) {

                    $good = 1;
                }

                ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
            }



            if ($num > 0) {

                $msg = "User <b>$username</b> already exists.";
            }
        } else {

            $msg = "User name <b>$username</b> is invalid.";
        }



        // check for password

        if ($msg == '') {

            if (strlen($new_pwd) || strlen($confirm_pwd)) {

                $response = check_pwd_change($db, $username, 0, '', $new_pwd, $confirm_pwd);

                if ($response == "success")

                    $sql_pwd = " password='" . md5($new_pwd) . "',\n";

                elseif (strlen(trim($response)))

                    $msg = $response;

                else

                    $msg = "There was a problem with this update. Please try again.";
            } else {

                $msg = "You must provide a password and confirm it.";
            }
        }



        if ($msg == '') {

            priv_decode($priv_level, $priv_admin, $priv_restrict);

            // update Users table

            $sql  = "insert into Users set\n";

            $sql .= " username='$quser',\n";

            if (strlen(trim($sql_pwd))) $sql .= $sql_pwd;

            $sql .= " report_mail='$report_mail',\n";

            $sql .= " notify_mail='$notify_mail',\n";

            $sql .= " priv_admin=$priv_admin,\n";

            $sql .= " priv_notify=$priv_notify,\n";

            $sql .= " priv_search=$priv_search,\n";

            $sql .= " priv_aquery=$priv_aquery,\n";

            $sql .= " priv_areport=$priv_areport,\n";

            $sql .= " priv_report=$priv_report,\n";

            $sql .= " priv_config=$priv_config,\n";

            $sql .= " priv_provis=$priv_provis,\n";

            $sql .= " priv_asset=$priv_asset,\n";

            $sql .= " priv_downloads=$priv_downloads,\n";

            $sql .= " priv_updates=$priv_updates,\n";

            $sql .= " priv_debug=$priv_debug,\n";

            $sql .= " priv_restrict=$priv_restrict,\n";

            $sql .= " priv_audit=$priv_audit,\n";

            $sql .= " priv_csrv=$priv_csrv,\n";

            $sql .= " filtersites=0,\n";

            $sql .= " logo_file='$logo',\n";

            $sql .= " logo_x='$xxx',\n";

            $sql .= " logo_y='$yyy',\n";

            $sql .= " footer_left='$lfoot',\n";

            $sql .= " footer_right='$rfoot'";

            $res  = redcommand($sql, $db);

            if ($res) {

                $num = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);

                $err = PHP_DSYN_InvalidateRow(
                    CUR,
                    (int)$num,

                    "userid",
                    constDataSetCoreUsers,

                    constOperationInsert
                );

                if ($err != constAppNoErr) {

                    logs::log(__FILE__, __LINE__, "GCFG_SetVariableValue: PHP_DSYN_InvalidateRow "

                        . "returned " . $err, 0);

                    return constSetVarValFailure;
                }



                $msg  = "New user <b>$username</b> created.<br>\n";

                $log  = "user $username created by $authuser;"

                    . " ad:$priv_admin db:$priv_debug cf:$priv_config as:$priv_asset"

                    . " dl:$priv_downloads up:$priv_updates"

                    . " gn:$priv_notify gr:$priv_report gs:$priv_search gq:$priv_aquery ga:$priv_areport"

                    . " pr:$priv_provis rs:$priv_restrict"

                    . " privAudit:$priv_audit privCSRV:$priv_csrv";

                logs::log(__FILE__, __LINE__, $log, 0);

                debug_note($log);

                groups_init($db, constGroupsInitFull);
            } else {

                $msg = "Unable to create <b>$username</b>.";

                $cust = array();
            }

            $carr = find_all_customers($db);

            if (($cust) && ($carr)) {

                reset($cust);

                foreach ($cust as $key => $data) {

                    $site = @$carr[$data];

                    debug_note("site:$site");

                    if (cust_exists($site, $db)) {

                        $qsite = safe_addslashes($site);

                        $sql  = "insert into Customers set\n";

                        $sql .= " customer='$qsite',\n";

                        $sql .= " username='$quser'";

                        $res  = redcommand($sql, $db);

                        if ($res) {

                            $msg .= "User <b>$username</b> has access to <b>$site</b>.<br>\n";

                            $log  = "user $username has access to $site.";

                            logs::log(__FILE__, __LINE__, $log, 0);
                        }
                    }
                }
            } else {

                debug_note("No sites for user $username");
            }
        }

        $msg .= backtoadmin();





        message($msg);



        //      phpinfo();

    }
}





/*

    |  Site ownership only counts when there is

    |  exactly one owner.  If by some accident or

    |  bug there are two or more, then it does not

    |  count, and we want to return an empty array.

    */



function find_site_owner($site, $db)

{

    $qs   = safe_addslashes($site);

    $sql  = "select * from Customers\n";

    $sql .= " where owner = 1 and\n";

    $sql .= " customer = '$qs'";

    return find_one($sql, $db);
}





/*

    |  Assign the ownership of the specified site.

    |

    |  The provisioning rpc code needs to know who owns

    |  the site to find the correct

    |  product id.

    |

    |  We want to always guarantee that each site has at

    |  most one owner.  It is ok for a site to have no

    |  owner, however

    |

    |  Always update the owner by explicity specifying

    |  both the site and the username ... if $cid is bogus

    |  we just want the update to fail, rather than

    |  updating the wrong record.

    */



function assign_site($admin, $authuser, $db)

{

    // these variables are posted from assign_site_owner()

    $id         = get_integer('id', 0);

    $site_email = get_string('site_email', '');



    $cust = find_cust($id, $db);

    $mesg = "This command requires administrative access.";

    if (($admin) && ($cust)) {

        $site = $cust['customer'];

        $qs   = safe_addslashes($site);

        $sql  = "update Customers\n";

        $sql .= " set owner = 0\n";

        $sql .= " where customer = '$qs'";

        redcommand($sql, $db);

        $cid  = get_integer('cid', 0);

        $user = find_cust($cid, $db);

        $good = false;

        if ($user) {

            $name = $user['username'];

            $qu   = safe_addslashes($name);

            $sql  = "update Customers set owner = 1\n";

            $sql .= " where username = '$qu' and\n";

            $sql .= " customer = '$qs'";

            $res  = redcommand($sql, $db);

            if (affected($res, $db)) {

                $good = true;
            }
        }

        if ($site_email) {

            $good = set_site_email($qs, $site_email, $db);
        }

        if ($good) {

            $text  = "admin: $site granted to $name by $authuser.";

            $mesg  = "Site <b>$site</b> owned by <b>$name</b>.";

            $mesg .= ($site_email) ? "<br>Site email set to <b>$site_email</b>." :

                "Site email not set.";
        } else {

            $text = "admin: $site has no owner by $authuser.";

            $mesg = "Site <b>$site</b> has no owner.";
        }

        logs::log(__FILE__, __LINE__, $text, 0);
    }

    $mesg .= "<br>\n";

    $mesg .= backtoadmin();

    message($mesg);
}





function set_site_owner($admin, $authuser, $db)

{

    $sid = get_integer('id', 0);

    $cid = 0;

    $cust = find_cust($sid, $db);

    if (($admin) && ($cust)) {

        $self = server_var('PHP_SELF');

        $site = $cust['customer'];

        $user = find_site_users($site, $db);

        if ($user) {

            $owner      = find_site_owner($site, $db);

            $site_email = find_site_email($site, $db);

            if ($owner) {

                $cid  = $owner['id'];

                $name = $owner['username'];

                $mesg = "Site <b>$site</b> is currently owned by <b>$name</b>.";
            } else {

                $mesg = "Site <b>$site</b> has no owner.";
            }

            message($mesg);

            echo "\n<br><br>\n\n";

            echo "<form method=\"post\" action=\"$self\">\n";

            echo "<input type=\"hidden\" name=\"action\" value=\"as\">\n";

            echo "<input type=\"hidden\" name=\"id\" value=\"$sid\">\n";

            $site_email_text = textbox('site_email', 50, $site_email);

            table_header();

            echo special_header('Set Site Owner', 2);

            $args[0] = 'Name: ';

            $args[1] = $site;

            table_data($args, 0);

            $args[0] = 'Owner: ';

            $args[1] = html_select('cid', $user, $cid, 1);

            table_data($args, 0);

            $args[0] = 'Site Email Address: ';

            $args[1] = $site_email_text;

            table_data($args, 0);

            $submit = '<input type="submit" name="submit" value="assign">';

            $reset  = '<input type="reset" value="reset">';

            $action = "$submit&nbsp;&nbsp;&nbsp;\n$reset";

            echo span_data(2, $action);

            table_footer();

            echo "</form>";
        } else {

            $mesg  = "Site <b>$site</b> has no users.<br>\n";

            $mesg .= backtoadmin();

            message($mesg);
        }
    } else {

        $mesg  = "Authorization denied.<br>";

        $mesg .= backtoadmin();

        message($mesg);
    }
}



function create_cust($admin, $authuser, $db)

{

    if ($admin) {

        $customer   = get_string('customer', '');

        $site_email = get_string('site_email', '');

        if ($customer) {

            if (cust_exists($customer, $db)) {

                message("Site <b>$customer</b> already exists.");
            } else {

                $qcust = safe_addslashes($customer);

                $sql   = "insert into\n Customers set\n";

                $sql  .= " customer='$qcust',\n";

                $sql  .= " username=";

                $none  = $sql . "''";

                $auth  = $sql . "'$authuser',\n owner=1";

                $res1  = redcommand($none, $db);

                $res2  = redcommand($auth, $db);

                if (($res1) && ($res2)) {

                    /* successfully created a new site */

                    $good    = set_site_email($qcust, $site_email, $db);

                    $msg_txt = ($good) ? "Site email set to <b>$site_email</b>" :

                        "Site email not set.";



                    message("Site <b>$customer</b> has been created.");

                    message($msg_txt);

                    logs::log(__FILE__, __LINE__, "new site $customer created by $authuser", 0);
                }
            }
        }

        echo backtoadmin();
    }
}





/*

    |  Need a hidden way to toggle the debug priv from the web interface,

    |  since many times a ssh login is difficult to arrange.

    |

    |  This should work only if the account is admin already.

    */



function toggle_debug($user, $db)

{

    $admin = $user['priv_admin'];

    $debug = $user['priv_debug'];

    $name  = $user['username'];

    $id    = $user['userid'];

    if ($admin) {

        $dbg = ($debug) ? 0 : 1;

        $sql = "update Users set priv_debug = $dbg where userid = $id";

        redcommand($sql, $db);

        logs::log(__FILE__, __LINE__, "users: $name has priv_debug:$dbg", 0);

        $enabled = ($dbg) ? 'enabled' : 'disabled';

        $msg = "Debug $enabled for account <b>$name</b>.";
    } else {

        $msg = "Authorization denied.";
    }

    message($msg);
}





/*

    |  Main program

    */



$db = db_connect();

$authuser = process_login($db);

$comp = component_installed();



$file     = $comp['file'];

$test_sql = ('adm-act.php' == $file) ? 0 : 1;

$user     = user_data($authuser, $db);

$admin    = @($user['priv_admin']) ? 1 : 0;

$debug    = @($user['priv_debug']) ? 1 : 0;

$id       = get_integer('id', 0);



$msg = ob_get_contents();           // save the buffered output so we can...

ob_end_clean();                     // (now dump the buffer)

echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users



echo "<br><br>\n";



debug_note("admin:$admin, debug:$debug, action:$action, id:$id");



switch ($action) {

    case 'auu':
        admin_update_user($admin, $authuser, $db);
        break;

    case 'cc':
        create_cust($admin, $authuser, $db);
        break;

    case 'cu':
        create_user($admin, $authuser, $db);
        break;

    case 'dc':
        delete_cust($id, $admin, $authuser, $db);
        break;

    case 'du':
        delete_user($id, $admin, $authuser, $db);
        break;

    case 'dbg':
        toggle_debug($user, $db);
        break;

    case 'ec': /* edit_cust($id,$admin,$db); */
        break;

    case 'eu':
        edit_user($id, $admin, $db);
        break;

        //  case 'gu'  : global_user($id,$admin,$authuser,$db);     break;

    case 'uc': /* update_cust($id,$admin,$users,$db); */
        break;

    case 'uu':
        update_user($admin, $authuser, $db);
        break;

    case 'ccc':
        confirm_create_cust($admin);
        break;

    case 'ccu':
        confirm_create_user($admin, $db);
        break;

    case 'cdc':
        confirm_delete_cust($id, $admin, $db);
        break;

    case 'cdu':
        confirm_delete_user($id, $admin, $db);
        break;

    case 'sso':
        set_site_owner($admin, $authuser, $db);
        break;

    case 'as':
        assign_site($admin, $authuser, $db);
        break;

    default:
        break;
}





echo head_standard_html_footer($authuser, $db);
