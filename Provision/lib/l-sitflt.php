<?php

/*
Revision history:

Date        Who     What
----        ---     ----
25-Apr-03   NL      Initial creation.
26-Apr-03   NL      Modify fncts cuz auxtables list all sites, with new filter column.
                    Pass $sitefilter, not $sitesCHECKED to update_obj_sitefilter().
                    Create checkboxes2sitefilter();
29-Apr-03   NL      show_checkboxlist renamed show_sitefilterlist
                    add sitefilter setting to show_sitefilterlist
                    add objecttype to show_sitefilterlist to diplay filtertype to user.
                    get_admin_filtersetting() ; rearrange functions;
                    rename update_obj_filtersites to update_obj_filtersetting;
29-Apr-03   NL      Oops. Correct type var in show_sitefilterlist
29-Apr-03   NL      Oops. more typoes in setting the type variable
29-Apr-03   NL      Remove $autheruser param from get_obj_filtersetting
30-Apr-03   NL      Rename get_admin_filtersetting --> get_user_filtersetting;
                    Rename get_admin_sitefilter --> get_user_sitefilter;
 5-May-03   NL      When calculating objfilter, for new sites (in userfilter), set
                        $filter to 0, but if no aux records exist, use user sitefilter
 5-May-03   NL      Add columns to show_sitefilterlist
 5-May-03   NL      Make sure $cols is between 1 and 10.
11-Jul-03   EWB     Removed extra newline from insert statement.
17-Jul-03   EWB     Removed extra newline from delete statement.
 8-Oct-03   EWB     Always show all the sites.
09-Nov-05   BJS     Removed event.RptSiteFilters & notifications.NotSiteFilters.
05-Dec-05   BJS     Removed get_obj_filtersetting/filtersite(), get_obj_vars(),
                    update_obj_filtersetting/filtersite().
*/


function JS_site_popup()
{
?>
    <script language="javascript">
        // <!--
        function OpenWindow(type, url) {
            window.name = 'main';

            if (type == "site_popup") {
                var winname = "site_popup";
                var winWidth = 300;
                var winHeight = 400;
            }

            var winLeft = screen.width - winWidth - 20;
            var winTop = 0;
            if (winLeft < 0) {
                winLeft = 0
            }
            if (winTop < 0) {
                winTop = 0
            }

            var win = window.open(url, winname,
                "Location=no,menubar=no,resizable=no,scrollbars=yes,height=" +
                winHeight + ",width=" + winWidth + ",screenX=" + winLeft +
                ",screenY=" + winTop + ",left=" + winLeft + ",top=" + winTop);
            win.focus();

        }
        // -->
    </script>

<?php

}

function get_user_filtersetting($authuser, $db)
{
    $filtersites = 0;
    if ($authuser) {
        $current_db_name = get_db_name($db);
        if ($current_db_name != 'core')
            db_change($GLOBALS['PREFIX'] . 'core', $db);

        $sql = "SELECT filtersites FROM Users WHERE username = '$authuser' ";
        $res = redcommand($sql, $db);

        if ($res) {
            if (mysqli_num_rows($res) == 1) {
                $row = mysqli_fetch_assoc($res);
                $filtersites = $row['filtersites'];
            }
        }

        if ($current_db_name != 'core')
            db_change($current_db_name, $db);
    }
    return $filtersites;
}

function JS_CheckUncheckAll()
{
?>
    <SCRIPT LANGUAGE="JavaScript">
        function CheckUncheckAll(all_setting) {
            var check;
            if (all_setting == "check")
                check = true;
            else if (all_setting == "uncheck")
                check = false;

            var checkboxes = window.document.myForm.sitesCHECKED;
            for (i = 0; i < checkboxes.length; i++)
                checkboxes[i].checked = check;
            return true;
        }
    </script>
<?php
}


function HTML_CheckUncheckAll($cols)
{
    $self = server_var('PHP_SELF');

    $querystring = server_var('QUERY_STRING');
    $querystring = preg_replace("/all_setting=(check|uncheck)&?/", "", $querystring);
    if (strlen($querystring))
        $querystring = '&' . $querystring;

    $span = $cols * 3;

    $ha = "$self?all_setting=check$querystring";
    $hn = "$self?all_setting=uncheck$querystring";
    $ja = "CheckUncheckAll('check');return false;";
    $jn = "CheckUncheckAll('uncheck');return false;";
    echo <<< HERE

<tr>
  <td colspan="$span">
    [<a href="$ha" onClick="$ja">check all</a> |
     <a href="$hn" onClick="$jn">uncheck all</a>]
  </td>
</tr>

HERE;
}


function get_user_sitefilter($authuser, $db)
{
    $current_db_name = get_db_name($db);
    if ($current_db_name != 'core')
        db_change($GLOBALS['PREFIX'] . 'core', $db);

    $user_sitefilter = array();
    $sql = "SELECT * FROM Customers\n WHERE username = '$authuser'\n ORDER BY customer";
    $res  = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_assoc($res)) {
                $user_sitefilter[$row['customer']] = $row['sitefilter'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    if ($current_db_name != 'core')
        db_change($current_db_name, $db);

    return $user_sitefilter;
}


function show_sitefilter_radio($filtersites, $db)
{
    $chk = ' checked';
    $on  = ($filtersites) ? $chk : '';
    $off = ($filtersites) ? ''   : $chk;

    echo "<input type=\"radio\" name=\"filtersites\" value=\"1\"$on>On ";
    echo "<input type=\"radio\" name=\"filtersites\" value=\"0\"$off>Off";
}


function show_sitefilterlist($objecttype, $sitefilter, $all_setting, $cols, $db)
{
    if (($cols < 1) || ($cols > 10)) $cols = 1;
    JS_CheckUncheckAll();
    table_header();
    HTML_CheckUncheckAll($cols);
    $count = safe_count($sitefilter);
    if ($count > 0) {
        $data = array();
        $cols = ($count > 2 * $cols) ? $cols : 1;
        if ($cols > 1) {
            $div  = intval($count / $cols);
            $rows = (($count % $cols) == 0) ? $div : $div + 1;
        } else {
            $rows = $count;
        }

        //      debug_note("count:$count, rows:$rows, cols:$cols");
        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < $cols; $col++) {
                $data[$row][$col]['site'] = '';
                $data[$row][$col]['filt'] = 0;
            }
        }

        $i = 0;
        reset($sitefilter);
        foreach ($sitefilter as $site => $filter) {
            $row = intval($i % $rows);
            $col = intval($i / $rows);
            $data[$row][$col]['site'] = $site;
            $data[$row][$col]['filt'] = $filter;
            $i++;
        }

        $chk = ' checked';
        for ($row = 0; $row < $rows; $row++) {
            $args = array();
            for ($col = 0; $col < $cols; $col++) {
                $site = $data[$row][$col]['site'];
                $filt = $data[$row][$col]['filt'];
                if ($site) {
                    if ($all_setting == 'check')
                        $checked = $chk;
                    elseif ($all_setting == 'uncheck')
                        $checked = '';
                    else
                        $checked = ($filt) ? $chk : '';

                    $hide   = "\n<input type=\"hidden\" name=\"sites[]\" value=\"$site\">\n";
                    $cbox   = "<input type=\"checkbox\" name=\"sitesCHECKED[]\" id=\"sitesCHECKED\" value=\"$site\"$checked>\n";
                    $args[] = $hide . $cbox;
                    $args[] = $site;
                    $args[] = '&nbsp;&nbsp;';
                } else {
                    $args[] = '<br>';
                    $args[] = '<br>';
                    $args[] = '<br>';
                }
            }
            table_data($args, 0);
        }
    }
    HTML_CheckUncheckAll($cols);
    table_footer();
}

function checkboxes2sitefilter($sites, $sitesCHECKED)
{
    $sitefilter = array();

    reset($sites);
    foreach ($sites as $key => $site) {
        $filter = (in_array($site, $sitesCHECKED)) ? 1 : 0;
        $sitefilter[$site] = $filter;
    }

    return $sitefilter;
}

?>