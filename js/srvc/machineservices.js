$(function() {
    MachineService();//service main grid function 
    MachineServiceGraph();//service graph function
});

/* services main grid function */
function MachineService() {
    $.ajax({
        url: "machinefunctions.php?function=get_machineList"+"&csrfMagicToken=" + csrfMagicToken,
        type: "POST",
        dataType: "json",
        success: function(gridData) {
            if (gridData == '') {
                $('#dartvalidation').show();
            }
            $(".se-pre-con").hide();
            $('#servicesDataList').DataTable().destroy();
            servicesTable = $('#servicesDataList').DataTable({
                scrollY: jQuery('#servicesDataList').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                responsive: true,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                columnDefs: [{className: "checkbox-btn", "targets": [0]},
//                             { "width": "30%", "targets": 1 },   
//                             { "width": "30%", "targets": 2 },   
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
    
    $('#servicesDataList').on('click', 'tr', function() {

        var rowID = servicesTable.row(this).data();
        var serviceuniq = rowID[7];
        $('#serviceuniq').val(serviceuniq);
        $('#censusuniq').val(rowID[8]);

        servicesTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });
    $("#machine_searchbox").keyup(function() {//machien service search code
        servicesTable.search(this.value).draw();
    });
}

function urlencode(str) {
    return escape(str).replace(/\+/g, '%2B').replace(/%20/g, '+').replace(/\*/g, '%2A').replace(/\//g, '%2F').replace(/@/g, '%40');
}

/* machine services graph fucntion */
function MachineServiceGraph() {
    var urlencodeaddress = '';
    var address = 'machinefunctions.php?function=get_machinegraphData';
    if (cid != '') {
        address = address + "&cid=" + cid +"&csrfMagicToken=" + csrfMagicToken;
        urlencodeaddress = urlencode(address);          
    }
            
    $.ajax({
        url:address,
        type:'post',
        dataType: "json",
        success:function(data) {            
            startDone = data[0].slice();
            startPending = data[1].slice();
            stopDone = data[2].slice();
            stopPending = data[3].slice();
            restartDone = data[4].slice();
            restartPending = data[5].slice();
            /* Graph */            
            Highcharts.chart('service-chart', {
                colors: ['#008f00', '#006600', '#ff6a6a', '#ff0000', '#ffb84d', '#ff9900'],
                chart: {
                    type: 'column',
                    spacingTop: 30
                },
                title: {
                    text: 'Action Performed',
                    y: 0,
                    style: {
                        color: '#444444',
                        fontSize: "24px"
                    }
                },
                xAxis: {
                    categories: ['23 Hours Ago', '22 Hours Ago', '21 Hours Ago', '20 Hours Ago', '19 Hours Ago', '18 Hours Ago', '17 Hours Ago', '16 Hours Ago', '15 Hours Ago', '14 Hours Ago', '13 Hours Ago', '12 Hours Ago', '11 Hours Ago', '10 Hours Ago', '9 Hours Ago', '8 Hours Ago', '7 Hours Ago', '6 Hours Ago', '5 Hours Ago', '4 Hours Ago', '3 Hours Ago', '2 Hours Ago', '1 Hours Ago', '0 Hours Ago'],
                    labels: {
                        style: {
                            color: '#48b2e4',
                            fontWeight: 'bold'
                        }
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: ''
                    },
                    stackLabels: {
                        enabled: true,
                        style: {
                            fontWeight: 'bold',
                            color: (Highcharts.theme && Highcharts.theme.textColor) || '#48b2e4'
                        }
                    }
                },
                legend: {
                    align: 'right',
                    x: -30,
                    verticalAlign: 'top',
                    y: 20,
                    floating: true,
                    backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
                    borderColor: '#CCC',
                    borderWidth: 1,
                    shadow: false,
                    itemStyle: {
                        color: '#48b2e4',
                    }
                },
                tooltip: {
                    headerFormat: '<b>{point.x}</b><br/>',
                    pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
                },
                plotOptions: {
                    column: {
                        stacking: 'normal',
                        dataLabels: {
                            enabled: true,
                            color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
                        }
                    }
                },
                series: [{
                        name: 'Start Action Pending',
                        data: startPending
                    }, {
                        name: 'Start Action Done',
                        data: startDone
                    }, {
                        name: 'Stop Action Pending',
                        data: stopPending
                    }, {
                        name: 'Stop Action Done',
                        data: stopDone
                    }, {
                        name: 'Restart Action Pending',
                        data: restartPending
                    }, {
                        name: 'Restart Action Done',
                        data: restartDone
                    }
                ]
            });
        },
        error:function(data){
        }        
    })    
}

/* start service function */
function startService() {

    if ($('#serviceuniq').val() != '') {
        
        showModal(2, $('#censusuniq').val(), $('#serviceuniq').val(), 1, 0, 2, 'start');
        
    } else {
        
        $('#servicemessage').html('<p style="color: black; margin-left: 9.2%;margin-top: 3%;">Please select the service to Start</p>');
        $('#startservicewarn').modal('show');
    }
} 

function stopService() {
    
    if ($('#serviceuniq').val() != '') {
        
        showModal(3, $('#censusuniq').val(), $('#serviceuniq').val(), 1, 0, 3, 'stop');
        
    } else {
        
        $('#servicemessage').html('<p style="color: black; margin-left: 9.2%;margin-top: 3%;">Please select the service to Stop</p>');
        $('#startservicewarn').modal('show');
        
    }    
}

function restartService() {
    
    if ($('#serviceuniq').val() != '') {
        
        showModal(4, $('#censusuniq').val(), $('#serviceuniq').val(), 1, 0, 4, 'restart');
        
    } else {
        
        $('#servicemessage').html('<p style="color: black; margin-left: 9.2%;margin-top: 3%;">Please select the service to Stop</p>');
        $('#startservicewarn').modal('show');
        
    } 
}

function advanceService() {
    
    if ($('#serviceuniq').val() != '') {
        
        showModal(5, $('#censusuniq').val(), $('#serviceuniq').val(), 1, 0, 5, 'advance');
        
    } else {
        
        $('#servicemessage').html('<p style="color: black; margin-left: 18.2%;margin-top: 3%;">Please select the service </p>');
        $('#startservicewarn').modal('show');
        
    }     
}

function showModal(snav_act, cid, sid, rp, page, clicked, serAction, confrimUpdate) {
    url = "snav_actions.php?function=AllService&snav_act=" + snav_act + "&cid=" + cid + "&sid=" + sid + "&rp=" + rp + "&page=" + page + "&click=" + clicked + "&action=" + serAction + "&confrimUpdate=" + confrimUpdate + "&id="+censusid+"&level="+level+"&site="+site+"&name="+machinename
    +"&csrfMagicToken=" + csrfMagicToken;
           
    $.ajax({
        url:url,
        type:"post",
        dataType:'json',
        success:function (data) {
            $('#mid').val(data.mid);//id
            $('#confirm').val(data.snavact);
            $('#cid').val(data.cid);
            $('#sid').val(data.sid);
            $('#rp').val(data.rp);
            $('#page').val(data.page);
            $('#mid').val(data.mid);
            $('#site').val(data.site);
            $('#level').val(data.level);
            $('#machinename').val(data.machinename);
            
            
            if (serAction == 'start') {
                $('#serviceheader').html('<h2>Start Service</h2>');
                $('#servicenotify').html('<p style="font-size: 14px !important;margin-left: 10px !important;">Are you sure you want to Start the Service?</p>');
                $('#startservicepopup').modal('show');
            } else if (serAction == 'stop') {
                $('#serviceheader').html('<h2>Stop Service</h2>');
                $('#servicenotify').html('<p style="font-size: 14px !important;margin-left: 10px !important;">Are you sure you want to Stop the Service?</p>');
                $('#startservicepopup').modal('show');
            } else if (serAction == 'restart') {
                $('#serviceheader').html('<h2>Restart Service</h2>');
                $('#servicenotify').html('<p style="font-size: 14px !important;margin-left: 10px !important;">Are you sure you want to Restart the Service?</p>');
                $('#startservicepopup').modal('show');
            } else if (serAction == 'advance') {                                        
                               
                 $.ajax({
                    url:'../srvc/advance.php',
                    type: 'GET',
//                    processData: false, // important
//                    contentType: false, // important
//                    data: m_data,
                    data:"snav_act=" + snav_act + "&cid=" + cid + "&sid=" + sid + "&rp=" + rp + "&page=" + page + "&click=" + clicked + "&action=" + serAction + "&confrimUpdate=" + confrimUpdate + "&id="+censusid+"&level="+level+"&site="+site+"&name="+machinename+"&csrfMagicToken=" + csrfMagicToken,
                    dataType:'json',
                    success:function(data) {
                        if (data.name == 'displayerror') {
                            $('#displayerrormsg').modal('show');
                            $('#errorMsgdisplay').html(data.machinename);
                            $('#maindisplayError').html('<p style="color: black; margin-left: 1.2%;margin-top: 3%;">An error has occurred processing this page..</p>');
                        } else if (data.name == 'success') {                            
                            $('#advance-config').modal('show');
                            $('#advanceservicecontent').html(data.html);
                        }
                    }                     
                 })
            }
        }
    });
}

function submittheform() {
    var snav_act = $('#snav_act').val();
    var confirm = $('#confirm').val();
    var sid = $('#sid').val();
    var cid = $('#cid').val();
    var gid = $('#gid').val();
    var page = $('#page').val();
    var rp = $('#rp').val();
    var mid = $('#mid').val();
    var site = $('#site').val();
    var level = $('#level').val();
    var Mname = $('#machinename').val();
    var confrimUpdate = $('#confrimUpdate').val();

    $.ajax({
        url: '../srvc/allservice.php',
        type: 'post',
        data: 'snav_act=' + snav_act + '&confirm=' + confirm + '&sid=' + sid + '&cid=' + cid + '&gid=' + gid + '&page=' + page + '&rp=' + rp + '&mid=' + mid + '&site=' + site + '&level=' + level + '&name=' + Mname + '&confrimUpdate=' + confrimUpdate+"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        success: function (data) {
            if (data.name == 'displayerror') {

                $('#displayerrormsg').modal('show');
                $('#errorMsgdisplay').html(data.machinename);
                $('#maindisplayError').html('<p style="color: black; margin-left: 1.2%;margin-top: 3%;">An error has occurred processing this page..</p>');

            } else if (data.name == 'displaymessage') {
                
                $('#showdisplaymessage').modal('show');
                $('#displaymessage').html(data.machinename);
                $('#maindisplaymessage').html('<p style="color: black; margin-left: 1.2%;margin-top: 3%;">Confirmation of actions to be taken on machine <b>' + data.machinename + '</b> </p>');
                
            }
        }
    })
}

function closethepopup() {
//    parent.location.href="index.php?id=<?php echo $mid; ?>&level=<?php echo $level; ?>&site=<?php echo $site; ?>&name=<?php echo $machine_name; ?>"
    location.reload();
}

function advanceUpdate() {    
    $('#advance-config').modal('hide');
    var mss = $('input[name=mss]:checked').val();
    var mst = $('input[name=mst]:checked').val();
    var snav_act = $('input[name=snav_act]').val();
    var sid = $('#serviceuniq').val();
    var cid = $('#censusuniq').val();
    var gid = $('#gid').val();
    var page = $('#page').val();
    var rp = '1';    
    $.ajax({
        url:'../srvc/updateadvance.php',
        type:'post',
        data:'id='+censusid+'&level='+level+'&site='+site+'&name='+machinename+'&mss='+mss+'&mst='+mst+'&snav_act='+snav_act+'&sid='+sid+'&cid='+cid+'&gid='+gid+'&page='+page+'&rp='+rp+"&csrfMagicToken=" + csrfMagicToken,
        dataType:'json',
        success:function(data) {            
            $('#advance-config').modal('show');
            $('#advanceservicecontent').html(data.html);
        }
    })    
}

function advanceReset() { 
    
    var resethtml = $('#advanceservicecontent').html();
    $('#advanceservicecontent').html(resethtml);   
}