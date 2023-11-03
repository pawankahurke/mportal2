<?php



    

    
    function SITE_GetTableSQL($tableName, $clause, $db) {

        $cols = array();
        $where = 'WHERE ';
        switch($tableName) {
            case 'VarValues':
                $cols = array('mgroupuniq', 'mcatuniq', 'varuniq', 'varscopuniq', 'varnameuniq', 'valu', 'revl', 'def', 'revldef', 'clientconf',
                    'revlclientconf', 'last', 'host', 'scop', 'name', 'seminit');
                $where .= '(varscopuniq NOT IN (md5(43), md5(190), md5(223), md5(253), md5(266))) AND ';
                break;
        }

        $sql = 'SELECT JSON_OBJECT(';
        $first = true;
        foreach($cols as $col) {
            if(!$first) {
                $sql .= ", ";
            }
            $sql .= "'" . $col . "', " . $col;
            $first = false;
        }
        $sql .= ') FROM ' . $tableName . ' ' . $where . $clause;

        return $sql;
    }

    function SITE_GetTable($tableName, $param, $db) {

        $sql = SITE_GetTableSQL($tableName, 'mgroupuniq=?', $db);
        $stmt = mysqli_prepare($db, $sql);
        $stmt->bind_param('s', $param);
        if(!$stmt->execute()) {
            logs::log(__FILE__, __LINE__, "Unable to select table data from SITE export: " . mysqli_error($db), 0);
            $stmt->close();
            return null;
        }

        if(!$stmt->store_result()) {
            logs::log(__FILE__, __LINE__, "Unable to store result from SITE export: " . mysqli_error($db), 0);
            $stmt->close();
            return null;
        }

        $stmt->bind_result($json);
        $result = '[';
        $first = true;
        while($row = $stmt->fetch()) {
            if(!$first) {
                $result .= ',';
            }
            $result .= $json;
            $first = false;
        }
        $result .= ']';

        $stmt->free_result();
        $stmt->close();

        return $result;
    }

    function SITE_ProcessVarValues($mgroupuniq, $varValueJson, $db) {

        $sql = "SELECT mcatuniq FROM MachineGroups WHERE mgroupuniq=?";
        $stmt = mysqli_prepare($db, $sql);
        $stmt->bind_param('s', $mgroupuniq);
        if(!$stmt->execute()) {
            logs::log(__FILE__, __LINE__, "Unable to select table data for machine group: " . mysqli_error($db), 0);
            $stmt->close();
            return null;
        }
        if(!$stmt->store_result()) {
            logs::log(__FILE__, __LINE__, "Unable to store result for machine group: " . mysqli_error($db), 0);
            $stmt->close();
            return null;
        }
        if($stmt->num_rows()!=1) {
            logs::log(__FILE__, __LINE__, "Unable to find machine group: " . $mgroupuniq, 0);
            $stmt->close();
            $stmt->free_result();
            return null;
        }
        $stmt->bind_result($mcatuniq);
        $stmt->fetch();
        $stmt->free_result();
        $stmt->close();

        $count = 0;

        foreach($varValueJson as $var) {
            $sql = "SELECT valueid FROM VarValues WHERE varuniq=? AND mgroupuniq=?";
            $stmt = mysqli_prepare($db, $sql);
            $stmt->bind_param('ss', $var->varuniq, $mgroupuniq);
            if(!$stmt->execute()) {
                logs::log(__FILE__, __LINE__, "Unable to select variable value data: " . mysqli_error($db), 0);
                $stmt->close();
                return null;
            }
            if(!$stmt->store_result()) {
                logs::log(__FILE__, __LINE__, "Unable to store result for variable value: " . mysqli_error($db), 0);
                $stmt->close();
                return null;
            }
            $numrows = $stmt->num_rows();
            $stmt->free_result();
            $stmt->close();
            if($numrows!=1) {
                $sql = "INSERT INTO VarValues (mgroupuniq,mcatuniq,varuniq,varscopuniq,varnameuniq,valu,revl,def,revldef,"
                    . "clientconf,revlclientconf,last,host,scop,name,seminit,lastchange) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,UNIX_TIMESTAMP())";
                $stmt = mysqli_prepare($db, $sql);
                $stmt->bind_param('ssssssddddddsdsd', $mgroupuniq, $mcatuniq, $var->varuniq, $var->varscopuniq, $var->varnameuniq,
                    $var->valu, $var->revl, $var->def, $var->revldef, $var->clientconf, $var->revlclientconf, $var->last,
                    $var->host, $var->scop, $var->name, $var->seminit);
                if(!$stmt->execute()) {
                    logs::log(__FILE__, __LINE__, "Unable to insert variable value data: " . mysqli_error($db) . ' ' . $stmt->error, 0);
                    $stmt->close();
                    return null;
                }
                $stmt->close();
                $count++;
            }
            else {
                $sql = "UPDATE VarValues SET mcatuniq=?,varscopuniq=?,varnameuniq=?,valu=?,revl=?,def=?,revldef=?,"
                    . "clientconf=?,revlclientconf=?,last=?,host=?,scop=?,name=?,seminit=?,lastchange=UNIX_TIMESTAMP()"
                    . " WHERE mgroupuniq=? AND varuniq=?";
                $stmt = mysqli_prepare($db, $sql);
                $stmt->bind_param('ssssddddddsdsdss', $mcatuniq, $var->varscopuniq, $var->varnameuniq,
                    $var->valu, $var->revl, $var->def, $var->revldef, $var->clientconf, $var->revlclientconf, $var->last,
                    $var->host, $var->scop, $var->name, $var->seminit, $mgroupuniq, $var->varuniq);
                if(!$stmt->execute()) {
                    logs::log(__FILE__, __LINE__, "Unable to insert variable value data: " . mysqli_error($db) . ' ' . $stmt->error, 0);
                    $stmt->close();
                    return null;
                }
                $stmt->close();
                $count++;
            }
        }

        return $count;
    }

    
    function SITE_Export($srcGroup, $db) {

        $res = '{"VarValues": ' . SITE_GetTable('VarValues', $srcGroup, $db) . '}';
        return $res;
    }

    function SITE_Import($destGroup, $srcJson, $db) {

        $json = safe_json_decode($srcJson);
        $count = SITE_ProcessVarValues($destGroup, $json->VarValues, $db);
        return $count;
    }
