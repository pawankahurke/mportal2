<?php



function find_dirty($name, $db)
{
    $opt = safe_addslashes($name);
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Options\n"
        . " where name = '$opt'";
    return find_one($sql, $db);
}

function create_dirty($name, $db)
{
    $now = time();
    $opt = safe_addslashes($name);
    $set = constDirtySet;
    $sql = "insert into\n"
        . " " . $GLOBALS['PREFIX'] . "core.Options set\n"
        . " name = '$opt',\n"
        . " value = '$set',\n"
        . " editable = 0,\n"
        . " modified = $now";
    $res = redcommand($sql, $db);
    $num = affected($res, $db);
    // $sql = "insert into\n"
    //      . " ".$GLOBALS['PREFIX']."core.OptionsPerm set\n"
    //      . " name = '$opt',\n"
    //      . " value = '$set',\n"
    //      . " editable = 0,\n"
    //      . " modified = $now";
    // redcommand($sql,$db);
    return $num;
}


function touch_option($name, $db)
{
    $now = time();
    $opt = safe_addslashes($name);
    $sql = "update " . $GLOBALS['PREFIX'] . "core.Options\n"
        . " set modified = $now\n"
        . " where name = '$opt'";
    $res = redcommand($sql, $db);
    $num = affected($res, $db);
    $sql = "update " . $GLOBALS['PREFIX'] . "core.OptionsPerm\n"
        . " set modified = $now\n"
        . " where name = '$opt'";
    redcommand($sql, $db);
    return $num;
}


function store_option($name, $val, $db)
{
    $num = update_opt($name, $val, $db);
    if ($num) {
        touch_option($name, $db);
    }
    return $num;
}


function clear_dirty($name, $db)
{
    return store_option($name, constDirtyClr, $db);
}


function set_dirty($name, $db)
{
    return store_option($name, constDirtySet, $db);
}
