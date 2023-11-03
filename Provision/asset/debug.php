<?php

/*
Revision history:

Date        Who     What
----        ---     ----
21-Feb-03   EWB     Created
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
17-Mar-03   EWB     New asset admin page.
19-Mar-03   NL      Include l-rcmd.php for debug_note().
24-Jun-03   EWB     Debug home.
17-Oct-03   EWB     Added debug asset names.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

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

    $now      = time();
    $db       = db_connect();
    $authuser = process_login($db);
    $title    = 'Debug Asset';
    $comp     = component_installed();
    $msg      = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer)
    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);

    $user  = user_data($authuser,$db);

    $debug = @ ($user['priv_debug'])? 1 : 0;
    $admin = @ ($user['priv_admin'])? 1 : 0;

    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users
    $date = datestring($now);
    echo "<h3>$date</h3>\n";

    echo "<ul>\n";
    if (($debug) && ($admin))
    {
        spewlink('d-query.php','debug asset query');
        spewlink('d-report.php','debug asset report');
        spewlink('census.php','asset census');
        spewlink('names.php','asset names');
        spewlink('dupes.php','check for duplicates');
        spewlink('../acct/index.php','debug home');
    }
    spewlink('index.php','asset home');
    spewlink('admin.php','asset admin');
    echo "</ul>\n";

    echo head_standard_html_footer($authuser,$db);
?>
