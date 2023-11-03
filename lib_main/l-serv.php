<?php






function serveroptions($db)
{
    $opt = array();
    $sql = 'select * from Options';
    $res = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $name = $row['name'];
            $opt[$name] = $row['value'];
        }
        mysqli_free_result($res);
    }
    return $opt;
}




function find_opt($name, $db)
{
    $row = array();
    $qn  = safe_addslashes($name);
    $sql = "select * from " . $GLOBALS['PREFIX'] . "core.Options where name = '$qn'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
        }
        mysqli_free_result($res);
    }
    return $row;
}




function opt_insert($name, $value, $ed, $db)
{
    $qn  = safe_addslashes($name);
    $qv  = safe_addslashes($value);
    $ed  = ($ed) ? 1 : 0;
    $now = time();
    $sql = "insert into " . $GLOBALS['PREFIX'] . "core.Options set\n"
        . " name = '$qn',\n"
        . " value = '$qv',\n"
        . " editable = $ed,\n"
        . " modified = $now";
    $res = redcommand($sql, $db);
    $num = affected($res, $db);
    // $sql = "insert into ".$GLOBALS['PREFIX']."core.OptionsPerm set\n"
    //      . " name = '$qn',\n"
    //      . " value = '$qv',\n"
    //      . " editable = $ed,\n"
    //      . " modified = $now";
    // redcommand($sql,$db);

    return $num;
}


function opt_update($name, $value, $ed, $db)
{
    $qn  = safe_addslashes($name);
    $qv  = safe_addslashes($value);
    $ed  = ($ed) ? 1 : 0;
    $now = time();
    $sql = "update " . $GLOBALS['PREFIX'] . "core.Options set\n"
        . " value = '$qv',\n"
        . " editable = $ed,\n"
        . " modified = $now\n"
        . " where name = '$qn'";
    $res = command($sql, $db);
    $num = affected($res, $db);
    // $sql = "update ".$GLOBALS['PREFIX']."core.OptionsPerm set\n"
    //      . " value = '$qv',\n"
    //      . " editable = $ed,\n"
    //      . " modified = $now\n"
    //      . " where name = '$qn'";
    // command($sql,$db);
    return $num;
}




function server_opt($name, $db)
{
    $row = find_opt($name, $db);
    return ($row) ? $row['value'] : '';
}




function server_def($name, $def, $db)
{
    $out = $def;
    $row = find_opt($name, $db);
    if ($row) {

        $tmp = $row['value'];
        if (($tmp) == ('')) {
            $out = $def;
        } else {
            $out = $tmp;
        }
    }
    return $out;
}




function server_int($name, $def, $db)
{
    $row = find_opt($name, $db);
    $val = ($row) ? $row['value'] : $def;
    return intval($val);
}



function update_opt($name, $valu, $db)
{
    $qn  = safe_addslashes($name);
    $qv  = safe_addslashes($valu);
    $sql = "update " . $GLOBALS['PREFIX'] . "core.Options\n"
        . " set value = '$qv'\n"
        . " where name = '$qn'";
    $res = command($sql, $db);
    $num = affected($res, $db);
    $sql = "update " . $GLOBALS['PREFIX'] . "core.OptionsPerm\n"
        . " set value = '$qv'\n"
        . " where name = '$qn'";
    command($sql, $db);
    return $num;
}




function server_set($name, $value, $db)
{
    $old = '';
    $row = find_opt($name, $db);
    if ($row) {
        $old = $row['value'];
        opt_update($name, $value, 1, $db);
    } else {
        opt_insert($name, $value, 1, $db);
    }
    return $old;
}




function server_name($db)
{
    $host = server_opt('server_name', $db);
    if ($host == '') {
        $host = server_var('SERVER_NAME');
    }
    if (($host == 'localhost') || ($host == '')) {
        $fqdn = `/bin/hostname -f`;
        $host = str_replace("\n", '', $fqdn);
    }
    return $host;
}



function find_site_email($site, $db)
{
    $qs = safe_addslashes($site);
    $sql = "select notify_sender from " . $GLOBALS['PREFIX'] . "core.Customers\n"
        . " where username = '' and\n"
        . " customer = '$qs'";
    $res = find_one($sql, $db);
    if (isset($res['notify_sender'])) {
        return $res['notify_sender'];
    }
    return '';
}



function set_site_email($qs, $site_email, $db)
{
    $good       = false;
    $site_email = safe_addslashes($site_email);

    $sql = "update Customers set\n"
        . " notify_sender  = '$site_email'\n"
        . " where username = ''\n"
        . " and customer   = '$qs'";
    $res = redcommand($sql, $db);
    if (affected($res, $db)) {
        $good = true;
    }
    return $good;
}




function build_custom_list($custom)
{
    $custom_list   = array();
    $custom_string = get_string($custom, '');
    $custom_list   = explode(',', $custom_string);
    $custom_list   = array_flip($custom_list);
    return $custom_list;
}



function customize_name($name, &$custom_list, $text_type_start, $text_type_end)
{
    if (isset($custom_list[$name])) {
        $anch = "<a name=\"#$name\"></a>";
        return "$anch $text_type_start $name $text_type_end";
    } else {
        return $name;
    }
}




function find_user_option($user, $db)
{
    $row = array();
    $name = safe_addslashes($user);
    $sql = "select * from " . $GLOBALS['PREFIX'] . "core.Users where username = '$name'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
        }
        mysqli_free_result($res);
    }
    return $row;
}



function get_user_options($option, $user, $def, $db)
{
    $out = $def;
    $user_option = safe_addslashes($option);
    $row = find_user_option($user, $db);
    if ($row) {

        $tmp = $row[$user_option];
        if (($tmp) == ('')) {

            $out = $def;
        } else {

            $out = $tmp;
        }
    }
    return $out;
}
