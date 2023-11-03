<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navigation
10-Feb-03   EWB     Uses sandbox libraries.
21-Feb-03   EWB     Added debug pages.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header() 
17-Mar-03   EWB     Copy look & feel of main page.
17-Mar-03   EWB     Moved admin pages out of here.
19-Mar-03   NL      Include l-rcmd.php for debug_note().
 8-May-03   NL      Addet "asset change" link.
22-May-03   NL      Change link to plural: "asset changes".
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

    
    function bold($msg)
    {
        return "<b>$msg</b>";
    }

    function spewlink($url,$txt,$doc)
    {
        $msg  = bold($txt);
        $link = "<a href='$url'>$msg</a>";
        $msg  = "<li>$link$doc</li>\n";
        echo $msg;
    }


    // this is gross .. makes the entire contents into
    // a table.  oh well.

    function silly_table()
    {
        return '<table width="100%" border="0"><tr><td>';
    }
     
    function section($name)
    {
        $head = bold($name);
        return "<br>\n$head<br>\n";
    }

   /*
    |  Main program
    */
    
    $db = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();
    $title = "Asset Home";
    
    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer) 
    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);
        
    $now = time();

    $user  = user_data($authuser,$db);
    $debug = @ ($user['priv_debug'])? 1 : 0;
    $admin = @ ($user['priv_admin'])? 1 : 0;

    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users  

    echo silly_table();
    
    echo section('Asset Management');

    echo "<ul>\n";
    spewlink('query.php','queries',': manage and run queries.');
    spewlink('console.php','console',': manage assets.');
    spewlink('change.php','changes',': view changes to assets.');    
    spewlink('report.php','reports',': manage reports.');
    if ($debug)
    {
        spewlink('admin.php','admin',': admin commands.');
        spewlink('debug.php','debug',': debug commands.');
    }
    echo "</ul>\n";

    echo "</table>\n";

    echo head_standard_html_footer($authuser,$db);
?>
