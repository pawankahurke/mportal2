<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 2-Aug-02   EWB     Always set enabled to 1 when creating new report.
 8-Oct-02   EWB     Back to events only version
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navagation
12-Dec-02   EWB     Fixed short open tags
 8-Jan-03   EWB     Minimal Quotes
 9-Jan-03   EWB     Reworked argument passing.
10-Feb-03   EWB     Uses sandbox libraries.
11-Feb-03   EWB     db_change()
13-Feb-03   EWB     Removed find_mail.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header().
14-Apr-03   NL      Comment out "if (trim($msg)) debug_note($msg)" cuz no $debug.
15-Apr-03   EWB     factored out the jumptable.
24-Apr-03   EWB     echo jumptable.
25-Apr-03   NL      Remove font tags; use CSS to control instructional text
28-Apr-03   NL      Oops.  Remove l-sitefl.php (not needed and wrong name)
29-Apr-03   EWB     Access cleanup.
29-May-03   EWB     Show report owner to debug user.
18-Jun-03   EWB     Slave Database.
20-Jun-03   EWB     No Slave Database.
17-Jul-03   EWB     File Output.
23-Jul-03   EWB     Implement Links
13-Aug-03   EWB     Wording change for output selection.
21-Aug-03   EWB     Immediate Run command.
11-Sep-03   EWB     Allow create report when user has no sites.
24-Sep-03   EWB     Specify HTML vs MHTML (rfc2557).
 1-Oct-03   EWB     Support for fake event field.
14-Nov-03   NL      Implement asset links option.
15-Nov-03   NL      By default, set "Include Asset Links" to Yes.
15-Nov-03   NL      Bug: html_select use: if use keys, pass key, not value as 3rd param.
16-Nov-03   NL      Change labels to sentence case.
16-Nov-03   NL      By default, set "Include Links" to Yes.
25-Nov-03   NL      Separate assetlinks & eventlinks controls: change labels.
 9-Apr-04   EWB     Added link to report debug page.
15-Oct-04   BJS     Added user/subject checkbox, subject textbox
29-Nov-04   EWB     Paging for report list.
30-Nov-04   EWB     Column Sorting for report list.
 2-Dec-04   EWB     New report interface.
 6-Dec-04   EWB     Edit Multiple Reports
 7-Dec-04   EWB     fixed bug updating report format.
10-Dec-04   EWB     gang delete.
13-Dec-04   BJS     Added skip owner option.
14-Dec-04   EWB     Moved much into l-tiny.php
16-Dec-04   EWB     Create queue_push
17-Dec-04   EWB     Gang Enable/Disable
 6-Jan-05   EWB     select by recipient contains
 6-Jan-05   EWB     display / sort / select by file / format / links
 7-Jan-05   EWB     display / sort by defmail
11-Jan-05   EWB     select by defmail
18-Jan-05   AAM     Wording changes as per Alex.
21-Jan-05   EWB     Help / Reset
25-Jan-05   EWB     gang delete allows deleting reports you do not own.
28-Jan-05   EWB     "Help" on gang edit form
 4-Feb-05   EWB     Select by owner still displays column
 7-Feb-05   EWB     New help pages
 1-Mar-05   EWB     Run immediate copies report, not user, filtersites.
 7-Mar-05   BJS     Aggregate Report ability.
 7-Mar-05   EWB     Database Consistancy Check
 9-Mar-05   BJS     Fixed some aggregate problems.
22-Mar-05   EWB     Control Queue Display Size
31-Mar-05   BJS     Added aggregate count to statistics. do not change
                    this/next_run of a running report.
24-Apr-05   EWB     queue_last can schedule inactive report.
20-May-05   EWB     report details shows default email.
21-May-05   EWB     resequence pending reports.
 6-Jun-05   BJS     Omit Zero Event reports option.
 7-Jun-05   BJS     Omit Zero Event status added to report details.
19-Jul-05   BJS     Added detaillinks, event report w/detail section only containing links.
21-Jul-05   BJS     Added omit and detaillinks to search & edit.
21-Oct-05   BJS     saved_search() moved into l-form.php.
03-Nov-05   BJS     Added l-evnt.php, ability to add groups to event reports,
                    edit multiple groups and search on groups.
07-Nov-05   BJS     Group include/exclude works when running reports, removed suspend.
09-Nov-05   BJS     Removed references to sitefilters & RptSiteFilters.
18-Nov-05   BJS     Added constQueryNoRestrict to build_group_list().
30-Nov-05   BJS     'All' group not hardwired to 1, instead call GRPS_ReturnAllMgroupid().
07-Dec-05   BJS     Added type to GRPS_exclude_instructions().
07-Aug-06   BTE     Bug 3579: Aggregate Reports select box is incorrect.
19-Sep-06   WOH     Bug 3657: Changed name html_footer.  Added username arg.

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
include_once ( '../lib/l-gbox.php'  );
include_once ( '../lib/l-gsql.php'  );
include_once ( '../lib/l-uprp.php'  );
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
include_once ( '../lib/l-evnt.php'  );


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

    function title($act)
    {
        $e = 'Event';
        $r = 'Report';
        $q = 'Queue';
        $m = 'Multiple';
        switch ($act)
        {
            case 'copy': return "Copy an $e $r";
            case 'edit': return "Edit an $e $r";
            case 'over': return 'Create a Local Report';
            case 'addn': return "Add an $e $r";
            case 'adda': return 'Add an Aggragate Report';
            case 'insn': return "$e $r Added";
            case 'stat': return "$r Statistics";
            case 'enab': return "Enable $e $r";
            case 'disb': return "Disable $e $r";
            case 'menu': return "$r Debug Menu";
            case 'cdel': return "Confirm Delete";
            case 'view': return "$e $r Details";
            case 'lock': return "Lock $r $q";
            case 'pick': return "Unlock $r $q";
            case 'dovr': return 'Disable Local Report';
            case 'eovr': return 'Enable Local Report';
            case 'genb': return "Enable/Disable $m $e ${r}s";
            case 'gang': return "Edit $m $e ${r}s";
            case 'mnge': return "Manage $e ${r}s";
            case 'sane': return "Database Consistancy Check";
            case 'gdel': ;
            case 'gexp': return "Delete $m $e ${r}s";
            case 'exec': return "Run An $e $r";
            case 'rseq': ;
            case 'push': ;
            case 'time': ;
            case 'frst': ;
            case 'post': ;
            case 'queu': return "$e $r $q";
            default    : return "Event ${r}s";
        }
    }


    function again(&$env)
    {
        $self = $env['self'];
        $jump = $env['jump'];
        $dbg = $env['priv'];
        $act = $env['act'];
        $cmd = "$self?act";
        $a   = array( );
        $a[] = html_link('#top','top');
        $a[] = html_link('#bottom','bottom');
        $a[] = html_link("$cmd=addn",'add');
        $a[] = html_link("$cmd=adda",'add aggregate');
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
        if (matchOld($act,'|||queu|post|frst|last|push|'))
        {
            $a[] = html_link('#table','table');
        }
        if ($dbg)
        {
            $args = $env['args'];
            $redo = ($args)? "$self?$args" : $self;
            $time = (86400 * 14);
            $comp = "$cmd=list&mal=-1&dsp=1&gbl=3&adv=0";
            $next = "$comp&nxt=$time&o=8$jump";
            $last = "$comp&o=2$jump";
            $a[] = html_link("$cmd=queu",'queue');
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
        $agg = $env['agg'];
        $crt = $env['crt'];
        $cyc = $env['cyc'];
        $def = $env['def'];
        $det = $env['det'];
        $dsp = $env['dsp'];
        $enb = $env['enb'];
        $flt = $env['flt'];
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
        $skp = $env['skp'];
        $txt = $env['txt'];
        $omt = $env['omt'];
        $dlk = $env['dlk'];
        $gnc = $env['gnc'];
        $gxc = $env['gxc'];
        $dbg = $env['dbug'];
        $prv = $env['priv'];

        if ($adv != 1) $set[] = "adv=$adv";
        if ($cyc != 0) $set[] = "cyc=$cyc";
        if ($det != 0) $set[] = "det=$det";
        if ($dsp != 0) $set[] = "dsp=$dsp";
        if ($enb != 0) $set[] = "enb=$enb";
        if ($flt != 0) $set[] = "flt=$flt";
        if ($lst != 0) $set[] = "lst=$lst";
        if ($mal != 0) $set[] = "mal=$mal";

        if ($agg != -1) $set[] = "agg=$agg";
        if ($crt != -1) $set[] = "crt=$crt";
        if ($def != -1) $set[] = "def=$def";
        if ($fmt != -1) $set[] = "fmt=$fmt";
        if ($gbl != -1) $set[] = "gbl=$gbl";
        if ($lnk != -1) $set[] = "lnk=$lnk";
        if ($mod != -1) $set[] = "mod=$mod";
        if ($nxt != -1) $set[] = "nxt=$nxt";
        if ($out != -1) $set[] = "out=$out";
        if ($own != -1) $set[] = "own=$own";
        if ($skp != -1) $set[] = "skp=$skp";
        if ($omt != -1) $set[] = "omt=$omt";
        if ($dlk != -1) $set[] = "dlk=$dlk";
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
        if (($prv) && ($dbg)) $set[] = "debug=1";
    }


    function green($msg)
    {
        return "<font color=\"green\">$msg</font>";
    }

    function dgreen($a,$b)
    {
        $aa = green($a);
        $bb = green($b);
        return double($aa,$bb);
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
    |  want to see, including today, except that we use
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

    function quote($txt)
    {
        if ($txt != '')
        {
            $txd = str_replace('"','""',$txt);
        }
        return '"' . $txt . '"';
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
           10 => "Site Filter ($a)",
           11 => "Site Filter ($d)",
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
           26 => "Output ($a)",
           27 => "Output ($d)",
           28 => "Format ($a)",
           29 => "Format ($d)",
           30 => "Links ($a)",
           31 => "Links ($d)",
           32 => "Default Recipients ($a)",
           33 => "Default Recipients ($d)",
           34 => "Skip Owner ($a)",
           35 => "Skip Owner ($d)",
           36 => "Aggregate ($a)",
           37 => "Aggregate ($d)",
           38 => "Omit Zero ($d)",
           39 => "Omit Zero ($a)",
           40 => "Links Only ($d)",
           41 => "Links Only ($a)",
           42 => "Include ($a)",
           43 => "Include ($d)",
           44 => "Exclude ($a)",
           45 => "Exclude ($d)"
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
            case 18: return 'details, id desc';
            case 19: return 'details desc, id';
            case 20: return 'username, name, id';
            case 21: return 'username desc, name desc, id desc';
            case 22: return 'id';
            case 23: return 'id desc';
            case 24: return 'hour, minute, id';
            case 25: return 'hour desc, minute desc';
            case 26: return 'file desc, name, id';
            case 27: return 'file, name desc, id';
            case 28: return 'format, username, name, id';
            case 29: return 'format desc, username desc, name desc, id';
            case 30: return 'links, assetlinks, username, name, id';
            case 31: return 'links desc, assetlinks desc, username desc, name desc, id';
            case 32: return 'defmail, name desc, id';
            case 33: return 'defmail desc, name, id';
            case 34: return 'skip_owner desc, name, id';
            case 35: return 'skip_owner, name desc, id';
            case 36: return 'aggregate desc, name, id';
            case 37: return 'aggregate, name desc, id';
            case 38: return 'omit desc, name, id';
            case 39: return 'omit, name desc, id';
            case 40: return 'detaillinks desc, name, id';
            case 41: return 'detaillinks, name desc, id';
            case 42: return 'group_include desc, name, id desc';
            case 43: return 'group_include, name';
            case 44: return 'group_exclude desc, name, id desc';
            case 45: return 'group_exclude, name';
            default: return order(0);
        }
    }

   /*
    |  We want to use the same procedure to generate sql for both
    |  the counting and the selection of records.
    */

    function gen_query(&$env,$count,$num)
    {
        $auth = $env['auth'];
        $agg = $env['agg'];
        $cyc = $env['cyc'];
        $def = $env['def'];
        $det = $env['det'];
        $enb = $env['enb'];
        $flt = $env['flt'];
        $fmt = $env['fmt'];
        $gbl = $env['gbl'];
        $lnk = $env['lnk'];
        $nxt = $env['nxt'];
        $out = $env['out'];
        $own = $env['own'];
        $pat = $env['pat'];
        $skp = $env['skp'];
        $txt = $env['txt'];
        $omt = $env['omt'];
        $dlk = $env['dlk'];
        $gnc = $env['gnc'];
        $gxc = $env['gxc'];
        $qu  = safe_addslashes($auth);
        if ($count)
        {
            $sel = "select count(R.id) from";
        }
        else
        {
            $sel = "select R.* from";
        }
        $lft = array( );
        $ons = array( );
        $trm = array( );
        $tab = array
        (
            'Reports as R'
        );

        if ($agg > 0)
        {
            $value = $agg - 1;
            $trm[] = "R.aggregate = $value";
        }
        if ($skp > 0)
        {
            $value = $skp - 1;
            $trm[] = "R.skip_owner = $value";
        }
        if ($omt > 0)
        {
            $value = $omt - 1;
            $trm[] = "R.omit = $value";
        }
        if ($dlk > 0)
        {
            $value = $dlk - 1;
            $trm[] = "R.detaillinks = $value";
        }
        if ($gnc > 0)
        {
            $trm[] = "R.group_include regexp '(^|,)$gnc(,|$)'";
        }
        if ($gxc > 0)
        {
            $trm[] = "R.group_exclude regexp '(^|,)$gxc(,|$)'";
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
            $trm[] = "R.details = $value";
        }
        if ($out > 0)
        {
            $value = $out - 1;
            $trm[] = "R.file = $value";
        }
        if ($fmt > 0)
        {
            $value = form_encode($fmt);
            $value = safe_addslashes($value);
            $trm[] = "R.format = '$value'";
        }
        if ($nxt > 0)
        {
            $value = time() + $nxt;
            $trm[] = "R.next_run < $value";
            $trm[] = "R.next_run > 0";
        }
        if ($flt > 0)
        {
            $value = safe_addslashes("%,$flt,%");
            $trm[] = "R.search_list like '$value'";
        }

        $e = 'R.links';
        $a = 'R.assetlinks';
        switch ($lnk)      // lnks_options()
        {
            case  1: $trm[] = "$e = 0"; $trm[] = "$a = 0"; break;
            case  2: $trm[] = "$e = 0"; $trm[] = "$a = 1"; break;
            case  3: $trm[] = "$e = 1"; $trm[] = "$a = 0"; break;
            case  4: $trm[] = "$e = 1"; $trm[] = "$a = 1"; break;
            default: break;
        }

        if ($pat != '')
        {
            $value = str_replace('%','\%',$pat);
            $value = str_replace('_','\_',$value);
            $value = safe_addslashes($value);
            $trm[] = "R.name like '%$value%'";
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
            $tab[] = $GLOBALS['PREFIX'].'core.Users as U';
            $trm[] = 'U.username = R.username';
            $trm[] = "U.userid = $own";
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
            $lft[] = 'Reports as X';
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
        if (($gbl == 2) && ($own <= 0))
        {
            $lft[] = 'Reports as X';
            $ons[] = 'X.name = R.name';
            $ons[] = 'X.global != R.global';
            $ons[] = "X.username = '$qu'";
            $trm[] = 'R.global = 1';
            $trm[] = 'X.id is NULL';
        }
        if (($gbl == 2) && ($own > 0))
        {
            $trm[] = 'R.global = 1';
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
        $tabs = join(",\n ",$tab);
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


    function find_aggregate_count(&$env,$db)
    {
        $num1 = 0;
        $num2 = 0;
        $qu   = quote($env['user']['username']);
        $sql  = 'select count(*) from  '.$GLOBALS['PREFIX'].'event.ReportGroups';
        $res  = redcommand($sql,$db);
        if ($res)
        {
            $num1 = mysqli_result($res, 0);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
        $sql = "select count(R.id), R.name from\n"
             . "  ".$GLOBALS['PREFIX']."event.Reports as R\n"
             . " left join  ".$GLOBALS['PREFIX']."event.Reports as X\n"
             . " on X.name = R.name\n"
             . " and X.global = 0\n"
             . " and X.username = '$qu'\n"
             . " where (R.username = '$qu'\n"
             . " or (R.global = 0) and (X.id is NULL))\n"
             . " and R.aggregate = 0\n"
             . " group by R.id order by name";
        $res = redcommand($sql,$db);
        if ($res)
        {
            $num2 = mysqli_result($res, 0);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
        debug_note("There are $num1 aggregate records. <br>");
        debug_note("You have access to $num2 records.  <br>");
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
            $lock = find_opt('report_lock',$db);
            $lpid = find_opt('report_pid',$db);
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


    function form_options()
    {
        return array
        (
            0 => 'None',
            1 => 'Plain Text',
            2 => 'HTML w/o Charts',
            3 => 'HTML w/ Pie Charts',
            4 => 'MHTML w/ Pie Charts',
            5 => 'HTML w/ Bar Charts',
            6 => 'MHTML w/ Bar Charts',
            7 => 'HTML w/ Column Charts',
            8 => 'MHTML w/ Column Charts'
        );
    }


    function form_decode($txt)
    {
        switch ($txt)
        {
            case 'text'  : return 1;
            case 'html'  : return 2;
            case 'pie'   : return 3;
            case 'mpie'  : return 4;
            case 'bar'   : return 5;
            case 'mbar'  : return 6;
            case 'column': return 7;
            case 'mcol'  : return 8;
            default      : return 2;
        }
    }


    function form_encode($frm)
    {
        switch ($frm)
        {
            case  1: return 'text';
            case  2: return 'html';
            case  3: return 'pie';
            case  4: return 'mpie';
            case  5: return 'bar';
            case  6: return 'mbar';
            case  7: return 'column';
            case  8: return 'mcol';
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


    function sits_options()
    {
        return array
        (
            -1 => constTagNone,
             0 => constTagAny,
             1 => 'Not Filtered',
             2 => 'Filtered'
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
              2 => 'information portal'
        );
    }


    function lnks_options()
    {
        return array
        (
             -1 => constTagNone,
              0 => constTagAny,
              1 => 'None',
              2 => 'Event',
              3 => 'Asset',
              4 => 'Both'
        );
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

    function link_name(&$row)
    {
        $elnk = ($row['links'])?      1 : 0;
        $alnk = ($row['assetlinks'])? 1 : 0;
        $code = 2*$alnk + $elnk;
        switch ($code)
        {
            case 0: return 'None';
            case 1: return 'Event';
            case 2: return 'Asset';
            case 3: return 'Both';
        }
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

    function filt_options($auth,$db)
    {
        $out = array(indent(10));
        $set = find_searches($auth,$db);
        reset($set);
        foreach ($set as $sid => $name)
        {
             $out[$sid] = $name;
        }
        return $out;
    }


    function find_members($rid,$db)
    {
        $sql = "select R.* from ReportGroups as G,\n"
             . " Reports as R\n"
             . " where G.owner = $rid\n"
             . " and R.id = G.member\n"
             . " and R.aggregate = 0\n"
             . " group by R.id\n"
             . " order by name";
        return find_many($sql,$db);
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
        echo hidden('hlp','list');
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
        $glbs = glbl_options();
        $opts = past_options($midn,$days);
        $owns = owns_options($env,$db);
        $enbs = enab_options();
        $disp = disp_options();
        $secs = secs_options();
        $cycs = cycs_options();
        $dets = dets_options();
        $outs = outs_options();
        $fmts = fmts_options();
        $lnks = lnks_options();
        $defs = defs_options();
        $omts = defs_options();
        $dlks = defs_options();
        $tiny = 50;
        $norm = 128;
        $wide = $norm*3 + 6;
        $yn = array('No','Yes');

        if (!$priv)
        {
            unset($glbs[3]);
        }

        $sel_include = GRPS_create_select_box($auth, constGroupIncludeTempTable,
                                              's_g_include', $env['gnc'],
                                              constEventReports, $db);

        $sel_exclude = GRPS_create_select_box($auth, constGroupExcludeTempTable,
                                              's_g_exclude', $env['gxc'],
                                              constEventReports, $db);

        $s_dbg = tiny_select('debug', $yn, $env['dbug'],1, $tiny);
        $s_lim = tiny_select('l',   $lims, $env['limt'],0, $tiny);
        $s_ord = tiny_select('o',   $ords, $env['ord'], 1, $norm);
        $s_lst = tiny_select('lst', $opts, $env['lst'], 1, $norm);
        $s_gbl = tiny_select('gbl', $glbs, $env['gbl'], 1, $norm);
        $s_enb = tiny_select('enb', $enbs, $env['enb'], 1, $norm);
        $s_mal = tiny_select('mal', $disp, $env['mal'], 1, $norm);
        $s_nxt = tiny_select('nxt', $secs, $env['nxt'], 1, $norm);
        $s_dsp = tiny_select('dsp', $dsps, $env['dsp'], 1, $norm);
        $s_cyc = tiny_select('cyc', $cycs, $env['cyc'], 1, $norm);
        $s_det = tiny_select('det', $dets, $env['det'], 1, $norm);

        $s_agg = tiny_select('agg', $defs, $env['agg'], 1, $norm);
        $s_fmt = tiny_select('fmt', $fmts, $env['fmt'], 1, $norm);
        $s_own = tiny_select('own', $owns, $env['own'], 1, $norm);

        if ($adv)
        {
            $defs = defs_options();
            $flts = filt_options($auth,$db);
            $s_crt = tiny_select('crt', $opts, $env['crt'], 1, $norm);
            $s_flt = tiny_select('flt', $flts, $env['flt'], 1, $wide);
            $s_lnk = tiny_select('lnk', $lnks, $env['lnk'], 1, $norm);
            $s_mod = tiny_select('mod', $opts, $env['mod'], 1, $norm);
            $s_def = tiny_select('def', $defs, $env['def'], 1, $norm);
            $s_skp = tiny_select('skp', $defs, $env['skp'], 1, $norm);
            $s_out = tiny_select('out', $outs, $env['out'], 1, $norm);
            $s_omt = tiny_select('omt', $omts, $env['omt'], 1, $norm);
            $s_dlk = tiny_select('dlk', $dlks, $env['dlk'], 1, $norm);
        }

        $s_pat = tinybox('pat', 40, $env['pat'], $norm);
        $s_txt = tinybox('txt', 40, $env['txt'], $norm);

        $href = 'ereport.htm';
        $open = "window.open('$href','help');";
        $help = click(constButtonHlp,$open);

        $tag  = ($adv)? constButtonLess : constButtonMore;
        $sub  = button(constButtonSub);
        $rset = button(constButtonRst);
        $tag  = button($tag);
        $head = table_header();
        $srch = pretty_header('Search Options',1);
        $disp = pretty_header('Display Options',1);
        $td   = 'td style="font-size: xx-small"';
        $ts   = $td . ' colspan="3"';
        $xn   = indent(4);
        if ($priv)
        {
            $dbg = green('Debug');
            $dbg = "<$td>$dbg<br>\n$s_dbg</td>\n";
        }
        else
        {
            $dbg   = '';
            $s_dbg = '';
        }

        $rep = 'Recipient';
        $advanced = '';
        if ($adv)
        {
            $advanced = <<< ADVANCED

              <tr>
                <$td>Default ${rep}s<br>\n$s_def</td>
                <$td>Skip Owner     <br>\n$s_skp</td>
                <$td>Links          <br>\n$s_lnk</td>
                <$td>Links Only     <br>\n$s_dlk</td>
              </tr>
              <tr>
                <$td>Created        <br>\n$s_crt</td>
                <$td>Modified       <br>\n$s_mod</td>
                <$td>Output         <br>\n$s_out</td>
              </tr>
              <tr>
                <$td>Include        <br>\n$sel_include</td>
                <$td>Exclude        <br>\n$sel_exclude</td>
                <$td>Omit Zero      <br>\n$s_omt</td>
              </tr>
              <tr>
                <$ts>Event Filter   <br>\n$s_flt</td>
              </tr>
ADVANCED;
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
                <$td>Report Cycle   <br>\n$s_cyc</td>
                <$td>Format         <br>\n$s_fmt</td>
              </tr>
              <tr>
                <$td>$rep Contains  <br>\n$s_txt</td>
                <$td>E-mail ${rep}s <br>\n$s_mal</td>
                <$td>Last Run       <br>\n$s_lst</td>
                <$td>Next Run       <br>\n$s_nxt</td>
              </tr>
              <tr>
                <$td>Details        <br>\n$s_det</td>
                <$td>Scope          <br>\n$s_gbl</td>
                <$td>Owner          <br>\n$s_own</td>
                <$td>Aggregate      <br>\n$s_agg</td>
              </tr> $advanced
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
                <$td>Display    <br>\n$s_dsp  </td>

                $dbg

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
            $admn = $env['user']['priv_admin'];
            $user = $rep['username'];
            $mine = ($user == $auth);
            if (($mine) || ($admn))
            {
                $self = $env['self'];
                $name = $rep['name'];
                $href = "$self?act=rdel&rid=$rid";
                $yes  = html_link($href,'[Yes]');
                $no   = html_link($self,'[No]');
                $in   = indent(5);

                echo <<< HERE

                <br>
                <p>Do you really want to delete <b>$name</b>?</p>
                <p>${yes}${in}${no}</p>
                <br>

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
            $admn = $env['user']['priv_admin'];
            $sql = "delete from Reports\n"
                 . " where id = $rid";
            if (!$admn)
            {
                $qu  = safe_addslashes($auth);
                $sql = "$sql\n and username = '$qu'";
            }
            $res = redcommand($sql,$db);
            $num = affected($res,$db);

            if ($num > 0)
            {
                $name = $rep['name'];
                debug_note("$num reports removed");

                $sql = "delete from ReportGroups\n"
                     . " where member = $rid\n"
                     . " or owner = $rid";
                $res = redcommand($sql,$db);

                echo para("Report <b>$name</b> has been deleted.");
            }
        }
        echo again($env);
    }



    function list_report(&$env,$db)
    {
        $p = 'p style ="font-size:8pt"';
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
        $lock = server_int('report_lock',0,$db);
        $lpid = server_int('report_pid',0,$db);
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
                if (update_opt('report_lock','1',$db))
                {
                    $sql = "update ".$GLOBALS['PREFIX']."core.Options set\n"
                         . " value = $pid,\n"
                         . " modified = $when\n"
                         . " where name = 'report_pid'";
                    redcommand($sql,$db);
                    $xxx = nanotime($when);
                    $txt = "report: fake lock by process $pid at $xxx";
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
        $lock = find_opt('report_lock',$db);
        $lpid = find_opt('report_pid',$db);
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
            opt_update('report_pid',0,0,$db);
            opt_update('report_lock',0,0,$db);
        }
        echo again($env);
    }


    function queue_reset(&$env,$db)
    {
        $now = time();
        $day = 86400;
        $big = $now + (366 * $day);
        $xxx = $now - 300;
        $tab = 'Reports';
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
        $num  = 'select count(*) from Reports';
        $find = "$num\n where";
        $enab = "$find enabled = 1";
        $disb = "$find enabled = 0";
        $invl = "$find enabled = 2";
        $glob = "$find global = 1";
        $locl = "$find global = 0";
        $pend = "$find enabled = 1 and next_run < $now";
        $runn = "$find enabled = 1 and next_run < 0";

        $next = "select * from Reports\n"
              . " where enabled = 1\n"
              . " order by next_run, global, cycle, id\n"
              . " limit 1";
        $row = find_one($next,$db);

        echo table_header();
        echo pretty_header('Report Statistics',2);
        echo double('Total:',   find_scalar($num,$db));
        echo double('Enabled:', find_scalar($enab,$db));
        echo double('Disabled:',find_scalar($disb,$db));
        echo double('Invalid:', find_scalar($invl,$db));
        echo double('Global:',  find_scalar($glob,$db));
        echo double('Local:',   find_scalar($locl,$db));
        echo double('Pending:', find_scalar($pend,$db));
        echo double('Running:', find_scalar($runn,$db));
        find_aggregate_count($env,$db);
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
        $cfg = ':entered:machine:text1:';
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
            'assetlinks'   => 1,
            'last_run'     => 0,
            'next_run'     => 0,
            'this_run'     => 0,
            'order1'       => '',
            'order2'       => '',
            'order3'       => '',
            'order4'       => '',
            'details'      => 1,
            'umin'         => 0,
            'umax'         => 0,
            'created'      => 0,
            'modified'     => 0,
            'retries'      => 0,
            'config'       => $cfg,
            'search_list'  => '',
            'include_user' => 0,
            'include_text' => 0,
            'subject_text' => '',
            'skip_owner'   => 0,
            'aggregate'    => 0,
            'omit'         => 0,
            'detaillinks'  => 0,
            'group_include'=> '',
            'group_exclude'=> ''
        );
    }

    function report_ints()
    {
        return array('global','defmail','file','cycle',
                     'hour','minute','wday','mday',
                     'enabled','links','assetlinks',
                     'details','include_user',
                     'include_text','skip_owner',
                     'aggregate', 'omit', 'detaillinks');
    }


    function find_sids(&$set)
    {
        $out = array( );
        reset($set);
        foreach ($set as $key => $row)
        {
             $sid = $row['id'];
             $out[$sid] = $row['name'];
        }
        return $out;
    }


    function find_searches($auth,$db)
    {
        $qu  = safe_addslashes($auth);
        $sql = "select S.id, S.name from\n"
             . " event.SavedSearches as S\n"
             . " left join event.SavedSearches as X\n"
             . " on X.name = S.name\n"
             . " and X.global = 0\n"
             . " and X.username = '$qu'\n"
             . " where S.username = '$qu'\n"
             . " or (S.global = 1 and (X.id is NULL))\n"
             . " order by name, id";
        $set = find_many($sql,$db);
        return find_sids($set);
    }


    function all_searches($db)
    {
        $sql = "select id, name from\n"
             . " event.SavedSearches\n"
             . " order by name, id";
        $set = find_many($sql,$db);
        return find_sids($set);
    }


    function saved_report($env,$reportlist,$db)
    {
        $qu   = safe_addslashes($env['auth']);
        $sql  = "select R.id, R.name from\n"
              . "  ".$GLOBALS['PREFIX']."event.Reports as R\n"
              . " left join  ".$GLOBALS['PREFIX']."event.Reports as X\n"
              . " on X.name = R.name\n"
              . " and X.global = 0\n"
              . " and X.aggregate = 0\n"
              . " and X.username = '$qu'\n"
              . " where (((R.username = '$qu')\n"
              . " and (R.global = 0))\n"
              . " or ((R.global = 1)\n"
              . " and (X.id is NULL)))\n"
              . " and R.aggregate = 0\n"
              . " group by R.id"
              . " order by name,id";
         $set  = find_many($sql,$db);

         $rpt  = "<select name=\"report_id[]\" multiple size=\"7\">\n";
         $o    = 'option';
         $s    = '';

         $selected = array();
         reset($reportlist);
         foreach ($reportlist as $reportkey => $reportvalue)
         {
             $id = $reportvalue['id'];
             $selected[$id] = true;
         }
         reset($set);
         foreach ($set as $k => $v)
         {
             $id    = $v['id'];
             $name  = $v['name'];
             $s     = (isset($selected[$id]))? 'selected ' : '';
             $rpt  .= "<$o ${s}value=\"$id\">$name</$o>\n";
         }
         $rpt .= "</select>";
         return $rpt;
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

    function content_section(&$env, $row)
    {
        $valid1 = $env['val1'];
        $valid2 = $env['val2'];
        $enames = $env['enam'];

        $o1 = trim($row['order1']);
        $o2 = trim($row['order2']);
        $o3 = trim($row['order3']);
        $o4 = trim($row['order4']);
        $ln = intval($row['links']);
        $al = intval($row['assetlinks']);
        $dl = intval($row['detaillinks']);
        $de = $row['details'];
        $en = $row['enabled'];

        if ($o1 == '') $o1 = $valid1[0];
        if ($o2 == '') $o2 = $valid2[0];
        if ($o3 == '') $o3 = $valid2[0];
        if ($o4 == '') $o4 = $valid2[0];

        $v1 = array_lookup($valid1,$enames);
        $v2 = array_lookup($valid2,$enames);

        $yn = array ('No','Yes');
        $s_o1 = html_select('order1',  $v1,     $enames[$o1],0);
        $s_o2 = html_select('order2',  $v2,     $enames[$o2],0);
        $s_o3 = html_select('order3',  $enames, $enames[$o3],0);
        $s_o4 = html_select('order4',  $enames, $enames[$o4],0);

        // these select boxes use keys, so pass a key, not value, as 3rd param
        $s_link = html_select('links',      $yn, $ln,1);
        $s_dets = html_select('details',    $yn, $de,1);
        $s_enab = html_select('enabled',    $yn, $en,1);
        $s_alnk = html_select('assetlinks', $yn, $al,1);
        $s_dlnk = html_select('detaillinks',$yn, $dl,1);

        return <<< HERE

        <table cellpadding="2" cellspacing="0" border="0">
        <tr>
           <th colspan="3">
             Content
           </th>
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
            Include event links:
          </td>
          <td colspan="2">
            $s_link
          </td>
        </tr>
        <tr>
          <td nowrap>
            Include asset links:
          </td>
          <td colspan="2">
            $s_alnk
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
       <tr>
          <td nowrap>
            Include details:
          </td>
          <td>
            $s_dets
          </td>
          <td>
            <span class="footnote">select fields below</span><br>
          </td>
       </tr>
       <tr>
          <td nowrap>
            Links only:
          </td>
          <td>
            $s_dlnk
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
        $umin = $row['umin'];
        $umax = $row['umax'];
        $mday = $row['mday'];
        $wday = $row['wday'];
        $type = $row['cycle'];

        $dmin = ($umin)? date('m/d/y H:i:s',$umin) : '';
        $dmax = ($umax)? date('m/d/y H:i:s',$umax) : '';

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
        $s_dmin = textmax('dmin',19,19,$dmin);
        $s_dmax = textmax('dmax',19,19,$dmax);

        return <<< HERE

        <table cellpadding="2" cellspacing="0" border="0">
        <tr>
            <th colspan="2" valign="top">
                Schedule
            </th>
        </tr>
        <tr>
            <td>
                Report cycle:
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
        <tr>
            <td>
                Start date:
            </td>
            <td>
                $s_dmin
            </td>
            <td class="footnote">
                date is mm/dd or mm/dd/yy or mm/dd hh:mm.<br>
                only needed for immediate reports.
            </td>
        </tr>
        <tr>
            <td>
                End date:
            </td>
            <td>
                $s_dmax
            </td>
            <td class="footnote">
                date is mm/dd or mm/dd/yy or mm/dd hh:mm.<br>
                only needed for immediate reports.
            </td>
        </tr>
        </table>

HERE;

    }

   /*
    |   Returns the original array, except that
    |   the empty elements have been filtered out.
    */

    function filter($p)
    {
        $list = array( );
        $n = safe_count($p);
        for ($i = 0; $i < $n; $i++)
        {
            $elem = $p[$i];
            if (strlen($elem))
                $list[] = $elem;
        }
        return $list;
    }

    function textmax($name,$size,$max,$valu)
    {
        $disp = str_replace('"','&quot;',$valu);
        $disp = str_replace("'",'&#039;',$disp);
        return "<input type=\"text\" name=\"$name\" size=\"$size\" maxlength=\"$max\" value=\"$disp\">";
    }

   /*
    |  We store the event list as a comma separated
    |  list with leading and trailing commas.  This
    |  returns an array with the actual list, without
    |  the empty elements.
    */

    function buildlist($delim,$elist)
    {
        $p = explode($delim,$elist);
        $list = filter($p);
        return $list;
    }

    function checkboxes($db,$config)
    {
        $txt = '';
        $fld = fake_event_list($db);
        if ($fld)
        {
            $cfg = buildlist(':',$config);
            $def = array( );
            $ncn = safe_count($cfg);
            for ($i = 0; $i < $ncn; $i++)
            {
                $def[$cfg[$i]] = 1;
            }
            $txt .= '<table cellpadding="3" cellspacing="0" bordercolor="COCOCO" border="1">' . "\n";
            $txt .= genboxes($fld,$def);
            $txt .= "</table>";
        }
        return $txt;
    }


    function report_form(&$env,&$rep,$head,$old,$db)
    {
        $now  = $env['now'];
        $auth = $env['auth'];
        $priv = $env['priv'];
        $midn = $env['midn'];
        $curr_ginc = ($rep['group_include'])? $rep['group_include'] : GRPS_ReturnAllMgroupid($db);
        $curr_gexc =  $rep['group_exclude'];
        $agg  = $rep['aggregate'];
        $gprv = ($env['user']['priv_report'])? 1 : 0;
        $yn   = array('No','Yes');
        $out  = array('Send as email','Publish on information portal');
        $rid  = $rep['id'];
        $sids = find_searches($auth,$db);
        $frms = form_options();
        unset($frms[0]);
        $form = form_decode($rep['format']);

        $s_form = html_select('format',$frms,$form,1);
        $s_file = html_select('file',$out,$rep['file'],1);
        $s_defm = html_select('defmail',$yn,$rep['defmail'],1);
        $s_dlnk = html_select('detaillinks',$yn,$rep['detaillinks'],1);
        $s_subj = textbox('subject_text',40,$rep['subject_text']);
        $s_mail = textbox('emaillist',40,$rep['emaillist']);
        $s_name = textmax('name',50,50,$rep['name']);
        $s_text = checkbox('include_text',$rep['include_text']);
        $s_user = checkbox('include_user',$rep['include_user']);
        $s_skip = checkbox('skip_owner',$rep['skip_owner']);
        $s_omit = checkbox('omit',$rep['omit']);
        $s_srch = saved_search($sids,$rep['search_list'],5, 'search_id[]','no saved searches');

        $grps = build_group_list($auth, constQueryNoRestrict, $db);
        $mstr = prep_for_multiple_select($grps);

        /* group multiple select boxes */
        $sel_include = saved_search($mstr, $curr_ginc, 7, 'g_include[]', constMachineGroupMessage);
        $sel_exclude = saved_search($mstr, $curr_gexc, 7, 'g_exclude[]', constMachineGroupMessage);

        $custom_URL = customURL(constPageEntryReports);
        $r_def      = preserve_report_state($env['rid'], $env['act']);
        $group_link = html_link("../config/groups.php?$custom_URL&$r_def", '[configure groups]');

        $elnk = html_link('search.php','[Edit a Filter]');
        $alnk = html_link('srch-add.php','[Add a Filter]');
        $cont = content_section($env,$rep);
        $shed = schedule_section($env,$rep);
        $cnfg = $rep['config'];
        $gbox = checkboxes($db,$cnfg);
        $edit = ($rid > 0);
        $omit = "Omit 'zero event' reports:";

        echo post_self('myform');
        echo hidden('debug','1');
        echo hidden('aggregate',$agg);

        /* group instructions */
        $please_note = GRPS_please_note();
        $inc_ins     = GRPS_include_instructions();
        $exc_ins     = GRPS_exclude_instructions(constEventReports);

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

        if (!$agg) /* classic report */
        {
           $q_prompt = 'A report without a query filter selects all the events,'
                     . ' and could easily become extremely large.  You should always'
                     . ' specify at least one query filter unless the date range is'
                     . ' very small.';

           $g_prompt = 'Use these checkboxes to select the detail information that'
                     . ' should be included in the report.  Except for \'ALL\', the names'
                     . ' shown are the actual field names in the event database.  Be'
                     . ' wary of requesting too much information. Reports can easily'
                     . ' become quite large.<br><br> Note that the checkboxes are'
                     . ' ignored unless the <i>Include details</i> option is enabled.';

           $di   = 'Detail Information';
           $e    = '';
           $enab = '';
           $agg_time = '';
        }
        else       /* aggregate report */
        {
            $cont = '';
            $elnk = '';
            $alnk = '';
            $gbox = '';
            $di   = '';
            $q_prompt = '';
            $g_prompt = '';
            $e = 'Enabled:';
            $agg_time = 'Please note that the schedule for <br>'
                      . 'the selected reports will be ignored. <br>'
                      . 'You will have to select a schedule for<br>'
                      . 'the aggregate report below.';
            $reportlist = find_members($old,$db);
            $s_srch     = saved_report($env,$reportlist,$db);
            $enab       = html_select('enabled',$yn,1,1);
        }

        $s_glob = '';
        if ($gprv)
        {
            $glob = checkbox('global',$rep['global']);
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
                $s_skip
              </td>
            </tr>
            <tr>
              <td>
                $omit
              </td>
              <td colspan="2">
                $s_omit
              </td>
            </tr>
            <tr>
              <td>
                $e
              </td>
              <td colspan="2">
                $enab
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
            <td valign="top">
              Event query filters:<br><br>
              <span class="footnote">
              To deselect, hold down 'ctrl' and <br>
              click again. (Mac: command key)   <br><br>
              $agg_time
              </span>
             </td>
            <td valign="top">
              $s_srch
            </td>
            <td valign="top" class="footnote">

              $elnk<br>
              $alnk<br>

              <br>
             $q_prompt

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
            <td>
              <br>
            </td>
          </tr>

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
          <tr>
            <td colspan="3">
              <b>$di</b>
            </td>
          </tr>

          <tr>
            <td valign="top" colspan="2">
              $gbox
            </td>
            <td valign="top" class="footnote">
              $g_prompt
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

HERE;

        echo form_footer();
    }


    function find_search($sid,$db)
    {
        $row = array();
        if ($sid > 0)
        {
            $sql = "select * from SavedSearches\n"
                 . " where id = $sid";
            $row = find_one($sql,$db);
        }
        return $row;
    }


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
            $rep['format']    = form_encode($frm);
            $rep['config']    = genconfig($db,true,$_POST);
            /* next/this_run is not set by update */
            $rep['next_run']  = 0;
            $rep['this_run']  = 0;
            $rep['emaillist'] = get_string('emaillist','');
            $rep[$sub]        = get_string($sub,'');
            $new = $rep['enabled'];
            if (($new) && ($old != $new))
            {
                $rep['retries'] = 0;
            }
        }

        if ($name == '')
        {
            $errs[] = 'You must specify a name for the report.';
            $good = false;
        }
        $type = $rep['cycle'];
        $mint = $rep['minute'];
        $hour = $rep['hour'];
        $agg  = $rep['aggregate'];
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
        if (($good) && ($type == ReportTypeImmediate))
        {
            $now  = $env['now'];
            $dmin = get_string('dmin','');
            $dmax = get_string('dmax','');
            $umin = parsedate($dmin,$now);
            $umax = ($dmax)? parsedate($dmax,$now) : $now;
            if ((0 < $umin) && ($umin < $umax))
            {
                $rep['umin'] = $umin;
                $rep['umax'] = $umax;
            }
            else
            {
                $errs[] = 'Did not understand the format of the start or end time';
                $good = false;
            }
        }
        if ($good)
        {
            if ($type != ReportTypeImmediate)
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
            $gprv = $env['user']['priv_report'];
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
        if (($good) && (!$agg))
        {
            $snum = 0;
            $list = '';
            $sids = find_searches($auth,$db);
            $set  = get_argument('search_id',0,array());

           /*
            |  The leading comma in the search_list is to make it
            |  easy to tell if a saved search is used, for example,
            |  when we want to delete it.
            |
            |   select * from Reports where (search_list like '%,$id,%')
            */

            if ((is_array($set)) && ($set) && ($sids))
            {
                $list = ',';
                reset($set);
                foreach ($set as $key => $sid)
                {
                    $name = @ trim($sids[$sid]);
                    if ($name)
                    {
                        $snum++;
                        debug_note("search sid:$sid name:$name");
                        if ($good)
                        {
                            $list .= "$sid,";
                        }
                        else
                        {
                            $list = '';
                        }
                    }
                    else
                    {
                        $good = false;
                        $errs[] = "Event filter <b>$sid</b> not found.";
                    }
                }
            }
            if ($snum <= 0)
            {
                $errs[] = 'The report should specify at least one event filter';
                $good = false;
                $list = '';
            }
            $rep['search_list'] = $list;
        }
        if ($good)
        {
            /* group values */
            $rep['group_include'] = GRPS_get_multiselect_values('g_include');
            $rep['group_exclude'] = GRPS_get_multiselect_values('g_exclude');
        }
        if ($good)
        {
            $set   = $env['enam'];
            $none  = 'Nothing';
            $valid = array();

            reset($set);
            foreach ($set as $key => $name)
            {
                $valid[$name] = $key;
            }
            $valid[$none] = '';
            $o1 = get_string('order1',$none);
            $o2 = get_string('order2',$none);
            $o3 = get_string('order3',$none);
            $o4 = get_string('order4',$none);
            $rep['order1'] = @ trim($valid[$o1]);
            $rep['order2'] = @ trim($valid[$o2]);
            $rep['order3'] = @ trim($valid[$o3]);
            $rep['order4'] = @ trim($valid[$o4]);
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
            $rep['created']   = $env['now'];
            $rep['modified']  = $env['now'];
            $name = $rep['name'];
            $rid  = update_report($rep,$db);
        }
        $good = false;

        if ($rid)
        {
            $rep['id'] = $rid;
            if ($rep['aggregate'])
            {
                $set = get_argument('report_id',0,array());
                reset($set);
                foreach ($set as $key => $member)
                {
                    $bmid = build_member($rid,$member,$db);
                }
            }
            $good = true;
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
            $act  = $env['act'];
            $agg  = ($act == 'adda')? 1 : 0;
            $auth = $env['auth'];
            $glob = $rep['global'];
            $gprv = $env['user']['priv_report'];
            $rep['id']     = 0;
            $rep['wday']   = -1;
            $rep['mday']   = 0;
            $rep['cycle']  = -1;
            $rep['global'] = ($gprv)? $glob : 0;
            $rep['username'] = $auth;
            $rep['aggregate'] = $agg;
            $head = ($agg)? 'Add an Aggregate' : 'Add a Report';
            report_form($env,$rep,$head,0,$db);
        }
        echo again($env);
    }


    function touch_report($rid,$db)
    {
        if ($rid > 0)
        {
            $now = time();
            $sql = "update Reports set\n"
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

        if ($rep['aggregate'])
        {
            $have = array();
            $want = array();
            $set  = get_argument('report_id',0,array());
            $tmp  = find_members($rid,$db);

            reset($set);
            foreach ($set as $key => $id)
            {
                $want[$id] = false;
            }

            reset($tmp);
            foreach ($tmp as $key => $id)
            {
                $have[$id['id']] = true;
            }

            reset($have);
            foreach ($have as $id => $data)
            {
                if(!isset($want[$id]))
                {
                    $delitem = kill_member($rid,$id,$db);
                }
            }

            reset($want);
            foreach ($want as $id => $data)
            {
                if(!isset($have[$id]))
                {
                    build_member($rid,$id,$db);
                }
            }
        }

        validate($env,$rep,$errs,$good,$db);
        if ($good)
        {
            $num = update_report($rep,$db);
            if ($num)
            {
                $errs[] = 'Report Updated';
                inactive($rid,$db);
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
        $set = 'adv,cyc,crt,dsp,def,det,enb,flt,lst,mal,gbl,'
             . 'mod,skp,out,own,fmt,lnk,nxt,pat,txt';
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

            unset($enbs[-1]); $enbs[0] = $dont;

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
        $auth = $env['auth'];
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
            $href = 'erep-em.htm';
            $open = "window.open('$href','help');";
            gang_preserve($env,'gang');
            echo hidden('hlp','gang');

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
            $x_ast = tag_int('x_ast',0,2,0);
            $x_omt = tag_int('x_omt',0,2,0);
            $x_dlk = tag_int('x_dlk',0,2,0);
            $x_pri = tag_int('x_pri',0,5,0);
            $x_frm = tag_int('x_frm',0,8,0);

            $x_mal = get_string('x_mal','');
            $x_sub = get_string('x_sub','');

            unset($enbs[ 3]);
            unset($enbs[-1]); $enbs[0] = $dont;

            $grps = build_group_list($auth, constQueryNoRestrict, $db);
            $mstr = prep_for_multiple_select($grps);

            $ALL_mgroupid = GRPS_ReturnAllMgroupid($db);
            $sel_include  = saved_search($mstr, $ALL_mgroupid, 7,
                                        'x_g_include[]', constMachineGroupMessage);
            $sel_exclude  = saved_search($mstr, 0, 7,
                                        'x_g_exclude[]', constMachineGroupMessage);

            $s_add = tiny_select('x_add', $mail, $x_add, 1, $norm);
            $s_enb = tiny_select('x_enb', $enbs, $x_enb, 1, $norm);
            $s_def = tiny_select('x_def', $defs, $x_def, 1, $norm);
            $s_inc = tiny_select('x_inc', $defs, $x_inc, 1, $norm);
            $s_eml = tiny_select('x_eml', $defs, $x_eml, 1, $norm);
            $s_usr = tiny_select('x_usr', $defs, $x_usr, 1, $norm);
            $s_fil = tiny_select('x_fil', $outs, $x_fil, 1, $norm);
            $s_det = tiny_select('x_det', $defs, $x_det, 1, $norm);
            $s_lnk = tiny_select('x_lnk', $defs, $x_lnk, 1, $norm);
            $s_set = tiny_select('x_set', $mail, $x_set, 1, $norm);
            $s_frm = tiny_select('x_frm', $frms, $x_frm, 1, $norm);
            $s_skp = tiny_select('x_skp', $defs, $x_skp, 1, $norm);
            $s_ast = tiny_select('x_ast', $defs, $x_ast, 1, $norm);
            $s_omt = tiny_select('x_omt', $defs, $x_omt, 1, $norm);
            $s_dlk = tiny_select('x_dlk', $defs, $x_dlk, 1, $norm);
            $s_mal = tinybox('x_mal',50,$x_mal,$wide);
            $s_sub = tinybox('x_sub',50,$x_sub,$wide);

            $head = table_header();
            $disp = pretty_header('Edit Options',1);
            $td   = 'td style="font-size: xx-small"';
            $ts   = $td . ' colspan="2"';
            $xn   = indent(4);
            $r    = 'Recipients';
            $e    = "E-mail $r";
            $add  = 'Subject Text';
            echo <<< XXXX

            $head

            $disp

            <tr><td>

              <table border="0" width="100%">
              <tr>
                <$td>State         <br>\n$s_enb</td>
                <$td>Output        <br>\n$s_fil</td>
                <$td>Details       <br>\n$s_det</td>
                <$td>Format        <br>\n$s_frm</td>
              </tr>
              <tr>
                <$td>Include User  <br>\n$s_usr</td>
                <$td>Skip Owner    <br>\n$s_skp</td>
                <$td>Events Links  <br>\n$s_lnk</td>
                <$td>Asset Links   <br>\n$s_ast</td>
              </tr>
              <tr>
                <$td>Include Text  <br>\n$s_inc</td>
                <$td>$add (update) <br>\n$s_add</td>
                <$ts>$add (value)  <br>\n$s_sub</td>
              </tr>
              <tr>
                <$td>Default $r    <br>\n$s_def</td>
                <$td>$e (update)   <br>\n$s_set</td>
                <$ts>$e (value)    <br>\n$s_mal</td>
              </tr>
              <tr>
                <$td>Links Only    <br>\n$s_dlk</td>
                <$td>Omit Zero     <br>\n$s_omt</td>
              </tr>
              <tr>
                <$td>Include            <br>\n$sel_include</td>
                <$td>Exclude            <br>\n$sel_exclude</td>
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

            $txt = 'Delete Reports';
            gang_table($env,$set,1,$txt);

            echo okcancel(5);
            echo checkallnone(5);
            echo form_footer();
        }

        echo again($env);
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
            $sql = "select * from Reports\n"
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
            $txt = 'Event Reports to be deleted';
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
        gang_preserve($env,'list');
        echo button('Continue');

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
            $x_det = tag_int('x_det',0,2,0);
            $x_eml = tag_int('x_eml',0,2,0);
            $x_inc = tag_int('x_inc',0,2,0);
            $x_lnk = tag_int('x_lnk',0,2,0);
            $x_usr = tag_int('x_usr',0,2,0);
            $x_skp = tag_int('x_skp',0,2,0);
            $x_ast = tag_int('x_ast',0,2,0);
            $x_omt = tag_int('x_omt',0,2,0);
            $x_dlk = tag_int('x_dlk',0,2,0);
            $x_enb = tag_int('x_enb',0,2,0);
            $x_frm = tag_int('x_frm',0,8,0);

            $x_mal = get_string('x_mal','');
            $x_sub = get_string('x_sub','');

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
            if ($x_inc)
            {
                $value = $x_inc - 1;
                $trm[] = "include_text = $value";
                $ors[] = "include_text != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Include Text',$value);
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
                $arg[] = array('Event Links',$value);
            }
            if ($ginclude_str != -1)
            {
                $trm[] = "group_include  = '$ginclude_str'";
                $ors[] = "group_include != '$ginclude_str'";
                $igrp  = find_mgrp_gid($ginclude_str, constReturnGroupTypeMany, $db);
                $arg[] = array('Include', group_detail($igrp));
            }
            if ($gexclude_str != -1)
            {
                $trm[] = "group_exclude  = '$gexclude_str'";
                $ors[] = "group_exclude != '$gexclude_str'";
                $egrp  = find_mgrp_gid($gexclude_str, constReturnGroupTypeMany, $db);
                $arg[] = array('Exclude', group_detail($egrp));
            }
            if ($x_fil)
            {
                $value = $x_fil - 1;
                $trm[] = "file = $value";
                $ors[] = "file != $value";
                $value = ($value)? 'Information Portal' : 'EMail';
                $arg[] = array('Output',$value);
            }
            if ($x_det)
            {
                $value = $x_det - 1;
                $trm[] = "details = $value";
                $ors[] = "details != $value";
                $value = ($value)? 'No' : 'Yes';
                $arg[] = array('Details',$value);
            }
            if ($x_skp)
            {
                $value = $x_skp - 1;
                $trm[] = "skip_owner = $value";
                $ors[] = "skip_owner != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Skip owner',$value);
            }
            if ($x_ast)
            {
                $value = $x_ast - 1;
                $trm[] = "assetlinks = $value";
                $ors[] = "assetlinks != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Asset Links',$value);
            }
            if ($x_omt)
            {
                $value = $x_omt - 1;
                $trm[] = "omit = $value";
                $ors[] = "omit != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array("Omit 'zero event' reports",$value);
            }
            if ($x_dlk)
            {
                $value = $x_dlk - 1;
                $trm[] = "detaillinks = $value";
                $ors[] = "detaillinks != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Links Only', $value);
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
            $sql  = "update Reports set\n $sets\n"
                  . " where $cond";
            $res  = redcommand($sql,$db);
            $num  = affected($res,$db);
            debug_note("$num records updated");
        }

        if (($num) && ($ids))
        {
            $txt = join(',',$ids);
            $wrd = order($env['ord']);
            $sql = "select * from Reports\n"
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
            $sql  = "update Reports set\n $sets\n"
                  . " where $cond";
            $res  = redcommand($sql,$db);
            $num  = affected($res,$db);
            debug_note("$num records updated");
        }

        if (($num) && ($ids))
        {
            $txt = join(',',$ids);
            $wrd = order($env['ord']);
            $sql = "select * from Reports\n"
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
            $sql = "delete from Reports\n"
                 . " where id in ($txt)";
            if (!$env['user']['priv_admin'])
            {
                $qu  = safe_addslashes($env['auth']);
                $sql = "$sql\n and username = '$qu'";
            }
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
            if ($num > 0)
            {
                /* delete entries for aggregates */
                $sql = "delete from ReportGroups\n"
                     . " where owner in ($txt)";
                $res = redcommand($sql,$db);

            }
        }

        $cont = button('Continue');
        gang_preserve($env,'list');
        echo para("$num reports deleted ... ");
        echo para($cont);
        echo form_footer();
        echo again($env);
    }


    function edit_form(&$env,$db)
    {
        echo again($env);
        $rid  = $env['rid'];
        $old  = $rid;
        $rep  = find_report($rid,$db);
        $good = false;
        if ($rep)
        {
            $auth = $env['auth'];
            $user = $rep['username'];
            $good = ($auth == $user);
        }
        if ($good)
        {
            $head = $rep['name'];
            report_form($env,$rep,$head,$old,$db);
        }
        else
        {
            echo para('No access ...');
        }
        echo again($env);
    }


    function copy_form(&$env,$db)
    {
        echo again($env);
        $rid  = $env['rid'];
        $old  = $rid;
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
            $gprv = $env['user']['priv_report'];
            $name = $rep['name'];
            $head = 'Duplicate A Report';
            $name = "Copy of $name";

            $rep['id'] = 0;
            $rep['username'] = $auth;
            $rep['name'] = $name;
            $rep['global'] = ($gprv)? $glob : 0;
            report_form($env,$rep,$head,$old,$db);
        }
        else
        {
            echo para('No access ...');
        }
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
        $next = "$comp&nxt=$time&o=8&adv=0&enb=2$jump";
        $last = "$comp&lst=14&o=2&adv=0$jump";
        $mods = "$comp&mod=30&o=16&adv=1$jump";
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
        $txt[] = 'Create A New Report';

        $act[] = "$cmd=adda";
        $txt[] = 'Create A New Aggregate';

        $act[] = $last;
        $txt[] = 'Reports Run Within the Past Two Weeks';

        $act[] = $next;
        $txt[] = 'Reports Scheduled To Run During the Next Two Weeks';

        $act[] = $mods;
        $txt[] = 'Reports Modified Within the Past Month';

        $act[] = $back;
        $txt[] = 'Back to Filtered Reports Page';

        $act[] = $self . $jump;
        $txt[] = 'Reports Default View';

        command_list($act,$txt);
        echo again($env);
    }


    function over_form(&$env,$db)
    {
        echo again($env);
        $rid  = $env['rid'];
        $old  = $rid;
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
            report_form($env,$rep,$head,$old,$db);
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
            $xid = update_report($row,$db);
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
            /* this/run_next not used */
            $row['next_run'] = 0;
            $row['this_run'] = 0;
            $row['last_run'] = 0;
            $row['modified'] = $now;
            $row['username'] = $auth;
            $xid = update_report($row,$db);
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
            $sql = "update Reports set\n"
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
            $text = "Report <b>$name</b> has been rescheduled to <b>$date</b>.";
        }
        else
        {
            $text = 'No change ...';
        }
        echo para($text);
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
            $bias = server_int('cron_bias',120,$db);
            $next = $rep['next_run'] + 1;
            $when = next_cycle($rep,$next) + $bias;
            $name = $rep['name'];
            $sql = "update Reports set\n"
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
            $text = "Report <b>$name</b> has been rescheduled to <b>$date</b>.";
        }
        else
        {
            $text = 'No change ...';
        }
        echo para($text);
        report_detail_table($env,$rid,$db);
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
            $sql = "update Reports set\n"
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
            $text = "Report <b>$name</b> has been enabled.";
        }
        else
        {
            $text = 'No change ...';
        }
        echo para($text);
        if ($good)
        {
            report_detail_table($env,$rid,$db);
        }
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
            $priv = $env['priv'];
            $name = $rep['name'];
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
            $sql = "update Reports set\n"
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
            $text = "Report <b>$name</b> has been disabled.";
        }
        else
        {
            $text = 'No change ...';
        }
        echo para($text);
        if ($good)
        {
            report_detail_table($env,$rid,$db);
        }
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
    |
    |   otherwise, we'll just create the new report
    |   the way it's supposed to be.
    |
    |   if the report does not contain an email address, then
    |   create the report as a file instead.
    */

    function execute_act(&$env,$db)
    {
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
            $rep['last_run'] = 0;       // first time
            $rep['this_run'] = 0;       // first time
            $rep['next_run'] = $now;
            $rep['retries']  = 0;       // first time
            $rep['modified'] = 0;
            $rep['username'] = $auth;
            if (!$mail)
            {
                if (($defm) && (!$udef))
                {
                    $rep['file'] = 1;
                }
            }
            if ($rep['file'])
            {
                $file = true;
            }
            $xid = update_report($rep,$db);
        }

        $good = false;
        $save = false;
        if ($xid)
        {
            if ($rep['aggregate'])
            {
                $rep['enabled'] = 0;
                $res = find_members($rid,$db);
                foreach ($res as $key => $data)
                {
                    $member = $data['id'];
                    build_member($xid,$member,$db);
                }
            }
            $good = true;
            $save = true;
        }
        if (($good) && ($xid))
        {
            if ($file)
            {
                $msg  = "<br>The report <b>$name</b> has no email.<br>\n";
                $msg .= "It will be published to the information portal shortly.<br><br>";
            }
            else
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
        echo again($env);
        $now = $env['now'];
        $qmx = $env['qmx'];
        $sql = "select * from Reports\n"
             . " where enabled = 1\n"
             . " order by next_run, global, cycle, id\n"
             . " limit $qmx";
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
                $glob = $row['global'];
                $user = $row['username'];
                $last = $row['last_run'];
                $next = $row['next_run'];
                $that = $row['this_run'];
                $wait = '<br>';

                if ($now <= $next)
                {
                    $wait = age($next - $now);
                }
                if ((0 < $next) && ($next < $now))
                {
                    $late = age($now - $next);
                    $wait = page_bold($late);
                }
                if (($next < 0) && (0 < $that) && ($that <= $now))
                {
                    $wait = age($now - $that);
                    $wait = "<b>run ($wait)</b>";
                }
                if (($next < 0) && ($that <= 0))
                {
                    $wait = page_bold('run');
                }

                $tlst = nanotime($last);
                $tnxt = nanotime($next);
                $scop = ($glob)? 'g' : 'l';
                $ownr = "$user($scop)";

                $cmd  = "$self?rid=$rid&act";
                $dbg  = "$self?debug=1&id=$rid&act";
                $ax   = array( );
                $link = html_link("$cmd=view",$name);
                $ax[] = html_link("$cmd=frst",'first');
                $ax[] = html_link("$cmd=time",'now');
                $ax[] = html_link("$cmd=post",'post');

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
        echo "<p>What do you want to do?</p>\n\n\n<ol>\n";

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
        $jump = $env['jump'];
        $cmd  = "$self?act";
        $dbg  = "$self?debug=1&act";
        $day  = 86400;
        $nxt  = $day * 7;
        $cmp  = "$self?dsp=1&mal=-1";

        $act = array( );
        $txt = array( );

        $act[] = "$cmp&gbl=3&nxt=$nxt&o=8$jump";
        $txt[] = 'Compact View -- Next Run';

        $act[] = "$cmp&gbl=3&o=2$jump";
        $txt[] = 'Compact View -- Last Run';

        $act[] = "$self?dsp=0";
        $txt[] = 'Expanded View';

        $act[] = "$cmd=menu";
        $txt[] = 'Debug Menu';

        $act[] = "$cmd=stat";
        $txt[] = 'Statistics';

        $act[] = "$dbg=sane";
        $txt[] = 'Database Consistancy Check';

        $act[] = "$dbg=rset";
        $txt[] = 'Reset Report Queue Only';

        $act[] = "$dbg=queu";
        $txt[] = 'Report Queue';

        $act[] = "$cmd=push&min=10&inc=10";
        $txt[] = 'Postpone 10 minutes';

        $act[] = "$cmd=push&min=60&inc=60";
        $txt[] = 'Postpone One Hour';

        $act[] = "$cmd=push&min=240&inc=300";
        $txt[] = 'Postpone Four Hours';

        $act[] = "$cmd=rseq";
        $txt[] = 'Resequence Pending';

        $act[] = 'notify.php?act=queu';
        $txt[] = 'Notify Queue';

        $act[] = '../asset/report.php?act=queu';
        $txt[] = 'Asset Queue';

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


    function build_member($owner,$member,$db)
    {
        $rid = 0;
        if (($owner) && ($member))
        {
            $sql = "insert into\n"
                 . "  ".$GLOBALS['PREFIX']."event.ReportGroups set\n"
                 . " owner = $owner,\n"
                 . " member = $member";
            $res = redcommand($sql,$db);

            if (affected($res,$db) == 1)
            {
                $rid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
            }
        }
        return $rid;
    }


    function kill_member($owner,$member,$db)
    {
        $sql = "delete from ReportGroups\n"
             . " where (owner = $owner)\n"
             . " and (member = $member)";
        $res = redcommand($sql,$db);
        return affected($res,$db);
    }


    function find_members_name($rid,$db)
    {
       $sql = "select R.name, R.id from ReportGroups as G,\n"
            . " Reports as R\n"
            . " where G.owner = $rid\n"
            . " and R.id = G.member\n"
            . " and R.aggregate = 0\n"
            . " order by name,id";
       return find_many($sql,$db);
    }


    function detail_links(&$env,&$row,$db)
    {
        $auth = $env['auth'];
        $self = $env['self'];
        $priv = $env['priv'];
        $admn = $env['user']['priv_admin'];

        $now  = time();
        $rid  = $row['id'];
        $name = $row['name'];
        $glob = $row['global'];
        $enab = $row['enabled'];
        $user = $row['username'];
        $next = $row['next_run'];
        $mine = ($user == $auth);
        $enab = ($enab == 1);
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
            $locl = find_report_name($name,0,$auth,$db);
            if (!$locl)
            {
                if ($enab)
                    $ax[] = html_link("$cmd=dovr",'disable');
                else
                    $ax[] = html_link("$cmd=eovr",'enable');
                $ax[] = html_link("$cmd=over",'edit');
            }
        }

        if (($glob) || ($mine) || ($admn))
        {
            $ax[] = html_link("$cmd=exec",'run');
        }

        if (($priv) && (!$mine))
        {
            if ($enab)
                $ax[] = html_link("$cmd=disb",'p.disable');
            else
                $ax[] = html_link("$cmd=enab",'p.enable');
        }
        if (($priv) && ($enab) && ($next > $now))
        {
            $ax[] = html_link("$cmd=redo",'p.redo');
        }
        if (($priv) && ($enab) && ($next > 0))
        {
            $ax[] = html_link("$cmd=skip",'p.skip');
        }
        return $ax;
    }


    function report_detail_table(&$env,$rid,$db)
    {
        $good = false;
        $auth = $env['auth'];
        $self = $env['self'];
        $priv = $env['priv'];
        $defs = $env['user']['report_mail'];
        $admn = $env['user']['priv_admin'];
        $defs = ($defs)? "<i>$defs</i>" : '';

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
            $names = '';
            $agg = $row['aggregate'];
            if ($agg)
            {
                $set = find_members($rid,$db);
                $tmp = array();
                if ($set)
                {
                    foreach($set as $report)
                    {
                        $mid   = $report['id'];
                        $text  = $report['name'];
                        $view  = "$self?rid=$mid&act=view";
                        $tmp[] = html_page($view,$text);
                    }
                }
                $names = ($tmp)? join('<br>',$tmp) : 'None';
                $names = double('Reports in Aggregate:',$names);
            }

            $now  = time();
            $omit = $row['omit'];
            $dlnk = $row['detaillinks'];
            $file = $row['file'];
            $name = $row['name'];
            $type = $row['cycle'];
            $dets = $row['details'];
            $enab = $row['enabled'];
            $next = $row['next_run'];
            $skip = $row['skip_owner'];
            $list = $row['search_list'];

            $o1 = $row['order1'];
            $o2 = $row['order2'];
            $o3 = $row['order3'];
            $o4 = $row['order4'];
            $ax = detail_links($env,$row,$db);

            $igrp = $row['group_include'];
            $egrp = $row['group_exclude'];

            $igrp = find_mgrp_gid($igrp, constReturnGroupTypeMany, $db);
            $egrp = find_mgrp_gid($egrp, constReturnGroupTypeMany, $db);

            $cfg  = explode(':',$row['config']);
            $cfg  = remove_empty($cfg);
            $scop = ($glob)? 'Global' : 'Local';
            $enab = enabled($row['enabled']);
            $shed = shed($env,$row);
            $typs = type_options();
            $frms = form_options();
            $form = form_decode($row['format']);
            $form = $frms[$form];
            $omit = ($omit)? 'Yes' : 'No';
            $dlnk = ($dlnk)? 'Yes' : 'No';
            $dets = ($dets)? 'Yes' : 'No';
            $itxt = ($igrp)? GRPS_edit_group_detail($igrp) : 'All groups included';
            $etxt = ($egrp)? GRPS_edit_group_detail($egrp) : 'Nothing Excluded';

            echo jumplist($ax);

            echo table_header();
            echo pretty_header($name,2);
            echo double(   'Owner',         $row['username']);
            echo double(   'Scope',         $scop);
            echo double(   'Cycle',         $typs[$type]);
            echo double( 'Details',         $dets);
            echo double('Links Only',       $dlnk);
            echo double('Schedule',         $shed);
            echo double(   'State',         $enab);
            echo double(  'Format',         $form);
            echo double('Omit Zero',        $omit);
            echo $names;
            echo double(  'Include',        $itxt);
            echo double(  'Exclude',        $etxt);

            if ($glob)
            {
                $skip_t = ($skip)? 'Yes' : 'No';
                echo double('Skip Owner',$skip_t);
            }
            if ($o1) echo double('Order1',$o1);
            if ($o2) echo double('Order2',$o2);
            if ($o3) echo double('Order3',$o3);
            if ($o4) echo double('Order4',$o4);

            $set = buildlist(',',$list);
            if ($admn)
                $sids = all_searches($db);
            else
                $sids = find_searches($auth,$db);
            if (($set) && ($sids))
            {
                $cmd  = 'search.php?act=view&sid';
                $snum = 0;
                $text = '';
                reset($set);
                foreach ($set as $key => $sid)
                {
                    $name = @ trim($sids[$sid]);
                    if ($name)
                    {
                        $link = html_link("$cmd=$sid",$name);
                        $text .= "$link<br>\n";
                        $snum++;
                    }
                }
                if (($snum) && ($text))
                {
                    if ($snum > 1)
                    {
                        echo double('Count',$snum);
                    }
                    echo double('Event Filter',$text);
                }
            }

            if ($row['retries'])
            {
                echo double('Retries',$row['retries']);
            }

            if (!$file)
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
                if (($defm) && (!$dst))
                {
                    echo double('EMail',"<i>$user default</i>");
                }
                $lnk = ($lnk)? 'Yes' : 'No';
                echo double('Links',$lnk);
            }
            else
            {
                echo double('Output','Information Portal');
            }
            $text = ($cfg)? join('<br>',$cfg) : 'None';
            echo double('Config',$text);
            if ($row['umin'])
            {
                echo double('Start Time',showtime($now,$row['umin']));
            }
            if ($row['umax'])
            {
                echo double('Stop Time',showtime($now,$row['umax']));
            }


            echo double(    'Created', showtime($now,$row['created']));
            echo double(   'Modified', showtime($now,$row['modified']));
            echo double(   'Last Run', showtime($now,$row['last_run']));
            echo double(   'Next Run', showtime($now,$row['next_run']));
            if ($priv)
            {
                if ($row['this_run'])
                {
                    echo double('This Run',showtime($now,$row['this_run']));
                }
                $date = datestring($now);
                echo dgreen('Record',$rid);
                echo dgreen(   'Now',$date);
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

    function report_detail(&$env,$db)
    {
        echo again($env);
        $rid  = $env['rid'];
        report_detail_table($env,$rid,$db);
        echo again($env);
    }


    function report_sanity(&$env,$db)
    {
        echo again($env);
        $sql = "select G.owner from\n"
             . " ReportGroups as G\n"
             . " left join Reports as R\n"
             . " on R.id = G.owner\n"
             . " where R.id is NULL\n"
             . " group by G.owner";
        $set = find_many($sql,$db);
        if ($set)
        {
            $num = safe_count($set);
            echo para("There are $num missing reports.");
            reset($set);
            foreach ($set as $key => $row)
            {
                $num = 0;
                $rid = $row['owner'];
                $sql = "delete from\n"
                     . " ReportGroups\n"
                     . " where owner = $rid";
                $res = redcommand($sql,$db);
                $num = affected($res,$db);
                debug_note("Report $rid does not exist, $num records removed.");
            }
        }
        else
        {
            echo para($GLOBALS['PREFIX'].'event.ReportGroups.owner: OK');
        }
        $sql = "select G.member from\n"
             . " ReportGroups as G\n"
             . " left join Reports as R\n"
             . " on R.id = G.member\n"
             . " where R.id is NULL\n"
             . " group by G.member";
        $set = find_many($sql,$db);
        if ($set)
        {
            $num = safe_count($set);
            echo para("There are $num missing reports.");
            reset($set);
            foreach ($set as $key => $row)
            {
                $rid = $row['member'];
                $sql = "delete from\n"
                     . " ReportGroups\n"
                     . " where member = $rid";
                $res = redcommand($sql,$db);
                $num = affected($res,$db);
                debug_note("Report $rid does not exist, $num records removed.");
            }
        }
        else
        {
            echo para($GLOBALS['PREFIX'].'event.ReportGroups.member: OK');
        }
        $imm = ReportTypeImmediate;
        $sql = "update Reports set\n"
             . " umin = 0,\n"
             . " umax = 0\n"
             . " where cycle != $imm";
        $res = redcommand($sql,$db);
        $num = affected($res,$db);
        debug_note("$num immediate problems.");
        $sql = "update Reports set\n"
             . " next_run = 0,\n"
             . " this_run = 0\n"
             . " where enabled = 0";
        $res = redcommand($sql,$db);
        $num = affected($res,$db);
        debug_note("$num schedule problems.");
        echo again($env);
    }


    function global_report_exists($name,$rid,$db)
    {
        $qn  = safe_addslashes($name);
        $sql = "select * from Reports\n"
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
        $sql = "select * from Reports\n"
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
            $sql = "select * from Reports\n"
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
            $sql = "select * from Reports where id = $id";
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
                $sql = "select * from Reports\n"
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
            $sql = "update Reports set\n"
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


    function queue_push(&$env,$db)
    {
        $nnn = 0;
        $now = $env['now'];
        $min = tag_int('min',0,1440,10);
        $inc = tag_int('inc',0,3600,60);
        $out = array( );

        $secs = $min * 60;
        $ntim = $now + $secs;
        $find = "select * from Reports\n"
              . " where next_run < $ntim\n"
              . " and next_run > 0\n"
              . " and enabled = 1\n"
              . " order by next_run, global, cycle, id";
        $set  = find_many($find,$db);

        if ($set)
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $rid  = $row['id'];
                $name = $row['name'];
                $user = $row['username'];
                $otim = $row['next_run'];
                $cmds = "update Reports set\n"
                      . " next_run = $ntim\n"
                      . " where id = $rid\n"
                      . " and enabled = 1\n"
                      . " and next_run = $otim";
                $res  = redcommand($cmds,$db);
                if (affected($res,$db))
                {
                    $out[$nnn][0] = $name;
                    $out[$nnn][1] = $user;
                    $out[$nnn][2] = $rid;
                    $out[$nnn][3] = nanotime($otim);
                    $out[$nnn][4] = nanotime($ntim);
                    $out[$nnn][5] = age($ntim - $otim);
                    $ntim = $ntim + $inc;
                    $nnn++;
                }
            }
        }
        if ($out)
        {
            $rows = safe_count($out);
            $args = explode('|','Name|Owner|Id|Old|New|Change');
            $cols = safe_count($args);
            $head = "Postponed Reports &nbsp ($rows found)";

            echo table_header();
            echo pretty_header($head,$cols);
            echo table_data($args,1);

            reset($out);
            foreach ($out as $nnn => $args)
            {
                echo table_data($args,0);
            }
            echo table_footer();
        }
        else
        {
            echo para('Nothing appropriate ...');
        }
        queue_manage($env,$db);
    }


   /*
    |  On a server that uses several long running
    |  reports we sometimes get a large backlog
    |  of pending reports, where many small daily
    |  reports can be stuck behind a group of
    |  monthly ones.
    |
    |    immediate
    |    weekdays
    |    daily
    |    weekly
    |    monthly
    */

    function queue_rseq(&$env,$db)
    {
        $nnn = 0;
        $out = array();
        $now = $env['now'];
        $inc = tag_int('inc',0,3600,60);
        $day = ReportTypeWeekdays;
        $sql = "update Reports set\n"
             . " umin = 1\n"
             . " where next_run < $now\n"
             . " and cycle = $day\n"
             . " and next_run > 0\n"
             . " and enabled = 1";
        $res = redcommand($sql,$db);
        $num = affected($res,$db);
        $sql = "select * from Reports\n"
             . " where next_run < $now\n"
             . " and next_run > 0\n"
             . " and enabled = 1\n"
             . " order by umin desc, cycle, global, next_run, id";
        $set = find_many($sql,$db);
        if ($num)
        {
            $imm = ReportTypeImmediate;
            $sql = "update Reports\n"
                 . " set umin = 0\n"
                 . " where cycle != $imm";
            redcommand($sql,$db);
        }
        if ($set)
        {
            $rows = safe_count($set);
            $time = $inc * $rows;
            $ntim = $now - $time;

            reset($set);
            foreach ($set as $key => $row)
            {
                $rid  = $row['id'];
                $name = $row['name'];
                $user = $row['username'];
                $otim = $row['next_run'];
                $cmds = "update Reports set\n"
                      . " next_run = $ntim\n"
                      . " where id = $rid\n"
                      . " and enabled = 1\n"
                      . " and next_run = $otim";
                $res  = redcommand($cmds,$db);
                if (affected($res,$db))
                {
                    $out[$nnn][0] = $name;
                    $out[$nnn][1] = $user;
                    $out[$nnn][2] = $rid;
                    $out[$nnn][3] = nanotime($otim);
                    $out[$nnn][4] = nanotime($ntim);
                    $out[$nnn][5] = age($ntim - $otim);
                    $ntim = $ntim + $inc;
                    $nnn++;
                }
            }
        }
        if ($out)
        {
            $rows = safe_count($out);
            $args = explode('|','Name|Owner|Id|Old|New|Change');
            $cols = safe_count($args);
            $head = "Resequenced Reports &nbsp ($rows found)";

            echo table_header();
            echo pretty_header($head,$cols);
            echo table_data($args,1);

            reset($out);
            foreach ($out as $nnn => $args)
            {
                echo table_data($args,0);
            }
            echo table_footer();
        }
        else
        {
            echo para('Nothing appropriate ...');
        }
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
            $sql  = "update Reports set\n"
                  . " next_run = $ntim\n"
                  . " where id = $rid\n"
                  . " and 0 <= next_run\n"
                  . " and this_run = 0\n"
                  . " and enabled = 1";
            $res  = redcommand($sql,$db);
            if (affected($res,$db))
            {
                $name = $old['name'];
                $otim = $old['next_run'];
                $otxt = nanotime($otim);
                $ntxt = nanotime($ntim);
                if (($ntim) && (!$otim))
                {
                    $text = 'scheduled at';
                }
                else
                {
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
                    $text = "$what by <b>$secs</b>, from <b>$otxt</b> to";
                }
                $msg = "Report <b>$name</b> $text <b>$ntxt</b>.";
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

                $sql = "select * from Reports\n"
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
            $sql = "update Reports set\n"
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
        $mail = ($ord == 12)? "$o=13" : "$o=12";    // mail  12, 13
        $ctim = ($ord == 14)? "$o=15" : "$o=14";    // ctim  14, 15
        $mtim = ($ord == 16)? "$o=17" : "$o=16";    // mtim  16, 17
        $dets = ($ord == 18)? "$o=19" : "$o=18";    // dets  18, 19
        $user = ($ord == 20)? "$o=21" : "$o=20";    // user  20, 21
        $rpid = ($ord == 22)? "$o=23" : "$o=22";    // rpid  22, 23
        $when = ($ord == 24)? "$o=25" : "$o=24";    // when  24, 25
        $outs = ($ord == 26)? "$o=27" : "$o=26";    // outs  26, 27
        $frmt = ($ord == 28)? "$o=29" : "$o=28";    // frmt  28, 29
        $lnks = ($ord == 30)? "$o=31" : "$o=30";    // lnks  30, 31
        $defm = ($ord == 32)? "$o=33" : "$o=32";    // defm  32, 33
        $skip = ($ord == 34)? "$o=35" : "$o=34";    // skip  34, 35
        $aggr = ($ord == 36)? "$o=37" : "$o=36";    // aggr  36, 37
        $omit = ($ord == 38)? "$o=39" : "$o=38";    // omit  38, 39
        $dlnk = ($ord == 40)? "$o=41" : "$o=40";    // dlnk  40, 41
        $ginc = ($ord == 42)? "$o=43" : "$o=42";
        $gexc = ($ord == 44)? "$o=45" : "$o=44";

        $acts = 'Action';
        $rpid = html_jump($rpid,$jump,'Id');
        $name = html_jump($name,$jump,'Name');
        $when = html_jump($when,$jump,'When');
        $user = html_jump($user,$jump,'Owner');
        $lnks = html_jump($lnks,$jump,'Links');
        $enab = html_jump($enab,$jump,'State');
        $glob = html_jump($glob,$jump,'Scope');
        $ctim = html_jump($ctim,$jump,'Create');
        $mtim = html_jump($mtim,$jump,'Modify');
        $outs = html_jump($outs,$jump,'Output');
        $frmt = html_jump($frmt,$jump,'Format');
        $dets = html_jump($dets,$jump,'Details');
        $next = html_jump($next,$jump,'Next Run');
        $last = html_jump($last,$jump,'Last Run');
        $aggr = html_jump($aggr,$jump,'Aggregate');
        $omit = html_jump($omit,$jump,'Omit Zero');
        $skip = html_jump($skip,$jump,'Skip Owner');
        $dlnk = html_jump($dlnk,$jump,'Links Only');
        $ginc = html_jump($ginc,$jump,'Include');
        $gexc = html_jump($gexc,$jump,'Exclude');
        $mail = html_jump($mail,$jump,'E-mail Recipients');
        $defm = html_jump($defm,$jump,'Default Recipients');

        $args = array( );

        show($env,$args,'d_act','Action');
        show($env,$args,'d_nam',$name);
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
        show($env,$args,'d_agg',$aggr);
        show($env,$args,'d_skp',$skip);
        show($env,$args,'d_det',$dets);
        show($env,$args,'d_lnk',$lnks);
        show($env,$args,'d_omt',$omit);
        show($env,$args,'d_dlk',$dlnk);
        show($env,$args,'d_gnc',$ginc);
        show($env,$args,'d_gxc',$gexc);

        if (($set) && ($args))
        {
            $now  = time();
            $defs = $env['user']['report_mail'];
            $admn = $env['user']['priv_admin'];
            $defs = ($defs)? "<i>$defs</i>" : '';
            $cols = safe_count($args);
            $text = 'Reports';
            $tiny = ($dsp)? 0 : 1;
            $acts = '<br>';
            $frms = form_options();

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
                $glob = $row['global'];
                $frmt = $row['format'];
                $defm = $row['defmail'];
                $enab = $row['enabled'];
                $dets = $row['details'];
                $skip = $row['skip_owner'];
                $aggr = $row['aggregate'];
                $omit = $row['omit'];
                $dlnk = $row['detaillinks'];
                $ginc = $row['group_include'];
                $gexc = $row['group_exclude'];
                $lnks = link_name($row);
                $name = disp($row,'name');
                $user = disp($row,'username');
                $frmt = form_decode($frmt);
                $frmt = $frms[$frmt];

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
                $dets = ($dets)? 'Yes' : 'No';
                $defm = ($defm)? 'Yes' : 'No';
                $skip = ($skip)? 'Yes' : 'No';
                $aggr = ($aggr)? 'Yes' : 'No';
                $omit = ($omit)? 'Yes' : 'No';
                $dlnk = ($dlnk)? 'Yes' : 'No';

                $ginc = find_mgrp_gid($ginc, constReturnGroupTypeMany, $db);
                $gexc = find_mgrp_gid($gexc, constReturnGroupTypeMany, $db);

                $ginc = GRPS_edit_group_detail($ginc);
                $gexc = GRPS_edit_group_detail($gexc);

                $ginc = ($ginc)? ($ginc) : 'No';
                $gexc = ($gexc)? ($gexc) : 'No';

                $outs = ($file)? 'information portal' : 'e-mail';
                $ctim = nanotime($row['created']);
                $mtim = nanotime($row['modified']);
                $next = nanotime($row['next_run']);
                $last = nanotime($row['last_run']);
                if ($tiny)
                {
                    $ax = array( );
                    if (($glob) || ($mine) || ($admn))
                    {
                        $ax[] = html_link("$cmd=exec",'[run]');
                        $ax[] = html_link("$cmd=copy",'[copy]');
                    }
                    if ($mine)
                    {
                        if ($enab == 1)
                            $ax[] = html_link("$cmd=disb",'[disable]');
                        else
                            $ax[] = html_link("$cmd=enab",'[enable]');
                        $ax[] = html_link("$cmd=edit",'[edit]');
                        $ax[] = html_link("$cmd=cdel",'[delete]');
                    }
                    if (($glob) && (!$mine))
                    {
                        if ($enab == 1)
                            $ax[] = html_link("$cmd=dovr",'[disable]');
                        else
                            $ax[] = html_link("$cmd=eovr",'[enable]');
                        $ax[] = html_link("$cmd=over",'[edit]');
                    }
                    if (!$ax)
                    {
                        $ax[] = html_link("$cmd=view",'[details]');
                    }
                    $acts = join("<br>\n",$ax);
                }

                $args = array( );
                $name = html_page("$cmd=view",$name);
                $when = shed($env,$row);
                $enab = enabled($enab);

                show($env,$args,'d_act',$acts);
                show($env,$args,'d_nam',$name);
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
                show($env,$args,'d_agg',$aggr);
                show($env,$args,'d_skp',$skip);
                show($env,$args,'d_det',$dets);
                show($env,$args,'d_lnk',$lnks);
                show($env,$args,'d_omt',$omit);
                show($env,$args,'d_dlk',$dlnk);
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
    $priv  = @ ($user['priv_debug'])? 1 : 0;
    $admn  = @ ($user['priv_admin'])? 1 : 0;
    $dbg   = get_integer('debug',0);
    $debug = ($priv)? $dbg : 0;


    if (trim($msg)) debug_note($msg);

    debug_array($debug,$_POST);

    $day = 86400;
    $dsp = tag_int('dsp', 0,1,0);        // display, expanded
    $adv = tag_int('adv', 0,1,1);        // advanced ui, enabled
    $mal = tag_int('mal',-1,0,0);        // recipients, any
    $det = tag_int('det',-1,2,0);        // details, displayed
    $agg = tag_int('agg',-1,2,-1);       // aggregate
    $def = tag_int('def',-1,2,-1);       // defmail, not displayed
    $skp = tag_int('skp',-1,2,-1);       // skip_owner, no display
    $out = tag_int('out',-1,2,-1);       // output (file, mail)
    $omt = tag_int('omt',-1,2,-1);       // omit zero reports
    $dlk = tag_int('dlk',-1,2,-1);       // detail event links
    $gbl = tag_int('gbl',-1,3,-1);       // global, not displayed
    $enb = tag_int('enb',-1,3,0);        // enabled, any
    $lnk = tag_int('lnk',-1,4,-1);       // links, no display
    $cyc = tag_int('cyc',-1,5,0);        // cycle, any
    $fmt = tag_int('fmt',-1,8,-1);       // format, no display
    $lst = tag_int('lst',-2,9999,0);     // last run, display
    $crt = tag_int('crt',-2,9999,-1);    // create, no display
    $mod = tag_int('mod',-2,9999,-1);    // modified, no display
    $nxt = tag_int('nxt',-1,$day*14,-1); // next run, no display
    $qmx = tag_int('qmx',10,500,30);
    $ord = tag_int('o',0,50,0);
    $pag = tag_int('p',0,9999,0);
    $lim = tag_int('l',5,5000,20);
    $gnc = tag_int('gnc',-2,9999,-1); // group_include
    $gxc = tag_int('gxc',-2,9999,-1); // group_exclude
    $txt = get_string('txt','');
    $pat = get_string('pat','');
    $hlp = get_string('hlp','');
    $rid = get_integer('rid',0);
    $flt = get_integer('flt',0);
    $own = get_integer('own',-1);

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
        $own = value_range(-1,0,$own);
        $gbl = value_range(-1,2,$gbl);
    }

    if ($post == constButtonRst)
    {
        $dsp = 0;  $def = -1;  $crt = -1;  $adv = 1; $agg = -1;
        $mal = 0;  $gbl = -1;  $mod = -1;  $pag = 0; $omt = -1;
        $cyc = 0;  $nxt = -1;  $ord = 0;   $dlk = -1;
        $enb = 0;  $out = -1;  $own = -1;  $flt = 0; $gnc = -1;
        $lst = 0;  $fmt = -1;  $skp = -1;  $txt = '';$gxc = -1;
        $rid = 0;  $lnk = -1;  $lim = 20;  $pat = '';
    }

    if ($post == constButtonMore)
    {
        $adv = 1;
    }
    if ($post == constButtonLess)
    {
        $adv = 0;
    }

    $env = array( );
    $env['pid'] = getmypid();
    $env['now'] = $now;
    $env['act'] = $act;
    $env['min'] = get_integer('min',5);
    $env['ord'] = $ord;
    $env['hlp'] = $hlp;

    $env['href'] = 'page_href';
    $env['midn'] = midnight($now);
    $env['page'] = $pag;
    $env['limt'] = $lim;
    $env['jump'] = '#table';
    $env['post'] = $post;
    $env['priv'] = $priv;
    $env['admn'] = $admn;
    $env['dbug'] = $debug;
    $env['auth'] = $auth;
    $env['user'] = $user;
    $env['cycl'] = $cyclenames;
    $env['days'] = $daynames;
    $env['enam'] = $enames;
    $env['val1'] = $valid1;
    $env['val2'] = $valid2;
    $env['self'] = server_var('PHP_SELF');
    $env['args'] = server_var('QUERY_STRING');

    $env['rid'] = $rid;   // Reports.id
    $env['omt'] = $omt;   // Reports.omit
    $env['out'] = $out;   // Reports.file
    $env['cyc'] = $cyc;   // Reports.cycle
    $env['gbl'] = $gbl;   // Reports.global
    $env['fmt'] = $fmt;   // Reports.format
    $env['crt'] = $crt;   // Reports.created
    $env['det'] = $det;   // Reports.details
    $env['def'] = $def;   // Reports.defmail
    $env['enb'] = $enb;   // Reports.enabled
    $env['own'] = $own;   // Reports.username
    $env['mod'] = $mod;   // Reports.modified
    $env['lst'] = $lst;   // Reports.last_run
    $env['nxt'] = $nxt;   // Reports.next_run
    $env['agg'] = $agg;   // Reports.aggregate
    $env['mal'] = $mal;   // Reports.emaillist
    $env['skp'] = $skp;   // Reports.skip_owner
    $env['lnk'] = $lnk;   // Reports.assetlinks
    $env['flt'] = $flt;   // Reports.search_list
    $env['dlk'] = $dlk;   // Reports.detaillinks
    $env['pat'] = $pat;   // Reports.name (like)
    $env['txt'] = $txt;   // Reports.emaillist (like)
    $env['dsp'] = $dsp;
    $env['adv'] = $adv;
    $env['qmx'] = $qmx;
    $env['gnc'] = $gnc;   // Reports.group_include
    $env['gxc'] = $gxc;   // Reports.group_exclude

    $env['d_nam'] = (   true  );
    $env['d_crt'] = (0 <= $crt);
    $env['d_cyc'] = (0 <= $cyc);
    $env['d_lst'] = (0 <= $lst);
    $env['d_mal'] = (0 <= $mal);
    $env['d_mod'] = (0 <= $mod);
    $env['d_nxt'] = (0 <= $nxt);
    $env['d_gnc'] = (0 <= $gnc);
    $env['d_gxc'] = (0 <= $gxc);
    $env['d_act'] = (0 == $dsp);
    $env['d_def'] = (0 == $def);
    $env['d_det'] = (0 == $det);
    $env['d_enb'] = (0 == $enb);
    $env['d_fmt'] = (0 == $fmt);
    $env['d_lnk'] = (0 == $lnk);
    $env['d_out'] = (0 == $out);
    $env['d_skp'] = (0 == $skp);
    $env['d_agg'] = (0 == $agg);
    $env['d_omt'] = (0 == $omt);
    $env['d_dlk'] = (0 == $dlk);
    $env['d_rid'] = (3 == $gbl);
    $env['d_own'] = ((0 <= $own) || (3 == $gbl));
    $env['d_gbl'] = ((0 == $gbl) || (3 == $gbl));

    if (!$priv)
    {
        $txt = '|||menu|stat|rset|lock|pick|queu|post|push|frst|time|sane|rseq|';
        if (matchOld($act,$txt))
        {
            $act = 'list';
        }
    }

    if (!$admn)
    {
        $txt = '|||skip|redo|';
        if (matchOld($act,$txt))
        {
            $act = 'list';
        }
    }

    check_queue($env,$db);
    db_change($GLOBALS['PREFIX'].'event',$db);
    switch ($act)
    {
        case 'list': list_report($env,$db);     break;
        case 'menu': debug_menu($env,$db);      break;
        case 'lock': claim_lock($env,$db);      break;
        case 'pick': pick_lock($env,$db);       break;
        case 'stat': statistics($env,$db);      break;
        case 'addn': add_form($env,$db);        break;
        case 'adda': add_form($env,$db);        break;
        case 'insn': add_exec($env,$db);        break;
        case 'rset': queue_reset($env,$db);     break;
        case 'exec': execute_act($env,$db);     break;
        case 'disb': disable_act($env,$db);     break;
        case 'enab': enable_act($env,$db);      break;
        case 'redo': redo_act($env,$db);        break;
        case 'skip': skip_act($env,$db);        break;
        case 'updt': update_act($env,$db);      break;
        case 'over': over_form($env,$db);       break;
        case 'copy': copy_form($env,$db);       break;
        case 'edit': edit_form($env,$db);       break;
        case 'dovr': over_disb($env,$db);       break;
        case 'eovr': over_enab($env,$db);       break;
        case 'gang': gang_disp($env,$db);       break;
        case 'genb': genb_disp($env,$db);       break;
        case 'gdel': gdel_disp($env,$db);       break;
        case 'gexp': gdel_exec($env,$db);       break;
        case 'sane': report_sanity($env,$db);   break;
        case 'mnge': manage_report($env,$db);   break;
        case 'cdel': delete_conf($env,$db);     break;
        case 'rdel': delete_act($env,$db);      break;
        case 'view': report_detail($env,$db);   break;
        case 'push': queue_push($env,$db);      break;
        case 'rseq': queue_rseq($env,$db);      break;
        case 'time': queue_last($env,$db);      break;
        case 'frst': queue_first($env,$db);     break;
        case 'post': queue_post($env,$db);      break;
        case 'queu': queue_manage($env,$db);    break;
        default    : list_report($env,$db);     break;
    }
    echo head_standard_html_footer($auth,$db);
?>
