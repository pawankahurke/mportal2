<?php



   

    function find_database_names($db)
    {
        $names = array( );
        $res = mysqli_list_dbs($db);
        if ($res)
        {
            $num = mysqli_num_rows($res);
            for ($i = 0; $i < $num; $i++)
            {
                $names[] = mysqli_db_name($res,$i);
            }
        }
        return $names;
    }


   

    function find_table_names($dbname,$db)
    {
        $names = array( );
        $res = mysqli_list_tables($dbname,$db);
        if ($res)
        {
            $n = mysqli_num_rows($res);
            for ($i = 0; $i < $n; $i++)
            {   
                $names[] = mysqli_tablename($res,$i);
            }
            mysqli_free_result($res);
        }
        return $names;
    }


   

    function find_field_names($dbname,$table,$db)
    {
        $fields = array( );
        $res = mysqli_list_fields($dbname,$table,$db);
        if ($res)
        {
            $n = mysqli_num_fields($res);
            for ($i = 0; $i < $n; $i++)
            {
                $name = mysqli_field_name($res,$i);
                $fields[] = $name;
            }
            mysqli_free_result($res);
        }
        return $fields;
    }

?>
