<?php

/*
Revision history:

Date        Who     What
----        ---     ----
16-Sep-03   EWB     Created.
17-Sep-03   EWB     Give a name to the image attatchments.
18-Sep-03   EWB     Better debug information.
18-Sep-03   EWB     Explanatory preamble.
19-Sep-03   EWB     Added special doctype for 'Outlook'
24-Sep-03   EWB     Support for non-mhtml
25-Sep-03   EWB     Specify correct creation-date.
25-Sep-03   EWB     Always generate legal attachment filenames.
 5-Nov-03   EWB     qp_encode rewritten to vastly reduce memory usage.
 9-Jan-04   EWB     server name.
16-Feb-04   EWB     mime mail needs server_name argument.
06-Dec-04   BJS     mime mail now takes summary & filename as argv.
29-Mar-05   BJS     mime mail computes qpencode/mail time & mail len.
 8-Jul-05   BJS     body of email reports built in temp file not array.
12-Jul-05   BJS     added error checking to file write/read procedures.
13-Jul-05   BJS     switched fwrite() to my_write/my_writesize().
07-Nov-05   BJS     Fixed sendmail bug.
*/


/*
 |  See RFC 2045, section 6.7
 |   "Quoted-Printable Content-Transfer-Encoding"
 |
 |   http://www.ietf.org/rfc/rfc2045.txt
 |   http://www.faqs.org/rfcs/rfc2045.html
 |
 |  We don't alter the input string ... I'm passing it
 |  by reference only because it could be very large,
 |  and we don't want to shove it on the stack.
 |
 |  5-Nov-03 *EWB*  Note that there is a much simpler way to
 |  do this, which involves exploding the string into lines,
 |  and then processing each line individually.
 |
 |  However since the input string can sometimes be
 |  extremely large (megabytes) we're processing it
 |  just one byte at a time.
 */

function qp_encode(&$str)
{
    $txt = '';
    $out = '';
    $esc = '=';
    $eol = "\r\n";
    $snl = $esc . $eol;     // soft newline
    $spc = 0;
    $sp  = ' ';
    $hex = explode('|', '0|1|2|3|4|5|6|7|8|9|A|B|C|D|E|F');

    $jmax = strlen($str);

    for ($j = 0; $j < $jmax; $j++) {
        $ccc  = $str[$j];
        $ord  = ord($ccc);

        if ($ccc == "\n") {
            // need to quote the last trailing space

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

        // we aren't required to encode tabs
        // but it's allowed and makes the code
        // simpler anyway.

        if (($ord == 61) || ($ord < 32) || ($ord > 126)) {
            $hi  = floor($ord / 16);
            $lo  = floor($ord % 16);
            $ccc = $esc . $hex[$hi] . $hex[$lo];
        }

        if (76 <= (strlen($out) + strlen($ccc))) {
            $out .= $snl;   // soft new line
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


/* copy the finished report to sendmail in 56k chunks,
       we do this to avoid holding the report in an array. */
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
//                /* nothing left to read, close file */
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





/*
 |  Generate a "happy" filename by filtering
 |  out any "unusual" characters that some
 |  filesystems might have problems with.
 |
 |  In particular, this includes, non-ascii, whitespace,
 |  and directory specifications.
 |
 |  We're allowing just alphanumeric plus
 |  dash, period, and underscore.
 |
 |  http://www.faqs.org/rfcs/rfc2183.html
 |    The Content-Disposition Header Field
 |
 |  See RFC 2183 section 2.3
 */

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



/*
 |  Save a copy of the output mime info to
 |  the temp directory.
 */

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
        logs::log(__FILE__, __LINE__, "debug: could not create $name", 0);
    }
}


/*
 |  See RFC 2045, section 6.8
 |   "Base64 Content-Transfer-Encoding"
 |
 |   http://www.ietf.org/rfc/rfc2045.txt
 |   http://www.faqs.org/rfcs/rfc2045.html
 |
 */

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



/*
 |  Returns some string to use as our mime boundary.
 |  It doesn't matter what it is as long as it cannot
 |  happen in the message itself ... which won't ever
 |  happen with base64 or quoted-printable, because
 |  the string '=_' is an illegal encoding.
 */

function mime_gate()
{
    $rand = md5(microtime());
    $mark = '___=___';
    $gate = $mark . $rand . $mark;
    return $gate;
}

/*
 |  See RFC 2045, section 6.7
 |   "Quoted-Printable Content-Transfer-Encoding"
 |
 |   http://www.ietf.org/rfc/rfc2045.txt
 |   http://www.faqs.org/rfcs/rfc2045.html
 |
 |  We don't alter the input string ... I'm passing it
 |  by reference only because it is likely very large,
 |  and we don't want to shove it on the stack.
 */

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


/* an html email has a small summary in the body of the email,
   so we can use html_section to process it. However, a
   mhtml emails body can be very large, so use this instead. */
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

/*
 |  http://www.faqs.org/rfcs/rfc2557.html
 |    MIME Encapsulation of Aggregate Documents, such as HTML (MHTML)
 |
 |  http://www.faqs.org/rfcs/rfc2045.html
 |     Multipurpose Internet Mail Extensions (MIME)
 |     Part One: Format of Internet Message Bodies
 |
 |  http://www.faqs.org/rfcs/rfc2046.html
 |     Multipurpose Internet Mail Extensions (MIME)
 |     Part Two: Media Types
 */

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


/* html_attach is called from mime_mail when creating the body of the email.
   we want to save the entire body to a temp file. html_attach takes the
   complete report from $tmpfile_h and calls qp_encode on 64k chunks.
   It saves the processed chunks to the body file ($mimefile_h).          */
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

/*
 |  Sends a single html file as an attachment.
 |
 |  http://www.faqs.org/rfcs/rfc2183.html
 |    The Content-Disposition Header Field
 |
 */

function mime_mail($dst, $sub, $src, $sum, $file, $tmpfile_h)
{
    /* this temp file will hold the body content */
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
