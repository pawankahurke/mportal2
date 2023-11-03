<?php

/* c-assign.php - Scrip Configuration Action cron */

/*
Revision history:

Date        Who     What
----        ---     ----
27-Apr-06   BTE     Original creation.
27-Apr-06   BTE     Don't use PHP authentication.
27-Apr-06   BTE     Only output if something changed.
01-May-06   BTE     Bug 3355: Scrip Config Status Page: Various Changes.
03-May-06   BTE     Bug 3362: Do general testing and bugfixing for Scrip config
                    status page.
24-Jun-06   BTE     Bug 3500: Config Wizards fail for 2.1 client.
20-Sep-06   BTE     Added l-tiny.php.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

    $title = 'Cron - Scrip Assignment Expiration';

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
include_once ( '../lib/l-gcfg.php'  );
include_once ( '../lib/l-grpw.php'  );
include_once ( '../lib/l-dsyn.php'  );
include_once ( '../lib/l-tiny.php'  );


    /* Perform authentication */
    $db       = db_connect();
    $comp = component_installed();

    $a   = array( );
    $a[] = html_link("index.php?act=wiz", 'wizards');
    $a[] = html_link("status.php", 'status');
    $a[] = html_link("status.php?level=1", 'advanced');
    $m   = join(' | ',$a);
    $nav = "<b>configuration:</b> $m\n<br><br>\n";
    $auth   = getenv("REMOTE_USER"); // cron does not use PHP auth'n


    echo standard_html_header($title,$comp,$auth,$nav,0,0,$db);

    /* This is very simple - if the time for the expiration date is in the
        past, revert the assignment. */
    $now = time();
    $sql = "SELECT valmapid FROM ValueMap WHERE expire<$now AND expire!=0";

    $num = 0;

    $set = DSYN_DeleteSet($sql, constDataSetGConfigValueMap, "valmapid",
        "c-assign", 1, 0, constOperationDelete, $db);

    if($set)
    {
        $sql = "UPDATE ValueMap LEFT JOIN MachineGroups ON (ValueMap."
            . "oldmgroupuniq=MachineGroups.mgroupuniq) SET ValueMap."
            . "mgroupuniq=oldmgroupuniq, oldmgroupuniq='', ValueMap.revl="
            . "ValueMap.revl+1, "
            . "ValueMap.mcatuniq=MachineGroups.mcatuniq WHERE (expire<$now AND"
            . " expire!=0 AND oldmgroupuniq!='')";

        $res = command($sql, $db);
        if($res)
        {
            $num += affected($res, $db);
        }

        $sql = "UPDATE VarValues LEFT JOIN ValueMap ON (VarValues.varuniq="
            . "ValueMap.varuniq AND VarValues.mgroupuniq=ValueMap.mgroupuniq)"
            . " SET VarValues.valu=ValueMap.oldvalu, VarValues.revl = "
            . "VarValues.revl + 1, ValueMap.oldvalu='', VarValues.revldef = "
            . "VarValues.revldef + VarValues.def, VarValues.def = 0 WHERE ("
            . "expire<$now AND expire!=0 AND oldvalu!='')";
        $res = command($sql, $db);
        if($res)
        {
            $num += affected($res, $db);
        }

        $sql = "UPDATE ValueMap SET expire=0, last=0, oldvalu='' WHERE "
            . "(expire<$now AND expire!=0)";
        $res = command($sql, $db);

        DSYN_UpdateSet($set, constDataSetGConfigValueMap, "valmapid", $db);
    }

    if($num > 0)
    {
        logs::log(__FILE__, __LINE__, "c-assign.php: $num ValueMap records were changed.",0);
    }

    echo head_standard_html_footer($auth,$db);
