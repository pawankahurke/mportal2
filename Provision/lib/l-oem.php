<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
 6-Dec-02   EWB     Created
*/

    
   /*
    |  We want this to work no matter where it is installed.
    |  In the usual case we install with the generic name 'main',
    |  and are calling from somewhere in level 2.
    |
    |    0: /index.php
    |    1: /main/index.php
    |    2: /main/cron/c-report.php3
    |
    |  In our usual case we'll call it with a value of 2,
    |  and it will return 'main'.
    */


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

    // http://www.php.net/manual/en/function.file-exists.php


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
