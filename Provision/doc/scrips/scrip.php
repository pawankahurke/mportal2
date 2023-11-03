<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
20-Sep-02   EWB     Giant refactoring.
 5-Dec-02   EWB     Reorginization Day
31-Dec-02   EWB     Unix file
31-Dec-02   EWB     Single quotes for non-evaluated strings
16-Jan-03   EWB     More refactoring.
10-Mar-03   NL      Added "../../lib" back in so code uses sandbox libraries. 
10-Mar-03   NL      Passed 0 as $legend to standard_html_header() 
19-Mar-03   NL      Comment out "if (trim($msg)) debug_note($msg)" cuz $debug non-existant 
19-Mar-03   EWB     Removed a non-existing include file.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

    $title = '';
    
    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include_once ( '../../lib/l-util.php'  );
include_once ( '../../lib/l-db.php'    ); 
include_once ( '../../lib/l-sql.php'   );    
include_once ( '../../lib/l-serv.php'  );  
include_once ( '../../lib/l-rcmd.php'  );       
include_once ( '../../lib/l-head.php'  );
        
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
    
    $scrip = get_argument('scrip',0,'');
    
    $file = sprintf('s%04dhlp.htm', $scrip);

include_once ( $file );

    echo head_standard_html_footer($authuser,$db);
?>

