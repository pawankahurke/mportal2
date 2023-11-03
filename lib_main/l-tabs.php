<?php



function table_header()
{
    return "<table border=\"2\" align=\"left\" cellspacing=\"2\" cellpadding=\"2\">\n";
}

function table_data($args, $head)
{
    $txt = '';
    $td  = ($head) ? 'th' : 'td';
    if ($args) {
        $txt .= "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            $txt .=  "<$td>$data</$td>\n";
        }
        $txt .= "</tr>\n";
    }
    return $txt;
}

function table_footer()
{
    return "</table>\n<br clear=\"all\">\n<br>\n";
}

function clear_all()
{
    return "<br clear=\"all\">\n";
}

function double($name, $value)
{
    return <<< HERE

        <tr>
            <td align="right">
                $name
            </td>
            <td align="left">
                $value
            </td>
        </tr>

HERE;
}

function pretty_header($name, $width)
{
    return <<< HERE

        <tr>
            <th colspan="$width" bgcolor="#333399">
                <font color="white">
                    $name
                </font>
            </th>
        </tr>

HERE;
}

function disp($row, $name)
{
    $text = $row[$name];
    return ($text == '') ? '<br>' : $text;
}

function disp_one($name)
{
    return ($name == '') ? '<br>' : $name;
}
