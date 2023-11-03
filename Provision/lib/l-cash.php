<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
19-Sep-02   EWB     Created.
13-Nov-02   EWB     Logs mysql errors
20-Jan-03   AAM     Fixed bug where empty customer list caused mysql error.
*/

/* ----------------------------------------------------------\
|                                                            |
|  EVENT FUNCTIONS                                           |
|                                                            |
\-----------------------------------------------------------*/
        
#
# GRM
# 
# Events table simple caching. To save time and effort, we cache
# common parametric search terms so that you can populate the search
# form much faster.
#
# event_cache_set sets a $column of the events table with a certain
# $value for a certain $customer, you can also set a $description for the
# column value. If the value, name and customer is already present, then 
# the function returns without error, if it is not present, it is 
# added. If you want the column to be inserted as an integer (for sorting purposes),
# specify $integer = 1. The question is, how does this get purged?
#
#CREATE TABLE EventColumnCache (
#  columnname varchar(50) DEFAULT '' NOT NULL,
#  columnvalue varchar(128) DEFAULT '' NOT NULL,
#  columnintvalue int DEFAULT 0 NOT NULL,
#  columndesc varchar(255) DEFAULT '' NOT NULL,
#  customer varchar(50) DEFAULT '' NOT NULL,
#  PRIMARY KEY (columnname,columnvalue,customer)
#);


/*
 |  Note event_cache_set is only used by the 
 |  logging code, and was moved there 19-Sep-2002.
 */


/*
function event_cache_set ($value, $description, $column, $customer, $integer, $db) 
{
    if ($integer) 
    {
        $field = "columnintvalue";
    } 
    else 
    {
        $field = "columnvalue";
        $value = "'$value'";
    }
    $sql  = "INSERT INTO EventColumnCache";
    $sql .= " (columnname, $field, customer, columndesc)";
    $sql .= " VALUES ('$column', $value, '$customer', '$description')";
    $result = command($sql, $db);
    if (!$result) 
    {
        # don't show this, errors are OK! echo "event_cache_set error: " . mysql_error();
    }
}
*/

#
# event_cache_get returns a mysql_result of values for the given $column
# and $customerlist. If there is an error, processing is halted. If you specify
# $integer = 1, you will retrieve and sort the column as an integer, if possible.
#

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

#
# event_cache_purge deletes the cache for a certain $column and $customer, returns 0 on
# success, and a mysql result error on failure
#

function event_cache_purge ($column, $customer, $db) 
{
    $sql  = "DELETE FROM EventColumnCache";
    $sql .= " WHERE columnname = '$column'";
    $sql .= " AND customer = '$customer'";
    return command($sql, $db);
}

#
# event_cache_populate clears a certain column of cache for all customers and repopulates
# it based on the current Events table. This is non-blocking, so it may miss some
# data. You can specify a sql fragment as $description_sql for the description. For
# example: 
#
# $description_sql = "CONCAT(scrip, ' - ', description)"
#
# Usually, just specify the column name for the description_sql. If this column
# should be inserted as an integer value, specify $integer = 1 (for sorting)
#

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
 



?>

