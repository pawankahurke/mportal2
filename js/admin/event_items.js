$(document).ready(function () {

    $(function () {
        $('#Availability').addClass('chngHigh');
        itemtype = 5;
        getEventsDet(itemtype);
        sectionNumber = 1;
        sectionNumberEdit = 1;
    });

    //eventItems_datatable();
    savedSearch();
    //$("#EventItemTitle").html("<span>Compliance : </span>" + elementName);
    var user_table = '';
    var temp;
    var h = window.innerHeight;
//user_datatable();
//No of rows for Datatable need to increase if page is opening on high resolution screen
    if (h > 700) {
        $("#eventItems_datatable").attr("data-page-length", "50");
    } else {
        $("#eventItems_datatable").attr("data-page-length", "25");
    }

});



function eventItems_datatable() {
    $("#userSearchValue").html($("#searchLabel").val());
    var text = '';

    $("#eventItems_datatable").dataTable().fnDestroy();
    var data = "function=CUSAJX_eventItemsGridData&csrfMagicToken=" + csrfMagicToken;
    //var encodedData = get_RSA_EnrytptedData(data);

    userTable = $('#eventItems_datatable').DataTable({
        scrollY: jQuery('#eventItems_datatable').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        searching: true,
        processing: true,
        //serverSide: true,
        bAutoWidth: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        //caseInsensitive: true,
        ajax: {
            url: "../lib/l-custAjax.php?" + data,
            type: "POST",
            dataType: "json",
            //data: {data:data},
        },
        language: {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search",
            "emptyTable": text
        },
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        columns: [
            {"data": "name"},
            {"data": "userid"},
            {"data": "global"},
            {"data": "enabled"},
            {"data": "monint"},
        ],
        "columnDefs": [
            {className: "dt-left tdColumn1", "targets": 0},
            {className: "dt-left tdColumn2", "targets": 1, "visible": false},
            {className: "dt-left tdColumn3", "targets": 2}
        ],
        ordering: true,
        select: false,
        bInfo: true,
        responsive: true,
        dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        drawCallback: function (settings) {

            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
            $(".checkbox-btn input[type='checkbox']").change(function () {
                if ($(this).is(":checked")) {
                    $(this).parents("tr").addClass("selected");
                }
            });
            $('.equalHeight').matchHeight();
        },
        initComplete: function (settings, json) {


            $("#se-pre-con-loader").hide();
            $(".site-info").show();
            $("#eventItems_datatable_div").show();
        }

    });
    $('#eventItems_datatable tbody').on('click', 'tr', function () {
        var rowID = userTable.row(this).data();
        //console.log("row data-->" + rowID.id);
        $("#eventId").val(rowID.id);
        userTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });

    $("#users_searchbox").keyup(function () {
        userTable.search(this.value).draw();
        $("#eventItems_datatable tbody").eq(0).html();
    });
}

function savedSearch() {
    var data = "function=AJAX_GetEventitemsSearchid&csrfMagicToken=" + csrfMagicToken;
    //var encodedData = get_RSA_EnrytptedData(data);

    $.ajax({
        url: "../lib/l-custAjax.php?" + data,
        type: "POST",
        //dataType: 'json',
        //data: {data: data},
        success: function (result) {

            //allSavedSearched = result;
            allSavedSearched = $.parseJSON(result);
        }
    });
}

function setSavedSearchDropdown(counter) {
    var optionStr = '<option value="0">--select--</option>';
    for (i = 0; i < allSavedSearched.length; i++) {
        optionStr += '<option value=' + allSavedSearched[i].id + '>' + allSavedSearched[i].name + '</option>';
    }
    if (counter) {
        //sectionNumber = 1;
        $("#search_id").html(optionStr);// append drop down value in add pop up
        $(".selectpicker").selectpicker("refresh");
    } else {
        var selid = $("#eventId").val();
        $(".se-pre-con").show();
        //$('#edit-event').modal('show');
        $("#search_id_Edit").html(optionStr);// append drop down value in add pop up
        getEditItemData(selid);
        $(".selectpicker").selectpicker("refresh");
    }

}

function getEditItemData(selid) {
    if (selid) {
        $.ajax({
            url: '../lib/l-custAjax.php?function=getEditItemData&selid=' + selid + "&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json',
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (data) {
                if (data.status) {
                    $("#Event-Name_Edit").val(data.result[0].name);

                    var global = data.result[0].global;

                    if (global == 1) {
                        $('#add_global_Edit').prop('checked', true);
                    } else {
                        $('#add_global_Edit').prop('checked', false);
                    }

                    var enabled = data.result[0].enabled;
                    if (enabled == 1) {
                        $('#add_enabled_Edit').prop('checked', true);
                    } else {
                        $('#add_enabled_Edit').prop('checked', false);
                    }
                    $("#m-ID_Edit").val(data.result[0].monint);
                    $("#mon_Type_Edit").val(data.result[0].montype);
                    $("#item_type_Edit").val(data.result[0].itemtype);
                    $("#search_id_Edit").val(data.result[0].id);

                    constructCreteriaEdit(data.resultC, data.total_countC);

                    $(".se-pre-con").hide();
                    $('#edit-event').modal('show');
                    $(".selectpicker").selectpicker("refresh");
                    return true;
                } else {
                    $(".se-pre-con").hide();
                    return false;
                }
            }
        });
    } else {
        $(".se-pre-con").hide();
        return false;
    }
}

function constructCreteriaEdit(resultC, total_countC) {
    //console.log("value--->" + resultC.length);
    //console.log(resultC[i].statusval);
    var removeIcon = '';
    if (total_countC > 0) {
        for (var i = 0; i < resultC.length; i++) {
            var newHeader = sectionNumberEdit;

            if (newHeader == 1) {
                removeIcon = "";
            } else {
                removeIcon = '<a href="#" ><i class="material-icons icon-ic_close_24px" onclick="removeSubHdrEdit(this)" title="Remove"></i></a>';
            }
            var summarySections = '<div class="row clearfix summarySec" id="Creteria_row' + newHeader + '">' +
                    '        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">' +
                    '           <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">' +
                    '             <a href="javascript:" onclick="addSectionDropDwonsEdit(this)" title="Add"><i class="icon-ic_add_24px material-icons"></i></a>' +
                    '         </div>' +
                    '         <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10">' +
                    '           <select class="form-control selectpicker dropdown-submenu" data-container="body" id="C-statusEdit_' + newHeader + '" data-size="5">' +
                    '              <option value="0">--select status--</option>' +
                    '             <option value="1">Ok</option>' +
                    '              <option value="3">Alert</option>' +
                    '             <option value="2">Warning</option>' +
                    '          </select>' +
                    '         <em class="error addreq" id="req-C-statusEdit_' + newHeader + '">*</em>' +
                    '    </div>' +
                    '  </div>' +
                    '  <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">' +
                    '     <select class="form-control selectpicker dropdown-submenu" data-container="body" id="C-typeEdit_' + newHeader + '" data-size="5">' +
                    '          <option value="0">--select Criteria Type--</option>' +
                    '          <option value="1">1. Count Minimum</option>' +
                    '          <option value="2">2. Count Maximum</option>' +
                    '         <option value="3">3. Count Minimum Param Maximum</option>' +
                    '         <option value="4">4. Count Minimum Param Maximum</option>' +
                    '     </select>' +
                    '<span class="icon-ic_info_outline_24px help-note" title="1. Count Minimum : The count of all items must be greater than or eual to countval&#13;2. Count Maximum : The count of all items must be less than or eual to countval&#13;3. Count Minimum Param Maximum : There must be at least countval items with a parameter greater than or equal to paramval&#13;4. Count Minimum Param Maximum : There must be at least countval items with a parameter less than or equal to paramval"></span>'+
                    '    <em class="error addreq" id="req-C-typeEdit_' + newHeader + '">*</em>' +
                    '   </div>' +
                    '   <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">' +
                    '      <input class="form-control" name="C-valueEdit_' + newHeader + '" id="C-valueEdit_' + newHeader + '" type="text" placeholder="Count Value" value="' + resultC[i].countval + '">' +
                    '<span class="icon-ic_info_outline_24px help-note" title="This represents total number of events required for this criteria and it should be a numeric value"></span>' +
                    '     <em class="error addreq" id="req-C-valueEdit_' + newHeader + '">*</em>' +
                    ' </div>' +
                    '  <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">' +
                    '      <input class="form-control" name="PvalEdit_' + newHeader + '" id="PvalEdit_' + newHeader + '" type="text" placeholder="Param Value" value="' + resultC[i].paramval + '">' +
                    '<span class="icon-ic_info_outline_24px help-note" title="1. It can be treated as a numeric which can be started as 0&#13;2. It can be created as time value as well which should be specified in seconds&#13;Note : Depends on Monitor Type"></span>' +
                    '      <em class="error addreq" id="req-PvalEdit_' + newHeader + '">*</em>' +
                    '  </div>' + removeIcon +
                    '</div>';

            sectionNumberEdit++;

            $("#Creteria_row" + (newHeader - 1)).after(summarySections);
            $('#C-statusEdit_' + newHeader).val(resultC[i].statusval);
            $('#C-typeEdit_' + newHeader).val(resultC[i].crittype);
            $('.edit-event-popup').mCustomScrollbar('update');
            $('.selectpicker').selectpicker('refresh');

        }
        sectionNumberEdit = resultC.length;
    } else {
        var contentsSingle = '<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">' +
                '          <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">' +
                '              <a href="javascript:" onclick="addSectionDropDwonsEdit(this)" title="Add"><i class="icon-ic_add_24px material-icons"></i></a>' +
                '</div>' +
                '<div class="col-lg-10 col-md-10 col-sm-10 col-xs-10">' +
                '   <select class="form-control selectpicker dropdown-submenu" data-container="body" id="C-statusEdit_1" data-size="5">' +
                '      <option value="0">--select status--</option>' +
                '      <option value="1">Ok</option>' +
                '     <option value="3">Alert</option>' +
                '      <option value="2">Warning</option>' +
                ' </select>' +
                '   <em class="error addreq" id="req-C-statuEdit_1">*</em>' +
                ' </div>' +
                '</div>' +
                '<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">' +
                '   <select class="form-control selectpicker dropdown-submenu" data-container="body" id="C-typeEdit_1" data-size="5">' +
                '      <option value="0">--select Criteria Type--</option>' +
                '     <option value="1">1. Count Minimum</option>' +
                '    <option value="2">2. Count Maximum</option>' +
                '   <option value="3">3. Count Minimum Param Maximum</option>' +
                '  <option value="4">4. Count Minimum Param Maximum</option>' +
                '</select>' +
                '<span class="icon-ic_info_outline_24px help-note" title="1. Count Minimum : The count of all items must be greater than or eual to countval&#13;2. Count Maximum : The count of all items must be less than or eual to countval&#13;3. Count Minimum Param Maximum : There must be at least countval items with a parameter greater than or equal to paramval&#13;4. Count Minimum Param Maximum : There must be at least countval items with a parameter less than or equal to paramval"></span>'+
                '<em class="error addreq" id="req-C-typeEdit_1">*</em>' +
                '</div>' +
                '<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">' +
                '   <input class="form-control" name="C-value_1" id="C-valueEdit_1" type="text" placeholder="Count Value">' +
                '<span class="icon-ic_info_outline_24px help-note" title="This represents total number of events required for this criteria and it should be a numeric value"></span>' +
                '  <em class="error addreq" id="req-C-valueEdit_1">*</em>' +
                '</div>' +
                '<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">' +
                '   <input class="form-control" name="Pval_1" id="PvalEdit_1" type="text" placeholder="Param Value">' +
                '<span class="icon-ic_info_outline_24px help-note" title="1. It can be treated as a numeric which can be started as 0&#13;2. It can be created as time value as well which should be specified in seconds&#13;Note : Depends on Monitor Type"></span>' +
                '  <em class="error addreq" id="req-PvalEdit_1">*</em>' +
                '                                </div>';

        $("#Creteria_row0").append(contentsSingle);
    }

    return true;
}

function addSectionDropDwons(obj) {
    //alert("val--->" + sectionNumber);
    if (sectionNumber > 2) {
        $("#Successerror_eventitems").html("");
        $("#Successerror_eventitems").show();
        $("#Successerror_eventitems").html("<span>You can add only 3 Criteria details.</span>");
        setTimeout(function () {
            $("#Successerror_eventitems").fadeOut(1800);
        }, 2000);
        return false;
    }
    sectionNumber++;
    var newHeader = sectionNumber;
    var summarySections = '<div class="row clearfix summarySec">' +
            '        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">' +
            '           <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">' +
            '             <a href="javascript:" onclick="addSectionDropDwons(this)" title="Add"><i class="icon-ic_add_24px material-icons"></i></a>' +
            '         </div>' +
            '         <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10">' +
            '           <select class="form-control selectpicker dropdown-submenu" data-container="body" id="C-status_' + newHeader + '" data-size="5">' +
            '              <option value="0">--select status--</option>' +
            '             <option value="1">Ok</option>' +
            '              <option value="3">Alert</option>' +
            '             <option value="2">Warning</option>' +
            '          </select>' +
            '         <em class="error addreq" id="req-C-status_' + newHeader + '">*</em>' +
            '    </div>' +
            '  </div>' +
            '  <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">' +
            '     <select class="form-control selectpicker dropdown-submenu" data-container="body" id="C-type_' + newHeader + '" data-size="5">' +
            '          <option value="0">--select Criteria Type--</option>' +
            '          <option value="1">1. Count Minimum</option>' +
            '          <option value="2">2. Count Maximum</option>' +
            '         <option value="3">3. Count Minimum Param Maximum</option>' +
            '         <option value="4">4. Count Minimum Param Maximum</option>' +
            '     </select>' +
            '<span class="icon-ic_info_outline_24px help-note" title="1. Count Minimum : The count of all items must be greater than or eual to countval&#13;2. Count Maximum : The count of all items must be less than or eual to countval&#13;3. Count Minimum Param Maximum : There must be at least countval items with a parameter greater than or equal to paramval&#13;4. Count Minimum Param Maximum : There must be at least countval items with a parameter less than or equal to paramval"></span>'+
            '    <em class="error addreq" id="req-C-type_' + newHeader + '">*</em>' +
            '   </div>' +
            '   <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">' +
            '      <input class="form-control" name="C-value_' + newHeader + '" id="C-value_' + newHeader + '" type="text" placeholder="Count Value">' +
            '<span class="icon-ic_info_outline_24px help-note" title="This represents total number of events required for this criteria and it should be a numeric value"></span>' +
            '     <em class="error addreq" id="req-C-value_' + newHeader + '">*</em>' +
            ' </div>' +
            '  <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">' +
            '      <input class="form-control" name="Pval_' + newHeader + '" id="Pval_' + newHeader + '" type="text" placeholder="Param Value">' +
            '<span class="icon-ic_info_outline_24px help-note" title="1. It can be treated as a numeric which can be started as 0&#13;2. It can be created as time value as well which should be specified in seconds&#13;Note : Depends on Monitor Type"></span>' +
            '      <em class="error addreq" id="req-Pval_' + newHeader + '">*</em>' +
            '  </div>' +
            '<a href="#" ><i class="material-icons icon-ic_close_24px" onclick="removeSubHdr(this)" title="Remove"></i></a>' +
            '</div>';
    //console.log("content--->" + JSON.stringify(summarySections));
    //$(".summarySec").append(summarySections);
    $(summarySections).insertAfter($(obj).parent().parent().parent());
    $('.add-new-event-popup').mCustomScrollbar('update');
    $('.selectpicker').selectpicker('refresh');
}

//edit eventItem popup close event
$("#edit-event").on("hidden.bs.modal", function () {
    $(".summarySec").html('');
    sectionNumberEdit = 1;
});

function addSectionDropDwonsEdit(obj) {
    //alert("val--->" + sectionNumberEdit);
    if (sectionNumberEdit > 2) {
        $("#Successerror_eventitems_Edit").html("");
        $("#Successerror_eventitems_Edit").show();
        $("#Successerror_eventitems_Edit").html("<span>You can add only 3 Criteria details.</span>");
        setTimeout(function () {
            $("#Successerror_eventitems_Edit").fadeOut(1800);
        }, 2000);
        return false;
    }
    sectionNumberEdit++;
    var newHeader = sectionNumberEdit;
    var summarySections = '<div class="row clearfix summarySec">' +
            '        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">' +
            '           <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">' +
            '             <a href="javascript:" onclick="addSectionDropDwonsEdit(this)" title="Add"><i class="icon-ic_add_24px material-icons"></i></a>' +
            '         </div>' +
            '         <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10">' +
            '           <select class="form-control selectpicker dropdown-submenu" data-container="body" id="C-statusEdit_' + newHeader + '" data-size="5">' +
            '              <option value="0">--select status--</option>' +
            '             <option value="1">Ok</option>' +
            '              <option value="3">Alert</option>' +
            '             <option value="2">Warning</option>' +
            '          </select>' +
            '         <em class="error addreq" id="req-C-statusEdit_' + newHeader + '">*</em>' +
            '    </div>' +
            '  </div>' +
            '  <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">' +
            '     <select class="form-control selectpicker dropdown-submenu" data-container="body" id="C-typeEdit_' + newHeader + '" data-size="5">' +
            '          <option value="0">--select Criteria Type--</option>' +
            '          <option value="1">1. Count Minimum</option>' +
            '          <option value="2">2. Count Maximum</option>' +
            '         <option value="3">3. Count Minimum Param Maximum</option>' +
            '         <option value="4">4. Count Minimum Param Maximum</option>' +
            '     </select>' +
            '<span class="icon-ic_info_outline_24px help-note" title="1. Count Minimum : The count of all items must be greater than or eual to countval&#13;2. Count Maximum : The count of all items must be less than or eual to countval&#13;3. Count Minimum Param Maximum : There must be at least countval items with a parameter greater than or equal to paramval&#13;4. Count Minimum Param Maximum : There must be at least countval items with a parameter less than or equal to paramval"></span>'+
            '    <em class="error addreq" id="req-C-typeEdit_' + newHeader + '">*</em>' +
            '   </div>' +
            '   <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">' +
            '      <input class="form-control" name="C-valueEdit_' + newHeader + '" id="C-valueEdit_' + newHeader + '" type="text" placeholder="Count Value">' +
            '<span class="icon-ic_info_outline_24px help-note" title="This represents total number of events required for this criteria and it should be a numeric value"></span>' +
            '     <em class="error addreq" id="req-C-valueEdit_' + newHeader + '">*</em>' +
            ' </div>' +
            '  <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">' +
            '      <input class="form-control" name="PvalEdit_' + newHeader + '" id="PvalEdit_' + newHeader + '" type="text" placeholder="Param Value">' +
            '<span class="icon-ic_info_outline_24px help-note" title="1. It can be treated as a numeric which can be started as 0&#13;2. It can be created as time value as well which should be specified in seconds&#13;Note : Depends on Monitor Type"></span>' +
            '      <em class="error addreq" id="req-PvalEdit_' + newHeader + '">*</em>' +
            '  </div>' +
            '<a href="#" ><i class="material-icons icon-ic_close_24px" onclick="removeSubHdrEdit(this)" title="Remove"></i></a>' +
            '</div>';
    //console.log("content--->" + JSON.stringify(summarySections));
    //$(".summarySec").append(summarySections);
    $(summarySections).insertAfter($(obj).parent().parent().parent());
    $('.add-new-event-popup').mCustomScrollbar('update');
    $('.selectpicker').selectpicker('refresh');
}

function removeSubHdr(obj) {
    sectionNumber--;
    //alert("removeval--->"+sectionNumber);
    $(obj).parent().parent().remove();
}
function removeSubHdrEdit(obj) {
    sectionNumberEdit--;
    //alert("removeval--->"+sectionNumber);
    $(obj).parent().parent().remove();
}

function addeventitems() {
    var Cstatus = [];
    var Ctype = [];
    var Cvalue = [];
    var Pval = [];
    var dupStatus = 0;
    var CstatusNull = 1;
    var CtypeNull = 1;
    var PvalNull = 1;
    var CvalueNull = 1;
    var NAN = 0;



    var EventName = $("#Event-Name").val();
    var mID = $("#m-ID").val();
    var Master = 1;
    var add_global;
    var add_enabled;
    var search_id = $("#search_id").val();
    var item_type = $("#item_type").val();
    var mon_Type = $("#mon_Type").val();

    //enabled or disabled
    if ($('#add_enabled').is(":checked"))
    {
        add_enabled = 1;
    } else {
        add_enabled = 0;
    }

    //global or not global
    if ($('#add_global').is(":checked"))
    {
        add_global = 1;
    } else {
        add_global = 0;
    }



    for (var i = 1; i <= sectionNumber; +i++) {  // storing creteria information
        Cstatus.push($("#C-status_" + i).val());
        Ctype.push($("#C-type_" + i).val());
        Cvalue.push($("#C-value_" + i).val());
        Pval.push($("#Pval_" + i).val());
    }

    //validation for input type text
    if (EventName == '' || mID == '' || Master == '' || Cvalue == '' || Pval == '') {
        $("#Successerror_eventitems").html("");
        $("#Successerror_eventitems").show();
        $("#Successerror_eventitems").html("<span>Please enter the * required fields</span>");
        setTimeout(function () {
            $("#Successerror_eventitems").fadeOut(1800);
        }, 2000);
        return false;
    }
    if (!isAlphaNumeric_CW(EventName)) {
        $("#Successerror_eventitems").html("");
        $("#Successerror_eventitems").show();
        $("#Successerror_eventitems").html("<span>Please enter AlphaNumeric Item Name.</span>");
        setTimeout(function () {
            $("#Successerror_eventitems").fadeOut(1800);
        }, 2000);
        return false;
    }

    for (var i = 0; i < Cstatus.length; +i++) {  //select filed validation
        if (Cstatus[i] == 0) {
            CstatusNull = 0;
        }
        if (Cvalue[i] == '') {
            CvalueNull = 0;
        }
        if (Pval[i] == '') {
            PvalNull = 0;
        }
        if (!isNormalInteger(Cvalue[i]) || !isNormalInteger(Pval[i])) {
            NAN = 1;
        }
    }

    for (var i = 0; i < Ctype.length; +i++) {   //select filed validation
        if (Ctype[i] == 0) {
            CtypeNull = 0;
        }
    }


    //validation for input type select
    if (item_type == 0 || CstatusNull == 0 || CtypeNull == 0 || search_id == 0 || CvalueNull == 0 || PvalNull == 0 || mon_Type == 0) {
        $("#Successerror_eventitems").html("");
        $("#Successerror_eventitems").show();
        $("#Successerror_eventitems").html("<span>Please enter the * required fields</span>");
        setTimeout(function () {
            $("#Successerror_eventitems").fadeOut(1800);
        }, 2000);
        return false;
    }

    if (!isNormalInteger(mID) || NAN) {
        $("#Successerror_eventitems").html("");
        $("#Successerror_eventitems").show();
        $("#Successerror_eventitems").html("<span>Expected Integer data.</span>");
        setTimeout(function () {
            $("#Successerror_eventitems").fadeOut(1800);
        }, 2000);
        return false;
    }

    dupStatus = find_duplicate_in_array(Cstatus);

    if (dupStatus !== sectionNumber) {
        $("#Successerror_eventitems").html("");
        $("#Successerror_eventitems").show();
        $("#Successerror_eventitems").html("<span>Criteria Status should not be same.</span>");
        setTimeout(function () {
            $("#Successerror_eventitems").fadeOut(1800);
        }, 2000);
        return false;
    }
    var data = "function=SubmitEventItems&EventName=" + EventName + "&mID=" + mID + "&Master=" + Master + "&add_global=" + add_global + "&add_enabled=" + add_enabled + "&search_id=" + search_id + "&item_type=" + item_type + "&Cstatus=" + Cstatus + "&Ctype=" + Ctype + "&Cvalue=" + Cvalue + "&Pval=" + Pval + "&mon_Type=" + mon_Type + "&csrfMagicToken=" + csrfMagicToken;
    //var encodedData = get_RSA_EnrytptedData(data);
    $.ajax({
        url: "../lib/l-custAjax.php?" + data,
        //data: {data: encodedData},
        type: 'POST',
        dataType: 'text',
        success: function (check) {

            console.log("check-->" + check);
            if ($.trim(check) === "available") {
                $('#Successerror_eventitems').fadeIn('');
                $("#Successerror_eventitems").html('<span style="color:red;">Event Name already exist.</span>');
                setTimeout(function () {
                    $("#Successerror_eventitems").fadeOut(3000);
                }, 2000);
            } else {
                if (check == 1 || check == "1") {
                    $("#SuccessMsg_eventitems").fadeIn();
                    $("#SuccessMsg_eventitems").html('<span style="color: green;">Compliance item added successfully.</span>');
                    setTimeout(function () {
                        $("#SuccessMsg_eventitems").fadeOut(3000);
                        $('#add-new-event').modal('toggle');
                        getEventsDet(itemtype);
                        savedSearch();
                        $('#addEventitem_form')[0].reset();
                    }, 2000);
                } else {
                    $("#Successerror_eventitems").html("");
                    $("#Successerror_eventitems").show();
                    $("#Successerror_eventitems").html("<span>Sorry,This Configuration is already exists.</span>");
                    setTimeout(function () {
                        $("#Successerror_eventitems").fadeOut(1800);
                    }, 2000);
                    return false;
                }
            }
        }
    });

}

function isNormalInteger(str) {
    return /^(0|[1-9]\d*)$/.test(str);
}

//edit eventItems

function editEventitems() {
    var Cstatus = [];
    var Ctype = [];
    var Cvalue = [];
    var Pval = [];
    var dupStatus = 0;
    var CtypeNull = 1;
    var CstatusNull = 1;
    var PvalNull = 1;
    var CvalueNull = 1;
    var NAN = 0;

    var eventId = $("#eventId").val();
    var EventName = $("#Event-Name_Edit").val();
    var mID = $("#m-ID_Edit").val();
    var Master = 1;
    var add_global;
    var add_enabled;
    var search_id = $("#search_id_Edit").val();
    var item_type = $("#item_type_Edit").val();
    var mon_Type = $("#mon_Type_Edit").val();

    //enabled or disabled
    if ($('#add_enabled_Edit').is(":checked"))
    {
        add_enabled = 1;
    } else {
        add_enabled = 0;
    }

    //global or not global
    if ($('#add_global_Edit').is(":checked"))
    {
        add_global = 1;
    } else {
        add_global = 0;
    }

    for (var i = 1; i <= sectionNumberEdit; +i++) {  // storing creteria information
        Cstatus.push($("#C-statusEdit_" + i).val());
        Ctype.push($("#C-typeEdit_" + i).val());
        Cvalue.push($("#C-valueEdit_" + i).val());
        Pval.push($("#PvalEdit_" + i).val());
    }

    //validation for input type text
    if (EventName == '' || mID == '' || Master == '' || Cvalue == '' || Pval == '') {
        $("#Successerror_eventitems_Edit").html("");
        $("#Successerror_eventitems_Edit").show();
        $("#Successerror_eventitems_Edit").html("<span>Please enter the * required fields</span>");
        setTimeout(function () {
            $("#Successerror_eventitems_Edit").fadeOut(1800);
        }, 2000);
        return false;
    }

    if (!isAlphaNumeric_CW(EventName)) {
        $("#Successerror_eventitems_Edit").html("");
        $("#Successerror_eventitems_Edit").show();
        $("#Successerror_eventitems_Edit").html("<span>Please enter AlphaNumeric Item Name.</span>");
        setTimeout(function () {
            $("#Successerror_eventitems_Edit").fadeOut(1800);
        }, 2000);
        return false;
    }

    for (var i = 0; i < Cstatus.length; +i++) {  //select filed validation
        if (Cstatus[i] == 0) {
            CstatusNull = 0;
        }
        if (Cvalue[i] == '') {
            CvalueNull = 0;
        }
        if (Pval[i] == '') {
            PvalNull = 0;
        }
        if (!isNormalInteger(Cvalue[i]) || !isNormalInteger(Pval[i])) {
            NAN = 1;
        }
    }

    for (var i = 0; i < Ctype.length; +i++) {   //select filed validation
        if (Ctype[i] == 0) {
            CtypeNull = 0;
        }
    }

    //validation for input type select
    if (item_type == 0 || CstatusNull == 0 || CtypeNull == 0 || search_id == 0 || CvalueNull == 0 || PvalNull == 0 || mon_Type == 0) {
        $("#Successerror_eventitems_Edit").html("");
        $("#Successerror_eventitems_Edit").show();
        $("#Successerror_eventitems_Edit").html("<span>Please enter the * required fields</span>");
        setTimeout(function () {
            $("#Successerror_eventitems_Edit").fadeOut(1800);
        }, 2000);
        return false;
    }

    if (!isNormalInteger(mID) || NAN) {
        $("#Successerror_eventitems_Edit").html("");
        $("#Successerror_eventitems_Edit").show();
        $("#Successerror_eventitems_Edit").html("<span>Expected Integer data.</span>");
        setTimeout(function () {
            $("#Successerror_eventitems_Edit").fadeOut(1800);
        }, 2000);
        return false;
    }

    dupStatus = find_duplicate_in_array(Cstatus);

    if (dupStatus !== sectionNumberEdit) {
        $("#Successerror_eventitems_Edit").html("");
        $("#Successerror_eventitems_Edit").show();
        $("#Successerror_eventitems_Edit").html("<span>Criteria Status should not be same.</span>");
        setTimeout(function () {
            $("#Successerror_eventitems_Edit").fadeOut(1800);
        }, 2000);
        return false;
    }
    var data = "function=UpdateEventItems&EventName=" + EventName + "&mID=" + mID + "&Master=" + Master + "&add_global=" + add_global + "&add_enabled=" + add_enabled + "&search_id=" + search_id + "&item_type=" + item_type + "&Cstatus=" + Cstatus + "&Ctype=" + Ctype + "&Cvalue=" + Cvalue + "&Pval=" + Pval + "&eventId=" + eventId + "&mon_Type=" + mon_Type + "&csrfMagicToken=" + csrfMagicToken;
    //var encodedData = get_RSA_EnrytptedData(data);
    $.ajax({
        url: "../lib/l-custAjax.php?" + data,
        //data: {data: encodedData},
        type: 'POST',
        dataType: 'text',
        success: function (check) {
            console.log("check-->" + check);
            if (check == 1 || check == "1") {
                $("#SuccessMsg_eventitems_Edit").fadeIn();
                $("#SuccessMsg_eventitems_Edit").html('<span style="color: green;">Compliance item edited successfully.</span>');
                setTimeout(function () {
                    $("#SuccessMsg_eventitems_Edit").fadeOut(3000);
                    $('#edit-event').modal('toggle');
                    getEventsDet(itemtype);
                    savedSearch();
                    $("#del_disabled_a").addClass('not-active');
                    $("#edit_disabled_a").addClass('not-active');
                    $('#editEventitem_form')[0].reset();
                }, 2000);
            } else {
                $("#Successerror_eventitems_Edit").html("");
                $("#Successerror_eventitems_Edit").show();
                $("#Successerror_eventitems_Edit").html("<span>Not sufficient data</span>");
                setTimeout(function () {
                    $("#Successerror_eventitems_Edit").fadeOut(1800);
                }, 2000);
                return false;
            }
        }
    });

}

function find_duplicate_in_array(arra1) {
    var i = 0;
    var len = arra1.length;
    var result = [];
    var obj = {};
    for (i = 0; i < len; i++)
    {
        obj[arra1[i]] = 0;
    }
    for (i in obj) {
        result.push(i);
    }
    return result.length;
}

$('input[type="button"]').click(function () {
    $('input[type="button"].chngHigh').removeClass('chngHigh')
    $(this).addClass('chngHigh');
});

function getEventDetails(data) {
    if (data.id === "Availability") {
        $('td.chngHigh').removeClass('chngHigh')
        $("#Availability").addClass('chngHigh');
        itemtype = 5;
        getEventsDet(itemtype);
    } else if (data.id === "Security") {
        $('td.chngHigh').removeClass('chngHigh')
        $("#Security").addClass('chngHigh');
        itemtype = 7;
        getEventsDet(itemtype);
    } else if (data.id === "Resources") {
        $('td.chngHigh').removeClass('chngHigh')
        $("#Resources").addClass('chngHigh');
        itemtype = 8;
        getEventsDet(itemtype);
    } else if (data.id === "Maintenance") {
        $('td.chngHigh').removeClass('chngHigh')
        $("#Maintenance").addClass('chngHigh');
        itemtype = 10;
        getEventsDet(itemtype);
    } else if (data.id === "Events") {
        $('td.chngHigh').removeClass('chngHigh')
        $("#Events").addClass('chngHigh');
        itemtype = 9;
        getEventsDet(itemtype);
    }
}

function getEventsDet(itemtype) {
    $("#eventItems_datatable").dataTable().fnDestroy();
    var data = "function=CUSAJX_eventItemsGridData";
    userTable = $('#eventItems_datatable').DataTable({
        scrollY: jQuery('#eventItems_datatable').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        searching: true,
        processing: true,
        //serverSide: true,
        bAutoWidth: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        //caseInsensitive: true,
        ajax: {
            url: "../lib/l-custAjax.php?" + data + "&itemtype=" + itemtype + "&csrfMagicToken=" + csrfMagicToken,
            type: "POST",
            dataType: "json",
            //data: {data:data},
        },
        language: {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search",
            //"emptyTable": text
        },
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        columns: [
            {"data": "name"},
            {"data": "userid"},
            {"data": "global"},
            {"data": "enabled"},
            {"data": "filterId"},
        ],
        "columnDefs": [
            {className: "dt-left tdColumn1", "targets": 0},
            {className: "dt-left tdColumn2", "targets": 1, "visible": false},
            {className: "dt-left tdColumn3", "targets": 2}
        ],
        ordering: true,
        select: false,
        bInfo: false,
        responsive: true,
        dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        drawCallback: function (settings) {

            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
            /*$(".checkbox-btn input[type='checkbox']").change(function () {
             if ($(this).is(":checked")) {
             $(this).parents("tr").addClass("selected");
             }
             });*/
            $('.equalHeight').matchHeight();
        },
        initComplete: function (settings, json) {

            $("#se-pre-con-loader").hide();
            $(".site-info").show();
            $("#eventItems_datatable_div").show();
            $("#del_disabled_a").addClass('not-active');
            $("#edit_disabled_a").addClass('not-active');
            $("#edit_disabled_a").removeAttr("onclick");
            $("#del_disabled_a").removeAttr("onclick");
        }

    });

    $('#eventItems_datatable tbody').off().on('click', 'tr', function () {
        $('#deleteerror_eventitems').html('');
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            $("#del_disabled_a").addClass('not-active');
            $("#edit_disabled_a").addClass('not-active');
            $("#edit_disabled_a").removeAttr("onclick");
            $("#del_disabled_a").removeAttr("onclick");
        } else {
            userTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var ids = userTable.row(this).data();
            var data = JSON.stringify(ids);
            var data = JSON.parse(data);
            temp = data.id;
            $("#eventId").val(data.id);
            $("#del_disabled_a").removeClass('not-active');
            $("#edit_disabled_a").removeClass('not-active');
            $("#edit_disabled_a").attr("onclick", "setSavedSearchDropdown(0);");
            $("#del_disabled_a").attr("onclick", "deleteEventItem();");
        }
    });

    $("#users_searchbox").keyup(function () {
        userTable.search(this.value).draw();
        $("#eventItems_datatable tbody").eq(0).html();
    });
}

function deleteEventItem() {
    if (userTable.rows('.selected').data().length) {
        $('#delete_Yes-eventitems').html('');
        $('#deleteerror_Yes-eventitems').html('');
        $('#delete-Yes-event').modal('show');
    }
}

function deleteYesEventItem() {
    var data = "function=CUSAJX_evenDeletetItem";
    var deletedataID = temp;
    $.ajax({
        url: "../lib/l-custAjax.php?" + data + "&deletedataID=" + deletedataID + "&csrfMagicToken=" + csrfMagicToken,
        type: "POST",
        success: function (data) {
            if ($.trim(data) === 'success') {
                $('#delete_Yes-eventitems').html('Compliance item deleted successfully.');
                setTimeout(function () {
                    $('#delete-Yes-event').modal('hide');
                    $("#del_disabled_a").addClass('not-active');
                    $("#edit_disabled_a").addClass('not-active');
                    getEventsDet(itemtype);
                }, 3000);
            } else {
                $('#deleteerror_Yes-eventitems').html('Some error is occured please check');
            }
        }
    });
}

// AlphaNumeric
function isAlphaNumeric_CW(objValue)
{
    var charpos = objValue.search("[^A-Za-z???????0?0-9 ]");
    if (objValue.length > 0 && charpos >= 0)
    {

        return false;
    }
    return true;
}
