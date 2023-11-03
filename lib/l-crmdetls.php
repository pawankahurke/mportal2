<?php

function RSLR_AviraCRMlogin($firstName, $lastname, $regEmail, $compName, $cid, $ctype, $prntId)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    global $suitecrm_username;
    global $suitecrm_password;
    global $aviraParentID;
    global $avirapartnerNm;

    $parentId = '';
    $parentName = '';
    if ($ctype == '2' || $ctype == 2) {
        $parentId = $aviraParentID;
        $parentName = $avirapartnerNm;
    }
    if ($ctype == '5' || $ctype == 5) {
        $sql_crmdel = "select crmAccId,accountname from " . $GLOBALS['PREFIX'] . "agent.contactDetails where chId='$prntId' limit 1";
        $crm_resdtl = find_one($sql_crmdel, $db);
        $parentId = $crm_resdtl['crmAccId'];
        $parentName = $crm_resdtl['accountname'];
    }

    $parameters = array(
        'user_auth' => array(
            'user_name' => $suitecrm_username,
            'password' => md5($suitecrm_password),
        ),
    );

    $response = RSLR_curlPost('login', $parameters);

    $crmLoginId = $response['id'];
    if ($crmLoginId != '') {

        $account_id = RSLR_pushAviraResellerAccountCRM($compName, $regEmail, $crmLoginId, $aviraParentID, $avirapartnerNm, 'Reseller');
        $business_type = 'Small_MSP_SOHO';
        $lead_source = 'Website_Trial';
        $crmlead_id = RSLR_pushLeadCRM($firstName, '', $compName, $business_type, $regEmail, $lead_source, $compName, $account_id, $crmLoginId);
        $contact_id = RSLR_pushContactCRM($firstName, '', $regEmail, $compName, $account_id, $crmLoginId);
        $mid = RSLR_mauticContact($firstName, $lastname, $regEmail, $compName);

        $sql_crm = "insert into " . $GLOBALS['PREFIX'] . "agent.contactDetails SET emailId='" . $regEmail . "',chId='$cid',accountname='" . $compName . "',crmAccId='" . $account_id . "',crmUserId='" . $contact_id . "',crmLeadId='" . $crmlead_id . "',mauticId='$mid',mauticSegid='',crmAcctType='Reseller'";
        $crm_res = redcommand($sql_crm, $db);
    }
}

function RSLR_CRMResellerlogin($firstName, $lastname, $regEmail, $compName, $cid)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    global $suitecrm_username;
    global $suitecrm_password;
    $parameters = array(
        'user_auth' => array(
            'user_name' => $suitecrm_username,
            'password' => md5($suitecrm_password),
        ),
    );
    $response = RSLR_curlPost('login', $parameters);

    $crmLoginId = $response['id'];
    if ($crmLoginId != '') {

        $account_id = RSLR_pushAccountCRM($compName, $regEmail, $crmLoginId, 'Reseller');
        $business_type = 'Small_MSP_SOHO';
        $lead_source = 'Website_Trial';
        $crmlead_id = RSLR_pushLeadCRM($firstName, '', $compName, $business_type, $regEmail, $lead_source, $compName, $account_id, $crmLoginId);
        $contact_id = RSLR_pushContactCRM($firstName, '', $regEmail, $compName, $account_id, $crmLoginId);
        $mid = RSLR_mauticContact($firstName, $lastname, $regEmail);
        $seg = RSLR_mauticcontacttosegment($mid);
        $sql_crm = "insert into contactDetails SET emailId='" . $regEmail . "',chId='$cid',crmAccId='" . $account_id . "',crmUserId='" . $contact_id . "',crmLeadId='" . $crmlead_id . "',mauticId='$mid',mauticSegid='5',crmAcctType='Reseller'";
        $crm_res = redcommand($sql_crm, $db);
    }
}

function RSLR_CRMlogin($firstName, $lastname, $regEmail, $compName, $custNo, $cid)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    global $suitecrm_username;
    global $suitecrm_password;
    global $matic_segmentId;
    global $crmAccountId;
    global $crmAccountName;

    $parameters = array(
        'user_auth' => array(
            'user_name' => $suitecrm_username,
            'password' => md5($suitecrm_password),
        ),
    );
    $response = RSLR_curlPost('login', $parameters);

    $crmLoginId = $response['id'];
    if ($crmLoginId != '') {

        $business_type = 'Small_MSP_SOHO';
        $lead_source = 'Web Site';
        $crmlead_id = RSLR_pushLeadCRM($firstName, $lastname, $compName, $business_type, $regEmail, $lead_source, $crmAccountName, $crmAccountId, $custNo, $crmLoginId);

        $mid = RSLR_mauticContact($firstName, $lastname, $regEmail, $compName);
        $seg = RSLR_mauticcontacttosegment($mid);

        $contact_id = 0;
        $sql_crm = "insert into contactDetails SET emailId='" . $regEmail . "',chId='$cid',crmUserId='" . $contact_id . "',crmLeadId='$crmlead_id',mauticId='$mid',mauticSegid='$matic_segmentId',crmAcctType='Lead'";
        $crm_res = redcommand($sql_crm, $db);
    }
}

function RSLR_CRMlLeadlogin($firstName, $lastname, $regEmail, $compName, $custNo, $cid)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    global $suitecrm_username;
    global $suitecrm_password;
    global $matic_segmentId;
    global $crmAccountId;
    global $crmAccountName;
    $crmlead_id = $_SESSION['user']['contactId'];
    $parameters = array(
        'user_auth' => array(
            'user_name' => $suitecrm_username,
            'password' => md5($suitecrm_password),
        ),
    );
    $response = RSLR_curlPost('login', $parameters);

    $crmLoginId = $response['id'];
    if ($crmLoginId != '') {


        $crmCheck = RSLR_checkLeadID($crmlead_id);
        if ($crmCheck != 0) {

            $sql_crm = "update contactDetails SET chId='$cid' where crmLeadId='$crmlead_id'";
            $crm_res = redcommand($sql_crm, $db);
            RSLR_mauticUpdateStage($crmCheck, $crmlead_id);
        } else {

            $mid = RSLR_mauticContact($firstName, $lastname, $regEmail, $compName);
            $seg = RSLR_mauticcontacttosegment($mid);

            $contact_id = 0;
            $sql_crm = "insert into contactDetails SET emailId='" . $regEmail . "',chId='$cid',crmUserId='" . $contact_id . "',crmLeadId='$crmlead_id',mauticId='$mid',mauticSegid='$matic_segmentId',crmAcctType='Lead'";
            $crm_res = redcommand($sql_crm, $db);
        }
    }
}

function RSLR_checkLeadID($crmlead_id)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $sql_crmdel = "select mauticId,mauticSegid from " . $GLOBALS['PREFIX'] . "agent.contactDetails where crmLeadId='$crmlead_id' limit 1";
    $crm_resdtl = find_one($sql_crmdel, $db);
    if (safe_count($crm_resdtl) > 0) {
        $mauticId = $crm_resdtl['mauticId'];
        return $mauticId;
    }
    return 0;
}

function RSLR_pushAccountCRM($name, $email, $crmLoginId, $account_type)
{
    $parameters = array(
        "session" => $crmLoginId,
        "module_name" => "Accounts",
        "name_value_list" => array(
            array(
                "name" => "name",
                "value" => $name
            ),
            array(
                "name" => "email1",
                "value" => $email
            ),
            array(
                "name" => "account_type",
                "value" => $account_type
            ),
            array(
                "name" => "industry",
                "value" => 'Technology'
            )
        ),
    );
    $response = RSLR_curlPost('set_entry', $parameters);
    return $response['id'];
}

function RSLR_pushAviraResellerAccountCRM($name, $email, $crmLoginId, $parent_id, $parent_name, $account_type)
{
    $parameters = array(
        "session" => $crmLoginId,
        "module_name" => "Accounts",
        "name_value_list" => array(
            array(
                "name" => "name",
                "value" => $name
            ),
            array(
                "name" => "email1",
                "value" => $email
            ),
            array(
                "name" => "parent_id",
                "value" => $parent_id
            ),
            array(
                "name" => "parent_name",
                "value" => $parent_name
            ),
            array(
                "name" => "account_type",
                "value" => $account_type
            ),
            array(
                "name" => "industry",
                "value" => 'Technology'
            )
        ),
    );
    $response = RSLR_curlPost('set_entry', $parameters);
    return $response['id'];
}

function RSLR_pushLeadCRM($first_name, $last_name, $company_name, $business_type, $email, $lead_source, $account_name, $account_id, $custNo, $crmLoginId)
{
    $parameters = array(
        "session" => $crmLoginId,
        "module_name" => "Leads",
        "name_value_list" => array(
            array(
                "name" => "first_name",
                "value" => $first_name
            ),
            array(
                "name" => "last_name",
                "value" => $last_name
            ),
            array(
                "name" => "company_name_c",
                "value" => $company_name
            ),
            array(
                "name" => "devicemanaged_c",
                "value" => ""
            ),
            array(
                "name" => "customer_stage_c",
                "value" => "trialing"
            ),
            array(
                "name" => "status",
                "value" => "New"
            ),
            array(
                "name" => "lead_source",
                "value" => $lead_source
            ),
            array(
                "name" => "email1",
                "value" => $email
            ),
            array(
                "name" => "account_name",
                "value" => $account_name
            ),
            array(
                "name" => "cust_id_c",
                "value" => $custNo
            ),
            array(
                "name" => "is_trial_c",
                "value" => "yes"
            ),
            array(
                "name" => "account_id",
                "value" => $account_id
            ),
        ),
    );
    $response = RSLR_curlPost('set_entry', $parameters);
    return $response['id'];
}

function RSLR_pushContactCRM($first_name, $last_name, $email, $custNo, $crmLoginId, $cid)
{
    global $crmAccountId;
    global $crmAccountName;
    $curDate = date("Y-m-d");
    $contractEnd = Date("Y-m-d", strtotime('+365 days'));
    $parameters = array(
        "session" => $crmLoginId,
        "module_name" => "Contacts",
        "name_value_list" => array(
            array(
                "name" => "first_name",
                "value" => $first_name
            ),
            array(
                "name" => "last_name",
                "value" => $last_name
            ),
            array(
                "name" => "email1",
                "value" => $email
            ),
            array(
                "name" => "customer_stage_c",
                "value" => "trialing"
            ),
            array(
                "name" => "trial_start_c",
                "value" => $curDate
            ),
            array(
                "name" => "trial_end_c",
                "value" => $contractEnd
            ), array(
                "name" => "trial_period_c",
                "value" => "365"
            ), array(
                "name" => "is_trial_c",
                "value" => "1"
            ),
            array(
                "name" => "account_name",
                "value" => $crmAccountName
            ),
            array(
                "name" => "account_id",
                "value" => $crmAccountId
            ), array(
                "name" => "cust_id_c",
                "value" => $custNo
            ),
            array(
                "name" => "is_trial_c",
                "value" => "yes"
            ),
            array(
                "name" => "lead_source",
                "value" => "Website"
            ),
            array(
                "name" => "nhcustid_c",
                "value" => $cid
            ),
            array(
                "name" => "trial_enddate_c",
                "value" => $contractEnd
            ),
        ),
    );
    $response = RSLR_curlPost('set_entry', $parameters);
    return $response['id'];
}

function RSLR_pushOrderCRM($contactId, $parametersList)
{
}

function RSLR_pushUpdateContactDtlCRM($custNo, $crmCntId, $cid)
{

    global $suitecrm_username;
    global $suitecrm_password;
    $parameters = array(
        'user_auth' => array(
            'user_name' => $suitecrm_username,
            'password' => md5($suitecrm_password),
        ),
    );
    $response = RSLR_curlPost('login', $parameters);
    $curDate = date("Y-m-d");
    $contractEnd = Date("Y-m-d", strtotime('+365 days'));
    $crmLoginId = $response['id'];
    if ($crmLoginId != '') {
        $parameters1 = array(
            "session" => $crmLoginId,
            "module_name" => "Contacts",
            "name_value_list" => array(
                array(
                    "name" => "id",
                    "value" => $crmCntId
                ),
                array(
                    "name" => "cust_id_c",
                    "value" => $custNo
                ),
                array(
                    "name" => "is_trial_c",
                    "value" => "yes"
                ),
                array(
                    "name" => "lead_source",
                    "value" => "Website"
                ),
                array(
                    "name" => "nhcustid_c",
                    "value" => $cid
                ),
                array(
                    "name" => "trial_enddate_c",
                    "value" => $contractEnd
                ), array(
                    "name" => "trial_start_c",
                    "value" => $curDate
                ),
                array(
                    "name" => "trial_end_c",
                    "value" => $contractEnd
                ), array(
                    "name" => "trial_period_c",
                    "value" => "365"
                ), array(
                    "name" => "is_trial_c",
                    "value" => "yes"
                ),
            ),
        );
        $response = RSLR_curlPost('set_entry', $parameters1);
        return $response['id'];
    }
}

function RSLR_pushUpdateLeadCRM($crmCntId, $mauticId)
{

    global $suitecrm_username;
    global $suitecrm_password;
    global $mauticURL;
    global $mauticKEY;


    $parameters = array(
        'user_auth' => array(
            'user_name' => $suitecrm_username,
            'password' => md5($suitecrm_password),
        ),
    );
    $response = RSLR_curlPost('login', $parameters);

    $crmLoginId = $response['id'];
    if ($crmLoginId != '') {
        $parameters1 = array(
            "session" => $crmLoginId,
            "module_name" => "Leads",
            "name_value_list" => array(
                array(
                    "name" => "id",
                    "value" => $crmCntId
                ),
                array(
                    "name" => "is_password_set_c",
                    "value" => "1"
                ),
            ),
        );
        $response = RSLR_curlPost('set_entry', $parameters1);
    }

    if ($mauticId != '') {

        $url = $mauticURL . 'service.php';

        $data = array(
            'contactid' => $mauticId,
            'is_password_set' => TRUE,
            'action' => 'editContact'
        );
        $response = post($data, $url);
    }
}

function RSLR_pushUpdateContactCRM($crmCntId, $mauticId)
{

    global $suitecrm_username;
    global $suitecrm_password;
    global $mauticURL;
    global $mauticKEY;


    $parameters = array(
        'user_auth' => array(
            'user_name' => $suitecrm_username,
            'password' => md5($suitecrm_password),
        ),
    );
    $response = RSLR_curlPost('login', $parameters);

    $crmLoginId = $response['id'];
    if ($crmLoginId != '') {
        $parameters1 = array(
            "session" => $crmLoginId,
            "module_name" => "Contacts",
            "name_value_list" => array(
                array(
                    "name" => "id",
                    "value" => $crmCntId
                ),
                array(
                    "name" => "is_password_set_c",
                    "value" => "1"
                ),
            ),
        );
        $response = RSLR_curlPost('set_entry', $parameters1);
        return $response['id'];
    }

    if ($mauticId != '') {

        $url = $mauticURL . 'service.php';

        $data = array(
            'contactid' => $mauticId,
            'is_password_set' => TRUE,
            'action' => 'editContact'
        );
        $response = post($data, $url);
    }
}

function RSLR_updateLastlogin($crmCntId, $lastlogindt)
{

    global $suitecrm_username;
    global $suitecrm_password;
    $loginDateEnd = Date("Y-m-d h:m:s", $lastlogindt);
    $parameters = array(
        'user_auth' => array(
            'user_name' => $suitecrm_username,
            'password' => md5($suitecrm_password),
        ),
    );
    $response = RSLR_curlPost('login', $parameters);

    $crmLoginId = $response['id'];
    if ($crmLoginId != '') {
        $parameters1 = array(
            "session" => $crmLoginId,
            "module_name" => "Contacts",
            "name_value_list" => array(
                array(
                    "name" => "id",
                    "value" => $crmCntId
                ),
                array(
                    "name" => "last_login_c",
                    "value" => $loginDateEnd
                ),
            ),
        );
        $response = RSLR_curlPost('set_entry', $parameters1);
        return $response['id'];
    }
}

function RSLR_updateLeadLastlogin($crmCntId, $lastlogindt, $mauticId)
{

    global $suitecrm_username;
    global $suitecrm_password;
    global $mauticURL;
    global $mauticKEY;

    $loginDateEnd = Date("Y-m-d h:m:s", $lastlogindt);
    $parameters = array(
        'user_auth' => array(
            'user_name' => $suitecrm_username,
            'password' => md5($suitecrm_password),
        ),
    );
    $response = RSLR_curlPost('login', $parameters);

    $crmLoginId = $response['id'];
    if ($crmLoginId != '') {
        $parameters1 = array(
            "session" => $crmLoginId,
            "module_name" => "Leads",
            "name_value_list" => array(
                array(
                    "name" => "id",
                    "value" => $crmCntId
                ),
                array(
                    "name" => "last_login_c",
                    "value" => $loginDateEnd
                ),
            ),
        );
        $response = RSLR_curlPost('set_entry', $parameters1);
    }

    $curDate = date("Y-m-d h:m");
    if ($mauticId != '') {

        $url = $mauticURL . 'service.php';

        $data = array(
            'contactid' => $mauticId,
            'last_login' => $curDate,
            'action' => 'editContact'
        );
        $response = post($data, $url);
    }
}

function RSLR_mauticUpdateStage($mauticId, $crmCntId)
{

    global $mauticURL;
    global $mauticKEY;


    global $suitecrm_username;
    global $suitecrm_password;
    global $mauticURL;
    global $mauticKEY;


    $parameters = array(
        'user_auth' => array(
            'user_name' => $suitecrm_username,
            'password' => md5($suitecrm_password),
        ),
    );
    $response = RSLR_curlPost('login', $parameters);

    $crmLoginId = $response['id'];
    if ($crmLoginId != '') {
        $parameters1 = array(
            "session" => $crmLoginId,
            "module_name" => "Leads",
            "name_value_list" => array(
                array(
                    "name" => "id",
                    "value" => $crmCntId
                ),
                array(
                    "name" => "is_trial_c",
                    "value" => "yes"
                ),
            ),
        );
        $response = RSLR_curlPost('set_entry', $parameters1);
    }

    $curDate = date("Y-m-d h:m");
    $contractEnd = Date("Y-m-d h:m", strtotime('+365 days'));
    if ($mauticId != '') {

        $url = $mauticURL . 'service.php';

        $data = array(
            'contactid' => $mauticId,
            'trial_start_date' => $curDate,
            'trial_end_date' => $contractEnd,
            'campaign_source' => 'website',
            'customer_stage' => 'trialing',
            'action' => 'editContact'
        );
        $response = post($data, $url);
    }
}

function RSLR_updateDownlCnt($crmCntId, $dwnlCnt, $mauticId)
{

    global $suitecrm_username;
    global $suitecrm_password;
    global $mauticURL;
    global $mauticKEY;

    $loginDateEnd = Date("Y-m-d h:m:s", $lastlogindt);
    $parameters = array(
        'user_auth' => array(
            'user_name' => $suitecrm_username,
            'password' => md5($suitecrm_password),
        ),
    );
    $response = RSLR_curlPost('login', $parameters);

    $crmLoginId = $response['id'];
    if ($crmLoginId != '') {
        $parameters1 = array(
            "session" => $crmLoginId,
            "module_name" => "Contacts",
            "name_value_list" => array(
                array(
                    "name" => "id",
                    "value" => $crmCntId
                ),
                array(
                    "name" => "download_count_c",
                    "value" => $dwnlCnt
                ),
            ),
        );
        $response = RSLR_curlPost('set_entry', $parameters1);
    }

    if ($mauticId != '') {

        $url = $mauticURL . 'service.php';

        $data = array(
            'contactid' => $mauticId,
            'download_count' => $dwnlCnt,
            'action' => 'editContact'
        );
        $response = post($data, $url);
    }
}

function RSLR_updateInstlCnt($crmCntId, $instlCnt, $mauticId)
{

    global $suitecrm_username;
    global $suitecrm_password;
    global $mauticURL;
    global $mauticKEY;

    $parameters = array(
        'user_auth' => array(
            'user_name' => $suitecrm_username,
            'password' => md5($suitecrm_password),
        ),
    );
    $response = RSLR_curlPost('login', $parameters);

    $crmLoginId = $response['id'];
    if ($crmLoginId != '') {
        $parameters1 = array(
            "session" => $crmLoginId,
            "module_name" => "Contacts",
            "name_value_list" => array(
                array(
                    "name" => "id",
                    "value" => $crmCntId
                ),
                array(
                    "name" => "installed_count_c",
                    "value" => $instlCnt
                ),
            ),
        );
        $response = RSLR_curlPost('set_entry', $parameters1);
    }

    if ($mauticId != '') {

        $url = $mauticURL . 'service.php';

        $data = array(
            'contactid' => $mauticId,
            'install_count' => $instlCnt,
            'action' => 'editContact'
        );
        $response = post($data, $url);
    }
}

function RSLR_updateTrialInstlCnt($crmCntId, $instlCnt, $mauticId)
{

    global $suitecrm_username;
    global $suitecrm_password;
    global $mauticURL;
    global $mauticKEY;

    $parameters = array(
        'user_auth' => array(
            'user_name' => $suitecrm_username,
            'password' => md5($suitecrm_password),
        ),
    );
    $response = RSLR_curlPost('login', $parameters);

    $crmLoginId = $response['id'];
    if ($crmLoginId != '') {
        $parameters1 = array(
            "session" => $crmLoginId,
            "module_name" => "Contacts",
            "name_value_list" => array(
                array(
                    "name" => "id",
                    "value" => $crmCntId
                ),
                array(
                    "name" => "trial_installs_c",
                    "value" => $instlCnt
                ),
            ),
        );
        $response = RSLR_curlPost('set_entry', $parameters1);
    }

    if ($mauticId != '') {

        $url = $mauticURL . 'service.php';

        $data = array(
            'contactid' => $mauticId,
            'install_count' => $instlCnt,
            'action' => 'editContact'
        );
        $response = post($data, $url);
    }
}

function RSLR_updateTrialStat($crmCntId)
{

    global $suitecrm_username;
    global $suitecrm_password;

    $parameters = array(
        'user_auth' => array(
            'user_name' => $suitecrm_username,
            'password' => md5($suitecrm_password),
        ),
    );
    $response = RSLR_curlPost('login', $parameters);

    $crmLoginId = $response['id'];
    if ($crmLoginId != '') {
        $parameters1 = array(
            "session" => $crmLoginId,
            "module_name" => "Contacts",
            "name_value_list" => array(
                array(
                    "name" => "id",
                    "value" => $crmCntId
                ),
                array(
                    "name" => "is_trial_c",
                    "value" => "no"
                ),
            ),
        );
        $response = RSLR_curlPost('set_entry', $parameters1);
        return $response['id'];
    }
}

function RSLR_curlPost($method, $parameters)
{

    global $suitcrm_service_url;

    $json = json_encode($parameters);

    $postArgs = array(
        'method' => $method,
        'input_type' => 'JSON',
        'response_type' => 'JSON',
        'rest_data' => $json,
    );
    $postArgs = http_build_query($postArgs);

    $curl = curl_init($suitcrm_service_url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    $response = curl_exec($curl);
    if ($response === false) {
        die("Request failed.\n");
    }

    if (!is_array($response)) {
        $response = safe_json_decode($response, TRUE);
    }


    return $response;
}

function RSLR_mauticContact($firstname, $lastname, $emailId, $compName)
{
    global $mauticURL;
    global $mauticKEY;

    $title = $_SESSION['user']['position'];
    $mauticphone = $_SESSION['user']['phone'];
    $mauticmobile = $_SESSION['user']['mobile'];
    $campaign_name = $_SESSION['user']['mcampaign'];
    $website = $_SESSION['user']['mwebsite'];

    $curDate = date("Y-m-d h:m");
    $contractEnd = Date("Y-m-d h:m", strtotime('+365 days'));

    $url = $mauticURL . 'service.php';
    $data = array(
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $emailId,
        'position' => $title,
        'phone' => $mauticphone,
        'mobile' => $mauticmobile,
        'company' => $compName,
        'campaign_name' => $campaign_name,
        'website' => $website,
        'campaign_source' => 'website',
        'ipAddress' => $_SERVER['REMOTE_ADDR'],
        'action' => 'createContact',
        'trial_start_date' => $curDate,
        'trial_end_date' => $contractEnd,
        'customer_stage' => 'trialing'
    );
    $response = post($data, $url);
    return $response;
}

function RSLR_mauticcontacttosegment($contId)
{
    global $mauticURL;
    global $mauticKEY;
    global $matic_segmentId;
    $url = $mauticURL . 'service.php';
    $data = array(
        'contactid' => $contId,
        'segmentid' => $matic_segmentId,
        'action' => 'attachContact'
    );
    $response = post($data, $url);
}

function post($data, $url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function triggerSignDoc($ch_id, $emailid, $firstName)
{
}

function CRM_GetResellerCustomers($CustomerType, $conn, $loggedEid)
{

    $sql = "SELECT eid,companyName FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE ctype = '$CustomerType' and channelId='$loggedEid'";

    $res = find_many($sql, $conn);
    if (safe_count($res) > 0) {
        $result = "";
        echo '<select onchange="GetcustomerSites();" class="form-control dropdown-submenu CRMcustomerlists" data-container="body" id="custName" data-size="5" >';
        echo '<option value="">Please select Customer</option>';
        echo '<option value="all">All</option>';
        foreach ($res as $value) {
            $eid = $value['eid'];
            $companyName = $value['companyName'];
            echo '<option value="' . $eid . '" >' . $companyName . '</option>';
        }


        echo '</select>'
            . '<span style="float: right;color:red;padding-right: 8px;">*</span><span class="error error_required" id="crm_site_required"> *</span>';
    } else {
        echo '<select onchange="GetcustomerSites();" class="form-control dropdown-submenu CRMcustomerlists" data-container="body" id="custName" data-size="5" >';
        echo '<option value="">No Customer available</option>';
    }
}

function CRMDEtails_Getsitelists($db, $custType, $cid)
{

    if (($custType == 5) || ($custType == '5')) {

        $sqlsnow = "select s.siteNames from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure s where chid='$cid' order by siteNames desc";
        $sqlres = find_many($sqlsnow, $db);
        if (safe_count($sqlres) > 0) {
            echo '<select class="form-control siteNames-List_reseller"  id="siteNames-List" style="width: 93%;" onchange="getTicketLists_Onchange()">';
            foreach ($sqlres as $key => $value) {
                $sitelist = $value['siteNames'];
                if (strpos($sitelist, ',') !== false) {

                    $resArr = explode(",", $sitelist);
                    for ($i = 0; $i < safe_count($resArr); $i++) {
                        $siteNaming = explode("__", $resArr[$i]);
                        $str = $siteNaming[0];
                        if (strpos($str, '__') !== false) {

                            $rs = explode("__", $str);
                            $count = safe_count($rs);
                            $resp = array();
                            for ($i = 0; $i < $count; $i++) {
                                if (is_numeric($rs[$i])) {
                                } else {
                                    $resp[] .= $rs[$i];
                                }
                            }
                            $str = $siteName;
                            if (strpos($str, '_') !== false) {

                                $rs = explode("_", $str);
                                $count = safe_count($rs);
                                $resp = array();
                                for ($i = 0; $i < $count; $i++) {
                                    if (is_numeric($rs[$i])) {
                                    } else {
                                        $resp[] .= $rs[$i];
                                    }
                                }
                                $siteName = implode(" ", $resp);
                            }
                            $siteName = implode(" ", $resp);
                        } elseif (strpos($str, '_') !== false) {

                            $rs = explode("_", $str);
                            $count = safe_count($rs);
                            $resp = array();
                            for ($i = 0; $i < $count; $i++) {
                                if (is_numeric($rs[$i])) {
                                } else {
                                    $resp[] .= $rs[$i];
                                }
                            }
                            $siteName = implode(" ", $resp);
                        } else {
                            $siteName = $siteNaming[0];
                        }

                        echo '<option value="' . $sitelist . '">' . $siteName . '</option>';
                    }
                }
                if (strpos($sitelist, ',') == false) {
                    $sitelist = $value['siteNames'];
                    $siteNaming = explode("__", $sitelist);
                    $str = $siteNaming[0];
                    if (strpos($str, '__') !== false) {

                        $rs = explode("__", $str);
                        $count = safe_count($rs);
                        $resp = array();
                        for ($i = 0; $i < $count; $i++) {
                            if (is_numeric($rs[$i])) {
                            } else {
                                $resp[] .= $rs[$i];
                            }
                        }
                        $str = $siteName;
                        if (strpos($str, '_') !== false) {

                            $rs = explode("_", $str);
                            $count = safe_count($rs);
                            $resp = array();
                            for ($i = 0; $i < $count; $i++) {
                                if (is_numeric($rs[$i])) {
                                } else {
                                    $resp[] .= $rs[$i];
                                }
                            }
                            $siteName = implode(" ", $resp);
                        }
                        $siteName = implode(" ", $resp);
                    } elseif (strpos($str, '_') !== false) {

                        $rs = explode("_", $str);
                        $count = safe_count($rs);
                        $resp = array();
                        for ($i = 0; $i < $count; $i++) {
                            if (is_numeric($rs[$i])) {
                            } else {
                                $resp[] .= $rs[$i];
                            }
                        }
                        $siteName = implode(" ", $resp);
                    } else {
                        $siteName = $siteNaming[0];
                    }

                    echo '<option value="' . $sitelist . '">' . $siteName . '</option>';
                }
            }
            echo '</select>';
        } else {
            echo '<select class="selectpicker" id="siteNames-List">'
                . '<option value="">No Sites</option>'
                . '<select>';
        }
    } else if (($custType == 2) || ($custType == '2')) {
        $sql = "SELECT eid FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE ctype = '5' and channelId='$cid'";

        $res = find_many($sql, $db);
        if (safe_count($res) > 0) {
            $eid = "";
            foreach ($res as $value) {
                $eid .= "'" . $value['eid'] . "',";
            }
            $eidLists = rtrim($eid, ",");

            $sqlsnow = "select s.siteNames from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure s where chid in($eidLists) order by siteNames desc";

            $sqlres = find_many($sqlsnow, $db);
            if (safe_count($sqlres) > 0) {
                echo '<select class="form-control siteNames-List_reseller" id="siteNames-List" style="width: 93%;" onchange="getTicketLists_Onchange()">';
                foreach ($sqlres as $key => $values) {
                    $siteNames = $values['siteNames'];
                    if (strpos($siteNames, ',') !== false) {


                        $resArr = explode(",", $siteNames);
                        for ($i = 0; $i < safe_count($resArr); $i++) {
                            $siteNaming = explode("__", $resArr[$i]);

                            $str = $siteNaming[0];
                            if (strpos($str, '__') !== false) {

                                $rs = explode("__", $str);
                                $count = safe_count($rs);
                                $resp = array();
                                for ($i = 0; $i < $count; $i++) {
                                    if (is_numeric($rs[$i])) {
                                    } else {
                                        $resp[] .= $rs[$i];
                                    }
                                }
                                $str = $siteName;
                                if (strpos($str, '_') !== false) {

                                    $rs = explode("_", $str);
                                    $count = safe_count($rs);
                                    $resp = array();
                                    for ($i = 0; $i < $count; $i++) {
                                        if (is_numeric($rs[$i])) {
                                        } else {
                                            $resp[] .= $rs[$i];
                                        }
                                    }
                                    $siteName = implode(" ", $resp);
                                }
                                $siteName = implode(" ", $resp);
                            } elseif (strpos($str, '_') !== false) {

                                $rs = explode("_", $str);
                                $count = safe_count($rs);
                                $resp = array();
                                for ($i = 0; $i < $count; $i++) {
                                    if (is_numeric($rs[$i])) {
                                    } else {
                                        $resp[] .= $rs[$i];
                                    }
                                }
                                $siteName = implode(" ", $resp);
                            } else {
                                $siteName = $siteNaming[0];
                            }

                            echo '<option value="' . $sitelist . '">' . $siteName . '</option>';
                        }
                    }
                    if (strpos($siteNames, ',') == false) {

                        $sitelist = $values['siteNames'];
                        $siteNaming = explode("__", $sitelist);

                        $str = $siteNaming[0];
                        if (strpos($str, '__') !== false) {

                            $rs = explode("__", $str);
                            $count = safe_count($rs);
                            $resp = array();
                            for ($i = 0; $i < $count; $i++) {
                                if (is_numeric($rs[$i])) {
                                } else {
                                    $resp[] .= $rs[$i];
                                }
                            }
                            $str = $siteName;
                            if (strpos($str, '_') !== false) {

                                $rs = explode("_", $str);
                                $count = safe_count($rs);
                                $resp = array();
                                for ($i = 0; $i < $count; $i++) {
                                    if (is_numeric($rs[$i])) {
                                    } else {
                                        $resp[] .= $rs[$i];
                                    }
                                }
                                $siteName = implode(" ", $resp);
                            }
                            $siteName = implode(" ", $resp);
                        } elseif (strpos($str, '_') !== false) {

                            $rs = explode("_", $str);
                            $count = safe_count($rs);
                            $resp = array();
                            for ($i = 0; $i < $count; $i++) {
                                if (is_numeric($rs[$i])) {
                                } else {
                                    $resp[] .= $rs[$i];
                                }
                            }
                            $siteName = implode(" ", $resp);
                        } else {
                            $siteName = $siteNaming[0];
                        }

                        echo '<option value="' . $sitelist . '">' . $siteName . '</option>';
                    }
                }
                echo '</select>';
            } else {
                echo '<select class="selectpicker" id="siteNames-List">'
                    . '<option value="">No Sites</option>'
                    . '<select>';
            }
        } else {
            echo '<select class="selectpicker">'
                . '<option value="">No Sites</option>'
                . '</select>';
        }
    }
}

function CRMDEtails_Getsitelists_Configured($custSiteName, $db, $custType, $cid)
{

    if (($custType == 5) || ($custType == '5')) {

        $sqlsnow = "select s.siteNames from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure s where chid='$cid' order by siteNames desc";

        $sqlres = find_many($sqlsnow, $db);
        if (safe_count($sqlres) > 0) {
            echo '<select class="form-control" id="siteNames-List" style="width: 93%;" onchange="getTicketLists_Onchange()">';
            foreach ($sqlres as $key => $value) {
                $sitelist = $value['siteNames'];
                if (strpos($sitelist, ',') !== false) {

                    $resArr = explode(",", $sitelist);
                    for ($i = 0; $i < safe_count($resArr); $i++) {
                        if ($custSiteName == $resArr[$i]) {
                            $selected = "selected";
                        } else {
                            $selected = "";
                        }
                        $siteNaming = explode("__", $resArr[$i]);
                        $str = $siteNaming[0];
                        if (strpos($str, '__') !== false) {

                            $rs = explode("__", $str);
                            $count = safe_count($rs);
                            $resp = array();
                            for ($i = 0; $i < $count; $i++) {
                                if (is_numeric($rs[$i])) {
                                } else {
                                    $resp[] .= $rs[$i];
                                }
                            }
                            $str = $siteName;
                            if (strpos($str, '_') !== false) {

                                $rs = explode("_", $str);
                                $count = safe_count($rs);
                                $resp = array();
                                for ($i = 0; $i < $count; $i++) {
                                    if (is_numeric($rs[$i])) {
                                    } else {
                                        $resp[] .= $rs[$i];
                                    }
                                }
                                $siteName = implode(" ", $resp);
                            }
                            $siteName = implode(" ", $resp);
                        } elseif (strpos($str, '_') !== false) {

                            $rs = explode("_", $str);
                            $count = safe_count($rs);
                            $resp = array();
                            for ($i = 0; $i < $count; $i++) {
                                if (is_numeric($rs[$i])) {
                                } else {
                                    $resp[] .= $rs[$i];
                                }
                            }
                            $siteName = implode(" ", $resp);
                        } else {
                            $siteName = $siteNaming[0];
                        }

                        echo '<option value="' . $sitelist . '">' . $siteName . '</option>';
                    }
                }
                if (strpos($sitelist, ',') == false) {
                    $sitelist = $value['siteNames'];
                    if ($custSiteName == $sitelist) {
                        $selected = "selected";
                    } else {
                        $selected = "";
                    }
                    $siteNaming = explode("__", $sitelist);


                    $str = $siteNaming[0];
                    if (strpos($str, '__') !== false) {

                        $rs = explode("__", $str);
                        $count = safe_count($rs);
                        $resp = array();
                        for ($i = 0; $i < $count; $i++) {
                            if (is_numeric($rs[$i])) {
                            } else {
                                $resp[] .= $rs[$i];
                            }
                        }
                        $str = $siteName;
                        if (strpos($str, '_') !== false) {

                            $rs = explode("_", $str);
                            $count = safe_count($rs);
                            $resp = array();
                            for ($i = 0; $i < $count; $i++) {
                                if (is_numeric($rs[$i])) {
                                } else {
                                    $resp[] .= $rs[$i];
                                }
                            }
                            $siteName = implode(" ", $resp);
                        }
                        $siteName = implode(" ", $resp);
                    } elseif (strpos($str, '_') !== false) {

                        $rs = explode("_", $str);
                        $count = safe_count($rs);
                        $resp = array();
                        for ($i = 0; $i < $count; $i++) {
                            if (is_numeric($rs[$i])) {
                            } else {
                                $resp[] .= $rs[$i];
                            }
                        }
                        $siteName = implode(" ", $resp);
                    } else {
                        $siteName = $siteNaming[0];
                    }

                    echo '<option value="' . $sitelist . '">' . $siteName . '</option>';
                }
            }
            echo '</select>';
        } else {
            echo '<select class="selectpicker" id="siteNames-List">'
                . '<option value="">No Sites</option>'
                . '<select>';
        }
    } else if (($custType == 2) || ($custType == '2')) {
        $sql = "SELECT eid FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE ctype = '5' and channelId='$cid'";

        $res = find_many($sql, $db) or die(mysql_error($conn) . "mysql error CRMDEtails_Getsitelists");

        if (safe_count($res) > 0) {
            $eid = "";
            foreach ($res as $value) {
                $eid .= "'" . $value['eid'] . "',";
            }
            $eidLists = rtrim($eid, ",");

            $sqlsnow = "select s.siteNames from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure s where chid in($eidLists) order by siteNames desc";


            $sqlres = find_many($sqlsnow, $db);
            if (safe_count($sqlres) > 0) {
                echo '<select class="form-control" id="siteNames-List" style="width: 93%;" onchange="getTicketLists_Onchange()">';
                foreach ($sqlres as $key => $values) {
                    $siteNames = $values['siteNames'];
                    if (strpos($siteNames, ',') !== false) {

                        $resArr = explode(",", $siteNames);
                        for ($i = 0; $i < safe_count($resArr); $i++) {

                            if ($custSiteName == $resArr[$i]) {
                                $selected = "selected";
                            } else {
                                $selected = "";
                            }
                            $siteNaming = explode("__", $resArr[$i]);


                            $str = $siteNaming[0];
                            if (strpos($str, '__') !== false) {

                                $rs = explode("__", $str);
                                $count = safe_count($rs);
                                $resp = array();
                                for ($i = 0; $i < $count; $i++) {
                                    if (is_numeric($rs[$i])) {
                                    } else {
                                        $resp[] .= $rs[$i];
                                    }
                                }
                                $str = $siteName;
                                if (strpos($str, '_') !== false) {

                                    $rs = explode("_", $str);
                                    $count = safe_count($rs);
                                    $resp = array();
                                    for ($i = 0; $i < $count; $i++) {
                                        if (is_numeric($rs[$i])) {
                                        } else {
                                            $resp[] .= $rs[$i];
                                        }
                                    }
                                    $siteName = implode(" ", $resp);
                                }
                                $siteName = implode(" ", $resp);
                            } elseif (strpos($str, '_') !== false) {

                                $rs = explode("_", $str);
                                $count = safe_count($rs);
                                $resp = array();
                                for ($i = 0; $i < $count; $i++) {
                                    if (is_numeric($rs[$i])) {
                                    } else {
                                        $resp[] .= $rs[$i];
                                    }
                                }
                                $siteName = implode(" ", $resp);
                            } else {
                                $siteName = $siteNaming[0];
                            }

                            echo '<option value="' . $sitelist . '">' . $siteName . '</option>';
                        }
                    }
                    if (strpos($siteNames, ',') == false) {
                        $sitelist = $values['siteNames'];
                        if ($custSiteName == $sitelist) {
                            $selected = "selected";
                        } else {
                            $selected = "";
                        }
                        $siteNaming = explode("__", $sitelist);

                        $str = $siteNaming[0];
                        if (strpos($str, '__') !== false) {

                            $rs = explode("__", $str);
                            $count = safe_count($rs);
                            $resp = array();
                            for ($i = 0; $i < $count; $i++) {
                                if (is_numeric($rs[$i])) {
                                } else {
                                    $resp[] .= $rs[$i];
                                }
                            }
                            $str = $siteName;
                            if (strpos($str, '_') !== false) {

                                $rs = explode("_", $str);
                                $count = safe_count($rs);
                                $resp = array();
                                for ($i = 0; $i < $count; $i++) {
                                    if (is_numeric($rs[$i])) {
                                    } else {
                                        $resp[] .= $rs[$i];
                                    }
                                }
                                $siteName = implode(" ", $resp);
                            }
                            $siteName = implode(" ", $resp);
                        } elseif (strpos($str, '_') !== false) {

                            $rs = explode("_", $str);
                            $count = safe_count($rs);
                            $resp = array();
                            for ($i = 0; $i < $count; $i++) {
                                if (is_numeric($rs[$i])) {
                                } else {
                                    $resp[] .= $rs[$i];
                                }
                            }
                            $siteName = implode(" ", $resp);
                        } else {
                            $siteName = $siteNaming[0];
                        }

                        echo '<option value="' . $sitelist . '">' . $siteName . '</option>';
                    }
                }
                echo '</select>';
            } else {
                echo '<select class="selectpicker" id="siteNames-List">'
                    . '<option value="">No Sites</option>'
                    . '<select>';
            }
        } else {
            echo '<select class="selectpicker">'
                . '</select>';
        }
    }
}

function CRM_GetReseller_CustomersSites($conn, $Cid)
{


    $sql = "select siteName from " . $GLOBALS['PREFIX'] . "agent.customerOrder O where O.compId='$Cid'";

    $res = find_many($sql, $conn);

    if (safe_count($res) > 0) {
        $result = "";
        $result .= ' <label for="user-name" class="col-sm-3 align-label">Sites</label>
                                            <div class="col-sm-9" >
                                            <select class="form-control dropdown-submenu" data-container="body" id="custSiteNameList" data-size="5" onchange="getcrmDetails_Onchange()">';
        $result .= '<option value="">Please select SiteName</option>';
        foreach ($res as $value) {
            $siteName = $value['siteName'];
            $siteexp = explode("__", $siteName);
            if (strpos($siteexp[0], '_') !== false) {
                $siteexpVal = str_replace("_", " ", $siteexp[0]);
            } else {
                $siteexpVal = $siteexp[0];
            }
            $result .= '<option value="' . $siteName . '" >' . $siteexpVal . '</option>';
        }

        $result .= '</select></div>'
            . '<span class="error error_required" id="crm_siteName_required" style="padding-right: 14px;"> *</span>';
        echo $result;
    } else {
        echo $result = "";
    }
}

function CRM_GetcrmDetails_CustomersSites($conn, $Cid)
{
    $crmData = getcrmDetails($Cid, $conn);
    $response = array("crmIP" => $crmData['crmIP'], "crmUsername" => $crmData['crmUsername'], "crmPassword" => $crmData['crmPassword'], "JsonData" => $crmData['jsonData'], "jsonCloseData" => $crmData['jsonCloseData'], "autoheal" => $crmData['autoheal'], "notification" => $crmData['notification']);
    return $response;
}

function getcrmDetails($Cid, $conn)
{

    $sql = "select * from " . $GLOBALS['PREFIX'] . "agent.channel where sitelist='$Cid' and syncAssetData='compucom' limit 1";
    $row = find_one($sql, $conn);
    $count = safe_count($row);
    $crmData = array();

    if ($count > 0) {

        $crmData['crmIP'] = $row['crmIP'];
        $crmData['crmUsername'] = $row['crmUsername'];
        $crmData['crmPassword'] = $row['crmPassword'];
        $json = getJsonData($Cid, $conn);
        $crmData['jsonData'] = $json['jsonData'];
        $crmData['jsonCloseData'] = $json['jsonCloseData'];
        $crmData['autoheal'] = $json['autoheal'];
        $crmData['notification'] = $json['notification'];
    } else {
        if (($crmData['crmIP'] = '0') || ($crmData['crmIp']) == 0) {
            $crmData['crmIP'] = "";
        } else {
            $crmData['crmIP'] = "";
        }
        if (($crmData['crmUsername'] = '0') || ($crmData['crmUsername']) == 0) {
            $crmData['crmUsername'] = "";
        } else {
            $crmData['crmUsername'] = "";
        }
        if (($crmData['crmPassword'] = '0') || ($crmData['crmPassword']) == 0) {
            $crmData['crmPassword'] = "";
        } else {
            $crmData['crmPassword'] = "";
        }
        $crmData['jsonData'] = '{  
"docType":"Nanoheal Enterprise",
"transactionId":"%%",
"timeStamp":"%%",
"sender":"Nanoheal Enterprise",
"client":"CompuCom Systems, Inc.",
"refCaseNumber":"NH_ID",
"openedDateStamp":"%%",
"transDateStamp":"%%",
"shortDescription":"%%",
"problemDescription":"%%",
"statusCode":1,
"priorityCode":"%%",
"impactCode":2,
"urgencyCode":2,
"supportGroup":"%%",
"category":"Incident",
"subCategory":"Reporting",
"notes":"%%",
"contactType": "%%",
"contact":{  
      "company":"CompuCom Systems, Inc."
  },
 "equipment":{  
      "model":"%%",
      "description":"%%"
 },

"internalNotes":"%1234%"
}';
        $crmData['jsonCloseData'] = '{  
   "docType":"Nanoheal Enterprise",
   "client":"CompuCom Systems, Inc.",
   "transactionId":"%%",
   "timeStamp":"%%",
   "sender":"Nanoheal Enterprise",
   "caseNumber": "%%",
   "refCaseNumber": "%%",
   "transDateStamp":"%%",
   "resolution":{  
      "text":"Issue resolved by nanoheal DART 7888",
      "code":"Resolved - Full Restoration",
      "timeStamp":"%%"
   },
   "statusCode":6,
   "problemDescription":"%%"
}';
        $crmData['autoheal'] = "null";
        $crmData['notification'] = "null";
    }



    return $crmData;
}

function CRM_SiteJsonData($customerSite, $db)
{
    if (($customerSite == "") || ($customerSite == 'undefined') || empty($customerSite)) {
        if (($crmData['crmIP'] = '0') || ($crmData['crmIp']) == 0) {
            $crmData['crmIP'] = "";
        } else {
            $crmData['crmIP'] = "";
        }
        if (($crmData['crmUsername'] = '0') || ($crmData['crmUsername']) == 0) {
            $crmData['crmUsername'] = "";
        } else {
            $crmData['crmUsername'] = "";
        }
        if (($crmData['crmPassword'] = '0') || ($crmData['crmPassword']) == 0) {
            $crmData['crmPassword'] = "";
        } else {
            $crmData['crmPassword'] = "";
        }
        $crmData['jsonData'] = '{  
"docType":"HIRO",
"transactionId":"%%",
"timeStamp":"%%",
"sender":"EUO",
"client":"CompuCom Systems, Inc.",
"refCaseNumber":"%%",
"openedDateStamp":"%%",
"transDateStamp":"%%",
"shortDescription":"%%",
"problemDescription":"%%",
"statusCode":1,
"priorityCode":"%%",
"impactCode":2,
"urgencyCode":2,
"supportGroup":"%%",
"category":"Incident",
"subCategory":"Reporting",
"contact":{  
      "company":"CompuCom Systems, Inc."
  },
 "equipment":{  
      "model":"%%",
      "description":"%%"
 },
"notes":"%%",
"internalNotes":"%default%"
}';
    } else {
        $crmData = getcrmDetails($Cid, $db);
        $sitecust = getSiteandCustomerList($Cid, $db);


        $response = array(
            "Sitelist" => $sitecust['siteList'], "Customerlist" => $sitecust['customerList'], "crmIP" => $crmData['crmIP'],
            "crmUsername" => $crmData['crmUsername'], "crmPassword" => $crmData['crmPassword'], "JsonData" => $crmData['jsonData']
        );
    }
    return $response;
}

function CRMDTLS_GetummaryDtls($conn, $loggedEid, $CustomerType)
{

    if (($CustomerType == 2) || ($CustomerType == '2')) {
        $sql = "SELECT eid,companyName FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE ctype = '5' and channelId='$loggedEid' and syncAssetData='compucom'";
        $res = find_many($sql, $conn) or die(mysql_error() . "mysql error CRM_GetummaryDtls");
        if (safe_count($res) > 0) {
            foreach ($res as $value) {
                $eid .= "'" . $value['eid'] . "',";
                $companyName = $value['companyName'];
            }
            $eidList = rtrim($eid, ",");

            $sqlsts = "select id,siteNames,autoheal,notification,tcktcreation,selfhelp,schedule from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where chid in ($eidList)";
            $sqlqry = find_many($sqlsts, $conn);
            foreach ($sqlqry as $values) {
                $id = $values['id'];
                $autoheal = $values['autoheal'];
                $selfhelp = $values['selfhelp'];
                $schedule = $values['schedule'];
                $siteName = explode("__", $values['siteNames']);
                $siteNames = $siteName[0];
                $notification = $values['notification'];
                $ticketcreatn = $values['tcktcreation'];
                if ($ticketcreatn == 'enabled') {
                    $ticketstaus = "enabled";
                } elseif ($ticketcreatn == 'disabled') {
                    $ticketstaus = "disabled";
                }

                if (($autoheal == 1) || ($autoheal == '1')) {
                    $autohealstaus = "enabled";
                } elseif (($autoheal == 0) || ($autoheal == '0')) {
                    $autohealstaus = "disabled";
                }
                if (($selfhelp == 1) || ($selfhelp == '1')) {
                    $selfhelpstaus = "enabled";
                } elseif (($selfhelp == 0) || ($selfhelp == '0')) {
                    $selfhelpstaus = "disabled";
                }
                if (($schedule == 1) || ($schedule == '1')) {
                    $schedulestaus = "enabled";
                } elseif (($schedule == 0) || ($schedule == '0')) {
                    $schedulestaus = "disabled";
                }

                if (($notification == 1) || ($notification == '1')) {
                    $notificationstaus = "enabled";
                } elseif (($notification == 0) || ($notification == '0')) {
                    $notificationstaus = "disabled";
                }
                $siteNames1 = '<p class="ellipsis" id="' . $siteNames . '" value="' . $siteNames . '" title="' . $siteNames . '">' . $siteNames . '</p>';
                $companyName1 = '<p class="ellipsis" id="' . $companyName . '" value="' . $companyName . '" title="' . $companyName . '">' . $companyName . '</p>';
                $notificationstausRes1 = '<p class="ellipsis" id="' . $notificationstaus . '" value="' . $notificationstaus . '" title="' . $notificationstaus . '">' . $notificationstaus . '</p>';
                $selfhelpRes1 = '<p class="ellipsis" id="' . $selfhelpstaus . '" value="' . $selfhelpstaus . '" title="' . $selfhelpstaus . '">' . $selfhelpstaus . '</p>';
                $schedulestaus1 = '<p class="ellipsis" id="' . $schedulestaus . '" value="' . $schedulestaus . '" title="' . $schedulestaus . '">' . $schedulestaus . '</p>';
                $autohealstausRes1 = '<p class="ellipsis" id="' . $autohealstaus . '" value="' . $autohealstaus . '" title="' . $autohealstaus . '">' . $autohealstaus . '</p>';
                $ticketstaus1 = '<p class="ellipsis" id="' . $ticketstaus . '" value="' . $ticketstaus . '" title="' . $ticketstaus . '">' . $ticketstaus . '</p>';
                $recordListData[] = array("DT_RowId" => $id, $siteNames1, $autohealstausRes1, $notificationstausRes1, $selfhelpRes1, $schedulestaus1, $ticketstaus1);
            }
        } else {
            $recordListData[] = "";
        }
    } elseif (($CustomerType == 5) || ($CustomerType == '5')) {
        $sql = "SELECT eid,companyName FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE ctype = '5' and eid='$loggedEid' and syncAssetData='compucom'";
        $res = find_many($sql, $conn) or die(mysql_error() . "mysql error CRM_GetummaryDtls");
        if (safe_count($res) > 0) {
            foreach ($res as $value) {
                $eid .= "'" . $value['eid'] . "',";
                $companyName = $value['companyName'];
            }
            $eidList = rtrim($eid, ",");
            $sqlsts = "select id,siteNames,autoheal,notification,tcktcreation,selfhelp,schedule from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where chid in($eidList)";
            $sqlqry = find_many($sqlsts, $conn);
            foreach ($sqlqry as $values) {
                $id = $values['id'];
                $autoheal = $values['autoheal'];
                $selfhelp = $values['selfhelp'];
                $schedule = $values['schedule'];
                $siteName = explode("__", $values['siteNames']);

                $siteNames = $siteName[0];

                $notification = $values['notification'];
                $ticketcreatn = $values['tcktcreation'];

                if (($autoheal == 1) || ($autoheal == '1')) {
                    $autohealstaus = "enabled";
                } elseif (($autoheal == 0) || ($autoheal == '0')) {
                    $autohealstaus = "disabled";
                }
                if ($ticketcreatn == 'enabled') {
                    $ticketstaus = "enabled";
                } elseif ($ticketcreatn == 'disabled') {
                    $ticketstaus = "disabled";
                }
                if (($selfhelp == 1) || ($selfhelp == '1')) {
                    $selfhelpstaus = "enabled";
                } elseif (($selfhelp == 0) || ($selfhelp == '0')) {
                    $selfhelpstaus = "disabled";
                }
                if (($schedule == 1) || ($schedule == '1')) {
                    $schedulestaus = "enabled";
                } elseif (($schedule == 0) || ($schedule == '0')) {
                    $schedulestaus = "disabled";
                }
                if (($notification == 1) || ($notification == '1')) {
                    $notificationstaus = "enabled";
                } elseif (($notification == 0) || ($notification == '0')) {
                    $notificationstaus = "disabled";
                }
                $siteNames1 = '<p class="ellipsis" id="' . $siteNames . '" value="' . $siteNames . '" title="' . $siteNames . '">' . $siteNames . '</p>';
                $companyName1 = '<p class="ellipsis" id="' . $companyName . '" value="' . $companyName . '" title="' . $companyName . '">' . $companyName . '</p>';
                $notificationstausRes1 = '<p class="ellipsis" id="' . $notificationstaus . '" value="' . $notificationstaus . '" title="' . $notificationstaus . '">' . $notificationstaus . '</p>';
                $selfhelpRes1 = '<p class="ellipsis" id="' . $selfhelpstaus . '" value="' . $selfhelpstaus . '" title="' . $selfhelpstaus . '">' . $selfhelpstaus . '</p>';
                $schedulestaus1 = '<p class="ellipsis" id="' . $schedulestaus . '" value="' . $schedulestaus . '" title="' . $schedulestaus . '">' . $schedulestaus . '</p>';
                $autohealstausRes1 = '<p class="ellipsis" id="' . $autohealstaus . '" value="' . $autohealstaus . '" title="' . $autohealstaus . '">' . $autohealstaus . '</p>';
                $ticketstaus1 = '<p class="ellipsis" id="' . $ticketstaus . '" value="' . $ticketstaus . '" title="' . $ticketstaus . '">' . $ticketstaus . '</p>';
                $recordListData[] = array("DT_RowId" => $id, $siteNames1, $autohealstausRes1, $notificationstausRes1, $selfhelpRes1, $schedulestaus1, $ticketstaus1);
            }
        } else {
            $recordListData[] = "";
        }
    }
    return $recordListData;
}

function CRMDTLS_editconfigs($selectedDataId, $conn, $Cid)
{

    db_change($GLOBALS['PREFIX'] . "event", $conn);
    $qry = "select autoheal,notification,tcktcreation,selfhelp,schedule from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where id='$selectedDataId'";
    $sql = find_one($qry, $conn);
    if (safe_count($sql) > 0) {
        $data['autoheal'] = $sql['autoheal'];
        $data['notification'] = $sql['notification'];
        $data['selfhelp'] = $sql['selfhelp'];
        $data['schedule'] = $sql['schedule'];
        $data['tcktcreation'] = $sql['tcktcreation'];
    } else {
        $data['autoheal'] = "";
        $data['notification'] = "";
        $data['selfhelp'] = "";
        $data['schedule'] = "";
        $data['tcktcreation'] = "";
    }
    return $data;
}

function CRMDTLS_updateconfigs($selectedChks, $editID_configs, $conn, $Cid)
{

    $ticktcrtn = 'disabled';
    $autoheal = 0;
    $notif = 0;
    $selfhelp = 0;
    $schedule = 0;

    $selectedchckboxs = safe_json_decode($selectedChks, true);

    foreach ($selectedchckboxs as $key => $value) {
        if ($key == 'edit_ticketcrtn') {
            $ticktcrtn = 'enabled';
        } elseif ($key == 'edit_autohealcheck') {
            $autoheal = 1;
        } elseif ($key == 'edit_notifcheck') {
            $notif = 1;
        } elseif ($key == 'edit_selfhelpcheck') {
            $selfhelp = 1;
        } elseif ($key == 'edit_schedulecheck') {
            $schedule = 1;
        }
    }




    $sql = "Update  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure A set A.autoheal='" . $autoheal . "',A.notification='" . $notif . "',A.tcktcreation='" . $ticktcrtn . "',A.selfhelp='" . $selfhelp . "',A.schedule='" . $schedule . "'
where A.id='" . $editID_configs . "'";


    $result = redcommand($sql, $conn) or die(mysql_error() . "error in inserting data");
    if ($result) {
        $resResp = "success";
    } else {
        $resResp = "failed";
    }
    return $resResp;
}

function getSiteandCustomerList($siteName, $db)
{
    $custType = $_SESSION['user']['customerType'];
    if (($custType == 5) || ($custType == '5')) {

        $sqlsnow = "select s.siteNames from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure s where chid='$cid' order by siteNames desc";
        $sqlres = find_many($sqlsnow, $db);
        if (safe_count($sqlres) > 0) {
            $result .= '<select class="form-control" id="siteNames-List" style="width: 93%;" onchange="getTicketLists_Onchange()">';
            foreach ($sqlres as $key => $value) {
                $sitelist = $value['siteNames'];
                if (strpos($sitelist, ',') !== false) {

                    $resArr = explode(",", $sitelist);
                    for ($i = 0; $i < safe_count($resArr); $i++) {
                        $siteNaming = explode("__", $resArr[$i]);
                        if ($siteNaming[0] == $siteName) {
                            $selected = "selected";
                        } else {
                            $selected = '';
                        }
                        $result .= '<option ' . $selected . ' value="' . $resArr[$i] . '">' . $siteNaming[0] . '</option>';
                    }
                }
                if (strpos($sitelist, ',') == false) {
                    $sitelist = $value['siteNames'];
                    $siteNaming = explode("__", $sitelist);
                    if ($siteNaming[0] == $siteName) {
                        $selected = "selected";
                    } else {
                        $selected = '';
                    }
                    $result .= '<option ' . $selected . ' value="' . $sitelist . '">' . $siteNaming[0] . '</option>';
                }
            }
            $result .= '</select>';
        } else {
            $result .= '<select class="selectpicker" id="siteNames-List">'
                . '<option value="">No Sites</option>'
                . '<select>';
        }
    } else if (($custType == 2) || ($custType == '2')) {
        $sql = "SELECT eid,channelId FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE sitelist = '$siteName'";
        $res = find_many($sql, $db) or die(mysql_error($db) . "mysql error getSiteandCustomerList");

        if (safe_count($res) > 0) {
            $eid = "";
            foreach ($res as $value) {
                $chId = $value['channelId'];
                $Selectedeid = $value['eid'];
            }
            $eidLists = rtrim($eid, ",");

            $sqlsnow = "select s.eid,s.companyName from " . $GLOBALS['PREFIX'] . "agent.channel s where channelId in($chId) order by eid asc";

            $sqlres = find_many($sqlsnow, $db);
            if (safe_count($sqlres) > 0) {
                $customer_List .= '<select onchange="GetcustomerSites();" class="form-control dropdown-submenu CRMcustomerlists" data-container="body" id="custName" data-size="5" localized="">';
                foreach ($sqlres as $key => $values) {
                    $customer = $values['companyName'];
                    $eid = $values['eid'];

                    if (strpos($customer, ',') == false) {
                        if ($Selectedeid == $eid) {
                            $selected = "selected";
                        } else {
                            $selected = "";
                        }
                        $customer = $values['companyName'];
                        $customerNames = explode("__", $customer);
                        $customer_List .= '<option ' . $selected . ' value="' . $eid . '">' . $customer . '</option>';
                    }
                }
                $customer_List .= '</select>';
            } else {
                $customer_List .= '<select class="selectpicker" id="siteNames-List">'
                    . '<option value="">No Sites</option>'
                    . '<select>';
            }

            $sqlsite = "Select siteName from " . $GLOBALS['PREFIX'] . "agent.customerOrder where compId='$Selectedeid'";
            $sqlresSite = find_many($sqlsite, $db);
            if (safe_count($sqlresSite) > 0) {

                $site_List .= '<select class="form-control dropdown-submenu" data-container="body" id="custSiteNameList" data-size="5" onchange="getcrmDetails_Onchange()" localized="">';
                foreach ($sqlresSite as $key => $values) {
                    $site_Names = $values['siteName'];
                    $eid = $values['eid'];

                    if (strpos($site_Names, ',') == false) {
                        if ($siteName == $site_Names) {
                            $selected = "selected";
                        } else {
                            $selected = "";
                        }

                        $sitelist = $values['siteName'];

                        $siteNams = explode("__", $sitelist);
                        $site_List .= '<option ' . $selected . ' value="' . $sitelist . '">' . $siteNams[0] . '</option>';
                    }
                }
                $site_List .= '</select>';
            } else {
                $site_List .= '<select class="selectpicker" id="siteNames-List">'
                    . '<option value="">No Sites</option>'
                    . '<select>';
            }

            $siteCustData = array("siteList" => $site_List, "customerList" => $customer_List);
        } else {
            $site_List .= '<select class="selectpicker" id="siteNames-List">'
                . '<option value="">No Sites</option>'
                . '<select>';

            $customer_List .= '<select class="selectpicker" id="siteNames-List">'
                . '<option value="">No Sites</option>'
                . '<select>';
            $siteCustData = array("siteList" => $site_List, "customerList" => $customer_List);
        }
    }


    return $siteCustData;
}

function getJsonData($Cid, $conn)
{
    $sql = "select jsonData,jsonCloseData,autoheal,notification from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where siteNames='$Cid' limit 1";
    $row = find_one($sql, $conn);
    $count = safe_count($row);
    $crmData = array();
    if ($count > 0) {
        $jsonData['jsonData'] = trim($row['jsonData']);
        $jsonData['closejsonData'] = trim($row['jsonCloseData']);
        $jsonData['autoheal'] = trim($row['autoheal']);
        $jsonData['notification'] = trim($row['notification']);
    } else {
        $jsonData['jsonData'] = "nojson";
        $jsonData['jsonCloseData'] = "nojson";
        $jsonData['autoheal'] = "nojson";
        $jsonData['notification'] = "nojson";
    }

    return $jsonData;
}

function CRM_GetReseller_singleCustomerSite($conn, $Cid)
{


    $sql = "select siteName from " . $GLOBALS['PREFIX'] . "agent.customerOrder O where O.compId='$Cid'";
    $res = find_many($sql, $conn);

    if (safe_count($res) > 0) {
        $siteName = "";
        foreach ($res as $value) {
            $siteName .= $value['siteName'] . ",";
        }

        echo $msg = rtrim($siteName, ",");
    } else {
        echo $msg = "";
    }
}

function get_CRMMapUI($crmType, $conn, $custId)
{

    $sql = "select * from " . $GLOBALS['PREFIX'] . "asset.cmdbAssetData A where A.cid='$custId' and A.sn_dataname!='' and A.status='1'";

    $res = find_many($sql, $conn);

    $recordList = [];
    if (safe_count($res) > 0) {
        $addSelects = '<select name="proactiveauditGrid_length" aria-controls="proactiveauditGrid" class="" localized=""><option value="10" localized="">10</option><option value="25" localized="">25</option><option value="50" localized="">50</option><option value="100" localized="">100</option></select>';
        foreach ($res as $key => $value) {
            $nh_dataid = $value['nh_dataId'];
            $groupid = $value['groupid'];
            if ($groupid == '1') {
                $category = "Windows Server";
            } elseif ($groupid == '2') {
                $category = "Network Adapter";
            } elseif ($groupid == '3') {
                $category = "Serial Number";
            } else if ($groupid == '4') {
                $category = "Memory Module";
            }
            $nh_dataid = '<p class="ellipsis" id="' . $value['nh_dataId'] . '" value="' . $value['nh_dataId'] . '" title="' . $value['nh_dataId'] . '">' . $value['nh_dataname'] . '</p>';
            $category_type = '<p class="ellipsis" id="' . $category . '" value="' . $category . '" title="' . $category . '">' . $category . '</p>';
            $crm_dataname = '<p class="ellipsis" id="' . $value['sn_dataname'] . '" value="' . $value['sn_dataname'] . '" title="' . $value['sn_dataname'] . '">' . $value['sn_dataname'] . '</p>';
            $recordList[] = array("DT_RowId" => $value['id'], $nh_dataid, $category_type, $crm_dataname);
        }
    } else {
        $noRecord = "No records";
        $recordList[] = array("DT_RowId" => $noRecord, $noRecord, $noRecord, $noRecord);
    }

    return $recordList;
}

function get_SkippedMapUI($crmData, $conn)
{

    $crmType = $_SESSION["user"]["crmType"] . "crm";

    $CRMlogin_value = $crmData['CRMlogin_value'];


    if ($CRMlogin_value == '2') {
        $cid = $_SESSION['user']['cId'];
        $CRMlogin_valueCust = '5';
        $cid = CRM_GetResellerCustomersId($CRMlogin_valueCust, $conn, $cid);
    } elseif ($CRMlogin_value == '5') {
        $cid = $_SESSION['user']['cId'];
    }



    $userConfigStatus = checkUser_configured($CRMlogin_value, $crmType, $cid, $conn);
    $recordList = [];

    if ($userConfigStatus == "unconfigured") {
        $Unconfigured = "Unconfigured";
        $recordList[] = array("DT_RowId" => $Unconfigured, $Unconfigured, $Unconfigured, $Unconfigured);
    } else if ($userConfigStatus == "configured") {


        if ($CRMlogin_value == '2') {
            $where = "A.cid in($cid)";
        } else if ($CRMlogin_value == '5') {
            $where = "cid ='$cid'";
        }

        $sql = "select * from " . $GLOBALS['PREFIX'] . "asset.cmdbAssetData A where $where and A.sn_dataname!='' and A.status='1'";


        $res = find_many($sql, $conn);


        if (safe_count($res) > 0) {
            foreach ($res as $key => $value) {
                $nh_dataid = $value['nh_dataId'];
                $groupid = $value['groupid'];
                if ($groupid == '1') {
                    $category = "Windows Server";
                } elseif ($groupid == '2') {
                    $category = "Network Adapter";
                } elseif ($groupid == '3') {
                    $category = "Serial Number";
                } else if ($groupid == '4') {
                    $category = "Memory Module";
                }
                $nh_dataid = '<p class="ellipsis" id="' . $value['nh_dataId'] . '" value="' . $value['nh_dataId'] . '" title="' . $value['nh_dataId'] . '">' . $value['nh_dataname'] . '</p>';
                $category_type = '<p class="ellipsis" id="' . $category . '" value="' . $category . '" title="' . $category . '">' . $category . '</p>';
                $crm_dataname = '<p class="ellipsis" id="' . $value['sn_dataname'] . '" value="' . $value['sn_dataname'] . '" title="' . $value['sn_dataname'] . '">' . $value['sn_dataname'] . '</p>';
                $recordList[] = array("DT_RowId" => $value['id'], $nh_dataid, $category_type, $crm_dataname);
            }
        } else {
            $noRecord = "No records";
            $recordList[] = array("DT_RowId" => $noRecord, $noRecord, $noRecord, $noRecord);
        }
    }
    return $recordList;
}

function CRM_GetResellerCustomersId($CRMlogin_valueCust, $conn, $cid)
{


    $sql = "SELECT eid,companyName FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE ctype = '$CRMlogin_valueCust' and channelId='$cid'";

    $res = find_many($sql, $conn) or die(mysql_error($conn) . "mysql error CRM_GetResellerCustomersId");

    if (safe_count($res) > 0) {
        $eid = "";

        foreach ($res as $value) {
            $eid .= $value['eid'] . ",";
        }
        $msg1 = "'" . str_replace(",", "','", $eid);
        $msg = rtrim($msg1, ",'") . "'";
    } else {
        $msg = "continue";
    }

    return $msg;
}

function checkUser_configured($CRMlogin_value, $crmType, $cid, $conn)
{



    $crmType = "SN";


    if ($CRMlogin_value == '2') {
        $sql = "SELECT eid,companyName FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE crmType = '$crmType' and eid in($cid)";
    } else if ($CRMlogin_value == '5') {
        $sql = "SELECT eid,companyName FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE crmType = '$crmType' and eid ='$cid'";
    }

    $res = find_many($sql, $conn);

    if (safe_count($res) > 0) {
        $response = "configured";
    } else {
        $response = "unconfigured";
    }

    return $response;
}

function CRM_GetDataLists($conn, $customerId)
{
    $sql = "SELECT D.nh_dataid,D.nh_dataname,D.crm_dataname,D.category_type
FROM " . $GLOBALS['PREFIX'] . "asset.cmdbDataMapping D
LEFT JOIN " . $GLOBALS['PREFIX'] . "asset.cmdbAssetData A ON D.nh_dataid = A.nh_dataid where D.crm_dataname!='' and D.crm_dataname!='0'";



    $res = find_many($sql, $conn);

    if (safe_count($res) > 0) {

        foreach ($res as $value) {
            $nh_dataid = $value['nh_dataid'];
            $nh_dataname = $value['nh_dataname'];
            $category_type = $value['category_type'];
            $crm_dataname = $value['crm_dataname'];
            $dataName .= '<option value="' . $nh_dataid . '">' . $nh_dataname . '</option>';
            $category .= '<option value="' . $nh_dataid . '">' . $category_type . '</option>';
            $crmdataname .= '<option value="' . $nh_dataid . '">' . $crm_dataname . '</option>';
        }

        $dataNameLists = '<select class = "selectpicker selectbox-c NHdataName-selectSection">' . $dataName . '</select>';
        $crmcategoryLists = '<select class = "selectpicker selectbox-c NHdataName-selectSection">' . $category . '</select>';
        $crmDataNameLists = '<select class = "selectpicker selectbox-c NHdataName-selectSection">' . $crmdataname . '</select>';

        $slectLists = array($dataNameLists, $crmcategoryLists, $crmDataNameLists);
    } else {
        $slectLists = "continue";
    }
    return $slectLists;
}

function CRM_GetNHDataLists($conn, $customerId, $categoryId)
{
    $sql = "SELECT D.category_id,D.nh_dataid,D.nh_dataname,D.crm_dataname,D.category_type
FROM " . $GLOBALS['PREFIX'] . "asset.cmdbDataMapping D where D.nh_dataid not in(select A.nh_dataId from " . $GLOBALS['PREFIX'] . "asset.cmdbAssetData A where A.cid='$customerId' and A.groupid='$categoryId') and D.crm_dataname!='';";

    $res = find_many($sql, $conn);

    if (safe_count($res) > 0) {
        echo '<select class="form-control selectpicker dropdown-submenu" data-container="body" onchange="GET_SNDatalists();"  id="nhData_lists" data-size="5" style="display:block !important;padding-left: 29px;">';

        foreach ($res as $value) {
            $nh_dataid = $value['nh_dataid'];
            $nh_dataname = $value['nh_dataname'];
            $category_type = $value['category_type'];
            $crm_dataname = $value['crm_dataname'];
            echo '<option value="' . $nh_dataid . '">' . $nh_dataname . '</option>';
        }
        echo '</select><em class="error addreq" id="req_advusername">*</em>';
    } else {
        echo $slectLists = "continue";
    }
}

function CRM_GetSNDataLists($conn, $customerId, $categoryId)
{
    $sql = "SELECT D.category_id,D.nh_dataid,D.nh_dataname,D.crm_dataname,D.category_type
FROM " . $GLOBALS['PREFIX'] . "asset.cmdbDataMapping D where D.crm_dataname!=''";

    $res = find_many($sql, $conn);

    if (safe_count($res) > 0) {
        echo '<select class="form-control selectpicker dropdown-submenu" data-container="body" id="sn_dataNames" data-size="5" style="display:block !important;padding-left: 29px;">';

        foreach ($res as $value) {
            $nh_dataid = $value['nh_dataid'];
            $nh_dataname = $value['nh_dataname'];
            $category_type = $value['category_type'];
            $crm_dataname = $value['crm_dataname'];
            echo '<option value="' . $crm_dataname . '">' . $crm_dataname . '</option>';
        }
        echo '</select><em class="error addreq" id="req_advusername">*</em>';
    } else {
        echo $slectLists = "continue";
    }
}

function CRM_congifNewdataLists($conn, $configData)
{

    $sql = "SELECT A.nh_dataId,A.nh_dataname,A.sn_dataname,A.status,A.groupid
FROM " . $GLOBALS['PREFIX'] . "asset.cmdbAssetData A where A.nh_dataId='" . $configData['NH_DataId'] . "' and A.nh_dataname='" . $configData['NH_DataName'] . "' and A.sn_dataname='" . $configData['SN_DataVal'] . "' and A.cid='" . $configData['customerId'] . "' and A.groupid='" . $configData['categoryId'] . "'";

    $res = find_many($sql, $conn);
    $CID = $configData['customerId'];
    $getSiteandMachineNames = Fetch_SiteNames($CID, $conn);
    $total = $getSiteandMachineNames['totalCount'];

    if (safe_count($res) == '0') {
        foreach ($getSiteandMachineNames['resultData'] as $value) {
            $machineName = $value['host'];
            $machineid = $value['machineid'];
            $sqlInsert = "INSERT INTO " . $GLOBALS['PREFIX'] . "asset.cmdbAssetData (`cid`, `nh_dataId`, `nh_dataname`, `sn_dataname`, `status`, `groupid`,`machineid`,`machinename`) VALUES ('" . $configData['customerId'] . "','" . $configData['NH_DataId'] . "','" . $configData['NH_DataName'] . "','" . $configData['SN_DataVal'] . "','1','" . $configData['categoryId'] . "','" . $machineid . "','" . $machineName . "')";
            $crm_res = redcommand($sqlInsert, $conn) or die(mysql_error() . "error in inserting data");
        }



        if ($crm_res == true) {
            echo $result = "success";
        } else {
            echo "failed";
        }
    } else if (safe_count($res) > '0') {
        echo $result = "exists";
    }
}

function Fetch_SiteNames($CID, $conn)
{

    $sql = "select A.sitelist from " . $GLOBALS['PREFIX'] . "agent.channel A where A.eid='$CID'";
    $crm_resdtl = find_one($sql, $conn) or die(mysql_error() . "error in selecting data");
    if (safe_count($crm_resdtl) > 0) {

        $sitelist = $crm_resdtl['sitelist'];


        $machineData = fetch_MachineNames($sitelist, $conn);
    } else {
        $machineData = "0";
    }
    return $machineData;
}

function fetch_MachineNames($sitelist, $conn)
{

    db_change($GLOBALS['PREFIX'] . "asset", $conn);
    $sites = "'" . str_replace(",", "','", $sitelist) . "'";
    $sql = "select host,machineid from " . $GLOBALS['PREFIX'] . "asset.Machine where cust in($sites)";



    $res = find_many($sql, $conn);
    $total = safe_count($res);

    if (safe_count($res) > 0) {

        foreach ($res as $value) {
            $machineName['host'] .= $value['host'] . ",";
            $machineid['machineid'] .= $value['machineid'] . ",";
        }

        $machineName['host'] = rtrim(implode(",", $machineName), ",");
        $machineLists = "'" . str_replace(",", "','", $machineName['host']) . "'";
        $machineid['machineid'] = rtrim(implode(",", $machineid), ",");
        $machineidLists = "'" . str_replace(",", "','", $machineid['machineid']) . "'";

        $msg = array("machineNames" => $machineName, "machineIds" => $machineid, "totalCount" => $total, "resultData" => $res);
    } else {
        $msg = array("machineNames" => "0", "machineIds" => "0", "totalCount" => "0", "resultData" => "0");
    }

    return $msg;
}

function CRM_congifEditdataLists($conn, $configData)
{

    $sql = "SELECT A.nh_dataId,A.nh_dataname,A.sn_dataname,A.status,A.groupid
FROM " . $GLOBALS['PREFIX'] . "asset.cmdbAssetData A where A.nh_dataId='" . $configData['NH_DataId'] . "' and A.nh_dataname='" . $configData['NH_DataName'] . "' and A.sn_dataname='" . $configData['SN_DataVal'] . "' and A.cid='" . $configData['customerId'] . "' and A.groupid='" . $configData['categoryId'] . "'";


    $res = find_many($sql, $conn);

    if (safe_count($res) == '0') {
        $sql = "Update " . $GLOBALS['PREFIX'] . "asset.cmdbAssetData A set A.nh_dataId='" . $configData['NH_DataId'] . "',A.nh_dataname='" . $configData['NH_DataName'] . "',A.sn_dataname='" . $configData['SN_DataVal'] . "',A.groupid='" . $configData['categoryId'] . "'
where A.id='" . $configData['DMedit_id'] . "'";
        $result = redcommand($sql, $conn) or die(mysql_error() . "error in inserting data");
        if ($result) {
            $resResp = "success";
        } else {
            $resResp = "failed";
        }
    } else {
        $resResp = "exists";
    }
    return $resResp;
}

function CRM_unconfigdataList($conn, $id)
{

    $sql = "update " . $GLOBALS['PREFIX'] . "asset.cmdbAssetData A set A.status='0' where id='$id'";
    $result = redcommand($sql, $conn) or die(mysql_error() . "error in inserting data");
    if ($result) {
        $res = "success";
    } else {
        $res = "failed";
    }
    return $res;
}

function CRM_EditdataListValue($conn, $id)
{
    $db = db_connect();

    $sql = "select * from " . $GLOBALS['PREFIX'] . "asset.cmdbAssetData A where A.id='$id'";


    $crm_resdtl = find_one($sql, $db) or die(mysql_error() . "Please select Data to edit");
    $nh_dataid = $crm_resdtl['nh_dataId'];
    $nh_dataname = $crm_resdtl['nh_dataname'];
    $sn_dataname = $crm_resdtl['sn_dataname'];
    $status = $crm_resdtl['status'];
    $groupid = $crm_resdtl['groupid'];

    $selected = '';
    if ($groupid == '1') {
        $selectedCategory = '<option value = "1" selected = "selected">Windows Server</option>"
        <option value = "2" >Network Adapter</option>
        <option value = "3" >Serial Number</option>
        <option value = "4" >Memory Module</option>';
    } else if ($groupid == '2') {
        $selectedCategory = '<option value = "1" >Windows Server</option>"
        <option value = "2" selected = "selected">Network Adapter</option>
        <option value = "3" >Serial Number</option>
        <option value = "4" >Memory Module</option>';
    } else if ($groupid == '3') {
        $selectedCategory = '<option value = "1" >Windows Server</option>"
        <option value = "2" >Network Adapter</option>
        <option value = "3" selected = "selected">Serial Number</option>
        <option value = "4" >Memory Module</option>';
    } else if ($groupid == '4') {
        $selectedCategory = '<option value = "1" >Windows Server</option>"
        <option value = "2">Network Adapter</option>
        <option value = "3">Serial Number</option>
        <option value = "4" selected = "selected">Memory Module</option>';
    }



    $NHres = getAllNHdataId($nh_dataid, $nh_dataname, $sn_dataname, $conn);
    $SNres = getAllSNdataNames($nh_dataid, $nh_dataname, $sn_dataname, $conn);


    $category_Type = '<select class="form-control selectpicker dropdown-submenu" data-container="body" id="editDM_category" data-size="5" style="display:block !important;padding-left: 29px;">
                                                        ' . $selectedCategory . '
                                                    </select>';
    $result = '<div class="form-group is-empty clearfix row">
                                                <label for="first-name" class="col-sm-3 align-label">SN Categories</label>
                                                <div class="col-sm-9" >
                                                ' . $category_Type . '
                                                </div>
                                                <input type="hidden" name="DMedit_id" id="DMedit_id" value="' . $id . '"/>
                                            </div>
                                            <div class="form-group is-empty clearfix row">
                                                <label for="first-name" class="col-sm-3 align-label nhdataname_Label">NH DataNames</label>
                                                <div class="col-sm-9" id="">
                                                    ' . $NHres . '
                                                </div>
                                            </div>
                                            <div class="form-group is-empty clearfix row">
                                                <label for="first-name" class="col-sm-3 align-label sndataname_Label">SN Datanames</label>
                                                <div class="col-sm-9" id="">
                                                    ' . $SNres . '
                                                </div>
                                            </div>';
    echo $result;
}

function getAllNHdataId($nanoheal_dataid, $nanoheal_dataname, $snow_dataname, $conn)
{
    $sql = "SELECT D.category_id,D.nh_dataid,D.nh_dataname,D.crm_dataname,D.category_type
FROM " . $GLOBALS['PREFIX'] . "asset.cmdbDataMapping D where D.crm_dataname!='' and D.category_type!='' and D.category_id!=''";


    $res = find_many($sql, $conn);

    if (safe_count($res) > 0) {



        foreach ($res as $value) {
            $selected = "";
            if ($nanoheal_dataid == $value['nh_dataid']) {
                $selected = "selected";
            } else {
                $selected = "";
            }
            $nh_dataid = $value['nh_dataid'];
            $nh_dataname = $value['nh_dataname'];
            $category_type = $value['category_type'];
            $crm_dataname = $value['crm_dataname'];
            $options .= '<option value="' . $nh_dataid . '" ' . $selected . '>' . $nh_dataname . '</option>';
        }

        $NHData_SelectBox = '<select class="form-control selectpicker dropdown-submenu" data-container="body" id="edit_nanoheal_Datanames" data-size="5" style="display:block !important;padding-left: 29px;">' . $options . '</select><em class="error addreq" id="req_advusername">*</em>';
    } else {
        echo $NHData_SelectBox = "continue";
    }

    return $NHData_SelectBox;
}

function getAllSNdataNames($nanoheal_dataid, $nanoheal_dataname, $snow_dataname, $conn)
{
    $sql = "SELECT D.category_id,D.nh_dataid,D.nh_dataname,D.crm_dataname,D.category_type
FROM " . $GLOBALS['PREFIX'] . "asset.cmdbDataMapping D where D.crm_dataname!='' and D.category_type!='' and D.category_id!=''";

    $res = find_many($sql, $conn);

    if (safe_count($res) > 0) {

        foreach ($res as $value) {
            $selected = "";
            if ($snow_dataname == $value['crm_dataname']) {
                $selected = "selected";
            } else {
                $selected = "";
            }
            $nh_dataid = $value['nh_dataid'];
            $nh_dataname = $value['nh_dataname'];
            $category_type = $value['category_type'];
            $crm_dataname = $value['crm_dataname'];
            $options .= '<option value="' . $crm_dataname . '" ' . $selected . '>' . $crm_dataname . '</option>';
        }

        $SNData_SelectBox = '<select class="form-control selectpicker dropdown-submenu" data-container="body" id="edit_servicenow_Datanames" data-size="5" style="display:block !important;padding-left: 29px;">' . $options . '</select><em class="error addreq" id="req_advusername">*</em>';
    } else {
        echo $SNData_SelectBox = "continue";
    }

    return $SNData_SelectBox;
}

function CRM_GetNotificationsList_Details($configData, $conn)
{



    $CRMlogin_value = $configData['CRMlogin_value'];

    if ($CRMlogin_value == '2') {

        $cid = $_SESSION['user']['cId'];
        $CRMlogin_valueCust = '5';
    } elseif ($CRMlogin_value == '5') {
        $cid = $_SESSION['user']['cId'];
    }
    $cid = $configData['custId'];


    if ($CRMlogin_value == '2') {
        $where = "A.ch_id in($cid)";
    } else if ($CRMlogin_value == '5') {
        $where = "ch_id ='$cid'";
    }


    $sql = "select * from  " . $GLOBALS['PREFIX'] . "event.crmConfigure A where $where and A.nid='" . $configData['Nid'] . "'";



    $res = find_many($sql, $conn);

    $recordList = [];
    if (safe_count($res) > 0) {
        $addSelects = '<select name="proactiveauditGrid_length" aria-controls="proactiveauditGrid" class="" localized=""><option value="10" localized="">10</option><option value="25" localized="">25</option><option value="50" localized="">50</option><option value="100" localized="">100</option></select>';
        foreach ($res as $key => $value) {
            $nid = $value['nid'];
            $notifName = $value['notifName'];
            $category = $value['category'];
            $subcategory = $value['subcategory'];
            $eventType = $value['eventType'];
            $eventType_Selected = "";
            if ($eventType == '1') {
                $eventType_Selected = "Autoheal";
            } else if ($eventType == '2') {
                $eventType_Selected = "SelfHelp";
            } else if ($eventType == '3') {
                $eventType_Selected = "Notifications";
            }

            $nid = '<p class="ellipsis" id="' . $value['nid'] . '" value="' . $value['nid'] . '" title="' . $value['nid'] . '">' . $value['nid'] . '</p>';
            $notifName = '<p class="ellipsis" id="' . $notifName . '" value="' . $notifName . '" title="' . $notifName . '">' . $notifName . '</p>';
            $category = '<p class="ellipsis" id="' . $category . '" value="' . $category . '" title="' . $category . '">' . $category . '</p>';
            $subcategory = '<p class="ellipsis" id="' . $subcategory . '" value="' . $subcategory . '" title="' . $subcategory . '">' . $subcategory . '</p>';
            $eventType_Selected = '<p class="ellipsis" id="' . $eventType_Selected . '" value="' . $eventType_Selected . '" title="' . $eventType_Selected . '">' . $eventType_Selected . '</p>';
            $recordList[] = array("DT_RowId" => $value['id'], $nid, $notifName, $category, $subcategory, $eventType_Selected);
        }
    } else {
        $recordNill = "no Record";
        $nid = "no Record";
        $notifName = "no Record";
        $category = "no Record";
        $subcategory = "no Record";
        $eventType_Selected = "no Record";
        $recordList[] = array("DT_RowId" => $recordNill, $nid, $notifName, $category, $subcategory, $eventType_Selected);
    }

    return $recordList;
}

function CRM_getPayoloadJsonData($selectedDataTeid, $db)
{
    $sql = "select ticketType,syncStatus,ccSentPayload,ccResppayload,closeSentPayload,closeRespPayload from  " . $GLOBALS['PREFIX'] . "event.ticketEvents where teid='$selectedDataTeid'";
    $res = find_many($sql, $db);
    $recordList = [];

    if (safe_count($res) > 0) {
        foreach ($res as $value) {
            $syncStatus = $value['syncStatus'];
            $ccSentPayload = $value['ccSentPayload'];
            $ccResppayload = $value['ccResppayload'];
            $closeSentPayload = $value['closeSentPayload'];
            $closeRespPayload = $value['closeRespPayload'];
            if (($closeSentPayload == "NULL") || empty($closeSentPayload)) {
                $closeSentPayload = "";
            } else if (($closeRespPayload == "NULL") || empty($closeRespPayload)) {
                $closeRespPayload = "";
            } else if (($ccSentPayload == "NULL") || empty($ccSentPayload)) {
                $ccSentPayload = "";
            } else if (($ccResppayload == "NULL") || empty($ccResppayload)) {
                $ccResppayload = "";
            }

            $ticketType = $value['ticketType'];
            if ((($ticketType == '1') || ($ticketType == 1)) || (($ticketType == '4') || ($ticketType == 4)) || (($ticketType == '3') || ($ticketType == 3))) {
                if (!empty($value['closeSentPayload'])) {
                    $datalis = safe_json_decode($value['ccSentPayload'], true);
                    $inter = $datalis['internalNotes'];
                    $r = explode("Site:", $inter);
                    $s = explode("Time:", $r[1]);
                    $sitelist = trim($s[0]);

                    $siteNaming = explode("__", $sitelist);
                    $str = $siteNaming[0];

                    $data = $r[0] . "Site: " . $str . " Time:" . $s[1];
                    $datalis['internalNotes'] = $data;
                    $datalis['problemDescription'] = $data;
                    $datalis['notes'] = $data;
                    $ccSentPayload = json_encode($datalis);
                }

                if (!empty($value['closeSentPayload'])) {
                    $datalisclose = safe_json_decode($value['closeSentPayload'], true);
                    $interclose = $datalisclose['problemDescription'];
                    $rclose = explode("Site:", $interclose);
                    $sclose = explode("Time:", $rclose[1]);
                    $sitelistclose = trim($sclose[0]);

                    $siteNamingclose = explode("__", $sitelistclose);
                    $strclose = $siteNamingclose[0];

                    $dataclose = $rclose[0] . " Site: " . $strclose . " Time:" . $s[1];
                    $datalisclose['problemDescription'] = $dataclose;
                    $closeSentPayload = json_encode($datalisclose);
                }
            }


            $payloadResp = array("response" => "success", "sentPayload" => $ccSentPayload, "receidPayload" => $ccResppayload, "ticketType" => $ticketType, "closeSentPayload" => $closeSentPayload, "closeRespPayload" => $closeRespPayload);
        }
    } else {

        $payloadResp = array("response" => "failed", "sent Payload" => $ccSentPayload, "receidPayload" => $ccResppayload, "ticketType" => $ticketType, "closeSentPayload" => $closeSentPayload, "closeRespPayload" => $closeRespPayload);
    }



    return $payloadResp;
}

function CRM_getActonDetails($teid, $db)
{
    $loggedinUser = $_SESSION['user']['logged_username'];
    $sql = "select * from  " . $GLOBALS['PREFIX'] . "event.ticketEvents where teid='$teid'";
    $res = find_one($sql, $db);
    $ticketType = $res['ticketType'];
    if ($ticketType == 2) {
        if ($res['status'] == 'open') {
            $actionfrom = "";
            $res['ticketClose'] = "";
        } else if ($res['status'] == 'Closed') {
            $actionfrom = $loggedinUser;

            $res['ticketClose'] = $res['ticketClose'];
        }
        $Type = "Action Details: Notifications";
        $response = "<p style='color:#696969'>"
            . "Status: " . $res['status'] . "<br>"
            . "Notification Time: " . date("m-d-Y H:m:i", $res['eventDate']) . "<br>"
            . "Action Time: " . $res['ticketClose'] . "<br>"
            . "Action From: " . $actionfrom . "<br>"
            . "Solution Pushed: " . $res['comments']
            . "</p>";
    } elseif ($ticketType == 1) {
        if ($res['ticketClose'] == 'open') {
            $res['ticketClose'] = "";
        } else {
            $res['ticketClose'] = $res['ticketClose'];
        }
        $response = "<p style='color:#696969'>"
            . "Status: " . $res['status'] . "<br>"
            . "Generated Time: " . date("m-d-Y H:m:i", $res['eventDate']) . "<br>"
            . "Closed Time: " . $res['ticketClose'] . "<br>"
            . "</p>";
        $Type = "Action Details: Autoheal";
    } elseif ($ticketType == 3) {
        if ($res['ticketClose'] == 'open') {
            $res['ticketClose'] = "";
        } else {
            $res['ticketClose'] = $res['ticketClose'];
        }
        $response = "<p style='color:#696969'>"
            . "Status: " . $res['status'] . "<br>"
            . "Generated Time: " . date("m-d-Y H:m:i", $res['eventDate']) . "<br>"
            . "Closed Time: " . $res['ticketClose'] . "<br>"
            . "</p>";
        $Type = "Action Details: Selfhelp";
    } elseif ($ticketType == 4) {
        if ($res['ticketClose'] == 'open') {
            $res['ticketClose'] = "";
        } else {
            $res['ticketClose'] = $res['ticketClose'];
        }
        $response = "<p style='color:#696969'>"
            . "Status: " . $res['status'] . "<br>"
            . "Generated Time: " . date("m-d-Y H:m:i", $res['eventDate']) . "<br>"
            . "Closed Time: " . $res['ticketClose'] . "<br>"
            . "</p>";
        $Type = "Action Details: Schedule";
    }
    $resp = array("type" => $Type, "resp" => $response);
    return $resp;
}
