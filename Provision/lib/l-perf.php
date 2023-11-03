<?php

/*
Revision history:

Date        Who     What
----        ---     ----
30-Nov-03   AAM     Created.
 2-Dec-03   EWB     Changed from DOS format.
*/

    /* Server performance debug options.  These are here rather than in
        the server option page so that they can be used without a database
        operation.  They are in this file because this is a small file that
        is easy to put unencoded onto a customer server, and it is included
        by everything. */

    define('pfDisableAsset', false);    /* Disable asset logs completely */
    define('pfDisableConf', false);     /* Disable VARS_CheckSync and VARS_ApplyPackage */
    define('pfTimeRPCs', false);        /* Timing for non-event RPCs */
    define('pfTimeIntQ', false);        /* Timing for interactive event queries */
    define('pfTimeRept', false);        /* Timing for event reports */
    define('pfTimeNotf', false);        /* Timing for event notifications */
    define('pfTimeDrep', false);        /* Detail timing for event reports */
    define('pfTimeDnot', false);        /* Detail tinimg for event notifications */

?>
