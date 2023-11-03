<?php

/*

Revision History

Date        Who     What
------      ---     ----
03-Jun-05   BJS     Created.
06-Jun-05   BJS     Added revision history, standard_html_footer().
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-cnst.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-gsql.php'  );
include_once ( '../lib/l-jump.php'  );
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-form.php'  );
include_once ( '../lib/l-tabs.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-csum.php'  );
include_once ( '../lib/l-cwiz.php'  );

/*
    Main program

    this file us used on all servers except microdata
    Its sets the translated field in AssetReports, 
    AssetSearchCriteria, and AssetSeaches to 1 
*/

    $now = time();
    $db  = db_connect();
    $authuser = process_login($db);
    $comp  = component_installed();
    $title = 'Translator';
    
    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);

    $dbg  = (get_argument('debug',0,1))?   1 : 0;

    $user = user_data($authuser,$db);

    $priv_debug = @ ($user['priv_debug'])? 1 : 0;
    $priv_admin = @ ($user['priv_admin'])? 1 : 0;

    $debug = ($priv_debug)? $dbg : 0;

    db_change($GLOBALS['PREFIX'].'asset',$db);

    $sql = "update AssetReports set translated = 1\n"
         . " where translated = 0";
    redcommand($sql,$db);

    $sql = "update AssetSearches set translated = 1\n"
         . " where translated = 0";
    redcommand($sql,$db);

    $sql = "update AssetSearchCriteria set translated = 1\n"
         . " where translated = 0";
    redcommand($sql,$db);

    echo head_standard_html_footer($authuser,$db);
?>
