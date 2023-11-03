function check_empty(fieldvalue, default_value){			
    if((fieldvalue == "")||(fieldvalue == default_value)){
            return true;
    } else{
            return false;	
    }
}

function is_number(fieldvalue){
    if(isNaN(fieldvalue)){
        return false;
    } else{
        return true;	
    }
}

function alphanumeric(fieldvalue) {   
    var letters = /^[0-9a-zA-Z]+$/;  
    if(fieldvalue.match(letters)){  //all valid characters present
        return true;  
    } else {  
        return false;  
    }  
}
function isAlphaNumeric(objValue,id) {
    var charpos = objValue.search("[^A-Za-z???????0?0-9]"); 
    if(objValue.length > 0 &&  charpos >= 0) { 
       return false; 
    }	
    return true;
}

function check_entitlement(check_param) {
    
    $('.loadingStage').show();
    $('#show_details').html('');
    
    $('#rvkRegnDiv').hide();
    $('#rvkRegnMessage').hide();
      
    $('#show_details').html('');
    var txt_val     = '';
    var search_type = '';
    
    if(check_param === 'ent_chk_customerNumber'){ 
        txt_val     = document.getElementById('ent_textfield').value;
        search_type = 'customerNum';                                        
    }
    if(check_param === 'ent_chk_orderNumber'){ 
        txt_val     = document.getElementById('ent_textfield').value;
        search_type = 'orderNum';                                    
    }
    if(check_param === 'ent_chk_servicetag'){ 
        txt_val     = document.getElementById('ent_textfield').value;
        search_type = 'serviceTag';                                     
    } 
		
    if(check_param === 'ent_chk_emailid'){ 
        txt_val     = document.getElementById('ent_textfield').value;
        search_type = 'emailId';                                     
    }
    $.ajax({
        type: "POST",
        url: "../lib/l-ptsAjax.php?function=PTSAJAX_getEntitlement",
        data: {id:txt_val,type:search_type,csrfMagicToken: csrfMagicToken},
        success: function(msg){
            $('#show_details').html('');
            $('#ent_show_labels_heading').html('');
            $('#ent_show_cust_no').html('');
            $('.loadingStage').hide();                                                
            $('#show_details').show();
            
             var first_cust_num = '';
		
            //Spliting data from 'customerOrder'
            var rows_customer_order = msg.split("%%%");

            cust_num_list = rows_customer_order[3];

            if(rows_customer_order[2] == 0){ 
                $('#show_details').append(cust_num_list);
            } else {
                $('#show_details').append(cust_num_list);
         
                if(rows_customer_order[2] == 1){
                   first_cust_num =  $("#ent_show_cust_no").html();
                   global_cust_no = first_cust_num;
                   customer_num_select(first_cust_num,txt_val, search_type);
                } else {
                   var e = document.getElementById("ent_show_cust_no");
                   first_cust_num = e.options[0].value;      
                   global_cust_no = first_cust_num;
                   customer_num_select(first_cust_num,txt_val, search_type);
                }     
            } 
        }
    });
}

function customer_num_select(cn,txt_val, search_type){
    $('.loadingStage').show();  
    var cust_num    = cn;
    $.ajax({
        type: "POST",
        url: "../lib/l-ptsAjax.php?function=PTSAJAX_getCustomerDetailsForEntitlement",
        data: {cust_num:cust_num ,searchType:search_type,txtVal:txt_val,csrfMagicToken: csrfMagicToken},
        success: function(msg){
            render_cust_num_details(msg ,cust_num);
            //Setting selected customer num in dropdown
            $("select option[value='"+cust_num+"']").attr("selected","selected");
        }
    /* end - success */
    });  
}

function render_cust_num_details(msg1,cust_num) {
    
    $('#show_details').show();
    $('#show_details').html('');

    //Spliting data from 'customerOrder'
    var rows_customer_order = msg1.split("%%%order_number_row%%%");
    var total_orders = rows_customer_order.length - 1;
    var total_orders_text = '';
    if (total_orders == 1) {
        total_orders_text = total_orders + ' order found.';
    } else {
        total_orders_text = total_orders + ' orders found.';
    }

    var remoteCallChk = 0;

    if (rows_customer_order[1] != 'NOT_FOUND') {

        $('#show_details').append(cust_num_list);
        $('#show_details').append('<label class="ent_show_line1"> ' + total_orders_text + '</label>');
        $('#show_details').append('<div id="ent_show_orders"></div>');
        var tempArray = [];
        /* For each order number for the customer number */
        for (var i = 1; i <= (rows_customer_order.length - 1); i++) {

            var col = (rows_customer_order[i]).split('##');
            var cust_num = col[1];
            var ordr_num = col[2];
            var corspId = '';

            // for service log details
            var viewAlrts   = '';
            var viewAlrtsSts = '';
            var serviceTag  = '';
            var uninsStatus = '';

            /* INSTALLED PCs INFO */
            var pcs_per_order = col[14];
            var rows_pcs    = pcs_per_order.split("%%%service_request_row%%%");
            var content     = '';
            var content2    = '';

            var oldOrder    = (col[12]).split('_');
            var oldOrderVal = oldOrder[0];
            var updVal      = oldOrder[1];

            var insStatus   = col[9].split("_")[0];
            var payStatus   = col[9].split("_")[1];
            var insertedId  = col[9].split("_")[2];
            var ppid        = col[9].split("_")[3];
            var nanoheal_prod = $.trim(col[9].split("_")[4]);
            //console.log("nanoheal_prod = " + nanoheal_prod);
            var subCol      = (col[10]).split('_');
            var provcode    = subCol[3];
            var orderCount  = subCol[4];
            var quantity    = 1 ; //subCol[5];
            var licenseKey  = $.trim(col[9].split("_")[5]);
            var trial       = $.trim(col[9].split("_")[6]);
            
            var activateStatus = "";
            
            $('#ent_show_orders').append('<div class="ent_container_div" id="' + i + '"></div>');
            //console.log("Count is ---> " + orderCount + "\t" + payStatus);
            
            /* Getting all installations under a particular order num */
            for (var m = 1; m <= (rows_pcs.length - 1); m++) {
                var pc_info = (rows_pcs[m]).split('@@');
                if (pc_info[3] === '0' || pc_info[3] === 'empty' || pc_info[3] === '') {
                    viewAlrts = '';
                    viewAlrtsSts = '';
                } else {
                    serviceTag = $.trim(pc_info[3]);
                    uninsStatus = $.trim(pc_info[9]);
                    activateStatus = $.trim(pc_info[10]);
                    break;
                }
            }
            /*var licenseString = "";
            if (licenseKey !== "") {
                licenseString = '<label class="ent_show_line2">License Key : </label><label class="ent_show_line1" id="ent_show_end_date">' + licenseKey + '</label>';
            }*/
            
            var top_content = '<table cellspacing="0">';
            top_content += '<tr valign="top">';
            top_content += '<td><label class="ent_show_line2">Order Number : </label><label class="ent_show_line1" id="ent_show_ordr_no">' + col[2] + '</label></td>';
            top_content += '<td><label class="ent_show_line2">Order Date : </label><label class="ent_show_line1" id="ent_show_ordr_date">' + col[3] + '</label></td>';
            top_content += '<td><label class="ent_show_line2">Service End Date : </label><label class="ent_show_line1" id="ent_show_end_date">' + col[4] + '</label>' 
                           // + licenseString
                        +'</td>';
            
            top_content += '</tr>';

            top_content += '<tr valign="top">';
            top_content += '<td><label class="ent_show_line2">Agent Email Id : </label><label  class="ent_show_line1"  id="ent_show_email_id">' + col[13] + '</label></td>';
            //top_content += '<td><label class="ent_show_line2" id="ent_show_sku_num">SKU No. : </label><label  class="ent_show_line1"  id="ent_show_sku_num">'+col[5]+'</label></td>';
            var displayNameSku = "";
            
            if (col[6].length > 25) {
                displayNameSku = col[6].substring(0 , 24) + "..";
            } else {
                displayNameSku = col[6];
            }
            
            top_content += '<td><label class="ent_show_line2" id="ent_show_sku_num">SKU Desc. : </label><label  class="ent_show_line1"  id="ent_show_sku_desc" title="' + col[6] + '">' + displayNameSku + '</label></td>';
            // if(subCol[1] != 1){
            top_content += '<td><label class="ent_show_line2">Customer Email Id : </label><label  class="ent_show_line1"  id="ent_show_email_id">' + col[8] + '</label></td>';
            // }
            
            if(payStatus === "done" && insStatus !== "Cancelled") {
                //if(uninsStatus !== "UNINS_DONE") {
                    top_content += '<td><label class="ent_show_line2"></label><label  class="ent_show_line1"  id="ent_show_sku_desc"><span class="actionBtn" id="sendEmailDownEnt" style= "margin-left: -2px; !important" onclick="sendDownloadMaiEnt(\'' + col[2] + '\',\'' + cust_num + '\' ,\'download\');" title="Send Download Mail">Resend Mail</span></label></td>';
                //}
            }
            
            top_content += '</tr>';

            /*if(subCol[1] != 1){
             top_content += '<tr valign="top">';
             top_content += '<td><label class="ent_show_line2">Email Id : </label><label  class="ent_show_line1"  id="ent_show_email_id">'+col[8]+'</label></td>'; 
             top_content += '<td></td>';
             top_content += '<td></td>';              
             top_content += '</tr>';
             }  */

            top_content += '<tr>';
            top_content += '<td><label class="ent_show_labels">Quantity : </label><labelclass="ent_show_labels">'+quantity+'</label></td>';
            $("#insertedIdEnt").val(insertedId);
            /*if (payStatus !== "done" && (ppid !== "0" || parseInt(ppid) !== 0) ) {
                top_content += '<td><label class="ent_show_labels">Status : </label><label class="ent_show_labels" id="ent_show_end_date" style="color: red;">Payment Pending</label></td>';
            } else */if (insStatus === 'Expired') {
                top_content += '<td><label class="ent_show_labels">Status : </label><label class="ent_show_labels" id="ent_show_end_date" style="color: red;">' + insStatus + '</label></td>';
            } else if (insStatus === 'Expiring soon') {
                top_content += '<td><label class="ent_show_labels">Status : </label><label class="ent_show_labels" id="ent_show_end_date" style="color: red;">' + insStatus + '</label></td>';
            } else if (insStatus === 'Cancelled') {
                top_content += '<td><label class="ent_show_labels">Status : </label><label class="ent_show_labels" id="ent_show_end_date" style="color: blue;">' + insStatus + '</label></td>';
            } else if (insStatus === 'Renewed') {
                top_content += '<td><label class="ent_show_labels">Status : </label><label class="ent_show_labels" id="ent_show_end_date" style="color: blue;">' + insStatus + '</label></td>';
            } else if (insStatus === 'Active') {
                top_content += '<td><label class="ent_show_labels">Status : </label><label class="ent_show_labels" id="ent_show_end_date" style="color: green;">' + insStatus + '</label></td>';
            }
            
            if (insStatus !== 'Expired' && insStatus !== 'Cancelled') {
                top_content += '<td><label class="ent_show_labels">Machine Status : </label><label class="ent_show_labels" id="ent_machine_status_' + col[2] + '"><img src="../js/entitlement/images/buttons/offline.png" title="Offline"> <b><h3 style="display:inline">Offline</h3></b> </label></td>';
            }
            top_content += '<td colspan="3" id="viewAlrtDv_' + ordr_num + '" align="right">' + viewAlrts + '</td>';

            //top_content += '<td><span id="survey" onclick="openSurvey(\''+col[1]+'\',\''+col[2]+'\')" class="actionBtn">Survey</span></td>';

            // "upgrade_slide_div('+cust_num+','+ordr_num+')"

            corspId = 'new';

            var action = '';
            var btn_id = '';
            if (parseInt(ppid) !== 0) {
                if (payStatus === "done") {
                    if ((insStatus === 'Expired') || (col[11] === 3) || (oldOrderVal === '1')) {  //Contract expired or cancelled or order num is old  
                        if (parseInt(subCol[1]) === 1 || subCol[1] === "1") {      // 1 - show upgrade button
                            action = 'upgrade';
                            btn_id = corspId + "-" + action;
                            if(parseInt(activateStatus) !== 0) {
                                top_content += '<td><span class="actionBtn" id="' + btn_id + '" style= "margin-right: 0%; !important" onclick="entitle_slide_div_upgd_renew(this,\'' + cust_num + '\',\'' + ordr_num + '\')">Upgrade Plan</span></td>';
                            }
                        }
                    } else {

                        if (col[17] === 1 || col[17] === '1') {     // DOWNLOAD STATUS NOT EXE or last order num has revokeStatus R - show regenerate button

                            if (insStatus === 'Expiring soon') {      // <= 45 days - show renew button

                                if (parseInt(subCol[0]) === 1 || subCol[0] === "1") {
                                    action = 'renewnew';
                                    btn_id = corspId + "-" + action;
                                    top_content += '<td><span class="actionBtn" id="' + btn_id + '" style= "margin-left: -50px; !important" onclick="entitle_slide_div_upgd_renew(this,\'' + cust_num + '\',\'' + ordr_num + '\')">Renew</span></td>';
                                }
                            }

                            // prov code shows 02 we need to show upgrade
                            if (parseInt(subCol[1]) === 1 || subCol[1] === "1") {  // 1 - show upgrade button
                                action = 'upgrade';
                                btn_id = corspId + "-" + action;
                                if(parseInt(activateStatus) !== 0) {
                                    top_content += '<td><span class="actionBtn" id="' + btn_id + '" style= "margin-right: 0%; !important" onclick="entitle_slide_div_upgd_renew(this,\'' + cust_num + '\',\'' + ordr_num + '\')">Upgrade Plan</span></td>';
                                }
                            }

                        } else {          // DOWNLOAD STATUS EXE  or last order num has revokeStatus I - show revoke button
                            if (insStatus === 'Expiring soon') {      // <= 45 days - show renew button
                                if (parseInt(subCol[0]) === 1 || subCol[0] === "1") {
                                    action = 'renewnew';
                                    btn_id = corspId + "-" + action;
                                    top_content += '<td><span class="actionBtn" id="' + btn_id + '" style= "margin-left: -50px; !important" onclick="entitle_slide_div_upgd_renew(this,\'' + cust_num + '\',\'' + ordr_num + '\')">Renew</span></td>';
                                }
                            }
                            // prov code shows 02 we need to show upgrade
                            if (parseInt(subCol[1]) === 1 || subCol[1] === "1") {      // 1 - show upgrade button
                                action = 'upgrade';
                                btn_id = corspId + "-" + action;
                                if(parseInt(activateStatus) !== 0) {
                                    top_content += '<td><span class="actionBtn" id="' + btn_id + '" style= "margin-right: 0%; !important" onclick="entitle_slide_div_upgd_renew(this,\'' + cust_num + '\',\'' + ordr_num + '\')">Upgrade Plan</span></td>';
                                }
                            }
                        }
                    }
                } else {
//                    top_content += '<td><span class="actionBtn" id="checkPaymentEntitle" style= "margin-right: 0%; !important" onclick="regenrt_div(\'' + cust_num + '\',\'' + ordr_num + '\',\'' + ppid + '\', \'0\')">Payment Link</span></td>';
                    //                /top_content += '<td><label class="ent_show_labels">Payment Status : </label><label class="ent_show_labels" id="ent_show_end_date" >Not Done</label></td>';
                }
            } else {
                if ((insStatus === 'Expired') || (col[11] === 3) || (oldOrderVal === '1')) {  //Contract expired or cancelled or order num is old  
                    if (parseInt(subCol[1]) === 1 || subCol[1] === "1") {      // 1 - show upgrade button
                        action = 'upgrade';
                        btn_id = corspId + "-" + action;
                        if(parseInt(activateStatus) !== 0) {
                            top_content += '<td><span class="actionBtn" id="' + btn_id + '" style= "margin-right: 0%; !important" onclick="entitle_slide_div_upgd_renew(this,\'' + cust_num + '\',\'' + ordr_num + '\')">Upgrade Plan</span></td>';
                        }
                    }
                } else {

                    if (col[17] === 1 || col[17] === '1') {     // DOWNLOAD STATUS NOT EXE or last order num has revokeStatus R - show regenerate button

                        if (insStatus === 'Expiring soon') {      // <= 45 days - show renew button

                            if (parseInt(subCol[0]) === 1 || subCol[0] === "1") {
                                action = 'renewnew';
                                btn_id = corspId + "-" + action;
                                top_content += '<td><span class="actionBtn" id="' + btn_id + '" style= "margin-left: -50px; !important" onclick="entitle_slide_div_upgd_renew(this,\'' + cust_num + '\',\'' + ordr_num + '\')">Renew</span></td>';
                            }
                        }

                        // prov code shows 02 we need to show upgrade
                        if (parseInt(subCol[1]) === 1 || subCol[1] === "1") {  // 1 - show upgrade button
                            action = 'upgrade';
                            btn_id = corspId + "-" + action;
                            if(parseInt(activateStatus) !== 0) {
                                top_content += '<td><span class="actionBtn" id="' + btn_id + '" style= "margin-right: 0%; !important" onclick="entitle_slide_div_upgd_renew(this,\'' + cust_num + '\',\'' + ordr_num + '\')">Upgrade Plan</span></td>';
                            }
                        }

                    } else {          // DOWNLOAD STATUS EXE  or last order num has revokeStatus I - show revoke button
                        if (insStatus === 'Expiring soon') {      // <= 45 days - show renew button
                            if (parseInt(subCol[0]) === 1 || subCol[0] === "1") {
                                action = 'renewnew';
                                btn_id = corspId + "-" + action;
                                top_content += '<td><span class="actionBtn" id="' + btn_id + '" style= "margin-left: -50px; !important" onclick="entitle_slide_div_upgd_renew(this,\'' + cust_num + '\',\'' + ordr_num + '\')">Renew</span></td>';
                            }
                        }
                        // prov code shows 02 we need to show upgrade
                        if (parseInt(subCol[1]) === 1 || subCol[1] === "1") {      // 1 - show upgrade button
                            action = 'upgrade';
                            btn_id = corspId + "-" + action;
                            if(parseInt(activateStatus) !== 0) {
                                top_content += '<td><span class="actionBtn" id="' + btn_id + '" style= "margin-right: 0%; !important" onclick="entitle_slide_div_upgd_renew(this,\'' + cust_num + '\',\'' + ordr_num + '\')">Upgrade Plan</span></td>';
                            }
                        }
                    }
                }
            }

            top_content += '</tr>';
            top_content += '</table>';
            $('#' + i).append(top_content);

            $('#' + i).append('<div style="height: 10px; clear: both;"></div>');

            /*if(subCol[1] == 1){ 
             $('#'+i).append('<label class="ent_show_labels">Online Backup Account : </label><br>');
             $('#'+i).append('<label class="ent_show_line2" style="background:#D8D8D8">not available for trail users </label><br>');				
             } else {        
             var rebit_result_col    = col[18];  
             var rebit_result_col2   = rebit_result_col.split("%%%user_found%%%");
             
             for(var m=1; m<=(rebit_result_col2.length-1); m++){					
             if((col[9] != 'Renewed')){    
             if(m == 1){
             $('#'+i).append('<label class="ent_show_labels">Online Backup Account : </label><br>');
             }
             var rebit_result_arr = (rebit_result_col2[m]).split('@@');
             $('#'+i).append('<label class="ent_show_line2">Capacity : </label><label class="ent_show_line1" id="ent_show_backup_capacity">'+rebit_result_arr[1]+'</label>');
             $('#'+i).append('<label class="ent_show_line2">Backup End Date : </label><label class="ent_show_line1" id="ent_show_backup_capacity">'+rebit_result_arr[2]+'</label>');
             $('#'+i).append('<label class="ent_show_line2">Backup Email Id : </label><label class="ent_show_line1" id="ent_show_backup_email">'+rebit_result_arr[3]+'</label>');
             if((col[9] == 'Active')){
             $('#'+i).append('<span class="blueBtn" onclick="upgrade_slide_div('+cust_num+','+ordr_num+')">Upgrade</span>'); 
             }
             }
             //$('#'+i).append('<span class="blueBtn" onclick="upgrade_slide_div('+cust_num+','+ordr_num+')">Upgrade</span>'); 
             }
             }*/
            /* end - for loop */

            $('#' + i).append('<div style="height: 10px; clear: both;"></div>');


            //console.log('Servicetag :' + serviceTag);
            /* this is the code to find the machine is online or offline */
            /*if(showPayment){
             top_content += '<td><label class="ent_show_labels"></label><label class="ent_show_labels" id="ent_show_end_date" >Payment</label></td>';
             }*/
            //alert(ppid);
            
            if (parseInt(ppid) !== 0) {
                if (payStatus === "done") {
                    if (serviceTag !== '0' && serviceTag !== 'empty' && serviceTag !== '' && insStatus !== 'Expired' && insStatus !== 'Cancelled') {
                        //if(remoteCallChk == 0){

                        if (uninsStatus === 'UNINS_EMPTY' && uninsStatus !== '' && remoteCallChk === 0) {

                            remoteCallChk = 0;
                            getMachineOnlineorOffile(serviceTag, ordr_num, cust_num, 1,provcode, trial);
                        } else if (uninsStatus === 'UNINS_DONE') {

                            viewAlrtsSts = '<span><img src="../js/entititlement/../js/entitlement/images/buttons/notavail.png" title="In Active"> <b><h3 style="display:inline">Uninstalled<h3></b></span>';
                            /*if(parseInt(nanoheal_prod) !== 0 || nanoheal_prod !== "0") {
                                viewAlrtsSts += '<td><span class="actionBtn" style="margin-right: -8%; font-weight: normal !important; float:right;" id="regn'+i+'" onclick="regenrt_div(\'' + cust_num + '\',\'' + ordr_num + '\',\'' + ppid +'\', \'1\')">Regenerate</span></td>';
                            }*/
                            viewAlrts = '';
                            $('#ent_machine_status_' + ordr_num).html(viewAlrtsSts);
                            //$('#viewAlrtDv_'+splMsg[3]).html(viewAlrts);
                        } else {
                            viewAlrtsSts = '<span><img src="../js/entitlement/images/buttons/notavail.png" title="In Active"> <b><h3 style="display:inline">Not Installed</h3></b> </span></td>';
                            if(parseInt(nanoheal_prod) !== 0 || nanoheal_prod !== "0") {
                                viewAlrtsSts += '<td><span class="actionBtn" style="margin-right: -8%; font-weight: normal !important; float:right;" id="regn'+i+'" onclick="regenrt_div(\'' + cust_num + '\',\'' + ordr_num + '\',\'' + ppid +'\', \'1\')">Regenerate</span></td>';
                            }
                            viewAlrts = '';
                            $('#ent_machine_status_' + ordr_num).html(viewAlrtsSts);
                        }
                    } else if (serviceTag !== '0' && serviceTag !== 'empty' && serviceTag !== '' && (insStatus === 'Expired' || insStatus === 'Cancelled')) {
                        viewAlrtsSts = '';
                        viewAlrts = '';

                        $('#ent_machine_status_' + ordr_num).html(viewAlrtsSts);
                    } else {
                        viewAlrtsSts = '<span><img src="../js/entitlement/images/buttons/notavail.png" title="In Active"> <b><h3 style="display:inline">Not Installed</h3></b></span></td>';
                        if(parseInt(nanoheal_prod) !== 0 || nanoheal_prod !== "0") {
                                viewAlrtsSts += '<td><span class="actionBtn" style="margin-right: -8%; font-weight: normal !important; float:right;" id="regn'+i+'" onclick="regenrt_div(\'' + cust_num + '\',\'' + ordr_num + '\',\'' + ppid +'\', \'1\')">Regenerate</span></td>';
                            }

                        viewAlrts = '';

                        $('#ent_machine_status_' + ordr_num).html(viewAlrtsSts);
                    }
                    $("#regn"+ i).show();
                } else {
                    viewAlrtsSts = '<span><img src="../js/entitlement/images/buttons/notavail.png" title="In Active"> <b><h3 style="display:inline">Not Installed</h3></b></span></td>';
                    $('#ent_machine_status_' + ordr_num).html(viewAlrtsSts);
                    $("#regn"+ i).show();
                }
                
            } else {
                if (serviceTag !== '0' && serviceTag !== 'empty' && serviceTag !== '' && insStatus !== 'Expired' && insStatus !== 'Cancelled') {
                    //if(remoteCallChk == 0){

                    if (uninsStatus === 'UNINS_EMPTY' && uninsStatus !== '' && remoteCallChk === 0) {

                        remoteCallChk = 0;
                        getMachineOnlineorOffile(serviceTag, ordr_num, cust_num, 1, provcode, trial);
                    } else if (uninsStatus === 'UNINS_DONE') {

                        viewAlrtsSts = '<span><img src="../js/entitlement/images/buttons/notavail.png" title="In Active"> <b><h3 style="display:inline">Uninstalled<h3></b>';
                        if(parseInt(nanoheal_prod) !== 0 || nanoheal_prod !== "0") {
                            viewAlrtsSts += '<span class="actionBtn" style="margin-right: -8%; font-weight: normal !important; float:right;" id="regn'+i+'" onclick="regenrt_div(\'' + cust_num + '\',\'' + ordr_num + '\',\'' + ppid +'\', \'1\')">Regenerate</span>';
                        }
                        viewAlrtsSts += "</span>";
                        viewAlrts = '';

                        $('#ent_machine_status_' + ordr_num).html(viewAlrtsSts);
                        //$('#viewAlrtDv_'+splMsg[3]).html(viewAlrts);
                    } else {
                        viewAlrtsSts = '<span><img src="../js/entitlement/images/buttons/notavail.png" title="In Active"> <b><h3 style="display:inline">Not Installed</h3></b> </span></td>';
                        if(parseInt(nanoheal_prod) !== 0 || nanoheal_prod !== "0") {
                            viewAlrtsSts += '<td><span class="actionBtn" style="margin-right: -8%; font-weight: normal !important; float:right;" id="regn'+i+'" onclick="regenrt_div(\'' + cust_num + '\',\'' + ordr_num + '\',\'' + ppid +'\', \'1\')">Regenerate</span></td>';
                        }
                        viewAlrts = '';
                        $('#ent_machine_status_' + ordr_num).html(viewAlrtsSts);
                    }
                } else if (serviceTag !== '0' && serviceTag !== 'empty' && serviceTag !== '' && (insStatus === 'Expired' || insStatus === 'Cancelled')) {
                    viewAlrtsSts = '';
                    viewAlrts = '';

                    $('#ent_machine_status_' + ordr_num).html(viewAlrtsSts);
                } else {
                    viewAlrtsSts = '<span><img src="../js/entitlement/images/buttons/notavail.png" title="In Active"> <b><h3 style="display:inline">Not Installed</h3></b></span></td>';
                    if(parseInt(nanoheal_prod) !== 0 || nanoheal_prod !== "0") {
                        viewAlrtsSts += '<td><span class="actionBtn" style="margin-right: -8%; font-weight: normal !important; float:right;" id="regn'+i+'" onclick="regenrt_div(\'' + cust_num + '\',\'' + ordr_num + '\',\'' + ppid +'\', \'1\')">Regenerate</span></td>';
                    }

                    viewAlrts = '';

                    $('#ent_machine_status_' + ordr_num).html(viewAlrtsSts);
                }
                $("#regn"+ i).show();
            }
            /* online or offline code ends */
            
            /* Getting all installations under a particular order num */
            for (var k = 1; k <= (rows_pcs.length - 1); k++) {

                var pc_info = (rows_pcs[k]).split('@@');

                if (k == 1) {
                    if (payStatus === "done") {
                        if (pc_info[5] == 'EXE') {

                            if (pc_info[6] == 'R') {
                                action = 'regenarate'; //regenarate
                                btn_id = corspId + "-" + action;
                                content2 += '<span class="actionBtn" style="margin-left: 5%; margin-bottom: -10%;" id="' + btn_id + '" onclick="entitle_slide_div(this,\'' + cust_num + '\',\'' + ordr_num + '\',\'' + k + '\')">Regenerate</span>';
                            } else {
                                action = 'revoke';  //revoked
                                btn_id = corspId + "-" + action;
                                content2 += '<span class="actionBtn" style="margin-left: 5%; margin-bottom: -10%;" id="' + btn_id + '" onclick="entitle_slide_div(this,\'' + cust_num + '\',\'' + ordr_num + '\',\'' + k + '\')">Revoke</span>';
                            }
                        } else if (pc_info[5] == 'D') {
                            action = 'regenarate';  //regenarate
                            btn_id = corspId + "-" + action;
                            content2 += '<span class="actionBtn" style="margin-left: 5%; margin-bottom: -10%;" id="' + btn_id + '" onclick="entitle_slide_div(this,\'' + cust_num + '\',\'' + ordr_num + '\',\'' + k + '\')">Regenerate</span>';
                        } else if (pc_info[5] == 'G') {
                            action = 'regenarate';  //regenarate
                            btn_id = corspId + "-" + action;
                            content2 += '<span class="actionBtn" style="margin-left: 5%; margin-bottom: -10%;" id="' + btn_id + '" onclick="entitle_slide_div(this,\'' + cust_num + '\',\'' + ordr_num + '\',\'' + k + '\')">Regenerate</span>';
                        }
                    } else {

                    }
                    content = '';
                    //content = '<div style="height:100px; width:20%; float:left; overflow:auto;"><table cellspacing="0" style="width:100%; float:left;">';
                    var licenseString = "";
                    
                    if (payStatus === "done") {
                        if (licenseKey !== "") {
                            licenseString = '<td><label class="ent_show_labels">&nbsp;&nbsp;License Key : </label></td><td id="licenseKeyTd'+i+'">&nbsp;'+ displayLicenseKey(licenseKey) +'</td>';
                        }
                    } else if (parseInt(ppid) === 0 && parseInt(trial) !== 1) {
                        if (licenseKey !== "") {
                            licenseString = '<td><label class="ent_show_labels">&nbsp;&nbsp;License Key : </label></td><td id="licenseKeyTd'+i+'">&nbsp;'+ displayLicenseKey(licenseKey) +'</td>';
                        }
                    }
                    
                    $('#' + i).append('<tr><td><label class="ent_show_labels">Installed PC&prime;s : </label></td><td id="seviceTagOptions'+i+'">&nbsp;</td>' + licenseString + '</tr>');
                    
                    //$('#'+i).append('<div style="height: 10px; clear: both;"></div>');
                    //content += '<tr bgcolor="#D8D8D8"><td><label class="ent_show_line3">Serial#</label>&nbsp;&nbsp;</td></tr>';
                }
                //alert($.trim(tempArray[1]));
                for (var p = 1; p <= 8; p++) {                //Fill empty columns with empty
                    if ((pc_info[p] == null) || (pc_info[p] == 0) || (pc_info[p] == undefined))
                        pc_info[p] = 'empty';
                }

                var st;
                if ((col[11] == 3)) {                     //Contract cancelled
                    st = 'Cancelled';
                    //no record with show up
                } else {
                    if ((col[9] == 'Expired')) {          //Contract Expired
                        st = 'Expired';
                    } else if (col[9] == 'Renewed') {
                        st = 'Revoked';
                    } else {                               //Contract Active
                        if (pc_info[6] == 'R') {          //If installtion revoked 
                            st = 'Revoked';
                        } else {
                            st = 'Active';
                        }
                    }
                }

                //content += '<tr><td><label class="ent_show_line4" style="cursor:pointer" onclick="showServTagDet(\''+pc_info[3]+'\',\''+i+'\',\''+cust_num+'\',\''+ordr_num+'\',\''+subCol[1]+'\')">'+pc_info[3]+'&nbsp;&nbsp;</label></td></tr>';  
                if (k == 1) {
                    showServTagDet(pc_info[3], i, cust_num, ordr_num, subCol[3]);
                }

                if (k == (rows_pcs.length - 1)) {
                    //content += '<tr bgcolor="#D8D8D8" style="height:2px;"><td colspan=1></td>';
                    //content += '</table></div>';
                    //$('#'+i).append(content);
                }
            }

            var contentDis = '<div id="servTagDet" style="float:left; width:100%"></div>';

            $('#' + i).append(contentDis);

            /* end - for loop */
            $('#' + i).append('<div style="height: 10px; clear: both;"></div>');

            /* INSTALLED PCs INFO HISTORY */
            var pcs_hist_order = col[18];
            var hist_pcs = pcs_hist_order.split("%%%history_list%%%");
            var content = '<table cellspacing="10" style="border-collapse: collapse; width:100%;">';

            var pc_info = (hist_pcs[1]).split('@@');

            /* Getting machines history from installed using a particular order num */

            for (var kl = 0; kl < (pc_info.length - 1); kl++) {
                if (kl == 0) {
                    $('#' + i).append('<label class="ent_show_labels">Orders,Upgrade &amp; Renewals History : </label>');
                }

                var hist_info = (pc_info[kl]).split('~~');
                var displaySku = "";
                if (hist_info[1].length > 25 ) {
                    displaySku = hist_info[1].substring(0,24) + "..";
                } else {
                    displaySku = hist_info[1];
                }
                 
                content += '<tr><td><label class="ent_show_line3" style="font-size:10px;">SKU Desc :</label></td><td><label class="ent_show_line4" style="font-size:10px;" title="' + hist_info[1] + '">' + displaySku + '</label></td><td><label class="ent_show_line3" style="font-size:10px;">Order Date :</label></td><td><label class="ent_show_line4" style="font-size:10px;">' + hist_info[2] + '</label></td><td><label class="ent_show_line3" style="font-size:10px;">Order Number :</label></td><td><label class="ent_show_line4" style="font-size:10px;">' + hist_info[3] + '</label></td><td><label class="ent_show_line3" style="font-size:10px;">Agent EmailID / PhoneID:</label></td><td><label class="ent_show_line4" style="font-size:10px;">' + hist_info[4] + '</label></td></tr>';
            }
            /* end - for loop */

            /* Backup upgrades */
            /*
             var upgrade_col     = col[19];                     
             upgrade_col         = (upgrade_col.split("%%%backup_upgrades%%%"));   	
             
             for(var z=1; z<=(upgrade_col.length-1); z++){
             var upgrade         = (upgrade_col[z]).split('@@');
             for(var q=1; q<=(upgrade.length-2); q++){
             
             var upgrade_order   = (upgrade[q].split("~~"))[0];
             var upgrade_date    = (upgrade[q].split("~~"))[1]; 
             var upgrade_sku     = (upgrade[q].split("~~"))[2]; 
             var upgrade_sku_desc= (upgrade[q].split("~~"))[3]; 
             
             if(upgrade_date ==0)    upgrade_date    ='empty';
             if(upgrade_sku ==0)     upgrade_sku     ='empty';
             if(upgrade_sku_desc ==0)upgrade_sku_desc='empty';
             if((col[9] != 'Renewed')){
             if(q == 1){
             //$('#'+i).append('<br><br>');
             //$('#'+i).append('<label class="ent_show_labels">Upgrades</label>');                          
             }
             content += '<tr><td><label class="ent_show_line2" style="font-size:10px;">SKU No : </label></td><td><label class="ent_show_line1" id="ent_show_backup_capacity" style="font-size:10px;">'+upgrade_sku+'</label></td><td><label class="ent_show_line2" style="font-size:10px;">SKU Desc : </label></td><td><label class="ent_show_line1" id="ent_show_backup_capacity" style="font-size:10px;">'+upgrade_sku_desc+'</label></td><td><label class="ent_show_line2" style="font-size:10px;">Order Date : </label></td><td><label class="ent_show_line1" id="ent_show_backup_capacity" style="font-size:10px;">'+upgrade_date+'</label></td><td><label class="ent_show_line2" style="font-size:10px;">Order Num : </label></td><td><label class="ent_show_line1" id="ent_show_backup_email" style="font-size:10px;">'+upgrade_order+'</label></td><td><label class="ent_show_line3" style="font-size:10px;">Agent ID :</label></td><td><label class="ent_show_line4" style="font-size:10px;">'+hist_info[4]+'</label></td></tr>';                       
             } 
             }
             } */

            $('#' + i).append(content);
        }

        /* start - Pagination*/
        var show_per_page = 1;
        var number_of_items = $('#ent_show_orders').children().size();
        var number_of_pages = Math.ceil(number_of_items / show_per_page);

        if (number_of_items > 1) {
            $('#show_details').append("<div id='page_navigation'></div>");
            $('#current_page').val(0);
            $('#show_per_page').val(show_per_page);

            var first_item = 0;
            var navigation_html = '<a class="first_link" href="javascript:go_to_page(' + first_item + ');"><img src="../js/entitlement/images/buttons/first_default.png" title="First" /></a>';
            navigation_html += '<a class="previous_link" href="javascript:previous();"><img src="../js/entitlement/images/buttons/previous_default.png" title="Previous" /></a>';
            var current_link = 0;
            navigation_html += 'Page <input type="text" id="textbox_pagelink" class="" value="1" onkeyup="javascript:textbox_go_to_page(this.value, ' + number_of_items + ')" name="txt" style="width:40px;"><label class="ent_show_line1" id="pagelink_text"></label>';

            while (number_of_pages > current_link) {
                navigation_html += '<a style="display:none;" class="page_link" href="javascript:go_to_page(' + current_link + ')" longdesc="' + current_link + '">' + (current_link + 1) + '</a>';
                current_link++;
            }
            navigation_html += '<a class="next_link" href="javascript:next();hideUrlDiv();"><img src="../js/entitlement/images/buttons/next_default.png" title="Next" /></a>';
            var last_item = number_of_items - 1;
            navigation_html += '<a class="last_link" href="javascript:go_to_page(' + last_item + ');"><img src="../js/entitlement/images/buttons/last_default.png" title="Last" /></a>';
            $('#page_navigation').html(navigation_html);
            $('#page_navigation .page_link:first').addClass('active_page');
            $('#ent_show_orders').children().css('display', 'none');
            $('#ent_show_orders').children().slice(0, show_per_page).css('display', 'block');

            $('#pagelink_text').html(" of " + number_of_items);
        }
        /* end - Pagination*/
    } else {
        //Record not found 
        $('#show_details').append('<label class="ent_show_labels_heading" style="color:#FF9933; font-size: 18px;">Not found !</label><label id="ent_show_cust_no" style="font-size: 16px; font-weight: bold;"></label>');
    }
    $('.loadingStage').hide();
}

function showServTagDet(servTag,i,cust_num,ordr_num,provcode){
    $('.loadingStage').show();
    $("#"+i).children("div#servTagDet").hide("slow");
    // data: "function=selServReqDetByServiceTag&servTag="+servTag+"&i="+i+"&cust_num="+cust_num+"&ordr_num="+ordr_num+"&provcode="+provcode,
    $.ajax({
        type: "POST",
        url: "../lib/l-ptsAjax.php?function=selServReqDetByServiceTag",
        data: {servTag:servTag,i:i,cust_num:cust_num,ordr_num:ordr_num,provcode:provcode,csrfMagicToken: csrfMagicToken},
        success: function(msg){
            $('.loadingStage').hide();
            var option = msg.split("%%");
            $("#seviceTagOptions" + i).html(option[1]);
            //showServTagDetailsAuto(option[1],i,cust_num,ordr_num,provcode);
            $("#"+i).children("div#servTagDet").html(option[0]);
            $("#"+i).children("div#servTagDet").show("slow");
            
        }
    /* end - success */
    });    
}

function getMachineOnlineorOffile(serviceTag, ordr_num, cust_num, stsCnt, provcode, trial){
    var viewAlrts       = '';
    var viewAlrtsSts    = '';
    var action          = '';
    var btn_id          = '';  
    if(stsCnt <= 8){
        $.ajax({
            type: "POST",
            url: "../lib/l-ptsAjax.php?function=get_onlineOrOfflineMachine",
            data: {serviceTag:serviceTag,ordr_num:ordr_num,cust_num:cust_num,csrfMagicToken: csrfMagicToken},
            success: function(msg){
                var splMsg    = msg.split("%%%%%");
                if(splMsg[1] == 'DONE'){
                    if(parseInt(splMsg[6]) === 1) {
                        if(splMsg[5] == 'Offline'){
                            viewAlrtsSts= '<span><img src="../ui/images/offline.png" title="Offline"> <b><h3 style="display:inline">Installed - Offline</h3></b></span>';
                            if(trial !== "1" && parseInt(trial) !== 1) {
                                viewAlrts   = '<span class="actionBtn" style="text-align:center; float:left; background:#33CCFF; margin-right:-1px;" id="'+btn_id+'"><a id="rdAnchor" href="../../NDashboard/support_action/index1.php?m='+splMsg[4]+'&es='+cust_num+'&sr='+ordr_num+'&from=prov" target="_blank" style="color: #fff;" >Remote Diagnosis</a></span>';  
                            }
                        } else {
                            viewAlrtsSts= '<span><img src="../ui/images/online.png" title="Online"> <b><h3 style="display:inline">Installed - Online</h3></b></span>';
                            if(trial !== "1" && parseInt(trial) !== 1) {
                                viewAlrts   = '<span class="actionBtn" style="text-align:center; float:left; background:#33CCFF; margin-right:-1px;" id="'+btn_id+'"><a id="rdAnchor" href="../../NDashboard/support_action/index1.php?m='+splMsg[4]+'&es='+cust_num+'&sr='+ordr_num+'&from=prov" target="_blank" style="color: #fff;" >Remote Diagnosis</a></span>';  
                            }
                            viewAlrts += '<span class="actionBtn" style="text-align:center; float:left; background:#33CCFF; margin-left:10px;" id="takeremote" onclick="takeRemoteNow();">Take Remote Now</span>';
                        }
                    } else {
                        viewAlrtsSts = '<span><img src="../ui/images/notavail.png" title="In Active">&nbsp;Not Activated</span>';
                        viewAlrts = '';
                    }
                } else {
                    //viewAlrtsSts  = '<span><img src="../ui/images/notavail.png" title="Not Reported"></span>';
                    //viewAlrts   = '';
                    viewAlrtsSts= '<span><img src="../ui/images/offline.png" title="Offline"> <b><h3 style="display:inline">Not Reported</h3></b></span>';
                    viewAlrts   = '';
                    $('#ent_machine_status_'+splMsg[3]).html(viewAlrtsSts);
                    $('#viewAlrtDv_'+splMsg[3]).html(viewAlrts);
                    
                    setTimeout(function(){
                        //your code to be executed after 1 mintues (30000)
                        getMachineOnlineorOffile(serviceTag, ordr_num, cust_num, stsCnt+1, provcode, trial);
                    },60000);       
                }
                $('#ent_machine_status_'+splMsg[3]).html(viewAlrtsSts);
                $('#viewAlrtDv_'+splMsg[3]).html(viewAlrts);
            }
        });
    }
}

function previous(){  
    new_page = parseInt($('#current_page').val()) - 1;  
		
    if($('.active_page').prev('.page_link').length==true){  
        go_to_page(new_page);  
    }  
} 
function next(){  
    new_page = parseInt($('#current_page').val()) + 1;  
		
    if($('.active_page').next('.page_link').length==true){  
        go_to_page(new_page);  
    }  
}

function hideUrlDiv(){
    $("#activity_entitlement_right").hide();
}

function textbox_go_to_page(n, i){
    if((n>=1) && (n<=i) && n!=0){
        n--;
        go_to_page(n);
    }
    else{
    //go_to_page(1);
    }
}

function go_to_page(page_num){  
    var show_per_page = parseInt($('#show_per_page').val());  

    var start_from 	= page_num * show_per_page;  
    var end_on 		= start_from + show_per_page;  
		
    $('#ent_show_orders').children().css('display', 'none').slice(start_from, end_on).css('display', 'block');  
    $('.page_link[longdesc=' + page_num +']').addClass('active_page').siblings('.active_page').removeClass('active_page');  
    $('#current_page').val(page_num);  
    $('#textbox_pagelink').val(page_num + 1);
}  

function takeRemoteNow() {
    $.nyroModalManual({
        debug: false,
        width: 360, // default Width If null, will be calculate automatically
        height: 250, // default Height If null, will be calculate automatically				
        bgColor: '#333',
        ajax: {url:"takeRemoteLMI.php"+"&csrfMagicToken=" + csrfMagicToken, data:'', type: 'GET'},
        closeButton: true,
        css: { // Default CSS option for the nyroModal Div. Some will be overwritten or updated when using IE6			
            wrapper: {
                position: 'absolute',
                top: '50%',
                left: '50%'
            }
        }
    });
}