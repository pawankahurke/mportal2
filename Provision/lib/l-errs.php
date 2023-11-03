<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
13-Nov-02   EWB     Created.
 2-Dec-02   EWB     constRevisionLevel 1413
28-May-03   MMK     Added constErrSiteNotFound.
30-Oct-03   EWB     Added constErrServerTooBusy.
24-Nov-03   EWB     Added constErrNotEncrypted.
17-Mar-05   EWB     Added census error codes
28-Mar-05   EWB     Real values
03-Sep-05   BTE     Moved from rpc/errs.php to lib/l-errs.php.
10-Nov-05   BTE     Added special server error codes.
14-Apr-06   BTE     Added constErrNoConfigVars.
04-Sep-07   BTE     Added constErrUniqueName.

*/

    
   /*
    |  These are error codes to use for RPC, and derived
    |  from defs/errs.h.
    |
    |  These should at some point become
    |  automatically generated.
    */

    define('constErrAssertFail',1);
    define('constAppNoErr',2);
    define('constErrNoConfigVars',347);
    define('constErrServerNoSupport',613);  
    define('constErrDatabaseNotAvailable',626);
    define('constErrSiteNotFound',705);
    define('constErrServerTooBusy',763);
    define('constErrNotEncrypted',768);
    define('constErrCensusUUID',888);
    define('constErrCensusName',889);
    define('constErrServChangeUUID',915);
    define('constErrServChangeName',916);
    define('constErrUniqueName',996);
    define('constLicenseExceeded',1229);

   /*
    |  This comes from $/defs/runt.h
    |
    |  The value returned by RUNT_GetRevision
    */

    define('constRevisionLevel',1413);
?>
