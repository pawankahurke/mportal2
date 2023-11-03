<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
 2-Sep-03   EWB     created
 2-Sep-03   EWB     delete_file_record
 3-Sep-03   EWB     file_site_list
 3-Sep-03   EWB     safe_unlink
*/


/*
    |  Hopefully this is paranoid enough for every day use.
    |  There's several possibilities:
    |
    |  1. We are running as root.  In this case, I think
    |     the only reasonable thing to do is log an error
    |     message and refuse to to proceed.
    |  2. The file exists, but we don't own it.
    |     Log the event, and return an error status.
    |  3. The file exists, but we can't remove it.
    |     As far as the user is concerned, everything
    |     is fine, but we'll want to log it anyway.
    |  4. The file was missing to begin with.  Since we
    |     want to remove it, this technically counts as 
    |     success ... but I'm going to log it anyway.
    |  5. The path is empty ... just return a failure
    |     status, don't log anything.
    */

function safe_unlink($path)
{
    $good = false;
    $msg  = '';
    if ($path) {
        if (file_exists($path)) {
            $euid = posix_geteuid();
            $fuid = fileowner($path);
            debug_note("safe_unlink: euid:$euid, fuid:$fuid, path:$path");
            if (($euid > 0) && ($euid == $fuid))
                $good = unlink($path);
            else if ($euid != $fuid)
                $msg = "unlink: euid:$euid, fuid:$fuid, path:$path";
            else
                $msg = "unlink: euid:$euid ... we should NOT be running as root";
        } else {
            $good = true;
            $msg = "unlink: $path: not found";
        }
    }
    if ((!$good) && (!$msg)) {
        $msg = "unlink: $path: unknown failure";
    }
    if ($msg) {
        logs::log(__FILE__, __LINE__, $msg, 0);
        debug_note($msg);
    }
    return $good;
}



function delete_file_record($row, $admin, $user, $db)
{
    $fid  = $row['id'];
    $path = $row['path'];
    $name = $row['name'];
    $good = false;
    if ($fid > 0) {
        if ($admin) {
            $sql  = "delete from Files\n";
            $sql .= " where id = $fid";
        } else {
            $sql  = "delete from Files\n";
            $sql .= " where id = $fid\n";
            $sql .= " and username = '$user'";
        }
        $res = redcommand($sql, $db);
        if ($res) {
            if (mysqli_affected_rows($db) == 1) {
                $good = true;
            }
        }

        /*
            |  We don't care how many records this might
            |  delete, since it's ok even if there 
            |  weren't any to begin with.
            */

        if ($good) {
            $sql  = "delete from FileSites\n";
            $sql .= " where fid = $fid";
            redcommand($sql, $db);
        }
    }

    if ($good) {
        safe_unlink($path);
    }

    return $good;
}


/*
    |  Creates a new FileSite record ... returns
    |  true if the record was created successfully.
    */

function create_filesite($fid, $site, $db)
{
    $good = false;
    debug_note("create_filesite $fid, $site");
    if (($fid > 0) && ($site)) {
        $qs   = safe_addslashes($site);
        $sql  = "insert into FileSites set\n";
        $sql .= " fid=$fid,\n";
        $sql .= " sitename='$qs'";
        $res  = redcommand($sql, $db);
        if ($res) {
            if (mysqli_affected_rows($db) == 1) {
                $good = true;
            }
        }
    }
    return $good;
}


/*
    |  Returns a list of sites associated with each file,
    |  as specified by the records in the FileSites table.
    | 
    |  It's unusual for the list to be empty, but we should
    |  be prepared for that to happen.
    |
    |  Also note that unlike the $carr array, the index values 
    |  do NOT correspond to records in the Customers table.
    */

function file_site_list($fid, $db)
{
    $list = array();
    if (($db) && ($fid > 0)) {
        $sql  = "select * from FileSites\n";
        $sql .= " where fid = $fid\n";
        $sql .= " order by sitename";
        $tmp  = find_many($sql, $db);
        reset($tmp);
        foreach ($tmp as $key => $row) {
            $list[] = $row['sitename'];
        }
    }
    return $list;
}
