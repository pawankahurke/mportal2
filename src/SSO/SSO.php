<?php

/*
 * SAML Related Functions : END
 */

class SSO
{

    /**
     * To get the SSO related details using API through CURL
     *
     * https://dev-93728384.okta.com/app/exk2vqve5n7h0VTAP5d7/sso/saml/metadata
     * isexk2vqve5n7h0VTAP5d7
     */
    public static function getCurlResponse($reqType, $endPoint, $reqData)
    {

        global $ssosamlapiurl;

        $data_string = json_encode($reqData);

        $header = array(
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string),
        );
        logs::log(__FILE__, __LINE__, "getCurlResponse:: $ssosamlapiurl . $endPoint", 0);
      logs::log('IGOR_URL', $ssosamlapiurl . $endPoint);
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $ssosamlapiurl . $endPoint);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $reqType);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15); //timeout in seconds
            $result = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);
        } catch (Exception $ex) {
            logs::log(__FILE__, __LINE__, $ex, 0);
            return "Exception : " . $ex;
        }
        return ['code' => $httpcode, 'data' => $result, 'nonce' => $_SESSION['nonce']];
    }
}
