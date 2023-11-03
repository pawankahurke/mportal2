/**
 * This file belongs to "Commercial/MSP" Resellers only.
 * This file is created for all provisioning functionality for "Commercial/MSP" flow.
 * In this file "msp" indicates "managed service provider" or "Commercial" bussiness flow.
 */

$(document).ready(function () {
    $('.order-table').DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: true,
        responsive: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ of _TOTAL_ entries",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>'
    });
    
    get_CommercialSites();
});

/**
 * Fetch all resellers list in json format which is required for Datatable.
 */
function get_CommercialSites(){
    $('#msp_Sites_Grid').dataTable().fnDestroy();
    siteGrid = $('#msp_Sites_Grid').DataTable({
        scrollY: jQuery('#msp_Sites_Grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        searching: true,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            },{
                className: "ignore", targets: [0]
            },
            {
                className: "centerAlignTd", targets: [1]
            }],
        ajax: {
            url: "../lib/l-entp.php?function=ENTP_GetCustomerOrderGrid&custid="+sel_CustomerEid+"&csrfMagicToken=" + csrfMagicToken,
            type: "POST"
        },
        columns: [
            {"data": "orderNum"},
            {"data":"pcCnt"},
            {"data":"orderDt"},
            {"data":"cntDt"}
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "_START_-_END_ of _TOTAL_ entries",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function(settings, json) {
           siteGrid.$('tr:first').click();
        },
        drawCallback: function(settings) {
                $(".dataTables_scrollBody").mCustomScrollbar({
                    theme: "minimal-dark"
                });
        }
    });
    
    $('#msp_Sites_Grid tbody').on('click', 'tr', function() {
        siteGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var id = siteGrid.row(this).id();
        getDeviceList(id);
    });
        
}

function getDeviceList(rowId){
    
    var sel_customerNum = getCustomerId(rowId, 0);
    var sel_orderNum    = getCustomerId(rowId, 1);
    var sel_compId      = getCustomerId(rowId, 2);
    var sel_procId      = getCustomerId(rowId, 3);
   
   
    $('#msp_Device_Grid').dataTable().fnDestroy();
    deviceGrid = $('#msp_Device_Grid').DataTable({
        scrollY: jQuery('#msp_Device_Grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        searching: true,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            },{
                className: "ignore", targets: [0,1,2]
            }],
        ajax: {
            url: "../lib/l-msp.php?function=MSP_GetSitesDeviceGrid&custId="+sel_compId+"&procId="+sel_procId+"&custNum="+sel_customerNum+"&ordNum="+sel_orderNum+"&csrfMagicToken=" + csrfMagicToken,
            type: "POST"
        },
        columns: [
             {"data": "devicename"},
             {"data": "installDt"},
             {"data": "uninstallDt"},
             {"data": "status"}
            
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "_START_-_END_ of _TOTAL_ entries",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function(settings, json) {
           
        },
        drawCallback: function(settings) {
                $(".dataTables_scrollBody").mCustomScrollbar({
                    theme: "minimal-dark"
                });
        }
    });
    
    $('#msp_Device_Grid tbody').on('click', 'tr', function() {
        deviceGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var id = deviceGrid.row(this).id();
        //enableOptions(id);
    });
    
    $("#customersite_searchbox").keyup(function () {//customer search code        
        deviceGrid.search(this.value).draw();
    });
}
function addSitePopup(){
   $("#site_name").val(''); 
    
 }
function customer_link(url){
    $('#msp_SiteLink').modal('show');
    if(url === "Url is not available"){
        $('#site_successMsg').val(url);
        $('#site_download_url').hide();
        $("#copy_link1").hide();
    }else{
        $("#site_download_url").val(url);
        $('#site_download_url').show();
        $("#copy_link1").show();
        $('#site_successMsg').val("Please click on copy button to copy download url");
    }
    
}

function getCustomerDownloadURL(){
    var rowId = $('#msp_Sites_Grid tbody tr.selected').attr('id');
    var custRowId = rowId.split('---');
    var cid = custRowId[2];
    var pid = custRowId[3];
    var custNum = custRowId[0];
    var ordNum = custRowId[1];
    
    
    if(rowId !== undefined) {
        $.ajax({
            url: '../lib/l-msp.php?function=MSP_getSiteDnlURL&custId=' + cid + "&procId=" + pid + "&custNum=" + custNum + "&ordNum=" + ordNum +"&csrfMagicToken=" + csrfMagicToken,
            type: 'POST',
            dataType: 'text',
            success: function(response) {
                $('#msp_SiteLink').modal('show');
                $("#site_download_url").val(response);
                $("copy_link1").show();
             },
            error: function(response) {
                console.log(response);
                console.log("something went wrong in ajax call in  function");
            }
        });
    } else {

    }
}


$('#copy_link1').click(function() {
    var urlField = document.querySelector('#site_download_url');
    urlField.select();
    document.execCommand('copy');
});

function create_Site(){
    var sitename = $("#site_name").val();
    if(sitename == ""){
        $("#required_Sitename").html("Please enter site name");
    }else if(!validate_alphanumeric_underscore(sitename)){
        $("#required_Sitename").html("Enter only Alphanumeric values(A-Z-0-9&_).");
    }else{
        $("#required_Sitename").html("");
        var m_data = new FormData();
        m_data.append('function', 'MSP_Create_Site');
        m_data.append('sitename', sitename);
        m_data.append("csrfMagicToken",csrfMagicToken);
         $.ajax({
            url: '../lib/l-msp.php',
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if($.trim(response.status) == "success"){
                    $('#site_successMsg').val(response.msg);
                    $('#msp_CreateSite').modal('hide');
                    $('#msp_SiteLink').modal('show');
                    $("#site_download_url").val(response.link);
                    $("copy_link1").show();
                    get_CommercialSites();
                }else{
                    $("#required_Sitename").html(response.msg);
                }
            },
            error: function(response) {
                $("#required_Sitename").html("Error Occurred");
                console.log('Error In create_Site function : '+response);
            }
        });
    }
}

function getCustomerId(selectedId, index) {
    var custRowId = selectedId.split('---');
    var cust_id = custRowId[index];
    return cust_id;
}

function create_Provision() {
    var customerNum = $("#prov_customerNumber").val();
    var orderNum = $("#prov_orderNumber").val();
    var sku = $("#resel_ProvSku").val();
    var errorVal = 0;
    $(".error").html("*");
    $('.provCustRequired').each(function() {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();
        if ($.trim(field_value) === "" && field_id !== "") {
            $("#prov_" + field_id).html(" required");
            errorVal++;
        } else if (field_id === "prov_customerNumber") {
            if (!validate_OrderNumber(field_value)) {
                $("#prov_" + field_id).html(" Enter valid customer number with length 8 to 16");
                errorVal++;
            }

        } else if (field_id === "prov_orderNumber") {
            if (!validate_OrderNumber(field_value)) {
                $("#prov_" + field_id).html(" Enter valid order number with length 8 to 16");
                errorVal++;
            }

        } else if (field_id === "resel_ProvSku") {
            if (field_value === "0") {
                $("#prov_" + field_id).html(" Please select SKU");
                errorVal++;
            }

        }
    });
    if (errorVal === 0) {
        var m_data = new FormData();

        m_data.append('customerNum', customerNum);
        m_data.append('orderNum', orderNum);
        m_data.append('eid', $("#customerEid").val());
        m_data.append('skuVal', sku);
        m_data.append("csrfMagicToken",csrfMagicToken);
        $.ajax({
            url: '../lib/l-entp.php?function=ENTP_NewProvision',
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status === "SUCCESS") {
                    $('#site_successMsg').val(response.msg);
                    $('#msp_CreateProvision').modal('hide');
                    $('#msp_SiteLink').modal('show');
                    $("#site_download_url").val(response.link);
                    $("copy_link1").show();
                    get_CommercialSites();
                    
                } else {
                    $("#prov_CustomerMsg").val(response.msg);
                }

            },
            error: function(response) {

            }
        });
    }

}


function getCustomerDetails() {
    var sel_Customer = $('#msp_Sites_Grid tbody tr.selected').attr('id');
    var m_data = new FormData();
    
    m_data.append('eid', sel_Customer.split("---")[2]);
    m_data.append("csrfMagicToken",csrfMagicToken);
    
    $.ajax({
        url: '../lib/l-entp.php?function=ENTP_GetEntityDetails',
        data: m_data,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#customerEid").val($.trim(response.eid));
            $("#prov_CustName").val($.trim(response.companyName));
            $("#prov_CustEmail").val($.trim(response.emailId));
         },
        error: function(response) {

        }
    });
}

function getSkuDetails(){
    var selectedSku = $("#resel_ProvSku").val();
    var m_data = new FormData();
    
    m_data.append('skuId', selectedSku);
    m_data.append("csrfMagicToken",csrfMagicToken);
    $.ajax({
        url: 'entpFun.php?function=getSkuDetails',
        data: m_data,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            $("#skuEndDate").val(response);
        },
        error: function(response) {

        }
    });
}

function getSkuList() {

    $.ajax({
        type: 'POST',
        url: '../lib/l-entp.php',
        data: "function=ENTP_GetSkuDtlsByCid"+"&csrfMagicToken=" + csrfMagicToken,
        async: false,
        dataType: 'json',
        success: function(data) {
            var skuStr = "";
            skuStr = '<option value="0" >Please select SKU</option>';
            for (var i = 0; i < data.length; i++) {
                skuStr += '<option value="' + data[i].id + '" >' + data[i].skuName + '</option>';
            }
            $("#resel_ProvSku").html(skuStr);
            $("#resel_editcompanySku").html(skuStr);
            $(".selectpicker").selectpicker("refresh");
        }
    });
}

$("#exportAllSites").click(function(){
    location.href='../lib/l-msp.php?function=MSP_ExportAllSites&custid='+sel_CustomerEid+"&csrfMagicToken=" + csrfMagicToken;
})

//###################################### Boostrap Modal CLOSE/OPEN Events Start ################################################//

$('#msp_CreateReseller').on('hidden.bs.modal', function() {
    $("#msp_CreateReseller input[type=text]").not("[readonly]").val('');
    $("#msp_CreateReseller .error").html("*");
    $("#add_ResellerMsg").html('');
});

$('#msp_CreateSite').on('hidden.bs.modal', function() {
    $("#msp_CreateSite input[type=text]").val('');
    $("#msp_CreateSite .form-group").addClass('is-empty');
    $("#msp_CreateSite .error").html("*");
    $("#required_Sitename").html('');
});

