$(document).ready(function () {


    Get_NotificationList();
    Get_NotificationForm();
    getServices();
    getCategory();
    getSubCategory();
    var CRMlogin_value = sessCustType;
//    alert(CRMlogin_value);

});

function Get_NotificationList() {

    var functionName = "CRM_GetNotificationsList";
    $.ajax({
        type: 'POST',
        url: '../lib/l-custAjax.php',
        datatype: 'json',
        data: {'function': functionName, 'csrfMagicToken': csrfMagicToken}
    })
            .done(function (data) {
                console.log(data);
                var json_obj = $.parseJSON(data);//parse JSON
                var output = "";
                for (var i in json_obj)
                {

                    var ID = json_obj[i].id;
                    var Name = json_obj[i].name;
                    var anchorLink = "<a name=" + Name.anchor(ID) + " id='notif_NameList' value=" + ID + ">" + Name + "</a>";
//                    if(i === '1'){
//                       output += "<li>" +Name.anchor(ID)+ "</li>";
//                    }
                    output += "<li class='notif_NameList'>" + Name.anchor(ID) + "</li>";

//                    output += "<li>" + anchorLink + "</li>";
                }
                output += "";

                $("#criteriaList").html(output);
                click_Notif();

                var result = "";
                result += '<select class="custom-select required include_notifs" size="10" multiple="" id="include_machine" style="overflow-x:hidden" localized="">';
//                result += '<option value="" selected>Please Select</option>';
                for (var j in json_obj)
                {

                    var ID = json_obj[j].id;
                    var Name = json_obj[j].name;
                    result += "<option class='text-truncate' value=" + ID + ">" + Name + "</option>";
                }
                result += "</select>";

                $("#notif-lists").html(result);



            });

    return false;
}


function click_Notif() {
    $(".notif_NameList").click(function () {

        $("#dataincidentTable_searchbox").css("display", "block");
        $("#dataMapTable_searchbox").css("display", "none");
        $(".burger-menu-dropdown").css("display", "block");
        $(".submit-Datamap").css("display", "block");
        $("#add-new-datamaps").css("display", "block");
        $("#edit-datamaps").css("display", "block");
        $("#delete-datamaps").css("display", "block");
        $("#config-btn").css("display", "block");
        $("#Btn_oneNext").css("display", "none");
        $(".incident_lists").css("display", "block");


        var CRMlogin_value = sessCustType;
        var Nid = $(this).find("a").attr("name");
        if (CRMlogin_value === '5') {
            var custName = '';
            var custSiteName = '';
            var custId = sessCId;
            var custSiteName = $("#singlecustSiteNameValue").val();


//            var custSiteName = getSingleCustomerSite;

        } else {
            var custName = $("#custName option:selected").text();
            var custSiteName = $("#custSiteName").val();
            var custId = $("#custName option:selected").val();
        }

        selectedCrmData = {Nid: Nid, CRMlogin_value: CRMlogin_value, custId: custId, 'csrfMagicToken': csrfMagicToken};
        $.ajax({
            url: "../lib/l-custAjax.php?function=GET_notificationListData",
            data: selectedCrmData,
            type: 'POST',
            dataType: 'json',
            success: function (gridData) {
                console.log(gridData);
                $(".cmdb_lists").css("display", "block");
                console.log(gridData[0].DT_RowId);

                var responseConfig = gridData[0].DT_RowId;
                if (responseConfig === '0') {

                } else {

                    $(".CRM_Type").css("display", "none");
                    $(".CRM_ActionType").css("display", "block");
                    $(".cmdb_lists").css("display", "none");
                    $("#display_inci_Data").css("display", "block");
                    $("#crmdatamap").attr('checked', 'checked');
                    $(".se-pre-con").hide();
                    $('#dataincidentTable').DataTable().destroy();
                    dataincidentTable = $('#dataincidentTable').DataTable({
                        scrollY: jQuery('#dataincidentTable').data('height'),
                        scrollCollapse: true,
                        paging: true,
                        searching: true,
                        ordering: true,
                        aaData: gridData,
                        bAutoWidth: false,
                        select: false,
                        bInfo: false,
                        responsive: true,
                        "lengthMenu": [[5, 10, 25, 50], [10, 25, 50]],
                        "language": {
                            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                            searchPlaceholder: "Search"
                        },
                        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                        initComplete: function (settings, json) {

                            $("#dataincidentTable_filter").hide();
                        },
                        drawCallback: function (settings) {
                            $(".dataTables_scrollBody").mCustomScrollbar({
                                theme: "minimal-dark"
                            });
                            $('.equalHeight').matchHeight();
                            $(".se-pre-con").hide();
                        }

                    });
                    $('#dataincidentTable').on('click', 'tr', function () {
                        var rowID = dataincidentTable.row(this).data();
                        var selected = rowID[8];
                        $("#selected").val(selected);
                        dataincidentTable.$('tr.selected').removeClass('selected');
                        $(this).addClass('selected');
                    });



//                } else if (data === "failed") {
//                    $("#successmsg-Config").css("color", "red");
//                    $("#successmsg-Config").css("display", "block");
//                    $("#successmsg-Config").show();
//                    var response = "Configuration Details Exists";
//                    $("#successmsg-Config").html(response);
//                    $("#successmsg-Config").css("display", "none");
//                }
                    $("#dataincidentTable_searchbox").keyup(function () {
                        dataincidentTable.search(this.value).draw();
                    });


                }
            },
            error: function (response) {
                console.log("Something went wrong in ajax call of skipFunction function");
                console.log(response);
            }

        });
//
    });

}



function Get_NotificationForm() {
    var functionName = "Get_NotificationForm";

    $.ajax({
        type: 'POST',
        url: '../lib/l-custAjax.php',
        datatype: 'html',
        data: {'function': functionName, 'csrfMagicToken': csrfMagicToken}
    })
            .done(function (data) {
                $("#rightcompliance").html(data);
                $('.selectpicker').selectpicker('refresh');
            });

    return false;
}

function sevicestxtbx() {
    $("#services-txtbx").show();
    $('#service_id').prop('disabled', true);
}
;
function sevicesdeltxtbx() {
    $("#services-txtbx").hide();
    $('#service_id').prop('disabled', false);
}
;
function categorytxtbx() {
    $("#category-txtbx").show();
    $('#category_id').prop('disabled', true);
}
;
function categorydeltxtbx() {
    $("#category-txtbx").hide();
    $('#category_id').prop('disabled', false);
}
;

function subcategorytxtbx() {
    $("#subcategory-txtbx").show();
    $('#subcategory_id').prop('disabled', true);
}
;
function subcategorydeltxtbx() {
    $("#subcategory-txtbx").hide();
    $('#subcategory_id').prop('disabled', false);
}
;


$("#crmDetails").submit(function () {

    var services = $("#cw_services_id").val();
    var add_services = $("#cw_addservices_id").val();
    var category = $("#cw_category_id").val();
    var add_category = $("#cw_addcategory_id").val();
    var cw_subcategory_id = $("#cw_subcategory_id").val();
    var cw_addsubcategory_id = $("#cw_addsubcategory_id").val();
    var cw_priority = $("#cw_priority").val();
    var cw_crmUser = $("#cw_crmUser").val();
    var cw_eventType = $("#cw_eventType").val();

    //alert(services);
});



/***********Add-Category*************************************/

$(".add-subcategory").click(function () {
    $("#subcategory-name").val('');
    $("#subcategory-value").val('');
});
$(".add-category").click(function () {
    $("#category-name").val('');
    $("#category-value").val('');
});


$("#add-CRMcategory").submit(function () {
    var CRMlogin_value = sessCustType;
    $("#responseMSG").html('');
    var functionName = "CRM_AddCategory";
    var categoryName = $("#category-name").val();
    var categoryValue = $("#category-value").val();
//alert(CRMlogin_value);
    var CategoryData = {categoryName: categoryName, categoryValue: categoryValue, CRMlogin_value: CRMlogin_value};
    $.ajax({
        type: 'POST',
        url: '../lib/l-custAjax.php',
        datatype: 'html',
        data: {'CategoryData': CategoryData, 'function': functionName, 'csrfMagicToken': csrfMagicToken}
    })
            .done(function (data) {
//                console.log(data+"here");
                if (data === "exists") {
                    $("#responseMSG").css("display", "block");
                    var response = "Category Already Exists";
                    $("#responseMSG").html(response);
                } else if (data === "success") {
                    $("#responseMSG").css("display", "block");
                    var response = "Category Added Successfully";
                    $("#responseMSG").html(response);
                    $("#category-name").val('');
                    $("#category-value").val('');


                }

            });
    return false;


});




/************add SubCategory***************/
$("#add-CRMSubcategory").submit(function () {
    var CRMlogin_value = sessCustType;
    $("#responseMSGSubcat").html('');
    var functionName = "CRM_AddSubCategory";
    var subcategoryName = $("#subcategory-name").val();
    var subcategoryValue = $("#subcategory-value").val();

    var SubCategoryData = {subcategoryName: subcategoryName, subcategoryValue: subcategoryValue, CRMlogin_value: CRMlogin_value};
    $.ajax({
        type: 'POST',
        url: '../lib/l-custAjax.php',
        datatype: 'html',
        data: {'SubCategoryData': SubCategoryData, 'function': functionName, 'csrfMagicToken': csrfMagicToken}
    })
            .done(function (data) {
//                console.log(data);
                if (data === "exists") {
                    $("#responseMSG").show();
                    var response = "SubCategory Already Exists";
                    $("#responseMSGSubcat").html(response);
                } else if (data === "success") {
                    $("#responseMSG").show();
                    var response = "SubCategory Added Successfully";
                    $("#responseMSGSubcat").html(response);
                    $("#subcategory-name").val('');
                    $("#subcategory-value").val('');
                    $("#successmsg").show();
                }
//                console.log(data);
            });
    return false;


});


/*******************Add Services*******************/
$("#addCRMServices").submit(function () {
    var CRMlogin_value = sessCustType;
    $("#responseMSGService").html('');

    var functionName = "CRM_AddServices";
    var serviceName = $("#crmservices-name").val();
    var serviceValue = $("#crmservices-value").val();

    var crmServicesData = {serviceName: serviceName, serviceValue: serviceValue, CRMlogin_value: CRMlogin_value};
    $.ajax({
        type: 'POST',
        url: '../lib/l-custAjax.php',
        datatype: 'html',
        data: {'crmServicesData': crmServicesData, 'function': functionName, 'csrfMagicToken': csrfMagicToken}
    })
            .done(function (data) {
                console.log(data);
                if (data === "exists") {
                    $("#responseMSG").show();
                    var response = "Services Already Exists";
                    $("#responseMSGService").html(response);
                } else if (data === "success") {
                    $("#responseMSG").show();
                    var response = "SubCategory Added Successfully";
                    $("#responseMSGService").html(response);
                    $("#crmservices-name").val('');
                    $("#crmservices-value").val('');
                    $("#successmsg").show();
                }
//                console.log(data);
            });
    return false;


});




$("#getCatSub").click(function () {
//    $("option:selected").prop("selected", false)
//    $("option:selected").prop("selected", false)
//    $("option:selected").prop("selected", false)
//    $(this).prop('selected', false);
    $("#crmServices").prop('selected', false);
    $("#crmCategory").prop('selected', false);
    $("#crmSubCategory").prop('selected', false);
    $("#crmPriority").val('');
    $("#crmUser").val('');
    $("#crmventType").val('');
    $(".include_notifs").val('');
    getServices();
    getCategory();
    getSubCategory();
    Get_NotificationList();

});

function getServices() {
    
    var CRMlogin_value = sessCustType;
    
    if (CRMlogin_value === '5') {
        var custName = '';
        var custSiteName = '';
        var custId = sessCId;
        var custSiteName = $("#singlecustSiteNameValue").val();
//            var custSiteName = getSingleCustomerSite;

    } else {
        var custName = $("#custName option:selected").text();
        var custSiteName = $("#custSiteName").val();
        var custId = $("#custName option:selected").val();
    }
    
    var functionName = "Get_Services";
    $.ajax({
        type: 'POST',
        url: '../lib/l-custAjax.php',
        datatype: 'json',
        data: {'function': functionName,CRMlogin_value:CRMlogin_value,custId:custId, 'csrfMagicToken': csrfMagicToken}
    })
            .done(function (data) {
                var json_obj = $.parseJSON(data);//parse JSON
                var output = "";
                output += '<select id="crmServices" name="search_id" required="" autofocus="" class="form-control" style="-webkit-appearance: menulist !important;">';
                output += '<option value="" selected>Please Select</option>';
                if (jsSession === "OTRS") {
                    output += '<option value="-">None</option>';
                }
                for (var i in json_obj)
                {

                    var name = json_obj[i].name;
                    var value = json_obj[i].value;
                    output += "<option value=" + value + ">" + name + "</option>";
                }
                output += "</select>";

                $("#services-Response").html(output);


                var services = "";
                for (var i in json_obj)
                {

                    var ID = json_obj[i].name;
                    var Name = json_obj[i].name;
                    services += "<li>" + Name.anchor(Name) + "</li>";
                }
                services += "";


                $("#displayServices").html(services);

            });

    return false;
}

function getCategory() {
    var CRMlogin_value = sessCustType;

    if (CRMlogin_value === '5') {
        var custName = '';
        var custSiteName = '';
        var custId = sessCId;
        var custSiteName = $("#singlecustSiteNameValue").val();
//            var custSiteName = getSingleCustomerSite;

    } else {
        var custName = $("#custName option:selected").text();
        var custSiteName = $("#custSiteName").val();
        var custId = $("#custName option:selected").val();
    }
    

    var functionName = "Get_Category";
    $.ajax({
        type: 'POST',
        url: '../lib/l-custAjax.php',
        datatype: 'json',
        data: {'function': functionName,CRMlogin_value:CRMlogin_value,custId:custId, 'csrfMagicToken': csrfMagicToken}
    })
            .done(function (data) {
                console.log(data);
                var json_obj = $.parseJSON(data);//parse JSON
                var output = "";
                output += '<select id="crmCategory" name="search_id" required="" autofocus="" class="form-control" style="-webkit-appearance: menulist !important;">';
                output += '<option value="" selected>Please Select</option>';
                if (jsSession === "OTRS") {
                    output += '<option value="-">None</option>';
                }
                for (var i in json_obj)
                {

                    var name = json_obj[i].name;
                    var value = json_obj[i].value;
                    output += "<option value=" + value + ">" + name + "</option>";
                }
                output += "</select>";

                $("#cat-Response").html(output);


                var category = "";
                for (var i in json_obj)
                {

                    var ID = json_obj[i].name;
                    var Name = json_obj[i].name;
                    category += "<li>" + Name.anchor(Name) + "</li>";
                }
                category += "";


                $("#displayCategory").html(category);
            });

    return false;
}
function getSubCategory() {

    var CRMlogin_value = sessCustType;
    
    if (CRMlogin_value === '5') {
        var custName = '';
        var custSiteName = '';
        var custId = sessCId;
        var custSiteName = $("#singlecustSiteNameValue").val();
//            var custSiteName = getSingleCustomerSite;

    } else {
        var custName = $("#custName option:selected").text();
        var custSiteName = $("#custSiteName").val();
        var custId = $("#custName option:selected").val();
    }


    var functionName = "Get_SubCategory";
    $.ajax({
        type: 'POST',
        url: '../lib/l-custAjax.php',
        datatype: 'json',
        data: {'function': functionName,CRMlogin_value:CRMlogin_value,custId:custId, 'csrfMagicToken': csrfMagicToken}
    })
            .done(function (data) {
                console.log(data);
                var json_obj = $.parseJSON(data);//parse JSON
                var output = "";
                output += '<select id="crmSubCategory" name="search_id" required="" autofocus="" class="form-control" style="-webkit-appearance: menulist !important;">';
                output += '<option value="" selected>Please Select</option>';
                if (jsSession === "OTRS") {
                    output += '<option value="-">None</option>';
                }
                for (var i in json_obj)
                {

                    var name = json_obj[i].name;
                    var value = json_obj[i].value;
                    output += "<option value=" + value + ">" + name + "</option>";
                }
                output += "</select>";

                $("#subcat-Response").html(output);



                var subcategory = "";
                for (var i in json_obj)
                {

                    var ID = json_obj[i].name;
                    var Name = json_obj[i].name;
                    subcategory += "<li>" + Name.anchor(Name) + "</li>";
                }
                subcategory += "";


                $("#displaySubCategory").html(subcategory);


            });

    return false;
}
$("#addNIDs").click(function () {
    $("#includedView").css("padding", "0 0.75rem 0 0.75rem");
    $(".include_notifs option:selected").remove().appendTo($("#includedView"));
});
$("#removeNIDs").click(function () {
    $("#includedView option:selected").remove().appendTo($(".include_notifs"));
});

$("#configure-crmdetails").submit(function () {
    
    var CRMlogin_value = sessCustType;
    
    if (CRMlogin_value === '5') {
        var custName = '';
        var custSiteName = '';
        var custId = sessCId;
        var custSiteName = $("#singlecustSiteNameValue").val();
//            var custSiteName = getSingleCustomerSite;

    } else {
        var custName = $("#custName option:selected").text();
        var custSiteName = $("#custSiteName").val();
        var custId = $("#custName option:selected").val();
    }
    
    $("#successmsg-Config").html('');
    var notifs = [];
    $.each($("#includedView option:selected"), function () {
        notifs.push($(this).val());
    });
    var selectedNIDs = notifs.join(", ");
    var functionName = "CRMconfigure";
    var crmServices = $("#crmServices").val();
    var crmCategory = $("#crmCategory").val();
    var crmSubcategory = $("#crmSubCategory").val();
    var crmPriority = $("#crmPriority").val();
    var crmUser = $("#crmUser").val();
    var crmventType = $("#crmventType").val();
    var crmTechnician = $("#crmTechnician").val();

    var configurationData = {crmServices: crmServices, crmCategory: crmCategory, crmSubcategory: crmSubcategory, crmPriority: crmPriority, crmUser: crmUser, crmTechnician: crmTechnician, crmventType: crmventType, selectedNIDs: selectedNIDs, CRMlogin_value: CRMlogin_value,custId:custId};

    $.ajax({
        type: 'POST',
        url: '../lib/l-custAjax.php',
        datatype: 'json',
        data: {'configurationData': configurationData, 'function': functionName, 'csrfMagicToken': csrfMagicToken}
    })
            .done(function (data) {
                console.log(data.responseType);
                if (data.responseType !== "failed") {
                    $("#successmsg-Config").css("display", "block");
                    $("#successmsg-Config").css("color", "green");
                    $("#successmsg-Config").html("successfully configured...");
                    $(".CRM_Type").css("display", "none");
                    $(".CRM_ActionType").css("display", "block");
                    $(".cmdb_lists").css("display", "none");
                    $("#display_inci_Data").css("display", "block");
                    $("#crmdatamap").attr('checked', 'checked');
                    var resultingData = data.gridData;
                    $(".se-pre-con").hide();
                    $('#dataincidentTable').DataTable().destroy();
                    dataincidentTable = $('#dataincidentTable').DataTable({
                        scrollY: jQuery('#dataincidentTable').data('height'),
                        scrollCollapse: true,
                        paging: true,
                        searching: true,
                        ordering: true,
                        aaData: resultingData,
                        bAutoWidth: false,
                        select: false,
                        bInfo: false,
                        responsive: true,
                        "lengthMenu": [[10, 25, 50], [10, 25, 50]],
                        "language": {
                            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                            searchPlaceholder: "Search"
                        },
                        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                        initComplete: function (settings, json) {

                            $("#dataincidentTable_filter").hide();
                        },
                        drawCallback: function (settings) {
                            $(".dataTables_scrollBody").mCustomScrollbar({
                                theme: "minimal-dark"
                            });
                            $('.equalHeight').matchHeight();
                            $(".se-pre-con").hide();
                        }

                    });
                    $('#dataincidentTable').on('click', 'tr', function () {
                        var rowID = dataincidentTable.row(this).data();
                        var selected = rowID[8];
                        $("#selected").val(selected);
                        dataincidentTable.$('tr.selected').removeClass('selected');
                        $(this).addClass('selected');
                    });

//                    $("#notifytools_searchbox").keyup(function () {
//                        dataincidentTable.search(this.value).draw();
//                    });


                } else if (data === "failed") {
                    $("#successmsg-Config").css("color", "red");
                    $("#successmsg-Config").css("display", "block");
                    $("#successmsg-Config").show();
                    var response = "Configuration Details Exists";
                    $("#successmsg-Config").html(response);
                    $("#successmsg-Config").css("display", "none");
                }

            });
    return false;
});