<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
31-Jul-03   EWB     Created.
15-Apr-04   EWB     Do refresh thing before the title
25-Jun-04   EWB     Added meter reports to restricted user.
14-Jun-06   JRN     Added 'Remote Control' link with 'Tools:'.
22-Jun-06   BTE     Bug 3498: PHP Warnings browsing to the information
                    portal.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()                    

*/

    function restricted_html_header($title,$comp,$authuser,$db)
    {
        check_cache(0, $db);

        $refreshtime = global_def('refreshtime','');
   
        $standard_style = standard_style();

        $msg  = '<html>';
        $msg .= <<< HERE

<head>
    $refreshtime
    <title>$title</title>
    $standard_style
</head>

<body link="#333399" vlink="#660066" alink="#00FF00" bgcolor="#FFFFFF">

<a name="top"></a>

<table width="100%" border="0">
<tr>
        <td align="left" valign="top">
HERE;
            echo $msg;
            $msg  = '<br>';
            $odir = $comp['odir'];
            if ($odir)
            {       
                $logo = head_logo_state($authuser,$comp,$db);
                $src  = $logo['src'];
                $xxx  = $logo['xxx'];
                $yyy  = $logo['yyy'];
                $href = "/$odir/acct/files.php";
                $img  = "<img border=\"0\" src=\"$src\" width=\"$xxx\" height=\"$yyy\">";
                $msg  = "<a href=\"$href\">\n$img</a>\n";
            }
            echo $msg;
?>
        </td>
        <td align="right">
<?php
            $msg = '<br>';
            if (($odir) && ($comp['acct']))
            {
                $a   = array( );
                $p   = "/$odir/acct/files.php?c";
                $a[] = html_link("$p=1",'event');
                $a[] = html_link("$p=2",'asset');
                $a[] = html_link("$p=3",'change');
                $a[] = html_link("$p=4",'meter');
                $msg = header_tag('information portal',$a);
            }
            echo $msg;
            $a = '';
            $msg = '<br>';
            if (($odir) && ($comp['acct']))
              {
                $q   = "/$odir/config/remote.php?scop=4&act=scop&pcn=cwiz&rcon=1";
                $a[] = html_link("$q",'remote control');
                $msg = header_tag('tools',$a);
              }
            echo $msg;
            echo <<< HERE

        </td>
    </tr>
</table>
 
<table width="100%" border="0">
    <tr>
        <td align="left" valign="top">
            <span class="heading">
                $title
            </span>
        </td>
        <td align="right" valign="top">

HERE;
            $date = date('F d, Y');
            $log  = logout_link($comp,$authuser);
            $msg  = "<b>user: $authuser</b> $log<br>\n"; 
            $msg .= "$date<br>\n";
            $msg  = "<span class=\"footnote\">\n$msg</span>\n"; 
            echo $msg;

            echo <<< HERE

        </td>
    </tr>
</table>

HERE;

    }



?>
