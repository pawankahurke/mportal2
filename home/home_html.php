<div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body" style="text-align: center !important;">
                    <img src="../vendors/images/welcome.png" alt="Welcome User" />

                    <div class="col-md-6" style="margin-left: 23%;">
                        <h4 style="font-weight: bold;">Welcome!</h4>
                      <a href="../"><button type="button" class="btn btn-sm btn-light">LOGOUT</button></a>
                        <p data-cy="text-not-access-user">You don't have access to any sites/devices at the moment.
                            Please contact your portal administrator to give you the
                            required access and user privileges</p>
                    </div>

                    <!--
                      Button not work
                      <div class="form-group has-label">
                        <button type="button" class="btn btn-alert btn-md btn mb-3 btn-lite" data-qa="contactAdminForPortalAccess" onclick="contactAdminForPortalAccess();">Contact Administrator</button>
                    </div> -->

                    <div id="response-msg">

                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="homeusername" value="<?php echo url::toText($_SESSION['user']['username']); ?>" />
</div>

<script type="text/javascript">
    document.getElementById('uname').innerHTML = document.getElementById('homeusername').value;

    function contactAdminForPortalAccess(useremail) {
        $.ajax({
            url: 'homeFunction.php',
            type: 'POST',
            data: {
                'function': 'contactAdminForPortalAccessFunc',
                csrfMagicToken: csrfMagicToken
            },
            success: function(data) {
                if ($.trim(data) == 'ok') {
                    $('#response-msg').html('Notified your administrator to grant you required access.').css({
                        'color': 'green'
                    });
                } else {
                    $('#response-msg').html('Failed to contact administrator. Please try again.').css({
                        'color': 'red'
                    });
                }
            },
            error: function(erro) {

            }
        });
    }
</script>
