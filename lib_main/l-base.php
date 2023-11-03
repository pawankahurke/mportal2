<?php


        
   

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

   

    function server_href($db)
    {
        $host = server_name($db);
        $ssl  = server_int('ssl',1,$db);
        $def  = ($ssl)? 443 : 80;
        $port = server_int('port',$def,$db);
        return server_path($host,$port,$ssl);        
    }


   

    function base_directory($host,$comp)
    {
        $odir = $comp['odir'];
        return ($odir)? "$host/$odir" : $host;
    }


?>

