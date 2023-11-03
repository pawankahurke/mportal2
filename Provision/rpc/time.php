<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
24-Sep-02   MMK     Original creation
 3-Oct-02   EWB     Don't need to log.
13-Nov-02   EWB     Better error codes
31-Oct-03   EWB     Pass by reference
*/

    /*  TIMR_GetNow
         Gets the current system time, puts it in the first item
         in the value array of the 'valu' member of $args. The
         time returned is the POSIX UCT (GMT) time.
    */

    function TIMR_GetNow(&$args)
    {
        $args['valu'][1] = time();
        $args['rval'] = constAppNoErr;
        $args['olog'] = 0;
        $args['oxml'] = 1;
    }

?>
