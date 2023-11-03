<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 1-Aug-02   EWB     Supports ssl override.
 2-Aug-02   EWB     Gave this page a real title.
30-Aug-02   EWB     Need db.php3 for server version number.
12-Sep-02   EWB     Added asset cron.
27-Sep-02   EWB     New asset Cron
27-Sep-02   EWB     No more fontspeak
11-Oct-02   EWB     Regular names for cron functions.
11-Oct-02   EWB     Asset functions added to menu.
15-Oct-02   EWB     Cilog Special
27-Nov-02   EWB     Census
 3-Feb-03   EWB     Server Options
17-Feb-03   EWB     Uses Sandbox libraries.
24-Feb-03   EWB     debug pages moved to asset/event.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
19-Mar-03   NL      Move $debug initialization and surrounding lines above ob_ lines
19-Mar-03   NL      Include l-rcmd.php for debug_note().
28-Mar-03   EWB     Updated census link.
19-May-03   EWB     Added logfile.
20-May-03   EWB     Server ident.
 2-Jun-03   EWB     mysql Information.
16-Jun-03   EWB     Don't try to test cron pages unless we are in a sandbox.
16-Jun-03   EWB     Enter/Exit Sandbox.
16-Jun-03   EWB     Anyone can use the census.
 8-Jul-03   EWB     Added link for uglobal.php
17-Jul-03   EWB     File Output.
19-Aug-03   EWB     Knowing about installed modules.
 8-Sep-03   EWB     "Cron Purge", etc.
11-Sep-03   EWB     Debug option for uglobal, etc.
30-Oct-03   EWB     sandbox link for ../provis
 9-Dec-03   EWB     scrip cache
11-Dec-03   EWB     direct link to config and asset census.
18-Dec-03   EWB     removed sandbox provision link.
 9-Jan-04   EWB     Server Name.
16-Feb-04   EWB     server_name variable.
 3-Mar-04   EWB     sandbox link to patch directory
16-Apr-04   EWB     Server Status
 6-May-04   EWB     Machine Groups.
15-Oct-04   EWB     Show numbers, it's easier.
 6-Jan-06   EWB     Export Wizard Link.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.                    
 
*/

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-jump.php'  );
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-head.php'  );

    function ident($now,$version)
    {
        $date = datestring($now);
        return "\n<h2>$date ($version)</h2>\n";
    }


    function spewlink($url,$msg)
    {
        $link = "<a href='$url'>$msg</a>";
        $msg  = "<li>$link</li>\n";
        echo $msg;
    }

    function again()
    {
        $self = server_var('PHP_SELF');
        $args = server_var('QUERY_STRING');
        $href = ($args)? "$self?$args" : $self;
        $dbg  = 'php?debug=1';

        $a   = array();
        $a[] = html_link($href,'again');
        $a[] = html_link('#top','top');
        $a[] = html_link('#bottom','bottom');
        $a[] = html_link('index.php','home');
        $a[] = html_link('status.php','status');
        $a[] = html_link('server.php','server');
        $a[] = html_link('census.php','census');
        $a[] = html_link('admin.php','admin');
        return jumplist($a);
    }


   /*
    |  Main program
    */

    $now = time();
    $db = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();

    $user   = user_data($authuser,$db);
    $admin  = ($user['priv_admin'])? 1 : 0;
    $debug  = ($user['priv_debug'])? 1 : 0;
    $server = server_name($db);
    $info   = asi_info();
    $vers   = $info['svvers'];
    $title  = "$server ($vers)";

    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer)
    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users


    echo again();
    echo ident($now,$vers);


    $self    = server_var('PHP_SELF');
    $sandbox = ($self == '/main/acct/index.php')? 0 : 1;
    $hfndev  = ($server == 'hfndev.com')? 1: 0;

    echo "<ol>\n";
    if ($comp['asst'])
    {
        spewlink('../asset/index.php','Asset Module');
    }
    if ($comp['evnt'])
    {
        spewlink('../event/index.php','Event Module');
    }
    spewlink('admin.php','Admin');
    spewlink('info.php','Information');
    spewlink('d-graph.php','Test Graphics');
    spewlink('update.php','Update');
    spewlink('census.php','Census');
    spewlink('files.php','File Manager');
    spewlink('export.php','Export Wizard');
    if ($admin)
    {
        spewlink('status.php','Server Status');
        spewlink('server.php','Server Options');
        spewlink('d-users.php','User List');
        spewlink('perf.php','RPC Performance Counters');
        spewlink('mysql.php','mysql Information');
        if (($debug) && ($admin))
        {
            if ($comp['evnt'])
            {
                spewlink('../event/debug.php','Event Debug');
                spewlink('cache.php','Scrip Cache');
            }
            if ($comp['asst'])
            {
                spewlink('../asset/admin.php','Asset Admin');
                spewlink('../asset/debug.php','Asset Debug');
                spewlink('../asset/census.php','Asset Census');
            }
            if ($comp['cnfg'])
            {
                spewlink('../config/admin.php','Config Admin');
                spewlink('../config/census.php','Config Census');
                spewlink('gconfig.php','Config Convert');
            }
            if ($comp['updt'])
            {
                spewlink('../updates/census.php','Update Census');
            }
            spewlink('logfile.php','Show Logfile');
            spewlink('fake.php','Fake Client');
            if ($comp['inst'])
            {
                spewlink('../install/index.php','Install Module');
            }
            if ($sandbox)
            {
                spewlink('../cron/c-sanity.php','Sanity Check');
                spewlink('../cron/c-purge.php?debug=1','Cron Purge');
                if ($comp['evnt'])
                {
                    spewlink('../cron/c-report.php?debug=1','Cron Report');
                    spewlink('../cron/c-notify.php?debug=1','Cron Notify');
                }
                if ($comp['asst'])
                {
                    spewlink('../cron/c-asset.php?debug=1','Cron Asset');
                }
                spewlink('../patch/index.php','Patch');
                spewlink('../../docs/index.html','Engr Docs');
                spewlink('/main/acct/index.php','Exit Sandbox');
            }
            else
            {

               /*
                |  are we getting a little lazy?
                |
                |  probably.
                */

                if ($hfndev)
                {
                    spewlink('/engr/index.html','HFN Engineering Website');
                    spewlink('/eric/dev/server/acct/index.php','Eric Sandbox');
                    spewlink('/amiller/dev/server/acct/index.php','AAM Sandbox');
                    spewlink('/nlauderdale/dev/server/acct/index.php','Nina Sandbox');
                    spewlink('/bscro/dev/server/acct/index.php','BJ Sandbox');
                    spewlink('/max/dev/server/acct/index.php','Max Sandbox');
                }
            }
        }
        spewlink('uglobal.php?debug=1','Global Items');
    }
    echo "</ol>\n\n\n";

    echo ident($now,$vers);
    echo again();
    echo head_standard_html_footer($authuser,$db);
?>
