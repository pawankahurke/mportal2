<?php



    
   


    function oem_name($level)
    {
        $oem  = '';
        $self = $_SERVER['PHP_SELF'];
        $dirs = explode("/",$self);
        $deep = safe_count($dirs);
        if ($deep > $level)
        {
            $n = $deep - $level;
            for ($i = 1; $i < $n; $i++)
            {
                if ($oem) $oem .= "/";
                $oem .= $dirs[$i];
            }
        }
        return $oem;
    }

    

    function oem_installed($level)
    {
        $temp  = array( );
        $oem   = oem_name($level);
        $root  = $_SERVER['DOCUMENT_ROOT'];
        $asst  = "$root/$oem/asset/index.php";
        $evnt  = "$root/$oem/event/index.php";
        $cnfg  = "$root/$oem/config/index.php";
        $updt  = "$root/$oem/updates/index.php";
        $acct  = "$root/$oem/acct/admin.php";
        $help  = "$root/$oem/doc/index.php";
        $temp['asst'] = file_exists($asst);
        $temp['evnt'] = file_exists($evnt);
        $temp['cnfg'] = file_exists($cnfg);
        $temp['updt'] = file_exists($updt);
        $temp['acct'] = file_exists($acct);
        $temp['help'] = file_exists($help);
        return $temp;
    }

?>
