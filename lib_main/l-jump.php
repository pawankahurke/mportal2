<?php




function mark_nocrlf($name)
{
    return "<a name=\"$name\"></a>";
}

function mark($name)
{
    return "<a name=\"$name\"></a>\n";
}

function marklink($link, $text)
{
    return html_link($link, $text);
}

function jumplist($list)
{
    $txt = '';
    if ($list) {
        $msg = join("&nbsp;|\n", $list);
        $txt = fontspeak("<p>[ $msg ]</p>") . "\n";
    }
    return $txt;
}


function jumptags(&$link, $tags)
{
    $args = explode(',', $tags);
    if ($args) {
        reset($args);
        foreach ($args as $key => $name) {
            $link[] = marklink("#$name", $name);
        }
    }
}




function jumptable($tags)
{
    $link = array();
    jumptags($link, $tags);
    return jumplist($link);
}
