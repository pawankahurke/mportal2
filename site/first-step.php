<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';

?>
<div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="site-add-welcome-holder">
                        <p>
                            <img src="../assets/img/hello.svg" class="welcome-img" />
                        </p>
                        <p class="site-add-welcome">Welcome to Nanoheal</p>
                        <p class="site-add-welcome-text" data-qa="Dashboard/site/first-step.php">You dont have any sites linked to your account.
                            Please add a site to continue using Nanoheal</p>
                    </div>
                </div>
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>

<script src="../assets/js/core/jquery.min.js"></script>

<?php
require_once '../layout/footer.php';
?>
<style type="text/css">
    .welcome-img {
        text-align: center;
    }

    .site-add-welcome-holder {
        display: block;
        text-align: center;
        vertical-align: middle;
        width: 100%;
    }

    .site-add-welcome {
        height: 30px;
        width: 275px;

        border-radius: 0px;
        width: 100%;
        font-family: Montserrat;
        font-style: normal;
        font-weight: 600;
        font-size: 24px;
        line-height: 30px;
        color: #000000;

    }

    .site-add-welcome-text {
        font-family: Montserrat;
        font-style: normal;
        font-weight: 600;
        font-size: 14px;
        line-height: 30px;
        text-align: center;
        color: #000000;
        text-align: center;
        width: 100%;
    }

    .sidebar .sidebar-wrapper .nav {
        display: none;
    }
</style>

<script type="text/javascript">
    $('#pageName').html('Site');
</script>