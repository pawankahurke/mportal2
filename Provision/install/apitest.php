<?php

include_once ( '../lib/l-perf.php'  );
include_once ( '../lib/l-cnst.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-rpcs.php'  );
include_once ( '../lib/l-rlib.php'  );
include_once ( '../lib/l-errs.php'  );
include_once ( '../lib/l-dsyn.php'  );
include_once ( '../lib/l-gcfg.php'  );
include_once ( '../lib/l-pcnt.php'  );
include_once ( '../lib/l-core.php'  );
include_once ( '../lib/l-gdrt.php'  );
include_once ( '../lib/l-grps.php'  );
include_once ( '../lib/l-pdrt.php'  );
include_once ( '../lib/l-svbt.php'  );
include_once ( '../rpc/server.php'  );
include_once ( '../rpc/census.php'  );
include_once ( '../rpc/event.php'   );
include_once ( '../rpc/publish.php' );
include_once ( '../rpc/asset.php'   );
include_once ( '../rpc/time.php'    );
include_once ( '../rpc/exec.php'    );
include_once ( '../rpc/install.php' );
include_once ( '../rpc/provis.php'  );
include_once ( '../rpc/inst.php'    );

//echo '<pre>';

$siteEmailId    = url::requestToAny('siteemailid');
$cSiteName = url::requestToAny('sitename');

$db = db_code('db_ins');
if ($db) {
    
    $result = SVBT_checkMaxInstallCountNew($siteEmailId, $cSiteName, $db);
    
    $iType = $result['iType'];
    $iResp = $result['iResp'];
    
    if($iResp === 'SUBSCRIBE') {
        echo 'Subscribe';
    } else if($iResp === 'EXCEED') {
        echo 'Exceeds';
    } else {
        echo 'Proceed Installation!';
    }
}