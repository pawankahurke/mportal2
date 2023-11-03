<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
10-Oct-02   EWB     Created.
16-Dec-02   EWB     Align query table results at top
12-Feb-03   EWB     Added query name to empty terms message.
22-May-03   EWB     Quote Crusade.
15-Nov-04   EWB     Resolved conflicts with l-tabs.php
14-Dec-05   BJS     Added QTBL_table_heading().
*/

/*
    |  Trying to keep columns small.
    */

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


/*
    | $list = an array of items to use as the heading
    | for a table. The list can be of any size, 
    | but must be in the following form to work;
    |
    | array( 0 => 'Action',
    |        1 => 'Site Name',
    |        2 => 'Machine Name' 
    |        N => '' );
   */
function QTBL_table_heading($list)
{
    $tbl = '<tr>';
    reset($list);
    foreach ($list as $key => $txt) {
        $tbl .= "<td>$txt</td>";
    }
    return $tbl . '</tr>';
}
