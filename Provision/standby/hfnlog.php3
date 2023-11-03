<?php

/*
Revision history:

Date        Who     What
----        ---     ----
15-Jun-05   BTE     Original creation (based on rpc/rpc.php).
12-Nov-05   BTE     Replaced rpc/errs.php with lib/l-errs.php.

*/

/* This just has stub procedures from rpc.php, all requests get 
    constErrServerTooBusy. */

    include ( '../../lib/l-rpcs.php'  );
    include ( '../../lib/l-errs.php'    );
    include ( '../../rpc/server.php'  );

    $usec = microtime();

    $procName = posted('ProcName');

    $args = build_args($usec, $procName);

    $args['rval'] = constErrServerTooBusy;
    $args['oxml'] = 1;

    $args['xml'] = hpc_return($args);
    echo $args['xml'];

/* Don't delete the newline from the end of this file */
?>


