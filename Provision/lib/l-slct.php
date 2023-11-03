<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
11-Sep-02   EWB     Merge with new asset code
11-Sep-02   EWB     get_checked_fields works if no fields checked.
12-Sep-02   EWB     fixed a bug in check_can_delete_search.
17-Sep-02   EWB     check_can_delete_search uses strstr instead of strpos
22-May-03   EWB     Quote Crusade.
 3-Jun-03   NL      html_select(): Add id to accomodate DHTML events.
 3-Jun-03   EWB     Fixed a typo.
27-Jun-07   AAM     Added html_select_class for use with user values for
                    automation.

*/



/*
 |   Create an HTML select option, with the current value 
 |   already selected.
 */

function html_select_class($name, $options, $selected, $keys, $class)
{
    if ($class == '') {
        $classtext = '';
    } else {
        $classtext = " class=\"$class\"";
    }
    reset($options);
    $m = "<select $classtext name=\"$name\" id=\"$name\" size=\"1\">\n";
    if ($keys) {
        foreach ($options as $key => $data) {
            if ($selected == $key)
                $m .= "<option selected value=\"$key\">$data</option>\n";
            else
                $m .= "<option value=\"$key\">$data</option>\n";
        }
    } else {
        foreach ($options as $key => $data) {
            if ($selected == $data)
                $m .= "<option selected>$data</option>\n";
            else
                $m .= "<option>$data</option>\n";
        }
    }
    $m .= "</select>\n";
    return $m;
}

function html_select_onchange_class($name, $options, $selected, $keys, $class, $changeEventName)
{
    if ($class == '') {
        $classtext = '';
    } else {
        $classtext = " class=\"$class\"";
    }

    $changeEventName = ($changeEventName) ? 'onchange=' . $changeEventName . '()' : '';

    reset($options);
    $m = "<select $classtext name=\"$name\" id=\"$name\" size=\"1\" $changeEventName>\n";
    if ($keys) {
        foreach ($options as $key => $data) {
            if ($selected == $key)
                $m .= "<option selected value=\"$key\">$data</option>\n";
            else
                $m .= "<option value=\"$key\">$data</option>\n";
        }
    } else {
        foreach ($options as $key => $data) {
            if ($selected == $data)
                $m .= "<option selected>$data</option>\n";
            else
                $m .= "<option>$data</option>\n";
        }
    }
    $m .= "</select>\n";
    return $m;
}

function html_select($name, $options, $selected, $keys)
{
    return html_select_class($name, $options, $selected, $keys, '');
}

function html_select_onchange($name, $options, $selected, $keys, $changeEventNm)
{
    return html_select_onchange_class($name, $options, $selected, $keys, '', $changeEventNm);
}

function yesno($bool)
{
    if ($bool)
        return "Yes";
    else
        return "No";
}

function html_multi_select_class($name, $options, $selected, $keys, $class)
{
    if ($class == '') {
        $classtext = '';
    } else {
        $classtext = " class=\"$class\"";
    }
    reset($options);
    $selectedList = explode(',', $selected);
    $m = "<select $classtext multiple name=\"$name" . "[]\" id=\"$name\" size=\"5\">\n";
    if ($keys) {
        foreach ($options as $key => $data) {
            //if ($selected == $key) {
            if (in_array($key, $selectedList)) {
                $m .= "<option selected value=\"$key\">$data</option>\n";
            } else {
                $m .= "<option value=\"$key\">$data</option>\n";
            }
        }
    } else {
        foreach ($options as $key => $data) {
            //if ($selected == $data) {
            if (in_array($data, $selectedList)) {
                $m .= "<option selected>$data</option>\n";
            } else {
                $m .= "<option>$data</option>\n";
            }
        }
    }
    $m .= "</select>\n";
    return $m;
}

function html_multiselect($name, $options, $selected, $keys)
{
    return html_multi_select_class($name, $options, $selected, $keys, '');
}
