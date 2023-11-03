<?php




   

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
                mysqli_free_result($res);
            }
        }
        return $list;
    }


   

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
                mysqli_free_result($res);
            }
        }
        return $list;
    }

?>

