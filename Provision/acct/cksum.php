<?php

/*
Revision History

11-Aug-13   BTE     Creation.

*/


    $title = 'Configuration Checksum Health';

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-ebld.php'  );
include_once ( '../lib/l-abld.php'  );
include_once ( '../lib/l-core.php'  );
include_once ( '../lib/l-rlib.php'  );
include_once ( '../lib/l-jump.php'  );
include_once ( '../lib/l-cbld.php'  );
include_once ( '../lib/l-cnst.php'  );
include_once ( '../lib/l-errs.php'  );
include_once ( '../lib/l-gsql.php'  );
include_once ( '../lib/l-dsyn.php'  );
include_once ( '../lib/l-gcfg.php'  );
include_once ( '../lib/l-grps.php'  );
include_once ( '../lib/l-tabs.php'  );
include_once ( '../lib/l-gdrt.php'  );
include_once ( '../lib/l-pdrt.php'  );
include_once ( '../lib/l-ptch.php'  );
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-repf.php'  );


    // note this is not the same as the library
    // redcommand.

    function redcommand($sql,$db)
    {
        $real  = real_command();
        $msg   = str_replace("\n","<br>\n&nbsp;&nbsp;&nbsp;",$sql);
        $color = ($real)? 'red' : 'blue';
        $msg   = "<font color='$color'>$msg</font><br>\n";
        if ($real)
        {
            $res = command($sql,$db);
            if (!$res)
            {
                $error = mysqli_error($db);
                $errno = mysqli_errno($db);
                $msg  .= "$errno:$error<br>\n";
            }
        }
        else
        {
            /* While simulating, pretend every command works. */
            $res = "fake success";
        }
        echo fontspeak("command: $msg<br>");
        return $res;
    }


   /*
    |  Main program
    */

    $db  = db_pconnect();
    if ($db)
    {
        if (! mysqli_select_db($db, core))
        {
            $msg  = "The database is currently unavailable.  ";
	    $msg .= "Please try again later.";
	    $msg = fontspeak("<p><b>$msg</b></p>");
	    die ($msg);
        }
    }
    else
    {
        $msg  = "The database is currently unavailable.  ";
        $msg .= "Please try again later.";
        $msg = fontspeak("<p><b>$msg</b></p>");
        die ($msg);
    }

    $authuser = process_login($db);
    $comp = component_installed();
    $user   = user_data($authuser,$db);
    $getrepair = get_integer('dorepair',constDSYNRepairCheckOnly);

    /* Allow plenty of time and ignore client disconnects */
    set_time_limit(0);
    ignore_user_abort(true);

    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer)
    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    if(!$user['priv_admin'])
    {
      die('Admin access required to use this page');
    }

    $self    = server_var('PHP_SELF');
    $date    = datestring(time());

    echo "<h2>$date</h2>\n";
    
    if($getrepair==constDSYNRepairCheckOnly)
    {
      echo "Checking the checksum health for configuration...<br><br>\n";
      echo "Click <a href=\"cksum.php?dorepair=" . constDSYNRepairAll
	. "\">here</a> to repair (only "
	. "necessary if any rows below are reported corrupt).<br><br>\n";
    }
    else
    {
      echo "Repairing checksums for configuration....<br><br>\n";
      echo "Click <a href=\"cksum.php?dorepair=" . constDSYNRepairCheckOnly
	. "\">here</a> to check for corruption again (recommended after a "
	. "repair, it may take multiple repairs as levels are fixed from "
	. "bottom to top).<br><br>\n";
    }
    
    $err = PHP_DSYN_RepairChecksums(CUR, $report, constAggregateSetGConfig,
      $getrepair);
    if($err!=constAppNoErr)
    {
        logs::log(__FILE__, __LINE__, "update: PHP_DSYN_RepairChecksums returned " . $err,
            0);
        echo "An error occurred repairing the configuration checksums.";
    }
    else
    {
        echo $report;
    }

    echo head_standard_html_footer($authuser,$db);
