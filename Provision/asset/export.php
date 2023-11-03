<?php

/*
Revision history:

Date        Who     What
----        ---     ----
29-Apr-04   EWB     Created.
28-Apr-04   EWB     Export machine asset database command.
29-Apr-04   EWB     CSV Format.
30-Apr-04   EWB     Export by Query.
 3-May-04   EWB     Attached CSV file.
17-Nov-04   BJS     Added Export to Excel.
01-Dec-04   BJS     Added Export xhost to Excel.
16-Dec-04   BJS     Fixed 'multiple' to actual values.
22-Dec-04   BJS     Added export to SQL.
18-Jan-05   BJS     Minor fixes.
20-Jan-05   BJS     Display to screen (debug) added. SQL formating.
 2-Feb-05   BJS     Cosmetic change for Alex.
10-Aug-05   BJS     Added Export to XML.
15-Aug-05   BJS     XML export improvements.
18-Aug-05   BJS     XML procs moved to l-rtxt.php.
                    added include l-rtxt.php.
 6-Sep-05   BJS     added create_pidtable_name().
 7-Sep-05   BJS     removed drop_selected_table().
12-Sep-05   BJS     Added empty_access();
16-Dec-05   BJS     Fixed JavaScript syntax.
12-Jan-06   BJS     Added l-cnst.php
23-Mar-08   BTE     Bug 4433: Add export function to ad-hoc asset queries.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-user.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-qtbl.php');
include('../lib/l-alib.php');
include('../lib/l-dids.php');
include('../lib/l-jump.php');
include('../lib/l-slct.php');
//  include ( '../lib/l-slav.php'  );
include('../lib/l-cmth.php');
include('../lib/l-qury.php');
include('../lib/l-mime.php');
include('../lib/l-head.php');
include('../lib/l-tabs.php');
include('../lib/l-form.php');
include('../lib/l-dsql.php');
include('../lib/l-rtxt.php');
include('../lib/l-abld.php');
include('../lib/l-grps.php');
include('../lib/l-cnst.php');
include('../lib/l-errs.php');
include('local.php');

define('constButtonExcel', 'Excel');
define('constButtonSQL',   'SQL');
define('constButtonXML',   'XML');

function min_html_header()
{
    echo <<< HERE
    <html>
    <head>
    <title>
        Export to Excel
    </title>
    </head>
    <body scroll = yes>
HERE;
}

function min_html_footer()
{
    echo <<< HERE
    </body>
    </html>
HERE;
}

function window_spec($x, $y)
{
    echo <<< HERE
    <script language="JavaScript">
    <!--
        self.moveTo(150,150)
        self.resizeTo($x,$y)
    // -->
    </script>
HERE;
}

function return_browser()
{
    $browser = $_SERVER['HTTP_USER_AGENT'];
    if ((stristr($browser, 'Firefox')) && (stristr($browser, 'Mozilla'))) {
        $browser = 'Mozilla';
    } else {
        $browser = 'IE';
    }
    return $browser;
}

function set_headers($browser)
{
    $type = 'inline';
    if ($browser == 'Mozilla') {
        $type = 'attachment';
    }
    return $type;
}

function send_headers($type, $file)
{
    // kludge I added (& removed) to get around the fact that ie
    // automatically renders the xml in the browser if the
    // extension is .xml, by adding an encoded blank space.
    /*
        if ('.xml' == substr($file, -4))
        {
            $file = $file . '%20';
        }
        */
    header("Content-Type: application");
    header("Content-Disposition: $type; filename=\"$file\"");
}

function array_to_sql($header, $data, $env)
{
    //remove .csv [4chars] & replace hyphens
    $file = substr($env['file'], 0, -4);
    $file = str_replace('-', '_', $file);
    // SQL //
    $defchar    = ' varchar (255)';
    $newline    = "\r\n";
    $eol        = ($env['priv_disp']) ? "<br>\n" : $newline;
    $indent     = ($env['priv_disp']) ? '&nbsp;' : ' ';
    $endparen   = ');';
    $nos_comma  = ',';
    $sql_create = 'CREATE TABLE ' . $file . ' (' . $eol . $indent . 'mid integer primary key, ';
    $sql_insert = 'INSERT INTO ';
    $sql_values = ' VALUES (';
    $sql_create_db = "CREATE DATABASE export_$file;";
    $sql_use_db    = "USE export_$file;";
    $sql        = $sql_create;
    //  Other Declarations //
    $sql_multiples   = array();
    $sql_aux         = array();
    $sql_main        = array();
    $sql_aux_insert  = array();
    $sql_main_insert = array();
    $hindx           = array();
    $header_indx     = array();
    $mindx           = array();
    $main_indx       = array();
    $sql_test        = array();
    $sqldata         = array();
    $txt             = '';
    $sql_string      = '';
    $sql_create_aux  = '';
    $sql_create_main = '';
    $sql_create_aux_tables  = '';
    $sql_create_main_tables = '';
    $header_size = safe_count($header);

    // Creates all tables in sql_main //
    foreach ($header as $name => $col) {
        $name = str_replace(' ', '_', $name);
        $name = str_replace('(', '', $name);
        $name = str_replace(')', '', $name);
        $sql_main[] = $name;
    }

    /* create all AUX TABLES (only multiples)
        | data -> mid -> ord -> [0-n]
        | arry1 -> ord -> [0-n]
        | arry2 -> [0-n]
        */
    reset($data);
    foreach ($data as $mid => $arry1) {
        reset($arry1);
        foreach ($arry1 as $ord => $arry2) {
            reset($arry2);
            foreach ($arry2 as $index => $value) {
                reset($header);
                foreach ($header as $name => $col) {
                    if (($col == $index) && ($ord > 1) && ($col > 1)) {
                        $name = strtolower($name);
                        $sql_multiples[$col] = $name;
                        $name = str_replace(' ', '_', $name);
                        $sql_aux[$col] = 'CREATE TABLE ' . $name . ' ('
                            . $eol . $indent . 'mid integer, '
                            . $eol . $indent . 'ordinal varchar(5), '
                            . $eol . $indent . $name . $defchar . $endparen . $eol . $eol;
                        $hindx[$col] = $col;
                        $header_indx[$col] = $name;
                    }
                }
            }
        }
    }

    // Remove sql_main entries that have multiple values //
    reset($sql_main);
    foreach ($sql_main as $name1 => $value1) {
        reset($hindx);
        foreach ($hindx as $name2 => $value2) {
            if ($name1 == $name2) {
                unset($sql_main[$name1]);
            }
        }
    }

    // set all MAIN TABLES (not multiples) //
    $comma = FALSE;
    reset($sql_main);
    foreach ($sql_main as $name => $col) {
        if ($comma) {
            $sql_create .= ', ';
        }
        $comma = TRUE;
        $sql_create  .= $eol . $indent . $col . $defchar;
        $mindx[$name] = $name;
        $main_indx[$name] = $col;
    }
    $sql_create .= $endparen . $eol;

    /* this section sets all AUXILLIARY DATA (multiples)
        | data -> mid -> ord -> [0-n] values
        | arry1 -> ord -> [0-n] values
        */
    reset($data);
    foreach ($data as $mid => $arry1) {
        reset($arry1);
        foreach ($arry1 as $ord => $arry2) {
            reset($arry2);
            foreach ($arry2 as $index => $value) {
                reset($hindx);
                foreach ($hindx as $name2 => $value2) {
                    if (($index == $name2) && ($sql_aux[$name2] != $value) && ($hindx[$name2] == $value2)) {
                        $value   = safe_addslashes($value);
                        $qvalue  = squote($value);
                        $sqlname = $header_indx[$value2];
                        $sql_aux_insert[] = $sql_insert . $sqlname . $sql_values
                            . $mid . $nos_comma . $ord . $nos_comma
                            . $qvalue . $endparen . $eol;
                    }
                }
            }
        }
    }

    /* Get all MAIN DATA
        | data -> mid -> ord -> [0-n] values
        | arry1 -> ord -> [0-n]
        */
    reset($data);
    foreach ($data as $mid => $arry1) {
        reset($arry1);
        foreach ($arry1 as $ord => $arry2) {
            ksort($arry2);
            reset($arry2);
            foreach ($arry2 as $index => $value) {
                reset($mindx);
                foreach ($mindx as $name2 => $value2) {
                    $mid_index = $mindx[$name2];
                    if (($index == $name2) && ($sql_main[$name2] != $value) && ($mindx[$name2] == $value2)) {
                        $mid_index = $mindx[$name2];
                        $sql_main_array[$mid][$mid_index] = safe_addslashes($value);
                    }
                    if (!isset($sql_main_array[$mid][$mid_index])) {
                        $sql_main_array[$mid][$mid_index] = ' ';
                    }
                }
            }
        }
    }

    //remove duplicate entries
    $sql_main = array_unique($sql_main);

    ksort($sql_main_array);
    reset($main_indx);
    reset($sql_main_array);
    $comma = TRUE;

    /* Create main table data */
    foreach ($sql_main_array as $mid => $index) {
        $sql_string    = $sql_insert . $file . $sql_values . $mid;
        $sqldata[$mid] = $sql_string;
        foreach ($index as $value2 => $index2) {
            reset($main_indx);
            foreach ($main_indx as $mvalue => $mindex) {
                if ($mvalue == $value2) {
                    $qindex2 = squote($index2);
                    if ($comma) {
                        $sql_string .= ',';
                    }
                    $comma = TRUE;
                    $sql_string .= $qindex2;
                }
            }
        }
        $sql_string   .= $endparen . $eol;
        $sqldata[$mid] = $sql_string;
    }

    //create a string of sql queries from each array//
    foreach ($sqldata as $value => $index) {
        $sql_create_main_tables .= $index;
    }
    foreach ($sql_aux as $value => $index) {
        $sql_create_aux .= $index;
    }
    foreach ($sql_aux_insert as $value => $index) {
        $sql_create_aux_tables .= $index;
    }
    $txt .= $sql_create_db          . $eol
        .  $sql_use_db             . $eol
        .  $sql_create             . $eol
        .  $sql_create_aux         . $eol
        .  $sql_create_main_tables . $eol
        .  $sql_create_aux_tables  . $eol;

    return $txt;
}

function array_to_csv($header, $data)
{
    $txt = array();
    $set = array();
    $eol = "\n";
    $header_size = safe_sizeof($header);

    /* - CSV the header - */
    foreach ($header as $name => $col) {
        $txt[] = quote($name);
    }
    $txt  = join(',', $txt);
    $txt .= $eol;

    /* - CSV the data - */
    reset($data);
    foreach ($data as $mid => $arry1) {
        reset($arry1);
        foreach ($arry1 as $ord => $arry2) {
            $set = array();
            for ($c = 0; $c < $header_size; $c++) {
                if (isset($arry2[$c])) {
                    $set[] = quote($arry2[$c]);
                } else {
                    $set[] = '';
                }
            }
            $set  = join(',', $set);
            $set .= $eol;
            $txt .= $set;
        }
    }
    return $txt;
}


function squote($value)
{
    return "'" . $value . "'";
}


function rfc_date($date)
{
    $date = date('r', $date);
    $date = quote($date);
    return $date;
}

function find_query($qid, $auth, $db)
{
    $row = array();
    if (($qid) && ($auth)) {
        $qu  = safe_addslashes($auth);
        $sql = "select * from AssetSearches\n"
            . " where id = $qid\n"
            . " and (global = 1\n"
            . " or username = '$qu')";
        $row = find_one($sql, $db);
    }
    return $row;
}

function asset_census($access, $carr, $db)
{
    $used = array();
    $list = array();
    $nums = array();
    $cids = array();
    $mids = array();
    if ($carr) {
        reset($carr);
        foreach ($carr as $cid => $site) {
            $nums[$site] = 0;
            $cids[$site] = $cid;
        }
        ksort($cids);
    }
    if (($cids) && ($access)) {
        $sql = "select * from Machine\n"
            . " where provisional = 0 and\n"
            . " cust in ($access)";
        $list = find_many($sql, $db);
    }
    if (($list) && ($nums)) {
        foreach ($list as $key => $row) {
            $site = $row['cust'];
            $host = $row['host'];
            $mid  = $row['machineid'];
            $nums[$site]++;
            $used[$site] = $cids[$site];
            $mids[$mid] = $host;
        }
        ksort($used);
        asort($mids);
    }
    $temp['used'] = $used;
    $temp['nums'] = $nums;
    $temp['mids'] = $mids;
    return $temp;
}

function asset_unknown($action)
{
    debug_note("asset_unkown: action:$action");
}

/*
    |  We want to create what is known as a CSV file,
    |  where CSV simply means comma-separated values.
    |
    |  http://msdn.microsoft.com/library/default.asp?url=/library/en-us/RSCREATE/htm/rcr_creating_dc_v1_10j8.asp
    |
    |    *  The first record contains headers for all the columns
    |       in the report.
    |
    |    *  All rows have the same number of columns.
    |
    |    *  The default field delimiter string is a comma (,).
    |
    |    *  The record delimiter string is the carriage return
    |       and line feed (<cr><lf>).
    |
    |    *  The text qualifier string is a quotation mark (").
    |
    |    *  If the text contains an embedded delimiter string
    |       or qualifier string, the text qualifier is placed
    |       around the text, and the embedded qualifier strings
    |       are doubled.
    |
    |    *  Formatting and layout are ignored.
    |
    */

function quote($txt)
{
    if ($txt != '') {
        $txt = str_replace('"', '""', $txt);
    }
    return '"' . $txt . '"';
}

/* called from export_dump() */
function create_xml(&$env, &$set)
{
    $str = '';
    if ($set) {
        $mid  = $env['mid'];
        $host = $env['hosts'][$mid]['host'];
        $cust = $env['hosts'][$mid]['cust'];

        $nl   = "\n";
        $str  = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>" . $nl;
        $str .= '<sites>'                  . $nl;
        $str .= "<site name=\"$cust\">"    . $nl;
        $str .= '<machines>'               . $nl;
        $str .= "<machine name=\"$host\">" . $nl;
        $str .= '<items>'                  . $nl;

        $include_ords = false;
        $next_did     = false;

        reset($set);
        foreach ($set as $key => $row) {
            $did  = $row['dataid'];
            $ords = $row['ordinal'];
            $valu = $row['value'];
            $name = $env['names'][$did]['name'];
            $gid  = $env['names'][$did]['groups'];
            $grps = ($gid) ? $env['names'][$gid]['name'] : '';

            if (isset($set[$key + 1]['dataid']))
                $next_did = $set[$key + 1]['dataid'];
            else
                $next_did = false;

            /* this means the last item's dataid was equal to the this items
                   dataid, and in that case we want to include the ordinal.
                   Otherwise the last item in the group wouldn't have its ordinal
                   included because it does not match the dataid of the next item.
                */
            if ($include_ords)
                $str .= return_row($name, $valu, $grps, constItem, constOrd, $ords);

            /* if not currently including ordinals, check to see if the dataid
                   of the current item matches the data id of the next. In that case
                   we do want to include ordinals.
                */
            if (!$include_ords) {
                if ($did == $next_did) {
                    $include_ords = true;
                    $str .= return_row($name, $valu, $grps, constItem, constOrd, $ords);
                } else
                    $str .= return_row($name, $valu, $grps, constItem, constNotOrd, 0);
            }

            /* if the current dataid does not mactch the next dataid, we do not
                   want to include ordinals
                */
            if ($did != $next_did)
                $include_ords = false;
        }
    }
    $str .= '</items>'    . $nl;
    $str .= '</machine>'  . $nl;
    $str .= '</machines>' . $nl;
    $str .= '</site>'     . $nl;
    $str .= '</sites>'    . $nl;
    return $str;
}

/* called from export_dump() */
function create_csv(&$env, &$set)
{
    $txt = '';
    if ($set) {
        $htime = $env['htime'];
        //$head = explode('|','Ord|Name|Value|Group');
        $eol  = "\n";
        //$eol  = "\r\n";
        $txt .= '"Name","Ordinal","Value","Group",';
        $txt .= '"Server Earliest","Server Observed","Server Latest",';
        $txt .= '"Client Earliest","Client Observed","Client Latest"';
        $txt .= $eol;

        reset($set);
        foreach ($set as $key => $row) {
            $did  = $row['dataid'];
            $smin = $row['searliest'];
            $sobs = $row['sobserved'];
            $smax = $row['slatest'];
            $cmin = $row['cearliest'];
            $cobs = $row['cobserved'];
            $cmax = $row['clatest'];
            $ords = $row['ordinal'];
            $valu = $row['value'];
            $name = $env['names'][$did]['name'];
            $gid  = $env['names'][$did]['groups'];
            $grps = ($gid) ? $env['names'][$gid]['name'] : '';

            $qgrp = quote($grps);
            $qnam = quote($name);
            $qval = quote($valu);

            if ($htime == '1') //convert timestamps to rfc 2822 time//
            {
                $smin = rfc_date($smin);
                $sobs = rfc_date($sobs);
                $smax = rfc_date($smax);
                $cmin = rfc_date($cmin);
                $cobs = rfc_date($cobs);
                $cmax = rfc_date($cmax);
            }
            $txt .= "$qnam,$ords,$qval,$qgrp,$smin,";
            $txt .= "$sobs,$smax,$cmin,$cobs,$cmax";
            $txt .= $eol;
        }
    }
    return $txt;
}


function export_dump(&$env, $db)
{
    $post  = $env['post'];
    $verb  = $env['verb'];
    $mid   = $env['mid'];
    $now   = $env['now'];
    $set   = array();
    $good  = false;
    $txt   = '';

    if ($mid > 0) {
        $sql = "select * from Machine\n"
            . " where machineid = $mid";
        $mch = find_one($sql, $db);
    }

    $names = $env['names'];
    if (($mch) && ($names)) {
        $smin = $mch['searliest'];
        $smax = $mch['slatest'];
        $host = $mch['host'];
        $sql = "select * from AssetData\n"
            . " where machineid = $mid\n"
            . " and $smax <= slatest\n"
            . " order by dataid, ordinal";
        $set = find_many($sql, $db);
    }

    if ($post == constButtonExcel) {
        $txt  = create_csv($env, $set);
        $file = $env['file'];
    }
    if ($post == constButtonXML) {
        $txt  = create_xml($env, $set);
        $file = $env['xmlfile'];
    }
    /* - Present user w/box, $file(name),clean and shutoff buffer, print data - */
    $browser = return_browser();
    $type    = set_headers($browser);
    if (!$env['priv_disp']) {
        send_headers($type, $file);
        ob_end_clean();
    }
    print($txt);
}

function asset_walk(&$env, $set)
{
    $out    = asset_walk_arrange($env, $set);
    $header = $out['header'];
    $data   = $out['data'];
    $post   = $env['post'];

    switch ($post) {
        case constButtonExcel:
            $txt = array_to_csv($header, $data);
            break;
        case constButtonSQL:
            $txt = array_to_sql($header, $data, $env);
            break;
        case constButtonXML:
            $txt = array_to_xml($header, $data);
            break;
    }
    return $txt;
}

function export_exec(&$env, $db)
{
    $num  = 0;
    $d1   = array();
    $d2   = array();
    $qid  = $env['qid'];
    $now  = $env['now'];
    $auth = $env['auth'];
    $verb = $env['verb'];
    $ords = $env['ords'];
    $slow = $env['slow'];
    $dbid = $env['dbid'];
    $post = $env['post'];

    $good     = false;
    $export_s = false;
    $qury  = find_query($qid, $auth, $db);
    $names = $env['names'];
    $name  = $qury['name'];

    if (($qury) && ($names)) {
        // set the temp table name
        $tbl = create_pidtable_name('SelectedAssetData', $db);
        $env['SelectedAssetDataTableName'] = $tbl;

        $q = query_query($env, $auth, $qid, $ords);
        $mids = $q['mids'];
        $dids = $q['dids'];
        if (($mids) && ($dids)) {
            $x  = query_command($env, $q);
            $s1 = $x['sql1'];
            $s2 = $x['sql2'];
            $d1 = ($s1) ? find_slow($s1, $slow, $dbid, $db) : array();
            $d2 = ($s2) ? find_slow($s2, $slow, $dbid, $db) : array();
        } else {
            if ($mids) {
                echo "<p>Nothing found to export.</p>";
            } else {
                echo "<p>No machines found.</p>";
            }
        }
        // remove the table
        $sql = "drop table $tbl";
        redcommand($sql, $db);
    }
    $n1  = safe_count($d1);
    $n2  = safe_count($d2);
    $num = $n1 + $n2;
    $d3  = array_merge($d1, $d2);
    $txt = asset_walk($env, $d3);

    if ($num) {
        $priv_disp = $env['priv_disp'];
        $browser   = return_browser();
        $type      = set_headers($browser);
        switch ($post) {
            case constButtonExcel:
                $file = $env['file'];
                break;
            case constButtonSQL:
                $file = $env['sqlfile'];
                break;
            case constButtonXML:
                $file = $env['xmlfile'];
                break;
        }
        if (!$priv_disp) {
            send_headers($type, $file);
            ob_end_clean();
        }
        print($txt);
        $export_s = true;
    }
    return $export_s;
}

function export_host(&$env, $db)
{
    $now  = $env['now'];
    $cid  = $env['cid'];
    $mid  = $env['mid'];
    $carr = $env['carr'];
    $mids = $env['mids'];
    $user = $env['user'];
    $good = false;
    if (($mid) && ($cid)) {
        $site = @strval($carr[$cid]);
        $host = @strval($mids[$mid]);
        if (($site) && ($host)) {
            $xclfile = strtolower("$host.csv");
            $xclfile = textbox('file', 60, $xclfile);
            $xmlfile = strtolower("$host.xml");
            $xmlfile = textbox('xmlfile', 60, $xmlfile);

            $priv_disp = checkbox('priv_disp', 0);
            $htime     = checkbox('htime', 0);

            $xclsubmit = button('Excel');
            $xmlsubmit = button('XML');

            echo post_self('act');
            echo hidden('act', 'xdump');
            echo hidden('mid', $mid);
            echo table_header();
            echo double('Filename:', $xclfile);
            echo double('<br>', $xclsubmit);
            echo double('Filename:', $xmlfile);
            echo double('<br>', $xmlsubmit);
            echo double('Format Time:', $htime);
            echo table_footer();
            echo "By default, the time will be formated as unix time-stamps: <i>1101854878</i><br>";
            echo "Selecting 'Format Time' will format time as: <i>Thu, 21 Dec 2000 16:00:02 +200</i>";
            if ($env['debug']) {
                echo double("<br><font color=\"green\">Display to screen</font>", $priv_disp);
            }
            echo form_footer();
            $good = true;
        }
    }
}


function create_file_with_extension($file, $ext)
{
    $file = substr($file, 0, -4);
    return $file . $ext;
}

function export_query(&$env, $db)
{
    $good = false;
    $qid  = $env['qid'];
    $auth = $env['auth'];
    $adhoc = get_integer('adhoc', 0);
    $qury = find_query($qid, $auth, $db);
    if ($qury) {
        if ($adhoc) {
            $name = 'asset-export-' . date('Y-m-d-H-i', time());
        } else {
            $name = $qury['name'];
        }
        $file    = strtolower("$name.csv");
        $file    = str_replace(' ', '-', $file);
        $file    = mime_filename($file);
        $sqlfile = create_file_with_extension($file, '.sql');
        $xmlfile = create_file_with_extension($file, '.xml');

        $xmlfile = textbox('xmlfile', 60, $xmlfile);
        $sqlfile = textbox('sqlfile', 60, $sqlfile);
        $file    = textbox('file',   60, $file);

        $priv_disp = checkbox('priv_disp', 0);

        $subxcl = button(constButtonExcel);
        $subsql = button(constButtonSQL);
        $subxml = button(constButtonXML);

        echo post_self('act');
        echo hidden('act', 'xexec');
        echo hidden('qid', $qid);
        echo hidden('adhoc', $env['adhoc']);
        echo hidden('adhocgrp', $env['adhocgrp']);

        echo table_header();
        echo double('Export to comma-delimited ASCII text file with headers (.csv extension)', $subxcl);
        echo double('.csv File name:', $file);
        echo double('Export to SQL table (.sql extension)', $subsql);
        echo double('SQL File name:', $sqlfile);
        echo double('Export to XML file (.xml extension)', $subxml);
        echo double('XML File name:', $xmlfile);

        if ($env['debug']) {
            echo double("<font color=\"green\">Display to screen</font>", $priv_disp);
        }

        echo form_footer();
        echo "</form>\n";
        $good = true;
    }
}


function empty_access()
{
    $msg = "You need access to at least one machine in order to use this page.";
    $msg = fontspeak($msg);
    echo "<p>$msg</p>\n";
}


/*
    |  Main program
    */

$now = time();
$db  = db_connect();
$act = get_string('act', 'qury');

$authuser = process_login($db); //process_login() sends headers via force_auth()

min_html_header();
window_spec(800, 650);

$export_s = FALSE; //if export to excel is a success or not
$ast = (get_integer('asset', 1)) ? 1 : 0;
$ord = get_integer('ord', 0);
$wrd = get_integer('wrd', 0);
$mid = get_integer('mid', 0);
$cid = get_integer('cid', 0);
$qid = get_integer('qid', 0);

$user  = user_data($authuser, $db); //returns auth user data from sql
$pa    = @($user['priv_asset']) ?  1 : 0;
$fs    = @($user['filtersites']) ? 1 : 0;
$asset = ($pa) ? $ast : 0;

$dbg = get_integer('debug', 1);
$priv_debug = @($user['priv_debug']) ? 1 : 0;
$debug = ($priv_debug) ? $dbg : 0;

$carr = site_array($authuser, $fs, $db);
$access = db_access($carr);
db_change($GLOBALS['PREFIX'] . 'asset', $db);

$adhoc = get_integer('adhoc', 0);
$grpinc = get_string('adhocgrp', '');
if (($qid == 0) && ($adhoc == 1)) {
    /* Create the query first */
    $qid = ALIB_BuildAdHocQuery($db);
    $grpinc = GRPS_get_multiselect_values(constAdHocGroupInc);
} else if ($adhoc == 0) {
    $sql = "SELECT querytype FROM AssetSearches WHERE id=$qid";
    $row = find_one($sql, $db);
    if ($row['querytype'] == constAssetQueryTypeAdHoc) {
        $adhoc = 1;
    }
}

$temp = asset_census($access, $carr, $db);
$used = $temp['used'];
$mids = $temp['mids'];
$nums = $temp['nums'];

$site = '';
$host = '';
if ($cid > 0) {
    $site = @trim($carr[$cid]);
}
if ($mid > 0) {
    $host = @trim($mids[$mid]);
}
if (!$used) {
    $act = 'empty';
}

$env = array();
$env['db']  = $db;
$env['now'] = $now;
$env['mid'] = $mid;
$env['ord'] = $ord;
$env['wrd'] = $wrd;
$env['cid'] = $cid;
$env['qid'] = $qid;
$env['debug'] = $debug;
$env['self']      = server_var('PHP_SELF');
$env['args']      = server_var('QUERY_STRING');
$env['post']      = get_string('button', '');
$env['file']      = get_string('file', '');
$env['sqlfile']   = get_string('sqlfile', '');
$env['xmlfile']   = get_string('xmlfile', '');
$env['verb']      = get_integer('verb', 0);
$env['htime']     = get_integer('htime', 0);
$env['priv_disp'] = get_integer('priv_disp', 0);
$env['priv_disp'] = ($priv_debug) ? $env['priv_disp'] : 0;
$env['slow'] = (float) server_def('slow_query_asset', 20, $db);
$env['dbid'] = 'master';
$env['carr'] = $carr;
$env['user'] = $user;
$env['auth'] = $authuser;
$env['used'] = $used;
$env['ords'] = array(0, 0, 0, 0);
$env['cron'] = 0;
$env['nums'] = $nums;
$env['mids'] = $mids;
$env['acts'] = $act;
$env['site'] = $site;

$env['names']  = asset_names($db);
$env['hosts']  = asset_machines($db);
$env['asset']  = $asset;
$env['access'][$authuser] = $access;

$env['adhoc'] = $adhoc;
$env['adhocgrp'] = $grpinc;

debug_note("action:$act filter:$fs user:$authuser");

switch ($act) {
    case 'qury':
        export_query($env, $db);
        break;
    case 'xhost':
        export_host($env, $db);
        break;
    case 'xdump':
        export_dump($env, $db);
        break;
    case 'xexec':
        $export_s = export_exec($env, $db);
        break;
    case 'empty':
        empty_access();
        break;
    default:
        asset_unknown($act);
        break;
}
if (!$export_s && $act != 'xdump') {
    min_html_footer();
}
