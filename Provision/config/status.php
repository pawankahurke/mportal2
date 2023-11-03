<?php

/* status.php - Scrip Configuration Action Status pages */

/*
Revision history:

Date        Who     What
----        ---     ----
27-Apr-06   BTE     Original creation.
01-May-06   BTE     Bug 3355: Scrip Config Status Page: Various Changes.
19-Sep-06   WOH     Bug 3657: Changed name html_footer.  Added username arg.

*/

    $title = 'Scrip Configuration Action Status';

include_once ( '../lib/l-cnst.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-errs.php'  );
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-user.php'  );

    function log_error($err)
    {
        if($err!=constAppNoErr)
        {
            echo "<html><head><title>Selection Page Failure</title></head>"
                . "<body>";
            echo "\nAn error has occurred processing this page.  See ";
            echo "<a href=\"../acct/csrv.php?error\">errlog.txt</a>.\n";
            echo "</body>\n";
        }
    }

    /* Perform authentication */
    $db       = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();

    $user     = user_data($authuser,$db);

    $dir = $comp['odir'];
    $a   = array( );
    $a[] = html_link("index.php?act=wiz", 'wizards');
    $a[] = html_link("status.php", 'status');
    $a[] = html_link("status.php?level=1", 'advanced');
    $m   = join(' | ',$a);
    $nav = "<b>configuration:</b> $m\n<br><br>\n";

    $level = get_integer('level', 0);

    $tableID = constTableIDValueMap;
    if($level==1)
    {
        $tableID = constTableIDValueMapAdv;
    }

    echo standard_html_header($title,$comp,$authuser,$nav,0,0,$db);

    $set = 1;
    $sort = 1;
    if(server_var('QUERY_STRING'))
    {
        if(strpos(server_var('QUERY_STRING'), "set")===false)
        {
            $set = 0;
        }
        else if(strpos(server_var('QUERY_STRING'), "sort")===false)
        {
            $sort = 0;
        }
    }
    else
    {
        $set = 0;
        $sort = 0;
    }

    $displayFull = 1;
    if(($set) || ($sort))
    {
        $err = PHP_HTML_StoreSearchOptions(CUR,
            isset($GLOBALS["HTTP_RAW_POST_DATA"]) ?
            $GLOBALS["HTTP_RAW_POST_DATA"] : NULL, $tableID,
            $user['username'], server_var('QUERY_STRING'));
        log_error($err);
    }

    if($displayFull)
    {
        $err = PHP_CSRV_GetTable(CUR, $html, server_var('QUERY_STRING'),
            $tableID, NULL, $user['username'], 
            ($level==1) ? "status.php?level=1&set=1" : "status.php?set=1",
            ($level==1) ? "status.php?level=1&sort=" : "status.php?sort=",
            ($level==1) ? "status.php?level=1" : "status.php?");
        log_error($err);
    }

    echo $html;

    echo head_standard_html_footer($authuser,$db);

?>
