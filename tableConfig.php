<?php



define("OSINFO",1);       define("PROCINFO",2);     define("CHASSISINFO",3);  define("NETINFO",4);      define("SOFTINFO",5);     define("RESOURCEINFO",6); 
$groupArray=array(OSINFO,CHASSISINFO,PROCINFO,NETINFO,SOFTINFO,RESOURCEINFO);



$groupArrayDataMap = array(    
    OSINFO => array('Operating System','NT Product Type','OS Version Number','NT Installed Service Pack'),
    PROCINFO => array('Processor Manufacturer','Processor Version','Processor Type','Registered Vendor'),
    CHASSISINFO => array('System Product','Chassis Type','Chassis Manufacturer','Chassis Serial Number'),    
    NETINFO   => array('IP address','DHCP Server','MAC address','Network Adapter'),
    SOFTINFO   => array('Product Name','Product Version','Installed Software Names','Version'),
    RESOURCEINFO => array('Logical Disk Name','Logical Disk KBytes total','Physical Memory Total (Kbytes)','Logical Disk KBytes free')
);

$multiDrillDown  = array('Microsoft Office','Microsoft Visual Studio','Microsoft Visio','Microsoft Project');

$softwareList1   = array('Microsoft Lync',
                        'Microsoft Visual Studio 2','McAfee AntiVirus Plus','Adobe Reader');

$softwareList2   = array('McAfee AntiVirus Plus','McAfee VirusScan Enterprise','Adobe Acrobat Reader','Norton Antivirus','Avast','Microsoft Office Professional','Kaspersky',
        'Microsoft Office Enterprise 2007','Microsoft Office Professional Plus 2007 ','Microsoft Office Professional Plus 2010','Microsoft Office 2010','Microsoft Office Standard 2007','Microsoft Office Professional Edition 2003','Microsoft Office Standard 2013','Microsoft Office Professional 2007','Microsoft Office Project Professional 2003','Microsoft Office Professional Plus 2007  ','Microsoft Office Standard 2013  ','Microsoft Office Standard 2010  ','Microsoft Office Professional Plus 2013  ','Microsoft Office Professional Plus 2010  ','Microsoft Office Professional Plus 2007','Microsoft Office 365',
    'Microsoft Project Professional 2013','Microsoft Project MUI (English) 2013','Microsoft Project Standard 2010','Microsoft Office Project Standard 2010','Microsoft Office Project MUI (English) 2010',
    'Microsoft Visio Professional 2013','Microsoft Visio MUI (English) 2013','Microsoft Visio Standard 2013','Microsoft Visio Premium 2010','Microsoft Visio Viewer 2010','Symantec Endpoint Protection','Microsoft Office H');