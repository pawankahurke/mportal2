<?php

define('REST_API_ROOT', '/api/v1/');
define('ROCKET_CHAT_INSTANCE', 'http://104.197.37.69');

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'rocketChat/RocketChatClient.php';
include_once 'rocketChat/RocketChatUser.php';


function RC_AddAgent($userName, $lastName, $userEmail, $pwd)
{
    try {

        $api = new \RocketChat\Client();
        $admin = new \RocketChat\User('admin@nanoheal.com', 'admin@123');
        if ($admin->login()) {

            $newuser = new \RocketChat\User($userName, $pwd, array(
                'nickname' => $userName . $lastName,
                'email' => $userEmail,
            ));
            if (!$newuser->login(false)) {
                $status = $newuser->create();
                if (!$status) {
                    console . log("User registration failed");
                } else {
                    $obj = $newuser->info();
                    RC_RegisterAgent($obj->user->_id, $obj->user->username);
                }
            } else {
                $obj = $newuser->info();
                RC_RegisterAgent($obj->user->_id, $obj->user->username);
            }
        } else {
            console . log("User registration failed");
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function RC_RegisterAgent($id, $username)
{
    try {
        $deptId = "";
        $deptName = "Nanoheal";
        $newuser = new \RocketChat\User($id, $username);
        $deptList = safe_json_decode($newuser->getDeptList());
        $departments = $deptList->departments;
        if (safe_count($departments) > 0) {
            foreach ($departments as $key) {
                if ($key->name === "Nanoheal") {
                    $deptId = $key->_id;
                    $deptName = $key->name;
                }
            }
        }

        $response = $newuser->regAgent($username);
        if ($response) {
            $dept = new stdClass();
            $dept->enabled = true;
            $dept->name = $deptName;
            $dept->showOnRegistration = true;
            $agent = array();
            $temp = new stdClass();
            $temp->agentId = $id;
            $temp->username = $username;
            $agent[] = $temp;

            if (!empty($deptId)) {

                $jsonString = safe_json_decode($newuser->getDeptInfo($deptId));
                $agentList = $jsonString->agents;
                foreach ($agentList as $key) {
                    $agent[] = $key;
                }
                $newuser->updateDept($deptId, $dept, $agent);
            } else {
                $newuser->regDept($dept, $agent);
            }
        } else {
            echo "user registration failed";
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        echo  'Caught exception: ',  $e->getMessage(), "<br>";
    }
}


function RC_deleteUser($userName)
{
    try {
        $temp = array();
        $user = new \RocketChat\User('admin@nanoheal.com', 'admin@123');
        if ($user->login()) {
            $agentList = safe_json_decode($user->getAgentsList());
            foreach ($agentList->users as $obj) {
                if (trim($userName) === trim($obj->username)) {
                    $user->deleteUser($obj->_id);
                    $deptList = safe_json_decode($user->getDeptList());
                    if (safe_count($deptList->departments) > 0) {
                        foreach ($deptList->departments as $key) {
                            if ($key->name === "Nanoheal") {
                                $deptInfo = safe_json_decode($user->getDeptInfo($key->_id));
                                foreach ($deptInfo->agents as $row) {
                                    if (trim($row->username) !== trim($userName)) {
                                        $temp[] = $row;
                                    }
                                }
                                if (safe_count($temp) > 0) {
                                    $user->updateDept($key->_id, $deptInfo->department, $temp);
                                }
                            }
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
    }
}
