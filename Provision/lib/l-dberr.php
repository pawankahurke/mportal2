<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
29-Aug-03   NL      Creation (Move get_key_index() function from install/header.php).
*/


   /*
    |  get_key_index()
    |  
    |  Gets all the keys from the specified table in the specified database
    |  and returns the key index for the key specified by $name.
    |  This can be used to preg_match against an mysql_error() such as 
    |               Duplicate entry '1-nanoheal' for key 2
    |  to determine which unique key has been violated.
    |
    */
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

