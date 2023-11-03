$(document).ready(function() {
    elSavedSearches(); 
});

$("#searchPopup_id").click(function(e) {
    $(".error").html('');
});

function elEventGridData() {
    eventListAllLevel();
}

function elSavedSearches() {
    $.ajax({
        type: "POST",
        dataType: 'text',
        url: "sitefunctions.php?function=getSavedSearches",
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function(response) {
            $("#saved_searches").html(response);
            $(".selectpicker").selectpicker("refresh");
        }
    });
}

function eventListAllLevel(){
    var search = $("#eventSearchinfo").text();
    $("#userSearchValue").text("Event Details : "+search);
    $('#eventsfiltergrid').hide();
    $("#eleventFilterGrid").dataTable().fnDestroy();
    $.ajax({
        url: "sitefunctions.php?function=eventlistallData",
        type: "POST",
        dataType: "json",
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#eleventFilterGrid').DataTable().destroy();
            eventTable = $('#eleventFilterGrid').DataTable({
                scrollY: jQuery('#eleventFilterGrid').data('height'),
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
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });
    
    $("#eventdetail_searchbox").keyup(function() {
        eventTable.search(this.value).draw();
    });
}


$("#searchBtn").click(function(e) {
    $(".error").html('');
    var tempSaved_searches = $("#saved_searches").val();
    var savedSearches = tempSaved_searches.split('--');
    var DartNumber = $("#DartNumber").val();
    var StartTime = $("#StartTime").val();
    var EndTime = $("#EndTime").val();
    
    if (DartNumber === "" && !validateNumber(DartNumber)) {
        $("#req_DartNumber").html("Please enter valid DartNumber");
        $("#req_DartNumber").show();
        return;
    }
    
    if (StartTime === "") {
        $("#req_StartTime").html("StartTime is required");
        $("#req_StartTime").show();
        return;
    }
    
    if (EndTime === "") {
        $("#req_EndTimel").html("EndTime is required");
        $("#req_EndTimel").show();
        return;
    }
    $("#searchPopup").modal("hide");
     $(".se-pre-con").show();
    $.ajax({
        url: "sitefunctions.php?function=filteredEventData&saved_searches=" + savedSearches[1] + "&DartNumber=" + DartNumber + "&StartTime=" + StartTime +"&EndTime=" + EndTime,
        type: "POST",
        dataType: "json",
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#eleventFilterGrid').DataTable().destroy();
            eventTable = $('#eleventFilterGrid').DataTable({
                scrollY: jQuery('#eleventFilterGrid').data('height'),
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
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });

});




//validating numbers by using regular expression
function validateNumber(name)   
{  
	if (/^[0-9]*$/.test(name))  
	{  
		return true;
	}
    return false;  
}  
