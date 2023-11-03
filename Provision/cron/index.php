<?php

/*
Revision history:

Date        Who     What
----        ---     ----
12-Dec-02   EWB     Created
10-Feb-03   EWB     Uses sandbox libraries
 6-Mar-03   NL      Uses output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/


    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-head.php'  );


   /*
    |  Main program
    */
    $refreshtime = '';
    $title       = "Cron Home";

    $db = db_connect();
    $authuser = getenv("REMOTE_USER"); // cron does not use PHP auth'n
    $comp = component_installed();

    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer)
    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    $now = time();
    $date = datestring($now);
    echo "<h3>$date</h3>\n";

    echo head_standard_html_footer($authuser,$db);
