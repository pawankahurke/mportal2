<?php


class MachineGroups
{
    public static function getMachinesByGroupName($name)
    {
        $sql = NanoDB::connect()->prepare("SELECT Census.host as host FROM " . $GLOBALS['PREFIX'] . "core.Census," . $GLOBALS['PREFIX'] . "core.MachineGroupMap," . $GLOBALS['PREFIX'] . "core.MachineGroups
        WHERE
        Census.censusuniq = MachineGroupMap.censusuniq
        AND
        MachineGroups.mgroupuniq=MachineGroupMap.mgroupuniq
        AND
        MachineGroups.style <> 1
        AND 
        MachineGroups.name = ?");
        $sql->execute([$name]);
        $data = $sql->fetchAll();

        $res = [];
        foreach ($data as $key => $row) {
            $res[] = $row["host"];
        }

        return $res;
    }

    /**
     * @deprecated
     */
    public static function createNewMgroupuniqId($name, $mcatid)
    {

        /*
    |  Builds a site or host machine group for config.
    |  We leave many fields default:
    |
    |     core.MachineGroups.human = 0;
    |     core.MachineGroups.username = '';
    |     core.MachineGroups.eventspan = 0;
    |     core.MachineGroups.eventquery = 0;
    |     core.MachineGroups.assetquery = 0;
    */
        $now = time();
        $typ = '1'; // - constStyleBuiltin


        $mgroupuniq = md5(uniqid(true));
        $MachineCategories = NanoDB::find_one("select mcatuniq from "
            . $GLOBALS['PREFIX'] . "core.MachineCategories where mcatid=? ", [$mcatid]);

        $sql = "insert into " . $GLOBALS['PREFIX'] . "core.MachineGroups (name,global,style,created,boolstring,"
            . "mgroupuniq,mcatuniq) values (  ?, 1, $typ, $now,'Built-In',"
            . " ?, ? ) ";

        NanoDB::insert($sql, [$name, $mgroupuniq, $MachineCategories['mcatuniq']]);
        return  $mgroupuniq;
    }


    public static function getmgroupuniqId($searchType = null, $searchValue = null, $rparentValue = null)
    {

        $db = NanoDB::connect();
        if (!$searchType) {
            if(isset($_SESSION['searchType']) && $_SESSION['searchType'] !== null){
                $searchType = trim($_SESSION['searchType']);
            }else{
                $searchType = '';
            }
        }
        if (!$searchValue) {
            if(isset($_SESSION['searchValue']) && $_SESSION['searchValue'] !== null){
                $searchValue = trim($_SESSION['searchValue']);
            }else{
                $searchValue = '';
            }
        }
        if (!$rparentValue) {
            if(isset($_SESSION['rparentName']) && $_SESSION['rparentName'] !== null){
                $rparentValue = trim($_SESSION['rparentName']);
            }else{
                $rparentValue = '';
            }
        }


        if ($searchType == 'Sites' || $searchType == 'Groups') {

            $sql = $db->prepare("SELECT mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");

            $sql->execute([$searchValue]);
            $sqlRes = $sql->fetch();

            $mgroupid = $sqlRes['mgroupuniq'];

            logs::log("getmgroupuniqId t=$searchType searchValue=$searchValue res", [$sql, $mgroupid]);
            return $mgroupid;
        }

        $sql = $db->prepare("SELECT mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name like ? order by mgroupid desc limit 1");
        $sql->execute(["%$searchValue%"]);
        $sqlRes = $sql->fetch();
        logs::log("getmgroupuniqId t=$searchType searchValue=$searchValue (1)", [$sql,  $sqlRes]);

        $sql1 = $db->prepare("SELECT mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
        $sql1->execute([$rparentValue]);
        $sql1Res = $sql1->fetch();
        logs::log("getmgroupuniqId t=$searchType searchValue=$searchValue (2)", [$sql1,  $sql1Res]);
        if($sql->rowCount() > 0){
            $mgroupid = $sqlRes['mgroupuniq'];
        }else{
            $mgroupid = '';
        }
        if($sql1->rowCount() > 0){
            $mgroupidParent = $sql1Res['mgroupuniq'];
        }else{
            $mgroupidParent = '';
        }

        logs::log("getmgroupuniqId res", [$mgroupid, $mgroupidParent]);

        if ($mgroupid == "") {
            return $mgroupidParent;
        }
        return $mgroupid;
    }
}
