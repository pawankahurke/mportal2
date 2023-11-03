<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
global $base_path;
require_once $base_path . 'lib/l-db.php';
require_once $base_path . 'include/common_functions.php';
require_once $base_path . 'include/helpers.php';

error_reporting(E_ALL);
ini_set("display_errors", "On");

if (!isset($_SESSION)) {
}

if (!isset($_SESSION['user']['userid'])) {
    header("location:../index.php");
}

nhRole::dieIfnoRoles(['softwaredistribution']); // roles: softwaredistribution

$routes = [
    'save' =>  'saveSoftwareDistribution',
    'list' =>  'fetchSwdDataTableData',
    'details' =>  'details',
    'configuration' =>  'getConfiguration',
    'push-configuration' => 'pushConfiguration',
    'save-cdn-credentials' => 'saveCdnCredentials',
    'save-ftp-credentials' => 'saveFtpCredentials',
    'fetch-credentials' => 'fetchCredentials'
];

if (url::issetInRequest('function')) {
    if (array_key_exists(url::requestToAny('function'), $routes)) {
        $function = $routes[url::requestToAny('function')];
        call_user_func($function);
    }
}

function formatValidationErrors($errorsArray)
{
    $newErrors = [];

    foreach ($errorsArray as $key => $eachErrors) {
        $inErrors = [];

        foreach ($eachErrors as $k => $eachInnerErrors) {
            if (strpos($eachInnerErrors, "::")) {
                $eachInnerErrors = str_replace("::", " at index ", $eachInnerErrors);
            }
            $inErrors[$k] = $eachInnerErrors;
        }

        $newErrors[$key] = $inErrors;
    }

    return $newErrors;
}

function uploadToS3($file)
{
    require_once '../swd/vendor/autoload.php';

    $userId = $_SESSION['user']['userid'];
    $s3Config = isset($_SESSION['software_distribution']['upload_configurations']['cdn']) && is_array($_SESSION['software_distribution']['upload_configurations']['cdn']) && array_key_exists('url', $_SESSION['software_distribution']['upload_configurations']['cdn']) ? $_SESSION['software_distribution']['upload_configurations']['cdn'] : false;

    if (!$s3Config) {
        $dbRow = getCdnCredentialsByUserId($userId);
        if (!$dbRow) return false;
        $s3Config = $dbRow;
    }

    try {
        $s3 =  Aws\S3\S3Client::factory(
            [
                'credentials' => [
                    'key' => $s3Config['access_key'],
                    'secret' => $s3Config['secret_key']
                ],
                'version' => 'latest',
            ]
        );
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        die(json_encode(['success' => false, 'message' => "Could not connect to CDN server " . $e->getMessage()]));
    }

    $filePathInfo = pathinfo($file);
    $baseName = $filePathInfo['basename'];

    try {

        $result = $s3->putObject(
            array(
                'Bucket' => $s3Config['bucket_name'],
                'ACL' => 'public-read',
                'Key' => $baseName,
                'SourceFile' => '../swd/' . $file,
                'Body' => '../swd/' . $file,
                'StorageClass' => 'REDUCED_REDUNDANCY'
            )
        );
    } catch (S3Exception $e) {
        die(json_encode(['success' => false, 'message' => $e->getMessage()]));
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        die(json_encode(['success' => false, 'message' => $e->getMessage()]));
    }

    if ($result) {
        return $uploadedUrl = 'https://' . $s3Config['bucket_name'] . '.s3.amazonaws.com/' . $baseName;
    }

    return false;
}

function uploadWithFtp($file)
{
    $userId = $_SESSION['user']['userid'];
    $ftpConfig = isset($_SESSION['software_distribution']['upload_configurations']['ftp']) && is_array($_SESSION['software_distribution']['upload_configurations']['ftp']) && array_key_exists('url', $_SESSION['software_distribution']['upload_configurations']['ftp']) ? $_SESSION['software_distribution']['upload_configurations']['ftp'] : false;

    if (!$ftpConfig) {
        $dbRow = getFtpCredentialsByUserId($userId);
        if (!$dbRow) return false;
        $ftpConfig = $dbRow;
    }

    $remoteFile = pathinfo($file);
    $remoteFile = $remoteFile['basename'];
    $login = ftp_login($ftpConn, $ftpConfig['username'], $ftpConfig['password']);

    if (ftp_put($ftpConn, '/var/www/html/' . $remoteFile, $file, FTP_BINARY)) {
        return $ftpConfig['url'] . '/' . $remoteFile;
    }

    ftp_close($ftpConn);

    return false;
}

function saveSoftwareDistribution()
{
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $priviledge = checkModulePrivilege('addsoftwaredistribution', 2);
        if (!$priviledge) exit(json_encode(['success' => false, 'message' => 'Permission denied']));

        global $NH_API_URL;

        $isConfigureExecute = url::issetInGet('configure-execute') && url::getToText('configure-execute') === 'true';
        $operation = $isConfigureExecute ? 'configure-execute' : (url::issetInPost('opt') && url::postToText('opt') === 'update' ? 'update' : 'create');
        $requestMethod = ($operation == 'create') ? 'POST' : 'PUT';
        $packageId = post('package-id');
        $platform = post('platform');

        if (!in_array($platform, ['windows', 'linux', 'mac', 'android', 'ios'])) {
            echo (json_encode(['success' => false, 'message' => 'Invalid platform']));
        }

        $url = $isConfigureExecute ? $NH_API_URL . 'v1/swd/configure-execute/' . $platform . '/' . $packageId : (($operation == 'create') ? $NH_API_URL . 'v1/swd/create/' . $platform : $NH_API_URL . 'v1/swd/update/' . $platform . '/' . $packageId);
        $apiMode = post('api-mode');
        $totalSegments = safe_sizeof($apiMode);
        $packageType = post('package-type');
        $packageTypeDistribute = 'distribute';
        $packageTypeExecute = 'execute';
        $windowsPlatformType = 'windows';

        $postRequest = [
            'user_id' => $_SESSION['user']['userid'],
            'package_name' => post('package-name'),
            'platform' => $platform,
            'package_type' => $isConfigureExecute ? 'execute' : post('package-type'),
            'is_global' => ctb(rsv('global')),
            'status_message_box' => ctb(rsv('status-message-box', '0')),
            'message_box_text' => empty(post('message-box-text')) ? null : post('message-box-text'),
            'max_time_per_patch' => empty(post('max-time-per-patch')) ? null : post('max-time-per-patch'),
            'process_to_kill' => (empty(post('process-to-kill')) ? null : post('process-to-kill')),
        ];

        if ($operation == 'update') $postRequest['id'] = $packageId;

        $postRequest['windows_type'] = post('windows-type');

        if ($packageType == $packageTypeExecute) {
            $postRequest = array_merge($postRequest, [
                'positive' => post('positive'),
                'negative' => post('negative'),
                'special' => post('special'),
                'log_file' => post('log-file'),
                'default' => post('default'),
                'delete_log_file' => ctb(rsv('delete-log-file', '0')),
            ]);
        }

        $platformTypeCases = [''];
        if (url::issetInPost('windows-type')) {
            if (!url::isEmptyInPost('windows-type')) {
                switch (url::postToAny('windows-type')) {
                    case '32':
                        $platformTypeCases = ['32'];
                        break;
                    case '64':
                        $platformTypeCases = ['64'];
                        break;
                    case 'both':
                        $platformTypeCases = ['32', '64'];
                        break;
                }
            }
        }

        $topSegmentKeys = ['session' => 'session', 'api-mode' => 'api_mode', 'url-credential' => 'url_credential', 'sleep-time' => 'sleep_time', 'hash-check' => 'hash_check'];
        $pCtK = 'precheck-type';
        $executeIfFields = [
            1 => ['file_execute_if' => 'file-precheck'],
            3 => ['registry_execute_if' => 'registry-precheck'],
        ];

        $preCheckKeys = function ($type) {
            $k = false;
            if (is_numeric($type)) {
                switch (intval($type)) {
                    case 3:
                        $k = ['registry_main_key' => 'root-key', 'registry_sub_key' => 'sub-key', 'registry_name' => 'sub-key-name', 'registry_type' => 'type', 'registry_value' => 'type-value'];
                        break;
                    case 1:
                        $k = ['file_path' => 'file-path'];
                        break;
                    case 2:
                        $k = ['software_name' => 'software-name', 'software_version' => 'software-version', 'software_knowledge_base' => 'knowledge-base', 'software_service_pack' => 'service-pack'];
                        break;
                }
            }

            return $k;
        };

        $getPathUrlKeys = function ($type, $packageType, $packageTypeDistribute, $packageTypeExecute) {
            $k = false;
            if (is_numeric($type)) {
                switch (intval($type)) {
                    case 1:
                        $k = ['source_type' => 'upload-to', 'uploaded_file' => 'uploaded-file-name'];
                        break;
                    case 2:
                        $k = ['file_url' => 'package-url'];
                        break;
                    case 3:
                        $k = ['file_path' => 'package-path'];
                        break;
                }

                $k['post_validation'] = 'post-validation';
                if (is_array($k) && $packageType == $packageTypeDistribute) $k['distribution_path'] = 'distribution-path';
                if (is_array($k) && $packageType == $packageTypeExecute) $k['command_line'] = 'command-line';
            }
            return $k;
        };

        $isAnyPreCheckFieldSet = function ($data, $platform, $i) {

            $isSet = false;
            foreach ($data as $key => $val) {
                $vS = $val . '-' . $platform;
                if (isset($_POST[$vS][$i]) && !empty($_POST[$vS][$i])) {
                    $isSet = true;
                    break;
                }
            }
            return $isSet;
        };

        for ($i = 0; $i < $totalSegments; $i++) {
            foreach ($topSegmentKeys as $eKsK => $eKs) {
                if (isset($_POST[$eKsK][$i])) {
                    $segment[$eKs] = $_POST[$eKsK][$i];
                }
            }

            $segment['propagation'] = isset($_POST['propagation'][$i]) ? ctb(rsv('propagation', '0', $i)) : true;
            $segment['resume_download'] = isset($_POST['resume-download'][$i]) ? ctb(rsv('resume-download', '0', $i)) : true;
            $segment['patch_dependency'] = '';
            $pathUrlData = $preCheckData = [];

            foreach ($platformTypeCases as $epTs) {
                $puTypeKey = 'source-type-' . $epTs;
                $puType = isset($_POST[$puTypeKey][$i]) ? $_POST[$puTypeKey][$i] : null;
                $pathUrlKeys = $getPathUrlKeys($puType, $packageType, $packageTypeDistribute, $packageTypeExecute);
                $uploadedFile = isset($_POST['uploaded-file-name-' . $epTs][$i]) ? $_POST['uploaded-file-name-' . $epTs][$i] : false;
                $isNewUpload = $uploadedFile ? strpos($uploadedFile, $_SERVER["HTTP_HOST"]) : false;
                if ($pathUrlKeys) {
                    foreach ($pathUrlKeys as $ePuK => $ePu) {
                        $vS = $ePu . '-' . $epTs;

                        if ($ePu == 'upload-to') {
                            $uploadType = rsv($vS, '1', $i);

                            if (is_numeric($uploadType) && $isNewUpload) {
                                if (intval($uploadType) == 0) {
                                    if (isset($_POST['uploaded-file-name-' . $epTs][$i])) {
                                        $uploadedFile = uploadWithFtp($_POST['uploaded-file-name-' . $epTs][$i]);
                                        if (!$uploadedFile) exit(json_encode(['success' => false, 'message' => 'Please configure FTP']));
                                    }
                                } else {
                                    if (isset($_POST['uploaded-file-name-' . $epTs][$i])) {
                                        $uploadedFile = uploadToS3($_POST['uploaded-file-name-' . $epTs][$i]);
                                        if (!$uploadedFile) exit(json_encode(['success' => false, 'message' => 'Please configure CDN']));
                                    }
                                }
                            }
                            $pathUrlData[$epTs][$ePuK] = $uploadType;
                        } else {
                            if (isset($_POST[$vS][$i])) $pathUrlData[$epTs][$ePuK] = ($ePu == 'uploaded-file-name') ? $uploadedFile : $_POST[$vS][$i];
                        }
                    }

                    $pathUrlData[$epTs]['type'] = $puType;
                }

                $preCheckType = $pCtK . '-' . $epTs;
                $modeKey = 'precheck-type-' . $epTs;
                $mode = isset($_POST[$modeKey][$i]) ? $_POST[$modeKey][$i] : null;
                $preCheckKeyArray = $preCheckKeys($mode);

                if ($preCheckKeyArray && $isAnyPreCheckFieldSet($preCheckKeyArray, $epTs, $i)) {
                    foreach ($preCheckKeyArray as $ePcK => $ePc) {
                        $vS = $ePc . '-' . $epTs;
                        if (isset($_POST[$vS][$i])) {
                            $preCheckData[$epTs][$ePcK] = $_POST[$vS][$i];
                        }
                    }

                    if ($mode && isset($executeIfFields[$mode]) && is_array($executeIfFields[$mode])) {
                        foreach ($executeIfFields[$mode] as $eIfK => $eif) {
                            $vS = $eif . '-' . $i . '-' . $epTs;
                            if (isset($_POST[$vS])) {
                                $preCheckData[$epTs][$eIfK] = $_POST[$vS];
                            }
                        }
                    }

                    $preCheckData[$epTs]['mode'] = $mode;
                }
            }

            $segment['path_url'] = $pathUrlData;
            $segment['pre_check'] = $preCheckData;
            $segmentData[] = $segment;
        }

        $postRequest['segments'] = $segmentData;
        $postRequest = ['data' => $postRequest];
        $accessToken = ApiHelper::getAccessToken();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postRequest));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestMethod);

        $headers = array(
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        );
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Bearer " . $accessToken;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $errorNo = curl_errno($ch);

        if ($errorNo) {
            echo (json_encode(['success' => false, 'message' => 'Something went wrong, unable to add package']));
        }

        $result = curl_exec($ch);
        $result = safe_json_decode($result, true);
        $responseStatus = isset($result['status']) ? $result['status'] : false;

        if ($responseStatus == 'error') {
            exit(json_encode(['success' => false, 'validator' => true, 'data' => $result['error']['message']]));
        } else if ($responseStatus == 'success') {
            exit(json_encode(['success' => true, 'message' => 'Sucessfully saved software distribution']));
        }
    }

    echo (json_encode(['success' => true, 'message' => 'Something went wrong']));
}


function requestUserAllPackageCount($userId)
{
    global $NH_API_URL;

    $url = $NH_API_URL . 'v1/swd/count/all/' . $userId;
    $accessToken = ApiHelper::getAccessToken();
    $ch = curl_init();

    $headers = [
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        "Content-Type: application/json",
        "Authorization: Bearer " . $accessToken
    ];
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorNo = curl_errno($ch);

    if ($errorNo) {
        return false;
    }

    $result = curl_exec($ch);
    curl_close($ch);

    $result = safe_json_decode($result, true);
    $responseStatus = isset($result['status']) ? $result['status'] : false;

    if ($responseStatus == 'error') {
        return false;
    }

    return isset($result['result']) ? $result['result'] : '0';
}

function requestList(int $userId, $search = null, $orderBy = null, $orderDirection = null, $offset = null, $limit = null)
{
    global $NH_API_URL;

    $accessToken = ApiHelper::getAccessToken();
    $ch = curl_init();
    $headers = [
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        "Content-Type: application/json",
        "Authorization: Bearer " . $accessToken
    ];


    if (!is_null($offset)) {
        $requestArray['page'] = $offset;
        if (!is_null($offset)) $requestArray['size'] = $limit;
    } else {
        $requestArray = ['page' => $offset, 'size' => $limit];
    }

    $requestArray['user-id'] = $userId;

    if (!is_null($search)) {
        $requestArray['search'] = $search;
    }

    if (!is_null($orderBy)) {
        $requestArray['sort-field'] = $orderBy;
        if (!is_null($orderDirection)) {
            $requestArray['sort-order'] = $orderDirection;
        }
    }

    $url = $NH_API_URL . 'v1/swd?' . http_build_query($requestArray);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorNo = curl_errno($ch);

    if ($errorNo) {
        return false;
    }

    $result = curl_exec($ch);

    curl_close($ch);

    $result = safe_json_decode($result, true);
    $responseStatus = isset($result['status']) ? $result['status'] : false;

    if ($responseStatus == 'error') {
        return false;
    }

    return $result['result'];
}

function fetchSwdDataTableData()
{
    $userId = isset($_SESSION['user']['userid']) && is_numeric($_SESSION['user']['userid']) ? $_SESSION['user']['userid'] : false;
    $draw = url::issetInPost('draw') ? url::postToAny('draw') : 1;
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);

    if (!$userId || !$priviledge) {
        exit(json_encode(['draw' => $draw, 'recordsTotal' => 0, 'data' => []]));
    }

    $totalRows = requestUserAllPackageCount($userId);

    if (!$totalRows) {
        exit(json_encode(['draw' => $draw, 'recordsTotal' => 0, 'data' => []]));
    }

    $offset = (url::issetInPost('start') && url::isNumericInPost('start')) ? url::postToInt('start') : 0;
    $limit = (url::issetInPost('length') && url::isNumericInPost('length')) ? url::postToAny('length') : 20;
    $searchString = (isset($_POST['search']['value']) && !empty($_POST['search']['value'])) ? $_POST['search']['value'] : null;
    $columNameByIndex = function ($index) {
        $columnName = 'id';
        switch (intval($index)) {
            case 1:
                $columnName = 'id';
                break;
            case 2:
                $columnName = 'name';
                break;
            case 3:
                $columnName = 'platform';
                break;
            case 4:
                $columnName = 'has-distribution';
                break;
            case 5:
                $columnName = 'has-execution';
                break;
            case 6:
                $columnName = 'has-execution-and-distribution';
                break;
            case 7:
                $columnName = 'global';
                break;
            case 8:
                $columnName = 'created-date';
                break;
            case 9:
                $columnName = 'modified-date';
                break;
        }

        return $columnName;
    };

    $orderBy = (isset($_POST['order'][0]['column']) && is_numeric($_POST['order'][0]['column'])) ? $columNameByIndex($_POST['order'][0]['column']) : null;
    $orderDirection = (isset($_POST['order'][0]['dir']) && !empty($_POST['order'][0]['dir'])) && in_array($_POST['order'][0]['dir'], ['asc', 'desc']) ? $_POST['order'][0]['dir'] : null;

    $list = requestList($userId, $searchString, $orderBy, $orderDirection, $offset, $limit);

    if (!$list) {
        exit(json_encode(['draw' => $draw, 'recordsTotal' => 0, 'data' => []]));
    }

    $rI = function ($status) {
        return (is_numeric($status) && intval($status) == 1) ? '<i class="dt-icons-l tim-icons icon-check-2 r-ic-sm success"></i>' : '<i class="dt-icons-l tim-icons icon-simple-remove r-ic-sm error"></i>';
    };

    $ic = function ($type) {
        switch ($type) {
            case 'windows':
                $img = '<div class="list-pl windows">&nbsp;</div>';
                break;
            case 'linux':
                $img = '<div class="list-pl linux">&nbsp;</div>';
                break;
            case 'mac':
                $img = '<div class="list-pl mac">&nbsp;</div>';
                break;
            case 'ios':
                $img = '<div class="list-pl ios">&nbsp;</div>';
                break;
            case 'android':
                $img = '<div class="list-pl android">&nbsp;</div>';
                break;
        }

        return $img;
    };

    $newList = [];

    foreach ($list as $columnName => $rowData) {
        $newList[] = [
            'sl-id' => $rowData['id'],
            'sl-icon' => '<input name="dt-package-id" type="hidden" value="' . $rowData['id'] . '" />' . $ic($rowData['platform']),
            'sl-name' => $rowData['name'],
            'sl-platform' => ucfirst($rowData['platform']),
            'sl-distribution' => $rI($rowData['has_distribution']) . '<input type="hidden" value="' . $rowData['has_distribution'] . '" name="has_distribution" />',
            'sl-execution' => $rI($rowData['has_execution']) . '<input type="hidden" value="' . $rowData['has_execution'] . '" name="has_execution" />',
            'sl-global' => $rI($rowData['is_global']),
            'sl-created' => date('Y-m-d H:i:s', strtotime($rowData['created_at'])),
            'sl-updated' => date('Y-m-d H:i:s', strtotime($rowData['last_update']))
        ];
    }

    $dataTableData = ["draw" => $draw, "recordsTotal" => $totalRows, "recordsFiltered" => $totalRows, "data" => $newList];

    echo json_encode($dataTableData);
}

function requestDetails(int $id, $type = null, $userId = '')
{
    global $NH_API_URL;

    $accessToken = ApiHelper::getAccessToken();
    $ch = curl_init();
    $headers = [
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        "Content-Type: application/json",
        "Authorization: Bearer " . $accessToken
    ];

    $url = $NH_API_URL . 'v1/swd/' . $id;
    if (!is_null($type)) $url .= '/' . $type;
    $url .= '?user-id=' . $userId;

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorNo = curl_errno($ch);

    if ($errorNo) {
        return false;
    }

    $result = curl_exec($ch);
    curl_close($ch);

    $result = safe_json_decode($result, true);
    $responseStatus = isset($result['status']) ? $result['status'] : false;

    if ($responseStatus == 'error') {
        return false;
    }

    return $result['result'];
}

function details()
{
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);
    if (!$priviledge) exit(json_encode(['success' => false, 'message' => 'Permission denied']));

    $id = post('id');
    $type = url::issetInGet('type') && !url::isEmptyInGet('type') ? url::getToAny('type') : null;;

    if (is_null($id)) exit(json_encode(['success' => false, 'message' => 'ID not found in request']));
    if (!is_numeric($id)) exit(json_encode(['success' => false, 'message' => 'Invalid request']));

    $detail = requestDetails($id, $type, $_SESSION['user']['userid']);
    if (!$detail) exit(json_encode(['success' => false, 'message' => 'Something went wrong, unable to fetch details']));

    $makeExecutableDetails = url::issetInGet('make_executable_details') && url::getToText('make_executable_details') === 'true' && isset($detail['package_type']) && $detail['package_type'] == 'distribute'  ? true : false;

    if ($makeExecutableDetails) {
        $packageDetailsTypeCheckArray = array_column_multi($detail['segments'], ['package_type'], true);
        if (is_array($packageDetailsTypeCheckArray) && safe_sizeof($packageDetailsTypeCheckArray) > 0) {
            $packageDetailsTypeCheckArray = array_column($packageDetailsTypeCheckArray, 'package_type');
            if (in_array('execute', $packageDetailsTypeCheckArray)) {
                exit(json_encode(['success' => false, 'message' => 'Execute for this package has been already configured']));
            }
        }
    }

    $packageData = array_column_multi($detail, ['id', 'name', 'platform', 'windows_type', 'package_type', 'positive', 'negative', 'special', 'log_file', 'default', 'delete_log_file', 'status_message_box', 'message_box_text', 'max_time_per_patch', 'process_to_kill', 'is_global']);
    $replacements = ['name' => 'package-name', 'package_type' => 'package-type', 'windows_type' => 'windows-type', 'process_to_kill' => 'process-to-kill', 'log_file' => 'log-file', 'max_time_per_patch' => 'max-time-per-patch', 'status_message_box' => 'status-message-box', 'message_box_text' => 'message-box-text', 'delete_log_file' => 'delete-log-file', 'is_global' => 'global'];
    $packageData = array_replace_keys($replacements, $packageData);

    $packageItemsData = array_column_multi($detail['segments'], ['package_type', 'id', 'session', 'api_mode', 'append_command', 'patch_dependency', 'resume_download', 'propagation', 'url_credentials', 'sleep_time', 'hash_check'], true);
    $replacements = ['id' => 'item-id[]', 'append_command' => 'append-command[]', 'session' => 'session[]', 'propagation' => 'propagation[]', 'api_mode' => 'api-mode[]', 'url_credentials' => 'url-credential[]', 'sleep_time' => 'sleep-time[]', 'hash_check' => 'hash-check[]', 'resume_download' => 'resume-download[]', 'patch_dependency' => 'patch-dependency[]'];
    $packageItemsData = array_replace_keys($replacements, $packageItemsData, true);

    $packagePathUrlData = array_column_multi($detail['segments'], ['path_url'], true);
    $packagePrecheckData = array_column_multi($detail['segments'], ['pre_check'], true);

    $totalSegments = safe_sizeof($detail['segments']);
    $platform = $detail['platform'];
    $packageType = $detail['package_type'];
    $typeCheck = [''];
    $radios = [];

    $windowsType = $detail['windows_type'];
    switch ($windowsType) {
        case '32':
            $typeCheck = ['32'];
            break;
        case '64':
            $typeCheck = ['64'];
            break;
        case 'both':
            $typeCheck = ['32', '64'];
            break;
    }

    $findTypeCheckData = function ($type, $array) {
        $return = $array[0];
        foreach ($array as $eachArray) {
            if (isset($eachArray['windows_type']) && $eachArray['windows_type'] == $type) {
                $return = $eachArray;
                break;
            }
        }
        return $return;
    };

    $resolvedExecutablePathInfo = function ($url, $distributionPath) {
        $pathUrlArray = pathinfo($url);
        $lastCharacter = substr($distributionPath, (strlen($distributionPath) - 1), 1);
        $hasTrailingSlash = $lastCharacter == '/' || $lastCharacter == strval('\\');
        $slash = $hasTrailingSlash ? '' : '/';
        return isset($pathUrlArray['basename']) ? $distributionPath . $slash . $pathUrlArray['basename'] : '';
    };

    $pathUrlReturnArray = $preCheckReturnArray = [];
    for ($i = 0; $i < $totalSegments; $i++) {
        $packagePathUrlDataI = $packagePathUrlData[$i]['path_url'];
        $packagePrecheckDataI = $packagePrecheckData[$i]['pre_check'];
        if (isset($packageItemsData[$i]['post-validation[]']) && $packageItemsData[$i]['post-validation[]'] == '#') $packageItemsData[$i]['post-validation[]'] = '';

        foreach ($typeCheck as $eT) {
            $pUrA = $pCA = [];
            $ka =  '-' . $eT . '[]';
            $eKa = '-' . $i . '-' . $eT;
            $pPuArray = $findTypeCheckData($eT, $packagePathUrlDataI);
            $pUtype = $makeExecutableDetails ? '3' : $pPuArray['type'];
            $pUrA['source-type' . $ka] = $pUtype;
            if ($packageType == 'distribute') $pUrA['distribution-path' . $ka] = isset($pPuArray['distribution_path']) ? $pPuArray['distribution_path'] : '';
            if ($packageType == 'execute') $pUrA['command-line' . $ka] = isset($pPuArray['command_line']) ? $pPuArray['command_line'] : '';
            $pUrA['post-validation' . $ka] = isset($pPuArray['post_validation']) ? $pPuArray['post_validation'] : '';

            if (is_numeric($pUtype)) {
                switch (intval($pUtype)) {
                    case 1:
                        $pUrA['upload-to' . $ka] = $pPuArray['source_type'];
                        $pUrA['uploaded-file-name' . $ka] = $pPuArray['uploaded_file'];
                        break;
                    case 2:
                        $pUrA['package-url' . $ka] = $pPuArray['file_url'];
                        break;
                    case 3:
                        if ($makeExecutableDetails) {
                            $pUl = ($pUtype == '1') ? $pPuArray['uploaded_file'] : (isset($pPuArray['file_url']) ? $pPuArray['file_url'] : '');
                            $pUrA['package-path' . $ka] = $resolvedExecutablePathInfo($pUl, $pPuArray['distribution_path']);
                        } else {
                            $pUrA['package-path' . $ka] = $pPuArray['file_path'];
                        }
                        break;
                }
            }

            $pCArray = $findTypeCheckData($eT, $packagePrecheckDataI);
            $pCmode = $pCArray['mode'];
            $pCA['precheck-type' . $ka] = $pCmode;

            if (is_numeric($pCmode)) {
                switch (intval($pCmode)) {
                    case 1:
                        $radios['file-precheck' . $eKa] = $pCArray['file_execute_if'];
                        $pCA['file-path' . $ka] = $pCArray['file_path'];
                        break;
                    case 2:
                        $pCA['software-name' . $ka] = $pCArray['software_name'];
                        $pCA['software-version' . $ka] = $pCArray['software_version'];
                        $pCA['knowledge-base' . $ka] = $pCArray['software_knowledge_base'];
                        $pCA['service-pack' . $ka] = $pCArray['software_service_pack'];

                        break;
                    case 3:
                        $radios['registry-precheck' . $eKa] = $pCArray['registry_execute_if'];
                        $pCA['root-key' . $ka] = $pCArray['registry_main_key'];
                        $pCA['sub-key' . $ka] = $pCArray['registry_sub_key'];
                        $pCA['sub-key-name' . $ka] = $pCArray['registry_name'];
                        $pCA['type' . $ka] = $pCArray['registry_type'];
                        $pCA['type-value' . $ka] = $pCArray['registry_value'];

                        break;
                }
            }

            if ((is_numeric($pCA['precheck-type' . $ka]) && intval($pCA['precheck-type' . $ka]) == 0)) $pCA['precheck-type' . $ka] = 3;
            $pathUrlReturnArray[$i][$eT] = $pUrA;
            $preCheckReturnArray[$i][$eT] = $pCA;
        }
    }

    if ($makeExecutableDetails) {
        $packageData['package-type'] = 'execute';
    } else if (url::issetInGet('type') && in_array(url::getToAny('type'), ['distribute', 'execute'])) {
        $packageData['package-type'] = url::getToAny('type');
    }

    $packageDetails = [
        'detail' => $packageData,
        'item' => $packageItemsData,
        'path' => $pathUrlReturnArray,
        'pre_check' => $preCheckReturnArray,
        'radios' => $radios
    ];

    if ($makeExecutableDetails) {
        $packageDetails['make_executable'] = true;
    }

    echo (json_encode(['success' => true, 'data' => $packageDetails]));
}

function requestConfiguration(int $id, $type = null, $userId = '')
{
    global $NH_API_URL;

    $accessToken = ApiHelper::getAccessToken();
    $ch = curl_init();
    $headers = [
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        "Content-Type: application/json",
        "Authorization: Bearer " . $accessToken
    ];

    $url = $NH_API_URL . 'v1/swd/configuration/' . $id;
    if (!is_null($type)) $url .= '/' . $type;
    $url .= '?user-id=' . $userId;

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorNo = curl_errno($ch);

    if ($errorNo) {
        return false;
    }

    $result = curl_exec($ch);
    curl_close($ch);

    $result = safe_json_decode($result, true);
    $responseStatus = isset($result['status']) ? $result['status'] : false;

    if ($responseStatus == 'error') {
        return false;
    }

    return $result['result'];
}

function getConfiguration()
{
    $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);
    if (!$priviledge) exit(json_encode(['success' => false, 'message' => 'Permission denied']));

    if (!url::issetInPost('id')) exit(json_encode(['success' => false, 'message' => 'request id not found']));

    $id = post('id');

    if (is_null($id)) exit(json_encode(['success' => false, 'message' => 'id should be a valid package id']));

    $type = url::issetInPost('type') && in_array(url::postToAny('type'), ['distribute', 'execute']) ? url::postToAny('type') : null;
    $configuration = requestConfiguration($id, $type, $_SESSION['user']['userid']);

    if (!$configuration) exit(json_encode(['success' => false, 'message' => 'configuration fetch error']));

    echo (json_encode(['success' => true, 'data' => $configuration]));
}

function requestPushConfiguration($packageId, $userId, $packageType = null)
{
    global $NH_API_URL;

    $accessToken = ApiHelper::getAccessToken();
    $ch = curl_init();
    $headers = [
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        "Content-Type: application/json",
        "Authorization: Bearer " . $accessToken
    ];

    $url = $NH_API_URL . 'v1/swd/configuration/push/' . $packageId;
    if (!is_null($packageType)) $url .= '/' . $packageType;
    $url .= '?user-id=' . $userId;

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

    $errorNo = curl_errno($ch);

    if ($errorNo) {
        return false;
    }

    $result = curl_exec($ch);
    curl_close($ch);

    $result = safe_json_decode($result, true);
    $responseStatus = isset($result['status']) ? $result['status'] : false;

    if ($responseStatus == 'error') {
        return false;
    }

    return $result['result'];
}

function requestJob($packageId, $packageType, $platform)
{
    global $base_url;

    $url = $base_url . 'communication/communication_ajax.php';
    $postFields = [
        'function' => 'AddRemoteJobsNew',
        'Dart' => $packageId,
        'Jobtype' => 'Software Distribution',
        'OS' => $platform,
        'GroupName' => 'undefined'
    ];

    if (!is_null($packageType)) $postFields['package-type'] = $packageType;
    $ch = curl_init();
    $url .= '?' . http_build_query($postFields);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
    ]);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

    $errorNo = curl_errno($ch);

    if ($errorNo) {
        return false;
    }

    $result = curl_exec($ch);
    curl_close($ch);
    $result = safe_json_decode($result, true);

    return true;
}

function pushConfiguration()
{
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        if (
            url::issetInPost('distribute') && url::isNumericInPost('distribute') && url::postToInt('distribute') == 1
            ||
            url::issetInPost('execute') && url::isNumericInPost('execute') && url::postToInt('execute') == 1
        ) {
            $priviledge = checkModulePrivilege('distributesoftwaredistribution', 2);
            if (!$priviledge) exit(json_encode(['success' => false, 'message' => 'Permission denied']));

            $packageId = url::postToAny('package-id');
            $packageType = null;
            $userId = $_SESSION['user']['userid'];
            $distribute = url::issetInPost('distribute') && url::isNumericInPost('distribute') && url::postToInt('distribute') == 1 ? true : false;
            $execute = url::issetInPost('execute') && url::isNumericInPost('execute') && url::postToInt('execute') == 1 ? true : false;

            if ($distribute && !$execute) {
                $packageType = 'distribute';
            } else if (!$distribute && $execute) {
                $packageType = 'execute';
            }

            $response = requestPushConfiguration($packageId, $userId, $packageType);

            if ($response) {
                $details = requestDetails($packageId, null, $userId);
                if ($details) {
                    $platform = $details['platform'];
                    requestJob($packageId, $packageType, $platform);
                    exit(json_encode(['success' => true, 'message' => 'Successfully pushed configuration']));
                }
            }
        }
    }

    echo (json_encode(['success' => false, 'message' => 'Something went wrong']));
}

function getCdnCredentialsByUserId(int $userId)
{
    $pdo = NanoDB::connect();
    $ob = $pdo->prepare("SELECT * FROM software_distribution.cdn_credentials WHERE user_id=?");
    $ob->execute([$userId]);

    return $ob->fetch(PDO::FETCH_ASSOC);
}

function createCdnCredential($userId, $url, $accessKey, $secretKey, $bucketName, $cdnRegion)
{
    $sql = 'INSERT INTO `software_distribution`.`cdn_credentials` (`user_id`, `url`, `access_key`, `secret_key`, `bucket_name`, `cdn_region`) VALUES (?, ?, ?, ?, ?, ?)';
    $pdo = NanoDB::connect();
    $ob = $pdo->prepare($sql);
    $ob->execute([$userId, $url, $accessKey, $secretKey, $bucketName, $cdnRegion]);

    return $pdo->lastInsertId();
}

function updateCdnCredential($userId, $url, $accessKey, $secretKey, $bucketName, $cdnRegion)
{
    $sql = 'UPDATE `software_distribution`.`cdn_credentials` SET `url`=?, `access_key`=?, `secret_key`=?, `bucket_name`=?, `cdn_region`=? WHERE `user_id`=?';
    $pdo = NanoDB::connect();
    $ob = $pdo->prepare($sql);
    $ob->execute([$url, $accessKey, $secretKey, $bucketName, $cdnRegion, $userId]);

    return true;
}


function saveCdnCredentials()
{
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);
        if (!$priviledge) exit(json_encode(['success' => false, 'message' => 'Permission denied']));

        $userId = $_SESSION['user']['userid'];
        $url = post('cdn-url');
        $accessKey = post('cdn-ak');
        $secretKey = post('cdn-sk');
        $bucketName = post('cdn-bucket-name');
        $cdnRegion = post('cdn-region');

        if (!is_null($url) && !is_null($accessKey)  && !is_null($secretKey)  && !is_null($bucketName)  && !is_null($cdnRegion)) {
            $isExists = getCdnCredentialsByUserId($userId);

            if (!$isExists) {
                createCdnCredential($userId, $url, $accessKey, $secretKey, $bucketName, $cdnRegion);
            } else {
                updateCdnCredential($userId, $url, $accessKey, $secretKey, $bucketName, $cdnRegion);
            }

            if (isset($_SESSION['software_distribution']['upload_configurations']['cdn'])) unset($_SESSION['software_distribution']['upload_configurations']['cdn']);

            exit(json_encode(['success' => true, 'message' => 'Successfully saved cdn credentials']));
        }
    }

    echo (json_encode(['success' => false, 'message' => 'Failed to update cdn credentials']));
}

function getFtpCredentialsByUserId(int $userId)
{
    $pdo = NanoDB::connect();
    $ob = $pdo->prepare("SELECT * FROM software_distribution.ftp_credentials WHERE user_id=?");
    $ob->execute([$userId]);

    return $ob->fetch(PDO::FETCH_ASSOC);
}

function createFtpCredential($userId, $url, $username, $password)
{
    $sql = 'INSERT INTO `software_distribution`.`ftp_credentials` (`user_id`, `url`, `username`, `password`) VALUES (?, ?, ?, ?)';
    $pdo = NanoDB::connect();
    $ob = $pdo->prepare($sql);
    $ob->execute([$userId, $url, $username, $password]);

    return $pdo->lastInsertId();
}

function updateFtpCredential($userId, $url, $username, $password)
{
    $sql = 'UPDATE `software_distribution`.`ftp_credentials` SET  `url`=?, `username`=?, `password`=? WHERE `user_id`=?';
    $pdo = NanoDB::connect();
    $ob = $pdo->prepare($sql);
    $ob->execute([$url, $username, $password, $userId]);

    return true;
}


function saveFtpCredentials()
{
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);
        if (!$priviledge) exit(json_encode(['success' => false, 'message' => 'Permission denied']));

        $userId = $_SESSION['user']['userid'];
        $url = post('ftp-url');
        $username = post('ftp-username');
        $password = post('ftp-password');

        if (!is_null($url)) {
            $isExists = getFtpCredentialsByUserId($userId);

            if (!$isExists) {
                createFtpCredential($userId, $url, $username, $password);
            } else {
                updateFtpCredential($userId, $url, $username, $password);
            }

            if (isset($_SESSION['software_distribution']['upload_configurations']['ftp'])) unset($_SESSION['software_distribution']['upload_configurations']['ftp']);

            exit(json_encode(['success' => true, 'message' => 'Successfully saved ftp credentials']));
        }
    }

    echo (json_encode(['success' => false, 'message' => 'Failed to update ftp credentials']));
}


function fetchCredentials()
{
    if (url::issetInGet('type') && in_array(url::getToAny('type'), ['cdn', 'ftp'])) {
        $priviledge = checkModulePrivilege('configuresoftwaredistribution', 2);
        if (!$priviledge) exit(json_encode(['success' => false, 'message' => 'Permission denied']));

        $userId = $_SESSION['user']['userid'];

        if (url::getToText('type') === 'cdn') {
            $configuration = getCdnCredentialsByUserId($userId);
            exit(json_encode(['success' => true, 'data' => $configuration]));
        } else if (url::getToText('type') === 'ftp') {
            $configuration = getFtpCredentialsByUserId($userId);
            exit(json_encode(['success' => true, 'data' => $configuration]));
        }
    }

    exit(json_encode(['success' => false, 'message' => 'Failed to fetch credentials']));
}
