<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
 1-Oct-03   EWB     Created.
*/

   /*
    |  Returns a list of the databases associated
    |  with the specified server.
    */

    function find_database_names($db)
    {
        $names = array( );
        $res = (($___mysqli_tmp = mysqli_query($db, "SHOW DATABASES")) ? $___mysqli_tmp : false);
        if ($res)
        {
            $num = mysqli_num_rows($res);
            for ($i = 0; $i < $num; $i++)
            {
                $names[] = ((mysqli_data_seek($res, $i) && (($___mysqli_tmp = mysqli_fetch_row($res)) !== NULL)) ? array_shift($___mysqli_tmp) : false);
            }
        }
        return $names;
    }


   /*
    |  Returns a list of the tables within the
    |  specified database.
    */

    function find_table_names($dbname,$db)
    {
        $names = array( );
        $res = mysqli_query($db, "SHOW TABLES FROM `$dbname`");
        if ($res)
        {
            $n = mysqli_num_rows($res);
            for ($i = 0; $i < $n; $i++)
            {   
                $names[] = ((mysqli_data_seek($res, $i) && (($___mysqli_tmp = mysqli_fetch_row($res)) !== NULL)) ? array_shift($___mysqli_tmp) : false);
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
        return $names;
    }


   /*
    |  Returns a list of the fields within the
    |  specified table.  Works even if the table
    |  contains no records.
    */

    function find_field_names($dbname,$table,$db)
    {
        $fields = array( );
        $res = (($___mysqli_tmp = mysqli_query($db, "SHOW COLUMNS FROM $dbname.$table")) ? $___mysqli_tmp : false);
        if ($res)
        {
            $n = (($___mysqli_tmp = mysqli_num_fields($res)) ? $___mysqli_tmp : false);
            for ($i = 0; $i < $n; $i++)
            {
                $name = ((($___mysqli_tmp = mysqli_fetch_field_direct($res, $i)->name) && (!is_null($___mysqli_tmp))) ? $___mysqli_tmp : false);
                $fields[] = $name;
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
        return $fields;
    }

?>
