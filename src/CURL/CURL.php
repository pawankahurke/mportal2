<?php

class CURL
{

    public static function getCookieHeader(): string
    {
        $h = getallheaders();
        if (isset($h['Cookie'])) {
            return $h['Cookie'];
        }
        return "";
    }

    public static function getContentByURL(string $strURL, $postData = null): string
    {
        logs::log(__FILE__, __LINE__, "getContentBycURL: $strURL", $postData);
        $ch = curl_init();

        if ($postData !== null) {
            if (!is_string($postData)) {
                $postData = json_encode($postData);
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        }

        $h =  array(
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),
            'PHPSESSID: ' . session_id(),
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
        logs::log("headers:", $h);

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Return data inplace of echoing on screen
        curl_setopt($ch, CURLOPT_URL, $strURL);
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $rsData = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode != 200) {
            logs::log(__FILE__, __LINE__, "[err=$httpcode]getContentBycURL url: $strURL");
            logs::log(__FILE__, __LINE__, "[err=$httpcode]getContentBycURL data: (postData=$postData) $rsData");
        } else {
            logs::log(__FILE__, __LINE__, "[ok=200]getContentBycURL rsData: (rsData=$rsData)");
        }

        return $rsData;
    }

    public static function uploadFileInStorage($folder, $file, $filename)
    {
        $token = JWT::getJWT([], getenv('APP_SECRET_KEY'));
        $url = 'http://' . getenv('DASHBOARD_SERVICE_HOST') . '/storage/api/upload?';

        $arrayPost = array(
            'token' => $token,
            'folder' => $folder,
            'fileName' => $filename,
        );

        try {
            $ch = curl_init($url . http_build_query($arrayPost, '', '&'));
            curl_setopt($ch, CURLOPT_POST, 1);
            $curl_file = curl_file_create($file);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array($curl_file));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "X-Nh-Token: " . nhRole::getNhTokenForHeader(),
                'PHPSESSID: ' . session_id(),
            ));

            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $result = safe_json_decode(curl_exec($ch), true);
            curl_close($ch);
        } catch (Exception $ex) {
            logs::log(__FILE__, __LINE__, $ex, 0);
            return "Exception : " . $ex;
        }
        return $result;
    }

    /*
     * function sending data to url
     *
     */
    public static function sendDataCurl($url, $data, $headers = [])
    {
        $headers[] =  'PHPSESSID: ' . session_id();
        $headers[] =  "X-Nh-Token: " . nhRole::getNhTokenForHeader();
        $post_data = http_build_query($data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        $result = curl_exec($curl);
        return $result;
    }

    public static function msTeamsWebHook($url, $data, $headers = [])
    {
        $curl = curl_init();
        $headers[] =  'PHPSESSID: ' . session_id();
        $headers[] =  "X-Nh-Token: " . nhRole::getNhTokenForHeader();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        $result = curl_exec($curl);
        return $result;
    }
}
