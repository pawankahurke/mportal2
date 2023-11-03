/*
 This file is releated to CRM Configuration Page
 */


var crmList = {"SN": "Service Now", "ME": "Manage Engine", "OT": "OTRS", "SG": "Sugar CRM", "CW": "Cherwell"};

$(document).ready(function () {
    var CRMlogin_value = sessCustType;


    $(".CRM_Type").css("display", "block");

    $("#crmConfigForm").css("display", "block");
    (".cmdb_section").css("display", "none");
    ("#crmConfigForm2").css("display", "none");
    (".ticketing-left").css("display", "none");


    $(".cmdb_datalistTable").css("display", "none");
//alert(sessCustType);
    getCrmDetails();

    if (sessCustType === '2') {
        getCustomers_And_Sitelist();
        $(".resellerSection").css("display", "block");

    } else if (sessCustType === '5') {
        getSingleCustomerSite();
        $(".resellerSection").css("display", "none");

    }
    $('input[name=loginType]:radio').click(function () {
        selected_value = $("input[name='loginType']:checked").val();
        //alert(selected_value);
        if (selected_value === "customer") {
            $(".resellerSection").css("display", "none");
        } else {
            $(".resellerSection").css("display", "block");
        }
    });

    $('input[name=crmradio]:radio').click(function () {
        var crmselected_value = $("input[name='crmradio']:checked").val();
        //alert(crmselected_value);
        //alert(selected_value);
        if (crmselected_value === "CW") {
            $("#crm_APIkey").css("display", "block");
        } else {
            $("#crm_APIkey").css("display", "none");
        }
    });

    


});


$("input[name='DataMapping-crmradio']:checked").click(function () {
//        alert("here");
        var selectedcrmType = $("input[name='DataMapping-crmradio']:checked").val();
//        alert(selectedcrmType);
        if (selectedcrmType === 'incident') {
            $(".cmdb_section").css("display", "none");
            $(".incident_section").css("display", "block");
            $(".incident_lists").css("display", "block");
            $(".cmdb_lists").css("display", "none");
            $("#display_inci_Data").css("display", "block");
            $("#getCatSub").css("display", "block");
            $("#incident_goback").css("display", "block");
        } else if (selectedcrmType === 'cmdb') {
            $("#display_inci_Data").css("display", "none");
            $(".cmdb_lists").css("display", "block");
            $(".incident_lists").css("display", "none");
            $(".cmdb_section").css("display", "block");
            $(".incident_section").css("display", "none");
        }
    });
function getCrmDetails() {
    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_GetCRMDetails" + "&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        dataType: 'json',
        success: function (response) {
            if (response.crmType !== "") {
                $("input[name=crmradio][value='" + response.crmType + "']").prop("checked", true);
                $("#crm_url").parent().addClass("is-focused");
                $("#crm_url").val(response.crmIP);
                if (response.crmKey === "-") {
                    apiKeyNotRequired();
                } else {
                    apiKeyRequired();
                }
            } else {
                $("input[name=crmradio][value='SN']").prop("checked", true);
                $("#crm_url").parent().removeClass("is-focused");
                apiKeyNotRequired();
            }
        },
        error: function (response) {
            console.log("Something went wrong in ajax call of getCrmDetails function");
            console.log(response);
        }
    });
}






/*
 * It will get triggered when left side radio buttons will get changed
 */
$('input[type=radio][name=crmradio]').click(function () {
    document.getElementById('crmConfigForm').reset();
    $("#crmConfigError").html("");
    $(".error_required").html(" *");

    var selectedCRM = $(this).val();

    switch (selectedCRM) {
        case "SN":
            apiKeyNotRequired();
            break;
        case "CW":
            apiKeyRequired();
            break;
        case "ME":
            apiKeyRequired();
            break;
        case "OT":
            apiKeyRequired();
            break;
        case "SG":
            apiKeyNotRequired();
            break;
        default:
            break;
    }
});

/*
 * This function hides api key text field
 * Only Url, Username, Password fields will be visible
 */
function apiKeyNotRequired() {
    $("#crm_key").removeClass("required");
    $("#crm_key").parent().hide();
}

/*
 * This function shows api key text field
 * Url, Username, Password, Api Key fields will be visible.
 */
function apiKeyRequired() {
    $("#crm_key").addClass("required");
    $("#crm_key").parent().show();
}

/*
 * This function will get called on click of Configure button
 * It will validate field data
 * After that it will make ajax call to l-crm.php file
 */
function configureCrmDetails_cmdb() {
    $(".cmdb_RadioBtn").prop("checked", true);
    $(".burger-menu-dropdown").css("display", "block"); // uncomment for validation
    $("#dataMapTable_searchbox").css("display", "none");
    $(".cmdb_lists").css("display", "block");
    //$("#dataMapTable_searchbox").css("display", "block");
    $("#dataincidentTable_searchbox").css("display", "none");
//    $(".burger-menu-dropdown").css("display", "block");
    $(".submit-Datamap").css("display", "block");
    $("#add-new-datamaps").css("display", "block");
    $("#edit-datamaps").css("display", "block");
    $("#delete-datamaps").css("display", "block");
    $("#config-btn").css("display", "block");
    $("#Btn_oneNext").css("display", "none");
    $(".incident_lists").css("display", "none");
    var selectedCrm = $("input[name='crmradio']:checked").val();
    var crm_url = $("#crm_url").val();
    var crm_username = $("#crm_username").val();
    var crm_password = $("#crm_password").val();

    if (selectedCrm === undefined) {
        $("#crmConfigError").html("Please select ticketing system");
    } else {
//        var crmName = crmList[selectedCrm];
        $("#crmConfigError").html("");

        var CRMlogin_value = sessCustType;
        var flag = 0;
        if (CRMlogin_value === '5') {
            var custName = '';
            var custId = sessCId;
            var custSiteName = $("#singlecustSiteNameValue").val();
//            var custSiteName = getSingleCustomerSite;

        } else {
            var custName = $("#custName option:selected").text();
//            var custSiteNames = $("#custSiteName").val();
            var txtSelectedValuesObj = document.getElementById('custSiteName');
            var custSiteName = new Array();
            var selObj = document.getElementById('custSiteName');
            var i;
            var count = 0;
            for (i = 0; i < selObj.options.length; i++) {
                if (selObj.options[i].selected) {
                    selectedArray[count] = selObj.options[i].value;
                    count++;
                }
            }

            var custId = $("#custName option:selected").val();

            if ((custName == '') || (custName == 'undefined')) {
                $("#Btn_oneNext").css("display", "block");
                $("#crm_site_required").html("Please select customer");
                flag++;

            }
            if ((custId == '') || (custId == 'undefined')) {
                $("#Btn_oneNext").css("display", "block");
                $("#crm_site_required").html("Please select customer");
                flag++;
            }
            if ((custSiteName == '') || (custSiteName == 'undefined')) {
                $("#Btn_oneNext").css("display", "block");
                $("#crm_siteName_required").html("Please select Sitename");
                flag++;
            }

        }

//        var custSiteName = $("#custSiteName").val();
        var crm_key = $("#crm_key").val();
        if (selectedCrm === "SN") {
            crm_key = '-';
        }
//        alert(flag);
        if (flag === 0) {

            $("#crmConfigError").html('<img src="../vendors/images/loader2.gif">');
            var CRMdata = {selectedCrm: selectedCrm, CRMlogin_value: CRMlogin_value, custName: custName, custId: custId, custSiteName: custSiteName, crm_url: crm_url, crm_username: crm_username, crm_password: crm_password, crm_key: crm_key, csrfMagicToken: csrfMagicToken};
            $.ajax({
                url: "../lib/l-custAjax.php?function=CUSTAJX_SetCRMDetails",
                data: CRMdata,
                type: 'POST',
                dataType: 'text',
                success: function (response) {
                    console.log(response);
                    if (response === "success") { // uncomment for validation
                        $("#config-btn").css("display", "none");
                        $("#Btn_oneNext").css("display", "none");
                        $(".burger-menu-dropdown").css("display", "block"); // uncomment for validation
                        $("#dataMapTable_searchbox").css("display", "block");
                        $("#crmConfigError").css("color", "green").html("Your " + custName + " account is configured successfully");
                        if (CRMlogin_value === '5') { // customer
                            selectedCrmData = {selectedCrm: selectedCrm, CRMlogin_value: CRMlogin_value, custId: custId};

                        } else if (CRMlogin_value === '2') { // reseller
                            selectedCrmData = {selectedCrm: selectedCrm, CRMlogin_value: CRMlogin_value, custId: custId, custSiteName: custSiteName, custName: custName};
                        }
                        configureNext_levelCRM(selectedCrmData);
//                    getDatname_Lists();

                    } else { // uncomment for validation
                        $("#Btn_oneNext").css("display", "block"); // uncomment for validation

                        $("#crmConfigError").css("color", "red").html("Given " + custName + " credentials are not valid"); // uncomment for validation
                    } // uncomment for validation

                },
                error: function (response) {
                    console.log("Something went wrong in ajax call of configureCrm function");
                    console.log(response);
                }
            });
//        }
        } else {
            return false;
        }
    }
}

function configureNext_levelCRM(selectedCrmData) {

    $("#dataMapTable_searchbox").css("display", "block");
    $("#dataincidentTable_searchbox").css("display", "none");

    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_getCRM_DataMapping" + "&csrfMagicToken=" + csrfMagicToken,
        data: selectedCrmData,
        type: 'POST',
        dataType: 'json',
        success: function (gridData) {
            console.log(gridData);

            $(".CRM_Type").css("display", "none");
            $(".CRM_ActionType").css("display", "block");
            $("#crmdatamap").attr('checked', 'checked');

            $(".se-pre-con").hide();
            $('#dataMapTable').DataTable().destroy();
            dataMapTable = $('#dataMapTable').DataTable({
                scrollY: jQuery('#dataMapTable').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
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
                    $("#dataMapTable_filter").hide();
                },
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();

                }

            });
            $('#dataMapTable').on('click', 'tr', function () {
                var rowID = dataMapTable.row(this).data();
                var selected = rowID[8];
                $("#selected").val(selected);
                dataMapTable.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            });

            $("#dataMapTable_searchbox").keyup(function () {
                //alert("here");
                dataMapTable.search(this.value).draw();
            });



        },
        error: function (response) {
            console.log("Something went wrong in ajax call of configureCrm function");
            console.log(response);
        }
    });


}


/*
 * It will download excel sheet.
 * Excel sheet will have details regarding CRM Configuration.
 * It will have Url, Category, Priority, Status, Services with respective notification name.
 */
$("#exportCrmDetails").click(function () {
    window.location = "../lib/l-custAjax.php?function=CUSTAJX_ExportCRMDetails";
});


function getCustomers_And_Sitelist() {
//    alert("here");
//    console.log("hhhhh");

    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_GetCRMcustomers" + "&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        dataType: 'text',
        success: function (response) {
            console.log(response);
            $("#CRMcustomerList").html(response);

        },
        error: function (response) {
            console.log("Something went wrong in ajax call of getCrmDetails function");
            console.log(response);
        }
    });

}

function GetcustomerSites() {
//    alert("here");
    var resellerId = sessCId;
    var customerId = $("#custName option:selected").val();


    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_GetCRMcustSiteList&Cid=" + customerId + "&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        dataType: 'text',
        success: function (response) {
            console.log(response);
            $("#CRMcustomerSiteList").html(response);

        },
        error: function (response) {
            console.log("Something went wrong in ajax call of getCrmDetails function");
            console.log(response);
        }
    });

}


function getSingleCustomerSite() {
    var customerId = sessCId;
//    alert(customerId);

    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_GetCRM_singlecustSite&Cid=" + customerId + "&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        dataType: 'text',
        success: function (response) {
            console.log(response);
            var getSinglesites = response;
//            alert(getSinglesites);
//            $("#CRMcustomerSiteList").html(response);
            $("#singlecustSiteNameValue").val(response);

        },
        error: function (response) {
            console.log("Something went wrong in ajax call of getCrmDetails function");
            console.log(response);
        }
    });
}


$('#add-new-datamaps').on('hidden.bs.modal', function (e) {
    $(".error").html(' *');
    $("#addUserForm input[name!='advroleId']").val("");
    $("#addUserForm .form-group").addClass("is-empty");
    $("#loadingSuccessMsg").html('');
    $("#reseller_radios").html('');
    $("#customer_radios").html('');
    $("#add_Customers").selectpicker("refresh");
    // if (ctype == 1) {
    //     $("#advroleId").val("agent").change();
    // }

});

$("#add-new-datamaps").click(function () {

    $('#add-new-datamaps-Form').modal('show');

});
$("#category_names").change(function () {
    //alert("here");
    var CRMlogin_value = sessCustType;
    //alert(CRMlogin_value);

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

    var customerId = sessCId;
    var categoryId = $("#category_names option:selected").val();
    if ((categoryId === '') || (categoryId === 'undefined')) {
        $("#error_category").css("color", "red");
        $("#error_category").html("Category cannot be empty...");

    }

    $(".plus-btn").css("display", "none");
    $(".select-datalist").css("display", "block");
    var customerLoginId = {customerId: custId, categoryId: categoryId};
    $.ajax({
        url: "../lib/l-custAjax.php?function=getNHDatname_Lists" + "&csrfMagicToken=" + csrfMagicToken,
        data: customerLoginId,
        type: 'POST',
        dataType: 'html',
        success: function (response) {
            console.log(response);
//            var res = JSON.parse(response);
//            alert(res);
            $(".nhdataname_Label").css("display", "block");
            $("#nanoheal_Datanames").css("display", "block");
            $("#nanoheal_Datanames").html(response);
            $("#nanoheal_DatanamesLists").html(response);

        }

    });
});

function GET_SNDatalists() {

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

//    var customerId = sessCId;
    var categoryId = $("#category_names option:selected").val();

    $(".plus-btn").css("display", "none");
    $(".select-datalist").css("display", "block");
    var customerLoginId = {customerId: custId, categoryId: categoryId};
    $.ajax({
        url: "../lib/l-custAjax.php?function=getSNDatname_Lists" + "&csrfMagicToken=" + csrfMagicToken,
        data: customerLoginId,
        type: 'POST',
        dataType: 'html',
        success: function (response) {
            console.log(response);
            $(".sndataname_Label").css("display", "block");
            $("#servicenow_Datanames").css("display", "block");
            $("#servicenow_Datanames").html(response);
//            $("#nanoheal_DatanamesLists").html(response)

        }
//        error: function (response) {
//            console.log("Something went wrong in ajax call of getCrmDetails function");
//            console.log(response);
//        }
    });
}

//$("#add-user-cancel-btn").click(function () {
//    $('#add-new-datamaps-Form').modal('hide');
//    var categoryId = $("#category_names option:selected").val('');
//
//    var NH_DataId = $("#nhData_lists option:selected").val('');
//
//    var SN_DataVal = $("#sn_dataNames option:selected").val('');
//
//});

$("#addNewMap_DATA").click(function () {
    var categoryId = $("#category_names option:selected").val();
    var categoryName = $("#category_names option:selected").text();
    var NH_DataId = $("#nhData_lists option:selected").val();
    var NH_DataName = $("#nhData_lists option:selected").text();
    var SN_DataVal = $("#sn_dataNames option:selected").val();
    var SN_DataName = $("#sn_dataNames option:selected").text();

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

//    var customerId = sessCId;


    $(".plus-btn").css("display", "none");
    $(".select-datalist").css("display", "block");
    var customerLoginId = {customerId: custId, CRMlogin_value: CRMlogin_value, categoryId: categoryId, categoryName: categoryName, NH_DataId: NH_DataId, NH_DataName: NH_DataName, SN_DataVal: SN_DataVal, SN_DataName: SN_DataName, 'csrfMagicToken': csrfMagicToken};
    $.ajax({
        url: "../lib/l-custAjax.php?function=config_NewDataNames",
        data: customerLoginId,
        type: 'POST',
        dataType: 'html',
        success: function (response) {
            console.log(response);
            $("#loadingSuccessMsg").css("display", "block");
            if (response === "exists") {
                $("#loadingSuccessMsg").css("color", "red");
                $("#loadingSuccessMsg").html("Data Already Configured");
            } else if (response === "success") {

                $("#loadingSuccessMsg").css("color", "green");
                $("#loadingSuccessMsg").html("Successfully configured Data");
                var selectedCrm = $("input[name='crmradio']:checked").val();
                if (CRMlogin_value === '5') { // customer
                    selectedCrmData = {selectedCrm: selectedCrm, CRMlogin_value: CRMlogin_value, custId: custId};

                } else if (CRMlogin_value === '2') { // reseller
                    selectedCrmData = {selectedCrm: selectedCrm, CRMlogin_value: CRMlogin_value, custId: custId, custSiteName: custSiteName, custName: custName};
                }
                configureNext_levelCRM(selectedCrmData);

            } else if (response === "failed") {
                $("#loadingSuccessMsg").css("color", "red");

                $("#loadingSuccessMsg").html("Failed to configure Data");
            }

        }

    });
});


function dataMap_ActionFunction(Action_Type) {


    var selectedDataId = $('#dataMapTable tbody tr.selected').attr('id');

    if (Action_Type === "delete-datamaps") {
        unconfigure_DataMapping(selectedDataId);
    } else if (Action_Type === "edit-datamaps") {
        edit_dataMapValues(selectedDataId);


    }

}

function edit_dataMapValues(selectedDataId) {
    var Data = {id: selectedDataId};
    $.ajax({
        url: "../lib/l-custAjax.php?function=EDIT_DataMapValues" + "&csrfMagicToken=" + csrfMagicToken,
        data: Data,
        type: 'POST',
        dataType: 'html',
        success: function (response) {
            console.log(response);
            $('#editDatmapForm').html(response);
            $('#edit-datamaps-Form').modal('show');
        }

    });

}

function unconfigure_DataMapping(selectedDataId) {

    var Data = {id: selectedDataId};
    $.ajax({
        url: "../lib/l-custAjax.php?function=unconfigure_Dataid" + "&csrfMagicToken=" + csrfMagicToken,
        data: Data,
        type: 'POST',
        dataType: 'html',
        success: function (response) {
            console.log(response);

            if (response === 'success') {

                $("#show-deleteError").modal('show');
                $("#delete-msg").html('unconfiguration is successfull');


            } else if (response === 'failed') {
                $("#show-deleteError").modal('show');
                $("#delete-msg").html('unconfiguration is Failed');

            }

        }

    });
}

$(".btn-default").click(function () {
    $("#show-deleteError").modal('hide');
});

$("#editMap_DATA").click(function () {
    var categoryId = $("#editDM_category option:selected").val();
    var categoryName = $("#editDM_category option:selected").text();
    var NH_DataId = $("#edit_nanoheal_Datanames option:selected").val();
    var NH_DataName = $("#edit_nanoheal_Datanames option:selected").text();
    var SN_DataVal = $("#edit_servicenow_Datanames option:selected").val();
    var SN_DataName = $("#edit_servicenow_Datanames option:selected").text();
    var DMedit_id = $("#DMedit_id").val();

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

//    var customerId = sessCId;


    $(".plus-btn").css("display", "none");
    $(".select-datalist").css("display", "block");
    var customerLoginId = {customerId: custId, categoryId: categoryId, categoryName: categoryName, NH_DataId: NH_DataId, NH_DataName: NH_DataName, SN_DataVal: SN_DataVal, SN_DataName: SN_DataName, DMedit_id: DMedit_id, 'csrfMagicToken': csrfMagicToken};
    $.ajax({
        url: "../lib/l-custAjax.php?function=config_editedDataNames",
        data: customerLoginId,
        type: 'POST',
        dataType: 'html',
        success: function (response) {
            console.log(response);
            $("#loadingSuccessMsgEdit").css("display", "block");
            if (response === "exists") {
                $("#loadingSuccessMsgEdit").css("color", "red");
                $("#loadingSuccessMsgEdit").html("Data Already Configured");
            } else if (response === "success") {

                $("#loadingSuccessMsgEdit").css("color", "green");
                $("#loadingSuccessMsgEdit").html("Successfully configured Data");
            } else if (response === "failed") {
                $("#loadingSuccessMsgEdit").css("color", "red");

                $("#loadingSuccessMsgEdit").html("Failed to configure Data");
            }

        }

    });
});

$("#incident_goback").click(function () {
    $(".cmdb_RadioBtn").prop("checked", true);
    skipConfiguration_cmdb();

});

$(".cmdb_goback").click(function () {
    location.reload();

});

function skipConfiguration_cmdb() {
    $(".cmdb_RadioBtn").prop("checked", true);
    $(".burger-menu-dropdown").css("display", "block");
    $("#dataMapTable_searchbox").css("display", "none");
    $(".cmdbMenu").css("display", "block");
    $("#dataincidentTable_searchbox").css("display", "none");
//    $(".burger-menu-dropdown").css("display", "block");
    $(".cmdb_lists").css("display", "block");
    $("#crmConfigError").html('');
    $("#display_inci_Data").css("display", "none");
    $(".incident_lists").css("display", "none");
    $("#incident_goback").css("display", "none");
    $("#getCatSub").css("display", "none");
    $(".cmdb_section").css("display", "block");
    $(".incident_section").css("display", "none");
//    alert("here");

    var CRMlogin_value = sessCustType;
    var flag = 0;

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

//        if ((custName == '') || (custName == 'undefined')) {
//            $("#Btn_oneNext").css("display", "block");
//            $("#crm_site_required").html("Please select customer");
//            flag++;
//
//        }
//        if ((custId == '') || (custId == 'undefined')) {
//            $("#Btn_oneNext").css("display", "block");
//            $("#crm_site_required").html("Please select customer");
//            flag++;
//        }
//        if ((custSiteName == '') || (custSiteName == 'undefined')) {
//            $("#Btn_oneNext").css("display", "block");
//            $("#crm_siteName_required").html("Please select Sitename");
//            flag++;
//        }
    }
    var selectedCrm = "SN";

    if (CRMlogin_value === '5') { // customer
        var selectedCrmData = {selectedCrm: selectedCrm, CRMlogin_value: CRMlogin_value, custId: custId};

    } else if (CRMlogin_value === '2') { // reseller
        var selectedCrmData = {selectedCrm: selectedCrm, CRMlogin_value: CRMlogin_value, custId: custId, custSiteName: custSiteName, custName: custName};
    }
    if (flag === 0) {
        $.ajax({
            url: "../lib/l-custAjax.php?function=CUSTAJX_getskippedCRM_DataMapping" + "&csrfMagicToken=" + csrfMagicToken,
            data: selectedCrmData,
            type: 'POST',
            dataType: 'json',
            success: function (gridData) {
                console.log(gridData);


                $(".cmdb_lists").css("display", "block");
                console.log(gridData[0].DT_RowId);

                var responseConfig = gridData[0].DT_RowId;

                if (responseConfig !== "Unconfigured") {
                    $(".CRM_Type").css("display", "none");
                    $(".CRM_ActionType").css("display", "block");
                    $("#crmdatamap").attr('checked', 'checked');
                    $(".burger-menu-dropdown").css("display", "block"); // uncomment for validation
                    $("#dataMapTable_searchbox").css("display", "block");

                    $(".se-pre-con").hide();
                    $('#dataMapTable').DataTable().destroy();
                    dataMapTable = $('#dataMapTable').DataTable({
                        scrollY: jQuery('#dataMapTable').data('height'),
                        scrollCollapse: true,
                        paging: true,
                        searching: true,
                        ordering: true,
                        aaData: gridData,
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
                            $("#dataMapTable_filter").hide();
                        },
                        drawCallback: function (settings) {
                            $(".dataTables_scrollBody").mCustomScrollbar({
                                theme: "minimal-dark"
                            });
                            $('.equalHeight').matchHeight();
                            $(".se-pre-con").hide();
//                        $("#dataMapTable_filter").hide();
                        }

                    });
                    $('#dataMapTable').on('click', 'tr', function () {
                        var rowID = dataMapTable.row(this).data();
                        var selected = rowID[8];
                        $("#selected").val(selected);
                        dataMapTable.$('tr.selected').removeClass('selected');
                        $(this).addClass('selected');
                    });


                } else {
                    $("#crmConfigError").css("display", "block");
                    $("#crmConfigError").css("color", "red");
                    $(".burger-menu-dropdown").css("display", "none");
                    $("#dataMapTable_searchbox").css("display", "none");
                    $("#crmConfigError").html("Please configure your crm Details");
                    return false;
                }



//                     var CRMlogin_value = $("input[name='loginType']:checked").val('');
//            var custId = $("#custName option:selected").val('');
//            var custName = $("#custName option:selected").text('');
//            var custSiteName = $("#custSiteName").val('');
//            var crm_url = $("#crm_url").val('');
//            var crm_username = $("#crm_username").val('');
//            var crm_password = $("#crm_password").val('');
////        var custSiteName = $("#custSiteName").val();
//            var crm_key = $("#crm_key").val('');



            },
            error: function (response) {
                console.log("Something went wrong in ajax call of skipFunction function");
                console.log(response);
            }
        });
    } else {
        return false;
    }

}
$("#dataMapTable_searchbox").keyup(function () {
    // alert("here");
    dataMapTable.search(this.value).draw();
});