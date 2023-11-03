var dashboardAPIURL = "../lib/l-dashboardAPI.php?url=";

$(document).ready(function () {
    get_Entitylist();
});


function get_Entitylist() {
    $('#entity_Grid').dataTable().fnDestroy();
    entityGrid = $('#entity_Grid').DataTable({
        //scrollY: jQuery('#entity_Grid').data('height'),
        scrollY: 'calc(100vh - 240px)',
        scrollCollapse: true,
        autoWidth: false,
        searching: true,
        processing: true,
        serverSide: false,
        bAutoWidth: true,
        stateSave: true,
        ajax: {
            url: "../lib/l-msp.php?function=MSP_GetEntityGrid&csrfMagicToken=" + csrfMagicToken,
            type: "POST"
        },
        "language": {
            //"info": "_START_-_END_ of _TOTAL_ entries",
            search: "_INPUT_",
            searchPlaceholder: "Search records"
        },
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        columns: [
            {"data": "firstName"},
            {"data": "lastName"},
            {"data": "email"},
            {"data": "company"}
        ],
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false
            }, {
                className: "ignore", targets: [0, 1, 2, 3]
            }],
        ordering: true,
        select: false,
        bInfo: false,
        responsive: true,
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function (settings, json) {

        }
        /*drawCallback: function(settings) {
         $(".dataTables_scrollBody").mCustomScrollbar({
         theme: "minimal-dark"
         });
         }*/
    });
    $('.dataTables_filter input').addClass('form-control');

    $("#tenant_searchbox").keyup(function () {//search code        
        entityGrid.search(this.value).draw();
    });

    $('#entity_Grid').DataTable().search('').columns().search('').draw();

    $('#entity_Grid tbody').on('click', 'tr', function () {
        entityGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        selid = entityGrid.row(this).id();
        //enableOptions(selid);
    });

    $('#entity_Grid tbody').on('dblclick', 'tr', function () {
        rightContainerSlideOn('edit-tenant');
        openEditEntityModal();
    });
}
$("#entity_logo").on("change", function () {

    var file = this.files[0],
            fileName = file.name,
            fileSize = file.size;
    // alert(fileName);

    $('#nameOfFile_uplogo').text(fileName).show();

});

function openAddEntityModal() {
    $('#entity_fn').val("");
    $('#entity_ln').val("");
    $('#entity_companyname').val("");
    //$('#entity_regno').val("");
    //$('#entity_refno').val("");        
    $('#entity_email').val("");
    $('#entity_address').val("");
    $('#entity_country').val("");
    $('#entity_city').val("");
    $('#entity_postal').val("");
    $('#entity_phoneno').val("");
    $('#entity_province').val("");
    //$('#entity_website').val("");
    $('#entity_logo').val("");
    $("#error_add_entity").text("");
    $('#entity_website').val("");
    $('#entity_skulist').val("");
    $('#nameOfFile_uplogo').text("");

    $('#entity_country').prop('selectedIndex', 0);

    $('.entitydetails').each(function () {
        var field_id = this.id;
        $("#required_" + field_id).css("color", "red").html("*");
    });

    commonAjaxCall(dashboardAPIURL + "servicetemplate&method=GET", "", "").then(function (res) {
        var statusObj = JSON.parse(res);
        var data = statusObj.result;
        if (statusObj.status === "success") {
            for (var k in data) {
                var rObj = data[k];
                var dropdown = "<option value='" + rObj.id + "'>" + rObj.name + "</option>";
                $("#entity_skulist").append(dropdown);
            }
            $("#entity_skulist").selectpicker("refresh");
            $("#addentity_popup").modal('show');
        }
    });
}

function openActivateEntityModal() {
    $("#activateEntity_popup").modal('show');
}

function openEditEntityModal() {
    $('#EditTenantInformationForm').trigger("reset");
    //$('#edit_entity_subscrpType').prop('selectedIndex', 0);
    //$('#edit_entity_subscrpType').selectpicker('refresh');
    $('#edit-tenant .error').html('');
    $("#edit_entity_skulist").html('');
    if (selid !== '') {
        commonAjaxCall(dashboardAPIURL + "tenant/details/" + selid + "&method=GET", "", "").then(function (res) {

            var statusObj = JSON.parse(res);
            if (statusObj.status === "success") {
                var tenantData = statusObj.result;
                var companyName = tenantData.companyName.split('_')[0];
                $('#edit_entity_companyname').val(companyName).prop('disabled', 'true');
                $('#edit_entity_fn').val(tenantData.firstName);
                $('#edit_entity_ln').val(tenantData.lastName);
                $('#edit_entity_email').val(tenantData.emailId).prop('disabled', 'true');
                if (tenantData.signupType != '') {
                    $('#edit_entity_subscrpType option[value="' + tenantData.signupType + '"]').attr('selected', true);
                    $('#edit_entity_subscrpType').selectpicker('refresh');
                } else {
                    $('#edit_entity_subscrpType').prop('selectedIndex', 0);
                }
                $('#edit_entity_phoneno').val(tenantData.phoneNo);
                $('#edit_entity_address').val(tenantData.address);
                $('#edit_entity_city').val(tenantData.city);
                $('#edit_entity_province').val(tenantData.province);
                $('#edit_entity_postal').val(tenantData.zipCode);

                var skuList = tenantData.skulist.split(',');
                commonAjaxCall(dashboardAPIURL + "servicetemplate&method=GET", "", "").then(function (res) {
                    var statusObj = JSON.parse(res);
                    var data = statusObj.result;
                    if (statusObj.status === "success") {
                        for (var k in data) {
                            var rObj = data[k];
                            var dropdown = "<option value='" + rObj.id + "'>" + rObj.name + "</option>";
                            $("#edit_entity_skulist").append(dropdown);
                        }
                        $(skuList).each(function (index) {
                            $('#edit_entity_skulist option[value="' + skuList[index] + '"]').attr('selected', true);
                        });
                        $("#edit_entity_skulist").selectpicker("refresh");
                    }
                });
                showCountryList('editTenant', tenantData.country);

            } else {
                $('#error_edit_entity').html('Error fetching Tenant Information');
            }

        });
    } else {
        console.log('Please select a tenant to Edit');
    }
}

function showCountryList(type, value) {
    $.ajax({
        url: "../lib/l-countrylist.php&csrfMagicToken=" + csrfMagicToken,
        type: 'GET',
        success: function (data) {
            if (type === 'editTenant') {
                $('#edit_entity_country').html(data);
                $('#edit_entity_country option[value="' + value + '"]').attr('selected', true);
            }
        },
        error: function (error) {
            console.log('Error getting Country List : ' + error);
        }
    });
}

function create_entity() {
    var errorVal = 0;
    errorVal = validateCreateEntityForm();
    $("#error_add_entity").text("");

    if ($('#entity_skulist').val().join() === '') {
        $('#required_entity_skulist').html('required');
        errorVal++;
    }
    if ($('#entity_subscrpType').val() === '') {
        $('#required_entity_subscrpType').html('required');
        errorVal++;
    }
    if ($('#entity_country').val() === '') {
        $('#required_entity_country').html('required');
        errorVal++;
    }

    var en_data = {};
    if (errorVal === 0) {

        en_data['first_name'] = $('#entity_fn').val();
        en_data['last_name'] = $('#entity_ln').val();
        en_data['company_name'] = $('#entity_companyname').val();
        en_data['email'] = $('#entity_email').val();
        en_data['address'] = $('#entity_address').val();
        en_data['country'] = $('#entity_country').val();
        en_data['city'] = $('#entity_city').val();
        en_data['zip_code'] = $('#entity_postal').val();
        en_data['phone_number'] = $('#entity_phoneno').val();
        en_data['province'] = $('#entity_province').val();
        en_data['logo'] = $('#entity_logo').val();
        en_data['sku_list'] = $('#entity_skulist').val().join();
        en_data['subscrptype'] = $('#entity_subscrpType').val();
        en_data['url'] = bburl;
        $(".loader").show();

        var postobj = {
            data: en_data
        };

        commonAjaxCall(dashboardAPIURL + "entity/create&method=POST", JSON.stringify(postobj), "").then(function (res) {

            var statusObj = JSON.parse(res);
            $('.loader').hide();
            if (statusObj.status == "success") {
                $("#addentity_popup").modal('hide');
                $("#add-tenant").hide();
                $.notify("Tenent was successfully added");
                get_Entitylist();
                closePopUp();
                setTimeout(function () {
                    location.reload();
                }, 2500);
            } else {
                $.notify('There is already a Tenant with the same name');
                //$("#error_add_entity").text("Error:" + JSON.stringify(statusObj.error.code) + " - " + JSON.stringify(statusObj.error.message));
                rightContainerSlideClose('add-tenant');
            }

        });
    }
}

function update_entity() {
    console.log('Inside update_entity Function');
    var errorVal = 0;
    errorVal = validateEditEntityForm();
    $("#error_edit_entity").text("");

    if ($('#edit_entity_skulist').val().join() === '') {
        $('#required_edit_entity_skulist').html('required');
        errorVal++;
    }
    /*if ($('#edit_entity_subscrpType').val() === '') {
     $('#required_edit_entity_subscrpType').html('required');
     errorVal++;
     }*/
    if ($('#edit_entity_country').val() === '') {
        $('.required_edit_entity_country').html('required');
        errorVal++;
    }

    var en_data = {};
    if (errorVal === 0) {
        en_data['channel_id'] = selid;
        en_data['first_name'] = $('#edit_entity_fn').val();
        en_data['last_name'] = $('#edit_entity_ln').val();
        en_data['sku_list'] = $('#edit_entity_skulist').val().join();
        en_data['subscrptype'] = $('#edit_entity_subscrpType').val();
        en_data['phone_number'] = $('#edit_entity_phoneno').val();
        en_data['address'] = $('#edit_entity_address').val();
        en_data['city'] = $('#edit_entity_city').val();
        en_data['state'] = $('#edit_entity_province').val();
        en_data['zip_code'] = $('#edit_entity_postal').val();
        en_data['country'] = $('#edit_entity_country').val();

        var postobj = {
            data: en_data
        };
        commonAjaxCall(dashboardAPIURL + "channel/chnl_list/save&method=PATCH", JSON.stringify(postobj), "").then(function (res) {
            var statusObj = JSON.parse(res);
            console.log(statusObj);
            $('.loader').hide();
            if (statusObj.status == "success") {
                //$("#addentity_popup").modal('hide');
                //$("#edit-tenant").hide();
                $('.closebtn').click();
                $.notify("Tenant information has been updated successfully");
                get_Entitylist();
                closePopUp();
                setTimeout(function () {
                    location.reload();
                }, 2500);
            } else {
                console.log("error");
                $("#error_edit_entity").text("Error:" + JSON.stringify(statusObj.error.code) + " - " + JSON.stringify(statusObj.error.message));
            }

        });

    }
}

function validateActivateEntityForm() {
    $(".error").html("");
    var errorVal = 0;

    $('.entitydetails').each(function () {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();
        //  alert(field_id+"----"+field_value);
        if (field_id != "") {
            if ($.trim(field_value) === "") {
                $("#required_" + field_id).css("color", "red").html(" required");
                errorVal++;
            } else if (field_id == "entity_tenant") {
                if (!validate_Alphanumeric(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                    errorVal++;
                }

            } else if (field_id == "entity_actvkey") {
                if (!validate_Alphanumeric(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                    errorVal++;
                }

            }
        }
    });

    return errorVal;
}

function validateCreateEntityForm() {
    $(".error").html("");
    var errorVal = 0;

    $('.entitydetails').each(function () {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();
        if (field_id != "") {
            if ($.trim(field_value) === "") {
                $("#required_" + field_id).css("color", "red").html(" required");
                errorVal++;
            } else if (field_id == "entity_fn") {
                if (!validate_Alphanumeric(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                    errorVal++;
                }

            } else if (field_id == "entity_ln") {
                if (!validate_Alphanumeric(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                    errorVal++;
                }

            } else if (field_id == "entity_email") {
                if (!validate_Email(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter valid email");
                    errorVal++;
                }
            } else if (field_id == "entity_postal") {
                if (!validate_ZipCode(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter valid postal code");
                    errorVal++;
                }
            } else if (field_id == "entity_address") {
                if (!validate_Alphanumeric_speciAL(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter valid address");
                    errorVal++;
                }
            } else if (field_id == "entity_country") {
                if ($.trim(field_value) === "--Select your Country--") {
                    $("#required_" + field_id).css("color", "red").html(" required");
                    errorVal++;
                }
            }
        }
    });

    return errorVal;
}

// form validation for edit tenant module
function validateEditEntityForm() {
    $(".error").html("");
    var errorVal = 0;

    $('.edit_entitydetails').each(function () {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();
        if (field_id != "") {
            if ($.trim(field_value) === "") {
                $(".required_" + field_id).css("color", "red").html(" required");
                errorVal++;
            } else if (field_id == "edit_entity_fn") {
                if (!validate_Alphanumeric(field_value)) {
                    $(".required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                    errorVal++;
                }
            } else if (field_id == "edit_entity_ln") {
                if (!validate_Alphanumeric(field_value)) {
                    $(".required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                    errorVal++;
                }
            } else if (field_id == "edit_entity_phoneno") {
                if (!validate_Number(field_value)) {
                    $(".required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                    errorVal++;
                }
            } else if (field_id == "edit_entity_address") {
                if (!validate_Alphanumeric(field_value)) {
                    $(".required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                    errorVal++;
                }
            } else if (field_id == "edit_entity_city") {
                if (field_value === '') {
                    $(".required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                    errorVal++;
                }
            } else if (field_id == "edit_entity_province") {
                if (field_value === '') {
                    $(".required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                    errorVal++;
                }
            } else if (field_id == "edit_entity_postal") {
                if (!validate_ZipCode(field_value)) {
                    $(".required_" + field_id).css("color", "red").html("Enter valid postal code");
                    errorVal++;
                }
            }
        }
    });

    return errorVal;
}

function activate_tenant() {
    var eid = $('#entity_id').val();
    var licKey = $('#entity_actvkey').val();

    $(".loader").show();
    $.ajax({
        url: "../lib/l-ptsAjax.php",
        data: "function=activae_tenant&eid=" + eid + "&licKey=" + licKey + "&csrfMagicToken=" + csrfMagicToken,
        dataType: "text",
        success: function (getField) {
            $(".loader").hide();
            $('#error_add_entity').html(getField);
        }
    });

}

