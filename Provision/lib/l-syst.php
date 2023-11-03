<?php
/*
Revision History 

When:       Who:    What:
-----       ----    -----
04-Jan-06   BJS     Created.
05-Jan-06   BJS     Added more procs from config/syst.php
11-Jan-06   BJS     Expanded support for multiple outputs.
12-Jan-06   BJS     Completed error handling & back buttons.
13-Jan-06   BJS     Added Displays.efile & filename creation.
16-Jan-06   BJS     Added descriptive act debug, added preview.
17-Jan-06   BJS     Added SQL generation for output destinations.
18-Jan-06   BJS     Moved definitions into l-text.php
30-Jan-06   BJS     Code clarifications.
01-Feb-06   BJS     SQL column name change update.
06-Apr-06   AAM     Checked in this change:
    20-Feb-06   NL      Added censusid thruout for use w/ snapshot of a machine;
                        Correct SQL to relate xxxuniq fields;
                        Comment out fcts that aren't needed 
                            once snapshot & dashboard are linked.
11-Apr-06   BTE    Removed duplicate function normalize.

*/

/*
    
      When adding a new field, do a search for __new
      to find all the procedures and defines that need
      to be expanded.
    
   */



/*                                  */
/*           Start Defines          */
/*                                  */

/* sometimes we dont want to pass in env to get $self */
define('constSelfUrl', '../config/syst.php');

/* Security Item, Modification to startup environment (scrip 27) */
define('constSecTypeStartMod',                 1);

define('constDashPopulateProfile',           900);
define('constDashPopulateDisplay',           905);

/* checkbox values we are looking for */
define('constFormatCheckbox',                -1);
define('constOutputCheckbox',                 1);

/* a parent in the asset.DataName table will have the value 0 */
define('constParent',                         0);

/* 
       All the actions will be defined by a constant
       in the form: constSTARTLINK_ (one of the 3)
        constDashStatus_
        constDashMachineGroup_
        constDashMacine_
       Various action terms are listed below.
    */
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

/* this denotes an output method requires configuration */
define('constDashConfig',                       999);

/* act set to zero after cancel */
define('constDashCancel',                         0);


/* 
      Per-Page & Configurable Output Instructions 
      __new Displays fields need to be added in l-text.php
   */


/* 
      Error messages the user recieves if they do not select the correct
      criteria on a particular page.
      __new Displays fields need to be added in l-text.php
   */


/* SQL Definitions 
    
      __new Displays fields need to be added here.
   */
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

/* report output destinations */
define('constReportOutputHTM',      'out1');
define('constReportOutputDWN',      'out2');
define('constReportOutputELM',      'out3');
define('constReportOutputASI',      'out4');
define('constReportOutputFTP',      'out5');

/* report format types */
define('constFormatTypeHTM', 'form1');
define('constFormatTypeCSV', 'form2');
define('constFormatTypeSQL', 'form3');
define('constFormatTypeXML', 'form4');

/* Configurable Variable Definitions 
    
      __new Displays fields need to be added here.
   */
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
/* yymmdd_hhmm */
define('constDateFormat',           'ymj_Gi'); /* 060113_1216 */


/*                               */
/*          End Defines          */
/*                               */




/*                                         */
/*          Start Set Definitions          */
/*                                         */


/*
      $output_type = user selected output destination
      Returns an array of configurable variables for
      an output destination.
    
      __new Displays fields need to be added here.
   */
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


/*
      array of configurable variables and error messages 
    
      __new Displays fields need to be added here.
   */
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


/*
      This array is used to see if an user supplied
      value has not been set. If the key is set to 
      the data in this array, the user has NOT changed it.
    
      __new Displays fields need to be added here.
   */
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


/* 
      array of directions for the configurable output segment 
    
      __new Displays fields need to be added here. 
   */
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


/*
      __new Displays fields need to be added here.
   */
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


/* array of headers for the configurable output segment */
function SYST_return_header_set()
{
    return array(
        constReportOutputDWN => constDWNHeader,
        constReportOutputFTP => constFTPHeader,
        constReportOutputELM => constELMHeader
    );
}

/* array of fake data used to populate the dashboard.Displays table */
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


/* array of all the possible formats for report generation */
function SYST_return_format_set()
{
    return array(
        constFormatTypeHTM => 'HTML (.html)',
        constFormatTypeCSV => 'Comma-delimited (.csv)',
        constFormatTypeSQL => 'SQL commands (.sql)',
        constFormatTypeXML => 'XML (.xml)'
    );
}


/* array of all the possbile output methods for reports */
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


/*
      $act = desired action
      Output destinations that require no configuration return $act, 
      others that do return constDashConfig.
   */
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



/*
      The key of this array is set to all of the possible (act)ions we
      can take. The value is set to the page instructions for that 
      particular action.
     
      We can easily calculate the next action and previous action
      by adding or subtracting one from the current action.
    
      __new actions added here
   */
function SYST_const_dash_set()
{
    return array(
        constDashCancel =>
        constDashCancel,

        /* constDashStatus */

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

        /* constDashMachineGroup */

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

        /* constDashMachine */

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



/*
      __new actions added here
     : debug :
   */
function SYST_act_desc()
{
    return array(
        constDashCancel =>
        'constDashCancel',

        constDashPopulateProfile =>
        'constDashPopulateProfile',

        constDashPopulateDisplay =>
        'constDashPopulateDisplay',

        /* constDashStart */

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

        /* constDashMachineGroup */

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

        /* constDashMachine */

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


/*                                       */
/*          End Set Definitions          */
/*                                       */



/*                                       */
/*          Start SQL Procedures         */
/*                                       */


/*
      $env = userdata (array)
   */
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



/*
      $env = userdata (array)

   */
function SYST_populate_DispID($env)
{
    $db        = $env['db'];
    $mgroupid  = $env['mgroupid'];
    $censusid  = $env['censusid'];
    $sel_count = 0;

    SYST_create_DispID_table(constDispID1, $db);
    SYST_create_DispID_table(constDispID2, $db);

    /*  Nina commented this out b/c the xxxuniq fields weren't related correctly.
        $sql = "INSERT IGNORE INTO " . constDispID1
             . " (iter, dataid, ordinal, value)\n"
             . " SELECT $sel_count, dataid, ordinal, value\n"
             . " FROM dashboard.ProfileDisplay as PD,\n"
             . " core.MachineGroupMap as MGM\n"
             . " WHERE (PD.censusid = MGM.censusuniq)"
             . " AND MGM.mgroupuniq = $mgroupid";
        */
    /*  Nina commented this out b/c we are using censusid instead of mgroupid, as snapshot is per machine            
        $sql = "INSERT IGNORE INTO " . constDispID1
             . " (iter, dataid, ordinal, value)\n"
             . " SELECT $sel_count, dataid, ordinal, value\n"
             . " FROM dashboard.ProfileDisplay as PD,\n"
             . " core.Census as C,\n"
             . " core.MachineGroupMap as MGM,\n"
             . " core.MachineGroups as MG\n"
             . " WHERE PD.censusid = C.id\n"
             . " AND C.censusuniq = MGM.censusuniq\n"
             . " AND MGM.mgroupuniq = MG.mgroupuniq\n"
             . " AND MG.mgroupid = " . $mgroupid;   
        */
    /* NINA: this is for one machine.  For machine groups, we
        would run this code in a loop
        for the subsequent table(s) - corresponding to the
        other machines in the machingroup */
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
        //SYST_select_star( constDispID1, $db );

        $sql = "SELECT * from " . constDispID1
            . " GROUP BY dataid";
    }
}



function SYST_name_me($env)
{
    //join constDispID1, dataorder, profiledisplay, 
    // order by ord1, ordn..
    $db  = $env['db'];
    $sql = "SELECT * FROM " . constDispID1 . ' AS D1, '
        . constDataOrder1 . ' AS DO,'
        . " " . $GLOBALS['PREFIX'] . "dashboard.ProfileDisplay AS DP\n"
        . " WHERE ( DO.dataid = D1.dataid )\n";
}

/* 
      : debug :
    */
function SYST_select_star($tbl, $db)
{
    $sql = "select * from $tbl";
}


/*
      $tbl1 = copy from this table (string)
      $tbl2 = copy to this table   (string)
      $db   = database handle      (resource identifier)

      Copy table1 to table2.
   */
function SYST_cp_table($tbl1, $tbl2, $db)
{
    $sql = "INSERT IGNORE INTO $tbl2\n"
        . " SELECT * FROM $tbl1";
    redcommand($sql, $db);
}


/*
      $tbl = table name      (string)
      $db  = database handle (resource identifier)

      Create the DispID tables.
   */
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


/*
      $tbl = table name      (string)
      $db  = database handle (resource identifier)

      Create the DataOrder tables.
   */
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


/* 
       $tbl = table name      (string)
       $db  = database handle (resource identifier)

       Returns the number of rows in the specified table.
    */
function SYST_set_count($tbl, $db)
{
    $sql     = "SELECT count(*) from $tbl";
    $set     = find_one($sql, $db);
    return $set['count(*)'];
}


/*
      $dispid = display id      (integer)
      $userid = user id         (integer)
      $item   = database item   (string)
      $db     = database handle (resource identifier)
      Returns '' (db item is not set) or the value
   */
function SYST_return_db_item($dispid, $userid, $item, $db)
{
    $sql = "SELECT $item from " . $GLOBALS['PREFIX'] . "dashboard.Displays\n"
        . " WHERE userid = $userid\n"
        . " AND dispid = $dispid";
    $set = find_one($sql, $db);
    return $set[$item];
}


/*
      : debug :
      $db = database handle (resource identifier)
      Populates the dashboard.ProfileItems table with data
      based on a hardwired machine group id joined with the
      asset table.
   */
/*
    function SYST_populate_ProfileItems( $db )
    {
        debug_note("<br> SYST_populate_ProfileItems() <br>");

        $mgroupid = constMachineGroupId;
        $sql = "INSERT IGNORE INTO dashboard.ProfileItems\n"
             . " (name, mgroupid, dataid)\n"
             . " SELECT asset.DataName.clientname,\n"
             . " core.MachineGroups.mgroupid,\n"
             . " asset.DataName.dataid\n"
             . " FROM asset.DataName JOIN core.MachineGroups\n"
             . " WHERE core.MachineGroups.mgroupid = $mgroupid";
        redcommand($sql, $db);
    }
    */

/*
      : debug :
      $db = database handle (resource identifier)
      Populates the dashboard.ProfileDisplay table with data
      based on a join with the ProfileItems, core.Census,
      core.MachineGroupMap and asset.DataName.
   */
/*
    function SYST_populate_ProfileDisplay($db)
    {
        debug_note("<br> SYST_populate_ProfileDisplay() <br>");

        /* commented out by BJ
        $sql = "INSERT IGNORE INTO dashboard.ProfileDisplay\n"
             . " (profitemid, censusid, dataid, ordinal, value, "
             . " updated, status)\n"
             . " SELECT dashboard.ProfileItems.profitemid, core.Census.id,\n"
             . " dashboard.ProfileItems.dataid, asset.AssetData.ordinal,"
             . " asset.AssetData.value, UNIX_TIMESTAMP(), 1\n"
             . " FROM dashboard.ProfileItems\n" 
             . " LEFT JOIN core.MachineGroupMap ON\n"
             . "  ( dashboard.ProfileItems.mgroupid"
             . "    = core.MachineGroupMap.mgroupid )\n"
             . "  LEFT JOIN core.Census ON\n"
             . "   ( core.MachineGroupMap.censusid"
             . "     = core.Census.id )\n"
             . "    LEFT JOIN asset.AssetData ON\n"
             . "      ( dashboard.ProfileItems.dataid"
             . "        = asset.AssetData.dataid )";
             redcommand($sql, $db);
        }
        */

/* commented out by Nina b/c uniq fields in WHERE clause aren't mapped right. 
        $sql = "INSERT IGNORE INTO dashboard.ProfileDisplay\n"
             . " (profitemid, censusid, dataid, ordinal, value, updated, status)\n"
             . " SELECT PI.profitemid, C.id,\n"
             . " PI.dataid, AD.ordinal, AD.value, UNIX_TIMESTAMP( ), 1\n"
             . " FROM dashboard.ProfileItems as PI, core.MachineGroupMap as MGM,"
             . " asset.AssetData as AD, asset.Machine as AM, core.Census as C\n"
             . " WHERE ( ( AD.machineid   = AM.machineid )\n"
             . "   AND   ( AD.slatest     = AM.slatest   )\n"
             . "   AND   ( PI.mgroupid    = MGM.mgroupuniq )\n"
             . "   AND   ( MGM.censusuniq = C.id )\n"
             . "   AND   ( PI.dataid      = AD.dataid    ) )\n"
             . " GROUP BY profitemid";
        */
/*
        $sql = "INSERT IGNORE INTO dashboard.ProfileDisplay\n"
             . " (profitemid, censusid, dataid, ordinal, value, updated, status)\n"
             . " SELECT PI.profitemid, C.id,\n"
             . " PI.dataid, AD.ordinal, AD.value, UNIX_TIMESTAMP( ), 1\n"
             . " FROM dashboard.ProfileItems as PI, core.Census as C,\n"
             . " core.MachineGroups as MG, core.MachineGroupMap as MGM,"
             . " asset.AssetData as AD, asset.Machine as AM\n"
             . " WHERE ( ( AD.machineid   = AM.machineid )\n"
             . "   AND   ( AD.slatest     = AM.slatest   )\n"
             . "   AND   ( PI.mgroupid    = MG.mgroupid )\n"
             . "   AND   ( MG.mgroupuniq  = MGM.mgroupuniq )\n"
             . "   AND   ( MGM.censusuniq = C.censusuniq )\n"
             . "   AND   ( PI.dataid      = AD.dataid    ) )\n"
             . " GROUP BY profitemid";
*/
/* commented out by BJ
        $sql = "INSERT IGNORE INTO dashboard.ProfileDisplay\n"
             . " (profitemid, censusid, dataid, ordinal, value, updated, status)\n"
             . " SELECT PI.profitemid, C.id,\n"
             . " PI.dataid, AD.ordinal, AD.value, UNIX_TIMESTAMP( ), 1\n"
             . " FROM dashboard.ProfileItems as PI, core.MachineGroupMap as MGM,"
             . " asset.AssetData as AD, asset.Machine as AM, core.Census as C\n"
             . " LEFT JOIN asset.AssetData ON ( asset.AssetData.machineid = AM.machineid )\n"
             . " WHERE ( AD.slatest   = AM.slatest   )\n"
             . " AND   ( PI.mgroupid  = MGM.mgroupid )\n"
             . " AND   ( MGM.censusid = C.id )\n"
             . " AND   ( PI.dataid    = AD.dataid    )\n"
             . " AND   ( AD.machineid = AM.machineid )"; 
        */
/*
        redcommand($sql, $db);    
    }
*/

/*
      $db = database handle (resource identifier)
      Wrapper for the following procedures:
   */
function SYST_populate_Profile($db)
{
    SYST_populate_ProfileItems($db);
    SYST_populate_ProfileDisplay($db);
}


/*
      : debug :
      $env = userdata (array)
      Populates the dashboard.Displays table with fake data
      to aid in debugging.
   */
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


/*
      $auth = current logged in user (string)
      $db   = database handle        (resource identifier)
      Selects the current user's displays.
   */
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


/*
      $auth   = current user    (string)
      $dispid = display id      (int)
      $tbl    = table name      (string)
      $db     = database handle (resource identifier)

      Returns array of displays user has access to.
   */
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


/*
      $env = userdata        (array)
      $sql = statements      (string)
      $db  = database handle (resource identifier)

      For every output destination selected
      we update the database to store the new
      values.
   */
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


/*
      $env         = userdata        (array)
      $use_default =                 (bool)
      $db          = database handle (resource identifier)
   */
function SYST_set_default($env, $use_default, $db)
{
    /* 
         Server generated filenames contain the time.
         We dont want to save a server generated filename
         to the database because we want to update the time
         every iteration. If the user enters their own file
         name, we set deffile to 0, otherwise its set to 1.
        
         We do this check by posting a hidden variable of
         the server generated filename. If they match, we
         know the user didn't change it and deffile should
         be set to 1 (server generate filename). If they 
         don't match, the user created their own filename
         and we should use that from now on, and deffile
         should be set to 0 (use user filename).
      */
    $dispid = $env['display'];
    $userid = $env['user']['userid'];
    $sql = "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "dashboard.Displays\n"
        . " SET  deffile = $use_default\n"
        . " WHERE dispid = $dispid\n"
        . " AND   userid = $userid";
    debug_note("<br>sql($sql)");
}


/*                                      */
/*          End SQL Procedures          */
/*                                      */




/*                                             */
/*           Start Display Procedures          */
/*                                             */


/*
      : debug :
      $env = array
      Presents the user will links to populate the dashboard.ProfileItems
      & ProfileDisplay tables.
   */
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


/*
      $debug = bool
      $p     = array of POST_DATA
      Display the post data to debug users
   */
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


/*
      The following procedures display links to the user
      so they can select 'status', 'machine' or a 'site'.
     
      These links will not be served by syst.php, they will
      instead be part of Nina's Dashboard. However the values
      they currently pass into the syst.php wizard will have to 
      remain the same.
   */


/*
      $env = userdata (array)

      Builds the status link to take you to the
      group wizard. This is the only link that
      implements the custom variable, the others
      use act.
   */
function SYST_display_status_link($env)
{
    $link = array();
    $self = $env['self'];
    $act  = SYST_build_act_value(constDashStatus_Start, constNextAct);
    $url  = "../config/groups.php?custom=$act";
    $link[] = html_link($url, '[Status]');
    return $link;
}


/*
      $env = userdata (array)

      Present this by default, a list of all groups, machines
      status link and debug features.
   */

/*   
    function SYST_provide_options( $env )
    {
        $mgroupid = $env['mgroupid'];

        $url   = array( );
        $url[] = SYST_display_status_link( $env );
        $url[] = SYST_display_machine_group_link($env, $mgroupid_set);
        $url[] = SYST_display_machine_link($env, $mgroupid_set);
        $links = SYST_build_link( $url );
        
        $debug_links   = array( );
        $debug_links[] = SYST_build_debug_link( $env );
        $links        .= SYST_build_link( $debug_links );
        echo $links;
    }
*/

/* 
       $env          = userdata          (array) 
       $mgroupid_set = machine group ids (array)

       Builds a link for each machine you have access to 
    */
/*    
    function SYST_display_machine_link($env, $mgroupid_set)
    {
        //debug_note("<br> SYST_display_machine_link() <br>");

        $db   = $env['db'];
        $self = $env['self'];
        $auth = $env['user']['username'];
        $link = array( );
        
        $act  = SYST_build_act_value(constDashMachine_Start, constNextAct);
        $url  = "$self?act=$act";
        
        $mgroupid_list = join(",", $mgroupid_set);
        
        /* return the unique host/site pairs for the given mgroupid(s) */
/*
        $groups_set    = GRPS_return_group_from_mgroupid($mgroupid_list,
                                                         $auth,
                                                     constQueryExcludeMgroupid,
                                                         $db);
        reset( $groups_set );
        while(list($key, $db_entry) = each( $groups_set ))
        {
            /* need the names in the form site:host */
/*
            $host         = $db_entry['host'];
            $concat_group = $db_entry['site'] . ':' . $host;
            
            /* fetch the mgroupid for the pair */
/*
            $sql = "select mgroupid from\n"
                 . " core.MachineGroups where\n"
                 . " name = '$concat_group'";
            $set = find_one($sql, $db);
            $mgroupid = $set['mgroupid'];
            
            /* create the links w/the mgroupid */
/* 
	      : debug : 
	      hard wire display 
	    */

/*
            $url    = $self . "?act=$act&mgroupid=$mgroupid&display="
	            . constProfileDisplayID;
            $link[] = html_link($url, "[$host]");
        }
        return $link;
    }
*/

/*
      $env          = userdata         (array)
      $mgroupid_set = machine group id (array)

      Builds a link for each machine group you
      have access to, this takes you to select a
      report output method.
   */
/*   
    function SYST_display_machine_group_link($env, &$mgroupid_set)
    {
        $link = array( );
        $db   = $env['db'];
        $self = $env['self'];
        $auth = $env['user']['username'];
        
        $act  = SYST_build_act_value(constDashMachineGroup_Start,
                                     constNextAct);
        $url  = "$self?act=$act";
        
        /* all GROUPS you have access to */
/*       
        $machine_groups = build_group_list($auth, constQueryRestrict, $db);

        reset( $machine_groups );
        while(list($key, $db_entry) = each( $machine_groups ))
        {
            reset( $db_entry );
            while(list($mgroupid, $set) = each( $db_entry ))
            {
                $mgname = $set['name'];
                /* 
 		  : debug :  
		  hard wire display 
		*/
/*
                $url    = $self . "?act=$act&mgroupid=$mgroupid&display="
		        . constProfileDisplayID;
                $link[] = html_link($url, "[$mgname]");
                $mgroupid_set[] = $mgroupid;
            }
        }
        return $link;
    }
*/

/*
      $env  = userdata       (array)
      $act  = action         (int)
      $type = type of button (int)

      When a user is on the configure variables page
      and they click back, values where not getting propagated.
      *NOTE* The reason for this is because we craft the 'back'
      button to be a link, not a form post. The hidden() vars only
      get used when the button is part of the form.
    
      This builds the url, handling 'back' and 'reset filenames' 
      buttons. It has also been expanded to handle going back
      from the preview page.
   */
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
            /* Configure Variables Page */
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

            /* Generate Preview Page */
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

    /* these values should always exist */
    $ext = "$self?act=$act&censusid=$censusid&mgroupid=$mgroupid&display=$display&$ext";
    $ext = SYST_build_url_extension($ext, $format_set);
    $ext = SYST_build_url_extension($ext, $output_set);

    debug_note("<br>($type)ext($ext)<br>");

    return SYST_build_button_link($ext, $type);
}


/*
      $ext = current url                (string)
      $set = enumerate values & add url (array)
        
      Return the url unmodified if set was empty
      or containing the additional variables.
    
      Both format type and output destination user 
      choices are stored in an array. When creating
      the reset button we want to append these vals
      onto the url.
   */
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


/*
      $url = links in the form:
      [0] => Array [0] => <a href="../config/groups.php?act=101">[Status]</a>
      [1] => Array [0] => <a href="/link">[All]</a>
                   [1] => <a href="/link">[User:JD]</a>

      Each link type (status, groups, machine) is held in its own array.
      Within each array contains all the links for that given type.
   */
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


/* 
      $env = userdata (array)
      For each format type create the sql
      and update the database.
   */
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


/*
      $out_num     = output destination   (int)
      $env         = user data            (array)
      $use_default = create a default file name or user
                     a user supplied one. (bool)
    
      Loop through set, check for the value existing in env,
      for which we contruct a sql line with the name of the
      constant (set[key]) = the value (env[key]).
      Builds a single line of sql.
   */
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
                /* user supplied a filename, use it */
                $use_default = 0;
            }
        }
    }
    if ($out) {
        reset($out);
        $sql = join(",\n", $out);
    }
    //debug_note("<br> sql($sql)");
    return $sql;
}


function SYST_build_report_set($env)
{
    //$format_set = SYST_build_format_set($env);

}


/*
      $env = user data (array)
      Called from /config/syst.php SYST_config_output( )
      we generate a table w/applicable input space for 
      each output destination that requires configuration.
   */
function SYST_load_configuration($env)
{
    $txt    = '';
    $output = $env['output'];
    if ($output) {
        reset($output);
        foreach ($output as $out_num => $val) {
            /* call for output methods that require configuration. */
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


/* 
      $env         = user data            (array)
      $output_type = report output method (string)
      For every output method the user wants (ftp, email, html)
      some may need to be configured. SYST_load_page() is only 
      called for those output methods that need configuration.
   */
function SYST_load_page($env, $output_type)
{
    //print_r($env);
    $db   = $env['db'];
    $disp = $env['display'];
    $auth = $env['user']['username'];

    /* returns sql for the join */
    $sql = SYST_build_display_sql($output_type);

    /* returns an array of vars that need configuration */
    $set = SYST_select_display_configurables($auth, $disp, $sql, $db);

    /* returns input html for the user */
    $fld = SYST_set_display_fields($env, $set, $output_type);

    /* returns the table header */
    $top = SYST_build_output_header($output_type);

    return <<< WHAT
        <font color=blue>$top</font>
        $fld          
        <br><br>
WHAT;
}


/* 
      $output_type = report output method (string)

      Returns SQL with the columns needed for each output type.
      email example; 'DD.eaddress, DD.esubject' 
   */
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
            logs::log(__FILE__, __LINE__, "l-syst.php: unknown($output_type)", 0);
    }
    return '';
}


/*
      $env         = user data                     (array)
      $set         = vars that need configuration  (array)
      $output_type = current output destination    (string)
    
      For every config variable we build the appropriate user
      input item (textbox, checkbox ...), set the header and
      explanation text and populate the input item.
   */
function SYST_set_display_fields($env, $set, $output_type)
{
    $txt = '<table>';

    /* for output type, return an array of items to be configured. */
    $config_set = SYST_return_configurable_set($output_type);

    reset($config_set);
    reset($set);

    foreach ($set as $key => $db_value) {
        /* This value needs to be configured. */
        if (@$config_set[$key]) {
            /* for key, return an html textbox or checkbox */
            $tmp  = SYST_build_configurable_item($env, $key, $db_value);
            $dir  = SYST_build_config_directions($key);
            $xpl  = SYST_build_config_explanation($key);
            $xpl  = "<font size=2>$xpl</font>";
            $txt .= "<tr><td>$dir</td><td>$tmp</td><td>$xpl</td></tr>";

            if ($key == constFTPPass) {
                /* 
                   | The 'password confirmation' field does not exist in the
                   | database. It is generated when needed per application.
                   | Handle this case:
                  */
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


/* return an explanation for the given key */
function SYST_build_config_explanation($key)
{
    $set = SYST_build_explanation_set();
    return $set[$key];
}


/* return directions for the given $key */
function SYST_build_config_directions($key)
{
    $set = SYST_build_direction_set();
    return $set[$key];
}


/* 
      $env      = user data                            (array)
      $item     = the configurable variable            (string constant)
      $db_value = current value of item, if any at all (mixed)
    
      __new Displays fields need to be added here.
   */
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


/* 
      $display_btn = buttons (array)

      Builds a table that includes all the buttons
      in the array.
   */
function SYST_build_button_table($display_btn)
{
    $table = '<table>';
    reset($display_btn);
    foreach ($display_btn as $key => $button) {
        $table .= "<tr><td>$button</td></tr>";
    }
    return ($table . '</table>');
}


/*
      $env      = userdata                         (array)
      $item     = config variable name             (string)
      $db_value = current value of config variable (mixed)
    
      If the user changed the filenames to something other
      than the default, deffile will be set to 0. However, 
      there can be the case where some filename fields are
      set and others are not. In that case we generate a file
      name if the db entry for that field is blank.
      We also propagate the db_value in the form: hidden_$item
   */
function SYST_set_filename($env, $item, $db_value)
{
    $db       = $env['db'];
    $dispid   = $env['display'];
    $r_button = $env['r_button'];
    $userid   = $env['user']['userid'];

    /* translate the dispid to the display name */
    $disp_name = SYST_return_db_item($dispid, $userid, 'name', $db);

    if (($r_button) ||
        (SYST_return_db_item($dispid, $userid, 'deffile', $db))
    ) {
        /* user wants default names, or they pressed the reset button;
               set a filename.                                          */
        $db_value = SYST_return_filename($disp_name);
    } else {
        /* user wants to use filenames in the db,
               however it may not be set.          */
        if (SYST_return_db_item($dispid, $userid, $item, $db) == '') {
            $db_value = SYST_return_filename($disp_name);
        }
    }

    /* Propagate db_value unaltered to monitor user change */
    $hidden = hidden("hidden_${item}", $db_value);
    return textbox($item, 55, $db_value) . $hidden;
}


function SYST_return_filename($disp_name)
{
    return ($disp_name . '_' . date(constDateFormat, time()));
}


/* returns the header for the $output_type */
function SYST_build_output_header($output_type)
{
    $set = SYST_return_header_set();
    return $set[$output_type];
}


/*
      $href   = desired url loaction   (string)
      $button = type of button created (string)

      Returns a button that uses the window.open($href, _self)
      to bring the user wherever we want.
   */
function SYST_build_button_link($href, $button)
{
    return create_custom_button($href, $button);
}


/*
      $display_set = Displays the user has access to (array)

      Returns an array of radio buttons with names.
   */
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


/*
      $set = items select (array)

      If an item exists in $set, then it was
      clicked on the previous page and we have to
      propagate its existence to the next page.
   */
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


/*
      $checkbox_set = output options (array)

      Builds a checkbox (default unclicked) for every
      output type avaiable to the user.
   */
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


/*
      $type = checkbox description (string)

      Tries to call get_integer() on every value returned
      from SYST_return_xxxxxx_set(). If the value exists
      we set the $out[$key] value to true. We use this to
      find all the checkboxes the user selected.
   */
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



/*
      $act  = current action          (int)
      $type = can be previous or next (string)

      constSet is an array of all the possible action choices,
      we use the type to get the next action (next 1) or the
      previous action (back -1) by returing that value which
      corresponds to an action in the constDashSet array().
   */
function SYST_build_act_value($act, $type)
{
    return ($act + $type);
}


/* display directions dependent on the current act */
function SYST_create_directions($act)
{
    $constSet = SYST_const_dash_set();
    return $constSet[$act];
}

/* displays the act constant as a string */
function SYST_return_act_desc($act)
{
    $set = SYST_act_desc();
    return $set[$act];
}


/*                                           */
/*           End Display Procedures          */
/*                                           */




/*                                         */
/*           Start Error Handling          */
/*                                         */


/*
      $val           = value we are checking for
      $error_message = arrray of error messages passed by
                       reference.
      $act           = current action
      $error_type    = error message to display
     
      For convience of calling a single function inside of
      SYST_validate, we are passing the error_message by 
      reference and returning act. The error_message is 
      currently displayed inside SYST_validate and $act is 
      returned from SYST_validate.
      :note:
      This proc assumes that the display and format types cannot be
      zero, otherwise the 'if comparision' will fail.
   */
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


/*
      $val           = string or int of user input
      $error_message = passed by reference, array to store error message
      $error_type    = the constant stored in error_message
      $output_type   = output method user wants to use
      $err           = bool 
      
      Unlike SYST_set_error that knows the user variables cannot be zero
      here we check the data type. We know a string has not been set by
      the user if its value is '', and we know an integer has not been 
      set if its value is -1.
   */
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
        /* user did not set the variable */
        $error_message[] = $error_type;
        $err = true;
    }
    return $err;
}


/* 
      error_message = array of error messages
      Display the error message if it is set.
   */
function SYST_display_errors($error_message)
{
    reset($error_message);
    foreach ($error_message as $key => $msg) {
        if ($msg != '') {
            echo "<br>$msg<br>";
        }
    }
}


/*
      This works by checking the action the user would like to proceed to.
      If they did not select the appropriate values in the previous page
      they will be greeted with an error of why they cannot proceed
      and returned to the page they were currently at.
     
      If they selected an output method that requires configuration, such
      as email or ftp they will be sent to a configuration page.
   */
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

            /* Check to see if the output method needs configuration */
            $act = SYST_set_config($env['output'], $act);
            break;

        case constDashStatus_GenConfigReport:;
        case constDashMachine_GenConfigReport:;
        case constDashMachineGroup_GenConfigReport:
            /* 
                | For each output format the configurable variables
                | must be set. SYST_check_output will change the value
                | of $act if the user is not allowed to proceed.
               */
            $act = SYST_check_output($env, $act, $error_message);
            break;

        case constDashStatus_ConfigOutput:;
        case constDashMachine_ConfigOutput:;
        case constDashMachineGroup_ConfigOutput:
    }
    /* User didn't click 'back button' */
    if (!$b_button) {
        SYST_display_errors($error_message);
    }
    return $act;
}


/*
      $set   = array of variables to check
      $const = val we are looking to be set to 
               1 in $set.
      Returns true if const is found in the $set 
      and its value is 1, otherwise return false.
   */
function SYST_in_set($set, $const)
{
    reset($set);
    foreach ($set as $name => $val) {
        if (($const == $name) && ($val)) {
            //debug_note("<br>const($const)name($name)val($val)");
            return true;
        }
    }
    return false;
}


/*
      $env           = user data array
      $act           = current action
      $error_message = array of error messages passed by reference
     
      Some output destinations require varibles to be configured. Those
      are declared in SYST_return_configurable_set( ).
      For each var in this set call SYST_validate_config( ) which sets
      $err to true if we encounter an error. If $err gets set to true
      we then decrement $act (user cannot proceed).
   */
function SYST_check_output($env, $act, &$error_message)
{
    $err           = false;
    $output_method = $env['output'];
    if ($output_method) {
        reset($output_method);
        foreach ($output_method as $out_num => $bool) {
            $set = SYST_return_configurable_set($out_num);
            if ($set) {
                /* some output formats need configuration */
                reset($set);
                foreach ($set as $output_config_variable => $same) {
                    /* check each configuration variable for errors */
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
        /* A configuration variable was not properly set. Cannot proceed */
        return SYST_build_act_value($act, constPrevAct);
    } else {
        /* 
            | If we did not recieve an error when checking the configurable
            | variables, we now must check to see if a preview page is needed.
           */

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
            /* we do not have to do a preview */
            return $act;
        } else {
            /* do a preview */
            return SYST_build_act_value($act, constNextAct);
        }
    }
}


/*
      $output_config_variable = configurable variable
      $env                    = userdata array
      $error_message          = array passed by reference
      $err                    = bool
     
      We can add special cases into the switch statement such as
      the password fields matching. Otherwise the default code is
      reached everytime to check the config variable value.
    
      __new Displays fields need to be added here only if they contain
      an exception, such as constFTPPass.
   */
function SYST_validate_config(
    $output_config_variable,
    $env,
    &$error_message,
    $err
) {
    /* returns an array of config vars and error messages */
    $set         =   SYST_build_config_error_set();

    /* returns the user set value for the config var */
    $value       =   $env[$output_config_variable];

    /* returns the error message for the config var */
    $error_const = @$set[$output_config_variable];

    switch ($output_config_variable) {
        case constFTPPass:
            $password_pass = $env['ftppass'];
            $password_conf = $env['ftpconf'];
            $password_pass = normalize($password_pass);
            $password_conf = normalize($password_conf);

            if ($password_pass != $password_conf) {
                /* passwords do not match */
                $error_const = constFTPPassMisMatch;
                $value       = '';
            }

            /* no break because we want to call SYST_set_config_error */

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


/*
      $output_method = user selected output destination
      $act           = current action
    
      Get the array of actions that require configuration.
      If the user selected an output method that needs to
      be configured we increment act. Otherwise return the
      current value of act.
   */
function SYST_set_config($output_method, $act)
{
    $req_act    = '';
    $output_act = SYST_return_output_act($act);

    reset($output_act);
    foreach ($output_act as $out_num => $required_action) {
        if (@$output_method[$out_num]) {
            /* 
               | An output method that requires no 
               | configuration does not change $act.
              */
            if ($required_action == constDashConfig) /* 999 */ {
                debug_note("<br>output($out_num) requires config");
                /* 
                    | However, if the user selected an output method 
                    | that requires additional configuration, we need
                    | to update $act.
                   */
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


    /*                                      */
    /*          End Error Handling          */
    /*                                      */
