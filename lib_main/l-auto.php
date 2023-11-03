<?php






define('constAutoSetTypeServer',    0);
define('constAutoSetTypeUser',      1);


define('constAutoConfStateStub',        0);
define('constAutoConfStateNew',         1);
define('constAutoConfStateComplete',    2);


define('constAutoDispKeepNew',      0);
define('constAutoDispCompleteNew',  1);
define('constAutoDispAppendNew',    2);


define('constBeginUrl',     'https://');
define('constEndUrl',       '/atservices/1.2/atws.wsdl');
define('constEndReqUrl',    '/atservices/1.2/atws.asmx');


define('constAutoSiteTypeDefault',  0);
define('constAutoSiteTypeLink',     1);


define('constPicksPriorities',      'p');
define('constPicksQueues',          'q');
define('constPicksUseNew',          'un');
define('constPicksNew',             'n');
define('constPicksUseComplete',     'uc');
define('constPicksComplete',        'c');
define('constPicksPrioritiesL',     'pl');
define('constPicksQueuesL',         'ql');
define('constPicksNoteType',        'nt');
define('constPicksNoteTypeL',       'ntl');
define('constPicksPublish',         'pb');
define('constPicksPublishL',        'pbl');


define('constAutotaskDescLimit',    4000);



class AutotaskPickListValue
{
    public $Value;
    public $Label;
    public $IsDefaultValue;
    public $SortOrder;
    public $parentValue;
}

class AutotaskArrayOfPickListValue
{
    public $PickListValue;
}

class AutotaskField
{
    public $Name;
    public $Label;
    public $Type;
    public $Length;
    public $Description;
    public $IsRequired;
    public $IsReadOnly;
    public $IsQueryable;
    public $IsReference;
    public $ReferenceEntityType;
    public $IsPickList;
    public $PicklistValues;
    public $PicklistParentValueField;
}

class AutotaskArrayOfField
{
    public $Field;
}

class AutotaskUserDefinedField
{
    public $Name;
    public $Value;
}

class AutotaskArrayOfUserDefinedField
{
    public $UserDefinedField;
}

class AutotaskEntity
{
    public $Fields;
    public $id;
    public $UserDefinedFields;
}

class AutotaskArrayOfEntity
{
    public $Entity;
}

class Autotaskquery
{
    public $sXML;
}

class AutotaskATWSError
{
    public $Message;
}

class AutotaskArrayOfATWSError
{
    public $ATWSError;
}

class AutotaskATWSResponse
{
    public $ReturnCode;
    public $EntityResults;
    public $EntityResultType;
    public $Errors;
}

class AutotaskqueryResponse
{
    public $queryResult;
}

class AutotaskTicket extends AutotaskEntity
{
    public $AccountID;
    public $AllocationCodeID;
    public $CompletedDate;
    public $ContactID;
    public $ContractID;
    public $CreateDate;
    public $CreatorResourceID;
    public $Description;
    public $DueDateTime;
    public $EstimatedHours;
    public $InstalledProductID;
    public $IssueType;
    public $LastActivityDate;
    public $Priority;
    public $QueueID;
    public $AssignedResourceID;
    public $AssignedResourceRoleID;
    public $Source;
    public $Status;
    public $SubIssueType;
    public $TicketNumber;
    public $Title;
    public $FirstResponseDateTime;
}

class AutotaskGetFieldInfo
{
    public $psObjectType;
}

class AutotaskGetFieldInfoResponse
{
    public $GetFieldInfoResult;
}

class Autotaskcreate
{
    public $Entities;
}

class AutotaskcreateResponse
{
    public $createResult;
}

function AUTO_OpenSoap($wsdl, $autouser, $autopass)
{
    $client = new SoapClient(
        $wsdl,
        array(
            'login' => $autouser,
            'password' => $autopass, 'trace' => 1,
            'classmap' => array(
                'PickListValue' => 'AutotaskPickListValue',
                'ArrayOfPickListValue' => 'AutotaskArrayOfPickListValue',
                'Field' => 'AutotaskField',
                'ArrayOfField' => 'AutotaskArrayOfField',
                'UserDefinedField' => 'AutotaskUserDefinedField',
                'ArrayOfUserDefinedField' => 'AutotaskArrayOfUserDefinedField',
                'Entity' => 'AutotaskEntity',
                'ArrayOfEntity' => 'AutotaskArrayOfEntity',
                'query' => 'Autotaskquery',
                'ATWSError' => 'AutotaskATWSError',
                'ArrayOfATWSError' => 'AutotaskArrayOfATWSError',
                'ATWSResponse' => 'AutotaskATWSResponse',
                'queryResponse' => 'AutotaskqueryResponse',
                'createResponse' => 'AutotaskcreateResponse',
                'Ticket' => 'AutotaskTicket'
            )
        )
    );
    return $client;
}

function AUTO_GetUserSettings($username, $db)
{
    $row = array();
    $quser = safe_addslashes($username);

    $sql = "SELECT * FROM  " . $GLOBALS['PREFIX'] . "autotask.UserSettings WHERE username='$quser'";
    $row = find_one($sql, $db);
    if (!($row)) {

        AUTO_CreateUserSettings($username, constAutoSetTypeUser, $db);
        $sql = "SELECT * FROM  " . $GLOBALS['PREFIX'] . "autotask.UserSettings WHERE username='$quser'";
        $row = find_one($sql, $db);
    }

    return $row;
}

function AUTO_GetDefaultSettings($db)
{
    $row = array();
    $sql = 'SELECT * FROM  ' . $GLOBALS['PREFIX'] . 'autotask.UserSettings WHERE settingtype='
        . constAutoSetTypeServer;
    $row = find_one($sql, $db);
    if (!($row)) {
        AUTO_CreateUserSettings('', constAutoSetTypeServer, $db);
        $sql = 'SELECT * FROM  ' . $GLOBALS['PREFIX'] . 'autotask.UserSettings WHERE settingtype='
            . constAutoSetTypeServer;
        $row = find_one($sql, $db);
    }
    return $row;
}

function AUTO_CreateUserSettings($username, $settingtype, $db)
{
    $quser = safe_addslashes($username);
    $sql = "INSERT INTO  " . $GLOBALS['PREFIX'] . "autotask.UserSettings (username, settingtype, "
        . "beginurl, endurl, confstate, endrequrl) VALUES ('$quser', "
        . $settingtype . ', \'' . constBeginUrl . '\', \''
        . constEndUrl . '\', ' . constAutoConfStateStub . ', \''
        . constEndReqUrl . '\')';
    command($sql, $db);
}

function AUTO_GetAutotaskAccounts($client)
{
    $options = array();
    $parameters = new Autotaskquery;
    $parameters->sXML = '<queryxml><entity>Account</entity>'
        . '<query><field>AccountName<expression op="isnotnull"></expression>'
        . '</field></query></queryxml>';
    $queryRes = $client->query($parameters);
    if ($queryRes->queryResult->ReturnCode <= 0) {
        echo 'Error querying account information: ';
        foreach ($queryRes->queryResult->Errors as $err) {
            if ($err) {
                echo $err->Message . '<br>';
            }
        }
        return 0;
    }


    if ($queryRes->queryResult->EntityResultType != 'account') {
        echo 'Unexpected result querying account information: '
            . $queryRes->queryResult->EntityResultType;
        return 0;
    }


    $count = 0;
    foreach ($queryRes->queryResult->EntityResults->Entity as $account) {
        if ($account) {
            $options[] = htmlentities($account->AccountName);
        }
    }

    if (!sort($options)) {
    }

    return $options;
}

function AUTO_MakeSelect(
    $options,
    $name,
    $nothingtxt,
    $curvalue,
    $emptytxt,
    $usekey,
    $optionsL
) {
    if (!($options)) {

        return $emptytxt;
    }

    $select = "<select name=\"$name\"><option ";
    if ($curvalue == null) {
        $select .= 'selected ';
    }
    $select .= "value=\"\">$nothingtxt</option>";

    reset($options);
    foreach ($options as $key => $value) {
        $thisvalue = $value;
        if ($usekey) {
            if ($optionsL == null) {
                $thisvalue = $key;
            } else {
                $thisvalue = $optionsL[$value];
            }
        }
        $select .= '<option ';
        if (($curvalue != null) && ($curvalue == $thisvalue)) {
            $select .= 'selected ';
        }
        $qvalue = htmlentities($thisvalue);
        $qtxt = htmlentities($value);
        $select .= "value=\"$qvalue\">$qtxt</option>";
    }

    return $select;
}

function AUTO_GetTicketPicklists($client)
{
    $picks = array();
    $picks[constPicksPriorities] = array();
    $picks[constPicksQueues] = array();
    $picks[constPicksPrioritiesL] = array();
    $picks[constPicksQueuesL] = array();
    $picks[constPicksUseNew] = false;
    $picks[constPicksNew] = 0;
    $picks[constPicksUseComplete] = false;
    $picks[constPicksComplete] = 0;

    $getfield = new AutotaskGetFieldInfo;
    $getfield->psObjectType = 'Ticket';
    $getInfoRes = $client->getFieldInfo($getfield);

    foreach ($getInfoRes->GetFieldInfoResult->Field as $field) {
        if ($field->Name == 'Priority') {
            AUTO_ConvertPickList(
                $picks[constPicksPriorities],
                $picks[constPicksPrioritiesL],
                $field
            );
        }
        if ($field->Name == 'QueueID') {
            AUTO_ConvertPickList(
                $picks[constPicksQueues],
                $picks[constPicksQueuesL],
                $field
            );
        }
        if ($field->Name == 'Status') {
            foreach ($field->PicklistValues->PickListValue as $pick) {

                if ($pick->Label == 'New') {
                    $picks[constPicksUseNew] = true;
                    $picks[constPicksNew] = $pick->Value;
                }
                if ($pick->Label == 'Complete') {
                    $picks[constPicksUseComplete] = true;
                    $picks[constPicksComplete] = $pick->Value;
                }
            }
        }
    }

    return $picks;
}

function AUTO_ConvertPickList(&$picklist, &$picklistL, $field)
{
    foreach ($field->PicklistValues->PickListValue as $pick) {
        $picklist[(string)$pick->Value] = $pick->Label;
        $picklistL[$pick->Label] = $pick->Value;
    }
    sort($picklist);
}

function AUTO_SendNotification($title, $desc, $username, $site, $not, $db)
{

    $realdesc = htmlentities($desc);
    $len = strlen($realdesc);
    if ($len > constAutotaskDescLimit) {
        AUTO_LogAudit("l-auto.php: truncating $len to 4000 bytes");
        $realdesc = substr(htmlentities($desc), 0, constAutotaskDescLimit);
    }

    $client = null;
    try {
        $settings = AUTO_ValidateSettings($username, true, $db);
        if (!$settings) {
            return;
        }

        $client = AUTO_OpenSoap(
            $settings['beginurl'] . $settings['middleurl']
                . $settings['endurl'],
            $settings['autouser'],
            $settings['autopass']
        );
        if (!($client)) {
            AUTO_LogAudit('Failed to open soap for user: '
                . $username);
            return;
        }

        $sql = 'SELECT descr FROM  ' . $GLOBALS['PREFIX'] . 'autotask.AutotaskAccount WHERE site=\''
            . safe_addslashes($site) . '\' AND linktype=' . constAutoSiteTypeLink;
        $row = find_one($sql, $db);
        if (!($row)) {

            $sql = 'SELECT descr FROM  ' . $GLOBALS['PREFIX'] . 'autotask.AutotaskAccount WHERE '
                . 'linktype=' . constAutoSiteTypeDefault;
            $row = find_one($sql, $db);
        }
        if (!($row)) {
            AUTO_LogAudit('No Autotask account link is available for site '
                . $site);
            return;
        }

        if (!AUTO_LookupAccount(
            $client,
            $settings['autoaccountid'],
            $row['descr']
        )) {
            AUTO_LogAudit('No Autotask account found for '
                . $row['descr']);
            return;
        }

        $createTicket = true;
        if ($settings['createdisp'] == constAutoDispAppendNew) {

            $sql = 'SELECT notemapid, ticketid FROM  ' . $GLOBALS['PREFIX'] . 'autotask.NotificationMap '
                . 'WHERE notifyid='
                . $not['id'] . ' AND accountid=' . $settings['autoaccountid'];
            $row = find_one($sql, $db);
            if ($row) {

                if (!(AUTO_GetTicketStatus(
                    $client,
                    $exists,
                    $status,
                    $row['ticketid']
                ))) {
                    return;
                }
                if (($exists) && ($status != $settings['statcloseid'])) {
                    $createTicket = false;
                    AUTO_AppendTicket(
                        $client,
                        $title,
                        $realdesc,
                        $row['ticketid'],
                        $settings
                    );
                } else {

                    $sql = 'DELETE FROM  ' . $GLOBALS['PREFIX'] . 'autotask.NotificationMap WHERE '
                        . 'notemapid=' . $row['notemapid'];
                    command($sql, $db);
                }
            }
        }

        if ($createTicket) {
            AUTO_CreateTicket(
                $client,
                $title,
                $realdesc,
                $settings,
                $not,
                $db
            );
        }
    } catch (SoapFault $e) {
        AUTO_LogAudit("Error contacting Autotask server: (faultcode: "
            . "{$e->faultcode}, faultstring: {$e->faultstring})");
    }
}


function AUTO_CreateTicket($client, $title, $desc, $settings, $not, $db)
{
    $accountid = $settings['autoaccountid'];


    $duedate = gmdate(
        'Y-m-d\TH:i:s.0000000+00:00',
        time() + $settings['duedateadd']
    );
    $priority = $settings['priorityid'];
    $queue = $settings['queueid'];

    switch ($settings['createdisp']) {
        case constAutoDispKeepNew:
        case constAutoDispAppendNew:
            $status = $settings['statusid'];
            break;
        case constAutoDispCompleteNew:
            $status = $settings['statcloseid'];
            break;
        default:
            AUTO_LogAudit('Unknown create disposition: '
                . $settings['createdisp']);
            return;
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?> <SOAP-ENV:Envelope '
        . 'xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" '
        . 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '
        . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
        . '<SOAP-ENV:Body>'
        . '<create xmlns="http://autotask.net/ATWS/v1_2/">'
        . '<Entities>'
        . '<Entity xsi:type="Ticket">'
        . "<AccountID xsi:type=\"xsd:int\">$accountid</AccountID>"
        . "<Description xsi:type=\"xsd:string\">$desc</Description>"
        . "<DueDateTime xsi:type=\"xsd:dateTime\">$duedate</DueDateTime>"
        . "<Priority xsi:type=\"xsd:int\">$priority</Priority>"
        . "<Status xsi:type=\"xsd:int\">$status</Status>"
        . "<Title xsi:type=\"xsd:string\">$title</Title>"
        . "<QueueID xsi:type=\"xsd:int\">$queue</QueueID>"
        . '</Entity></Entities></create></SOAP-ENV:Body>'
        . '</SOAP-ENV:Envelope>';
    $result = $client->__doRequest(
        $xml,
        $settings['beginurl'] . $settings['middleurl']
            . $settings['endrequrl'],
        'http://autotask.net/ATWS/v1_2/create',
        1
    );


    if (strpos($result, '<ReturnCode>1</ReturnCode>') === false) {

        AUTO_LogAudit('Failed to create ticket: ' . $result);
    } else {
        $pos = strpos($result, '<id>') + strlen('<id>');
        $lpos = strpos($result, '</id>');
        $id = substr($result, $pos, $lpos - $pos);
        AUTO_LogAudit("Created ticket $id, title $title");


        if ($settings['createdisp'] == constAutoDispAppendNew) {
            $sql = 'INSERT INTO  ' . $GLOBALS['PREFIX'] . 'autotask.NotificationMap (notifyid, accountid,'
                . ' ticketid) VALUES (' . $not['id'] . ", $accountid, $id)";
            command($sql, $db);
        }
    }
}


function AUTO_GetTicketStatus($client, &$exists, &$status, $ticketid)
{
    $exists = false;
    $parameters = new Autotaskquery;
    $parameters->sXML = '<queryxml><entity>Ticket</entity>'
        . "<query><field>id<expression op=\"equals\">$ticketid</expression>"
        . '</field></query></queryxml>';
    $queryRes = $client->query($parameters);
    if ($queryRes->queryResult->ReturnCode <= 0) {
        $msg =  'Error querying ticket information: ';
        foreach ($queryRes->queryResult->Errors as $err) {
            if ($err) {
                $msg .= $err->Message . ', ';
            }
        }
        AUTO_LogAudit($msg);
        return false;
    }


    if ($queryRes->queryResult->EntityResultType != 'ticket') {
        AUTO_LogAudit('Unexpected result querying ticket information: '
            . $queryRes->queryResult->EntityResultType);
        return false;
    }
    if ($queryRes->queryResult->EntityResults->Entity != null) {
        $status = $queryRes->queryResult->EntityResults->Entity->Status;
        $exists = true;
        return true;
    }

    return true;
}

function AUTO_LogAudit($text)
{
    $err = PHP_AUDT_LogLocalAudit(
        CUR,
        constAUTONotifyLevel,
        constModuleAUTO,
        constClassDebug,
        constAuditGroupAUTONotification,
        '',
        $text
    );
    if ($err != constAppNoErr) {
    }
}

function AUTO_LookupAccount($client, &$accountid, $accountname)
{

    $parameters = new Autotaskquery;
    $parameters->sXML = '<queryxml><entity>Account</entity>'
        . "<query><field>AccountName<expression op=\"equals\">$accountname"
        . '</expression></field></query></queryxml>';
    $queryRes = $client->query($parameters);
    if ($queryRes->queryResult->ReturnCode <= 0) {
        $msg =  'Error querying account information: ';
        foreach ($queryRes->queryResult->Errors as $err) {
            if ($err) {
                $msg .= $err->Message . ', ';
            }
        }
        AUTO_LogAudit($msg);
        return false;
    }


    if ($queryRes->queryResult->EntityResultType != 'account') {
        AUTO_LogAudit('Unexpected result querying account information: '
            . $queryRes->queryResult->EntityResultType);
        return false;
    }
    if ($queryRes->queryResult->EntityResults->Entity != null) {
        $accountid = $queryRes->queryResult->EntityResults->Entity->id;
        return true;
    }

    return false;
}

function AUTO_AppendTicket($client, $title, $desc, $ticketid, $settings)
{
    $notetypeid = $settings['notetypeid'];
    $publishid = $settings['publishid'];
    $xml = '<?xml version="1.0" encoding="UTF-8"?> <SOAP-ENV:Envelope '
        . 'xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" '
        . 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '
        . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
        . '<SOAP-ENV:Body>'
        . '<create xmlns="http://autotask.net/ATWS/v1_2/">'
        . '<Entities>'
        . '<Entity xsi:type="TicketNote">'
        . "<Description xsi:type=\"xsd:string\">$desc</Description>"
        . "<NoteType xsi:type=\"xsd:int\">$notetypeid</NoteType>"
        . "<Publish xsi:type=\"xsd:int\">$publishid</Publish>"
        . "<TicketID xsi:type=\"xsd:int\">$ticketid</TicketID>"
        . "<Title xsi:type=\"xsd:string\">$title</Title>"
        . '</Entity></Entities></create></SOAP-ENV:Body>'
        . '</SOAP-ENV:Envelope>';
    $result = $client->__doRequest(
        $xml,
        $settings['beginurl'] . $settings['middleurl']
            . $settings['endrequrl'],
        'http://autotask.net/ATWS/v1_2/create',
        1
    );


    if (strpos($result, '<ReturnCode>1</ReturnCode>') === false) {

        AUTO_LogAudit('Failed to create ticket note: ' . $result);
    } else {
        $pos = strpos($result, '<id>') + strlen('<id>');
        $lpos = strpos($result, '</id>');
        $id = substr($result, $pos, $lpos - $pos);
        AUTO_LogAudit("Created ticket note $id, title $title");
    }
}

function AUTO_GetTicketNotePicklists($client)
{
    $picks = array();

    $getfield = new AutotaskGetFieldInfo;
    $getfield->psObjectType = 'TicketNote';
    $getInfoRes = $client->getFieldInfo($getfield);

    foreach ($getInfoRes->GetFieldInfoResult->Field as $field) {
        if ($field->Name == 'NoteType') {
            AUTO_ConvertPickList(
                $picks[constPicksNoteType],
                $picks[constPicksNoteTypeL],
                $field
            );
        }
        if ($field->Name == 'Publish') {
            AUTO_ConvertPickList(
                $picks[constPicksPublish],
                $picks[constPicksPublishL],
                $field
            );
        }
    }

    return $picks;
}


function AUTO_ValidateSettings($username, $log, $db)
{
    $settings = array();

    $settings = AUTO_GetUserSettings($username, $db);
    if (!$settings) {
        if ($log) {
            AUTO_LogAudit('No Autotask settings for user: '
                . $username);
        }
        return array();
    }
    if ($settings['confstate'] != constAutoConfStateComplete) {

        $settings = AUTO_GetDefaultSettings($db);
        if (!$settings) {
            if ($log) {
                AUTO_LogAudit('No Autotask settings for the server.');
            }
            return array();
        }
    }

    if ($settings['confstate'] != constAutoConfStateComplete) {

        if ($log) {
            AUTO_LogAudit('Incomplete Autotask settings for user: '
                . $username . ' or incomplete server defaults.');
        }
        return array();
    }

    return $settings;
}
