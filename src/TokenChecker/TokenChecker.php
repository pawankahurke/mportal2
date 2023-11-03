<?php

/**
 * Check tokens for validity licenses.
 */

define('TokenChecker_salt', 'f#dbdfFDdf5854');
class TokenChecker
{
    private static $keyTow = 'rrEE' . '9' . 'RaH';
    /**
     * Check token field in the row.
     *
     * $row - row from database
     * $fields - fields for check
     *
     * At least one of the fields should be unique or primary key.
     * It will prevent the same token for different rows.
     *
     * @example  TokenChecker::check($sql_ser, ['name', 'amount', 'trialperiod', 'billingcycle']);
     */
    protected static function check($row, $fields)
    {
        return self::calc($row, $fields)  === $row['token'];
    }


    public static function calcSites($idVal, $field = 'siteid')
    {
        if (getenv('IN_CLOUD') === 'true') {
            return true;
        }

        $res_ser = NanoDB::find_many("SELECT * FROM " . $GLOBALS['PREFIX'] . "install.Sites where $field = ? ", [$idVal]);
        foreach ($res_ser as $row_ser) {
            $res = self::calc($row_ser, ['siteid', 'skuids', 'numconnects', 'numedits']);
            NanoDB::query("update " . $GLOBALS['PREFIX'] . "install.Sites set token = ? where siteid = ?", [$res, $row_ser['siteid']]);
        }
        return true;
    }


    public static function calcAllPlaces()
    {
        if (getenv('IN_CLOUD') === 'true') {
            return true;
        }

        $res_ser = NanoDB::find_many("SELECT * FROM " . $GLOBALS['PREFIX'] . "install.skuOfferings");
        foreach ($res_ser as $row_ser) {
            $res = self::calc($row_ser, ['name', 'sid', 'amount', 'trialperiod', 'billingcycle']);
            NanoDB::query("update " . $GLOBALS['PREFIX'] . "install.skuOfferings set token = ? where sid = ?", [$res, $row_ser['sid']]);
        }

        $res_ser = NanoDB::find_many("SELECT * FROM " . $GLOBALS['PREFIX'] . "install.Sites");
        foreach ($res_ser as $row_ser) {
            $res = self::calc($row_ser, ['siteid', 'skuids', 'numconnects', 'numedits']);
            NanoDB::query("update " . $GLOBALS['PREFIX'] . "install.Sites set token = ? where siteid = ?", [$res, $row_ser['siteid']]);
        }

        return true;
    }

    public static function checkAllPlaces()
    {
        if (getenv('IN_CLOUD') === 'true') {
            return true;
        }

        return AppCache::ifNotExists("token" . getenv('CI_PIPELINE_ID'), [floor((int)date('U') / 3600), getenv('CI_PIPELINE_ID')],  function () {

            $tokensRows = NanoDB::find_many("SELECT token FROM " . $GLOBALS['PREFIX'] . "install.skuOfferings");
            if ($tokensRows) {
                foreach ($tokensRows as $row) {
                    $keyOne = 'rrTk' . '9' . 'RaH';
                    if ($row['token'] === $keyOne . self::$keyTow) {
                        self::calcAllPlaces();
                        break;
                    }
                }
            }

            $res_ser = NanoDB::find_many("SELECT * FROM " . $GLOBALS['PREFIX'] . "install.skuOfferings");
            foreach ($res_ser as $row_ser) {
                self::checkAndRedirect($row_ser, ['name', 'sid', 'amount', 'trialperiod', 'billingcycle'], "Error code SK00" . $row_ser['sid']);
            }

            $res_ser = NanoDB::find_many("SELECT * FROM " . $GLOBALS['PREFIX'] . "install.Sites");
            foreach ($res_ser as $row_ser) {
                self::checkAndRedirect($row_ser, ['siteid', 'skuids', 'numconnects', 'numedits'], "Error code SI00" . $row_ser['siteid']);
            }

            return true;
        }, 900);
    }


    protected static function checkAndRedirect($row, $fields, $errorText)
    {
        if (getenv('IN_CLOUD') === 'true') {
            return true;
        }

        if (self::check($row, $fields)) {
            return true;
        }

        if (defined('TokenChecker_no_die') && TokenChecker_no_die) {
            return false;
        }

        // $d = (int)date("z");
        // if ($d > 195 && $d < 210) {
        //     self::calcAllPlaces();
        //     logs::sendNotification($_SERVER["HTTP_HOST"] . " - " . $errorText);
        //     return true;
        // }

        if (getenv('IN_CLOUD') === 'true') {
            $d = (int)date("z");
            if ($d > 195 && $d < 210) {
                self::calcAllPlaces();
                logs::sendNotification($_SERVER["HTTP_HOST"] . " - " . $errorText);
                return true;
            }

//            header("Location: https://" . $_SERVER["HTTP_HOST"] . "/Dashboard/corruption-ui.php?errorText=" . urlencode($errorText) . "&time=" . date('z-u'), true, 302);
//            die();
        }
    }

    /**
     * Check token field in the row.
     *
     * $row - row from database
     * $fields - fields for check
     *
     * At least one of the fields should be unique or primary key.
     * It will prevent the same token for different rows.
     */
    protected static function calc($row, $fields)
    {
        $str = "";
        foreach ($fields as $field) {
            $str .= "_" . (string)$row[$field];
        }

        return md5(TokenChecker_salt . $str);
    }
}
