<?php
$session_time = getenv('SESSION_TIME') ?: 300;
ini_set('session.gc_maxlifetime', $session_time * 60);
//require_once '../customer/purchase_html.php';
global $base_url;
?>
<input type="hidden" id="uid" value="<?php echo url::toText($uid); ?>">
<input type="hidden" id="user" value="">
<input type="hidden" id="custtype" value="">
<input type="hidden" id="userlogorole" value="<?php echo url::toText($_SESSION['user']['roleValue']['userlogo']); ?>">
<input type="hidden" id="customertype" value="<?php echo url::toText($_SESSION['user']['customerType']); ?>">
<input type="hidden" id="Timezone" name="Timezone">
<!-- Session timeout modal -->
<div class="modal fade" id="mdlLoggedOut" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" style="margin-top:25vh">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i id="abso-lgt-in" class="tim-icons icon-alert-circle-exc"></i> Information</h4>
            </div>
            <div class="modal-body">
                <p>You have been Signed Out due to Inactivity. Please click on Ok to Log In and continue.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="document.location.href='<?php echo "https://" . $_SERVER['HTTP_HOST'] . "/Dashboard/logout.php" ?>'">Ok</button>
            </div>
        </div>
    </div>
</div>
<!-- footer stars here -->

<!-- <footer class="footer white-content">
    <div class="container-fluid">

        <div class="copyright">
            &copy;
            <script>
                document.write(new Date().getFullYear())
            </script> Nanoheal
        </div>
    </div>
</footer> -->
</div>
</div>


<!--   Core JS Files   -->
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
<script src="../assets/js/plugins/moment.min.js"></script>
<!--  Plugin for Switches, full documentation here: http://www.jque.re/plugins/version3/bootstrap.switch/ -->
<script src="../assets/js/plugins/bootstrap-switch.js"></script>
<!--  Plugin for Sweet Alert -->
<script src="../assets/js/plugins/sweetalert2.min.js"></script>
<!--  Plugin for Sorting Tables -->
<script src="../assets/js/plugins/jquery.tablesorter.js"></script>
<!-- Forms Validations Plugin -->
<script src="../assets/js/plugins/jquery.validate.min.js"></script>
<!--  Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
<script src="../assets/js/plugins/jquery.bootstrap-wizard.js"></script>
<!--	Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
<script src="../assets/js/plugins/bootstrap-selectpicker.js"></script>
<!--  Plugin for the DateTimePicker, full documentation here: https://eonasdan.github.io/bootstrap-datetimepicker/ -->
<script src="../assets/js/plugins/bootstrap-datetimepicker.js"></script>
<!--  DataTables.net Plugin, full documentation here: https://datatables.net/    -->
<script src="../assets/js/plugins/jquery.dataTables.min.js"></script>
<!--	Plugin for Tags, full documentation here: https://github.com/bootstrap-tagsinput/bootstrap-tagsinputs  -->
<script src="../assets/js/plugins/bootstrap-tagsinput.js"></script>
<!-- Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
<script src="../assets/js/plugins/jasny-bootstrap.min.js"></script>
<!--  Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
<script src="../assets/js/plugins/nouislider.min.js"></script>
<!-- Chart JS -->
<!-- <script src="../assets/js/plugins/chartjs.min.js"></script> -->
<!--  Notifications Plugin    -->
<script src="../assets/js/plugins/bootstrap-notify.js"></script>
<!-- Control Center for Black Dashboard: parallax effects, scripts for the example pages etc -->
<script src="../assets/js/black-dashboard.min.js?v=1.0.0"></script>
<script src="../assets/js/bootstrap-colorpicker.js"></script>
<!-- <script src="../assets/js/angular.min.js"></script> -->
<script type="text/javascript" src="../js/session/idle-timer.js"></script>
<script src="../assets/js/plugins/bootstrap-tour.min.js"></script>

<!--<script src="../assets/js/plugins/bootstrap.min.js"></script>-->


<script src="../assets/js/common.js"></script>
<!-- <script src="../js/common_ajax.js"></script> -->
<!-- <script src="../js/customer/form-validation.js"></script> -->
<!-- <script src="../js/customer/purchase.js"></script> -->
<!-- <script src="../js/home/home.js"></script> -->
<!--<script src="../js/notification/notify.js"></script>-->
<!-- <script src="../js/dashTour/dashboardHelper.js"></script> -->
<script src="../js/profileViewer/profile.js"></script>
<style>
    #mdlLoggedOut {
        z-index: 100000 !important;
        background: linear-gradient(0deg, rgba(9, 35, 149, 0.1) 0%, rgba(12, 30, 94, 0.1) 100%);
    }

    #mdlLoggedOut.show {
        opacity: 1 !important;
    }

    #abso-lgt-in {
        color: red;
        margin-right: 4px;
    }

    #mdlLoggedOut h4.modal-title {
        font-weight: bold;
    }
</style>
?>
<script type="text/javascript">
    var base = '<?php echo $base_url; ?>';
    var loggedUsername = '<?php echo isset($_SESSION['user']['logged_username']) ? $_SESSION['user']['logged_username'] : '' ?>';
    var timeLogin = '<?php echo $session_time; ?>';

    $(function() {
        // var stimeout = (1000 * 60) * 30;
        var stimeout = (1000 * 60) * timeLogin;

        $('body').bind("idle.idleTimer", function() {
            $('.content.white-content,.sidebar, nav.navbar.navbar-absolute,.rightSidenav').css({
                '-webkit-filter': 'blur(1px)',
                '-moz-filter': 'blur(1px)',
                '-o-filter': 'blur(1px)'
            });
            $("#mdlLoggedOut").modal("show");
            clearphpSession();
        });

        $('body').bind("active.idleTimer", function() {});
        $('body').idleTimer(stimeout);
    });

    function clearSession() {
        sessionStorage.clear();
    }

    function clearphpSession() {
        $.ajax({
            type: "POST",
            url: "<?php echo "https://" . $_SERVER['HTTP_HOST'] . "/Dashboard/logout.php" ?>",
            data: {
                'no-redirect': 'true',
                'csrfMagicToken': csrfMagicToken
            },
            async: true,
            success: function(msg) {}
        });

    }

    function getwsurl() {
        $.ajax({
            url: "../lib/l-custAjax.php",
            type: "POST",
            data: "function=getwsurl&csrfMagicToken=" + csrfMagicToken,
            success: function(data) {
                wsurl = $.trim(data);
            },
            error: function(err) {
                console.log(err);
            }
        });
        return wsurl;
    }
</script>
<?php  //var_dump(xdebug_get_code_coverage());
//  xdebug_stop_code_coverage() ;
?>
</body>

</html>
