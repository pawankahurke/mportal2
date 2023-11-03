// get imporsation details if exists
var ws = '';
$(document).ready(function () {

    $("input").keyup(function () {
        $(".circleGrey").css("background-color", "rgb(249, 75, 120)");
    });

    $("#editOption").click(function () {

        $("#toggleButton1").show();
        $("#toggleButton1").css("display", "block !important");
        $("#editOption").css("display", "none");
        var valueset = false;
        enabledisable(valueset);
    });


    $(".icon-simple-remove").click(function () {
        $("#toggleButton1").hide();
        $("#toggleButton1").css("display", "none !important");
        $("#editOption").css("display", "block");
        var valueset = false;
        enabledisable(valueset);
        rightContainerSlideOn('settings-add-container');//to open slider
    });

    $(".closecross").click(function () {
        $("#toggleButton").css("display", "none");
        $("#toggleButton1").css("display", "none");
        $("#editOption").css("display", "block");

        var valueset = true;
        enabledisable(valueset);
    });

    $(".deployaction").click(function () {
        $("#dipl_pushMsg").hide();
        $("#dipl_clickMsg").show();
        $("#dipl_pushMsg").show();
        var searchtype = $('#searchType').val();

        var depid = $('.depdetailsview:checked').map(function () {
            return $(this).attr('id');
        }).get();

        var ipval = $('#dtLeftList tbody tr.selected').attr('id');
        $('#dtLeftList tbody tr.selected').css('background-color":"#1e1e2f');

        if ((searchtype == "Sites") || (searchtype == "Groups")) {
            $.notify("Please select a device");
        } else {
            if ((ipval == "") || (ipval == undefined)) {
                $.notify("Please select the Subnet IP");
            } else if ((depid == "") || (depid == undefined)) {
                $.notify("Please choose at least one record");
            } else {
                selectedsubnetmask = $('#dtLeftList tbody tr.selected').attr('id');

                $("#dep_subnetUrl").val(selectedsubnetmask);
                //$("#dep_subnetip").val(subnetip);
                rightContainerSlideOn('deployimpdetails-add-container');
            }
        }
    });
    Get_imporsationDetails();
    $("#deploy_main").click(function () {
        $("#dipl_updaMsg").hide();
        $("#dipl_pushMsg").hide();
        $("#dipl_impMsg").hide();
        $("#dipl_clickMsg").hide();
        saveImpDetails();
//        deployFunction();
    });

    $("#edit_impdetails").click(function () {
        modify_imp();
    });

    $(".addingsubneturl").click(function () {
        subnetUrl();
    });
    $(".icon-simple-remove").click(function () {
        $("#toggleButton").css("display", "none");
        $("#editOption").css("display", "block");
        rightContainerSlideOn('modifyimpdetails-add-container');//to open slider
    });

    $(".impdetails_Edit").click(function () {
        var Servicetag = $("#searchValue").val();
        var Site = $("#rparentName").val();
        var searchtype = $("#searchType").val();
        if ((searchtype == "Sites") || (searchtype == "Groups")) {
            $.notify("Please select a device");
        } else if (searchtype == "ServiceTag") {
            $("#toggleButton1").css("display", "none");
            $("#editOption").css("display", "block");
            var valueset = true;
            enabledisable(valueset);

            $.ajax({
                url: '../lib/l-ajax.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    'function': 'AJAX_getImporsonationDetails', 'csrfMagicToken': csrfMagicToken
                },
                success: function (data) {
                    if (data.level == 'machine') {
                        $("#edit_impuser").val(data.username);
                        $("#edit_imppwd").val(data.password);
                        $("#edit_impdomain").val(data.domain);
                    } else {
                        $("#edit_impuser").prop('disabled', false);
                        $("#edit_imppwd").prop('disabled', false);
                        $("#edit_impdomain").prop('disabled', false);
                    }
                }
            });
            rightContainerSlideOn('modifyimpdetails-add-container');

        }
    });

    Get_DeploymentLeftDT();

});
$('#dtLeftList').DataTable({
    select: {
        style: 'single'
    }
});

$('.addSubnetIp').click(function () {
    Get_imporsationDetails();
});

$('.closebtn').click(function () {
    $('#subnetUrl, #impuser, #imppwd, #impdomain').val('');
});

function enabledisable(valueset) {

    $("#edit_impuser").prop("disabled", valueset);
    $("#edit_imppwd").prop("disabled", valueset);
    $("#edit_impdomain").prop("disabled", valueset);
}

function saveImpDetails() {
    var username = $("#dep_impuser").val();
    var pwd = $("#dep_imppwd").val();
    var domain = $("#dep_impdomain").val();
    var Servicetag = $("#searchValue").val();

    $.ajax({
        url: '../lib/l-ajax.php',
        type: 'POST',
        dataType: 'text',
        data: {
            'function': 'AJAX_UpdateImpersonationCreds',
            'username': username,
            'pwd': pwd,
            'domain': domain, 'csrfMagicToken': csrfMagicToken
        },
        success: function (data) {
            if (data.trim() == "success") {
                $.notify("Impersonation details have been added successfully");

                $(".deploymsg").css("display:block");
                $(".deploymsg").show();
                var ip = $("#dep_subnetUrl").val(selectedsubnetmask);
                var DirectJob = 'VarName=S00111_InclusionList;VarType=2;VarVal=' + ip + ';Action=SET;DartNum=111;VarScope=1;#;NextConf;#VarName=RunNowSemaphore;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=111;VarScope=1;';
                ExecuteDirectJob(Servicetag, DirectJob);
            } else if (data.trim() == "failed") {
                $(".deploymsg").css("display:none");
                $(".deploymsg").hide();
                $.notify("Failed to add Impersonation details. Please try again");
            } else if (data == "not valid") {
                $(".deploymsg").css("display:none");
                $(".deploymsg").hide();
                $.notify("Update failed. Please try again");
            }
        }
    });
}


var id = '';

var trigdeploy = false;

function Get_DeploymentLeftDT() {

    $('#dtLeftList').DataTable().destroy();

    var passlevel = $("#passlevel").val();
    var Site = $("#rparentName").val();
    var searchType = $("#searchType").val();

    if (searchType == 'Groups') {
        $('.NDGroupsShow').show();
        $('.NDGroupsHide').hide();
    } else {
        $('.NDGroupsShow').hide();
        $('.NDGroupsHide').show();
    }

    if (passlevel == 'Groups' || Site == 'All') {
        $("#addSubnet").addClass('disabled');
        $("#export").addClass('disabled');
        $("#impsetting").addClass('disabled');
    } else if (searchType == 'Sites') {
        $("#impsetting").addClass('disabled');
    } else if (searchType == 'ServiceTag') {
        $("#addSubnet").removeClass('disabled');
        $("#export").removeClass('disabled');
        $("#impsetting").removeClass('disabled');
    }
    $.ajax({
        type: "POST",
        url: "../lib/l-ajax.php",
        dataType: 'json',
        data: {
            'function': 'AJAX_GetDeploymentLeftList',
            'site': Site,
            'csrfMagicToken': csrfMagicToken
        },
        success: function (gridData) {

            var subnetVal = "";
            var emptyMes = "";
            if (gridData.length > 0) {
                subnetVal = gridData[0][0];
                emptyMes = "Not Scanned Yet.Please run a scan";
            } else if (gridData.length == 0) {
                subnetVal = "foundnone";
                emptyMes = "No subnet added";

            }

            if ($("#dep_Audit").is(":visible") == true) {
                deployAudit();
            } else {
                $("#selectedsubnetmask").val(subnetVal);
                Get_DeploymentRightDT(subnetVal);
            }
            if ((searchType == "Sites") || (searchType == "Groups")) {
                $(".scanlink").css("display", "none !important");
            }

            $(".se-pre-con").hide();

            $(".se-pre-con").hide();

            dtLeftList = $('#dtLeftList').DataTable({
                scrollY: jQuery('#dtLeftList').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: true,
                bInfo: false,
                responsive: true,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                   "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    search: "_INPUT_",
                    searchPlaceholder: "Search records",
                    "emptyTable": emptyMes
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, 
                    { "type": "date", "targets": [1] },
                    {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                initComplete: function (settings, json) {
                    $('#dtLeftList tbody tr:eq(0)').addClass("selected");
                },
                drawCallback: function (settings) {

                }

            });
            $('#dtLeftList tbody').on('click', 'tr', function () {
                var rowID = dtLeftList.row(this).data();

                var selected = rowID['DT_RowId'];

                $("#selectedsubnetmask").val(selected);
                Get_DeploymentRightDT(selected);
                Get_imporsationDetails();

                dtLeftList.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            });
        },
        error: function (msg) {

        }
    });

}

function Get_DeploymentRightDT(subnetmask) {
    var subnetmaskval;
    if (subnetmask == "") {
        subnetmaskval = "foundnone";
    } else {
        subnetmaskval = subnetmask;
    }


    $('#' + subnetmask).css("background-color", "#f1f1f1");
    var Servicetag = $("#searchValue").val();
    var passlevel = $("#passlevel").val();
    var Site = $("#rparentName").val();
    var searchType = $("#searchType").val();
    $("#selectedsubnetmask").val(subnetmask);

    checkQueueVlues(subnetmaskval, Site);
    $("#selectedsubnetmask").val(subnetmaskval);

    $.ajax({
        type: "POST",
        url: "../lib/l-ajax.php",
        dataType: 'json',
        data: {
            'function': 'AJAX_GetDeploymentRightList',
            'submask': subnetmaskval,
            'host': Servicetag,
            'site': Site,
            'csrfMagicToken': csrfMagicToken
        },
        success: function (gridData) {

            var strifyData = JSON.stringify(gridData);
            gridData = JSON.parse(strifyData);

            var emptyMes = "";
            var gridDatas = "";
            if (subnetmaskval == "foundnone") {
                emptyMes = "Subnetmask is not available";
                gridDatas = [];
                $("#scanbutton").addClass('disabled');
                $("#deploybutton").addClass('disabled');
            } else {
                if (passlevel == 'Groups' || Site == 'All') {
                    emptyMes = "<span>Please select a site to use deployment</span>";
                } else if (gridData.status == 'scan triggered') {
                    emptyMes = "Scan triggered from Dashboard. Waiting for machine response";
                    $("#scanbutton").addClass('disabled');
                    $("#deploybutton").addClass('disabled');
                } else if (gridData.status == 'scan initiated') {
                    $(".table-loader").hide();
                    emptyMes = "Scan in Progress wait for result";
//                document.getElementById("scanbutton").disabled = true;
//                document.getElementById("deploybutton").disabled = true;
                    $("#scanbutton").addClass('disabled');
                    $("#deploybutton").addClass('disabled');
                } else if (gridData.status == 'scan error') {
                    $(".table-loader").hide();
                    emptyMes = "There was error in last scan.Please run scan again";
                    document.getElementById("scanbutton").disabled = false;
                    document.getElementById("deploybutton").disabled = true;
                    $("#scanbutton").removeClass('disabled');
                    $("#deploybutton").addClass('disabled');
                } else if (gridData.status == 'not scanned') {
                    $(".table-loader").hide();
                    emptyMes = "Not Scanned Yet.Please run a scan";
                    $("#scanbutton").removeClass('disabled');
                    $("#deploybutton").addClass('disabled');

                } else {
                    $(".v8-loader").show();
                }
                $(".v8-loader").hide();
                gridDatas = gridData.griddata;
            }
            $('#dtRightList').DataTable().clear().destroy();

            dtRightList = $('#dtRightList').DataTable({
                scrollY: jQuery('#dtRightList').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridDatas,
                bAutoWidth: false,
                select: true,
                bInfo: false,
                responsive: true,
                processing: true,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    search: "_INPUT_",
                    searchPlaceholder: "Search records",
                    "emptyTable": emptyMes,
                    "processing": "<img src='../vendors/images/loader2.gif'> Loading..."
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                drawCallback: function (settings) {

                }
            });
//            }

        },
        error: function (msg) {

        }
    });
    $("#deployment_searchbox").keyup(function () {
        dtRightList.search(this.value).draw();
    });
}

function checkQueueVlues(subnetmaskval, Site) {

    var subnetmaskval = subnetmaskval;
    var Site = Site;

    $.ajax({
        url: '../lib/l-ajax.php',
        type: 'POST',
        data: {
            'function': 'AJAX_DEPL_CheckSubnetVlues',
            'submask': subnetmaskval,
            'site': Site, 'csrfMagicToken': csrfMagicToken
        },
        success: function (data) {
            if (data == "Data Not Found") {
                $("#reset").addClass('disabled');
            } else if (data == "Data Found") {
                $("#reset").removeClass('disabled');
            }

        }
    });
}

function Get_imporsationDetails() {

    var Servicetag = $("#searchValue").val();
    var Site = $("#rparentName").val();
//    $("#impmodal").modal('show');
    $.ajax({
        url: '../lib/l-ajax.php',
        type: 'POST',
        data: {
            'function': 'AJAX_GetImpersonationCreds',
            'host': Servicetag,
            'site': Site, 'csrfMagicToken': csrfMagicToken
        },
        success: function (data) {
            if ((data.username == '') || (data.password == "") || (data.domain == '')) {
                $("#deploy_main").show();
                $("#dipl_impMsg").show();
                $(".save_deploy").show();
                $(".deploymsg").hide();
                $(".deploymsg").css("display", "none");
                $("#dep_impuser").prop("disabled", false);
                $("#dep_imppwd").prop("disabled", false);
                $("#dep_impdomain").prop("disabled", false);
            } else {
                $(".save_deploy").hide();
                $("#deploy_main").hide();
                $(".deploymsg").css("display", "block");
                $("#dipl_pushMsg").show();
                $(".deploymsg").show();
                $("#impuser").val(data.username);
                $("#imppwd").val(data.password);
                $("#impdomain").val(data.domain);

                $("#dep_impuser").val(data.username);
                $("#dep_imppwd").val(data.password);
                $("#dep_impdomain").val(data.domain);

                $("#dep_impuser").prop("disabled", true);
                $("#dep_imppwd").prop("disabled", true);
                $("#dep_impdomain").prop("disabled", true);
            }

        }
    });

}

function modify_imp() {
    $('#error_dis_update').html('');
    var username = $("#edit_impuser").val();
    var pwd = $("#edit_imppwd").val();
    var domain = $("#edit_impdomain").val();
    var Servicetag = $("#searchValue").val();
    if (username == '' || pwd == '' || domain == '') {
        if (username == '') {
            $.notify("Please enter the Username");
        } else if (pwd == '') {
            $.notify("Please enter the password");
        } else if (domain == '') {
            $.notify("Please enter the domain name");
        }
    } else {
        $.ajax({
            url: '../lib/l-ajax.php',
            type: 'POST',
            data: {
                'function': 'AJAX_UpdateImpersonationCreds',
                'username': username,
                'pwd': pwd,
                'domain': domain, 'csrfMagicToken': csrfMagicToken
            },
            dataType: 'text',
            success: function (data) {
                data = data.trim();
                if (data == "success") {
                    $.notify("Impersonation details have been added successfully");
                    rightContainerSlideClose('modifyimpdetails-add-container');
                    Get_imporsationDetails();
                    var ip = $("#dep_subnetUrl").val(selectedsubnetmask);
                    var DirectJob = 'VarName=S00111_InclusionList;VarType=2;VarVal=' + ip + ';Action=SET;DartNum=111;VarScope=1;#;NextConf;#VarName=RunNowSemaphore;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=111;VarScope=1;';
                    ExecuteDirectJob(Servicetag, DirectJob);

                } else if (data == "failed") {
                    $.notify("Update failed. Please try again");
                    rightContainerSlideClose('modifyimpdetails-add-container');
                } else if (data == "not valid") {
                    $.notify("Update failed. Please try again");
                    rightContainerSlideClose('modifyimpdetails-add-container');
                }
            }
        });
    }

}

$(".reset-Scan-container-close").click(function () {
    rightContainerSlideClose('reset-Scan-container');
})

function subnetUrl() {

    var subnetUrl = $("#subnetUrl").val();
    // var Site = $("#rparentName").val();
    var userName = $("#impuser").val();
    var password = $("#imppwd").val();
    var domain = $("#impdomain").val();
    // var Servicetag = $("#searchValue").val();
    // var Site = $("#rparentName").val();

    $("#dep_subnetUrl").val(subnetUrl);

    if (subnetUrl == '') {
        $.notify('<span>Please enter the Subnet IP</span>');
        return false;
    } /*else if (userName == '') {
     $.notify('<span>Please enter the Username</span>');
     return false;
     } else if (password == '') {
     $.notify('<span>Please enter the password</span>');
     return false;
     } else if(domain == '') {
     $.notify('<span>Please enter the domain name</span>');
     return false;
     }*/ else {

        if (subnetUrl != "") {

            var subnetUrlBrk = subnetUrl.split(".");

            if (subnetUrlBrk.length == 4) {
                var pos1 = parseInt(subnetUrlBrk[0]);
                var pos2 = parseInt(subnetUrlBrk[1]);
                var pos3 = parseInt(subnetUrlBrk[2]);
                var pos4 = parseInt(subnetUrlBrk[3]);
                if (pos1 <= 255 && pos2 <= 255 && pos3 <= 255) {
                    $.ajax({
                        url: '../lib/l-ajax.php',
                        type: 'POST',
                        data: {
                            'function': 'AJAX_DEPL_AddSubnetId',
                            'subip': subnetUrl,
                            'username': userName,
                            'password': password,
                            'domain': domain, 'csrfMagicToken': csrfMagicToken
                        },
                        success: function (data) {
                            $("#error_disp").html('');
                            if (data == "1") {
                                $("#subnetErr").html("");
                                $("#subnetErr").fadeIn();
                                $.notify("Subnet IP already exists");

                                setTimeout(function () {
                                    $("#subnetErr").fadeOut('5000');
                                }, 1000);
                            } else {
                                $.notify("Subnet IP added Successfully");
                                //Get_DeploymentLeftDT();
                                setTimeout(function () {
                                    location.reload();
                                }, 2500);
                            }
                        }
                    });
                } else {
                    $("#subnetErr").html("");
                    $("#subnetErr").fadeIn();
                    $.notify("Please enter the correct Subnet IP");

                    setTimeout(function () {
                        $("#subnetErr").fadeOut('5000');
                    }, 1000);
                }

            } else {
                $("#subnetErr").html("");
                $("#subnetErr").fadeIn();
                $.notify("Please enter the correct Subnet IP");

                setTimeout(function () {
                    $("#subnetErr").fadeOut('5000');
                }, 1000);
            }

        } else {

            $("#subnetErr").html("");
            $("#subnetErr").fadeIn();
            $.notify("Please enter the Subnet IP");

            setTimeout(function () {
                $("#subnetErr").fadeOut('5000');
            }, 1000);

        }
    }
}

function uniqueCheckBox(getHost) {

    $("#selHosts").append(getHost + ",");
    $(".user_check").change(function () {
        if ($('.user_check:checked').length == $('.user_check').length) {
            $("#dtRightList tbody tr").removeClass("selected");
            $('#topCheckBox').prop('checked', true);
            $("#topCheckBox").val("1");
        } else {
            $("#dtRightList tbody tr").removeClass("selected");
            $('#topCheckBox').prop('checked', false);
            $("#topCheckBox").val("0");
        }
    });
}

$("#topCheckBox").click(function () {
    var checked = $("#topCheckBox").val();
    if (checked == "0") {
        $(".user_check").prop('checked', true);
        $("#topCheckBox").val("1");
    } else {
        $(".user_check").prop('checked', false);
        $("#topCheckBox").val("0");
    }

});

function scanFunction(Site, Servicetag, subnetmask) {

//     var searchType = $("#searchType").val();
    var searchType = "ServiceTag";
    if ((searchType == "Sites") || (searchType == "Groups")) {
        $.notify("Please select a device");
    } else {

        var subnetmask_Format = subnetmask.replace(/\./g, "_");

        $("." + subnetmask_Format + "scangif").css("display", "block");
        $("." + subnetmask_Format + "scanlink").css("display", "none");
        $("." + subnetmask_Format + "lastscan").css("display", "none");
        $("." + subnetmask_Format + "lastscan_scanning").css("display", "block");

        if (searchType != "ServiceTag") {
            $.notify("Please select a device");
        } else {
            // var Servicetag = $("#searchValue").val();
            // var Site = $("#rparentName").val();
            // var subnetmask = $("#selectedsubnetmask").val();

            var subnetsplit = subnetmask.split(".");
            var subnetIp = subnetsplit[0] + '.' + subnetsplit[1] + '.' + subnetsplit[2] + '.1';
            $.ajax({
                url: '../lib/l-ajax.php',
                type: 'POST',
                data: {
                    'function': 'AJAX_DEPL_CheckScanJob',
                    'site': Site,
                    'host': Servicetag,
                    'submask': subnetmask, 'csrfMagicToken': csrfMagicToken
                },
                success: function (data) {

                    if (($.trim(data) == "offline") || ($.trim(data) == "Offline")) {

                        $("." + subnetmask_Format + "scangif").css("display", "none");
                        $("." + subnetmask_Format + "scanlink").css("display", "block");
                        $("." + subnetmask_Format + "lastscan_offline").css("display", "block");
//                        Get_DeploymentLeftDT();
//                        Get_DeploymentRightDT(subnetmask);
                        $.notify("Please choose a device that is online ");
                    } else if ($.trim(data) == "no impersonation") {
                        $("#impmodal").modal('show');
//                    $("#warning").modal('show');
//                    $("#mainError").hide();
//                    $("#scanError").hide();
//                    $("#impersonation").show();
                        trigscan = true;
                        $.notify("Please add the Impersonation Details");
                    } else {
                        if ($.trim(data) == "scan triggered") {
                            $("." + subnetmask_Format + "lastscan_offline").css("display", "none");
                            var DirectJob = 'VarName=OnlyInclusionList;VarType=3;VarVal=TRUE;Action=SET;DartNum=111;VarScope=1;#;NextConf;#VarName=S00111_InclusionList;VarType=2;VarVal=' + subnetIp + ';Action=SET;DartNum=111;VarScope=1;#;NextConf;#VarName=RunNowSurvey;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=111;VarScope=1;';
                            ExecuteDirectJob(Servicetag, DirectJob);
                            location.reload();
                            Get_DeploymentLeftDT();
                            Get_DeploymentRightDT(subnetmask);


                        } else if ($.trim(data) == "scan initiated") {
                            var DirectJob = 'VarName=OnlyInclusionList;VarType=3;VarVal=TRUE;Action=SET;DartNum=111;VarScope=1;#;NextConf;#VarName=S00111_InclusionList;VarType=2;VarVal=' + subnetIp + ';Action=SET;DartNum=111;VarScope=1;#;NextConf;#VarName=RunNowSurvey;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=111;VarScope=1;';
                            ExecuteDirectJob(Servicetag, DirectJob);
                            Get_DeploymentLeftDT();
                            Get_DeploymentRightDT(subnetmask);
                            location.reload();

                        } else if (($.trim(data) == "scan error") || ($.trim(data) == "no scan")) {
                            $("." + subnetmask_Format + "lastscan_failed").css("display", "block");
                            $("." + subnetmask_Format + "lastscan_scanning").css("display", "none");
                            $("." + subnetmask_Format + "lastscan").css("display", "none");
                            $("." + subnetmask_Format + "scangif").css("display", "none");
                            $("." + subnetmask_Format + "lastscan_offline").css("display", "none");
                            $("." + subnetmask_Format + "scanlink").css("display", "block");
                        }

                    }

                }
            });
        }
    }
}

function deployFunction() {
    var searchType = $("#searchType").val();

    var Servicetag = $("#searchValue").val();
    var Site = $("#rparentName").val();
    var subnetmask = $("#dep_subnetUrl").val(); //$('#dtLeftList tbody tr.selected').attr('id')//

    var subnetsplit = subnetmask.split(".");
    var subnetIp = subnetsplit[0] + '.' + subnetsplit[1] + '.' + subnetsplit[2] + '.XXX';
    var ipval = $('.depdetailsview:checked').map(function () {
        return $(this).attr('value');
    }).get();

    var username = $("#dep_impuser").val();
    var imppassword = $("#dep_imppwd").val();
    var domain = $("#dep_impdomain").val();

    if (username == '') {
        $("#error_dispDip").show();
        $.notify('Please enter the Username');
        return false;
    } else if (imppassword == '') {
        $("#error_dispDip").show();
        $.notify('Please enter the password');
        return false;
    } else if (domain == '') {
        $("#error_dispDip").show();
        $.notify('Please enter the domain name');
        return false;
    } else {

        $.ajax({
            url: '../lib/l-ajax.php',
            type: 'POST',
            dataType: 'text',
            data: {
                'function': 'AJAX_DEPL_DeployJob',
                'site': Site,
                'host': Servicetag,
                'submask': subnetIp,
                'ip': ipval,
                'password': imppassword,
                'username': username,
                'domain': domain, 'csrfMagicToken': csrfMagicToken
            },
            success: function (data1) {
                data = data1.trim();
                data == "added";
                if (data == "offline" || data == "Offline") {
                    $("#warning").modal('show');
                    $("#mainError").show();
                    $("#impersonationmsg").hide();
                    $("#impersonation").hide();
                } else if (data == "no impersonation") {
                    $("#warning").modal('show');
                    $("#mainError").hide();
                    $("#impersonationmsg").hide();
                    $("#impersonation").show();
                } else if (data == "added") {
                    $("#error_dispDip").hide();
                    $("#dipl_pushMsg").hide();
                    $("#dipl_updaMsg").show();
                    $("#deploy_main").hide();
                    var subnetIp = $("#selectedsubnetmask").val();
                    Get_imporsationDetails();

                }

            }
        });
    }
}

function deployconfirm() {

    var searchType = $("#searchType").val();

    var Servicetag = $("#searchValue").val();
    var Site = $("#rparentName").val();
    var subnetmask = $("#dep_subnetUrl").val(); //$('#dtLeftList tbody tr.selected').attr('id')//

    var subnetsplit = subnetmask.split(".");
    var subnetIp = subnetsplit[0] + '.' + subnetsplit[1] + '.' + subnetsplit[2] + '.XXX';
    var ipval = $('.depdetailsview:checked').map(function () {
        return $(this).attr('value');
    }).get();

    $("#dipl_updaMsg").hide();
    $("#dipl_impMsg").hide();

    var username = $("#dep_impuser").val();
    var imppassword = $("#dep_imppwd").val();
    var domain = $("#dep_impdomain").val();

    $.ajax({
        url: '../lib/l-ajax.php',
        type: 'POST',
        dataType: 'text',
        data: {
            'function': 'AJAX_DEPL_DeployJobConfirm',
            'site': Site,
            'host': Servicetag,
            'submask': subnetIp,
            'ip': ipval,
            'password': imppassword,
            'username': username,
            'domain': domain, 'csrfMagicToken': csrfMagicToken
        },
        success: function (data1) {
            data = data1.trim();

            if (data == "pushed") {
                $("#dipl_clickMsg").hide();
                $("#dipl_updaMsg").hide();
                $("#dipl_pushMsg").show();
                $.notify("Deployment has been successfully triggered");
                var DirectJob = 'VarName=S00111_InclusionList;VarType=2;VarVal=' + ipval + ';Action=SET;DartNum=111;VarScope=1;#;NextConf;#VarName=RunNowSemaphore;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=111;VarScope=1;';
                ExecuteDirectJob(Servicetag, DirectJob);
                rightContainerSlideClose('deployimpdetails-add-container');
                setTimeout(function () {
                    location.reload();
                }, 3500);

            }

        }
    });
}

function addImpersonation() {
    var userName = $("#impuser").val();
    var password = $("#imppassword").val();
    var domain = $("#impdomain").val();
    var Servicetag = $("#searchValue").val();
    var Site = $("#rparentName").val();
    if (userName == '') {
        $.notify('Please enter the Username');
        return false;
    } else if (password == '') {
        $.notify('Please enter the password');
        return false;
    } else {
        $("#impmodal").modal('hide');
        $.ajax({
            url: '../lib/l-ajax.php',
            type: 'POST',
            dataType: 'text',
            data: {
                'function': 'AJAX_ImpersonationCreds',
                'site': Site,
                'host': Servicetag,
                'password': password,
                'username': userName,
                'domain': domain, 'csrfMagicToken': csrfMagicToken
            },
            success: function (data) {
                if ($.trim(data) == "success") {
                    $("#warning").modal('show');
                    var ip = '';
                    if (trigdeploy) {
                        $(".user_check").each(function () {
                            if ($(this).is(":checked")) {
                                ip += $(this).val() + '\r\n';
                            }
                        });
                        $.notify('Impersonation Credentials have been Reviewed and Deployed');
                        var DirectJob = 'VarName=S00111_InclusionList;VarType=2;VarVal=' + ip + ';Action=SET;DartNum=111;VarScope=1;#;NextConf;#VarName=RunNowSemaphore;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=111;VarScope=1;';
                        ExecuteDirectJob(Servicetag, DirectJob);
                        trigdeploy = false;
                    } else {
                        $.notify('Administrator Credentials have been succesfully added');
                    }
                    $("#impersonationmsg").show();
                    $("#mainError").hide();
                    $("#impersonation").hide();
                } else {
                    $("#warning").modal('show');
                    $.notify('Failed to add impersonation credentials. Please try again');
                    $("#impersonationmsg").show();
                    $("#mainError").hide();
                    $("#impersonation").hide();
                }
            }
        });
    }
}

function impmodel() {
    var Servicetag = $("#searchValue").val();
    var Site = $("#rparentName").val();
    $("#impmodal").modal('show');
    $.ajax({
        url: '../lib/l-ajax.php',
        type: 'POST',
        data: {
            'function': 'AJAX_GetImpersonationCreds',
            'site': Site,
            'host': Servicetag, 'csrfMagicToken': csrfMagicToken
        },
        dataType: 'json',
        success: function (data) {
            $("#impuser").val(data.username);
            $("#imppassword").val(data.password);
            $("#impdomain").val(data.domain);
        }
    });
}

function exportDetails() {
    var subnetmask = $("#selectedsubnetmask").val();
    window.location.href = '../lib/l-ajax.php?function=AJAX_DEPL_GetExportDetails&subnetmask=' + subnetmask;
}

function reload() {
    location.reload();
}

$("#reset").click(function () {
    var subnetmask = $("#selectedsubnetmask").val();
    var Site = $("#rparentName").val();

    if (subnetmask == undefined || subnetmask == "" || subnetmask == "foundnone") {
        $.notify('Subnetmask is not available , please choose a site that is configured with a subnetmask.');
        $("#reset_warning_modal").modal('show');
    } else {
        $.ajax({
            url: '../lib/l-ajax.php',
            type: 'POST',
            data: {
                'function': 'AJAX_CheckDeployScan',
                'subnetmask': subnetmask,
                'site': Site, 'csrfMagicToken': csrfMagicToken
            },
            dataType: 'text',
            success: function (data) {
                if ($.trim(data) <= 0) {
                    $("#reset_modal").modal('show');
                } else if ($.trim(data) >= 0) {
                    $.notify('Scan usually takes 10-15 minutes. Reset functionality will be available after ' + data + ' minutes');
                    $("#reset_warning_modal").modal('show');
                }
            }
        });
    }
});

function ResetscanFunction_1(Site, Servicetag, subnetmask) {

    sweetAlert({
        title: ' Are you sure you want to Reset Last triggered scan?',
        text: "You won't be able to revert this action!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#050d30',
        cancelButtonColor: '#fa0f4b',
        cancelButtonText: "No",
        confirmButtonText: 'Yes'
    }).then(function (result) {

        var timeRemaining = $("#timeRemaing").val();
        var titleVal;

        if ((timeRemaining == '') || (timeRemaining == '0') || (timeRemaining == 0)) {
            timeRemaining = timerFunction();
            titleVal = "15m 0s";
        } else {
            timeRemaining = $("#timeRemaing").val();
            titleVal = timeRemaining;
        }
        sweetAlert({
            title: "Scan usually takes 10-15 minutes. Reset functionality will be available after " + titleVal,
            text: "You won't be able to revert this action!",
            type: 'warning',
            showCancelButton: false,
            confirmButtonColor: '#050d30',
            cancelButtonColor: '#fa0f4b',
            cancelButtonText: "No, cancel it!",
            confirmButtonText: 'ok'
        });

    });

}
function ResetscanFunction(Site, Servicetag, subnetmask) {
    sweetAlert({
        title: ' Are you sure you want to Reset Last triggered scan?',
        text: "You won't be able to revert this action!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#050d30',
        cancelButtonColor: '#fa0f4b',
        cancelButtonText: "No",
        confirmButtonText: 'Yes'
    }).then(function (result) {

        var timeRemaining = $("#timeRemaing").val();
        var titleVal;

        if ((timeRemaining == '') || (timeRemaining == '0') || (timeRemaining == 0)) {
            timeRemaining = timerFunction();
            titleVal = "15m 0s";
        } else {
            timeRemaining = $("#timeRemaing").val();
            titleVal = timeRemaining;
        }
        sweetAlert({
            title: "Scan usually takes 10-15 minutes. Reset functionality will be available after " + titleVal,
            text: "You won't be able to revert this action!",
            type: 'warning',
            showCancelButton: false,
            confirmButtonColor: '#050d30',
            cancelButtonColor: '#fa0f4b',
            cancelButtonText: "No, cancel it!",
            confirmButtonText: 'ok'
        });

    });
    setTimeout(resetCearCall(Site, Servicetag, subnetmask), 54000);
}


function resetCearCall(Site, Servicetag, subnetmask) {
    $("#timeRemaing").val('0');
    $.ajax({
        url: '../lib/l-ajax.php',
        type: 'POST',
        data: {
            'function': 'AJAX_ResetDeployScan',
            'subnetmask': subnetmask,
            'site': Site, 'csrfMagicToken': csrfMagicToken
        },
        dataType: 'text',
        success: function (data) {
        }
    });
}

function ResetscanFunction_org(Site, Servicetag, subnetmask) {
    var subnetmask = subnetmask; // $("#selectedsubnetmask").val();
    var Site = Site; //$("#rparentName").val();

//    var timer = setTimeout(yourfunction, 54000);
    sweetAlert({
        title: ' Last scan has been terminated now successfully. Please click on scan button to re-trigger scan.',
        text: "You won't be able to revert this action!",
        type: 'warning',
        showCancelButton: false,
        confirmButtonColor: '#050d30',
        cancelButtonColor: '#fa0f4b',
        cancelButtonText: "No",
        confirmButtonText: 'ok'
    }).then(function (result) {
        $.ajax({
            url: '../lib/l-ajax.php',
            type: 'POST',
            data: {
                'function': 'AJAX_ResetDeployScan',
                'subnetmask': subnetmask,
                'site': Site, 'csrfMagicToken': csrfMagicToken
            },
            dataType: 'text',
            success: function (data) {
            }
        });

    });

}
function timerFunction() {
    var dt = new Date();
    var countDownDate = dt.setMinutes(dt.getMinutes() + 15);
    setInterval(function () {

        // Get todays date and time
        var now = new Date().getTime();
        // Find the distance between now and the count down date
        var distance = countDownDate - now;
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);
        var timerem = minutes + "m " + seconds + "s ";
        $("#timeRemaing").val(timerem);
        return timerem;
    });
}



/** Deployment Audit
 *  Table Start **/

function deployAudit() {
    $(".se-pre-con").hide();
    $("#deploy_Audit").hide();
    $("#back").show();
    $("#export_auditdetails").show();
    $('#rightGrid').hide();
    $('#dep_Audit').show();
    $.ajax({
        type: "POST",
        url: '../lib/l-ajax.php',
        data: {
            'function': 'Ajax_DeploymentAuditGrid', 'csrfMagicToken': csrfMagicToken
        },
        dataType: 'json',
        success: function (gridData) {
            $('#deploymentauditGrid').DataTable().destroy();
            deployauditTable = $('#deploymentauditGrid').DataTable({
                scrollY: jQuery('#deploymentauditGrid').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                responsive: true,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search Records",
                    "emptyTable": emptyMes
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                columnDefs: [{className: "checkbox-btn", "targets": 0}, {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                columns: [
                    {"data": "site"},
                    {"data": "machine"},
                    {"data": "time"},
                    {"data": "text1"},
                    {"data": "details"}
                ],
                initComplete: function (settings, json) {
                    $('#deploymentauditGrid tbody tr:eq(0)').addClass("selected");
                },
                drawCallback: function (settings) {

                }

            });
        },
        error: function (msg) {

        }

    });
}


/** Deployment Audit Details
 *  Table Start **/

function getDeployAuditDetails(idx) {
    id = idx;
    $(".se-pre-con").hide();
    $("#deploy_details_warning_modal").modal('show');
    viewdetailpopupclicked();
    $.ajax({
        type: "POST",
        url: '../lib/l-ajax.php',
        data: {
            'function': 'Ajax_DeploymentAuditDetailsGrid',
            'idx': idx, 'csrfMagicToken': csrfMagicToken
        },
        dataType: 'text',
        success: function (gridData) {
            $.notify($.trim(gridData));
            $("#audit_Details td").mCustomScrollbar({
                theme: "minimal-dark"
            });
            $("#audit_Details td").mCustomScrollbar({
                theme: "minimal-dark"
            });
        },
        error: function (msg) {

        }

    });
}

/** Avoid Table
 *  Header Compression
 *  in Deployment Audit Details
 *   **/

function viewdetailpopupclicked() {
    setTimeout(function () {
        $(".event-info-grid-host").click();
    }, 300);
}

function backToDeploymentDetails() {
//    window.location = "../customer/deployment.php";

    $("#deploy_Audit").show();
    $('#rightGrid').show();
    $('#dep_Audit').hide();
    $('#back').hide();
    $("#export_auditdetails").hide();
//    Get_DeploymentLeftDT();
    viewdetailpopupclicked();
}

/**** Excel Sheet for 
 * Deployment Audit Details  ****/

$("#deployauditDetails").click(function () {
    window.location = '../lib/l-ajax.php?function=Ajax_DeploymentAuditDetailsExcel&idx=' + id;
});
function exportDeploymentDetails() {
    window.location.href = '../lib/l-ajax.php?function=Ajax_Deploy_AuditExcel';
}
$(".refreshpage").click(function () {
    location.reload();
});

$('.impdetails_Delete').click(function () {
    var selectedmassk = $('#selectedsubnetmask').val();
    var selectedSite = $('#searchValue').val();

    if (selectedmassk == '') {
        closePopUp();
        $.notify("Please select the record you want to delete");
    } else {
        closePopUp();
        sweetAlert({
            title: 'Are you sure that you want to continue?',
            text: "You wont be able to recover the subnet IP details once deleted",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: "No, cancel it!",
            confirmButtonText: 'Yes, delete it!'
        }).then(function (result) {
            var obj = {
                "function": "Ajax_DeploymentSubnetDetails",
                "selectedmassk": selectedmassk,
                "selectedSite": selectedSite, 'csrfMagicToken': csrfMagicToken
            };
            $.ajax({
                url: '../lib/l-ajax.php',
                data: obj,
                type: "post",
                success: function (msg) {
                    closePopUp();
                    msg = $.trim(msg);
                    if (msg == 'Success') {
                        $.notify('subnet IP deleted successfully.');
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    } else {
                        $.notify('Failed to delete. Please try again');
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
});
