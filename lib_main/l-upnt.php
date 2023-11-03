<?php




  


   

    function update_notify($row,$db)
    {
        $id        = $row['id'];
        $gbl       = $row['global'];
        $ntype     = $row['ntype'];
        $days      = $row['days'];
        $solo      = $row['solo'];
        $priority  = $row['priority'];
        $console   = $row['console'];
        $email     = $row['email'];
        $defmail   = $row['defmail'];
        $links     = $row['links'];
        $search_id = $row['search_id'];
        $threshold = $row['threshold'];
        $seconds   = $row['seconds'];
        $retries   = $row['retries'];
        $suspend   = $row['suspend'];
        $enabled   = $row['enabled'];
        $last_run  = $row['last_run'];
        $next_run  = $row['next_run'];
        $this_run  = $row['this_run'];
        $ginclude  = $row['ginclude'];
        $gexclude  = $row['gexclude'];
        $gsuspend  = $row['gsuspend'];
        $skip_owner = $row['skip_owner'];
        $email_footer     = $row['email_footer'];
        $email_per_site   = $row['email_per_site'];
        $email_sender     = $row['email_sender'];
        $created   = intval($row['created']);
        $modified  = intval($row['modified']);
        $autotask = $row['autotask'];
        if($autotask)
        {
            
            $email_per_site = 1;
        }

        $qn  = safe_addslashes($row['name']);
        $qc  = safe_addslashes($row['config']);
        $qu  = safe_addslashes($row['username']);
        $ql  = safe_addslashes($row['emaillist']);
        $qe  = safe_addslashes($row['email_footer_txt']);

                
        $cmd = ($id)? 'update' : 'insert into';
        $sql = "$cmd Notifications set\n"
             . " name = '$qn',\n"
             . " days = $days,\n"
             . " solo = $solo,\n"
             . " ntype = $ntype,\n"
             . " links = $links,\n"
             . " email = $email,\n"
             . " global = $gbl,\n"
             . " config = '$qc',\n"
             . " defmail = $defmail,\n"
             . " seconds = $seconds,\n"
             . " console = $console,\n"
             . " suspend = $suspend,\n"
             . " created = $created,\n"
             . " retries = $retries,\n"
             . " enabled = $enabled,\n"
             . " priority = $priority,\n"
             . " username = '$qu',\n"
             . " last_run = $last_run,\n"
             . " next_run = $next_run,\n"
             . " this_run = $this_run,\n"
             . " modified = $modified,\n"
             . " group_include = '$ginclude',\n"
             . " group_exclude = '$gexclude',\n"
             . " group_suspend = '$gsuspend',\n"
             . " emaillist = '$ql',\n"
             . " search_id = $search_id,\n"
             . " threshold = $threshold,\n"
             . " skip_owner  = $skip_owner,\n"
             . " email_footer     = $email_footer,\n"
             . " email_per_site   = $email_per_site,\n"
             . " email_footer_txt = '$qe',\n"
             . " email_sender     = $email_sender,\n"
             . " autotask = $autotask";
        if ($id) $sql .= "\n where id = $id";
        $res = redcommand($sql,$db);
        $num = affected($res,$db);
        if (($num) && (!$id))
        {
            $num = mysqli_insert_id($db);
        }
        return $num;
    }


    
    function default_email_footer()
    {
        return "<Autotask>\n"
               . "<ID name=\"%ticketuser%\"/>\n"
               . "<PW name=\"%ticketpassword%\"/>\n"
               . "<Customer name=\"%site%\"/>\n"
               . "<SubIssue name=\"%name%\"/>\n"
               . "</Autotask>";
    }


   
    function preserve_notification_state($nid,$act)
    {
        return "notification_id=$nid&notification_act=$act";
    }


   
    function return_notification_url()
    {
        $n_id  = get_integer('notification_id','');
        $n_act = get_string('notification_act','');
        return "nid=$n_id&act=$n_act";
    }
