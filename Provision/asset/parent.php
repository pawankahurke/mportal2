<?php

// mysqldump -u weblog asset DataName > dataname.sql

/*
Revision history:

Date        Who     What
----        ---     ----
13-Sep-02   EWB     Creation
20-Sep-02   EWB     Giant Refactoring
24-Sep-02   EWB     Removed obsolete items.
25-Sep-02   EWB     New hierarchy.
26-Sep-02   EWB     Moved Start-Up programs into hierarchy
11-Oct-02   EWB     No more 999s
16-Oct-02   EWB     New Netscape Fields
24-Oct-02   EWB     Reordered the members of several groups.
30-Oct-02   EWB     New Outlook Express fields.
15-Nov-02   EWB     More new fields
15-Nov-02   EWB     'Default Printer' ungroup
15-Nov-02   EWB     'SMTP Reply To Email Address' ungroup
 4-Dec-02   EWB     Reorginization Day
10-Dec-02   EWB     Local Navigation
23-Jan-03   EWB     Added 'location', 'UUID'.
 7-Feb-03   EWB     New 3.1 database scheme.
11-Feb-03   EWB     db_change().
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
19-Mar-03   NL      Move debug_note line below $debug.
 2-May-03   EWB     Asset Group Leader
 6-May-03   EWB     Added 'Monitor'
 6-May-03   EWB     'SMTP Reply To Email Address' parent match nanoheal group.
 7-May-03   EWB     Remove 'start up program' group leader.
 8-May-03   EWB     Reordered outlook express group.
17-Jul-03   EWB     Added new created field to DataName table.
 6-Oct-03   EWB     Added new include field to DataName table.
14-Oct-03   WOH     Added alot of tables to handle new data from dmidecode.
17-Oct-03   EWB     Add log entries for creation of new dataname entries.
17-Oct-03   EWB     Set the 'setbyclient' field from list.
10-Nov-03   WOH     Added arrays to handle more data from WMI
20-Nov-03   WOH     Added three more hardware classes.
 4-Dec-03   WOH     Added two new categories.  Software update, Update file info.
 5-Jan-03   EWB     'Logical Disk Name' becomes group leader.
23-Mar-04   EWB     Many new user account information fields.
26-Apr-04   EWB     Ordered the new user account fields better.
31-Jan-05   WOH     Fixed spelling. 
01-Feb-05   WOH     Fixed spelling again.
08-May-05   AAM     Added names and groups for bug 2709 fix.
 2-Jun-05   BJS     Fixed spelling. Added clientname field.
12-Oct-06   WOH     Made changes for bugzilla #3657

*/

// mysqldump -u root -p hfnlog DataName > dataname.sql

$title = 'Asset Parent';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-head.php');
include('local.php');
include('../lib/l-user.php');
include('../lib/l-rcmd.php');


function find_record($name, $db)
{
    $qname = safe_addslashes($name);
    $sql  = "select * from DataName where";
    $sql .= " clientname = '$qname'";
    $res = command($sql, $db);
    $row = array();
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_array($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $row;
}


/*
    |  Find a dataid record for the specified name.
    |
    |  Returns the record id if we found it, or zero
    |  if the record doesn't exist.
    */

function find_did($name, $db)
{
    $did = 0;
    $row = find_record($name, $db);
    if ($row) {
        $did = $row['dataid'];
    }
    return $did;
}


function change_name($old, $new, $db)
{
    $row = find_record($old, $db);
    if ($row) {
        $did  = $row['dataid'];
        $name = $row['name'];
        if ($name != $new) {
            $qn   = safe_addslashes($new);
            $sql  = "update DataName set\n";
            $sql .= " name = '$qn'\n";
            $sql .= " where dataid = $did";
            redcommand($sql, $db);
            $msg  = "asset: rename '$old' as '$new'";
            logs::log(__FILE__, __LINE__, $msg, 0);
        }
    }
}


function create_name($name)
{
    logs::log(__FILE__, __LINE__, "assets: create: $name", 0);
}


/*
    |  Create a new DataName record.
    |  We always set setbyclient to
    |  zero, since we're building structure.
    */

function create_did($name, $pid, $ord, $now, $db)
{
    $qname = safe_addslashes($name);

    $sql  = "insert into DataName set\n";
    $sql .= " name='$qname',\n";
    $sql .= " parent=$pid,\n";
    $sql .= " ordinal=$ord,\n";
    $sql .= " created=$now,\n";
    $sql .= " clientname='$qname',\n";
    $sql .= " setbyclient=0";
    $res  = redcommand($sql, $db);
    $did  = find_did($name, $db);
    create_name($name);
    return $did;
}


function update_did($did, $pid, $ord, $db)
{
    $sql  = "update DataName set\n";
    $sql .= " parent=$pid,\n";
    $sql .= " ordinal=$ord\n";
    $sql .= " where dataid=$did";
    redcommand($sql, $db);
}


function children($db, $pid, $names, $now)
{
    $ord = 0;
    reset($names);
    foreach ($names as $key => $data) {
        $ord++;
        enter_did($data, $pid, $ord, $now, $db);
    }
}



function enter_did($name, $pid, $ord, $now, $db)
{
    $row = find_record($name, $db);
    if ($row) {
        $update = 0;
        $did  = $row['dataid'];
        $set  = $row['setbyclient'];
        $dord = $row['ordinal'];
        $dpid = $row['parent'];
        $gid  = $row['groups'];
        if ($ord) {
            // if both parent and child
            // it is the child that counts

            if ($ord != $dord) {
                $dord = $ord;
                $update = 1;
            }
        }
        if (($pid != $dpid) && ($pid != 0)) {
            $dpid = $pid;
            $update = 1;
        }
        if ($update) {
            update_did($did, $dpid, $dord, $db);
        }
    } else {
        $did = create_did($name, $pid, $ord, $now, $db);
    }
    return $did;
}


function build($db, $parent, $children, $now)
{
    $pid = enter_did($parent, 0, 0, $now, $db);
    $ord = 0;
    foreach ($children as $key => $data) {
        $ord++;
        enter_did($data, $pid, $ord, $now, $db);
    }
}

function table_data($args)
{
    if ($args) {
        echo "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            $s = fontspeak($data);
            echo "<td>$s</td>\n";
        }
        echo "</tr>\n";
    }
}


function main($db, $now)
{
    $sql  = "update DataName set\n";
    $sql .= " created = $now\n";
    $sql .= " where created = 0";
    redcommand($sql, $db);

    // client group parent, client leaf children

    $p = 'DNS Servers';             // 10
    $c = array('DNS Server');       // 10
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Network Services';        // 64
    $c = array('Network Service');  // 64
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Network Clients';         // 66
    $c = array('Network Client');   // 66
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Network Protocols';        // 62
    $c = array('Network Protocol');  // 62
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Installed Programs';          // 39
    $c = array('Installed program');    // 39
    build($db, $p, $c, $now);


    $p = 'Installed Software Updates';           // client group
    $c = array(
        'Update Category',
        'Update Name',
        'Update Description',
        'Update Installation Date',
        'Update Installed By',
        'Update Type',
        'Update File Changes',
        'Update Uninstall Command',
        'Update File and Version'
    );    // 40
    build($db, $p, $c, $now);


    $p = 'Update File Version Info';           // client group
    $c = array(
        'Update',
        'Update Product Name',
        'Update Process Version',
        'Update File Description',
        'Update Company Name',
        'Update Legal Copyright',
        'Update Product Version',
        'Update Process Size',
        'Update Process Creation Date',
        'Update Process File Name',
        'Update comments'
    );           // 41
    build($db, $p, $c, $now);


    // client group parent, client leaf children

    $p = 'Network Adapters';         // 12
    $c = array(
        'Network Adapter',
        'IP address',
        'Subnet Mask',
        'Default Gateway',
        'DHCP Server',
        'DHCP Subnet Mask',
        'MAC Address'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Network Adapter Information';         // 12a
    $c = array(
        'Adapter Name',
        'Adapter Manufacturer',
        'Adapter AdapterType',
        'Adapter IPAddress',
        'Adapter IPSubnet',
        'Adapter DefaultIPGateway',
        'Adapter DNSServerSearchOrder',
        'Adapter MACAddress',
        'Adapter DHCPEnabled',
        'Adapter DHCPServer',
        'Adapter Driver FileName',
        'Adapter Driver Version',
        'Adapter Driver LastModified'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Software Version Information';   // 137
    $c = array(
        'Product Name',        // 137
        'Product Version',
        'File Description',
        'Company Name',
        'Legal Copyright',
        'Process Version',
        'Process Size',
        'Process Creation Date',
        'Process File Name',
        'Comments'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Software License Information'; // 68
    $c = array(
        'Licensed Product Name',
        'License Number',             // 68
        'Product Key'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'local Drive Information'; // 47
    $c = array(
        'Local Drive',
        'KBytes total',
        'KBytes used',
        'KBytes free',
        'Percentage used',
        'Percentage free'
    );
    build($db, $p, $c, $now);


    // client group parent, client leaf children

    $p = 'Physical Disk Information';
    $c = array(
        'Physical Disk Caption',
        'Physical Disk Description',
        'Physical Disk DeviceID',
        'Physical Disk InterfaceType',
        'Physical Disk Manufacturer',
        'Physical Disk MediaType',
        'Physical Disk Model',
        'Physical Disk Partitions',
        'Physical Disk Status',
        'Physical Disk Serial',
        'Physical Disk Size in Bytes',
        'Physical Disk TotalCylinders',
        'Physical Disk TotalHeads',
        'Physical Disk TotalSectors',
        'Physical Disk BytesPerSector',
        'Physical Disk SectorsPerTrack',
        'Physical Disk TracksPerCylinder',
        'Physical Disk TotalTracks',
        'Physical Disk SCSIBus',
        'Physical Disk SCSILogicalUnit',
        'Physical Disk SCSIPort',
        'Physical Disk SCSITargetId'
    );
    build($db, $p, $c, $now);


    // client group parent, client leaf children

    $p = 'Logical Disk Information'; // 48
    $c = array(
        'Logical Disk Name',
        'Logical Disk KBytes total',
        'Logical Disk KBytes used',
        'Logical Disk KBytes free',
        'Logical Disk Percentage used',
        'Logical Disk Percentage free',
        'Logical Disk Description',
        'Logical Disk VolumeName',
        'Logical Disk FileSystem',
        'Logical Disk ProviderName',
        'Partition Info Name',
        'Logical Disk VolumeSerialNumber',
        'Logical Disk Compressed',
        'Partition Info PrimaryPartition',
        'Partition Info Bootable'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'SCSI Controller Information';
    $c = array(
        'SCSI Controller Name',
        'SCSI Controller Status',
        'SCSI Controller Description',
        'SCSI Controller DriverName',
        'SCSI Controller Manufacturer',
        'Attached Device List'
    );
    build($db, $p, $c, $now);


    // client group parent, client leaf children

    $p = 'CDROM Drive Information';
    $c = array(
        'CDROM Drive Name',
        'CDROM Drive Description',
        'CDROM Drive DriverName',
        'CDROM Drive Status',
        'CDROM Drive Manufacturer',
        'CDROM Drive Driver FileName',
        'CDROM Drive Driver Version',
        'CDROM Drive Driver LastModified'
    );
    build($db, $p, $c, $now);


    // client group parent, client leaf children

    $p = 'Modem Information';
    $c = array(
        'Modem Name',
        'Modem Model',
        'Modem Manufacturer',
        'Modem AttachedTo',
        'Modem DeviceType',
        'Modem Status'
    );
    build($db, $p, $c, $now);
    // client group parent, client leaf children


    $p = 'Sound Card Information';
    $c = array(
        'Sound Card ProductName',
        'Sound Card Manufacturer',
        'Sound Card Description',
        'Sound Card Status',
        'Sound Card Driver FileName',
        'Sound Card Driver LastModified',
        'Sound Card Driver Version'
    );
    build($db, $p, $c, $now);


    // client group parent, client leaf children

    $p = 'Network Drive Information';   // 60
    $c = array('Network Drive');
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Start-up Services';       // 73
    $c = array('Start-up Service'); // 73
    build($db, $p, $c, $now);

    $p = 'Start-up Programs';
    $c = array('Start-up Program');
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'BIOS Characteristics';
    $c = array('Characteristics');
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'BIOS Extended Characteristics 1';
    $c = array('Characteristics 1');
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'BIOS Extended Characteristics 2';
    $c = array('Characteristics 2');
    build($db, $p, $c, $now);


    // client group parent, client leaf children

    $p = 'Socket Information';
    $c = array(
        'Processor Socket Designation',
        'Processor ID',
        'Processor Status',
        'Processor Serial Number',
        'Processor Asset Tag',
        'Processor Part Number'
    );
    build($db, $p, $c, $now);


    // client group parent, client leaf children

    $p = 'Processor Flags';
    $c = array('Flag');
    build($db, $p, $c, $now);


    // client group parent, client leaf children

    $p = 'Error Correcting Capabilities';
    $c = array('Correcting Capabilities');
    build($db, $p, $c, $now);


    // client group parent, client leaf children

    $p = 'Enabled Error Correcting Capabilities';
    $c = array('Enabled Correcting Capabilities');
    build($db, $p, $c, $now);


    // client group parent, client leaf children

    $p = 'Memory Module Voltage';
    $c = array(
        'Voltage',   /* Obsoleted by bug 2709 fix */
        'Supported Memory Module Voltage'
    );
    build($db, $p, $c, $now);

    /* Added for bug 2709 */
    // client group parent, client leaf children

    $p = 'Processor Voltage';
    $c = array('Supported Processor Voltage');
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Supported Memory Speeds';
    $c = array('Speed');
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Supported Memory Types';
    $c = array('Memory Types');
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Memory Module Information';
    $c = array(
        'Bank Connections',
        'Current Speed in ns',
        'Enabled Size in MB',
        'Error Status',
        'Installed Size in MB',
        'Socket Designation',
        'Type'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Memory Slot Addresses';
    $c = array('Address');
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Cache Information';
    $c = array(
        'Associativity',
        'Cache Error Correction Type',
        'Cache Location',
        'Cache Socket Designation',
        'Installed Size in kB',
        'Installed SRAM Type',
        'Maximum Size in kB',
        'Operational Mode',
        'Speed in ns',
        'Supported SRAM Types',
        'System Type'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Port Connector Information';
    $c = array(
        'Internal Reference Designator',
        'Internal Connector Type',
        'External Reference Designator',
        'External Connector Type',
        'Port Type'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'System Slot Information';
    $c = array(
        'Slot Designation',
        'Slot Type',
        'Current Usage',
        'Slot Length',
        'Slot ID',
        'Slot Characteristics'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'On Board Device Information';
    $c = array(
        'Device Type',
        'Device Description',
        'Device Status'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'OEM String Information';
    $c = array('OEM String');
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'System Configuration Options';
    $c = array('Options');
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Memory Array Mapped Address';
    $c = array(
        'Array Starting Address',
        'Array Ending Address',
        'Array Range Size',
        'Physical Array Handle',
        'Partition Width'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Memory Device Mapped Address';
    $c = array(
        'Device Starting Address',
        'Device Ending Address',
        'Device Range Size',
        'Physical Device Handle',
        'Memory Array Mapped Address Handle',
        'Partition Row Position',
        'Interleave Position',
        'Interleaved Data Depth'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Language Information';
    $c = array('Language');
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Associations';
    $c = array('Groups');
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Log Type Descriptor';
    $c = array(
        'Event Descriptor',
        'Event Data Format'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Memory Channel Devices';
    $c = array(
        'Load',
        'Handle'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'System Power Supply';
    $c = array(
        'Power Unit Group',
        'Power Supply Location',
        'Power Supply Name',
        'Power Supply Manufacturer',
        'Power Supply Serial Number',
        'Power Supply Asset Tag',
        'Model Part Number',
        'Power Supply Revision',
        'Max Power Capacity in W',
        'Power Supply Status',
        'Power Supply Type',
        'Input Voltage Range Switching',
        'Plugged',
        'Hot Replaceable',
        'Input Voltage Probe Handle',
        'Cooling Device Handle',
        'Input Current Probe Handle'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'User Account Information';
    $c = array(
        'User Account Name',
        'User Account Privileges',
        'User Account SuccessfulLogons',
        'User Account Description',
        'User Account FullName',
        'User Account AccountExpirationTime',
        'User Account Caption',
        'User Account Disabled',
        'User Account Domain',
        'User Account FailedLogons',
        'User Account Groups',
        'User Account HomeDirPath',
        'User Account LocalAccount',
        'User Account Lockout',
        'User Account LogonAllowedWorkstations',
        'User Account LogonServerName',
        'User Account PasswordAge',
        'User Account PasswordChangeable',
        'User Account PasswordExpires',
        'User Account PasswordRequired',
        'User Account UserDiskStorageSizeLimit'
    );
    build($db, $p, $c, $now);





    // client group parent, client leaf children

    $p = 'Monitor Information'; // client group
    $c = array(
        'Monitor Model',
        'Monitor Description',
        'Monitor Mfg',
        'Monitor Serial Number'
    );
    build($db, $p, $c, $now);


    // client group parent, client leaf children

    $p = 'Video Controller Information'; // client group
    $c = array(
        'Video Controller Name',
        'Video Controller Status',
        'Video Controller Description',
        'Video Controller AdapterCompatibility',
        'Video Controller AdapterDACType',
        'Video Controller Adapter RAM in MB',
        'Video Controller Caption',
        'Video Controller InstalledDisplayDrivers',
        'Video Controller DriverVersion',
        'Video Controller DriverDate',
        'Video Controller VideoModeDescription',
        'Video Controller CurrentRefreshRate',
        'Video Controller VideoProcessor'
    );
    build($db, $p, $c, $now);


    // client group parent, client leaf children

    $p = 'Printer Information';     // 1
    $c = array(
        'Printer Name',
        'Printer Comment',
        'Printer Default',
        'Printer Local',
        'Printer Location',
        'Printer Network',
        'Printer PortName',
        'Printer PrintJobDataType',
        'Printer PrintProcessor',
        'Printer Shared',
        'Printer ShareName',
        'Printer SystemName',
        'Printer Driver FileName',
        'Printer Driver LastModified',
        'Printer Driver Version',
        'Printer OEMUrl',
        'Name',
        'Printer Driver',
        'Port'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Netscape Information';    // 118
    $c = array(
        'Netscape Current User',
        'Netscape Username',
        'Netscape Account name',
        'Netscape User name',
        'Netscape POP3 User name',
        'Netscape POP3 Server',
        'Netscape SMTP User name',
        'Netscape SMTP Server',
        'Netscape Proxy server address',
        'Netscape Proxy port',
        'Netscape Proxy execeptions',
        'Netscape Proxy configuration'
    );
    build($db, $p, $c, $now);

    // client group parent, client leaf children

    $p = 'Pegasus Mail Information';        // client group
    $c = array(
        'Pegasus Current User',
        'Pegasus Version',
        'Pegasus Identity',
        'Pegasus Account Name',
        'Pegasus User Name',
        'Pegasus POP3 Server',
        'Pegasus POP3 Port',
        'Pegasus SMTP Server',
        'Pegasus SMTP Port',
        'Pegasus User',
        'Current User',
        'User',
        'Version'
    );
    build($db, $p, $c, $now);

    $p = 'Outlook Express General';
    $c = array(
        'OE Product Name',
        'OE Product Version',
        'OE File Description',
        'OE Company Name',
        'OE Legal Copyright',
        'OE Process Version',
        'OE Process Size',
        'OE Process Creation Date',
        'OE Process File Name'
    );
    build($db, $p, $c, $now);

    $p = 'Outlook Express Information';
    $c = array(
        'Outlook Express Account Name',
        'Outlook Express User Name',
        'OE Default LDAP account',
        'OE Default mail account',
        'OE Default News Account'
    );
    build($db, $p, $c, $now);


    $p = 'Outlook Express Mail Accounts';
    $c = array(
        'OE Mail Account Name',
        'OE SMTP Port',
        'OE SMTP Server',
        'OE SMTP Email Address',
        'OE SMTP Reply To Email Address',
        'OE SMTP Display Name',
        'OE POP3 Server',
        'OE POP3 Port',
        'OE POP3 User Name'
    );
    build($db, $p, $c, $now);


    // 'SMTP Reply To Email Address', nanoheal has as group member, hollis does not.

    // client group parent, client leaf children

    $p = 'Outlook Information';
    $c = array(
        'Outlook User Name',
        'Outlook Account Name',
        'POP3 User Name',
        'POP3 Server',
        'POP3 Port',
        'Default mail account',
        'Default News Account',
        'Default LDAP account',
        'SMTP Email Address',
        'SMTP Reply To Email Address', // special
        'SMTP Display Name',
        'SMTP Server',
        'SMTP Port'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Identification';
    $c = array(
        'System Manufacturer',
        'System Product',
        'System Service Tag',
        'System Version',
        'Case Serial Number',
        'Case Type',
        'System Serial Number',
        'Asset Tag',
        'Registered Service Tag',
        'Registered System Model',
        'Registered System Manufacturer',
        'System UUID',
        'System Wake-up Type',
        'BIOS Vendor',                      /* 3.3.1 BIOS Information */
        'BIOS Date',
        'BIOS Version',
        'BIOS Address',
        'BIOS ROM Size In kB',
        'BIOS Characteristics',              // Client group
        'BIOS Extended Characteristics 1',   // Client group
        'BIOS Extended Characteristics 2'
    );  // Client group
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'BIOS Language Information';
    $c = array(
        'Installable Languages',
        'Language Information', // Client group
        'Currently Installed Language'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Group Associations';
    $c = array(
        'Group Name',
        'Group Items',
        'Associations'
    ); // Client group
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'System Event Log';
    $c = array(
        'Area Length',
        'Area Length in bytes',
        'Header Start Offset',
        'Header Length in bytes',
        'Data Start Offset',
        'Access Method',
        'Access Address',
        'Event Log Status',
        'Change Token',
        'Header Format',
        'Supported Log Type Descriptors',
        'Log Type Descriptor'
    );  // client group
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Physical Memory Array';
    $c = array(
        'Memory Array Location',
        'Memory Array Use',
        'Memory Error Correction Type',
        'Maximum Capacity',
        'Maximum Capacity in GB',
        'Maximum Capacity in MB',
        'Maximum Capacity in kB',
        'Array Error Information Handle',
        'Number Of Devices'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Memory Device';
    $c = array(
        'Array Handle',
        'Memory Error Information Handle',
        'Total Width in bits',
        'Data Width in bits',
        'Memory Size',
        'Form Factor',
        'Set',
        'Locator',
        'Bank Locator',
        'Memory Device Type',
        'Device Type Detail',
        'Device Speed',
        'Memory Manufacturer',
        'Memory Serial Number',
        'Memory Asset Tag',
        'Memory Part Number'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = '32-bit Memory Error Information';
    $c = array(
        '32-bit Type',
        '32-bit Granularity',
        '32-bit Operation',
        '32-bit Vendor Syndrome',
        '32-bit Memory Array Address',
        '32-bit Device Address',
        '32-bit Resolution'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Built-in Pointing Device';
    $c = array(
        'Pointing Device Type',
        'Interface',
        'Ponting Device Interface',
        'Buttons'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Portable Battery';
    $c = array(
        'Battery Location',
        'Battery Manufacturer',
        'Manufacture Date',
        'Serial Number Battery',
        'Battery Name',
        'Chemistry',
        'Design Capacity in mWh',
        'Design Voltage in mV',
        'SBDS Version',
        'Maximum Error',
        'SBDS Serial Number',
        'SBDS Manufacture Date',
        'SBDS Chemistry',
        'Battery OEM-specific Information'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'System Reset';
    $c = array(
        'Reset Status',
        'Watchdog Timer',
        'Boot Option',
        'Boot Option On Limit',
        'Reset Count',
        'Reset Limit',
        'Timer Interval in Min',
        'Timeout in Min'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Hardware Security';
    $c = array(
        'Power-On Password Status',
        'Keyboard Password Status',
        'Administrator Password Status',
        'Front Panel Reset Status'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'System Power Controls';
    $c = array(
        'Next Scheduled Power-on'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Voltage Probe';
    $c = array(
        'Voltage Probe Description',
        'Voltage Probe Location',
        'Voltage Probe Status',
        'Maximum Value in V',
        'Minimum Value in V',
        'Resolution in mV',
        'Tolerance in V',
        'Voltage Probe Accuracy',
        'Voltage Probe OEM-specific Information',
        'Nominal Value in V'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Cooling Device';
    $c = array(
        'Temperature Probe Handle',
        'Cooling Device Type',
        'Cooling Device Status',
        'Cooling Unit Group',
        'Cooling Device OEM-specific Information',
        'Nominal Speed in rpm'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Temperature Probe';
    $c = array(
        'Temperature Probe Description',
        'Temperature Probe Location',
        'Temperature Probe Status',
        'Maximum Value in deg C',
        'Minimum Value in deg C',
        'Resolution in deg C',
        'Tolerance in deg C',
        'Temperature Probe Accuracy',
        'Temperature Probe OEM-specific Information',
        'Nominal Value in deg C'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Electrical Current Probe';
    $c = array(
        'Electrical Current Probe Description',
        'Electrical Current Probe Location',
        'Electrical Current Probe Status',
        'Maximum Value in A',
        'Minimum Value in A',
        'Resolution in mA',
        'Tolerance in A',
        'Electrical Current Probe Accuracy',
        'Electrical Current Probe OEM-specific Information',
        'Nominal Value in A'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Out-of-band Remote Access';
    $c = array(
        'Manufacturer Name',
        'Inbound Connection',
        'Outbound Connection'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'System Boot Information';
    $c = array(
        'System Boot Status'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = '64-bit Memory Error Information';
    $c = array(
        '64-bit Type',
        '64-bit Granularity',
        '64-bit Operation',
        '64-bit Vendor Syndrome',
        '64-bit Memory Array Address',
        '64-bit Device Address',
        '64-bit Resolution'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Management Device';
    $c = array(
        'Management Device Description',
        'Management Device Type',
        'Management Device Address',
        'Address Type'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Management Device Component';
    $c = array(
        'Management Device Component Description',
        'Management Device Handle',
        'Component Handle',
        'Threshold Handle'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Management Device Threshold Data';
    $c = array(
        'Lower Non-critical Threshold',
        'Upper Non-critical Threshold',
        'Lower Critical Threshold',
        'Upper Critical Threshold',
        'Lower Non-recoverable Threshold',
        'Upper Non-recoverable Threshold'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Memory Channel';
    $c = array(
        'Memory Channel Type',
        'Maximal Load',
        'Memory Channel Devices'
    ); //client group
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'IPMI Device Information';
    $c = array(
        'Interface Type',
        'Specification Version',
        'I2C Slave Address',
        'NV Storage Device Address',
        'NV Storage Device',
        'Base Address',
        'Register Spacing',
        'Interrupt Polarity',
        'Interrupt Trigger Mode',
        'Interrupt Number'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Eudora Information';
    $c = array(
        'Eudora Product Name',
        'Eudora Product Version',
        'Eudora File Description',
        'Eudora Company Name',
        'Eudora Legal Copyright',
        'Eudora Process Version',
        'Eudora Process Size',
        'Eudora Process Creation Date',
        'Eudora Process File Name'
    );
    build($db, $p, $c, $now);

    //  server parent, client leaf children

    $p = 'Netscape General';
    $c = array(
        'Netscape Product Name',
        'Netscape Product Version',
        'Netscape File Description',
        'Netscape Company Name',
        'Netscape Legal Copyright',
        'Netscape Process Version',
        'Netscape Process Size',
        'Netscape Process Creation Date',
        'Netscape Process File Name'
    );
    build($db, $p, $c, $now);

    $p = 'Outlook General';
    $c = array(
        'Outlook Product Name',
        'Outlook Product Version',
        'Outlook File Description',
        'Outlook Company Name',
        'Outlook Legal Copyright',
        'Outlook Process Version',
        'Outlook Process Size',
        'Outlook Process Creation Date',
        'Outlook Process File Name'
    );

    build($db, $p, $c, $now);

    $p = 'Pegasus General';
    $c = array(
        'Pegasus Product Name',
        'Pegasus Product Version',
        'Pegasus File Description',
        'Pegasus Company Name',
        'Pegasus Legal Copyright',
        'Pegasus Process Version',
        'Pegasus Process Size',
        'Pegasus Process Creation Date',
        'Pegasus Process File Name'
    );

    build($db, $p, $c, $now);


    $p = 'Operating System Information';
    $c = array(
        'Operating System',
        'OS Version Number',
        'NT Installed Service Pack',
        'NT Product Type'
    );
    build($db, $p, $c, $now);


    // server parent, client leaf children

    $p = 'Base Board Information';  // server
    $c = array(
        'Base Board Manufacturer',
        'Base Board Product',
        'Base Board Version',
        'Serial Number',
        'Base Board Asset Tag',
        'Location In Chassis',
        'Base Board Location In Chassis',
        'Base Board Chassis Handle',
        'Base Board Type'
    );
    build($db, $p, $c, $now);

    // server parent, client leaf children

    $p = 'Chassis Information';  // server
    $c = array(
        'Chassis Manufacturer',
        'Chassis Type',
        'Chassis Lock',
        'Chassis Version',
        'Chassis Serial Number',
        'Chassis Asset Tag',
        'Chassis Boot-up State',
        'Chassis Power Supply State',
        'Chassis Thermal State',
        'Chassis Security Status',
        'Chassis OEM Information',
        'Chassis Height',
        'Chassis Number Of Power Cords'
    );
    build($db, $p, $c, $now);


    //  server parent, client leaf children

    $p = 'Processor Information';
    $c = array(
        'Socket Information',  // client group
        'Processor Type',
        'Processor Flags',     // client group
        'Processor Version',
        'Processor Voltage',     /* Obsoleted by bug 2709 fix */
        'Processor Current Voltage',
        'Processor Family',
        'Registered Processor',
        'Registered Vendor',
        'Processor Manufacturer',
        'Processor External Clock in MHz',
        'Processor Max Speed in MHz',
        'Processor Current Speed in MHz',
        'Processor Upgrade',
        'Processor L1 Cache Handle',
        'Processor L2 Cache Handle',
        'Processor L3 Cache Handle',
        'Processor CurSpeed in Megahertz',
        'Processor MaxSpeed in Megahertz'
    );
    build($db, $p, $c, $now);


    //  server parent, client leaf children

    $p = 'Memory Controller Information';
    $c = array(
        'Memory Error Detecting Method',
        'Error Correcting Capabilities',  // client group
        'Memory Supported Interleave',
        'Memory Current Interleave',
        'Maximum Memory Module Size in MB',
        'Maximum Total Memory Size in MB',
        'Supported Memory Speeds',  // client group
        'Supported Memory Types',  // client group
        'Memory Module Voltage',  // client group -obsoleted by 2709
        'Memory Module Current Voltage',
        'Associated Memory Slots',
        'Memory Slot Addresses',  // client group
        'Enabled Error Correcting Capabilities'
    ); // client group
    build($db, $p, $c, $now);


    // server parent, client leaf children

    $p = 'IE Information';  // server
    $c = array(
        'IE Product Name',
        'IE Product Version',    // client leaf
        'IE File Description',
        'IE Company Name',
        'IE Legal Copyright',
        'IE Process Version',
        'IE Process Size',
        'IE Process Creation Date',
        'IE Process File Name',
        'IE Proxy',
        'IE Proxy addres',      // client spelling!
        'IE Proxy Address',
        'IE Proxy Exceptions',
        'IE Build'
    );
    build($db, $p, $c, $now);


    // server parent, client leaf children

    $p = 'General';
    $c = array('Workgroup', 'Host', 'Domain');
    build($db, $p, $c, $now);


    //  server parent, client leaf children

    $p = 'Properties';      // server
    $c = array(
        'Machine Name',  // client
        'UUID',          // client
        'Site Name',     // fake client
        'User Name',     // client
        'Monitor',       // client
        'location',      // client
        'Physical Memory Total (Kbytes)', // client
        'Time Zone',
        'Default Printer'
    );    // client
    build($db, $p, $c, $now);


    // server parent, client group children

    $p = 'Storage';
    $c = array(
        'Physical Disk Information', //client group
        'Logical Disk Information', //client group
        'Network Drive Information',
        'Local Drive Information',
        'SCSI Controller Information'
    ); //client group
    build($db, $p, $c, $now);


    // server parent, mostly client group children

    $p = 'Network';             // server parent
    $c = array(
        'General',       // server parent
        'DNS Servers',           // g 10
        'Network Clients',       // g 66
        'Network Services',      // g 64
        'Network Protocols',     // g 62
        'Network Adapters',     // g 12
        'Network Adapter Information'
    ); //g 12a
    build($db, $p, $c, $now);

    // root server parent, mixed children

    $p = 'System Summary';                  // server parent
    $c = array(
        'Identification',            // server parent
        'Operating System Information',
        'Base Board Information',
        'Chassis Information',
        'Processor Information',
        'Memory Controller Information',
        'Memory Module Information',     // client group
        'Cache Information',             // client group
        'Port Connector Information',    // client group
        'System Slot Information',       // client group
        'On Board Device Information',
        'OEM String Information',        // client group
        'System Configuration Options',  // client group
        'BIOS Language Information',
        'Group Associations',
        'System Event Log',
        'Physical Memory Array',
        'Memory Device',                 // client group
        '32-bit Memory Error Information',
        'Memory Array Mapped Address',   // client group
        'Memory Device Mapped Address',  // client group
        'Built-in Pointing Device',
        'Portable Battery',
        'System Reset',
        'Hardware Security',
        'System Power Controls',
        'Voltage Probe',
        'Cooling Device',
        'Temperature Probe',
        'Electrical Current Probe',
        'Out-of-band Remote Access',
        'System Boot Information',
        '64-bit Memory Error Information',
        'Management Device',
        'Management Device Component',
        'Management Device Threshold Data',
        'Memory Channel',
        'IPMI Device Information',
        'System Power Supply',
        'User Account Information',      // client group
        'Start-up Services',                 // client group
        'Start-up Programs',                 // client group
        'Properties'
    );                       // server parent
    build($db, $p, $c, $now);


    // root server parent, mixed children

    $p = 'Component';       // server parent
    $c = array(
        'Monitor Information', // client group
        'Video Controller Information', // client group
        'Network',   // server parent
        'Storage',   // server parent
        'CDROM Drive Information', //client parent
        'Modem Information', //client group
        'Sound Card Information', //client group
        'Printer Information'
    );  // client group
    build($db, $p, $c, $now);


    // root server parent, mixed children

    $p = 'Software';        // server root parent
    $c = array(
        'Software Version Information',  // client group
        'Software License Information',  // client group
        'Installed Programs',            // client group
        'Installed Software Updates',    // client group
        'Update File Version Info',      // client group
        'Application Configuration Information'
    );  // server parent
    build($db, $p, $c, $now);


    // server parent, mixed children

    $p = 'Application Configuration Information';            // server parent
    $c = array(
        'Netscape Information',  // client group
        'Netscape General',      // server parent
        'Outlook Information',          // client group
        'Outlook General',      // server parent
        'Outlook Express General',      // server parent
        'Outlook Express Information',  // client group
        'Outlook Express Mail Accounts', // client group
        'IE Information',             // server parent
        'Eudora Information',         // server parent
        'Pegasus Mail Information',    // client group
        'Pegasus General'
    );            // server parent

    build($db, $p, $c, $now);

    // root of tree

    $c = array('System Summary', 'Component', 'Software');
    children($db, 0, $c, $now);

    /*
        |   Want to leave this one as last, if it
        |   exists at all.  We cannot remove from
        |   dataname ... so we just put things
        |   here that got inserted by mistake.
        */

    /*
        $p = 'Obsolete';            // server
        $c = array('Start-up Programs', // server
                   'Start-up Program'); // server
        build($db,$p,$c,$now);
    */
}



function build_clients($list, $now, $db)
{
    reset($list);
    foreach ($list as $k => $name) {
        $row = find_record($name, $db);
        if ($row) {
            $did = $row['dataid'];
            $set = $row['setbyclient'];
            if ($set < 1) {
                $sql  = "update DataName set\n";
                $sql .= " setbyclient = 1\n";
                $sql .= " where dataid = $did";
                redcommand($sql, $db);
            }
        } else {
            debug_note("name: $name does not exist");
            $qname = safe_addslashes($name);
            $sql  = "insert into DataName set\n";
            $sql .= " name = '$qname',\n";
            $sql .= " created = $now,\n";
            $sql .= " clientname = '$qname',\n";
            $sql .= " setbyclient=1";
            redcommand($sql, $db);
            create_name($name);
        }
    }
}


function build_leaders($list, $db)
{
    $sql = "update DataName set leader = 0";
    //  redcommand($sql,$db);

    reset($list);
    foreach ($list as $k => $name) {
        $row = find_record($name, $db);
        if ($row) {
            $did = $row['dataid'];
            $ldr = $row['leader'];
            if ($ldr < 1) {
                $sql  = "update DataName\n";
                $sql .= " set leader = 1\n";
                $sql .= " where dataid=$did";
                redcommand($sql, $db);
            }
        }
    }
}


function build_include($list, $db)
{
    $sql = "update DataName set leader = 0";
    //  redcommand($sql,$db);

    reset($list);
    foreach ($list as $k => $name) {
        $row = find_record($name, $db);
        if ($row) {
            $did = $row['dataid'];
            $inc = $row['include'];
            if ($inc < 1) {
                $sql  = "update DataName\n";
                $sql .= " set include = 1\n";
                $sql .= " where dataid = $did";
                redcommand($sql, $db);
            }
        }
    }
}


function count_dataname($db)
{
    $num = 0;
    $sql = "select count(*) from DataName";
    $res = redcommand($sql, $db);
    if ($res) {
        $num = mysqli_result($res, 0);
    }
    debug_note("DataName contains $num records");
    return $num;
}

function count_server($db)
{
    $num = 0;
    $sql = "select dataid from DataName where setbyclient=0";
    $res = redcommand($sql, $db);
    if ($res) {
        $num = mysqli_num_rows($res);
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    debug_note("DataName contains $num server records");
    return $num;
}

function draw_table($sql, $db)
{
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            $head = explode('|', 'did|name|pid|ord|gid|ldr|inc|set');

            echo "<table bgcolor='wheat' border='2'
                      align='left' cellspaceing='2' cellpadding='2'>\n";
            table_data($head);
            while ($row = mysqli_fetch_array($res)) {
                $name = $row['name'];
                $did  = $row['dataid'];
                $pid  = $row['parent'];
                $ord  = $row['ordinal'];
                $gid  = $row['groups'];
                $ldr  = $row['leader'];
                $inc  = $row['include'];
                $set  = $row['setbyclient'];

                $list = array($did, $name, $pid, $ord, $gid, $ldr, $inc, $set);

                table_data($list);
            }
            echo "</table>";
            echo "<br clear='all'>\n";
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
}




/*
    |
    |  list generated on 10/17/2003 7:36 PM.
    |
    */


$clients = array(
    '32-bit Device Address',
    '32-bit Granularity',
    '32-bit Memory Array Address',
    '32-bit Operation',
    '32-bit Resolution',
    '32-bit Type',
    '32-bit Vendor Syndrome',
    '64-bit Device Address',
    '64-bit Granularity',
    '64-bit Memory Array Address',
    '64-bit Operation',
    '64-bit Resolution',
    '64-bit Type',
    '64-bit Vendor Syndrome',
    'Access Address',
    'Access Method',
    'Adapter AdapterType',
    'Adapter DefaultIPGateway',
    'Adapter DHCPEnabled',
    'Adapter DHCPServer',
    'Adapter DNSServerSearchOrder',
    'Adapter Driver FileName',
    'Adapter Driver Version',
    'Adapter Driver LastModified',
    'Adapter IPAddress',
    'Adapter IPSubnet',
    'Adapter MACAddress',
    'Adapter Manufacturer',
    'Adapter Name',
    'Address',
    'Address Type',
    'Administrator Password Status',
    'Area Length',
    'Area Length in bytes',
    'Array Ending Address',
    'Array Error Information Handle',
    'Array Handle',
    'Array Range Size',
    'Array Starting Address',
    'Asset Tag',
    'Associated Memory Slots',
    'Associations',
    'Associativity',
    'Attached Device List',
    'Bank Connections',
    'Bank Locator',
    'Base Address',
    'Base Board Asset Tag',
    'Base Board Chassis Handle',
    'Base Board Location In Chassis',
    'Base Board Manufacturer',
    'Base Board Product',
    'Base Board Type',
    'Base Board Version',
    'Battery Location',
    'Battery Manufacturer',
    'Battery Name',
    'Battery OEM-specific Information',
    'BIOS Date',
    'BIOS Vendor',
    'BIOS Version',
    'Boot Option',
    'Boot Option On Limit',
    'Buttons',
    'Cache Error Correction Type',
    'Cache Information',
    'Cache Location',
    'Cache Socket Designation',
    'Case Serial Number',
    'Case Type',
    'CDROM Drive Name',
    'CDROM Drive Description',
    'CDROM Drive DriverName',
    'CDROM Drive Information',
    'CDROM Drive Status',
    'CDROM Drive Manufacturer',
    'CDROM Drive Driver FileName',
    'CDROM Drive Driver Version',
    'CDROM Drive Driver LastModified',
    'Change Token',
    'Chassis Asset Tag',
    'Chassis Boot-up State',
    'Chassis Height',
    'Chassis Lock',
    'Chassis Manufacturer',
    'Chassis Number Of Power Cords',
    'Chassis OEM Information',
    'Chassis Power Supply State',
    'Chassis Security Status',
    'Chassis Serial Number',
    'Chassis Thermal State',
    'Chassis Type',
    'Chassis Version',
    'Chemistry',
    'Comments',
    'Company Name',
    'Cooling Device Handle',
    'Cooling Device OEM-specific Information',
    'Cooling Device Status',
    'Cooling Device Type',
    'Cooling Unit Group',
    'Correcting Capabilities',
    'Currently Installed Language',
    'Current Speed in ns',
    'Current Usage',
    'Current User',
    'Data Start Offset',
    'Data Width in bits',
    'Default Gateway',
    'Default LDAP account',
    'Default mail account',
    'Default News Account',
    'Default Printer',
    'Design Capacity in mWh',
    'Design Voltage in mV',
    'Device Description',
    'Device Ending Address',
    'Device Range Size',
    'Device Speed',
    'Device Starting Address',
    'Device Status',
    'Device Type',
    'Device Type Detail',
    'DHCP Server',
    'DHCP Subnet Mask',
    'DNS Server',
    'DNS Servers',
    'Domain',
    'Electrical Current Probe Accuracy',
    'Electrical Current Probe Description',
    'Electrical Current Probe Location',
    'Electrical Current Probe OEM-specific Information',
    'Electrical Current Probe Status',
    'Enabled Correcting Capabilities',
    'Enabled Error Correcting Capabilities',
    'Enabled Size in MB',
    'Error Correcting Capabilities',
    'Error Status',
    'Eudora Company Name',
    'Eudora File Description',
    'Eudora Legal Copyright',
    'Eudora Process Creation Date',
    'Eudora Process File Name',
    'Eudora Process Size',
    'Eudora Process Version',
    'Eudora Product Name',
    'Eudora Product Version',
    'Event Data Format',
    'Event Descriptor',
    'Event Log Status',
    'External Connector Type',
    'External Reference Designator',
    'File Description',
    'Flag',
    'Form Factor',
    'Front Panel Reset Status',
    'Group Items',
    'Group Name',
    'Groups',
    'Handle',
    'Header Format',
    'Header Length in bytes',
    'Header Start Offset',
    'Host',
    'Hot Replaceable',
    'I2C Slave Address',
    'IE Build',
    'IE Company Name',
    'IE File Description',
    'IE Legal Copyright',
    'IE Process Creation Date',
    'IE Process File Name',
    'IE Process Size',
    'IE Process Version',
    'IE Product Name',
    'IE Product Version',
    'IE Proxy',
    'IE Proxy addres',
    'IE Proxy Exceptions',
    'Inbound Connection',
    'Input Current Probe Handle',
    'Input Voltage Probe Handle',
    'Input Voltage Range Switching',
    'Installable Languages',
    'Installed Program',
    'Installed Programs',
    'Installed Size in kB',
    'Installed Size in MB',
    'Installed SRAM Type',
    'Interface',
    'Interface Type',
    'Interleaved Data Depth',
    'Interleave Position',
    'Internal Connector Type',
    'Internal Reference Designator',
    'Interrupt Number',
    'Interrupt Polarity',
    'Interrupt Trigger Mode',
    'IP address',
    'KBytes Free',
    'KBytes Total',
    'KBytes Used',
    'Keyboard Password Status',
    'Language',
    'Language Information',
    'Legal Copyright',
    'Licensed Product Name',
    'License Number',
    'Load',
    'Local Drive',
    'Local Drive Information',
    'location',
    'Location In Chassis',
    'Locator',
    'Log Type Descriptor',
    'Logical Disk Compressed',
    'Logical Disk Description',
    'Logical Disk FileSystem',
    'Logical Disk Information',
    'Logical Disk KBytes total',
    'Logical Disk KBytes used',
    'Logical Disk KBytes free',
    'Logical Disk Name',
    'Logical Disk ProviderName',
    'Logical Disk Percentage used',
    'Logical Disk Percentage free',
    'Logical Disk VolumeName',
    'Logical Disk VolumeSerialNumber',
    'Lower Critical Threshold',
    'Lower Non-critical Threshold',
    'Lower Non-recoverable Threshold',
    'MAC address',
    'Machine Name',
    'Management Device Address',
    'Management Device Description',
    'Management Device Type',
    'Manufacture Date',
    'Manufacturer Name',
    'Maximal Load',
    'Maximum Capacity',
    'Maximum Capacity in GB',
    'Maximum Capacity in kB',
    'Maximum Capacity in MB',
    'Maximum Error',
    'Maximum Memory Module Size in MB',
    'Maximum Size in kB',
    'Maximum Total Memory Size in MB',
    'Maximum Value in A',
    'Maximum Value in deg C',
    'Maximum Value in V',
    'Max Power Capacity in W',
    'Memory Array Location',
    'Memory Array Mapped Address',
    'Memory Array Mapped Address Handle',
    'Memory Array Use',
    'Memory Asset Tag',
    'Memory Channel Devices',
    'Memory Channel Type',
    'Memory Current Interleave',
    'Memory Device',
    'Memory Device Mapped Address',
    'Memory Device Type',
    'Memory Error Correction Type',
    'Memory Error Detecting Method',
    'Memory Error Information Handle',
    'Memory Manufacturer',
    'Memory Module Current Voltage',        /* bug 2709 */
    'Memory Module Information',
    'Memory Module Voltage',
    'Memory Part Number',
    'Memory Serial Number',
    'Memory Size',
    'Memory Slot Addresses',
    'Memory Supported Interleave',
    'Memory Types',
    'Minimum Value in A',
    'Minimum Value in deg C',
    'Minimum Value in V',
    'Model Part Number',
    'Modem AttachedTo',
    'Modem DeviceType',
    'Modem Information',
    'Modem Manufacturer',
    'Modem Model',
    'Modem Name',
    'Modem Status',
    'Monitor',
    'Monitor Information',
    'Monitor Description',
    'Monitor Mfg',
    'Monitor Model',
    'Monitor Serial Number',
    'Name',
    'Netscape Account name',
    'Netscape Company Name',
    'Netscape Current User',
    'Netscape File Description',
    'Netscape Information',
    'Netscape Legal Copyright',
    'Netscape POP3 Server',
    'Netscape POP3 User name',
    'Netscape Process Creation Date',
    'Netscape Process File Name',
    'Netscape Process Size',
    'Netscape Process Version',
    'Netscape Product Name',
    'Netscape Product Version',
    'Netscape Proxy configuration',
    'Netscape Proxy execeptions',
    'Netscape Proxy port',
    'Netscape Proxy server address',
    'Netscape SMTP Server',
    'Netscape SMTP User name',
    'Netscape Username',
    'Netscape User name',
    'Network Adapter',
    'Network Adapter Information',
    'Network Adapters',
    'Network Client',
    'Network Clients',
    'Network Drive',
    'Network Drive Information',
    'Network Protocol',
    'Network Protocols',
    'Network Service',
    'Network Services',
    'Next Scheduled Power-on',
    'Nominal Speed in rpm',
    'Nominal Value in A',
    'Nominal Value in deg C',
    'Nominal Value in V',
    'NT Installed Service Pack',
    'NT Product Type',
    'Number Of Devices',
    'NV Storage Device',
    'NV Storage Device Address',
    'OE Company Name',
    'OE Default LDAP account',
    'OE Default mail account',
    'OE Default News Account',
    'OE File Description',
    'OE Legal Copyright',
    'OEM String',
    'OEM String Information',
    'OE POP3 Server',
    'OE POP3 User Name',
    'OE Process Creation Date',
    'OE Process File Name',
    'OE Process Size',
    'OE Process Version',
    'OE Product Name',
    'OE Product Version',
    'OE SMTP Display Name',
    'OE SMTP Email Address',
    'OE SMTP Reply To Email Address',
    'OE SMTP Server',
    'Operating System',
    'Operational Mode',
    'Options',
    'OS Version Number',
    'Outbound Connection',
    'Outlook Account Name',
    'Outlook Company Name',
    'Outlook Express Account Name',
    'Outlook Express Information',
    'Outlook Express Mail Accounts',
    'Outlook Express User Name',
    'Outlook File Description',
    'Outlook Information',
    'Outlook Legal Copyright',
    'Outlook Process Creation Date',
    'Outlook Process File Name',
    'Outlook Process Size',
    'Outlook Process Version',
    'Outlook Product Name',
    'Outlook Product Version',
    'Outlook User Name',
    'Partition Info Bootable',
    'Partition Info Name',
    'Partition Info PrimaryPartition',
    'Partition Row Position',
    'Partition Width',
    'Pegasus Account Name',
    'Pegasus Company Name',
    'Pegasus Current User',
    'Pegasus File Description',
    'Pegasus Identity',
    'Pegasus Legal Copyright',
    'Pegasus Mail Information',
    'Pegasus POP3 Port',
    'Pegasus POP3 Server',
    'Pegasus Process Creation Date',
    'Pegasus Process File Name',
    'Pegasus Process Size',
    'Pegasus Process Version',
    'Pegasus Product Name',
    'Pegasus Product Version',
    'Pegasus SMTP Port',
    'Pegasus SMTP Server',
    'Pegasus User',
    'Pegasus User Name',
    'Pegasus Version',
    'Percentage Free',
    'Percentage Used',
    'Physical Array Handle',
    'Physical Device Handle',
    'Physical Disk Caption',
    'Physical Disk Description',
    'Physical Disk DeviceID',
    'Physical Disk Information',
    'Physical Disk InterfaceType',
    'Physical Disk Manufacturer',
    'Physical Disk MediaType',
    'Physical Disk Model',
    'Physical Disk Partitions',
    'Physical Disk Status',
    'Physical Disk Serial',
    'Physical Disk SCSIBus',
    'Physical Disk SCSILogicalUnit',
    'Physical Disk SCSIPort',
    'Physical Disk SCSITargetId',
    'Physical Disk BytesPerSector',
    'Physical Disk SectorsPerTrack',
    'Physical Disk Size in Bytes',
    'Physical Disk TotalCylinders',
    'Physical Disk TotalHeads',
    'Physical Disk TotalSectors',
    'Physical Disk TotalTracks',
    'Physical Disk TracksPerCylinder',
    'Physical Memory Total (Kbytes)',
    'Plugged',
    'Pointing Device Type',
    'Ponting Device Interface',
    'POP3 Port',
    'POP3 Server',
    'POP3 User Name',
    'Port',
    'Port Connector Information',
    'Port Type',
    'Power-On Password Status',
    'Power Supply Asset Tag',
    'Power Supply Location',
    'Power Supply Manufacturer',
    'Power Supply Name',
    'Power Supply Revision',
    'Power Supply Serial Number',
    'Power Supply Status',
    'Power Supply Type',
    'Power Unit Group',
    'Printer Comment',
    'Printer Default',
    'Printer Information',
    'Printer Local',
    'Printer Location',
    'Printer Name',
    'Printer Network',
    'Printer PortName',
    'Printer PrintJobDataType',
    'Printer PrintProcessor',
    'Printer Shared',
    'Printer ShareName',
    'Printer SystemName',
    'Printer OEMUrl',
    'Printer Driver',
    'Printer Driver FileName',
    'Printer Driver LastModified',
    'Printer Driver Version',
    'Process Creation Date',
    'Process File Name',
    'Processor Asset Tag',
    'Processor Current Speed in MHz',
    'Processor Current Voltage',            /* bug 2709 */
    'Processor CurSpeed in Megahertz',
    'Processor CurSpeed in Megahertz',
    'Processor External Clock in MHz',
    'Processor Family',
    'Processor Family',
    'Processor Flags',
    'Processor ID',
    'Processor L1 Cache Handle',
    'Processor L2 Cache Handle',
    'Processor L3 Cache Handle',
    'Processor Manufacturer',
    'Processor Manufacturer',
    'Processor MaxSpeed in Megahertz',
    'Processor MaxSpeed in Megahertz',
    'Processor Max Speed in MHz',
    'Processor Part Number',
    'Processor Serial Number',
    'Processor Socket Designation',
    'Processor Status',
    'Processor Type',
    'Processor Upgrade',
    'Processor Version',
    'Processor Voltage',
    'Process Size',
    'Process Version',
    'Product Key',
    'Product Name',
    'Product Version',
    'Registered Processor',
    'Registered Processor',
    'Registered Service Tag',
    'Registered System Manufacturer',
    'Registered System Model',
    'Registered Vendor',
    'Registered Vendor',
    'Register Spacing',
    'Reset Count',
    'Reset Limit',
    'Reset Status',
    'Resolution in deg C',
    'Resolution in mA',
    'Resolution in mV',
    'SBDS Chemistry',
    'SBDS Manufacture Date',
    'SBDS Serial Number',
    'SBDS Version',
    'SCSI Controller Name',
    'SCSI Controller Description',
    'SCSI Controller DriverName',
    'SCSI Controller Information',
    'SCSI Controller Status',
    'SCSI Controller Manufacturer',
    'Serial Number',
    'Serial Number Battery',
    'Set',
    'Site Name',
    'Slot Characteristics',
    'Slot Designation',
    'Slot ID',
    'Slot Length',
    'Slot Type',
    'SMTP Display Name',
    'SMTP Email Address',
    'SMTP Port',
    'SMTP Reply To Email Address',
    'SMTP Server',
    'Socket Designation',
    'Socket Information',
    'Software License Information',
    'Software Version Information',
    'Sound Card Description',
    'Sound Card Driver FileName',
    'Sound Card Information',
    'Sound Card Driver LastModified',
    'Sound Card Manufacturer',
    'Sound Card ProductName',
    'Sound Card Status',
    'Sound Card Driver Version',
    'Specification Version',
    'Speed',
    'Speed in ns',
    'Start-up Program',
    'Start-up Programs',
    'Start-up Service',
    'Start-up Services',
    'Subnet Mask',
    'Supported Log Type Descriptors',
    'Supported Memory Module Voltage',      /* bug 2709 */
    'Supported Memory Speeds',
    'Supported Memory Types',
    'Supported Processor Voltage',          /* bug 2709 */
    'Supported SRAM Types',
    'System Boot Status',
    'System Configuration Options',
    'System Manufacturer',
    'System Product',
    'System Serial Number',
    'System Service Tag',
    'System Slot Information',
    'System Type',
    'Temperature Probe Accuracy',
    'Temperature Probe Description',
    'Temperature Probe Handle',
    'Temperature Probe Location',
    'Temperature Probe OEM-specific Information',
    'Temperature Probe Status',
    'Timeout in Min',
    'Timer Interval in Min',
    'Time Zone',
    'Tolerance in A',
    'Tolerance in deg C',
    'Tolerance in V',
    'Total Width in bits',
    'Type',
    'Update',
    'Update Category',
    'Update comments',
    'Update Company Name',
    'Update Description',
    'Update File and Version',
    'Update File Changes',
    'Update File Description',
    'Update Installed By',
    'Update Installation Date',
    'Update Legal Copyright',
    'Update Name',
    'Update Product Name',
    'Update Process Version',
    'Update Product Version',
    'Update Process Size',
    'Update Process Creation Date',
    'Update Process File Name',
    'Update Type',
    'Update Uninstall Command',
    'Upper Critical Threshold',
    'Upper Non-critical Threshold',
    'Upper Non-recoverable Threshold',
    'User',
    'User Account Caption',
    'User Account Description',
    'User Account Disabled',
    'User Account Domain',
    'User Account FullName',
    'User Account Groups',
    'User Account Information',
    'User Account LocalAccount',
    'User Account Lockout',
    'User Account Name',
    'User Account PasswordChangeable',
    'User Account PasswordExpires',
    'User Account PasswordRequired',
    'User Name',
    'UUID',
    'Version',
    'Video Controller AdapterCompatibility',
    'Video Controller AdapterDACType',
    'Video Controller Adapter RAM in MB',
    'Video Controller Caption',
    'Video Controller CurrentRefreshRate',
    'Video Controller Description',
    'Video Controller DriverDate',
    'Video Controller DriverVersion',
    'Video Controller Information',
    'Video Controller Name',
    'Video Controller Status',
    'Video Controller InstalledDisplayDrivers',
    'Video Controller VideoModeDescription',
    'Video Controller VideoProcessor',
    'Voltage',
    'Voltage Probe Accuracy',
    'Voltage Probe Description',
    'Voltage Probe Location',
    'Voltage Probe OEM-specific Information',
    'Voltage Probe Status',
    'Watchdog Timer',
    'Workgroup'
);

$leaders = array(
    'Local Drive',
    'Logical Disk Name',
    'Name',
    'Network Adapter',
    'Outlook Express Account Name',
    'Process File Name'
);

$include = array(
    'Site Name',
    'Machine Name'
);

/*
    |  Main program
    */

$now = time();
$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);


$rebuild  = (get_argument('rebuild', 0, 0)) ? 1 : 0;
$dbg      = (get_argument('debug', 0, 1)) ?   1 : 0;

$user = user_data($authuser, $db);

$priv_debug = @($user['priv_debug']) ? 1 : 0;
$priv_admin = @($user['priv_admin']) ? 1 : 0;


$debug = ($priv_debug) ? $dbg : 0;

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

db_change($GLOBALS['PREFIX'] . 'asset', $db);

/*
    |  client groups as of 9/25/2002
    */

if (($rebuild) && ($priv_admin)) {
    $sql = "delete from DataName where setbyclient = 0";
    redcommand($sql, $db);
    $sql = "update DataName set parent = 0, ordinal = 0";
    redcommand($sql, $db);
}

$num = count_dataname($db);
$srv = count_server($db);

/*
    |  We want administative access to rebuild the
    |  hierarchy.   However, in the special case
    |  when the database is empty, then we'll let
    |  just anyone do it.
    */

if (($priv_admin) || ($num == 0) || ($srv == 0)) {
    build_clients($clients, $now, $db);
    build_leaders($leaders, $db);
    build_include($include, $db);
    main($db, $now);
    change_name('IE Proxy exceptions', 'IE Proxy Exceptions', $db);
} else {
    $msg = "You need administrative access to rebuild the tree.";
    $msg = "<p>$msg</p><br>\n";
    echo $msg;
}



$sql  = "select * from DataName\n";
$sql .= " order by dataid";
draw_table($sql, $db);

$sql  = "select * from DataName\n";
$sql .= " where (groups != 0)\n";
$sql .= " and (groups != parent)\n";
$sql .= " order by dataid";
draw_table($sql, $db);

echo head_standard_html_footer($authuser, $db);
