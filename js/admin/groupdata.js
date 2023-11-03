$(document).ready(function () {
    Group_datatablelist();
})

/* ******************* GROUP NEW CODE ******************* */
/* =========== GROUP DETAIL LIST ============ */
function Group_datatablelist() {

//    var functioncall    = "function=get_viewgroups";
//    var encryptedData   = get_RSA_EnrytptedData(functioncall);

    $.ajax({
        url: "groupfunctions.php",
        type: "POST",
        data: "function=get_viewgroups&csrfMagicToken=" + csrfMagicToken,
        dataType: "json",
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#groupdataList').DataTable().destroy();
            groupTable = $('#groupdataList').DataTable({
//                scrollY: jQuery('#groupdataList').data('height'),
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
                scrollY: 'calc(100vh - 240px)',
                "pagingType": "full_numbers",
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records",
                },
                "dom": '<"top"f>rt<"bottom"lp><"clear">',
//                columnDefs: [{className: "checkbox-btn", "targets": [0]}, 
//                             { "width": "30%", "targets": 1 },   
//                             { "width": "30%", "targets": 2 },   
//                    {
//                        targets: "datatable-nosort",
//                        orderable: false
//                    }],
                initComplete: function (settings, json) {
                },
                /* drawCallback: function (settings) {
                 $(".dataTables_scrollBody").mCustomScrollbar({
                 theme: "minimal-dark"
                 });
                 $('.equalHeight').matchHeight();
                 $(".se-pre-con").hide();
                 } */
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });

    $('#groupdataList').on('click', 'tr', function () {
        var rowID = groupTable.row(this).data();
        var selected = rowID[5];
        var boolstring = rowID[6];
        var count = rowID[1];
        $('#selected').val(selected);
        $('#Count').val(count);
        $('#grupnamehidden').val(rowID[0]);

        if (boolstring == 'CSV' || boolstring == 'Manual') {
            $('#edit_group_drop').show();
            $('#delete_group').show();
        } else {
            $('#delete_group').hide();
            $('#edit_group_drop').hide();
        }
        groupTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });

    $('#groupdataList').on('dblclick', 'tr', function () {
        $('#editOption').show();
        $('#toggleButton').hide();
        selectConfirm('edit_group_drop');
    });

    $("#groups_searchbox").keyup(function () {//group search code
        groupTable.search(this.value).draw();
    });
}

/* ===== GROUP VIEW DETAIL ===== */
function groupviewDetail(grpid) {

//    var functioncall = "function=get_groupviewDetail&grpid=" + grpid;
//    var encryptedData = get_RSA_EnrytptedData(functioncall);

    $.ajax({
        url: "groupfunctions.php",
        type: "POST",
        data: "function=get_groupviewDetail&grpid=" + grpid + '&csrfMagicToken=' + csrfMagicToken,
        dataType: "json",
        success: function (gridData) {
//            $(".information-portal-popup .se-pre-con").hide();
            $('#groupeventDtl').DataTable().destroy();
            groupviewTable = $('#groupeventDtl').DataTable({
                scrollY: jQuery('#groupeventDtl').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                autoWidth: false,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                responsive: true,
                stateSave: true,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                columnDefs: [{className: "checkbox-btn", "targets": [0]},
                    {"width": "3%", "targets": 1},
                    {"width": "17%", "targets": 0},
                    {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                initComplete: function (settings, json) {
                },
                /* drawCallback: function (settings) {
                 $(".dataTables_scrollBody").mCustomScrollbar({
                 theme: "minimal-dark"
                 });
                 $(".information-portal-popup .se-pre-con").hide();
                 } */
            });
        },
        error: function (msg) {

        }
    });
}

/* ====== GROUP DROP MENU FUNCTIONS ====== */
function selectConfirm(data_target_id) {
    var editgid = $('#selected').val();
    var editcount = $('#Count').val();
    $('#succDelMsg').html('');
    if (data_target_id == 'edit_group_drop') {
        if (editgid == '') {
            //$('#warningemptygroup').modal('show');
            sweetAlert(
                    'Alert!',
                    'Please select a record',
                    'success'
                    );
            closePopUp();
        } else {
            get_groupnameajax(editgid, editcount);
            rightContainerSlideOn('edit-group');
//            $('#' + data_target_id).attr('data-bs-target', 'edit-group');
        }
    } else if (data_target_id == 'viewdetail_group_drop') {
        if (editgid == '') {
            $('#warningemptygroup').modal('show');
        } else {
            $('#' + data_target_id).attr('data-bs-target', 'group-view-detail');
            groupviewDetail(editgid);
            viewdetailpopupclicked();
        }
    } else if (data_target_id == 'delete_group') {

        var id = editgid;//$('#selected').val();

        if (id == undefined || id == 'undefined' || id == '') {
            sweetAlert("Alert", "Please select a package to delete", "error");
        } else {
            /*$.ajax({
             url: "SWD_Function.php?function=deleteFn&id=" + id,
             type: "POST",
             success: function(data) {
             Get_SoftwareRepositoryData();
             sweetAlert(
             'Deleted!',
             'Your package has been deleted.',
             'success'
             );
             }
             });*/
            sweetAlert({
                title: 'Are you sure?',
                text: "You will not be able to recover this group!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                deletegrouplist();
            });
        }

    } else if (data_target_id == 'export_group_list') {
        if (editgid == '') {
            //$('#warningemptygroup').modal('show');
            sweetAlert(
                    'Alert!',
                    'Please select a record',
                    'success'
                    );
        } else {
            viewgroupdetailExport(editgid);
        }
    } else if (data_target_id == 'view_detail_export') {

        viewgroupExport();
    } else if (data_target_id == 'editcsvcancel') {
//        refresh();

    } else if (data_target_id == 'editmanualcancel') {
//        refresh();
    } else if (data_target_id == 'editcrosscancel') {
        refresh();
    }
}

/* ====== ADD GROUP (CSV UPLOAD) ====== */

function resetCsvGroupMessage() {
    setTimeout(function () {
        $('#successmsg').html('');
        $('#successmsg').show();
    }, 3000);
}


function csvgroupcreate() {
    if (!($('#csvradio').is(":checked")) && !($('#manualradio').is(":checked"))) {
        $('#successmsg').html('<span style="color:red;">Please select CSV or Manual to create Group</span>');
        $('#successmsg').show();
        resetCsvGroupMessage();

        return false;
    }

    var Mgroupcsv = $('#csvgname').val();
//    var Mgroupcsv = btoa(Mgroup);
    var csvq = $('input[name=csv]')[0].files[0];
    var global = '0';
    if ($("#addglobal").is(":checked")) {
        global = "1";
    }


    if (csvq == 'undefined' || csvq == undefined) {
        $('#successmsg').html('<span style="color:red">Please upload .CSV file</span>');
        $('#successmsg').show();
        resetCsvGroupMessage();
    } else {
        var filecsv = csvq.name;
        var fileext = filecsv.substring(filecsv.lastIndexOf('.') + 1);
    }


    if (Mgroupcsv == '') {
        $('#successmsg').html('<span style="color:red">Please enter group name</span>');
        $('#successmsg').show();
        resetCsvGroupMessage();

    } else if (!validate_AlphaNumeric(Mgroupcsv)) {

        $('#successmsg').html('<span style="color:red">Special charecters not allowed in Group name</span>');
        $('#successmsg').show();
        resetCsvGroupMessage();

    } else if (fileext != 'csv') {
        $('#successmsg').html('<span style="color:red">Please upload .CSV file</span>');
        $('#successmsg').show();
        resetCsvGroupMessage();
    } else {
        var m_data = new FormData();
//        m_data.append('csvname', $('input[name=csv]')[0].files[0]);
        m_data.append('groupname', Mgroupcsv);
        m_data.append('csvname', csvq);
        $("#loadingCSVAdd").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');
        m_data.append('global', global);
        m_data.append('csrfMagicToken', csrfMagicToken);
//        var functioncall    = "function=get_addgroupcsv";
//        var encryptedData   = get_RSA_EnrytptedData(functioncall);
        $.ajax({
            url: 'groupfunctions.php?function=get_addgroupcsv',
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                $('#loadingCSVAdd').hide();
                $('#successmsg').show();
                $('#successmsg').html('<span style="color:green">' + response.msg + '</span>');
//                $('#successmsg').fadeOut(3000);

                if (response.error == 'Invalid') {
                    $('#loadingCSVAdd').hide();
                    $('#successmsg').show();
                    $('#successmsg').html('<span style="color:red">0 Machine Updated.</span>');
//                    $('#successmsg').fadeOut(3000);
                    resetCsvGroupMessage();
                }

                if (response.status == 'Failed') {
                    $('#loadingCSVAdd').hide();
                    $('#successmsg').show();
                    $('#successmsg').html('<span style="color:red">Group Name already exists</span>');
//                    $('#successmsg').fadeOut(3000);
                    resetCsvGroupMessage();
                }

                if (response.error == 'nodata') {
                    $('#loadingCSVAdd').hide();
                    $('#successmsg').show();
                    $('#successmsg').html('<span style="color:red">Unable to create group as csv is blank</span>');
                    resetCsvGroupMessage();
                }

                setTimeout(function () {
                    $("#addGroup").modal("hide");
                    //location.href = 'groups.php';
                    refresh();
                }, 3200);
            },
            error: function (data) {

            }
        });
    }
}

/* ====== SELECTED EDIT GROUP NAME ======  */
function get_groupnameajax(editgid, count) {

//    var functioncall    = "function=get_editgroupname&editgid=" + editgid;
//    var encryptedData   = get_RSA_EnrytptedData(functioncall);

    $.ajax({
        url: 'groupfunctions.php',
        // data: "function=get_editgroupname&editgid=" + editgid + "&count=" + count + "&csrfMagicToken=" + csrfMagicToken,
        data: {
            'function': 'get_edit_groupname',
            'editgid': editgid,
            'count': count,
            'csrfMagicToken': csrfMagicToken
        },
        type: 'post',
        dataType: 'json',
        success: function (data) {
            disableFields();
            $('#editfocused').addClass('is-focused');
            $('#editcsvgname').val(data.gname);
            var machinecount = data.option;
            $('#editmachinecount').html(machinecount.length);
//            $('#exclude_machine_edit').html(data.option);
//            $('#include_machine_edit').html(data.grpleftoption);                        
            $('#editcsvradio,#editmanualradio').removeAttr("checked");
            $('#editmanualradio,#editcsvradio').removeAttr("disabled");
            if (data.global == '1') {
                $('#editglobalyes').prop("checked", true);
//                 $('#editglobalno').prop("disabled",true);
            } else {
//                 $('#editglobalyes').prop("disabled",true);
                $('#editglobalno').prop("checked", true);
            }

            if (data.type == 'CSV') {

                $('#editmanualradio').prop("disabled", true);
                $('#editcsvradio').prop("checked", true);
                $('#editcsvuploaddata').show();
                $('#editcsvuploadbutton').show();
                $('#editmanualmachinelist').hide();
                $('#editmanualmachinebutton').hide();
            } else if (data.type == 'Manual') {
                $.ajax({
                    type: "POST",
                    url: "groupfunctions.php?function=getMachineListEdit&csrfMagicToken=" + csrfMagicToken,
                    dataType: "json",
                    data: {mid: data.machinelist}
                }).done(function (data) {
                    if ($.trim(data.state) === 'success') {
                        $('#include_machine').html(data.option);
                    }
                });

                $('#editcsvradio').prop("disabled", true);
                $('#editmanualradio').prop("checked", true);
                $('#editmanualmachinelist').show();
                $('#editmanualmachinebutton').show();
                $('#editcsvuploaddata').hide();
                $('#editcsvuploadbutton').hide();
            }
        }
    })
}

/* ===== EDIT CSV UPLOAD FUNCTION ===== */
function csvgroupedit() {
    if (!($('#editcsvradio').is(":checked")) && !($('#editmanualradio').is(":checked"))) {
        $('#successmsgedit').html('<span style="color:red;">Please select CSV or Manual to create Group</span>');
        $('#successmsgedit').show();
        return false;
    }

    var grpid = $('#selected').val();

    var grpedit = $('#editcsvgname').val();
    var csvqedit = $('input[name=csvedit]')[0].files[0];
    var globaledit = '0';
    if ($("#editglobal").is(":checked")) {
        globaledit = "1";
    }

    if (csvqedit == 'undefined' || csvqedit == undefined) {
        $('#successmsgedit').html('<span style="color:red">Please upload .CSV file</span>');
        $('#successmsgedit').show();
    } else {
        var filecsv = csvqedit.name;
        var fileext = filecsv.substring(filecsv.lastIndexOf('.') + 1);
    }

    if (grpedit == '') {
        $('#successmsgedit').html('<span style="color:red">please enter group name</span>');
        $('#successmsgedit').show();

    } else if (!validate_AlphaNumeric(grpedit)) {

        $('#successmsgedit').html('<span style="color:red">Special charecters not allowed in Group name</span>');
        $('#successmsgedit').show();

    } else if (fileext != 'csv') {
        $('#successmsgedit').html('<span style="color:red">Please upload .CSV file</span>');
        $('#successmsgedit').show();
    } else {
        var m_data = new FormData();
//        m_data.append('csvname', $('input[name=csv]')[0].files[0]);
        m_data.append('groupname', grpedit);
        m_data.append('grpid', grpid);
        m_data.append('csvname', csvqedit);
        $("#loadingCSVEdit").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');
        m_data.append('global', globaledit);
        m_data.append('csrfMagicToken', csrfMagicToken);

        $.ajax({
            url: 'groupfunctions.php?function=checkEditAccess',
            type: 'post',
            data: 'groupid=' + grpid + "&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json',
            success: function (data) {
                if (data.msg === 'success') {
                    $.ajax({
                        type: 'post',
                        url: 'groupfunctions.php?function=get_editgroupcsv',
                        data: m_data,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function (response) {
                            if (response.status == 'success') {
                                $('#loadingCSVEdit').hide();
                                $('#successmsgedit').show();
                                $('#successmsgedit').html('<span style="color:green">' + response.msg + '</span>');
//                    $('#successmsgedit').fadeOut(3000);
                                setTimeout(function () {
                                    $("#editGroup").modal("hide");
                                    location.href = 'groups.php';
                                }, 3200);

                            } else if (response.status == 'duplicate') {
                                $('#loadingCSVEdit').hide();
                                $('#successmsgedit').html('<span style="color:red">Group Name already exists</span>');
                                $('#successmsgedit').show();
                            }

                            if (response.error == 'Invalid') {
                                $('#loadingCSVEdit').hide();
                                $('#successmsgedit').show();
                                $('#successmsgedit').html('<span style="color:red">0 Machine Updated.</span>');
//                    $('#successmsgedit').fadeOut(3000);
                                setTimeout(function () {
                                    $("#editGroup").modal("hide");
                                    location.href = 'groups.php';
                                }, 3200);
                            }

                            if (response.status == 'Failed') {
                                $('#loadingCSVEdit').hide();
                                $('#successmsgedit').show();
                                $('#successmsgedit').html('<span style="color:red">Group Name already exists</span>');
//                    $('#successmsgedit').fadeOut(3000);
                                setTimeout(function () {
                                    $("#editGroup").modal("hide");
                                    location.href = 'groups.php';
                                }, 3200);
                            }
                        }
                    });
                } else {
                    $('#loadingCSVEdit').hide();
                    $('#successmsgedit').show();
                    $('#successmsgedit').html('<span style="color:red">You do not have access to edit this group</span>')
                    setTimeout(function () {
                        $("#editGroup").modal("hide");
                        location.href = 'groups.php';
                    }, 2000);
                }
            }
        });
    }
}

/* ======= SAMPLE FILE EXPORT FOR ADD GROUP ======= */
function samplefileExport() {
//    var functioncall    = "function=get_editgroupname&editgid=" + editgid;
//    var encryptedData   = get_RSA_EnrytptedData(functioncall);

    window.location.href = 'groupfunctions.php?function=get_samplefileDownload'
}

/* ======= SAMPLE FILE EXPORT FOR EDIT GROUP ======= */
function samplefileEditExport() {

    window.location.href = 'groupfunctions.php?function=get_samplefileDownload'
}

/* ======= VIEW GROUP DETAIL EXPORT ======== */
function viewgroupdetailExport(grpid) {

    var grpname = $('#grupnamehidden').val();
    window.location.href = 'groupfunctions.php?function=get_viewgroupdetailexportList&grupid=' + grpid + '&grpname=' + grpname;
}

/* ======= VIEW GROUP FULL EXPORT ======== */

function viewgroupExport() {

    window.location.href = 'groupfunctions.php?function=get_viewgroupexportList';
}

/* ======== DELETE GROUP CODE ======== */
function deletegrouplist() {
    console.log("indi");
    var str = $('#selected').val();
    $('#DelMsg').html('');
    $('#DelMsg').show();

//    var functioncall    = "function=get_groupListDelete&value=" + str;
//    var encryptedData   = get_RSA_EnrytptedData(functioncall);

    $.ajax({
        url: 'groupfunctions.php?function=checkEditAccess',
        type: 'post',
        data: 'groupid=' + str + "&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        success: function (data) {
            if (data.msg === 'success') {
                $.ajax({
                    type: "GET",
                    url: "groupfunctions.php",
                    data: "function=get_groupListDelete&value=" + str + "&csrfMagicToken=" + csrfMagicToken,
                    dataType: 'json',
                    success: function (data) {
                        /*if ($.trim(data.msg) === 'success') {
                         $('#succDelMsg').html('Record successfully deleted');
                         setTimeout(function(){
                         $('#delete-group-detail').modal('hide');
                         Group_datatablelist();
                         },3000);
                         }*/
                        sweetAlert(
                                'Deleted!',
                                'Your group has been deleted.',
                                'success'
                                );
                        Group_datatablelist();
                    }
                });
            } else {
                /*$('#DelMsg').html('You do not have access to delete this group');
                 setTimeout(function(){
                 $('#delete-group-detail').modal('hide');
                 Group_datatablelist();
                 },3000);*/
                sweetAlert(
                        'Sorry!',
                        'Your do not have access to delete this group.',
                        'success'
                        );
                Group_datatablelist();
            }
        }
    });
}

/* ======= GROUP VIEW DETAIL GRID COLUMN ONLOAD CLICK FUNCTION ======== */
function viewdetailpopupclicked() {
    setTimeout(function () {
        $(".event-info-grid-host").click();
    }, 300);
}

/* ====== ADD AND EDIT GROUP VALIDATION HIDE AND SHOW CODE ===== */
$("#csvgname").keyup(function () {
    $('#successmsg').hide();
    $('#successmsgmanual').hide();
})

$("#editcsvgname").keyup(function () {
    $('#successmsgedit').hide();
    $('#manualsuccessmsgedit').hide();
})

$('#csvS').mousedown(function () {
    $('#successmsg').hide();
})

$('#csvSedit').mousedown(function () {
    $('#successmsgedit').hide();
})

/* ======== CODE FOR EDIT POP-UP(BROWSE FOR FILE) ========  */
jQuery(document).ready(function ($) {
    jQuery('#fileuploader1').change(function (ev) {
        if ($('#fileuploader1').val().split('\\').pop() != '') {
            $(".samplefileEdit").hide();
            $(".browse-fileEdit").hide();
            $(".samplefileEdit2").html($('#fileuploader1').val().split('\\').pop());
            $('.remove-fileEdit').show();
        }
    });
    jQuery('.remove-fileEdit').click(function (event) {
        $(".samplefileEdit").show();
        $(".browse-fileEdit").show();
        $(".samplefileEdit2").html('');
        $('#fileuploader1').val('');
        $('.remove-fileEdit').hide();
    });
});

$('.remove-file').on('click', function () {
    $('#add_file').hide();
});

/*function moveOptions(srcList, destList, moveAll) {
 $('#successmsgmanual').hide();
 $('#manualsuccessmsgedit').hide();
 $('#manualeditfeed').hide();
 
 var selCnt =0;
 var source = document.getElementById(srcList);
 var destination = document.getElementById(destList);
 
 
 for (var j = 0; j < source.length; j++) {
 if ((source.options[j].selected) || (moveAll)) {
 selCnt++;
 }
 }
 if(selCnt > 0) {
 var i;
 for (i = 0; i < source.length; i++) {
 if ((source.options[i].selected) || (moveAll)) {
 destination.options[destination.length] = new Option(source.options[i].text,
 source.options[i].value, source.options[i].title, false, false);
 source.options[i] = null;
 i--;
 }
 }
 } else {
 $('#successmsgmanual').html('<span style="color:red">Please select some machine to perform action</span>');
 $('#successmsgmanual').show();  
 $('#manualeditfeed').html('<span style="color:red">Please select some machine to perform action</span>');
 $('#manualeditfeed').show(); 
 
 
 setTimeout(function(){ 
 $('#successmsgmanual').html(''); 
 $('#successmsgmanual').show();
 $('#manualeditfeed').html('');
 $('#manualeditfeed').show(); 
 
 }, 3000);
 } 
 
 }*/

var filterId = [];
function addDevices() {

    $("input:checkbox[name=type]:checked").each(function () {
        filterId.push($(this).val());
    });
    console.log(filterId);
    rightContainerSlideClose('rsc-add-container5');
    rightContainerSlideOn('grp-add-container');

    $('#groupslider').hide();
    $('#machine_count').show();
    $('#machinecount').html(filterId.length);
}

function editDevices() {

    $("input:checkbox[name=type]:checked").each(function () {
        filterId.push($(this).val());
    });
    rightContainerSlideClose('rsc-add-container5');
    rightContainerSlideOn('edit-group');
    $('#editmachinecount').html(filterId.length);

}


function manualgroupcreate() {

//    var filterId = [];
    var gname = $('#csvgname').val();
//    var gname = btoa(Mgroup);
    var global = '0';

    if ($('#globalyes').is(":checked")) {
        global = "1";
    }

    filterId = filterId.toString();
    var machinelist = filterId;
    console.log(machinelist);
    if (gname == '') {
        $('#successmsgmanual').html('<span style="color:red">Please enter group name</span>');
        $('#successmsgmanual').show();
        resetManualAddGroupMessage();
    } else if (!validate_AlphaNumeric(gname)) {
        $('#successmsgmanual').html('<span style="color:red">Special characters are not allowed</span>');
        $('#successmsgmanual').show();
        resetManualAddGroupMessage();
    } else if (machinelist == '') {
        $('#successmsgmanual').html('<span style="color:red">Please select some machines</span>');
        $('#successmsgmanual').show();
        resetManualAddGroupMessage();
    } else {

//        var functioncall    = "function=get_ManualGroupAdd&groupname="+gname+"&machinelist="+machinelist;
//        var encryptedData   = get_RSA_EnrytptedData(functioncall);

        $("#loadingMaualAdd").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');
        $.ajax({
            url: 'groupfunctions.php?function=get_ManualGroupAdd',
            type: 'post',
            data: "groupname=" + gname + "&machinelist=" + machinelist + "&global=" + global + "&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json',
            success: function (data) {

                if (data.status == 'Failed') {
                    $('#loadingMaualAdd').hide();
                    $('#successmsgmanual').show();
                    $('#successmsgmanual').html('<span style="color:red;">Group Name already exists</span>');
//                    $('#successmsgmanual').fadeOut(3000); 

                } else if (data.status == 'success') {
                    $('#loadingMaualAdd').hide();
                    $('#successmsgmanual').show();
                    $('#successmsgmanual').html('<span style="color:green;">Group created successfully</span>');
//                    $('#successmsgmanual').fadeOut(3000); 

                }

                setTimeout(function () {
                    rightContainerSlideClose('grp-add-container');
                    Group_datatablelist();
                }, 3200);
            }
        });
    }
}

function resetManualAddGroupMessage() {
    setTimeout(function () {
        $('#successmsgmanual').html('');
        $('#successmsgmanual').show();
    }, 3000);
}


function manualgroupedit() {
    var grpid = $('#selected').val();
    var grpedit = $('#editcsvgname').val();
//    var grpedit = btoa(grpeditmanual);

//    var filterId = [];
    var gname = $('#editcsvgname').val();
//    var gname = btoa(gnameedit);
    var global = '0';
    if ($('#editglobalyes').is(':checked')) {
        global = 1;
    }

//    $(".exclude_machine_edit option").each(function () {
//        filterId.push($(this).val());
//    });
    filterId = filterId.toString();
    var machinelist = filterId;

    if (gname == '') {

        $('#manualsuccessmsgedit').html('<span style="color:red">please enter group name</span>');
        $('#manualsuccessmsgedit').show();

    } else if (!validate_AlphaNumeric(gname)) {

        $('#manualsuccessmsgedit').html('<span style="color:red">special characters are not allowed</span>');
        $('#manualsuccessmsgedit').show();

    } else if (machinelist == '') {

        $('#manualsuccessmsgedit').html('<span style="color:red">please select some machines</span>');
        $('#manualsuccessmsgedit').show();

    } else {

//        var functioncall    = "function=get_ManualGroupEdit&groupname="+gname+"&machinelist="+machinelist+"&grpid="+grpid+"&grpedit="+grpedit;
//        var encryptedData   = get_RSA_EnrytptedData(functioncall);

        $.ajax({
            url: 'groupfunctions.php?function=checkEditAccess',
            type: 'post',
            data: 'groupid=' + grpid + "&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json',
            success: function (data) {
                if (data.msg === 'success') {

                    $("#loadingMaualEdit").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');
                    $.ajax({
                        url: 'groupfunctions.php?function=get_ManualGroupEdit',
                        type: 'post',
                        data: "groupname=" + gname + "&machinelist=" + machinelist + "&grpid=" + grpid + "&grpedit=" + grpedit + "&global=" + global + "&csrfMagicToken=" + csrfMagicToken,
                        dataType: 'json',
                        success: function (data) {

                            if (data.msg == 'success') {
                                $('#loadingMaualEdit').hide();
                                $('#manualsuccessmsgedit').show();
                                $('#manualsuccessmsgedit').html('<span style="color:green">Group Updated Successfully</span>');
//                    $('#manualsuccessmsgedit').fadeOut(3000);
                            } else if (data.msg == 'dublicate') {
                                $('#loadingMaualEdit').hide();
                                $('#manualsuccessmsgedit').show();
                                $('#manualsuccessmsgedit').html('<span style="color:red">Group Name already exists</span>');
//                    $('#manualsuccessmsgedit').fadeOut(3000);
                            }
                            setTimeout(function () {
                                rightContainerSlideClose('edit-group');
                                Group_datatablelist();
                            }, 2000);
                        }
                    });
                } else {
                    $('#loadingMaualEdit').hide();
                    $('#manualsuccessmsgedit').show();
                    $('#manualsuccessmsgedit').html('<span style="color:red">You do not have access to edit this group</span>')
                    setTimeout(function () {
                        $("#edit-group").modal("hide");
                        location.href = 'groups.php';
                    }, 2000);
                }
            }
        });
    }
}

$('#csvradio').change(function () {
    $('#csvuploaddata').show();
    $('#csvuploadbutton').show();
    $('#manualmachinebutton').hide();
    $('#manualmachinelist').hide();
    $('#successmsg').html('');
    $('#successmsg').hide();
})

$('#manualradio').change(function () {
    $("#add-manual").show();
    $('#csvuploaddata').hide();
    $('#csvuploadbutton').hide();
    $('#manualmachinebutton').show();
    $('#manualmachinelist').show();
//    $('#manualLoader').show();
    $.ajax({
        type: "POST",
        url: "groupfunctions.php?function=getMachineList&csrfMagicToken=" + csrfMagicToken,
        dataType: "json"
    }).done(function (data) {
        if ($.trim(data.state) === 'success') {
            $('#include_machine').html(data.option);
        }
    });

//    $('#successmsg').html('');
//    $('#successmsg').hide();
});

//check box selection logic
$('input[type="checkbox"]').change(function (e) {

    var checked = $(this).prop("checked"),
            container = $(this).parent(),
            siblings = container.siblings();
    console.log("checked=>" + checked + "container=>" + container + "siblings=>" + siblings);


    container.find('input[type="checkbox"]').prop({
        indeterminate: false,
        checked: checked
    });

    function checkSiblings(el) {

        var parent = el.parent().parent(),
                all = true;

        el.siblings().each(function () {
            return all = ($(this).children('input[type="checkbox"]').prop("checked") === checked);
        });

        if (all && checked) {

            parent.children('input[type="checkbox"]').prop({
                indeterminate: false,
                checked: checked
            });

            checkSiblings(parent);

        } else if (all && !checked) {

            parent.children('input[type="checkbox"]').prop("checked", checked);
            parent.children('input[type="checkbox"]').prop("indeterminate", (parent.find('input[type="checkbox"]:checked').length > 0));
            checkSiblings(parent);

        } else {

            el.parents("li").children('input[type="checkbox"]').prop({
                indeterminate: true,
                checked: false
            });

        }

    }

    checkSiblings(container);
});

$('#editcsvradio').change(function () {
    $('#editcsvuploaddata').show();
    $('#editcsvuploadbutton').show();
    $('#editmanualmachinelist').hide();
    $('#editmanualmachinebutton').hide();
    $('#successmsgedit').html('');
    $('#successmsgedit').hide();
})

$('#editmanualradio').change(function () {
    $("#add-manual").show();
    $('#editcsvuploaddata').hide();
    $('#editcsvuploadbutton').hide();
    $('#editmanualmachinelist').show();
    $('#editmanualmachinebutton').show();
    $('#successmsgedit').html('');
    $('#successmsgedit').hide();
})

function refresh() {
    location.reload();
}

function validate_AlphaNumeric(name) {
    var filter = /^[a-z\d\_\ \s]+$/i;
    if (filter.test(name)) {
        return true;
    } else {
        return false;
    }
}

$(".rightslide-container-close").click(function () {
    $("#csvuploaddata,#manualmachinelist").hide();
    $('#csvradio').prop("checked", false);
    $('#manualradio').prop("checked", false);
    $("#csvgname").val("");
    $("select#exclude_machine").find('option').remove();
});


