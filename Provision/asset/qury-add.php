<?php
/* 
Revision history:

Date        Who     What
----        ---     ----
23-Sep-02   NL      start file from 19-Sep-02, EWB, Giant refactoring.
23-Sep-02   NL      remove event code --> asset only
...
09-Oct-02   NL      add rel date "X days ago" functionality
11-Oct-02   NL      fixed bug with rel dates & date_value
21-Oct-02   NL      add "temporary query" functionality ($disposition)
23-Oct-02   NL      change create to add
31-Oct-02   NL      change outputJavascriptShowDaysAgo to outputJavascriptShowElement
04-Nov-02   NL      global_auth checks for "priv_aquery" instead of "priv_search"
04-Nov-02   NL      only display "and Global Property" if $global_auth  
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navigation
10-Dec-02   EWB     Cleaned up short tags
 3-Feb-03   EWB     Minimal quotes
 7-Feb-03   EWB     Moved to asset world
10-Feb-03   EWB     Uses sandbox libraries.
11-Feb-03   EWB     db_change()
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header() 
19-Mar-03   NL      Comment out "if (trim($msg)) debug_note($msg)" cuz $debug non-existant
 5-May-03   NL      Include l-js.php cuz outputJavascriptShowElement() moved there. 
26-May-03   EWB     Fixed Javascript relative-date problem
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
17-Aug-07   BTE     Changes for summary sections phase 1.
23-Oct-07   BTE     Added a comment.

*/

  
    $title = 'Add An Asset Query';

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    ); 
include_once ( '../lib/l-sql.php'   );    
include_once ( '../lib/l-serv.php'  );    
include_once ( '../lib/l-head.php'  );
include_once ( 'local.php'   );        
include_once ( '../lib/l-cmth.php'  );    
include_once ( '../lib/l-asst.php'  );    
include_once ( '../lib/l-slct.php'  );
include_once ( '../lib/l-srch.php'  );    
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-crit.php'  );
include_once ( '../lib/l-js.php'    );
    
    
   /*
    |  Main program
    */
    
    $db = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();
    $isparent = get_integer('isparent',0);
    
    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer) 
    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);
    //if (trim($msg)) debug_note($msg);   // ...display any errors to debug users  

    $global_auth = user_info($db,$authuser,'priv_aquery',0);     
    db_change($GLOBALS['PREFIX'].'asset',$db);
?> 

<form method=post action="qury-act.php" name="form">
    <input type=hidden name=action value=add>
<?php
    echo '<input type=hidden name=isparent value=' . $isparent . '>';
?>
    <!-- 
        Since checkbox values aren't passed if unchecked, 
        provide a hidden variable as a default  
    -->
    <input type=hidden name=global value=0>
       
<table border=0 cellpadding=3>
    <tr>
        <td colspan=2><br><b>Provide a Name
<?php 
        if ($global_auth) echo " and Global Property"; 
?>
        :</b></td>
    </tr> 
    <tr>
        <td>Name:</td>
        <td><input type="text" name="name" maxlength="50" size="40"></td>
    </tr>
<?php   
    if ($global_auth) 
    { 
?>
    <tr>
        <td>Global:</td>
        <td><input type="checkbox" name="global" value="1"></td>
    </tr>
    
<?php
    }
?>      

</table>
<br>
     
<table border=0 cellpadding=3>     
    <tr>
        <td bgcolor="#EEEEEE" valign=top nowrap> 
            <b>Select Fields to Display:</b><br> 
            <span class=footnote><i>
            <br>
            Click on <img src="../pub/plus.gif">'s and 
            <img src="../pub/minus.gif">'s 
            to navigate <span class=faded>categories</span>.<br>
            Click on <img src="../pub/check_box.gif">'s to choose 
            <span class=blue>fields</span> to be displayed.<br> 
            Clicking on the <span class=blue>field</span> itself 
            will enter it in<br>
            the Search Criteria table to the right.<br> 
            </i></span>
            <br>                   
            <?php 
                outputJavascriptAssetTree(1,1,0,0,''); 
            ?>
            <br>
        </td>
     
        <td width=20 rowspan=4>&nbsp;</td> 
                  
        <td bgcolor="#EEEEEE" valign=top>                                 
            <b>Create Search Criteria:</b><br> 
            <span class=footnote><i>
            <br>
            1. Click on a <span class=blue>field</span> in the list 
            to the left to include it in the search
            criteria below.<br>
            <img src="../pub/closed.gif" width="7" height="7" border="0">
            &nbsp;It will appear next to the black arrow.  
            Click in any field to move the arrow to another row.<br>  
            2. Select a comparison option. <br>
            3. Type in a value to be matched.<br>
            <!-- To group fields so that matching fields are associated,
                enter a <span class=faded>category</span> from the left
                in the group by field.<br> -->

            <br> 
            </i></span>
            
            <table cellpadding=3 cellspacing=3 border=1 bordercolor="#999999">
            <tr>
                <td></td>
                <td>1. field name</td>
                <td>2. comparison option</td>
                <td>3. value to match</td>
                <!-- <td>group by</td> -->
            </tr>
            <?php
                outputJavascriptCritBldr(9,9,''); 
            ?>
            </table>  
       </td>           
    </tr>  
</table> 

<br>
     
<table border=0 cellpadding=3 cellspacing=0>
    <tr>
        <td><b>Select Date:</b></td>
        <td colspan=2>&nbsp;</td>
    </tr>     
                
    <tr>
        <td><input type="Radio" Name="DateType" Value="RelDate" checked>Relative Date:</td>
        <td>
<?php  /* This code is copied in three places:
            asset/adhoc.php
            asset/qury-act.php */
            global $date_code;
        
            $date_codes[0] = ' - - - - - - - - - - - - - - -';
            $date_codes[1] = 'latest';
            $date_codes[2] = '1 day ago';
            $date_codes[3] = 'some days ago...'; 
            $date_codes[4] = '1 week ago';
            $date_codes[5] = '1 month ago';
            $date_codes[6] = '3 months ago';
            $date_codes[7] = '6 months ago';
            $date_codes[8] = '1 year ago';                

            $select  = html_select('date_code', $date_codes, $date_code, 1);
            $show    = "showElement('rel_days_ago,rel_days_ago_text', document.form.date_code.selectedIndex,3,'')"; 
            $change  = "onChange=\"$show\"";
            $pattern = 'size="1"';
            $replace = "$change $pattern";
            echo str_replace($pattern,$replace,$select);
?>          
        </td>
        <td nowrap>
            <input type="text" size="2" name="rel_days_ago" id="rel_days_ago">
            <span id="rel_days_ago_text">days ago</span>
<?php
            outputJavascriptShowElement("rel_days_ago,rel_days_ago_text", 
                        "document.form.date_code.selectedIndex","3","");
?> 
        </td>
    </tr>
    <tr>
        <td valign=top><input type="Radio" Name="DateType" Value="ExactDate">Exact Date: </td>
        <td colspan=2>
            <?php 
                echo date_selector('','','');
            ?> 
        </td>   
    </tr>
                    
    <tr>
        <td colspan=2><br><b>Select Display Options:</b></td>
        <td>&nbsp;</td>
    </tr> 
    
    <tr>
        <td colspan=2>Number of Results per Page:</td>
        <td>
<?php 
            $rowsizes = array('25', '50', '100');
            echo html_select('rowsize', $rowsizes, '50', 0); 
?>   
        </td>
    </tr>
    
    <tr>
        <td colspan=2>Refresh Page Every (in minutes):</td>
        <td>
<?php 
            $refreshes = array('never', '5', '10', '15');
            echo html_select('refresh', $refreshes, 'never', 0);
?>
        </td>
    </tr>
</table> 
<br>
<table border=0 cellpadding=3>    
    <tr>
        <td>&nbsp;</td>
        <td>
            <font face="verdana,helvetica" size="2">
                <input type="submit" name="disposition" value="Save">
                &nbsp;&nbsp;&nbsp;
                <input type="submit" name="disposition" value="Run">
                &nbsp;&nbsp;&nbsp;
                <input type="submit" name="disposition" value="Save and Run">
                &nbsp;&nbsp;&nbsp;
                <input type="reset" value="Reset">
                <br>
            </font>
        </td>
    </tr>   
</table>    

</form>

<?php 
    echo head_standard_html_footer($authuser,$db);
?>

