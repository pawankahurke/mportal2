<?php







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

function html_select($name, $options, $selected, $keys)
{
    return html_select_class($name, $options, $selected, $keys, '');
}

function yesno($bool)
{
    if ($bool)
        return "Yes";
    else
        return "No";
}
