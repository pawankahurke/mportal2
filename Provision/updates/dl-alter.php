<?php

/*
Revision history:

Date        Who     What
----        ---     ----
06-Nov-02   NL      create file
22-Nov-02   NL      filter sitename to authuser access
22-Nov-02   NL      alter_priv (required to continue)
22-Nov-02   NL      move redcommand to common.php3
22-Nov-02   NL      add error checking for action, id & alter_priv
22-Nov-02   NL      add Site Name, Username, and Password fields
26-Nov-02   NL      comment out php code in commented out HTML (future fields)
 3-Dec-02   NL      change titles
 5-Dec-02   NL      get sitename from hfnlog.Customers table; just use cust_array()
16-Dec-02   EWB     fixed short php tags
16-Jan-03   EWB     Don't require register_globals.
10-Feb-03   EWB     Uses sandbox libraries
10-Feb-03   EWB     Uses new database
11-Feb-03   EWB     Removed common.php
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
12-Mar-03   EWB     changed database name to 'swupdate'.
25-Apr-03   EWB     Site filters.
29-Apr-03   EWB     l-cust not needed.
30-Apr-03   EWB     user filter sites.
 5-May-03   EWB     Fixed backwards priv check.
26-May-03   AAM     Fixed spelling of privileges.
29-Mar-04   EWB     Allow user update for Downloads.cmdline.
30-Mar-04   EWB     Allow user to set Downloads.cmdline when creating new version.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( 'header.php'  );
include_once ( '../lib/l-slct.php'  );
include_once ( '../lib/l-gsql.php'  );
include_once ( '../lib/l-user.php'  );


    function double($name,$valu)
    {
        echo <<< HERE

        <tr>
            <td>$name</td>
            <td>$valu</td>
        </tr>
HERE;
       
    }

    function checkbox($name,$checked)
    {
        $valu = ($checked)? 'checked' : '';
        return "<input type=\"checkbox\" $valu name=\"$name\" value=\"1\">";
    }

    function textbox($name,$max,$size,$valu)
    {
        $text = '';
        if ($valu != '')
        {
            $disp = str_replace('"','&quot;',$valu);
            $disp = str_replace("'",'&#039;',$disp);
            $text = " value=\"$disp\"";
        }
        return "<input type=\"text\" name=\"$name\" maxlength=\"$max\" size=\"$size\"$text>";
    }

    function passbox($name,$max,$size,$valu)
    {
        $text = '';
        if ($valu != '')
        {
            $disp = str_replace('"','&quot;',$valu);
            $disp = str_replace("'",'&#039;',$disp);
            $text = " value=\"$disp\"";
        }
        return "<input type=\"password\" name=\"$name\" maxlength=\"$max\" size=\"$size\"$text>";
    }

   /*
    |  Main program
    */

    $action = get_string('action','none');
    $id     = get_integer('id',0);

    switch ($action)
    {
        case 'add'  : $title = "Add a Version Record";   $submit_label = "Add";     break;
        case 'edit' : $title = "Edit a Version Record";  $submit_label = "Update";  break;
        case 'copy' : $title = "Copy a Version Record";  $submit_label = "Copy";    break;
        default     : $title = "Version Record";                                    break;
    }

    $db = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();
    $user = user_data($authuser,$db);

    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer)
    echo standard_html_header($title,$comp,$authuser,$local_nav,0,0,$db);
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users


   /*
    |   Perform error checking
    */

    $error = '';
    $filter     = @ ($user['filtersites'])?    1 : 0;
    $alter_priv = @ ($user['priv_downloads'])? 1 : 0;
    $debug      = @ ($user['priv_debug'])?     1 : 0;

    // check for action & id

    if (($id <= 0) && ($action != 'add'))
    {
        $error .= "<br>Error -- no id specified.";
    }

    if ($action == 'none')
    {
        $error .= "<br>Error -- no action specified.";
    }

    // check for permissions to alter records
    if (!$alter_priv)
    {
        $error .= "<br>Error -- You do not have privileges to add or edit records.";
    }

   /*
    |   Get sitename options for select list
    */

    $sitenames = site_array($authuser,$filter,$db);
    db_change($GLOBALS['PREFIX'].'swupdate',$db);

    $global   = 1;
    $name     = '';
    $filename = '';
    $password = '';
    $username = '';
    $version  = '';
    $cmdline  = '';
    $target   = '';
    $url      = '';

   /*
    |   Get existing values from the database
    */

    if ((($action == 'edit') || ($action == 'copy')) && ($id > 0))
    {
        $qu  = safe_addslashes($authuser);
        $sql = "select * from Downloads\n"
             . " where id = $id and\n"
             . " (owner in ('$qu','') or\n"
             . " global = 1)";
        $row = find_one($sql, $db);
        if ($row)
        {
            $id         = $row['id'];
            $name       = $row['name'];
            $version    = $row['version'];
            $global     = $row['global'];
            $sitename   = $row['sitename'];
            $url        = @ trim($row['url']);
            $username   = @ trim($row['username']);
            $password   = @ trim($row['password']);
            $cmdline    = @ trim($row['cmdline']);
            $filename   = @ trim($row['filename']);
            $target     = @ trim($row['target']);
        }
        else
        {
            $error .= '<br>Can not find the correct record.';
        }
    }

    if ($error)
    {
        echo "<span class=red>$error</span>\n";
        $action = 'error';
    }

    if ($action == 'add')
    {
        $cmdline = server_opt('update_cmdline',$db);
    }


    $len = 50;
    $input_name = textbox('name',255,$len,$name);
    $input_glob = checkbox('global',$global);
    $input_pass = passbox('password',255,$len,$password);
    $input_vers = textbox('version',255,$len,$version);
    $input_url  = textbox('url',255,$len,$url);
    $input_user = textbox('username',255,$len,$username);
    $input_cmd  = textbox('cmdline',255,$len,$cmdline);
    $input_targ = textbox('target',255,$len,$target);
    $input_file = textbox('filename',255,$len,$filename);

    if (($action == 'add') || ($action == 'edit') || ($action == 'copy'))
    {
        echo <<< HERE

        <form method="post" action="dl-act.php" name="form">
        <input type="hidden" name="action" value="$action">

HERE;

    }

    if ($action == 'edit') 
    {
        echo "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";
    }

    if (($action == 'add') || ($action == 'edit') || ($action == 'copy'))
    {
        echo "<table border=0 cellpadding=3>\n";
        double('Version Name:',     $input_name);
        double('Version Number:',   $input_vers);
        double('Global:',           $input_glob);
        double('Download URL:',     $input_url);
        double('Username:',         $input_user);
        double('Password:',         $input_pass);
     // double('Filename:',         $input_file);
     // double('Target Directory:', $input_targ);
        double('Command Line:',     $input_cmd);
        echo <<< HERE

        </table>
        <br>

        <table border=0 cellpadding=3>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="submit" value="$submit_label">
                    &nbsp;&nbsp;&nbsp;
                    <input type="reset" value="Reset">
                </td>
            </tr>
        </table>

        </form>

HERE;

    }

    echo head_standard_html_footer($authuser,$db);
