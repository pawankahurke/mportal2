<?php

/*
Revision history:

Date        Who     What
----        ---     ----
10-Dec-02   EWB     Created
 7-Feb-03   EWB     3.1 database scheme
 3-Mar-03   EWB     Added visible attribute to Machines table.
 2-May-03   EWB     Added asset group leader.
20-Jun-03   EWB     Addded 'provisional' to Machine table.
 6-Oct-03   EWB     Addded 'include' to DataName table.
30-May-05   BJS     Added 'clientname' to DataName and unique
                    index on clientname.
30-Aug-05   BJS     build_assetdata takes the table name as an arg.
16-Dec-05   BJS     build_assetdata creates temp or static tables.
01-Sep-07   BTE     Added index to speed up queries.

*/

   /*
    |  needs:
    |    l-sql.php   -- command
    |    l-rcmd.php  -- redcommand
    */

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
  //    $sql .= "  earliest int(11) NOT NULL default '0',\n";
  //    $sql .= "  latest int(11) NOT NULL default '0',\n";
        $sql .= "  cearliest int(11) NOT NULL default '0',\n";   // 8/16/2002
        $sql .= "  cobserved int(11) NOT NULL default '0',\n";   // 9/20/2002
        $sql .= "  clatest int(11) NOT NULL default '0',\n";     // 8/16/2002
        $sql .= "  searliest int(11) NOT NULL default '0',\n";   // 8/16/2002
        $sql .= "  sobserved int(11) NOT NULL default '0',\n";   // 9/20/2002
        $sql .= "  slatest int(11) NOT NULL default '0',\n";     // 8/16/2002
        $sql .= "  uuid varchar(50) NOT NULL default '',\n";     // 2/7/2003
        $sql .= "  PRIMARY KEY  (id),\n";
        $sql .= "  KEY machineid (machineid),\n";    // 9/20/2002
        $sql .= "  KEY dataid (dataid),\n";          // 9/20/2002
        $sql .= "  KEY ordinal (ordinal),\n";        // 9/20/2002
        $sql .= "  KEY slatest (slatest),\n";        // 9/20/2002
        $sql .= "  KEY clatest (clatest),\n";        // 10/7/2002
        $sql .= "  KEY test (machineid,dataid,clatest)\n";      // 8/17/2007
        $sql .= ")";
        redcommand($sql,$db);
    }



    function build_dataname($db)
    {
        $sql  = "";
        $sql .= "CREATE TABLE DataName (\n";
        $sql .= "  dataid int(11) NOT NULL auto_increment,\n";
        $sql .= "  setbyclient tinyint(1) NOT NULL default '0',\n";   // 8/16/2002
        $sql .= "  name varchar(50) NOT NULL default '',\n";
        $sql .= "  parent int(11) NOT NULL default '0',\n";
        $sql .= "  ordinal int(11) NOT NULL default '0',\n";
        $sql .= "  groups int(11) NOT NULL default '0',\n";      // 8/16/2002
        $sql .= "  created int(11) NOT NULL default '0',\n";     // 7/17/2003
        $sql .= "  leader tinyint(1) not null default '0',\n";   // 5/2/2003
        $sql .= "  include tinyint(1) not null default '0',\n";  // 10/6/2003
        $sql .= "  clientname varchar(50) not null default '',\n"; // 5/30/05
        $sql .= "  PRIMARY KEY  (dataid),\n";
        $sql .= "  unique index uniq (name),\n";     // 2/7/2003
        $sql .= "  unique index uniq2 (clientname)\n"; // 5/30/05
        $sql .= ")";
        redcommand($sql,$db);
    }


    function build_machine($db)
    {
        $sql  = "CREATE TABLE Machine (\n";
        $sql .= "  machineid int(11) NOT NULL auto_increment,\n";
        $sql .= "  host varchar(64) NOT NULL default '',\n";
        $sql .= "  cust varchar(50) NOT NULL default '',\n";
        $sql .= "  uuid varchar(50) NOT NULL default '',\n";
        $sql .= "  visible int(11) NOT NULL default '1',\n";    // 3/03/2003
        $sql .= "  cearliest int(11) NOT NULL default '0',\n";  // 8/16/2002
        $sql .= "  clatest int(11) NOT NULL default '0',\n";    // 8/16/2002
        $sql .= "  searliest int(11) NOT NULL default '0',\n";  // 8/16/2002
        $sql .= "  slatest int(11) NOT NULL default '0',\n";    // 8/16/2002
        $sql .= "  provisional int(11) not null default '0',\n";  // 6/20/2003
        $sql .= "  PRIMARY KEY  (machineid),\n";
        $sql .= "  unique index uniq (cust,host)\n";   // 2/7/2003
        $sql .= ")";
        redcommand($sql,$db);
    }


    function build_asset_tables($tbl_type, $tbl_name, $db)
    {
        build_machine($db);
        build_dataname($db);
        build_assetdata($tbl_type, $tbl_name, $db);
    }


?>
