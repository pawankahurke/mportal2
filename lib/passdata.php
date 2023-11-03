<?php


function print_data($data){
    echo $data;
}

function print_json_data($data){
    echo json_encode($data);
}

function md5_data($data){
    $ftnConn      = new FTP();
    return $ftnConn->md5_pass($data);
}

function base64_data($data){
    echo base64_encode($data);
}

function connect_db($db_host_master,$db_user,$db_password){
    $ftnConn      = new FTP();
    return $ftnConn->dbstmt($db_host_master, $db_user, $db_password);

    
}

function generate_session(){
    $ftnConn      = new FTP();
    return $ftnConn->md5_session();
}

function generate_ftpdata($file_path,$fileName){
    $ftnConn      = new FTP();
    return $ftnConn->ftp_data($file_path,$fileName);
}

?>