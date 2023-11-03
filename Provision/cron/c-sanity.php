<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
 1-Aug-02   EWB     Fixed a problem with non-standard ports
 2-Aug-02   EWB     Allows port to be specified on command line
20-Sep-02   EWB     Giant refactoring
 5-Dec-02   EWB     Reorginization Day
13-Dec-02   EWB     Mail from $SERVER_NAME
10-Feb-03   EWB     Uses sandbox libraries.
13-Feb-03   EWB     Moved the mskb perl scripts to $odir/event
14-Feb-03   EWB     Removed deprecated call-time pass by reference.
 6-Mar-03   NL      Uses output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header() 
19-Mar-03   EWB     Uses server_def for ssl and port option.
13-Feb-03   EWB     Uses server_name();
16-Feb-03   EWB     server_name variable.
23-Mar-04   EWB     no more base_name
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

    $title  = "Sanity Checks";

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    ); 
include_once ( '../lib/l-sql.php'   );    
include_once ( '../lib/l-serv.php'  );  
include_once ( '../lib/l-rcmd.php'  );       
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-jump.php'  );
include_once ( '../lib/l-base.php'  );


    function again()
    {
        $self = server_var('PHP_SELF');
        $args = server_var('QUERY_STRING');
        $href = ($args)? "$self?$args" : $self;
        $home = '../acct/index.php';

        $a   = array( );
        $a[] = html_link('#top','top');
        $a[] = html_link('#bottom','bottom');
        $a[] = html_link($home,'home');
        $a[] = html_link($href,'again');
        return jumplist($a);
    }

    function new_line()
    {
        echo "<br clear='all'>\n";
    }   

    function show_error($cmd,$error)
    {
        echo "<p><b>cmd: $cmd<br>error: $error</b></p>";
    }

    function search_cmd($cmd, $phrase)
    {
        $output = array();      
        $exec   = "$cmd $phrase";
        exec ($exec, $output, $error);
        if ($error)
        {
            show_error($cmd,$error);
            $output = array( );
        }
        return $output;
    }

    function alert_mail($serv,$dst,$n,$expect,$db)
    {
        if ($dst)
        {
            $comp = component_installed();
            $odir = $comp['odir'];
            $base = server_href($db);
            $sub  = "The mskb script has failed.";
            $msg  = "The mskb script has failed its self test.\n"; 
            $url  = "$base/$odir/cron/c-sanity.php";
            $frm  = "From: notify@$serv";
            $msg .= "Expecting $expect results, but only got $n.\n";
            $msg .= "see $url\n\n\n";
            logs::sendNotification("[alert_mail] $sub $msg ");
            // mail($dst,$sub,$msg,$frm);
        }
    }

    function search_words($path,$phrase)
    {
        $encode = rawurlencode($phrase);
        $result = search_cmd("$path/mskb.pl -a", $encode); 
        return $result;
    }

    function search_exact($path,$phrase)
    {
        $encode = rawurlencode($phrase);
        $result = search_cmd("$path/mskb.pl -e", $encode); 
        return $result;
    }

    function make_link($link,$text)
    {
        $msg  = fontspeak($text);
        $item = "<a href='$link'>$msg</a>";     
        return "<li>$item</li>\n"; 
    }

    function show_list($search,$phrase,$expect)
    {
        $msg = "<br><br>Phrase:<b>$phrase</b><br>Expecting at least $expect results.<br>\n";
        echo fontspeak($msg);
        $x = 0;
        $n = safe_count($search);
        if ($n > 2)
        {
            $x = $search[0];
            $link = $search[1];
            echo "<ul>\n";
            echo make_link($search[1],'Query');
            for ($i = 2; $i < $n; $i++)
            {
                $j = $i - 1;
                echo make_link($search[$i],"Result $j");
            }
            echo "</ul>\n";
        }
        return $x;
    }


    function selftest($path,$server,$phrase,$expect,$email,$db)
    {
        $search = search_words($path,$phrase);
        $n = show_list($search,$phrase,$expect);
        if ($n < $expect)
        {
            alert_mail($server,$email,$n,$expect,$db);
        }
        new_line();
    }

   /*
    |  This is not currently used, but probably will be
    |  again some day.
    */

    function find_email($db)
    {
        $email = "";
        $sql   = "SELECT * FROM Users where username = 'hfn'";
        $res   = mysqli_query($db, $sql);
        if ($res)
        {
            if (mysqli_num_rows($res))
            {
                $row = mysqli_fetch_array($res);
                $email = $row['notify_mail'];
                if (empty($email))
                {
                    $email = $row['report_mail'];
                }
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
        return $email;
    }

    
   /*
    |  Main program
    */
    
    $db = db_connect();
    $authuser = getenv("REMOTE_USER"); // cron does not use PHP auth'n
    $comp = component_installed();
        
    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer) 
    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users 

    $now   = time();
    $debug = (get_integer('debug',0))? 1 : 0;
    $date  = datestring($now); 

    echo again();

    $support = server_opt('support_email',$db);
    $server  = server_name($db);
    $email   = '';

    echo "<h2>$date</h2>";

 // $email = find_email($db);

    if ($email == '')
    {
        $email = $support;
    }

    new_line();
    if ($email != '')
    {
        $path = $comp['path'] . '/event';
        $msg = "Will notify <b>$email</b> upon failure.<br>\n";
        echo fontspeak($msg);
        selftest($path,$server,'ioctlsocket',9,$email,$db); 
        selftest($path,$server,'codepage',10,$email,$db); 
        selftest($path,$server,'QueryPerformanceCounter',10,$email,$db); 
    }

    echo again();

    echo head_standard_html_footer($authuser,$db);
