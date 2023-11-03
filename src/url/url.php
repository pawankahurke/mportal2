<?php

/**
 * Class for validation values from $_GET and $_POST arrays.
 *
 * @package url
 * @category Utils
 *
 * \$_GET\[['"]([A-z0-9_-]+)['"]\]([^\[]) -> url::getToAny('$1')$2
 * \$_POST\[['"]([A-z0-9_-]+)['"]\]([^\[]) -> url::postToAny('$1')$2
 */
class url
{
    public static function isEmptyInGet($name)
    {
        return empty($_GET[$name]);
    }

    public static function isEmptyInPost($name)
    {
        return empty($_POST[$name]);
    }

    public static function isEmptyInRequest($name)
    {
        return empty($_REQUEST[$name]);
    }

    public static function isNumericInGet($name)
    {
        return is_numeric($_GET[$name]);
    }

    public static function isNumericInPost($name)
    {
        return is_numeric($_POST[$name]);
    }

    public static function isNumericInRequest($name)
    {
        return is_numeric($_REQUEST[$name]);
    }

    public static function issetInGet($name)
    {
        return isset($_GET[$name]);
    }

    public static function issetInPost($name)
    {
        return isset($_POST[$name]);
    }

    public static function issetInRequest($name)
    {
        return isset($_REQUEST[$name]);
    }

    public static function safeAny($val, $trace = 1)
    {
        if (!is_string($val)) {
            if (is_array($val)) {
                $keys = array_keys($val);
                foreach ($keys as $key) {
                    $val[$key] = self::safeAny($val[$key], $trace + 1);
                }
            }
            return $val;
        }


        $j = json_decode($val, true);
        // if ($j  === null) {
        //     logs::trace(1, "Warn:ToJson(safeAny) with invalid json:`" . substr($val, 0, 100) . "`");
        // }
        if ($j !== null) {
            return $val;
        }


        $res = preg_replace("#~#im", "X_splitRegExp_X", "" . $val);


        $res = preg_replace("~[^A-z0-9_. #;:,\[\]\(\)\{\}+=?`!@\/-]~im", "", $res);

        $res = preg_replace("#X_splitRegExp_X#im", "~", $res);

        if ($res != $val) {
            logs::trace($trace, 'Warning, safeAny get a string:', ["res" => $res, "src" => $val]);
        }
        return $res;
    }

    public static function requestToJson($name)
    {
        if (!isset($_REQUEST[$name])) {
            return "";
        }

        return self::safeAny($_REQUEST[$name], 2);
    }

    /**
     * Use another function with stricter data filtration
     * @deprecated
     */
    public static function requestToAny($name)
    {
        if (!isset($_REQUEST[$name])) {
            return "";
        }

        return self::safeAny($_REQUEST[$name], 2);
    }

    /**
     * Un safe
     */
    public static function rawPost($name)
    {
        if (!isset($_POST[$name])) {
            return "";
        }

        return  $_POST[$name];
    }

    /**
     * Un safe
     */
    public static function rawRequest($name)
    {
        if (!isset($_REQUEST[$name])) {
            return "";
        }

        return  $_REQUEST[$name];
    }

    /**
     * Use another function with stricter data filtration 
     */
    public static function postToAny($name)
    {
        if (!isset($_POST[$name])) {
            return null;
        }

        return self::safeAny($_POST[$name], 2);
    }

    /**
     * Use another function with stricter data filtration
     * @deprecated
     */
    public static function getToAny($name)
    {
        if (!isset($_GET[$name])) {
            return null;
        }

        return self::safeAny($_GET[$name], 2);
    }

    public static function getToInt($name)
    {
        if (!isset($_GET[$name])) {
            return 0;
        }

        return (int) $_GET[$name];
    }

    public static function requestToInt($name)
    {
        if (!isset($_REQUEST[$name])) {
            return 0;
        }

        return (int) $_REQUEST[$name];
    }

    public static function getToRegExp($name, $regexp)
    {
        if (!isset($_GET[$name])) {
            return "";
        }

        return preg_replace($regexp, "", urldecode($_GET[$name]));
    }

    public static function requestToText($name, $altValue = "")
    {
        if (!isset($_REQUEST[$name])) {
            return  $altValue;
        }

        return self::toText($_REQUEST[$name]);
    }

    public static function getToText($name, $altValue = "")
    {
        if (!isset($_GET[$name])) {
            return $altValue;
        }

        return self::toText($_GET[$name]);
    }

    public static function postToInt($name, $altValue = 0)
    {
        if (!isset($_POST[$name])) {
            return  $altValue;
        }

        return (int) $_POST[$name];
    }

    public static function getToBoolean($name)
    {
        if (!isset($_GET[$name])) {
            return 0;
        }

        return (int) $_GET[$name] > 0 || $_GET[$name] == "true";
    }

    public static function requestToBoolean($name)
    {
        if (!isset($_REQUEST[$name])) {
            return 0;
        }

        return (int) $_REQUEST[$name] > 0 || $_REQUEST[$name] == "true";
    }

    public static function postToBoolean($name)
    {
        if (!isset($_POST[$name])) {
            return 0;
        }

        return (int) $_POST[$name] > 0 || $_POST[$name] == "true";
    }

    public static function postToFloat($name)
    {
        if (!isset($_POST[$name])) {
            return 0;
        }
        return str_replace(',', '.', "" . (float) $_POST[$name]);
    }

    public static function getToFloat($name)
    {
        if (!isset($_GET[$name])) {
            return 0;
        }

        return str_replace(',', '.', "" . (float) $_POST[$name]);
    }


    public static function postToJson($name)
    {
        if (!isset($_POST[$name])) {
            return null;
        }

        $res =  safe_json_decode($_POST[$name], true);
        if ($res  === null) {
            logs::trace(1, "Warn:ToJson($name) with invalid json:`" . substr($_POST[$name], 0, 100) . "`");
        }
        return $res;
    }

    public static function getToJson($name)
    {
        if (!isset($_GET[$name])) {
            return null;
        }

        $res =  safe_json_decode($_GET[$name], true);
        if ($res  === null) {
            logs::trace(1, "Warn:ToJson($name) with invalid json:`" . substr($_GET[$name], 0, 100) . "`");
        }
        return $res;
    }

    public static function toStringAz09($in)
    {
        $out =  preg_replace("#[^A-z0-9_.\-\s]#im", "", $in . "");

        if ($out != $in) {
            logs::log("warn:" . __FUNCTION__ . " in!=out ", ["out" => $out, "in" => $in]);
        }
        return $out;
    }

    public static function postToStringAz09($name)
    {
        return self::toStringAz09(self::postToAny($name));
    }

    public static function getToStringAz09($name)
    {
        return self::toStringAz09(self::getToAny($name));
    }

    public static function requestToStringAz09($name)
    {
        return self::toStringAz09(self::rawRequest($name));
    }

    public static function toText($text1)
    {
        if (is_null($text1)) {
            return null;
        }

        if (!is_string($text1)) {
            $text1 = "" . $text1;
        }

        $text = urldecode($text1);
        $text = trim($text);
        stripslashes($text);
        $text = htmlspecialchars($text);
        $text = htmlentities($text);
        $text = safe_addslashes($text);

        if ($text1 != $text) {
            logs::log("warn:" . __FUNCTION__ . " in!=out ", ["out" => $text, "in" => $text1]);
        }

        return $text;
    }
    public static function postToText($name, $altValue = "")
    {
        if (!isset($_POST[$name])) {
            return $altValue;
        }

        return self::toText($_POST[$name]);
    }

    public static function postToTextWithException($name, $exception = "")
    {
        if (!isset($_POST[$name])) {
            return null;
        }
        $_POST[$name] = preg_replace('/' . $exception . '/mi', '#SEP', $_POST[$name]);
        $clearStr = preg_replace('/#SEP/mi', $exception, self::toText($_POST[$name]));
        return $clearStr;
    }

    public static function postToArrayWithException($name, $exception = "")
    {
        $val = $_POST[$name];
        if (!isset($val) || !is_array($val)) {
            return null;
        }
        $keys = array_keys($val);
        foreach ($keys as $key) {
            if (!empty($exception)) {
                $val[$key] = preg_replace('/' . $exception . '/mi', '#SEP', $val[$key]);
            }
            $val[$key] = self::safeAny($val[$key]);
            if (!empty($exception)) {
                $val[$key] = preg_replace('/#SEP/mi', $exception, self::toText($val[$key]));
            }
        }

        return $val;
    }

    public static function postToRegExp($name, $regexp)
    {
        if (!isset($_POST[$name])) {
            return "";
        }

        return preg_replace($regexp, "", urldecode($_POST[$name]));
    }

    public static function getUri($withParamString = true)
    {
        if (!$withParamString) {
            return explode('?', $_SERVER['REQUEST_URI'])[0];
        }

        return $_SERVER['REQUEST_URI'];
    }

    public static function arrayToString($array)
    {
        return implode(',', $_REQUEST[$array]);
    }
}
