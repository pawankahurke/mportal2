<?php

/* Revision History

When       Who   What
----       ---   ----
31-Aug-05  BJS   Created. Share table creation code between scripts.
 6-Sep-05  BJS   Removed deleted field.
01-Jun-06  BTE   Bug 3445: Optimize Events table.
11-Jun-06  BTE   Bug 3445: Optimize Events table (better indices).
06-Jul-06  BTE   Bug 3515: Fix ad-hoc queries to no longer use *.

*/


    /*
    |  $tbl = table name in the form, 'CREATE TABLE tbl_name'
    |         or 'CREATE TEMPORARY TABLE tbl_name'
    |  $db  = database handle
    */ 
    function build_events_table($create_table, $collate, $db)
    {
        $def = 'default';
        $sql = "  $create_table (\n"
             . "  idx int(11) not null auto_increment,\n"
             . "  scrip int(11),\n"
             . "  entered int(11) default 0 not null,\n"
             . "  customer varchar(50) $collate $def '',\n"
             . "  machine varchar(64) $collate $def '',\n"
             . "  username varchar(50) $collate,\n"
             . "  clientversion varchar(20) $collate,\n"
             . "  clientsize int(11),\n"
             . "  priority int(11),\n"
             . "  description varchar(80) $collate,\n"
             . "  type varchar(20) $collate $def '',\n"
             . "  path varchar(255) $collate,\n"
             . "  executable varchar(20) $collate,\n"
             . "  version varchar(20) $collate,\n"
             . "  size int(11),\n"
             . "  id int(11),\n"
             . "  windowtitle varchar(255) $collate,\n"
             . "  string1 varchar(255) $collate,\n"
             . "  string2 varchar(255) $collate,\n"
             . "  text1 text $collate,\n"
             . "  text2 text $collate,\n"
             . "  text3 text $collate,\n"
             . "  text4 text $collate,\n"
             . "  servertime int(11) $def 0,\n"
             . "  uuid varchar(50) $collate $def '',\n"
             . "  PRIMARY KEY (idx),\n"
             . "  KEY sservertime (servertime, customer, machine, scrip),\n"
             . "  KEY custmach (customer, machine, servertime),\n"
             . "  KEY customer (customer, scrip, servertime),\n"
             . "  KEY machine (customer, machine, scrip, servertime)\n"
             . ")";
        redcommand($sql,$db);
    }


?>