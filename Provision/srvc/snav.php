<?php

/* dnav.php: PHP access to DNAV (dashboard navigation). */

/*
Revision history:

Date        Who     What
----        ---     ----
07-Apr-06   AAM     Initial creation.
14-Aug-07   WOH     Modified to create services screen on dashboard.

*/

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
    $userName = process_login($db);
    $htmlRoot = '/' . server_root() . '/';
    $info     = asi_info();

    /* Note that the dashboard display generates its own headers. */
    if(isset($GLOBALS["HTTP_RAW_POST_DATA"]))
    {
        $err = PHP_SNAV_DisplayServices(CUR, $html, server_var('QUERY_STRING'),
            $GLOBALS["HTTP_RAW_POST_DATA"], $userName, $htmlRoot,
            $info['svvers'], $info['svdate']);
    }
    else
    {
        $err = PHP_SNAV_DisplayServices(CUR, $html, server_var('QUERY_STRING'),
            NULL, $userName, $htmlRoot, $info['svvers'], $info['svdate']);
    }

    if($err!=constAppNoErr)
    {
        echo "<html><head><title>Dashboard Failure</title></head><body>\n";
        echo "An error has occurred processing this page.";
        echo "</body>\n";
    }
    else
    {
        echo $html;
    }

?>
