<?php

/*
Revision history:

Date        Who     What
----        ---     ----
10-Dec-02   EWB     Created
16-Jan-03   EWB     Don't require register_globals
10-Feb-03   EWB     Uses asset datbase.
10-Feb-03   EWB     Uses sandbox libraries.
11-Feb-03   EWB     db_change()
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
19-Mar-03   NL      Move debug_note line below $debug.
29-Apr-03   EWB     priv_asset controls machine removal.
17-Oct-03   EWB     log when the machine was removed and who did it.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

    $title  = 'Delete Machine';

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( 'local.php'   );

    function colorspeak($size,$color,$msg)
    {
        $face = 'verdana,helvetica';
        return "<font face=\"$face\" color=\"$color\" size=\"$size\">$msg</font>";
    }

    function newline()
    {
        echo "<br>\n";
    }


    function clear_machine($mid,$db)
    {
        $sql1 = "delete from Machine where machineid = $mid";
        $sql2 = "delete from AssetData where machineid = $mid";
        redcommand($sql1,$db);
        redcommand($sql2,$db);
    }


   /*
    |  Main program
    */

    $db = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();

    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer)
    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);

    $mid = intval(get_argument('mid',0,0));
    $yes = intval(get_argument('yes',0,0));
    $dbg = intval(get_argument('debug',0,1));

    $host = '';
    $cust = '';

    $user = user_data($authuser,$db);
    $list = find_customers($authuser,$db);

    db_change($GLOBALS['PREFIX'].'asset',$db);
    $sql = "select * from Machine where machineid = $mid and cust in ($list)";
    $res = command($sql,$db);
    if ($res)
    {
        if (mysqli_num_rows($res) == 1)
        {
            $row  = mysqli_fetch_array($res);
            $cust = $row['cust'];
            $host = $row['host'];
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    $priv_debug = @ ($user['priv_debug'])? 1 : 0;
    $priv_asset = @ ($user['priv_asset'])? 1 : 0;

    $file = $comp['file'];
    $self = $comp['self'];

    $debug = ($priv_debug)? $dbg : 0;

    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    $test_sql = ('del-host.php' == $file)? 0 : 1;


    debug_note("debug:$debug mid:$mid yes:$yes host:$host test:$test_sql");

    newline();
    newline();
    if (($host) && ($cust) && ($priv_asset))
    {
        if ($yes)
        {
            clear_machine($mid,$db);
            $msg = "assets: $authuser has removed machine $host from $cust";
            debug_note($msg);
            logs::log(__FILE__, __LINE__, $msg,0);
            $have = ($test_sql)? 'would have' : 'have';
            $msg  = "Records for machine $host $have been ";
            $msg .= "removed from the database.<br>";
            $msg .= '<br>';
            $msg .= "<br>\n";
        }
        else
        {
            $y    = "$self?mid=$mid&yes=1";
            $n    = "index.php";
/*
 |  This will remove all the machine specific values from the asset
 |  database for the machine dupont.  However this will not affect
 |  other machines at the HFN Development site.
 */
            $msg  = "This will remove all the machine specific values";
            $msg .= " from the asset<br>database for the machine $host.";
            $msg .= " &nbsp;&nbsp;However this will not affect<br>other";
            $msg .= " machines at the $cust site.<br>";
            $msg .= "<br>";
            $msg .= "Delete records for machine $host?<br>";
            $msg .= "<br>";
            $msg .= "<a href='$y'>Yes, delete $host.</a><br>";
            $msg .= "<br>";
            $msg .= "<a href='$n'>No, don't do anything.</a><br>";
        }
        $msg = colorspeak(2,'black',$msg);
    }
    else
    {
        if ($priv_asset)
            $msg = "No machine specified to be deleted.";
        else
            $msg = "Authorization denied.";
        $msg = colorspeak(3,'red',$msg);
    }

    echo "<p>$msg</p>\n";

    newline();
    newline();

    echo head_standard_html_footer($authuser,$db);
