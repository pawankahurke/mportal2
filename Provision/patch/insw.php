<?php

/* insw.php - MUM Intelligent Status Wizard */

/*
Revision history:

Date        Who     What
----        ---     ----
11-Oct-06   BTE     Original creation.
21-Oct-06   BTE     Bug 2813: Microsoft Update Management: Add intelligent
                    status page.

*/

include_once ( '../lib/l-cnst.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-errs.php'  );
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-user.php'  );
include_once ( 'local.php'          );

    /* Perform authentication */
    $db       = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();
    $nav  = patch_navigate($comp);

    $user     = user_data($authuser,$db);
    $csrvRight = @ ($user['priv_csrv'])? 1 : 0;
    $auditRight = @ ($user['priv_audit'])? 1 : 0;

    /* And simply run CSRV_Main directly */
    if(isset($GLOBALS["HTTP_RAW_POST_DATA"]))
    {
        $err = PHP_INSW_Wizard(CUR, $html, $title, server_var('QUERY_STRING'),
            $GLOBALS["HTTP_RAW_POST_DATA"], $user['username']);
    }
    else
    {
        $err = PHP_INSW_Wizard(CUR, $html, $title, server_var('QUERY_STRING'),
            NULL, $user['username']);
    }

    if(!($title))
    {
        $title = 'CSRV';
    }

    echo standard_html_header($title,$comp,$authuser,$nav,0,0,$db);

    if($err!=constAppNoErr)
    {
        echo "An error has occurred processing this page.  See ";
        echo "<a href=\"..\acct\csrv.php?error\">errlog.txt</a>.\n";
    }
    else
    {
        echo $html;
    }

    echo head_standard_html_footer($authuser,$db);
?>
