<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once('./common.php');

    
    if($version[0] != ''){
        
        $dashid = url::requestToAny('id');
        $submit = url::requestToAny('type');
        
    if($dashid && !$submit){
        $stmt = $pdo->query("SELECT `schema` from `analytics`.`schema` WHERE id = ".$dashid." ");
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        print_r($rows['schema']);
    }else{
        $stmt = $pdo->query("SELECT `schema` from `analytics`.`schema` WHERE id = ".$dashid." ");
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            
        $jsonData = safe_json_decode($rows['schema'],true);
        
        $FinalArr = array();
        $cubeNameArr = array();
        
        if($submit == 'submit' || $dashid){
            foreach($jsonData as $key => $val){
            $cubename = $val['cubename'];
            array_push($cubeNameArr,$cubename);
            $query = "CREATE TABLE schemas.$cubename ( ";
            
            $measure = $val['measures'];
            $dimensions = $val['dimensions'];
            $measureArr = '';
            $dimensionArr = '';
            
            foreach($measure as $k1=>$v1){
                $type = "INT";
                $sql = $v1['sql'];
                $measurename = $v1['title'];
                $title = $v1['displaytitle'];
                
                $query .= "$measurename" . " " . "$type" . "(100),"  ;
                
            }
            
            foreach($dimensions as $k2=>$v2){
                $type = "VARCHAR";
                $sql = $v2['sql'];
                $dimensionname = $v2['title'];
                $title = $v2['displaytitle'];
                
                $query .= "$dimensionname" . " " . "$type" . "(10)".","  ;
            }
            
            $query = rtrim($query,",");
            $query .= ")
            COLLATE='latin1_swedish_ci'
            ENGINE=InnoDB; ";
            
            array_push($FinalArr,$query);
            
            }

            foreach($FinalArr as $k3=>$v3){
                $stmt = $pdo->query($v3);
            }
                    print_r(json_encode($cubeNameArr));

            }
        }
    } 
    
?>