<?php



   

    function fix_machine($mid,$smax,$cmax,$prov,$db)
    {
        if (($smax > 0) && ($prov > 0) && ($mid > 0))
        {
                        $sql  = "delete from AssetData\n";
            $sql .= " where machineid = $mid and\n";
            $sql .= " $prov <= sobserved";
            $res  = redcommand($sql,$db);

            if ($res)
            {
                                $sql  = "update AssetData set\n";
                $sql .= " slatest = $smax,\n";
                $sql .= " clatest = $cmax\n";
                $sql .= " where $prov <= slatest and\n";
                $sql .= " machineid = $mid ";
                $res  = redcommand($sql,$db);
            }

            if ($res)
            {
                                $sql  = "update Machine set\n";
                $sql .= " provisional = 0\n";
                $sql .= " where machineid = $mid";
                $res  = redcommand($sql,$db);
            }
        }

                
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
