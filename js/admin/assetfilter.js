
function Get_AssetQueryDT() {
    $.ajax({
        type: "GET",
        url: "../lib/l-ajax.php",
        data: "function=AJAX_GetAssetQueryGridData&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        success: function(gridData) {
            $(".se-pre-con").hide();
            $('#assetQuerTable').DataTable().destroy();
            assetQuerTable = $('#assetQuerTable').DataTable({
                scrollY: jQuery('#assetQuerTable').data('height'),
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
                initComplete: function(settings, json) {
                    $('#assetQuerTable tbody tr:eq(0)').addClass("selected");
                    var qid = $('#assetQuerTable tbody tr:eq(0) p')[0].id;
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
        },
        error: function(msg) {

        }
    });
    
    $('#assetQuerTable').on('click', 'tr', function() {
        var rowID = assetQuerTable.row(this).data();
        var selected = rowID[3];
        var assetname = rowID[4]
        $("#selected").val(selected);
        $('#Assetnme').val(assetname);
        assetQuerTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        loadQueryData(selected);
    });
    
    $("#deviceinfo_searchbox").keyup(function() {
        assetQuerTable.search(this.value).draw();
    });

}

//Popup validation start
function selectConfirm(data_target_id) {

    $("#normError").hide();
    $("#mainError").show();

    var selected = $("#selected").val();

    if (selected === '') {

        $("#warning").modal('show');
        
    } else {

        if (data_target_id === 'edit') {
            
            window.location.href = '../admin/qury-act.php?action=edit&id='+selected;

        } else if (data_target_id === 'copy') {
            
            window.location.href = '../admin/qury-act.php?action=duplicate&id='+selected;

        } else if (data_target_id === 'delete') {

            $("#deleteAssetQuer").modal('show');

        }  else if (data_target_id === 'detail') {
            assetFilterDetails(selected);
            
        } else if (data_target_id === 'run') {
            
            var user = userName;
//            var site     = searchType;
//            var machineName = searchVal;
//            var rparent     = rParent;
          
            $.ajax({
               type: "POST",
               url : "../lib/l-ajax.php?function=AJAX_RunAssetQuery",
                data: '&id=' + selected + '&auth=' + user + '&csrfMagicToken=' + csrfMagicToken
            }).done(function (data) {
                 if($.trim(data) === 'success') {
                     $('#runAssetQuer').modal('show');
                 }
            });
        }
    }

    return true;

}

function deleteAssetQuery(){
    var selected = $("#selected").val();
    var name     = $('#Assetnme').val();

    $.ajax({
        url: '../lib/l-ajax.php',
        data: 'function=AJAX_ADMN_DeleteAssetQuery&id='+selected+"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'text',
        type: 'POST',
        success: function(data){
            if(data){
//                $("#selected").val("");
                $('#deletemessage').html('<span style="color:green;margin-left: 40%;"> Asset query</span> '+name+' <span>deleted successfully</span>');
                $('#deletemessage').fadeOut(3000);
                setTimeout(function(){ 
                    location.reload();
                }, 3200);
//                Get_AssetQueryDT();
            }
        }
    });
}

function addNewAssetFilter(){
    window.location.href = "../admin/qury-add.php?action=add";
}

function AssetQryRefresh() {
    $(".se-pre-con").show(); 
    Get_AssetQueryDT();  
    $("#deviceinfo_searchbox").val("");
}

function loadQueryData(id) {
   
   var searchType = $('#searchValue').val();
   
    $.ajax({
        type: "GET",
        url: "../home/sitefunctions.php?function=get_machinereportlist&id="+id+"&searchType="+searchType+"&csrfMagicToken=" + csrfMagicToken,
        data: "",
        dataType: 'json',
        success: function(gridData) {
            $('#assetqueryData').DataTable().destroy();
            assetTable = $('#assetqueryData').DataTable({
                scrollY: jQuery('#assetqueryData').data('height'),
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
                order: [[0, "desc"]],
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
                }

            });

        },
        error: function(msg) {

        }
    });
}

/*==== file download ====*/
function Downloadxls(url) {
    window.location.href = '../../export/' + url;
}

function assetFilterDetails(id) {
   
    $.ajax({
        url: '../lib/l-ajax.php?function=AJAX_getAssetDetails',
        data: {id:id, 'csrfMagicToken': csrfMagicToken},
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            $('#assetDetails').modal('show');
            $('#assetName').html(data.name);
            $('#criteria').html(data.condition);
            $('#displayField').html(data.fields);
        } 
       
        
    });
    
}