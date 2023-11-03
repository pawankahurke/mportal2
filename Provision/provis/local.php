<?php

/*
Revision history:

Date        Who     What
----        ---     ----
29-Oct-03   EWB     Created.
 9-Dec-03   EWB     Added "Special" variable names.
11-Dec-03   EWB     Renamed "special" variable names.
17-Dec-03   EWB     Don't need submenu any more.
29-Dec-03   EWB     Added constVendorUser
25-Mar-04   EWB     Documentation for date formats.
*/

function provis_navigate()
{
    $txt   = '';
    //     $lnk   = array( );
    //     $lnk[] = html_link('product.php','products');
    //     $lnk[] = html_link('sites.php','sites');
    //     $lnk[] = html_link('meter.php','metering');
    //     $lnk[] = html_link('audit.php','audit');
    //     $msg = implode(" | \n",$lnk);
    //     $txt = "<b>provisioning:</b> $msg<br><br>\n";
    return $txt;
}

function post_self()
{
    $self  = server_var('PHP_SELF');
    return "<form method=\"post\" action=\"$self\">\n\n";
}

function form_footer()
{
    return "\n\n</form>\n\n";
}

function hidden($name, $value)
{
    return "<input type=\"hidden\" name=\"$name\" value=\"$value\">\n\n";
}

function checkbox($name, $checked)
{
    $valu = ($checked) ? 'checked' : '';
    return "<input type=\"checkbox\" $valu name=\"$name\" value=\"1\">";
}

function button($valu)
{
    $type = 'type="submit"';
    $name = 'name="submit"';
    $valu = "value=\"$valu\"";
    return "<input $type $name $valu>";
}

/*
    |  Need to be carefull here, since the value can contain
    |  a double quote.
    */

function textbox($name, $size, $valu)
{
    $disp = str_replace('"', '&quot;', $valu);
    $disp = str_replace("'", '&#039;', $disp);
    return "<input type=\"text\" name=\"$name\" size=\"$size\" value=\"$disp\">";
}


function fulldate($time)
{
    return ($time) ? date('m/d/y H:i:s', $time) : '<br>';
}

/*
    |  sadly, it does not work to specify a default value.
    |
    |  It doesn't do the directory for linux,
    |  but it works fine for IE.
    |
    |  http://www.ietf.org/rfc/rfc1867.txt
    */

function filebox($name, $size)
{
    return "<input type=\"file\" name=\"$name\" size=\"$size\">";
}


function value_range($min, $max, $val)
{
    if ($val <= $min) $val = $min;
    if ($max <= $val) $val = $max;
    return $val;
}

function green($msg)
{
    return "<font color=\"green\">$msg</font>";
}

function debug_array($debug, $p)
{
    if ($debug) {
        reset($p);
        foreach ($p as $key => $data) {
            $msg = green("$key: $data");
            echo "$msg<br>\n";
        }
    }
}

function two_col($prompt, $action)
{
    return <<< HERE
<tr>
    <td align="right">
        $prompt
    </td>
    <td align="left">
        $action
    </td>
</tr>

HERE;
}


function date_doc()
{
    return  "Date is (mm/dd), (mm/dd/yy), or (mm/dd/yyyy).<br>\n"
        .    "Time is (hh:mm) or (hh:mm:ss).<br>\n"
        .    "Date without time is midnight of the specified day.<br>"
        .    "Time without date is specified time today.";
}
