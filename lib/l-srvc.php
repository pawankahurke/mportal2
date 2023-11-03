<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-dashboard.php';


function SRVC_GetMachineData($key,$db,$censusId) {
    $key = DASH_ValidateKey($key);
    if ($key) {
            $sqlcensus  = SRVC_GetCensusuniq($db,$key,$censusId);
            $cid        = $sqlcensus['censusuniq'];
        
        $sql = "select sp.censusuniq, sp.status, sp.name, sp.serviceuniq,  replace(replace( replace(sp.servicedesc, '\\n', ' '), '\\r', ' '), '\'', ' ') as servicedesc, if(sp.status=1,'Started','') as status, 
		if(sp.startuptype=0,'Boot',if(sp.startuptype=1,'System', if(sp.startuptype=2,'Automatic',
		if(sp.startuptype=3,'Manual','Disabled')))) as startuptype, sp.logonas, sp.lastupdate,
		if (max(coalesce(sact.createtime,0)) = 0, '',
		case substr(max(concat(coalesce(sact.createtime,'0000000000'),',',
		coalesce(ss.status,'4'))), 12, 12) 
		when 0 then 'Undefined' 
		when 1 then 'Done' 
		when 2 then 'n/a' 
		when 3 then 'Error' 
		when 4 then 'Pending' 
		else 'Unknown' 
		end) as ActionStatus,                
		case substr(max(concat(coalesce(sact.createtime,'0000000000'),',',
		coalesce(sact.actiontype,'6'))), 12, 12)
		when 0 then 'Undefined' 
		when 1 then 'Start' 
		when 2 then 'Stop' 
		when 3 then 'Restart' 
		when 4 then 'Set start type' 
		when 5 then 'Delete' 
		when 6 then '' 
		else 'Unknown' 
		end as lastaction,
		CASE WHEN max(coalesce(sact.createtime,0))=0 THEN 'Never' ELSE FROM_UNIXTIME(max(coalesce(sact.createtime,0)),'%W %M %d, %Y %h:%i:%s') END as last_action_status
		from ".$GLOBALS['PREFIX']."srvc.SrvcProfile AS sp
		left join  ".$GLOBALS['PREFIX']."core.MachineGroupMap AS mgm ON 
		mgm.censusuniq = sp.censusuniq
		left join  ".$GLOBALS['PREFIX']."srvc.SrvcAction AS sact ON 
		((sp.serviceuniq = sact.serviceuniq) AND 
		(mgm.mgroupuniq = sact.mgroupuniq)) 
		left join  ".$GLOBALS['PREFIX']."srvc.SrvcStatus  AS ss ON 
		sact.actionuniq = ss.actionuniq 		
		where  (sp.logonas != '') AND sp.censusuniq = '" . $cid . "' group by sp.name";
        
        $sqlres = find_many($sql,$db);
        
    } else {
        echo "Your key has been expired";
    }
    
    return $sqlres;    
}

function SRVC_GetCensusuniq($db,$key,$censusId){
    $key = DASH_ValidateKey($key);
    if ($key) {
        
            $sql   = "SELECT censusuniq from ".$GLOBALS['PREFIX']."core.Census where id = '$censusId'";
            $sqlres = find_one($sql,$db);
        
    } else {
        echo "Your key has been expired";
    }
    
    return $sqlres;
}

function SRVC_GetMachineGraph($key,$db,$cid,$now,$prev){
    $key = DASH_ValidateKey($key);
    if ($key) {
        
        $sql = "select DATE_FORMAT(FROM_UNIXTIME(sact.createtime),'%H') as createtime ,   
		case substr(max(concat(coalesce(sact.createtime,'0000000000'),',',
		coalesce(sact.actiontype,'6'))), 12, 12)
		when 0 then 'Undefined' 
		when 1 then 'Start' 
		when 2 then 'Stop' 
		when 3 then 'Restart' 
		when 4 then 'Set start type' 
		when 5 then 'Delete' 
		when 6 then '' 
		else 'Unknown' 
		end as lastaction,count(sact.actiontype) as cnt,
                if (max(coalesce(sact.createtime,0)) = 0, '',
		case substr(max(concat(coalesce(sact.createtime,'0000000000'),',',
		coalesce(ss.status,'4'))), 12, 12) 
		when 0 then 'Undefined' 
		when 1 then 'Done' 
		when 2 then 'n/a' 
		when 3 then 'Error' 
		when 4 then 'Pending' 
		else 'Unknown' 
		end) as ActionStatus
                from
		".$GLOBALS['PREFIX']."srvc.SrvcProfile AS sp
		left join  ".$GLOBALS['PREFIX']."core.MachineGroupMap AS mgm ON 
		mgm.censusuniq = sp.censusuniq
		left join  ".$GLOBALS['PREFIX']."srvc.SrvcAction AS sact ON 
		((sp.serviceuniq = sact.serviceuniq) AND 
		(mgm.mgroupuniq = sact.mgroupuniq)) 
		left join  ".$GLOBALS['PREFIX']."srvc.SrvcStatus  AS ss ON 
		sact.actionuniq = ss.actionuniq 	
		where  
		(sp.logonas != '') AND sp.censusuniq = '".$cid."' and 
		sact.createtime  between '".$prev."' AND '".$now."'
		and sact.createtime is not null and sact.actiontype is not null
		group by createtime, sact.actiontype";
        
        $sqlres = find_many($sql,$db);
        
    } else {
        echo "Your key has been expired";
    }
    
    return $sqlres;
}



?>