<?php



   

    function did_error($name)
    {
        $msg = "Error -- Field ($name) does not exist.";
        $msg = "<font color='red'>$msg</font><br>\n";
        return $msg;
    }


    function find_did($name,$db)
    {
        $qnm  = safe_addslashes($name);
        $did  = 0;
        $sql  = "select * from DataName where";
        $sql .= " name = '$qnm'";
        $res  = command($sql,$db);
        if ($res)
        {
            if (mysqli_num_rows($res) == 1)
            {
                $row = mysqli_fetch_assoc($res);
                $did = $row['dataid'];
            }            
            mysqli_free_result($res);
        }
        if ($did == 0)
        {
            echo did_error($name);
                    }
        return $did;
    }
