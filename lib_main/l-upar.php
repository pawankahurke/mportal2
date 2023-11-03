<?php




  


   

    function update_asset($row,$db)
    {
        $qn =   safe_addslashes($row['name']);
        $qu =   safe_addslashes($row['username']);
        $ql =   safe_addslashes($row['emaillist']);
        $qf =   safe_addslashes($row['format']);
        $q1 = @ safe_addslashes(strval($row['order1']));
        $q2 = @ safe_addslashes(strval($row['order2']));
        $q3 = @ safe_addslashes(strval($row['order3']));
        $q4 = @ safe_addslashes(strval($row['order4']));
        $qt =   safe_addslashes($row['subject_text']);
        $qurl = safe_addslashes($row['xmlurl']);
        $qpas = safe_addslashes($row['xmlpass']);
        $qusr = safe_addslashes($row['xmluser']);
        $qfil = safe_addslashes($row['xmlfile']);

        $id         = $row['id'];
        $log        = $row['log'];
        $gbl        = $row['global'];
        $cycle      = $row['cycle'];
        $defmail    = $row['defmail'];
        $file       = $row['file'];
        $links      = $row['links'];
        $last_run   = $row['last_run'];
        $next_run   = $row['next_run'];
        $this_run   = $row['this_run'];
        $enabled    = $row['enabled'];
        $content    = $row['content'];
        $retries    = $row['retries'];
        $i_user     = $row['include_user'];
        $i_text     = $row['include_text'];
        $skip_owner = $row['skip_owner'];
        $tabular    = $row['tabular'];
        $xmlpasv    = $row['xmlpasv'];
        $g_include  = $row['group_include'];
        $g_exclude  = $row['group_exclude'];

        $hour = @ intval($row['hour']);
        $mint = @ intval($row['minute']);
        $wday = @ intval($row['wday']);
        $mday = @ intval($row['mday']);
        $qid  = @ intval($row['searchid']);
        $chng = @ intval($row['change_rpt']);
        $umin = @ intval($row['umin']);
        $umax = @ intval($row['umax']);
        $ctim = @ intval($row['created']);
        $mtim = @ intval($row['modified']);

        $cmd = ($id)? 'update' : 'insert into';
        $sql = "$cmd AssetReports set\n"
             . " global = $gbl,\n"
             . " name = '$qn',\n"
             . " username = '$qu',\n"
             . " emaillist = '$ql',\n"
             . " defmail = $defmail,\n"
             . " file = $file,\n"
             . " links = $links,\n"
             . " format = '$qf',\n"
             . " cycle = $cycle,\n"
             . " hour = $hour,\n"
             . " minute = $mint,\n"
             . " wday = $wday,\n"
             . " mday = $mday,\n"
             . " enabled = $enabled,\n"
             . " last_run = $last_run,\n"
             . " next_run = $next_run,\n"
             . " this_run = $this_run,\n"
             . " order1 = '$q1',\n"
             . " order2 = '$q2',\n"
             . " order3 = '$q3',\n"
             . " order4 = '$q4',\n"
             . " searchid = $qid,\n"
             . " change_rpt = $chng,\n"
             . " content = $content,\n"
             . " created = $ctim,\n"
             . " modified = $mtim,\n"
             . " retries = $retries,\n"
             . " log = $log,\n"
             . " umax = $umax,\n"
             . " umin = $umin,\n"
             . " include_user = $i_user,\n"
             . " include_text = $i_text,\n"
             . " subject_text = '$qt',\n"
             . " skip_owner = $skip_owner,\n"
             . " tabular = $tabular,\n"
             . " xmlurl  = '$qurl',\n"
             . " xmluser = '$qusr',\n"
             . " xmlpass = '$qpas',\n"
             . " xmlfile = '$qfil',\n"
             . " xmlpasv = $xmlpasv,\n"
             . " group_include = '$g_include',\n"
             . " group_exclude = '$g_exclude'\n";
        if ($id) $sql .= "\n where id = $id";
        $res = redcommand($sql,$db);
        $num = affected($res,$db);
        if (($num) && (!$id))
        {
            $num = mysqli_insert_id($db);
        }
        return $num;
    }


    
    function return_asset_url()
    {
        $a_id  = get_integer('asset_id','');
        $a_act = get_string('asset_act','');
        return "rid=$a_id&act=$a_act";
    }


   
    function preserve_asset_state($aid,$act)
    {
        return "asset_id=$aid&asset_act=$act";
    }
