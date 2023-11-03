/*======== Users Grid Data code ============ */

$(document).ready(function() {
    $("#userList").dataTable().fnDestroy();
    var userListTable = $('#userList').DataTable({
        autoWidth: false,
        paging: true,
        searching: false,
        processing: true,
        serverSide: true,
        pagingType: "full_numbers",
        bAutoWidth: false ,
        bLengthChange: false,
        bExpandableGrouping : true,
        ajax: {
            url: "users.php?srch=1",
            type: "POST"
        },
        columns: [
            {"data": "name"},
            {"data": "email"},
            {"data": "notifymail"}
            
        ],
        "columnDefs": [
            { className: "dt-left tdColumn1", "targets": 0 },
            { className: "dt-left tdColumn2", "targets": 1 },
            { className: "dt-left tdColumn3", "targets": 2 }
          ]
    });
    $('#userList tbody').on( 'mouseover', 'td', function () {
            var rowID = userListTable.row(this).data();
            $("#userList tbody tr td").eq(0).attr("data-target","tooltip");
            $("#userList tbody tr td").eq(1).attr("data-target","tooltip");
            $("#userList tbody tr td").eq(2).attr("data-target","tooltip");
            $("td:nth-child(1)").attr("title",""+rowID.name);
            $("td:nth-child(2)").attr("title",""+rowID.email);
            $("td:nth-child(3)").attr("title",""+rowID.notifymail);
    });
});
    