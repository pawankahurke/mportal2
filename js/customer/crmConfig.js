/*
 This file is releated to CRM Configuration Page
 */


var crmList = {"SN": "Service Now", "ME": "Manage Engine", "OT": "OTRS", "SG": "Sugar CRM", "CW": "Cherwell"};

$(document).ready(function () {
    if (($("#custSiteNameList option:selected") === '') || ($("#custSiteNameList option:selected") === 'undefined')) {
        $("#crm_url").val('');
        $("#crm_username").val('');
        $("#crm_password").val('');
    }
    var CRMlogin_value = sessCustType;
    var compucomStatus = compucomValue;

    if ((compucomStatus === 0) || (compucomStatus === '0')) {
        $(".cmdbValue").css("display", "block");
        $(".cmdb_section").css("display", "block");
        $(".incident_section").css("display", "none");
        $(".compcomValue").css("display", "none");
        $(".crmTypelist").css("display", "block");
    } else if ((compucomStatus === 1) || (compucomStatus === '1')) {
        $(".compcomValue").css("display", "block");
        $(".cmdbValue").css("display", "none");
        $(".crmTypelist").css("display", "none");
    }
    if (sessCustType === '2') {
        getCustomers_And_Sitelist();
        $(".resellerSection").css("display", "block");

    } else if (sessCustType === '5') {
        getSingleCustomerSite();
        $(".resellerSection").css("display", "none");

    }
    getSiteLists();
    getCrmDetails();

    $('input[name=loginType]:radio').click(function () {
        selected_value = $("input[name='loginType']:checked").val();
        //alert(selected_value);
        if (selected_value === "customer") {
            $(".resellerSection").css("display", "none");
        } else {
            $(".resellerSection").css("display", "block");
        }
});

    $(".summary_section").css("display", "none");
    $("#Btn_oneNext").click(function () {
        $("#configure-selectTType").modal({backdrop: "static"});
    });

});



function getCrmDetails() {
    var custId = sessCId;

    if (sessCustType === '2') {
        var cidList = sessCustType;  // reseller
    } else if (sessCustType === '5') {
        var cidList = sessCustType; //customer
    }


    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_GetCRMDetails",
        data: {cidLsts: custId, sessCustType: sessCustType, 'csrfMagicToken': csrfMagicToken},
        type: 'POST',
        dataType: 'text',
        success: function (response) {

                $(".left-heading").show();
            var siteName = $("#siteNames-List option:selected").val();
            if ((siteName === 'undefined') || (siteName === undefined)) {
                siteName = " ";
            }
            var MysiteName = siteName.split("_");

              var resps = "";
              var resp = "";
            if (siteName.indexOf('_') > -1)
            {
                
                var arr = siteName.split("_");
                var len = arr.length;
                
                for(var i=0;i<len;i++){
                    if(isNaN(arr[i])){
                        resps += arr[i]+" ";
                    }else{
                        resp += arr[i]+" ";
                    }
                
                }
                      
            } else {
                resps = $("#siteNames-List option:selected").text();
            }
            
            $(".left-heading").html("<h3>Tickets Detail: " + resps + "</h3>");

                $(".snowCustDrop1").css("display", "inline-block");
                    $(".services-page").css("display", "none");
                    $(".incident_section").css("display", "block");

                    $(".CRM_Type").css("display", "none");
                    $(".crmConfigForm").css("display", "none");
                    Get_TicketLists();
                    $(".incident_listsMenu").css("display", "none");
                    $("#view-incidentLists").css("display", "none");
            $("#view-summary").css("display", "none");
            $("#edit-summary").css("display", "none");

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
function configureCrmDetails() {
    $("#payload_title").html("Create Payload JSON Data");
    $("#crmConfigError").css("display", "block");
    $("#crm-jsonData").css("display", "block");
    $(".nextConfig").css("display", "inline");
    $("#crmconfigBtn").css("display", "none");
    $(".tick_Error").css("display", "none");
    $("#crm-jsonData_closepayload").css("display", "none");
    $("#crmconfigBtn-cancel").css("display", "inline");
    $("#crmConfigError").html();
    var custNameVal = $("#custName option:selected").val();
    var custSiteName = $("#custSiteNameList option:selected").val();
    var crm_url = $("#crm_url").val();
    var crm_username = $("#crm_username").val();
    var crm_password = $("#crm_password").val();
    
    
    if ((custNameVal === '') || (custNameVal === undefined)) {
        $("#crmConfigError").css("color", "red").html("Please Select Customer");
        return false;
    }else if ((custSiteName === '') || (custSiteName === undefined)) {
        $("#crmConfigError").css("color", "red").html("Please Select SiteName");
        return false;
    }else if ((crm_url === '') || (crm_url === undefined)) {
        $("#crmConfigError").css("color", "red").html("Please Enter URL");
        return false;
    } else if ((crm_username === '') || (crm_username === undefined)) {
        $("#crmConfigError").css("color", "red").html("Please Enter Username");
        return false;
    } else if ((crm_password === '') || (crm_password === undefined)) {
        $("#crmConfigError").css("color", "red").html("Please Enter Password");
        return false;
    } else {


    $(".burger-menu-dropdown").css("display", "none"); // uncomment for validation
    $("#dataMapTable_searchbox").css("display", "none");
    $("#dataincidentTable_searchbox").css("display", "none");
    $(".cmdb_lists").css("display", "block");
    $(".submit-Datamap").css("display", "block");
    $("#add-new-datamaps").css("display", "block");
    $("#edit-datamaps").css("display", "block");
    $("#delete-datamaps").css("display", "block");
    $("#config-btn").css("display", "block");
    $("#Btn_oneNext").css("display", "none");
    $(".incident_lists").css("display", "none");
    var selectedCrm = $("input[name='crmradio']:checked").val();

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
//              var custSiteName = getSingleCustomerSite;

            } else {
                var custNameVal = $("#custName option:selected").val();
            var custName = $("#custName option:selected").text();
                var custSiteName = $("#custSiteNameList option:selected").val();
            var custId = $("#custName option:selected").val();

                if ((custNameVal === '') || (custNameVal === 'undefined')) {
                $("#Btn_oneNext").css("display", "block");
                $("#crm_site_required").html("Please select customer");
                flag++;

            }
                if ((custId === '') || (custId === 'undefined')) {
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
        var crm_key = $("#crm_key").val();
        if (selectedCrm === "SN") {
            crm_key = '-';
        }
        if (flag === 0) {

            $("#crmConfigError").html('<img src="../vendors/images/loader2.gif">');
            var CRMdata = {selectedCrm: selectedCrm, CRMlogin_value: CRMlogin_value, custName: custName, custId: custId, custSiteName: custSiteName, crm_url: crm_url, crm_username: crm_username, crm_password: crm_password, crm_key: crm_key, 'csrfMagicToken': csrfMagicToken};
            $.ajax({
                url: "../lib/l-custAjax.php?function=CUSTAJX_SetCRMDetails",
                data: CRMdata,
                type: 'POST',
                    dataType: 'json',
                    success: function (result) {
                        if ($.trim(result.response) === "success") { // uncomment for validation
                            $("#crm-jsonData").val(result.jsonData);
                            $("#crm-jsonData_closepayload").val(result.closejsonData);
                            $('#configure-selectTType').modal('show');
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


                        } else if ($.trim(result.response) === "invalid") { // uncomment for validation
                            $('#configure-crm').modal('hide');
                            $("#Btn_oneNext").css("display", "block"); // uncomment for validation

                        $("#crmConfigError").css("color", "red").html("Given " + custName + " credentials are not valid"); // uncomment for validation
                        } else if ($.trim(result.response) === "notexist") {// uncomment for validation
                            $('#configure-crm').modal('hide');

                            $("#Btn_oneNext").css("display", "block"); // uncomment for validation

                            $("#crmConfigError").css("color", "red").html("Customer doesn't exist..."); // uncomment for validation
                        }

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
}
$(".nextConfig-tickettype").click(function () {
    $("#configure-crm").modal({backdrop: "static"});
    var autoheal = $("#autohealcheck").val();
    var notifcheck = $("#notifcheck").val();

//    var selectedchckboxs = new Array();
//    $('input[name="tickeType"]:checked').each(function () {
//        selectedchckboxs.push(this.value);
//    });

    
    var selectedchckboxs = '{';
    $('input[name="tickeType"]:checked').each(function () {
        var key = $(this).attr('id');
        selectedchckboxs += '"' + key + '"' + ' : ' + '"' + $(this).val() + '",';
    });
    selectedchckboxs = selectedchckboxs.slice(0, -1);
    selectedchckboxs += '}';
    var selectedchckboxslength = selectedchckboxs.length;

//    alert(selectedchckboxs);

    if ((selectedchckboxslength === '0') || (selectedchckboxslength === 'undefined') || (selectedchckboxslength === '') || (selectedchckboxslength === 0)) {
        $(".tick_Error").css("display", "block");
    } else {
        $(".tick_Error").css("display", "none");
        $('#configure-selectTType').modal('hide');
        $("#ticketTyp-autoheal").val(selectedchckboxs);
        $('#configure-crm').modal('show');
    }
});


$("#action_details").click(function () {
    var selectedDataId = $('#dataincidentTable tbody tr.selected').attr('id');
    if ((selectedDataId === '') || (selectedDataId === 'undefined') || (selectedDataId === undefined)) {
        $('#select_errorModal').modal('show');
    } else {

        var ticketData = {selectedDataId: selectedDataId, 'csrfMagicToken': csrfMagicToken};
        $.ajax({
            url: "../lib/l-custAjax.php?function=CustViewAction",
            data: ticketData,
            type: 'POST',
            dataType: 'json',
            success: function (response) {

                $('.actionHeading').html(response.type);
                $('.aCTION_DetailsView').html(response.resp);
                $('#Action_DetailsModal').modal('show');

            }
        });
    }
});

$("#viewJSON").click(function () {
    $("#updateRespoTEID-success").html('');
    var selectedDataId = $("#siteNames-List option:selected").val();
    if((selectedDataId === '')||(selectedDataId === 'undefined') || (selectedDataId === undefined)){
         $('#configure-crmupdateError').modal('show');
    }else{
//    var siteName = getSitenameForselected(selectedDataId);
    var ticketData = {selectedDataId: selectedDataId, 'csrfMagicToken': csrfMagicToken};
    $.ajax({
        url: "../lib/l-custAjax.php?function=CustjsonViewEdit",
        data: ticketData,
        type: 'POST',
        dataType: 'text',
        success: function (response) {
            console.log(response);

            $('#crmupdate-jsonData').val(response);
            $('#configure-crmupdate').modal('show');
            $("#crmconfigBtn-crmupdate").css("display", "inline");
            $("#crmconfigBtn-crmupdate-cancel").css("display", "inline");
        }
});
    }

});

$("#viewcloseJSON").click(function () {
    $("#updateRespoTEID-success").html('');
    var selectedDataId = $("#siteNames-List option:selected").val();
    if ((selectedDataId === '') || (selectedDataId === 'undefined') || (selectedDataId === undefined)) {
        $('#configure-crmupdateError').modal('show');
    } else {
        var ticketData = {selectedDataId: selectedDataId, 'csrfMagicToken': csrfMagicToken};
        $.ajax({
            url: "../lib/l-custAjax.php?function=CustjsoncloseViewEdit",
            data: ticketData,
            type: 'POST',
            dataType: 'text',
            success: function (response) {
                console.log(response);

                $('#crmupdate-closejsonData').val(response);
                $('#configure-Closecrmupdate').modal('show');
                $("#crmconfigBtn-closecrmupdate").css("display", "inline");
                $("#crmconfigBtn-closecrmupdate-cancel").css("display", "inline");
            }
        });
    }

});

function getSitenameForselected(selectedDataId) {
    var ticketData = {selectedDataId: selectedDataId, 'csrfMagicToken': csrfMagicToken};
    $.ajax({
        url: "../lib/l-custAjax.php?function=CUST_getSitename",
        data: ticketData,
        type: 'POST',
        dataType: 'text',
        success: function (response) {
            console.log(response);
            $("#selectedTied").val(response);

        }
    });

}

function crmDetailsUpdate_Teid() {
    $("#updateRespoTEID-success").html('');
    var jsonData = $("#crmupdate-jsonData").val();
     var selectedSiteName = $("#siteNames-List option:selected").val();

    var CRMdata = {selectedSiteName: selectedSiteName, jsonData: jsonData, 'csrfMagicToken': csrfMagicToken};
    $.ajax({
        url: "../lib/l-custAjax.php?function=CUST_UpdateJson",
        data: CRMdata,
        type: 'POST',
        dataType: 'text',
        success: function (response) {
            console.log(response);
            if ($.trim(response) === "success") {
                $("#updateRespoTEID-success").html("Configured Data Successfully...");
                $('#configure-crmupdate').modal('hide');
                getSiteLists();
            } else {
//                $("#updateRespoTEID-success").html("Configured Data Successfully...");
//                $('#configure-crmupdate').modal('hide');
//                getSiteLists();
            }


        }
    });

}
function crmDetailsUpdate_CloseTeid() {
    $("#updateRespoTEID-success").html('');
    var jsonData = $("#crmupdate-closejsonData").val();
    var selectedSiteName = $("#siteNames-List option:selected").val();

    var CRMdata = {selectedSiteName: selectedSiteName, jsonData: jsonData, 'csrfMagicToken': csrfMagicToken};
    $.ajax({
        url: "../lib/l-custAjax.php?function=CUST_UpdateCloseJson",
        data: CRMdata,
        type: 'POST',
        dataType: 'text',
        success: function (response) {
            console.log(response);
            if ($.trim(response) === "success") {
                $("#siteNames-List").css("display", "block");
                $("#closeupdateRespoTEID-success").html("Configured Data Successfully...");
                $('#configure-Closecrmupdate').modal('hide');
                getSiteLists();
            } else {
//                $("#updateRespoTEID-success").html("Configured Data Successfully...");
//                $('#configure-crmupdate').modal('hide');
//                getSiteLists();
            }


        }
    });

}

$(".nextConfig").click(function () {
    $(".payload_title").html("Close Payload JSON Data");
    $("#crm-jsonData").hide();
     $("#crm-jsonData_closepayload").css("display","block");
    $(".nextConfig").hide();
    $("#crmconfigBtn").show();
   
});

function crmDetailsUpdate() {
    $(".snowCustDrop1").show();
    var CRMlogin_value = sessCustType;
    if (CRMlogin_value === '5') {
        var custName = '';
        var custId = sessCId;
        var custSiteName = $("#singlecustSiteNameValueJson").val();
        var crmjsonData = $("#crm-jsonData").val();
        var crmClosejsonData = $("#crm-jsonData_closepayload").val();
        var ticketType = $("#ticketTyp-autoheal").val();
    } else {
        var custName = $("#custName option:selected").text();
        var custSiteName = $("#custSiteNameList option:selected").val();
        var custId = $("#custName option:selected").val();
        var crmjsonData = $("#crm-jsonData").val();
        var crmClosejsonData = $("#crm-jsonData_closepayload").val();
        var ticketType = $("#ticketTyp-autoheal").val();
    }

    var CRMdata = {CRMlogin_value: CRMlogin_value, custName: custName, custId: custId, custSiteName: custSiteName, crmjsonData: crmjsonData, crmClosejsonData: crmClosejsonData, ticketType: ticketType, 'csrfMagicToken': csrfMagicToken};
    var flag = 0;
    if (flag === 0) {
        $.ajax({
            url: "../lib/l-custAjax.php?function=CUSTAJX_SetCRMDetailsComp",
            data: CRMdata,
            type: 'POST',
            dataType: 'text',
            success: function (response) {
//                console.log(response);
                if ($.trim(response) === "success") {
                    $('#configure-crm').modal('hide');
                    Get_TicketLists();
                    getSiteLists_Configured();
                    $(".left-heading").show();
//                    var siteName = $("#siteNames-List option:selected").val();
//                    var siteNameSel = custSiteName.split(__);
//                    $(".left-heading").html("<h3>Tickets Detail: "+siteNameSel[0]+ "</h3>");
                    $(".left-heading").html("<h3>Tickets Detail: "+custName+ "</h3>");
                    $("#updateRespo-success").html("Configured Data Successfully...");
                    $(".incident_section").css("display", "block");
                    $(".incident_lists").css("display", "block");
                    $("#display_inci_Data").css("display", "block");
//                    $(".left-heading").html("<h3>CRM Type: SN</h3>");
                    $(".CRM_Type").css("display", "none");
                    $(".crmConfigForm").css("display", "none");
                    $("#newConfigure").css("display", "block");
                    $("#view-incidentLists").css("display", "none");
                    $("#view-summary").css("display", "none");
                    $("#edit-summary").css("display", "none");
                    $("#Export-incidents").css("display", "block");
                    $("#viewJSON").css("display", "block");
                    $("#action_details").css("display", "block");
                    $("#viewcloseJSON").css("display", "block");
                    $("#crmconfigBtn").hide();
                    $("#crmconfigBtn-cancel").hide();
                } else if ($.trim(response) === "failed") {
                    $("#updateRespo-failed").html("Failed to Configure Data...");
                } else if ($.trim(response) === "empty") {
                    $("#updateRespo-failed").html("Machines are not configured for the site");

                }

            },
            error: function (response) {
                console.log("Something went wrong in ajax call of getCrmDetails function");
                console.log(response);
            }
        });
    } else {
        return false;
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
                stateSave: true,
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
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
 * Excel sheet will have details regarding Ticket Details of autoheal and notifications with ticket status.
 * It will have Url, Category, Priority, Status, Services with respective notification name.
 */
$("#exportCrmDetails").click(function () {
    window.location = "../lib/l-custAjax.php?function=CUSTAJX_ExportCRMDetails";
});


function getCustomers_And_Sitelist() {

    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_GetCRMcustomers" + "&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        dataType: 'text',
        success: function (response) {
//            console.log(response);
            $("#CRMcustomerList").html(response);
        },
        error: function (response) {
            console.log("Something went wrong in ajax call of getCrmDetails function");
            console.log(response);
        }
    });

}

function GetcustomerSites() {

    var resellerId = sessCId;
    var customerId = $("#custName option:selected").val();

    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_GetCRMcustSiteList&Cid="+customerId + "&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        dataType: 'text',
        success: function (response) {
            $("#CRMcustomerSiteList").html(response);

        },
        error: function (response) {
            console.log("Something went wrong in ajax call of getCrmDetails function");
            console.log(response);
        }
    });

}

function getcrmDetails_Onchange() {
    var resellerId = sessCId;
    var customerId = $("#custSiteNameList option:selected").val();

    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_GetCRMcrmDtlSiteList&Cid=" + customerId + "&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        dataType: 'json',
        success: function (response) {
            $("#crm_url").val(response.crmIP);
            $("#crm_username").val(response.crmUsername);
            $("#crm_password").val(response.crmPassword);
            $("#crm-jsonData").val(response.JsonData);
            $("#crm-jsonData_closepayload").val(response.jsonCloseData);
            var chkboxnoti = response.notification; // notification
            if ((chkboxnoti === '1') || (chkboxnoti === 1)) {
                $("#notifcheck").attr("checked", true);
            } else if ((chkboxnoti === '0') || (chkboxnoti === 0)) {
                $("#notifcheck").attr("checked", false);
            } else {
                $("#notifcheck").attr("checked", false);
            }

            var chkboxautohl = response.autoheal; // autoheal

            if ((chkboxautohl === '1') || (chkboxautohl === 1)) {
                $("#autohealcheck").attr("checked", true);
            } else if ((chkboxautohl === '0') || (chkboxautohl === 0)) {
                $("#autohealcheck").attr("checked", false);
            } else {
                $("#autohealcheck").attr("checked", false);
            }
        },
        error: function (response) {
            console.log("Something went wrong in ajax call of getCrmDetails function");
            console.log(response);
        }
    });
}


function getSingleCustomerSite() {
    var customerId = sessCId;

    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_GetCRM_singlecustSite&Cid="+customerId + "&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        dataType: 'text',
        success: function (response) {
            var response = $.trim(response);
            $("#singlecustSiteNameValue").val(response);
            $("#singlecustSiteNameValueJson").val(response);

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

});

$("#add-new-datamaps").click(function () {

    $('#add-new-datamaps-Form').modal('show');

});
$("#category_names").change(function () {

    var CRMlogin_value = sessCustType;

    if (CRMlogin_value === '5') {
        var custName = '';
        var custSiteName = '';
        var custId = sessCId;
        var custSiteName = $("#singlecustSiteNameValue").val();

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
    var customerLoginId = {customerId: custId, categoryId: categoryId, 'csrfMagicToken': csrfMagicToken};
    $.ajax({
        url: "../lib/l-custAjax.php?function=getNHDatname_Lists",
        data: customerLoginId,
        type: 'POST',
        dataType: 'html',
        success: function (response) {
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

    var categoryId = $("#category_names option:selected").val();

    $(".plus-btn").css("display", "none");
    $(".select-datalist").css("display", "block");
    var customerLoginId = {customerId: custId, categoryId: categoryId, 'csrfMagicToken': csrfMagicToken};
    $.ajax({
        url: "../lib/l-custAjax.php?function=getSNDatname_Lists",
        data: customerLoginId,
        type: 'POST',
        dataType: 'html',
        success: function (response) {
//            console.log(response);
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
    } else {
        var custName = $("#custName option:selected").text();
        var custSiteName = $("#custSiteName").val();
        var custId = $("#custName option:selected").val();
    }

    $(".plus-btn").css("display", "none");
    $(".select-datalist").css("display", "block");
    var customerLoginId = {customerId: custId, CRMlogin_value: CRMlogin_value, categoryId: categoryId, categoryName: categoryName, NH_DataId: NH_DataId, NH_DataName: NH_DataName, SN_DataVal: SN_DataVal, SN_DataName: SN_DataName, 'csrfMagicToken': csrfMagicToken};
    $.ajax({
        url: "../lib/l-custAjax.php?function=config_NewDataNames",
        data: customerLoginId,
        type: 'POST',
        dataType: 'html',
        success: function (response) {

            $("#loadingSuccessMsg").css("display", "block");
            if ($.trim(response) === "exists") {
                $("#loadingSuccessMsg").css("color", "red");
                $("#loadingSuccessMsg").html("Data Already Configured");
            } else if ($.trim(response) === "success") {

                $("#loadingSuccessMsg").css("color", "green");
                $("#loadingSuccessMsg").html("Successfully configured Data");
                 var selectedCrm = $("input[name='crmradio']:checked").val();
                 if (CRMlogin_value === '5') { // customer
                            selectedCrmData = {selectedCrm: selectedCrm, CRMlogin_value: CRMlogin_value, custId: custId};

                        } else if (CRMlogin_value === '2') { // reseller
                            selectedCrmData = {selectedCrm: selectedCrm, CRMlogin_value: CRMlogin_value, custId: custId, custSiteName: custSiteName, custName: custName};
                        }
                        configureNext_levelCRM(selectedCrmData);
                
            } else if ($.trim(response) === "failed") {
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
    var Data = {id: selectedDataId, 'csrfMagicToken': csrfMagicToken};
    $.ajax({
        url: "../lib/l-custAjax.php?function=EDIT_DataMapValues",
        data: Data,
        type: 'POST',
        dataType: 'html',
        success: function (response) {
            $('#editDatmapForm').html(response);
            $('#edit-datamaps-Form').modal('show');
        }

    });

}

function unconfigure_DataMapping(selectedDataId) {

    var Data = {id: selectedDataId, 'csrfMagicToken': csrfMagicToken};
    $.ajax({
        url: "../lib/l-custAjax.php?function=unconfigure_Dataid",
        data: Data,
        type: 'POST',
        dataType: 'html',
        success: function (response) {
            if ($.trim(response) === 'success') {
                $("#show-deleteError").modal('show');
                $("#delete-msg").html('unconfiguration is successfull');
            } else if ($.trim(response) === 'failed') {
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

    } else {
        var custName = $("#custName option:selected").text();
        var custSiteName = $("#custSiteName").val();
        var custId = $("#custName option:selected").val();
    }

    $(".plus-btn").css("display", "none");
    $(".select-datalist").css("display", "block");
    var customerLoginId = {customerId: custId, categoryId: categoryId, categoryName: categoryName, NH_DataId: NH_DataId, NH_DataName: NH_DataName, SN_DataVal: SN_DataVal, SN_DataName: SN_DataName, DMedit_id: DMedit_id, 'csrfMagicToken': csrfMagicToken};
    $.ajax({
        url: "../lib/l-custAjax.php?function=config_editedDataNames",
        data: customerLoginId,
        type: 'POST',
        dataType: 'html',
        success: function (response) {

            $("#loadingSuccessMsgEdit").css("display", "block");
            if ($.trim(response) === "exists") {
                $("#loadingSuccessMsgEdit").css("color", "red");
                $("#loadingSuccessMsgEdit").html("Data Already Configured");
            } else if ($.trim(response) === "success") {

                $("#loadingSuccessMsgEdit").css("color", "green");
                $("#loadingSuccessMsgEdit").html("Successfully configured Data");
            } else if ($.trim(response) === "failed") {
                $("#loadingSuccessMsgEdit").css("color", "red");

                $("#loadingSuccessMsgEdit").html("Failed to configure Data");
            }

        }

    });
});

$("#incident_goback").click(function () {
    $(".cmdb_RadioBtn").prop("checked", true);
    skipConfiguration();

});

$(".cmdb_goback").click(function () {
    location.reload();

});

$("#view-incidentLists").click(function () {
    var siteName = $("#siteNames-List option:selected").val();
    
    if(siteName === undefined || siteName === 'undefined'){
        $(".left-heading").html("<h3>Ticket Details:</h3>");
    } else {
    var MysiteName = siteName.split("__");
        $(".left-heading").html("<h3>Ticket Details: " + MysiteName[0] + "</h3>");
    }
    Get_TicketLists();
    $(".snowCustDrop1").show();
    $(".incident_section").css("display", "block");
    $(".summary_section").css("display", "none");
    $("#view-summary").css("display", "none");
    $("#edit-summary").css("display", "none");
    $(".incident_lists").css("display", "block");
    $("#display_inci_Data").css("display", "block");
    $(".CRM_Type").css("display", "none");
    $(".crmConfigForm").css("display", "none");
    $("#newConfigure").css("display", "block");
    $("#view-incidentLists").css("display", "none");
    $("#Export-incidents").css("display", "block");
    $("#viewJSON").css("display", "block");
    $("#action_details").css("display", "block");
    $("#viewcloseJSON").css("display", "block");


});

$("#edit-summary").click(function () {
    $(".tick_Error_summ").css("display", "none");
    var selValue = $('#ticketsummary_Grid tbody tr.selected').attr('id');
    if ((selValue === 'undefined') || (selValue === undefined)) {
        $("#select_errorModal").modal('show');
    } else {
        $.ajax({
            url: "../lib/l-custAjax.php?function=GET_Configurations&selectedDataId=" + selValue + "&csrfMagicToken=" + csrfMagicToken,
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                console.log(response);
                $("#editID_configs").val(selValue);
                console.log(response);
                $("#edit_report_id").html(response);
                var chkboxtckt = response.tcktcreation; // notification
                if (chkboxtckt === 'enabled') {
                    $("#edit_ticketcrtn").prop("checked", true);
                } else if (chkboxtckt === 'disabled') {
                    $("#edit_ticketcrtn").prop("checked", false);
                } else {
                    $("#edit_ticketcrtn").prop("checked", false);
                }
                var chkboxauto = response.autoheal; // notification
                if ((chkboxauto === '1') || (chkboxauto === 1)) {
                    $("#edit_autohealcheck").prop("checked", true);
                } else if ((chkboxauto === '0') || (chkboxauto === 0)) {
                    $("#edit_autohealcheck").prop("checked", false);
                } else {
                    $("#edit_autohealcheck").prop("checked", false);
                }
                var chkboxnoti = response.notification; // notification
                if ((chkboxnoti === '1') || (chkboxnoti === 1)) {
                    $("#edit_notifcheck").prop("checked", true);
                } else if ((chkboxnoti === '0') || (chkboxnoti === 0)) {
                    $("#edit_notifcheck").prop("checked", false);
                } else {
                    $("#edit_notifcheck").prop("checked", false);
                }
                var chkboxselfhl = response.selfhelp; // notification
                if ((chkboxselfhl === '1') || (chkboxselfhl === 1)) {
                    $("#edit_selfhelpcheck").prop("checked", true);
                } else if ((chkboxselfhl === '0') || (chkboxselfhl === 0)) {
                    $("#edit_selfhelpcheck").prop("checked", false);
                } else {
                    $("#edit_selfhelpcheck").prop("checked", false);
                }
                var chkboxshdle = response.schedule; // notification
                if ((chkboxshdle === '1') || (chkboxshdle === 1)) {
                    $("#edit_schedulecheck").prop("checked", true);
                } else if ((chkboxshdle === '0') || (chkboxshdle === 0)) {
                    $("#edit_schedulecheck").prop("checked", false);
                } else {
                    $("#edit_schedulecheck").prop("checked", false);
                }
                $("#Editsummary_modal").modal('show');
            }
        });
    }

});

$(".update_ticketCreation").click(function () {
    var selectedChks = new Array();
    $('input[name="edit_tickeType"]:checked').each(function () {
        selectedChks.push(this.value);
    });
    
    var selectedchckboxs = '{';
    $('input[name="edit_tickeType"]:checked').each(function () {
        var key = $(this).attr('id');
        selectedchckboxs += '"' + key + '"' + ' : ' + '"' + $(this).val() + '",';
    });
    selectedchckboxs = selectedchckboxs.slice(0, -1);
    selectedchckboxs += '}';
    
    var editID_configs = $("#editID_configs").val();
    var selectedchckboxslength = selectedChks.length;

//    if ((selectedchckboxslength === '0') || (selectedchckboxslength === 'undefined') || (selectedchckboxslength === '') || (selectedchckboxslength === 0)) {
//        $(".tick_Error_summ").css("display", "block");
//    } else {
        $.ajax({
            url: "../lib/l-custAjax.php?function=CUSTAJX_updateConfigs&selectedChks=" + selectedchckboxs + "&editID_configs=" + editID_configs + "&csrfMagicToken=" + csrfMagicToken,
            type: 'POST',
            dataType: 'text',
            success: function (msg) {
                var response = msg.trim();
                if (response === "success") {
                    $(".tick_Error_summ").css("display","none");
                    $(".add-user-cancel-btn").click();
                    view_SummaryFunc();

                } else if (response === "failed") {
                    $(".add-user-cancel-btn").click();
                }
            },
            error: function (response) {
                console.log("Something went wrong in ajax call of getCrmDetails function");
                console.log(response);
            }
        });
//    }
});
function view_SummaryFunc() {
     //$(".se-pre-con").show();
    $(".left-heading").html("<h3>Configuration Summary</h3>");
    $(".services-page").css("display", "none");
    $("#view-summary").css("display", "none");
    $("#edit-summary").css("display", "block");
    $("#view-incidentLists").css("display", "block");
    $(".summary_section").css("display", "block");
    $("#newConfigure").css("display", "block");

    $.ajax({
        type: "GET",
        url: "../lib/l-custAjax.php?function=CUSTAJX_GetSummary&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        success: function (msg) {
            console.log(msg);
            $(".se-pre-con").hide();
            $('#ticketsummary_Grid').DataTable().destroy();
            ticketsummary_Grid = $('#ticketsummary_Grid').DataTable({
                scrollY: jQuery('#ticketsummary_Grid').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: msg,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                processing: true,
                responsive: true,
                stateSave: true,
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search",
//                    "emptyTable": "No configuration of any Customer found, please Configure Customer from top Menu (bread crumb)",
                    "processing": "<img src='../vendors/images/loader2.gif'> Loading..."
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                initComplete: function (settings, json) {

                    $("#ticketsummary_Grid_filter").hide();
                },
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }

            });
            $('#ticketsummary_Grid').on('click', 'tr', function () {
                var rowID = ticketsummary_Grid.row(this).data();
                var selected = rowID['DT_RowId'];

                $("#selected").val(selected);
                ticketsummary_Grid.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            });

            $("#TicketTable_searchbox").keyup(function () {
                ticketsummary_Grid.search(this.value).draw();
            });
            //$('#summary_modal').modal('show');
        },
        error: function (response) {
            console.log("Something went wrong in ajax call of view-summary function");
            console.log(response);
        }

    });
}


function getSiteLists() {

    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_GetSitelist&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        dataType: 'text',
        success: function (response) {
//            console.log(response);

            $(".snowCustDrop1").html(response);
//            var siteName = $("#siteNames-List option:selected").val();
//            $(".left-heading").html("<h3>Ticket Details: " + siteName + "</h3>");
        },
        error: function (response) {
            console.log("Something went wrong in ajax call of getCrmDetails function");
            console.log(response);
        }
    });

}

function getSiteLists_Configured() {
    
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
    var configuredSite = {custSiteName:custSiteName, 'csrfMagicToken': csrfMagicToken}
    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_GetSitelistConfigured",
        type: 'POST',
        data:configuredSite,
        dataType: 'text',
        success: function (response) {
            console.log(response);
            $(".snowCustDrop1").html(response);
//            var siteName = $("#siteNames-List option:selected").val();
//            $(".left-heading").html("<h3>Ticket Details: " + siteName + "</h3>");
        },
        error: function (response) {
            console.log("Something went wrong in ajax call of getCrmDetails function");
            console.log(response);
        }
    });

}

function getTicketLists_Onchange() {
    $(".se-pre-con").show();
    var siteName = $("#siteNames-List option:selected").val();
    var MysiteName = siteName.split("__");
    var MysiteName = siteName.split("_");

              var resps = "";
              var resp = "";
            if (siteName.indexOf('_') > -1)
            {
                
                var arr = siteName.split("_");
                var len = arr.length;
                
                for(var i=0;i<len;i++){
                    if(isNaN(arr[i])){
                        resps += arr[i]+" ";
                    }else{
                        resp += arr[i]+" ";
                    }
                
                }
                      
            }else{
                resps = $("#siteNames-List option:selected").text();
            }
    $(".left-heading").html("<h3>Ticket Details: " + MysiteName[0] + "</h3>");
    var selectedCrmData = {siteName: siteName, 'csrfMagicToken': csrfMagicToken};
    $.ajax({
        url: "../lib/l-custAjax.php?function=GET_TicketLists_Onchange",
        data: selectedCrmData,
        type: 'POST',
        dataType: 'json',
        success: function (gridData) {
            console.log(gridData);

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
                   stateSave: true,
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search",
                    "emptyTable":     "No configuration of any Customer found, please Configure Customer from top Menu (bread crumb)",
                     "processing": "<img src='../vendors/images/loader2.gif'> Loading..."
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
            $("#TicketTable_searchbox").keyup(function () {
                dataincidentTable.search(this.value).draw();
            });


//                }
        },
        error: function (response) {
            console.log("Something went wrong in ajax call of skipFunction function");
            console.log(response);
        }

    });

}

function Get_TicketLists() {
    $("#TicketTable_searchbox").css("display", "block");
    var CRMlogin_value = sessCustType;
    var Nid = $(this).find("a").attr("name");
    if (CRMlogin_value === '5') {
        var custName = '';
        var custSiteName = '';
        var custId = sessCId;
        var custSiteName = $("#singlecustSiteNameValue").val();
    } else {
        var custName = $("#custName option:selected").text();
        var siteName = $(".siteNames-List_reseller option:selected").val();
        var custSiteName = $("#custSiteName").val();
        var custId = $("#custName option:selected").val();
    }


    var selectedCrmData = {Nid: Nid, CRMlogin_value: CRMlogin_value, custId: custId,siteName:siteName, 'csrfMagicToken': csrfMagicToken};
    $.ajax({
        url: "../lib/l-custAjax.php?function=GET_TicketLists",
        data: selectedCrmData,
        type: 'POST',
        dataType: 'json',
        success: function (gridData) {
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
                sortable: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                processing: true,
                responsive: true,
                   stateSave: true,
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search",
                    "emptyTable": "No configuration of any Customer found, please Configure Customer from top Menu (bread crumb)",
                    "processing": "<img src='../vendors/images/loader2.gif'> Loading..."
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
//                var selected = rowID['DT_RowId'];

                $("#selected").val(selected);
                dataincidentTable.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            });

            $("#TicketTable_searchbox").keyup(function () {
                dataincidentTable.search(this.value).draw();
            });


//                }
        },
        error: function (response) {
            console.log("Something went wrong in ajax call of skipFunction function");
            console.log(response);
        }

    });
}

function get_JSONPayload(selected) {
    $(".close_resp").css("display","none");

    if ((selected === '') || (selected === 'undefined') || (selected === undefined)) {
        $('#configure-crmupdateError').modal('show');
        
    } else {
        var ticketData = {selectedDataTeid: selected, 'csrfMagicToken': csrfMagicToken};
        $.ajax({
            url: "../lib/l-custAjax.php?function=CUST_comppayload",
            data: ticketData,
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                console.log(response);
//                alert(response.ticketType);
                $('#payloadData').modal('show');
//                response.ticketType == 1;
//                $("#sentPaload_Data").val(response.sentPayload);
//                $("#receivedPayload_Data").val(response.receidPayload);
                if ((response.ticketType === '1') || (response.ticketType === 1)) {
                    $(".create_resp").css("diaplay", "block");
                    $(".close_resp").css("diaplay", "block");
                    $(".close_resp").show();
                $("#sentPaload_Data").val(response.sentPayload);
                $("#receivedPayload_Data").val(response.receidPayload);

                    $("#creareclose_sent").val(response.closeSentPayload);
                    $("#creareclose_resp").val(response.closeRespPayload);
                    
                } else if ((response.ticketType === '2') || (response.ticketType === 2)) {
                    $(".create_resp").css("diaplay", "block");
                    $(".close_resp").css("diaplay", "block");

                    $("#sentPaload_Data").val(response.sentPayload);
                    $("#receivedPayload_Data").val(response.receidPayload);
                    if ((response.closeSentPayload == "NULL") || (response.closeRespPayload == "NULL")) {
                        $(".close_resp").hide();
                    } else {
                        $(".close_resp").show();
                        $("#creareclose_sent").val(response.closeSentPayload);
                        $("#creareclose_resp").val(response.closeRespPayload);
                }
                }else if ((response.ticketType === '3') || (response.ticketType === 3)) {
                    $(".create_resp").css("diaplay", "block");
                    $(".close_resp").css("diaplay", "block");
                    $(".close_resp").show();
                $("#sentPaload_Data").val(response.sentPayload);
                $("#receivedPayload_Data").val(response.receidPayload);

                    $("#creareclose_sent").val(response.closeSentPayload);
                    $("#creareclose_resp").val(response.closeRespPayload);
                    
                }else if ((response.ticketType === '4') || (response.ticketType === 4)) {
                    $(".create_resp").css("diaplay", "block");
                    $(".close_resp").css("diaplay", "block");
                    $(".close_resp").show();
                    $("#sentPaload_Data").val(response.sentPayload);
                    $("#receivedPayload_Data").val(response.receidPayload);

                    $("#creareclose_sent").val(response.closeSentPayload);
                    $("#creareclose_resp").val(response.closeRespPayload);

                }

        },
        error: function (response) {
            console.log("Something went wrong in ajax call of configureCrm function");
            console.log(response);
        }
            });
    }
    ;
}

$("#newConfigure").click(function () {

    $(".snowCustDrop1").hide();
    $(".summary_section").css("display", "none");
    $(".services-page").css("display", "block");
    $(".incident_section").css("display", "none");
    $(".incident_lists").css("display", "none");
    $("#display_inci_Data").css("display", "none");
    $(".left-heading").html("<h3>CRM Type: SN</h3>");
    $(".CRM_Type").css("display", "block");
    $(".crmConfigForm").css("display", "block");
    $("#newConfigure").css("display", "none");
    $("#TicketTable_searchbox").css("display", "none");
    $("#viewJSON").css("display", "none");
    $("#action_details").css("display", "none");
    $("#viewcloseJSON").css("display", "none");
    $("#view-incidentLists").css("display", "block");
    $("#view-summary").css("display", "block");
    $("#edit-summary").css("display", "none");
    $("#Export-incidents").css("display", "none");
    $("#Btn_oneNext").css("display", "block");
    $("#crmConfigError").css("display", "none");
    $(".error").html('');
});


function getJsonData() {

    var resellerId = sessCId;
    var customerSite = $("#siteNames-List option:selected").val();

    var valdata = {customerSite: customerSite, 'csrfMagicToken': csrfMagicToken};
    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_GetJsonDataC",
        data: valdata,
        type: 'POST',
        dataType: 'json',
        success: function (data) {
            alert(data);
            console.log("responsefdsfd");
            var response = JSON.parse(data);
            alert(response.crmUsername+"test");
            $("#CRMcustomerSiteList").val(response.Sitelist);
            $("#CRMcustomerList").val(response.Customerlist);
            $("#crm_url").val(response.crmIP);
            $("#crm_username").val(response.crmUsername);
            $("#crm_password").val(response.crmPassword);
            $("#crm-jsonData").val(response.JsonData);
        },
        error: function (response) {
            console.log("Something went wrong in ajax call of getCrmDetails function");
            console.log(response);
        }
    });
}
$(".ccticketsbtn").click(function (){
    alert("here");
});
function skipConfiguration() {

    $(".left-heading").html("<h3>CRM Type: SN</h3>");
    $(".cmdb_section").css("display", "none");
    $(".incident_section").css("display", "block");
    $(".incident_lists").css("display", "block");
    $(".cmdb_lists").css("display", "none");
    $("#display_inci_Data").css("display", "block");

    $(".burger-menu-dropdown").css("display", "none"); 
    $("#dataMapTable_searchbox").css("display", "none");

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
        var selectedCrmData = {selectedCrm: selectedCrm, CRMlogin_value: CRMlogin_value, custId: custId, 'csrfMagicToken': csrfMagicToken};

    } else if (CRMlogin_value === '2') { // reseller
        var selectedCrmData = {selectedCrm: selectedCrm, CRMlogin_value: CRMlogin_value, custId: custId, custSiteName: custSiteName, custName: custName, 'csrfMagicToken': csrfMagicToken};
    }
    if (flag === 0) {
        $.ajax({
            url: "../lib/l-custAjax.php?function=CUSTAJX_getskippedCRM_DataMapping",
            data: selectedCrmData,
            type: 'POST',
            dataType: 'json',
            success: function (gridData) {
//                console.log(gridData);


                $(".cmdb_lists").css("display", "block");
//                console.log(gridData[0].DT_RowId);

                var responseConfig = gridData[0].DT_RowId;

                if ($.trim(response) !== "Unconfigured") {
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
                           stateSave: true,
                        "stateSaveParams": function (settings, data) {
                            data.search.search = "";
                        },
                        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
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
    dataMapTable.search(this.value).draw();
});



function exportTickets() {
    $("#datefrom").val('');
    $("#dateto").val('');
    $("#export_duraion").modal('show');

}

function exportTicket_Details() {
    
 var siteName = $("#siteNames-List option:selected").val();
     var dateFrom = $('#datefrom').val();
        var dateTo = $('#dateto').val();

    if (dateFrom === '') {
        $('#startDateMsg').html('<span>Please select date</span>');
        setTimeout(function () {
            $("#startDateMsg").fadeOut(2000);
        }, 1000);
        return;
}
    if (dateTo === '') {
        $('#endDateMsg').html('<span>Please select date</span>');
        setTimeout(function () {
            $("#endDateMsg").fadeOut(2000);
        }, 1000);
        return;
    }

    var start = (new Date(dateFrom).getTime());
    var to = (new Date(dateTo).getTime());
    if (start === to) {
        var dateFrom = $('#datefrom').val();
        var dateTo = $('#dateto').val();
        window.location.href = '../lib/l-custAjax.php?function=CUSTAJAX_ExportTickets&siteName=' + siteName + "&startDate=" + dateFrom + "&endDate=" + dateTo;
    } else if (start > to) {
        $('#less_moreDate').html('<span>Start Date should be less than end date</span>');
        setTimeout(function () {
            $("#less_moreDate").fadeOut(2000);
        }, 1000);
        return;
    } else {
        var dateFrom = $('#datefrom').val();
        var dateTo = $('#dateto').val();
        window.location.href = '../lib/l-custAjax.php?function=CUSTAJAX_ExportTickets&siteName='+siteName+"&startDate="+dateFrom+"&endDate="+dateTo;
    }
}

$(".date_format").datetimepicker({
    format: "mm/dd/yyyy h:i:s",
    autoclose: true,
    todayBtn: false,
    pickerPosition: "bottom-left",
    startDate: "2018-01-01",
    endDate: new Date()
});



