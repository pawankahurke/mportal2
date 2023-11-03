<?php







define('constRoot',        1);
define('constItem',        0);
define('constOrd',         1);
define('constNotOrd',      0);

function html_report_header($prompt, $data)
{
    return <<< HERE

<tr>
 <td valign="top" align="right">
  <font face="verdana,helvetica" size="3" color="333399">
   $prompt:
  </font>
 </td>
 <td valign="top">
  <font face="verdana,helvetica" size="3">
   $data
  </font>
 </td>
</tr>

HERE;
}


function html_rule()
{
    return <<< HERE

<hr color="#333399" noshade size="1" width="610" align="left">

HERE;
}


function html_header($title)
{
    $m  = '<html>';
    $m .= <<< HERE

<head>
 <title>
   Report: $title
 </title>
 <style type="text/css">
  .pagebreak { page-break-before: always }
 </style>
</head>

<body link="#333399" vlink="#660066" alink="#00FF00">

HERE;

    return $m;
}


function html_close_table()
{
    return "\n</table>\n";
}


function html_data($rows)
{
    $m = '';
    if ($rows) {
        reset($rows);
        foreach ($rows as $key => $row) {
            $t = $row['text'];
            $v = $row['valu'];
            $m .= html_report_header($t, $v);
        }
    }
    return $m;
}


function html_append(&$rows, $text, $valu)
{
    $tmp = array();
    $tmp['text'] = $text;
    $tmp['valu'] = $valu;
    $rows[] = $tmp;
}


function html_empty_row($span)
{
    return <<< HERE

<tr>
 <td colspan="$span">
   <br>
  </td>
</tr>

HERE;
}


function html_table($p, $s, $c, $b)
{
    return <<< HERE

<table cellpadding="$p" cellspacing="$s" bordercolor="$c" border="$b">

HERE;
}



function logo_iref($logo, $host)
{
    $xxx = $logo['xxx'];
    $yyy = $logo['yyy'];
    $src = $logo['src'];
    $src = $host . $src;
    return <<< HERE

<img border="0" width="$xxx" height="$yyy" src="$src">

HERE;
}


function html_page_title(&$env, &$row, $title)
{
    $iref    = $env['iref'];
    $link    = @$env['link'];
    $evnt    = $env['event'];
    $type    = ($evnt) ? 'event'  : 'item';
    $sect    = ($evnt) ? 'events' : 'table';
    $details = ($evnt) ? $row['details'] : $row['content'];
    $E       = ($details) ? " | \n <a href=\"#E\">$sect</a>" : '';
    $summary_links = '&nbsp;';

    $format = $row['format'];
    if (($format == 'mpie') || ($format == 'mcol') || ($format == 'mbar')) {
        $iref = $env['iref'];
    } else {
        $xxx = $env['xxx'];
        $yyy = $env['yyy'];
        $src = $env['src'];
        $iref = <<< HERE
<img border="0" width="$xxx" height="$yyy"
    src="$src">
HERE;
    }

    if ($link) {
        $href = $env['href'];
        $text = <<< HERE

   <a href="$href">
    $iref
   </a>

HERE;
    } else {
        $text = $iref;
    }
    if ((!$evnt) || (!$row['aggregate'])) {
        $summary_links = "<a href=\"#RS\">report summary</a> |
                          <a href=\"#ES\">$type summary</a> $E";
    }
    return <<< HERE

<table width="610" border="0">
<tr>
 <td rowspan="2" align="left" valign="bottom">
  <a name="top">
    $text
  </a>
 </td>
 <td align="right">
  <font face="verdana,helvetica" size="-1">
   $summary_links
  </font>
 </td>
</tr>
</table>

<br>
<br>

<p>
 <font face="verdana,helvetica" color="#333399" size="4">
   $title
 </font>
</p>

<br>

HERE;
}


function s_anchor($id, $key)
{
    return "summary_${id}_" . urlencode($key);
}


function e_anchor($id, $key)
{
    return "events_${id}_" . urlencode($key);
}



function file_links(&$env, &$row)
{
    $evnt    = $env['event'];
    $type    = ($evnt) ? 'event'  : 'item';
    $sect    = ($evnt) ? 'events' : 'table';
    $details = ($evnt) ? $row['details'] : $row['content'];
    $E       = ($details) ? " | \n <a href=\"#E\">$sect</a>" : '';
    $summary_links = '&nbsp;';
    if ((!$evnt) || (!$row['aggregate'])) {
        $summary_links  = "<a href=\"#RS\">report summary</a> |
                           <a href=\"#ES\">$type summary</a> $E";
    }

    return <<< HERE

<table width="610" border="0">
<tr>
 <td align="right">
  <font face="verdana,helvetica" size="-1">
   $summary_links
  </font>
 </td>
</tr>
</table>

<br>
<br>

HERE;
}

function html_order_params($order1, $order2, $order3, $order4)
{
    $rows = array();
    html_append($rows, 'Group by first', $order1);
    html_append($rows, 'Group by second', $order2);
    html_append($rows, 'Sort by', $order3);
    html_append($rows, 'Sort by', $order4);
    return html_data($rows);
}

function html_times($tmin, $tmax, $tnow, $hours)
{
    $rows = array();
    html_append($rows, 'Start Date', $tmin);
    html_append($rows, 'End Date', $tmax);
    html_append($rows, 'Report Date', $tnow);
    if ($hours > 0) {
        html_append($rows, 'Elapsed Time', "$hours hours");
    }
    return html_data($rows);
}

function html_stats($title, $user, $email)
{
    $rows = array();
    html_append($rows, 'Report Title', $title);
    html_append($rows, 'Creator', $user);
    html_append($rows, 'Recipients', $email);
    return html_data($rows);
}


function html_new_pagetable()
{
    return <<< HERE

<table class="pagebreak" cellpadding="3" cellspacing="0" bordercolor="COCOCO" border="1" width="610">

HERE;
}


function html_distinct($n, $names)
{
    return <<< HERE

<tr>
  <td colspan="3">
    <font face="verdana,helvetica" size="2" color="333399">
      Statistics are reported for <font color="000000">$n</font>
      distinct <font color="000000">$names</font>.
    </font>
  </td>
</tr>

HERE;
}


function html_reportsummary_subheader($iref, $name1, $name2, $format)
{
    $msg = '';
    if ($format != 'html') {
        $msg .= <<< HERE

<tr>
 <td colspan="3">
   $iref
 </td>
</tr>

HERE;
    }

    $msg .= <<< HERE

<tr>
 <td valign="top">
  <font face="verdana,helvetica" size="2" color="333399">
   <b>$name1</b>
  </font>
 </td>

 <td valign="top">
  <font face="verdana,helvetica" size="2" color="333399">
   <b>$name2</b>
  </font>
 </td>

 <td valign="top" align="right">
  <br>
 </td>
</tr>

HERE;

    return $msg;
}



function html_reportsummary_data($n, $key1, $data1, $link1, $id)
{
    $anchor = s_anchor($id, $key1);
    if (strlen($link1)) $key1 = $link1;

    return <<< HERE

<tr>
 <td valign="top">
  <font face="verdana,helvetica" size="2">
   $key1
  </font>
 </td>
 <td valign="top">
   <font face="verdana,helvetica" size="2">
    $data1
  </font>
 </td>
 <td valign="top" align="right">
  <font face="verdana,helvetica" size="2">
    <a href="#$anchor">view item summary</a>
   </font>
 </td>
</tr>

HERE;
}


function html_pagebreak($pagebreak)
{
    $m = '';
    if ($pagebreak == 'on') {
        $m .= html_close_table();
        $m .= "\n\n\n";
        $m .= '<br clear="all">';
        $m .= "\n";
        $m .= html_new_pagetable();
    }
    return $m;
}





function html_separator($span, $text)
{
    return <<< HERE

<tr>
 <td colspan="$span" bgcolor="C0C0C0" align="right">
  <font face="verdana,helvetica" size="1">
   <a href="#top">$text</a>
   </font>
 </td>
</tr>

HERE;
}


function html_eventsummary_header($event)
{
    $span = 3;
    $m  = html_separator($span, 'back to top');
    $m .= html_pagebreak('on');
    $m .= <<< HERE

<tr>
 <td colspan="$span" bgcolor="#333399">
  <a name="ES"></a>
  <font face="verdana,helvetica" color="white" size="2">
   <b>$event Summary</b>
  </font>
 </td>
</tr>

HERE;

    return $m;
}


function html_eventsummary_chart($key1, $iref, $format)
{
    $msg = '';

    if ($format != 'html') {
        $anchor = 'summary_' . urlencode($key1);
        $msg = <<< HERE

<tr>
 <td colspan="3">
   <a name="$anchor"></a>
   $iref
 </td>
</tr>

HERE;
    }
    return $msg;
}



function html_eventsummary_data($key, $data)
{
    return <<< HERE

<tr>
 <td valign="top">
  <br>
 </td>
 <td valign="top">
  <font face="verdana,helvetica" size="2">
   $key
  </font>
 </td>
 <td valign="top" align="right" nowrap>
  <font face="verdana,helvetica" size="2">
   $data items
  </font>
 </td>
</tr>

HERE;
}



function html_rlink($href, $text)
{
    return <<< HERE

<tr>
 <td valign="top">
  <br>
 </td>
 <td valign="top">
  <font face="verdana,helvetica" size="2">
   <a href="$href">
     $text
   </a>
  </font>
 </td>
</tr>

HERE;
}





function charting(&$row)
{
    switch ($row['format']) {
        case 'text':
            return false;
        case 'html':
            return false;
        case 'mpie':
            return true;
        case 'pie':
            return true;
        case 'mbar':
            return true;
        case 'bar':
            return true;
        case 'mcol':
            return true;
        case 'column':
            return true;
        default:
            return false;
    }
}




function saving(&$row)
{
    if ($row['file']) {
        $save = true;
    } else {
        switch ($row['format']) {
            case 'text':
                $save = false;
                break;
            case 'html':
                $save = false;
                break;
            case 'mpie':
                $save = false;
                break;
            case 'pie':
                $save = true;
                break;
            case 'mbar':
                $save = false;
                break;
            case 'bar':
                $save = true;
                break;
            case 'mcol':
                $save = false;
                break;
            case 'column':
                $save = true;
                break;
            default:
                $save = false;
                break;
        }
    }
    return $save;
}

function create_filesite($id, $site, $db)
{
    debug_note("create_filesite $id, $site");
    $qs  = safe_addslashes($site);
    $sql = "insert into " . $GLOBALS['PREFIX'] . "core.FileSites set\n"
        . " fid=$id,\n"
        . " sitename='$qs'";
    redcommand($sql, $db);
}


function write_file(&$env, &$row, $type, $days, $num, $sites)
{
    $mdb       = $env['mdb'];
    $root      = $env['root'];
    $tmpfile_h = $row['tmpfile_h'];
    $name = $row['name'];
    $user = $row['username'];
    $now  = time();
    $exp  = $now + ($days * 86400);
    $sql  = "insert into " . $GLOBALS['PREFIX'] . "core.Files set\n"
        . " username='$user',\n"
        . " name='$name',\n"
        . " type='$type',\n"
        . " created=$now,\n"
        . " expires=$exp,\n"
        . " counted=$num,\n"
        . " path='',\n"
        . " link=''";
    $id   = 0;
    $res  = redcommand($sql, $mdb);
    if ($res) {
        $id = mysql_insert_id($mdb);
    }
    $fyle = false;
    if ($id > 0) {
        $file  = sprintf('%08d.php', $id);
        $link  = "/main/files/$file";
        $path  = "$root/main/files/$file";
        $head  = '<' . "?php\n";
        $tail  = '?' . ">\n";
        $incl  = "    include ( 'include.php' );\n";
        $incl .= "    \$env = file_header($id);\n";
        $foot  = "    file_footer(\$env);\n";
        $incl  = $head . $incl . $tail . "\n\n";
        $foot  = "\n\n" . $head . $foot . $tail;
        $fyle  = fopen($path, 'wb+');
    }
    $good = false;
    if ($fyle && $tmpfile_h) {
        $good = append_file($fyle, $tmpfile_h, $incl);
        if ($good) {
            $good = my_write($fyle, $foot);
        }
        if ($good) {
            $good = fclose($fyle);
        } else {
            fclose($fyle);
            unlink($path);
        }
    }
    if ($good) {
        $sql = "update " . $GLOBALS['PREFIX'] . "core.Files set\n"
            . " path='$path',\n"
            . " link='$link'\n"
            . " where id = $id";
    } else {
        $sql = "delete from " . $GLOBALS['PREFIX'] . "core.Files\n"
            . " where id = $id";
    }
    if ($id > 0) {
        redcommand($sql, $mdb);
        if (($good) && ($sites)) {
            reset($sites);
            foreach ($sites as $cid => $site) {
                create_filesite($id, $site, $mdb);
            }
        }
    }
    return $good;
}


function report_image($format, $args, $label, $quality, $server)
{
    $img = array();
    if (safe_count($args) > 0) {
        $numbers = array();
        $names   = array();
        reset($args);
        foreach ($args as $key => $data) {
            $names[] = $key;
            $numbers[] = $data;
        }
        $img = chart_image($format, $numbers, $names, $label, $quality, $server);
        if ($img) {
            $xxx  = $img['xsize'];
            $yyy  = $img['ysize'];
            $cid  = $img['cid'];
            debug_note("image x:$xxx y:$yyy <$cid>");
        }
    }
    return $img;
}




function fixlink($path)
{
    $link = str_replace('../reports/', 'reports/', $path);
    return $link;
}


function path_iref(&$img, $base)
{
    $iref = '';
    if ($img) {
        $xxx  = $img['xsize'];
        $yyy  = $img['ysize'];
        $path = image_path($img);
        $link = fixlink($path);
        $src  = "$base/$link";
        $iref = <<< HERE
<img src="$src" width="$xxx" height="$yyy" border="0">
HERE;
    }
    return $iref;
}





function report_iref($row, $args, $label, $quality, $base, &$imgs, $server)
{
    $format = $row['format'];
    $img = report_image($format, $args, $label, $quality, $server);
    if (saving($row)) {
        $iref = path_iref($img, $base);
    } else {
        $iref = image_href($img);
        $imgs[] = $img;
    }
    return $iref;
}

function find_report($now, $name, $serv, $sites)
{
    $data = datestring($now);
    $sites = join("<br>\n", $sites);

    return <<< HERE
<html>
<body>
  Please find the attached report.<br><br>
  Server: $serv<br>
  Creation Date: $data<br>
  File Name: $name<br>
</body>
</html>
HERE;
}




function report_mail(&$row, $dst, $sub, $tmpfile_h, $src, &$imgs, $server, $sites)
{
    $good     = false;
    $format   = $row['format'];
    switch ($format) {
        case 'text':
            $from = "From: $src";
            $good = sendmail($dst, $from, $sub, $tmpfile_h, '');
            break;
        case 'html':;
        case 'pie':;
        case 'bar':;
        case 'column':;
            $now  = time();
            $uname = $row['username'];
            $uname = "_User_$uname";
            $name = trim($row['name']);
            $name = str_replace(' ', '_', $name);
            $uname = str_replace(' ', '_', $uname);
            $name = $name . $uname;
            $name = date('Y-m-d', $now) . "_$name.html";
            $now  = time();
            $file = mime_filename($name);
            $sum  = find_report($now, $file, $server, $sites);
            $good = mime_mail($dst, $sub, $src, $sum, $file, $tmpfile_h);
            debug_note("l-rtxt: report_mail,mime_mail good:($good)");
            break;
        case 'mbar':;
        case 'mpie':;
        case 'mcol':;
            $good = mhtml_mail($dst, $sub, $tmpfile_h, $src, $imgs);
            debug_note("l-rtxt: report_mail,mhtml_mail good:($good)");
            break;
        default:;
            $msg = "report_mail: bad format: $format";
            debug_note($msg);
    }
    return $good;
}




function array_to_xml($header, $data)
{
    reset($header);
    reset($data);

    $new_site    = 1;
    $new_machine = 1;

    $group      = false;

    $machines   = '<machines>';
    $e_machines = '</machines>';
    $e_machine  = '</machine>';
    $items      = '<items>';
    $e_items    = '</items>';
    $e_sites    = '</sites>';
    $e_site     = '</site>';


    $str  = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>" . "\n";
    $str .= '<sites>' . "\n";

    $c_header = safe_count($header);
    $f_header = array_flip($header);
    $data     = arrange_sequential($data);

    reset($data);
    foreach ($data as $mid => $set) {
        $current_ordinal = 0;
        $ordinal_type    = constNotOrd;

        $ord_keys = safe_array_keys($set);
        sort($ord_keys);

        reset($ord_keys);
        reset($set);
        foreach ($set as $ordinal => $site) {
            $actual_ord = $ord_keys[$current_ordinal];

            $c_actual_ord = safe_count($ord_keys);

            if (isset($set[$actual_ord])) {
                $next_site_same = false;
                $next_same      = false;


                $current_site = $set[$actual_ord];


                if (isset($data[$mid + 1][1][0])) {
                    if ($data[$mid + 1][1][0] == $current_site[0])
                        $next_site_same = true;
                }


                if (($current_ordinal + 1 < $c_actual_ord) &&
                    (isset($set[$ord_keys[$current_ordinal + 1]]))
                ) {
                    $next_same = true;
                }

                $index = 0;
                if ($new_site) {
                    $i_name = $f_header[$index];
                    $i_data = $current_site[$index];
                    $str   .= return_row($i_name, $i_data, $group, constRoot, constNotOrd, 0);
                    $str   .= e_nl($machines);
                }
                if ($new_machine) {
                    $index++;
                    $i_name = $f_header[$index];
                    $i_data = $current_site[$index];
                    $str   .= return_row($i_name, $i_data, $group, constRoot, constNotOrd, 0);
                    $str   .= e_nl($items);
                }
                for ($c = 2; $c < $c_header; $c++) {

                    $i_data = false;
                    $i_name = $f_header[$c];

                    if (isset($current_site[$c]))
                        $i_data = $current_site[$c];

                    $next_ord_set = check_ordinal($set, $c, $ord_keys, $current_ordinal, 1);
                    $prev_ord_set = check_ordinal($set, $c, $ord_keys, $current_ordinal, -1);

                    if (($next_ord_set) || ($prev_ord_set))
                        $ordinal_type = constOrd;
                    else
                        $ordinal_type = constNotOrd;

                    if ($i_data)
                        $str .= return_row($i_name, $i_data, $group, constItem, $ordinal_type, $actual_ord);
                }


                if ($next_same) {
                    $new_site    = 0;
                    $new_machine = 0;
                } else if ($next_site_same) {
                    $new_site    = 0;
                    $new_machine = 1;
                    $str .= e_append($e_items, $e_machine);
                } else {
                    $new_site    = 1;
                    $new_machine = 1;

                    $str .= e_append($e_items,    $e_machine);
                    $str .= e_append($e_machines, $e_site);
                }
            }
            $current_ordinal++;
        }
    }
    $str .= e_nl($e_sites);
    return $str;
}



function return_row($f_header, $row, $group, $type, $ord, $count)
{
    $gt     = '<';
    $lt     = '>';
    $eqlbsl = "=\"";
    $bsl    = "\"";
    $ntag   = 'name';
    $gtag   = 'group';
    $nitem  = 'item';
    $sp     = ' ';
    $br     = "\n";
    $e_item = '</item>';
    $otag     = ($ord) ? $sp . "ordinal=\"$count\"" : '';
    $group    = ($group) ? $sp . $gtag . $eqlbsl . $group . $bsl : '';
    $f_header = strtolower($f_header);
    $row      = validate_xml($row);
    return ($type) ? $gt . $f_header . $sp . $ntag . $eqlbsl . $row . $bsl . $lt . $br
        : $gt . $nitem . $sp . $ntag  . $eqlbsl  . $f_header . $bsl
        . $otag  . $group . $lt . $row . $e_item . $br;
}



function validate_xml($row)
{
    $ixml = invalid_xml_characters();
    reset($ixml);
    foreach ($ixml as $ichar => $rchar) {
        $row = str_replace($ichar, $rchar, $row);
    }
    return $row;
}



function invalid_xml_characters()
{
    return array(
        '<' => '&lt;',
        '>' => '&gt;',
        '&' => '&amp;',
        "'" => '&apos;',
        '"' => '&quot;'
    );
}



function check_ordinal(&$set, $c, &$ord_keys, &$current_ordinal, $pos)
{
    if (isset($ord_keys[$current_ordinal + $pos])) {
        $ord_to_check = $ord_keys[$current_ordinal + $pos];
        return isset($set[$ord_to_check][$c]);
    }
    return false;
}


function arrange_sequential($data)
{
    $out = array();
    reset($data);
    foreach ($data as $mid => $set) {
        $out[] = $set;
    }
    return $out;
}


function e_append($item1, $item2)
{
    return (e_nl($item1) . e_nl($item2));
}


function e_nl($item)
{
    return "\n" . $item;
}



function build_filename($string, $qid, $username, $db)
{
    $finished = '';
    for ($c = 0; $c < strlen($string); $c++) {
        if ($string[$c] == '%') {
            if (isset($string[$c + 1])) {
                $t_format = $string[$c + 1];

                switch ($t_format) {
                    case 'q':
                        $t_date = fetch_queryname($qid, $db);
                        break;
                    case 'u':
                        $t_date = $username;
                        break;
                    case 'i':;
                    case 'h':;
                    case 'j':;
                    case 'm':;
                    case 'd':;
                    case 'Y':
                        $t_date = date($t_format);
                        break;
                    default:
                        $t_date = $t_format;
                }
                $finished .= $t_date;

                $c++;
            }
        } else
            $finished .= $string[$c];
    }
    $finished = mime_filename($finished);
    return $finished;
}


function fetch_queryname($qid, $db)
{
    $out  = '';
    $sql  = "select name from AssetSearches where id = $qid";
    $srch = find_one($sql, $db);
    if ($srch) {
        $out = $srch['name'];
    }
    return $out;
}
