<?php





define('constDefUserUniq',  'hfn_default_item');


function user_data($name, $db, $userfield = 'username')
{
    $usr = array();
    $sql = "select * from Users where $userfield='$name'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $usr = mysqli_fetch_assoc($res);
        }
        mysqli_free_result($res);
    }
    return $usr;
}


function user_info($db, $username, $column, $def)
{
    $val = $def;
    $usr = user_data($username, $db);
    if ($usr) {
        if (isset($usr[$column]))
            $val = $usr[$column];
    }
    return $val;
}



function load_search_global($db)
{
    $sql = "select * from SavedSearches where global = 1";
    return find_several($sql, $db);
}



function find_global_owner($db)
{
    $sql = "select * from " . $GLOBALS['PREFIX'] . "event.SavedSearches where global = 1";
    $search = find_several($sql, $db);
    $owners = array();
    $user   = '';
    $max    = 0;
    reset($search);
    foreach ($search as $key => $row) {
        $name = $row['username'];
        if (isset($owners[$name]))
            $owners[$name]++;
        else
            $owners[$name] = 1;
    }
    reset($owners);
    foreach ($owners as $name => $count) {
        if ($count > $max) {
            $user = $name;
            $max  = $count;
        }
    }
    return $user;
}

function USER_GenerateManagedUniq($name, $user, $db)
{
    $searchuniq = '';
    $owner = find_global_owner($db);
    if ((!$owner) || ($user == $owner)) {
        $searchuniq = md5(constDefUserUniq . ',' . $name);
    } else {
        $searchuniq = md5($user . ',' . $name);
    }

    return $searchuniq;
}
