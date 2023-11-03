<?php




include_once '../include/NH-Config_API.php';
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

function ADMN_GetNotifyGridData($key, $db, $search, $authuser, $temp2)
{

    $res = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if ($search != "") {
            $var = "%$search%";
            $sql = $db->prepare("SELECT  N.id as id,
                    N.name as notification_name,
                    S.name as event_filter,
                    S.id as search_id,
                    N.seconds as frequency,
                    N.last_run as last_run,
                    N.modified as modified,
                    N.console as console,
                    N.email as email,
                    N.emaillist as email_recipients,
                    N.priority as priority,
                    N.enabled as state,
                    N.auto_soln as auto_soln,
                    N.profile_name as profile_name,
                    N.username as username
            FROM     " . $GLOBALS['PREFIX'] . "event.Notifications as N,
                    " . $GLOBALS['PREFIX'] . "event.SavedSearches as S
            LEFT JOIN  " . $GLOBALS['PREFIX'] . "event.Notifications as X
            ON S.name = X.name
            AND X.global = 0
            AND X.username = ?
            WHERE N.search_id = S.id
            AND ((N.username = ?) or (N.global = 1 and X.id is NULL)) and notification_name like ?");
            $sql->execute([$authuser, $authuser, $var]);
        } else {
            $sql = $db->prepare("SELECT  N.id as id,
                    N.name as notification_name,
                    S.name as event_filter,
                    S.id as search_id,
                    N.seconds as frequency,
                    N.last_run as last_run,
                    N.modified as modified,
                    N.console as console,
                    N.email as email,
                    N.emaillist as email_recipients,
                    N.priority as priority,
                    N.enabled as state,
                    N.auto_soln as auto_soln,
                    N.profile_name as profile_name,
                    N.username as username
            FROM     " . $GLOBALS['PREFIX'] . "event.Notifications as N,
                    " . $GLOBALS['PREFIX'] . "event.SavedSearches as S
            LEFT JOIN  " . $GLOBALS['PREFIX'] . "event.Notifications as X
            ON S.name = X.name
            AND X.global = 0
            AND X.username = ?
            WHERE N.search_id = S.id
            AND ((N.username = ?) or (N.global = 1 and X.id is NULL)) ");
            $sql->execute([$authuser, $authuser]);
        }
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($res) > 0) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}



function ADMN_GetSavedSearchList($type, $id)
{
    $pdo = pdo_connect();
    $authuser = $_SESSION['user']['username'];
    $recordListevent = '';
    $eventFilterList = DASH_GetEventFilerList("1", $pdo, $authuser);
    if ($type == "add") {
        foreach ($eventFilterList as $key => $val) {
            $recordListevent .= "<option value='" . $val['id'] . "' >" . $val['name'] . "</option>";
        }
    } else if ($type == "edit" || $type == "copy") {
        $stmt = $pdo->prepare("SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Notifications WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        foreach ($eventFilterList as $key => $val) {
            $recordListevent .= "<option value='" . $val['id'] . "'";
            if ($val['id'] == $res['search_id']) {
                $recordListevent .= " selected";
            } else {
                $recordListevent .= "";
            }
            $recordListevent .= ">" . $val['name'] . "</option>";
        }
    }

    return $recordListevent;
}



function ADMN_GetIncExcGroups($userName, $channelId, $grpCategory, $id, $type)
{
    $pdo = pdo_connect();
    if ($type == "add") {
        $result = ADMN_getSiteGroupsForUser($pdo, $userName, $channelId);
        $res = ADMN_getAllGroupid($pdo);
        $machOpt = "<option value='" . $res . "' >All</option>";
        foreach ($result as $key => $val) {
            $machOpt .= "<option value='" . $val['mgroupid'] . "' >" . UTIL_GetTrimmedGroupName($val['name']) . "</option>";
        }
        return $machOpt;
    } else {
        $result = ADMN_getSiteGroupsForUser($pdo, $userName, $channelId);
        $res = ADMN_getAllGroupid($pdo);

        $stmt = $pdo->prepare("SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Notifications WHERE id = ?");
        $stmt->execute([$id]);
        $resi = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resi['group_include'] == $res) {
            $incOpt = "<option value='" . $res . "' selected>All</option>";
        } else {
            $incOpt = "<option value='" . $res . "' >All</option>";
        }
        if ($resi['group_exclude'] == $res) {
            $excOpt = "<option value='" . $res . "' selected>All</option>";
        } else {
            $excOpt = "<option value='" . $res . "' >All</option>";
        }

        foreach ($result as $key => $val) {
            $incOpt .= "<option value='" . $val['mgroupid'] . "'";
            if (strpos($resi['group_include'], $val['mgroupid']) !== false) {
                $incOpt .= " selected";
            } else {
                $incOpt .= "";
            }
            $incOpt .= ">" . UTIL_GetTrimmedGroupName($val['name']) . "</option>";

            $excOpt .= "<option value='" . $val['mgroupid'] . "'";
            if (strpos($resi['group_exclude'], $val['mgroupid']) !== false) {
                $excOpt .= " selected";
            } else {
                $excOpt .= "";
            }
            $excOpt .= ">" . UTIL_GetTrimmedGroupName($val['name']) . "</option>";
        }
        return $incOpt . "@@@@" . $excOpt;
    }
}

function ADMN_NotifyL3Profiles($key, $db, $id)
{
    $res = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $profile_sql = $db->prepare("select type,dart,profile,sequence,OS,id from " . $GLOBALS['PREFIX'] . "event.profile where (dart IS NOT NULL AND dart != '' AND dart != 'null' AND type='L3') group by profile");
        $profile_sql->execute();
        $profile_res = $profile_sql->fetchAll(PDO::FETCH_ASSOC);

        $sqli = $db->prepare("SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.NotificationProfile WHERE nid = ? order by id desc limit 1");
        $sqli->execute([$id]);
        $resi = $sqli->fetch(PDO::FETCH_ASSOC);

        $notifySql = $db->prepare("SELECT name FROM  " . $GLOBALS['PREFIX'] . "event.Notifications WHERE id =? order by id desc limit 1");
        $notifySql->execute([$id]);
        $notifyRes = $notifySql->fetch(PDO::FETCH_ASSOC);

        $name = $notifyRes['name'];
        $checkBox .= '<div class="checkbox"><label>Enable Auto Solution <input type="checkbox" class="check user_check" id="autoenable" name="autoenable" value="' . $resi['auto_soln'] . '" onclick="changeCheckVal();"';
        if ($resi['auto_soln'] == "1") {
            $checkBox .= ' checked';
        } else {
            $checkBox .= '';
        }
        $checkBox .= '';
        $checkBox .= '><span class="checkbox-material"><span class="check"></span></span></label></div>';
        $profiles = "<option value='####'> <-- Select a solution --> </option>";
        foreach ($profile_res as $key => $val) {
            $profiles .= "<option value='" . $val['profile'] . "' id='" . $val['id'] . "' ";
            if (strpos($resi['description'], $val['profile']) !== false) {
                $profiles .= " selected";
            } else {
                $profiles .= "";
            }
            $profiles .= ">" . $val['profile'] . "</option>";
        }
        $res[] = array($name, $profiles, $checkBox);
    } else {
        echo "Your key has been expired";
    }
    return $res;
}



function ADMN_ValidateNotifyName($key, $pdo, $user, $name, $type, $id)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        if ($type == 'edit') {
            $stmt = $pdo->prepare("SELECT name FROM  " . $GLOBALS['PREFIX'] . "event.Notifications WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['name'] !== $name) {
                $stmt1 = $pdo->prepare("SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Notifications WHERE name = ?");
                $stmt1->execute([$name]);
                $res = $stmt1->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            $stmt = $pdo->prepare("SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Notifications WHERE name = ?");
            $stm1->execute([$name]);
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if (safe_count($res) > 0) {
            echo "available";
        } else {
            echo "na";
        }
    } else {
        echo "Your key has been expired";
    }
}

function ADMN_GetNotificationDetails($key, $db, $id)
{
    $details = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = $db->prepare("SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Notifications WHERE id = ?");
        $sql->execute([$id]);
        $res = $sql->fetch(PDO::FETCH_ASSOC);
        $details[] = array(
            'name' => $res['name'],
            'username' => $res['username'],
            'search_id' => $res['search_id'],
            'email' => $res['email'],
            'emaillist' => $res['emaillist'],
            'defmail' => $res['defmail'],
            'threshold' => $res['threshold'],
            'solo' => $res['solo'],
            'enabled' => $res['enabled'],
            'priority' => $res['priority'],
            'days' => $res['days'],
            'autotask' => $res['autotask'],
            'email_per_site' => $res['email_per_site'],
            'email_sender' => $res['email_sender'],
            'email_footer' => $res['email_footer'],
            'email_footer_txt' => $res['email_footer_txt'],
            'console' => $res['console'],
            'links' => $res['links'],
            'g_include' => $res['group_include'],
            'g_exclude' => $res['group_exclude'],
            'global' => $res['global'],
            'skip_owner' => $res['skip_owner'],
            'ntype' => $res['ntype'],
            'config' => $res['config'],
            'seconds' => $res['seconds'],
        );
        return $details;
    } else {
        echo "Your key has been expired";
    }
}

function ADMN_NotyUpdateSolution($key, $db)
{
    $id = url::requestToText('id');
    $solution = url::requestToText('solution');
    $autoenable = url::requestToText('autoenable');
    $profileId = url::requestToText('profileId');
    $name = url::requestToText('name');

    $key = DASH_ValidateKey($key);
    if ($key) {
        $profileSql = $db->prepare("SELECT id,dart,tileDesc,varValue,profile,shortDesc FROM " . $GLOBALS['PREFIX'] . "event.profile WHERE id = ?");
        $profileSql->execute([$profileId]);
        $profileRes = $profileSql->fetch(PDO::FETCH_ASSOC);

        $dart = $profileRes['dart'];
        $tileDesc = $profileRes['shortDesc'];
        $varValue = $profileRes['varValue'];
        $desc = $profileRes['profile'];

        $sql = $db->prepare("INSERT INTO  " . $GLOBALS['PREFIX'] . "event.NotificationProfile (nid,name,dartnum,dartconfig,description,tileDesc) " .
            "VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE dartconfig=? ,description=?,tileDesc=? ");
        $sql->execute([$id, $name, $dart, $varValue, $desc, $tileDesc, $varValue, $desc, $tileDesc]);
        $res = $db->lastInsertId();
        if ($res) {
            echo "success";
        } else {
            echo "failed";
        }
    } else {
        echo "Your key has been expired";
    }
}



function ADMN_GetSelNotifyData($selectedid, $type)
{

    $pdo = pdo_connect();
    $stmt = $pdo->prepare("SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Notifications WHERE id = ?");
    $stmt->execute([$selectedid]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    global $global;
    global $ntype;
    global $priority;
    global $username;
    global $name;
    global $days;
    global $solo;
    global $console;
    global $email;
    global $emaillist;
    global $defmail;
    global $search_id;
    global $seconds;
    global $threshold;
    global $last_run;
    global $next_run;
    global $this_run;
    global $group_include;
    global $group_exclude;
    global $group_suspend;
    global $config;
    global $machines;
    global $excluded;
    global $enabled;
    global $links;
    global $created;
    global $modified;
    global $skip_owner;
    global $email_footer;
    global $email_per_site;
    global $email_footer_txt;
    global $email_sender;
    global $autotask;

    $global = $res['global'];
    $ntype = $res['ntype'];
    $priority = $res['priority'];
    $username = $res['username'];
    if ($type == "edit") {
        $name = $res['name'];
    } else if ($type == "copy") {
        $name = "Copy of " . $res['name'];
    }
    $days = $res['days'];
    $solo = $res['solo'];
    $console = $res['console'];
    $email = $res['email'];
    $emaillist = $res['emaillist'];
    $defmail = $res['defmail'];
    $search_id = $res['search_id'];
    $seconds = $res['seconds'];
    $threshold = $res['threshold'];
    $last_run = $res['last_run'];
    $next_run = $res['next_run'];
    $this_run = $res['this_run'];
    $group_include = $res['group_include'];
    $group_exclude = $res['group_exclude'];
    $group_suspend = $res['group_suspend'];
    $config = $res['config'];
    $machines = $res['machines'];
    $excluded = $res['excluded'];
    $enabled = $res['enabled'];
    $links = $res['links'];
    $created = $res['created'];
    $modified = $res['modified'];
    $skip_owner = $res['skip_owner'];
    $email_footer = $res['email_footer'];
    $email_per_site = $res['email_per_site'];
    $email_footer_txt = $res['email_footer_txt'];
    $email_sender = $res['email_sender'];
    $autotask = $res['autotask'];
}



function ADMN_SubmitNotifyData($key, $pdo, $user, $type, $id)
{

    $name = url::requestToText('name');
    $search_id = url::requestToText('search_id');
    $email = url::requestToText('email');
    $emaillist = url::requestToText('emaillist');
    $defmail = url::requestToText('defmail');
    $threshold = url::requestToText('threshold');
    $solo = url::requestToText('solo');
    $enabled = url::requestToText('enabled');
    $priority = url::requestToText('priority');
    $days = url::requestToText('days');
    $autotask = url::requestToText('autotask');
    $email_per_site = url::requestToText('email_per_site');
    $email_sender = url::requestToText('email_sender');
    $email_footer = url::requestToText('email_footer');
    $email_footer_txt = url::requestToText('footertext');
    $console = url::requestToText('console');
    $links = url::requestToText('links');
    $g_include = url::requestToText('g_include');
    $g_exclude = url::requestToText('g_exclude');
    $global = url::requestToText('global');
    $skip_owner = url::requestToText('skip_owner');
    $ntype = url::requestToText('ntype');
    $config = url::requestToText('rprt_dtl');
    $seconds = url::requestToText('seconds');
    $userName = $_SESSION['user']['username'];
    $channelId = $_SESSION['user']['cId'];
    $time = time();

    $key = DASH_ValidateKey($key);
    $res = ADMN_getAllGroupid($pdo);

    foreach ($g_include as $val) {
        if (safe_sizeof($g_include) == 1) {
            $g_include_all = $val;
        } else {
            $site_Temp .= $val . ",";
        }
    }
    $site_temp_include = rtrim($site_Temp, ',');

    foreach ($g_exclude as $val) {
        if (safe_sizeof($g_exclude) == 1) {
            $g_exclude_all = $val;
        } else {
            $site_Temp_exc .= $val . ",";
        }
    }
    $site_temp_exclude = rtrim($site_Temp_exc, ',');

    $g_include = $g_include_all == $res ? $res : $site_temp_include;
    $g_exclude = $g_exclude_all == $res ? $res : $site_temp_exclude;

    if ($key) {
        if ($type == 'add' || $type == 'copy') {
            $stmt = $pdo->prepare("INSERT INTO  " . $GLOBALS['PREFIX'] . "event.Notifications (global,ntype,priority,name,username,days,solo,console,email,emaillist"
                . ",defmail,search_id,seconds,threshold,group_include,group_exclude,config,enabled,links,created"
                . ",skip_owner,email_footer,email_per_site,email_footer_txt,email_sender,autotask)"
                . " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $res = $stmt->execute([
                $global, $ntype, $priority, $name, $user, $days, $solo, $console, $email, $emaillist, $defmail, $search_id, $seconds,
                $threshold, $g_include, $g_exclude, $config, $enabled, $links, $time, $skip_owner, $email_footer, $email_per_site, $email_footer_txt,
                $email_sender, $autotask
            ]);
        } else if ($type == 'edit') {
            $stmt = $pdo->prepare("UPDATE  " . $GLOBALS['PREFIX'] . "event.Notifications SET global = ?, ntype = ?, priority = ?, name = ?, username = ?, days = ?, solo = ?,"
                . "console = ?, email = ?, emaillist = ?, defmail = ?, search_id = ?, seconds = ?, threshold = ?, group_include = ?, "
                . "group_exclude = ?, config = ?, enabled = ?, links = ?, modified = ?, skip_owner = ?, email_footer = ?, email_per_site = ?,"
                . "email_footer_txt = ?, email_sender = ?, autotask = ? WHERE id = ?");
            $res = $stmt->execute([
                $global, $ntype, $priority, $name, $user, $days, $solo, $console, $email, $emaillist, $defmail, $search_id, $seconds,
                $threshold, $g_include, $g_exclude, $config, $enabled, $links, $time, $skip_owner, $email_footer, $email_per_site, $email_footer_txt,
                $email_sender, $autotask, $id
            ]);
        }
    } else {
        echo "Your key has been expired";
    }
    return $res;
}



function ADMN_DeleteNotify($key, $db, $id)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = $db->prepare("DELETE FROM  " . $GLOBALS['PREFIX'] . "event.Notifications where id = ?");
        $sql->execute([$id]);
        $res = $db->lastInsertId();
    } else {
        echo "Your key has been expired";
    }
    return $res;
}



function ADMN_GetNanotime($when)
{
    if ($when > 0) {
        $that = date('m/d/y', time());
        $date = date('m/d/y', $when);
        $time = date('H:i:s', $when);
        $text = ($date == $that) ? $time : "$date $time";
    }
    if ($when < 0) {
        $text = "";
    }
    return $text;
}

function ADMN_GetEventfilterGridData($key, $db, $append_search, $authuser, $where, $fromDate)
{
    $res = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = $db->prepare("SELECT S.id,S.name,S.created,S.modified,S.username,replace(replace(S.searchstring, '\n', ' '), '\r', ' ') as  searchstring FROM  " . $GLOBALS['PREFIX'] . "event.SavedSearches as S
	        LEFT JOIN " . $GLOBALS['PREFIX'] . "event.SavedSearches as X ON S.name = X.name AND X.global = 0 AND X.username = ? WHERE X.id = S.id AND S.username = ? or S.global = 1
                order by name desc $append_search $where");
        $sql->execute([$authuser, $authuser]);
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($res) > 0) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function ADMN_EventValidateName($key, $db, $name, $username)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "event.SavedSearches WHERE name = ?");
        $sql->execute([$name]);
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        if (safe_count($res) > 0) {
            echo "available";
        } else {
            echo "continue";
        }
    } else {
        echo "Your key has been expired";
    }
}

function ADMN_EventSubmitData($key, $db, $name, $decode_filter, $username, $md5filter, $global)
{

    $key = DASH_ValidateKey($key);
    $time = time();

    if ($key == 1) {

        $value = ADMN_GetEventTag($decode_filter, $name);
        $dartNum = $value['dartNum'];
        $eventTag = $value['eventTag'];

        $sql = $db->prepare('INSERT INTO ' . $GLOBALS['PREFIX'] . 'event.SavedSearches (name,searchstring,username,searchuniq,created,global,dartnum,eventtag) VALUES (?,?,?,?,?,?,?,?)');
        $sql->execute([$name, $decode_filter, $username, $md5filter, $time, $global, $dartNum, $eventTag]);
        $res = $db->lastInsertId();

        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
    } else {
        echo "Your key has been expired";
    }
}

function ADMN_EventUpdateData($key, $db, $name, $decode_filter, $username, $editid, $global)
{
    $time = time();
    $key = DASH_ValidateKey($key);
    if ($key) {
        $value = ADMN_GetEventTag($decode_filter, $name);
        $dartNum = $value['dartNum'];
        $eventTag = $value['eventTag'];

        $sql = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "event.SavedSearches SET name=?, searchstring=?,modified=?,global=?,eventtag=?,dartnum=? WHERE id=? ");
        $sql->execute([$name, $decode_filter, $time, $global, $eventTag, $dartNum, $editid]);
        $res = $db->lastInsertId();

        if ($res) {
            echo "success";
        } else {
            echo "failed";
        }
    } else {
        echo "Your key has been expired";
    }
}

function ADMN_DeleteData($key, $db, $deleteid)
{
    $sql = "Delete from " . $GLOBALS['PREFIX'] . "event.SavedSearches where id=" . $deleteid . "";
    $res = redcommand($sql, $db);

    foreach ($res as $count) {
        if (count > 1) {
            echo "record deleted successfully";
        } else {
            echo "Sorry, not sufficient data";
        }
    }
}

function ADMN_CopyData($key, $db, $name, $filter, $username, $copyid, $md5filter, $global)
{
    $time = time();
    $key = DASH_ValidateKey($key);
    if ($key == 1) {
        $value = ADMN_GetEventTag($filter, $name);
        $dartNum = $value['dartNum'];
        $eventTag = $value['eventTag'];

        $sql = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "event.SavedSearches (name,searchstring,username,searchuniq,created,global,eventtag,dartnum)'
                . ' VALUES (?,?,?,?,?,?,?,?) ");
        $sql->execute([$name, $filter, $username, $md5filter, $time, $global, $eventTag, $dartNum]);
        $res = $db->lastInsertId();

        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
    } else {
        echo "Your key has been expired";
    }
}

function ADMN_AssetQueryGridData($key, $db, $username, $t1, $t2)
{
    $res = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = $db->prepare("SELECT S.username,S.id,S.name,replace(replace(S.searchstring,'\n',''),'\r','') as  searchstring,S.created,S.modified
			FROM " . $GLOBALS['PREFIX'] . "asset.AssetSearches as S
			LEFT JOIN " . $GLOBALS['PREFIX'] . "asset.AssetSearches as X
			ON S.name = X.name
			AND X.global = 0
			AND X.username = ?
			WHERE ((S.username = ?) OR (S.global = 1 and X.id is NULL))");
        $sql->execute([$username, $username]);
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($res) > 0) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}



function ADMN_DeleteAssetQuery($key, $db, $id)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "asset.AssetSearches where id =?");
        $sql->execute([$id]);
        $res = $db->lastInsertId();
    } else {
        echo "Your key has been expired";
    }
    return $res;
}



function ADMN_RunAssetQuery($db, $id, $name, $searchType, $searchVal, $parent)
{

    if ($searchType == 'Sites' || $searchType == 'Site') {
        $searchCol = 'cust';
        $searchName = $searchVal;
        $searchVals = $searchVal;
    } elseif ($searchType == 'ServiceTag' || $searchType == 'Host Name') {
        $searchCol = 'host';
        $searchName = $searchVal;
        $searchVals = $parent . ":" . $searchVal;
    } else {
        $searchCol = 'host';
        $searchName = $searchVal;
        $searchVals = $searchVal;
    }
    $offset = 1000;

    $searchNameArr = array();
    $searchNameArr = explode(',', $searchName);
    $in  = str_repeat('?,', safe_count($searchNameArr) - 1) . '?';
    $machineSql = $db->prepare("SELECT count(Distinct machineid) as count from " . $GLOBALS['PREFIX'] . "asset.Machine where $searchCol in ($in) ORDER BY machineid ASC");
    $machineSql->execute($searchNameArr);
    $machineRes = $machineSql->fetch(PDO::FETCH_ASSOC);
    $totalMachine = $machineRes['count'];

    $sql = $db->prepare("SELECT name FROM " . $GLOBALS['PREFIX'] . "asset.AssetSearches where id =?");
    $sql->execute([$id]);
    $result = $sql->fetch(PDO::FETCH_ASSOC);

    $sql1 = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "asset.AssetQueryResult( pid,qid,sitename,sitetype,machine,offset,queryName,status,global,pathName,fileName,userName,startTime,endTime)" .
        "VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $sql1->execute(['', $id, $searchVals, $searchType, $totalMachine, $offset, $result['name'], 'Process initiated', 0, 0, '', $name, '', '']);
    $insertRes = $db->lastInsertId();

    if ($insertRes) {
        return true;
    } else {
        return false;
    }
}



function ADMIN_GetEventFilterDetails($db, $key, $eid)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = $db->prepare("select id,name,username, replace(replace(searchstring, '', ' '), '', ' ') as  searchstring , created from " . $GLOBALS['PREFIX'] . "event.SavedSearches where id = ? limit 1");
        $sql->execute([$eid]);
        $sqlres = $sql->fetch(PDO::FETCH_ASSOC);
    } else {
        $msg = 'Your key has been expired';
        print_data($msg);
    }

    return $sqlres;
}

function ADMIN_GetEventFilterCronResult($db, $key, $eid, $condition)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = $db->prepare("select * from  " . $GLOBALS['PREFIX'] . "event.EventQueryResult where eid = ? $condition");
        $sql->execute([$eid]);
        $sqlres = $sql->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $msg = 'Your key has been expired';
        print_data($msg);
    }
    return $sqlres;
}

function ADMN_RunEventFilterQuery($db, $id, $name, $searchType, $searchVal, $rParent)
{

    if ($searchType == 'Sites' || $searchType == 'Site') {
        $searchCol = 'cust';
        $searchName = $searchVal;
        $searchVals = $searchVal;
    } elseif ($searchType == 'ServiceTag' || $searchType == 'Host Name') {
        $searchCol = 'host';
        $searchName = $searchVal;
        $searchVals = $rParent . ":" . $searchVal;
    } else {
        $searchCol = 'host';
        $searchName = $searchVal;
        $searchVals = $searchVal;
    }
    $offset = 1000;
    $searchNameArr = array();
    $searchNameArr = explode(',', $searchName);
    $in  = str_repeat('?,', safe_count($searchNameArr) - 1) . '?';
    $machineSql = $db->prepare("SELECT count(Distinct machineid) as count from " . $GLOBALS['PREFIX'] . "asset.Machine where $searchCol in ($in) ORDER BY machineid ASC");
    $machineSql->execute([$searchNameArr]);
    $machineRes = $machineSql->fetch(PDO::FETCH_ASSOC);
    $totalMachine = $machineRes['count'];

    $sql = $db->prepare("SELECT name from " . $GLOBALS['PREFIX'] . "event.SavedSearches where id = ?");
    $sql->execute([$id]);
    $result = $sql->fetch(PDO::FETCH_ASSOC);

    $sql1 = $db->prepare("INSERT INTO  " . $GLOBALS['PREFIX'] . "event.EventQueryResult( pid,eid,sitename,sitetype,machine,offset,filterName,status,global,pathName,fileName,userName,startTime,endTime)" .
        "VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $sql1->execute(['', $id, $searchVals, $searchType, $totalMachine, $offset, $result['name'], 'Process initiated', 0, 0, '', $name, '', '']);
    $insertRes = $db->lastInsertId();
    if ($insertRes) {
        return true;
    } else {
        return false;
    }
}


function ADMN_GetCensusData($db, $username, $searchType, $searchValue, $orderVal, $searchVal)
{

    $siteSql = $db->prepare("select customer as name from " . $GLOBALS['PREFIX'] . "core.Customers where username=? $searchVal $orderVal");
    $siteSql->execute([$username]);
    $siteRes = $siteSql->fetchAll(PDO::FETCH_ASSOC);

    foreach ($siteRes as $key => $val) {

        $siteName = $val['name'];
        $machineSql = $db->prepare("select host,born,last from " . $GLOBALS['PREFIX'] . "core.Census where site = ? ");
        $machineSql->execute([$siteName]);
        $machineRes = $machineSql->fetchAll(PDO::FETCH_ASSOC);
        if (safe_count($machineRes) > 0) {
            $list[$siteName] = $machineRes;
            $siteList[$siteName] = safe_count($machineRes);
        }
    }
    if (safe_count($siteList) > 0) {
        foreach ($siteList as $site => $count) {
            $sitePage[] = array(
                "DT_RowId" => $site,
                "siteName" => '<p class="ellipsis" title="' . $site . '">' . UTIL_GetTrimmedGroupName($site) . '</p>',
                "count" => '<p class="ellipsis" title="' . $count . '">' . $count . '</p>'
            );
        }
    } else {
        $sitePage = [];
    }
    return $sitePage;
}

function ADMN_GetMachineData($pdo, $siteName, $orderval, $where)
{
    $machineRes = NanoDB::find_many("SELECT DISTINCT(C.host) as host,C.born as born, C.last as last,id,os,clientversion FROM " . $GLOBALS['PREFIX'] . "core.Census C WHERE site = ?", [$siteName]);
    if (!empty($machineRes)) {

        foreach ($machineRes as $key => $val) {

            $host = $val['host'];
            $os = $val['os'];
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $borntime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $val['born'], "m/d/Y g:i:s A");
                $lasttime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $val['last'], "m/d/Y g:i:s A");
            } else {
                $borntime = date("m/d/Y g:i:s A", $val['born']);
                $lasttime = date("m/d/Y g:i:s A", $val['last']);
            }

            $born = ($val['born'] == 0) ? '-' : $borntime;
            $last = ($val['last'] == 0) ? '-' : $lasttime;
            $id = $val['id'];
            if ($os == '') {
                $os = '-';
            }

            $clientversion = $val['clientversion'];
            if (!$clientversion  || empty($clientversion)) {
                $clientversion = '-';
            }

            $action = "<p class='ellipsis' title='Expunge'><a href='javascript:'"
                . "onclick='removeMachin(&quot;$id&quot;,&quot;$host&quot;);' style='text-color:#ffedsw;color: #fa0f4b;text-decoration: underline;' >Expunge"
                . "<span id='loader_$id' style='display:none;'>"
                . "<img class='' alt='loader...' src='../assets/img/loader-sm.gif'>"
                . "</span></a></p>";
            $machineList[] = array($host, $os, $born, $last, $action, $clientversion, $id);
        }
    } else {
        $machineList = [];
    }

    return $machineList;
}

function ADMN_GetGroupMachineData($pdo, $groupname, $group)
{
    $stmt = $pdo->prepare("select m.username, m.created, m.updated, mp.censusuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups m inner join "
        . $GLOBALS['PREFIX'] . "core.MachineGroupMap mp where m.mgroupuniq=mp.mgroupuniq and m.mgroupid = ?");
    $stmt->execute([$groupname]);
    $sqlRes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sqlRes as $val) {
        $username = $val['username'];
        $createdTime = $val['created'];
        $modifiedTime = $val['updated'];
        $censusuniq[] = $val['censusuniq'];
    }
    $censusuniq = rtrim($censusuniq, ',');
    $census_in = str_repeat('?,', safe_count($censusuniq) - 1) . '?';
    $stmt1 = $pdo->prepare("select site,host,born,last,id,os from " . $GLOBALS['PREFIX'] . "core.Census c where c.censusuniq in ($census_in)");
    $stmt1->execute($censusuniq);
    $sql1Res = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sql1Res as $vals) {
        $host = $vals['host'];
    }

    $updatedTime = date('m/d/Y g:i:s A', $modifiedTime);
    if ($modifiedTime == 0) {
        $updatedTime = date('m/d/Y g:i:s A', $createdTime);
    }

    if (safe_count($sql1Res) > 0) {
        foreach ($sql1Res as $key => $val) {

            $host = $val['host'];
            $os = $val['os'];
            $born = ($val['born'] == 0) ? '-' : date('m/d/Y g:i:s A', $val['born']);
            $last = ($val['last'] == 0) ? '-' : date('m/d/Y g:i:s A', $val['last']);
            $id = $val['id'];
            if ($os == '') {
                $os = '-';
            }

            $action = "<p class='ellipsis' title='Expunge not allowed in Groups'>"
                . "<a href='javascript:' style='text-color:#ffedsw;color: #fa0f4b;text-decoration: underline;cursor: not-allowed;'>Expunge"
                . "<span id='loader_$id' style='display:none;'>"
                . "<img class='' alt='loader...' src='../assets/img/loader-sm.gif'>"
                . "</span></a></p>";
            $machineList[] = array($username, $updatedTime, $host, $os, $born, $last, $action, $id);
        }
    } else {
        $machineList = [];
    }

    return $machineList;
}

function ADMN_GetMachineOsData($db, $siteName)
{

    $machineSql = $db->prepare("select S.serviceTag,S.machineOS from " . $GLOBALS['PREFIX'] . "agent.serviceRequest S where S.siteName =? and S.downloadStatus='EXE' group by S.serviceTag  order by sid");
    $machineSql->execute([$siteName]);
    $machineRes = $machineSql->fetchAll(PDO::FETCH_ASSOC);
    $machineList = array();
    if (safe_count($machineRes) > 0) {
        foreach ($machineRes as $key => $val) {
            $host = $val['serviceTag'];
            $os = $val['machineOS'];
            $machineList[strtolower($host)] = $os;
        }
    } else {
        $machineList = [];
    }

    return $machineList;
}

function ADMIN_GetCensusExport($db, $column, $condition, $id)
{

    $column = $column == 'null' ? '' : $column;
    $dataNames = $column;

    if ($column != '') {
        $displayColumn = $condition . ",$column";
    } else {
        $displayColumn = $condition;
    }

    $condSplit = explode(',', $condition);
    foreach ($condSplit as $key => $val) {
        if ($val == 'Site Name') {
            $dataNames .= "," . $val;
        } else if ($val == 'Machine Name') {
            $dataNames .= "," . $val;
        }
    }
    $names = explode(',', $dataNames);
    $datanameArr = array();
    foreach ($names as $value) {
        array_push($datanameArr, $value);
    }

    $in  = str_repeat('?,', safe_count($datanameArr) - 1) . '?';
    $dataSql = $db->prepare("select dataid from " . $GLOBALS['PREFIX'] . "asset.DataName where name in ($in)");
    $dataSql->execute($datanameArr);
    $dataRes = $dataSql->fetchAll(PDO::FETCH_ASSOC);

    $dataidsArr = array();
    foreach ($dataRes as $value) {
        array_push($dataidsArr, $value['dataid']);
    }

    $sqlMachids = $db->prepare("select machineid from " . $GLOBALS['PREFIX'] . "asset.Machine where cust = ?");
    $sqlMachids->execute([$id]);
    $machIds = $sqlMachids->fetchAll(PDO::FETCH_ASSOC);

    $machineidsArr = array();
    foreach ($machIds as $value) {
        array_push($machineidsArr, $value['machineid']);
    }


    $sql = $db->prepare("select C.site,C.host,C.born as born ,C.last as last,M.slatest as lastsync,M.machineid from " . $GLOBALS['PREFIX'] . "core.Census as C
                left join " . $GLOBALS['PREFIX'] . "asset.Machine as M on C.host = M.host and C.site = M.cust
                where C.site = ?
                group by C.host order by C.host");
    $sql->execute([$id]);
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);

    $in1  = str_repeat('?,', safe_count($dataidsArr) - 1) . '?';
    $in2  = str_repeat('?,', safe_count($machineidsArr) - 1) . '?';
    $params = array_merge($dataidsArr, $machineidsArr);
    $sqlAsst = $db->prepare("select machineid,value,D.name from " . $GLOBALS['PREFIX'] . "asset.AssetDataLatest as AL join " .
        "asset.DataName as D on AL.dataid = D.dataid where D.dataid in ($in1) and machineid in ($in2) order by machineid,D.dataid");
    $sqlAsst->execute($params);
    $result1 = $sqlAsst->fetchAll(PDO::FETCH_ASSOC);

    $machineData = [];
    foreach ($result1 as $key => $value) {
        if ($value['name'] == 'IP address' || $value['name'] == 'IMEI NO') {
            $machineData[$value['machineid']][$value['name']][] = $value['value'];
            continue;
        }
        $machineData[$value['machineid']][$value['name']] = $value['value'];
    }


    $exportSheet = EXPORT_censusReport($result, $machineData, $displayColumn);

    return $exportSheet;
}

function ADMN_Census_Delete($mid)
{

    $username = 'admin';
    $password = 'nanoheal@123';
    $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
    $main_url = $pageURL . $_SERVER["HTTP_HOST"] . "/main/";

    $url = $main_url . 'acct/census.php?mid=' . $mid . '&action=del';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_GET, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function ADMN_Census_Expunge($mid,$index)
{
    $action = 'exp';
    $res = NH_Config_API_exp($mid, $action,$index);
    return $res;
}

function ADMN_Redis_Expunge($machid)
{
    $pdo = pdo_connect();

    $stmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Census where id = ? limit 1");
    $stmt->execute([$machid]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {

        $ServiceTag = $data['host'];

        $redis = RedisLink::connect();

        $redis->delete("$ServiceTag", 0, -1);
    }
}

function ADMN_getSiteGroupsForUser($pdo, $user, $ch_id)
{

    $stmt = $pdo->prepare("select customer as name,id as mgroupid from " . $GLOBALS['PREFIX'] . "core.Customers where username = ? order by lower(customer)");
    $stmt->execute([$user]);
    $siteres = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $siteres;
}

function ADMN_GetEventTag($decode_filter, $name)
{

    $searchVal = strtolower($decode_filter);

    $string = str_replace("(", "", $searchVal);
    $string1 = str_replace(")", "", $string);
    $string2 = str_replace("and", "", $string1);
    $string3 = str_replace("or", "", $string2);

    $string4 = str_replace(" =", "=", $string3);
    $string5 = str_replace("= ", "=", $string4);
    $string6 = str_replace(" !=", "=", $string5);

    preg_match_all('/scrip=([^\s]+)/', $string6, $matches);
    $dartNum = implode(',', $matches[1]);

    $str = $name;
    $input = preg_replace("/[^a-zA-Z0-9]+/", "", $str);
    $eventtag = strtolower($input);

    return array("dartNum" => $dartNum, "eventTag" => $eventtag);
}

function ADMN_GetSkuList($db)
{

    $skuList = array();

    $sql = $db->prepare("SELECT id,skuName,description,noOfDays,licenseCnt,skuPrice FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster ");
    $sql->execute();
    $sqlRes = $sql->fetchAll(PDO::FETCH_ASSOC);


    return $sqlRes;
}

function ADMN_createSku($db, $skuName, $skuRef, $skuDesc, $skuPrice, $skuReminder, $skuType, $licenseCount, $lang, $validity, $platformPrice)
{

    $sql = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.skuMaster (skuType,skuName,skuRef,description,licensePeriod," .
        "noOfDays,licenseCnt,skuPrice,platformPrice,localization) VALUES(?,?,?,?,?,?,?,?,?,?) ");
    $sql->execute([$skuType, $skuName, $skuRef, $skuDesc, $validity, $validity, $licenseCount, $skuPrice, $platformPrice, $lan]);
    $sqlRes = $db->lastInsertId();

    if ($sqlRes) {
        return array('status' => 'success', 'msg' => 'Sku addition successful');
    } else {
        return array('status' => 'failed', 'msg' => 'Sku addition failed.Try after sometime.');
    }
}

function ADMN_GetSkuDetails($db, $id)
{

    $result = array();

    $sql = $db->prepare("SELECT skuType,skuName,skuRef,description,licensePeriod," .
        "noOfDays,licenseCnt,skuPrice,platformPrice,localization FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE id=?");
    $sql->execute([$id]);
    $sqlRes = $sql->fetch(PDO::FETCH_ASSOC);

    if (safe_count($sqlRes) > 0) {
        $result = $sqlRes;
    }
    return $result;
}

function ADMN_updateSku($db, $skuName, $skuRef, $skuDesc, $skuPrice, $skuReminder, $skuType, $licenseCount, $lang, $validity, $platformPrice, $id)
{

    $sql = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.skuMaster SET skuType= ?,skuName=?,skuRef=?," .
        "description=?,licensePeriod=?,noOfDays=?," .
        "licenseCnt=?,skuPrice=?,platformPrice=?,localization=? WHERE id=?");
    $sql->execute([$skuType, $skuName, $skuRef, $skuDesc, $licenseCount, $validity, $licenseCount, $skuPrice, $platformPrice, $lan, $id]);
    $sqlRes = $db->lastInsertId();

    if ($sqlRes) {
        return array('status' => 'success', 'msg' => 'Sku update successful');
    } else {
        return array('status' => 'failed', 'msg' => 'Sku update failed.Try after sometime.');
    }
}

function ADMN_GetAuditData($db, $user, $fromdate, $todate)
{
    $from = strtotime($fromdate);
    $to = strtotime($todate);
    $siteArrListArr = array();

    $user_id = $_SESSION['user']['userid'];
    $loggedUserName = $_SESSION['user']['logged_username'];

    $usrArr = getChildDetails($user_id, 'username');

    $usrArrData = array_merge([$loggedUserName], $usrArr);

    $grouplist = $_SESSION['user']['group_list'];
    $siteAccessList = $_SESSION['user']['site_list'];
    foreach ($grouplist as $key => $val) {
        array_push($siteAccessList, $key);
    }
    foreach ($siteAccessList as $value) {
        array_push($siteArrListArr, $value);
    }
    $in  = str_repeat('?,', safe_count($siteArrListArr) - 1) . '?';
    $in2  = str_repeat('?,', safe_count($usrArrData) - 1) . '?';
    if ($fromdate == '') {
        $last24hrs = strtotime('-30 Days');
        $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.Audit where site IN ($in) and user in ($in2) and time >= ? order by time desc");
        $params = array_merge($siteArrListArr, $usrArrData, [$last24hrs]);
        $sql->execute($params);
    } else {
        $params = array_merge($siteArrListArr, $usrArrData, [$from, $to]);
        $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.Audit where site IN ($in) and user in ($in2) and time BETWEEN ? AND ? order by time desc");
        $sql->execute($params);
    }
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);
    if (safe_count($res)) {
        return $res;
    } else {
        return array();
    }
}

function ADMN_GetAuditDataSite($db, $user, $fromdate, $todate, $siteAccessList)
{
    $from = strtotime($fromdate);
    $to = strtotime($todate);
    if ($fromdate == '') {
        if ($siteAccessList == 'All') {
            $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.Audit order by time desc");
            $sql->execute();
        } else {
            $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.Audit where site = ? order by time desc");
            $sql->execute([$siteAccessList]);
        }
    } else {
        if ($siteAccessList == 'All') {
            $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.Audit where time BETWEEN ? AND ? order by time desc");
            $sql->execute([$from, $to]);
        } else {
            $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.Audit where site = ? and time BETWEEN ? AND ? order by time desc");
            $sql->execute([$siteAccessList, $from, $to]);
        }
    }
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);

    if (safe_count($res)) {
        return $res;
    } else {
        return array();
    }
}

function ADMN_GetAuditDataUser($db, $user, $fromdate, $todate, $username)
{
    $db = pdo_connect();
    $fromdate = strtotime($fromdate);
    $todate = strtotime($todate);

    $where = '';

    $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE username=?");
    $sql->execute([$username]);
    $SqlRes = $sql->fetch(PDO::FETCH_ASSOC);
    $email = $SqlRes['user_email'];
    $userId = $SqlRes['userid'];
    $uId = $_SESSION['user']['userid'];
    $loggeduser = $_SESSION['user']['logged_username'];
    $usrArr = getChildDetails($uId, 'username');
    array_push($usrArr, $loggeduser);
    $customerType = $_SESSION['user']['customerType'];
    $uname = $_SESSION['user']['username'];

    $sitesql = $db->prepare("select customer as name from " . $GLOBALS['PREFIX'] . "core.Customers where username=? order by lower(customer)");
    $sitesql->execute([$uname]);
    $siteres = $sitesql->fetchAll(PDO::FETCH_ASSOC);


    foreach ($siteres as $key => $val) {
        $sites[] = utf8_encode($val['name']);
    }

    if ($username == 'All') {
        $params = array_merge($usrArr, $sites, [$fromdate, $todate]);
    } else {
        $params = array_merge([$username], $sites, [$fromdate, $todate]);
    }

    if ($username == 'All') {
        $in1 = str_repeat('?,', safe_count($usrArr) - 1) . '?';
        $in2 = str_repeat('?,', safe_count($sites) - 1) . '?';
        $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.Audit WHERE user in ($in1) and site in($in2) and time >= ? and time <= ? order by time desc");
    } else {
        $in2 = str_repeat('?,', safe_count($sites) - 1) . '?';
        $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.Audit WHERE user =? and site in($in2) and time >= ? and time <= ? order by time desc");
    }
    $sql2->execute($params);
    $result = $sql2->fetchAll(PDO::FETCH_ASSOC);

    return $result;
}

function ADMN_getAllGroupid($pdo)
{
    $stmt = $pdo->prepare("SELECT mgroupid FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ?");
    $stmt->execute(['All']);
    $sqlRes = $stmt->fetch(PDO::FETCH_ASSOC);

    $mgroupid = $sqlRes['mgroupid'];
    return $mgroupid;
}

function ADMN_ExportAudit($db, $fromDate, $toDate, $level, $sublistval)
{

    $headerArray = array("A" => "Time", "B" => "User", "C" => "Detail");

    try {
        $objPHPExcel = AJAX_GetExcelSheetObject($headerArray, 30);
        if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
            $userTimeZone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : date_default_timezone_get();
            $myTimeZone = $userTimeZone;
            $toTimeZone = date_default_timezone_get();
            date_default_timezone_set($myTimeZone);
        }

        if ($level == 'User') {
            $res = ADMN_GetAuditDataUser($db, $user, $fromDate, $toDate, $sublistval);
        } else {
            $res = ADMN_GetAuditDataSite($db, $user, $fromDate, $toDate, $sublistval);
        }
        if (safe_count($res) > 0) {
            $objPHPExcel = AJAX_CreateDartAuditExcelSheet($objPHPExcel, $res);
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . 2, 'No Data Available');
        }

        $fn = "Audit.xls";
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fn . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        $objWriter->save('php://output');
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function ADMN_GetMachineData_export($db, $siteName)
{
    // $orderColoumn = '';

    // $machineSqlCount = $db->prepare("SELECT count(DISTINCT(C.host)) as count FROM " . $GLOBALS['PREFIX'] . "core.Census C WHERE site = ? ");
    // $machineSqlCount->execute([$siteName]);
    // $machineTotal = $machineSqlCount->fetch(PDO::FETCH_ASSOC);

    $machineSql = $db->prepare("SELECT DISTINCT(C.host) as host, C.born as born," .
        "C.last as last, id, os, clientversion FROM " . $GLOBALS['PREFIX'] . "core.Census C WHERE site = ? ");
    $machineSql->execute([$siteName]);
    $machineRes = $machineSql->fetchAll(PDO::FETCH_ASSOC);
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $activeSheet =  $objPHPExcel->getActiveSheet();

    $activeSheet->getStyle("A1:Z1")->getFont()->setBold(true);
    $activeSheet->setCellValue('A1', "Site Name");
    $activeSheet->setCellValue('B1', "Machine Name");
    $activeSheet->setCellValue('C1', "Machine OS");
    $activeSheet->setCellValue('D1', "Date Added");
    $activeSheet->setCellValue('E1', "Last Event");
    $activeSheet->setCellValue('F1', "Version");
    // $activeSheet->setTitle($siteName . "Site Details");
    //   $timezone = $_SESSION['user']['usertimezone'];
    if (safe_count($machineRes) > 0) {
        $index = 2;
        foreach ($machineRes as $key => $val) {
            $host = $val['host'];
            $os = $val['os'];
            $born = $val['born'];
            $last = $val['last'];
            $version = $val['clientversion'];

            $activeSheet->setCellValue('A' . $index, $siteName);
            $activeSheet->setCellValue('B' . $index, $host);
            $activeSheet->setCellValue('C' . $index, $os);
            if ($born == 0) {
                $activeSheet->setCellValue('D' . $index, 'NA');
            } else {
                $activeSheet->setCellValue('D' . $index, date('Y-m-d H:i:s', $born));
            }
            if ($last == 0) {
                $activeSheet->setCellValue('E' . $index, 'NA');
            } else {
                $activeSheet->setCellValue('E' . $index, date('Y-m-d H:i:s', $last));
            }
            if ($version == 0) {
                $activeSheet->setCellValue('F' . $index, 'NA');
            } else {
                $activeSheet->setCellValue('F' . $index, $version);
            }
            $index++;
        }
    } else {
        $activeSheet->setCellValue('A2', 'No data available');
    }
    // $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $siteName . '_Site_Details.csv"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
    ob_end_clean();
    $objWriter->save('php://output');
}

function ADMN_GetGroupMachineData_export($db, $groupname, $group)
{

    $sql = $db->prepare("select censusuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups m inner join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap mp where m.mgroupuniq=mp.mgroupuniq and m.mgroupid = ?");
    $sql->execute([$groupname]);
    $sqlRes = $sql->fetchAll(PDO::FETCH_ASSOC);
    $censusuniq = array();
    foreach ($sqlRes as $val) {
        array_push($censusuniq, $val['censusuniq']);
    }

    $in  = str_repeat('?,', safe_count($censusuniq) - 1) . '?';
    $sql1 = $db->prepare("select site, host, born, last, id, os, clientversion from " . $GLOBALS['PREFIX'] . "core.Census c where c.censusuniq in ($in)");
    $sql1->execute([$censusuniq]);
    $sql1Res = $sql1->fetchAll(PDO::FETCH_ASSOC);


    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $activeSheet =  $objPHPExcel->getActiveSheet();
    $activeSheet->getStyle("A1:Z1")->getFont()->setBold(true);
    $activeSheet->setCellValue('A1', "Group Name");
    $activeSheet->setCellValue('B1', "Machine Name");
    $activeSheet->setCellValue('C1', "Machine OS");
    $activeSheet->setCellValue('D1', "Date Added");
    $activeSheet->setCellValue('E1', "Last Event");
    $activeSheet->setCellValue('F1', "Timezone");
    $activeSheet->setCellValue('G1', "Version");
    $timezone = $_SESSION['user']['usertimezone'];

    if (safe_count($sql1Res) > 0) {
        $index = 2;
        foreach ($sql1Res as $key => $val) {

            $host = $val['host'];
            $os = $val['os'];
            $born = ($val['born'] == 0) ? '-' : date('m/d/Y g:i:s A', $val['born']);
            $last = ($val['last'] == 0) ? '-' : date('m/d/Y g:i:s A', $val['last']);
            $version = $val['clientversion'];

            if ($os == '') {
                $os = '-';
            }

            $activeSheet->setCellValue('A' . $index, $group);
            $activeSheet->setCellValue('B' . $index, $host);
            $activeSheet->setCellValue('C' . $index, $os);
            $activeSheet->setCellValue('D' . $index, $born);
            $activeSheet->setCellValue('E' . $index, $last);
            $activeSheet->setCellValue('F' . $index, $timezone);
            $activeSheet->setCellValue('G' . $index, $version);
            $index++;
        }
    } else {
        $activeSheet->setCellValue('A2', 'No data available');
    }

    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="Groups.csv"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
    ob_end_clean();
    $objWriter->save('php://output');
}

function ADMN_GetMachinesAndGroups($pdo, $groupname, $group)
{
    $username = $_SESSION['user']['username'];

    $strname = $groupname;
    $groupsql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.MachineGroups where lower(name) = ? and style = ? limit 1");
    $groupsql->execute([strtolower($strname), $group]);
    $selectres = $groupsql->fetch(PDO::FETCH_ASSOC);
    $groupname = $selectres['mgroupid'];
    $gname = $selectres['name'];
    $sqlRes = NanoDB::find_many("select G.username, G.created, G.updated, M.censusuniq from " . $GLOBALS['PREFIX'] . "core.Census as C, "
        . $GLOBALS['PREFIX'] . "core.Customers as U, " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M, " . $GLOBALS['PREFIX'] . "core.MachineGroups as G "
        . "where M.censusuniq = C.censusuniq and M.mgroupuniq = G.mgroupuniq and "
        . "G.mgroupid = ? and U.customer = C.site and U.username = ?;", [$groupname, $username]);

    $censusuniq = [];
    // var_dump($sqlRes);
    foreach ($sqlRes as $val) {
        $username = $val['username'];
        $censusuniq[] = $val['censusuniq'];
    }

    if (empty($censusuniq)) {
        return [];
    }

    $census_in = str_repeat('?,',  count($censusuniq) - 1) . '?';
    $sql1Res = NanoDB::find_many("select site,host,born,last,id,updated,os, clientversion from " . $GLOBALS['PREFIX'] . "core.Census c where c.censusuniq in ($census_in)", $censusuniq);

    foreach ($sql1Res as $vals) {
        $host = $vals['host'];
    }

    $groupid = $groupname;
    if (!empty($sql1Res)) {
        $groupname = '<input type="hidden" id="hidden_groupid" value="' . $groupname . '">'
            . '<input type="hidden" id="hidden_groupname" value="' . $gname . '">';
        foreach ($sql1Res as $key => $val) {
            $site = $val['site'];
            $host = $val['host'];
            $os = $val['os'];
            $born = ($val['born'] == 0) ? '-' : date('m/d/Y g:i:s A', $val['born']);
            $last = ($val['last'] == 0) ? '-' : date('m/d/Y g:i:s A', $val['last']);

            $clientversion = $val['clientversion'];
            if (!$clientversion  || empty($clientversion)) {
                $clientversion = '-';
            }

            if ($os == '') {
                $os = '-';
            }

            $machineList[] = array($site, $host, $os, $born, $last, $groupid, $clientversion);
        }
    } else {
        $machineList = [];
    }

    return $machineList;
}

function ADMN_GetGroupsInfo($pdo, $groupname, $group)
{
    $username = $_SESSION['user']['username'];
    $strname = $groupname;

    $groupsql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.MachineGroups where lower(name) = ? and style = ? limit 1");
    $groupsql->execute([strtolower($strname), $group]);
    $selectres = $groupsql->fetch(PDO::FETCH_ASSOC);
    $groupname = $selectres['mgroupid'];
    $gname = $selectres['name'];
    $stmt = $pdo->prepare("select G.username, G.created, G.updated, M.censusuniq from " . $GLOBALS['PREFIX'] . "core.Census as C, "
        . $GLOBALS['PREFIX'] . "core.Customers as U, " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M, " . $GLOBALS['PREFIX'] . "core.MachineGroups as G "
        . "where M.censusuniq = C.censusuniq and M.mgroupuniq = G.mgroupuniq and "
        . "G.mgroupid = ? and U.customer = C.site and U.username = ? limit 1;");
    $stmt->execute([$groupname, $username]);
    $sqlRes = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = $sqlRes['username'];
    $createdTime = $sqlRes['created'];
    $modifiedTime = $sqlRes['updated'];
    $censusuniq = $sqlRes['censusuniq'];
    $gmsql = "select modifiedby, modifiedtime from " . $GLOBALS['PREFIX'] . "core.GroupMappings where groupname IN "
        . "(select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = ?) limit 1;";
    $gmstmt = $pdo->prepare($gmsql);
    $gmstmt->execute([$groupname]);
    $gmres = $gmstmt->fetch(PDO::FETCH_ASSOC);
    $grpmodifiedby = ($gmres['modifiedby'] != '') ? $gmres['modifiedby'] : '-';
    $grpmodifiedtime = ($gmres['modifiedtime'] != '') ? date('m/d/Y g:i:s A', $gmres['modifiedtime']) : '-';
    if ($gmres['modifiedtime'] != '') {
        if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
            $grpmodifiedtime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $gmres['modifiedtime'], "m/d/Y g:i:s A");
        } else {
            $grpmodifiedtime = date("m/d/Y g:i:s A", $gmres['modifiedtime']);
        }
    } else {
        $grpmodifiedtime = '-';
    }
    $stmt1 = $pdo->prepare("select site,host,born,last,id,os,updated from " . $GLOBALS['PREFIX'] . "core.Census c where c.censusuniq = ?");
    $stmt1->execute([$censusuniq]);
    $sql1Res = $stmt1->fetch(PDO::FETCH_ASSOC);
    $host = $sql1Res['host'];

    if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
        $groupcTime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $createdTime, "m/d/Y g:i:s A");
        $uTime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $modifiedTime, "m/d/Y g:i:s A");
    } else {
        $groupcTime = date("m/d/Y g:i:s A", $createdTime);
        $uTime = date("m/d/Y g:i:s A", $modifiedTime);
    }
    $cTime = $groupcTime;
    $updatedTime = $uTime;
    if ($modifiedTime == 0) {
        $updatedTime = $cTime;
    }
    $host = $sql1Res['host'];
    $os = $sql1Res['os'];
    $born = ($sql1Res['born'] == 0) ? '-' : date('m/d/Y g:i:s A', $sql1Res['born']);
    $last = ($sql1Res['last'] == 0) ? '-' : date('m/d/Y g:i:s A', $sql1Res['last']);
    $id = $sql1Res['id'];
    if ($os == '') {
        $os = '-';
    }

    $action = "<p class='ellipsis' title='Expunge not allowed in Groups'>"
        . "<a href='javascript:' style='text-color:#ffedsw;color: #2695ca;text-decoration: underline;cursor: not-allowed;' >Expunge"
        . "<span id='loader_$id' style='display:none;'>"
        . "<img class='' alt='loader...' src='../assets/img/loader-sm.gif'>"
        . "</span></a></p>";
    $stmtstyle1 = $pdo->prepare("select * from group_styles where style_number = ?");
    $stmtstyle1->execute([$group]);
    $sql1styleRes = $stmtstyle1->fetch(PDO::FETCH_ASSOC);
    $machineList = array("groupname" => $gname, "style" => $sql1styleRes['style_name'], "Updatetime" => $updatedTime, "Modifiedby" => $grpmodifiedby, "ModifyTime" => $grpmodifiedtime, "username" => $username, "MachineName" => $host, "addedon" => $groupcTime, "Lastupdate" => $last, "Groupid" => $groupname);
    return $machineList;
}


function callEsfunction($type, $deviceName, $siteName, $action, $index)
{
    callEs2($type, $deviceName, $siteName, $action, $index);
}

function callEs2($type, $machineName, $siteName, $action, $index = '*')
{
    global $elastic_username;
    global $elastic_password;
    global $elastic_url;

    $targetIndex = ($index == '*') ? 'assets*,events*,patches*' : $index;

    if ($action == 'get') {
        $url = $elastic_url . $targetIndex . '/_search?size=10000';
    } else if ($action == 'delete') {
        $url = $elastic_url . $targetIndex . '/_delete_by_query';
    }
    if ($url) {
        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Basic " . base64_encode($elastic_username . ":" . $elastic_password);
        $key = ('user' == $type) ? 'user' : 'machine';

        if ($type == 'machine') {
            $query = '{
                "query": {
                  "bool": {
                    "must": [
                      {
                        "match": {
                          "machine.keyword": "' . $machineName . '"
                        }
                      },
                      {
                        "match": {
                          "site.keyword": "' . $siteName . '"
                        }
                      }
                    ]
                  }
                }
              }';
        } else {
            $query = '{
                "query" : {
                    "bool" : {
                        "must" : {
                            "match" : {
                                "' . $key . '.keyword" : "' . $machineName . '"
                            }
                        }
                    }
                }
            }';
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        $errorno = curl_errno($ch);

        $response = safe_json_decode($result, true);

        return $response;
    }


    return false;
}
