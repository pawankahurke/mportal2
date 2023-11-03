<?php

/*
Revision history:

Date        Who     What
----        ---     ----
29-Oct-03   EWB     Created.
11-Nov-03   EWB     Delete Product removes KeyFiles.
 4-Dec-03   EWB     Allow creation of product with no meterfilename
17-Dec-03   EWB     Filter keyfiles filenames also.
29-Jan-04   EWB     Global/Local Products.
29-Jan-04   EWB     Copy Product Copies Keyfiles.
30-Jan-04   EWB     Raised textbox limits.
 2-Feb-04   EWB     Added browse for file, for new files.
 2-Feb-04   EWB     Multiple Meter files.
 6-Feb-04   EWB     Meterfile not part of product.
11-Feb-04   EWB     Removing product removes MeterFiles
13-Feb-04   EWB     Ditto CryptKeys.
20-Feb-04   EWB     Added a few "Done" buttons.
25-Feb-04   EWB     global / local product issues.
25-Feb-04   EWB     Added "View Product".
26-Feb-04   EWB     View Product only for ones you can't edit.
 8-Mar-04   EWB     Added Create/Modify Columns
 8-Mar-04   EWB     Normal users can create global products.
 8-Mar-04   EWB     require priv_provis to modify products
 9-Mar-04   EWB     Warn about deleting assigned products.
 9-Mar-04   EWB     Serious Warning.
 9-Mar-04   EWB     Gang insert key/meter files.
10-Mar-04   EWB     Copy of global product not automatically global.
10-Mar-04   EWB     Copy Product Complete.
 7-Apr-04   EWB     sort by sites/number of machines
 7-Apr-04   EWB     debug page sort by column headers.
21-Apr-04   EWB     include checksum file.
23-Apr-04   EWB     a few minor wording changes.
 9-May-05   EWB     New database.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
 
*/

ob_start();
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-slct.php');
include('../lib/l-user.php');
include('../lib/l-jump.php');
include('../lib/l-slav.php');
include('../lib/l-head.php');
include('../lib/l-csum.php');
include('../lib/l-tabs.php');
include('../lib/l-rlib.php');
include('../lib/l-gcfg.php');
include('local.php');
include('../lib/l-prov.php');


function bool($x)
{
    return ($x > 0) ? 'Yes' : 'No';
}


/*
    |  Cancel out of edit product page goes to product list page.
    |  instead of the normal edit product complete page.
    |
    |  Cancel out of update keyfile goes to back to edit products.
    */

function redirect_action($act, $post)
{
    if ($post == 'Cancel') {
        if ($act == 'epc') $act = 'list';
        if ($act == 'apc') $act = 'list';
        if ($act == 'cpc') $act = 'list';
        if ($act == 'ukf') $act = 'ep';
        if ($act == 'umf') $act = 'ep';
    }
    return $act;
}


/*
    |  The title we show the user doesn't quite match
    |  the real state.  Note that redirect action
    |  gets called first.
    */

function build_title($act)
{
    switch ($act) {
        case 'cpc':
            return 'Copy Product Complete';
        case 'epc':
            return 'Edit Product Complete';
        case 'apc':
            return 'Add Product Complete';
        case 'ap':
            return 'Add Product';
        case 'ep':
            return 'Manage Product';
        case 'cdkf':
            return 'Delete Key File';
        case 'cdmf':
            return 'Delete Metering File';
        case 'cdp':
            return 'Delete Product';
        case 'cp':
            return 'Copy Product';
        case 'cpe':
            return 'Copy Product Error';
        case 'dbg':
            return 'Debug Product';
        case 'dkf':
            return 'Key File Removed';
        case 'dmf':
            return 'Meter File Removed';
        case 'ekf':
            return 'Edit Key File';
        case 'del':
            return 'Delete Product Complete';
        case 'umd':;
        case 'ukd':;
        case 'list':
            return 'Products';
        case 'psh':
            return 'Push Update Complete';
        case 'ukf':
            return 'Key File Updated';
        case 'umf':
            return 'Meter File Updated';
        case 'view':
            return 'View Product';
        case 'xxx':
            return 'Product Single';
        default:
            return 'Unknown Action';
    }
}


/*
    |  Product names are not allowed to contain any
    |  commas or newlines, so we'll just replace those
    |  with nothing.
    */

function product_name($name)
{
    $find = array("\n", "\r\n", ",");
    $repl = array('', '', '');
    $text = str_replace($find, $repl, $name);
    return trim($text);
}


function again(&$env)
{
    $priv = $env['priv'];
    $self = $env['self'];
    $pprv = $env['pprv'];
    $act  = $env['act'];
    $acts = "$self?act";

    $ea  = "|$act|";
    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');

    $pos = strpos('||ap|apc|cdp|cdkf|cdmf|cp|cpc|del|dbg|dkf|ep|epc|ekf|emf|ekp|psh|view|xxx|', $ea);
    if ($pos > 0) {
        $a[] = html_link($self, 'products');
    }
    if (($act != 'ap') && ($pprv)) {
        $a[] = html_link("$acts=ap", 'add product');
    }
    if ($priv) {
        $args = server_var('QUERY_STRING');
        $href = ($args) ? "$self?$args" : $self;
        $a[] = html_link('../acct/index.php', 'home');
        $a[] = html_link($href, 'again');
        $a[] = html_link("$acts=dbg", 'debug');
    }
    return jumplist($a);
}

function order($ord)
{
    switch ($ord) {
        case  0:
            return 'prodname, productid';
        case  1:
            return 'prodname desc, productid';
            break;
        case  2:
            return 'username, prodname, productid';
            break;
        case  3:
            return 'username desc, prodname, productid';
            break;
        case  4:
            return 'productid';
            break;
        case  5:
            return 'productid desc';
            break;
        case  6:
            return 'global desc, prodname, productid';
            break;
        case  7:
            return 'global, prodname, productid';
            break;
        case  8:
            return 'created desc, productid';
            break;
        case  9:
            return 'created, productid';
            break;
        case 10:
            return 'modified desc, productid';
            break;
        case 11:
            return 'modified, productid';
            break;
        case 12:
            return 'defaultenable desc, prodname, productid';
            break;
        case 13:
            return 'defaultenable, prodname, productid';
            break;
        case 14:
            return 'defaultmonitor desc, prodname, productid';
            break;
        case 15:
            return 'defaultmonitor, prodname, productid';
            break;
        default:
            return order(0);
    }
    return $order;
}

function count_products($db)
{
    $sql = "select count(*) from Products";
    return find_scalar($sql, $db);
}


function find_any_product($id, $db)
{
    $row = array();
    if ($id > 0) {
        $sql = "select * from Products\n"
            . " where productid = $id";
        $row = find_one($sql, $db);
    }
    return $row;
}

function find_product(&$env, $db)
{
    $row = array();
    $id  = $env['id'];
    $qu  = safe_addslashes($env['auth']);
    if ($id > 0) {
        $sql = "select * from Products\n"
            . " where productid = $id and\n"
            . " ((global = 1) or (username = '$qu'))";
        $row = find_one($sql, $db);
    }
    return $row;
}


function missing_product($env)
{
    return "<br>\n"
        .   "The specified product no longer exists.<br>\n"
        .   "Either someone removed it, or it never existed"
        .   " in the first place.<br>\n"
        .   "<br>\n";
}

function find_keyfile($pid, $kid, $db)
{
    $row = array();
    if (($pid > 0) && ($kid > 0)) {
        $sql = "select * from KeyFiles\n"
            . " where productid = $pid and\n"
            . " keyid = $kid";
        $row = find_one($sql, $db);
    }
    return $row;
}


function find_meterfile($pid, $mid, $db)
{
    $row = array();
    if (($pid > 0) && ($mid > 0)) {
        $sql = "select * from MeterFiles\n"
            . " where productid = $pid and\n"
            . " meterid = $mid";
        $row = find_one($sql, $db);
    }
    return $row;
}


/*
    |  This is used for checking if it's ok to create
    |  a new global product, and also to see if an
    |  existing local product can become global.
    |
    |  Since we don't do overrides for products, an
    |  existing one can become global only if there
    |  isn't already one with that name.
    */

function count_product_name($name, $id, $db)
{
    $num = 0;
    if ($name) {
        $qn  = safe_addslashes($name);
        $sql = "select count(*) from Products\n"
            . " where prodname = '$qn'\n"
            . " and productid != $id";
        $num = find_scalar($sql, $db);
    }
    return $num;
}

/*
    |  Find a product specified by name.
    */

function find_product_name(&$env, $name, $db)
{
    $row = array();
    $qn  = safe_addslashes($name);
    $qu  = safe_addslashes($env['auth']);
    if ($name) {
        $sql = "select * from Products\n"
            . " where prodname = '$qn' and\n"
            . " ((global = 1) or (username = '$qu'))\n"
            . " order by global limit 1";
        $row = find_one($sql, $db);
    }
    return $row;
}


function return_link(&$env)
{
    $self = $env['self'];
    $link = html_link($self, 'Return to Products');
    $text = "<br><br>\n$link<br>\n";
    return $text;
}


function continue_return(&$env)
{
    $self = $env['self'];
    $id   = $env['id'];
    $msg  = '';
    if ($id > 0) {
        $cref = "$self?act=ep&id=$id";
        $continue = html_link($cref, 'Continue');
        $msg = "<br><br>$continue\n\n";
    }
    $return = html_link($self, 'Return to Products');
    $msg .= "<br><br>$return";
    return $msg;
}


function list_products(&$env, $db)
{
    echo again($env);
    $self  = $env['self'];
    $auth  = $env['auth'];
    $gprv  = $env['gprv'];
    $pprv  = $env['pprv'];
    $ord   = $env['ord'];
    $order = order($ord);
    $sql   = "select * from Products\n"
        . " where global = 1\n"
        . " or username = '$auth'\n"
        . " order by $order";
    $list  = find_many($sql, $db);
    if ($list) {

        $o    = "$self?ord";
        $nref = ($ord ==  0) ? "$o=1"  : "$o=0";
        $gref = ($ord ==  6) ? "$o=7"  : "$o=6";
        $cref = ($ord ==  8) ? "$o=9"  : "$o=8";
        $mref = ($ord == 10) ? "$o=11" : "$o=10";

        $head   = array();
        $head[] = 'Action';
        $head[] = html_link($nref, 'Product name');
        $head[] = html_link($gref, 'Global');
        $head[] = 'Enable by default';
        $head[] = 'Meter by default';
        $head[] = html_link($cref, 'Created');
        $head[] = html_link($mref, 'Modified');

        echo table_header();
        echo table_data($head, 1);

        reset($list);
        foreach ($list as $key => $row) {
            $id   = $row['productid'];
            $user = $row['username'];
            $name = $row['prodname'];
            $crt  = fulldate($row['created']);
            $mod  = fulldate($row['modified']);
            $enab = bool($row['defaultenable']);
            $dmon = bool($row['defaultmonitor']);
            $glob = bool($row['global']);

            $a    = array();
            $act  = "$self?id=$id&act";
            $view = html_link("$act=view", '[view]');
            if ($pprv) {
                if ($user == $auth) {
                    $a[] = html_link("$act=ep", '[edit]');
                    $a[] = html_link("$act=cdp", '[delete]');
                } else {
                    $a[] = $view;
                }
                $a[]  = html_link("$act=cp", '[copy]');
            } else {
                $a[] = $view;
            }
            $acts = join('<br>', $a);
            $args = array($acts, $name, $glob, $enab, $dmon, $crt, $mod);
            echo table_data($args, 0);
        }
        echo table_footer();
    } else {
        echo "There are no products.";
    }
    echo again($env);
}


function debug_products(&$env, $db)
{
    $list = array();
    $priv = $env['priv'];
    $self = $env['self'];
    $ord  = $env['ord'];
    if ($priv) {
        $order = order($ord);
        $sql   = "select * from Products\n order by $order\n limit 50";
        $list  = find_many($sql, $db);
    } else {
        list_products($env, $db);
    }
    if ($list) {
        echo again($env);

        $o    = "$self?act=dbg&ord";
        $nref = ($ord ==  0) ? "$o=1"  : "$o=0";   // name     0/1
        $oref = ($ord ==  2) ? "$o=3"  : "$o=2";   // owner    2/3
        $iref = ($ord ==  4) ? "$o=5"  : "$o=4";   // id       4/5
        $gref = ($ord ==  6) ? "$o=7"  : "$o=6";   // global   6/7
        $cref = ($ord ==  8) ? "$o=9"  : "$o=8";   // create   8/9
        $mref = ($ord == 10) ? "$o=11" : "$o=10";  // modify   10/11
        $eref = ($ord == 12) ? "$o=13" : "$o=12";  // enable   12/13
        $xref = ($ord == 14) ? "$o=15" : "$o=14";  // meter    14/15

        $head   = array();
        $head[] = html_link($nref, 'Name');
        $head[] = html_link($iref, 'Id');
        $head[] = html_link($oref, 'Owner');
        $head[] = html_link($eref, 'Enable');
        $head[] = html_link($xref, 'Meter');
        $head[] = html_link($cref, 'Create');
        $head[] = html_link($mref, 'Modify');

        echo table_header();
        echo table_data($head, 1);

        reset($list);
        foreach ($list as $key => $row) {
            $id   = $row['productid'];
            $glob = ($row['global']) ? 'g' : 'l';
            $user = disp($row, 'username');
            $name = disp($row, 'prodname');
            $enab = bool($row['defaultenable']);
            $dmon = bool($row['defaultmonitor']);
            $crt  = fulldate($row['created']);
            $mod  = fulldate($row['modified']);
            $owner = "$user($glob)";
            $args = array($name, $id, $owner, $enab, $dmon, $crt, $mod);
            echo table_data($args, 0);
        }
        echo table_footer();

        echo again($env);
    } else {
        if ($priv) {
            echo again($env);
            echo "There are no products.";
            echo again($env);
        }
    }
}

function add_product($env, $db)
{
    echo again($env);

    debug_note("add_product");

    $gprv = $env['gprv'];
    $name = textbox('name', 100, '');
    $glob = checkbox('glob', 0);
    $enab = checkbox('enab', 1);
    $dmon = checkbox('dmon', 1);
    $keys = array();
    $mets = array();
    for ($i = 1; $i <= 5; $i++) {
        $keys[$i] = filebox("key$i", 100);
        $mets[$i] = filebox("met$i", 100);
    }
    $submit = button('Add');
    $cancel = button('Cancel');

    echo post_self();
    echo hidden('act', 'apc');

    echo table_header();
    echo pretty_header('Add a New Product', 2);
    echo two_col('Product name:', $name);
    echo two_col('Global:', $glob);
    echo two_col('Enable by default when provisioned:', $enab);
    echo two_col('Meter by default:', $dmon);

    reset($mets);
    foreach ($mets as $i => $row) {
        echo two_col("Meter File $i", $row);
    }

    reset($keys);
    foreach ($keys as $i => $row) {
        echo two_col("Key File $i", $row);
    }

    echo two_col($submit, $cancel);
    echo table_footer();
    echo form_footer();
    echo again($env);
}


function find_pid($pid, $db, $table)
{
    $list = array();
    if ($pid > 0) {
        $sql  = "select * from $table\n where productid = $pid";
        $list = find_many($sql, $db);
    }
    return $list;
}


function find_keys($pid, $db)
{
    return find_pid($pid, $db, 'KeyFiles');
}

function find_mets($pid, $db)
{
    return find_pid($pid, $db, 'MeterFiles');
}


function edit_product(&$env, $db)
{
    echo again($env);
    debug_note("edit_product");
    $gprv = $env['gprv'];
    $row  = find_product($env, $db);
    if ($row) {
        $self = $env['self'];
        $id   = $row['productid'];
        $name = $row['prodname'];
        $user = $row['username'];
        $glob = $row['global'];
        $enab = $row['defaultenable'];
        $dmon = $row['defaultmonitor'];


        $name = textbox('name', 100, $name);
        $glob = checkbox('glob', $glob);
        $enab = checkbox('enab', $enab);
        $dmon = checkbox('dmon', $dmon);

        $submit = button('Update');
        $cancel = button('Cancel');

        if ($user == $env['auth']) {
            $del  = 'Delete this product';
            $href = "$self?id=$id&act=cdp";
            $link = html_link($href, $del);
            echo "<br>$link.<br>\n";
        }

        echo post_self();
        echo hidden('act', 'epc');
        echo hidden('id', $id);
        echo table_header();
        echo pretty_header('Manage Product', 2);
        echo two_col('Product name:', $name);
        echo two_col('Global:', $glob);
        echo two_col('Enable by default when provisioned:', $enab);
        echo two_col('Meter by default:', $dmon);
        echo two_col($submit, $cancel);
        echo table_footer();

        $keys = find_keys($id, $db);
        $mets = find_mets($id, $db);

        echo table_header();
        echo pretty_header('Key Files', 2);

        if ($keys) {
            reset($keys);
            foreach ($keys as $key => $row) {
                $kid  = $row['keyid'];
                $pid  = $row['productid'];
                $file = $row['filename'];

                $act  = "$self?id=$pid&kid=$kid&act";
                $ed   = html_link("$act=ekf", '[edit]');
                $del  = html_link("$act=cdkf", '[delete]');
                $act  = "$ed $del";
                echo two_col($act, $file);
            }
        }

        $key = filebox('keyf', 100);
        echo two_col('Key file:', $key);
        echo table_footer();

        echo table_header();
        echo pretty_header('Meter Files', 2);

        if ($mets) {
            reset($mets);
            foreach ($mets as $met => $row) {
                $mid  = $row['meterid'];
                $pid  = $row['productid'];
                $file = $row['filename'];

                $act  = "$self?id=$pid&mid=$mid&act";
                $ed   = html_link("$act=emf", '[edit]');
                $del  = html_link("$act=cdmf", '[delete]');
                $act  = "$ed $del";
                echo two_col($act, $file);
            }
        }


        $key = filebox('metf', 100);
        echo two_col('Meter file:', $key);
        echo table_footer();
        echo form_footer();
    } else {
        echo missing_product($env);
    }

    echo again($env);
}


function view_products(&$env, $db)
{
    echo again($env);
    debug_note("view_product");
    $gprv = $env['gprv'];
    $row  = find_product($env, $db);
    if ($row) {
        $auth = $env['auth'];
        $pprv = $env['pprv'];
        $id   = $row['productid'];
        $name = $row['prodname'];
        $user = $row['username'];

        $glob = bool($row['global']);
        $enab = bool($row['defaultenable']);
        $dmon = bool($row['defaultmonitor']);
        $done = button('Done');

        $keys = find_keys($id, $db);
        $mets = find_mets($id, $db);

        if (($pprv) && ($user == $auth)) {
            $self = $env['self'];
            $act  = "$self?id=$id&act";
            $del  = html_link("$act=cdp", 'Delete this product');
            $edt  = html_link("$act=ep",  'Edit this product');
            echo "<br>$edt.<br><br>$del.<br>\n";
        }
        echo post_self();
        echo hidden('act', 'list');
        echo table_header();
        echo pretty_header('View Product', 2);
        echo two_col('Product name:', $name);
        echo two_col('Global:', $glob);
        echo two_col('Enable by default when provisioned:', $enab);
        echo two_col('Meter by default:', $dmon);

        if ($keys) {
            reset($keys);
            foreach ($keys as $key => $row) {
                $file = $row['filename'];
                echo two_col('Key File', $file);
            }
        }

        if ($mets) {
            reset($mets);
            foreach ($mets as $met => $row) {
                $file = $row['filename'];
                echo two_col('Meter File', $file);
            }
        }
        echo two_col('<br>', $done);
        echo table_footer();
        echo form_footer();
    } else {
        echo missing_product($env);
    }

    echo again($env);
}



function edit_keyfile(&$env, $db)
{
    echo again($env);
    $kid  = $env['kid'];
    $pid  = $env['id'];
    $gprv = $env['gprv'];
    $row = find_product($env, $db);
    $key = find_keyfile($pid, $kid, $db);
    if (($row) && ($key)) {
        $self = $env['self'];
        $pid  = $row['productid'];
        $name = $row['prodname'];
        $glob = $row['global'];
        $enab = $row['defaultenable'];
        $dmon = $row['defaultmonitor'];

        $enab = bool($enab);
        $dmon = bool($dmon);

        echo table_header();
        echo pretty_header('Product', 2);
        echo two_col('Product name:', $name);
        echo two_col('Global:', bool($glob));
        echo two_col('Enable by default when provisioned:', $enab);
        echo two_col('Meter by default:', $dmon);
        echo table_footer();

        echo post_self();
        echo hidden('act', 'ukf');
        echo hidden('id', $pid);
        echo hidden('kid', $kid);
        echo table_header();
        echo pretty_header('Edit Key File', 2);

        $keyf = $key['filename'];
        $keyf = textbox('keyf', 100, $keyf);
        echo two_col('File', $keyf);
        $update = button('Update');
        $cancel = button('Cancel');
        echo two_col($update, $cancel);
        echo table_footer();
        echo form_footer();
    } else {
        echo missing_product($env);
    }

    echo again($env);
}

function edit_meterfile(&$env, $db)
{
    echo again($env);
    $mid  = $env['mid'];
    $pid  = $env['id'];
    $gprv = $env['gprv'];
    $row = find_product($env, $db);
    $met = find_meterfile($pid, $mid, $db);
    if (($row) && ($met)) {
        $self = $env['self'];
        $pid  = $row['productid'];
        $name = $row['prodname'];
        $glob = $row['global'];
        $enab = $row['defaultenable'];
        $dmon = $row['defaultmonitor'];

        $enab = bool($enab);
        $dmon = bool($dmon);

        echo table_header();
        echo pretty_header('Product', 2);
        echo two_col('Product name:', $name);
        echo two_col('Global:', bool($glob));
        echo two_col('Enable by default when provisioned:', $enab);
        echo two_col('Meter by default:', $dmon);
        echo table_footer();

        echo post_self();
        echo hidden('act', 'umf');
        echo hidden('id', $pid);
        echo hidden('mid', $mid);
        echo table_header();
        echo pretty_header('Edit Meter File', 2);

        $metf = $met['filename'];
        $metf = textbox('metf', 100, $metf);
        echo two_col('File', $metf);
        $update = button('Update');
        $cancel = button('Cancel');
        echo two_col($update, $cancel);
        echo table_footer();
        echo form_footer();
    } else {
        echo missing_product($env);
    }

    echo again($env);
}

function copy_name(&$env, $name, $db)
{
    $row = find_product_name($env, $name, $db);
    while ($row) {
        debug_note("product $name exists");
        $name = "Copy of $name";
        $row = find_product_name($env, $name, $db);
    }
    return $name;
}



function copy_product(&$env, $db)
{
    echo again($env);
    debug_note("copy_product");
    $row = find_product($env, $db);
    if ($row) {
        $gprv = $env['gprv'];
        $id   = $row['productid'];
        $name = $row['prodname'];
        $enab = $row['defaultenable'];
        $dmon = $row['defaultmonitor'];

        $text = copy_name($env, $name, $db);

        $name = textbox('name', 100, $text);
        $glob = checkbox('glob', 0);
        $enab = checkbox('enab', $enab);
        $dmon = checkbox('dmon', $dmon);

        $copy   = button('Copy');
        $cancel = button('Cancel');

        echo post_self();
        echo hidden('act', 'cpc');
        echo hidden('id', $id);

        echo table_header();
        echo pretty_header('Copy This Product', 2);
        echo two_col('Product name:', $name);
        echo two_col('Global:', $glob);
        echo two_col('Enable by default when provisioned:', $enab);
        echo two_col('Meter by default:', $dmon);
        echo two_col($copy, $cancel);
        echo table_footer();
        echo form_footer();
    } else {
        $id = $env['id'];
        echo "Product <b>$id</b> was not found.<br>";
    }
    echo again($env);
}


function insert_file($table, $file, $pid, $db)
{
    $qf  = safe_addslashes($file);
    $sql = "insert into\n"
        . " $table set\n"
        . " productid=$pid,\n"
        . " filename='$qf'";
    $res = redcommand($sql, $db);
    $num = affected($res, $db);
    return ($num == 1) ? true : false;
}

function browse_name($name, $valu)
{
    if (isset($_FILES[$name]['name'])) {
        $valu = $_FILES[$name]['name'];
        print_r($_FILES);
        debug_note("browse name: $name, value:$valu");
    }
    return $valu;
}


function site_table($sites)
{
    if ($sites) {
        $head = explode('|', 'Site|Provisioned|Enabled|Metered');

        echo table_header();
        echo pretty_header('Site Assignments', 4);
        echo table_data($head, 1);
        reset($sites);
        foreach ($sites as $key => $row) {
            $site = $row['sitename'];
            $prov = $row['provisioned'];
            $enab = $row['enabled'];
            $metr = $row['metered'];

            $enab = ($prov) ? bool($enab) : '<br>';
            $metr = bool($metr);
            $prov = bool($prov);
            $args = array($site, $prov, $enab, $metr);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}

function host_table($hosts)
{
    if ($hosts) {
        $head = explode('|', 'Site|Machine|Provisioned|Enabled|Metered');
        echo table_header();
        echo pretty_header('Machine Assignments', 5);
        echo table_data($head, 1);
        reset($hosts);
        foreach ($hosts as $key => $row) {
            $site = $row['sitename'];
            $host = $row['machine'];
            $prov = $row['provisioned'];
            $enab = $row['enabled'];
            $metr = $row['metered'];

            $enab = ($prov) ? bool($enab) : '<br>';
            $metr = bool($metr);
            $prov = bool($prov);

            $args = array($site, $host, $prov, $enab, $metr);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}



function push_update(&$env, $db)
{
    $sites = array();
    $hosts = array();
    $prod = find_product($env, $db);
    $used = false;
    if ($prod) {
        $pid   = $prod['productid'];
        $name  = $prod['prodname'];
        $sites = site_assign($pid, $db);
        $hosts = host_assign($pid, $db);
        $used  = (($sites) || ($hosts)) ? true : false;
    }
    if ($used) {
        //      echo "<p>The product <b>$name</b> is being used.</p>";
        site_table($sites);
        host_table($hosts);

        $self = $env['self'];
        $act  = "$self?id=$pid&act";
        $yes  = html_link("$act=psh", 'Yes');
        $no   = html_link("$act=list", 'No');

        echo  "<p>Would you like to update the sites and machines"
            . " that use product <b>$name</b>?</p>"
            . "<p>"
            . "$yes &nbsp;&nbsp;&nbsp;&nbsp; $no\n"
            . "</p><br>\n\n";
    }
}


function push_sites($sites, $db)
{
    $num = 0;
    if ($sites) {
        reset($sites);
        foreach ($sites as $key => $row) {
            $good = false;
            $site = $row['sitename'];
            echo "<p>Updating site <b>$site</b> ...<p>\n";

            if (publish_site($site, $db)) {
                $good = true;
                $num++;
            }
            $fate = ($good) ? 'succeded' : 'failed';
            echo "<p>Update of <b>$site</b> $fate.</p><br>\n";
        }
    }
    return $num;
}

function push_hosts($hosts, $db)
{
    $num = 0;
    if ($hosts) {
        foreach ($hosts as $key => $row) {
            $good = false;
            $site = $row['sitename'];
            $host = $row['machine'];
            $text = ucwords($host);
            echo "<p>Updating <b>$text</b> at <b>$site</b> ...</p>\n";

            if (($site) && ($host)) {
                if (publish_host($site, $host, $db)) {
                    $good = true;
                    $num++;
                }
            }
            $fate = ($good) ? 'succeded' : 'failed';
            echo "<p>Update of <b>$text</b> at <b>$site</b> $fate.</p><br>\n";
        }
    }
    return $num;
}



function push_product(&$env, $db)
{
    echo again($env);
    debug_note("push_product");
    $sites = array();
    $hosts = array();
    $prod = find_product($env, $db);
    $used = false;
    if ($prod) {
        $pid   = $prod['productid'];
        $name  = $prod['prodname'];
        $sites = site_assign($pid, $db);
        $hosts = host_assign($pid, $db);
        $used  = (($sites) || ($hosts)) ? true : false;
    }
    if ($used) {
        echo "<p>The product <b>$name</b> is currently assigned.</p>";
        site_table($sites);
        $ss = push_sites($sites, $db);
        host_table($hosts);
        $hh = push_hosts($hosts, $db);
    } else {
        echo "<p>The product is not assigned.</p>\n";
    }
    echo again($env);
}



/*
    |  Edit Product Complete
    |  Edit Product Done
    |
    |  An existing product can become global if and only
    |  if no other product with that name already exists.
    */

function update_product(&$env, $db)
{
    $change = 0;
    $post = $env['post'];
    $act  = $env['act'];

    echo again($env);
    debug_note("update_product");
    $row  = find_product($env, $db);

    if ($row) {
        $self = $env['self'];
        $name = $env['name'];
        $user = $env['auth'];
        $keyf = $env['keyf'];
        $metf = $env['metf'];
        $gprv = $env['gprv'];
        $id   = $env['id'];
        $now  = $env['now'];
        $dmon = ($env['dmon']) ? 1 : 0;
        $enab = ($env['enab']) ? 1 : 0;
        $glob = ($env['glob']) ? 1 : 0;

        $keyf = browse_name('keyf', $keyf);
        $metf = browse_name('metf', $metf);

        $name = product_name($name);
        $keyf = product_name($keyf);
        $metf = product_name($metf);

        $dup  = find_product_name($env, $name, $db);
        $num  = 0;

        $error = '';
        if ($name == '') $error .= 'The product name is empty.<br>';
        if ($user == '') $error .= 'The user file name is empty.<br>';
        if (($dup) && ($dup['productid'] != $row['productid'])) {
            $num++;
        }
        if ($glob) {
            $num += count_product_name($name, $id, $db);
        }
        if ($num > 0) {
            $error .= "There is already a product named <b>$name</b><br>";
            $error .= "so you must use a different name.<br>";
        }

        if ($error) {
            echo $error;
        } else {
            $change = 0;

            $qn = safe_addslashes($name);
            $qu = safe_addslashes($user);

            $sql = "update Products set\n"
                . " prodname='$qn',\n"
                . " global=$glob,\n"
                . " defaultmonitor=$dmon,\n"
                . " defaultenable=$enab\n"
                . " where productid=$id\n"
                . " and username='$qu'";
            $res = redcommand($sql, $db);
            $num = affected($res, $db);
            if ($num == 1) {
                $name = $row['prodname'];
                echo "The definition of product <b>$name</b>"
                    .  " has been changed.<br><br>\n";
                $change++;
            }

            if ($keyf) {
                if (insert_file('KeyFiles', $keyf, $id, $db)) {
                    echo "Added the new key file <b>$keyf</b>"
                        . " to product <b>$name</b>.<br><br>\n";
                    $change++;
                }
            }

            if ($metf) {
                if (insert_file('MeterFiles', $metf, $id, $db)) {
                    echo "Added the new meter file <b>$metf</b>"
                        .  " to product <b>$name</b>.<br><br>\n";
                    $change++;
                }
            }

            if ($change) {
                $sql = "update Products set\n"
                    . " modified=$now\n"
                    . " where productid=$id\n"
                    . " and username='$qu'";
                $res = redcommand($sql, $db);
            } else {
                echo "Product <b>$name</b> is unchanged.<br>\n";
            }
        }
        $edit = 'Edit this product';
        $href = "$self?id=$id&act=ep";
        $link = html_link($href, $edit);
        echo "<br>$link.<br>\n";

        if ($change) {
            push_update($env, $db);
        }
    }
    echo again($env);
}


function update_file($prod, $sql, $db)
{
    $name = $prod['prodname'];
    $res = redcommand($sql, $db);
    $num = affected($res, $db);
    if ($num == 1) {
        $now = time();
        $pid = $prod['productid'];
        $sql = "update Products set\n"
            . " modified=$now\n"
            . " where productid=$pid";
        $res = redcommand($sql, $db);
        echo "Changes made to product <b>$name</b>.<br><br>\n";
    } else {
        echo "Product <b>$name</b> is unchanged.<br>\n";
    }
    return $num;
}

function update_keyfile(&$env, $db)
{
    echo again($env);
    $num  = 0;
    $pid  = $env['id'];
    $act  = $env['act'];
    $kid  = $env['kid'];
    $keyf = $env['keyf'];
    debug_note("update_keyfile: pid:$pid, kid:$kid");

    $row = find_product($env, $db);
    $key = find_keyfile($pid, $kid, $db);
    if (($row) && ($key)) {
        $name = $row['prodname'];
        $keyf = product_name($keyf);

        $qf  = safe_addslashes($keyf);
        $sql = "update KeyFiles set\n"
            . " filename = '$qf'\n"
            . " where keyid = $kid\n"
            . " and productid = $pid";
        $num = update_file($row, $sql, $db);
    }
    echo continue_return($env);
    if ($num == 1) {
        push_update($env, $db);
    }
    echo again($env);
}

function update_meterfile(&$env, $db)
{
    echo again($env);
    $num  = 0;
    $pid  = $env['id'];
    $act  = $env['act'];
    $mid  = $env['mid'];
    $metf = $env['metf'];
    debug_note("update_meterfile: pid:$pid, mid:$mid");

    $row = find_product($env, $db);
    $met = find_meterfile($pid, $mid, $db);
    if (($row) && ($met)) {
        $name = $row['prodname'];
        $metf = product_name($metf);

        $qf  = safe_addslashes($metf);
        $sql = "update MeterFiles set\n"
            . " filename = '$qf'\n"
            . " where meterid = $mid\n"
            . " and productid = $pid";
        $num = update_file($row, $sql, $db);
    }
    echo continue_return($env);
    if ($num == 1) {
        push_update($env, $db);
    }

    echo again($env);
}


function kill_pid($pid, $db, $table)
{
    if ($pid > 0) {
        $sql = "delete from $table\n where productid = $pid";
        redcommand($sql, $db);
    }
}


function kill_product($pid, $db)
{
    kill_pid($pid, $db, 'KeyFiles');
    kill_pid($pid, $db, 'MeterFiles');
    kill_pid($pid, $db, 'SiteAssignments');
    kill_pid($pid, $db, 'MachineAssignments');
    kill_pid($pid, $db, 'CryptKeys');
}

function audit_trail($user, $name, $acts, $db)
{
    $host = server_name($db);
    $qp  = safe_addslashes($name);
    $qh  = safe_addslashes($host);
    $qu  = safe_addslashes($user);
    $qa  = safe_addslashes($acts);
    $now = time();
    $sql = "insert into Audit set\n"
        . " who = 1,\n"
        . " servertime = $now,\n"
        . " clienttime = $now,\n"
        . " product = '$qp',\n"
        . " machine = '$qh',\n"
        . " owner = '$qu',\n"
        . " username = '$qu',\n"
        . " action = '$qa'";
    redcommand($sql, $db);
}

function audit_insert($user, $name, $db)
{
    audit_trail($user, $name, 'create', $db);
}

function audit_delete($user, $name, $db)
{
    audit_trail($user, $name, 'delete', $db);
}


function delete_product(&$env, $db)
{
    echo again($env);
    $id = $env['id'];
    debug_note("delete_product:$id");
    $row = find_product($env, $db);
    if ($row) {
        $name  = $row['prodname'];
        $pid   = $row['productid'];
        $user  = $env['auth'];

        echo "<p>Removing product <b>$name</b> ...</p>\n";

        $sites = site_assign($pid, $db);
        $hosts = host_assign($pid, $db);

        $qu  = safe_addslashes($user);
        $sql = "delete from Products\n"
            . " where productid = $id\n"
            . " and username = '$qu'";
        $res = redcommand($sql, $db);
        if (affected($res, $db) == 1) {
            kill_product($id, $db);
            echo "Product <b>$name</b> has been removed.<br>\n";
            audit_delete($user, $name, $db);
            site_table($sites);
            push_sites($sites, $db);
            host_table($hosts);
            push_hosts($hosts, $db);
        }
    }

    echo again($env);
}



function delete_keyfile(&$env, $db)
{
    echo again($env);
    $good = false;
    $pid = $env['id'];
    $kid = $env['kid'];
    $row = find_product($env, $db);
    $key = find_keyfile($pid, $kid, $db);
    $msg = '';
    debug_note("delete_keyfile, pid:$pid, kid:$kid");
    if (($row) && ($key)) {
        $keyf = $key['filename'];
        $name = $row['prodname'];
        $sql  = "delete from KeyFiles\n"
            . " where keyid=$kid\n"
            . " and productid=$pid";
        $res  = redcommand($sql, $db);
        if (affected($res, $db) == 1) {
            $good = true;
            $msg = "The keyfile <b>$keyf</b> has been removed from product <b>$name</b>.";
        } else {
            $msg = "Could not remove key file <b>$keyf</b> from product <b>$name</b>\n";
        }
    } else {
        if (!$key) $msg .= "Keyfile <b>$kid</b> does not exist.<br>";
        if (!$row) $msg .= "Product <b>$pid</b> does not exist.<br>";
    }
    echo "<br>\n$msg<br>\n\n";
    echo continue_return($env);
    if ($good) {
        push_update($env, $db);
    }
    echo again($env);
}


function delete_meterfile(&$env, $db)
{
    echo again($env);
    $good = false;
    $pid = $env['id'];
    $mid = $env['mid'];
    $row = find_product($env, $db);
    $met = find_meterfile($pid, $mid, $db);
    $msg = '';
    debug_note("delete_meter, pid:$pid, mid:$mid");
    if (($row) && ($met)) {
        $metf = $met['filename'];
        $name = $row['prodname'];
        $sql  = "delete from MeterFiles\n"
            . " where meterid=$mid\n"
            . " and productid=$pid";
        $res  = redcommand($sql, $db);
        if (affected($res, $db) == 1) {
            $good = true;
            $msg = "The meter file <b>$metf</b> has been removed from product <b>$name</b>.";
        } else {
            $msg = "Could not remove meter file <b>$metf</b> from product <b>$name</b>\n";
        }
    } else {
        if (!$met) $msg .= "Meter file <b>$mid</b> does not exist.<br>";
        if (!$row) $msg .= "Product <b>$pid</b> does not exist.<br>";
    }
    echo "<br>\n$msg<br>\n\n";
    echo continue_return($env);
    if ($good) {
        push_update($env, $db);
    }
    echo again($env);
}


function site_assign($pid, $db)
{
    $res = array();
    if ($pid > 0) {
        $sql = "select * from SiteAssignments\n"
            . " where productid = $pid\n"
            . " order by sitename";
        $res = find_many($sql, $db);
    }
    return $res;
}

function host_assign($pid, $db)
{
    $res = array();
    if ($pid > 0) {
        $sql = "select * from MachineAssignments\n"
            . " where productid = $pid\n"
            . " order by sitename, machine";
        $res = find_many($sql, $db);
    }
    return $res;
}





function warn_assign($pid, $name, $db)
{
    $hosts = host_assign($pid, $db);
    $sites = site_assign($pid, $db);
    $ud    = array();
    $wd    = array();
    if (($hosts) || ($sites)) {
        reset($hosts);
        foreach ($hosts as $key => $row) {
            $site = $row['sitename'];
            $host = $row['machine'];
            $enab = $row['enabled'];
            $prov = $row['provisioned'];
            $metr = $row['metered'];

            $warn = (($prov) && (!$enab));
            $enab = ($prov) ? bool($enab) : '<br>';
            $prov = bool($prov);
            $metr = bool($metr);
            $args = array($site, $host, $prov, $enab, $metr);
            if ($warn)
                $wd[] = $args;
            else
                $ud[] = $args;
        }
        reset($sites);
        foreach ($sites as $key => $row) {
            $site = $row['sitename'];
            $host = '<i>Global</i>';
            $enab = $row['enabled'];
            $prov = $row['provisioned'];
            $metr = $row['metered'];
            $warn = (($prov) && (!$enab));

            $enab = ($prov) ? bool($enab) : '<br>';
            $prov = bool($prov);
            $metr = bool($metr);
            $args = array($site, $host, $prov, $enab, $metr);
            if ($warn)
                $wd[] = $args;
            else
                $ud[] = $args;
        }
    }

    $head = explode('|', 'Site|Machine|Provisioned|Enabled|Metered');

    if ($ud) {
        echo table_header();
        echo pretty_header('This Product Is Used', 5);
        echo table_data($head, 1);
        foreach ($ud as $key => $args) {
            echo table_data($args, 0);
        }
        echo table_footer();
    }


    if ($wd) {

        // You are deleting the product blah that is
        // still provisioned and disabled on these machines:

        echo "<p>You are deleting the product <b>$name</b> that is<br>"
            . " still provisioned and disabled on these machines:"
            . "</p>\n";

        echo table_header();
        echo pretty_header('Serious Warnings', 5);
        echo table_data($head, 1);
        foreach ($wd as $key => $args) {
            echo table_data($args, 0);
        }
        echo table_footer();

        // If you continue, then you will NEVER be able to enable the product
        // on those machines, even if you re-create the product from scratch.

        // You should only do this if you are absolutely sure that those machines
        // have been decommissioned or are otherwise disabled in a way that
        // they will never again need to use that product.

        echo  "<p>If you continue, then you will NEVER be able to"
            .   " enable the product<br>on those machines, even"
            .  " if you re-create the product from scratch.</p>\n"

            .  "<p>You should only do this if you are absolutely"
            .  " sure that those machines<br> have been"
            .  " decommissioned or are otherwise disabled"
            .  " in a way that<br>they will never"
            .  " again need to use that product.</p>\n";
    }
}


function confirm_delete(&$env, $db)
{
    echo again($env);
    $id = $env['id'];
    debug_note("confirm delete:$id");
    $row = find_product($env, $db);
    if ($row) {
        $self = $env['self'];
        $id   = $row['productid'];
        $name = $row['prodname'];
        warn_assign($id, $name, $db);
        $yref = "$self?act=del&id=$id";
        $nref = $self;
        $yes  = html_link($yref, 'Yes');
        $no   = html_link($nref, 'No');
        echo "Are you sure you want to delete <b>$name</b>?<br><br>";
        echo "$yes &nbsp;&nbsp;&nbsp;&nbsp; $no<br>\n\n";
    } else {
        $msg = "Product <b>$id</b> was not found";
        echo "<br>$msg<br>\n\n";
    }

    echo again($env);
}


function confirm_keyfile(&$env, $db)
{
    echo again($env);
    $pid = $env['id'];
    $kid = $env['kid'];
    debug_note("confirm keyfile:id:$pid, kid:$kid");
    $row = find_product($env, $db);
    $key = find_keyfile($pid, $kid, $db);
    if (($row) && ($key)) {
        $self = $env['self'];
        $keyf = $key['filename'];
        $pid  = $row['productid'];
        $name = $row['prodname'];
        $act  = "$self?id=$pid&kid=$kid&act";
        $yes  = html_link("$act=dkf", 'Yes');
        $no   = html_link("$act=ep", 'No');

        $msg  = "<br><br>\n\n"
            . "Are you sure you want to delete the key file"
            . " <b>$keyf</b> from product <b>$name</b>?<br><br>"
            . "$yes &nbsp;&nbsp;&nbsp;&nbsp; $no<br>\n\n";
    } else {
        $msg = ($kid) ? "no such product" : "no such key file";
    }

    echo "$msg<br>\n";
    echo again($env);
}


function confirm_meterfile(&$env, $db)
{
    echo again($env);
    $pid = $env['id'];
    $mid = $env['mid'];
    debug_note("confirm meterfile:id:$pid, mid:$mid");
    $row = find_product($env, $db);
    $met = find_meterfile($pid, $mid, $db);
    if (($row) && ($met)) {
        $self = $env['self'];
        $metf = $met['filename'];
        $pid  = $row['productid'];
        $name = $row['prodname'];
        $act  = "$self?id=$pid&mid=$mid&act";
        $yes  = html_link("$act=dmf", 'Yes');
        $no   = html_link("$act=ep", 'No');

        $msg  = "<br><br>\n\n"
            . "Are you sure you want to delete the meter file"
            . " <b>$metf</b> from product <b>$name</b>?<br><br>"
            . "$yes &nbsp;&nbsp;&nbsp;&nbsp; $no<br>\n\n";
    } else {
        $msg = ($mid) ? "no such product" : "no such meter file";
    }

    echo "$msg<br>\n";
    echo again($env);
}



function duplicate_files($old, $new, $db)
{
    $keys = find_keys($old, $db);
    if ($keys) {
        reset($keys);
        foreach ($keys as $key => $row) {
            $file = $row['filename'];
            insert_file('KeyFiles', $file, $new, $db);
        }
    }
    $mets = find_mets($old, $db);
    if ($mets) {
        reset($mets);
        foreach ($mets as $met => $row) {
            $file = $row['filename'];
            insert_file('MeterFiles', $file, $new, $db);
        }
    }
}


function gang_insert($pid, $name, $db)
{
    $txt = '';
    for ($i = 1; $i <= 5; $i++) {
        $mfile = get_string("met$i", '');
        $pfile = get_string("key$i", '');
        if ($mfile) {
            if (insert_file('MeterFiles', $mfile, $pid, $db)) {
                $txt .= "Added the new meter file <b>$mfile</b> to product <b>$name</b>.<br>\n";
            }
        }
        if ($pfile) {
            if (insert_file('KeyFiles', $pfile, $pid, $db)) {
                $txt .= "Added the new key file <b>$pfile</b> to product <b>$name</b>.<br>\n";
            }
        }
    }
    if ($txt) {
        echo "<p>$txt</p>\n";
    }
}



/*
    |  This is known by four different names:
    |
    |   Add Product Complete
    |   Add Product Done
    |   Copy Product Complete
    |   Copy Product Done
    */

function create_product(&$env, $db)
{
    echo again($env);

    $now  = &$env['now'];
    $id   = &$env['id'];
    $user = &$env['auth'];
    $dmon = &$env['dmon'];
    $enab = &$env['enab'];
    $glob = &$env['glob'];
    $gprv = &$env['gprv'];
    $post = &$env['post'];
    $self = &$env['self'];
    $act  =  $env['act'];
    $name =  $env['name'];
    $good =  false;
    $dup  =  false;

    debug_note("create_product id:$id glob:$glob");
    $name = product_name($name);
    $row  = find_product_name($env, $name, $db);
    $dup  = ($row) ? true : false;
    $error = '';
    if ($user == '') $error .= 'The username is empty.<br>';
    if ($name == '') $error .= 'The product name is empty.<br>';
    if ($glob) {
        if (count_product_name($name, 0, $db)) {
            $dup = true;
        }
    }
    if ($dup) {
        $error .= "There is already a product named <b>$name</b><br>";
        $error .= "so you must use a different name.<br>";
    }

    if ($error == '') {
        $qn  = safe_addslashes($name);
        $qu  = safe_addslashes($user);
        $sql = "insert into Products set\n"
            . " username='$qu',\n"
            . " prodname='$qn',\n"
            . " global=$glob,\n"
            . " defaultmonitor=$dmon,\n"
            . " defaultenable=$enab,\n"
            . " created=$now,\n"
            . " modified=$now";
        $res = redcommand($sql, $db);
        if (affected($res, $db) == 1) {
            $new = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
            $old = $id;
            duplicate_files($old, $new, $db);
            $env['id'] = $new;
            $env['now'] = time();
            $good = true;
            audit_insert($user, $name, $db);
            gang_insert($new, $name, $db);
        }
    }
    if ($good) {
        $edit = 'Edit this product';
        $href = "$self?id=$new&act=ep";
        $link = html_link($href, $edit);
        echo "Product <b>$name</b> has been created."
            . "<br><br>\n"
            . "$link.<br>\n";
    } else {
        if ($error) {
            echo $error;
        } else {
            echo "Product <b>$name</b> was not created.<br>";
        }
    }
    echo again($env);
}

function unknown_action(&$env, $db)
{
    debug_note("unknown action");
}

function one_shot_deal(&$env, $db)
{;
}


/*
    |  Main program
    */

$now   = time();
$db    = db_connect();
$auth  = process_login($db);
$comp  = component_installed();
$act   = get_string('act', 'list');
$post  = get_string('submit', '');
$dbg   = get_integer('debug', 1);
$user  = user_data($auth, $db);
$priv  = @($user['priv_debug']) ?   1  : 0;
$pprv  = @($user['priv_provis']) ?  1  : 0;
$debug = @($user['priv_debug']) ? $dbg : 0;

/*
    |  Users who do not have priv_provis can
    |  examine products but not change them.
    |
    |  This means that all they get to do is
    |  list or view ... and that's the only
    |  links we give them.
    |
    |  But remember, anyone can type anything they
    |  please into their browser command line.
    */

if ((!$pprv) && ($act != 'view')) {
    $act = 'list';
}

if (!$priv) {
    $tmp = "|$act|";
    $txt = '||||xxx|dbg|';
    $pos = strpos($txt, $tmp);
    if ($pos > 1) $act = 'list';
}

$act   = redirect_action($act, $post);
$title = build_title($act);

$nav = provis_navigate();
$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $auth, $nav, 0, 0, $db);

$date = datestring(time());


$musr = server_def('master_user', constVendorUser, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($debug) echo "<h2>$date</h2>";

debug_array($debug, $_POST);

$env = array();
$env['db']     = $db;
$env['id']     = get_integer('id', 0);
$env['act']    = $act;
$env['ord']    = get_integer('ord', 0);
$env['kid']    = get_integer('kid', 0);
$env['mid']    = get_integer('mid', 0);
$env['now']    = $now;
$env['priv']   = $priv;
$env['pprv']   = $pprv;
$env['musr']   = $musr;
$env['dmon']   = get_integer('dmon', 0);
$env['enab']   = get_integer('enab', 0);
$env['glob']   = get_integer('glob', 0);
$env['self']   = server_var('PHP_SELF');
$env['auth']   = $auth;
$env['user']   = $user;
$env['gprv']   = ($auth == $musr) ? 1 : 0;
$env['name']   = get_string('name', '');
$env['keyf']   = get_string('keyf', '');
$env['metf']   = get_string('metf', '');
$env['self']   = server_var('PHP_SELF');
$env['post']   = $post;
$env['debug']  = $debug;
$env['limit']  = get_integer('limit', 50);

db_change($GLOBALS['PREFIX'] . 'provision', $db);
switch ($act) {
    case 'ap':
        add_product($env, $db);
        break;
    case 'apc':
        create_product($env, $db);
        break;
    case 'cdkf':
        confirm_keyfile($env, $db);
        break;
    case 'cdmf':
        confirm_meterfile($env, $db);
        break;
    case 'cdp':
        confirm_delete($env, $db);
        break;
    case 'cp':
        copy_product($env, $db);
        break;
    case 'cpc':
        create_product($env, $db);
        break;
    case 'dbg':
        debug_products($env, $db);
        break;
    case 'del':
        delete_product($env, $db);
        break;
    case 'dkf':
        delete_keyfile($env, $db);
        break;
    case 'dmf':
        delete_meterfile($env, $db);
        break;
    case 'ekf':
        edit_keyfile($env, $db);
        break;
    case 'emf':
        edit_meterfile($env, $db);
        break;
    case 'ep':
        edit_product($env, $db);
        break;
    case 'epc':
        update_product($env, $db);
        break;
    case 'list':
        list_products($env, $db);
        break;
    case 'psh':
        push_product($env, $db);
        break;
    case 'ukd':
        update_keyfile($env, $db);
        break;
    case 'ukf':
        update_keyfile($env, $db);
        break;
    case 'umd':
        update_meterfile($env, $db);
        break;
    case 'umf':
        update_meterfile($env, $db);
        break;
    case 'view':
        view_products($env, $db);
        break;
    case 'xxx':
        one_shot_deal($env, $db);
        break;
    default:
        unknown_action($env, $db);
        break;
}
db_change($GLOBALS['PREFIX'] . 'core', $db);
echo head_standard_html_footer($auth, $db);
