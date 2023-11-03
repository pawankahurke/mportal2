<?php




  


    function inactive($rid,$db)
    {
        $now = time();
        $sql = "Update  ".$GLOBALS['PREFIX']."event.Reports set\n"
             . " this_run = 0,\n"
             . " next_run = 0,\n"
             . " modified = $now\n"
             . " where next_run >= 0\n"
             . " and id = $rid";
        $res = redcommand($sql,$db);
        return affected($res,$db);
    }


   

    function update_report($row,$db)
    {
        $qn =   safe_addslashes($row['name']);
        $qu =   safe_addslashes($row['username']);
        $ql =   safe_addslashes($row['emaillist']);
        $qf =   safe_addslashes($row['format']);
        $q1 = @ safe_addslashes(strval($row['order1']));
        $q2 = @ safe_addslashes(strval($row['order2']));
        $q3 = @ safe_addslashes(strval($row['order3']));
        $q4 = @ safe_addslashes(strval($row['order4']));
        $qc = @ safe_addslashes(strval($row['config']));
        $qs =   safe_addslashes($row['search_list']);
        $qt =   safe_addslashes($row['subject_text']);

        $id        = $row['id'];
        $gbl       = $row['global'];
        $cycle     = $row['cycle'];
        $defmail   = $row['defmail'];
        $file      = $row['file'];
        $links     = $row['links'];
        $assetlinks= $row['assetlinks'];
        $last_run  = $row['last_run'];
        $enabled   = $row['enabled'];
        $details   = $row['details'];
        $include_user  = $row['include_user'];
        $include_text  = $row['include_text'];
        $skip_owner    = $row['skip_owner'];
        $aggregate     = $row['aggregate'];
        $omit          = $row['omit'];
        $detaillinks   = $row['detaillinks'];
        $group_include = $row['group_include'];
        $group_exclude = $row['group_exclude'];

        $hour      = @ intval($row['hour']);
        $minute    = @ intval($row['minute']);
        $wday      = @ intval($row['wday']);
        $mday      = @ intval($row['mday']);
        $umin      = @ intval($row['umin']);
        $umax      = @ intval($row['umax']);
        $created   = @ intval($row['created']);
        $modified  = @ intval($row['modified']);
        $retries   = @ intval($row['retries']);

        $cmd = ($id)? 'update' : 'insert into';
        $sql = "$cmd Reports set\n"
             . " global = $gbl,\n"
             . " name = '$qn',\n"
             . " username = '$qu',\n"
             . " emaillist = '$ql',\n"
             . " defmail = $defmail,\n"
             . " file = $file,\n"
             . " links = $links,\n"
             . " assetlinks = $assetlinks,\n"
             . " format = '$qf',\n"
             . " cycle = $cycle,\n"
             . " hour = $hour,\n"
             . " minute = $minute,\n"
             . " wday = $wday,\n"
             . " mday = $mday,\n"
             . " enabled = $enabled,\n"
             . " last_run = $last_run,\n"
             . " order1 = '$q1',\n"
             . " order2 = '$q2',\n"
             . " order3 = '$q3',\n"
             . " order4 = '$q4',\n"
             . " details = $details,\n"
             . " umin = $umin,\n"
             . " umax = $umax,\n"
             . " created = $created,\n"
             . " modified = $modified,\n"
             . " retries = $retries,\n"
             . " config = '$qc',\n"
             . " search_list = '$qs',\n"
             . " include_user = $include_user,\n"
             . " include_text = $include_text,\n"
             . " subject_text = '$qt',\n"
             . " skip_owner   = $skip_owner,\n"
             . " aggregate    = $aggregate,\n"
             . " omit         = $omit,\n"
             . " detaillinks  = $detaillinks,\n"
             . " group_include = '$group_include',\n"
             . " group_exclude = '$group_exclude'";
        if ($id) $sql .= "\n where id = $id";
        $res = redcommand($sql,$db);
        $num = affected($res,$db);
        if (($num) && (!$id))
        {
            $num = mysqli_insert_id($db);
        }
        return $num;
    }

   
    function return_report_url()
    {
        $r_id  = get_integer('report_id','');
        $r_act = get_string('report_act','');
        return "rid=$r_id&act=$r_act";
    }
