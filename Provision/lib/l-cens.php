<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 8-Feb-03   EWB     Created.
24-Feb-03   EWB     Handle the case where access list is empty.
 3-Mar-03   EWB     Oops ... forgot to alphabetize the host list.
28-May-03   EWB     Make the list unique after all.
25-Nov-03   NL      Create census_site_machine().
26-Nov-03   EWB     Learn to spell "guaranteed".
*/


   /*
    |  Returns the list of machines that belong to
    |  the specified list of sites ... this can be
    |  empty of course.  The host names are
    |  guaranteed to be unique.
    */

    function census_machine($access,$db)
    {
        $list = array( );
        if ($access)
        {
            $sql  = "select * from ".$GLOBALS['PREFIX']."core.Census\n";
            $sql .= " where site in ($access)\n";
            $sql .= " order by host";
            $res  = command($sql,$db);
            if ($res)
            {
                $prev = '';
                while ($row = mysqli_fetch_assoc($res))
                {
                    $mid  = $row['id'];
                    $name = $row['host'];
                    if ($name != $prev)
                    {
                        $list[$mid] = $name;
                        $prev = $name;
                    }
                }
                ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
            }
        }
        return $list;
    }


   /*
    |  Returns a 2D array of sites and machines that belong to
    |  the specified list of sites ... this can be
    |  empty of course.
    */

    function census_site_machine($access,$db)
    {
        $list = array( );
        if ($access)
        {
            $sql  = "select * from ".$GLOBALS['PREFIX']."core.Census\n";
            $sql .= " where site in ($access)\n";
            $sql .= " order by site,host";
            $res  = command($sql,$db);
            if ($res)
            {
                while ($row = mysqli_fetch_assoc($res))
                {
                    $site = $row['site'];
                    $host = $row['host'];
                    $list[$site][] = $host;
                }
                ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
            }
        }
        return $list;
    }

?>

