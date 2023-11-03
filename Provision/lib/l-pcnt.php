<?php

/*
Revision history:

Date        Who     What
----        ---     ----
08-Dec-05   AAM     Created from existing pieces, extended to do timing
22-May-06   AAM     Added byte counts to performance data.
26-Jan-07   BTE     Bug 4015: Add TX performance counters.

*/


/*
    |  If the variable doesn't exist yet, then shm_get_var will
    |  write a warning message and return false ... this means
    |  there really isn't a clean way to tell if the memory is
    |  initialized or not.
    |
    |  This means can't distinguish between disabled and uninitialized.
    |  The good news is that this doesn't really matter, since either
    |  way, we do NOT gather statistics.
    */

/* start_perf_count
        Record the start of a performance counter name $proc.  This returns the
        parameters that must be passed into finish_perf_count.
    */
function start_perf_count(
    $proc,
    &$wSec,
    &$wUsec,
    &$uSec,
    &$uUsec,
    &$sSec,
    &$sUsec,
    &$enabled
) {

    return null;
}


/* finish_perf_count
        Record the end of a performance counter name $proc.
    */
function finish_perf_count(
    $proc,
    $wSec,
    $wUsec,
    $uSec,
    $uUsec,
    $sSec,
    $sUsec,
    $enabled
) {
    return null;
}


/* finish_tx_bytes

        Appends the amount of transmit data for this RPC call to the
        performance counter for $proc.  Note that this ignores the returned
        $enabled from start_perf_count (this was necessary due to where this
        call has to be placed to capture the size of the output).
    */
function finish_tx_bytes($proc)
{
    return null;
}
