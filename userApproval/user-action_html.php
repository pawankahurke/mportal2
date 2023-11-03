<?php
include_once '../config.php';
include_once '../lib/l-dbConnect.php';

$pdo = pdo_connect();

//$q = "select userid, username, firstName, lastName, user_email, userStatus,
//     userKey from core.Users";
$q = "select userid, username, firstName, lastName, user_email, userStatus, userKey, userType from " . $GLOBALS['PREFIX'] . "core.Users where userType in ('SSO', 'Other') AND userStatus != '1'";
$stmt = $pdo->prepare($q);
$stmt->execute();
$userdata = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rolesql = $pdo->prepare('SELECT assignedRole,displayName FROM ' . $GLOBALS['PREFIX'] . 'core.RoleMapping WHERE global=1');
$rolesql->execute();
$roleres = $rolesql->fetchAll(PDO::FETCH_ASSOC);

$sitesql = $pdo->prepare("SELECT customer FROM " . $GLOBALS['PREFIX'] . "core.Customers GROUP BY customer");
$sitesql->execute();
$siteres = $sitesql->fetchAll(PDO::FETCH_ASSOC);

$loggedUser = $_SESSION['user']['rolename'];

if ($loggedUser === 'SuperAdminRole') {
    $disabled2 = '';
} else {
    $disabled2 = 'disabled';
}

?>
<style type="text/css">
    .nhcenter {
        text-align: center;
        margin-top: 2px;
    }
</style>
<div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-scroll" data-qa="userApprovalPageBody">
                <?php
                //print_r($userdata);
                if (safe_count($userdata) > 0) { ?>
                    <?php foreach ($userdata as $key => $value) {
                        $domain_name = substr(strrchr($value['user_email'], "@"), 1);
                        $userEmail = safe_addslashes("%$domain_name%");
                        $userSql = $pdo->prepare('SELECT * FROM ' . $GLOBALS['PREFIX'] . 'core.Users WHERE user_email like "' . $userEmail . '"');
                        $userSql->execute();
                        $userRes = $userSql->fetchAll(PDO::FETCH_ASSOC);

                        if (safe_count($userRes) < 2) {
                            $roleName = 'SuperAdminRole';
                            $userRole = '<select disabled class="form-control" data-style="btn btn-info" title="Select user role" data-size="3" id="userrole' . $key . '" name="userrole' . $key . '" required style="height: 37px;">';
                            foreach ($roleres as $rolevalue) {
                                if ($rolevalue['displayName'] === $roleName) {
                                    $selected = 'selected';
                                } else {
                                    $selected = '';
                                }
                                $userRole .= '<option value="' . $rolevalue['assignedRole'] . '" ' . $selected . ' >' . $rolevalue['displayName'] . '</option>';
                            }
                            $userRole .= '</select>';
                        } else {
                            $selected = '';
                            $userRole = '<select "' . $disabled2 . '" class="form-control" data-style="btn btn-info" title="Select user role" data-size="3" id="userrole' . $key . '" name="userrole' . $key . '" required style="height: 37px;">';
                            foreach ($roleres as $rolevalue) {
                                $userRole .= '<option value="' . $rolevalue['assignedRole'] . '" ' . $selected . ' >' . $rolevalue['displayName'] . '</option>';
                            }
                            $userRole .= '</select>';
                        }
                        // print_r($userRole);exit;
                    ?>
                        <div class="card-body" style="padding: 2% 3% 0px 3%;">
                            <form id="userApproval<?php echo $key; ?>" name="userApproval<?php echo $key; ?>">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group has-label">
                                            <div class="row">
                                                <div class="col-md-3 nhcenter">
                                                    <h5 for="appr-fname<?php echo $key; ?>">First Name</h5><em class="error" id="err-fname"></em>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="appr-fname" id="appr-fname<?php echo $key; ?>" class="form-control" placeholder="First Name" readonly="" value="<?php echo url::toText($value['firstName']); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group has-label">
                                            <div class="row">
                                                <div class="col-md-3 nhcenter">
                                                    <h5 for="appr-emailid">EMail ID</h5><em class="error" id="err-lname"></em>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="appr-emailid" id="appr-emailid" class="form-control" placeholder="EMail ID" readonly="" value="<?php echo url::toText($value['user_email']); ?>">
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group has-label">
                                            <div class="row">
                                                <div class="col-md-3 nhcenter">
                                                    <h5 for="appr-fnameLast<?php echo $key; ?>">Last Name</h5><em class="error" id="err-lname"></em>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="text" name="appr-lname" id="appr-fnameLast<?php echo $key; ?>" class="form-control" placeholder="Last Name" readonly="" value="<?php echo url::toText($value['lastName']); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group has-label" style="margin-top: 4%;">
                                            <div class="row">
                                                <?php if ($value['userStatus'] == '1') {
                                                    $disabled = ''; ?>
                                                    <div class="col-md-6" style="text-align: center; margin-top: 2%;">
                                                        <b data-qa="approveUser-<?php echo $key; ?>">User access approved</b>
                                                    </div>
                                                <?php } else {
                                                    $disabled = 'disabled'; ?>
                                                    <div class="col-md-6" style="text-align: center;">
                                                        <button type="button" class="btn btn-success btn-md btn mb-3 btn-lite" data-qa="approveUser-<?php echo $key; ?>" onclick="approveUser('<?php echo $value['userKey']; ?>','<?php echo $value['user_email']; ?>');">Approve</button>
                                                    </div>
                                                <?php } ?>
                                                <div class="col-md-6">
                                                    <button type="button" class="btn btn-alert btn-md btn mb-3 btn-lite" data-qa="rejectUser-<?php echo $key; ?>" onclick="rejectUser('<?php echo $value['userKey']; ?>','<?php echo $value['user_email']; ?>');">Reject</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <h5><b>User Permissions</b></h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group has-label">
                                            <div class="row">
                                                <div class="col-md-3 nhcenter">
                                                    <h5>User Role <em class="error">&nbsp;</em></h5>
                                                </div>
                                                <div class="col-md-9">
                                                    <?php echo $userRole ?>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group has-label" style="margin-top: 4%;">
                                            <button <?php echo $disabled2; ?> type="button" class="btn btn-success btn-md btn mb-3 btn-lite" data-qa="saveUserDetails-<?php echo $key; ?>" onclick="saveUserDetails(<?php echo $key . ',\'' . $value['userKey'] . '\'' . ',\'' . $value['user_email'] . '\''; ?>)">Save</button>
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                            <button <?php echo $disabled2; ?> type="button" class="btn btn-alert btn-md btn mb-3 btn-lite" data-qa="CancelUserDetails-<?php echo $key; ?>">Cancel</button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group has-label">
                                            <div class="row">
                                                <div class="col-md-3 nhcenter">
                                                    <h5> Site Permission <em class="error">&nbsp;</em></h5>
                                                </div>
                                                <div class="col-md-9">
                                                    <select <?php echo $disabled2; ?> class="form-control" multiple="" data-style="btn btn-info" data-qa="site-select-<?php echo $key; ?>" title="Select the site" data-size="3" id="sitelist<?php echo $key; ?>" name="sitelist<?php echo $key; ?>" required>
                                                        <?php foreach ($siteres as $key => $value) { ?>
                                                            <option data-qa="site-option-<?php echo $key; ?>" value="<?php echo url::toText($value['customer']); ?>"><?php echo $value['customer']; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <p class="dropdown-divider"></p>
                    <?php }
                } else { ?>
                    <div style="text-align: center; margin-top: 20%;">
                        <h4 data-qa="no-pending-userApproval">You don't have any pending access requests!</h4>
                    </div>
                <?php } ?>
                <!-- end content-->
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-6 -->

    </div>
    <!-- end row -->
</div>
