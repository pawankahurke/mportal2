
var handleChart1 = '';
var handleChart2 = '';
var handleChart3 = '';
var handleData1 = '';
var handleData2 = '';
itemtype = 5;
var count = 0;
$(document).ready(function() {
$('[data-toggle="tooltip"]').tooltip();
    $('#Createsite').tooltip({
        selector: "a[rel=tooltip]"
    })



    //This should work only for Trial Sign up SMB customers.
    if (bussLevel === 'Commercial') {
        getUsertrialSites();
    }

    $(window).on('load resize', function() {
        //drawStuff(compliance);
    });

    $(window).on('load resize', function() {
        //drawChart();
    });
    if (bussLevel === 'Consumer') {
        $('#comp_switch').prop('checked', true);
        $("#comp_switch").change();
    }
    $('#backhide_comp').hide();
    $('#backhide_noti').hide();
});

function walkthroughEstart() {
    $('body').pagewalkthrough({
        name: 'introduction',
        steps: [{
                wrapper: '',
                popup: {
                    content: '#walkthrough-1',
                    type: 'modal'
                }
            }, {
                wrapper: '.rightbar i',
                popup: {
                    content: '#walkthrough-2',
                    type: 'tooltip',
                    position: 'bottom'
                }
            }, {
                wrapper: '.links i',
                popup: {
                    content: '#walkthrough-3',
                    type: 'tooltip',
                    position: 'bottom'
                }
            }, {
                wrapper: '.search i',
                popup: {
                    content: '#walkthrough-4',
                    type: 'tooltip',
                    position: 'right'
                }
            }, {
                wrapper: '#nanoheal_logo_lm span',
                popup: {
                    content: '#walkthrough-5',
                    type: 'tooltip',
                    position: 'right'
                }
            }, {
                wrapper: '.user-menu img',
                popup: {
                    content: '#walkthrough-6',
                    type: 'tooltip',
                    position: 'bottom'
                }
            }]
    });

    // Show the tour
    $('body').pagewalkthrough('show');
}

var homepage = 'home';
var compliance;
var notification;
var priority = 1;
function drawChart(deviceTypesArray, resolutions) {
    $(".window,.mac,.android,.linux,.others,.proactive,.predictive").css("width", "0%");

    var totalDevice = parseInt(deviceTypesArray['Windows']) + parseInt(deviceTypesArray['MAC OS']) + parseInt(deviceTypesArray['Android']) + parseInt(deviceTypesArray['Linux']) + parseInt(deviceTypesArray['iOS']) + parseInt(deviceTypesArray['others']);
    $(".deviceTotal").html(totalDevice + " <span>Total</span>");
    $(".window").css("width", "" + (parseInt(deviceTypesArray['Windows']) / totalDevice) * 100 + "%");
    $(".mac").css("width", "" + (parseInt(deviceTypesArray['MAC OS']) / totalDevice) * 100 + "%");
    $(".android").css("width", "" + (parseInt(deviceTypesArray['Android']) / totalDevice) * 100 + "%");
    $(".linux").css("width", "" + (parseInt(deviceTypesArray['Linux']) / totalDevice) * 100 + "%");
    $(".iOS").css("width", "" + (parseInt(deviceTypesArray['iOS']) / totalDevice) * 100 + "%");
    $(".others").css("width", "" + (parseInt(deviceTypesArray['others']) / totalDevice) * 100 + "%");
    $(".windowCount").text(parseInt(deviceTypesArray['Windows']));
    $(".macCount").text(parseInt(deviceTypesArray['MAC OS']));
    $(".androidCount").text(parseInt(deviceTypesArray['Android']));
    $(".linuxCount").text(parseInt(deviceTypesArray['Linux']));
    $(".iOSCount").text(parseInt(deviceTypesArray['iOS']));
    $(".othersCount").text(parseInt(deviceTypesArray['others']));

    var totalResolutions = parseInt(resolutions[1]) + parseInt(resolutions[0]) + parseInt(resolutions[2]) + parseInt(resolutions[3]);
    $(".resolutionsTotal").html(totalResolutions + " <span>Total</span>");
    if(resolutions[1] == 0) {
         $(".proactive").css("width", "0%");
    } else {
    $(".proactive").css("width", "" + (parseInt(resolutions[1]) / totalResolutions) * 100 + "%");
    }
    if(resolutions[0] == 0) {
        $(".predictive").css("width", "0%");
    } else {
    $(".predictive").css("width", "" + (parseInt(resolutions[0]) / totalResolutions) * 100 + "%");
    }
    if(resolutions[2] == 0) {
        $(".selfhelp").css("width", "0%");
    } else {
    $(".selfhelp").css("width", "" + (parseInt(resolutions[2]) / totalResolutions) * 100 + "%");
    }
    if(resolutions[3] == 0) {
        $(".schedule").css("width", "0%");
    } else {
    $(".schedule").css("width", "" + (parseInt(resolutions[3]) / totalResolutions) * 100 + "%");
    }
    $(".proactiveCount").text(parseInt(resolutions[1]));
    $(".predictiveCount").text(parseInt(resolutions[0]));
    $(".selfhelpCount").text(parseInt(resolutions[2]));
    $(".scheduleCount").text(parseInt(resolutions[3]));
}

function drawStuff(complianceArray) {
    $(".compliance-loader").show();
     $(".compliance-trend").show();
    var date = new Array(); var ok = new Array(); var warning = new Array(); var alert = new Array();
    var finaldate = new Array(); var finalok = new Array(); var finalwarning = new Array(); var finalalert = new Array();
    $.each(complianceArray,function(key,val){
        if (key != 0) {
            date.push(val[0]);
            ok.push(val[1]);
            warning.push(val[2]);
            alert.push(val[3]);
        }
    });
    
    finaldate = date.slice();
    finalok = ok.slice();
    finalwarning = warning.slice();
    finalalert = alert.slice();
    Highcharts.chart('highchart-compliance', {
        chart: {
            type: 'column',
            events: {
                load: function(event) {
                    $(".compliance-loader").hide();
                }
            } 
        },
        colors: ['#209688', '#f9d47a', '#fb5b55'],
        title: {
            text: ''
        },
        xAxis: {
            categories: finaldate,
            crosshair: true,
            lineWidth: 1,
            lineColor: '#979797',
            labels: {
                style: {
                    fontSize: '10px',
                    color: '#5a5a5a'
                }
            }
        },
        yAxis: {
            min: 0,
            endOnTick: false,
            gridLineWidth: 0,
            lineWidth: 1,
            lineColor: '#979797',
            title: {
                text: ''
            },
            stackLabels: {
                enabled: false,
                style: {
                    fontWeight: 'bold',
                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                }
            }
        },
        legend: {
            enabled: false
        },
        tooltip: {
            headerFormat: '<b>{point.x}</b><br/>',
            pointFormat: '{series.name}: {point.y}'
        },
        plotOptions: {
            column: {
                stacking: 'normal',
                dataLabels: {
                    enabled: false,
                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
                },
                borderWidth: 0
            }
        },
        series: [{
                name: 'Ok',
                maxPointWidth: 10,
                data: finalok
            }, {
                name: 'Warning',
                maxPointWidth: 10,
                data: finalwarning
            }, {
                name: 'Alert',
                maxPointWidth: 10,
                data: finalalert
            }]
    });
}

function drawNotifStuff(notificationArray) {
    $(".compliance-loader").show();
     $(".compliance-trend").show();
    var date = new Array(); var noaction = new Array(); var others = new Array(); var fixed = new Array();
    var finaldate = new Array(); var finalnoaction = new Array(); var finalothers = new Array(); var finalfixed = new Array();
    $.each(notificationArray,function(key,val){
        if (key != 0) {
            date.push(val[0]);
            noaction.push(val[1]);
            others.push(val[2]);
            fixed.push(val[3]);
        }
    });
    
    finaldate = date.slice();
    finalnoaction = noaction.slice();
    finalothers = others.slice();
    finalfixed = fixed.slice();
    Highcharts.chart('highchart-notification', {
        chart: {
            type: 'column',
            events: {
                load: function(event) {
                    $(".compliance-loader").hide();
                }
            } 
        },
        colors: ['#209688', '#f9d47a', '#fb5b55'],
        title: {
            text: ''
        },
        xAxis: {
            categories: finaldate,
            crosshair: true,
            lineWidth: 1,
            lineColor: '#979797',
            labels: {
                style: {
                    fontSize: '10px',
                    color: '#5a5a5a'
                }
            }
        },
        yAxis: {
            min: 0,
            endOnTick: false,
            gridLineWidth: 0,
            lineWidth: 1,
            lineColor: '#979797',
            title: {
                text: ''
            },
            stackLabels: {
                enabled: false,
                style: {
                    fontWeight: 'bold',
                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                }
            }
        },
        legend: {
            enabled: false
        },
        tooltip: {
            headerFormat: '<b>{point.x}</b><br/>',
            pointFormat: '{series.name}: {point.y}'
        },
        plotOptions: {
            column: {
                stacking: 'normal',
                dataLabels: {
                    enabled: false,
                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
                },
                borderWidth: 0
            }
        },
        series: [
             {
                name: 'Fixed',
                maxPointWidth: 10,
                data: finalfixed
            },{
                name: 'Others',
                maxPointWidth: 10,
                data: finalothers
            },{
                name: 'No Action',
                maxPointWidth: 10,
                data: finalnoaction
            }]
    });
}




function notificationCoding(prio_val, id, priority) {
    if (prio_val > 50) {
        $('#' + id).html('<span>Priority ' + priority + ' <i class="red">' + prio_val + '%</i></span>');
    } else {
        $('#' + id).html('<span>Priority ' + priority + ' <i>' + prio_val + '%</i></span>');
    }
}

function ComplianceCoding(complianceData, id) {
    if (complianceData > 50) {
        $('#' + id).html('<span class="gray">' + complianceData + '%</span>');
    } else {
        $('#' + id).html('<span class="red">' + complianceData + '%</span>');
    }
}

function compliancePercIcon(compliancePerc, ComplianceChng, id) {

    if (ComplianceChng >= 0) {
        $('#' + id).html(compliancePerc + ' (<i class="icon-ic_call_made_24px material-icons"></i> ' + ComplianceChng + ')');
    } else {
        $('#' + id).html(compliancePerc + ' (<i class="icon-ic_call_received_24px material-icons"></i> ' + ComplianceChng + ')');
    }
}

function notificationPercIcon(compliancePerc, ComplianceChng, id) {

    if (ComplianceChng >= 0) {
        $('#' + id).html(compliancePerc + ' (<i class="icon-ic_call_made_24px material-icons"></i> ' + ComplianceChng + ')');
    } else {
        $('#' + id).html(compliancePerc + ' (<i class="icon-ic_call_received_24px material-icons"></i> ' + ComplianceChng + ')');
    }
}

function home_data() {
    google.charts.load("current", {packages: ["corechart", 'bar']});
    google.charts.setOnLoadCallback(load_home_data);
}

function load_home_data() {
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var elementName = $('#rparentName').val();
    var elementLabel = $('#searchLabel').val();
//    console.log($('#searchType').val());
    var deviceTypesArray = [];
    var resolutions = [];
    var complianceArray = [['', 'Ok', 'Warning', 'Alert']];
//    var weeklyPerc = 0;
$(".compliance-trend").hide();
 $(".compliance-loader").show();
    $.ajax({
        url: "../lib/l-ajax.php?function=AJAX_GetHomePageData",
        type: 'POST',
        data: 'searchType=' + searchType + '&searchValue=' + searchValue + '&itemtype=5',
        dataType: 'json',
        success: function(data) {


            $("#viewCount").attr("title", elementLabel);
            if (searchValue.length > 12) {
                $("#viewCount").html(elementLabel.substring(0, 13));
            } else {
                $("#viewCount").html('<span>' + elementLabel + '</span>');
            }

            $("#viewName").html('<span>' + searchType + ' Managed</span>');
            $('#totaldevicecount').html(data.deviceCount);
            $("#user_count").html(data.userCount);

        },
        error: function(err) {

        }
    });

    //If notification trend check box is checked, then call notification trend graph function to bring graph data.
    if ($("#comp_switch").is(":checked")) {
        getNotificationGraphData();
        getNotificationHoursTrend();
    }

}

function complianceGraph(itemType, obj) {
    $('#backhide_comp').show();
    itemtype = itemType;
    $(".compcat").removeClass("active");
    $(obj).addClass('active');
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var complianceArray = [['', 'Ok', 'Warning', 'Alert']];

    var URL = "../lib/l-ajax.php?function=AJAX_GetComplianceTrend&searchType=" + searchType + "&searchValue=" + searchValue + "&itemtype=" + itemType;

    $.ajax({
        url: URL,
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            var complianceGrpArr = data.complianceTrend;
            var dateGraphLabel = data.graphDataLabel;
            for (var i = 1; i <= complianceGrpArr.length; i++) {
                complianceArray[i] = [dateGraphLabel[i - 1], parseInt(complianceGrpArr[i - 1][0]), parseInt(complianceGrpArr[i - 1][2]), parseInt(complianceGrpArr[i - 1][1])];

            }
            compliance = complianceArray;
            drawStuff(complianceArray);
        },
        error: function(err) {

        }
    });
}



$('#itemtype_drop').find('ul li').click(function() {
    var itemsel = $(this).find('a').html();
    var caret = '&nbsp; <span class="caret"></span>';
    itemtype = $(this).find('a').attr('itemtype');
    $('#itemtype').html('<span>' + itemsel + '</span>' + caret);
    complianceGraph(itemtype);
});


function selectHandler1() {
    var selectedItem = handleChart1.getSelection()[0];
    var topping = handleData1.getValue(selectedItem.row, 0);
    if (topping.indexOf("Windows") > -1) {
        str = 'Windows';
    } else if (topping.indexOf("MAC") > -1) {
        str = 'MAC';
    } else if (topping.indexOf("Android") > -1) {
        str = 'Android';
    } else if (topping.indexOf("Linux") > -1) {
        str = 'Linux';
    } else if (topping.indexOf("iOS") > -1) {
        str = 'iOS';
    } else if (topping.indexOf("others") > -1) {
        str = 'others';
    }
    switch (str) {
        case 'Windows':
            if (role_id == "187" || role_id == 187) {
                return false;
            } else {
                location.href = 'site.php';
            }

            break;
        case 'MAC':
            if (role_id == "187" || role_id == 187) {
                return false;
            } else {
                location.href = 'site.php';
            }
            break;
        case 'Android':
            if (role_id == "187" || role_id == 187) {
                return false;
            } else {
                location.href = 'site.php';
            }
            break;
        case 'Linux':
            if (role_id == "187" || role_id == 187) {
                return false;
            } else {
                location.href = 'site.php';
            }
            break;
        case 'iOS':
            if (role_id == "187" || role_id == 187) {
                return false;
            } else {
                location.href = 'site.php';
            }
            break;
        case 'others':
            if (role_id == "187" || role_id == 187) {
                return false;
            } else {
                location.href = 'site.php';
            }
            break;
        default:
            location.href = 'javascript:;';
    }

}


function selectHandler2() {
    var selectedItem = handleChart2.getSelection()[0];
    var topping = handleData2.getValue(selectedItem.row, 0);
    if (topping.indexOf("Predictive") > -1) {
        str = 'Predictive';
    } else if (topping.indexOf("Proactive") > -1) {
        str = 'Proactive';
    }
    switch (str) {
        case 'Predictive':
            if (role_id == "187" || role_id == 187) {
                return false;
            } else {
                location.href = '../resolutions/predictive.php';
            }

//              location.href = '#';
            break;
        case 'Proactive':
            if (role_id == "187" || role_id == 187) {
                return false;
            } else {
                location.href = '../resolutions/proactive.php';
            }
            break;
        default:
            location.href = 'javascript:;';
    }

}

function selectHandler3() {
    var selectedItem = handleChart3.getSelection()[0];
    console.log(selectedItem);
    if (typeof selectedItem != 'undefined') {
        get_alert_warning_index(selectedItem.row);
    } else {
        get_alert_warning_index(14);
    }
}

function readyHandler() {
    handleChart3.setSelection([{row: 14, column: 1}]);
}

function get_alert_warning_index(day) {
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    $.ajax({
        url: "../lib/l-ajax.php?function=AJAX_GetAlertNWarn&searchType=" + searchType + "&searchValue=" + searchValue + "&itemtype=" + itemtype + "&day=" + day,
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            var activeAlerts = data.activeAlert;
            var activeWarning = data.activeWarning;
            $("#activeAlrts").html(activeAlerts);
            $("#activeWarns").html(activeWarning);
        },
        error: function(err) {

        }
    });
}

function naviagteToCompliance() {
    var stattype = 1;
    location.href = "compliance.php?itemType=" + itemtype + "&stattype=" + stattype;
}

// $(window).on('load resize',function(){
//     if($(window).height()<1000){
//         jQuery('.equalHeight2').matchHeight();
//     }

// });

function customerpop() {
    $(".error").html(" *");
    var sitename = $.trim($('#addNewSite').val());
    var trialSite = $('#trialSite').val();
    var aviraotc = '';
    var aviraemail = '';
    var compName = '';
    var status = 1;
    var avira_enabled = $('#aviraEnabled').val();
    var avira_pcno = 5;
    var error = 0;
    var pending = 0;
    var otcType = $('#otcType').val();
    var language = $('#lang').val();
    if (sitename === '') {
        $("#addNewSite_error").html('<span>Please Enter Customer Name</span>');
        error++;
    }
    
    if (sitename.indexOf("__") != -1) {
        $("#addNewSite_error").html('<span>More than one underscore not allowed</span>');
        error++;
    }
                    
    if (sitename !== '') {
        if (!avira_validate_Alphanumeric(aviraemail)) {
            $("#addNewSite_error").html('<span>Special character not allowed in Customer Name other than(-_.)</span>');
            error++;
        }
    }
    if (sitename !== '') {
        if (sitename.length > 25) {
            $("#addNewSite_error").html('<span>Customer Name allowed max 25 character</span>');
            error++;
        }
    }
    if (avira_enabled === 1 || avira_enabled === '1') {
        pending = $("#avira_pending_hidden").val();
        aviraotc = $.trim($('#aviraotc').val());
        aviraemail = $.trim($('#email').val());
        compName = $.trim($('#compName').val());
        avira_pcno = $('#avira_pcno').val();
        status = $('input[name=status]:checked').val();
        if (aviraotc === '') {
            $("#aviraotc_error").html('<span>Please Enter OTC Code</span>');
            error++;
        }
        if (aviraemail === '') {
            $("#email_error").html('<span>Please Enter Email</span>');
            error++;
        }
        if (aviraemail !== '') {
            if (!avira_validate_Email(aviraemail)) {
                $("#email_error").html('<span>Please Enter Valid Email Id</span>');
                error++;
            }
        }
        if (compName === '') {
            $("#compName_error").html('<span>Please Enter Company Name</span>');
            error++;
        }
        if (status === 1 || status === '1') {
            if (avira_pcno <= 0) {
                $("#avira_pcno_error").html('<span>Please Enter valid number of PC</span>');
                error++;
            } else if (avira_pcno > parseInt(pending)) {
                $("#avira_pcno_error").html('<span>Please Enter valid number of PC</span>');
                error++;
            }
        }
    }

    if (error === 0) {

        $("#addNewSite_error").html('*');
        $.ajax({
            url: "../customer/addCustomerModel.php?function=addSitename&sitename=" + sitename + "&trialSite=" + trialSite + "&aviraOtc=" + otcType + "&new_otc=" + aviraotc + "&new_email=" + aviraemail + "&new_compName=" + compName + "&status=" + status + "&pcCnt=" + avira_pcno + "&language="+language+"&defaultGateway=0",
            type: 'POST',
            dataType: 'json',
            success: function(response) {

                if (response.msg === 'Success') {
                    $('#Createsite').modal('hide');
                    $("#clickherelink").val(response.clientUrl);
                    $('#Createcustomer').modal('show');
                    $("#add_successMsg").html(' ' + sitename + ' <span>&nbsp;has been successfully created.</span>');
                    $("#custNo_val").html('<b>Customer ID:</b>' + response.link);
                } else {
                    $("#add_errorMsg").html(response.msg);
                }
            },
            error: function(err) {

            }
        });
    }
}

$("#addNewSite").blur(function() {
    var sitename = $.trim($('#addNewSite').val());
    if (sitename !== '') {
        $.ajax({
            url: "../lib/l-custAjax.php?function=CUSTAJX_IsSiteExist&siteName=" + sitename,
            type: 'POST',
            dataType: 'text',
            success: function(response) {
                if ($.trim(response) === "TRUE") {
                    $("#addNewSite_error").html("<span>Site name already exist</span>");
                } else {
                    $("#addNewSite_error").html("");
                }
            },
            error: function(err) {

            }
        });
    } else {
        $("#addNewSite_error").html("<span>Please enter site name</span>");
    }


});

function avira_createCustomer() {
    $(".error").html(" *");
    var sitename = $.trim($('#addNewSite').val());
    var trialSite = $('#trialSite').val();
    var aviraotc = '';
    var aviraemail = '';
    var compName = '';
    var lastname = '';
    var status = 1;
    var avira_enabled = $('#aviraEnabled').val();
    var avira_pcno = 5;
    var error = 0;
    var pending = 0;
    var otcType = $('#otcType').val();
    if (sitename == '') {
        $("#addNewSite_error").html('<span>Please Enter Customer Name</span>');
        error++;
    }else if (sitename.indexOf("__") != -1) {
        $("#addNewSite_error").html('<span>More than one underscore not allowed</span>');
        error++;
    }

    status = $('input[name=status]:checked').val();
    if (status === 0 || status === '0') {
        pending = $("#avira_pending_hidden").val();
        aviraotc = $.trim($('#aviraotc').val());
        aviraemail = $.trim($('#cust_email').val());
        compName = $.trim($('#cust_firstName').val());
        lastname = $.trim($("#cust_lastName").val());
        avira_pcno = $('#avira_pcno').val();
        if (aviraotc === '') {
            $("#aviraotc_error").html('<span>Please Enter OTC Code</span>');
            error++;
        }
        if (aviraemail === '') {
            $("#cust_email_error").html('<span>Please Enter Email</span>');
            error++;
        }
        if (aviraemail !== '') {
            if (!avira_validate_Email(aviraemail)) {
                $("#cust_email_error").html('<span>Please Enter Valid Email Id</span>');
                error++;
            }
        }
        if (compName === '') {
            $("#cust_firstName_error").html('<span>Please Enter First Name</span>');
            error++;
        }
        if (lastname === '') {
            $("#cust_lastName_error").html('<span>Please Enter Last Name</span>');
            error++;
        }

    } else if (status === 1 || status === '1') {
        pending = $("#avira_pending_hidden").val();
        aviraotc = $.trim($('#aviraotc').val());
        aviraemail = $.trim($('#email').val());
        compName = $.trim($('#compName').val());
        lastname = '';
        var successMessage = '';
        avira_pcno = $('#avira_pcno').val();
        if (avira_pcno <= 0) {
            $("#avira_pcno_error").html('<span>Please Enter valid number of PC</span>');
            error++;
        } else if (avira_pcno > parseInt(pending)) {
            $("#avira_pcno_error").html('<span>Please Enter valid number of PC</span>');
            error++;
        }
    }

    if (error === 0) {
        $("#add_errorMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        $("#addNewSite_error").html('*');
        $.ajax({
            url: "../customer/addCustomerModel.php?function=addSitename&sitename=" + sitename + "&trialSite=" + trialSite + "&aviraOtc=" + otcType + "&new_otc=" + aviraotc + "&new_email=" + aviraemail + "&new_compName=" + compName + "&status=" + status + "&pcCnt=" + avira_pcno,
            type: 'POST',
            dataType: 'json',
            success: function(response) {

                if (response.msg === 'Success') {
                    $('#createCustomerDiv').hide();
                    $("#clickherelink").val(response.clientUrl);
                    $("#downloadId").val(response.link);
                    //$('#Createcustomer').modal('show');
                    $("#aviraConfigureNext").show();
                    $("#aviraConfigureNextForGateway").hide();
                    $("#aviraConfigurePrevious").hide();
                    $('#avira_configureDiv').show();
                    if (status == 1) {
                        $("#clickHereDownLoad").show();
                        successMessage = sitename + '<span>&nbsp;has been successfully created</span>';
                    } else {
                        $("#clickHereDownLoad").hide();
                        successMessage = sitename + ' <span>&nbsp;has been successfully created.</span> ' + '<span>Email has been sent to given email address</span> ';
                    }
                    $("#custNo_val").html('<b>Customer ID:</b>' + response.link);
                    $("#add_successMsg").html(successMessage);

//                    $('#Createcustomer').modal('show');
                } else {
                    $("#add_errorMsg").html(response.msg);
                }
            },
            error: function(err) {

            }
        });
    }
}

$('input[type=radio][name=status]').change(function() {
    if (this.value == 1) {
        $(".myself_avira").show();
        $(".customer_avira").hide();
    } else if (this.value == 0) {
        $(".myself_avira").hide();
        $(".customer_avira").show();
    }
});

//$('#notify_switch').change(function() {
//        if($(this).is(":checked")) {
////            $("#home_notification").hide();
////            $("#home_compliance").show(); 
////            $("#comp_switch").prop("checked",false);
//        }
//        else{
//            $("#home_notification").hide();
//            $("#home_compliance").show(); 
//            drawStuff(compliance);
//            $("#comp_switch").prop("checked",false);
//            resetSlickSlider();
//        }
//});



//$('#comp_switch').change(function() {
//    if($(this).is(":checked")) {
//        $("#home_compliance").hide();  
//        $("#home_notification").show(); 
//        $("#notify_switch").prop("checked",true);
//        resetSlickSlider();
//    } 
//    if(bussLevel !== 'Consumer') {
//        getNotificationGraphData();
//        getNotificationHoursTrend();
//    }
//    
//});
//$('#notify_switch').click(function(){
//    $("#home_notification").hide();
//    $("#home_compliance").show(); 
//    drawStuff(compliance);
//    $("#comp_switch").prop("checked",false);
//    resetSlickSlider();
//    $(".compdiv").show();
//    $(".notdiv").hide();
//});
//
//$('#comp_switch').click(function(){
//    $("#home_compliance").hide();  
//    $("#home_notification").show(); 
//    resetSlickSlider();
//    $(".compdiv").hide();
//    $(".notdiv").show();
//    if(bussLevel !== 'Consumer') {
//        getNotificationGraphData();
//        getNotificationHoursTrend();
//    }
//});

$('#notify_switch').click(function() {
    $("#home_notification").hide();
    $("#home_compliance").show();
    drawStuff(compliance);
    resetSlickSlider();
});

$('#comp_switch').click(function() {
    $("#home_compliance").hide();
    $("#home_notification").show();
    resetSlickSlider();
    if (bussLevel !== 'Consumer') {
        getNotificationGraphData();
        getNotificationHoursTrend();
    }
});

function getUsertrialSites() {
    $.ajax({
        url: "../customer/addCustomerModel.php?function=checkTrialSite",
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            response = $.trim(response);
            if (response === 'Exist') {
                $('#Createsite').modal('hide');
                if (aviraFlow == 1) {
                    if (showAVTrial === 1 || showAVTrial == '1') {
                        $('#try-now-popup').modal('show');
                        $('#buy-now').hide();
                        $('#trial-flow-start').hide();
                    } else if (showAVTrialButton === 1 || showAVTrialButton == '1') {
                        $('#try-now-popup').modal('hide');
                        $('#buy-now').hide();
                        $('#trial-flow-start').show();
                    } else if (showAVBuy === 1 || showAVBuy == '1') {
                        $('#try-now-popup').modal('hide');
                        $('#buy-now').show();
                        $('#trial-flow-start').hide();
                    }
                } else {
load_home_data();
                }

             } else if(response === 'Nil'){
                 var searchType = $('#searchType').val();
                 parentClick(searchType,'All','All','All');
                 if(aviraFlow == 1){
                    $('#Createsite').modal('show');
                }
                load_home_data();
            }
        },
        error: function(err) {

        }
    });

}

function complianceItemShow(itemType, obj) {
return false;
    itemtype = itemType;
//    $(".compcat").removeClass("active")
//    $(obj).addClass('active');
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var complianceArray = [['', 'Ok', 'Warning', 'Alert']];

    var URL = "../lib/l-ajax.php?function=AJAX_GetComplianceHomeItems&searchType=" + searchType + "&searchValue=" + searchValue + "&itemtype=" + itemType;

    $.ajax({
        url: URL,
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            var compDeviation = data.compDeviation;
            var compItemNames = data.itemNames;
            var compItemIds = data.itemId;
            var compItemsStr = '';

            for (var i = 0; i < compDeviation.length; i++) {

                var compliancePerc24 = compDeviation[i]['percData24'];
                var ComplianceChng = compDeviation[i]['diffData'];
                var last24 = '';

                if (typeof (compliancePerc24) == 'undefined' || typeof (ComplianceChng) == 'undefined') {
                    compliancePerc24 = 0;
                    ComplianceChng = 0;
                    compDeviation[i]['percData1'] = 0;
                }

                if (ComplianceChng >= 0) {
                    last24 = compliancePerc24 + ' (<i class="icon-ic_call_made_24px material-icons"></i> ' + ComplianceChng + ')';
                } else {
                    last24 = compliancePerc24 + ' (<i class="icon-ic_call_received_24px material-icons"></i> ' + ComplianceChng + ')';
                }

                var last1 = compDeviation[i]['percData1'] + ' (<i class="icon-ic_call_made_24px material-icons"></i>0)';

                compItemsStr += '<li onclick="complianceGraphItem(' + itemType + ',' + compItemIds[i] + ',this)" class="compItem">'
                        + '<h2 title="' + compItemNames[i] + '">' + compItemNames[i] + '</h2>'
                        + '<div class="availability_hours">'
                        + '<h4>Last 1 hour</h4>'
                        + '<a href="javascript:;">' + last1 + '</a>'
                        + '</div>'
                        + '<div class="availability_hours">'
                        + '<h4>Last 24 hour</h4>'
                        + '<a href="javascript:;">' + last24 + '</a>'
                        + '</div>'
                        + '<div class="view-availability">'
                        + '<a id="backhide_comp" href="javascript:void(0);" onclick="backLevel()">Back</a>'
                        + '</div>'
                        + '</li>';
            }
//            $('.items ul').slick('removeSlide', null, null, true);
            $("#itemDetails").html(compItemsStr);
            $("#itemDetails").find("li").first().addClass('active');

            $(".category").hide();
            $(".items").show();
            resetSlickSlider();
        },
        error: function(err) {

        }
    });

}

function complianceGraphItem(itemType, itemid, obj) {
    itemtype = itemType
    $(".compItem").removeClass("active");
    $(obj).addClass('active');
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var complianceArray = [['', 'Ok', 'Warning', 'Alert']];

    var URL = "../lib/l-ajax.php?function=AJAX_GetComplianceTrend&searchType=" + searchType + "&searchValue=" + searchValue + "&itemtype=" + itemType + "&itemid" + itemid;

    $.ajax({
        url: URL,
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            var complianceGrpArr = data.complianceTrend;
            var dateGraphLabel = data.graphDataLabel;
            for (var i = 1; i <= complianceGrpArr.length; i++) {
                complianceArray[i] = [dateGraphLabel[i - 1], parseInt(complianceGrpArr[i - 1][0]), parseInt(complianceGrpArr[i - 1][2]), parseInt(complianceGrpArr[i - 1][1])];

            }
            compliance = complianceArray;
            console.log(complianceArray);
            drawStuff(complianceArray);
        },
        error: function(err) {

        }
    });
}

function backLevel() {

    $(".category").show();
    $(".items").hide();
    $('#backhide_comp').hide();
    resetSlickSlider();

}

// ##################### Notification Trend Graph Code Start ############################ //

/*
 * This function make ajax call and bring data in json format.
 * This json data will be used in notification trend graph.
 */
function getNotificationGraphData() {
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var notificationArray = [['', 'No Action', 'Others', 'Fixed']];
    $.ajax({
        url: "../notifications/notification.php?function=getNotificationTrendGraph&searchType=" + searchType + "&searchValue=" + searchValue + "&priority=" + priority,
        type: 'POST',
        dataType: 'json',
        success: function(result) {
            var notificationGrpArr = result.graphData;
            var dateGraphLabel = result.graphLables;

            for (var i = 1; i <= dateGraphLabel.length; i++) {
                notificationArray[i] = [dateGraphLabel[i - 1], parseInt(notificationGrpArr[dateGraphLabel[i - 1]]['noact']), parseInt(notificationGrpArr[dateGraphLabel[i - 1]]['other']), parseInt(notificationGrpArr[dateGraphLabel[i - 1]]['fixed'])];
            }
            notification = notificationArray;
            drawNotifStuff(notificationArray);

        },
        error: function(result) {
            console.log("Something went wrong");
        }
    });
}

//This function will get called on click of Notification Trend Priorities.
//Respective priority will come on click.
function notificationTrendGraph(priority, obj) {
    priority = priority;
    $(".notifPrioOpt").removeClass("active");
    $(obj).addClass('active');

    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var notificationArray = [['', 'No Action', 'Others', 'Fixed']];
    $.ajax({
        url: "../notifications/notification.php?function=getNotificationTrendGraph&searchType=" + searchType + "&searchValue=" + searchValue + "&priority=" + priority,
        type: 'POST',
        dataType: 'json',
        success: function(result) {
            var notificationGrpArr = result.graphData;
            var dateGraphLabel = result.graphLables;

            for (var i = 1; i <= dateGraphLabel.length; i++) {
                notificationArray[i] = [dateGraphLabel[i - 1], parseInt(notificationGrpArr[dateGraphLabel[i - 1]]['noact']), parseInt(notificationGrpArr[dateGraphLabel[i - 1]]['other']), parseInt(notificationGrpArr[dateGraphLabel[i - 1]]['fixed'])];
            }
            notification = notificationArray;
            drawNotifStuff(notificationArray);
        },
        error: function(result) {
            console.log("Something went wrong");
        }
    });
}

//This function will get called on click of Notification Trend Priorities.
//Respective priority will come on click.
function notificationDetailTrendGraph(pri, nid, obj) {
    priority = pri;
    $(".compItem").removeClass("active");
    $(obj).addClass('active');

    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var notificationArray = [['', 'No Action', 'Others', 'Fixed']];
    $.ajax({
        url: "../notifications/notification.php?function=getNotificationTrendGraph&searchType=" + searchType + "&searchValue=" + searchValue + "&priority=" + pri + "&nid=" + nid,
        type: 'POST',
        dataType: 'json',
        success: function(result) {
            var notificationGrpArr = result.graphData;
            var dateGraphLabel = result.graphLables;

            for (var i = 1; i <= dateGraphLabel.length; i++) {
                notificationArray[i] = [dateGraphLabel[i - 1], parseInt(notificationGrpArr[dateGraphLabel[i - 1]]['noact']), parseInt(notificationGrpArr[dateGraphLabel[i - 1]]['other']), parseInt(notificationGrpArr[dateGraphLabel[i - 1]]['fixed'])];
            }
            notification = notificationArray;
            drawNotifStuff(notificationArray);
        },
        error: function(result) {
            console.log("Something went wrong");
        }
    });
}

/*
 * This function make ajax call and bring data in json format.
 * This json data will be used in notification trend data for 1 hour and 24 hours.
 */
function getNotificationHoursTrend() {
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    $.ajax({
        url: "../notifications/notification.php?function=getNotificationHoursTrend&searchType=" + searchType + "&searchValue=" + searchValue,
        type: 'POST',
        dataType: 'json',
        success: function(result) {
            //Placing data for 24 hours
            notificationPercIcon(result[1]['24Hrs']['24hrs'], result[1]['24Hrs']['24hrsDiff'], 'prioOnePerc24');
            notificationPercIcon(result[2]['24Hrs']['24hrs'], result[2]['24Hrs']['24hrsDiff'], 'prioTwoPerc24');
            notificationPercIcon(result[3]['24Hrs']['24hrs'], result[3]['24Hrs']['24hrsDiff'], 'prioThreePerc24');
            notificationPercIcon(result[4]['24Hrs']['24hrs'], result[4]['24Hrs']['24hrsDiff'], 'prioFourPerc24');
            notificationPercIcon(result[5]['24Hrs']['24hrs'], result[5]['24Hrs']['24hrsDiff'], 'prioFivePerc24');

            //Placing data for 1 hour
            notificationPercIcon(result[1]['1Hr']['1hr'], result[1]['1Hr']['1hrDiff'], 'prioOnePerc1');
            notificationPercIcon(result[2]['1Hr']['1hr'], result[2]['1Hr']['1hrDiff'], 'prioTwoPerc1');
            notificationPercIcon(result[3]['1Hr']['1hr'], result[3]['1Hr']['1hrDiff'], 'prioThreePerc1');
            notificationPercIcon(result[4]['1Hr']['1hr'], result[4]['1Hr']['1hrDiff'], 'prioFourPerc1');
            notificationPercIcon(result[5]['1Hr']['1hr'], result[5]['1Hr']['1hrDiff'], 'prioFivePerc1');

        },
        error: function(result) {
            console.log("Something went wrong");
        }
    });
}

function notificationItemShow(prio, obj) {
return false;
    priority = prio;
    $('#backhide_noti').show();
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();

    var URL = "../notifications/notification.php?function=getNotificationDetailTrends&searchType=" + searchType + "&searchValue=" + searchValue + "&priority=" + prio;
    $.ajax({
        url: URL,
        type: 'POST',
        dataType: 'json',
        success: function(notifResult) {
            if (notifResult['status'] == 2 || notifResult['status'] == '2') {
                return false;
            } else {
                $(".notifitems_sub").show();
                $('.notifPriority').hide();
                var length = Object.keys(notifResult.data).length;
                var notifItemsStr = '';
                for (var key in notifResult.data) {
                    if (notifResult.data.hasOwnProperty(key)) {
                        var nid = key;
                        var nidPerc24 = notifResult.data[key]['24Hrs']['24hrs'];
                        var nidName = notifResult.data[key].name;
                        var ComplianceChng = 1;

                        var last24 = '';
                        var last1 = '';

                        if (ComplianceChng >= 0) {
                            last24 = nidPerc24 + ' (<i class="icon-ic_call_made_24px material-icons"></i> ' + notifResult.data[key]['24Hrs']['24hrsDiff'] + ')';
                        } else {
                            last24 = nidPerc24 + ' (<i class="icon-ic_call_received_24px material-icons"></i> ' + notifResult.data[key]['24Hrs']['24hrsDiff'] + ')';
                        }

                        last1 = notifResult.data[key]['1Hr']['1hr'] + ' (<i class="icon-ic_call_made_24px material-icons"></i>' + notifResult.data[key]['1Hr']['1hrDiff'] + ')';

                        notifItemsStr += '<li onclick="notificationDetailTrendGraph(' + priority + ',' + nid + ',this)" class="compItem">'
                                + '<h2 title="' + nidName + '">' + nidName + '</h2>'
                                + '<div class="availability_hours">'
                                + '<h4>Last 1 hour</h4>'
                                + '<a href="javascript:;">' + last1 + '</a>'
                                + '</div>'
                                + '<div class="availability_hours">'
                                + '<h4>Last 24 hour</h4>'
                                + '<a href="javascript:;">' + last24 + '</a>'
                                + '</div>'
                                + '<div class="view-availability">'
                                + '<a id="backhide_noti" href="javascript:void(0);" onclick="backNotifLevel();">Back</a>'
                                + '</div>'
                                + '</li>';
                    }
                }
//                $('.notifitems_sub .compliance-availability-slider ul').slick('removeSlide', null, null, true);
                $("#notifItemDetails").html(notifItemsStr);
                $("#notifItemDetails").find("li").first().addClass('active');
                $("#notifItemDetails").find("li").first().click();
                $(".notifPriority").hide();
                //$(".left").hide();
                $(".notifitems").show();
                resetSlickSlider();
            }

        },
        error: function(err) {
            console.log("Something went wrong");
        }
    });

}

function backNotifLevel() {
    $(".notifPriority").show();
    $(".notifitems_sub").hide();
    $("notifitems").show();
    $('#backhide_noti').hide();
    resetSlickSlider();
    getNotificationGraphData();
}

function resetSlickSlider() {
    $('.compliance-availability-slider ul').slick('unslick');
    $('.compliance-availability-slider ul').slick({
        slidesToShow: 5,
        slidesToScroll: 1,
        speed: 700,
        adaptiveHeight: true,
        dots: false,
        swipe: false,
        arrows: true,
        autoplay: false,
        centerMode: false,
        focusOnSelect: false,
        fade: false,
        infinite: false,
        responsive: [
            {
                breakpoint: 1500,
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 1200,
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 640,
                settings: {
                    slidesToShow: 1
                }
            }
        ]
    });
}




// ##################### Notification Trend Graph Code End ############################ //



$("#clickhereokbutton").click(function() {
    location.reload();
});




// ########################################################################################################################### //
// ########################################## Customer Creation/Provisioning Code Start ###################################### //
// ########################################################################################################################### //

function avira_validate_Email(email) {
    var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
    if (filter.test(email)) {
        return true;
        }
    else {
        return false;
}
}
function avira_validate_Alphanumeric(value)
{
    var regExp = /^[a-zA-Z0-9-._\-\s]+$/;
    if (value.match(regExp)) {

        return true;
        }
    else
    {
        return false;
    }
}

//To open download page in new tab when click on "click here to download"
function downloadClientUrl() {
    var url = $("#clickherelink").val();
    window.open(url, '_blank');
}

$('#Createsite').on('hidden.bs.modal', function() {
    $("#addNewSite_error").html("*");
    $("#cust_email").val('');
    $("#cust_firstName").val('');
    $("#cust_lastName").val('');
    $("#aviraotc").val('');
    $("#addNewSite").val('');

    $("#cust_email").prop('disabled', false);
    $("#cust_firstName").prop('disabled', false);
    $("#cust_lastName").prop('disabled', false);
    $("#aviraotc").prop('disabled', false);
    $("#addNewSite").prop('disabled', false);
});

//This function will get called when otc will get verified successfully.
//This function will change button hide show on the basis of gateway configuration checkbox.
function triggerConfiureGatewayCheckEvent() {
    $('#confiure_gateway').change(function() {
        if ($(this).is(":checked")) {
            $("#avira_next").hide();
            $("#createCustomerNext").show();
        } else {
            $("#avira_next").show();
            $("#createCustomerNext").hide();
        }

    });
}

$("#createCustomerNext").click(function() {
    $(".error").html(" *");
    var sitename = $.trim($('#addNewSite').val());
    var aviraotc = '';
    var aviraemail = '';
    var compName = '';
    var lastname = '';
    var status = 1;
    var avira_pcno = 5;
    var error = 0;
    var pending = 0;
    if (sitename == '') {
        $("#addNewSite_error").html('<span>Please Enter Customer Name</span>');
        error++;
    }
    if (sitename.indexOf("__") != -1) {
        $("#addNewSite_error").html('<span>More than one underscore not allowed</span>');
        error++;
    }else if(!avira_validate_Alphanumeric(sitename)){
        $("#addNewSite_error").html('<span>Special character not allowed in Customer Name other than(-_.)</span>');
        error++;
    }

    status = $('input[name=status]:checked').val();
    if (status === 0 || status === '0') {
        pending = $("#avira_pending_hidden").val();
        aviraotc = $.trim($('#aviraotc').val());
        aviraemail = $.trim($('#cust_email').val());
        compName = $.trim($('#cust_firstName').val());
        lastname = $.trim($("#cust_lastName").val());
        avira_pcno = $('#avira_pcno').val();
        if (aviraotc === '') {
            $("#aviraotc_error").html('<span>Please Enter OTC Code</span>');
            error++;
        }
        if (aviraemail === '') {
            $("#cust_email_error").html('<span>Please Enter Email</span>');
            error++;
        }
        if (aviraemail !== '') {
            if (!avira_validate_Email(aviraemail)) {
                $("#cust_email_error").html('<span>Please Enter Valid Email Id</span>');
                error++;
            }
        }
        if (compName === '') {
            $("#cust_firstName_error").html('<span>Please Enter First Name</span>');
            error++;
        }
        if (lastname === '') {
            $("#cust_lastName_error").html('<span>Please Enter Last Name</span>');
            error++;
        }

    } else if (status === 1 || status === '1') {
        pending = $("#avira_pending_hidden").val();
        aviraotc = $.trim($('#aviraotc').val());
        aviraemail = $.trim($('#email').val());
        compName = $.trim($('#compName').val());
        lastname = '';
        avira_pcno = $('#avira_pcno').val();
        if (avira_pcno <= 0) {
            $("#avira_pcno_error").html('<span>Please Enter valid number of PC</span>');
            error++;
        } else if (avira_pcno > parseInt(pending)) {
            $("#avira_pcno_error").html('<span>Please Enter valid number of PC</span>');
            error++;
        }
    }

    if (error === 0) {
        $('#createCustomerDiv').hide();
        $('#avira_configureDiv').show();
        $('#avira_gatewayDiv').hide();
    }
});

$("#gatewayInfoPrevious").click(function(){
   $('#createCustomerDiv').hide();
   $('#avira_configureDiv').show();
   $('#avira_gatewayDiv').hide();
});

$("#aviraConfigureNextForGateway").click(function() {
    $('#createCustomerDiv').hide();
    $('#avira_configureDiv').hide();
    $('#avira_gatewayDiv').show();
})

$("#aviraConfigurePrevious").click(function(){
    $('#createCustomerDiv').show();
    $('#avira_configureDiv').hide();
    $('#avira_gatewayDiv').hide();
});

$("#gatewayInfoPrevious").click(function(){
    $('#createCustomerDiv').hide();
    $('#avira_configureDiv').show();
    $('#avira_gatewayDiv').hide();
});

$("#addCutomerWithGatewayInfo").click(function() {
    var sitename  = $.trim($('#addNewSite').val());
    var trialSite = $('#trialSite').val();
    var otcType   = $('#otcType').val();
    var aviraotc  = $.trim($('#aviraotc').val());
    var aviraemail= '';
    
    var status      = '';
    var compName    = '';
    var avira_pcno  = 5;
    status = $('input[name=status]:checked').val();
    var validateGatewayInfo1 = validateGatewayInfo();

    if (status === 0 || status === '0') {
        aviraemail = $.trim($('#cust_email').val());
        compName = $.trim($('#cust_firstName').val());
        avira_pcno = $('#avira_pcno').val();
    } else if (status === 1 || status === '1') {
        aviraemail = $.trim($('#email').val());
        compName = $.trim($('#compName').val());
        avira_pcno = $('#avira_pcno').val();
    }

    if (validateGatewayInfo1 == 0) {
        $("#gateModalLoader").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        createCustomerOfGatewayInfo(sitename, trialSite, otcType, aviraotc, aviraemail, compName, status, avira_pcno);
    }
});


function validateGatewayInfo() {
    var isReqFieldsFilled = 0;
    $('.error').html("*");
    $('.gatewayReq').each(function() {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();

        if ($.trim(field_value) === "") {
            $("#gatewayReq_" + field_id).html(" <span>required</span>");
            isReqFieldsFilled++;
        }
    });
    return isReqFieldsFilled;
}

function createCustomerOfGatewayInfo(sitename, trialSite, otcType, aviraotc, aviraemail, compName, status, avira_pcno) {
    var successMessage = '';
    var GatewayMachine  = $("#gatewayHostName").val();
    var GatewayHost     = $("#gatewayHostName").val();
    var GatewayIP       = $("#gatewayIPAddress").val();
    var GatewayPort     = $("#gatewayPort").val();
    var GatewayDomain   = $("#gatewayDomain").val();
    var GatewayUN       = $("#gatewayUsername").val();
    var GatewayPassword = $("#gatewayPassword").val();
    var GatewayMachine  = $("#gatewayHostName").val();
    
    var params = "../customer/addCustomerModel.php?function=addSitename&sitename=" + sitename + "&trialSite=" + trialSite 
            + "&aviraOtc=" + otcType + "&new_otc=" + aviraotc + "&new_email=" + aviraemail + "&new_compName=" + compName 
            + "&status=" + status + "&pcCnt=" + avira_pcno + "&defaultGateway=1&GatewayMachine=" + GatewayMachine 
            + "&GatewayHost=" + GatewayHost + "&GatewayIP=" + GatewayIP + "&GatewayPort=" + GatewayPort 
            + "&GatewayDomain=" + GatewayDomain + "&GatewayUN=" + GatewayUN 
            + "&GatewayPassword=" + GatewayPassword + "&GatewayMachine=" + GatewayMachine;
    $.ajax({
        url: params,
        type: 'POST',
        dataType: 'json',
        success: function(response) {

            if (response.msg === 'Success') {
                $("#gateModalLoader").html('');
                $('#createCustomerDiv').hide();
                $("#clickherelink").val(response.clientUrl);
                $("#downloadId").val(response.link);
                var downloadId = response.link;
                var unistallModule = $("input[name='avira_unistall']:checked").val()
                var allModules = [];
                $('.confiure_avira:checked').each(function() {
                    allModules.push($(this).val());
                });
                var configureAvira = configureAviraForDownloadId(downloadId, allModules, unistallModule);
                if (status == 1) {
                    $("#clickHereDownLoad").show();
                    successMessage = sitename + '<span>&nbsp;has been successfully created</span>';
                } else {
                    $("#clickHereDownLoad").hide();
                    successMessage = sitename + ' <span>&nbsp;has been successfully created.</span> ' + '<span>Email has been sent to given email address</span> ';
                }
                $("#custNo_val").html('<b>Customer ID:</b>' + response.link);
                $("#add_successMsg").html(successMessage);
//                var defaultGateway = configureDefaultGateway(downloadId);
            } else {
                $("#gatewayPassword").html(response.msg);
            }
        },
        error: function(err) {

        }
    });
}



/**
 * This function will get called on successfully creation of customer.
 * This functin will get called when submit button clicked on Gateway module.
 * downloadId is uniq id which will get assigned to every customer after succesfull creation.
 * aviraModules are all checked modules which has been selected on Avira configure pop up.
 * aviraUnistall is value whether previous installed modules need to unistall or not.
 */
function configureAviraForDownloadId(downloadId, aviraModules, aviraUnistall) {
    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_changeAviraConfiguration&downloadId=" + downloadId + "&unistallModule=" + aviraUnistall + "&allModules=" + aviraModules,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            if ($.trim(response) === 'success') {
                $('#avira_configureDiv').hide();
                $('#avira_gatewayDiv').show();
                $('#Createsite').modal('hide');
                $('#Createcustomer').modal('show');
            } else {
                $("#gatewayPassword").html('Error occurred, please try after some time');
            }
        },
        error: function(err) {
            console.log("Some error occurred in configureAviraForDownloadId function -->".err);
        }
    });
    return true;
}

function configureDefaultGateway(downloadId) {
    var GatewayMachine  = $("#gatewayHostName").val();
    var GatewayHost     = $("#gatewayHostName").val();
    var GatewayIP       = $("#gatewayIPAddress").val();
    var GatewayPort     = $("#gatewayPort").val();
    var GatewayDomain   = $("#gatewayDomain").val();
    var GatewayUN       = $("#gatewayUsername").val();
    var GatewayPassword = $("#gatewayPassword").val();
    var params = "&GatewayMachine=" + GatewayMachine + "&GatewayHost=" + GatewayHost + "&GatewayIP=" + GatewayIP + "&GatewayPort=" + GatewayPort + "&GatewayDomain=" + GatewayDomain + "&GatewayUN=" + GatewayUN + "&GatewayPassword=" + GatewayPassword;

    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_SetDefaultGateway&downloadId=" + downloadId + params,
        type: 'POST',
        dataType: 'text',
        date:params,
        success: function(response) {
            if(response.status == "SUCCESS"){
                $("#gateModalLoader").html('');
                $("#gatewayPassword").html('');
                $('#avira_configureDiv').hide();
                $('#avira_gatewayDiv').hide();
                $('#Createsite').modal('hide');
                $('#Createcustomer').modal('show');
            }else{
                $("#gateModalLoader").html('');
                $("#gatewayPassword").html('some error occurred');
            }

        },
        error: function(err) {
            console.log("Some error occurred in configureAviraForDownloadId function -->".err);
}
    });
    return true;
}


$('.confiure_avira').on('change', function() {
    if ($('.confiure_avira:checked').length == $('.confiure_avira').length) {
        $('#confiure_avira_all').prop("checked", true);
    } else {
        $('#confiure_avira_all').prop("checked", false);
    }
});

$('#confiure_avira_all').click(function() {
    if ($(this).is(':checked')) {
        $('.confiure_avira').prop("checked", true);
    } else {
        $('.confiure_avira').prop("checked", false);
    }
});

function configureAviraInstall() {
    var downloadId = $("#downloadId").val();
    var unistallModule = $("input[name='avira_unistall']:checked").val()
    var allModules = [];
    $('.confiure_avira:checked').each(function() {
        allModules.push($(this).val());
    });

    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_changeAviraConfiguration&downloadId=" + downloadId + "&unistallModule=" + unistallModule + "&allModules=" + allModules,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            if ($.trim(response) === 'success') {
                $('#Createsite').modal('hide');
                $('#avira_configureDiv').show();
                $('#Createcustomer').modal('show');
            } else {
                //$('#Createcustomer').modal('hide');
    }
        },
        error: function(err) {

    }
    });
}

function configureAviraInstallPrevious(){
   $('#avira_configureDiv').show();
   $('#Createsite').modal('show'); 
    }


function doNotShowTrialPopUp(element) {
    var isChecked = element.checked;

    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_DoNotShowTrialPop&isChecked=" + isChecked,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            if (isChecked) {
                //location.reload();

                $('#try-now-popup').modal('hide');
                $('#buy-now').hide();
                $('#trial-flow-start').show();

    }
        },
        error: function(err) {

}
    });
}

function startFreeTrial() {
    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_StartTrial",
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            $('#try-now-popup').modal('hide');
            $("#trialStartedMsg").html("<span>Your trial period has been started, it is valid upto </span>" + response + "<span>. Please click on Ok button to relogin</span>");
            $('#trialStarted').modal('show');
        },
        error: function(err) {

        }
    });

}

function verifyOTC() {
    $(".error").html(" *");
    var sitename = $.trim($('#addNewSite').val());
    var otcCode = $.trim($("#aviraotc").val());

    var status_val = $.trim($('input[name=status]:checked').val());
    var error = 0;

    if (sitename === '') {
        $("#addNewSite_error").html('<span>Please Enter Customer Name</span>');
        error++;
    }

    if (otcCode === '') {
        $("#aviraotc_error").html('<span>Please Enter OTC Code</span>');
        error++;
    }

    if (status_val === 0) {
        var email = $.trim($("#cust_email").val());
        var compName = $.trim($("#cust_firstName").val());
        var lastName = $.trim($("#cust_lastName").val());
        if (compName === '') {
            $("#cust_firstName_error").html('<span>Please Enter First name.</span>');
            error++;
        }
        if (email === '') {
            $("#aviraotc_error").html('<span>Please Enter Customer email.</span>');
            error++;
        }
        if (lastName === '') {
            $("#cust_lastName_error").html('<span>Please Enter Last name.</span>');
            error++;
        }
    } else {
        var email = $.trim($("#email").val());
        var compName = $.trim($("#compName").val());
    }

    if (error === 0) {

        $("#add_errorMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        $.ajax({
            url: "../lib/l-custAjax.php?function=CUSTAJX_VerifyOTC&otcCode=" + otcCode + "&email=" + email + "&compName=" + compName + "&status=" + status_val + "&sitename=" + sitename,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $("#add_errorMsg").html('');
                if (response.status == "SUCCESS") {
                    $("#aviraotc").attr('readonly', true);
                    
                    if ($('#confiure_gateway').is(':checked')) {
                        $("#aviraConfigureNextForGateway").show();
                        $("#aviraConfigureNext").hide();
                        $("#avira_next").hide();
                        $("#createCustomerNext").show();
                    } else {
                        $("#aviraConfigureNextForGateway").hide();
                        $("#aviraConfigureNext").show();
                        $("#createCustomerNext").hide();
                        $("#avira_next").show();
                    }
                    triggerConfiureGatewayCheckEvent();
                    $("#avira_verify").hide();
                    $("#avira_total").html(response.licsCnt);
                    $("#avira_used").html(response.used);
                    $("#avira_pending").html(response.pendingCount);
                    $("#avira_pcno").val(response.licsCnt);
                    $("#avira_pcno").attr("max", response.pendingCount);
                    $("#avira_pending_hidden").val(response.licenseCount);
                    if (status_val === 0) {
                        $("#cust_email").prop('disabled', true);
                        $("#cust_firstName").prop('disabled', true);
                        $("#cust_lastName").prop('disabled', true);
                        $("#aviraotc").prop('disabled', true);
                        $("#addNewSite").prop('disabled', true);
                    }

                    $('#Createsite .customscroll').mCustomScrollbar('scrollTo', 'bottom');

                } else if (response.status == "DUPLICATE") {
                    $("#aviraotc").attr('readonly', false);
                    $("#avira_next").hide();
                    $(".verifybutton").show();
                    $("#add_errorMsg").html("<span>This OTC is already in use.</span>");
                } else if (response.status == "ERROR") {
                    $("#aviraotc").attr('readonly', false);
                    $("#avira_next").hide();
                    $("#add_errorMsg").html(response.message);
                } else {
                    $("#aviraotc").attr('readonly', false);
                    $("#avira_next").hide();
                    $("#add_errorMsg").html(response.status);
                }

            },
            error: function(err) {

            }
        });
    }
}

$("#avira_gatewayDiv .icon-ic_info_outline_24px").mouseover(function(){
    $(this).parent().find('.tooltip-inner').show();
})

$("#avira_gatewayDiv .icon-ic_info_outline_24px").mouseleave(function(){
    $(this).parent().find('.tooltip-inner').hide();
})

// ##################### Customer Creation/Provisioning End ############################ //

// ************** Reset Password Ends Here ***************** //