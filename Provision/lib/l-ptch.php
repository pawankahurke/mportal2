<?php

/*
Revision history:

Date        Who     What
----        ---     ----
15-Jun-04   EWB     Created.
 7-Jul-04   EWB     Added patch dirty bit.
 8-Jul-04   EWB     Patch dirty managed by l-pdrt.php
 6-Aug-04   EWB     Added database specifier to find_pcat_name.
31-Oct-06   BTE     Bug 3794: Finish server for first customer release of MUM
                    changes.
09-Dec-06   BTE     Bug 3842: Make mandatory an update attribute.

*/

function find_pcat_name($name, $db)
{
    $row = array();
    if ($name) {
        $qn  = safe_addslashes($name);
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.PatchCategories\n"
            . " where category = '$qn'";
        $row = find_one($sql, $db);
    }
    return $row;
}

function find_pgrp_name($name, $db)
{
    $row = array();
    if ($name) {
        $qn  = safe_addslashes($name);
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.PatchGroups\n"
            . " where name = '$qn'";
        $row = find_one($sql, $db);
    }
    return $row;
}

function pcat_options($db)
{
    $txt = str_repeat('&nbsp;', 10);
    $opt = array($txt);
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "softinst.PatchCategories\n"
        . " order by precedence";
    $set = find_many($sql, $db);
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $kid = $row['pcategoryid'];
            $cat = $row['category'];
            $opt[$kid] = $cat;
        }
    }
    return $opt;
}


function pgrp_options($auth, $db)
{
    $opt  = array();
    $grps = array();
    if ($auth) {
        $qu   = safe_addslashes($auth);
        $sql  = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.PatchGroups\n"
            . " where global = 1\n"
            . " or username = '$qu'\n"
            . " order by pcategoryid, name";
        $grps = find_many($sql, $db);
    }
    if ($grps) {
        $none = str_repeat('&nbsp;', 10);
        $opt[0][0] = $none;
        reset($grps);
        foreach ($grps as $key => $row) {
            $jid = $row['pgroupid'];
            $kid = $row['pcategoryid'];
            $grp = $row['name'];
            $opt[$kid][0] = $none;
            $opt[$kid][$jid] = $grp;
        }
    }
    return $opt;
}


function PTCH_GetAllTypes()
{
    $types = array();
    $types[constPatchTypeAll] = constTagAny;
    $types[constPatchTypeNotDisplayed] = constTagNone;
    $types[constPatchTypeUndefined] = constPatchTypeUndefinedStr;
    $types[constPatchTypeUpdate] = constPatchTypeUpdateStr;
    $types[constPatchTypeServicePack] = constPatchTypeServicePackStr;
    $types[constPatchTypeRollup] = constPatchTypeRollupStr;
    $types[constPatchTypeSecurity] = constPatchTypeSecurityStr;
    $types[constPatchTypeCritical] = constPatchTypeCriticalStr;

    return $types;
}
