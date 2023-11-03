<?php



   

    function build_assetdata($tbl_type, $tbl_name ,$db)
    {
        $tbl_type = ($tbl_type)? '' : 'TEMPORARY';
        $tbl_name = " CREATE $tbl_type TABLE $tbl_name "; 

        $sql  = "$tbl_name (\n";
        $sql .= "  id int(11) NOT NULL auto_increment,\n";
        $sql .= "  machineid int(11) NOT NULL default '0',\n";
        $sql .= "  dataid int(11) NOT NULL default '0',\n";
        $sql .= "  value varchar(255) NOT NULL,\n";
        $sql .= "  ordinal int(11) NOT NULL default '0',\n";
            $sql .= "  cearliest int(11) NOT NULL default '0',\n";           $sql .= "  cobserved int(11) NOT NULL default '0',\n";           $sql .= "  clatest int(11) NOT NULL default '0',\n";             $sql .= "  searliest int(11) NOT NULL default '0',\n";           $sql .= "  sobserved int(11) NOT NULL default '0',\n";           $sql .= "  slatest int(11) NOT NULL default '0',\n";             $sql .= "  uuid varchar(50) NOT NULL default '',\n";             $sql .= "  PRIMARY KEY  (id),\n";
        $sql .= "  KEY machineid (machineid),\n";            $sql .= "  KEY dataid (dataid),\n";                  $sql .= "  KEY ordinal (ordinal),\n";                $sql .= "  KEY slatest (slatest),\n";                $sql .= "  KEY clatest (clatest),\n";                $sql .= "  KEY test (machineid,dataid,clatest)\n";              $sql .= ")";
        redcommand($sql,$db);
    }



    function build_dataname($db)
    {
        $sql  = "";
        $sql .= "CREATE TABLE DataName (\n";
        $sql .= "  dataid int(11) NOT NULL auto_increment,\n";
        $sql .= "  setbyclient tinyint(1) NOT NULL default '0',\n";           $sql .= "  name varchar(50) NOT NULL default '',\n";
        $sql .= "  parent int(11) NOT NULL default '0',\n";
        $sql .= "  ordinal int(11) NOT NULL default '0',\n";
        $sql .= "  groups int(11) NOT NULL default '0',\n";              $sql .= "  created int(11) NOT NULL default '0',\n";             $sql .= "  leader tinyint(1) not null default '0',\n";           $sql .= "  include tinyint(1) not null default '0',\n";          $sql .= "  clientname varchar(50) not null default '',\n";         $sql .= "  PRIMARY KEY  (dataid),\n";
        $sql .= "  unique index uniq (name),\n";             $sql .= "  unique index uniq2 (clientname)\n";         $sql .= ")";
        redcommand($sql,$db);
    }


    function build_machine($db)
    {
        $sql  = "CREATE TABLE Machine (\n";
        $sql .= "  machineid int(11) NOT NULL auto_increment,\n";
        $sql .= "  host varchar(64) NOT NULL default '',\n";
        $sql .= "  cust varchar(50) NOT NULL default '',\n";
        $sql .= "  uuid varchar(50) NOT NULL default '',\n";
        $sql .= "  visible int(11) NOT NULL default '1',\n";            $sql .= "  cearliest int(11) NOT NULL default '0',\n";          $sql .= "  clatest int(11) NOT NULL default '0',\n";            $sql .= "  searliest int(11) NOT NULL default '0',\n";          $sql .= "  slatest int(11) NOT NULL default '0',\n";            $sql .= "  provisional int(11) not null default '0',\n";          $sql .= "  PRIMARY KEY  (machineid),\n";
        $sql .= "  unique index uniq (cust,host)\n";           $sql .= ")";
        redcommand($sql,$db);
    }


    function build_asset_tables($tbl_type, $tbl_name, $db)
    {
        build_machine($db);
        build_dataname($db);
        build_assetdata($tbl_type, $tbl_name, $db);
    }


?>
