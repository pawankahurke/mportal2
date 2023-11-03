<?php



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
        $res = mysqli_list_tables($name,$db);
        if ($res)
        {
            $n = mysqli_num_rows($res);
            for ($i = 0; $i < $n; $i++)
            {
                $name = mysqli_tablename($res,$i);
                $set[$name] = true;
            }
            mysqli_free_result($res);
        }
        return $set;
    }


    function CBLD_BuildGConfig($name,$db)
    {
        
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
