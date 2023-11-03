<?php

/*
Revision history:

Date        Who     What
----        ---     ----
16-Mar-04   EWB     Created.
21-Apr-04   EWB     Clear the cache when we change a variable.
 9-May-05   EWB     Adapt for new database.
 1-Jun-05   EWB     Legacy Checksum Cache
12-Oct-05   BTE     Changed reference from gconfig to core.
01-May-06   BTE     Bug 3355: Scrip Config Status Page: Various Changes.

*/

define('constVendorUser', 'hfn');
define('constProvisScope', 229);
define('constMeterScope',  230);
define('constProductList', 'ProductList');

function missing_site_variables($site)
{
    $ps = constProvisScope;
    $ms = constMeterScope;
    echo "<p>Missing site variables for scrips $ps and $ms<br>"
        . "at site <b>$site</b>.  This just means that the<br>"
        . "client varsion is too old to support provisioning.<br>"
        . "Update your client software and try again later.</p>";
}

function missing_host_variables($site, $host)
{
    $ps  = constProvisScope;
    $ms  = constMeterScope;
    $msg = ucwords($host);
    echo "<p>Missing local variables for scrips $ps and $ms<br>"
        .  "for machine <b>$msg</b> at site <b>$site</b>.<br>"
        .  "This just means that the client varsion is too old<br>"
        .  "to support provisioning.<br>"
        .  "Update your client software and try again later.</p>\n";
}

function missing_keyfile($pid, $name)
{
    debug_note("missing_keyfile($pid,$name)");

    echo "<p>Product <b>$name</b> has no key files"
        .  " and therefore cannot be provisioned.</p>\n";
}

function missing_meterfile($pid, $name)
{
    debug_note("missing_meterfile($pid,$name)");

    echo "<p>Product <b>$name</b> has no meter files"
        .  " and therefore cannot be metered.</p>\n";
}


/*
    |  Returns the list of key files for 
    |  the specified products.
    */

function provis_pids($pids, $db)
{
    $keys = array();
    foreach ($pids as $key => $pid) {
        $keys[$pid] = array();
    }
    if ($pids) {
        $text = join(',', $pids);
        $sql  = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "provision.KeyFiles\n"
            . " where productid in ($text)";
        $list = find_many($sql, $db);
        reset($list);
        foreach ($list as $key => $row) {
            $file = $row['filename'];
            $pid  = $row['productid'];
            $kid  = $row['keyid'];
            $keys[$pid][$kid] = $file;
        }
    }
    return $keys;
}



/*
    |  Returns the list of meter files for 
    |  the specified products.
    */

function meter_pids($pids, $db)
{
    $mets = array();
    foreach ($pids as $key => $pid) {
        $mets[$pid] = array();
    }
    if ($pids) {
        $text = join(',', $pids);
        $sql  = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "provision.MeterFiles\n"
            . " where productid in ($text)";
        $list = find_many($sql, $db);
        reset($list);
        foreach ($list as $key => $row) {
            $file = $row['filename'];
            $pid  = $row['productid'];
            $mid  = $row['meterid'];
            $mets[$pid][$mid] = $file;
        }
    }
    return $mets;
}


/*
    |  Returns a list of products
    |  from a list of pids.
    */

function product_pids($pids, $db)
{
    $prds = array();
    if ($pids) {
        $text = join(',', $pids);
        $sql  = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "provision.Products\n"
            . " where productid in ($text)";
        $list = find_many($sql, $db);
        reset($list);
        foreach ($list as $key => $row) {
            $pid  = $row['productid'];
            $prds[$pid] = $row;
        }
    }
    return $prds;
}


/*
    |  Format correctly for the ProductList variables
    |  for scrips 229 and 230.
    */

function build_mval_pval(&$pids, &$prds, &$mv, &$pv, $db)
{
    $prod = product_pids($pids, $db);
    $keys = provis_pids($pids, $db);
    $mets = meter_pids($pids, $db);

    $mval = array();
    $pval = array();

    reset($prds);
    foreach ($prds as $pid => $row) {
        $user = $prod[$pid]['username'];
        $name = $prod[$pid]['prodname'];
        if ($row['metered']) {
            if (safe_count($mets[$pid])) {
                $file = join(',', $mets[$pid]);
                $args = array($user, $name, $file);
                $mval[] = join(',', $args);
            } else {
                missing_meterfile($pid, $name);
            }
        }
        if ($row['provisioned']) {
            if (safe_count($keys[$pid])) {
                $enab = $row['enabled'];
                $file = join(',', $keys[$pid]);
                $args = array($user, $name, $enab, $file);
                $pval[] = join(',', $args);
            } else {
                missing_keyfile($pid, $name);
            }
        }
    }

    $mv = join("\r\n", $mval);
    $pv = join("\r\n", $pval);
}


function site_pids($site, &$pids, &$prds, $db)
{
    $qs   = safe_addslashes($site);
    $sql  = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "provision.SiteAssignments\n"
        . " where sitename = '$qs'";
    $list = find_many($sql, $db);
    foreach ($list as $key => $row) {
        $pid = $row['productid'];
        $pids[] = $pid;
        $prds[$pid] = $row;
    }
}


function host_pids($site, $host, &$pids, &$prds, $db)
{
    $qs  = safe_addslashes($site);
    $qh  = safe_addslashes($host);
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "provision.MachineAssignments where\n"
        . " sitename = '$qs' and\n"
        . " machine = '$qh'";
    $list = find_many($sql, $db);
    foreach ($list as $key => $row) {
        $pid = $row['productid'];
        $pids[] = $pid;
        $prds[$pid] = $row;
    }
}


/*
    |  We don't want to update the revision levls unless
    |  mysql confirms that the value of the global variable
    |  has really changed.
    |
    |  We return the number of updated global variables,
    |  which we expect to be zero or one.
    */

function site_variable($sgrp, $scop, $valu, $now, $db)
{
    $num = 0;
    $gid = ($sgrp) ? $sgrp['mgroupid'] : 0;
    $var = find_var(constProductList, $scop, $db);
    if ($var) {
        $vid = $var['varid'];
        $tmp = server_name($db);
        debug_note("site variable (s:$scop,v:$vid,g:$gid)");
        $num = update_value(
            $vid,
            $gid,
            $now,
            $tmp,
            $valu,
            constSourceScripConfig,
            $db
        );
    }
    return $num;
}


/*
    |  This updates the site configuration (core.VarValues)
    |  directly from the SiteAssignments table.
    */

function publish_site($site, $db)
{
    $pids = array();
    $prds = array();

    debug_note("publish_site: $site");
    site_pids($site, $pids, $prds, $db);
    $mv = '';
    $pv = '';
    build_mval_pval($pids, $prds, $mv, $pv, $db);

    $sgrp = find_site_mgrp($site, $db);

    $now = time();
    $ps  = constProvisScope;
    $ms  = constMeterScope;
    $act = 'unchanged';
    $num = 0;

    $num += site_variable($sgrp, $ms, $mv, $now, $db);
    $num += site_variable($sgrp, $ps, $pv, $now, $db);
    if ($num > 0) {
        $act = 'updated';
        dirty_site($site, $db);
        site_revision($site, $now, $db);
    }
    $date = date('F jS', $now);
    $time = date('g:i:s A', $now);
    echo "<br>\n"
        . "Site <b>$site</b> $act on $date at $time.<br>\n"
        . "<br>\n";
    return $num;
}


/*
    |  We don't want to update the revision levels unless
    |  mysql confirms that the value of the local variable
    |  has really changed.
    |
    |  We return the number of updated local variables,
    |  which we expect to be zero or one.
    */

function host_variable($hgrp, $scop, $valu, $now, $db)
{
    $num = 0;
    $gid = ($hgrp) ? $hgrp['mgroupid'] : 0;
    $var = find_var(constProductList, $scop, $db);
    if ($var) {
        $vid = $var['varid'];
        $tmp = server_name($db);
        debug_note("host variable (s:$scop,v:$vid,g:$gid)");
        $num = update_value(
            $vid,
            $gid,
            $now,
            $tmp,
            $valu,
            constSourceScripConfig,
            $db
        );
    }
    return $num;
}


function publish_host($site, $host, $db)
{
    debug_note("publish host: <b>$host</b> at <b>$site</b>.");
    $pids = array();
    $prds = array();

    host_pids($site, $host, $pids, $prds, $db);

    $hgrp = find_hgrp($site, $host, $db);

    $mv = '';
    $pv = '';
    build_mval_pval($pids, $prds, $mv, $pv, $db);

    $ps  = constProvisScope;
    $ms  = constMeterScope;
    $now = time();
    $act = 'unchanged';
    $num = 0;

    $num += host_variable($hgrp, $ms, $mv, $now, $db);
    $num += host_variable($hgrp, $ps, $pv, $now, $db);
    if ($num > 0) {
        dirty_host($site, $host, $db);
        host_revision($site, $host, $now, $db);
        $act = 'updated';
    }

    $date = date('F jS', $now);
    $time = date('g:i:s A', $now);
    $name = ucwords($host);
    echo "<br>\n"
        . "<b>$name</b> at <b>$site</b> $act on $date at $time.<br>\n"
        . "<br>\n";
    return $num;
}
