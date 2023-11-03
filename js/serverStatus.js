$(document).ready(function () {
    var selected = $('#selectedUrlType').val();
    defaultServerConfig(selected);
});

function defaultServerConfig(selected){
    $('#insertUrl').val('');
    $('#msgError').html('');
    $.ajax({
        url: '../serverSetup/serverFunctions.php',
        type: 'POST',
        data:{'function':'getServerConfiguration','selected':selected},
        dataType: 'json',
        success: function (data) {
            if(selected == 2){
                var url = data[0].Url;
                $('#insertUrl').val(data[0].Url);
                $('#hiddenValueUrl').val(data[0].reportUrl);
            }else if(selected == 1){
                var url = data[0].Url;
                $('#insertUrl').val(data[0].reportUrl);
                $('#hiddenValueUrl').val(data[0].reportUrl);
            }else if(selected == 0){
                var url = data[0].Url;
                $('#insertUrl').val(data[0].Url+"rpc/rpc.php");
                $('#hiddenValueUrl').val(data[0].reportUrl);
            }
        },
        error: function(error){
            
        }
    });
}

function checkUrlStatus(){
    var wsurl = $('#insertUrl').val();
    var reportingurl = $('#hiddenValueUrl').val();
    var selected = $('#selectedUrlType').val();
    if(selected == 1 || selected == 0){
        $.ajax({
            url: '../serverSetup/serverFunctions.php',
            type: 'POST',
            data:{'function':'CurlFunction','reportingurl':wsurl,csrfMagicToken:csrfMagicToken},
            success: function (data) {
                console.log(data);
                if(data == 200){
                    $('#msgError').html('Connection Successful');
                    $('#msgError').css('color','green');
                }else{
                    $('#msgError').html('Error in Connection');
                    $('#msgError').css('color','red');
                }
            },
            error: function(error){

            }
        });
    }else if(selected == 2){
        wsconnect('wss://' + wsurl, reportingurl);
    }
    
}

function wsconnect(wsurl, reportingurl) {
    $('#msgError').html('');
        ws = new WebSocket(wsurl);
        ws.onopen = function () {
            console.log("success");
            $('#msgError').html('Connection Successful');
            $('#msgError').css('color','green');
            var ConnectData = {};
            ConnectData['Type'] = 'Dashboard';
            ConnectData['AgentId'] = '<?php echo $agentUniqId; ?>';
            ConnectData['AgentName'] = '<?php echo $agentName; ?>';
            ConnectData['ReportingURL'] = reportingurl;
            ws.send(JSON.stringify(ConnectData));
        };
        ws.onmessage = function (msg) {
            var JsonMsg = JSON.parse(msg.data);
            var ServiceTag = JsonMsg.ServiceTag;
            if (GlServiceTag === ServiceTag) {
                ShowJobProgress(msg);
            }
        };
        ws.onclose = function () {
            setTimeout(function () {
                wsconnect(wsurl);
            }, 2000);
        };
        ws.error = function(msg){
            console.log("error");
            $('#msgError').html('Error in Web socket connection');
            $('#msgError').css('color','red');
            wsconnect(wsurl,reportingurl);
        };
    }


function saveSelectedStatus(){
    var selected = $('#selectedUrlType').val();
    var value = '';
    if(selected == 0){
        value = 'License Server';
        defaultServerConfig(selected);
    }else if(selected == 1){
        value = 'Reporting Server';
        defaultServerConfig(selected);
    }else if(selected == 2){
        value = 'Node';
        defaultServerConfig(selected);
    }
}