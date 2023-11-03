<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
 1-Aug-02   EWB     Cleaned up a few php log warnings.
 1-Aug-02   EWB     Added convenient home link.
20-Sep-02   EWB     Giant refactoring.
 5-Dec-02   EWB     Reorginization Day
17-Feb-03   EWB     Uses sandbox libraries.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header() 
19-Mar-03   NL      Move $debug initialization and surrounding lines above ob_ lines
19-Mar-03   NL      Include l-rcmd.php for debug_note().
12-Sep-03   EWB     Uses new graphics API.
12-Sep-03   EWB     Test of base64 encoding and direct graphics.
12-Sep-03   EWB     experiments with inline images.
22-Sep-03   EWB     uses server option 'jpeg_quality'.
22-Sep-03   EWB     command line override for jpeg_quality.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

    $title = 'Graphics Test';
    
    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)  
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    ); 
include_once ( '../lib/l-sql.php'   );    
include_once ( '../lib/l-serv.php'  ); 
include_once ( '../lib/l-rcmd.php'  );    
include_once ( '../lib/l-jump.php'  );
include_once ( '../lib/l-base.php'  );
include_once ( '../lib/l-graf.php'  );
include_once ( '../lib/l-head.php'  );

    function again()
    {
        $self = server_var('PHP_SELF');
        $args = server_var('QUERY_STRING');
        $href = ($args)? "$self?$args" : $self;
        $test = "$self?test";

        $a   = array( );
        $a[] = html_link('#top','top');
        $a[] = html_link('#bottom','bottom'); 
        $a[] = html_link('index.php','home');
        $a[] = html_link($href,'again');
        $a[] = html_link("$test=0",'weekday');
        $a[] = html_link("$test=1",'wednesday');
        $a[] = html_link("$test=2",'months');
        $a[] = html_link("$test=3",'companies');
        $a[] = html_link("$test=4",'food');
        $a[] = html_link("$test=5",'long');
        $a[] = html_link("$test=6",'fifty');
        return jumplist($a);
    }



   /*
    |  See the following:
    |
    |    http://www.ietf.org/rfc/rfc2397.txt
    |
    |  Inline images are really supposed to be
    |  for little things, so these are probably
    |  too big.  Plus, MSIE doesn't work with
    |  them.  However, they are a good way
    |  to test the base64 encoding.
    */

    function draw_image(&$img,$inline)
    {
        if ($img)
        {
            if ($inline)
            {
                $xxx = $img['xsize'];
                $yyy = $img['ysize'];
                $b64 = image_encode($img);
                echo <<< HERE
<img 
  border="0" 
  width="$xxx" 
  height="$yyy" 
  src="data:image/jpg;base64,
$b64">

HERE;
            }
            else
            {
                $path = image_path($img);
                $link = str_replace("../../","/",$path);
                echo "<img src=\"$link\">\n\n";
            }
        }
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
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users
    
    
    $qdef    = server_int('jpeg_quality',95,$db);
    $debug   = get_integer('debug',0); 
    $test    = get_integer('test',0);
    $inline  = get_integer('inline',0);
    $quality = get_integer('quality',$qdef);
    $server  = server_name($db);

    echo again();

    debug_note("test:$test, inline:$inline, quality:$quality");

    if ($test == 0)
    {
        $numbers = array(1203, 1273, 4394, 678, 932, 1450, 3300);
        $names   = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
        $label   = "Weekday";
    }

    if ($test == 1)
    {
        $numbers = array(1203);
        $names   = array('Wednesday');
        $label   = "Weekday";
    }


    if ($test == 2)
    {
        $numbers = array(31,28,31,30,31,30,
                         31,31,30,31,30,31);
        $names   = array('January','February','March','April','May','June',
                         'July','August','September','October','November','December');
        $label   = "Months";
    }


    if ($test == 3)
    {
        $names   = array('DuPont', 'Valvoline', 'Mars', 'Texaco', 'Goodyear', 'Kellogs', 'Valvoline',
                         'DeWalt', 'Nokia','Interstate','General Mills', 'Kmart','McDonalds','Excite',
                         'Union Carbide','Motorala','Exxon','Sears');
        $numbers = array(4931,1265,3421,3774,2312,2309,4514,3946,8330,
                         5471,2202,4031,3329,1308,3703,3103,2207,3462);
        $label = "Companies";
    } 

    if ($test == 4)
    {
        $names   = array('Pizza', 'Sushi', 'Panang Beef', 'Burrito');
        $numbers = array(1920,1432,1103,1205);
        $label = "Food";
    } 

    if ($test == 5)
    {
        $names   = array('123456789012345678901234567890123456789012345678901234567890extra',
                         'Another Very Long Name For Testing Graphics',
                         'Yet Another Long Name');
        $numbers = array(1920,1432,1778);
        $label = "Big Names";
    } 

    if ($test == 6)
    {
        $names = array('zero','one','two','three','four',
                        'five','six','seven','eight','nine',
                        'ten','eleven','twelve','thirteen','fourteen',
                        'fifteen','sixteen','seventeen','eightteen','nineteen',
                        'twenty','twenty-one','twenty-two','twenty-three','twenty-four',
                        'twenty-five','twenty-six','twenty-seven','twenty-eight','twenty-nine',
                        'thirty','thirty-one','thirty-two','thirty-three','thirty-four',
                        'thirty-five','thirty-six','thirty-seven','thirty-eight','thirty-nine',
                        'fourty','fourty-one','fourty-two','fourty-three','fourty-four',
                        'fourty-five','fourty-six','fourty-seven','fourty-eight','fourty-nine');

        $numbers = array(0,1,2,3,4,5,6,7,8,9,
                         10,11,12,13,14,15,16,17,18,19,
                         20,21,22,23,24,25,26,27,28,29,
                         30,31,32,33,34,35,36,37,38,39,
                         40,41,42,43,44,45,46,47,48,49);
        $label = "Fifty Numbers";
    } 

    
    echo "<br clear='all'>\n";

    $mintime = getmicro(microtime());
    $pie_img = chart_image('pie',   $numbers,$names,$label,$quality,$server);
    $pietime = getmicro(microtime());
    $bar_img = chart_image('bar',   $numbers,$names,$label,$quality,$server);
    $bartime = getmicro(microtime());
    $col_img = chart_image('column',$numbers,$names,$label,$quality,$server);
    $coltime = getmicro(microtime());

    $piemsec = round($pietime['msec'] - $mintime['msec']);
    $barmsec = round($bartime['msec'] - $pietime['msec']);
    $colmsec = round($coltime['msec'] - $bartime['msec']);
    
    $piesize = $pie_img['bsize'];
    $barsize = $bar_img['bsize'];
    $colsize = $col_img['bsize'];

    echo "<h2>Pie Chart ($piemsec msec, $piesize bytes)</h2>\n";

    draw_image($pie_img,$inline);

    echo "<h2>Bar Graph ($barmsec msec, $barsize bytes)</h2>\n";

    draw_image($bar_img,$inline);

    echo "<h2>Column Graph ($colmsec msec, $colsize bytes)</h2>\n";

    draw_image($col_img,$inline);

    echo "<br clear='all'>\n";
    echo again();

    echo head_standard_html_footer($authuser,$db);
?>
