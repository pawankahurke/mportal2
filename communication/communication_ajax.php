<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '1G');
ini_set('post_max_size', '1G');
ini_set('upload_max_filesize', '1G');

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'communication_func.php';
include_once '../lib/l-util.php';
include_once '../lib/l-dashboard.php';


//Replace $routes['post'] with if else
if (url::issetInPost('function')) {
    $function = url::postToText('function');

    nhRole::dieIfnoRoles(['troubleshooting']); // roles:   troubleshooting
}
if (url::issetInGet('function')) {
    $function = url::getToAny('function');

    nhRole::dieIfnoRoles(['troubleshooting']); // roles:   troubleshooting
}

if ($function === 'get_OriginData') {
    nhRole::dieIfnoRoles(['troubleshooting']); // roles:   troubleshooting
    get_OriginData();
} else if ($function === 'Add_RemoteJobs') {
    nhRole::dieIfnoRoles(['troubleshooting']); // roles:   troubleshooting 
    echo AddRemoteJobs_func();
} else if ($function === 'Add_AndroidJobs') {
    nhRole::dieIfnoRoles(['troubleshooting']); // roles:   troubleshooting
    Add_AndroidJobs();
} else if ($function === 'getAutoSolnPush') {
    nhRole::dieIfnoRoles(['troubleshooting']); // roles:   troubleshooting
    getAutoSolnPush();
} else if ($function === 'get_MachineOS1') {
    nhRole::dieIfnoRoles(['troubleshooting']); // roles:   troubleshooting
    get_MachineOS1();
} else if ($function === 'profile_Data') {
    nhRole::dieIfnoRoles(['troubleshooting']); // roles:   troubleshooting
    profile_Data();
} else if ($function === 'advprofileData') {
    nhRole::dieIfnoRoles(['troubleshooting']); // roles:   troubleshooting
    advprofileData();
} else if ($function === 'profile_DataList') {
    nhRole::dieIfnoRoles(['troubleshooting']); // roles:   troubleshooting
    profile_DataList();
} else if ($function === 'get_TileNames') {
    nhRole::dieIfnoRoles(['troubleshooting']); // roles:   troubleshooting
    get_TileNames();
} else {
    echo $function . ' missing';
}

function get_MachineOS1()
{
    $ret = getMachineOS_funcCall();
    echo $ret;
}

function getOsVarValues()
{
    $ret = getOsVarValues_func();
    echo $ret;
}

function getScheduledata()
{
    $ret = getScheduledata_func();
    echo $ret;
}

function getTempAuditdata()
{
    $ret = getTempAuditdata_func();
    echo $ret;
}

function updateAuditData()
{
    $ret = updateAuditData_func();
    echo $ret;
}

function getCancelMachineDet()
{
    $ret = getCancelMachineDet_info();
    echo $ret;
}

function getmachinelistSD()
{
    $ret = getmachinelistSD_info();
    echo $ret;
}

function profile_Data()
{
    $ret = profileData_info();
    echo $ret;
}

function advprofileData()
{
    $ret = advprofileData_info();
    echo $ret;
}

function profile_DataList()
{
    $ret = profileDataList_info();
    echo $ret;
}

function getProfileName()
{
    $ret = getProfileName_func();
    echo $ret;
}

function cancelPendingJobs()
{
    $ret = cancelPendingJobs_func();
    echo $ret;
}

function getAndroidVarValues()
{
    $ret = getAndroidVarValues_func();
    echo $ret;
}

function getMacVarValues()
{
    $ret = getMacVarValues_func();
    echo $ret;
}

function doActionOnInteractive()
{
    $ret = doActionOnInteractive_func();
    echo $ret;
}

function doActionOnNotification()
{
    $ret = doActionOnNotification_func();
    echo $ret;
}

function doActionOnInteractiveAndroid()
{
    $ret = doActionOnInteractiveAndroid_func();
    echo $ret;
}

function doActionOnInteractiveMac()
{
    $ret = doActionOnInteractiveMac_func();
    echo $ret;
}

function AddRemoteJobsNew()
{
    $ret = AddRemoteJobsNew_func();
    echo $ret;
}

function getAutoSolnPush()
{
    $ret = getPreSoln();
    echo $ret;
}

function Add_AndroidJobs()
{
    $ret = AddAndroidJobs_func();
    echo $ret;
}

function ExecuteDirectJob()
{
    logs::log(__FILE__, __LINE__, "ExecuteDirectJob_func implementation not found");
    die("ExecuteDirectJob_func implementation not found");
    // $ret = ExecuteDirectJob_func();
    // echo $ret;
}

function add_profileDataList()
{
    logs::log(__FILE__, __LINE__, "add_profileList implementation not found");
    die("add_profileList implementation not found");
    // $ret = add_profileList();
    // echo $ret;
}

function edit_profileDataList()
{
    $ret = edit_profileList();
    echo $ret;
}

function add_newprofileDataList()
{
    $ret = add_newprofileData();
    echo $ret;
}

function getL1tiles_info()
{
    $ret = getL1tiles();
    echo $ret;
}

function getL12tiles_info()
{
    logs::log(__FILE__, __LINE__, "getL12tiles implementation not found");
    die("getL12tiles implementation not found");
    // $ret = getL12tiles();
    // echo $ret;
}

function delete_profileDataList()
{
    $ret = delete_profileList();
    echo $ret;
}

function get_OriginData()
{
    $ret = getOrigin();
    echo $ret;
}

function get_TileNames()
{
    $ret = getTileNames();
    echo $ret;
}
