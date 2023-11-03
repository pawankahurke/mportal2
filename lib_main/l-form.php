<?php



include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

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




function checkbox($name, $checked)
{
    $valu = ($checked) ? ' checked' : '';
    return "<input type=\"checkbox\"$valu name=\"$name\" value=\"1\">";
}



function checkbox_onclick($name, $checked, $onclick)
{
    $valu = ($checked) ? ' checked' : '';
    return "<input type=\"checkbox\"$valu name=\"$name\" value=\"1\""
        . " onclick=\"$onclick\">";
}



function textbox($name, $size, $valu)
{
    $disp = check_text_input($valu);
    return "<input type=\"text\" name=\"$name\" size=\"$size\" value=\"$disp\">";
}



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



function areabox($name, $rows, $cols, $wrap, $valu)
{
    $args = " name=\"$name\" rows=\"$rows\" cols=\"$cols\"";

    if ($wrap) {
        $args .= " wrap=\"$wrap\"";
    }
    return "<textarea $args>$valu</textarea>\n";
}



function filebox($name, $size)
{
    return "<input type=\"file\" name=\"$name\" size=\"$size\">";
}


function radio($name, $valu, $var)
{
    $disp = ($var == $valu) ? ' checked' : '';
    return "<input type=\"radio\" name=\"$name\"$disp value=\"$valu\">";
}



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
