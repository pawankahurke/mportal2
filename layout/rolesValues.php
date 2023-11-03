<?php

 function setRoleForAnchorTag($roleName, $requiredVal) {
     $return = '';
    if (isset($_SESSION["user"]["roleValue"][$roleName])) {
        $roleValue = $_SESSION["user"]["roleValue"][$roleName];
        if ($roleValue == 0) {
            $return = 'hideAnchorTag';
        } else if ($roleValue == $requiredVal) {
            $return = 'enableAnchorTag';
        } else if ($roleValue == 1) {
            $return = 'disableAnchorTag';
        }
    } else {
        $return = 'enableAnchorTag';    }

    return $return;
}

function setActiveClass($window, $windowValue) {
    if ($_SESSION[$window] == $windowValue) {
        return 'active';
    } else {
        return '';
    }
}


 function isRoleEnabled($roleName) {

    if (isset($_SESSION["user"]["roleValue"][$roleName]) && is_numeric($_SESSION["user"]["roleValue"][$roleName]) && (intval($_SESSION["user"]["roleValue"][$roleName]) == 2 || intval($_SESSION["user"]["roleValue"][$roleName]) == 1)) {
        return true;
    }

    return false;
}

?>
