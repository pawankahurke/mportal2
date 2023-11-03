$(document).ready(function () {
    loadMessageConfiguration();
    getMessageAudit();
    $('#BackTab').hide();
    $('#ExportAuditDetails').hide();
    $('#msgconfig_grid').attr('display','block');
    $('#messageConfig').show();
    $('#BackToMsgConfig').hide();
    $('#msg_add_configurations').show();
    $('#msg_edit_configurations').show();
    $('#msg_delete_configurations').show();
    $('#msg_trigger_configurations').show();
    $('#msg_audit_configurations').show();
    $('#msg_clear_configurations').show();
    $('#ExportAudit').hide();
//    $('#back_configurations').show();
});

function backtoMessageAudit(){
    $('#pageName').html("Message Configuration");
    closePopUp();
    $('#msgconfig_grid').attr('display','block');
    $('#messageConfig').show();
    $('#AuditGrid_wrapper').hide();
    $('#auditGridDetail_wrapper').hide();
    $('#BackTab').hide();
    $('#BackToMsgConfig').show();
    $('#msg_add_configurations').show();
    $('#msg_edit_configurations').show();
    $('#msg_delete_configurations').show();
    $('#msg_trigger_configurations').show();
    $('#msg_audit_configurations').show();
    $('#msg_clear_configurations').show();
    $('#ExportAudit').show();
    $('#ExportAuditDetails').hide();

}

function showMessageAudit(){
    $('#pageName').html("Message Audit");
    closePopUp();
    $('#AuditGrid_wrapper').show();
    $('#auditGridDetail_wrapper').show();
    $('#AuditGrid_grid').attr('display','block');
    $('#leftMsgAudit').show();
    $('#msgconfig_grid').attr('display','none');
    $('#messageConfig').hide();
    $('#auditGridDetail_grid').attr('display','block');
    $('#rightMsgAudit').hide();
    $('#msg_add_configurations').hide();
    $('#msg_edit_configurations').hide();
    $('#msg_delete_configurations').hide();
    $('#msg_trigger_configurations').hide();
    $('#msg_audit_configurations').hide();
    $('#msg_clear_configurations').hide();
    $('#BackTab').show();
    $('#BackToMsgConfig').hide();
    $('#ExportAudit').hide();
    $('#ExportAuditDetails').show();
}

function backtoMessageConfig(type=''){
    closePopUp();
    showMessageAudit();
}

function loadMessageConfiguration() {

    $.ajax({
        url: "../services/messageFunc.php",
        type: "POST",
        dataType: "json",
        data: {'function':'get_MessageConfig', 'csrfMagicToken': csrfMagicToken},
        success: function (gridData) {
           // console.log("success");
            $(".se-pre-con").hide();
            $('#msgconfig_grid').DataTable().destroy();
            messageTable = $('#msgconfig_grid').DataTable({
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
                scrollY: 'calc(100vh - 240px)',
                "pagingType": "full_numbers",
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    search: "_INPUT_",
                    searchPlaceholder: "Search records",
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                initComplete: function(settings, json) {

                },
            });
        },
        error: function (msg) {

        }
    });

    $('#msgconfig_grid').on('click', 'tr', function () {
        var rowID = messageTable.row(this).data();
        var id = rowID['id'];
        var msgName = rowID['name'];
        $("#messageId").val(id);
        $("#messageName").val(msgName);
        messageTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');

    });

    $("#msgconfig_grid_searchbox").keyup(function () {
        messageTable.search(this.value).draw();
        $("#messageGrid tbody").eq(0).html();
    });

}


function addMessage(){
    var title = $('#msgtitle').val().trim();
    var msg = $('#msgtext').val().trim();
    var url = $('#msgURL').val().trim();
    var time = $('#retrytime').val().trim();
    var frequency = $('#retryfreq').val().trim();
    var timeLive = $('#msglife').val().trim();
    var button1 = $('#btntxt').val().trim();
    var button2 = $('#snoozebtntxt').val().trim();
    $('#errormsg').show();
    $('#errormsg').html('');

    if (title == '') {
        $('#err_msgtitle').html("Please provide message name");
        $('#err_msgtitle').fadeOut(3000);
        return false;
    }
    if (msg == '') {
        $('#err_msgtext').html("Please provide message");
        $('#err_msgtext').fadeOut(3000);
        return false;
    }
    if (url == '') {
        $('#err_msgURL').html("Please provide message url");
        $('#err_msgURL').fadeOut(3000);
        return false;
    }
    if (button1 == '') {
        $('#err_btntxt').html("Please provide button text");
        $('#err_btntxt').fadeOut(3000);
        return false;
    }
    if (button2 == '') {
        $('#err_snoozebtntxt').html("Please provide snooze button text");
        $('#err_snoozebtntxt').fadeOut(3000);
        return false;
    }
    if (time == '') {
        $('#err_retrytime').html("Please provide retry interval");
        $('#err_retrytime').fadeOut(3000);
        return false;
    } else {
        var regExp = /^[0-9]+$/;
        if (!regExp.test(time)) {
            $('#err_retrytime').html("Please provide numeric retry interval");
            $('#err_retrytime').fadeOut(3000);
            return false;
        }
        if (time < 1) {
            $('#err_retrytime').html("Minimum retry interval should be 1");
            $('#err_retrytime').fadeOut(3000);
            return false;
        }
        if (time > 40320) {
            $('#err_retrytime').html("Maximum retry interval should be 40320");
            $('#err_retrytime').fadeOut(3000);
            return false;
        }
    }
    if (frequency == '') {
        $('#err_retryfreq').html("Please provide retry frequency");
        $('#err_retryfreq').fadeOut(3000);
        return false;
    } else {
        var regExp = /^[0-9]+$/;
        if (!regExp.test(frequency)) {
            $('#err_retryfreq').html("Please provide numeric retry frequency");
            $('#err_retryfreq').fadeOut(3000);
            return false;
        }
        if (frequency < 1) {
            $('#err_retryfreq').html("Minimum retry frequency should be 1");
            $('#err_retryfreq').fadeOut(3000);
            return false;
        }
        if (frequency > 999) {
            $('#err_retryfreq').html("Maximum retry frequency should be 999");
            $('#err_retryfreq').fadeOut(3000);
            return false;
        }
    }

    if (timeLive == '') {
        $('#err_msglife').html("Please provide message life time");
        $('#err_msglife').fadeOut(3000);
        return false;
    } else {
        var regExp = /^[0-9]+$/;
        if (!regExp.test(timeLive)) {
            $('#err_msglife').html("Please provide numeric message life time");
            $('#err_msglife').fadeOut(3000);
            return false;
        }
        if (timeLive < 1) {
            $('#err_msglife').html("Minimum Message Lifetime should be 1");
            $('#err_msglife').fadeOut(3000);
            return false;
        }
        if (timeLive > 672) {
            $('#err_msglife').html("Maximum Message Lifetime should be 672");
            $('#err_msglife').fadeOut(3000);
            return false;
        }
    }


    $.ajax({
        url: '../services/messageFunc.php',
        type: 'post',
        data: {function: 'add_Message',title: title, msg: msg, url: url, time: time, frequency: frequency, livetime: timeLive, button1: button1, button2: button2, csrfMagicToken: csrfMagicToken},
        success: function (msg) {
            msg = $.trim(msg);
            if (msg === 'DONE') {
                $.notify('Message added successfully.');
                setTimeout(function () {
                    messageConfig();
                }, 2000);

            } else {
                $.notify('Failed to add the message. Please try again');
                setTimeout(function () {
                    location.reload();
                }, 2000);
            }
        }
    });
}

function editMessage() {

    var title = $('#editmsgtitle').val().trim();
    var msg = $('#editmsgtext').val().trim();
    var url = $('#editmsgURL').val().trim();
    var id = $('#messageId').val().trim();
    var time = $('#editretrytime').val().trim();
    var frequency = $('#editretryfreq').val().trim();
    var timeLive = $('#editmsglife').val().trim();
    var button1 = $('#editbtntxt').val().trim();
    var button2 = $('#editsnoozebtntxt').val().trim();

    if (title == '') {
        $('#err_editmsgtitle').html("Please provide message name");
        $('#err_editmsgtitle').fadeOut(3000);
        return false;
    }
    if (msg == '') {
        $('#err_editmsgtext').html("Please provide message");
        $('#err_editmsgtext').fadeOut(3000);
        return false;
    }
    if (url == '') {
        $('#err_editmsgURL').html("Please provide message url");
        $('#err_editmsgURL').fadeOut(3000);
        return false;
    }
    if (button1 == '') {
        $('#err_editbtntxt').html("Please provide button text");
        $('#err_editbtntxt').fadeOut(3000);
        return false;
    }
    if (button2 == '') {
        $('#err_editsnoozebtntxt').html("Please provide snooze button text");
        $('#err_editsnoozebtntxt').fadeOut(3000);
        return false;
    }

    if (time == '') {
        $('#err_editretrytime').html("Please provide retry interval");
        $('#err_editretrytime').fadeOut(3000);
        return false;
    } else {
        var regExp = /^[0-9]+$/;
        if (!regExp.test(time)) {
            $('#err_editretrytime').html("Please provide numeric retry interval");
            $('#err_editretrytime').fadeOut(3000);
            return false;
        }
        if (time < 1) {
            $('#err_editretrytime').html("Minimum retry interval should be 1");
            $('#err_editretrytime').fadeOut(3000);
            return false;
        }
        if (time > 40320) {
            $('#err_editretrytime').html("Maximum retry interval should be 40320");
            $('#err_editretrytime').fadeOut(3000);
            return false;
        }
    }
    if (frequency == '') {
        $('#err_retryfreq').html("Please provide retry frequency");
        $('#err_retryfreq').fadeOut(3000);
        return false;
    } else {
        var regExp = /^[0-9]+$/;
        if (!regExp.test(frequency)) {
            $('#err_retryfreq').html("Please provide numeric retry frequency");
            $('#err_retryfreq').fadeOut(3000);
            return false;
        }
        if (frequency < 1) {
            $('#err_retryfreq').html("Minimum retry frequency should be 1");
            $('#err_retryfreq').fadeOut(3000);
            return false;
        }
        if (frequency > 999) {
            $('#err_retryfreq').html("Maximum retry frequency should be 999");
            $('#err_retryfreq').fadeOut(3000);
            return false;
        }
    }
    if (timeLive == '') {
        $('#err_editmsglife').html("Please provide message life time");
        $('#err_editmsglife').fadeOut(3000);
        return false;
    } else {
        var regExp = /^[0-9]+$/;
        if (!regExp.test(timeLive)) {
            $('#err_editmsglife').html("Please provide numeric message life time");
            $('#err_editmsglife').fadeOut(3000);
            return false;
        }
        if (timeLive < 1) {
            $('#err_editmsglife').html("Minimum Message Lifetime should be 1");
            $('#err_editmsglife').fadeOut(3000);
            return false;
        }
        if (timeLive > 672) {
            $('#err_editmsglife').html("Maximum Message Lifetime should be 672");
            $('#err_editmsglife').fadeOut(3000);
            return false;
        }
    }

    $.ajax({
        url: '../services/messageFunc.php',
        type: 'POST',
        data: {function: 'edit_Message',title: title, msg: msg, url: url, msgId: id, time: time, frequency: frequency, livetime: timeLive, button1: button1, button2: button2,csrfMagicToken: csrfMagicToken},
        success: function (msg) {
            msg = $.trim(msg);
            if (msg === 'DONE') {
                $.notify('Message updated successfully.');
                setTimeout(function () {
                    messageConfig();
                }, 2000);
            } else {
                $.notify('Failed to update the message. Please try again');
                setTimeout(function () {
                    location.reload();
                }, 2000);
            }
        }
    });
}

function getMessageDetails() {

    var msgId = $('#messageId').val();

    $.ajax({
        url: '../services/messageFunc.php',
        type: 'POST',
        data: {function:'get_MessageDetails',msgId: msgId,csrfMagicToken: csrfMagicToken},
        dataType: 'json',
        success: function (data) {
           // console.log(data);
            $('#editmsgtitle').val(data.title);
            $('#editmsgtext').val(data.message);
            $('#editmsgURL').val(data.url);
            $('#editbtntxt').val(data.button1);
            $('#editsnoozebtntxt').val(data.button2);
            $('#editretrytime').val(data.time);
            $('#editretryfreq').val(data.frequency);
            $('#editmsglife').val(data.livetime);
            $(".selectpicker").selectpicker('refresh');
        }
    });

}

function deleteMessage() {
    closePopUp();
    var msgId = $('#messageId').val();
    if(msgId == ''){
        closePopUp();
        $.notify("Please select the record you want to delete");
    }else{
        sweetAlert({
            title: 'Are you sure that you want to continue?',
            text: "You wont be able to recover the message once deleted",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#050d30',
            cancelButtonColor: '#fa0f4b',
            cancelButtonText: "No, cancel it!",
            confirmButtonText: 'Yes, delete it!'
        }).then(function (result) {
        $.ajax({
        url: '../services/messageFunc.php?function=deleteMessage&msgId=' + msgId+"&csrfMagicToken=" + csrfMagicToken,
        type: 'Delete',
        dataType: 'text',
        success: function (msg) {
            closePopUp();
            msg = $.trim(msg);
            if (msg === 'DONE') {
                $.notify('Message deleted successfully.');
                setTimeout(function () {
                            messageConfig();
                }, 2000);
            } else {
                $.notify('Failed to delete the message. Please try again');
                setTimeout(function () {
                    location.reload();
                }, 2000);
            }
        }
    });
    }).catch(function (reason) {
        $(".closebtn").trigger("click");
    });
    }
}

function triggerMessage() {

    var profile = $('#messageName').val();
    var msgId = $('#messageId').val();
    var GroupName = $("#searchValue").val();

    if(msgId == ''){
        closePopUp();
        $.notify("Please select a message to trigger");
    }else{
        $.ajax({
        url: '../communication/communication_ajax.php',
        type: 'POST',
        data: {function:'Add_AndroidJobs',OS: 'Android', Jobtype: 'Message', ProfileName: profile, Dart: '', variable: msgId, GroupName: GroupName,csrfMagicToken: csrfMagicToken},
        dataType: 'text',
        success: function (msg) {
                sweetAlert.close();
                $.notify('Message Triggered');
                setTimeout(function () {
                    messageConfig();
                }, 2000);
        },
        beforeSend: function(){
                   closePopUp();
                   sweetAlert({
                               title: 'Please Wait',
                               text: "Data is taking some time to load",
                               type: 'info',
   //                            showCancelButton: false,
   //                            confirmButtonColor: '#3085d6'
                           });
        },
        complete: function(){
        }
       });
    }


}


function getMessageAudit() {
 var pack = "";
 var bid = "";
 $.ajax({
        url: "../services/messageFunc.php",
        type: "POST",
        data: {'function':get_MessageAudit, 'csrfMagicToken':csrfMagicToken},
        dataType: 'json',
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#AuditGrid').DataTable().destroy();
            auditTable = $('#AuditGrid').DataTable({
                scrollCollapse: true,
                paging: true,
                searching: false,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                responsive: true,
                stateSave: true,
                scrollY: 'calc(100vh - 240px)',
                "pagingType": "full_numbers",
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    search: "_INPUT_",
                    searchPlaceholder: "Search records",
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                initComplete: function(settings, json) {

                },
            });
        },
        error:function(error){
            console.log("error");
        }
    });

    $('#AuditGrid').on('click', 'tr', function () {
        var rowID = auditTable.row(this).data();
        if(rowID != undefined) {
        bid = rowID[4];
        pack = rowID[3];
        $('#selected').val(bid);
        $('#selectedPackage').val(rowID[3]);
        auditTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    } else {
         auditGridDetail('');
    }
    });

    $('#AuditGrid').on('dblclick', 'tr', function () {
        var rowID = auditTable.row(this).data();
        if(rowID != undefined) {
        bid = rowID[4];
        pack = rowID[3];
        $('#selected').val(bid);
        $('#selectedPackage').val(rowID[3]);
        auditGridDetail(pack);
        auditTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    } else {
         auditGridDetail('');
    }

    });


}

function auditGridDetail(pack) {
    $('#ExportAuditDetails').hide();
    $('#BackTab').hide();
    $('#rightMsgAudit').show();
    $('#BackToMsgConfig').show();
    $('#ExportAudit').show();
    $('#AuditGrid_wrapper').hide();
    $("#se-pre-con-loader").show();
    $('.move-left-right .bottomtable').remove();
    var bid = $('#selected').val();
    var auditSearch = $("#valueSearch").val();


    $("#auditGridDetail").dataTable().fnDestroy();
    detailTable = $('#auditGridDetail').DataTable({
                paging: true,
                ordering: true,
                bAutoWidth: false,
                select: false,
                responsive: true,
                stateSave: true,
                scrollY: 'calc(100vh - 240px)',
                "pagingType": "full_numbers",
        scrollCollapse: true,
        autoWidth: true,
        searching: false,
        processing: false,
        serverSide: true,
        ajax: {
            url : "../services/messageFunc.php",
            type: "POST",
            data: {function:'get_MessageAuditDetail',pack:pack,bid:bid,audit:auditSearch,type:'display',csrfMagicToken: csrfMagicToken}
        },
        drawCallback: function (settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
            $("#se-pre-con-loader").hide();
        },
        columns: [
            {"data": "SelectionType"},
            {"data": "MachineTag"},
            {"data": "Status"}
        ],
        language: {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ordering: false,
        select: false,
        bInfo: false,
        responsive: true,
        dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>'
    });

    $('#auditGridDetail tbody').on('click', 'tr', function () {

        detailTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');

    });
    $(".bottompager").each(function () {
        $(this).append($(this).find(".bottomtable"));
    });
}


$("#addMessage").on('hidden.bs.modal', function () {

    $('#msgTitle').val('');
    $('#message').val('');
    $('#url').val('');
    $('#button1').val('');
    $('#button2').val('');
    $('#messageAdd').html('');
    $('#errormsg').html('');
    $('#addMessage .form-control').val('');
    $(".selectpicker").selectpicker("refresh");
});


function clearMessage() {

    var searchValue = $("#searchValue").val();
    var messageName = $('#messageName').val();
    var msg = 'CLEAR_FCM_QUEUE:1';

      $.ajax({
        url: '../communication/communication_ajax.php',
        type: 'POST',
        data: {
          function: 'Add_AndroidJobs',
          OS: 'Android',
          Jobtype: 'ClearMessage',
          ProfileName: 'ClearMsg',
          Dart: messageName, variable: msg,
          GroupName: searchValue,
          csrfMagicToken: csrfMagicToken
        },
        dataType: 'text',
        success: function (msg) {
            $.notify('Message queue has been cleared');
            setTimeout(function() {
                messageConfig();
            },3000);
        }
    });
}

$('#ExportAudit').click(function(){
    //rightContainerSlideOn('rsc-add-container34');
    exportAlertAudit();
});

$('#ExportAuditDetails').click(function(){
    rightContainerSlideOn('rsc-add-container34');
});

function exportAlertAudit() {
    var bid = $('#selected').val();
    var pack = $('#selectedPackage').val();
    window.location.href = '../services/messageFunc.php?function=getMessageAuditDetail&export=1&pack='+pack+'&bid='+bid+'&type='+'export'+'&csrfMagicToken=' + csrfMagicToken;
    $.notify("Data Successfully Exported");
    closePopUp();
}

function exportAlertAuditDetails(){
    var from = $('#datefrom').val();
    var to = $('#dateto').val();
    var bid = $('#selected').val();
    var pack = $('#selectedPackage').val();
    window.location.href = '../services/messageFunc.php?function=getMessageAuditDetailTime&export=1&bid='+bid+'&from='+from+'&to='+to+'&type='+'display'+'&csrfMagicToken=' + csrfMagicToken;
    $.notify("Please wait, data will be Exported in sometime");
    closePopUp();
}

function addmessageConfig(){
    rightContainerSlideOn('addmsgconfig');
}

function editmessageConfig(){
    var msgid = $('#messageId').val();
    if(msgid == ''){
        closePopUp();
        $.notify("Please select a record to Edit");
    }else{
    rightContainerSlideOn('editmsgconfig');
    getMessageDetails();
}

}
