<?php



   

    function bugspeak($msg)
    {
    }

   

    function getmicro($mtime)
    {
        $temp  = explode(" ",$mtime);
        $micr  = (float) $temp[0];
        $now   = (int)   $temp[1];
        $micr  = round($micr*1000000);
        if (1000000 <= $micr) $micr = 999999;
        $tday  = getdate($now);

        $d = $tday['mday'];
        $m = $tday['mon'];
        $y = $tday['year'];

        bugspeak("today:$m/$d/$y");

        $midn = mktime(0,0,0,$m,$d,$y);
        $dsec = $now - $midn;

        $usec = (double) $dsec;
        $usec = ($usec * 1000000) + $micr;
        $msec = ($dsec * 1000) + round($micr / 1000);

        bugspeak("mtime: $mtime<br> now:$now, midn:$midn, dsec:$dsec, msec:$msec, usec:$usec");

        $time = $tday;
        $time['now']  = $now;           $time['midn'] = $midn;          $time['dsec'] = $dsec;          $time['msec'] = $msec;          $time['usec'] = $usec;          $time['micr'] = $micr;          return $time;
    }


   

    function image_type($name)
    {
        $res = 'image/jpeg';
        $txt = strtolower($name);
        $len = strlen($txt);
        if (5 <= $len)
        {
            $ext = substr($txt,-4);
            switch ($ext)
            {
                case '.gif': $res = 'image/gif';  break;
                case '.png': $res = 'image/png';  break;
                case '.jpg': $res = 'image/jpeg'; break;
                case '.jpe': $res = 'image/jpeg'; break;
                case '.bmp': $res = 'image/bmp';  break;
                case '.xbm': $res = 'image/xbm';  break;
                default    : break;
            }
        }
        return $res;
    }


    function tmpname($tday)
    {
        $yday = $tday['yday'];
        $year = $tday['year'];
        $dsec = $tday['dsec'];
        $micr = $tday['micr'];

        $year = $year % 100;
        $yday = $yday + 1;

        $pid  = posix_getpid() % 100000;
        $name = sprintf("../reports/%02d/%03d/%05d%06d.%05d.jpg",$year,$yday,$dsec,$micr,$pid);

        bugspeak("year:$year, yday:$yday, dsec:$dsec, micr:$micr, pid:$pid <br>$name");

        return $name;
    }



   

    function build_cid($time,$srv)
    {
        $pid = posix_getpid() % 100000;
        $cid = sprintf('%04d%02d%02d.%02d%02d%02d.%06d.%05d@%s',
            $time['year'],$time['mon'],$time['mday'],
            $time['hours'],$time['minutes'],$time['seconds'],
            $time['micr'],$pid,$srv);
        return $cid;
    }


   

    function image_name($time,$ext)
    {
        $name = sprintf('%04d%02d%02d%02d%02d%02d%06d%s',
            $time['year'],
            $time['mon'],
            $time['mday'],
            $time['hours'],
            $time['minutes'],
            $time['seconds'],
            $time['micr'],
            $ext);
        return $name;
    }


    function build_image($im,$sx,$sy,$quality,$server)
    {
        $res  = array( );
        $time = getmicro(microtime());

        ob_start();
        {
            ImageJpeg($im,NULL,$quality);
            $res['image'] = ob_get_contents();
            ob_end_clean();
        }
        $name = image_name($time,'.jpg');
        ImageDestroy($im);
        $res['bsize'] = strlen($res['image']);
        $res['xsize'] = $sx;
        $res['ysize'] = $sy;
        $res['time']  = $time;
        $res['name']  = $name;
        $res['type']  = image_type($name);
        $res['cid']   = build_cid($time,$server);
        return $res;
    }


    function closest($target,$choices)
    {
        $n = safe_count($choices);
        $x = $choices[0];
        $best = abs($target-$x);
        bugspeak("target:$target, n:$n");
        for ($i = 0; $i < $n; $i++)
        {
            $candidate = $choices[$i];
            $distance  = abs($target-$candidate);
            if ($distance < $best)
            {
                $x = $candidate;
                $best = $distance;
            }
        }
        bugspeak("final:$x, best:$best");
        return $x;
    }


   

    function xaxis($im,$message,$font,$color)
    {
        $len  = strlen($message);
        if ($len)
        {
            $sx = imagesx($im);
            $sy = imagesy($im);
            $tx = imagefontwidth($font) * $len;
            $ty = imagefontheight($font);

            bugspeak("xaxis: message:$message, len:$len, sx:$sx, sy:$sy, tx:$tx, ty:$ty");

            if (($tx < $sx) && ($ty < $sy))
            {
                $x = round(($sx - $tx) / 2);
                $y = round($sy - ($ty*1.50));
                ImageString($im,$font,$x,$y,$message,$color);
            }
        }
    }


   

    function yaxis($im,$message,$font,$color)
    {
        $len  = strlen($message);
        if ($len)
        {
            $sx = imagesx($im);
            $sy = imagesy($im);
            $tx = imagefontheight($font);
            $ty = imagefontwidth($font) * $len;

            bugspeak("yaxis: message: $message, len:$len, sx:$sx, sy:$sy, tx:$tx, ty:$ty");

            if (($tx < $sx) && ($ty < $sy))
            {
                $x = round($tx / 2);
                $y = round(($sy + $ty) / 2);
                ImageStringUp($im,$font,$x,$y,$message,$color);
            }
        }
    }

    function xscale($data,$xmax,$rmax)
    {
        $nx = round(($data * $xmax) / $rmax);
          return $nx;
    }

    function ticks($dmax)
    {
        $base = array(10,20,25,50);
        $good = array(1,2,5);
        $x = 1;
        $n = safe_count($base);
        while ($x < 1000000000)
        {
            for ($i = 0; $i < $n; $i++)
            {
               $good[] = $x * $base[$i];
            }
            $x = $x * 10;
        }

        $marks = array( );
        $range = round($dmax / 0.90);
        $x = closest(round($range / 8),$good);
        $n = 0;
        while ($n < $range)
        {
            $marks[] = $n;
            $n = $n + $x;
        }
        $marks[] = $n;
        bugspeak("dmax:$dmax, interval: $x, range:$range, end:$n");
        return $marks;
    }


    function length($s)
    {
        if (is_array($s))
        {
            $n = safe_count($s);
            for ($i = 0; $i < $n; $i++)
            {
                $len[] = strlen($s[$i]);
            }
        }
        else
        {
            $len = strlen($s);
        }
        return $len;
    }

    function truncate($s,$len)
    {
        if (is_array($s))
        {
            $n = safe_count($s);
            for ($i = 0; $i < $n; $i++)
            {
                if (strlen($s[$i]) > $len)
                    $x = substr($s[$i],0,$len);
                else
                    $x = $s[$i];
                $d[] = $x;
            }
        }
        else
        {
            if (strlen($s) > $len)
                $d = substr($s,0,$len);
            else
                $d = $s;
        }
        return $d;
    }


    function bargraph($args)
    {
        $numbers = $args['numbers'];
        $quality = $args['quality'];
        $server  = $args['server'];
        $names   = truncate($args['names'],60);
        $date    = $args['date'];
        $back_a  = $args['back'];
        $fore_a  = $args['fore'];
        $color_a = $args['colors'];
        $font    = $args['font'];
        $xaxis   = $args['xaxis'];
        $yaxis   = $args['yaxis'];
        $xfont   = $args['xfont'];

        $tx = imagefontwidth($font);
        $ty = imagefontheight($font);
        $qx = imagefontwidth($xfont);
        $qy = imagefontheight($xfont);

        $sx = 600;            $sy = 400;            $rx = 0;              $ry = 0;              $dx = 450;            $dy = 340;            $my = $ty+2;  
        $n   = safe_count($numbers);
        $nn  = safe_count($names);
        $len = length($names);

        $lx = $tx * (max($len)+3);
        $ly = $my * $n;

        $ex = $tx * 5;          $ey = $ty * 3;  
        if (strlen($xaxis)) $rx = 2 * $qy;
        if (strlen($yaxis)) $ry = 2 * $qy;
        if ($dy < $ly) $dy = $ly;

        $vx = $rx + $lx + $dx + $ex;
        $vy = $ry + $dy + $ey;

        if ($sx < $vx) $sx = $vx;
        if ($sy < $vy) $sy = $vy;

        $gxmin = $lx + $rx;
        $gxmax = $sx - $ex;
        $gymin = $ty;
        $gymax = $sy - (2*$ty + $ry);

        $dy = $gymax - $gymin;
        $dx = $gxmax - $gxmin;

        $im     = ImageCreate($sx,$sy);
        $back   = color_index($im,$back_a);
        $fore   = color_index($im,$fore_a);
        $colors = index_array($im,$color_a,$n);

        $nc   = safe_count($colors);
        $dmax = max($numbers);
        $dmin = min($numbers);

        if (($n <= 0) || ($dmax <= 0) || ($dmin < 0) || ($nn < $n) || ($nc <= 0))
        {
            bugspeak("n:$n, nn:$nn, nc:$nc, dmax:$dmax, dmin:$dmin, nn:$nn");
        }
        else
        {
            bugspeak("bargraph: sx:$sx, sy:$sy, tx:$tx, ty:$ty, qx:$qx, qy:$qy, n:$n, font:$font, dmax:$dmax");
            bugspeak("bargraph: dx:$dx, dy:$dy, gxmin:$gxmin, gxmax:$gxmax, gymin:$gymin, gymax:$gymax");

            $grey = ImageColorAllocate($im,190,190,190);
            ImageFilledRectangle($im,$gxmin,$gymin,$gxmax,$gymax,$grey);
            ImageRectangle($im,$gxmin,$gymin,$gxmax,$gymax,$fore);

            $marks = ticks($dmax);
            $rmax  = max($marks);
            $xmin = $gxmin;
            $xmax = $gxmax;
            $ymax = $gymax;

            $nm = safe_count($marks);
            for ($i = 0; $i < $nm; $i++)
            {
                $x = $xmin + xscale($marks[$i],$dx,$rmax);
                $label = sprintf("%d",$marks[$i]);
                $txt = $x - round($tx * (strlen($label)/2));
                ImageLine($im,$x,$gymin,$x,$gymax,$fore);
                ImageString($im,$font,$txt,$ymax,$label,$fore);
            }

            $n  = safe_count($numbers);
            $by = $dy / $n;
            $cy = $by / 2;

            if ($cy >  40) $cy = 40;
            if ($cy < $my) $cy = $my;
            if ($cy > $by) $cy = $by;

            $y  = $gymin;

            
            for ($i = 0; $i < $n; $i++)
            {
                $ycen = $y + ($by / 2);
                $ymin = round($ycen - $cy / 2);
                $ymax = round($ycen + $cy / 2);
                $cz   = $colors[$i % $nc];
                $txtx = $xmin - ($tx*($len[$i]+1));
                $txty = round($ycen - ($ty / 2));
                $x    = $xmin + xscale($numbers[$i],$dx,$rmax);
                ImageFilledRectangle($im,$xmin,$ymin,$x,$ymax,$cz);
                ImageRectangle($im,$xmin,$ymin,$x,$ymax,$fore);
                ImageString($im,$font,$txtx,$txty,$names[$i],$fore);
                if ($ty <= $cy)
                {
                    $val  = sprintf("%d",$numbers[$i]);
                    $vx   = $tx*(strlen($val)+1);
                    if ($vx <= $x - $xmin)
                        $txtx = round($x + $tx / 2 - $vx);
                    else
                        $txtx = round($x + $tx / 2);
                    $txty = round($ycen - $ty / 2);
                    ImageString($im,$font,$txtx,$txty,$val,$fore);
                }
                $y += $by;
            }
        }

        xaxis($im,$xaxis,$xfont,$fore);
        yaxis($im,$yaxis,$xfont,$fore);

        $txtx = $sx - (($tx * strlen($date)) + 1);
        $txty = $sy - ($ty + 1);
        ImageString($im,$font,$txtx,$txty,$date,$fore);

        return build_image($im,$sx,$sy,$quality,$server);
    }

    function color_array($r,$g,$b)
    {
        $color = array( );
        $color['red']   = $r;
        $color['green'] = $g;
        $color['blue']  = $b;
        return $color;
    }

    function color_index($im,$color)
    {
        $r = $color['red'];
        $g = $color['green'];
        $b = $color['blue'];
        return ImageColorAllocate($im,$r,$g,$b);
    }

    function index_array($im,$colors,$n)
    {
        $c = array( );
        $x = safe_count($colors);
        if ($n > $x) $n = $x;
        for ($i = 0; $i < $n ; $i++)
        {
            $c[] = color_index($im,$colors[$i]);
        }
        return $c;
    }

   

    function colors( )
    {
        $c   = array( );
        $c[] = color_array(255,255,  0);          $c[] = color_array( 78,238,148);          $c[] = color_array(240,248,255);          $c[] = color_array(255,165,  0);          $c[] = color_array(  0,255,255);          $c[] = color_array(187,255,255);          $c[] = color_array(  0,255,  0);          $c[] = color_array(255,255,170);          $c[] = color_array(255,155, 79);          $c[] = color_array(239,130, 66);          $c[] = color_array(255,215,  0);          $c[] = color_array(255,  0,255);          $c[] = color_array(  0,128, 64);          $c[] = color_array(130,130,130);          $c[] = color_array(248,248,255);          $c[] = color_array(255,193,193);          $c[] = color_array(233, 90, 37);          $c[] = color_array(100,229, 81);          $c[] = color_array(255,240,245);          $c[] = color_array(238, 18,137);          $c[] = color_array(240,255,240);          $c[] = color_array(238, 99, 99);          $c[] = color_array(135,206,235);          $c[] = color_array( 50,205, 50);          $c[] = color_array(107,142, 35);          $c[] = color_array(255,182,203);          $c[] = color_array(152,245,255);          $c[] = color_array(255,127, 80);          $c[] = color_array(219,219,219);          $c[] = color_array(255,222,173);          $c[] = color_array(255,245,238);          $c[] = color_array(255,250,205);          $c[] = color_array(255,  0,  0);          $c[] = color_array(240,230,140);          $c[] = color_array(127,255,212);          $c[] = color_array(127,255,  0);          $c[] = color_array(255,140,  0);          $c[] = color_array( 70,130,180);          $c[] = color_array(255,255,240);          $c[] = color_array(240,255,255);          $c[] = color_array(255,228,225);          $c[] = color_array(  0,191,255);          $c[] = color_array(211,211,211);          $c[] = color_array(148,  0,211);          $c[] = color_array(255, 48, 48);          $c[] = color_array(  0,  0,255);          $c[] = color_array(165, 42, 42);          $c[] = color_array(160, 32,240);          $c[] = color_array(128,  0,  0);          $c[] = color_array(  0,  0,140);          return $c;
    }


    function bargraph_image($numbers,$names,$label,$quality,$server)
    {
        $args['back']    = color_array(255,255,255);
        $args['fore']    = color_array(0,0,0);
        $args['colors']  = colors();
        $args['quality'] = $quality;
        $args['server']  = $server;
        $args['numbers'] = $numbers;
        $args['names']   = $names;
        $args['date']    = datestring(time());
        $args['font']    = 2;
        $args['xfont']   = 5;
        $args['xaxis']   = 'Events';
        $args['yaxis']   = $label;

        return bargraph($args);
    }





    function yscale($data,$xmax,$rmax)
    {
        $ny = round(($data * $xmax) / $rmax);
          return $ny;
    }


    function colgraph($args)
    {
        $numbers  = $args['numbers'];
        $quality  = $args['quality'];
        $server   = $args['server'];
        $names    = truncate($args['names'],60);
        $date     = $args['date'];
        $back_a   = $args['back'];
        $fore_a   = $args['fore'];
        $color_a  = $args['colors'];
        $font     = $args['font'];
        $xfont    = $args['xfont'];
        $xaxis    = $args['xaxis'];
        $yaxis    = $args['yaxis'];

        $tx = imagefontwidth($font);
        $ty = imagefontheight($font);
        $qx = imagefontwidth($xfont);
        $qy = imagefontheight($xfont);

        $sx = 600;            $sy = 400;            $rx = 0;              $ry = 0;              $dx = 500;            $dy = 310;            $my = $ty*2;  
        $n   = safe_count($numbers);
        $nn  = safe_count($names);
        $len = length($names);

        $dmax  = max($numbers);
        $dmin  = min($numbers);
        $marks = ticks($dmax);
        $rmax  = max($marks);

        if (strlen($yaxis)) $rx = 2 * $qy;
        if (strlen($xaxis)) $ry = 2 * $qy;

        $label = sprintf("%d",$rmax);
        $mx    = $tx * (strlen($label) + 1);
        $lx    = $ty * $nn;
        $width = $tx * (1+max($len));
        if ($lx > $dx) $dx = $lx;
        if ($width > $dx / $n)
        {
            $lx = $ty;
            $ly = $width;
            $turn  = 1;
        }
        else
        {
            $lx = $width;
            $ly = $ty * 2;
            $turn  = 0;
        }

        $ex  = $tx * 5;
        $ey  = $ty * 3;
        $vx  = $rx + $mx + $dx + $ex;
        $vy  = $ey + $dy + $ly + $ry;

        if ($sx < $vx) $sx = $vx;
        if ($sy < $vy) $sy = $vy;

        $gxmin = $rx + $mx;
        $gxmax = $sx - $ex;
        $gymin = $ty * 2;
        $gymax = $sy - ($ry + $ly);

        $dy = $gymax - $gymin;
        $dx = $gxmax - $gxmin;

        $im     = ImageCreate($sx,$sy);
        $back   = color_index($im,$back_a);
        $fore   = color_index($im,$fore_a);
        $colors = index_array($im,$color_a,$n);

        $nc   = safe_count($colors);
        if (($n <= 0) || ($dmax <= 0) || ($dmin < 0) || ($nn < $n) || ($nc <= 0))
        {
            bugspeak("n:$n, nn:$nn, nc:$nc, $dmax:$dmax, $dmin:dmin");
        }
        else
        {
            bugspeak("colgraph: dx:$dx, dy:$dy, gxmin:$gxmin, gxmax:$gxmax, gymin:$gymin, gymax:$gymax");

            $grey = ImageColorAllocate($im,190,190,190);
            ImageFilledRectangle($im,$gxmin,$gymin,$gxmax,$gymax,$grey);
            ImageRectangle($im,$gxmin,$gymin,$gxmax,$gymax,$fore);

            $xmin = $gxmin;
            $xmax = $gxmax;
            $ymax = $gymax;

           

            $nm = safe_count($marks);
            for ($i = 0; $i < $nm; $i++)
            {
                $y = $ymax - yscale($marks[$i],$dy,$rmax);
                $label = sprintf("%d",$marks[$i]);
                $txtx = $gxmin - ($tx * (1+strlen($label)));
                $txty = $y - round($ty / 2);
                ImageLine($im,$xmin,$y,$xmax,$y,$fore);
                ImageString($im,$font,$txtx,$txty,$label,$fore);
            }

           

            $bx = $dx / $n;
            $cx = $bx / 2;

            if ($cx >  40) $cx = 40;
            if ($cx < $my) $cx = $my;
            if ($cx > $bx) $cx = $bx;

            $x  = $gxmin;
            $vlen = length($numbers);
            $horz = ((max($vlen)+1) * $tx) <= $cx;

            for ($i = 0; $i < $n; $i++)
            {
                $cz   = $colors[$i % $nc];
                $xcen = $x + $bx / 2;
                $xmin = round($xcen - $cx / 2);
                $xmax = round($xcen + $cx / 2);
                $y    = $ymax - yscale($numbers[$i],$dy,$rmax);
                $name = $names[$i];
                bugspeak("$i:$name, xmin:$xmin, xmax:$xmax, ymin:$y, ymax:$ymax, cz:$cz");
                ImageFilledRectangle($im,$xmin,$y,$xmax,$ymax,$cz);
                ImageRectangle($im,$xmin,$y,$xmax,$ymax,$fore);
                $vx = $tx * $len[$i];
                if ($turn)
                {
                    $txtx = round($xcen - $ty / 2);
                    $txty = round($gymax + $vx + $tx);
                    ImageStringUp($im,$font,$txtx,$txty,$name,$fore);
                }
                else
                {
                    $txtx = round($xcen - $vx / 2);
                    $txty = round($ymax + $ty / 2);
                    ImageString($im,$font,$txtx,$txty,$name,$fore);
                }

                if ($horz)
                {
                    $vx  = $tx * $vlen[$i];
                    $vy  = $ty * 2;
                    if ($vy <= $ymax - $y)
                        $txty = round($y + $ty / 2);
                    else
                        $txty = round($y - $ty);
                    $txtx = round($xcen - $vx / 2);
                    ImageString($im,$font,$txtx,$txty,$numbers[$i],$fore);
                }
                else
                {
                    if ($ty <= $cx)
                    {
                        $vx = $tx * ($vlen[$i]+1);
                        if ($vx <= $ymax - $y)
                            $txty = round($y + $vx);
                        else
                            $txty = round($y - ($vx - $tx));
                        $txtx = round($xcen - $ty / 2);
                        ImageStringUp($im,$font,$txtx,$txty,$numbers[$i],$fore);
                    }
                }
                $x += $bx;
            }
        }
        xaxis($im,$xaxis,$xfont,$fore);
        yaxis($im,$yaxis,$xfont,$fore);

        $txtx = $sx - (($tx * strlen($date)) + 1);
        $txty = $sy - ($ty + 1);
        ImageString($im,$font,$txtx,$txty,$date,$fore);

        return build_image($im,$sx,$sy,$quality,$server);
    }


    function colgraph_image($numbers,$names,$label,$quality,$server)
    {
        $args['back']    = color_array(255,255,255);
        $args['fore']    = color_array(0,0,0);
        $args['colors']  = colors();
        $args['quality'] = $quality;
        $args['server']  = $server;
        $args['numbers'] = $numbers;
        $args['names']   = $names;
        $args['date']    = datestring(time());
        $args['font']    = 2;
        $args['xfont']   = 5;
        $args['xaxis']   = $label;
        $args['yaxis']   = 'Events';

        return colgraph($args);
    }






    function sum($numbers)
    {
        $total = (double) 0;
        $n = safe_count($numbers);
        for ($i = 0; $i < $n; $i++)
        {
            $term = $numbers[$i];
            $total += $term;
            bugspeak("number[$i]: $term");
        }

        bugspeak("total: $total");

        return $total;
    }


    function permill($x)
    {
        return round($x*1000);
    }

    function percent($p,$x)
    {
        return round(($p*$x) / 100);
    }

    function xcoord($x,$r,$theta)
    {
        $nx = round($x + ($r * sin($theta)));
           return $nx;
    }

    function ycoord($y,$r,$theta)
    {
        $ny = round($y - ($r * cos($theta)));
           return $ny;
    }

    function twopi($x)
    {
        $nx = pi() * $x * 2;
            return $nx;
    }


    function piechart($args)
    {
        $sx = 600;
        $sy = 400;
        $r  = 160;
        $d  = $r * 2;

        $numbers  = $args['numbers'];
        $quality  = $args['quality'];
        $server   = $args['server'];
        $names    = truncate($args['names'],60);
        $back_a   = $args['back'];
        $fore_a   = $args['fore'];
        $color_a  = $args['colors'];
        $date     = $args['date'];
        $font     = $args['font'];

        $n    = safe_count($numbers);
        $nn   = safe_count($names);
        $tx   = imagefontwidth($font);
        $ty   = imagefontheight($font);
        $len  = length($names);
        $vlen = length($numbers);

        $lx  = (max($len)+2)*$tx;
        $ly  = ($ty+2) * $nn;
        $bx  = $ty * 2;
        $rx  = 2 * ($tx * ((max($vlen) + 2)));

        $vy  = ($ty * 4) + $ly;
        $vx  = $d + $rx + $bx + $lx;

        if ($vy > $sy) $sy = $vy;
        if ($vx > $sx) $sx = $vx;

        $x = round (($sx - ($bx + $lx)) / 2);
        $y = round ($sy / 2);

        bugspeak("sx:$sx, sy:$sy, x:$x, y:$y, d:$d, r:$r");

        $im       = ImageCreate ($sx, $sy);
        $back     = color_index($im,$back_a);
        $fore     = color_index($im,$fore_a);
        $colors   = index_array($im,$color_a,$n);

        $nc      = safe_count($colors);
        $dmin    = min($numbers);
        $total   = sum($numbers);

       

        if (($n <= 0) || ($total <= 0) || ($nn < $n) || ($dmin < 0))
        {
            bugspeak("n:$n, nn:$nn, nc:$nc, dmin:$dmin, total:$total");
        }
        else
        {
            bugspeak("n:$n, total:$total");

            $dy  = round(($sy - ($ty*4)) / $nn);
            $yy  = round(($ty * 2) + ($dy / 2) - ($ty / 2));
            $xx  = $sx - $lx;
            $xmax = $xx - $tx;
            $xmin = $xmax - $ty;
            for ($i = 0; $i < $nn; $i++)
            {
                $ymin = $yy;
                $ymax = $ymin + $ty;
                $cz   = $colors[$i % $nc];
                ImageString($im,$font,$xx,$ymin,$names[$i],$fore);
                ImageFilledRectangle($im,$xmin,$ymin,$xmax,$ymax,$cz);
                ImageRectangle($im,$xmin,$ymin,$xmax,$ymax,$fore);
                $yy += $dy;
            }

            $nx = array( );
            $ny = array( );
            $an = array( );
            $pc = array( );

           

            $px    = 0;
            $theta = twopi($px);
            $tp    = twopi($total);
            $an[]  = $theta;
            $nx[]  = xcoord($x,$r,$theta);
            $ny[]  = ycoord($y,$r,$theta);
            for ($i = 0; $i < $n; $i++)
            {
                $arc   = twopi($numbers[$i]);
                $px   += $arc;
                $theta = $px / $total;
                $an[]  = $theta;                                  $nx[]  = xcoord($x,$r,$theta);                    $ny[]  = ycoord($y,$r,$theta);                    $pc[]  = permill($arc / $tp);                 }

           

            imagearc($im,$x,$y,$d,$d,0,360,$fore);

           

            if ($n > 1)
            {
                for ($i = 0; $i < $n; $i++)
                {
                    imageline($im,$x,$y,$nx[$i],$ny[$i],$fore);
                }
            }

           

            $tx = imagefontwidth($font);
            $ty = imagefontheight($font);
            bugspeak("font:$font, tx:$tx, ty:$ty");
            for ($i = 0; $i < $n; $i++)
            {
                $theta = ($an[$i] + $an[$i+1]) / 2;
                if ($pc[$i] > 8)
                {
                    $cx = xcoord($x,$r*0.8,$theta);
                    $cy = ycoord($y,$r*0.8,$theta);
                    $cz = $colors[$i % $nc];
                    bugspeak("theta:$theta, cx:$cx, cy:$cy, cz:$cz");
                    imagefilltoborder($im,$cx,$cy,$fore,$cz);
                }
                if ($pc[$i] > 12)
                {
                    $text = sprintf("%d",$numbers[$i]);
                    $cx = xcoord($x,$r*1.1,$theta);
                    $cy = ycoord($y,$r*1.1,$theta);
                    $lx = $cx - round(($tx * strlen($text))/2);
                    $ly = $cy - round($ty / 2);
                    imagestring($im,$font,$lx,$ly,$text,$fore);
                    bugspeak("lx:$lx, ly:$ly, text:$text");
                       }
            }
        }

        $txtx = $sx - (($tx * strlen($date)) + 1);
        $txty = $sy - ($ty + 1);
        ImageString($im,$font,$txtx,$txty,$date,$fore);

        return build_image($im,$sx,$sy,$quality,$server);
    }


    function piechart_image($numbers,$names,$quality,$server)
    {
        $args['back']    = color_array(255,255,255);
        $args['fore']    = color_array(0,0,0);
        $args['colors']  = colors();
        $args['quality'] = $quality;
        $args['server']  = $server;
        $args['numbers'] = $numbers;
        $args['names']   = $names;
        $args['date']    = datestring(time());
        $args['font']    = 2;
        return piechart($args);
    }



   

    function chart_image($format,$numbers,$names,$label,$quality,$server)
    {
        $img = array( );
        switch ($format)
        {
            case 'text':
                break;
            case 'html':
                break;
            case 'mpie': ;
            case 'pie' : ;
                $img = piechart_image($numbers,$names,$quality,$server);
                break;
            case 'mbar': ;
            case 'bar' : ;
                $img = bargraph_image($numbers,$names,$label,$quality,$server);
                break;
            case 'mcol': ;
            case 'column':
                $img = colgraph_image($numbers,$names,$label,$quality,$server);
                break;
        }
        return $img;
    }


    function image_path(&$img)
    {
        $path = '';
        if ($img)
        {
            $xx  = $img['xsize'];
            $yy  = $img['ysize'];
            $bb  = $img['bsize'];
            $cid = $img['cid'];
            bugspeak("cid:$cid");

            $good = false;
            $name = tmpname($img['time']);
            $fyle = fopen($name,'wb+');
            if ($fyle)
            {
                $good = fwrite($fyle,$img['image']);
                if ($good)
                {
                    $good = fclose($fyle);
                }
                else
                {
                    fclose($fyle);
                    unlink($name);
                }
            }
            if ($good)
            {
                $path = $name;
            }
            else
            {
                            }
            bugspeak("xsize:$xx, ysize:$yy, bsize:$bb, name:$name");
        }
        return $path;
    }


    function image_href(&$img)
    {
        $txt = '';
        if ($img)
        {
            $xxx = $img['xsize'];
            $yyy = $img['ysize'];
            $cid = $img['cid'];
            $txt = <<< HERE

<img src="cid:$cid"
  border="0" width="$xxx" height="$yyy">

HERE;

        }
        return $txt;
    }


   

    function image_encode(&$img)
    {
        $txt = '';
        if ($img)
        {
            set_magic_quotes_runtime(0);
            $txt = chunk_split(base64_encode($img['image']));
        }
        return $txt;
    }


    function loadfile($name,&$size,&$data)
    {
        $good = false;
        $data = '';
        $size = filesize($name);
        if ($size > 0)
        {
            $file = fopen($name,'r');
            if ($file)
            {
                set_magic_quotes_runtime(0);
                $data = fread($file,$size);
                if (fclose($file))
                {
                    if (strlen($data) == $size)
                    {
                        $good = true;
                    }
                }
            }
        }
        if (!$good)
        {
                        $size = 0;
            $data = '';
        }
        return $good;
    }


    function logo_image($logo,$server)
    {
        $img = array( );
        if ($logo)
        {
            $size = 0;
            $data = '';
            $root = server_var('DOCUMENT_ROOT');
            $path = $root . $logo['src'];

            
                                                
            if (loadfile($path,$size,$data))
            {
                $name = basename($path);
                $type = image_type($name);
                $time = getmicro(microtime());
                $img['cid']   = build_cid($time,$server);
                $img['name']  = $name;
                $img['type']  = $type;
                $img['xsize'] = $logo['xxx'];
                $img['ysize'] = $logo['yyy'];
                $img['bsize'] = $size;
                $img['image'] = $data;
            }
        }
        return $img;
    }

?>
