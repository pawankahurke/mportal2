<?php






function intersect($a, $b)
{
    $tmp = array();
    $res = array();
    reset($a);
    foreach ($a as $key => $data) {
        $tmp[$data] = false;
    }
    reset($b);
    foreach ($b as $key => $data) {
        $tmp[$data] = true;
    }
    reset($a);
    foreach ($a as $key => $data) {
        if ($tmp[$data])
            $res[] = $data;
    }
    return $res;
}





function union($a, $b)
{
    $tmp = array();
    $res = array();
    reset($a);
    foreach ($a as $key => $data) {
        $tmp[$data] = true;
    }
    reset($b);
    foreach ($b as $key => $data) {
        $tmp[$data] = true;
    }
    reset($tmp);
    foreach ($tmp as $key => $data) {
        $res[] = $key;
    }
    return $res;
}




function subset($a, $b)
{
    $vote = array();
    $good = true;
    reset($a);
    foreach ($a as $key => $data) {
        $vote[$data] = true;
    }
    reset($b);
    foreach ($b as $key => $data) {
        $vote[$data] = false;
    }
    reset($vote);
    foreach ($vote as $key => $data) {
        if ($data) {
            $good = false;
        }
    }
    return $good;
}
