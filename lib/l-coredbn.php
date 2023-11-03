<?php



define('constDBNVarVersions', 0);
define('constDBNVarValues', 1);
define('constDBNValueMap', 2);
define('constDBNScrips', 3);
define('constVariables', 4);

function get_query($code, $row, $importvers)
{
    $q = null;
    switch ($code) {
        case constDBNVarValues:
            $q  = "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.DBN_VarValues (mgroupuniq, mcatuniq, varuniq, varscopuniq, "
                . "varnameuniq, valu, revl, def, revldef, clientconf, revlclientconf, last, "
                . "host, scop, name, seminit, cksum) VALUES ("
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?)";
            return (int)NanoDB::query($q, [
                $row['mgroupuniq'],
                $row['mcatuniq'],
                $row['varuniq'],
                $row['varscopuniq'],
                $row['varnameuniq'],
                $row['valu'],
                $row['revl'],
                $row['def'],
                $row['revldef'],
                $row['clientconf'],
                $row['revlclientconf'],
                $row['last'],
                $row['host'],
                $row['scop'],
                $row['name'],
                $row['seminit'],
                $importvers
            ]);

        case constDBNVarVersions:
            $q = "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.DBN_VarVersions (vers, varuniq, varscopuniq, varnameuniq, "
                . "descval, pwsc, dngr, defval, config, configorder, grpany, grpall, grpuser, "
                . "grpsite, grpmach, varversuniq, defmap, cksum) VALUES ("
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?)";
            return (int)NanoDB::query($q, [
                $row['vers'],
                $row['varuniq'],
                $row['varscopuniq'],
                $row['varnameuniq'],
                $row['descval'],
                $row['pwsc'],
                $row['dngr'],
                utf8_encode($row['defval']),

                $row['config'],
                $row['configorder'],
                $row['grpany'],
                $row['grpall'],

                $row['grpuser'],
                $row['grpsite'],
                $row['grpmach'],

                $row['varversuniq'],
                $row['defmap'],
                $importvers
            ]);
        case constDBNValueMap:
            $q = "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.DBN_ValueMap (censusuniq, censussiteuniq, mgroupuniq, "
                . "mcatuniq, varuniq, varscopuniq, varnameuniq, stat, srev, revl, last, "
                . "expire, oldmgroupuniq, oldvalu, source, cksum) VALUES ("
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?,"
                . "?)";

            return (int)NanoDB::query($q, [
                $row['censusuniq'],
                $row['censussiteuniq'],
                $row['mgroupuniq'],
                $row['mcatuniq'],
                $row['varuniq'],
                $row['varscopuniq'],
                $row['varnameuniq'],
                $row['stat'],
                $row['srev'],
                $row['revl'],
                $row['last'],
                $row['expire'],
                $row['oldmgroupuniq'],
                $row['oldvalu'],
                $row['source'],
                $importvers
            ]);
        case constDBNScrips:
            $q = "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.DBN_Scrips (num, name, vers, cksum) VALUES ("
                . "?,"
                . "?,"
                . "?,"
                . "?)";
            return (int)NanoDB::query($q, [
                $row['num'],
                $row['name'],
                $row['vers'],
                $importvers
            ]);
        case constVariables:
            $q = "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.Variables (name, scop, itype, varuniq, varscopuniq, "
                . "varnameuniq) VALUES ("
                . "?,"
                . '?,'
                . '?,'
                . "?,"
                . "?,"
                . "?)";
            return (int)NanoDB::query($q, [
                $row['name'],
                $row['scop'],
                $row['itype'],
                $row['varuniq'],
                $row['varscopuniq'],
                $row['varnameuniq']
            ]);
    }
}

function process_vars($pdo, $dbsqlite)
{
    $query = $dbsqlite->query('SELECT * FROM Variables');
    if (!$query) {
        echo "Failed to select from Variables: " . $dbsqlite->lastErrorMsg();
        return;
    }

    $countInserts = 0;
    $countFailed = 0;
    while ($row = $query->fetchArray()) {
        $count = get_query(constVariables, $row, '');
        $countInserts += $count;
    }

    return $countFailed;
}

function process_table($sqlitequery, $code, &$verboseoutput, $tblname, $pdo, $dbsqlite, $importvers)
{
    logs::log("process_table:", ["sqlitequery" => $sqlitequery, "code" => $code, "tblname" => $tblname]);
    $query = $dbsqlite->query($sqlitequery);
    if (!$query) {
        echo "Failed to select from $tblname: " . $dbsqlite->lastErrorMsg();
        return;
    }

    $countInserts = 0;
    $countFailed = 0;
    while ($row = $query->fetchArray()) {
        $count = get_query($code, $row, $importvers);
        $countInserts += $count;
        $vout = "<br/>" . $sqlitequery . "<br/>";
        if ($count == 0) {
            $countFailed++;
        }
        $verboseoutput[] = $vout;
    }
    return $countFailed;
}

function do_cleanup($importvers, $verboseoutput, $pdo)
{

    $stmt1 = $pdo->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "core.DBN_Versions WHERE vers = ?");
    $stmt1->execute([$importvers]);

    $stmt2 = $pdo->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "core.DBN_VarVersions WHERE cksum = ?");
    $stmt2->execute([$importvers]);

    $stmt3 = $pdo->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "core.DBN_VarValues WHERE cksum = ?");
    $stmt3->execute([$importvers]);

    $stmt4 = $pdo->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "core.DBN_ValueMap WHERE cksum = ?");
    $stmt4->execute([$importvers]);

    $stmt5 = $pdo->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "core.DBN_Scrips WHERE cksum = ?");
    $stmt5->execute([$importvers]);

    foreach ($verboseoutput as $line) {
        echo $line;
    }
}
