<?php

/*
Revision history:

Date        Who     What
----        ---     ----
19-Sep-02   EWB     Giant refactoring.
20-Sep-02   EWB     8.3 library names.
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navagation
30-Dec-02   EWB     Single quotes for non evaluated string literals
31-Dec-02   EWB     Do not require register-globals.
 1-Jan-03   EWB     Load refresh also by get_argument().
16-Jan-03   EWB     Access to $_SERVER variables.
 7-Feb-03   EWB     Use new database scheme.
11-Feb-03   EWB     db_change()
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed $legend to standard_html_header()
14-Apr-03   NL      Move debug_note line below $debug.
14-Apr-03   NL      Consolidate "Main program" section.
15-Apr-03   EWB     factored out the jumptable.
17-Apr-03   EWB     order by site.
24-Apr-03   EWB     echo jumptable.
29-Apr-03   EWB     Always include empty sites in result.
30-Apr-03   EWB     user sitefilter bits.
22-May-03   EWB     Quote Crusade
18-Jun-03   EWB     Slave Database.
18-Jun-03   EWB     select servertime between
20-Jun-03   EWB     No Slave Database.
30-Jul-03   EWB     Uses html_target.
25-Nov-03   NL      Change $ne from safe_count($events) to $row['count'] for zero-events
                    (which are generated one per machine).
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
                    
*/

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-page.php'  );
include_once ( '../lib/l-date.php'  );
include_once ( '../lib/l-ntfy.php'  );
include_once ( '../lib/l-slct.php'  );
include_once ( '../lib/l-jump.php'  );
//  include ( '../lib/l-slav.php'  );
include_once ( 'local.php'   );
include_once ( '../lib/l-head.php'  );

    function shortdate($utime)
    {
        $msg = "Never";
        if ($utime < 0x7fffffff)
        {
            $date = getdate($utime);
            $year = $date['year'];
            $mon  = $date['mon'];
            $day  = $date['mday'];
            $msg  = sprintf("%02d/%02d",$mon,$day);
            if (assumeyear($mon) != $year)
                $msg .= sprintf("/%02d",$year % 100);
            if (($date['hours']) || ($date['minutes']))
                $msg .= sprintf(" %02d:%02d",$date['hours'],$date['minutes']);
        }
        return $msg;
    }


    function green($text)
    {
        return "<font color=\"green\">$text</font>";
    }

    function appendarg($link,$arg)
    {
        if (strlen($link))
            $link = "$link&$arg";
        else
            $link = $arg;
        return $link;
    }

   /*
    |   Returns the original array, except that
    |   the empty elements have been filtered out.
    */

    function filter($p)
    {
        $list = array( );
        $n = safe_count($p);
        for ($i = 0; $i < $n; $i++)
        {
            $elem = $p[$i];
            if (strlen($elem))
                $list[] = $elem;
        }
        return $list;
    }


   /*
    |  We store the event list as a comma separated
    |  list with leading and trailing commas.  This
    |  returns an array with the actual list, without
    |  the empty elements.
    */

    function buildlist($delim,$elist)
    {
        $p = explode($delim,$elist);
        $list = filter($p);
        return $list;
    }


    function findevents($db,$elist)
    {
        $result = FALSE;
        $n = safe_count($elist);
        if ($n > 0)
        {
            $el  = implode(",",$elist);
            $sql = "select * from Events where idx in ($el)";
            $result = command($sql,$db);
        }
        return $result;
    }

    function image($image,$width,$height)
    {

        return <<< HERE
        <img src="../pub/$image" width="$width" height="$height" border="0">
HERE;

    }

    function event_detail($index)
    {
        $link = image('detail.gif',33,14);
        $href = "detail.php?eid=$index";
        return html_page($href,$link);
    }

    function console_detail($id)
    {

        $link = image('detail.gif',33,14);
        $href = "cnsl-det.php?id=$id";
        return html_page($href,$link);
    }

    function console_order($ord)
    {
        switch ($ord)
        {
            case  0: return 'site, priority asc, servertime desc';
            case  1: return 'site, priority desc, servertime desc';
            case  2: return 'site, servertime desc, priority';
            case  3: return 'site, servertime asc, priority';
            case  4: return 'site, name asc, servertime desc';
            case  5: return 'site, name desc, servertime desc';
            case  6: return 'site, count asc, priority, servertime desc';
            case  7: return 'site, count desc, priority, servertime desc';
            case  8: return 'site, expire desc, priority, servertime desc';
            case  9: return 'site, expire asc, priority, servertime desc';
            case 10: return 'priority asc, servertime desc';
            case 11: return 'priority desc, servertime desc';
            case 12: return 'servertime desc, priority';
            case 13: return 'servertime asc, priority';
            case 14: return 'name asc, servertime desc';
            case 15: return 'name desc, servertime desc';
            case 16: return 'count asc, priority, name, servertime';
            case 17: return 'count desc, priority, name, servertime';
            case 18: return 'expire desc, priority, name, servertime';
            case 19: return 'expire asc, priority, name, servertime';
            default: return 'site, priority asc, servertime desc';
        }
    }


    function console_sort($ord)
    {
        $order = array(
            'priority','priority',
            'servertime','servertime',
            'name','name',
            'count','count',
            'expire','expire',
            'priority','priority',
            'servertime','servertime',
            'name','name',
            'count','count',
            'expire','expire');
        return $order[$ord];
    }



    function console_choice()
    {
        $order = array( );
        $order[0] = 'site, increasing priority, time';
        $order[1] = 'site, decreasing priority, time';
        $order[2] = 'site, decreasing time, priority';
        $order[3] = 'site, increasing time, priority';
        $order[4] = 'site, name, time';
        $order[5] = 'site, reverse name, time';
        $order[6] = 'site, increasing count, priority, time';
        $order[7] = 'site, decreasing count, priority, time';
        $order[8] = 'site, decreasing expire, priority, time';
        $order[9] = 'site, increasing expire, priority, time';
        $order[10] = 'increasing priority, time';
        $order[11] = 'decreasing priority, time';
        $order[12] = 'decreasing time, priority';
        $order[13] = 'increasing time, priority';
        $order[14] = 'name, time';
        $order[15] = 'reverse name, time';
        $order[16] = 'increasing count, priority, time';
        $order[17] = 'decreasing count, priority, time';
        $order[18] = 'decreasing expire, priority, time';
        $order[19] = 'increasing expire, priority, time';
        return $order;
    }


    function action($id,$nid)
    {
        $a = array( );
        $act = 'cnsl-act.php?action';
        $a[] = html_link("$act=edit&id=$id",'[edit]');
        $a[] = html_link("$act=confirmdelete&id=$id",'[delete]');
        $a[] = html_link("$act=confirmpurge&nid=$nid",'[purge]');
        $a[] = html_link("$act=confirmsuspend&id=$id",'[suspend]');
        return join("&nbsp;&nbsp;\n",$a);
    }



    function table_row($msg,$color)
    {
        $bgcolor = ($color)? " bgcolor=\"$color\"" : '';
        return "<tr$bgcolor>\n$msg\n</tr>\n";
    }

    function td($msg,$width,$color)
    {
        $colspan = ($width > 1)? " colspan=\"$width\"" : '';
        $bgcolor = ($color)?     " bgcolor=\"$color\"" : '';
        return "<td$bgcolor$colspan>\n$msg\n</td>\n";
    }

    function table_col($msg,$width)
    {
        return td($msg,$width,'');
    }

    function table_head($msg,$color)
    {
        $bgcolor = (strlen($color)) ? " bgcolor=\"$color\"" : '';
        return "<th$bgcolor>\n$msg\n</td>\n";
    }

    function show_events($db,$events,$config,$color)
    {
        $ne = safe_count($events);
        $nc = safe_count($config);
        if ($ne > 0)
        {
            $details = findevents($db,$events);
            if ($details)
            {
                $nd = mysqli_num_rows($details);
                $bgcolor = ($color)? " bgcolor=\"$color\"" : '';
                echo "<table$bgcolor border=\"2\" align=\"left\" cellspacing=\"2\" cellpadding=\"2\" width=\"98%\">\n";
                if ($nd == 0)
                {
                    $msg = 'No events found for this console notification.';
                    $msg = table_col($msg,1);
                    echo table_row($msg,'');
                }
                else
                {
                    if ($nc > 0)
                    {
                        $msg = table_head('<br>','');
                        for ($i = 0; $i < $nc; $i++)
                        {
                            $head = $config[$i];
                            $msg .= table_head($head,'');
                        }
                        echo table_row($msg,'');
                    }
                    while ($ev = mysqli_fetch_array($details))
                    {
                        $link  = event_detail($ev['idx']);
                        $msg   = table_col($link,1);
                        for ($i = 0; $i < $nc; $i++)
                        {
                            $key  = $config[$i];
                            $data = $ev[$key];
                            if (is_string($data))
                            {
                                if (strlen($data))
                                    $data = nl2br($data);
                                else
                                    $data = '<br>';
                            }
                            $msg .= table_col($data,1);
                        }
                        echo table_row($msg,'');
                    }
                }
                echo "</table>\n";
                ((mysqli_free_result($details) || (is_object($details) && (get_class($details) == "mysqli_result"))) ? true : false);
            }
        }
    }


   /*
    |  Most days are 24 hours.  However April has a single
    |  day of 23 hours, and October has one of 25 hours.
    |
    |  Sun Apr  1 2001 (23 hours) 1:59 EST -> 3:00 AM EDT
    |  Sun Oct 28 2001 (25 hours) 1:59 EDT -> 1:00 AM EST
    |
    |  No such time as 2:30 AM, Sunday April 1st 2001.
    */

    function midnight($tdate)
    {
        $hour  = (60*60);
        $tday  = getdate($tdate);
        $delta = $tday['seconds'] + (60 * ($tday['minutes'] + (60 * $tday['hours'])));
        if ($delta <= 0)
            $ydate = $tdate;
        else
        {
            $ydate  = $tdate - $delta;
            $yday   = getdate($tdate);
            $ydate += (($tday['hours'] - $yday['hours']) * $hour);
        }
        return $ydate;
    }

    function image_link($link,$align,$image,$w,$h)
    {
        $ln  = "<td align=\"$align\" valign=\"top\">\n";
        $ln .= "   <a href=\"$link\">\n";
        $ln .=         image($image,$w,$h);
        $ln .= "   </a>";
        $ln .= "</td>";
        return $ln;
    }

    function image_cell($align,$image,$w,$h)
    {
        $ln  = "<td align=\"$align\" valign=\"top\">\n";
        $ln .=     image($image,$w,$h);
        $ln .= "</td>";
        return $ln;
    }

    function prevnext($page,$cnt,$query)
    {
        $self = server_var('PHP_SELF');
        if ($query)
        {
            $link = "$query&";
            $link = preg_replace('/ndx=-*[0-9]*[&]*/',  '', $link);
            $link = preg_replace('/cnt=-*[0-9]*[&]*/',  '', $link);
            $link = preg_replace('/&$/',                '', $link);
        }
        else
            $link = '';

        echo '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
        echo '<tr>';

        $cntarg = '';
        if ($cnt != 20) $cntarg = "&cnt=$cnt";

        if ($page['previous']['exists'])
        {
            $prevstart = $page['previous']['start'];
            $prevlink  = appendarg($link,"ndx=$prevstart$cntarg");
            echo image_link("$self?$prevlink","left",'previous.gif',68,22);
        }
        else
        {
            echo image_cell('left','previous-gray.gif',68,22);
        }
        if ($page['next']['exists'])
        {
            $nextstart = $page['next']['start'];
            $nextlink = appendarg($link,"ndx=$nextstart$cntarg");
            echo image_link("$self?$nextlink",'right','next.gif',47,22);
        }
        else
        {
            echo image_cell('right','next-gray.gif',47,22);
        }
        echo "</tr></table>";
    }


/*
    function table_key($colors)
    {
        $n = safe_count($colors);
        if ($n > 0)
        {
            $msg = '';
            echo "<table border='2' align='center' cellspacing='2' cellpadding='2' width='100%'>\n";
            for ($i = 0; $i < $n; $i++)
            {
                $data = ($i) ? "Priority $i" : "Details";
                $data = fontspeak($data);
                $msg .= td($data,1,$colors[$i]);
            }
            echo table_row($msg,'');
            echo "</table>\n";
        }
    }

*/

    function umin($now,$days)
    {
        $delta = ($days * 24 * 3600);
        return midnight($now - $delta);
    }


   /*
    |  We can go back up to three weeks in the event log.
    |  We use noon just so we don't need to worry about
    |  daylight savings time.
    */

    function dayoptions($now)
    {
        $hour = 3600;
        $midn = midnight($now);
        $day  = 24 * $hour;
        $noon = $midn + (12 * $hour);
        $n    = 22;
        for ($i = 0; $i < $n; $i++)
        {
            $xday     = getdate($noon);
            $date[$i] = sprintf("%s %02d/%02d",$xday['weekday'],$xday['mon'],$xday['mday']);
            $xopt[$i] = "$i days";
            $noon = $noon - $day;
        }
        $xopt[0]  = "Today";
        $xopt[1]  = "Yesterday";
        $xopt[7]  = "1 Week";
        $xopt[14] = "2 Weeks";
        $xopt[21] = "3 Weeks";
        for ($i = 0; $i < $n; $i++)
        {
            $d = $date[$i];
            $xopt[$i] .= " ($d)";
        }
        return $xopt;
    }


    function control_table($now,$days,$cnt,$exp,$dmin,$dmax,$ord,$debug,$priv_debug,$refresh)
    {
        $self = server_var('PHP_SELF');
        echo jumptable("top,bottom,control,data");
        echo "<form action='$self' method='get'>\n";

        echo "<table border='2' align='left' cellspacing='2' cellpadding='2'>\n";

        $xmin = '';
        $xmax = '';

        if (strlen($dmin)) $xmin = " value='$dmin'";
        if (strlen($dmax)) $xmax = " value='$dmax'";

        // mm/dd/yyyy hh:mm:ss
        // 1234567890123456789

        $common = "input size='19' maxlength='19'";
        $imin = "<$common$xmin name='dmin'>";
        $imax = "<$common$xmax name='dmax'>";

        $doc  = "Date should be mm/dd or mm/dd/yy or mm/dd/yyyy.<br>";
        $doc .= "Time is optional and should be hh:mm or hh:mm:ss.<br>";
        $doc .= "Time is midnight unless otherwise specified";
        $doc  = "<i>$doc</i>";
        $msg  = table_col("Start:",1);
        $msg .= table_col($imin,1);
        $msg .= table_col($doc,1);
        echo table_row($msg,'');

        $doc  = "Ending time should be later than starting time.<br>";
        $doc .= "Defaults to midnight tonight if not specified.";
        $doc  = "<i>$doc</i>";
        $msg  = table_col('End:',1);
        $msg .= table_col($imax,1);
        $msg .= table_col($doc,1);
        echo table_row($msg,'');

        $doc  = "Selects all records from the specified start time until midnight tonight.<br>";
        $doc .= "This is only used when the date range above is invalid or unspecified.";
        $opt  = dayoptions($now);
        $sel  = html_select('days',$opt,$days,1);
        $msg  = table_col("Days:",1);
        $msg .= table_col($sel,1);
        $msg .= table_col("<i>$doc</i>",1);
        echo table_row($msg,'');

        $doc  = "Specifies how to sort console events.";
        $opt  = console_choice();
        $sel  = html_select('ord',$opt,$ord,1);
        $msg  = table_col('Sort:',1);
        $msg .= table_col($sel,1);
        $msg .= table_col("<i>$doc</i>",1);
        echo table_row($msg,'');

        $doc  = "Turns on the expanded display, which includes the event list details.";
        $opt  = array('No', 'Yes');
        $sel  = html_select('exp',$opt,$exp,1);
        $msg  = table_col('Expand:',1);
        $msg .= table_col($sel,1);
        $msg .= table_col("<i>$doc</i>",1);
        echo table_row($msg,'');

        $doc  = "Controls how many rows to display at once.<br>";
        $doc .= "Smaller is better when viewing expanded details.";
        $opt  = array(5, 10, 15,  20, 25, 50, 75, 100, 150);
        $sel  = html_select("cnt",$opt,$cnt,0);
        $msg  = table_col('Rowsize:',1);
        $msg .= table_col($sel,1);
        $msg .= table_col("<i>$doc</i>",1);
        echo table_row($msg,'');

        $opt  = array('never','1','5','10','15');
        $sel  = html_select("refresh",$opt,$refresh,0);
        $doc  = "How often to refresh screen (in minutes).";
        $doc  = "<i>$doc</i>";
        $msg  = table_col('Refresh:',1);
        $msg .= table_col($sel,1);
        $msg .= table_col($doc,1);
        echo table_row($msg,'');

        $sub  = "<input type=\"submit\" name=\"submit\" value=\"submit\">";
        $sub .= "&nbsp;&nbsp;&nbsp;<input type=\"reset\" value=\"reset\">";
        $msg = table_col($sub,3);
        echo table_row($msg,'');
        if ($priv_debug)
        {
            $opt  = array('No', 'Yes');
            $sel  = html_select("debug",$opt,$debug,1);
            $doc  = "Enable debugging messages in new screen.";
            $doc  = "<i>$doc</i>";
            $msg  = table_col(green('$debug'),1);
            $msg .= table_col($sel,1);
            $msg .= table_col($doc,1);
            echo table_row($msg,'');
        }

        echo "</table>\n\n";
        echo "<br clear=\"all\">\n\n";
        echo jumptable('top,bottom,control,data');
        echo "</form>\n\n";
        echo "<br>\n";
        echo "<br>\n";
    }


   /*
    |  Main program
    */

    $refresh = get_string('refresh','never');
    $text = '';
    if ($refresh > 0)
    {
        $secs = 60 * $refresh;
        $text = "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n"
              . "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"$secs\">\n";
    }
    $refreshtime = $text;

    $title  = 'Notification Console';
    $legend = '../pub/priority.gif';

    $now  = time();
    $umin = 0;
    $umax = 0;

    $cnt  = get_integer('cnt', 20);
    $ndx  = get_integer('ndx',  1);
    $exp  = get_integer('exp',  0);
    $days = get_integer('days', 0);
    $dbg  = get_integer('debug',1);
    $ord  = get_integer('ord',  0);
    $dmin = get_string('dmin', '');
    $dmax = get_string('dmax', '');

    if ($dmin != '') $umin = parsedate($dmin,$now);
    if ($dmax != '') $umax = parsedate($dmax,$now);
    if ($umin == 0)  $umin = umin($now,$days);
    if ($umax == 0)  $umax = umin($now,-1);
    $unow = $now;

    $db = db_connect();

    $authuser = process_login($db);
    $comp = component_installed();
    $user = user_data($authuser,$db);
    $priv_debug = @ ($user['priv_debug'])?  1 : 0;
    $filter     = @ ($user['filtersites'])? 1 : 0;
    $debug = ($priv_debug)? $dbg : 0;

    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer)
    echo standard_html_header($title,$comp,$authuser,0,0,$legend,$db);
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users


    $vnow = datestring($unow);
    $vmin = datestring($umin);
    $vmax = datestring($umax);

   /*
    |  We don't really need to do this, but why pass
    |  along a lot of extra garbage which consists
    |  of default or excess values.
    */

    $link  = '';
    $query = server_var('QUERY_STRING');
    if ($query)
    {
        $link = "$query&";
        $link = str_replace('days=0&',         '', $link);
        $link = str_replace('ndx=1&',          '', $link);
        $link = str_replace('exp=0&',          '', $link);
        $link = str_replace('dmin=&',          '', $link);
        $link = str_replace('dmax=&',          '', $link);
        $link = str_replace('debug=0&',        '', $link);
        $link = str_replace('submit=submit&',  '', $link);
        $link = str_replace('refresh=never&',  '', $link);
        $link = preg_replace('/&$/',           '', $link);
    }

    $header = array ('servertime', 'priority', 'name', 'count','expire', 'site', 'action');
    $hnames = array ('When', 'Priority', 'Name', 'Count', 'Expiration date', 'Site', 'Action');
    $n = safe_count($header);
    for ($i = 0; $i < $n; $i++)
    {
        $name  = $header[$i];
        $srtclr[$name] = 'white';
        $srtdir[$name] = 'asc';
    }

    $srtclr['action']    = '';

    $sort = console_sort($ord);

    $srtclr[$sort] = '#99CCFF';

    $usort = "$link&";
    $usort = preg_replace('/\bord=[0-9]*[&]*/',  '', $usort);
    $usort = preg_replace('/&$/',                   '', $usort);
    $self  = server_var('PHP_SELF');


    $up['priority'] = 0;
    $dn['priority'] = 1;
    $up['servertime'] = 2;
    $dn['servertime'] = 3;
    $up['name'] = 4;
    $dn['name'] = 5;
    $up['count'] = 6;
    $dn['count'] = 7;
    $up['expire'] = 8;
    $dn['expire'] = 9;
    $up['site'] = 11;
    $dn['site'] = 13;

    $n = safe_count($header);
    for ($i = 0; $i < $n-1; $i++)
    {
        $name  = $header[$i];
        $title = $hnames[$i];
        $tmp   = $up[$name];
        $xxx   = ($ord == $tmp)? $dn[$name] : $up[$name];
        $args  = appendarg($usort,"ord=$xxx");
        $action[$name] = html_link("$self?$args",$title);
    }

    $action['site']   = 'Site';
    $action['action'] = 'Action';

    if ($debug)
    {
        echo '<font size="2" color="green">';
        echo " dmin:$dmin<br>\n";
        echo " dmax:$dmax<br>\n";
        echo " umin:$umin  vmin:$vmin<br>\n";
        echo " umax:$umax  vmax:$vmax<br>\n";
        echo " unow:$unow  vnow:$vnow<br>\n";
        echo " cnt:$cnt, ndx:$ndx, exp:$exp, ord:$ord, days:$days<br>\n";
        echo "</font>\n";
    }

    if ($umax <= $umin)
    {
        $err  = "Error -- Starting date must be before ending date<br>\n";
        $err .= "Begin: $vmin<br>\n";
        $err .= "End: $vmax<br>\n";
        echo fontspeak("<b>$err</b>");
    }

    $carr = site_array($authuser,$filter,$db);
    $num  = 0;
    db_change($GLOBALS['PREFIX'].'event',$db);
    $order = console_order($ord);
    if ($carr)
    {
        $carr[] = '';
        $access = db_access($carr);
        $max  = $umax - 1;
        $sql  = "select * from Console\n";
        $sql .= " where username = '$authuser' and\n";
        $sql .= " site in ($access) and\n";
        $sql .= " servertime between $umin and $max\n";
        $sql .= " order by $order";
    }
    else
    {
        $sql  = "select * from Console\n";
        $sql .= " where servertime < 0";
    }
    $res = redcommand($sql,$db);

    if (!$res)
    {
        echo mark('data');
        $msg  = "<p>mysql Query Failed</p>";
        echo fontspeak($msg);
  //    echo mark('key');
    }
    else
    {
        $num = mysqli_num_rows($res);

        if ($num <= 0)
        {
            echo mark('data');
            $msg  = '<p>No notifications were found in this date range.<br>';
            $msg .= "$vmin<br>";
            $msg .= "$vmax</p>";
            echo fontspeak($msg);
   //       echo mark('key');
        }
        else
        {
            $page = paginate($ndx, $cnt, $num);
            $emin = $page['current']['start'];
            $emax = $page['current']['end'];
            $imax = $page['current']['size'];

            echo mark('data');

            echo "
                <p>
                    <font face=\"verdana,helvetica\" size=\"3\">
                        <i>
                            Events $emin through $emax (of $num)<br>
                            From $vmin to $vmax
                        </i>
                    </font>
                </p>";

            echo jumptable('top,bottom,control,data');

            prevnext($page,$cnt,$link);

            echo "<table border='2' align='left' cellspacing='2' cellpadding='2' width='100%'>\n";

            $n    = safe_count($header);
            $msg  = table_head('&nbsp;','');
            for ($i = 0; $i < $n ; $i++)
            {
                $name  = $header[$i];
                $title = $action[$name];
                $msg  .= table_head($title,$srtclr[$name]);
            }
            echo table_row($msg,'');

            # seek to the starting row
            mysqli_data_seek($res,  $page['current']['start'] - 1);

            $i = 0;
            while (($row = mysqli_fetch_array($res)) && ($i < $imax))
            {
                $id        = $row['id'];
                $nid       = $row['nid'];
                $priority  = $row['priority'];
                $name      = $row['name'];
                $site      = $row['site'];
                $when      = shortdate($row['servertime']);
                $expire    = shortdate($row['expire']);
                $color     = $priorities[$priority];
                $events    = buildlist(',',$row['event_list']);
                $config    = buildlist(':',$row['config']);
                $action    = action($id,$nid);
                //$ne        = safe_count($events); // not for zero-event notifications
                $ne        = $row['count'];
                $nc        = safe_count($config);
                $count     = ($ne == 1) ? '1 event' : "$ne events";
                $detail    = console_detail($id);
                if ($site == '') $site = '<br>';

                $msg  = table_col($detail,1);
                $msg .= table_col($when,1);
                $msg .= table_col($priority,1);
                $msg .= table_col($name,1);
                $msg .= table_col($count,1);
                $msg .= table_col($expire,1);
                $msg .= table_col($site,1);
                $msg .= table_col($action,1);
                echo table_row($msg,$color);
                if ($exp)
                {
                    echo "<tr><td><br></td><td colspan='7'>";
                    show_events($db,$events,$config,$priorities[0]);
                    echo "</td></tr>";
                }
                $i++;
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
            echo "</table>\n";
            echo "<br clear='all'>\n";
   //       echo mark('key');
   //       table_key($priorities);
            prevnext($page,$cnt,$link);
        }
    }

    echo "<br clear=\"all\">\n";

    echo mark('control');
    control_table($now,$days,$cnt,$exp,$dmin,$dmax,$ord,$debug,$priv_debug,$refresh);

    echo head_standard_html_footer($authuser,$db);
?>


