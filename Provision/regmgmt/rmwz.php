<?php

/* rmwz.php: PHP access to all the wizard pages of the registry
    management interface.
/*
Revision history:

Date        Who     What
----        ---     ----
09-Feb-2007 AAM     Created from rmgt.php.
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

    /* Note that the Registry Manager generates its own headers. */
    if(isset($GLOBALS["HTTP_RAW_POST_DATA"]))
    {
        $err = PHP_RMGT_Wizard(CUR, $html, server_var('QUERY_STRING'),
                   $GLOBALS["HTTP_RAW_POST_DATA"], $userName, $htmlRoot,
                   $info['svvers'], $info['svdate']);
    }
    else
    {
        $err = PHP_RMGT_Wizard(CUR, $html, server_var('QUERY_STRING'),
	     NULL, $userName, $htmlRoot, $info['svvers'], $info['svdate']);
    }

    if($err!=constAppNoErr)
    {
        echo "<html><head><title>Registry Managment Failure</title></head><body>\n";
        echo "An error has occurred processing this page.";
        echo "</body>\n";
    }
    else
    {
        echo $html;
    }

?>
