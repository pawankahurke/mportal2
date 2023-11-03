<?php

/*
Revision history:

Date        Who     What
----        ---     ----
23-Oct-03   EWB     Created
17-Nov-03   EWB     Added the chunk stuff.
19-Sep-06   WOH     Bug 3657: Changed name html_footer.  Added username arg.

*/

   /*
    |  What's the point of this ... ?
    |
    |  We want to be able to tell if a browser times out.
    |  And if so, how long does it take?
    |
    |  So ... we create a script that takes arbitraily long to run.
    */

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-jump.php'  );
include_once ( '../lib/l-head.php'  );


    function again()
    {
        $self = server_var('PHP_SELF');
        $args = server_var('QUERY_STRING');
        $href = ($args)? "$self?$args" : $self;
        $c010 = "$self?chunk=10&wait";
        $c060 = "$self?chunk=60&wait";
        $c600 = "$self?chunk=600&wait";

        $a   = array();
        $a[] = html_link($href,'again');
        $a[] = html_link('../acct/index.php','home');
        $a[] = html_link('#top','top');
        $a[] = html_link('#bottom','bottom');
        $a[] = html_link("$c010=10",'10S');
        $a[] = html_link("$c010=30",'30S');
        $a[] = html_link("$c010=60",'60S');
        $a[] = html_link("$c010=120",'2M');
        $a[] = html_link("$c060=300",'5M');
        $a[] = html_link("$c060=600",'10M');
        $a[] = html_link("$c600=1200",'20M');
        $a[] = html_link("$c600=1800",'30M');
        $a[] = html_link("$c600=3600",'60M');
        $a[] = html_link("$c600=10800",'180M');
        return jumplist($a);
    }


    function message($msg)
    {
        $txt = "sleep: $msg";
        logs::log(__FILE__, __LINE__, $txt,0);
    }


    function sleeper($wait,$chunk,$silent)
    {
        $start = time();
        $pid = getmypid();
        $age = 0;
        debug_note("wait: $wait, silent:$silent");
        echo "We will be sleeping for $wait seconds ... <br>\n";
        message("waiting for for $wait seconds. ($pid)");
        if ($chunk > $wait) $chunk = $wait;
        while ($age < $wait)
        {
            sleep($chunk);
            $now  = time();
            $age  = $now - $start;
            $time = date('H:i:s',$now);
            $msg  = "$age seconds have elapsed ... ($pid)";
            message($msg);
            if (!$silent)
            {
                echo  "$time: $msg<br>\n\n";
                flush();
            }
        }
        message("wait of $wait seconds finished. ($pid)");
        echo "<br><br>All done.<br>\n\n";
    }



   /*
    |  Main program
    */

    $now    = time();
    $pid    = getmypid();
    $db     = db_connect();
    $auth   = getenv("REMOTE_USER"); // cron does not use PHP auth'n
    $comp   = component_installed();
    $debug  = get_integer('debug',1);
    $wait   = get_integer('wait',60);
    $chunk  = get_integer('chunk',10);
    $silent = get_integer('silent',1);
    $title  = "Cron Sleep ($pid)";
    $msg    = ob_get_contents();           // save the buffered output so we can...

    ob_end_clean();                     // (now dump the buffer)
    echo standard_html_header($title,$comp,$auth,0,0,0,$db);
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    set_time_limit(0);
    echo again();
    sleeper($wait,$chunk,$silent);
    echo again();
    echo head_standard_html_footer($auth,$db);
