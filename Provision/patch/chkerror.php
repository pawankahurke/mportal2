<?php

/* csrv.php: A wrapper around the csrv.cgi program. */

/*
Revision history:

Date        Who     What
----        ---     ----
09-Oct-06   BTE     Original creation.
21-Oct-06   BTE     Use new footer code.

*/

    $title = 'Microsoft Update Management - Error Code Details';

include_once ( '../lib/l-cnst.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-errs.php'  );
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-gsql.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-tiny.php'  );
include_once ( '../lib/l-rlib.php'  );
include_once ( 'local.php'          );

    /* Perform authentication */
    $db       = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();
    $nav  = patch_navigate($comp);

    echo standard_html_header($title,$comp,$authuser,$nav,0,0,$db);

    $deccode = get_string('deccode','');

    db_change($GLOBALS['PREFIX'].'softinst',$db);

    $sql = "SELECT hexcode, deccode, strcode, textcode FROM ErrorCodes "
        . "WHERE deccode=$deccode";
    $row = find_one($sql, $db);
    if($row)
    {
        echo "<b>Hexadecimal code:</b> " . $row['hexcode'] . "<br>";
        echo "<b>Decimal code:</b> " . $row['deccode'] . "<br>";
        echo "<b>String identifier:</b> " . $row['strcode'] . "<br>";
        echo "<b>Description:</b> " . $row['textcode'] . "<br>";
    }
    else
    {
        echo "No details are available for error code $deccode";
    }
    echo head_standard_html_footer($authuser,$db);

?>
