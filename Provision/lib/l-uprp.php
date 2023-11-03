<?php

/*
Revision history:

Date        Who     What
----        ---     ----
19-Feb-03   EWB     Created.
20-Feb-03   EWB     Ported to 3.1
20-Feb-03   EWB     Added created/modified
17-Jul-03   EWB     Added file/links
21-Aug-03   EWB     Added filtersites.
22-Oct-03   EWB     Added next_run.
14-Nov-03   NL      Implement asset links option.
 9-Apr-04   EWB     Propogate this_run, retries
27-Oct-04   BJS     Added include_user, include_text, subject_text.
30-Nov-04   EWB     Return report id after inserting a new one.
13-Dec-04   BJS     Added skip_owner field.
16-Feb-05   BJS     Added aggregate field.
28-Mar-05   BJS     inactive(), removed this_run/next_run
                    from update_report(), so a user changing
                    a report will not disrupt a running report.
 6-Jun-05   BJS     added omit field.
19-Jul-05   BJS     added detaillinks field.
02-Nov-05   BJS     added preserve_report_state(), group_include/exclude/suspend.
07-Nov-05   BJS     removed group_suspend & suspend.
10-Nov-05   BJS     removed filtersites.
02-Dec-05   BJS     removed preserve_report_state().
*/


  /*
   |  requires:
   |     lib-db.php3
   |     lib-sql.php3
   |     lib-rcmd.php3
   */


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


   /*
    |  create or update a report.
    |  does not modify this/next_run
    |  to avoid messing up the que.
    */

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
            $num = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        }
        return $num;
    }

   /*
    | Return the user to report they were previously
    | configuring.
   */
    function return_report_url()
    {
        $r_id  = get_integer('report_id','');
        $r_act = get_string('report_act','');
        return "rid=$r_id&act=$r_act";
    }
