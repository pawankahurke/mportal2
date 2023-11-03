<?php

define('NH_LOGS_ALL', 5);
define('NH_LOGS_DEBUG', 4);
define('NH_LOGS_INFO', 3);
define('NH_LOGS_WARN', 2);
define('NH_LOGS_ERROR', 1);
define('NH_LOGS_NO', 0);

define('LOGS_CURRENT_LEVEL', (int)(getenv('NH_LOG_LEVEL') ?: NH_LOGS_INFO));


class logs
{
    // protected static $__fp;
    protected static $__query_id;
    protected static $__query_t;
    protected static $__last_t;

    public static function trace($level = 0, $msg = null, $objData = -1)
    {
        $trace = debug_backtrace();
        for ($i = $level; $i >= 0; $i--) {
            if (isset($trace[$i])) {
                $file =  $trace[$i]["file"];
                $line = $trace[$i]["line"];
                return self::log($file, $line, $msg, $objData);
            }
        }
    }

    public static function debug($msg = null, $objData = -1, $level = 0)
    {
        $d_level = (int)(getenv('NH_LOG_LEVEL') ?: 3);

        if ($d_level  >= NH_LOGS_DEBUG) {
            return self::trace($level + 1, $msg, $objData);
        }
    }

    public static function tag($tag, $msg = null, $objData = -1, $level = 1)
    {
        if (getenv("LOG_TAG_$tag") === "true") {
            self::trace($level, $msg, $objData);
            return true;
        }
        return false;
    }

    public static function sendNotification($msg = "")
    {
        if (getenv("LOG_TEAMS_WEBHOOK") === "false" || !getenv("LOG_TEAMS_WEBHOOK")) {
            return;
        }

        $msg = "[" . getenv('PROJECT_IMAGE_NAME') . "]<a href='https://" . getenv('DASHBOARD_SERVICE_HOST') . "'>
        Deployment: " . getenv('DEPLOYMENT_ID') . " - " . getenv('DASHBOARD_SERVICE_HOST') . "
        </a><br>
        " . $msg
            . "<br>---<br><pre>POST=" . json_encode($_POST) . "</pre>"
            . "<br>---<br><pre>GET=" . json_encode($_GET) . "</pre>";

        CURL::msTeamsWebHook(getenv("LOG_TEAMS_WEBHOOK"), json_encode(["text" => mb_strimwidth($msg, 0, 1000, "...")]), [
            'Content-Type' => 'application/json',
        ]);
    }

    public static function info($msg = null, $objData = -1, $level = 1)
    {
        if (LOGS_CURRENT_LEVEL  >= NH_LOGS_INFO) {
            return self::trace($level, $msg, $objData);
        }
    }

    public static function error($msg = null, $objData = -1, $level = 1)
    {
        if (LOGS_CURRENT_LEVEL  >= NH_LOGS_ERROR) {
            return self::trace($level, $msg, $objData);
        }
    }

    public static function warn($msg = null, $objData = -1, $level = 1)
    {
        if (LOGS_CURRENT_LEVEL  >= NH_LOGS_WARN) {
            return self::trace($level, $msg, $objData);
        }
    }

    public static function log($file, $line = -1, $msg = null, $objData = -1, $logType = 'null')
    {
        if (LOGS_CURRENT_LEVEL <= NH_LOGS_NO) {
            return;
        }
        // if ($logType === 'null') {
        //     return;
        // }

        if ($msg === null && $objData === -1) {
            $trace = debug_backtrace();
            $msg = $file;
            $objData =  $line;
            $file =  $trace[0]["file"];
            $line = $trace[0]["line"];
        }

        // if (!self::$__fp) {
        //     self::$__query_id = uniqid();
        //     self::$__query_t = microtime(true);
        //     if (rand(0, 100) > 90 && filesize("/var/www/html/error.log") >= 256 * 1024 * 1024) {
        //         // Remove log file if it is too big.
        //         @unlink("/var/www/html/error.log");
        //     }

        //     self::$__fp = fopen("/var/www/html/error.log", "a+");
        // }


        $filePath = str_replace("/var/www/html/main/", "", $file);
        $filePath = str_replace("/var/www/html/Dashboard/", "", $filePath);
        $res = [
            "uid" => self::$__query_id,
            "t" => floor(microtime(true)    * 1000) / 1000,
            "f" => $filePath,
            "l" => $line,
        ];

        if (getenv("LOG_TIME") === "true") {
            $res['s'] =  floor((microtime(true) - self::$__query_t) * 1000) / 1000;
            $res['d'] =  floor((microtime(true) - self::$__last_t) * 1000) / 1000;
        }


        self::$__last_t = microtime(true);
        if (is_array($msg)) {
            $res = array_merge($res, $msg);
        } else {
            $res["msg"] = $msg;
        }

        if (is_array($objData)) {
            $res = array_merge($res, $objData);
        } else {
            $res["objData"] = $objData;
        }


        // fwrite(self::$__fp, json_encode($res) . "\r\n");
        error_log("\n" . json_encode($res) . "\n", 0);

        if (isset($res['errno']) && $res['errno'] === 1) {
            self::sendNotification(json_encode($res));
        }
    }
}
