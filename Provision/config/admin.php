<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 6-Aug-02   EWB     Cleaned up some php warnings.
 7-Aug-02   EWB     Changed Delete Customer to Delete Site.
15-Aug-02   EWB     Only show debug links to user with debug priv
15-Aug-02   EWB     Update filenames for php4
15-Aug-02   EWB     Use formal start script tags
19-Aug-02   EWB     8.3 filenames for debug pages
22-Aug-02   EWB     Changed "Host" to "Machine"
22-Aug-02   EWB     Debug link for deleting browser interface.
23-Aug-02   EWB     Added link for purge and rebuild.
28-Aug-02   EWB     We call them "Machines" now ...
28-Aug-02   EWB     Log all mysql failures
28-Aug-02   EWB     Title change for Alex.
 3-Sep-02   EWB     Another title change for Alex.
 9-Sep-02   EWB     Link to select machine page.
20-Sep-02   EWB     Giant refactoring
25-Nov-02   EWB     Implement new priv_config.
 4-Dec-02   EWB     Reorginization Day
10-Dec-02   EWB     Uses standard header
13-Jan-03   EWB     Removed 'Home' link.
13-Jan-03   EWB     Don't require register_globals
13-Jan-03   EWB     Another title change.
16-Jan-03   EWB     Database is now a debug command.
16-Jan-03   EWB     Delete site now requires cid, not hid.
20-Jan-03   AAM     Removed references to crevl.
11-Feb-03   EWB     Uses sandbox libraries.
11-Feb-03   EWB     db_change()
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
 6-Mar-03   NL      Add calls to config_navigate() & config_info()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
11-Dec-03   EWB     Link back to debug home.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

    $title = 'Site Administration';

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( 'local.php'   );
include_once ( '../lib/l-user.php'  );


    function link_element($url,$text)
    {
        $text = fontspeak($text);
        $msg  = "<a href='$url'>$text</a>";
        $msg  = "<li>$msg</li>\n";
        return $msg;
    }

    function cust_array($authuser,$db)
    {
        $arr  = array( );
        $sql  = "select * from Customers where";
        $sql .= " username = '$authuser'";
        $sql .= " order by customer";
        $res  = command($sql,$db);
        if ($res)
        {
            if (mysqli_num_rows($res) > 0)
            {
                while ($row = mysqli_fetch_assoc($res))
                {
                    $cid  = $row['id'];
                    $name = $row['customer'];
                    $arr[$name] = $cid;
                }
            }
        }
        return $arr;
    }


   /*
    |  Main program
    */

    $db = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();

    $hid  = intval(get_argument('hid',0,0));
    $sid  = 0;
    $cid  = 0;
    $host = '';
    $cust = '';
    $vers = '';
    $good = 0;
    $list = find_customers($authuser,$db);
    $user = user_data($authuser,$db);
    $carr = cust_array($authuser,$db);

    db_change($GLOBALS['PREFIX'].'siteman',$db);
    if ($hid)
    {
        $sql = "select * from Revisions where id = $hid and cust in ($list)";
        $res = command($sql,$db);
        if ($res)
        {
            if (mysqli_num_rows($res) == 1)
            {
                $row   = mysqli_fetch_assoc($res);
                $cust  = $row['cust'];
                $host  = $row['host'];
                $vers  = $row['vers'];
                $good  = 1;
                $uhost = ucwords($host);
                $title = "$uhost Machine Administration";
                if (safe_count($carr) > 1)
                {
                    if (isset($carr[$cust]))
                    {
                        $cid = $carr[$cust];
                    }
                }
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    }

    $local_nav = config_navigate($cid,$hid,$sid);
    $local_inf = config_info($authuser,$vers,$host);

    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer)
    db_change($GLOBALS['PREFIX'].'core',$db);
    echo standard_html_header($title,$comp,$authuser,$local_nav,$local_inf,0,$db);
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    $priv_debug  = @ ($user['priv_debug'])?  1 : 0;
    $priv_admin  = @ ($user['priv_admin'])?  1 : 0;
    $priv_config = @ ($user['priv_config'])? 1 : 0;

    echo "<ul>\n";

    if ($cid)
    {
        echo link_element("index.php?cid=$cid",'Machines');
        echo link_element("del-cust.php?cid=$cid",'Delete Site');
    }
    if ($good)
    {
        echo link_element("config.php?hid=$hid",'Scrips');
        if ($priv_config)
        {
            echo link_element("del-host.php?hid=$hid",'Delete Machine');
        }
    }

    if ($priv_debug)
    {
        echo link_element('list-var.php','Debug Variables');
        echo link_element('list-dsc.php','Debug Descriptions');
        echo link_element('list-scp.php','Debug Scrips');
        echo link_element('census.php','Census');
        echo link_element('../acct/index.php','Home');
        if ($good)
        {
            echo link_element("database.php?hid=$hid",'Dump Database');
        }
    }

    if ($priv_admin)
    {
        echo link_element("purge.php?hid=$hid",'Purge Database');
        echo link_element("rebuild.php?hid=$hid",'Rebuild Database');
    }

    echo "</ul><br><br>\n";

    if (!$good)
    {
        $msg = "No machine specified.";
        $msg = fontspeak($msg);
        echo "<p>$msg</p>\n";
    }

    echo head_standard_html_footer($authuser,$db);
?>
