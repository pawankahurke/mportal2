<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
19-Sep-02   EWB     Giant refactoring.
23-Sep-02   EWB     Saved Searches now called Query Filters
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navagation
13-Dec-02   EWB     Fixed short php tags
 9-Jan-03   EWB     Don't need select library.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
14-Apr-03   NL      Comment out "if (trim($msg)) debug_note($msg)" cuz no $debug
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
04-Jun-07   BTE     Added $isparent handling.

*/

    $title = 'Add a Query Filter';
    
    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    ); 
include_once ( '../lib/l-sql.php'   );    
include_once ( '../lib/l-serv.php'  );  
include_once ( '../lib/l-rcmd.php'  );       
include_once ( '../lib/l-head.php'  );
include_once ( 'local.php'   );
include_once ( '../lib/l-user.php'  );
    
   /*
    |  Main program
    */
    
    $db = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();
        
    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer) 

    $isparent = get_integer('isparent', 0);

    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);
    //if (trim($msg)) debug_note($msg);   // ...display any errors to debug users       

?> 

<br>
<br>
<font face="verdana,helvetica" size="2">

    To add a query filter to your 
    query page, enter the information below:

</font>

<?php   
    echo "<form method=\"post\" action=\"srch-act.php?isparent=$isparent\">";
?>
<input type="hidden" name="action" value="add">
    <!-- 
        Since checkbox values aren't passed if unchecked, 
        provide a hidden variable as a default  
    -->
<input type=hidden name=global value=0>
<table border=0 padding=3>
    <tr>
        <td>
            <font face="verdana,helvetica" size="2">
                Name:
            </font>
        </td>
        <td>
            <input type="text" name="name" maxlength="50" size="40">
        </td>
    </tr>
    <tr>
        <td>
            <font face="verdana,helvetica" size="2">
                Query Filter:
            </font>
        </td>
        <td>
            <textarea wrap="virtual" rows="8" cols="80" name="searchstring"></textarea>
        </td>
    </tr>


<?php   
    $global_auth = user_info($db,$authuser,'priv_search',0);
    if ($global_auth)
    { 
?>
    <tr>
        <td>
            <font face="verdana,helvetica" size="2">
                Global:
            </font>
        </td>
        <td>
            <input type="checkbox" name="global" value="1">
        </td>
    </tr>
<?php
    }
?>
    <tr>
        <td>
            <br>
        </td>
        <td>
            <font face="verdana,helvetica" size="2">
                <input type="submit" value="Add">
                &nbsp;&nbsp;&nbsp;
                <input type="reset" value="reset">
                <br><br>
            </font>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <font face="verdana,helvetica" size="2">
                Tip:
            </font>
        </td>
        <td>
            <pre><?php include ( 'srch-doc.txt' ) ?></pre>
        </td>
    </tr>
</table>
</form>

<?php 
    echo head_standard_html_footer($authuser,$db);
?>
