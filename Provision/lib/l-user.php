<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
19-Sep-02   EWB     Created.
28-May-03   NL      user_data(): make the userfield configurable (e.g. for use w/ INSTALL)
03-May-07   BTE     Added some functions from acct/uglobal.php.
31-Jul-07   BTE     Universal unique function.
14-Sep-07   BTE     Fixed a bug that broke uglobal from last checkin.

*/

/* ----------------------------------------------------------\
|                                                            |
|  USER / ACCESS / PERMISSION UTILITIES                      |
|                                                            |
\-----------------------------------------------------------*/

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
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
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


/* load_search_global

        Returns all global SavedSearches from the active database $db.  Do not
        specify event.SavedSearches here, some callers (uglobal.php) rely on
        this procedure returning saved searches from databases other than
        event.
    */
function load_search_global($db)
{
    $sql = "select * from SavedSearches where global = 1";
    return find_several($sql, $db);
}

/*
    |  Figure out who is the global owner.
    |  This information probably ought to be stored
    |  somewhere, but currently it is not.
    |
    |  We survey all the existing global saved searches and
    |  find out who owns the most.  This account is declared
    |  the global owner, and all the new items will
    |  become owned by him.
    */

function find_global_owner($db)
{
    $sql = "select * from event.SavedSearches where global = 1";
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
