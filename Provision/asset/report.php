<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 2-Aug-02   EWB     Always set enabled to 1 when creating new report.
11-Sep-02   EWB     Merge with new asset code.
19-Sep-02   EWB     Giant refactoring.
20-Sep-02   EWB     8.3 libary names.
27-Sep-02   EWB     No more get_dataset_passer()
 8-Oct-02   EWB     Back to asset world.
11-Oct-02   NL      Remove all references to dataset and events
23-Oct-02   NL      enable report format options
24-Oct-02   NL      move write_JS_order_options to lib-arpt.php3
30-Oct-02   NL      add "change report" functionality
04-Nov-02   NL      global_auth checks for "priv_areport" instead of "priv_report"
04-Nov-02   NL      change $last --> $last_run; $run -->  $last_run_str
04-Nov-02   NL      add "run" functionality (makes a dup w/ cycle=immediate)
12-Nov-02   NL      comment out "change report" functionality
 4-Dec-02   EWB     Reorginization Day
 5-Dec-02   EWB     Do not require php short_open_tag
 6-Dec-02   EWB     Local Navigation
24-Jan-03   EWB     Reworked argument passing.
10-Feb-03   EWB     Uses asset database.
10-Feb-03   EWB     No more event cache.
10-Feb-03   EWB     Uses sandbox libraries.
11-Feb-03   EWB     db_change()
13-Feb-03   EWB     Precache report mail for this user.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
19-Mar-03   NL      Move debug_note line below $debug.
15-Apr-03   EWB     Factored out the jumptable.
24-Apr-03   EWB     Echo Jumptable
 5-May-03   NL      Include l-js.php cuz outputJavascriptShowElement() moved there.
14-May-03   EWB     Change reports.
16-May-03   EWB     Made start-end difference the default.
20-May-03   EWB     Refactoring.
18-Jun-03   EWB     Slave Database.
20-Jun-03   EWB     No Slave Database.
17-Jul-03   EWB     File Output
23-Jul-03   EWB     Implement Links.
13-Aug-03   EWB     Wording change for output selection.
10-Sep-03   EWB     Allow the creation of a report with a query
                    even if the the user owns no sites.
 3-Oct-03   EWB     Some small formatting changes.
 1-Nov-04   BJS     included l-form, added email options
16-Nov-04   EWB     Optional Report Content.
22-Nov-04   EWB     Content option moved below links selection.
 9-Dec-04   EWB     New User Interface.
10-Dec-04   EWB     Gang Delete.
13-Dec-04   EWB     Display, Select and Sort by Query
15-Dec-04   EWB     Name Contains
 3-Jan-05   EWB     Enable/Disable Multiple Reports
 5-Jan-05   EWB     "Change" -> "Report Type"
 6-Jan-05   EWB     Select by e-mail address substring
 6-Jan-05   EWB     gang update subject_text
 7-Jan-05   EWB     new columns: format / defmail / links / output
18-Jan-05   AAM     Wording changes as per Alex.
25-Jan-05   BJS     added: tabular output option.
27-Jan-05   BJS     Cosmetic change for Alex.
27-Jan-05   EWB     select by owner, tabular
 4-Feb-05   EWB     Select by owner still displays column
 7-Feb-05   EWB     New help pages.
 1-Mar-05   EWB     Run immediate copies report, not user, filtersites.
21-Mar-05   EWB     Changed default AssetReports.tabular from 0 to 1.
16-Aug-05   BJS     Added XML option.
17-Aug-05   BJS     Added passbox().
18-Aug-05   BJS     Improved XML report creation. Updated search options
                    for format and output.
26-Aug-05   BJS     UI improvements for Alex.
04-Nov-05   BJS     saved_search -> asset_saved_search.
29-Nov-05   BJS     Added l-grps.php
30-Nov-05   BJS     Include/Exclude groups for Reports.
05-Dec-05   BJS     Removed sitefilter references, delete_reports(),
                    site_filters(), sits_options(), kill_associates().
07-Dec-05   BJS     group_include was not getting set after 'updating' report.
11-Aug-06   BTE     Bug 3590: Undefined index in asset/report.php.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.

*/

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-rprt.php'  );
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-slct.php'  );
include_once ( '../lib/l-jump.php'  );
include_once ( '../lib/l-msql.php'  );
include_once ( '../lib/l-cmth.php'  );
include_once ( '../lib/l-date.php'  );
include_once ( '../lib/l-gsql.php'  );
include_once ( '../lib/l-upar.php'  );
include_once ( '../lib/l-alib.php'  );
include_once ( '../lib/l-dids.php'  );
include_once ( '../lib/l-arpt.php'  );
//  include ( '../lib/l-slav.php'  );
include_once ( 'local.php'   );
include_once ( '../lib/l-sitflt.php');
include_once ( '../lib/l-js.php'    );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-form.php'  );
include_once ( '../lib/l-tabs.php'  );
include_once ( '../lib/l-tiny.php'  );
include_once ( '../lib/l-grps.php'  );
include_once ( '../lib/l-rlib.php'  );

    define('constButtonYes',  'Yes');
    define('constButtonNo',   'No');
    define('constButtonOk',   'OK');
    define('constButtonHlp',  'Help');
    define('constButtonRst',  'Reset');
    define('constButtonSub',  'Search');
    define('constButtonCan',  'Cancel');
    define('constButtonAll',  'Check all');
    define('constButtonNone', 'Uncheck all');
    define('constButtonLess', '<< Less');
    define('constButtonMore', 'More >>');
    define('constFormXML',    'XML');

    define('constReportXML',2);
    define('constReportASI',1);
    define('constReportMAIL',0);

    function title($act)
    {
        $a = 'Asset';
        $r = 'Report';
        $q = 'Queue';
        $m = 'Multiple';
        switch ($act)
        {
            case 'copy': return "Copy an $a $r";
            case 'edit': return "Edit an $a $r";
            case 'over': return "Create a Local $r";
            case 'addn': return "Add an $a $r";
            case 'insn': return "$a $r Added";
            case 'stat': return "$a $r Statistics";
            case 'enab': return "Enable $a $r";
            case 'disb': return "Disable $a $r";
            case 'menu': return "$a $r Debug Menu";
            case 'mnge': return "Manage $a ${r}s";
            case 'dovr': return "Disable Local $r";
            case 'eovr': return "Enable Local $r";
            case 'gang': return "Edit $m $a ${r}s";
            case 'gdel': ;
            case 'gexp': return "Delete $m $a ${r}s";
            case 'sane': return 'Database Consistancy Check';
            case 'genb': return "Enable/Disable $m $a ${r}s";
            case 'cdel': return "Confirm Delete";
            case 'view': return "$a $r Details";
            case 'lock': return "Lock $a $r $q";
            case 'pick': return "Unlock $a $r $q";
            case 'exec': return "Run An $a $r";
            case 'now' : ;
            case 'frst': ;
            case 'post': ;
            case 'queu': return "$a $r $q";
            default    : return "$a ${r}s";
        }
    }


    function again(&$env)
    {
        $self = $env['self'];
        $dbg = $env['priv'];
        $act = $env['act'];
        $cmd = "$self?act";
        $a   = array( );
        $a[] = html_link('#top','top');
        $a[] = html_link('#bottom','bottom');
        if ($act == 'list')
        {
            $a[] = html_link('#control','control');
            $a[] = html_link('#table','table');
            $a[] = gang_link($env,'mnge','manage');
        }
        else
        {
            $a[] = html_link($self,'reports');
        }
        if (matchOld($act,'|||post|frst|last|'))
        {
            $a[] = html_link('#table','table');
        }
        $a[] = html_link("$cmd=addn",'add');
        if ($dbg)
        {
            $args = $env['args'];
            $jump = $env['jump'];
            $time = 14 * 86400;
            $comp = "$cmd=list&dsp=1&mal=-1&gbl=3&adv=0";
            $queu = "$cmd=queu$jump";
            $next = "$comp&nxt=$time&o=8$jump";
            $last = "$comp&o=2$jump";
            $redo = ($args)? "$self?$args" : $self;
            $a[] = html_link($queu,'queue');
            $a[] = html_link($next,'next');
            $a[] = html_link($last,'last');
            $a[] = html_link("$cmd=menu",'menu');
            $a[] = html_link('../acct/index.php','home');
            $a[] = html_link($redo,'again');
        }
        return jumplist($a);
    }


    function gang_href(&$env,$act)
    {
        $self = $env['self'];
        $page = $env['page'];
        $limt = $env['limt'];
        $ord  = $env['ord'];
        $args = array("$self?act=$act&o=$ord&p=$page&l=$limt");
        query_state($env,$args);
        return join('&',$args);
    }


    function gang_link(&$env,$act,$text)
    {
        $href = gang_href($env,$act);
        return html_link($href,$text);
    }


    function query_state(&$env,&$set)
    {
        $adv = $env['adv'];
        $chg = $env['chg'];
        $crt = $env['crt'];
        $cyc = $env['cyc'];
        $def = $env['def'];
        $det = $env['det'];
        $dsp = $env['dsp'];
        $enb = $env['enb'];
        $fmt = $env['fmt'];
        $gbl = $env['gbl'];
        $lnk = $env['lnk'];
        $lst = $env['lst'];
        $mal = $env['mal'];
        $mod = $env['mod'];
        $nxt = $env['nxt'];
        $out = $env['out'];
        $own = $env['own'];
        $pat = $env['pat'];
        $qry = $env['qry'];
        $src = $env['src'];
        $skp = $env['skp'];
        $txt = $env['txt'];
        $tab = $env['tab'];
        $gnc = $env['gnc'];
        $gxc = $env['gxc'];
        $dbg = $env['dbug'];
        $prv = $env['priv'];

        if ($adv != 1) $set[] = "adv=$adv";
        if ($chg != 0) $set[] = "chg=$chg";
        if ($cyc != 0) $set[] = "cyc=$cyc";
        if ($dsp != 0) $set[] = "dsp=$dsp";
        if ($lst != 0) $set[] = "lst=$lst";
        if ($mal != 0) $set[] = "mal=$mal";
        if ($qry != 0) $set[] = "qry=$qry";

        if ($crt != -1) $set[] = "crt=$crt";
        if ($def != -1) $set[] = "def=$def";
        if ($det != -1) $set[] = "det=$det";
        if ($enb != -1) $set[] = "enb=$enb";
        if ($fmt != -1) $set[] = "fmt=$fmt";
        if ($gbl != -1) $set[] = "gbl=$gbl";
        if ($lnk != -1) $set[] = "lnk=$lnk";
        if ($mod != -1) $set[] = "mod=$mod";
        if ($nxt != -1) $set[] = "nxt=$nxt";
        if ($out != -1) $set[] = "out=$out";
        if ($own != -1) $set[] = "own=$own";
        if ($skp != -1) $set[] = "skp=$skp";
        if ($tab != -1) $set[] = "tab=$tab";
        if ($gnc != -1) $set[] = "gnc=$gnc";
        if ($gxc != -1) $set[] = "gxc=$gxc";

        if ($pat != '')
        {
            $value = urlencode($pat);
            $set[] = "pat=$value";
        }
        if ($txt != '')
        {
            $value = urlencode($txt);
            $set[] = "txt=$value";
        }
        if ($src != '')
        {
            $value = urlencode($src);
            $set[] = "src=$value";
        }
        if (($prv) && ($dbg)) $set[] = "debug=1";
    }


    function green($msg)
    {
        return "<font color=\"green\">$msg</font>";
    }

    function debug_array($debug,$p)
    {
        if ($debug)
        {
            reset($p);
            foreach ($p as $key => $data)
            {
                $msg = green("$key: $data");
                echo "$msg<br>\n";
            }
        }
    }


   /*
    |   Returns the original array, except that
    |   the empty elements have been filtered out.
    */

    function remove_empty($set)
    {
        $out = array( );
        reset($set);
        foreach ($set as $key => $data)
        {
            if ($data)
            {
                $out[] = $data;
            }
        }
        return $out;
    }


    function matchOld($act,$txt)
    {
        $tmp = "|$act|";
        return strpos($txt,$tmp);
    }


    function enabled($code)
    {
        switch ($code)
        {
            case  0: return 'Disabled';
            case  1: return 'Enabled';
            default: return 'Invalid';
        }
    }

   /*
    |  In general, D signifies the number of days of data we
    |  want to see, including today, except that we use -1 to
    |  signify that the field should not be displayed and
    |  zero to mean that any date is valid.
    |
    |   -1 --> not dispayed
    |    0 --> any date
    |    1 --> today since midnight
    |    2 --> yesterday
    |    3 --> day before yesterday
    */

    function date_code($when,$d)
    {
        if ($d > 1)
        {
            $when = days_ago($when,$d-1);
        }
        return $when;
    }


    function restrict_time(&$env,&$trm,$code,$field)
    {
        $valu = $env[$code];
        if ($valu > 0)
        {
            $midn  = $env['midn'];
            $time  = date_code($midn,$valu);
            $trm[] = "R.$field > $time";
        }
        if ($valu == -2)
        {
            $trm[] = "N.$field = 0";
        }
    }


    function ords()
    {
        $a = 'ascending';
        $d = 'descending';
        return array
        (
            0 => "Name ($a)",
            1 => "Name ($d)",
            2 => "Last Run ($d)",
            3 => "Last Run ($a)",
            4 => "Enabled ($a)",
            5 => "Enabled ($d)",
            6 => "Global ($a)",
            7 => "Global ($d)",
            8 => "Next Run ($a)",
            9 => "Next Run ($d)",
           12 => "E-mail Recipients ($a)",
           13 => "E-mail Recipients ($d)",
           14 => "Created ($d)",
           15 => "Created ($a)",
           16 => "Modify ($d)",
           17 => "Modify ($a)",
           18 => "Details ($a)",
           19 => "Details ($d)",
           20 => "Owner ($a)",
           21 => "Owner ($d)",
           22 => "Id ($a)",
           23 => "Id ($d)",
           24 => "When ($a)",
           25 => "When ($d)",
           26 => "Change ($a)",
           27 => "Change ($d)",
           28 => "Query ($a)",
           29 => "Query ($d)",
           30 => "Format ($a)",
           31 => "Format ($d)",
           32 => "Default Recipients ($a)",
           33 => "Default Recipients ($d)",
           34 => "Links ($a)",
           35 => "Links ($d)",
           36 => "Tabular ($a)",
           37 => "Tabular ($d)",
        );
    }


   /*
    |  When sorting by email addreses, remember that
    |  they only display when file = 0.  Also note
    |  that most of them are empty ...
    */

    function order($ord)
    {
        switch ($ord)
        {
            case  0: return 'name, username, id';
            case  1: return 'name desc, username, id';
            case  2: return 'last_run desc, id';
            case  3: return 'last_run, id';
            case  4: return 'enabled desc, id';
            case  5: return 'enabled, id';
            case  6: return 'global desc, id';
            case  7: return 'global, id';
            case  8: return 'next_run, global, cycle, id';
            case  9: return 'next_run desc, global desc, cycle desc, id';
            case 12: return 'file desc, emaillist, name, id desc';
            case 13: return 'file, emaillist desc, name desc, id';
            case 14: return 'created desc, id';
            case 15: return 'created, id';
            case 16: return 'modified desc, id';
            case 17: return 'modified, id';
            case 18: return 'content, id desc';
            case 19: return 'content desc, id';
            case 20: return 'username, name, id';
            case 21: return 'username desc, name desc, id desc';
            case 22: return 'id';
            case 23: return 'id desc';
            case 24: return 'hour, minute, id';
            case 25: return 'hour desc, minute desc';
            case 26: return 'change_rpt desc, name, id';
            case 27: return 'change_rpt, name desc, id';
            case 28: return 'query, name, id';
            case 29: return 'query desc, name desc, id';
            case 30: return 'format, name, id';
            case 31: return 'format desc, name desc, id';
            case 32: return 'defmail, name, id';
            case 33: return 'defmail desc, name desc, id';
            case 34: return 'links, name, id';
            case 35: return 'links desc, name desc, id';
            case 36: return 'tabular, name, id';
            case 37: return 'tabular desc, name desc, id';
            case 38: return 'group_include desc, name, id desc';
            case 39: return 'group_include, name';
            case 40: return 'group_exclude desc, name, id desc';
            case 41: return 'group_exclude, name';
            default: return order(0);
        }
    }


   /*
    |  We want to use the same procedure to generate sql for both
    |  the counting and the selection of records.
    */

    function gen_query(&$env,$count,$num)
    {
        $chg = $env['chg'];
        $cyc = $env['cyc'];
        $def = $env['def'];
        $det = $env['det'];
        $enb = $env['enb'];
        $fmt = $env['fmt'];
        $gbl = $env['gbl'];
        $lnk = $env['lnk'];
        $nxt = $env['nxt'];
        $out = $env['out'];
        $own = $env['own'];
        $pat = $env['pat'];
        $qry = $env['qry'];
        $skp = $env['skp'];
        $src = $env['src'];
        $tab = $env['tab'];
        $txt = $env['txt'];
        $gnc = $env['gnc'];
        $gxc = $env['gxc'];

        $auth = $env['auth'];
        $qu   = safe_addslashes($auth);
        if ($count)
        {
            $sel = "select count(R.id) from";
        }
        else
        {
            $sel = "select R.*,\n"
                 . " Q.name as query from";
        }
        $lft = array( );
        $ons = array( );
        $trm = array( );
        $tbl = array
        (
            'AssetReports as R',
            'AssetSearches as Q'
        );

        $trm = array
        (
            'R.searchid = Q.id'
        );
        if ($nxt > 0)
        {
            $value = time() + $nxt;
            $trm[] = "R.next_run < $value";
            $trm[] = "R.next_run > 0";
        }
        if ($chg > 0)
        {
            $value = $chg - 1;
            $trm[] = "R.change_rpt = $value";
        }

        if ($cyc > 0)
        {
            $value = $cyc - 1;
            $trm[] = "R.cycle = $value";
        }

        if ($def > 0)
        {
            $value = $def - 1;
            $trm[] = "R.defmail = $value";
        }

        if ($det > 0)
        {
            $value = $det - 1;
            $trm[] = "R.content = $value";
        }

        if ($fmt > 0)
        {
            $value = form_encode($fmt);
            $value = safe_addslashes($value);
            $trm[] = "R.format = '$value'";
        }

        if ($out > 0)
        {
            $value = $out - 1;
            $trm[] = "R.file = $value";
        }

        if ($lnk > 0)
        {
            $value = $lnk - 1;
            $trm[] = "R.links = $value";
        }

        if ($tab > 0)
        {
            $value = $tab - 1;
            $trm[] = "R.tabular = $value";
        }

        if ($skp > 0)
        {
            $value = $skp - 1;
            $trm[] = "R.skip_owner = $value";
        }

        if ($qry > 0)
        {
            $trm[] = "R.searchid = $qry";
        }

        if ($pat != '')
        {
            $value = str_replace('%','\%',$pat);
            $value = str_replace('_','\_',$value);
            $value = safe_addslashes($value);
            $trm[] = "R.name like '%$value%'";
        }

        if ($src != '')
        {
            $value = str_replace('%','\%',$src);
            $value = str_replace('_','\_',$value);
            $value = safe_addslashes($value);
            $trm[] = "Q.name like '%$value%'";
        }

       /*
        |  Pattern match on e-mail recipients also
        |  restricts to reports that send e-mail.
        */

        if ($txt != '')
        {
            $value = str_replace('%','\%',$txt);
            $value = str_replace('_','\_',$value);
            $value = safe_addslashes($value);
            $trm[] = "R.emaillist like '%$value%'";
            $trm[] = 'R.file = 0';
        }

        if ($own > 0)
        {
            $tbl[] =$GLOBALS['PREFIX'].'core.Users as U';
            $trm[] = 'U.username = R.username';
            $trm[] = "U.userid = $own";
        }

        if ($gnc > 0)
        {
            $trm[] = "R.group_include regexp '(^|,)$gnc(,|$)'";
        }

        if ($gxc > 0)
        {
            $trm[] = "R.group_exclude regexp '(^|,)$gxc(,|$)'";
        }


       /*
        |  Global:
        |   -1: same as 0, but not displayed
        |    0: both, honor local override
        |    1: locals owned by current user
        |    2: globals, honor local override
        |    3: debug only, show all
        |
        |    glbl_options()
        */

        if (($gbl <= 0) && ($own <= 0))
        {
            $u = "username = '$qu'";
            $lft[] = 'AssetReports as X';
            $ons[] = 'R.name = X.name';
            $ons[] = 'X.global = 0';
            $ons[] = "X.$u";
            $trm[] = "((R.$u) or (R.global = 1 and X.id is NULL))";
        }
        if (($gbl == 1) && ($own <= 0))
        {
            $trm[] = "R.username = '$qu'";
            $trm[] = 'R.global = 0';
        }
        if (($gbl == 1) && ($own > 0))
        {
            $trm[] = 'R.global = 0';
        }
        if (($gbl == 2) && ($own > 0))
        {
            $trm[] = 'R.global = 1';
        }
        if (($gbl == 2) && ($own <= 0))
        {
            $lft[] = 'AssetReports as X';
            $ons[] = 'X.name = R.name';
            $ons[] = 'X.global != R.global';
            $ons[] = "X.username = '$qu'";
            $trm[] = 'R.global = 1';
            $trm[] = 'X.id is NULL';
        }

        // enab_options()

        if ($enb == 1)
        {
            $trm[] = 'R.enabled = 0';
        }
        if ($enb == 2)
        {
            $trm[] = 'R.enabled = 1';
        }
        if ($enb > 2)
        {
            $trm[] = 'R.enabled > 1';
        }
        restrict_time($env,$trm,'mod','modified');
        restrict_time($env,$trm,'crt','created');
        restrict_time($env,$trm,'lst','last_run');

        if (!$trm)
        {
            $trm[] = 'R.id > 0';  // need at least one
        }

        $onss = '';
        $lfts = '';
        $tabs = join(",\n ",$tbl);
        $trms = join("\n and ",$trm);
        if ($lft)
        {
            $lj   = 'left join';
            $txt  = join("\n $lj ",$lft);
            $lfts = " $lj $txt\n";
        }
        if ($ons)
        {
            $txt  = join("\n and ",$ons);
            $onss = " on $txt\n";
        }

        if ($count)
        {
            $sql = "$sel\n $tabs\n${lfts}${onss} where $trms";
        }
        else
        {
            $ord  = $env['ord'];
            $page = $env['page'];
            $limt = $env['limt'];
            $ords = order($ord);
            debug_note("ord:$ord, page:$page, size:$limt");
            $pmin = ($page > 0)? $limt * $page : 0;
            if (($num <= $limt) || ($num <= $pmin))
            {
                $pmin = 0;
            }
            $sql = "$sel\n $tabs\n${lfts}${onss} where $trms\n"
                 . " order by $ords\n"
                 . " limit $pmin, $limt";
        }
        return $sql;
    }


    function find_report_count(&$env,$db)
    {
        $num = 0;
        $sql = gen_query($env,1,0);
        $res = redcommand($sql,$db);
        if ($res)
        {
            $num = mysqli_result($res, 0);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
        debug_note("There are $num total matching records.");
        return $num;
    }


    function check_queue($env,$db)
    {
        $lock = array();
        $lpid = array();
        $priv = $env['priv'];
        $now  = $env['now'];
        if ($priv)
        {
            $lock = find_opt('asset_lock',$db);
            $lpid = find_opt('asset_pid',$db);
        }
        if (($lock) && ($lpid))
        {
            if ($lock['value'])
            {
                $when = '';
                $age  = 'unknown';
                $time = $lpid['modified'];
                $ownr = $lpid['value'];
                if ($now > $time)
                {
                    $when = nanotime($time);
                    $age  = age($now - $time);
                }

                $text = "Report Queue Locked by $ownr since $when ($age)";
                echo "\n\n<br><h2>$text</h2><br>\n\n";
            }
        }
    }


    function find_query($qid,$db)
    {
        $row = array( );
        if ($qid)
        {
            $sql = "select * from AssetSearches\n"
                 . " where id = $qid";
            $row = find_one($sql,$db);
        }
        return $row;
    }

    function glbl_options()
    {
        return array
        (
             -1 => constTagNone,
              0 => constTagAny,
              1 => 'Local',
              2 => 'Global',
              3 => 'Debug'
        );
    }

    function enab_options()
    {
        return array
        (
             -1 => constTagNone,
              0 => constTagAny,
              1 => 'Disabled',
              2 => 'Enabled',
              3 => 'Invalid'
        );
    }


    function owns_options(&$env,$db)
    {
        $set = array();
        $out = disp_options();
        if ($env['user']['priv_admin'])
        {
            $sql = "select userid, username\n"
                 . " from ".$GLOBALS['PREFIX']."core.Users\n"
                 . " order by username";
            $set = find_many($sql,$db);
        }
        if ($set)
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $uid = $row['userid'];
                $out[$uid] = $row['username'];
            }
        }
        return $out;
    }


    function form_options()
    {
        return array
        (
            0 => 'None',
            1 => 'HTML w/o Charts',
            2 => 'HTML w/ Pie Charts',
            3 => 'MHTML w/ Pie Charts',
            4 => 'HTML w/ Bar Charts',
            5 => 'MHTML w/ Bar Charts',
            6 => 'HTML w/ Column Charts',
            7 => 'MHTML w/ Column Charts',
            8 => 'XML'
        );
    }


    function form_decode($txt)
    {
        switch ($txt)
        {
            case 'html'  : return 1;
            case 'pie'   : return 2;
            case 'mpie'  : return 3;
            case 'bar'   : return 4;
            case 'mbar'  : return 5;
            case 'column': return 6;
            case 'mcol'  : return 7;
            case 'xml'   : return 8;
            default      : return 1;
        }
    }


    function form_encode($frm)
    {
        switch ($frm)
        {
            case  1: return 'html';
            case  2: return 'pie';
            case  3: return 'mpie';
            case  4: return 'bar';
            case  5: return 'mbar';
            case  6: return 'column';
            case  7: return 'mcol';
            case  8: return 'xml';
            default: return 'html';
        }
    }


    function type_options()
    {
        return array
        (
            ReportTypeInvalid   => indent(6),
            ReportTypeDaily     => 'Daily',      // 0
            ReportTypeWeekly    => 'Weekly',     // 1
            ReportTypeMonthly   => 'Monthly',    // 2
            ReportTypeWeekdays  => 'Weekdays',   // 3
            ReportTypeImmediate => 'Immediate'   // 4
        );
    }

    function cycs_options()
    {
        $type = type_options();
        return array
        (
             -1 => constTagNone,
              0 => constTagAny,
              1 => $type[0],
              2 => $type[1],
              3 => $type[2],
              4 => $type[3],
              5 => $type[4]
        );
    }


    function past_options($midn,$days)
    {
        $opts = array
        (
            -2 => constTagNever,
            -1 => constTagNone,
             0 => constTagAny,
             1 => constTagToday
        );
        reset($days);
        foreach ($days as $key => $day)
        {
            $time = date_code($midn,$day);
            $text = date('D m/d',$time) . " ($day days)";
            $opts[$day] = $text;
        }
        return $opts;
    }


    function disp_options()
    {
        return array
        (
             -1 => constTagNone,
              0 => constTagAny
        );
    }


    function future_options()
    {
        $m = 60;
        $h = $m * 60;
        $d = $h * 24;
        return array
        (
            $h*1  => '1 hour',
            $h*2  => '2 hours',
            $h*3  => '3 hours',
            $h*4  => '4 hours',
            $h*5  => '5 hours',
            $h*6  => '6 hours',
            $h*7  => '7 hours',
            $h*8  => '8 hours',
            $h*9  => '9 hours',
            $h*10 => '10 hours',
            $h*11 => '11 hours',
            $h*12 => '12 hours',
            $h*13 => '13 hours',
            $h*14 => '14 hours',
            $h*15 => '15 hours',
            $h*16 => '16 hours',
            $h*17 => '17 hours',
            $h*18 => '18 hours',
            $h*19 => '19 hours',
            $h*20 => '20 hours',
            $h*21 => '21 hours',
            $h*22 => '22 hours',
            $h*23 => '23 hours',
            $d    => '1 day',
            $d*2  => '2 days',
            $d*3  => '3 days',
            $d*4  => '4 days',
            $d*5  => '5 days',
            $d*6  => '6 days',
            $d*7  => '1 week',
            $d*14 => '2 weeks'
        );
    }


    function secs_options()
    {
        $out = disp_options();
        $set = future_options();

        reset($set);
        foreach ($set as $secs => $txt)
        {
            $out[$secs] = $txt;
        }
        return $out;
    }

    function fmts_options()
    {
        $set = disp_options();
        $frm = form_options();
        unset($frm[0]);
        reset($frm);
        foreach ($frm as $key => $data)
        {
            $set[$key] = $data;
        }
        return $set;
    }

    function defs_options()
    {
        return array
        (
             -1 => constTagNone,
              0 => constTagAny,
              1 => 'No',
              2 => 'Yes'
        );
    }

    function dets_options()
    {
        return array
        (
             -1 => constTagNone,
              0 => constTagAny,
              1 => 'No Details',
              2 => 'Details'
        );
    }

    function outs_options()
    {
        return array
        (
             -1 => constTagNone,
              0 => constTagAny,
              1 => 'e-mail',
              2 => 'information portal',
              3 => 'xml'
        );
    }

    function chng_options()
    {
        return array
        (
             -1 => constTagNone,
              0 => constTagAny,
              1 => 'status report',
              2 => 'change report'
        );
    }

    function query_options($auth,$db)
    {
        $qu  = safe_addslashes($auth);
        $sql = "select S.id, S.name from\n"
             . " ".$GLOBALS['PREFIX']."asset.AssetSearches as S\n"
             . " left join ".$GLOBALS['PREFIX']."asset.AssetSearches as X\n"
             . " on X.name = S.name\n"
             . " and X.global = 0\n"
             . " and X.username = '$qu'\n"
             . " where S.username = '$qu'\n"
             . " or (S.global = 1 and (X.id is NULL))\n"
             . " order by name, id";
        $set = find_many($sql,$db);
        $out = array();
        reset($set);
        foreach ($set as $key => $row)
        {
             $qid = $row['id'];
             $out[$qid] = $row['name'];
        }
        return $out;
    }


    function qury_options($auth,$db)
    {
        $out = disp_options();
        $qids = query_options($auth,$db);
        reset($qids);
        foreach ($qids as $qid => $name)
        {
            $out[$qid] = $name;
        }
        return $out;
    }


    function report_control(&$env,$total,$db)
    {
        $auth = $env['auth'];
        $limt = $env['limt'];
        $page = $env['page'];
        $priv = $env['priv'];
        $self = $env['self'];
        $jump = $env['jump'];
        $ord  = $env['ord'];
        $adv  = $env['adv'];
        $form = $self . $jump;

        echo post_other('myform',$form);
        echo hidden('act','list');
        echo hidden('adv',$adv);
        echo hidden('page',$page);

        $days = array(2,3,4,5,6,7,8,9,10,11,12,13,14,21,30,60,90,120,150,180,365,720,3000);
        $lims = array(5,10,20,25,50,75,100,150,200,250,500,1000);

        if (!in_array($limt,$lims))
        {
            $lims[] = $limt;
            sort($lims,SORT_NUMERIC);
        }

        $midn = $env['midn'];
        $dsps = array('Expanded','Compact');
        $ords = ords();

        $chgs = chng_options();
        $cycs = cycs_options();
        $disp = disp_options();
        $enbs = enab_options();
        $glbs = glbl_options();
        $outs = outs_options();
        $owns = owns_options($env,$db);
        $qids = qury_options($auth,$db);
        $opts = past_options($midn,$days);
        $tiny = 50;
        $norm = 128;
        $wide = $norm*2 + 6;
        $yn = array('No','Yes');

        if (!$priv)
        {
            unset($glbs[3]);
            unset($enbs[3]);
        }

        $sel_include = GRPS_create_select_box($auth, constGroupIncludeTempTable,
                                              's_g_include', $env['gnc'],
                                              constAssetReports, $db);

        $sel_exclude = GRPS_create_select_box($auth, constGroupExcludeTempTable,
                                              's_g_exclude', $env['gxc'],
                                              constAssetReports, $db);

        $s_dbg = tiny_select('debug', $yn, $env['dbug'],1, $tiny);
        $s_lim = tiny_select('l',   $lims, $env['limt'],0, $tiny);
        $s_ord = tiny_select('o',   $ords, $env['ord'], 1, $norm);

        $s_chg = tiny_select('chg', $chgs, $env['chg'], 1, $norm);
        $s_cyc = tiny_select('cyc', $cycs, $env['cyc'], 1, $norm);
        $s_dsp = tiny_select('dsp', $dsps, $env['dsp'], 1, $norm);
        $s_enb = tiny_select('enb', $enbs, $env['enb'], 1, $norm);
        $s_gbl = tiny_select('gbl', $glbs, $env['gbl'], 1, $norm);
        $s_lst = tiny_select('lst', $opts, $env['lst'], 1, $norm);
        $s_mal = tiny_select('mal', $disp, $env['mal'], 1, $norm);
        $s_out = tiny_select('out', $outs, $env['out'], 1, $norm);
        $s_qry = tiny_select('qry', $qids, $env['qry'], 1, $wide);
        $s_own = tiny_select('own', $owns, $env['own'], 1, $norm);
        if ($adv)
        {
            $defs = defs_options();
            $dets = dets_options();
            $fmts = fmts_options();
            $secs = secs_options();
            $s_crt = tiny_select('crt', $opts, $env['crt'], 1, $norm);
            $s_det = tiny_select('det', $dets, $env['det'], 1, $norm);
            $s_def = tiny_select('def', $defs, $env['def'], 1, $norm);
            $s_fmt = tiny_select('fmt', $fmts, $env['fmt'], 1, $norm);
            $s_lnk = tiny_select('lnk', $defs, $env['lnk'], 1, $norm);
            $s_mod = tiny_select('mod', $opts, $env['mod'], 1, $norm);
            $s_nxt = tiny_select('nxt', $secs, $env['nxt'], 1, $norm);
            $s_skp = tiny_select('skp', $defs, $env['skp'], 1, $norm);
            $s_tab = tiny_select('tab', $defs, $env['tab'], 1, $norm);
        }

        $s_pat = tinybox('pat', 40, $env['pat'], $norm);
        $s_txt = tinybox('txt', 40, $env['txt'], $norm);
        $s_src = tinybox('src', 40, $env['src'], $norm);

        $href = 'areport.htm';
        $open = "window.open('$href','help');";
        $help = click(constButtonHlp,$open);

        $tag  = ($adv)? constButtonLess : constButtonMore;
        $tag  = button($tag);
        $sub  = button(constButtonSub);
        $rset = button(constButtonRst);
        $head = table_header();
        $srch = pretty_header('Search Options',1);
        $disp = pretty_header('Display Options',1);
        $td   = 'td style="font-size: xx-small"';
        $ts   = $td . ' colspan="2"';
        $xn   = indent(4);
        $rec  = 'Recipient';
        $dbug = '';
        $advanced = '';
        if ($adv)
        {
            $advanced = <<< ADVANCED

            <tr>
              <$td>Details        <br>\n$s_det</td>
              <$td>Output         <br>\n$s_out</td>
              <$td>Format         <br>\n$s_fmt</td>
              <$td>Skip Owner     <br>\n$s_skp</td>
            </tr>
            <tr>
              <$td>Links          <br>\n$s_lnk</td>
              <$td>Default ${rec}s<br>\n$s_def</td>
              <$td>Created        <br>\n$s_crt</td>
              <$td>Modified       <br>\n$s_mod</td>
            </tr>
            <tr>
              <$td>Next Run       <br>\n$s_nxt</td>
              <$td>Tabulate       <br>\n$s_tab</td>
              <$td>Include        <br>\n$sel_include</td>
              <$td>Exclude        <br>\n$sel_exclude</td>
            </tr>
            <tr>


            </tr>\n
ADVANCED;

        }

        if ($priv)
        {
            $dbg  = green('Debug');
            $dbug = "\n<$td>$dbg<br>\n$s_dbg</td>\n";
        }

        echo <<< XXXX

        <table>
        <tr valign="top">
          <td rowspan="2">

            $head

            $srch

            <tr><td>
              <table border="0" width="100%">
              <tr>
                <$td>Name Contains  <br>\n$s_pat</td>
                <$td>State          <br>\n$s_enb</td>
                <$td>Last Run       <br>\n$s_lst</td>
                <$td>Report Cycle   <br>\n$s_cyc</td>
              </tr>
              <tr>
                <$td>$rec Contains  <br>\n$s_txt</td>
                <$td>E-mail ${rec}s <br>\n$s_mal</td>
                <$td>Scope          <br>\n$s_gbl</td>
                <$td>Report Type    <br>\n$s_chg</td>
              </tr>
              <tr>
                <$td>Query Contains <br>\n$s_src</td>
                <$ts>Query          <br>\n$s_qry</td>
                <$td>Owner          <br>\n$s_own</td> $advanced
              </tr>
              </table>
            </td></tr>
            </table>

          </td>

          <td rowspan="2">
            $xn
          </td>

          <td>
            $head
            $disp

            <tr><td>
              <table border="0" width="100%">
              <tr>
                <$td>Page Size  <br>\n$s_lim  </td>
                <$td>Sort By    <br>\n$s_ord  </td>
                <$td>Display    <br>\n$s_dsp  </td>$dbug
              </tr>
              </table>
            </td></tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
            <table width="100%">
            <tr><td align="left" valign="bottom">

              ${sub}${xn}${help}${xn}${tag}${xn}${rset}

            </td></tr>
            </table>
           <td>
        </tr>
        </table>

        <br clear="all">


XXXX;

        echo form_footer();
    }


    function delete_conf(&$env,$db)
    {
        echo again($env);
        $rid = $env['rid'];
        $rep = find_report($rid,$db);
        if ($rep)
        {
            $auth = $env['auth'];
            $self = $env['self'];
            $priv = $env['user']['priv_admin'];
            $user = $rep['username'];
            $name = $rep['name'];
            $mine = ($user == $auth);
            if (($mine) || ($priv))
            {
                $href = "$self?act=rdel&rid=$rid";
                $yes  = html_link($href,'[Yes]');
                $no   = html_link($self,'[No]');
                $in   = indent(5);

                echo <<< HERE

                <br>
                <p>Do you really want to delete <b>$name</b>?</p>
                <p>${yes}${in}${no}</p>

HERE;
            }
        }
        echo again($env);
    }


   /*
    |  check the username clause, just in case.
    */

    function delete_act(&$env,$db)
    {
        echo again($env);
        $rid = $env['rid'];
        $rep = find_report($rid,$db);
        if ($rep)
        {
            $auth = $env['auth'];
            $priv = $env['user']['priv_admin'];
            $name = $rep['name'];
            $sql = "delete from AssetReports\n"
                 . " where id = $rid";
            if (!$priv)
            {
                $qu  = safe_addslashes($auth);
                $sql = "$sql\n and username = '$qu'";
            }
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
            if ($num > 0)
            {
                debug_note("$num reports removed");
                echo para("Report <b>$name</b> has been deleted.");
            }
        }
        echo again($env);
    }


    function list_report(&$env,$db)
    {
        $p = 'p style="font-size:8pt"';
        echo <<< ZORT

        <$p>
          Click on the <i>manage</i> link below to perform
          management actions (e.g. edit) on multiple
          reports.
        </p>

        <$p>
          Clicking on the <i>control</i> and <i>table</i> links will
          take you to the beginning of the <i>Search Options</i> panel,
          and the report list, respectively.
        </p>
ZORT;
        $num = find_report_count($env,$db);
        $sql = gen_query($env,0,$num);
        $set = find_many($sql,$db);
        echo mark('control');
        echo again($env);
        report_control($env,$num,$db);
        echo mark('table');
        echo again($env);
        if ($set)
        {
            $tmp = safe_count($set);
            debug_note("There were $tmp records loaded.");
            report_table($env,$set,$num,$db);
        }
        else
        {
            echo para('There were no matching reports ...');
        }
        echo again($env);
    }

    function show(&$env,&$args,$tag,$col)
    {
        if ($env[$tag]) $args[] = $col;
    }


    function page_href(&$env,$page,$ord)
    {
        $self = $env['self'];
        $limt = $env['limt'];

        $a   = array("$self?p=$page");
        $a[] = "o=$ord";
        $a[] = "l=$limt";
        query_state($env,$a);
        return join('&',$a);
    }



    function age($secs)
    {
        if ($secs <= 0) $secs = 0;

        $ss = intval($secs);
        $mm = intval($secs / 60);
        $hh = intval($secs / 3600);
        $dd = intval($secs / 86400);

        $ss = $ss % 60;
        $mm = $mm % 60;
        $hh = $hh % 24;

        if ($secs < 3600)
            $txt = sprintf('%d:%02d',$mm,$ss);
        if ((3600 <= $secs) && ($secs < 86400))
            $txt = sprintf('%d:%02d:%02d',$hh,$mm,$ss);
        if ((86400 <= $secs) && ($dd <= 7))
            $txt = sprintf('%d %02d:%02d:%02d',$dd,$hh,$mm,$ss);
        if (8 <= $dd)
        {
            $dd  = intval(round($secs / 86400));
            $txt = "$dd days";
        }

        return $txt;
    }


    function shed(&$env,&$rep)
    {
        $cycl = $rep['cycle'];
        $mint = $rep['minute'];
        $hour = $rep['hour'];
        $type = $env['cycl'][$cycl];
        $hhmm = sprintf('%02d:%02d',$hour,$mint);
        switch ($cycl)
        {
            case ReportTypeDaily:
                return "$hhmm $type";
            case ReportTypeWeekly:
                $wday = $rep['wday'];
                $text = $env['days'][$wday];
                return "$hhmm $text";
            case ReportTypeMonthly:
                $mday = $rep['mday'];
                return "$hhmm $type $mday";
            case ReportTypeWeekdays:
                return "$hhmm $type";
            case ReportTypeImmediate:
                $umin = $rep['umin'];
                $umax = $rep['umax'];
                $text = $type;
                if (($umin) && ($umax))
                {
                    $dmin = nanotime($umin);
                    $dmax = nanotime($umax);
                    $text = "$dmin - $dmax";
                }
                return $text;
            default:
                return "Invalid ($cycl)";
        }
    }


    function claim_lock(&$env,$db)
    {
        echo again($env);
        $pid  = $env['pid'];
        $lock = server_int('asset_lock',0,$db);
        $lpid = server_int('asset_pid',0,$db);
        $timo = server_int('report_timeout',0,$db);
        if (($lock) && ($lpid))
        {
            echo para("Report Lock owned by <b>$lpid</b>.");
        }
        else
        {
            if (($timo > 0) && ($pid))
            {
                $now  = time();
                $when = ($now - $timo) + 60;
                echo para("Timeout is <b>$timo</b> seconds.");
                if (update_opt('asset_lock','1',$db))
                {
                    $sql = "update ".$GLOBALS['PREFIX']."core.Options set\n"
                         . " value = $pid,\n"
                         . " modified = $when\n"
                         . " where name = 'report_pid'";
                    redcommand($sql,$db);
                    $xxx = nanotime($when);
                    $txt = "asset: fake lock by process $pid at $xxx";
                    echo para("Report Lock claimed by process <b>$pid</b>.");
                    logs::log(__FILE__, __LINE__, $txt,0);
                    debug_note($txt);
                }
            }
            else
            {
                echo para('Timeout is zero.');
            }
        }

        echo again($env);
    }


    function pick_lock(&$env,$db)
    {
        echo again($env);
        $lock = find_opt('asset_lock',$db);
        $lpid = find_opt('asset_pid',$db);
        if (($lock) && ($lpid))
        {
            if ($lock['value'])
            {
                $time = 0;
                $now  = $env['now'];
                $who  = ($lpid['value'])? $lpid['value'] : 'unknown';
                $when = $lock['modified'];
                if ((0 < $when) && ($when < $now))
                {
                    $time = ($now - $when);
                }
                $age = age($time);
                echo para("Report Lock owned by <b>$who</b>. ($age)");
            }
            else
            {
                echo para('Report Queue Was Not Locked.');
            }
            opt_update('asset_pid',0,0,$db);
            opt_update('asset_lock',0,0,$db);
        }
        echo again($env);
    }


    function queue_reset(&$env,$db)
    {
        $now = time();
        $day = 86400;
        $big = $now + (366 * $day);
        $xxx = $now - 300;
        $tab = 'AssetReports';
        $sql = "update $tab set\n"
             . " this_run = 0,\n"
             . " next_run = $xxx\n"
             . " where next_run < 0\n"
             . " and enabled = 1";
        $res = redcommand($sql,$db);
        $num = affected($res,$db);
        $sql = "update $tab set\n"
             . " this_run = 0,\n"
             . " next_run = 0\n"
             . " where next_run < 0\n"
             . " or this_run > 0";
        $res = redcommand($sql,$db);
        $dis = affected($res,$db);
        $sql = "update $tab set\n"
             . " next_run = 0\n"
             . " where enabled = 1\n"
             . " and next_run > $big";
        $res = redcommand($sql,$db);
        $foo = affected($res,$db);
        $sql = "update $tab set\n"
             . " next_run = 0,\n"
             . " last_run = $now\n"
             . " where last_run > $now\n"
             . " and enabled = 1";
        $res = redcommand($sql,$db);
        $xxx = affected($res,$db);
        pick_lock($env,$db);
    }


    function find_scalar($sql,$db)
    {
        $val = '';
        $res = command($sql,$db);
        if ($res)
        {
            $val = mysqli_result($res, 0);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
        return $val;
    }


    function statistics($env,$db)
    {
        echo again($env);
        $now  = time();
        $num  = 'select count(*) from AssetReports';
        $find = "$num\n where";
        $enab = "$find enabled = 1";
        $disb = "$find enabled = 0";
        $invl = "$find enabled = 2";
        $glob = "$find global = 1";
        $locl = "$find global = 0";
        $pend = "$find enabled = 1 and next_run < $now";
        $runn = "$find enabled = 1 and next_run < 0";

        $next = "select * from AssetReports\n"
              . " where enabled = 1\n"
              . " order by next_run, global, cycle, id\n"
              . " limit 1";
        $row = find_one($next,$db);

        echo table_header();
        echo pretty_header('Asset Report Statistics',2);
        echo double('Total:',   find_scalar($num,$db));
        echo double('Enabled:', find_scalar($enab,$db));
        echo double('Disabled:',find_scalar($disb,$db));
        echo double('Invalid:', find_scalar($invl,$db));
        echo double('Global:',  find_scalar($glob,$db));
        echo double('Local:',   find_scalar($locl,$db));
        echo double('Pending:', find_scalar($pend,$db));
        echo double('Running:', find_scalar($runn,$db));
        if ($row)
        {
            $name = $row['name'];
            $user = $row['username'];
            $glob = $row['global'];
            $next = $row['next_run'];
            $stat = ($glob)? 'g' : 'l';
            $when = '<br>';
            if ($next <  0) $when = 'running now';
            if ($next == 0) $when = 'frozen';
            if ((0 < $next) && ($next <= $now))
            {
                $date = timestamp($next);
                $secs = age($now - $next);
                $when = "pending ($secs) $date";
            }
            if ($now < $next)
            {
                $date = date('m/d/y H:i:s',$next);
                $secs = age($next - $now);
                $when = "future ($secs) $date";
            }
            echo double('Next scheduled:',$name);
            echo double('Owned by',"$user($stat)");
            echo double('When:',$when);
        }
        echo table_footer();
        echo again($env);
    }


    function default_report()
    {
        return array
        (
            'id'           => 0,
            'global'       => 0,
            'name'         => '',
            'username'     => '',
            'emaillist'    => '',
            'defmail'      => 1,
            'file'         => 0,
            'format'       => 'html',
            'cycle'        => ReportTypeWeekly,
            'hour'         => 0,
            'minute'       => 20,
            'wday'         => 0,
            'mday'         => 1,
            'enabled'      => 1,
            'links'        => 1,
            'last_run'     => 0,
            'next_run'     => 0,
            'this_run'     => 0,
            'order1'       => '',
            'order2'       => '',
            'order3'       => '',
            'order4'       => '',
            'searchid'     => 0,
            'content'      => 1,
            'change_rpt'   => 0,
            'umin'         => 0,
            'umax'         => 0,
            'created'      => 0,
            'modified'     => 0,
            'retries'      => 0,
            'log'          => 0,
            'include_user' => 0,
            'include_text' => 0,
            'subject_text' => '',
            'skip_owner'   => 0,
            'tabular'      => 1,
            'xmluser'      => '',
            'xmlpass'      => '',
            'xmlurl'       => '',
            'xmlfile'      => '',
            'xmlpasv'      => 1,
            'group_include'=> '',
            'group_exclude'=> ''
        );
    }

    function report_ints()
    {
        return array
        (
            'global','defmail','file','cycle',
            'hour','minute','wday','mday',
            'enabled','links','searchid','content',
            'change_rpt','log','include_user',
            'include_text','skip_owner','tabular'
        );
    }


    function array_lookup($keys,$names)
    {
        $t = array( );
        foreach ($keys as $key => $data)
        {
            $t[] = $names[$data];
        }
        return $t;
    }

    function asset_saved_search($searches, $searchid)
    {
        if ($searches)
        {
            $op = 'option';
            $sl = 'selected';
            $js = 'onclick="get_displayfields_for_search(this.form,this.selectedIndex)"';
            $m = "\n\n<select name=\"searchid\" size=\"5\" $js>\n";
            reset($searches);
            foreach ($searches as $id => $name)
            {
               $opt = ($id == $searchid)? "$op $sl" : $op;
               $m .= "<$opt value=\"$id\">$name</$op>\n";
            }
            $m .= "</select>\n\n";
        }
        else
        {
            $m = <<< HERE
            <input type="hidden" name="searchid" value="0">
            <b>No saved queries</b>
HERE;
        }
        return $m;
    }


    function content_section(&$env, &$row, $db)
    {
        $qid = $row['searchid'];

        $o1 = trim($row['order1']);
        $o2 = trim($row['order2']);
        $o3 = trim($row['order3']);
        $o4 = trim($row['order4']);
        $ln = intval($row['links']);
        $de = $row['content'];
        $en = $row['enabled'];
        $tb = $row['tabular'];

        $none = asset_null();
        $ords = asset_order_options($qid,$db);
        $ords = prepend($none,$ords);

        if ($o1 == '') $o1 = $none;
        if ($o2 == '') $o2 = $none;
        if ($o3 == '') $o3 = $none;
        if ($o4 == '') $o4 = $none;

        $yn = array ('No','Yes');
        $s_o1 = html_select('order1', $ords, $o1,0);
        $s_o2 = html_select('order2', $ords, $o2,0);
        $s_o3 = html_select('order3', $ords, $o3,0);
        $s_o4 = html_select('order4', $ords, $o4,0);

        // these select boxes use keys, so pass a key, not value, as 3rd param
        $s_link = html_select('links',      $yn, $ln,1);
        $s_dets = html_select('content',    $yn, $de,1);
        $s_enab = html_select('enabled',    $yn, $en,1);
        $s_tab  = html_select('tabular',    $yn, $tb,1);

        return <<< HERE

        <table cellpadding="2" cellspacing="0" border="0">
        <tr>
           <td colspan="3">
             <b>Grouping &amp; Sorting Options</b>
             <br>
             <font style="font-size:8pt">
             (not supported for reports in XML format)
           </td>
        </tr>
        <tr>
          <td nowrap>
            Categorize by:
          </td>
          <td colspan="2">
            $s_o1
          </td>
        </tr>
        <tr>
          <td nowrap>
            And then by:
          </td>
          <td colspan="2">
            $s_o2
          </td>
        </tr>
        <tr>
          <td nowrap>
            Then sort by:
          </td>
          <td colspan="2">
            $s_o3
          </td>
        </tr>
        <tr>
          <td nowrap>
            And last by:
          </td>
          <td colspan="2">
            $s_o4
          </td>
        </tr>

        <tr>
          <td nowrap>
            Include Links:
          </td>
          <td colspan="2">
            $s_link
          </td>
        </tr>

        <tr>
          <td nowrap>
            Include details:
          </td>
          <td>
            $s_dets
          </td>
          <td>
            <span class="footnote">
              Include detail asset information, or just a summary.
            </span>
          </td>
        </tr>

        <tr>
         <td>
           Details in by-system tables:
         </td>
         <td>
           $s_tab
         </td>
         <td>
           <span class="footnote">
           Details must be on for this option.
           </span>
         </td>
        </tr>

        <tr>
          <td>
            Enabled:
          </td>
          <td colspan="2">
            $s_enab
          </td>
        </tr>

        </table>

HERE;

    }

    function prepend($value,$arr)
    {
        $x = array($value);
        foreach ($arr as $key => $data)
        {
           $x[] = $data;
        }
        return $x;
    }




    function wday_options()
    {
        return array
        (
           -1 => indent(5),
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thusrday',
            5 => 'Friday',
            6 => 'Saturday'
        );
    }


    function schedule_section(&$env,&$row)
    {
        $hour = $row['hour'];
        $mint = $row['minute'];
        $mday = $row['mday'];
        $wday = $row['wday'];
        $type = $row['cycle'];

        $types = type_options();
        $blank = indent(6);
        $mdays = range(0,28);
        $mdays[0] = $blank;
        $wdays = wday_options();
        $hours = range(0,23);
        $mins  = array(0, 10, 20, 30, 40, 50);

        $s_type = html_select('cycle',$types,$type,1);
        $s_hour = html_select('hour',$hours,$hour,0);
        $s_mint = html_select('minute',$mins, $mint, 0);
        $s_mday = html_select('mday',$mdays,$mday,1);
        $s_wday = html_select('wday',$wdays,$wday,1);

        return <<< HERE

        <table cellpadding="2" cellspacing="0" border="0">
        <tr>
          <td colspan="2" valign="top">
            <b>Report Schedule</b>
          </td>
        </tr>
        <tr>
          <td>
            Run Report:
          </td>
          <td>
            $s_type
          </td>
          <td class="footnote">
            required for all reports
          </td>
        </tr>
        <tr>
          <td>
             Hour:
          </td>
          <td>
             $s_hour
          </td>
          <td class="footnote">
             for daily, weekly or monthly reports
          </td>
        </tr>

        <tr>
          <td>
             Minute:
          </td>
          <td>
             $s_mint
          </td>
          <td class="footnote">
             for daily, weekly or monthly reports
          </td>
        </tr>

        <tr>
          <td>
             Day of month:
          </td>
          <td>
             $s_mday
          </td>
          <td class="footnote">
             only needed for monthly reports
          </td>
        </tr>

        <tr>
          <td>
             Day of week:
          </td>
          <td>
             $s_wday
          </td>
          <td class="footnote">
             only needed for weekly reports
          </td>
        </tr>

        </table>

HERE;

    }

    function textmax($name,$size,$max,$valu)
    {
        $disp = str_replace('"','&quot;',$valu);
        $disp = str_replace("'",'&#039;',$disp);
        return "<input type=\"text\" name=\"$name\" size=\"$size\" maxlength=\"$max\" value=\"$disp\">";
    }


    function report_form(&$env,&$rep,$head,$db)
    {
        $now  = $env['now'];
        $auth = $env['auth'];
        $priv = $env['priv'];
        $midn = $env['midn'];
        $curr_ginc = ($rep['group_include'])?
          $rep['group_include'] : GRPS_ReturnAllMgroupid($db);

        $curr_gexc =  $rep['group_exclude'];
        $gprv = ($env['user']['priv_areport'])? 1 : 0;
        $yn   = array('No','Yes');
        $out  = array('Send as email','Publish on information portal','FTP (XML Only)');
        $rid  = $rep['id'];
        $frms = form_options();
        unset($frms[0]);
        $form = form_decode($rep['format']);
        $searches  = search_list($auth,$db);

        $s_form = html_select('format',$frms,$form,1);
        $s_file = html_select('file',$out,$rep['file'],1);
        $s_defm = html_select('defmail',$yn,$rep['defmail'],1);
        $s_subj = textbox('subject_text',40,$rep['subject_text']);
        $s_mail = textbox('emaillist',40,$rep['emaillist']);
        $s_xfil = textbox('xmlfile',30,$rep['xmlfile']);
        $s_xurl = textbox('xmlurl', 50,$rep['xmlurl']);
        $s_xusr = textbox('xmluser',50,$rep['xmluser']);
        $s_xpas = passbox('xmlpass',50,$rep['xmlpass']);
        $s_xvfy = passbox('xmlvrfy',50,$rep['xmlpass']);
        $s_name = textmax('name',50,50,$rep['name']);
        $s_text = checkbox('include_text',$rep['include_text']);
        $s_user = checkbox('include_user',$rep['include_user']);
        $s_pasv = checkbox('xmlpasv'     ,$rep['xmlpasv']);
        $s_qury = asset_saved_search($searches,$rep['searchid']);

        $grps = build_group_list($auth, constQueryNoRestrict, $db);
        $mstr = prep_for_multiple_select($grps);

        $sel_include = saved_search($mstr, $curr_ginc, 7, 'g_include[]',
                                    constMachineGroupMessage);
        $sel_exclude = saved_search($mstr, $curr_gexc, 7, 'g_exclude[]',
                                    constMachineGroupMessage);

        $custom_URL = customURL(constPageEntryAsset);
        $a_def      = preserve_asset_state($env['rid'], $env['act']);
        $group_link = html_link("../config/groups.php?$custom_URL&$a_def",
                                '[configure groups]');

        $elnk = html_link('query.php','[Edit a Query]');
        $alnk = html_link('qury-add.php','[Add a Query]');
        $cont = content_section($env,$rep,$db);
        $shed = schedule_section($env,$rep);

        $umin = $rep['umin'];
        $umax = $rep['umax'];
        $log  = $rep['log'];
        $chng = $rep['change_rpt'];
        $allf = 'change1,change2,change3,change4,change5,change6,change7';
        $chks = 'document.myform.change_rpt.checked';
        $date = 'change2,change4';
        $rite = 'true';

        $chck = ($chng)? 'checked' : '';
        $dmin = ($umin)? date('m/d/y H:i:s',$umin) : '';
        $dmax = ($umax)? date('m/d/y H:i:s',$umax) : '';
        $s0 = ($log == 0)? ' selected' : '';
        $s1 = ($log == 1)? ' selected' : '';

        $edit = ($rid > 0);

        echo post_self('myform');
        echo hidden('debug','1');
        if ($edit)
        {
            echo hidden('act','updt');
            echo hidden('rid',$rid);
            $sub = button('Update');
        }
        else
        {
            echo hidden('act','insn');
            $sub = button('Add');
        }

        /* group instructions */
        $please_note = GRPS_please_note();
        $inc_ins     = GRPS_include_instructions();
        $exc_ins     = GRPS_exclude_instructions(constAssetReports);

        $s_glob = '';
        if ($gprv)
        {
            $glob = checkbox('global',$rep['global']);
            $sbox = checkbox('skip_owner',$rep['skip_owner']);
            $s_glob = <<< HERE

            <tr>
              <td>
                Global:
              </td>
              <td colspan="2">
                $glob
              </td>
            </tr>

            <tr>
              <td>
                Do not generate report for owner:
              </td>
              <td colspan="2">
                $sbox
              </td>
            </tr>

            <tr>
              <td colspan="3">
                <br>
              </td>
            </tr>
HERE;

        }

        echo <<< HERE


    <table cellpadding="3" cellspacing="0" bordercolor="COCOCO" border="1">
    <tr>
      <th colspan=5 bgcolor="#333399">
        <font color="white">
          <b>$head</b>
        </font>
      </th>
    </tr>

    <tr>
      <td colspan="5">

      <table cellpadding="2" cellspacing="0" border="0">
      <tr>
        <td>
          Report title:
        </td>
        <td colspan="2">
          $s_name
        </td>
      </tr>

      <tr>
        <td>
          Report format:
        </td>
        <td colspan="2">
          $s_form
        </td>
      </tr>

      <tr>
        <td>
          Report output:
        </td>
        <td colspan="2">
          $s_file
        </td>
      </tr>

      <tr>
        <td>
          E-mail recipients:
        </td>
        <td colspan="2" class="footnote">
          $s_mail
          Enter all the recipients e-mail addresses separated by commas.
        </td>
      </tr>

      <tr>
        <td nowrap>
          Default e-mail recipients:
        </td>
        <td colspan="2" class="footnote">
          $s_defm
          Add default email list members to the list of e-mail recipients.
        </td>
      </tr>

      <tr>
        <td>
          Include user in email subject:
        </td>
        <td colspan="2">
          $s_user
        </td>
      </tr>

      <tr>
        <td>
          Additional text to be added<br>
          to the subject line:
        </td>
        <td colspan="2">
          $s_text
          $s_subj
        </td>
      </tr>

      <tr>
        <td colspan="1">
        </td>
        <td colspan="1">
          <hr>
      </tr>

      <tr>
        <td colspan="1">
        </td>
        <td colspan="2">
          Use the fields in this section only if you select XML for report format
        </td>
      </tr>

      <tr>
        <td>
          XML FTP Username:
        </td>
        <td>
          $s_xusr
        </td>
      </tr>

      <tr>
        <td>
          XML FTP Password:
        </td>
        <td colspan="2">
          $s_xpas
        </td>
      </tr>


      <tr>
        <td>
          Verify FTP Password:
        </td>
        <td colspan="2">
          $s_xvfy
        </td>
      </tr>

      <tr>
        <td>
          XML FTP URL:
        </td>
        <td colspan="3" style="font-size: 8pt">
          $s_xurl For example, <b>ftp.yoursite.com/path/to/dir</b>
        </td>
      </tr>

      <tr>
        <td colspan="2">
        </td>
        <td style="font-size: 8pt">
          Please note that you do not need to include a <b>/</b> as the
          last character in the FTP URL.
        </td>
      </tr>

      <tr>
        <td>
          XML Filename:
        </td>
        <td colspan="2" style="font-size: 8pt">
          $s_xfil
        </td>
      </tr>

      <tr>
        <td>
        </td>
        <td colspan="2" style="font-size: 8pt">
          Supported filename variables:  %i = min, %h = hour, %j = day, %m = month, %Y = year, %q = queryname, %u = username
          <br>
          For example: %m_%j_%Y_%q-%u.xml could result in the following file name: 08_25_05_Hardware-HFN.xml
          <br>
          In the filename, you do not have to include the .xml file extension.
        </td>
      </tr>

      <tr>
       <td>
        XML FTP Passive Mode:
       </td>
       <td colspan="2">
        $s_pasv
       </td>
      </tr>

      <tr>
       <td colspan="1">
       </td>
       <td colspan="2">
        <font style="font-size: 8pt">
        When passive FTP mode is enabled, the file transfer is initiated by the ASI client,
        instead of the FTP server.
        <br> It may be necessary for systems behind a firewall.
        By default Passive FTP is enabled.
       </td>
      </tr>

      <tr>
        <td colspan="1">
        </td>
        <td>
         <hr>
         <br>
        </td>
      </tr>

      <tr>
        <td rowspan="2" valign="top">
          Saved&nbsp;Queries:
        </td>
        <td rowspan="2" valign="top">
          $s_qury
        </td>
        <td valign="top" width="100%" style="font-size: 8pt">
          <span>
            To select, hold down the 'ctrl'
            key and click on desired entries.
            <br>
          </span>
          <span>
            To deselect, hold down 'ctrl'
            and click again.
            (Mac: command key)
          </span>
        </td>
      </tr>
      <tr>
        <td valign="bottom">
          $elnk<br>
          $alnk
        </td>
      </tr>


      $s_glob

      <tr>
        <td valign="top">
          Include:
        </td>
        <td colspan="2">
          <table>
          <tr>
            <td>
              $sel_include
            </td>
            <td>
              $inc_ins
              <br>
              <br>
              $group_link
              <br>
              $please_note
            </td>
          </tr>
          </table>
        </td>
      </tr>

      <tr>
        <td>
          <br>
        </td>
      </tr>

      <tr>
        <td valign="top">
          Exclude:
        </td>
        <td colspan="2">
        <table>
          <tr>
            <td>
              $sel_exclude
            </td>
            <td>
              $exc_ins
              <br>
              <br>
              $group_link
              <br>
              $please_note
            </td>
          </tr>
         </table>
       </td>
     </tr>

      <tr>
        <td colspan="3">
          <br>
        </td>
      </tr>
      <tr>
        <td valign="top">
          Change Report:
          <br>
           <font style="font-size:8pt">
           (not supported for
           <br>
           reports in XML format)
          <br>
          &nbsp;
        </td>
        <td colspan="2" valign="top" align="left">

        <table cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td valign="top" colspan="3">
            <input type="checkbox" $chck name="change_rpt"
              value="1" onClick="showElement('$allf',$chks,$rite,'$date');">
          </td>
        </tr>

        <tr>
          <td>
            <span id="change1">
              Start Date:
            </span>
          </td>
          <td>
            <input type="text" name="dmin" id="change2" value="$dmin" size="19">
          </td>
          <td rowspan="2">
            <span class="footnote" id="change5">
            <i>
              enter dates in mm/dd, mm/dd/yy, or mm/dd hh:mm format<br>
              dates only used for immediate asset change reports.
            </i>
            </span>
          </td>
        </tr>

        <tr>
          <td>
            <span id="change3">
              End Date:
            </span>
          </td>
          <td>
            <input type="text" name="dmax" id="change4" value="$dmax" size="19">
          </td>
        </tr>

        <tr>
          <td>
            <span id="change6">
              Output:
            </span>
          </td>
          <td colspan="2">
            <span id="change7">
              <select name="log" size="1">
              <option$s0 value="0">Start-End difference</option>
              <option$s1 value="1">Log of changes</option>
              </select>
            </span>
          </td>
        </tr>
        </table>

      </td>
      </tr>
      </table>

      <table cellpadding="2" cellspacing="0" border="0">
      <tr>
        <td valign="top" align="left">
          $cont
        </td>
        <td valign="top" align="left" colspan="2">
          $shed
        </td>
      </tr>
      </table>

      <table cellpadding="2" cellspacing="0" border="0">
      <tr>
          <td colspan="3">
            <br>
          </td>
      </tr>
      </table>

      </td>
    </tr>

    <tr>
      <td colspan="5">

        $sub
        &nbsp;&nbsp;&nbsp;
        <input type="reset" value="reset">

      </td>
    </tr>
    </table>

    <script language="JavaScript">

    if (window.document.myform.searchid.selectedIndex == -1)
    { // no query selected
        window.document.myform.order1.options[0] = null;
        window.document.myform.order1.options[0] = new Option("(first select a search)");
        window.document.myform.order1.options[0].value = "";
        window.document.myform.order1.selectedIndex = 0;

        window.document.myform.order2.options[0] = null;
        window.document.myform.order2.options[0] = new Option("(first select a search)");
        window.document.myform.order2.options[0].value = "";
        window.document.myform.order2.selectedIndex = 0;

        window.document.myform.order3.options[0] = null;
        window.document.myform.order3.options[0] = new Option("(first select a search)");
        window.document.myform.order3.options[0].value = "";
        window.document.myform.order3.selectedIndex = 0;

        window.document.myform.order4.options[0] = null;
        window.document.myform.order4.options[0] = new Option("(first select a search)");
        window.document.myform.order4.options[0].value = "";
        window.document.myform.order4.selectedIndex = 0;
    }
    </script>
HERE;

        $o1 = $rep['order1'];
        $o2 = $rep['order2'];
        $o3 = $rep['order3'];
        $o4 = $rep['order4'];
        $qids = searchid_list($searches);
        outputJavascriptShowElement($allf,$chks,$rite,$date);
        write_JS_order_options($qids,$o1,$o2,$o3,$o4,$db);
        echo form_footer();
    }


    /* string values get posted to here from the report form
    // validating occurs, and the report will succede or fail.
    */
    function validate(&$env,&$rep,&$errs,&$good,$db)
    {
        $auth = $env['auth'];
        if (!$rep)
        {
            $errs[] = 'The report does not exist.';
            $good = false;
        }
        $name = trim(get_string('name',''));
        if ($good)
        {
            $old  = $rep['enabled'];
            $ints = report_ints();
            reset($ints);
            foreach ($ints as $key => $fld)
            {
                $rep[$fld] = get_integer($fld,0);
            }
            $frm = get_integer('format',0);

            $sub = 'subject_text';
            $rep['name']      = $name;
            $rep['next_run']  = 0;
            $rep['this_run']  = 0;
            $rep['emaillist'] = get_string('emaillist','');
            $rep[$sub]        = get_string($sub,'');
            $rep['xmlpasv']   = get_integer('xmlpasv',0);

            $new = $rep['enabled'];
            if (($new) && ($old != $new))
            {
                $rep['retries'] = 0;
            }
        }
        /* output of the report */
        $file_type = $rep['file'];

        /* type of report */
        $format = form_encode($frm);
        $rep['format'] = $format;

        /* password(s) from user */
        $xmlpass = get_string('xmlpass','');
        $xmlvrfy = get_string('xmlvrfy','');
        $xmlurl  = get_string('xmlurl' ,'');
        $xmluser = get_string('xmluser','');

        /* xml filename from user */
        $xmlfile = get_string('xmlfile','');

        if ($name == '')
        {
            $errs[] = 'You must specify a name for the report.';
            $good   = false;
        }
        if ( ($format == 'xml') || ($file_type == 2) )
        {
            if ($xmlfile == '')
            {
                $errs[] = 'You must specify a name for the xml file.';
                $good   = false;
            }
            if ($xmlpass != $xmlvrfy)
            {
                $errs[] = 'Passwords must match for the report.';
                $good   = false;
            }
            if ($xmlurl == '')
            {
                $errs[] = 'URL must specify a remote location.';
                $good   = false;
            }
            if ($xmluser == '')
            {
                $errs[] = 'You must specify a login name.';
                $good   = false;
            }
        }
        if ($good)
        {
            //otherwise save the value(s).
            $rep['xmlpass'] = $xmlpass;
            $rep['xmlfile'] = $xmlfile;
            $rep['xmlurl']  = $xmlurl;
            $rep['xmluser'] = $xmluser;
        }

        $type = $rep['cycle'];
        $mint = $rep['minute'];
        $hour = $rep['hour'];
        $chng = $rep['change_rpt'];
        if ($good)
        {
            if (($mint < 0) || ($mint > 59) || ($hour < 0) || ($hour > 23))
            {
                $errs[] = 'Invalid time specification.';
                $good = false;
            }
        }

        if ($good)
        {
            if (($type < 0) || ($type > ReportTypeImmediate))
            {
                $errs[] = 'You must specify a report cycle for this report.';
                $good = false;
            }
        }
        if (($good) && ($type == ReportTypeWeekly))
        {
            $wday = $rep['wday'];
            if (($wday < 0) || ($wday > 6))
            {
                $errs[] = 'You must specify a day of the week for a weekly report.';
                $good = false;
            }
        }
        if (($good) && ($type == ReportTypeMonthly))
        {
            $mday = $rep['mday'];
            if (($mday < 0) || ($mday > 28))
            {
                $errs[] = 'You must specify a day of the month for a monthly report.';
                $good = false;
            }
        }
        if (($good) && ($chng))
        {
            $now  = $env['now'];
            $dmin = get_string('dmin','');
            $dmax = get_string('dmax','');
            $umin = ($dmin)? parsedate($dmin,$now) : 0;
            $umax = ($dmax)? parsedate($dmax,$now) : 0;
            if ((0 < $umin) && ($umin < $umax))
            {
                $rep['umin'] = $umin;
                $rep['umax'] = $umax;
            }
        }
        if ($good)
        {
            if (!$chng)
            {
                $rep['umin'] = 0;
                $rep['umax'] = 0;
            }
            if ($type != ReportTypeMonthly)
            {
                $rep['mday'] = 0;
            }
            if ($type != ReportTypeWeekly)
            {
                $rep['wday'] = 0;
            }
        }
        if ($good)
        {
            $rid = $rep['id'];
            if (owned_report_exists($name,$auth,$rid,$db))
            {
                $errs[] = "You already own a report named <b>$name</b>.";
                $good = false;
            }
        }
        if ($good)
        {
            $glob = $rep['global'];
            $gprv = $env['user']['priv_areport'];
            $rep['global'] = ($gprv)? $glob : 0;
            if ($rep['global'])
            {
                $rid = $rep['id'];
                if (global_report_exists($name,$rid,$db))
                {
                    $errs[] = "There is already a global report named <b>$name</b>.";
                    $good = false;
                }
            }
            else
            {
               $rep['skip_owner'] = 0;
            }
        }

        if ($good)
        {
            $rgbl = $rep['global'];
            $qid  = $rep['searchid'];
            $qury = find_query($qid,$db);
            if ($qury)
            {
                $glob = $qury['global'];
                $user = $qury['username'];
                if (!$glob)
                {
                    if ($rgbl)
                    {
                        $errs[] = "Can't use local query for global report.";
                        $good = false;
                    }
                    if ($user != $auth)
                    {
                        $errs[] = "Query authorization fault";
                        $good = false;
                    }
                }
            }
            else
            {
                $errs[] = 'Query not found.';
                $good = false;
            }
        }

        if ($good)
        {
            $group_include = GRPS_get_multiselect_values('g_include');
            if (!$group_include)
            {
                $errs[] = "Cannot create a report without an 'included group' selected.";
                $good   = false;
            }
            $rep['group_exclude'] = GRPS_get_multiselect_values('g_exclude');
            $rep['group_include'] = $group_include;
        }

        if ($good)
        {
            $names = asset_names($db);
            $null  = asset_null();
            $ords  = array('order1','order2','order3','order4');
            foreach ($ords as $key => $name)
            {
                $text = get_string($name,$null);
                $none = (($text == $null) || ($text == ''));
                $dids = ($none)? 0 : find_did($text,$db);
                $text = ($dids)? $names[$dids]['name'] : '';
                $rep[$name] = $text;
            }
        }
    }


    function detail_links(&$env,&$row,$db)
    {
        $auth = $env['auth'];
        $self = $env['self'];
        $priv = $env['priv'];
        $admn = $env['user']['priv_admin'];

        $now  = time();
        $rid  = $row['id'];
        $glob = $row['global'];
        $enab = $row['enabled'];
        $user = $row['username'];
        $next = $row['next_run'];
        $mine = ($user == $auth);
        $enab = ($row['enabled'] == 1)? 1 : 0;
        $cmd  = "$self?rid=$rid&act";
        $ax   = array( );

        $ax[] = html_link("$cmd=view",'details');
        if ($mine)
        {
            if ($enab)
                $ax[] = html_link("$cmd=disb",'disable');
            else
                $ax[] = html_link("$cmd=enab",'enable');
            $ax[] = html_link("$cmd=edit",'edit');
            $ax[] = html_link("$cmd=cdel",'delete');
        }
        $ax[] = html_link("$cmd=copy",'copy');
        if (($glob) && (!$mine))
        {
            $name = $row['name'];
            $locl = find_report_name($name,0,$auth,$db);
            if (!$locl)
            {
                $ax[] = html_link("$cmd=over",'edit');
                if ($enab)
                    $ax[] = html_link("$cmd=dovr",'disable');
                else
                    $ax[] = html_link("$cmd=eovr",'enable');
            }
        }
        if (($priv) && (!$mine))
        {
            if ($enab)
                $ax[] = html_link("$cmd=disb",'p.disable');
            else
                $ax[] = html_link("$cmd=enab",'p.enable');
            $ax[] = html_link("$cmd=cdel",'p.delete');
        }

        if (($glob) || ($mine) || ($admn))
        {
            $ax[] = html_link("$cmd=exec",'run');
        }

        if (($priv) && ($next > $now))
        {
            $ax[] = html_link("$cmd=redo",'p.redo');
        }
        if (($priv) && ($next > 0))
        {
            $ax[] = html_link("$cmd=skip",'p.skip');
        }
        return $ax;
    }


    function report_detail_table(&$env,$rid,$db)
    {
        $auth = $env['auth'];
        $self = $env['self'];
        $priv = $env['priv'];
        $defs = $env['user']['report_mail'];
        $admn = $env['user']['priv_admin'];
        $defs = ($defs)? "<i>$defs</i>" : '';
        $good = false;

        $row  = find_report($rid,$db);
        if ($row)
        {
            $user = $row['username'];
            $glob = $row['global'];
            $mine = ($user == $auth);
            $good = (($mine) || ($glob) || ($admn));
        }
        if ($good)
        {
            $now  = time();
            $rid  = $row['id'];
            $name = $row['name'];
            $file = $row['file'];
            $enab = $row['enabled'];
            $qid  = $row['searchid'];
            $next = $row['next_run'];
            $skip = $row['skip_owner'];
            $tab  = ($row['tabular'])? 'Yes' : 'No';
            $form = form_decode($row['format']);
            $mine = ($user == $auth);

            $scop = ($glob)? 'Global' : 'Local';
            $enab = enabled($row['enabled']);
            $shed = shed($env,$row);
            $type = $row['cycle'];
            $typs = type_options();
            $frms = form_options();
            $form = $frms[$form];
            $qury = find_query($qid,$db);

            $o1 = disp($row,'order1');
            $o2 = disp($row,'order2');
            $o3 = disp($row,'order3');
            $o4 = disp($row,'order4');

            $xmlfile = disp_one($row['xmlfile']);
            $xmluser = disp_one($row['xmluser']);
            $xmlurl  = disp_one($row['xmlurl']);
            $xmlpasv = disp_one($row['xmlpasv']);

            $ax = detail_links($env,$row,$db);

            $igrp = $row['group_include'];
            $egrp = $row['group_exclude'];

            $igrp = find_mgrp_gid($igrp, constReturnGroupTypeMany, $db);
            $egrp = find_mgrp_gid($egrp, constReturnGroupTypeMany, $db);

            $itxt = ($igrp)? GRPS_edit_group_detail($igrp) : 'All groups included';
            $etxt = ($egrp)? GRPS_edit_group_detail($egrp) : 'Nothing excluded';

            echo jumplist($ax);

            echo table_header();
            echo pretty_header($name,2);
            echo double(   'Owner', $row['username']);
            echo double(   'Scope', $scop);
            echo double(   'Cycle', $typs[$type]);
            echo double('Schedule', $shed);
            echo double(   'State', $enab);
            echo double(  'Format', $form);
            echo double( 'Tabular', $tab);
            echo double( 'Include', $itxt);
            echo double( 'Exclude', $etxt);
            if ($glob)
            {
                $skip_t = ($skip)? 'Yes' : 'No';
                echo double('Skip Owner',$skip_t);
            }

            if ($qury)
            {
                $text = '';
                $name = $qury['name'];
                $flds = $qury['displayfields'];
                $href = "query.php?act=view&qid=$qid";
                $link = html_link($href,$name);
                echo double('Query',$link);
                if ($flds)
                {
                    $set = explode(':',$flds);
                    reset($set);
                    foreach ($set as $key => $fld)
                    {
                        if (strlen($fld))
                        {
                            $text .= "$fld<br>\n";
                        }
                    }
                }
                if ($text)
                {
                    echo double('Display',$text);
                }
            }

            echo double('Categorize by',$o1);
            echo double(  'And then by',$o2);
            echo double( 'Then sort by',$o3);
            echo double(  'And last by',$o4);

            if ($row['retries'])
            {
                echo double('Retries',$row['retries']);
            }
            $text = 'No';

            if ( (!$file) && ($form != constFormXML) )
            {
                $lnk  = $row['links'];
                $dst  = $row['emaillist'];
                $defm = $row['defmail'];
                if (($glob) || ($mine))
                {
                    if (($defm) && ($defs))
                    {
                        $dst = ($dst)? "$defs,$dst" : $defs;
                    }
                }
                if ($dst)
                {
                    echo double('EMail',str_replace(',','<br>',$dst));
                }
                $lnk = ($lnk)? 'Yes' : 'No';
                echo double('Links',$lnk);
            }
            if ( ($file) && ($form != constFormXML) )
            {
                echo double('Output','Information Portal');
            }
            if ($form == constFormXML)
            {
                $xmlpasv = ($xmlpasv)? 'Yes' : 'No';

                echo double('Output'      ,'FTP XML');
                echo double('FTP User'    ,$xmluser);
                echo double('FTP URL'     ,$xmlurl);
                echo double('XML File'    ,$xmlfile);
                echo double('Passive Mode',$xmlpasv);
            }
            if ($row['umin'])
            {
                echo double('Start Time',showtime($now,$row['umin']));
            }
            if ($row['umax'])
            {
                echo double('Stop Time',showtime($now,$row['umax']));
            }
            echo double( 'Created', showtime($now,$row['created']));
            echo double('Modified', showtime($now,$row['modified']));
            echo double('Last Run', showtime($now,$row['last_run']));
            echo double('Next Run', showtime($now,$row['next_run']));
            if ($row['this_run'])
            {
                echo double('This Run',showtime($now,$row['this_run']));
            }
            if ($priv)
            {
                $date = green(datestring($now));
                $text = green('Now');
                echo double($text,$date);
            }
            echo table_footer();
            echo jumplist($ax);
        }
        else
        {
            if ($row)
            {
                $txt = 'No access to this report.';
            }
            else
            {
                $txt = "Report <b>$rid</b> does not exist.";
            }
            echo para($txt);
        }
    }


    function add_exec(&$env,$db)
    {
        echo again($env);
        $errs = array();
        $good = false;
        $auth = $env['auth'];
        $rid  = 0;
        $rep  = default_report();
        if ($rep)
        {
            $rep['id']       = 0;
            $rep['username'] = $auth;
            $good = true;
        }
        validate($env,$rep,$errs,$good,$db);
        if ($good)
        {
            $type = $rep['cycle'];
            $immd = ($type == ReportTypeImmediate);
            $rep['created']  = $env['now'];
            $rep['modified'] = $env['now'];
            $name = $rep['name'];
            $rid  = update_asset($rep,$db);
        }
        $good = false;
        if ($rid)
        {
            $rep['id'] = $rid;
            if ($immd)
            {
                $good = true;
            }
        }
        else
        {
            $errs[] = 'Could not create new report.';
        }

        if (($good) && ($rid))
        {
            echo para("Report <b>$name</b> has been created.");
            report_detail_table($env,$rid,$db);
        }
        if ($errs)
        {
            $txt = join("<br>\n",$errs);
            echo para($txt);
        }
        echo again($env);
    }


    function add_form(&$env,$db)
    {
        echo again($env);
        $rep = default_report();
        if ($rep)
        {
            $auth = $env['auth'];
            $glob = $rep['global'];
            $gprv = $env['user']['priv_areport'];
            $rep['id']     = 0;
            $rep['wday']   = -1;
            $rep['mday']   = 0;
            $rep['cycle']  = -1;
            $rep['global'] = ($gprv)? $glob : 0;
            $rep['username'] = $auth;
            $head = 'Add a Report';
            report_form($env,$rep,$head,$db);
        }
        echo again($env);
    }


    function touch_report($rid,$db)
    {
        if ($rid > 0)
        {
            $now = time();
            $sql = "update AssetReports set\n"
                 . " modified = $now\n"
                 . " where id = $rid";
            redcommand($sql,$db);
        }
    }

    function update_act(&$env,$db)
    {
        echo again($env);
        $good = false;
        $disp = false;
        $errs = array();
        $rid  = $env['rid'];
        $auth = $env['auth'];
        $rep  = find_report($rid,$db);
        if ($rep)
        {
            $user = $rep['username'];
            if ($user == $auth)
            {
                $good = true;
                $disp = true;
            }
            else
            {
                $errs[] = 'You do not own this report.';
            }
        }
        else
        {
            $errs[] = 'The report does not exist.';
        }
        validate($env,$rep,$errs,$good,$db);
        if ($good)
        {
            $num = update_asset($rep,$db);
            if ($num)
            {
                $errs[] = 'Report Updated';
                touch_report($rid,$db);
            }
            else
            {
                $errs[] = 'Report Unchanged';
            }
        }
        if ($errs)
        {
            $txt = join('<br>',$errs);
            echo para($txt);
        }
        if ($disp)
        {
            report_detail_table($env,$rid,$db);
        }
        echo again($env);
    }


    function gang_table(&$env,&$set,$frm,$head)
    {
        $rows = safe_count($set);

        if ($rows <= 0)
        {
            return;
        }

        $cols = (12 <= $rows)? 4 : 1;
        if ($frm)
        {
            $post = $env['post'];
            $aflg = ($post == constButtonAll);
            $nflg = ($post == constButtonNone);
        }

        $out = array( );
        $tmp = array( );

        reset($set);
        foreach ($set as $nnn => $data)
        {
            $nam = $data['name'];
            if ($frm)
            {
                $rid = $data['id'];
                $tag = "rid_$rid";
                $chk = get_integer($tag,0);
                $chk = ($aflg)? 1 : $chk;
                $chk = ($nflg)? 0 : $chk;
                $box = checkbox($tag,$chk) . '&nbsp;';
                $txt = $box . $nam;
            }
            else
            {
                $txt = $nam;
            }
            $tmp[$nnn] = $txt;
        }

        if ($cols > 1)
        {
            $dec = $cols - 1;
            $max = intval(($rows+$dec) / $cols);
            for ($row = 0; $row < $max; $row++)
            {
                for ($col = 0; $col < $cols; $col++)
                {
                    $out[$row][$col] = '<br>';
                }
            }
        }
        else
        {
            $max = $rows;
            $col = 0;
        }

        reset($tmp);
        foreach ($tmp as $nnn => $txt)
        {
            if ($cols > 1)
            {
                $row = intval($nnn % $max);
                $col = intval($nnn / $max);
            }
            else
            {
                $row = $nnn;
            }
            $out[$row][$col] = $txt;
        }

        $text = "$head &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text,$cols);

        reset($out);
        foreach ($out as $key => $args)
        {
            echo table_data($args,0);
        }
        echo table_footer();
    }


    function okcancel($n)
    {
        $in  = indent($n);
        $ok  = button(constButtonOk);
        $can = button(constButtonCan);
        return para("${in}${ok}${in}${can}");
    }


    function okcanhlp($n,$act)
    {
        $in  = indent($n);
        $ok  = button(constButtonOk);
        $can = button(constButtonCan);
        $hlp = click(constButtonHlp,$act);
        return para("${in}${ok}${in}${can}${in}${hlp}");
    }


    function checkallnone($n)
    {
        $in = indent($n);
        $ck = button(constButtonAll);
        $un = button(constButtonNone);
        return para("${in}${ck}${in}${un}");
    }


    function preserve(&$env,$txt)
    {
        $tags = explode(',',$txt);
        if ($tags)
        {
            reset($tags);
            foreach ($tags as $key => $tag)
            {
                echo hidden($tag,$env[$tag]);
            }
        }
    }


    function gang_preserve(&$env,$act)
    {
        $self = $env['self'];
        $jump = $env['jump'];
        $form = $self . $jump;
        echo post_other('myform',$form);

        echo hidden('act',$act);
        echo hidden('debug','1');
        echo hidden('p',$env['page']);
        echo hidden('o',$env['ord']);
        echo hidden('l',$env['limt']);
        $set = 'adv,chg,crt,cyc,def,det,dsp,enb,fmt,gbl,'
             . 'lnk,lst,mal,mod,nxt,out,own,skp,src,pat,txt,tab';
        preserve($env,$set);
    }


    function genb_form(&$env,$db)
    {
        $num = find_report_count($env,$db);
        $set = array( );
        if ($num)
        {
            $sql = gen_query($env,0,$num);
            $set = find_many($sql,$db);
        }

        echo mark('table');
        echo again($env);
        if ($set)
        {
            gang_preserve($env,'genb');

            echo okcancel(5);

            $norm = 128;
            $enbs = enab_options();
            $dont = 'No Change';

            $x_enb = tag_int('x_enb',0,2,0);

            unset($enbs[ 3]);
            unset($enbs[-1]);
            $enbs[0] = $dont;

            $s_enb = tiny_select('x_enb', $enbs, $x_enb, 1, $norm);

            $head = table_header();
            $disp = pretty_header('Edit Options',1);
            $td   = 'td style="font-size: xx-small"';
            $xn   = indent(4);
            echo <<< XXXX

            $head

            $disp

            <tr><td>

              <table border="0" width="100%">
              <tr>
                <$td>State        <br>$s_enb   </td>
              </tr>
              </table>

            </td></tr>
            </table>
            <br clear="all">
            <br>
XXXX;

            echo checkallnone(5);
            $txt = 'Select Reports';
            gang_table($env,$set,1,$txt);
            echo checkallnone(5);

            echo okcancel(5);
            echo form_footer();
        }

        echo again($env);
    }



    function gang_form(&$env,$db)
    {
        $num = find_report_count($env,$db);
        $set = array( );
        if ($num)
        {
            $sql = gen_query($env,0,$num);
            $set = find_many($sql,$db);
        }

        echo mark('table');
        echo again($env);
        if ($set)
        {
            $auth = $env['auth'];
            $href = 'arep-em.htm';
            $open = "window.open('$href','help');";
            gang_preserve($env,'gang');

            echo okcanhlp(5,$open);

            $norm = 128;
            $wide = $norm*2 + 6;

            $enbs = enab_options();
            $dont = 'No Change';

            $defs = array($dont,'No','Yes');
            $mail = array($dont,'Update');
            $outs = array($dont,'Email','Information Portal');
            $frms = form_options();
            $frms[0] = $dont;

            $x_gnc = get_integer('x_gnc',-1);
            $x_gcl = get_integer('x_gcl',-1);

            $x_set = tag_int('x_set',0,1,0);
            $x_add = tag_int('x_add',0,1,0);
            $x_def = tag_int('x_def',0,2,0);
            $x_eml = tag_int('x_eml',0,2,0);
            $x_lnk = tag_int('x_lnk',0,2,0);
            $x_inc = tag_int('x_inc',0,2,0);
            $x_det = tag_int('x_det',0,2,0);
            $x_usr = tag_int('x_usr',0,2,0);
            $x_fil = tag_int('x_fil',0,2,0);
            $x_enb = tag_int('x_enb',0,2,0);
            $x_skp = tag_int('x_skp',0,2,0);
            $x_tab = tag_int('x_tab',0,2,0);
            $x_pri = tag_int('x_pri',0,5,0);
            $x_frm = tag_int('x_frm',0,8,0);
            $x_psv = tag_int('x_psv',0,2,0);

            $x_mal = get_string('x_mal','');
            $x_sub = get_string('x_sub','');
            $x_url = get_string('x_url','');
            $x_xsr = get_string('x_xsr','');
            $x_pas = get_string('x_pas','');
            $x_xfl = get_string('x_xfl','');

            $enbs[0] = $dont;
            unset($enbs[-1]);
            unset($enbs[3]);

            $grps = build_group_list($auth, constQueryNoRestrict, $db);
            $mstr = prep_for_multiple_select($grps);

            $ALL_mgroupid = GRPS_ReturnAllMgroupid($db);
            $sel_include  = saved_search($mstr, $ALL_mgroupid, 7,
                                        'x_g_include[]', constMachineGroupMessage);

            $sel_exclude  = saved_search($mstr, 0, 7,
                                         'x_g_exclude[]', constMachineGroupMessage);

            $s_set = tiny_select('x_set', $mail, $x_set, 1, $norm);
            $s_add = tiny_select('x_add', $mail, $x_add, 1, $norm);
            $s_enb = tiny_select('x_enb', $enbs, $x_enb, 1, $norm);
            $s_def = tiny_select('x_def', $defs, $x_def, 1, $norm);
            $s_eml = tiny_select('x_eml', $defs, $x_eml, 1, $norm);
            $s_usr = tiny_select('x_usr', $defs, $x_usr, 1, $norm);
            $s_fil = tiny_select('x_fil', $outs, $x_fil, 1, $norm);
            $s_det = tiny_select('x_det', $defs, $x_det, 1, $norm);
            $s_lnk = tiny_select('x_lnk', $defs, $x_lnk, 1, $norm);
            $s_inc = tiny_select('x_inc', $defs, $x_inc, 1, $norm);
            $s_frm = tiny_select('x_frm', $frms, $x_frm, 1, $norm);
            $s_psv = tiny_select('x_psv', $defs, $x_psv, 1, $norm);
            $s_skp = tiny_select('x_skp', $defs, $x_skp, 1, $norm);
            $s_tab = tiny_select('x_tab', $defs, $x_tab, 1, $norm);
            $s_mal = tinybox('x_mal',50,$x_mal,$wide);
            $s_sub = tinybox('x_sub',50,$x_sub,$wide);
            $s_url = tinybox('x_url',255,$x_url,$wide);
            $s_xsr = tinybox('x_xsr',255,$x_xsr,$wide);
            $s_pas = tinybox('x_pas',255,$x_pas,$wide);
            $s_xfl = tinybox('x_xfl',255,$x_xfl,$wide);

            $head = table_header();
            $disp = pretty_header('Edit Options',1);
            $td   = 'td style="font-size: xx-small"';
            $ts   = $td . ' colspan="2"';
            $xn   = indent(4);
            $add  = 'Subject Text';
            $r    = 'Recipients';
            $e    = "E-mail $r";
            echo <<< XXXX

            $head

            $disp

            <tr><td>

              <table border="0" width="100%">
              <tr>
                <$td>State         <br>\n$s_enb</td>
                <$td>Output        <br>\n$s_fil</td>
                <$td>Default $r    <br>\n$s_def</td>
                <$td>Links         <br>\n$s_lnk</td>
              </tr>
              <tr>
                <$td>Details       <br>\n$s_det</td>
                <$td>Format        <br>\n$s_frm</td>
                <$td>Skip Owner    <br>\n$s_skp</td>
                <$td>Tabular       <br>\n$s_tab</td>
              </tr>
              <tr>
                <$td>Include Text  <br>\n$s_inc</td>
                <$td>$add (update) <br>\n$s_add</td>
                <$ts>$add (value)  <br>\n$s_sub</td>
              </tr>
              <tr>
                <$td>Include User  <br>\n$s_usr</td>
                <$td>$e (update)   <br>\n$s_set</td>
                <$ts>$e (value)    <br>\n$s_mal</td>
              </tr>
              <tr>
                <$td>Include       <br>\n$sel_include</td>
                <$td>Exclude       <br>\n$sel_exclude</td>
              </tr>
              </table>

            </td></tr>
            </table>
            <br clear="all">
            <br>
XXXX;

            echo checkallnone(5);
            $txt = 'Select Reports';
            gang_table($env,$set,1,$txt);
            echo checkallnone(5);

            echo okcanhlp(5,$open);
            echo form_footer();
        }

        echo again($env);
    }

    function gdel_form(&$env,$db)
    {
        $num = find_report_count($env,$db);
        $set = array( );
        if ($num)
        {
            $sql = gen_query($env,0,$num);
            $set = find_many($sql,$db);
        }

        echo mark('table');
        echo again($env);
        if ($set)
        {
            gang_preserve($env,'gdel');
            echo okcancel(5);
            echo checkallnone(5);

            $txt = 'Delete Asset Reports';
            gang_table($env,$set,1,$txt);

            echo okcancel(5);
            echo checkallnone(5);
            echo form_footer();
        }

        echo again($env);
    }


    function gang_exec(&$env,$db)
    {
        $num = find_report_count($env,$db);
        $set = array( );
        $ids = array( );
        $trm = array( );
        $cnd = array( );
        $ors = array( );
        $arg = array( );
        if ($num)
        {
            $sql = gen_query($env,0,$num);
            $set = find_many($sql,$db);
            $num = 0;
        }

        $initial = 0;
        if ($set)
        {
            $initial = safe_count($set);
            reset($set);
            foreach ($set as $key => $data)
            {
                $rid = $data['id'];
                $tag = "rid_$rid";
                if (get_integer($tag,0))
                {
                    $ids[] = $rid;
                }
            }
            $set = array( );
        }

        echo mark('table');
        echo again($env);

        $now  = $env['now'];
        $auth = $env['auth'];

        gang_preserve($env,'list');
        echo para(button('Continue'));

        if (!$ids)
        {
            echo para('No reports selected ...');
        }
        else
        {
            $x_set = tag_int('x_set',0,1,0);
            $x_add = tag_int('x_add',0,1,0);
            $x_fil = tag_int('x_fil',0,2,0);
            $x_def = tag_int('x_def',0,2,0);
            $x_inc = tag_int('x_inc',0,2,0);
            $x_det = tag_int('x_det',0,2,0);
            $x_eml = tag_int('x_eml',0,2,0);
            $x_lnk = tag_int('x_lnk',0,2,0);
            $x_usr = tag_int('x_usr',0,2,0);
            $x_enb = tag_int('x_enb',0,2,0);
            $x_tab = tag_int('x_tab',0,2,0);
            $x_skp = tag_int('x_skp',0,2,0);
            $x_frm = tag_int('x_frm',0,8,0);
            $x_psv = tag_int('x_psv',0,2,0);

            $x_mal = get_string('x_mal','');
            $x_sub = get_string('x_sub','');
            $x_url = get_string('x_url','');
            $x_xsr = get_string('x_xsr','');
            $x_pas = get_string('x_pas','');
            $x_xfl = get_string('x_xfl','');

            $ginclude_set = get_argument('x_g_include', 0, -1);
            $gexclude_set = get_argument('x_g_exclude', 0, -1);

            $ginclude_str = ($ginclude_set != -1)? join(',', $ginclude_set) : $ginclude_set;
            $gexclude_str = ($gexclude_set != -1)? join(',', $gexclude_set) : $gexclude_set;

            $qu  = safe_addslashes($env['auth']);
            if ($x_set)
            {
                $value = safe_addslashes($x_mal);
                $trm[] = "emaillist = '$value'";
                $ors[] = "emaillist != '$value'";
                $value = ($x_mal == '')? '<br>' : $x_mal;
                $arg[] = array('E-mail Recipients',$value);
            }
            if ($x_add)
            {
                $value = safe_addslashes($x_sub);
                $trm[] = "subject_text = '$value'";
                $ors[] = "subject_text != '$value'";
                $value = ($x_sub == '')? '<br>' : $x_sub;
                $arg[] = array('Subject Text',$value);
            }
            if ($x_url)
            {
                $value = safe_addslashes($x_url);
                $trm[] = "xmlurl = '$value'";
                $ors[] = "xmlurl != '$value'";
                $value = ($x_url == '')? '<br>' : $x_url;
                $arg[] = array('XML URL',$value);
            }
            if ($x_xsr)
            {
                $value = safe_addslashes($x_xsr);
                $trm[] = "xmluser = '$value'";
                $ors[] = "xmluser != '$value'";
                $value = ($x_xsr == '')? '<br>' : $x_xsr;
                $arg[] = array('XML User',$value);
            }
            if ($x_xfl)
            {
                $value = safe_addslashes($x_xfl);
                $trm[] = "xmlfile = '$value'";
                $ors[] = "xmlfile != '$value'";
                $value = ($x_xfl == '')? '<br>' : $x_xfl;
                $arg[] = array('XML Filename',$value);
            }
            if ($x_inc)
            {
                $value = $x_inc - 1;
                $trm[] = "include_text = $value";
                $ors[] = "include_text != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Include Text',$value);
            }
            if ($x_psv)
            {
                $value = $x_psv - 1;
                $trm[] = "xmlpasv = $value";
                $ors[] = "xmlpasv != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Passive Mode',$value);
            }
            if ($ginclude_str != -1)
            {
                $trm[] = "group_include = '$ginclude_str'";
                $ors[] = "group_include != '$ginclude_str'";
                $igrp  = find_mgrp_gid($ginclude_str, constReturnGroupTypeMany, $db);
                $arg[] = array('Include', group_detail($igrp));
            }
            if ($gexclude_str != -1)
            {
                $trm[] = "group_exclude = '$gexclude_str'";
                $ors[] = "group_exclude != '$gexclude_str'";
                $egrp  = find_mgrp_gid($gexclude_str, constReturnGroupTypeMany, $db);
                $arg[] = array('Exclude', group_detail($egrp));
            }
            if ($x_enb)
            {
                $value = $x_enb - 1;
                $trm[] = "enabled = $value";
                $ors[] = "enabled != $value";
                $trm[] = 'next_run = 0';
                if ($value)
                {
                    $trm[] = 'retries = 0';
                }
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Enabled',$value);
            }
            if ($x_usr)
            {
                $value = $x_usr - 1;
                $trm[] = "include_user = $value";
                $ors[] = "include_user != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Include User',$value);
            }
            if ($x_def)
            {
                $value = $x_def - 1;
                $trm[] = "defmail = $value";
                $ors[] = "defmail != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Default Recipients',$value);
            }
            if ($x_frm)
            {
                $value = form_encode($x_frm);
                $trm[] = "format = '$value'";
                $ors[] = "format != '$value'";
                $value = form_decode($value);
                $forms = form_options();
                $arg[] = array('Format',$forms[$value]);
            }
            if ($x_lnk)
            {
                $value = $x_lnk - 1;
                $trm[] = "links = $value";
                $ors[] = "links != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Links',$value);
            }
            if ($x_fil)
            {
                $value = $x_fil - 1;
                $trm[] = "file = $value";
                $ors[] = "file != $value";
                switch($value)
                {
                   case 0 : $value = 'E-mail';
                   case 1 : $value = 'Information Portal';
                   case 2 : $value = 'FTP XML';
                }
                //$value = ($value)? 'Information Portal' : 'E-mail';
                $arg[] = array('Output',$value);
            }
            if ($x_det)
            {
                $value = $x_det - 1;
                $trm[] = "content = $value";
                $ors[] = "content != $value";
                $value = ($value)? 'No' : 'Yes';
                $arg[] = array('Details',$value);
            }
            if ($x_tab)
            {
                $value = $x_tab - 1;
                $trm[] = "tabular = $value";
                $ors[] = "tabular != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Tabular',$value);
            }
            if ($x_skp)
            {
                $value = $x_skp - 1;
                $trm[] = "skip_owner = $value";
                $ors[] = "skip_owner != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Skip owner',$value);
            }
        }

        if (($trm) && ($ids))
        {
            $txt   = join(',',$ids);
            $cnd[] = "username = '$qu'";
            $cnd[] = '0 <= next_run';
            $trm[] = "modified = $now";
            $cnd[] = "id in ($txt)";
            if ($ors)
            {
                $value = join("\n or ",$ors);
                $count = safe_count($ors);
                $cnd[] = ($count > 1)? "($value)" : $value;
            }
            $sets  = join(",\n ",$trm);
            $cond  = join("\n and ",$cnd);
            $sql   = "update AssetReports set\n $sets\n"
                   . " where $cond";
            $res   = redcommand($sql,$db);
            $num   = affected($res,$db);
            debug_note("$num records updated");
        }

        if (($num) && ($ids))
        {
            $txt = join(',',$ids);
            $wrd = order($env['ord']);
            $sql = "select * from AssetReports\n"
                 . " where modified = $now\n"
                 . " and id in ($txt)\n"
                 . " order by $wrd";
            $set = find_many($sql,$db);
        }

        if ($set)
        {
            $txt = 'Updated';
            gang_table($env,$set,0,$txt);
        }

        if (($set) && ($arg))
        {
            $date  = datestring($now);
            $arg[] = array('Available',$initial);
            $arg[] = array('Selected',count($ids));
            $arg[] = array('Updated',count($set));
            $arg[] = array('Modified',$date);

            echo table_header();
            echo pretty_header('Changes',2);
            reset($arg);
            foreach ($arg as $key => $row)
            {
                echo double($row[0],$row[1]);
            }
            echo table_footer();
        }

        if (($ids) && (!$set))
        {
            echo para('No updates were applied.');
        }

        echo button('Continue');
        echo form_footer();
        echo again($env);
    }


    function genb_exec(&$env,$db)
    {
        $num = find_report_count($env,$db);
        $set = array( );
        $ids = array( );
        $trm = array( );
        $cnd = array( );
        $ors = array( );
        $arg = array( );
        if ($num)
        {
            $sql = gen_query($env,0,$num);
            $set = find_many($sql,$db);
            $num = 0;
        }

        $initial = 0;
        if ($set)
        {
            $initial = safe_count($set);
            reset($set);
            foreach ($set as $key => $data)
            {
                $rid = $data['id'];
                $tag = "rid_$rid";
                if (get_integer($tag,0))
                {
                    $ids[] = $rid;
                }
            }
            $set = array( );
        }

        echo mark('table');
        echo again($env);

        $now  = $env['now'];
        gang_preserve($env,'list');

        echo button('Continue');

        if (!$ids)
        {
            echo para('No reports selected ...');
        }
        else
        {
            $x_enb = tag_int('x_enb',0,2,0);
            $qu  = safe_addslashes($env['auth']);
            if ($x_enb)
            {
                $value = $x_enb - 1;
                $trm[] = "enabled = $value";
                $ors[] = "enabled != $value";
                $trm[] = 'next_run = 0';
                if ($value)
                {
                    $trm[] = 'retries = 0';
                }
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Enabled',$value);
            }
        }

        if (($trm) && ($ids))
        {
            $txt   = join(',',$ids);
            $cnd[] = "username = '$qu'";
            $cnd[] = '0 <= next_run';
            $trm[] = "modified = $now";
            $cnd[] = "id in ($txt)";
            if ($ors)
            {
                $value = join("\n or ",$ors);
                $count = safe_count($ors);
                $cnd[] = ($count > 1)? "($value)" : $value;
            }
            $sets = join(",\n ",$trm);
            $cond = join("\n and ",$cnd);
            $sql  = "update AssetReports set\n $sets\n"
                  . " where $cond";
            $res  = redcommand($sql,$db);
            $num  = affected($res,$db);
            debug_note("$num records updated");
        }

        if (($num) && ($ids))
        {
            $txt = join(',',$ids);
            $wrd = order($env['ord']);
            $sql = "select * from AssetReports\n"
                 . " where modified = $now\n"
                 . " and id in ($txt)\n"
                 . " order by $wrd";
            $set = find_many($sql,$db);
        }

        if ($set)
        {
            $txt = 'Updated';
            gang_table($env,$set,0,$txt);
        }

        if (($set) && ($arg))
        {
            $date  = datestring($now);
            $arg[] = array('Available',$initial);
            $arg[] = array('Selected',count($ids));
            $arg[] = array('Updated',count($set));
            $arg[] = array('Modified',$date);

            echo table_header();
            echo pretty_header('Changes',2);
            reset($arg);
            foreach ($arg as $key => $row)
            {
                echo double($row[0],$row[1]);
            }
            echo table_footer();
        }

        if (($ids) && (!$set))
        {
            echo para('No reports were enabled or disabled.');
        }

        echo button('Continue');
        echo form_footer();
        echo again($env);
    }

    function gang_disp(&$env,$db)
    {
        $post = $env['post'];
        $done = ($post == constButtonOk);
        if ($done)
            gang_exec($env,$db);
        else
            gang_form($env,$db);
    }

    function genb_disp(&$env,$db)
    {
        $post = $env['post'];
        $done = ($post == constButtonOk);
        if ($done)
            genb_exec($env,$db);
        else
            genb_form($env,$db);
    }

    function gdel_disp(&$env,$db)
    {
        $post = $env['post'];
        $done = ($post == constButtonOk);
        if ($done)
            gdel_conf($env,$db);
        else
            gdel_form($env,$db);
    }


    function gdel_conf(&$env,$db)
    {
        $num = find_report_count($env,$db);
        $set = array( );
        $ids = array( );
        if ($num)
        {
            $sql = gen_query($env,0,$num);
            $set = find_many($sql,$db);
        }

        echo mark('table');
        echo again($env);
        if ($set)
        {
            reset($set);
            foreach ($set as $key => $data)
            {
                $rid = $data['id'];
                $tag = "rid_$rid";
                if (get_integer($tag,0))
                {
                    $ids[] = $rid;
                }
            }
            $set = array( );
        }
        if ($ids)
        {
            $txt = join(',',$ids);
            $sql = "select * from AssetReports\n"
                 . " where id in ($txt)";
            if (!$env['user']['priv_admin'])
            {
                $qu  = safe_addslashes($env['auth']);
                $sql = "$sql\n and username = '$qu'";
            }
            $set = find_many($sql,$db);
        }

        $next = ($set)? 'gexp' : 'list';
        gang_preserve($env,$next);
        if ($set)
        {
            reset($set);
            foreach ($set as $key => $data)
            {
                $rid = $data['id'];
                $tag = "rid_$rid";
                echo hidden($tag,'1');
            }
            $txt = 'Asset Reports to be Deleted';
            gang_table($env,$set,0,$txt);
            echo para('Delete these reports?');
            echo okcancel(5);
        }
        else
        {
            $cont = button('Continue');
            echo para('You are not allowed to delete any of those reports.');
            echo para($cont);
        }
        echo form_footer();
        echo again($env);
    }


    function gdel_exec(&$env,$db)
    {
        echo mark('table');
        echo again($env);

        $num = find_report_count($env,$db);
        $set = array( );
        $ids = array( );
        if ($num)
        {
            $sql = gen_query($env,0,$num);
            $set = find_many($sql,$db);
            $num = 0;
        }

        if ($set)
        {
            reset($set);
            foreach ($set as $key => $data)
            {
                $rid = $data['id'];
                $tag = "rid_$rid";
                if (get_integer($tag,0))
                {
                    $ids[] = $rid;
                }
            }
            $set = array( );
        }

        $num = 0;
        if ($ids)
        {
            $txt = join(',',$ids);
            $sql = "delete from AssetReports\n"
                 . " where id in ($txt)";
            if (!$env['user']['priv_admin'])
            {
                $qu  = safe_addslashes($env['auth']);
                $sql = "$sql\n and username = '$qu'";
            }
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }

        $cont = button('Continue');

        gang_preserve($env,'list');
        echo para("$num reports deleted ...");
        echo para($cont);
        echo form_footer();
        echo again($env);
    }


    function edit_form(&$env,$db)
    {
        echo again($env);
        $good = false;
        $rid  = $env['rid'];
        $rep  = find_report($rid,$db);
        if ($rep)
        {
            $auth = $env['auth'];
            $user = $rep['username'];
            $good = ($auth == $user);
        }
        if ($good)
        {
            $head = $rep['name'];
            report_form($env,$rep,$head,$db);
        }
        else
        {
            echo para('No access ...');
        }
        echo again($env);
    }


    function report_sanity(&$env,$db)
    {
        echo again($env);
        $sql = "select R.searchid from\n"
             . " AssetReports as R\n"
             . " left join AssetSearches as Q\n"
             . " on Q.id = R.searchid\n"
             . " where Q.id is NULL\n"
             . " group by R.searchid";
        $set = find_many($sql,$db);
        if ($set)
        {
            $num = safe_count($set);
            echo para("There are $num missing queries.");
            reset($set);
            foreach ($set as $key => $row)
            {
                $qid = $row['searchid'];
                $sql = "delete from\n"
                     . " AssetReports\n"
                     . " where searchid = $qid";
                $res = redcommand($sql,$db);
                $num = affected($res,$db);
                debug_note("Query $qid does not exist, $num records removed.");
            }
        }
        else
        {
            echo para('asset.AssetReports.searchid: OK');
        }
        $sql = "select C.assetsearchid from\n"
             . " AssetSearchCriteria as C\n"
             . " left join AssetSearches as Q\n"
             . " on Q.id = C.assetsearchid\n"
             . " where Q.id is NULL\n"
             . " group by C.assetsearchid";
        $set = find_many($sql,$db);
        if ($set)
        {
            $num = safe_count($set);
            echo para("There are $num missing queries.");
            reset($set);
            foreach ($set as $key => $row)
            {
                $qid = $row['assetsearchid'];
                $sql = "delete from\n"
                     . " AssetSearchCriteria\n"
                     . " where assetsearchid = $qid";
                $res = redcommand($sql,$db);
                $num = affected($res,$db);
                debug_note("Query $qid does not exist, $num records removed.");
            }
        }
        else
        {
            echo para('asset.AssetSearchCriteria.assetsearchid: OK');
        }
        $sql = "update AssetReports set\n"
             . " next_run = 0,\n"
             . " this_run = 0\n"
             . " where enabled = 0";
        $res = redcommand($sql,$db);
        $num = affected($res,$db);
        debug_note("$num schedule problems.");
        echo again($env);
    }


    function manage_report(&$env,$db)
    {
        echo again($env);
        $self = $env['self'];
        $jump = $env['jump'];
        $priv = $env['priv'];
        $glob = ($priv)? 3 : 0;
        $cmd  = "$self?act";

        $time = (86400 * 14);
        $comp = "$cmd=list&mal=-1&dsp=1&gbl=$glob&own=0";
        $next = "$comp&nxt=$time&enb=2&o=8$jump";
        $last = "$comp&lst=14&adv=0&o=2$jump";
        $mods = "$comp&mod=30&o=16$jump";
        $back = gang_href($env,'list') . $jump;

        $act = array( );
        $txt = array( );

        $act[] = gang_href($env,'gang');
        $txt[] = 'Edit Multiple Reports';

        $act[] = gang_href($env,'genb');
        $txt[] = 'Enable/Disable Multiple Reports';

        $act[] = gang_href($env,'gdel');
        $txt[] = 'Delete Multiple Reports';

        $act[] = "$cmd=addn";
        $txt[] = 'Create A New Asset Report';

        $act[] = $last;
        $txt[] = 'Asset Reports Run Within the Past Two Weeks';

        $act[] = $next;
        $txt[] = 'Asset Reports Scheduled to Be Run During the Next Two Weeks';

        $act[] = $mods;
        $txt[] = 'Asset Reports Modified Within the Past Month';

        $act[] = $back;
        $txt[] = 'Back to List Asset Reports Page';

        $act[] = $self . $jump;
        $txt[] = 'Asset Reports Default View';

        command_list($act,$txt);
        echo again($env);
    }


    function copy_form(&$env,$db)
    {
        echo again($env);
        $rid  = $env['rid'];
        $rep  = find_report($rid,$db);
        $good = false;
        if ($rep)
        {
            $admn = $env['user']['priv_admin'];
            $auth = $env['auth'];
            $user = $rep['username'];
            $glob = $rep['global'];
            $mine = ($auth == $user);
            $good = (($mine) || ($glob) || ($admn));
        }
        if ($good)
        {
            $gprv = $env['user']['priv_notify'];
            $name = $rep['name'];
            $head = 'Duplicate A Report';
            $name = "Copy of $name";

            $rep['id'] = 0;
            $rep['username'] = $auth;
            $rep['name'] = $name;
            $rep['global'] = ($gprv)? $glob : 0;
            report_form($env,$rep,$head,$db);
        }
        else
        {
            echo para('No access ...');
        }
        echo again($env);
    }

    function over_enab(&$env,$db)
    {
        echo again($env);
        $xid  = 0;
        $now  = $env['now'];
        $rid  = $env['rid'];
        $row  = find_report($rid,$db);
        $locl = array( );
        $good = false;
        if ($row)
        {
            $auth = $env['auth'];
            $name = $row['name'];
            $glob = $row['global'];
            $enab = $row['enabled'];
            $user = $row['username'];
            $locl = find_report_name($name,0,$auth,$db);
            $mine = ($user == $auth);
            $good = (($glob) && (!$mine) && (!$locl) && ($enab != 1));
        }
        if ($locl)
        {
            $lid  = $locl['id'];
            $self = $env['self'];
            $href = "$self?act=view&rid=$lid";
            $name = html_link($href,$name);
            $text = "You already own a report named <b>$name</b>.";
            echo para($text);
        }
        if ($good)
        {
            $row['id']       = 0;
            $row['global']   = 0;
            $row['retries']  = 0;
            $row['enabled']  = 1;
            $row['created']  = $now;
            $row['next_run'] = 0;
            $row['this_run'] = 0;
            $row['last_run'] = 0;
            $row['modified'] = $now;
            $row['username'] = $auth;
            $xid = update_asset($row,$db);
        }
        if (($xid) && ($good))
        {
            $row['id'] = $xid;
            $text = "Enabled Local Report <b>$name</b> has been created.";
            echo para($text);
            report_detail_table($env,$xid,$db);
        }
        else
        {
            echo para('Nothing has changed.');
        }
        echo again($env);
    }


    function over_disb(&$env,$db)
    {
        echo again($env);
        $xid  = 0;
        $now  = $env['now'];
        $rid  = $env['rid'];
        $row  = find_report($rid,$db);
        $locl = array( );
        $good = false;
        if ($row)
        {
            $auth = $env['auth'];
            $name = $row['name'];
            $glob = $row['global'];
            $enab = $row['enabled'];
            $user = $row['username'];
            $locl = find_report_name($name,0,$auth,$db);
            $mine = ($user == $auth);
            $good = (($glob) && (!$mine) && (!$locl) && ($enab == 1));
        }
        if ($locl)
        {
            $lid  = $locl['id'];
            $self = $env['self'];
            $href = "$self?act=view&rid=$lid";
            $name = html_link($href,$name);
            $text = "You already own a report named <b>$name</b>.";
            echo para($text);
        }
        if ($good)
        {
            $row['id']       = 0;
            $row['global']   = 0;
            $row['retries']  = 0;
            $row['enabled']  = 0;
            $row['created']  = $now;
            $row['next_run'] = 0;
            $row['this_run'] = 0;
            $row['last_run'] = 0;
            $row['modified'] = $now;
            $row['username'] = $auth;
            $xid = update_asset($row,$db);
        }
        if (($xid) && ($good))
        {
            $name = $row['name'];
            $text = "Disabled Local Report <b>$name</b> has been created.";
            echo para($text);
            report_detail_table($env,$xid,$db);
        }
        else
        {
            echo para('Nothing has changed.');
        }
        echo again($env);
    }


    function over_form(&$env,$db)
    {
        echo again($env);
        $rid  = $env['rid'];
        $rep  = find_report($rid,$db);
        $good = false;
        if ($rep)
        {
            $auth = $env['auth'];
            $name = $rep['name'];
            $glob = $rep['global'];
            $user = $rep['username'];
            $mine = ($auth == $user);
            $locl = find_report_name($name,0,$auth,$db);
            $good = ($glob) && (!$mine) && (!$locl);
        }
        if ($good)
        {
            $head = 'Create A Local Report';

            $rep['id'] = 0;
            $rep['username'] = $auth;
            $rep['global'] = 0;
            report_form($env,$rep,$head,$db);
        }
        else
        {
            echo para('No access ...');
        }
        echo again($env);
    }


    function enable_act(&$env,$db)
    {
        echo again($env);
        $rid  = $env['rid'];
        $rep  = find_report($rid,$db);
        $num  = 0;
        $good = false;
        if ($rep)
        {
            $auth = $env['auth'];
            $admn = $env['user']['priv_admin'];
            $priv = $env['priv'];
            $name = $rep['name'];
            $user = $rep['username'];
            $mine = ($auth == $user);
            if (($mine) || ($admn))
            {
                $enab = $rep['enabled'];
                $good = ($enab != 1);
            }
        }
        if ($good)
        {
            $now = time();
            $sql = "update AssetReports set\n"
                 . " enabled = 1,\n"
                 . " retries = 0,\n"
                 . " this_run = 0,\n"
                 . " next_run = 0,\n"
                 . " modified = $now\n"
                 . " where id = $rid\n"
                 . " and enabled != 1";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
        if ($num)
        {
            echo para("Asset Report <b>$name</b> has been enabled.");
        }
        else
        {
            echo para('No change ...');
        }
        report_detail_table($env,$rid,$db);
        echo again($env);
    }


    function redo_act(&$env,$db)
    {
        echo again($env);
        $rid  = $env['rid'];
        $rep  = find_report($rid,$db);
        $now  = time();
        $num  = 0;
        $good = false;
        if ($rep)
        {
            $admn = $env['user']['priv_admin'];
            $priv = $env['priv'];
            if (($admn) || ($priv))
            {
                $next = $rep['next_run'];
                $enab = $rep['enabled'];
                $good = (($enab == 1) && ($next > $now));
            }
        }
        if ($good)
        {
            $when = $now - 300;
            $name = $rep['name'];
            $sql = "update AssetReports set\n"
                 . " next_run = $when\n"
                 . " where id = $rid\n"
                 . " and next_run > $now\n"
                 . " and enabled = 1";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
        if ($num)
        {
            $date = nanotime($now);
            echo para("Report <b>$name</b> has been rescheduled to <b>$date</b>.");
        }
        else
        {
            echo para('No change ...');
        }
        report_detail_table($env,$rid,$db);
        echo again($env);
    }


    function skip_act(&$env,$db)
    {
        echo again($env);
        $rid  = $env['rid'];
        $rep  = find_report($rid,$db);
        $now  = time();
        $num  = 0;
        $good = false;
        if ($rep)
        {
            $admn = $env['user']['priv_admin'];
            $priv = $env['priv'];
            if (($admn) || ($priv))
            {
                $next = $rep['next_run'];
                $enab = $rep['enabled'];
                $good = (($enab == 1) && ($next > 0));
            }
        }
        if ($good)
        {
            $next = $rep['next_run'] + 1;
            $when = next_cycle($rep,$next);
            $name = $rep['name'];
            $sql = "update AssetReports set\n"
                 . " next_run = $when\n"
                 . " where id = $rid\n"
                 . " and next_run > 0\n"
                 . " and enabled = 1";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
        if ($num)
        {
            $date = nanotime($when);
            echo para("Report <b>$name</b> has been rescheduled to <b>$date</b>.");
        }
        else
        {
            echo para('No change ...');
        }
        report_detail_table($env,$rid,$db);
        echo again($env);
    }


    function disable_act(&$env,$db)
    {
        echo again($env);
        $rid  = $env['rid'];
        $rep  = find_report($rid,$db);
        $num  = 0;
        $good = false;
        $enab = 0;
        if ($rep)
        {
            $auth = $env['auth'];
            $admn = $env['user']['priv_admin'];
            $user = $rep['username'];
            $mine = ($auth == $user);
            if (($mine) || ($admn))
            {
                $enab = $rep['enabled'];
                $good = ($enab != 0);
            }
        }
        if ($good)
        {
            $now = time();
            $sql = "update AssetReports set\n"
                 . " enabled = 0,\n"
                 . " retries = 0,\n"
                 . " this_run = 0,\n"
                 . " next_run = 0,\n"
                 . " modified = $now\n"
                 . " where id = $rid\n"
                 . " and 0 <= next_run\n"
                 . " and enabled != 0";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
        if ($num)
        {
            $name = $rep['name'];
            echo para("Report <b>$name</b> has been disabled.");
        }
        else
        {
            echo para('No change ...');
        }
        report_detail_table($env,$rid,$db);
        echo again($env);
    }


   /*
    |   creates an immediate version of the specified report.
    |
    |   if the user has sitefiltering enabled:
    |
    |     1. create the new immediate report, disabled.
    |     2. create the new site filter records.
    |     3. enable the new immediate report.
    |   Dec 5, 2005: Sitefiltering doesn't exist for reports anymore.
    |
    |   otherwise, we'll just create the new report
    |   the way it's supposed to be.
    |
    |   if the report does not contain an email address, then
    |   create the report as a file instead.
    */

    function execute_act(&$env,$db)
    {
        echo again($env);
        $good = false;
        $rid  = $env['rid'];
        $xid  = 0;
        $rep  = find_report($rid,$db);
        $auth = $env['auth'];
        $admn = $env['user']['priv_admin'];
        $udef = $env['user']['report_mail'];
        $file = false;
        if ($rep)
        {
            $name = $rep['name'];
            $glob = $rep['global'];
            $user = $rep['username'];
            $file_type = $rep['file'];
            $mine = ($auth == $user);
            if (($mine) || ($glob) || ($admn))
            {
                $good = true;
            }
        }
        if ($good)
        {
            $now  = time();
            $date = datestring($now);
            $bias = server_int('cron_bias',120,$db);
            $name = $rep['name'] . ' (tmp)';
            $mail = $rep['emaillist'];
            $type = $rep['cycle'];
            $defm = $rep['defmail'];
            if ($type <= 3)
            {
                $umax = $now - $bias;
                $rep['umax'] = $umax;
                $rep['umin'] = cyclic($type,$umax);
            }
            debug_note("create immediate report: $date");
            $rep['id']       = 0;
            $rep['name']     = $name;
            $rep['cycle']    = ReportTypeImmediate;
            $rep['global']   = 0;       // local
            $rep['enabled']  = 1;
            $rep['created']  = $now;    // now
            $rep['retries']  = 0;       // first time
            $rep['last_run'] = 0;       // first time
            $rep['this_run'] = 0;       // first time
            $rep['next_run'] = $now;
            $rep['modified'] = 0;
            $rep['username'] = $auth;

            //this is commented out because it was causing immediate reports to
            //be a file (file = 1) even when it was an xml file
            //if (!$mail)
            // {
               //if (($defm) && (!$udef))
               // {
               //     $rep['file'] = 1;
               // }
            // }
            //if ($rep['file'])
            // {
            //     $file = true;
            // }

            $xid = update_asset($rep,$db);
        }
        $good = ($xid)? true : false;
        if (($good) && ($xid))
        {
            if ($file_type == constReportXML)
            {
                $xmlurl = $rep['xmlurl'];
                $msg  = "<br>The report <b>$name</b> is being run ";
                $msg .= " and will be sent to <b>$xmlurl</b> via ftp shortly.<br><br>";
            }
            if ($file_type == constReportASI)
            {
                $msg  = "<br>The report <b>$name</b> has no email.<br>\n";
                $msg .= "It will be published to the information portal shortly.<br><br>";
            }
            if ($file_type == constReportMAIL)
            {
                $msg  = "<br>The report <b>$name</b> is being run ";
                $msg .= "and will be emailed shortly.<br><br>";
            }
        }
        else
        {
            $msg  = "<br>The report can not be run at this time.<br>";
            $msg .= "Please try again later.<br><br>";
        }
        echo $msg;
        if (($xid) && ($good))
        {
            report_detail_table($env,$xid,$db);
        }
        echo again($env);
    }



    function queue_manage(&$env,$db)
    {
        echo mark('table');
        echo again($env);
        $now = $env['now'];
        $sql = "select * from AssetReports\n"
             . " where enabled = 1\n"
             . " order by next_run, global, cycle, id\n"
             . " limit 30";
        $set = find_many($sql,$db);
        if ($set)
        {
            $self = $env['self'];
            $head = explode('|','Wait|Name|Owner|Id|Schedule|Next|Last|Action');
            $cols = safe_count($head);
            $text = datestring($now);

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $rid  = $row['id'];
                $name = $row['name'];
                $user = $row['username'];
                $glob = $row['global'];
                $last = $row['last_run'];
                $next = $row['next_run'];
                $that = $row['this_run'];
                $wait = '(none)';

                if ($next > 0)
                {
                    if ($next > $now)
                    {
                        $secs = $next - $now;
                        $wait = age($secs);
                    }
                    else
                    {
                        $secs = $now - $next;
                        $late = age($secs);
                        $wait = page_bold($late);
                    }
                }
                else if ($next < 0)
                {
                    if ($that > 0)
                    {
                        $name = page_bold($name);
                        $wait = '(run)';
                    }
                }

                $tlst = nanotime($last);
                $tnxt = nanotime($next);
                $scop = ($glob)? 'g' : 'l';
                $ownr = "$user($scop)";

                $cmd  = "$self?rid=$rid&act";
                $ax   = array( );
                $ax[] = html_link("$cmd=frst",'first');
                $ax[] = html_link("$cmd=last",'now');
                $ax[] = html_link("$cmd=post",'post');
                $link = html_link("$cmd=view",$name);
                $acts = join(' ',$ax);
                $shed = shed($env,$row);
                $args = array($wait,$link,$ownr,$rid,$shed,$tnxt,$tlst,$acts);
                echo table_data($args,0);
            }
            echo table_footer();
        }
        echo again($env);
    }


    function command_list(&$act,&$txt)
    {
        echo para('What do you want to do?');
        echo "\n\n<ol>\n";
        reset($txt);
        foreach ($txt as $key => $doc)
        {
            $cmd = html_link($act[$key],$doc);
            echo "<li>$cmd</li>\n";
        }
        echo "</ol>\n";
    }


    function debug_menu(&$env,$db)
    {
        echo again($env);
        $self = $env['self'];
        $cmd  = "$self?act";
        $dbg  = "$self?debug=1&act";
        $day  = 86400;
        $nxt  = $day * 7;
        $cmp  = "$self?dsp=1&mal=-1";

        $act = array( );
        $txt = array( );

        $act[] = "$cmp&gbl=3&nxt=$nxt&o=8";
        $txt[] = 'Compact View -- Next Run';

        $act[] = "$cmp&gbl=3&o=2";
        $txt[] = 'Compact View -- Last Run';

        $act[] = "$self?dsp=0";
        $txt[] = 'Expanded View';

        $act[] = "$cmd=menu";
        $txt[] = 'Debug Menu';

        $act[] = "$cmd=stat";
        $txt[] = 'Statistics';

        $act[] = "$dbg=rset";
        $txt[] = 'Reset Asset Report Queue Only';

        $act[] = "$dbg=queu";
        $txt[] = 'Asset Report Queue';

        $act[] = 'census.php';
        $txt[] = 'Asset Census';

        $act[] = '../event/notify.php?act=queu';
        $txt[] = 'Notify Queue';

        $act[] = '../event/report.php?act=queu';
        $txt[] = 'Event Report Queue';

        $act[] = "$dbg=sane";
        $txt[] = 'Database Consistancy Check';

        $act[] = "$dbg=lock";
        $txt[] = 'Claim Lock';

        $act[] = "$dbg=pick";
        $txt[] = 'Release Lock';

        $act[] = '../acct/index.php';
        $txt[] = 'Debug Home';

        command_list($act,$txt);
        echo again($env);
    }



    function showtime($now,$then)
    {
        if ($then <  0) return 'running';
        if ($then == 0) return 'never';
        if ($then <= $now)
        {
            $when = nanotime($then);
            $age  = age($now - $then);
            $text = "$when (age $age)";
        }
        else
        {
            $when = nanotime($then);
            $wait = age($then - $now);
            $text = "$when (wait $wait)";
        }
        return $text;
    }


    function report_detail(&$env,$db)
    {
        echo again($env);
        $rid  = $env['rid'];
        report_detail_table($env,$rid,$db);
        echo again($env);
    }


    function global_report_exists($name,$rid,$db)
    {
        $qn  = safe_addslashes($name);
        $sql = "select * from AssetReports\n"
             . " where id != $rid\n"
             . " and name = '$qn'\n"
             . " and global = 1";
        $set = find_many($sql,$db);
        return ($set)? true : false;
    }


    function owned_report_exists($name,$auth,$rid,$db)
    {
        $qn  = safe_addslashes($name);
        $qa  = safe_addslashes($auth);
        $sql = "select * from AssetReports\n"
             . " where id != $rid\n"
             . " and name = '$qn'\n"
             . " and username = '$qa'";
        $set = find_one($sql,$db);
        return ($set)? true : false;
    }

    function find_report_name($name,$glob,$user,$db)
    {
        $row = array();
        if ($name)
        {
            $qn  = safe_addslashes($name);
            $sql = "select * from AssetReports\n"
                 . " where name = '$qn'\n"
                 . " and global = $glob";
            if ($user)
            {
                $qu  = safe_addslashes($user);
                $sql = "$sql\n and username = '$qu'";
            }
            $row = find_one($sql,$db);
        }
        return $row;
    }


    function find_report($id,$db)
    {
        $row = array( );
        if ($id > 0)
        {
            $sql = "select * from AssetReports where id = $id";
            $row = find_one($sql,$db);
        }
        return $row;
    }

    function queue_post(&$env,$db)
    {
        $new = array( );
        $rid = $env['rid'];
        $old = find_report($rid,$db);
        $msg = 'No change';
        if ($old)
        {
            $nxt = $old['next_run']-1;
            $enb = $old['enabled'];
            if (($enb) && ($nxt > 28))
            {
                $sql = "select * from AssetReports\n"
                     . " where enabled = 1\n"
                     . " and next_run > $nxt\n"
                     . " and id != $rid\n"
                     . " order by next_run\n"
                     . " limit 1";
                $new = find_one($sql,$db);
            }
        }
        if ($new)
        {
            $nxt = $new['next_run'] + 1;
            $sql = "update AssetReports set\n"
                 . " next_run = $nxt\n"
                 . " where id = $rid\n"
                 . " and next_run > 0\n"
                 . " and next_run < $nxt\n"
                 . " and enabled = 1";
            $res = redcommand($sql,$db);
            if (affected($res,$db))
            {
                $name = $old['name'];
                $otim = $old['next_run'];
                $secs = age($nxt - $otim);
                $otxt = nanotime($otim);
                $ntxt = nanotime($nxt);
                $msg  = "Report <b>$name</b> postponed by <b>$secs</b>,"
                      . " from <b>$otxt</b> to <b>$ntxt</b>.";
            }
        }
        echo para($msg);
        queue_manage($env,$db);
    }


    function queue_last(&$env,$db)
    {
        $new = array( );
        $rid = $env['rid'];
        $now = $env['now'];
        $old = find_report($rid,$db);
        $msg = 'No change';
        if ($old)
        {
            $ntim = $now - 1;
            $otim = $old['next_run'];
            $sql  = "update AssetReports set\n"
                  . " next_run = $ntim\n"
                  . " where id = $rid\n"
                  . " and next_run > 0\n"
                  . " and this_run = 0\n"
                  . " and enabled = 1";
            $res  = redcommand($sql,$db);
            if (affected($res,$db))
            {
                $name = $old['name'];
                $otim = $old['next_run'];
                if ($otim < $ntim)
                {
                    $secs = age($ntim - $otim);
                    $what = 'postponed';
                }
                else
                {
                    $secs = age($otim - $ntim);
                    $what = 'advanced';
                }
                $otxt = nanotime($otim);
                $ntxt = nanotime($ntim);
                $msg  = "Report <b>$name</b> $what by <b>$secs</b>,"
                      . " from <b>$otxt</b> to <b>$ntxt</b>.";
            }
        }
        echo para($msg);
        queue_manage($env,$db);
    }


    function queue_first(&$env,$db)
    {
        $new = array( );
        $rid = $env['rid'];
        $old = find_report($rid,$db);
        $msg = 'No change';
        if ($old)
        {
            $enb = $old['enabled'];
            $nxt = $old['next_run'];
            if (($enb) && ($nxt > 28))
            {
                // note this should be ordered exactly
                // the same way that c-report chooses
                // the next report to process.

                $sql = "select * from AssetReports\n"
                     . " where enabled = 1\n"
                     . " and next_run > 86400\n"
                     . " and next_run <= $nxt\n"
                     . " and id != $rid\n"
                     . " order by next_run, global, cycle, id\n"
                     . " limit 1";
                $new = find_one($sql,$db);
            }
        }
        if ($new)
        {
            $nxt = $new['next_run'] - 1;
            $sql = "update AssetReports set\n"
                 . " next_run = $nxt\n"
                 . " where id = $rid\n"
                 . " and next_run > $nxt\n"
                 . " and enabled = 1";
            $res = redcommand($sql,$db);
            if (affected($res,$db))
            {
                $name = $old['name'];
                $otim = $old['next_run'];
                $secs = age($otim - $nxt);
                $otxt = nanotime($otim);
                $ntxt = nanotime($nxt);
                $msg  = "Report <b>$name</b> advanced by <b>$secs</b>,"
                      . " from <b>$otxt</b> to <b>$ntxt</b>.";
            }
        }
        echo para($msg);
        queue_manage($env,$db);
    }



    function tiny_data($args,$tiny)
    {
        $m  = '';
        $td = ($tiny)? 'td style="font-size: x-small"' : 'td';
        if ($args)
        {
            $m .= "<tr>\n";
            reset($args);
            foreach ($args as $key => $data)
            {
                $m .= "<$td>$data</td>\n";
                $td = 'td';
            }
            $m .= "</tr>\n";
        }
        return $m;
    }


    function report_table(&$env,$set,$total,$db)
    {
        $ord  = $env['ord'];
        $lim  = $env['limt'];
        $self = $env['self'];
        $priv = $env['priv'];
        $auth = $env['auth'];
        $jump = $env['jump'];
        $dsp  = $env['dsp'];

        $args = array("$self?act=list&l=$lim");
        query_state($env,$args);
        $o = join('&',$args) . "&o";

        $name = ($ord ==  0)? "$o=1"  : "$o=0";     // name   0, 1
        $last = ($ord ==  2)? "$o=3"  : "$o=2";     // last   2, 3
        $enab = ($ord ==  4)? "$o=5"  : "$o=4";     // enab   4, 5
        $glob = ($ord ==  6)? "$o=7"  : "$o=6";     // glob   6, 7
        $next = ($ord ==  8)? "$o=9"  : "$o=8";     // next   8, 9
        $file = ($ord == 12)? "$o=13" : "$o=12";    // mail  12, 13
        $ctim = ($ord == 14)? "$o=15" : "$o=14";    // ctim  14, 15
        $mtim = ($ord == 16)? "$o=17" : "$o=16";    // mtim  16, 17
        $dets = ($ord == 18)? "$o=19" : "$o=18";    // dets  18, 19
        $user = ($ord == 20)? "$o=21" : "$o=20";    // user  20, 21
        $rpid = ($ord == 22)? "$o=23" : "$o=22";    // rpid  22, 23
        $when = ($ord == 24)? "$o=25" : "$o=24";    // when  24, 25
        $chng = ($ord == 26)? "$o=27" : "$o=26";    // chng  26, 27
        $qury = ($ord == 28)? "$o=29" : "$o=28";    // qury  28, 29
        $frmt = ($ord == 30)? "$o=31" : "$o=30";    // frmt  30, 31
        $defm = ($ord == 32)? "$o=33" : "$o=32";    // defm  32, 33
        $lnks = ($ord == 34)? "$o=35" : "$o=34";    // lnks  34, 35
        $tabs = ($ord == 36)? "$o=37" : "$o=36";    // tabs  36, 37
        $ginc = ($ord == 38)? "$o=39" : "$o=38";    // group_include
        $gexc = ($ord == 40)? "$o=41" : "$o=40";    // group_exclude

        $acts = 'Action';
        $rpid = html_jump($rpid,$jump,'Id');
        $name = html_jump($name,$jump,'Name');
        $when = html_jump($when,$jump,'When');
        $glob = html_jump($glob,$jump,'Scope');
        $user = html_jump($user,$jump,'Owner');
        $enab = html_jump($enab,$jump,'State');
        $qury = html_jump($qury,$jump,'Query');
        $lnks = html_jump($lnks,$jump,'Links');
        $outs = html_jump($file,$jump,'Output');
        $ctim = html_jump($ctim,$jump,'Create');
        $frmt = html_jump($frmt,$jump,'Format');
        $mtim = html_jump($mtim,$jump,'Modify');
        $tabs = html_jump($tabs,$jump,'Tabular');
        $dets = html_jump($dets,$jump,'Details');
        $next = html_jump($next,$jump,'Next Run');
        $last = html_jump($last,$jump,'Last Run');
        $chng = html_jump($chng,$jump,'Report Type');
        $mail = html_jump($file,$jump,'E-mail Recipients');
        $defm = html_jump($defm,$jump,'Default Recipients');
        $ginc = html_jump($ginc,$jump,'Include');
        $gexc = html_jump($gexc,$jump,'Exclude');

        $args = array();
        show($env,$args,'d_act',$acts);
        show($env,$args,'d_nam',$name);
        show($env,$args,'d_qry',$qury);
        show($env,$args,'d_own',$user);
        show($env,$args,'d_cyc',$when);
        show($env,$args,'d_out',$outs);
        show($env,$args,'d_fmt',$frmt);
        show($env,$args,'d_crt',$ctim);
        show($env,$args,'d_mod',$mtim);
        show($env,$args,'d_nxt',$next);
        show($env,$args,'d_lst',$last);
        show($env,$args,'d_gbl',$glob);
        show($env,$args,'d_rid',$rpid);
        show($env,$args,'d_def',$defm);
        show($env,$args,'d_mal',$mail);
        show($env,$args,'d_enb',$enab);
        show($env,$args,'d_det',$dets);
        show($env,$args,'d_lnk',$lnks);
        show($env,$args,'d_chg',$chng);
        show($env,$args,'d_tab',$tabs);
        show($env,$args,'d_gnc',$ginc);
        show($env,$args,'d_gxc',$gexc);

        if (($set) && ($args))
        {
            $defs = $env['user']['report_mail'];
            $admn = $env['user']['priv_admin'];
            $defs = ($defs)? "<i>$defs</i>" : '';
            $cols = safe_count($args);
            $frms = form_options();
            $text = 'Asset Reports';
            $tiny = ($dsp)? 0 : 1;
            $acts = '<br>';
            $act  = 'query.php?act=view&qid';
            if (($total > $lim) || ($total > 40))
            {
                $text = "$text &nbsp; ($total found)";
            }

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($args,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $rid  = $row['id'];
                $file = $row['file'];
                $lnks = $row['links'];
                $qury = $row['query'];
                $glob = $row['global'];
                $frmt = $row['format'];
                $defm = $row['defmail'];
                $enab = $row['enabled'];
                $cont = $row['content'];
                $tabs = $row['tabular'];
                $qid  = $row['searchid'];
                $chng = $row['change_rpt'];
                $skip = $row['skip_owner'];

                $ginc = $row['group_include'];
                $gexc = $row['group_exclude'];

                $name = disp($row,'name');
                $user = disp($row,'username');

                $code = form_decode($frmt);
                $frmt = $frms[$code];

                $mine = ($user == $auth)? 1 : 0;
                $list = '';
                if (!$file)
                {
                    $list = $row['emaillist'];
                    if (($glob) || ($mine))
                    {
                        if (($defm) && ($defs))
                        {
                            $list = ($list)? "$defs,$list" : $defs;
                        }
                    }
                }
                $cmd  = "$self?rid=$rid&act";
                $list = ($list)? str_replace(',','<br>',$list) : '<br>';
                $glbl = ($glob)? 'Global' : 'Local';
                $tabs = ($tabs)? 'Yes' : 'No';
                $dets = ($cont)? 'Yes' : 'No';
                $defm = ($defm)? 'Yes' : 'No';
                $lnks = ($lnks)? 'Yes' : 'No';
                $chng = ($chng)? 'change report' : 'status report';
                $outs = ($file)? 'information portal' : 'e-mail';

                $ginc = find_mgrp_gid($ginc, constReturnGroupTypeMany, $db);
                $gexc = find_mgrp_gid($gexc, constReturnGroupTypeMany, $db);

                $ginc = GRPS_edit_group_detail($ginc);
                $gexc = GRPS_edit_group_detail($gexc);

                $ginc = ($ginc)? ($ginc) : 'No';
                $gexc = ($gexc)? ($gexc) : 'No';

                $ctim = nanotime($row['created']);
                $mtim = nanotime($row['modified']);
                $next = nanotime($row['next_run']);
                $last = nanotime($row['last_run']);
                if ($tiny)
                {
                    $ax = array( );
                    if (($glob) || ($mine) || ($admn))
                    {
                        $ax[] = html_link("$cmd=exec&debug=1",'[run]');
                        $ax[] = html_link("$cmd=copy",'[copy]');
                    }
                    if ($mine)
                    {
                        $ax[] = html_link("$cmd=edit",'[edit]');
                        $ax[] = html_link("$cmd=cdel",'[delete]');
                        if ($enab == 1)
                            $ax[] = html_link("$cmd=disb",'[disable]');
                        else
                            $ax[] = html_link("$cmd=enab",'[enable]');
                    }
                    if (($glob) && (!$mine))
                    {
                        $ax[] = html_link("$cmd=over",'[edit]');
                        if ($enab == 1)
                            $ax[] = html_link("$cmd=dovr",'[disable]');
                        else
                            $ax[] = html_link("$cmd=eovr",'[enable]');
                    }
                    if (!$ax)
                    {
                        $ax[] = html_link("$cmd=view",'[details]');
                    }
                    $acts = join("<br>\n",$ax);
                }

               /*
                |  I'd prefer to let the user decide for himself
                |  if he wants a new window or not ... I find
                |  websites that open lots of windows to be
                |  annoying ... but I was overruled.
                */

                $qury = html_page("$act=$qid",$qury);
                $name = html_page("$cmd=view",$name);
                $when = shed($env,$row);
                $enab = enabled($enab);

                $args = array( );
                show($env,$args,'d_act',$acts);
                show($env,$args,'d_nam',$name);
                show($env,$args,'d_qry',$qury);
                show($env,$args,'d_own',$user);
                show($env,$args,'d_cyc',$when);
                show($env,$args,'d_out',$outs);
                show($env,$args,'d_fmt',$frmt);
                show($env,$args,'d_crt',$ctim);
                show($env,$args,'d_mod',$mtim);
                show($env,$args,'d_nxt',$next);
                show($env,$args,'d_lst',$last);
                show($env,$args,'d_gbl',$glbl);
                show($env,$args,'d_rid',$rid);
                show($env,$args,'d_def',$defm);
                show($env,$args,'d_mal',$list);
                show($env,$args,'d_enb',$enab);
                show($env,$args,'d_det',$dets);
                show($env,$args,'d_lnk',$lnks);
                show($env,$args,'d_chg',$chng);
                show($env,$args,'d_tab',$tabs);
                show($env,$args,'d_gnc',$ginc);
                show($env,$args,'d_gxc',$gexc);

                echo tiny_data($args,$tiny);
            }

            echo table_footer();

            echo prevnext($env,$total);
        }
        else
        {
            echo para('There were no matching reports ...');
        }
    }

    function nanotime($when)
    {
        $text = '<br>';
        if ($when > 0)
        {
            $that = date('m/d/y',time());
            $date = date('m/d/y',$when);
            $time = date('H:i:s',$when);
            $midn = ($time == '00:00:00');
            $tday = ($date == $that);
            if ($midn)
            {
                $text = $date;
            }
            else if ($tday)
            {
                $text = $time;
            }
            else
            {
                $text = "$date $time";
            }
        }
        if ($when < 0)
        {
            $text = "running";
        }
        return $text;
    }

    function tag_int($name,$min,$max,$def)
    {
        $valu = get_integer($name,$def);
        return value_range($min,$max,$valu);
    }


   /*
    |  Main program
    */

    $now  = time();
    $db   = db_connect();
    $auth = process_login($db);
    $comp = component_installed();
    $act  = get_string('act','list');
    $post = get_string('button','');
    if ($post == constButtonCan)
    {
        $act = 'list';
    }

    $name = title($act);
    $msg  = ob_get_contents();
    ob_end_clean();
    echo standard_html_header($name,$comp,$auth,0,0,'',$db);
    $user  = user_data($auth,$db);
    $priv  = @ ($user['priv_debug'])?  1 : 0;
    $admn  = @ ($user['priv_admin'])?  1 : 0;
    $dbg   = get_integer('debug',0);
    $debug = ($priv)? $dbg : 0;

    if (trim($msg)) debug_note($msg);

    debug_array($debug,$_POST);

    $day = 86400;
    $nxt = (14 * $day);
    $dsp = tag_int('dsp', 0,1, 0);      // display, expanded
    $adv = tag_int('adv', 0,1, 1);      // avanced ui
    $mal = tag_int('mal',-1,0, 0);      // recipients, any
    $chg = tag_int('chg',-1,2, 0);      // change, displayed
    $def = tag_int('def',-1,2,-1);      // defmail, not displayed
    $det = tag_int('det',-1,2,-1);      // details, not displayed
    $lnk = tag_int('lnk',-1,2,-1);      // links, not displayed
    $out = tag_int('out',-1,3,-1);      // output, not displayed
    $skp = tag_int('skp',-1,2,-1);      // skip owner, no display
    $tab = tag_int('tab',-1,2,-1);      // tab, not displayed
    $enb = tag_int('enb',-1,3,-1);      // enabled, not displayed
    $gbl = tag_int('gbl',-1,3,-1);      // global, not displayed
    $cyc = tag_int('cyc',-1,5, 0);      // cycle, any
    $fmt = tag_int('fmt',-2,8,-1);      // format, no display
    $lst = tag_int('lst',-2,9999, 0);   // last run, display
    $crt = tag_int('crt',-2,9999,-1);   // create, no display
    $mod = tag_int('mod',-2,9999,-1);   // modified, no display
    $nxt = tag_int('nxt',-1,$nxt,-1);   // next run, no display
    $ord = tag_int('o',0,41,0);
    $pag = tag_int('p',0,9999,0);
    $lim = tag_int('l',5,5000,20);
    $gnc = tag_int('gnc',-2,9999,-1);  // group_include
    $gxc = tag_int('gxc',-2,9999,-1);  // group_exclude

    $txt = get_string('txt','');
    $pat = get_string('pat','');
    $src = get_string('src','');

    $rid = get_integer('rid', 0);
    $qry = get_integer('qry', 0);
    $own = get_integer('own',-1);
    db_change($GLOBALS['PREFIX'].'asset',$db);

    $s_g_include = get_integer('s_g_include', -2);
    $s_g_exclude = get_integer('s_g_exclude', -2);
    if ($s_g_include > $gnc)
    {
        $gnc = $s_g_include;
    }
    if ($s_g_exclude > $gxc)
    {
        $gxc = $s_g_exclude;
    }

    if (!$admn)
    {
        $gbl = value_range(-1,2,$gbl);
        $own = value_range(-1,0,$own);
    }

    if ($post == constButtonMore)
    {
        $adv = 1;
    }
    if ($post == constButtonLess)
    {
        $adv = 0;
    }

    if ($post == constButtonRst)
    {
        $dsp = 0;  $out = -1;  $src = '';  $gxc = -1;
        $mal = 0;  $def = -1;  $fmt = -1;  $txt = '';
        $chg = 0;  $det = -1;  $crt = -1;  $pat = '';
        $cyc = 0;  $enb = -1;  $mod = -1;  $lim = 20;
        $lst = 0;  $gbl = -1;  $nxt = -1;  $pag =  0;
        $rid = 0;  $lnk = -1;  $tab = -1;  $adv =  1;
        $ord = 0;  $own = -1;  $skp = -1;  $gnc = -1;
    }

    $env = array( );
    $env['pid'] = getmypid();
    $env['now'] = $now;
    $env['act'] = $act;
    $env['ord'] = $ord;
    $env['adv'] = $adv;

    $env['href'] = 'page_href';
    $env['midn'] = midnight($now);
    $env['page'] = $pag;
    $env['limt'] = $lim;
    $env['self'] = server_var('PHP_SELF');
    $env['args'] = server_var('QUERY_STRING');
    $env['jump'] = '#table';
    $env['post'] = $post;
    $env['priv'] = $priv;
    $env['dbug'] = $debug;
    $env['auth'] = $auth;
    $env['user'] = $user;
    $env['cycl'] = $cyclenames;
    $env['days'] = $daynames;
    $env['cron'] = 0;

    $env['rid'] = $rid;   // AssetReports.id
    $env['out'] = $out;   // AssetReports.file
    $env['cyc'] = $cyc;   // AssetReports.cycle
    $env['lnk'] = $lnk;   // AssetReports.links
    $env['fmt'] = $fmt;   // AssetReports.format
    $env['gbl'] = $gbl;   // AssetReports.global
    $env['crt'] = $crt;   // AssetReports.created
    $env['det'] = $det;   // AssetReports.content
    $env['def'] = $def;   // AssetReports.defmail
    $env['enb'] = $enb;   // AssetReports.enabled
    $env['tab'] = $tab;   // AssetReports.tabular
    $env['own'] = $own;   // AssetReports.username
    $env['mod'] = $mod;   // AssetReports.modified
    $env['lst'] = $lst;   // AssetReports.last_run
    $env['nxt'] = $nxt;   // AssetReports.next_run
    $env['qry'] = $qry;   // AssetReports.searchid
    $env['mal'] = $mal;   // AssetReports.emaillist
    $env['skp'] = $skp;   // AssetReports.skip_owner
    $env['chg'] = $chg;   // AssetReports.change_rpt
    $env['dsp'] = $dsp;   // compact / expanded
    $env['pat'] = $pat;   // R.name like
    $env['src'] = $src;   // Q.name like
    $env['txt'] = $txt;   // R.emaillist like
    $env['gnc'] = $gnc;   // group_include
    $env['gxc'] = $gxc;   // group_exclude

    $env['d_nam'] = (   true  );
    $env['d_act'] = (0 == $dsp);
    $env['d_qry'] = (0 == $qry);
    $env['d_det'] = (0 == $det);
    $env['d_chg'] = (0 == $chg);
    $env['d_enb'] = (0 == $enb);
    $env['d_fmt'] = (0 == $fmt);
    $env['d_out'] = (0 == $out);
    $env['d_def'] = (0 == $def);
    $env['d_lnk'] = (0 == $lnk);
    $env['d_tab'] = (0 == $tab);
    $env['d_crt'] = (0 <= $crt);
    $env['d_cyc'] = (0 <= $cyc);
    $env['d_lst'] = (0 <= $lst);
    $env['d_mal'] = (0 <= $mal);
    $env['d_mod'] = (0 <= $mod);
    $env['d_nxt'] = (0 <= $nxt);
    $env['d_gnc'] = (0 <= $gnc);
    $env['d_gxc'] = (0 <= $gxc);
    $env['d_rid'] = (3 == $gbl);
    $env['d_own'] = ((0 <= $own) || (3 == $gbl));
    $env['d_gbl'] = ((0 == $gbl) || (3 == $gbl));

    if (!$priv)
    {
        $txt = '|||menu|stat|rset|lock|pick|queu|post|frst|last|sane|';
        if (matchOld($act,$txt))
        {
            $act = 'list';
        }
    }

    check_queue($env,$db);
    switch ($act)
    {
        case 'list': list_report($env,$db);           break;
        case 'menu': debug_menu($env,$db);            break;
        case 'lock': claim_lock($env,$db);            break;
        case 'pick': pick_lock($env,$db);             break;
        case 'stat': statistics($env,$db);            break;
        case 'addn': add_form($env,$db);              break;
        case 'insn': add_exec($env,$db);              break;
        case 'rset': queue_reset($env,$db);           break;
        case 'exec': execute_act($env,$db);           break;
        case 'disb': disable_act($env,$db);           break;
        case 'enab': enable_act($env,$db);            break;
        case 'skip': skip_act($env,$db);              break;
        case 'redo': redo_act($env,$db);              break;
        case 'updt': update_act($env,$db);            break;
        case 'over': over_form($env,$db);             break;
        case 'dovr': over_disb($env,$db);             break;
        case 'eovr': over_enab($env,$db);             break;
        case 'copy': copy_form($env,$db);             break;
        case 'edit': edit_form($env,$db);             break;
        case 'gang': gang_disp($env,$db);             break;
        case 'genb': genb_disp($env,$db);             break;
        case 'gdel': gdel_disp($env,$db);             break;
        case 'gexp': gdel_exec($env,$db);             break;
        case 'mnge': manage_report($env,$db);         break;
        case 'sane': report_sanity($env,$db);         break;
        case 'cdel': delete_conf($env,$db);           break;
        case 'rdel': delete_act($env,$db);            break;
        case 'view': report_detail($env,$db);         break;
        case 'last': queue_last($env,$db);            break;
        case 'frst': queue_first($env,$db);           break;
        case 'post': queue_post($env,$db);            break;
        case 'queu': queue_manage($env,$db);          break;
        default    : list_report($env,$db);           break;
    }
    echo head_standard_html_footer($auth,$db);
?>
