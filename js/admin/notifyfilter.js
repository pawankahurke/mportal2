//Notify JS for ADMIN -> Notification
//Get_NotificationDT();
$(document).ready(function (){
    $('#enableDisNoty').html("<span class='enableTxt'>Enable/Disable</span>");
});

function Get_NotificationDT() {
    $.ajax({
        type: "GET",
        url: "../lib/l-ajax.php",
        data: "function=AJAX_GetNotifyGridData&srch=1&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#notifyTable').DataTable().destroy();
            notifyTable = $('#notifyTable').DataTable({
                scrollY: jQuery('#notifyTable').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                responsive: true,
                stateSave: true,
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }

            });
        },
        error: function (msg) {

        }
    });

    $('#notifyTable').on('click', 'tr', function () {
        var rowID = notifyTable.row(this).data();
        var selected = rowID[8];
        var selected_user = rowID[1];
        var state = rowID[5];
        $("#selected").val(selected);
        $('#selected_user').val(selected_user);
        $('#state').val(state);
        notifyTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        if(state === 'Enabled'){
            $('#enableDisNoty').html("<span class='enableTxt'>Disable</span>");
        }else{
            $('#enableDisNoty').html("<span class='enableTxt'>Enable</span>");
        }
    });

    $("#notifytools_searchbox").keyup(function () {
        notifyTable.search(this.value).draw();
    });

}

//Add new Notification Popup functionality
function addNewNotify() {
    window.location.href = '../admin/notify.php?act=add';
}


var actionType = $("#actionType").val();
if (actionType) {
    addNotifyPage();
}

function addNotifyPage() {
    var actionType = $("#actionType").val();
    var selected = $("#selected").val();
    var id;
    if (selected) {
        id = selected;
    } else {
        id = "none";
    }
    $.ajax({
        url: "../lib/l-ajax.php",
        data: "function=AJAX_GetNotifAddFields&type=" + actionType + "&selected=" + selected + "&csrfMagicToken=" + csrfMagicToken,
        dataType: "json",
        success: function (getField) {
            $("#search_id").html(getField.eventfilterlist);
            $("#g_include").html(getField.incgroup);
            $("#g_exclude").html(getField.excgroup);
            $(".selectpicker").selectpicker('refresh');
        }
    });
}

function formSubmitValidate() {
    var actionType = $("#actionType").val();
    var name = $.trim($("#name").val());
    if (name == '') {
        $('#validateError').text("");
        $("#validateError").show();
        $("#validateError").html("<span>Name is mandatory.</span>");
        setTimeout(function () {
            $("#validateError").fadeOut();
        }, 3000);
        return false;
    }
    if (name !== '') {
        var regx = /[^a-zA-Z0-9\_\s]/;
        if (regx.test(name)) {
            $('#validateError').text("");
            $("#validateError").show();
            $("#validateError").html("<span>Only alphanumeric and underscore allowed for name field.</span>");
            setTimeout(function () {
                $("#validateError").fadeOut(3000);
            }, 2000);
            return false;
        }
    }
    if ($('#email').val() === '1' && $('#emaillist').val() === '') {
        $('#validateError').text("");
        $("#validateError").show();
        $("#validateError").html("<span>Please enter E-mail recipients</span>");
        setTimeout(function () {
            $("#validateError").fadeOut();
        }, 3000);
        return false;
    } else if ($('#emaillist').val() !== '') {
        var emailId = $('#emaillist').val();
        var email = emailId.split(',');
        var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;

        for (var i = 0; i < email.length; i++) {
            if (!filter.test(email[i])) {
                $('#validateError').text("");
                $("#validateError").show();
                $("#validateError").html("<span>Please enter valid E-mail recipients</span>");
                setTimeout(function () {
                    $("#validateError").fadeOut();
                }, 3000);
                return false;
            }
        }
    }

    var url = "../lib/l-ajax.php";
    if (actionType === "add" || actionType === "copy") {
        $.ajax({
            url: "../lib/l-ajax.php?function=AJAX_SubmitNotifyFilter&val=1&name=" + name + "&type=" + actionType + "&csrfMagicToken=" + csrfMagicToken,
            type: 'POST',
            dataType: 'text',
            success: function (check) {
                if (check === "available") {
                    $("#validateError").text("");
                    $("#validateError").show();
                    $("#validateError").html("<span>Name already exists.</span>");
                    setTimeout(function () {
                        $("#validateError").fadeOut();
                    }, 3000);
                } else {
                    var formData = $("#notifyfilter_form").serialize();
                    $.ajax({
                        url: "../lib/l-ajax.php?function=AJAX_SubmitNotifyFilter&val=0&type=" + actionType + "&csrfMagicToken=" + csrfMagicToken,
                        data: formData,
                        type: 'POST',
                        dataType: 'text',
                        success: function (data) {
                            if (actionType == "add") {
                                $("#validateSuccess").html("<span>Notification is added successfully.</span>");
                            } else if (actionType == "copy") {
                                $("#validateSuccess").html("<span>Notification is copied successfully.</span>");
                            } else if (actionType == "edit") {
                                $("#validateSuccess").html("<span>Notification is updated successfully.</span>");
                            }

                            setTimeout(function () {
                                window.location.href = '../admin/notifyfilter.php';
                            }, 2000);
                        }
                    });
                }
            }
        });
    } else if (actionType === "edit") {
        var selected = $("#selected").val();
        $.ajax({
            url: url,
            data: "function=AJAX_SubmitNotifyFilter&val=1&name=" + name + "&type=" + actionType + "&sel=" + selected + "&csrfMagicToken=" + csrfMagicToken,
            type: 'POST',
            dataType: 'text',
            success: function (check) {
                if (check === "available") {
                    $("#validateError").text("");
                    $("#validateError").show();
                    $("#validateError").html("<span>Name already exists.</span>");
                    setTimeout(function () {
                        $("#validateError").fadeOut();
                    }, 3000);
                } else {
                    var selected = $("#selected").val();
                    var formData = $("#notifyfilter_form").serialize();
                    $.ajax({
                        url: "../lib/l-ajax.php?function=AJAX_SubmitNotifyFilter&val=0&type=" + actionType + "&sel=" + selected + "&csrfMagicToken=" + csrfMagicToken,
                        data: formData,
                        type: 'POST',
                        dataType: 'text',
                        success: function (data) {
                            $("#validateSuccess").html("<span>Notification is updated successfully.</span>");
                            setTimeout(function () {
                                window.location.href = '../admin/notifyfilter.php';
                            }, 2000);
                        }
                    });
                }
            }
        });
    }

}

function backToNotifyFilterPage() {
    window.location.href = "../admin/notifyfilter.php";
}

//$('#autotask, #email_per_site, #email_sender, #email_footer, #global, #skip_owner').click(function () {
$('#global').click(function () {
    if ($(this).is(':checked')) {
        $(this).val(1);
    } else {
        $(this).val(0);
    }
});

/*function detailCheckAppendFn() {
    var temp = "";
 $('.report_detail').each(function() {
        if ($(this).is(':checked')) {
            temp += $(this).val() + ':';
        }
    });
    $("#rprt_dtl").val(":" + temp);
 }*/

function deleteNotification() {
    var selected = $("#selected").val();
    $.ajax({
        url: '../lib/l-ajax.php',
        data: 'function=AJAX_ADMN_DeleteNotify&id=' + selected + "&csrfMagicToken=" + csrfMagicToken,
        dataType: 'text',
        type: 'POST',
        success: function (data) {
            if (data) {
                $("#selected").val("");
                Get_NotificationDT();
            }
        }
    });
}

function updateSolution() {
    var selected = $("#selected").val();
    $.ajax({
        url: '../lib/l-ajax.php',
        type: 'post',
        data: 'function=AJAX_ADMN_NotifyL3Profiles&id=' + selected + "&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        success: function (data) {
            if (data.length > 0) {
                $("#name").val(data[0][0]);
                $("#solution").html(data[0][1]);
                $("#checkField").html(data[0][2]);
                $(".selectpicker").selectpicker('refresh');
            }
        }
    });
}

function changeCheckVal() {
    var cVal = $("#autoenable").val();
    if (cVal === "1") {
        $("#autoenable").val("0");
    } else if (cVal === "0") {
        $("#autoenable").val("1");
    }
}

function updateSolutionData() {
    var formData = $("#updateSoln_form").serialize();
    var selected = $("#selected").val();
    var opt = $('#solution').val();
     var profile = $("#solution option:selected").attr("id");

    if(opt === '####') {
        $("#errorMsg").show();
        $("#errorMsg").html();
        $("#errorMsg").html("<span>Please select atleast one solution</span>");
            setTimeout(function () {
               $("#errorMsg").fadeOut(); 
        }, 2000);
    } else {
    $.ajax({
        url: '../lib/l-ajax.php?function=AJAX_ADMN_NotyUpdateSolution&id=' + selected+'&profileId='+profile + "&csrfMagicToken=" + csrfMagicToken,
        type: 'post',
        data: formData,
        dataType: 'text',
        success: function (data) {
            $("#ajaxmessage").text("");
                if ($.trim(data) === "success") {
                $("#ajaxmessage").html("<span>Notification updated successfully</span>");
                setTimeout(function () {
                    $("#updateSolution").modal("hide");
                        window.location.href = '../admin/notifyfilter.php';
                }, 2000);
            }
        }
    });
}
}

function notifyDetails(id) {
    $(".clearvalues").val("");
    $.ajax({
        url: '../lib/l-ajax.php',
        data: 'function=AJAX_ADMN_GetNotifDetails&id=' + id + "&csrfMagicToken=" + csrfMagicToken,
        type: 'post',
        dataType: 'json',
        success: function (data) {
//            console.log(data[0]);
            $("#noty_detail").modal('show');
            $("#notifname").text(data[0]['name']);
            $("#owner").val(data[0]['username']);
            //$("#skipowner").val(data[0]['skip_owner']);
            $("#type").val(data[0]['ntype']);
            //$("#includefooter").val(data[0]['email_footer']);
            //$("#usesiteemail").val(data[0]['email_per_site']);
            $("#priority").val(data[0]['priority']);
            $("#restricted").val(data[0]['name']);
            $("#exclude").val(data[0]['group_exclude']);
            $("#search").val(data[0]['search_id']);
            $("#email").val(data[0]['email']);
            //$("#links").val(data[0]['links']);
            $("#eventfilter").val(data[0]['name']);
            $("#config").val(data[0]['config']);
            $("#scope").val(data[0]['global']);
            $("#state").val(data[0]['enabled']);
            $("#console").val(data[0]['console']);
            //$("#emailpersite").val(data[0]['email_per_site']);
            $("#emailnoticonfiglink").val(data[0]['email_edit_notification_link']);
            $("#schedule").val(data[0]['name']);
            $("#threshold").val(data[0]['threshold']);
            $("#include").val(data[0]['group_include']);
            $("#created").val(data[0]['created']);
            $("#lastrun").val(data[0]['last_run']);
            $("#modified").val(data[0]['modified']);
            $("#nextrun").val(data[0]['next_run']);
            //$("#footertext").val(data[0]['email_footer_txt']);

        }
    });
}


//Popup validation start
function selectConfirm(data_target_id) {

    $("#normError").hide();
    $("#mainError").show();

    var selected = $("#selected").val();

    if (selected === '') {

        $("#warning").modal('show');

    } else {

        if (data_target_id === 'update') {
            $("#updateSolution").modal('show');
            updateSolution();
        } else if (data_target_id === 'edit') {

            window.location.href = '../admin/notify.php?act=edit&id=' + selected;

        } else if (data_target_id === 'copy') {

            window.location.href = '../admin/notify.php?act=copy&id=' + selected;

        } else if (data_target_id === 'delete') {

            $("#deleteNotify").modal('show');

        }
    }

    return true;

}

function enableDisUpdate(){
    $('#EnaDismainError').html('');
    $('#popup_titleMsg').html('');
    var selected_id = $("#selected").val();
    var selected_username = $('#selected_user').val();
    var target_id = $('#state').val();
    
    if(selected_id !== ''){
        if(userName === selected_username){
            $.ajax({
                url: '../lib/l-ajax.php?function=ADMN_NotyEnabelDisable&id='+ selected_id +'&targetId='+target_id + "&csrfMagicToken=" + csrfMagicToken,
                type: 'POST',
                success: function (data) {
                    if ($.trim(data) === "success") {
                        $('#enableDisNoty').html("<span class='enableTxt'>Enable/Disable</span>");
                        $('#popup_titleMsg').html('<label style="color: #48b2e4;text-align: center;font-size: 20px;">&nbsp;Success</label>');
                        $('#EnaDismainError').html('<label style="color: black;margin-left: 28px;text-align: center;">Successfully state updated </label>');
                        $("#EnaDiswarning").modal('show');
                        setTimeout(function () {
                            $("#EnaDiswarning").modal('hide');
                            Get_NotificationDT();
                        }, 3000);
                    }else{
                        $('#enableDisNoty').html("<span class='enableTxt'>Enable/Disable</span>");
                        $('#popup_titleMsg').html('<label style="color: red;text-align: center;font-size: 20px;">&nbsp;Failed</label>');
                        $('#EnaDismainError').html('<label style="color: black;margin-left: 28px;text-align: center;">State updation failed </label>');
                        $("#EnaDiswarning").modal('show');
                        setTimeout(function () {
                            $("#EnaDiswarning").modal('hide');
                        }, 3000);
                    }
                }
            });
        }else{
            $('#popup_titleMsg').html('<label style="color: red;text-align: center;font-size: 20px;">&nbsp;Alert</label>');
            $('#EnaDismainError').html('You are not authorised to perform this action to this Notification');
            $("#EnaDiswarning").modal('show');
        }
    }else{
        $('#popup_titleMsg').html('<label style="color: red;text-align: center;font-size: 20px;">&nbsp;Alert</label>');
        $('#EnaDismainError').html('Please select a Notification to perform this action.');
        $("#EnaDiswarning").modal('show');
    }
}