<?php




define('constTagAny',  '(all)');
define('constTagNever','(never)');
define('constTagNone', '(not displayed)');
define('constTagToday','(today)');
define('constTagEmpty','(empty)');
define('constTagLaden','(exists)');


function tinybox($name,$size,$valu,$pix)
{
    $temp = textbox($name,$size,$valu);
    $patn = "size=\"$size\"";
    $chng = "style=\"font-size: xx-small; width:${pix}px\"";
    $repl = "$patn $chng";
    return str_replace($patn,$repl,$temp);
}


function tiny_select($name,$opt,$val,$key,$pix)
{
    $temp = html_select($name,$opt,$val,$key);
    $patn = 'size="1"';
    $chng = "style=\"font-size:xx-small; width:${pix}px\"";
    $repl = "$patn $chng";
    return str_replace($patn,$repl,$temp);
}




function pages($total,$size)
{
    if ((1 <= $total) && ($total <= $size))
    {
        return 1;
    }
    if ((1 <= $size) && ($size < $total))
    {
        $tmp  = $total + $size - 1;
        return intval($tmp / $size);
    }
    return 0;
}


function indent($n)
{
    $sp = '&nbsp;';
    return str_repeat($sp,$n);
}


function para($txt)
{
    return "<p>$txt</p>\n";
}


function page_bold($txt)
{
    return "<b>[$txt]</b>";
}


function html_jump($href,$jump,$name)
{
    $text = $href . $jump;
    return html_link($text,$name);
}


function prevnext(&$env,$total)
{
    $self = $env['self'];
    $page = $env['page'];
    $limt = $env['limt'];
    $jump = $env['jump'];
    $proc = $env['href'];
    $ord  = $env['ord'];

    $pmin = ($page > 0)? $limt * $page : 0;
    $pmax = $pmin + $limt;
    $xmin = $pmin + 1;
    $xmax = ($pmax > $total)? $total : $pmax;
    $pnum = pages($total,$limt);
    $rnge = "$xmin - $xmax of $total";

    if ($pnum > 1)
    {
        $aa = array();
        $in = indent(2);
        $xn = indent(4);

        $size = 10;
        $imin = $page - $size;
        $imax = $page + $size;
        $imin = ($imin < 0)? 0 : $imin;
        $imax = ($imax < $pnum)? $imax : $pnum - 1;

        $ptxt = '';
        $ntxt = '';
        $ftxt = '';
        $ltxt = '';
        if ($page > 0)
        {
            $text = "Previous $limt";
            $href = $proc($env,$page-1,$ord);
            $ptxt = html_jump($href,$jump,$text) . $in;
        }
        if ($imin > 0)
        {
            $text = "First $limt";
            $href = $proc($env,0,$ord);
            $ftxt = html_jump($href,$jump,$text) . $in;
        }

        if ($page+1 < $pnum)
        {
            $temp = ($page+2 < $pnum)? $limt : $total - $pmax;
            $text = "Next $temp";
            $href = $proc($env,$page+1,$ord);
            $ntxt = $in . html_jump($href,$jump,$text);
        }

        if ($imax + 1 < $pnum)
        {
            $modd = $total % $limt;
            $temp = ($modd)? $modd : $limt;
            $text = "Last $temp";
            $href = $proc($env,$pnum-1,$ord);
            $ltxt = $in . html_jump($href,$jump,$text);
        }

        $fake = $page + 1;
        debug_note("page $fake of $pnum");

        $i = $imin;
        while ($i <= $imax)
        {
            $pref = $proc($env,$i,$ord);
            $fake = $i + 1;
            $link = html_jump($pref,$jump,$fake);
            $aa[] = ($i == $page)? page_bold($fake) : $link;
            $i++;
        }
        $span = ($aa)? join($in,$aa) : '';
        $text = "$rnge${xn}${ftxt}${ptxt}${span}${ntxt}${ltxt}";
    }
    else
    {
        $text = $rnge;
    }
    return para($text);
}


?>
