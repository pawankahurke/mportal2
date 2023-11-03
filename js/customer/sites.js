/**
 * This file belongs to "Commercial/MSP" Resellers only.
 * This file is created for all provisioning functionality for "Commercial/MSP" flow.
 * In this file "msp" indicates "managed service provider" or "Commercial" bussiness flow.
 */

$(document).ready(function () {
    $('.order-table').DataTable({
//        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: true,
        responsive: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        scrollY: 'calc(100vh - 240px)',
        "pagingType": "full_numbers",
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            search: "_INPUT_",
            searchPlaceholder: "Search records",
        },
        "dom": '<"top"f>rt<"bottom"lp><"clear">',
    });

    get_CommercialSites();
});

/**
 * Fetch all resellers list in json format which is required for Datatable.
 */
function get_CommercialSites() {
    $('#msp_Sites_Grid').dataTable().fnDestroy();
    siteGrid = $('#msp_Sites_Grid').DataTable({
//        scrollY: jQuery('#msp_Sites_Grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        searching: true,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        stateSave: true,
        scrollY: 'calc(100vh - 240px)',
        "pagingType": "full_numbers",
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0]
            },
            {
                className: "centerAlignTd", targets: [1]
            }],
        ajax: {
            // url: "../lib/l-msp.php?function=MSP_GetCustomerSitesGrid&custid=" + sel_CustomerEid,
            url: "../lib/l-msp.php?function=MSP_getSiteGridData&custid=" + sel_CustomerEid,
            type: "POST"
        },
        columns: [
            {"data": "sites"},
            {"data": "installCount"}
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            search: "_INPUT_",
            searchPlaceholder: "Search records",
        },
       "dom": '<"top"f>rt<"bottom"lp><"clear">',
        initComplete: function (settings, json) {
            siteGrid.$('tr:first').click();
        },
        /* drawCallback: function (settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
        } */
    });

    $('#msp_Sites_Grid tbody').on('click', 'tr', function () {
        siteGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var id = siteGrid.row(this).id();
        getDeviceList(id);
    });

}

function getDeviceList(rowId) {

    var sel_customerNum = getCustomerId(rowId, 0);
    var sel_orderNum = getCustomerId(rowId, 1);
    var sel_compId = getCustomerId(rowId, 2);
    var sel_procId = getCustomerId(rowId, 3);


    $('#msp_Device_Grid').dataTable().fnDestroy();
    deviceGrid = $('#msp_Device_Grid').DataTable({
//        scrollY: jQuery('#msp_Device_Grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        searching: true,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        scrollY: 'calc(100vh - 240px)',
        "pagingType": "full_numbers",
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0, 1, 2]
            }],
        ajax: {
            url: "../lib/l-msp.php?function=MSP_GetSitesDeviceGrid&custId=" + sel_compId + "&procId=" + sel_procId + "&custNum=" + sel_customerNum + "&ordNum=" + sel_orderNum,
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
            search: "_INPUT_",
            searchPlaceholder: "Search records",
        },
        "dom": '<"top"f>rt<"bottom"lp><"clear">',
        initComplete: function (settings, json) {

        },
        /* drawCallback: function (settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
        } */
    });

    $('#msp_Device_Grid tbody').on('click', 'tr', function () {
        deviceGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var id = deviceGrid.row(this).id();
        //enableOptions(id);
    });

    $("#customersite_searchbox").keyup(function () {//customer search code        
        deviceGrid.search(this.value).draw();
    });
}
function addSitePopup() {
    $("#site_name").val('');
    $("#act_key").val('');
    $("#required_Sitename").html('');

    var payinfo = $('#payinfo').html();
    if(payinfo == "0") {
        $('.deploy_sitekey_div').show();
    }
}
function customer_link(url) {
    $('#msp_SiteLink').modal('show');
    if (url === "Url is not available") {
        $('#site_successMsg').val(url);
        $('#site_download_url').hide();
        $("#copy_link1").hide();
    } else {
        $("#site_download_url").val(url);
        $('#site_download_url').show();
        $("#copy_link1").show();
        $('#site_successMsg').val("Please click on copy button to copy download url");
    }

}

function getCustomerDownloadURL() {
    //     var rowId =4;// $('#msp_Sites_Grid tbody tr.selected').attr('id');
    //     //var custRowId = rowId.split('---');
    //     var cid = 4;//custRowId[2];
    //     var pid = 4;//custRowId[3];
    //     var custNum = 4507187688;//custRowId[0];
    //     var ordNum = 1005992065;//custRowId[1];

    // //    alert(rowId);
    //     if (rowId !== undefined) {
    //         $.ajax({
    //             url: '../lib/l-msp.php?function=MSP_getSiteDnlURL&custId=' + cid + "&procId=" + pid + "&custNum=" + custNum + "&ordNum=" + ordNum,
    //             type: 'POST',
    //             dataType: 'text',
    //             success: function (response) {
    //                 $('#msp_SiteLink').modal('show');
    //                 $("#site_download_url").val(response);
    //                 $("copy_link1").show();
    //             },
    //             error: function (response) {
    //                 console.log(response);
    //                 console.log("something went wrong in ajax call in  function");
    //             }
    //         });
    //     } else {

    //     }

    var businesstype = $("#getbusinessType").text();
    var cid = $("#getchannelid").text();

    $("#selSite").html("");
    $('#site_download_url').val("");

    getSitelist({value: cid});
}

function getSitelist(ob) {
    //alert(ob.value);

    commonAjaxCall(
            dashboardAPIURL + "sites/by/customer_id/" + ob.value + "&method=GET",
            "",
            ""
            ).then(function (res) {
        var statusObj = JSON.parse(res);
        $(".loader").hide();

        if (statusObj.status == "success") {
            // var data=statusObj.result;
            if (statusObj.result.length > 0) {
                var data = statusObj.result;

                $("#selSite").append("<option value=''>--select--</option>");
                for (var k in data) {
                    var rObj = data[k];
                    var sn = rObj.siteName;
                    var snArr = sn.split("__");
                    //console.log(rObj.eid+"---"+rObj.companyName);
                    $("#selSite").append(
                            "<option value='" + sn + "'>" + snArr[0] + "</option>"
                            );
                }
            } else {
                $("#selSite").append("<option value=''>--Site not found--</option>");
            }
        } else {
            $("#selSite").append("<option value=''>--Site not found--</option>");
        }
        $(".selectpicker").selectpicker("refresh");
        });
}

function getdownloadUrl(thisobj) {
    $("#generateddownloadUrl").val("");
    $("#status_emailsent").html("");
    $(".loader").show();

    var companyid = $("#selCustomer2").val();
    var postdata = {
        comp_id: companyid,
        site_name: thisobj.value
    };
    //alert(JSON.stringify(postdata));
    commonAjaxCall(
            dashboardAPIURL + "download_id/by/site_name/comp_id" + "&method=POST",
            JSON.stringify(postdata),
            ""
            ).then(function (res) {
        var statusObj = JSON.parse(res);
        $(".loader").hide();
        if (statusObj.status == "success") {
            var downloadId = statusObj.result;
            var pathArray = window.location.href.split("/");
            var downloadUrl =
                    pathArray[0] +
                    "//" +
                    pathArray[2] +
                    "/" +
                    pathArray[3] +
                    "/eula.php?id=" +
                    downloadId;
            $("#site_download_url").val(downloadUrl);
    } else {
            $("#status_emailsent")
                    .css("color", "red")
                    .html(
                            "Error : " +
                            JSON.stringify(statusObj.error.code) +
                            " - " +
                            JSON.stringify(statusObj.error.message)
                            );
    }
    });
}


$('#copy_link1').click(function () {
    var urlField = document.querySelector('#site_download_url');
    urlField.select();
    document.execCommand('copy');
});

function create_Site() {
    var sitename = $("#site_name").val();
    var actkey=$("#act_key").val();
    if (sitename == ""&&actkey=='') {
        $("#required_Sitename").html("Please enter all the fields");
    } else if (!validate_alphanumeric_underscore(sitename)) {
        $("#required_Sitename").html("Enter only Alphanumeric values(A-Z-0-9&_).");
    } else if(sitename != ""&&actkey=='')
    {
        $("#required_Sitename").html("Please enter Ativation Key");
    }else if(sitename == ""&&actkey!='')
    {
        $("#required_Sitename").html("Please enter Site Name");
    }else {
        $("#required_Sitename").html("");
        var m_data = new FormData();
        m_data.append('function', 'MSP_Create_Site');
        m_data.append('sitename', sitename);
        m_data.append('csrfMagicToken', csrfMagicToken);
        $.ajax({
            url: '../lib/l-msp.php',
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                if ($.trim(response.status) == "success") {
                    $('#site_successMsg').val(response.msg);
                    $('#msp_CreateSite').modal('hide');
                    $('#msp_SiteLink').modal('show');
                    $("#site_download_url").val(response.link);
                    $("copy_link1").show();
                    get_CommercialSites();
                } else {
                    $("#required_Sitename").html(response.msg);
                }
            },
            error: function (response) {
                $("#required_Sitename").html("Error Occurred");
                console.log('Error In create_Site function : ' + response);
            }
        });
    }
}

function addDeploymntSite(){
    // var cid=$("#getchannelid").text();
    // alert("addDeploymntSite");
    var selected_cid=$('#selCustomer').val();
    
    $(".error").html("");
    $('.loader').show();
    
    var errorVal = 0;
    var field_value = $("#deploy_sitename").val();
    var sitekey_val = $("#deploy_sitekey").val();

    console.log('Test : ' + field_value + '--->' + sitekey_val);

    if($.trim(field_value)=="") {
        $("#required_sitename").css("color", "red").html(" required");
             errorVal++;
    } else if($.trim(sitekey_val)=="") {
        $("#required_sitekey").css("color", "red").html(" required");
             errorVal++;
    }else if ($.trim(field_value)!="" && $.trim(sitekey_val)!="") {
        if (!validate_alphanumeric_underscore(field_value)) {
            $("#required_sitename").css("color", "red").html("Enter only Alphanumeric,Underscore values ");
            errorVal++;
        }else{
            $("#required_sitename").html("*");
           }
    }
    
    if(errorVal===0){
        //generateDownloadURL(selected_cid,field_value);
        attachSiteKey(field_value, sitekey_val);
    }
}

function attachSiteKey(siteName, siteKey) {
    $.ajax({
        url: "../lib/l-ptsAjax.php",
        type: "POST",
        data: "function=attachSiteKey&sitename="+siteName+ "&sitekey="+siteKey + "&csrfMagicToken=" + csrfMagicToken,
        success: function(res) {
            console.log('Msg : ' + JSON.stringify(res));
            var data = JSON.parse(res);
            $('.loader').hide();

            if(data['status'] == 'success') {
                if(data['msg'] == 'DURL') {
                    var durl = data['url'];
                    var key = data['key'];
                    var sid = data['sid'];

                    $('.download_url_div').show();
                    $('#download_url').val(durl + '?key='+ key + '&sid=' + sid).css({'color':'green'});
                } else {
                    $('#required_Sitename').html(data['msg']).css({'color':'green'});
                }
                $('#msp_Sites_Grid').DataTable().ajax.reload();
            } else {
                $('#required_Sitename').html(data['msg']).css({'color':'red'});
            }
        },
        error: function(err) {
            console.log('Error : ' + err);
        }
    });
}

function getCustomerId(selectedId, index) {
    var custRowId = selectedId.split('---');
    var cust_id = custRowId[index];
    return cust_id;
}


$("#exportAllSites").click(function () {
    location.href = '../lib/l-msp.php?function=MSP_ExportAllSites&custid=' + sel_CustomerEid;
});

//###################################### Boostrap Modal CLOSE/OPEN Events Start ################################################//

$('#msp_CreateReseller').on('hidden.bs.modal', function () {
    $("#msp_CreateReseller input[type=text]").not("[readonly]").val('');
    $("#msp_CreateReseller .error").html("*");
    $("#add_ResellerMsg").html('');
});

$('#msp_CreateSite').on('hidden.bs.modal', function () {
    $("#msp_CreateSite input[type=text]").val('');
    $("#msp_CreateSite .form-group").addClass('is-empty');
    $("#msp_CreateSite .error").html("*");
    $("#required_Sitename").html('');
});

