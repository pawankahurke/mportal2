<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
global $base_path;

class ApiHelper
{
    public static function getAccessToken()
    {

        $accessTokenExpiry = isset($_SESSION['access_token_expiry']) ? $_SESSION['access_token_expiry'] : false;
        $accessToken = isset($_SESSION['access_token']) ? $_SESSION['access_token'] : false;

        if (!$accessToken || !$accessTokenExpiry || time() >= $accessTokenExpiry) {
            global $NH_API_URL;
            $url = $NH_API_URL . 'login';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => 'admin@nanoheal.com', 'password' => 'nanoheal@123']));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            $headers = array(
                "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
            );
            $headers[] = "Content-Type: application/json";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $errorNo = curl_errno($ch);
            if ($errorNo) return false;

            $result = curl_exec($ch);
            $result = safe_json_decode($result, true);

            if (isset($result['status']) && $result['status'] == 'success' && isset($result['result']['access_token'])) {
                $accessToken = $result['result']['access_token'];
                $expiresIn = isset($result['result']['expires_in']) ? $result['result']['expires_in'] : false;
                $_SESSION['access_token'] = $accessToken;

                if ($expiresIn && is_numeric($expiresIn)) {
                    $expiresIn = strtotime('+' . $expiresIn . ' second');
                    $_SESSION['access_token_expiry'] = $expiresIn;
                }
            }
        }

        return $accessToken;
    }
}

function ctb($v)
{
    return $v == '1' ? true : false;
}

function rsv($key, $default = '1', $index = null)
{
    $post = is_null($index) ? (isset($_POST[$key]) ? $_POST[$key] : false) : (isset($_POST[$key][$index]) ? $_POST[$key][$index] : false);

    if (!isset($post) || !($post)) {
        if ($default == '1') {
            $return =  '0';
        } else if ($default == '0') {
            $return =  '1';
        }
    } else {
        if ($default == '1') {
            $return =  isset($post) && $post == 'on' ? '1' : '0';
        } else if ($default == '0') {
            $return =  isset($post) && $post == 'on' ? '0' : '1';
        }
    }

    return $return;
}

function post($key, $index = null)
{
    if (!is_null($index)) {
        return isset($_POST[$key][$index]) ? $_POST[$key][$index] : null;
    }

    return isset($_POST[$key]) ? $_POST[$key] : null;
}


function array_column_multi($array, $key, $multiDimension = false)
{
    if (!is_array($array)) {
        return $array;
    }

    $pluckFromArray = function ($array, $key) {
        $array = array_filter($array, function ($values) use ($key) {
            if (in_array($values, $key)) {
                return true;
            } else {
                return false;
            }
        }, ARRAY_FILTER_USE_KEY);

        return $array;
    };

    if ($multiDimension) {
        $finalArray = [];
        foreach ($array as $keys => $eachArray) {
            $finalArray[$keys] = $pluckFromArray($eachArray, $key);
        }

        return $finalArray;
    }

    return $pluckFromArray($array, $key);;
}

function array_replace_keys($replacements, $array, $multiDimension = false)
{
    $replace = function ($replacements, $array) {
        $newArrayKeys = [];
        $array_values = array_values($array);
        $safe_array_keys = safe_array_keys($array);
        foreach ($safe_array_keys as $eachKeys) {
            if (isset($replacements[$eachKeys])) {
                $replace = $replacements[$eachKeys];
            } else {
                $replace = $eachKeys;
            }

            $newArrayKeys[] = $replace;
        }

        $array = array_combine($newArrayKeys, $array_values);

        return $array;
    };

    if ($multiDimension) {
        $finalArray = [];
        foreach ($array as $keys => $eachArray) {
            $finalArray[$keys] = $replace($replacements, $eachArray);
        }

        return $finalArray;
    }

    return $replace($replacements, $array);
}
