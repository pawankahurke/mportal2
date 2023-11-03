<?php

/* fmgt.php: PHP access to FMGT (file management configuration). */

/*
Revision history:

Date        Who     What
----        ---     ----
01-Apr-06   AAM     Initial creation.

*/

    $title = 'File management configuration'; 

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

    /* Should add dashboard navigation-bar code to set $nav here. */
    $nav = '';

    echo standard_html_header($title,$comp,$authuser,$nav,0,0,$db);

    /* Run the DDSP wizard. */
    if(isset($GLOBALS["HTTP_RAW_POST_DATA"]))
    {
        $err = PHP_DDSP_Wizard(CUR, $html, server_var('QUERY_STRING'),
            $GLOBALS["HTTP_RAW_POST_DATA"]);
    }
    else
    {
        $err = PHP_FMGT_Wizard(CUR, $html, server_var('QUERY_STRING'), NULL);
    }
    if($err!=constAppNoErr)
    {
        echo "<html><head><title>$title Failure</title></head><body>\n";
        echo "An error has occurred processing this page.";
        echo "</body>\n";
    }
    else
    {
        echo $html;
    }

?>
