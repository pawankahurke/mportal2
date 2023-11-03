<?php



    
function find_record_id($table,$id,$db)
{
    $row = array( );
    $sql = "select * from $table where id = $id";
    $res = command($sql, $db);
    if ($res) 
    {
        if (mysqli_num_rows($res) == 1)
        {
            $row = mysqli_fetch_assoc($res);
        }
        mysqli_free_result($res);
    }    
    return $row;
}



function check_nonowner_edit($authuser, $table_name, $id, $db) 
{   
    $nonowner_edit = 0;
    $row = find_record_id($table_name,$id,$db);
    if ($row) 
    {
        $global   = $row['global'];
        $username = $row['username'];  
        if (($global) && ($username != $authuser))
            $nonowner_edit = 1;
        else
            $nonowner_edit = 0;
    }    
    return $nonowner_edit;  
}

function check_existing_name($table_name, $name, $username, $db) 
{   
    $exists = 0;
    $qname = safe_addslashes($name);
    $sql  = "SELECT name FROM $table_name"; 
    $sql .= " WHERE name = '$qname' AND";
    $sql .= " username = '$username'";   
    $res  = command($sql, $db);
    if ($res)
    {
        $exists = (mysqli_num_rows($res)) ? 1 : 0;
        mysqli_free_result($res);
    }       
    return $exists;
}

function check_existing_name_asset($name,$username,$id,$db) {   
    $exists = 0;
    $sql    = "SELECT name FROM AssetSearches"; 
    $sql   .= " WHERE name = '$name' AND";
    $sql   .= " username = '$username'";  
    $sql   .= " AND expires = 0";
    if (strlen($id)) {
        $sql   .= " AND id != $id";
    }

    $res = command($sql, $db);
    if ($res)
    {
        $exists = (mysqli_num_rows($res)) ? 1 : 0;
        mysqli_free_result($res);
    }       
    return $exists;
}


function check_can_make_search_local($currentuser,$search_id,$db) 
{
    $answer = 1;
    $sql    = "SELECT id, name, username FROM Notifications";
    $sql   .= " WHERE search_id = $search_id";
    $sql   .= " AND ( (username != '$currentuser') OR (global = 1) )";
    $result = command($sql, $db);
    if ($result)
    {
        $answer = (mysqli_num_rows($result)) ? 0 : 1;
    }
    if ($answer)
    {
        $sql  = "SELECT id, name, username FROM Reports";
        $sql .= " WHERE search_list like '%,$search_id,%'";
        $sql .= " AND ( (username != '$currentuser') OR (global = 1) )";
        $result = command($sql, $db);
        if ($result)
        {
            $answer = (mysqli_num_rows($result)) ? 0 : 1;   
        }
    }     
    return $answer;
}

function check_can_delete_search($table_name,$search_id,$db) 
{
    $delete = 1;
    $dataset = strstr($table_name,"Asset")? "asset" : "event";
    

    if ($dataset != "asset") 
    {
        $sql    = "SELECT id, name, username FROM Notifications";
        $sql   .= " WHERE search_id = $search_id";
        $result = command($sql, $db);
        if ($result) 
        {
            $delete = (mysqli_num_rows($result)) ? 0 : 1;           
        }
    }
    if ($delete)
    {
        if ($dataset == "asset")
        {
            $sql  = "SELECT id, name, username FROM AssetReports";
            $sql .= " WHERE searchid = $search_id";
        }
        else
        {
            $sql  = "SELECT id, name, username FROM Reports";
            $sql .= " WHERE search_list like '%,$search_id,%'";
        }
        $result = command($sql, $db);
        if ($result) 
        {   
            $delete = (mysqli_num_rows($result)) ? 0 : 1;   
        }    
    }
    return $delete;
}



function check_can_delete_item($table_name, $authuser, $item_id, $db) 
{ 
    $delete = "ok";
    $row  = find_record_id($table_name,$item_id,$db);
    $user = ($row)? $row['username'] : ''; 
    if ($user != $authuser) 
    {
                $delete = "nonowner";
    } 
    else 
    {
                if (strstr($table_name,"Searches"))  
        {
            $can_delete_search = check_can_delete_search ($table_name,$item_id,$db); 
            if ($can_delete_search)
                $delete = "ok";
            else
                $delete = "dependencies";
        }
    }   
    return $delete;
}
    

function get_name($table_name, $id, $db) 
{   
    $row  = find_record_id($table_name,$id,$db);
    return ($row)? $row['name'] : '';
}
