<?php






function qp_encode(&$str)
{
    $txt = '';
    $out = '';
    $esc = '=';
    $eol = "\r\n";
    $snl = $esc . $eol;
    $spc = 0;
    $sp  = ' ';
    $hex = explode('|', '0|1|2|3|4|5|6|7|8|9|A|B|C|D|E|F');

    $jmax = strlen($str);

    for ($j = 0; $j < $jmax; $j++) {
        $ccc  = $str[$j];
        $ord  = ord($ccc);

        if ($ccc == "\n") {

            while ($spc > 0) {
                $sp = ($spc > 1) ? ' ' : '=20';
                if (76 <= strlen($out) + strlen($sp)) {
                    $out .= $snl;
                    $txt .= $out;
                    $out  = '';
                }
                $out .= $sp;
                $spc--;
            }
            $out .= $eol;
            $txt .= $out;
            $out  = '';
            continue;
        }

        if ($ccc == ' ') {
            $spc++;
            continue;
        }

        while ($spc > 0) {
            if (76 <= strlen($out) + 1) {
                $out .= $snl;
                $txt .= $out;
                $out  = '';
            }
            $out .= ' ';
            $spc--;
        }


        if (($ord == 61) || ($ord < 32) || ($ord > 126)) {
            $hi  = floor($ord / 16);
            $lo  = floor($ord % 16);
            $ccc = $esc . $hex[$hi] . $hex[$lo];
        }

        if (76 <= (strlen($out) + strlen($ccc))) {
            $out .= $snl;
            $txt .= $out;
            $out  = '';
        }
        $out .= $ccc;
    }

    while ($spc > 0) {
        $sp = ($spc > 1) ? ' ' : '=20';
        if (76 <= strlen($out) + strlen($sp)) {
            $out .= $snl;
            $txt .= $out;
            $out  = '';
        }
        $out .= $sp;
        $spc--;
    }

    $txt .= $out;

    return $txt;
}



function sendmail($to, $from, $subject, $tmpfile_h, $headers)
{
    $good = true;
//    $fd   = popen('/usr/sbin/sendmail -t', 'w');
//    if ($fd) {
//        fputs($fd, "To: $to\n");
//        fputs($fd, "$from\n");
//        fputs($fd, "Subject: $subject\n");
//        fputs($fd, "X-Mailer: PHP5\n");
//
//        if ($headers)
//            fputs($fd, "$headers\n");
//
//        fputs($fd, "\n");
//
//        rewind($tmpfile_h);
//        while (!feof($tmpfile_h)) {
//            $str = fread($tmpfile_h, 524288);
//            if (strlen($str) > 0) {
//                if (!$good = fputs($fd, $str, strlen($str))) {
//                    pclose($fd);
//                    return $good;
//                }
//            } else {
//
//                pclose($fd);
//                return $good;
//            }
//        }
//        pclose($fd);
//    }
  // send from visualisationService
  $arrayPost = array(
    'from' => getenv('SMTP_USER_LOGIN'),
    'to' => $to,
    'subject' => $subject,
    'text' =>'',
    'html' => $tmpfile_h,
    'token' => getenv('APP_SECRET_KEY'),
  );
  $url = getenv('VISUALISATION_SERVICE_API_URL')."/mailer/sendmassage";
  CURL::sendDataCurl($url, $arrayPost);

  return $good;
}







function mime_filename($name)
{
    $txt = '';
    $len = strlen($name);
    for ($i = 0; $i < $len; $i++) {
        $good = false;
        $ch = substr($name, $i, 1);
        if (('a' <= $ch) && ($ch <= 'z')) {
            $good = true;
        } elseif (('0' <= $ch) && ($ch <= '9')) {
            $good = true;
        } elseif (('A' <= $ch) && ($ch <= 'Z')) {
            $good = true;
        } elseif (($ch == '.') || ($ch == '_') || ($ch == '-')) {
            $good = true;
        }
        if ($good) {
            $txt .= $ch;
        }
    }
    $out = ($txt == '') ? 'file.txt' : $txt;
    return $out;
}





function debug_mime(&$txt)
{
    $name = '/tmp/debug-mime.txt';
    if (file_exists($name)) {
        unlink($name);
    }
    $file = @fopen($name, 'w+');
    if ($file) {
        fwrite($file, $txt);
        fclose($file);
    } else {
    }
}




function image_section(&$img)
{
    $txt = '';
    if ($img) {
        $name = $img['name'];
        $type = $img['type'];
        $xxx  = $img['xsize'];
        $yyy  = $img['ysize'];
        $bbb  = $img['bsize'];
        $cid  = $img['cid'];
        $txt .= "Content-Type: $type;\r\n";
        $txt .= "    name=\"$name\"\r\n";
        $txt .= "Content-Description: image, height:$yyy, width:$xxx, size:$bbb\r\n";
        $txt .= "Content-Id: <$cid>\r\n";
        $txt .= "Content-Transfer-Encoding: base64\r\n";
        $txt .= "\r\n";
        $txt .= image_encode($img);
        $txt .= "\r\n";
    }
    return $txt;
}





function mime_gate()
{
    $rand = md5(microtime());
    $mark = '___=___';
    $gate = $mark . $rand . $mark;
    return $gate;
}



function html_section(&$html)
{
    $dtd  = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
    $txt  = "Content-Type: text/html; charset=us-ascii\r\n";
    $txt .= "Content-Transfer-Encoding: quoted-printable\r\n";
    $txt .= "\r\n";
    $txt .= "$dtd\r\n";
    $txt .= qp_encode($html);
    $txt .= "\r\n";
    return $txt;
}



function mhtml_section($tmpfile_h, $mimefile_h)
{
    $size = 0;
    $dtd  = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
    $msg  = "Content-Type: text/html; charset=us-ascii\r\n";
    $msg .= "Content-Transfer-Encoding: quoted-printable\r\n";
    $msg .= "\r\n";
    $msg .= "$dtd\r\n";

    if (my_write($mimefile_h, $msg)) {
        rewind($tmpfile_h);
        while (!feof($tmpfile_h)) {
            $chunk = fread($tmpfile_h, 524288);
            if ($chunk) {
                $chunk = qp_encode($chunk);
                $tmps  = my_writesize($mimefile_h, $chunk);
                if ($tmps < 0) return $tmps;

                $size = $size + $tmps;
            }
        }
        if (my_write($mimefile_h, "\r\n"))
            return $size + 2;
    }
    return -1;
}



function mhtml_mail($dst, $sub, $tmpfile_h, $src, &$imgs)
{
    $good = false;
    $mimefile_h = tmpfile();
    if (!$mimefile_h) return false;

    $gate  = mime_gate();
    $from  = "From: $src";
    $head  = "MIME-Version: 1.0;\n";
    $head .= "Content-Type: multipart/related;\n";
    $head .= "    boundary=\"$gate\"";

    $msg   = "This is a multipart message in MIME format.\r\n\r\n";
    $msg  .= "--$gate\r\n";
    $msg  .= "Content-Type: multipart/alternative;\r\n";
    $msg  .= "    boundary=\"$gate\"\r\n\r\n";
    $msg  .= "--$gate\r\n";

    if (!my_write($mimefile_h, $msg)) return false;

    $size = mhtml_section($tmpfile_h, $mimefile_h);

    if ($size >= 0) {
        $msg  = "--$gate--\r\n";
        $msg .= "\r\n";

        if (!my_write($mimefile_h, $msg)) return false;

        if ($imgs) {
            $msg = '';
            reset($imgs);
            foreach ($imgs as $k => $img) {
                $msg .= "--$gate\r\n";
                $msg .= image_section($img);
            }
            $msg .= "--$gate--\r\n";

            if (!my_write($mimefile_h, $msg)) return false;
        }

        $good = sendmail($dst, $from, $sub, $mimefile_h, $head);
        $dbug = @($GLOBALS['debug']) ? 1 : 0;
        if ($dbug) {
            $num  = safe_count($imgs);
            $date = datestring(time());
            $tmp  = " source:$src\n";
            $tmp .= "   dest:$dst\n";
            $tmp .= "subject:$sub\n";
            $tmp .= "   date:$date\n";
            $tmp .= " images:$num\n";
            $tmp .= "   size:$size\n";
            $tmp .= "-------\n\n";
            $tmp .= $head;
            unset($head);
            $tmp .= "\n\n";
            debug_mime($tmp);
        }
    }
    fclose($mimefile_h);
    return $good;
}



function html_attach($time, $name, $tmpfile_h, $mimefile_h)
{
    $date = date('r', $time);
    $size = 0;

    $msg  = "Content-Type: text/html; charset=us-ascii\r\n";
    $msg .= "Content-Transfer-Encoding: quoted-printable\r\n";
    $msg .= "Content-Disposition: attachment;\r\n";
    $msg .= "    filename=\"$name\";\r\n";
    $msg .= "    creation-date=\"$date\"\r\n";
    $msg .= "\r\n";

    if (!my_write($mimefile_h, $msg)) return -1;

    rewind($tmpfile_h);
    while (!feof($tmpfile_h)) {
        $chunk = fread($tmpfile_h, 524288);
        $chunk = qp_encode($chunk);

        $tmps  = my_writesize($mimefile_h, $chunk);
        if ($tmps < 0) return $tmps;

        $size  = $size + $tmps;
    }
    $tmps = my_writesize($mimefile_h, "\r\n");
    if ($tmps > 0)
        return $size + $tmps;
    else
        return -1;
}



function mime_mail($dst, $sub, $src, $sum, $file, $tmpfile_h)
{

    $mimefile_h = tmpfile();
    if (!$mimefile_h) return false;

    $gate  = mime_gate();
    $from  = "From: $src";
    $head  = "MIME-Version: 1.0;\n";
    $head .= "Content-Type: multipart/mixed;\n";
    $head .= "    boundary=\"$gate\"";

    $now   = time();

    $msg   = "This is a multipart message in MIME format.\r\n\r\n";
    $msg  .= "--$gate\r\n";

    if (!my_write($mimefile_h, $msg))               return false;

    if (!my_write($mimefile_h, html_section($sum))) return false;

    if (!my_write($mimefile_h, "--$gate\r\n"))      return false;


    $size = html_attach($now, $file, $tmpfile_h, $mimefile_h);
    if ($size > 0) {
        if (!my_write($mimefile_h, "--$gate--\r\n")) return false;

        $qtotal = (time() - $now);
        $now    = time();

        $good   = sendmail($dst, $from, $sub, $mimefile_h, $head);
        debug_note("l-mime: mime_mail,sendmail good:($good)");
        $mtotal = (time() - $now);
        $dbug = @($GLOBALS['debug']) ? 1 : 0;
        if ($dbug) {
            $date = datestring(time());
            $tmp  = " source:$src\n";
            $tmp .= "   dest:$dst\n";
            $tmp .= "subject:$sub\n";
            $tmp .= "   date:$date\n";
            $tmp .= "qpncode:$qtotal(sec)\n";
            $tmp .= "   mail:$mtotal(sec)\n";
            $tmp .= "   size:$size\n";
            $tmp .= "-------\n\n";
            $tmp .= $head;
            unset($head);
            $tmp .= "\n\n";
            debug_mime($tmp);
        }
    }
    fclose($mimefile_h);
    return $good;
}
