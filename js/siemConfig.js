/*
 This file is releated to SIEM Page
 */

$(document).ready(function () {
    loadConfiguration();
});

function loadConfiguration() {

      $.ajax({
        url: "../lib/l-siem.php?function=getSiemData",
        type: "POST",
        dataType: "json",
        success: function(gridData) {
            $(".loader").hide();
//            $('#siemTable').DataTable().destroy();
            siemTable = $('#siemTable').DataTable({
                scrollY: jQuery('#siemTable').data('height'),
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
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    search: "_INPUT_",
                    searchPlaceholder: "Search records"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                initComplete: function (settings, json) {
                    $('#siemTable tbody tr:eq(0)').addClass("selected");
                    var qid = $('#siemTable tbody tr:eq(0) p')[0].id;
                     $("#selected").val(qid);

                },
                drawCallback: function (settings) {
                }
            });
             $('.dataTables_filter input').addClass('form-control');
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });

    $('#siemTable').on('click', 'tr', function() {
        var rowID = siemTable.row(this).data();
        var selected = rowID[2];
        $("#selected").val(rowID[2]);
        siemTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });
    $('#siemTable').on('dblclick', 'tr', function() {
        rightContainerSlideOn("edit-new-siem");
        disableFields();
        fetchExistingData();
    });
}

function fetchExistingData() {

    var id = $('#selected').val();
    console.log(id);
    $.ajax({
        url: "../lib/l-siem.php?function=SIEM_getSiemData&id="+id,
        type: 'POST',
        dataType: 'json',
        success: function (response) {
            $('#edit_SIEM_Name').val(response.name);
            if(response.global == 1) {
                $('#edit_global_val').prop('checked',true);
            }
            if(response.log != '' && response.log != null) {
                $('#edit_log_val').prop('checked',true);
            }
            if(response.asset != '' && response.asset != null ) {
                $('#edit_asset_val').prop('checked',true);
            }
            if(response.dart != '' && response.dart !=null) {
                $('#edit_dart_val').prop('checked',true);
            }
            if(response.noc != '' && response.noc !=null) {
                $('#edit_notify_val').prop('checked',true);
            }
            if(response.comp != '' && response.comp !=null) {
                $('#edit_comp_val').prop('checked',true);
            }
            if(response.pro != '' && response.pro !=null) {
                $('#edit_pro_val').prop('checked',true);
            }
            if(response.patch != '' && response.patch !=null) {
                $('#edit_patch_val').prop('checked',true);
            }
            if(response.event != ''&& response.event !=null) {
                $('#edit_event_val').prop('checked',true);
            }

            $('#edit_logurl').val(response.log);
            $('#edit_asseturl').val(response.asset);
            $('#edit_notifyurl').val(response.noc);
            $('#edit_compurl').val(response.comp);
            $('#edit_darturl').val(response.dart);
            $('#edit_prourl').val(response.pro);
            $('#edit_patchurl').val(response.patch);
            $('#edit_eventurl').val(response.event);
           $('#edit_Sites').html(response.site);
            $(".selectpicker").selectpicker("refresh");

        },
        error: function (response) {
            console.log("Something went wrong in ajax call of getCrmDetails function");
            console.log(response);
        }
    });


}


function getCustomerSites(num) {

    $.ajax({
        url: "../lib/l-siem.php?function=SIEM_getSiteList",
        type: 'POST',
        dataType: 'text',
        success: function (response) {
            $('#C-site_' + num).html(response);
            $('#Sites').html(response);
            $(".selectpicker").selectpicker("refresh");

        },
        error: function (response) {
            console.log("Something went wrong in ajax call of getCrmDetails function");
            console.log(response);
        }
    });

}

function submitsiem() {

    var configname = $('#SIEM_Name').val();
    var global = 0;
    var logurl = $('#logurl').val();
    var darturl = $('#darturl').val();
    var proactiveurl = $('#prourl').val();
    var asseturl = $('#asseturl').val();
    var complianceurl = $('#compurl').val();
    var notificationurl = $('#notifyurl').val();
    var eventurl = $('#eventurl').val();
    var patchurl = $('#patchurl').val();
    var siteName = $('#Sites').val();

    if (configname == '') {
        $.notify("Please enter name");
        return false;
    }

    if ($('#global_val').is(':checked')) {
        global = 1;
    }

    if($('#log_val').is(':checked')) {
        if (logurl == '') {
            $.notify("Please enter log url");
            return false;
        }
    }

    if($('#asset_val').is(':checked')) {
        if(asseturl == '') {
            $.notify("Please enter asset url");
            return false;
        }
    }

    if($('#notify_val').is(':checked')) {
        if(notificationurl == '') {
            $.notify("Please enter notification url");
            return false;
        }
    }
    if($('#comp_val').is(':checked')) {
        if(complianceurl == '') {
            $.notify("Please enter compliance url");
            return false;
        }
    }
    if($('#dart_val').is(':checked')) {
        if(darturl == '') {
            $.notify("Please enter dart url");
            return false;
        }
    }
    if($('#pro_val').is(':checked')) {
        if(proactiveurl == '') {
            $.notify("Please enter proactive url");
            return false;
        }
    }
    if($('#patchurl').is(':checked')) {
        if(patchurl == '') {
            $.notify("Please enter patch url");
            return false;
        }
    }

    if($('#eventurl').is(':checked')) {
        if(eventurl == '') {
            $.notify("Please enter event url");
            return false;
        }
    }

    var finalarr = new Object;
    finalarr.name = configname;
    finalarr.global = global;
    finalarr.logurl = logurl;
    finalarr.eventurl = eventurl;
    finalarr.asseturl = asseturl;
    finalarr.darturl = darturl;
    finalarr.patchurl = patchurl;
    finalarr.complianceurl = complianceurl;
    finalarr.notificationurl = notificationurl;
    finalarr.proactiveurl = proactiveurl;
    finalarr.sitename = siteName;


    var sectionJson = JSON.stringify(finalarr);

    $.ajax({
        url: "../lib/l-siem.php?function=SIEM_addConfig",
        type: 'POST',
        dataType: 'json',
        data: sectionJson,
        success: function (data) {
            $.notify("Siem configuration saved successfully");
            setTimeout(function () {
                location.reload();
            }, 1500);

        },
        error: function (response) {
            console.log("Something went wrong in ajax call of SIEM_addSiemConfig function");
            console.log(response);
        }
    });

}

function editsiem() {

    var configname = $('#edit_SIEM_Name').val();
    var global = 0;
    var logurl = $('#edit_logurl').val();
    var darturl = $('#edit_darturl').val();
    var proactiveurl = $('#edit_prourl').val();
    var asseturl = $('#edit_asseturl').val();
    var complianceurl = $('#edit_compurl').val();
    var notificationurl = $('#edit_notifyurl').val();
    var eventurl = $('#edit_eventurl').val();
    var patchurl = $('#edit_patchurl').val();
    var siteName = $('#edit_Sites').val();

    if (configname == '') {
        $.notify("Please enter name");
        return false;
    }

    if ($('#edit_global_val').is(':checked')) {
        global = 1;
    }

    if($('#edit_log_val').is(':checked')) {
        if (logurl == '') {
            $.notify("Please enter log url");
            return false;
        }
    }

    if($('#edit_asset_val').is(':checked')) {
        if(asseturl == '') {
            $.notify("Please enter asset url");
            return false;
        }
    }

    if($('#edit_notify_val').is(':checked')) {
        if(notificationurl == '') {
            $.notify("Please enter notification url");
            return false;
        }
    }
    if($('#edit_comp_val').is(':checked')) {
        if(complianceurl == '') {
            $.notify("Please enter compliance url");
            return false;
        }
    }
    if($('#edit_dart_val').is(':checked')) {
        if(darturl == '') {
            $.notify("Please enter dart url");
            return false;
        }
    }
    if($('#edit_pro_val').is(':checked')) {
        if(proactiveurl == '') {
            $.notify("Please enter proactive url");
            return false;
        }
    }
    if($('#edit_patchurl').is(':checked')) {
        if(patchurl == '') {
            $.notify("Please enter patch url");
            return false;
        }
    }

    if($('#edit_eventurl').is(':checked')) {
        if(eventurl == '') {
            $.notify("Please enter event url");
            return false;
        }
    }

    var finalarr = new Object;
    finalarr.name = configname;
    finalarr.global = global;
    finalarr.logurl = logurl;
    finalarr.eventurl = eventurl;
    finalarr.asseturl = asseturl;
    finalarr.darturl = darturl;
    finalarr.patchurl = patchurl;
    finalarr.complianceurl = complianceurl;
    finalarr.notificationurl = notificationurl;
    finalarr.proactiveurl = proactiveurl;
    finalarr.sitename = siteName;


    var sectionJson = JSON.stringify(finalarr);

    $.ajax({
        url: "../lib/l-siem.php?function=SIEM_addConfig",
        type: 'POST',
        dataType: 'json',
        data: sectionJson,
        success: function (data) {
            $.notify("Siem configuration edited successfully");
            setTimeout(function () {
                location.reload();
            }, 1500);

        },
        error: function (response) {
            console.log("Something went wrong in ajax call of SIEM_addSiemConfig function");
            console.log(response);
        }
    });

}



