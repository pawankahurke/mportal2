<?php

/*
  Revision history:

  Date        Who     What
  ----        ---     ----
  03-Oct-19   SVG     Created.

 */

define('constDefUserUniq', 'hfn_default_item');

/* function user_data($name, $db, $userfield = 'username') {
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

  function user_info($db, $username, $column, $def) {
  $val = $def;
  $usr = user_data($username, $db);
  if ($usr) {
  if (isset($usr[$column]))
  $val = $usr[$column];
}
  return $val;
  }

  function load_search_global($db) {
  $sql = "select * from SavedSearches where global = 1";
  return find_several($sql, $db);
  }


  function find_global_owner($db) {
  $sql = "select * from event.SavedSearches where global = 1";
  $search = find_several($sql, $db);
  $owners = array();
  $user = '';
  $max = 0;
  reset($search);
  foreach ($search as $key => $row) {
  $name = $row['username'];
  if (isset($owners[$name]))
  $owners[$name] ++;
  else
  $owners[$name] = 1;
  }
  reset($owners);
  foreach ($owners as $name => $count) {
  if ($count > $max) {
  $user = $name;
  $max = $count;
  }
  }
  return $user;
  }

  function USER_GenerateManagedUniq($name, $user, $db) {
  $searchuniq = '';
  $owner = find_global_owner($db);
  if ((!$owner) || ($user == $owner)) {
  $searchuniq = md5(constDefUserUniq . ',' . $name);
  } else {
  $searchuniq = md5($user . ',' . $name);
  }

  return $searchuniq;
  } */

function get_UserName($id, $db)
{
    $usr = '';
    $sql = "select installuser from Users where installuserid ='$id'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $usr_ser = mysqli_fetch_assoc($res);
            $usr = $usr_ser['installuser'];
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $usr;
}

function get_SkuNames($skiList, $db)
{
    $skuText = [];
    $sql = "SELECT description FROM skuOfferings s where s.sid in ($skiList)";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_assoc($res)) {
                $skuText[] = $row['description'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    //    print_r($skuText);
    //    die();
    return implode(",", $skuText);
}

// This is a short-term solution.  Future: remove
function cust_array($auth, $db)
{
    return site_array($auth, 0, $db);
}

// This is a short-term solution.  Future: remove
function filtered_cust_array($auth, $db)
{
    return site_array($auth, 1, $db);
}

function get_configuredSku_custpage($installuserid, $db)
{

    $skulist = [];
    $offerings = [];
    $sql = "SELECT skuids FROM Users WHERE installuserid = $installuserid";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $skulist[] = $row['skuids'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }


    $offerids = implode(',', $skulist);
    $offerSql = "SELECT * FROM skuOfferings where sid IN ($offerids)";
    $offerRes = command($offerSql, $db);
    if ($offerRes) {
        if (mysqli_num_rows($offerRes)) {
            while ($row = mysqli_fetch_array($offerRes)) {
                $offerings[$row['sid']] = $row['name'];
            }
        }
        ((mysqli_free_result($offerRes) || (is_object($offerRes) && (get_class($offerRes) == "mysqli_result"))) ? true : false);
    }

    return $offerings;
}

function get_configuredSku_custpage_all($db)
{

    $offerSql = "SELECT * FROM skuOfferings";
    $offerRes = command($offerSql, $db);
    if ($offerRes) {
        if (mysqli_num_rows($offerRes)) {
            while ($row = mysqli_fetch_array($offerRes)) {
                $offerings[$row['sid']] = $row['name'];
            }
        }
        ((mysqli_free_result($offerRes) || (is_object($offerRes) && (get_class($offerRes) == "mysqli_result"))) ? true : false);
    }

    return $offerings;
}

function get_cust_data($id, $db)
{
    $sql = "SELECT * FROM Customers WHERE cid  = '$id'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) != 1) {
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
            return array();
        } else {
            $row = mysqli_fetch_array($res);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
            return $row;
        }
    }
}
