<?php



    
    define('CUR', 1);

    
    define('constCheckSyncDoNothing', 0);
    define('constCheckSyncDoSync', 1);
    define('constCheckSyncDoRegisterAndSync', 2);
    define('constCheckSyncMachineList', 8);

    
    define('constInfoSite',    'site');
    define('constInfoUUID',    'uuid');
    define('constInfoMachine', 'machine');
    define('constInfoVersion', 'version');
    define('constInfoToken',   'token');

    define('constInfoGlobalChecksum', 'globalchk');
    define('constInfoLocalChecksum',  'localchk');
    define('constInfoConfigChecksum', 'configchk');

    
    define('constVarPackageVars',      'vars');
    define('constVarPackageMachines',  'machines');
    define('constVarPackageType',      't');
    define('constVarPackageState',     's');
    define('constVarPackageStateRev',  'sr');
    define('constVarPackageGlobal',    'g');
    define('constVarPackageGlobalRev', 'gr');
    define('constVarPackageLocal',     'l');
    define('constVarPackageLocalRev',  'lr');

   

    define('constVblTypeInteger',      0);
    define('constVblTypeDateTime',     1);
    define('constVblTypeString',       2);
    define('constVblTypeBoolean',      3);
    define('constVblTypeInvalid',      4);
    define('constVblTypeMailSendList', 4); 
    define('constVblTypeLogInfoList',  5);  
    define('constVblTypeAList',        6);
    define('constVblTypeSemaphore',    7);
    define('constVblTypeQueue',        8);

   

    define('constPasswordSecVarDefault', 0);
    define('constPasswordSecCleartext',  1);
    define('constPasswordSecHashed',     2);
    define('constPasswordSecEncrypted',  3);
    define('constPasswordSecInvalid',    4);

   

    define('constConfigNormal',  0);
    define('constConfigPassword',1);
    define('constConfigSkip',    2);
    define('constConfigPrivate', 3);
    define('constConfigIllegal', 4);

   

    define('constVarConfStateGlobal',    0);
    define('constVarConfStateLocal',     1);
    define('constVarConfStateLocalOnly', 2);

    
    define('constCookieCustID',      'CustomerID');
    define('constCookieSiteEmailID', 'MachineID');
    define('constCookieProxy',       'ProxyURL');

    
    define('constConfListCustID',    'CustomerID');
    define('constConfListEmailCode', 'MachineID');
    define('constConfListSiteName',  'SiteName');

    
    define('constProtocolVer100',   100);
    define('constProtocolVer101',   101);
    define('constProtocolVer102',   102);

    

    
    define('constAuditNone',            0);
    define('constAuditLowestDetail',    1);
    define('constAuditMediumDetail',    5);
    define('constAuditHighestDetail',   10);

    
    define('constMUMChangeLevel',       4);
    define('constAUTONotifyLevel',      constAuditMediumDetail);

    
    define('constModuleDSYN',           1);
    define('constModuleCORE',           2);
    define('constModuleConfig',         3);
    define('constModuleINST',           4);
    define('constModuleMUM',            5);
    define('constModuleAUTO',           9);

    
    define('constClassDebug',           1);
    define('constClassUser',            2);

    
    define('constProductClient',        1);
    define('constProductServer',        2);
    define('constProductCSRV',          3);

    
    define('constAuditGroupMUMChange',  8);
    define('constAuditGroupAUTONotification',   12);

    

    
    define('constTableTypeStatic',    1);
    define('constTableTypeTemporary', 0);
    define('constTableAssetData',     'AssetData');

    
    define('constTableIDEvents',        200); 
    define('constTableIDAudit',         201); 
    define('constTableIDAddEventFilters',202); 
    define('constTableIDAddMgrpInclude',203); 
    define('constTableIDTest',          999); 
    define('constTableIDEventDisplay',  100); 
    define('constTableIDMonitorDisplay',101); 
    define('constTableIDProfileDisplay',102); 
    define('constTableIDResourceDisplay',103); 
    define('constTableIDSecurityDisplay',104); 
    define('constTableIDMaintenanceDisplay',    105);   
    define('constTableIDDisplayMachineDisplay', 106);   
    define('constTableIDDisplayMonitorDisplay', 107);   
    define('constTableIDMachineGroupDisplay',   108);   
    define('constTableIDMonItemGroupDisplay',   109);   
    define('constTableIDMachineDisplay',        110);   
    define('constTableIDValueMap',      400);   
    define('constTableIDValueMapAdv',   401);   
    define('constTableIDMUMUpdates',    500);   
    define('constTableIDSections',      501);   
    define('constTableIDMUMSections',   502);   
    define('constTableIDReports',       600);   
    define('constTableIDAddSections',   601);   
    define('constTableIDEventSections', 602);   
    define('constTableIDSchedules',     700);   
    define('constTableIDAddSchedules',  701);   
    define('constTableIDAddAssetQueries',   800);   
    define('constTableIDExecSumSections',   900); 

    
    define('constOptionPageSize',               1);
    define('constOptionPageSizeStr',            "1");
    define('constOptionDisplayComplexity',      2);
    define('constOptionDisplayComplexityStr',   "2");
    define('constOptionOptionsComplexity',      3);
    define('constOptionOptionsComplexityStr',   "3");

    
    define('constSortOptionNone',               0);
    define('constSortOptionBoth',               1);

    
    define('constSortSettingNone',              0);
    define('constSortSettingAsc',               1);
    define('constSortSettingDesc',              2);

    
    define('constDispDataNone',                 0);
    define('constDispDataTimestamp',            1);

    
    define('constSelSearchBasic',               0);
    define('constSelSearchExtended',            1);
    define('constSelSearchDate',                2);
    define('constSelSearchQuery',               3);
    define('constSelSearchScrip',               4);

    
    define('constNextAct',   1);
    define('constPrevAct',  -1);
    define('constCancelAct', 0);

    
    define('CFGFRMT_CLIENT',        0);
    define('CFGFRMT_SERVER',        1);
    define('CFGFRMT_SRVGROUP',      2);
    define('CFGFRMT_SRVGROUPADV',   3);
    define('CFGFRMT_SRVGROUPNOMAP', 4);
    define('CFGFRMT_SRVGROUPADVNOMAP', 5);

    
    define('constPageTypeScripConfig',          1);
    define('constPageTypeConfirm1',             2);
    define('constPageTypeConfirm2',             3);

    
    define('constIDMin',  200);
    define('constIDMax',  1999999);

    
    define('constSourceScripConfig',            "0");
    define('constSourceScripGroupConfig',       "1");
    define('constSourceScripGroupAdvConfig',    "2");
    define('constSourceScripRemoteWizard',      "3");
    define('constSourceScripMalwareWizard',     "4");
    define('constSourceScripUpdateWizard',      "5");
    define('constSourceScripFreqWizard',        "6");

    
    define('constMUMIntMachineStart',           1);
    define('constOutputUpdateInt',              3);

    
    define('constScheduleBlank',                1);

    
    define('constReportDisabled',               1);
    define('constReportEnabled',                2);

    
    define('constDataTypeReports',              1);

    
    define('constObjectTypeReport',             1);

    
    define('constSchedFormCreate',              0);
    define('constSchedFormEdit',                1);
    define('constSchedFormView',                2);

    
    define('constServerOptionReptCSS',          'rept_css');
    define('constServerOptionServerURL',        'server_url');

    
    define('constJavaListEventFilters',         0);
    define('constJavaListEventMgrpInclude',     1);
    define('constJavaListEventMgrpExclude',     2);
    define('constJavaListAssetQueries',         3);

    
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

    
    define('constRepfFormPrefix',               'ctrform_');

    
    define('constSectionNameEventConfigUniq',
        '6f8a39ea9f28bf718052e75e5f11e173');
    define('constSectionNameMUMConfigUniq',
        'a4790423ed34921c368c922a0a81b52e');
    define('constSectionNameExecSumConfigUniq',
        '08fff781eae063e35db95473e27fed59');

    
    define('constReportNameConfigUniq', '47ff2696057fc7c5164c845d0f905e6d');

    
    define('constSchedNameConfigUniq', 'ctrsched_name');

    
    define('constJavaListDispositionEdit', 0);
    define('constJavaListDispositionView', 1);

    
    define('constFontSizeClickHere',    '<font size="3">');

    
    define('constAssetQueryTypePerm',   0);
    define('constAssetQueryTypeAdHoc',  1);

    
    define('constOptionEventCode',      0);
    define('constOptionEventCodeStr',   'event_code');

 ?>
