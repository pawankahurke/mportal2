$(document).ready(function () {

    if (window.location.href.indexOf("device") > -1) {
        get_advncdgroupData(nextPage = 1,notifSearch = '',key = '',sort = '');
    }

    $('#addNewAdvGroup').click(function () {
        getUsersList();
        getFilterList();
        get_advncdgroupData(nextPage = 1,notifSearch = '',key = '',sort = '');
        getSiteList();
    });
});

$('body').on('click', '.page-link', function () {
    var nextPage = $(this).data('pgno');
    notifName = $(this).data('name');
    const activeElement = window.currentActiveSortElement;
    const key = (activeElement) ? activeElement.sort : '';
    const sort = (activeElement) ? activeElement.type : '';
    get_advncdgroupData(nextPage, '', key, sort);
})

$('body').on('change', '#notifyDtl_lengthSel', function () {
    get_advncdgroupData(1,'');
});

function get_advncdgroupData(nextPage = 1, notifSearch = '', key = '', sort = '') {
    notifSearch = $('#notifSearch').val();

    checkAndUpdateActiveSortElement(key, sort);

    if (typeof notifSearch === 'undefined') {
        notifSearch = '';
    }

    $("#loader").show();
    $.ajax({
        url: "../admin/groupfunctions.php",
        type: "POST",
        data: {
        'function': 'get_viewadvncdgroups',
        'csrfMagicToken': csrfMagicToken,
        'limitCount': $('#notifyDtl_length :selected').val(),
        'nextPage': nextPage,
        'notifSearch': notifSearch,
        'order' : key,
        'sort' :sort
        },
        dataType: "json",
        success: function (gridData) {
            $('.loader').hide();
            $(".se-pre-con").hide();
            $('#advncdgroupList').DataTable().destroy();
            $('#advncdgroupList tbody').empty();
            groupTable  = $('#advncdgroupList').DataTable({
                scrollY: 'calc(100vh - 240px)',
                scrollCollapse: true,
                paging: false,
                searching: false,
                ordering: false,
                aaData: gridData.html,
                bAutoWidth: true,
                select: false,
                bInfo: false,
                responsive: true,
                stateSave: true,
                "pagingType": "full_numbers",
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                order: [[3, "desc"]],
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    search: "_INPUT_",
                    searchPlaceholder: "Search records"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                columnDefs: [{ "type": "date", "targets": [2,3] }],
                initComplete: function (settings, json) {
                    if (gridData.length === 0) {
                        $('.bottom').hide();
                        $('#totalmachinecount').val(gridData.length);
                        $('#view_grpDetails').hide();
                        $('#delete_group').hide();
                    } else {
                        $('.bottom').show();
                        $('#totalmachinecount').val(gridData.length);
                        $('#view_grpDetails').show();
                        $('#delete_group').show();
                    }
                    $("th").removeClass('sorting_desc');
                    $("th").removeClass('sorting_asc');
                },
                "drawCallback": function (settings) {
                    $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
                    $("#se-pre-con-loader").hide();
                   }
            });
            $('.dataTables_filter input').addClass('form-control');
        },
        error: function (msg) {
            console.log('Grid Error : ' + msg);
        }
    });

    $('#advncdgroupList').on('click', 'tr', function () {
                groupTable.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
                var rowID = groupTable.row(this).data();

                if (rowID != 'undefined' && rowID !== undefined) {
                    var grpname = rowID[8];
                    var advcndgrpid = rowID[7];
                    $('#groupid').val(advcndgrpid);
                    $('#groupname').val(grpname);
                    $('#selected').val(rowID[5]);
                    $('#hiddengrpid').val(rowID[7]);
                    $('#groupideditcsv').val(rowID[7]);
                    $('#grupnamehidden').val(rowID[8]);
                    $('#groupType').val(rowID[2]);
                }

            });

    $('#advncdgroupList').on('dblclick', 'tr', function () {
        var GroupType = $('#groupType').val();
        if(GroupType == 'Dynamic Group'){
            EditAdvGroup('Dynamic');
            $('#editGrpType').val('Dynamic');
        }else if(GroupType == 'Census Group'){
            EditAdvGroup('Census');
            $('#editGrpType').val('Census');
        }else{
            checkGroupEditAccess();
        }
    });

    $("#groups_searchbox").keyup(function () {//group search code
        groupTable.search(this.value).draw();
    });
}

var ulist = false;
function getUsersList() {
    if(ulist){
        return;
    }
    ulist = true;
    var formData = new FormData();
    formData.append('function', 'get_UsersList');
    formData.append('csrfMagicToken', csrfMagicToken);

    $.ajax({
        url: "../admin/groupfunctions.php",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'html',
        success: function (data) {
            $('#groupUsers2').html(data);
            $(".selectpicker").selectpicker("refresh");
        },
        error: function (errorThrown) {
            console.log(errorThrown);
        }
    });
}

function getFilterList() {
    var formData = new FormData();
    formData.append('function', 'get_FilterList');
    formData.append('csrfMagicToken', csrfMagicToken);

    $.ajax({
        url: "../admin/groupfunctions.php",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (data) {
            $('#assetFilter').html(data.asset);
            //$('#eventFilter').html(data.event);
            $('.selectpicker').selectpicker('refresh');
        }
    });
}

function getSiteList() {
    var formData = new FormData();
    formData.append('function', 'getSiteList');
    formData.append('csrfMagicToken', csrfMagicToken);

    console.log("Loading site list" + formData);
    $.ajax({
        url: "../admin/groupfunctions.php",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (data) {
            $('#sitelist').html(data.site);
            $('.selectpicker').selectpicker('refresh');
        }
    });
}

function createAdvanceGrp() {

    var gname = $('#advgname').val();
    var userlist = $('#groupUsers2').val();
    var evntrdo = '0';
    var asstrdo = '0';

    if ($("#assetradio").is(":checked")) {
        asstrdo = "1";
    }
    var asstid = $('#assetFilter').val();
    var searchString = $('#assetFilter').val();
    var searchtype = $('#assetOperator').val();
    var searchval = $('#assetVal').val();
    var sitelist = $('#sitelist').val();

    var object = {
        "gname": gname,
        "userlist": userlist,
        "str": searchString,
        "condition": searchtype,
        "strval": searchval,
        "site": sitelist
    };

    if (gname == '') {
        $.notify("Please enter the name of the group ");
        return false;
    }

    if (!validate_AlphaNumeric(gname)) {
        $.notify("Special characters not allowed in the name of the group");
        $('#successmsgadv').show();
        return false;
    }

    if (userlist == '') {
        $.notify("Please select the Users");
        return false;
    }

    if (asstid == '') {
        $.notify("Please choose the asset query");
        evntid = '';
        return false;
    }

    if (searchtype == '') {
        $.notify("Please choose asset operator");
        return false;
    }

    if (searchval == '') {
        $.notify("Please choose asset value");
        return false;
    }

    if (sitelist == '') {
        $.notify("Please select site");
        return false;
    }

    var formData = new FormData();
    formData.append('function', 'createAdvanceGrp');
    formData.append('gname', gname);
    formData.append('userlist', userlist);
    formData.append('str', searchString);
    formData.append('condition', searchtype);
    formData.append('strval', searchval);
    formData.append('site', sitelist);
    formData.append('csrfMagicToken', csrfMagicToken);

    $.ajax({
        url: '../admin/groupfunctions.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (data) {
            if (data.msg === 'success') {
                $.notify("Advance Group has been successfully added");
                setTimeout(function () {
                    rightContainerSlideClose('advgrp-add-container');
                    location.reload();
                }, 2000);
            } else if (data.msg === 'nomachine') {
                $.notify("No machine has been updated");
                setTimeout(function () {
                    rightContainerSlideClose('advgrp-add-container');
                    location.reload();
                }, 2000);
            } else if (data.msg === 'error') {
                $.notify("Group name already exists");
            }
        }
    });

}

$('#eventradio').on('click', function () {
    $('.eventfilter').show();
    $('.assetFilter').hide();

});

$('#assetradio').on('click', function () {
    $('.eventfilter').hide();
    $('.assetfilter').show();

});

function DeleteAdvGroup() {
    var selectedGrp = $('#groupid').val();

    if (selectedGrp == '') {
        closePopUp();
        $.notify("Please choose the group you want to delete");
    } else {
        closePopUp();
        setTimeout(function () {
            sweetAlert({
                title: 'Are you sure that you want to delete Advance Group?',
                text: "If you delete, you will not be able to undo the action.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#050d30',
                cancelButtonColor: '#fa0f4b',
                cancelButtonText: "No, cancel it!",
                confirmButtonText: 'Yes, delete it!'
            }).then(function () {
                var formData = new FormData();
                formData.append('function', 'deleteAdvGroup');
                formData.append('selid', selectedGrp);
                formData.append('csrfMagicToken', csrfMagicToken);

                // var obj = {
                //     'function': 'deleteAdvGroup',
                //     'selid': selectedGrp
                // };
                $.ajax({
                    url: "../admin/groupfunctions.php",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function (data) {
                        if ($.trim(data) == 'Success') {
                            $.notify("Group Deleted Successfully");
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        } else {
                            $.notify("Some error occurred. Please try again.");
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        }
                    },
                    error: function (errorThrown) {
                        console.log(errorThrown);
                    }
                });
            }).catch(function () {
                $(".closebtn").trigger("click");
            });
        }, 500);
    }

}

function ViewAdvGroup() {
    var selectedGrp = $('#groupid').val();
    var grpType = $('#groupType').val();
    if (selectedGrp == '') {
        closePopUp();
        $.notify("Please choose a group to view group details");
    } else {
        var formData = new FormData();
        formData.append('function', 'adv_groupviewDetail');
        formData.append('selid', selectedGrp);
        formData.append('groupType', grpType);
        formData.append('csrfMagicToken', csrfMagicToken);

        $.ajax({
            url: "../admin/groupfunctions.php",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (gridData) {
                // console.log($.trim(gridData),"griddata");
                if($.trim(gridData.msg) == 'Permission Denied'){
                    $.notify("You don't have the access to view the group");
                    closePopUp();
                }else{
                    rightContainerSlideOn('viewgrp-add-container');
                $('#versionDetail').DataTable().destroy();
                groupViewTable = $('#versionDetail').DataTable({
                    scrollY: 'calc(100vh - 240px)',
                    scrollCollapse: false,
                    paging: false,
                    searching: false,
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
                    language: {
                        "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                        search: "_INPUT_",
                        searchPlaceholder: "Search records",
                    },
                    "dom": '<"top"f>rt<"bottom"lp><"clear">',
                    columnDefs: [
                        {
                            targets: "datatable-nosort",
                            orderable: false
                        },
                        {
                            className: "table-plus",
                            targets: 0
                        }
                    ],
                    drawCallback: function (settings) {
                        $(".checkbox-btn input[type='checkbox']").change(function () {
                            if ($(this).is(":checked")) {
                                $(this).parents("tr").addClass("selected");
                            }
                        });
                    }

                });
                }

                $('.tableloader').hide();
            },
            error: function (errorThrown) {
                console.log(errorThrown);
            }
        });
    }
}

/* Advance Group Edit Func :: Start */
function EditAdvGroup(type) {
    var sel_grpid = $('#groupid').val();
    var sel_grpname = $('#groupname').val();
    if (sel_grpid == '') {
        closePopUp();
        $.notify("Please select the group you want to edit");
    } else {
        checkAdvGrpEditAccess(sel_grpid, sel_grpname,type);
    }
}

function checkAdvGrpEditAccess(sel_grpid, sel_grpname,type='') {
    var data = {
        'function' : 'checkAdvGrpEditAccess',
        'advgrpid' : sel_grpid, 'csrfMagicToken': csrfMagicToken
    };

    $.ajax({
        url: "../admin/groupfunctions.php",
        type: 'POST',
        data: data,
        success: function (data) {
            if (data == 'ok') {
                $('#edit-advgname').val(sel_grpname).attr('readonly', true);
                getEditUsersList(sel_grpid);
                rightContainerSlideOn('advgrp-edit-container');

                var newData = {
                    'function' : 'checkSavedValues',
                    'csrfMagicToken': csrfMagicToken,
                    'grpname' : sel_grpname,
                    'type' : type
                }

                $.ajax({
                    url: "../admin/groupfunctions.php",
                    type: 'POST',
                    data: newData,
                    dataType : 'json',
                    success:function(data){
                        if(type == 'Dynamic'){
                            $('#editcensusfilter').hide();
                            $('#editassetfilter').show();
                            $('#editassetVal').val(data.stringVal);
                            $('#Edynamicsitelist').html(data.site);
                            $('.selectpicker').selectpicker('refresh');
                            get_FilterList('edit',data.dataid);
                            showOtherValues('','edit',data.str,data.operator,data.dataid);
                            $('#editassetOperator').val(data.operator);
                            // document.getElementById("editassetOperator").value = data.operator;
                            $('.selectpicker').selectpicker('refresh')
                        }else{
                            $('#editcensusfilter').show();
                            $('#editassetfilter').hide();
                            $('#editcensusVal').val(data.stringVal);
                            $('#Edynamicsitelist').html(data.site);
                            $('.selectpicker').selectpicker('refresh');
                        }
                    },
                    error:function(error){
                        console.log("error");
                    }
                });
            } else {
                closePopUp();
                $.notify("You don't have the permission to edit this group");
                return false;
            }
        },
        error: function (errorThrown) {
            console.log(errorThrown);
        }
    });
}

function getEditUsersList(sel_grpid) {
    var formData = new FormData();
    formData.append('function', 'getEditUsersList');
    formData.append('advgrpid', sel_grpid);
    formData.append('csrfMagicToken', csrfMagicToken);

    $.ajax({
        url: "../admin/groupfunctions.php",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
//        dataType: 'json',
        success: function (data) {
            $('#edit-groupUsers2').html('');
            $('#edit-groupUsers2').html(data);
            $(".selectpicker").selectpicker("refresh");
        },
        error: function (errorThrown) {
            console.log(errorThrown);
        }
    });
}

function updateAdvanceGrp() {
    var grpType = $('#editGrpType').val();
    if(grpType == 'Census'){
        createDynamicCensusGrp('edit');
    }else if(grpType == 'Dynamic'){
        createDynamicAssetGrp('edit');
    }

}

// function updateAdvanceGrp() {
//     var sel_grpid = $('#groupid').val();
//     var sel_grpname = $('#groupname').val();
//     var user_list = $('#edit-groupUsers2').val();
//     var formData = new FormData();
//     formData.append('function', 'updateAdvGrpDetails');
//     formData.append('advgrpid', sel_grpid);
//     formData.append('advgrpname', sel_grpname);
//     formData.append('userlist', user_list);
//     formData.append('csrfMagicToken', csrfMagicToken);

//     $.ajax({
//         url: "../admin/groupfunctions.php",
//         type: 'POST',
//         data: formData,
//         processData: false,
//         contentType: false,
//         success: function (data) {
//             if (data == 'success') {
//                 $.notify('Advance Group <b>' + sel_grpname + '</b> has been updated successfully!');
//                 setTimeout(function () {
//                     $('.closebtn').click();
//                     get_advncdgroupData();
//                 }, 1500);
//             } else {
//                 $.notify('Failed to update the details. Please try again');
//             }
//         },
//         error: function (errorThrown) {
//             console.log("updateAdvGrpDetails::" + errorThrown);
//         }
//     });
// }

/* Advance Group Edit Func :: End */

function getSavedValues() {
    var grpid = $('#groupid').val();
    var gname = $('#groupname').val();
    var global = "0";
    var asstrdo = '0';

    var formData = new FormData();
    formData.append('function', 'editadvgroupValues');
    formData.append('gname', gname);
    formData.append('global', global);
    formData.append('asstrdo', asstrdo);
    formData.append('groupid', grpid);
    formData.append('csrfMagicToken', csrfMagicToken);
    // var data = {
    //     "function": "editadvgroupValues",
    //     "gname": gname,
    //     "global": global,
    //     "asstrdo": asstrdo,
    //     "groupid": grpid
    // };
    $.ajax({
        url: '../admin/groupfunctions.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (data) {
            console.log("success");
        },
        error: function (err) {
            console.log("Failed : " + err);
        }
    });
}

function RefreshAdvGroup() {
    var selectedGrp = $('#groupid').val();
    var groupname = $('#groupname').val();
    if (selectedGrp == '') {
        closePopUp();
        $.notify("Please Select a group to Refresh");
    } else {
        closePopUp();
        var formData = new FormData();
        formData.append('function', 'refreshAdvGroup');
        formData.append('selectedGrp', selectedGrp);
        formData.append('groupname', groupname);
        formData.append('csrfMagicToken', csrfMagicToken);

        $.ajax({
            url: '../admin/groupfunctions.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
//            dataType: 'json',
            success: function (data) {
                data = JSON.parse(data);
                if ($.trim(data.msg) == 'success') {
                    $.notify("Group has been successfully refreshed");
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
                } else {
                    $.notify("Some error occurred. Please try again.");
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
                }
            }, error: function (error) {
                console.log("error");
            }
        });
    }
}
