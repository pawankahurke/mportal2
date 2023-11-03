<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
21-Nov-05   NL      Creation
*/

    function dashbrd_nav() 
    {
        $local_nav  = '';
        $local_nav .= "dashboard:\n";       
        $local_nav .= "<a href='display.php'>display</a> |\n";
        $local_nav .= "<a href='wizard.php'>wizard</a>\n";
        $local_nav .= "<br><br>\n";
        return $local_nav;
    }    

?>
