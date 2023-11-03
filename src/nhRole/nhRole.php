<?php


define('USER_ALLOWED_PRIVILEGES', 2);
define('USER_NOT_ALLOWED_PRIVILEGES', 1);

define('USER_RestrictedRole', 98);
define('USER_NormalRole', 97);


/**
 * Common functions for user model
 */
class nhRole
{
    public static $ALLOWED_PRIVILEGES  = USER_ALLOWED_PRIVILEGES;
    public static $NOT_ALLOWED_PRIVILEGES = USER_NOT_ALLOWED_PRIVILEGES;


    public static $TYPE_RestrictedRole = USER_RestrictedRole;
    public static $TYPE_NormalRole = USER_NormalRole;


    public static function checkModulePrivilege($roleName, $requiredVal = USER_ALLOWED_PRIVILEGES)
    {

        if (self::checkNhToken()) {
            return true;
        }

        if (!$_SESSION || !isset($_SESSION["user"]["rolename"])) {
            return false;
        }

        if ($_SESSION["user"]["rolename"] === "SuperAdminRole") {
            return true;
        }

        if (!is_array($roleName)) {
            $roleName = [$roleName];
        }

        foreach ($roleName as $key => $value) {
            if (isset($_SESSION["user"]["roleValue"][$value])) {
                $roleValue = (int)$_SESSION["user"]["roleValue"][$value];
                if ($roleValue === 0) {
                    return false;
                } else if ($roleValue === (int)$requiredVal) {
                    continue;
                } else if ($roleValue === 1) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function getMyRoles()
    {
        if (!$_SESSION || !isset($_SESSION["user"]["roleValue"])) {
            return [];
        }

        $r = [];
        foreach ($_SESSION["user"]["roleValue"] as $key => $value) {
            $roleValue = (int)$_SESSION["user"]["roleValue"][$key];
            if ($roleValue === 2) {
                $r[] =  $key;
            }
        }
        return  $r;
    }

    public static function dieIfnoRoles($roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (self::checkModulePrivilege($roles)) {
            return true;
        }

        $roleValue = false;
        if (isset($_SESSION["user"]["roleValue"])) {
            $roleValue = $_SESSION["user"]["roleValue"];
        }

        logs::trace(1, "Die due: You do not have one of thees roles: " . implode(", ", $roles));

        http_response_code(409);
        die(json_encode([
            'status' => false,
            'message' => 'Permission denied',
            'error' => "You do not have one of thees roles: " . implode(", ", $roles),
            '_roles' =>  self::getMyRoles(),
            '_s' => $roleValue
        ]));
    }

    public static function getNhTokenForHeader(): string
    {
        $header = md5(md5(getenv('DEPLOYMENT_ID') . md5(getenv('APP_SECRET_KEY'))) . date("m.d.y.H"));
        return $header;
    }

    public static function checkNhToken(): bool
    {
        $headers = getallheaders();
        $headerKey = md5(md5(getenv('DEPLOYMENT_ID') . md5(getenv('APP_SECRET_KEY'))) . date("m.d.y.H"));
        if (
            isset($headers["X-Nh-Token"])
        ) {
            if (strrpos($headers["X-Nh-Token"], $headerKey) !== false) {

                // Allow all queries from this deployment without checks for roles if it is internal queries.
                return true;
            } else {
                logs::log("Wrong X-Nh-Token $headerKey != " . $headers["X-Nh-Token"]);
            }
        }
        return false;
    }

    public static function dieIfNotSuperAdminRole()
    {
        if ($_SESSION["user"]["rolename"] === "SuperAdminRole") {
            return true;
        }

        if (self::checkNhToken()) {
            return true;
        }

        logs::trace(1, "Die due: This opration avaliabled only for SuperAdminRole");
        die('This opration avaliabled only for SuperAdminRole');
    }

    public static function checkRoleForPage($role)
    {
        $res = self::checkModulePrivilege($role);
        if (!$res) {
            echo '<h4 style="text-align: center">Access is denied. To view this page, you must have the "' . $role . '" permission.</h4>';
        }
    }

    public static function currentRoleName(): string
    {
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['rolename'])) {
            return null;
        }

        return  $_SESSION['user']['rolename'];
    }
}
