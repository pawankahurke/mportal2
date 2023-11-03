<?php

/*
Revision history:

Date        Who     What
----        ---     ----
19-Feb-03   EWB     Created.
20-Feb-03   EWB     Ported to 3.1
21-Feb-03   EWB     created/modified
17-Jul-03   EWB     links
25-Mar-04   EWB     next_run
 9-Apr-04   EWB     this_run
 8-Oct-04   EWB     ntype, seconds, retries
14-Oct-04   EWB     insert returns new id.
21-Oct-04   EWB     filtersites
14-Dec-04   BJS     added skip_owner
27-Jul-05   EWB     rem: machines/exclude
27-Jul-05   EWB     add: ginclude/gexclude/gsuspend
26-Sep-05   BJS     added email_footer, email_per_site, email_footer_txt.
                    default_email_footer().
27-Sep-05   BJS     added Notifications.email_sender.
30-Sep-05   BJS     added %ticketuser% & %ticketpassword%.
03-Oct-05   BJS     addedslashes(email_footer_txt).
13-Oct-05   BJS     added preserve_notification_state().
14-Oct-05   BJS     added return_notification_url().
21-Oct-05   BJS     added group_included/excluded/suspend.
                    removed ginclude/exclude/suspend.
09-Nov-05   BJS     removed filtersites.
27-Dec-07   BTE     Added support for Autotask.

*/


  /*
   |  requires:
   |     l-db.php
   |     l-sql.php
   |     l-rcmd.php
   */


   /*
    |  create or update a notification
    */

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
            /* Autotask enabled notifications require email per site. */
            $email_per_site = 1;
        }

        $qn  = safe_addslashes($row['name']);
        $qc  = safe_addslashes($row['config']);
        $qu  = safe_addslashes($row['username']);
        $ql  = safe_addslashes($row['emaillist']);
        $qe  = safe_addslashes($row['email_footer_txt']);

        // insert into Notifications set
        // update Notifications set

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
            $num = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        }
        return $num;
    }


    /*
    |  Returns the default value of the Autodesk footer.
    */
    function default_email_footer()
    {
        return "<Autotask>\n"
               . "<ID name=\"%ticketuser%\"/>\n"
               . "<PW name=\"%ticketpassword%\"/>\n"
               . "<Customer name=\"%site%\"/>\n"
               . "<SubIssue name=\"%name%\"/>\n"
               . "</Autotask>";
    }


   /*  
    |  $nid = notification id
    |  $act = notification action
    |  This string is appended to the url to 
    |  keep track of the notification the user
    |  was working with when they clicked the
    |  'configure groups' link.
   */
    function preserve_notification_state($nid,$act)
    {
        return "notification_id=$nid&notification_act=$act";
    }


   /*
    |  When the user has finished 'configuring groups'
    |  through the wizard, we will return them to the
    |  notification they were previously editing.
   */
    function return_notification_url()
    {
        $n_id  = get_integer('notification_id','');
        $n_act = get_string('notification_act','');
        return "nid=$n_id&act=$n_act";
    }
