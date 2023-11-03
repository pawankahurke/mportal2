<?php

/**
 * Common functions for user model
 */
class nhUser
{

    public static $max_login_attempts = 5;

    /**
     * NCP-671 Unrestricted User Self registration (Allow to block self registration)
     * Add env var DASHBOARD_AllowSignUp=true to allow sign up
     */
    public static function isAllowSignUp()
    {
        /**
         * NCP-671 Unrestricted User Self registration (Allow to block self registration)
         */
        if (getenv('DASHBOARD_AllowSignUp') === 'true') {
            return true;
        }
        return false;
    }

    /**
     * replace for old function getUserInformation
     */
    public static function getUserInformation($username, $db)
    {
        $sql = "select * from " . $GLOBALS['PREFIX'] . "core.Users where (lower(user_email) = lower(?) or user_phone_no=?)  limit 1";
        $bindings = array($username, $username);
        $pdo = $db->prepare($sql);
        $pdo->execute($bindings);
        $res = $pdo->fetch(PDO::FETCH_ASSOC);
        return $res;
    }

    public static function isAuth()
    {
        return isset($_SESSION) && isset($_SESSION['user']) && isset($_SESSION['user']['userid']) && $_SESSION['user']['userid'] > 0;
    }

    public static function redirectIfNotAuth()
    {
        if (self::isAuth()) {
            return true;
        }
        global $base_url;
        header("Location: $base_url");
        die();
    }

    public static function getCurrentUserId()
    {
        return (int) $_SESSION['user']['userid'];
    }

    public static function getAdminSites_PDO($adminid, $db)
    {

        $siteSql = "select C.customer from " . $GLOBALS['PREFIX'] . "core.Customers C, " . $GLOBALS['PREFIX'] . "core.Users U where C.username = U.username and U.userid=? group by C.customer";
        $pdo = $db->prepare($siteSql);
        $pdo->execute([$adminid]);
        $resultSite = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $agent_sites = array();

        foreach ($resultSite as $row) {
            $agent_sites[] = "'" . $row['customer'] . "'";
        }
        logs::log(__FILE__, __LINE__, [$agent_sites, $adminid]);
        return $agent_sites;
    }

    public static function get_sitelist_PDO($user_sites)
    {
        if (empty($user_sites)) {
            return [];
        }
        $bindings = [];
        foreach ($user_sites as $eachSites) {
            $bindings[] = str_replace("'", "", $eachSites);
        }
        $bindDelim = str_repeat('?,', safe_count($bindings) - 1) . '?';
        $siteList = array();

        try {

            $siteQuery = "select customer as name from " . $GLOBALS['PREFIX'] . "core.Customers where customer in (" . $bindDelim . ") group by customer";
            $pdo = NanoDB::connect()->prepare($siteQuery);
            $pdo->execute($bindings);
            $siteListdata = $pdo->fetchAll(PDO::FETCH_ASSOC);

            foreach ($siteListdata as $value) {
                $siteList[$value['name']] = $value['name'];
            }

            return $siteList;
        } catch (Exception $e) {
            logs::log(__FILE__, __LINE__, $e);
            return [];
        }
    }

    public static function getUserStatus_PDO($userId)
    {
        $umsg = 0;
        $lmsg = 0;
        $pmsg = 0;
        $currentTimestamp = time();
        $agentsql = "select userid,userStatus,loginStatus,passwordDate,revusers from " . $GLOBALS['PREFIX'] . "core.Users where (lower(user_email) = lower(?) or user_phone_no=?)  limit 1";
        $bindings = array($userId, $userId);
        $pdo = NanoDB::connect()->prepare($agentsql);
        $pdo->execute($bindings);
        $resultStatus = $pdo->fetch(PDO::FETCH_ASSOC);

        if ($resultStatus && safe_count($resultStatus) > 0) {
            $userstatus = $resultStatus['userStatus'];
            $loginStatus = $resultStatus['loginStatus'];
            $passwordDate = $resultStatus['passwordDate'];

            $_SESSION['user']['licenseuser'] = $resultStatus['revusers'];

            if ($userstatus == 0) {
                $umsg = 0;
            } else {
                $umsg = 1;
            }
            if ($loginStatus == 1) {
                $lmsg = 0;
            } else {
                $lmsg = 1;
            }

            if ($passwordDate == '' || $passwordDate == null) {
                $pmsg = 2;
            } else {
                $numDays = ($passwordDate - $currentTimestamp) / 24 / 60 / 60;

                if ($numDays <= 0) {
                    $pmsg = 0;
                } else {
                    $pmsg = 1;
                }
            }
        } else {
            $umsg = 3;
            $lmsg = 3;
            $pmsg = 3;
        }
        return [$umsg, $lmsg, $pmsg];
    }

    public static function sendEmail($user_email, $subject = 'Nanoheal', $body = '', $data = null)
    {

        if ($data) {
            foreach ($data as $key => $value) {
                $body = str_replace($key, $value, $body);
            }
        }

        // send from visualisationService
        $arrayPost = array(
            'from' => getenv('SMTP_USER_LOGIN'),
            'to' => $user_email,
            'subject' => $subject,
            'text' => '',
            'html' => $body,
            'token' => getenv('APP_SECRET_KEY'),
        );
        $url = getenv('VISUALISATION_SERVICE_API_URL') . "/mailer/sendmassage";

        try {
            CURL::sendDataCurl($url, $arrayPost);
            //      $sendgrid->send($email);
        } catch (Exception $e) {
            logs::log(__FILE__, __LINE__, $e);
        }
    }

    public static function blockUserLogin($user_email)
    {
        $sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET login_attempts=? where user_email = ?;";
        NanoDB::query($sql, [nhUser::$max_login_attempts, $user_email]);
    }

    public static function unblockUserLogin($userId)
    {
        NanoDB::query("update " . $GLOBALS['PREFIX'] . "core.Users set login_attempts=0,otp_blocktime=0,otp_blocked='0',otp_retry=0,otp_resend_count=0,otp_resend_expiretime=0 where user_email=?", [$userId]);
    }


    /*
     * Function of receiving information about the user by email
     * @param $userEmail = 'example@mail.com'
     */
    public static function getUserInfo($userEmail)
    {
        $query = "select * from " . $GLOBALS['PREFIX'] . "core.Users where user_email=? limit 1";
        $bindings = array($userEmail);
        $pdo = NanoDB::connect()->prepare($query);
        $pdo->execute($bindings);
        $userInfo = $pdo->fetchAll(PDO::FETCH_ASSOC);
        return $userInfo[0];
    }

    public static function getUserById($id)
    {
        $query = "select * from " . $GLOBALS['PREFIX'] . "core.Users where userid=? limit 1";
        $bindings = array($id);
        $pdo = NanoDB::connect()->prepare($query);
        $pdo->execute($bindings);
        $userInfo = $pdo->fetchAll(PDO::FETCH_ASSOC);
        return $userInfo[0];
    }
}
