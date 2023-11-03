<?php



include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'lib/l-db.php';
include_once 'lib/l-sql.php';
include_once 'lib/l-gsql.php';
include_once 'lib/l-rcmd.php';
include_once 'lib/l-crmdetls.php';

global $base_url;
global $file_path;
global $CRMEN;

$conn       = db_connect();
db_change($GLOBALS['PREFIX'] . "agent", $conn);
$sid        = '';
$cid        = '';
// @warn sql injection
$insertedId = url::issetInRequest('id') ? url::requestToAny('id') : '';
if ($insertedId != '') {
    $cid = $insertedId;
}

if ($insertedId == '') {
    // @warn sql injection
    $insertedId = url::issetInRequest('sid') ? url::requestToAny('sid') : '';
    $sid        = $insertedId;
}

if ($insertedId == '') {
    echo '<script type="text/javascript">
    debugger;   location.href="../index.php";
            </script>';
} else {
    $status = '';
    if ($insertedId) {
        $status = 'DONE';
    } else {
        $status = 'NOTDONE';
    }
}

if ($cid != '') {
    // @warn sql injection
    $sql_url = "SELECT CO.sessionid, CO.customerNum customerNum, CO.orderNum orderNum,CO.processId processId,CO.compId from customerOrder CO WHERE CO.downloadId = '" . $insertedId . "' limit 1";
} elseif ($sid != '') {
    // @warn sql injection
    $sql_url = "SELECT SR.sessionid sessionid, CO.customerNum customerNum, CO.orderNum orderNum,CO.processId processId,CO.compId from customerOrder CO , " . $GLOBALS['PREFIX'] . "agent.serviceRequest SR WHERE SR.downloadId = '" . $insertedId . "' and SR.customerNum = CO.customerNum and CO.orderNum = SR.orderNum and CO.processId = SR.processId limit 1";
}

$res_url = find_one($sql_url, $conn);

if (safe_count($res_url) > 0) {
    $sessionDow = $res_url['sessionid'];
    $pid        = $res_url['processId'];
    $cid        = $res_url['compId'];
    $sql_chnl = "select C.eid,C.companyName,C.status from " . $GLOBALS['PREFIX'] . "agent.channel C  where eid='$cid'";
    $res_chnl = find_one($sql_chnl, $conn);
    if (safe_count($res_chnl) > 0) {
        $chnl_status = $res_chnl['status'];
        if ($chnl_status == '1' || $chnl_status == 1) {
            $sql_proc = "select phoneNo,chatLink,serviceLink,privacyLink,variation,logoName,downlrName,processName,deployPath32,deployPath64,androidsetup,macsetup,linuxsetup,linuxsetup64,ubuntusetup32,ubuntusetup64 from processMaster where pId=$pid limit 1";
            $res_proc = find_one($sql_proc, $conn);

            $LangCode    = 'en-US';
            $hfnCode     = '01';
            $phoineNum   = $res_proc['phoneNo'];
            $chatLink    = urlencode($res_proc['chatLink']);
            $serviceLink = $res_proc['serviceLink'];
            $privacyLink = urlencode($res_proc['privacyLink']);
            $logoName    = $res_proc['logoName'];
            $downlName   = $res_proc['downlrName'];
            $companyName = $res_proc['processName'];
            $setup32bit  = $res_proc['deployPath32'];
            $setup64bit  = $res_proc['deployPath64'];
            $android     = $res_proc['androidsetup'];
            $macsetup    = $res_proc['macsetup'];
            $susesetup32 = $res_proc['linuxsetup'];
            $susesetup64 = $res_proc['linuxsetup64'];
            $ubuntusetup  = $res_proc['ubuntusetup32'];
            $ubuntusetup64 = $res_proc['ubuntusetup64'];

            $errorVal = 0;
        } else {
            $errorVal = 2;
        }
    } else {
        $errorVal = 1;
    }
    if ($CRMEN == 1) {
        $sql_insCnt = "select id,emailId,chId,crmUserId,crmLeadId,mauticId,downloadCnt,installCnt from " . $GLOBALS['PREFIX'] . "agent.contactDetails where chId='$cid' limit 1";
        $res_DnlCnt = find_one($sql_insCnt, $conn);
        if (safe_count($res_DnlCnt) > 0) {
            $dnlCnt = $res_DnlCnt['downloadCnt'];
            $mauticId = $res_DnlCnt['mauticId'];
            $cnid   = $res_DnlCnt['id'];
            $crmCntId = $res_DnlCnt['crmUserId'];
            $dwnlCnt = $dnlCnt + 1;
            $dnlcnt_sql = "update " . $GLOBALS['PREFIX'] . "agent.contactDetails set downloadCnt='$dwnlCnt' where id='$cnid'";
            $res_login = redcommand($dnlcnt_sql, $conn);
            RSLR_updateDownlCnt($crmCntId, $dwnlCnt, $mauticId);
        }
    }
} else {
    $status = "NOTDONE";
    $errorVal = 1;
}

function foundBrowser()
{
    $u_agent = $_SERVER['HTTP_USER_AGENT'];

    if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
        $bname = 'Internet Explorer';
        $ub    = "MSIE";
    } elseif (preg_match('/Firefox/i', $u_agent)) {
        $bname = 'Mozilla Firefox';
        $ub    = "Firefox";
    } elseif (preg_match('/Chrome/i', $u_agent)) {
        $bname = 'Google Chrome';
        $ub    = "Chrome";
    } elseif (preg_match('/Safari/i', $u_agent)) {
        $bname = 'Apple Safari';
        $ub    = "Safari";
    } elseif (preg_match('/Opera/i', $u_agent)) {
        $bname = 'Opera';
        $ub    = "Opera";
    } elseif (preg_match('/Netscape/i', $u_agent)) {
        $bname = 'Netscape';
        $ub    = "Netscape";
    }

    return $ub;
}

$browser = foundBrowser();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/apple-icon.ico" ?>" />
    <link rel="icon" type="image/png" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/favicon.ico" ?>" />
    <title>Dashboard :: Sites</title>
    <!-- CSS Files -->
    <link href="assets/css/family=Montserrat.css" rel="stylesheet">
    <link href="assets/css/all.css" rel="stylesheet">
    <link href="assets/css/icons.css" rel="stylesheet" />
    <link href="assets/css/styles.css" rel="stylesheet" />
    <link href="assets/css/common.css" rel="stylesheet" />
</head>

<body class="login-page">
    <script>
        var errorVal = parseInt("<?php echo url::toText($errorVal); ?>");

        var downloadUrl = "";
        var interval = null;

        function getOsVersion() {
            var osVersion = '';
            var osType = navigator.userAgent.toLowerCase();

            if (osType.indexOf("android") !== -1) {
                osVersion = 'android';
            } else if (osType.indexOf("mac") !== -1) {
                osVersion = 'mac';
            } else if (osType.indexOf("ubuntu") !== -1 && osType.indexOf("linux") !== -1) {
                //                    osVersion = 'ubuntu';
                if (osType.indexOf("x86_64") !== -1) {
                    osVersion = 'ubuntu64bit';
                } else {
                    osVersion = 'ubuntu32bit';
                }
            } else if (osType.indexOf("linux") !== -1) {
                //                    osVersion = 'ubuntu';
                if (osType.indexOf("x86_64") !== -1) {
                    osVersion = 'suse64bit';
                } else {
                    osVersion = 'suse32bit';
                }

            } else {
                if (osType.indexOf("WOW64") !== -1 || osType.indexOf("wow64") !== -1 || osType.indexOf("Win64") !== -1 || osType.indexOf("win64") !== -1) {
                    osVersion = '64bit';
                } else {
                    osVersion = '32bit';
                }
            }
            /*if (navigator.userAgent.indexOf("WOW64") !== -1 || navigator.userAgent.indexOf("Win64") !== -1) {
             osVersion = '64bit';
             } else {
             osVersion = '32bit';
             }*/

            return osVersion;
        }

        function cancelDownload() {

            var url = "<?php echo $base_url; ?>download_helper.php?cancel_download=1";

            $.ajax({
                url: url,
                type: "GET",
                async: true,
                success: function(data, textStatus, jqXHR) {
                    resetDownloadBtn();
                },
                error: function(jqXHR, textStatus, errorThrown) {

                }
            });
        }

        function resetDownloadBtn() {
            clearInterval(interval);
            setTimeout(function() {
                $(".btn_download").show();
                $(".progressbarWidth").css("width", "0%");
                // $(".scanProgress").hide();
            }, 3000);
        }

        var globalDownloadSize = 0;
        var globalCounter = 0;

        function downloadClient() {

            var osVersion = getOsVersion();
            //console.log(osVersion);
            if (errorVal === 0) {
                $("#successDiv").show();
                $("#error_div").hide();
                var downSetup = '';
                if (osVersion === '32bit') {
                    downSetup = '<?php echo $setup32bit; ?>';
                } else if (osVersion === '64bit') {
                    downSetup = '<?php echo $setup64bit; ?>';
                } else if (osVersion === 'android') {
                    downSetup = '<?php echo $android; ?>';
                } else if (osVersion === 'mac') {
                    downSetup = '<?php echo $macsetup; ?>';
                } else if (osVersion === 'ubuntu32bit') {
                    downSetup = '<?php echo $ubuntusetup; ?>';
                } else if (osVersion === 'ubuntu64bit') {
                    downSetup = '<?php echo $ubuntusetup64; ?>';
                } else if (osVersion === 'suse32bit') {
                    downSetup = '<?php echo $susesetup32; ?>';
                } else if (osVersion === 'suse64bit') {
                    downSetup = '<?php echo $susesetup64; ?>';
                }

                var donwloadUrl = "<?php echo $base_url; ?>download_helper.php?sessionid=<?php echo $sessionDow; ?>&downlName=" + downSetup + "&downType=<?php echo $companyName; ?>&proId=<?php echo $pid ?>";
                //console.log(donwloadUrl); 
                debugger;
                window.location.href = donwloadUrl;


            } else if (errorVal === 2) {

                $("#successDiv").hide();
                $("#error_div").show();
                return;
            } else {
                $("#successDiv").hide();
                $("#error_div").show();
                return;
            }
        }
    </script>

    <div class="wrapper wrapper-full-page">
        <div class="full-page login-page">
            <div class="content white-content">
                <div class="container">
                    <div class="col-lg-4 col-md-6 ml-auto mr-auto">
                        <div class="card card-login card-white">

                            <div class="card-body">
                                <h2>Nanoheal</h2>
                                <p>
                                    You are just a few steps away from installing <br>
                                    Nanoheal. If your download does not <br>
                                    start in few seconds, click Download now.
                                </p>
                            </div>
                            <div class="card-footer">
                                <button disabled="true" type="button" class="btn btn-primary btn-lg btn mb-3" onclick="cancelDownload();">Cancel</button>
                                <button type="button" class="btn btn-primary btn-lg btn mb-3 btn_download" onclick="downloadClient();">Download</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/core/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".btn_download").hover(function() {
                $(".btn_download").addClass('hover');
            });

            $(".btn_download").mouseleave(function() {
                $(".btn_download").removeClass('hover');
            });
        });

        $(window).on("load", function() {
            downloadClient();
        });
    </script>
</body>

</html>