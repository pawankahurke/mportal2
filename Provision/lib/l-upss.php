<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
20-Feb-03   EWB     Created.
04-Jun-07   BTE     Added calls to PHP_REPF_UpdateDynamicList.
06-Jun-07   BTE     Added code to support SavedSearches.searchuniq.
27-Jun-07   BTE     Bug 4198: Fix searchuniq for global SavedSearches.
31-Jul-07   BTE     Universal unique function.

*/


  /*
   |  requires:
   |     l-db.php
   |     l-sql.php
   |     l-rcmd.php
   */


   /*
    |  create or update a saved search
    */

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
