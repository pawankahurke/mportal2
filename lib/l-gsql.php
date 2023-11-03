<?php

function find_many($sql, $db = null)
{
    logs::tag("SQL", __FUNCTION__, ["sql" => $sql], 1);
    return NanoDB::find_many($sql, $db);
}


function find_one($sql, $db = null)
{
    logs::tag("SQL", __FUNCTION__, ["sql" => $sql], 1);
    return NanoDB::find_one($sql, $db);
}


function find_several($sql, $db = null)
{
    logs::tag("SQL", __FUNCTION__, ["sql" => $sql], 1);
    return NanoDB::find_several($sql, $db);
}
