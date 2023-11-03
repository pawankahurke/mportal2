<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//error_reporting(-1);
//ini_set('display_errors', 'On');

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-dbConnect.php';
include_once '../include/common_functions.php';

nhRole::dieIfnoRoles(['sso']); // roles: sso

//Replace $routes['post'] with if else
if (url::postToText('function') === 'getConfiguredSSODetailsFunc') { // roles: sso
    getConfiguredSSODetailsFunc();
} else if (url::postToText('function') === 'getMetaDataDetailsFunc') { // roles: sso
    getMetaDataDetailsFunc();
} else if (url::postToText('function') === 'updateSsoStatusFunc') { // roles: sso
    updateSsoStatusFunc();
} else if (url::postToText('function') === 'verifyOAuthDetailsFunc') { // roles: sso
    verifyOAuthDetailsFunc();
} else if (url::postToText('function') === 'saveOauthDetailsFunc') { // roles: sso
    saveOauthDetailsFunc();
} else if (url::postToText('function') === 'verifySamlDetailsFunc') { // roles: sso
    verifySamlDetailsFunc();
} else if (url::postToText('function') === 'saveSamlDetailsFunc') { // roles: sso
    saveSamlDetailsFunc();
} else if (url::postToText('function') === 'clearSSODetailsFunc') { // roles: sso
    clearSSODetailsFunc();
}

function updateSsoStatusFunc()
{
    $pdo = pdo_connect();

    $domainName = $_SERVER['HTTP_HOST'];
    $ssoStatus = url::postToAny('sso_status');

    $chkstmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.singlesignon where domain_name = ?");
    $chkstmt->execute([$domainName]);
    $chkdata = $chkstmt->fetch(PDO::FETCH_ASSOC);

    if ($chkdata) {
        $uptstmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.singlesignon set sso_status = ? where domain_name = ?");
        $uptdata = $uptstmt->execute([$ssoStatus, $domainName]);

        if ($uptdata) {
            $response = ['code' => 200, 'data' => 'DONE'];
        } else {
            $response = ['code' => 200, 'data' => 'FAILED'];
        }
    } else {
        $response = ['code' => 200, 'data' => 'KEEP_ALIVE'];
    }

    echo json_encode($response);
}

function createRespectiveTable($dbName, $tableName)
{
    $pdo = pdo_connect();

    $stmt = $pdo->prepare("SELECT * FROM information_schema.TABLES where "
        . "TABLE_SCHEMA = ? and TABLE_NAME = ?;");
    $stmt->execute([$dbName, $tableName]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        // Create Table
        $crtstmt = $pdo->prepare("CREATE TABLE $dbName.$tableName (
                                ssoid INT(11) NOT NULL AUTO_INCREMENT,
                                domain_name VARCHAR(50) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
                                sso_type VARCHAR(50) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
                                company_name VARCHAR(100) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
                                idp_full_name VARCHAR(100) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
                                authorize_url VARCHAR(100) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
                                access_url VARCHAR(100) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
                                client_id VARCHAR(100) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
                                client_secret VARCHAR(100) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
                                scope VARCHAR(100) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
                                resource_url VARCHAR(100) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
                                oauth_version INT(11) NULL DEFAULT NULL,
                                tenant_id VARCHAR(50) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
                                oauth_vstatus INT(11) NULL DEFAULT '0',
                                idp_metadata_url VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
                                idp_metadata TEXT NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
                                sp_entity_id VARCHAR(100) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
                                acs_url VARCHAR(100) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
                                saml_vstatus INT(11) NULL DEFAULT '0',
                                sso_status INT(11) NULL DEFAULT '0',
                                createdtime INT(11) NULL DEFAULT NULL,
                                modifiedtime INT(11) NULL DEFAULT NULL,
                                PRIMARY KEY (ssoid) USING BTREE
                        ) COLLATE='latin1_swedish_ci' ENGINE=MyISAM;");
        $crtstmt->execute();
    }
    return 1;
}

function getConfiguredSSODetailsFunc()
{
    $pdo = pdo_connect();

    $domainName = $_SERVER['HTTP_HOST'];

    createRespectiveTable('core', 'singlesignon');

    $gtssostmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.singlesignon where domain_name = ?");
    $gtssostmt->execute([$domainName]);
    $gtssodata = $gtssostmt->fetch(PDO::FETCH_ASSOC);

    if ($gtssodata) {
        $response = ['code' => 200, 'data' => $gtssodata];
    } else {
        $response = ['code' => 200, 'data' => "NODATA"];
    }
    echo json_encode($response);
}

/*
 * OAUTH Related Functions : START
 */

function verifyOAuthDetailsFunc()
{

    global $base_url;
    global $ssosamlapiurl;

    $authorizeUrl = url::postToAny('authorizeUrl');
    $accessUrl = url::postToAny('accessUrl');
    $oauthVersion = url::postToAny('oauthVers');
    $clientID = url::postToAny('clientId');
    $clientSecret = url::postToAny('clientSecret');
    $scope = url::postToAny('scope');
    $resourceUrl = url::postToAny('resourceUrl');

    //scope parser
    $scopeDelimiter = "";
    $scopeData = explode(',', $scope);
    if (safe_count($scopeData) > 1) {
        $scopeDelimiter = ',';
    } else {
        $scopeData = explode(" ", $scope);
        $scopeDelimiter = " ";
    }

    $reqData['authorizeUrl'] = $authorizeUrl;
    $reqData['accessUrl'] = $accessUrl;
    $reqData['oauthVersion'] = $oauthVersion;
    $reqData['key'] = $clientID;
    $reqData['secret'] = $clientSecret;
    $reqData['scope'] = array_values($scopeData);
    $reqData['scope_delimiter'] = $scopeDelimiter;
    $reqData['baseRedirectUrl'] = $base_url . 'verifySSO.php';
    $reqData['resourceUrl'] = $resourceUrl;

    //print_r($reqData); die();

    $resp = SSO::getCurlResponse("POST", '/api/oauth/login/provider/details', $reqData);
    $resp['reurl'] = $ssosamlapiurl;


    echo json_encode($resp);
}

function saveOauthDetailsFunc()
{
    $pdo = pdo_connect();

    $domainName = $_SERVER['HTTP_HOST'];
    $ssoType = url::postToAny('ssoType');
    $companyName = url::postToAny('companyName');
    $idpName = url::postToAny('idpName');
    $authorizeUrl = url::postToAny('authorizeUrl');
    $accessUrl = url::postToAny('accessUrl');
    $clientId = url::postToAny('clientId');
    $clientSecret = url::postToAny('clientSecret');
    $scope = url::postToAny('scope');
    $resourceUrl = url::postToAny('resourceUrl');
    $oauthVersion = url::postToAny('oauthVers');
    $tenantId = url::postToAny('tenantId');
    $ssoStatData = url::postToAny('ssoStatData');

    $now = time();

    try {
        $gtoauthstmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.singlesignon where domain_name = ?");
        $gtoauthstmt->execute([$domainName]);
        $gtoauthdata = $gtoauthstmt->fetch(PDO::FETCH_ASSOC);

        if ($gtoauthdata) {
            // Update configured OAuth details
            $utoauthstmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.singlesignon set sso_type = ?, company_name = ?, idp_full_name = ?, "
                . "authorize_url = ?, access_url = ?, client_id = ?, client_secret = ?, scope = ?, "
                . "oauth_version = ?, tenant_id = ?, resource_url = ? where domain_name = ?");
            $utoauthdata = $utoauthstmt->execute([
                $ssoType, $companyName, $idpName, $authorizeUrl, $accessUrl,
                $clientId, $clientSecret, $scope, $oauthVersion, $tenantId, $resourceUrl, $domainName,
            ]);
            if ($utoauthdata) {
                $response = ['code' => 200, 'data' => 'UPDATED'];
            } else {
                $response = ['code' => 200, 'data' => 'UPDATE_FAILED'];
            }
        } else {
            // Make New OAuth entry
            $stoauthstmt = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.singlesignon (domain_name, sso_type, "
                . "company_name, idp_full_name, authorize_url, access_url, client_id, "
                . "client_secret, scope, resource_url, oauth_version, tenant_id, sso_status, createdtime) "
                . "values (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stoauthdata = $stoauthstmt->execute([
                $domainName, $ssoType, $companyName,
                $idpName, $authorizeUrl, $accessUrl, $clientId, $clientSecret, $scope,
                $resourceUrl, $oauthVersion, $tenantId, $ssoStatData, $now,
            ]);
            if ($stoauthdata) {
                $response = ['code' => 200, 'data' => 'DONE'];
            } else {
                $response = ['code' => 200, 'data' => 'FAILED'];
            }
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        $response = ['code' => 200, 'data' => 'ERROR'];
    }

    echo json_encode($response);
}

function clearSSODetailsFunc()
{
    $pdo = pdo_connect();

    $domainName = $_SERVER['HTTP_HOST'];

    $dltstmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'core.singlesignon where domain_name = ?');
    $dltdata = $dltstmt->execute([$domainName]);

    if ($dltdata) {
        $response = ['code' => 200, 'data' => 'DONE'];
    } else {
        $response = ['code' => 200, 'data' => 'FAILED'];
    }

    echo json_encode($response);
}

/*
 * OAUTH Related Functions : END
 */

/*
 * SAML Related Functions : START
 */

function checkValidUrl($url): bool
{
    $checkUrl = preg_match('/^(http|https)?:\/\/[a-zA-Z0-9-\.]+\.[a-z]{2,4}/', $url);
    $checkProtocol = preg_match('/^((?!.*?(?:http|https))\w+:\/\/)/m', $url);
    $checkLocalhost = preg_match('/(localhost)/mi', $url);
    $checkIPv4 = preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/mi', $url);
    $checkIPv6 = preg_match('/(([a-f0-9:]+:+)+[a-f0-9]+)/mi', $url);
    return $checkUrl && !($checkProtocol && $checkLocalhost && $checkIPv4 && $checkIPv6);
}

function getMetaDataDetailsFunc()
{
    $metaDataUrl = url::postToAny('metadataurl');

    if ($metaDataUrl && $metaDataUrl !== '') {
        if (checkValidUrl($metaDataUrl)) {
            $metaData = CURL::getContentByURL($metaDataUrl);
            if ($metaData) {
                $reader = XMLReader::XML($metaData);
                $reader->setParserProperty(XMLReader::VALIDATE, true);
                // $checkXml = preg_match("/^(<\?xml)/", preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $metaData));

                if ($reader->isValid()) { // early here was been $checkXml too
                    $response = ['code' => 200, 'data' => $metaData];
                } else {
                    $response = ['code' => 401, 'data' => "You entered an invalid URL"];
                }
            } else {
                $response = ['code' => 401, 'data' => "Meta data cannot be read"];
            }
        } else {
            $response = ['code' => 401, 'data' => "You entered an invalid URL"];
        }
    } else {
        $response = ['code' => 401, 'data' => 'Meta data URL is empty.'];
    }
    echo json_encode($response);
}

function verifySamlDetailsFunc()
{
    // @todo remov ui for this call in next release.
    echo json_encode(['code' => '200', 'data' => "ok"]);
}

function saveSamlDetailsFunc()
{
    global $ssosamlapiurl;
    $pdo = pdo_connect();

    $domainName = $_SERVER['HTTP_HOST'];
    $ssoType = url::postToAny('ssoType');
    $companyName = url::postToAny('companyName');
    $idpName = url::postToAny('idpName');
    $idpMetadataUrl = url::postToAny('idpMetadataUrl');
    $idpMetaData = url::rawPost('idpMetaData');
    $spEntityId = url::postToAny('spEntityId');
    $acsUrl = $ssosamlapiurl . '/api/saml/callback';
    $ssoStatData = url::postToAny('ssoStatData');

    $now = time();

    try {
        $gtsamlstmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.singlesignon where domain_name = ?");
        $gtsamlstmt->execute([$domainName]);
        $gtsamldata = $gtsamlstmt->fetch(PDO::FETCH_ASSOC);

        if ($gtsamldata) {
            // Update
            $utsamlstmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.singlesignon set sso_type = ?, company_name = ?, "
                . "idp_full_name = ?, idp_metadata_url = ?, idp_metadata = ?, sp_entity_id = ?, "
                . "acs_url = ?, modifiedtime = ? where domain_name = ?");
            $utsamldata = $utsamlstmt->execute([
                $ssoType, $companyName, $idpName, $idpMetadataUrl,
                $idpMetaData, $spEntityId, $acsUrl, $now, $domainName,
            ]);
            if ($utsamldata) {
                $response = ['code' => 200, 'data' => 'UPDATED'];
            } else {
                $response = ['code' => 200, 'data' => 'UPDATE_FAILED'];
            }
        } else {
            //Insert
            $stsamlstmt = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.singlesignon (domain_name, sso_type, "
                . "company_name, idp_full_name, idp_metadata_url, idp_metadata, sp_entity_id, "
                . "acs_url, sso_status, createdtime) values (?,?,?,?,?,?,?,?,?,?)");
            $stsamldata = $stsamlstmt->execute([
                $domainName, $ssoType, $companyName, $idpName,
                $idpMetadataUrl, $idpMetaData, $spEntityId, $acsUrl, $ssoStatData, $now,
            ]);
            if ($stsamldata) {
                $response = ['code' => 200, 'data' => 'DONE'];
            } else {
                $response = ['code' => 200, 'data' => 'FAILED'];
            }
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        $response = ['code' => 200, 'data' => 'ERROR'];
    }

    echo json_encode($response);
}
