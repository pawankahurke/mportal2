<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
19-Sep-02   EWB     Giant refactoring.
13-Nov-02   EWB     Log mysql errors.
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navagation
10-Jan-03   EWB     Don't require register_globals.
10-Jan-03   EWB     Use servertime for the "about the same time" link.
16-Jan-03   EWB     Fixed a typo.
17-Jan-03   EWB     Access to $_SERVER variables.
10-Feb-03   EWB     Use the sandbox libraries.
10-Feb-03   EWB     Use the new datbase.
11-Feb-03   EWB     db_change()
 4-Mar-03   EWB     Fixed a bug in about the same time.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
 9-Apr-03   EWB     Direct access to pager timestamps.
14-Apr-03   NL      Move debug_note line below $debug.
22-May-03   EWB     Quote Crusade.
17-Jun-03   EWB     Slave database.
20-Jun-03   EWB     No Slave Database.
16-Jul-03   EWB     Fixed unset variable.
20-Aug-03   EWB     Added scrip 77 to mskb class
 9-Nov-03   NL      Add link to asset detail page: display_asset_link().
10-Nov-03   NL      Change asset link location & label; display_asset_link() -> asset_link().
17-Jan-05   AAM     Fixed spelling of "occurring".
09-Mar-06   AAM     Removed query to generate link to asset detail page.  The
                    query was moved to asset/detail.php (bug 2924).
04-Sep-06   AAM     Bug 3650: Added call to htmlspecialchars to display text3.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()                    
18-Jul-07   RWM     Bug 4236: Show String 2 for all users, not just debug.

*/

    $title = 'Event Detail';

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    ); 
include_once ( '../lib/l-sql.php'   );    
include_once ( '../lib/l-serv.php'  );  
include_once ( '../lib/l-rcmd.php'  );       
include_once ( 'local.php'   );
include_once ( '../lib/l-user.php'  );
//  include ( '../lib/l-slav.php'  );
include_once ( '../lib/l-head.php'  );


    function show_table_item($name, $valu)
    {
        echo <<< HERE

<tr valign="top">
  <td>
    <b>$name:</b>
  </td>
  <td>
    $valu
  </td>
</tr>

HERE;

    }

    // 46 -- error dialog box message
    // 47 -- warning dialog box message
    // 48 -- informative dialog box message
    // 49 -- question dialog box message
    // 77 -- windows event log change
    // 94 -- log icon dialog box message

    function searchevent($scrip,$text1)
    {
        $result = 0;
        if (strlen($text1))
        {
            if ((46 <= $scrip) && ($scrip <= 49))
                $result = 1;
            if ($scrip == 77)
                $result = 1;
            if ($scrip == 94)
                $result = 1;
        }
        return $result;
    }

    function asset_link($site,$machine,$db)
    {                  
        $link = '';
        
        if ($site && $machine)
        {
            $link = "../asset/detail.php?site=$site&machine=$machine";
        }         
        
        return $link;
    }     
    
    function display_row($row,$debug,$db)
    {
        $idx        = $row ['idx'];
        $entered    = $row ['entered'];
        $scrip      = $row ['scrip'];
        $customer   = $row ['customer'];
        $machine    = $row ['machine'];
        $executable = $row ['executable'];
        $servertime = $row ['servertime'];

        /* Only translate special characters in text3 because Scrip 100
            stores data in there that can cause the page display to be
            distorted.  We may extend this to other fields at some point
            but not right now. */
        $text1     = nl2br($row ['text1']); 
        $text2     = nl2br($row ['text2']); 
        $text3     = nl2br(htmlspecialchars($row ['text3'], ENT_QUOTES));
        $text4     = nl2br($row ['text4']); 

        $new_query = html_link('event.php','new query');
        $href = "../doc/scrips/scrip.php?scrip=$scrip";
        $text = "view a description of scrip $scrip";
        $view_desc = html_link($href,$text);
        echo "<p>\n";

        $msg = "Click 'back' on your browser to return to the query " .
               "results or perform a $new_query.<br>\n" . 
               "You can also $view_desc " . 
               "which provides more information about " .
               "the fields on this page.";
        $msg = fontspeak($msg);

        echo "$msg<br>\n\n";
        $mskb = "You may also view results of " .
                "<a href=\"mskb.php?eid=$idx\">" . 
                "knowledge base</a> " . 
                "searches about the symptom reported by this event.";
        $mskb = fontspeak($mskb);


        // 38 -- processor fault

        if (($scrip == 38) && ($executable) && ($text4))
        {
            echo "$mskb<br>\n\n";
        }

        if (searchevent($scrip,$text1))
        {
            echo "$mskb<br>\n\n";
        }

        $umin = $servertime - 300;
        $umax = $servertime + 300;

        $host = urlencode($machine);
        $site = urlencode($customer);
              
        $href  = "pager.php?";
        $href .= "sel_machine=$host&";
        $href .= "sel_customer=$site&";
        $href .= "umin=$umin&umax=$umax";
        
        $asset_link = asset_link($site,$host,$db);

        $msg  = "You may also see <a href=\"$href\">events occurring about" .
                " the same time</a> on this system";
        $msg  .=  (strlen($asset_link))? ", and its <a href='$asset_link'>asset information</a>." : ".";
        $msg  = fontspeak($msg);
        echo "$msg\n</p>\n\n\n";

        $ctime = mysqltime($entered);
        $stime = mysqltime($servertime);

        #Table With Event Detail Info
        echo '<table border="0" cellpadding="3" cellspacing="0">';
        show_table_item('Client time',$ctime);
        show_table_item('Server time',$stime);
        show_table_item('Scrip number',$scrip);
        show_table_item('Customer',$customer);
        show_table_item('Machine',$machine);
        show_table_item('UUID',$row['uuid']);
        show_table_item('Username', $row['username']);
        show_table_item('Client version',$row['clientversion']);
        if ($debug)
            show_table_item('Client size',$row['clientsize']);
        show_table_item('Priority',$row['priority']);
        show_table_item('Description',$row['description']);
        show_table_item('Type',$row['type']);
        show_table_item('Path',$row['path']);
        show_table_item('Executable',$row['executable']);
        show_table_item('Version',$row['version']);
        show_table_item('Size',$row['size']);
        show_table_item('ID',$row['id']);
        show_table_item('Window title',$row['windowtitle']);
        show_table_item('String 1',$row['string1']);
        show_table_item('String 2',$row['string2']);
        show_table_item('Text 1',$text1);
        show_table_item('Text 2',$text2);
        show_table_item('Text 3',$text3);
        show_table_item('Text 4',$text4);
        echo "</table>\n\n\n<br clear=\"all\">\n\n";
    }  


   /*
    |  Main program
    */
    
    $db = db_connect();
/************************
    $mdb = db_connect();
    $sdb = db_slave($mdb);
    if ($sdb)
    {
        db_change($GLOBALS['PREFIX'].'core',$sdb);
        $db = $sdb;
    }
    else
    {
        $db = $mdb;
    }
************************/
    $authuser = process_login($db);
    $comp = component_installed();
    
    $priv   = user_info($db,$authuser,'priv_debug',0);
    $eid    = intval(get_argument('eid',0,0));
    if ($eid == 0)
    {
        $eid = intval(get_argument('sel_id',0,0));
    }
    $debug  = (get_argument('debug',0,$priv))? 1 : 0;
    $debug  = ($priv)? $debug : 0;        
    
    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer) 
    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users 

/***********************
    if ($sdb)
        debug_note("replicated database, mdb:$mdb, sdb:$sdb");
    else
        debug_note("normal database");
************************/
    $access = find_customers($authuser,$db);

    db_change($GLOBALS['PREFIX'].'event',$db);

    $row  = array( );
    $sql  = "select * from Events\n";
    $sql .= " where idx = $eid and\n";
    $sql .= " (customer in ($access))";
    $res  = redcommand($sql, $db);

    if ($res)
    {
        if (mysqli_num_rows($res) == 1)
        {
            $row = mysqli_fetch_array($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    if ($row)
    {          
        display_row($row,$debug,$db);
    }
    else
    {
        $msg = "No such record found.";
        echo "<p>$msg</p>\n";
    }
    echo head_standard_html_footer($authuser,$db);    
?>


