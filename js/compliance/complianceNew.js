priority = '';
nfstatus = '';
$(document).ready(function () {
    compliance_datatable();
    // getTopnotification();
    // $('#absoLoader').show();
});

$('#topCheckBox').click(function () {
    if ($(this).is(':checked')) {
        $("input:checkbox[name*=checkNoc]").each(function () {
            $(this).prop('checked', true);
        });
    } else {
        $("input:checkbox[name*=checkNoc]").each(function () {
            $(this).prop('checked', false);
        });
    }
});

function showRightPane(){
    rightMenuFunctionality();
}

function getTopnotification() {

    $.ajax({
        type: "POST",
        url: "../compliance/compliance_func.php",
        dataType: 'text',
        data: {
            'function': 'getTopNotification',
            'csrfMagicToken': csrfMagicToken
        },
        success: function (msg) {
            $('#alert_notify').html('');
            $('#alert_notify').append(msg);

        },
        error: function (msg) {

        }
    });

}
var notifName;
function compliance_datatable(item = '', category = '') {
    $('.sortArrow').addClass('headerDown');
    $('.sortArrow').removeClass('headerUp');
    $('#absoLoader').show();
    $.ajax({
        type: "POST",
        url: "compliance_func.php",
        data: {
            "function": 'get_compliance',
            'csrfMagicToken': csrfMagicToken,
            'item' : item,
            'category' : category
        },
        success: function (gridData) {
            $('#absoLoader').hide();
            if (gridData == '##') {
            }
                var dataList = gridData.split('##');
                if (dataList.length > 0) {
                    $("#notificationList").html('');
                    $("#notificationList").html(dataList[0]);
                    notifName = dataList[1];
                    complianceDtl_datatable(item,category,dataList[1], 'mainactive', nfstatus);
                }
        },
        error: function (msg) {

        }
    });
}

function complianceDtl_datatable(item= '', category= '', name = '', reflag = '', status = '', nextPage = 1, notifSearch = '', key = '', sort = '') {
    //   alert(name);
    $('#absoLoader').show();
    if (status == '') {
        status = nfstatus;
    }
    if (name == '') {
        name = $('#notiname').val();
    }

  if (key === 'itemtype' || key === 'category') {
    $('.equalHeight').show();
    $('#absoLoader').hide();
    return;
  }

  checkAndUpdateActiveSortElement(key, sort);

    //  alert(notifName);
    //  console.log('ooo'+status);
    $('#notiname').val(name);
    $('#topCheckBox').prop('checked', false);
    if (reflag != 'mainactive') {
        $('.notif-padding').each(function () {
            $(this).removeClass('active');
        });
        $(reflag).addClass('active');
    }
    selNid = name;
    if (name.length > 25) {
        $('#activeNotif').html(name.substring(0, 25) + '...').attr('title', name);
    } else {
        $('#activeNotif').html(name);
    }

    notifSearch = $('#notifSearch').val();
    if (typeof notifSearch === 'undefined') {
        notifSearch = '';
    }
    var dat = {
        "function": 'get_complianceDtl',
        'name': name,
        'csrfMagicToken': csrfMagicToken,
        'limitCount': $('#notifyDtl_length :selected').val(),
        'nextPage': nextPage,
        'notifSearch': notifSearch,
        'item': item,
        'category' : category,
        'order' : key,
        'sort' :sort
    };
    var gridData = {};
    $.ajax({
        url: "compliance_func.php",
        type: "POST",
        dataType: "json",
        data: dat,
        success: function (gridData) {
        //    console.log(gridData.html);
            $('#absoLoader').hide();
            $(".se-pre-con").hide();
            $('#complDtl').DataTable().destroy();
            $('#complDtl tbody').empty();
            table3 = $('#complDtl').DataTable({
                scrollY: 'calc(100vh - 240px)',
                scrollCollapse: true,
                paging: false,
                searching: false,
                bFilter: false,
                aaSorting: [],
                ordering: false,
                aaData: gridData.html,
                bAutoWidth: true,
                select: false,
                bInfo: false,
                responsive: true,
                stateSave: true,
                processing: true,
                "pagingType": "full_numbers",
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                // order: [[2, "asc"]],
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                //                "lengthChange": false,
                "language": {
                    "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    search: "_INPUT_",
                    searchPlaceholder: "Search records"
                },
                "columnDefs": [
                    {
                        "targets": "_all",
                        "orderable": false
                    }
                ],
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                initComplete: function (settings, json) {
                    $('.equalHeight').show();
                    $('#absoLoader').hide();
                    $("th").removeClass('sorting_desc');
                    $("th").removeClass('sorting_asc');

                    $('#sortKey0').html('<i class="fa fa-caret-down cursorPointer direction" id = "machine1" onclick = "addActiveSort(\'machine\', \'asc\'); complianceDtl_datatable(\'\',\'\',\'' + name + '\',\'mainactive\',\'\',1,\'\',\'machine\', \'asc\');sortingIconColor(\'machine1\')" style="font-size:18px"></i>'
                        +'<i class="fa fa-caret-up cursorPointer direction" id = "machine2"  onclick = "addActiveSort(\'machine\', \'desc\'); complianceDtl_datatable(\'\',\'\',\'' + name + '\',\'mainactive\',\'\',1,\'\',\'machine\', \'desc\');sortingIconColor(\'machine2\')" style="font-size:18px"></i>');

                    $('#sortKey1').html('<i class="fa fa-caret-down cursorPointer direction" id = "site1" onclick = "addActiveSort(\'site\', \'asc\'); complianceDtl_datatable(\'\',\'\',\'' + name + '\',\'mainactive\',\'\',1,\'\',\'site\', \'asc\');sortingIconColor(\'site1\')" style="font-size:18px"></i>'
                        +'<i class="fa fa-caret-up cursorPointer direction" id = "site2"  onclick = "addActiveSort(\'site\', \'desc\'); complianceDtl_datatable(\'\',\'\',\'' + name + '\',\'mainactive\',\'\',1,\'\',\'site\', \'desc\');sortingIconColor(\'site2\')" style="font-size:18px"></i>');

                    $('#sortKey2').html('<i class="fa fa-caret-down cursorPointer direction" id = "itemtype1" onclick = "addActiveSort(\'itemtype\', \'asc\'); complianceDtl_datatable(\'\',\'\',\'' + name + '\',\'mainactive\',\'\',1,\'\',\'itemtype\', \'asc\');sortingIconColor(\'itemtype1\')" style="font-size:18px"></i>'
                        +'<i class="fa fa-caret-up cursorPointer direction" id = "itemtype2"  onclick = "addActiveSort(\'itemtype\', \'desc\'); complianceDtl_datatable(\'\',\'\',\'' + name + '\',\'mainactive\',\'\',1,\'\',\'itemtype\', \'desc\');sortingIconColor(\'itemtype1\')" style="font-size:18px"></i>');

                    $('#sortKey3').html('<i class="fa fa-caret-down cursorPointer direction" id = "category1" onclick = "addActiveSort(\'category\', \'asc\'); complianceDtl_datatable(\'\',\'\',\'' + name + '\',\'mainactive\',\'\',1,\'\',\'category\', \'asc\');sortingIconColor(\'category1\')" style="font-size:18px"></i>'
                        +'<i class="fa fa-caret-up cursorPointer direction" id = "category2"  onclick = "addActiveSort(\'category\', \'desc\'); complianceDtl_datatable(\'\',\'\',\'' + name + '\',\'mainactive\',\'\',1,\'\',\'category\', \'desc\');sortingIconColor(\'category2\')" style="font-size:18px"></i>');

                    $('#sortKey4').html('<i class="fa fa-caret-down cursorPointer direction" id = "servertime1" onclick = "addActiveSort(\'servertime\', \'asc\'); complianceDtl_datatable(\'\',\'\',\'' + name + '\',\'mainactive\',\'\',1,\'\',\'servertime\', \'asc\');sortingIconColor(\'servertime1\')" style="font-size:18px"></i>'
                        +'<i class="fa fa-caret-up cursorPointer direction" id = "servertime2"  onclick = "addActiveSort(\'servertime\', \'desc\'); complianceDtl_datatable(\'\',\'\',\'' + name + '\',\'mainactive\',\'\',1,\'\',\'servertime\', \'desc\');sortingIconColor(\'servertime2\')" style="font-size:18px"></i>');

                    $('#sortKey5').html('<i class="fa fa-caret-down cursorPointer direction" id = "count1" onclick = "addActiveSort(\'count\', \'asc\'); complianceDtl_datatable(\'\',\'\',\'' + name + '\',\'mainactive\',\'\',1,\'\',\'count\', \'asc\');sortingIconColor(\'count1\')" style="font-size:18px"></i>'
                        +'<i class="fa fa-caret-up cursorPointer direction" id = "count2"  onclick = "addActiveSort(\'count\', \'desc\'); complianceDtl_datatable(\'\',\'\',\'' + name + '\',\'\',\'mainactive\',1,\'\',\'count\', \'desc\');sortingIconColor(\'count2\')" style="font-size:18px"></i>');

                    $('#sortKey6').html('<i class="fa fa-caret-down cursorPointer direction" id = "reset1" onclick = "addActiveSort(\'reset\', \'asc\'); complianceDtl_datatable(\'\',\'\',\'' + name + '\',\'mainactive\',\'\',1,\'\',\'reset\', \'asc\');sortingIconColor(\'reset1\')" style="font-size:18px"></i>'
                        +'<i class="fa fa-caret-up cursorPointer direction" id = "reset2"  onclick = "addActiveSort(\'reset\', \'desc\'); complianceDtl_datatable(\'\',\'\',\'' + name + '\',\'mainactive\',\'\',1,\'\',\'reset\', \'desc\');sortingIconColor(\'reset2\')" style="font-size:18px"></i>');
                },
                "drawCallback": function (settings) {

                    $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
                    // $(".dataTables_filter:first").replaceWith('<div id="notifyDtl_filter" class="dataTables_filter"><label><input type="text" class="form-control form-control-sm" placeholder="Search records" value="' + notifSearch + '" id="notifSearch" aria-controls="notifyDtl"></label></div>');
                }
            });
            $('.dataTables_filter input').addClass('form-control');
            $('.tableloader').hide();
        }
    });

    $('#complDtl tbody').on('click', 'tr', function () {
        var rowID = table3.row(this).data();
        table3.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        $('#notiname').val(rowID[6]);
        $('#macname').val(rowID[1]);
        $('#eventtime').val(rowID[2]);
        $('#custname').val(rowID[7]);
    });
    $("#notification_searchbox").keyup(function () {
        table3.search(this.value).draw();
    });

    $('#notifyDtl').DataTable().search('').columns().search('').draw();
}


$('body').on('click', '.page-link', function () {
    var nextPage = $(this).data('pgno');
    const activeElement = window.currentActiveSortElement;
    const key = (activeElement) ? activeElement.type : '';
    const sort = (activeElement) ? activeElement.sort : '';
    notifName = $(this).data('name');
    complianceDtl_datatable('', '', '', 'mainactive', '', nextPage, '', key, sort);
})
$('body').on('change', '#notifyDtl_lengthSel', function () {

    complianceDtl_datatable('','','', 'mainactive', '', 1);
});

// $(document).on('keypress', function (e) {
//     if (e.which == 13) {
//         var notifSearch = $('#notifSearch').val();
//         if (notifSearch != ''){
//             complianceDtl_datatable('','','', 'mainactive', '', 1);
//         }else{
//             complianceDtl_datatable('','','', 'mainactive', '', 1);
//         }
//     }
// });

$('#complianceDetailsList').on("click", "tbody tr td a.resetComplianceGroupHand", function (event) {

    var trObj = $(this).parents('tr');
    var detailsTab = window.detailsDataTableGlobal;
    var row = detailsTab.row(trObj).data();

    trObj.addClass('active');
    resetComplianceItem(encodeURIComponent(window.selectedComplianceName), row);
});


function addNotes(tname, cust, machine, entered) {

    var machine = machine;//$('#macname').val();
    var eventTime = entered;//$('#eventtime').val();
    //    var name = $('#notiname').val();
    var site = cust;//$('#custname').val();
    $.ajax({
        type: "POST",
        url: "compliance_func.php",
        dataType: 'text',
        data: {
            "function": 'getNotes',
            'name': tname,
            'site': site,
            'eventDt': eventTime,
            'machine': machine,
            'csrfMagicToken': csrfMagicToken
        },
        success: function (msg) {
            msg = $.trim(msg);
            if (msg === 'add') {
                $('#notesText').val('');
                $('#rsc-add-note').find('.form-control').attr('readonly', false);
                $('#rsc-add-note').find('.selectpicker').attr('disabled', false);
                $('#rsc-add-note').find(".selectpicker").selectpicker("refresh");
                $('#addNote').show();
                $('#editOption').hide();
                $('#toggleButton').hide();
            } else {
                $('#rsc-add-note').find('.form-control').attr('readonly', true);
                $('#addNote').hide();
                $('#editOption').show();
                $('#notesText').val(msg);
            }
            $('#toggleButton').hide();
            rightContainerSlideOn('rsc-add-note');

        },
        error: function (msg) {

        }
    });

}

function addNoteByName(type) {
    var machine = $('#macname').val();
    var eventTime = $('#eventtime').val();
    var name = $('#notiname').val();
    var site = $('#custname').val();
    var note = $('#notesText').val();

    /*var regExp = /^[a-zA-Z0-9\_ ]*$/;

     if (!regExp.test(note)) {
     errorNotify("The note data should contain only alphanumeric characters, underscore or space");
     return;
     }*/

    var data = {
        function: "updateNote",
        name: name,
        site: site,
        eventDt: eventTime,
        machine: machine,
        note: note,
        'csrfMagicToken': csrfMagicToken
    };

    $.ajax({
        type: "POST",
        url: "compliance_func.php",
        data: data,
        dataType: 'text',
        success: function (msg) {
            if ($.trim(msg) === 'success') {
                if (type == 'add') {
                    $.notify("Note added successfully");
                } else {
                    $.notify("Note updated successfully");
                }
                setTimeout(function () {
                    location.reload();
                });
            }
            rightContainerSlideClose('rsc-add-note');

        },
        error: function (msg) {

        }
    });
}

function notifyFix() {
    //    var chk = $('.notifychk').is(':checked');
    //    if($("#notifyDtl tbody tr").hasClass("selected")){
    if ($('.notifychk').is(':checked')) {
        var name = $('#notiname').val();
        if (name == '') {
            $.notify("Please choose at least one record");
        } else {
            $.ajax({
                type: "POST",
                url: "compliance_func.php",
                data: {
                    function: 'get_notificationSoln', nid: name, token: token,
                    'csrfMagicToken': csrfMagicToken
                },
                success: function (gridData) {

                    rightContainerSlideOn('rsc-add-fix');
                    //            $('#default-fixes').modal('show');
                    $("#notify_actionMsg").html('');
                    $('#notificationfixList').html(gridData);

                },
                error: function (msg) {

                }
            });
        }
    } else {
        $.notify("Please select a record");
    }

}

function getDetails() {

    var notifDetList = [];
    var gridSel = $('#selected').val();
    $("input:checkbox[name*=checkNoc]:checked").each(function () {
        //notifDetList += $(this).attr('id') + ',';
        notifDetList.push($(this).attr('id'));
    });

    if(gridSel == '' && notifDetList == '') {
        $.notify('Please select a record!');
        return false;
    } else if(gridSel != '' && notifDetList == '') {
        notifDetList.push(gridSel);
    }

        var rightSlider = new RightSlider('#rsc-details');
        rightSlider.showLoader();

        var postData = {
        function: 'get_notificationsEvents',
        notifdetlist: notifDetList
        };

        $.ajax({
        type: "GET",
            url: "compliance_func.php",
            data: postData,
            dataType: 'json',
            success: function (gridData) {
                rightContainerSlideOn('rsc-details');
                $('#notifyeventDtl').DataTable().destroy();
                table4 = $('#notifyeventDtl').DataTable({
                    scrollY: 'calc(100vh - 240px)',
                    aaData: gridData,
                    autoWidth: false,
                    paging: true,
                    searching: false,
                    processing: true,
                    serverSide: false,
                    ordering: true,
                    select: true,
                    bInfo: false,
                    responsive: false,
                    stateSave: true,
                    "pagingType": "full_numbers",
                    "stateSaveParams": function (settings, data) {
                        data.search.search = "";
                    },
                    "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                    "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>"
                    },
                    "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                    drawCallback: function (settings) {
                    },
                    fnInitComplete: function (oSettings, json) {
                        setTimeout(function () {
                            rightSlider.hideLoader();
                        }, 1000);
                    }
                });

            },
            error: function (msg) {
                rightSlider.hideLoader();
            }
        });

        delay_AndSort(); // sort table

        $('#notifyeventDtl tbody').on('click', 'tr', function () {
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
            } else {
                table4.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');

            }
        });
    }



function delay_AndSort() {
    setTimeout(function () {
        table4.order([0, 'asc']).draw();
    }, 1500);
}

function export_notification() {


    var name = $('#notiname').val();

    if (name == '') {
        $.notify("Please choose at least one record");
    } else {
        location.href = "compliance_func.php?function=exportComplianceselected&name=" + name;
    }
}

//$("#export_allnotification").click(function() {
//    location.href = "compliance_func.php?function=exportComplianceselected";
//});


$("#interactiveNotifyPush").click(function () {

    var name = $('#notiname').val();
    var arrAllcheckVal = '';
    var checkedInput = $("input:checkbox[name*=checkNoc]:checked");

    checkedInput.each(function () {
        arrAllcheckVal += $(this).val() + '~~~~';
    });

    var sclid = arrAllcheckVal, machineName = '';

    if (checkedInput.length == 1) {
        machineName = checkedInput.parents('tr').find('td').eq(1).text();
    }
    var machineArg = (checkedInput.length >= 1) ? (checkedInput.length > 1 ? '&machine=multi' : '&machine=' + machineName) : '';

    $.ajax({
        type: "POST",
        url: "compliance_func.php",
        data: {
            function: "get_notificationSolnIntre", nid: name, sel: sclid, token: token,
            'csrfMagicToken': csrfMagicToken
        },
        success: function (gridData) {
            var url = window.location.href;     // Returns full URL
            var urlList = url.split('notification');
            location.href = urlList[0] + "resolution/index.php?notification" + machineArg;
        },
        error: function (msg) {
        }
    });
});

//$("#notifysolnPush").click(function() {
function updateSolution() {
    var arrAllcheckVal = '';
    $("input:checkbox[name*=checkNoc]:checked").each(function () {
        arrAllcheckVal += $(this).val() + '~~~~';
    });
    var sclid = arrAllcheckVal;
    if (sclid !== '') {
        var othersVal = $('input[name=othersSoln]:checked').val();
        var fixedVal = $('input[name=profilename]:checked').val();
        if (typeof othersVal !== 'undefined') {
            notifyOthersFix();
        }
        if (typeof fixedVal !== "undefined") {
            notifyVarValue(fixedVal, sclid);
        }
        if (typeof othersVal === 'undefined' && typeof fixedVal === 'undefined') {
            $.notify('Please select an entry to attach to the notification as the recommended fix.');
            //            $("#notify_actionMsg").css("color", "red").html('<span>Please select an entry to attach to the notification as the recommended fix..</span>');
        }

    } else {
        $.notify('Please select an entry to attach to the notification as the recommended fix.');
    }
}
//});

function notifyOthersFix() {

    $("#notify_actionMsg").html('');
    var arrAllcheckVal = '';
    $("input:checkbox[name*=checkNoc]:checked").each(function () {
        arrAllcheckVal += $(this).val() + '~~~~';
    });
    var actionVal = $('input[name=othersSoln]:checked').val();

    var actionStr = '';
    var sclid = arrAllcheckVal;
    if (sclid != '' && (actionVal != '' && actionVal != undefined)) {
        var selectedRow22 = sclid.split("~~~~");

        var sql = [];

        for (var i = 0; i < selectedRow22.length - 1; i++) {
            var selectedRow1 = selectedRow22[i].split('~~');
            var nid = selectedRow1[0];
            var sitename = selectedRow1[2];
            var machine = selectedRow1[3];
            var cidList = 0;
            var eventTime = selectedRow1[6];
            var eventIdx = 0;

            var status = actionVal;
            var dartnum = selectedRow1[5];
            var ts = Math.round((new Date()).getTime() / 1000);

            sql[i] = '(' + nid + ', "' + sitename + '","' + machine + '","' + status + '","' + ts + '",' + dartnum + ',' + cidList + ',"' + eventTime + '","' + eventIdx + '","' + status + '")))';

            actionStr += nid + '~~' + sitename + '~~' + machine + '~~' + status + '~~' + dartnum + '~~' + cidList + '~~' + eventTime + '~~' + actionVal + '~~~~';
        }

        if (actionStr !== '') {

            $.ajax({
                type: "POST",
                url: "compliance_func.php",
                data: {
                    function: "updateNocStatus", machineDet: actionStr, name: selNid, token: token,
                    'csrfMagicToken': csrfMagicToken
                },
                success: function (msg) {
                    msg = $.trim(msg);
                    if (msg === 'Done') {
                        $.notify('Solution pushed successfully');
                        complianceDtl_datatable(selNid, 'mainactive');
                        setTimeout(function () {
                            rightContainerSlideClose('rsc-add-fix');
                            //location.reload();
                        }, 1000);

                    } else {
                        $.notify('Failed to push the solution. Please try again');
                        rightContainerSlideClose('rsc-add-fix');
                    }

                },
                error: function () {
                    //reloadPage(selectedRow22, actionVal);
                    //$(".loadingStage").css({'display': 'none'});
                }
            });
        }


    } else {
        $.notify('Please select the solution that you want to pushed for the selected notification');
    }

}

function notifyVarValue(fixedVal, sclid) {
    $("#notify_actionMsg").html('');
    $.ajax({
        type: "POST",
        url: "compliance_func.php",
        data: {
            function: "get_notificationSolnDtl", notifyArr: sclid, token: token,
            'csrfMagicToken': csrfMagicToken
        },
        success: function (gridData) {
            var result = fixedVal.split("##");
            var dart = result[0];
            var variable = result[1];
            var shortDesc = result[2];
            var ProfileName = result[3];
            var profileOS = result[4];

            notifyFixes(dart, variable, shortDesc, ProfileName, profileOS, sclid);

        }
    });

}

function notifyFixes(dart, variable, shortDesc, ProfileName, profileOS, sclid) {
    $("#notify_actionMsg").html('');

    var GroupName = '';
    var params = {
        Dart: dart,
        variable: variable,
        shortDesc: shortDesc,
        Jobtype: "Notification",
        ProfileName: ProfileName,
        NotificationWindow: "1",
        GroupName: GroupName,
        ProfileOS: profileOS,
    }

    // var params = ''; //'function=AddRemoteJobs';
    // params += "&Dart=" + dart;
    // params += "&variable=" + variable;
    // params += "&shortDesc=" + shortDesc;
    // params += "&Jobtype=Notification";
    // params += "&ProfileName=" + ProfileName;
    // params += "&NotificationWindow=1";
    // params += "&GroupName=" + GroupName;
    // params += "&ProfileOS=" + profileOS;

    const functionName = `Add_RemoteJobs`
    var os = 'windows';// $("#osTypeDrop").val();
    if (os.toLowerCase() === 'windows') {
        params.OS = "windows"
        params.function = functionName
    } else if (os.toLowerCase() === 'android') {
        params.OS = "android"
        params.function = "Add_AndroidJobs"
    } else if ($(os.toLowerCase() === 'mac')) {
        params.OS = "os x"
        params.function = functionName
    } else if (os.toLowerCase() === 'linux') {
        params.OS = "linux"
        params.function = functionName
    } else if (os.toLowerCase() === 'ios') {
        params.OS = "ios"
        params.function = functionName
    }
    params.csrfMagicToken = csrfMagicToken;

    $("#executeLoader").show();
    $("#executeJob").hide();
    $.ajax({
        type: "POST",
        url: "../communication/communication_ajax.php",
        data: params,
        success: function (data) {
            $("#executeLoader").hide();
            if (data !== "error") {
                var result = data.split("##");
                var SupportedMachines = result[0];
                EmitJobsForServiceTags(SupportedMachines, "");
                var ShowProgressServiceTag = $.trim(result[6]);
                if (ShowProgressServiceTag === 'success') {


                    var actionVal = $('input[name=othersSoln]:checked').val();
                    var actionStr = '';
                    if (sclid != '') {
                        var selectedRow22 = sclid.split("~~~~");

                        for (var i = 0; i < selectedRow22.length - 1; i++) {
                            var selectedRow1 = selectedRow22[i].split('~~');
                            var nid = selectedRow1[0];
                            var sitename = selectedRow1[2];
                            var machine = selectedRow1[3];
                            var cidList = 0;
                            var eventTime = selectedRow1[6];
                            var eventIdx = 0;

                            var status = actionVal;
                            var dartnum = selectedRow1[5];
                            var ts = Math.round((new Date()).getTime() / 1000);

                            actionStr += nid + '~~' + sitename + '~~' + machine + '~~' + status + '~~' + dartnum + '~~' + cidList + '~~' + eventTime + '~~' + variable + '~~~~';
                        }


                        $.ajax({
                            type: "POST",
                            url: "compliance_func.php",
                            data: {
                                function: "updateNocStatus",
                                name: selNid,
                                machineDet: actionStr,
                                sugg: 1,
                                'csrfMagicToken': csrfMagicToken

                            },
                            success: function (data) {
                                $.notify("Solution pushed successfully");
                                rightContainerSlideClose('rsc-add-fix');
                                complianceDtl_datatable(selNid, 'mainactive');
                            }
                        });
                    }


                } else {
                    $.notify("Failed to push the solution. Please try again");
                    rightContainerSlideClose('rsc-add-fix');
                }
            }
        }
    });
}

function uniqueCheckBox(getStatus) {

    var checked = $('.form-check-input').is(':checked');
    var numberOfChecked = $('.form-check-input:checked').length;
    var getStatusVal = $.trim(getStatus);
    if (checked && numberOfChecked === 1) {
        if (getStatusVal === "Pending" || getStatusVal === "New") {
            $("#notifyfix").css({ "pointer-events": "fill", "color": "#333333" });
            //$("#direct_other_option").css({"pointer-events": "fill", "color": "#333333"});
        } else {
            $("#notifyfix").css({ "pointer-events": "none", "color": "#bfbfbf" });
            //$("#direct_other_option").css({"pointer-events": "none", "color": "#bfbfbf"});
        }
        $("#view_event_dtl").css({ "pointer-events": "fill", "color": "#333333" });
    } else if (checked && numberOfChecked > 0) {
        if (getStatusVal === "Pending" || getStatusVal === "New") {
            $("#notifyfix").css({ "pointer-events": "fill", "color": "#333333" });
            //$("#direct_other_option").css({"pointer-events": "fill", "color": "#333333"});
        } else {
            $("#notifyfix").css({ "pointer-events": "none", "color": "#bfbfbf" });
            //$("#direct_other_option").css({"pointer-events": "none", "color": "#bfbfbf"});
        }
        $("#view_event_dtl").css({ "pointer-events": "none", "color": "#bfbfbf" });
    } else {
        $("#notifyDtl tbody tr").removeClass("selected");
        $("#notifyfix").css({ "pointer-events": "none", "color": "#bfbfbf" });
        //$("#direct_other_option").css({"pointer-events": "none", "color": "#bfbfbf"});
        $("#view_event_dtl").css({ "pointer-events": "none", "color": "#bfbfbf" });
    }

    $(".user_check").change(function () {
        if ($('.user_check:checked').length == $('.user_check').length) {
            $("#notifyDtl tbody tr").removeClass("selected");
            $('#topCheckBox').prop('checked', true);
        } else {
            $("#notifyDtl tbody tr").removeClass("selected");
            $('#topCheckBox').prop('checked', false);
        }
    });
}


function getProfiles() {

    var name = $('#notiname').val();
    $('#notificationName').attr('readonly', 'readonly');

    if (name == '') {
        $.notify("Please choose at least one record");

    } else {
        $.ajax({
            url: "compliance_func.php",
            type: 'post',
            data: {
                function: "notify_getprofile", name: name,
                'csrfMagicToken': csrfMagicToken
            },
            success: function (data) {
                $('#notificationName').val(name);
                $('#soln').html(data);
                $(".selectpicker").selectpicker("refresh");
                rightContainerSlideOn('rsc-update-sol');

            }
        });
    }
}

function updateSol() {

    var name = $('#notificationName').val();
    var value = $('#soln').val();
    var mid = $("#soln option:selected").attr("id");
    if (value == '') {
        $.notify("Please select the solution that must be pushed");
    } else {
        $.ajax({
            url: "compliance_func.php",
            data: {
                function: "updateSoln",
                name: name,
                val: value,
                mid: mid,
                'csrfMagicToken': csrfMagicToken
            },
            type: "post",
            success: function (data) {
                $.notify("Solution updated successfully");
                rightContainerSlideClose('rsc-update-sol');
                location.reload();
            }
        });
    }
}

function showComplianceFilters() {
    rightContainerSlideOn('rsc-add-filter-container');
}

function loadComplianceUsingFilters() {

    priority = [];
    nfstatus = [];
    var prioritySpanData = '';
    var statusSpanData = '';
    $('#notifPriority').html('');
    $("input:checkbox[name*=prio_]:checked").each(function () {
        priority.push($(this).val());
        prioritySpanData += $(this).val() + ",";
    });
    $('#notifPriority').html(prioritySpanData.replace(/,\s*$/, ""));

    $("input:checkbox[name*=status_]:checked").each(function () {
        nfstatus.push($(this).val());
        statusSpanData +=  $(this).val() + ",";
    });
    $('#notifStatus').html(statusSpanData.replace(/,\s*$/, ""));

    rightContainerSlideClose('rsc-add-filter-container');

    if(priority.length <= 0){
        $.notify("Please select atleast one Item type");
        return;
    }

    if(nfstatus.length <= 0){
        $.notify("Please select atleast one Category type");
        return;
    }
    compliance_datatable(priority, nfstatus);
}
