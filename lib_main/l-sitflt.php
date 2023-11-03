<?php




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
        mysqli_free_result($res);
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