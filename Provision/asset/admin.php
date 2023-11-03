<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
17-Mar-03   EWB     Created.
19-Mar-03   NL      Include l-rcmd.php for debug_note().
24-Jun-03   EWB     Added link to debug home.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    ); 
include_once ( '../lib/l-sql.php'   );    
include_once ( '../lib/l-serv.php'  ); 
include_once ( '../lib/l-rcmd.php'  );    
include_once ( '../lib/l-head.php'  );    
include_once ( 'local.php'   );
include_once ( '../lib/l-user.php'  );

    
    function spewlink($url,$msg)
    {
        $link = "<a href='$url'>$msg</a>";
        $msg  = "<li>$link</li>\n";
        echo $msg;
    }


   /*
    |  Main program
    */
    
    $db = db_connect();
    $authuser = process_login($db);
    $title    = "Asset Admin";
    $comp     = component_installed();
    
    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer) 
    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);
        
    $now = time();

    $user  = user_data($authuser,$db);
    $debug = @ ($user['priv_debug'])? 1 : 0;
    $admin = @ ($user['priv_admin'])? 1 : 0;
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users  

    $date = datestring($now);
    echo "<h3>$date</h3>\n";

    echo "<ul>\n";
    spewlink('index.php','Asset Home');
    if ($admin)
    {
        spewlink('parent.php','Build Asset Hierarchy');
        spewlink('purge.php','Clear Asset Database');
        spewlink('build.php','Build Asset Database');
    }
    if ($debug)
    {
        spewlink('debug.php','Debug Pages');
        spewlink('../acct/index.php','Debug Home');
    }
    echo "</ul>\n";

    echo head_standard_html_footer($authuser,$db);
   
?>
