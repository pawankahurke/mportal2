<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
21-Feb-03   EWB     Created
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
14-Apr-03   NL      Move debug_note line below $debug.
 6-Jun-03   EWB     Added Purge.
19-Jun-03   EWB     Debug Home.
28-Oct-04   EWB     Removed purge (it was defunct anyway)
19-Sep-06   WOH     Bug 3657: Changed name html_footer.  Added username arg.                    

*/


    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    ); 
include_once ( '../lib/l-sql.php'   );    
include_once ( '../lib/l-serv.php'  );  
include_once ( '../lib/l-rcmd.php'  );       
include_once ( '../lib/l-head.php'  );
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
    $comp = component_installed();
    $now   = time();
    $user  = user_data($authuser,$db);
    $debug = @ ($user['priv_debug'])? 1 : 0;
    $admin = @ ($user['priv_admin'])? 1 : 0;          
    $title = 'Debug Event';    
    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer) 

    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users  

    $date = datestring($now);
    echo "<h3>$date</h3>\n";
    
    echo "<ul>\n";
    if (($debug) && ($admin))
    {
        $cmd = 'notify.php?act=queu';
        spewlink('d-search.php','debug event filter');
        spewlink('d-report.php','debug event report');
        spewlink($cmd,          'debug event notify');
        spewlink('d-cnsole.php','debug notify console');
        spewlink('dupes.php','check for duplicates');
        spewlink('../acct/index.php','debug home');
    }
    spewlink('index.php','event home');
    spewlink('../acct/admin.php','admin');
    echo "</ul>\n";
    echo head_standard_html_footer($authuser,$db);
?>
