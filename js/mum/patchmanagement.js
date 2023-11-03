$(document).ready(function () {
    var page = $('#pageName').html();
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var gridData = {};
    $('#patchAllListData').hide();
    $('#patchAllListData_wrapper').hide();
    $('.filter-div').hide();
    $('#showDeclinePatch').css("display", "block");
    $('#showApprovePatch').css("display", "block");
    $('#showRemovePatch').css("display", "block");
    $('#pageValue').val('normal');
    $('#loadermain').hide();
    $('#Actiontaken').html("Approve Patch");
    $("#declinepatchpage").val('');
    $('#criticalPage').val('');
    $('.tooltip').tooltip()
    $('.exportPatchData').show();
    // $(".filter-div").css("display", "block");
    // $(".filter-div").show();
    $(".setting_click").css("display", "block");
    $(".setting_click").show();
    $(".setting_click").css("display", "block");
    $(".setting_click").show();
    $(".setting_appr_click").css("display", "block");
    $(".setting_appr_click").show();
    $(".setting_appr_click").css("display", "block");
    $(".setting_appr_click").show();
    $(".takeaction").css("display", "block");
    $(".takeaction").show();
    $("#backbtn").css("display", "none");
    $("#backbtn").hide();
    $("input").change(function () {
    $(".circleGrey").css("background-color", "rgb(249, 75, 120)");
    });

    $(".setting_click").click(function () {
		$('#pageValue').val('normal');
        get_defaultconfiguration();
        $("#toggleButton").css("display", "none");
        $("#editOption").css("display", "block");

        var valueset = true;
        disableFields();
        var config = $('#config_value').val();
        if ((config == 'data') || (config == null) || (config == 'null') || (config != '')) {
        rightContainerSlideOn('settings-add-container');//to open slider
        } else if((config == 'nodata') || (config == null) || (config == 'null') || (config == '') ){
            $.notify("Default configurations are not available. Please configure settings for Patch Management");
            rightContainerSlideOn('settings-add-container');//to open slider
        }
    });

    $("#editOption").click(function () {
        $("#toggleButton").css("display", "block");
        $("#editOption").css("display", "none");
        enableFields();
    });

    $("#editOption2").click(function () {
        $("#toggleButton2").css("display", "block");
        $("#editOption2").css("display", "none");
        enableFields();
    });

    $("#editOption3").click(function () {
        $("#toggleButton3").css("display", "block");
        $("#editOption3").css("display", "none");
        enableFields();
    });

    $("#editOption4").click(function () {
        $("#toggleButton4").css("display", "block");
        $("#editOption4").css("display", "none");
        enableFields();
    });

     $("#editApprOption").click(function () {
        $("#toggleApprButton").css("display", "block");
        enableFields();
    });

    $(".closecross").click(function () {
        $("#toggleButton").css("display", "none");
        $("#toggleButton2").css("display", "none");
        $("#toggleButton3").css("display", "none");
        $("#toggleButton4").css("display", "none");
        $("#editOption").css("display", "block");
        $("#editOption2").css("display", "block");
        $("#editOption3").css("display", "block");
        $("#editOption4").css("display", "block");
        $("#toggleApprButton").css("display", "none");
        $("#editApprOption").css("display", "block");
        disableFields();
    });


    $(".icon-simple-remove").click(function () {
        $("#toggleButton").css("display", "none");
        $("#toggleButton2").css("display", "none");
        $("#toggleButton3").css("display", "none");
        $("#toggleButton4").css("display", "none");
        $("#editOption").css("display", "block");
        $("#editOption2").css("display", "block");
        $("#editOption3").css("display", "block");
        $("#editOption4").css("display", "block");
        if (page != 'Patch Management'){
          rightContainerSlideOn('settings-add-container');//to open slider
        }
    });

    $(".emprtyerror").css("display", "none");
    if(page == 'Patch Management Configure'){
        get_defaultconfiguration();
    }

    $("select.schedulewkly").change(function () {
        var selectedwk = $(this).children("option:selected").val();
        if (selectedwk == "daily") {
            $(".weeklydiv").css("display", "none");
            $(".monthlydiv").css("display", "none");
            $(".yearlydiv").css("display", "none");
        } else if (selectedwk == "weekly") {
            $(".weeklydiv").css("display", "block");
            $(".monthlydiv").css("display", "none");
            $(".yearlydiv").css("display", "none");
        } else if (selectedwk == "monthly") {
            $(".monthlydiv").css("display", "block");
            $(".weeklydiv").css("display", "none");
            $(".yearlydiv").css("display", "none");

        } else if (selectedwk == "yearly") {
            $(".yearlydiv").css("display", "block");
            $(".monthlydiv").css("display", "none");
            $(".weeklydiv").css("display", "none");

        }
    });

    // notification click action
    $("select.notifwkly").change(function () {
        var selectenotifdwk = $(this).children("option:selected").val();

        if (selectenotifdwk == "daily") {
            $(".notifweeklydiv").css("display", "none");
            $(".notifmonthlydiv").css("display", "none");
            $(".notifyearlydiv").css("display", "none");
        } else if (selectenotifdwk == "weekly") {
            $(".notifweeklydiv").css("display", "block");
            $(".notifmonthlydiv").css("display", "none");
            $(".notifyearlydiv").css("display", "none");
        } else if (selectenotifdwk == "monthly") {
            $(".notifmonthlydiv").css("display", "block");
            $(".notifweeklydiv").css("display", "none");
            $(".notifyearlydiv").css("display", "none");

        } else if (selectenotifdwk == "yearly") {
            $(".notifyearlydiv").css("display", "block");
            $(".notifmonthlydiv").css("display", "none");
            $(".notifweeklydiv").css("display", "none");

        }


    });

    MUM_RenderAllData();
    var funType;
    funType = "onload";
    $("#statusDIV").css("display", "none");
    $("#patchAllListData_wrapper").css("display", "block");


    $(".filterSubmit").click(function () {
        funType = "filtersubmit";
        patchlistData(funType,'filter',1);
    });

    $(".exportsubmit").click(function () {
        funType = "filtersubmit";
        patchlistData(funType,'export',1);
    });

    $(".settingsubmit").click(function () {
		$('#pageValue').val('normal');
        updatePatchmethod('settingsubmit');
    });

    $(".apprsettingsubmit").click(function () {
$('#pageValue').val('approve');
        updatePatchmethod('apprsettingsubmit');
    });

    $('input[name="mgmnt"]').click(function () {
        mgmntType = $("input[name='mgmnt']:checked").val();
        if (mgmntType == "5") {
            $(".mgmnt_hide").css("display", "none");
        } else {
            $(".mgmnt_hide").css("display", "block");
        }
    });

});


function checkFilter(){
    $('#datefrom').val('');
    $('#dateto').val('');
    $('#filter_title').html("Filter Patch Details");
    $('.exportsubmit').hide();
    $('.filterSubmit').show();
    var os = $('#OSTYPE').html();
    var action = $('#ActionType').html();
    var type = $('#PATCHTYPE').html();
    var statusVal = $('#PATCHSTATUS').html();

    if(os == 'All' && action == 'All' && type == 'All' && statusVal == 'All'){
        $('.platform').prop('checked', true);
        $('.patchtype').prop('checked', true);
        $('.statuscheck').prop('checked', true);
        $('#statusAll').prop('checked', true);
    }else {
        if(action == 'All'){
            $('#statusAll').prop('checked', true);
        }else if(action == 'Approved'){
            $('#action1').prop('checked', true);
        }else if(action == 'Declined'){
            $('#action2').prop('checked', true);
        }else if(action == 'Critical'){
            $('#action3').prop('checked', true);
        }else if(action == 'Removed'){
            $('#action4').prop('checked', true);
        }

        if(os == 'All'){
            $('.platform').prop('checked', true);
        }else{

            var OsArr = os.split(",");
            OsArr.forEach(function(number) {
                $("input[type=checkbox][value='"+number+"']").prop("checked",true);
            });
        }

        if(type == 'All'){
            $('.patchtype').prop('checked', true);
        }else{
            var typeArr = type.split(",");
            typeArr.forEach(function(number) {
                $("input[type=checkbox][value='"+number+"']").prop("checked",true);
            });
        }

        if(statusVal == 'All'){
            $('.statuscheck').prop('checked', true);
        }else{
            var statusValArr = statusVal.split(",");
            statusValArr.forEach(function(number) {
                $("input[type=checkbox][value="+number+"]").prop("checked",true);
            });
        }
    }


    rightContainerSlideOn('rsc-add-container34');
}


function get_defaultconfiguration() {
	var wintype = $('#pageValue').val();
    $.ajax({
        url: '../mum/mumfunctions.php',
        type: 'post',
        data:{'function':'getdefaultconfiguration','wintype':wintype, csrfMagicToken:csrfMagicToken},
        dataType: 'json',
        success: function (data) {
            var configjson = JSON.stringify(data);
            var jsonData = JSON.parse(configjson);
            console.log(jsonData);
            var selects;
            $('#config_value').val(jsonData.data);
		var apprid = jsonData.apprid;
		$('#hidden_approvedgrpid').val(apprid);
            if ((jsonData.data == 'data') || (jsonData.data == null) || (jsonData.data == 'null')) {
            	console.log(jsonData.installation);
            if ((jsonData.installation == 4) || (jsonData.installation == '4')) {
            	console.log("auto");
            	$('.automaticupdate').prop('checked',true);
                $('#selUpdateMethodVal').html('All updates approved automatically');
            } else if ((jsonData.installation == 1) || (jsonData.installation == '1')) {
            	console.log("manual");
                $('#selUpdateMethodVal').html('Manually approve updates');
                $('.manualupdate').prop('checked',true);
            }

            // schedule view data
            var schedhour = jsonData.schedhour;
            var schedminute = jsonData.schedminute;
            var schedday = jsonData.schedday;
            var schedmonth = jsonData.schedmonth;
            var schedweek = jsonData.schedweek;

            $('#sched_hour1').html(schedhour);
            $('#sched_min1').html(schedminute);
            $('#sched_month1').html(schedmonth);
            $('#sched_week1').html(schedweek);
            $('#sched_day1').html(schedday);

            if ((schedhour != 24) || (schedhour != '24')) {
                $('.schlehour').val(schedhour);
                $('.schlehourappr').val(schedhour);
            }
            if ((schedminute != 60) || (schedminute != '60')) {
                $('.schlemin').val(schedminute);
                $('.schleminappr').val(schedminute);
            }
            if ((schedday != 0) || (schedday != '0')) {
                $('.schedleday').val(schedday);
                $('.schleminappr').val(schedday);
            }
            if ((schedmonth != 0) || (schedmonth != '0')) {
                $('.schedlemon').val(schedmonth);
                $('.schleminappr').val(schedmonth);
            }
            if ((schedweek != 0) || (schedweek != '0')) {
                $('.schleweek').val(schedweek);
                $('.schleminappr').val(schedweek);
            }

            $('.schleedelay').val(jsonData.scheddelay);
            $('.schlerandomdelay').val(jsonData.schedrandom);

            $('#delay1').html(jsonData.scheddelay);
            $('#randomdelay1').html(jsonData.schedrandom);

            $('.schleedelayappr').val(jsonData.scheddelay);
            $('.schlerandomdelayappr').val(jsonData.schedrandom);

            if ((jsonData.schedtype == 1) || (jsonData.schedtype == '1')) {
                $('#actionmissed1').html('Run as soon as Possible');
                $('.scheduletypeasap').prop("checked", true);
                $('.scheduletypeasapappr').prop("checked", true);
            } else if ((jsonData.schedtype == 2) || (jsonData.schedtype == '2')) {
                $('#actionmissed1').html('Run at schedule time');
                $('.scheduletypetiming').prop("checked", true);
                $('.scheduletypetimingappr').prop("checked", true);
            }

            // notification view data

            var notifhour = jsonData.notifyhour;
            var notifminute = jsonData.notifyminute;
            var notifday = jsonData.notifyday;
            var notifmonth = jsonData.notifymonth;
            var notifweek = jsonData.notifyweek;

            $('#sched_hour2').html(schedhour);
            $('#sched_min2').html(schedminute);
            $('#sched_month2').html(schedmonth);
            $('#sched_week2').html(schedweek);
            $('#sched_day2').html(schedday);

            if ((notifhour != 24) || (notifhour != '24')) {
                $(".notifhour").val(notifhour);
                $(".notifhourappr").val(notifhour);
            }
            if ((notifminute != 60) || (notifminute != '60')) {
                $('.notifmin').val(notifminute);
                $('.notifminappr').val(notifminute);
            }
            if ((notifday != 0) || (notifday != '0')) {
                $('.notifday').val(notifday);
                $('.notifdayappr').val(notifday);
            }
            if ((notifmonth != 0) || (notifmonth != '0')) {
                $('.notifmon').val(notifmonth);
                $('.notifmonappr').val(notifmonth);
            }
            if ((notifweek != 0) || (notifweek != '0')) {
                   $('.notifwkly').val(notifweek);
                    $('.notifwklyappr').val(notifweek);
            }

            $('.notifrandomdelay').val(jsonData.notifyrandom);
            $('.notifrandomdelayappr').val(jsonData.notifyrandom);

            $('#delay2').html(jsonData.notifyrandom);
            $('#randomdelay2').html(jsonData.notifyrandom);

            if ((jsonData.notifytype == 1) || (jsonData.notifytype == '1')) {
                $('#actionmissed2').html('Run as soon as Possible');
                $('.notifasap').prop("checked", true);
                $('.notifasapappr').prop("checked", true);
            } else if ((jsonData.notifytype == 2) || (jsonData.notifytype == '2')) {
                $('#actionmissed2').html('Run at schedule time');
                $('.notiftiming').prop("checked", true);
                $('.notiftimingappr').prop("checked", true);
            }
                    $('#notifyText2').html(jsonData.notifytext);
                    $('#notif_text').val(jsonData.notifytext);
                    $('#notif_textappr').val(jsonData.notifytext);

            }
            }

    });
}

function MUM_RenderAllData() {
    var page = $('#pageName').html();
    if(page != 'Patch Management Configure'){
        mum_patchlistData(); //patch-list
    }
    if(page == 'Patch Management Configure'){
        mum_firstschceduleset();
    }
}

function exportPatchData(){
   $('#loadermain').hide();
    window.location.href = "../mum/mumfunctions.php?function=PatchExportDetails&csrfMagicToken="+csrfMagicToken;
    setTimeout(function(){
        $.notify('Patch Details Exported Successfully');
    },1000);

}

function myfunc(){
    rightMenuFunctionality();
    // alert('clicked');
}

/* MUM APPROVE GRID DATA FUNCTION */
function mum_patchlistData(wintype = '', nextPage= 1, notifSearch= '', key= '', sort= '') {
    $('#msgData').html('');

    notifSearch = $('#notifSearch').val();

    if (typeof notifSearch === 'undefined') {
        notifSearch = '';
    }

    checkAndUpdateActiveSortElement(key, sort);

    $('#loader').show();
    var search = $('#patchSearch').text();
    $('#declinepatchgridheader').hide();
    $('#removepatchgridheader').hide();
    $('#criticalupdatepatchgridheader').hide();
    $('#retrypatchgridheader').hide();
    $('#approvepatchgridheader').show();
    $('#approved_header').show();
    $('#declined_header').hide();
    $('#removed_header').hide();
    $('#mumfiltergrid').hide();
    $(".se-pre-con").show();


    $("#replace").text("Patch Management : " + search + "/Approve");
    if (search == 'All') {

        $("#replace").html('<h3 style="color:red;">please Select Site or Machine</h3>');
        MUM_RenderAllData();

    } else {
      $.ajax({
        url: "../mum/mumfunctions.php",
        type: "POST",
        data: {
          'function': 'getallpatchData',
          'type': wintype,
          'csrfMagicToken': csrfMagicToken,
          'limitCount': $('#notifyDtl_length :selected').val(),
          'nextPage': nextPage,
          'notifSearch': notifSearch,
          'order': key,
          'sort': sort
        },
        dataType: "json",
        success: function (gridData) {
          $('#loadermain').hide();
          $('.loader').hide();

          var Gridhtml = gridData.html;
          var totalCount = Gridhtml.length;
          if ($.trim(gridData.status) === 'enabled') {
            console.log("if");
            if ((totalCount == '0') || (totalCount == 0)) {
              $(".appr-btn-section").css("display", "none");
              $('.filter-div').hide();
              $('.msg-div').show();
              $('#patchAllListData').hide();
              $('#patchAllListData_wrapper').hide();
              $('.filter-div').hide();
              $('#msgData').html("Patches are not detected on this site");
            } else {
              // $('.filter-div').show();
              $('.msg-div').hide();
              $(".appr-btn-section").css("display", "block");
              $('#MUMMessage').html("");
              $('#patchAllListData').show();
              $('#patchAllListData_wrapper').show();
              // $('.filter-div').show();
              $("#patchAllListData").dataTable().fnDestroy();
              $('#loadermain').hide();
              groupTable = $('#patchAllListData').DataTable({
                scrollY: jQuery('#patchAllListData').data('height'),
                scrollCollapse: true,
                paging: false,
                searching: false,
                ordering: false,
                aaData: gridData.html,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                responsive: true,
                stateSave: true,
                processing: false,
                "stateSaveParams": function (settings, data) {
                  data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                  "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                  search: "_INPUT_",
                  searchPlaceholder: "Search records",
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                columnDefs: [{className: "checkbox-btn", "targets": [0]},
                  {"type": "date", "targets": [3]},
                  {
                    targets: "datatable-nosort",
                    orderable: false
                  }],
                initComplete: function (settings, json) {
                  $(".se-pre-con").hide();
                  $("th").removeClass('sorting_desc');
                  $("th").removeClass('sorting_asc');
                },
                drawCallback: function (settings) {
                  $(".se-pre-con").hide();
                  $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
                  // $(".dataTables_filter:first").replaceWith('<div id="notifyDtl_filter" class="dataTables_filter"><label><input type="text" class="form-control form-control-sm" placeholder="Search records" value="' + notifSearch + '" id="notifSearch" aria-controls="notifyDtl"></label></div>');
                }
              });
              $('.dataTables_filter input').addClass('form-control');
              $('.tableloader').hide();
            }
          } else {
            $('#loadermain').hide();
            $('.filter-div').hide();
            $('.msg-div').show();
            $('#patchAllListData').hide();
            $('#patchAllListData_wrapper').hide();
            $('#msgData').html("Patch Management is not enabled for this site.Enable DART 237 to use Patch Management on this site");
            return;
          }
        },
        error: function (msg) {

        }
      });

      var searchType = $('#searchType').val();
      var searchValue = $('#searchValue').val();
      var gridData = {};
      $('#loadermain').show();

      $('#patchAllListData tbody').on('click', 'tr', function () {
        groupTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var rowID = groupTable.row(this).data();
        if (rowID != 'undefined' && rowID !== undefined) {
          $('#selected_appr_patch').val(rowID[8]);
        }
      });

    }

}


$('body').on('click', '.page-link', function () {
  var nextPage = $(this).data('pgno');
  notifName = $(this).data('name');
  const activeElement = window.currentActiveSortElement;
  const key = (activeElement) ? activeElement.sort : '';
  const sort = (activeElement) ? activeElement.type : '';
  mum_patchlistData('', nextPage, '', key, sort);
})

$('body').on('change', '#notifyDtl_lengthSel', function () {
    mum_patchlistData('',1);
});
// $(document).on('keypress', function (e) {
//     if (e.which == 13) {
//         var notifSearch = $('#notifSearch').val();
//         if (notifSearch != '')
//         mum_patchlistData('',1);
//     }
// });


/* MUM SCHEDULE SET IF PCONFIGID IS NOT PRESENT IN DB */

function mum_firstschceduleset() {
    var name = $('#patchSearch').text();
    var scope =  $('#searchType').val();
    var scopeVal = $('#searchValue').val();
    $.ajax({
        url: '../mum/mumfunctions.php',
        type: 'post',
        data:{'function':'mumgetscheduleset','name':name,'scope':scope,'scopeVal':scopeVal,
                'csrfMagicToken': csrfMagicToken},
        dataType: 'json',
        success: function (data) {
            if (data.msg == 'windows') {
                $("#replace").html('<h3 style="color:red;">please enable the dart 237,inorder to use the MUM</h3>');
            } else if (data.msg == 'Mac') {
                $("#replace").html('<h3 style="color:red;">please enable the dart 1013,inorder to use the MUM.</h3>');
            }
        },
        error: function(error){
            console.log("error");
        }
    });
}

function declineCancel(){
    declinepatchdata();
}

/* MUM DECLINE GRID DATA FUNCTION */
function declinepatchdata() {
    $("#declinepatchpage").val('Decline Page');
    $('#Actiontaken').html("Decline Patch");
    $('.declinepatch').hide();
    $('#patchAllListData_wrapper').hide();
    $('#retrypatchListData').hide();
    $('#DeclinepatchListData').show();
    $('.kbs-container-hand').hide();
    $('.removepatch').hide();
    $('.setting_click').hide();
    $('.exportPatchData').hide();
    $('.retrypatch').hide();
    $('.setting_appr_click').hide();
    $('.retrypatchenable').hide();
    $('.criticalinstall').hide();
    $('.exportPatchDetails').hide();
    $('.machConfig').hide();
    $('#backbtn').html("Back");
    $('#backbtn').hide();
    $.ajax({
        url: '../mum/mumfunctions.php',
        type: 'post',
        data: {'function':'getapprovepatchData',
                'csrfMagicToken': csrfMagicToken},
        dataType: 'json',
        success: function (gridData) {
            console.log(gridData);
            console.log("success");
            var totalCount = gridData.length;
            if ((totalCount == '0') || (totalCount == 0)) {
                $(".decl-btn-section").css("display", "none");
            } else {
                $(".decl-btn-section").css("display", "block");
            }
            $(".se-pre-con").hide();
            $("#DeclinepatchListData").dataTable().fnDestroy();
            groupTable = $('#DeclinepatchListData').DataTable({
                scrollY: jQuery('#DeclinepatchListData').data('height'),
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
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                    targets: "datatable-nosort",
                    orderable: false
                }],
                initComplete: function (settings, json) {
                },
                drawCallback: function (settings) {
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {
            console.log("error");
        }
    });

    $('#DeclinepatchListData').on('click', 'tr', function () {
        var rowID = groupTable.row(this).data();
        $('#selectedpatchid').val(rowID[7]);
        $('#selectedpgroupid').val(rowID[8]);
    });

    $("#patchmanagment_searchbox").keyup(function () {
        groupTable.search(this.value).draw();
    });

    $('#DeclinepatchListData').DataTable().search('').columns().search('').draw();
}

/******MUM CRITICALUPDATE DATA*****/

function updateCriticalMethod() {
var crtiticalpage = $('#criticalPage').val('critical');
    $.ajax({
        url: "../mum/mumfunctions.php",
        type: "POST",
        data: {
            'function': 'getCriticalUpdatepatchData',
            'csrfMagicToken': csrfMagicToken
        },
        dataType: "json",
        success: function (gridData) {
            $('#loadermain').hide();
            $('.declinepatch').hide();
            $('#patchAllListData_wrapper').hide();
            $('#patchAllListData').hide();
            $('#criticalupdatepatchListData').show();
            $('.kbs-container-hand').hide();
            $('.setting_click').hide();
            $('.exportPatchData').hide();
            $('.retrypatch').hide();
            $('.removepatch').hide();
            $('.setting_appr_click').hide();
            $('.retrypatchenable').hide();
            $('.criticalinstall').hide();
            $('.exportPatchDetails').hide();
            $('.machConfig').hide();
            $('#backbtn').html("Back");
            $('#backbtn').hide();
            $("#criticalupdatepatchgridheader").show();
            $("#criticalupdatepatchListData").dataTable().fnDestroy();
            groupTable = $('#criticalupdatepatchListData').DataTable({
                scrollY: jQuery('#criticalupdatepatchListData').data('height'),
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
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                    targets: "datatable-nosort",
                    orderable: false
                }],
                initComplete: function (settings, json) {
                },
                drawCallback: function (settings) {
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });

    $('#criticalupdatepatchListData').on('click', 'tr', function () {
        var rowID = groupTable.row(this).data();
        groupTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var rowID = groupTable.row(this).data();
        if (rowID != 'undefined' && rowID !== undefined) {
        $('#selectedpgroupid').val(rowID[8]);
            }
    });

    $("#patchmanagment_searchbox").keyup(function () {
        groupTable.search(this.value).draw();
    });

    $('#criticalupdatepatchListData').DataTable().search('').columns().search('').draw();
}
/**********************/

/* MUM REMOVE GRID DATA FUNCTION */
function removepatchdata() {

    $.ajax({
        url: "../mum/mumfunctions.php",
        type: "POST",
        data: {
            'function': 'getremovepatchData',
            'csrfMagicToken': csrfMagicToken
        },
        dataType: "json",
        success: function (gridData) {
            $('#loadermain').hide();
            $('.declinepatch').hide();
            $('#patchAllListData_wrapper').hide();
            $('#patchAllListData').hide();
            $('#criticalupdatepatchListData').hide();
            $('#RemovepatchListData').show();
            $('.kbs-container-hand').hide();
            $('.setting_click').hide();
            $('.exportPatchData').hide();
            $('.retrypatch').hide();
            $('.removepatch').hide();
            $('.setting_appr_click').hide();
            $('.retrypatchenable').hide();
            $('.criticalinstall').hide();
            $('.exportPatchDetails').hide();
            $('.machConfig').hide();
            $('#backbtn').html("Back");
            $('#backbtn').hide();
            $('.removeselectedpatch').show();
            $('.takeaction').hide();
            $("#criticalupdatepatchgridheader").show();
            $("#RemovepatchListData").dataTable().fnDestroy();
            groupTable = $('#RemovepatchListData').DataTable({
                scrollY: jQuery('#RemovepatchListData').data('height'),
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
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                    targets: "datatable-nosort",
                    orderable: false
                }],
                initComplete: function (settings, json) {
                },
                drawCallback: function (settings) {

                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });

    $('#RemovepatchListData').on('click', 'tr', function () {
        var rowID = groupTable.row(this).data();
        groupTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var rowID = groupTable.row(this).data();
        console.log(rowID);
        $('#selectedpgroupid').val(rowID[7]);
    });

    $("#patchmanagment_searchbox").keyup(function () {
        groupTable.search(this.value).draw();
    });

    $('#RemovepatchListData').DataTable().search('').columns().search('').draw();
}

$("#topCheckBox").change(function() {

    if (this.checked) {
        $('.user_check').prop('checked', true);
        $("#retrypatchListData tbody tr").addClass("selected");
    } else {
        $('.user_check').prop('checked', false);
        $("#notifyDtl tbody tr").removeClass("selected");
    }
});

$("#topCheckBox2").change(function() {
    if (this.checked) {
        $('.user_check').prop('checked', true);
        // $("#patchAllListData tbody tr").addClass("selected");
    } else {
        $('.user_check').prop('checked', false);
        // $("#patchAllListData tbody tr").removeClass("selected");
    }
});

//MUM Retry Patch
function RetryPatchMethod() {
    $('#loadermain').show();
    $('#pageValue').val('retry');
	$('#retryPage').val('retry');
    $.ajax({
        url: "../mum/mumfunctions.php",
        type: "POST",
        data: {
            'function': 'getretrypatchData',
            'csrfMagicToken': csrfMagicToken
        },
        dataType: "json",
        success: function (gridData) {
            $('#loadermain').hide();
            $('.declinepatch').hide();
            $('#patchAllListData_wrapper').hide();
            $('#retrypatchListData').show();
            $('.kbs-container-hand').hide();
            $('.removepatch').hide();
            $('.setting_click').hide();
            $('.exportPatchData').hide();
            $('.retrypatch').hide();
            $('.setting_appr_click').hide();
            $('.retrypatchenable').hide();
            $('.criticalinstall').hide();
            $('.exportPatchDetails').hide();
            $('.machConfig').hide();
            $('#backbtn').html("Back");
            $('#backbtn').hide();
            $("#retrypatchListData").dataTable().fnDestroy();
            groupTable = $('#retrypatchListData').DataTable({
                scrollY: jQuery('#retrypatchListData').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                stateSave: true,
                responsive: true,
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                    targets: "datatable-nosort",
                    orderable: false
                }],
                initComplete: function (settings, json) {
                },
                drawCallback: function (settings) {
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {
        }
    });

    $('#retrypatchListData').on('click', 'tr', function () {
        var rowID = groupTable.row(this).data();
        groupTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var rowID = groupTable.row(this).data();
        if (rowID != 'undefined' && rowID !== undefined) {
        $('#selectedpgroupid').val(rowID[8]);
            }
    });

    $("#patchmanagment_searchbox").keyup(function () {
        groupTable.search(this.value).draw();
    });

    $('#retrypatchListData').DataTable().search('').columns().search('').draw();
}
/**********************/

/*==== patch Remove button code ========*/
function removePatch() {
    var ptchid = $('.actionchkptch:checked').map(function () {
        return $(this).attr('name');
    }).get();
    var pgrpid = $('.actionchkptch:checked').map(function () {
        return $(this).attr('id');
    }).get();


    if (ptchid != '' || pgrpid != '') {
        $.ajax({
            url: "../mum/mumfunctions.php",
            type: "POST",
            data: {
                'function': 'Remove_patch','check': ptchid, 'pgroupid': pgrpid,
                'csrfMagicToken': csrfMagicToken
            },
            success: function (data) {
                $.notify("Patch has been Removed");
                rightContainerSlideClose('takeaction-add-container');
                mum_patchlistData();
            }
        });
    } else {
        $.notify("Please select the record you want to remove");
    }
}

function Approvepatchdata() {
    mum_patchlistData();
}

function approveCancel() {
    mum_patchlistData();
}

function declineCancel() {
    declinepatchdata();
}
function criticalCancel() {
    updateCriticalMethod();
}
function retryCancel() {
    RetryPatchMethod();
}

function removeCancel() {
    removepatchdata();
}

/* === Multiple check selection code === */
$("#topCheckBox").change(function () {
    if (this.checked) {
        $('.check').prop('checked', true);
    } else {
        $('.check').prop('checked', false);
    }
});

function BacktoMum() {
    $('#mum_maingrid').show();
    $('#mumfiltergrid').hide();
}

/**************MUM EXPORT functionality starts here(pushpa)***************/

function exportData(){
    $('#datefrom').val('');
    $('#dateto').val('');
    $('#loadermain').hide();
    $('#filter_title').html("Export Patch Details");
    $('.exportsubmit').show();
    $('.filterSubmit').hide();
	$('.patchTypeExpand').show();
    $('.platformExpand').show();
    var os = $('#OSTYPE').html();
    var action = $('#ActionType').html();
    var type = $('#PATCHTYPE').html();
    var statusVal = $('#PATCHSTATUS').html();
	if(os == 'All' && action == 'All' && type == 'All' && statusVal == 'All'){
    $('.platform').prop('checked', true);
    $('.patchtype').prop('checked', true);
    $('.statuscheck').prop('checked', true);
    $('#statusAll').prop('checked', true);
    }else {
        if(action == 'All'){
            $('#statusAll').prop('checked', true);
        }else if(action == 'Approved'){
            $('#action1').prop('checked', true);
        }else if(action == 'Declined'){
            $('#action2').prop('checked', true);
        }else if(action == 'Critical'){
            $('#action3').prop('checked', true);
        }else if(action == 'Removed'){
            $('#action4').prop('checked', true);
        }

        if(os == 'All'){
            $('.platform').prop('checked', true);
        }else{
            var OsArr = os.split(",");
            OsArr.forEach(function(number) {
                $("input[type=checkbox][value='"+number+"']").prop("checked",true);
            });
        }

        if(type == 'All'){
            $('.patchtype').prop('checked', true);
        }else{
            var typeArr = type.split(",");
            typeArr.forEach(function(number) {
                $("input[type=checkbox][value='"+number+"']").prop("checked",true);
            });
        }

        if(statusVal == 'All'){
            $('.statuscheck').prop('checked', true);
        }else{
            var statusValArr = statusVal.split(",");
            statusValArr.forEach(function(number) {
                $("input[type=checkbox][value="+number+"]").prop("checked",true);
            });
        }
    }
    rightContainerSlideOn('rsc-add-container34');
}

function exportPATCH_Details() {
    funType = "filtersubmit";
    patchlistData(funType,'export');
    }

/************* New UI Functionality(pushpa) ******************/
function updatePatchmethod(type) {
    var window = $('#pageValue').val();
    var scope = $('#selected_scope').val();
    var scopeVal = $('#selected_scopeVal').val();
    var updatemethod;
    updatemethod = $("input[name='updatemethod']:checked").val();

    //schedule options
    var scheduledelay;
    var sdelayoper;
    var scheduleaction;
    var schedlemin;
    var schedlehour;
    var schedlweek;
    var schedlemon;
    var schedleday;

    if(type == 'settingsubmit'){
        schedlemin = $(".schlemin option:selected").val();
        schedlehour = $(".schlehour option:selected").val();
        schedlweek = $(".schleweek option:selected").val();
        schedlemon = $(".schedlemon option:selected").val();
        schedleday = $(".schedleday option:selected").val();
        scheduledelay = $(".schlerandomdelay").val();
        sdelayoper = $(".schleedelay").val();
        scheduleaction = $("input[name='schleradio']:checked").val();
        console.log(schedlehour,"schedlehour");
        console.log(schedleday,"schedleday");
    }else{
        schedlemin = $(".schleminappr option:selected").val();
        schedlehour = $(".schlehourappr option:selected").val();
        schedlweek = $(".schleweekappr option:selected").val();
        schedlemon = $(".schedlemonappr option:selected").val();
        schedleday = $(".schedledayappr option:selected").val();
        scheduledelay = $(".schlerandomdelayappr").val();
        sdelayoper = $(".schleedelayappr").val();
        scheduleaction = $("input[name='schleradioappr']:checked").val();
    }

    //notification options notif
    var notifhour;
    var notifmin;
    var notifweek;
    var notifmon;
    var notifday;
    var notifymins;
    var notifremnd;
    var notifprevsys;
    var notifschdlsop;
    var notif_text;
    var notirdelay;
    var notiaction;

    if(type == 'settingsubmit'){
        notifhour = $(".notifhour option:selected").val();
        notifmin = $(".notifmin option:selected").val();
        notifweek = $(".notifwkly option:selected").val();
        notifmon = $(".notifmon option:selected").val();
        notifday = $(".notifday option:selected").val();
        notirdelay = $(".notifrandomdelay").val();
        notif_text = $("#notif_text").val();
        notiaction = $("input[name='notifradio']:checked").val();
    }else{
        notifhour = $(".notifhourappr option:selected").val();
        notifmin = $(".notifminappr option:selected").val();
        notifweek = $(".notifwklyappr option:selected").val();
        notifmon = $(".notifmonappr option:selected").val();
        notifday = $(".notifdayappr option:selected").val();
        notirdelay = $(".notifrandomdelayappr").val();
        notif_text = $("#notif_textappr").val();
        notiaction = $("input[name='notifradioappr']:checked").val();
    }

    if ($(".notifymins").prop('checked') == true) {
        notifymins = $(".notinumb").val();
    } else {
        notifymins = "0";
    }
    if ($(".notifremnd").prop('checked') == true) {
        notifremnd = $(".notifremnd").val();
    } else {
        notifremnd = "0";
    }
    if ($(".notifprevsys").prop('checked') == true) {
        notifprevsys = $(".notifprevsys").val();
    } else {
        notifprevsys = "0";
    }
    if ($(".notifschdlsop").prop('checked') == true || $(".notifschdlsopappr").prop('checked') == true) {
        notifschdlsop = $(".notifschdlsop").val();
        notifschdlsop = $('.notifschdlsopappr').val();
    } else {
        notifschdlsop = "0";
        notifschdlsop = "0";
    }
	var type;
    var apprpgroupid = $('#hidden_approvedgrpid').val();
       var retryWindow = $('#retryPage').val();
var criticalPatch = $('#criticalPage').val();
	if(retryWindow == 'retry'){
       window = 'retry';
		type = 'retry';
       }
	if(criticalPatch == 'critical'){
	window = 'critical';
		type = 'critical';
	}
        $('#loader').show();
    $.ajax({
        url: '../mum/mumfunctions.php',
        type: "POST",
        data:{'function':'mumupdatemethod',
            'method':updatemethod,
            'sdelayoper':sdelayoper,
            'schedlemin':schedlemin,
            'schedlehour':schedlehour,
            'schedday':schedleday,
            'scheduleweek':schedlweek,
            'shedmonth':schedlemon,
            'scheduledelay':scheduledelay,
            'scheduleaction':scheduleaction,
            'notifmin':notifmin,
            'notifhour':notifhour,
            'notiweek':notifweek,
            'notifmon':notifmon,
            'notifday':notifday,
            'notirdelay':notirdelay,
            'notiaction':notiaction,
            'notif_text':notif_text ,
            'notiopt':notifymins,
            'notiremind':notifremnd,
            'notiprev':notifprevsys,
            'notisched':notifschdlsop,
            'apprpgroupid':apprpgroupid,
            'updatetype':type,
            'wintype':window,
            'scope' : scope,
            'scopeVal': scopeVal,
            'csrfMagicToken': csrfMagicToken
        },
        dataType: "json",
        success: function (data) {
            $('.loader').hide();
            console.log(data);
            if(data.msg == "success"){
                if(type == 'settingsubmit'){
                    $('#loader').show();
                $.notify("Settings have been updated successfully");
                rightContainerSlideClose('settings-add-container');
                rightContainerSlideClose('settings-appr-container');
                setTimeout(function(){
                    $('.loader').hide();
                    get_defaultconfiguration();
                    mum_firstschceduleset();
                    getSiteConfigDetails();
                },1000);
                location.reload();
                }else{
                    var pagetype = $('#declinepatchpage').val();
                    if( pagetype == 'Decline Page'){
                        var action = 'decline';
                        $('#Actiontaken').html("Decline Patch");
                    }else{
                        var action = 'approve';
                        $('#Actiontaken').html("Approve Patch");
                    }
                    actionSelection(action,type);
                }

            }else if(data.msg == "failed"){
                $.notify("Failed to add configuration. Please try again");
        }
        }
    });

}


function patchlistData(funtype,clickType,nextPage=1) {
    var notifSearch = $('#notifSearch').val();
    if (typeof notifSearch === 'undefined') {
        notifSearch = '';
    }
    var platformArray = [];
    var patchtypeArray = [];
    var ActionArray = [];
    var statustypeArray = [];
    var platform;
    var patchtype;
    var actionType;
    var patchstatus;
    var from;
    var to;
    var viewplatform;
    var viewmsg;
    var viewpatchtype;

    if (funtype == "onload") {

        platform = "'Windows 10','Windows 8','Windows 8.1','Windows 7','Ubuntu','Mac'";
        patchtype = "'0','1','3','4','5'";
        actionType = "approved";
        status = "";
        from = $(".frompatch").val();
        to = $(".topatch").val();

    } else if (funtype == "filtersubmit") {
        $(".setting_click").css("display", "none");
        $(".setting_click").hide();
        $('.exportPatchData').show();
        $(".takeaction").css("display", "none");
        $(".takeaction").hide();
        $("#backbtn").css("display", "block");
        $("#backbtn").hide();
        /* look for all patchtype that have a class 'platform' attached to it and check if it was checked */
        $(".platform:checked").each(function () {
            platformArray.push($(this).val());
        });
        $(".patchtype:checked").each(function () {
            patchtypeArray.push($(this).val());
        });

        /* we join the array separated by the comma */

        platform = "'" + platformArray.join("','") + "'";
        patchtype = "'" + patchtypeArray.join("','") + "'";
        viewplatform = platformArray.join(',');
        viewpatchtype = patchtypeArray.join(',');

        if ((viewpatchtype == '0') || (viewpatchtype == 0)) {
            viewpatchtype = "Undefined";
        } else if ((viewpatchtype == '2') || (viewpatchtype == 2)) {
            viewpatchtype = "Update";

        } else if ((viewpatchtype == '3') || (viewpatchtype == 3)) {
            viewpatchtype = "Roll Up";

        } else if ((viewpatchtype == '4') || (viewpatchtype == 4)) {
            viewpatchtype = "Security";

        } else if ((viewpatchtype == '5') || (viewpatchtype == 5)) {
            viewpatchtype = "Critical";
        }

        if($('#statusAll').is(':checked')) {
            var value = $('#statusAll').val();
        }else if($('#action1').is(':checked')){
            var value = $('#action1').val();
        }else if($('#action2').is(':checked')){
            var value = $('#action2').val();
        }else if($('#action3').is(':checked')){
            var value = $('#action3').val();
        }else if($('#action4').is(':checked')){
            var value = $('#action4').val();
        }
        $('#hiddenStatusValue').val(value);

        /**radio button value collected here */
        var actionTypeValue = $("#hiddenStatusValue").val();
        $(".actiontype:checked").each(function () {
            ActionArray.push($(this).val());
        });
        $(".statuscheck:checked").each(function () {
            statustypeArray.push($(this).val());
        });

        actionType = "'" + ActionArray.join("','") + "'";
        patchstatus = "'" + statustypeArray.join("','") + "'";

        from = $(".frompatch").val();
        to = $(".topatch").val();

        var start = (new Date(from).getTime());
        var end = (new Date(to).getTime());
        if(start > end ) {
            $.notify("Start Date is expected to be before the end date.");
            return;
        }

        if(clickType == 'filter'){
            $('#patchAllListData').hide();
        if(platform == "'all','Windows 10','Windows 7','Windows 8','Windows 8.1','Others'"){
            $('#OSTYPE').html('All');
        }else{
            platformnew = platform.replace(/'/g, '');
            platformnew = platformnew.charAt(0).toUpperCase() + platformnew.slice(1);
            $('#OSTYPE').html(platformnew);
        }

        if(patchtype == "'all','0','1','3','4','5'"){
            $('#PATCHTYPE').html('All');
        }else{
            var patchArr = [];
            patchtypenew = patchtype.split(",");
            patchtypenew.forEach(function(number) {
                  if(number == "'0'"){
                      patchArr.push('Undefined');
                  }else if(number == "'1'"){
                      patchArr.push('Update');
                  }else if(number == "'3'"){
                      patchArr.push('Roll Up');
                  }else if(number == "'4'"){
                      patchArr.push('Security');
                  }else if(number == "'5'"){
                      patchArr.push('Critical');
                  }
            });
            $('#PATCHTYPE').html(patchArr.toString());
        }

        if(patchstatus == "'all','detected','downloaded','installed','pendinginstall','declined','alreadyinstalled','pendinguninstall','scheduledinstall','scheduledunInstall','disable','uninstall','pendingdownload','pendingreboot','potentialinstallfailure','superseded','waiting'"){
            $('#PATCHSTATUS').html('All');
        }else{
            patchstatusnew = patchstatus.replace(/'/g, '');
//            patchstatusnew = patchstatusnew.charAt(0).toUpperCase() + patchstatusnew.slice(1);
            $('#PATCHSTATUS').html(patchstatusnew);
            $('#PATCHSTATUS').attr('title',patchstatusnew);
        }

        actionTypeValue = actionTypeValue.charAt(0).toUpperCase() + actionTypeValue.slice(1);
        $('#ActionType').html(actionTypeValue);
        var value = $('#hiddenStatusValue').val();
            if(value == 'All'){
                $('#showDeclinePatch').css("display", "block");
                $('#showApprovePatch').css("display", "block");
                $('#showRemovePatch').css("display", "block");
            }else if(value == 'approved'){
                $('#showDeclinePatch').css("display", "block");
                $('#showApprovePatch').css("display", "none");
                $('#showRemovePatch').css("display", "block");
            }else if(value == 'declined'){
                $('#showDeclinePatch').css("display", "none");
                $('#showApprovePatch').css("display", "block");
                $('#showRemovePatch').css("display", "block");
            }else if(value == 'critical'){
                $('#showDeclinePatch').css("display", "none");
                $('#showApprovePatch').css("display", "block");
                $('#showRemovePatch').css("display", "block");
            }
            $('#loadermain').show();
            $('#loader').show();
    $.ajax({
        url: '../mum/mumfunctions.php',
        type: "POST",
        data: {'function':'mumgetfilterData',
        'platform':platform,
        'type':patchtype,
        'actiontype':actionType,
        'patchstatus':patchstatus,
        'from':from,
        'to':to,
        'leveltype':clickType,
        'csrfMagicToken': csrfMagicToken,
        'limitCount': $('#notifyDtl_length :selected').val(),
        'nextPage': nextPage,
        'notifSearch': notifSearch
        },
        dataType: "json",
        success: function (gridData) {
            $('.loader').hide();
            $('#loadermain').hide();
            $('#patchAllListData').show();
            rightContainerSlideClose('rsc-add-container34');
            $("#patchAllListData").dataTable().fnDestroy();
            groupTable = $('#patchAllListData').DataTable({
                scrollY: jQuery('#patchAllListData').data('height'),
                scrollCollapse: true,
                paging: false,
                searching: false,
                ordering: false,
                aaData: gridData.html,
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
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                    targets: "datatable-nosort",
                    orderable: false
                }],
                initComplete: function (settings, json) {
                    $(".se-pre-con").hide();
                    $(".se-pre-con").hide();
                    $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
                    // $(".dataTables_filter:first").replaceWith('<div id="notifyDtl_filter" class="dataTables_filter"><label><input type="text" class="form-control form-control-sm" placeholder="Search records" value="' + notifSearch + '" id="notifSearch" aria-controls="notifyDtl"></label></div>');
                },
                drawCallback: function (settings) {

                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {
            console.log("error");
        }
    });
    }else{
        $.ajax({
        url: '../mum/mumfunctions.php',
        type: "POST",
        data:{'function':'mumgetfilterData',
        'platform':platform,
        'type':patchtype,
        'actiontype':actionType,
        'patchstatus':patchstatus,
        'from':from,
        'to':to,
        'leveltype': clickType,
        'csrfMagicToken': csrfMagicToken
        },
        dataType: "json",
        success: function (gridData) {
        window.location.href = '../mum/mumfunctions.php?function=MUMGetPatchStatusExport&csrfMagicToken'+csrfMagicToken;
            $.notify("Data Exported Successfully");
            $(".closebtn").trigger("click");
            $('#backbtn').hide();
            $('.setting_click').show();
            $('.exportPatchData').show();
        },
        error: function(error){
            console.log("error");
        }
        });
    }

        }


}

$(".takeaction").click(function (){
   var pagetype = $('#declinepatchpage').val();
    var ptchid = $('.actionchkptch:checked').map(function () {
        return $(this).attr('value');
    }).get();

    if ((ptchid == "") || (ptchid == undefined)) {
        rightContainerSlideClose('takeaction-add-container');
        $.notify("Please choose at least one record");
    } else {
       if( pagetype == 'Decline Page'){
                        var action = 'decline';
                        $('#Actiontaken').html("Decline Patch");
    actionSelection('decline');
       }else{
                        var action = 'approve';
                        $('#Actiontaken').html("Approve Patch");
        rightContainerSlideOn('settings-appr-container');
                    }

    }
});

function actionSelection(action,type) {
var page = $('#pageValue').val();
	if(type == 'retry'){
	page = 'retry';
	}
    $("#show_Error").css("display", "block !important");
    //    var action = $("#actionsel option:selected").val();
    var ptchid = $('.actionchkptch:checked').map(function () {
        return $(this).attr('value');
    }).get();
    var pgrpid = $('.actionchkptch:checked').map(function () {
        return $(this).attr('id');
    }).get();

//        var res = checkDuplicates(pgrpid);
console.log("action"+action+"ptchid="+ptchid+"pgrpid=>"+pgrpid);
    if (ptchid == '') {
        $.notify("Please Select a record");
    } else {
        if (action === "approve") {
console.log("inide");
    $('#loader').show();
            $.ajax({
                url: "../mum/mumfunctions.php" ,
                type: "POST",
                data: {
                    'function': 'Approve_patch', 'check': ptchid, 'pgroupid': pgrpid, 'page': page,
                    'csrfMagicToken': csrfMagicToken
                },
                success: function (data) {
                    $('#pageValue').val('');
			$('#retryPage').val('');
                    if($.trim(data) == 'success'){
                    $('.loader').hide();
                    $.notify("Patch has been Approved succesfully. It will take sometime to update the status");
                    rightContainerSlideClose('takeaction-add-container');
			    rightContainerSlideClose('settings-appr-container');
                            if(page == 'retry'){
                                retryCancel();
                            }else{
//                    setTimeout(function(){
                        mum_patchlistData();
//                    location.reload();
//                    },1000);
                            }

                    }else{
                        $.notify("Some error occurred. Please try again.");
                        rightContainerSlideClose('takeaction-add-container');
			rightContainerSlideClose('settings-appr-container');
//                    setTimeout(function(){
                        mum_patchlistData();
//                        location.reload();
//                    },1000);
                }

                }
            });

        } else if (action === "decline") {
            $.ajax({
                // url: "../mum/mumfunctions.php?function=Declinepatch&check=" + ptchid + "&pgroupid=" + pgrpid,
                url: "../mum/mumfunctions.php",
                type: "POST",
                data: { 'function': 'Declinepatch','check': ptchid,'pgroupid' : pgrpid,'csrfMagicToken': csrfMagicToken },
                success: function (data) {
                    $.notify("Patch has been Declined");
                    rightContainerSlideClose('takeaction-add-container');
//                    setTimeout(function(){
                        mum_patchlistData();
//                    location.reload();
//                    },1000);
                }
            });
        } else if (action === "retry") {

        }
    }

}

function getkbs(patchid, count) {
    $.ajax({
        url: "../mum/mumfunctions.php",
        type: "POST",
        dataType: 'json',
        data: {
            'function': 'Kbs_patchdetailList', 'patchid': patchid,
            'csrfMagicToken': csrfMagicToken },
        success: function (gridData) {
            rightContainerSlideOn('kbs-add-container');
            $('#serVerFile').html(gridData[0]['ServerFile']);
        },
        error: function (msg) {

        }
    });
}

function patchStatus_NewUI(pid, patchType) {
    rightContainerSlideOn('show-status-container');
    $('.retrypatch').show();
	$('.declinepatch').show();
	$('.criticalinstall').hide();
	$('.removepatch').hide();
	$('.machConfig').hide();
    $('.exportPatchData').show();
    $(".setting_click").css("display", "none");
    $(".setting_click").hide();
    $(".filter-div").css("display", "block");
    $(".filter-div").show();
    $("#backbtn").css("display", "block");
    $(".criticalinstall ").css("display", "none");
    $(".exportPatchDetails ").css("display", "block");
    $("#backbtn").hide();
    $(".takeaction").hide();
    $("#patchAllListData_wrapper").css("display", "block");
    $("#criticalupdatepatchListData_wrapper").hide();
    $("#statusDIV").css("display", "block");
    $.ajax({
        url: "../mum/mumfunctions.php",
        type: "POST",
        dataType: "json",
        data: {
            'function': 'get_patchStatusNewUIFunc','pid': pid, 'type': patchType,
            'csrfMagicToken': csrfMagicToken },
        success: function (gridData) {
            $(".se-pre-con").hide();
            $("#statusData").dataTable().fnDestroy();
            groupTable = $('#statusData').DataTable({
                scrollY: jQuery('#statusData').data('height'),
                scrollCollapse: true,
                paging: false,
                searching: false,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                stateSave: true,
                responsive: true,
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                    targets: "datatable-nosort",
                    orderable: false
                }],
                initComplete: function (settings, json) {
                },
                drawCallback: function (settings) {
                    $("#statusData_filter").hide();
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });

    $('#statusData').val(pid);
}

$('.kbs-container-close').click(function () {
    var targetId = $(this).attr('data-bs-target');
    var target = $('#' + targetId);

    target.removeClass('sm-3');
    target.removeClass('md-6');
    target.removeClass('lg-9');
    target.addClass('rightslide-container-hide');
    $('#absoFeed').css({
        'display': 'none',
        'width': '0px'
    });
});

$("#backbtn").click(function () {
    location.reload();
});

//$('.closebtn').click(function(){
//    // $('#datefrom').val('');
//    // $('#dateto').val('');
//    $('#schlerandomdelay').val('');
//    $('#notifrandomdelay').val('');
//    $(".pltmwin10").prop("checked", false);
//    $(".pltmwin7").prop("checked", false);
//    $(".pltmwin8").prop("checked", false);
//    $(".pltmwin81").prop("checked", false);
//    $(".others").prop("checked", false);
//    $(".ptchty_undef").prop("checked", false);
//    $(".ptchty_update").prop("checked", false);
//    $(".ptchty_rol").prop("checked", false);
//    $(".ptchty_sec").prop("checked", false);
//    $(".ptchty_criti").prop("checked", false);
//    $(".actiontype").prop("checked", false);
//    $(".statuscheck").prop("checked",false);
//});

$('.appr_setting_click').click(function(){
	$('#pageValue').val('approve');
    rightContainerSlideOn('settings-appr-container');

});

//Approved Patches Configuration -Manika
function Approve_PatchSettings(){
    var selected = $('#selected_appr_patch').val();
    var pgroupid = $('#hidden_pgroupid').val();
    if(selected === 'Approved'){
        rightContainerSlideOn('settings-appr-container');
        $("#toggleButton").css("display", "none");
        $("#editOption").css("display", "block");
        var valueset = true;
        disableFields();
        get_apprdefaultconfiguration();

    }else{
        $.notify("Please select an approved Patch");
    }
}

function get_apprdefaultconfiguration(){
    var name = $('#selected_appr_patchname').val();
    var pgroupid = $('#hidden_pgroupid').val();
    $.ajax({
        type: "POST",
        url: "../mum/mumfunctions.php",
        dataType: 'json',
        data: {
            'function': 'getapprdefaultconfiguration', 'name': name, 'pgroupid': pgroupid,
            'csrfMagicToken': csrfMagicToken },
                success: function(data) {

            var configjson = JSON.stringify(data);
            var jsonData = JSON.parse(configjson);
            console.log(jsonData.installation);
            $('#config_value').val(jsonData.data);
            if ((jsonData.data == 'data') || (jsonData.data == null) || (jsonData.data == 'null')) {
            if ((jsonData.installation == 4) || (jsonData.installation == '4')) {
                $(".automaticupdateappr").prop("checked", true);
            } else if ((jsonData.installation == 1) || (jsonData.installation == '1')) {
                $(".manualupdateappr").prop("checked", true);
            }

            // schedule view data
            var schedhour = jsonData.schedhour;
            var schedminute = jsonData.schedminute;
            var schedday = jsonData.schedday;
            var schedmonth = jsonData.schedmonth;
            var schedweek = jsonData.schedweek;
            var pgroupid = jsonData.pgroupid;
            if ((schedhour != 24) || (schedhour != '24')) {
                $(".schlehourappr").val(schedhour);
            }
            if ((schedminute != 60) || (schedminute != '60')) {
                $(".schleminappr").val(schedminute);
            }
            if ((schedday != 0) || (schedday != '0')) {
                $('.schedledayappr').val(schedday);
            }
            if ((schedmonth != 0) || (schedmonth != '0')) {
                $('.schedlemonappr').val(schedmonth);
            }
            if ((schedweek != 0) || (schedweek != '0')) {
                $('.schleweekappr').val(schedweek);
            }

            $(".schleedelayappr").val(jsonData.scheddelay);
            $(".schlerandomdelayappr").val(jsonData.schedrandom);

            if ((jsonData.schedtype == 1) || (jsonData.schedtype == '1')) {
                $(".scheduletypeasapappr").prop("checked", true);
            } else if ((jsonData.schedtype == 2) || (jsonData.schedtype == '2')) {
                $(".scheduletypetimingappr").prop("checked", true);
            }

            // notification view data

            var notifhour = jsonData.notifyhour;
            var notifminute = jsonData.notifyminute;
            var notifday = jsonData.notifyday;
            var notifmonth = jsonData.notifymonth;
            var notifweek = jsonData.notifyweek;

            if ((notifhour != 24) || (notifhour != '24')) {
                $(".notifhourappr").val(notifhour);
            }
            if ((notifminute != 60) || (notifminute != '60')) {
                $(".notifminappr").val(notifminute);
            }
            if ((notifday != 0) || (notifday != '0')) {
                $('.notifdayappr').val(notifday);
            }
            if ((notifmonth != 0) || (notifmonth != '0')) {
                $(".notifmonappr").val(notifmonth);
            }
            if ((notifweek != 0) || (notifweek != '0')) {
                $('.notifwklyappr').val(notifweek);
            }

            $(".notifrandomdelayappr").val(jsonData.notifyrandom);

            if ((jsonData.notifytype == 1) || (jsonData.notifytype == '1')) {
                $(".notifasapappr").prop("checked", true);
            } else if ((jsonData.notifytype == 2) || (jsonData.notifytype == '2')) {
                $(".notiftimingappr").prop("checked", true);
            }
                    $("#notif_textappr").val(jsonData.notifytext);

            }

                },
                error: function(error){
                     console.log("error");
                }
            });
}

$('.machConfig').click(function(){
    rightContainerSlideOn('machConfig-add-container');
    var sitename = $('#searchValue').val();
    $('#siteselected').val(sitename);
});


function MachineConfigsettings(){
    var type = $('#searchType').val();
    if(type == 'Sites'){
        var sitename = $('#siteselected').val();
        var valueselected = $("input[name='allsel']:checked").val();
        var serverSelected = $("input[name='sourceaction']:checked").val();
        var url = $('#susServer').val();
        var obj = {
            "function" : "update_MachConfig",
            "SiteName" : sitename,
            "SelectedVal" : valueselected,
            "ServerSelected" : serverSelected,
            "url": url,
            'csrfMagicToken': csrfMagicToken
        };
        $.ajax({
            type: "POST",
            url: "../mum/mumfunctions.php",
//            dataType: 'json',
            data:obj,
            success:function(data){
                if($.trim(data) == 'Success'){
                    $.notify("Successfully Updated");
                    rightContainerSlideClose('machConfig-add-container');
                    setTimeout(function(){
                        location.reload();
                    });
                }else{
                    $.notify("Some error occurred. Please try again.");
                    rightContainerSlideClose('machConfig-add-container');
                    setTimeout(function(){
                        location.reload();
                    });
}
            },
            error:function(error){
                console.log("error");
            }
        });
    }else{
        $('#siteselected').val('');
    }
}

$('#statusCheckAll').on('click',function(){
    var isChecked = $(this).is(':checked');
    checkUncheckItems(isChecked,'statusType');
});

$('#windowsCheckAll').on('click',function(){
    var isChecked = $(this).is(':checked');
    checkUncheckItems(isChecked,'winType');
})

$('#patchtypeCheck').on('click',function(){
    var isChecked = $(this).is(':checked');
    checkUncheckItems(isChecked,'patchType');
});

function checkUncheckItems(check,type){
    if(type == 'statusType'){
       var items = $('.statuscheck');
    }else if(type == 'winType'){
       var items = $('.platform');
    }else if(type == 'patchType'){
       var items = $('.patchtype');
    }

    $.each(items, function(){
        if(check){
            $(this).prop('checked', true);
        } else {
            $(this).prop('checked', false);
        }
    });

    return true;
}

$('#statusAll').on('click',function(){
    var value = $('#statusAll').val();
    $('#hiddenStatusValue').val(value);
});

$('#action1').on('click',function(){
    var value = $('#action1').val();
    $('#hiddenStatusValue').val(value);
});

$('#action2').on('click',function(){
    var value = $('#action2').val();
    $('#hiddenStatusValue').val(value);
});

$('#action3').on('click',function(){
    var value = $('#action3').val();
    $('#hiddenStatusValue').val(value);
});

$('#action4').on('click',function(){
    var value = $('#action4').val();
    $('#hiddenStatusValue').val(value);
});

const checkSelectedPatch = () => {
  return new Promise((resolve, reject) => {
    if ($('.actionchkptch:checked').length > 0) {
      var pStatusElement = "";
      $('.actionchkptch:checked').map(function () {
        if (pStatusElement != "") {
          if ($(this.parentElement.parentElement.parentElement.parentElement.children[1]).html() != pStatusElement) {
            $.notify("Please select patches with similar status to perform bulk actions");
            pStatusElement = "error";
          } else {
            // alert(pStatusElement);
          }
        } else {
          pStatusElement = $(this.parentElement.parentElement.parentElement.parentElement.children[1]).html();
        }
      })
    }
    resolve(pStatusElement);
  })
}

$('.btn-setting-patch-management').click(function () {
  checkSelectedPatch().then((result) => {
    if (result == "-"){
      $('.checkUIbtn-approve').css('display','block');
      $('.checkUIbtn-decline').css('display','block');
      $('.checkUIbtn-remove').css('display','block');
      $('.dropdown-menu').css('display','block');
    }
    if (result == "Approved"){
      $('.checkUIbtn-approve').css('display','none');
      $('.checkUIbtn-decline').css('display','block');
      $('.checkUIbtn-remove').css('display','block');
      $('.dropdown-menu').css('display','block');
    }
    if (result == "Declined"){
      $('.checkUIbtn-approve').css('display','block');
      $('.checkUIbtn-decline').css('display','none');
      $('.checkUIbtn-remove').css('display','block');
      $('.dropdown-menu').css('display','block');
    }
    if (result == "Removed"){
      $('.checkUIbtn-decline').css('display','none');
      $('.checkUIbtn-remove').css('display','none');
      $('.dropdown-menu').css('display','block');
    }
    if (result == "error"){
      $('.dropdown-menu').css('display','none');
    }
  });
})
