<?php



/*

Revision history:



Date        Who     What

----        ---     ----

20-Sep-02   EWB     Giant refactoring.

 4-Dec-02   EWB     Reorginization Day

16-Jan-03   EWB     Further refactering.

 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()

10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.

10-Mar-03   NL      Passed 0 as $legend to standard_html_header()

01-May-03   AAM     Retro-fixed this change:

12-Dec-02   WOH     Added links for new pdfs dealing with asset.

include_once WOH     Updated links for pdf files.

include_once WOH     Updated link for event log management.

include_once WOH     Added Portal user guide

18-Jun-04   WOH     Added Installation users guide.  Removed word log from 1 label.

21-Jun-04   WOH     Removed the word log from one of the links.  Per Alex request.

09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

11-Dec-06   WOH     Updated help file for Alex. Bugzilla # 3942





*/



    $title = 'Help Index';



    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)

    include ( '../lib/l-util.php'  );

    include ( '../lib/l-db.php'    );

    include ( '../lib/l-sql.php'   );

    include ( '../lib/l-serv.php'  );

    include ( '../lib/l-rcmd.php'  );

    include ( '../lib/l-head.php'  );



   /*

    |  Main program

    */



    $db = db_connect();

    $authuser = process_login($db);

    $comp = component_installed();



    $msg = ob_get_contents();           // save the buffered output so we can...

    ob_end_clean();                     // (now dump the buffer)

    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);

    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users



?>



<br>



<table width="100%" border="0">

<tr><td>

    <b>

        <a href="usergd.pdf">ASI Event Management User Guide</a>

    </b>

    <br>

    General information about using the event management facility, in PDF form<p>



    <b>

        <a href="querygd.pdf">ASI Support Query Module User Guide</a>

    </b>

    <br>

    General information about using automated knowledge base queries, in PDF form<p>



    <b>

        <a href="reportgd.pdf">ASI Event Management Report Module User Guide</a>

    </b>

    <br>

    General information about using the event management facility report module, in PDF form<p>



    <b>

        <a href="asstmgmt.pdf">ASI Asset Management User Guide</a>

    </b>

    <br>

    General information about using the asset management facility, in PDF form<p>



    <b>

        <a href="asstrpt.pdf">ASI Asset Management Report Module User Guide</a>

    </b>

    <br>

    General information about using the asset management facility report module, in PDF form<p>



    <b>

        <a href="sitemgnt.pdf">ASI Site Management User Guide</a>

    </b>

    <br>

    General information about using the ASI site management facility, in PDF form<p>



    <b>

        <a href="portalgd.pdf">ASI Information Portal User Guide</a>

    </b>

    <br>

    General information about using the ASI Information Portal facility, in PDF form<p>



    <b>

        <a href="searches.xls">Event filters</a>

    </b>

    <br>

    Microsoft Excel spreadsheet listing currently available event filters<p>



    <b>

        <a href="notify.xls">Event Notification Scrip Cross-reference</a>

    </b>

    <br>

    Microsoft Excel spreadsheet listing currently available event

    notifications and the Scrips whose events they retrieve<p>



    <b>

        <a href="reports.xls">Event Report Scrip Cross-reference</a>

    </b>

    <br>

    Microsoft Excel spreadsheet listing currently available event reports

    and the Scrips whose events they retrieve<p>



    <b>

        <a href="installgd.pdf">ASI Client Installation User Guide</a>

    </b>

    <br>

    General information about the ASI client installation, in PDF form<p>



    <b>

        <a href="scrips/index.php">ASI Scrip Index</a>

    </b>

    <br>

    Listing of currently available scrips ordered by number with links to Scrip detail log help pages<p>

</td></tr>

</table>



<?php

    echo head_standard_html_footer($authuser,$db);

?>

