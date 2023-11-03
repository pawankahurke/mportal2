// Metering Report JS

$(document).ready(function() {
    table = $('#meterTable').DataTable({
        autoWidth: true,
        paging: true,
        searching: false,
        processing: true,
        serverSide: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        ajax: {
            url: "meterFunction.php?function=get_MeteringReport"+"&csrfMagicToken=" + csrfMagicToken,
            type: "POST",
            rowId: 'pid'
        },
        columns: [
            {"data": "name", "orderable": true},
            {"data": "type"},
            {"data": "username"},
            {"data": "created"},
            {"data": "expires"}
        ],
        "columnDefs": [
            { className: "dt-left", "targets": [ 0,1,2,3,4 ] }
          ],
        ordering: true,
        select: false,
        bInfo: false,
        responsive: true,
        dom: '<"top"i>rt<"bottomtable"flp><"clear">',
    });
    $('#meterTable_length').hide();
    $(".bottompager").each(function(){
        $(this).append($(this).find(".bottomtable"));
    });
    $('#meterTable').on('click', 'tr', function() {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        } else {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });
    
    $('#infoPortal').click(function(){
        if($(this).prop("checked") == true){
            $('#infoPortal').val(1);
        }
        else if($(this).prop("checked") == false){
            $('#infoPortal').val(0);
        }
    });
    $('#dispTimTot').click(function(){
        if($(this).prop("checked") == true){
            $('#dispTimTot').val(1);
        }
        else if($(this).prop("checked") == false){
            $('#dispTimTot').val(0);
        }
    });
    $('#dispCounTot').click(function(){
        if($(this).prop("checked") == true){
            $('#dispCounTot').val(1);
        }
        else if($(this).prop("checked") == false){
            $('#dispCounTot').val(0);
        }
    });
    
    $('#createreport').click(function (){
        $.ajax({
            url: "meterFunction.php?function=createMeterReport"+"&csrfMagicToken=" + csrfMagicToken,
            type: 'POST',
            data: $('#addreportform').serialize(),
            success: function (data) {
                if(data.trim() == 'EXIST') {
                    $('#errmsg').html('Report name already exists.');
                } else {
                    $('#closemodal').click();
                    var urlnew = "meterFunction.php?function=get_MeteringReport";
                    table.ajax.url(urlnew).load();
                }
            }
        });
        return false;
    });
    
    $('#viewreport').click(function (){
        var meterid   = $('#meterTable tbody tr.selected').attr('id');
        var metername = $('#meterTable tbody tr.selected td').first().html();
        if(typeof meterid === 'undefined' || meterid === 'undefined') {
            alert('Please select a record to View');
            return false;
        } else {
            $.ajax({
                url: "meterFunction.php?function=viewMeterReport",
                type: 'POST',
                data: 'meterid='+meterid+"&csrfMagicToken=" + csrfMagicToken,
                dataType: 'json',
                success: function (data) {
                    $('#title').html(data.title);
                    $('#creator').html(data.creator);
                    $('#type').html(data.type);
                    $('#total').html(data.total);
                    $('#using').html(data.using);
                    $('#records').html(data.records);
                    $('#startdate').html(data.startdate);
                    $('#enddate').html(data.enddate);
                    $('#repdate').html(data.repdate);
                }
            });
        }
    });
    
    $('#downloadreport').click(function(){
        var meterid   = $('#meterTable tbody tr.selected').attr('id');
        location.href= 'meterReportDownload.php?meterid='+meterid+"&csrfMagicToken=" + csrfMagicToken;
        $('#closemodal').click();
        return false;
    });
    
    $('#delreport').click(function (){
        var reportid   = $('#meterTable tbody tr.selected').attr('id');
        var reportname = $('#meterTable tbody tr.selected td').first().html();
        if(typeof reportid === 'undefined' || reportid === 'undefined') {
            alert('Please select a record to Delete');
            return false;
        } else {
            var conf = confirm('Are you sure want to delete report ' + reportname);
            if(conf) {
                $.ajax({
                url: "meterFunction.php?function=deleteMeterReport",
                type: 'POST',
                data: 'reportid='+reportid+"&csrfMagicToken=" + csrfMagicToken,
                success: function () {
                    var urlnew = "meterFunction.php?function=get_MeteringReport";
                    table.ajax.url(urlnew).load();
                }
            });
            return false;
            }
        }
    });
   
    
    // Audit : Sub  Module
    
    audittable = $('#auditTable').DataTable({
        autoWidth: true,
        paging: true,
        searching: false,
        processing: true,
        serverSide: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        ajax: {
            url: "meterFunction.php?function=get_AuditDetail"+"&csrfMagicToken=" + csrfMagicToken,
            type: "POST",
            rowId: 'pid'
        },
        columns: [
            {"data": "who", "orderable": true},
            {"data": "product"},
            {"data": "servertime"},
            {"data": "clienttime"},
            {"data": "sitename", "orderable": false},
            {"data": "machine", "orderable": false},
            {"data": "username"},
            {"data": "action"}
        ],
        "columnDefs": [
            { className: "dt-left", "targets": [ 0,1,2,3,4,5,6,7 ] }
          ],
        ordering: true,
        select: false,
        bInfo: false,
        responsive: true,
        dom: '<"top"i>rt<"bottomtable"flp><"clear">',
          
    });
    
    $('#auditTable_length').hide();
    $(".bottompager").each(function(){
        $(this).append($(this).find(".bottomtable"));
    });
    $('#auditTable').on('click', 'tr', function() {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        } else {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });
    
});
