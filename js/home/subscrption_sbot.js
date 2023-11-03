// Service Bot related JS functionalities.
$(function(){
    $('.consumer').hide();
    $('.commercial').hide();
});

$('#addCustSKUSVar').change(function(){
    $('.consumer').hide();
    $('.commercial').hide();
    
    $('#successProvisionButtonsVal, #successCommercial').hide();
    
    $("#addCustCustomerNumberSub").val('');
    $("#addCustOrderNumberSub").val('');
    $("#addCustFirstNameSub").val('');
    $("#addCustLastNameSub").val('');
    $("#addCustEmailIdSub").val('');
    $("#addCustPhoneNoSub").val('');
    $("#addCustOrderDateSub").val('');
    
    $('#addNewProvisionSubmit').css('pointer-events', 'initial').parent().css('cursor', 'pointer');
    
    var skuDetail = $(this).val();
    var skuData = skuDetail.split('####');

    var skuId = skuData[0];
    var skuType = skuData[1];
    var skuSubType = skuData[2];
    var relatedSku = skuData[3];
     
    $('#skuData').val(skuId + '####' + skuType);
    $('.ComSubType').html('Subscription Type : ' + skuSubType);
    $('#skuTypeVal').html('Customer Sku Type :' + skuType);
     
    if(skuType == 'Consumer' && skuSubType== 'Device') {
        $('.consumer').show();
        $('.commercial').hide();
        $('#pts_CreateProvision .error').html('*');
    } else {
     
        $('.consumer').hide();
        $('.commercial').show();
     

     $.ajax({
             url: "../lib/l-ptsAjax.php",
             type: "POST",
            data: "function=getCustomerDetails" + '&csrfMagicToken=' + csrfMagicToken,
             success: function(data) {
                // console.log('Option : ' + data);
                 $('#tenantCustomer').html(data);
                 $(".selectpicker").selectpicker("refresh");
             },
             error : function(err) {
                 //console.log('Error : ' + err);
             }
         });
    }
});