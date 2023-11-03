<?php

/*
Revision history:

Date        Who     What
----        ---     ----
10-Oct-02   EWB     Created
 2-Dec-04   EWB     Just the associated part
27-Jun-07   BTE     Added find_several.

*/


function find_many($sql, $db)
{
    logs::tag("SQL", __FUNCTION__, ["sql" => $sql], 1);
    return NanoDB::find_many($sql, $db);
}


function find_one($sql, $db)
{
    logs::tag("SQL", __FUNCTION__, ["sql" => $sql], 1);
    return NanoDB::find_one($sql, $db);
}


function find_several($sql, $db)
{
    logs::tag("SQL", __FUNCTION__, ["sql" => $sql], 1);
    return NanoDB::find_several($sql, $db);
}
