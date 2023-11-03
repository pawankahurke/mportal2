<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
10-Oct-02   EWB     Created.
*/

/*
    |  This is for pages that need to look up dids.
    |
    |     a-query
    |     a-details
    |    cr-report
    */

function did_error($name)
{
    $msg = "Error -- Field ($name) does not exist.";
    $msg = "<font color='red'>$msg</font><br>\n";
    return $msg;
}


function find_did($name, $db)
{
    $qnm  = safe_addslashes($name);
    $did  = 0;
    $sql  = "select * from DataName where";
    $sql .= " name = '$qnm'";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
            $did = $row['dataid'];
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    if ($did == 0) {
        echo did_error($name);
        logs::log(__FILE__, __LINE__, "DataName: $name not found", 0);
    }
    return $did;
}
