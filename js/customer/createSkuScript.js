// Scroller Function
    $('.scroller').each(function() {
        var height;
        if ($(this).attr("data-height")) {
            height = $(this).attr("data-height");
        } else {
            height = $(this).css('height');
        }
        $(this).slimScroll({
            size: '7px',
            height: height,
            alwaysVisible: ($(this).attr("data-always-visible") == "1" ? true : false),
            railVisible: ($(this).attr("data-rail-visible") == "1" ? true : false),
            disableFadeOut: true
        });
    });
    
    function validateSkuNumber(str){
        regExp = /^[0-9]+$/;
        if(!regExp.test(str)){
            return false;
        }else{
            return true;
        }
    }
    
    
    $("#createSKU").on("click", function(e) {
        
        var isReqFieldsFilled = true;
        var idArray = [];
            $('.req1').each(function () {
            var req_id=this.id;
            var field_id=req_id.replace("err_","");
            var field_value=$("#"+field_id).val();
            if($.trim(field_value)===""){
                isReqFieldsFilled=false;
                $("#err_"+field_id).css("color","red").html(" required"); 
                return false;
            }else if(field_id == "conPeriod"){
                if(validateSkuNumber(field_value) == false){
                    isReqFieldsFilled=false;
                    $("#err_"+field_id).css("color","red").html("invalid period"); 
                    return false;
                }else if(field_value < 2){
                    isReqFieldsFilled=false;
                    $("#err_"+field_id).css("color","red").html("contract period should be minimum 2 days"); 
                    return false;
                }else{
                    isReqFieldsFilled=true;
                    $("#err_"+field_id).css("color","red").html(""); 
                    return true;
                }
            }else if(field_id == "pcCount"){
                if(validateSkuNumber(field_value) == false){
                    isReqFieldsFilled=false;
                    $("#err_"+field_id).css("color","red").html("invalid number"); 
                    return false;
                }else if(field_value < 1){
                    isReqFieldsFilled=false;
                    $("#err_"+field_id).css("color","red").html("count should be greater than zero"); 
                    return false;
                }else{
                    isReqFieldsFilled=true;
                    $("#err_"+field_id).css("color","red").html(""); 
                    return true;
                }
            }else if(field_id == "skuPrice"){
                if(validateSkuNumber(field_value) == false){
                    isReqFieldsFilled=false;
                    $("#err_"+field_id).css("color","red").html("invalid price"); 
                    return false;
                }else{
                    isReqFieldsFilled=true;
                    $("#err_"+field_id).css("color","red").html(""); 
                    return true;
                }
            } else if(field_id == "paymentmode"){
                var paymentGateway = $("#paymentgateway").val();
                var paymentmode     = $("#paymentmode").val();
                if(paymentmode === 'Prepaid' && paymentGateway===''){
                    isReqFieldsFilled=false;
                    $("#err_err_paymentgateway").css("color","red").html("Select Payment Gateway"); 
                    return false;
                } else {
                    isReqFieldsFilled=true;
                }
            } else{
                isReqFieldsFilled=true;
            }
        });
        e.preventDefault();
        if(isReqFieldsFilled){
        $("#skuSuccessMsg").html('<img src="../images/ajax-login.gif" class="loadhome" alt="loading..." style="width:auto !important;padding-left:22% !important;"/>');
        $.ajax({
            type: 'POST',
            url: 'addCustomerModel.php',
            data: "function=createEntitySKU&" + "&csrfMagicToken=" + csrfMagicToken + $('#skuform').serialize(),
            dataType: 'json',
            async: false,
            success: function (data) {
                $("#skuSuccessMsg").html(data.msg+' Please refresh the page.');
                getSkuList();
                }
        });
    }
 });
    