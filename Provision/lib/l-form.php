<?php

/*
Revision history:

Date        Who     What
----        ---     ----
15-Jun-04   EWB     Created.
17-Jun-04   EWB     value_range();
18-Jun-04   EWB     areabox()
23-Jun-04   EWB     changed meaning of radio arguments.
28-Jun-04   EWB     added post_other
25-Jan-05   EWB     added post_meth
31-Jan-05   EWB     added click()
17-Aug-05   BJS     added passbox()
26-Sep-05   BJS     added textareabox().
21-Oct-05   BJS     added saved_search().
16-Nov-05   BJS     added input_text().
20-Sep-06   BTE     Added/moved to here _onclick variants.

*/

function post_meth($name, $meth, $page)
{
    return "<form method=\"$meth\" name=\"$name\" action=\"$page\">\n";
}

function post_other($name, $page)
{
    return post_meth($name, 'post', $page);
}

function post_self($name)
{
    $self  = server_var('PHP_SELF');
    return post_meth($name, 'post', $self);
}

function form_footer()
{
    return "\n\n</form>\n\n";
}

function button($valu)
{
    $type = 'type="submit"';
    $name = 'name="button"';
    $valu = "value=\"$valu\"";
    return "<input $type $name $valu>";
}

function click($valu, $action)
{
    return "<input type=\"button\" value=\"$valu\" onclick=\"$action\">";
}


function hidden($name, $value)
{
    return "<input type=\"hidden\" name=\"$name\" value=\"$value\">\n";
}


/*
    |  We always send a 1 here, since nothing is sent
    |  unless the box is checked.
    |
    |  So, a get_integer($name,0) will return 1 if the box
    |  was checked and 0 otherwise ... which is what you want.
    */

function checkbox($name, $checked)
{
    $valu = ($checked) ? ' checked' : '';
    return "<input type=\"checkbox\"$valu name=\"$name\" value=\"1\">";
}

/*
    |  We always send a 1 here, since nothing is sent
    |  unless the box is checked.
    |
    |  So, a get_integer($name,0) will return 1 if the box
    |  was checked and 0 otherwise ... which is what you want.
    */

function checkbox_onclick($name, $checked, $onclick)
{
    $valu = ($checked) ? ' checked' : '';
    return "<input type=\"checkbox\"$valu name=\"$name\" value=\"1\""
        . " onclick=\"$onclick\">";
}

/*
    |  Need to be carefull here, since the value can contain
    |  a double quote.
    */

function textbox($name, $size, $valu)
{
    $disp = check_text_input($valu);
    return "<input type=\"text\" name=\"$name\" size=\"$size\" value=\"$disp\">";
}


/*
    |  $name  = refer to the textbox contents with this name
    |  $width = desired width in pixels.
    |  $rows  = the number of rows in the textbox
    |  $valu  = the value (if any) to be displayed in the box
    */
function textareabox($name, $width, $rows, $valu)
{
    $disp  = htmlentities($valu, ENT_QUOTES);
    $width = $width . 'px';
    return "<textarea rows=\"$rows\" style=\"width:$width\" name=\"$name\">$disp</textarea>";
}

function check_text_input($valu)
{
    $disp = str_replace('"', '&quot;', $valu);
    $disp = str_replace("'", '&#039;', $disp);
    return $disp;
}

function passbox($name, $size, $valu)
{
    $disp = str_replace('"', '&quot;', $valu);
    $disp = str_replace("'", '&#039;', $disp);
    return "<input type=\"password\" name=\"$name\" size=\"$size\" value=\"$disp\">";
}

/*
    |  It looks like wrap might be a netscape feature.
    */

function areabox($name, $rows, $cols, $wrap, $valu)
{
    $args = " name=\"$name\" rows=\"$rows\" cols=\"$cols\"";

    if ($wrap) {
        $args .= " wrap=\"$wrap\"";
    }
    return "<textarea $args>$valu</textarea>\n";
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


function radio($name, $valu, $var)
{
    $disp = ($var == $valu) ? ' checked' : '';
    return "<input type=\"radio\" name=\"$name\"$disp value=\"$valu\">";
}


/* radio_onclick

        JavaScript capable version of radio.  Pass in the name of the radio
        control in $name, the value $value, the current value for the group
        $var, and the JavaScript code $click.
    */
function radio_onclick($name, $valu, $var, $click)
{
    $disp = ($var == $valu) ? ' checked' : '';
    return "<input type=\"radio\" name=\"$name\"$disp value=\"$valu\""
        . " onclick=\"$click\">";
}

function value_range($min, $max, $val)
{
    if ($val <= $min) $val = $min;
    if ($max <= $val) $val = $max;
    return $val;
}


function saved_search($searches, $search_list, $size, $select_name, $message)
{
    $selected = array();
    if (strlen($search_list)) {
        $s = explode(',', $search_list);
        reset($s);
        foreach ($s as $key => $id) {
            if ($id > 0) {
                $selected[$id] = true;
            }
        }
    }
    if ($searches) {
        $o = 'option';
        $m = "<select name=\"$select_name\" multiple size=\"$size\">\n";
        reset($searches);
        foreach ($searches as $id => $name) {
            $s  = (isset($selected[$id])) ? 'selected ' : '';
            $m .= "<$o ${s}value=\"$id\">$name</$o>\n";
        }
        $m .= "</select>\n";
    } else {
        $m = "<b>$message</b>\n";
    }
    return $m;
}


function input_text($name, $valu)
{
    $disp = str_replace('"', '&quot;', $valu);
    $disp = str_replace("'", '&#039;', $disp);
    return "<input type=\"text\" name=\"$name\" value=\"$disp\">";
}
