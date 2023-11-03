var dashboardAPIURL = "../lib/l-dashboardAPI.php?url=";

$(document).ready(function () {

    get_Tenantlist();
});


function get_Tenantlist() {
    $('#entity_Grid').dataTable().fnDestroy();
    entityGrid = $('#entity_Grid').DataTable({
//        scrollY: jQuery('#entity_Grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        searching: true,
        processing: true,
        serverSide: false,
        bAutoWidth: true,
        stateSave: true,
        scrollY: 'calc(100vh - 240px)',
        "pagingType": "full_numbers",
        ajax: {
            url: "../lib/l-msp.php?function=MSP_GetTenantGrid&csrfMagicToken=" + csrfMagicToken,
            type: "POST"
        },
        "language": {
            search: "_INPUT_",
            searchPlaceholder: "Search records",
        },
        "dom": '<"top"f>rt<"bottom"lp><"clear">',
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        columns: [
            {"data": "firstName"},
            {"data": "lastName"},
            {"data": "email"},
            {"data": "company"},
            {"data": "actStatus"}
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
        initComplete: function (settings, json) {
            $('#tenant_searchbox input').addClass('form-control-sm');
        }
//        drawCallback: function(settings) {
//                $(".dataTables_scrollBody").mCustomScrollbar({
//                    theme: "minimal-dark"
//                });
//        }
    });
    $('.dataTables_filter input').addClass('form-control');
    $("#tenant_searchbox").keyup(function () {//search code        
        entityGrid.search(this.value).draw();
    });

    $('#entity_Grid').DataTable().search('').columns().search('').draw();

    $('#entity_Grid tbody').on('click', 'tr', function () {
        entityGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var id = entityGrid.row(this).id();
//        enableOptions(id);
    });

//    $('#entity_Grid tbody').on('dblclick', 'tr', function() {
//        
//        rightContainerSlideOn('add-tenant');
//    });

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
    $('#entity_regno').val("");
    $('#entity_refno').val("");
    $('#entity_email').val("");
    $('#entity_address').val("");
    $('#entity_country').val("");
    $('#entity_city').val("");
    $('#entity_postal').val("");
    $('#entity_phoneno').val("");
    $('#entity_province').val("");
    $('#entity_website').val("");
    $('#entity_logo').val("");
    $("#error_add_entity").text("");
    $('#entity_website').val("");
    $('#entity_skulist').val("");
    $('#nameOfFile_uplogo').text("");

    $('.entitydetails').each(function () {
        var field_id = this.id;
        $("#required_" + field_id).css("color", "red").html("*");
    });

    commonAjaxCall(dashboardAPIURL + "servicetemplate&method=GET", "", "").then(function (res) {
        var statusObj = JSON.parse(res);
        // alert(JSON.stringify(statusObj));  
        var data = statusObj.result;
        var divhtml = "";
        // alert("data"+JSON.stringify(data));  
        if (statusObj.status == "success") {

            for (var k in data) {
                var rObj = data[k];
                var dropdown = "<option value='" + rObj.id + "'>" + rObj.name + "</option>";
                // alert(dropdown);
                //if(rObj.id=="18" || rObj.id=="19"){
                $("#entity_skulist").append(dropdown);
                //}               

            }

            $("#entity_skulist").selectpicker("refresh");
            $("#addentity_popup").modal('show');
        }
    });
}


function openActivateEntityModal() {
    $('#error_add_entity').html('');
    $("#activateEntity_popup").modal('show');

}



function create_entity() {
    var errorVal = 0;
    errorVal = validateCreateEntityForm();
    $("#error_add_entity").text("");
    //alert(errorVal);

    var en_data = {};
    if (errorVal === 0) {

        en_data['first_name'] = $('#entity_fn').val();
        en_data['last_name'] = $('#entity_ln').val();
        en_data['company_name'] = $('#entity_companyname').val();
//        en_data['reg_number']=$('#entity_regno').val();
//        en_data['reference_number']= $('#entity_refno').val();        
        en_data['email'] = $('#entity_email').val();
        en_data['address'] = $('#entity_address').val();
        en_data['country'] = $('#entity_country').val();
        en_data['city'] = $('#entity_city').val();
        en_data['zip_code'] = $('#entity_postal').val();
        en_data['phone_number'] = $('#entity_phoneno').val();
        en_data['province'] = $('#entity_province').val();
//        en_data['website']=$('#entity_website').val();
        en_data['logo'] = $('#entity_logo').val();
        en_data['sku_list'] = $('#entity_skulist').val().join();
        //  alert(JSON.stringify(en_data)); 
        $(".loader").show();

        var postobj = {
            data: en_data
        };

        commonAjaxCall(dashboardAPIURL + "entity/create&method=POST", JSON.stringify(postobj), "").then(function (res) {

            var statusObj = JSON.parse(res);

            $('.loader').hide();
            if (statusObj.status == "success") {
//                $("#addentity_popup").modal('hide');

                get_Entitylist();
                rightContainerSlideClose('add-tenant');
            } else {
                $("#error_add_entity").text("Error:" + JSON.stringify(statusObj.error.code) + " - " + JSON.stringify(statusObj.error.message));
            }

        });
    }
}

/*function activate_tenant(){
 var errorVal = 0;
 errorVal = validateActivateEntityForm();      
 $("#error_add_entity").text("");
 //alert(errorVal);
 
 var en_data ={};
 if (errorVal === 0) {   
 
 en_data['first_name']= $('#entity_fn').val();
 en_data['last_name']=$('#entity_ln').val();
 en_data['company_name']= $('#entity_companyname').val();
 en_data['reg_number']=$('#entity_regno').val();
 en_data['reference_number']= $('#entity_refno').val();        
 en_data['email']=$('#entity_email').val();
 en_data['address']=$('#entity_address').val();
 en_data['country']=$('#entity_country').val();
 en_data['city']=$('#entity_city').val();
 en_data['zip_code']=$('#entity_postal').val();
 en_data['phone_number']=$('#entity_phoneno').val();   
 en_data['province']= $('#entity_province').val();
 en_data['website']=$('#entity_website').val();
 en_data['logo']=$('#entity_logo').val();
 en_data['sku_list']=$('#entity_skulist').val().join();
 //  alert(JSON.stringify(en_data)); 
 $(".loader").show();
 
 var postobj={
 data:en_data
 };
 
 commonAjaxCall(dashboardAPIURL+"entity/create&method=POST",JSON.stringify(postobj), "").then(function(res) {          
 
 var statusObj=JSON.parse(res);
 
 $('.loader').hide();
 if(statusObj.status=="success"){
 $("#addentity_popup").modal('hide');
 get_Entitylist();                                   
 } else {             
 $("#error_add_entity").text("Error:"+JSON.stringify(statusObj.error.code)+" - "+JSON.stringify(statusObj.error.message));                     
 }
 
 });
 }
 }*/

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
        //  alert(field_id+"----"+field_value);
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
                if (!validate_Alphanumeric(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
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


function activate_tenant() {

    var eid = $('#entity_id').val();
    var licKey = $('#entity_actvkey').val();

    var entity_tenant = $('#entity_tenant').val();
    $('.err_entity_id, .err_entity_actvkey').html('');

    if (entity_tenant === '' && licKey === '') {
        $('.err_entity_id, .err_entity_actvkey').html('* required');
        return;
    } else if (entity_tenant === '') {
        $('.err_entity_id').html('* required');
        return;
    } else if (licKey === '') {
        $('.err_entity_actvkey').html('* required');
        return;
    } else {
        $('.err_entity_id, .err_entity_actvkey').html('');
    }

    $('#tenantActiveBtn').css({'pointer-events': 'none'}).parent().css({'cursor': 'not-allowed'});
    $(".loader").show();

    $.ajax({
        url: "../lib/l-ptsAjax.php",
        data: "function=activae_tenant&eid=" + eid + "&licKey=" + licKey + "&csrfMagicToken=" + csrfMagicToken,
        dataType: "text",
        success: function (getField) {
            $(".loader").hide();
            //$('#error_add_entity').html(getField).css({'color': 'green'});
            $.notify(getField);
            setTimeout(function () {
                $('.closebtn').click();
                get_Tenantlist();
                $('#entity_actvkey').val('');
                $('#tenantActiveBtn').css({'pointer-events': 'initial'}).parent().css({'cursor': 'pointer'});
            }, 2000);
        }
    });

}



