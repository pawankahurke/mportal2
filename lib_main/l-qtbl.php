<?php





function tdate($utime)
{
    $date = date('j-M-Y', $utime);
    $time = date('H:i:s', $utime);
    return "$date<br>$time";
}

function table_start()
{
    $msg = "<table border=\"2\" align=\"left\" cellspacing=\"2\" cellpadding=\"4\">\n";
    return $msg;
}


function table_end()
{
    $msg  = "</table>\n";
    $msg .= "<br clear=\"all\">\n";
    return $msg;
}

function asset_header($msg)
{
    return "<h2>$msg</h2>\n";
}


function asset_data($args)
{
    $m = '';
    if ($args) {
        $m .= "<tr valign=\"top\">\n";
        reset($args);
        foreach ($args as $key => $data) {
            $m .= " <td>$data</td>\n";
        }
        $m .= "</tr>\n";
    }
    return $m;
}


function message($msg)
{
    return "<br><br><p>$msg</p>\n";
}

function empty_mids()
{
    return message("There were no machines found matching your selection criteria.");
}

function empty_terms($name)
{
    return message("There were no selection criteria found for query <b>$name</b>.");
}

function empty_dids()
{
    return message("There are no fields specified to be displayed.");
}

function missing_query()
{
    return message("There is no query specified.");
}

function empty_data()
{
    return message("There were no records found matching your selection criteria.");
}



function QTBL_table_heading($list)
{
    $tbl = '<tr>';
    reset($list);
    foreach ($list as $key => $txt) {
        $tbl .= "<td>$txt</td>";
    }
    return $tbl . '</tr>';
}
