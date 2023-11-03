<?php




   
    function foot_get_user_footer($user,&$env,$mdb)
    {
        
        $env['foot'] = head_standard_html_footer($user,$mdb);    
        return $env;        
    }


?>
