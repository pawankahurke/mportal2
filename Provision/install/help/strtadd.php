<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
16-Sep-03   NL      Creation.
              
*/

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent) 

include_once ( 'htmlhead.php'        );
include_once ( 'htmlfoot.php'        );  

    function newlines($n)
    {
        for ($i = 0; $i < $n; $i++)
        {
            echo "<br>\n";
        }
    }

    function display_content()
    { 
    
        /*  The following HTML was generated by OpenOffice from the Word doc
            with the following changes:
            1.  Remove all references to below, shown below, seen below, and see below.
            2.  Remove all images; For form buttons, replace with text.
            3.  Nest lists as necessary.
            4.  Remove hyperlinks.
        */    
        
        $msg = <<< HERE

<H3 CLASS="western">Add Scrip configuration</H3>
<P CLASS="western">The <FONT COLOR="#0000ff"><U><FONT FACE="Verdana, sans-serif">check
all</FONT></U></FONT><FONT FACE="Verdana, sans-serif"> </FONT>and<FONT FACE="Verdana, sans-serif">
</FONT><FONT COLOR="#0000ff"><U><FONT FACE="Verdana, sans-serif">uncheck
all</FONT></U></FONT><FONT FACE="Verdana, sans-serif"> </FONT>actions
let you select or de-select all available Scrips with one mouse
click. By default<FONT FACE="Verdana, sans-serif"> no Scrip is
selected.</FONT></P>
<P CLASS="western">Fields on this page are:</P>
<UL>
	<LI><P CLASS="ww-list-bullet1-western"><FONT FACE="Verdana, sans-serif">Startup
	Option Name </FONT>&ndash; the name of a Scrip configuration should
	be a single text string of up to 50 characters in length. When
	creating the name of a Scrip configuration you should avoid using
	the following characters:</P>
    <UL>
    	<LI><P CLASS="ww-list-bullet-21-western">&amp;</P>
    	<LI><P CLASS="ww-list-bullet-21-western">, (comma)</P>
    	<LI><P CLASS="ww-list-bullet-21-western">. (period)</P>
    </UL>
    <P CLASS="western">The names <B><FONT FACE="Courier New, monospace">All</FONT></B>
    and <B><FONT FACE="Courier New, monospace">None </FONT></B>have been
    reserved for use by the ASI installation management facility and
    cannot be used</P>
</UL>
<UL>
	<LI><P CLASS="ww-list-bullet1-western">The list of available Scrips
	&ndash; if you want a Scrip to be enabled when this option is used
	by the ASI client either during the start-up or follow-on phases,
	simply click in the check box to the left of the entry for that
	Scrip.</P>
</UL>
<P CLASS="western">At the top and bottom of the <FONT SIZE=2 STYLE="font-size: 11pt"><FONT FACE="Verdana, sans-serif"><FONT COLOR="#333399">Add
Scrip Configuration</FONT></FONT></FONT> page, you will find three
buttons: 
</P>
<UL>
  	<LI><P CLASS="ww-list-bullet1-western"><FONT FACE="Verdana, sans-serif"><B>Cancel &nbsp; Help &nbsp; Enter</B></FONT></P>
</UL>
<P CLASS="western">Clicking on 
<FONT FACE="Verdana, sans-serif"><B>Cancel</B></FONT>
takes you back to the <FONT SIZE=2 STYLE="font-size: 11pt"><FONT FACE="Verdana, sans-serif"><FONT COLOR="#333399">Scrip
Configurations </FONT></FONT></FONT>page without making any changes.</P>
<P CLASS="western">Clicking on 
<FONT FACE="Verdana, sans-serif"><B>Enter</B></FONT>
checks the site information for validity, and either takes you to the
<FONT SIZE=2 STYLE="font-size: 11pt"><FONT FACE="Verdana, sans-serif"><FONT COLOR="#333399">Creating
Scrip Configuration </FONT></FONT></FONT>page, if the
Scrip configuration creation operation was completed successfully, or
to a page with the same name and a message explaining where the error
was made.</P>
<P CLASS="western">Clicking on 
<FONT FACE="Verdana, sans-serif"><B>Help</B></FONT>
opens the <FONT SIZE=2 STYLE="font-size: 11pt"><FONT FACE="Verdana, sans-serif"><FONT COLOR="#333399">Add
Scrip Configuration Help</FONT></FONT></FONT> page in a new browser
window. 
</P>
    
HERE;

    echo $msg;
    }


   /*
    |  Main program
    */
    
/*    $db = db_connect();
    db_change($GLOBALS['PREFIX'].'install',$db);
    $authuser = install_login($db);
    $comp = component_installed();
  
    $action = get_argument('action',0,'edit'); // non-admin user clicks on user navbar link 
    $title   = ucwords($action) . ' User Help';
    
    $user   = install_user($authuser,$db);    
    $admin  = @ ($user['priv_admin'])  ? 1 : 0;    
    $serv   = @ ($user['priv_servers'])  ? 1 : 0; 
    if ($id == 0) $id = $user['installuserid'];
*/    
    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer) 
    echo html_header();
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users  

     
        
    newlines(1);   
    
    display_content();
    
    echo html_footer();
?>
