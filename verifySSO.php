<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//error_reporting(-1);
//ini_set('display_errors', 'On');

include_once 'config.php';
include_once 'lib/l-dbConnect.php';
include_once 'include/common_functions.php';

$pdo = pdo_connect();

$verified_email_id = url::requestToAny('email');

$domainName = $_SERVER['HTTP_HOST'];
if($verified_email_id != '') {
    $stmt = $pdo->prepare("update ".$GLOBALS['PREFIX']."core.singlesignon set oauth_vstatus = ?, saml_vstatus = ? where domain_name = ?");
    $stmt->execute([1, 1, $domainName]);
}

?>

<script type="text/javascript">
    setTimeout(function() {
        window.close();
    }, 1000);
</script>