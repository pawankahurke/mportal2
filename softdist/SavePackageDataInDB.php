<?php

require_once '../include/common_functions.php';
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
require_once '../lib/l-db.php';
require_once '../lib/l-sql.php';
require_once '../lib/l-gsql.php';
require_once '../lib/l-rcmd.php';
require_once '../lib/l-swd.php';
require_once '../lib/passdata.php';

if (
    strcasecmp(isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '', 'application/json') != 0 &&
    strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0
) {
    throw new Exception('Content type must be application/json and method POST');
}

csrf_check_custom();

$input = trim(file_get_contents("php://input"));
$_REQUEST = safe_json_decode($input, true);

$priviledge = checkModulePrivilege('addsoftwaredistribution', 2);

if (!$priviledge) {
    echo 'Permission denied';
    logs::log('==================', ['Permission denied']);
    exit();
}

logs::log('==================', [$_REQUEST]);


$UserRestrict = $_SESSION["user"]["restirct"];
$UserIsAdmin = $_SESSION["user"]["isadmin"];
$username = $_SESSION["user"]["username"];
$_SESSION['windowtype'] = 'Manage';
$_SESSION['currentwindow'] = 'Sofware Distribution';
$user = $_SESSION['user']['adminEmail'];
$now = date("U");
$siteList = $_SESSION["user"]["site_list"];
$package = $GLOBALS['PREFIX'] . 'softinst.Packages';
$selectType = url::requestToText('selectType1');
$editid = url::requestToText('id1');
$androidIcon = "";
$andPreCheckCond = null;
$downloadType = null;
$dbo = pdo_connect();

$pdo = $dbo->prepare('select * from ' . $GLOBALS['PREFIX'] . 'softinst.PackagesFtp where user=?');
$pdo->execute([$user]);
$storageData =  $pdo->fetch(PDO::FETCH_ASSOC);

// if(!$storageData){
//     exit('FTP/CDN configuration data not found');
// }

// if(isset($_POST['uploads']) && is_numeric($_POST['uploads']) && intval($_POST['uploads']) == 2 && (is_null($storageData['cdnUrl']) || empty($storageData['cdnUrl']))){
//     exit('CDN Configuration not found');
// }

if ($selectType == "edit") {

    if (isset($_GET['check']) && $_GET['check'] == 'true') {
        if ((!empty($_POST['packName1']) && !empty($_POST['version1']) && !empty($_POST['id1']))) {
            $pdo = $dbo->prepare('select * from ' . $GLOBALS['PREFIX'] . 'softinst.Packages where packageName=? and packageDesc=? and version=? and id<>?');
            $pdo->execute([$_POST['packName1'], $_POST['packDesc1'], $_POST['version1'], $_POST['id1']]);
            $isAlreadyExists =  $pdo->fetch(PDO::FETCH_ASSOC);
            if ($isAlreadyExists) {
                exit(json_encode(['success' => false, 'message' => 'Package already exists']));
            } else {
                exit(json_encode(['success' => true, 'message' => 'success']));
            }
        } else {
            exit(json_encode(['success' => false, 'message' => 'Package name,description and version should not be empty']));
        }
    }

    $platform = url::requestToText('platform1');
    $type = url::requestToText('types1');
    $stype = url::requestToText('stype1');
    $protocol = url::requestToText('protocol1');
    $packDesc = url::requestToText('packDesc1');
    $path = url::requestToText('path1');
    $macpath = url::requestToText('path1');
    $fileName = url::requestToText('filename1');
    $macfileName = url::requestToText('filename1');
    $version = url::requestToText('version1');
    $siteName = strip_tags(url::requestToText('siteArray1'));
    $actionDate = url::requestToText('actionDate1');
    $notify = url::requestToText('notify1');
    $uniAction = url::requestToText('uniAction1');
    $access = url::requestToText('access1');
    $uname = url::requestToText('username1');
    $password = url::requestToText('password1');
    $domain = url::requestToText('domain1');
    $global = url::requestToText('global1');
    $distribute = url::requestToText('distribute1');
    $dpath = strip_tags(url::requestToText('dPath1'));
    $upStat = url::requestToText('upStatus1');
    $appId = url::requestToText('appId1');
    $manifestType = url::requestToText('manifesttypes1');
    $manifestName = url::requestToText('manifestname1');
    $preinstall = url::issetInRequest('preinstall1') ? url::requestToText('preinstall1') : 'NA';
    $oninstall = url::issetInRequest('oninstall1') ? url::requestToText('oninstall1') : 'NA';
    $posKey = strip_tags(url::requestToText('posKey1'));
    $packageExpiry = strip_tags(url::requestToText('packExpiry1'));
    $policyEnforce = strip_tags(url::requestToText('policyEnforce1'));
    $androidPreCheck = strip_tags(url::requestToText('andPreCheck1'));
    $androidPostCheck = strip_tags(url::requestToText('andPostCheck1'));
    $maxTime = strip_tags(url::requestToText('maxTime1'));
    $peerdistribution = url::requestToText('peerDistribution1');

    if (!$peerdistribution || $peerdistribution == '') {
        $peerdistribution = null;
    }

    $pExecPreCheckVal = 1;
    $pRegName = null;
    $pType = null;
    $pValue = null;

    if ($androidPostCheck == 1) {
        if (url::requestToText('andPPackName1') == "" || strip_tags(url::requestToText('andPVersionCode1')) == "") {
            $andPostCheckCond = "";
            $androidPostCheck = "";
        } else {
            $andPostCheckCond = strip_tags(url::requestToText('andPPackName1')) . "#" . strip_tags(url::requestToText('andPVersionCode1'));
        }
    } else {
        $andPostCheckCond = "";
    }

    $andDestPath = "";

    if ($androidPreCheck == 0) {
        $andPreCheckPath = strip_tags(url::requestToText('preCheckPath1'));
        $andDestPath = strip_tags(url::requestToText('downloadPath1'));
    } else if ($androidPreCheck == 1) {
        $andPreCheckPath = "";
        $andPreCheckCond = strip_tags(url::requestToText('andPackName1')) . "#" . strip_tags(url::requestToText('andVersionCode1'));
    } else if ($androidPreCheck == 2) {
        $andPreCheckPath = "";
        $andPreCheckCond = strip_tags(url::requestToText('apkPath1')) . "#" . strip_tags(url::requestToText('apkSize1'));
    }

    if ($macpath) {
        $macpathExp = explode("/", $macpath);
        $macpathExpCount = safe_count($macpathExp);
        $macpathlastval = $macpathExp[$macpathExpCount - 1];
        if ($macpathlastval != "") {
            $macpath = $macpath . "/";
        } else {
            $macpath = $macpath;
        }
    }

    $installType = url::requestToText('installType1');
    $preDownloadMsg = url::requestToText('preDownloadMsg1');
    $preDownloadPosMsg = url::requestToText('preDownloadPosMsg1');
    $preDownloadNegMsg = url::requestToText('preDownloadNegMsg1');
    $postDownloadMsg = url::requestToText('postDownloadMsg1');
    $postDownloadPosMsg = url::requestToText('postDownloadPosMsg1');
    $postDownloadNegMsg = url::requestToText('postDownloadNegMsg1');
    $installMsg = url::requestToText('installMsg1');
    $installMsgBut = url::requestToText('installMsgBut1');
    $installAction = url::requestToText('installAction1');
    $installFinishMsg = url::requestToText('installFinishMsg1');
    $installPopupMsg = url::requestToText('installPopupMsg1');
    $frequencySet = url::requestToText('frequencySet1');
    $intervalSet = url::requestToText('intervalSet1');
    $policyEnforceAction = url::requestToText('policyEnforceAction1');
    $enfMessage = url::requestToText('enfMessage1');
    $distributionType = url::requestToText('distType1');
    $andSourcePath = url::requestToText('sourcePath1');
    $andDestinationPath = url::requestToText('destinationPath1');
    $messageText = url::requestToText('title1');

    switch ($installType) {
        case "0":
            $preInstallMsg = "";
            $postDownloadMsg = "";
            $finalInstallMsg = "";
            $frequencySettings = "";
            break;
        case "3":
            $preInstallMsg = "," . $preDownloadMsg . "," . $preDownloadPosMsg . "," . $preDownloadNegMsg;
            $postDownloadMsg = "," . $postDownloadMsg . "," . $postDownloadPosMsg . "," . $postDownloadNegMsg;
            if ($installAction == "1") {
                $instalFinOrPop = $installFinishMsg;
            } else if ($installAction == "2") {
                $instalFinOrPop = $installPopupMsg;
            }
            $finalInstallMsg = "," . $installMsg . "," . $installAction . "," . $instalFinOrPop;
            $frequencySettings = $frequencySet . "," . $intervalSet . "," . $policyEnforceAction . "," . $enfMessage;
            break;
        case "5":
            $preInstallMsg = "," . $preDownloadMsg . "," . $preDownloadPosMsg . "," . $preDownloadNegMsg;
            $postDownloadMsg = "," . $postDownloadMsg . "," . $postDownloadPosMsg . "," . $postDownloadNegMsg;
            if ($installAction == "1") {
                $instalFinOrPop = $installFinishMsg;
            } else if ($installAction == "2") {
                $instalFinOrPop = $installPopupMsg;
            }
            $finalInstallMsg = "," . $installMsg . "," . $installAction . "," . $instalFinOrPop;
            $frequencySettings = $frequencySet . "," . $intervalSet . "," . $policyEnforceAction . "," . $enfMessage;
            break;
        default:
            $preInstallMsg = "";
            $postDownloadMsg = "";
            $finalInstallMsg = "";
            $frequencySettings = "";
            break;
    }


    if ($platform == 'linux') {
        $packNameOri = url::requestToText('packName1');
        $packNameOriExp = explode('_', $packNameOri);
        $packName = $packNameOriExp[0];
    } else {
        $packName = url::requestToText('packName1');
    }

    if (isset($_GET['filebrowse1'])) {
        $fileBrowse = strip_tags($_GET['filebrowse1']);
    } else {
        $fileBrowse = url::requestToText('filebrowse1');
    }

    $dTime = url::requestToText('dTime1');
    $validPath = url::requestToText('dvPath1');
    $mandatory = url::requestToText('mandatory1');

    if ($mandatory != '1') {
        $mandatory = '0';
    }

    $sourceToUpload = url::requestToText('uploads');
    $cdnUrl = isset($storageData['cdnUrl']) ? $storageData['cdnUrl'] : '';
    $ftpUrl = url::requestToText('sFtpUrl1');
    $cdnBucket = url::requestToText('AWSBUCKET1');
    $preinst = url::requestToText('preinstcheck1');

    if ($stype == '2' || $stype == '5') {
        if ($selectType == "edit") {
            if ($sourceToUpload == "") {
                $sourcesql = "SELECT protocol FROM " . $GLOBALS['PREFIX'] . "softinst.Packages WHERE id=?";
                $pdo = $dbo->prepare($sourcesql);
                $pdo->execute([$editid]);
                $sourceres = $pdo->fetch(PDO::FETCH_ASSOC);
                $sourceToUpload = $sourceres['protocol'];
            } else {
                $sourceToUpload = $sourceToUpload;
            }
        } else {
            $sourceToUpload = $sourceToUpload;
        }
        $protocol = $sourceToUpload;
        if ($sourceToUpload == 2) {
            $dom = $cdnUrl;
            $path = $cdnUrl . $fileName;
            $androidIcon = $cdnUrl . url::requestToText('androidI1');
        } else if ($sourceToUpload == 1) {
            $dom = $ftpUrl;
            $path = $ftpUrl . $fileName;
            $androidIcon = $ftpUrl . url::requestToText('androidI1');
        } else {
            $dom = $ftpUrl;
            $path = $path;
            $androidIcon = url::requestToText('androidI1');
        }
    } else if ($stype === '3') {
        if ($platform == 'windows') {
            $path = $packName;
        }
    }

    if ($peerdistribution == "1" || $peerdistribution == 1) {
        $peer = ",NA,NA,1";
    } else if ($peerdistribution == "0" || $peerdistribution == 0) {
        $peer = ",NA,NA,0";
    } else {
        $peer = "";
    }

    if ($platform == 'windows') {

        if ($distribute == '1') {

            if ($preinst != '') {

                $pfilePath = strip_tags(url::requestToText('pfilePath1'));
                $pSoftName = url::requestToText('pSoftName1');
                $pSoftVer = url::requestToText('pSoftVer1');
                $pKb = url::requestToText('pKb1');
                $pServicePack = url::requestToText('pServicePack1');
                $rootKey = url::requestToText('rootKey1');
                $subKey = strip_tags(url::requestToText('subKey1'));
                $pExecPreCheckVal = url::requestToText('pExecPreCheckVal2');
                $pRegName = strip_tags(url::requestToText('pRegName1'));
                $pType = strip_tags(url::requestToText('pType1'));
                $pValue = strip_tags(url::requestToText('pValue1'));

                if ($pExecPreCheckVal == 0) {
                    if ($preinst == '1') {
                        $notVal = "";
                    } else {
                        $notVal = "!";
                    }
                } else {
                    $notVal = "";
                }

                $pKb = str_replace(",", "&&", $pKb);

                if ($pSoftVer == '') {
                    $pSoftVer_m = 'NA';
                } else {
                    $pSoftVer_m = $pSoftVer;
                }

                if ($pKb == '') {
                    $pKb_m = 'NA';
                } else {
                    $pKb_m = $pKb;
                }

                if ($pServicePack == '') {
                    $pServicePack_m = 'NA';
                } else {
                    $pServicePack_m = $pServicePack;
                }

                if ($preinst == 0) {
                    $preinstallCheck = $notVal . "0,$pfilePath";
                } else if ($preinst == 1) {
                    $preinstallCheck = $notVal . "1,";

                    if ($pSoftVer_m == 'NA' && $pKb_m == 'NA' && $pServicePack_m == 'NA') {
                        $preinstallCheck .= "$pSoftName";
                    } else if ($pKb_m == 'NA' && $pServicePack_m == 'NA') {
                        if ($pSoftName == '') {
                            $pSoftName = 'NA';
                        }

                        $preinstallCheck .= "$pSoftName#$pSoftVer_m";
                    } else if ($pServicePack_m == 'NA') {
                        if ($pSoftName == '') {
                            $pSoftName = 'NA';
                        }

                        $preinstallCheck .= "$pSoftName#$pSoftVer_m#$pKb_m";
                    } else {
                        if ($pSoftName == '') {
                            $pSoftName = 'NA';
                        }

                        $preinstallCheck .= "$pSoftName#$pSoftVer_m#$pKb_m#$pServicePack_m";
                    }
                } else if ($preinst == 2) {
                    $preinstallCheck = $notVal . "2,$rootKey#$subKey#$pRegName#$pType#$pValue";

                    if ($rootKey == '') {
                        $preinstallCheck = "2,NA#$subKey#$pRegName#$pType#$pValue";
                    }

                    if ($subKey == '') {
                        $preinstallCheck = "2,$rootKey#$pRegName#$pType#$pValue";
                    }
                } else if ($preinst == 3) {
                    $preinstallCheck = $notVal . "1,$pSoftName";
                } else {
                    $preinstallCheck = "NA,NA";
                }
                $confStr = "1,NT,$path,1,1,4,$dpath,$preinstallCheck,0#$dpath$fileName$peer";
            } else {
                $confStr = "1,NT,$path,1,1,4,$dpath,NA,NA,0#$dpath$fileName$peer";
            }
        }
    } else if ($platform == 'mac' && $stype == '2') {
        $pfilePath = $pSoftName = $pSoftVer = $pExecPreCheckVal = $preinstallCheck = '';
        if ($preinst != '') {
            if ($preinst == 0) {
                $pfilePath = url::issetInRequest('pfilePath1') ? strip_tags(url::requestToText('pfilePath1')) : '';
            } else if ($preinst == 1) {
                $pSoftName = url::issetInRequest('pSoftName1') ? url::requestToText('pSoftName1') : '';
                $pSoftVer = url::issetInRequest('pSoftVer1') ? url::requestToText('pSoftVer1') : '';
            }
            if ($pSoftVer == '') {
                $pSoftVer_m = 'NA';
            } else {
                $pSoftVer_m = $pSoftVer;
            }
            if ($preinst == 0) {
                $preinstallCheck = ",0,$pfilePath";
            } else if ($preinst == 1) {
                $preinstallCheck = ",1,";
                if ($pSoftVer_m == 'NA') {
                    $preinstallCheck .= "$pSoftName";
                } else {
                    if ($pSoftName == '') {
                        $pSoftName = 'NA';
                    }
                    $preinstallCheck .= "$pSoftName#$pSoftVer_m";
                }
            }
        }

        if ($stype == '3') {
            $confstr = "1,MAC,$path,0,0,4,$macpath,1,$packName";
        } else if ($stype == '2') {
            if ($preinst == '') {
                $confstr = "1,MAC,$path,0,0,4,$macpath";
            } else {
                $confstr = "1,MAC,$path,0,0,4,$macpath$preinstallCheck";
            }
        }
    } else if ($platform == 'mac' && $stype == '3') {
        $confstr = $macpath . $fileName;
    } else if ($platform == 'ios') {
        $confstr = "0,$path";
    } else if ($platform == 'linux' && $stype == '2') {
        $pfilePath = $pSoftName = $pSoftVer = $pExecPreCheckVal = $preinstallCheck = '';
        if ($preinst != '') {
            if ($preinst == 0) {
                $pfilePath = url::issetInRequest('pfilePath1') ? strip_tags(url::requestToText('pfilePath1')) : '';
            } else if ($preinst == 1) {
                $pSoftName = url::issetInRequest('pSoftName1') ? url::requestToText('pSoftName1') : '';
                $pSoftVer = url::issetInRequest('pSoftVer1') ? url::requestToText('pSoftVer1') : '';
            }
            if ($pSoftVer == '') {
                $pSoftVer_m = 'NA';
            } else {
                $pSoftVer_m = $pSoftVer;
            }
            if ($preinst == 0) {
                $preinstallCheck = ",0,$pfilePath";
            } else if ($preinst == 1) {
                $preinstallCheck = ",1,";
                if ($pSoftVer_m == 'NA') {
                    $preinstallCheck .= "$pSoftName";
                } else {
                    if ($pSoftName == '') {
                        $pSoftName = 'NA';
                    }
                    $preinstallCheck .= "$pSoftName#$pSoftVer_m";
                }
            }
        }

        if ($stype == '3') {
            $confstr = "1,LINUX,$path,0,0,4,$macpath,1,$packName";
        } else if ($stype == '2') {
            if ($preinst == '') {
                $confstr = "1,LINUX,$path,0,0,4,$macpath";
            } else {
                $confstr = "1,LINUX,$path,0,0,4,$macpath$preinstallCheck";
            }
        }
    } else if ($platform == 'linux' && $stype == '3') {
        $confstr = $macpath . $fileName;
    } else {
        if ($stype == '4') {
            $confstr = $mandatory . "," . $packName;
        } else {
            $confstr = $mandatory . "," . $path;
        }
    }

    if ($distributionType == "3") {
        $stype = "2";
        $packDesc = "NA";
        $version = "NA";
        $policyEnforce = 0;
        $downloadType = 1;
    } else if ($distributionType == "1") {
        $policyEnforce = 0;
        $downloadType = 1;
    } else if ($distributionType == "2") {
        $policyEnforce = $policyEnforce;
        $downloadType = 1;
    }

    $upStatus = url::requestToAny('uStatus1');

    if ($platform == 'ios') {

        if ($stype == '5' || $stype == '2') {

            if ($sourceToUpload == 2) {
                $manifestNamePath = "itms-services://?action=download-manifest&url=" . $ftpUrl . "" . $manifestName . ".plist";
                $manifestCreation = $ftpUrl . "" . $manifestName . ".plist";
                $myManifestFile = fopen("../swd/$manifestName.plist", "w");

                if (strpos($path, 'png') !== false) {
                    $temp = $path;
                    $path = $androidIcon;
                    $androidIcon = $temp;
                }

                if ($stype == '5') {
                    $SWDPOLICY = '';
                    $plistContent = '<?xml version="1.0" encoding="UTF-8"?>
                        <!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
                        <plist version="1.0">';
                } else {
                    $SWDPOLICY = 'SWDPOLICY#';
                    $plistContent = '';
                }

                $plistContent .= $SWDPOLICY . '<dict>
                                <key>items</key>
                                <array>
                                        <dict>
                                                <key>assets</key>
                                                <array>
                                                        <dict>
                                                                <key>kind</key>
                                                                <string>software-package</string>
                                                                <key>url</key>
                                                                <string>' . $path . '</string>
                                                        </dict>
                                                        <dict>
                                                                <key>kind</key>
                                                                <string>display-image</string>
                                                                <key>url</key>
                                                                <string>' . $androidIcon . '</string>
                                                        </dict>
                                                        <dict>
                                                                <key>kind</key>
                                                                <string>full-size-image</string>
                                                                <key>url</key>
                                                                <string>' . $androidIcon . '</string>
                                                        </dict>
                                                </array>
                                                <key>metadata</key>
                                                <dict>
                                                        <key>bundle-identifier</key>
                                                        <string>' . $packName . '</string>
                                                        <key>bundle-version</key>
                                                        <string>' . $version . '</string>
                                                        <key>kind</key>
                                                        <string>software</string>
                                                        <key>title</key>
                                                        <string>' . $fileName . '</string>
                                                </dict>
                                        </dict>
                                </array>
                        </dict>';
                if ($stype == '5') {
                    $plistContent .= '</plist>';
                }

                fwrite($myManifestFile, $plistContent);
                fclose($myManifestFile);
            } else if ($sourceToUpload == 1) {
                $manifestNamePath = "itms-services://?action=download-manifest&url=" . $ftpUrl . "" . $manifestName . ".plist";
                $manifestCreation = $ftpUrl . "" . $manifestName . ".plist";
                $myManifestFile = fopen("../swd/$manifestName.plist", "w");

                if (strpos($path, 'png') !== false) {
                    $temp = $path;
                    $path = $androidIcon;
                    $androidIcon = $temp;
                }

                if ($stype == '5') {
                    $SWDPOLICY = '';
                    $plistContent = '<?xml version="1.0" encoding="UTF-8"?>
                        <!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
                        <plist version="1.0">';
                } else {
                    $SWDPOLICY = 'SWDPOLICY#';
                    $plistContent = '';
                }
                $plistContent .= $SWDPOLICY . '<dict>
                                <key>items</key>
                                <array>
                                        <dict>
                                                <key>assets</key>
                                                <array>
                                                        <dict>
                                                                <key>kind</key>
                                                                <string>software-package</string>
                                                                <key>url</key>
                                                                <string>' . $path . '</string>
                                                        </dict>
                                                        <dict>
                                                                <key>kind</key>
                                                                <string>display-image</string>
                                                                <key>url</key>
                                                                <string>' . $androidIcon . '</string>
                                                        </dict>
                                                        <dict>
                                                                <key>kind</key>
                                                                <string>full-size-image</string>
                                                                <key>url</key>
                                                                <string>' . $androidIcon . '</string>
                                                        </dict>
                                                </array>
                                                <key>metadata</key>
                                                <dict>
                                                        <key>bundle-identifier</key>
                                                        <string>' . $packName . '</string>
                                                        <key>bundle-version</key>
                                                        <string>' . $version . '</string>
                                                        <key>kind</key>
                                                        <string>software</string>
                                                        <key>title</key>
                                                        <string>' . $fileName . '</string>
                                                </dict>
                                        </dict>
                                </array>
                        </dict>';
                if ($stype == '5') {
                    $plistContent .= '</plist>';
                }
                fwrite($myManifestFile, $plistContent);
                fclose($myManifestFile);
            } else {
                $manifestNamePath = "";
            }

            if ($upStat == "1" || $upStat == 1) {
                $aStatus = 'Uploaded';
            } else {
                $aStatus = 'Initiated';
            }

            $sql = "UPDATE $package SET platform=?,type=?, sourceType=?,protocol=?,packageDesc=?, path=?, fileName=?,packageName=?, androidIcon=?, version=?,androiddate=?,androidnotify=?,androidUninstall=?, access=?, userName=?,password=?, domain=?, status=?, fileSize=?, global=?, owner=?, distrubute = '0', distributionPath=?, user=?, lastModified=?,configDetail=?,distributionConfigDetail=?,isConfigured = '1' WHERE id=?";
            $updateBindings = array($platform, $type, $stype, $protocol, $packDesc, $manifestNamePath, $fileName, $packName, $androidIcon, $version, $actionDate, $notify, $uniAction, $access, $uname, $password, $domain, $aStatus, $filesize, $global, $user, $manifestNamePath, $username, $now, $confstr, $confstr, $editid);
        } else if ($stype == '6') {
            $sql = "UPDATE $package SET appId=?,platform=?,type=?, sourceType=?,protocol=?,packageDesc=' N.A. ', path=?, fileName=?,packageName=?, version=?, access=?, userName=?,password=?, domain=?, status='Uploaded', fileSize=?, global=?, owner=?, distrubute='0', distributionPath=?, user=?, lastModified=?,configDetail=?,distributionConfigDetail=?,isConfigured = '1' WHERE id=?";
            $updateBindings = array($appId, $platform, $type, $stype, $protocol, $path, $fileName, $packName, $version, $access, $uname, $password, $domain, $filesize, $global, $user, $dpath, $username, $now, $confstr, $confstr, $editid);
        } else {
            $sql = "UPDATE $package SET platform=?,type=?, sourceType=?,protocol=?,packageDesc=?, path=?, fileName=?,packageName=?, version=?, access=?, userName=?,password=?, domain=?, status='--', fileSize=?, global=?, owner=?, distrubute='0', distributionPath=?, user=?, lastModified=?,configDetail=?,distributionConfigDetail=?,isConfigured = '1' WHERE id=?";
            $updateBindings = array($platform, $type, $stype, $protocol, $packDesc, $path, $fileName, $packName, $version, $access, $uname, $password, $domain, $filesize, $global, $user, $dpath, $username, $now, $confstr, $confstr, $editid);
        }
    } else if ($platform == 'android') {

        $mdistribute = !isset($distribute) || empty($distribute) ? '0' : $distribute;
        $muniAction = (!isset($uniAction) || empty($uniAction)) ? '' : ", androidUninstall=?";

        if ($stype == '2' || $stype == '5') {

            if ($upStat == "1" || $upStat == 1) {
                $aStatus = 'Uploaded';
            } else {
                $aStatus = 'Initiated';
            }

            $sql = "UPDATE $package SET platform=?,type=?, sourceType=?,protocol=?,packageDesc=?, path=?, fileName=?,packageName=?, androidIcon=?, androidSite=?, version=?,androiddate=?,androidnotify=? $muniAction , access=?, userName=?,password=?, domain=?, status=?, fileSize=?, global=?, owner=?, distrubute=?, distributionPath=?, user=?, lastModified=?,configDetail=?, preinstall=?, oninstall=? WHERE id=?";

            $updateBindings = array($platform, $type, $stype, $protocol, $packDesc, $path, $fileName, $packName, $androidIcon, $siteName, $version, $actionDate, $notify);
            if ((!isset($uniAction) || empty($uniAction))) $updateBindings[] = $uniAction;
            $updateBindingsOther = array($access, $uname, $password, $domain, $aStatus, $filesize, $global, $user, $mdistribute, $dpath, $username, $now, $confstr, $preinstall, $oninstall, $editid);
            $updateBindings = array_merge($updateBindings, $updateBindingsOther);
        } else if ($stype == '4') {
            $sql = "UPDATE $package SET platform=?,type=?, sourceType=?,protocol=?,packageDesc=?, path=?, fileName=?,packageName=?, version=?, access=?, userName=?,password=?, domain=?, status='Uploaded', fileSize=?, global=?, owner=?, distrubute=?, distributionPath=?, user=?, lastModified=?,configDetail=? WHERE id=?";
            $updateBindings =  array($platform, $type, $stype, $protocol, $packDesc, $path, $fileName, $packName, $version, $access, $uname, $password, $domain, $filesize, $global, $user, $mdistribute, $dpath, $username, $now, $confstr, $editid);
        } else {
            $sql = "UPDATE $package SET platform=?,type=?, sourceType=?,protocol=?,packageDesc=?, path=?, fileName=?,packageName=?, version=?, access=?, userName=?,password=?, domain=?, status='Uploaded', fileSize=?, global=?, owner=?, distrubute=?, distributionPath=?, user=?, lastModified=?,configDetail=? WHERE id=?";
            $updateBindings =  array($platform, $type, $stype, $protocol, $packDesc, $path, $fileName, $packName, $version, $access, $uname, $password, $domain, $filesize, $global, $user, $mdistribute, $dpath, $username, $now, $confstr, $editid);
        }
    } else if ($platform == 'mac' || $platform == 'linux') {

        if ($preinst == "") {
            $distributeMAC = "0";
        } else {
            $distributeMAC = "1";
        }

        if ($stype == '2') {

            if ($upStat == "1" || $upStat == 1) {
                $aStatus = 'Uploaded';
            } else {
                $aStatus = 'Initiated';
            }

            $sql = "UPDATE $package SET platform=?,type=?, sourceType=?,protocol=?,packageDesc=?, path=?, fileName=?,packageName=?, androidIcon=?, version=?, access=?, userName=?,password=?, domain=?, status=?, fileSize=?, global=?, owner=?, distrubute=?, distributionPath=?, user=?, lastModified=?,configDetail=?, ftpcdnURL=? WHERE id=?";
            $updateBindings = array($platform, $type, $stype, $protocol, $packDesc, $macpath, $fileName, $packName, $androidIcon, $version, $access, $uname, $password, $domain, $aStatus, $filesize, $global, $user, $distributeMAC, $dpath, $username, $now, $confstr, $dom, $editid);
        } else {
            $sql = "UPDATE $package SET platform=?,type=?, sourceType=?,protocol=?,packageDesc=?, path=?, fileName=?,packageName=?, version=?, access=?, userName=?,password=?, domain=?, status='Uploaded', fileSize=?, global=?, owner=?, distrubute=?, distributionPath=?, user=?, lastModified=?,configDetail=?, ftpcdnURL=? WHERE id=?";
            $updateBindings = array($platform, $type, $stype, $protocol, $packDesc, $macpath, $fileName, $packName, $version, $access, $uname, $password, $domain, $filesize, $global, $user, $distributeMAC, $dpath, $username, $now, $confstr, $dom, $editid);
        }
    } else {

        if ($upStat == "1" || $upStat == 1) {
            $upStatus = 'Uploaded';
        } else {
            $upStatus = 'Initiated';
        }

        $mdistribute = !isset($distribute) || empty($distribute) ? '0' : $distribute;
        $sql = "UPDATE $package SET platform=?,type=?, sourceType=?,protocol=?,packageDesc=?, path=?, fileName=?,packageName=?, version=?, access=?, userName=?,password=?, domain=?, status=?, fileSize=?, global=?, owner=?, distrubute=?, distributionPath=?, distributionTime=?,distributionVpath=?, user=?, lastModified=?, addConfigDetail=?,configDetail=? WHERE id=?";
        $updateBindings  = array($platform, $type, $stype, $protocol, $packDesc, $path, $fileName, $packName, $version, $access, $uname, $password, $domain, $upStatus, $filesize, $global, $user, $mdistribute, $dpath, $dTime, $validPath, $username, $now, $confStr, $confstr, $editid);
    }

    $pdo = $dbo->prepare($sql);
    $q = $pdo->execute($updateBindings);

    $configCheckSql = "SELECT count(id) as count FROM " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration WHERE packageId=?";
    $pdo = $dbo->prepare($configCheckSql);
    $pdo->execute([$editid]);
    $configCheckRes = $pdo->fetch(PDO::FETCH_ASSOC);
    $configStat = $configCheckRes['count'];

    if ($platform == 'windows' && $distribute == '1') {
        echo $editid . ',D';
        if ($preinst != '') {

            if ($configStat > 0) {
                $configSql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration SET dcheckPreInstall=?, dValidationFilePath=?, dsoftwareName=?, dsoftwareVersion=?, dknowledgeBase=?, dservicePack=?, dRootKey=?, dSubKey=?, pExecPreCheckVal=?, pRegName=?, pType=?, pValue=?";
                $bindings = array($preinst, $pfilePath, $pSoftName, $pSoftVer, $pKb, $pServicePack, $rootKey, $subKey, $pExecPreCheckVal, $pRegName, $pType, $pValue);

                if (isset($peerdistribution) && !empty($peerdistribution)) {
                    $configSql .= " , peerdistribution=?";
                    $bindings[] = $peerdistribution;
                }

                $configSql .= " WHERE packageId=?";
                $bindings[] = $editid;
            } else {

                $peerSqlA = '';
                $peerSqlB = '';

                if (isset($peerdistribution) && !empty($peerdistribution)) {
                    $peerSqlA = " , peerdistribution";
                    $peerSqlB = ", ?";
                }

                $configSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration (packageId, dcheckPreInstall, dValidationFilePath,dsoftwareName, dsoftwareVersion, dknowledgeBase, dservicePack, dRootKey, dSubKey $peerSqlA ,pExecPreCheckVal,pRegName,pType,pValue) VALUES(?, ?, ?, ?, ?, ?, ?, ?,? $peerSqlB,?,?,?,?)";
                $bindings = array($editid, $preinst, $pfilePath, $pSoftName, $pSoftVer, $pKb, $pServicePack, $rootKey, $subKey);
                if (isset($peerdistribution) && !empty($peerdistribution)) $bindings[] = $peerdistribution;
                $bindingsOther = array($pExecPreCheckVal, $pRegName, $pType, $pValue);
                $bindings = array_merge($bindings, $bindingsOther);
            }
        } else {
            if ($configStat > 0) {

                $configSql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration SET dcheckPreInstall=?, dValidationFilePath=?, dsoftwareName=?, dsoftwareVersion=?, dknowledgeBase=?, dservicePack=?, dRootKey=?, dSubKey=?";
                $bindings = array($preinst, $pfilePath, $pSoftName, $pSoftVer, $pKb, $pServicePack, $rootKey, $subKey);
                if (isset($peerdistribution) && !empty($peerdistribution)) {
                    $configSql .= " , peerdistribution=?";
                    $bindings[] = $peerdistribution;
                }

                $configSql .= " WHERE packageId=?";
                $bindings[] = $editid;
            }
        }

        $pdo = $dbo->prepare($configSql);
        $pdo->execute($bindings);
    } else if ($platform == 'mac' && $preinst != '') {
        if ($configStat > 0) {
            $configSql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration SET dcheckPreInstall=?, dValidationFilePath=?, dsoftwareName=?, dsoftwareVersion=? WHERE packageId=?";
            $bindings = array($preinst, $pfilePath, $pSoftName, $pSoftVer, $editid);
        } else {
            $configSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration (packageId, dcheckPreInstall, dValidationFilePath,dsoftwareName, dsoftwareVersion) VALUES (?, ?, ?, ?, ?)";
            $bindings = array($editid, $preinst, $pfilePath, $pSoftName, $pSoftVer);
        }

        $pdo = $dbo->prepare($configSql);
        $pdo->execute($bindings);
    } else {
        echo $editid . ',ND';
        if ($configStat > 0) {
            $configSql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration SET dcheckPreInstall=?, dValidationFilePath=?, dsoftwareName=?, dsoftwareVersion=?, dknowledgeBase=?, dservicePack=?, dRootKey=?, dSubKey=?, peerdistribution=? WHERE packageId=?";
            $bindings = array($preinst, $pfilePath, $pSoftName, $pSoftVer, $pKb, $pServicePack, $rootKey, $subKey, $peerdistribution, $editid);

            $pdo = $dbo->prepare($configSql);
            $pdo->execute($bindings);
        }
    }

    if ($platform == 'android' && $stype == '2') {

        if ($downloadType == "" || $downloadType == null) {
            $downloadType = 0;
        }

        if ($policyEnforce == "" || $policyEnforce == null) {
            $policyEnforce = 0;
        }

        if ($configStat > 0) {
            $possql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration SET posKeywords=?, packageExpiry=?, policyEnforce=?, androidPreCheck=?, androidPostCheck=?, downloadType=?, maxTime=?, preInstallMsg=?, postDownloadMsg=?, finalInstallMsg=?, frequencySettings=?,installType=?, andPreCheckCond=?, andSourcePath=?, andDestinationPath=?, distributionType=?, messageText=?, andPostCheckCond=?, andDestPath=?, andPreCheckPath=? where packageId=?";
            $bindings = array($posKey, $packageExpiry, $policyEnforce, $androidPreCheck, $androidPostCheck, $downloadType, $maxTime, $preInstallMsg, $postDownloadMsg, $finalInstallMsg, $frequencySettings, $installType, $andPreCheckCond, $andSourcePath, $andDestinationPath, $distributionType, $messageText, $andPostCheckCond, $andDestPath, $andPreCheckPath, $editid);
        } else {
            $possql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration (packageId,posKeywords,packageExpiry,policyEnforce,androidPreCheck, androidPostCheck,downloadType,maxTime,preInstallMsg,postDownloadMsg,finalInstallMsg,frequencySettings,installType,andPreCheckCond, andSourcePath,andDestinationPath,distributionType,messageText,andPostCheckCond,andDestPath,andPreCheckPath) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $bindings = array($editid, $posKey, $packageExpiry, $policyEnforce,  $androidPreCheck,  $androidPostCheck, $downloadType,  $maxTime,  $preInstallMsg,  $postDownloadMsg,  $finalInstallMsg,  $frequencySettings,  $installType,  $andPreCheckCond,  $andSourcePath,  $andDestinationPath,  $distributionType,   $messageText,  $andPostCheckCond,  $andDestPath,  $andPreCheckPath);
        }

        $pdo = $dbo->prepare($possql);
        $pdo->execute($bindings);
    }
} else {
    $configurationstatusType = url::requestToText('same32_64config');
    if ($configurationstatusType == 'no') {
        $Conig_32_64 = 'different';
        $fileName = url::requestToText('filename');
        $filename2 = url::requestToText('filename2');
    } else {
        $Conig_32_64 = 'same';
        $fileName = url::requestToText('filename');
        $filename2 = '';
    }

    $file_part1 = pathinfo($fileName);
    $ext1 = $file_part1['extension'];

    $file_part2 = pathinfo($filename2);
    $ext2 = $file_part2['extension'];

    if ($ext1 == 'php' || $ext1 == 'js' || $ext1 == 'html') {
        echo "Unable to upload file with the given extension type";
        exit();
    }

    if ($ext2 == 'php' || $ext2 == 'js' || $ext2 == 'html') {
        echo "Unable to upload file with the given extension type";
        exit();
    }

    $platform = url::requestToText('platform');
    $type = url::requestToText('types');
    $stype = url::requestToText('stype');
    $protocol = url::requestToText('protocol');
    $packDesc = url::requestToText('packDesc');
    $macpath = url::requestToText('path');
    $path = url::requestToText('path');
    $macfileName = url::requestToText('filename');
    $version = url::requestToText('version');

    $siteName = url::requestToJson('siteArray');
    $siteName = $siteName ? implode(",", safe_json_decode($siteName)) : null;

    $actionDate = url::requestToText('actionDate');
    $notify = url::requestToText('notify');
    $uniAction = url::requestToText('uniAction');
    $access = url::requestToText('access');
    $access = $access ? $access : null;
    $uname = url::requestToText('username');
    $password = url::requestToText('password');
    $domain = url::requestToText('domain');
    $global = url::requestToText('global');
    $distribute = url::requestToText('distribute');
    $dpath = url::requestToText('dPath');
    $upStat = url::requestToText('upStatus');
    $appId = url::requestToText('appId');
    $manifestType = url::requestToText('manifesttypes');
    $manifestName = url::requestToText('manifestname');
    $peerdistribution = url::requestToText('peerDistribution');
    if (!$peerdistribution || $peerdistribution == '') {
        $peerdistribution = null;
    }
    $preinstall = url::issetInRequest('preinstall') ? url::requestToText('preinstall') : 'NA';
    $oninstall = url::issetInRequest('oninstall') ? url::requestToText('oninstall') : 'NA';
    $posKey = url::requestToText('posKey');
    $packageExpiry = url::requestToText('packExpiry');
    $policyEnforce = url::requestToText('policyEnforce');
    $androidPreCheck = url::requestToText('andPreCheck');
    $androidPostCheck = url::requestToText('andPostCheck');
    $maxTime = url::requestToText('maxTime');
    $pExecPreCheckVal = 1;
    $pRegName = null;
    $pType = null;
    $pValue = null;

    if ($androidPostCheck == 1) {
        if (url::requestToText('andPPackName') == "" || url::requestToText('andPVersionCode') == "") {
            $andPostCheckCond = "";
            $androidPostCheck = "";
        } else {
            $andPostCheckCond = url::requestToText('andPPackName') . "#" . url::requestToText('andPVersionCode');
        }
    } else {
        $andPostCheckCond = "";
    }

    if ($macpath) {
        $macpathExp = explode("/", $macpath);
        $macpathExpCount = safe_count($macpathExp);
        $macpathlastval = $macpathExp[$macpathExpCount - 1];
        if ($macpathlastval != "") {
            $macpath = $macpath . "/";
        } else {
            $macpath = $macpath;
        }
    }

    $andDestPath = "";

    if ($androidPreCheck == 0) {
        $andPreCheckPath = url::requestToText('preCheckPath');
        $andDestPath = url::requestToText('downloadPath');
    } else if ($androidPreCheck == 1) {
        $andPreCheckPath = "";
        $andPreCheckCond = url::requestToText('andPackName') . "#" . url::requestToText('andVersionCode');
    } else if ($androidPreCheck == 2) {
        $andPreCheckPath = "";
        $andPreCheckCond = url::requestToText('apkPath') . "#" . url::requestToText('apkSize');
    }

    $installType = url::requestToText('installType');
    $preDownloadMsg = url::requestToText('preDownloadMsg');
    $preDownloadPosMsg = url::requestToText('preDownloadPosMsg');
    $preDownloadNegMsg = url::requestToText('preDownloadNegMsg');
    $postDownloadMsg = url::requestToText('postDownloadMsg');
    $postDownloadPosMsg = url::requestToText('postDownloadPosMsg');
    $postDownloadNegMsg = url::requestToText('postDownloadNegMsg');
    $installMsg = url::requestToText('installMsg');
    $installMsgBut = url::requestToText('installMsgBut');
    $installAction = url::requestToText('installAction');
    $installFinishMsg = url::requestToText('installFinishMsg');
    $installPopupMsg = url::requestToText('installPopupMsg');
    $frequencySet = url::requestToText('frequencySet');
    $intervalSet = url::requestToText('intervalSet');
    $policyEnforceAction = url::requestToText('policyEnforceAction');
    $enfMessage = url::requestToText('enfMessage');
    $distributionType = url::requestToText('distType');
    $andSourcePath = url::requestToText('sourcePath');
    $andDestinationPath = url::requestToText('destinationPath');
    $messageText = url::requestToText('title');

    logs::log(__FILE__, __LINE__, '$_Request - ', [$_REQUEST]);

    if ($platform == "android") {

        switch ($installType) {
            case "0":
                $preInstallMsg = "";
                $postDownloadMsg = "";
                $finalInstallMsg = "";
                $frequencySettings = "";
                break;
            case "3":
                $preInstallMsg = "," . $preDownloadMsg . "," . $preDownloadPosMsg . "," . $preDownloadNegMsg;
                $postDownloadMsg = "," . $postDownloadMsg . "," . $postDownloadPosMsg . "," . $postDownloadNegMsg;
                if ($installAction == "1") {
                    $instalFinOrPop = $installFinishMsg;
                } else if ($installAction == "2") {
                    $instalFinOrPop = $installPopupMsg;
                }
                $finalInstallMsg = "," . $installMsg . "," . $installAction . "," . $instalFinOrPop;
                $frequencySettings = $frequencySet . "," . $intervalSet . "," . $policyEnforceAction . "," . $enfMessage;
                break;
            case "5":
                $preInstallMsg = "," . $preDownloadMsg . "," . $preDownloadPosMsg . "," . $preDownloadNegMsg;
                $postDownloadMsg = "," . $postDownloadMsg . "," . $postDownloadPosMsg . "," . $postDownloadNegMsg;
                if ($installAction == "1") {
                    $instalFinOrPop = $installFinishMsg;
                } else if ($installAction == "2") {
                    $instalFinOrPop = $installPopupMsg;
                }
                $finalInstallMsg = "," . $installMsg . "," . $installAction . "," . $instalFinOrPop;
                $frequencySettings = $frequencySet . "," . $intervalSet . "," . $policyEnforceAction . "," . $enfMessage;
                break;
            default:
                $preInstallMsg = "";
                $postDownloadMsg = "";
                $finalInstallMsg = "";
                $frequencySettings = "";
                break;
        }
    }

    if ($platform == 'linux') {
        $packNameOri = url::requestToText('packName');
        $packNameOriExp = explode('_', $packNameOri);
        $packName = $packNameOriExp[0];
    } else {
        $packName = url::requestToText('packName');
    }

    if (isset($_GET['filebrowse'])) {
        $fileBrowse = strip_tags($_GET['filebrowse']);
    } else {
        $fileBrowse = url::requestToText('filebrowse');
    }

    $dTime = url::requestToText('dTime');
    $validPath = url::requestToText('dvPath');
    $mandatory = url::requestToText('mandatory');

    if ($mandatory != '1') $mandatory = '0';
    $sourceToUpload = url::requestToText('uploads');


    // $cdnUrl = "https://" . getenv("STORAGE_S3_BUCKET_NAME") . ".s3.amazonaws.com/" . getenv("STORAGE_S3_PATH") . "/softdist/";
    // if (getenv('STORAGE_TYPE') != 's3' || empty(getenv('STORAGE_S3_BUCKET_NAME'))) {
    $cdnUrl = "https://" . getenv("DASHBOARD_SERVICE_HOST") . "/storage/softdist/";
    // }



    $ftpUrl = null;
    $preinst = url::requestToText('preinstcheck');
    $iconName = url::requestToText('androidI');

    if ($stype == '2' || $stype == '5') {
        if ($selectType == "edit") {
            if ($sourceToUpload == "") {
                $sourcesql = "SELECT protocol FROM " . $GLOBALS['PREFIX'] . "softinst.Packages WHERE id=?";
                $pdo = $dbo->prepare($sourcesql);
                $pdo->execute([$editid]);
                $sourceres = $pdo->fetch(PDO::FETCH_ASSOC);
                $sourceToUpload = $sourceres['protocol'];
            } else {
                $sourceToUpload = $sourceToUpload;
            }
        } else {
            $sourceToUpload = $sourceToUpload;
        }
        $protocol = $sourceToUpload;
        if ($sourceToUpload == 2) {
            $dom = $cdnUrl;
            $path = $cdnUrl . $fileName;
            if ($filename2 != '') {
                $path2 = $cdnUrl . $filename2;
            }

            $androidIcon = $cdnUrl . url::requestToText('androidI');
        } else if ($sourceToUpload == 1) {
            $dom = $ftpUrl;
            $path = $ftpUrl . $fileName;
            if ($filename2 != '') {
                $path2 = $ftpUrl . $filename2;
            }
            $androidIcon = $ftpUrl . strip_tags(url::requestToText('androidI'));
        } else {
            $dom = $ftpUrl;
            $path = $path;
            $androidIcon = url::requestToText('androidI');
        }
    } else if ($stype === '3') {
        if ($platform == 'windows') {
            $path = $packName;
        }
    }
    if ($peerdistribution == "1" || $peerdistribution == 1) {
        $peer = ",NA,NA,1";
    } else if ($peerdistribution == "0" || $peerdistribution == 0) {
        $peer = ",NA,NA,0";
    } else {
        $peer = "";
    }

    if ($distributionType == "3") {
        $stype = "2";
        $packDesc = "NA";
        $version = "NA";
        $policyEnforce = 0;
        $downloadType = 1;
    } else if ($distributionType == "1") {
        $policyEnforce = 0;
        $downloadType = 1;
    } else if ($distributionType == "2") {
        $policyEnforce = $policyEnforce;
        $downloadType = 1;
    }

    if ($platform == 'windows') {
        if ($distribute == '1') {

            if ($preinst != '') {

                $pfilePath = url::requestToText('pfilePath');
                $pSoftName = url::requestToText('pSoftName');
                $pSoftVer = url::requestToText('pSoftVer');
                $pKb = url::requestToText('pKb');
                $pServicePack = url::requestToText('pServicePack');
                $rootKey = url::requestToText('rootKey');
                $subKey = url::requestToText('subKey');
                $pExecPreCheckVal = url::requestToText('pExecPreCheckVal');
                $pRegName = url::requestToText('pRegName');
                $pType = url::requestToText('pType');
                $pValue = url::requestToText('pValue');

                if ($pExecPreCheckVal == 0) {
                    if ($preinst == '1') {
                        $notVal = "";
                    } else {
                        $notVal = "!";
                    }
                } else {
                    $notVal = "";
                }

                $pKb = str_replace(",", "&&", $pKb);

                if ($pSoftVer == '') {
                    $pSoftVer_m = 'NA';
                } else {
                    $pSoftVer_m = $pSoftVer;
                }
                if ($pKb == '') {
                    $pKb_m = 'NA';
                } else {
                    $pKb_m = $pKb;
                }
                if ($pServicePack == '') {
                    $pServicePack_m = 'NA';
                } else {
                    $pServicePack_m = $pServicePack;
                }

                if ($preinst == 0) {
                    $preinstallCheck = $notVal . "0,$pfilePath";
                } else if ($preinst == 1) {
                    $preinstallCheck = $notVal . "1,";
                    if ($pSoftVer_m == 'NA' && $pKb_m == 'NA' && $pServicePack_m == 'NA') {
                        $preinstallCheck .= "$pSoftName";
                    } else if ($pKb_m == 'NA' && $pServicePack_m == 'NA') {
                        if ($pSoftName == '') {
                            $pSoftName = 'NA';
                        }
                        $preinstallCheck .= "$pSoftName#$pSoftVer_m";
                    } else if ($pServicePack_m == 'NA') {
                        if ($pSoftName == '') {
                            $pSoftName = 'NA';
                        }
                        $preinstallCheck .= "$pSoftName#$pSoftVer_m#$pKb_m";
                    } else {
                        if ($pSoftName == '') {
                            $pSoftName = 'NA';
                        }
                        $preinstallCheck .= "$pSoftName#$pSoftVer_m#$pKb_m#$pServicePack_m";
                    }
                } else if ($preinst == 2) {
                    $preinstallCheck = $notVal . "2,$rootKey#$subKey#$pRegName#$pType#$pValue";
                    if ($rootKey == '') {
                        $preinstallCheck = $notVal . "2,NA#$subKey#$pRegName#$pType#$pValue";
                    }
                    if ($subKey == '') {
                        $preinstallCheck = $notVal . "2,$rootKey#$pRegName#$pType#$pValue";
                    }
                } else if ($preinst == 3) {
                    $preinstallCheck = $notVal . "1,$pSoftName";
                } else {
                    $preinstallCheck = "NA,NA";
                }

                $confStr = "1,NT,$path,1,1,4,$dpath,$preinstallCheck,0#$dpath$fileName$peer";
            } else {

                $confStr = "1,NT,$path,1,1,4,$dpath,NA,NA,0#$dpath$fileName$peer";
            }
        }
    } else if ($platform == 'mac' && $stype == '2') {
        $pfilePath = $pSoftName = $pSoftVer = $pExecPreCheckVal = $preinstallCheck = '';
        if ($preinst != '') {
            if ($preinst == 0) {
                $pfilePath = url::issetInRequest('pfilePath') ? url::requestToText('pfilePath') : '';
            } else if ($preinst == 1) {
                $pSoftName = url::issetInRequest('pSoftName') ? url::requestToText('pSoftName') : '';
                $pSoftVer = url::issetInRequest('pSoftVer') ? url::requestToText('pSoftVer') : '';
            }
            if ($pSoftVer == '') {
                $pSoftVer_m = 'NA';
            } else {
                $pSoftVer_m = $pSoftVer;
            }
            if ($preinst == 0) {
                $preinstallCheck = ",0,$pfilePath";
            } else if ($preinst == 1) {
                $preinstallCheck = ",1,";
                if ($pSoftVer_m == 'NA') {
                    $preinstallCheck .= "$pSoftName";
                } else {
                    if ($pSoftName == '') {
                        $pSoftName = 'NA';
                    }
                    $preinstallCheck .= "$pSoftName#$pSoftVer_m";
                }
            }
        }

        if ($stype == '3') {
            $confstr = "1,MAC,$path,0,0,4,$macpath,1,$packName";
        } else if ($stype == '2') {
            if ($preinst == '') {
                $confstr = "1,MAC,$path,0,0,4,$macpath";
            } else {
                $confstr = "1,MAC,$path,0,0,4,$macpath$preinstallCheck";
            }
        }
    } else if ($platform == 'mac' && $stype == '3') {
        $confstr = $macpath . $fileName;
    } else if ($platform == 'ios') {
        $confstr = "0,$path";
    } else if ($platform == 'linux' && $stype == '2') {
        $pfilePath = $pSoftName = $pSoftVer = $pExecPreCheckVal = $preinstallCheck = '';
        if ($preinst != '') {
            if ($preinst == 0) {
                $pfilePath = url::issetInRequest('pfilePath') ? url::requestToText('pfilePath') : '';
            } else if ($preinst == 1) {
                $pSoftName = url::issetInRequest('pSoftName') ? url::requestToText('pSoftName') : '';
                $pSoftVer = url::issetInRequest('pSoftVer') ? url::requestToText('pSoftVer') : '';
            }
            if ($pSoftVer == '') {
                $pSoftVer_m = 'NA';
            } else {
                $pSoftVer_m = $pSoftVer;
            }
            if ($preinst == 0) {
                $preinstallCheck = ",0,$pfilePath";
            } else if ($preinst == 1) {
                $preinstallCheck = ",1,";
                if ($pSoftVer_m == 'NA') {
                    $preinstallCheck .= "$pSoftName";
                } else {
                    if ($pSoftName == '') {
                        $pSoftName = 'NA';
                    }
                    $preinstallCheck .= "$pSoftName#$pSoftVer_m";
                }
            }
        }

        if ($stype == '3') {
            $confstr = "1,LINUX,$path,0,0,4,$macpath,1,$packName";
        } else if ($stype == '2') {
            if ($preinst == '') {
                $confstr = "1,LINUX,$path,0,0,4,$macpath";
            } else {
                $confstr = "1,LINUX,$path,0,0,4,$macpath$preinstallCheck";
            }
        }
    } else if ($platform == 'linux' && $stype == '3') {
        $confstr = $macpath . $fileName;
    } else {
        if ($stype == '4') {
            $confstr = $mandatory . "," . $packName;
        } else {
            $confstr = $mandatory . "," . $path;
        }
    }

    if ($platform == 'ios') {

        $distributeLabel = (!isset($uniAction) || empty($uniAction)) ? '' : ', distrubute';
        $distributeValue = (!isset($uniAction) || empty($uniAction)) ? '' : ", ?";

        if ($stype == '5' || $stype == '2') {
            if ($sourceToUpload == 2) {
                $manifestNamePath = "itms-services://?action=download-manifest&url=" . $ftpUrl . "" . $manifestName . ".plist";
                $manifestCreation = $ftpUrl . "" . $manifestName . ".plist";
                $myManifestFile = fopen("../swd/$manifestName.plist", "w");

                if (strpos($path, 'png') !== false) {
                    $temp = $path;
                    $path = $androidIcon;
                    $androidIcon = $temp;
                }

                if ($stype == '5') {
                    $SWDPOLICY = '';
                    $plistContent = '<?xml version="1.0" encoding="UTF-8"?>
                        <!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
                        <plist version="1.0">';
                } else {
                    $SWDPOLICY = 'SWDPOLICY#';
                    $plistContent = '';
                }

                $plistContent .= $SWDPOLICY . '<dict>
                                <key>items</key>
                                <array>
                                        <dict>
                                                <key>assets</key>
                                                <array>
                                                        <dict>
                                                                <key>kind</key>
                                                                <string>software-package</string>
                                                                <key>url</key>
                                                                <string>' . $path . '</string>
                                                        </dict>
                                                        <dict>
                                                                <key>kind</key>
                                                                <string>display-image</string>
                                                                <key>url</key>
                                                                <string>' . $androidIcon . '</string>
                                                        </dict>
                                                        <dict>
                                                                <key>kind</key>
                                                                <string>full-size-image</string>
                                                                <key>url</key>
                                                                <string>' . $androidIcon . '</string>
                                                        </dict>
                                                </array>
                                                <key>metadata</key>
                                                <dict>
                                                        <key>bundle-identifier</key>
                                                        <string>' . $packName . '</string>
                                                        <key>bundle-version</key>
                                                        <string>' . $version . '</string>
                                                        <key>kind</key>
                                                        <string>software</string>
                                                        <key>title</key>
                                                        <string>' . $fileName . '</string>
                                                </dict>
                                        </dict>
                                </array>
                        </dict>';
                if ($stype == '5') {
                    $plistContent .= '</plist>';
                }

                fwrite($myManifestFile, $plistContent);
                fclose($myManifestFile);
            } else if ($sourceToUpload == 1) {
                $manifestNamePath = "itms-services://?action=download-manifest&url=" . $ftpUrl . "" . $manifestName . ".plist";
                $manifestCreation = $ftpUrl . "" . $manifestName . ".plist";
                $myManifestFile = fopen("../swd/$manifestName.plist", "w");

                if (strpos($path, 'png') !== false) {
                    $temp = $path;
                    $path = $androidIcon;
                    $androidIcon = $temp;
                }

                if ($stype == '5') {
                    $SWDPOLICY = '';
                    $plistContent = '<?xml version="1.0" encoding="UTF-8"?>
                        <!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
                        <plist version="1.0">';
                } else {
                    $SWDPOLICY = 'SWDPOLICY#';
                    $plistContent = '';
                }
                $plistContent .= $SWDPOLICY . '<dict>
                                <key>items</key>
                                <array>
                                        <dict>
                                                <key>assets</key>
                                                <array>
                                                        <dict>
                                                                <key>kind</key>
                                                                <string>software-package</string>
                                                                <key>url</key>
                                                                <string>' . $path . '</string>
                                                        </dict>
                                                        <dict>
                                                                <key>kind</key>
                                                                <string>display-image</string>
                                                                <key>url</key>
                                                                <string>' . $androidIcon . '</string>
                                                        </dict>
                                                        <dict>
                                                                <key>kind</key>
                                                                <string>full-size-image</string>
                                                                <key>url</key>
                                                                <string>' . $androidIcon . '</string>
                                                        </dict>
                                                </array>
                                                <key>metadata</key>
                                                <dict>
                                                        <key>bundle-identifier</key>
                                                        <string>' . $packName . '</string>
                                                        <key>bundle-version</key>
                                                        <string>' . $version . '</string>
                                                        <key>kind</key>
                                                        <string>software</string>
                                                        <key>title</key>
                                                        <string>' . $fileName . '</string>
                                                </dict>
                                        </dict>
                                </array>
                        </dict>';
                if ($stype == '5') {
                    $plistContent .= '</plist>';
                }
                fwrite($myManifestFile, $plistContent);
                fclose($myManifestFile);
            } else {
                $manifestNamePath = "";
            }

            if ($upStat == "1" || $upStat == 1) {
                $upStatus = 'Uploaded';
            } else {
                $upStatus = 'Initiated';
            }
            $sql = "INSERT INTO $package (platform, type, sourceType, protocol, packageDesc, path,path2,config3264type, fileName,fileName2, packageName, androidIcon, version, androiddate, androidnotify, androidUninstall, access, userName, password, domain, status, fileSize, global, owner, distrubute, distributionPath, user, lastModified, configDetail, distributionConfigDetail, isConfigured) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'0',?,?,?,?,?,'1')";
            $insertBindings = array($platform,  $type,  $stype,  $protocol,  $packDesc,  $manifestNamePath, $path2, $Conig_32_64, $fileName, $filename2,  $packName,  $androidIcon,  $version,  $actionDate,  $notify,  $uniAction,  $access,  $uname,  $password,  $domain,  $upStatus,  $filesize,  $global,  $user,  $manifestNamePath,  $username,  $now,  $confstr,  $confstr);
        } else if ($stype == '6') {
            $sql = "INSERT INTO $package (appId, platform, type, sourceType, protocol, packageDesc, path,path2,config3264type, fileName ,fileName2,packageName, version, access, userName, password, domain, status, fileSize, global, owner $distributeLabel, distributionPath, user, lastModified, configDetail, distributionConfigDetail, isConfigured) VALUES(?,?,?,?,?,' N.A. ',?,?,?,?,?,?,?,?,?,?,?,'Uploaded',?,?,? $distributeValue ,?,?,?,?,?,'1')";
            $insertBindings = array($appId, $platform, $type, $stype, $protocol, $path, $path2, $Conig_32_64, $fileName, $filename2, $packName, $version, $access, $uname, $password, $domain, $filesize, $global, $user);
            if (!isset($uniAction) || empty($uniAction)) $insertBindings[] = $distribute;
            $insertBindingsOther = array($dpath, $username, $now, $confstr, $confstr);
            $insertBindings = array_merge($insertBindings, $insertBindingsOther);
        } else {
            $sql = "INSERT INTO $package (platform, type, sourceType, protocol, packageDesc, path,path2,config3264type, fileName,fileName2, packageName, version, access, userName, password, domain, status, fileSize, global, owner $distributeLabel, distributionPath, user, lastModified, configDetail, distributionConfigDetail, isConfigured) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'Uploaded',?,?,? $distributeValue ,?,?,?,?,?,'1')";
            $insertBindings = array($platform, $type, $stype, $protocol, $packDesc, $path, $path2, $Conig_32_64, $fileName, $filename2,  $packName, $version, $access, $uname, $password, $domain, $filesize, $global, $user);
            if (!isset($uniAction) || empty($uniAction)) $insertBindings[] = $distribute;
            $insertBindingsOther = array($dpath, $username, $now, $confstr, $confstr);
            $insertBindings = array_merge($insertBindings, $insertBindingsOther);
        }
    } else if ($platform == 'android') {
        if ($distributionType == "2" || $distributionType == 2) {
            $isconfigure = 1;
        } else {
            $distribute = 1;
            $isconfigure = 0;
        }

        if ($stype == '2' || $stype == '5') {
            if ($upStat == "1" || $upStat == 1) {
                $upStatus = 'Uploaded';
            } else {
                $upStatus = 'Initiated';
            }

            $mUniActionLabel = (!isset($uniAction) || empty($uniAction)) ? '' : ', androidUninstall';
            $mUniActionValue = (!isset($uniAction) || empty($uniAction)) ? '' : ", '" . $uniAction . "'";

            $sql = "INSERT INTO $package (platform, type, sourceType, protocol, packageDesc, path,config3264type, fileName,fileName2, packageName, androidIcon, androidSite, version,androiddate, androidnotify $mUniActionLabel, access, userName, password, domain, status, fileSize, global, owner, distrubute, distributionPath, user, lastModified, configDetail, preinstall, oninstall,isConfigured) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,? $mUniActionValue,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $distribute = (!isset($distribute) || empty($distribute)) ? '0' : $distribute;

            $insertBindings = array($platform, $type, $stype, $protocol, $packDesc, $path, $Conig_32_64, $fileName, $filename2, $packName, $androidIcon, $siteName, $version, $actionDate, $notify);
            // if((isset($uniAction) && !empty($uniAction))) $insertBindings[] = $uniAction;
            $insertBindingsOther = array($access, $uname, $password, $domain, $upStatus, $filesize, $global, $user, $distribute, $dpath, $username, $now, $confstr, $preinstall, $oninstall, $isconfigure);
            $insertBindings = array_merge($insertBindings, $insertBindingsOther);
        } else if ($stype == '4') {
            $sql = "INSERT INTO $package (platform, type, sourceType, protocol, packageDesc, path,config3264type, fileName,fileName2, packageName, version, access, userName, password, domain, status, fileSize, global, owner, distrubute, distributionPath, user, lastModified,configDetail,isConfigured) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,'Uploaded',?,?,?,?,?,?,?,?,?,?)";
            $insertBindings = array($platform,  $type,  $stype,  $protocol,  $packDesc,  $path, $Conig_32_64, $fileName, $filename2,  $packName,  $version,  $access,  $uname,  $password,  $domain,  $filesize,  $global,  $user,  $distribute,  $dpath,  $username,  $now,  $confstr,  $isconfigure);
        } else {
            $sql = "INSERT INTO $package (platform, type, sourceType, protocol, packageDesc, path,config3264type, fileName, fileName2, packageName, version, access, userName, password, domain, status, fileSize, global, owner, distrubute, distributionPath, user, lastModified, configDetail,isConfigured) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,'Uploaded',?,?,?,?,?,?,?,?,?,?)";
            $insertBindings =  array($platform,  $type,  $stype,  $protocol,  $packDesc,  $path, $Conig_32_64, $fileName, $filename2,  $packName,  $version,  $access,  $uname,  $password,  $domain,  $filesize,  $global,  $user,  $distribute,  $dpath,  $username,  $now,  $confstr,  $isconfigure);
        }
    } else if ($platform == 'mac' || $platform == 'linux') {

        if ($preinst == "") {
            $distributeMAC = "0";
        } else {
            $distributeMAC = "1";
        }

        if ($stype == '2') {

            if ($upStat == "1" || $upStat == 1) {
                $upStatus = 'Uploaded';
            } else {
                $upStatus = 'Initiated';
            }

            $sql = 'INSERT INTO ' . $package . ' (platform, type, sourceType, protocol, packageDesc, path,config3264type, fileName,fileName2, packageName, androidIcon, version, access, userName, password, domain, status, fileSize, global, owner, distrubute, distributionPath, user, lastModified, configDetail, ftpcdnURL) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
            $insertBindings = array($platform, $type, $stype,  $protocol, $packDesc,  $macpath, $Conig_32_64, $fileName, $filename2, $packName,  $androidIcon,  $version, $access, $uname, $password,  $domain, $upStatus, $filesize, $global, $user, $distributeMAC, $dpath,  $username, $now, $confstr,  $dom);
        } else {
            $sql = 'INSERT INTO ' . $package . ' (platform, type, sourceType, protocol, packageDesc, path,config3264type, fileName,fileName2, packageName, androidIcon, version, access, userName, password, domain, status, fileSize, global, owner, distrubute, distributionPath, user, lastModified, configDetail, ftpcdnURL) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
            $insertBindings = array($platform, $type, $stype,  $protocol, $packDesc,  $macpath, $Conig_32_64, $fileName, $filename2, $packName,  $androidIcon,  $version, $access, $uname, $password,  $domain, $upStatus, $filesize, $global, $user, $distributeMAC, $dpath,  $username, $now, $confstr,  $dom);
        }
    } else {

        if ($upStat == "1" || $upStat == 1) {
            $upStatus = 'Uploaded';
        } else {
            $upStatus = 'Initiated';
        }

        $saveDistribute = (!isset($distribute) || $distribute == '' || !is_numeric($distribute)) ? '0' : $distribute;

        if ($distribute == '1') {
            $sql = "INSERT INTO $package (platform, type, sourceType, protocol, packageDesc, path,path2,config3264type, fileName,fileName2, packageName,version, access, userName, password, domain, status, fileSize, global, owner, distrubute, distributionPath, distributionTime, distributionVpath, user, lastModified, addConfigDetail, configDetail)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $insertBindings = array($platform,  $type,  $stype,  $protocol,  $packDesc,  $path, $path2, $Conig_32_64,  $fileName, $filename2,  $packName,  $version,  $access,  $uname,  $password,  $domain,  $upStatus,  $filesize,  $global,  $user,  $saveDistribute,  $dpath,  $dTime,  $validPath,  $username,  $now,  $confStr,  $confstr);
        } else {
            $sql = "INSERT INTO $package (platform, type, sourceType, protocol, packageDesc, path,path2, config3264type,fileName ,fileName2, packageName,version, access, userName, password, domain, status, fileSize, global, owner, distrubute, distributionPath, distributionTime, distributionVpath, user, lastModified, configDetail) 
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $insertBindings = array($platform,  $type,  $stype,  $protocol,  $packDesc,  $path, $path2, $Conig_32_64, $fileName, $filename2,  $packName,  $version,  $access,  $uname,  $password,  $domain,  $upStatus,  $filesize,  $global,  $user,  $saveDistribute,  $dpath,  $dTime,  $validPath,  $username,  $now,  $confstr);
        }
    }
    $pdo = $dbo->prepare($sql);
    $pdo->execute($insertBindings);
    $insertedId = $dbo->lastInsertId();

    if ($stype == 2 && $sourceToUpload == 2) {
        $cdnSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.cdnfileUpload (packageid,cdnfile,platform,loggeduser) Values (?,?,?,?)";
        $insertBindings = array($insertedId, $fileName, $platform, $user);
        $pdo = $dbo->prepare($cdnSql);
        $cdnRes = $pdo->execute($insertBindings);
    }

    if ($platform == 'windows' && $distribute == '1') {
        echo $insertedId . ',D';
        if ($preinst != '') {
            $sql = "UPDATE " . $GLOBALS['PREFIX'] . "softinst.Packages SET isConfigured='3' where id=?";
            $pdo = $dbo->prepare($sql);
            $sqlres = $pdo->execute([$insertedId]);
        }

        if (!isset($peerdistribution) || empty($peerdistribution)) {
            $configSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration(packageId, dcheckPreInstall, dValidationFilePath, dsoftwareName, dsoftwareVersion, dknowledgeBase, dservicePack, dRootKey, dSubKey, pExecPreCheckVal, pRegName, pType, pValue) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?)";
            $bindings = array($insertedId, $preinst, $pfilePath, $pSoftName, $pSoftVer, $pKb, $pServicePack, $rootKey, $subKey, $pExecPreCheckVal, $pRegName, $pType, $pValue);
        } else {
            $configSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration(packageId, dcheckPreInstall, dValidationFilePath, dsoftwareName, dsoftwareVersion, dknowledgeBase, dservicePack, dRootKey, dSubKey, peerdistribution, pExecPreCheckVal, pRegName, pType, pValue) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $bindings =  array($insertedId, $preinst, $pfilePath, $pSoftName, $pSoftVer, $pKb, $pServicePack, $rootKey, $subKey, $peerdistribution, $pExecPreCheckVal, $pRegName, $pType, $pValue);
        }

        $pdo = $dbo->prepare($configSql);
        $pdo->execute($bindings);
    } else if ($platform == 'android' && $stype == '2') {
        echo $insertedId . ',ND';
        if ($downloadType == "" || $downloadType == null) {
            $downloadType = 0;
        }
        if ($policyEnforce == "" || $policyEnforce == null) {
            $policyEnforce = 0;
        }

        $possql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration (packageId,posKeywords,packageExpiry,policyEnforce,androidPreCheck,androidPostCheck,downloadType,maxTime,preInstallMsg,postDownloadMsg,finalInstallMsg,frequencySettings,installType,andPreCheckCond,andSourcePath,andDestinationPath,distributionType,messageText,andPostCheckCond,andDestPath,andPreCheckPath) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $bindings = array($insertedId,  $posKey, $packageExpiry, $policyEnforce,  $androidPreCheck,  $androidPostCheck, $downloadType,  $maxTime,  $preInstallMsg,  $postDownloadMsg,  $finalInstallMsg,  $frequencySettings,  $installType,  $andPreCheckCond,  $andSourcePath,  $andDestinationPath,  $distributionType,   $messageText,  $andPostCheckCond,  $andDestPath,   $andPreCheckPath);

        $pdo = $dbo->prepare($possql);
        $pdo->execute($bindings);
    } else if (($platform == 'mac' || $platform == 'linux') && $preinst != '' && $stype == '2') {
        $configSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration (packageId, dcheckPreInstall, dValidationFilePath, dsoftwareName, dsoftwareVersion) VALUES (?,?,?,?,?)";
        $bindings = array($insertedId, $preinst, $pfilePath, $pSoftName, $pSoftVer);
        $pdo = $dbo->prepare($configSql);
        $pdo->execute($bindings);
    } else {
        if ($platform == 'windows' && $stype == '2') {
            $configSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PackagesConfiguration(packageId, dcheckPreInstall, dValidationFilePath, dsoftwareName, dsoftwareVersion, dknowledgeBase, dservicePack, dRootKey, dSubKey, peerdistribution) VALUES (?,?,?,?,?,?,?,?,?,?)";
            $bindings = array($insertedId, $preinst, $pfilePath, $pSoftName, $pSoftVer, $pKb, $pServicePack, $rootKey, $subKey, $peerdistribution);
            $pdo = $dbo->prepare($configSql);
            $pdo->execute($bindings);
        }
        echo $insertedId . ',ND';
    }
}
