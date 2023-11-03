<?php

/* 

Revision History 

Date        Who     What
----        ---     ----
15-May-05   BJS     Created.
24-May-05   BJS     Added ordinal and grouping to mapping.
26-May-05   BJS     Some existing names are the same as future 
                    microdata names, renamed those to avoid 
                    conflicts.
01-Jun-05   BJS     Added translation section.
02-Jun-05   BJS     Added update_xxx_t() procedures.
03-Jun-05   BJS     Checked into branch 4.2.23
06-Jun-05   BJS     Added revision history, newline, standard_html_footer().
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.

*/


include('../lib/l-util.php');
include('../lib/l-cnst.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-jump.php');
include('../lib/l-user.php');
include('../lib/l-form.php');
include('../lib/l-tabs.php');
include('../lib/l-head.php');
include('../lib/l-csum.php');
include('../lib/l-cwiz.php');


/* asset report */
function update_assetreport($id, $col, $name, $db)
{
    $qname = safe_addslashes($name);

    $sql = "update AssetReports set\n"
        . " $col = '$qname',\n"
        . " translated = 1\n"
        . " where id = $id";
    redcommand($sql, $db);
}

function update_assetreport_t($id, $db)
{
    $sql = "update AssetReports set\n"
        . " translated = 1\n"
        . " where id = $id";
    redcommand($sql, $db);
}


/* asset searchcriteria */
function update_assetsearchcriteria($id, $name, $db)
{
    $qname = safe_addslashes($name);
    $sql = "update AssetSearchCriteria set\n"
        . " fieldname = '$qname',\n"
        . " translated = 1\n"
        . " where id = $id";
    redcommand($sql, $db);
}

function update_assetsearchcriteria_t($id, $db)
{
    $sql = "update AssetSearchCriteria set\n"
        . " translated = 1\n"
        . " where id = $id";
    redcommand($sql, $db);
}


/* asset searches */
function update_assetsearches($id, $name, $db)
{
    $qname = safe_addslashes($name);
    $sql = "update AssetSearches set\n"
        . " displayfields = '$qname',\n"
        . " translated = 1\n"
        . " where id = $id";
    redcommand($sql, $db);
}


/*
     Main program
*/

$now = time();
$db  = db_connect();
$authuser = process_login($db);
$comp  = component_installed();
$title = 'Microdata Map';

echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);

$dbg  = (get_argument('debug', 0, 1)) ?   1 : 0;

$user = user_data($authuser, $db);

$priv_debug = @($user['priv_debug']) ? 1 : 0;
$priv_admin = @($user['priv_admin']) ? 1 : 0;

$debug = ($priv_debug) ? $dbg : 0;

db_change($GLOBALS['PREFIX'] . 'asset', $db);

/* Microdata sub-groups */
$fields = array('Asset Data', 'Motherboard', 'BIOS', 'Memory', 'Peripherals', 'Network Information');

$c = 4;
reset($fields);
foreach ($fields as $i => $f) {
    $qf  = safe_addslashes($f);
    $now = time();
    $sql = "insert into DataName set\n"
        . " setbyclient = 0,\n"
        . " name = '$qf',\n"
        . " parent  = 0,\n"
        . " ordinal = $c,\n"
        . " groups  = 0,\n"
        . " created = $now,\n"
        . " leader  = 0,\n"
        . " clientname = '$qf'";
    redcommand($sql, $db);
    $sql = "select dataid from DataName where name = '$qf'";
    $set[$f] = find_one($sql, $db);
    $c++;
}

/* these existing values are changed so we don't have conflicts */
$old = array(
    'Version'       => 'Old Pegasus Version',
    'Speed'         => 'Memory Speed',
    'Serial Number' => 'Base Board Serial Number',
    'Voltage'       => 'Module Voltage',
    'location'      => 'Machine Location',
    'Slot Type'     => 'System Slot Type'
);

reset($old);
foreach ($old as $k => $d) {
    $k = safe_addslashes($k);
    $d = safe_addslashes($d);
    $sql = " update DataName set name = '$d' where name = '$k'";
    redcommand($sql, $db);
}


/* these are the microdata mappings */
$data = array(
    'Asset Data' => array(
        'Location' => 'Site Name',
        'Make'     => 'System Manufacturer',
        'Model'    => 'System Product',
        'Serial Number' => 'System Serial Number',
        'Monitor Mfg'   => 'Monitor Mfg',
        'Monitor Model' => 'Monitor Model',
        'Local Printer' => 'Default Printer',
        'Video Controller' => 'Video Controller Name',
        'Disk Model'    => 'Physical Disk Model',
        'Disk Drive'    => 'Logical Disk Name',
        'Disk Drive Capacity' => 'Logical Disk KBytes Total',
        'Disk Drive Capacity % Used' => 'Logical Disk Percentage used',
        'CDROM'         => 'CDROM Drive Name',
        'Modem'         => 'Modem Name',
        'Sound Card'    => 'Sound Card ProductName'
    ),

    'Motherboard' => array(
        'Processor'     => 'Processor Type',
        'Speed'         => 'Processor Current Speed in MHz',
        'Slot Type'     => 'Slot Designation',
        'Socket'        => 'Processor Socket Designation',
        'Voltage'       => 'Processor Current Voltage'
    ),

    'BIOS' =>        array(
        'Manufacturer'  => 'BIOS Vendor',
        'Version'       => 'BIOS Version',
        'Date'          => 'BIOS Date'
    ),

    'Memory' =>      array(
        'Capacity' => 'Maximum Total Memory Size in MB',
        'Installed Size in MB' => 'Installed Size in MB'
    ),

    'Peripherals' => array(
        'Printer Port'  => 'Printer PortName',
        'Printer'       => 'Printer Name',
        'Internal Reference Designator' => 'Internal Reference Designator',
        'Port Type'     => 'Port Type'
    ),

    'Network Information' => array(
        'Computer Name'   => 'Machine Name',
        'Workgroup'       => 'Workgroup',
        'Domain'          => 'Domain',
        'User Name'       => 'User Name',
        'Network Adapter' => 'Network Adapter',
        'MAC Address'     => 'MAC Address',
        'IP Address'      => 'IP Address'
    ),
);

/* here we add all the items in the correct grouping */
reset($data);
foreach ($data as $key => $val) {
    $parentid = $set[$key]['dataid'];
    $c = 0;

    reset($val);
    foreach ($val as $n_name => $o_name) {
        $c++;
        $qn_name = safe_addslashes($n_name);
        $qo_name = safe_addslashes($o_name);

        $sql = "update DataName set\n"
            . " name    = '$qn_name',\n"
            . " parent  = $parentid,\n"
            . " ordinal = $c\n"
            . " where clientname = '$qo_name'";
        redcommand($sql, $db);
    }
}

/* this section fixes the holes we leave in ordinal listing
       by resetting them from 0->n for each distinct group */

$sql = 'select distinct parent from DataName';
$set = find_many($sql, $db);

reset($set);
foreach ($set as $i => $v) {
    $c = 0;
    $p = $v['parent'];

    $sql = "select dataid, ordinal from DataName\n"
        . " where parent = $p order by ordinal";
    $out = find_many($sql, $db);

    reset($out);
    foreach ($out as $dex => $didord) {
        $c++;
        $did = $didord['dataid'];
        $sql = "update DataName set ordinal = $c\n"
            . " where dataid = $did";
        redcommand($sql, $db);
    }
}

/* translation */

/* get all clientname/name pairs that do not match */
$sql = "select clientname, name from DataName\n"
    . " where clientname != name";
$out = find_many($sql, $db);

$set = array();

/* build an array indexed by clientname */
reset($out);
foreach ($out as $indx => $a) {
    reset($a);
    $c = $a['clientname'];
    $n = $a['name'];
    $set[$c] = $n;
}
reset($set);

/* get all AssetReports data that needs to be translated */
$sql = "select id, order1, order2, order3, order4 from AssetReports\n"
    . " where translated = 0";
$ast = find_many($sql, $db);


reset($ast);
foreach ($ast as $indx => $ast_array) {
    $id = $ast_array['id'];
    $o1 = $ast_array['order1'];
    $o2 = $ast_array['order2'];
    $o3 = $ast_array['order3'];
    $o4 = $ast_array['order4'];

    if (@$set[$o1])
        update_assetreport($id, 'order1', $set[$o1], $db);

    if (@$set[$o2])
        update_assetreport($id, 'order2', $set[$o2], $db);

    if (@$set[$o3])
        update_assetreport($id, 'order3', $set[$o3], $db);

    if (@$set[$o4])
        update_assetreport($id, 'order4', $set[$o4], $db);

    /* always executes */
    update_assetreport_t($id, $db);
}


/* get all AssetSearchCriteria data that needs to be translated */
$sql = "select id, fieldname from AssetSearchCriteria\n"
    . " where translated = 0";
$asc = find_many($sql, $db);

reset($set);
reset($asc);
foreach ($asc as $indx => $asc_array) {
    $id = $asc_array['id'];
    $fn = $asc_array['fieldname'];

    if (@$set[$fn])
        update_assetsearchcriteria($id, $set[$fn], $db);

    update_assetsearchcriteria_t($id, $db);
}


/* get all AssetSearches data that needs to be translated */
$sql = "select id, displayfields from AssetSearches\n"
    . " where translated = 0";
$asr = find_many($sql, $db);

reset($set);
reset($asr);
foreach ($asr as $indx => $asr_array) {
    $id = $asr_array['id'];

    /* displayfields are stored as :val1:val2:val3: */
    $df = explode(":", $asr_array['displayfields']);
    $str = ":";

    reset($df);
    foreach ($df as $key => $data) {
        if ($data) {
            $tmp = $data;
            if (@$set[$data])
                $tmp = $set[$data];

            $str .= $tmp . ':';
        }
    }
    update_assetsearches($id, $str, $db);
}

echo head_standard_html_footer($authuser, $db);
