<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
24-Jan-03   EWB     Extended copyright date to 2003.
21-Feb-03   EWB     Contents removed.
21-Apr-03   EWB     Send along the database.
10-Oct-06   WOH     Added foot_get_user_footer()

*/


   /*
    |  We now have to look for user specific footer information
    |  Using "user" get the footer information for reports.
    */
    function foot_get_user_footer($user,&$env,$mdb)
    {
        /* Put the footer info into the "env" array for use later */
        $env['foot'] = head_standard_html_footer($user,$mdb);    
        return $env;        
    }


?>
