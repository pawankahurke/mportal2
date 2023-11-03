<?php

/* listvnc.php - lists all applicable UltraVNC connection identifiers that
    an user has access to. */

/*
Revision history:

Date        Who     What
----        ---     ----
29-Mar-06   BTE     Original creation.
21-Jun-06   BTE     Bug 3466: The primary census server page is no longer in
                    order.
24-Jul-06   BTE     Bug 3551: New help file and text change.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.

*/

$title = 'UltraVNC Connection Identifiers';

ob_start();
include('../lib/l-cnst.php');
include('../lib/l-db.php');
include('../lib/l-util.php');
include('../lib/l-head.php');
include('../lib/l-serv.php');
include('../lib/l-sql.php');
include('../lib/l-user.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');

/* Perform authentication */
$db       = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$user     = user_data($authuser, $db);

$msg  = ob_get_contents();
ob_end_clean();

echo standard_html_header($title, $comp, $authuser, '', 0, 0, $db);

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

/* Retrieve connection identifiers that this user has access to */
$div = constIDMax - constIDMin + 1;
$sql = "SELECT Census.site AS site, Census.host AS host, "
    . "MOD((CONV(SUBSTRING(Census.uuid, 31), 16, 10)), $div) + "
    . constIDMin . " AS connid FROM Census LEFT JOIN Customers ON ("
    . "Census.site=Customers.customer) WHERE Customers.customer IS NOT "
    . "NULL AND Customers.username='" . $user['username'] . "' AND "
    . "Census.uuid!='' ORDER BY CONVERT(Census.site USING latin1), "
    . "CONVERT(Census.host USING latin1)";

$rows = find_many($sql, $db);
if ($rows) {
    reset($rows);
    $first = 1;
    foreach ($rows as $key => $row) {
        $titlesite = 0;
        if ($first) {
            $titlesite = 1;
        } else if (!(strcmp($last, $row['site']) == 0)) {

            $titlesite = 1;
        }
        $last = $row['site'];

        if ($titlesite) {
            if (!($first)) {
                echo "</table><p>";
            }
            echo "<span class=blue><font size=3><b>Site " . $row['site']
                . "</b></font></span>";
            echo "<p><table><tr><th>Machine&nbsp;&nbsp;&nbsp;&nbsp;"
                . "&nbsp;</th><th>Connection ID</th></tr>";
        }
        $first = 0;

        echo "<tr><td>" . $row['host'] . "</td>"
            . "<td>" . $row['connid'] . "</td></tr>";
    }
    echo "</table><p>";
} else {
    echo "The user does not have access to any machines, or there are no "
        . "machines available.";
}

echo head_standard_html_footer($authuser, $db);
