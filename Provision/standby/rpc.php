<?php

/*
Revision history:

Date        Who     What
----        ---     ----
15-Jun-05   BTE     Original creation (based on rpc/rpc.php).
06-Jul-05   BTE     Go one directory deeper for standby mode.
12-Nov-05   BTE     Replaced rpc/errs.php with lib/l-errs.php.
11-Aug-06   BTE     Bug 3597: Undefined constants in standby mode.

*/

/* This just has stub procedures from rpc.php, all requests get 
    constErrServerTooBusy. */

include_once ( '../../../lib/l-rpcs.php'  );
include_once ( '../../../lib/l-errs.php'  );
include_once ( '../../../lib/l-cnst.php'  );
include_once ( '../../../rpc/server.php'  );

    $usec = microtime();

    $procName = posted('ProcName');

    $args = build_args($usec, $procName);

    $args['rval'] = constErrServerTooBusy;
    $args['oxml'] = 1;

    $args['xml'] = hpc_return($args);
    echo $args['xml'];

/* Don't delete the newline from the end of this file */
?>


