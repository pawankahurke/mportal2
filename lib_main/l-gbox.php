<?php






function gbox($i, $n, $name, $enabled)
{
    $checked = '';
    $out = '';
    if ($i < $n) {
        if ($enabled) $checked = 'checked';
        $out = <<< HERE

  <td align="right">
    $name:
  </td>
  <td>
    <input type="checkbox" name="d_$name" value="1" $checked>
  </td>

HERE;
    }
    return $out;
}




function event_fields($db)
{
    $names = find_field_names('event', 'Events', $db);
    $keys  = array_flip($names);
    unset($keys['deleted']);
    return array_flip($keys);
}


function genboxes($fnames, $defaults)
{
    $n = safe_count($fnames);

    $names = array();
    $names[] = 'ALL';

    reset($fnames);
    foreach ($fnames as $key => $name) {
        $names[] = $name;
    }

    for ($i = 0; $i < 6; $i++) {
        $names[] = '';
    }

    reset($names);
    foreach ($names as $key => $name) {
        if (!isset($defaults[$name])) {
            $defaults[$name] = '';
        }
    }

    $n = $n + 1;
    $x = intval(($n + 4) / 5);
    $i = 0;
    $m = '';
    for ($r = 0; $r < $x; $r++) {
        $m .= "<tr>\n";
        $m .= gbox($i, $n, $names[$i], $defaults[$names[$i]]);
        $i++;
        $m .= gbox($i, $n, $names[$i], $defaults[$names[$i]]);
        $i++;
        $m .= gbox($i, $n, $names[$i], $defaults[$names[$i]]);
        $i++;
        $m .= gbox($i, $n, $names[$i], $defaults[$names[$i]]);
        $i++;
        $m .= gbox($i, $n, $names[$i], $defaults[$names[$i]]);
        $i++;
        $m .= "</tr>\n";
    }
    return $m;
}


function genconfig_list($db, $details, $post, $list)
{
    $config = '';
    if ($details) {
        $config = ':';
        $all    = @$post['d_ALL'];

        reset($list);
        foreach ($list as $xxx => $name) {
            $d_name = "d_$name";
            $found  = @$post[$d_name];

            if (($all) || ($found)) {
                $config .= "$name:";
            }
        }
    }
    return $config;
}




function genconfig($db, $details, $post)
{
    $list = find_field_names('event', 'Events', $db);
    return genconfig_list($db, $details, $post, $list);
}
