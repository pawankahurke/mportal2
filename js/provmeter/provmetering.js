$(function () {
    provmeterReport();
    $('.error').html('');
})
var selected = '';
function provmeterReport() {
    $.ajax({
        url: "provmeterfunction.php?function=get_provmeterreportList"+"&csrfMagicToken=" + csrfMagicToken,
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
                bInfo:false,
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
        selected = rowID[5];
        //downloadReportFile (selected);    
        $('#selected').val(selected);

        groupTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });

    $("#provmeterreport_searchbox").keyup(function () {//group search code
        groupTable.search(this.value).draw();
    });
}
$('#viewreportMeter').on('click', function () {
    id = selected;
    //alert(id);
    //return false;
    if (id) {
        $.ajax({
            url: 'provmeterfunction.php?function=get_meterreportDisplay',
            type: 'post',
            data: 'id=' + id +"&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json',
            success: function (data) {
                //alert(123);
                $('#meterreportdisplay').modal('show');
                $('#rtitle').html(data.title);
                $('#creator').html(data.username);
                $('#rtype').html(data.reporttype);
                $('#totalby').html(data.totalby);
                $('#using').html(data.using);
                $('#startdate').html(data.created);
                $('#endate').html(data.expires);
                $('#filename').val(data.file);
            }
        })
    } else {
        $('#warning').modal('show');
    }
});

function createreportSubmit() {
    var regex = /^[a-zA-Z0-9- ]*$/;

    var reportname = $('#report_name').val();
    var timetouse = $('#time_to_use').val();
    var reporttype = $('#report_type').val();
    var totalby = $('#total_item').val();
    var withintotal = $('#total_by_item').val();
    var datefrom = $('#datefrom').val();
    var datato = $('#dateto').val();
    var infoportal = booleanvalue($('#infoprotalcheck').prop('checked'));
    var dispcheck = booleanvalue($('#disptimecheck').prop('checked'));
    var dispcntchk = booleanvalue($('#dispcountcheck').prop('checked'));

    var start = (new Date(datefrom).getTime());
    var to = (new Date(datato).getTime());


    if (reportname == '') {
        $('.error').html('');
        $('#errname').html('<span style="color:red;margin-left: 40%;text-align: center;">Please enter a report name</span>');
    } else if (!regex.test(reportname)) {
        $('.error').html('');
        $('#errname').html('<span style="color:red;margin-left: 32%;text-align: center;">Special characters not allowed in report name</span>');
    } else if (datefrom == '') {
        $('.error').html('');
        $('#errname').html('<span style="color:red;margin-left: 40%;text-align: center;">Please select start date</span>');
    } else if (datato == '') {
        $('.error').html('');
        $('#errname').html('<span style="color:red;margin-left: 40%;text-align: center;">Please select end date </span>');
    } else if (start > to) {
        $('.error').html('');
        $('#errname').html('<span style="color:red;margin-left: 5%;text-align: center;">Start date cannot be greater than End date </span>');
    } else {
        $('.error').html('');
        $.ajax({
            url: 'provmeterfunction.php?function=get_reportnameCheck',
            type: 'post',
            data: 'rptname=' + reportname +"&csrfMagicToken=" + csrfMagicToken,
            success: function (data) {
                if ($.trim(data) == 'ADD') {

                    $.ajax({
                        url: 'provmeterfunction.php?function=get_reportValueAdd',
                        data: 'rptname=' + reportname + '&timetouse=' + timetouse + '&reporttype=' + reporttype + '&totalby=' + totalby + '&withintotal=' + withintotal + '&datefrom=' + datefrom + '&datato=' + datato + '&infoportal=' + infoportal + '&dispcheck=' + dispcheck + '&dispcntchk=' + dispcntchk +"&csrfMagicToken=" + csrfMagicToken,
                        type: 'post',
                        dataType: 'text',
                        success: function (data) {
                            var dataRes = data.trim();
                            if (dataRes === 'success') {
                                $('.error').html('');
                                $('#errname').html('<span style="color:green;margin-left: 40.5%;">Report added successfully</span>');
                                    $('#addmeterReport').modal('hide');
                                    provmeterReport();
                            } else {
                                $('#errname').html('<span style="color:red;margin-left: 32%;">Some error occured </span>');
                            }
                        }
                    })

                } else if (data == 'EXIST') {

                    $('.error').html('');
                    $('#errname').html('Report name already exists');

                }
            }
        })
    }
}


function booleanvalue(val) {
    if (val == true) {
        return 1;
    } else {
        return 0;
    }
}

$(".date_format").datetimepicker({
    format: "mm/dd/yyyy hh:ii",
    autoclose: true,
    todayBtn: false,
    pickerPosition: "bottom-left",
    startDate: "2012-01-01 01:00",
    endDate: new Date(),
});

// function downloadReportFile(id) {
//     alert(id);
//     if(id){
//         $.ajax({
//             url:'provmeterfunction.php?function=get_meterreportDisplay',
//             type:'post',
//             data:'id='+id,
//             dataType:'json',
//             success:function(data) {
//                 $('#meterreportdisplay').modal('show');            
//                 $('#rtitle').html(data.title);
//                 $('#creator').html(data.username);
//                 //$('#rtype').html(data.reporttype);
//                 $('#rtype').html('Process');
//                 //$('#totalby').html(data.totalby);
//                 $('#totalby').html('Product');
//                 //$('#using').html(data.using);
//                 $('#using').html('Client Time');
//                 $('#startdate').html(data.created);
//                 $('#endate').html(data.expires);
//                 $('#filename').val(data.file);
//             }        
//         })
//     }else{
//         $('#warning').modal('show');
//     }
// }

function DownloadMeterxls() {
    var file = $('#filename').val();
    window.location.href = '../provmeter/files/' + file +"&csrfMagicToken=" + csrfMagicToken;
}

function Downloadinforxls(url) {
    window.location.href = '../provmeter/files/' + url +"&csrfMagicToken=" + csrfMagicToken;
}

$('#runsubmit').click(function () {
    var id = $('#selected').val();
    if (id != '') {
        $.ajax({
            url: 'provmeterfunction.php?function=submitInfoportalValue',
            type: 'post',
            data: 'id=' + id +"&csrfMagicToken=" + csrfMagicToken,
            success: function (data) {
                $('#informationportal').modal('show');
                $('#mainErrorinformation').html('<span>' + data + '</span>')
            }
        })
    } else {
        $('#warning').modal('show');
    }
})

$('#meterInforamtion').click(function () {
    $('#informationmodalshow').modal('show');
    inforamtionportalClick();
    $.ajax({
        url: "provmeterfunction.php?function=get_meterinfoPortalDetails"+"&csrfMagicToken=" + csrfMagicToken,
        type: "POST",
        dataType: "json",
        success: function (gridData) {
            $(".information-portal-popup .se-pre-con").hide();
            $('#groupeventDtl').DataTable().destroy();
            groupviewTable = $('#groupeventDtl').DataTable({
                scrollY: jQuery('#groupeventDtl').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                autoWidth: false,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo:false,
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
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                initComplete: function (settings, json) {
                },
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $(".information-portal-popup .se-pre-con").hide();
                    $('#groupeventDtl_filter').css({"margin-right": "4%", "margin-top": "-5%"});
                }
            });
        },
        error: function (msg) {

        }
    });
    $('#groupeventDtl').on('click', 'tr', function () {

        var rowID = groupviewTable.row(this).data();
        selectedDel = rowID[5];
        //alert(selected);
        //downloadReportFile (selected);    
        $('#selected').val(selected);

        groupviewTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });

    // $("#provmeterreport_searchbox").keyup(function () {//group search code
    //     groupTable.search(this.value).draw();
    // });
})

function inforamtionportalClick() {
    setTimeout(function () {
        $(".fullcolumn").click();
    }, 300);
}

function inforeportDelete() {
    var selectedDel = $('#informationmodalshow tbody tr.selected').attr('id');
    var checkedValues = selectedDel;
    alert(checkedValues);
    if((checkedValues === undefined) || (checkedValues === "undefined")){
         $('#informationmodalshow').modal('hide');
         $('#warning').modal('show');
    }else if ((checkedValues !== '') || (checkedValues !== 'undefined')) {
        $.ajax({
            url: 'provmeterfunction.php?function=get_meterreportDelete',
            type: 'post',
            data: 'id=' + checkedValues +"&csrfMagicToken=" + csrfMagicToken,
            success: function (data) {
                $('#informationmodalshow').modal('hide');
                $('#deleteSuccess').modal('show');
                provmeterReport();
            }
        });
    }
}