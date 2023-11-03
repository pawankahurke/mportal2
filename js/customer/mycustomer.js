/**
 * This file belongs to "Commercial/MSP" Customers only.
 * This file is created for all provisioning functionality for "Commercial/MSP" flow.
 * In this file "msp" indicates "managed service provider" or "Commercial" bussiness flow.
 */
var dashboardAPIURL = "../lib/l-dashboardAPI.php?url=";

$(document).ready(function () {
    get_CommercialCustomers();
    getUserRoles();
    getSkuListData();
});

function resetFormData() {
    $('#errMsg').html('');
    $('#custcompanyName').html('');
    $('#custfirstName').html('');
    $('#custlastName').html('');
    $('#custemail').html('');
    $('#custcompWeb').html('');
    $('#custphoneNum').html('');
    $('#custcompanyAddr').html('');
    $('#custcompanyCity').html('');
    $('#custcompanyState').html('');
    $('#custcompanyZip').html('');
    $('#custcompanyCountry').html('');
    $('#custroleId').html('');
    $('#RegisterValidation').trigger("reset");
}

/**
 * Fetch all customers list in json format which is required for Datatable.
 */
function get_CommercialCustomers() {
    $('#msp_Customer_Grid').dataTable().fnDestroy();
    customerGrid = $('#msp_Customer_Grid').DataTable({
        scrollY: 'calc(100vh - 240px)',
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        searching: true,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        stateSave: true,
        bInfo: false,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0]
            }],
        ajax: {
            url: "../lib/l-mycustomer.php?function=MSP_GetCustomerGrid",
            type: "POST"
        },
        columns: [
            {"data": "customer"},
            {"data": "firstName"},
            {"data": "lastName"},
            {"data": "email"},
            //{"data": "createdtime"},
            {"data": "status"}
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "_START_-_END_ of _TOTAL_ entries",
            search: "_INPUT_",
            searchPlaceholder: "Search records"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function (settings, json) {
            customerGrid.$('tr:first').click();
        },
        drawCallback: function (settings) {
            /*$(".dataTables_scrollBody").mCustomScrollbar({
             theme: "minimal-dark"
             });*/
        }
    });
    $('.dataTables_filter input').addClass('form-control');
    $('#msp_Customer_Grid tbody').on('click', 'tr', function () {
        customerGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        //var id = customerGrid.row(this).id();
        //enableOptions(id);

    });

    $("#customer_searchbox").keyup(function () {//group search code
        customerGrid.search(this.value).draw();
    });
}

function create_Customer() {
    $('#custcompanyName').html('');
    $('#custfirstName').html('');
    $('#custlastName').html('');
    $('#custemail').html('');
    $('#custcompWeb').html('');
    $('#custphoneNum').html('');
    $('#custcompanyAddr').html('');
    $('#custcompanyCity').html('');
    $('#custcompanyState').html('');
    $('#custcompanyZip').html('');
    $('#custcompanyCountry').html('');
    $('#custroleId').html('');
    $("#errMsg").html('');
    var cust_companyName = $("#cust_companyName").val();
    var cust_firstName = $("#cust_firstName").val();
    var cust_lastName = $("#cust_lastName").val();
    var cust_email = $("#cust_email").val();
    var cust_roleId = $("#cust_roleId").val();
    var cust_compWeb = $("#cust_compWeb").val();
    
    var cust_companyPhone = $("#cust_phoneNum").val();
    var cust_companyAddr = $("#cust_companyAddr").val();
    var cust_companyCity = $("#cust_companyCity").val();
    var cust_companyState = $("#cust_companyState").val();
    var cust_companyCntry = $("#cust_companyCountry").val();
    var cust_companyZip = $("#cust_companyZip").val();

    //var cust_licence = $("#cust_licence").val();

    if (cust_companyName === "") {
        $('#custcompanyName').css("color", "red").html('* Please enter Customer name');
        return false;
    }
    if (!validate_alphanumeric_underscore(cust_companyName)) {
        $('#custcompanyName').css("color", "red").html('* Only Alphanumeric values allowed in Customer name');
        return false;
    }

    if (cust_firstName === "") {
        $('#custfirstName').css("color", "red").html('* Please enter First Name');
        return false;
    }
    if (!validate_Name(cust_firstName)) {
        $('#custfirstName').css("color", "red").html('* Only Alphabet values allowed in First Name');
        return false;
    }
    if (cust_lastName === "") {
        $('#custlastName').css("color", "red").html('* Please enter Last Name');
        return false;
    }
    if (!validate_Name(cust_lastName)) {
        $('#custlastName').css("color", "red").html('* Only Alphabet values allowed in Last Name');
        return false;
    }
    if (cust_email === "") {
        $('#custemail').css("color", "red").html('* Please enter Email Id');
        return false;
    }
    if (!validate_Email(cust_email)) {
        $('#custemail').css("color", "red").html('* Enter valid Email Id');
        return false;
    }
    
    if (cust_roleId === "" || cust_roleId === undefined) {
        $('#custroleId').css("color", "red").html('* Please select the user role');
        return false;
    }
    
    if (cust_compWeb === "") {
        $('#custcompWeb').css("color", "red").html('* Please enter Website');
        return false;
    }

    if (cust_companyPhone === "") {
        $('#custphoneNum').css("color", "red").html('* Please enter Phone number');
        return false;
    }
    if (!validate_Number(cust_companyPhone)) {
        $('#custphoneNum').css("color", "red").html('* Enter valid Phone number');
        return false;
    }

    if (cust_companyAddr === "") {
        $('#custcompanyAddr').css("color", "red").html('* Please enter Address');
        return false;
    }
    if (!validate_Alphanumeric_speciAL(cust_companyAddr)) {
        $('#custcompanyAddr').css("color", "red").html('* Only Alphanumeric values allowed in Address');
        return false;
    }

    if (cust_companyCity === "") {
        $('#custcompanyCity').css("color", "red").html('* Please enter City');
        return false;
    }
    if (!validate_AlphaNumeric(cust_companyCity)) {
        $('#custcompanyCity').css("color", "red").html('* Only Alphabet values allowed in City');
        return false;
    }

    if (cust_companyState === "") {
        $('#custcompanyState').css("color", "red").html('* Please enter State');
        return false;
    }
    if (!validate_AlphaNumeric(cust_companyState)) {
        $('#custcompanyState').css("color", "red").html('* Only Alphabet values allowed in State');
        return false;
    }

    if(cust_companyCntry === "") {
        $('#custcompanyCountry').css("color", "red").html('* Please select the country');
        return false;
    }

    if (cust_companyZip === "") {
        $('#custcompanyZip').css("color", "red").html('* Please enter Zip Code');
        return false;
    }
    if (!validate_ZipCode(cust_companyZip)) {
        $('#custcompanyZip').css("color", "red").html('* Only numeric values allowed in Zip Code');
        return false;
    }

    var m_data = {};

    //Customer's company's details.
    m_data['parent_company_id'] = $('#parent_company_id').val();
    m_data['company_name'] = $('#cust_companyName').val();
    m_data['first_name'] = $('#cust_firstName').val();
    m_data['last_name'] = $('#cust_lastName').val();
    m_data['email'] = $('#cust_email').val();
    m_data['role_id'] = $('#cust_roleId option:selected').val();
    //m_data['sku_list'] = $('#cust_skuList').val().join();


    m_data['website'] = $('#cust_compWeb').val();
    m_data['phone_number'] = $('#cust_phoneNum').val();
    m_data['address'] = $('#cust_companyAddr').val();
    m_data['city'] = $('#cust_companyCity').val();
    m_data['province'] = $('#cust_companyState').val();
    m_data['country'] = $('#cust_companyCountry').val();
    m_data['zip_code'] = $('#cust_companyZip').val();
    
    // Empty Data
    
    m_data['reg_number'] = '';
    m_data['reference_number'] = '';
    m_data['logo'] = '';
    m_data['icon_logo'] = '';

    //Customer's details.
    var postobj = {
        data: m_data
    };

    commonAjaxCall(dashboardAPIURL + "customer/create&method=POST", JSON.stringify(postobj), "").then(function (res) {

        var statusObj = JSON.parse(res);

        $('.loader').hide();
        if (statusObj.status == "success") {
            console.log('Customer has been created successfully : ' + statusObj.result);
            $.notify('Customer ' + m_data['company_name'] + ' has been created successfully');
            rightContainerSlideClose('add-customer');
            //$("#errMsg").html('Customer ' + m_data['company_name'] + ' has been created successfully').css({'color': 'green'});
            get_CommercialCustomers();
            setTimeout(function() {
                $('.closebtn').click();
            }, 2000);
        } else {
            console.log("Error:" + JSON.stringify(statusObj.error.code) + " - " + JSON.stringify(statusObj.error.message));
            //$("#errMsg").text("Error: " + JSON.stringify(statusObj.error.message));
            $.notify("Error: " + JSON.stringify(statusObj.error.message));
        }
    });
}

function validateAddCustomerForm() {
    $(".error").html(" *");
    var errorVal = 0;

    $('.addCustRequired').each(function () {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();

        if ($.trim(field_value) === "") {
            $("#required_" + field_id).css("color", "red").html(" required");
            errorVal++;
        } else if (field_id == "cust_companyName") {
            if (!validate_alphanumeric_underscore(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
            }

        } else if (field_id == "cust_firstName") {
            if (!validate_Name(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Enter only Alphabet values");
            }

        } else if (field_id == "cust_lastName") {
            if (!validate_Name(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Enter only Alphabet values");
            }

        } else if (field_id == "cust_email") {
            if (!validate_Email(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html(" Enter valid email");
            }
        } else if (field_id == "pcCnt") {
            if (field_value < 0) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html(" Enter valid no of pc");
            }
        } else if (field_id == "cust_companyZip") {
            if (!validate_ZipCode(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Enter only numeric values");
            }
        } else if ((field_id == "cust_licence") && ((field_value != '') || (!empty(field_value)))) {
            if (!validate_ZipCode(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Enter only numeric values");
            } else if ((field_value < 1) || (field_value === 0)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Enter Minimum Licence Count");

            } else if ((field_value > 1000)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Maximum Of 1000 Licences Allowed,Please Enter Other Licence Count Value");
            }
            //}
        }
    });
    return errorVal;
}

function getUserRoles() {
    $.ajax({
        type: "POST",
        url: "../lib/l-custAjax.php",
        // data: "function=CUSAJX_GetAllUserRoles&csrfMagicToken=" + csrfMagicToken,
        data: {'function': 'CUSAJX_GetAllUser_Roles', 'csrfMagicToken': csrfMagicToken},

        success: function (response) {
            $("#cust_roleId").html(response);
            $(".selectpicker").selectpicker("refresh");
        }
    });
}

function getSkuListData() {
    $.ajax({
        type: "GET",
        url: "../lib/l-dashboardAPI.php?url=servicetemplate&method=GET",
        data: {'csrfMagicToken': csrfMagicToken},
        success: function (response) {
            var data = JSON.parse(response);
            var res = data.result;

            if (data.status == "success") {
                for (var k in res) {
                    var rObj = res[k];
                    var dropdown = "<option value='" + rObj.id + "'>" + rObj.name + "</option>";
                    $("#cust_skuList").append(dropdown);
                }
                $("#cust_skuList").selectpicker("refresh");
            }
        }
    });
}
