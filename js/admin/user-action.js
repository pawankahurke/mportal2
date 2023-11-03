$(document).ready(function () {
    //getLockedUserDetails();
    //getUserRoles();
    //getLicensedSites();
});

function getUserRoles() {
    $.ajax({
        type: "POST",
        url: "../lib/l-custAjax.php",
        data: {'function': 'CUSAJX_GetAllUser_Roles', 'csrfMagicToken': csrfMagicToken},
        success: function (response) {
            $(".selectpicker.userrole").html(response);
            $(".selectpicker").selectpicker("refresh");
        }
    });
}

function getLicensedSites() {
    var optionStr = "";
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "../lib/l-custAjax.php",
        data: {'function': 'CUSTAJX_ResellerCustomers', 'csrfMagicToken': csrfMagicToken},
        success: function (result) {
            if (result.length === 0) {
                optionStr = '<option value="">No Customers Available</option>';
            } else {
                for (i = 0; i < result.length; i++) {
                    optionStr += '<option value=' + result[i].eid + '>' + result[i].companyName + '</option>';
                }
            }
            $(".selectpicker.sitelist").html("");
            $(".selectpicker.sitelist").html(optionStr);
            $(".selectpicker").selectpicker("refresh");
        }
    });
}

function approveUser(userkey,userEmail) {
    // if (userkey == '' ) {
    //     $.notify('Aprroval error. Try again later.');
    // } else {
        $.ajax({
            url: '../lib/l-faAjax.php',
            type: 'POST',
            data: {'function': 'FAAJX_approveUser', userEmail: userEmail,userkey: userkey, 'csrfMagicToken': csrfMagicToken},
            success: function (data) {
                var res = $.trim(data);
                if (res == 'success') {
                    $.notify('User has been approved successfully.');
                    setInterval(function () {
                        location.reload();
                    }, 1500);
                } else {
                    $.notify('User approval failed.');
                }
            },
            error: function (error) {

            }
        });
    // }
}

function rejectUser(userkey, userEmail) {
    // if (userkey == '') {
    //     $.notify('Some error occurred. Try again later.');
    // } else {
        $.ajax({
            url: '../lib/l-faAjax.php',
            type: 'POST',
            data: {'function': 'FAAJX_rejectUser', userEmail: userEmail,userkey: userkey, 'csrfMagicToken': csrfMagicToken},
            success: function (data) {
                var res = $.trim(data);
                if (res == 'success') {
                    $.notify('User has been rejected/disabled successfully.');
                    setInterval(function () {
                        location.reload();
                    }, 1500);
                } else {
                    $.notify('Rejecting user failed.');
                }
            },
            error: function (error) {

            }
        });
    // }
}

function saveUserDetails(formid, userkey, userEmail) {
    var userRole = $('#userrole' + formid).val();
    var userSite = $('#sitelist' + formid).val();

    if (userRole == '') {
        $.notify('Please select the user role.');
        return false;
    }

    // if (userSite == '') {
    //     $.notify('Please select atleast a site to provide access.');
    //     return false;
    // }

    var request = {
        'function': 'FAAJX_saveUserPermission',
        'userkey': userkey,
        'userrole': userRole,
        'userEmail' : userEmail,
        'usersite': userSite,
        'csrfMagicToken': csrfMagicToken
    };
    $.ajax({
        url: "../lib/l-faAjax.php",
        type: 'POST',
        data: request,
        success: function (response) {
            if ($.trim(response) == 'success') {
                $.notify('User permissions updated successfully.');
                setInterval(function () {
                     location.reload();
                }, 1500);
            } else {
                $.notify('Failed to update user permissions.');
            }
        }
    });
}