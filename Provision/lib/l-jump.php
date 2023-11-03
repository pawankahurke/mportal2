<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
28-Mar-03   EWB     Created.
24-Apr-03   EWB     return results, don't echo them.
22-May-03   EWB     Quote Crusade
30-Jul-03   EWB     Factored jumptable into a few smaller functions.    
 6-Oct-03   AAM     Added mark_nocrlf for functions that need a mark that
                    doesn't introduce a whitespace character.
*/

/* mark_nocrlf
        This is just like "mark" below except it doesn't put in the extra
        newline.  This is important in some places where we are using spacing
        to control indentation.  "$name" is the name of the mark to create
        at the current position.
    */
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


/*
    |  Note that the marks for top and bottom are l-head.php.
    */

function jumptable($tags)
{
    $link = array();
    jumptags($link, $tags);
    return jumplist($link);
}
