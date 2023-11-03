$(function () {
    provproductGrid();
})

function provproductGrid() {
    $.ajax({
        url: "provmeterfunction.php?function=get_provproductList"+"&csrfMagicToken=" + csrfMagicToken,
        type: "POST",
        dataType: "json",
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#provproductGrid').DataTable().destroy();
            groupTable = $('#provproductGrid').DataTable({
                scrollY: jQuery('#provproductGrid').data('height'),
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
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                columnDefs: [{className: "checkbox-btn", "targets": [0]},
                    {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                initComplete: function (settings, json) {
                },
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });

    $('#provproductGrid').on('click', 'tr', function () {

        var rowID = groupTable.row(this).data();
        var selected = rowID[6];
        $('#selected').val(selected);

        groupTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });

    $("#provproduct_searchbox").keyup(function () {//group search code
        groupTable.search(this.value).draw();
    });

    $('#provproductGrid').DataTable().search('').columns().search('').draw();
}

function selectConfigure(data_attr_id) {
    var selected = $('#selected').val();
    if (data_attr_id == 'addProduct') {

        $('#addProductpopup').modal('show');
        $('#headingpopup').html('<h2 class="popup-title">Add Product</h2>');
        $('#addproductbutton').show();
        $('#viewproductbutton').hide();
        $('#editproductbutton').hide();

    } else if (data_attr_id == 'confProduct') {

        configureProduct(selected);

    } else if (data_attr_id == 'deleteProduct') {

        if (selected != '') {
            $('#delete-product-detail').modal('show');
        } else {
            $('#warning').modal('show');
        }
    } else if (data_attr_id == 'deleteProductfinal') {

        deleteProductpopup(selected);

    } else if (data_attr_id == 'editProduct') {
        var name = 'Edit Product';
        editproductValue(selected, name, 'edit');

    } else if (data_attr_id == 'viewProduct') {
        var name = 'View Product';
        editproductValue(selected, name, 'view');
    } else if (data_attr_id == 'cancelAdd') {
        cancelReload();
    } else if (data_attr_id == 'cancelEdit') {
        cancelReload();
    } else if (data_attr_id == 'cancelView') {
        cancelReload();
    }
}

function configureProduct(id) {

    var level = $("#searchType").val();
    var sitename = $("#searchValue").val();
    var URL;
    if (id != '') {
        if (level == 'Sites') {
            URL = 'provmeterfunction.php?function=get_configureproductList';
        } else if (level == 'ServiceTag') {
            URL = 'provmeterfunction.php?function=get_configureprdctMachineList';
        }

        $.ajax({
            url: URL,
            type: 'post',
            data: 'pid=' + id + '&type=config' +"&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json',
            success: function (data) {
                console.log(data);

                var site = sitename.split('__')[0];
                if (data.msg == 'invalid') {

                    $('#configurenothing').modal('show');
                    $('#confproductnothing').html('<p>Missing site variables for scrips 229 and 230 at site <b>' + site + '</b> This just means that the client version is too old to support provisioning.Update your client software and try again later.</p>');

                } else if (data.msg == 'valid') {

                    $('#configureValue').modal('show');
                    $('#sitename').val(site);
                    $('#Pname').val(data.pname);
                    $('#provcheck').attr({checked: data.provChk, value: data.provVal});
                    $('#enablecheck').attr({checked: data.enableChk, value: data.enableVal});
                    $('#metercheck').attr({checked: data.monitorChkn, value: data.monitorVal});

                } else if (data.msg == 'TRUE') {

                } else if (data.msg == 'FALSE') {
                    $('#machineConfigure').modal('show');
                    $('#sitname').val(data.rparentName);
                    $('#Macname').val(data.machinename);
                    $('#machinevaluefalse').show();
                    $('#checkLocal').html('<p>Are you sure you want to create machine-specific settings that will override the site-wide settings for provisioning and metering for machine<b> ' + data.machinename + ' </b> at site <b> ' + data.rparentName + ' ?</b></p>');
                    $('#delSetMsg').html('<p>Are you sure you want to remove the local product settings for machine <b>' + data.machinename + '</b> at site <b>' + data.rparentName + '</b> and revert to the site-wide settings? </p>');
                    $('#buttonshow').show();
                    $('#prodname').val(data.pname);
                    $('#provcheckMach').attr({checked: data.provChk, value: data.provVal});
                    $('#enablecheckMach').attr({checked: data.enableChk, value: data.enableVal});
                    $('#metercheckMach').attr({checked: data.monitorChkn, value: data.monitorVal});
                }
            }
        })

    } else {
        $('#warning').modal('show');
    }
}

$('#configYes').click(function () {
    $.ajax({
        url: 'configProductMachineFunction.php?function=createLocalConfiguration&' + parameter +"&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        success: function () {
//            console.log('Local settings added');
            $('#conf').html('Yes');
            $('#checkLocal').hide();
            $('#buttonshow').hide();
            $('#configyesshow').show();
//                        $('.mainControl').show();
//                        $('#editsettings').show();
//                        $('#delsettings').show();
        }
    });
})

$('#editsettings').click(function () {
    var pid = $('#selected').val();
    $.ajax({
        url: "configProductMachineFunction.php?function=checkProductConfig&pid=" + pid +"&csrfMagicToken=" + csrfMagicToken, 
        type: 'POST',
        success: function (data) {
            if (data.trim() == 'CONF') {
                $('.machinevaluefalse').hide();
                $('#productnameshow,#Provisionedmachine,#Enableddmachine,#machinevaluefalse').show();
                $('#AddMachine').show();
            } else {
//                            $('.addpro').show();
                $('#productnameshow,#Provisionedmachine,#Enableddmachine,#machinevaluefalse,#meterMachine,#meterMachine').show();
                $('#machinevaluefalse,#configyesshow').hide();
                $('#AddMachine').show();
//                            $('.update').hide();
//                            $('.mainConfig').hide();
//                            $('.machineConfig').show();
//                            $('#delproduct').hide();
//                            $('.locconf').hide();
            }
        }
    });
})

$('#addproduct').click(function () {
    var pid = $('#selected').val();
    var prov = $('#provcheckMach').val();
    var enab = $('#enablecheckMach').val();
    var metr = $('#metercheckMach').val();


    var param = "&prov=" + prov + "&enab=" + enab + "&metr=" + metr + "&host=" + parameter;
    $.ajax({
        url: "configProductMachineFunction.php?function=addMachineProduct&type=add&pid=" + pid + param +"&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        success: function (data) {
//            console.log(data);
            $('#error1').html('<span style="color:green;">' + data + '</span>');
            setTimeout(function () {
                location.reload();
            }, 3000);
        }
    });
});

$('#delsettings').click(function () {
    $('#delSetMsg').show();
    $('#buttonshow,#configyesshow,#AddMachine').hide();
    $('#deleteshow').show();
});

$('#delsetNoconf').click(function () {
    $('#delSetMsg').hide();
//        $('.mainConfig').show();
//        $('.mainControl').show();
});

$('#delsetconf').click(function () {
    $.ajax({
        url: "configProductMachineFunction.php?function=revertSettings&host=" + parameter +"&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        success: function (data) {
//            console.log(data);
            $('#error1').html('<span style="color:green;">' + data + '</span>');
            setTimeout(function () {
                location.reload();
            }, 3000);

            //$('.nyroModalClose').click();
        }
    });
});

function deleteProductpopup(id) {
    $.ajax({
        url: 'provmeterfunction.php?function=get_productDelete',
        type: 'post',
        data: 'pid=' + id +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'text',
        success: function (data) {
            if ($.trim(data) == 'success') {
                $('#delete-product-detail').modal('hide');
            provproductGrid();
//                location.reload();
        }
        }
    })
}


function configproduct() {
    var sitename = $('#sitename').val();
    var Pname = $('#Pname').val();
    var provcheck = $('#provcheck').val();
    var enablecheck = $('#enablecheck').val();
    var metercheck = $('#metercheck').val();
    var id = $('#selected').val();

    $.ajax({
        url: 'provmeterfunction.php?function=get_configureproductSites',
        type: 'post',
        data: 'site=' + sitename + '&pname=' + Pname + '&provcheck=' + provcheck + '&enablechk=' + enablecheck + '&meterchk=' + metercheck + '&pid=' + id +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        success: function (data) {
            if (data.msg == 'success') {
                $('.error').html('');
                $('#successmsg').html('<span style="color:green"> Configured successfully </span>');
            } else if (data.msg == 'failed') {
                $('.error').html('');
                $('#successmsg').html('<span style="color:red"> Configured failed </span>');
            }

            setTimeout(function () {
                location.reload();
            }, 3000);
        }

    })
}

function editproductValue(id, value, type) {

    if (id != '') {

        $.ajax({
            url: 'provmeterfunction.php?function=get_editproductValue',
            type: 'post',
            data: 'pid=' + id +"&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json',
            success: function (data) {
                $('#addProductpopup').modal('show');
                if (value == 'Edit Product') {
                    $('#editproductbutton').show();
                    $('#viewproductbutton').hide();
                    $('#addproductbutton').hide();
                } else if (value == 'View Product') {
                    $('#viewproductbutton').show();
                    $('#editproductbutton').hide();
                    $('#addproductbutton').hide();
                }

                $('#headingpopup').html('<h2 class="popup-title">' + value + '</h2>');
                $('#product_name').val(data.pname);
                $('#globalcheck').attr({checked: data.globchk, value: data.globval});
                $('#enable_default').attr({checked: data.enablechk, value: data.enableval});
                $('#meter_default').attr({checked: data.monitorchk, value: data.monitorval});

                if (type == 'view') {
                    $("#product_name").prop("readonly", true);
                    $("#globalcheck,#enable_default,#meter_default").attr('readonly', true);
                }

                if (data.mtxt1 != null) {
                    $('.MeterFileFirst').html(data.mtxt1);
                    $(".browse-file,.samplefile").hide();
                    $('.remove-file').show();
                }

                if (data.mtxt2 != null) {
                    $('.MeterFileSecond').html(data.mtxt2);
                    $(".browse-filefooter,.samplefilefooter").hide();
                    $('.remove-filefooter').show();
                }

                if (data.mtxt3 != null) {
                    $('.MeterFileThird').html(data.mtxt3);
                    $(".browse-fileclient,.samplefileclient").hide();
                    $('.remove-fileclient').show();
                }

                if (data.mtxt4 != null) {
                    $('.MeterFileFourth').html(data.mtxt4);
                    $(".browse-filemeter,.samplefilemeter").hide();
                    $('.remove-filemeter').show();
                }

                if (data.mtxt5 != null) {
                    $('.MeterFileFifth').html(data.mtxt5);
                    $(".browse-filemeter1,.samplefilemet").hide();
                    $('.remove-filemeter1').show();
                }

                if (data.ktxt1 != null) {
                    $('.keyFileSixth').html(data.ktxt1);
                    $(".browse-filekey,.samplefilekey").hide();
                    $('.remove-filekey').show();
                }

                if (data.ktxt2 != null) {
                    $('.keyFileSeventh').html(data.ktxt2);
                    $(".browse-filekey2,.samplefilekey1").hide();
                    $('.remove-filekey2').show();
                }

                if (data.ktxt3 != null) {
                    $('.keyFileEighth').html(data.ktxt3);
                    $(".browse-filekey3,.samplefilekey2").hide();
                    $('.remove-filekey3').show();
                }

                if (data.ktxt4 != null) {
                    $('.keyFileNinth').html(data.ktxt4);
                    $(".browse-filekey4,.samplefilekey3").hide();
                    $('.remove-filekey4').show();
                }

                if (data.ktxt5 != null) {
                    $('.keyFileTenth').html(data.ktxt5);
                    $(".browse-filekey5,.samplefilekey4").hide();
                    $('.remove-filekey5').show();
                }

            }
        })

    } else {
        $('#warning').modal('show');
    }

}
//hold for some time
function createproductSubmit() {
    var pname = $('#product_name').val();
    var Gcheck = $('input[name=globalcheck]:checked').val();
    var Echeck = $('input[name=enable_default]:checked').val();
    var Mcheck = $('input[name=meter_default]:checked').val();

    var meterfileFirst = $('input[name=fileuploader2]')[0].files[0];
    var meterfileSecond = $('input[name=fileuploader3]')[0].files[0];
    var meterfilethird = $('input[name=fileuploader4]')[0].files[0];
    var meterfileFourth = $('input[name=fileuploader5]')[0].files[0];
    var meterfileFifth = $('input[name=fileuploader6]')[0].files[0];

    var keyfilesixth = $('input[name=fileuploader7]')[0].files[0];
    var keyfileseventh = $('input[name=fileuploader8]')[0].files[0];
    var keyfileeighth = $('input[name=fileuploader9]')[0].files[0];
    var keyfilenineth = $('input[name=fileuploader10]')[0].files[0];
    var keyfiletenth = $('input[name=fileuploader11]')[0].files[0];

    if (meterfileFirst != undefined) {
        var Mfileone = $('input[name=fileuploader2]')[0].files[0].name;
    }

    if (meterfileSecond != undefined) {
        var Mfiletwo = $('input[name=fileuploader3]')[0].files[0].name;
    }

    if (meterfilethird != undefined) {
        var Mfilethree = $('input[name=fileuploader4]')[0].files[0].name;
    }

    if (meterfileFourth != undefined) {
        var Mfilefour = $('input[name=fileuploader5]')[0].files[0].name;
    }

    if (meterfileFifth != undefined) {
        var Mfilefive = $('input[name=fileuploader6]')[0].files[0].name;
    }

    if (keyfilesixth != undefined) {
        var Keyfileone = $('input[name=fileuploader7]')[0].files[0].name;
    }

    if (keyfileseventh != undefined) {
        var Keyfiletwo = $('input[name=fileuploader8]')[0].files[0].name;
    }

    if (keyfileeighth != undefined) {
        var Keyfilethree = $('input[name=fileuploader9]')[0].files[0].name;
    }

    if (keyfilenineth != undefined) {
        var Keyfilefour = $('input[name=fileuploader10]')[0].files[0].name;
    }

    if (keyfiletenth != undefined) {
        var Keyfilefive = $('input[name=fileuploader11]')[0].files[0].name;
    }
    if (pname != '' && (meterfileFirst != undefined || meterfileSecond != undefined || meterfilethird != undefined ||
            meterfileFourth != undefined || meterfileFifth != undefined) && (keyfilesixth != undefined || keyfileseventh != undefined ||
            keyfileeighth != undefined || keyfilenineth != undefined || keyfiletenth != undefined)) {
        $.ajax({
            url: 'provmeterfunction.php?function=get_addproductcheck',
            type: 'post',
            data: 'pname=' + pname +"&csrfMagicToken=" + csrfMagicToken,
            success: function (res) {
                
                if (res.trim() == 'EXIST') {
                    $('#errname').html('');
                    $('#errname').html('<span style="color:red;margin-left: 30%;">Product name already exists.</span>');
                    return false;
                } else {
                    var m_data = new FormData();
                    m_data.append('mtxt1', Mfileone);// meter 1
                    m_data.append('mtxt2', Mfiletwo);// meter 2
                    m_data.append('mtxt3', Mfilethree);// meter 3   
                    m_data.append('mtxt4', Mfilefour);// meter 4   
                    m_data.append('mtxt5', Mfilefive);// meter 5   
                    m_data.append('ktxt1', Keyfileone);// key 1
                    m_data.append('ktxt2', Keyfiletwo);// key 2   
                    m_data.append('ktxt3', Keyfilethree);// key 3   
                    m_data.append('ktxt4', Keyfilefour);// key 4   
                    m_data.append('ktxt5', Keyfilefive);// key 5   

                    m_data.append('pname', pname);
                    m_data.append('Gcheck', Gcheck);
                    m_data.append('Echeck', Echeck);
                    m_data.append('Mcheck', Mcheck);
                    m_data.append('csrfMagicToken', csrfMagicToken);
                    $.ajax({
                        url: 'provmeterfunction.php?function=get_addproductvalueSubmit',
                        type: 'post',
                        processData: false, // important
                        contentType: false, // important
                        data: m_data,
                        success: function (data) {
                            if (data.msg == 'valid') {
                                $('#errname').html('');
                                $('#errname').html('<span style="color:green;margin-left: 30%;">successfully submitted</span>');
                            } else if (data.msg == 'invalid') {
                                $('#errname').html('');
                                $('#errname').html('<span style="color:green;margin-left: 30%;">Not submitted</span>');
                            }

                            setTimeout(function () {
                                location.reload();
                            }, 3000);
                        }
                    })
                }
            }
        })
    } else {
        if (pname == '') {
        $('#errname').html('');
            $('#errname').html('<span style="color:red;margin-left: 30%;">Please enter product name </span>');
        } else if (meterfileFirst == undefined && meterfileSecond == undefined && meterfilethird == undefined &&
                meterfileFourth == undefined && meterfileFifth == undefined) {
            $('#errname').html('');
            $('#errname').html('<span style="color:red;margin-left: 30%;">Please upload atleast one meter file</span>');
        } else if (keyfilesixth == undefined && keyfileseventh == undefined &&
                keyfileeighth == undefined && keyfilenineth == undefined && keyfiletenth == undefined) {
            $('#errname').html('');
            $('#errname').html('<span style="color:red;margin-left: 30%;">Please upload atleast one key file</span>');
    }
}
}

function editproductSubmit() {
    var pid = $('#selected').val();
    var pname = $('#product_name').val();
    var Gcheck = $('input[name=globalcheck]:checked').val();
    var Echeck = $('input[name=enable_default]:checked').val();
    var Mcheck = $('input[name=meter_default]:checked').val();

    var meterfileFirst = $('input[name=fileuploader2]')[0].files[0];
    var meterfileSecond = $('input[name=fileuploader3]')[0].files[0];
    var meterfilethird = $('input[name=fileuploader4]')[0].files[0];
    var meterfileFourth = $('input[name=fileuploader5]')[0].files[0];
    var meterfileFifth = $('input[name=fileuploader6]')[0].files[0];

    var keyfilesixth = $('input[name=fileuploader7]')[0].files[0];
    var keyfileseventh = $('input[name=fileuploader8]')[0].files[0];
    var keyfileeighth = $('input[name=fileuploader9]')[0].files[0];
    var keyfilenineth = $('input[name=fileuploader10]')[0].files[0];
    var keyfiletenth = $('input[name=fileuploader11]')[0].files[0];

    if (meterfileFirst != undefined) {
        var Mfileone = $('input[name=fileuploader2]')[0].files[0].name;
    }

    if (meterfileSecond != undefined) {
        var Mfiletwo = $('input[name=fileuploader3]')[0].files[0].name;
    }

    if (meterfilethird != undefined) {
        var Mfilethree = $('input[name=fileuploader4]')[0].files[0].name;
    }

    if (meterfileFourth != undefined) {
        var Mfilefour = $('input[name=fileuploader5]')[0].files[0].name;
    }

    if (meterfileFifth != undefined) {
        var Mfilefive = $('input[name=fileuploader6]')[0].files[0].name;
    }

    if (keyfilesixth != undefined) {
        var Keyfileone = $('input[name=fileuploader7]')[0].files[0].name;
    }

    if (keyfileseventh != undefined) {
        var Keyfiletwo = $('input[name=fileuploader8]')[0].files[0].name;
    }

    if (keyfileeighth != undefined) {
        var Keyfilethree = $('input[name=fileuploader9]')[0].files[0].name;
    }

    if (keyfilenineth != undefined) {
        var Keyfilefour = $('input[name=fileuploader10]')[0].files[0].name;
    }

    if (keyfiletenth != undefined) {
        var Keyfilefive = $('input[name=fileuploader11]')[0].files[0].name;
    }

    if (pname != '') {

        $.ajax({
            url: 'provmeterfunction.php?function=get_editprdtvalueSubmit',
            type: 'post',
            data: 'pname=' + pname + '&pid=' + pid +"&csrfMagicToken=" + csrfMagicToken,
            success: function (data) {
                if ($.trim(data) === 'success') {
                    $('#errname').html('<span style="color:green;">Product edited successfully')
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
            }
            }
        })

    } else {
        $('#errname').html('');
        $('#errname').html('<span style="color:red;margin-left: 30%;"> Please enter product name </span>');
    }
}

function cancelReload() {
    location.reload();
}

jQuery(document).ready(function ($) {
    jQuery('#fileuploader2').change(function (ev) {
        if ($('#fileuploader2').val().split('\\').pop() != '') {
            $(".samplefile").hide();
            $(".browse-file").hide();
            $(".samplefile2").html($('#fileuploader2').val().split('\\').pop());
            $('.remove-file').show();
        }
    });
    jQuery('.remove-file').click(function (event) {
        $('.MeterFileFirst').hide();
        $(".samplefile").show();
        $(".browse-file").show();
        $(".samplefile2").html('');
        $(".samplefile2").show();
        $('#fileuploader2').val('');
        $('.remove-file').hide();

    });
});

jQuery(document).ready(function ($) {
    jQuery('#fileuploader3').change(function (ev) {
        if ($('#fileuploader3').val().split('\\').pop() != '') {
            $(".samplefilefooter").hide();
            $(".browse-filefooter").hide();
            $(".samplefilefooter2").html($('#fileuploader3').val().split('\\').pop());
            $('.remove-filefooter').show();
        }
    });
    jQuery('.remove-filefooter').click(function (event) {
        $('.MeterFileSecond').hide();
        $(".samplefilefooter").show();
        $(".browse-filefooter").show();
        $(".samplefilefooter2").html('');
        $('#fileuploader3').val('');
        $('.remove-filefooter').hide();

    });
});

jQuery(document).ready(function ($) {
    jQuery('#fileuploader4').change(function (ev) {
        if ($('#fileuploader4').val().split('\\').pop() != '') {
            $(".samplefileclient").hide();
            $(".browse-fileclient").hide();
            $(".samplefileclient3").html($('#fileuploader4').val().split('\\').pop());
            $('.remove-fileclient').show();
        }
    });
    jQuery('.remove-fileclient').click(function (event) {
        $('.MeterFileThird').hide();
        $(".samplefileclient").show();
        $(".browse-fileclient").show();
        $(".samplefileclient3").html('');
        $('#fileuploader4').val('');
        $('.remove-fileclient').hide();

    });
});

jQuery(document).ready(function ($) {
    jQuery('#fileuploader5').change(function (ev) {
        if ($('#fileuploader5').val().split('\\').pop() != '') {
            $(".samplefilemeter").hide();
            $(".browse-filemeter").hide();
            $(".samplefilemeter4").html($('#fileuploader5').val().split('\\').pop());
            $('.remove-filemeter').show();
        }
    });
    jQuery('.remove-filemeter').click(function (event) {
        $('.MeterFileFourth').hide();
        $(".samplefilemeter").show();
        $(".browse-filemeter").show();
        $(".samplefilemeter4").html('');
        $('#fileuploader5').val('');
        $('.remove-filemeter').hide();

    });
});

jQuery(document).ready(function ($) {
    jQuery('#fileuploader6').change(function (ev) {
        if ($('#fileuploader6').val().split('\\').pop() != '') {
            $(".samplefilemet").hide();
            $(".browse-filemeter1").hide();
            $(".samplefilemeter5").html($('#fileuploader6').val().split('\\').pop());
            $('.remove-filemeter1').show();
        }
    });
    jQuery('.remove-filemeter1').click(function (event) {
        $('.MeterFileFifth').hide();
        $(".samplefilemet").show();
        $(".browse-filemeter1").show();
        $(".samplefilemeter5").html('');
        $('#fileuploader6').val('');
        $('.remove-filemeter1').hide();

    });
});

jQuery(document).ready(function ($) {
    jQuery('#fileuploader7').change(function (ev) {
        if ($('#fileuploader7').val().split('\\').pop() != '') {
            $(".samplefilekey").hide();
            $(".browse-filekey").hide();
            $(".samplefilekey12").html($('#fileuploader7').val().split('\\').pop());
            $('.remove-filekey').show();
        }
    });
    jQuery('.remove-filekey').click(function (event) {
        $('.keyFileSixth').hide();
        $(".samplefilekey").show();
        $(".browse-filekey").show();
        $(".samplefilekey12").html('');
        $('#fileuploader7').val('');
        $('.remove-filekey').hide();

    });
});

jQuery(document).ready(function ($) {
    jQuery('#fileuploader8').change(function (ev) {
        if ($('#fileuploader8').val().split('\\').pop() != '') {
            $(".samplefilekey1").hide();
            $(".browse-filekey2").hide();
            $(".samplefilekey2").html($('#fileuploader8').val().split('\\').pop());
            $('.remove-filekey2').show();
        }
    });
    jQuery('.remove-filekey2').click(function (event) {
        $('.keyFileSeventh').hide();
        $(".samplefilekey1").show();
        $(".browse-filekey2").show();
        $(".samplefilekey2").html('');
        $('#fileuploader8').val('');
        $('.remove-filekey2').hide();

    });
});

jQuery(document).ready(function ($) {
    jQuery('#fileuploader9').change(function (ev) {
        if ($('#fileuploader9').val().split('\\').pop() != '') {
            $(".samplefilekey2").hide();
            $(".browse-filekey3").hide();
            $(".samplefilekey3").html($('#fileuploader9').val().split('\\').pop());
            $('.remove-filekey3').show();
        }
    });
    jQuery('.remove-filekey3').click(function (event) {
        $('.keyFileEighth').hide();
        $(".samplefilekey2").show();
        $(".browse-filekey3").show();
        $(".samplefilekey3").html('');
        $('#fileuploader9').val('');
        $('.remove-filekey3').hide();

    });
});

jQuery(document).ready(function ($) {
    jQuery('#fileuploader10').change(function (ev) {
        if ($('#fileuploader10').val().split('\\').pop() !== '') {
            $(".samplefilekey3").hide();
            $(".browse-filekey4").hide();
            $(".samplefilekey4").html($('#fileuploader10').val().split('\\').pop());
            $('.remove-filekey4').show();
        }
    });
    jQuery('.remove-filekey4').click(function (event) {
        $('.keyFileNinth').hide();
        $(".samplefilekey3").show();
        $(".browse-filekey4").show();
        $(".samplefilekey4").html('');
        $('#fileuploader10').val('');
        $('.remove-filekey4').hide();

    });
});

jQuery(document).ready(function ($) {
    jQuery('#fileuploader11').change(function (ev) {
        if ($('#fileuploader11').val().split('\\').pop() != '') {
            $(".samplefilekey4").hide();
            $(".browse-filekey5").hide();
            $(".samplefilekey5").html($('#fileuploader11').val().split('\\').pop());
            $('.remove-filekey5').show();
        }
    });
    jQuery('.remove-filekey5').click(function (event) {
        $('.keyFileTenth').hide();
        $(".samplefilekey4").show();
        $(".browse-filekey4").show();
        $(".samplefilekey5").html('');
        $('#fileuploader11').val('');
        $('.remove-filekey5').hide();

    });
});

