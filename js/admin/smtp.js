$(document).ready(function () {
    getDetails();
});

function getDetails() {

    $.ajax({
        url: "../lib/l-smtp.php",
        type: "POST",
        data: {'function': 'SMTP_getTable_Details', 'csrfMagicToken': csrfMagicToken},
        dataType: "json",
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#smtpTable').DataTable().destroy();
            smtpTable = $('#smtpTable').DataTable({
                scrollY: 'calc(100vh - 240px)',
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: true,
                select: false,
                bInfo: false,
                responsive: true,
                stateSave: true,
                "pagingType": "full_numbers",
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                order: [[0, "desc"]],
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "language": {
                    "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    search: "_INPUT_",
                    searchPlaceholder: "Search records",
                },
                columnDefs: [{className: "checkbox-btn", "targets": [0]},
                    {"type": "date", "targets": [1, 2]}],
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                initComplete: function (settings, json) {
                },

            });
            $('.dataTables_filter input').addClass('form-control');
            $('.tableloader').hide();
        }
    });

    $('#smtpTable').on('click', 'tr', function () {
        var rowID = smtpTable.row(this).data();
        var selected = rowID[3];
        $('#smtp_selected').val(selected);
        smtpTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });

    $('#smtpTable').on('dblclick', 'tr', function () {
        getdetails();
    });

}

$('.closebtn').click(function () {
    enableFields();
    $('#name_err').html('');
    $('#port_err').html('');
    $('#fromemail_err').html('');
    /*$("#required_name").html('');
     $("#required_host").html('');
     $("#required_port").html('');
     $("#required_username").html('');
     $("#required_pwd").html('');
     $("#required_fromemail").html('');*/
    $('#name').val('');
    $('#host').val('');
    $('#port').val('');
    $('#username').val('');
    $('#pwd').val('');
    $('#fromemail').val('');
});

function createsmtp() {

    var name = $('#name').val();
    var host = $('#host').val();
    var port = $('#port').val();
    var username = $('#username').val();
    var password = $('#pwd').val();
    var security = 'SSL';
    var fromAdd = $('#fromemail').val();

    if ($('#tls').is(":checked")) {
        security = "TLS";
    } else if ($('#ssl').is(":checked")) {
        security = "SSL";
    } else {
        security = "None";
    }

    if (security !== 'None') {
        if (name.length > 24 || name.length > 24) {
            $('#name_err').css("color", "red").html('Name should not be greater than 24 characters');
            return false;
        }
        if (name == '') {
            $("#required_name").css("color", "red").html(" required");
            return false;
        }
        if (!validate_Name(name)) {
            $('#name_err').css("color", "red").html('No special characters or numeric allowed in name');
            return false;
        }
        if (host == '') {
            $("#required_host").css("color", "red").html(" required");
            return false;
        }
        if (port == '') {
            $("#required_port").css("color", "red").html(" required");
            return false;
        }
        if (!validate_Number(port)) {
            $('#port_err').css("color", "red").html('Only numeric allowed');
            return false;
        }
        /*if (username == '') {
         $("#required_username").css("color", "red").html(" required");
         return false;
         }
         if (password == '') {
         $("#required_pwd").css("color", "red").html(" required");
         return false;
         }*/
        if (fromAdd == '') {
            $("#required_fromemail").css("color", "red").html(" required");
            return false;
        }
        if (!validate_Email(fromAdd)) {
            $('#fromemail_err').css("color", "red").html('Enter valid email');
            return false;
        }
    }
    var mdata = {
        'function': 'SMTP_add_Config',
        'name': name,
        'host': host,
        'port': port,
        'username': username,
        'pwd': password,
        'security': security,
        'from': fromAdd,
        'csrfMagicToken': csrfMagicToken
    };

    $.ajax({
        url: '../lib/l-smtp.php',
        type: 'post',
        data: mdata,
        dataType: 'text',
        success: function (data) {
            if ($.trim(data) === 'success') {
                $.notify("Configuration has been successfully added");
                rightContainerSlideClose('rsc-addsmtp-container');
                location.reload();
                $('#addsmtpconfig').hide();
            } else {
                $.notify("Failed to add configuration. Please try again");
                rightContainerSlideClose('rsc-addsmtp-container');
                location.reload();
                $('#addsmtpconfig').hide();
            }
        }

    });
}

function sendmail() {
    var email = $('#toemail').val();
    if (email == '') {
        $.notify("Please enter an email address");
        return false;
    }

    $.ajax({
        url: '../lib/l-smtp.php',
        type: 'POST',
        data: {'function': 'SMTP_send_Mail', 'name': email, 'csrfMagicToken': csrfMagicToken},
        dataType: 'text',
        success: function (data) {
            if ($.trim(data) === 'success') {
                $.notify("Email has been sent successfully");
                rightContainerSlideClose('rsc-sendmail');
            } else {
                $.notify("Failed to send the email. Please try again");
                rightContainerSlideClose('rsc-sendmail');
            }

            setTimeout(function () {
                $("#rsc-sendmail").modal("hide");
                //location.href = 'smtp.php';
            }, 3200);
        }
    });



}

function getdetails() {
    var id = $('#smtp_selected').val();
    $.ajax({
        url: '../lib/l-smtp.php',
        type: 'post',
        data: {'function': 'SMTP_get_Details', 'id': id, 'csrfMagicToken': csrfMagicToken},
        dataType: 'json',
        success: function (data) {
            if ($.trim(data.status) === 'success') {
                disableFields();
                $('#editname').val(data.name);
                $('#edithost').val(data.host);
                $('#editport').val(data.port);
                $('#editusername').val(data.username);
                $('#editpwd').val(data.pwd);
                $('#editfromemail').val(data.from);
                if (data.security === 'TLS') {
//                   $('#editssl').attr('checked',true);
                    $('#edittls').prop("checked", true);
                } else if (data.security === 'SSL') {
                    $('#editssl').prop('checked', true);
                } else {
                    $('#editnone').prop('checked', true);
                }
                rightContainerSlideOn('rsc-editsmtp-container');

            }
        }
    });
}

function editsmtp() {

    var name = $('#editname').val();
    var host = $('#edithost').val();
    var port = $('#editport').val();
    var username = $('#editusername').val();
    var password = $('#editpwd').val();
    var security = 'SSL';
    var fromAdd = $('#editfromemail').val();

    if ($('#edittls').is(":checked")) {
        security = "TLS";
    } else if ($('#editssl').is(":checked")) {
        security = "SSL";
    } else {
        security = "None";
    }

    if (security !== 'None') {
        if (name.length > 24 || name.length > 24) {
            $('#editname_err').css("color", "red").html('Name should not be greater than 24 characters');
            return false;
        }
        if (name == '') {
            $("#required_editname").css("color", "red").html(" required");
            return false;
        }
        if (!validate_Name(name)) {
            $('#editname_err').css("color", "red").html('No special characters or numeric allowed in  name');
            return false;
        }
        if (host == '') {
            $("#required_edithost").css("color", "red").html(" required");
            return false;
        }
        if (port == '') {
            $("#required_editport").css("color", "red").html(" required");
            return false;
        }
        if (!validate_Number(port)) {
            $('#editport_err').css("color", "red").html('Only numeric allowed');
            return false;
        }
        if (username == '') {
            $("#required_editusername").css("color", "red").html(" required");
            return false;
        }
        if (password == '') {
            $("#required_editpwd").css("color", "red").html(" required");
            return false;
        }
        if (fromAdd == '') {
            $("#required_editfromemail").css("color", "red").html(" required");
            return false;
        }
        if (!validate_Email(fromAdd)) {
            $('#editfromemail_err').css("color", "red").html('Enter valid email');
            return false;
        }
    }

    $.ajax({
        url: '../lib/l-smtp.php',
        type: 'post',
        dataType: 'text',
        data: {"function": SMTP_edit_Config, "name=": name, "host":host, "port":port, "username=": username, "pwd": password, "security=": security, "from":fromAdd, "csrfMagicToken":csrfMagicToken},
        success: function (data) {
            if ($.trim(data) === 'success') {
                $.notify("Configuration has been successfully edited");
                rightContainerSlideClose('rsc-editsmtp-container');
                getDetails();
                location.reload();
                $('#addsmtpconfig').hide();

            } else {
                $.notify("Failed to edit the configuration. Please try again");
                rightContainerSlideClose('rsc-editsmtp-container');
                getDetails();
                location.reload();
                $('#addsmtpconfig').hide();
            }
        }

    });

}

$('#addsmtpconfig').on('click', function () {
    enableFields();
    $('#pwd').val('');
    $('#username').val('');
});

