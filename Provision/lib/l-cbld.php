<?php

/*
Revision history:

Date        Who     What
----        ---     ----
10-Dec-02   EWB     Created
20-Dec-02   EWB     Index Globals, Locals
20-Dec-02   EWB     Unique index for Scrips, Variables
20-Jan-03   AAM     Removed extraneous $revl.  Updated databases:
                    Globals: removed type; added itype; added index uniq
                    Locals: removed type; added srev, itype; added index uniq
                    Revisions: removed crevl, srevl
23-Jan-03   EWB     Propogation to 3.1
 7-Feb-03   EWB     3.1 database scheme
05-Sep-03   MMK     Added password security and dangerous attribute fields to
                    Variables table in siteman.
10-Dec-03   AAM     Added "provisional" column to "Revisions" table.
21-Apr-04   EWB     Create GlobalCache / LocalCache tables.
12-May-05   EWB     Builds new gconfig version.
31-May-05   EWB     Legacy Checksum Cache
 8-Jul-05   EWB     gconfig.VarValues.valu becomes "mediumtext"
14-Jul-05   EWB     no more cksums ...
22-Jul-05   EWB     deleted "deleted".
12-Oct-05   BTE     Removed build_hmap, build_sema, build_gset, build_valu,
                    build_vars, build_vers, build_scrp, build_desc, and
                    build_revs.  Added call to csrv.so to build these tables.
13-Oct-05   BTE     Build core.LegacyCache as part of building gconfig.
24-Oct-05   BTE     csrv.so now creates databases/tables upon startup, so we
                    no longer request csrv to make the tables here.
06-May-06   BTE     Bug 3373: Fix InvalidVars.valu to use mediumtext.

*/

    function vc($size)
    {
        return "varchar($size) not null default ''";
    }


    function build_invd($db)
    {
        $v50 = vc(50);
        $v64 = vc(64);
        $def = 'not null default';
        $i11 = "int(11) $def 0";
        $byt = "tinyint(1) $def 0";
        $txt = "mediumtext $def ''";
        $sql = "create table InvalidVars (\n"
             . " invid    int(11) not null auto_increment,\n"
             . " isglobal $byt,\n"
             . " censusid $i11,\n"
             . " siteid   $i11,\n"
             . " name     $v50,\n"
             . " valu     $txt,\n"
             . " stat     $i11,\n"
             . " revl     $i11,\n"
             . " scop     $i11,\n"
             . " last     $i11,\n"
             . " srev     $i11,\n"
             . " itype    $i11,\n"
             . " host     $v64,\n"
             . " primary key (invid),\n"
             . " unique index uniq (isglobal,censusid,siteid,scop,name)\n"
             . ")";
        redcommand($sql,$db);
    }


   /*
    |  This will store either site or host
    |  information, but not both at the
    |  same time.
    |
    |  The site values will have censusid of zero.
    */

    function build_csum($db)
    {
        $v32 = vc(32);
        $def = 'not null default';
        $i11 = "int(11) $def 0";
        $byt = "tinyint(1) $def 0";
        $sql = "create table LegacyCache (\n"
             . " csumid    int(11) not null auto_increment,\n"
             . " censusid $i11,\n"
             . " siteid   $i11,\n"
             . " last     $i11,\n"
             . " gsum     $v32,\n"
             . " lsum     $v32,\n"
             . " ssum     $v32,\n"
             . " drty     $byt,\n"
             . " primary key (csumid),\n"
             . " unique index uniq (siteid,censusid)\n"
             . ")";
        redcommand($sql,$db);
    }


    function gbld($db,$tab,$proc,$key)
    {
        if (!isset($tab[$key]))
        {
            $proc($db);
        }
    }


    function find_table_names($name,$db)
    {
        $set = array( );
        $res = mysqli_query($db, "SHOW TABLES FROM `$name`");
        if ($res)
        {
            $n = mysqli_num_rows($res);
            for ($i = 0; $i < $n; $i++)
            {
                $name = ((mysqli_data_seek($res, $i) && (($___mysqli_tmp = mysqli_fetch_row($res)) !== NULL)) ? array_shift($___mysqli_tmp) : false);
                $set[$name] = true;
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
        return $set;
    }


    function CBLD_BuildGConfig($name,$db)
    {
        /* Build only the tables that the Client is not aware of */
        $tab = find_table_names($name,$db);
        gbld($db,$tab,'build_invd','InvalidVars');
        gbld($db,$tab,'build_csum','LegacyCache');
    }

    function build_temp($db)
    {
        $v50 = vc(50);
        $def = 'not null default';
        $i11 = "int(11) $def 0";
        $sql = "create table InvalidTemp (\n"
             . " id   int(11) not null auto_increment,\n"
             . " name $v50,\n"
             . " scop $i11,\n"
             . " type $i11,\n"
             . " primary key (id),\n"
             . " unique index uniq (scop,name)\n"
             . ")";
        redcommand($sql,$db);
    }

?>
