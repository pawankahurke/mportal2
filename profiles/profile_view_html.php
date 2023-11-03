<?php


unset($_SESSION['pwvardata']);

$pw_id = url::requestToAny('id');
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

include_once '../lib/l-profilewiz.php';

$profileName = PRWZ_getProfileName($pw_id, 'view');
if ($profileName == '') {
    header('Location: index.php');
}

?>
<div class="content white-content profilePage">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form action="profile.php" method="POST" onsubmit="return false;">
                    <div class="card-body" id="profwiz-add">
                        <div class="toolbar">
                            <div class="bullDropdown leftDropdown">
                                <div class="dropdown">
                                    <span>View Profile : <b><?php echo $profileName; ?></b></span>
                                </div>
                            </div>

                            <div class="bullDropdown">
                                <div class="dropdown">
                                    <div id="close-swd-widget" class="r-ic" onclick="javascript:location.href='index.php'"><i class="tim-icons icon-simple-remove"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="row clearfix innerPage">
                            <div class="col-md-12">

                                <!-- hidden fields -->
                                <input type="hidden" name="profileID" id="profileID" value="<?php echo url::toText($pw_id); ?>">
                                <input type="hidden" id="reviewdata" value="client">

                                <!-- profileFive starts here -->
                                <div class="col-md-12 col-sm-12 rt-equalHeight profileFive eachPWrap" id="profile5">
                                    <button type="button" class="swal2-confirm btn btn-success btn-sm btn-next review-tiles" id="render-dash" onclick="renderProfileConfiguration();" style="width: 170px !important;">View Dashboard Tiles</button>
                                    <button type="button" class="swal2-confirm btn btn-alert btn-sm btn-next review-tiles" id="render-clnt" onclick="renderClientProfileConfiguration();" style="width: 140px !important;">View Client Tiles</button>
                                    <div class="Box">
                                        <div class="row clearfix innerPage">
                                            <div class="col-md-3 col-sm-12 col-xs-12 lf-rt-br equalHeight">
                                                <div class="table-responsive innerLeft">
                                                    <div class="form">
                                                        <div class="sidebar">
                                                            <ul class="nav" id="levelOneData">
                                                                <!-- Data renders here dynamically -->
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-9 col-sm-12 col-xs-12 rt-lf equalHeight">
                                                <div class="troubleInn">
                                                    <h4 id="tile-header">Troubleshooters</h4>
                                                    <p id="tile-description">Use these troubleshooting and resolution tools to quickly and easily resolve many common device issues. Choose a category on the left and then select the fix that best matches the issue you are experiencing.</p>
                                                </div>

                                                <div class="troubleInn" style="display: block;">
                                                    <div class="table-full-width table-responsive">
                                                        <div class="form">
                                                            <div class="sidebar">
                                                                <div id="child-lvl">
                                                                    <!-- Data renders here dynamically -->
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- end content-->
                </form>
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>

<style>
    .open-dart-console {
        cursor: pointer
    }

    a.w3-dart-title,
    a.w3-dart-title:hover {
        color: #1d253b;
        font-size: 12px;
    }

    .w3-dart-elements .my-collapse-content span {
        position: relative;
        left: 15px;
    }

    .bx-cnt-spn {
        border: 1px solid #06113c;
        border-radius: 20px;
        font-size: 10px;
        float: right;
        width: 25px;
        text-align: center;
        color: #000;
        font-weight: bold;
        height: 25px;
        margin: -12px -12px 0px 0px;
        padding: 4px 0px 0px;
    }

    span.error {
        margin-right: 6px !important;
        position: relative;
        top: 3px;
    }

    span.error.left {
        float: left !important;
        top: -3px !important;
    }

    span.error.opsys {
        font-size: 0.875rem !important;
        top: -1px !important;
    }

    select.dart-select.left {
        width: 96%;
    }

    input.dart-input-title {
        width: 96%;
    }

    div#ck-uck-all {
        position: relative;
        top: -12px;
    }

    div#ck-uck-all p {
        position: relative;
        top: 7px;
        color: #1d253b;
        font-weight: 400;
        font-size: 13px;
    }

    div#ck-uck-all-cli {
        position: relative;
        top: -12px;
    }

    div#ck-uck-all-cli p {
        position: relative;
        top: 7px;
        color: #1d253b;
        font-weight: 400;
        font-size: 13px;
    }

    #accordion .card div.card-header {
        width: 98%;
    }

    .r-ic {
        background-color: #ffffff;
        border-radius: 13px;
        padding: 0px 5px 2px 5px;
        border: 1px solid #C1C1C1;
        cursor: pointer;
        box-shadow: 0px 0px 3px 1px #ccc;
        -webkit-box-shadow: 0px 0px 3px 1px #ccc;
    }
</style>