<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-dashboard.php';

function SCORE_GetGridData($db,$key) {
    $key = DASH_ValidateKey($key);    
    if ($key) {    
        
    $sql = "select * from ".$GLOBALS['PREFIX']."report.Scorecard";
    $sqlres = find_many($sql,$db);
    
    } else {
        $msg = 'Your key has been expired';
    }
    
    return $sqlres;
}

function SCORE_getFilterList($db,$key) {
    $key = DASH_ValidateKey($key);    
    if ($key) {
        
        $sql = "select id,name,username from ".$GLOBALS['PREFIX']."event.SavedSearches";
        $sqlres = find_many($sql,$db);
        
    } else {
        $msg = 'Your key has been expired';
    }
    
    return $sqlres;
}

function SCORE_GetAddScoreSubmit($db,$key,$scorename,$filterid,$status,$date,$dart) {
    $key = DASH_ValidateKey($key);    
    if ($key) { 
        
        $sql = "insert into ".$GLOBALS['PREFIX']."report.Scorecard (scorename,filterid,status,date,scrips) values ('$scorename',$filterid,'$status',$date,'$dart')";
        $sqlres = redcommand($sql,$db);                
        
    } else {
        $msg = 'Your key has been expired';
    }
    
    return $sqlres;
}

function SCORE_GetEditScore($db,$key,$id) {
    $key = DASH_ValidateKey($key);    
    if ($key) { 
        
        $sql = "select * from ".$GLOBALS['PREFIX']."report.Scorecard where id = $id limit 1";
        $sqlres = find_one($sql,$db);
        
    } else {
        $msg = 'Your key has been expired';                
    }
    
    return $sqlres;
}

function SCORE_GetDataUpdate($db,$key,$scorename,$filterid,$status,$date,$dart,$id){
    $key = DASH_ValidateKey($key);    
    if ($key) { 
        
        $sql= "update ".$GLOBALS['PREFIX']."report.Scorecard set scorename = '$scorename', filterid = $filterid, status = '$status', date = $date, scrips = $dart where id = $id";
        $sqlres = redcommand($sql,$db);
        
    } else {
        $msg = 'Your key has been expired';                
    }
    
    return $sqlres;
}

function SCORE_GetDeleteScore($db,$key,$id) {
    $key = DASH_ValidateKey($key);    
    if ($key) {
        
        $sql = "delete from ".$GLOBALS['PREFIX']."report.Scorecard where id = $id";
        $sqlres = redcommand($sql,$db);
        
    } else {
        $msg = 'Your key has been expired';      
    }
    return $sqlres;
}

function SCORE_GetNameCheck($db,$key,$scorename){
    $key = DASH_ValidateKey($key);    
    if ($key) { 
        
        $sql = "select id,scorename from ".$GLOBALS['PREFIX']."report.Scorecard where scorename = '$scorename' limit 1";
        $sqlres = find_one($sql,$db);        
        
    } else {
        $msg = 'Your key has been expired';
    }
    
    return $sqlres;
}


function SCORE_GetAgentGridData($db,$key) {
    $key = DASH_ValidateKey($key);    
    if ($key) {
        
        $sql = "select * from ".$GLOBALS['PREFIX']."report.scorecardDetails";
        $sqlres = find_many($sql,$db);
        
    } else {
        $msg = 'Your key has been expired';
    }
    return $sqlres;
}

function SCORE_GetAddAgentList($db,$key) {
    $key = DASH_ValidateKey($key);    
    if ($key) {
        
        $result = SCORE_GetGridData($db,$key);             
        
    } else {
        $msg = 'Your key has been expired';
    }
    
    return $result;
}

function SCORE_GetAgentValueSubmit($db,$key,$scoreid,$scoretext,$percent,$eid) {
    $key = DASH_ValidateKey($key);    
    if ($key) { 
        $sqldelete = "delete from ".$GLOBALS['PREFIX']."report.scorecardDetails where custid = $eid";
        redcommand($sqldelete,$db);
        
        foreach ($scoreid as $key => $value) {
            $return .= '(';
            $return .= '"' . $value . '", ';
            $return .= '"' . $eid. '", ';
            $return .= '"' . $scoretext[$key]. '", ';
            $return .= '"' . $percent[$key]. '", ';
            $return .= '"' . time(). '"';
            $return .= "),";
        }
        $return = rtrim($return,","); 
        
        $sql = "insert into ".$GLOBALS['PREFIX']."report.scorecardDetails (scoreid,custid,scorevalue,weightage,date) values".$return;
        $result = redcommand($sql,$db);
        
    } else {
        $msg = 'Your key has been expired';
    }
    
    return $result;
}

function SCORE_GetDeleteAgent($db,$key,$id){
    $key = DASH_ValidateKey($key);    
    if ($key) { 
        
        $sql = "delete from ".$GLOBALS['PREFIX']."report.scorecardDetails where id = $id";
        $sqlres = redcommand($sql,$db); 
        
    } else {
        $msg = 'Your key has been expired';
    }
    
    return $sqlres;
}

function SCORE_GetEditValue($db,$key,$eid) {
    $key = DASH_ValidateKey($key);    
    if ($key) { 
        
        $sql = "select id,scoreid,custid,scorevalue,weightage from ".$GLOBALS['PREFIX']."report.scorecardDetails where custid = $eid";
        $sqlres = find_many($sql,$db);
        
    } else {
       $msg = 'Your key has been expired'; 
    }
    return $sqlres;
}

function SCORE_GetTrendData($db,$key,$searchValue,$searchType,$eid,$rparentname) {
    $key = DASH_ValidateKey($key);    
    if ($key) {  
        
        if ($searchType == 'Sites') {
            $sql = "select * from ".$GLOBALS['PREFIX']."report.scorecardSummary where siteName = '$searchValue';";
        } else if ($searchType == 'ServiceTag') {
            $sql = "select * from ".$GLOBALS['PREFIX']."report.scorecardSummary where siteName = '$rparentname' and machine = '$searchValue';"; 
        }
        $sqlres = find_many($sql,$db);

        foreach ($sqlres as $key => $value) {
            $scoreid .= '"'.$value['scid'].'",';
        }
        $scoreid = rtrim($scoreid, ',');

        $sqlscore = "select scorename,scoreid,custid,scorevalue,weightage,sd.date,filterid,scrips from ".$GLOBALS['PREFIX']."report.scorecardDetails as sd join ".$GLOBALS['PREFIX']."report.Scorecard "
                . "as sc on sd.scoreid = sc.id where scoreid in ($scoreid);";
        
        $sqlscoreres = find_many($sqlscore,$db);
        $totalcount = safe_count($sqlscoreres);
        
        foreach ($sqlscoreres as $value) {
            $add += $value['weightage'];
        }
        
        if ($totalcount > 0) {
        $total = 100 - $add;
        } else {
            $total = '';
        }
        
        
    } else {
        $msg = 'Your key has been expired'; 
    }
    return $result = array('table' => $sqlscoreres, 'total' => $total);
}

function SCORE_GetTrendGraphData($db,$key,$searchType,$searchValue,$rparentname,$passLevel,$eid) {
    $key = DASH_ValidateKey($key);    
    if ($key) {  
        
        if ($searchType == 'Sites') {
            $sql = "select * from ".$GLOBALS['PREFIX']."report.scorecardSummary where siteName = '$searchValue';";
        } else if ($searchType == 'ServiceTag') {
            $sql = "select * from ".$GLOBALS['PREFIX']."report.scorecardSummary where siteName = '$rparentname' and machine = '$searchValue';"; 
        }
        $sqlres = find_many($sql,$db);
        
        foreach ($sqlres as $key => $value) {
            $scoreid .= '"'.$value['scid'].'",';
        }
        $scoreid = rtrim($scoreid, ',');

        $sqlscore = "select scorename,scoreid,custid,scorevalue,weightage,sd.date,filterid,scrips,scs.serverdate,scs.serverTime as serverTime"
                . " from ".$GLOBALS['PREFIX']."report.scorecardDetails as sd join ".$GLOBALS['PREFIX']."report.Scorecard "
                . "as sc on sd.scoreid = sc.id join ".$GLOBALS['PREFIX']."report.scorecardSummary as scs on sc.id = scs.scid where scoreid in ($scoreid);";
        
        $sqlscoreres = find_many($sqlscore,$db);
        
        
        return $sqlscoreres;
        
    } else {
        $msg = 'Your key has been expired'; 
    }
}



?>