<?php

/**
 * Common functions for user model
 */
class whitelist
{
    /**
     * Send 405 status if we gat the query is not one of [GET, POST, PATCH, PUT, DELETE]
     *
     * @description Potentially Unsafe HTTP Options enabled on the API
     * @link https://nanoheal.atlassian.net/browse/NCP-793
     */
    public static function dieIfRequestIsWrong()
    {
        if (!in_array($_SERVER["REQUEST_METHOD"], ['GET', 'POST', 'PATCH', 'PUT', 'DELETE'])) {
            http_response_code(405);
            die("Not auth error (OPTIONS is not avaliable)");
        }
    }

    public static function checkRoute()
    {

        if (nhUser::isAuth()) {
            if (in_array($_SERVER["REQUEST_METHOD"], ['GET'])) {
                TokenChecker::checkAllPlaces();
            }
            return true;
        }

        if (in_array($_SERVER["REQUEST_URI"], [
            "/Dashboard/",
            "/Dashboard/index.php",
            "/Dashboard/api/",
            "/Dashboard/api/index.php",
            "/Dashboard/healthz/index.php",
            "/Dashboard/userSignUp.php", //
            "/Dashboard/logout.php", //
            "/Dashboard/forgot-password.php",
            "/Dashboard/mfa/",
            "/Dashboard/custom/json_android.php",
            "/Dashboard/custom/json_browser.php",
            "/Dashboard/custom/json_kiosk.php",
            "/Dashboard/custom/MobileRegister.php",
        ])) {
            return true;
        }

        if (preg_match('#^\/gateway\/Dashboard\/api\/.*$#', $_SERVER["REQUEST_URI"])) {
            return true;
        }
        if (preg_match('#^\/Dashboard\/(api|cron)\/.*$#', $_SERVER["REQUEST_URI"])) {
            return true;
        }

        if (preg_match('#^\/Dashboard\/Provision\/cron\/.*$#', $_SERVER["REQUEST_URI"])) {
            return true;
        }

        if (in_array($_SERVER["SCRIPT_NAME"], [
            "/Dashboard/getInstallStatus.php",
            "/Dashboard/getPcInstallCount.php",
            "/Dashboard/getPcInstallStatus.php",
            "/Dashboard/getUninstallStatus.php",
            "/Dashboard/install-downloadhelper.php",
            "/Dashboard/install-eula.php",
            "/Dashboard/download_helper.php",
            "/Dashboard/eula.php",
            "/Dashboard/resolution/index.php",
            "/Dashboard/reset-password.php", //
            "/Dashboard/Provision/install/asiapi.php",
            "/Dashboard/Provision/install/download.php",
            "/Dashboard/Provision/install/d.php",
            "/Dashboard/Provision/install/getclient.php",
            "/Dashboard/Provision/download/download_helper.php",
            "/Dashboard/Provision/rpc/rpc.php",
            "/Dashboard/Provision/install/getBrandingUrl.php",
            "/Dashboard/cron/c-crmincident.php",
            "/Dashboard/cron/c-crmincident_closedEvents.php",
            "/Dashboard/cron/c-SQLDaily.php",
            "/Dashboard/cron/MetaBaseAgregation.php",
            "/Dashboard/src/MetaBaseAgregation/MetaBaseAgregation.php",
            "/Dashboard/Provision/cron/c-purge.php",
            "/Dashboard/admin/expunge_machines_cron.php",
            "/Dashboard/admin/expunge_append_line_to_log.php",
            "/Dashboard/signinCheck.php",
            "/Dashboard/communication/MobileRegister.php",
            "/Dashboard/custom/json_android.php",
            "/Dashboard/custom/json_browser.php",
            "/Dashboard/custom/json_kiosk.php",
            "/Dashboard/custom/MobileRegister.php",
            "/Dashboard/ini.php",
            "/Dashboard/softwareupdate/softwarefunctions.php",
        ])) {
            return true;
        }

        $allowForAll = [
            "checkSingleSignOnStat",
            "SignupNewUser",
            "validatevid",
            "updatepasswrd",
            "validateUserDetails",
            "processSingleSignOn",
        ];

        if (in_array(url::getToText('function'), $allowForAll)) {
            return true;
        }
        if (in_array(url::postToText('function'), $allowForAll)) {
            return true;
        }
        if (in_array(url::requestToText('function'), $allowForAll)) {
            return true;
        }

        $headers = getallheaders();
        if (
            isset($headers["Host"]) &&
            strrpos($headers["Host"], "dashboard.default.svc.cluster.local") !== false
        ) {
            // Allow all internal queries without checks for csrf.
            return true;
        }


        $headers = getallheaders();
        $X_Nh_MobileToken = getenv('X_Nh_MobileToken') ?: '729cf0bc198a493030dcf88ef3e4252d';
        if (
            isset($headers["X-Nh-Mobiletoken"]) && $headers["X-Nh-Mobiletoken"] ===  $X_Nh_MobileToken
        ) {
            // Add X-Nh-Mobiletoken for android app https://nanoheal.atlassian.net/browse/NCP-1937
            // if the call go to this files --> allow the query with hardcoded token 
            // curl -H "X-Nh-Mobiletoken: 729cf0bc198a493030dcf88ef3e4252d" https://testing.nanoheal.work/Dashboard/custom/MobileRegister1.php

            if (in_array($_SERVER["SCRIPT_NAME"], [
                "/Dashboard/custom/MobileRegister1.php",
                "/Dashboard/custom/json_android1.php",
                "/Dashboard/custom/json_browser1.php",
                "/Dashboard/custom/json_kiosk1.php",
            ])) {
                return true;
            }
        }

        if (nhRole::checkNhToken()) {
            return true;
        }

        logs::log("whitelist error", ["REQUEST_URI" => $_SERVER["REQUEST_URI"], "SCRIPT_NAME" => $_SERVER["SCRIPT_NAME"], "POST" => $_POST,  "GET" => $_GET]);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            http_response_code(423);
            die("Not auth error (whitelist) xhr");
        }

        header("Location: https://" . $_SERVER["HTTP_HOST"] . "/Dashboard/");
        die("Not auth error (whitelist) web");
    }
}
