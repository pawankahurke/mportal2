<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *
 * OAUTH CALLBAK FILE
 */

include_once '../../config.php';

global $ssosamlapiurl;

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

logs::log("oauth_callback_log",  $_REQUEST);


header("location: $ssosamlapiurl/connect/provider/callback", true, 307);
