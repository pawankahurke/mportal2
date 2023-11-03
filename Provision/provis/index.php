<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
20-Oct-03   EWB     Created.
 2-Jan-04   EWB     moved update to main/acct
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
 
*/

    $refreshtime = '';

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)  
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    ); 
include_once ( '../lib/l-sql.php'   );    
include_once ( '../lib/l-serv.php'  ); 
include_once ( '../lib/l-rcmd.php'  );        
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( 'local.php'  );
    
    function ident($now)
    {
        $date = datestring($now);
        $info = asi_info();
        $version = $info['svvers'];
        return "\n<h2>$date ($version)</h2>\n";
    }

    
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
    
    $user  = user_data($authuser,$db);
    $admin = ($user['priv_admin'])? 1 : 0;
    $debug = ($user['priv_debug'])? 1 : 0;        
    $title = server_name($db);
    
    $nav = provis_navigate();
    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer) 
    echo standard_html_header($title,$comp,$authuser,$nav,0,0,$db);
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    $now = time();

    echo ident($now);

    $self = server_var('PHP_SELF');

    echo "<ul>\n";
    spewlink('product.php','Products');
    spewlink('sites.php','Sites');
    spewlink('audit.php','Audit');
    spewlink('meter.php','Meter');
    if (($debug) && ($admin))
    {
        spewlink('../acct/index.php','Debug Home');
    }

    if ($comp['asst'])
    {
        spewlink('../asset/index.php','Asset Module');
    }
    if ($comp['evnt'])
    {
        spewlink('../event/index.php','Event Module');
    }
    echo "</ul>\n\n\n";

    echo ident($now);

    echo head_standard_html_footer($authuser,$db);
?>
