<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
22-Nov-02   NL      create file 
25-Nov-02   NL      delete a machine
 3-Dec-02   NL      change titles & return page link 
 4-Dec-02   EWB     Reorginization Day
16-Jan-03   EWB     Don't require register_globals
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
 5-Jun-03   EWB     Switch database to swupdate.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
 
*/
 
    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    ); 
include_once ( '../lib/l-sql.php'   );    
include_once ( '../lib/l-serv.php'  );  
include_once ( '../lib/l-rcmd.php'  );       
include_once ( '../lib/l-head.php'  );
include_once ( 'header.php' ); 
    
    
    function go_back($siteid)
    {
        $href = "mu-list.php?siteid=$siteid";
        $text = 'Machine Update Status List';
        $link = html_link($href,$text);
        echo "<p>Return to the $link.</p>\n";
    }
      
     
    function error_generic($db)
    {
?>
        <br><span class="red">
        There was a problem with your submission. Please try again.
        </span>
<?php
    }

    function error_dup_record($machine,$sitename) 
    {
        $error = "<br>There is already a record with machine <b>$machine</b> and site <b>$sitename</b>.";  
        return $error;
    }    

    function add_success($machine)
    {  
?>         
        <br>
        You have added a record for machine <b><?php echo $machine ?></b>.       
        <br><br>
<?php
    }
               

    function edit_success($machine)
    {
?>                       
        <br>
        You have updated the record for machine <b><?php echo $machine ?></b>.     
        <br><br>
<?php

    }

    
    function delete_success($machine)
    {
?>                     
        <br>
        You have deleted the record for machine <b><?php echo $machine ?></b>.
        <br><br>
<?php
    }



    function confirm_delete($machine,$id,$siteid)
    {
        $self = server_var('PHP_SELF');
?>            
        <br>
        Do you really want to delete the record for machine <b><?php echo $machine ?></b>?
        <br><br>
         
<?php 
        $href = "$self?action=delete&id=$id"; 
        echo "<a href='$href'>[Yes]</a>"; 
?>
        &nbsp;&nbsp;
        <a href='mu-list.php?siteid=<?php echo $siteid ?>'>[No]</a>
<?php    
    }
                  

    function delete($id,$machine,$siteid,$db) 
    { 
        $good = 0;
        
        $sql = "DELETE FROM UpdateMachines WHERE id = $id";

        if (redcommand($sql, $db))
        {
            if (mysqli_affected_rows($db))
            {
                $good = 1;  
            }
        }            


        if ($good) 
        {
            delete_success($machine);
            go_back($siteid);
        } 
    }

  
    function add($sitename,$machine,$force,$siteid,$db)
    {    
        $good = 0;
      
        $sql  = "INSERT INTO UpdateMachines";
        $sql .= " SET sitename='" . $sitename . "'";         
        $sql .= " , machine='" . $machine . "'";
        $sql .= " , doforce=" . $force;
          
           
        if (redcommand($sql, $db)) 
        {
            if (mysqli_affected_rows($db))
            {
                $good = 1;
            }
        }
           
        if ($good) 
        {
            add_success($machine);
            go_back($siteid);
        }
    }
                

    function edit($id,$sitename,$machine,$force,$siteid,$db)
    {      
        $good = 0;

        $sql  = "UPDATE UpdateMachines";
        $sql .= " SET sitename='" . $sitename . "'";         
        $sql .= " , machine='" . $machine . "'";     
        $sql .= " , doforce=" . $force;           
        $sql .= " WHERE id = $id";   
    
        $result = redcommand($sql, $db); 
        if ($result) 
        {        
            $good = 1;
        }                                                                                                         
                               
        if ($good) 
        {
            edit_success($machine);
            go_back($siteid);
        } 
        else 
        {
            error_generic($db);
        }       
    }

   
   /*
    |  Main program
    */
    
    $db = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();
    
    $action = trim(get_argument('action',0,'none'));
    $id     = intval(get_argument('id',0,0));

    switch ($action)
    {
        case 'add'            : $title = "Machine Update Record Added";   break;    
        case 'edit'           : $title = "Machine Update Record Updated"; break;
        case 'confirmdelete'  : $title = "Delete A Machine Update Record";break;
        case 'delete'         : $title = "Machine Update Record Deleted"; break;
        default               : $title = "Machine Update Record";         break;
    }

    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer) 
    echo standard_html_header($title,$comp,$authuser,$local_nav,0,0,$db);
    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users 

    $error    = ''; 
    $machine  = trim(get_argument('machine',1,''));
    $sitename = trim(get_argument('sitename',1,''));
    $force    = trim(get_argument('force',1,''));
     
?>
    <table><tr><td>
<?php    

    
   /*
    |   Perform error checking
    */   

    
    db_change($GLOBALS['PREFIX'].'swupdate',$db);

    if (($id <= 0) && ($action != 'add')) 
    {
        $error .= "<br>Error -- no id specified.";
    }
    
    if ($action == 'none') 
    {
        $error .= "<br>Error -- no action specified.";
    }
    
    if ($action == 'add' || $action == 'edit') 
    {    
    // prevent record with the same sitename and machine name as an existing record.
        $sql  = "SELECT * FROM UpdateMachines ";
        $sql .= "WHERE sitename = '$sitename' AND machine = '$machine'";         
    
        $result = redcommand($sql, $db); 
        if ($result && mysqli_num_rows($result) > 0) 
        {        
            $error .= error_dup_record($machine,$sitename);
        }          

    }
   
    if ($error) 
    { 
        echo "<span class=red>$error</span>\n";
    } 
    else 
    {

        if (($action == 'confirmdelete') || ($action == 'delete')) 
        {

           /*
            |   Get existing values from the database (not from the form)
            */
        
            $good = 0;
            
            $sql = "SELECT machine, sitename from UpdateMachines WHERE id = $id"; 
            $result = redcommand($sql, $db);
        
            if ($result) 
            {
                if (mysqli_num_rows($result) == 1)
                {
                    $row        = mysqli_fetch_array($result);       
                    $machine    = $row['machine']; 
                    $sitename   = $row['sitename'];        
                }
            }
    
            $sql = "SELECT id from UpdateSites WHERE sitename = '$sitename'"; 
            $result = redcommand($sql, $db);
      
            if ($result) 
            {
                if (mysqli_num_rows($result) > 0)
                {
                    $row    = mysqli_fetch_array($result);       
                    $siteid = $row['id'];          
                }
            }
            
        }                 
             
    
        if ($action == 'add') 
        {                            
            add($sitename,$machine,$force,$db);
        }  
        
        if ($action == 'edit') 
        {                 
            edit($id,$sitename,$machine,$force,$db); 
        }
        
        if ($action == 'confirmdelete') 
        {
            confirm_delete($machine,$id,$siteid);
        }
 
        if ($action == 'delete') 
        {
            delete($id,$machine,$siteid,$db);
        }                                  
    }       
            
?>
    </td></tr></table>
<?php
    echo head_standard_html_footer($authuser,$db);
?>
