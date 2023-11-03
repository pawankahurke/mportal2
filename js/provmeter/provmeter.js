// Provisioning & Metering Module JavaScript Functionality.

$(document).ready(function() {
    var table = $('#productTable').DataTable({
        autoWidth: true,
        paging: true,        
        searching: true,
        processing: true,
        serverSide: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        ajax: {
            url: "productFunction.php?function=get_ProductList"+"&csrfMagicToken=" + csrfMagicToken,
            type: "POST",
            rowId: 'pid'
        },
        columns: [
            {"data": "prodname", "orderable": true},
            {"data": "global"},
            {"data": "defaultenable"},
            {"data": "defaultmonitor"},
            {"data": "created"},
            {"data": "modified"}
        ],
        "columnDefs": [
            { className: "dt-left", "targets": [ 0,1,2,3,4,5 ] }
          ],
        ordering: true,
        select: false,
        bInfo: false,
        responsive: true,
        dom: '<"top"i>rt<"bottomtable"flp><"clear">',
    });
    $('#productTable_filter').hide();
    $("#Provisioning_searchbox").keyup(function () {
        table.search(this.value).draw();
    });
    $(".bottompager").each(function(){
        $(this).append($(this).find(".bottomtable"));
    });
    $("#productTable_length").hide();
    
    $('#productTable').on('click', 'tr', function() {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        } else {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });        
    
    // JavaScript Content for the Add Product : Start.
    
    $('input[type="text"]').removeAttr('disabled');
    $('.filestyle').each(function(){
        var name = $(this).attr('name');
        $('#'+name).next('div').find('input[type="text"]').attr('name', name);
    });
    
    $('#glob').click(function(){
        if($(this).prop("checked") == true){
            $('#glob').val(1);
        }
        else if($(this).prop("checked") == false){
            $('#glob').val(0);
        }
    });
    $('#enab').click(function(){
        if($(this).prop("checked") == true){
            $('#enab').val(1);
        } else {
            $('#enab').val(0);
        }
    });
    $('#dmon').click(function(){
        if($(this).prop("checked") == true){
            $('#dmon').val(1);
        } else {
            $('#dmon').val(0);
        }
    });
    
    $('#addproduct').click(function(){
        $('#addproductform')[0].reset();
        $('#prodtype').html('Add New');
        $('.label-floating').addClass('is-focused, is-empty');
        $('.label-floating').removeClass('is-focused');
    });
    
    $('#addsubmit').click(function() {
        var pname = $('#prod_name').val();
        if(pname === '') {
            $('#err').html('Please enter a product name');
        } else {
            $.ajax({
                url: "productFunction.php?function=addProductDetail"+"&csrfMagicToken=" + csrfMagicToken,
                type: 'POST',
                data: $('#addproductform').serialize(),
                success: function (data) {
                    var res = data.trim();
                    if(res === 'EXIST') {
                        $('#err').html('Product name already exists.');
                    } else {
                        $('#err').html('Product added successfully.');
                        $('#close_modal').click();
                        var urlnew = "productFunction.php?function=get_ProductList";
                        table.ajax.url(urlnew).load();
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
    });
    // JavaScript Content for the Add Product : End.
    
    
    // JavaScript Content for the Edit Product : Start.

    $('#editproduct').click(function(){
        $('#editproductform')[0].reset();
        $('.label-floating').addClass('is-focused, is-empty');
        $('.label-floating').removeClass('is-focused');
        $('#add_new_product').hide();
        var prodid = $('#productTable tbody tr.selected').attr('id');
        if(typeof prodid === 'undefined' || prodid === 'undefined') {
            alert('Please select a record to edit');
            return false;
        } else {
            $.ajax({
                url: "productFunction.php?function=get_ProductDetail",
                type: 'POST',
                data: 'prodid='+prodid+"&csrfMagicToken=" + csrfMagicToken,
                dataType: 'json',
                success: function (data) {
                    $("#prodid").val(prodid);
                    $('#eprod_name').val(data.prodname);
                    $('#eprod_name').prev().parent().removeClass('is-empty');
                    if(data.global === '1') {
                        $('#eglob').prop('checked', 'checked');
                        $('#eglob').val(data.global);
                    } else {
                        $('#eglob').val(0);
                    }
                    if(data.enable === '1') { 
                        $('#eenab').prop('checked', 'checked'); 
                        $('#eenab').val(data.enable);
                    } else{
                        $('#eenab').val(0);
                    }
                    if(data.monitor === '1') { 
                        $('#edmon').prop('checked', 'checked'); 
                        $('#edmon').val(data.monitor);
                    } else {
                        $('#edmon').val(0);
                    }
                    
                    if(data.meter1) {
                        $('#emtxt1').next('div').find('input[type="text"]').val(data.meter1);
                        $('#emtxt1').prev().parent().removeClass('is-empty');
                    }
                    if(data.meter2) {
                        $('#emtxt2').next('div').find('input[type="text"]').val(data.meter2);
                        $('#emtxt2').prev().parent().removeClass('is-empty');
                    }
                    if(data.meter3) {
                        $('#emtxt3').next('div').find('input[type="text"]').val(data.meter3);
                        $('#emtxt3').prev().parent().removeClass('is-empty');
                    }
                    if(data.meter4) {
                        $('#emtxt4').next('div').find('input[type="text"]').val(data.meter4);
                        $('#emtxt4').prev().parent().removeClass('is-empty');
                    }
                    if(data.meter5) {
                        $('#emtxt5').next('div').find('input[type="text"]').val(data.meter5);
                        $('#emtxt5').prev().parent().removeClass('is-empty');
                    }
                    
                    if(data.key1) {
                        $('#ektxt1').next('div').find('input[type="text"]').val(data.key1);
                        $('#ektxt1').prev().parent().removeClass('is-empty');
                    }
                    if(data.key2) {
                        $('#ektxt2').next('div').find('input[type="text"]').val(data.key2);
                        $('#ektxt2').prev().parent().removeClass('is-empty');
                    }
                    if(data.key3) {
                        $('#ektxt3').next('div').find('input[type="text"]').val(data.key3);
                        $('#ektxt3').prev().parent().removeClass('is-empty');
                    }
                    if(data.key4) {
                        $('#ektxt4').next('div').find('input[type="text"]').val(data.key4);
                        $('#ektxt4').prev().parent().removeClass('is-empty');
                    }
                    if(data.key5) {
                        $('#ektxt5').next('div').find('input[type="text"]').val(data.key5);
                        $('#ektxt5').prev().parent().removeClass('is-empty');
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
        $('#eglob').click(function(){
            if($(this).prop("checked") == true){
                $('#eglob').val(1);
            }
            else if($(this).prop("checked") == false){
                $('#eglob').val(0);
            }
        });
        $('#eenab').click(function(){
            if($(this).prop("checked") == true){
                $('#eenab').val(1);
            } else {
                $('#eenab').val(0);
            }
        });
        $('#edmon').click(function(){
            if($(this).prop("checked") == true){
                $('#edmon').val(1);
            } else {
                $('#edmon').val(0);
            }
        });
    });
    
    $('#editsubmit').click(function() {
        var pname = $('#eprod_name').val();
        if(pname === '') {
            $('#eerr').html('Product name cannot be empty.');
        } else {
            $.ajax({
                url: "productFunction.php?function=editProductDetail"+"&csrfMagicToken=" + csrfMagicToken,
                type: 'POST',
                data: $('#editproductform').serialize(),
                success: function (data) {
                    var res = data.trim();
                    if(res === 'EXIST') {
                        $('#eerr').html('Product name already exists.');
                    } else {
                        $('#eerr').html('Product updated successfully.');
                        $('#eclose_modal').click();
                        var urlnew = "productFunction.php?function=get_ProductList";
                        table.ajax.url(urlnew).load();
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
    });

    // JavaScript Content for the Edit Product : End.

    // JavaScript Content for the View Product : Start.
    
    $('#viewproduct').click(function(){
        $('#edit_product').hide();
        $('#viewproductform')[0].reset();
        $('.label-floating').addClass('is-focused, is-empty');
        $('.label-floating').removeClass('is-focused');
        var prodid = $('#productTable tbody tr.selected').attr('id');
        if(typeof prodid === 'undefined' || prodid === 'undefined') {
            alert('Please select a record to view');
            return false;
        } else {
            $.ajax({
                url: "productFunction.php?function=get_ProductDetail",
                type: 'POST',
                data: 'prodid='+prodid+"&csrfMagicToken=" + csrfMagicToken,
                dataType: 'json',
                success: function (data) {
                    $("#prodid").val(prodid);
                    $('#vprod_name').val(data.prodname);
                    $('#vprod_name').prev().parent().removeClass('is-empty');
                    if(data.global === '1') {
                        $('#vglob').prop('checked', 'checked'); }
                    if(data.enable === '1') { 
                        $('#venab').prop('checked', 'checked'); }
                    if(data.monitor === '1') { 
                        $('#vdmon').prop('checked', 'checked'); }
                    
                    if(data.meter1) {
                        $('#vmtxt1').next('div').find('input[type="text"]').val(data.meter1);
                        $('#vmtxt1').prev().parent().removeClass('is-empty');
                    }
                    if(data.meter2) {
                        $('#vmtxt2').next('div').find('input[type="text"]').val(data.meter2);
                        $('#vmtxt2').prev().parent().removeClass('is-empty');
                    }
                    if(data.meter3) {
                        $('#vmtxt3').next('div').find('input[type="text"]').val(data.meter3);
                        $('#vmtxt3').prev().parent().removeClass('is-empty');
                    }
                    if(data.meter4) {
                        $('#vmtxt4').next('div').find('input[type="text"]').val(data.meter4);
                        $('#vmtxt4').prev().parent().removeClass('is-empty');
                    }
                    if(data.meter5) {
                        $('#vmtxt5').next('div').find('input[type="text"]').val(data.meter5);
                        $('#vmtxt5').prev().parent().removeClass('is-empty');
                    }
                    
                    if(data.key1) {
                        $('#vktxt1').next('div').find('input[type="text"]').val(data.key1);
                        $('#vktxt1').prev().parent().removeClass('is-empty');
                    }
                    if(data.key2) {
                        $('#vktxt2').next('div').find('input[type="text"]').val(data.key2);
                        $('#vktxt2').prev().parent().removeClass('is-empty');
                    }
                    if(data.key3) {
                        $('#vktxt3').next('div').find('input[type="text"]').val(data.key3);
                        $('#vktxt3').prev().parent().removeClass('is-empty');
                    }
                    if(data.key4) {
                        $('#vktxt4').next('div').find('input[type="text"]').val(data.key4);
                        $('#vktxt4').prev().parent().removeClass('is-empty');
                    }
                    if(data.key5) {
                        $('#vktxt5').next('div').find('input[type="text"]').val(data.key5);
                        $('#vktxt5').prev().parent().removeClass('is-empty');
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
    });
    
    // JavaScript Content for the View Product : End.

    
    $('#delproduct').click(function(){
        var prodid = $('#productTable tbody tr.selected').attr('id');
        var prodname = $('#productTable tbody tr.selected td').first().html();
        if(typeof prodid === 'undefined' || prodid === 'undefined') {
            alert('Please select a record to delete');
            return false;
        } else {
            var conf = confirm('Are you sure want to delete the Product `' + prodname + '`');
            if(conf) {
                $.ajax({
                    url: "productFunction.php?function=deleteProductDetail",
                    type: 'POST',
                    data: 'prodid='+prodid+'&prodname'+prodname+"&csrfMagicToken=" + csrfMagicToken,
                    success: function (data) {
                        console.log(data);
                        var urlnew = "productFunction.php?function=get_ProductList";
                        table.ajax.url(urlnew).load();
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        }
    });

    
    // JavaScript Content for the Copy Product : Start.
    
    $("#copyproduct").click(function (){
        $('#addproductform')[0].reset();
        $('.label-floating').addClass('is-focused, is-empty');
        $('.label-floating').removeClass('is-focused');
        $('#prodtype').html('Copy');
        var prodid = $('#productTable tbody tr.selected').attr('id');
        if(typeof prodid === 'undefined' || prodid === 'undefined') {
            alert('Please select a record to edit');
            return false;
        } else {
            $.ajax({
                url: "productFunction.php?function=get_ProductDetail",
                type: 'POST',
                data: 'prodid='+prodid+"&csrfMagicToken=" + csrfMagicToken,
                dataType: 'json',
                success: function (data) {
                    $('#prod_name').val('Copy of ' + data.prodname);
                    $('#prod_name').prev().parent().removeClass('is-empty');
                    if(data.global === '1') {
                        $('#glob').prop('checked', 'checked');
                        $('#glob').val(data.global);
                    } else {
                        $('#glob').val(0);
                    }
                    if(data.enable === '1') { 
                        $('#enab').prop('checked', 'checked'); 
                        $('#enab').val(data.enable);
                    } else{
                        $('#enab').val(0);
                    }
                    if(data.monitor === '1') { 
                        $('#dmon').prop('checked', 'checked'); 
                        $('#dmon').val(data.monitor);
                    } else {
                        $('#dmon').val(0);
                    }
                    
                    if(data.meter1) {
                        $('#mtxt1').next('div').find('input[type="text"]').val(data.meter1);
                        $('#mtxt1').prev().parent().removeClass('is-empty');
                    }
                    if(data.meter2) {
                        $('#mtxt2').next('div').find('input[type="text"]').val(data.meter2);
                        $('#mtxt2').prev().parent().removeClass('is-empty');
                    }
                    if(data.meter3) {
                        $('#mtxt3').next('div').find('input[type="text"]').val(data.meter3);
                        $('#mtxt3').prev().parent().removeClass('is-empty');
                    }
                    if(data.meter4) {
                        $('#mtxt4').next('div').find('input[type="text"]').val(data.meter4);
                        $('#mtxt4').prev().parent().removeClass('is-empty');
                    }
                    if(data.meter5) {
                        $('#mtxt5').next('div').find('input[type="text"]').val(data.meter5);
                        $('#mtxt5').prev().parent().removeClass('is-empty');
                    }
                    
                    if(data.key1) {
                        $('#ktxt1').next('div').find('input[type="text"]').val(data.key1);
                        $('#ktxt1').prev().parent().removeClass('is-empty');
                    }
                    if(data.key2) {
                        $('#ktxt2').next('div').find('input[type="text"]').val(data.key2);
                        $('#ktxt2').prev().parent().removeClass('is-empty');
                    }
                    if(data.key3) {
                        $('#ktxt3').next('div').find('input[type="text"]').val(data.key3);
                        $('#ktxt3').prev().parent().removeClass('is-empty');
                    }
                    if(data.key4) {
                        $('#ktxt4').next('div').find('input[type="text"]').val(data.key4);
                        $('#ktxt4').prev().parent().removeClass('is-empty');
                    }
                    if(data.key5) {
                        $('#ktxt5').next('div').find('input[type="text"]').val(data.key5);
                        $('#ktxt5').prev().parent().removeClass('is-empty');
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
        $('#glob').click(function(){
            if($(this).prop("checked") == true){
                $('#glob').val(1);
            }
            else if($(this).prop("checked") == false){
                $('#glob').val(0);
            }
        });
        $('#enab').click(function(){
            if($(this).prop("checked") == true){
                $('#enab').val(1);
            } else {
                $('#enab').val(0);
            }
        });
        $('#dmon').click(function(){
            if($(this).prop("checked") == true){
                $('#dmon').val(1);
            } else {
                $('#dmon').val(0);
            }
        });
    });
    
    // JavaScript Content for the Copy Product : End.
    
    
    // JavaScript Content for the Configure Product : Start.
    
    $('#configproduct').click(function (){
        $('#configure_form')[0].reset();
        $('.confok').hide();
        var prodid = $('#productTable tbody tr.selected').attr('id');
        var prodname = $('#productTable tbody tr.selected td').first().html();
        if(typeof prodid === 'undefined' || prodid === 'undefined') {
            alert('Please select a record to configure.');
            return false;
        } else {
            $.ajax({
                url: "productFunction.php?function=get_ConfigureDetail",
                type: 'POST',
                data: 'prodid='+prodid+'&prodname='+prodname+"&csrfMagicToken=" + csrfMagicToken,
                dataType: 'json',
                success: function (data) {
                    if(data.missing == 'NOTAVIA') {
                        $('.missing_site').show();
                        $('.conf_form, .confnew').hide();
                    }
                    if(data.conftype == 'UPDATE') {
                        $('.confnew').hide();
                        $('.confok').show();
                    }
                    $('#sitename').val(data.sitename);
                    $('#sitename').prev().parent().removeClass('is-empty');
                    $('#confprodname').val(data.prodname);
                    $('#confprodname').prev().parent().removeClass('is-empty');
                    if(data.provis === '1' || data.provis === 1) {
                        $('#provis').prop('checked', 'checked');
                        $('#provis').val(data.provis);
                    } else {
                        $('#provis').val(0);
                    }
                    if(data.enable === '1' || data.enable === 1) {
                        $('#enable').prop('checked', 'checked');
                        $('#enable').val(data.provis);
                    } else {
                        $('#enable').val(0);
                    }
                    if(data.monitor === '1' || data.monitor === 1) {
                        $('#meter').prop('checked', 'checked');
                        $('#meter').val(data.provis);
                    } else {
                        $('#meter').val(0);
                    }
                    $('#configtype').val(data.conftype);
                    $('#provis').click(function(){
                        if($(this).prop("checked") == true){
                            $('#provis').val(1);
                        }
                        else if($(this).prop("checked") == false){
                            $('#provis').val(0);
                        }
                    });
                    $('#enable').click(function(){
                        if($(this).prop("checked") == true){
                            $('#enable').val(1);
                        } else {
                            $('#enable').val(0);
                        }
                    });
                    $('#meter').click(function(){
                        if($(this).prop("checked") == true){
                            $('#meter').val(1);
                        } else {
                            $('#meter').val(0);
                        }
                    });
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
    });
    
    function commonConfigFunction() {
        var contype = $('#configtype').val();
        var prodid  = $('#productTable tbody tr.selected').attr('id');
        var provis  = $('#provis').val();
        var enable  = $('#enable').val();
        var monitor = $('#meter').val();
        $.ajax({
            url: "productFunction.php?function=updateConfigureDetail",
            type: 'POST',
            data: 'contype='+contype+'&prodid='+prodid+'&provis='+provis+'&enable='+enable+'&monitor='+monitor+"&csrfMagicToken=" + csrfMagicToken,
            success: function (data) {
                setTimeout(function(){
                    $("#msg").html(data.trim());
                }, 3000);
            },
            error: function (err) {
                console.log(err);
            }
        });
    }
    
    $('#configure').click(function (){
        commonConfigFunction();
    });
    
    $('#confupdate').click(function (){
        commonConfigFunction();
    });
    
    $('#confdelete').click(function (){
        $('#configtype').val('DELETE');
        var sitename = $('#sitename').val();
        var prodname = $('#confprodname').val();
        var conf = confirm('Are you sure want to delete the Product `' + prodname + '` from site `'+sitename+'`');
        if(conf) {
            commonConfigFunction();
        } else {
            return false;
        }
    });
    
    // Machine Level Configuration Changes
    $('#mac_configproduct').click(function (){
        $('#configure_machine_form')[0].reset();
        $('.mac_confnew, .conf_set, .add_conf, .pro_conf').hide();
        $('#mac_msg').html('');
        var prodid   = $('#productTable tbody tr.selected').attr('id');
        var prodname = $('#productTable tbody tr.selected td').first().html();
        if(typeof prodid === 'undefined' || prodid === 'undefined') {
            alert('Please select a record to configure.');
            return false;
        } else {
            $.ajax({
                url: "productFunction.php?function=get_MachineConfigureDetail"+"&csrfMagicToken=" + csrfMagicToken,
                type: 'POST',
                data: '',
                dataType: 'json',
                success: function (data) {
                    $('#mac_sitename').val(data.sitename);
                    $('#mac_sitename').prev().parent().removeClass('is-empty');
                    $('#machinename').val(data.machinename);
                    $('#machinename').prev().parent().removeClass('is-empty');
                    if(!data.localsetting) {
                        $('.mac_cancel').hide();
                        $('#mac_sitename').val(data.sitename);
                        $('#mac_sitename').prev().parent().removeClass('is-empty');
                        $('#machinename').val(data.machinename);
                        $('#machinename').prev().parent().removeClass('is-empty');
                        $('#mac_name').html(data.machinename);
                        $('#site_name').html(data.sitename);
                    } else {
                        $('.conf_set').show();
                        $('.mac_confok').hide();
                        $('#yesmsg').hide();
                        $('#localupdate').html('Yes');
                        $('.localexist').hide();
                        $('.localnot').show();
                    }
                    
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
    });
    
    $('#mac_yes').click(function (){
        $.ajax({
            url:'configProductMachineFunction.php?function=createLocalConfiguration'+"&csrfMagicToken=" + csrfMagicToken,
            type: 'POST',
            success: function () {
                $('#localupdate').html('Yes');
                $('#yesmsg, .mac_confok').hide();
                $('.conf_set').show();
                $('.mac_cancel').show();
            }
        });
        return false;
    });
    
    $('#mprovis').click(function(){
        if($(this).prop("checked") == true){
            $('#mprovis').val(1);
        }
        else if($(this).prop("checked") == false){
            $('#mprovis').val(0);
        }
    });
    $('#menable').click(function(){
        if($(this).prop("checked") == true){
            $('#menable').val(1);
        } else {
            $('#menable').val(0);
        }
    });
    $('#mmeter').click(function(){
        if($(this).prop("checked") == true){
            $('#mmeter').val(1);
        } else {
            $('#mmeter').val(0);
        }
    });
    
    $('#editset').click(function (){
        var prodid   = $('#productTable tbody tr.selected').attr('id');
        var prodname = $('#productTable tbody tr.selected td').first().html();
        $('.localexist').show();
        $('.localnot, .conf_set, .pro_conf').hide();
        $('#mac_prodname').val(prodname);
        $('#mac_prodname').prev().parent().removeClass('is-empty');
        $.ajax({
            url:'productFunction.php?function=updateLocalConfiguration',
            type:'POST',
            data:'prodid='+prodid+'&prodname='+prodname+"&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json',
            success: function (data) {
                if(data.provis === '1' || data.provis === 1) {
                    $('#mprovis').prop('checked', 'checked');
                    $('#mprovis').val(data.provis);
                } else {
                    $('#mprovis').val(0);
                }
                if(data.enable === '1' || data.enable === 1) {
                    $('#menable').prop('checked', 'checked');
                    $('#menable').val(data.provis);
                } else {
                    $('#menable').val(0);
                }
                if(data.monitor === '1' || data.monitor === 1) {
                    $('#mmeter').prop('checked', 'checked');
                    $('#mmeter').val(data.provis);
                } else {
                    $('#mmeter').val(0);
                }
                if(data.constat == 'CONF') {
                    $('.pro_conf').show();
                } else {
                    $('.conf_set').hide();
                    $('.add_conf').show();
                }
            }
        });
        return false;
    });
    
    
    $("#delset").click(function (){
        var sitename = $('#mac_sitename').val();
        var hostname = $('#machinename').val();
        var conf = confirm('Are you sure you want to remove the local product settings for machine '+hostname+' at site '+sitename+' and revert to the site-wide settings? ');
        if(conf) {
            $.ajax({
                url: "configProductMachineFunction.php?function=revertSettings"+"&csrfMagicToken=" + csrfMagicToken,
                type: 'POST',
                success: function (data) {
                    console.log(data);
                }
            });
        }
        return false;
    });
    
    $('#mac_addroduct').click(function(){
        var pid  = $('#productTable tbody tr.selected').attr('id');
        var prov = $('#mprovis').val();
        var enab = $('#menable').val();
        var metr = $('#mmeter').val();

        var param = "&prov="+prov+"&enab="+enab+"&metr="+metr;
        $.ajax({
            url: "configProductMachineFunction.php?function=addMachineProduct&type=add&pid="+pid+param,
            type: 'POST',
            success: function (data) {
                $('#mac_msg').html(data.trim());
                setTimeout(function(){ $('#closemodal').click(); }, 3000);
            }
        });
        return false;
    });
    
    $('#mac_update').click(function (){
        var pid   = $('#productTable tbody tr.selected').attr('id');
        var prov  = $('#mprovis').val();
        var enab  = $('#menable').val();
        var metr  = $('#mmeter').val();
        var param = "&pid="+pid+"&prov="+prov+"&enab="+enab+"&metr="+metr;
        $.ajax({
            url: "configProductMachineFunction.php?function=addMachineProduct&type=update"+param+"&csrfMagicToken=" + csrfMagicToken,
            type: 'POST',
            success: function (data) {
                $('#mac_msg').html(data.trim());
                setTimeout(function(){ $('#closemodal').click(); }, 3000);
            }
        });
        return false;
    });
    
    $('#mac_delete').click(function (){
        var pid   = $('#productTable tbody tr.selected').attr('id');
        var sitename = $('#mac_sitename').val();
        var hostname = $('#machinename').val();
        var conf = confirm('Are you sure you want to delete product from machine '+hostname+' at site '+sitename+'?');
        if(conf) {
            $.ajax({
                url: "configProductMachineFunction.php?function=addMachineProduct&type=delete&pid="+pid,
                type: 'POST',
                success: function (data) {
                    $('#mac_msg').html(data.trim());
                    setTimeout(function(){ $('#closemodal').click(); }, 3000);
                }
            });
        }
        return false;
    });
    
    // JavaScript Content for the Configure Product : End.
    

});
