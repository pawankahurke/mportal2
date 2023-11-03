<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navagation
10-Feb-03   EWB     Uses sandbox libraries.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
14-Apr-03   NL      Comment out "if (trim($msg)) debug_note($msg)" cuz no $debug.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/


    $title = 'Welcome';
    
    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    ); 
include_once ( '../lib/l-sql.php'   );    
include_once ( '../lib/l-serv.php'  );  
include_once ( '../lib/l-rcmd.php'  );       
include_once ( '../lib/l-head.php'  );
include_once ( 'local.php'   );
    
    
   /*
    |  Main program
    */
    
    $db = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();
        
    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer) 
    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);
    //if (trim($msg)) debug_note($msg);   // ...display any errors to debug users   
        
?>    

<table width="100%" border="0">
<tr><td>
<br>
<b>Event Management</b><br>
<ul>
   <li>
     <a href="event.php"><b>ad-hoc query</b></a>: 
        create and run a one-time ad-hoc query
        based on a filter 
   <li>
     <a href="search.php"><b>filters</b></a>: manage query filters
   <li>
     <a href="notify.php"><b>notifications</b></a>: manage notifications
   <li>
     <a href="console.php"><b>console</b></a>: view notifications
   <li>
     <a href="report.php"><b>reports</b></a>: manage reports
</ul>

<br>      

<br>
<b>Tools</b>   
<ul>      
   <li>
      <a href="../acct/admin.php"><b>admin</b></a>
   <li>
      <a href="doc/index.php"><b>help</b></a>
</ul>

</table>

<?php
    echo head_standard_html_footer($authuser,$db);
?>

