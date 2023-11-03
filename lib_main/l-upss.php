<?php




  


   

    function update_search($row,$db)
    {
        $qn = safe_addslashes(strval($row['name'])); 
        $qu = safe_addslashes(strval($row['username']));
        $qs = safe_addslashes(strval($row['searchstring']));

        $id       = $row['id'];
        $global   = $row['global'];
        $created  = @ intval($row['created']);
        $modified = @ intval($row['modified']);

        $cmd  = ($id)? 'update' : 'insert into';
        $sql  = "$cmd SavedSearches set\n";
        $sql .= " name = '$qn',\n";
        $sql .= " username = '$qu',\n";
        $sql .= " global = $global,\n";
        $sql .= " searchstring = '$qs',\n";
        $sql .= " created = $created,\n";
        $sql .= " modified = $modified";
        $searchuniq = USER_GenerateManagedUniq($row['name'], $row['username'],
            $db);
        $sql .= ",\n searchuniq = '$searchuniq'";
        if($id)
        {
            $sql .= "\n where id = $id";
        }
        $res = redcommand($sql,$db);
        PHP_REPF_UpdateDynamicList(CUR, constJavaListEventFilters);
        return $res;
    }
