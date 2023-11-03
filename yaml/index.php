<?php

if (!isset($_SESSION)) {
}
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'alertnotification';
$_SESSION['currentwindow'] = 'alertnotification'; 
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'yaml_list_html.php';
require_once '../layout/footer.php';
?>
<script type="text/javascript" src="../js/yaml.js"></script>
<style>
    .slider-feedwrapper {
        height: 60px;
        background-color: #fff;
        top: -12px;
    }

    .tm0 {
        top: 0px !important;
        float: left;
        margin-left: 25px;
        font-size: 12px;
    }

    .success {
        color: green;
    }

    .error {
        color: red;
    }

    .dt-its {
        position: relative;
        top: -2px;
        left: 6px;
    }


    div.bottom {
        bottom: 39px !important;
    }

    #YamlListGrid_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }
</style>