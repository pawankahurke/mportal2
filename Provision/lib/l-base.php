<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
14-Oct-02   EWB     Created.
13-Dec-02   EWB     Addded base_directory()
16-Jan-03   EWB     Access to $_SERVER variables.
 6-Jan-04   EWB     Support for PHP 4.2.2.
 7-Jan-04   EWB     uses `/bin/hostname -f` instead
 9-Jan-04   EWB     server name.
23-Mar-04   EWB     added server_href, server_path, removed base_name.
*/
        
   /*
    |  Generate the header for a url on this server.
    |  This can be either http or https, depending.
    |
    |  Note $SERVER_NAME does *NOT* include the port
    |  number when running on a non-standard port.
    |
    |  $HTTP_HOST does include the nonstandard port, 
    |  but it just returns "localhost" when run 
    |  from the cron job.  So, we just do the the 
    |  best we can.
    |
    |  In php 4.2.2 server_name just returns localhost
    |  when run via curl for the cron jobs ...
    */

    function server_path($host,$port,$ssl)
    {
        if ((($ssl == 1)  && ($port != 443)) ||
            (($ssl == 0)  && ($port != 80)))
        {
            $host = "$host:$port";
        }
        $protocol = ($ssl)? "https" : "http";
        return "$protocol://$host";
    }

   /*
    |  Generates a url which represents 
    |  this server.  For example:
    |
    |      https://hfndev.com:9443
    */

    function server_href($db)
    {
        $host = server_name($db);
        $ssl  = server_int('ssl',1,$db);
        $def  = ($ssl)? 443 : 80;
        $port = server_int('port',$def,$db);
        return server_path($host,$port,$ssl);        
    }


   /*
    |  This does the same thing that base_name() does, 
    |  except that it also includes the installed 
    |  directory name.
    |
    |     https://hfndev.com:9443/eric/dev/server
    */

    function base_directory($host,$comp)
    {
        $odir = $comp['odir'];
        return ($odir)? "$host/$odir" : $host;
    }


?>

