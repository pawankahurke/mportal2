<?php

/*
Revision history:

Date        Who     What
----        ---     ----
13-Feb-03   EWB     Created.
27-Apr-03   NL      Sitefiltering: get_filtered_sitelist() instead of accesstree()
28-Apr-03   NL      sitefiltertree()
28-Apr-03   NL      Create siteaccesstree() to use find_sites & produce tree of arrays, not lists
29-Apr-03   NL      sitefiltertree() returns sites that SHOULD BE INCLUDED (filter=1);
                    Delete get_filtered_sitelist()
29-Apr-03   NL      In sitefiltertree, make sure ids exist before doing sql query
10-Jun-03   EWB     In sitefiltertree, cause an error for bad input, free mysql result
13-Jun-03   EWB     AccessTree is no longer needed.
30-Oct-03   NL      Moved find_active_sites() from c-report, c-asset.
18-Nov-03   EWB     Impose a consistant order.
 7-Jan-04   EWB     case-insensitive site calculations.
10-Nov-04   EWB     userlist returns only "active" users.
15-Oct-05   BTE     Added hex call in userlist.
09-Nov-05   BJS     Added hex call to userlist 'and' clause.
                    Removed event.RptSiteFilters & notifications.NotSiteFilters.
13-Jun-06   BTE     Part of bug 3468: Slow query locks event logging.

*/

/*
    |  An array of all the "active" user names.
    |
    |  I'm now (11/18/03) alphabetizing this list.  We don't really *need*
    |  it to be alphabetized, but it is helpful for debugging to do
    |  things in a predictable and consistant way, and this list controls
    |  the order that proxy reports are processed.
    */

function userlist($db)
{
    $set = array();
    $sql = "select distinct U.username\n"
        . " from " . $GLOBALS['PREFIX'] . "core.Users as U,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers as S\n"
        . " where U.username = S.username\n"
        . " and S.customer = C.site\n"
        . " order by U.username";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_assoc($res)) {
                $set[] = $row['username'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $set;
}

/*
    |  For each user, return an array of the sites he is
    |  allowed to access.
    */

function siteaccesstree($users, $db)
{
    $siteaccesstree = array();
    if ($users) {
        reset($users);
        foreach ($users as $key => $user) {
            $siteaccesstree[$user] = site_array($user, 0, $db);
        }
    }
    return $siteaccesstree;
}

/*
    |   sitefiltertree
    |
    |   For given objects (reports, events) return an array of the sites for which
    |   data should be included.
    |
    |   parameters:
    |       objecttype = [ eventnotification |  eventreport | assetreport ]
    |       objectids = array of ids for the reports or notifications.
    */

function sitefiltertree($objecttype, $objectids, $db)
{
    $filtertree = array();

    switch ($objecttype) {
        case 'assetreport':
            $auxtable = 'RptSiteFilters';
            $idfield  = 'assetreportid';
            break;
        default:
            $auxtable = 'TheWrongTable';
            $idfield  = 'TheWrongId';
    }

    if ($objectids) {
        $idlist = implode(',', $objectids);

        $sql  = "SELECT site,$idfield FROM $auxtable\n";
        $sql .= " WHERE $idfield IN ($idlist)\n";
        $sql .= " AND filter = 1\n";
        $sql .= " ORDER BY site";
        $res  = redcommand($sql, $db);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $id = $row[$idfield];
                $filtertree[$id][] = $row['site'];
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    }

    return $filtertree;
}

/*
    |   find_active_sites
    |
    |   Returns an array of those sites which are active (where active
    |   means that the site has an entry in the core.Census table)
    |
    |   parameters:
    |       $db:        Handle to the database connection.
    */

function find_active_sites($db)
{
    $active = array();
    $sql = "select distinct site from " . $GLOBALS['PREFIX'] . "core.Census order by site";
    $res = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $active[] = $row['site'];
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $active;
}

/*
    |  This returns all information about all the users.
    |  Note that the user records are small and there
    |  aren't very many of them.
    */

function usertree($db)
{
    $tree = array();
    $sql  = "select * from " . $GLOBALS['PREFIX'] . "core.Users order by username";
    $res  = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $name = $row['username'];
            $tree[$name] = $row;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $tree;
}


function email_list($to, $defmail, $def)
{
    if ($defmail) {
        if ($def) {
            if ($to)
                $to = "$to,$def";
            else
                $to = $def;
        }
    }
    return $to;
}


/*
    |  This is a case insensitive version of array_intersect.
    |  The problem is that site names are not guaranteed to
    |  be consistant between the census and the customer 
    |  records, especially if the site is manually created.
    |
    |  This was a problem for the microdata server.
    */

function site_intersect($a, $b)
{
    $tmp = array();
    $res = array();
    reset($a);
    foreach ($a as $key => $data) {
        $name = strtolower($data);
        $tmp[$name] = false;
    }
    reset($b);
    foreach ($b as $key => $data) {
        $name = strtolower($data);
        $tmp[$name] = true;
    }
    reset($a);
    foreach ($a as $key => $data) {
        $name = strtolower($data);
        if ($tmp[$name])
            $res[] = $data;
    }
    return $res;
}
