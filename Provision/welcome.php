<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navagation
10-Feb-03   EWB     Uses sandbox libraries.
26-Feb-03   EWB     Copies look of 3.0.
26-Feb-03   EWB     Removed sublinks for updates section.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Feb-03   NL      Added "lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header() 
19-Mar-03   NL      Move debug_note line below $debug.
31-Mar-03   EWB     Added census link.
 8-May-03   NL      Addet "asset change" link.
22-May-03   NL      Change link to plural: "asset changes".  
17-Jul-03   EWB     Added files link.
28-Jul-03   EWB     Changed name of link from 'files' to 'information portal';
 6-Aug-03   EWB     Moved file manager into it's own section
 8-Aug-03   EWB     Changed file manager arguments.
17-Dec-03   EWB     Added provisioning
11-Jun-04   EWB     Groups page.
18-Jan-05   AAM     Modified wording as per Alex.
20-Jan-05   AAM     Added Microsoft Update links, and put in same order as
                    page menu.
01-Feb-05   AAM     Added wizard item to Site Configuration.
09-Jan-06   BTE     Added audit link under tools.
14-Jun-06   AAM     Implemented new, friendlier layout.
31-Jul-06   AAM     Moved from index.php to welcome.php because the "main" page
                    is now the dashboard.  Also, updated some links.
28-Aug-06   AAM     Added the "Manage groups" link.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.
27-Jun-07   BTE     Bug 4189: Add classic report links and page.

*/


    $title = 'Welcome - What would you like to do?'; 

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)      
include_once ( 'lib/l-util.php' ); 
include_once ( 'lib/l-db.php'   ); 
include_once ( 'lib/l-sql.php'  ); 
include_once ( 'lib/l-user.php' );
include_once ( 'lib/l-serv.php' );
include_once ( 'lib/l-head.php' );   

    
    /* Generate a link for the welcome page.  Only generate the link if $cond
        is true.  $name is the visible name of the link, and $link is the
        actual link that is generated. */
    function welcome_link($cond, $name, $link)
    {
        if ($cond)
        {
            echo
                "<li><a href=\"$link\"><b>$name</b></a>\n";
        }
    }

    /* Section header for the welcome page.  $icon is the name of the file
        to use for the icon, ($x,$y) is the size of the icon, and $name is the
        text for the section header. */
    function welcome_section($icon, $x, $y, $name)
    {
        echo
            "<table>\n" .
            "<tr>\n" .
            "<td><img src=\"pub/$icon\" width=\"$x\" height=\"$y\"></td>" .
            "<td valign=\"center\"><b>$name</b></td>\n" .
            "</tr>\n" .
            "</table>";
    }

    
   /*
    |  Main program
    */
    
    $db = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();
    
    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer) 
    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);
               
    $dbg   = get_argument('debug',0,1);
    $adm   = get_argument('admin',0,1);
    $user  = user_data($authuser,$db);
    $debug = @ ($user['priv_debug'])? $dbg : 0;
    $admin = @ ($user['priv_admin'])? $adm : 0;  
      
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users     
    
    /* Make entire page a table. */
    echo '<table width="100%" border="0"><tr><td>';
    
    if (trim($msg)) debug_note($msg);

    /* "Take action" section. */
    if ($comp['dash'] || $comp['cnfg'] || $comp['ptch'])
    {
        welcome_section('takeact.gif', 45, 40, 'Take action');
        echo "<ul>\n";
        welcome_link($comp['dash'], 'Use the dashboard',
            'dashbrd/dnav.php');
        welcome_link($comp['cnfg'], 'Perform a management, maintenance, or support action',
            'config/index.php?act=wiz');
        welcome_link($comp['cnfg'], 'Take remote control of a system',
            'config/remote.php?scop=4?act=scop?pcn=cwiz?rcon=1');
        welcome_link($comp['ptch'], 'Manage Microsoft software updates',
            'patch/wu-confg.php');
        welcome_link($comp['ptch'], 'Provision and meter applications',
            'provis/sites.php');
        echo "</ul>\n<br>\n";
    }

    /* "View information" section. */
    if ($comp['ptch'] || $comp['evnt'] || $comp['asst'])
    {
        welcome_section('viewinfo.gif', 45, 40, 'View information');
        echo "<ul>\n";
        welcome_link($comp['ptch'], 'View Microsoft patch status',
            'patch/wu-stats.php');
        welcome_link($comp['evnt'], 'View alerts',
            'event/console.php');
        welcome_link($comp['evnt'], 'Look at system events',
            'event/event.php');
        welcome_link($comp['evnt'], 'Run an event report',
            'report/report.php');
        welcome_link($comp['asst'], 'Look at system asset information',
            'asset/console.php');
        welcome_link($comp['asst'], 'Run an asset query',
            'asset/query.php');
        welcome_link($comp['asst'], 'Run an asset report',
            'asset/report.php');
        echo "</ul>\n<br>\n";
    }

    /* "Use tools" section. */
    /*   (there is always at least one link in this section) */
    welcome_section('usetools.gif', 45, 40, 'Use tools');
    echo "<ul>\n";
    welcome_link($comp['evnt'], 'Manage alerts',
        'event/notify.php');
    welcome_link($comp['cnfg'], 'Manage groups',
        'config/groups.php?custom=2');
    welcome_link(true, 'View a listing of all sites and systems',
        'acct/census.php');
    welcome_link($comp['updt'], 'Update ASI client',
        'updates/index.php');
    welcome_link(true, 'Manage user accounts on ASI server',
        'acct/admin.php');
    welcome_link(true, 'Access online documentation',
        'doc/index.php');
    echo "</ul>\n<br>\n";


    /* "Debug" section for everything else. */
    if ($debug)
    {
        welcome_section('debug.gif', 45, 40, 'Debug tools');
        echo "<ul>\n";
        welcome_link(true, 'mysql status', 'acct/mysql.php');
        welcome_link(true, 'Event status', 'acct/status.php');
        welcome_link(true, 'Perf counters', 'acct/perf.php');
        welcome_link(true, 'Audit log', 'acct/csrv.php?auditlog');
        welcome_link(true, 'Event debug', 'event/debug.php');
        welcome_link(true, 'Asset admin', 'asset/admin.php');
        welcome_link(true, 'Scrip config', 'config/index.php');
        welcome_link(true, 'MS Updates', 'patch/wu-sites.php');
        welcome_link(true, 'Provisioning', 'provis/sites.php');
        welcome_link(true, 'Info portal', 'acct/files.php');
        welcome_link(true, 'Special debug features', 'acct/index.php');
        echo "</ul>\n<br>\n";
    }

    /* End table for entire page. */
    echo "</table>\n";

    echo head_standard_html_footer($authuser,$db);

?>

