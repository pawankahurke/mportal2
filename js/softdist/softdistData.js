$(document).ready(function () {
    if (window.location.href.indexOf("softdist_config") > -1) {
        Get_SoftwareRepositoryData2();
    } else {
        Get_SoftwareRepositoryData();
    }

    $('table.nhl-datatable').parent('div.dataTables_scrollBody').css({ "height": "calc(100vh - 240px)" });
    // $('#pageName').text('Software Distribution');

    $('body').on('click', 'input.select-global-alt-hand', function () {
        var val = $(this).val();
        var globalField = $(this).parents('form').find('input.patch-global');

        switch (val) {
            case 'yes':
                globalField.val('yes');
                break;
            case 'no':
                globalField.removeAttr('value');
                break;
        }
    });




    $('#packageGrid tbody').on('dblclick', 'tr', function () {
        var editHand = $('#rsc-edit-container .myform-enable-edit'),
            saveHand = $('#rsc-edit-container .myform-edit-group');

        editHand.show();
        saveHand.hide();
        editPackagePrepare('extraButtonConfigure');
    });

});

$('.indistro-sitegroup-selection').click(function () {
    rightContainerSlideClose('rsc-distribute-execute-slider');
    rightContainerSlideClose('rsc-export-slider');
    $('input[name=jsCallback]').val('reopenDistroSlider');
    rightContainerSlideOn('rsc-add-container3');
});

$('.indistro-sitegroup-selection2').click(function () {
    rightContainerSlideClose('rsc-export-slider');
    $('input[name=jsCallback]').val('reopenDistroSliderExport');
    rightContainerSlideOn('rsc-add-container3');
});

$('.inftpcdn-conf-selection').click(function () {
    rightContainerSlideClose('rsc-ftp-cdn-configuration');
    $('input[name=jsCallback]').val('reopen-cdn-ftp-conf-slider');
    rightContainerSlideOn('rsc-add-container3');
});


function reopenDistroSlider() {

    var selectedTxt = $('input[name=searchValue]').val();
    selectedTxt = (selectedTxt.length > 90) ? selectedTxt.substring(0, 90) + '...' : selectedTxt;

    rightContainerSlideClose('rsc-add-container3');
    $('.indistro-selection-label').text(selectedTxt);
    $('.indistro-selection-label').attr('title', selectedTxt);
    rightContainerSlideOn('rsc-distribute-execute-slider');
}


function reopenDistroSliderExport() {
    var selectedTxt = $('input[name=searchValue]').val();
    selectedTxt = (selectedTxt.length > 90) ? selectedTxt.substring(0, 90) + '...' : selectedTxt;

    rightContainerSlideClose('rsc-add-container3');
    $('.indistro-selection-label').text(selectedTxt);
    $('.indistro-selection-label').attr('title', selectedTxt);
    rightContainerSlideOn('rsc-export-slider');
}

function reopenCdnFtpConfSlider() {

    var selectedTxt = $('input[name=searchValue]').val();
    selectedTxt = (selectedTxt.length > 90) ? selectedTxt.substring(0, 90) + '...' : selectedTxt;

    rightContainerSlideClose('rsc-add-container3');
    $('.inslide-site-gr-selected-label').text(selectedTxt);
    $('.inslide-site-gr-selected-label').attr('title', selectedTxt);
    rightContainerSlideOn('rsc-ftp-cdn-configuration');
}

var dTabObj;

$(document).ready(function () {
    $("#ascrail2009-hr").removeAttr('style');
    $("#ascrail2009-hr div").removeAttr('style');
    $("#ascrail2008-hr").removeAttr('style');
    $("#ascrail2008-hr div").removeAttr('style');
    $("#ascrail2007-hr").removeAttr('style');
    $("#ascrail2007-hr div").removeAttr('style');

    $('#softrepo').show();
    $('#softdist').hide();
    $(".dropdown-menu li a").css("white-space", "nowrap");

});


function confirmDelete() {

    var id = $('#selected').val();

    if (id == undefined || id == 'undefined' || id == '') {
        errorNotify('Please select a package to delete');
        return;
    }

    selectConfirm('delPackage');
    sweetAlert({
        title: 'Are you sure?',
        text: "You will not be able to recover this package!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#050d30',
        cancelButtonColor: '#fa0f4b',
        confirmButtonText: 'Yes, delete it!'
    }).then(function (result) {
        if (id == undefined || id == 'undefined' || id == '') {
            errorNotify('Please select a package to delete');
        } else {
            var formData = new FormData();
            formData.append('function', 'deleteFn');
            formData.append('id', id);
            formData.append('csrfMagicToken', csrfMagicToken)
            $.ajax({
                url: "SWD_Function.php",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (data) {
                    $('#selected').val('');
                    Get_SoftwareRepositoryData();
                    successNotify('Your package has been deleted');
                    location.reload();
                }, error: function (error) {
                    console.log(error)
                    $.notify('!Oops, something went wrong');
                }
            });
        }
    });

}

function resetSelectedDataFields() {
    $('#selected,#selOsType,#distId,#sel1').val('');
}

function Get_SoftwareRepositoryData(nextPage = 1, notifSearch = '', key = '', sort = '') {
    notifSearch = $('#notifSearch').val();

    checkAndUpdateActiveSortElement(key, sort);

    $('#loader').show();
    $('#absoLoader').show();


    if (typeof notifSearch === 'undefined') {
        notifSearch = '';
    }
    var gridData = {};
    $.ajax({
        url: "SWD_Function.php",
        type: "POST",
        dataType: "json",
        data: {
            'function': 'packageGridFn',
            'csrfMagicToken': csrfMagicToken,
            'limitCount': $('#notifyDtl_length :selected').val(),
            'nextPage': nextPage,
            'notifSearch': notifSearch,
            'order' : key,
            'sort' :sort,
            'type' : 'add'
            },
        success: function (gridData) {
            resetSelectedDataFields();
            $('#softrepository').show();
            $('#softdistribution').hide();
            $('.loader').hide();
            $('#absoLoader').hide();

            var search = $("#valueSearch").val();
            $("#packageGrid").dataTable().fnDestroy();
                   $('#packageGrid tbody').empty();
                   repoTable = $('#packageGrid').DataTable({
                       scrollY: 'calc(100vh - 240px)',
                       scrollCollapse: true,
                       paging: false,
                       searching: false,
                       bFilter: false,
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
                       order: [[2, "asc"]],
                       "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                       //                "lengthChange": false,
                       "language": {
                           "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                           search: "_INPUT_",
                           searchPlaceholder: "Search records"
                       },
                       "columnDefs": [
                           {
                               "targets": 0,
                               "orderable": false
                           }
                       ],
                       "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                       initComplete: function (settings, json) {
                           $('.equalHeight').show();
                           $('#absoLoader').hide();
                           $("th").removeClass('sorting_desc');
                           $("th").removeClass('sorting_asc');
                       },
                       "drawCallback": function (settings) {
                        $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
                        // $(".dataTables_filter:first").replaceWith('<div id="packageGrid_filter" class="dataTables_filter"><label><input type="text" class="form-control form-control-sm" placeholder="Search records" value="' + notifSearch + '" id="notifSearch" aria-controls="notifyDtl"></label></div>');
                        $(".user-page").show();
                        $("#se-pre-con-loader").hide();
                       }
                   });
                   $('.dataTables_filter input').addClass('form-control');
                   $('.tableloader').hide();
                //   $('#packageGrid_processing').css('top', ($('.dataTables_scroll').offset().top - 18) + 'px');
    $('.dataTables_filter input').addClass('form-control');
    // repoTable.column(0).visible(false); // swd update fix

    // window.dTabObj = repoTable;

    $("#repository_searchbox").keyup(function () {
        repoTable.search(this.value).draw();
        $("#packageGrid tbody").eq(0).html();
    });

    $('#packageGrid').DataTable().search('').columns().search('').draw();

    repoTable.on('search.dt', function () {
        $('#selected').val('');
    });

    /*$('#packageGrid tbody').on('mouseover', 'td', function() {
        var rowID = repoTable.row(this).data();
    });*/

    $(".bottompager").each(function () {
        $(this).append($(this).find(".bottomtable"));
    });

    $('#packageGrid tbody').on('click', 'tr', function () {

        //        var repoTable = window.dTabObj; // swd updates fix cj

        var rowID = repoTable.row(this).data();
        var platformSelected = rowID[3];
        var winres = platformSelected.match(/windows/g);
        var andres = platformSelected.match(/android/g);
        var iosres = platformSelected.match(/ios/g);
        var macres = platformSelected.match(/mac/g);
        var linres = platformSelected.match(/linux/g);

        if (winres == "windows") {

            $("#selOsType").val("windows");
            $("#editProp").show();

        } else if (andres == "android") {

            $("#selOsType").val("android");
            $("#editProp").show();

        } else if (iosres == "ios") {

            $("#selOsType").val("ios");
            $("#editProp").show();

        } else if (macres == "mac") {

            $("#selOsType").val("os x");
            $("#editProp").show();

        } else if (linres == "linux") {

            $("#selOsType").val("linux");
            $("#editProp").show();

        } else {

            $("#selOsType").val("");

        }

        $('#selected').val(rowID[6]);
        $('#sel').val(rowID[6]);
        $('#sel1').val(rowID[6]);
        $('#id1').val(rowID[6]);
        $('#ccid').val(rowID[6]);
        $('#ecid').val(rowID[6]);
        $('#distId').val(rowID[6]);
        repoTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');

    });

                }

    });

    //    Software Details

    $("#swdDetail").on('click', function () {

        var swd_detail = $('#selected').val();
        if (swd_detail == '') {
            $.notify("Please select a record");
            closePopUp();
        } else {
            rightContainerSlideOn('rsc-view-container');
            $.ajax({
                url: "SWD_Function.php",
                type: "POST",
                dataType: "json",
                data: { function: "swdDetailFn", sel: swd_detail ,csrfMagicToken: csrfMagicToken},
                async: true,
                success: function (data) {
                    if (data.config_type == 'same') {
                        $('#same32_64config_view').prop('checked', true);
                        $('#diff32_64config_view').prop('checked', false);
                        $('#same_configfile').show();
                        $('#different_configfile').hide();
                        $('#forfDetail').val(data.config_32);
                    } else {
                        $('#same32_64config_view').prop('checked', false);
                        $('#diff32_64config_view').prop('checked', true);
                        $('#same_configfile').show();
                        $('#different_configfile').show();
                        $('#forfDetail').val(data.config_32);
                        $('#forfDetail2').val(data.config_64);
                    }
                    $('input[type=text]').prev().parent().removeClass('is-empty');
                    $('#platformDetail').val(data.platformDetail);
                    $('#typeDetail').val(data.typeDetail);
                    $('#packNameDetail').val(data.packNameDetail);
                    $('#packNameDetail').attr('title', data.packNameDetail);
                    $('#versionDetail').val(data.versionDetail);
                    $('#pathDetail').val(data.pathDetail);
                    $('#path2Detail').val(data.path2Detail);
                    $('#androidIcon').val(data.androidIcon);
                    $('#forfDetail').val(data.forfDetail);
                    $('#forfDetail').attr('title', data.forfDetail);
                    $('#packDescDetail').val(data.packDescDetail);
                    $('#uploadDetail').val(data.uploadDetail);
                    $('#modifyDetail').val(data.modifyDetail);
                    $('#globalDetail').val(data.globalDetail);

                }
            });
        }

    });
}

$('body').on('click', '.page-link', function () {
    var nextPage = $(this).data('pgno');
    notifName = $(this).data('name');
    const activeElement = window.currentActiveSortElement;
    const key = (activeElement) ? activeElement.sort : '';
    const sort = (activeElement) ? activeElement.type : '';
    if (window.location.href.indexOf("softdist_config") > -1) {
        Get_SoftwareRepositoryData2(nextPage,'', key, sort);
    } else {
        Get_SoftwareRepositoryData(nextPage,'', key, sort);
    }
})

$('body').on('change', '#notifyDtl_lengthSel', function () {
    if (window.location.href.indexOf("softdist_config") > -1) {
        Get_SoftwareRepositoryData2(1,'');
    } else {
        Get_SoftwareRepositoryData(1,'');
    }
});

// $(document).on('keypress', function (e) {
//     if (e.which == 13) {
//         var notifSearch = $('#notifSearch').val();
//         if (notifSearch != ''){
//             if (window.location.href.indexOf("softdist_config") > -1) {
//                 Get_SoftwareRepositoryData2(1,notifSearch);
//             } else {
//                 Get_SoftwareRepositoryData(1,notifSearch);
//             }
//         }else{
//             if (window.location.href.indexOf("softdist_config") > -1) {
//                 Get_SoftwareRepositoryData2(1,'');
//             } else {
//                 Get_SoftwareRepositoryData(1,'');
//             }
//         }

//     }
// });

function Get_SoftwareRepositoryData2(nextPage = 1, notifSearch, key = '', sort = '') {
    notifSearch = $('#notifSearch').val();

    if (typeof notifSearch === 'undefined') {
        notifSearch = '';
    }

    checkAndUpdateActiveSortElement(key, sort);

    $('#loader').show();
    $.ajax({
        url: "../softdist/SWD_Function.php",
        type: "POST",
        dataType: "json",
        data: {
            'function': 'packageGridFn',
            'csrfMagicToken': csrfMagicToken,
            'limitCount': $('#notifyDtl_length :selected').val(),
            'nextPage': nextPage,
            'notifSearch': notifSearch,
            'order' : key,
            'sort' :sort,
            'type': 'config'
        },
        success: function (gridData) {
            // resetSelectedDataFields();
            $('#softrepository').show();
            $('#softdistribution').hide();
            var search = $("#valueSearch").val();
            $("#swdGrid2").dataTable().fnDestroy();
                   $('#swdGrid2 tbody').empty();
                   repoTable = $('#swdGrid2').DataTable({
                       scrollY: 'calc(100vh - 240px)',
                       scrollCollapse: true,
                       paging: false,
                       searching: false,
                       bFilter: false,
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
                    //    order: [[2, "asc"]],
                       "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                       //                "lengthChange": false,
                       "language": {
                           "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                           search: "_INPUT_",
                           searchPlaceholder: "Search records"
                       },
                       "columnDefs": [
                           {
                               "targets": 0,
                               "orderable": false
                           }
                       ],
                       "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                       initComplete: function (settings, json) {
                           $('.equalHeight').show();
                           $('.loader').hide();
                           $("th").removeClass('sorting_desc');
                           $("th").removeClass('sorting_asc');
                       },
                       "drawCallback": function (settings) {
                        $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
                       }
                   });
                   $('.dataTables_filter input').addClass('form-control');
                   $('.tableloader').hide();

                $('#swdGrid2 tbody').on('click', 'tr', function () {

                    var rowID = repoTable.row(this).data();

                    var PackageSelected = rowID[3];
                    var checkVal = PackageSelected.split('>').join(',').split('<').join(',').split(',');
                    var PackageNAme = checkVal[2];
                    $("#selectedPackageName").val(PackageNAme);
                    var platformSelected = rowID[0];
                    var winres = platformSelected.match(/windows/g);
                    var andres = platformSelected.match(/android/g);
                    var iosres = platformSelected.match(/ios/g);
                    var macres = platformSelected.match(/mac/g);
                    var linres = platformSelected.match(/linux/g);

                    if (winres == "windows") {

                        $("#selOsType").val("windows");
                        $("#editProp").show();

                    } else if (andres == "android") {

                        $("#selOsType").val("android");
                        $("#editProp").show();

                    } else if (iosres == "ios") {

                        $("#selOsType").val("ios");
                        $("#editProp").show();

                    } else if (macres == "mac") {

                        $("#selOsType").val("os x");
                        $("#editProp").show();

                    } else if (linres == "linux") {

                        $("#selOsType").val("linux");
                        $("#editProp").show();

                    } else {

                        $("#selOsType").val("");

                    }

                    $('#selected').val(rowID[5]);
                    $('#sel').val(rowID[5]);
                    $('#sel1').val(rowID[5]);
                    $('#id1').val(rowID[5]);
                    $('#ccid').val(rowID[5]);
                    $('#ecid').val(rowID[5]);
                    $('#distId').val(rowID[5]);
                    repoTable.$('tr.selected').removeClass('selected');
                    $(this).addClass('selected');

                });

            }
    });

    //    Software Details

    $("#swdDetail").on('click', function () {

        var swd_detail = $('#selected').val();
        if (swd_detail == '') {
            $.notify("Please select a record");
            closePopUp();
        } else {
            rightContainerSlideOn('rsc-view-container');
            $.ajax({
                url: "SWD_Function.php",
                type: "POST",
                dataType: "json",
                data: { function: "swdDetailFn", sel: swd_detail, csrfMagicToken: csrfMagicToken },
                async: true,
                success: function (data) {

                    $('input[type=text]').prev().parent().removeClass('is-empty');
                    $('#platformDetail').val(data.platformDetail);
                    $('#typeDetail').val(data.typeDetail);
                    $('#packNameDetail').val(data.packNameDetail);
                    $('#packNameDetail').attr('title', data.packNameDetail);
                    $('#versionDetail').val(data.versionDetail);
                    $('#pathDetail').val(data.pathDetail);
                    $('#forfDetail').val(data.forfDetail);
                    $('#forfDetail').attr('title', data.forfDetail);
                    $('#packDescDetail').val(data.packDescDetail);
                    $('#uploadDetail').val(data.uploadDetail);
                    $('#modifyDetail').val(data.modifyDetail);
                    $('#globalDetail').val(data.globalDetail);

                }
            });
        }

    });
}


function softDistRefresh() {

    $("#packageGrid").dataTable().fnDestroy();
    $("#packageGrid_filter").hide();

    var repoTable = $('#packageGrid').DataTable({
        autoWidth: true,
        paging: true,
        searching: true,
        processing: true,
        serverSide: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        pagingType: "full_numbers",
        bLengthChange: false,
        ajax: {
            url: "SWD_Function.php",
            type: "POST",
            dataType: "JSON",
            data:{'function': 'packageGridFn','csrfMagicToken' : csrfMagicToken}
        },
        columns: [
            { "data": "platform" },
            { "data": "packageName" },
            { "data": "version" },
            { "data": "packageDesc" },
            { "data": "global" }
        ],
        columnDefs: [
            { className: "dt-left", "targets": [0, 1, 2, 3, 4] }
        ]
    });
}

//Popup validation start
function selectConfirm(data_target_id) {

    $("#normError").hide();
    $("#mainError").show();

    var selected = $("#selected").val();

    if (selected === '') {

        $('#' + data_target_id).attr('data-bs-target', '#warning');

    } else {

        if (data_target_id === 'swdDetail') {

            $('#' + data_target_id).attr('data-bs-target', '#swd_detail');

        } else if (data_target_id === 'editPopup') {

            $('#' + data_target_id).attr('data-bs-target', '#edit_software_distribution');

        } else if (data_target_id === 'distexecPack') {

            $('#' + data_target_id).attr('data-bs-target', '#distPopup');

        } else if (data_target_id === 'delPackage') {

            $('#' + data_target_id).attr('data-bs-target', '#deletePackage');

        }
    }

    return true;

}

/*====== Package delete =========*/
function deletePackage() {

    var id = $('#selected').val();

    $.ajax({
        url: "SWD_Function.php",
        type: "POST",
        data: { function: "deleteFn", id: id ,csrfMagicToken: csrfMagicToken},
        success: function (data) {

            window.location.href = "index.php";

        }
    });
}

function pageRefresh() {
    setTimeout(function () {
        location.reload();
    }, 500);
}

function clearAllField() {

    $("#sele").val("");
    $("#packNamed").val("");
    $("#configStat").val("");
    $("#bit32").val("");
    $("#bit64").val("");
    $('#session').val("");
    $('#runas').val("");
    $("#cmdLineSetting").val("");
    $("#enablemsg").val("");
    $("#cmdLine").val("");
    $("#pposKey").val("");
    $("#pnegKey").val("");
    $("#msgtext").val("");
    $("#maxtime").val("");
    $("#defaultRead").val("");
    $("#logfiles").val("");
    $("#pprocesskill").val("");
    $("#deletelog").val("");
    $("#ppreInsCheck").val("");
    $("#ppSoftware").attr("");
    $('#prootKey').val("");
    $("#ppfilePath").val("");
    $("#ppSoftName").val("");
    $("#ppSoftVer").val("");
    $("#ppKb").val("");
    $("#ppServicePack").val("");
    $("#psubKey").val("");
    $("#platform1").val("");
    $("#types1").val("");
    $("#sfolder1").attr("");
    $("#nhrep1").removeAttr("checked");
    $("#otrep0").removeAttr("checked");
    $("#gplay1").removeAttr("checked");
    $("#nplay1").removeAttr("checked");
    $("#iplay1").removeAttr("checked");
    $("#packName1").val("");
    $("#iconName1").val("");
    $("#path1").val("");
    $("#filename1").val("");
    $("#packDesc1").val("");
    $("#version1").val("");
    $("#actionDate1").val("");
    $("#notify1").val("");
    $("#uniAction1").val("");
    $("#username1").val("");
    $("#password1").val("");
    $("#domain1").val("");
    $("#distCheck1").removeAttr("checked");
    $("#dPath1").val("");
    $("#dTime1").val("");
    $("#dvPath1").val("");
    $("#preDisCheck1").removeAttr("checked");
    $(".preinstcheck1").removeAttr("checked");
    $("#pfilePath1").val("");
    $("#pSoftName1").val("");
    $("#pSoftVer1").val("");
    $("#pKb1").val("");
    $("#pServicePack1").val("");
    $("#rootKey1").removeAttr("selected");
    $("#subKey1").val("");
    $("#global1").removeAttr("checked");
    $("#global").removeAttr("checked");
    $("#dvPath1").val("");
    $("#distributeNow").removeAttr("checked");
    $("#distributeNow").val("0");
    $("#executeNow").removeAttr("checked");
    $("#executeNow").val("0");
    $("#edconfig").val("");
    $("#edconfig").html("");
    $("#edconfig").hide();
    $("#siteArray1").val("");
}

$('#configurePackage').click(function () {
    var id = $('#id1').val();
    if (id == '') {
        $.notify("Please select a record to configure");
    } else {
        rightContainerSlideOn('rsc-distribute-package');
        const url = $('#iFrameConfigurePackageFormURLTemplate').val() + id;
        $('#iFrameConfigurePackageForm').attr('src', url);
        document.getElementById('iFrameConfigurePackageForm').contentDocument.location.reload(true);
    }
});


function get32bitConfig(id){
    var mdata = {
        'function':'get32bitConfig',
        'id' : id,
        'csrfMagicToken': csrfMagicToken
    };
    $.ajax({
            url: 'SWD_Function.php',
            type: 'POST',
            data: mdata,
            dataType: 'json',
            success: function(data) {
                var line1 = data.line1;
                var line2 = data.line2;
                var line3 = data.line3;
                var line4 = data.line4;
                var line5 = data.line5;
                var line6 = data.line6;
                    line1 = JSON.parse(line1);
                line2 = JSON.parse(line2);
                // line3 = JSON.parse(line3);
                // line4 = JSON.parse(line4);
                // line5 = JSON.parse(line5);
                // line6 = JSON.parse(line6);

                if(line1.line1_enable === '1' && line2.line2_enable !== '1') {
                    $('#deployconfig').prop('checked',true);
                    $('#executeconfig').prop('checked',false);
                    $('#deployexecute').prop('checked',false);
                    $('#32deployclick_div').show();
                    $('#32executeclick_div').hide();
                    render32DivData(line1,'line1');

                } else if(line1.line1_enable !== '1' && line2.line2_enable === '1') {
                    $('#deployconfig').prop('checked',false);
                    $('#executeconfig').prop('checked',true);
                    $('#deployexecute').prop('checked',false);
                    $('#32executeclick_div').show();
                    $('#32deployclick_div').hide();
                    render32DivData(line2,'line2');

                }else if(line1.line1_enable === '1' && line2.line2_enable === '1') {
                    $('#deployconfig').prop('checked',false);
                    $('#executeconfig').prop('checked',false);
                    $('#deployexecute').prop('checked',true);
                    $('#32deployclick_div').show();
                    $('#32executeclick_div').show();
                    render32DivData(line1,'line1');
                    render32DivData(line2,'line2');
                }

            },
            error: function(data){
                console.log("error");
            }
        });
}


function get64bitConfig(id){
    var mdata = {
        'function':'get64bitConfig',
        'id' : id,
        'csrfMagicToken': csrfMagicToken
    };
    $.ajax({
            url: 'SWD_Function.php',
            type: 'POST',
            data: mdata,
            dataType: 'json',
            success: function(data) {
                var line1 = data.line1;
                var line2 = data.line2;
                    line1 = JSON.parse(line1);
                line2 = JSON.parse(line2);

                if(line1.line1_enable === '1' && line2.line2_enable !== '1') {
                    $('#deployconfig_64').prop('checked',true);
                    $('#executeconfig_64').prop('checked',false);
                    $('#deployexecute_64').prop('checked',false);
                    $('#64deployclick_div_64').show();
                    $('#64executeclick_div_64').hide();
                    render64DivData(line1,'line1');
                }else if(line1.line1_enable !== '1' && line2.line2_enable === '1') {
                    $('#deployconfig_64').prop('checked',false);
                    $('#executeconfig_64').prop('checked',true);
                    $('#deployexecute_64').prop('checked',false);
                    $('#64executeclick_div_64').show();
                    $('#64deployclick_div_64').hide();
                    render64DivData(line2,'line2');
                }else if(line1.line1_enable === '1' && line2.line2_enable === '1') {
                    $('#deployconfig_64').prop('checked',false);
                    $('#executeconfig_64').prop('checked',false);
                    $('#deployexecute_64').prop('checked',true);
                    $('#64deployclick_div_64').show();
                    $('#64executeclick_div_64').show();
                    render64DivData(line2,'line2');
                    render64DivData(line1,'line1');
                }
            },
            error: function(data){
                console.log("error");
            }
        });
}


function getResetConfig(id){
    var mdata = {
        'function':'getResetConfig',
        'id' : id,
        'csrfMagicToken': csrfMagicToken
    };
    $.ajax({
            url: 'SWD_Function.php',
            type: 'POST',
            data: mdata,
            dataType: 'json',
            success: function(data) {
                var line3_32 = data.line3_32;
                var line4_32 = data.line4_32;
                var line5_32 = data.line5_32;
                var line6_32 = data.line6_32;
                var line3_64 = data.line3_64;
                var line4_64 = data.line4_64;
                var line5_64 = data.line5_64;
                var line6_64 = data.line6_64;

                line3_32 = JSON.parse(line3_32);
                line4_32 = JSON.parse(line4_32);
                line5_32 = JSON.parse(line5_32);
                line6_32 = JSON.parse(line6_32);
                line3_64 = JSON.parse(line3_64);
                line4_64 = JSON.parse(line4_64);
                line5_64 = JSON.parse(line5_64);
                line6_64 = JSON.parse(line6_64);
                if(line3_32.line3_url || ''  && line4_32.line4_url != '' || line3_64.line3_url != '' || line4_64.line4_url != ''){
                   $('#restartNHClientyes').prop('checked',true);
                   $('#restartNHClientno').prop('checked',false);
                   if(line3_32.line3_url != '' && line4_32.line4_url == '' && line3_64.line3_url == '' && line4_64.line4_url == ''){
                       $('#32deployclick_div_resetClient').show();
                       renderResetDivData(line3_32,'_resetClient32depl',"line3_32");
                   }else if(line3_32.line3_url == '' && line4_32.line4_url != '' && line3_64.line3_url == '' && line4_64.line4_url == ''){
                       $('#32executeclick_div_resetClient').show();
                       renderResetDivData(line4_32,'_resetClient32exec',"line4_32");
                   }else if(line3_32.line3_url != '' && line4_32.line4_url != '' && line3_64.line3_url == '' && line4_64.line4_url == ''){
                        $('#32deployclick_div_resetClient').show();
                       $('#32executeclick_div_resetClient').show();
                       renderResetDivData(line3_32,'_resetClient32depl',"line3_32");
                       renderResetDivData(line4_32,'_resetClient32exec',"line4_32");
                   }else if(line3_32.line3_url == '' && line4_32.line4_url == '' && line3_64.line3_url != '' && line4_64.line4_url == ''){
                       $('#64deployclick_div_resetClient').show();
                       renderResetDivData(line3_64,'_resetClient64depl',"line3_64");
                   }else if(line3_32.line3_url == '' && line4_32.line4_url == '' && line3_64.line3_url == '' && line4_64.line4_url != ''){
                       $('#64executeclick_div_resetClient').show();
                       renderResetDivData(line4_64,'_resetClient64exec',"line4_64");
                   }else if(line4_64.line4_url != '' && line3_64.line3_url != '' && line3_32.line3_url == '' && line4_32.line4_url == ''){
                        $('#64executeclick_div_resetClient').show();
                       renderResetDivData(line4_64,'_resetClient64exec',"line4_64");
                       $('#64deployclick_div_resetClient').show();
                       renderResetDivData(line3_64,'_resetClient64depl',"line3_64");
                   }else if(line4_64.line4_url != '' && line3_64.line3_url != '' && line3_32.line3_url != '' && line4_32.line4_url != ''){
                        $('#64executeclick_div_resetClient').show();
                       renderResetDivData(line4_64,'_resetClient64exec',"line4_64");
                       $('#64deployclick_div_resetClient').show();
                       renderResetDivData(line3_64,'_resetClient64depl',"line3_64");
                       $('#32deployclick_div_resetClient').show();
                       $('#32executeclick_div_resetClient').show();
                       renderResetDivData(line3_32,'_resetClient32depl',"line3_32");
                       renderResetDivData(line4_32,'_resetClient32exec',"line4_32");
                   }
                }else{
                   $('#restartNHClientyes').prop('checked',false);
                   $('#restartNHClientno').prop('checked',true);
                   $('#32deployclick_div_resetClient').hide();
                   $('#64deployclick_div_resetClient').hide();
                   $('#32executeclick_div_resetClient').hide();
                   $('#64executeclick_div_resetClient').hide();
                }

                if(line5_32.line5_url != '' || line6_32.line6_url != '' || line5_64.line5_url != '' || line6_64.line6_url != ''){
                   $('#restartPCyes').prop('checked',true);
                   $('#restartPCno').prop('checked',false);
                   if(line5_32.line5_url != '' && line6_32.line6_url == '' &&  line5_64.line5_url == '' && line6_64.line6_url == ''){
                       $('#32deployclick_div_resetPC').show();
                       renderResetDivData(line5_32,'_resetPC32depl',"line5_32");
                   }else if(line5_32.line5_url == '' && line6_32.line6_url != '' &&  line5_64.line5_url == '' && line6_64.line6_url == ''){
                       $('#32executeclick_div_resetPC').show();
                        renderResetDivData(line6_32,'_resetPC32exec',"line6_32");
                   }else if(line5_32.line5_url != '' && line6_32.line6_url != '' &&  line5_64.line5_url == '' && line6_64.line6_url == ''){
                       $('#32executeclick_div_resetPC').show();
                        renderResetDivData(line6_32,'_resetPC32exec',"line6_32");
                        $('#32deployclick_div_resetPC').show();
                       renderResetDivData(line5_32,'_resetPC32depl',"line5_32");
                   }else if(line5_32.line5_url == '' && line6_32.line6_url == '' &&  line5_64.line5_url != '' && line6_64.line6_url == ''){
                       $('#64deployclick_div_resetPC').show();
                       renderResetDivData(line5_64,'_resetPC64depl',"line5_64");
                   }else if(line5_32.line5_url == '' && line6_32.line6_url == '' &&  line5_64.line5_url == '' && line6_64.line6_url != ''){
                       $('#64executeclick_div_resetPC').show();
                       renderResetDivData(line6_64,'_resetPC64exec',"line6_64");
                   }else if(line5_32.line5_url == '' && line6_32.line6_url == '' &&  line6_64.line6_url != '' && line5_64.line5_url != ''){
                        $('#64executeclick_div_resetPC').show();
                       renderResetDivData(line6_64,'_resetPC64exec',"line6_64");
                       $('#64deployclick_div_resetPC').show();
                       renderResetDivData(line5_64,'_resetPC64depl',"line5_64");
                   }else if(line5_32.line5_url != '' && line6_32.line6_url != '' &&  line6_64.line6_url != '' && line5_64.line5_url != ''){
                       $('#32executeclick_div_resetPC').show();
                        renderResetDivData(line6_32,'_resetPC32exec',"line6_32");
                        $('#32deployclick_div_resetPC').show();
                       renderResetDivData(line5_32,'_resetPC32depl',"line5_32");
                       $('#64executeclick_div_resetPC').show();
                       renderResetDivData(line6_64,'_resetPC64exec',"line6_64");
                       $('#64deployclick_div_resetPC').show();
                       renderResetDivData(line5_64,'_resetPC64depl',"line5_64");
                   }
                }else{
                   $('#restartPCyes').prop('checked',false);
                   $('#restartPCno').prop('checked',true);
                   $('#32deployclick_div_resetPC').hide();
                   $('#64deployclick_div_resetPC').hide();
                   $('#32executeclick_div_resetPC').hide();
                   $('#64executeclick_div_resetPC').hide();
                }

            },
            error: function(data){
                console.log("error");
            }
        });
}

function getPackageDetails(id) {
    var mdata = {
        'function': 'getAllPackageDetails',
        'id': id,
        csrfMagicToken: csrfMagicToken
    };
    $.ajax({
        url: 'SWD_Function.php',
        type: 'POST',
        data: mdata,
        dataType: 'json',
        success: function (data) {
            $('#configpackagenameid').val(data.packagename);
            $('#configostypeid').val(data.platform);
            $('#configClick').val(data.posKeywords);
            $('#configDobleClick').val(data.negKeywords);
            $('input#logFileSave').val(data.logFilesToRead);

            if (data.preInstallMsg != '' || data.postDownloadMsg != '') {
                $('#Message_boxshow').show();
                $('#statusyes').prop('checked', true);
                $('#statusno').prop('checked', false);
            } else {
                $('#Message_boxshow').hide();
                $('#statusyes').prop('checked', false);
                $('#statusno').prop('checked', true);
            }
            $('#msgDownload').val(data.postDownloadMsg);
            $('#msgInstall').val(data.preInstallMsg);
            $('#maxpatchtime').val(data.maxTime);
            $('#processtokill').val(data.processToKill);

            if (data.configType == 'same') {
                $('#url_32val').val(data.path);
                $('#url_64val').val(data.path);

                $('#url_32valexec').val(data.path);
                $('#url_64valexec').val(data.path);
            } else {
                $('#url_32val').val(data.path);
                $('#url_64val').val(data.path2);

                $('#url_32valexec').val(data.path);
                $('#url_64valexec').val(data.path2);
            }

        },
        error: function (data) {
            console.log("error");
        }
    });
}



$('#deployconfig').click(function () {
    $('#32deployclick_div').show();
    $('#64deployclick_div').hide();
    $('#32executeclick_div').hide();
    $('#64executeclick_div').hide();
    $('#Deploy3264').show();
    $('#execute3264').hide();
    $('#deployexecute3264').hide();
    $("#executeconfig").prop("checked", false);
    $("#deployexecute").prop("checked", false);
});

$('#executeconfig').click(function () {
    $('#32deployclick_div').hide();
    $('#64deployclick_div').hide();
    $('#32executeclick_div').show();
    $('#64executeclick_div').hide();
    $('#deploy_click_option').hide();
    $('#Deploy3264').hide();
    $('#execute3264').show();
    $('#deployexecute3264').hide();
    $("#deployconfig").prop("checked", false);
    $("#deployexecute").prop("checked", false);
});

$('#deployexecute').click(function () {
    $('#32deployclick_div').show();
    $('#64deployclick_div').hide();
    $('#32executeclick_div').show();
    $('#64executeclick_div').hide();
    $('#deploy_click_option').hide();
    $('#Deploy3264').hide();
    $('#execute3264').hide();
    $('#deployexecute3264').show();
    $("#deployconfig").prop("checked", false);
    $("#executeconfig").prop("checked", false);
});

$('#deployconfig_64').click(function () {
    $('#32executeclick_div').hide();
    $('#64executeclick_div_64').hide();
    $('#32deployclick_div').hide();
    $('#64deployclick_div_64').show();
    $('#deploy_click_option_64').hide();
    $('#Deploy3264_64').show();
    $('#execute3264_64').hide();
    $('#deployexecute3264_64').hide();
    $("#executeconfig_64").prop("checked", false);
    $("#deployexecute_64").prop("checked", false);
});

$('#executeconfig_64').click(function () {
    $('#32deployclick_div').hide();
    $('#64deployclick_div_64').hide();
    $('#32executeclick_div').hide();
    $('#64executeclick_div_64').show();
    $('#deploy_click_option_64').hide();
    $('#Deploy3264_64').hide();
    $('#execute3264_64').show();
    $('#deployexecute3264_64').hide();
    $("#deployconfig_64").prop("checked", false);
    $("#deployexecute_64").prop("checked", false);
});

$('#deployexecute_64').click(function () {
    $('#64deployclick_div_64').show();
    $('#64executeclick_div_64').show();
    $('#deploy_click_option_64').hide();
    $('#Deploy3264_64').hide();
    $('#execute3264_64').hide();
    $('#deployexecute3264_64').show();
    $("#deployconfig_64").prop("checked", false);
    $("#executeconfig_64").prop("checked", false);
});

$('#restartNHClientyes').click(function () {
    //    $('#restartNHClient_div').show();
    $('#32deployclick_div_resetClient').show();
    $('#64deployclick_div_resetClient').show();
    $('#32executeclick_div_resetClient').show();
    $('#64executeclick_div_resetClient').show();
    $("#restartNHClientno").prop("checked", false);
    $('#restartClienturl').show();
    $('#restartClientpath').show();
});

$('#restartNHClientno').click(function () {
    $('#restartClienturl').hide();
    $('#restartClientpath').hide();
    $('#nhinstallpatch').val('');
    $('#32deployclick_div_resetClient').hide();
    $('#64deployclick_div_resetClient').hide();
    $('#32executeclick_div_resetClient').hide();
    $('#64executeclick_div_resetClient').hide();
    $('#restartNHClient_div').hide();
    $("#restartNHClientyes").prop("checked", false);
});

$('#restartPCyes').click(function () {
    $('#restartPCurl').show();
    $('#restartPCpath').show();
    $('#32deployclick_div_resetPC').show();
    $('#64deployclick_div_resetPC').show();
    $('#32executeclick_div_resetPC').show();
    $('#64executeclick_div_resetPC').show();
    //    $('#restartPC_div').show();
    $("#restartPCno").prop("checked", false);
});

$('#restartPCno').click(function () {
    $('#restartPCurl').hide();
    $('#restartPCpath').hide();
    $('#nhrestartpc').val('');
    $('#restartPC_div').hide();
    $("#restartPCyes").prop("checked", false);
    $('#32deployclick_div_resetPC').hide();
    $('#64deployclick_div_resetPC').hide();
    $('#32executeclick_div_resetPC').hide();
    $('#64executeclick_div_resetPC').hide();
});

$('#nhinstallpatch').change(function () {
    var value = $(this).val();
    if (value == 'restartclient_32dep') {
        $('#32deployclick_div_resetClient').show();
        $('#64deployclick_div_resetClient').hide();
    } else if (value == 'restartclient_64dep') {
        $('#32deployclick_div_resetClient').hide();
        $('#64deployclick_div_resetClient').show();
    } else if (value == 'restartclient_32exec') {
        $('#32executeclick_div_resetClient').show();
        $('#64executeclick_div_resetClient').hide();
    } else if (value == 'restartclient_64exec') {
        $('#32executeclick_div_resetClient').hide();
        $('#64executeclick_div_resetClient').show();
    } else if (value == 'restartclient_32exec,restartclient_32dep,restartclient_64exec,restartclient_64dep') {
        $('#32deployclick_div_resetClient').show();
        $('#64deployclick_div_resetClient').show();
        $('#32executeclick_div_resetClient').show();
        $('#64executeclick_div_resetClient').show();
    } else if (value == 'restartclient_32dep,restartclient_32exec' || value == 'restartclient_32exec,restartclient_32dep') {
        $('#32deployclick_div_resetClient').show();
        $('#64deployclick_div_resetClient').hide();
        $('#32executeclick_div_resetClient').show();
        $('#64executeclick_div_resetClient').hide();
    } else if (value == 'restartclient_64dep,restartclient_64exec' || value == 'restartclient_64exec,restartclient_64depl') {
        $('#32deployclick_div_resetClient').hide();
        $('#64deployclick_div_resetClient').show();
        $('#32executeclick_div_resetClient').hide();
        $('#64executeclick_div_resetClient').show();
    } else if (value == 'restartclient_32exec,restartclient_64exec' || value == 'restartclient_64exec,restartclient_32exec') {
        $('#32deployclick_div_resetClient').hide();
        $('#64deployclick_div_resetClient').hide();
        $('#32executeclick_div_resetClient').show();
        $('#64executeclick_div_resetClient').show();
    } else if (value == 'restartclient_32dep,restartclient_64dep' || value == 'restartclient_64dep,restartclient_32dep') {
        $('#32deployclick_div_resetClient').show();
        $('#64deployclick_div_resetClient').show();
        $('#32executeclick_div_resetClient').hide();
        $('#64executeclick_div_resetClient').hide();
    } else {
        $('#32deployclick_div_resetClient').hide();
        $('#64deployclick_div_resetClient').hide();
        $('#32executeclick_div_resetClient').hide();
        $('#64executeclick_div_resetClient').hide();
    }
});

$('#nhrestartpc').change(function () {
    var value = $(this).val();
    if (value == 'restartpc_32depl') {
        $('#32deployclick_div_resetPC').show();
        $('#64deployclick_div_resetPC').hide();
    } else if (value == 'restartpc_64depl') {
        $('#32deployclick_div_resetPC').hide();
        $('#64deployclick_div_resetPC').show();
    } else if (value == 'restartpc_32exec') {
        $('#32executeclick_div_resetPC').show();
        $('#64executeclick_div_resetPC').hide();
    } else if (value == 'restartpc_64exec') {
        $('#32executeclick_div_resetPC').hide();
        $('#64executeclick_div_resetPC').show();
    } else if (value == 'restartpc_32exec,restartpc_32depl,restartpc_64exec,restartpc_64depl') {
        $('#32deployclick_div_resetPC').show();
        $('#64deployclick_div_resetPC').show();
        $('#32executeclick_div_resetPC').show();
        $('#64executeclick_div_resetPC').show();
    } else if (value == 'restartpc_32depl,restartpc_32exec' || value == 'restartpc_32exec,restartpc_32depl') {
        $('#32deployclick_div_resetPC').show();
        $('#64deployclick_div_resetPC').hide();
        $('#32executeclick_div_resetPC').show();
        $('#64executeclick_div_resetPC').hide();
    } else if (value == 'restartpc_64exec,restartpc_64depl' || value == 'restartpc_64depl,restartpc_64exec') {
        $('#32deployclick_div_resetPC').hide();
        $('#64deployclick_div_resetPC').show();
        $('#32executeclick_div_resetPC').hide();
        $('#64executeclick_div_resetPC').show();
    } else {
        $('#32deployclick_div_resetPC').hide();
        $('#64deployclick_div_resetPC').hide();
        $('#32executeclick_div_resetPC').hide();
        $('#64executeclick_div_resetPC').hide();
    }
});

$('#statusyes').on('click', function () {
    $('#statusno').prop('checked', false);
    $('#Message_boxshow').show();
});

$('#statusno').on('click', function () {
    $('#statusyes').prop('checked', false);
    $('#Message_boxshow').hide();
});



