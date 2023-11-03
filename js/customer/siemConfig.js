/*
 This file is releated to SIEM Page
 */

$(document).ready(function () {
    getCustomerSites();
    loadConfiguration();
});

function loadConfiguration() {

    $.ajax({
        url: "../lib/l-siem.php?function=getSiemData&csrfMagicToken=" + csrfMagicToken,
        type: "POST",
        dataType: "json",
        success: function (gridData) {
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

    $('#siemTable').on('click', 'tr', function () {
        var rowID = siemTable.row(this).data();
        var selected = rowID[2];
        $("#selected").val(rowID[2]);
        siemTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });
    $('#siemTable').on('dblclick', 'tr', function () {
        rightContainerSlideOn("edit-new-siem");
        disableFields();
        fetchExistingData();
    });
}

function fetchExistingData() {

    var id = $('#selected').val();
    //console.log(id);
    if (id === undefined || id === 'undefined' || id === '') {
        $.notify("Please create a record");
        closePopUp();
        return false;
    }
    $.ajax({
        url: "../lib/l-siem.php?function=SIEM_getSiemData&id=" + id + "&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        dataType: 'json',
        success: function (response) {
            $('#edit_SIEM_Name').val(response.name);
            if (response.global == 1) {
                $('#edit_global_val').prop('checked', true);
            }
            if (response.log != '' && response.log != null) {
                $('#edit_log_val').prop('checked', true);
            }
            if (response.asset != '' && response.asset != null) {
                $('#edit_asset_val').prop('checked', true);
            }
            if (response.dart != '' && response.dart != null) {
                $('#edit_dart_val').prop('checked', true);
            }
            if (response.noc != '' && response.noc != null) {
                $('#edit_notify_val').prop('checked', true);
            }
            if (response.comp != '' && response.comp != null) {
                $('#edit_comp_val').prop('checked', true);
            }
            if (response.pro != '' && response.pro != null) {
                $('#edit_pro_val').prop('checked', true);
            }
            if (response.patch != '' && response.patch != null) {
                $('#edit_patch_val').prop('checked', true);
            }
            if (response.event != '' && response.event != null) {
                $('#edit_event_val').prop('checked', true);
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


function getCustomerSites() {

    $.ajax({
        url: "../lib/l-siem.php?function=SIEM_getSiteList&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        dataType: 'text',
        success: function (response) {
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

    if (siteName === "" || siteName === "undefined" || siteName === undefined) {
        $.notify("Please select the Site");
        return false;
    }
    /*var csvq = $('input[name=csv]')[0].files[0];
     if (csvq == 'undefined' || csvq == undefined) {

     } else {
     var filecsv = csvq.name;
     var fileext = filecsv.substring(filecsv.lastIndexOf('.') + 1);
     }*/

    if (configname == '') {
        $.notify("Please enter name");
        return false;
    }

    global = 1;

    if (logurl == '') {
        $.notify("Please enter log url");
        return false;
    }
    if (!validateUrl(logurl)) {
        $.notify("Please enter valid log url");
        return false;
    }

    if (asseturl == '') {
        $.notify("Please enter asset url");
        return false;
    }
    if (!validateUrl(asseturl)) {
        $.notify("Please enter valid asset url");
        return false;
    }

    if (notificationurl == '') {
        $.notify("Please enter notification url");
        return false;
    }
    if (!validateUrl(notificationurl)) {
        $.notify("Please enter notification log url");
        return false;
    }

    if (complianceurl == '') {
        $.notify("Please enter compliance url");
        return false;
    }
    if (!validateUrl(complianceurl)) {
        $.notify("Please enter valid compliance url");
        return false;
    }

    if (darturl == '') {
        $.notify("Please enter dart url");
        return false;
    }
    if (!validateUrl(darturl)) {
        $.notify("Please enter valid dart url");
        return false;
    }

    if (proactiveurl == '') {
        $.notify("Please enter proactive url");
        return false;
    }
    if (!validateUrl(proactiveurl)) {
        $.notify("Please enter valid proactive url");
        return false;
    }

    if (patchurl == '') {
        $.notify("Please enter patch url");
        return false;
    }
    if (!validateUrl(patchurl)) {
        $.notify("Please enter valid patch url");
        return false;
    }

    if (eventurl == '') {
        $.notify("Please enter event url");
        return false;
    }
    if (!validateUrl(eventurl)) {
        $.notify("Please enter valid event url");
        return false;
    }

    var filename = $('#csv_name').html();

    var m_data = new FormData();
    m_data.append('name', configname);
    m_data.append('global', global);
    m_data.append('logurl', logurl);
    m_data.append('eventurl', eventurl);
    m_data.append('asseturl', asseturl);
    m_data.append('darturl', darturl);
    m_data.append('patchurl', patchurl);
    m_data.append('complianceurl', complianceurl);
    m_data.append('notificationurl', notificationurl);
    m_data.append('proactiveurl', proactiveurl);
    m_data.append('sitename', siteName);
    //m_data.append('file', csvq);
    m_data.append('filename', filename);

    $.ajax({
        url: "../lib/l-siem.php?function=SIEM_addConfig&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        dataType: 'json',
//        data: sectionJson,
        data: m_data,
        processData: false,
        contentType: false,
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
    var id = $('#selected').val();

    if (siteName === "" || siteName === "undefined" || siteName === undefined) {
        $.notify("Please select the Site.");
        return false;
    }

    if (configname == '') {
        $.notify("Please enter name");
        return false;
    }

    if ($('#edit_global_val').is(':checked')) {
        global = 1;
    }

    if ($('#edit_log_val').is(':checked')) {
        if (logurl == '') {
            $.notify("Please enter log url");
            return false;
        }
        if (!validateUrl(logurl)) {
            $.notify("Please enter valid log url");
            return false;
        }
    }

    if ($('#edit_asset_val').is(':checked')) {
        if (asseturl == '') {
            $.notify("Please enter asset url");
            return false;
        }
        if (!validateUrl(asseturl)) {
            $.notify("Please enter valid asset url");
            return false;
        }
    }

    if ($('#edit_notify_val').is(':checked')) {
        if (notificationurl == '') {
            $.notify("Please enter notification url");
            return false;
        }
        if (!validateUrl(notificationurl)) {
            $.notify("Please enter valid notification url");
            return false;
        }
    }
    if ($('#edit_comp_val').is(':checked')) {
        if (complianceurl == '') {
            $.notify("Please enter compliance url");
            return false;
        }
        if (!validateUrl(complianceurl)) {
            $.notify("Please enter valid compliance url");
            return false;
        }
    }
    if ($('#edit_dart_val').is(':checked')) {
        if (darturl == '') {
            $.notify("Please enter dart url");
            return false;
        }
        if (!validateUrl(darturl)) {
            $.notify("Please enter valid dart url");
            return false;
        }
    }
    if ($('#edit_pro_val').is(':checked')) {
        if (proactiveurl == '') {
            $.notify("Please enter proactive url");
            return false;
        }
        if (!validateUrl(proactiveurl)) {
            $.notify("Please enter valid proactive url");
            return false;
        }
    }
    if ($('#edit_patchurl').is(':checked')) {
        if (patchurl == '') {
            $.notify("Please enter patch url");
            return false;
        }
        if (!validateUrl(patchurl)) {
            $.notify("Please enter valid patch url");
            return false;
        }
    }

    if ($('#edit_eventurl').is(':checked')) {
        if (eventurl == '') {
            $.notify("Please enter event url");
            return false;
        }
        if (!validateUrl(eventurl)) {
            $.notify("Please enter valid event url");
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
    finalarr.id = id;


    var sectionJson = JSON.stringify(finalarr);

    $.ajax({
        url: "../lib/l-siem.php?function=SIEM_editConfig&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        dataType: 'json',
        data: sectionJson,
        success: function (data) {
            //alert("data.status--->" + data.status);
            if (data.status === 'success') {
                $.notify("Successfully edited the Siem configuration");
                setTimeout(function () {
                    location.reload();
                }, 1500);
            } else if (data.status === 'failed') {
                $.notify("No changes have been made");
            } else {
                $.notify("Some error occurred. Please try again.");
            }


        },
        error: function (response) {
            console.log("Something went wrong in ajax call of SIEM_addSiemConfig function");
            console.log(response);
        }
    });

}

function samplefileExport() {

    window.location.href = '../lib/l-siem.php?function=get_samplefileDownload'
}

$("#csv_file").on("change", function () {
    var file_data = $("#csv_file").prop("files")[0];
    var logo_data = new FormData();
    var csv_name = $("#csv_file").prop("files")[0]["name"];

    logo_data.append("file", file_data);
    logo_data.append("type", "headerlogo");

    $("#csv_name").html(csv_name).css({color: "black"});
    $(".logo_loader").show();
    $('#remove_logo').show();
});

$("#remove_logo").click(function () {
    $("#csv_file").val("");
    $("#csv_name").html("");

    $(".logo").attr("src", "../assets/img/bask-logo.png");
    $("#remove_logo").hide();
});

function deleteConfig() {

    var id = $('#selected').val();

    if (id == undefined || id == 'undefined' || id == '') {
        $.notify("Please choose at least one record");
        closePopUp();
    } else {
        sweetAlert({
            title: ' Are you sure you want to delete the configuration?',
            text: "You won't be able to revert this action!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#050d30',
            cancelButtonColor: '#fa0f4b',
            cancelButtonText: "No, cancel it!",
            confirmButtonText: 'Yes, delete it!'
        }).then(function (result) {
            $.ajax({
                type: "POST",
                dataType: "text",
                url: "../lib/l-siem.php?function=SIEM_deleteConfiguration&sel=" + id + "&csrfMagicToken=" + csrfMagicToken,
                success: function (result) {
                    $.notify("Configuration deleted successfully");
                    location.reload();
                }
            }
            );
        }
        ).catch(function (reason) {
            $(".closebtn").trigger("click");
        });
        closePopUp();
    }
}



