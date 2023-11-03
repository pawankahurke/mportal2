<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
29-Sep-03   EWB     Created.
*/


function defined_value($row, $field)
{
    $valu = (isset($row[$field])) ? $row[$field] : '';
    return $valu;
}


/*
    |  describe the specified table.
    |
    |  there has to be at least one field found.
    |
    */

function describe_table($dbase, $table, $db)
{
    $desc = array();
    $sql = "show columns from $dbase.$table";
    $tmp = find_many($sql, $db);
    if ($tmp) {
        $name = array();
        reset($tmp);
        foreach ($tmp as $key => $row) {
            $fld = defined_value($row, 'Field');
            if ($fld) {
                $type[$fld] = defined_value($row, 'Type');
                $null[$fld] = defined_value($row, 'Null');
                $keys[$fld] = defined_value($row, 'Key');
                $defs[$fld] = defined_value($row, 'Default');
                $extr[$fld] = defined_value($row, 'Extra');
                $name[]     = $fld;
            }
        }
        if ($name) {
            $desc['name'] = $name;
            $desc['type'] = $type;
            $desc['null'] = $null;
            $desc['keys'] = $keys;
            $desc['defs'] = $defs;
            $desc['extr'] = $extr;
        }
    }

    return $desc;
}
