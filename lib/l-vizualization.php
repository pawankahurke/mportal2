<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once 'l-db.php';
require_once '../include/common_functions.php';

function getUserDefaultDashboardData(int $userId)
{
    $pdo = NanoDB::connect();
    $parents = getParents($userId);
    $delims = implode(",", $parents);
    $userTreeSql = $parents ? '(uid in(' . $delims . '))' : '';
    $userTreeSqlWhere = '';
    $condition = function ($case) {
        return "IF(
                    envGlobal=1 AND defaultPage='1' AND global='1' AND dashboardId!='',
                    $case,
                    0
                )";
    };

    $restCondition = $condition(2);

    if ($parents) {
        $userTreeSqlWhere = $userTreeSql . ' OR';
        $restCondition = "IF(
                                defaultPage='1' AND global='1' AND dashboardId!='' AND $userTreeSql,
                                2,
                                " . $condition(3) . "
                        )";
    }

    $query = "SELECT
                *,
                IF(
                        defaultPage=1 && uid=? && dashboardId!='',
                        1,
                        " . $restCondition . "
                )
                as hierarchy,
                GROUP_CONCAT(uid) as gr_uid,
                GROUP_CONCAT(dashboardId) as gr_dashboardId,
                GROUP_CONCAT(dashboardName) as gr_dashboardName
                FROM " . $GLOBALS['PREFIX'] . "agent.dashboard
                WHERE
                        (defaultPage=1 AND uid=? AND dashboardId!='')
                        OR
                        (
                            defaultPage='1' AND global='1' AND dashboardId!=''
                            AND
                            (
                                " . $userTreeSqlWhere . "
                                (envGlobal=1)
                            )
                        )
                GROUP BY hierarchy
                ORDER BY hierarchy LIMIT 1";

    $bindings = [$userId, $userId];
    $ob = $pdo->prepare($query);
    $ob->execute($bindings);
    $row = $ob->fetch(PDO::FETCH_ASSOC);

    $fetchTopFromGroupedRow = function ($groupedRow, $parentOrder) {
        $foundIndex = false;

        $grUids = explode(",", $groupedRow['gr_uid']);
        $grDashIds = explode(",", $groupedRow['gr_dashboardId']);
        $grDashName = explode(",", $groupedRow['gr_dashboardName']);
        $grDashType = explode(",", $groupedRow['type']);

        $idx = intval(0);
        foreach ($parentOrder as $eachParentId) {
            if (in_array($eachParentId, $grUids)) {
                $foundIndex = array_search($eachParentId, $grUids);
                break;
            }
            $idx++;
        }

        return $foundIndex || is_numeric($foundIndex) ? ['uid' => $grUids[$foundIndex], 'dashboardId' => $grDashIds[$foundIndex], 'dashboardName' => $grDashName[$foundIndex], 'type' => $grDashType[$foundIndex]] : false;
    };

    if ($row) {
        $returnRow = [];

        if (is_numeric($row['hierarchy']) && intval($row['hierarchy']) == 2) {
            $rowData = $fetchTopFromGroupedRow($row, $parents);
            if (!$rowData) return false;
            $returnRow['uid'] = $rowData['uid'];
            $returnRow['dashboardId'] = $rowData['dashboardId'];
            $returnRow['dashboardName'] = $rowData['dashboardName'];
            $returnRow['type'] = $rowData['type'];
        } else {
            $returnRow['uid'] = $row['uid'];
            $returnRow['dashboardId'] = $row['dashboardId'];
            $returnRow['dashboardName'] = $row['dashboardName'];
            $returnRow['type'] = $row['type'];
        }

        return $returnRow;
    }

    return false;
}

function getUserDefaultDashboard()
{
    $sql = NanoDB::connect()->prepare("select * from " . $GLOBALS['PREFIX'] . "core.UserDashboards where visualization = 0 and global = 1 order by id asc limit 1;");
    $sql->execute();
    $res = $sql->fetch();
    $id = $res['id'];
    $name = $res['name'];
    return array('id' => $id, 'name' => $name);
}

function getUserVizualizations( )
{
    $db = NanoDB::connect();
    $resultArr1 =  array();
    $resultArr2 = array();
    $allowdTrue = array();

    // $sql1 = $db->prepare("SELECT * FROM ".$GLOBALS['PREFIX']."core.Dashboards");
    // $sql1->execute();
    // $sql1Res = $sql1->fetchAll(PDO::FETCH_ASSOC);

    $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.UserDashboards");
    $sql2->execute();
    $sql2Res = $sql2->fetchAll(PDO::FETCH_ASSOC);

    // $allDashboardArr = array_merge($sql1Res,$sql2Res);
    // foreach($allDashboardArr as $key=>$val){
    foreach ($sql2Res as $key => $val) {
        $id = $val['id'];
        $name = $val['name'];
        $type = $val['visualization'];
        $resultArr2[] = array('id' => $id, 'name' => $name, 'type' => $type);
    }
    return $resultArr2;
}

function getVizList(int $userId)
{
    $db = NanoDB::connect();
    $resultArr1 =  array();
    $resultArr2 = array();
    $allowdTrue = array();

    $sql1 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Dashboards");
    $sql1->execute();
    $sql1Res = $sql1->fetchAll(PDO::FETCH_ASSOC);

    $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.UserDashboards");
    $sql2->execute();
    $sql2Res = $sql2->fetchAll(PDO::FETCH_ASSOC);

    $allDashboardArr = array_merge($sql1Res, $sql2Res);
    $dashIds = array();
    foreach ($allDashboardArr as $key => $val) {
        $ids = $val['id'];
        $sql1 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.DashboardUsers WHERE dashid=? and allowedit = '1'");
        $sql1->execute([$ids]);
        $sql1Res = $sql1->fetch(PDO::FETCH_ASSOC);
        $allowdTrue = explode(',', $sql1Res['user']);
        $type = $sql1Res['type'];
        if ($type == 'userDef') {
            if (in_array($userId, $allowdTrue)) {
                $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.UserDashboards where id=?  and visualization = 0");
                $sql->execute([$ids]);
                $sqlRes = $sql->fetch(PDO::FETCH_ASSOC);
                $id = $sqlRes['id'];
                $name = $sqlRes['name'];
                $type = $sqlRes['visualization'];
                $resultArr1[] = array('id' => $id, 'name' => $name, 'type' => $type);
            }
        } else {
            if (in_array($userId, $allowdTrue)) {
                $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Dashboards where id=?  and visualization = 0");
                $sql->execute([$ids]);
                $sqlRes = $sql->fetch(PDO::FETCH_ASSOC);
                $id = $sqlRes['id'];
                $name = $sqlRes['name'];
                $type = $sqlRes['visualization'];
                $resultArr2[] = array('id' => $id, 'name' => $name, 'type' => $type);
            }
        }
    }
    $finalArr = array_merge($resultArr1, $resultArr2);
    return $finalArr;
}
