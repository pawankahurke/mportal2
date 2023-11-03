<?php




   
    function get_key_index($database,$table,$name,$db)
    {
        $sql = "SHOW INDEX FROM $database.$table";
        $res = redcommand($sql,$db); 
        if ($res)
        {
            if (mysqli_num_rows($res))
            {    
                $idx = 0;
                $prev_key_name = '';
                while ($row = mysqli_fetch_array($res))
                {
                    $key_name   = $row['Key_name'];
                    if($key_name != $prev_key_name)
                    {
                        $idx++;
                    }
                    if ($key_name == $name)
                    {
                        return $idx;
                    }
                    else
                    {
                        $prev_key_name = $key_name;
                    }
                }
            }
        }
        return 0;
    }


?>

