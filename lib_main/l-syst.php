<?php











define('constSelfUrl', '../config/syst.php');


define('constSecTypeStartMod',                 1);

define('constDashPopulateProfile',           900);
define('constDashPopulateDisplay',           905);


define('constFormatCheckbox',                -1);
define('constOutputCheckbox',                 1);


define('constParent',                         0);


define('constDashStatus_Start',                 100);
define('constDashStatus_SelectMachineGroup',    101);
define('constDashStatus_SelectDisplay',         102);
define('constDashStatus_SelectFormat',          103);
define('constDashStatus_SelectOutput',          104);
define('constDashStatus_GenReport',             105);
define('constDashStatus_ConfigOutput',          106);
define('constDashStatus_GenConfigReport',       107);
define('constDashStatus_GenPreview',            108);

define('constDashMachineGroup_Start',           200);
define('constDashMachineGroup_SelectFormat',    201);
define('constDashMachineGroup_SelectOutput',    202);
define('constDashMachineGroup_GenReport',       203);
define('constDashMachineGroup_ConfigOutput',    204);
define('constDashMachineGroup_GenConfigReport', 205);
define('constDashMachineGroup_GenPreview',      206);

define('constDashMachine_Start',                300);
define('constDashMachine_SelectFormat',         301);
define('constDashMachine_SelectOutput',         302);
define('constDashMachine_GenReport',            303);
define('constDashMachine_ConfigOutput',         304);
define('constDashMachine_GenConfigReport',      305);
define('constDashMachine_GenPreview',           306);


define('constDashConfig',                       999);


define('constDashCancel',                         0);









$DDftp = 'DD.ftpuser, DD.ftppass, DD.ftpurl, DD.ftpfile, DD.ftppasv';
$DDelm = 'DD.eaddress, DD.esender, DD.esubject, DD.efile';

define('constAssetDataName', 'asset.DataName');
define('constDisplaysFTP',   $DDftp);
define('constDisplaysDWN',   'DD.getfile');
define('constDisplaysELM',   $DDelm);
define('constDataOrder1',    'asset.DataOrder1');
define('constDataOrder2',    'asset.DataOrder2');
define('constDispID1',       'asset.DispID1');
define('constDispID2',       'asset.DispID2');


define('constReportOutputHTM',      'out1');
define('constReportOutputDWN',      'out2');
define('constReportOutputELM',      'out3');
define('constReportOutputASI',      'out4');
define('constReportOutputFTP',      'out5');


define('constFormatTypeHTM', 'form1');
define('constFormatTypeCSV', 'form2');
define('constFormatTypeSQL', 'form3');
define('constFormatTypeXML', 'form4');


define('constEAddress',             'eaddress');
define('constESubject',             'esubject');
define('constESender',              'esender');
define('constEFile',                'efile');
define('constFTPUser',              'ftpuser');
define('constFTPPass',              'ftppass');
define('constFTPConf',              'ftpconf');
define('constFTPUrl',               'ftpurl');
define('constFTPFile',              'ftpfile');
define('constFTPPasv',              'ftppasv');
define('constDWNGetfile',           'getfile');

define('constDateFormat',           'ymj_Gi');















function SYST_return_configurable_set($output_type)
{
    switch ($output_type) {
        case constReportOutputELM:
            return array(
                constEAddress => constEAddress,
                constESubject => constESubject,
                constESender  => constESender,
                constEFile    => constEFile
            );

        case constReportOutputFTP:
            return array(
                constFTPUser => constFTPUser,
                constFTPPass => constFTPPass,
                constFTPConf => constFTPConf,
                constFTPUrl  => constFTPUrl,
                constFTPFile => constFTPFile,
                constFTPPasv => constFTPPasv
            );

        case constReportOutputDWN:
            return array(constDWNGetfile => constDWNGetfile);

        default:
            return array();
    }
}



function SYST_build_config_error_set()
{
    return array(
        constEAddress   => constELMEaddressError,
        constESender    => constELMEsenderError,
        constEFile      => constELMEfileError,
        constFTPUser    => constFTPUserError,
        constFTPPass    => constFTPPassError,
        constFTPConf    => constFTPConfError,
        constFTPUrl     => constFTPUrlError,
        constFTPFile    => constFTPFileError,
        constDWNGetfile => constDWNGetfileError
    );
}



function SYST_return_output_type_data_set()
{
    return array(
        constEAddress   => '',
        constESender    => '',
        constESubject   => -1,
        constEFile      => '',
        constFTPUser    => '',
        constFTPPass    => '',
        constFTPConf    => '',
        constFTPUrl     => '',
        constFTPFile    => '',
        constDWNGetfile => '',
        constFTPPasv    => -1,
    );
}



function SYST_build_direction_set()
{
    return array(
        constDWNGetfile => constDWNGetfileDir,
        constEAddress   => constEAddressDir,
        constESubject   => constESubjectDir,
        constESender    => constESenderDir,
        constEFile      => constEFileDir,
        constFTPUser    => constFTPUserDir,
        constFTPPass    => constFTPPassDir,
        constFTPConf    => constFTPConfDir,
        constFTPUrl     => constFTPUrlDir,
        constFTPPasv    => constFTPPasvDir,
        constFTPFile    => constFTPFileDir
    );
}



function SYST_build_explanation_set()
{
    return array(
        constDWNGetfile => constDWNGetfileXpl,
        constEAddress   => constEAddressXpl,
        constESubject   => constESubjectXpl,
        constESender    => constESenderXpl,
        constEFile      => constEFileXpl,
        constFTPUser    => constFTPUserXpl,
        constFTPPass    => constFTPPassXpl,
        constFTPConf    => constFTPConfXpl,
        constFTPUrl     => constFTPUrlXpl,
        constFTPPasv    => constFTPPasvXpl,
        constFTPFile    => constFTPFileXpl
    );
}



function SYST_return_header_set()
{
    return array(
        constReportOutputDWN => constDWNHeader,
        constReportOutputFTP => constFTPHeader,
        constReportOutputELM => constELMHeader
    );
}


function SYST_return_display_set()
{
    return array(
        'ProfileDisplay'  => 'The Profile Display',
        'SecurityDisplay' => 'The Security Display',
        'ResourceDisplay' => 'The Resource Display',
        'EventDisplay'    => 'The Event Display',
        'MonitorDisplay'  => 'The Monitor Display'
    );
}



function SYST_return_format_set()
{
    return array(
        constFormatTypeHTM => 'HTML (.html)',
        constFormatTypeCSV => 'Comma-delimited (.csv)',
        constFormatTypeSQL => 'SQL commands (.sql)',
        constFormatTypeXML => 'XML (.xml)'
    );
}



function SYST_return_output_set()
{
    return array(
        constReportOutputHTM =>
        'Preview before generating',

        constReportOutputDWN =>
        'Download immediately',

        constReportOutputELM =>
        'Sent via email',

        constReportOutputASI =>
        'Stored on the information portal',

        constReportOutputFTP =>
        'Uploaded via FTP to a server'
    );
}



function SYST_return_output_act($act)
{
    return array(
        constReportOutputHTM => $act,
        constReportOutputDWN => constDashConfig,
        constReportOutputELM => constDashConfig,
        constReportOutputASI => $act,
        constReportOutputFTP => constDashConfig,
    );
}




function SYST_const_dash_set()
{
    return array(
        constDashCancel =>
        constDashCancel,



        constDashStatus_Start =>
        constDashStatus_Start,

        constDashStatus_SelectMachineGroup =>
        constDashStatus_SelectMachineGroup,

        constDashStatus_SelectDisplay =>
        constReportDisplay,

        constDashStatus_SelectOutput =>
        constReportOutput,

        constDashStatus_SelectFormat =>
        constReportFormat,

        constDashStatus_GenReport =>
        constDashStatus_GenReport,

        constDashStatus_ConfigOutput =>
        constConfigOutput,

        constDashStatus_GenPreview =>
        '',



        constDashMachineGroup_Start =>
        constDashMachineGroup_Start,

        constDashMachineGroup_SelectOutput =>
        constReportOutput,

        constDashMachineGroup_SelectFormat =>
        constReportFormat,

        constDashMachineGroup_GenReport =>
        constDashMachineGroup_GenReport,

        constDashMachineGroup_ConfigOutput =>
        constConfigOutput,

        constDashMachineGroup_GenPreview =>
        '',



        constDashMachine_Start =>
        constDashMachine_Start,

        constDashMachine_SelectOutput =>
        constReportOutput,

        constDashMachine_SelectFormat =>
        constReportFormat,

        constDashMachine_GenReport =>
        constDashMachine_GenReport,

        constDashMachine_ConfigOutput =>
        '',

        constDashMachine_GenPreview =>
        ''
    );
}




function SYST_act_desc()
{
    return array(
        constDashCancel =>
        'constDashCancel',

        constDashPopulateProfile =>
        'constDashPopulateProfile',

        constDashPopulateDisplay =>
        'constDashPopulateDisplay',



        constDashStatus_Start =>
        'constDashStatus_Start',

        constDashStatus_SelectMachineGroup =>
        'constDashStatus_SelectMachineGroup',

        constDashStatus_SelectDisplay =>
        'cosntDashStatus_SelectDisplay',

        constDashStatus_SelectFormat =>
        'constDashStatus_SelectFormat',

        constDashStatus_SelectOutput =>
        'constDashStatus_SelectOutput',

        constDashStatus_GenReport =>
        'constDashStatus_GenReport',

        constDashStatus_ConfigOutput =>
        'constDashStatus_ConfigOutput',

        constDashStatus_GenConfigReport =>
        'constDashStatus_GenConfigReport',

        constDashStatus_GenPreview =>
        'constDashStatus_GenPreview',



        constDashMachineGroup_Start =>
        'constDashMachineGroup_Start',

        constDashMachineGroup_SelectFormat =>
        'constDashMachineGroup_SelectFormat',

        constDashMachineGroup_SelectOutput =>
        'constDashMachineGroup_SelectOutput',

        constDashMachineGroup_GenReport =>
        'constDashMachineGroup_GenReport',

        constDashMachineGroup_ConfigOutput =>
        'constDashMachineGroup_ConfigOutput',

        constDashMachineGroup_GenConfigReport =>
        'constDashMachineGroup_GenConfigReport',

        constDashMachineGroup_GenPreview =>
        'constDashMachineGroup_GenPreview',



        constDashMachine_Start =>
        'constDashMachine_Start',

        constDashMachine_SelectFormat =>
        'constDashMachine_SelectFormat',

        constDashMachine_SelectOutput =>
        'constDashMachine_SelectOutput',

        constDashMachine_GenReport =>
        'constDashMachine_GenReport',

        constDashMachine_ConfigOutput =>
        'constDashMachine_ConfigOutput',

        constDashMachine_GenConfigReport =>
        'constDashMachine_GenConfigReport',

        constDashMachine_GenPreview =>
        'constDashMachine_GenPreview'
    );
}














function SYST_populate_DataOrder($env)
{
    $db = $env['db'];

    SYST_create_DataOrder_table(constDataOrder1, $db);
    SYST_create_DataOrder_table(constDataOrder2, $db);

    $sql = "INSERT IGNORE INTO " . constDataOrder1
        . " (level, ord1, dataid, name)\n"
        . " SELECT 1, D1.ordinal, D1.dataid, D1.name\n"
        . " FROM " . $GLOBALS['PREFIX'] . "asset.DataName as D1\n"
        . " WHERE D1.parent = 0\n";
    redcommand($sql, $db);

    SYST_cp_table(constDataOrder1, constDataOrder2, $db);

    for ($count = 2; $count <= 4; $count++) {
        if ($count >= 2) {
            $ords   = 'ord1, ord2';
            $select = 'SELECT 2, DO.ord1';
            $where  = ' WHERE (DO.level = 1)';
        }
        if ($count >= 3) {
            $ords  .= ',ord3';
            $select = 'SELECT 3, DO.ord1, DO.ord2';
            $where  = ' WHERE (DO.level = 2)';
        }
        if ($count >= 4) {
            $ords  .= ',ord4';
            $select = 'SELECT 4, DO.ord1, DO.ord2, DO.ord3';
            $where  = ' WHERE (DO.level = 3)';
        }

        $sql = "INSERT IGNORE INTO " . constDataOrder1
            . " (level, $ords, dataid, name)\n"
            . $select . ", D1.ordinal, D1.dataid, D1.name\n"
            . " FROM " . constDataOrder2
            . " AS DO, " . $GLOBALS['PREFIX'] . "asset.DataName as D1\n"
            . $where . " and (D1.parent = DO.dataid)";

        $res = redcommand($sql, $db);
        if (affected($res, $db) == 0) {
            debug_note("Nothing inserted, SQL:$sql");
        }
        SYST_cp_table(constDataOrder1, constDataOrder2, $db);
    }
}




function SYST_populate_DispID($env)
{
    $db        = $env['db'];
    $mgroupid  = $env['mgroupid'];
    $censusid  = $env['censusid'];
    $sel_count = 0;

    SYST_create_DispID_table(constDispID1, $db);
    SYST_create_DispID_table(constDispID2, $db);




    $sql = "INSERT IGNORE INTO " . constDispID1
        . " (iter, dataid, ordinal, value)\n"
        . " SELECT $sel_count, dataid, ordinal, value\n"
        . " FROM " . $GLOBALS['PREFIX'] . "dashboard.ProfileDisplay as PD\n"
        . " WHERE PD.censusid = " . $censusid;

    redcommand($sql, $db);

    SYST_cp_table(constDispID1, constDispID2, $db);

    for ($count = 2; $count <= 4; $count++) {
        $sel_count++;
        $iter_count = ($sel_count - 1);

        $sql = "INSERT IGNORE INTO " . constDispID1
            . " (iter, dataid, groups)\n"
            . " SELECT $sel_count, DN.parent, DN.groups"
            . " FROM " . $GLOBALS['PREFIX'] . "asset.DataName\n"
            . " AS DN, " . constDispID2  . " as DI\n"
            . " WHERE (DI.iter = $iter_count)\n"
            . " AND (DN.dataid = DI.dataid)";
        $res = redcommand($sql, $db);
        if (affected(redcommand($sql, $db), $db) == 0) {
            debug_note("Nothing inserted, SQL:$sql");
            $good = false;
        }
        SYST_cp_table(constDispID1, constDispID2, $db);

        $sql = "SELECT * from " . constDispID1
            . " GROUP BY dataid";
    }
}



function SYST_name_me($env)
{
    $db  = $env['db'];
    $sql = "SELECT * FROM " . constDispID1 . ' AS D1, '
        . constDataOrder1 . ' AS DO,'
        . " " . $GLOBALS['PREFIX'] . "dashboard.ProfileDisplay AS DP\n"
        . " WHERE ( DO.dataid = D1.dataid )\n";
}


function SYST_select_star($tbl, $db)
{
    $sql = "select * from $tbl";
}



function SYST_cp_table($tbl1, $tbl2, $db)
{
    $sql = "INSERT IGNORE INTO $tbl2\n"
        . " SELECT * FROM $tbl1";
    redcommand($sql, $db);
}



function SYST_create_DispID_table($tbl, $db)
{
    $sql = "DROP TABLE IF EXISTS $tbl";
    redcommand($sql, $db);

    $sql = "CREATE TEMPORARY TABLE $tbl (\n"
        . " iter    int(11)    default 0  not null,\n"
        . " dataid  int(11)    default 0  not null,\n"
        . " ordinal int(11)    default 0  not null,\n"
        . " groups  int(11)    default 0  not null,\n"
        . " value varchar(255) default '' not null\n"
        . " )";
    redcommand($sql, $db);
}



function SYST_create_DataOrder_table($tbl, $db)
{
    $sql = "DROP TABLE IF EXISTS $tbl";
    redcommand($sql, $db);

    $sql = "CREATE TEMPORARY TABLE $tbl (\n"
        . " level    int(11) default 0  not null,\n"
        . " ord1     int(11) default 0  not null,\n"
        . " ord2     int(11) default 0  not null,\n"
        . " ord3     int(11) default 0  not null,\n"
        . " ord4     int(11) default 0  not null,\n"
        . " dataid   int(11) default 0  not null,\n"
        . " name varchar(50) default '' not null\n"
        . " )";
    redcommand($sql, $db);
}



function SYST_set_count($tbl, $db)
{
    $sql     = "SELECT count(*) from $tbl";
    $set     = find_one($sql, $db);
    return $set['count(*)'];
}



function SYST_return_db_item($dispid, $userid, $item, $db)
{
    $sql = "SELECT $item from " . $GLOBALS['PREFIX'] . "dashboard.Displays\n"
        . " WHERE userid = $userid\n"
        . " AND dispid = $dispid";
    $set = find_one($sql, $db);
    return $set[$item];
}














function SYST_populate_Profile($db)
{
    SYST_populate_ProfileItems($db);
    SYST_populate_ProfileDisplay($db);
}



function SYST_populate_displays($env)
{
    $db   = $env['db'];
    $auth = $env['user']['username'];

    $display_set = SYST_return_display_set();
    reset($display_set);
    foreach ($display_set as $display_name => $display_desc) {
        $sql = "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "dashboard.Displays\n"
            . " (userid, name, descr, global, monint)\n"
            . " SELECT " . $GLOBALS['PREFIX'] . "core.Users.userid,\n"
            . " '$display_name',\n"
            . " '$display_desc',\n"
            . " 0,\n"
            . " 1234512345 FROM " . $GLOBALS['PREFIX'] . "core.Users where username = '$auth'";
        redcommand($sql, $db);
    }
}



function SYST_select_accessable_displays($auth, $db)
{
    $sql = "SELECT DD.dispid, CU.userid, DD.name, DD.descr, DD.global,"
        . " DD.monint\n"
        . " from " . $GLOBALS['PREFIX'] . "dashboard.Displays as DD,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Users as CU\n"
        . " where DD.userid = CU.userid"
        . " and CU.username = '$auth'";
    return find_many($sql, $db);
}



function SYST_select_display_configurables($auth, $dispid, $tbl, $db)
{
    $sql = "SELECT $tbl\n"
        . " FROM " . $GLOBALS['PREFIX'] . "dashboard.Displays as DD,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Users as CU\n"
        . " WHERE DD.userid = CU.userid\n"
        . " AND CU.username = '$auth'"
        . " AND DD.dispid   = $dispid";
    return find_one($sql, $db);
}



function SYST_set_output_sql($env, $sql, $db)
{
    $dispid = $env['display'];
    $userid = $env['user']['userid'];
    $txt = "UPDATE " . $GLOBALS['PREFIX'] . "dashboard.Displays set\n"
        . " $sql\n"
        . " WHERE dispid = $dispid\n"
        . " AND   userid = $userid";
    debug_note("<br>txt($txt)");
}



function SYST_set_default($env, $use_default, $db)
{

    $dispid = $env['display'];
    $userid = $env['user']['userid'];
    $sql = "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "dashboard.Displays\n"
        . " SET  deffile = $use_default\n"
        . " WHERE dispid = $dispid\n"
        . " AND   userid = $userid";
    debug_note("<br>sql($sql)");
}















function SYST_build_debug_link($env)
{
    $link   = array();
    $self   = $env['self'];
    $txt    = '[populate profile tables]';
    $link[] = html_link("$self?act=" . constDashPopulateProfile, $txt);

    $txt    = '[populate display tables]';
    $link[] = html_link("$self?act=" . constDashPopulateDisplay, $txt);
    return $link;
}



function SYST_debug_array($debug, $p)
{
    debug_note("syst_debug_array()");
    if ($debug) {
        reset($p);
        foreach ($p as $key => $data) {
            $msg = debug_note("$key: $data");
            echo "$msg<br>\n";
        }
    }
}






function SYST_display_status_link($env)
{
    $link = array();
    $self = $env['self'];
    $act  = SYST_build_act_value(constDashStatus_Start, constNextAct);
    $url  = "../config/groups.php?custom=$act";
    $link[] = html_link($url, '[Status]');
    return $link;
}





















function SYST_build_button_url($env, $act, $type)
{
    $self       = $env['self'];
    $censusid   = $env['censusid'];
    $mgroupid   = $env['mgroupid'];
    $display    = $env['display'];
    $format_set = $env['format'];
    $output_set = $env['output'];
    $ext        = '';

    debug_note("<br>build_button_url($act)<br>");
    switch ($act) {

        case constDashStatus_ConfigOutput:;
        case constDashMachine_ConfigOutput:;
        case constDashMachineGroup_ConfigOutput:
            switch ($type) {
                case constButtonReset:
                    $ext = 'r_button=1';
                    break;

                case constButtonBack:
                    $output_set = array();
                    $ext        = 'b_button=1';
                    $act = SYST_build_act_value($act, constPrevAct);
                    break;

                default:
                    debug_note("unknown type($type)");
                    break;
            }
            break;


        case constDashStatus_GenPreview:;
        case constDashMachine_GenPreview:;
        case constDashMachineGroup_GenPreview:
            switch ($type) {
                case constButtonBack:
                    $ext = 'b_button=1';
                    $act = SYST_build_act_value($act, constPrevAct);
                    break;

                default:
                    debug_note("unknown type($type)");
                    break;
            }
            break;
    }


    $ext = "$self?act=$act&censusid=$censusid&mgroupid=$mgroupid&display=$display&$ext";
    $ext = SYST_build_url_extension($ext, $format_set);
    $ext = SYST_build_url_extension($ext, $output_set);

    debug_note("<br>($type)ext($ext)<br>");

    return SYST_build_button_link($ext, $type);
}



function SYST_build_url_extension($ext, $set)
{
    if ($set) {
        reset($set);
        foreach ($set as $form_num => $int) {
            $ext .= "&$form_num=$int";
        }
    }
    return $ext;
}



function SYST_build_link($url)
{
    $text = '';
    reset($url);
    foreach ($url as $k1 => $group) {
        reset($group);
        foreach ($group as $k2 => $links) {
            $text .= $links . '<br>';
        }
        $text .= '<br>';
    }
    return $text;
}



function SYST_update_db($env)
{
    $db          = $env['db'];
    $output      = $env['output'];
    $use_default = 1;
    reset($output);
    foreach ($output as $out_num => $val) {
        switch ($out_num) {
            case constReportOutputELM:;
            case constReportOutputDWN:;
            case constReportOutputFTP:
                $sql = SYST_gen_output_sql($out_num, $env, $use_default);
                SYST_set_output_sql($env, $sql, $db);
                SYST_set_default($env, $use_default, $db);
                break;
        }
    }
}



function SYST_gen_output_sql($out_num, $env, &$use_default)
{
    $sql = '';
    $out = array();
    $set = SYST_return_configurable_set($out_num);

    reset($set);
    foreach ($set as $key => $val) {
        if (isset($env[$key])) {
            $out[] = $set[$key] . " = '" . $env[$key] . "'";

            $hidden_file = 'hidden_' . $set[$key];
            if ((isset($env[$hidden_file])) &&
                ($env[$hidden_file] != $env[$key])
            ) {

                $use_default = 0;
            }
        }
    }
    if ($out) {
        reset($out);
        $sql = join(",\n", $out);
    }
    return $sql;
}


function SYST_build_report_set($env)
{
}



function SYST_load_configuration($env)
{
    $txt    = '';
    $output = $env['output'];
    if ($output) {
        reset($output);
        foreach ($output as $out_num => $val) {

            switch ($out_num) {
                case constReportOutputELM:;
                case constReportOutputDWN:;
                case constReportOutputFTP:;
                    $txt .= SYST_load_page($env, $out_num);
                    break;
            }
        }
    }
    return $txt;
}



function SYST_load_page($env, $output_type)
{
    $db   = $env['db'];
    $disp = $env['display'];
    $auth = $env['user']['username'];


    $sql = SYST_build_display_sql($output_type);


    $set = SYST_select_display_configurables($auth, $disp, $sql, $db);


    $fld = SYST_set_display_fields($env, $set, $output_type);


    $top = SYST_build_output_header($output_type);

    return <<< WHAT
        <font color=blue>$top</font>
        $fld          
        <br><br>
WHAT;
}



function SYST_build_display_sql($output_type)
{
    switch ($output_type) {
        case constReportOutputELM:
            return constDisplaysELM;

        case constReportOutputFTP:
            return constDisplaysFTP;

        case constReportOutputDWN:
            return constDisplaysDWN;

        default:
    }
    return '';
}



function SYST_set_display_fields($env, $set, $output_type)
{
    $txt = '<table>';


    $config_set = SYST_return_configurable_set($output_type);

    reset($config_set);
    reset($set);

    foreach ($set as $key => $db_value) {

        if (@$config_set[$key]) {

            $tmp  = SYST_build_configurable_item($env, $key, $db_value);
            $dir  = SYST_build_config_directions($key);
            $xpl  = SYST_build_config_explanation($key);
            $xpl  = "<font size=2>$xpl</font>";
            $txt .= "<tr><td>$dir</td><td>$tmp</td><td>$xpl</td></tr>";

            if ($key == constFTPPass) {

                $tmp  = passbox('ftpconf', 55, $db_value);
                $dir  = SYST_build_config_directions(constFTPConf);
                $txt .= "<tr><td>$dir</td><td>$tmp</td></tr>";
            }
        } else {
            debug_note("key($key) needs no configuration");
        }
    }
    return $txt . '</table>';
}



function SYST_build_config_explanation($key)
{
    $set = SYST_build_explanation_set();
    return $set[$key];
}



function SYST_build_config_directions($key)
{
    $set = SYST_build_direction_set();
    return $set[$key];
}



function SYST_build_configurable_item($env, $item, $db_value)
{
    switch ($item) {
        case constESender:;
        case constEAddress:;
        case constESubject:;
        case constFTPUrl:;
        case constFTPUser:;
            return textbox($item, 55, $db_value);

        case constFTPPass:
            return passbox($item, 55, $db_value);

        case constFTPPasv:
            return checkbox($item, $db_value);

        case constEFile:;
        case constDWNGetfile:;
        case constFTPFile:;
            return SYST_set_filename($env, $item, $db_value);
    }
}



function SYST_build_button_table($display_btn)
{
    $table = '<table>';
    reset($display_btn);
    foreach ($display_btn as $key => $button) {
        $table .= "<tr><td>$button</td></tr>";
    }
    return ($table . '</table>');
}



function SYST_set_filename($env, $item, $db_value)
{
    $db       = $env['db'];
    $dispid   = $env['display'];
    $r_button = $env['r_button'];
    $userid   = $env['user']['userid'];


    $disp_name = SYST_return_db_item($dispid, $userid, 'name', $db);

    if (($r_button) ||
        (SYST_return_db_item($dispid, $userid, 'deffile', $db))
    ) {

        $db_value = SYST_return_filename($disp_name);
    } else {

        if (SYST_return_db_item($dispid, $userid, $item, $db) == '') {
            $db_value = SYST_return_filename($disp_name);
        }
    }


    $hidden = hidden("hidden_${item}", $db_value);
    return textbox($item, 55, $db_value) . $hidden;
}


function SYST_return_filename($disp_name)
{
    return ($disp_name . '_' . date(constDateFormat, time()));
}



function SYST_build_output_header($output_type)
{
    $set = SYST_return_header_set();
    return $set[$output_type];
}



function SYST_build_button_link($href, $button)
{
    return create_custom_button($href, $button);
}



function SYST_build_radio_buttons($display_set)
{
    $radio_set = array();
    reset($display_set);
    foreach ($display_set as $key => $db_entry) {
        $tmp    = radio('display', $db_entry['dispid'], 0);
        $tmp   .= ' ' . $db_entry['descr'];
        $radio_set[] = $tmp;
    }
    return $radio_set;
}



function SYST_hidden_multiple($set)
{
    $tmp = '';
    if ($set) {
        reset($set);
        foreach ($set as $key => $item) {
            $tmp .= hidden($key, 1);
        }
        return $tmp;
    }
    return false;
}



function SYST_build_checkboxes($checkbox_set)
{
    $set = array();
    reset($checkbox_set);
    foreach ($checkbox_set as $key => $format_type) {
        $tmp   = checkbox($key, false);
        $tmp  .= ' ' . $format_type;
        $set[] = $tmp;
    }
    return $set;
}



function SYST_get_checkbox_values($type)
{
    $out = array();
    $set = ($type == constFormatCheckbox) ? SYST_return_format_set() :
        SYST_return_output_set();
    reset($set);
    foreach ($set as $key => $format) {
        if (get_integer($key, 0)) {
            $out[$key] = true;
        }
    }
    return ($out) ? $out : false;
}




function SYST_build_act_value($act, $type)
{
    return ($act + $type);
}



function SYST_create_directions($act)
{
    $constSet = SYST_const_dash_set();
    return $constSet[$act];
}


function SYST_return_act_desc($act)
{
    $set = SYST_act_desc();
    return $set[$act];
}















function SYST_set_error($val, &$error_message, $act, $error_type)
{
    debug_note("val($val)");
    if (!$val) {
        debug_note("val($val)");
        $error_message[] = $error_type;
        $act = SYST_build_act_value($act, constPrevAct);
    }
    return $act;
}



function SYST_set_config_error(
    $val,
    &$error_message,
    $error_type,
    $output_type,
    $err
) {
    $set       = SYST_return_output_type_data_set();
    $unset_val = $set[$output_type];

    if ($val == $unset_val) {

        $error_message[] = $error_type;
        $err = true;
    }
    return $err;
}



function SYST_display_errors($error_message)
{
    reset($error_message);
    foreach ($error_message as $key => $msg) {
        if ($msg != '') {
            echo "<br>$msg<br>";
        }
    }
}



function SYST_validate($env)
{
    $good          = false;
    $error_message = array();
    $act           = $env['act'];
    $b_button      = $env['b_button'];

    switch ($act) {
        case constDashStatus_SelectFormat:
            $act = SYST_set_error(
                $env['display'],
                $error_message,
                $act,
                constSelectFormatError
            );
            break;

        case constDashStatus_SelectOutput:;
        case constDashMachine_SelectOutput:;
        case constDashMachineGroup_SelectOutput:
            $act = SYST_set_error(
                $env['format'],
                $error_message,
                $act,
                constSelectOutputError
            );
            break;

        case constDashStatus_GenReport:;
        case constDashMachine_GenReport:;
        case constDashMachineGroup_GenReport:
            $act = SYST_set_error(
                $env['output'],
                $error_message,
                $act,
                constSelectGenReportError
            );


            $act = SYST_set_config($env['output'], $act);
            break;

        case constDashStatus_GenConfigReport:;
        case constDashMachine_GenConfigReport:;
        case constDashMachineGroup_GenConfigReport:

            $act = SYST_check_output($env, $act, $error_message);
            break;

        case constDashStatus_ConfigOutput:;
        case constDashMachine_ConfigOutput:;
        case constDashMachineGroup_ConfigOutput:
    }

    if (!$b_button) {
        SYST_display_errors($error_message);
    }
    return $act;
}



function SYST_in_set($set, $const)
{
    reset($set);
    foreach ($set as $name => $val) {
        if (($const == $name) && ($val)) {
            return true;
        }
    }
    return false;
}



function SYST_check_output($env, $act, &$error_message)
{
    $err           = false;
    $output_method = $env['output'];
    if ($output_method) {
        reset($output_method);
        foreach ($output_method as $out_num => $bool) {
            $set = SYST_return_configurable_set($out_num);
            if ($set) {

                reset($set);
                foreach ($set as $output_config_variable => $same) {

                    $err = SYST_validate_config(
                        $output_config_variable,
                        $env,
                        $error_message,
                        $err
                    );
                }
            }
        }
    } else {
        $err = true;
        debug_note("no output destination selected");
    }

    if ($err) {

        return SYST_build_act_value($act, constPrevAct);
    } else {


        $output_preview = SYST_in_set(
            $output_method,
            constReportOutputHTM
        );

        $format_method  = $env['format'];
        $format_preview = SYST_in_set(
            $format_method,
            constFormatTypeHTM
        );

        if ((!$output_preview) && (!$format_preview)) {

            return $act;
        } else {

            return SYST_build_act_value($act, constNextAct);
        }
    }
}



function SYST_validate_config(
    $output_config_variable,
    $env,
    &$error_message,
    $err
) {

    $set         =   SYST_build_config_error_set();


    $value       =   $env[$output_config_variable];


    $error_const = @$set[$output_config_variable];

    switch ($output_config_variable) {
        case constFTPPass:
            $password_pass = $env['ftppass'];
            $password_conf = $env['ftpconf'];
            $password_pass = normalize($password_pass);
            $password_conf = normalize($password_conf);

            if ($password_pass != $password_conf) {

                $error_const = constFTPPassMisMatch;
                $value       = '';
            }



        default:
            $err = SYST_set_config_error(
                $value,
                $error_message,
                $error_const,
                $output_config_variable,
                $err
            );
    }
    return $err;
}



function SYST_set_config($output_method, $act)
{
    $req_act    = '';
    $output_act = SYST_return_output_act($act);

    reset($output_act);
    foreach ($output_act as $out_num => $required_action) {
        if (@$output_method[$out_num]) {

            if ($required_action == constDashConfig) {
                debug_note("<br>output($out_num) requires config");

                switch ($act) {
                    case constDashStatus_GenReport:;
                    case constDashMachine_GenReport:;
                    case constDashMachineGroup_GenReport:
                        return SYST_build_act_value($act, constNextAct);

                    default:
                        debug_note("unknown act($act)");
                }
            }
        }
    }
    return $act;
}
