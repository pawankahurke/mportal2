<?php




    function fwrite_error($len,$wsize)
    {
        $stat = "fwrite: IO failure wrote $len of $wsize";
            }


    function my_writesize($handle,$msg)
    {
        $len   = strlen($msg);
        $wsize = fwrite($handle,$msg);

        

        if ($wsize === false) $wsize = -1;
        if ( $wsize == $len )
        {
            return $wsize;
        }

        fwrite_error($len,$wsize);
        fclose($handle);
        return $wsize;
    }

    function my_write($handle,$msg)
    {
        $res = my_writesize($handle,$msg);
        if ($res == strlen($msg))
            return true;
        else
            return false;
    }

    
    function append_file($new_handle,$old_handle,$message)
    {
        $good = my_write($new_handle, $message);
        if ($good)
        {
            if ($old_handle)
            {
                rewind($old_handle);
                while(!feof($old_handle))
                {
                    $chunk = fread($old_handle, 524288);
                    if (!my_write($new_handle, $chunk)) return false;
                }
            }
        }
        return $good;
    }


    
    function append_tmp_file($old_handle,$message)
    {
        $new_handle = tmpfile();
        if ($new_handle)
        {
            if (my_write($new_handle, $message))
            {
                if ($old_handle)
                {
                    rewind($old_handle);
                    while(!feof($old_handle))
                    {
                        $data = fread($old_handle, 524288);
                        if (!my_write($new_handle, $data)) return false;
                    }
                }
            }
        }
        return $new_handle;
    }

?>
