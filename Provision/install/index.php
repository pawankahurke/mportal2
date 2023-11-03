<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
15-May-03   NL      Creation.
29-May-03   NL      install_html_header(): pass $priv_servers (to display servers link).
02-Jun-03   NL      Call install_html_footer (has its own version).
02-Jun-03   NL      Change links to correspond to new page titles.
04-Jun-03   NL      Change title and page descriptions.
09-Jun-03   NL      Change sitelist.php to sites.php?action=list
16-Jun-03   NL      Change include line: '../lib/l-head.php' --> 'header.php'.
23-Jul-03   NL      Change footer to standard_html_footer().
29-Sep-03   NL      Remove "user" link --> even non-admin user goes to userlist page.
23-Oct-03   NL      BACK OUT: Remove "user" link --> even non-admin user goes to userlist page.
13-Jul-04   WOH     Made minor format changes.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
03-Oct-08   BTE     Bug 4828: Change customization feature of server.

*/

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent) 
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    ); 
include_once ( '../lib/l-sql.php'   );    
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-rcmd.php'  );      
include_once ( '../lib/l-user.php'  );  
include_once ( '../lib/l-head.php'  );      
include_once ( 'header.php'         );
include_once ( '../lib/l-errs.php' );
include_once ( '../lib/l-cnst.php' );
   

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
    
    $title   = 'Welcome';
        
    $db = db_connect();
    db_change($GLOBALS['PREFIX'].'install',$db);
    
    $fusersql = "select count(installuserid) as usercount from Users where installuser not in ('hfn', 'admin')";
    $fuserres = command($fusersql, $db);
    $fuserdat = mysqli_fetch_assoc($fuserres);
    
    if($fuserdat['usercount'] == 0) {
        header('Location: tenantimport.php');
    } else {
        $authuser       = install_login($db);
        $authuserdata   = install_user($authuser,$db);   
        $priv_admin     = @ ($authuserdata['priv_admin'])  ? 1 : 0;    
        $priv_servers   = @ ($authuserdata['priv_servers'])? 1 : 0;

        $comp = component_installed();

        $msg = ob_get_contents();           // save the buffered output so we can...
        ob_end_clean();                     // (now dump the buffer) 
        echo install_html_header($title,$comp,$authuser,$priv_admin,$priv_servers,$db);
        if (trim($msg)) debug_note($msg);   // ...display any errors to debug users  

        echo silly_table();

        echo section('Installation Management');

        $userpagename   = ($priv_admin) ? 'Users' : 'User';  
        $userpageurl    = ($priv_admin) ? 'userlist.php' : 'userdata.php';

        echo "<ul>\n";       
        spewlink($userpageurl,$userpagename,': Manage user information.');
        spewlink('sites.php?action=list','Sites',': Create and modify site installation and deployment information.');
        spewlink('strtlist.php','Scrip configurations',': Manage Scrip configurations.');    
        if ($priv_servers)
        {
            spewlink('servlist.php','ASI servers',': Manage ASI server information.');
        }
        if($priv_admin) {
            spewlink('maildata.php','SMTP Config',': Manage SMTP configuration.');
            spewlink('skudata.php','Import Key',': Manage SKU offerings.');
        }
        echo "</ul>\n";

        echo "</table>\n";

        /* Hardwired to pass in hfn for the user. */
        $user = 'hfn';
        echo head_standard_html_footer($user,$db);
    }
?>
