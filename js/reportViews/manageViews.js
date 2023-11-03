
$(document).ready(function() {
    getReports();
    addEventsToChartCheck();
});

function getReports() {
    $('#reportTable').DataTable().destroy();
    table1 = $('#reportTable').DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: true,
        searching: true,
        processing: true,
        serverSide: false,
        responsive: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        ajax: {
            url: "manageViewsFun.php?function=getReports"+"&csrfMagicToken=" + csrfMagicToken,
            type: "POST",
            rowId: 'id'
        },
        columns: [
            {"data": "name"},
            {"data": "created"},
            {"data": "username"}
        ],
        columnDefs: [
            {className: "table-plus", "targets": 0}
        ],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function(settings, json) {
            table1.$('tr:first').click();
            if(table1.$('tr:first').length === 0) {
                $('#disableView').hide();
                $('#enableView').hide();
                $('#editView').hide();
                $('#deleteView').hide();
            } else {
                 $('#disableView').show();
                $('#enableView').show();
                $('#editView').show();
                $('#deleteView').show();
            }
        },
        drawCallback: function(settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
            $(".se-pre-con").hide();
        }
    });
    $('#reportTable tbody').on('click', 'tr', function() {
        table1.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var rowdata = table1.row(this).data();
        name = rowdata.name;
        id = rowdata.DT_RowId;
        loadSectionsGrid(id);
        toggleEnableDisable(id);
    });
}

$("#salesinsight_searchbox").keyup(function (){
    table1.search(this.value).draw();
});

function loadSectionsGrid(reportId) {
    $('#reportSectionTable').DataTable().destroy();
    table2 = $('#reportSectionTable').DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: true,
        searching: true,
        processing: true,
        serverSide: false,
        responsive: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        ajax: {
            url: "manageViewsFun.php?function=getReportSections&repId=" + reportId +"&csrfMagicToken=" + csrfMagicToken,
            type: "POST",
            rowId: 'id'
        },
        columns: [
            {"data": "name"},
            {"data": "type"}
        ],
        columnDefs: [],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function(settings, json) {

        },
        drawCallback: function(settings) {

        }
    });
    loadSections();
}

function toggleEnableDisable(id) {
    $.ajax({
        url: "manageViewsFun.php?function=editViewDetails&viewId="+id+"&csrfMagicToken=" + csrfMagicToken,
        type: "POST",
        data: "",
        dataType:"json",
        success: function(data) {
            for (var value in data) {
                var status = data[value].status;
                if (status == 0) {
                    $("#disableView").hide();
                    $("#enableView").show();
                } else if (status == 1) {
                    $("#disableView").show();
                    $("#enableView").hide();
                } else {
                    $("#disableView").hide();
                    $("#enableView").hide();
                }
            }
            
        },
        error: function(err) {
            console.log(err.toString());
        }
    });
}

function submitReport() {
    $("#err1").html('');
    /*validation starts*/
    if ($("#view_name").val() == '') {
        $("#err1").html("<span>Please enter report name</span>");
        return false;
    }

    if ($("#view_name").val().length > 25) {
        $("#err1").html("<span>Report name cannot be more than 25 characters</span>");
        return false;
    }

    /*validation ends*/

    var cycType = $("#reportCycle").val();
    var day = 0;
    var weekday = 7;
    var hour = 0;
    var min = 0;

    var machGrp = $("#incuser").val();
    machGrp = machGrp.toString();

    var destination = $("#dest").val();
    destination = destination.toString();

    var infportal = 0;
    if (destination.indexOf("1") != -1) {
        infportal = 1;
    }
    var sections = $("#section").val();
    sections = sections.toString();

    var json_data = {};
    json_data.reportName = $("#view_name").val();
    json_data.reportGlobal = 1;
    json_data.envGlobal = $("#envGlobal").is(':checked') ? 1 : 0;
    json_data.includeMachGrp = machGrp;
    json_data.infPortal = infportal;
    json_data.emailList = $("#emailList").val();
    json_data.enabled = $("#enable").is(':checked') ? 1 : 0;
    json_data.type = type
    json_data.defEmail = $("#defEmail").is(':checked') ? 1 : 0;
    json_data.schedData = [cycType, day, weekday, hour, min];
    json_data.sections = sections;
    var sendData = JSON.stringify(json_data);

    $("#err").html('Adding..');
    $.ajax({
        type: "POST",
        url: "../lib/l-mngdRprt.php?function=1&functionToCall=addReport"+"&csrfMagicToken=" + csrfMagicToken,
        data: sendData,
        dataType: 'json'
    }).done(function(data) {
        $("#err").html('');
        $("#err").html(data.status);
        setTimeout(function() {
            $(".fclose").click();
            location.reload();
        }, 1500);
    });
}

function loadSections() {
    $.ajax({
        url: "manageViewsFun.php?function=getSections"+"&csrfMagicToken=" + csrfMagicToken,
        type: "POST",
        data: "",
        success: function(data) {
            var str = "";
            var allSections = JSON.parse(data);
            for (y in allSections) {
                str += '<div class="checkbox" localized=""><label localized=""><input type="checkbox" id="'+allSections[y].id+'" localized=""><span class="checkbox-material" localized=""><span class="check" localized=""></span></span>'+allSections[y].name+'</label></div>';
            }
            $("#allSections").html(str);
        },
        error: function(err) {
            console.log(err.toString());
        }
    });
}


function refresh() {
    location.reload();
}

function getAllSectionList() {
    var sectionList = "";
//    var tempSelected = [];
//    $(".selectSectionList").each(function() {
//        var selectedSection = $(this).val();
//        if(selectedSection !== ""){
//            tempSelected.push(selectedSection);
//        }
//    });
    for (var i = 0; i < tempSectionArr.length; i++) {
            sectionList += '<option value="' + tempSectionArr[i].id + '">' + tempSectionArr[i].name + '</option>';
    }
    return sectionList;
}

function getEditSectionList(sectionId) {
    var sectionList = "";
//    var tempSelected = [];
//    $(".selectSectionList").each(function() {
//        var selectedSection = $(this).val();
//        if(selectedSection !== ""){
//            tempSelected.push(selectedSection);
//        }
//    });
    for (var i = 0; i < tempSectionArr.length; i++) {
            if(sectionId == tempSectionArr[i].id){
                sectionList += '<option value="' + tempSectionArr[i].id + '" selected>' + tempSectionArr[i].name + '</option>';
            }else{
                sectionList += '<option value="' + tempSectionArr[i].id + '">' + tempSectionArr[i].name + '</option>';
            }
            
    }
    return sectionList;
}

function addSectionDropDwons(header, obj) {
    sectionNumber++;
    var sectionList = getAllSectionList();
    var newHeader = sectionNumber;
    var summarySections = '<div class="row clearfix summarySec" >' +
            '<div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">' +
            '<div class="add-sumsecdata">' +
            '<a href="javascript:" onclick="addSectionDropDwons('+newHeader+', this)"><i class="icon-ic_add_24px material-icons"></i></a>' +
            '<div class="form-group eventDuration">' +
            '<select class="form-control selectpicker selectSectionList" data-size="3" id="sectionSelect_'+newHeader+'">' +
            sectionList +
            '</select>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">' +
            '<div class="form-group checkbox inline">' +
            '<label localized="">'+
            '<input type="checkbox" name="'+newHeader+'enableGridData" class="enableGridData" id="sectionSelect_'+newHeader+'_Grid" localized=""><span class="checkbox-material" localized=""><span class="check" localized=""></span></span> Enable Grid'+
            '</label>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">' +
            '<div class="row clearfix ">' +
            '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">' +
            '<div class="form-group checkbox inline">' +
            '<label localized="">'+
            '<input type="checkbox" name="'+newHeader+'enableChart" class="enableChart" id="sectionSelect_'+newHeader+'_Chart" localized=""><span class="checkbox-material" localized=""><span class="check" localized=""></span></span> Enable Chart'+
            '</label>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">' +
            '<div class="remove-sumsecdata">' +
            '<div class="form-group">' +
            '<select class="form-control selectpicker" data-size="3" name="selectChart" disabled id="sectionSelect_'+newHeader+'_ChartType" onchange="getchartdetails(this);">' +
            '<option value="1" selected>Bar Chart</option>' +
            '<option value="2" class="piechart" style="display:none;">Pie Chart</option>' +
            '<option value="3">Line Chart</option>'+
            '</select>' +
            '</div>' +
            '<a href="#" ><i class="material-icons icon-ic_close_24px" onclick="removeSubHdr(this)"></i></a>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
    $(summarySections).insertAfter($(obj).parent().parent().parent());
    $('.add-report-popup').mCustomScrollbar('update');
    $('.selectpicker').selectpicker('refresh');
    addEventsToChartCheck();
}

function addEditSectionDropDwons(sectionId, gridEnabled, chartEnabled, chartType, filterCount) {
    var sectionList = getEditSectionList(sectionId);
    var newHeader = filterCount;
    var crossIcon = "";
    var gridEnabledCheck = "";
    var chartEnabledCheck = "";
    var chartTypeDropDown = "";
    var summarySections = "";
    if(filterCount == 0){
        crossIcon = '';
    }else{
        crossIcon = '<a href="#" localized=""><i class="material-icons icon-ic_close_24px" onclick="removeSubHdr(this)" localized=""></i></a>';
    }
    
    if(gridEnabled == 1){
        gridEnabledCheck = '<input type="checkbox" name="'+newHeader+'enableGridData" class="enableGridData" id="sectionSelect_'+newHeader+'_Grid" localized="" checked><span class="checkbox-material" localized=""><span class="check" localized=""></span></span> Enable Grid';
    }else{
        gridEnabledCheck = '<input type="checkbox" name="'+newHeader+'enableGridData" class="enableGridData" id="sectionSelect_'+newHeader+'_Grid" localized="" ><span class="checkbox-material" localized=""><span class="check" localized=""></span></span> Enable Grid';
    }
    
    if (chartEnabled == 1) {
        chartEnabledCheck = '<input type="checkbox" name="' + newHeader + 'enableGridData" class="edit_enableChart" id="sectionSelect_' + newHeader + '_Chart" localized="" checked><span class="checkbox-material" localized=""><span class="check" localized=""></span></span> Enable Chart';
        chartTypeDropDown = '<select class="form-control selectpicker" data-size="3" name="edit_selectChart" id="sectionSelect_' + newHeader + '_ChartType" onchange="getchartdetails(this);">' +
                '<option value="1">Bar Chart</option>' +
                '<option value="2" class="piechart">Pie Chart</option>' +
                '<option value="3">Line Chart</option>'+
                '</select>';
    } else {
        chartEnabledCheck = '<input type="checkbox" name="' + newHeader + 'enableGridData" class="edit_enableChart" id="sectionSelect_' + newHeader + '_Chart" localized=""><span class="checkbox-material" localized=""><span class="check" localized=""></span></span> Enable Chart';
        chartTypeDropDown = '<select class="form-control selectpicker" data-size="3" name="edit_selectChart" disabled id="sectionSelect_' + newHeader + '_ChartType" onchange="getchartdetails(this);">' +
                '<option value="1">Bar Chart</option>' +
                '<option value="2" class="piechart">Pie Chart</option>' +
                '<option value="3">Line Chart</option>'+
                '</select>';
    }
    
    var summarySections = '<div class="row clearfix summarySec" >' +
            '<div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">' +
            '<div class="add-sumsecdata">' +
            '<a href="javascript:" onclick="addSectionDropDwons('+newHeader+', this)"><i class="icon-ic_add_24px material-icons"></i></a>' +
            '<div class="form-group eventDuration">' +
            '<select class="form-control selectpicker selectSectionList" data-size="3" id="sectionSelect_'+newHeader+'">' +
            sectionList +
            '</select>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">' +
            '<div class="form-group checkbox inline">' +
            '<label localized="">'+ gridEnabledCheck +
            '</label>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">' +
            '<div class="row clearfix ">' +
            '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">' +
            '<div class="form-group checkbox inline">' +
            '<label localized="">'+ chartEnabledCheck +
            '</label>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">' +
            '<div class="remove-sumsecdata">' +
            '<div class="form-group">' + chartTypeDropDown +
            '</div>' + crossIcon +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
    $(".edit_sumSecData").append(summarySections);
    $('.edit-report-popup').mCustomScrollbar('update');
    if(chartEnabled == 1) {
        $('#sectionSelect_' + newHeader + '_ChartType').val(chartType);
    }
    $('.selectpicker').selectpicker('refresh');
    addEventsToChartCheck();
}

function removeSubHdr(obj) {
    $(obj).parent().parent().parent().parent().parent().parent().remove();
}

function addEventsToChartCheck() {
    $('.enableChart').change(function() {
        if ($(this).is(":checked")) {
            $(this).parent().parent().parent().parent().find('select[name=selectChart]').prop('disabled', false);
            $('.selectpicker').selectpicker('refresh');
        } else {
            $(this).parent().parent().parent().parent().find('select[name=selectChart]').prop('disabled', true);
            $('.selectpicker').selectpicker('refresh');
        }
    });
    
    $('.edit_enableChart').change(function() {
        if ($(this).is(":checked")) {
            $(this).parent().parent().parent().parent().find('select[name=edit_selectChart]').prop('disabled', false);
            $('.selectpicker').selectpicker('refresh');
        } else {
            $(this).parent().parent().parent().parent().find('select[name=edit_selectChart]').prop('disabled', true);
            $('.selectpicker').selectpicker('refresh');
        }
    });
}

function addView() {
    var jsonObj = [];
    var viewName = $("#viewName").val();
    var envGlobal = 0;
    var global = 1;
    $("#error1").html("");
    $("#error1").show();
    
//    if($("#env_global_view").is(":checked")){
//        envGlobal = 1;
//    }
//    
//    if($("#global_view").is(":checked")){
//        global = 1;
//    }
    
    if($.trim(viewName) == '') {
        $("#error1").html("Please provide view name");
        setTimeout(function () {
            $("#error1").fadeOut(3600);
        }, 3600);
        return false;
    
    }
    /*if(viewName != '') {
//        var filter = /^[a-z\d\_\s]+$/i;
        var filter = /^[a-zA-Z\d\ \s]+$/i;
        if (filter.test(viewName)) {
            $("#error1").html("Please provide valid view name");
            setTimeout(function() {
                $("#error1").fadeOut(3600);
            }, 3600);
            return false;
        }
    }*/
    
    $(".selectSectionList").each(function(index) {
        var secId = $(this).val();
        var sectionId = $(this).attr('id');
        var exist = false;
        if (secId !== "") {
            for (var i = 0; i < jsonObj.length; i++) {
                if (secId === jsonObj[i].secId) {
                    exist = true;
                }
            }
            if (exist === false) {
                var grid = $("#" + sectionId + "_Grid").is(":checked");
                var chart = $("#" + sectionId + "_Chart").is(":checked");
                var chartType = 0;
                if(chart){
                    chartType = $("#" + sectionId + "_ChartType").val();
                }
                
                item = {};
                item ["secId"] = secId;
                item ["grid"] = grid;
                item ["chart"] = chart;
                item ["charttype"] = chartType;
                jsonObj.push(item);
            }
        }
    });
    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=createView&viewName="+viewName+"&envGlobal=" + envGlobal + "&global=" + global + "&sectionJsonData=" + JSON.stringify(jsonObj) +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'
    }).done(function(response) {
        if(response.status == "SUCCESS"){
            $("#error1").html("");
            $("#error").html("View created successfully");
            getReports();
            setTimeout(function(){ 
                $('#managed-report').modal('hide'); 
            }, 3000);
        }else{
            $("#error").html("");
             $("#error1").show();
            $("#error1").html("Given view name already in use.");
        }
    });

}

function updateView() {
    var jsonObj = [];
    var viewId = $("#edit_hiddenViewId").val();
    var viewName = $("#edit_viewName").val();
    var envGlobal = 0;
    var global = 1;
    $("#editerror1").show();
    $("#editerror1").html("");
//    if($("#edit_env_global_view").is(":checked")){
//        envGlobal = 1;
//    }
//    
//    if($("#edit_global_view").is(":checked")){
//        global = 1;
//    }
    if($.trim(viewName) == '') {
        $("#editerror1").html("View name cannot be empty");
        setTimeout(function() {
            $("#editerror1").fadeOut(3600);
        }, 3600);
        return false;
    } 
    /*if(viewName != '') {
        var filter = /^[a-z\d\ \s]+$/i;
        if (filter.test(viewName)) {
            $("#editerror1").html("Please provide valid view name");
            setTimeout(function() {
                $("#editerror1").fadeOut(3600);
            }, 3600);
            return false;
        }
    }*/
    
    $("#edit_managed-report .selectSectionList").each(function(index) {
       
        var secId = $(this).val();
        var sectionId = $(this).attr('id');
        var exist = false;
        if (secId !== "") {
            for (var i = 0; i < jsonObj.length; i++) {
                if (secId === jsonObj[i].secId) {
                    exist = true;
                }
            }
            if (exist === false) {
                var grid = $("#edit_managed-report  #" + sectionId + "_Grid").is(":checked");
                
                var chart = $("#edit_managed-report  #" + sectionId + "_Chart").is(":checked");
                var chartType = 0;
                if(chart){
                    chartType = $("#edit_managed-report  #" + sectionId + "_ChartType").val();
                }
                
                edititem = {};
                edititem ["secId"] = secId;
                edititem ["grid"] = grid;
                edititem ["chart"] = chart;
                edititem ["charttype"] = chartType;
                jsonObj.push(edititem);
            }
        }
    });
    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=updateView&viewId="+viewId+"&viewName="+viewName+"&envGlobal=" + envGlobal + "&global=" + global + "&sectionJsonData=" + JSON.stringify(jsonObj) +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'
    }).done(function(response) {
        if(response.status == "SUCCESS"){
            $("#editerror1").html("");
            $("#editerror").html("View updated successfully");
            getReports();
            setTimeout(function(){ 
                location.reload();
            }, 3000);
        }else{
            $("#editerror").html("");
            $("#editerror1").html("Given view name already in use.");
        }
    });

}

function getEditReportDetails(){
    viewId =  $('#reportTable tbody tr.selected').attr('id');//$('#sectionId').val();
    $.ajax({
        url: 'manageViewsFun.php?function=editViewDetails&viewId=' + viewId +"&csrfMagicToken=" + csrfMagicToken,
        type: 'post',
        dataType: 'json',
    }).done(function(data) {
        $("#edit_hiddenViewId").val(viewId);
        for (var value in data) {
            var viewName = data[value].name;
            var global = data[value].global;
            var envglobal = data[value].envglobal;
            
            if(global == "1"){
                $('#edit_global_view').prop('checked', true);
            }else{
                $('#edit_global_view').prop('checked', false);
            }
            
            if(envglobal == "1"){
                $('#edit_env_global_view').prop('checked', true);
            }else{
                $('#edit_env_global_view').prop('checked', false);
            }
            
            $("#edit_viewName").val(viewName);
            addEditSectionDropDwons(data[value].sectionid,data[value].gridEnabled, data[value].chartEnabled,data[value].chartType, value);
        }
    });
}



function deleteReport() {
    var checkVal = '';
    $.ajax({
        type: "POST",
        url: "../lib/l-mngdRprt.php?function=1&functionToCall=checkAuth&id=" + id + "&type=" + type +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'
    }).done(function(data) {
        if (data.auth == 'yes') {
            $.ajax({
                type: "POST",
                url: "../lib/l-mngdRprt.php?function=1&functionToCall=deleteReport&id=" + id + "&type=" + type +"&csrfMagicToken=" + csrfMagicToken,
                dataType: 'json'
            }).done(function() {
                location.reload();
            });
        }
        else {
            $("#infoMsg").html("<span>You Are not authorised to delete this report</span>");
            $("#notification").modal("show");
        }
    });
}

function editReport() {

    if (id === '' || id == undefined) {
        $("#infoMsg").html("<span>No report to edit</span>");
        $("#notification").modal("show");
    } else {
        $("#edit_asset_report").modal("show");
        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getReportEditData&name=" + name + "&reportType=" + type + "&id=" + id +"&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json'
        }).done(function(data) {
            $("#editSelect").val(data.id);
            $("#editname").val(name);
            $("#editnamediv").removeClass('is-empty');
            $("#editincuser").html(data.include);
            $('.selectpicker').selectpicker('refresh');
            if (data.global == 1) {
                $("#editglobal").attr("checked", "checked");
            }
            if (data.status == 1) {
                $("#editenable").attr("checked", "checked");
            }
            if (data.envglobal == 1) {
                $("#editenvGlobal").attr("checked", "checked");
            }

            $('#editreportCycle option[value="' + data.schedtype + '"]').attr("selected", "selected");
            $('#editweekDay option[value="' + data.weekday + '"]').attr("selected", "selected");
            $('#editDay option[value="' + data.mnthday + '"]').attr("selected", "selected");
            $('#edithour option[value="' + data.hour + '"]').attr("selected", "selected");
            $('#editmin option[value="' + data.min + '"]').attr("selected", "selected");

            editshowRprtCycOptn(document.getElementById('editreportCycle'));

            if (data.infportal == 1) {
                $("#editdest").val("1").change();
                $('.selectpicker').selectpicker('refresh');
            }
            if (data.emaillist != '') {
                $("#editdest").val("2").change();
                $('.selectpicker').selectpicker('refresh');
                $("#editemailList").show();
                $("#editemailList").val(data.emaillist);
            }

            $("#editsection").html(data.sections);

            edit_include_users();
            edit_section_display();

            $("#editsection").selectpicker("refresh");
        });
    }
}

function deleteSelectedView() {
    var selectedViewId = $('#reportTable tbody tr.selected').attr('id');
    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=deleteView&viewId=" + selectedViewId +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'
    }).done(function(data) {
        if(data.status === "SUCCESS"){
            $("#delete-view").modal("hide");
            $("#success-modal h2").css("color","green");
            $("#success-modal h2").html(data.status);
            $("#success-modal h5").html(data.message);
            $("#success-modal").modal("show");
            getReports();
        }else{
           $("#delete-view").modal("hide");
            $("#success-modal h2").css("color","red");
            $("#success-modal h2").html(data.status);
            $("#success-modal h5").html(data.message);
            $("#success-modal").modal("show"); 
        }

    });
}

function disableSelectedView(){
    var selectedViewId = $('#reportTable tbody tr.selected').attr('id');
    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=disableView&viewId=" + selectedViewId +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'
    }).done(function(data) {
        if(data.status === "SUCCESS"){
            $("#disable-view").modal("hide");
            $("#success-modal h2").css("color","green");
            $("#success-modal h2").html(data.status);
            $("#success-modal h5").html(data.message);
            $("#success-modal").modal("show");
            getReports();
        }else{
           $("#disable-view").modal("hide");
            $("#success-modal h2").css("color","red");
            $("#success-modal h2").html(data.status);
            $("#success-modal h5").html(data.message);
            $("#success-modal").modal("show"); 
        }

    });
}

function enableSelectedView(){
    var selectedViewId = $('#reportTable tbody tr.selected').attr('id');
    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=enableView&viewId=" + selectedViewId +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'
    }).done(function(data) {
        if(data.status === "SUCCESS"){
            $("#enable-view").modal("hide");
            $("#success-modal h2").css("color","green");
            $("#success-modal h2").html(data.status);
            $("#success-modal h5").html(data.message);
            $("#success-modal").modal("show");
            getReports();
        }else{
           $("#enable-view").modal("hide");
            $("#success-modal h2").css("color","red");
            $("#success-modal h2").html(data.status);
            $("#success-modal h5").html(data.message);
            $("#success-modal").modal("show"); 
        }

    });
}


$('#edit_managed-report').on('hidden.bs.modal', function() {

    $('.form-group input').val('');
    $('.form-group select').val('');
    $('input:checkbox').removeAttr('checked');
    $(".edit_sumSecData").html('');
    
});


function getchartdetails(obj) {

    var sectionId = $(obj).val();
    
    $.ajax({
       type: 'POST',
       url: "manageViewsFun.php?function=getSectionData&sectionId=" + sectionId +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'text'
    }).done(function(data) {
//        console.log(data);
        if($.trim(data) == 1 || $.trim(data) == '1') {
            console.log("inside");
            $('.piechart').hide();
        } else {
             $('.piechart').show();
        }
        
        
        
        });
    
    
}
