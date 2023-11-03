//Get_EventDT();
function Get_EventDT() {    
    $('#eventsfiltergrid').hide();
    $.ajax({
        url: "../lib/l-ajax.php",
        data: "function=AJAX_GetEventfilterGridData&srch=1&csrfMagicToken=" + csrfMagicToken,
        type: "GET",
        dataType: 'json',
        success: function(gridData) {
            $(".se-pre-con").hide();
            $('#eventTable').DataTable().destroy();
            eventTable = $('#eventTable').DataTable({
                scrollY: jQuery('#eventTable').data('height'),
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
                order: [[0, "asc"]],
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                initComplete: function(settings, json) {
                    $('#eventTable tbody tr:eq(0)').addClass("selected");
                    var qid = $('#eventTable tbody tr:eq(0) p')[0].id;                        
                    $("#selected").val(qid);
                    loadQueryData(qid);
                },
                drawCallback: function(settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }
            });
            $("#eventdetail_searchbox").keyup(function() {
                eventTable.search(this.value).draw();
            });
            $('#proactiveauditGrid_filter').hide();
        },
        error: function(msg) {

        }
    });
    /*
     * This function is for selecting
     *  row in event filter
     */

    $('#eventTable').on('click', 'tr', function() {
        var rowID = eventTable.row(this).data();
        //console.debug(rowID);
        var selected = rowID[3];
        $("#selected").val(selected);
        loadQueryData(selected);
        eventTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        
    });
    /*
     * This function is for serching
     *  in event filter
     */

    $("#eventdetail_searchbox").keyup(function() {
        eventTable.search(this.value).draw();
    });
}


/* Add new Eventfilter
 * Popup functionality 
 */
function addNewFilter() {     
    window.location.href = '../admin/addfilter.php?act=addn';
}

/* This functionality is 
 * for go back to
 *  event-filter page 
 */

function backToEventFilterPage() {    
    window.location.href = "../admin/eventfilter.php";
}

/* This function is for 
 * selecting different pop-up */

function selectConfirm(data_target_id) {
    var selected = $("#selected").val();
    if (selected === '') {
        $("#warning").modal('show');
    } else {

        if (data_target_id === 'edit') {
            var selected = $("#selected").val();
            window.location.href = "../admin/editfilter.php?act=edit&editid=" + selected;
        } else if (data_target_id === 'copy') {
            var selected = $("#selected").val();
            window.location.href = "../admin/Copyeventfilter.php?act=copy&copyid=" + selected;
        } else if (data_target_id === 'delete') {
            $("#message").modal('show');
            $("#deleteModal").modal('show');
        } else if (data_target_id == 'eventfltrDtil') {
            eventfilterDetails(selected);
        } else if (data_target_id == 'eventrun') {
            eventfilterRun(selected);
        } 
    }
    return true;
}

$("#global").click(function() {
    if ($(this).is(":checked")) {
        $(this).val("1");
    } else {
        $(this).val("0");
    }
});
/* This eventfilter functionality
 *  is for add new data in table 
 */

function addSubmitForm() {   
    var ename = $.trim($("#name").val());
    var ename1 = encodeURIComponent(ename); //to parse special character in name
    var filter = $.trim($("#filter").val());
    var encode_fiter = encodeURIComponent(filter);
    var global = $("#global").val();
    if (ename === "" || filter === "") {
        $("#valErr").html("");
        $("#valErr").show();
        $("#valErr").html("<span>Please enter the * required fields</span>");
        setTimeout(function() {
        $("#valErr").fadeOut(3000);
        }, 2000);
        return false;
    } 
    if (ename !== "") { 
            $("#valErr").html("");
            $("#valErr").show();
       var regx = /[^a-zA-Z0-9\_\s]/;
        if(regx.test(ename)) {
                 $("#valErr").html("<span>Only alphanumeric and underscore allowed for name field.</span>");
                setTimeout(function() {
                $("#valErr").fadeOut(3000);
                }, 2000);
                return false;
            }
    }
    if (ename !== "" && filter !== "") {
            
            $.ajax({
                url: "../lib/l-ajax.php",
                data: "function=AJAX_SubmitFilter&val=1&name=" + ename1 + "&filter=" + encode_fiter + "&global=" + global + "&csrfMagicToken=" + csrfMagicToken,
                type: 'POST',
                success: function(check) {
//            $("#validateSuccess").removeClass("text-danger");   $("#validateSuccess").removeClass("text-success");
                    if (check === "available") {
                        $("#valErr").html("");
                        $("#valErr").show();
                        $("#validateSuccess").fadeIn();
                        $("#validateSuccess").addClass("text-danger");
                        $("#valErr").html("<span>Name already exist.</span>");
                        setTimeout(function() {
                            $("#valErr").fadeOut(3000);
                        }, 2000);
                    } else {
                        var filter = $("#filter").val();
                        var regExp = /^[a-zA-Z0-9\s%\"\\n\:\)\(+=>,</.'_-]+$/g;
                        var res = regExp.test(filter);
                        if (res === true) {
                            $.ajax({
                                url: "../lib/l-ajax.php",
                                data: "function=AJAX_SubmitFilter&val=0&name=" + ename1 + "&filter=" + encode_fiter + "&global=" + global + "&csrfMagicToken=" + csrfMagicToken,
                                type: 'POST',
                                success: function(check) {
                                    if (check == 1) {
                                        $("#valErr").html("");
                                        $("#valErr").show();
                                        $("#validateSuccess").fadeIn();
                                        $("#validateSuccess").removeClass("text-danger");
                                        $("#validateSuccess").addClass("text-success");
                                        $("#validateSuccess").html('<span>New Event Filter is added successfully</span>');
                                        setTimeout(function() {
                                            $("#validateSuccess").fadeOut(3000);
                                        }, 2000);
                                        setTimeout(function() {
                                            window.location.href = "../admin/eventfilter.php";
                                        }, 2000);
                                    } else {
                                        $("#valErr").html("");
                                        $("#valErr").show();
                                        $("#validateSuccess").fadeIn();
                                        $("#validateSuccess").addClass("text-success");
                                        $("#validateSuccess").removeClass("text-success");
                                        $("#valErr").html('<span>Event Filter cannot be created with Double Quotes</span>');
                                        setTimeout(function() {
                                            $("#valErr").fadeOut(3000);
                                        }, 2000);
                                    }
                                }
                            });
                        } else {
                            $("#filtervalidId").show();
                            setTimeout(function() {
                                $("#valErr").fadeOut(3000);
                            }, 2000);
                        }
                    }
                }
            });
        }
    }

/* This eventfilter functionality
 *  is for update data in table 
 */

function eventUpdateForm() {
    var name = $.trim($("#name1").val());
    var name1 = encodeURIComponent(name);
    var filter =  $.trim($("#filter1").val());
    var encode_fiter = encodeURIComponent(filter);
    var global = $("#global").val();
    var editid = $("#selected").val();
    var formfields = $(".form-control").val();

    if (name == "" || filter == "") {
        $("#valErr").html("");
        $("#valErr").show();
        $("#valErr").html("<span>Please enter the * required fields</span>");
        setTimeout(function() {
        $("#valErr").fadeOut(3000);
        }, 2000);
        return false;
    } else {
        $("#valErr").html("");
        $("#valErr").show();
        var regx = /[^a-zA-Z0-9\_\s]/;
        if(regx.test(name)) {
             $("#valErr").html("<span>Only alphanumeric and underscore allowed for name field.</span>");
            setTimeout(function() {
            $("#valErr").fadeOut(3000);
            }, 2000);
            return false;
        }
        var filter = $("#filter1").val();
        var regExp = /^[a-zA-Z0-9\s%\"\\n\:\)\(<+=.,>/'_-]+$/g;
        var res = regExp.test(filter);

        if (res === true) {

            $.ajax({
                url: "../lib/l-ajax.php",
                data: "function=AJAX_UpdateFilter&val=0&name=" + name1 + "&filter=" + encode_fiter + "&editid=" + editid + "&global=" + global + '&csrfMagicToken=' + csrfMagicToken,
                type: "POST",
                success: function(getdata) {
                    if ($.trim(getdata) === "success") {
                        $("#valErr").html("");
                        $("#valErr").show();
                        $("#validateSuccess").fadeIn();
                        $("#validateSuccess").removeClass("text-danger");
                        $("#validateSuccess").addClass("text-success");
                        $("#validateSuccess").html('<span>Event Filter is updated successfully</span>');
                        setTimeout(function() {
                            $("#validateSuccess").fadeOut(3000);
                        }, 2000);
                        setTimeout(function() {
                            window.location.href = "../admin/eventfilter.php";
                        }, 2000);
                    } else {
                        $("#valErr").html("");
                        $("#valErr").show();
                        $("#validateSuccess").fadeIn();
                        $("#validateSuccess").addClass("text-success");
                        $("#validateSuccess").removeClass("text-success");
                        $("#valErr").html('<span>Event Filter cannot be created with Double Quotes</span>');
                        setTimeout(function() {
                            $("#valErr").fadeOut(3000);
                        }, 2000);
                    }
                }
            });
        } else {
            $("#filtervalidId").show();
            setTimeout(function() {
                $("#valErr").fadeOut(3000);
            }, 2000);
        }
    }
}

/*  This eventfilter functionality
 *  is for create duplicate event filter
 *  in table 
 */

function CopyForm() {    
    var name = $("#copy_name").val();
    var filter = $("#copy_filter").val();
    var global = $("#global").val();
    var copyid = $("#selected").val();
    var formfields = $(".form-control").val();
    if (name == "" || filter == "") {
        $("#valErr").html("");
        $("#valErr").show();
        $("#valErr").html("<span>Please enter the * required fields</span>");
        setTimeout(function() {
        $("#valErr").fadeOut(3000);
        }, 2000);
        return false;
    } else {
        $("#valErr").html("");
        $("#valErr").show();
        var regx = /[^a-zA-Z0-9\_\s]/; 
        if(regx.test(name)) {
             $("#valErr").html("<span>Only alphanumeric and underscore allowed for name field.</span>");
            setTimeout(function() {
            $("#valErr").fadeOut(3000);
            }, 2000);
            return false;
        }

        var filter = $("#copy_filter").val();
        var regExp = /^[a-zA-Z0-9\s%\"\\n\:\)\(<+=.,>/'_-]+$/g;
        var res = regExp.test(filter);

        if (res === true) {
        $.ajax({
            url: "../lib/l-ajax.php",
            data: "function=AJAX_copyFilter&val=1&name=" + name + "&filter=" + filter + "&copyid=" + copyid + "&global=" + global + '&csrfMagicToken=' + csrfMagicToken,
            type: "POST",
            success: function(check) {
                if ($.trim(check) === "available") {
                    $("#validateSuccess").fadeIn();
                    $("#validateSuccess").addClass("text-danger");
                    $("#validateSuccess").removeClass("text-success");
                        $("#valErr").html("<span>Event name is already exist</span>");
                        setTimeout(function() {
                            $("#valErr").fadeOut(3000);
                        }, 2000);
                } else {
                    $.ajax({
                        url: "../lib/l-ajax.php",
                        data: "function=AJAX_copyFilter&val=0&name=" + name + "&filter=" + filter + "&copyid=" + copyid + "&global=" + global + '&csrfMagicToken=' + csrfMagicToken,
                        type: "POST",
                        dataType: "text",
                        success: function(getdata) {
                            if ($.trim(getdata) === 1 || $.trim(getdata) === '1') {
                                $("#validateSuccess").fadeIn();
                                $("#validateSuccess").removeClass("text-danger");
                                $("#validateSuccess").addClass("text-success");
                                $("#validateSuccess").html('<span>Event Filter is copied successfully</span>');
                                setTimeout(function() {
                                        $("#validateSuccess").fadeOut(3000);
                                    }, 2000);
                                    setTimeout(function() {
                                    window.location.href = "../admin/eventfilter.php";
                                }, 2000);
                            } else {
                                $("#valErr").html("");
                                $("#valErr").show();
                                $("#validateSuccess").fadeIn();
                                $("#validateSuccess").addClass("text-danger");
                                $("#validateSuccess").removeClass("text-success");
                                $("#valErr").html('<span>Event Filter cannot be created with Double Quotes</span>');
                                    setTimeout(function() {
                                        $("#valErr").fadeOut(3000);
                                    }, 2000);
                            }
                        }
                    });
                }
            }
        });
        } else {
            $("#filtervalidId").show();
            setTimeout(function() {
                $("#valErr").fadeOut(3000);
            }, 2000);
    }

}
}

/* This eventfilter functionality
 *  is for delete data from table 
 */

function deleterecord() {
    var deleteid = $("#selected").val();
    $.ajax({
        url: "../lib/l-ajax.php",
        data: "function=AJAX_deleteFilter&deleteid=" + deleteid + '&csrfMagicToken=' + csrfMagicToken,
        type: "POST",
        success: function(getdata) {
            if (getdata != "available") {
//                $("#validateSuccess").fadeIn();
//                $("#validateSuccess").removeClass("text-danger");
//                $("#validateSuccess").addClass("text-success");
//                $("#validateSuccess").html('<span>Event Filter is deleted successfully</span>');
//                setTimeout(function() {
//                    $("#validateSuccess").fadeOut(3000);
//                }, 2000);
     $('#deleteModal').modal('hide');
                $('#deleteeventfilterpopup').modal('show');
                setTimeout(function() {
                    window.location.href = "../admin/eventfilter.php";
                }, 2000);
            } else {
                $("#validateSuccess").fadeIn();
                $("#validateSuccess").addClass("text-danger");
                $("#validateSuccess").removeClass("text-success");
                $("#validateSuccess").html('<span>This action is not possible</span>');
                setTimeout(function() {
                    $("#validateSuccess").fadeOut(3000);
                }, 2000);
            }
        }
    });
}

/* ====== Event query filter code ====== */
function EventfilterSearch() {

    var evntScope = $('#evntScope').val();
    var eventname = $('#eventname').val();
    var evntowner = $('#event_sel_searchstring').val();
    var crtedmnth = $('#eventmonth').val();
    var crtdday = $('#eventday').val();
    var crtdyear = $('#eventyear').val();      
    
    $.ajax({
        url: "../lib/l-ajax.php",
        data: "function=AJAX_GetEventfilterGridData&srch=1&evntScope=" + evntScope + '&eventname=' + eventname + '&evntowner=' + evntowner + '&crtedmnth=' + crtedmnth + '&crtdday=' + crtdday + '&crtdyear=' + crtdyear + "&csrfMagicToken=" + csrfMagicToken + '&search=1',
        type: "GET",
        dataType: 'json',
        success: function(gridData) {
//            console.debug(gridData);
            $(".se-pre-con").hide();
            $('#eventTable').DataTable().destroy();
            eventTable = $('#eventTable').DataTable({
                scrollY: jQuery('#eventTable').data('height'),
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
                drawCallback: function(settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }
            });
            $("#eventdetail_searchbox").keyup(function() {
                eventTable.search(this.value).draw();
            });
            $('#proactiveauditGrid_filter').hide();
        },
        error: function(msg) {

        }
    });         
}
function refreshConfirm() {
   $(".se-pre-con").show(); 
   Get_EventDT();  
   $("#eventdetail_searchbox").val("");
}

function eventfilterDetails(id) {       
    $.ajax({
        url: '../lib/l-ajax.php?function=AJAX_GetEventFilterDtal&csrfMagicToken=' + csrfMagicToken,
        type: 'post',
        data: 'eid=' + id,
        dataType: 'json',
        success: function(data) {
            $('#eventfilerpopup').modal('show');
            $('#createdTime').html(data.created);
            $('#serhstring').html(data.searchstring);
        }        
    })
}

function loadQueryData(id) {    
    $.ajax({
        url: '../lib/l-ajax.php?function=AJAX_GetEventRightGridData&csrfMagicToken=' + csrfMagicToken,
        type: 'post',
        data: 'eid=' + id,
        dataType: 'json',
        success: function(gridData) {                                   
            $('#eventRightTableData').DataTable().destroy();
            eventRightTable = $('#eventRightTableData').DataTable({
                scrollY: jQuery('#eventRightTableData').data('height'),
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
                drawCallback: function(settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }
            });            
        },
        error: function(msg) {

        }
    });    
}

function eventfilterRun(id) {    
    $.ajax({
        url: '../lib/l-ajax.php?function=AJAX_GetEventRunSubmit&csrfMagicToken=' + csrfMagicToken,
        type: 'post',
        data: 'eid=' + id,
        success: function(data) {
            if (data == 'success') {
                $('#runeventfilterpopup').modal('show');
            }
        }
    })
}

function Downloadxls(url) {
    window.location.href = '../../export/' + url;
}
