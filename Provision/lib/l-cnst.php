<?php

/*
Revision history:

Date        Who     What
----        ---     ----
13-Jan-03   AAM     Created.
08-Feb-03   MMK     Added constVarPackageMachines.
14-Feb-03   MMK     Added constCheckSyncMachineList.
28-May-03   MMK     Added names of ALIST items for install cookie.
11-Feb-04   EWB     Added constInfoUUID
25-Mar-05   EWB     Added constInfoToken
27-May-05   EWB     Added 'pwsc' value codes
03-Sep-05   BTE     Added CUR and constAggregateSetGConfig.
12-Sep-05   BTE     Moved DSYN related constants to l-dsyn.php.
12-Oct-05   BTE     Changed references from gconfig to core in some comments.
10-Nov-05   BTE     Added protocol version constants.
22-Nov-05   BTE     Added auditing constants.
15-Dec-05   BJS     Added table constants.
21-Dec-05   BJS     Added dasboard constants.
03-Jan-06   BTE     Added some display.SelectionOptions.tableid constants.
05-Jan-06   BJS     Moved dashboard specific defines into l-syst.php
02-Feb-06   BTE     Added constants to support selection page tables.
08-Mar-06   BTE     Added constModuleCORE and constModuleConfig.
20-Mar-06   BTE     Added constants to use the PHP_SCNF_* routines.
29-Mar-06   BTE     Added some UltraVNC constants.
06-Apr-06   AAM     Checked in this change:
    01-Mar-06   NL      Added more constants for dashboard tableids (for sel
                        tables).
11-Apr-06   BTE     Added CFGFRMT_SRVGROUP.
14-Apr-06   BTE     Added CFGFRMT_SRVGROUPADV.
27-Apr-06   BTE     Added some table identifiers.
01-May-06   BTE     Added constSourceScrip* constants.
20-Sep-06   BTE     Added constTableIDMUMUpdates.
21-Oct-06   BTE     Added additional auditing constants.
13-Nov-06   BTE     Added constProtocolVer102.
05-Dec-06   BTE     Added some insw.h constants.
24-Feb-07   BTE     Added several constants to support report
                    scheduling/saving.
14-Mar-07   BTE     Updated default schedule contants.
03-Apr-07   BTE     Added report enable/disable constants.
09-Apr-07   BTE     Added constDataTypeReports.
15-Apr-07   BTE     Added some constants.
04-May-07   BTE     Added constSchedFormView.
09-May-07   BTE     Added constServerOptionReptCSS.
04-Jun-07   BTE     Added some constants.
19-Jun-07   AAM     Added new constants for automation.
20-Jun-07   BTE     Bug 4152: Event sections: make sure all buttons work.
22-Jun-07   BTE     Added constReportNameConfigUniq.
27-Jun-07   BTE     Added a constant.
27-Jun-07   AAM     Added constants for using user values for automation.
31-Jul-07   BTE     Added some constants.
17-Aug-07   BTE     Added some constants.
04-Oct-07   BTE     Added constFontSizeClickHere.
23-Oct-07   BTE     Added some constants.
27-Dec-07   BTE     Added some constants.
19-Feb-08   BTE     Added some constants.
03-Oct-19   SVG     Added some constants.

*/

    /* copied from defs/data.h */
    define('CUR', 1);

    /* Action codes for VARS_CheckSync, from vars.h. */
    define('constCheckSyncDoNothing', 0);
    define('constCheckSyncDoSync', 1);
    define('constCheckSyncDoRegisterAndSync', 2);
    define('constCheckSyncMachineList', 8);

    /* Field names for INFO_GetMachineID, from info.h. */
    define('constInfoSite',    'site');
    define('constInfoUUID',    'uuid');
    define('constInfoMachine', 'machine');
    define('constInfoVersion', 'version');
    define('constInfoToken',   'token');

    define('constInfoGlobalChecksum', 'globalchk');
    define('constInfoLocalChecksum',  'localchk');
    define('constInfoConfigChecksum', 'configchk');

    /* Field names for VARS_ApplyPackage, from strs.h. */
    define('constVarPackageVars',      'vars');
    define('constVarPackageMachines',  'machines');
    define('constVarPackageType',      't');
    define('constVarPackageState',     's');
    define('constVarPackageStateRev',  'sr');
    define('constVarPackageGlobal',    'g');
    define('constVarPackageGlobalRev', 'gr');
    define('constVarPackageLocal',     'l');
    define('constVarPackageLocalRev',  'lr');

   /*
    | Variable types for VARS_ApplyPackage, from vars.h.
    |
    |   core.Variables.itype
    */

    define('constVblTypeInteger',      0);
    define('constVblTypeDateTime',     1);
    define('constVblTypeString',       2);
    define('constVblTypeBoolean',      3);
    define('constVblTypeInvalid',      4);
    define('constVblTypeMailSendList', 4); /* obsolete. replaced with queues */
    define('constVblTypeLogInfoList',  5);  /* obsolete. replaced with queues */
    define('constVblTypeAList',        6);
    define('constVblTypeSemaphore',    7);
    define('constVblTypeQueue',        8);

   /*
    |  Variable types for
    |    core.VarVersions.pwsc
    |
    */

    define('constPasswordSecVarDefault', 0);
    define('constPasswordSecCleartext',  1);
    define('constPasswordSecHashed',     2);
    define('constPasswordSecEncrypted',  3);
    define('constPasswordSecInvalid',    4);

   /*
    |  Config codes for
    |    core.VarVersions.config
    |
    */

    define('constConfigNormal',  0);
    define('constConfigPassword',1);
    define('constConfigSkip',    2);
    define('constConfigPrivate', 3);
    define('constConfigIllegal', 4);

   /*
    | Configuration states for several routines, from vars.h.
    |
    |  siteman.Locals.stat
    |  siteman.Variables.stat
    |  core.ValueMap.stat
    */

    define('constVarConfStateGlobal',    0);
    define('constVarConfStateLocal',     1);
    define('constVarConfStateLocalOnly', 2);

    /* Names of alist items for install cookie. */
    define('constCookieCustID',      'CustomerID');
    define('constCookieSiteEmailID', 'MachineID');
    define('constCookieProxy',       'ProxyURL');

    /* Names of items for the list that comes from the client
        when it requests a variable package containing installation
        variables (CONF_GetClientSettings) */
    define('constConfListCustID',    'CustomerID');
    define('constConfListEmailCode', 'MachineID');
    define('constConfListSiteName',  'SiteName');
    define('constConfListOsName',    'OsName');
    define('constConfListHostName',  'HostName');
    define('constConfListUniqueValue',  'UniqueValue');
 
    define('customerid',    'cust_id');

    /* Different protocol versions used in htpc.c */
    define('constProtocolVer100',   100);
    define('constProtocolVer101',   101);
    define('constProtocolVer102',   102);

    /* Auditing constants from audt.h
        Note that only select constants are duplicated here.  audt.h has all
        of them.
     */

    /* Constant definitions - auditing level details */
    define('constAuditNone',            0);
    define('constAuditLowestDetail',    1);
    define('constAuditMediumDetail',    5);
    define('constAuditHighestDetail',   10);

    /* Constant definitions - auditing levels for various functions */
    define('constMUMChangeLevel',       4);
    define('constAUTONotifyLevel',      constAuditMediumDetail);

    /* Constant definitions - modules */
    define('constModuleDSYN',           1);
    define('constModuleCORE',           2);
    define('constModuleConfig',         3);
    define('constModuleINST',           4);
    define('constModuleMUM',            5);
    define('constModuleAUTO',           9);

    /* Constant definitions - event classes */
    define('constClassDebug',           1);
    define('constClassUser',            2);

    /* Constant definitions - products */
    define('constProductClient',        1);
    define('constProductServer',        2);
    define('constProductCSRV',          3);

    /* Constant definitions - groups */
    define('constAuditGroupMUMChange',  8);
    define('constAuditGroupAUTONotification',   12);

    /* End auditing constants */

    /* SQL Table constants */
    define('constTableTypeStatic',    1);
    define('constTableTypeTemporary', 0);
    define('constTableAssetData',     'AssetData');

    /* Constants for display.SelectionOptions.tableid */
    define('constTableIDEvents',        200); /* event.Events */
    define('constTableIDAudit',         201); /* event.Audit */
    define('constTableIDAddEventFilters',202); /* event.SavedSearches (add) */
    define('constTableIDAddMgrpInclude',203); /* core.MachineGroups (add) */
    define('constTableIDTest',          999); /* core.Test */
    define('constTableIDEventDisplay',  100); /* dashboard.EventDisplay */
    define('constTableIDMonitorDisplay',101); /* dashboard.MonitorDisplay */
    define('constTableIDProfileDisplay',102); /* dashboard.ProfileDisplay */
    define('constTableIDResourceDisplay',103); /* dashboard.ResourceDisplay */
    define('constTableIDSecurityDisplay',104); /* dashboard.SecurityDisplay */
    define('constTableIDMaintenanceDisplay',    105);   /* dashboard.MaintenanceDisplay */
    define('constTableIDDisplayMachineDisplay', 106);   /* dashboard.DisplayMachineDisplay */
    define('constTableIDDisplayMonitorDisplay', 107);   /* dashboard.DisplayMonitorDisplay */
    define('constTableIDMachineGroupDisplay',   108);   /* dashboard.MachineGroupDisplay */
    define('constTableIDMonItemGroupDisplay',   109);   /* dashboard.MonItemGroupDisplay */
    define('constTableIDMachineDisplay',        110);   /* dashboard.MachineDisplay */
    define('constTableIDValueMap',      400);   /* core.ValueMap (basic) */
    define('constTableIDValueMapAdv',   401);   /* core.ValueMap (advanced) */
    define('constTableIDMUMUpdates',    500);   /* softinst.Patches */
    define('constTableIDSections',      501);   /* report.Sections */
    define('constTableIDMUMSections',   502);   /* report.Sections (MUM) */
    define('constTableIDReports',       600);   /* report.Report */
    define('constTableIDAddSections',   601);   /* report.Sections (add) */
    define('constTableIDEventSections', 602);   /* report.Sections (Event) */
    define('constTableIDSchedules',     700);   /* schedule.Schedules */
    define('constTableIDAddSchedules',  701);   /* schedule.Schedules (add) */
    define('constTableIDAddAssetQueries',   800);   /* asset.AssetSearches (add) */
    define('constTableIDExecSumSections',   900); /* report.Sections (ExecSum) */

    /* Constants for display.SelectionOptions.opt */
    define('constOptionPageSize',               1);
    define('constOptionPageSizeStr',            "1");
    define('constOptionDisplayComplexity',      2);
    define('constOptionDisplayComplexityStr',   "2");
    define('constOptionOptionsComplexity',      3);
    define('constOptionOptionsComplexityStr',   "3");

    /* Constants for display.ColumnOptions.sort */
    define('constSortOptionNone',               0);
    define('constSortOptionBoth',               1);

    /* Constants for display.ColumnOptions.sortopt */
    define('constSortSettingNone',              0);
    define('constSortSettingAsc',               1);
    define('constSortSettingDesc',              2);

    /* Constants for display.ColumnOptions.dispdata */
    define('constDispDataNone',                 0);
    define('constDispDataTimestamp',            1);

    /* Constants for display.ColumnOptions.schopt */
    define('constSelSearchBasic',               0);
    define('constSelSearchExtended',            1);
    define('constSelSearchDate',                2);
    define('constSelSearchQuery',               3);
    define('constSelSearchScrip',               4);

    /* Constants for button actions */
    define('constNextAct',   1);
    define('constPrevAct',  -1);
    define('constCancelAct', 0);

    /* Config formats as defined in scnf.h */
    define('CFGFRMT_CLIENT',        0);
    define('CFGFRMT_SERVER',        1);
    define('CFGFRMT_SRVGROUP',      2);
    define('CFGFRMT_SRVGROUPADV',   3);

    /* Config page types for PHP_SCNF_ProcessConfigVars - defined in scnfi.c */
    define('constPageTypeScripConfig',          1);
    define('constPageTypeConfirm1',             2);
    define('constPageTypeConfirm2',             3);

    /* UltraVNC related constants */
    define('constIDMin',  200);
    define('constIDMax',  1999999);

    /* String equivalents for config change sources - scnf.h */
    define('constSourceScripConfig',            "0");
    define('constSourceScripGroupConfig',       "1");
    define('constSourceScripGroupAdvConfig',    "2");
    define('constSourceScripRemoteWizard',      "3");
    define('constSourceScripMalwareWizard',     "4");
    define('constSourceScripUpdateWizard',      "5");
    define('constSourceScripFreqWizard',        "6");

    /* Selected constants from insw.h */
    define('constMUMIntMachineStart',           1);
    define('constOutputUpdateInt',              3);

    /* Constants for SCED_CreateSchedule.defSettings */
    define('constScheduleBlank',                1);

    /* Report states */
    define('constReportDisabled',               1);
    define('constReportEnabled',                2);

    /* data versioning types from dbas.h */
    define('constDataTypeReports',              1);

    /* object types from sced.h */
    define('constObjectTypeReport',             1);

    /* SCED_GenerateHTMLControl.disposition constants */
    define('constSchedFormCreate',              0);
    define('constSchedFormEdit',                1);
    define('constSchedFormView',                2);

    /* server options */
    define('constServerOptionReptCSS',          'rept_css');
    define('constServerOptionServerURL',        'server_url');

    /* JavaScript special lists */
    define('constJavaListEventFilters',         0);
    define('constJavaListEventMgrpInclude',     1);
    define('constJavaListEventMgrpExclude',     2);
    define('constJavaListAssetQueries',         3);

    /* from insvi.c */
    define('constStartupTypeUninitialized', 0);
    define('constStartupTypeList',          1);
    define('constStartupTypeNone',          2);
    define('constStartupTypeAll',           3);
    define('constFollowonTypeUninitialized',0);
    define('constFollowonTypeList',         1);
    define('constFollowonTypeNone',         2);
    define('constFollowonTypeAll',          3);
    define('constFollowonTypeUninstall',    4);
    define('constInstPageUninitialized',    0);
    define('constInstPageFull',             1);
    define('constInstPageFrame',            2);
    define('constIntroTextUninitialized',   0);
    define('constIntroTextNone',            1);
    define('constIntroTextText',            2);
    define('constFormTypeUninitialized',    0);
    define('constFormTypeText',             1);
    define('constFormTypeTextArea',         2);
    define('constFormTypeCheckbox',         3);
    define('constFormTypeSalutation',       4);
    define('constFormTypeState',            5);
    define('constFormTypeCountry',          6);
    define('constFormTypeMonth',            7);
    define('constFormTypeDay',              8);
    define('constFormTypeYear',             9);

    /* Report config control prefix */
    define('constRepfFormPrefix',               'ctrform_');

    /* Section name reportconfiguniqs */
    define('constSectionNameEventConfigUniq',
        '6f8a39ea9f28bf718052e75e5f11e173');
    define('constSectionNameMUMConfigUniq',
        'a4790423ed34921c368c922a0a81b52e');
    define('constSectionNameExecSumConfigUniq',
        '08fff781eae063e35db95473e27fed59');

    /* Report name reportconfiguniqs */
    define('constReportNameConfigUniq', '47ff2696057fc7c5164c845d0f905e6d');

    /* Schedule name configuniq */
    define('constSchedNameConfigUniq', 'ctrsched_name');

    /* report/control.js disposition constants */
    define('constJavaListDispositionEdit', 0);
    define('constJavaListDispositionView', 1);

    /* Font size for click here links */
    define('constFontSizeClickHere',    '<font size="3">');

    /* AssetSearches.querytype constants */
    define('constAssetQueryTypePerm',   0);
    define('constAssetQueryTypeAdHoc',  1);

    /* Option caching constants */
    define('constOptionEventCode',      0);
    define('constOptionEventCodeStr',   'event_code');
