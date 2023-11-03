$(document).ready(function(){
    eventListAllLevel();
            });

function eventListAllLevel() 
{
    $("#eventTable").dataTable().fnDestroy();
    eventTable = $('#eventTable').DataTable({
        scrollY: 'calc(100vh - 200px)',
        bInfo: false,
        responsive: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0, 1, 2, 3]
            }],
        ajax: {
            url: "sitefunctions_EL.php?function=eventlistallData",//&host=" + machine + '&cust=' + site,
            type: "POST"
        },
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        columns: [
            {"data": "device"},
            {"data": "desc"},
            {"data": "scrip"},
            {"data": "clientTime"},
            {"data": "serverTime"}
        ],
        initComplete: function (settings, json) {

        }
    });


    $('#eventTable').on('click', 'tr', function () { //row selection code            
        eventTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        if ($(this).hasClass('selected')) {
            var rowdata = eventTable.row(this).data();
            $("#sel_id").val(rowdata.id);
        } else {
            var rowdata = eventTable.row(this).data();
            eventTable.$('tr.selected').removeClass('selected');
            $("#sel_id").val(rowdata.id);
            $(this).addClass('selected');
        }
    });

    $('#eventTable').on('dblclick', 'tbody tr', function(){
         var rowID = eventTable.row(this).data();
        if (rowID != 'undefined' && rowID !== undefined) {
            $('#eventEventInfo modal-body').html();
            $('#eventEventInfo').find('.modal-body').css('word-wrap','break-word');
            $('#eventEventInfo').modal({'show' : true,'backdrop' : true});
            var clickedTr = $(this), valueData;
            var eventInfoData = clickedTr.find('.event_info_node').attr('data-event-info'), htm = '';
                eventInfoData = $.parseJSON(eventInfoData);
                $.each(eventInfoData, function(k,d){
                    valueData = (typeof d == 'object') ? '<pre style="color:#525f7f">'+JSON.stringify(d, undefined, 2)+'</pre>' : d;
                    htm +="<b>"+k+" :</b> "+valueData+"<br />";
                });
            $('#eventEventInfo .modal-body').html(htm);
        }
        
    });

    $("#eventdetail_searchbox").keyup(function () {
        eventTable.search(this.value).draw();
    });
}


function exportEventInformation(){
    document.location.href = "../home/sitefunctions_EL.php?function=eventlistallData&export";
}