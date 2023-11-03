<?php
if (!isset($_SESSION)) {
}
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

$_SESSION['windowtype'] = 'sso';
$_SESSION['currentwindow'] = 'sso';

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
require_once 'sso_auth_html.php';
require_once '../layout/footer.php';

// Check authorization
nhUser::redirectIfNotAuth();

?>
<style type="text/css">
    .buttonGrey {
        background: rgba(0, 0, 0, 0.20);
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 52px;
        height: 25px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 22px;
        width: 22px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked+.slider {
        background-color: #fb1864;
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked+.slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    #absoLoader {
        width: 98%;
        height: 100%;
        position: absolute;
        z-index: 10;
        background-color: #ffffff;
        top: 10px;
        opacity: 0.5;
    }

    #absoLoader img {
        margin-top: 20%;
        margin-left: 47%;
    }

    .btn-sso {
        font-size: 0.7rem;
        font-weight: normal;
        padding: 10px;
    }

    #saml_idp_metadata {
        border: 1px solid lightgrey;
        border-radius: 5px;
        max-height: 250px;
        height: 225px;
    }

    .txt-loader {
        margin-top: 15px;
        font-size: 12px;
        margin-left: 15px;
    }
</style>

<script type="text/javascript">
    $('#pageName').html('Single sign-on Configuration');
</script>
<script src="../js/rightmenu/rightMenu.js"></script>
<?php
$res = nhRole::checkModulePrivilege('sso');
if ($res) {
?>
    <script src="../js/sso/sso_auth.js"></script>
<?php
}
?>
