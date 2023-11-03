<?php

/* Revision History
[l-rtxt.php needs this file]

When:       Who:   What:
-----       ----   ----

11-Jul-05   BJS    created.
12-Jul-05   BJS    added error checking to append_file()
                   and append_tmp_file().
13-Jul-05   BJS    added my_write & my_writesize.
14-Jul-05   BJS    improved my_write().
15-Jul-05   BJS    cleaned up.
18-Jul-05   BJS    my_write calls my_writesize, added debug code.
*/


    function fwrite_error($len,$wsize)
    {
        $stat = "fwrite: IO failure wrote $len of $wsize";
        logs::log(__FILE__, __LINE__, $stat,0);
    }


    function my_writesize($handle,$msg)
    {
        $len   = strlen($msg);
        $wsize = fwrite($handle,$msg);

        /*  <debug> 
        $rand = mt_rand(0,100);
        if ($rand < 10)
            $wsize = false;
        */

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

    /* similiar to append_tmp_file, except no tmp files are used.
       write the message to new_handle
       loop on old_handle
         copy 64k chunk from old_handle
         write 64k chunk to new_handle
    */
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


    /* create a new_handle
       write message to begining of new_handle.
       loop on old_handle:
           read 64k chunk of old_handle.
           write 64k chunk to new_handle.
    */
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
