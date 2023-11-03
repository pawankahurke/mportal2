<?php




        








function event_cache_get ($column, $customerlist, $integer, $db) 
{
    $res = false;
    if ($customerlist)
    {
        if ($integer) 
            $field = "columnintvalue";
        else 
            $field = "columnvalue";
        $sql  = "SELECT DISTINCT $field AS $column, columndesc";
        $sql .= " AS the_description FROM EventColumnCache"; 
        $sql .= " WHERE columnname = '$column' AND"; 
        $sql .= " customer IN ($customerlist)";
        $sql .= " ORDER BY $field";
        $res  = command($sql, $db);
    }
    return $res;
}


function event_cache_purge ($column, $customer, $db) 
{
    $sql  = "DELETE FROM EventColumnCache";
    $sql .= " WHERE columnname = '$column'";
    $sql .= " AND customer = '$customer'";
    return command($sql, $db);
}


function event_cache_populate ($column, $description_sql, $integer, $db) 
{
    $sql = "DELETE FROM EventColumnCache WHERE columnname = '$column'";
    $result = command($sql, $db);

    $sql  = "SELECT DISTINCT $column, customer, $description_sql";
    $sql .= " AS the_description FROM Events";
    $result = command($sql, $db);

    if (!$result) 
    {
        die ("Error executing select query in event_cache_popluate");
    }
        
    while ($row = mysqli_fetch_assoc($result)) 
    {
        $cust = $row['customer'];
        $desc = $row['the_description'];
        $data = $row[$column];

        if ($integer) 
            $field = "columnintvalue";
        else 
        {
            $field = "columnvalue";
            $data  = "'$data'";
        }
        $sql  = "INSERT INTO EventColumnCache";
        $sql .= " (columnname, $field, customer, columndesc)";
        $sql .= " VALUES ('$column', $data, '$cust', '$desc')";
        $tmp  = command($sql, $db);

        if (!$tmp) 
        {
            die ("Error executing insert query in event_cache_popluate");
        }
    }
}
