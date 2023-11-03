<?php






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
