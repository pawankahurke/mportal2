<?php

/* csrv.php: A wrapper around the csrv.cgi program. */

/*
Revision history:

Date        Who     What
----        ---     ----
18-Oct-05   BTE     Original creation.
09-Jan-06   BTE     Updated with uniform header, send post data to CSRV_Main.

*/

    $title = 'CSRV'; 

include_once ( '../lib/l-cnst.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-errs.php'  );
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-user.php'  );

    /* Perform authentication */
    $db       = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();

    $user     = user_data($authuser,$db);
    $csrvRight = @ ($user['priv_csrv'])? 1 : 0;
    $auditRight = @ ($user['priv_audit'])? 1 : 0;

    $dir = $comp['odir'];
    $p   = "/$dir/acct";
    $a   = array( );
    if($auditRight)
    {
        $a[] = html_link("$p/csrv.php?auditlog", 'audit log');
        $a[] = html_link("$p/csrv.php?audit", 'audit control');
    }
    if($csrvRight)
    {
        $a[] = html_link("$p/csrv.php?events", 'events');
        $a[] = html_link("$p/csrv.php?error", 'ASI errorlog');
    }
    $m   = join(' | ',$a);
    $nav = "$m\n<br><br>\n";

    echo standard_html_header($title,$comp,$authuser,$nav,0,0,$db);

    /* And simply run CSRV_Main directly */
    if(isset($GLOBALS["HTTP_RAW_POST_DATA"]))
    {
        $err = PHP_CSRV_Main(CUR, $html, server_var('QUERY_STRING'),
            $GLOBALS["HTTP_RAW_POST_DATA"], FALSE, $user['username']);
    }
    else
    {
        $err = PHP_CSRV_Main(CUR, $html, server_var('QUERY_STRING'),
            NULL, FALSE, $user['username']);
    }
    if($err!=constAppNoErr)
    {
        echo "<html><head><title>CSRV Failure</title></head><body>\n";
        echo "An error has occurred processing this page.  See ";
        echo "<a href=\"csrv.php?error\">errlog.txt</a>.\n";
        echo "</body>\n";
    }
    else
    {
        echo $html;
    }

?>
