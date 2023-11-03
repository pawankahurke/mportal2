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
        $files = array();
        
        if($submit == 'submit' || $dashid){
            foreach($jsonData as $key=>$val){
                $measuresArr = '';
                $dimensionArr = '';
                $segmentArr = '';
                $preAggArr = '';
                
                $schemaTemp = $val['schematemplate'];
                $selecttext = $val['selecttext'];
                $dataid = $val['dataid'];
                $scrip = $val['scripno'];
                $measures = $val['measures'];
                $dimensions= $val['dimensions'];
                $preAgg = $val['pre_agg'];
                $segments = $val['segments'];
                $cubename = $val['cubename'];
                
                $query = "cube(`".$cubename."`, {".PHP_EOL;
                
                if($schemaTemp == 'asset'){
                    $sqlVal = "`"."SELECT id,from_unixtime(slatest,'%y-%m-%dT%H:%i:%s') as 'slatest',machineid, $selecttext,max(slatest) from ".$GLOBALS['PREFIX']."asset.AssetData where dataid = $dataid"."`,";
                }else if($schemaTemp == 'event'){
                    $sqlVal = "`"."SELECT idx,from_unixtime(servertime,'%y-%m-%dT%H:%i:%s') as 'servertime',customer,machine,username, $selecttext from  ".$GLOBALS['PREFIX']."event.Events where scrip = $scrip"."`,";
                }else if($schemaTemp == 'compliance'){
                    $sqlVal = "`"."SELECT id,from_unixtime(servertime,'%y-%m-%dT%H:%i:%s') as 'servertime',machine , site,username,$selecttext from  ".$GLOBALS['PREFIX']."event.Console"."`,";
                }
                
                $query .= "sql:".$sqlVal.PHP_EOL;
                
                $query .= "refreshKey: {".PHP_EOL
                            ."every: `60 second`".PHP_EOL
                            ."},".PHP_EOL;
                
                foreach($measures as $k1=>$v1){
                    $title = $v1['title'];
                    $type = "`".$v1['type']."`";
                    $sql =  "`".$v1['sql']."`";
                    $displaytitle = "`".$v1['displaytitle']."`";
                    
                    $measureJSON = PHP_EOL."$title: {".PHP_EOL
                            ."type: ".$type.",".PHP_EOL
                            ."sql: ".$sql.",".PHP_EOL
                            ."title: ".$displaytitle.PHP_EOL
                          ."},";
                    
                    $measuresArr .= $measureJSON;
                  }
                 
                $query .= "measures:{".rtrim($measuresArr,",").PHP_EOL."},".PHP_EOL;

                foreach($dimensions as $k2=>$v2){
                    $title = $v2['title'];
                    $type = "`".$v2['type']."`";
                    $sql =  "`".$v2['sql']."`";
                    $displaytitle = "`".$v2['displaytitle']."`";
                    
                    $dimensionJSON = PHP_EOL."$title: {".PHP_EOL
                            ."type: ".$type.",".PHP_EOL
                            ."sql: ".$sql.",".PHP_EOL
                            ."title: ".$displaytitle.PHP_EOL
                          ."},";
                    
                    $dimensionArr .= $dimensionJSON;
                  }
                
                $query .= "dimensions:{".rtrim($dimensionArr,",").PHP_EOL."},".PHP_EOL;
                  
                foreach($segments as $k3=>$v3){
                    $title = $v3['title'];
                    $type = "`".$v3['type']."`";
                    $sql =  "`".$v3['sql']."`";
                    $displaytitle = "`".$v3['displaytitle']."`";
                    
                    $segmentJSON = PHP_EOL."$title: {".PHP_EOL
                            ."type: ".$type.",".PHP_EOL
                            ."sql: ".$sql.",".PHP_EOL
                            ."title: ".$displaytitle.PHP_EOL
                          ."},";
                    
                    $segmentArr .= $segmentJSON;
                    
                  }
                
                $query .= "segments:{".rtrim($segmentArr,",").PHP_EOL."},".PHP_EOL; 
              
                foreach($preAgg as $k4=>$v4){
                    $name = $v4['name'];
                    $type = "`".$v4['type']."`";
                    $measureRef = $v4['measureReferences'];
                    $dimRef = $v4['dimensionReferences'];
                    $timeDimRef = $v4['timeDimensionReference'];
                    $gran = "`".$v4['granularity']."`";
                    $pargran = "`".$v4['partitionGranularity']."`";
                    if($v4['scheduledRefresh'] == '1'){
                        $schedRef = "true";
                    }else{
                        $schedRef = "false";
                    }
                    
                    $schRef = "`".$schedRef."`";
                    $index = $v4['indexes'];
                    
                    $preAggJson = PHP_EOL."$name: {".PHP_EOL
                                    ."type: ".$type.",".PHP_EOL
                                    ."measureReferences: ".$measureRef.",".PHP_EOL
                                    ."dimensionReferences: ".$dimRef.",".PHP_EOL
                                    ."timeDimensionReference: ".$timeDimRef.",".PHP_EOL
                                    ."granularity: ".$gran.",".PHP_EOL
                                    ."partitionGranularity: ".$pargran.",".PHP_EOL
                                    ."scheduledRefresh: ".$schRef.",".PHP_EOL
                                    ."indexes: {".PHP_EOL
                                      ."main: {".PHP_EOL
                                        ."columns: ".$index.",".PHP_EOL
                                      ."}".PHP_EOL
                                    ."}".PHP_EOL
                                  ."},";
                    $preAggArr .= $preAggJson;
                }
                $query .= "preAggregations:{".rtrim($preAggArr,",").PHP_EOL."}".PHP_EOL;
                
                $query .= "});";
                
                $file = $cubename.".js";
                $txt = fopen($file, "w") or die("Unable to open file!");
                fwrite($txt, $query);
                fclose($txt);
                array_push($files,$file);
            }
   
            $zipname = 'logs.zip';
            $zip = new ZipArchive;
            $zip->open($zipname, ZipArchive::CREATE);
            foreach ($files as $file) {
              $zip->addFile($file);
            }
            $zip->close();
            
            header('Content-Type: application/zip');
            header('Content-disposition: attachment; filename='.$zipname);
            header('Content-Length: ' . filesize($zipname));
            readfile($zipname);
        }
    }
    }
    
?>