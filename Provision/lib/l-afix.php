<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
25-Jun-03   EWB     Created.
*/

   /*
    |  Repair a damaged asset upload.
    */

    function fix_machine($mid,$smax,$cmax,$prov,$db)
    {
        if (($smax > 0) && ($prov > 0) && ($mid > 0))
        {
            // remove all the records that just got added.
            $sql  = "delete from AssetData\n";
            $sql .= " where machineid = $mid and\n";
            $sql .= " $prov <= sobserved";
            $res  = redcommand($sql,$db);

            if ($res)
            {
                // undo the time extends
                $sql  = "update AssetData set\n";
                $sql .= " slatest = $smax,\n";
                $sql .= " clatest = $cmax\n";
                $sql .= " where $prov <= slatest and\n";
                $sql .= " machineid = $mid ";
                $res  = redcommand($sql,$db);
            }

            if ($res)
            {
                // and clear the flag.
                $sql  = "update Machine set\n";
                $sql .= " provisional = 0\n";
                $sql .= " where machineid = $mid";
                $res  = redcommand($sql,$db);
            }
        }

        // if there was an error during machine introduction, 
        // then the only possible fix is to remove the machine.

        if ((0 < $mid) && ($smax == 0) && (0 < $prov))
        {
            $sql  = "delete from AssetData\n";
            $sql .= " where machineid = $mid";
            $res  = redcommand($sql,$db);

            if ($res)
            {
                $sql  = "delete from Machine\n";
                $sql .= " where machineid = $mid";
                $res  = redcommand($sql,$db);
            }
        }
    }


?>
